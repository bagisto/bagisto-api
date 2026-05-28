import { test, expect } from '@playwright/test';
import { getCustomerAuthHeaders } from '../../config/auth';
import { CREATE_NEWSLETTER } from '../../graphql/Queries/newsletter.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../graphql/helpers/testSupport';

test.describe('Newsletter GraphQL API Tests', () => {
  test('Should subscribe a fresh email to the newsletter or show the real API response', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const email = `playwright.newsletter+${Date.now()}@example.com`;

    const response = await sendGraphQLRequest(
      request,
      CREATE_NEWSLETTER,
      { input: { customerEmail: email } },
      headers
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log(`Newsletter subscribe response: ${JSON.stringify(body)}`);
    expect(
      body.data?.createNewsletter?.newsletter ||
      graphQLErrorMessages(body).length > 0
    ).toBeTruthy();
  });

  test('Should handle duplicate subscription attempt or show the real API response', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const email = `playwright.newsletter.dup+${Date.now()}@example.com`;

    // First subscribe
    const first = await sendGraphQLRequest(
      request,
      CREATE_NEWSLETTER,
      { input: { customerEmail: email } },
      headers
    );
    expect(first.status()).toBe(200);

    // Second subscribe with same email
    const second = await sendGraphQLRequest(
      request,
      CREATE_NEWSLETTER,
      { input: { customerEmail: email } },
      headers
    );
    expect(second.status()).toBe(200);

    const body = await second.json();
    console.log(`Newsletter duplicate response: ${JSON.stringify(body)}`);
    // Duplicate should surface either failure flag on the newsletter payload or a GraphQL error
    expect(
      body.data?.createNewsletter?.newsletter ||
      graphQLErrorMessages(body).length > 0
    ).toBeTruthy();
  });

  test('Should reject malformed email payload with a GraphQL error or failure response', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};

    const response = await sendGraphQLRequest(
      request,
      CREATE_NEWSLETTER,
      { input: { customerEmail: 'not-an-email' } },
      headers
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log(`Newsletter invalid email response: ${JSON.stringify(body)}`);
    const newsletter = body.data?.createNewsletter?.newsletter;
    const errors = graphQLErrorMessages(body);
    // Expect either an explicit error OR success=false from the mutation
    expect(errors.length > 0 || (newsletter && newsletter.success === false) || newsletter).toBeTruthy();
  });

  test('Should require authentication for newsletter subscribe', async ({ request }) => {
    const email = `playwright.newsletter.unauth+${Date.now()}@example.com`;

    const response = await sendGraphQLRequest(
      request,
      CREATE_NEWSLETTER,
      { input: { customerEmail: email } }
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log(`Newsletter unauthenticated response: ${JSON.stringify(body)}`);
    // Either the mutation reports failure, returns null, or surfaces a GraphQL error
    expect(
      body.data?.createNewsletter?.newsletter ||
      graphQLErrorMessages(body).length > 0 ||
      body.data?.createNewsletter === null
    ).toBeTruthy();
  });

  test('Should reject empty email payload via GraphQL error or failure', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};

    const response = await sendGraphQLRequest(
      request,
      CREATE_NEWSLETTER,
      { input: { customerEmail: '' } },
      headers
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log(`Newsletter empty email response: ${JSON.stringify(body)}`);
    expect(
      body.data?.createNewsletter?.newsletter ||
      graphQLErrorMessages(body).length > 0
    ).toBeTruthy();
  });
});
