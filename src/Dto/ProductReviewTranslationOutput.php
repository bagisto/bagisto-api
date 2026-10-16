<?php

namespace Webkul\BagistoApi\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class ProductReviewTranslationOutput
{
    #[Groups(['query'])]
    public ?string $content = null;
}
