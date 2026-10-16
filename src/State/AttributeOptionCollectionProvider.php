<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Webkul\Attribute\Enums\AttributeTypeEnum;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\Attribute;
use Webkul\BagistoApi\Models\AttributeOption;
use Webkul\BagistoApi\Models\AttributeOptionTranslation;

/**
 * Attribute options, optionally scoped to one attribute and searched by label or admin name.
 * A boolean attribute yields the core Yes/No options instead of stored rows.
 */
class AttributeOptionCollectionProvider implements ProviderInterface
{
    protected const DEFAULT_PER_PAGE = 10;

    protected const MAX_PER_PAGE = 50;

    public function __construct(
        private readonly Pagination $pagination,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $isRest = $operation instanceof HttpOperation;
        $args = $isRest ? request()->query() : ($context['args'] ?? []);

        $attributeId = $uriVariables['attribute_id'] ?? $uriVariables['attributeId']
            ?? $args['attributeId'] ?? $args['attribute_id'] ?? null;

        $search = trim((string) ($args['search'] ?? ''));

        [$perPage, $offset] = $isRest ? $this->restPaging($args) : $this->cursorPaging($args);

        $attribute = null;

        if ($attributeId !== null && $attributeId !== '') {
            $attribute = Attribute::query()->find((int) $attributeId);

            if (! $attribute) {
                throw new ResourceNotFoundException(__('bagistoapi::app.graphql.attribute.not-found'));
            }

            if ($attribute->type === AttributeTypeEnum::BOOLEAN->value) {
                return $this->paginate($this->booleanOptions($attribute->id), 2, $perPage, 0);
            }
        }

        $query = AttributeOption::query()
            ->with('translations')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc');

        if ($attribute) {
            $query->where('attribute_id', $attribute->id);
        }

        if ($search !== '') {
            $locale = core()->getCurrentLocale()->code;

            $query->where(function ($query) use ($search, $locale) {
                $query->whereHas('translations', fn ($query) => $query->where('locale', $locale)->where('label', 'like', "%{$search}%"))
                    ->orWhere('admin_name', 'like', "%{$search}%");
            });
        }

        $total = (clone $query)->count();

        if ($offset > $total) {
            $offset = max(0, $total - $perPage);
        }

        return $this->paginate($query->offset($offset)->limit($perPage)->get(), $total, $perPage, $offset);
    }

    /**
     * The Yes/No options core returns for a boolean attribute, keyed 0 and 1.
     */
    private function booleanOptions(int $attributeId): Collection
    {
        $locale = core()->getCurrentLocale()->code;

        return collect(AttributeTypeEnum::getBooleanOptions())->map(function (array $option) use ($attributeId, $locale) {
            $model = new AttributeOption;

            $model->forceFill([
                'id' => $option['id'],
                'attribute_id' => $attributeId,
                'admin_name' => $option['name'],
                'sort_order' => $option['id'],
            ]);

            $translation = (new AttributeOptionTranslation)->forceFill([
                'attribute_option_id' => $option['id'],
                'locale' => $locale,
                'label' => $option['name'],
            ]);

            $model->setRelation('translations', collect([$translation]));
            $model->setRelation('translation', $translation);

            return $model;
        });
    }

    private function restPaging(array $args): array
    {
        $perPage = (int) ($args['per_page'] ?? self::DEFAULT_PER_PAGE);
        $perPage = $perPage > 0 ? min($perPage, self::MAX_PER_PAGE) : self::DEFAULT_PER_PAGE;
        $page = max(1, (int) ($args['page'] ?? 1));

        return [$perPage, ($page - 1) * $perPage];
    }

    private function cursorPaging(array $args): array
    {
        $perPage = isset($args['first']) ? (int) $args['first'] : (isset($args['last']) ? (int) $args['last'] : self::DEFAULT_PER_PAGE);
        $offset = 0;

        if (! empty($args['after'])) {
            $decoded = base64_decode($args['after'], true);
            $offset = ctype_digit((string) $decoded) ? ((int) $decoded + 1) : 0;
        }

        if (! empty($args['before'])) {
            $decoded = base64_decode($args['before'], true);
            $cursor = ctype_digit((string) $decoded) ? (int) $decoded : 0;
            $offset = max(0, $cursor - $perPage);
        }

        return [$perPage, $offset];
    }

    private function paginate(Collection $items, int $total, int $perPage, int $offset): Paginator
    {
        $currentPage = $total > 0 ? (int) floor($offset / $perPage) + 1 : 1;

        return new Paginator(
            new LengthAwarePaginator($items, $total, $perPage, $currentPage, ['path' => request()->url()])
        );
    }
}
