<?php

namespace Webkul\BagistoApi\Admin\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Webkul\BagistoApi\Admin\DataGrids\ApiSessionDataGrid;
use Webkul\BagistoApi\Admin\Repositories\AdminApiSessionRepository;
use Webkul\BagistoApi\Admin\Services\AdminApiAuthService;
use Webkul\User\Repositories\AdminRepository;

class ApiSessionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected AdminApiSessionRepository $sessionRepository,
        protected AdminRepository $adminRepository,
        protected AdminApiAuthService $authService,
    ) {}

    /**
     * Display the API session listing, or its datagrid data for an AJAX request.
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(ApiSessionDataGrid::class)->process();
        }

        return view('bagistoapi::api-sessions.index');
    }

    /**
     * Revoke one session, whose access and refresh tokens stop working at once.
     */
    public function destroy(int $id): JsonResponse
    {
        $session = $this->sessionRepository->findWithAdmin($id);

        abort_unless($session, 404);

        if (! $session->isActive()) {
            return new JsonResponse([
                'message' => trans('bagistoapi::app.admin.api-sessions.errors.not-active'),
            ], 400);
        }

        $this->authService->revokeSession(auth()->guard('admin')->user(), $session, request());

        return new JsonResponse([
            'message' => trans('bagistoapi::app.admin.auth.sessions.revoked'),
        ]);
    }

    /**
     * Revoke every live session of an admin.
     */
    public function revokeAll(int $adminId): JsonResponse
    {
        $admin = $this->adminRepository->find($adminId);

        abort_unless($admin, 404);

        $revoked = $this->authService->revokeAdminSessions(auth()->guard('admin')->user(), $admin, request());

        return new JsonResponse([
            'message' => trans('bagistoapi::app.admin.auth.sessions.revoked-for-admin', ['count' => $revoked]),
        ]);
    }
}
