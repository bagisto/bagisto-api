import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { sendRestRequest } from '../../../../rest/helpers/restClient';
import { ADMIN_CUSTOMERS } from '../../../../rest/endpoints/admin.customers.endpoints';
import { ENDPOINTS } from '../../../../rest/endpoints/endpoints';

test.describe.configure({ timeout: 120_000 });

interface SignedInCustomer {
  id: number;
  accessToken: string;
  sessionId: number;
}

async function signedInCustomer(request: APIRequestContext, sessions = 1): Promise<SignedInCustomer[]> {
  const suffix = `${Date.now()}${Math.floor(Math.random() * 100000)}`;
  const credentials = { email: `admin.sessions.${suffix}@example.com`, password: `Sess${suffix}!Aa` };

  const register = await sendRestRequest(request, ENDPOINTS.CUSTOMER_REGISTER, {
    method: 'POST',
    data: { firstName: 'Admin', lastName: 'Sessions', ...credentials, confirmPassword: credentials.password },
  });

  expect(register.status(), await register.text()).toBe(201);

  const signedIn: SignedInCustomer[] = [];

  for (let i = 0; i < sessions; i++) {
    const login = await sendRestRequest(request, ENDPOINTS.CUSTOMER_LOGIN, {
      method: 'POST',
      data: { ...credentials, device_name: `Device ${i}` },
    });
    const body = await login.json();

    expect(login.status(), JSON.stringify(body)).toBe(201);

    signedIn.push({ id: Number(body.customer.id), accessToken: body.accessToken, sessionId: Number(body.session.id) });
  }

  return signedIn;
}

async function profileStatus(request: APIRequestContext, accessToken: string): Promise<number> {
  return (await sendRestRequest(request, ENDPOINTS.CUSTOMER_PROFILE, {
    headers: { Authorization: `Bearer ${accessToken}` },
  })).status();
}

test.describe('admin customer api sessions rest api', () => {
  test('lists a customer\'s live sessions', async ({ request }) => {
    const [first, second] = await signedInCustomer(request, 2);

    const response = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER_API_SESSIONS(first.id));
    const body = await response.json();

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body.data.map((row: any) => Number(row.id))).toEqual(expect.arrayContaining([first.sessionId, second.sessionId]));
    expect(Number(body.data[0].customerId)).toBe(first.id);

    const all = await sendAdminRequest(request, ADMIN_CUSTOMERS.API_SESSIONS, { params: { customer_id: String(first.id) } });

    expect(all.status()).toBe(200);
    expect((await all.json()).data.length).toBe(3);
  });

  test('ends one session, whose access token stops working at once', async ({ request }) => {
    const [lost, kept] = await signedInCustomer(request, 2);

    const response = await sendAdminRequest(request, ADMIN_CUSTOMERS.API_SESSION(lost.sessionId), { method: 'DELETE' });

    expect(response.status()).toBe(204);
    expect(await profileStatus(request, lost.accessToken)).toBe(401);
    expect(await profileStatus(request, kept.accessToken)).toBe(200);

    const again = await sendAdminRequest(request, ADMIN_CUSTOMERS.API_SESSION(lost.sessionId), { method: 'DELETE' });

    expect(again.status()).toBe(404);
  });

  test('ends every session of a customer', async ({ request }) => {
    const sessions = await signedInCustomer(request, 2);

    const response = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER_REVOKE_API_SESSIONS(sessions[0].id), {
      method: 'POST',
      data: {},
    });
    const body = await response.json();

    expect(response.status(), JSON.stringify(body)).toBeLessThan(300);
    expect(body.revoked).toBe(3);

    for (const session of sessions) {
      expect(await profileStatus(request, session.accessToken)).toBe(401);
    }
  });

  test('an unknown customer is not found', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER_API_SESSIONS(999999999));

    expect(response.status()).toBe(404);
  });
});
