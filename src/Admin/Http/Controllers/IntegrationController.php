<?php

namespace Webkul\BagistoApi\Admin\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Webkul\BagistoApi\Admin\DataGrids\IntegrationDataGrid;
use Webkul\BagistoApi\Admin\Http\Requests\IntegrationStoreRequest;
use Webkul\BagistoApi\Admin\Http\Requests\IntegrationUpdateRequest;
use Webkul\BagistoApi\Admin\Mail\AdminTokenNotification;
use Webkul\BagistoApi\Admin\Models\AdminPersonalAccessToken;
use Webkul\BagistoApi\Admin\Services\AdminTokenService;
use Webkul\User\Models\Admin;

class IntegrationController extends Controller
{
    public function __construct(protected AdminTokenService $tokenService)
    {
        $this->middleware(function ($request, $next) {
            $enabled = core()->getConfigData('api.integration.settings.enabled');

            abort_unless($enabled === null ? true : (bool) $enabled, 404);

            return $next($request);
        })->except('revokeViaEmail');
    }

    /**
     * Landing route for the Integration menu. Redirects to the Tokens screen,
     * which lives at its own `/integration/token` URL so the sidebar's
     * substring-based active-state does not also highlight Tokens when the
     * History sub-menu (`/integration/history`) is open.
     */
    public function redirectToTokens()
    {
        return redirect()->route('admin.integration.token.index');
    }

    public function index()
    {
        if (request()->ajax()) {
            return datagrid(IntegrationDataGrid::class)->process();
        }

        return view('bagistoapi::integration.index');
    }

    public function create()
    {
        $availableAdmins = $this->tokenService->adminsWithoutActiveToken();
        $aclTree = config('acl');

        return view('bagistoapi::integration.create', compact('availableAdmins', 'aclTree'));
    }

    public function store(IntegrationStoreRequest $request)
    {
        $token = $this->tokenService->createDraft(
            $request->validated(),
            auth()->guard('admin')->id()
        );

        session()->flash('success', trans('bagistoapi::app.integration.messages.draft-created'));

        return redirect()->route('admin.integration.edit', $token->id);
    }

    public function edit(int $id)
    {
        $token = AdminPersonalAccessToken::with(['admin.role'])->findOrFail($id);

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

    public function update(IntegrationUpdateRequest $request, int $id)
    {
        $token = AdminPersonalAccessToken::findOrFail($id);

        if ($token->isRevoked() || $token->isRegenerated()) {
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

    public function generate(int $id)
    {
        $token = AdminPersonalAccessToken::findOrFail($id);

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

    public function regenerate(int $id)
    {
        $oldToken = AdminPersonalAccessToken::findOrFail($id);

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

    public function destroy(int $id): JsonResponse
    {
        $token = AdminPersonalAccessToken::findOrFail($id);

        if ($token->isRevoked() || $token->isRegenerated()) {
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
     * Revoke a token via the signed, login-free link sent in lifecycle emails.
     *
     * The `signed` middleware on this route guarantees the link was issued by
     * us and has not expired. No admin session is required — the token owner
     * can act straight from their inbox.
     */
    public function revokeViaEmail(int $id)
    {
        $token = AdminPersonalAccessToken::with('admin')->findOrFail($id);

        if ($token->isRevoked() || $token->isRegenerated()) {
            return view('bagistoapi::integration.revoke-confirmation', [
                'token' => $token,
                'alreadyInactive' => true,
            ]);
        }

        $this->tokenService->revoke($token, (int) $token->admin_id);

        return view('bagistoapi::integration.revoke-confirmation', [
            'token' => $token->fresh(),
            'alreadyInactive' => false,
        ]);
    }

    /**
     * Email the token owner about a lifecycle event.
     *
     * The plaintext token is never included — only the event, metadata, and
     * (for generate/regenerate) a signed revoke link. Mail failures are logged
     * but never bubble up: notification must not block token management.
     */
    protected function notifyOwner(AdminPersonalAccessToken $token, string $event): void
    {
        $token->loadMissing('admin');

        if (! $token->admin || empty($token->admin->email)) {
            return;
        }

        try {
            Mail::queue(new AdminTokenNotification($token, $event, request()->ip()));
        } catch (\Throwable $e) {
            Log::error('Admin token notification failed: '.$e->getMessage(), [
                'token_id' => $token->id,
                'event' => $event,
            ]);
        }
    }
}
