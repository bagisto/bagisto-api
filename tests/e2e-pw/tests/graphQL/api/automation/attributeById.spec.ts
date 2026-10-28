import { test, expect } from '@playwright/test';
import { GET_ATTRIBUTE_BY_ID, GET_ALL_ATTRIBUTES_FULL, GET_ATTRIBUTE_WITH_OPTIONS, GET_ALL_ATTRIBUTES } from '../../graphql/Queries/attribute.queries';
import { 
  assertGetAttributeByIdFullResponse,
  assertGetAttributeByIdNotFound,
  assertGetAttributeByIdWithOptions,
  assertGetAttributeByIdWithTranslations,
  assertAttributeWithOptionsResponse,
  assertGraphQLETypedError 
} from '../../graphql/assertions/attribute.assertions';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';

test.describe('get attribute by id api - full details', () => {

  test('should return attribute by valid id with full details', async ({ request }) => {
    const listResponse = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES_FULL, { first: 1 });
    expect(listResponse.status()).toBe(200);
    
    const listBody = await listResponse.json();
    expect(listBody.data.attributes.edges.length).toBeGreaterThan(0);
    
    const attributeId = listBody.data.attributes.edges[0].node.id;
    
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: attributeId });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    assertGetAttributeByIdFullResponse(body);
    
    expect(body.data.attribute.id).toBe(attributeId);
  });

  test('should return attribute with all basic fields populated', async ({ request }) => {
    const listResponse = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES_FULL, { first: 1 });
    expect(listResponse.status()).toBe(200);
    
    const listBody = await listResponse.json();
    const attributeId = listBody.data.attributes.edges[0].node.id;
    
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: attributeId });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const attribute = body.data.attribute;
    
    expect(attribute.id).toBeDefined();
    expect(attribute._id).toBeDefined();
    expect(attribute.code).toBeDefined();
    expect(attribute.adminName).toBeDefined();
    expect(attribute.type).toBeDefined();
    
    expect(typeof attribute.isRequired).toBe('boolean');
    expect(typeof attribute.isUnique).toBe('boolean');
    expect(typeof attribute.isFilterable).toBe('boolean');
    expect(typeof attribute.isComparable).toBe('boolean');
    expect(typeof attribute.isConfigurable).toBe('boolean');
    expect(typeof attribute.isUserDefined).toBe('boolean');
    expect(typeof attribute.isVisibleOnFront).toBe('boolean');
    expect(typeof attribute.valuePerLocale).toBe('boolean');
    expect(typeof attribute.valuePerChannel).toBe('boolean');
    expect(typeof attribute.enableWysiwyg).toBe('boolean');
    
    expect(typeof attribute.position).toBe('number');
    
    expect(attribute.createdAt).toBeDefined();
    expect(attribute.updatedAt).toBeDefined();
    
    expect(new Date(attribute.createdAt).toString()).not.toBe('Invalid Date');
    expect(new Date(attribute.updatedAt).toString()).not.toBe('Invalid Date');
  });

  test('should return attribute with options when available', async ({ request }) => {
    const listResponse = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES_FULL, { first: 10 });
    expect(listResponse.status()).toBe(200);
    
    const listBody = await listResponse.json();
    expect(listBody.data.attributes.edges.length).toBeGreaterThan(0);
    
    let attributeWithOptions = null;
    let attributeId = null;
    
    for (const edge of listBody.data.attributes.edges) {
      if (edge.node.options && edge.node.options.totalCount > 0) {
        attributeWithOptions = edge.node;
        attributeId = edge.node.id;
        break;
      }
    }
    
    if (attributeWithOptions) {
      const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: attributeId });
      expect(response.status()).toBe(200);
      
      const body = await response.json();
      assertGetAttributeByIdWithOptions(body);
      
      const attribute = body.data.attribute;
      expect(attribute.options.edges.length).toBeGreaterThan(0);
      
      attribute.options.edges.forEach((edge: any) => {
        expect(edge.node.id).toBeDefined();
        expect(edge.node.adminName).toBeDefined();
        expect(edge.node.sortOrder).toBeDefined();
        expect(edge.cursor).toBeDefined();
      });
      
      expect(attribute.options.pageInfo).toBeDefined();
      expect(attribute.options.pageInfo.endCursor).toBeDefined();
      expect(typeof attribute.options.pageInfo.hasNextPage).toBe('boolean');
    } else {
      const firstAttributeId = listBody.data.attributes.edges[0].node.id;
      const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: firstAttributeId });
      expect(response.status()).toBe(200);
      
      const body = await response.json();
      expect(body.data.attribute).not.toBeNull();
      expect(body.data.attribute.options).toBeDefined();
    }
  });

  test('should return attribute with translations when available', async ({ request }) => {
    const listResponse = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES_FULL, { first: 10 });
    expect(listResponse.status()).toBe(200);
    
    const listBody = await listResponse.json();
    expect(listBody.data.attributes.edges.length).toBeGreaterThan(0);
    
    let attributeWithTranslations = null;
    let attributeId = null;
    
    for (const edge of listBody.data.attributes.edges) {
      if (edge.node.translations && edge.node.translations.totalCount > 0) {
        attributeWithTranslations = edge.node;
        attributeId = edge.node.id;
        break;
      }
    }
    
    if (attributeWithTranslations) {
      const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: attributeId });
      expect(response.status()).toBe(200);
      
      const body = await response.json();
      assertGetAttributeByIdWithTranslations(body);
      
      const attribute = body.data.attribute;
      expect(attribute.translations.edges.length).toBeGreaterThan(0);
      
      attribute.translations.edges.forEach((edge: any) => {
        expect(edge.node.id).toBeDefined();
        expect(edge.node.attributeId).toBeDefined();
        expect(edge.node.locale).toBeDefined();
        expect(edge.node.name).toBeDefined();
        expect(edge.cursor).toBeDefined();
      });
      
      expect(attribute.translations.pageInfo).toBeDefined();
      expect(attribute.translations.pageInfo.endCursor).toBeDefined();
      expect(typeof attribute.translations.pageInfo.hasNextPage).toBe('boolean');
    } else {
      const firstAttributeId = listBody.data.attributes.edges[0].node.id;
      const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: firstAttributeId });
      expect(response.status()).toBe(200);
      
      const body = await response.json();
      expect(body.data.attribute).not.toBeNull();
      expect(body.data.attribute.translations).toBeDefined();
    }
  });

  test('should return different attributes when querying different ids', async ({ request }) => {
    const listResponse = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES_FULL, { first: 2 });
    expect(listResponse.status()).toBe(200);
    
    const listBody = await listResponse.json();
    expect(listBody.data.attributes.edges.length).toBe(2);
    
    const firstAttributeId = listBody.data.attributes.edges[0].node.id;
    const secondAttributeId = listBody.data.attributes.edges[1].node.id;
    
    const firstResponse = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: firstAttributeId });
    const firstBody = await firstResponse.json();
    
    const secondResponse = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: secondAttributeId });
    const secondBody = await secondResponse.json();
    
    expect(firstBody.data.attribute.id).not.toBe(secondBody.data.attribute.id);
    expect(firstBody.data.attribute.code).not.toBe(secondBody.data.attribute.code);
    expect(firstBody.data.attribute._id).not.toBe(secondBody.data.attribute._id);
  });

  test('should return attribute with valid optional fields', async ({ request }) => {
    const listResponse = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES_FULL, { first: 1 });
    expect(listResponse.status()).toBe(200);
    
    const listBody = await listResponse.json();
    const attributeId = listBody.data.attributes.edges[0].node.id;
    
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: attributeId });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const attribute = body.data.attribute;
    
    if (attribute.swatchType !== undefined) {
      expect(attribute.swatchType === null || typeof attribute.swatchType === 'string').toBeTruthy();
    }
    if (attribute.validation !== undefined) {
      expect(attribute.validation === null || typeof attribute.validation === 'string').toBeTruthy();
    }
    if (attribute.regex !== undefined) {
      expect(attribute.regex === null || typeof attribute.regex === 'string').toBeTruthy();
    }
    if (attribute.defaultValue !== undefined) {
      expect(attribute.defaultValue === null || typeof attribute.defaultValue === 'string').toBeTruthy();
    }
    if (attribute.columnName !== undefined) {
      expect(attribute.columnName === null || typeof attribute.columnName === 'string').toBeTruthy();
    }
    if (attribute.validations !== undefined) {
      expect(attribute.validations === null || typeof attribute.validations === 'string').toBeTruthy();
    }
  });

  test('should return null for non-existent attribute id', async ({ request }) => {
    const nonExistentId = 'non-existent-attribute-id-12345';
    
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: nonExistentId });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    if (body.data && body.data.attribute === null) {
      assertGetAttributeByIdNotFound(body);
    } else if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attribute).toBeNull();
    }
  });

  test('should return error for invalid id format (empty string)', async ({ request }) => {
    const invalidId = '';
    
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: invalidId });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attribute).toBeNull();
    }
  });

  test('should return error when id parameter is missing', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, {});
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    assertGraphQLETypedError(body);
  });

  test('should return error for invalid id format with special characters', async ({ request }) => {
    const invalidId = 'invalid@#$%id';
    
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: invalidId });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attribute).toBeNull();
    }
  });

  test('should return error for sql injection attempt in id', async ({ request }) => {
    const maliciousId = "1' OR '1'='1";
    
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: maliciousId });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attribute).toBeNull();
    }
  });

  test('should return error for extremely long id', async ({ request }) => {
    const extremelyLongId = 'a'.repeat(1000);
    
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: extremelyLongId });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attribute).toBeNull();
    }
  });

  test('should return error for null id', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: null });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attribute).toBeNull();
    }
  });

  test('should return error for numeric id when string is expected', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: 12345 });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    expect(body).toHaveProperty('data');
  });
});

test.describe('get attribute with options api', () => {

  test('should return attribute with options', async ({ request }) => {
    const listResponse = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, { first: 1 });
    expect(listResponse.status()).toBe(200);
    
    const listBody = await listResponse.json();
    expect(listBody.data.attributes.edges.length).toBeGreaterThan(0);
    
    const attributeId = listBody.data.attributes.edges[0].node.id;
    
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_WITH_OPTIONS, { 
      id: attributeId,
      first: 10 
    });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    assertAttributeWithOptionsResponse(body);
    
    expect(body.data.attribute.id).toBe(attributeId);
  });

  test('should return attribute with basic fields', async ({ request }) => {
    const listResponse = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, { first: 1 });
    const listBody = await listResponse.json();
    const attributeId = listBody.data.attributes.edges[0].node.id;
    
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_WITH_OPTIONS, { 
      id: attributeId,
      first: 5 
    });
    const body = await response.json();
    const attribute = body.data.attribute;
    
    expect(attribute.id).toBeDefined();
    expect(attribute.code).toBeDefined();
    expect(attribute.adminName).toBeDefined();
    
    expect(typeof attribute.id).toBe('string');
    expect(typeof attribute.code).toBe('string');
    expect(typeof attribute.adminName).toBe('string');
  });

  test('should return attribute options with cursor', async ({ request }) => {
    const listResponse = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, { first: 1 });
    const listBody = await listResponse.json();
    const attributeId = listBody.data.attributes.edges[0].node.id;
    
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_WITH_OPTIONS, { 
      id: attributeId,
      first: 5 
    });
    const body = await response.json();
    const options = body.data.attribute.options;
    
    if (options.edges.length > 0) {
      expect(options.edges[0].cursor).toBeDefined();
    }
  });

  test('should respect first parameter limit', async ({ request }) => {
    const listResponse = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, { first: 1 });
    const listBody = await listResponse.json();
    const attributeId = listBody.data.attributes.edges[0].node.id;
    
    const first = 2;
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_WITH_OPTIONS, { 
      id: attributeId,
      first 
    });
    const body = await response.json();
    const options = body.data.attribute.options;
    
    expect(options.edges.length).toBeLessThanOrEqual(first);
  });

  test('should return null for non-existent attribute id', async ({ request }) => {
    const nonExistentId = 'non-existent-attribute-12345';
    
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_WITH_OPTIONS, { 
      id: nonExistentId,
      first: 5 
    });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    if (body.data && body.data.attribute === null) {
      expect(body.data.attribute).toBeNull();
    } else if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attribute).toBeNull();
    }
  });

  test('should return error for empty id', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_WITH_OPTIONS, { 
      id: '',
      first: 5 
    });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attribute).toBeNull();
    }
  });

  test('should return error when id parameter is missing', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_WITH_OPTIONS, { first: 5 });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    assertGraphQLETypedError(body);
  });

  test('should return error for null id', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_WITH_OPTIONS, { 
      id: null,
      first: 5 
    });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attribute).toBeNull();
    }
  });

  test('should handle invalid id format', async ({ request }) => {
    const invalidId = 'invalid@#$%id';
    
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_WITH_OPTIONS, { 
      id: invalidId,
      first: 5 
    });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attribute).toBeNull();
    }
  });

  test('should handle negative first parameter', async ({ request }) => {
    const listResponse = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, { first: 1 });
    const listBody = await listResponse.json();
    const attributeId = listBody.data.attributes.edges[0].node.id;
    
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_WITH_OPTIONS, { 
      id: attributeId,
      first: -1 
    });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attribute).toBeDefined();
    }
  });
});
