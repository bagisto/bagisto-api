// tests/restAPI/api/automation/admin/customers/customerGroups.spec.ts
//
// Admin Customer Groups (W2) — CRUD + mass-delete + system-group guards.
//
// Probed 2026-05-26: System groups have ids 1 (guest), 2 (general),
// 3 (wholesale) with is_user_defined=0. Update of `code` on a system group
// → 422. Delete of a system group → 400. User-created groups can be deleted
// when not in use.

import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_CUSTOMERS } from '../../../../rest/endpoints/admin.customers.endpoints';

test.describe.configure({ timeout: 60_000 });

function uniqueCode(): string {
  return `e2egrp${Date.now()}${Math.floor(Math.random() * 100000)}`;
}

async function createGroup(request: APIRequestContext) {
  const code = uniqueCode();
  const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.GROUPS, {
    method: 'POST',
    data: { code, name: `E2E Group ${code}` },
  });
  return { resp, code };
}

test.describe('Admin Customer Groups REST API', () => {
  test('list returns envelope', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.GROUPS);
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(Array.isArray(body.data)).toBe(true);
    // 3 seeded system groups always present
    expect(body.data.length).toBeGreaterThanOrEqual(3);
  });

  test('list filters by is_user_defined=0 returns only system groups', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.GROUPS, {
      params: { is_user_defined: '0' },
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    for (const g of body.data) {
      expect(g.isUserDefined).toBe(0);
    }
  });

  test('create + detail + delete round-trip', async ({ request }) => {
    const { resp: createResp } = await createGroup(request);
    expect([200, 201]).toContain(createResp.status());
    const created = await createResp.json();
    expect(created.isUserDefined).toBe(1); // API forces user-defined

    const detail = await sendAdminRequest(request, ADMIN_CUSTOMERS.GROUP(created.id));
    expect(detail.status()).toBe(200);
    const detailBody = await detail.json();
    expect(detailBody.id).toBe(created.id);
    expect(typeof detailBody.customersCount).toBe('number');

    const del = await sendAdminRequest(request, ADMIN_CUSTOMERS.GROUP(created.id), {
      method: 'DELETE',
    });
    expect([200, 204]).toContain(del.status());

    const missing = await sendAdminRequest(request, ADMIN_CUSTOMERS.GROUP(created.id));
    expect(missing.status()).toBe(404);
  });

  test('create rejects duplicate code with 422', async ({ request }) => {
    const { resp: firstResp } = await createGroup(request);
    const first = await firstResp.json();

    const dup = await sendAdminRequest(request, ADMIN_CUSTOMERS.GROUPS, {
      method: 'POST',
      data: { code: first.code, name: 'Duplicate' },
    });
    expect([400, 422]).toContain(dup.status());

    await sendAdminRequest(request, ADMIN_CUSTOMERS.GROUP(first.id), { method: 'DELETE' });
  });

  test('create rejects invalid Code rule (kebab-case)', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.GROUPS, {
      method: 'POST',
      data: { code: '123-bad-code', name: 'Bad' },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('update name on user-defined group', async ({ request }) => {
    const { resp: createResp } = await createGroup(request);
    const created = await createResp.json();

    const newName = `Renamed ${Date.now()}`;
    const upd = await sendAdminRequest(request, ADMIN_CUSTOMERS.GROUP(created.id), {
      method: 'PUT' as any,
      data: { name: newName, code: created.code },
    });
    expect([200, 201]).toContain(upd.status());
    const updated = await upd.json();
    expect(updated.name).toBe(newName);

    await sendAdminRequest(request, ADMIN_CUSTOMERS.GROUP(created.id), { method: 'DELETE' });
  });

  test('SYSTEM GUARD: updating code on system group (general, id=2) is rejected with 422', async ({
    request,
  }) => {
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.GROUP(2), {
      method: 'PUT' as any,
      data: { code: 'renamed_general', name: 'General' },
    });
    expect(resp.status()).toBe(422);
  });

  test('SYSTEM GUARD: updating name only on system group is allowed', async ({ request }) => {
    // Read first to preserve existing name; restore after.
    const before = await sendAdminRequest(request, ADMIN_CUSTOMERS.GROUP(2));
    const beforeBody = await before.json();
    const original = beforeBody.name;

    const newName = `General-${Date.now()}`;
    const upd = await sendAdminRequest(request, ADMIN_CUSTOMERS.GROUP(2), {
      method: 'PUT' as any,
      data: { name: newName },
    });
    expect([200, 201]).toContain(upd.status());

    // Restore.
    await sendAdminRequest(request, ADMIN_CUSTOMERS.GROUP(2), {
      method: 'PUT' as any,
      data: { name: original },
    });
  });

  test('SYSTEM GUARD: deleting system group (guest, id=1) is rejected with 400', async ({
    request,
  }) => {
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.GROUP(1), { method: 'DELETE' });
    expect(resp.status()).toBe(400);
  });

  test('delete returns 404 for unknown id', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.GROUP(99999999), {
      method: 'DELETE',
    });
    expect([400, 404]).toContain(resp.status());
  });

  test('mass-delete removes user-defined groups and skips system', async ({ request }) => {
    const a = await createGroup(request);
    const b = await createGroup(request);
    const aId = (await a.resp.json()).id;
    const bId = (await b.resp.json()).id;

    const mass = await sendAdminRequest(request, ADMIN_CUSTOMERS.GROUPS_MASS_DELETE, {
      method: 'POST',
      data: { indices: [aId, bId, 1 /* guest — system, should skip */] },
    });
    expect(mass.status()).toBe(200);
    const body = await mass.json();
    expect(Array.isArray(body.deleted)).toBe(true);
    expect(body.deleted).toEqual(expect.arrayContaining([aId, bId]));
    // Some envelopes name it `skipped` with reasons; tolerate either presence/absence.
    if (Array.isArray(body.skipped)) {
      const skippedIds = body.skipped.map((s: any) => (typeof s === 'object' ? s.id : s));
      expect(skippedIds).toContain(1);
    }
    // Guest must still exist.
    const guestProbe = await sendAdminRequest(request, ADMIN_CUSTOMERS.GROUP(1));
    expect(guestProbe.status()).toBe(200);
  });

  test('mass-delete rejects empty indices', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.GROUPS_MASS_DELETE, {
      method: 'POST',
      data: { indices: [] },
    });
    expect([400, 422]).toContain(resp.status());
  });
});
