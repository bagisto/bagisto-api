import { test, expect, APIRequestContext, APIResponse } from '@playwright/test';
import { sendAdminRequest } from '../../../rest/helpers/adminClient';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import { ADMIN_RMA } from '../../../rest/endpoints/coverage/adminRma.endpoints';

test.describe.configure({ timeout: 60_000 });

const UNKNOWN_ID = 999999999;

const NOT_FOUND = 'Return custom field not found.';

const SELECT_OPTIONS = [
  { name: 'Refund', value: 'refund' },
  { name: 'Exchange', value: 'exchange' },
];

async function safeJson(resp: APIResponse): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

function errorMessage(body: any): string | undefined {
  return body?.detail ?? body?.message;
}

function uniqueCode(): string {
  return `e2e_rma_cf_${Date.now()}_${Math.floor(Math.random() * 1_000_000)}`;
}

function fieldInput(overrides: Record<string, any> = {}): Record<string, any> {
  const code = uniqueCode();

  return {
    code,
    label: `E2E ${code}`,
    type: 'text',
    position: 1,
    is_required: 0,
    status: 0,
    ...overrides,
  };
}

async function createField(request: APIRequestContext, data: Record<string, any>): Promise<any> {
  const resp = await sendAdminRequest(request, ADMIN_RMA.CUSTOM_FIELDS, { method: 'POST', data });
  expect(resp.status(), await resp.text()).toBe(201);
  return safeJson(resp);
}

function optionPairs(options: any[]): Array<{ name: string; value: string }> {
  return options.map((option: any) => ({ name: option.name, value: option.value }));
}

test.describe('admin rma custom fields rest api', () => {
  let created: number[];

  test.beforeEach(() => {
    created = [];
  });

  test.afterEach(async ({ request }) => {
    for (const id of created) {
      await sendAdminRequest(request, ADMIN_RMA.CUSTOM_FIELD(id), { method: 'DELETE' });
    }
  });

  test('should refuse the custom field listing without an admin token', async ({ request }) => {
    const resp = await sendRestRequest(request, ADMIN_RMA.CUSTOM_FIELDS);
    expect(resp.status()).toBe(401);
    expect((await safeJson(resp))?.message).toBe('Unauthenticated.');
  });

  test('should refuse to create a custom field without an admin token', async ({ request }) => {
    const resp = await sendRestRequest(request, ADMIN_RMA.CUSTOM_FIELDS, { method: 'POST', data: fieldInput() });
    expect(resp.status()).toBe(401);
    expect((await safeJson(resp))?.message).toBe('Unauthenticated.');
  });

  test('should create a text custom field without options', async ({ request }) => {
    const input = fieldInput({ position: 4, input_validation: 'alpha' });
    const body = await createField(request, input);
    created.push(body.id);

    expect(typeof body.id).toBe('number');
    expect(body).toEqual(expect.objectContaining({
      code: input.code,
      label: input.label,
      type: 'text',
      position: 4,
      isRequired: 0,
      inputValidation: 'alpha',
      status: 0,
      options: [],
    }));
  });

  test('should create a select custom field with its options', async ({ request }) => {
    const input = fieldInput({ type: 'select', options: SELECT_OPTIONS });
    const body = await createField(request, input);
    created.push(body.id);

    expect(body.type).toBe('select');
    expect(optionPairs(body.options)).toEqual(SELECT_OPTIONS);
    for (const option of body.options) {
      expect(typeof option.id).toBe('number');
    }
  });

  test('should reject a select custom field without options', async ({ request }) => {
    const input = fieldInput({ type: 'select' });
    const resp = await sendAdminRequest(request, ADMIN_RMA.CUSTOM_FIELDS, { method: 'POST', data: input });
    expect(resp.status()).toBe(422);
    expect(errorMessage(await safeJson(resp))).toBe('The options field is required when type is select.');

    const list = await sendAdminRequest(request, ADMIN_RMA.CUSTOM_FIELDS, { params: { code: input.code } });
    expect((await safeJson(list)).data).toEqual([]);
  });

  test('should reject a custom field of an unsupported type', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_RMA.CUSTOM_FIELDS, {
      method: 'POST',
      data: fieldInput({ type: 'file' }),
    });
    expect(resp.status()).toBe(422);
    expect(errorMessage(await safeJson(resp))).toBe('The selected type is invalid.');
  });

  test('should reject a custom field whose code is already taken', async ({ request }) => {
    const first = await createField(request, fieldInput());
    created.push(first.id);

    const resp = await sendAdminRequest(request, ADMIN_RMA.CUSTOM_FIELDS, {
      method: 'POST',
      data: fieldInput({ code: first.code }),
    });
    expect(resp.status()).toBe(422);
    expect(errorMessage(await safeJson(resp))).toBe('The code has already been taken.');
  });

  test('should list only the custom field matching the code filter in a data and meta envelope', async ({ request }) => {
    const field = await createField(request, fieldInput({ type: 'radio', options: SELECT_OPTIONS }));
    created.push(field.id);

    const resp = await sendAdminRequest(request, ADMIN_RMA.CUSTOM_FIELDS, { params: { code: field.code } });
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);

    expect(body.meta).toEqual(expect.objectContaining({ total: 1, currentPage: 1 }));
    expect(body.data).toHaveLength(1);
    expect(body.data[0]).toEqual(expect.objectContaining({ id: field.id, code: field.code, type: 'radio' }));

    const otherType = await sendAdminRequest(request, ADMIN_RMA.CUSTOM_FIELDS, { params: { code: field.code, type: 'text' } });
    expect((await safeJson(otherType)).data).toEqual([]);
  });

  test('should return a single custom field with its options', async ({ request }) => {
    const field = await createField(request, fieldInput({ type: 'checkbox', options: SELECT_OPTIONS }));
    created.push(field.id);

    const resp = await sendAdminRequest(request, ADMIN_RMA.CUSTOM_FIELD(field.id));
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);
    expect(body).toEqual(expect.objectContaining({ id: field.id, code: field.code, label: field.label, type: 'checkbox' }));
    expect(optionPairs(body.options)).toEqual(SELECT_OPTIONS);
  });

  test('should return 404 for a custom field that does not exist', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_RMA.CUSTOM_FIELD(UNKNOWN_ID));
    expect(resp.status()).toBe(404);
    expect(errorMessage(await safeJson(resp))).toBe(NOT_FOUND);
  });

  test('should update a custom field and replace its options', async ({ request }) => {
    const field = await createField(request, fieldInput({ type: 'select', options: SELECT_OPTIONS }));
    created.push(field.id);
    const replacement = [{ name: 'Store credit', value: 'store_credit' }];

    const resp = await sendAdminRequest(request, ADMIN_RMA.CUSTOM_FIELD(field.id), {
      method: 'PUT',
      data: {
        code: field.code,
        label: 'Updated by e2e',
        type: 'multiselect',
        position: 5,
        is_required: 0,
        status: 0,
        options: replacement,
      },
    });
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);
    expect(body).toEqual(expect.objectContaining({ id: field.id, label: 'Updated by e2e', type: 'multiselect', position: 5 }));
    expect(optionPairs(body.options)).toEqual(replacement);

    const detail = await safeJson(await sendAdminRequest(request, ADMIN_RMA.CUSTOM_FIELD(field.id)));
    expect(optionPairs(detail.options)).toEqual(replacement);
  });

  test('should return 404 when updating a custom field that does not exist', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_RMA.CUSTOM_FIELD(UNKNOWN_ID), { method: 'PUT', data: fieldInput() });
    expect(resp.status()).toBe(404);
    expect(errorMessage(await safeJson(resp))).toBe(NOT_FOUND);
  });

  test('should delete a custom field the test created and confirm it with a message', async ({ request }) => {
    const field = await createField(request, fieldInput());

    const resp = await sendAdminRequest(request, ADMIN_RMA.CUSTOM_FIELD(field.id), { method: 'DELETE' });
    expect(resp.status()).toBe(200);
    expect((await safeJson(resp))?.message).toBe('Return custom field deleted successfully.');

    expect((await sendAdminRequest(request, ADMIN_RMA.CUSTOM_FIELD(field.id))).status()).toBe(404);
  });

  test('should return 404 when deleting a custom field that does not exist', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_RMA.CUSTOM_FIELD(UNKNOWN_ID), { method: 'DELETE' });
    expect(resp.status()).toBe(404);
    expect(errorMessage(await safeJson(resp))).toBe(NOT_FOUND);
  });

  test('should mass update the active flag of the custom fields that exist', async ({ request }) => {
    const first = await createField(request, fieldInput({ status: 1 }));
    const second = await createField(request, fieldInput({ status: 1 }));
    created.push(first.id, second.id);

    const resp = await sendAdminRequest(request, ADMIN_RMA.CUSTOM_FIELDS_MASS_UPDATE_STATUS, {
      method: 'POST',
      data: { indices: [first.id, second.id, UNKNOWN_ID], value: 0 },
    });
    expect(resp.status()).toBe(201);
    const body = await safeJson(resp);
    expect(body.updated).toEqual([first.id, second.id]);
    expect(body.message).toBe('Selected items updated.');

    for (const id of [first.id, second.id]) {
      expect((await safeJson(await sendAdminRequest(request, ADMIN_RMA.CUSTOM_FIELD(id)))).status).toBe(0);
    }
  });

  test('should reject a mass custom field update with a value other than zero or one', async ({ request }) => {
    const field = await createField(request, fieldInput());
    created.push(field.id);

    const resp = await sendAdminRequest(request, ADMIN_RMA.CUSTOM_FIELDS_MASS_UPDATE_STATUS, {
      method: 'POST',
      data: { indices: [field.id], value: 9 },
    });
    expect(resp.status()).toBe(422);
    expect(errorMessage(await safeJson(resp))).toBe('Status value must be 0 or 1.');
  });

  test('should mass delete the custom fields the test created', async ({ request }) => {
    const first = await createField(request, fieldInput());
    const second = await createField(request, fieldInput({ type: 'select', options: SELECT_OPTIONS }));

    const resp = await sendAdminRequest(request, ADMIN_RMA.CUSTOM_FIELDS_MASS_DELETE, {
      method: 'POST',
      data: { indices: [first.id, UNKNOWN_ID, second.id] },
    });
    expect(resp.status()).toBe(201);
    const body = await safeJson(resp);
    expect(body.deleted).toEqual([first.id, second.id]);
    expect(body.message).toBe('Selected items deleted.');

    expect((await sendAdminRequest(request, ADMIN_RMA.CUSTOM_FIELD(first.id))).status()).toBe(404);
    expect((await sendAdminRequest(request, ADMIN_RMA.CUSTOM_FIELD(second.id))).status()).toBe(404);
  });

  test('should reject a mass custom field delete without ids', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_RMA.CUSTOM_FIELDS_MASS_DELETE, { method: 'POST', data: { indices: [] } });
    expect(resp.status()).toBe(422);
    expect(errorMessage(await safeJson(resp))).toBe('At least one id is required.');
  });
});
