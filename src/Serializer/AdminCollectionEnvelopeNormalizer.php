<?php

namespace Webkul\BagistoApi\Serializer;

use ApiPlatform\State\Pagination\PaginatorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Wraps admin REST collection responses in a `{ data, meta }` envelope.
 *
 * Runs only for `/api/admin/*` REST routes (GraphQL lives at /api/graphql and
 * is untouched — it uses native cursor pagination). For every other route the
 * normalizer declines and the default chain handles the response.
 *
 * `meta` keys are written camelCase directly — this normalizer returns a raw
 * array, so the API Platform name converter never sees them.
 */
class AdminCollectionEnvelopeNormalizer implements NormalizerAwareInterface, NormalizerInterface
{
    use NormalizerAwareTrait;

    private const FLAG = 'bagistoapi_admin_envelope';

    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $items = [];

        foreach ($data as $item) {
            $items[] = $this->normalizer->normalize($item, $format, $context + [self::FLAG => true]);
        }

        $total = (int) $data->getTotalItems();
        $perPage = (int) $data->getItemsPerPage();
        $page = (int) $data->getCurrentPage();
        $count = count($items);

        $from = $total > 0 && $count > 0 ? ($page - 1) * $perPage + 1 : null;

        $meta = [
            'currentPage' => $page,
            'perPage' => $perPage,
            'lastPage' => (int) $data->getLastPage(),
            'total' => $total,
            'from' => $from,
            'to' => $from !== null ? $from + $count - 1 : null,
        ];

        // Allow paginators to contribute additional meta keys (e.g. totalQty
        // on the product-inventories listing). The paginator must expose
        // public method getExtraMeta(): array — interface duck-typed for
        // minimal coupling.
        if (method_exists($data, 'getExtraMeta')) {
            $extra = $data->getExtraMeta();
            if (is_array($extra)) {
                $meta = array_merge($meta, $extra);
            }
        }

        return [
            'data' => $items,
            'meta' => $meta,
        ];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        $path = (string) request()?->getPathInfo();

        return $data instanceof PaginatorInterface
            && empty($context[self::FLAG])
            && str_starts_with($path, '/api/admin')
            && ! str_starts_with($path, '/api/admin/graphql');
    }

    public function getSupportedTypes(?string $format): array
    {
        return [PaginatorInterface::class => false];
    }
}
