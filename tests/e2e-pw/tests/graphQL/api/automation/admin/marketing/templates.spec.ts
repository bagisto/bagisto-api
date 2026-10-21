import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_TEMPLATE_QUERY,
  ADMIN_TEMPLATE_CREATE_MUTATION,
  ADMIN_TEMPLATE_UPDATE_MUTATION,
} from '../../../../graphql/Queries/admin/marketing/templates.queries';
import { UNSAFE_HTML, assertSanitisedHtml } from '../../../../graphql/assertions/adminSanitisedHtml.assertions';

test.describe.configure({ timeout: 60_000 });

const UNKNOWN_IRI = '/api/admin/marketing/templates/999999999';

const NOT_FOUND = 'Email template not found.';

const TEMPLATES_FILTER_QUERY = `
  query adminMarketingTemplates($first: Int, $name: String, $status: String) {
    adminMarketingTemplates(first: $first, name: $name, status: $status) {
      totalCount
      edges { node { _id name status } }
    }
  }
`;

const TEMPLATE_DELETE_MUTATION = `
  mutation deleteAdminMarketingTemplate($input: deleteAdminMarketingTemplateInput!) {
    deleteAdminMarketingTemplate(input: $input) {
      clientMutationId
    }
  }
`;

function uniqueName(): string {
  return `e2e_gql_tpl_${Date.now()}_${Math.floor(Math.random() * 1_000_000)}`;
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

async function templates(request: APIRequestContext, variables: Record<string, any>): Promise<any> {
  return dataOf(await gql(request, TEMPLATES_FILTER_QUERY, { first: 5, ...variables }), 'adminMarketingTemplates');
}

test.describe('admin marketing email templates graphql', () => {
  let created: string[];

  test.beforeEach(() => {
    created = [];
  });

  test.afterEach(async ({ request }) => {
    for (const id of created) {
      await sendAdminGraphQLRequest(request, TEMPLATE_DELETE_MUTATION, { input: { id } });
    }
  });

  async function createTemplate(request: APIRequestContext, input: Record<string, any>): Promise<any> {
    const template = dataOf(await gql(request, ADMIN_TEMPLATE_CREATE_MUTATION, { input }), 'createAdminMarketingTemplate').adminMarketingTemplate;
    created.push(template.id);
    return template;
  }

  test('listing filtered by name returns only the created template', async ({ request }) => {
    const name = uniqueName();
    const template = await createTemplate(request, { name, status: 'active', content: '<p>e2e</p>' });

    const connection = await templates(request, { name });
    expect(connection.totalCount).toBe(1);
    expect(connection.edges[0].node).toEqual({ _id: template._id, name, status: 'active' });
  });

  test('listing status filter keeps or drops the created template by its status', async ({ request }) => {
    const name = uniqueName();
    const template = await createTemplate(request, { name, status: 'draft', content: '<p>e2e</p>' });

    const draft = await templates(request, { name, status: 'draft' });
    expect(draft.totalCount).toBe(1);
    expect(draft.edges[0].node._id).toBe(template._id);

    const active = await templates(request, { name, status: 'active' });
    expect(active.totalCount).toBe(0);
  });

  test('detail unknown id reports the template as not found', async ({ request }) => {
    const body = await gql(request, ADMIN_TEMPLATE_QUERY, { id: UNKNOWN_IRI });
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.adminMarketingTemplate ?? null).toBeNull();
  });

  test('create empty payload is rejected with the name requirement', async ({ request }) => {
    const body = await gql(request, ADMIN_TEMPLATE_CREATE_MUTATION, { input: {} });
    expect(firstError(body)).toBe('The name field is required.');
    expect(body.data?.createAdminMarketingTemplate ?? null).toBeNull();
  });

  test('create + update + delete round trip', async ({ request }) => {
    const name = uniqueName();
    const template = await createTemplate(request, { name, status: 'active', content: '<p>e2e gql</p>' });
    expect(template).toEqual({ id: `/api/admin/marketing/templates/${template._id}`, _id: template._id, name, status: 'active' });

    const updatedName = `${name}_upd`;
    const updated = dataOf(
      await gql(request, ADMIN_TEMPLATE_UPDATE_MUTATION, {
        input: { id: template.id, name: updatedName, status: 'inactive', content: '<p>upd</p>' },
      }),
      'updateAdminMarketingTemplate',
    ).adminMarketingTemplate;
    expect(updated).toEqual({ id: template.id, _id: template._id, name: updatedName });

    const read = dataOf(await gql(request, ADMIN_TEMPLATE_QUERY, { id: template.id }), 'adminMarketingTemplate');
    expect(read).toEqual({ id: template.id, _id: template._id, name: updatedName, status: 'inactive', content: '<p>upd</p>' });

    const deleted = dataOf(
      await gql(request, TEMPLATE_DELETE_MUTATION, { input: { id: template.id, clientMutationId: 'e2e-delete-template' } }),
      'deleteAdminMarketingTemplate',
    );
    expect(deleted.clientMutationId).toBe('e2e-delete-template');
    created = [];

    expect(firstError(await gql(request, ADMIN_TEMPLATE_QUERY, { id: template.id }))).toBe(NOT_FOUND);
  });

  test('create invalid status is rejected and nothing is stored', async ({ request }) => {
    const name = uniqueName();
    const body = await gql(request, ADMIN_TEMPLATE_CREATE_MUTATION, {
      input: { name, status: 'banana', content: '<p>x</p>' },
    });
    expect(firstError(body)).toBe('The selected status is invalid.');
    expect(body.data?.createAdminMarketingTemplate ?? null).toBeNull();
    expect((await templates(request, { name })).totalCount).toBe(0);
  });

  test('should strip scripts and template code from email template content on create and update', async ({ request }) => {
    const name = uniqueName();
    const template = await createTemplate(request, { name, status: 'active', content: UNSAFE_HTML });

    const createdBody = await gql(request, ADMIN_TEMPLATE_QUERY, { id: template.id });
    assertSanitisedHtml(dataOf(createdBody, 'adminMarketingTemplate').content);

    dataOf(
      await gql(request, ADMIN_TEMPLATE_UPDATE_MUTATION, {
        input: { id: template.id, name: `${name}_upd`, status: 'inactive', content: `${UNSAFE_HTML}<p>Updated</p>` },
      }),
      'updateAdminMarketingTemplate',
    );

    const updatedContent = dataOf(await gql(request, ADMIN_TEMPLATE_QUERY, { id: template.id }), 'adminMarketingTemplate').content;
    assertSanitisedHtml(updatedContent);
    expect(updatedContent).toContain('<p>Updated</p>');
  });
});
