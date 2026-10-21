import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_MARKETING } from '../../../../rest/endpoints/admin.marketing.endpoints';

test.describe.configure({ timeout: 120_000 });

function uniqueStamp(): string {
  return `${Date.now()}-${Math.floor(Math.random() * 1_000_000)}`;
}

function basePayload(ts: string) {
  return {
    name: `E2E CR ${ts}`,
    description: 'e2e catalog rule',
    channels: [1],
    customer_groups: [2],
    action_type: 'by_percent',
    discount_amount: 10,
    sort_order: 0,
    status: 1,
    starts_from: null,
    ends_till: null,
    conditions: [],
  };
}

async function createRule(request: APIRequestContext, payload: Record<string, any>): Promise<number> {
  const r = await sendAdminRequest(request, ADMIN_MARKETING.CATALOG_RULES, { method: 'POST', data: payload });
  expect(r.status()).toBe(201);

  return (await r.json()).id;
}

test.describe('admin marketing catalog rules', () => {
  test('listing returns 200 and envelope', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CATALOG_RULES);
    expect(resp.status()).toBe(200);
    expect(Array.isArray((await resp.json()).data)).toBe(true);
  });

  test('listing with pagination', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CATALOG_RULES, {
      params: { page: '1', per_page: '5' },
    });
    expect(resp.status()).toBe(200);
    expect((await resp.json()).meta.perPage).toBe(5);
  });

  test('listing with name filter', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CATALOG_RULES, {
      params: { name: 'nonexistent-xyz' },
    });
    expect(resp.status()).toBe(200);
  });

  test('detail non-existent returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CATALOG_RULE(99999999));
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe('Catalog rule not found.');
  });

  test('create with empty body returns validation error', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CATALOG_RULES, {
      method: 'POST',
      data: {},
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The name field is required.');
  });

  test('create detail update and delete round trip', async ({ request }) => {
    const ts = uniqueStamp();
    const createResp = await sendAdminRequest(request, ADMIN_MARKETING.CATALOG_RULES, {
      method: 'POST',
      data: basePayload(ts),
    });
    expect(createResp.status()).toBe(201);
    const created = await createResp.json();
    expect(created).toEqual(expect.objectContaining({
      name: `E2E CR ${ts}`,
      description: 'e2e catalog rule',
      actionType: 'by_percent',
      discountAmount: 10,
      status: 1,
    }));
    expect(created.channels.map((channel: any) => channel.id)).toEqual([1]);
    const id = created.id;

    const detail = await sendAdminRequest(request, ADMIN_MARKETING.CATALOG_RULE(id));
    expect(detail.status()).toBe(200);
    expect((await detail.json()).name).toBe(`E2E CR ${ts}`);

    const updateResp = await sendAdminRequest(request, ADMIN_MARKETING.CATALOG_RULE(id), {
      method: 'PUT' as any,
      data: { ...basePayload(ts), name: `E2E CR ${ts} updated` },
    });
    expect(updateResp.status()).toBe(200);
    expect(await updateResp.json()).toEqual(expect.objectContaining({ id, name: `E2E CR ${ts} updated` }));

    const reread = await sendAdminRequest(request, ADMIN_MARKETING.CATALOG_RULE(id));
    expect((await reread.json()).name).toBe(`E2E CR ${ts} updated`);

    const delResp = await sendAdminRequest(request, ADMIN_MARKETING.CATALOG_RULE(id), {
      method: 'DELETE',
    });
    expect(delResp.status()).toBe(200);
    expect((await delResp.json()).message).toBe('Catalog rule deleted successfully.');

    const after = await sendAdminRequest(request, ADMIN_MARKETING.CATALOG_RULE(id));
    expect(after.status()).toBe(404);
  });

  test('create with by_percent above 100 returns 422', async ({ request }) => {
    const ts = uniqueStamp();
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CATALOG_RULES, {
      method: 'POST',
      data: { ...basePayload(ts), discount_amount: 200 },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The discount amount field must not be greater than 100.');

    const list = await sendAdminRequest(request, ADMIN_MARKETING.CATALOG_RULES, { params: { name: `E2E CR ${ts}` } });
    expect((await list.json()).data).toEqual([]);
  });

  test('mass-delete empty indices returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CATALOG_RULES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [] },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('No catalog rule IDs supplied for mass-delete.');
  });

  test('mass-delete round trip', async ({ request }) => {
    const ts = uniqueStamp();
    const ids = [
      await createRule(request, { ...basePayload(`${ts}-0`), name: `E2E CR mass ${ts}-0` }),
      await createRule(request, { ...basePayload(`${ts}-1`), name: `E2E CR mass ${ts}-1` }),
    ];

    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CATALOG_RULES_MASS_DELETE, {
      method: 'POST',
      data: { indices: ids },
    });
    expect(resp.status()).toBe(200);
    expect(await resp.json()).toEqual(expect.objectContaining({
      deleted: ids,
      skipped: [],
      message: 'Selected catalog rules deleted successfully.',
    }));

    for (const id of ids) {
      expect((await sendAdminRequest(request, ADMIN_MARKETING.CATALOG_RULE(id))).status()).toBe(404);
    }
  });
});
