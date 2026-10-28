<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Closure;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webkul\BagistoApi\Admin\Models\AdminReturnFile;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\RMA\Helpers\Attachment;
use Webkul\RMA\Repositories\RMAImageRepository;
use Webkul\RMA\Repositories\RMAMessageRepository;

class AdminReturnFileProvider implements ProviderInterface
{
    /**
     * Create a new instance.
     */
    public function __construct(
        private readonly Attachment $attachment,
        private readonly RMAImageRepository $rmaImageRepository,
        private readonly RMAMessageRepository $rmaMessageRepository,
    ) {}

    /**
     * Stream a return image inline or a message attachment as a download, as core's RequestController does.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): StreamedResponse
    {
        AdminReadPermissions::authorize($operation, AdminReturnFile::class);

        $id = (int) ($uriVariables['id'] ?? 0);

        $template = $operation instanceof HttpOperation ? (string) $operation->getUriTemplate() : '';

        if (str_ends_with($template, '/attachment')) {
            return $this->downloadAttachment($id);
        }

        return $this->showImage($id);
    }

    /**
     * Stream a photo attached to a return for display.
     */
    protected function showImage(int $id): StreamedResponse
    {
        $image = $this->rmaImageRepository->find($id);

        if (empty($image?->path)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.rma.image-not-found'));
        }

        return $this->send(
            fn () => $this->attachment->inline($image->path),
            'bagistoapi::app.admin.rma.image-not-found',
        );
    }

    /**
     * Stream the file a message on a return carries, under its original name.
     */
    protected function downloadAttachment(int $id): StreamedResponse
    {
        $message = $this->rmaMessageRepository->find($id);

        if (empty($message?->attachment_path)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.rma.attachment-not-found'));
        }

        return $this->send(
            fn () => $this->attachment->download($message),
            'bagistoapi::app.admin.rma.attachment-not-found',
        );
    }

    /**
     * Run the core helper, turning its 404 for a file missing from every disk into the API's not-found error.
     */
    protected function send(Closure $stream, string $notFoundKey): StreamedResponse
    {
        try {
            return $stream();
        } catch (NotFoundHttpException) {
            throw new ResourceNotFoundException(__($notFoundKey));
        }
    }
}
