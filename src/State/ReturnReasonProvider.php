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
    public function __construct(
        private readonly RMAReasonRepository $rmaReasonRepository,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        if (! Auth::guard('sanctum')->user()) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        $resolutionType = $context['args']['resolutionType']
            ?? request()->query('resolution_type');

        $valid = array_map(fn ($c) => $c->value, DefaultRMAResolution::cases());

        if (! in_array($resolutionType, $valid, true)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.return.invalid-resolution-type'));
        }

        return $this->rmaReasonRepository
            ->getRMAReasonsByResolutionType($resolutionType)
            ->map(function ($reason) {
                $r = new ReturnReason;
                $r->id = (int) $reason->id;
                $r->title = $reason->title;
                $r->position = $reason->position !== null ? (int) $reason->position : null;

                return $r;
            })
            ->values()
            ->all();
    }
}
