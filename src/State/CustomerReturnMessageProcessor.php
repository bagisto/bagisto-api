<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\Mime\MimeTypes;
use Webkul\Admin\Mail\Admin\RMA\CustomerToAdminConversationNotification;
use Webkul\BagistoApi\Dto\SendCustomerReturnMessageInput;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\CustomerReturnMessage;
use Webkul\RMA\Repositories\RMAMessageRepository;
use Webkul\RMA\Repositories\RMARepository;

class CustomerReturnMessageProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
        private readonly RMARepository $rmaRepository,
        private readonly RMAMessageRepository $rmaMessageRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof SendCustomerReturnMessageInput) {
            return $this->handleSend($data->return_id, $data->message, null);
        }

        if ($data instanceof CustomerReturnMessage && $operation instanceof Post) {
            $returnId = request()->input('return_id');
            $message = request()->input('message');
            $file = request()->hasFile('file') ? request()->file('file') : null;

            return $this->handleSend($returnId !== null ? (int) $returnId : null, $message, $file);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    private function handleSend(?int $returnId, ?string $message, $file): CustomerReturnMessage
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        if (! $returnId) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.return.invalid-input'));
        }

        $rma = $this->rmaRepository
            ->whereHas('order', fn ($q) => $q->where('customer_id', $customer->id))
            ->find($returnId);

        if (! $rma) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.return.not-found'));
        }

        $stored = $this->rmaMessageRepository->create([
            'rma_id' => $rma->id,
            'message' => $message,
            'is_admin' => 0,
        ]);

        if ($file) {
            $extension = MimeTypes::getDefault()->getExtensions($file->getMimeType())[0] ?? null;

            $path = $file->storeAs(
                'rma-conversation/'.$stored->id,
                Str::random(40).($extension ? '.'.$extension : '')
            );

            $this->rmaMessageRepository->update([
                'attachment_path' => $path,
                'attachment' => $file->getClientOriginalName(),
            ], $stored->id);
        }

        try {
            Mail::queue(new CustomerToAdminConversationNotification($stored->refresh()));
        } catch (\Exception) {
        }

        return CustomerReturnMessage::fromModel($stored->refresh());
    }
}
