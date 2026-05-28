// tests/restAPI/api/automation/admin/auth.spec.ts
//
// Admin Auth REST API — post 2026-05-27 refactor.
// Login / Logout / ForgotPassword / ProfileUpdate were removed.
// Only the profile-read endpoint (`GET /api/admin/get`) remains. The new test
// "request without Bearer token is rejected" proves the auth gate works.

import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../rest/helpers/adminClient';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import { ENDPOINTS } from '../../../rest/endpoints/endpoints';
import { env } from '../../../config/env';

test.describe.configure({ timeout: 60_000 });

test.describe('Admin Auth REST API', () => {
  test('profile read returns the authenticated admin', async ({ request }) => {
    const response = await sendAdminRequest(request, ENDPOINTS.ADMIN_PROFILE);

    const status = response.status();
    console.log('profile read:', status);
    expect(status).toBe(200);

    const body = await response.json();
    // GET /api/admin/get returns
    // [{ id, name, email, image, status, roleId, roleName, success, message }].
    const admin = Array.isArray(body) ? body[0] : (body?.email ? body : (body?.data ?? body));
    expect(typeof admin.email).toBe('string');
    expect(admin.email.length).toBeGreaterThan(0);
    expect(admin.email).toBe(env.adminEmail);
  });

  test('profile read without Bearer token returns 401', async ({ request }) => {
    // Bypass sendAdminRequest so no Bearer header is injected.
    const response = await sendRestRequest(request, ENDPOINTS.ADMIN_PROFILE, {
      method: 'GET',
    });
    const status = response.status();
    console.log('profile no bearer:', status);
    expect([401, 403]).toContain(status);
  });

  test('profile read with garbage Bearer token returns 401', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.ADMIN_PROFILE, {
      method: 'GET',
      headers: { 'Authorization': 'Bearer this-is-not-a-real-token' },
    });
    const status = response.status();
    console.log('profile garbage bearer:', status);
    expect([401, 403]).toContain(status);
  });

  test('removed login endpoint is no longer routable', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.ADMIN_LOGIN, {
      method: 'POST',
      data: { email: env.adminEmail, password: 'whatever' },
    });
    const status = response.status();
    console.log('login removed:', status);
    // 404 / 405 / 500 — see CLAUDE.md "Admin authentication refactor" notes.
    // Bagisto's exception handler maps unknown API routes to 500 with an HTML
    // body rather than emitting a plain 404. Either way, the endpoint is unreachable.
    expect([404, 405, 500]).toContain(status);
  });

  test('admin endpoint accepts Bearer token without X-Admin-Key', async ({ request }) => {
    // Hit a real admin data endpoint to prove the X-Admin-Key gate was
    // actually removed (not just the auth middleware).
    const response = await sendAdminRequest(request, '/api/admin/customers', {
      method: 'GET',
      params: { per_page: '1' },
    });
    const status = response.status();
    console.log('admin/customers (Bearer only):', status);
    expect(status).toBe(200);
  });
});
