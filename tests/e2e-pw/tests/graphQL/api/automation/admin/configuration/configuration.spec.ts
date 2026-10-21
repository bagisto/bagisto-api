import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';

test.describe.configure({ timeout: 60_000 });

const SLUG = 'general.general';
const WEIGHT_UNIT = 'general.general.locale_options.weight_unit';
const CHANNEL = 'default';
const LOCALE = 'en';

const SLUG_NOT_FOUND = 'No configuration section is registered for the given slug.';
const UPDATE_SUCCESS = 'Configuration updated successfully.';

const CONFIG_MENU = `
  query menuAdminConfigurationMenu($slug: String, $includeValues: Boolean, $channel: String, $locale: String) {
    menuAdminConfigurationMenu(slug: $slug, include_values: $includeValues, channel: $channel, locale: $locale) {
      slug
      tree
    }
  }
`;

const CONFIG_VALUES = `
  query valuesAdminConfigurationValues($slug: String!, $channel: String, $locale: String) {
    valuesAdminConfigurationValues(slug: $slug, channel: $channel, locale: $locale) {
      slug channel locale values
    }
  }
`;

const CONFIG_UPDATE = `
  mutation createAdminConfigurationUpdate($slug: String!, $values: Iterable!, $channel: String, $locale: String) {
    createAdminConfigurationUpdate(input: { slug: $slug, values: $values, channel: $channel, locale: $locale }) {
      adminConfigurationUpdate { slug success message channel locale values }
    }
  }
`;

async function gql(request: APIRequestContext, query: string, variables: Record<string, any> = {}): Promise<any> {
  const resp = await sendAdminGraphQLRequest(request, query, variables);
  expect(resp.status()).toBe(200);
  return resp.json();
}

function dataOf(body: any, field: string): any {
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
  return body.data[field];
}

function firstError(body: any): string | undefined {
  return body.errors?.[0]?.message;
}

async function weightUnit(request: APIRequestContext): Promise<string> {
  const values = dataOf(await gql(request, CONFIG_VALUES, { slug: SLUG, channel: CHANNEL, locale: LOCALE }), 'valuesAdminConfigurationValues');
  return values.values[WEIGHT_UNIT];
}

function child(node: any, key: string): any {
  return (node.children ?? []).find((entry: any) => entry.key === key);
}

test.describe('admin configuration graphql api', () => {
  test('menu without a slug returns the whole tree with the general section', async ({ request }) => {
    const menu = dataOf(await gql(request, CONFIG_MENU, {}), 'menuAdminConfigurationMenu');
    expect(menu.slug).toBeNull();

    const general = menu.tree.find((node: any) => node.key === 'general');
    expect(general).toEqual(expect.objectContaining({ key: 'general', name: 'General', sort: 1 }));
    expect(child(general, SLUG)).toEqual(expect.objectContaining({ key: SLUG }));
  });

  test('menu scoped to the general slug returns only that section with the weight unit field', async ({ request }) => {
    const menu = dataOf(await gql(request, CONFIG_MENU, { slug: SLUG }), 'menuAdminConfigurationMenu');
    expect(menu.slug).toBe(SLUG);
    expect(menu.tree).toHaveLength(1);
    expect(menu.tree[0].key).toBe(SLUG);

    const localeOptions = child(menu.tree[0], 'general.general.locale_options');
    const field = localeOptions.fields.find((entry: any) => entry.code === WEIGHT_UNIT);
    expect(field).toEqual(expect.objectContaining({
      name: 'weight_unit',
      code: WEIGHT_UNIT,
      type: 'select',
      default: 'kgs',
      channelBased: true,
      localeBased: false,
    }));
    expect(field.options.map((option: any) => option.value)).toEqual(['lbs', 'kgs']);
  });

  test('menu with an unknown slug reports the slug is not registered', async ({ request }) => {
    const body = await gql(request, CONFIG_MENU, { slug: 'general.no-such-section' });
    expect(firstError(body)).toBe(SLUG_NOT_FOUND);
    expect(body.data?.menuAdminConfigurationMenu ?? null).toBeNull();
  });

  test('values for the general slug resolve for the requested channel and locale', async ({ request }) => {
    const values = dataOf(await gql(request, CONFIG_VALUES, { slug: SLUG, channel: CHANNEL, locale: LOCALE }), 'valuesAdminConfigurationValues');
    expect(values).toEqual(expect.objectContaining({ slug: SLUG, channel: CHANNEL, locale: LOCALE }));
    expect(['lbs', 'kgs']).toContain(values.values[WEIGHT_UNIT]);
  });

  test('values for an unknown slug report the slug is not registered', async ({ request }) => {
    const body = await gql(request, CONFIG_VALUES, { slug: 'general.no-such-section', channel: CHANNEL, locale: LOCALE });
    expect(firstError(body)).toBe(SLUG_NOT_FOUND);
  });

  test('update with a key outside the slug is rejected', async ({ request }) => {
    const before = await weightUnit(request);

    const body = await gql(request, CONFIG_UPDATE, {
      slug: SLUG,
      values: { 'catalog.products.some-field': 'x' },
      channel: CHANNEL,
      locale: LOCALE,
    });
    expect(firstError(body)).toBe('The key `catalog.products.some-field` is outside the requested slug subtree.');
    expect(body.data?.createAdminConfigurationUpdate ?? null).toBeNull();
    expect(await weightUnit(request)).toBe(before);
  });

  test('update with an unregistered key inside the slug is rejected', async ({ request }) => {
    const body = await gql(request, CONFIG_UPDATE, {
      slug: SLUG,
      values: { 'general.general.weight_unit': 'kgs' },
      channel: CHANNEL,
      locale: LOCALE,
    });
    expect(firstError(body)).toBe('The key `general.general.weight_unit` is not a registered configuration field.');
  });

  test('update with a registered key stores the value and reads it back', async ({ request }) => {
    const original = await weightUnit(request);
    const changed = original === 'kgs' ? 'lbs' : 'kgs';

    try {
      const result = dataOf(
        await gql(request, CONFIG_UPDATE, { slug: SLUG, values: { [WEIGHT_UNIT]: changed }, channel: CHANNEL, locale: LOCALE }),
        'createAdminConfigurationUpdate',
      ).adminConfigurationUpdate;
      expect(result).toEqual(expect.objectContaining({ slug: SLUG, success: true, message: UPDATE_SUCCESS, channel: CHANNEL, locale: LOCALE }));
      expect(result.values[WEIGHT_UNIT]).toBe(changed);

      expect(await weightUnit(request)).toBe(changed);
    } finally {
      await gql(request, CONFIG_UPDATE, { slug: SLUG, values: { [WEIGHT_UNIT]: original }, channel: CHANNEL, locale: LOCALE });
    }

    expect(await weightUnit(request)).toBe(original);
  });
});
