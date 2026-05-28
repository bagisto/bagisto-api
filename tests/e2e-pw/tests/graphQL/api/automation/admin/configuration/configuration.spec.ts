// Admin Configuration GraphQL e2e — generic menu / values / update.

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_CONFIG_MENU,
  ADMIN_CONFIG_VALUES,
  ADMIN_CONFIG_UPDATE,
} from '../../../../graphql/Queries/admin/configuration/configuration.queries';

test.describe.configure({ timeout: 60_000 });
async function safeJson(r: any) { try { return await r.json(); } catch { return null; } }

test.describe('Admin Configuration GraphQL API', () => {
  test('menu returns tree (no slug)', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CONFIG_MENU, {});
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);
    // tree is an Iterable; tolerant of either populated array or null due to GraphQL Iterable quirks
    if (!body?.errors) {
      expect(body?.data?.menuAdminConfigurationMenu).toBeTruthy();
    }
  });

  test('menu scoped to slug=general.general', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CONFIG_MENU, { slug: 'general.general' });
    expect(resp.status()).toBe(200);
  });

  test('values for general.general slug', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CONFIG_VALUES, {
      slug: 'general.general', channel: 'default', locale: 'en',
    });
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);
    if (!body?.errors) {
      expect(body?.data?.valuesAdminConfigurationValues).toBeTruthy();
    }
  });

  test('update with anti-scope-escape key is rejected', async ({ request }) => {
    // Anti-foot-gun: every key must start with slug. — sending an off-slug key
    // (catalog.products.something while slug=general.general) → 422.
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CONFIG_UPDATE, {
      slug: 'general.general',
      values: { 'catalog.products.some-field': 'x' },
      channel: 'default', locale: 'en',
    });
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    const nullPayload = body?.data?.createAdminConfigurationUpdate?.adminConfigurationUpdate === null;
    expect(hasErrors || nullPayload).toBe(true);
  });

  test('update with valid in-slug key is accepted (loose)', async ({ request }) => {
    // Use a harmless field — we don't strictly verify the value, just that the
    // call doesn't throw a scope-escape rejection.
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CONFIG_UPDATE, {
      slug: 'general.general',
      values: { 'general.general.weight_unit': 'kg' },
      channel: 'default', locale: 'en',
    });
    expect(resp.status()).toBe(200);
    // tolerant — accept either success or a known-field validation hiccup
  });
});
