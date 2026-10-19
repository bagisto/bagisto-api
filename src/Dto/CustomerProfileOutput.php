<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

class CustomerProfileOutput
{
    /**
     * The id.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[SerializedName('id')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * The id.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[SerializedName('_id')]
    #[Groups(['mutation'])]
    public ?string $_id = null;

    /**
     * The first name.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[SerializedName('firstName')]
    #[Groups(['mutation'])]
    public ?string $firstName = null;

    /**
     * The last name.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[SerializedName('lastName')]
    #[Groups(['mutation'])]
    public ?string $lastName = null;

    /**
     * The email.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $email = null;

    /**
     * The phone.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $phone = null;

    /**
     * The gender.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $gender = null;

    /**
     * The date of birth.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[SerializedName('dateOfBirth')]
    #[Groups(['mutation'])]
    public ?string $dateOfBirth = null;

    /**
     * The status.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $status = null;

    /**
     * The subscribed to news letter.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[SerializedName('subscribedToNewsLetter')]
    #[Groups(['mutation'])]
    public ?bool $subscribedToNewsLetter = null;

    /**
     * The is verified.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[SerializedName('isVerified')]
    #[Groups(['mutation'])]
    public ?string $isVerified = null;

    /**
     * The is suspended.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[SerializedName('isSuspended')]
    #[Groups(['mutation'])]
    public ?string $isSuspended = null;

    /**
     * The image.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $image = null;

    /**
     * The success.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?bool $success = null;

    /**
     * The message.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $message = null;

    /**
     * Create a new instance.
     */
    public function __construct(
        ?string $id = null,
        ?string $_id = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $email = null,
        ?string $phone = null,
        ?string $gender = null,
        ?string $dateOfBirth = null,
        ?string $status = null,
        ?bool $subscribedToNewsLetter = null,
        ?string $isVerified = null,
        ?string $isSuspended = null,
        ?string $image = null,
        ?bool $success = null,
        ?string $message = null,
    ) {
        $this->id = $id;
        $this->_id = $_id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phone = $phone;
        $this->gender = $gender;
        $this->dateOfBirth = $dateOfBirth;
        $this->status = $status;
        $this->subscribedToNewsLetter = $subscribedToNewsLetter;
        $this->isVerified = $isVerified;
        $this->isSuspended = $isSuspended;
        $this->image = $image;
        $this->success = $success;
        $this->message = $message;
    }
}
