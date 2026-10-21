<?php

namespace Webkul\BagistoApi\Admin\Audit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Models\AdminApiAudit;

class AdminApiAuditRecorder
{
    /**
     * Pre-save diffs keyed by spl_object_id, captured on `updating`.
     */
    protected array $pending = [];

    /**
     * Create a new instance.
     */
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

    /**
     * On updating.
     */
    public function onUpdating(string $event, array $payload): void
    {
        $model = $payload[0] ?? null;
        if (
            ! $model instanceof Model
            || ! $this->shouldAudit($model)
        ) {
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

    /**
     * On updated.
     */
    public function onUpdated(string $event, array $payload): void
    {
        $model = $payload[0] ?? null;
        if (! $model instanceof Model) {
            return;
        }

        $diff = $this->pending[spl_object_id($model)] ?? null;
        unset($this->pending[spl_object_id($model)]);

        if (
            ! $diff
            || ! $this->shouldAudit($model)
        ) {
            return;
        }

        $this->persist($model, 'updated', $diff['old'], $diff['new']);
    }

    /**
     * On created.
     */
    public function onCreated(string $event, array $payload): void
    {
        $model = $payload[0] ?? null;
        if (
            ! $model instanceof Model
            || ! $this->shouldAudit($model)
        ) {
            return;
        }

        $this->persist($model, 'created', null, $model->getAttributes());
    }

    /**
     * On deleted.
     */
    public function onDeleted(string $event, array $payload): void
    {
        $model = $payload[0] ?? null;
        if (
            ! $model instanceof Model
            || ! $this->shouldAudit($model)
        ) {
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
                'new_values' => $newValues ? $this->redact($newValues, $auditableType) : null,
                'created_at' => now(),
            ]));

            $this->context->recordWritten();
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    /**
     * Persist.
     */
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
                'old_values' => $old !== null ? $this->redact($old, $type) : null,
                'new_values' => $new !== null ? $this->redact($new, $type) : null,
                'created_at' => now(),
            ]));

            $this->context->recordWritten();
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    /**
     * Should audit.
     */
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

    /**
     * Next version.
     */
    protected function nextVersion(?string $type, $id): int
    {
        if (
            ! $type
            || $id === null
        ) {
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
     * Replace sensitive values with "[redacted]" (exact, "*suffix" or substring match).
     */
    protected function redact(array $values, ?string $modelClass = null): array
    {
        $patterns = (array) config('bagistoapi.audit.redact', []);
        $secretRow = $this->isSecretRow($values);
        $valueRedacted = $secretRow || $this->redactsValueColumn($modelClass);

        foreach ($values as $key => $value) {
            $isValueColumn = $key === 'value' && $valueRedacted;

            if (
                $isValueColumn
                || $this->matchesRedactPattern((string) $key, $patterns)
                || $this->containsSecretWord((string) $key)
            ) {
                $values[$key] = '[redacted]';
            }
        }

        return $values;
    }

    /**
     * Whether the key equals a pattern, or ends with a "*suffix" pattern.
     */
    protected function matchesRedactPattern(string $key, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            $matches = str_starts_with($pattern, '*')
                ? str_ends_with($key, ltrim($pattern, '*'))
                : $key === $pattern;

            if ($matches) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether the text contains any configured secret word.
     */
    protected function containsSecretWord(string $text): bool
    {
        foreach ((array) config('bagistoapi.audit.redact_contains', []) as $word) {
            if (str_contains(strtolower($text), $word)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether the row's `code` names a secret setting.
     */
    protected function isSecretRow(array $values): bool
    {
        return isset($values['code'])
            && is_string($values['code'])
            && $this->containsSecretWord($values['code']);
    }

    /**
     * Whether the model's `value` column must never be stored.
     */
    protected function redactsValueColumn(?string $modelClass): bool
    {
        foreach ((array) config('bagistoapi.audit.redact_value_models', []) as $redacted) {
            if (
                $modelClass !== null
                && is_a($modelClass, $redacted, true)
            ) {
                return true;
            }
        }

        return false;
    }
}
