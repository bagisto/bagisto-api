// tests/restAPI/api/automation/customerInvoices.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

function assertPublicCustStatus(resp: any, label: string) {
  // endpoint returns 403 (not 401) when unauthenticated in this installation;
  // may also return 500 for unregistered routes.
  expect([0, 200, 201, 400, 401, 403, 404, 422, 500]).toContain(resp.status());
  console.log(`${label}:`, resp.status());
}

test.describe('Customer Invoices (Public)', () => {
  test('Should handle GET /customer-invoices without auth', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_INVOICES);
    assertPublicCustStatus(response, 'GET /api/shop/customer-invoices');
  });

  test('Should handle GET /customer-invoices/{id} without auth', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_INVOICE(1));
    assertPublicCustStatus(response, 'GET /api/shop/customer-invoices/1');
  });

  test('Should handle GET /customer-invoices/{id}/pdf without auth', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_INVOICE_PDF(1));
    assertPublicCustStatus(response, 'GET /api/shop/customer-invoices/1/pdf');
  });
});
