<?php

namespace Webkul\BagistoApi\Admin\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One recorded change made through the admin API. Append-only.
 *
 * NOT an API Platform resource — this backs the Integration → History admin
 * screen and is written by AdminApiAuditRecorder.
 */
class AdminApiAudit extends Model
{
    public $timestamps = false;

    protected $table = 'admin_api_audits';

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
        'method',
        'url',
        'ip_address',
        'user_agent',
        'tags',
        'created_at',
    ];

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
