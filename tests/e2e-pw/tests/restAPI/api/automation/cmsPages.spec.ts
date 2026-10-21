import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { SHOP_CMS } from '../../rest/endpoints/coverage/shopCms.endpoints';

test.describe('cms pages rest api', () => {
  test('should not accept post on collection', async ({ request }) => {
    const urlKey = `should-not-create-${Date.now()}`;
    const response = await sendRestRequest(request, SHOP_CMS.PAGES, {
      method: 'POST',
      data: { urlKey, pageTitle: urlKey },
    });
    const body = await response.json();

    expect(response.status(), JSON.stringify(body)).toBe(405);
    expect(body.message).toContain('The POST method is not supported for route api/shop/pages.');

    const listing = await sendRestRequest(request, SHOP_CMS.PAGES, { params: { url_key: urlKey } });

    expect(listing.status()).toBe(200);
    expect(await listing.json()).toEqual([]);
  });
});
