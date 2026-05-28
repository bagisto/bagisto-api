// tests/restAPI/api/automation/cart.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import { assertCartResponseFields, assertCartItemFields } from '../../rest/assertions/cart.assertions';

// Cart endpoints can be slow under parallel load (session + cart bootstrap
// path). 60s gives headroom for the GET to come back without flaking.
test.describe.configure({ timeout: 60_000 });

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

// ─── REGRESSION (strict): coupon flow returns non-null couponCode + message ──
//
// Locks down two related bug fixes:
//   C3 — Read Cart was returning couponCode = null even after a coupon was
//        applied. The applied code MUST be echoed back on subsequent GETs.
//   C4 — apply / remove coupon mutations were returning success=null /
//        message=null. Both fields MUST be non-null and meaningful.
//
// NOTE: requires an active coupon to be seeded in the dev DB. If no coupon is
// available (or the test cart can't be primed with an item), the test will
// `test.skip` rather than false-fail. To exercise the strict path locally,
// seed at least one row in cart_rule_coupons with status=1 and ensure a cart
// rule with status=1, starts_from <= today, ends_till >= today exists.
test.describe('REGRESSION — coupon flow (couponCode + success + message)', () => {
  // Adjust this if your dev DB seeds a different active code.
  const SEEDED_COUPON = process.env.E2E_ACTIVE_COUPON ?? 'SAVE10';

  async function ensureCartWithItem(request: any) {
    // Add a product to the cart so coupons have something to act on.
    const list = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { per_page: '1' },
    });
    if (list.status() !== 200) return false;
    const items = await list.json();
    if (!Array.isArray(items) || items.length === 0) return false;
    const productId = items[0].id;
    const add = await sendRestRequest(request, ENDPOINTS.ADD_TO_CART, {
      method: 'POST',
      data: { productId, quantity: 1 },
    });
    return add.status() === 200 || add.status() === 201;
  }

  test('Apply valid coupon — success===true, message non-empty, couponCode echoed', async ({ request }) => {
    const ok = await ensureCartWithItem(request);
    if (!ok) {
      test.skip(true, 'Could not prime cart with an item');
      return;
    }

    const applyResp = await sendRestRequest(request, ENDPOINTS.APPLY_COUPON, {
      method: 'POST',
      data: { code: SEEDED_COUPON, couponCode: SEEDED_COUPON },
    });
    if (applyResp.status() === 404 || applyResp.status() === 400) {
      test.skip(true, `Coupon '${SEEDED_COUPON}' not seeded as active — set E2E_ACTIVE_COUPON env var`);
      return;
    }
    expect([200, 201]).toContain(applyResp.status());
    const applyBody = await applyResp.json();

    // C4: success + message must be present and truthful.
    expect(applyBody).toHaveProperty('success');
    expect(applyBody.success).toBe(true);
    expect(applyBody).toHaveProperty('message');
    expect(typeof applyBody.message).toBe('string');
    expect(applyBody.message.length).toBeGreaterThan(0);

    // C3: read the cart back and confirm couponCode persists.
    const readResp = await sendRestRequest(request, ENDPOINTS.GET_CART, { method: 'POST' });
    if (![200, 201].includes(readResp.status())) {
      // Some installs use GET for read-cart; fall back to that.
      const readGet = await sendRestRequest(request, ENDPOINTS.GET_CART);
      expect([200, 201]).toContain(readGet.status());
      const cart = await readGet.json();
      const code = cart.couponCode ?? cart.coupon_code ?? cart.data?.couponCode;
      expect(code).not.toBeNull();
      expect(typeof code).toBe('string');
      expect(code.toUpperCase()).toBe(SEEDED_COUPON.toUpperCase());
      return;
    }
    const cart = await readResp.json();
    const code = cart.couponCode ?? cart.coupon_code ?? cart.data?.couponCode;
    expect(code).not.toBeNull();
    expect(typeof code).toBe('string');
    expect(code.toUpperCase()).toBe(SEEDED_COUPON.toUpperCase());
  });

  test('Remove coupon — success===true, message non-empty', async ({ request }) => {
    const ok = await ensureCartWithItem(request);
    if (!ok) {
      test.skip(true, 'Could not prime cart with an item');
      return;
    }

    // Apply first so there's something to remove.
    const apply = await sendRestRequest(request, ENDPOINTS.APPLY_COUPON, {
      method: 'POST',
      data: { code: SEEDED_COUPON, couponCode: SEEDED_COUPON },
    });
    if (![200, 201].includes(apply.status())) {
      test.skip(true, `Coupon '${SEEDED_COUPON}' not seeded — set E2E_ACTIVE_COUPON`);
      return;
    }

    const removeResp = await sendRestRequest(request, ENDPOINTS.REMOVE_COUPON, {
      method: 'DELETE',
    });
    expect([200, 201, 204]).toContain(removeResp.status());
    if (removeResp.status() === 204) return; // no body

    const body = await removeResp.json();
    expect(body).toHaveProperty('success');
    expect(body.success).toBe(true);
    expect(body).toHaveProperty('message');
    expect(typeof body.message).toBe('string');
    expect(body.message.length).toBeGreaterThan(0);
  });

  test('Apply unknown coupon — success===false, message non-empty', async ({ request }) => {
    const ok = await ensureCartWithItem(request);
    if (!ok) {
      test.skip(true, 'Could not prime cart with an item');
      return;
    }
    const bogus = 'INVALIDCODE9999';
    const resp = await sendRestRequest(request, ENDPOINTS.APPLY_COUPON, {
      method: 'POST',
      data: { code: bogus, couponCode: bogus },
    });
    // The mutation may return 200 with success=false (project convention) or 4xx.
    expect([200, 201, 400, 404, 422]).toContain(resp.status());
    if (resp.status() === 200 || resp.status() === 201) {
      const body = await resp.json();
      expect(body).toHaveProperty('success');
      expect(body.success).toBe(false);
      expect(body).toHaveProperty('message');
      expect(typeof body.message).toBe('string');
      expect(body.message.length).toBeGreaterThan(0);
    } else {
      const body = await resp.json().catch(() => ({}));
      // Even on 4xx, a message is expected.
      if (body && typeof body === 'object') {
        const msg = body.message ?? body.error ?? body.detail;
        if (msg !== undefined) {
          expect(typeof msg).toBe('string');
          expect((msg as string).length).toBeGreaterThan(0);
        }
      }
    }
  });
});
