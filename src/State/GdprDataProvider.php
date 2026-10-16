<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Dto\GdprDataOutput;
use Webkul\BagistoApi\Services\GdprDataExportService;

class GdprDataProvider implements ProviderInterface
{
    /**
     * Create a new provider instance.
     */
    public function __construct(protected GdprDataExportService $exports) {}

    /**
     * Provide the authenticated customer's GDPR export data.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): GdprDataOutput
    {
        return $this->provideCurrentCustomer();
    }

    /**
     * Build the authenticated customer's GDPR export: profile, saved addresses and orders with their items.
     */
    public function provideCurrentCustomer(): GdprDataOutput
    {
        $export = $this->exports->exportForCurrentCustomer();
        $customer = $export['customer'];

        $output = new GdprDataOutput;
        $output->customer = [
            'id' => (int) $customer->id,
            'firstName' => $customer->first_name,
            'lastName' => $customer->last_name,
            'email' => $customer->email,
            'gender' => $customer->gender,
            'dateOfBirth' => $customer->date_of_birth?->format('Y-m-d'),
            'phone' => $customer->phone,
        ];
        $output->addresses = $export['addresses'];
        $output->orders = $export['orders'];

        return $output;
    }
}
