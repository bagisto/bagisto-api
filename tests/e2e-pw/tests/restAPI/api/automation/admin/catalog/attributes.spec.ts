import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_CATALOG } from '../../../../rest/endpoints/admin.catalog.endpoints';

test.describe.configure({ timeout: 60_000 });

const MISSING_ID = 99999999;
const ATTRIBUTE_NOT_FOUND = 'Attribute not found.';

function uniqueSuffix(): string {
  return `${Date.now()}_${Math.floor(Math.random() * 1_000_000)}`;
}

async function createAttribute(request: APIRequestContext, type: string, codePrefix = `e2e_${type}`): Promise<any> {
  const suffix = uniqueSuffix();
  const resp = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTES, {
    method: 'POST',
    data: {
      code: `${codePrefix}_${suffix}`,
      admin_name: `E2E ${type} ${suffix}`,
      type,
    },
  });
  expect(resp.status()).toBe(201);
  const body = await resp.json();
  expect(typeof body.id).toBe('number');
  expect(body.type).toBe(type);

  return body;
}

test.describe('admin catalog attributes rest api', () => {
  let createdIds: number[];

  test.beforeEach(() => {
    createdIds = [];
  });

  test.afterEach(async ({ request }) => {
    for (const id of createdIds) {
      await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTE(id), { method: 'DELETE' });
    }
  });

  test('listing returns 200 + envelope', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTES);
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(Array.isArray(body.data)).toBe(true);
    expect(body.meta.currentPage).toBe(1);
    expect(body.meta.perPage).toBe(10);
  });

  test('listing with filter + sort + pagination', async ({ request }) => {
    const prefix = `e2e_flt_${uniqueSuffix()}`;
    const first = await createAttribute(request, 'text', prefix);
    createdIds.push(first.id);
    const second = await createAttribute(request, 'text', prefix);
    createdIds.push(second.id);
    const other = await createAttribute(request, 'textarea', prefix);
    createdIds.push(other.id);

    const resp = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTES, {
      params: { per_page: '5', sort: 'id-desc', type: 'text', code: prefix },
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.meta.perPage).toBe(5);
    expect(body.meta.total).toBe(2);
    expect(body.data.map((row: any) => row.id)).toEqual([second.id, first.id]);
    for (const row of body.data) {
      expect(row.type).toBe('text');
    }
  });

  test('detail for a freshly created attribute', async ({ request }) => {
    const created = await createAttribute(request, 'text');
    createdIds.push(created.id);

    const resp = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTE(created.id));
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.id).toBe(created.id);
    expect(body.code).toBe(created.code);
    expect(body.type).toBe('text');
    expect(body.adminName).toBe(created.adminName);
    expect(body.isUserDefined).toBe(1);
  });

  test('detail for non-existent id returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTE(MISSING_ID));
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe(ATTRIBUTE_NOT_FOUND);
  });

  test('create attribute then delete it', async ({ request }) => {
    const suffix = uniqueSuffix();
    const code = `e2e_attr_${suffix}`;
    const createResp = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTES, {
      method: 'POST',
      data: {
        code,
        admin_name: `E2E Attr ${suffix}`,
        type: 'text',
        is_required: false,
        is_unique: false,
        validation: null,
        position: 0,
      },
    });
    expect(createResp.status()).toBe(201);
    const created = await createResp.json();
    expect(created.code).toBe(code);
    expect(created.adminName).toBe(`E2E Attr ${suffix}`);
    expect(created.type).toBe('text');
    expect(created.isRequired).toBe(0);
    expect(created.isUnique).toBe(0);
    expect(created.isUserDefined).toBe(1);

    const delResp = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTE(created.id), {
      method: 'DELETE',
    });
    expect(delResp.status()).toBe(200);
    expect((await delResp.json()).message).toBe('Attribute deleted successfully.');

    const gone = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTE(created.id));
    expect(gone.status()).toBe(404);
  });

  test('create attribute with empty body is rejected', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTES, {
      method: 'POST',
      data: {},
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The code field is required.');
  });

  test('update non-existent attribute returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTE(MISSING_ID), {
      method: 'PUT',
      data: { admin_name: 'no' },
    });
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe(ATTRIBUTE_NOT_FOUND);
  });

  test('delete non-existent attribute returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTE(MISSING_ID), {
      method: 'DELETE',
    });
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe(ATTRIBUTE_NOT_FOUND);
  });

  test('mass-delete empty indices is rejected', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [] },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The indices field is required and must be a non-empty array.');
  });

  test('mass-delete on freshly created attributes', async ({ request }) => {
    const ids: number[] = [];
    for (let i = 0; i < 2; i++) {
      const created = await createAttribute(request, 'text');
      ids.push(created.id);
      createdIds.push(created.id);
    }

    const del = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTES_MASS_DELETE, {
      method: 'POST',
      data: { indices: ids },
    });
    expect(del.status()).toBe(200);
    const body = await del.json();
    expect(body.deleted).toEqual(ids);
    expect(body.message).toBe('Attributes deleted successfully.');

    for (const id of ids) {
      const gone = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTE(id));
      expect(gone.status()).toBe(404);
    }
  });
});

test.describe('admin catalog attribute options rest api', () => {
  let attributeId: number;

  test.afterEach(async ({ request }) => {
    if (attributeId) {
      await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTE(attributeId), { method: 'DELETE' });
    }
  });

  test('create option + update + delete on a select attribute', async ({ request }) => {
    attributeId = 0;
    attributeId = (await createAttribute(request, 'select')).id;

    const suffix = uniqueSuffix();
    const createResp = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTE_OPTIONS(attributeId), {
      method: 'POST',
      data: {
        admin_name: `Option ${suffix}`,
        sort_order: 0,
      },
    });
    expect(createResp.status()).toBe(201);
    const created = await createResp.json();
    expect(created.id).toBe(attributeId);
    const option = created.options.find((row: any) => row.adminName === `Option ${suffix}`);
    expect(option).toBeDefined();
    expect(option.sortOrder).toBe(0);

    const upd = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTE_OPTION(attributeId, option.id), {
      method: 'PUT',
      data: { admin_name: `Option ${suffix} v2` },
    });
    expect(upd.status()).toBe(200);
    const updated = await upd.json();
    expect(updated.options.find((row: any) => row.id === option.id).adminName).toBe(`Option ${suffix} v2`);

    const del = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTE_OPTION(attributeId, option.id), {
      method: 'DELETE',
    });
    expect(del.status()).toBe(200);
    expect((await del.json()).message).toBe('Attribute option deleted successfully.');

    const detail = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTE(attributeId));
    expect(detail.status()).toBe(200);
    expect((await detail.json()).options.map((row: any) => row.id)).not.toContain(option.id);
  });

  test('create option on non-select attribute is rejected', async ({ request }) => {
    attributeId = 0;
    attributeId = (await createAttribute(request, 'text')).id;

    const resp = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTE_OPTIONS(attributeId), {
      method: 'POST',
      data: { admin_name: 'nope' },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe(
      'Attribute type "text" does not support options. Only select, multiselect, and checkbox attributes can have options.'
    );

    const detail = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTE(attributeId));
    expect(detail.status()).toBe(200);
    expect((await detail.json()).options).toEqual([]);
  });
});
