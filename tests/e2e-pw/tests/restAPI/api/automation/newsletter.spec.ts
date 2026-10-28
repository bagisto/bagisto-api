import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import { expectProblem, readJson, uniqueSuffix } from '../../rest/helpers/coverage/shopCart.helpers';

const MSG = {
  SUBSCRIBED: 'You have successfully subscribed to our newsletter.',
  ALREADY_SUBSCRIBED: 'You are already subscribed to our newsletter.',
  EMAIL_REQUIRED: 'The customer email field is required.',
  EMAIL_INVALID: 'The customer email field must be a valid email address.',
};

function uniqueEmail(): string {
  return `newsletter.${uniqueSuffix()}@example.com`;
}

test.describe('newsletter subscribe rest api', () => {
  test('a new email is subscribed', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.NEWSLETTER_SUBSCRIBE, {
      method: 'POST',
      data: { customerEmail: uniqueEmail() },
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.success).toBe(true);
    expect(body.message).toBe(MSG.SUBSCRIBED);
  });

  test('a second subscription of the same email is rejected as already subscribed', async ({ request }) => {
    const customerEmail = uniqueEmail();

    const first = await sendRestRequest(request, ENDPOINTS.NEWSLETTER_SUBSCRIBE, {
      method: 'POST',
      data: { customerEmail },
    });
    expect(first.status()).toBe(201);

    const second = await sendRestRequest(request, ENDPOINTS.NEWSLETTER_SUBSCRIBE, {
      method: 'POST',
      data: { customerEmail },
    });

    await expectProblem(second, 400, MSG.ALREADY_SUBSCRIBED);
  });

  test('an invalid email is rejected', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.NEWSLETTER_SUBSCRIBE, {
      method: 'POST',
      data: { customerEmail: 'not-an-email' },
    });

    await expectProblem(response, 400, MSG.EMAIL_INVALID);
  });

  test('a missing email is rejected', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.NEWSLETTER_SUBSCRIBE, {
      method: 'POST',
      data: {},
    });

    await expectProblem(response, 400, MSG.EMAIL_REQUIRED);
  });

  test('an empty email string is rejected', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.NEWSLETTER_SUBSCRIBE, {
      method: 'POST',
      data: { customerEmail: '' },
    });

    await expectProblem(response, 400, MSG.EMAIL_REQUIRED);
  });
});
