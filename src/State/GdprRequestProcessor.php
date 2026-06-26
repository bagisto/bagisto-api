<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Webkul\BagistoApi\Dto\CreateGdprRequestInput;
use Webkul\BagistoApi\Dto\DeleteGdprRequestInput;
use Webkul\BagistoApi\Dto\RevokeGdprRequestInput;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\GdprRequest;
use Webkul\BagistoApi\State\Concerns\GdprFeatureGate;

class GdprRequestProcessor implements ProcessorInterface
{
    use GdprFeatureGate;

    private const ACTIVE_STATUSES = ['pending', 'processing'];

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $this->assertGdprEnabled();

        $operationName = $operation->getName();

        if ($data instanceof CreateGdprRequestInput) {
            $this->hydrateCreateInput($data, $context);

            return $this->handleCreate($data);
        }

        if ($data instanceof RevokeGdprRequestInput) {
            return $this->handleRevoke($this->resolveId($data->id, $uriVariables, $context));
        }

        if ($data instanceof DeleteGdprRequestInput) {
            return $this->handleDelete($this->resolveId($data->id, $uriVariables, $context), $operation);
        }

        if ($operationName === 'revoke_post' || $operationName === 'revoke') {
            return $this->handleRevoke($this->resolveId(null, $uriVariables, $context));
        }

        if ($data instanceof GdprRequest && $operation instanceof \ApiPlatform\Metadata\Post) {
            $input = new CreateGdprRequestInput;
            $input->type = request()->input('type');
            $input->message = request()->input('message');

            return $this->handleCreate($input);
        }

        if ($operation instanceof \ApiPlatform\Metadata\Delete) {
            return $this->handleDelete($this->resolveId(null, $uriVariables, $context), $operation);
        }

        return $this->handleCreate($this->buildCreateFromRequest());
    }

    private function handleCreate(CreateGdprRequestInput $input): GdprRequest
    {
        $customer = $this->resolveCustomer();

        $type = is_string($input->type) ? strtolower(trim($input->type)) : null;

        if (empty($type)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.gdpr.type-required'));
        }

        if (! in_array($type, ['delete', 'update'], true)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.gdpr.type-invalid'));
        }

        $message = is_string($input->message) ? trim($input->message) : '';

        if ($message === '') {
            throw new InvalidInputException(__('bagistoapi::app.graphql.gdpr.message-required'));
        }

        $this->safeDispatch('customer.account.gdpr-request.create.before', null);

        $gdprRequest = GdprRequest::create([
            'customer_id' => $customer->id,
            'email'       => $customer->email,
            'status'      => 'pending',
            'type'        => $type,
            'message'     => $message,
        ]);

        $this->safeDispatch('customer.account.gdpr-request.create.after', $gdprRequest);

        $this->safeDispatch('customer.gdpr-request.create.after', $gdprRequest);

        $gdprRequest->setResponseMessage(__('bagistoapi::app.graphql.gdpr.raised'));

        return $gdprRequest;
    }

    private function handleRevoke(int $id): GdprRequest
    {
        $customer = $this->resolveCustomer();

        $gdprRequest = GdprRequest::where('id', $id)
            ->where('customer_id', $customer->id)
            ->first();

        if (! $gdprRequest) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.gdpr.not-found'));
        }

        if (! in_array($gdprRequest->status, self::ACTIVE_STATUSES, true)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.gdpr.revoke-not-allowed'), 422);
        }

        $this->safeDispatch('customer.account.gdpr-request.update.before', null);

        $gdprRequest->update([
            'status'     => 'revoked',
            'revoked_at' => Carbon::now(),
        ]);

        $this->safeDispatch('customer.account.gdpr-request.update.after', $gdprRequest);

        $this->safeDispatch('customer.gdpr-request.update.after', $gdprRequest);

        $gdprRequest->setResponseMessage(__('bagistoapi::app.graphql.gdpr.revoked'));

        return $gdprRequest->fresh();
    }

    private function handleDelete(int $id, Operation $operation): mixed
    {
        $customer = $this->resolveCustomer();

        $gdprRequest = GdprRequest::where('id', $id)
            ->where('customer_id', $customer->id)
            ->first();

        if (! $gdprRequest) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.gdpr.not-found'));
        }

        $snapshot = clone $gdprRequest;

        $gdprRequest->delete();

        if ($operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation) {
            $snapshot->setResponseMessage(__('bagistoapi::app.graphql.gdpr.deleted'));

            return $snapshot;
        }

        return null;
    }

    private function buildCreateFromRequest(): CreateGdprRequestInput
    {
        $input = new CreateGdprRequestInput;
        $input->type = request()->input('type');
        $input->message = request()->input('message');

        return $input;
    }

    private function hydrateCreateInput(CreateGdprRequestInput $input, array $context): void
    {
        $args = $context['args']['input'] ?? $context['args'] ?? null;

        if (is_array($args)) {
            if ($input->type === null && isset($args['type'])) {
                $input->type = $args['type'];
            }

            if ($input->message === null && isset($args['message'])) {
                $input->message = $args['message'];
            }
        }

        if ($input->type === null) {
            $input->type = request()->input('type');
        }

        if ($input->message === null) {
            $input->message = request()->input('message');
        }
    }

    private function resolveId(?string $rawId, array $uriVariables, array $context): int
    {
        $id = $rawId
            ?? ($uriVariables['id'] ?? null)
            ?? ($context['args']['input']['id'] ?? null)
            ?? ($context['args']['id'] ?? null);

        if ($id === null || $id === '') {
            throw new InvalidInputException(__('bagistoapi::app.graphql.gdpr.not-found'), 404);
        }

        $numericId = (int) basename((string) $id);

        if ($numericId <= 0) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.gdpr.not-found'), 404);
        }

        return $numericId;
    }

    private function resolveCustomer(): object
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.gdpr.unauthenticated'));
        }

        return $customer;
    }

    private function safeDispatch(string $event, mixed $payload): void
    {
        try {
            Event::dispatch($event, $payload);
        } catch (\Throwable $e) {
            Log::warning('BagistoApi gdpr event listener failed', [
                'event'   => $event,
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
        }
    }
}
