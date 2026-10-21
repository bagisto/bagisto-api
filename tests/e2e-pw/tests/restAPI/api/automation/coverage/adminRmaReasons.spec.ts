import { test, expect, APIRequestContext, APIResponse } from '@playwright/test';
import { sendAdminRequest } from '../../../rest/helpers/adminClient';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import { ADMIN_RMA } from '../../../rest/endpoints/coverage/adminRma.endpoints';

test.describe.configure({ timeout: 60_000 });

const UNKNOWN_ID = 999999999;

const NOT_FOUND = 'Return reason not found.';

async function safeJson(resp: APIResponse): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

function errorMessage(body: any): string | undefined {
  return body?.detail ?? body?.message;
}

function rowsOf(body: any): any[] {
  if (Array.isArray(body)) return body;
  return Array.isArray(body?.data) ? body.data : [];
}

function uniqueTitle(): string {
  return `e2e rma reason ${Date.now()}-${Math.floor(Math.random() * 1_000_000)}`;
}

async function createReason(request: APIRequestContext, data: Record<string, any>): Promise<any> {
  const resp = await sendAdminRequest(request, ADMIN_RMA.REASONS, { method: 'POST', data });
  expect(resp.status(), await resp.text()).toBe(201);
  return safeJson(resp);
}

async function resolutionReasonIds(request: APIRequestContext, resolutionType: string): Promise<number[]> {
  const resp = await sendAdminRequest(request, ADMIN_RMA.REQUEST_RESOLUTION_REASONS, {
    params: { resolution_type: resolutionType },
  });
  expect(resp.status()).toBe(200);
  return rowsOf(await safeJson(resp)).map((row: any) => row.id);
}

test.describe('admin rma reasons rest api', () => {
  let created: number[];

  test.beforeEach(() => {
    created = [];
  });

  test.afterEach(async ({ request }) => {
    for (const id of created) {
      await sendAdminRequest(request, ADMIN_RMA.REASON(id), { method: 'DELETE' });
    }
  });

  test('should refuse the reason listing without an admin token', async ({ request }) => {
    const resp = await sendRestRequest(request, ADMIN_RMA.REASONS);
    expect(resp.status()).toBe(401);
    expect((await safeJson(resp))?.message).toBe('Unauthenticated.');
  });

  test('should refuse to create a reason without an admin token', async ({ request }) => {
    const resp = await sendRestRequest(request, ADMIN_RMA.REASONS, {
      method: 'POST',
      data: { title: uniqueTitle(), status: 1, position: 1, resolution_type: ['return'] },
    });
    expect(resp.status()).toBe(401);
    expect((await safeJson(resp))?.message).toBe('Unauthenticated.');
  });

  test('should create a reason with its resolution types', async ({ request }) => {
    const title = uniqueTitle();
    const body = await createReason(request, {
      title,
      status: 0,
      position: 7,
      resolution_type: ['return', 'cancel_items'],
    });
    created.push(body.id);

    expect(typeof body.id).toBe('number');
    expect(body).toEqual(expect.objectContaining({ title, status: 0, position: 7, isAdmin: 0 }));
    expect([...body.resolutionType].sort()).toEqual(['cancel_items', 'return']);
    expect(typeof body.createdAt).toBe('string');
  });

  test('should reject a reason without a resolution type', async ({ request }) => {
    const title = uniqueTitle();
    const resp = await sendAdminRequest(request, ADMIN_RMA.REASONS, {
      method: 'POST',
      data: { title, status: 1, position: 1 },
    });
    expect(resp.status()).toBe(422);
    expect(errorMessage(await safeJson(resp))).toBe('The resolution type field is required.');

    const list = await sendAdminRequest(request, ADMIN_RMA.REASONS, { params: { title } });
    expect((await safeJson(list)).data).toEqual([]);
  });

  test('should reject a reason without a title', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_RMA.REASONS, {
      method: 'POST',
      data: { status: 1, position: 1, resolution_type: ['return'] },
    });
    expect(resp.status()).toBe(422);
    expect(errorMessage(await safeJson(resp))).toBe('The title field is required.');
  });

  test('should list only the reason matching the title filter in a data and meta envelope', async ({ request }) => {
    const title = uniqueTitle();
    const reason = await createReason(request, { title, status: 0, position: 3, resolution_type: ['return'] });
    created.push(reason.id);

    const resp = await sendAdminRequest(request, ADMIN_RMA.REASONS, { params: { title } });
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);

    expect(body.meta).toEqual(expect.objectContaining({ total: 1, currentPage: 1 }));
    expect(body.data).toHaveLength(1);
    expect(body.data[0]).toEqual(expect.objectContaining({ id: reason.id, title, status: 0, position: 3 }));
  });

  test('should return a single reason with its resolution types', async ({ request }) => {
    const title = uniqueTitle();
    const reason = await createReason(request, { title, status: 1, position: 2, resolution_type: ['cancel_items'] });
    created.push(reason.id);

    const resp = await sendAdminRequest(request, ADMIN_RMA.REASON(reason.id));
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);
    expect(body).toEqual(expect.objectContaining({ id: reason.id, title, status: 1, position: 2 }));
    expect(body.resolutionType).toEqual(['cancel_items']);
  });

  test('should return 404 for a reason that does not exist', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_RMA.REASON(UNKNOWN_ID));
    expect(resp.status()).toBe(404);
    expect(errorMessage(await safeJson(resp))).toBe(NOT_FOUND);
  });

  test('should update a reason and replace its resolution types', async ({ request }) => {
    const reason = await createReason(request, {
      title: uniqueTitle(),
      status: 1,
      position: 1,
      resolution_type: ['return', 'cancel_items'],
    });
    created.push(reason.id);
    const title = uniqueTitle();

    const resp = await sendAdminRequest(request, ADMIN_RMA.REASON(reason.id), {
      method: 'PUT',
      data: { title, status: 0, position: 9, resolution_type: ['return'] },
    });
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);
    expect(body).toEqual(expect.objectContaining({ id: reason.id, title, status: 0, position: 9 }));
    expect(body.resolutionType).toEqual(['return']);

    const detail = await safeJson(await sendAdminRequest(request, ADMIN_RMA.REASON(reason.id)));
    expect(detail.resolutionType).toEqual(['return']);
    expect(detail.title).toBe(title);
  });

  test('should return 404 when updating a reason that does not exist', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_RMA.REASON(UNKNOWN_ID), {
      method: 'PUT',
      data: { title: uniqueTitle(), status: 1, position: 1, resolution_type: ['return'] },
    });
    expect(resp.status()).toBe(404);
    expect(errorMessage(await safeJson(resp))).toBe(NOT_FOUND);
  });

  test('should delete a reason the test created', async ({ request }) => {
    const reason = await createReason(request, { title: uniqueTitle(), status: 0, position: 1, resolution_type: ['return'] });

    const resp = await sendAdminRequest(request, ADMIN_RMA.REASON(reason.id), { method: 'DELETE' });
    expect(resp.status()).toBe(204);

    expect((await sendAdminRequest(request, ADMIN_RMA.REASON(reason.id))).status()).toBe(404);
  });

  test('should return 404 when deleting a reason that does not exist', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_RMA.REASON(UNKNOWN_ID), { method: 'DELETE' });
    expect(resp.status()).toBe(404);
    expect(errorMessage(await safeJson(resp))).toBe(NOT_FOUND);
  });

  test('should mass update the active flag of the reasons that exist', async ({ request }) => {
    const first = await createReason(request, { title: uniqueTitle(), status: 0, position: 1, resolution_type: ['return'] });
    const second = await createReason(request, { title: uniqueTitle(), status: 0, position: 1, resolution_type: ['return'] });
    created.push(first.id, second.id);

    const resp = await sendAdminRequest(request, ADMIN_RMA.REASONS_MASS_UPDATE_STATUS, {
      method: 'POST',
      data: { indices: [first.id, UNKNOWN_ID, second.id], value: 1 },
    });
    expect(resp.status()).toBe(201);
    const body = await safeJson(resp);
    expect(body.updated).toEqual([first.id, second.id]);
    expect(body.message).toBe('Selected items updated.');

    for (const id of [first.id, second.id]) {
      expect((await safeJson(await sendAdminRequest(request, ADMIN_RMA.REASON(id)))).status).toBe(1);
    }
  });

  test('should reject a mass reason update with a value other than zero or one', async ({ request }) => {
    const reason = await createReason(request, { title: uniqueTitle(), status: 0, position: 1, resolution_type: ['return'] });
    created.push(reason.id);

    const resp = await sendAdminRequest(request, ADMIN_RMA.REASONS_MASS_UPDATE_STATUS, {
      method: 'POST',
      data: { indices: [reason.id], value: 3 },
    });
    expect(resp.status()).toBe(422);
    expect(errorMessage(await safeJson(resp))).toBe('Status value must be 0 or 1.');
    expect((await safeJson(await sendAdminRequest(request, ADMIN_RMA.REASON(reason.id)))).status).toBe(0);
  });

  test('should mass delete the reasons the test created', async ({ request }) => {
    const first = await createReason(request, { title: uniqueTitle(), status: 0, position: 1, resolution_type: ['return'] });
    const second = await createReason(request, { title: uniqueTitle(), status: 0, position: 1, resolution_type: ['cancel_items'] });

    const resp = await sendAdminRequest(request, ADMIN_RMA.REASONS_MASS_DELETE, {
      method: 'POST',
      data: { indices: [first.id, second.id, UNKNOWN_ID] },
    });
    expect(resp.status()).toBe(201);
    const body = await safeJson(resp);
    expect(body.deleted).toEqual([first.id, second.id]);
    expect(body.message).toBe('Selected items deleted.');

    expect((await sendAdminRequest(request, ADMIN_RMA.REASON(first.id))).status()).toBe(404);
    expect((await sendAdminRequest(request, ADMIN_RMA.REASON(second.id))).status()).toBe(404);
  });

  test('should reject a mass reason delete without ids', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_RMA.REASONS_MASS_DELETE, { method: 'POST', data: { indices: [] } });
    expect(resp.status()).toBe(422);
    expect(errorMessage(await safeJson(resp))).toBe('At least one id is required.');
  });

  test('should offer an active reason only for the resolution types it carries', async ({ request }) => {
    const active = await createReason(request, { title: uniqueTitle(), status: 1, position: 1, resolution_type: ['return'] });
    const inactive = await createReason(request, { title: uniqueTitle(), status: 0, position: 1, resolution_type: ['return'] });
    created.push(active.id, inactive.id);

    const resp = await sendAdminRequest(request, ADMIN_RMA.REQUEST_RESOLUTION_REASONS, {
      params: { resolution_type: 'return' },
    });
    expect(resp.status()).toBe(200);
    const rows = rowsOf(await safeJson(resp));
    expect(rows.find((row: any) => row.id === active.id)).toEqual({ id: active.id, title: active.title, position: 1 });
    expect(rows.map((row: any) => row.id)).not.toContain(inactive.id);

    expect(await resolutionReasonIds(request, 'cancel_items')).not.toContain(active.id);
  });

  test('should reject a resolution reason lookup for an unknown resolution type', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_RMA.REQUEST_RESOLUTION_REASONS, {
      params: { resolution_type: 'refund' },
    });
    expect(resp.status()).toBe(422);
    expect(errorMessage(await safeJson(resp))).toBe('Resolution type must be "return" or "cancel_items".');
  });

  test('should refuse the resolution reason lookup without an admin token', async ({ request }) => {
    const resp = await sendRestRequest(request, ADMIN_RMA.REQUEST_RESOLUTION_REASONS, {
      params: { resolution_type: 'return' },
    });
    expect(resp.status()).toBe(401);
    expect((await safeJson(resp))?.message).toBe('Unauthenticated.');
  });
});
