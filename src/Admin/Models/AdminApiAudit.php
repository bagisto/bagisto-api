<?php

namespace Webkul\BagistoApi\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class AdminApiAudit extends Model
{
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
