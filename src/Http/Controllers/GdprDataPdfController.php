<?php

namespace Webkul\BagistoApi\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\State\Concerns\GdprFeatureGate;
use Webkul\Core\Traits\PDFHandler;
use Webkul\Customer\Repositories\CustomerAddressRepository;
use Webkul\Sales\Repositories\OrderRepository;

/**
 * Streams the signed-in customer's data as the PDF the storefront GDPR page downloads.
 */
class GdprDataPdfController extends Controller
{
    use GdprFeatureGate;
    use PDFHandler;

    public function __invoke(OrderRepository $orderRepository, CustomerAddressRepository $addressRepository): Response
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        $this->assertGdprEnabled();

        $param = array_filter([
            'customerInformation' => $customer,
            'order' => $orderRepository->findWhere(['customer_id' => $customer->id])->toArray() ?: null,
            'address' => $addressRepository->where('address_type', 'customer')->where('customer_id', $customer->id)->get()->toArray() ?: null,
        ]);

        return $this->downloadPDF(view('shop::customers.account.gdpr.pdf', compact('param'))->render(), 'customerInfo');
    }
}
