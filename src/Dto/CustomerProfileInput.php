<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for customer profile operations with token-based authentication
 * Token is passed via Authorization: Bearer header, NOT as input parameter
 *
 * NOTE: Token is NOT a DTO property. It is extracted from the Authorization header
 * via TokenHeaderFacade::getAuthorizationBearerToken() in the processor.
 */
class CustomerProfileInput
{
    /**
     * Customer ID (optional for get, required for update/delete when multiple profiles exist)
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * First name
     */
    #[Groups(['mutation'])]
    public ?string $firstName = null;

    /**
     * Last name
     */
    #[Groups(['mutation'])]
    public ?string $lastName = null;

    /**
     * Email address
     */
    #[Groups(['mutation'])]
    public ?string $email = null;

    /**
     * Phone number
     */
    #[Groups(['mutation'])]
    public ?string $phone = null;

    /**
     * Gender
     */
    #[Groups(['mutation'])]
    public ?string $gender = null;

    /**
     * Date of birth
     */
    #[Groups(['mutation'])]
    public ?string $dateOfBirth = null;

    /**
     * Current password (for password change verification)
     */
    #[Groups(['mutation'])]
    public ?string $password = null;

    /**
     * New password confirmation
     */
    #[Groups(['mutation'])]
    public ?string $confirmPassword = null;

    /**
     * Customer status
     */
    #[Groups(['mutation'])]
    public ?string $status = null;

    /**
     * Newsletter subscription
     */
    #[Groups(['mutation'])]
    public ?bool $subscribedToNewsLetter = null;

    /**
     * Verification status
     */
    #[Groups(['mutation'])]
    public ?string $isVerified = null;

    /**
     * Suspension status
     */
    #[Groups(['mutation'])]
    public ?string $isSuspended = null;

    /**
     * Customer profile image (base64 encoded)
     * Format: data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEA...
     */
    #[Groups(['mutation'])]
    public ?string $image = null;

    /**
     * Flag to delete existing image
     */
    #[Groups(['mutation'])]
    public ?bool $deleteImage = null;

    /**
     * Success status of the operation
     */
    #[Groups(['mutation'])]
    public ?bool $success = null;

    /**
     * Response message
     */
    #[Groups(['mutation'])]
    public ?string $message = null;
}
