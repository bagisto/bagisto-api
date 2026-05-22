// tests/restAPI/rest/assertions/channel.assertions.ts
import { expect } from '@playwright/test';

export function assertChannelFields(channel: any) {
  expect(channel).toHaveProperty('id');
  expect(channel).toHaveProperty('code');
  expect(typeof channel.id).toBe('number');
  expect(typeof channel.code).toBe('string');
}

export function assertChannelWithDetails(channel: any) {
  assertChannelFields(channel);
  expect(channel).toHaveProperty('timezone');
  expect(channel).toHaveProperty('theme');
  expect(channel).toHaveProperty('hostname');
  expect(channel).toHaveProperty('locales');
  expect(channel).toHaveProperty('currencies');
}

export function assertChannelTranslationFields(translation: any) {
  expect(translation).toHaveProperty('id');
  expect(translation).toHaveProperty('channelId');
  expect(translation).toHaveProperty('locale');
  expect(translation).toHaveProperty('name');
  expect(typeof translation.name).toBe('string');
}
