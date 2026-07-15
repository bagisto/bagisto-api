<?php

namespace Webkul\BagistoApi\Admin\Audit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Models\AdminApiAudit;

/**
 * Records admin-API writes as audit rows by listening to Eloquent model events.
 *
 * Only acts when AdminApiAuditContext is active (i.e. an admin-API write request)
 * — so normal web-panel changes and reads are never recorded, with zero
 * per-model code and no Bagisto core edits.
 *
 * Bound as a singleton so the updating→updated diff stash survives within a
 * request. Every write is wrapped so auditing can never break the real change.
 */
class AdminApiAuditRecorder
{
    /** Pre-save diffs keyed by spl_object_id, captured on `updating`. */
    protected array $pending = [];

    public function __construct(protected AdminApiAuditContext $context) {}

    /**
     * Register the Eloquent listeners. Called once from the service provider.
     */
    public function register(): void
    {
        Event::listen('eloquent.updating: *', [$this, 'onUpdating']);
        Event::listen('eloquent.updated: *', [$this, 'onUpdated']);
        Event::listen('eloquent.created: *', [$this, 'onCreated']);
        Event::listen('eloquent.deleted: *', [$this, 'onDeleted']);
    }

    public function onUpdating(string $event, array $payload): void
    {
        $model = $payload[0] ?? null;
        if (! $model instanceof Model || ! $this->shouldAudit($model)) {
            return;
        }

        $dirty = $model->getDirty();
        if (empty($dirty)) {
            return;
        }

        $this->pending[spl_object_id($model)] = [
            'old' => array_intersect_key($model->getOriginal(), $dirty),
            'new' => $dirty,
        ];
    }

    public function onUpdated(string $event, array $payload): void
    {
        $model = $payload[0] ?? null;
        if (! $model instanceof Model) {
            return;
        }

        $diff = $this->pending[spl_object_id($model)] ?? null;
        unset($this->pending[spl_object_id($model)]);

        if (! $diff || ! $this->shouldAudit($model)) {
            return;
        }

        $this->persist($model, 'updated', $diff['old'], $diff['new']);
    }

    public function onCreated(string $event, array $payload): void
    {
        $model = $payload[0] ?? null;
        if (! $model instanceof Model || ! $this->shouldAudit($model)) {
            return;
        }

        $this->persist($model, 'created', null, $model->getAttributes());
    }

    public function onDeleted(string $event, array $payload): void
    {
        $model = $payload[0] ?? null;
        if (! $model instanceof Model || ! $this->shouldAudit($model)) {
            return;
        }

        $this->persist($model, 'deleted', $model->getAttributes(), null);
    }

    /**
     * Envelope fallback — one row when a write produced no model events.
     */
    public function writeEnvelope(?string $auditableType, $auditableId, string $event, ?array $newValues): void
    {
        if (! $this->context->isActive()) {
            return;
        }

        try {
            AdminApiAudit::create(array_merge($this->context->baseAttributes(), [
                'version_id' => $auditableId ? $this->nextVersion($auditableType, $auditableId) : 1,
                'event' => $event,
                'auditable_type' => $auditableType,
                'auditable_id' => $auditableId,
                'old_values' => null,
                'new_values' => $newValues ? $this->redact($newValues) : null,
                'created_at' => now(),
            ]));

            $this->context->recordWritten();
        } catch (\Throwable $e) {
            report($e);
        }
    }

    protected function persist(Model $model, string $event, ?array $old, ?array $new): void
    {
        try {
            $type = get_class($model);
            $id = $model->getKey();

            AdminApiAudit::create(array_merge($this->context->baseAttributes(), [
                'version_id' => $this->nextVersion($type, $id),
                'event' => $event,
                'auditable_type' => $type,
                'auditable_id' => $id,
                'old_values' => $old !== null ? $this->redact($old) : null,
                'new_values' => $new !== null ? $this->redact($new) : null,
                'created_at' => now(),
            ]));

            $this->context->recordWritten();
        } catch (\Throwable $e) {
            // Auditing must never break the underlying write.
            report($e);
        }
    }

    protected function shouldAudit(Model $model): bool
    {
        if (! config('bagistoapi.audit.enabled', true)) {
            return false;
        }

        if (! $this->context->isActive()) {
            return false;
        }

        foreach ((array) config('bagistoapi.audit.exclude_models', []) as $excluded) {
            if ($model instanceof $excluded) {
                return false;
            }
        }

        return true;
    }

    protected function nextVersion(?string $type, $id): int
    {
        if (! $type || $id === null) {
            return 1;
        }

        try {
            return (int) AdminApiAudit::where('auditable_type', $type)
                ->where('auditable_id', $id)
                ->max('version_id') + 1;
        } catch (\Throwable) {
            return 1;
        }
    }

    /**
     * Replace sensitive values with "[redacted]" (exact or "*suffix" match).
     */
    protected function redact(array $values): array
    {
        $patterns = (array) config('bagistoapi.audit.redact', []);

        foreach ($values as $key => $value) {
            foreach ($patterns as $pattern) {
                $matches = str_starts_with($pattern, '*')
                    ? str_ends_with((string) $key, ltrim($pattern, '*'))
                    : $key === $pattern;

                if ($matches) {
                    $values[$key] = '[redacted]';
                    break;
                }
            }
        }

        return $values;
    }
}
