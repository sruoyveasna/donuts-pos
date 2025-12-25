<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IngredientController extends Controller
{
    /**
     * GET /api/ingredients
     * Query (optional):
     *  - q: search by name
     *  - low_only: 1|0 (only low stock)
     *  - per_page: 10/20/50/100
     *  - sort: name|current_qty|low_alert_qty|last_restocked_at|created_at
     *  - dir: asc|desc
     */
    public function index(Request $request)
    {
        $data = $request->validate([
            'q'        => ['nullable', 'string', 'max:100'],
            'low_only' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
            'sort'     => ['nullable', 'in:name,current_qty,low_alert_qty,last_restocked_at,created_at'],
            'dir'      => ['nullable', 'in:asc,desc'],
        ]);

        $perPage = (int)($data['per_page'] ?? 10);
        $sort    = $data['sort'] ?? 'created_at';
        $dir     = $data['dir'] ?? 'desc';
        $q       = trim((string)($data['q'] ?? ''));

        $query = Ingredient::query()
            ->withCount('movements');

        if ($q !== '') {
            $query->where('name', 'like', "%{$q}%");
        }

        if (!empty($data['low_only'])) {
            // low = current_qty <= low_alert_qty (or <= 0 if low_alert_qty is null)
            $query->whereRaw('current_qty <= COALESCE(low_alert_qty, 0)');
        }

        $paged = $query->orderBy($sort, $dir)->paginate($perPage);

        // Add computed flags for UI
        $paged->getCollection()->transform(function ($ing) {
            $ing->is_low = (float)$ing->current_qty <= (float)($ing->low_alert_qty ?? 0);
            return $ing;
        });

        return response()->json($paged);
    }

    /**
     * GET /api/ingredients/{ingredient}
     * Query (optional):
     *  - movements_per_page: 10/20/50/100
     */
    public function show(Request $request, Ingredient $ingredient)
    {
        $data = $request->validate([
            'movements_per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        $movementsPerPage = (int)($data['movements_per_page'] ?? 20);

        $ingredient->is_low = (float)$ingredient->current_qty <= (float)($ingredient->low_alert_qty ?? 0);

        $movements = $ingredient->movements()
            ->with(['user:id,name,email'])
            ->latest()
            ->paginate($movementsPerPage);

        return response()->json([
            'ingredient' => $ingredient,
            'movements'  => $movements,
        ]);
    }

    /**
     * POST /api/ingredients
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => ['required', 'string', 'max:120'],
            'unit'            => ['required', 'string', 'max:20'], // e.g. g, kg, ml, l, pcs
            'low_alert_qty'   => ['nullable', 'numeric', 'min:0'],
            'current_qty'     => ['nullable', 'numeric', 'min:0'],
            'last_restocked_at' => ['nullable', 'date'],
        ]);

        $ingredient = Ingredient::create([
            'name'             => $data['name'],
            'unit'             => $data['unit'],
            'low_alert_qty'    => $data['low_alert_qty'] ?? 0,
            'current_qty'      => $data['current_qty'] ?? 0,
            'last_restocked_at'=> $data['last_restocked_at'] ?? null,
        ]);

        $ingredient->is_low = (float)$ingredient->current_qty <= (float)($ingredient->low_alert_qty ?? 0);

        return response()->json([
            'message'    => 'Ingredient created',
            'ingredient' => $ingredient,
        ], 201);
    }

    /**
     * PATCH /api/ingredients/{ingredient}
     */
    public function update(Request $request, Ingredient $ingredient)
    {
        $data = $request->validate([
            'name'            => ['nullable', 'string', 'max:120'],
            'unit'            => ['nullable', 'string', 'max:20'],
            'low_alert_qty'   => ['nullable', 'numeric', 'min:0'],
            'current_qty'     => ['nullable', 'numeric', 'min:0'],
        ]);

        $ingredient->update($data);

        $ingredient->is_low = (float)$ingredient->current_qty <= (float)($ingredient->low_alert_qty ?? 0);

        return response()->json([
            'message'    => 'Updated',
            'ingredient' => $ingredient,
        ]);
    }

    /**
     * DELETE /api/ingredients/{ingredient}
     * (Hard delete unless you add SoftDeletes on model/migration.)
     */
    public function destroy(Ingredient $ingredient)
    {
        $ingredient->delete();

        return response()->json([
            'message' => 'Deleted',
        ]);
    }

    /**
     * POST /api/ingredients/{ingredient}/adjust
     *
     * Supports 3 modes:
     * 1) restock: qty adds to stock
     * 2) consume: qty subtracts from stock
     * 3) adjust: delta_qty can be positive or negative
     *
     * Payload example:
     * { "action": "restock", "qty": 2.5, "note": "New delivery" }
     * { "action": "consume", "qty": 0.25, "note": "Used for latte" }
     * { "action": "adjust", "delta_qty": -1.0, "note": "Waste" }
     */
    public function adjust(Request $request, Ingredient $ingredient)
    {
        $data = $request->validate([
            'action'    => ['required', 'in:restock,consume,adjust'],
            'qty'       => ['nullable', 'numeric', 'min:0.001'],
            'delta_qty' => ['nullable', 'numeric'], // can be negative for adjust
            'note'      => ['nullable', 'string', 'max:255'],
        ]);

        $userId = $request->user()?->id;

        $result = DB::transaction(function () use ($ingredient, $data, $userId) {
            // lock row for safe concurrent stock updates
            $ing = Ingredient::whereKey($ingredient->id)->lockForUpdate()->firstOrFail();

            $note = $data['note'] ?? null;

            if ($data['action'] === 'restock') {
                $qty = (float)($data['qty'] ?? 0);
                if ($qty <= 0) abort(422, 'Qty is required for restock.');
                $ing->addStock($qty, $note, $userId);
            }

            if ($data['action'] === 'consume') {
                $qty = (float)($data['qty'] ?? 0);
                if ($qty <= 0) abort(422, 'Qty is required for consume.');
                $ing->useStock($qty, $note, $userId);
            }

            if ($data['action'] === 'adjust') {
                if (!isset($data['delta_qty'])) abort(422, 'delta_qty is required for adjust.');
                $delta = (float)$data['delta_qty'];
                if ($delta == 0.0) abort(422, 'delta_qty must not be zero.');
                $ing->adjustStock($delta, 'adjust', $note, $userId);
            }

            $ing->refresh();
            $ing->is_low = (float)$ing->current_qty <= (float)($ing->low_alert_qty ?? 0);

            return $ing;
        });

        return response()->json([
            'message'    => 'Stock updated',
            'ingredient' => $result,
        ]);
    }
}
