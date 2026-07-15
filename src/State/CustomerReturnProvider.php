<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\CustomerReturn;
use Webkul\BagistoApi\State\Concerns\BuildsCustomerReturn;
use Webkul\RMA\Repositories\RMARepository;

class CustomerReturnProvider implements ProviderInterface
{
    use BuildsCustomerReturn;

    public function __construct(
        private readonly RMARepository $rmaRepository,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        $isCollection = $operation instanceof GetCollection || $operation instanceof QueryCollection;

        return $isCollection
            ? $this->provideCollection($customer, $context)
            : $this->provideItem($customer, $uriVariables, $context);
    }

    private function provideItem(object $customer, array $uriVariables, array $context): CustomerReturn
    {
        $id = $uriVariables['id'] ?? $context['args']['id'] ?? null;

        if (! $id) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.return.not-found'));
        }

        $rma = $this->scopedQuery($customer->id)->find(is_string($id) ? basename($id) : $id);

        if (! $rma) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.return.not-found'));
        }

        return $this->buildCustomerReturn($rma, true, $this->rmaRepository);
    }

    private function provideCollection(object $customer, array $context): Paginator
    {
        $args = $context['args'] ?? [];
        $filters = $context['filters'] ?? [];

        $query = $this->scopedQuery($customer->id);

        $status = $args['status'] ?? $filters['status'] ?? request()->query('status');

        if ($status !== null && $status !== '') {
            $query->where('rma_status_id', (int) $status);
        }

        $perPage = isset($args['first']) ? (int) $args['first'] : (int) (request()->query('per_page') ?? 10);
        $perPage = max(1, min($perPage, 50));

        $offset = 0;

        if ($after = $args['after'] ?? null) {
            $decoded = base64_decode($after, true);
            $offset = ctype_digit((string) $decoded) ? ((int) $decoded + 1) : 0;
        } elseif ($page = (int) request()->query('page')) {
            $offset = max(0, ($page - 1) * $perPage);
        }

        $query->orderBy('id', 'desc');

        $total = (clone $query)->count();

        $items = $query->offset($offset)->limit($perPage)->get()
            ->map(fn ($rma) => $this->buildCustomerReturn($rma, false, $this->rmaRepository));

        $currentPage = $total > 0 ? (int) floor($offset / $perPage) + 1 : 1;

        return new Paginator(
            new LengthAwarePaginator($items, $total, $perPage, $currentPage, ['path' => request()->url()])
        );
    }

    private function scopedQuery(int $customerId)
    {
        return $this->rmaRepository->with(self::RETURN_RELATIONS)
            ->whereHas('order', fn ($q) => $q->where('customer_id', $customerId));
    }
}
