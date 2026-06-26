<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiResource;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminCustomerDetailDto
{
    use AcceptsCamelCaseWrites;

    public ?int $id = null;

    public ?string $first_name = null;

    public ?string $last_name = null;

    public ?string $name = null;

    public ?string $email = null;

    public ?string $phone = null;

    public ?string $gender = null;

    public ?string $date_of_birth = null;

    public ?int $channel_id = null;

    public ?int $status = null;

    public ?bool $subscribed_to_news_letter = null;

    public ?int $is_verified = null;

    public ?int $is_suspended = null;

    public ?int $total_addresses = null;

    public ?int $total_orders = null;

    public ?float $total_amount_spent = null;

    public ?string $created_at = null;

    public ?string $updated_at = null;

    public ?array $group = null;
}
