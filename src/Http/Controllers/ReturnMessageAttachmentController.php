<?php

namespace Webkul\BagistoApi\Http\Controllers;

use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Support\CurrentCustomer;
use Webkul\RMA\Helpers\Attachment;
use Webkul\RMA\Repositories\RMAMessageRepository;

class ReturnMessageAttachmentController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected RMAMessageRepository $rmaMessageRepository,
        protected Attachment $attachment,
    ) {}

    /**
     * Download the file a message on the customer's own return carries, as core's shop RMA controller does.
     */
    public function __invoke(int $id): StreamedResponse
    {
        $customer = CurrentCustomer::user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        $message = $this->rmaMessageRepository
            ->whereHas('rma.order', fn ($query) => $query->where('customer_id', $customer->id))
            ->find($id);

        if (empty($message?->attachment_path)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.return.attachment-not-found'));
        }

        try {
            return $this->attachment->download($message);
        } catch (NotFoundHttpException) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.return.attachment-not-found'));
        }
    }
}
