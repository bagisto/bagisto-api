import { test, expect, APIRequestContext } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import { bearer, expectProblem, readJson, registerShopCustomer } from '../../rest/helpers/coverage/shopCart.helpers';
import { e2eFixtures, missingFixtureReason, E2eCustomerFixture } from '../../../fixtures/e2eFixtures';

const MSG = {
  UNAUTHENTICATED: 'Unauthenticated. Please login to perform this action',
  ORDER_NOT_FOUND: (id: number) => `Customer order with ID "${id}" not found`,
};

const MISSING_ORDER_ID = 2147480000;

async function loginToken(request: APIRequestContext, customer: E2eCustomerFixture): Promise<string> {
  const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_LOGIN, {
    method: 'POST',
    data: { email: customer.email, password: customer.password },
  });
  const body = await readJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(201);
  expect(body.success).toBe(true);

  return body.token;
}

test.describe('customer order detail rest api without a token', () => {
  test('order detail without a bearer token is refused', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ORDER(1));

    await expectProblem(response, 403, MSG.UNAUTHENTICATED);
  });

  test('order detail of a missing order without a bearer token is refused before the lookup', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ORDER(MISSING_ORDER_ID));

    await expectProblem(response, 403, MSG.UNAUTHENTICATED);
  });
});

test.describe('customer order detail rest api', () => {
  test('own order detail carries the order and its items', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const token = await loginToken(request, fx.customer!);
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ORDER(fx.order!.id), { headers: bearer(token) });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body.id).toBe(fx.order!.id);
    expect(body.incrementId).toBe(fx.order!.incrementId);
    expect(body.customerEmail).toBe(fx.customer!.email);
    expect(typeof body.status).toBe('string');
    expect(Array.isArray(body.items)).toBe(true);
    expect(body.items.map((item: any) => item.productId)).toContain(fx.order!.productId);
  });

  test('own order items carry id, name and ordered quantity', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const token = await loginToken(request, fx.customer!);
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ORDER(fx.order!.id), { headers: bearer(token) });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);

    const item = body.items.find((row: any) => row.productId === fx.order!.productId);
    expect(item, JSON.stringify(body.items)).toBeDefined();
    expect(typeof item.id).toBe('number');
    expect(typeof item.name).toBe('string');
    expect(item.qtyOrdered).toBeGreaterThanOrEqual(1);
  });

  test('order detail of another customer order is not found', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.otherCustomer, missingFixtureReason('otherCustomer'));

    const token = await loginToken(request, fx.otherCustomer!);
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ORDER(fx.order!.id), { headers: bearer(token) });

    await expectProblem(response, 404, MSG.ORDER_NOT_FOUND(fx.order!.id));
  });

  test('order detail of a missing order is not found', async ({ request }) => {
    const customer = await registerShopCustomer(request);

    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ORDER(MISSING_ORDER_ID), {
      headers: bearer(customer.token),
    });

    await expectProblem(response, 404, MSG.ORDER_NOT_FOUND(MISSING_ORDER_ID));
  });
});
