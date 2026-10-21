import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import { graphQLErrorMessages } from '../../../../graphql/helpers/testSupport';
import {
  ADMIN_FAMILY_DETAIL_QUERY,
  ADMIN_FAMILY_CREATE_MUTATION,
  ADMIN_FAMILY_DELETE_MUTATION,
} from '../../../../graphql/Queries/admin/catalog/families.queries';

test.describe.configure({ timeout: 60_000 });

const MESSAGES = {
  NOT_FOUND: 'Attribute family not found.',
  DEFAULT_DELETE: 'The default attribute family cannot be deleted.',
  CODE_REQUIRED: 'The code field is required.',
  CODE_TAKEN: 'The code has already been taken.',
};

const FAMILIES_BY_CODE = `
  query adminAttributeFamilies($code: String, $first: Int) {
    adminAttributeFamilies(code: $code, first: $first) {
      edges {
        node {
          id
          _id
          code
          name
        }
      }
    }
  }
`;

const UPDATE_FAMILY = `
  mutation updateAdminAttributeFamily($id: ID!, $code: String, $name: String, $attributeGroups: Iterable) {
    updateAdminAttributeFamily(input: { id: $id, code: $code, name: $name, attributeGroups: $attributeGroups }) {
      adminAttributeFamily {
        id
        _id
        code
        name
      }
    }
  }
`;

interface CreatedFamily {
  id: number;
  iri: string;
  code: string;
  name: string;
}

async function runAdmin(request: APIRequestContext, query: string, variables: Record<string, any> = {}): Promise<any> {
  const response = await sendAdminGraphQLRequest(request, query, variables);

  expect(response.status()).toBe(200);

  return response.json();
}

function expectNoErrors(body: any): void {
  expect(body.errors, graphQLErrorMessages(body).join(' | ')).toBeUndefined();
}

function familyIri(id: number): string {
  return `/api/admin/catalog/families/${id}`;
}

function generalGroup(): Record<string, any>[] {
  return [{ code: 'general', name: 'General', column: 1, position: 1, custom_attributes: [{ id: 1 }] }];
}

async function createFamily(request: APIRequestContext): Promise<CreatedFamily> {
  const stamp = `${Date.now()}${Math.floor(Math.random() * 100000)}`;
  const code = `e2e_fam_${stamp}`;
  const name = `E2E Family ${stamp}`;

  const body = await runAdmin(request, ADMIN_FAMILY_CREATE_MUTATION, { code, name, attributeGroups: generalGroup() });

  expectNoErrors(body);

  const created = body.data.createAdminAttributeFamily.adminAttributeFamily;

  expect(created.code).toBe(code);
  expect(created.name).toBe(name);

  const id = Number(created._id);

  expect(id).toBeGreaterThan(0);
  expect(created.id).toBe(familyIri(id));

  return { id, iri: familyIri(id), code, name };
}

test.describe('admin catalog attribute families graphql', () => {
  test('should list the family it created when filtered by code', async ({ request }) => {
    const family = await createFamily(request);

    const body = await runAdmin(request, FAMILIES_BY_CODE, { code: family.code, first: 10 });
    expectNoErrors(body);

    const nodes = body.data.adminAttributeFamilies.edges.map((edge: any) => edge.node);

    expect(nodes.filter((node: any) => node.code === family.code)).toEqual([
      { id: family.iri, _id: family.id, code: family.code, name: family.name },
    ]);
  });

  test('should read the family it created with its attribute groups', async ({ request }) => {
    const family = await createFamily(request);

    const body = await runAdmin(request, ADMIN_FAMILY_DETAIL_QUERY, { id: family.iri });
    expectNoErrors(body);

    const detail = body.data.adminAttributeFamily;

    expect(detail._id).toBe(family.id);
    expect(detail.code).toBe(family.code);
    expect(detail.name).toBe(family.name);
    expect(detail.attributeGroups.map((group: any) => group.code)).toEqual(['general']);
    expect(detail.attributeGroups[0].attributes.map((attribute: any) => attribute.id)).toEqual([1]);
  });

  test('should update and then delete the family it created', async ({ request }) => {
    const family = await createFamily(request);
    const renamed = `${family.name} upd`;

    const updated = await runAdmin(request, UPDATE_FAMILY, {
      id: family.iri,
      code: family.code,
      name: renamed,
    });
    expectNoErrors(updated);
    expect(updated.data.updateAdminAttributeFamily.adminAttributeFamily).toEqual({
      id: family.iri,
      _id: family.id,
      code: family.code,
      name: renamed,
    });

    const reread = await runAdmin(request, ADMIN_FAMILY_DETAIL_QUERY, { id: family.iri });
    expectNoErrors(reread);
    expect(reread.data.adminAttributeFamily.name).toBe(renamed);

    const deleted = await runAdmin(request, ADMIN_FAMILY_DELETE_MUTATION, { id: family.iri });
    expectNoErrors(deleted);
    expect(Number(deleted.data.deleteAdminAttributeFamily.adminAttributeFamily._id)).toBe(family.id);

    const gone = await runAdmin(request, ADMIN_FAMILY_DETAIL_QUERY, { id: family.iri });
    expect(graphQLErrorMessages(gone)).toEqual([MESSAGES.NOT_FOUND]);
  });

  test('should reject a family without a code', async ({ request }) => {
    const name = `E2E Codeless ${Date.now()}`;

    const body = await runAdmin(request, ADMIN_FAMILY_CREATE_MUTATION, { code: '', name, attributeGroups: [] });

    expect(graphQLErrorMessages(body)).toEqual([MESSAGES.CODE_REQUIRED]);
    expect(body.data?.createAdminAttributeFamily ?? null).toBeNull();
  });

  test('should reject a family whose code is already taken', async ({ request }) => {
    const family = await createFamily(request);

    const body = await runAdmin(request, ADMIN_FAMILY_CREATE_MUTATION, {
      code: family.code,
      name: `${family.name} duplicate`,
      attributeGroups: generalGroup(),
    });

    expect(graphQLErrorMessages(body)).toEqual([MESSAGES.CODE_TAKEN]);

    const listed = await runAdmin(request, FAMILIES_BY_CODE, { code: family.code, first: 10 });
    expectNoErrors(listed);
    expect(listed.data.adminAttributeFamilies.edges.filter((edge: any) => edge.node.code === family.code)).toHaveLength(1);
  });

  test('should report a family that does not exist on delete', async ({ request }) => {
    const body = await runAdmin(request, ADMIN_FAMILY_DELETE_MUTATION, { id: familyIri(999999999) });

    expect(graphQLErrorMessages(body)).toEqual([MESSAGES.NOT_FOUND]);
    expect(body.data?.deleteAdminAttributeFamily ?? null).toBeNull();
  });

  test('should refuse to delete the default family', async ({ request }) => {
    const listed = await runAdmin(request, FAMILIES_BY_CODE, { code: 'default', first: 10 });
    expectNoErrors(listed);

    const defaultFamily = listed.data.adminAttributeFamilies.edges
      .map((edge: any) => edge.node)
      .find((node: any) => node.code === 'default');

    expect(defaultFamily, 'the seeded default attribute family is missing').toBeTruthy();

    const body = await runAdmin(request, ADMIN_FAMILY_DELETE_MUTATION, { id: defaultFamily.id });

    expect(graphQLErrorMessages(body)).toEqual([MESSAGES.DEFAULT_DELETE]);

    const detail = await runAdmin(request, ADMIN_FAMILY_DETAIL_QUERY, { id: defaultFamily.id });
    expectNoErrors(detail);
    expect(detail.data.adminAttributeFamily.code).toBe('default');
  });
});
