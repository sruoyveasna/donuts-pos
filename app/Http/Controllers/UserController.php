<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Web page: GET /users
     */
    public function page()
    {
        return view('users.index');
    }

    /**
     * API: GET /api/users
     * Query params:
     * - q
     * - sort: name|email|role|created_at
     * - dir: asc|desc
     * - per_page: 10..100
     * - page
     * - with_trashed: 1/0
     * - only_trashed: 1/0
     */
    public function index(Request $request)
    {
        $actor = Auth::user();

        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(1, min($perPage, 100));

        $q = trim((string) $request->query('q', ''));

        $sort = (string) $request->query('sort', 'name');
        $dir  = strtolower((string) $request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $withTrashed = (bool) ((int) $request->query('with_trashed', 0));
        $onlyTrashed = (bool) ((int) $request->query('only_trashed', 0));

        $allowedSorts = ['name', 'email', 'role', 'created_at'];
        if (!in_array($sort, $allowedSorts, true)) $sort = 'name';

        $query = User::query()
            ->with([
                'role:id,name',
                // ✅ your profiles table has 'avatar' (from ProfileController)
                'profile:id,user_id,avatar',
            ]);

        if ($onlyTrashed) {
            $query->onlyTrashed();
        } elseif ($withTrashed) {
            $query->withTrashed();
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhereHas('role', fn ($r) => $r->where('name', 'like', "%{$q}%"));
            });
        }

        // Sort (role requires join)
        if ($sort === 'role') {
            $query->leftJoin('roles', 'roles.id', '=', 'users.role_id')
                ->select('users.*')
                ->orderBy('roles.name', $dir);
        } else {
            $query->orderBy($sort, $dir);
        }

        $paged = $query->paginate($perPage);

        $paged->getCollection()->transform(function (User $u) use ($actor) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,

                'role' => $u->role ? [
                    'id' => $u->role->id,
                    'name' => $u->role->name,
                ] : null,

                'avatar_url' => $this->avatarUrl($u),
                'avatar_fallback' => $this->avatarFallback($u),

                'deleted_at' => $u->deleted_at,
                'created_at' => $u->created_at,
                'updated_at' => $u->updated_at,

                'can_delete' => $this->canDelete($actor, $u),
            ];
        });

        return response()->json($paged);
    }

    /**
     * API: POST /api/users
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'password' => ['required', 'string', 'min:6'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ]);

        // password hashed by model cast ('hashed')
        $user = User::create($validated);
        $user->load(['role:id,name', 'profile:id,user_id,avatar']);

        return response()->json([
            'message' => 'Created',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ? ['id' => $user->role->id, 'name' => $user->role->name] : null,
                'avatar_url' => $this->avatarUrl($user),
                'avatar_fallback' => $this->avatarFallback($user),
            ],
        ], 201);
    }

    /**
     * API: GET /api/users/{user}
     */
    public function show(User $user)
    {
        $user->load(['role:id,name', 'profile:id,user_id,avatar']);

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ? ['id' => $user->role->id, 'name' => $user->role->name] : null,
                'avatar_url' => $this->avatarUrl($user),
                'avatar_fallback' => $this->avatarFallback($user),
                'deleted_at' => $user->deleted_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ]);
    }

    /**
     * API: PATCH /api/users/{user}
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes', 'required', 'email', 'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at')->ignore($user->id),
            ],
            'password' => ['sometimes', 'nullable', 'string', 'min:6'],
            'role_id' => ['sometimes', 'required', 'integer', 'exists:roles,id'],
        ]);

        // don’t overwrite password with empty string
        if (array_key_exists('password', $validated) && !$validated['password']) {
            unset($validated['password']);
        }

        $user->update($validated);
        $user->load(['role:id,name', 'profile:id,user_id,avatar']);

        return response()->json([
            'message' => 'Updated',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ? ['id' => $user->role->id, 'name' => $user->role->name] : null,
                'avatar_url' => $this->avatarUrl($user),
                'avatar_fallback' => $this->avatarFallback($user),
            ],
        ]);
    }

    /**
     * API: DELETE /api/users/{user}
     * Rules:
     * - cannot delete self
     * - cannot delete user with same role_id
     */
    public function destroy(User $user)
    {
        $actor = Auth::user();

        if ($actor->id === $user->id) {
            return response()->json([
                'message' => 'You cannot delete your own account while logged in.',
            ], 403);
        }

        if ((int) $actor->role_id === (int) $user->role_id) {
            return response()->json([
                'message' => 'You cannot delete a user with the same role.',
            ], 403);
        }

        $user->delete();

        return response()->json(['message' => 'Deleted']);
    }

    /**
     * API: POST /api/users/{id}/restore
     */
    public function restore($id)
    {
        $actor = Auth::user();

        $user = User::onlyTrashed()->findOrFail($id);

        if ($actor->id === $user->id) {
            return response()->json(['message' => 'You cannot restore your own account from here.'], 403);
        }

        if ((int) $actor->role_id === (int) $user->role_id) {
            return response()->json(['message' => 'You cannot restore a user with the same role.'], 403);
        }

        $user->restore();

        return response()->json(['message' => 'Restored']);
    }

    // ---------------- Helpers ----------------

    private function canDelete(?User $actor, User $target): bool
    {
        if (!$actor) return false;
        if ($actor->id === $target->id) return false;
        if ((int) $actor->role_id === (int) $target->role_id) return false;
        return true;
    }

    /**
     * Avatar priority:
     * 1) profile.avatar (your ProfileController stores '/storage/avatars/..' here)
     * 2) Gravatar identicon fallback
     */
    private function avatarUrl(User $user): string
    {
        $p = $user->profile;

        if ($p?->avatar) {
            $a = (string) $p->avatar;

            // if someone saved full URL
            if (str_starts_with($a, 'http://') || str_starts_with($a, 'https://')) {
                return $a;
            }

            // if saved like "/storage/avatars/..."
            return asset(ltrim($a, '/'));
        }

        $hash = md5(strtolower(trim((string) $user->email)));
        return "https://www.gravatar.com/avatar/{$hash}?d=identicon&s=128";
    }

    private function avatarFallback(User $user): string
    {
        $name = trim((string) $user->name);
        if ($name === '') return 'U';

        $parts = preg_split('/\s+/', $name) ?: [];
        $first = mb_substr($parts[0] ?? 'U', 0, 1);
        $last  = mb_substr($parts[count($parts)-1] ?? '', 0, 1);

        $initials = mb_strtoupper($first . $last);
        return $initials ?: 'U';
    }
}
