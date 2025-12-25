<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\MenuItemVariant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MenuItemVariantController extends Controller
{
    // GET /api/menu/items/{menuItem}/variants
    public function index(Request $request, MenuItem $menuItem)
    {
        $request->validate([
            'with_trashed' => 'nullable|boolean',
            'visible_only' => 'nullable|boolean',
        ]);

        $withTrashed = $request->boolean('with_trashed', false);
        $visibleOnly = $request->boolean('visible_only', false);

        $q = $menuItem->variants()->orderBy('position')->orderBy('id');
        if ($withTrashed) $q->withTrashed();
        if ($visibleOnly) $q->visible();

        return response()->json($q->get());
    }

    // POST /api/menu/items/{menuItem}/variants
    public function store(Request $request, MenuItem $menuItem)
    {
        $data = $request->validate([
            'name'      => [
                'required', 'string', 'max:100',
                Rule::unique('menu_item_variants', 'name')
                    ->where(fn($q) => $q->where('menu_item_id', $menuItem->id))
                    ->whereNull('deleted_at'),
            ],
            'price'     => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'sku'       => [
                'nullable','string','max:80',
                Rule::unique('menu_item_variants','sku')
                    ->where(fn($q) => $q->where('menu_item_id', $menuItem->id))
                    ->whereNull('deleted_at'),
            ],
            'position'  => 'nullable|integer|min:0',

            'discount_type'      => 'sometimes|nullable|in:percent,fixed',
            'discount_value'     => 'sometimes|nullable|numeric|min:0',
            'discount_starts_at' => 'sometimes|nullable|date',
            'discount_ends_at'   => 'sometimes|nullable|date|after_or_equal:discount_starts_at',
        ]);

        if (!empty($data['discount_type'])) {
            $dv = $data['discount_value'] ?? null;
            if ($data['discount_type'] === 'percent' && $dv !== null && $dv > 100) {
                return response()->json(['message' => 'percent cannot exceed 100'], 422);
            }
            if ($data['discount_type'] === 'fixed' && $dv !== null && $dv > (float)$data['price']) {
                return response()->json(['message' => 'fixed discount cannot exceed price'], 422);
            }
        }

        if (!array_key_exists('position', $data)) {
            $data['position'] = (int) $menuItem->variants()->max('position') + 1;
        }

        $data['is_active']    = $data['is_active'] ?? true;
        $data['menu_item_id'] = $menuItem->id;

        $variant = MenuItemVariant::create($data);
        return response()->json($variant, 201);
    }

    // SHALLOW: POST /api/variants/{variant}
    public function update(Request $request, MenuItemVariant $variant)
    {
        $data = $request->validate([
            'name'      => [
                'sometimes', 'string', 'max:100',
                Rule::unique('menu_item_variants', 'name')
                    ->ignore($variant->id)
                    ->where(fn($q) => $q->where('menu_item_id', $variant->menu_item_id))
                    ->whereNull('deleted_at'),
            ],
            'price'     => 'sometimes|numeric|min:0',
            'is_active' => 'sometimes|boolean',
            'sku'       => [
                'sometimes','nullable','string','max:80',
                Rule::unique('menu_item_variants','sku')
                    ->ignore($variant->id)
                    ->where(fn($q) => $q->where('menu_item_id', $variant->menu_item_id))
                    ->whereNull('deleted_at'),
            ],
            'position'  => 'sometimes|integer|min:0',

            'discount_type'      => 'sometimes|nullable|in:percent,fixed',
            'discount_value'     => 'sometimes|nullable|numeric|min:0',
            'discount_starts_at' => 'sometimes|nullable|date',
            'discount_ends_at'   => 'sometimes|nullable|date|after_or_equal:discount_starts_at',
        ]);

        $effectivePrice = array_key_exists('price', $data) ? (float)$data['price'] : (float)$variant->price;

        if (array_key_exists('discount_type', $data) && $data['discount_type']) {
            $dv = $data['discount_value'] ?? $variant->discount_value ?? null;
            if ($data['discount_type'] === 'percent' && $dv !== null && $dv > 100) {
                return response()->json(['message' => 'percent cannot exceed 100'], 422);
            }
            if ($data['discount_type'] === 'fixed' && $dv !== null && $dv > $effectivePrice) {
                return response()->json(['message' => 'fixed discount cannot exceed price'], 422);
            }
        }

        $variant->update($data);

        return response()->json([
            'message' => 'Updated',
            'variant' => $variant->fresh()
        ]);
    }

    // SHALLOW: DELETE /api/variants/{variant}
    public function destroy(Request $request, MenuItemVariant $variant)
    {
        $force = $request->boolean('force', false);
        if ($force) {
            $variant->forceDelete();
            return response()->json(['message' => 'Variant permanently deleted']);
        }

        $variant->update(['is_active' => false]);
        $variant->delete();

        return response()->json(['message' => 'Variant archived']);
    }

    // NESTED: POST /api/menu/items/{menuItem}/variants/{variant}/restore
    public function restore(Request $request, $menuItemId, $variantId)
    {
        $variant = MenuItemVariant::withTrashed()
            ->where('menu_item_id', $menuItemId)
            ->findOrFail($variantId);

        $variant->restore();
        if ($request->boolean('reactivate', true)) {
            $variant->update(['is_active' => true]);
        }

        return response()->json(['message' => 'Variant restored', 'variant' => $variant->fresh()]);
    }
}
