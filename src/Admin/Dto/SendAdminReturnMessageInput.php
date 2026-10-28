<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class SendAdminReturnMessageInput
{
    /**
     * The return id.
     */
    #[ApiProperty(description: 'Return (RMA) id the message belongs to')]
    #[Groups(['mutation'])]
    public ?int $return_id = null;

    /**
     * The message.
     */
    #[ApiProperty(description: 'Message body')]
    #[Groups(['mutation'])]
    public ?string $message = null;

    /**
     * Get return id.
     */
    public function getReturn_id(): ?int
    {
        return $this->return_id;
    }

    /**
     * Set return id.
     */
    public function setReturn_id(?int $value): void
    {
        $this->return_id = $value;
    }

    /**
     * Get message.
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * Set message.
     */
    public function setMessage(?string $value): void
    {
        $this->message = $value;
    }
}
