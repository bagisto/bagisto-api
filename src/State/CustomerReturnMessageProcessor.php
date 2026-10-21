<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Mime\MimeTypes;
use Webkul\Admin\Mail\Admin\RMA\CustomerToAdminConversationNotification;
use Webkul\BagistoApi\Dto\SendCustomerReturnMessageInput;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\CustomerReturnMessage;
use Webkul\BagistoApi\State\Concerns\DecodesBase64Files;
use Webkul\RMA\Repositories\RMAMessageRepository;
use Webkul\RMA\Repositories\RMARepository;

class CustomerReturnMessageProcessor implements ProcessorInterface
{
    use DecodesBase64Files;

    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
        private readonly RMARepository $rmaRepository,
        private readonly RMAMessageRepository $rmaMessageRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof SendCustomerReturnMessageInput) {
            $input = $context['args']['input'] ?? [];

            return $this->handleSend(
                $data->return_id,
                $data->message,
                $data->attachment ?? $input['attachment'] ?? null,
                $data->attachment_name ?? $input['attachmentName'] ?? null,
            );
        }

        if ($operation instanceof Post) {
            $returnId = request()->input('return_id', request()->input('returnId'));

            return $this->handleSend(
                $returnId !== null ? (int) $returnId : null,
                request()->input('message'),
                request()->hasFile('file') ? request()->file('file') : $this->stringInput('attachment'),
                $this->stringInput('attachment_name') ?? $this->stringInput('attachmentName'),
            );
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    /**
     * @param  UploadedFile|string|null  $file  multipart upload or base64 data URI
     */
    private function handleSend(?int $returnId, ?string $message, UploadedFile|string|null $file, ?string $fileName): CustomerReturnMessage
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

        $allowedTypes = array_filter(array_map(
            'trim',
            explode(',', (string) core()->getConfigData('sales.rma.setting.allowed_file_extension'))
        ));

        if (is_string($file)) {
            $file = $file === '' ? null : $this->decodeBase64File($file, $allowedTypes);

            if ($file && empty($allowedTypes)) {
                throw new InvalidInputException(__('bagistoapi::app.graphql.return.invalid-image'));
            }
        } elseif ($file && ! in_array($file->getMimeType(), $allowedTypes, true)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.return.invalid-image'));
        }

        $stored = $this->rmaMessageRepository->create([
            'rma_id' => $rma->id,
            'message' => $message,
            'is_admin' => 0,
        ]);

        if ($file) {
            $this->storeAttachment($file, $fileName, $stored->id);
        }

        try {
            Mail::queue(new CustomerToAdminConversationNotification($stored->refresh()));
        } catch (\Exception) {
        }

        return CustomerReturnMessage::fromModel($stored->refresh());
    }

    private function stringInput(string $key): ?string
    {
        $value = request()->input($key);

        return is_string($value) ? $value : null;
    }

    /**
     * Stores the file under rma-conversation/{messageId}, as the storefront does.
     *
     * @param  UploadedFile|array{bytes: string, mime: string, extension: string}  $file
     */
    private function storeAttachment(UploadedFile|array $file, ?string $fileName, int $messageId): void
    {
        $directory = 'rma-conversation/'.$messageId;

        if ($file instanceof UploadedFile) {
            $extension = MimeTypes::getDefault()->getExtensions($file->getMimeType())[0] ?? null;

            $path = $file->storeAs($directory, Str::random(40).($extension ? '.'.$extension : ''));
            $name = $file->getClientOriginalName();
        } else {
            $path = $directory.'/'.Str::random(40).'.'.$file['extension'];
            $name = filled($fileName) ? basename($fileName) : basename($path);

            Storage::put($path, $file['bytes']);
        }

        $this->rmaMessageRepository->update([
            'attachment_path' => $path,
            'attachment' => $name,
        ], $messageId);
    }
}
