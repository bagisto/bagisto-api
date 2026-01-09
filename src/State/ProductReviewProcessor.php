<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Dto\CreateProductReviewInput;
use Webkul\BagistoApi\Dto\UpdateProductReviewInput;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\Product;
use Webkul\BagistoApi\Models\ProductReview;
use Webkul\GraphQL\Dto\ProductReviewOutput;
use Webkul\GraphQL\Models\ProductReviewAttachment;

/**
 * ProductReviewProcessor - Handles create/update operations for product reviews
 * Validates input and delegates persistence to the persist processor
 */
class ProductReviewProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $persistProcessor
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof CreateProductReviewInput) {
            return $this->handleCreate($data);
        }

        if ($data instanceof UpdateProductReviewInput) {
            return $this->handleUpdate($data);
        }

        if (! ($data instanceof ProductReview)) {
            $data = new ProductReview;
        }

        if (isset($context['request'])) {
            $request = $context['request'];
            if (method_exists($request, 'all')) {
                $allData = $request->all();

                $data->fill($allData);
            }
        }

        $this->validateReview($data);

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    /**
     * Handle create operation for GraphQL mutations
     */
    private function handleCreate(CreateProductReviewInput $data)
    {
        $review = new ProductReview;

        $review->setAttribute('product_id', $data->productId);
        $review->setAttribute('title', $data->title);
        $review->setAttribute('comment', $data->comment);
        $review->setAttribute('rating', $data->rating);
        $review->setAttribute('name', $data->name);
        $review->setAttribute('status', $data->status ?? 0);

        $this->validateReview($review);

        $review->save();

        $attachments = [];

        if (! empty($data->attachments) && $review?->id) {
            $attachments = $this->saveAttachments($data->attachments, $review->id);
        }

        if ($attachments) {
            $review->setAttribute('attachments', json_encode($attachments));
        }

        $output = new \stdClass;

        foreach ($review->getAttributes() as $key => $value) {
            $output->$key = $value;
        }

        return $output;
    }

    /**
     * Handle update operation for GraphQL mutations
     */
    private function handleUpdate(UpdateProductReviewInput $data)
    {
        $reviewId = $data->id;
        if (is_string($reviewId) && preg_match('/\/(\d+)$/', $reviewId, $matches)) {
            $reviewId = (int) $matches[1];
        } else {
            $reviewId = (int) $reviewId;
        }

        $review = ProductReview::findOrFail($reviewId);

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
        if ($data->status !== null) {
            $review->setAttribute('status', $data->status);
        }

        $this->validateReview($review);

        $review->save();

        $attachments = [];

        if (! empty($data->attachments) && $review?->id) {
            $attachments = $this->saveAttachments($data->attachments, $review->id);
        }

        if ($attachments) {
            $review->setAttribute('attachments', json_encode($attachments));
        }

        $output = new \stdClass;

        foreach ($review->getAttributes() as $key => $value) {
            $output->$key = $value;
        }

        return $output;

    }

    /**
     * Validate review data
     */
    private function validateReview(ProductReview|CreateProductReviewInput|UpdateProductReviewInput $review): void
    {
        $productId = $review instanceof CreateProductReviewInput ? $review->productId : ($review instanceof UpdateProductReviewInput ? $review->product_id : $review->getAttribute('product_id'));

        if ($productId === null) {
            return;
        }

        if (empty($productId)) {
            throw new InvalidInputException(__('graphql::app.graphql.product-review.product-id-required'));
        }

        $product = Product::find($productId);
        if (! $product) {
            throw new ResourceNotFoundException(__('graphql::app.graphql.product-review.product-not-found'));
        }

        $rating = $review instanceof CreateProductReviewInput ? $review->rating : ($review instanceof UpdateProductReviewInput ? $review->rating : $review->getAttribute('rating'));

        if (isset($rating)) {
            $ratingVal = (int) $rating;
            if ($ratingVal < 1 || $ratingVal > 5) {
                throw new InvalidInputException(__('graphql::app.graphql.product-review.rating-invalid'));
            }
        }

        $title = $review instanceof CreateProductReviewInput ? $review->title : ($review instanceof UpdateProductReviewInput ? $review->title : $review->getAttribute('title'));
        $comment = $review instanceof CreateProductReviewInput ? $review->comment : ($review instanceof UpdateProductReviewInput ? $review->comment : $review->getAttribute('comment'));

        if ($review instanceof CreateProductReviewInput) {
            if (empty($title)) {
                throw new InvalidInputException(__('graphql::app.graphql.product-review.title-required'));
            }
            if (empty($comment)) {
                throw new InvalidInputException(__('graphql::app.graphql.product-review.comment-required'));
            }
        }
    }

    /**
     * Map ProductReview model to ProductReviewOutput DTO for GraphQL response
     */
    private function mapToOutput(ProductReview $review): ProductReviewOutput
    {
        $output = new ProductReviewOutput;
        $output->id = (int) $review->getAttribute('id');
        $output->productId = (int) $review->getAttribute('product_id');
        $output->title = (string) $review->getAttribute('title');
        $output->comment = (string) $review->getAttribute('comment');
        $output->rating = (int) $review->getAttribute('rating');
        $output->name = (string) $review->getAttribute('name');
        $output->status = (int) $review->getAttribute('status');

        $createdAt = $review->getAttribute('created_at');
        $output->createdAt = $createdAt ? ($createdAt instanceof \DateTime ? $createdAt->format('Y-m-d H:i:s') : (string) $createdAt) : null;

        $updatedAt = $review->getAttribute('updated_at');
        $output->updatedAt = $updatedAt ? ($updatedAt instanceof \DateTime ? $updatedAt->format('Y-m-d H:i:s') : (string) $updatedAt) : null;

        return $output;
    }

    private function checkImageOrVideo(string $imageData): string
    {
        // Check if it's base64 encoded with data URI scheme
        if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
            return 'image';  // It's an image
        }
        if (preg_match('/^data:video\/(\w+);base64,/', $imageData, $matches)) {
            return 'video';  // It's a video
        }

        throw new InvalidInputException(__('Invalid file format'));
    }

    /**
     * Handle image upload with base64 encoding
     */
    private function saveAttachments(string $attachmentsJson, $reviewId): array
    {
        $attachmentUrls = [];

        if ($attachmentsJson) {
            $attachments = json_decode($attachmentsJson, true);

            if (! is_array($attachments)) {
                throw new InvalidInputException('Invalid attachments format.');
            }

            foreach ($attachments as $item) {
                $attachmentUrls[] = $this->handleReviewMedia($item, $reviewId);
            }
        }

        return $attachmentUrls;
    }

    private function handleReviewMedia($mediaData, $reviewId)
    {
        if (! preg_match('/^data:(image|video)\/(\w+);base64,/', $mediaData, $matches)) {
            throw new InvalidInputException(__('graphql::app.graphql.upload.invalid-format'));
        }

        $mediaType = $matches[1];
        $extension = $matches[2];
        $mimeType = "{$mediaType}/{$extension}";

        $pure = substr($mediaData, strpos($mediaData, ',') + 1);
        $decoded = base64_decode($pure, true);

        if ($decoded === false) {
            throw new InvalidInputException(__('graphql::app.graphql.upload.invalid-base64'));
        }

        if (strlen($decoded) > 5 * 1024 * 1024) {
            throw new InvalidInputException(__('graphql::app.graphql.upload.size-exceeds-limit'));
        }

        $directory = "review/{$reviewId}";
        $filename = $directory.'/'.uniqid().'.'.$extension;

        try {
            Storage::put($filename, $decoded);

            ProductReviewAttachment::create([
                'review_id' => $reviewId,
                'path'      => $filename,
                'type'      => $mediaType,
                'mime_type' => $mimeType,
            ]);

            return [
                'type' => $mediaType,
                'url'  => Storage::url($filename),
            ];
        } catch (\Exception $e) {
            report($e);

            throw new InvalidInputException(__('graphql::app.graphql.upload.failed'));
        }
    }
}
