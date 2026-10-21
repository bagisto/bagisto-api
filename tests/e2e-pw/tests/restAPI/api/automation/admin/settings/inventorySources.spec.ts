import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_SETTINGS } from '../../../../rest/endpoints/admin.settings.endpoints';

test.describe.configure({ timeout: 60_000 });

const NOT_FOUND = 'Inventory source not found.';
const DELETED = 'Inventory source deleted successfully.';
const MASS_DELETED = 'Inventory sources deleted successfully.';
const INDICES_REQUIRED = 'The indices field is required and must be a non-empty array.';

interface SourceInput {
  code: string;
  name: string;
  contact_email: string;
}

function uniqueSuffix(): string {
  return `${Date.now().toString(36)}${Math.floor(Math.random() * 1_000_000).toString(36)}`;
}

function sourceInput(): SourceInput {
  const suffix = uniqueSuffix();

  return {
    code: `e2e-src-${suffix}`,
    name: `E2E Source ${suffix}`,
    contact_email: `e2e-src-${suffix}@example.com`,
  };
}

async function createSource(request: APIRequestContext, input: SourceInput): Promise<any> {
  const resp = await sendAdminRequest(request, ADMIN_SETTINGS.INVENTORY_SOURCES, {
    method: 'POST',
    data: {
      ...input,
      description: 'e2e generated',
      contact_name: 'E2E Tester',
      contact_number: '1234567890',
      country: 'US',
      state: 'CA',
      city: 'San Francisco',
      street: '1 Market St',
      postcode: '94105',
      priority: 0,
      status: 1,
    },
  });
  const body = await resp.json();
  expect(resp.status(), JSON.stringify(body)).toBe(201);

  return body;
}

async function deleteSource(request: APIRequestContext, id: number): Promise<void> {
  await sendAdminRequest(request, ADMIN_SETTINGS.INVENTORY_SOURCE(id), { method: 'DELETE' });
}

test.describe('admin settings inventory sources rest api', () => {
  let input: SourceInput;

  test.beforeEach(() => {
    input = sourceInput();
  });

  test('listing returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.INVENTORY_SOURCES);
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(Array.isArray(body.data)).toBe(true);
    expect(typeof body.meta.total).toBe('number');
  });

  test('listing with code filter', async ({ request }) => {
    const created = await createSource(request, input);

    try {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.INVENTORY_SOURCES, {
        params: { code: input.code },
      });
      expect(resp.status()).toBe(200);
      const body = await resp.json();
      expect(body.data).toHaveLength(1);
      expect(body.data[0].id).toBe(created.id);
      expect(body.data[0].code).toBe(input.code);
    } finally {
      await deleteSource(request, created.id);
    }
  });

  test('detail of a created source returns 200', async ({ request }) => {
    const created = await createSource(request, input);

    try {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.INVENTORY_SOURCE(created.id));
      expect(resp.status()).toBe(200);
      const body = await resp.json();
      expect(body.id).toBe(created.id);
      expect(body.code).toBe(input.code);
      expect(body.name).toBe(input.name);
      expect(body.contactEmail).toBe(input.contact_email);
      expect(body.status).toBe(1);
    } finally {
      await deleteSource(request, created.id);
    }
  });

  test('detail non-existent id returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.INVENTORY_SOURCE(99999999));
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe(NOT_FOUND);
  });

  test('create inventory source happy path', async ({ request }) => {
    const created = await createSource(request, input);

    try {
      expect(typeof created.id).toBe('number');
      expect(created.code).toBe(input.code);
      expect(created.name).toBe(input.name);
      expect(created.contactName).toBe('E2E Tester');
      expect(created.country).toBe('US');
      expect(created.postcode).toBe('94105');
      expect(created.status).toBe(1);

      const detail = await sendAdminRequest(request, ADMIN_SETTINGS.INVENTORY_SOURCE(created.id));
      expect(detail.status()).toBe(200);
      expect((await detail.json()).code).toBe(input.code);
    } finally {
      await deleteSource(request, created.id);
    }
  });

  test('create source missing required fields returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.INVENTORY_SOURCES, {
      method: 'POST',
      data: { code: input.code },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The name field is required.');

    const list = await sendAdminRequest(request, ADMIN_SETTINGS.INVENTORY_SOURCES, {
      params: { code: input.code },
    });
    expect((await list.json()).data).toHaveLength(0);
  });

  test('update source partial name only', async ({ request }) => {
    const created = await createSource(request, input);
    const renamed = `Renamed ${input.name}`;

    try {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.INVENTORY_SOURCE(created.id), {
        method: 'PUT',
        data: { name: renamed },
      });
      expect(resp.status()).toBe(200);
      const body = await resp.json();
      expect(body.id).toBe(created.id);
      expect(body.name).toBe(renamed);
      expect(body.code).toBe(input.code);

      const detail = await sendAdminRequest(request, ADMIN_SETTINGS.INVENTORY_SOURCE(created.id));
      expect((await detail.json()).name).toBe(renamed);
    } finally {
      await deleteSource(request, created.id);
    }
  });

  test('delete fresh source returns 200', async ({ request }) => {
    const created = await createSource(request, input);

    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.INVENTORY_SOURCE(created.id), {
      method: 'DELETE',
    });
    expect(resp.status()).toBe(200);
    expect((await resp.json()).message).toBe(DELETED);

    const detail = await sendAdminRequest(request, ADMIN_SETTINGS.INVENTORY_SOURCE(created.id));
    expect(detail.status()).toBe(404);
  });

  test('mass-delete with single fresh id', async ({ request }) => {
    const created = await createSource(request, input);

    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.INVENTORY_SOURCES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [created.id] },
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.message).toBe(MASS_DELETED);
    expect(body.deleted).toEqual([created.id]);

    const detail = await sendAdminRequest(request, ADMIN_SETTINGS.INVENTORY_SOURCE(created.id));
    expect(detail.status()).toBe(404);
  });

  test('mass-delete empty indices returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.INVENTORY_SOURCES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [] },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe(INDICES_REQUIRED);
  });
});
