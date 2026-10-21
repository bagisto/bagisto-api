import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../graphql/helpers/adminGraphqlClient';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { env } from '../../../config/env';
import {
  ADMIN_CONFIGURATION_SLUGS_COVERAGE_QUERY,
  ADMIN_CONFIGURATION_SLUGS_UNKNOWN_FIELD_QUERY,
} from '../../../graphql/Queries/coverage/adminMenu.queries';

test.describe.configure({ timeout: 60_000 });

test.describe('admin configuration slugs graphql', () => {
  test('should list every registered configuration slug with its label and shape', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CONFIGURATION_SLUGS_COVERAGE_QUERY);
    expect(resp.status()).toBe(200);

    const body = await resp.json();
    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

    const slugs = body.data.listAdminConfigurationSlug.slugs;
    expect(Array.isArray(slugs)).toBe(true);
    expect(slugs.length).toBeGreaterThan(0);

    for (const entry of slugs) {
      expect(Object.keys(entry).sort()).toEqual(['hasChildren', 'hasFields', 'name', 'slug', 'sort']);
    }

    expect(slugs.find((entry: any) => entry.slug === 'general')).toEqual({
      slug: 'general',
      name: 'General',
      sort: 1,
      hasFields: false,
      hasChildren: true,
    });

    expect(slugs.find((entry: any) => entry.slug === 'sales.order_settings.order_number')).toMatchObject({
      name: 'Order Number Settings',
      hasFields: true,
      hasChildren: false,
    });
  });

  test('should return the same slugs over graphql as over rest', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CONFIGURATION_SLUGS_COVERAGE_QUERY);
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

    const rest = await request.get(`${env.baseUrl}/api/admin/configuration/slugs`, {
      headers: { Accept: 'application/json', Authorization: `Bearer ${env.adminIntegrationToken}` },
    });
    expect(rest.status()).toBe(200);

    expect(body.data.listAdminConfigurationSlug.slugs).toEqual((await rest.json())[0].slugs);
  });

  test('should refuse the slug listing without an admin token', async ({ request }) => {
    const resp = await sendGraphQLRequest(request, ADMIN_CONFIGURATION_SLUGS_COVERAGE_QUERY, {}, {}, env.adminGraphqlEndpoint);
    expect(resp.status()).toBe(401);
    const body = await resp.json();
    expect(body.message).toBe('Unauthenticated.');
    expect(body.data).toBeUndefined();
  });

  test('should reject a field the slug listing does not have', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CONFIGURATION_SLUGS_UNKNOWN_FIELD_QUERY);
    const body = await resp.json();
    expect(body.errors?.[0]?.message).toMatch(/^Cannot query field "sections" on type "AdminConfigurationSlug"\./);
    expect(body.data).toBeUndefined();
  });
});
