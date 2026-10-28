import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../rest/helpers/adminClient';
import { ENDPOINTS } from '../../../rest/endpoints/endpoints';

test.describe.configure({ timeout: 60_000 });

interface SubPage {
  label: string;
  entity: string;
  endpoint: string;
  defaultType: string;
  types: string[];
}

const SUB_PAGES: SubPage[] = [
  {
    label: 'overview',
    entity: 'overview',
    endpoint: ENDPOINTS.ADMIN_REPORTING_STATS,
    defaultType: 'total-sales',
    types: ['total-sales', 'total-orders', 'total-customers'],
  },
  {
    label: 'sales',
    entity: 'sales',
    endpoint: ENDPOINTS.ADMIN_REPORTING_SALES,
    defaultType: 'total-sales',
    types: ['total-sales', 'total-orders', 'purchase-funnel'],
  },
  {
    label: 'customers',
    entity: 'customers',
    endpoint: ENDPOINTS.ADMIN_REPORTING_CUSTOMERS,
    defaultType: 'total-customers',
    types: ['total-customers', 'customers-with-most-orders'],
  },
  {
    label: 'products',
    entity: 'products',
    endpoint: ENDPOINTS.ADMIN_REPORTING_PRODUCTS,
    defaultType: 'total-sold-quantities',
    types: ['top-selling-products-by-revenue', 'top-selling-products-by-quantity'],
  },
];

test.describe('admin reporting rest api', () => {
  for (const sub of SUB_PAGES) {
    test(`${sub.label} default returns 200`, async ({ request }) => {
      const response = await sendAdminRequest(request, sub.endpoint);
      expect(response.status()).toBe(200);

      const body = await response.json();
      expect(body).toHaveLength(1);
      expect(body[0]).toEqual(expect.objectContaining({ entity: sub.entity, type: sub.defaultType }));
    });

    for (const type of sub.types) {
      test(`${sub.label} type ${type}`, async ({ request }) => {
        const response = await sendAdminRequest(request, sub.endpoint, {
          params: { type },
        });
        expect(response.status()).toBe(200);

        const body = await response.json();
        expect(body).toHaveLength(1);
        expect(body[0]).toEqual(expect.objectContaining({ entity: sub.entity, type }));
        expect(body[0]).toHaveProperty('statistics');
      });
    }

    test(`${sub.label} with date range returns 200`, async ({ request }) => {
      const response = await sendAdminRequest(request, sub.endpoint, {
        params: {
          start: '2026-01-01',
          end: '2026-05-26',
        },
      });
      expect(response.status()).toBe(200);
      expect((await response.json())[0].type).toBe(sub.defaultType);
    });

    test(`${sub.label} unknown type is rejected with 400`, async ({ request }) => {
      const response = await sendAdminRequest(request, sub.endpoint, {
        params: { type: 'definitely-not-a-real-type' },
      });
      expect(response.status()).toBe(400);
      expect((await response.json()).detail).toBe('Unknown reporting stat type for this entity.');
    });
  }
});
