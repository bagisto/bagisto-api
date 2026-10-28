import { test, expect } from '@playwright/test';
import { env } from '../../../config/env';

test.describe.configure({ timeout: 60_000 });

const HTML_HEADERS = { 'Accept': 'text/html' };

test.describe('admin api documentation pages', () => {
  test('should serve the admin graphiql playground as html pointing at the admin graphql endpoint', async ({ request }) => {
    const resp = await request.get(`${env.baseUrl}/api/admin/graphiql`, { headers: HTML_HEADERS });
    expect(resp.status()).toBe(200);
    expect(resp.headers()['content-type']).toContain('text/html');

    const html = await resp.text();
    expect(html).toContain('<!DOCTYPE html>');
    expect(html).toContain('<title>GraphQL - Admin API Platform</title>');
    expect(html.replace(/\\\//g, '/')).toContain('"entrypoint":"/api/admin/graphql"');
  });

  test('should serve the api documentation page as html', async ({ request }) => {
    const resp = await request.get(`${env.baseUrl}/api/docs`, { headers: HTML_HEADERS });
    expect(resp.status()).toBe(200);
    expect(resp.headers()['content-type']).toContain('text/html');
    expect((await resp.text()).toLowerCase()).toContain('<!doctype html>');
  });

  test('should keep serving the admin openapi document as json', async ({ request }) => {
    const resp = await request.get(`${env.baseUrl}/api/admin/docs`);
    expect(resp.status()).toBe(200);
    expect(resp.headers()['content-type']).toContain('application/json');
    expect(await resp.json()).toHaveProperty('paths');
  });
});
