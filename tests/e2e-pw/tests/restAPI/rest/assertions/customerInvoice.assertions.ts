// tests/restAPI/rest/assertions/customerInvoice.assertions.ts
import { expect } from '@playwright/test';

export function assertCustomerInvoiceFields(invoice: any) {
  expect(invoice).toHaveProperty('id');
  expect(invoice).toHaveProperty('orderId');
  expect(invoice).toHaveProperty('invoiceNumber');
  expect(invoice).toHaveProperty('grandTotal');
  expect(typeof invoice.id).toBe('number');
}
