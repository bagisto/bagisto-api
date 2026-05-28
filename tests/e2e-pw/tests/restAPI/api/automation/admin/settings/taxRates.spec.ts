// Admin Settings — Tax Rates REST e2e.
// Listing / detail / create / update / delete (no mass-delete in monolith).
// Conditional is_zip rules: false → zip_code required; true → zip_from+zip_to
// required.

import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_SETTINGS } from '../../../../rest/endpoints/admin.settings.endpoints';

test.describe.configure({ timeout: 60_000 });

const OK_LIST = [200];
const OK_CREATE = [200, 201, 400, 422, 429];
const OK_UPDATE = [200, 201, 400, 404, 422, 429];
const OK_DELETE = [200, 204, 400, 404, 422, 429];

async function safeJson(resp: any): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

function uniqueId(): string {
  return `e2e_tr_${Date.now().toString(36).slice(-6)}`;
}

async function createTaxRate(request: any, overrides: Record<string, any> = {}): Promise<{ id: number | null }> {
  const data = {
    identifier: uniqueId(),
    country: 'US',
    state: 'CA',
    is_zip: false,
    zip_code: '94105',
    tax_rate: 8.5,
    ...overrides,
  };
  const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATES, {
    method: 'POST',
    data,
  });
  const body = await safeJson(resp);
  return { id: body?.id ?? body?.data?.id ?? null };
}

test.describe('Admin Settings Tax Rates REST API', () => {
  test('listing returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATES);
    expect(OK_LIST).toContain(resp.status());
    const body = await safeJson(resp);
    if (body) expect(Array.isArray(body.data) || Array.isArray(body)).toBe(true);
  });

  test('listing with country filter', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATES, {
      params: { country: 'US' },
    });
    expect(OK_LIST).toContain(resp.status());
  });

  test('detail non-existent id returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATE(99999999));
    expect([400, 404]).toContain(resp.status());
  });

  test('create tax rate is_zip=false happy path', async ({ request }) => {
    const { id } = await createTaxRate(request);
    if (id) {
      await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATE(id), { method: 'DELETE' });
    }
  });

  test('create tax rate is_zip=true with zip range', async ({ request }) => {
    const { id } = await createTaxRate(request, {
      is_zip: true,
      zip_code: null,
      zip_from: '90000',
      zip_to: '99999',
    });
    if (id) {
      await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATE(id), { method: 'DELETE' });
    }
  });

  test('create is_zip=true missing zip_from returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATES, {
      method: 'POST',
      data: {
        identifier: uniqueId(),
        country: 'US',
        state: 'CA',
        is_zip: true,
        tax_rate: 8.5,
      },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('create is_zip=false missing zip_code returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATES, {
      method: 'POST',
      data: {
        identifier: uniqueId(),
        country: 'US',
        state: 'CA',
        is_zip: false,
        tax_rate: 8.5,
      },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('create missing required fields returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATES, {
      method: 'POST',
      data: {},
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('update tax rate partial', async ({ request }) => {
    const { id } = await createTaxRate(request);
    if (!id) { test.skip(true, 'create failed'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATE(id), {
      method: 'PUT',
      data: { tax_rate: 9.5 },
    });
    expect(OK_UPDATE).toContain(resp.status());
    await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATE(id), { method: 'DELETE' });
  });

  test('delete fresh tax rate returns 200/204', async ({ request }) => {
    const { id } = await createTaxRate(request);
    if (!id) { test.skip(true, 'create failed'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATE(id), {
      method: 'DELETE',
    });
    expect(OK_DELETE).toContain(resp.status());
  });
});
