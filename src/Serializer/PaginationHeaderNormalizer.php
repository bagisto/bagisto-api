<?php

namespace Webkul\BagistoApi\Serializer;

use ApiPlatform\State\Pagination\HasNextPagePaginatorInterface;
use ApiPlatform\State\Pagination\PaginatorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Captures pagination metadata onto the current request so the Laravel
 * middleware can emit X-Total-Count / X-Page / X-Per-Page / X-Total-Pages
 * headers without each REST provider having to do anything.
 *
 * Runs first for any PaginatorInterface, stashes counts on
 * request()->attributes, then delegates to the next normalizer in the chain.
 */
class PaginationHeaderNormalizer implements NormalizerAwareInterface, NormalizerInterface
{
    use NormalizerAwareTrait;

    private const CAPTURED_FLAG = 'bagistoapi_pagination_captured';

    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        if ($data instanceof PaginatorInterface) {
            request()->attributes->set('bagistoapi.pagination', [
                'total'       => (int) $data->getTotalItems(),
                'page'        => (int) $data->getCurrentPage(),
                'per_page'    => (int) $data->getItemsPerPage(),
                'total_pages' => (int) $data->getLastPage(),
                'has_next'    => $data instanceof HasNextPagePaginatorInterface ? $data->hasNextPage() : null,
            ]);
        }

        $context[self::CAPTURED_FLAG] = true;

        return $this->normalizer->normalize($data, $format, $context);
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof PaginatorInterface && empty($context[self::CAPTURED_FLAG]);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [PaginatorInterface::class => false];
    }
}
