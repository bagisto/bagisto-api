// tests/api/automation/attributeById.spec.ts
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

test.describe('Get Attribute By ID API - Full Details', () => {

  // ==================== POSITIVE TEST CASES ====================

  /**
   * Positive Test: Should return attribute by valid ID with all fields
   */
  test('Should return attribute by valid ID with full details', async ({ request }) => {
    // First, get a valid attribute ID from the list
    const listResponse = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES_FULL, { first: 1 });
    expect(listResponse.status()).toBe(200);
    
    const listBody = await listResponse.json();
    expect(listBody.data.attributes.edges.length).toBeGreaterThan(0);
    
    const attributeId = listBody.data.attributes.edges[0].node.id;
    
    // Now get the attribute by ID
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: attributeId });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    assertGetAttributeByIdFullResponse(body);
    
    // Verify the returned attribute has the same ID
    expect(body.data.attribute.id).toBe(attributeId);
  });

  /**
   * Positive Test: Should return attribute with all basic fields populated
   */
  test('Should return attribute with all basic fields populated', async ({ request }) => {
    // First, get a valid attribute ID
    const listResponse = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES_FULL, { first: 1 });
    expect(listResponse.status()).toBe(200);
    
    const listBody = await listResponse.json();
    const attributeId = listBody.data.attributes.edges[0].node.id;
    
    // Get attribute by ID
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: attributeId });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const attribute = body.data.attribute;
    
    // Validate all required fields
    expect(attribute.id).toBeDefined();
    expect(attribute._id).toBeDefined();
    expect(attribute.code).toBeDefined();
    expect(attribute.adminName).toBeDefined();
    expect(attribute.type).toBeDefined();
    
    // Validate boolean fields
    expect(typeof attribute.isRequired === 'boolean' || typeof attribute.isRequired === 'string').toBeTruthy();
    expect(typeof attribute.isUnique === 'boolean' || typeof attribute.isUnique === 'string').toBeTruthy();
    expect(typeof attribute.isFilterable === 'boolean' || typeof attribute.isFilterable === 'string').toBeTruthy();
    expect(typeof attribute.isComparable === 'boolean' || typeof attribute.isComparable === 'string').toBeTruthy();
    expect(typeof attribute.isConfigurable === 'boolean' || typeof attribute.isConfigurable === 'string').toBeTruthy();
    expect(typeof attribute.isUserDefined === 'boolean' || typeof attribute.isUserDefined === 'string').toBeTruthy();
    expect(typeof attribute.isVisibleOnFront === 'boolean' || typeof attribute.isVisibleOnFront === 'string').toBeTruthy();
    expect(typeof attribute.valuePerLocale === 'boolean' || typeof attribute.valuePerLocale === 'string').toBeTruthy();
    expect(typeof attribute.valuePerChannel === 'boolean' || typeof attribute.valuePerChannel === 'string').toBeTruthy();
    expect(typeof attribute.enableWysiwyg === 'boolean' || typeof attribute.enableWysiwyg === 'string').toBeTruthy();
    
    // Validate position
    expect(typeof attribute.position).toBe('number');
    
    // Validate timestamps
    expect(attribute.createdAt).toBeDefined();
    expect(attribute.updatedAt).toBeDefined();
    
    // Validate timestamps are valid date strings (can be parsed by Date)
    expect(new Date(attribute.createdAt).toString()).not.toBe('Invalid Date');
    expect(new Date(attribute.updatedAt).toString()).not.toBe('Invalid Date');
  });

  /**
   * Positive Test: Should return attribute with options when available
   */
  test('Should return attribute with options when available', async ({ request }) => {
    // Get an attribute that might have options
    const listResponse = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES_FULL, { first: 10 });
    expect(listResponse.status()).toBe(200);
    
    const listBody = await listResponse.json();
    expect(listBody.data.attributes.edges.length).toBeGreaterThan(0);
    
    // Find an attribute with options
    let attributeWithOptions = null;
    let attributeId = null;
    
    for (const edge of listBody.data.attributes.edges) {
      if (edge.node.options && edge.node.options.totalCount > 0) {
        attributeWithOptions = edge.node;
        attributeId = edge.node.id;
        break;
      }
    }
    
    // If we found one, test it
    if (attributeWithOptions) {
      const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: attributeId });
      expect(response.status()).toBe(200);
      
      const body = await response.json();
      assertGetAttributeByIdWithOptions(body);
      
      // Verify options structure
      const attribute = body.data.attribute;
      expect(attribute.options.edges.length).toBeGreaterThan(0);
      
      // Validate option fields
      attribute.options.edges.forEach((edge: any) => {
        expect(edge.node.id).toBeDefined();
        expect(edge.node.adminName).toBeDefined();
        expect(edge.node.sortOrder).toBeDefined();
        expect(edge.cursor).toBeDefined();
      });
      
      // Validate options pageInfo
      expect(attribute.options.pageInfo).toBeDefined();
      expect(attribute.options.pageInfo.endCursor).toBeDefined();
      expect(typeof attribute.options.pageInfo.hasNextPage).toBe('boolean');
    } else {
      // If no attribute with options found, at least verify the query works
      const firstAttributeId = listBody.data.attributes.edges[0].node.id;
      const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: firstAttributeId });
      expect(response.status()).toBe(200);
      
      const body = await response.json();
      expect(body.data.attribute).not.toBeNull();
      expect(body.data.attribute.options).toBeDefined();
    }
  });

  /**
   * Positive Test: Should return attribute with translations when available
   */
  test('Should return attribute with translations when available', async ({ request }) => {
    // Get an attribute that might have translations
    const listResponse = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES_FULL, { first: 10 });
    expect(listResponse.status()).toBe(200);
    
    const listBody = await listResponse.json();
    expect(listBody.data.attributes.edges.length).toBeGreaterThan(0);
    
    // Find an attribute with translations
    let attributeWithTranslations = null;
    let attributeId = null;
    
    for (const edge of listBody.data.attributes.edges) {
      if (edge.node.translations && edge.node.translations.totalCount > 0) {
        attributeWithTranslations = edge.node;
        attributeId = edge.node.id;
        break;
      }
    }
    
    // If we found one, test it
    if (attributeWithTranslations) {
      const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: attributeId });
      expect(response.status()).toBe(200);
      
      const body = await response.json();
      assertGetAttributeByIdWithTranslations(body);
      
      // Verify translations structure
      const attribute = body.data.attribute;
      expect(attribute.translations.edges.length).toBeGreaterThan(0);
      
      // Validate translation fields
      attribute.translations.edges.forEach((edge: any) => {
        expect(edge.node.id).toBeDefined();
        expect(edge.node.attributeId).toBeDefined();
        expect(edge.node.locale).toBeDefined();
        expect(edge.node.name).toBeDefined();
        expect(edge.cursor).toBeDefined();
      });
      
      // Validate translations pageInfo
      expect(attribute.translations.pageInfo).toBeDefined();
      expect(attribute.translations.pageInfo.endCursor).toBeDefined();
      expect(typeof attribute.translations.pageInfo.hasNextPage).toBe('boolean');
    } else {
      // If no attribute with translations found, at least verify the query works
      const firstAttributeId = listBody.data.attributes.edges[0].node.id;
      const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: firstAttributeId });
      expect(response.status()).toBe(200);
      
      const body = await response.json();
      expect(body.data.attribute).not.toBeNull();
      expect(body.data.attribute.translations).toBeDefined();
    }
  });

  /**
   * Positive Test: Should return different attributes when querying different IDs
   */
  test('Should return different attributes when querying different IDs', async ({ request }) => {
    // Get two different attributes
    const listResponse = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES_FULL, { first: 2 });
    expect(listResponse.status()).toBe(200);
    
    const listBody = await listResponse.json();
    expect(listBody.data.attributes.edges.length).toBe(2);
    
    const firstAttributeId = listBody.data.attributes.edges[0].node.id;
    const secondAttributeId = listBody.data.attributes.edges[1].node.id;
    
    // Get first attribute
    const firstResponse = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: firstAttributeId });
    const firstBody = await firstResponse.json();
    
    // Get second attribute
    const secondResponse = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: secondAttributeId });
    const secondBody = await secondResponse.json();
    
    // Verify they are different
    expect(firstBody.data.attribute.id).not.toBe(secondBody.data.attribute.id);
    expect(firstBody.data.attribute.code).not.toBe(secondBody.data.attribute.code);
    expect(firstBody.data.attribute._id).not.toBe(secondBody.data.attribute._id);
  });

  /**
   * Positive Test: Should return attribute with valid optional fields
   */
  test('Should return attribute with valid optional fields', async ({ request }) => {
    // Get an attribute
    const listResponse = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES_FULL, { first: 1 });
    expect(listResponse.status()).toBe(200);
    
    const listBody = await listResponse.json();
    const attributeId = listBody.data.attributes.edges[0].node.id;
    
    // Get attribute by ID
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: attributeId });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const attribute = body.data.attribute;
    
    // Validate optional fields can be string or null
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

  // ==================== NEGATIVE TEST CASES ====================

  /**
   * Negative Test: Should return null for non-existent attribute ID
   */
  test('Should return null for non-existent attribute ID', async ({ request }) => {
    const nonExistentId = 'non-existent-attribute-id-12345';
    
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: nonExistentId });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    // Depending on API behavior, it might return null or an error
    // This test assumes the API returns null for non-existent ID
    if (body.data && body.data.attribute === null) {
      assertGetAttributeByIdNotFound(body);
    } else if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      // If neither, fail the test
      expect(body.data.attribute).toBeNull();
    }
  });

  /**
   * Negative Test: Should return error for invalid ID format (empty string)
   */
  test('Should return error for invalid ID format (empty string)', async ({ request }) => {
    const invalidId = '';
    
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: invalidId });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    // Should return either an error or null
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attribute).toBeNull();
    }
  });

  /**
   * Negative Test: Should return error when ID parameter is missing
   */
  test('Should return error when ID parameter is missing', async ({ request }) => {
    // Send request without the id parameter
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, {});
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    // Should return GraphQL error about missing required argument
    assertGraphQLETypedError(body);
  });

  /**
   * Negative Test: Should return error for invalid ID format (special characters)
   */
  test('Should return error for invalid ID format with special characters', async ({ request }) => {
    const invalidId = 'invalid@#$%id';
    
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: invalidId });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    // Should return either an error or null
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attribute).toBeNull();
    }
  });

  /**
   * Negative Test: Should return error for invalid ID format (SQL injection attempt)
   */
  test('Should return error for SQL injection attempt in ID', async ({ request }) => {
    const maliciousId = "1' OR '1'='1";
    
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: maliciousId });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    // Should return either an error or null (not execute the query)
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attribute).toBeNull();
    }
  });

  /**
   * Negative Test: Should return error for extremely long ID
   */
  test('Should return error for extremely long ID', async ({ request }) => {
    const extremelyLongId = 'a'.repeat(1000);
    
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: extremelyLongId });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    // Should return either an error or null
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attribute).toBeNull();
    }
  });

  /**
   * Negative Test: Should return error for null ID
   */
  test('Should return error for null ID', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: null });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    // Should return GraphQL error about invalid type
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attribute).toBeNull();
    }
  });

  /**
   * Negative Test: Should return error for numeric ID when string is expected
   */
  test('Should return error for numeric ID when string is expected', async ({ request }) => {
    // This sends a number instead of a string - GraphQL ID type should accept both
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_BY_ID, { id: 12345 });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    // GraphQL might coerce the number to string or return error
    // Just verify the response structure is valid
    expect(body).toHaveProperty('data');
  });
});

test.describe('Get Attribute with Options API', () => {

  // ==================== POSITIVE TEST CASES ====================

  /**
   * Positive Test: Should return attribute with options
   */
  test('Should return attribute with options', async ({ request }) => {
    // First, get a valid attribute ID
    const listResponse = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, { first: 1 });
    expect(listResponse.status()).toBe(200);
    
    const listBody = await listResponse.json();
    expect(listBody.data.attributes.edges.length).toBeGreaterThan(0);
    
    const attributeId = listBody.data.attributes.edges[0].node.id;
    
    // Now get attribute with options
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_WITH_OPTIONS, { 
      id: attributeId,
      first: 10 
    });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    assertAttributeWithOptionsResponse(body);
    
    // Verify the returned attribute has the same ID
    expect(body.data.attribute.id).toBe(attributeId);
  });

  /**
   * Positive Test: Should return attribute with basic fields
   */
  test('Should return attribute with basic fields', async ({ request }) => {
    // Get a valid attribute ID
    const listResponse = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, { first: 1 });
    const listBody = await listResponse.json();
    const attributeId = listBody.data.attributes.edges[0].node.id;
    
    // Get attribute with options
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_WITH_OPTIONS, { 
      id: attributeId,
      first: 5 
    });
    const body = await response.json();
    const attribute = body.data.attribute;
    
    // Validate basic attribute fields
    expect(attribute.id).toBeDefined();
    expect(attribute.code).toBeDefined();
    expect(attribute.adminName).toBeDefined();
    
    expect(typeof attribute.id).toBe('string');
    expect(typeof attribute.code).toBe('string');
    expect(typeof attribute.adminName).toBe('string');
  });

  /**
   * Positive Test: Should return attribute options with cursor
   */
  test('Should return attribute options with cursor', async ({ request }) => {
    // Get a valid attribute ID
    const listResponse = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, { first: 1 });
    const listBody = await listResponse.json();
    const attributeId = listBody.data.attributes.edges[0].node.id;
    
    // Get attribute with options
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_WITH_OPTIONS, { 
      id: attributeId,
      first: 5 
    });
    const body = await response.json();
    const options = body.data.attribute.options;
    
    // Validate options have cursors
    if (options.edges.length > 0) {
      expect(options.edges[0].cursor).toBeDefined();
    }
  });

  /**
   * Positive Test: Should respect first parameter limit
   */
  test('Should respect first parameter limit', async ({ request }) => {
    // Get a valid attribute ID
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
    
    // Verify the limit is respected
    expect(options.edges.length).toBeLessThanOrEqual(first);
  });

  // ==================== NEGATIVE TEST CASES ====================

  /**
   * Negative Test: Should return null for non-existent attribute ID
   */
  test('Should return null for non-existent attribute ID', async ({ request }) => {
    const nonExistentId = 'non-existent-attribute-12345';
    
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_WITH_OPTIONS, { 
      id: nonExistentId,
      first: 5 
    });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    // Should return null for non-existent attribute
    if (body.data && body.data.attribute === null) {
      expect(body.data.attribute).toBeNull();
    } else if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attribute).toBeNull();
    }
  });

  /**
   * Negative Test: Should return error for empty ID
   */
  test('Should return error for empty ID', async ({ request }) => {
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

  /**
   * Negative Test: Should return error when ID parameter is missing
   */
  test('Should return error when ID parameter is missing', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_WITH_OPTIONS, { first: 5 });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    assertGraphQLETypedError(body);
  });

  /**
   * Negative Test: Should return error for null ID
   */
  test('Should return error for null ID', async ({ request }) => {
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

  /**
   * Negative Test: Should handle invalid ID format
   */
  test('Should handle invalid ID format', async ({ request }) => {
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

  /**
   * Negative Test: Should handle negative first parameter
   */
  test('Should handle negative first parameter', async ({ request }) => {
    // Get a valid attribute ID
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
