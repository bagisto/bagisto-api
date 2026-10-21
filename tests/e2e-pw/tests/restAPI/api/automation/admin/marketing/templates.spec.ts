import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_MARKETING } from '../../../../rest/endpoints/admin.marketing.endpoints';
import { UNSAFE_HTML, assertSanitisedHtml } from '../../../../rest/assertions/adminSanitisedHtml.assertions';

test.describe.configure({ timeout: 60_000 });

function uniqueStamp(): string {
  return `${Date.now()}-${Math.floor(Math.random() * 1_000_000)}`;
}

test.describe('admin marketing email templates', () => {
  test('listing returns 200 and envelope', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.TEMPLATES);
    expect(resp.status()).toBe(200);
    expect(Array.isArray((await resp.json()).data)).toBe(true);
  });

  test('listing with pagination', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.TEMPLATES, {
      params: { page: '1', per_page: '5' },
    });
    expect(resp.status()).toBe(200);
    expect((await resp.json()).meta.perPage).toBe(5);
  });

  test('listing with name filter', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.TEMPLATES, {
      params: { name: 'nonexistent-zzz' },
    });
    expect(resp.status()).toBe(200);
  });

  test('detail non-existent returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.TEMPLATE(99999999));
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe('Email template not found.');
  });

  test('create with empty body returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.TEMPLATES, {
      method: 'POST',
      data: {},
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The name field is required.');
  });

  test('create detail update and delete round trip', async ({ request }) => {
    const ts = uniqueStamp();
    const createResp = await sendAdminRequest(request, ADMIN_MARKETING.TEMPLATES, {
      method: 'POST',
      data: {
        name: `E2E Tpl ${ts}`,
        status: 'active',
        content: '<p>Hello e2e</p>',
      },
    });
    expect(createResp.status()).toBe(201);
    const created = await createResp.json();
    expect(created).toEqual(expect.objectContaining({ name: `E2E Tpl ${ts}`, status: 'active', content: '<p>Hello e2e</p>' }));
    const id = created.id;

    const detail = await sendAdminRequest(request, ADMIN_MARKETING.TEMPLATE(id));
    expect(detail.status()).toBe(200);
    expect((await detail.json()).name).toBe(`E2E Tpl ${ts}`);

    const updateResp = await sendAdminRequest(request, ADMIN_MARKETING.TEMPLATE(id), {
      method: 'PUT' as any,
      data: {
        name: `E2E Tpl ${ts} updated`,
        status: 'inactive',
        content: '<p>updated</p>',
      },
    });
    expect(updateResp.status()).toBe(200);
    expect(await updateResp.json()).toEqual(expect.objectContaining({ id, name: `E2E Tpl ${ts} updated`, status: 'inactive' }));

    const reread = await sendAdminRequest(request, ADMIN_MARKETING.TEMPLATE(id));
    expect((await reread.json()).content).toBe('<p>updated</p>');

    const delResp = await sendAdminRequest(request, ADMIN_MARKETING.TEMPLATE(id), {
      method: 'DELETE',
    });
    expect(delResp.status()).toBe(200);
    expect((await delResp.json()).message).toBe('Email template deleted successfully.');

    const gone = await sendAdminRequest(request, ADMIN_MARKETING.TEMPLATE(id));
    expect(gone.status()).toBe(404);
  });

  test('create with invalid status returns 422', async ({ request }) => {
    const name = `E2E Tpl ${uniqueStamp()}`;
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.TEMPLATES, {
      method: 'POST',
      data: { name, status: 'banana', content: '<p>x</p>' },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The selected status is invalid.');
  });

  test('should strip scripts and template code from email template content on create and update', async ({ request }) => {
    const name = `E2E Sanitised Tpl ${Date.now()}-${Math.floor(Math.random() * 100000)}`;
    const createResp = await sendAdminRequest(request, ADMIN_MARKETING.TEMPLATES, {
      method: 'POST',
      data: { name, status: 'active', content: UNSAFE_HTML },
    });
    expect(createResp.status()).toBe(201);
    const id = (await createResp.json()).id;
    expect(typeof id).toBe('number');

    try {
      const created = await sendAdminRequest(request, ADMIN_MARKETING.TEMPLATE(id));
      expect(created.status()).toBe(200);
      assertSanitisedHtml((await created.json()).content);

      const upd = await sendAdminRequest(request, ADMIN_MARKETING.TEMPLATE(id), {
        method: 'PUT',
        data: { name: `${name} updated`, status: 'inactive', content: `${UNSAFE_HTML}<p>Updated</p>` },
      });
      expect(upd.status()).toBe(200);

      const updated = await sendAdminRequest(request, ADMIN_MARKETING.TEMPLATE(id));
      expect(updated.status()).toBe(200);
      const updatedBody = await updated.json();
      assertSanitisedHtml(updatedBody.content);
      expect(updatedBody.content).toContain('<p>Updated</p>');
    } finally {
      await sendAdminRequest(request, ADMIN_MARKETING.TEMPLATE(id), { method: 'DELETE' });
    }
  });
});
