// tests/restAPI/api/automation/admin/customers/customerReviews.spec.ts
//
// Admin Customer Reviews (W2) — moderation-only. No create endpoint
// (reviews originate from storefront). We rely on existing seeded reviews in
// the dev DB for read/update/delete tests, skip cleanly if none present.

import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_CUSTOMERS } from '../../../../rest/endpoints/admin.customers.endpoints';

test.describe.configure({ timeout: 60_000 });

async function pickReviewId(request: APIRequestContext): Promise<number | null> {
  const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.REVIEWS, {
    params: { per_page: '1' },
  });
  const body = await resp.json();
  if (!body.data || body.data.length === 0) return null;
  return body.data[0].id;
}

test.describe('Admin Customer Reviews REST API', () => {
  test('list returns envelope with reviews', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.REVIEWS, {
      params: { per_page: '3' },
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(Array.isArray(body.data)).toBe(true);
    expect(body.meta).toBeDefined();
  });

  test('list filter by status=approved is accepted', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.REVIEWS, {
      params: { status: 'approved' },
    });
    expect(resp.status()).toBe(200);
  });

  test('list filter by rating is accepted', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.REVIEWS, {
      params: { rating: '5' },
    });
    expect(resp.status()).toBe(200);
  });

  test('detail returns 200 for an existing review', async ({ request }) => {
    const id = await pickReviewId(request);
    if (id === null) test.skip(true, 'no reviews in dev DB');
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.REVIEW(id!));
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.id).toBe(id);
  });

  test('detail returns 404 for unknown id', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.REVIEW(99999999));
    expect(resp.status()).toBe(404);
  });

  test('update status to pending then back', async ({ request }) => {
    const id = await pickReviewId(request);
    if (id === null) test.skip(true, 'no reviews in dev DB');

    // Read original status to restore after.
    const before = await sendAdminRequest(request, ADMIN_CUSTOMERS.REVIEW(id!));
    const originalStatus = String((await before.json()).status);

    const upd = await sendAdminRequest(request, ADMIN_CUSTOMERS.REVIEW(id!), {
      method: 'PUT' as any,
      data: { status: 'pending' },
    });
    expect([200, 201]).toContain(upd.status());

    // Restore.
    if (['pending', 'approved', 'disapproved'].includes(originalStatus)) {
      await sendAdminRequest(request, ADMIN_CUSTOMERS.REVIEW(id!), {
        method: 'PUT' as any,
        data: { status: originalStatus },
      });
    }
  });

  test('update rejects invalid status with 422', async ({ request }) => {
    const id = await pickReviewId(request);
    if (id === null) test.skip(true, 'no reviews in dev DB');

    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.REVIEW(id!), {
      method: 'PUT' as any,
      data: { status: 'completely-bogus' },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('mass-update-status rejects empty indices', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.REVIEWS_MASS_UPDATE_STATUS, {
      method: 'POST',
      data: { indices: [], value: 'pending' },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('mass-update-status rejects invalid value', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.REVIEWS_MASS_UPDATE_STATUS, {
      method: 'POST',
      data: { indices: [1], value: 'bogus' },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('mass-delete rejects empty indices', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.REVIEWS_MASS_DELETE, {
      method: 'POST',
      data: { indices: [] },
    });
    expect([400, 422]).toContain(resp.status());
  });
});
