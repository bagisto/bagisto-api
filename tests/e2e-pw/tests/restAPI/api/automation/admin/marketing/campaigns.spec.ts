import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_MARKETING } from '../../../../rest/endpoints/admin.marketing.endpoints';

test.describe.configure({ timeout: 60_000 });

function uniqueStamp(): string {
  return `${Date.now()}-${Math.floor(Math.random() * 1_000_000)}`;
}

async function makeTemplate(request: APIRequestContext, ts: string): Promise<number> {
  const r = await sendAdminRequest(request, ADMIN_MARKETING.TEMPLATES, {
    method: 'POST',
    data: {
      name: `E2E Tpl for camp ${ts}`,
      status: 'active',
      content: '<p>e2e</p>',
    },
  });
  expect(r.status()).toBe(201);

  return (await r.json()).id;
}

test.describe('admin marketing campaigns', () => {
  test('listing returns 200 and envelope', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CAMPAIGNS);
    expect(resp.status()).toBe(200);
    expect(Array.isArray((await resp.json()).data)).toBe(true);
  });

  test('listing with pagination', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CAMPAIGNS, {
      params: { page: '1', per_page: '5' },
    });
    expect(resp.status()).toBe(200);
    expect((await resp.json()).meta.perPage).toBe(5);
  });

  test('detail non-existent returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CAMPAIGN(99999999));
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe('Campaign not found.');
  });

  test('create with empty body returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CAMPAIGNS, {
      method: 'POST',
      data: {},
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The name field is required.');
  });

  test('create detail update and delete round trip', async ({ request }) => {
    const ts = uniqueStamp();
    const tplId = await makeTemplate(request, ts);

    const createResp = await sendAdminRequest(request, ADMIN_MARKETING.CAMPAIGNS, {
      method: 'POST',
      data: {
        name: `E2E Camp ${ts}`,
        subject: `Test subject ${ts}`,
        marketing_template_id: tplId,
        channel_id: 1,
        customer_group_id: 2,
        status: 0,
      },
    });
    expect(createResp.status()).toBe(201);
    const created = await createResp.json();
    expect(created).toEqual(expect.objectContaining({ name: `E2E Camp ${ts}`, subject: `Test subject ${ts}`, status: 0 }));
    expect(created.marketingTemplate.id).toBe(tplId);
    const id = created.id;

    const detail = await sendAdminRequest(request, ADMIN_MARKETING.CAMPAIGN(id));
    expect(detail.status()).toBe(200);
    expect((await detail.json()).name).toBe(`E2E Camp ${ts}`);

    const updateResp = await sendAdminRequest(request, ADMIN_MARKETING.CAMPAIGN(id), {
      method: 'PUT' as any,
      data: {
        name: `E2E Camp ${ts} updated`,
        subject: `Updated subject ${ts}`,
        marketing_template_id: tplId,
        channel_id: 1,
        customer_group_id: 2,
        status: 0,
      },
    });
    expect(updateResp.status()).toBe(200);
    expect(await updateResp.json()).toEqual(expect.objectContaining({ id, name: `E2E Camp ${ts} updated`, subject: `Updated subject ${ts}` }));

    const reread = await sendAdminRequest(request, ADMIN_MARKETING.CAMPAIGN(id));
    expect((await reread.json()).subject).toBe(`Updated subject ${ts}`);

    const delResp = await sendAdminRequest(request, ADMIN_MARKETING.CAMPAIGN(id), {
      method: 'DELETE',
    });
    expect(delResp.status()).toBe(200);
    expect((await delResp.json()).message).toBe('Campaign deleted successfully.');

    const gone = await sendAdminRequest(request, ADMIN_MARKETING.CAMPAIGN(id));
    expect(gone.status()).toBe(404);

    await sendAdminRequest(request, ADMIN_MARKETING.TEMPLATE(tplId), { method: 'DELETE' });
  });
});
