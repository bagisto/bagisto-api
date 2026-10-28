import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../graphql/helpers/adminGraphqlClient';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { env } from '../../../config/env';
import { withAdminToken } from '../../../config/adminAuth';
import {
  ADMIN_MENU_COVERAGE_QUERY,
  ADMIN_PERMISSIONS_COVERAGE_QUERY,
} from '../../../graphql/Queries/coverage/adminMenu.queries';

test.describe.configure({ timeout: 60_000 });

function flatten(nodes: any[]): any[] {
  return nodes.flatMap((node: any) => [node, ...flatten(node.children ?? [])]);
}

async function adminQuery(request: APIRequestContext, query: string): Promise<any> {
  const resp = await sendAdminGraphQLRequest(request, query);
  expect(resp.status()).toBe(200);
  const body = await resp.json();
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
  return body.data;
}

test.describe('admin menu and permissions graphql', () => {
  test('should return the effective permission set of the token admin', async ({ request }) => {
    const data = await adminQuery(request, ADMIN_PERMISSIONS_COVERAGE_QUERY);
    const payload = data.getAdminPermissions;

    expect(['all', 'custom']).toContain(payload.permissionType);
    expect(Array.isArray(payload.permissions)).toBe(true);

    if (payload.permissionType === 'all') {
      expect(payload.permissions).toEqual(['*']);
    } else {
      expect(payload.permissions.length).toBeGreaterThan(0);
      expect(payload.permissions).not.toContain('*');
    }
  });

  test('should refuse the permission set without an admin token', async ({ request }) => {
    const resp = await sendGraphQLRequest(request, ADMIN_PERMISSIONS_COVERAGE_QUERY, {}, {}, env.adminGraphqlEndpoint);
    expect(resp.status()).toBe(401);
    const body = await resp.json();
    expect(body.message).toBe('Unauthenticated.');
    expect(body.data).toBeUndefined();
  });

  test('should return the sidebar menu tree linked to api resources', async ({ request }) => {
    const data = await adminQuery(request, ADMIN_MENU_COVERAGE_QUERY);
    const menu = data.getAdminMenu;

    expect(Array.isArray(menu.tree)).toBe(true);
    expect(menu.tree.length).toBeGreaterThan(0);

    for (const node of flatten(menu.tree)) {
      expect(typeof node.key).toBe('string');
      expect(node.permission).toBe(node.key);
      expect(Array.isArray(node.children)).toBe(true);
    }

    const orders = flatten(menu.tree).find((node: any) => node.key === 'sales.orders');
    expect(orders.apiResource).toEqual({ rest: '/api/admin/orders', graphql: 'adminOrders' });
    expect(menu.tree.find((node: any) => node.key === 'sales').apiResource).toBeNull();
  });

  test('should return the same menu tree over graphql as over rest', async ({ request }) => {
    const data = await adminQuery(request, ADMIN_MENU_COVERAGE_QUERY);

    const rest = await withAdminToken(request, token => request.get(`${env.baseUrl}/api/admin/menu`, {
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
    }));
    expect(rest.status()).toBe(200);

    expect(data.getAdminMenu.tree).toEqual((await rest.json())[0].tree);
  });

  test('should refuse the menu without an admin token', async ({ request }) => {
    const resp = await sendGraphQLRequest(request, ADMIN_MENU_COVERAGE_QUERY, {}, {}, env.adminGraphqlEndpoint);
    expect(resp.status()).toBe(401);
    expect((await resp.json()).message).toBe('Unauthenticated.');
  });

  test('should refuse the menu with a token that does not exist', async ({ request }) => {
    const resp = await sendGraphQLRequest(
      request,
      ADMIN_MENU_COVERAGE_QUERY,
      {},
      { Authorization: 'Bearer 999999|not-a-real-token' },
      env.adminGraphqlEndpoint,
    );
    expect(resp.status()).toBe(401);
    expect((await resp.json()).message).toBe('Unauthenticated.');
  });
});
