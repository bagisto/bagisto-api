<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCustomerCreateInput
{
    /**
     * The first name.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $first_name = null;

    /**
     * The last name.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $last_name = null;

    /**
     * The email.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $email = null;

    /**
     * The phone.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $phone = null;

    /**
     * The gender.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $gender = null;

    /**
     * The date of birth.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $date_of_birth = null;

    /**
     * The customer group id.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $customer_group_id = null;

    /**
     * The channel id.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $channel_id = null;

    /**
     * The status.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $status = null;

    /**
     * The subscribed to news letter.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?bool $subscribed_to_news_letter = null;

    /**
     * The send password.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?bool $send_password = null;

    /**
     * The password.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $password = null;
}
