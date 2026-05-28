// rest/endpoints/endpoints.ts

export const ENDPOINTS = {
  // ── ATTRIBUTES ──────────────────────────────────────────────
  ATTRIBUTES: '/api/shop/attributes',
  ATTRIBUTE: (id: number | string) => `/api/shop/attributes/${id}`,
  ATTRIBUTE_OPTIONS: '/api/shop/attribute-options',
  ATTRIBUTE_OPTION: (id: number | string) => `/api/shop/attribute-options/${id}`,
  ATTRIBUTE_TRANSLATIONS: '/api/shop/attribute_translations',
  ATTRIBUTE_TRANSLATION: (id: number | string) => `/api/shop/attribute_translations/${id}`,

  // ── PRODUCTS ────────────────────────────────────────────────
  PRODUCTS: '/api/shop/products',
  PRODUCT: (id: number | string) => `/api/shop/products/${id}`,
  PRODUCT_SEARCH: '/api/shop/products',
  PRODUCT_IMAGES: (id: number | string) => `/api/shop/products/${id}/images`,
  PRODUCT_IMAGE: (id: number | string) => `/api/shop/product-images/${id}`,
  ALL_PRODUCT_IMAGES: '/api/shop/product-images',
  PRODUCT_VIDEOS: (id: number | string) => `/api/shop/products/${id}/videos`,
  PRODUCT_VIDEO: (id: number | string) => `/api/shop/product-videos/${id}`,
  ALL_PRODUCT_VIDEOS: '/api/shop/product-videos',
  PRODUCT_GROUP_PRICES: (id: number | string) => `/api/shop/products/${id}/customer-group-prices`,
  CUSTOMER_GROUP_PRICE: (id: number | string) => `/api/shop/customer-group-prices/${id}`,
  PRODUCT_CUSTOMIZABLE_OPTIONS: (id: number | string) => `/api/shop/products/${id}/customizable-options`,
  PRODUCT_CUSTOMIZABLE_OPTION: (id: number | string) => `/api/shop/product_customizable_options/${id}`,
  PRODUCT_CUSTOMIZABLE_OPTION_PRICES: '/api/shop/product_customizable_option_prices',
  PRODUCT_CUSTOMIZABLE_OPTION_TRANSLATIONS: '/api/shop/product_customizable_option_translations',
  PRODUCT_ATTRIBUTE_VALUES: (id: number | string) => `/api/shop/products/${id}/attribute-values`,
  PRODUCT_REVIEWS: (id: number | string) => `/api/shop/products/${id}/reviews`,
  PRODUCT_REVIEW: (productId: number | string, reviewId: number | string) => `/api/shop/products/${productId}/reviews/${reviewId}`,

  // ── PRODUCT TYPES ───────────────────────────────────────────
  PRODUCT_VARIANTS: (id: number | string) => `/api/shop/products/${id}/variants`,
  BOOKING_PRODUCTS: (id: number | string) => `/api/shop/products/${id}/booking-products`,
  BOOKING_PRODUCT: (id: number | string) => `/api/shop/booking-products/${id}`,
  BOOKING_DEFAULT_SLOTS: (id: number | string) => `/api/shop/booking_product_default_slots/${id}`,
  BOOKING_APPOINTMENT_SLOTS: (id: number | string) => `/api/shop/booking_product_appointment_slots/${id}`,
  BOOKING_RENTAL_SLOTS: (id: number | string) => `/api/shop/booking_product_rental_slots/${id}`,
  BOOKING_TABLE_SLOTS: (id: number | string) => `/api/shop/booking_product_table_slots/${id}`,
  BOOKING_EVENT_TICKETS: (id: number | string) => `/api/shop/booking_product_event_tickets/${id}`,
  BOOKING_SLOTS: '/api/shop/booking-slots',
  PRODUCT_BUNDLE_OPTIONS: (id: number | string) => `/api/shop/products/${id}/bundle-options`,
  PRODUCT_BUNDLE_OPTION: (id: number | string) => `/api/shop/product_bundle_options/${id}`,
  ALL_BUNDLE_OPTION_PRODUCTS: '/api/shop/product-bundle-option-products',
  PRODUCT_GROUPED_PRODUCTS: (id: number | string) => `/api/shop/products/${id}/grouped-products`,
  PRODUCT_DOWNLOADABLE_LINKS: (id: number | string) => `/api/shop/products/${id}/downloadable-links`,
  PRODUCT_DOWNLOADABLE_SAMPLES: (id: number | string) => `/api/shop/products/${id}/downloadable-samples`,

  // ── CATEGORIES ──────────────────────────────────────────────
  CATEGORIES: '/api/shop/categories',
  CATEGORY: (id: number | string) => `/api/shop/categories/${id}`,
  CATEGORY_TREE: '/api/shop/category-trees',
  CATEGORY_TRANSLATIONS: '/api/shop/category_translations',
  CATEGORY_TRANSLATION: (id: number | string) => `/api/shop/category_translations/${id}`,

  // ── CART ────────────────────────────────────────────────────
  CREATE_CART: '/api/shop/cart',
  GET_CART: '/api/shop/cart',
  ADD_TO_CART: '/api/shop/cart/items',
  UPDATE_CART_ITEM: (id: number | string) => `/api/shop/cart/items/${id}`,
  REMOVE_CART_ITEM: (id: number | string) => `/api/shop/cart/items/${id}`,
  APPLY_COUPON: '/api/shop/cart/coupon',
  REMOVE_COUPON: '/api/shop/cart/coupon',

  // ── CHECKOUT ────────────────────────────────────────────────
  CHECKOUT_ADDRESSES: '/api/shop/checkout/addresses',
  SET_SHIPPING_ADDRESS: '/api/shop/checkout/shipping-address',
  SET_BILLING_ADDRESS: '/api/shop/checkout/billing-address',
  CHECKOUT_SHIPPING_METHODS: '/api/shop/checkout/shipping-methods',
  SET_SHIPPING_METHOD: '/api/shop/checkout/shipping-method',
  CHECKOUT_PAYMENT_METHODS: '/api/shop/checkout/payment-methods',
  SET_PAYMENT_METHOD: '/api/shop/checkout/payment-method',
  PLACE_ORDER: '/api/shop/checkout/order',
  ESTIMATE_SHIPPING: '/api/estimate_shippings',

// ── CUSTOMERS ───────────────────────────────────────────────
  CUSTOMER_REGISTER: '/api/shop/customers',
  CUSTOMER_LOGIN: '/api/shop/customer/login',
  CUSTOMER_LOGOUT: '/api/shop/customers/logout',
  CUSTOMER_VERIFY_TOKEN: '/api/shop/customers/verify-token',
  CUSTOMER_FORGOT_PASSWORD: '/api/shop/customers/forgot-password',
  CUSTOMER_RESET_PASSWORD: '/api/shop/customers/reset-password',
  CUSTOMER_PROFILE: '/api/shop/customers/profile',
  CUSTOMER_DELETE_ACCOUNT: '/api/shop/customers/profile',
  CUSTOMER_ADDRESSES: '/api/shop/customers/addresses',
  CUSTOMER_ADDRESS: (id: number | string) => `/api/shop/customer-addresses/${id}`,
  CUSTOMER_ADDRESS_CREATE: '/api/shop/customer-addresses',
  CUSTOMER_ORDERS: '/api/shop/customer-orders',
  CUSTOMER_ORDER: (id: number | string) => `/api/shop/customer-orders/${id}`,
  CUSTOMER_DOWNLOADABLE_PRODUCTS: '/api/shop/customer-downloadable-products',
  CUSTOMER_DOWNLOADABLE_PRODUCT: (id: number | string) => `/api/shop/customer-downloadable-products/${id}`,
  CUSTOMER_INVOICES: '/api/shop/customer-invoices',
  CUSTOMER_INVOICE: (id: number | string) => `/api/shop/customer-invoices/${id}`,
  CUSTOMER_INVOICE_PDF: (id: number | string) => `/api/shop/customer-invoices/${id}/pdf`,
  CUSTOMER_REVIEWS: '/api/shop/customer-reviews',
  CUSTOMER_REVIEW: (id: number | string) => `/api/shop/customer-reviews/${id}`,

  // ── LEGACY / ALTERNATIVE PATHS ──────────────────────────────
  CART_TOKEN: '/api/cart_tokens',
  CART_TOKEN_BY_ID: (id: number | string) => `/api/cart_tokens/${id}`,
  ADD_PRODUCT_TO_CART: '/api/shop/add-product-in-cart',
  UPDATE_CART_ITEMS: '/api/update_cart_items',
  REMOVE_CART_ITEM_LEGACY: '/api/shop/remove-cart-item',
  REMOVE_CART_ITEMS_BY_ID: (id: number | string) => `/api/remove_cart_items/${id}`,
  BATCH_REMOVE_CART_ITEMS: '/api/remove_cart_items',
  READ_CART: '/api/read_carts',
  READ_CART_BY_ID: (id: number | string) => `/api/read_carts/${id}`,
  APPLY_COUPON_LEGACY: '/api/shop/apply-coupon',
  MERGE_CARTS: '/api/merge_carts',
  CHECKOUT_ADDRESSES_LEGACY: '/api/checkout_addresses',
  CHECKOUT_ADDRESS_BY_ID: (id: number | string) => `/api/checkout_addresses/${id}`,
  CHECKOUT_SHIPPING_METHODS_LEGACY: '/api/checkout_shipping_methods',
  CHECKOUT_SHIPPING_METHOD_BY_ID: (id: number | string) => `/api/checkout_shipping_methods/${id}`,
  PLACE_ORDER_LEGACY: '/api/checkout_orders',
  MUTATIONS_CREATE_CART: '/api/mutations/create-cart',
  MUTATIONS_ADD_TO_CART: '/api/mutations/add-to-cart',
  CART_CREATE_ALT: '/api/carts',
  CART_ITEM: (id: number | string) => `/api/cart_items/${id}`,

  // ── COUNTRIES ───────────────────────────────────────────────
  COUNTRIES: '/api/shop/countries',
  COUNTRY: (id: number | string) => `/api/shop/countries/${id}`,
  COUNTRY_STATES_NESTED: (id: number | string) => `/api/shop/countries/${id}/states`,
  COUNTRY_STATES: '/api/shop/countries/states',
  COUNTRY_STATE: (id: number | string) => `/api/shop/country-states/${id}`,

  // ── CHANNELS ────────────────────────────────────────────────
  CHANNELS: '/api/shop/channels',
  CHANNEL: (id: number | string) => `/api/shop/channels/${id}`,
  CHANNEL_TRANSLATIONS: '/api/shop/channel_translations',
  CHANNEL_TRANSLATION: (id: number | string) => `/api/shop/channel_translations/${id}`,

  // ── LOCALES ─────────────────────────────────────────────────
  LOCALES: '/api/shop/locales',
  LOCALE: (id: number | string) => `/api/shop/locales/${id}`,

  // ── CMS PAGES ───────────────────────────────────────────────
  CMS_PAGES: '/api/shop/cms_pages',

  // ── THEME CUSTOMIZATIONS ───────────────────────────────────
  THEME_CUSTOMIZATIONS: '/api/shop/theme-customizations',
  THEME_CUSTOMIZATION: (id: number | string) => `/api/shop/theme-customizations/${id}`,

  // ── SHOP DOCS ───────────────────────────────────────────────
  SHOP_DOCS: '/api/shop/shop_docs',

  // ── CONTACT US ──────────────────────────────────────────────
  CONTACT_US: '/api/shop/contact-us',

  // ── NEWSLETTER ──────────────────────────────────────────────
  NEWSLETTER_SUBSCRIBE: '/api/shop/newsletters',

  // ── ESTIMATE SHIPPING (GraphQL only — kept for completeness) ─
  ESTIMATE_SHIPPING_REST: '/api/shop/estimate-shippings',

  // ── CMS PAGES ───────────────────────────────────────────────
  CMS_PAGE: (id: number | string) => `/api/shop/cms_pages/${id}`,

  // ── CURRENCIES ──────────────────────────────────────────────
  CURRENCIES: '/api/shop/currencies',
  CURRENCY: (id: number | string) => `/api/shop/currencies/${id}`,

  // ── DEFAULT CHANNEL ─────────────────────────────────────────
  DEFAULT_CHANNEL: '/api/shop/default-channel',

  // ── CUSTOMER ORDER ACTIONS ──────────────────────────────────
  CANCEL_ORDER: '/api/shop/cancel-order',
  REORDER_ORDER: '/api/shop/reorder',

  // ── ADMIN ───────────────────────────────────────────────────
  // W0 — Authentication
  ADMIN_LOGIN: '/api/admin/login',
  ADMIN_LOGOUT: '/api/admin/logout',
  ADMIN_PROFILE: '/api/admin/get',
  ADMIN_PROFILE_UPDATE: '/api/admin/update',
  ADMIN_FORGOT_PASSWORD: '/api/admin/forgot-password',

  // W0 — Dashboard
  ADMIN_DASHBOARD_STATS: '/api/admin/dashboard/stats',

  // W0 — Reporting
  ADMIN_REPORTING_STATS: '/api/admin/reporting/stats',
  ADMIN_REPORTING_SALES: '/api/admin/reporting/sales',
  ADMIN_REPORTING_CUSTOMERS: '/api/admin/reporting/customers',
  ADMIN_REPORTING_PRODUCTS: '/api/admin/reporting/products',
};