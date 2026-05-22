// tests/restAPI/api/automation/customerDownloads.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import { assertCustomerDownloadFields } from '../../rest/assertions/customerDownloadable.assertions';

test.describe('Customer Downloadable Products (Public)', () => {
  test('Should return 403 when unauthenticated — requires bearer token', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_DOWNLOADABLE_PRODUCTS);
    expect(response.status()).toBe(403);
    const body = await response.json();
    expect(body).toHaveProperty('detail');
    expect(body.detail).toBe('Unauthenticated. Please login to perform this action');
    console.log('Downloadable products (no auth):', response.status());
  });

  test('Should return 403 when fetching single item without auth', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_DOWNLOADABLE_PRODUCT(1));
    expect(response.status()).toBe(403);
    console.log('Single download (no auth):', response.status());
  });
});
