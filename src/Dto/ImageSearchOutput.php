<?php

namespace Webkul\BagistoApi\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class ImageSearchOutput
{
    /**
     * The image url.
     */
    #[Groups(['mutation'])]
    public ?string $imageUrl = null;

    /**
     * The keywords.
     */
    #[Groups(['mutation'])]
    public string $keywords = '';

    /**
     * The engine.
     */
    #[Groups(['mutation'])]
    public string $engine = 'tensorflow';
}
