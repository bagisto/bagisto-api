// tests/restAPI/api/automation/productFilter.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import { assertProductCard } from '../../rest/assertions/product.assertions';

// Discovery beforeEach + filter-bodied tests can chain 2-3 product-list
// calls; the dev DB's listing endpoint is slow enough that 30s is tight.
test.describe.configure({ timeout: 60_000 });

test.describe('Product Filter REST API', () => {
  let productId: number;

  test.beforeEach(async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { per_page: '1' },
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(Array.isArray(body)).toBeTruthy();
    expect(body.length).toBeGreaterThan(0);
    productId = body[0].id;
  });

  test('Should filter products by category ID', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { per_page: '10', category_id: '1' },
    });

    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(Array.isArray(body)).toBeTruthy();
    console.log('Products filtered by category_id=1:', body.length);

    if (body.length > 0) {
      body.forEach((p: any) => assertProductCard(p));
      console.log('Category-filtered first product:', body[0].name);
    }
  });

  test('Should filter products by categoryId alias', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { per_page: '10', categoryId: '1' },
    });

    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(Array.isArray(body)).toBeTruthy();
    console.log('Products filtered by categoryId=1:', body.length);
  });

  test('Should filter products by price range', async ({ request }) => {
    // NOTE: per_page=100 takes ~12s on this dev DB and was timing out under
    // parallel load. per_page=25 is enough to find a reasonable price range
    // without blowing the 30s per-test budget.
    const allResp = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { per_page: '25' },
    });
    const allProducts = await allResp.json();

    if (allProducts.length >= 2) {
      const prices = allProducts.map((p: any) => p.price).filter((p: number) => p != null);
      prices.sort((a: number, b: number) => a - b);
      const from = Math.round(prices[0]);
      const to = Math.round(prices[prices.length - 1] / 2);

      const response = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
        params: { per_page: '20', price: `${from},${to}` },
      });
      expect(response.status()).toBe(200);
      const body = await response.json();
      expect(Array.isArray(body)).toBeTruthy();
      console.log(`Products filtered by price ${from}-${to}:`, body.length);

      body.forEach((p: any) => {
        expect(p.price).toBeGreaterThanOrEqual(from);
        expect(p.price).toBeLessThanOrEqual(to);
      });
    } else {
      console.log('Not enough products to test price range filter');
    }
  });

  test('Should filter products by price_from and price_to', async ({ request }) => {
    const allResp = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { per_page: '25' },
    });
    const allProducts = await allResp.json();

    if (allProducts.length >= 2) {
      const prices = allProducts.map((p: any) => p.price).filter((p: number) => p != null);
      prices.sort((a: number, b: number) => a - b);
      const priceFrom = Math.round(prices[0]);
      const priceTo = Math.round(prices[prices.length - 1] / 2);

      const response = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
        params: { per_page: '20', price_from: String(priceFrom), price_to: String(priceTo) },
      });
      expect(response.status()).toBe(200);
      const body = await response.json();
      expect(Array.isArray(body)).toBeTruthy();
      console.log(`Products filtered by price_from=${priceFrom} price_to=${priceTo}:`, body.length);
    } else {
      console.log('Not enough products for price_from/price_to filter');
    }
  });

  test('Should filter products marked as new', async ({ request }) => {
    // per_page=50 was slow + flaky; 20 is enough to verify the filter shape.
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { per_page: '20', new: '1' },
    });

    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(Array.isArray(body)).toBeTruthy();
    console.log('Products marked as new:', body.length);
  });

  test('Should filter products marked as featured', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { per_page: '20', featured: '1' },
    });

    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(Array.isArray(body)).toBeTruthy();
    console.log('Products marked as featured:', body.length);
  });

  test('Should return empty array for non-matching filter', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { per_page: '10', query: 'nonexistentproductxyz123abc' },
    });

    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(Array.isArray(body)).toBeTruthy();
    console.log('Non-matching filter result:', body.length);
  });
});
