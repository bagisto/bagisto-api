import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../graphql/helpers/adminGraphqlClient';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { env } from '../../../config/env';
import {
  ADMIN_RMA_CUSTOM_FIELDS_QUERY,
  ADMIN_RMA_CUSTOM_FIELD_QUERY,
  CREATE_ADMIN_RMA_CUSTOM_FIELD,
  UPDATE_ADMIN_RMA_CUSTOM_FIELD,
  DELETE_ADMIN_RMA_CUSTOM_FIELD,
  ADMIN_RMA_CUSTOM_FIELD_MASS_DELETE,
  ADMIN_RMA_CUSTOM_FIELD_MASS_UPDATE_STATUS,
} from '../../../graphql/Queries/coverage/adminRma.queries';

test.describe.configure({ timeout: 60_000 });

const UNKNOWN_IRI = '/api/admin/rma/custom-fields/999999999';

const NOT_FOUND = 'Return custom field not found.';

const SELECT_OPTIONS = [
  { name: 'Refund', value: 'refund' },
  { name: 'Exchange', value: 'exchange' },
];

function uniqueCode(): string {
  return `e2e_gql_rma_cf_${Date.now()}_${Math.floor(Math.random() * 1_000_000)}`;
}

function fieldInput(overrides: Record<string, any> = {}): Record<string, any> {
  const code = uniqueCode();

  return {
    code,
    label: `E2E ${code}`,
    type: 'text',
    position: 1,
    isRequired: 0,
    status: 0,
    ...overrides,
  };
}

function optionPairs(options: any[]): Array<{ name: string; value: string }> {
  return options.map((option: any) => ({ name: option.name, value: option.value }));
}

async function gql(request: APIRequestContext, query: string, variables: Record<string, any> = {}): Promise<any> {
  const resp = await sendAdminGraphQLRequest(request, query, variables);
  expect(resp.status()).toBe(200);
  return resp.json();
}

function dataOf(body: any, field: string): any {
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
  return body.data[field];
}

function firstError(body: any): string | undefined {
  return body.errors?.[0]?.message;
}

async function createField(request: APIRequestContext, variables: Record<string, any>): Promise<any> {
  return dataOf(await gql(request, CREATE_ADMIN_RMA_CUSTOM_FIELD, variables), 'createAdminRmaCustomField').adminRmaCustomField;
}

async function fieldById(request: APIRequestContext, id: string): Promise<any> {
  return gql(request, ADMIN_RMA_CUSTOM_FIELD_QUERY, { id });
}

test.describe('admin rma custom fields graphql', () => {
  let created: string[];

  test.beforeEach(() => {
    created = [];
  });

  test.afterEach(async ({ request }) => {
    for (const id of created) {
      await sendAdminGraphQLRequest(request, DELETE_ADMIN_RMA_CUSTOM_FIELD, { id });
    }
  });

  test('should refuse the custom field listing without an admin token', async ({ request }) => {
    const resp = await sendGraphQLRequest(request, ADMIN_RMA_CUSTOM_FIELDS_QUERY, { first: 1 }, {}, env.adminGraphqlEndpoint);
    expect(resp.status()).toBe(401);
    expect((await resp.json())?.message).toBe('Unauthenticated.');
  });

  test('should create a select custom field with its options', async ({ request }) => {
    const input = fieldInput({ type: 'select', inputValidation: 'alpha', options: SELECT_OPTIONS });
    const field = await createField(request, input);
    created.push(field.id);

    expect(field.id).toBe(`/api/admin/rma/custom-fields/${field._id}`);
    expect(field).toEqual(expect.objectContaining({
      code: input.code,
      label: input.label,
      type: 'select',
      isRequired: 0,
      inputValidation: 'alpha',
      status: 0,
    }));
    expect(optionPairs(field.options)).toEqual(SELECT_OPTIONS);
  });

  test('should reject a radio custom field without options', async ({ request }) => {
    const input = fieldInput({ type: 'radio' });
    expect(firstError(await gql(request, CREATE_ADMIN_RMA_CUSTOM_FIELD, input))).toBe('The options field is required when type is radio.');

    const connection = dataOf(await gql(request, ADMIN_RMA_CUSTOM_FIELDS_QUERY, { code: input.code, first: 5 }), 'adminRmaCustomFields');
    expect(connection.totalCount).toBe(0);
  });

  test('should reject a custom field without a label', async ({ request }) => {
    const input = fieldInput();
    delete input.label;

    expect(firstError(await gql(request, CREATE_ADMIN_RMA_CUSTOM_FIELD, input))).toBe('The label field is required.');
  });

  test('should list only the custom field matching the code and type filters', async ({ request }) => {
    const field = await createField(request, fieldInput({ type: 'textarea' }));
    created.push(field.id);

    const connection = dataOf(await gql(request, ADMIN_RMA_CUSTOM_FIELDS_QUERY, { code: field.code, first: 5 }), 'adminRmaCustomFields');
    expect(connection.totalCount).toBe(1);
    expect(connection.edges[0].node).toEqual(expect.objectContaining({ _id: field._id, code: field.code, type: 'textarea', options: [] }));

    const otherType = dataOf(await gql(request, ADMIN_RMA_CUSTOM_FIELDS_QUERY, { code: field.code, type: 'date', first: 5 }), 'adminRmaCustomFields');
    expect(otherType.totalCount).toBe(0);
  });

  test('should return a single custom field by iri with its options', async ({ request }) => {
    const field = await createField(request, fieldInput({ type: 'multiselect', options: SELECT_OPTIONS }));
    created.push(field.id);

    const node = dataOf(await fieldById(request, field.id), 'adminRmaCustomField');
    expect(node).toEqual(expect.objectContaining({ _id: field._id, code: field.code, type: 'multiselect' }));
    expect(optionPairs(node.options)).toEqual(SELECT_OPTIONS);
  });

  test('should report a custom field that does not exist', async ({ request }) => {
    const body = await fieldById(request, UNKNOWN_IRI);
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.adminRmaCustomField ?? null).toBeNull();
  });

  test('should update a custom field and replace its options', async ({ request }) => {
    const field = await createField(request, fieldInput({ type: 'select', options: SELECT_OPTIONS }));
    created.push(field.id);
    const replacement = [{ name: 'Store credit', value: 'store_credit' }];

    const updated = dataOf(
      await gql(request, UPDATE_ADMIN_RMA_CUSTOM_FIELD, {
        id: field.id,
        code: field.code,
        label: 'Updated by e2e',
        type: 'checkbox',
        position: 6,
        isRequired: 0,
        status: 0,
        options: replacement,
      }),
      'updateAdminRmaCustomField',
    ).adminRmaCustomField;
    expect(updated).toEqual(expect.objectContaining({ _id: field._id, label: 'Updated by e2e', type: 'checkbox', position: 6 }));
    expect(optionPairs(updated.options)).toEqual(replacement);

    expect(optionPairs(dataOf(await fieldById(request, field.id), 'adminRmaCustomField').options)).toEqual(replacement);
  });

  test('should report an update of a custom field that does not exist', async ({ request }) => {
    expect(firstError(await gql(request, UPDATE_ADMIN_RMA_CUSTOM_FIELD, { id: UNKNOWN_IRI, ...fieldInput() }))).toBe(NOT_FOUND);
  });

  test('should delete a custom field the test created and confirm it with a message', async ({ request }) => {
    const field = await createField(request, fieldInput());

    const deleted = dataOf(await gql(request, DELETE_ADMIN_RMA_CUSTOM_FIELD, { id: field.id }), 'deleteAdminRmaCustomField').adminRmaCustomField;
    expect(deleted).toEqual(expect.objectContaining({ _id: field._id, code: field.code, message: 'Return custom field deleted successfully.' }));

    expect(firstError(await fieldById(request, field.id))).toBe(NOT_FOUND);
  });

  test('should report a delete of a custom field that does not exist', async ({ request }) => {
    expect(firstError(await gql(request, DELETE_ADMIN_RMA_CUSTOM_FIELD, { id: UNKNOWN_IRI }))).toBe(NOT_FOUND);
  });

  test('should mass update the active flag of the custom fields that exist', async ({ request }) => {
    const first = await createField(request, fieldInput());
    const second = await createField(request, fieldInput());
    created.push(first.id, second.id);

    const result = dataOf(
      await gql(request, ADMIN_RMA_CUSTOM_FIELD_MASS_UPDATE_STATUS, { indices: [first._id, 999999999, second._id], value: 1 }),
      'createAdminRmaCustomFieldMassUpdateStatus',
    ).adminRmaCustomFieldMassUpdateStatus;
    expect(result.updated).toEqual([first._id, second._id]);
    expect(result.message).toBe('Selected items updated.');
    expect(dataOf(await fieldById(request, second.id), 'adminRmaCustomField').status).toBe(1);
  });

  test('should reject a mass custom field update without ids', async ({ request }) => {
    expect(firstError(await gql(request, ADMIN_RMA_CUSTOM_FIELD_MASS_UPDATE_STATUS, { indices: [], value: 0 }))).toBe('At least one id is required.');
  });

  test('should mass delete the custom fields the test created', async ({ request }) => {
    const first = await createField(request, fieldInput());
    const second = await createField(request, fieldInput({ type: 'radio', options: SELECT_OPTIONS }));

    const result = dataOf(
      await gql(request, ADMIN_RMA_CUSTOM_FIELD_MASS_DELETE, { indices: [first._id, second._id] }),
      'createAdminRmaCustomFieldMassDelete',
    ).adminRmaCustomFieldMassDelete;
    expect(result.deleted).toEqual([first._id, second._id]);
    expect(result.message).toBe('Selected items deleted.');
    expect(firstError(await fieldById(request, second.id))).toBe(NOT_FOUND);
  });

  test('should reject a mass custom field delete without ids', async ({ request }) => {
    expect(firstError(await gql(request, ADMIN_RMA_CUSTOM_FIELD_MASS_DELETE, { indices: [] }))).toBe('At least one id is required.');
  });
});
