// rest/endpoints/admin.appearance.endpoints.ts
//
// Admin Appearance endpoint registry. A theme is addressed by its `code`; a
// section by its numeric id, except when it is created or listed under a theme.

export const ADMIN_APPEARANCE = {
  // ── Themes ──────────────────────────────────────────────────
  THEMES: '/api/admin/appearance/themes',
  THEME: (code: string) => `/api/admin/appearance/themes/${code}`,
  THEME_IMPACT: (code: string) => `/api/admin/appearance/themes/${code}/impact`,
  THEME_ACTIVATE: (code: string) => `/api/admin/appearance/themes/${code}/activate`,

  // ── Sections ────────────────────────────────────────────────
  THEME_SECTIONS: (code: string) => `/api/admin/appearance/themes/${code}/sections`,
  SECTION: (id: number | string) => `/api/admin/appearance/sections/${id}`,
  SECTION_DRAFT: (id: number | string) => `/api/admin/appearance/sections/${id}/draft`,
  SECTION_STATUS: (id: number | string) => `/api/admin/appearance/sections/${id}/status`,
  SECTION_DUPLICATE: (id: number | string) => `/api/admin/appearance/sections/${id}/duplicate`,
  SECTION_FIELDS: (id: number | string) => `/api/admin/appearance/sections/${id}/fields`,
  SECTION_MEDIA: (id: number | string) => `/api/admin/appearance/sections/${id}/media`,
  SECTIONS_REORDER: '/api/admin/appearance/sections/reorder',
  SECTIONS_PUBLISH: (code: string) => `/api/admin/appearance/themes/${code}/sections/publish`,
  SECTIONS_DISCARD: (code: string) => `/api/admin/appearance/themes/${code}/sections/discard`,
  SECTIONS_PREVIEW: (code: string) => `/api/admin/appearance/themes/${code}/sections/preview`,
} as const;
