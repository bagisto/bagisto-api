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

export async function getCustomerAuthHeaders(
  request: APIRequestContext
): Promise<Record<string, string> | null> {
  if (!env.customerEmail || !env.customerPassword) {
    return null;
  }

  const response = await sendGraphQLRequest(request, CUSTOMER_LOGIN, {
    email: env.customerEmail,
    password: env.customerPassword,
  });

  expect(response.status()).toBe(200);

  const body = await response.json();
  const token = body.data?.createCustomerLogin?.customerLogin?.token;

  if (!token) {
    return null;
  }

  return {
    Authorization: `Bearer ${token}`,
  };
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
