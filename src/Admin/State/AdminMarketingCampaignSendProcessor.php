<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Mail;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCampaignSendInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminMarketingCampaignSend;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Core\Models\SubscribersList;
use Webkul\Marketing\Mail\NewsletterMail;
use Webkul\Marketing\Models\Campaign;

/**
 * Handles POST /api/admin/marketing/campaigns/{id}/send + createAdminMarketingCampaignSend.
 *
 * Mirrors Webkul\Marketing\Helpers\Campaign::process for a single campaign —
 * resolves the recipient email list from the campaign's customer_group (or the
 * guest subscribers list when the group code is 'guest') and queues a
 * NewsletterMail for each. Skips the date-based event gate because manual
 * trigger semantics are "send now" regardless of event.date.
 *
 * Refuses inactive campaigns (status = 0) with HTTP 422.
 *
 * Permission: marketing.communications.campaigns.edit.
 */
class AdminMarketingCampaignSendProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $this->assertPermission($admin, 'marketing.communications.campaigns.edit');

        $id = $this->resolveCampaignId($data, $uriVariables, $context);
        if (! $id) {
            throw new InvalidInputException(__('bagistoapi::app.admin.marketing.campaign.send.id-required'), 422);
        }

        $campaign = Campaign::with(['email_template', 'customer_group'])->find($id);
        if (! $campaign) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.campaign.not-found'));
        }

        if ((int) $campaign->status !== 1) {
            throw new InvalidInputException(__('bagistoapi::app.admin.marketing.campaign.send.inactive'), 422);
        }

        $emails = $this->resolveRecipients($campaign);

        foreach ($emails as $email) {
            Mail::queue(new NewsletterMail($email, $campaign));
        }

        $result = new AdminMarketingCampaignSend;
        $result->id = (int) $campaign->id;
        $result->campaignId = (int) $campaign->id;
        $result->queued = count($emails);
        $result->message = __('bagistoapi::app.admin.marketing.campaign.send.queued', ['count' => count($emails)]);

        return $result;
    }

    /**
     * Resolve recipient email list. Mirrors Helpers\Campaign::getEmailAddresses.
     *
     * @return array<int,string>
     */
    protected function resolveRecipients(Campaign $campaign): array
    {
        $group = $campaign->customer_group;
        if (! $group) {
            return [];
        }

        if ($group->code === 'guest') {
            $emails = SubscribersList::whereNull('customer_id')->pluck('email')->toArray();
        } else {
            $emails = $group->customers()->where('subscribed_to_news_letter', 1)->pluck('email')->toArray();
        }

        return array_values(array_unique(array_filter($emails)));
    }

    protected function resolveCampaignId(mixed $data, array $uriVariables, array $context): int
    {
        if (! empty($uriVariables['id'])) {
            return (int) $uriVariables['id'];
        }

        if ($data instanceof AdminMarketingCampaignSendInput && $data->campaignId) {
            return (int) $data->campaignId;
        }

        $fromArgs = $context['args']['input']['campaignId']
            ?? $context['args']['campaignId']
            ?? null;
        if ($fromArgs) {
            return (int) $fromArgs;
        }

        $iri = $context['args']['input']['id'] ?? $context['args']['id'] ?? null;
        if ($iri) {
            return (int) basename((string) $iri);
        }

        $routeId = request()->route('id');
        if ($routeId) {
            return (int) $routeId;
        }

        return (int) (request()->input('campaignId') ?? request()->input('campaign_id') ?? 0);
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.campaign.no-permission'));
        }

        if (($role->permission_type ?? null) === 'all') {
            return;
        }

        $perms = $role->permissions ?? [];
        if (is_string($perms)) {
            $perms = array_map('trim', explode(',', $perms));
        }
        if (! is_array($perms)) {
            $perms = [];
        }

        if (! in_array($permission, $perms, true) && ! in_array('*', $perms, true)) {
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.campaign.no-permission'));
        }
    }
}
