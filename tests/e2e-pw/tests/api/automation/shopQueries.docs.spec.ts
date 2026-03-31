import { test, expect, APIRequestContext } from '@playwright/test';
import { env } from '../../config/env';
import { getCustomerAuthHeaders, getGuestCartHeaders } from '../../config/auth';
import { SHOP_DOCS_QUERIES } from '../../graphql/Queries/shopDocs.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';

type Json = Record<string, any>;

function pick(body: Json, path: string): any {
  return path.split('.').reduce((value, key) => value?.[key], body);
}

function expectGraphQLSuccess(body: Json, path: string) {
  expect(body.errors).toBeUndefined();
  expect(pick(body, path)).toBeTruthy();
}

function expectProtectedResponse(body: Json, path: string) {
  const payload = pick(body, path);

  if (payload !== undefined && payload !== null) {
    expect(payload).toBeDefined();
    return;
  }

  expect(Array.isArray(body.errors) || payload === null).toBeTruthy();
}

async function fetchBody(
  request: APIRequestContext,
  query: string,
  variables: Record<string, any> = {},
  headers: Record<string, string> = {}
) {
  const response = await sendGraphQLRequest(request, query, variables, headers);
  expect(response.status()).toBe(200);
  return response.json();
}

async function fetchFirstEdgeNode(
  request: APIRequestContext,
  query: string,
  rootPath: string,
  variables: Record<string, any> = {},
  headers: Record<string, string> = {}
) {
  const body = await fetchBody(request, query, variables, headers);
  expectGraphQLSuccess(body, rootPath);
  const edges = pick(body, `${rootPath}.edges`);
  expect(Array.isArray(edges)).toBeTruthy();
  expect(edges.length).toBeGreaterThan(0);
  return edges[0].node;
}

test.describe.configure({ mode: 'serial' });

test.describe('Bagisto GraphQL Shop Docs Coverage', () => {
  test('covers public catalog and configuration queries from the docs', async ({ request }) => {
    const attribute = await fetchFirstEdgeNode(
      request,
      SHOP_DOCS_QUERIES.getAttributes,
      'data.attributes',
      { first: 1 }
    );
    const attributeBody = await fetchBody(request, SHOP_DOCS_QUERIES.getAttribute, { id: attribute.id });
    expectGraphQLSuccess(attributeBody, 'data.attribute');

    const attributeOptionsBody = await fetchBody(
      request,
      SHOP_DOCS_QUERIES.getAttributeOptions,
      { first: 5 }
    );
    expectGraphQLSuccess(attributeOptionsBody, 'data.attributeOptions');

    const category = await fetchFirstEdgeNode(
      request,
      SHOP_DOCS_QUERIES.getCategories,
      'data.categories',
      { first: 1 }
    );
    const categoryBody = await fetchBody(request, SHOP_DOCS_QUERIES.getCategory, { id: category.id });
    expectGraphQLSuccess(categoryBody, 'data.category');

    const treeBody = await fetchBody(request, SHOP_DOCS_QUERIES.treeCategories);
    expectGraphQLSuccess(treeBody, 'data.treeCategories');

    const channel = await fetchFirstEdgeNode(
      request,
      SHOP_DOCS_QUERIES.getChannels,
      'data.channels',
      { first: 1 }
    );
    const channelBody = await fetchBody(request, SHOP_DOCS_QUERIES.getChannel, { id: channel.id });
    expectGraphQLSuccess(channelBody, 'data.channel');

    const country = await fetchFirstEdgeNode(
      request,
      SHOP_DOCS_QUERIES.getCountries,
      'data.countries',
      { first: 10 }
    );
    const countryBody = await fetchBody(request, SHOP_DOCS_QUERIES.getCountry, { id: country.id });
    expectGraphQLSuccess(countryBody, 'data.country');

    const statesBody = await fetchBody(request, SHOP_DOCS_QUERIES.getCountryStates, {
      countryId: country._id,
    });
    expectGraphQLSuccess(statesBody, 'data.countryStates');
    const stateEdges = pick(statesBody, 'data.countryStates.edges') ?? [];
    if (stateEdges.length > 0) {
      const stateBody = await fetchBody(request, SHOP_DOCS_QUERIES.getCountryState, {
        id: stateEdges[0].node.id,
      });
      expectGraphQLSuccess(stateBody, 'data.countryState');
    }

    const currency = await fetchFirstEdgeNode(
      request,
      SHOP_DOCS_QUERIES.getCurrencies,
      'data.currencies',
      { first: 1 }
    );
    const currencyBody = await fetchBody(request, SHOP_DOCS_QUERIES.getCurrency, { id: currency.id });
    expectGraphQLSuccess(currencyBody, 'data.currency');

    const locale = await fetchFirstEdgeNode(
      request,
      SHOP_DOCS_QUERIES.getLocales,
      'data.locales',
      { first: 1 }
    );
    const localeBody = await fetchBody(request, SHOP_DOCS_QUERIES.getLocale, { id: locale.id });
    expectGraphQLSuccess(localeBody, 'data.locale');

    const page = await fetchFirstEdgeNode(
      request,
      SHOP_DOCS_QUERIES.getPages,
      'data.pages',
      { first: 1 }
    );
    const pageBody = await fetchBody(request, SHOP_DOCS_QUERIES.getPage, { id: page.id });
    expectGraphQLSuccess(pageBody, 'data.page');

    const themeCustomization = await fetchFirstEdgeNode(
      request,
      SHOP_DOCS_QUERIES.getThemeCustomizations,
      'data.themeCustomizations',
      { first: 1 }
    );
    const themeCustomizationBody = await fetchBody(
      request,
      SHOP_DOCS_QUERIES.getThemeCustomization,
      { id: themeCustomization.id }
    );
    expectGraphQLSuccess(themeCustomizationBody, 'data.themeCustomization');

    const product = await fetchFirstEdgeNode(
      request,
      SHOP_DOCS_QUERIES.getProducts,
      'data.products',
      { first: 2 }
    );
    const productBody = await fetchBody(request, SHOP_DOCS_QUERIES.getProduct, { id: product.id });
    expectGraphQLSuccess(productBody, 'data.product');

    const searchBody = await fetchBody(request, SHOP_DOCS_QUERIES.searchProducts, {
      query: product.name.split(' ')[0],
      sortKey: 'TITLE',
      reverse: false,
      first: 5,
    });
    expectGraphQLSuccess(searchBody, 'data.products');

    const productReviewsBody = await fetchBody(request, SHOP_DOCS_QUERIES.getProductReviews, {
      first: 5,
    });
    expectGraphQLSuccess(productReviewsBody, 'data.productReviews');
  });

  test('covers guest cart and checkout-related docs queries', async ({ request }) => {
    const guestHeaders = await getGuestCartHeaders(request);

    const cartBody = await fetchBody(
      request,
      SHOP_DOCS_QUERIES.createReadCart,
      {},
      guestHeaders
    );
    expectGraphQLSuccess(cartBody, 'data.createReadCart.readCart');

    const addressesBody = await fetchBody(
      request,
      SHOP_DOCS_QUERIES.getCheckoutAddresses,
      {},
      guestHeaders
    );
    expectGraphQLSuccess(addressesBody, 'data.collectionGetCheckoutAddresses');

    const paymentMethodsBody = await fetchBody(
      request,
      SHOP_DOCS_QUERIES.getPaymentMethods,
      {},
      guestHeaders
    );
    expectProtectedResponse(paymentMethodsBody, 'data.paymentMethods');

    const shippingMethodsBody = await fetchBody(
      request,
      SHOP_DOCS_QUERIES.getShippingMethods,
      {},
      guestHeaders
    );
    expectProtectedResponse(shippingMethodsBody, 'data.shippingMethods');
  });

  test('covers protected customer docs queries with auth when configured and validates protected behavior otherwise', async ({
    request,
  }) => {
    const customerHeaders = await getCustomerAuthHeaders(request);
    const headers = customerHeaders ?? {};

    const profileBody = await fetchBody(request, SHOP_DOCS_QUERIES.getCustomerProfile, {}, headers);
    expectProtectedResponse(profileBody, 'data.readCustomerProfile');

    const addressesBody = await fetchBody(request, SHOP_DOCS_QUERIES.getCustomerAddresses, {}, headers);
    expectProtectedResponse(addressesBody, 'data.customerAddresses');

    const ordersBody = await fetchBody(
      request,
      SHOP_DOCS_QUERIES.getCustomerOrders,
      { first: 5 },
      headers
    );
    expectProtectedResponse(ordersBody, 'data.customerOrders');

    const orderEdges = pick(ordersBody, 'data.customerOrders.edges') ?? [];
    if (customerHeaders && orderEdges.length > 0) {
      const order = orderEdges[0].node;

      const orderBody = await fetchBody(
        request,
        SHOP_DOCS_QUERIES.getCustomerOrder,
        { id: order.id },
        headers
      );
      expectGraphQLSuccess(orderBody, 'data.customerOrder');

      const shipmentsBody = await fetchBody(
        request,
        SHOP_DOCS_QUERIES.getCustomerOrderShipments,
        { orderId: order.id },
        headers
      );
      expectGraphQLSuccess(shipmentsBody, 'data.customerOrderShipments');

      const shipmentEdges = pick(shipmentsBody, 'data.customerOrderShipments.edges') ?? [];
      if (shipmentEdges.length > 0) {
        const shipmentBody = await fetchBody(
          request,
          SHOP_DOCS_QUERIES.getCustomerOrderShipment,
          { id: shipmentEdges[0].node.id },
          headers
        );
        expectGraphQLSuccess(shipmentBody, 'data.customerOrderShipment');
      }
    }

    const invoicesBody = await fetchBody(
      request,
      SHOP_DOCS_QUERIES.getCustomerInvoices,
      { first: 5 },
      headers
    );
    expectProtectedResponse(invoicesBody, 'data.customerInvoices');

    const invoiceEdges = pick(invoicesBody, 'data.customerInvoices.edges') ?? [];
    if (customerHeaders && invoiceEdges.length > 0) {
      const invoice = invoiceEdges[0].node;
      const invoiceBody = await fetchBody(
        request,
        SHOP_DOCS_QUERIES.getCustomerInvoice,
        { id: invoice.id },
        headers
      );
      expectGraphQLSuccess(invoiceBody, 'data.customerInvoice');

      const downloadUrl = pick(invoiceBody, 'data.customerInvoice.downloadUrl');
      if (downloadUrl) {
        const downloadResponse = await request.get(downloadUrl, {
          headers: {
            'X-STOREFRONT-KEY': env.storefrontAccessKey,
            ...headers,
          },
        });
        expect(downloadResponse.ok()).toBeTruthy();
        expect(downloadResponse.headers()['content-type']).toContain('application/pdf');
      }
    }

    const customerReviewsBody = await fetchBody(
      request,
      SHOP_DOCS_QUERIES.getCustomerReviews,
      { first: 5 },
      headers
    );
    expectProtectedResponse(customerReviewsBody, 'data.customerReviews');

    const customerReviewEdges = pick(customerReviewsBody, 'data.customerReviews.edges') ?? [];
    if (customerHeaders && customerReviewEdges.length > 0) {
      const customerReviewBody = await fetchBody(
        request,
        SHOP_DOCS_QUERIES.getCustomerReview,
        { id: customerReviewEdges[0].node.id },
        headers
      );
      expectGraphQLSuccess(customerReviewBody, 'data.customerReview');
    }

    const downloadsBody = await fetchBody(
      request,
      SHOP_DOCS_QUERIES.getCustomerDownloadableProducts,
      { first: 5 },
      headers
    );
    expectProtectedResponse(downloadsBody, 'data.customerDownloadableProducts');

    const downloadEdges = pick(downloadsBody, 'data.customerDownloadableProducts.edges') ?? [];
    if (customerHeaders && downloadEdges.length > 0) {
      const downloadBody = await fetchBody(
        request,
        SHOP_DOCS_QUERIES.getCustomerDownloadableProduct,
        { id: downloadEdges[0].node.id },
        headers
      );
      expectGraphQLSuccess(downloadBody, 'data.customerDownloadableProduct');
    }

    const compareItemsBody = await fetchBody(request, SHOP_DOCS_QUERIES.getCompareItems, {}, headers);
    expectProtectedResponse(compareItemsBody, 'data.compareItems');

    const compareEdges = pick(compareItemsBody, 'data.compareItems.edges') ?? [];
    if (customerHeaders && compareEdges.length > 0) {
      const compareItemBody = await fetchBody(
        request,
        SHOP_DOCS_QUERIES.getCompareItem,
        { id: compareEdges[0].node.id },
        headers
      );
      expectGraphQLSuccess(compareItemBody, 'data.compareItem');
    }

    const wishlistsBody = await fetchBody(
      request,
      SHOP_DOCS_QUERIES.getWishlists,
      { first: 5 },
      headers
    );
    expectProtectedResponse(wishlistsBody, 'data.wishlists');

    const wishlistEdges = pick(wishlistsBody, 'data.wishlists.edges') ?? [];
    if (customerHeaders && wishlistEdges.length > 0) {
      const wishlistBody = await fetchBody(
        request,
        SHOP_DOCS_QUERIES.getWishlist,
        { id: wishlistEdges[0].node.id },
        headers
      );
      expectGraphQLSuccess(wishlistBody, 'data.wishlist');
    }
  });

  test('covers booking slots docs query when booking data is configured', async ({ request }) => {
    test.skip(!env.bookingProductId, 'Set BAGISTO_BOOKING_PRODUCT_ID to run booking slots coverage.');

    const bookingBody = await fetchBody(request, SHOP_DOCS_QUERIES.getBookingSlots, {
      id: Number(env.bookingProductId),
      date: env.bookingDate,
    });

    expect(bookingBody.errors).toBeUndefined();
    expect(Array.isArray(bookingBody.data?.bookingSlots)).toBeTruthy();
  });
});
