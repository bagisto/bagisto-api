<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use Illuminate\Database\Eloquent\Model;
use Webkul\BagistoApi\Dto\GenerateDownloadLinkInput;
use Webkul\BagistoApi\State\DownloadableProductProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'DownloadableProductDownloadLink',
    uriTemplate: '/downloadable-product-download-links',
    operations: [],
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
    /**
     * The table.
     */
    protected $table = 'downloadable_product_download_links';

    /**
     * The fillable.
     */
    protected $fillable = [
        'token',
        'url',
        'downloadable_link_purchased_id',
        'expires_at',
    ];

    /**
     * The casts.
     */
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * The timestamps.
     */
    public $timestamps = true;
}
