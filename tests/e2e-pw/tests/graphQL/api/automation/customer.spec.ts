import { test, expect, APIRequestContext } from '@playwright/test';
import { getCustomerAuthHeaders } from '../../config/auth';
import { SHOP_DOCS_QUERIES } from '../../graphql/Queries/shopDocs.queries';
import {
  CREATE_CUSTOMER,
  CREATE_CUSTOMER_PROFILE_DELETE,
  CREATE_CUSTOMER_PROFILE_UPDATE,
  CREATE_FORGOT_PASSWORD,
  CREATE_LOGOUT,
  CREATE_VERIFY_TOKEN,
  GET_CUSTOMER_ORDERS_BY_STATUS,
  GET_CUSTOMER_ORDERS_PAGINATED,
} from '../../graphql/Queries/customer.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { expectConnection, expectGraphQLSuccess, graphQLErrorMessages } from '../../graphql/helpers/testSupport';
import { e2eFixtures, missingFixtureReason, E2eCustomerFixture } from '../../../fixtures/e2eFixtures';

const MSG = {
  INVALID_CREDENTIALS: 'Invalid email or password',
  LOGIN_SUCCESS: 'You have logged in successfully',
  TOKEN_VALID: 'Token is valid',
  UNAUTHENTICATED: 'Unauthenticated. Please login to perform this action',
  EMAIL_NOT_FOUND: 'Email address not found',
  PROFILE_UPDATED: 'Customer profile updated successfully',
  LOGGED_OUT: 'Logged out successfully',
  TOKEN_REQUIRED: 'Authentication token is required. Please provide the token in the Authorization header as "Bearer <token>"',
  DELETE_PASSWORD_INCORRECT: 'The password you entered is incorrect',
  CURRENT_PASSWORD_INCORRECT: 'The current password you entered is incorrect',
};

const CUSTOMER_LOGIN = `
  mutation createCustomerLogin($email: String!, $password: String!) {
    createCustomerLogin(input: { email: $email, password: $password }) {
      customerLogin {
        token
        accessToken
        refreshToken
        success
        message
      }
    }
  }
`;

const PROFILE_UPDATE_WITH_RESULT = `
  mutation updateCustomerProfile($input: createCustomerProfileUpdateInput!) {
    createCustomerProfileUpdate(input: $input) {
      customerProfileUpdate {
        success
        message
        firstName
        lastName
        gender
      }
    }
  }
`;

const PROFILE_DELETE_WITH_PASSWORD = `
  mutation deleteCustomerProfile($input: createCustomerProfileDeleteInput!) {
    createCustomerProfileDelete(input: $input) {
      customerProfileDelete {
        id
      }
    }
  }
`;

const ORDER_SHIPMENTS = `
  query getOrderShipments($orderId: Int, $first: Int) {
    customerOrderShipments(orderId: $orderId, first: $first) {
      edges {
        node {
          _id
        }
      }
      totalCount
    }
  }
`;

interface RegisteredCustomer {
  email: string;
  password: string;
  headers: Record<string, string>;
}

function uniqueSuffix(): string {
  return `${Date.now()}-${Math.floor(Math.random() * 100000)}`;
}

async function gql(
  request: APIRequestContext,
  query: string,
  variables: Record<string, any> = {},
  headers: Record<string, string> = {},
  status = 200
): Promise<any> {
  const response = await sendGraphQLRequest(request, query, variables, headers);
  expect(response.status()).toBe(status);

  return response.json();
}

function errorCode(body: any): string | undefined {
  return body?.errors?.[0]?.extensions?.code;
}

async function registerCustomer(request: APIRequestContext, firstName = 'Graph', lastName = 'Customer'): Promise<RegisteredCustomer> {
  const email = `playwright.customer.${uniqueSuffix()}@playwrighttest.local`;
  const password = 'Passw0rd!123';

  const body = await gql(request, CREATE_CUSTOMER, {
    input: { firstName, lastName, email, password, confirmPassword: password, subscribedToNewsLetter: false },
  });
  expect(body.errors, graphQLErrorMessages(body).join(' | ')).toBeUndefined();

  const token = body.data.createCustomer.customer.token;
  expect(typeof token).toBe('string');

  return { email, password, headers: { Authorization: `Bearer ${token}` } };
}

async function fixtureHeaders(request: APIRequestContext, customer: E2eCustomerFixture): Promise<Record<string, string>> {
  const body = await gql(request, CUSTOMER_LOGIN, { email: customer.email, password: customer.password });
  expect(body.errors).toBeUndefined();
  expect(body.data.createCustomerLogin.customerLogin.success).toBe(true);

  return { Authorization: `Bearer ${body.data.createCustomerLogin.customerLogin.token}` };
}

function nodeIds(connection: any): number[] {
  return connection.edges.map((edge: any) => Number(edge.node._id));
}

test.describe('customer graphql api', () => {
  test('should return the profile of the authenticated customer', async ({ request }) => {
    const customer = await registerCustomer(request, 'Profile', 'Reader');
    const body = await gql(request, SHOP_DOCS_QUERIES.getCustomerProfile, {}, customer.headers);

    expectGraphQLSuccess(body, 'data.readCustomerProfile');
    expect(body.data.readCustomerProfile.email).toBe(customer.email);
    expect(body.data.readCustomerProfile.firstName).toBe('Profile');
    expect(body.data.readCustomerProfile.lastName).toBe('Reader');
  });

  test('should refuse the profile without a customer token', async ({ request }) => {
    const body = await gql(request, SHOP_DOCS_QUERIES.getCustomerProfile);

    expect(body.data?.readCustomerProfile ?? null).toBeNull();
    expect(graphQLErrorMessages(body)).toEqual([MSG.UNAUTHENTICATED]);
  });

  test('should list the orders of the customer', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const headers = await fixtureHeaders(request, fx.customer!);
    const connection = expectConnection(
      await gql(request, SHOP_DOCS_QUERIES.getCustomerOrders, { first: 50 }, headers),
      'data.customerOrders'
    );

    const order = connection.edges.find((edge: any) => edge.node._id === fx.order!.id)?.node;
    expect(order, `order ${fx.order!.id} is not listed`).toBeTruthy();
    expect(order.incrementId).toBe(fx.order!.incrementId);
  });

  test('should list no orders for a customer who placed none', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);
    const connection = expectConnection(
      await gql(request, SHOP_DOCS_QUERIES.getCustomerOrders, { first: 5 }, headers),
      'data.customerOrders'
    );

    expect(connection.totalCount).toBe(0);
    expect(connection.edges).toEqual([]);
  });

  test('should fetch a single customer order', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const headers = await fixtureHeaders(request, fx.customer!);
    const body = await gql(request, SHOP_DOCS_QUERIES.getCustomerOrder, { id: `/api/shop/customer-orders/${fx.order!.id}` }, headers);

    expectGraphQLSuccess(body, 'data.customerOrder');
    expect(body.data.customerOrder._id).toBe(fx.order!.id);
    expect(body.data.customerOrder.incrementId).toBe(fx.order!.incrementId);
  });

  test('should fetch the shipments of a customer order', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const headers = await fixtureHeaders(request, fx.customer!);
    const connection = expectConnection(
      await gql(request, ORDER_SHIPMENTS, { orderId: fx.order!.id, first: 10 }, headers),
      'data.customerOrderShipments'
    );

    expect(nodeIds(connection)).toEqual([fx.order!.shipmentId]);
  });

  test('should filter the customer orders by status', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const headers = await fixtureHeaders(request, fx.customer!);
    const detail = await gql(request, SHOP_DOCS_QUERIES.getCustomerOrder, { id: String(fx.order!.id) }, headers);
    expectGraphQLSuccess(detail, 'data.customerOrder');
    const status = detail.data.customerOrder.status;

    const matching = expectConnection(
      await gql(request, GET_CUSTOMER_ORDERS_BY_STATUS, { first: 50, status }, headers),
      'data.customerOrders'
    );
    expect(nodeIds(matching)).toContain(fx.order!.id);
    for (const edge of matching.edges) {
      expect(edge.node.status).toBe(status);
    }

    const otherStatus = status === 'canceled' ? 'closed' : 'canceled';
    const other = expectConnection(
      await gql(request, GET_CUSTOMER_ORDERS_BY_STATUS, { first: 50, status: otherStatus }, headers),
      'data.customerOrders'
    );
    expect(nodeIds(other)).not.toContain(fx.order!.id);
  });

  test('should page through the customer orders', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const headers = await fixtureHeaders(request, fx.customer!);
    const firstPage = expectConnection(
      await gql(request, GET_CUSTOMER_ORDERS_PAGINATED, { first: 1, after: null }, headers),
      'data.customerOrders'
    );

    expect(firstPage.edges).toHaveLength(1);
    expect(firstPage.pageInfo.hasNextPage).toBe(firstPage.totalCount > 1);

    if (firstPage.totalCount > 1) {
      const secondPage = expectConnection(
        await gql(request, GET_CUSTOMER_ORDERS_PAGINATED, { first: 1, after: firstPage.pageInfo.endCursor }, headers),
        'data.customerOrders'
      );
      expect(secondPage.edges).toHaveLength(1);
      expect(secondPage.edges[0].node._id).not.toBe(firstPage.edges[0].node._id);
    }
  });

  test('should refuse a login with an email that is not registered', async ({ request }) => {
    const body = await gql(request, CUSTOMER_LOGIN, {
      email: `bagisto.playwright+${uniqueSuffix()}@example.com`,
      password: 'SecurePass@123',
    });

    expect(errorCode(body)).toBe('invalid_credentials');
    expect(graphQLErrorMessages(body)).toEqual([MSG.INVALID_CREDENTIALS]);
    expect(body.data?.createCustomerLogin ?? null).toBeNull();
  });

  test('should log a registered customer in and verify the issued token', async ({ request }) => {
    const customer = await registerCustomer(request);

    const loginBody = await gql(request, CUSTOMER_LOGIN, { email: customer.email, password: customer.password });
    expect(loginBody.errors).toBeUndefined();
    const login = loginBody.data.createCustomerLogin.customerLogin;
    expect(login.success).toBe(true);
    expect(login.message).toBe(MSG.LOGIN_SUCCESS);
    expect(login.token).toBe(login.accessToken);
    expect(String(login.accessToken).split('.')).toHaveLength(3);
    expect(typeof login.refreshToken).toBe('string');

    const verifyBody = await gql(request, CREATE_VERIFY_TOKEN, {}, { Authorization: `Bearer ${login.token}` });
    expect(verifyBody.errors).toBeUndefined();
    expect(verifyBody.data.createVerifyToken.verifyToken.isValid).toBe(true);
    expect(verifyBody.data.createVerifyToken.verifyToken.message).toBe(MSG.TOKEN_VALID);
  });

  test('should report a token verification without a token as unauthenticated', async ({ request }) => {
    const body = await gql(request, CREATE_VERIFY_TOKEN);

    expect(body.errors).toBeUndefined();
    expect(body.data.createVerifyToken.verifyToken.isValid).toBe(false);
    expect(body.data.createVerifyToken.verifyToken.message).toBe(MSG.UNAUTHENTICATED);
  });

  test('should register a customer with full details', async ({ request }) => {
    const email = `bagisto.playwright+${uniqueSuffix()}@example.com`;
    const phone = String(Date.now()).slice(-10);

    const body = await gql(request, CREATE_CUSTOMER, {
      input: {
        firstName: 'GraphQL',
        lastName: 'Tester',
        gender: 'Male',
        dateOfBirth: '1990-01-15',
        phone,
        email,
        password: 'SecurePass@123',
        confirmPassword: 'SecurePass@123',
        subscribedToNewsLetter: true,
      },
    });

    expect(body.errors, graphQLErrorMessages(body).join(' | ')).toBeUndefined();
    const customer = body.data.createCustomer.customer;
    expect(customer.email).toBe(email);
    expect(customer.firstName).toBe('GraphQL');
    expect(customer.lastName).toBe('Tester');
    expect(customer.phone).toBe(phone);
    expect(typeof customer.token).toBe('string');
  });

  test('should report a forgot password request for an email that is not registered', async ({ request }) => {
    const body = await gql(request, CREATE_FORGOT_PASSWORD, { email: `bagisto.playwright+${uniqueSuffix()}@example.com` });

    expect(body.errors).toBeUndefined();
    expect(body.data.createForgotPassword.forgotPassword.success).toBe(false);
    expect(body.data.createForgotPassword.forgotPassword.message).toBe(MSG.EMAIL_NOT_FOUND);
  });

  test('should update the customer profile and read the change back', async ({ request }) => {
    const customer = await registerCustomer(request);

    const updateBody = await gql(
      request,
      PROFILE_UPDATE_WITH_RESULT,
      { input: { firstName: 'Jane', lastName: 'Doe', gender: 'female' } },
      customer.headers
    );
    expect(updateBody.errors, graphQLErrorMessages(updateBody).join(' | ')).toBeUndefined();
    const updated = updateBody.data.createCustomerProfileUpdate.customerProfileUpdate;
    expect(updated.success).toBe(true);
    expect(updated.message).toBe(MSG.PROFILE_UPDATED);

    const profile = await gql(request, SHOP_DOCS_QUERIES.getCustomerProfile, {}, customer.headers);
    expectGraphQLSuccess(profile, 'data.readCustomerProfile');
    expect(profile.data.readCustomerProfile.firstName).toBe('Jane');
    expect(profile.data.readCustomerProfile.lastName).toBe('Doe');
  });

  test('should log the customer out and revoke the token', async ({ request }) => {
    const customer = await registerCustomer(request);

    const logoutBody = await gql(request, CREATE_LOGOUT, {}, customer.headers);
    expect(logoutBody.errors).toBeUndefined();
    expect(logoutBody.data.createLogout.logout.success).toBe(true);
    expect(logoutBody.data.createLogout.logout.message).toBe(MSG.LOGGED_OUT);

    const profile = await gql(request, SHOP_DOCS_QUERIES.getCustomerProfile, {}, customer.headers, 401);
    expect(profile.data?.readCustomerProfile ?? null).toBeNull();
    expect(errorCode(profile)).toBe('unauthenticated');
  });

  test('should refuse to delete a profile without a customer token', async ({ request }) => {
    const body = await gql(request, CREATE_CUSTOMER_PROFILE_DELETE);

    expect(body.data?.createCustomerProfileDelete ?? null).toBeNull();
    expect(graphQLErrorMessages(body)).toEqual([MSG.TOKEN_REQUIRED]);
  });

  test('should refuse to delete a profile with the wrong password and keep the account', async ({ request }) => {
    const customer = await registerCustomer(request);

    const body = await gql(request, PROFILE_DELETE_WITH_PASSWORD, { input: { password: 'WrongPassw0rd!' } }, customer.headers);
    expect(body.data?.createCustomerProfileDelete ?? null).toBeNull();
    expect(graphQLErrorMessages(body)).toEqual([MSG.DELETE_PASSWORD_INCORRECT]);

    const profile = await gql(request, SHOP_DOCS_QUERIES.getCustomerProfile, {}, customer.headers);
    expectGraphQLSuccess(profile, 'data.readCustomerProfile');
    expect(profile.data.readCustomerProfile.email).toBe(customer.email);
  });
});

test.describe('customer profile update current password regression', () => {
  test.slow();

  test('should change the password when the current password is correct and log in with the new one', async ({ request }) => {
    const email = `playwright.pwd.ok.${uniqueSuffix()}@playwrighttest.local`;
    const oldPassword = 'OldPassw0rd!123';
    const newPassword = 'NewPassw0rd!456';

    const registerBody = await gql(request, CREATE_CUSTOMER, {
      input: {
        firstName: 'Pwd',
        lastName: 'Regression',
        email,
        password: oldPassword,
        status: '1',
        subscribedToNewsLetter: false,
        isVerified: '1',
        isSuspended: '0',
      },
    });
    const initialToken = registerBody.data?.createCustomer?.customer?.token;
    expect(initialToken, `failed to register customer for pwd-regression: ${JSON.stringify(registerBody)}`).toBeTruthy();

    const updateBody = await gql(
      request,
      CREATE_CUSTOMER_PROFILE_UPDATE,
      {
        input: {
          firstName: 'Pwd',
          lastName: 'Regression',
          currentPassword: oldPassword,
          password: newPassword,
          confirmPassword: newPassword,
        },
      },
      { Authorization: `Bearer ${initialToken}` }
    );
    expect(updateBody.errors, `update errored: ${graphQLErrorMessages(updateBody).join(' | ')}`).toBeUndefined();

    const reloginBody = await gql(request, CUSTOMER_LOGIN, { email, password: newPassword });
    expect(
      reloginBody.data?.createCustomerLogin?.customerLogin?.token,
      `re-login with new password failed: ${JSON.stringify(reloginBody)}`
    ).toBeTruthy();

    const oldLoginBody = await gql(request, CUSTOMER_LOGIN, { email, password: oldPassword });
    expect(errorCode(oldLoginBody)).toBe('invalid_credentials');
  });

  test('should reject a password change when the current password is wrong and keep the old password', async ({ request }) => {
    const email = `playwright.pwd.bad.${uniqueSuffix()}@playwrighttest.local`;
    const oldPassword = 'OldPassw0rd!123';
    const newPassword = 'NewPassw0rd!456';

    const registerBody = await gql(request, CREATE_CUSTOMER, {
      input: {
        firstName: 'Pwd',
        lastName: 'WrongCurrent',
        email,
        password: oldPassword,
        status: '1',
        subscribedToNewsLetter: false,
        isVerified: '1',
        isSuspended: '0',
      },
    });
    const initialToken = registerBody.data?.createCustomer?.customer?.token;
    expect(initialToken).toBeTruthy();

    const updateBody = await gql(
      request,
      CREATE_CUSTOMER_PROFILE_UPDATE,
      {
        input: {
          firstName: 'Pwd',
          lastName: 'WrongCurrent',
          currentPassword: 'TotallyWrongPassword!999',
          password: newPassword,
          confirmPassword: newPassword,
        },
      },
      { Authorization: `Bearer ${initialToken}` }
    );

    expect(updateBody.data?.createCustomerProfileUpdate ?? null).toBeNull();
    expect(graphQLErrorMessages(updateBody)).toEqual([MSG.CURRENT_PASSWORD_INCORRECT]);

    const reloginBody = await gql(request, CUSTOMER_LOGIN, { email, password: oldPassword });
    expect(reloginBody.data?.createCustomerLogin?.customerLogin?.token).toBeTruthy();
  });
});
