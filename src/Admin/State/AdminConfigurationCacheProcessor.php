<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Webkul\Admin\Services\CacheManagerService;
use Webkul\BagistoApi\Admin\Dto\AdminConfigurationCacheInput;
use Webkul\BagistoApi\Admin\Models\AdminConfigurationCache;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Exception\InvalidInputException;

/**
 * Mirrors the admin CacheManagementController::execute through core's CacheManagerService,
 * which only runs actions from its own allow-list.
 */
class AdminConfigurationCacheProcessor implements ProcessorInterface
{
    public function __construct(protected CacheManagerService $cacheManager) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        AdminReadPermissions::authorize(null, null, 'configuration');

        $isGraphQL = $operation instanceof Mutation;

        $action = $isGraphQL
            ? ($context['args']['input']['action'] ?? ($data instanceof AdminConfigurationCacheInput ? $data->action : null))
            : request()->input('action');

        $validator = Validator::make(['action' => $action], ['action' => 'required|string']);

        if ($validator->fails()) {
            throw new InvalidInputException($validator->errors()->first(), 422);
        }

        $result = $this->cacheManager->execute($action);

        if (! $isGraphQL) {
            return new JsonResponse([
                'success' => $result['success'],
                'message' => $result['message'],
                'output' => $result['output'],
                'command' => $result['command'],
            ], $result['success'] ? 200 : 422);
        }

        if (! $result['success']) {
            throw new InvalidInputException($result['message'], 422);
        }

        $output = new AdminConfigurationCache;
        $output->action = $action;
        $output->success = true;
        $output->message = $result['message'];
        $output->output = $result['output'];
        $output->command = $result['command'];

        return $output;
    }
}
