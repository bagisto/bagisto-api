import { test, expect, APIRequestContext } from '@playwright/test';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../../graphql/helpers/testSupport';
import { SHOP_ACCOUNT_QUERIES as Q } from '../../../graphql/Queries/coverage/shopAccount.queries';
import { SHOP_ACCOUNT_MESSAGES as MSG } from '../../../../restAPI/rest/endpoints/coverage/shopAccount.endpoints';
import { e2eFixtures, missingFixtureReason, E2eCustomerFixture } from '../../../../fixtures/e2eFixtures';

const STATUS_PENDING = 1;
const STATUS_SOLVED = 6;
const STATUS_CANCELED = 9;

function uniqueSuffix(): string {
  return `${Date.now()}-${Math.floor(Math.random() * 1_000_000)}`;
}

function bearer(token: string): Record<string, string> {
  return { Authorization: `Bearer ${token}` };
}

function returnIri(id: number): string {
  return `/api/shop/returns/${id}`;
}

async function gql(request: APIRequestContext, query: string, variables: Record<string, any> = {}, token?: string): Promise<any> {
  const response = await sendGraphQLRequest(request, query, variables, token ? bearer(token) : {});
  expect(response.status()).toBe(200);

  return response.json();
}

async function loginToken(request: APIRequestContext, customer: E2eCustomerFixture): Promise<string> {
  const body = await gql(request, Q.loginCustomer, { email: customer.email, password: customer.password });
  expect(body.errors).toBeUndefined();

  const login = body.data.createCustomerLogin.customerLogin;
  expect(login.success).toBe(true);

  return login.token;
}

async function registerToken(request: APIRequestContext): Promise<string> {
  const email = `shop-account-${uniqueSuffix()}@example.com`;
  const password = 'Passw0rd!123';

  const body = await gql(request, Q.registerCustomer, {
    input: {
      firstName: 'Shop',
      lastName: 'Account',
      email,
      password,
      confirmPassword: password,
      status: '1',
      isVerified: '1',
      isSuspended: '0',
      subscribedToNewsLetter: false,
    },
  });

  expect(body.errors).toBeUndefined();
  const token = body.data.createCustomer.customer.token;

  if (token) {
    return token;
  }

  const loginBody = await gql(request, Q.loginCustomer, { email, password });

  return loginBody.data.createCustomerLogin.customerLogin.token;
}

function expectError(body: any, field: string, message: string): void {
  expect(body.data?.[field] ?? null).toBeNull();
  expect(graphQLErrorMessages(body)).toContain(message);
}

async function requiredCustomAnswers(request: APIRequestContext, token: string): Promise<Record<string, any>> {
  const body = await gql(request, Q.returnCustomFields, {}, token);
  expect(body.errors).toBeUndefined();

  const answers: Record<string, any> = {};

  for (const field of body.data.returnCustomFields.filter((row: any) => row.isRequired)) {
    const firstOption = field.options?.[0]?.value;

    if (['select', 'radio'].includes(field.type)) {
      answers[String(field._id)] = firstOption;
    } else if (['multiselect', 'checkbox'].includes(field.type)) {
      answers[String(field._id)] = [firstOption];
    } else {
      answers[String(field._id)] = 'e2e answer';
    }
  }

  return answers;
}

async function buildReturnInput(request: APIRequestContext, token: string, orderId: number): Promise<Record<string, any> | null> {
  const items = await gql(request, Q.returnableItems, { orderId }, token);
  expect(items.errors).toBeUndefined();

  const item = items.data.returnableItems.find((row: any) => row.currentQuantity >= 1 && row.forReturnQuantity >= 1);

  const reasons = await gql(request, Q.returnReasons, { resolutionType: 'return' }, token);
  expect(reasons.errors).toBeUndefined();

  if (
    !item
    || reasons.data.returnReasons.length === 0
  ) {
    return null;
  }

  return {
    orderId,
    orderItemId: item.orderItemId,
    rmaQty: 1,
    resolutionType: 'return',
    rmaReasonId: reasons.data.returnReasons[0]._id,
    information: `e2e return ${uniqueSuffix()}`,
    packageCondition: 'packed',
    customAttributes: await requiredCustomAnswers(request, token),
    agreement: true,
  };
}

async function createReturn(request: APIRequestContext, token: string, input: Record<string, any>): Promise<any> {
  const body = await gql(request, Q.createCustomerReturn, { input }, token);
  expect(body.errors).toBeUndefined();

  const created = body.data.createCustomerReturn.customerReturn;
  expect(typeof created._id).toBe('number');
  expect(created.orderId).toBe(input.orderId);
  expect(created.statusId).toBe(STATUS_PENDING);
  expect(created.information).toBe(input.information);
  expect(created.packageCondition).toBe('packed');
  expect(created.item.order_item_id).toBe(input.orderItemId);
  expect(created.item.quantity).toBe(1);
  expect(created.item.resolution).toBe('return');

  return created;
}

async function readStatus(request: APIRequestContext, token: string, id: number): Promise<number> {
  const body = await gql(request, Q.customerReturn, { id: returnIri(id) }, token);
  expect(body.errors).toBeUndefined();

  return body.data.customerReturn.statusId;
}

test.describe('shop account return lookups graphql api', () => {
  test('should list the return reasons of a resolution type', async ({ request }) => {
    const token = await registerToken(request);

    for (const resolutionType of ['return', 'cancel_items']) {
      const body = await gql(request, Q.returnReasons, { resolutionType }, token);

      expect(body.errors).toBeUndefined();
      expect(Array.isArray(body.data.returnReasons)).toBe(true);
      for (const reason of body.data.returnReasons) {
        expect(typeof reason._id).toBe('number');
        expect(typeof reason.title).toBe('string');
      }
    }
  });

  test('should reject an unknown resolution type for return reasons', async ({ request }) => {
    const token = await registerToken(request);

    const body = await gql(request, Q.returnReasons, { resolutionType: 'exchange' }, token);

    expectError(body, 'returnReasons', MSG.RETURN_INVALID_RESOLUTION);
  });

  test('should refuse the return lookups without a customer token', async ({ request }) => {
    expectError(await gql(request, Q.returnReasons, { resolutionType: 'return' }), 'returnReasons', MSG.UNAUTHENTICATED);
    expectError(await gql(request, Q.returnCustomFields), 'returnCustomFields', MSG.UNAUTHENTICATED);
    expectError(await gql(request, Q.returnableItems, { orderId: 1 }), 'returnableItems', MSG.UNAUTHENTICATED);
    expectError(await gql(request, Q.returnableOrders), 'returnableOrders', MSG.UNAUTHENTICATED);
  });

  test('should list the active return form custom fields', async ({ request }) => {
    const token = await registerToken(request);

    const body = await gql(request, Q.returnCustomFields, {}, token);

    expect(body.errors).toBeUndefined();
    expect(Array.isArray(body.data.returnCustomFields)).toBe(true);
    for (const field of body.data.returnCustomFields) {
      expect(typeof field._id).toBe('number');
      expect(typeof field.type).toBe('string');
      expect(typeof field.isRequired).toBe('boolean');
      expect(Array.isArray(field.options)).toBe(true);
    }
  });

  test('should list the returnable items of the customer order with consistent quantities', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const token = await loginToken(request, fx.customer!);
    const body = await gql(request, Q.returnableItems, { orderId: fx.order!.id }, token);

    expect(body.errors).toBeUndefined();
    expect(Array.isArray(body.data.returnableItems)).toBe(true);
    for (const item of body.data.returnableItems) {
      expect(typeof item.orderItemId).toBe('number');
      expect(item.currentQuantity).toBe(item.qtyOrdered - item.rmaQuantity);
    }
  });

  test('should refuse the returnable items of another customer order', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.otherCustomer, missingFixtureReason('otherCustomer'));

    const token = await loginToken(request, fx.otherCustomer!);
    const body = await gql(request, Q.returnableItems, { orderId: fx.order!.id }, token);

    expectError(body, 'returnableItems', MSG.RETURN_INVALID_ORDER);
  });

  test('should list no returnable orders for a customer without orders', async ({ request }) => {
    const token = await registerToken(request);

    const body = await gql(request, Q.returnableOrders, {}, token);

    expect(body.errors).toBeUndefined();
    expect(body.data.returnableOrders).toEqual([]);
  });

  test('should only list returnable orders of the customer that hold quantity to return', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));
    test.skip(!fx.otherCustomer, missingFixtureReason('otherCustomer'));

    const ownerToken = await loginToken(request, fx.customer!);
    const owner = await gql(request, Q.returnableOrders, {}, ownerToken);

    expect(owner.errors).toBeUndefined();
    for (const order of owner.data.returnableOrders) {
      expect(order.returnableQty).toBeGreaterThan(0);
      expect(order.returnableQty).toBe(order.totalQtyOrdered - order.totalReturnedQty);
    }

    const otherToken = await loginToken(request, fx.otherCustomer!);
    const other = await gql(request, Q.returnableOrders, { incrementId: fx.order!.incrementId }, otherToken);

    expect(other.errors).toBeUndefined();
    expect(other.data.returnableOrders.map((order: any) => order._id)).not.toContain(fx.order!.id);
  });
});

test.describe('shop account returns graphql api', () => {
  test('should refuse the return list without a customer token', async ({ request }) => {
    expectError(await gql(request, Q.customerReturns, { first: 10 }), 'customerReturns', MSG.UNAUTHENTICATED);
  });

  test('should list and return the customer return by id', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.rma, missingFixtureReason('rma'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const token = await loginToken(request, fx.customer!);

    const list = await gql(request, Q.customerReturns, { first: 50 }, token);
    expect(list.errors).toBeUndefined();
    expect(list.data.customerReturns.edges.map((edge: any) => edge.node._id)).toContain(fx.rma!.id);

    const detail = await gql(request, Q.customerReturn, { id: returnIri(fx.rma!.id) }, token);
    expect(detail.errors).toBeUndefined();

    const rma = detail.data.customerReturn;
    expect(rma._id).toBe(fx.rma!.id);
    expect(rma.orderId).toBe(fx.rma!.orderId);
    expect(typeof rma.statusId).toBe('number');
    expect(typeof rma.statusTitle).toBe('string');
    expect(typeof rma.canClose).toBe('boolean');
    expect(typeof rma.canReopen).toBe('boolean');
    expect(rma.messagesCount).toBeGreaterThan(0);
  });

  test('should hide the customer return from another customer', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.rma, missingFixtureReason('rma'));
    test.skip(!fx.otherCustomer, missingFixtureReason('otherCustomer'));

    const token = await loginToken(request, fx.otherCustomer!);

    expectError(await gql(request, Q.customerReturn, { id: returnIri(fx.rma!.id) }, token), 'customerReturn', MSG.RETURN_NOT_FOUND);

    const list = await gql(request, Q.customerReturns, { first: 50 }, token);
    expect(list.errors).toBeUndefined();
    expect(list.data.customerReturns.edges.map((edge: any) => edge.node._id)).not.toContain(fx.rma!.id);
  });

  test('should reject a return without the policy agreement and create nothing', async ({ request }) => {
    const token = await registerToken(request);

    const body = await gql(request, Q.createCustomerReturn, {
      input: { orderId: 999999999, orderItemId: 999999999, rmaQty: 1, resolutionType: 'return', rmaReasonId: 1, agreement: false },
    }, token);

    expectError(body, 'createCustomerReturn', MSG.RETURN_AGREEMENT_REQUIRED);

    const list = await gql(request, Q.customerReturns, { first: 10 }, token);
    expect(list.data.customerReturns.edges).toEqual([]);
  });

  test('should refuse a return on another customer order', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.otherCustomer, missingFixtureReason('otherCustomer'));

    const token = await loginToken(request, fx.otherCustomer!);

    const body = await gql(request, Q.createCustomerReturn, {
      input: {
        orderId: fx.order!.id,
        orderItemId: 999999999,
        rmaQty: 1,
        resolutionType: 'return',
        rmaReasonId: 1,
        customAttributes: await requiredCustomAnswers(request, token),
        agreement: true,
      },
    }, token);

    expectError(body, 'createCustomerReturn', MSG.RETURN_INVALID_ORDER);
  });

  test('should refuse a return without a customer token', async ({ request }) => {
    const body = await gql(request, Q.createCustomerReturn, {
      input: { orderId: 1, orderItemId: 1, rmaQty: 1, resolutionType: 'return', rmaReasonId: 1, agreement: true },
    });

    expectError(body, 'createCustomerReturn', MSG.UNAUTHENTICATED);
  });

  test('should cancel a return the customer raised and refuse a second cancel', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const token = await loginToken(request, fx.customer!);
    const input = await buildReturnInput(request, token, fx.order!.id);
    test.skip(!input, 'The fixture order has no quantity left to return.');

    const created = await createReturn(request, token, input!);

    const cancel = await gql(request, Q.cancelCustomerReturn, { id: returnIri(created._id) }, token);
    expect(cancel.errors).toBeUndefined();
    expect(cancel.data.cancelCustomerReturn.customerReturn._id).toBe(created._id);
    expect(cancel.data.cancelCustomerReturn.customerReturn.statusId).toBe(STATUS_CANCELED);

    expectError(await gql(request, Q.cancelCustomerReturn, { id: returnIri(created._id) }, token), 'cancelCustomerReturn', MSG.RETURN_ALREADY_CANCELED);

    expect(await readStatus(request, token, created._id)).toBe(STATUS_CANCELED);
  });

  test('should reopen a canceled return only when the store allows it', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const token = await loginToken(request, fx.customer!);
    const input = await buildReturnInput(request, token, fx.order!.id);
    test.skip(!input, 'The fixture order has no quantity left to return.');

    const created = await createReturn(request, token, input!);

    const cancel = await gql(request, Q.cancelCustomerReturn, { id: returnIri(created._id) }, token);
    expect(cancel.errors).toBeUndefined();
    const canReopen = cancel.data.cancelCustomerReturn.customerReturn.canReopen;

    const reopen = await gql(request, Q.reopenCustomerReturn, { id: returnIri(created._id) }, token);

    if (canReopen) {
      expect(reopen.errors).toBeUndefined();
      expect(reopen.data.reopenCustomerReturn.customerReturn.statusId).toBe(STATUS_PENDING);

      const release = await gql(request, Q.cancelCustomerReturn, { id: returnIri(created._id) }, token);
      expect(release.errors).toBeUndefined();
      expect(release.data.cancelCustomerReturn.customerReturn.statusId).toBe(STATUS_CANCELED);
    } else {
      expectError(reopen, 'reopenCustomerReturn', MSG.RETURN_REOPEN_NOT_ALLOWED);
      expect(await readStatus(request, token, created._id)).toBe(STATUS_CANCELED);
    }
  });

  test('should close a pending return and refuse to close it again', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const token = await loginToken(request, fx.customer!);
    const input = await buildReturnInput(request, token, fx.order!.id);
    test.skip(!input, 'The fixture order has no quantity left to return.');

    const created = await createReturn(request, token, input!);
    expect(created.canClose).toBe(true);

    const close = await gql(request, Q.closeCustomerReturn, { id: returnIri(created._id) }, token);
    expect(close.errors).toBeUndefined();
    expect(close.data.closeCustomerReturn.customerReturn.statusId).toBe(STATUS_SOLVED);
    expect(close.data.closeCustomerReturn.customerReturn.canClose).toBe(false);

    expectError(await gql(request, Q.closeCustomerReturn, { id: returnIri(created._id) }, token), 'closeCustomerReturn', MSG.RETURN_CLOSE_NOT_ALLOWED);
  });

  test('should refuse to cancel, close or reopen another customer return', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.rma, missingFixtureReason('rma'));
    test.skip(!fx.customer, missingFixtureReason('customer'));
    test.skip(!fx.otherCustomer, missingFixtureReason('otherCustomer'));

    const ownerToken = await loginToken(request, fx.customer!);
    const before = await readStatus(request, ownerToken, fx.rma!.id);

    const otherToken = await loginToken(request, fx.otherCustomer!);

    for (const [query, field] of [
      [Q.cancelCustomerReturn, 'cancelCustomerReturn'],
      [Q.closeCustomerReturn, 'closeCustomerReturn'],
      [Q.reopenCustomerReturn, 'reopenCustomerReturn'],
    ]) {
      const body = await gql(request, query, { id: returnIri(fx.rma!.id) }, otherToken);

      expect(body.data?.[field] ?? null).toBeNull();
      expect(graphQLErrorMessages(body).length).toBeGreaterThan(0);
    }

    expect(await readStatus(request, ownerToken, fx.rma!.id)).toBe(before);
  });

  test('should refuse return actions without a customer token', async ({ request }) => {
    for (const [query, field] of [
      [Q.cancelCustomerReturn, 'cancelCustomerReturn'],
      [Q.closeCustomerReturn, 'closeCustomerReturn'],
      [Q.reopenCustomerReturn, 'reopenCustomerReturn'],
    ]) {
      const body = await gql(request, query, { id: returnIri(1) });

      expect(body.data?.[field] ?? null).toBeNull();
      expect(graphQLErrorMessages(body).length).toBeGreaterThan(0);
    }
  });
});

test.describe('shop account return messages graphql api', () => {
  test('should post a message on the customer return and list it first', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.rma, missingFixtureReason('rma'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const token = await loginToken(request, fx.customer!);
    const message = `e2e message ${uniqueSuffix()}`;

    const created = await gql(request, Q.createCustomerReturnMessage, { returnId: fx.rma!.id, message }, token);
    expect(created.errors).toBeUndefined();

    const sent = created.data.createCustomerReturnMessage.customerReturnMessage;
    expect(typeof sent._id).toBe('number');
    expect(sent.rmaId).toBe(fx.rma!.id);
    expect(sent.message).toBe(message);
    expect(sent.isAdmin).toBe(false);

    const list = await gql(request, Q.customerReturnMessages, { returnId: fx.rma!.id }, token);
    expect(list.errors).toBeUndefined();
    expect(list.data.customerReturnMessages[0]._id).toBe(sent._id);
    expect(list.data.customerReturnMessages.map((row: any) => row._id)).toContain(fx.rma!.messageId);
  });

  test('should hide the return conversation from another customer', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.rma, missingFixtureReason('rma'));
    test.skip(!fx.customer, missingFixtureReason('customer'));
    test.skip(!fx.otherCustomer, missingFixtureReason('otherCustomer'));

    const otherToken = await loginToken(request, fx.otherCustomer!);
    const message = `e2e intrusion ${uniqueSuffix()}`;

    expectError(await gql(request, Q.customerReturnMessages, { returnId: fx.rma!.id }, otherToken), 'customerReturnMessages', MSG.RETURN_NOT_FOUND);
    expectError(
      await gql(request, Q.createCustomerReturnMessage, { returnId: fx.rma!.id, message }, otherToken),
      'createCustomerReturnMessage',
      MSG.RETURN_NOT_FOUND
    );

    const ownerToken = await loginToken(request, fx.customer!);
    const ownerList = await gql(request, Q.customerReturnMessages, { returnId: fx.rma!.id }, ownerToken);
    expect(ownerList.data.customerReturnMessages.map((row: any) => row.message)).not.toContain(message);
  });

  test('should reject a message without a return id', async ({ request }) => {
    const token = await registerToken(request);

    expectError(await gql(request, Q.createCustomerReturnMessage, { message: 'e2e' }, token), 'createCustomerReturnMessage', MSG.RETURN_INVALID_INPUT);
  });

  test('should refuse the return conversation without a customer token', async ({ request }) => {
    expectError(await gql(request, Q.customerReturnMessages, { returnId: 1 }), 'customerReturnMessages', MSG.UNAUTHENTICATED);
    expectError(await gql(request, Q.createCustomerReturnMessage, { returnId: 1, message: 'e2e' }), 'createCustomerReturnMessage', MSG.UNAUTHENTICATED);
  });
});
