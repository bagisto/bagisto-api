import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_CATALOG } from '../../../../rest/endpoints/admin.catalog.endpoints';
import { UNSAFE_HTML, assertSanitisedHtml } from '../../../../rest/assertions/adminSanitisedHtml.assertions';

test.describe.configure({ timeout: 60_000 });

const MISSING_ID = 99999999;
const PRODUCT_NOT_FOUND = 'Product not found.';

function uniqueSku(prefix: string): string {
  return `${prefix}-${Date.now()}-${Math.floor(Math.random() * 1_000_000)}`;
}

async function createSimpleProduct(request: APIRequestContext, sku: string): Promise<any> {
  const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCTS, {
    method: 'POST',
    data: { sku, type: 'simple', attribute_family_id: 1 },
  });
  expect(resp.status()).toBe(201);
  const body = await resp.json();
  expect(typeof body.id).toBe('number');
  expect(body.sku).toBe(sku);
  expect(body.type).toBe('simple');

  return body;
}

async function setStatus(request: APIRequestContext, ids: number[], value: number): Promise<any> {
  const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCTS_MASS_UPDATE_STATUS, {
    method: 'POST',
    data: { indices: ids, value },
  });
  expect(resp.status()).toBe(200);

  return resp.json();
}

async function listIds(request: APIRequestContext, params: Record<string, string>): Promise<{ ids: number[]; total: number }> {
  const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCTS, { params });
  expect(resp.status()).toBe(200);
  const body = await resp.json();

  return { ids: body.data.map((row: any) => row.id), total: body.meta.total };
}

test.describe('admin catalog products rest api', () => {
  let createdIds: number[];

  test.beforeEach(() => {
    createdIds = [];
  });

  test.afterEach(async ({ request }) => {
    for (const id of createdIds) {
      await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT(id), { method: 'DELETE' });
    }
  });

  test('listing returns 200 + envelope', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCTS);
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(Array.isArray(body.data)).toBe(true);
    expect(body.meta.currentPage).toBe(1);
  });

  test('listing with filters', async ({ request }) => {
    const prefix = uniqueSku('e2e-lst');
    const first = await createSimpleProduct(request, `${prefix}-a`);
    createdIds.push(first.id);
    const second = await createSimpleProduct(request, `${prefix}-b`);
    createdIds.push(second.id);

    const simple = await listIds(request, { per_page: '5', sort: 'product_id-desc', type: 'simple', sku: prefix });
    expect(simple.total).toBe(2);
    expect(simple.ids).toEqual([second.id, first.id]);

    const virtual = await listIds(request, { per_page: '5', type: 'virtual', sku: prefix });
    expect(virtual.total).toBe(0);
    expect(virtual.ids).toEqual([]);
  });

  test('listing with status filter', async ({ request }) => {
    const prefix = uniqueSku('e2e-st');
    const enabled = await createSimpleProduct(request, `${prefix}-on`);
    createdIds.push(enabled.id);
    const disabled = await createSimpleProduct(request, `${prefix}-off`);
    createdIds.push(disabled.id);

    await setStatus(request, [enabled.id], 1);
    await setStatus(request, [disabled.id], 0);

    expect((await listIds(request, { per_page: '5', status: '1', sku: prefix })).ids).toEqual([enabled.id]);
    expect((await listIds(request, { per_page: '5', status: '0', sku: prefix })).ids).toEqual([disabled.id]);
  });

  test('listing with sku partial filter', async ({ request }) => {
    const prefix = uniqueSku('e2e-part');
    const created = await createSimpleProduct(request, `${prefix}-x`);
    createdIds.push(created.id);

    const result = await listIds(request, { per_page: '5', sku: prefix.slice(4) });
    expect(result.total).toBe(1);
    expect(result.ids).toEqual([created.id]);
  });

  test('detail for a freshly created product', async ({ request }) => {
    const sku = uniqueSku('e2e-det');
    const created = await createSimpleProduct(request, sku);
    createdIds.push(created.id);

    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT(created.id));
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.id).toBe(created.id);
    expect(body.sku).toBe(sku);
    expect(body.type).toBe('simple');
    expect(body.attributeFamilyId).toBe(1);
  });

  test('detail for non-existent product returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT(MISSING_ID));
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe(PRODUCT_NOT_FOUND);
  });

  test('create simple product happy path then cleanup', async ({ request }) => {
    const created = await createSimpleProduct(request, uniqueSku('e2e-sku'));
    createdIds.push(created.id);

    const del = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT(created.id), {
      method: 'DELETE',
    });
    expect(del.status()).toBe(204);

    const gone = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT(created.id));
    expect(gone.status()).toBe(404);
  });

  test('create product with empty body is rejected', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCTS, {
      method: 'POST',
      data: {},
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The sku field is required.');
  });

  test('create product with unknown type is rejected', async ({ request }) => {
    const sku = uniqueSku('e2e-bad-type');
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCTS, {
      method: 'POST',
      data: {
        sku,
        type: 'frobnicator',
        attribute_family_id: 1,
      },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe(
      'Product type "frobnicator" is not supported by this API. Allowed types: simple, virtual, downloadable, grouped, bundle, configurable, booking.'
    );
    expect((await listIds(request, { sku })).total).toBe(0);
  });

  test('update non-existent product returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT(MISSING_ID), {
      method: 'PUT',
      data: { sku: 'nope' },
    });
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe(PRODUCT_NOT_FOUND);
  });

  test('update simple product (partial) then cleanup', async ({ request }) => {
    const created = await createSimpleProduct(request, uniqueSku('e2e-upd'));
    createdIds.push(created.id);
    const name = `E2E Updated ${Date.now()}`;

    const upd = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT(created.id), {
      method: 'PUT',
      data: { name },
    });
    expect(upd.status()).toBe(200);
    const updated = await upd.json();
    expect(updated.id).toBe(created.id);
    expect(updated.name).toBe(name);

    const detail = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT(created.id));
    expect(detail.status()).toBe(200);
    expect((await detail.json()).name).toBe(name);
  });

  test('copy product creates a new row', async ({ request }) => {
    const source = await createSimpleProduct(request, uniqueSku('e2e-copy'));
    createdIds.push(source.id);

    const copyResp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_COPY(source.id), {
      method: 'POST',
      data: {},
    });
    expect(copyResp.status()).toBe(200);
    const copy = await copyResp.json();
    createdIds.push(copy.id);
    expect(copy.id).not.toBe(source.id);
    expect(copy.sourceId).toBe(source.id);
    expect(copy.success).toBe(true);
    expect(copy.message).toBe('Product copied successfully.');
    expect(copy.type).toBe('simple');
    expect(copy.sku).not.toBe(source.sku);

    const detail = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT(copy.id));
    expect(detail.status()).toBe(200);
    expect((await detail.json()).sku).toBe(copy.sku);
  });

  test('copy non-existent product returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_COPY(MISSING_ID), {
      method: 'POST',
      data: {},
    });
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe(PRODUCT_NOT_FOUND);
  });

  test('delete non-existent product returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT(MISSING_ID), {
      method: 'DELETE',
    });
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe(PRODUCT_NOT_FOUND);
  });

  test('mass-delete with empty indices is rejected', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCTS_MASS_DELETE, {
      method: 'POST',
      data: { indices: [] },
    });
    expect(resp.status()).toBe(400);
    expect((await resp.json()).detail).toBe('The indices field is required and must be a non-empty array.');
  });

  test('mass-update-status invalid value rejected', async ({ request }) => {
    const created = await createSimpleProduct(request, uniqueSku('e2e-badst'));
    createdIds.push(created.id);

    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCTS_MASS_UPDATE_STATUS, {
      method: 'POST',
      data: { indices: [created.id], value: 99 },
    });
    expect(resp.status()).toBe(400);
    expect((await resp.json()).detail).toBe('The value field is required and must be 0 or 1.');
  });

  test('mass-delete + mass-update-status round trip on fresh entries', async ({ request }) => {
    const prefix = uniqueSku('e2e-mass');
    const ids: number[] = [];
    for (let i = 0; i < 2; i++) {
      const created = await createSimpleProduct(request, `${prefix}-${i}`);
      ids.push(created.id);
      createdIds.push(created.id);
    }

    const stat = await setStatus(request, ids, 0);
    expect(stat.updated).toEqual(ids);
    expect(stat.message).toBe('Products status updated successfully.');
    expect((await listIds(request, { status: '0', sku: prefix })).total).toBe(2);

    const del = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCTS_MASS_DELETE, {
      method: 'POST',
      data: { indices: ids },
    });
    expect(del.status()).toBe(200);
    const delBody = await del.json();
    expect(delBody.deleted).toEqual(ids);
    expect(delBody.message).toBe('Products deleted successfully.');

    for (const id of ids) {
      const gone = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT(id));
      expect(gone.status()).toBe(404);
    }
  });

  test('should strip scripts and template code from a wysiwyg description on update', async ({ request }) => {
    const id = (await createSimpleProduct(request, uniqueSku('e2e-xss'))).id;
    createdIds.push(id);

    const upd = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT(id), {
      method: 'PUT',
      data: { description: UNSAFE_HTML },
    });
    expect(upd.status()).toBe(200);
    assertSanitisedHtml((await upd.json()).description);

    const detail = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT(id));
    expect(detail.status()).toBe(200);
    assertSanitisedHtml((await detail.json()).description);
  });
});
