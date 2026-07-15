<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\Mime\MimeTypes;
use Webkul\BagistoApi\Admin\Dto\SendAdminReturnMessageInput;
use Webkul\BagistoApi\Admin\Models\AdminReturnMessage;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\RMA\Repositories\RMAMessageRepository;
use Webkul\RMA\Repositories\RMARepository;
use Webkul\Shop\Mail\Customer\RMA\AdminToCustomerConversationNotification;

class AdminReturnMessageProcessor implements ProcessorInterface
{
    use ChecksAdminPermission;

    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
        private readonly RMARepository $rmaRepository,
        private readonly RMAMessageRepository $rmaMessageRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof SendAdminReturnMessageInput) {
            return $this->handleSend($data->return_id, $data->message, null);
        }

        if ($data instanceof AdminReturnMessage && $operation instanceof Post) {
            $returnId = request()->input('return_id');
            $file = request()->hasFile('file') ? request()->file('file') : null;

            return $this->handleSend($returnId !== null ? (int) $returnId : null, request()->input('message'), $file);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    private function handleSend(?int $returnId, ?string $message, $file): AdminReturnMessage
    {
        $this->authorizedAdmin('sales.rma.requests', 'bagistoapi::app.admin.rma.no-permission');

        if (! $returnId) {
            throw new InvalidInputException(__('bagistoapi::app.admin.rma.invalid-input'), 422);
        }

        if (! $this->rmaRepository->find($returnId)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.rma.not-found'));
        }

        $stored = $this->rmaMessageRepository->create([
            'rma_id' => $returnId,
            'message' => $message,
            'is_admin' => 1,
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
            Mail::queue(new AdminToCustomerConversationNotification($stored->refresh()));
        } catch (\Exception) {
        }

        return AdminReturnMessage::fromModel($stored->refresh());
    }
}
