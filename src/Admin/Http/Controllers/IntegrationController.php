<?php

namespace Webkul\BagistoApi\Admin\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Webkul\BagistoApi\Admin\DataGrids\IntegrationDataGrid;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Http\Requests\IntegrationStoreRequest;
use Webkul\BagistoApi\Admin\Http\Requests\IntegrationUpdateRequest;
use Webkul\BagistoApi\Admin\Mail\AdminTokenNotification;
use Webkul\BagistoApi\Admin\Models\AdminPersonalAccessToken;
use Webkul\BagistoApi\Admin\Services\AdminTokenService;

class IntegrationController extends Controller
{
    /**
     * Create a new controller instance, guarding every action behind the integration toggle and its ACL key.
     */
    public function __construct(protected AdminTokenService $tokenService)
    {
        $this->middleware(function ($request, $next) {
            $enabled = core()->getConfigData('api.integration.settings.enabled');

            abort_unless($enabled === null ? true : (bool) $enabled, 404);

            return $next($request);
        })->except(['confirmRevokeViaEmail', 'revokeViaEmail']);

        $this->middleware(function ($request, $next) {
            abort_unless(bouncer()->hasPermission($this->permissionFor($request->route()?->getActionMethod())), 401);

            return $next($request);
        })->except(['confirmRevokeViaEmail', 'revokeViaEmail']);
    }

    /**
     * Redirect to the integration token listing.
     */
    public function redirectToTokens()
    {
        return redirect()->route('admin.integration.token.index');
    }

    /**
     * Display the integration token listing, or its datagrid data for an AJAX request.
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(IntegrationDataGrid::class)->process();
        }

        return view('bagistoapi::integration.index');
    }

    /**
     * Show the create form with the admins the signed-in admin may manage and that hold no active token.
     */
    public function create()
    {
        $availableAdmins = $this->tokenService->adminsWithoutActiveToken()
            ->filter(fn ($admin) => $this->canManageAdmin($admin))
            ->values();
        $aclTree = config('acl');

        return view('bagistoapi::integration.create', compact('availableAdmins', 'aclTree'));
    }

    /**
     * Store a new draft token and redirect to its edit page.
     */
    public function store(IntegrationStoreRequest $request)
    {
        $token = $this->tokenService->createDraft(
            $request->validated(),
            auth()->guard('admin')->id()
        );

        session()->flash('success', trans('bagistoapi::app.integration.messages.draft-created'));

        return redirect()->route('admin.integration.edit', $token->id);
    }

    /**
     * Show the edit page of a token the signed-in admin may manage.
     */
    public function edit(int $id)
    {
        $token = AdminPersonalAccessToken::with(['admin.role'])->findOrFail($id);

        $this->abortUnlessManageable($token);

        $availableAdmins = collect([$token->admin])->filter();
        $aclTree = config('acl');
        $plainToken = session('integration_plain_token');

        return view('bagistoapi::integration.edit', compact(
            'token',
            'availableAdmins',
            'aclTree',
            'plainToken'
        ));
    }

    /**
     * Update the metadata of a draft or active token; revoked and regenerated tokens cannot be edited.
     */
    public function update(IntegrationUpdateRequest $request, int $id)
    {
        $token = AdminPersonalAccessToken::findOrFail($id);

        $this->abortUnlessManageable($token);

        if (
            $token->isRevoked()
            || $token->isRegenerated()
        ) {
            session()->flash('warning', trans('bagistoapi::app.integration.messages.cannot-edit-historic'));

            return redirect()->route('admin.integration.edit', $token->id);
        }

        $data = $request->validated();

        if ($token->isDraft()) {
            $this->tokenService->updateDraftMetadata($token, $data);
        } else {
            $this->tokenService->updateActiveMetadata($token, $data);
        }

        session()->flash('success', trans('bagistoapi::app.integration.messages.updated'));

        return redirect()->route('admin.integration.token.index');
    }

    /**
     * Generate the plain token for a draft, notify its owner and flash the plain token once.
     */
    public function generate(int $id)
    {
        $token = AdminPersonalAccessToken::findOrFail($id);

        $this->abortUnlessManageable($token);

        if (! $token->isDraft()) {
            session()->flash('warning', trans('bagistoapi::app.integration.messages.generate-only-draft'));

            return redirect()->route('admin.integration.edit', $token->id);
        }

        $overrides = request()->only([
            'expires_mode',
            'expires_at',
            'rate_min_mode',
            'rate_limit_per_minute',
            'rate_day_mode',
            'rate_limit_per_day',
            'ip_mode',
            'allowed_ips',
            'allowed_ips_text',
        ]);

        $result = $this->tokenService->generate($token, $overrides);

        $this->notifyOwner($result['token'], AdminTokenNotification::EVENT_GENERATED);

        session()->flash('success', trans('bagistoapi::app.integration.messages.generated'));
        session()->flash('integration_plain_token', $result['plain_text']);

        return redirect()->route('admin.integration.edit', $result['token']->id);
    }

    /**
     * Regenerate an active token, notify its owner and flash the new plain token once.
     */
    public function regenerate(int $id)
    {
        $oldToken = AdminPersonalAccessToken::findOrFail($id);

        $this->abortUnlessManageable($oldToken);

        if (! $oldToken->isActive()) {
            session()->flash('warning', trans('bagistoapi::app.integration.messages.regenerate-only-active'));

            return redirect()->route('admin.integration.edit', $oldToken->id);
        }

        $result = $this->tokenService->regenerate($oldToken, (int) auth()->guard('admin')->id());

        $this->notifyOwner($result['token'], AdminTokenNotification::EVENT_REGENERATED);

        session()->flash('success', trans('bagistoapi::app.integration.messages.regenerated'));
        session()->flash('integration_plain_token', $result['plain_text']);

        return redirect()->route('admin.integration.edit', $result['token']->id);
    }

    /**
     * Revoke a token that is still live and notify its owner.
     */
    public function destroy(int $id): JsonResponse
    {
        $token = AdminPersonalAccessToken::findOrFail($id);

        $this->abortUnlessManageable($token);

        if (
            $token->isRevoked()
            || $token->isRegenerated()
        ) {
            return new JsonResponse([
                'message' => trans('bagistoapi::app.integration.messages.already-inactive'),
            ], 400);
        }

        $this->tokenService->revoke($token, (int) auth()->guard('admin')->id());

        $this->notifyOwner($token->fresh(), AdminTokenNotification::EVENT_REVOKED);

        return new JsonResponse([
            'message' => trans('bagistoapi::app.integration.messages.revoked'),
        ]);
    }

    /**
     * Show the revoke confirmation page; nothing is revoked on GET.
     */
    public function confirmRevokeViaEmail(int $id)
    {
        $token = AdminPersonalAccessToken::with('admin')->findOrFail($id);

        if (
            $token->isRevoked()
            || $token->isRegenerated()
        ) {
            return view('bagistoapi::integration.revoke-confirmation', [
                'token' => $token,
                'alreadyInactive' => true,
                'confirmUrl' => null,
            ]);
        }

        return view('bagistoapi::integration.revoke-confirmation', [
            'token' => $token,
            'alreadyInactive' => false,
            'confirmUrl' => URL::temporarySignedRoute(
                'admin.integration.revoke_via_email.perform',
                now()->addHour(),
                ['id' => $token->id]
            ),
        ]);
    }

    /**
     * Revoke the token after the confirmation form is submitted.
     */
    public function revokeViaEmail(int $id)
    {
        $token = AdminPersonalAccessToken::with('admin')->findOrFail($id);

        if (
            $token->isRevoked()
            || $token->isRegenerated()
        ) {
            return view('bagistoapi::integration.revoke-confirmation', [
                'token' => $token,
                'alreadyInactive' => true,
                'confirmUrl' => null,
            ]);
        }

        $this->tokenService->revoke($token, (int) $token->admin_id);

        return view('bagistoapi::integration.revoke-confirmation', [
            'token' => $token->fresh(),
            'alreadyInactive' => false,
            'confirmUrl' => null,
        ]);
    }

    /**
     * Map a controller action to the ACL key it requires.
     */
    protected function permissionFor(?string $action): string
    {
        return match ($action) {
            'create', 'store' => 'integration.create',
            'update' => 'integration.edit',
            'destroy' => 'integration.delete',
            'generate' => 'integration.generate',
            'regenerate' => 'integration.regenerate',
            default => 'integration.view',
        };
    }

    /**
     * Whether the signed-in admin holds every permission of the given admin's role.
     */
    protected function canManageAdmin(?object $admin): bool
    {
        return AdminAuthHelper::canGrantRole(auth()->guard('admin')->user(), $admin?->role);
    }

    /**
     * Abort unless the signed-in admin may manage the admin who owns the token.
     */
    protected function abortUnlessManageable(AdminPersonalAccessToken $token): void
    {
        $token->loadMissing('admin.role');

        abort_unless($this->canManageAdmin($token->admin), 403);
    }

    /**
     * Queue the token event email to the owning admin, logging any failure instead of throwing.
     */
    protected function notifyOwner(AdminPersonalAccessToken $token, string $event): void
    {
        $token->loadMissing('admin');

        if (
            ! $token->admin
            || empty($token->admin->email)
        ) {
            return;
        }

        try {
            Mail::queue(new AdminTokenNotification($token, $event, request()->ip()));
        } catch (\Throwable $exception) {
            Log::error('Admin token notification failed: '.$exception->getMessage(), [
                'token_id' => $token->id,
                'event' => $event,
            ]);
        }
    }
}
