<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Resolver\CustomerQueryResolver;
use Webkul\BagistoApi\State\CustomerProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'Customer',
    operations: [
        new \ApiPlatform\Metadata\GetCollection(
            normalizationContext: [
                'skip_null_values' => false,
            ],
        ),
        new \ApiPlatform\Metadata\Get(
            normalizationContext: [
                'skip_null_values' => false,
            ],
        ),
        new \ApiPlatform\Metadata\Post(
            input: self::class,
            output: self::class,
            processor: CustomerProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            normalizationContext: [
                'skip_null_values' => false,
            ],
            openapi: new Model\Operation(
                summary: 'Create a new customer',
                description: 'Register a new customer account',
                requestBody: new Model\RequestBody(
                    description: 'Customer registration data',
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['firstName', 'lastName', 'email', 'password', 'confirmPassword'],
                                'properties' => [
                                    'firstName'              => ['type' => 'string', 'example' => 'John'],
                                    'lastName'               => ['type' => 'string', 'example' => 'Doe'],
                                    'email'                  => ['type' => 'string', 'format' => 'email', 'example' => 'john@example.com'],
                                    'password'               => ['type' => 'string', 'format' => 'password', 'example' => 'Password123!'],
                                    'confirmPassword'        => ['type' => 'string', 'format' => 'password', 'example' => 'Password123!'],
                                    'phone'                  => ['type' => 'string', 'example' => '1234567890'],
                                    'gender'                 => ['type' => 'string', 'enum' => ['Male', 'Female', 'Other']],
                                    'dateOfBirth'            => ['type' => 'string', 'format' => 'date', 'example' => '1990-01-15'],
                                    'subscribedToNewsLetter' => ['type' => 'boolean', 'example' => true],
                                ],
                            ],
                        ],
                    ]),
                ),
            ),
        ),
        new \ApiPlatform\Metadata\Put(
            input: self::class,
            output: self::class,
            processor: CustomerProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
        ),
        new \ApiPlatform\Metadata\Delete(
            input: self::class,
            output: self::class,
            processor: CustomerProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
        ),
    ],
    graphQlOperations: [
        new Query(resolver: CustomerQueryResolver::class),
        new Mutation(
            name: 'create',
            processor: CustomerProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
        ),
        new Mutation(
            name: 'update',
            processor: CustomerProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
        ),
        new Mutation(
            name: 'delete',
            processor: CustomerProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
        ),
    ]
)]
class Customer extends \Webkul\Customer\Models\Customer
{
    protected $appends = [
        'confirm_password',
    ];

    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'email',
        'phone',
        'password',
        'api_token',
        'token',
        'customer_group_id',
        'channel_id',
        'subscribed_to_news_letter',
        'status',
        'is_verified',
        'is_suspended',
        'image',
    ];

    protected $visible = [
        'id',
        'password',
        'api_token',
        'remember_token',
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'email',
        'phone',
        'password',
        'api_token',
        'token',
        'customer_group_id',
        'channel_id',
        'subscribed_to_news_letter',
        'status',
        'is_verified',
        'is_suspended',
        'confirm_password',
        'name',
        'createdAt',
        'updatedAt',
        'imageUrl',
    ];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[ApiProperty(readable: true, writable: false)]
    public function get_id(): ?int
    {
        return $this->id;
    }

    #[ApiProperty(
        writable: true,
        readable: true,
        required: true
    )]
    #[Groups(['mutation'])]
    public function getFirst_name(): ?string
    {
        return $this->first_name;
    }

    public function setFirst_name(?string $value): void
    {
        $this->first_name = $value;
    }

    #[ApiProperty(
        writable: true,
        readable: true,
        required: true
    )]
    #[Groups(['mutation'])]
    public function getLast_name(): ?string
    {
        return $this->last_name;
    }

    public function setLast_name(?string $value): void
    {
        $this->last_name = $value;
    }

    #[ApiProperty(
        writable: true,
        readable: true,
        required: true
    )]
    #[Groups(['mutation'])]
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $value): void
    {
        $this->email = $value;
    }

    #[ApiProperty(
        writable: true,
        readable: true,
        required: false
    )]
    #[Groups(['mutation'])]
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $value): void
    {
        $this->phone = $value;
    }

    #[ApiProperty(
        writable: true,
        readable: true,
        required: false
    )]
    #[Groups(['mutation'])]
    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $value): void
    {
        $this->gender = $value;
    }

    #[ApiProperty(
        writable: true,
        readable: true,
        required: false
    )]
    #[Groups(['mutation'])]
    public function getDate_of_birth(): ?string
    {
        return $this->date_of_birth;
    }

    public function setDate_of_birth(?string $value): void
    {
        $this->date_of_birth = $value;
    }

    #[ApiProperty(
        writable: true,
        readable: false,
        required: false
    )]
    #[Groups(['mutation'])]
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $value): void
    {
        $this->password = $value;
    }

    #[ApiProperty(
        writable: true,
        readable: false,
        required: false
    )]
    #[Groups(['mutation'])]
    public ?string $confirm_password = null;

    public function getConfirm_passwordAttribute(): ?string
    {
        return $this->confirm_password;
    }

    public function getConfirm_password(): ?string
    {
        return $this->getConfirm_passwordAttribute();
    }

    public function setConfirm_password(?string $value): void
    {
        $this->confirm_password = $value;
    }

    #[ApiProperty(
        writable: true,
        readable: true,
        required: false
    )]
    #[Groups(['mutation'])]
    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $value): void
    {
        $this->status = $value;
    }

    #[ApiProperty(
        writable: true,
        readable: true,
        required: false
    )]
    #[Groups(['mutation'])]
    public function getIs_verified(): ?string
    {
        return $this->is_verified;
    }

    public function setIs_verified(?string $value): void
    {
        $this->is_verified = $value;
    }

    #[ApiProperty(
        writable: true,
        readable: true,
        required: false
    )]
    #[Groups(['mutation'])]
    public function getIs_suspended(): ?string
    {
        return $this->is_suspended;
    }

    public function setIs_suspended(?string $value): void
    {
        $this->is_suspended = $value;
    }

    #[ApiProperty(
        writable: true,
        readable: true,
        required: false
    )]
    #[Groups(['mutation'])]
    public function getSubscribed_to_news_letter(): ?bool
    {
        return $this->subscribed_to_news_letter;
    }

    public function setSubscribed_to_news_letter(?bool $value): void
    {
        $this->subscribed_to_news_letter = $value;
    }

    #[ApiProperty(writable: false, readable: true)]
    #[Groups(['mutation'])]
    public function getApi_token(): ?string
    {
        return $this->api_token;
    }

    public function setApi_token(?string $value): void
    {
        $this->api_token = $value;
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $value): void
    {
        $this->token = $value;
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getCustomer_group_id(): ?int
    {
        return $this->customer_group_id;
    }

    public function setCustomer_group_id(?int $value): void
    {
        $this->customer_group_id = $value;
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getChannel_id(): ?int
    {
        return $this->channel_id;
    }

    public function setChannel_id(?int $value): void
    {
        $this->channel_id = $value;
    }

    #[ApiProperty(
        writable: true,
        readable: true,
        required: false,
        description: 'Customer profile image URL or base64 encoded image'
    )]
    #[Groups(['mutation'])]
    public function getImage(): ?string
    {
        if ($this->image) {
            return Storage::url($this->image);
        }

        return null;
    }

    public function setImage(?string $value): void
    {
        $this->image = $value;
    }

    /**
     * Get addresses for the customer
     */
    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class, 'customer_id');
    }
}
