<?php

namespace Webkul\BagistoApi\Serializer;

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

class TokenHeaderDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * The denormalizer can handle these DTO classes.
     */
    private const SUPPORTED_CLASSES = [
        VerifyTokenInput::class,
        LogoutInput::class,
        CustomerProfileInput::class,
        CustomerAddressInput::class,
        CheckoutAddressInput::class,
        CheckoutAddressQueryInput::class,
    ];

    /**
     * Denormalize.
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $object = $this->denormalizer->denormalize($data, $type, $format, $context);

        if (
            $object instanceof VerifyTokenInput
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

    /**
     * Supports denormalization.
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return in_array($type, self::SUPPORTED_CLASSES, true);
    }

    /**
     * Get supported types.
     */
    public function getSupportedTypes(?string $format): array
    {
        return array_combine(
            self::SUPPORTED_CLASSES,
            array_fill(0, count(self::SUPPORTED_CLASSES), true)
        );
    }

    /**
     * Inject token from Authorization Bearer header if not provided in input.
     *
     * @deprecated This method is no longer functional. Token property has been removed from all DTOs.
     * Token extraction now happens exclusively in processors/providers via TokenHeaderFacade.
     */
    private function injectHeaderToken(object $object, array $data): void {}
}
