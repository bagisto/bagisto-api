<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Auth;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Models\ReturnReason;
use Webkul\RMA\Enums\DefaultRMAResolution;
use Webkul\RMA\Repositories\RMAReasonRepository;

class ReturnReasonProvider implements ProviderInterface
{
    /**
     * Create a new instance.
     */
    public function __construct(
        private readonly RMAReasonRepository $rmaReasonRepository,
    ) {}

    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        if (! Auth::guard('sanctum')->user()) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        $resolutionType = $context['args']['resolutionType']
            ?? request()->query('resolution_type');

        $valid = array_map(fn ($resolution) => $resolution->value, DefaultRMAResolution::cases());

        if (! in_array($resolutionType, $valid, true)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.return.invalid-resolution-type'));
        }

        return $this->rmaReasonRepository
            ->getRMAReasonsByResolutionType($resolutionType)
            ->map(function ($reason) {
                $returnReason = new ReturnReason;
                $returnReason->id = (int) $reason->id;
                $returnReason->title = $reason->title;
                $returnReason->position = $reason->position !== null ? (int) $reason->position : null;

                return $returnReason;
            })
            ->values()
            ->all();
    }
}
