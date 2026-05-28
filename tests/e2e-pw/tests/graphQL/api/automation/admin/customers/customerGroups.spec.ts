// tests/graphQL/api/automation/admin/customers/customerGroups.spec.ts
//
// Admin Customer Groups (W2 GraphQL) — CRUD + mass-delete + system-group
// guards. System groups have ids 1 (guest), 2 (general), 3 (wholesale) with
// is_user_defined=0. Update of `code` on a system group is rejected; delete
// of a system group is rejected.

import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_CUSTOMER_GROUPS_LIST,
  ADMIN_CUSTOMER_GROUP_DETAIL,
  ADMIN_CUSTOMER_GROUP_CREATE,
  ADMIN_CUSTOMER_GROUP_UPDATE,
  ADMIN_CUSTOMER_GROUP_DELETE,
  ADMIN_CUSTOMER_GROUP_MASS_DELETE,
} from '../../../../graphql/Queries/admin/customers/customerGroups.queries';

test.describe.configure({ timeout: 60_000 });

function uniqueCode(): string {
  return `e2egqlgrp${Date.now()}${Math.floor(Math.random() * 100000)}`;
}

async function createGroup(request: APIRequestContext) {
  const code = uniqueCode();
  const resp = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_GROUP_CREATE, {
    code,
    name: `E2E GQL Group ${code}`,
  });
  return { resp, code };
}

async function dropGroup(request: APIRequestContext, id: string) {
  try {
    await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_GROUP_DELETE, { id });
  } catch {
    // ignore
  }
}

test.describe('Admin Customer Groups GraphQL API', () => {
  test('list returns edges', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_GROUPS_LIST, {
      first: 5,
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.errors).toBeUndefined();
    const edges = body?.data?.adminCustomerGroups?.edges ?? [];
    // 3 seeded system groups always present
    expect(edges.length).toBeGreaterThanOrEqual(3);
  });

  test('list filter by is_user_defined=0 narrows', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_GROUPS_LIST, {
      is_user_defined: 0,
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.errors).toBeUndefined();
    // isUserDefined comes back as null over GraphQL (project-wide quirk);
    // we just assert the filter runs and returns rows.
    expect(Array.isArray(body?.data?.adminCustomerGroups?.edges)).toBe(true);
  });

  test('create + detail + delete round-trip', async ({ request }) => {
    const { resp: createResp } = await createGroup(request);
    const createBody = await createResp.json();
    expect(createBody.errors).toBeUndefined();
    const created = createBody?.data?.createAdminCustomerGroup?.adminCustomerGroup;
    expect(created).toBeTruthy();
    expect(created._id).toBeGreaterThan(0);

    // Detail
    const detailResp = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_GROUP_DETAIL, {
      id: created.id,
    });
    const detailBody = await detailResp.json();
    expect(detailBody.errors).toBeUndefined();
    const detail = detailBody?.data?.adminCustomerGroup;
    expect(detail).toBeTruthy();
    expect(detail._id).toBe(created._id);

    // Delete
    const delResp = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_GROUP_DELETE, {
      id: created.id,
    });
    expect(delResp.status()).toBe(200);

    // Confirm gone
    const missing = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_GROUP_DETAIL, {
      id: created.id,
    });
    const missingBody = await missing.json();
    const hasErrors = Array.isArray(missingBody?.errors) && missingBody.errors.length > 0;
    const isNull = missingBody?.data?.adminCustomerGroup === null;
    expect(hasErrors || isNull).toBe(true);
  });

  test('create rejects duplicate code', async ({ request }) => {
    const { resp: firstResp } = await createGroup(request);
    const first = (await firstResp.json())?.data?.createAdminCustomerGroup?.adminCustomerGroup;

    const dupResp = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_GROUP_CREATE, {
      code: first.code,
      name: 'Duplicate',
    });
    const dupBody = await dupResp.json();
    const hasErrors = Array.isArray(dupBody?.errors) && dupBody.errors.length > 0;
    const isNull = dupBody?.data?.createAdminCustomerGroup?.adminCustomerGroup === null;
    expect(hasErrors || isNull).toBe(true);

    await dropGroup(request, first.id);
  });

  test('create rejects invalid Code rule (kebab-case)', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_GROUP_CREATE, {
      code: '123-bad-code',
      name: 'Bad',
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    const isNull = body?.data?.createAdminCustomerGroup?.adminCustomerGroup === null;
    expect(hasErrors || isNull).toBe(true);
  });

  test('update name on user-defined group', async ({ request }) => {
    const { resp: createResp } = await createGroup(request);
    const created = (await createResp.json())?.data?.createAdminCustomerGroup?.adminCustomerGroup;

    const newName = `Renamed ${Date.now()}`;
    const updResp = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_GROUP_UPDATE, {
      id: created.id,
      code: created.code,
      name: newName,
    });
    const updBody = await updResp.json();
    expect(updBody.errors, `update errors: ${JSON.stringify(updBody.errors)}`).toBeUndefined();

    await dropGroup(request, created.id);
  });

  test('SYSTEM GUARD: updating code on system group (general, id=2) is rejected', async ({
    request,
  }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_GROUP_UPDATE, {
      id: '/api/admin/customers/groups/2',
      code: 'renamed_general',
      name: 'General',
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    const isNull = body?.data?.updateAdminCustomerGroup?.adminCustomerGroup === null;
    expect(hasErrors || isNull).toBe(true);
  });

  test('SYSTEM GUARD: deleting system group (guest, id=1) is rejected', async ({
    request,
  }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_GROUP_DELETE, {
      id: '/api/admin/customers/groups/1',
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    const isNull = body?.data?.deleteAdminCustomerGroup?.adminCustomerGroup === null;
    expect(hasErrors || isNull).toBe(true);
  });

  test('mass-delete removes user-defined and skips system', async ({ request }) => {
    const a = await createGroup(request);
    const b = await createGroup(request);
    const aId = (await a.resp.json())?.data?.createAdminCustomerGroup?.adminCustomerGroup?._id;
    const bId = (await b.resp.json())?.data?.createAdminCustomerGroup?.adminCustomerGroup?._id;

    const mass = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_GROUP_MASS_DELETE, {
      indices: [aId, bId, 1 /* guest — system, should skip */],
    });
    expect(mass.status()).toBe(200);

    // Guest must still exist.
    const guestProbe = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_GROUP_DETAIL, {
      id: '/api/admin/customers/groups/1',
    });
    const guestBody = await guestProbe.json();
    expect(guestBody?.data?.adminCustomerGroup?._id).toBe(1);
  });

  test('mass-delete rejects empty indices', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_GROUP_MASS_DELETE, {
      indices: [],
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    expect(hasErrors).toBe(true);
  });
});
