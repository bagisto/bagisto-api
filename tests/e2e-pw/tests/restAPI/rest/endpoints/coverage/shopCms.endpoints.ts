export const SHOP_CMS = {
  PAGES: '/api/shop/pages',
  PAGE: (id: number | string) => `/api/shop/pages/${id}`,
  PAGE_TRANSLATIONS: '/api/shop/page_translations',
  PAGE_TRANSLATION: (id: number | string) => `/api/shop/page_translations/${id}`,
  ADMIN_CMS_PAGES: '/api/admin/cms/pages',
  ADMIN_CMS_PAGE: (id: number | string) => `/api/admin/cms/pages/${id}`,
};
