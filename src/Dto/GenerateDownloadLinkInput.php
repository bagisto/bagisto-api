<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;

class GenerateDownloadLinkInput
{
    /**
     * The downloadable link purchased id.
     */
    #[ApiProperty(description: 'ID of the purchased downloadable link')]
    public int $downloadableLinkPurchasedId;

    /**
     * Create a new instance.
     */
    public function __construct(int $downloadableLinkPurchasedId)
    {
        $this->downloadableLinkPurchasedId = $downloadableLinkPurchasedId;
    }
}
