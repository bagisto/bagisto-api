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
use Webkul\BagistoApi\Models\EuWithdrawal;
use Webkul\BagistoApi\State\Concerns\BuildsEuWithdrawal;
use Webkul\EUWithdrawal\Repositories\WithdrawalRepository;

class EuWithdrawalProvider implements ProviderInterface
{
    use BuildsEuWithdrawal;

    /**
     * Create a new instance.
     */
    public function __construct(private readonly WithdrawalRepository $withdrawals) {}

    /**
     * Provide.
     */
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

    /**
     * Scoped query.
     */
    private function scopedQuery(int $customerId)
    {
        return $this->withdrawals->getModel()
            ->newQuery()
            ->with('order')
            ->whereHas('order', fn ($query) => $query->where('customer_id', $customerId));
    }

    /**
     * Provide item.
     */
    private function provideItem(object $customer, array $uriVariables, array $context): EuWithdrawal
    {
        $id = $uriVariables['id'] ?? $context['args']['id'] ?? null;

        if (! $id) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.eu-withdrawal.not-found'));
        }

        $withdrawal = $this->scopedQuery($customer->id)->find(is_string($id) ? basename($id) : $id);

        if (! $withdrawal) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.eu-withdrawal.not-found'));
        }

        return $this->buildEuWithdrawal($withdrawal);
    }

    /**
     * Provide collection.
     */
    private function provideCollection(object $customer, array $context): Paginator
    {
        $args = $context['args'] ?? [];
        $first = isset($args['first']) ? (int) $args['first'] : null;
        $perPage = $first ?? (int) (request()->query('per_page', 30));
        $perPage = max(1, min($perPage, 50));

        $offset = 0;
        if ($after = $args['after'] ?? null) {
            $decoded = base64_decode($after, true);
            $offset = ctype_digit((string) $decoded) ? ((int) $decoded + 1) : 0;
        }

        $query = $this->scopedQuery($customer->id)->orderBy('id', 'desc');

        $total = (clone $query)->count();
        $items = $query->offset($offset)->limit($perPage)->get()
            ->map(fn ($withdrawal) => $this->buildEuWithdrawal($withdrawal));

        $page = $total > 0 ? (int) floor($offset / $perPage) + 1 : 1;

        return new Paginator(
            new LengthAwarePaginator($items, $total, $perPage, $page, ['path' => request()->url()])
        );
    }
}
