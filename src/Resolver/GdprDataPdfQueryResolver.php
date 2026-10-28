<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Dto\GdprDataPdfOutput;
use Webkul\BagistoApi\Services\GdprDataExportService;

class GdprDataPdfQueryResolver implements QueryItemResolverInterface
{
    /**
     * Create a new resolver instance.
     */
    public function __construct(protected GdprDataExportService $exports) {}

    /**
     * Resolve the authenticated customer's GDPR export as a base64-encoded PDF.
     */
    public function __invoke(?object $item, array $context): GdprDataPdfOutput
    {
        $pdf = $this->exports->pdfForCurrentCustomer();

        $output = new GdprDataPdfOutput;
        $output->file_name = 'customerInfo.pdf';
        $output->mime_type = 'application/pdf';
        $output->size = strlen($pdf);
        $output->content = base64_encode($pdf);

        return $output;
    }
}
