<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {
    }

    /**
     * @param array{email: string, password: string} $credentials
     * @throws ValidationException when credentials are wrong or the account is throttled
     */
    public function login(array $credentials, string $throttleKey): User
    {
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Try again in {$seconds} seconds.",
            ]);
        }

        $user = $this->users->findByEmail($credentials['email']);

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            // Deliberately identical whether the email doesn't exist or the
            // password is wrong — never tell a caller which one it was.
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        if ($user->currentMembership()?->blocked_at) {
            throw ValidationException::withMessages([
                'email' => 'Your access has been blocked by an administrator.',
            ]);
        }

        Auth::login($user);

        return $user;
    }

    /**
     * Explicitly targets the "web" guard — the "auth:sanctum" middleware
     * authenticates requests through Sanctum's own guard, a lightweight
     * RequestGuard that has no logout() of its own. The session itself is
     * always owned by the underlying "web" (session) guard, so that's what
     * actually needs logging out, plus invalidating the session and
     * rotating the CSRF token so nothing stale can be replayed.
     */
    public function logout(Request $request): void
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}