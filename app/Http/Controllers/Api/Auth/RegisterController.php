<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function __construct(
        private readonly RegistrationService $registrationService,
    ) {
    }

    /**
     * Thin by design: validation lives in RegisterRequest, the actual work
     * (create user + organization + owner membership, in a transaction)
     * lives in RegistrationService. This method only wires the two together
     * and shapes the HTTP response.
     */
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $user = $this->registrationService->register($request->validated());

        Auth::login($user);

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }
}
