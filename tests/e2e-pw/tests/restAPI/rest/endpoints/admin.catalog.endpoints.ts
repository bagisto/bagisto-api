
export const ADMIN_CATALOG = {
  CATEGORIES: '/api/admin/catalog/categories',
  CATEGORY: (id: number | string) => `/api/admin/catalog/categories/${id}`,
  CATEGORY_TREE: '/api/admin/catalog/categories/tree',
  CATEGORIES_MASS_DELETE: '/api/admin/catalog/categories/mass-delete',
  CATEGORIES_MASS_UPDATE_STATUS: '/api/admin/catalog/categories/mass-update-status',
  ATTRIBUTES: '/api/admin/catalog/attributes',
  ATTRIBUTE: (id: number | string) => `/api/admin/catalog/attributes/${id}`,
  ATTRIBUTES_MASS_DELETE: '/api/admin/catalog/attributes/mass-delete',
  ATTRIBUTE_OPTIONS: (attributeId: number | string) =>
    `/api/admin/catalog/attributes/${attributeId}/options`,
  ATTRIBUTE_OPTION: (attributeId: number | string, optionId: number | string) =>
    `/api/admin/catalog/attributes/${attributeId}/options/${optionId}`,
  FAMILIES: '/api/admin/catalog/families',
  FAMILY: (id: number | string) => `/api/admin/catalog/families/${id}`,
  PRODUCTS: '/api/admin/catalog/products',
  PRODUCT: (id: number | string) => `/api/admin/catalog/products/${id}`,
  PRODUCT_COPY: (sourceId: number | string) =>
    `/api/admin/catalog/products/${sourceId}/copy`,
  PRODUCTS_MASS_DELETE: '/api/admin/catalog/products/mass-delete',
  PRODUCTS_MASS_UPDATE_STATUS: '/api/admin/catalog/products/mass-update-status',
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
  PRODUCT_VIDEOS: (productId: number | string) =>
    `/api/admin/catalog/products/${productId}/videos`,
  PRODUCT_VIDEO: (productId: number | string, id: number | string) =>
    `/api/admin/catalog/products/${productId}/videos/${id}`,
};
