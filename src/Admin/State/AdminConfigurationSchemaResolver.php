<?php

namespace Webkul\BagistoApi\Admin\State;

use Illuminate\Support\Collection;
use Webkul\Core\SystemConfig\Item;

/**
 * Shared resolver that walks the `system_config()` tree exactly once per
 * request and surfaces field definitions by their full dotted code.
 *
 * Used by:
 *   - AdminConfigurationMenuProvider   (serialise the tree)
 *   - AdminConfigurationValuesProvider (list the field codes under a slug)
 *   - AdminConfigurationUpdateProcessor (look up validation / scope flags)
 *
 * The `system_config()->getItems()` collection itself is memoised by core,
 * but our flattened code→field map is not — we build it once and cache on
 * the instance.
 */
class AdminConfigurationSchemaResolver
{
    /**
     * Flat map: full dotted field code → field metadata array.
     *
     * @var array<string, array<string, mixed>>|null
     */
    protected ?array $fieldMap = null;

    /**
     * Flat map: slug (item key) → Item.
     *
     * @var array<string, Item>|null
     */
    protected ?array $itemMap = null;

    /**
     * Roots ordered by sort (top-level sections like `general`, `sales`, ...).
     */
    protected ?Collection $roots = null;

    /**
     * Return the top-level menu items collection.
     */
    public function getRoots(): Collection
    {
        if ($this->roots === null) {
            $this->roots = system_config()->getItems();
        }

        return $this->roots;
    }

    /**
     * Look up an Item by its full slug (e.g. "sales.order_settings").
     */
    public function getItem(string $slug): ?Item
    {
        $this->buildMaps();

        return $this->itemMap[$slug] ?? null;
    }

    /**
     * Look up a field definition by its full dotted code
     * (e.g. "sales.order_settings.reorder.admin").
     *
     * @return array<string, mixed>|null
     */
    public function getField(string $code): ?array
    {
        $this->buildMaps();

        return $this->fieldMap[$code] ?? null;
    }

    /**
     * Return every registered field whose code is under the given slug subtree
     * (key prefix match). Map of code → field metadata.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getFieldsUnder(string $slug): array
    {
        $this->buildMaps();
        $prefix = $slug.'.';

        $out = [];
        foreach ($this->fieldMap as $code => $field) {
            if (str_starts_with($code, $prefix)) {
                $out[$code] = $field;
            }
        }

        return $out;
    }

    /**
     * Serialise the entire tree to a plain associative-array shape.
     *
     * @return array<int, array<string, mixed>>
     */
    public function toArray(?string $slugFilter = null): array
    {
        $roots = $this->getRoots();

        $out = [];
        foreach ($roots as $section) {
            $node = $this->itemToArray($section, $slugFilter);
            if ($node !== null) {
                $out[] = $node;
            }
        }

        return $out;
    }

    /**
     * Find a single item subtree (returns the item serialised + all its
     * children + nested field groups under it).
     *
     * @return array<string, mixed>|null
     */
    public function findSlug(string $slug): ?array
    {
        $item = $this->getItem($slug);

        if (! $item) {
            return null;
        }

        return $this->serialiseItem($item, true);
    }

    /**
     * Build the flat code → field map and slug → item map.
     */
    protected function buildMaps(): void
    {
        if ($this->fieldMap !== null) {
            return;
        }

        $this->fieldMap = [];
        $this->itemMap = [];

        foreach ($this->getRoots() as $section) {
            $this->indexItem($section);
        }
    }

    /**
     * Recursively register an Item and any fields/children it carries.
     */
    protected function indexItem(Item $item): void
    {
        $this->itemMap[$item->key] = $item;

        if (! empty($item->fields)) {
            foreach ($item->fields as $field) {
                $code = $item->key.'.'.$field['name'];
                $this->fieldMap[$code] = $field + ['_itemKey' => $item->key];
            }
        }

        foreach ($item->getChildren() as $child) {
            $this->indexItem($child);
        }
    }

    /**
     * Recursive serialiser for `toArray()` — drops branches outside
     * `$slugFilter` when supplied.
     *
     * @return array<string, mixed>|null
     */
    protected function itemToArray(Item $item, ?string $slugFilter): ?array
    {
        if ($slugFilter !== null) {
            $isMatch = $item->key === $slugFilter
                || str_starts_with($item->key.'.', $slugFilter.'.')
                || str_starts_with($slugFilter.'.', $item->key.'.');

            if (! $isMatch) {
                return null;
            }
        }

        $node = $this->serialiseItem($item, false);

        $children = [];
        foreach ($item->getChildren() as $child) {
            $childNode = $this->itemToArray($child, $slugFilter);
            if ($childNode !== null) {
                $children[] = $childNode;
            }
        }

        if (! empty($children)) {
            $node['children'] = $children;
        }

        return $node;
    }

    /**
     * Serialise a single Item to its API shape.
     *
     * @return array<string, mixed>
     */
    protected function serialiseItem(Item $item, bool $withChildren): array
    {
        $node = [
            'key'   => $item->key,
            'name'  => $item->name,
            'info'  => $item->info,
            'icon'  => $item->icon,
            'sort'  => $item->sort,
        ];

        if (! empty($item->fields)) {
            $node['fields'] = $this->serialiseFields($item);
        }

        if ($withChildren) {
            $children = [];
            foreach ($item->getChildren() as $child) {
                $children[] = $this->serialiseItem($child, true);
            }
            if (! empty($children)) {
                $node['children'] = $children;
            }
        }

        return $node;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function serialiseFields(Item $item): array
    {
        $out = [];
        foreach ($item->fields as $field) {
            $type = $field['type'] ?? 'text';
            $isCustom = ! empty($field['path']);

            $out[] = [
                'name'         => $field['name'],
                'code'         => $item->key.'.'.$field['name'],
                'title'        => $field['title'] ?? null,
                'type'         => $isCustom ? 'custom' : $type,
                'customView'   => $isCustom ? $field['path'] : null,
                'default'      => $field['default'] ?? null,
                'channelBased' => (bool) ($field['channel_based'] ?? false),
                'localeBased'  => (bool) ($field['locale_based'] ?? false),
                'validation'   => $field['validation'] ?? null,
                'options'      => $this->normaliseOptions($field['options'] ?? null),
                'depends'      => $field['depends'] ?? null,
                'info'         => $field['info'] ?? null,
            ];
        }

        return $out;
    }

    /**
     * Options may be: array of {title,value}, callable string, or null.
     * Normalise to an array; non-array (callable/string ref) becomes null
     * since the API can't usefully render it.
     *
     * @return array<int, array<string, mixed>>|null
     */
    protected function normaliseOptions(mixed $options): ?array
    {
        if (! is_array($options)) {
            return null;
        }

        $out = [];
        foreach ($options as $opt) {
            if (is_array($opt)) {
                $out[] = $opt;
            }
        }

        return $out;
    }
}
