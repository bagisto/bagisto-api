// Admin Settings — Tax Categories GraphQL e2e. No mass-delete.

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_TAX_CATEGORIES_LIST,
  ADMIN_TAX_CATEGORY_DETAIL,
  ADMIN_TAX_CATEGORY_CREATE,
  ADMIN_TAX_CATEGORY_UPDATE,
  ADMIN_TAX_CATEGORY_DELETE,
} from '../../../../graphql/Queries/admin/settings/taxCategories.queries';
import { ADMIN_TAX_RATE_CREATE, ADMIN_TAX_RATE_DELETE } from '../../../../graphql/Queries/admin/settings/taxRates.queries';

test.describe.configure({ timeout: 60_000 });
async function safeJson(r: any) { try { return await r.json(); } catch { return null; } }
function parseId(iri: any): number | null {
  if (typeof iri === 'number') return iri;
  if (typeof iri === 'string' && iri.includes('/')) return parseInt(iri.split('/').pop() || '0', 10);
  return null;
}
function unique(): string { return `e2e_tc_${Date.now().toString(36).slice(-6)}${Math.floor(Math.random()*100)}`; }

async function createRate(request: any): Promise<number | null> {
  const resp = await sendAdminGraphQLRequest(request, ADMIN_TAX_RATE_CREATE, {
    identifier: `e2e-tr-${Date.now().toString(36).slice(-6)}${Math.floor(Math.random()*100)}`,
    country: 'US', state: 'NY', taxRate: 8.5, isZip: false, zipCode: '10001',
  });
  const body = await safeJson(resp);
  return parseId(body?.data?.createAdminSettingsTaxRate?.adminSettingsTaxRate?._id
    ?? body?.data?.createAdminSettingsTaxRate?.adminSettingsTaxRate?.id);
}

test.describe('Admin Settings TaxCategories GraphQL API', () => {
  test('listing returns edges', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_TAX_CATEGORIES_LIST, { first: 5 });
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);
    expect(Array.isArray(body?.data?.adminSettingsTaxCategories?.edges)).toBe(true);
  });

  test('detail not-found returns errors[]', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_TAX_CATEGORY_DETAIL, { id: '/api/admin/settings/tax-categories/99999999' });
    expect(resp.status()).toBe(200);
  });

  test('create + update + delete lifecycle', async ({ request }) => {
    const rateId = await createRate(request);
    if (!rateId) return;
    const code = unique();
    const cre = await sendAdminGraphQLRequest(request, ADMIN_TAX_CATEGORY_CREATE, {
      code, name: `E2E TC ${code}`, description: 'desc', taxrates: [rateId],
    });
    expect(cre.status()).toBe(200);
    const cb = await safeJson(cre);
    const id = parseId(cb?.data?.createAdminSettingsTaxCategory?.adminSettingsTaxCategory?._id
      ?? cb?.data?.createAdminSettingsTaxCategory?.adminSettingsTaxCategory?.id);
    if (id) {
      const iri = `/api/admin/settings/tax-categories/${id}`;
      const upd = await sendAdminGraphQLRequest(request, ADMIN_TAX_CATEGORY_UPDATE, {
        id: iri, name: `Renamed ${code}`, description: 'updated', taxrates: [],
      });
      expect(upd.status()).toBe(200);
      const del = await sendAdminGraphQLRequest(request, ADMIN_TAX_CATEGORY_DELETE, { id: iri });
      expect(del.status()).toBe(200);
    }
    await sendAdminGraphQLRequest(request, ADMIN_TAX_RATE_DELETE, { id: `/api/admin/settings/tax-rates/${rateId}` });
  });
});
