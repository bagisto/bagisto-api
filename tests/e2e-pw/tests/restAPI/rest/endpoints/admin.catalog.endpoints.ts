// rest/endpoints/admin.catalog.endpoints.ts
//
// W1 — Admin Catalog endpoint registry. Self-contained (not added to the
// central ENDPOINTS const) so this file can land without merge conflicts with
// other parallel waves touching endpoints.ts.

export const ADMIN_CATALOG = {
  // ── Categories ──────────────────────────────────────────────
  CATEGORIES: '/api/admin/catalog/categories',
  CATEGORY: (id: number | string) => `/api/admin/catalog/categories/${id}`,
  CATEGORY_TREE: '/api/admin/catalog/categories/tree',
  CATEGORIES_MASS_DELETE: '/api/admin/catalog/categories/mass-delete',
  CATEGORIES_MASS_UPDATE_STATUS: '/api/admin/catalog/categories/mass-update-status',

  // ── Attributes ──────────────────────────────────────────────
  ATTRIBUTES: '/api/admin/catalog/attributes',
  ATTRIBUTE: (id: number | string) => `/api/admin/catalog/attributes/${id}`,
  ATTRIBUTES_MASS_DELETE: '/api/admin/catalog/attributes/mass-delete',

  // ── Attribute Options (sub-resource) ────────────────────────
  ATTRIBUTE_OPTIONS: (attributeId: number | string) =>
    `/api/admin/catalog/attributes/${attributeId}/options`,
  ATTRIBUTE_OPTION: (attributeId: number | string, optionId: number | string) =>
    `/api/admin/catalog/attributes/${attributeId}/options/${optionId}`,

  // ── Attribute Families ──────────────────────────────────────
  FAMILIES: '/api/admin/catalog/families',
  FAMILY: (id: number | string) => `/api/admin/catalog/families/${id}`,

  // ── Products ────────────────────────────────────────────────
  PRODUCTS: '/api/admin/catalog/products',
  PRODUCT: (id: number | string) => `/api/admin/catalog/products/${id}`,
  PRODUCT_COPY: (sourceId: number | string) =>
    `/api/admin/catalog/products/${sourceId}/copy`,
  PRODUCTS_MASS_DELETE: '/api/admin/catalog/products/mass-delete',
  PRODUCTS_MASS_UPDATE_STATUS: '/api/admin/catalog/products/mass-update-status',

  // ── Product sub-resources ───────────────────────────────────
  PRODUCT_INVENTORIES: (productId: number | string) =>
    `/api/admin/catalog/products/${productId}/inventories`,
  PRODUCT_CUSTOMER_GROUP_PRICES: (productId: number | string) =>
    `/api/admin/catalog/products/${productId}/customer-group-prices`,
  PRODUCT_CUSTOMER_GROUP_PRICE: (productId: number | string, id: number | string) =>
    `/api/admin/catalog/products/${productId}/customer-group-prices/${id}`,
  PRODUCT_IMAGES: (productId: number | string) =>
    `/api/admin/catalog/products/${productId}/images`,
  PRODUCT_IMAGES_REORDER: (productId: number | string) =>
    `/api/admin/catalog/products/${productId}/images/reorder`,
  PRODUCT_IMAGE: (productId: number | string, id: number | string) =>
    `/api/admin/catalog/products/${productId}/images/${id}`,
};
