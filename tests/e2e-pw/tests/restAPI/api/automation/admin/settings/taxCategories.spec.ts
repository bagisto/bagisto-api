import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_SETTINGS } from '../../../../rest/endpoints/admin.settings.endpoints';

test.describe.configure({ timeout: 60_000 });

const NOT_FOUND = 'Tax category not found.';
const DELETED = 'Tax category deleted successfully.';
const IN_USE = 'This tax category still has tax rates attached and cannot be deleted.';

function uniqueCode(prefix: string): string {
  return `${prefix}_${Date.now().toString(36)}${Math.floor(Math.random() * 1_000_000).toString(36)}`;
}

async function createTaxRate(request: APIRequestContext): Promise<any> {
  const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATES, {
    method: 'POST',
    data: {
      identifier: uniqueCode('e2e_tr'),
      country: 'US',
      state: 'CA',
      is_zip: false,
      zip_code: '94105',
      tax_rate: 8.5,
    },
  });
  const body = await resp.json();
  expect(resp.status(), JSON.stringify(body)).toBe(201);

  return body;
}

async function createTaxCategory(request: APIRequestContext, code: string, taxRateId: number): Promise<any> {
  const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_CATEGORIES, {
    method: 'POST',
    data: {
      code,
      name: `E2E Tax Cat ${code}`,
      description: 'e2e generated',
      taxrates: [taxRateId],
    },
  });
  const body = await resp.json();
  expect(resp.status(), JSON.stringify(body)).toBe(201);

  return body;
}

async function cleanup(request: APIRequestContext, taxRateId: number, categoryId?: number): Promise<void> {
  await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATE(taxRateId), { method: 'DELETE' });

  if (categoryId) {
    await sendAdminRequest(request, ADMIN_SETTINGS.TAX_CATEGORY(categoryId), { method: 'DELETE' });
  }
}

async function codeMatches(request: APIRequestContext, code: string): Promise<number> {
  const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_CATEGORIES, { params: { code } });
  expect(resp.status()).toBe(200);

  return (await resp.json()).data.length;
}

test.describe('admin settings tax categories rest api', () => {
  let code: string;

  test.beforeEach(() => {
    code = uniqueCode('e2e_tc');
  });

  test('listing returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_CATEGORIES);
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(Array.isArray(body.data)).toBe(true);
    expect(typeof body.meta.total).toBe('number');
  });

  test('listing with code filter', async ({ request }) => {
    const rate = await createTaxRate(request);
    const created = await createTaxCategory(request, code, rate.id);

    try {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_CATEGORIES, { params: { code } });
      expect(resp.status()).toBe(200);
      const body = await resp.json();
      expect(body.data).toHaveLength(1);
      expect(body.data[0].id).toBe(created.id);
      expect(body.data[0].code).toBe(code);

      expect(await codeMatches(request, uniqueCode('e2e_absent'))).toBe(0);
    } finally {
      await cleanup(request, rate.id, created.id);
    }
  });

  test('detail non-existent id returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_CATEGORY(99999999));
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe(NOT_FOUND);
  });

  test('create tax category happy path', async ({ request }) => {
    const rate = await createTaxRate(request);
    const created = await createTaxCategory(request, code, rate.id);

    try {
      expect(typeof created.id).toBe('number');
      expect(created.code).toBe(code);
      expect(created.name).toBe(`E2E Tax Cat ${code}`);
      expect(created.description).toBe('e2e generated');
      expect(created.taxRates).toEqual([
        expect.objectContaining({ id: rate.id, identifier: rate.identifier }),
      ]);

      const detail = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_CATEGORY(created.id));
      expect(detail.status()).toBe(200);
      expect((await detail.json()).code).toBe(code);
    } finally {
      await cleanup(request, rate.id, created.id);
    }
  });

  test('create missing taxrates returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_CATEGORIES, {
      method: 'POST',
      data: { code, name: 'No rates', description: 'x' },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The taxrates field is required.');
    expect(await codeMatches(request, code)).toBe(0);
  });

  test('create missing all fields returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_CATEGORIES, {
      method: 'POST',
      data: {},
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The code field is required.');
  });

  test('update tax category name', async ({ request }) => {
    const rate = await createTaxRate(request);
    const created = await createTaxCategory(request, code, rate.id);
    const renamed = `Renamed ${code}`;

    try {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_CATEGORY(created.id), {
        method: 'PUT',
        data: { code, name: renamed, description: 'e2e generated', taxrates: [rate.id] },
      });
      expect(resp.status()).toBe(200);
      const body = await resp.json();
      expect(body.id).toBe(created.id);
      expect(body.name).toBe(renamed);
      expect(body.code).toBe(code);

      const detail = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_CATEGORY(created.id));
      expect((await detail.json()).name).toBe(renamed);
    } finally {
      await cleanup(request, rate.id, created.id);
    }
  });

  test('update tax category without code returns 422', async ({ request }) => {
    const rate = await createTaxRate(request);
    const created = await createTaxCategory(request, code, rate.id);

    try {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_CATEGORY(created.id), {
        method: 'PUT',
        data: { name: `Renamed ${code}`, taxrates: [rate.id] },
      });
      expect(resp.status()).toBe(422);
      expect((await resp.json()).detail).toBe('The code field is required.');

      const detail = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_CATEGORY(created.id));
      expect((await detail.json()).name).toBe(`E2E Tax Cat ${code}`);
    } finally {
      await cleanup(request, rate.id, created.id);
    }
  });

  test('delete with attached rates returns 400', async ({ request }) => {
    const rate = await createTaxRate(request);
    const created = await createTaxCategory(request, code, rate.id);

    try {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_CATEGORY(created.id), {
        method: 'DELETE',
      });
      expect(resp.status()).toBe(400);
      expect((await resp.json()).detail).toBe(IN_USE);

      const detail = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_CATEGORY(created.id));
      expect(detail.status()).toBe(200);
    } finally {
      await cleanup(request, rate.id, created.id);
    }
  });

  test('delete once its only rate is removed returns 200', async ({ request }) => {
    const rate = await createTaxRate(request);
    const created = await createTaxCategory(request, code, rate.id);

    const rateDelete = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATE(rate.id), { method: 'DELETE' });
    expect(rateDelete.status()).toBe(200);

    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_CATEGORY(created.id), {
      method: 'DELETE',
    });
    expect(resp.status()).toBe(200);
    expect((await resp.json()).message).toBe(DELETED);

    const detail = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_CATEGORY(created.id));
    expect(detail.status()).toBe(404);
  });
});
