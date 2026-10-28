import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../graphql/helpers/adminGraphqlClient';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { env } from '../../../config/env';
import { e2eFixtures, missingFixtureReason } from '../../../../fixtures/e2eFixtures';
import {
  ADMIN_RETURNS_QUERY,
  ADMIN_RETURN_QUERY,
  ADMIN_RETURN_MESSAGES_QUERY,
  ADMIN_RETURNABLE_ITEMS_QUERY,
  CREATE_ADMIN_RETURN,
  CREATE_ADMIN_RETURN_MESSAGE,
  UPDATE_STATUS_ADMIN_RETURN,
  REOPEN_ADMIN_RETURN,
  CREATE_ADMIN_RMA_REASON,
  DELETE_ADMIN_RMA_REASON,
} from '../../../graphql/Queries/coverage/adminRma.queries';

test.describe.configure({ timeout: 90_000 });

const UNKNOWN_ID = 999999999;

const UNKNOWN_IRI = `/api/admin/rma/requests/${UNKNOWN_ID}`;

const NOT_FOUND = 'Return request not found.';

const PENDING = 1;

const ACCEPT = 2;

const DECLINED = 7;

const PENDING_ONLY_TRANSITIONS = [ACCEPT, DECLINED];

const NEVER_OFFERED_AFTER_PENDING = [1, 2, 5, 6, 7, 8];

function returnIri(id: number): string {
  return `/api/admin/rma/requests/${id}`;
}

function uniqueText(label: string): string {
  return `e2e gql ${label} ${Date.now()}-${Math.floor(Math.random() * 1_000_000)}`;
}

async function gql(request: APIRequestContext, query: string, variables: Record<string, any> = {}): Promise<any> {
  const resp = await sendAdminGraphQLRequest(request, query, variables);
  expect(resp.status()).toBe(200);
  return resp.json();
}

function dataOf(body: any, field: string): any {
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
  return body.data[field];
}

function firstError(body: any): string | undefined {
  return body.errors?.[0]?.message;
}

function statusIds(statuses: any[]): number[] {
  return (statuses ?? []).map((status: any) => status.id);
}

async function returnableItem(request: APIRequestContext, orderId: number): Promise<any | undefined> {
  const items = dataOf(await gql(request, ADMIN_RETURNABLE_ITEMS_QUERY, { orderId }), 'adminReturnableItems');
  return items.find((item: any) => item.forReturnQuantity >= 1);
}

async function readReturn(request: APIRequestContext, id: number): Promise<any> {
  return dataOf(await gql(request, ADMIN_RETURN_QUERY, { id: returnIri(id) }), 'adminReturn');
}

test.describe('admin rma returns graphql', () => {
  test('should refuse the return listing without an admin token', async ({ request }) => {
    const resp = await sendGraphQLRequest(request, ADMIN_RETURNS_QUERY, { first: 1 }, {}, env.adminGraphqlEndpoint);
    expect(resp.status()).toBe(401);
    expect((await resp.json())?.message).toBe('Unauthenticated.');
  });

  test('should refuse to open a return without an admin token', async ({ request }) => {
    const resp = await sendGraphQLRequest(request, CREATE_ADMIN_RETURN, { orderId: UNKNOWN_ID }, {}, env.adminGraphqlEndpoint);
    expect(resp.status()).toBe(401);
    expect((await resp.json())?.message).toBe('Unauthenticated.');
  });

  test('should report a return that does not exist', async ({ request }) => {
    const body = await gql(request, ADMIN_RETURN_QUERY, { id: UNKNOWN_IRI });
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.adminReturn ?? null).toBeNull();
  });

  test('should report the messages of a return that does not exist', async ({ request }) => {
    expect(firstError(await gql(request, ADMIN_RETURN_MESSAGES_QUERY, { returnId: UNKNOWN_ID }))).toBe(NOT_FOUND);
  });

  test('should report a status change on a return that does not exist', async ({ request }) => {
    expect(firstError(await gql(request, UPDATE_STATUS_ADMIN_RETURN, { id: UNKNOWN_IRI, rmaStatusId: ACCEPT }))).toBe(NOT_FOUND);
  });

  test('should report a reopen of a return that does not exist', async ({ request }) => {
    expect(firstError(await gql(request, REOPEN_ADMIN_RETURN, { id: UNKNOWN_IRI }))).toBe(NOT_FOUND);
  });

  test('should report a message sent to a return that does not exist', async ({ request }) => {
    const body = await gql(request, CREATE_ADMIN_RETURN_MESSAGE, { returnId: UNKNOWN_ID, message: uniqueText('message') });
    expect(firstError(body)).toBe(NOT_FOUND);
  });

  test('should reject a message that names no return', async ({ request }) => {
    const body = await gql(request, CREATE_ADMIN_RETURN_MESSAGE, { message: uniqueText('message') });
    expect(firstError(body)).toBe('Missing or invalid return details.');
  });

  test('should reject a return that names no order', async ({ request }) => {
    const body = await gql(request, CREATE_ADMIN_RETURN, { orderItemId: 1, rmaQty: 1, resolutionType: 'return', rmaReasonId: 1 });
    expect(firstError(body)).toBe('The order id field is required.');
    expect(body.data?.createAdminReturn ?? null).toBeNull();
  });

  test('should return no returnable items for an order that does not exist', async ({ request }) => {
    expect(dataOf(await gql(request, ADMIN_RETURNABLE_ITEMS_QUERY, { orderId: UNKNOWN_ID }), 'adminReturnableItems')).toEqual([]);
  });

  test('should list the returnable items of the order fixture with their quantity caps', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));

    const items = dataOf(await gql(request, ADMIN_RETURNABLE_ITEMS_QUERY, { orderId: fx.order!.id }), 'adminReturnableItems');
    test.skip(items.length === 0, 'the order fixture has no item inside its return window');

    for (const item of items) {
      expect(typeof item.orderItemId).toBe('number');
      expect(typeof item.sku).toBe('string');
      expect(item.currentQuantity).toBe(item.qtyOrdered - item.rmaQuantity);
      expect(typeof item.rmaReturnPeriod).toBe('number');
    }
    expect(items.map((item: any) => item.productId)).toContain(fx.order!.productId);
  });

  test.describe('the return fixture', () => {
    test.beforeEach(() => {
      test.skip(!e2eFixtures().rma, missingFixtureReason('rma'));
    });

    test('should return the full detail of the return fixture with its image', async ({ request }) => {
      const rma = e2eFixtures().rma!;
      const detail = await readReturn(request, rma.id);

      expect(detail.id).toBe(returnIri(rma.id));
      expect(detail._id).toBe(rma.id);
      expect(detail.orderId).toBe(rma.orderId);
      expect(typeof detail.orderIncrementId).toBe('string');
      expect(typeof detail.statusId).toBe('number');
      expect(typeof detail.canReopen).toBe('boolean');
      expect(detail.messagesCount).toBeGreaterThanOrEqual(1);
      expect(Array.isArray(detail.availableStatuses)).toBe(true);

      const image = detail.images.find((row: any) => row.id === rma.imageId);
      expect(image).toBeTruthy();
      expect(image.url).toMatch(new RegExp(`/api/admin/rma/requests/images/${rma.imageId}$`));
    });

    test('should narrow the return listing to the fixture by id and by order', async ({ request }) => {
      const rma = e2eFixtures().rma!;

      const byId = dataOf(await gql(request, ADMIN_RETURNS_QUERY, { id: rma.id, first: 5 }), 'adminReturns');
      expect(byId.totalCount).toBe(1);
      expect(byId.edges[0].node).toEqual(expect.objectContaining({ _id: rma.id, orderId: rma.orderId }));

      const byOrder = dataOf(await gql(request, ADMIN_RETURNS_QUERY, { order_id: rma.orderId, first: 50 }), 'adminReturns');
      expect(byOrder.edges.map((edge: any) => edge.node._id)).toContain(rma.id);
      for (const edge of byOrder.edges) {
        expect(edge.node.orderId).toBe(rma.orderId);
      }
    });

    test('should list the fixture conversation newest first with the attachment link', async ({ request }) => {
      const rma = e2eFixtures().rma!;
      const messages = dataOf(await gql(request, ADMIN_RETURN_MESSAGES_QUERY, { returnId: rma.id }), 'adminReturnMessages');

      const ids = messages.map((message: any) => message._id);
      expect(ids).toEqual([...ids].sort((a: number, b: number) => b - a));
      for (const message of messages) {
        expect(message.rmaId).toBe(rma.id);
      }

      const withAttachment = messages.find((message: any) => message._id === rma.messageId);
      expect(withAttachment).toEqual(expect.objectContaining({ attachment: rma.attachmentName }));
      expect(withAttachment.attachmentUrl).toMatch(new RegExp(`/api/admin/rma/messages/${rma.messageId}/attachment$`));
    });

    test('should refuse a status that does not exist and keep the fixture status', async ({ request }) => {
      const rma = e2eFixtures().rma!;
      const before = await readReturn(request, rma.id);

      const body = await gql(request, UPDATE_STATUS_ADMIN_RETURN, { id: returnIri(rma.id), rmaStatusId: UNKNOWN_ID });
      expect(firstError(body)).toBe('Invalid return status.');

      expect((await readReturn(request, rma.id)).statusId).toBe(before.statusId);
    });
  });

  test.describe('a return the test opens', () => {
    let reasonIri: string | null;
    let reasonId: number;
    let reasonTitle: string;
    let orderItem: any;

    test.beforeEach(async ({ request }) => {
      reasonIri = null;
      reasonTitle = uniqueText('return reason');
      const fx = e2eFixtures();
      test.skip(!fx.order, missingFixtureReason('order'));

      orderItem = await returnableItem(request, fx.order!.id);
      test.skip(!orderItem, 'the order fixture has no item with a quantity left to return');

      const reason = dataOf(
        await gql(request, CREATE_ADMIN_RMA_REASON, { title: reasonTitle, status: 1, position: 1, resolutionType: ['return'] }),
        'createAdminRmaReason',
      ).adminRmaReason;
      reasonIri = reason.id;
      reasonId = reason._id;
    });

    test.afterEach(async ({ request }) => {
      if (reasonIri) {
        await sendAdminGraphQLRequest(request, DELETE_ADMIN_RMA_REASON, { id: reasonIri });
      }
    });

    async function openReturn(request: APIRequestContext, information: string): Promise<any> {
      return dataOf(
        await gql(request, CREATE_ADMIN_RETURN, {
          orderId: e2eFixtures().order!.id,
          orderItemId: orderItem.orderItemId,
          rmaQty: 1,
          resolutionType: 'return',
          rmaReasonId: reasonId,
          information,
          packageCondition: 'open',
        }),
        'createAdminReturn',
      ).adminReturn;
    }

    test('should open a pending return for the order item with the chosen reason', async ({ request }) => {
      const information = uniqueText('return information');
      const created = await openReturn(request, information);

      expect(created.id).toBe(returnIri(created._id));
      expect(created).toEqual(expect.objectContaining({
        orderId: e2eFixtures().order!.id,
        statusId: PENDING,
        information,
        packageCondition: 'open',
        canReopen: false,
        messagesCount: 1,
      }));
      expect(created.item).toEqual(expect.objectContaining({ quantity: 1, resolution: 'return' }));
      expect(created.item.reason).toBe(reasonTitle);
      for (const id of statusIds(created.availableStatuses)) {
        expect(PENDING_ONLY_TRANSITIONS).toContain(id);
      }

      const listed = dataOf(await gql(request, ADMIN_RETURNS_QUERY, { id: created._id, first: 1 }), 'adminReturns');
      expect(listed.edges[0].node).toEqual(expect.objectContaining({ _id: created._id, statusId: PENDING }));
    });

    test('should refuse a return quantity above what the item can still return', async ({ request }) => {
      const body = await gql(request, CREATE_ADMIN_RETURN, {
        orderId: e2eFixtures().order!.id,
        orderItemId: orderItem.orderItemId,
        rmaQty: orderItem.forReturnQuantity + 1,
        resolutionType: 'return',
        rmaReasonId: reasonId,
      });
      expect(firstError(body)).toBe('The requested quantity exceeds the returnable quantity.');
      expect(body.data?.createAdminReturn ?? null).toBeNull();
    });

    test('should refuse to reopen a pending return then reopen it once declined', async ({ request }) => {
      const created = await openReturn(request, uniqueText('reopen flow'));

      const refused = await gql(request, REOPEN_ADMIN_RETURN, { id: created.id });
      expect(firstError(refused)).toBe('This return request cannot be reopened.');
      expect((await readReturn(request, created._id)).statusId).toBe(PENDING);

      const declined = dataOf(
        await gql(request, UPDATE_STATUS_ADMIN_RETURN, { id: created.id, rmaStatusId: DECLINED }),
        'updateStatusAdminReturn',
      ).adminReturn;
      expect(declined.statusId).toBe(DECLINED);
      expect(declined.canReopen).toBe(true);
      expect(declined.messagesCount).toBe(created.messagesCount + 1);
      for (const id of statusIds(declined.availableStatuses)) {
        expect(NEVER_OFFERED_AFTER_PENDING).not.toContain(id);
      }

      const reopened = dataOf(await gql(request, REOPEN_ADMIN_RETURN, { id: created.id }), 'reopenAdminReturn').adminReturn;
      expect(reopened.statusId).toBe(PENDING);
      expect(reopened.canReopen).toBe(false);
      expect(reopened.messagesCount).toBe(declined.messagesCount + 1);

      expect((await readReturn(request, created._id)).statusId).toBe(PENDING);
    });

    test('should post an admin message to the return conversation', async ({ request }) => {
      const created = await openReturn(request, uniqueText('message flow'));
      const text = uniqueText('admin message');

      const message = dataOf(
        await gql(request, CREATE_ADMIN_RETURN_MESSAGE, { returnId: created._id, message: text }),
        'createAdminReturnMessage',
      ).adminReturnMessage;
      expect(message).toEqual(expect.objectContaining({
        rmaId: created._id,
        message: text,
        isAdmin: true,
        attachment: null,
        attachmentUrl: null,
      }));

      const messages = dataOf(await gql(request, ADMIN_RETURN_MESSAGES_QUERY, { returnId: created._id }), 'adminReturnMessages');
      expect(messages[0]).toEqual(expect.objectContaining({ _id: message._id, message: text }));
      expect(messages).toHaveLength(2);
    });
  });
});
