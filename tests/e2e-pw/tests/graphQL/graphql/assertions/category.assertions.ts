import { expect } from '@playwright/test';

export function assertNoGraphQLErrors(body: any) {
  expect(body.errors, 'GraphQL errors found').toBeUndefined();
  expect(body.data).toBeTruthy();
}

export function assertCategoryTranslation(translation: any) {
  expect(translation).toBeTruthy();
  expect(typeof translation.name).toBe('string');
  expect(translation.name.trim().length).toBeGreaterThan(0);
  expect(typeof translation.slug).toBe('string');
  expect(translation.slug.trim().length).toBeGreaterThan(0);
  expect(translation.urlPath).toBeDefined();
}

export function assertBasicCategory(category: any) {
  expect(category).toBeTruthy();
  expect(category).toHaveProperty('id');
  expect(category).toHaveProperty('_id');
  expect(category).toHaveProperty('position');
  expect(category).toHaveProperty('status');
  expect(category).toHaveProperty('translation');
  expect(typeof category.id).toBe('string');
  expect(category.id).toMatch(/\/api\/shop\/categories\/\d+/);
  expect(typeof category._id).toBe('number');
  expect(typeof category.position).toBe('number');
  expect(typeof category.status).toBe('boolean');
  assertCategoryTranslation(category.translation);
}

export function assertChildrenConnection(children: any) {
  expect(children).toBeDefined();
  expect(Array.isArray(children.edges)).toBeTruthy();
}

export function assertCategoryTree(categories: any[]) {
  expect(Array.isArray(categories)).toBeTruthy();
  expect(categories.length).toBeGreaterThan(0);
  categories.forEach((category) => {
    assertBasicCategory(category);
    if (category.children?.edges?.length) {
      category.children.edges.forEach((edge: any) => {
        expect(edge.node).toBeTruthy();
        assertBasicCategory(edge.node);
        if (edge.node.children?.edges?.length) {
          assertCategoryTree(
            edge.node.children.edges.map((e: any) => e.node)
          );
        }
      });
    }
  });
}

export function assertGetAllCategoryResponse(body: any) {
  assertNoGraphQLErrors(body);
  expect(Array.isArray(body.data.treeCategories)).toBeTruthy();
  assertCategoryTree(body.data.treeCategories);
}

export function assertTreeCategoryBasicResponse(body: any) {
  assertNoGraphQLErrors(body);
  const categories = body.data.treeCategories;
  expect(Array.isArray(categories)).toBeTruthy();
  expect(categories.length).toBeGreaterThan(0);
  categories.forEach((category: any) => {
    assertBasicCategory(category);
    assertChildrenConnection(category.children);
    category.children.edges.forEach((edge: any) => {
      expect(edge.node).toBeTruthy();
      assertBasicCategory(edge.node);
      expect(edge.node.children).toBeUndefined();
    });
  });
}

export function assertExtendedCategory(category: any) {
  assertBasicCategory(category);
  expect(category).toHaveProperty('displayMode');
  expect(category).toHaveProperty('_lft');
  expect(category).toHaveProperty('_rgt');
  expect(category).toHaveProperty('createdAt');
  expect(category).toHaveProperty('updatedAt');
  expect(category).toHaveProperty('url');
  expect(typeof category.displayMode).toBe('string');
  expect(typeof category._lft).toBe('number');
  expect(typeof category._rgt).toBe('number');
  expect(typeof category.createdAt).toBe('string');
  expect(typeof category.updatedAt).toBe('string');
  expect(typeof category.url).toBe('string');
  if (category.logoPath !== null)
    expect(typeof category.logoPath).toBe('string');
  if (category.bannerPath !== null)
    expect(typeof category.bannerPath).toBe('string');
  if (category.logoUrl !== null)
    expect(typeof category.logoUrl).toBe('string');
  if (category.bannerUrl !== null)
    expect(typeof category.bannerUrl).toBe('string');
}

export function assertCategoryTranslationNode(node: any) {
  expect(node).toBeTruthy();
  expect(typeof node.id).toBe('string');
  expect(typeof node._id).toBe('number');
  expect(typeof node.categoryId).toBe('number');
  expect(typeof node.name).toBe('string');
  expect(typeof node.slug).toBe('string');
  expect(typeof node.locale).toBe('string');
}

export function assertTranslationsConnection(translations: any) {
  expect(translations).toBeDefined();
  expect(Array.isArray(translations.edges)).toBeTruthy();
  expect(typeof translations.totalCount).toBe('number');
  expect(translations.pageInfo).toBeDefined();
  expect(typeof translations.pageInfo.hasNextPage).toBe('boolean');
  expect(typeof translations.pageInfo.hasPreviousPage).toBe('boolean');
  translations.edges.forEach((edge: any) => {
    expect(edge.cursor).toBeTruthy();
    assertCategoryTranslationNode(edge.node);
  });
}

export function assertTreeCategoryCompleteResponse(body: any) {
  assertNoGraphQLErrors(body);
  const categories = body.data.treeCategories;
  expect(Array.isArray(categories)).toBeTruthy();
  expect(categories.length).toBeGreaterThan(0);
  categories.forEach((category: any) => {
    assertExtendedCategory(category);
    assertChildrenConnection(category.children);
    category.children.edges.forEach((edge: any) => {
      assertBasicCategory(edge.node);
    });
    assertTranslationsConnection(category.translations);
  });
}

export function assertTreeCategoryFilteredResponse(body: any) {
  assertNoGraphQLErrors(body);
  const categories = body.data.treeCategories;
  expect(Array.isArray(categories)).toBeTruthy();
  expect(categories.length).toBeGreaterThan(0);
  categories.forEach((category: any) => {
    assertBasicCategory(category);
    expect(category).toHaveProperty('displayMode');
    expect(typeof category.displayMode).toBe('string');

    if (category.logoPath !== null) {
      expect(typeof category.logoPath).toBe('string');
    }

    assertChildrenConnection(category.children);

    category.children.edges.forEach((edge: any) => {
      assertBasicCategory(edge.node);
      expect(edge.node.children).toBeUndefined();
    });
  });
}

export function assertPageInfo(pageInfo: any) {
  expect(pageInfo).toBeDefined();
  expect(typeof pageInfo.hasNextPage).toBe('boolean');

  if (pageInfo.endCursor !== undefined)
    expect(typeof pageInfo.endCursor).toBe('string');

  if (pageInfo.startCursor !== undefined)
    expect(typeof pageInfo.startCursor).toBe('string');

  if (pageInfo.hasPreviousPage !== undefined)
    expect(typeof pageInfo.hasPreviousPage).toBe('boolean');
}

export function assertCategoriesConnection(categories: any) {
  expect(categories).toBeDefined();
  expect(Array.isArray(categories.edges)).toBeTruthy();

  categories.edges.forEach((edge: any) => {
    expect(edge.node).toBeTruthy();
  });

  if (categories.totalCount !== undefined)
    expect(typeof categories.totalCount).toBe('number');

  if (categories.pageInfo)
    assertPageInfo(categories.pageInfo);
}

export function assertLightCategoryTranslation(translation: any) {
  expect(translation).toBeTruthy();
  expect(typeof translation.name).toBe('string');
  expect(translation.name.trim().length).toBeGreaterThan(0);
  expect(typeof translation.slug).toBe('string');
  expect(translation.slug.trim().length).toBeGreaterThan(0);
}

export function assertChildrenTotalCount(children: any) {
  expect(children).toBeDefined();
  expect(typeof children.totalCount).toBe('number');
}

export function assertGetCategoriesBasicResponse(body: any) {
  assertNoGraphQLErrors(body);

  const categories = body.data.categories;

  assertCategoriesConnection(categories);

  categories.edges.forEach((edge: any) => {
    assertBasicCategory(edge.node);

    expect(edge.node.children).toBeUndefined();
  });
}

export function assertGetCategoriesCompleteResponse(body: any) {
  assertNoGraphQLErrors(body);

  const categories = body.data.categories;

  assertCategoriesConnection(categories);

  categories.edges.forEach((edge: any) => {
    const node = edge.node;

    assertExtendedCategory(node);

    assertChildrenConnection(node.children);
    node.children.edges.forEach((childEdge: any) => {
      assertBasicCategory(childEdge.node);
    });

    assertTranslationsConnection(node.translations);
  });
}

export function assertGetCategoriesCursorResponse(body: any) {
  assertNoGraphQLErrors(body);

  const categories = body.data.categories;

  assertCategoriesConnection(categories);

  categories.edges.forEach((edge: any) => {
    expect(typeof edge.cursor).toBe('string');

    const node = edge.node;

    expect(typeof node.id).toBe('string');
    expect(typeof node._id).toBe('number');
    expect(typeof node.position).toBe('number');
    expect(typeof node.status).toBe('boolean');

    assertLightCategoryTranslation(node.translation);

    assertChildrenTotalCount(node.children);
  });
}

export function assertGetCategoriesWithChildrenResponse(body: any) {
  assertNoGraphQLErrors(body);

  const categories = body.data.categories;

  assertCategoriesConnection(categories);

  categories.edges.forEach((edge: any) => {
    const node = edge.node;

    expect(typeof node.id).toBe('string');
    expect(typeof node._id).toBe('number');
    expect(typeof node.position).toBe('number');

    assertLightCategoryTranslation(node.translation);

    expect(Array.isArray(node.children.edges)).toBeTruthy();
    expect(typeof node.children.totalCount).toBe('number');

    node.children.edges.forEach((childEdge: any) => {
      expect(childEdge.node).toBeTruthy();
      assertLightCategoryTranslation(childEdge.node.translation);
    });
  });
}
