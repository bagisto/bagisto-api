<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCustomerCreateInput
{
    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $first_name = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $last_name = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $email = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $phone = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $gender = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $date_of_birth = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $customer_group_id = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $channel_id = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $status = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?bool $subscribed_to_news_letter = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?bool $send_password = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $password = null;
}
