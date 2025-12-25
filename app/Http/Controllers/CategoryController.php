<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * GET /api/categories
     * Query:
     *  - q=donut                   (search name/slug)
     *  - per_page=10               (1..100)
     *  - with_trashed=1            (include archived)
     *  - visible_only=1            (is_active=1)
     *  - include_counts=1          (menu_items_count)
     *  - sort=name|slug|created_at (default: name)
     *  - dir=asc|desc              (default: asc)
     */
    public function index(Request $request)
    {
        $q            = trim((string) $request->query('q', ''));
        $withTrashed  = $request->boolean('with_trashed', false);
        $visibleOnly  = $request->boolean('visible_only', false);
        $includeCount = $request->boolean('include_counts', false);

        $perPage = (int) $request->integer('per_page', 10);
        $perPage = max(1, min($perPage, 100));

        $sortable = ['name', 'slug', 'created_at'];
        $sort = in_array($request->query('sort', 'name'), $sortable, true) ? $request->query('sort', 'name') : 'name';
        $dir  = $request->query('dir', 'asc') === 'desc' ? 'desc' : 'asc';

        $query = Category::query();

        if ($withTrashed) {
            $query->withTrashed();
        }
        if ($visibleOnly) {
            $query->visible();
        }
        if ($includeCount) {
            $query->withCount('menuItems');
        }

        if ($q !== '') {
            $needle = str_replace(['%','_'], ['\\%','\\_'], $q);
            $query->where(function ($sub) use ($needle) {
                $sub->where('name', 'like', "%{$needle}%")
                    ->orWhere('slug', 'like', "%{$needle}%");
            });
        }

        $cats = $query->orderBy($sort, $dir)->paginate($perPage)->appends($request->query());

        return response()->json($cats);
    }

    /**
     * GET /api/categories/{category}
     * Query:
     *  - include_items=1
     *  - visible_only_items=1
     */
    public function show(Request $request, Category $category)
    {
        $includeItems     = $request->boolean('include_items', false);
        $visibleOnlyItems = $request->boolean('visible_only_items', false);

        $relations = [];
        if ($includeItems) {
            $relations[] = 'menuItems';
        }

        $category->load($relations);

        if ($includeItems && $visibleOnlyItems) {
            // apply visible() scope in-memory
            $category->setRelation(
                'menuItems',
                $category->menuItems->where('is_active', true)->values()
            );
        }

        return response()->json($category);
    }

    /**
     * POST /api/categories
     * body: { name, slug?, is_active? }
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => [
                'required', 'string', 'max:150',
                Rule::unique('categories', 'name')->whereNull('deleted_at'),
            ],
            'slug'       => [
                'nullable', 'string', 'max:180',
                Rule::unique('categories', 'slug')->whereNull('deleted_at'),
            ],
            'is_active'  => ['sometimes', 'boolean'],
        ]);

        // Normalize name
        $data['name'] = preg_replace('/\s+/u', ' ', trim($data['name']));

        // Normalize slug input and auto-generate if empty/missing
        $slugInput   = $this->normalizeSlug($data['slug'] ?? null);
        $baseSlug    = Str::slug($slugInput ?: $data['name']);
        $data['slug'] = $this->uniqueSlug($baseSlug);

        // default active if not provided
        if (!array_key_exists('is_active', $data)) {
            $data['is_active'] = true;
        }

        $category = Category::create($data);

        return response()->json($category, 201);
    }

    /**
     * POST/PATCH /api/categories/{category}
     * body: { name?, slug?, is_active? }
     */
    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name'       => [
                'sometimes', 'string', 'max:150',
                Rule::unique('categories', 'name')
                    ->ignore($category->id)
                    ->whereNull('deleted_at'),
            ],
            'slug'       => [
                'sometimes', 'nullable', 'string', 'max:180',
                Rule::unique('categories', 'slug')
                    ->ignore($category->id)
                    ->whereNull('deleted_at'),
            ],
            'is_active'  => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('name', $data) && $data['name'] !== null) {
            $data['name'] = preg_replace('/\s+/u', ' ', trim($data['name']));
        }

        // Slug logic:
        if (array_key_exists('slug', $data)) {
            // slug was included (could be empty) → empty means auto-generate
            $slugInput = $this->normalizeSlug($data['slug']);

            if ($slugInput === null) {
                // auto from new name (if provided) else current name
                $from = $data['name'] ?? $category->name;
                $data['slug'] = $this->uniqueSlug(Str::slug($from), $category->id);
            } else {
                $data['slug'] = $this->uniqueSlug(Str::slug($slugInput), $category->id);
            }
        } elseif (array_key_exists('name', $data) && blank($category->slug)) {
            // record had no slug and name is being set → generate
            $data['slug'] = $this->uniqueSlug(Str::slug($data['name']), $category->id);
        }

        $category->update($data);

        return response()->json([
            'message'  => 'Updated',
            'category' => $category->fresh(),
        ]);
    }

    /**
     * DELETE /api/categories/{category}
     * ?force=1 permanently deletes (blocked if has items). Soft delete by default.
     */
    public function destroy(Request $request, Category $category)
    {
        $force = $request->boolean('force', false);

        if ($force) {
            if ($category->menuItems()->exists()) {
                return response()->json([
                    'message' => 'Cannot permanently delete: category still has menu items. Archive it or move items first.',
                ], 409);
            }
            $category->forceDelete();
            return response()->json(['message' => 'Permanently deleted']);
        }

        $category->update(['is_active' => false]);
        $category->delete();

        return response()->json(['message' => 'Archived']);
    }

    /**
     * POST /api/categories/{id}/restore
     * body: { reactivate?:bool=true }
     */
    public function restore(Request $request, $id)
    {
        $reactivate = $request->boolean('reactivate', true);

        $category = Category::withTrashed()->findOrFail($id);
        if (!$category->trashed()) {
            return response()->json(['message' => 'Category is not archived.'], 400);
        }

        $category->restore();

        if ($reactivate) {
            $category->update(['is_active' => true]);
        }

        return response()->json([
            'message'  => 'Restored',
            'category' => $category->fresh(),
        ]);
    }

    // ─────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────

    private function normalizeSlug(?string $slug): ?string
    {
        if (!is_string($slug)) return null;
        $slug = trim($slug);
        return $slug === '' ? null : $slug;
    }

    /**
     * Ensure slug uniqueness (considers soft-deletes).
     * Also guards against empty slugs (e.g., Khmer names) and length caps.
     */
    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = trim($base, '-');
        if ($slug === '') {
            // fallback if Str::slug produced empty (non-Latin)
            $slug = 'category';
        }

        // cap to your DB column length (180 recommended below)
        $max = 180;
        $slug = mb_substr($slug, 0, $max);

        $try = $slug;
        $suffix = 2;

        while (
            Category::withTrashed()
                ->where('slug', $try)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $tail = '-' . $suffix++;
            $try = mb_substr($slug, 0, $max - mb_strlen($tail)) . $tail;
        }

        return $try;
    }
}
