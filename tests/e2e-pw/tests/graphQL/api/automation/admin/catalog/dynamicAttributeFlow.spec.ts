import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_FAMILY_DETAIL_QUERY,
  ADMIN_FAMILY_CREATE_MUTATION,
  ADMIN_FAMILY_DELETE_MUTATION,
} from '../../../../graphql/Queries/admin/catalog/families.queries';
import {
  ADMIN_PRODUCT_CREATE_MUTATION,
  ADMIN_PRODUCT_DELETE_MUTATION,
} from '../../../../graphql/Queries/admin/catalog/products.queries';
import { ADMIN_ATTRIBUTE_DELETE_MUTATION } from '../../../../graphql/Queries/admin/catalog/attributes.queries';
import {
  FLOW_ATTRIBUTE_CREATE,
  FLOW_PRODUCT_UPDATE_EXTRAS,
  FLOW_PRODUCT_DETAIL,
} from '../../../../graphql/Queries/admin/catalog/dynamicFlow.queries';

test.describe.configure({ timeout: 90_000 });

const DEFAULT_FAMILY_IRI = '/api/admin/catalog/families/1';

function rand(): string {
  return Math.random().toString(36).slice(2, 8);
}

async function gql(request: APIRequestContext, query: string, variables: Record<string, any>) {
  const resp = await sendAdminGraphQLRequest(request, query, variables);
  return resp.json();
}

function payloadId(body: any, mutation: string, payload: string): number | null {
  return body?.data?.[mutation]?.[payload]?._id ?? null;
}

async function cleanup(
  request: APIRequestContext,
  ids: { prodId?: number | null; famId?: number | null; attrId?: number | null }
) {
  if (ids.prodId) {
    await gql(request, ADMIN_PRODUCT_DELETE_MUTATION, {
      id: `/api/admin/catalog/products/${ids.prodId}`,
    }).catch(() => {});
  }
  if (ids.famId) {
    await gql(request, ADMIN_FAMILY_DELETE_MUTATION, {
      id: `/api/admin/catalog/families/${ids.famId}`,
    }).catch(() => {});
  }
  if (ids.attrId) {
    await gql(request, ADMIN_ATTRIBUTE_DELETE_MUTATION, {
      id: `/api/admin/catalog/attributes/${ids.attrId}`,
    }).catch(() => {});
  }
}

async function buildFamilyWithAttribute(
  request: APIRequestContext,
  attrId: number
): Promise<number | null> {
  const detail = await gql(request, ADMIN_FAMILY_DETAIL_QUERY, { id: DEFAULT_FAMILY_IRI });
  const sourceGroups = detail?.data?.adminAttributeFamily?.attributeGroups ?? [];
  expect(sourceGroups.length, 'default family must expose its groups').toBeGreaterThan(0);

  const groups = sourceGroups.map((g: any) => ({
    code: g.code,
    name: g.name,
    column: g.column,
    position: g.position,
    custom_attributes: (g.attributes ?? []).map((a: any) => ({ id: a.id, position: a.position })),
  }));
  groups[0].custom_attributes.push({ id: attrId, position: 999 });

  const create = await gql(request, ADMIN_FAMILY_CREATE_MUTATION, {
    code: `flowfam${rand()}`,
    name: `Flow Fam ${rand()}`,
    attributeGroups: groups,
  });
  const famId = payloadId(create, 'createAdminAttributeFamily', 'adminAttributeFamily');
  expect(famId, `family create failed: ${JSON.stringify(create?.errors)}`).toBeTruthy();
  return famId;
}

test.describe('Admin Catalog — Dynamic Attribute → Family → Product flow (GraphQL)', () => {
  test('a user-created attribute becomes a dynamic field on a product with value + label round-trip', async ({
    request,
  }) => {
    const ids: { prodId?: number | null; famId?: number | null; attrId?: number | null } = {};
    try {
      const code = `flowdyn${rand()}`;
      const label = `Flow Dynamic ${rand()}`;

      const attrResp = await gql(request, FLOW_ATTRIBUTE_CREATE, {
        code,
        adminName: label,
        type: 'text',
        translations: { en: { name: label } },
      });
      ids.attrId = payloadId(attrResp, 'createAdminAttribute', 'adminAttribute');
      expect(ids.attrId, `attribute create failed: ${JSON.stringify(attrResp?.errors)}`).toBeTruthy();

      ids.famId = await buildFamilyWithAttribute(request, ids.attrId!);

      const sku = `FLOW${rand()}`;
      const prodResp = await gql(request, ADMIN_PRODUCT_CREATE_MUTATION, {
        sku,
        attributeFamilyId: Number(ids.famId),
        type: 'simple',
      });
      ids.prodId = payloadId(prodResp, 'createAdminCatalogProduct', 'adminCatalogProduct');
      expect(ids.prodId, `product create failed: ${JSON.stringify(prodResp?.errors)}`).toBeTruthy();

      const value = `DYN-${rand()}`;
      const update = await gql(request, FLOW_PRODUCT_UPDATE_EXTRAS, {
        id: `/api/admin/catalog/products/${ids.prodId}`,
        extras: { [code]: value },
      });
      expect(update?.errors, `extras update errored: ${JSON.stringify(update?.errors)}`).toBeUndefined();

      const detail = await gql(request, FLOW_PRODUCT_DETAIL, {
        id: `/api/admin/catalog/products/${ids.prodId}`,
      });
      const attrs = detail?.data?.adminCatalogProduct?.attributes ?? [];
      const mine = attrs.find((a: any) => a.code === code);

      expect(mine, 'the user-created attribute must appear as a dynamic field on the product').toBeTruthy();
      expect(mine.value).toBe(value);
      expect(mine.adminName).toBe(label);
    } finally {
      await cleanup(request, ids);
    }
  });

  test('an attribute created without a name translation has a null label but value still persists (core parity)', async ({
    request,
  }) => {
    const ids: { prodId?: number | null; famId?: number | null; attrId?: number | null } = {};
    try {
      const code = `flownolbl${rand()}`;

      const attrResp = await gql(request, FLOW_ATTRIBUTE_CREATE, {
        code,
        adminName: `No Label ${rand()}`,
        type: 'text',
      });
      ids.attrId = payloadId(attrResp, 'createAdminAttribute', 'adminAttribute');
      expect(ids.attrId, `attribute create failed: ${JSON.stringify(attrResp?.errors)}`).toBeTruthy();

      ids.famId = await buildFamilyWithAttribute(request, ids.attrId!);

      const prodResp = await gql(request, ADMIN_PRODUCT_CREATE_MUTATION, {
        sku: `FLOW${rand()}`,
        attributeFamilyId: Number(ids.famId),
        type: 'simple',
      });
      ids.prodId = payloadId(prodResp, 'createAdminCatalogProduct', 'adminCatalogProduct');
      expect(ids.prodId, `product create failed: ${JSON.stringify(prodResp?.errors)}`).toBeTruthy();

      const value = `DYN-${rand()}`;
      await gql(request, FLOW_PRODUCT_UPDATE_EXTRAS, {
        id: `/api/admin/catalog/products/${ids.prodId}`,
        extras: { [code]: value },
      });

      const detail = await gql(request, FLOW_PRODUCT_DETAIL, {
        id: `/api/admin/catalog/products/${ids.prodId}`,
      });
      const attrs = detail?.data?.adminCatalogProduct?.attributes ?? [];
      const mine = attrs.find((a: any) => a.code === code);

      expect(mine, 'the attribute must still appear as a field on the product').toBeTruthy();
      expect(mine.value).toBe(value);
      expect(mine.adminName).toBeNull();
    } finally {
      await cleanup(request, ids);
    }
  });
});
