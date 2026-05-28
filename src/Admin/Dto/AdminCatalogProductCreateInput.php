<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for Admin Catalog Product step-1 create.
 *
 * Mirrors the Bagisto admin Create-Product wizard step 1: only sku +
 * attribute_family_id + type are submitted (plus super_attributes when
 * type='configurable'). Name / description / price / inventories etc.
 * arrive in the step-2 update endpoint (Phase 5.9).
 *
 * Phases 5.3 — 5.8 + 5.8-booking: accepts type ∈
 * {simple, virtual, downloadable, grouped, bundle, configurable, booking}.
 *
 * REST  : POST /api/admin/catalog/products
 * GraphQL: createAdminCatalogProduct
 */
class AdminCatalogProductCreateInput
{
    #[ApiProperty(description: 'Unique product SKU (lowercase letters/digits, hyphen-separated).')]
    #[Groups(['mutation'])]
    public ?string $sku = null;

    #[ApiProperty(description: 'Attribute family ID the new product belongs to.')]
    #[Groups(['mutation'])]
    public ?int $attributeFamilyId = null;

    #[ApiProperty(description: 'Product type. Defaults to "simple". One of: simple, virtual, downloadable, grouped, bundle, configurable, booking.')]
    #[Groups(['mutation'])]
    public ?string $type = 'simple';

    /**
     * Required for type='configurable'. Map of attribute_code (or attribute_id)
     * to a non-empty list of option_ids. Example: { "color": [1, 2], "size": [4, 5] }.
     * The core Configurable::create generates the full Cartesian-product of variants.
     *
     * @var array<string|int, int[]>|null
     */
    #[ApiProperty(description: 'Required for type=configurable. Map of attribute code (or id) to option_ids, e.g. { "color": [1,2], "size": [4,5] }. Generates the Cartesian-product of variants on the server.')]
    #[Groups(['mutation'])]
    public ?array $superAttributes = null;
}
