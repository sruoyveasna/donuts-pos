<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    // GET /api/discounts
    public function index(Request $request)
    {
        $data = $request->validate([
            'q'           => ['nullable','string','max:100'],
            'with_trashed'=> ['nullable','boolean'],
            'active_only' => ['nullable','boolean'],
            'per_page'    => ['nullable','integer','min:1','max:200'],
            'sort'        => ['nullable','in:created_at,name,code,is_active,starts_at,ends_at'],
            'dir'         => ['nullable','in:asc,desc'],
        ]);

        $q        = trim((string)($data['q'] ?? ''));
        $perPage  = (int)($data['per_page'] ?? 10);
        $sort     = $data['sort'] ?? 'created_at';
        $dir      = $data['dir'] ?? 'desc';
        $trashed  = (bool)($data['with_trashed'] ?? false);
        $activeOnly = (bool)($data['active_only'] ?? false);

        $query = Discount::query();

        if ($trashed) $query->withTrashed();
        if ($activeOnly) $query->where('is_active', true);

        if ($q !== '') {
            $query->where(function ($s) use ($q) {
                $s->where('name','like',"%{$q}%")
                  ->orWhere('code','like',"%{$q}%");
            });
        }

        return response()->json(
            $query->orderBy($sort, $dir)->paginate($perPage)
        );
    }

    // GET /api/discounts/{discount}
    public function show(Discount $discount)
    {
        return response()->json(['discount' => $discount]);
    }

    // POST /api/discounts
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => ['required','string','max:120'],
            'code'             => ['nullable','string','max:50','unique:discounts,code'],
            'type'             => ['required','in:fixed_khr,percent'],
            'value'            => ['required','numeric','min:0'],

            'min_subtotal_khr' => ['nullable','integer','min:0'],
            'max_discount_khr' => ['nullable','integer','min:0'],

            'is_active'        => ['nullable','boolean'],
            'starts_at'        => ['nullable','date'],
            'ends_at'          => ['nullable','date','after_or_equal:starts_at'],

            'usage_limit'      => ['nullable','integer','min:1'],
            'meta'             => ['nullable','array'],
        ]);

        $discount = Discount::create([
            ...$data,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return response()->json(['message' => 'Created', 'discount' => $discount], 201);
    }

    // PATCH /api/discounts/{discount}
    public function update(Request $request, Discount $discount)
    {
        $data = $request->validate([
            'name'             => ['nullable','string','max:120'],
            'code'             => ['nullable','string','max:50','unique:discounts,code,'.$discount->id],
            'type'             => ['nullable','in:fixed_khr,percent'],
            'value'            => ['nullable','numeric','min:0'],

            'min_subtotal_khr' => ['nullable','integer','min:0'],
            'max_discount_khr' => ['nullable','integer','min:0'],

            'is_active'        => ['nullable','boolean'],
            'starts_at'        => ['nullable','date'],
            'ends_at'          => ['nullable','date'],

            'usage_limit'      => ['nullable','integer','min:1'],
            'meta'             => ['nullable','array'],
        ]);

        $discount->update($data);

        return response()->json(['message' => 'Updated', 'discount' => $discount->fresh()]);
    }

    // DELETE /api/discounts/{discount}
    public function destroy(Discount $discount)
    {
        $discount->delete();
        return response()->json(['message' => 'Archived']);
    }

    // POST /api/discounts/{id}/restore
    public function restore($id)
    {
        $discount = Discount::withTrashed()->findOrFail($id);
        $discount->restore();
        return response()->json(['message' => 'Restored', 'discount' => $discount]);
    }
}
