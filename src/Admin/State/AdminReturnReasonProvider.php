<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Models\AdminReturnReason;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\RMA\Enums\DefaultRMAResolution;
use Webkul\RMA\Repositories\RMAReasonRepository;

class AdminReturnReasonProvider implements ProviderInterface
{
    use ChecksAdminPermission;

    public function __construct(
        private readonly RMAReasonRepository $rmaReasonRepository,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $this->authorizedAdmin('sales.rma.requests.create', 'bagistoapi::app.admin.rma.no-permission');

        $resolutionType = $context['args']['resolutionType'] ?? request()->query('resolution_type');

        $valid = array_map(fn ($c) => $c->value, DefaultRMAResolution::cases());

        if (! in_array($resolutionType, $valid, true)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.rma.invalid-resolution-type'), 422);
        }

        return $this->rmaReasonRepository
            ->getRMAReasonsByResolutionType($resolutionType)
            ->map(function ($reason) {
                $r = new AdminReturnReason;
                $r->id = (int) $reason->id;
                $r->title = $reason->title;
                $r->position = $reason->position !== null ? (int) $reason->position : null;

                return $r;
            })
            ->values()
            ->all();
    }
}
