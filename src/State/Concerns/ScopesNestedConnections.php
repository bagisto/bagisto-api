<?php

namespace Webkul\BagistoApi\State\Concerns;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

trait ScopesNestedConnections
{
    /**
     * Limit a nested GraphQL connection to the records of its parent, through the parent's own relation.
     */
    protected function scopeToParent(Builder $query, array $context): void
    {
        $parentClass = $context['linkClass'] ?? null;
        $property = $context['linkProperty'] ?? null;
        $parentId = $context['source']['#itemIdentifiers']['id'] ?? null;

        if (
            ! is_string($parentClass)
            || ! is_string($property)
            || $parentId === null
            || ! is_subclass_of($parentClass, Model::class)
        ) {
            return;
        }

        $model = $query->getModel();

        $parent = $parentClass::query()->find($parentId);

        $relation = $parent ? $this->parentRelation($parent, $property) : null;

        if (! $parent) {
            $query->whereRaw('1 = 0');

            return;
        }

        if (
            ! $relation
            || $relation->getRelated()->getTable() !== $model->getTable()
        ) {
            return;
        }

        $query->whereIn(
            $model->getQualifiedKeyName(),
            $relation->getQuery()->select($relation->getRelated()->getQualifiedKeyName()),
        );
    }

    /**
     * The parent's relation behind a connection field, whether the method is named in camel or snake case.
     */
    protected function parentRelation(Model $parent, string $property): ?Relation
    {
        foreach (array_unique([$property, Str::camel($property), Str::snake($property)]) as $method) {
            if (! method_exists($parent, $method)) {
                continue;
            }

            $relation = $parent->{$method}();

            if ($relation instanceof Relation) {
                return $relation;
            }
        }

        return null;
    }
}
