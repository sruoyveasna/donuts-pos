<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\MenuItem;
use App\Models\MenuItemVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecipeController extends Controller
{
    /**
     * GET /api/recipes
     * Query:
     * - menu_item_id (optional)
     * - menu_item_variant_id (optional, can be null)
     * - ingredient_id (optional)
     * - include: ingredient|menu_item|variant (bool flags)
     * - per_page
     */
    public function index(Request $request)
    {
        $data = $request->validate([
            'menu_item_id'         => ['nullable', 'integer', 'exists:menu_items,id'],
            'menu_item_variant_id' => ['nullable', 'integer', 'exists:menu_item_variants,id'],
            'ingredient_id'        => ['nullable', 'integer', 'exists:ingredients,id'],
            'include_ingredient'   => ['nullable', 'boolean'],
            'include_menu_item'    => ['nullable', 'boolean'],
            'include_variant'      => ['nullable', 'boolean'],
            'per_page'             => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        $perPage = (int)($data['per_page'] ?? 20);

        $q = Recipe::query();

        // Optional eager-loads
        $with = [];
        if (!empty($data['include_ingredient'])) $with[] = 'ingredient';
        if (!empty($data['include_menu_item']))  $with[] = 'menuItem';   // withTrashed already in model
        if (!empty($data['include_variant']))    $with[] = 'variant';    // withTrashed already in model
        if ($with) $q->with($with);

        if (!empty($data['menu_item_id'])) $q->where('menu_item_id', $data['menu_item_id']);
        if (array_key_exists('menu_item_variant_id', $data)) $q->where('menu_item_variant_id', $data['menu_item_variant_id']);
        if (!empty($data['ingredient_id'])) $q->where('ingredient_id', $data['ingredient_id']);

        return response()->json(
            $q->orderBy('menu_item_id')
              ->orderByRaw('menu_item_variant_id IS NULL DESC') // show variant first or last as you like
              ->orderBy('ingredient_id')
              ->paginate($perPage)
              ->appends($request->query())
        );
    }

    /**
     * GET /api/recipes/group?menu_item_id=1&menu_item_variant_id=2(or omit for null)
     * Returns one “recipe set” (all lines).
     */
    public function showGroup(Request $request)
    {
        $data = $request->validate([
            'menu_item_id'         => ['required', 'integer', 'exists:menu_items,id'],
            'menu_item_variant_id' => ['nullable', 'integer', 'exists:menu_item_variants,id'],
            'include_ingredient'   => ['nullable', 'boolean'],
            'include_menu_item'    => ['nullable', 'boolean'],
            'include_variant'      => ['nullable', 'boolean'],
        ]);

        $this->assertVariantBelongsToItem($data['menu_item_id'], $data['menu_item_variant_id'] ?? null);

        $q = Recipe::where('menu_item_id', $data['menu_item_id'])
            ->where('menu_item_variant_id', $data['menu_item_variant_id'] ?? null);

        $with = [];
        if (!empty($data['include_ingredient'])) $with[] = 'ingredient';
        if (!empty($data['include_menu_item']))  $with[] = 'menuItem';
        if (!empty($data['include_variant']))    $with[] = 'variant';
        if ($with) $q->with($with);

        $lines = $q->orderBy('ingredient_id')->get();

        return response()->json([
            'menu_item_id'         => $data['menu_item_id'],
            'menu_item_variant_id' => $data['menu_item_variant_id'] ?? null,
            'lines'                => $lines,
        ]);
    }

    /**
     * PUT /api/recipes/group
     * Body:
     * {
     *   "menu_item_id": 1,
     *   "menu_item_variant_id": 2, // nullable
     *   "lines": [
     *      {"ingredient_id": 5, "quantity": 0.250},
     *      {"ingredient_id": 6, "quantity": 1.000}
     *   ]
     * }
     *
     * Behavior: REPLACE whole group (delete missing lines).
     */
    public function upsertGroup(Request $request)
    {
        $data = $request->validate([
            'menu_item_id'         => ['required', 'integer', 'exists:menu_items,id'],
            'menu_item_variant_id' => ['nullable', 'integer', 'exists:menu_item_variants,id'],

            'lines'                => ['required', 'array', 'min:1'],
            'lines.*.ingredient_id'=> ['required', 'integer', 'exists:ingredients,id'],
            'lines.*.quantity'     => ['required', 'numeric', 'gt:0'],
        ]);

        $variantId = $data['menu_item_variant_id'] ?? null;
        $this->assertVariantBelongsToItem($data['menu_item_id'], $variantId);

        $result = DB::transaction(function () use ($data, $variantId) {
            // Lock existing set to avoid race conditions while editing recipe
            $existing = Recipe::where('menu_item_id', $data['menu_item_id'])
                ->where('menu_item_variant_id', $variantId)
                ->lockForUpdate()
                ->get();

            $incomingIngredientIds = collect($data['lines'])->pluck('ingredient_id')->values();

            // delete lines that are removed
            Recipe::where('menu_item_id', $data['menu_item_id'])
                ->where('menu_item_variant_id', $variantId)
                ->whereNotIn('ingredient_id', $incomingIngredientIds)
                ->delete();

            // upsert each line
            foreach ($data['lines'] as $line) {
                Recipe::updateOrCreate(
                    [
                        'menu_item_id'         => $data['menu_item_id'],
                        'menu_item_variant_id' => $variantId,
                        'ingredient_id'        => $line['ingredient_id'],
                    ],
                    [
                        'quantity' => $line['quantity'],
                    ]
                );
            }

            return Recipe::where('menu_item_id', $data['menu_item_id'])
                ->where('menu_item_variant_id', $variantId)
                ->with(['ingredient'])
                ->orderBy('ingredient_id')
                ->get();
        });

        return response()->json([
            'message' => 'Recipe saved',
            'menu_item_id' => $data['menu_item_id'],
            'menu_item_variant_id' => $variantId,
            'lines' => $result,
        ]);
    }

    /**
     * DELETE /api/recipes/group?menu_item_id=1&menu_item_variant_id=2
     * Deletes all recipe lines for that group.
     */
    public function deleteGroup(Request $request)
    {
        $data = $request->validate([
            'menu_item_id'         => ['required', 'integer', 'exists:menu_items,id'],
            'menu_item_variant_id' => ['nullable', 'integer', 'exists:menu_item_variants,id'],
        ]);

        $variantId = $data['menu_item_variant_id'] ?? null;
        $this->assertVariantBelongsToItem($data['menu_item_id'], $variantId);

        Recipe::where('menu_item_id', $data['menu_item_id'])
            ->where('menu_item_variant_id', $variantId)
            ->delete();

        return response()->json(['message' => 'Recipe group deleted']);
    }

    /**
     * DELETE /api/recipes/{recipe}
     * Deletes one line.
     */
    public function destroy(Recipe $recipe)
    {
        $recipe->delete();
        return response()->json(['message' => 'Recipe line deleted']);
    }

    /**
     * Safety: variant must belong to item
     */
    private function assertVariantBelongsToItem(int $menuItemId, ?int $variantId): void
    {
        if ($variantId === null) return;

        $ok = MenuItemVariant::where('id', $variantId)
            ->where('menu_item_id', $menuItemId)
            ->exists();

        if (!$ok) abort(422, 'Selected variant does not belong to the menu item.');
    }
}
