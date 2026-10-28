import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_CATALOG } from '../../../../rest/endpoints/admin.catalog.endpoints';

test.describe.configure({ timeout: 60_000 });

const MISSING_ID = 99999999;
const FAMILY_NOT_FOUND = 'Attribute family not found.';

function uniqueSuffix(): string {
  return `${Date.now()}_${Math.floor(Math.random() * 1_000_000)}`;
}

function generalGroup(): Record<string, any> {
  return { code: 'general', name: 'General', column: 1, position: 1, custom_attributes: [] };
}

async function createFamily(request: APIRequestContext, code: string, name: string): Promise<any> {
  const resp = await sendAdminRequest(request, ADMIN_CATALOG.FAMILIES, {
    method: 'POST',
    data: { code, name, attribute_groups: [generalGroup()] },
  });
  expect(resp.status()).toBe(201);
  const body = await resp.json();
  expect(typeof body.id).toBe('number');
  expect(body.code).toBe(code);

  return body;
}

test.describe('admin catalog attribute families rest api', () => {
  let createdIds: number[];

  test.beforeEach(() => {
    createdIds = [];
  });

  test.afterEach(async ({ request }) => {
    for (const id of createdIds) {
      await sendAdminRequest(request, ADMIN_CATALOG.FAMILY(id), { method: 'DELETE' });
    }
  });

  test('listing returns 200 + envelope', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.FAMILIES);
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(Array.isArray(body.data)).toBe(true);
    expect(body.meta.currentPage).toBe(1);
  });

  test('listing with filter + sort', async ({ request }) => {
    const prefix = `e2e_famflt_${uniqueSuffix()}`;
    const first = await createFamily(request, `${prefix}_a`, `E2E Filter ${prefix} A`);
    createdIds.push(first.id);
    const second = await createFamily(request, `${prefix}_b`, `E2E Filter ${prefix} B`);
    createdIds.push(second.id);

    const resp = await sendAdminRequest(request, ADMIN_CATALOG.FAMILIES, {
      params: { per_page: '5', sort: 'id-desc', code: prefix },
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.meta.perPage).toBe(5);
    expect(body.meta.total).toBe(2);
    expect(body.data.map((row: any) => row.id)).toEqual([second.id, first.id]);
  });

  test('detail for default family (id=1)', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.FAMILY(1));
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.id).toBe(1);
    expect(body.code).toBe('default');
    expect(Array.isArray(body.attributeGroups)).toBe(true);
    expect(body.attributeGroups.length).toBeGreaterThan(0);
  });

  test('detail for non-existent id', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.FAMILY(MISSING_ID));
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe(FAMILY_NOT_FOUND);
  });

  test('create family with empty body is rejected', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.FAMILIES, {
      method: 'POST',
      data: {},
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The code field is required.');
  });

  test('create + update + delete family', async ({ request }) => {
    const suffix = uniqueSuffix();
    const code = `e2e_fam_${suffix}`;
    const created = await createFamily(request, code, `E2E Family ${suffix}`);
    createdIds.push(created.id);
    expect(created.name).toBe(`E2E Family ${suffix}`);
    expect(created.attributeGroups.map((group: any) => group.code)).toEqual(['general']);

    const upd = await sendAdminRequest(request, ADMIN_CATALOG.FAMILY(created.id), {
      method: 'PUT',
      data: { code, name: `E2E Family ${suffix} v2` },
    });
    expect(upd.status()).toBe(200);
    const updated = await upd.json();
    expect(updated.id).toBe(created.id);
    expect(updated.name).toBe(`E2E Family ${suffix} v2`);

    const reread = await sendAdminRequest(request, ADMIN_CATALOG.FAMILY(created.id));
    expect(reread.status()).toBe(200);
    expect((await reread.json()).name).toBe(`E2E Family ${suffix} v2`);

    const del = await sendAdminRequest(request, ADMIN_CATALOG.FAMILY(created.id), {
      method: 'DELETE',
    });
    expect(del.status()).toBe(200);
    expect((await del.json()).message).toBe('Attribute family deleted successfully.');

    const gone = await sendAdminRequest(request, ADMIN_CATALOG.FAMILY(created.id));
    expect(gone.status()).toBe(404);
  });

  test('delete non-existent family returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.FAMILY(MISSING_ID), {
      method: 'DELETE',
    });
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe(FAMILY_NOT_FOUND);
  });

  test('delete a family that a product uses is refused', async ({ request }) => {
    const suffix = uniqueSuffix();
    const family = await createFamily(request, `e2e_fam_inuse_${suffix}`, `E2E Family In Use ${suffix}`);
    createdIds.push(family.id);

    let productId: number | null = null;

    try {
      const prodResp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCTS, {
        method: 'POST',
        data: {
          sku: `e2e-fam-inuse-${suffix.replace('_', '-')}`,
          attribute_family_id: family.id,
          type: 'simple',
        },
      });
      expect(prodResp.status()).toBe(201);
      productId = (await prodResp.json()).id;
      expect(typeof productId).toBe('number');

      const del = await sendAdminRequest(request, ADMIN_CATALOG.FAMILY(family.id), {
        method: 'DELETE',
      });
      expect(del.status()).toBe(400);
      expect((await del.json()).detail).toBe(
        'This attribute family is in use by one or more products. Reassign those products before deleting it.'
      );

      const still = await sendAdminRequest(request, ADMIN_CATALOG.FAMILY(family.id));
      expect(still.status()).toBe(200);
    } finally {
      if (productId) {
        await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT(productId), { method: 'DELETE' });
      }
    }
  });
});
