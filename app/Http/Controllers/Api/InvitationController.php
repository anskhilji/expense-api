<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organizations\AcceptInvitationRequest;
use App\Http\Requests\Organizations\InviteMemberRequest;
use App\Http\Resources\UserResource;
use App\Services\Organizations\InvitationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvitationController extends Controller
{
    public function __construct(
        private readonly InvitationService $invitations,
    ) {
    }

    /** Admin-only (permission:organization.manage). */
    public function store(InviteMemberRequest $request)
    {
        $invitation = $this->invitations->invite(
            $request->user()->currentOrganization,
            $request->string('email')->value(),
            $request->string('role')->value(),
        );

        // No mail transport assumed for a home-network setup — return the
        // token/link so the Admin can share it directly (WhatsApp, SMS,
        // handing over the phone). Wiring up Mail::send is a drop-in swap
        // later if you add outbound email.
        return response()->json([
            'email' => $invitation->email,
            'role' => $request->string('role')->value(),
            'invite_link' => rtrim(config('app.frontend_url', config('app.url')), '/')."/invitations/{$invitation->token}",
        ], 201);
    }

    /** Public — lets the frontend show "You've been invited to <org> as <role>" before asking to sign in/up. */
    public function show(string $token)
    {
        $invitation = $this->invitations->findValidByToken($token);

        return response()->json([
            'organization_name' => $invitation->organization->name,
            'email' => $invitation->email,
            'role' => $invitation->role->slug,
        ]);
    }

    public function accept(AcceptInvitationRequest $request, string $token)
    {
        $wasAlreadySignedIn = (bool) $request->user();
        $newAccountData = $wasAlreadySignedIn ? null : $request->only('name', 'password');

        $user = $this->invitations->accept($token, $request->user(), $newAccountData);

        // If the person didn't already have a session (new account created
        // inline, or an existing account they weren't logged into), sign
        // them in now so the response's session cookie is immediately usable.
        if (! $wasAlreadySignedIn) {
            Auth::login($user);
            $request->session()->regenerate();
        }

        return new UserResource($user);
    }
}
