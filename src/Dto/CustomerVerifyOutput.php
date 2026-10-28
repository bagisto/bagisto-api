<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\SerializedName;

class CustomerVerifyOutput
{
    /**
     * The id.
     */
    #[ApiProperty(writable: false, readable: true)]
    #[SerializedName('id')]
    public ?int $id = null;

    /**
     * The first name.
     */
    #[ApiProperty(writable: false, readable: true)]
    #[SerializedName('firstName')]
    public ?string $firstName = null;

    /**
     * The last name.
     */
    #[ApiProperty(writable: false, readable: true)]
    #[SerializedName('lastName')]
    public ?string $lastName = null;

    /**
     * The email.
     */
    #[ApiProperty(writable: false, readable: true)]
    #[SerializedName('email')]
    public ?string $email = null;

    /**
     * The is valid.
     */
    #[ApiProperty(writable: false, readable: true)]
    #[SerializedName('isValid')]
    public ?bool $isValid = null;

    /**
     * The message.
     */
    #[ApiProperty(writable: false, readable: true)]
    #[SerializedName('message')]
    public ?string $message = null;

    /**
     * Create a new instance.
     */
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
