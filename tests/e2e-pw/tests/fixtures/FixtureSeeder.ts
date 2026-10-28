import crypto from 'crypto';
import {
  E2eBookingFixture,
  E2eCustomerFixture,
  E2eDownloadableFixture,
  E2eFixtures,
  E2eGuestOrderFixture,
  E2eOrderFixture,
  E2eReturnFixture,
} from './e2eFixtures';
import { SeedClient, SeedRequestError } from './SeedClient';
import { pngImage } from '../restAPI/rest/helpers/mediaFixtures';

interface Shopper {
  token: string;
  email: string;
}

interface SeededCustomer extends E2eCustomerFixture, Shopper {}

interface PlacedOrder {
  id: number;
  incrementId: string;
  productId: number;
  items: any[];
}

const OFFLINE_PAYMENT_METHODS = ['cashondelivery', 'moneytransfer'];

const PREFERRED_SHIPPING_METHOD = 'flatrate_flatrate';

const BOOKING_TYPE_PREFERENCE = ['default', 'appointment', 'table', 'event', 'rental'];

const BOOKING_SEARCH_DAYS = 21;

const RMA_SETTING_SLUG = 'sales.rma.setting';

const EU_WITHDRAWAL_SLUG = 'sales.eu_withdrawal.general';

const RETURNABLE_ORDER_QTY = 10;

export class FixtureSeeder {
  private readonly stamp = `${Date.now()}${crypto.randomInt(1000, 9999)}`;

  private readonly fixtures: E2eFixtures = {};

  private rmaReadyOrder?: PlacedOrder;

  constructor(
    private readonly client: SeedClient,
    private readonly timezone: string,
  ) {}

  async seed(): Promise<E2eFixtures> {
    await this.step('eu withdrawal configuration', () => this.ensureEuWithdrawalEnabled());

    const customer = await this.step('customer', () => this.registerCustomer('primary'));
    const otherCustomer = await this.step('otherCustomer', () => this.registerCustomer('other'));

    if (customer) {
      this.fixtures.customer = FixtureSeeder.publicCustomer(customer);
    }

    if (otherCustomer) {
      this.fixtures.otherCustomer = FixtureSeeder.publicCustomer(otherCustomer);
    }

    if (customer) {
      this.fixtures.order = await this.step('order', () => this.seedShippedOrder(customer));
      this.fixtures.downloadable = await this.step('downloadable', () => this.seedDownloadablePurchase(customer));
      this.fixtures.rma = await this.step('rma', () => this.seedReturn(customer));
    } else {
      FixtureSeeder.report('order, downloadable, rma', 'skipped because the customer fixture could not be seeded');
    }

    this.fixtures.guestOrder = await this.step('guestOrder', () => this.seedGuestOrder());
    this.fixtures.booking = await this.step('booking', () => this.findBookableDate());

    return Object.fromEntries(
      Object.entries(this.fixtures).filter(([, value]) => value !== undefined),
    ) as E2eFixtures;
  }

  private async step<T>(name: string, run: () => Promise<T>): Promise<T | undefined> {
    try {
      const result = await run();

      FixtureSeeder.report(name, 'seeded');

      return result;
    } catch (error) {
      FixtureSeeder.report(name, `not seeded: ${FixtureSeeder.describe(error)}`);

      return undefined;
    }
  }

  private async registerCustomer(label: string): Promise<SeededCustomer> {
    const email = `e2e.${label}.${this.stamp}@example.com`;
    const password = `E2e-${crypto.randomBytes(6).toString('hex')}-Aa1!`;

    await this.client.shop('/customers', {
      method: 'POST',
      data: {
        firstName: 'E2e',
        lastName: `Customer ${label}`,
        email,
        password,
        confirmPassword: password,
      },
    });

    const login = await this.client.shop('/customer/login', {
      method: 'POST',
      data: { email, password },
    });

    FixtureSeeder.require(login?.accessToken && login?.id, `login returned no access token for ${email}`);

    return { id: Number(login.id), email, password, token: login.accessToken };
  }

  private async seedShippedOrder(customer: SeededCustomer): Promise<E2eOrderFixture> {
    await this.step('rma configuration', () => this.ensureRmaAllowsProductType('simple'));

    const productId = await this.addFirstSaleableProduct(customer, 'simple', async id => {
      await this.assertStockCovers(id, RETURNABLE_ORDER_QTY);

      return { productId: id, quantity: RETURNABLE_ORDER_QTY };
    });

    await this.step('rma product setting', () => this.ensureProductAllowsRma(productId));

    const order = await this.checkout(customer, productId, true);

    this.rmaReadyOrder = order;

    const invoiceId = await this.invoiceOrder(order);

    const shipment = await this.shipOrder(order);

    return {
      id: order.id,
      incrementId: order.incrementId,
      productId,
      invoiceId,
      shipmentId: Number(shipment.id),
      shipmentItemIds: (shipment.items ?? []).map((item: any) => Number(item.id)),
    };
  }

  private async seedGuestOrder(): Promise<E2eGuestOrderFixture> {
    const cart = await this.client.shop('/cart-tokens', { method: 'POST', data: {} });

    const token = cart?.sessionToken ?? cart?.cartToken;

    FixtureSeeder.require(token, `the guest cart response carried no token: ${SeedClient.excerpt(JSON.stringify(cart))}`);

    const guest: Shopper = { token, email: `e2e.guest.${this.stamp}@example.com` };

    const productId = await this.addFirstSaleableProduct(guest, 'simple', id => ({ productId: id, quantity: 1 }));

    const order = await this.checkout(guest, productId, true);

    return { id: order.id, incrementId: order.incrementId, email: guest.email };
  }

  private async seedDownloadablePurchase(customer: SeededCustomer): Promise<E2eDownloadableFixture> {
    const productId = await this.addFirstSaleableProduct(customer, 'downloadable', async id => {
      const links = await this.client.shop(`/products/${id}/downloadable-links`);

      FixtureSeeder.require(Array.isArray(links) && links.length > 0, `product ${id} has no downloadable links`);

      return { productId: id, quantity: 1, links: [Number(links[0].id)] };
    });

    const order = await this.checkout(customer, productId, false);

    await this.invoiceOrder(order);

    const purchases = await this.client.shop('/customer-downloadable-products', {
      token: customer.token,
      params: { per_page: '50' },
    });

    const purchase = FixtureSeeder.list(purchases).find(entry => FixtureSeeder.orderIdOf(entry) === order.id);

    FixtureSeeder.require(purchase, `no purchased link listed for order ${order.id}`);

    return { orderId: order.id, productId, purchasedLinkId: Number(purchase.id) };
  }

  private async seedReturn(customer: SeededCustomer): Promise<E2eReturnFixture> {
    const order = this.rmaReadyOrder;

    FixtureSeeder.require(order, 'the order step did not place an order to return');

    const orderItem = order.items.find(item => Number(item.productId) === order.productId) ?? order.items[0];

    const reasons = await this.client.shop('/return-reasons', {
      token: customer.token,
      params: { resolution_type: 'return' },
    });

    const reason = FixtureSeeder.list(reasons)[0];

    FixtureSeeder.require(reason, 'no return reason is available for the return resolution');

    const customAttributes = await this.requiredReturnCustomAttributes(customer);

    const created = await this.client.shop('/returns', {
      method: 'POST',
      token: customer.token,
      multipart: {
        order_id: order.id,
        order_item_id: Number(orderItem.id),
        rma_qty: 1,
        resolution_type: 'return',
        rma_reason_id: Number(reason.id),
        information: `E2e return ${this.stamp}`,
        package_condition: 'packed',
        agreement: true,
        ...(Object.keys(customAttributes).length > 0 ? { custom_attributes: JSON.stringify(customAttributes) } : {}),
        'images[]': { name: `e2e-return-${this.stamp}.png`, mimeType: 'image/png', buffer: pngImage() },
      },
    });

    const returnId = Number(created?.id);

    FixtureSeeder.require(returnId, `the return create response carried no id: ${SeedClient.excerpt(JSON.stringify(created))}`);

    const imageId = Number(FixtureSeeder.list(created.images)[0]?.id);

    FixtureSeeder.require(imageId, `return ${returnId} carries no stored image`);

    const attachmentName = `e2e-attachment-${this.stamp}.png`;

    const message = await this.sendReturnMessageWithAttachment(returnId, attachmentName);

    FixtureSeeder.require(message?.id, `the admin message response carried no id: ${SeedClient.excerpt(JSON.stringify(message))}`);

    return { id: returnId, orderId: order.id, imageId, messageId: Number(message.id), attachmentName };
  }

  private async findBookableDate(): Promise<E2eBookingFixture> {
    const products = FixtureSeeder.list(await this.client.shop('/products', {
      params: { type: 'booking', per_page: '50' },
    }));

    FixtureSeeder.require(products.length > 0, 'no booking product is listed by the shop products API');

    const bookings: { productId: number; bookingProductId: number; type: string }[] = [];

    for (const product of products) {
      const bookingProducts = FixtureSeeder.list(await this.client.shop(`/products/${product.id}/booking-products`));

      if (bookingProducts[0]?.id) {
        bookings.push({
          productId: Number(product.id),
          bookingProductId: Number(bookingProducts[0].id),
          type: String(bookingProducts[0].type),
        });
      }
    }

    bookings.sort((left, right) => FixtureSeeder.bookingRank(left.type) - FixtureSeeder.bookingRank(right.type));

    for (const booking of bookings) {
      for (let offset = 1; offset <= BOOKING_SEARCH_DAYS; offset++) {
        const date = this.dateInAppTimezone(offset);

        const slots = await this.client.shop('/booking-slots', {
          params: { id: String(booking.bookingProductId), date },
        });

        if (FixtureSeeder.list(slots).length > 0) {
          return { ...booking, date };
        }
      }
    }

    throw new Error(`no booking product has slots in the next ${BOOKING_SEARCH_DAYS} days`);
  }

  private async addFirstSaleableProduct(
    customer: Shopper,
    type: string,
    cartPayload: (productId: number) => Record<string, unknown> | Promise<Record<string, unknown>>,
  ): Promise<number> {
    const products = FixtureSeeder.list(await this.client.shop('/products', {
      params: { type, per_page: '50' },
    }));

    const refusals: string[] = [];

    for (const product of products) {
      try {
        const cart = await this.client.shop('/add-product-in-cart', {
          method: 'POST',
          token: customer.token,
          data: await cartPayload(Number(product.id)),
        });

        const added = FixtureSeeder.list(cart?.items).some(item => Number(item.productId) === Number(product.id));

        FixtureSeeder.require(added, `the cart does not hold product ${product.id}`);

        return Number(product.id);
      } catch (error) {
        refusals.push(`${product.id}: ${FixtureSeeder.describe(error)}`);
      }
    }

    throw new Error(`no saleable ${type} product among ${products.length} listed (${refusals.slice(0, 3).join(' | ')})`);
  }

  private async checkout(customer: Shopper, productId: number, needsShipping: boolean): Promise<PlacedOrder> {
    await this.client.shop('/checkout-addresses', {
      method: 'POST',
      token: customer.token,
      data: {
        billingFirstName: 'E2e',
        billingLastName: 'Customer',
        billingEmail: customer.email,
        billingAddress: '1 Fixture Street',
        billingCity: 'Los Angeles',
        billingCountry: 'US',
        billingState: 'CA',
        billingPostcode: '90210',
        billingPhoneNumber: '5550000000',
        useForShipping: true,
      },
    });

    if (needsShipping) {
      await this.chooseShippingMethod(customer);
    }

    await this.chooseOfflinePaymentMethod(customer);

    const placed = await this.client.shop('/checkout-orders', {
      method: 'POST',
      token: customer.token,
      data: {},
    });

    FixtureSeeder.require(placed?.orderId, `place order answered without an order id: ${SeedClient.excerpt(JSON.stringify(placed))}`);

    const order = await this.client.admin(`/orders/${placed.orderId}`);

    return {
      id: Number(order.id),
      incrementId: String(order.incrementId),
      productId,
      items: FixtureSeeder.list(order.items),
    };
  }

  private async chooseShippingMethod(customer: Shopper): Promise<void> {
    const rates = FixtureSeeder.list(await this.client.shop('/checkout-shipping-methods', { token: customer.token }));

    const method = rates.find(rate => rate.method === PREFERRED_SHIPPING_METHOD)?.method ?? rates[0]?.method;

    FixtureSeeder.require(method, 'the cart offers no shipping method');

    await this.client.shop('/checkout-shipping-methods', {
      method: 'POST',
      token: customer.token,
      data: { shippingMethod: method },
    });
  }

  private async chooseOfflinePaymentMethod(customer: Shopper): Promise<void> {
    const methods = FixtureSeeder.list(await this.client.shop('/payment-methods', { token: customer.token }));

    const offered = methods
      .filter(method => method.isAllowed !== false)
      .map(method => String(method.method));

    const method = OFFLINE_PAYMENT_METHODS.find(code => offered.includes(code));

    FixtureSeeder.require(method, `no offline payment method is offered (offered: ${offered.join(', ')})`);

    await this.client.shop('/checkout-payment-methods', {
      method: 'POST',
      token: customer.token,
      data: { paymentMethod: method },
    });
  }

  private async invoiceOrder(order: PlacedOrder): Promise<number> {
    const invoice = await this.client.admin(`/orders/${order.id}/invoices`, {
      method: 'POST',
      data: {
        items: order.items.map(item => ({ orderItemId: Number(item.id), quantity: Number(item.qtyOrdered) })),
      },
    });

    FixtureSeeder.require(invoice?.id, `the invoice response carried no id: ${SeedClient.excerpt(JSON.stringify(invoice))}`);

    return Number(invoice.id);
  }

  private async shipOrder(order: PlacedOrder): Promise<any> {
    const inventories = FixtureSeeder.list(await this.client.admin(`/catalog/products/${order.productId}/inventories`));

    const source = inventories
      .slice()
      .sort((left, right) => Number(right.qty) - Number(left.qty))[0];

    FixtureSeeder.require(source?.sourceId, `product ${order.productId} has no inventory source`);

    const sourceId = Number(source.sourceId);

    const shipment = await this.client.admin(`/orders/${order.id}/shipments`, {
      method: 'POST',
      data: {
        source: sourceId,
        carrierTitle: 'E2e Carrier',
        trackNumber: `E2E-${this.stamp}`,
        items: order.items.map(item => ({
          orderItemId: Number(item.id),
          inventorySourceId: sourceId,
          quantity: Number(item.qtyOrdered),
        })),
      },
    });

    FixtureSeeder.require(shipment?.id, `the shipment response carried no id: ${SeedClient.excerpt(JSON.stringify(shipment))}`);

    return shipment;
  }

  private async ensureEuWithdrawalEnabled(): Promise<void> {
    const settings = FixtureSeeder.list(await this.client.admin('/configuration', {
      params: { slug: EU_WITHDRAWAL_SLUG },
    }))[0];

    const key = `${EU_WITHDRAWAL_SLUG}.enabled`;
    const value = settings?.values?.[key];

    if (
      value === true
      || Number(value) === 1
    ) {
      return;
    }

    await this.client.admin('/configuration', {
      method: 'POST',
      data: { slug: EU_WITHDRAWAL_SLUG, channel: settings?.channel, values: { [key]: 1 } },
    });
  }

  private async assertStockCovers(productId: number, quantity: number): Promise<void> {
    const inventories = FixtureSeeder.list(await this.client.admin(`/catalog/products/${productId}/inventories`));

    const stock = inventories.reduce((total, inventory) => total + Number(inventory.qty ?? 0), 0);

    FixtureSeeder.require(stock >= quantity, `product ${productId} has ${stock} in stock, ${quantity} needed`);
  }

  private async ensureRmaAllowsProductType(productType: string): Promise<void> {
    const settings = FixtureSeeder.list(await this.client.admin('/configuration', {
      params: { slug: RMA_SETTING_SLUG },
    }))[0];

    const values = settings?.values ?? {};

    const typesKey = `${RMA_SETTING_SLUG}.select_allowed_product_type`;
    const extensionsKey = `${RMA_SETTING_SLUG}.allowed_file_extension`;

    const types = FixtureSeeder.csv(values[typesKey]);
    const extensions = FixtureSeeder.csv(values[extensionsKey]);

    const changes: Record<string, string> = {};

    if (!types.includes(productType)) {
      changes[typesKey] = [...types, productType].join(',');
    }

    if (!extensions.includes('image/png')) {
      changes[extensionsKey] = [...extensions, 'image/png'].join(',');
    }

    if (Object.keys(changes).length === 0) {
      return;
    }

    await this.client.admin('/configuration', {
      method: 'POST',
      data: { slug: RMA_SETTING_SLUG, channel: settings?.channel, values: changes },
    });
  }

  private async ensureProductAllowsRma(productId: number): Promise<void> {
    const product = await this.client.admin(`/catalog/products/${productId}`);

    const allowRma = FixtureSeeder.list(product?.attributes).find(attribute => attribute.code === 'allow_rma');

    FixtureSeeder.require(allowRma, `product ${productId} has no allow_rma attribute in its family`);

    if (allowRma.value === true || Number(allowRma.value) === 1) {
      return;
    }

    await this.client.admin(`/catalog/products/${productId}`, {
      method: 'PUT',
      data: { allow_rma: 1 },
    });
  }

  private async sendReturnMessageWithAttachment(returnId: number, attachmentName: string): Promise<any> {
    return this.client.admin('/rma/messages', {
      method: 'POST',
      multipart: {
        return_id: returnId,
        message: `E2e message ${this.stamp}`,
        file: { name: attachmentName, mimeType: 'image/png', buffer: pngImage() },
      },
    });
  }

  private async requiredReturnCustomAttributes(customer: SeededCustomer): Promise<Record<string, unknown>> {
    const fields = FixtureSeeder.list(await this.client.shop('/return-custom-fields', { token: customer.token }));

    const answers: Record<string, unknown> = {};

    for (const field of fields.filter(entry => entry.isRequired === true || entry.is_required === true)) {
      answers[String(field.id)] = FixtureSeeder.customFieldAnswer(field);
    }

    return answers;
  }

  private dateInAppTimezone(offsetDays: number): string {
    const today = new Intl.DateTimeFormat('en-CA', {
      timeZone: this.timezone,
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
    }).format(new Date());

    const [year, month, day] = today.split('-').map(Number);

    return new Date(Date.UTC(year, month - 1, day + offsetDays)).toISOString().slice(0, 10);
  }

  private static customFieldAnswer(field: any): unknown {
    const option = FixtureSeeder.list(field.options)[0];
    const optionValue = option?.value ?? option?.id;

    if (['multiselect', 'checkbox'].includes(field.type)) {
      return optionValue !== undefined ? [optionValue] : [];
    }

    if (['select', 'radio'].includes(field.type)) {
      return optionValue;
    }

    return 'E2e';
  }

  private static publicCustomer(customer: SeededCustomer): E2eCustomerFixture {
    return { id: customer.id, email: customer.email, password: customer.password };
  }

  private static bookingRank(type: string): number {
    const rank = BOOKING_TYPE_PREFERENCE.indexOf(type);

    return rank === -1 ? BOOKING_TYPE_PREFERENCE.length : rank;
  }

  private static orderIdOf(purchase: any): number {
    const order = purchase?.order;

    if (order && typeof order === 'object') {
      return Number(order.id ?? order._id);
    }

    if (typeof order === 'string') {
      return Number(order.split('/').pop());
    }

    return Number(purchase?.orderId);
  }

  private static list(value: any): any[] {
    if (Array.isArray(value)) {
      return value;
    }

    if (Array.isArray(value?.data)) {
      return value.data;
    }

    if (Array.isArray(value?.['hydra:member'])) {
      return value['hydra:member'];
    }

    return [];
  }

  private static csv(value: unknown): string[] {
    return String(value ?? '')
      .split(',')
      .map(entry => entry.trim())
      .filter(entry => entry !== '');
  }

  private static require(condition: unknown, message: string): asserts condition {
    if (!condition) {
      throw new Error(message);
    }
  }

  private static describe(error: unknown): string {
    if (error instanceof SeedRequestError) {
      return error.message;
    }

    return error instanceof Error ? error.message : String(error);
  }

  private static report(step: string, outcome: string): void {
    console.log(`[e2e fixtures] ${step}: ${outcome}`);
  }
}
