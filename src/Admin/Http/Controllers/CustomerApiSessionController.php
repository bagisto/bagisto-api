<?php

namespace Webkul\BagistoApi\Admin\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Webkul\BagistoApi\Admin\DataGrids\CustomerApiSessionDataGrid;
use Webkul\BagistoApi\Repositories\CustomerApiSessionRepository;
use Webkul\BagistoApi\Services\CustomerAuth\CustomerApiAuthService;
use Webkul\Customer\Repositories\CustomerRepository;

class CustomerApiSessionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected CustomerApiSessionRepository $sessionRepository,
        protected CustomerRepository $customerRepository,
        protected CustomerApiAuthService $authService,
    ) {}

    /**
     * Display the customer session listing, or its datagrid data for an AJAX request.
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(CustomerApiSessionDataGrid::class)->process();
        }

        return view('bagistoapi::customer-api-sessions.index');
    }

    /**
     * End one customer session, whose access and refresh tokens stop working at once.
     */
    public function destroy(int $id): JsonResponse
    {
        $session = $this->sessionRepository->findWithCustomer($id);

        abort_unless($session, 404);

        if (! $session->isActive()) {
            return new JsonResponse([
                'message' => trans('bagistoapi::app.admin.api-sessions.errors.not-active'),
            ], 400);
        }

        $this->authService->revokeSessionAsAdmin(auth()->guard('admin')->user(), $session, request());

        return new JsonResponse([
            'message' => trans('bagistoapi::app.graphql.auth.session-revoked'),
        ]);
    }

    /**
     * End every live session of a customer, from the listing or the customer page.
     */
    public function revokeAll(int $customerId): JsonResponse|RedirectResponse
    {
        $customer = $this->customerRepository->find($customerId);

        abort_unless($customer, 404);

        $revoked = $this->authService->revokeAllAsAdmin(auth()->guard('admin')->user(), $customer, request());

        $message = trans('bagistoapi::app.admin.auth.sessions.revoked-for-admin', ['count' => $revoked]);

        if (request()->expectsJson()) {
            return new JsonResponse(['message' => $message]);
        }

        session()->flash('success', $message);

        return redirect()->back();
    }
}
