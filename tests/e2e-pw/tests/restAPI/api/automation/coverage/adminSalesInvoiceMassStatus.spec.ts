import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../rest/helpers/adminClient';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import {
  ADMIN_SALES_COVERAGE as API,
  ADMIN_SALES_MESSAGES as MSG,
  UNKNOWN_ID,
  expectProblem,
  expectUnauthenticated,
  invoiceOrder,
  invoiceState,
  placeOrder,
  safeJson,
} from '../../../rest/helpers/coverage/adminSales.helpers';

test.describe.configure({ timeout: 240_000 });

test.describe('admin invoice mass status update rest api', () => {
  test('should move the selected invoices to the requested state', async ({ request }) => {
    const first = await invoiceOrder(request, (await placeOrder(request)).orderId);
    const second = await invoiceOrder(request, (await placeOrder(request)).orderId);

    const response = await sendAdminRequest(request, API.INVOICES_MASS_UPDATE_STATUS, {
      method: 'POST',
      data: { indices: [first.id, second.id], value: 'overdue' },
    });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body.message).toBe(MSG.INVOICE_MASS_STATUS_SUCCESS);
    expect([...body.updated].sort()).toEqual([first.id, second.id].sort());
    expect(await invoiceState(request, first.id)).toBe('overdue');
    expect(await invoiceState(request, second.id)).toBe('overdue');
  });

  test('should report only the invoices that exist', async ({ request }) => {
    const invoice = await invoiceOrder(request, (await placeOrder(request)).orderId);

    const response = await sendAdminRequest(request, API.INVOICES_MASS_UPDATE_STATUS, {
      method: 'POST',
      data: { indices: [invoice.id, UNKNOWN_ID], value: 'overdue' },
    });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body.updated).toEqual([invoice.id]);
    expect(await invoiceState(request, invoice.id)).toBe('overdue');
  });

  test('should reject a status outside pending paid and overdue', async ({ request }) => {
    const invoice = await invoiceOrder(request, (await placeOrder(request)).orderId);

    const response = await sendAdminRequest(request, API.INVOICES_MASS_UPDATE_STATUS, {
      method: 'POST',
      data: { indices: [invoice.id], value: 'refunded' },
    });

    await expectProblem(response, 422, MSG.INVOICE_MASS_STATUS_VALUE_INVALID);
    expect(await invoiceState(request, invoice.id)).toBe(invoice.state);
  });

  test('should reject an empty selection', async ({ request }) => {
    const response = await sendAdminRequest(request, API.INVOICES_MASS_UPDATE_STATUS, {
      method: 'POST',
      data: { indices: [], value: 'paid' },
    });

    await expectProblem(response, 422, MSG.INVOICE_MASS_STATUS_INDICES_REQUIRED);
  });

  test('should refuse the mass status update without an admin token', async ({ request }) => {
    const response = await sendRestRequest(request, API.INVOICES_MASS_UPDATE_STATUS, {
      method: 'POST',
      data: { indices: [UNKNOWN_ID], value: 'paid' },
    });

    await expectUnauthenticated(response);
  });
});
