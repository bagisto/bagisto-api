<?php

namespace Webkul\BagistoApi\Admin\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Webkul\BagistoApi\Admin\DataGrids\AuditHistoryDataGrid;
use Webkul\BagistoApi\Admin\Models\AdminApiAudit;

/**
 * Integration → History admin screen: browse / inspect / clean up the
 * admin-API audit trail.
 */
class AuditHistoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $enabled = core()->getConfigData('api.integration.settings.enabled');

            abort_unless($enabled === null ? true : (bool) $enabled, 404);

            return $next($request);
        });
    }

    public function index()
    {
        if (request()->ajax()) {
            return datagrid(AuditHistoryDataGrid::class)->process();
        }

        return view('bagistoapi::integration.history.index');
    }

    public function view(int $id)
    {
        $audit = AdminApiAudit::findOrFail($id);

        $siblings = AdminApiAudit::where('history_id', $audit->history_id)
            ->where('id', '!=', $audit->id)
            ->orderBy('id')
            ->get();

        $versions = $audit->auditable_type && $audit->auditable_id
            ? AdminApiAudit::where('auditable_type', $audit->auditable_type)
                ->where('auditable_id', $audit->auditable_id)
                ->orderByDesc('version_id')
                ->get()
            : collect();

        return view('bagistoapi::integration.history.view', compact('audit', 'siblings', 'versions'));
    }

    public function massDestroy(): JsonResponse
    {
        $this->authorizeDelete();

        $indices = (array) request()->input('indices', []);

        $deleted = $indices
            ? AdminApiAudit::whereIn('id', $indices)->delete()
            : 0;

        return new JsonResponse([
            'message' => trans('bagistoapi::app.integration.history.deleted', ['count' => $deleted]),
        ]);
    }

    /**
     * Delete logs older than a cutoff — either a date or N days. Web form
     * action: redirects back with a flash message.
     */
    public function destroyOlderThan()
    {
        $this->authorizeDelete();

        $days = request()->input('days');
        $date = request()->input('date');

        if ($days !== null && is_numeric($days) && (int) $days >= 0) {
            $cutoff = Carbon::now()->subDays((int) $days);
        } elseif ($date) {
            $cutoff = Carbon::parse($date)->endOfDay();
        } else {
            session()->flash('error', trans('bagistoapi::app.integration.history.cleanup-input-required'));

            return redirect()->route('admin.integration.history.index');
        }

        $deleted = AdminApiAudit::where('created_at', '<', $cutoff)->delete();

        session()->flash('success', trans('bagistoapi::app.integration.history.deleted', ['count' => $deleted]));

        return redirect()->route('admin.integration.history.index');
    }

    protected function authorizeDelete(): void
    {
        abort_unless(bouncer()->hasPermission('integration.history.delete'), 401);
    }
}
