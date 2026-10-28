import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_SETTINGS } from '../../../../rest/endpoints/admin.settings.endpoints';

test.describe.configure({ timeout: 60_000 });

const NOT_FOUND = 'Tax rate not found.';
const DELETED = 'Tax rate deleted successfully.';
const ZIP_CODE_REQUIRED = 'The zip_code field is required when is_zip is false.';
const ZIP_RANGE_REQUIRED = 'Both zip_from and zip_to are required when is_zip is true.';

function uniqueIdentifier(): string {
  return `e2e_tr_${Date.now().toString(36)}${Math.floor(Math.random() * 1_000_000).toString(36)}`;
}

async function createTaxRate(
  request: APIRequestContext,
  identifier: string,
  overrides: Record<string, any> = {}
): Promise<any> {
  const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATES, {
    method: 'POST',
    data: {
      identifier,
      country: 'US',
      state: 'CA',
      is_zip: false,
      zip_code: '94105',
      tax_rate: 8.5,
      ...overrides,
    },
  });
  const body = await resp.json();
  expect(resp.status(), JSON.stringify(body)).toBe(201);

  return body;
}

async function deleteTaxRate(request: APIRequestContext, id: number): Promise<void> {
  await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATE(id), { method: 'DELETE' });
}

async function identifierMatches(request: APIRequestContext, identifier: string): Promise<number> {
  const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATES, { params: { identifier } });
  expect(resp.status()).toBe(200);

  return (await resp.json()).data.length;
}

test.describe('admin settings tax rates rest api', () => {
  let identifier: string;

  test.beforeEach(() => {
    identifier = uniqueIdentifier();
  });

  test('listing returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATES);
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(Array.isArray(body.data)).toBe(true);
    expect(typeof body.meta.total).toBe('number');
  });

  test('listing with country filter', async ({ request }) => {
    const created = await createTaxRate(request, identifier);

    try {
      const matching = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATES, {
        params: { country: 'US', identifier },
      });
      expect(matching.status()).toBe(200);
      const body = await matching.json();
      expect(body.data).toHaveLength(1);
      expect(body.data[0].id).toBe(created.id);
      expect(body.data[0].country).toBe('US');

      const otherCountry = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATES, {
        params: { country: 'IN', identifier },
      });
      expect(otherCountry.status()).toBe(200);
      expect((await otherCountry.json()).data).toHaveLength(0);
    } finally {
      await deleteTaxRate(request, created.id);
    }
  });

  test('detail non-existent id returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATE(99999999));
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe(NOT_FOUND);
  });

  test('create tax rate with is zip false', async ({ request }) => {
    const created = await createTaxRate(request, identifier);

    try {
      expect(typeof created.id).toBe('number');
      expect(created.identifier).toBe(identifier);
      expect(created.isZip).toBe(false);
      expect(created.zipCode).toBe('94105');
      expect(created.zipFrom).toBeNull();
      expect(created.zipTo).toBeNull();
      expect(created.country).toBe('US');
      expect(created.state).toBe('CA');
      expect(created.taxRate).toBe(8.5);

      const detail = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATE(created.id));
      expect(detail.status()).toBe(200);
      expect((await detail.json()).identifier).toBe(identifier);
    } finally {
      await deleteTaxRate(request, created.id);
    }
  });

  test('create tax rate with is zip true and a zip range', async ({ request }) => {
    const created = await createTaxRate(request, identifier, {
      is_zip: true,
      zip_code: null,
      zip_from: '90000',
      zip_to: '99999',
    });

    try {
      expect(created.identifier).toBe(identifier);
      expect(created.isZip).toBe(true);
      expect(created.zipCode).toBeNull();
      expect(created.zipFrom).toBe('90000');
      expect(created.zipTo).toBe('99999');
    } finally {
      await deleteTaxRate(request, created.id);
    }
  });

  test('create with is zip true missing zip from returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATES, {
      method: 'POST',
      data: {
        identifier,
        country: 'US',
        state: 'CA',
        is_zip: true,
        tax_rate: 8.5,
      },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe(ZIP_RANGE_REQUIRED);
    expect(await identifierMatches(request, identifier)).toBe(0);
  });

  test('create with is zip false missing zip code returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATES, {
      method: 'POST',
      data: {
        identifier,
        country: 'US',
        state: 'CA',
        is_zip: false,
        tax_rate: 8.5,
      },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe(ZIP_CODE_REQUIRED);
    expect(await identifierMatches(request, identifier)).toBe(0);
  });

  test('create missing required fields returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATES, {
      method: 'POST',
      data: {},
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The identifier field is required.');
  });

  test('update tax rate partial', async ({ request }) => {
    const created = await createTaxRate(request, identifier);

    try {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATE(created.id), {
        method: 'PUT',
        data: { tax_rate: 9.5 },
      });
      expect(resp.status()).toBe(200);
      const body = await resp.json();
      expect(body.id).toBe(created.id);
      expect(body.taxRate).toBe(9.5);
      expect(body.identifier).toBe(identifier);
      expect(body.zipCode).toBe('94105');

      const detail = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATE(created.id));
      expect((await detail.json()).taxRate).toBe(9.5);
    } finally {
      await deleteTaxRate(request, created.id);
    }
  });

  test('delete fresh tax rate returns 200', async ({ request }) => {
    const created = await createTaxRate(request, identifier);

    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATE(created.id), {
      method: 'DELETE',
    });
    expect(resp.status()).toBe(200);
    expect((await resp.json()).message).toBe(DELETED);

    const detail = await sendAdminRequest(request, ADMIN_SETTINGS.TAX_RATE(created.id));
    expect(detail.status()).toBe(404);
  });
});
