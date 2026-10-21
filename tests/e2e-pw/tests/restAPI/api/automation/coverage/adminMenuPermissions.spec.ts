import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../rest/helpers/adminClient';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import { ADMIN_MENU_COVERAGE } from '../../../rest/endpoints/coverage/adminMenu.endpoints';

test.describe.configure({ timeout: 60_000 });

const MENU_NODE_KEYS = ['key', 'label', 'icon', 'sort', 'permission', 'apiResource', 'children'];

async function safeJson(resp: any): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

function flatten(nodes: any[]): any[] {
  return nodes.flatMap((node: any) => [node, ...flatten(node.children ?? [])]);
}

function findNode(nodes: any[], key: string): any {
  return flatten(nodes).find((node: any) => node.key === key);
}

async function readPermissions(request: APIRequestContext): Promise<any> {
  const resp = await sendAdminRequest(request, ADMIN_MENU_COVERAGE.PERMISSIONS);
  expect(resp.status()).toBe(200);
  const body = await safeJson(resp);
  expect(Array.isArray(body)).toBe(true);
  expect(body).toHaveLength(1);
  return body[0];
}

test.describe('admin menu and permissions rest api', () => {
  test('should return the effective permission set of the token admin', async ({ request }) => {
    const payload = await readPermissions(request);

    expect(payload.id).toBe('permissions');
    expect(['all', 'custom']).toContain(payload.permissionType);
    expect(Array.isArray(payload.permissions)).toBe(true);

    if (payload.permissionType === 'all') {
      expect(payload.permissions).toEqual(['*']);
    } else {
      expect(payload.permissions.length).toBeGreaterThan(0);
      for (const key of payload.permissions) {
        expect(typeof key).toBe('string');
        expect(key).not.toBe('*');
      }
    }
  });

  test('should refuse the permission set without an admin token', async ({ request }) => {
    const resp = await sendRestRequest(request, ADMIN_MENU_COVERAGE.PERMISSIONS);
    expect(resp.status()).toBe(401);
    const body = await safeJson(resp);
    expect(body.message).toBe('Unauthenticated.');
    expect(body.error).toBe('unauthenticated');
  });

  test('should refuse the permission set with a token that does not exist', async ({ request }) => {
    const resp = await sendRestRequest(request, ADMIN_MENU_COVERAGE.PERMISSIONS, {
      headers: { Authorization: 'Bearer 999999|not-a-real-token' },
    });
    expect(resp.status()).toBe(401);
    expect((await safeJson(resp)).message).toBe('Unauthenticated.');
  });

  test('should return the sidebar menu as a tree linked to api resources', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MENU_COVERAGE.MENU);
    expect(resp.status()).toBe(200);

    const body = await safeJson(resp);
    expect(Array.isArray(body)).toBe(true);
    expect(body).toHaveLength(1);
    expect(body[0].id).toBe('menu');

    const tree = body[0].tree;
    expect(Array.isArray(tree)).toBe(true);
    expect(tree.length).toBeGreaterThan(0);

    for (const node of flatten(tree)) {
      expect(Object.keys(node).sort()).toEqual([...MENU_NODE_KEYS].sort());
      expect(typeof node.key).toBe('string');
      expect(typeof node.label).toBe('string');
      expect(typeof node.sort).toBe('number');
      expect(node.permission).toBe(node.key);
      expect(Array.isArray(node.children)).toBe(true);
      if (node.apiResource !== null) {
        expect(node.apiResource.rest).toMatch(/^\/api\/admin\//);
      }
    }

    for (const level of [tree, ...flatten(tree).map((node: any) => node.children)]) {
      const sorts = level.map((node: any) => node.sort);
      expect(sorts).toEqual([...sorts].sort((a: number, b: number) => a - b));
    }

    const sales = tree.find((node: any) => node.key === 'sales');
    expect(sales, 'sales group is listed').toBeTruthy();
    expect(sales.apiResource).toBeNull();
    expect(findNode(tree, 'sales.orders').apiResource).toEqual({
      rest: '/api/admin/orders',
      graphql: 'adminOrders',
    });
    expect(findNode(tree, 'catalog.families').apiResource).toEqual({
      rest: '/api/admin/catalog/families',
      graphql: 'adminAttributeFamilies',
    });
    expect(findNode(tree, 'dashboard').apiResource).toEqual({
      rest: '/api/admin/dashboard/stats',
      graphql: 'statsAdminDashboard',
    });
  });

  test('should only list menu entries the token admin is permitted to see', async ({ request }) => {
    const permissions = await readPermissions(request);

    const resp = await sendAdminRequest(request, ADMIN_MENU_COVERAGE.MENU);
    expect(resp.status()).toBe(200);
    const tree = (await safeJson(resp))[0].tree;

    if (permissions.permissionType === 'all') {
      expect(tree.map((node: any) => node.key)).toEqual(expect.arrayContaining(['dashboard', 'sales', 'catalog', 'customers', 'settings']));
      return;
    }

    for (const node of flatten(tree)) {
      expect(permissions.permissions).toContain(node.permission);
    }
  });

  test('should refuse the menu without an admin token', async ({ request }) => {
    const resp = await sendRestRequest(request, ADMIN_MENU_COVERAGE.MENU);
    expect(resp.status()).toBe(401);
    const body = await safeJson(resp);
    expect(body.message).toBe('Unauthenticated.');
    expect(body.tree).toBeUndefined();
  });
});
