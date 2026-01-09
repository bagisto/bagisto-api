<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use Illuminate\Database\Eloquent\Model;
use Webkul\BagistoApi\Dto\GenerateDownloadLinkInput;
use Webkul\BagistoApi\State\DownloadableProductProcessor;

/**
 * Temporary download link for purchased downloadable products.
 *
 * Stores secure tokens and metadata for file downloads with automatic expiration.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'DownloadableProductDownloadLink',
    uriTemplate: '/downloadable-product-download-links',
    operations: [
        new Get(uriTemplate: '/downloadable-product-download-links/{id}'),
        new GetCollection(uriTemplate: '/downloadable-product-download-links'),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            processor: DownloadableProductProcessor::class,
            input: GenerateDownloadLinkInput::class,
        ),
    ]
)]
class DownloadableProductDownloadLink extends Model
{
    protected $table = 'downloadable_product_download_links';

    protected $fillable = [
        'token',
        'url',
        'downloadable_link_purchased_id',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public $timestamps = true;
}
