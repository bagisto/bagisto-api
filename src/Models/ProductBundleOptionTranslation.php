<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\Product\Models\ProductBundleOptionTranslation as BaseProductBundleOptionTranslation;

#[ApiResource(
    routePrefix: '/api/shop',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Get(
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Product Types'],
                summary: 'Get a product bundle option translation by ID',
                description: 'Returns a single locale-specific translation row (`label`) for a bundle option. Referenced from `/api/shop/product_bundle_options/{id}` responses via the `translations` IRI list.',
            ),
        ),
        new GetCollection(
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Product Types'],
                summary: 'List bundle option translations',
                description: 'Lists all bundle option translation rows. Use the parent `/product_bundle_options/{id}` resource to scope to one option (its `translations` IRI list lets you fetch each locale individually).',
            ),
        ),
    ],
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
