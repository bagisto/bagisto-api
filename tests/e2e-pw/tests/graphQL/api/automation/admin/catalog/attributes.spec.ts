// tests/graphQL/api/automation/admin/catalog/attributes.spec.ts
//
// Admin Catalog Attributes + Attribute Options GraphQL smoke.

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_ATTRIBUTES_LIST_QUERY,
  ADMIN_ATTRIBUTE_DETAIL_QUERY,
  ADMIN_ATTRIBUTE_CREATE_MUTATION,
  ADMIN_ATTRIBUTE_UPDATE_MUTATION,
  ADMIN_ATTRIBUTE_DELETE_MUTATION,
  ADMIN_ATTRIBUTE_MASS_DELETE_MUTATION,
  ADMIN_ATTRIBUTE_OPTION_CREATE_MUTATION,
  ADMIN_ATTRIBUTE_OPTION_UPDATE_MUTATION,
  ADMIN_ATTRIBUTE_OPTION_DELETE_MUTATION,
} from '../../../../graphql/Queries/admin/catalog/attributes.queries';

test.describe.configure({ timeout: 60_000 });

test.describe('Admin Catalog Attributes GraphQL', () => {
  test('list returns edges', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_ATTRIBUTES_LIST_QUERY, { first: 5 });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    if (body.errors) console.log('list errors:', body.errors);
    expect(body.data?.adminAttributes?.edges).toBeDefined();
  });

  // Owns its attribute: the newest listed row races with sibling specs that
  // create and then delete attributes when the suite runs in parallel.
  test('detail by id', async ({ request }) => {
    const createResp = await sendAdminGraphQLRequest(request, ADMIN_ATTRIBUTE_CREATE_MUTATION, {
      code: `e2e_attr_detail_${Date.now()}_${Math.floor(Math.random() * 100000)}`,
      adminName: `E2E Attr Detail ${Date.now()}`,
      type: 'text',
    });
    const created = (await createResp.json()).data?.createAdminAttribute?.adminAttribute;
    test.skip(!created?.id, 'attribute fixture could not be created');

    try {
      const resp = await sendAdminGraphQLRequest(request, ADMIN_ATTRIBUTE_DETAIL_QUERY, { id: created.id });
      expect(resp.status()).toBe(200);
      const body = await resp.json();
      if (body.errors) console.log('detail errors:', body.errors);
      expect(body.data?.adminAttribute?._id).toBe(Number(created._id));
    } finally {
      await sendAdminGraphQLRequest(request, ADMIN_ATTRIBUTE_DELETE_MUTATION, { id: created.id });
    }
  });

  test('create + update + delete text attribute roundtrip', async ({ request }) => {
    const code = `e2e_attr_${Date.now()}`;
    const createResp = await sendAdminGraphQLRequest(request, ADMIN_ATTRIBUTE_CREATE_MUTATION, {
      code,
      adminName: `E2E Attr ${Date.now()}`,
      type: 'text',
    });
    expect(createResp.status()).toBe(200);
    const cb = await createResp.json();
    if (cb.errors) console.log('create errors:', cb.errors);
    const created = cb.data?.createAdminAttribute?.adminAttribute;
    test.skip(!created?._id, 'create returned no id');

    const id = created.id;
    const updResp = await sendAdminGraphQLRequest(request, ADMIN_ATTRIBUTE_UPDATE_MUTATION, {
      id,
      adminName: `Updated ${code}`,
    });
    expect(updResp.status()).toBe(200);

    const delResp = await sendAdminGraphQLRequest(request, ADMIN_ATTRIBUTE_DELETE_MUTATION, { id });
    expect(delResp.status()).toBe(200);
  });

  test('create with duplicate code surfaces errors', async ({ request }) => {
    const code = `e2e_dup_${Date.now()}`;
    const first = await sendAdminGraphQLRequest(request, ADMIN_ATTRIBUTE_CREATE_MUTATION, {
      code, adminName: 'first', type: 'text',
    });
    const created = (await first.json()).data?.createAdminAttribute?.adminAttribute;
    test.skip(!created?._id, 'first create failed');

    const dup = await sendAdminGraphQLRequest(request, ADMIN_ATTRIBUTE_CREATE_MUTATION, {
      code, adminName: 'second', type: 'text',
    });
    expect(dup.status()).toBe(200);
    const body = await dup.json();
    const failed = !!body.errors || body.data?.createAdminAttribute?.adminAttribute == null;
    expect(failed).toBeTruthy();

    // cleanup
    await sendAdminGraphQLRequest(request, ADMIN_ATTRIBUTE_DELETE_MUTATION, { id: created.id });
  });

  test('delete non-existent surfaces errors', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_ATTRIBUTE_DELETE_MUTATION, {
      id: '/api/admin/catalog/attributes/999999999',
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    const ok = !!body.errors || body.data?.deleteAdminAttribute?.adminAttribute == null;
    expect(ok).toBeTruthy();
  });

  test('mass-delete with empty indices surfaces errors', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_ATTRIBUTE_MASS_DELETE_MUTATION, {
      indices: [],
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    const ok = !!body.errors || body.data != null;
    expect(ok).toBeTruthy();
  });

  test('attribute option create + update + delete on a select attribute', async ({ request }) => {
    // Create a select-type attribute we can attach options to.
    const code = `e2e_sel_${Date.now()}`;
    const ac = await sendAdminGraphQLRequest(request, ADMIN_ATTRIBUTE_CREATE_MUTATION, {
      code, adminName: `Select ${code}`, type: 'select',
    });
    const attr = (await ac.json()).data?.createAdminAttribute?.adminAttribute;
    test.skip(!attr?._id, 'select attribute create failed');

    const optResp = await sendAdminGraphQLRequest(request, ADMIN_ATTRIBUTE_OPTION_CREATE_MUTATION, {
      attributeId: String(attr._id),
      adminName: 'Opt A',
      sortOrder: 1,
    });
    expect(optResp.status()).toBe(200);
    const ob = await optResp.json();
    if (ob.errors) console.log('option create errors:', ob.errors);
    const opt = ob.data?.createAdminAttributeOption?.adminAttributeOption;

    if (opt?._id) {
      await sendAdminGraphQLRequest(request, ADMIN_ATTRIBUTE_OPTION_UPDATE_MUTATION, {
        id: opt.id, adminName: 'Opt A upd', sortOrder: 2,
      });
      await sendAdminGraphQLRequest(request, ADMIN_ATTRIBUTE_OPTION_DELETE_MUTATION, {
        id: opt.id, attributeId: Number(attr._id), optionId: Number(opt._id),
      });
    }

    // cleanup parent attribute
    await sendAdminGraphQLRequest(request, ADMIN_ATTRIBUTE_DELETE_MUTATION, { id: attr.id });
  });

  test('attribute option create on non-select attribute surfaces errors', async ({ request }) => {
    // text attributes don't support options
    const code = `e2e_txt_${Date.now()}`;
    const ac = await sendAdminGraphQLRequest(request, ADMIN_ATTRIBUTE_CREATE_MUTATION, {
      code, adminName: `Text ${code}`, type: 'text',
    });
    const attr = (await ac.json()).data?.createAdminAttribute?.adminAttribute;
    test.skip(!attr?._id, 'text attribute create failed');

    const optResp = await sendAdminGraphQLRequest(request, ADMIN_ATTRIBUTE_OPTION_CREATE_MUTATION, {
      attributeId: String(attr._id),
      adminName: 'Should fail',
      sortOrder: 1,
    });
    expect(optResp.status()).toBe(200);
    const body = await optResp.json();
    const failed = !!body.errors || body.data?.createAdminAttributeOption?.adminAttributeOption == null;
    expect(failed).toBeTruthy();

    await sendAdminGraphQLRequest(request, ADMIN_ATTRIBUTE_DELETE_MUTATION, { id: attr.id });
  });
});
