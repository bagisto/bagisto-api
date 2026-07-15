<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class SendCustomerReturnMessageInput
{
    #[ApiProperty(description: 'Return (RMA) id the message belongs to')]
    #[Groups(['mutation'])]
    public ?int $return_id = null;

    #[ApiProperty(description: 'Message body')]
    #[Groups(['mutation'])]
    public ?string $message = null;

    public function getReturn_id(): ?int
    {
        return $this->return_id;
    }

    public function setReturn_id(?int $v): void
    {
        $this->return_id = $v;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $v): void
    {
        $this->message = $v;
    }
}
