<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiResource;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminCustomerDetailDto
{
    use AcceptsCamelCaseWrites;

    /**
     * The id.
     */
    public ?int $id = null;

    /**
     * The first name.
     */
    public ?string $first_name = null;

    /**
     * The last name.
     */
    public ?string $last_name = null;

    /**
     * The name.
     */
    public ?string $name = null;

    /**
     * The email.
     */
    public ?string $email = null;

    /**
     * The phone.
     */
    public ?string $phone = null;

    /**
     * The gender.
     */
    public ?string $gender = null;

    /**
     * The date of birth.
     */
    public ?string $date_of_birth = null;

    /**
     * The channel id.
     */
    public ?int $channel_id = null;

    /**
     * The status.
     */
    public ?int $status = null;

    /**
     * The subscribed to news letter.
     */
    public ?bool $subscribed_to_news_letter = null;

    /**
     * The is verified.
     */
    public ?int $is_verified = null;

    /**
     * The is suspended.
     */
    public ?int $is_suspended = null;

    /**
     * The total addresses.
     */
    public ?int $total_addresses = null;

    /**
     * The total orders.
     */
    public ?int $total_orders = null;

    /**
     * The total amount spent.
     */
    public ?float $total_amount_spent = null;

    /**
     * The created at.
     */
    public ?string $created_at = null;

    /**
     * The updated at.
     */
    public ?string $updated_at = null;

    /**
     * The group.
     */
    public ?array $group = null;
}
