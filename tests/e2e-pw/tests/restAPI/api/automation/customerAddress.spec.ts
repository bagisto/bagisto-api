// tests/restAPI/api/automation/customerAddress.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import { assertCustomerAddressFields } from '../../rest/assertions/customerAddress.assertions';

test.describe('Customer Addresses (Public)', () => {
  test('Should return 401/404 when unauthenticated on GET /customer-addresses', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ADDRESSES);
    expect([401, 404]).toContain(response.status());
    console.log('Customer addresses (no auth):', response.status());
  });

  test('Should return 401/404 when creating address without auth', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ADDRESS_CREATE, {
      method: 'POST',
      data: {
        firstName: 'Test', lastName: 'User',
        address1: '123 Main St', city: 'Los Angeles',
        state: 'CA', country: 'US', postcode: '90210',
        phone: '555-0101',
      },
    });
    expect([401, 404]).toContain(response.status());
    console.log('POST /customer-addresses (no auth):', response.status());
  });

  test('Should return 401/404 when fetching address /id without auth', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ADDRESS(1));
    expect([401, 404]).toContain(response.status());
    console.log('GET /customer-addresses/1 (no auth):', response.status());
  });
});

test.describe('Customer Addresses (PUT / DELETE)', () => {
  test('Should return 404 when updating non-existent address without auth', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ADDRESS(999999), {
      method: 'PUT',
      data: { city: 'San Francisco' },
    });
    expect(response.status()).toBe(404);
    console.log('PUT /customer-addresses/999999: 404 (route not registered)');
  });

  test('Should return 404 when deleting non-existent address without auth', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ADDRESS(999999), {
      method: 'DELETE',
    });
    expect(response.status()).toBe(404);
    console.log('DELETE /customer-addresses/999999: 404 (route not registered)');
  });
});

// ─── REGRESSION (strict): companyName round-trip on create + update ───────────
// Locks down the bug fix where companyName was being dropped from the response
// even though it was persisted. Both create and update must echo the value.
test.describe('REGRESSION — companyName on address create/update', () => {
  let regToken: string | null = null;

  test.beforeAll(async ({ request }) => {
    const email = `addrCo_${Date.now()}@example.com`;
    const password = `AddrCo${Math.floor(Math.random() * 10000)}!`;
    const reg = await sendRestRequest(request, ENDPOINTS.CUSTOMER_REGISTER, {
      method: 'POST',
      data: {
        first_name: 'Addr',
        last_name: 'Company',
        email,
        password,
        password_confirmation: password,
      },
    });
    if (reg.status() === 200 || reg.status() === 201) {
      const login = await sendRestRequest(request, ENDPOINTS.CUSTOMER_LOGIN, {
        method: 'POST',
        data: { email, password },
      });
      if (login.status() === 200) {
        regToken = ((await login.json()).token as string) ?? null;
      }
    }
  });

  test('POST /customer-addresses with companyName returns it on response', async ({ request }) => {
    if (!regToken) {
      test.skip(true, 'Registration / login failed');
      return;
    }
    const companyName = 'Acme Corp';
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ADDRESS_CREATE, {
      method: 'POST',
      data: {
        companyName,
        firstName: 'Addr',
        lastName: 'Company',
        address1: ['123 Main St'],
        address: '123 Main St',
        city: 'Los Angeles',
        state: 'CA',
        country: 'US',
        postcode: '90210',
        phone: '5550101',
        email: `addr_${Date.now()}@example.com`,
      },
      headers: { Authorization: `Bearer ${regToken}` },
    });

    expect([200, 201]).toContain(response.status());
    const body = await response.json();
    expect(body).toBeDefined();
    // The fix: companyName (camelCased through the name converter) must be
    // non-null AND equal to what we sent.
    const echoed = body.companyName ?? body.company_name;
    expect(echoed).not.toBeNull();
    expect(echoed).toBe(companyName);
  });

  test('PUT /customer-addresses/{id} with updated companyName reflects new value', async ({ request }) => {
    if (!regToken) {
      test.skip(true, 'Registration / login failed');
      return;
    }
    // First, create an address with a companyName.
    const createResp = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ADDRESS_CREATE, {
      method: 'POST',
      data: {
        companyName: 'Original Co',
        firstName: 'Addr',
        lastName: 'Company',
        address1: ['456 Side St'],
        address: '456 Side St',
        city: 'San Francisco',
        state: 'CA',
        country: 'US',
        postcode: '94103',
        phone: '5550202',
        email: `addr2_${Date.now()}@example.com`,
      },
      headers: { Authorization: `Bearer ${regToken}` },
    });
    if (![200, 201].includes(createResp.status())) {
      test.skip(true, `Create failed: ${createResp.status()}`);
      return;
    }
    const created = await createResp.json();
    const addrId = created.id;
    expect(addrId).toBeTruthy();

    const newCompany = 'New Co';
    const putResp = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ADDRESS(addrId), {
      method: 'PUT',
      data: {
        companyName: newCompany,
        firstName: 'Addr',
        lastName: 'Company',
        address1: ['456 Side St'],
        address: '456 Side St',
        city: 'San Francisco',
        state: 'CA',
        country: 'US',
        postcode: '94103',
        phone: '5550202',
      },
      headers: { Authorization: `Bearer ${regToken}` },
    });
    expect([200, 201]).toContain(putResp.status());
    const putBody = await putResp.json();
    const echoed = putBody.companyName ?? putBody.company_name;
    expect(echoed).not.toBeNull();
    expect(echoed).toBe(newCompany);
  });
});
