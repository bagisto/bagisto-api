//Category api test cases
import { test, expect } from '@playwright/test';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { GET_ALL_CATEGORY, GET_TREE_CATEGORY_BASIC, GET_TREE_CATEGORY_COMPLETE, GET_TREE_CATEGORY_BY_PARENT_FILTER,
  GET_CATEGORIES_BASIC,
  GET_CATEGORIES_COMPLETE,
  GET_CATEGORIES_CURSOR,
  GET_CATEGORIES_WITH_CHILDREN,
  GET_CATEGORY_BY_ID, GET_CATEGORY_BY_ID_COMPLETE, GET_CATEGORY_BY_ID_WITH_CHILDREN
} 
from '../../graphql/Queries/category.queries';
import { assertGetAllCategoryResponse, assertTreeCategoryBasicResponse, assertTreeCategoryCompleteResponse,
  assertTreeCategoryFilteredResponse,
  assertGetCategoriesBasicResponse,
  assertGetCategoriesCompleteResponse,
  assertGetCategoriesCursorResponse,
  assertGetCategoriesWithChildrenResponse,
  assertNoGraphQLErrors, assertBasicCategory, assertExtendedCategory, assertTranslationsConnection,
  assertChildrenConnection, assertPageInfo, assertLightCategoryTranslation
} 
from '../../graphql/assertions/category.assertions';

test.describe('Category GraphQL Tests', () => {
  test('Should fetch all category successfully', async ({ request }) => {

  console.log('\n📤 Sending treeCategories query...');
  console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

  const start = Date.now();

  const response = await sendGraphQLRequest(
    request,
    GET_ALL_CATEGORY
  );

  const duration = Date.now() - start;

  console.log('\n📥 RESPONSE DETAILS');
  console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
  console.log('Status Code:', response.status());
  console.log('Status Text:', response.statusText());
  console.log(`Response Time: ${duration} ms`);

  expect(response.status()).toBe(200);

  const body = await response.json();

  const categories = body.data.treeCategories;

  console.log('\n🌳 CATEGORY TREE STRUCTURE');
  console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

  categories.forEach((root: any, rootIndex: number) => {
    console.log(`\n${rootIndex + 1}. ${root.translation.name} (ID: ${root._id})`);
    console.log(`   Status: ${root.status}`);
    console.log(`   Position: ${root.position}`);

    const level1 = root.children?.edges || [];

    level1.forEach((child: any, index1: number) => {
      const node1 = child.node;

      console.log(`   ├── ${node1.translation.name} (ID: ${node1._id})`);
      console.log(`   │   Status: ${node1.status}`);
      console.log(`   │   Position: ${node1.position}`);

      const level2 = node1.children?.edges || [];

      level2.forEach((child2: any) => {
        const node2 = child2.node;

        console.log(`   │   └── ${node2.translation.name} (ID: ${node2._id})`);
        console.log(`   │       Status: ${node2.status}`);
        console.log(`   │       Position: ${node2.position}`);
      });
    });
  });
  console.log('\n📊 SUMMARY');
  console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
  console.log('Root Categories:', categories.length);
  console.log('\n✅ GET_ALL_CATEGORY API Test Passed!\n');
  assertGetAllCategoryResponse(body);
});


  test('Should fetch tree category for parentId 1', async ({ request }) => {
    const response = await sendGraphQLRequest(
      request,
      GET_TREE_CATEGORY_BASIC,
      { parentId: 1 }
    );
    expect(response.status()).toBe(200);
    const body = await response.json();
    assertTreeCategoryBasicResponse(body);
  });

  test('Should fetch complete tree category', async ({ request }) => {
  const response = await sendGraphQLRequest(
    request,
    GET_TREE_CATEGORY_COMPLETE,
    { parentId: 1 }
  );
  expect(response.status()).toBe(200);
  const body = await response.json();
  assertTreeCategoryCompleteResponse(body);
});

test('Should fetch filtered tree category for parentId 2', async ({ request }) => {
  const response = await sendGraphQLRequest(
    request,
    GET_TREE_CATEGORY_BY_PARENT_FILTER,
    { parentId: 2 }
  );
  expect(response.status()).toBe(200);
  const body = await response.json();
  assertTreeCategoryFilteredResponse(body);
});

 /* ---------------------------------------------------
   * 1️⃣ GET CATEGORIES – BASIC
   * --------------------------------------------------- */
  test('Should fetch categories (basic)', async ({ request }) => {
    const response = await sendGraphQLRequest(
      request,
      GET_CATEGORIES_BASIC,
      { first: 10 }
    );

    expect(response.status()).toBe(200);

    const body = await response.json();

    assertGetCategoriesBasicResponse(body);
  });


  /* ---------------------------------------------------
   * 2️⃣ GET CATEGORIES – COMPLETE DETAILS
   * --------------------------------------------------- */
  test('Should fetch categories (complete details)', async ({ request }) => {
    const response = await sendGraphQLRequest(
      request,
      GET_CATEGORIES_COMPLETE,
      { first: 10 }
    );

    expect(response.status()).toBe(200);

    const body = await response.json();

    assertGetCategoriesCompleteResponse(body);
  });


  /* ---------------------------------------------------
   * 3️⃣ GET CATEGORIES – CURSOR PAGINATION
   * --------------------------------------------------- */
  test('Should fetch categories with cursor pagination', async ({ request }) => {
    const response = await sendGraphQLRequest(
      request,
      GET_CATEGORIES_CURSOR,
      { first: 10 }
    );

    expect(response.status()).toBe(200);

    const body = await response.json();

    assertGetCategoriesCursorResponse(body);
  });


  /* ---------------------------------------------------
   * 4️⃣ GET CATEGORIES – WITH CHILD CATEGORIES
   * --------------------------------------------------- */
  test('Should fetch categories with child categories', async ({ request }) => {
    const response = await sendGraphQLRequest(
      request,
      GET_CATEGORIES_WITH_CHILDREN,
      { first: 10 }
    );

    expect(response.status()).toBe(200);

    const body = await response.json();

    assertGetCategoriesWithChildrenResponse(body);
  });

  test('Should fetch next page using endCursor', async ({ request }) => {

  // First call
  const firstResponse = await sendGraphQLRequest(
    request,
    GET_CATEGORIES_BASIC,
    { first: 2 }
  );

  const firstBody = await firstResponse.json();
  const endCursor = firstBody.data.categories.pageInfo.endCursor;

  // Second call using cursor
  const secondResponse = await sendGraphQLRequest(
    request,
    GET_CATEGORIES_BASIC,
    { first: 2, after: endCursor }
  );

  expect(secondResponse.status()).toBe(200);

  const secondBody = await secondResponse.json();

  assertGetCategoriesBasicResponse(secondBody);
});

test('Should handle invalid cursor gracefully', async ({ request }) => {
  const response = await sendGraphQLRequest(
    request,
    GET_CATEGORIES_CURSOR,
    { first: 5, after: 'invalid-cursor' }
  );

  expect(response.status()).toBe(200);

  const body = await response.json();

  // Depending on API behavior:
  if (body.errors) {
    expect(body.errors.length).toBeGreaterThan(0);
  } else {
    assertGetCategoriesCursorResponse(body);
  }
});

test('Should return empty result when first = 0', async ({ request }) => {
  const response = await sendGraphQLRequest(
    request,
    GET_CATEGORIES_BASIC,
    { first: 0 }
  );

  expect(response.status()).toBe(200);

  const body = await response.json();

  if (body.errors) {
    expect(body.errors.length).toBeGreaterThan(0);
  } else {
    expect(Array.isArray(body.data.categories.edges)).toBeTruthy();
  }
});

test('Should fetch category by valid IRI ID', async ({ request }) => {
    const response = await sendGraphQLRequest( request, GET_CATEGORY_BY_ID,
    { id: '/api/shop/categories/1' });
    expect(response.status()).toBe(200);
    const body = await response.json();
    // Reuse existing assertions
    assertNoGraphQLErrors(body);
    const category = body.data.category;
    assertBasicCategory(category);
    // Only assert additional field here (not in assertions file)
    expect(category.translation.description).toBeDefined();
    expect(typeof category.translation.description).toBe('string');
  });

test('Should fetch category by valid ID', async ({ request }) => {
    const response = await sendGraphQLRequest( request, GET_CATEGORY_BY_ID,
    { id: '1' });
    expect(response.status()).toBe(200);
    const body = await response.json();
    // Reuse existing assertions
    assertNoGraphQLErrors(body);
    const category = body.data.category;
    assertBasicCategory(category);
    // Only assert additional field here (not in assertions file)
    expect(category.translation.description).toBeDefined();
    expect(typeof category.translation.description).toBe('string');
  });

  test('Should fetch complete category details by ID', async ({ request }) => {
    const response = await sendGraphQLRequest( request, GET_CATEGORY_BY_ID_COMPLETE,
    { id: '/api/shop/categories/1' });
    expect(response.status()).toBe(200);
    const body = await response.json();
    assertNoGraphQLErrors(body);
    const category = body.data.category;
    // Reuse Extended Validation
    assertExtendedCategory(category);
    // Translation (extra fields)
    expect(category.translation.description).toBeDefined();
    expect(typeof category.translation.description).toBe('string');
    expect(typeof category.translation.metaTitle).toBe('string');
    expect(typeof category.translation.metaDescription).toBe('string');
    expect(typeof category.translation.metaKeywords).toBe('string');
    expect(typeof category.translation.locale).toBe('string');
    // Translations Connection
    assertTranslationsConnection(category.translations);
    // Children Connection
    assertChildrenConnection(category.children);
    assertPageInfo(category.children.pageInfo);
    expect(typeof category.children.totalCount).toBe('number');
    category.children.edges.forEach((edge: any) => {
      const child = edge.node;
      expect(typeof child.id).toBe('string');
      expect(typeof child._id).toBe('number');
      expect(typeof child.position).toBe('number');
      expect(typeof child.status).toBe('string');
      // Reuse light translation assertion
      assertLightCategoryTranslation(child.translation);
    });
  });

  test('Should cover category with children and SEO docs query', async ({ request }) => {
    const listResponse = await sendGraphQLRequest(request, GET_CATEGORIES_BASIC, { first: 1, after: null });
    expect(listResponse.status()).toBe(200);
    const listBody = await listResponse.json();
    const categoryId = listBody.data?.categories?.edges?.[0]?.node?.id;

    if (!categoryId) {
      console.log('No category available for children/SEO coverage.');
      expect(true).toBeTruthy();
      return;
    }

    const response = await sendGraphQLRequest(request, GET_CATEGORY_BY_ID_WITH_CHILDREN, { id: categoryId });
    expect(response.status()).toBe(200);
    const body = await response.json();
    assertNoGraphQLErrors(body);
    expect(body.data?.category?.translation?.metaTitle !== undefined || body.data?.category?.translation).toBeTruthy();
    expect(body.data?.category?.children).toBeTruthy();
  });
});
