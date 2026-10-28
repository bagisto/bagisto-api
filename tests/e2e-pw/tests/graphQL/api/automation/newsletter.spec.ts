import { test, expect, APIRequestContext } from '@playwright/test';
import { getCustomerAuthHeaders } from '../../config/auth';
import { CREATE_NEWSLETTER } from '../../graphql/Queries/newsletter.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../graphql/helpers/testSupport';

const MESSAGES = {
  SUBSCRIBED: 'You have successfully subscribed to our newsletter.',
  ALREADY_SUBSCRIBED: 'You are already subscribed to our newsletter.',
  EMAIL_REQUIRED: 'The customer email field is required.',
  EMAIL_INVALID: 'The customer email field must be a valid email address.',
};

function uniqueEmail(prefix: string): string {
  return `${prefix}.${Date.now()}.${Math.floor(Math.random() * 1_000_000)}@example.com`;
}

async function subscribe(
  request: APIRequestContext,
  customerEmail: string,
  headers: Record<string, string> = {}
): Promise<any> {
  const response = await sendGraphQLRequest(request, CREATE_NEWSLETTER, { input: { customerEmail } }, headers);

  expect(response.status()).toBe(200);

  return response.json();
}

function expectSubscribed(body: any): void {
  expect(body.errors, graphQLErrorMessages(body).join(' | ')).toBeUndefined();
  expect(body.data.createNewsletter.newsletter).toEqual({ success: true, message: MESSAGES.SUBSCRIBED });
}

function expectRefused(body: any, message: string): void {
  expect(graphQLErrorMessages(body)).toEqual([message]);
  expect(body.data?.createNewsletter ?? null).toBeNull();
}

test.describe('newsletter graphql api tests', () => {
  test('should subscribe a fresh email for an authenticated customer', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);

    expectSubscribed(await subscribe(request, uniqueEmail('playwright.newsletter'), headers));
  });

  test('should refuse a second subscription of the same email', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);
    const email = uniqueEmail('playwright.newsletter.dup');

    expectSubscribed(await subscribe(request, email, headers));

    expectRefused(await subscribe(request, email, headers), MESSAGES.ALREADY_SUBSCRIBED);
  });

  test('should reject a malformed email', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);

    expectRefused(await subscribe(request, 'not-an-email', headers), MESSAGES.EMAIL_INVALID);
  });

  test('should subscribe a guest email without a bearer token', async ({ request }) => {
    const email = uniqueEmail('playwright.newsletter.guest');

    expectSubscribed(await subscribe(request, email));

    expectRefused(await subscribe(request, email), MESSAGES.ALREADY_SUBSCRIBED);
  });

  test('should reject an empty email', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);

    expectRefused(await subscribe(request, '', headers), MESSAGES.EMAIL_REQUIRED);
  });
});
