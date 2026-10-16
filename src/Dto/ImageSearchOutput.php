<?php

namespace Webkul\BagistoApi\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class ImageSearchOutput
{
    #[Groups(['mutation'])]
    public ?string $imageUrl = null;

    #[Groups(['mutation'])]
    public string $keywords = '';

    #[Groups(['mutation'])]
    public string $engine = 'tensorflow';
}
