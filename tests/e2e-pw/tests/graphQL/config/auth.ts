import { APIRequestContext, expect } from '@playwright/test';
import { env } from './env';
import { sendGraphQLRequest } from '../graphql/helpers/graphqlClient';

const CUSTOMER_LOGIN = `
  mutation createCustomerLogin($email: String!, $password: String!) {
    createCustomerLogin(input: { email: $email, password: $password }) {
      customerLogin {
        token
        success
        message
      }
    }
  }
`;

const REGISTER_CUSTOMER = `
  mutation registerCustomer($input: createCustomerInput!) {
    createCustomer(input: $input) {
      customer {
        id
        _id
        token
        email
      }
    }
  }
`;

const CREATE_CART_TOKEN = `
  mutation createCart {
    createCartToken(input: {}) {
      cartToken {
        sessionToken
        cartToken
        isGuest
        success
        message
      }
    }
  }
`;

async function registerAndLoginCustomer(request: APIRequestContext): Promise<string> {
  const uniqueSuffix = `${Date.now()}-${Math.floor(Math.random() * 100000)}`;
  const email = `playwright.customer.${uniqueSuffix}@playwrighttest.local`;
  const password = 'Passw0rd!123';

  const registerResponse = await sendGraphQLRequest(request, REGISTER_CUSTOMER, {
    input: {
      firstName: 'Playwright',
      lastName: 'Tester',
      email,
      password,
      status: '1',
      subscribedToNewsLetter: false,
      isVerified: '1',
      isSuspended: '0',
    },
  });
  expect(registerResponse.status()).toBe(200);

  const registerBody = await registerResponse.json();
  const registerToken = registerBody.data?.createCustomer?.customer?.token;
  if (typeof registerToken === 'string' && registerToken.length > 0) {
    return registerToken;
  }

  const loginResponse = await sendGraphQLRequest(request, CUSTOMER_LOGIN, { email, password });
  expect(loginResponse.status()).toBe(200);

  const loginBody = await loginResponse.json();
  const loginToken = loginBody.data?.createCustomerLogin?.customerLogin?.token;
  expect(loginToken, `failed to obtain customer token: ${JSON.stringify(registerBody)}`).toBeTruthy();
  return loginToken as string;
}

export async function getCustomerAuthHeaders(
  request: APIRequestContext
): Promise<Record<string, string>> {
  if (env.customerEmail && env.customerPassword) {
    const response = await sendGraphQLRequest(request, CUSTOMER_LOGIN, {
      email: env.customerEmail,
      password: env.customerPassword,
    });
    expect(response.status()).toBe(200);

    const body = await response.json();
    const token = body.data?.createCustomerLogin?.customerLogin?.token;
    if (typeof token === 'string' && token.length > 0) {
      return { Authorization: `Bearer ${token}` };
    }
  }

  const token = await registerAndLoginCustomer(request);
  return { Authorization: `Bearer ${token}` };
}

export async function getGuestCartHeaders(
  request: APIRequestContext
): Promise<Record<string, string>> {
  const response = await sendGraphQLRequest(request, CREATE_CART_TOKEN);
  expect(response.status()).toBe(200);

  const body = await response.json();
  const token =
    body.data?.createCartToken?.cartToken?.sessionToken ??
    body.data?.createCartToken?.cartToken?.cartToken;

  expect(token).toBeTruthy();

  return {
    Authorization: `Bearer ${token}`,
  };
}
