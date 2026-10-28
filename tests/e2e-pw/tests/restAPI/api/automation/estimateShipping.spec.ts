import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import {
  SHOP_CART_MESSAGES,
  bearer,
  createGuestCart,
  expectProblem,
  randomUuid,
  readJson,
} from '../../rest/helpers/coverage/shopCart.helpers';

test.describe('estimate shipping rest api', () => {
  test('get has no route and returns 404', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.ESTIMATE_SHIPPING_REST);

    expect(response.status(), JSON.stringify(await readJson(response))).toBe(404);
  });

  test('post without a bearer token returns 401', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.ESTIMATE_SHIPPING_REST, {
      method: 'POST',
      data: { country: 'US', state: 'CA', postcode: '94103' },
    });

    await expectProblem(response, 401, SHOP_CART_MESSAGES.AUTHENTICATION_REQUIRED);
  });

  test('post with an unknown cart token returns 404', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.ESTIMATE_SHIPPING_REST, {
      method: 'POST',
      data: { country: 'US', state: 'CA', postcode: '94103' },
      headers: bearer(randomUuid()),
    });

    await expectProblem(response, 404, SHOP_CART_MESSAGES.CART_NOT_FOUND);
  });

  test('post without a state on a guest cart returns 400', async ({ request }) => {
    const cart = await createGuestCart(request);

    const response = await sendRestRequest(request, ENDPOINTS.ESTIMATE_SHIPPING_REST, {
      method: 'POST',
      data: { country: 'US', postcode: '94103' },
      headers: bearer(cart.token),
    });

    await expectProblem(response, 400, SHOP_CART_MESSAGES.ADDRESS_DATA_REQUIRED);
  });
});
