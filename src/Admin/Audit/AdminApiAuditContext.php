<?php

namespace Webkul\BagistoApi\Admin\Audit;

use Webkul\User\Models\Admin;

class AdminApiAuditContext
{
    /**
     * The active.
     */
    protected bool $active = false;

    /**
     * The history id.
     */
    public ?string $historyId = null;

    /**
     * The admin id.
     */
    public ?int $adminId = null;

    /**
     * The admin name.
     */
    public ?string $adminName = null;

    /**
     * The token id.
     */
    public ?int $tokenId = null;

    /**
     * The token name.
     */
    public ?string $tokenName = null;

    /**
     * The method.
     */
    public ?string $method = null;

    /**
     * The url.
     */
    public ?string $url = null;

    /**
     * The ip address.
     */
    public ?string $ipAddress = null;

    /**
     * The user agent.
     */
    public ?string $userAgent = null;

    /**
     * The tags.
     */
    public ?string $tags = null;

    /**
     * Number of audit rows written during this request (envelope fallback).
     */
    protected int $rowCount = 0;

    /**
     * Activate.
     */
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

    /**
     * Whether active.
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * Record written.
     */
    public function recordWritten(): void
    {
        $this->rowCount++;
    }

    /**
     * Row count.
     */
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
            'user_type' => $this->adminId ? Admin::class : null,
            'user_id' => $this->adminId,
            'admin_name' => $this->adminName,
            'token_id' => $this->tokenId,
            'token_name' => $this->tokenName,
            'method' => $this->method,
            'url' => $this->url,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'tags' => $this->tags,
        ];
    }
}
