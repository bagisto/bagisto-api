// Admin Settings — Inventory Sources GraphQL e2e.

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_INV_SOURCES_LIST,
  ADMIN_INV_SOURCE_DETAIL,
  ADMIN_INV_SOURCE_CREATE,
  ADMIN_INV_SOURCE_UPDATE,
  ADMIN_INV_SOURCE_DELETE,
  ADMIN_INV_SOURCE_MASS_DELETE,
} from '../../../../graphql/Queries/admin/settings/inventorySources.queries';

test.describe.configure({ timeout: 60_000 });
async function safeJson(r: any) { try { return await r.json(); } catch { return null; } }
function parseId(iri: any): number | null {
  if (typeof iri === 'number') return iri;
  if (typeof iri === 'string' && iri.includes('/')) return parseInt(iri.split('/').pop() || '0', 10);
  return null;
}
function unique(): string { return `e2e-inv-${Date.now().toString(36).slice(-6)}${Math.floor(Math.random()*100)}`; }

async function createSource(request: any): Promise<{ id: number | null; code: string }> {
  const code = unique();
  const resp = await sendAdminGraphQLRequest(request, ADMIN_INV_SOURCE_CREATE, {
    code, name: `E2E ${code}`, contactName: 'Tester', contactEmail: 'tester@example.com',
    contactNumber: '5555555555', country: 'US', state: 'NY', city: 'NYC',
    street: '1 Test St', postcode: '10001', priority: 0, status: 1,
  });
  const body = await safeJson(resp);
  const id = parseId(body?.data?.createAdminSettingsInventorySource?.adminSettingsInventorySource?._id
    ?? body?.data?.createAdminSettingsInventorySource?.adminSettingsInventorySource?.id);
  return { id, code };
}

test.describe('Admin Settings InventorySources GraphQL API', () => {
  test('listing returns edges', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_INV_SOURCES_LIST, { first: 5 });
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);
    expect(Array.isArray(body?.data?.adminSettingsInventorySources?.edges)).toBe(true);
  });

  test('detail for id=1', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_INV_SOURCE_DETAIL, { id: '/api/admin/settings/inventory-sources/1' });
    expect(resp.status()).toBe(200);
  });

  test('create + update + delete lifecycle', async ({ request }) => {
    const { id, code } = await createSource(request);
    if (!id) return;
    const iri = `/api/admin/settings/inventory-sources/${id}`;
    const upd = await sendAdminGraphQLRequest(request, ADMIN_INV_SOURCE_UPDATE, { id: iri, name: `Renamed ${code}` });
    expect(upd.status()).toBe(200);
    const del = await sendAdminGraphQLRequest(request, ADMIN_INV_SOURCE_DELETE, { id: iri });
    expect(del.status()).toBe(200);
  });

  test('mass-delete fresh row', async ({ request }) => {
    const { id } = await createSource(request);
    if (!id) return;
    const resp = await sendAdminGraphQLRequest(request, ADMIN_INV_SOURCE_MASS_DELETE, { indices: [id] });
    expect(resp.status()).toBe(200);
  });
});
