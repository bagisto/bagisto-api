<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Sales\Models\Invoice;

/**
 * GET /api/admin/invoices/{id}/print — returns the invoice as an
 * application/pdf binary attachment. Mirrors the monolith
 * InvoiceController::printInvoice flow (dompdf via the PDFHandler trait).
 */
class AdminInvoicePrintProvider implements ProviderInterface
{
    public function __construct(protected AdminOrderActionGuard $guard) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Response
    {
        $this->guard->resolveAdmin();

        $id = (int) basename((string) ($uriVariables['id'] ?? 0));

        if ($id <= 0) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.order.actions.invoice.not-found'));
        }

        $invoice = Invoice::with(['items.orderItem', 'order.billing_address', 'order.shipping_address', 'order.channel'])->find($id);

        if (! $invoice) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.order.actions.invoice.not-found'));
        }

        try {
            $html = view('admin::sales.invoices.pdf', compact('invoice'))->render();

            $pdf = Pdf::loadHTML($html)
                ->setPaper('A4', 'portrait')
                ->set_option('defaultFont', 'Courier');

            $filename = 'invoice-'.$invoice->id.'.pdf';

            return new Response($pdf->output(), 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        } catch (\Throwable $e) {
            throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.invoice.pdf-failed').' '.$e->getMessage(), 500, $e);
        }
    }
}
