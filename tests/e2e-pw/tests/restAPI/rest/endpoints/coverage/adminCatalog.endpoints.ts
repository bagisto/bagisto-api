export const ADMIN_CATALOG_COVERAGE = {
  PRODUCTS: '/api/admin/catalog/products',
  PRODUCT: (id: number | string) => `/api/admin/catalog/products/${id}`,
  PRODUCTS_EXPORT: '/api/admin/catalog/products/export',
  PRODUCT_IMAGES: (productId: number | string) => `/api/admin/catalog/products/${productId}/images`,
  PRODUCT_VIDEOS: (productId: number | string) => `/api/admin/catalog/products/${productId}/videos`,
  PRODUCT_PICKER: '/api/admin/products',
  PRODUCT_IMAGE_IRI: (imageId: number | string) => `/api/admin/catalog/product-images/${imageId}`,
  PRODUCT_VIDEO_IRI: (videoId: number | string) => `/api/admin/catalog/product-videos/${videoId}`,
};

export const ADMIN_CMS_COVERAGE = {
  PAGES: '/api/admin/cms/pages',
  PAGE: (id: number | string) => `/api/admin/cms/pages/${id}`,
  PAGES_EXPORT: '/api/admin/cms/pages/export',
  CHANNELS: '/api/admin/settings/channels',
};

export const ADMIN_MARKETING_COVERAGE = {
  SEARCH_TERM: (id: number | string) => `/api/admin/marketing/search-terms/${id}`,
  CHANNELS: '/api/admin/settings/channels',
  LOCALES: '/api/admin/settings/locales',
};
