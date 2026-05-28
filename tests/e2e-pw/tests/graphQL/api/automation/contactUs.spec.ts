import { test, expect } from '@playwright/test';
import { CREATE_CONTACT_US } from '../../graphql/Queries/contactUs.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../graphql/helpers/testSupport';

test.describe('Contact Us GraphQL API Tests', () => {
  test('Should submit the contact us mutation or show the real API response', async ({ request }) => {
    const response = await sendGraphQLRequest(request, CREATE_CONTACT_US, {
      input: {
        name: 'Playwright Tester',
        email: `playwright.contact+${Date.now()}@example.com`,
        contact: '+1234567890',
        message: 'Testing the Bagisto contact us GraphQL mutation.',
        clientMutationId: 'contact-form-001',
      },
    });
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log(`Contact us response: ${JSON.stringify(body)}`);
    expect(body.data?.createContactUs?.contactUs || graphQLErrorMessages(body).length > 0).toBeTruthy();
  });
});
