<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * GET /api/profile
     * Return current user + profile data
     */
    public function show(Request $request)
    {
        $user = $request->user()->load('profile');

        return response()->json([
            'status' => 'ok',
            'user'   => $user,
        ]);
    }

    /**
     * POST /api/profile
     * Update current user profile
     */
    public function update(Request $request)
    {
        $user = $request->user(); // auth:sanctum

        $data = $request->validate([
            // main user fields
            'name'      => ['required', 'string', 'max:100'],

            // profile fields (from your Profile model)
            'phone'     => ['nullable', 'string', 'max:30'],
            'gender'    => ['nullable', Rule::in(['none', 'male', 'female', 'other'])],
            'birthdate' => ['nullable', 'date'],
            'address'   => ['nullable', 'string', 'max:255'],

            // avatar is now an IMAGE file (max ~2MB)
            'avatar'    => ['nullable', 'image', 'max:2048'],
        ]);

        // 1) Update user
        $user->name = $data['name'];
        $user->save();

        // 2) Build profile data
        $profileData = [
            'phone'     => $data['phone']     ?? null,
            'gender'    => $data['gender']    ?? null,
            'birthdate' => $data['birthdate'] ?? null,
            'address'   => $data['address']   ?? null,
        ];

        // 3) If avatar file uploaded, store it and save path
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');

            // store in storage/app/public/avatars
            $path = $file->store('avatars', 'public');

            // make it web-accessible: /storage/avatars/...
            $profileData['avatar'] = '/storage/' . $path;
        }

        // 4) Create or update profile for this user
        $user->profile()->updateOrCreate(
            [], // where; relationship will fill user_id automatically
            $profileData
        );

        return response()->json([
            'status'  => 'ok',
            'message' => 'Profile updated.',
            'user'    => $user->load('profile'),
        ]);
    }
}
