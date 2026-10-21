<?php

namespace Webkul\BagistoApi\Admin\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use Webkul\BagistoApi\Admin\DataGrids\ApiAuditDataGrid;
use Webkul\BagistoApi\Admin\Repositories\AdminApiAuditRepository;

class ApiAuditController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected AdminApiAuditRepository $auditRepository) {}

    /**
     * Display the API audit listing, or its datagrid data for an AJAX request.
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(ApiAuditDataGrid::class)->process();
        }

        return view('bagistoapi::api-audit.index');
    }

    /**
     * Display one audit row with the other changes of its request and the versions of its record.
     */
    public function view(int $id)
    {
        $audit = $this->auditRepository->findOrFail($id);

        return view('bagistoapi::api-audit.view', [
            'audit' => $audit,
            'siblings' => $this->auditRepository->siblingsOf($audit),
            'versions' => $this->auditRepository->versionsOf($audit),
        ]);
    }

    /**
     * Delete the selected audit rows.
     */
    public function massDestroy(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $deleted = $this->auditRepository->deleteMany($massDestroyRequest->input('indices'));

        return new JsonResponse([
            'message' => trans('bagistoapi::app.admin.api-audit.deleted', ['count' => $deleted]),
        ]);
    }

    /**
     * Delete the audit rows older than a number of days or a date.
     */
    public function destroyOlderThan(): RedirectResponse
    {
        $cutoff = $this->cleanupCutoff(request()->input('days'), request()->input('date'));

        if (! $cutoff) {
            session()->flash('error', trans('bagistoapi::app.admin.api-audit.cleanup-input-required'));

            return redirect()->route('admin.settings.api_audit.index');
        }

        $deleted = $this->auditRepository->deleteOlderThan($cutoff);

        session()->flash('success', trans('bagistoapi::app.admin.api-audit.deleted', ['count' => $deleted]));

        return redirect()->route('admin.settings.api_audit.index');
    }

    /**
     * The cutoff a cleanup deletes before, from a number of days or else a date.
     */
    protected function cleanupCutoff(mixed $days, mixed $date): ?Carbon
    {
        if (
            is_numeric($days)
            && (int) $days >= 0
        ) {
            return Carbon::now()->subDays((int) $days);
        }

        if (
            is_string($date)
            && strtotime($date) !== false
        ) {
            return Carbon::parse($date)->endOfDay();
        }

        return null;
    }
}
