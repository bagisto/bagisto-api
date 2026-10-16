<?php

namespace Webkul\BagistoApi\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Webkul\BagistoApi\Services\GdprDataExportService;

class GdprDataPdfController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected GdprDataExportService $exports) {}

    /**
     * Download the authenticated customer's GDPR export as PDF.
     */
    public function __invoke(): Response
    {
        return new Response(
            $this->exports->pdfForCurrentCustomer(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="customerInfo.pdf"',
            ],
        );
    }
}
