<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Webkul\BagistoApi\Admin\Models\AdminInvoice;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Sales\Models\Invoice;

class AdminInvoicePrintProvider implements ProviderInterface
{
    public function __construct(protected AdminOrderActionGuard $guard) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Response
    {
        AdminReadPermissions::authorize($operation, AdminInvoice::class);

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
            $orderCurrencyCode = $invoice->order->order_currency_code;

            $html = view('shop::customers.account.orders.pdf', compact('invoice', 'orderCurrencyCode'))->render();

            $pdf = Pdf::loadHTML($html)
                ->setPaper('A4', 'portrait')
                ->set_option('defaultFont', 'Courier');

            $filename = 'invoice-'.$invoice->id.'.pdf';

            return new Response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        } catch (\Throwable $exception) {
            throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.invoice.pdf-failed').' '.$exception->getMessage(), 500, $exception);
        }
    }
}
