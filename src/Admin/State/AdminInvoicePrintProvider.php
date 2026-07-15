<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Sales\Models\Invoice;

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

        $invoice = Invoice::with(['items.order_item', 'order'])->find($id);

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
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        } catch (\Throwable $e) {
            throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.invoice.pdf-failed').' '.$e->getMessage(), 500, $e);
        }
    }
}
