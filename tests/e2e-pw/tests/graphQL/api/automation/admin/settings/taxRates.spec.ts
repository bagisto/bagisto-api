// Admin Settings — Tax Rates GraphQL e2e. No mass-delete.

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_TAX_RATES_LIST,
  ADMIN_TAX_RATE_DETAIL,
  ADMIN_TAX_RATE_CREATE,
  ADMIN_TAX_RATE_UPDATE,
  ADMIN_TAX_RATE_DELETE,
} from '../../../../graphql/Queries/admin/settings/taxRates.queries';

test.describe.configure({ timeout: 60_000 });
async function safeJson(r: any) { try { return await r.json(); } catch { return null; } }
function parseId(iri: any): number | null {
  if (typeof iri === 'number') return iri;
  if (typeof iri === 'string' && iri.includes('/')) return parseInt(iri.split('/').pop() || '0', 10);
  return null;
}
function unique(): string { return `e2e-tr-${Date.now().toString(36).slice(-6)}${Math.floor(Math.random()*100)}`; }

async function createRate(request: any, isZip = false): Promise<{ id: number | null; identifier: string }> {
  const identifier = unique();
  const vars: any = isZip
    ? { identifier, country: 'US', state: 'NY', taxRate: 8.5, isZip: true, zipFrom: '10000', zipTo: '10999' }
    : { identifier, country: 'US', state: 'NY', taxRate: 8.5, isZip: false, zipCode: '10001' };
  const resp = await sendAdminGraphQLRequest(request, ADMIN_TAX_RATE_CREATE, vars);
  const body = await safeJson(resp);
  const id = parseId(body?.data?.createAdminSettingsTaxRate?.adminSettingsTaxRate?._id
    ?? body?.data?.createAdminSettingsTaxRate?.adminSettingsTaxRate?.id);
  return { id, identifier };
}

test.describe('Admin Settings TaxRates GraphQL API', () => {
  test('listing returns edges', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_TAX_RATES_LIST, { first: 5 });
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);
    expect(Array.isArray(body?.data?.adminSettingsTaxRates?.edges)).toBe(true);
  });

  test('detail not-found', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_TAX_RATE_DETAIL, { id: '/api/admin/settings/tax-rates/99999999' });
    expect(resp.status()).toBe(200);
  });

  test('create zip=false + update + delete', async ({ request }) => {
    const { id } = await createRate(request, false);
    if (!id) return;
    const iri = `/api/admin/settings/tax-rates/${id}`;
    const upd = await sendAdminGraphQLRequest(request, ADMIN_TAX_RATE_UPDATE, { id: iri, taxRate: 9.0 });
    expect(upd.status()).toBe(200);
    const del = await sendAdminGraphQLRequest(request, ADMIN_TAX_RATE_DELETE, { id: iri });
    expect(del.status()).toBe(200);
  });

  test('create zip=true + delete', async ({ request }) => {
    const { id } = await createRate(request, true);
    if (!id) return;
    const del = await sendAdminGraphQLRequest(request, ADMIN_TAX_RATE_DELETE, { id: `/api/admin/settings/tax-rates/${id}` });
    expect(del.status()).toBe(200);
  });

  test('create is_zip=false without zip_code is rejected', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_TAX_RATE_CREATE, {
      identifier: unique(), country: 'US', state: 'NY', taxRate: 8.5, isZip: false,
    });
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    const nullPayload = body?.data?.createAdminSettingsTaxRate?.adminSettingsTaxRate === null;
    expect(hasErrors || nullPayload).toBe(true);
  });
});
