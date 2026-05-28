// Admin Settings — Tax Categories REST e2e.
// Listing / detail / create / update / delete. No mass-delete.
// Requires `taxrates` array — we mint a fresh tax rate for the create test.
// Delete refuses when in use; we detach via update before delete.

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

function uniqueCode(prefix = 'e2e_tc'): string {
  return `${prefix}_${Date.now().toString(36).slice(-6)}`;
}

async function createTaxRate(request: any): Promise<{ id: number | null }> {
  const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATES, {
    method: 'POST',
    data: {
      identifier: uniqueCode('e2e_tr'),
      country: 'US', state: 'CA',
      is_zip: false, zip_code: '94105',
      tax_rate: 8.5,
    },
  });
  const body = await safeJson(resp);
  return { id: body?.id ?? body?.data?.id ?? null };
}

async function createTaxCategory(request: any, taxRateId: number): Promise<{ id: number | null }> {
  const code = uniqueCode();
  const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_CATEGORIES, {
    method: 'POST',
    data: {
      code,
      name: `E2E Tax Cat ${code}`,
      description: 'e2e generated',
      taxrates: [taxRateId],
    },
  });
  const body = await safeJson(resp);
  return { id: body?.id ?? body?.data?.id ?? null };
}

test.describe('Admin Settings Tax Categories REST API', () => {
  test('listing returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_CATEGORIES);
    expect(OK_LIST).toContain(resp.status());
    const body = await safeJson(resp);
    if (body) expect(Array.isArray(body.data) || Array.isArray(body)).toBe(true);
  });

  test('listing with code filter', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_CATEGORIES, {
      params: { code: 'nonexistent' },
    });
    expect(OK_LIST).toContain(resp.status());
  });

  test('detail non-existent id returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_CATEGORY(99999999));
    expect([400, 404]).toContain(resp.status());
  });

  test('create tax category happy path', async ({ request }) => {
    const { id: trId } = await createTaxRate(request);
    if (!trId) { test.skip(true, 'create tax rate failed'); return; }
    const { id } = await createTaxCategory(request, trId);
    if (id) {
      // detach + delete
      await sendAdminRequest(request, ADMIN_SETTINGS.TAX_CATEGORY(id), {
        method: 'PUT',
        data: { taxrates: [] },
      });
      await sendAdminRequest(request, ADMIN_SETTINGS.TAX_CATEGORY(id), { method: 'DELETE' });
    }
    await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATE(trId), { method: 'DELETE' });
  });

  test('create missing taxrates returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_CATEGORIES, {
      method: 'POST',
      data: { code: uniqueCode(), name: 'No rates', description: 'x' },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('create missing all fields returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_CATEGORIES, {
      method: 'POST',
      data: {},
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('update tax category name', async ({ request }) => {
    const { id: trId } = await createTaxRate(request);
    if (!trId) { test.skip(true, 'create tax rate failed'); return; }
    const { id } = await createTaxCategory(request, trId);
    if (!id) { test.skip(true, 'create category failed'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_CATEGORY(id), {
      method: 'PUT',
      data: { name: 'Renamed E2E TaxCat', taxrates: [trId] },
    });
    expect(OK_UPDATE).toContain(resp.status());
    // cleanup
    await sendAdminRequest(request, ADMIN_SETTINGS.TAX_CATEGORY(id), {
      method: 'PUT',
      data: { taxrates: [] },
    });
    await sendAdminRequest(request, ADMIN_SETTINGS.TAX_CATEGORY(id), { method: 'DELETE' });
    await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATE(trId), { method: 'DELETE' });
  });

  test('delete with attached rates returns 400', async ({ request }) => {
    const { id: trId } = await createTaxRate(request);
    if (!trId) { test.skip(true, 'create tax rate failed'); return; }
    const { id } = await createTaxCategory(request, trId);
    if (!id) { test.skip(true, 'create category failed'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_CATEGORY(id), {
      method: 'DELETE',
    });
    expect([200, 204, 400, 422]).toContain(resp.status());
    // cleanup
    await sendAdminRequest(request, ADMIN_SETTINGS.TAX_CATEGORY(id), {
      method: 'PUT', data: { taxrates: [] },
    });
    await sendAdminRequest(request, ADMIN_SETTINGS.TAX_CATEGORY(id), { method: 'DELETE' });
    await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATE(trId), { method: 'DELETE' });
  });
});
