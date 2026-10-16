<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use ReflectionMethod;
use Webkul\BagistoApi\Exception\InvalidInputException;

/**
 * Shared plumbing for the REST-only admin media upload endpoints: path variables,
 * file validation and the JSON detail response.
 */
trait HandlesAdminMediaUpload
{
    protected function pathVariable(array $uriVariables, string $key): ?string
    {
        $value = $uriVariables[$key] ?? request()->route($key);

        return is_null($value) ? null : (string) $value;
    }

    /**
     * Validate the uploaded file and any extra fields, throwing 422 on failure.
     */
    protected function validatedUpload(string $field, string|array $fileRules, array $extraRules = []): UploadedFile
    {
        $file = request()->file($field);

        if (is_array($file)) {
            $file = current($file) ?: null;
        }

        if (! $file instanceof UploadedFile) {
            throw new InvalidInputException(__('bagistoapi::app.admin.media.file-required', ['field' => $field]), 422);
        }

        $validator = Validator::make(
            array_merge(request()->except($field), [$field => $file]),
            array_merge([$field => array_merge(['required', 'file'], (array) $fileRules)], $extraRules),
        );

        if ($validator->fails()) {
            throw new InvalidInputException($validator->errors()->first(), 422);
        }

        return $file;
    }

    /**
     * Map an item provider's detail DTO and return it as the REST response body.
     */
    protected function detailResponse(object $itemProvider, object $entity, int $status = 200): JsonResponse
    {
        $dto = (new ReflectionMethod($itemProvider, 'mapToDto'))->invoke($itemProvider, $entity);

        return $this->dtoResponse($dto, $status);
    }

    protected function dtoResponse(object $dto, int $status = 200): JsonResponse
    {
        $body = [];

        foreach (get_object_vars($dto) as $key => $value) {
            $body[Str::camel($key)] = $value;
        }

        return new JsonResponse($body, $status);
    }
}
