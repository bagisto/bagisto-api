<?php

namespace Webkul\BagistoApi\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Core\Traits\PDFHandler;
use Webkul\Customer\Models\Customer;
use Webkul\Sales\Models\Invoice;

/**
 * Invoice PDF Download Controller
 *
 * Generates and streams invoice PDF for authenticated customers.
 * Scopes access through order → customer relationship.
 */
class InvoicePdfController extends Controller
{
    use PDFHandler;

    /**
     * Download invoice as PDF
     *
     * @param  int  $id  Invoice ID
     * @return \Illuminate\Http\Response
     */
    public function __invoke(int $id)
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        $invoice = Invoice::where('id', $id)
            ->whereHas('order', function ($query) use ($customer) {
                $query->where('customer_id', $customer->id)
                    ->where('customer_type', Customer::class);
            })
            ->with(['order', 'items'])
            ->first();

        if (! $invoice) {
            throw new ResourceNotFoundException(
                __('bagistoapi::app.graphql.customer-invoice.not-found', ['id' => $id])
            );
        }

        $orderCurrencyCode = $invoice->order->order_currency_code;

        return $this->downloadPDF(
            view('shop::customers.account.orders.pdf', compact('invoice', 'orderCurrencyCode'))->render(),
            'invoice-'.$invoice->created_at->format('d-m-Y')
        );
    }
}
