// tests/restAPI/rest/assertions/productMedia.assertions.ts
import { expect } from '@playwright/test';

export function assertProductImageFields(image: any) {
  expect(image).toHaveProperty('id');
  expect(image).toHaveProperty('path');
  expect(typeof image.id).toBe('number');
}

export function assertProductVideoFields(video: any) {
  expect(video).toHaveProperty('id');
  expect(video).toHaveProperty('video_path');
}
