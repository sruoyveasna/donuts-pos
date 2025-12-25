<?php
namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Notifications\PasswordOtpNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthController extends Controller
{
    private int $otpTtlInitialSeconds = 60;      // like sample :contentReference[oaicite:7]{index=7}
    private int $graceAfterVerifyMinutes = 10;   // like sample :contentReference[oaicite:8]{index=8}
    private int $maxAttempts = 5;               // like sample :contentReference[oaicite:9]{index=9}
    private int $maxResends  = 5;               // like sample :contentReference[oaicite:10]{index=10}

    // 1) Register start: send OTP, store pending, DO NOT create user
    public function register(Request $request)
    {
        $request->validate([
            'name'  => ['required','string','max:255'],
            'email' => ['required','email'],
        ]);

        $email = strtolower($request->email);

        if (User::where('email', $email)->exists()) {
            return response()->json(['message' => 'Email is already registered'], 422);
        }

        $code = (string) random_int(100000, 999999);
        $hash = Hash::make($code);

        DB::table('registration_otps')->where('email', $email)->delete();

        DB::table('registration_otps')->insert([
            'name'       => $request->name,
            'email'      => $email,
            'code_hash'  => $hash,
            'expires_at' => now()->addSeconds($this->otpTtlInitialSeconds),
            'attempts'   => 0,
            'resends'    => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $displayMinutes = max(1, (int) ceil($this->otpTtlInitialSeconds / 60));
        Notification::route('mail', $email)
            ->notify(new PasswordOtpNotification($code, 'register', $displayMinutes));

        return response()->json([
            'message' => 'We sent a 6-digit code to your email. Use it to set your password.',
        ], 200);
    }

    // 2) Forgot password: send OTP (generic response to avoid enumeration) :contentReference[oaicite:11]{index=11}
    public function forgot(Request $request)
    {
        $request->validate(['email' => ['required','email']]);
        $email = strtolower($request->email);

        $user = User::where('email', $email)->first();
        if ($user) {
            $this->issuePasswordResetOtp($email);
        }

        return response()->json(['message' => 'If the email exists, a 6-digit code was sent.']);
    }

    // 3) Resend OTP (registration or reset) :contentReference[oaicite:12]{index=12}
    public function resendOtp(Request $request)
    {
        $request->validate(['email' => ['required','email']]);
        $email = strtolower($request->email);

        // registration pending?
        $row = DB::table('registration_otps')->where('email', $email)->first();
        if ($row) {
            if ($row->resends >= $this->maxResends) {
                return response()->json(['message' => 'Resend limit reached'], 429);
            }

            $code = (string) random_int(100000, 999999);
            DB::table('registration_otps')->where('email', $email)->update([
                'code_hash'  => Hash::make($code),
                'expires_at' => now()->addSeconds($this->otpTtlInitialSeconds),
                'resends'    => $row->resends + 1,
                'updated_at' => now(),
            ]);

            $displayMinutes = max(1, (int) ceil($this->otpTtlInitialSeconds / 60));
            Notification::route('mail', $email)
                ->notify(new PasswordOtpNotification($code, 'register', $displayMinutes));

            return response()->json(['message' => 'OTP resent']);
        }

        // else: reset OTP for existing user
        $user = User::where('email', $email)->first();
        if ($user) {
            $this->issuePasswordResetOtp($email, true);
            return response()->json(['message' => 'OTP resent']);
        }

        // generic
        return response()->json(['message' => 'OTP sent if the email exists.']);
    }

    // 4) Verify OTP (extend expiry so user has time to set password) :contentReference[oaicite:13]{index=13}
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => ['required','email'],
            'code'  => ['required','digits:6'],
        ]);

        $email = strtolower($request->email);

        $row = DB::table('registration_otps')->where('email', $email)->first();
        $context = 'register';
        $table = 'registration_otps';

        if (! $row) {
            $row = DB::table('password_otps')->where('email', $email)->first();
            $context = 'password_reset';
            $table = 'password_otps';
        }

        $invalid = fn() => response()->json(['message' => 'Invalid or expired code'], 422);
        if (! $row) return $invalid();

        if (now()->greaterThan($row->expires_at)) {
            DB::table($table)->where('email', $email)->delete();
            return $invalid();
        }

        if (($row->attempts ?? 0) >= $this->maxAttempts) {
            return response()->json(['message' => 'Too many attempts. Try again later.'], 429);
        }

        if (! Hash::check($request->code, $row->code_hash)) {
            DB::table($table)->where('email', $email)->update([
                'attempts'   => ($row->attempts ?? 0) + 1,
                'updated_at' => now(),
            ]);
            return $invalid();
        }

        $newExpiry = now()->addMinutes($this->graceAfterVerifyMinutes);

        DB::table($table)->where('email', $email)->update([
            'expires_at' => $newExpiry,
            'attempts'   => 0,
            'updated_at' => now(),
        ]);

        return response()->json([
            'message'     => 'Code verified',
            'context'     => $context,
            'ttl_seconds' => now()->diffInSeconds($newExpiry),
        ]);
    }

    // 5) Set/Reset password with OTP:
    // - if user exists => reset
    // - else if pending registration exists => create user
    public function resetWithOtp(Request $request)
    {
        $request->validate([
            'email'    => ['required','email'],
            'code'     => ['required','digits:6'],
            'password' => ['required','confirmed', PasswordRule::min(6)], // keep your min(6) or make stronger
        ]);

        $email = strtolower($request->email);

        // A) existing user => password reset
        $user = User::where('email', $email)->first();
        if ($user) {
            $row = DB::table('password_otps')->where('email', $email)->first();
            $invalid = fn() => response()->json(['message' => 'Invalid or expired code'], 422);

            if (! $row || now()->greaterThan($row->expires_at)) {
                DB::table('password_otps')->where('email', $email)->delete();
                return $invalid();
            }
            if (($row->attempts ?? 0) >= $this->maxAttempts) {
                return response()->json(['message' => 'Too many attempts. Try again later.'], 429);
            }
            if (! Hash::check($request->code, $row->code_hash)) {
                DB::table('password_otps')->where('email', $email)->update([
                    'attempts' => ($row->attempts ?? 0) + 1,
                    'updated_at' => now(),
                ]);
                return $invalid();
            }

            $user->password = Hash::make($request->password);
            $user->remember_token = Str::random(60);
            $user->save();

            // revoke tokens
            $user->tokens()->delete();

            DB::table('password_otps')->where('email', $email)->delete();

            return response()->json(['message' => 'Password reset successful. You can log in now.']);
        }

        // B) pending registration => create user now
        $pending = DB::table('registration_otps')->where('email', $email)->first();
        $invalid = fn() => response()->json(['message' => 'Invalid or expired code'], 422);

        if (! $pending || now()->greaterThan($pending->expires_at)) {
            DB::table('registration_otps')->where('email', $email)->delete();
            return $invalid();
        }
        if (($pending->attempts ?? 0) >= $this->maxAttempts) {
            return response()->json(['message' => 'Too many attempts. Try again later.'], 429);
        }
        if (! Hash::check($request->code, $pending->code_hash)) {
            DB::table('registration_otps')->where('email', $email)->update([
                'attempts' => ($pending->attempts ?? 0) + 1,
                'updated_at' => now(),
            ]);
            return $invalid();
        }

        // Race-safe re-check
        if (User::where('email', $email)->exists()) {
            DB::table('registration_otps')->where('email', $email)->delete();
            return response()->json(['message' => 'This email is already registered.'], 422);
        }

        // Your default role logic (Cashier)
        $roleId = Role::where('name','Cashier')->value('id') ?? Role::query()->value('id');

        $newUser = User::create([
            'name'     => $pending->name,
            'email'    => $email,
            'password' => Hash::make($request->password),
            'role_id'  => $roleId,
        ]);

        DB::table('registration_otps')->where('email', $email)->delete();

        // OPTIONAL: auto-login + token like your current controller
        Auth::login($newUser);
        $request->session()->regenerate();
        $token = $newUser->createToken('web')->plainTextToken;

        return response()->json([
            'message' => 'Account created',
            'token'   => $token,
            'user'    => $newUser->only('id','name','email','role_id'),
        ], 201);
    }

    // keep your login/logout/me (or adjust)
    public function login(Request $request)
    {
        $cred = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required'],
            'remember' => ['sometimes','boolean'],
        ]);

        $remember = (bool) $request->boolean('remember');

        if (! Auth::attempt(['email'=>$cred['email'], 'password'=>$cred['password']], $remember)) {
            throw ValidationException::withMessages(['email' => ['Invalid credentials.']]);
        }

        $request->session()->regenerate();

        $user = $request->user()->load('role');
        $token = $user->createToken('web')->plainTextToken;

        return response()->json([
            'message' => 'Logged in',
            'token'   => $token,
            'user'    => $user->only('id','name','email','role_id'),
            'role'    => $user->role?->name,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request)
    {
        $u = $request->user()->load('role');
        return response()->json([
            'user' => $u->only('id','name','email','role_id'),
            'role' => $u->role?->name,
        ]);
    }

    private function issuePasswordResetOtp(string $email, bool $rotateIfExists = false): void
    {
        $user = User::where('email', $email)->first();
        if (! $user) return;

        $existing = DB::table('password_otps')->where('email', $email)->first();

        $code = (string) random_int(100000, 999999);
        $hash = Hash::make($code);

        $displayMinutes = max(1, (int) ceil($this->otpTtlInitialSeconds / 60));

        if ($existing && $rotateIfExists) {
            if (($existing->resends ?? 0) >= $this->maxResends) {
                return; // or throw 429 in caller
            }
            DB::table('password_otps')->where('email', $email)->update([
                'code_hash'  => $hash,
                'expires_at' => now()->addSeconds($this->otpTtlInitialSeconds),
                'resends'    => ($existing->resends ?? 0) + 1,
                'updated_at' => now(),
            ]);
        } else {
            DB::table('password_otps')->where('email', $email)->delete();
            DB::table('password_otps')->insert([
                'email'      => $email,
                'code_hash'  => $hash,
                'expires_at' => now()->addSeconds($this->otpTtlInitialSeconds),
                'attempts'   => 0,
                'resends'    => 0,
                'purpose'    => 'password_reset',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $user->notify(new PasswordOtpNotification($code, 'password_reset', $displayMinutes));
    }
}
