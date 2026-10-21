import { test, expect, APIRequestContext, APIResponse } from '@playwright/test';
import { sendAdminRequest } from '../../../rest/helpers/adminClient';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import { ADMIN_RMA } from '../../../rest/endpoints/coverage/adminRma.endpoints';

test.describe.configure({ timeout: 60_000 });

const UNKNOWN_ID = 999999999;

const NOT_FOUND = 'Return rule not found.';

async function safeJson(resp: APIResponse): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

function errorMessage(body: any): string | undefined {
  return body?.detail ?? body?.message;
}

function uniqueName(): string {
  return `e2e rma rule ${Date.now()}-${Math.floor(Math.random() * 1_000_000)}`;
}

async function createRule(request: APIRequestContext, data: Record<string, any>): Promise<any> {
  const resp = await sendAdminRequest(request, ADMIN_RMA.RULES, { method: 'POST', data });
  expect(resp.status(), await resp.text()).toBe(201);
  return safeJson(resp);
}

function ruleInput(overrides: Record<string, any> = {}): Record<string, any> {
  return {
    name: uniqueName(),
    description: 'Return window created by the e2e suite.',
    status: 0,
    return_period: 30,
    ...overrides,
  };
}

test.describe('admin rma rules rest api', () => {
  let created: number[];

  test.beforeEach(() => {
    created = [];
  });

  test.afterEach(async ({ request }) => {
    for (const id of created) {
      await sendAdminRequest(request, ADMIN_RMA.RULE(id), { method: 'DELETE' });
    }
  });

  test('should refuse the rule listing without an admin token', async ({ request }) => {
    const resp = await sendRestRequest(request, ADMIN_RMA.RULES);
    expect(resp.status()).toBe(401);
    expect((await safeJson(resp))?.message).toBe('Unauthenticated.');
  });

  test('should refuse to create a rule without an admin token', async ({ request }) => {
    const resp = await sendRestRequest(request, ADMIN_RMA.RULES, { method: 'POST', data: ruleInput() });
    expect(resp.status()).toBe(401);
    expect((await safeJson(resp))?.message).toBe('Unauthenticated.');
  });

  test('should create a rule with its return window', async ({ request }) => {
    const input = ruleInput({ return_period: 45 });
    const body = await createRule(request, input);
    created.push(body.id);

    expect(typeof body.id).toBe('number');
    expect(body).toEqual(expect.objectContaining({
      name: input.name,
      description: input.description,
      status: 0,
      returnPeriod: 45,
    }));
    expect(typeof body.createdAt).toBe('string');
  });

  test('should reject a rule without a description', async ({ request }) => {
    const input = ruleInput();
    delete input.description;

    const resp = await sendAdminRequest(request, ADMIN_RMA.RULES, { method: 'POST', data: input });
    expect(resp.status()).toBe(422);
    expect(errorMessage(await safeJson(resp))).toBe('The description field is required.');

    const list = await sendAdminRequest(request, ADMIN_RMA.RULES, { params: { name: input.name } });
    expect((await safeJson(list)).data).toEqual([]);
  });

  test('should reject a rule without a name', async ({ request }) => {
    const input = ruleInput();
    delete input.name;

    const resp = await sendAdminRequest(request, ADMIN_RMA.RULES, { method: 'POST', data: input });
    expect(resp.status()).toBe(422);
    expect(errorMessage(await safeJson(resp))).toBe('The name field is required.');
  });

  test('should list only the rule matching the name filter in a data and meta envelope', async ({ request }) => {
    const rule = await createRule(request, ruleInput({ return_period: 14 }));
    created.push(rule.id);

    const resp = await sendAdminRequest(request, ADMIN_RMA.RULES, { params: { name: rule.name } });
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);

    expect(body.meta).toEqual(expect.objectContaining({ total: 1, currentPage: 1 }));
    expect(body.data).toHaveLength(1);
    expect(body.data[0]).toEqual(expect.objectContaining({ id: rule.id, name: rule.name, returnPeriod: 14, status: 0 }));

    const active = await sendAdminRequest(request, ADMIN_RMA.RULES, { params: { name: rule.name, status: '1' } });
    expect((await safeJson(active)).data).toEqual([]);
  });

  test('should return a single rule by id', async ({ request }) => {
    const rule = await createRule(request, ruleInput({ return_period: 21 }));
    created.push(rule.id);

    const resp = await sendAdminRequest(request, ADMIN_RMA.RULE(rule.id));
    expect(resp.status()).toBe(200);
    expect(await safeJson(resp)).toEqual(expect.objectContaining({
      id: rule.id,
      name: rule.name,
      description: rule.description,
      returnPeriod: 21,
    }));
  });

  test('should return 404 for a rule that does not exist', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_RMA.RULE(UNKNOWN_ID));
    expect(resp.status()).toBe(404);
    expect(errorMessage(await safeJson(resp))).toBe(NOT_FOUND);
  });

  test('should update the name, description and return window of a rule', async ({ request }) => {
    const rule = await createRule(request, ruleInput());
    created.push(rule.id);
    const input = ruleInput({ description: 'Extended window from the e2e suite.', return_period: 60 });

    const resp = await sendAdminRequest(request, ADMIN_RMA.RULE(rule.id), { method: 'PUT', data: input });
    expect(resp.status()).toBe(200);
    expect(await safeJson(resp)).toEqual(expect.objectContaining({
      id: rule.id,
      name: input.name,
      description: 'Extended window from the e2e suite.',
      returnPeriod: 60,
    }));

    const detail = await safeJson(await sendAdminRequest(request, ADMIN_RMA.RULE(rule.id)));
    expect(detail).toEqual(expect.objectContaining({ name: input.name, returnPeriod: 60 }));
  });

  test('should return 404 when updating a rule that does not exist', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_RMA.RULE(UNKNOWN_ID), { method: 'PUT', data: ruleInput() });
    expect(resp.status()).toBe(404);
    expect(errorMessage(await safeJson(resp))).toBe(NOT_FOUND);
  });

  test('should delete a rule the test created', async ({ request }) => {
    const rule = await createRule(request, ruleInput());

    const resp = await sendAdminRequest(request, ADMIN_RMA.RULE(rule.id), { method: 'DELETE' });
    expect(resp.status()).toBe(204);

    expect((await sendAdminRequest(request, ADMIN_RMA.RULE(rule.id))).status()).toBe(404);
  });

  test('should return 404 when deleting a rule that does not exist', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_RMA.RULE(UNKNOWN_ID), { method: 'DELETE' });
    expect(resp.status()).toBe(404);
    expect(errorMessage(await safeJson(resp))).toBe(NOT_FOUND);
  });

  test('should mass update the active flag of the rules that exist', async ({ request }) => {
    const first = await createRule(request, ruleInput());
    const second = await createRule(request, ruleInput());
    created.push(first.id, second.id);

    const resp = await sendAdminRequest(request, ADMIN_RMA.RULES_MASS_UPDATE_STATUS, {
      method: 'POST',
      data: { indices: [first.id, second.id, UNKNOWN_ID], value: 1 },
    });
    expect(resp.status()).toBe(201);
    const body = await safeJson(resp);
    expect(body.updated).toEqual([first.id, second.id]);
    expect(body.message).toBe('Selected items updated.');

    for (const id of [first.id, second.id]) {
      expect((await safeJson(await sendAdminRequest(request, ADMIN_RMA.RULE(id)))).status).toBe(1);
    }
  });

  test('should reject a mass rule update without ids', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_RMA.RULES_MASS_UPDATE_STATUS, {
      method: 'POST',
      data: { indices: [], value: 1 },
    });
    expect(resp.status()).toBe(422);
    expect(errorMessage(await safeJson(resp))).toBe('At least one id is required.');
  });

  test('should reject a mass rule update with a value other than zero or one', async ({ request }) => {
    const rule = await createRule(request, ruleInput());
    created.push(rule.id);

    const resp = await sendAdminRequest(request, ADMIN_RMA.RULES_MASS_UPDATE_STATUS, {
      method: 'POST',
      data: { indices: [rule.id], value: 5 },
    });
    expect(resp.status()).toBe(422);
    expect(errorMessage(await safeJson(resp))).toBe('Status value must be 0 or 1.');
  });

  test('should mass delete the rules the test created', async ({ request }) => {
    const first = await createRule(request, ruleInput());
    const second = await createRule(request, ruleInput());

    const resp = await sendAdminRequest(request, ADMIN_RMA.RULES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [UNKNOWN_ID, first.id, second.id] },
    });
    expect(resp.status()).toBe(201);
    const body = await safeJson(resp);
    expect(body.deleted).toEqual([first.id, second.id]);
    expect(body.message).toBe('Selected items deleted.');

    expect((await sendAdminRequest(request, ADMIN_RMA.RULE(first.id))).status()).toBe(404);
    expect((await sendAdminRequest(request, ADMIN_RMA.RULE(second.id))).status()).toBe(404);
  });
});
