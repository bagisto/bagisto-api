<?php

namespace Webkul\BagistoApi\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class CheckoutMessageOutput
{
    /**
     * The AI-generated checkout success message, or null when the feature is off or generation failed.
     */
    #[Groups(['query'])]
    public ?string $message = null;
}
