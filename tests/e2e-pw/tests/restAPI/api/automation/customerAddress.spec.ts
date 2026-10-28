import { test, expect, APIRequestContext } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import {
  SHOP_CART_MESSAGES,
  ShopCustomer,
  bearer,
  expectProblem,
  readJson,
  registerShopCustomer,
} from '../../rest/helpers/coverage/shopCart.helpers';

const MSG = {
  BEARER_REQUIRED: 'Authentication token is required. Please provide the token in the Authorization header as "Bearer <token>"',
  ADDRESS_TOKEN_REQUIRED: 'Authentication token is required',
};

function addressPayload(companyName: string, street: string): Record<string, any> {
  return {
    companyName,
    firstName: 'Addr',
    lastName: 'Company',
    address1: street,
    city: 'San Francisco',
    state: 'CA',
    country: 'US',
    postcode: '94103',
    phone: '5550202',
    email: `addr_${Date.now()}@example.com`,
  };
}

async function createAddress(request: APIRequestContext, customer: ShopCustomer, data: Record<string, any>): Promise<any> {
  const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ADDRESS_CREATE, {
    method: 'POST',
    data,
    headers: bearer(customer.token),
  });
  const body = await readJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(201);
  expect(typeof body.id).toBe('number');

  return body;
}

async function listAddressIds(request: APIRequestContext, customer: ShopCustomer): Promise<number[]> {
  const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ADDRESS_CREATE, {
    headers: bearer(customer.token),
  });
  const body = await readJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);
  expect(Array.isArray(body), JSON.stringify(body)).toBe(true);

  return body.map((address: any) => Number(address.id));
}

test.describe('customer addresses without a bearer token', () => {
  test('listing returns 401', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ADDRESS_CREATE);

    await expectProblem(response, 401, MSG.BEARER_REQUIRED);
  });

  test('the legacy nested listing path has no route and returns 404', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ADDRESSES);

    expect(response.status(), JSON.stringify(await readJson(response))).toBe(404);
  });

  test('creating an address returns 401', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ADDRESS_CREATE, {
      method: 'POST',
      data: {
        firstName: 'Test', lastName: 'User',
        address1: '123 Main St', city: 'Los Angeles',
        state: 'CA', country: 'US', postcode: '90210',
        phone: '555-0101',
      },
    });

    await expectProblem(response, 401, MSG.ADDRESS_TOKEN_REQUIRED);
  });

  test('fetching an address by id returns 401', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ADDRESS(1));

    await expectProblem(response, 401, MSG.BEARER_REQUIRED);
  });

  test('updating a non-existent address returns 404', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ADDRESS(999999), {
      method: 'PUT',
      data: { city: 'San Francisco' },
    });

    expect(response.status()).toBe(404);
  });

  test('deleting a non-existent address returns 404', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ADDRESS(999999), {
      method: 'DELETE',
    });

    expect(response.status()).toBe(404);
  });
});

test.describe('customer address company name round trip', () => {
  let customer: ShopCustomer;

  test.beforeEach(async ({ request }) => {
    customer = await registerShopCustomer(request);
  });

  test('create echoes the company name and stores it', async ({ request }) => {
    const created = await createAddress(request, customer, addressPayload('Acme Corp', '123 Main St'));

    expect(created.companyName).toBe('Acme Corp');
    expect(created.firstName).toBe('Addr');
    expect(created.address1).toBe('123 Main St');

    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ADDRESS(created.id), {
      headers: bearer(customer.token),
    });
    const stored = await readJson(response);

    expect(response.status(), JSON.stringify(stored)).toBe(200);
    expect(stored.id).toBe(created.id);
    expect(stored.companyName).toBe('Acme Corp');
    expect(await listAddressIds(request, customer)).toEqual([created.id]);
  });

  test('update replaces the company name', async ({ request }) => {
    const created = await createAddress(request, customer, addressPayload('Original Co', '456 Side St'));

    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ADDRESS(created.id), {
      method: 'PUT',
      data: addressPayload('New Co', '456 Side St'),
      headers: bearer(customer.token),
    });
    const updated = await readJson(response);

    expect(response.status(), JSON.stringify(updated)).toBe(200);
    expect(updated.id).toBe(created.id);
    expect(updated.companyName).toBe('New Co');

    const read = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ADDRESS(created.id), {
      headers: bearer(customer.token),
    });
    const stored = await readJson(read);

    expect(read.status(), JSON.stringify(stored)).toBe(200);
    expect(stored.companyName).toBe('New Co');
  });

  test('another customer cannot read the address', async ({ request }) => {
    const created = await createAddress(request, customer, addressPayload('Private Co', '789 Hidden St'));
    const other = await registerShopCustomer(request);

    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ADDRESS(created.id), {
      headers: bearer(other.token),
    });

    await expectProblem(response, 403, SHOP_CART_MESSAGES.ADDRESS_NOT_FOUND);
  });
});
