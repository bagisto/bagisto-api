<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Auth;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Models\ReturnCustomField;
use Webkul\BagistoApi\State\Concerns\ResolvesReturnCustomFields;
use Webkul\RMA\Repositories\RMACustomFieldRepository;

class ReturnCustomFieldProvider implements ProviderInterface
{
    use ResolvesReturnCustomFields;

    public function __construct(
        private readonly RMACustomFieldRepository $rmaCustomFieldRepository,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        if (! Auth::guard('sanctum')->user()) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        return $this->activeReturnCustomFields($this->rmaCustomFieldRepository)
            ->map(function ($field) {
                $row = new ReturnCustomField;
                $row->id = (int) $field->id;
                $row->code = $field->code;
                $row->label = $field->label;
                $row->type = $field->type;
                $row->is_required = (int) $field->is_required === 1;
                $row->position = $field->position !== null ? (int) $field->position : null;
                $row->input_validation = $field->input_validation;
                $row->options = $field->options->map(fn ($option) => [
                    'id' => (int) $option->id,
                    'name' => $option->name,
                    'value' => $option->value,
                ])->values()->all();

                return $row;
            })
            ->values()
            ->all();
    }
}
