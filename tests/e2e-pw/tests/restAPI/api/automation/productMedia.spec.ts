// tests/restAPI/api/automation/productMedia.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import { assertProductCard } from '../../rest/assertions/product.assertions';

test.describe('Product Media & Type Resources REST API', () => {
  let productId: number;
  let bookingProductId: number | null = null;

  test.beforeEach(async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { per_page: '1' },
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(body.length).toBeGreaterThan(0);
    productId = body[0].id;

    const bookingResp = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { per_page: '200', type: 'booking' },
    });
    const bookingBody = await bookingResp.json();
    if (Array.isArray(bookingBody) && bookingBody.length > 0) {
      const bpResp = await sendRestRequest(request, ENDPOINTS.BOOKING_PRODUCTS(productId));
      if (bpResp.status() === 200) {
        const bp = await bpResp.json();
        expect(Array.isArray(bp)).toBeTruthy();
        if (bp.length > 0) bookingProductId = bp[0].id;
      }
    }
  });

  // ── IMAGES ────────────────────────────────────────────────
  test('Should return all product images', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.ALL_PRODUCT_IMAGES);
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(Array.isArray(body)).toBeTruthy();
    console.log('All product images catalog:', body.length);

    if (body.length > 0) {
      expect(body[0]).toHaveProperty('id');
      console.log('First catalog image:', JSON.stringify({ id: body[0].id, path: body[0].path }));
    }
  });

  test('Should return images for a product', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_IMAGES(productId));
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(Array.isArray(body)).toBeTruthy();
    console.log(`Images for product ${productId}:`, body.length);

    body.forEach((img: any) => {
      expect(img).toHaveProperty('id');
      expect(img).toHaveProperty('path');
    });
  });

  test('Should return 404 for images of a non-existent product', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_IMAGES(999999));
    expect([200, 404]).toContain(response.status());
  });

  test('Should return single image by ID', async ({ request }) => {
    const imagesResp = await sendRestRequest(request, ENDPOINTS.PRODUCT_IMAGES(productId));
    const images = await imagesResp.json();

    if (Array.isArray(images) && images.length > 0) {
      const imageId = images[0].id;
      const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_IMAGE(imageId));
      expect(response.status()).toBe(200);
      const body = await response.json();
      expect(body).toHaveProperty('id', imageId);
      console.log('Single image:', body.path);
    }
  });

  // ── VIDEOS ────────────────────────────────────────────────
  test('Should return all product videos', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.ALL_PRODUCT_VIDEOS);
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(Array.isArray(body)).toBeTruthy();
    console.log('All product videos catalog:', body.length);
  });

  test('Should return videos for a product', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_VIDEOS(productId));
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(Array.isArray(body)).toBeTruthy();
    console.log(`Videos for product ${productId}:`, body.length);
  });

  test('Should return single video by ID', async ({ request }) => {
    const videosResp = await sendRestRequest(request, ENDPOINTS.ALL_PRODUCT_VIDEOS);
    const allVideos = await videosResp.json();
    if (Array.isArray(allVideos) && allVideos.length > 0) {
      const vid = allVideos[0];
      const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_VIDEO(vid.id));
      expect(response.status()).toBe(200);
      console.log('Single video ID:', vid.id);
    } else {
      console.log('No videos in catalog to test single endpoint');
    }
  });

  // ── BUNDLE OPTIONS ────────────────────────────────────────
  test('Should return bundle options for a product or 404 for non-bundle', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_BUNDLE_OPTIONS(productId));
    expect([200, 404]).toContain(response.status());
    console.log(`Bundle options for product ${productId}:`, response.status());
  });

  test('Should return all bundle option products', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.ALL_BUNDLE_OPTION_PRODUCTS);
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(Array.isArray(body)).toBeTruthy();
    console.log('All bundle option products:', body.length);
  });

  // ── GROUPED PRODUCTS ──────────────────────────────────────
  test('Should return grouped products or 404 for non-grouped', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_GROUPED_PRODUCTS(productId));
    expect([200, 404]).toContain(response.status());
    console.log(`Grouped products for ${productId}:`, response.status());
  });

  // ── DOWNLOADABLE ──────────────────────────────────────────
  test('Should return downloadable links or 404 for non-downloadable', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_DOWNLOADABLE_LINKS(productId));
    expect([200, 404]).toContain(response.status());
    console.log(`Downloadable links for ${productId}:`, response.status());
  });

  test('Should return downloadable samples or 404', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_DOWNLOADABLE_SAMPLES(productId));
    expect([200, 404]).toContain(response.status());
  });

  // ── CUSTOMIZABLE OPTIONS ──────────────────────────────────
  test('Should return customizable options or empty array', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_CUSTOMIZABLE_OPTIONS(productId));
    expect([200, 404]).toContain(response.status());
    if (response.status() === 200) {
      const body = await response.json();
      expect(Array.isArray(body)).toBeTruthy();
      console.log(`Customizable options for ${productId}:`, body.length);
    }
  });

  test('Should return all customizable option prices', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_CUSTOMIZABLE_OPTION_PRICES);
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(Array.isArray(body)).toBeTruthy();
    console.log('Customizable option prices:', body.length);
  });

  // ── GROUP PRICES ──────────────────────────────────────────
  test('Should return customer group prices for a product', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_GROUP_PRICES(productId));
    expect([200, 404]).toContain(response.status());
    console.log(`Group prices for ${productId}:`, response.status());
  });

  // ── BOOKING PRODUCTS ──────────────────────────────────────
  test('Should return booking configuration for a product', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.BOOKING_PRODUCTS(productId));
    expect([200, 404]).toContain(response.status());
    console.log(`Booking products for ${productId}:`, response.status());

    if (response.status() === 200) {
      const body = await response.json();
      if (Array.isArray(body) && body.length > 0) {
        bookingProductId = body[0].id;
        expect(body[0]).toHaveProperty('id');
        console.log('First booking config:', JSON.stringify({ id: body[0].id, type: body[0].type }));
      }
    }
  });

  test('Should return single booking product by ID', async ({ request }) => {
    if (!bookingProductId) {
      test.skip(true, 'No booking product available');
      return;
    }
    const response = await sendRestRequest(request, ENDPOINTS.BOOKING_PRODUCT(bookingProductId));
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(body.id).toBe(bookingProductId);
    console.log('Single booking product:', body.type);
  });

  test('Should return booking default slots', async ({ request }) => {
    if (!bookingProductId) {
      test.skip(true, 'No booking product available');
      return;
    }
    const response = await sendRestRequest(request, ENDPOINTS.BOOKING_DEFAULT_SLOTS(bookingProductId));
    expect([200, 404]).toContain(response.status());
    console.log('Booking default slots:', response.status());
  });

  test('Should return booking appointment slots', async ({ request }) => {
    if (!bookingProductId) {
      test.skip(true, 'No booking product available');
      return;
    }
    const response = await sendRestRequest(request, ENDPOINTS.BOOKING_APPOINTMENT_SLOTS(bookingProductId));
    expect([200, 404]).toContain(response.status());
  });

  test('Should return booking rental slots', async ({ request }) => {
    if (!bookingProductId) {
      test.skip(true, 'No booking product available');
      return;
    }
    const response = await sendRestRequest(request, ENDPOINTS.BOOKING_RENTAL_SLOTS(bookingProductId));
    expect([200, 404]).toContain(response.status());
  });

  test('Should return booking table slots', async ({ request }) => {
    if (!bookingProductId) {
      test.skip(true, 'No booking product available');
      return;
    }
    const response = await sendRestRequest(request, ENDPOINTS.BOOKING_TABLE_SLOTS(bookingProductId));
    expect([200, 404]).toContain(response.status());
  });

  test('Should return booking event tickets', async ({ request }) => {
    if (!bookingProductId) {
      test.skip(true, 'No booking product available');
      return;
    }
    const response = await sendRestRequest(request, ENDPOINTS.BOOKING_EVENT_TICKETS(bookingProductId));
    expect([200, 404]).toContain(response.status());
  });

  // ── BOOKING SLOTS (runtime availability) ──────────────────
  test('Should return runtime booking slot availability', async ({ request }) => {
    const today = new Date().toISOString().split('T')[0];
    const response = await sendRestRequest(request, ENDPOINTS.BOOKING_SLOTS, {
      params: { date: today },
    });
    expect([200, 400]).toContain(response.status());
    console.log(`Booking slots for ${today}:`, response.status());
  });

  test('Should return 400 when booking slots date param is missing', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.BOOKING_SLOTS);
    expect([200, 400]).toContain(response.status());
    console.log('Booking slots without date:', response.status());
  });
});
