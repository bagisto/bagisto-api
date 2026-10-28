import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../graphql/helpers/adminGraphqlClient';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { sendAdminRequest } from '../../../../restAPI/rest/helpers/adminClient';
import { ADMIN_MARKETING_COVERAGE } from '../../../../restAPI/rest/endpoints/coverage/adminCatalog.endpoints';
import { env } from '../../../config/env';
import {
  ADMIN_SEARCH_TERM_CREATE_COVERAGE_MUTATION,
  ADMIN_SEARCH_TERM_READ_COVERAGE_QUERY,
} from '../../../graphql/Queries/coverage/adminMarketing.queries';

test.describe.configure({ timeout: 60_000 });

async function firstRow(request: APIRequestContext, endpoint: string): Promise<any> {
  const resp = await sendAdminRequest(request, endpoint);
  expect(resp.status()).toBe(200);
  const rows = (await resp.json()).data;
  expect(rows.length).toBeGreaterThan(0);
  return rows[0];
}

async function createTerm(request: APIRequestContext, input: Record<string, any>): Promise<any> {
  const resp = await sendAdminGraphQLRequest(request, ADMIN_SEARCH_TERM_CREATE_COVERAGE_MUTATION, { input });
  expect(resp.status()).toBe(200);
  return resp.json();
}

test.describe('admin marketing search term create graphql', () => {
  let createdIds: number[];
  let channel: { id: number; code: string };
  let locale: string;
  let term: string;

  test.beforeEach(async ({ request }) => {
    createdIds = [];
    const channelRow = await firstRow(request, ADMIN_MARKETING_COVERAGE.CHANNELS);
    channel = { id: channelRow.id, code: channelRow.code };
    locale = (await firstRow(request, ADMIN_MARKETING_COVERAGE.LOCALES)).code;
    term = `e2e-cov-term-${Date.now().toString(36)}-${Math.floor(Math.random() * 1e6).toString(36)}`;
  });

  test.afterEach(async ({ request }) => {
    for (const id of createdIds) {
      await sendAdminRequest(request, ADMIN_MARKETING_COVERAGE.SEARCH_TERM(id), { method: 'DELETE' });
    }
  });

  test('should create a search term for the channel and locale', async ({ request }) => {
    const body = await createTerm(request, {
      term,
      redirectUrl: 'https://example.com/coverage',
      channelId: channel.id,
      locale,
    });
    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

    const created = body.data.createAdminMarketingSearchTerm.adminMarketingSearchTerm;
    createdIds.push(created._id);

    expect(typeof created._id).toBe('number');
    expect(created.id).toBe(`/api/admin/marketing/search-terms/${created._id}`);
    expect(created.term).toBe(term);
    expect(created.locale).toBe(locale);
    expect(created.redirectUrl).toBe('https://example.com/coverage');
    expect(created.results).toBe(0);
    expect(created.uses).toBe(0);
    expect(created.channel).toEqual({ _id: channel.id, code: channel.code });

    const read = await sendAdminGraphQLRequest(request, ADMIN_SEARCH_TERM_READ_COVERAGE_QUERY, { id: created.id });
    expect(read.status()).toBe(200);
    const readBody = await read.json();
    expect(readBody.errors, JSON.stringify(readBody.errors)).toBeUndefined();
    expect(readBody.data.adminMarketingSearchTerm).toEqual({
      _id: created._id,
      term,
      locale,
      redirectUrl: 'https://example.com/coverage',
    });
  });

  test('should create a search term without a redirect url', async ({ request }) => {
    const body = await createTerm(request, { term, channelId: channel.id, locale });
    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

    const created = body.data.createAdminMarketingSearchTerm.adminMarketingSearchTerm;
    createdIds.push(created._id);

    expect(created.term).toBe(term);
    expect(created.redirectUrl).toBeNull();
  });

  test('should reject a search term without a term', async ({ request }) => {
    const body = await createTerm(request, { channelId: channel.id, locale });
    expect(body.errors?.[0]?.message).toBe('The term field is required.');
    expect(body.data?.createAdminMarketingSearchTerm ?? null).toBeNull();
  });

  test('should reject a search term for a channel that does not exist', async ({ request }) => {
    const body = await createTerm(request, { term, channelId: 999999999, locale });
    expect(body.errors?.[0]?.message).toBe('The selected channel id is invalid.');
    expect(body.data?.createAdminMarketingSearchTerm ?? null).toBeNull();
  });

  test('should reject a search term for a locale that does not exist', async ({ request }) => {
    const body = await createTerm(request, { term, channelId: channel.id, locale: 'zz_ZZ' });
    expect(body.errors?.[0]?.message).toBe('The selected locale is invalid.');
    expect(body.data?.createAdminMarketingSearchTerm ?? null).toBeNull();
  });

  test('should reject a redirect url that is not http or https', async ({ request }) => {
    const body = await createTerm(request, {
      term,
      redirectUrl: 'javascript:alert(1)',
      channelId: channel.id,
      locale,
    });
    expect(body.errors?.[0]?.message).toBe('The redirect url field must be a valid URL.');
    expect(body.data?.createAdminMarketingSearchTerm ?? null).toBeNull();
  });

  test('should refuse the create mutation without an admin token', async ({ request }) => {
    const resp = await sendGraphQLRequest(
      request,
      ADMIN_SEARCH_TERM_CREATE_COVERAGE_MUTATION,
      { input: { term, channelId: channel.id, locale } },
      {},
      env.adminGraphqlEndpoint,
    );
    expect(resp.status()).toBe(401);
    const body = await resp.json();
    expect(body.message).toBe('Unauthenticated.');
    expect(body.data).toBeUndefined();
  });
});
