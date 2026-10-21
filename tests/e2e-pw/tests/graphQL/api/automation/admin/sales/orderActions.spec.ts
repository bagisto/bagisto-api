import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import { graphQLErrorMessages } from '../../../../graphql/helpers/testSupport';
import {
  ADMIN_REORDER_MUTATION,
  ADMIN_INVOICE_CREATE_MUTATION,
  ADMIN_SHIPMENT_CREATE_MUTATION,
  ADMIN_REFUND_CREATE_MUTATION,
  ADMIN_REFUND_PREVIEW_MUTATION,
} from '../../../../graphql/Queries/admin/sales/orderActions.queries';
import {
  UNKNOWN_ID,
  invoiceOrder,
  placeOrder,
} from '../../../../../restAPI/rest/helpers/coverage/adminSales.helpers';

test.describe.configure({ timeout: 240_000 });

const MESSAGES = {
  ORDER_NOT_FOUND: 'Order not found.',
  REORDER_SUCCESS: 'Reorder successful. A new draft cart has been created.',
  REORDER_DISABLED: 'Reorder by admin is disabled in store settings.',
  CANCEL_SUCCESS: 'Order canceled successfully.',
  NOTHING_TO_CANCEL: 'There is nothing to cancel on this order.',
  COMMENT_REQUIRED: 'Comment is required.',
  INVOICE_ITEMS_REQUIRED: 'At least one item with a positive quantity is required.',
  ALREADY_INVOICED: 'An invoice has already been generated for this order.',
  SHIPMENT_ITEMS_REQUIRED: 'At least one item with a positive quantity is required.',
  NOTHING_TO_REFUND: 'There is nothing left to refund on this order.',
};

const CANCEL_ORDER = `
  mutation adminCancelOrder($orderId: Int!) {
    createAdminCancelOrder(input: { orderId: $orderId }) {
      adminCancelOrder {
        _id
        orderId
        status
        success
        message
      }
    }
  }
`;

const ORDER_COMMENTS = `
  query adminOrderComments($first: Int, $orderId: Int!) {
    adminOrderComments(first: $first, orderId: $orderId) {
      edges {
        node {
          _id
          orderId
          comment
          customerNotified
        }
      }
    }
  }
`;

const CREATE_ORDER_COMMENT = `
  mutation adminOrderCommentCreate($orderId: Int!, $comment: String!, $customerNotified: Boolean) {
    createAdminOrderComment(input: { orderId: $orderId, comment: $comment, customerNotified: $customerNotified }) {
      adminOrderComment {
        _id
        orderId
        comment
        customerNotified
      }
    }
  }
`;

const REFUND_INPUT = { items: {}, shipping: 0, adjustmentRefund: 0, adjustmentFee: 0 };

async function runAdmin(request: APIRequestContext, query: string, variables: Record<string, any>): Promise<any> {
  const response = await sendAdminGraphQLRequest(request, query, variables);

  expect(response.status()).toBe(200);

  return response.json();
}

function expectNoErrors(body: any): void {
  expect(body.errors, graphQLErrorMessages(body).join(' | ')).toBeUndefined();
}

function expectRefused(body: any, message: string, root: string): void {
  expect(graphQLErrorMessages(body)).toEqual([message]);
  expect(body.data?.[root] ?? null).toBeNull();
}

async function orderComments(request: APIRequestContext, orderId: number): Promise<any[]> {
  const body = await runAdmin(request, ORDER_COMMENTS, { first: 50, orderId });
  expectNoErrors(body);

  return body.data.adminOrderComments.edges.map((edge: any) => edge.node);
}

test.describe('admin order actions graphql api', () => {
  test('should build a draft cart when reordering an order', async ({ request }) => {
    const order = await placeOrder(request);

    const body = await runAdmin(request, ADMIN_REORDER_MUTATION, { orderId: String(order.orderId) });

    test.skip(
      graphQLErrorMessages(body).includes(MESSAGES.REORDER_DISABLED),
      'reorder by admin is disabled in sales.order_settings.reorder'
    );

    expectNoErrors(body);

    const reorder = body.data.createAdminReorder.adminReorder;

    expect(reorder.success).toBe(true);
    expect(reorder.message).toBe(MESSAGES.REORDER_SUCCESS);
    expect(reorder.cartId).toBeGreaterThan(0);
    expect(reorder.cartId).not.toBe(order.cartId);
  });

  test('should report an unknown order on reorder', async ({ request }) => {
    expectRefused(await runAdmin(request, ADMIN_REORDER_MUTATION, { orderId: String(UNKNOWN_ID) }), MESSAGES.ORDER_NOT_FOUND, 'createAdminReorder');
  });

  test('should cancel a pending order once', async ({ request }) => {
    const order = await placeOrder(request);

    const body = await runAdmin(request, CANCEL_ORDER, { orderId: order.orderId });
    expectNoErrors(body);

    expect(body.data.createAdminCancelOrder.adminCancelOrder).toEqual({
      _id: order.orderId,
      orderId: order.orderId,
      status: 'canceled',
      success: true,
      message: MESSAGES.CANCEL_SUCCESS,
    });

    expectRefused(await runAdmin(request, CANCEL_ORDER, { orderId: order.orderId }), MESSAGES.NOTHING_TO_CANCEL, 'createAdminCancelOrder');
  });

  test('should report an unknown order on cancel', async ({ request }) => {
    expectRefused(await runAdmin(request, CANCEL_ORDER, { orderId: UNKNOWN_ID }), MESSAGES.ORDER_NOT_FOUND, 'createAdminCancelOrder');
  });

  test('should add a comment to the order and list it', async ({ request }) => {
    const order = await placeOrder(request);
    const comment = `E2E gql comment ${Date.now()}-${Math.floor(Math.random() * 1_000_000)}`;

    const body = await runAdmin(request, CREATE_ORDER_COMMENT, { orderId: order.orderId, comment, customerNotified: false });
    expectNoErrors(body);

    const created = body.data.createAdminOrderComment.adminOrderComment;

    expect(created._id).toBeGreaterThan(0);
    expect(created.orderId).toBe(order.orderId);
    expect(created.comment).toBe(comment);
    expect(created.customerNotified).toBe(false);

    expect((await orderComments(request, order.orderId)).filter((node: any) => node.comment === comment)).toEqual([
      { _id: created._id, orderId: order.orderId, comment, customerNotified: false },
    ]);
  });

  test('should reject an empty order comment', async ({ request }) => {
    const order = await placeOrder(request);

    const body = await runAdmin(request, CREATE_ORDER_COMMENT, { orderId: order.orderId, comment: '   ', customerNotified: false });

    expectRefused(body, MESSAGES.COMMENT_REQUIRED, 'createAdminOrderComment');
    expect(await orderComments(request, order.orderId)).toEqual([]);
  });

  test('should require invoice items with a positive quantity', async ({ request }) => {
    const order = await placeOrder(request);

    expectRefused(
      await runAdmin(request, ADMIN_INVOICE_CREATE_MUTATION, { orderId: order.orderId, items: {} }),
      MESSAGES.INVOICE_ITEMS_REQUIRED,
      'createAdminInvoice'
    );
  });

  test('should refuse a second invoice for a fully invoiced order', async ({ request }) => {
    const order = await placeOrder(request);
    await invoiceOrder(request, order.orderId);

    expectRefused(
      await runAdmin(request, ADMIN_INVOICE_CREATE_MUTATION, { orderId: order.orderId, items: {} }),
      MESSAGES.ALREADY_INVOICED,
      'createAdminInvoice'
    );
  });

  test('should require shipment items with a positive quantity', async ({ request }) => {
    const order = await placeOrder(request);

    expectRefused(
      await runAdmin(request, ADMIN_SHIPMENT_CREATE_MUTATION, { orderId: order.orderId, source: 1, items: {} }),
      MESSAGES.SHIPMENT_ITEMS_REQUIRED,
      'createAdminShipment'
    );
  });

  test('should refuse a refund for an order that was never invoiced', async ({ request }) => {
    const order = await placeOrder(request);

    expectRefused(
      await runAdmin(request, ADMIN_REFUND_CREATE_MUTATION, { orderId: order.orderId, ...REFUND_INPUT }),
      MESSAGES.NOTHING_TO_REFUND,
      'createAdminRefund'
    );
  });

  test('should refuse a refund preview for an order that was never invoiced', async ({ request }) => {
    const order = await placeOrder(request);

    expectRefused(
      await runAdmin(request, ADMIN_REFUND_PREVIEW_MUTATION, { orderId: order.orderId, ...REFUND_INPUT }),
      MESSAGES.NOTHING_TO_REFUND,
      'previewAdminRefund'
    );
  });
});
