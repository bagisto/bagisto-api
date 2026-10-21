<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

use Webkul\BagistoApi\Admin\Models\AdminApiSession;
use Webkul\BagistoApi\Admin\Models\AdminSessionResource;

trait MapsAdminApiSession
{
    /**
     * Fill a session resource from a stored session, marking the one the request was made with.
     */
    protected function mapSession(AdminApiSession $session, AdminSessionResource $resource, ?int $currentSessionId = null): AdminSessionResource
    {
        $resource->id = (int) $session->id;
        $resource->admin_id = (int) $session->admin_id;
        $resource->admin_name = $session->admin?->name;
        $resource->name = $session->name;
        $resource->client_type = $session->client_type;
        $resource->ip_address = $session->ip_address;
        $resource->user_agent = $session->user_agent;
        $resource->last_ip_address = $session->last_ip_address;
        $resource->last_user_agent = $session->last_user_agent;
        $resource->last_used_at = $session->last_used_at?->toIso8601String();
        $resource->last_channel = $session->last_channel;
        $resource->refresh_expires_at = $session->refresh_expires_at?->toIso8601String();
        $resource->expires_at = $session->expires_at?->toIso8601String();
        $resource->revoked_at = $session->revoked_at?->toIso8601String();
        $resource->revoked_reason = $session->revoked_reason;
        $resource->status = $this->sessionStatus($session);
        $resource->is_current = $currentSessionId !== null
            && (int) $session->id === $currentSessionId;
        $resource->created_at = $session->created_at?->toIso8601String();

        return $resource;
    }

    /**
     * The session status: active, expired or revoked.
     */
    protected function sessionStatus(AdminApiSession $session): string
    {
        if ($session->isRevoked()) {
            return 'revoked';
        }

        return $session->isExpired()
            ? 'expired'
            : 'active';
    }
}
