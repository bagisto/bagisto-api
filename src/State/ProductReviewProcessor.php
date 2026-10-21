<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use finfo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Dto\CreateProductReviewInput;
use Webkul\BagistoApi\Dto\UpdateProductReviewInput;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\Product;
use Webkul\BagistoApi\Models\ProductReview;
use Webkul\BagistoApi\Models\ProductReviewAttachment;

class ProductReviewProcessor implements ProcessorInterface
{
    /**
     * Media MIME types accepted for a review attachment, mapped to the stored extension.
     */
    private const REVIEW_MEDIA_MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'image/bmp' => 'bmp',
        'image/x-ms-bmp' => 'bmp',
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
        'video/quicktime' => 'mov',
    ];

    /**
     * Review fields a storefront client may write. Status and ownership are set by the server.
     */
    private const CLIENT_WRITABLE_FIELDS = [
        'product_id',
        'title',
        'comment',
        'rating',
        'name',
    ];

    /**
     * Status every storefront-submitted review starts with, as in core.
     */
    private const STATUS_PENDING = 'pending';

    /**
     * Create a new instance.
     */
    public function __construct(
        private readonly ProcessorInterface $persistProcessor
    ) {}

    /**
     * Process.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof CreateProductReviewInput) {
            return $this->handleCreate($data);
        }

        if ($data instanceof UpdateProductReviewInput) {
            return $this->handleUpdate($data);
        }

        if (
            $operation instanceof Delete
            || $this->isDeleteMutation($operation)
        ) {
            return $this->handleDelete($data, $operation, $context);
        }

        if (! ($data instanceof ProductReview)) {
            $data = new ProductReview;
        }

        if (
            ! ($operation instanceof Post)
            && $data->exists
        ) {
            $this->assertOwnedReview($data);

            $data->setAttribute('status', $data->getOriginal('status'));
            $data->setAttribute('customer_id', $data->getOriginal('customer_id'));
        }

        if (isset($context['request'])) {
            $request = $context['request'];
            if (method_exists($request, 'all')) {
                $allData = $request->all();

                if (
                    isset($allData['productId'])
                    && ! isset($allData['product_id'])
                ) {
                    $allData['product_id'] = $allData['productId'];
                }

                $data->fill(array_intersect_key($allData, array_flip(self::CLIENT_WRITABLE_FIELDS)));
            }
        }

        if ($operation instanceof Post) {
            $customer = Auth::guard('sanctum')->user();

            $this->assertReviewsAllowed($customer !== null);
            $this->assertTitleAndComment($data->getAttribute('title'), $data->getAttribute('comment'));

            $data->setAttribute('customer_id', $customer?->id);
            $data->setAttribute('status', self::STATUS_PENDING);

            if ($customer) {
                $data->setAttribute('name', $customer->name);
            }
        }

        $this->validateReview($data);

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    /**
     * Handle create operation for GraphQL mutations.
     */
    private function handleCreate(CreateProductReviewInput $data)
    {
        $customer = Auth::guard('sanctum')->user();

        $this->assertReviewsAllowed($customer !== null);

        $review = new ProductReview;

        $review->setAttribute('product_id', $data->productId);
        $review->setAttribute('title', $data->title);
        $review->setAttribute('comment', $data->comment);
        $review->setAttribute('rating', $data->rating);
        $review->setAttribute('name', $customer?->name ?? $data->name);
        $review->setAttribute('status', self::STATUS_PENDING);
        $review->setAttribute('customer_id', $customer?->id);

        $this->validateReview($review);

        $review->save();

        $attachments = [];

        if (
            ! empty($data->attachments)
            && $review?->id
        ) {
            $attachments = $this->saveAttachments($data->attachments, $review->id);
        }

        if ($attachments) {
            $review->setAttribute('attachments', json_encode($attachments));
        }

        return $this->mapToOutput($review);
    }

    /**
     * Assert reviews allowed.
     */
    private function assertReviewsAllowed(bool $isCustomer): void
    {
        if (! core()->getConfigData('catalog.products.review.customer_review')) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.product-review.review-disabled'));
        }

        if (
            ! $isCustomer
            && ! core()->getConfigData('catalog.products.review.guest_review')
        ) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.product-review.guest-review-disabled'));
        }
    }

    /**
     * Assert title and comment.
     */
    private function assertTitleAndComment(mixed $title, mixed $comment): void
    {
        if (blank($title)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.product-review.title-required'));
        }

        if (blank($comment)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.product-review.comment-required'));
        }
    }

    /**
     * Handle update.
     */
    private function handleUpdate(UpdateProductReviewInput $data)
    {
        $reviewId = $data->id;
        if (
            is_string($reviewId)
            && preg_match('/\/(\d+)$/', $reviewId, $matches)
        ) {
            $reviewId = (int) $matches[1];
        } else {
            $reviewId = (int) $reviewId;
        }

        $review = ProductReview::findOrFail($reviewId);

        $this->assertOwnedReview($review);

        if ($data->product_id !== null) {
            $review->setAttribute('product_id', $data->product_id);
        }
        if ($data->title !== null) {
            $review->setAttribute('title', $data->title);
        }
        if ($data->comment !== null) {
            $review->setAttribute('comment', $data->comment);
        }
        if ($data->rating !== null) {
            $review->setAttribute('rating', $data->rating);
        }
        if ($data->name !== null) {
            $review->setAttribute('name', $data->name);
        }
        $this->validateReview($review);

        $review->save();

        $attachments = [];

        if (
            ! empty($data->attachments)
            && $review?->id
        ) {
            $attachments = $this->saveAttachments($data->attachments, $review->id);
        }

        if ($attachments) {
            $review->setAttribute('attachments', json_encode($attachments));
        }

        return $this->mapToOutput($review);
    }

    /**
     * Delete the caller's review, returning the deleted review to a GraphQL delete mutation.
     */
    private function handleDelete(mixed $data, Operation $operation, array $context): mixed
    {
        if (
            ! $data instanceof ProductReview
            || ! $data->exists
        ) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.product-review.item-not-found', [
                'id' => $context['args']['input']['id'] ?? '',
            ]));
        }

        $this->assertOwnedReview($data);

        $snapshot = clone $data;

        $data->delete();

        return $this->isDeleteMutation($operation) ? $snapshot : null;
    }

    /**
     * Determine whether the operation is the GraphQL delete mutation.
     */
    private function isDeleteMutation(Operation $operation): bool
    {
        return $operation instanceof Mutation
            && $operation->getName() === 'delete';
    }

    /**
     * Assert owned review.
     */
    private function assertOwnedReview(ProductReview $review): void
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.product-review.owner-required'));
        }

        $ownerId = $review->getAttribute('customer_id');

        if (
            ! $ownerId
            || (int) $ownerId !== (int) $customer->id
        ) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.product-review.not-owned'));
        }
    }

    /**
     * Validate review.
     */
    private function validateReview(ProductReview|CreateProductReviewInput|UpdateProductReviewInput $review): void
    {
        $productId = $review instanceof CreateProductReviewInput ? $review->productId : ($review instanceof UpdateProductReviewInput ? $review->product_id : $review->getAttribute('product_id'));

        if ($productId === null) {
            return;
        }

        if (empty($productId)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.product-review.product-id-required'));
        }

        $product = Product::find($productId);
        if (! $product) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.product-review.product-not-found'));
        }

        $rating = $review instanceof CreateProductReviewInput ? $review->rating : ($review instanceof UpdateProductReviewInput ? $review->rating : $review->getAttribute('rating'));

        if (isset($rating)) {
            $ratingVal = (int) $rating;
            if (
                $ratingVal < 1
                || $ratingVal > 5
            ) {
                throw new InvalidInputException(__('bagistoapi::app.graphql.product-review.rating-invalid'));
            }
        }

        $title = $review instanceof CreateProductReviewInput ? $review->title : ($review instanceof UpdateProductReviewInput ? $review->title : $review->getAttribute('title'));
        $comment = $review instanceof CreateProductReviewInput ? $review->comment : ($review instanceof UpdateProductReviewInput ? $review->comment : $review->getAttribute('comment'));

        if ($review instanceof CreateProductReviewInput) {
            if (empty($title)) {
                throw new InvalidInputException(__('bagistoapi::app.graphql.product-review.title-required'));
            }
            if (empty($comment)) {
                throw new InvalidInputException(__('bagistoapi::app.graphql.product-review.comment-required'));
            }
        }
    }

    /**
     * Map to output.
     */
    private function mapToOutput(ProductReview $review): \stdClass
    {
        $output = new \stdClass;

        foreach ($review->getAttributes() as $key => $value) {
            switch ($key) {
                case 'id':
                case 'product_id':
                case 'rating':
                    $output->$key = (int) $value;

                    break;

                case 'title':
                case 'comment':
                case 'name':
                    $output->$key = (string) $value;
                    break;

                case 'status':
                    $output->$key = $value === 'approved' ? 1 : 0;
                    break;

                case 'created_at':
                case 'updated_at':
                    if ($value instanceof \DateTime) {
                        $output->$key = $value->format('Y-m-d H:i:s');
                    } else {
                        $output->$key = $value;
                    }
                    break;
                default:
                    $output->$key = $value;
                    break;
            }
        }

        return $output;
    }

    /**
     * Check image or video.
     */
    private function checkImageOrVideo(string $imageData): string
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
            return 'image';
        }
        if (preg_match('/^data:video\/(\w+);base64,/', $imageData, $matches)) {
            return 'video';
        }

        throw new InvalidInputException(__('Invalid file format'));
    }

    /**
     * Save attachments.
     */
    private function saveAttachments(string $attachmentsJson, $reviewId): array
    {
        $attachmentUrls = [];

        if ($attachmentsJson) {
            $attachments = json_decode($attachmentsJson, true);

            if (! is_array($attachments)) {
                throw new InvalidInputException(__('bagistoapi::app.graphql.product-review.invalid-attachments'));
            }

            foreach ($attachments as $item) {
                $attachmentUrls[] = $this->handleReviewMedia($item, $reviewId);
            }
        }

        return $attachmentUrls;
    }

    /**
     * Handle review media.
     */
    private function handleReviewMedia($mediaData, $reviewId)
    {
        if (! preg_match('/^data:(image|video)\/(\w+);base64,/', $mediaData, $matches)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.upload.invalid-format'));
        }

        $pure = substr($mediaData, strpos($mediaData, ',') + 1);
        $decoded = base64_decode($pure, true);

        if ($decoded === false) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.upload.invalid-base64'));
        }

        if (strlen($decoded) > 5 * 1024 * 1024) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.upload.size-exceeds-limit'));
        }

        $mimeType = (new finfo(FILEINFO_MIME_TYPE))->buffer($decoded);
        $extension = self::REVIEW_MEDIA_MIME_EXTENSIONS[$mimeType] ?? null;

        if (! $extension) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.upload.invalid-format'));
        }

        $mediaType = strtok($mimeType, '/');

        $directory = "review/{$reviewId}";
        $filename = $directory.'/'.uniqid().'.'.$extension;

        try {
            Storage::put($filename, $decoded);

            ProductReviewAttachment::create([
                'review_id' => $reviewId,
                'path' => $filename,
                'type' => $mediaType,
                'mime_type' => $mimeType,
            ]);

            return [
                'type' => $mediaType,
                'url' => Storage::url($filename),
            ];
        } catch (\Exception $exception) {
            report($exception);

            throw new InvalidInputException(__('bagistoapi::app.graphql.upload.failed'));
        }
    }
}
