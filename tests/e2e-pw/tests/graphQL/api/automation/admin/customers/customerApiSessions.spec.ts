import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import { sendGraphQLRequest } from '../../../../graphql/helpers/graphqlClient';

test.describe.configure({ timeout: 120_000 });

const REGISTER = `
  mutation register($input: createCustomerInput!) {
    createCustomer(input: $input) { customer { _id } }
  }
`;

const LOGIN = `
  mutation login($input: createCustomerLoginInput!) {
    createCustomerLogin(input: $input) { customerLogin { accessToken session customer } }
  }
`;

const SESSIONS = `
  query sessions($customerId: Int) {
    adminCustomerApiSessions(customer_id: $customerId, first: 50) {
      edges { node { _id customerId customerEmail clientType expiresAt } }
    }
  }
`;

const DELETE_SESSION = `
  mutation revoke($input: deleteAdminCustomerApiSessionInput!) {
    deleteAdminCustomerApiSession(input: $input) { adminCustomerApiSession { _id revokedReason } }
  }
`;

const REVOKE_ALL = `
  mutation revokeAll($input: createAdminCustomerApiSessionRevokeInput!) {
    createAdminCustomerApiSessionRevoke(input: $input) { adminCustomerApiSessionRevoke { customerId revoked } }
  }
`;

async function signedIn(request: APIRequestContext, count: number): Promise<{ customerId: number; sessionIds: number[] }> {
  const suffix = `${Date.now()}${Math.floor(Math.random() * 100000)}`;
  const credentials = { email: `admin.gql.sessions.${suffix}@example.com`, password: `Sess${suffix}!Aa` };

  const register = await (await sendGraphQLRequest(request, REGISTER, {
    input: { firstName: 'Admin', lastName: 'Sessions', ...credentials, confirmPassword: credentials.password },
  })).json();

  expect(register.errors, JSON.stringify(register.errors)).toBeUndefined();

  const sessionIds: number[] = [];
  let customerId = 0;

  for (let i = 0; i < count; i++) {
    const body = await (await sendGraphQLRequest(request, LOGIN, { input: credentials })).json();

    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

    const login = body.data.createCustomerLogin.customerLogin;

    customerId = Number(login.customer.id);
    sessionIds.push(Number(login.session.id));
  }

  return { customerId, sessionIds };
}

test.describe('admin customer api sessions graphql api', () => {
  test('adminCustomerApiSessions lists a customer\'s sessions', async ({ request }) => {
    const { customerId, sessionIds } = await signedIn(request, 2);

    const body = await (await sendAdminGraphQLRequest(request, SESSIONS, { customerId })).json();

    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

    const nodes = body.data.adminCustomerApiSessions.edges.map((edge: any) => edge.node);

    expect(nodes.map((node: any) => node._id)).toEqual(expect.arrayContaining(sessionIds));
    expect(nodes[0].customerId).toBe(customerId);
  });

  test('deleteAdminCustomerApiSession and createAdminCustomerApiSessionRevoke end sessions', async ({ request }) => {
    const { customerId, sessionIds } = await signedIn(request, 3);

    const deleted = await (await sendAdminGraphQLRequest(request, DELETE_SESSION, {
      input: { id: `/api/admin/customers/api-sessions/${sessionIds[0]}` },
    })).json();

    expect(deleted.errors, JSON.stringify(deleted.errors)).toBeUndefined();
    expect(deleted.data.deleteAdminCustomerApiSession.adminCustomerApiSession._id).toBe(sessionIds[0]);

    const revoked = await (await sendAdminGraphQLRequest(request, REVOKE_ALL, { input: { customerId } })).json();

    expect(revoked.errors, JSON.stringify(revoked.errors)).toBeUndefined();
    expect(revoked.data.createAdminCustomerApiSessionRevoke.adminCustomerApiSessionRevoke.revoked).toBe(3);
  });
});
