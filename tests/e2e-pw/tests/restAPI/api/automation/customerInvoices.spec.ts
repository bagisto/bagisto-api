// tests/restAPI/api/automation/customerInvoices.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import { assertCustomerInvoiceFields } from '../../rest/assertions/customerInvoice.assertions';

test.describe('Customer Invoices (Public)', () => {
  test('Should return 403 when unauthenticated — requires bearer token', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_INVOICES);
    expect(response.status()).toBe(403);
    const body = await response.json();
    expect(body).toHaveProperty('detail');
    expect(body.detail).toBe('Unauthenticated. Please login to perform this action');
    console.log('Invoices (no auth):', response.status());
  });

  test('Should return 403 when fetching single invoice without auth', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_INVOICE(1));
    expect(response.status()).toBe(403);
    console.log('Single invoice (no auth):', response.status());
  });
});
