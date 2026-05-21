<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductCollectionProvider;

/**
 * Admin Catalog → Products datagrid listing.
 *
 * REST   : GET /api/admin/catalog/products
 * GraphQL: adminCatalogProducts (added in a later task)
 *
 * 1:1 parity with Webkul\Admin\DataGrids\Catalog\ProductDataGrid — same
 * columns, same filters, same sort columns, same dual DB / Elasticsearch
 * branch (gated by the same two core config flags the datagrid uses).
 *
 * This is distinct from src/Admin/Models/AdminProduct.php (the slim picker
 * used by the Create-Order modal). The two endpoints coexist and serve
 * different surfaces.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCatalogProduct',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/catalog/products',
            provider: AdminCatalogProductCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Catalog'],
                summary: 'List catalog products (datagrid parity)',
                description: 'Paginated, filterable, sortable product list mirroring the admin Catalog → Products datagrid. Routes via Elasticsearch when the admin panel is configured to.',
            ),
        ),
    ],
)]
class AdminCatalogProduct
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $sku = null;

    #[ApiProperty(writable: false)]
    public ?string $name = null;

    #[ApiProperty(writable: false)]
    public ?string $type = null;

    #[ApiProperty(writable: false)]
    public ?int $status = null;

    #[ApiProperty(writable: false)]
    public ?string $price = null;

    #[ApiProperty(writable: false)]
    public ?string $formattedPrice = null;

    #[ApiProperty(writable: false)]
    public ?int $quantity = null;

    #[ApiProperty(writable: false)]
    public ?string $baseImageUrl = null;

    #[ApiProperty(writable: false)]
    public ?int $imagesCount = null;

    #[ApiProperty(writable: false)]
    public ?int $categoryId = null;

    #[ApiProperty(writable: false)]
    public ?string $categoryName = null;

    #[ApiProperty(writable: false)]
    public ?string $channel = null;

    #[ApiProperty(writable: false)]
    public ?string $locale = null;

    #[ApiProperty(writable: false)]
    public ?int $attributeFamilyId = null;

    #[ApiProperty(writable: false)]
    public ?string $attributeFamilyName = null;

    #[ApiProperty(writable: false)]
    public ?string $urlKey = null;

    #[ApiProperty(writable: false)]
    public ?bool $visibleIndividually = null;
}
