import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../graphql/helpers/adminGraphqlClient';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { env } from '../../../config/env';
import {
  ADMIN_RMA_RULES_QUERY,
  ADMIN_RMA_RULE_QUERY,
  CREATE_ADMIN_RMA_RULE,
  UPDATE_ADMIN_RMA_RULE,
  DELETE_ADMIN_RMA_RULE,
  ADMIN_RMA_RULE_MASS_DELETE,
  ADMIN_RMA_RULE_MASS_UPDATE_STATUS,
} from '../../../graphql/Queries/coverage/adminRma.queries';

test.describe.configure({ timeout: 60_000 });

const UNKNOWN_IRI = '/api/admin/rma/rules/999999999';

const NOT_FOUND = 'Return rule not found.';

function uniqueName(): string {
  return `e2e gql rma rule ${Date.now()}-${Math.floor(Math.random() * 1_000_000)}`;
}

function ruleInput(overrides: Record<string, any> = {}): Record<string, any> {
  return {
    name: uniqueName(),
    description: 'Return window created by the e2e suite.',
    status: 0,
    returnPeriod: 30,
    ...overrides,
  };
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

async function createRule(request: APIRequestContext, variables: Record<string, any>): Promise<any> {
  return dataOf(await gql(request, CREATE_ADMIN_RMA_RULE, variables), 'createAdminRmaRule').adminRmaRule;
}

async function ruleById(request: APIRequestContext, id: string): Promise<any> {
  return gql(request, ADMIN_RMA_RULE_QUERY, { id });
}

test.describe('admin rma rules graphql', () => {
  let created: string[];

  test.beforeEach(() => {
    created = [];
  });

  test.afterEach(async ({ request }) => {
    for (const id of created) {
      await sendAdminGraphQLRequest(request, DELETE_ADMIN_RMA_RULE, { id });
    }
  });

  test('should refuse the rule listing without an admin token', async ({ request }) => {
    const resp = await sendGraphQLRequest(request, ADMIN_RMA_RULES_QUERY, { first: 1 }, {}, env.adminGraphqlEndpoint);
    expect(resp.status()).toBe(401);
    expect((await resp.json())?.message).toBe('Unauthenticated.');
  });

  test('should create a rule with its return window', async ({ request }) => {
    const input = ruleInput({ returnPeriod: 45 });
    const rule = await createRule(request, input);
    created.push(rule.id);

    expect(rule.id).toBe(`/api/admin/rma/rules/${rule._id}`);
    expect(rule).toEqual(expect.objectContaining({
      name: input.name,
      description: input.description,
      status: 0,
      returnPeriod: 45,
    }));
  });

  test('should reject a rule without a description', async ({ request }) => {
    const input = ruleInput();
    delete input.description;

    expect(firstError(await gql(request, CREATE_ADMIN_RMA_RULE, input))).toBe('The description field is required.');

    const connection = dataOf(await gql(request, ADMIN_RMA_RULES_QUERY, { name: input.name, first: 5 }), 'adminRmaRules');
    expect(connection.totalCount).toBe(0);
  });

  test('should list only the rule matching the name filter', async ({ request }) => {
    const rule = await createRule(request, ruleInput({ returnPeriod: 14 }));
    created.push(rule.id);

    const connection = dataOf(await gql(request, ADMIN_RMA_RULES_QUERY, { name: rule.name, first: 5 }), 'adminRmaRules');
    expect(connection.totalCount).toBe(1);
    expect(connection.edges[0].node).toEqual(expect.objectContaining({ _id: rule._id, name: rule.name, returnPeriod: 14 }));

    const active = dataOf(await gql(request, ADMIN_RMA_RULES_QUERY, { name: rule.name, status: 1, first: 5 }), 'adminRmaRules');
    expect(active.totalCount).toBe(0);
  });

  test('should return a single rule by iri', async ({ request }) => {
    const rule = await createRule(request, ruleInput({ returnPeriod: 21 }));
    created.push(rule.id);

    const node = dataOf(await ruleById(request, rule.id), 'adminRmaRule');
    expect(node).toEqual(expect.objectContaining({ _id: rule._id, name: rule.name, description: rule.description, returnPeriod: 21 }));
  });

  test('should report a rule that does not exist', async ({ request }) => {
    const body = await ruleById(request, UNKNOWN_IRI);
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.adminRmaRule ?? null).toBeNull();
  });

  test('should update the name, description and return window of a rule', async ({ request }) => {
    const rule = await createRule(request, ruleInput());
    created.push(rule.id);
    const input = ruleInput({ description: 'Extended window from the e2e suite.', returnPeriod: 60 });

    const updated = dataOf(await gql(request, UPDATE_ADMIN_RMA_RULE, { id: rule.id, ...input }), 'updateAdminRmaRule').adminRmaRule;
    expect(updated).toEqual(expect.objectContaining({ _id: rule._id, name: input.name, returnPeriod: 60 }));

    const node = dataOf(await ruleById(request, rule.id), 'adminRmaRule');
    expect(node).toEqual(expect.objectContaining({ description: 'Extended window from the e2e suite.', returnPeriod: 60 }));
  });

  test('should report an update of a rule that does not exist', async ({ request }) => {
    expect(firstError(await gql(request, UPDATE_ADMIN_RMA_RULE, { id: UNKNOWN_IRI, ...ruleInput() }))).toBe(NOT_FOUND);
  });

  test('should delete a rule the test created and confirm it with a message', async ({ request }) => {
    const rule = await createRule(request, ruleInput());

    const deleted = dataOf(await gql(request, DELETE_ADMIN_RMA_RULE, { id: rule.id }), 'deleteAdminRmaRule').adminRmaRule;
    expect(deleted).toEqual(expect.objectContaining({ _id: rule._id, name: rule.name, message: 'Return rule deleted successfully.' }));

    expect(firstError(await ruleById(request, rule.id))).toBe(NOT_FOUND);
  });

  test('should report a delete of a rule that does not exist', async ({ request }) => {
    expect(firstError(await gql(request, DELETE_ADMIN_RMA_RULE, { id: UNKNOWN_IRI }))).toBe(NOT_FOUND);
  });

  test('should mass update the active flag of the rules that exist', async ({ request }) => {
    const first = await createRule(request, ruleInput());
    const second = await createRule(request, ruleInput());
    created.push(first.id, second.id);

    const result = dataOf(
      await gql(request, ADMIN_RMA_RULE_MASS_UPDATE_STATUS, { indices: [999999999, first._id, second._id], value: 1 }),
      'createAdminRmaRuleMassUpdateStatus',
    ).adminRmaRuleMassUpdateStatus;
    expect(result.updated).toEqual([first._id, second._id]);
    expect(result.message).toBe('Selected items updated.');
    expect(dataOf(await ruleById(request, first.id), 'adminRmaRule').status).toBe(1);
  });

  test('should reject a mass rule update with a value other than zero or one', async ({ request }) => {
    const rule = await createRule(request, ruleInput());
    created.push(rule.id);

    expect(firstError(await gql(request, ADMIN_RMA_RULE_MASS_UPDATE_STATUS, { indices: [rule._id], value: 2 }))).toBe('Status value must be 0 or 1.');
    expect(dataOf(await ruleById(request, rule.id), 'adminRmaRule').status).toBe(0);
  });

  test('should mass delete the rules the test created', async ({ request }) => {
    const first = await createRule(request, ruleInput());
    const second = await createRule(request, ruleInput());

    const result = dataOf(
      await gql(request, ADMIN_RMA_RULE_MASS_DELETE, { indices: [first._id, second._id, 999999999] }),
      'createAdminRmaRuleMassDelete',
    ).adminRmaRuleMassDelete;
    expect(result.deleted).toEqual([first._id, second._id]);
    expect(result.message).toBe('Selected items deleted.');
    expect(firstError(await ruleById(request, first.id))).toBe(NOT_FOUND);
  });

  test('should reject a mass rule delete without ids', async ({ request }) => {
    expect(firstError(await gql(request, ADMIN_RMA_RULE_MASS_DELETE, { indices: [] }))).toBe('At least one id is required.');
  });
});
