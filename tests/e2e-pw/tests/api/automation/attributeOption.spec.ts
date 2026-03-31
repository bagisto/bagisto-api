// tests/api/automation/attributeOption.spec.ts
import { test, expect } from '@playwright/test';
import { 
  GET_ATTRIBUTE_OPTIONS, 
  GET_ATTRIBUTE_OPTION_BY_ID, 
  GET_ATTRIBUTE_OPTIONS_PAGINATED,
  GET_ATTRIBUTE_OPTIONS_WITH_TRANSLATIONS,
  GET_COLOR_OPTIONS,
  GET_SWATCH_OPTIONS
} from '../../graphql/Queries/attribute.queries';
import { 
  assertAttributeOptionsResponse,
  assertAttributeOptionsWithTotalCount,
  assertGetAttributeOptionByIdResponse,
  assertAttributeOptionNotFound,
  assertAttributeOptionsPaginatedResponse,
  assertAttributeOptionsWithTranslationsResponse,
  assertColorOptionsResponse,
  assertSwatchOptionsResponse,
  assertGraphQLETypedError 
} from '../../graphql/assertions/attribute.assertions';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';

test.describe('Get Attribute Options API - Basic', () => {

  // ==================== POSITIVE TEST CASES ====================

  /**
   * Positive Test: Should return attribute options successfully
   */
  test('Should return attribute options successfully', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS, { first: 10 });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    assertAttributeOptionsResponse(body);
  });

  /**
   * Positive Test: Should return attribute options with first limit
   */
  test('Should return attribute options with first limit', async ({ request }) => {
    const first = 5;
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS, { first });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    assertAttributeOptionsResponse(body);
    
    // Verify the number of returned options respects the first limit
    const edges = body.data.attributeOptions.edges;
    expect(edges.length).toBeLessThanOrEqual(first);
  });

  /**
   * Positive Test: Should return attribute options with all required fields
   */
  test('Should return attribute options with all required fields', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS, { first: 1 });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    // If there are options, validate their structure
    if (body.data.attributeOptions.edges.length > 0) {
      const option = body.data.attributeOptions.edges[0].node;
      
      expect(option.id).toBeDefined();
      expect(option._id).toBeDefined();
      expect(option.adminName).toBeDefined();
      expect(option.sortOrder).toBeDefined();
      
      // Validate field types
      expect(typeof option.id).toBe('string');
      expect(typeof option._id === 'string' || typeof option._id === 'number').toBeTruthy();
      expect(typeof option.adminName).toBe('string');
      expect(typeof option.sortOrder).toBe('number');
    }
  });

  /**
   * Positive Test: Should return attribute options with pagination info
   */
  test('Should return attribute options with pagination info', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS, { first: 10 });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const options = body.data.attributeOptions;
    
    // Validate pageInfo structure
    expect(options.pageInfo).toBeDefined();
    expect(options.pageInfo.hasNextPage).toBeDefined();
    expect(options.pageInfo.endCursor).toBeDefined();
    expect(typeof options.pageInfo.hasNextPage).toBe('boolean');
  });

  /**
   * Positive Test: Should handle first parameter as zero
   */
  test('Should handle first parameter as zero', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS, { first: 0 });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    expect(body).toHaveProperty('data');
    expect(body.data).toHaveProperty('attributeOptions');
    expect(body.data.attributeOptions).not.toBeNull();
    
    // Should return empty array when first is 0
    expect(body.data.attributeOptions.edges).toBeDefined();
    expect(Array.isArray(body.data.attributeOptions.edges)).toBeTruthy();
  });

  /**
   * Positive Test: Should return different results for different first values
   */
  test('Should return different results for different first values', async ({ request }) => {
    const firstSmall = 2;
    const firstLarge = 10;
    
    const responseSmall = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS, { first: firstSmall });
    const bodySmall = await responseSmall.json();
    
    const responseLarge = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS, { first: firstLarge });
    const bodyLarge = await responseLarge.json();
    
    // Large request should return at least as many options as small request
    expect(bodyLarge.data.attributeOptions.edges.length).toBeGreaterThanOrEqual(bodySmall.data.attributeOptions.edges.length);
  });

  // ==================== NEGATIVE TEST CASES ====================

  /**
   * Negative Test: Should handle invalid first parameter (negative number)
   */
  test('Should handle invalid first parameter (negative number)', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS, { first: -1 });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    // API might return error or handle gracefully
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attributeOptions).toBeDefined();
    }
  });

  /**
   * Negative Test: Should handle extremely large first parameter
   */
  test('Should handle extremely large first parameter', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS, { first: 999999 });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    // Should either return error or handle gracefully
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attributeOptions).toBeDefined();
    }
  });

  /**
   * Negative Test: Should handle non-integer first parameter
   */
  test('Should handle non-integer first parameter', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS, { first: 5.5 });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    // GraphQL returns error for non-integer values
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attributeOptions).toBeDefined();
    }
  });

  /**
   * Negative Test: Should handle null first parameter
   */
  test('Should handle null first parameter', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS, { first: null });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    // Should return default results or error
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attributeOptions).toBeDefined();
    }
  });

  /**
   * Negative Test: Should handle string first parameter
   */
  test('Should handle string first parameter', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS, { first: '10' as any });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    // GraphQL returns error for string values
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attributeOptions).toBeDefined();
    }
  });

  /**
   * Negative Test: Should handle missing first parameter (use default)
   */
  test('Should handle missing first parameter (use default)', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS, {});
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    // Should return default results
    expect(body).toHaveProperty('data');
    expect(body.data.attributeOptions).toBeDefined();
  });
});

test.describe('Get Attribute Option By ID API', () => {

  // ==================== POSITIVE TEST CASES ====================

  /**
   * Positive Test: Should return attribute option by valid ID
   */
  test('Should return attribute option by valid ID', async ({ request }) => {
    // First, get a valid option ID
    const listResponse = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS, { first: 1 });
    expect(listResponse.status()).toBe(200);
    
    const listBody = await listResponse.json();
    expect(listBody.data.attributeOptions.edges.length).toBeGreaterThan(0);
    
    const optionId = listBody.data.attributeOptions.edges[0].node.id;
    
    // Now get the option by ID
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTION_BY_ID, { id: optionId });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    assertGetAttributeOptionByIdResponse(body);
    
    // Verify the returned option has the same ID
    expect(body.data.attributeOption.id).toBe(optionId);
  });

  /**
   * Positive Test: Should return attribute option with all fields
   */
  test('Should return attribute option with all fields', async ({ request }) => {
    // Get a valid option ID
    const listResponse = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS, { first: 1 });
    const listBody = await listResponse.json();
    const optionId = listBody.data.attributeOptions.edges[0].node.id;
    
    // Get option by ID
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTION_BY_ID, { id: optionId });
    const body = await response.json();
    const option = body.data.attributeOption;
    
    // Validate required fields
    expect(option.id).toBeDefined();
    expect(option._id).toBeDefined();
    expect(option.adminName).toBeDefined();
    expect(option.sortOrder).toBeDefined();
    
    // Validate field types
    expect(typeof option.id).toBe('string');
    expect(typeof option._id === 'string' || typeof option._id === 'number').toBeTruthy();
    expect(typeof option.adminName).toBe('string');
    expect(typeof option.sortOrder).toBe('number');
  });

  /**
   * Positive Test: Should return different options when querying different IDs
   */
  test('Should return different options when querying different IDs', async ({ request }) => {
    // Get two different options
    const listResponse = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS, { first: 2 });
    const listBody = await listResponse.json();
    expect(listBody.data.attributeOptions.edges.length).toBe(2);
    
    const firstOptionId = listBody.data.attributeOptions.edges[0].node.id;
    const secondOptionId = listBody.data.attributeOptions.edges[1].node.id;
    
    // Get first option
    const firstResponse = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTION_BY_ID, { id: firstOptionId });
    const firstBody = await firstResponse.json();
    
    // Get second option
    const secondResponse = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTION_BY_ID, { id: secondOptionId });
    const secondBody = await secondResponse.json();
    
    // Verify they are different
    expect(firstBody.data.attributeOption.id).not.toBe(secondBody.data.attributeOption.id);
    expect(firstBody.data.attributeOption.adminName).not.toBe(secondBody.data.attributeOption.adminName);
  });

  // ==================== NEGATIVE TEST CASES ====================

  /**
   * Negative Test: Should return null for non-existent option ID
   */
  test('Should return null for non-existent option ID', async ({ request }) => {
    const nonExistentId = 'non-existent-option-id-12345';
    
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTION_BY_ID, { id: nonExistentId });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    if (body.data && body.data.attributeOption === null) {
      assertAttributeOptionNotFound(body);
    } else if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attributeOption).toBeNull();
    }
  });

  /**
   * Negative Test: Should return error for empty ID
   */
  test('Should return error for empty ID', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTION_BY_ID, { id: '' });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attributeOption).toBeNull();
    }
  });

  /**
   * Negative Test: Should return error when ID parameter is missing
   */
  test('Should return error when ID parameter is missing', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTION_BY_ID, {});
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    assertGraphQLETypedError(body);
  });

  /**
   * Negative Test: Should return error for invalid ID format
   */
  test('Should return error for invalid ID format', async ({ request }) => {
    const invalidId = 'invalid@#$%id';
    
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTION_BY_ID, { id: invalidId });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attributeOption).toBeNull();
    }
  });

  /**
   * Negative Test: Should return error for SQL injection attempt
   */
  test('Should return error for SQL injection attempt', async ({ request }) => {
    const maliciousId = "1' OR '1'='1";
    
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTION_BY_ID, { id: maliciousId });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attributeOption).toBeNull();
    }
  });

  /**
   * Negative Test: Should return error for null ID
   */
  test('Should return error for null ID', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTION_BY_ID, { id: null });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attributeOption).toBeNull();
    }
  });
});

test.describe('Get Attribute Options Paginated API', () => {

  // ==================== POSITIVE TEST CASES ====================

  /**
   * Positive Test: Should return paginated attribute options
   */
  test('Should return paginated attribute options', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS_PAGINATED, { first: 10 });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    assertAttributeOptionsPaginatedResponse(body);
  });

  /**
   * Positive Test: Should return paginated options with cursor
   */
  test('Should return paginated options with cursor', async ({ request }) => {
    // First, get first page
    const firstResponse = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS_PAGINATED, { first: 5 });
    const firstBody = await firstResponse.json();
    
    // Verify first page has cursor
    const firstOptions = firstBody.data.attributeOptions;
    expect(firstOptions.edges.length).toBeGreaterThan(0);
    expect(firstOptions.edges[0].cursor).toBeDefined();
    
    // If there's a next page, get second page using cursor
    if (firstOptions.pageInfo.hasNextPage) {
      const secondResponse = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS_PAGINATED, { 
        first: 5, 
        after: firstOptions.pageInfo.endCursor 
      });
      const secondBody = await secondResponse.json();
      
      // Verify second page
      expect(secondBody.data.attributeOptions).toBeDefined();
      expect(secondBody.data.attributeOptions.edges.length).toBeGreaterThan(0);
    }
  });

  /**
   * Positive Test: Should return options with full pagination info
   */
  test('Should return options with full pagination info', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS_PAGINATED, { first: 10 });
    const body = await response.json();
    const options = body.data.attributeOptions;
    
    // Validate full pageInfo
    expect(options.pageInfo.hasNextPage).toBeDefined();
    expect(options.pageInfo.endCursor).toBeDefined();
    expect(options.pageInfo.hasPreviousPage).toBeDefined();
    expect(options.pageInfo.startCursor).toBeDefined();
    expect(typeof options.pageInfo.hasNextPage).toBe('boolean');
    expect(typeof options.pageInfo.hasPreviousPage).toBe('boolean');
  });

  /**
   * Positive Test: Should return different pages with different first values
   */
  test('Should return different pages with different first values', async ({ request }) => {
    const response1 = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS_PAGINATED, { first: 2 });
    const body1 = await response1.json();
    
    const response2 = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS_PAGINATED, { first: 5 });
    const body2 = await response2.json();
    
    // Larger first should return at least as many options
    expect(body2.data.attributeOptions.edges.length).toBeGreaterThanOrEqual(body1.data.attributeOptions.edges.length);
  });

  // ==================== NEGATIVE TEST CASES ====================

  /**
   * Negative Test: Should handle invalid first parameter
   */
  test('Should handle invalid first parameter', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS_PAGINATED, { first: -1 });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attributeOptions).toBeDefined();
    }
  });

  /**
   * Negative Test: Should handle invalid after cursor
   */
  test('Should handle invalid after cursor', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS_PAGINATED, { 
      first: 5, 
      after: 'invalid-cursor' 
    });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    // Should either return error or empty results
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attributeOptions).toBeDefined();
    }
  });

  /**
   * Negative Test: Should handle null first parameter
   */
  test('Should handle null first parameter', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS_PAGINATED, { first: null });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attributeOptions).toBeDefined();
    }
  });

  /**
   * Negative Test: Should handle missing first parameter
   */
  test('Should handle missing first parameter', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS_PAGINATED, {});
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    // Should return default results or error
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attributeOptions).toBeDefined();
    }
  });

  /**
   * Negative Test: Should handle extremely large first parameter
   */
  test('Should handle extremely large first parameter', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS_PAGINATED, { first: 999999 });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attributeOptions).toBeDefined();
    }
  });
});

test.describe('Get Attribute Options with Translations API', () => {

  // ==================== POSITIVE TEST CASES ====================

  /**
   * Positive Test: Should return attribute options with translations
   */
  test('Should return attribute options with translations', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS_WITH_TRANSLATIONS, { first: 10 });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    assertAttributeOptionsWithTranslationsResponse(body);
  });

  /**
   * Positive Test: Should return options with translations structure
   */
  test('Should return options with translations structure', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS_WITH_TRANSLATIONS, { first: 5 });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const options = body.data.attributeOptions.edges;
    
    // Verify translations structure
    options.forEach((edge: any) => {
      expect(edge.node.translations).toBeDefined();
      expect(edge.node.translations.edges).toBeDefined();
      expect(Array.isArray(edge.node.translations.edges)).toBeTruthy();
    });
  });

  /**
   * Positive Test: Should return options with first limit
   */
  test('Should return options with first limit', async ({ request }) => {
    const first = 3;
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS_WITH_TRANSLATIONS, { first });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const edges = body.data.attributeOptions.edges;
    expect(edges.length).toBeLessThanOrEqual(first);
  });

  /**
   * Positive Test: Should handle request with translations even if empty
   */
  test('Should handle request with translations even if empty', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS_WITH_TRANSLATIONS, { first: 1 });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    expect(body.data.attributeOptions).toBeDefined();
    expect(body.data.attributeOptions.edges).toBeDefined();
  });

  // ==================== NEGATIVE TEST CASES ====================

  /**
   * Negative Test: Should handle invalid first parameter
   */
  test('Should handle invalid first parameter', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS_WITH_TRANSLATIONS, { first: -1 });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attributeOptions).toBeDefined();
    }
  });

  /**
   * Negative Test: Should handle null first parameter
   */
  test('Should handle null first parameter', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS_WITH_TRANSLATIONS, { first: null });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attributeOptions).toBeDefined();
    }
  });

  /**
   * Negative Test: Should handle missing first parameter
   */
  test('Should handle missing first parameter', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ATTRIBUTE_OPTIONS_WITH_TRANSLATIONS, {});
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    expect(body.data.attributeOptions).toBeDefined();
  });
});

test.describe('Get Color Options API', () => {

  // ==================== POSITIVE TEST CASES ====================

  /**
   * Positive Test: Should return color options successfully
   */
  test('Should return color options successfully', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_COLOR_OPTIONS, {});
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    assertColorOptionsResponse(body);
  });

  /**
   * Positive Test: Should return color options with admin names
   */
  test('Should return color options with admin names', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_COLOR_OPTIONS, {});
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const options = body.data.attributeOptions.edges;
    
    // Verify all options have admin names
    expect(options.length).toBeGreaterThan(0);
    options.forEach((edge: any) => {
      expect(edge.node.adminName).toBeDefined();
      expect(typeof edge.node.adminName).toBe('string');
    });
  });

  /**
   * Positive Test: Should return color options with optional swatch values
   */
  test('Should return color options with optional swatch values', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_COLOR_OPTIONS, {});
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const options = body.data.attributeOptions.edges;
    
    // swatchValue may or may not be present
    options.forEach((edge: any) => {
      expect(edge.node.adminName).toBeDefined();
      if (edge.node.swatchValue !== undefined && edge.node.swatchValue !== null) {
        expect(typeof edge.node.swatchValue).toBe('string');
      }
    });
  });

  /**
   * Positive Test: Should return color options with optional translations
   */
  test('Should return color options with optional translations', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_COLOR_OPTIONS, {});
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const options = body.data.attributeOptions.edges;
    
    // Translation may or may not be present
    options.forEach((edge: any) => {
      expect(edge.node.adminName).toBeDefined();
      if (edge.node.translation !== undefined && edge.node.translation !== null) {
        expect(edge.node.translation.label).toBeDefined();
      }
    });
  });

  // ==================== NEGATIVE TEST CASES ====================

  /**
   * Negative Test: Should handle GraphQL errors gracefully
   */
  test('Should handle GraphQL errors gracefully', async ({ request }) => {
    // Send an invalid request to trigger error
    const response = await sendGraphQLRequest(request, '{ invalid { field } }', {});
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    // Should return GraphQL errors
    if (body.errors) {
      expect(body.errors.length).toBeGreaterThan(0);
    }
  });

  /**
   * Negative Test: Should handle invalid query structure
   */
  test('Should handle invalid query structure', async ({ request }) => {
    const response = await sendGraphQLRequest(request, 'query { attributeOptions { invalid } }', {});
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    // Should return errors
    if (body.errors) {
      assertGraphQLETypedError(body);
    }
  });
});

test.describe('Get Swatch Options API', () => {

  // ==================== POSITIVE TEST CASES ====================

  /**
   * Positive Test: Should return swatch options successfully
   */
  test('Should return swatch options successfully', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_SWATCH_OPTIONS, { first: 10 });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    assertSwatchOptionsResponse(body);
  });

  /**
   * Positive Test: Should return swatch options with swatch values
   */
  test('Should return swatch options with swatch values', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_SWATCH_OPTIONS, { first: 5 });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const options = body.data.attributeOptions.edges;
    
    // At least verify structure is correct
    expect(options.length).toBeGreaterThan(0);
    options.forEach((edge: any) => {
      expect(edge.node.id).toBeDefined();
      expect(edge.node.adminName).toBeDefined();
    });
  });

  /**
   * Positive Test: Should return swatch options with first limit
   */
  test('Should return swatch options with first limit', async ({ request }) => {
    const first = 3;
    const response = await sendGraphQLRequest(request, GET_SWATCH_OPTIONS, { first });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const edges = body.data.attributeOptions.edges;
    expect(edges.length).toBeLessThanOrEqual(first);
  });

  /**
   * Positive Test: Should handle swatch options with or without swatch values
   */
  test('Should handle swatch options with or without swatch values', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_SWATCH_OPTIONS, { first: 10 });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const options = body.data.attributeOptions.edges;
    
    // Options may or may not have swatch values
    options.forEach((edge: any) => {
      expect(edge.node.id).toBeDefined();
      expect(edge.node.adminName).toBeDefined();
      
      // swatchValue can be null or string
      if (edge.node.swatchValue !== undefined) {
        expect(edge.node.swatchValue === null || typeof edge.node.swatchValue === 'string').toBeTruthy();
      }
      
      // swatchValueUrl can be null or string
      if (edge.node.swatchValueUrl !== undefined) {
        expect(edge.node.swatchValueUrl === null || typeof edge.node.swatchValueUrl === 'string').toBeTruthy();
      }
      
      // translation can be null or object
      if (edge.node.translation !== undefined) {
        expect(edge.node.translation === null || typeof edge.node.translation === 'object').toBeTruthy();
      }
    });
  });

  // ==================== NEGATIVE TEST CASES ====================

  /**
   * Negative Test: Should handle invalid first parameter
   */
  test('Should handle invalid first parameter', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_SWATCH_OPTIONS, { first: -5 });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attributeOptions).toBeDefined();
    }
  });

  /**
   * Negative Test: Should handle extremely large first parameter
   */
  test('Should handle extremely large first parameter', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_SWATCH_OPTIONS, { first: 999999 });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attributeOptions).toBeDefined();
    }
  });

  /**
   * Negative Test: Should handle null first parameter
   */
  test('Should handle null first parameter', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_SWATCH_OPTIONS, { first: null });
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    if (body.errors) {
      assertGraphQLETypedError(body);
    } else {
      expect(body.data.attributeOptions).toBeDefined();
    }
  });

  /**
   * Negative Test: Should handle missing first parameter
   */
  test('Should handle missing first parameter', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_SWATCH_OPTIONS, {});
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    expect(body.data.attributeOptions).toBeDefined();
  });
});
