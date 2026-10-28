<?php

namespace Webkul\BagistoApi\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class ProductReviewTranslationOutput
{
    /**
     * The content.
     */
    #[Groups(['query'])]
    public ?string $content = null;
}
