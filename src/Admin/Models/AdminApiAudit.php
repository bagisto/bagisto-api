<?php

namespace Webkul\BagistoApi\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\BagistoApi\Admin\Contracts\AdminApiAudit as AdminApiAuditContract;

class AdminApiAudit extends Model implements AdminApiAuditContract
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
     * The token kind of rows written with the Integration tokens removed in 2.5.
     */
    public const TOKEN_KIND_INTEGRATION = 'integration';

    /**
     * The token kind of a storefront customer login session.
     */
    public const TOKEN_KIND_CUSTOMER_SESSION = 'customer_session';

    /**
     * The channel of a REST request.
     */
    public const CHANNEL_REST = 'rest';

    /**
     * The channel of a GraphQL request.
     */
    public const CHANNEL_GRAPHQL = 'graphql';

    /**
     * The channel of an event triggered from the admin panel.
     */
    public const CHANNEL_PANEL = 'panel';

    /**
     * The channel of an event triggered by a console command.
     */
    public const CHANNEL_CLI = 'cli';

    /**
     * Every token kind an audit row can carry.
     */
    public const TOKEN_KINDS = [
        self::TOKEN_KIND_SESSION,
        self::TOKEN_KIND_API_TOKEN,
        self::TOKEN_KIND_INTEGRATION,
        self::TOKEN_KIND_CUSTOMER_SESSION,
    ];

    /**
     * Every channel an audit row can carry.
     */
    public const CHANNELS = [
        self::CHANNEL_REST,
        self::CHANNEL_GRAPHQL,
        self::CHANNEL_PANEL,
        self::CHANNEL_CLI,
    ];

    /**
     * Whether the model maintains created and updated timestamps.
     */
    public $timestamps = false;

    /**
     * The table associated with the model.
     */
    protected $table = 'admin_api_audits';

    /**
     * The attributes that are mass assignable.
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
     * The attributes that should be cast.
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
