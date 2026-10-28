<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Models\AdminMagicAiImage;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\OperationFailedException;
use Webkul\MagicAI\Facades\MagicAI;

/**
 * Magic AI text and image generation, mirroring the admin MagicAIController::content and
 * ::image with the configuration gates the admin editor applies before offering the feature.
 */
class AdminMagicAiProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        AdminReadPermissions::authorize();

        $isImage = $operation->getClass() === AdminMagicAiImage::class;

        $input = $this->payload($data, $context, $isImage ? ['prompt', 'model', 'n', 'size', 'quality'] : ['prompt', 'model']);

        $this->assertEnabled($isImage ? 'image_generation' : 'text_generation');

        $this->validate($input, $isImage);

        try {
            $result = $isImage
                ? ['images' => MagicAI::generateImage($input['prompt'], array_filter([
                    'n' => $input['n'] ?? null,
                    'size' => $input['size'] ?? null,
                    'quality' => $input['quality'] ?? null,
                ], fn ($value) => $value !== null), $input['model'] ?? null)]
                : ['content' => MagicAI::generateContent($input['prompt'], $input['model'] ?? null)];
        } catch (\Exception $e) {
            if ($operation instanceof Mutation) {
                throw new OperationFailedException($e->getMessage());
            }

            return new JsonResponse(['message' => $e->getMessage()], 500);
        }

        if (! $operation instanceof Mutation) {
            return new JsonResponse($result, 200);
        }

        $class = $operation->getClass();
        $resource = new $class;
        $resource->id = 0;

        foreach ($result as $key => $value) {
            $resource->{$key} = $value;
        }

        return $resource;
    }

    /**
     * Collect the input from the DTO, the GraphQL args or the REST body.
     */
    protected function payload(mixed $data, array $context, array $keys): array
    {
        $args = $context['args']['input'] ?? [];

        $input = [];

        foreach ($keys as $key) {
            $value = is_object($data) && property_exists($data, $key) ? $data->{$key} : null;

            $input[$key] = $value ?? $args[$key] ?? request()->input($key);
        }

        return $input;
    }

    /**
     * Magic AI must be switched on globally and for the requested admin feature.
     */
    protected function assertEnabled(string $feature): void
    {
        if (
            ! core()->getConfigData('magic_ai.general.settings.enabled')
            || ! core()->getConfigData("magic_ai.admin_features.{$feature}.enabled")
        ) {
            throw new InvalidInputException(__('bagistoapi::app.admin.magic-ai.disabled'), 400);
        }
    }

    /**
     * Same rules as core's ContentGenerationRequest / ImageGenerationRequest.
     */
    protected function validate(array $input, bool $isImage): void
    {
        $rules = [
            'prompt' => ['required', 'string'],
            'model' => ['nullable', 'string'],
        ];

        if ($isImage) {
            $rules += [
                'n' => ['nullable', 'integer', 'min:1', 'max:10'],
                'size' => ['required', 'in:1:1,2:3,3:2'],
                'quality' => ['nullable', 'in:high,medium,low'],
            ];
        }

        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            throw new InvalidInputException($validator->errors()->first(), 422);
        }
    }
}
