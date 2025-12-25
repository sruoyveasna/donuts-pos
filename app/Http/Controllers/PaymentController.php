<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;
class PaymentController extends Controller
{
    /**
     * POST /api/pos/orders/{order}/payments
     */
 // optional if you want fallback from settings

    public function store(Request $request, Order $order)
    {
        $data = $request->validate([
            'method'         => ['required', 'string', 'max:50'],
            'currency'       => ['required', 'in:KHR,USD'],
            'tendered_khr'   => ['nullable', 'integer', 'min:0'],
            'tendered_usd'   => ['nullable', 'numeric', 'min:0'],
            // ✅ do not accept exchange_rate from client
            // 'exchange_rate' => ['nullable', 'numeric', 'min:1'],
            'transaction_id' => ['nullable', 'string', 'max:100'],
            'meta'           => ['nullable', 'array'],
        ]);

        $result = DB::transaction(function () use ($data, $order) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            $order->loadSum('payments as paid_khr', 'amount_khr');
            $paid   = (int)($order->paid_khr ?? 0);
            $dueKhr = max(0, (int)$order->total_khr - $paid);

            if ($dueKhr <= 0 || $order->status === 'paid') {
                abort(422, 'Order already fully paid.');
            }

            // ✅ Use order snapshot exchange rate
            $rate = (float) ($order->exchange_rate ?: 0);
            if ($rate <= 0) {
                // fallback only if old orders missing rate
                $rate = (float) Setting::getCached('pos.currency.exchange_usd', 4100);
            }

            if ($data['currency'] === 'KHR') {
                $tenderedKhr = (int)($data['tendered_khr'] ?? 0);
            } else {
                $tenderedUsd = (float)($data['tendered_usd'] ?? 0);
                $tenderedKhr = (int) round($tenderedUsd * $rate);
            }

            if ($tenderedKhr <= 0) abort(422, 'Tendered amount must be greater than zero.');
            if ($tenderedKhr < $dueKhr) abort(422, 'Tendered amount is less than order total.');

            $amountKhr = $dueKhr;
            $changeKhr = max(0, $tenderedKhr - $dueKhr);

            $payment = $order->payments()->create([
                'method'         => $data['method'],
                'status'         => 'confirmed',
                'transaction_id' => $data['transaction_id'] ?? null,
                'confirmed_at'   => now(),
                'currency'       => $data['currency'],
                'amount_khr'     => $amountKhr,
                'tendered_khr'   => $tenderedKhr,
                'change_khr'     => $changeKhr,
                'exchange_rate'  => $rate,
                'meta'           => $data['meta'] ?? [],
            ]);

            $order->update([
                'status'  => 'paid',
                'paid_at' => now(),
            ]);

            $order->load([
                'user:id,name,email',
                'items.menuItem:id,name',
                'items.menuItemVariant:id,name,price',
                'payments',
            ])->loadSum('payments as paid_khr', 'amount_khr');

            $paidNew = (int)($order->paid_khr ?? 0);
            $order->due_khr = max(0, (int)$order->total_khr - $paidNew);
            $order->is_paid = $order->due_khr <= 0;

            return ['payment' => $payment, 'order' => $order];
        });

        return response()->json([
            'message' => 'Payment recorded',
            'payment' => $result['payment'],
            'order'   => $result['order'],
        ], 201);
    }

}
