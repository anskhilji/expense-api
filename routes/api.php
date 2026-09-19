<?php

use App\Http\Controllers\Api\Auth\EmailVerificationController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\BudgetController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\IncomeController;
use App\Http\Controllers\Api\InvitationController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/login', fn () => response()->json(['message' => 'Unauthenticated.'], 401))->name('login');
Route::post('/register', RegisterController::class);
Route::post('/login', [LoginController::class, 'store']);

// Forgot / reset password — public, no session required.
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
Route::post('/reset-password', [PasswordResetController::class, 'reset']);

// Email verification link the user clicks from their inbox — public
// (the signature itself, not a login session, is what proves it's valid).
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

// Public — lets the frontend show invite details before the person signs in/up.
// Deliberately outside the auth:sanctum group (that middleware would 401 a
// guest instead of letting them create an account here) — but $request->user()
// inside the controller still resolves correctly for an already-signed-in
// visitor, because `php artisan install:api` puts Sanctum's
// EnsureFrontendRequestsAreStateful middleware on the whole api group, which
// is what makes the session cookie readable on every /api/* route.
Route::get('/invitations/{token}', [InvitationController::class, 'show']);
Route::post('/invitations/{token}/accept', [InvitationController::class, 'accept']);

Route::middleware(['auth:sanctum', 'blocked'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy']);

    Route::get('/user', fn (Request $request) => new UserResource($request->user()->load('currentOrganization')));

    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1');

    // --- Organization & members (Admin/Owner only) ---
    Route::get('/org/members', [MemberController::class, 'index'])->middleware('permission:organization.manage');
    Route::patch('/org/members/{user}', [MemberController::class, 'update'])->middleware('permission:organization.manage');
    Route::delete('/org/members/{user}', [MemberController::class, 'destroy'])->middleware('permission:organization.manage');
    Route::post('/org/members/{user}/block', [MemberController::class, 'block'])->middleware('permission:organization.manage');
    Route::post('/org/members/{user}/unblock', [MemberController::class, 'unblock'])->middleware('permission:organization.manage');
    Route::post('/org/invitations', [InvitationController::class, 'store'])->middleware('permission:organization.manage');

    // --- Categories ---
    Route::get('/categories', [CategoryController::class, 'index'])->middleware('permission:expenses.view');
    Route::post('/categories', [CategoryController::class, 'store'])->middleware('permission:categories.manage');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->middleware('permission:categories.manage');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->middleware('permission:categories.manage');

    // --- Incomes ---
    Route::get('/incomes', [IncomeController::class, 'index'])->middleware('permission:incomes.view');
    Route::post('/incomes', [IncomeController::class, 'store'])->middleware('permission:incomes.create');
    Route::put('/incomes/{income}', [IncomeController::class, 'update'])->middleware('permission:incomes.edit');
    Route::delete('/incomes/{income}', [IncomeController::class, 'destroy'])->middleware('permission:incomes.delete');

    // --- Budgets (envelope allocations) ---
    Route::get('/budgets', [BudgetController::class, 'index'])->middleware('permission:expenses.view');
    Route::post('/budgets', [BudgetController::class, 'store'])->middleware('permission:budgets.manage');
    Route::put('/budgets', [BudgetController::class, 'update'])->middleware('permission:budgets.manage');

    // --- Expenses ---
    Route::get('/expenses', [ExpenseController::class, 'index'])->middleware('permission:expenses.view');
    Route::post('/expenses', [ExpenseController::class, 'store'])->middleware('permission:expenses.create');
    Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->middleware('permission:expenses.edit');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->middleware('permission:expenses.delete');

    // --- Reports ---
    Route::get('/reports/summary', [ReportController::class, 'summary'])->middleware('permission:reports.view');
    Route::get('/reports/ledger', [ReportController::class, 'ledger'])->middleware('permission:reports.view');
    Route::get('/reports/export', [ReportController::class, 'export'])->middleware('permission:reports.export');
});
