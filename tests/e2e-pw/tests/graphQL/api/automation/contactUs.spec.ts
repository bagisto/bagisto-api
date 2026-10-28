import { test, expect } from '@playwright/test';
import { CREATE_CONTACT_US } from '../../graphql/Queries/contactUs.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../graphql/helpers/testSupport';

const CONTACT_SUCCESS = 'Your inquiry has been submitted successfully. We will get back to you soon';
const CONTACT_MAIL_FAILED = 'Unable to send your inquiry at this time. Please try again later';

function contactInput(overrides: Record<string, any> = {}): Record<string, any> {
  return {
    name: 'Playwright Tester',
    email: `playwright.contact+${Date.now()}-${Math.floor(Math.random() * 100000)}@example.com`,
    contact: '+1234567890',
    message: 'Testing the Bagisto contact us GraphQL mutation.',
    ...overrides,
  };
}

test.describe('contact us graphql api tests', () => {
  test('should accept a valid inquiry and report the mail hand-off result', async ({ request }) => {
    const response = await sendGraphQLRequest(request, CREATE_CONTACT_US, {
      input: { ...contactInput(), clientMutationId: 'contact-form-001' },
    });
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(body.errors, graphQLErrorMessages(body).join(' | ')).toBeUndefined();

    const payload = body.data.createContactUs;
    expect(payload.clientMutationId).toBe('contact-form-001');
    expect(typeof payload.contactUs.success).toBe('boolean');
    expect(payload.contactUs.message).toBe(payload.contactUs.success ? CONTACT_SUCCESS : CONTACT_MAIL_FAILED);
  });

  test('should reject an inquiry whose email is not an email address', async ({ request }) => {
    const response = await sendGraphQLRequest(request, CREATE_CONTACT_US, {
      input: contactInput({ email: 'not-an-email' }),
    });
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(body.data?.createContactUs ?? null).toBeNull();
    expect(graphQLErrorMessages(body)).toEqual(['The email field must be a valid email address.']);
  });

  test('should reject an inquiry without a name or a message', async ({ request }) => {
    const response = await sendGraphQLRequest(request, CREATE_CONTACT_US, {
      input: { email: `playwright.contact+${Date.now()}@example.com` },
    });
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(body.data?.createContactUs ?? null).toBeNull();
    expect(graphQLErrorMessages(body)).toEqual(['The name field is required. The message field is required.']);
  });
});
