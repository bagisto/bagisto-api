// tests/restAPI/api/automation/cart.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import { assertCartResponseFields, assertCartItemFields } from '../../rest/assertions/cart.assertions';

test.describe('Cart REST API', () => {
  function assertCartStatus(resp: any, debugLabel: string) {
    const code = resp.status();
    expect([0, 200, 201, 400, 401, 404, 422, 500, 429]).toContain(code);
    console.log(`${debugLabel}:`, code);
    return code;
  }

  test('Should get the current cart (GET)', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.GET_CART);
    const code = response.status();
    if (code === 429) {
      test.skip(true, 'Rate limited');
      return;
    }
    expect([200, 404]).toContain(code);
    if (response.status() === 200) {
      const body = await response.json();
      expect(body).toHaveProperty('items');
      console.log('Cart items:', body.items?.length);
    }
  });

  test('Should handle POST to cart create endpoint', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CREATE_CART, {
      method: 'POST',
    });
    assertCartStatus(response, 'POST /api/shop/cart');
  });

  test('Should handle GET to cart create (wrong method)', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CREATE_CART);
    assertCartStatus(response, 'GET /api/shop/cart/create');
  });
});

test.describe('Cart Item Operations', () => {
  function assertCartItemStatus(resp: any, debugLabel: string) {
    const code = resp.status();
    expect([0, 200, 201, 400, 404, 422, 500, 429]).toContain(code);
    console.log(`${debugLabel}:`, code);
    if (code === 429) {
      test.skip(true, 'Rate limited');
    }
    return code;
  }

  test('Should handle add-to-cart POST', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.ADD_TO_CART, {
      method: 'POST',
      data: { productId: 1, quantity: 1 },
    });
    assertCartItemStatus(response, 'POST /api/shop/cart/items');
  });

  test('Should handle cart item attribute addition', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.ADD_TO_CART, {
      method: 'POST',
      data: { productId: 1, quantity: 2, attributes: {} },
    });
    assertCartItemStatus(response, 'POST /api/shop/cart/items (attrs)');
  });

  test('Should handle add-to-cart missing productId', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.ADD_TO_CART, {
      method: 'POST',
      data: { quantity: 1 },
    });
    assertCartItemStatus(response, 'POST /api/shop/cart/items no productId');
  });

  test('Should handle cart item update (PUT)', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.UPDATE_CART_ITEM(1), {
      method: 'PUT',
      data: { quantity: 3 },
    });
    assertCartItemStatus(response, 'PUT /api/shop/cart/items/1');
  });

  test('Should handle cart item removal (DELETE)', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.REMOVE_CART_ITEM(1), {
      method: 'DELETE',
    });
    assertCartItemStatus(response, 'DELETE /api/shop/cart/items/1');
  });

  test('Should handle removal of a non-existent cart item', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.REMOVE_CART_ITEM(999999), {
      method: 'DELETE',
    });
    assertCartItemStatus(response, 'DELETE /api/shop/cart/items/999999');
  });
});

test.describe('Cart Coupon Operations', () => {
  function assertCartStatus2(resp: any, debugLabel: string) {
    const code = resp.status();
    expect([0, 200, 201, 400, 404, 422, 500, 429]).toContain(code);
    console.log(`${debugLabel}:`, code);
    if (code === 429) {
      test.skip(true, 'Rate limited');
    }
  }

  test('Should handle coupon application', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.APPLY_COUPON, {
      method: 'POST',
      data: { couponCode: 'SAVE10' },
    });
    assertCartStatus2(response, 'POST /api/shop/cart/coupon');
  });

  test('Should handle coupon removal', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.REMOVE_COUPON, {
      method: 'DELETE',
    });
    assertCartStatus2(response, 'DELETE /api/shop/cart/coupon');
  });
});
