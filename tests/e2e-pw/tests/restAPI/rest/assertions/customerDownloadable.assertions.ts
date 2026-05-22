// tests/restAPI/rest/assertions/customerDownloadable.assertions.ts
import { expect } from '@playwright/test';

export function assertCustomerDownloadFields(download: any) {
  expect(download).toHaveProperty('id');
  expect(download).toHaveProperty('product_name');
  expect(download).toHaveProperty('downloads_remaining');
}
