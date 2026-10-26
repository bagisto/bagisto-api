import { test, expect, APIRequestContext } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

const MISSING_PURCHASED_LINK_ID = 999999999;

function purchasedDownloadPath(id: number | string): string {
  return `${ENDPOINTS.CUSTOMER_DOWNLOADABLE_PRODUCT(id)}/download`;
}

async function registerCustomerToken(request: APIRequestContext): Promise<string> {
  const email = `downloads_${Date.now()}_${Math.floor(Math.random() * 100000)}@example.com`;
  const password = `Downloads${Math.floor(Math.random() * 10000)}!`;

  const registerResp = await sendRestRequest(request, ENDPOINTS.CUSTOMER_REGISTER, {
    method: 'POST',
    data: {
      first_name: 'Downloads',
      last_name: 'Customer',
      email,
      password,
      password_confirmation: password,
    },
  });
  expect([200, 201], `customer registration failed: ${await registerResp.text()}`).toContain(registerResp.status());

  const loginResp = await sendRestRequest(request, ENDPOINTS.CUSTOMER_LOGIN, {
    method: 'POST',
    data: { email, password },
  });
  expect([200, 201]).toContain(loginResp.status());

  const token = (await loginResp.json()).accessToken;
  expect(typeof token === 'string' && token.length > 0, 'customer login returned no token').toBeTruthy();
  return token as string;
}

test.describe('customer downloadable products (public)', () => {
  test('should return 403 when unauthenticated — requires bearer token', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_DOWNLOADABLE_PRODUCTS);
    expect(response.status()).toBe(403);
    const body = await response.json();
    expect(body).toHaveProperty('detail');
    expect(body.detail).toBe('Unauthenticated. Please login to perform this action');
    console.log('Downloadable products (no auth):', response.status());
  });

  test('should return 403 when fetching single item without auth', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_DOWNLOADABLE_PRODUCT(1));
    expect(response.status()).toBe(403);
    console.log('Single download (no auth):', response.status());
  });
});

test.describe('customer purchased download endpoint', () => {
  test('should refuse to download a purchased link for an anonymous caller', async ({ request }) => {
    const response = await sendRestRequest(request, purchasedDownloadPath(MISSING_PURCHASED_LINK_ID));
    expect(response.status()).toBe(401);

    const body = await response.json();
    expect(body.error).toBe('unauthenticated');
    expect(body.message).toBe('Unauthorized: Customer authentication required.');
  });

  test('should answer not found for a purchased link that is not the customer\'s', async ({ request }) => {
    const token = await registerCustomerToken(request);

    const response = await sendRestRequest(request, purchasedDownloadPath(MISSING_PURCHASED_LINK_ID), {
      headers: { Authorization: `Bearer ${token}` },
    });
    expect(response.status()).toBe(404);

    const body = await response.json();
    expect(body.error).toBe('not_found');
    expect(body.message).toBe('Downloadable product not found.');
  });
});
