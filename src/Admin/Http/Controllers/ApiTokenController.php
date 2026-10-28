<?php

namespace Webkul\BagistoApi\Admin\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Webkul\BagistoApi\Admin\DataGrids\ApiTokenDataGrid;
use Webkul\BagistoApi\Admin\Http\Requests\ApiTokenRequest;
use Webkul\BagistoApi\Admin\Models\AdminApiToken;
use Webkul\BagistoApi\Admin\Repositories\AdminApiTokenRepository;
use Webkul\BagistoApi\Admin\Services\AdminApiAuthSettings;
use Webkul\BagistoApi\Admin\Services\AdminApiTokenIssuer;
use Webkul\User\Models\Admin;
use Webkul\User\Repositories\AdminRepository;

class ApiTokenController extends Controller
{
    /**
     * The session key holding the id of the token whose string is shown once.
     */
    public const PLAIN_TEXT_ID_KEY = 'api_token_plain_text_id';

    /**
     * The session key holding the token string shown once.
     */
    public const PLAIN_TEXT_KEY = 'api_token_plain_text';

    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected AdminApiTokenRepository $apiTokenRepository,
        protected AdminRepository $adminRepository,
        protected AdminApiTokenIssuer $issuer,
        protected AdminApiAuthSettings $settings,
    ) {}

    /**
     * Display the API token listing, or its datagrid data for an AJAX request.
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(ApiTokenDataGrid::class)->process();
        }

        return view('bagistoapi::api-tokens.index');
    }

    /**
     * Show the create form with the admins the signed-in admin may create a token for.
     */
    public function create()
    {
        $actor = $this->actor();

        $admins = $this->adminRepository->with('role')->all()
            ->filter(fn (Admin $admin) => $this->issuer->canCreateFor($actor, $admin))
            ->values();

        return view('bagistoapi::api-tokens.create', [
            'admins' => $admins,
            'allowNoExpiry' => $this->settings->allowNonExpiringApiTokens(),
        ]);
    }

    /**
     * Create the API token and show its string once on its page.
     */
    public function store(ApiTokenRequest $request)
    {
        $owner = $this->adminRepository->with('role')->findOrFail($request->integer('admin_id'));

        $issued = $this->issuer->create(
            $this->actor(),
            $owner,
            (string) $request->input('name'),
            (string) $request->input('domain'),
            $request->expiresAt(),
            $request,
        );

        session()->flash(self::PLAIN_TEXT_ID_KEY, $issued->token->id);
        session()->flash(self::PLAIN_TEXT_KEY, $issued->plainText);
        session()->flash('success', trans('bagistoapi::app.admin.api-tokens.messages.created'));

        return redirect()->route('admin.settings.api_tokens.view', $issued->token->id);
    }

    /**
     * Show an API token, with its string right after it was created.
     */
    public function view(int $id)
    {
        $token = $this->manageableToken($id);

        $plainText = (int) session(self::PLAIN_TEXT_ID_KEY) === (int) $token->id
            ? session(self::PLAIN_TEXT_KEY)
            : null;

        return view('bagistoapi::api-tokens.view', [
            'token' => $token,
            'plainText' => $plainText,
        ]);
    }

    /**
     * Rename an API token; its domain and expiry cannot change.
     */
    public function update(ApiTokenRequest $request, int $id)
    {
        $token = $this->manageableToken($id);

        $this->issuer->rename($this->actor(), $token, (string) $request->input('name'), $request);

        session()->flash('success', trans('bagistoapi::app.admin.api-tokens.messages.updated'));

        return redirect()->route('admin.settings.api_tokens.view', $token->id);
    }

    /**
     * Revoke an API token, which takes effect on its next request.
     */
    public function destroy(int $id): JsonResponse
    {
        $token = $this->manageableToken($id);

        if ($token->isRevoked()) {
            return new JsonResponse([
                'message' => trans('bagistoapi::app.admin.api-tokens.errors.already-revoked'),
            ], 400);
        }

        $this->issuer->revoke($this->actor(), $token, request());

        return new JsonResponse([
            'message' => trans('bagistoapi::app.admin.api-tokens.messages.revoked'),
        ]);
    }

    /**
     * The signed-in admin.
     */
    protected function actor(): Admin
    {
        return auth()->guard('admin')->user();
    }

    /**
     * The API token with this id, aborting with 404 unless the signed-in admin may manage it.
     */
    protected function manageableToken(int $id): AdminApiToken
    {
        $token = $this->apiTokenRepository->findWithAdmin($id);

        abort_if(
            ! $token
            || ! $this->issuer->canManage($this->actor(), $token),
            404
        );

        return $token;
    }
}
