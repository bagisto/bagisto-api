import { expect } from '@playwright/test';

/* ===================================================
 * ATOMIC ASSERTIONS (Reusable)
 * =================================================== */

/**
 * Assert no GraphQL errors in response
 */
export function assertNoGraphQLErrors(body: any) {
  expect(body.errors, 'GraphQL errors found').toBeUndefined();
  expect(body.data).toBeTruthy();
}

/**
 * Assert GraphQL errors exist (for negative tests)
 */
export function assertGraphQLErrors(body: any, expectedErrorMessage?: string) {
  expect(body.errors).toBeTruthy();
  if (expectedErrorMessage) {
    const errorMessages = body.errors.map((e: any) => e.message).join(' ');
    expect(errorMessages).toContain(expectedErrorMessage);
  }
}

/**
 * Assert basic channel structure
 */
export function assertBasicChannel(channel: any) {
  expect(channel).toBeTruthy();
  expect(channel).toHaveProperty('id');
  expect(channel).toHaveProperty('code');
  expect(channel).toHaveProperty('hostname');

  expect(typeof channel.id).toBe('string');
  expect(channel.id.length).toBeGreaterThan(0);

  expect(typeof channel.code).toBe('string');
  expect(channel.code.trim().length).toBeGreaterThan(0);

  expect(typeof channel.hostname).toBe('string');
}

/**
 * Assert channel with internal ID
 */
export function assertChannelWithInternalId(channel: any) {
  assertBasicChannel(channel);
  expect(channel).toHaveProperty('_id');
  expect(typeof channel._id).toBe('number');
}

/**
 * Assert channel has timezone
 */
export function assertChannelWithTimezone(channel: any) {
  assertBasicChannel(channel);
  expect(channel).toHaveProperty('timezone');
  if (channel.timezone !== null) {
    expect(typeof channel.timezone).toBe('string');
  }
}

/**
 * Assert complete channel details
 */
export function assertCompleteChannel(channel: any) {
  assertChannelWithInternalId(channel);

  // Branding fields
  expect(channel).toHaveProperty('theme');
  expect(channel).toHaveProperty('hostname');
  expect(channel).toHaveProperty('logo');
  expect(channel).toHaveProperty('favicon');
  expect(channel).toHaveProperty('logoUrl');
  expect(channel).toHaveProperty('faviconUrl');

  // Maintenance mode
  expect(channel).toHaveProperty('isMaintenanceOn');
  expect(channel).toHaveProperty('allowedIps');

  // Timestamps
  expect(channel).toHaveProperty('createdAt');
  expect(channel).toHaveProperty('updatedAt');

  // Logo and favicon URLs should be valid if present
  if (channel.logoUrl !== null) {
    expect(typeof channel.logoUrl).toBe('string');
  }
  if (channel.faviconUrl !== null) {
    expect(typeof channel.faviconUrl).toBe('string');
  }

  // Timestamps should be valid date strings
  if (channel.createdAt) {
    expect(Date.parse(channel.createdAt)).not.toBeNaN();
  }
  if (channel.updatedAt) {
    expect(Date.parse(channel.updatedAt)).not.toBeNaN();
  }
}

/**
 * Assert channel with branding assets
 */
export function assertChannelWithBranding(channel: any) {
  assertBasicChannel(channel);

  expect(channel).toHaveProperty('theme');
  expect(channel).toHaveProperty('logo');
  expect(channel).toHaveProperty('favicon');
  expect(channel).toHaveProperty('logoUrl');
  expect(channel).toHaveProperty('faviconUrl');
}

/**
 * Assert channel with maintenance mode
 */
export function assertChannelWithMaintenanceMode(channel: any) {
  assertBasicChannel(channel);

  expect(channel).toHaveProperty('isMaintenanceOn');
  expect(channel).toHaveProperty('allowedIps');

  // isMaintenanceOn can be boolean or string ("true"/"false")
  const isMaintenanceOn = channel.isMaintenanceOn;
  expect(typeof isMaintenanceOn === 'boolean' || typeof isMaintenanceOn === 'string').toBe(true);

  // allowedIps can be null or array
  if (channel.allowedIps !== null) {
    expect(Array.isArray(channel.allowedIps)).toBe(true);
  }
}

/* ===================================================
 * TRANSLATION ASSERTIONS
 * =================================================== */

/**
 * Assert single translation object
 */
export function assertTranslation(translation: any) {
  expect(translation).toBeTruthy();
  expect(translation).toHaveProperty('id');
  expect(translation).toHaveProperty('locale');
  expect(translation).toHaveProperty('name');

  expect(typeof translation.id).toBe('string');
  expect(typeof translation.locale).toBe('string');
  // name can be string or object (with locale-specific values)
  expect(typeof translation.name === 'string' || typeof translation.name === 'object').toBe(true);

  // Optional fields - can be string or object
  if (translation.hasOwnProperty('description')) {
    expect(typeof translation.description === 'string' || typeof translation.description === 'object').toBe(true);
  }
  if (translation.hasOwnProperty('maintenanceModeText')) {
    expect(typeof translation.maintenanceModeText === 'string' || typeof translation.maintenanceModeText === 'object').toBe(true);
  }
  if (translation.hasOwnProperty('channelId')) {
    expect(typeof translation.channelId).toBe('string');
  }
}

/**
 * Assert translations connection
 */
export function assertTranslationsConnection(translations: any) {
  expect(translations).toBeTruthy();
  expect(translations).toHaveProperty('edges');
  expect(translations).toHaveProperty('totalCount');
  expect(Array.isArray(translations.edges)).toBe(true);

  translations.edges.forEach((edge: any) => {
    expect(edge).toHaveProperty('node');
    assertTranslation(edge.node);
  });
}

/* ===================================================
 * PAGINATION ASSERTIONS
 * =================================================== */

/**
 * Assert pagination info
 */
export function assertPageInfo(pageInfo: any) {
  expect(pageInfo).toBeTruthy();
  expect(pageInfo).toHaveProperty('hasNextPage');
  // hasPreviousPage may not be present in all responses
  expect(pageInfo).toHaveProperty('endCursor');
  // startCursor may not be present
  if (pageInfo.startCursor) {
    expect(typeof pageInfo.startCursor).toBe('string');
  }

  expect(typeof pageInfo.hasNextPage).toBe('boolean');
  expect(typeof pageInfo.endCursor).toBe('string');

  // hasPreviousPage may not be returned by the API
  if (pageInfo.hasPreviousPage !== undefined) {
    expect(typeof pageInfo.hasPreviousPage).toBe('boolean');
  }
}

/**
 * Assert channels edge structure
 */
export function assertChannelEdge(edge: any) {
  expect(edge).toBeTruthy();
  expect(edge).toHaveProperty('node');
  // cursor may not be present in all responses
  if (edge.cursor) {
    expect(typeof edge.cursor).toBe('string');
  }
}

/* ===================================================
 * RESPONSE ASSERTIONS
 * =================================================== */

/**
 * Assert getChannels basic response
 */
export function assertGetChannelsBasicResponse(body: any) {
  assertNoGraphQLErrors(body);
  const channels = body.data.channels;
  expect(channels).toBeTruthy();
  expect(channels).toHaveProperty('edges');
  expect(channels).toHaveProperty('pageInfo');
  expect(Array.isArray(channels.edges)).toBe(true);

  channels.edges.forEach((edge: any) => {
    assertChannelEdge(edge);
    assertBasicChannel(edge.node);
  });

  assertPageInfo(channels.pageInfo);
}

/**
 * Assert getChannels complete response
 */
export function assertGetChannelsCompleteResponse(body: any) {
  assertNoGraphQLErrors(body);
  const channels = body.data.channels;
  expect(channels).toBeTruthy();
  expect(channels).toHaveProperty('totalCount');
  expect(channels).toHaveProperty('edges');
  expect(channels).toHaveProperty('pageInfo');

  expect(typeof channels.totalCount).toBe('number');
  expect(channels.totalCount).toBeGreaterThanOrEqual(0);

  channels.edges.forEach((edge: any) => {
    assertChannelEdge(edge);
    assertCompleteChannel(edge.node);

    // Assert translations if present
    if (edge.node.translations) {
      assertTranslationsConnection(edge.node.translations);
    }

    // Assert translation if present
    if (edge.node.translation) {
      assertTranslation(edge.node.translation);
    }
  });

  assertPageInfo(channels.pageInfo);
}

/**
 * Assert getChannels with pagination response
 */
export function assertGetChannelsWithPaginationResponse(body: any, expectedCount?: number) {
  assertNoGraphQLErrors(body);
  const channels = body.data.channels;
  expect(channels).toBeTruthy();
  expect(channels).toHaveProperty('edges');
  expect(channels).toHaveProperty('pageInfo');
  expect(channels).toHaveProperty('totalCount');

  if (expectedCount !== undefined) {
    expect(channels.edges.length).toBeLessThanOrEqual(expectedCount);
  }

  channels.edges.forEach((edge: any) => {
    assertChannelEdge(edge);
    assertChannelWithInternalId(edge.node);
  });

  assertPageInfo(channels.pageInfo);
}

/**
 * Assert getChannelByID basic response
 */
export function assertGetChannelByIDBasicResponse(body: any) {
  assertNoGraphQLErrors(body);
  const channel = body.data.channel;
  expect(channel).toBeTruthy();
  assertChannelWithInternalId(channel);
  expect(channel).toHaveProperty('timezone');
}

/**
 * Assert getChannelByID complete response
 */
export function assertGetChannelByIDCompleteResponse(body: any) {
  assertNoGraphQLErrors(body);
  const channel = body.data.channel;
  expect(channel).toBeTruthy();
  assertCompleteChannel(channel);

  if (channel.translation) {
    assertTranslation(channel.translation);
  }

  if (channel.translations) {
    assertTranslationsConnection(channel.translations);
  }
}

/**
 * Assert getChannelByID with branding response
 */
export function assertGetChannelByIDBrandingResponse(body: any) {
  assertNoGraphQLErrors(body);
  const channel = body.data.channel;
  expect(channel).toBeTruthy();
  assertChannelWithBranding(channel);

  if (channel.translation) {
    expect(channel.translation).toHaveProperty('name');
    expect(channel.translation).toHaveProperty('description');
  }
}

/**
 * Assert getChannelByID with maintenance mode response
 */
export function assertGetChannelByIDMaintenanceModeResponse(body: any) {
  assertNoGraphQLErrors(body);
  const channel = body.data.channel;
  expect(channel).toBeTruthy();
  assertChannelWithMaintenanceMode(channel);

  if (channel.translation) {
    expect(channel.translation).toHaveProperty('locale');
    expect(channel.translation).toHaveProperty('name');
    expect(channel.translation).toHaveProperty('maintenanceModeText');
  }

  if (channel.translations) {
    expect(channel.translations).toHaveProperty('totalCount');
  }
}

/**
 * Assert getChannelByID with all translations response
 */
export function assertGetChannelByIDAllTranslationsResponse(body: any) {
  assertNoGraphQLErrors(body);
  const channel = body.data.channel;
  expect(channel).toBeTruthy();
  assertChannelWithInternalId(channel);

  expect(channel).toHaveProperty('translations');
  assertTranslationsConnection(channel.translations);
}

/**
 * Assert channel not found (null response)
 */
export function assertChannelNotFound(body: any) {
  // Handle GraphQL errors - the API may return errors for invalid IDs
  if (body.errors) {
    // If there are errors, the channel should be null
    expect(body.data.channel).toBeNull();
  } else {
    assertNoGraphQLErrors(body);
    const channel = body.data.channel;
    expect(channel).toBeNull();
  }
}

/**
 * Assert invalid ID format error
 */
export function assertInvalidIDFormatError(body: any) {
  assertGraphQLErrors(body);
}

/**
 * Assert channel data is null for non-existent ID
 */
export function assertNonExistentChannelResponse(body: any) {
  // Handle GraphQL errors - the API may return errors for invalid IDs
  // In that case, we check if the channel is null or if there are errors
  if (body.errors) {
    // If there are errors, the channel should be null
    expect(body.data.channel).toBeNull();
  } else {
    assertNoGraphQLErrors(body);
    expect(body.data.channel).toBeNull();
  }
}
