<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use Webkul\BagistoApi\State\CategoryTreeProvider;

/**
 * Category Tree REST API Resource
 *
 * Provides hierarchical category tree structure via REST API
 * Endpoint: GET /api/shop/category-trees
 *
 * Query Parameters:
 * - parentId: Filter by parent category ID
 * - depth: Maximum depth to traverse (default: 4)
 */
#[ApiResource(
    routePrefix: '/api/shop',
    operations: [
        new GetCollection(
            uriTemplate: '/category-trees',
            provider: CategoryTreeProvider::class,
            paginationEnabled: false,
            description: 'Get hierarchical category tree structure. Use parentId to filter by parent, depth to control depth.',
        ),
    ],
)]
class CategoryTree
{
    /**
     * This class doesn't need any properties as it's just a DTO
     * The data is provided by the CategoryTreeProvider
     */
}
