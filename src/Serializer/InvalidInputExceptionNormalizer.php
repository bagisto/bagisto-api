<?php

namespace Webkul\BagistoApi\Serializer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webkul\BagistoApi\Exception\InvalidInputException;

/**
 * Normalizer for InvalidInputException
 * Converts InvalidInputException to proper API Platform error response format for REST APIs
 */
class InvalidInputExceptionNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($exception, $format = null, array $context = []): array
    {
        return [
            'type'   => '/errors/400',
            'title'  => 'Bad Request',
            'status' => 400,
            'detail' => $exception->getMessage(),
            'error'  => 'Invalid Input',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof InvalidInputException;
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            InvalidInputException::class => true,
        ];
    }
}
