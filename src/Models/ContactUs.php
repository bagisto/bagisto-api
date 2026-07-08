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
                'groups' => ['mutation'],
            ],
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            description: 'Submit a contact us inquiry',
            openapi: new Model\Operation(
                tags: ['ContactUs'],
                summary: 'Submit a contact us inquiry',
                description: 'Submits a contact-us inquiry. Public endpoint. `name`, `email` and `message` are required; `contact` (phone) is optional.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['name', 'email', 'message'],
                                'properties' => [
                                    'name' => ['type' => 'string', 'example' => 'John Doe'],
                                    'email' => ['type' => 'string', 'format' => 'email', 'example' => 'john@example.com'],
                                    'contact' => ['type' => 'string', 'example' => '+1234567890'],
                                    'message' => ['type' => 'string', 'example' => 'I have a question about your products'],
                                ],
                            ],
                            'example' => [
                                'name' => 'John Doe',
                                'email' => 'john@example.com',
                                'contact' => '1234567890',
                                'message' => 'Do you ship internationally?',
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'Inquiry submitted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'success' => true,
                                    'message' => 'Your inquiry has been submitted successfully. We will get back to you soon',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Validation failed (missing name/email/message, invalid email).'),
                ],
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
                'groups' => ['mutation'],
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
