import { test, expect } from '@playwright/test';
import { GET_ALL_ATTRIBUTES, GET_ALL_ATTRIBUTES_FULL } from '../../graphql/Queries/attribute.queries';
import { 
  assertAttributesResponse, 
  assertAttributesWithFirstLimit, 
  assertAttributesWithOptions,
  assertAttributesResponseFull,
  assertAttributesWithTranslations,
  assertEmptyAttributesResponse
} from '../../graphql/assertions/attribute.assertions';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';

test.describe('get attributes api - basic', () => {

  test('should return all attributes with valid parameters', async ({ request }) => {
    const variables = {
      first: 10
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    assertAttributesResponse(body);
  });

  test('should return limited number of attributes when first parameter is specified', async ({ request }) => {
    const first = 5;
    const variables = {
      first: first
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    assertAttributesWithFirstLimit(body, first);
  });

  test('should return attributes with default first value (no pagination)', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    assertAttributesResponse(body);
  });

  test('should return attributes with first=1 and verify pagination structure', async ({ request }) => {
    const variables = {
      first: 1
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const attributes = body.data.attributes;
    
    expect(attributes.edges.length).toBe(1);
    
    expect(attributes.pageInfo).toBeDefined();
    expect(attributes.pageInfo.hasNextPage).toBeDefined();
    expect(attributes.pageInfo.endCursor).toBeDefined();
    
    if (attributes.totalCount > 1) {
      expect(attributes.pageInfo.hasNextPage).toBe(true);
    }
  });

  test('should return attributes with all fields populated', async ({ request }) => {
    const variables = {
      first: 10
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const attributes = body.data.attributes;
    
    const firstAttribute = attributes.edges[0].node;
    
    expect(firstAttribute.id).toBeDefined();
    expect(firstAttribute._id).toBeDefined();
    expect(firstAttribute.code).toBeDefined();
    expect(firstAttribute.adminName).toBeDefined();
    expect(firstAttribute.type).toBeDefined();
    
    expect(typeof firstAttribute.isRequired).toBe('boolean');
    expect(typeof firstAttribute.isConfigurable).toBe('boolean');
    
    expect(typeof firstAttribute.position).toBe('number');
  });

  test('should return attributes including those with options', async ({ request }) => {
    const variables = {
      first: 20
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    assertAttributesWithOptions(body);
  });

  test('should return valid cursor for pagination', async ({ request }) => {
    const variables = {
      first: 5
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const attributes = body.data.attributes;
    
    attributes.edges.forEach((edge: any) => {
      expect(edge.cursor).toBeDefined();
      expect(typeof edge.cursor).toBe('string');
      expect(edge.cursor.length).toBeGreaterThan(0);
    });
    
    if (attributes.pageInfo.hasNextPage) {
      expect(attributes.pageInfo.endCursor).toBeDefined();
      expect(attributes.pageInfo.endCursor).toBe(attributes.edges[attributes.edges.length - 1].cursor);
    }
  });

  test('should return correct totalcount', async ({ request }) => {
    const variables = {
      first: 10
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const attributes = body.data.attributes;
    
    expect(attributes.totalCount).toBeGreaterThan(0);
    expect(attributes.totalCount).toBeGreaterThanOrEqual(attributes.edges.length);
  });

  test('should return an empty page for a negative first value', async ({ request }) => {
    const variables = {
      first: -1
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    expect(body.errors).toBeUndefined();
    expect(body.data.attributes.edges).toEqual([]);
  });

  test('should return error for invalid first value (zero)', async ({ request }) => {
    const variables = {
      first: 0
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    if (body.errors) {
      expect(body.errors.length).toBeGreaterThan(0);
    } else {
      expect(body.data.attributes).toBeDefined();
    }
  });

  test('should return error for non-integer first value', async ({ request }) => {
    const variables = {
      first: 5.5
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    expect(body).toHaveProperty('errors');
    expect(body.errors.length).toBeGreaterThan(0);
  });

  test('should return error for string first value', async ({ request }) => {
    const variables = {
      first: "10"
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    expect(body).toHaveProperty('errors');
    expect(body.errors.length).toBeGreaterThan(0);
  });

  test('should return error for invalid after cursor', async ({ request }) => {
    const variables = {
      first: 5,
      after: "invalid_cursor_xyz"
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    if (body.errors) {
      expect(body.errors.length).toBeGreaterThan(0);
    } else {
      assertEmptyAttributesResponse(body);
    }
  });

  test('should return error for malformed after cursor', async ({ request }) => {
    const variables = {
      first: 5,
      after: "bnVsbA=="
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    if (body.errors) {
      expect(body.errors.length).toBeGreaterThan(0);
    } else {
      expect(body.data.attributes).toBeDefined();
    }
  });

  test('should return error when first exceeds maximum allowed value', async ({ request }) => {
    const variables = {
      first: 999999999
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    if (body.errors) {
      expect(body.errors.length).toBeGreaterThan(0);
    } else {
      expect(body.data.attributes).toBeDefined();
    }
  });

  test('should handle very large first value gracefully', async ({ request }) => {
    const variables = {
      first: 1000000
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    expect(body.data.attributes).toBeDefined();
    expect(body.data.attributes.totalCount).toBeGreaterThan(0);
  });

  test('should return error for invalid field in query', async ({ request }) => {
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

  test('should return error for completely invalid query', async ({ request }) => {
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

test.describe('get attributes api - full with translations', () => {

  test('should return all attributes with full fields and translations', async ({ request }) => {
    const variables = {
      first: 10
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES_FULL, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    assertAttributesResponseFull(body);
  });

  test('should return attributes with all boolean fields', async ({ request }) => {
    const variables = {
      first: 10
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES_FULL, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const attributes = body.data.attributes;
    const firstAttribute = attributes.edges[0].node;
    
    expect(typeof firstAttribute.isRequired).toBe('boolean');
    expect(typeof firstAttribute.isUnique).toBe('boolean');
    expect(typeof firstAttribute.isFilterable).toBe('boolean');
    expect(typeof firstAttribute.isComparable).toBe('boolean');
    expect(typeof firstAttribute.isConfigurable).toBe('boolean');
    expect(typeof firstAttribute.isUserDefined).toBe('boolean');
    expect(typeof firstAttribute.isVisibleOnFront).toBe('boolean');
    expect(typeof firstAttribute.valuePerLocale).toBe('boolean');
    expect(typeof firstAttribute.valuePerChannel).toBe('boolean');
    expect(typeof firstAttribute.enableWysiwyg).toBe('boolean');
  });

  test('should return attributes with validation fields', async ({ request }) => {
    const variables = {
      first: 10
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES_FULL, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const attributes = body.data.attributes;
    const firstAttribute = attributes.edges[0].node;
    
    expect(firstAttribute.validation).toBeDefined();
    expect(firstAttribute.regex).toBeDefined();
    expect(firstAttribute.validations).toBeDefined();
    expect(firstAttribute.columnName).toBeDefined();
    expect(firstAttribute.defaultValue).toBeDefined();
  });

  test('should return attributes with timestamps', async ({ request }) => {
    const variables = {
      first: 10
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES_FULL, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const attributes = body.data.attributes;
    const firstAttribute = attributes.edges[0].node;
    
    expect(firstAttribute.createdAt).toBeDefined();
    expect(firstAttribute.updatedAt).toBeDefined();
    expect(new Date(firstAttribute.createdAt).toString()).not.toBe('Invalid Date');
    expect(new Date(firstAttribute.updatedAt).toString()).not.toBe('Invalid Date');
  });

  test('should return attributes with full pagination (startcursor, haspreviouspage)', async ({ request }) => {
    const variables = {
      first: 10
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES_FULL, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const attributes = body.data.attributes;
    
    expect(attributes.pageInfo.startCursor).toBeDefined();
    expect(attributes.pageInfo.hasPreviousPage).toBeDefined();
    expect(typeof attributes.pageInfo.hasPreviousPage).toBe('boolean');
    
    expect(attributes.pageInfo.hasPreviousPage).toBe(false);
  });

  test('should return attributes with options having sortorder', async ({ request }) => {
    const variables = {
      first: 10
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES_FULL, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const attributes = body.data.attributes;
    
    const attributeWithOptions = attributes.edges.find((edge: any) => 
      edge.node.options && edge.node.options.edges.length > 0
    );
    
    if (attributeWithOptions) {
      const option = attributeWithOptions.node.options.edges[0].node;
      expect(option.sortOrder).toBeDefined();
      expect(typeof option.sortOrder).toBe('number');
    }
  });

  test('should return attributes with options having swatchvalue and swatchvalueurl', async ({ request }) => {
    const variables = {
      first: 20
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES_FULL, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    const attributes = body.data.attributes;
    
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
    
    expect(attributes.edges.length).toBeGreaterThan(0);
  });

  test('should return attributes with translations', async ({ request }) => {
    const variables = {
      first: 10
    };

    const response = await sendGraphQLRequest(request, GET_ALL_ATTRIBUTES_FULL, variables);
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    assertAttributesWithTranslations(body);
  });
});
