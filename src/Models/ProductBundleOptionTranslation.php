<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\Product\Models\ProductBundleOptionTranslation as BaseProductBundleOptionTranslation;

#[ApiResource(
    routePrefix: '/api/shop',
    operations: [],
    graphQlOperations: []
)]
class ProductBundleOptionTranslation extends BaseProductBundleOptionTranslation
{
    /**
     * Get the translation identifier.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the label.
     */
    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['mutation'])]
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * Set the label.
     */
    public function setLabel(?string $value): void
    {
        $this->label = $value;
    }

    /**
     * Get the locale.
     */
    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['mutation'])]
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * Set the locale.
     */
    public function setLocale(?string $value): void
    {
        $this->locale = $value;
    }

    /**
     * Get the channel.
     */
    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['mutation'])]
    public function getChannel(): ?string
    {
        return $this->channel;
    }

    /**
     * Set the channel.
     */
    public function setChannel(?string $value): void
    {
        $this->channel = $value;
    }
}
