<?php

namespace Webkul\BagistoApi\Admin\Audit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Repositories\AdminApiAuditRepository;

class AdminApiAuditRecorder
{
    /**
     * Pre-save diffs keyed by spl_object_id, captured on `updating`.
     */
    protected array $pending = [];

    /**
     * The audit repository, resolved on first write so registering the listeners at boot builds no model.
     */
    protected ?AdminApiAuditRepository $audits = null;

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
     * Capture the pre-save diff of an audited model about to be updated.
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
     * Write the audit row of an audited model that was updated.
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
     * Write the audit row of an audited model that was created.
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
     * Write the audit row of an audited model that was deleted.
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
            $this->audits()->record(array_merge($this->context->baseAttributes(), [
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
     * Write one audit row for an authentication event, with or without an audited request.
     */
    public function writeEvent(array $attributes): void
    {
        try {
            $this->audits()->record(array_merge($attributes, [
                'version_id' => $this->nextVersion($attributes['auditable_type'] ?? null, $attributes['auditable_id'] ?? null),
                'created_at' => now(),
            ]));

            if ($this->context->isActive()) {
                $this->context->recordWritten();
            }
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    /**
     * Write the audit row of a model event under the active request context.
     */
    protected function persist(Model $model, string $event, ?array $old, ?array $new): void
    {
        try {
            $type = get_class($model);
            $id = $model->getKey();

            $this->audits()->record(array_merge($this->context->baseAttributes(), [
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
     * Whether a model change is audited: auditing is on, a request context is active and the model is not excluded.
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
     * The version the next audit row of a record gets, or 1 when it cannot be read.
     */
    protected function nextVersion(?string $type, $id): int
    {
        try {
            return $this->audits()->nextVersion($type, $id);
        } catch (\Throwable) {
            return 1;
        }
    }

    /**
     * The audit repository.
     */
    protected function audits(): AdminApiAuditRepository
    {
        return $this->audits ??= app(AdminApiAuditRepository::class);
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
