<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {
    }

    public function store(LoginRequest $request): UserResource
    {
        $user = $this->authService->login(
            $request->only('email', 'password'),
            $request->throttleKey(),
        );

        $request->session()->regenerate();

        return new UserResource($user->load('currentOrganization'));
    }

    public function destroy(Request $request): JsonResponse
    {
        $this->authService->logout($request);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out.']);
    }
}
