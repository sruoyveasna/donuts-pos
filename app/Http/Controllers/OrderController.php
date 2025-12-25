<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Round KHR to nearest 100 (ends with 00)
     * Examples: 12344->12300, 12351->12400
     */
    protected function roundKhr($n): int
    {
        return (int) (round(((float) $n) / 100) * 100);
    }

    /**
     * ✅ Stock deduction (safe):
     * - If a menu item (or variant) has NO recipe lines => still allow order, just skip deduction for that item.
     * - Variant recipe takes priority; if variant has no recipe, fallback to base recipe (if exists).
     * - Deduct aggregated ingredient usage once (locks ingredient rows).
     *
     * Assumptions (adjust if your table/columns differ):
     * - recipes table has: menu_item_id, menu_item_variant_id (nullable), ingredient_id, quantity
     * - ingredients table has: id, current_qty
     */
    protected function deductStockFromRecipes(array $posItems): void
    {
        if (empty($posItems)) return;

        // Collect ids
        $menuItemIds   = [];
        $variantIds    = [];

        foreach ($posItems as $row) {
            if (!empty($row['menu_item_id'])) {
                $menuItemIds[] = (int) $row['menu_item_id'];
            }
            if (!empty($row['menu_item_variant_id'])) {
                $variantIds[] = (int) $row['menu_item_variant_id'];
            }
        }

        $menuItemIds = array_values(array_unique(array_filter($menuItemIds)));
        $variantIds  = array_values(array_unique(array_filter($variantIds)));

        if (!$menuItemIds && !$variantIds) return;

        // Pull all relevant recipe lines in 2 queries
        $baseLines = [];
        if ($menuItemIds) {
            $baseLines = DB::table('recipes')
                ->select(['menu_item_id', 'ingredient_id', 'quantity'])
                ->whereIn('menu_item_id', $menuItemIds)
                ->whereNull('menu_item_variant_id')
                ->get()
                ->toArray();
        }

        $variantLines = [];
        if ($variantIds) {
            $variantLines = DB::table('recipes')
                ->select(['menu_item_variant_id', 'ingredient_id', 'quantity'])
                ->whereIn('menu_item_variant_id', $variantIds)
                ->get()
                ->toArray();
        }

        // Index them for fast lookup
        $baseMap = [];    // menu_item_id => lines[]
        foreach ($baseLines as $l) {
            $mid = (int)($l->menu_item_id ?? 0);
            if (!$mid) continue;
            $baseMap[$mid][] = [
                'ingredient_id' => (int)($l->ingredient_id ?? 0),
                'quantity'      => (float)($l->quantity ?? 0),
            ];
        }

        $variantMap = []; // variant_id => lines[]
        foreach ($variantLines as $l) {
            $vid = (int)($l->menu_item_variant_id ?? 0);
            if (!$vid) continue;
            $variantMap[$vid][] = [
                'ingredient_id' => (int)($l->ingredient_id ?? 0),
                'quantity'      => (float)($l->quantity ?? 0),
            ];
        }

        // Aggregate total ingredient usage for this order
        $deduct = []; // ingredient_id => qty_to_deduct
        foreach ($posItems as $row) {
            $qty = (int)($row['quantity'] ?? 0);
            if ($qty <= 0) continue;

            $menuId = !empty($row['menu_item_id']) ? (int)$row['menu_item_id'] : null;
            $varId  = !empty($row['menu_item_variant_id']) ? (int)$row['menu_item_variant_id'] : null;

            // Choose recipe lines:
            // 1) variant lines if exist
            // 2) else base lines if exist
            $lines = [];
            if ($varId && !empty($variantMap[$varId])) {
                $lines = $variantMap[$varId];
            } elseif ($menuId && !empty($baseMap[$menuId])) {
                $lines = $baseMap[$menuId];
            } else {
                // ✅ No recipe at all -> skip stock deduction for this item
                continue;
            }

            foreach ($lines as $l) {
                $ingId = (int)($l['ingredient_id'] ?? 0);
                $per   = (float)($l['quantity'] ?? 0);

                if ($ingId <= 0 || $per <= 0) continue;

                $use = $per * $qty;
                if (!isset($deduct[$ingId])) $deduct[$ingId] = 0.0;
                $deduct[$ingId] += $use;
            }
        }

        if (!$deduct) return;

        // Lock ingredient rows once, then update
        $ingredientIds = array_keys($deduct);

        // Lock rows to avoid race conditions during concurrent orders
        $locked = DB::table('ingredients')
            ->whereIn('id', $ingredientIds)
            ->lockForUpdate()
            ->get(['id', 'current_qty']);

        // Update each ingredient (clamp at 0 to avoid unsigned/negative issues)
        foreach ($locked as $ing) {
            $id = (int)$ing->id;
            $use = (float)($deduct[$id] ?? 0);
            if ($use <= 0) continue;

            // MySQL: GREATEST works; if you use SQLite/other, adjust accordingly.
            DB::table('ingredients')
                ->where('id', $id)
                ->update([
                    'current_qty' => DB::raw('GREATEST(current_qty - ' . (float)$use . ', 0)')
                ]);
        }
    }

    /**
     * GET /api/orders
     */
    public function index(Request $request)
    {
        $data = $request->validate([
            'q'        => ['nullable', 'string', 'max:100'],
            'status'   => ['nullable', 'string', 'max:30'],
            'from'     => ['nullable', 'date'],
            'to'       => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
            'sort'     => ['nullable', 'in:created_at,total_khr,order_code,status'],
            'dir'      => ['nullable', 'in:asc,desc'],
        ]);

        $perPage = (int)($data['per_page'] ?? 10);
        $sort    = $data['sort'] ?? 'created_at';
        $dir     = $data['dir'] ?? 'desc';

        $q      = trim((string)($data['q'] ?? ''));
        $status = $data['status'] ?? null;
        $from   = $data['from'] ?? null;
        $to     = $data['to'] ?? null;

        $query = Order::query()
            ->with([
                'user:id,name,email',
                'discount:id,name,code,type,value,is_active,starts_at,ends_at',
            ])
            ->withCount(['items as items_count'])
            ->withSum('payments as paid_khr', 'amount_khr');

        if ($q !== '') {
            $query->where(function ($s) use ($q) {
                $s->where('order_code', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($u) use ($q) {
                        $u->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%");
                    });
            });
        }

        if ($status) $query->where('status', $status);
        if ($from)   $query->whereDate('created_at', '>=', $from);
        if ($to)     $query->whereDate('created_at', '<=', $to);

        $paged = $query->orderBy($sort, $dir)->paginate($perPage);

        $paged->getCollection()->transform(function ($o) {
            $paid = (int)($o->paid_khr ?? 0);
            $o->due_khr = max(0, (int)$o->total_khr - $paid);
            $o->is_paid = $o->due_khr <= 0;
            return $o;
        });

        return response()->json($paged);
    }

    /**
     * GET /api/orders/{order}
     */
    public function show(Order $order)
    {
        $order->load([
            'user:id,name,email',
            'discount:id,name,code,type,value,is_active,starts_at,ends_at',
            'items.menuItem:id,name',
            'items.menuItemVariant:id,name,price',
            'payments',
        ])->loadSum('payments as paid_khr', 'amount_khr');

        $paid = (int)($order->paid_khr ?? 0);
        $order->due_khr = max(0, (int)$order->total_khr - $paid);
        $order->is_paid = $order->due_khr <= 0;

        $order->items->transform(function ($it) {
            $it->menu_item_name = $it->menuItem?->name;
            $it->variant_name   = $it->menuItemVariant?->name;
            return $it;
        });

        return response()->json(['order' => $order]);
    }

    /**
     * POST /api/pos/orders
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'items'                        => ['required', 'array', 'min:1'],
            'items.*.menu_item_id'         => ['required_without:items.*.menu_item_variant_id', 'exists:menu_items,id'],
            'items.*.menu_item_variant_id' => ['nullable', 'exists:menu_item_variants,id'],
            'items.*.quantity'             => ['required', 'integer', 'min:1'],
            'items.*.unit_price'           => ['required', 'numeric', 'min:0'], // USD
            'items.*.customizations'       => ['nullable', 'array'],
            'items.*.note'                 => ['nullable', 'string', 'max:500'],

            // NEW discount inputs
            'discount_mode'     => ['nullable', 'in:amount,percent,code'],
            'discount_amount'   => ['nullable', 'required_if:discount_mode,amount', 'numeric', 'min:0'],
            'discount_currency' => ['nullable', 'required_if:discount_mode,amount', 'in:KHR,USD'],
            'discount_percent'  => ['nullable', 'required_if:discount_mode,percent', 'numeric', 'min:0', 'max:100'],
            'discount_code'     => ['nullable', 'required_if:discount_mode,code', 'string', 'max:50'],

            // Backward compatibility
            'discount_khr'      => ['nullable', 'integer', 'min:0'],
            'discount_id'       => ['nullable', 'exists:discounts,id'],
        ]);

        $user = $request->user();

        // Settings (server truth)
        $exchangeRate = (float) Setting::getCached('pos.currency.exchange_usd', 4100);

        $taxEnabled = Setting::getCached('pos.tax.enabled', 'true') === 'true';
        $taxRate    = $taxEnabled ? (float) Setting::getCached('pos.tax.rate', 10) : 0.0;

        $order = DB::transaction(function () use ($data, $user, $exchangeRate, $taxRate) {

            // ---- subtotal: sum USD first, convert once, round once (matches your JS) ----
            $subtotalUsd = 0.0;
            $totalItems  = 0;

            foreach ($data['items'] as $row) {
                $qty     = (int) $row['quantity'];
                $unitUsd = (float) $row['unit_price'];

                $subtotalUsd += ($unitUsd * $qty);
                $totalItems  += $qty;
            }

            $subtotalKhr = $this->roundKhr($subtotalUsd * $exchangeRate);

            // ---- resolve discount ----
            $mode = $data['discount_mode'] ?? null;

            // legacy fallback
            $discountKhr = (int)($data['discount_khr'] ?? 0);
            $discount    = null;

            $discountId   = $data['discount_id'] ?? null;
            $discountCode = Str::upper(trim((string)($data['discount_code'] ?? '')));

            if (!$mode && $discountCode !== '') $mode = 'code';

            // 1) amount
            if ($mode === 'amount') {
                $amt = (float) ($data['discount_amount'] ?? 0);
                $cur = $data['discount_currency'] ?? 'KHR';

                $discountKhr = ($cur === 'USD')
                    ? $this->roundKhr($amt * $exchangeRate)
                    : $this->roundKhr($amt);
            }

            // 2) percent (manual)
            if ($mode === 'percent') {
                $p = (float) ($data['discount_percent'] ?? 0);
                $p = max(0, min(100, $p));
                $discountKhr = $this->roundKhr($subtotalKhr * ($p / 100));
            }

            // 3) promo code (or discount_id)
            if ($mode === 'code' || $discountId || $discountCode !== '') {

                if (!$discountId && $discountCode !== '') {
                    $discountId = Discount::query()
                        ->whereRaw('UPPER(code) = ?', [$discountCode])
                        ->value('id');
                }

                if (!$discountId) abort(422, 'Invalid promo code.');

                $discount = Discount::whereKey($discountId)->lockForUpdate()->first();
                if (!$discount) abort(422, 'Discount not found.');

                if (method_exists($discount, 'isUsable')) {
                    if (!$discount->isUsable((int)$subtotalKhr)) abort(422, 'Discount is not usable.');
                } else {
                    if (!(bool)$discount->is_active) abort(422, 'Discount is not active.');
                }

                $type  = (string) $discount->type;
                $value = (float)  $discount->value;

                if ($type === 'percent') {
                    $discountKhr = $this->roundKhr($subtotalKhr * ($value / 100));
                } else {
                    $discountKhr = $this->roundKhr($value);
                }

                $discountKhr = max(0, min($discountKhr, (int)$subtotalKhr));

                if ($discount->max_discount_khr !== null && (int)$discount->max_discount_khr > 0) {
                    $cap = $this->roundKhr((int)$discount->max_discount_khr);
                    $discountKhr = min($discountKhr, $cap);
                }
            }

            $discountKhr = max(0, min((int)$discountKhr, (int)$subtotalKhr));

            // ---- tax after discount ----
            $afterDiscountKhr = max(0, $subtotalKhr - $discountKhr);
            $taxKhr           = $this->roundKhr($afterDiscountKhr * ($taxRate / 100));
            $totalKhr         = $this->roundKhr(max(0, $afterDiscountKhr + $taxKhr));

            // ---- create order ----
            $order = Order::create([
                'user_id'       => $user?->id,
                'discount_id'   => $discount?->id,
                'order_code'    => $this->generateOrderCode(),
                'status'        => 'unpaid',
                'subtotal_khr'  => $subtotalKhr,
                'discount_khr'  => $discountKhr,
                'tax_khr'       => $taxKhr,
                'total_khr'     => $totalKhr,
                'total_items'   => $totalItems,
                'tax_rate'      => $taxRate,
                'exchange_rate' => $exchangeRate,
            ]);

            if ($discount) {
                try { $discount->increment('used_count'); } catch (\Throwable $e) {}
            }

            // ---- create items ----
            foreach ($data['items'] as $row) {
                $qty     = (int) $row['quantity'];
                $unitUsd = (float) $row['unit_price'];

                OrderItem::create([
                    'order_id'             => $order->id,
                    'menu_item_id'         => $row['menu_item_id'] ?? null,
                    'menu_item_variant_id' => $row['menu_item_variant_id'] ?? null,
                    'quantity'             => $qty,
                    'price'                => $unitUsd,
                    'subtotal'             => $unitUsd * $qty,
                    'customizations'       => $row['customizations'] ?? null,
                    'note'                 => $row['note'] ?? null,
                ]);
            }

            // ✅ STOCK DEDUCTION (skip items with no recipe lines)
            $this->deductStockFromRecipes($data['items']);

            return $order;
        });

        $order->load([
            'user:id,name,email',
            'discount:id,name,code,type,value,is_active,starts_at,ends_at',
            'items.menuItem:id,name',
            'items.menuItemVariant:id,name,price',
            'payments',
        ])->loadSum('payments as paid_khr', 'amount_khr');

        $paid = (int)($order->paid_khr ?? 0);
        $order->due_khr = max(0, (int)$order->total_khr - $paid);
        $order->is_paid = $order->due_khr <= 0;

        return response()->json([
            'message' => 'Order created',
            'order'   => $order,
        ], 201);
    }

    public function previewDiscount(Request $request)
    {
        $data = $request->validate([
            'discount_code' => ['required', 'string', 'max:50'],
            'subtotal_khr'  => ['required', 'integer', 'min:0'],
        ]);

        $code = Str::upper(trim($data['discount_code']));
        $subtotalKhr = $this->roundKhr((int) $data['subtotal_khr']);

        $discount = Discount::query()
            ->whereRaw('UPPER(code) = ?', [$code])
            ->first();

        if (!$discount) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid promo code.',
                'discount_khr' => 0,
            ], 422);
        }

        if (!(bool) $discount->is_active) {
            return response()->json([
                'valid' => false,
                'message' => 'Discount is not active.',
                'discount_khr' => 0,
            ], 422);
        }

        $now = now();
        if (($discount->starts_at && $now->lt($discount->starts_at)) ||
            ($discount->ends_at && $now->gt($discount->ends_at))) {
            return response()->json([
                'valid' => false,
                'message' => 'Promo code not available.',
                'discount_khr' => 0,
            ], 422);
        }

        if ($discount->min_subtotal_khr !== null && $subtotalKhr < (int)$discount->min_subtotal_khr) {
            return response()->json([
                'valid' => false,
                'message' => 'Subtotal is too low for this promo.',
                'discount_khr' => 0,
            ], 422);
        }

        $type  = (string) $discount->type;
        $value = (float) $discount->value;

        if ($type === 'percent') {
            $discountKhr = $this->roundKhr($subtotalKhr * ($value / 100));
        } elseif ($type === 'fixed_khr') {
            $discountKhr = $this->roundKhr($value);
        } else {
            $discountKhr = 0;
        }

        $discountKhr = max(0, min($discountKhr, $subtotalKhr));

        if ($discount->max_discount_khr !== null && (int)$discount->max_discount_khr > 0) {
            $cap = $this->roundKhr((int)$discount->max_discount_khr);
            $discountKhr = min($discountKhr, $cap);
        }

        return response()->json([
            'valid' => true,
            'code' => $code,
            'discount_khr' => $discountKhr,
            'discount' => [
                'id' => $discount->id,
                'name' => $discount->name,
                'type' => $discount->type,
                'value' => $discount->value,
            ],
        ]);
    }

    protected function generateOrderCode(): string
    {
        return 'ORD-' . now()->format('ymd-His') . '-' . strtoupper(Str::random(4));
    }
}
