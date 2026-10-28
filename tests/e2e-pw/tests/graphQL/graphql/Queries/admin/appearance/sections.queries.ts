// Admin Appearance — Sections GraphQL operations. Sections belong to a theme and a
// channel; edits are staged as drafts and only reach the storefront on publish.

export const ADMIN_APPEARANCE_SECTIONS_LIST = `
  query adminAppearanceSections($code: String!, $channel: Int, $locale: String) {
    adminAppearanceSections(code: $code, channel: $channel, locale: $locale) {
      id
      _id
      name
      type
      themeCode
      channelId
      sortOrder
      status
      hasDraft
      isPinned
    }
  }
`;

export const ADMIN_APPEARANCE_SECTION_DETAIL = `
  query adminAppearanceSection($id: ID!) {
    adminAppearanceSection(id: $id) {
      id
      _id
      name
      type
      themeCode
      channelId
      sortOrder
      status
      hasDraft
      translations {
        edges {
          node {
            locale
            options
            draftOptions
          }
        }
      }
    }
  }
`;

export const ADMIN_APPEARANCE_SECTION_CREATE = `
  mutation createAdminAppearanceSection($code: String!, $name: String!, $type: String!, $channel: Int) {
    createAdminAppearanceSection(input: { code: $code, name: $name, type: $type, channel: $channel }) {
      adminAppearanceSection {
        id
        _id
        name
        type
        status
      }
    }
  }
`;

export const ADMIN_APPEARANCE_SECTION_UPDATE = `
  mutation updateAdminAppearanceSection($id: ID!, $name: String) {
    updateAdminAppearanceSection(input: { id: $id, name: $name }) {
      adminAppearanceSection {
        id
        _id
        name
      }
    }
  }
`;

export const ADMIN_APPEARANCE_SECTION_DELETE = `
  mutation deleteAdminAppearanceSection($id: ID!) {
    deleteAdminAppearanceSection(input: { id: $id }) {
      adminAppearanceSection {
        _id
        message
      }
    }
  }
`;

export const ADMIN_APPEARANCE_SECTION_DRAFT = `
  mutation createAdminAppearanceSectionDraft($sectionId: Int!, $options: Iterable!, $locale: String) {
    createAdminAppearanceSectionDraft(input: { sectionId: $sectionId, options: $options, locale: $locale }) {
      adminAppearanceSectionDraft {
        sectionId
        locale
        hasDraft
        message
      }
    }
  }
`;

export const ADMIN_APPEARANCE_SECTION_STATUS = `
  mutation createAdminAppearanceSectionStatus($sectionId: Int!, $status: Boolean!) {
    createAdminAppearanceSectionStatus(input: { sectionId: $sectionId, status: $status }) {
      adminAppearanceSectionStatus {
        sectionId
        draftStatus
        hasDraft
        message
      }
    }
  }
`;

export const ADMIN_APPEARANCE_SECTION_REORDER = `
  mutation createAdminAppearanceSectionReorder($sectionIds: Iterable!) {
    createAdminAppearanceSectionReorder(input: { sectionIds: $sectionIds }) {
      adminAppearanceSectionReorder {
        sectionIds
        message
      }
    }
  }
`;

export const ADMIN_APPEARANCE_SECTION_DUPLICATE = `
  mutation createAdminAppearanceSectionDuplicate($sectionId: Int!) {
    createAdminAppearanceSectionDuplicate(input: { sectionId: $sectionId }) {
      adminAppearanceSectionDuplicate {
        sectionId
        sourceId
        name
        type
        message
      }
    }
  }
`;

export const ADMIN_APPEARANCE_SECTION_PUBLISH = `
  mutation createAdminAppearanceSectionPublish($code: String!, $channel: Int) {
    createAdminAppearanceSectionPublish(input: { code: $code, channel: $channel }) {
      adminAppearanceSectionPublish {
        themeCode
        channelId
        sectionIds
        message
      }
    }
  }
`;

export const ADMIN_APPEARANCE_SECTION_DISCARD = `
  mutation createAdminAppearanceSectionDiscard($code: String!, $channel: Int) {
    createAdminAppearanceSectionDiscard(input: { code: $code, channel: $channel }) {
      adminAppearanceSectionDiscard {
        themeCode
        channelId
        sectionIds
        message
      }
    }
  }
`;

export const ADMIN_APPEARANCE_SECTION_FIELDS = `
  query adminAppearanceSectionFields($sectionId: Int!, $locale: String) {
    adminAppearanceSectionFields(sectionId: $sectionId, locale: $locale) {
      sectionId
      type
      locale
      schema
      options
    }
  }
`;

export const ADMIN_APPEARANCE_SECTION_PREVIEW = `
  query adminAppearanceSectionPreview($code: String!, $channel: Int, $locale: String) {
    adminAppearanceSectionPreview(code: $code, channel: $channel, locale: $locale) {
      themeCode
      channelId
      locale
      sections
    }
  }
`;
