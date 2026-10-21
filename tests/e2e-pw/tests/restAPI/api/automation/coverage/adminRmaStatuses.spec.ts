import { test, expect, APIRequestContext, APIResponse } from '@playwright/test';
import { sendAdminRequest } from '../../../rest/helpers/adminClient';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import { ADMIN_RMA } from '../../../rest/endpoints/coverage/adminRma.endpoints';

test.describe.configure({ timeout: 60_000 });

const UNKNOWN_ID = 999999999;

const DEFAULT_STATUS_ID = 1;

const NOT_FOUND = 'Return status not found.';

async function safeJson(resp: APIResponse): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

function errorMessage(body: any): string | undefined {
  return body?.detail ?? body?.message;
}

function uniqueTitle(): string {
  return `e2e rma status ${Date.now()}-${Math.floor(Math.random() * 1_000_000)}`;
}

async function createStatus(request: APIRequestContext, data: Record<string, any>): Promise<any> {
  const resp = await sendAdminRequest(request, ADMIN_RMA.STATUSES, { method: 'POST', data });
  expect(resp.status(), await resp.text()).toBe(201);
  return safeJson(resp);
}

test.describe('admin rma statuses rest api', () => {
  let created: number[];

  test.beforeEach(() => {
    created = [];
  });

  test.afterEach(async ({ request }) => {
    for (const id of created) {
      await sendAdminRequest(request, ADMIN_RMA.STATUS(id), { method: 'DELETE' });
    }
  });

  test('should refuse the status listing without an admin token', async ({ request }) => {
    const resp = await sendRestRequest(request, ADMIN_RMA.STATUSES);
    expect(resp.status()).toBe(401);
    expect((await safeJson(resp))?.message).toBe('Unauthenticated.');
  });

  test('should refuse to create a status without an admin token', async ({ request }) => {
    const resp = await sendRestRequest(request, ADMIN_RMA.STATUSES, {
      method: 'POST',
      data: { title: uniqueTitle(), status: 1 },
    });
    expect(resp.status()).toBe(401);
    expect((await safeJson(resp))?.message).toBe('Unauthenticated.');
  });

  test('should create a status and return it with default off', async ({ request }) => {
    const title = uniqueTitle();
    const body = await createStatus(request, { title, status: 1, color: '#FDB022' });
    created.push(body.id);

    expect(typeof body.id).toBe('number');
    expect(body).toEqual(expect.objectContaining({
      title,
      status: 1,
      color: '#FDB022',
      default: 0,
    }));
    expect(typeof body.createdAt).toBe('string');
    expect(typeof body.updatedAt).toBe('string');
  });

  test('should reject a status without a title', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_RMA.STATUSES, { method: 'POST', data: { status: 1 } });
    expect(resp.status()).toBe(422);
    expect(errorMessage(await safeJson(resp))).toBe('The title field is required.');
  });

  test('should reject a status whose title is already taken', async ({ request }) => {
    const title = uniqueTitle();
    created.push((await createStatus(request, { title, status: 0 })).id);

    const resp = await sendAdminRequest(request, ADMIN_RMA.STATUSES, { method: 'POST', data: { title, status: 0 } });
    expect(resp.status()).toBe(422);
    expect(errorMessage(await safeJson(resp))).toBe('The title has already been taken.');

    const list = await sendAdminRequest(request, ADMIN_RMA.STATUSES, { params: { title } });
    expect((await safeJson(list)).data).toHaveLength(1);
  });

  test('should list only the status matching the title filter in a data and meta envelope', async ({ request }) => {
    const title = uniqueTitle();
    const status = await createStatus(request, { title, status: 0, color: '#12B76A' });
    created.push(status.id);

    const resp = await sendAdminRequest(request, ADMIN_RMA.STATUSES, { params: { title } });
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);

    expect(body.meta).toEqual(expect.objectContaining({ total: 1, currentPage: 1 }));
    expect(body.data).toHaveLength(1);
    expect(body.data[0]).toEqual(expect.objectContaining({
      id: status.id,
      title,
      status: 0,
      color: '#12B76A',
      default: 0,
    }));
  });

  test('should narrow the status listing by the active flag', async ({ request }) => {
    const title = uniqueTitle();
    created.push((await createStatus(request, { title, status: 0 })).id);

    const active = await sendAdminRequest(request, ADMIN_RMA.STATUSES, { params: { title, status: '1' } });
    expect(active.status()).toBe(200);
    expect((await safeJson(active)).data).toEqual([]);

    const inactive = await sendAdminRequest(request, ADMIN_RMA.STATUSES, { params: { title, status: '0' } });
    expect((await safeJson(inactive)).data.map((row: any) => row.title)).toEqual([title]);
  });

  test('should return a single status by id', async ({ request }) => {
    const title = uniqueTitle();
    const status = await createStatus(request, { title, status: 1, color: '#000000' });
    created.push(status.id);

    const resp = await sendAdminRequest(request, ADMIN_RMA.STATUS(status.id));
    expect(resp.status()).toBe(200);
    expect(await safeJson(resp)).toEqual(expect.objectContaining({
      id: status.id,
      title,
      status: 1,
      color: '#000000',
      default: 0,
    }));
  });

  test('should return 404 for a status that does not exist', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_RMA.STATUS(UNKNOWN_ID));
    expect(resp.status()).toBe(404);
    expect(errorMessage(await safeJson(resp))).toBe(NOT_FOUND);
  });

  test('should update the title, active flag and colour of a status', async ({ request }) => {
    const status = await createStatus(request, { title: uniqueTitle(), status: 1, color: '#FDB022' });
    created.push(status.id);
    const title = uniqueTitle();

    const resp = await sendAdminRequest(request, ADMIN_RMA.STATUS(status.id), {
      method: 'PUT',
      data: { title, status: 0, color: '#12B76A' },
    });
    expect(resp.status()).toBe(200);
    expect(await safeJson(resp)).toEqual(expect.objectContaining({ id: status.id, title, status: 0, color: '#12B76A' }));

    const detail = await safeJson(await sendAdminRequest(request, ADMIN_RMA.STATUS(status.id)));
    expect(detail).toEqual(expect.objectContaining({ title, status: 0, color: '#12B76A' }));
  });

  test('should return 404 when updating a status that does not exist', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_RMA.STATUS(UNKNOWN_ID), {
      method: 'PUT',
      data: { title: uniqueTitle(), status: 1 },
    });
    expect(resp.status()).toBe(404);
    expect(errorMessage(await safeJson(resp))).toBe(NOT_FOUND);
  });

  test('should reject an update that takes the title of another status', async ({ request }) => {
    const first = await createStatus(request, { title: uniqueTitle(), status: 0 });
    const second = await createStatus(request, { title: uniqueTitle(), status: 0 });
    created.push(first.id, second.id);

    const resp = await sendAdminRequest(request, ADMIN_RMA.STATUS(second.id), {
      method: 'PUT',
      data: { title: first.title, status: 0 },
    });
    expect(resp.status()).toBe(422);
    expect(errorMessage(await safeJson(resp))).toBe('The title has already been taken.');

    const detail = await safeJson(await sendAdminRequest(request, ADMIN_RMA.STATUS(second.id)));
    expect(detail.title).toBe(second.title);
  });

  test('should delete a status the test created', async ({ request }) => {
    const status = await createStatus(request, { title: uniqueTitle(), status: 0 });

    const resp = await sendAdminRequest(request, ADMIN_RMA.STATUS(status.id), { method: 'DELETE' });
    expect(resp.status()).toBe(204);

    const detail = await sendAdminRequest(request, ADMIN_RMA.STATUS(status.id));
    expect(detail.status()).toBe(404);
  });

  test('should return 404 when deleting a status that does not exist', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_RMA.STATUS(UNKNOWN_ID), { method: 'DELETE' });
    expect(resp.status()).toBe(404);
    expect(errorMessage(await safeJson(resp))).toBe(NOT_FOUND);
  });

  test('should refuse to delete a default status and keep it', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_RMA.STATUS(DEFAULT_STATUS_ID), { method: 'DELETE' });
    expect(resp.status()).toBe(422);
    expect(errorMessage(await safeJson(resp))).toBe('A default return status cannot be deleted.');

    const detail = await sendAdminRequest(request, ADMIN_RMA.STATUS(DEFAULT_STATUS_ID));
    expect(detail.status()).toBe(200);
    expect((await safeJson(detail)).default).toBe(1);
  });

  test('should mass update the active flag of the statuses that exist', async ({ request }) => {
    const first = await createStatus(request, { title: uniqueTitle(), status: 1 });
    const second = await createStatus(request, { title: uniqueTitle(), status: 1 });
    created.push(first.id, second.id);

    const resp = await sendAdminRequest(request, ADMIN_RMA.STATUSES_MASS_UPDATE_STATUS, {
      method: 'POST',
      data: { indices: [first.id, second.id, UNKNOWN_ID], value: 0 },
    });
    expect(resp.status()).toBe(201);
    const body = await safeJson(resp);
    expect(body.updated).toEqual([first.id, second.id]);
    expect(body.message).toBe('Selected items updated.');

    for (const id of [first.id, second.id]) {
      expect((await safeJson(await sendAdminRequest(request, ADMIN_RMA.STATUS(id)))).status).toBe(0);
    }
  });

  test('should reject a mass status update with a value other than zero or one', async ({ request }) => {
    const status = await createStatus(request, { title: uniqueTitle(), status: 1 });
    created.push(status.id);

    const resp = await sendAdminRequest(request, ADMIN_RMA.STATUSES_MASS_UPDATE_STATUS, {
      method: 'POST',
      data: { indices: [status.id], value: 2 },
    });
    expect(resp.status()).toBe(422);
    expect(errorMessage(await safeJson(resp))).toBe('Status value must be 0 or 1.');

    expect((await safeJson(await sendAdminRequest(request, ADMIN_RMA.STATUS(status.id)))).status).toBe(1);
  });

  test('should reject a mass status update without ids', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_RMA.STATUSES_MASS_UPDATE_STATUS, {
      method: 'POST',
      data: { indices: [], value: 1 },
    });
    expect(resp.status()).toBe(422);
    expect(errorMessage(await safeJson(resp))).toBe('At least one id is required.');
  });

  test('should mass delete the created statuses and skip a default one', async ({ request }) => {
    const first = await createStatus(request, { title: uniqueTitle(), status: 0 });
    const second = await createStatus(request, { title: uniqueTitle(), status: 0 });

    const resp = await sendAdminRequest(request, ADMIN_RMA.STATUSES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [first.id, DEFAULT_STATUS_ID, second.id, UNKNOWN_ID] },
    });
    expect(resp.status()).toBe(201);
    const body = await safeJson(resp);
    expect(body.deleted).toEqual([first.id, second.id]);
    expect(body.message).toBe('Selected items deleted.');

    expect((await sendAdminRequest(request, ADMIN_RMA.STATUS(first.id))).status()).toBe(404);
    expect((await sendAdminRequest(request, ADMIN_RMA.STATUS(second.id))).status()).toBe(404);
    expect((await sendAdminRequest(request, ADMIN_RMA.STATUS(DEFAULT_STATUS_ID))).status()).toBe(200);
  });

  test('should reject a mass status delete without ids', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_RMA.STATUSES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [] },
    });
    expect(resp.status()).toBe(422);
    expect(errorMessage(await safeJson(resp))).toBe('At least one id is required.');
  });
});
