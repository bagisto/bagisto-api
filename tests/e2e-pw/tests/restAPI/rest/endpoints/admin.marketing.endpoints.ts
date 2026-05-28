// rest/endpoints/admin.marketing.endpoints.ts
//
// W4a — Admin Marketing endpoint registry. Self-contained (not added to the
// central ENDPOINTS const) so this file can land without merge conflicts with
// other parallel waves touching endpoints.ts.

export const ADMIN_MARKETING = {
  // ── Promotions ── Catalog Rules ─────────────────────────────
  CATALOG_RULES: '/api/admin/marketing/catalog-rules',
  CATALOG_RULE: (id: number | string) => `/api/admin/marketing/catalog-rules/${id}`,
  CATALOG_RULES_MASS_DELETE: '/api/admin/marketing/catalog-rules/mass-delete',

  // ── Promotions ── Cart Rules ────────────────────────────────
  CART_RULES: '/api/admin/marketing/cart-rules',
  CART_RULE: (id: number | string) => `/api/admin/marketing/cart-rules/${id}`,
  CART_RULES_MASS_DELETE: '/api/admin/marketing/cart-rules/mass-delete',

  // ── Promotions ── Cart Rule Coupons (sub-resource) ──────────
  CART_RULE_COUPONS: (cartRuleId: number | string) =>
    `/api/admin/marketing/cart-rules/${cartRuleId}/coupons`,
  CART_RULE_COUPON: (cartRuleId: number | string, id: number | string) =>
    `/api/admin/marketing/cart-rules/${cartRuleId}/coupons/${id}`,
  CART_RULE_COUPONS_GENERATE: (cartRuleId: number | string) =>
    `/api/admin/marketing/cart-rules/${cartRuleId}/coupons/generate`,
  CART_RULE_COUPONS_MASS_DELETE: (cartRuleId: number | string) =>
    `/api/admin/marketing/cart-rules/${cartRuleId}/coupons/mass-delete`,

  // ── Communications ── Email Templates ───────────────────────
  TEMPLATES: '/api/admin/marketing/templates',
  TEMPLATE: (id: number | string) => `/api/admin/marketing/templates/${id}`,

  // ── Communications ── Events ────────────────────────────────
  EVENTS: '/api/admin/marketing/events',
  EVENT: (id: number | string) => `/api/admin/marketing/events/${id}`,

  // ── Communications ── Campaigns ─────────────────────────────
  CAMPAIGNS: '/api/admin/marketing/campaigns',
  CAMPAIGN: (id: number | string) => `/api/admin/marketing/campaigns/${id}`,
  CAMPAIGN_SEND: (id: number | string) => `/api/admin/marketing/campaigns/${id}/send`,

  // ── Communications ── Newsletter Subscribers ────────────────
  SUBSCRIBERS: '/api/admin/marketing/subscribers',
  SUBSCRIBER: (id: number | string) => `/api/admin/marketing/subscribers/${id}`,

  // ── Search SEO ── URL Rewrites ──────────────────────────────
  URL_REWRITES: '/api/admin/marketing/url-rewrites',
  URL_REWRITE: (id: number | string) => `/api/admin/marketing/url-rewrites/${id}`,
  URL_REWRITES_MASS_DELETE: '/api/admin/marketing/url-rewrites/mass-delete',

  // ── Search SEO ── Search Terms ──────────────────────────────
  SEARCH_TERMS: '/api/admin/marketing/search-terms',
  SEARCH_TERM: (id: number | string) => `/api/admin/marketing/search-terms/${id}`,
  SEARCH_TERMS_MASS_DELETE: '/api/admin/marketing/search-terms/mass-delete',

  // ── Search SEO ── Search Synonyms ───────────────────────────
  SEARCH_SYNONYMS: '/api/admin/marketing/search-synonyms',
  SEARCH_SYNONYM: (id: number | string) => `/api/admin/marketing/search-synonyms/${id}`,
  SEARCH_SYNONYMS_MASS_DELETE: '/api/admin/marketing/search-synonyms/mass-delete',

  // ── Search SEO ── Sitemaps ──────────────────────────────────
  SITEMAPS: '/api/admin/marketing/sitemaps',
  SITEMAP: (id: number | string) => `/api/admin/marketing/sitemaps/${id}`,
  SITEMAP_GENERATE: (id: number | string) => `/api/admin/marketing/sitemaps/${id}/generate`,
};
