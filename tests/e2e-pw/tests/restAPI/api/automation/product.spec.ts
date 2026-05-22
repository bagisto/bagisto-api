// tests/restAPI/api/automation/product.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import { assertProductCard } from '../../rest/assertions/product.assertions';

test.describe('Get Product REST API', () => {
  test('Should return product with valid ID', async ({ request }) => {
    const response = await sendRestRequest(request, `${ENDPOINTS.PRODUCTS}/1`);

    if (response.status() === 429) {
      test.skip(true, 'Rate limited');
      return;
    }
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(body).toHaveProperty('id', 1);
    assertProductCard(body);
    console.log('Product details:', JSON.stringify({
      id: body.id,
      name: body.name,
      sku: body.sku,
      type: body.type,
      price: body.price,
      formattedPrice: body.formattedPrice,
    }, null, 2));
  });

  test('Should return 404 for non-existent product ID', async ({ request }) => {
    const response = await sendRestRequest(request, `${ENDPOINTS.PRODUCTS}/999999`);
    const code = response.status();
    if (code === 429) {
      test.skip(true, 'Rate limited');
      return;
    }
    expect(code).toBe(404);
    console.log('404 received for non-existent product: 999999');
  });

  test('Should return product with formatted price', async ({ request }) => {
    const response = await sendRestRequest(request, `${ENDPOINTS.PRODUCTS}/1`);
    const code = response.status();
    if (code === 429) {
      test.skip(true, 'Rate limited');
      return;
    }
    expect(code).toBe(200);
    const body = await response.json();
    expect(body).toHaveProperty('formattedPrice');
    expect(typeof body.formattedPrice).toBe('string');
    console.log('Product formatted price:', body.formattedPrice);
  });
});

test.describe('Get Products REST API', () => {
  test('Should return paginated list of products', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { page: '1', per_page: '10' },
    });
    const code = response.status();
    if (code === 429) {
      test.skip(true, 'Rate limited');
      return;
    }
    expect(code).toBe(200);
    const body = await response.json();
    expect(Array.isArray(body)).toBeTruthy();
    console.log('Products count:', body.length);

    if (body.length > 0) {
      const product = body[0];
      assertProductCard(product);
      console.log('First product:', {
        id: product.id,
        name: product.name,
        sku: product.sku,
        type: product.type,
        price: product.price,
        formattedPrice: product.formattedPrice,
      });
    }
  });

  test('Should return pagination headers', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { page: '1', per_page: '10' },
    });
    const code = response.status();
    if (code === 429) {
      test.skip(true, 'Rate limited');
      return;
    }
    expect(code).toBe(200);
    const headers = response.headers();
    expect(headers).toHaveProperty('x-total-count');
    expect(headers).toHaveProperty('x-page');
    expect(headers).toHaveProperty('x-per-page');
    expect(headers).toHaveProperty('x-total-pages');
    console.log('Products pagination:', {
      total: headers['x-total-count'],
      page: headers['x-page'],
      per_page: headers['x-per-page'],
      total_pages: headers['x-total-pages'],
    });
  });

  test('Should return products with custom per_page limit', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { page: '1', per_page: '5' },
    });
    const code = response.status();
    if (code === 429) {
      test.skip(true, 'Rate limited');
      return;
    }
    expect(code).toBe(200);
    const body = await response.json();
    expect(body.length).toBeLessThanOrEqual(5);
    console.log('Custom per_page limit results:', body.length);
  });
});

test.describe('Search Products REST API', () => {
  test('Should return products matching search query', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { query: 'coastal' },
    });
    const code = response.status();
    if (code === 429) {
      test.skip(true, 'Rate limited');
      return;
    }
    expect(code).toBe(200);
    const body = await response.json();
    expect(Array.isArray(body)).toBeTruthy();
    console.log('Search "coastal" results:', body.length);
  });

  test('Should return no results for non-existent search term', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { query: 'nonexistentproductxyz123' },
    });
    const code = response.status();
    if (code === 429) {
      test.skip(true, 'Rate limited');
      return;
    }
    expect(code).toBe(200);
    const body = await response.json();
    expect(body).toEqual([]);
    console.log('Search "nonexistentproductxyz123" results: 0');
  });

  test('Should handle empty search query (returns all products)', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { query: '' },
    });
    const code = response.status();
    if (code === 429) {
      test.skip(true, 'Rate limited');
      return;
    }
    expect(code).toBe(200);
    const body = await response.json();
    expect(Array.isArray(body)).toBeTruthy();
    expect(body.length).toBeGreaterThan(0);
    console.log('Empty search returns:', body.length, 'products');
  });
});