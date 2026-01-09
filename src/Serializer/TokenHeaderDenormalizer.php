<?php

namespace Webkul\BagistoApi\Serializer;

use Illuminate\Http\Request;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Webkul\BagistoApi\Dto\CheckoutAddressInput;
use Webkul\BagistoApi\Dto\CheckoutAddressQueryInput;
use Webkul\BagistoApi\Dto\CustomerAddressInput;
use Webkul\BagistoApi\Dto\CustomerProfileInput;
use Webkul\BagistoApi\Dto\LogoutInput;
use Webkul\BagistoApi\Dto\VerifyTokenInput;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;

/**
 * Custom denormalizer to inject Authorization Bearer token from header into DTOs
 *
 * This ensures that token from the Authorization header is automatically
 * injected into token-based DTOs when using GraphQL mutations without explicitly
 * providing the token in the request body.
 *
 * Recommended usage:
 * Header: Authorization: Bearer <customer_token>
 */
class TokenHeaderDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * The denormalizer can handle these DTO classes
     */
    private const SUPPORTED_CLASSES = [
        VerifyTokenInput::class,
        LogoutInput::class,
        CustomerProfileInput::class,
        CustomerAddressInput::class,
        CheckoutAddressInput::class,
        CheckoutAddressQueryInput::class,
    ];

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        // Let the wrapped denormalizer handle the actual denormalization first
        $object = $this->denormalizer->denormalize($data, $type, $format, $context);

        // Then inject the header token if applicable
        if ($object instanceof VerifyTokenInput
            || $object instanceof LogoutInput
            || $object instanceof CustomerProfileInput
            || $object instanceof CustomerAddressInput
            || $object instanceof CheckoutAddressInput
            || $object instanceof CheckoutAddressQueryInput
        ) {
            $this->injectHeaderToken($object, $data);
        }

        return $object;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return in_array($type, self::SUPPORTED_CLASSES, true);
    }

    public function getSupportedTypes(?string $format): array
    {
        return array_combine(
            self::SUPPORTED_CLASSES,
            array_fill(0, count(self::SUPPORTED_CLASSES), true)
        );
    }

    /**
     * Inject token from Authorization Bearer header if not provided in input
     *
     * @deprecated This method is no longer functional. Token property has been removed from all DTOs.
     * Token extraction now happens exclusively in processors/providers via TokenHeaderFacade.
     */
    private function injectHeaderToken(object $object, array $data): void
    {
        // This method does nothing - token extraction now happens in processors/providers
        // DTOs no longer have $token property
    }
}
