<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Reports\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reports,
    ) {
    }

    public function summary(Request $request)
    {
        $month = Carbon::parse($request->input('month', now()->toDateString()));

        return $this->reports->summaryForMonth($request->user()->current_org_id, $month);
    }

    public function ledger(Request $request)
    {
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 10);

        return $this->reports->ledger($request->user()->current_org_id, $page, $perPage);
    }

    /**
     * Server-rendered PDF of one month's report, built from the exact same
     * ReportService::summaryForMonth the on-screen dashboard uses — so the
     * printed numbers can never drift from what's on screen.
     *
     * Requires `composer require barryvdh/laravel-dompdf` (see
     * README-INTEGRATION.md). If you'd rather avoid the extra dependency,
     * the `format=html` branch below returns a print-styled page instead —
     * the browser's own "Print to PDF" does the rest, with no package.
     */
    public function export(Request $request)
    {
        $month = Carbon::parse($request->input('month', now()->toDateString()));
        $summary = $this->reports->summaryForMonth($request->user()->current_org_id, $month);
        $organization = $request->user()->currentOrganization;

        $view = view('reports.month', [
            'organization' => $organization,
            'summary' => $summary,
            'month' => $month,
        ]);

        if ($request->input('format') === 'html') {
            return $view; // browser handles Ctrl+P / "Save as PDF" itself
        }

        $pdf = Pdf::loadHTML($view->render())->setPaper('a4');

        return $pdf->download("expense-report-{$summary['month']}.pdf");
    }
}
