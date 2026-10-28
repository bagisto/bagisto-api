<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminApiToken;
use Webkul\BagistoApi\Admin\Models\AdminSettingsApiToken;

trait MapsAdminApiToken
{
    /**
     * Fill an API token resource from a stored API token, marking the one the request was made with.
     */
    protected function mapApiToken(AdminApiToken $token): AdminSettingsApiToken
    {
        $resource = new AdminSettingsApiToken;

        $resource->id = (int) $token->id;
        $resource->admin_id = (int) $token->admin_id;
        $resource->admin_name = $token->admin?->name;
        $resource->created_by_admin_id = $token->created_by_admin_id !== null ? (int) $token->created_by_admin_id : null;
        $resource->created_by_name = $token->createdBy?->name;
        $resource->name = $token->name;
        $resource->token_preview = $token->token_preview;
        $resource->domain = $token->domain;
        $resource->resolved_ips = is_array($token->resolved_ips) ? array_values($token->resolved_ips) : null;
        $resource->resolved_at = $token->resolved_at?->toIso8601String();
        $resource->domain_mismatch_count = (int) $token->domain_mismatch_count;
        $resource->last_domain_mismatch_at = $token->last_domain_mismatch_at?->toIso8601String();
        $resource->expires_at = $token->expires_at?->toIso8601String();
        $resource->last_used_at = $token->last_used_at?->toIso8601String();
        $resource->last_ip_address = $token->last_ip_address;
        $resource->last_user_agent = $token->last_user_agent;
        $resource->last_channel = $token->last_channel;
        $resource->revoked_at = $token->revoked_at?->toIso8601String();
        $resource->revoked_reason = $token->revoked_reason;
        $resource->revoked_by_admin_id = $token->revoked_by_admin_id !== null ? (int) $token->revoked_by_admin_id : null;
        $resource->status = $token->status();
        $resource->is_current = (int) AdminAuthHelper::credential()?->apiToken?->id === (int) $token->id;
        $resource->created_at = $token->created_at?->toIso8601String();

        return $resource;
    }
}
