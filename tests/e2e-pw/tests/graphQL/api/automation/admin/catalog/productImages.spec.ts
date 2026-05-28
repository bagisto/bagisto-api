// tests/graphQL/api/automation/admin/catalog/productImages.spec.ts
//
// Admin Catalog Product Images GraphQL smoke. Upload is multipart REST-only
// and is intentionally skipped here (CLAUDE.md Phase 5.11). Reorder + delete
// are usable but require a known image id (no list query exposed).

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_PRODUCT_IMAGE_REORDER_MUTATION,
  ADMIN_PRODUCT_IMAGE_DELETE_MUTATION,
} from '../../../../graphql/Queries/admin/catalog/productImages.queries';

test.describe.configure({ timeout: 60_000 });

test.describe('Admin Catalog Product Images GraphQL', () => {
  test('upload is REST-only — skipped over GraphQL', async () => {
    test.skip(true, 'multipart upload REST-only (CLAUDE.md Phase 5.11)');
  });

  test('reorder with non-existent image id surfaces errors', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_IMAGE_REORDER_MUTATION, {
      id: '/api/admin/catalog/products/1/images/reorder',
      productId: 1,
      order: [{ id: 999999999, position: 1 }],
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    const ok = !!body.errors || body.data?.reorderAdminCatalogProductImage?.adminCatalogProductImage == null;
    expect(ok).toBeTruthy();
  });

  test('delete non-existent image surfaces errors', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_IMAGE_DELETE_MUTATION, {
      id: '/api/admin/catalog/products/1/images/999999999',
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    const ok = !!body.errors || body.data?.deleteAdminCatalogProductImage?.adminCatalogProductImage == null;
    expect(ok).toBeTruthy();
  });
});
