<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Attribute\Groups;

class AdminTransactionCreateInput
{
    #[ApiProperty(description: 'The invoice to record the payment against.')]
    #[Groups(['mutation'])]
    public ?int $invoiceId = null;

    #[ApiProperty(description: 'Payment method code (e.g. cashondelivery, moneytransfer).')]
    #[Groups(['mutation'])]
    public ?string $paymentMethod = null;

    #[ApiProperty(description: 'Amount paid. Must be > 0 and cannot push the invoice total above its grand total.')]
    #[Groups(['mutation'])]
    public ?float $amount = null;
}
