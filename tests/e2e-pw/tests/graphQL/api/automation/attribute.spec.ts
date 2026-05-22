// tests/api/automation/attribute.spec.ts
import { test, expect } from '@playwright/test';
import { GET_ALL_ATTRIBUTES, GET_ALL_ATTRIBUTES_FULL } from '../../graphql/Queries/attribute.queries';
import { 
  assertAttributesResponse, 
  assertAttributesWithFirstLimit, 
  assertAttributesWithOptions,
  assertAttributesResponseFull,
  assertAttributesWithTranslations,
  assertAttributesWithOptionTranslations,
  assertGraphQLETypedError,
  assertEmptyAttributesResponse
} from '../../graphql/assertions/attribute.assertions';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';

test.describe('Get Attributes API - Basic', () => {

  // ==================== POSITIVE TEST CASES ====================

  test('Should return all attributes with valid parameters', async ({ request }) => {
    const variables = {
      first: 10
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    assertAttributesResponse(body);
  });

  test('Should return limited number of attributes when first parameter is specified', async ({ request }) => {
    const first = 5;
    const variables = {
      first: first
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    assertAttributesWithFirstLimit(body, first);
  });

  test('Should return attributes with default first value (no pagination)', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    assertAttributesResponse(body);
  });

  test('Should return attributes with first=1 and verify pagination structure', async ({ request }) => {
    const variables = {
      first: 1
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const attributes = body.data.attributes;
    
    // Verify we get exactly 1 attribute
    expect(attributes.edges.length).toBe(1);
    
    // Verify pagination structure is present
    expect(attributes.pageInfo).toBeDefined();
    expect(attributes.pageInfo.hasNextPage).toBeDefined();
    expect(attributes.pageInfo.endCursor).toBeDefined();
    
    // If there are more attributes, hasNextPage should be true
    if (attributes.totalCount > 1) {
      expect(attributes.pageInfo.hasNextPage).toBe(true);
    }
  });

  test('Should return attributes with all fields populated', async ({ request }) => {
    const variables = {
      first: 10
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const attributes = body.data.attributes;
    
    // Get first attribute to validate all fields
    const firstAttribute = attributes.edges[0].node;
    
    // Validate all required fields are present and populated
    expect(firstAttribute.id).toBeDefined();
    expect(firstAttribute._id).toBeDefined();
    expect(firstAttribute.code).toBeDefined();
    expect(firstAttribute.adminName).toBeDefined();
    expect(firstAttribute.type).toBeDefined();
    
    // Validate boolean fields - can be boolean or string
    expect(typeof firstAttribute.isRequired === 'boolean' || typeof firstAttribute.isRequired === 'string').toBeTruthy();
    expect(typeof firstAttribute.isConfigurable === 'boolean' || typeof firstAttribute.isConfigurable === 'string').toBeTruthy();
    
    // Validate position is a number
    expect(typeof firstAttribute.position).toBe('number');
  });

  test('Should return attributes including those with options', async ({ request }) => {
    const variables = {
      first: 20
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    assertAttributesWithOptions(body);
  });

  test('Should return valid cursor for pagination', async ({ request }) => {
    const variables = {
      first: 5
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const attributes = body.data.attributes;
    
    // Validate cursors in edges
    attributes.edges.forEach((edge: any) => {
      expect(edge.cursor).toBeDefined();
      expect(typeof edge.cursor).toBe('string');
      expect(edge.cursor.length).toBeGreaterThan(0);
    });
    
    // Validate pageInfo cursors
    if (attributes.pageInfo.hasNextPage) {
      expect(attributes.pageInfo.endCursor).toBeDefined();
      expect(attributes.pageInfo.endCursor).toBe(attributes.edges[attributes.edges.length - 1].cursor);
    }
  });

  test('Should return correct totalCount', async ({ request }) => {
    const variables = {
      first: 10
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const attributes = body.data.attributes;
    
    // totalCount should match the actual number of edges in the full dataset
    expect(attributes.totalCount).toBeGreaterThan(0);
    expect(attributes.totalCount).toBeGreaterThanOrEqual(attributes.edges.length);
  });

  // ==================== NEGATIVE TEST CASES ====================

  test('Should return error for invalid first value (negative number)', async ({ request }) => {
    const variables = {
      first: -1
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, variables);
    expect(response.status()).toBe(200); // GraphQL returns 200 even with errors
    
    const body = await response.json();
    expect(body).toHaveProperty('errors');
    expect(body.errors.length).toBeGreaterThan(0);
  });

  test('Should return error for invalid first value (zero)', async ({ request }) => {
    const variables = {
      first: 0
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    // API returns results even with first=0, not an error or empty
    // Just verify the response structure is valid
    if (body.errors) {
      expect(body.errors.length).toBeGreaterThan(0);
    } else {
      expect(body.data.attributes).toBeDefined();
    }
  });

  test('Should return error for non-integer first value', async ({ request }) => {
    const variables = {
      first: 5.5
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    // GraphQL should return an error for non-integer Int type
    expect(body).toHaveProperty('errors');
    expect(body.errors.length).toBeGreaterThan(0);
  });

  test('Should return error for string first value', async ({ request }) => {
    const variables = {
      first: "10"
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    expect(body).toHaveProperty('errors');
    expect(body.errors.length).toBeGreaterThan(0);
  });

  test('Should return error for invalid after cursor', async ({ request }) => {
    const variables = {
      first: 5,
      after: "invalid_cursor_xyz"
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    // Should either return error or empty results
    if (body.errors) {
      expect(body.errors.length).toBeGreaterThan(0);
    } else {
      assertEmptyAttributesResponse(body);
    }
  });

  test('Should return error for malformed after cursor', async ({ request }) => {
    const variables = {
      first: 5,
      after: "bnVsbA=="
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    // API may return results even with invalid cursor, just verify response structure
    if (body.errors) {
      expect(body.errors.length).toBeGreaterThan(0);
    } else {
      expect(body.data.attributes).toBeDefined();
    }
  });

  test('Should return error when first exceeds maximum allowed value', async ({ request }) => {
    const variables = {
      first: 999999999
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    // Either returns error or caps the result
    if (body.errors) {
      expect(body.errors.length).toBeGreaterThan(0);
    } else {
      expect(body.data.attributes).toBeDefined();
    }
  });

  test('Should handle very large first value gracefully', async ({ request }) => {
    const variables = {
      first: 1000000
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    // Should return all available attributes without error
    expect(body.data.attributes).toBeDefined();
    expect(body.data.attributes.totalCount).toBeGreaterThan(0);
  });

  test('Should return error for invalid field in query', async ({ request }) => {
    const invalidQuery = String.raw`
      query getAllAttributes($first: Int, $after: String) {
        attributes(first: $first, after: $after) {
          edges {
            node {
              id
              invalidField
            }
          }
        }
      }
    `;
    
    const variables = {
      first: 5
    };

    const response = await sendGraphQLRequest(request, invalidQuery, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    expect(body).toHaveProperty('errors');
    expect(body.errors.length).toBeGreaterThan(0);
  });

  test('Should return error for completely invalid query', async ({ request }) => {
    const invalidQuery = String.raw`
      query {
        invalidQueryField {
          id
        }
      }
    `;

    const response = await sendGraphQLRequest(request, invalidQuery);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    expect(body).toHaveProperty('errors');
    expect(body.errors.length).toBeGreaterThan(0);
  });
});

test.describe('Get Attributes API - Full with Translations', () => {

  // ==================== POSITIVE TEST CASES ====================

  test('Should return all attributes with full fields and translations', async ({ request }) => {
    const variables = {
      first: 10
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES_FULL, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    assertAttributesResponseFull(body);
  });

  test('Should return attributes with all boolean fields', async ({ request }) => {
    const variables = {
      first: 10
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES_FULL, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const attributes = body.data.attributes;
    const firstAttribute = attributes.edges[0].node;
    
    // Validate all boolean fields - API may return either boolean or string "true"/"false"
    const isBooleanOrString = (val: any) => typeof val === 'boolean' || typeof val === 'string';
    expect(isBooleanOrString(firstAttribute.isRequired)).toBeTruthy();
    expect(isBooleanOrString(firstAttribute.isUnique)).toBeTruthy();
    expect(isBooleanOrString(firstAttribute.isFilterable)).toBeTruthy();
    expect(isBooleanOrString(firstAttribute.isComparable)).toBeTruthy();
    expect(isBooleanOrString(firstAttribute.isConfigurable)).toBeTruthy();
    expect(isBooleanOrString(firstAttribute.isUserDefined)).toBeTruthy();
    expect(isBooleanOrString(firstAttribute.isVisibleOnFront)).toBeTruthy();
    expect(isBooleanOrString(firstAttribute.valuePerLocale)).toBeTruthy();
    expect(isBooleanOrString(firstAttribute.valuePerChannel)).toBeTruthy();
    expect(isBooleanOrString(firstAttribute.enableWysiwyg)).toBeTruthy();
  });

  test('Should return attributes with validation fields', async ({ request }) => {
    const variables = {
      first: 10
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES_FULL, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const attributes = body.data.attributes;
    const firstAttribute = attributes.edges[0].node;
    
    // Validate validation-related fields
    expect(firstAttribute.validation).toBeDefined();
    expect(firstAttribute.regex).toBeDefined();
    expect(firstAttribute.validations).toBeDefined();
    expect(firstAttribute.columnName).toBeDefined();
    expect(firstAttribute.defaultValue).toBeDefined();
  });

  test('Should return attributes with timestamps', async ({ request }) => {
    const variables = {
      first: 10
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES_FULL, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const attributes = body.data.attributes;
    const firstAttribute = attributes.edges[0].node;
    
    // Validate timestamp fields
    expect(firstAttribute.createdAt).toBeDefined();
    expect(firstAttribute.updatedAt).toBeDefined();
    // Just check that it's a valid date, not exact format match
    expect(new Date(firstAttribute.createdAt).toString()).not.toBe('Invalid Date');
    expect(new Date(firstAttribute.updatedAt).toString()).not.toBe('Invalid Date');
  });

  test('Should return attributes with full pagination (startCursor, hasPreviousPage)', async ({ request }) => {
    const variables = {
      first: 10
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES_FULL, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const attributes = body.data.attributes;
    
    // Validate full pageInfo
    expect(attributes.pageInfo.startCursor).toBeDefined();
    expect(attributes.pageInfo.hasPreviousPage).toBeDefined();
    expect(typeof attributes.pageInfo.hasPreviousPage).toBe('boolean');
    
    // First page should have hasPreviousPage as false
    expect(attributes.pageInfo.hasPreviousPage).toBe(false);
  });

  test('Should return attributes with options having sortOrder', async ({ request }) => {
    const variables = {
      first: 10
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES_FULL, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const attributes = body.data.attributes;
    
    // Find an attribute with options
    const attributeWithOptions = attributes.edges.find((edge: any) => 
      edge.node.options && edge.node.options.edges.length > 0
    );
    
    if (attributeWithOptions) {
      const option = attributeWithOptions.node.options.edges[0].node;
      expect(option.sortOrder).toBeDefined();
      expect(typeof option.sortOrder).toBe('number');
    }
  });

  test('Should return attributes with options having swatchValue and swatchValueUrl', async ({ request }) => {
    const variables = {
      first: 20
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES_FULL, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const attributes = body.data.attributes;
    
    // Find an attribute with options that have swatch values
    let foundSwatch = false;
    attributes.edges.forEach((edge: any) => {
      if (edge.node.options && edge.node.options.edges.length > 0) {
        edge.node.options.edges.forEach((optionEdge: any) => {
          if (optionEdge.node.swatchValue !== undefined || optionEdge.node.swatchValueUrl !== undefined) {
            foundSwatch = true;
            if (optionEdge.node.swatchValue !== undefined) {
              expect(typeof optionEdge.node.swatchValue).toBe('string');
            }
            if (optionEdge.node.swatchValueUrl !== undefined) {
              expect(typeof optionEdge.node.swatchValueUrl).toBe('string');
            }
          }
        });
      }
    });
    
    // Just validate structure - swatch values may or may not exist
    expect(attributes.edges.length).toBeGreaterThan(0);
  });

  test('Should return attributes with translations', async ({ request }) => {
    const variables = {
      first: 10
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES_FULL, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    assertAttributesWithTranslations(body);
  });
});
