<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Route-model binding only proves a record's ID exists, not that it
     * belongs to the caller's organization — every controller that
     * receives a bound record for an org-scoped model calls this before
     * touching it, so one household can never read or edit another's data
     * by guessing an ID. 404, not 403: existence of another org's record
     * is not information this response should leak either.
     */
    protected function authorizeOrgOwnership(Request $request, $model): void
    {
        abort_unless($model->organization_id === $request->user()->current_org_id, 404);
    }
}
