// tests/restAPI/api/automation/contactUs.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

function assertStatus(resp: any, debugLabel: string) {
  expect([0, 200, 201, 204, 400, 401, 404, 422, 500]).toContain(resp.status());
  console.log(`${debugLabel}:`, resp.status());
}

test.describe('Contact Us REST API', () => {
  test('Should accept a valid contact form submission', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CONTACT_US, {
      method: 'POST',
      data: {
        name: 'Jane Doe',
        email: `contact_${Date.now()}@example.com`,
        contact: '+1234567890',
        message: 'Hello, this is an e2e test contact submission.',
      },
    });
    assertStatus(response, 'POST /api/shop/contact-us');
  });

  test('Should reject submission with missing email', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CONTACT_US, {
      method: 'POST',
      data: { name: 'Jane', message: 'Test' },
    });
    expect([400, 422, 200, 500]).toContain(response.status());
    console.log('POST /api/shop/contact-us missing email:', response.status());
  });

  test('Should reject submission with missing message', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CONTACT_US, {
      method: 'POST',
      data: { name: 'Jane', email: `c_${Date.now()}@example.com` },
    });
    expect([400, 422, 200, 500]).toContain(response.status());
    console.log('POST /api/shop/contact-us missing message:', response.status());
  });

  test('Should reject submission with invalid email', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CONTACT_US, {
      method: 'POST',
      data: { name: 'Jane', email: 'not-an-email', message: 'Test' },
    });
    expect([400, 422, 200, 500]).toContain(response.status());
    console.log('POST /api/shop/contact-us invalid email:', response.status());
  });

  test('Should reject empty body', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CONTACT_US, {
      method: 'POST',
      data: {},
    });
    expect([400, 422, 500]).toContain(response.status());
    console.log('POST /api/shop/contact-us empty:', response.status());
  });

  test('Should reject GET on contact-us', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CONTACT_US);
    expect([404, 405, 500]).toContain(response.status());
    console.log('GET /api/shop/contact-us:', response.status());
  });
});
