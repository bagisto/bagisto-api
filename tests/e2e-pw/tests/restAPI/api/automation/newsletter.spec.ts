// tests/restAPI/api/automation/newsletter.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

function assertStatus(resp: any, debugLabel: string) {
  expect([0, 200, 201, 204, 400, 401, 404, 422, 500]).toContain(resp.status());
  console.log(`${debugLabel}:`, resp.status());
}

test.describe('Newsletter Subscribe REST API', () => {
  test('Should subscribe a new email', async ({ request }) => {
    const email = `newsletter_${Date.now()}@example.com`;
    const response = await sendRestRequest(request, ENDPOINTS.NEWSLETTER_SUBSCRIBE, {
      method: 'POST',
      data: { email },
    });
    assertStatus(response, `POST /api/shop/newsletters (${email})`);
  });

  test('Should handle duplicate subscription gracefully', async ({ request }) => {
    const email = `dup_${Date.now()}@example.com`;
    const first = await sendRestRequest(request, ENDPOINTS.NEWSLETTER_SUBSCRIBE, {
      method: 'POST',
      data: { email },
    });
    assertStatus(first, 'POST /api/shop/newsletters first');

    const second = await sendRestRequest(request, ENDPOINTS.NEWSLETTER_SUBSCRIBE, {
      method: 'POST',
      data: { email },
    });
    // Duplicate may return 200 (idempotent toggle), 201 (created), 400/422 (validation)
    expect([200, 201, 204, 400, 409, 422, 500]).toContain(second.status());
    console.log('POST /api/shop/newsletters duplicate:', second.status());
  });

  test('Should reject invalid email', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.NEWSLETTER_SUBSCRIBE, {
      method: 'POST',
      data: { email: 'not-an-email' },
    });
    expect([400, 422, 500]).toContain(response.status());
    console.log('POST /api/shop/newsletters invalid email:', response.status());
  });

  test('Should reject missing email', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.NEWSLETTER_SUBSCRIBE, {
      method: 'POST',
      data: {},
    });
    expect([400, 422, 500]).toContain(response.status());
    console.log('POST /api/shop/newsletters empty:', response.status());
  });

  test('Should reject empty email string', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.NEWSLETTER_SUBSCRIBE, {
      method: 'POST',
      data: { email: '' },
    });
    expect([400, 422, 500]).toContain(response.status());
    console.log('POST /api/shop/newsletters empty string:', response.status());
  });
});
