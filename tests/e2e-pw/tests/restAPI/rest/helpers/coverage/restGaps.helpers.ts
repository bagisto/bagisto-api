import { APIRequestContext, APIResponse, expect } from '@playwright/test';
import { sendAdminMultipartRequest, sendAdminRequest, MultipartFile } from '../adminClient';
import { sendRestRequest } from '../restClient';
import { env } from '../../../config/env';

export const REST_GAPS = {
  COUNTRIES: '/api/shop/countries',
  COUNTRY_STATES: '/api/shop/country-states',
  COUNTRY_STATE_OF: (countryId: number | string, id: number | string) => `/api/shop/countries/${countryId}/states/${id}`,
  CUSTOMIZABLE_OPTION_PRICE: (id: number | string) => `/api/shop/product_customizable_option_prices/${id}`,
  CUSTOMIZABLE_OPTION_TRANSLATIONS: '/api/shop/product_customizable_option_translations',
  CUSTOMIZABLE_OPTION_TRANSLATION: (id: number | string) => `/api/shop/product_customizable_option_translations/${id}`,
  PRODUCT_ATTRIBUTE_FILE: (productId: number | string, attributeId: number | string) =>
    `/api/shop/products/${productId}/attribute-files/${attributeId}`,
  REVIEW_TRANSLATION: (productId: number | string, reviewId: number | string) =>
    `/api/shop/products/${productId}/reviews/${reviewId}/translation`,
  BUNDLE_OPTION_PRODUCT: (id: number | string) => `/api/shop/product-bundle-option-products/${id}`,
  BOOKING_PRODUCTS_OF: (productId: number | string) => `/api/shop/products/${productId}/booking-products`,
  BOOKING_APPOINTMENT_SLOTS: '/api/shop/booking_product_appointment_slots',
  BOOKING_DEFAULT_SLOTS: '/api/shop/booking_product_default_slots',
  BOOKING_EVENT_TICKETS: '/api/shop/booking_product_event_tickets',
  BOOKING_RENTAL_SLOTS: '/api/shop/booking_product_rental_slots',
  BOOKING_TABLE_SLOTS: '/api/shop/booking_product_table_slots',
  SHOP_CUSTOMER_LOGIN: '/api/shop/customer/login',
  CUSTOMER_DOWNLOADABLE_PRODUCT: (id: number | string) => `/api/shop/customer-downloadable-products/${id}`,
  CUSTOMER_DOWNLOADABLE_PRODUCT_DOWNLOAD: (id: number | string) => `/api/shop/customer-downloadable-products/${id}/download`,

  ADMIN_PRODUCTS: '/api/admin/catalog/products',
  ADMIN_PRODUCT: (id: number | string) => `/api/admin/catalog/products/${id}`,
  ADMIN_DOWNLOADABLE_LINK_UPLOAD: (productId: number | string) => `/api/admin/catalog/products/${productId}/downloadable-links/upload`,
  ADMIN_DOWNLOADABLE_SAMPLE_UPLOAD: (productId: number | string) => `/api/admin/catalog/products/${productId}/downloadable-samples/upload`,
  ADMIN_DOWNLOADABLE_DOWNLOAD: (productId: number | string, attributeId: number | string) =>
    `/api/admin/catalog/products/${productId}/downloadable/${attributeId}/download`,
  ADMIN_CONFIGURATION: '/api/admin/configuration',

  CUSTOMER_ADDRESS: (customerId: number | string, id: number | string) => `/api/admin/customers/${customerId}/addresses/${id}`,
  CUSTOMER_ADDRESS_SET_DEFAULT: (customerId: number | string, id: number | string) =>
    `/api/admin/customers/${customerId}/addresses/${id}/set-default`,
  CUSTOMER_CART_ITEMS: (customerId: number | string) => `/api/admin/customers/${customerId}/cart-items`,
  CUSTOMER_COMPARE_ITEMS: (customerId: number | string) => `/api/admin/customers/${customerId}/compare-items`,
  CUSTOMER_RECENT_ORDER_ITEMS: (customerId: number | string) => `/api/admin/customers/${customerId}/recent-order-items`,
  CUSTOMER_WISHLIST_ITEMS: (customerId: number | string) => `/api/admin/customers/${customerId}/wishlist-items`,

  CART_RULE_COPY: (id: number | string) => `/api/admin/marketing/cart-rules/${id}/copy`,
  INVOICE_SEND_DUPLICATE: (id: number | string) => `/api/admin/invoices/${id}/send-duplicate`,

  RMA_REQUESTS: '/api/admin/rma/requests',
  RMA_REQUEST: (id: number | string) => `/api/admin/rma/requests/${id}`,
  RMA_REQUEST_REOPEN: (id: number | string) => `/api/admin/rma/requests/${id}/reopen`,
  RMA_REQUEST_UPDATE_STATUS: (id: number | string) => `/api/admin/rma/requests/${id}/update-status`,
  RMA_REQUEST_ORDER_ITEMS: '/api/admin/rma/requests/order-items',
  RMA_REASONS: '/api/admin/rma/reasons',
  RMA_REASON: (id: number | string) => `/api/admin/rma/reasons/${id}`,

  IMPORTS: '/api/admin/settings/data-transfer/imports',
  IMPORT: (id: number | string) => `/api/admin/settings/data-transfer/imports/${id}`,
  IMPORT_ACTION: (id: number | string, action: string) => `/api/admin/settings/data-transfer/imports/${id}/${action}`,
};

export const REST_GAPS_MESSAGES = {
  MISSING_STOREFRONT_KEY: 'X-STOREFRONT-KEY header is required',
  GENERIC_NOT_FOUND: 'Not Found',
  SHOP_PRODUCT_NOT_FOUND: 'Product not found',
  REVIEW_TRANSLATION_FORBIDDEN: '403 Forbidden',
  DOWNLOAD_UNAUTHENTICATED: 'Unauthorized: Customer authentication required.',
  DOWNLOAD_NOT_FOUND: 'Downloadable product not found.',
  ADMIN_UNAUTHENTICATED: 'Unauthenticated.',
  ADMIN_PRODUCT_NOT_FOUND: 'Product not found.',
  DOWNLOADABLE_FILE_REQUIRED: 'A file is required (multipart field "file").',
  DOWNLOADABLE_FILE_NOT_FOUND: 'No stored file found for this product and attribute.',
  CART_RULE_NOT_FOUND: 'Cart rule not found.',
  CART_RULE_COPY_OF: (name: string) => `Copy of ${name}`,
  INVOICE_NOT_FOUND: 'Invoice not found.',
  INVOICE_EMAIL_INVALID: 'A valid recipient email is required.',
  INVOICE_DUPLICATE_SENT: (email: string) => `Invoice email sent to ${email}.`,
  CUSTOMER_NOT_FOUND: 'Customer not found.',
  ADDRESS_NOT_FOUND: 'Address not found.',
  ADDRESS_NOT_OWNED: 'This address does not belong to the specified customer.',
  RETURN_NOT_FOUND: 'Return request not found.',
  RETURN_INVALID_STATUS: 'Invalid return status.',
  RETURN_REOPEN_NOT_ALLOWED: 'This return request cannot be reopened.',
  IMPORT_NOT_FOUND: 'Import not found.',
  IMPORT_NOTHING_TO_IMPORT: 'There is nothing to import.',
  IMPORT_NOT_VALID: 'The import is not valid.',
  IMPORT_ERROR_REPORT_NOT_FOUND: 'No error report is available for download.',
  IMPORT_INVALID_PRODUCT_TYPE: 'Product type is invalid or not supported',
};

export const BINARY_HEADERS = { Accept: 'application/octet-stream' };

export const UNKNOWN_ID = 999999999;

export const RMA_STATUS = {
  PENDING: 1,
  DECLINED: 7,
};

export const IMPORT_PRODUCT_COLUMNS = [
  'sku', 'parent_sku', 'locale', 'attribute_family_code', 'type', 'categories', 'images', 'name',
  'description', 'short_description', 'status', 'visible_individually', 'new', 'featured',
  'guest_checkout', 'length', 'width', 'height', 'weight', 'tax_category_name', 'price', 'cost',
  'special_price', 'special_price_from', 'special_price_to', 'customer_group_prices', 'url_key',
  'meta_title', 'meta_keywords', 'meta_description', 'manage_stock', 'inventories', 'related_skus',
  'cross_sell_skus', 'up_sell_skus', 'configurable_variants', 'bundle_options', 'associated_skus',
  'booking_options',
];

export type ImportAction = 'validate' | 'start' | 'link' | 'index';

export interface ShopLogin {
  token: string;
}

export async function safeJson(response: APIResponse): Promise<any> {
  try {
    return await response.json();
  } catch {
    return null;
  }
}

export function detailOf(body: any): string {
  return String(body?.detail ?? body?.message ?? '');
}

export async function expectDetail(response: APIResponse, status: number, detail: string): Promise<any> {
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(status);
  expect(detailOf(body), JSON.stringify(body)).toBe(detail);

  return body;
}

export async function expectAdminUnauthenticated(response: APIResponse): Promise<void> {
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(401);
  expect(body?.message).toBe(REST_GAPS_MESSAGES.ADMIN_UNAUTHENTICATED);
}

export async function expectMissingStorefrontKey(response: APIResponse): Promise<void> {
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(401);
  expect(body?.message).toBe(REST_GAPS_MESSAGES.MISSING_STOREFRONT_KEY);
  expect(body?.error).toBe('missing_key');
}

export function rowsOf(body: any): any[] {
  if (Array.isArray(body)) {
    return body;
  }

  return Array.isArray(body?.data) ? body.data : [];
}

export function numericIdOf(value: any): number {
  if (typeof value === 'number') {
    return value;
  }

  if (value && typeof value === 'object') {
    return numericIdOf(value._id ?? value.id);
  }

  return Number(String(value ?? '').split('/').pop());
}

export function uniqueStamp(): string {
  return `${Date.now()}${Math.floor(Math.random() * 1_000_000)}`;
}

export function uniqueSku(prefix: string): string {
  return `${prefix}-${Date.now()}-${Math.floor(Math.random() * 1e6)}`;
}

export function basenameOf(path: string): string {
  return String(path).split('/').pop() ?? '';
}

export async function getWithoutStorefrontKey(request: APIRequestContext, endpoint: string): Promise<APIResponse> {
  return request.get(`${env.baseUrl}${endpoint}`, {
    headers: { Accept: 'application/json' },
  });
}

export async function postMultipartWithoutToken(
  request: APIRequestContext,
  endpoint: string,
  fields: Record<string, string | number | MultipartFile>
): Promise<APIResponse> {
  return request.fetch(`${env.baseUrl}${endpoint}`, {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      'X-STOREFRONT-KEY': env.storefrontAccessKey,
    },
    multipart: fields,
  });
}

export async function createAdminProduct(request: APIRequestContext, type: string): Promise<{ id: number; sku: string }> {
  const sku = uniqueSku(`e2e-gap-${type}`);
  const response = await sendAdminRequest(request, REST_GAPS.ADMIN_PRODUCTS, {
    method: 'POST',
    data: { sku, type, attribute_family_id: 1 },
  });
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(201);
  expect(typeof body?.id).toBe('number');

  return { id: body.id, sku };
}

export async function updateAdminProduct(request: APIRequestContext, id: number, data: Record<string, any>): Promise<any> {
  const response = await sendAdminRequest(request, REST_GAPS.ADMIN_PRODUCT(id), {
    method: 'PUT',
    data,
  });
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);

  return body;
}

export async function readAdminProduct(request: APIRequestContext, id: number): Promise<any> {
  const response = await sendAdminRequest(request, REST_GAPS.ADMIN_PRODUCT(id));
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);

  return body;
}

export async function deleteAdminProducts(request: APIRequestContext, ids: number[]): Promise<void> {
  for (const id of [...ids].reverse()) {
    await sendAdminRequest(request, REST_GAPS.ADMIN_PRODUCT(id), { method: 'DELETE' });
  }
}

export async function collectPages(
  request: APIRequestContext,
  endpoint: string,
  predicate: (row: any) => boolean
): Promise<any[]> {
  const matches: any[] = [];

  for (let page = 1; page <= 200; page++) {
    const response = await sendRestRequest(request, endpoint, {
      params: { page: String(page), per_page: '50' },
    });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(Array.isArray(body), JSON.stringify(body)).toBe(true);

    matches.push(...body.filter(predicate));

    if (body.length < 50) {
      break;
    }
  }

  return matches;
}

export async function shopBookingProductId(request: APIRequestContext, productId: number): Promise<number> {
  const response = await sendRestRequest(request, REST_GAPS.BOOKING_PRODUCTS_OF(productId));
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);

  const rows = rowsOf(body);

  expect(rows, JSON.stringify(body)).toHaveLength(1);

  return numericIdOf(rows[0].id);
}

export async function adminConfigValue(request: APIRequestContext, slug: string, key: string): Promise<string | null> {
  const response = await sendAdminRequest(request, REST_GAPS.ADMIN_CONFIGURATION, {
    params: { slug, channel: 'default' },
  });
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);

  const payload = Array.isArray(body) ? body[0] : body;
  const value = payload?.values?.[key];

  return value === undefined || value === null ? null : String(value);
}

export async function shopLogin(request: APIRequestContext, email: string, password: string): Promise<string> {
  const response = await sendRestRequest(request, REST_GAPS.SHOP_CUSTOMER_LOGIN, {
    method: 'POST',
    data: { email, password },
  });
  const body = await safeJson(response);

  expect([200, 201], JSON.stringify(body)).toContain(response.status());
  expect(typeof body?.token).toBe('string');

  return body.token;
}

export function productImportCsv(sku: string, type: string): Buffer {
  const row: Record<string, string> = {
    sku,
    locale: 'en',
    attribute_family_code: 'default',
    type,
    name: `E2E Gap Import ${sku}`,
    description: 'E2E gap import description',
    short_description: 'E2E gap import short description',
    status: '1',
    visible_individually: '1',
    new: '0',
    featured: '0',
    guest_checkout: '0',
    weight: '1.23',
    price: '14',
    url_key: sku,
    manage_stock: '0',
  };

  const line = IMPORT_PRODUCT_COLUMNS.map(column => row[column] ?? '').join(',');

  return Buffer.from(`${IMPORT_PRODUCT_COLUMNS.join(',')}\n${line}\n`);
}

export async function createProductImport(request: APIRequestContext, sku: string, csv: Buffer): Promise<any> {
  const response = await sendAdminMultipartRequest(request, REST_GAPS.IMPORTS, {
    type: 'products',
    action: 'append',
    validation_strategy: 'stop-on-errors',
    allowed_errors: 0,
    field_separator: ',',
    process_in_queue: '0',
    image_source: 'url',
    file: { name: `${sku}.csv`, mimeType: 'text/csv', buffer: csv },
  });
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(201);
  expect(typeof body?.id).toBe('number');
  expect(body.state).toBe('pending');
  expect(typeof body.filePath).toBe('string');

  return body;
}

export async function runImportAction(request: APIRequestContext, importId: number, action: ImportAction): Promise<APIResponse> {
  return sendAdminRequest(request, REST_GAPS.IMPORT_ACTION(importId, action), {
    method: 'POST',
    data: {},
  });
}

export async function runImportActionOk(request: APIRequestContext, importId: number, action: ImportAction): Promise<any> {
  const response = await runImportAction(request, importId, action);
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);
  expect(body.id).toBe(importId);
  expect(body.success).toBe(true);

  return body;
}

export async function importStats(request: APIRequestContext, importId: number, state?: string): Promise<APIResponse> {
  return sendAdminRequest(request, REST_GAPS.IMPORT_ACTION(importId, 'stats'), state ? { params: { state } } : {});
}

export async function readImport(request: APIRequestContext, importId: number): Promise<any> {
  const response = await sendAdminRequest(request, REST_GAPS.IMPORT(importId));
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);

  return body;
}

export async function deleteImport(request: APIRequestContext, importId: number): Promise<void> {
  await sendAdminRequest(request, REST_GAPS.IMPORT(importId), { method: 'DELETE' });
}

export async function productIdsBySku(request: APIRequestContext, sku: string): Promise<number[]> {
  const response = await sendAdminRequest(request, REST_GAPS.ADMIN_PRODUCTS, {
    params: { sku, per_page: '50' },
  });
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);

  return rowsOf(body).filter((row: any) => row.sku === sku).map((row: any) => Number(row.id));
}
