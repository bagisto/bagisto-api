<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * DTO for customer info returned from token verification
 */
class CustomerVerifyOutput
{
    #[ApiProperty(writable: false, readable: true)]
    #[SerializedName('id')]
    public ?int $id = null;

    #[ApiProperty(writable: false, readable: true)]
    #[SerializedName('firstName')]
    public ?string $firstName = null;

    #[ApiProperty(writable: false, readable: true)]
    #[SerializedName('lastName')]
    public ?string $lastName = null;

    #[ApiProperty(writable: false, readable: true)]
    #[SerializedName('email')]
    public ?string $email = null;

    #[ApiProperty(writable: false, readable: true)]
    #[SerializedName('isValid')]
    public ?bool $isValid = null;

    #[ApiProperty(writable: false, readable: true)]
    #[SerializedName('message')]
    public ?string $message = null;

    public function __construct(
        ?int $id = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $email = null,
        ?bool $isValid = null,
        ?string $message = null,
    ) {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->isValid = $isValid;
        $this->message = $message;
    }
}
