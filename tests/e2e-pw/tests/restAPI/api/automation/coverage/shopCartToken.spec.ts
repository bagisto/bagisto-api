import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import {
  SHOP_CART_ENDPOINTS,
  SHOP_CART_MESSAGES,
  addCartLine,
  bearer,
  createGuestCart,
  expectProblem,
  findSaleableSimpleProducts,
  randomUuid,
  readCart,
  readJson,
  registerShopCustomer,
} from '../../../rest/helpers/coverage/shopCart.helpers';

test.describe.configure({ timeout: 120_000 });

test.describe('shop cart token rest api', () => {
  test('should create a guest cart with a fresh session token', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.CART_TOKENS, {
      method: 'POST',
      data: { createNew: true },
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.success).toBe(true);
    expect(body.isGuest).toBe(true);
    expect(body.message).toBe(SHOP_CART_MESSAGES.NEW_GUEST_CART);
    expect(body.sessionToken).toMatch(/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/);
    expect(body.cartToken).toBe(body.sessionToken);
    expect(body.customerId ?? null).toBeNull();
    expect(body.itemsCount ?? 0).toBe(0);
    expect(Number(body.id)).toBeGreaterThan(0);
  });

  test('should issue a different guest cart on every call', async ({ request }) => {
    const first = await createGuestCart(request);
    const second = await createGuestCart(request);

    expect(second.token).not.toBe(first.token);
    expect(second.cartId).not.toBe(first.cartId);
  });

  test('should return the same customer cart to an authenticated customer', async ({ request }) => {
    const customer = await registerShopCustomer(request);

    const first = await sendRestRequest(request, SHOP_CART_ENDPOINTS.CART_TOKENS, {
      method: 'POST',
      data: {},
      headers: bearer(customer.token),
    });
    const firstBody = await readJson(first);

    expect(first.status(), JSON.stringify(firstBody)).toBe(201);
    expect(firstBody.isGuest).toBe(false);
    expect(firstBody.success).toBe(true);
    expect(firstBody.message).toBe(SHOP_CART_MESSAGES.AUTHENTICATED_CART);
    expect(firstBody.customerId).toBe(customer.id);
    expect(firstBody.sessionToken ?? null).toBeNull();

    const second = await sendRestRequest(request, SHOP_CART_ENDPOINTS.CART_TOKENS, {
      method: 'POST',
      data: {},
      headers: bearer(customer.token),
    });
    const secondBody = await readJson(second);

    expect(second.status(), JSON.stringify(secondBody)).toBe(201);
    expect(secondBody.id).toBe(firstBody.id);
  });

  test('should refuse a cart token request without a storefront key', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.CART_TOKENS, {
      method: 'POST',
      data: { createNew: true },
      headers: { 'X-STOREFRONT-KEY': '' },
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(401);
    expect(body.error).toBe('missing_key');
    expect(body.message).toBe('X-STOREFRONT-KEY header is required');
  });
});

test.describe('shop verify token rest api', () => {
  test('should confirm a valid customer token with the customer details', async ({ request }) => {
    const customer = await registerShopCustomer(request);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.VERIFY_TOKENS, {
      method: 'POST',
      data: {},
      headers: bearer(customer.token),
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.isValid).toBe(true);
    expect(body.id).toBe(customer.id);
    expect(body.email).toBe(customer.email);
    expect(body.firstName).toBe(customer.firstName);
    expect(body.lastName).toBe(customer.lastName);
    expect(body.message).toBe(SHOP_CART_MESSAGES.TOKEN_IS_VALID);
  });

  test('should report an unauthenticated result without a bearer token', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.VERIFY_TOKENS, {
      method: 'POST',
      data: {},
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.isValid).toBe(false);
    expect(body.id).toBe(0);
    expect(body.email).toBe('');
    expect(body.message).toBe(SHOP_CART_MESSAGES.UNAUTHENTICATED);
  });

  test('should not accept a guest cart token as a customer token', async ({ request }) => {
    const guest = await createGuestCart(request);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.VERIFY_TOKENS, {
      method: 'POST',
      data: {},
      headers: bearer(guest.token),
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.isValid).toBe(false);
    expect(body.id).toBe(0);
    expect(body.message).toBe(SHOP_CART_MESSAGES.UNAUTHENTICATED);
  });
});

test.describe('shop merge cart rest api', () => {
  test('should hand a guest cart over to a customer without a cart', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const guest = await createGuestCart(request);
    const line = await addCartLine(request, guest.token, productId, 2);
    const customer = await registerShopCustomer(request);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.MERGE_CARTS, {
      method: 'POST',
      data: { guest_cart_token: guest.token, cart_id: line.cartId },
      headers: bearer(customer.token),
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.success).toBe(true);
    expect(body.message).toBe(SHOP_CART_MESSAGES.GUEST_CART_MERGED);
    expect(body.isGuest).toBe(false);
    expect(body.customerId).toBe(customer.id);

    const merged = (body.items ?? []).find((item: any) => Number(item.productId) === productId);

    expect(merged, JSON.stringify(body)).toBeTruthy();
    expect(Number(merged.quantity)).toBe(2);

    await expectProblem(await readCart(request, guest.token), 404, SHOP_CART_MESSAGES.CART_NOT_FOUND);
  });

  test('should add the guest quantities to the customer cart', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const customer = await registerShopCustomer(request);
    await addCartLine(request, customer.token, productId, 1);

    const guest = await createGuestCart(request);
    await addCartLine(request, guest.token, productId, 3);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.MERGE_CARTS, {
      method: 'POST',
      data: { guest_cart_token: guest.token },
      headers: bearer(customer.token),
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.success).toBe(true);

    const merged = (body.items ?? []).find((item: any) => Number(item.productId) === productId);

    expect(merged, JSON.stringify(body)).toBeTruthy();
    expect(Number(merged.quantity)).toBe(4);
  });

  test('should refuse a merge without a bearer token', async ({ request }) => {
    const guest = await createGuestCart(request);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.MERGE_CARTS, {
      method: 'POST',
      data: { guest_cart_token: guest.token },
    });

    await expectProblem(response, 401, SHOP_CART_MESSAGES.AUTHENTICATION_REQUIRED);
  });

  test('should refuse a merge authorised only by a guest cart token', async ({ request }) => {
    const guest = await createGuestCart(request);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.MERGE_CARTS, {
      method: 'POST',
      data: { guest_cart_token: guest.token },
      headers: bearer(guest.token),
    });

    await expectProblem(response, 401, SHOP_CART_MESSAGES.MERGE_REQUIRES_AUTH);
  });

  test('should report an unknown guest cart token as not found', async ({ request }) => {
    const customer = await registerShopCustomer(request);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.MERGE_CARTS, {
      method: 'POST',
      data: { guest_cart_token: randomUuid() },
      headers: bearer(customer.token),
    });

    await expectProblem(response, 404, SHOP_CART_MESSAGES.GUEST_CART_NOT_FOUND);
  });

  test('should leave the guest cart alone when the cart id does not match its token', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const guest = await createGuestCart(request);
    const line = await addCartLine(request, guest.token, productId, 1);
    const customer = await registerShopCustomer(request);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.MERGE_CARTS, {
      method: 'POST',
      data: { guest_cart_token: guest.token, cart_id: line.cartId + 100000 },
      headers: bearer(customer.token),
    });

    await expectProblem(response, 404, SHOP_CART_MESSAGES.GUEST_CART_NOT_FOUND);

    const guestCart = await readCart(request, guest.token);
    const guestBody = await readJson(guestCart);

    expect(guestCart.status(), JSON.stringify(guestBody)).toBe(201);
    expect(guestBody.isGuest).toBe(true);
    expect(guestBody.itemsCount).toBe(1);
  });
});
