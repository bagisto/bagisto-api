export const SHOP_CATALOG = {
  BUNDLE_OPTION_TRANSLATIONS: '/api/shop/product_bundle_option_translations',
  BUNDLE_OPTION_TRANSLATION: (id: number | string) => `/api/shop/product_bundle_option_translations/${id}`,
  GROUPED_PRODUCT: (id: number | string) => `/api/shop/product_grouped_products/${id}`,
  IMAGE_SEARCHES: '/api/shop/image-searches',
  THEME: '/api/shop/theme',
  CATEGORY_TREES: '/api/shop/category-trees',
  ADMIN_PRODUCTS: '/api/admin/catalog/products',
  ADMIN_PRODUCT: (id: number | string) => `/api/admin/catalog/products/${id}`,
  ADMIN_CATEGORIES: '/api/admin/catalog/categories',
  ADMIN_CATEGORY: (id: number | string) => `/api/admin/catalog/categories/${id}`,
};
