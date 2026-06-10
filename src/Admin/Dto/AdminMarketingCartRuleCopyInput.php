<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input for the Cart Rule copy mutation. REST takes the id from the URI;
 * GraphQL passes it as `cartRuleId` (the `id` field is reserved as the IRI).
 */
class AdminMarketingCartRuleCopyInput
{
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $cartRuleId = null;
}
