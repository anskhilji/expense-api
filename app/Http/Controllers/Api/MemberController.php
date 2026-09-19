<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organizations\ChangeMemberRoleRequest;
use App\Http\Resources\MemberResource;
use App\Services\Organizations\MemberService;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function __construct(
        private readonly MemberService $members,
    ) {
    }

    /** Admin-only (permission:organization.manage) — every user in the current org and their role. */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);

        $result = $this->members->listMembersPaginated($request->user()->currentOrganization, $search, $perPage);

        return [
            'data' => MemberResource::collection($result['data']),
            'has_more' => $result['has_more'],
            'next_page' => $result['next_page'],
        ];
    }

    public function update(ChangeMemberRoleRequest $request, int $user)
    {
        $this->members->changeRole($request->user()->currentOrganization, $user, $request->string('role')->value());

        return response()->json(['message' => 'Role updated.']);
    }

    public function destroy(Request $request, int $user)
    {
        $this->members->removeMember($request->user()->currentOrganization, $user);

        return response()->noContent();
    }

    public function block(Request $request, int $user)
    {
        $this->members->blockMember($request->user()->currentOrganization, $user);

        return response()->json(['message' => 'Member blocked.']);
    }

    public function unblock(Request $request, int $user)
    {
        $this->members->unblockMember($request->user()->currentOrganization, $user);

        return response()->json(['message' => 'Member unblocked.']);
    }
}
