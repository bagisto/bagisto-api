<?php

namespace Webkul\BagistoApi\Serializer;

use ApiPlatform\State\Pagination\PaginatorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AdminCollectionEnvelopeNormalizer implements NormalizerAwareInterface, NormalizerInterface
{
    use NormalizerAwareTrait;

    /**
     * The flag constant.
     */
    private const FLAG = 'bagistoapi_admin_envelope';

    /**
     * Normalize.
     */
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

    /**
     * Supports normalization.
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        $path = (string) request()?->getPathInfo();

        return $data instanceof PaginatorInterface
            && empty($context[self::FLAG])
            && str_starts_with($path, '/api/admin')
            && ! str_starts_with($path, '/api/admin/graphql');
    }

    /**
     * Get supported types.
     */
    public function getSupportedTypes(?string $format): array
    {
        return [PaginatorInterface::class => false];
    }
}
