<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Attribute\Groups;

class AdminTransactionCreateInput
{
    /**
     * The invoice id.
     */
    #[ApiProperty(description: 'The invoice to record the payment against.')]
    #[Groups(['mutation'])]
    public ?int $invoiceId = null;

    /**
     * The payment method.
     */
    #[ApiProperty(description: 'Payment method code (e.g. cashondelivery, moneytransfer).')]
    #[Groups(['mutation'])]
    public ?string $paymentMethod = null;

    /**
     * The amount.
     */
    #[ApiProperty(description: 'Amount paid. Must be > 0 and cannot push the invoice total above its grand total.')]
    #[Groups(['mutation'])]
    public ?float $amount = null;
}
