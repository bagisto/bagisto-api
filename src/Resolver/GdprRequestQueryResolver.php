<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Models\GdprRequest;
use Webkul\BagistoApi\Service\GenericIdNormalizer;

class GdprRequestQueryResolver implements QueryItemResolverInterface
{
    public function __invoke(?object $item, array $context): object
    {
        if (! core()->getConfigData('general.gdpr.settings.enabled')) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.gdpr.disabled'));
        }

        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.gdpr.unauthenticated'));
        }

        $id = $context['args']['id'] ?? null;

        if ($id === null || $id === '') {
            throw new BadRequestHttpException(__('bagistoapi::app.graphql.gdpr.not-found'));
        }

        $numericId = GenericIdNormalizer::extractNumericId($id);

        if ($numericId === null) {
            throw new BadRequestHttpException(__('bagistoapi::app.graphql.gdpr.not-found'));
        }

        $gdprRequest = GdprRequest::where('id', (int) $numericId)
            ->where('customer_id', $customer->id)
            ->first();

        if (! $gdprRequest) {
            throw new BadRequestHttpException(__('bagistoapi::app.graphql.gdpr.not-found'));
        }

        return $gdprRequest;
    }
}
