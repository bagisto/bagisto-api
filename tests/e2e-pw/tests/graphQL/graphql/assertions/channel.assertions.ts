import { expect } from '@playwright/test';

export function assertNoGraphQLErrors(body: any) {
  expect(body.errors, 'GraphQL errors found').toBeUndefined();
  expect(body.data).toBeTruthy();
}

export function assertGraphQLErrors(body: any, expectedErrorMessage?: string) {
  expect(body.errors).toBeTruthy();
  if (expectedErrorMessage) {
    const errorMessages = body.errors.map((e: any) => e.message).join(' ');
    expect(errorMessages).toContain(expectedErrorMessage);
  }
}

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

export function assertChannelWithInternalId(channel: any) {
  assertBasicChannel(channel);
  expect(channel).toHaveProperty('_id');
  expect(typeof channel._id).toBe('number');
}

export function assertChannelWithTimezone(channel: any) {
  assertBasicChannel(channel);
  expect(channel).toHaveProperty('timezone');
  if (channel.timezone !== null) {
    expect(typeof channel.timezone).toBe('string');
  }
}

export function assertCompleteChannel(channel: any) {
  assertChannelWithInternalId(channel);

  expect(channel).toHaveProperty('theme');
  expect(channel).toHaveProperty('hostname');
  expect(channel).toHaveProperty('logo');
  expect(channel).toHaveProperty('favicon');
  expect(channel).toHaveProperty('logoUrl');
  expect(channel).toHaveProperty('faviconUrl');

  expect(channel).toHaveProperty('isMaintenanceOn');
  expect(channel).not.toHaveProperty('allowedIps');

  expect(channel).toHaveProperty('createdAt');
  expect(channel).toHaveProperty('updatedAt');

  if (channel.logoUrl !== null) {
    expect(typeof channel.logoUrl).toBe('string');
  }
  if (channel.faviconUrl !== null) {
    expect(typeof channel.faviconUrl).toBe('string');
  }

  if (channel.createdAt) {
    expect(Date.parse(channel.createdAt)).not.toBeNaN();
  }
  if (channel.updatedAt) {
    expect(Date.parse(channel.updatedAt)).not.toBeNaN();
  }
}

export function assertChannelWithBranding(channel: any) {
  assertBasicChannel(channel);

  expect(channel).toHaveProperty('theme');
  expect(channel).toHaveProperty('logo');
  expect(channel).toHaveProperty('favicon');
  expect(channel).toHaveProperty('logoUrl');
  expect(channel).toHaveProperty('faviconUrl');
}

export function assertChannelWithMaintenanceMode(channel: any) {
  assertBasicChannel(channel);

  expect(channel).toHaveProperty('isMaintenanceOn');
  expect(channel).not.toHaveProperty('allowedIps');

  const isMaintenanceOn = channel.isMaintenanceOn;
  expect(typeof isMaintenanceOn === 'boolean' || typeof isMaintenanceOn === 'string').toBe(true);

  expect(channel).not.toHaveProperty('allowedIps');
}

export function assertTranslation(translation: any) {
  expect(translation).toBeTruthy();
  expect(translation).toHaveProperty('id');
  expect(translation).toHaveProperty('locale');
  expect(translation).toHaveProperty('name');

  expect(typeof translation.id).toBe('string');
  expect(typeof translation.locale).toBe('string');
  expect(typeof translation.name === 'string' || typeof translation.name === 'object').toBe(true);

  if (translation.hasOwnProperty('description')) {
    expect(typeof translation.description === 'string' || typeof translation.description === 'object').toBe(true);
  }
  if (translation.hasOwnProperty('maintenanceModeText')) {
    expect(typeof translation.maintenanceModeText === 'string' || typeof translation.maintenanceModeText === 'object').toBe(true);
  }
  if (translation.hasOwnProperty('channelId')) {
    expect(typeof translation.channelId).toBe('number');
  }
}

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

export function assertPageInfo(pageInfo: any) {
  expect(pageInfo).toBeTruthy();
  expect(pageInfo).toHaveProperty('hasNextPage');
  expect(pageInfo).toHaveProperty('endCursor');
  if (pageInfo.startCursor) {
    expect(typeof pageInfo.startCursor).toBe('string');
  }

  expect(typeof pageInfo.hasNextPage).toBe('boolean');
  expect(typeof pageInfo.endCursor).toBe('string');

  if (pageInfo.hasPreviousPage !== undefined) {
    expect(typeof pageInfo.hasPreviousPage).toBe('boolean');
  }
}

export function assertChannelEdge(edge: any) {
  expect(edge).toBeTruthy();
  expect(edge).toHaveProperty('node');
  if (edge.cursor) {
    expect(typeof edge.cursor).toBe('string');
  }
}

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

    if (edge.node.translations) {
      assertTranslationsConnection(edge.node.translations);
    }

    if (edge.node.translation) {
      assertTranslation(edge.node.translation);
    }
  });

  assertPageInfo(channels.pageInfo);
}

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

export function assertGetChannelByIDBasicResponse(body: any) {
  assertNoGraphQLErrors(body);
  const channel = body.data.channel;
  expect(channel).toBeTruthy();
  assertChannelWithInternalId(channel);
  expect(channel).toHaveProperty('timezone');
}

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

export function assertGetChannelByIDAllTranslationsResponse(body: any) {
  assertNoGraphQLErrors(body);
  const channel = body.data.channel;
  expect(channel).toBeTruthy();
  assertChannelWithInternalId(channel);

  expect(channel).toHaveProperty('translations');
  assertTranslationsConnection(channel.translations);
}

export function assertChannelNotFound(body: any) {
  if (body.errors) {
    expect(body.data.channel).toBeNull();
  } else {
    assertNoGraphQLErrors(body);
    const channel = body.data.channel;
    expect(channel).toBeNull();
  }
}

export function assertInvalidIDFormatError(body: any) {
  assertGraphQLErrors(body);
}

export function assertNonExistentChannelResponse(body: any) {
  if (body.errors) {
    expect(body.data.channel).toBeNull();
  } else {
    assertNoGraphQLErrors(body);
    expect(body.data.channel).toBeNull();
  }
}
