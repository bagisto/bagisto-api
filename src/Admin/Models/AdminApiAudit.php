<?php

namespace Webkul\BagistoApi\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class AdminApiAudit extends Model
{
    /**
     * The token kind of a request made with a login session's access token.
     */
    public const TOKEN_KIND_SESSION = 'session';

    /**
     * The token kind of a request made with an API token.
     */
    public const TOKEN_KIND_API_TOKEN = 'api_token';

    /**
     * The token kind of rows written by the removed Integration tokens.
     */
    public const TOKEN_KIND_INTEGRATION = 'integration';

    /**
     * The channel of an event triggered from the admin panel.
     */
    public const CHANNEL_PANEL = 'panel';

    /**
     * The channel of an event triggered by a console command.
     */
    public const CHANNEL_CLI = 'cli';

    /**
     * The timestamps.
     */
    public $timestamps = false;

    /**
     * The table.
     */
    protected $table = 'admin_api_audits';

    /**
     * The fillable.
     */
    protected $fillable = [
        'history_id',
        'version_id',
        'event',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'user_type',
        'user_id',
        'admin_name',
        'token_id',
        'token_name',
        'token_kind',
        'channel',
        'method',
        'url',
        'ip_address',
        'user_agent',
        'tags',
        'created_at',
    ];

    /**
     * The casts.
     */
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Short class name of the audited model, e.g. "Currency".
     */
    public function getAuditableLabelAttribute(): string
    {
        if (! $this->auditable_type) {
            return '—';
        }

        return class_basename($this->auditable_type);
    }
}
