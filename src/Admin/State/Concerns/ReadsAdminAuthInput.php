<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

use ApiPlatform\Metadata\GraphQl\Operation as GraphQlOperation;
use ApiPlatform\Metadata\Operation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Webkul\BagistoApi\Exception\InvalidInputException;

trait ReadsAdminAuthInput
{
    /**
     * The request input with snake_case keys, from the GraphQL input argument or the REST body, never the query string.
     */
    protected function authInput(Operation $operation, array $context): array
    {
        $raw = $operation instanceof GraphQlOperation
            ? (array) ($context['args']['input'] ?? [])
            : request()->request->all();

        unset($raw['clientMutationId']);

        $input = [];

        foreach ($raw as $key => $value) {
            $input[Str::snake((string) $key)] = $value;
        }

        return $input;
    }

    /**
     * Validate the input, failing with the first message as a 422.
     *
     * @throws InvalidInputException
     */
    protected function validateAuthInput(array $input, array $rules): array
    {
        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            throw new InvalidInputException($validator->errors()->first(), 422);
        }

        return $validator->validated();
    }

    /**
     * The numeric id at the end of an IRI or an id, from the URI or the GraphQL input.
     */
    protected function idFromRequest(array $uriVariables, array $context): int
    {
        $id = $uriVariables['id'] ?? $context['args']['input']['id'] ?? $context['args']['id'] ?? '';

        $id = basename((string) $id);

        return ctype_digit($id)
            ? (int) $id
            : 0;
    }
}
