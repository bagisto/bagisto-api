<?php

namespace Webkul\BagistoApi\Admin\Audit;

/**
 * Request-scoped holder for the "who / where" context of an admin-API write.
 *
 * Populated by SetAdminApiAuditContext middleware (only on admin-API write
 * requests) and read by AdminApiAuditRecorder when Eloquent model events fire.
 * Bound as a singleton, so it is effectively one-per-request.
 */
class AdminApiAuditContext
{
    protected bool $active = false;

    public ?string $historyId = null;

    public ?int $adminId = null;

    public ?string $adminName = null;

    public ?int $tokenId = null;

    public ?string $tokenName = null;

    public ?string $method = null;

    public ?string $url = null;

    public ?string $ipAddress = null;

    public ?string $userAgent = null;

    public ?string $tags = null;

    /** Number of audit rows written during this request (envelope fallback). */
    protected int $rowCount = 0;

    public function activate(array $data): void
    {
        $this->active = true;
        $this->historyId = $data['history_id'] ?? null;
        $this->adminId = $data['admin_id'] ?? null;
        $this->adminName = $data['admin_name'] ?? null;
        $this->tokenId = $data['token_id'] ?? null;
        $this->tokenName = $data['token_name'] ?? null;
        $this->method = $data['method'] ?? null;
        $this->url = $data['url'] ?? null;
        $this->ipAddress = $data['ip_address'] ?? null;
        $this->userAgent = $data['user_agent'] ?? null;
        $this->tags = $data['tags'] ?? null;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function recordWritten(): void
    {
        $this->rowCount++;
    }

    public function rowCount(): int
    {
        return $this->rowCount;
    }

    /**
     * The shared columns every audit row inherits from the request.
     */
    public function baseAttributes(): array
    {
        return [
            'history_id' => $this->historyId,
            'user_type'  => $this->adminId ? \Webkul\User\Models\Admin::class : null,
            'user_id'    => $this->adminId,
            'admin_name' => $this->adminName,
            'token_id'   => $this->tokenId,
            'token_name' => $this->tokenName,
            'method'     => $this->method,
            'url'        => $this->url,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'tags'       => $this->tags,
        ];
    }
}
