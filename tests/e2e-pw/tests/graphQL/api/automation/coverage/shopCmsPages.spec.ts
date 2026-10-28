import { test, expect, APIRequestContext, APIResponse } from '@playwright/test';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../../graphql/helpers/testSupport';
import { SHOP_CMS_QUERIES } from '../../../graphql/Queries/coverage/shopCms.queries';
import { sendAdminRequest } from '../../../../restAPI/rest/helpers/adminClient';
import { SHOP_CMS } from '../../../../restAPI/rest/endpoints/coverage/shopCms.endpoints';
import { env } from '../../../config/env';

test.describe.configure({ timeout: 90_000 });

const MISSING_KEY_MESSAGE = 'X-STOREFRONT-KEY header is required for this operation';

interface CreatedPage {
  id: number;
  urlKey: string;
  pageTitle: string;
  htmlContent: string;
  metaTitle: string;
  metaKeywords: string;
  metaDescription: string;
}

function uniqueSuffix(): string {
  return `${Date.now().toString(36)}-${Math.floor(Math.random() * 1e6).toString(36)}`;
}

async function createCmsPage(request: APIRequestContext): Promise<CreatedPage> {
  const suffix = uniqueSuffix();
  const page = {
    urlKey: `e2e-gql-cms-${suffix}`,
    pageTitle: `E2E GraphQL Cms ${suffix}`,
    htmlContent: `<p>e2e graphql cms body ${suffix}</p>`,
    metaTitle: `e2e meta ${suffix}`,
    metaKeywords: `e2e,${suffix}`,
    metaDescription: `e2e description ${suffix}`,
  };

  const response = await sendAdminRequest(request, SHOP_CMS.ADMIN_CMS_PAGES, {
    method: 'POST',
    data: {
      url_key: page.urlKey,
      page_title: page.pageTitle,
      html_content: page.htmlContent,
      meta_title: page.metaTitle,
      meta_keywords: page.metaKeywords,
      meta_description: page.metaDescription,
      channels: [1],
    },
  });
  expect(response.status()).toBe(201);

  const body = await response.json();
  expect(typeof body?.id).toBe('number');

  return { id: body.id, ...page };
}

async function shopQuery(request: APIRequestContext, query: string, variables: Record<string, any> = {}): Promise<any> {
  const response = await sendGraphQLRequest(request, query, variables);
  expect(response.status()).toBe(200);

  return response.json();
}

async function postWithoutStorefrontKey(request: APIRequestContext, query: string, variables: Record<string, any>): Promise<APIResponse> {
  return request.post(`${env.baseUrl}${env.graphqlEndpoint}`, {
    data: { query, variables },
    headers: { 'Content-Type': 'application/json' },
  });
}

async function pageTranslationNodes(request: APIRequestContext, cmsPageId: number): Promise<{ nodes: any[] }> {
  const nodes: any[] = [];
  let after: string | null = null;

  for (let page = 0; page < 200; page++) {
    const body = await shopQuery(request, SHOP_CMS_QUERIES.pageTranslations, { first: 50, after });
    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

    const connection = body.data.pageTranslations;
    expect(typeof connection.totalCount).toBe('number');
    nodes.push(...connection.edges.map((edge: any) => edge.node).filter((node: any) => Number(node.cmsPageId) === cmsPageId));

    if (!connection.pageInfo.hasNextPage) {
      break;
    }

    after = connection.pageInfo.endCursor;
  }

  return { nodes };
}

function expectTranslationOfPage(translation: any, page: CreatedPage): void {
  expect(translation.pageTitle).toBe(page.pageTitle);
  expect(translation.urlKey).toBe(page.urlKey);
  expect(translation.htmlContent).toBe(page.htmlContent);
  expect(translation.metaTitle).toBe(page.metaTitle);
  expect(translation.metaKeywords).toBe(page.metaKeywords);
  expect(translation.metaDescription).toBe(page.metaDescription);
  expect(Number(translation.cmsPageId)).toBe(page.id);
  expect(typeof translation.locale).toBe('string');
}

test.describe('shop cms pages graphql', () => {
  let page: CreatedPage | null = null;

  test.beforeEach(async ({ request }) => {
    page = null;
    page = await createCmsPage(request);
  });

  test.afterEach(async ({ request }) => {
    if (page) {
      await sendAdminRequest(request, SHOP_CMS.ADMIN_CMS_PAGE(page.id), { method: 'DELETE' });
    }
  });

  test('should find a newly created page by its url key', async ({ request }) => {
    const body = await shopQuery(request, SHOP_CMS_QUERIES.pageByUrlKey, { urlKey: page!.urlKey });
    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

    const pages = body.data.pageByUrlKeypages;
    expect(pages).toHaveLength(1);
    expect(pages[0]._id).toBe(page!.id);
    expect(String(pages[0].id)).toMatch(new RegExp(`/${page!.id}$`));
    expect(pages[0].createdAt).toMatch(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/);
    expectTranslationOfPage(pages[0].translation, page!);
  });

  test('should return no page for a url key nothing uses', async ({ request }) => {
    const body = await shopQuery(request, SHOP_CMS_QUERIES.pageByUrlKey, { urlKey: `missing-${uniqueSuffix()}` });
    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
    expect(body.data.pageByUrlKeypages).toEqual([]);
  });

  test('should return no page for the url key of a deleted page', async ({ request }) => {
    await sendAdminRequest(request, SHOP_CMS.ADMIN_CMS_PAGE(page!.id), { method: 'DELETE' });
    const urlKey = page!.urlKey;
    page = null;

    const body = await shopQuery(request, SHOP_CMS_QUERIES.pageByUrlKey, { urlKey });
    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
    expect(body.data.pageByUrlKeypages).toEqual([]);
  });

  test('should refuse a page lookup without the required url key', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_CMS_QUERIES.pageByUrlKeyWithoutArgument);
    const body = await response.json();

    expect(graphQLErrorMessages(body).join(' ')).toContain('argument "urlKey" of type "String!" is required');
    expect(body.data?.pageByUrlKeypages ?? null).toBeNull();
  });

  test('should list every locale translation of a newly created page', async ({ request }) => {
    const { nodes } = await pageTranslationNodes(request, page!.id);

    expect(nodes.length).toBeGreaterThan(0);

    for (const node of nodes) {
      expectTranslationOfPage(node, page!);
      expect(String(node.id)).toMatch(new RegExp(`/${node._id}$`));
    }

    expect(new Set(nodes.map((node: any) => node.locale)).size).toBe(nodes.length);
  });

  test('should cap the page translation connection at the requested size', async ({ request }) => {
    const body = await shopQuery(request, SHOP_CMS_QUERIES.pageTranslations, { first: 1 });
    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

    const connection = body.data.pageTranslations;
    expect(connection.edges).toHaveLength(1);
    expect(connection.totalCount).toBeGreaterThan(1);
    expect(connection.pageInfo.hasNextPage).toBe(true);
  });

  test('should return a single page translation by its iri', async ({ request }) => {
    const { nodes } = await pageTranslationNodes(request, page!.id);
    expect(nodes.length).toBeGreaterThan(0);

    const body = await shopQuery(request, SHOP_CMS_QUERIES.pageTranslation, { id: nodes[0].id });
    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

    const translation = body.data.pageTranslation;
    expect(translation._id).toBe(nodes[0]._id);
    expect(translation.locale).toBe(nodes[0].locale);
    expectTranslationOfPage(translation, page!);
  });

  test('should report an error for a page translation that does not exist', async ({ request }) => {
    const body = await shopQuery(request, SHOP_CMS_QUERIES.pageTranslation, { id: '/api/shop/page_translations/999999999' });

    expect(graphQLErrorMessages(body).length).toBeGreaterThan(0);
    expect(body.data?.pageTranslation ?? null).toBeNull();
  });
});

test.describe('shop cms pages graphql without a storefront key', () => {
  const operations = [
    { label: 'a page lookup by url key', query: SHOP_CMS_QUERIES.pageByUrlKey, variables: { urlKey: 'about-us' } },
    { label: 'the page translation connection', query: SHOP_CMS_QUERIES.pageTranslations, variables: { first: 1 } },
    { label: 'a single page translation', query: SHOP_CMS_QUERIES.pageTranslation, variables: { id: '/api/shop/page_translations/1' } },
  ];

  for (const { label, query, variables } of operations) {
    test(`should refuse ${label} without a storefront key`, async ({ request }) => {
      const response = await postWithoutStorefrontKey(request, query, variables);
      expect(response.status()).toBe(401);

      const body = await response.json();
      expect(body.message).toBe(MISSING_KEY_MESSAGE);
      expect(body.errors[0].extensions.code).toBe('UNAUTHENTICATED');
      expect(body.data).toBeUndefined();
    });
  }
});
