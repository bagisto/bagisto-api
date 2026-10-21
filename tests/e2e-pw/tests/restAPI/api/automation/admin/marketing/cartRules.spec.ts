import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_MARKETING } from '../../../../rest/endpoints/admin.marketing.endpoints';

test.describe.configure({ timeout: 60_000 });

function uniqueStamp(): string {
  return `${Date.now()}-${Math.floor(Math.random() * 1_000_000)}`;
}

function basePayload(ts: string) {
  return {
    name: `E2E CartRule ${ts}`,
    description: 'e2e cart rule',
    channels: [1],
    customer_groups: [2],
    coupon_type: 0,
    use_auto_generation: 0,
    action_type: 'by_percent',
    discount_amount: 5,
    sort_order: 0,
    status: 1,
    conditions: [],
    uses_per_customer: 0,
    uses_per_coupon: 0,
  };
}

async function createRule(request: APIRequestContext, payload: Record<string, any>): Promise<number> {
  const r = await sendAdminRequest(request, ADMIN_MARKETING.CART_RULES, { method: 'POST', data: payload });
  expect(r.status()).toBe(201);

  return (await r.json()).id;
}

test.describe('admin marketing cart rules', () => {
  test('listing returns 200 and envelope', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CART_RULES);
    expect(resp.status()).toBe(200);
    expect(Array.isArray((await resp.json()).data)).toBe(true);
  });

  test('listing with pagination', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CART_RULES, {
      params: { page: '1', per_page: '5' },
    });
    expect(resp.status()).toBe(200);
    expect((await resp.json()).meta.perPage).toBe(5);
  });

  test('detail non-existent returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CART_RULE(99999999));
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe('Cart rule not found.');
  });

  test('create with empty body returns validation error', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CART_RULES, {
      method: 'POST',
      data: {},
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The name field is required.');
  });

  test('create detail update and delete round trip', async ({ request }) => {
    const ts = uniqueStamp();
    const createResp = await sendAdminRequest(request, ADMIN_MARKETING.CART_RULES, {
      method: 'POST',
      data: basePayload(ts),
    });
    expect(createResp.status()).toBe(201);
    const created = await createResp.json();
    expect(created).toEqual(expect.objectContaining({
      name: `E2E CartRule ${ts}`,
      description: 'e2e cart rule',
      couponType: 0,
      actionType: 'by_percent',
      discountAmount: 5,
      status: 1,
    }));
    const id = created.id;

    const detail = await sendAdminRequest(request, ADMIN_MARKETING.CART_RULE(id));
    expect(detail.status()).toBe(200);
    expect((await detail.json()).name).toBe(`E2E CartRule ${ts}`);

    const updateResp = await sendAdminRequest(request, ADMIN_MARKETING.CART_RULE(id), {
      method: 'PUT' as any,
      data: { name: `E2E CartRule ${ts} updated` },
    });
    expect(updateResp.status()).toBe(200);
    expect(await updateResp.json()).toEqual(expect.objectContaining({ id, name: `E2E CartRule ${ts} updated`, discountAmount: 5 }));

    const reread = await sendAdminRequest(request, ADMIN_MARKETING.CART_RULE(id));
    expect((await reread.json()).name).toBe(`E2E CartRule ${ts} updated`);

    const delResp = await sendAdminRequest(request, ADMIN_MARKETING.CART_RULE(id), {
      method: 'DELETE',
    });
    expect(delResp.status()).toBe(200);
    expect((await delResp.json()).message).toBe('Cart rule deleted successfully.');

    const gone = await sendAdminRequest(request, ADMIN_MARKETING.CART_RULE(id));
    expect(gone.status()).toBe(404);
  });

  test('create coupon_type 1 without coupon_code returns 422', async ({ request }) => {
    const ts = uniqueStamp();
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CART_RULES, {
      method: 'POST',
      data: { ...basePayload(ts), coupon_type: 1, use_auto_generation: 0 },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The coupon code field is required.');
  });

  test('mass-delete empty indices returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CART_RULES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [] },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The indices field is required and must be a non-empty array.');
  });

  test('mass-delete round trip', async ({ request }) => {
    const ts = uniqueStamp();
    const ids = [
      await createRule(request, { ...basePayload(`${ts}-0`), name: `E2E CartRule mass ${ts}-0` }),
      await createRule(request, { ...basePayload(`${ts}-1`), name: `E2E CartRule mass ${ts}-1` }),
    ];

    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CART_RULES_MASS_DELETE, {
      method: 'POST',
      data: { indices: ids },
    });
    expect(resp.status()).toBe(200);
    expect(await resp.json()).toEqual(expect.objectContaining({
      deleted: ids,
      skipped: [],
      message: 'Cart rules deleted successfully.',
    }));

    for (const id of ids) {
      expect((await sendAdminRequest(request, ADMIN_MARKETING.CART_RULE(id))).status()).toBe(404);
    }
  });
});
