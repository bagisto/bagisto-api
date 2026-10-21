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

    #[ApiProperty(description: 'Attachment as a base64 data URI (data:image/png;base64,...), max 5 MB. Allowed types follow Configuration > Sales > RMA > Allowed file extension.')]
    #[Groups(['mutation'])]
    public ?string $attachment = null;

    #[ApiProperty(description: 'File name shown for the attachment. Defaults to a generated name.')]
    #[Groups(['mutation'])]
    public ?string $attachment_name = null;

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

    public function getAttachment(): ?string
    {
        return $this->attachment;
    }

    public function setAttachment(?string $v): void
    {
        $this->attachment = $v;
    }

    public function getAttachment_name(): ?string
    {
        return $this->attachment_name;
    }

    public function setAttachment_name(?string $v): void
    {
        $this->attachment_name = $v;
    }
}
