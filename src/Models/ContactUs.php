<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Serializer\Attribute\Groups;
use Webkul\BagistoApi\Dto\ContactUsInput;
use Webkul\BagistoApi\Dto\ContactUsOutput;
use Webkul\BagistoApi\State\Processor\ContactUsProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ContactUs',
    operations: [
        new Post(
            name: 'submitContactUs',
            uriTemplate: '/contact-us',
            input: ContactUsInput::class,
            output: ContactUsOutput::class,
            processor: ContactUsProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            description: 'Submit a contact us inquiry',
            openapi: new Model\Operation(
                summary: 'Submit a contact us inquiry',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['name', 'email', 'message'],
                                'properties' => [
                                    'name'    => ['type' => 'string', 'example' => 'John Doe'],
                                    'email'   => ['type' => 'string', 'format' => 'email', 'example' => 'john@example.com'],
                                    'contact' => ['type' => 'string', 'example' => '+1234567890'],
                                    'message' => ['type' => 'string', 'example' => 'I have a question about your products'],
                                ],
                            ],
                        ],
                    ]),
                ),
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: ContactUsInput::class,
            output: ContactUsOutput::class,
            processor: ContactUsProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            description: 'Submit a contact us inquiry',
        ),
    ]
)]
class ContactUs
{
    #[ApiProperty(readable: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(readable: false, writable: true)]
    #[Groups(['query', 'mutation'])]
    public ?string $name;

    #[ApiProperty(readable: false, writable: true)]
    #[Groups(['query', 'mutation'])]
    public ?string $email;

    #[ApiProperty(readable: false, writable: true)]
    #[Groups(['query', 'mutation'])]
    public ?string $contact;

    #[ApiProperty(readable: false, writable: true)]
    #[Groups(['query', 'mutation'])]
    public ?string $message;

    #[ApiProperty(readable: true, writable: false)]
    public bool $success;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $message_response;
}
