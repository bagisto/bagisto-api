import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_CATALOG } from '../../../../rest/endpoints/admin.catalog.endpoints';
import { UNSAFE_HTML, assertSanitisedHtml } from '../../../../rest/assertions/adminSanitisedHtml.assertions';

test.describe.configure({ timeout: 60_000 });

const MISSING_ID = 99999999;
const CATEGORY_NOT_FOUND = 'Category not found.';

function uniqueSuffix(): string {
  return `${Date.now()}-${Math.floor(Math.random() * 1_000_000)}`;
}

function categoryPayload(suffix: string, overrides: Record<string, any> = {}): Record<string, any> {
  return {
    slug: `e2e-cat-${suffix}`,
    name: `E2E Category ${suffix}`,
    description: 'e2e seed',
    position: 1,
    status: 1,
    display_mode: 'products_and_description',
    parent_id: 1,
    attributes: [1],
    ...overrides,
  };
}

async function filterablePriceAttributeId(request: APIRequestContext): Promise<number> {
  const resp = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTES, { params: { code: 'price', per_page: '50' } });
  expect(resp.status()).toBe(200);
  const price = (await resp.json()).data.find((row: any) => row.code === 'price');
  expect(price?.isFilterable, JSON.stringify(price)).toBe(1);

  return price.id;
}

async function createCategory(
  request: APIRequestContext,
  suffix: string,
  overrides: Record<string, any> = {},
): Promise<any> {
  const resp = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORIES, {
    method: 'POST',
    data: categoryPayload(suffix, overrides),
  });
  expect(resp.status()).toBe(201);
  const body = await resp.json();
  expect(typeof body.id).toBe('number');

  return body;
}

test.describe('admin catalog categories rest api', () => {
  let createdIds: number[];

  test.beforeEach(() => {
    createdIds = [];
  });

  test.afterEach(async ({ request }) => {
    for (const id of createdIds) {
      await sendAdminRequest(request, ADMIN_CATALOG.CATEGORY(id), { method: 'DELETE' });
    }
  });

  test('listing returns 200 + envelope shape', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORIES);
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(Array.isArray(body.data)).toBe(true);
    expect(body.meta.currentPage).toBe(1);
  });

  test('listing with pagination params', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORIES, {
      params: { page: '1', per_page: '5' },
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.meta.perPage).toBe(5);
    expect(body.data.length).toBeLessThanOrEqual(5);
  });

  test('listing with name filter', async ({ request }) => {
    const suffix = uniqueSuffix();
    const created = await createCategory(request, suffix);
    createdIds.push(created.id);

    const resp = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORIES, {
      params: { name: `E2E Category ${suffix}` },
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.meta.total).toBe(1);
    expect(body.data.map((row: any) => row.id)).toEqual([created.id]);
  });

  test('tree returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORY_TREE);
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(Array.isArray(body.data)).toBe(true);
  });

  test('detail for root (id=1) returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORY(1));
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.id).toBe(1);
    expect(body.parentId).toBeNull();
  });

  test('detail for non-existent id returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORY(MISSING_ID));
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe(CATEGORY_NOT_FOUND);
  });

  test('create category then delete it', async ({ request }) => {
    const suffix = uniqueSuffix();
    const priceAttributeId = await filterablePriceAttributeId(request);
    const created = await createCategory(request, suffix, { attributes: [priceAttributeId] });
    createdIds.push(created.id);
    expect(created.slug).toBe(`e2e-cat-${suffix}`);
    expect(created.name).toBe(`E2E Category ${suffix}`);
    expect(created.status).toBe(1);
    expect(created.parentId).toBe(1);
    expect(created.filterableAttributeIds).toEqual([priceAttributeId]);

    const delResp = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORY(created.id), {
      method: 'DELETE',
    });
    expect(delResp.status()).toBe(200);
    expect((await delResp.json()).message).toBe('Category deleted successfully.');

    const gone = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORY(created.id));
    expect(gone.status()).toBe(404);
  });

  test('create category with empty attributes is rejected', async ({ request }) => {
    const suffix = uniqueSuffix();
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORIES, {
      method: 'POST',
      data: categoryPayload(suffix, { attributes: [] }),
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The attributes field is required.');

    const search = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORIES, {
      params: { name: `E2E Category ${suffix}` },
    });
    expect((await search.json()).meta.total).toBe(0);
  });

  test('create category with empty body returns validation error', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORIES, {
      method: 'POST',
      data: {},
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The slug field is required.');
  });

  test('update non-existent category returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORY(MISSING_ID), {
      method: 'PUT',
      data: { en: { name: 'nope' } },
    });
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe(CATEGORY_NOT_FOUND);
  });

  test('delete root category (id=1) is refused', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORY(1), {
      method: 'DELETE',
    });
    expect(resp.status()).toBe(400);
    expect((await resp.json()).detail).toBe('Root and channel-root categories cannot be deleted.');

    const still = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORY(1));
    expect(still.status()).toBe(200);
  });

  test('mass-delete with empty indices is rejected', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORIES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [] },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The indices field is required and must be a non-empty array.');
  });

  test('mass-delete + mass-update-status round trip on freshly created entries', async ({ request }) => {
    const ids: number[] = [];
    for (let i = 0; i < 2; i++) {
      const created = await createCategory(request, `${uniqueSuffix()}-${i}`);
      ids.push(created.id);
      createdIds.push(created.id);
    }

    const statusResp = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORIES_MASS_UPDATE_STATUS, {
      method: 'POST',
      data: { indices: ids, value: 0 },
    });
    expect(statusResp.status()).toBe(200);
    const statusBody = await statusResp.json();
    expect(statusBody.updated).toEqual(ids);
    expect(statusBody.message).toBe('Categories status updated successfully.');

    for (const id of ids) {
      const detail = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORY(id));
      expect(detail.status()).toBe(200);
      expect((await detail.json()).status).toBe(0);
    }

    const delResp = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORIES_MASS_DELETE, {
      method: 'POST',
      data: { indices: ids },
    });
    expect(delResp.status()).toBe(200);
    const delBody = await delResp.json();
    expect(delBody.deleted).toEqual(ids);
    expect(delBody.message).toBe('Categories deleted successfully.');

    for (const id of ids) {
      const gone = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORY(id));
      expect(gone.status()).toBe(404);
    }
  });

  test('should strip scripts and template code from a category description on create and update', async ({ request }) => {
    const slug = `e2e-cat-xss-${Date.now()}-${Math.floor(Math.random() * 100000)}`;
    const createResp = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORIES, {
      method: 'POST',
      data: {
        slug,
        name: 'E2E Sanitised Category',
        description: UNSAFE_HTML,
        display_mode: 'products_and_description',
        position: 1,
        attributes: [1],
        locale: 'en',
      },
    });
    expect(createResp.status()).toBe(201);
    const id = (await createResp.json()).id;
    expect(typeof id).toBe('number');

    try {
      const created = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORY(id));
      expect(created.status()).toBe(200);
      assertSanitisedHtml((await created.json()).description);

      const upd = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORY(id), {
        method: 'PUT',
        data: {
          locale: 'en',
          position: 2,
          attributes: [1],
          display_mode: 'products_and_description',
          en: { slug, name: 'E2E Sanitised Category Updated', description: `${UNSAFE_HTML}<p>Updated</p>` },
        },
      });
      expect(upd.status()).toBe(200);

      const updated = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORY(id));
      expect(updated.status()).toBe(200);
      const updatedBody = await updated.json();
      assertSanitisedHtml(updatedBody?.description);
      expect(updatedBody?.description).toContain('<p>Updated</p>');
    } finally {
      await sendAdminRequest(request, ADMIN_CATALOG.CATEGORY(id), { method: 'DELETE' });
    }
  });
});
