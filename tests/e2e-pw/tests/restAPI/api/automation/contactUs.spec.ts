import { test, expect, APIResponse } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

const MSG = {
  SUCCESS: 'Your inquiry has been submitted successfully. We will get back to you soon',
  FAILED: 'Unable to send your inquiry at this time. Please try again later',
  NAME_REQUIRED: 'The name field is required.',
  EMAIL_REQUIRED: 'The email field is required.',
  EMAIL_INVALID: 'The email field must be a valid email address.',
  MESSAGE_REQUIRED: 'The message field is required.',
};

async function expectRejected(response: APIResponse, detail: string): Promise<void> {
  const body = await response.json();

  expect(response.status(), JSON.stringify(body)).toBe(400);
  expect(body.title).toBe('Bad Request');
  expect(body.detail).toBe(detail);
}

test.describe('contact us rest api', () => {
  test('should accept a valid contact form submission', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CONTACT_US, {
      method: 'POST',
      data: {
        name: 'Jane Doe',
        email: `contact_${Date.now()}@example.com`,
        contact: '+1234567890',
        message: 'Hello, this is an e2e test contact submission.',
      },
    });
    const body = await response.json();

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(Object.keys(body).sort()).toEqual(['message', 'success']);
    expect(body.message).toBe(body.success ? MSG.SUCCESS : MSG.FAILED);

    test.skip(!body.success, 'the mail transport refused the inquiry, so only the failure envelope could be checked');

    expect(body).toEqual({ success: true, message: MSG.SUCCESS });
  });

  test('should reject submission with missing email', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CONTACT_US, {
      method: 'POST',
      data: { name: 'Jane', message: 'Test' },
    });

    await expectRejected(response, MSG.EMAIL_REQUIRED);
  });

  test('should reject submission with missing message', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CONTACT_US, {
      method: 'POST',
      data: { name: 'Jane', email: `c_${Date.now()}@example.com` },
    });

    await expectRejected(response, MSG.MESSAGE_REQUIRED);
  });

  test('should reject submission with invalid email', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CONTACT_US, {
      method: 'POST',
      data: { name: 'Jane', email: 'not-an-email', message: 'Test' },
    });

    await expectRejected(response, MSG.EMAIL_INVALID);
  });

  test('should reject empty body', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CONTACT_US, {
      method: 'POST',
      data: {},
    });

    await expectRejected(response, [MSG.NAME_REQUIRED, MSG.EMAIL_REQUIRED, MSG.MESSAGE_REQUIRED].join(' '));
  });

  test('should reject get on contact-us', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CONTACT_US);

    expect(response.status()).toBe(404);
  });
});
