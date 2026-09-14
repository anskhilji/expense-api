<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hand-rolled route-level authorization — no third-party package.
 *
 * Usage in routes/api.php:
 *   Route::post('/expenses', ExpenseController::class)->middleware('permission:expenses.create');
 *
 * Checks the permission against the signed-in user's role in their CURRENT
 * organization (User::hasPermission walks user -> currentMembership -> role
 * -> permissions). This is the server-side enforcement; the frontend's
 * RoleGate component only hides buttons for a nicer UI, it is never trusted
 * on its own — every request is re-checked here regardless of what the UI
 * showed.
 */
class EnsureHasPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasPermission($permission)) {
            abort(403, "You don't have permission to do that ({$permission}).");
        }

        return $next($request);
    }
}
