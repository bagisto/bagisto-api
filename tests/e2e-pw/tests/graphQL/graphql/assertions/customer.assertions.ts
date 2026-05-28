// tests/graphql/assertions/customer.assertions.ts

import { expect } from '@playwright/test';

/**
 * Validates a customer profile response
 */
export const assertCustomerProfileResponse = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('readCustomerProfile');
  
  const profile = body.data.readCustomerProfile;
  
  if (profile) {
    // Validate required fields
    expect(profile.id).toBeDefined();
    expect(profile.id).toContain('/api/shop/customers/');
    
    // Validate field types
    if (profile.firstName !== null) {
      expect(typeof profile.firstName).toBe('string');
    }
    if (profile.lastName !== null) {
      expect(typeof profile.lastName).toBe('string');
    }
    if (profile.email !== null) {
      expect(typeof profile.email).toBe('string');
      expect(profile.email).toMatch(/^[^\s@]+@[^\s@]+\.[^\s@]+$/);
    }
    
    // Validate optional fields
    if (profile.dateOfBirth !== null && profile.dateOfBirth !== undefined) {
      expect(typeof profile.dateOfBirth).toBe('string');
    }
    if (profile.gender !== null && profile.gender !== undefined) {
      expect(typeof profile.gender).toBe('string');
    }
    if (profile.phone !== null && profile.phone !== undefined) {
      expect(typeof profile.phone).toBe('string');
    }
    if (profile.status !== null && profile.status !== undefined) {
      expect(typeof profile.status).toBe('number');
    }
    if (profile.subscribedToNewsLetter !== null && profile.subscribedToNewsLetter !== undefined) {
      expect(typeof profile.subscribedToNewsLetter).toBe('boolean');
    }
    if (profile.isVerified !== null && profile.isVerified !== undefined) {
      expect(typeof profile.isVerified).toBe('boolean');
    }
    
    console.log('\n========== CUSTOMER PROFILE ==========');
    console.log(`ID: ${profile.id}`);
    console.log(`Name: ${profile.firstName} ${profile.lastName}`);
    console.log(`Email: ${profile.email}`);
    console.log(`Status: ${profile.status}`);
    console.log(`Verified: ${profile.isVerified}`);
    console.log('======================================\n');
  }
};

/**
 * Validates customer profile not found response
 */
export const assertCustomerProfileNotFound = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data.readCustomerProfile).toBeNull();
  
  console.log('\n===== CUSTOMER PROFILE NOT FOUND =====\n');
};

/**
 * Validates a customer orders list response
 */
export const assertCustomerOrdersResponse = (body: any, expectedFirst?: number) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('customerOrders');
  expect(body.data.customerOrders).not.toBeNull();
  
  const orders = body.data.customerOrders;
  
  // Validate edges array
  expect(Array.isArray(orders.edges)).toBeTruthy();
  
  if (expectedFirst !== undefined) {
    expect(orders.edges.length).toBeLessThanOrEqual(expectedFirst);
  }
  
  // Validate totalCount
  expect(typeof orders.totalCount).toBe('number');
  expect(orders.totalCount).toBeGreaterThanOrEqual(0);
  
  // Validate pageInfo
  expect(orders.pageInfo).toBeDefined();
  expect(typeof orders.pageInfo.hasNextPage).toBe('boolean');
  expect(typeof orders.pageInfo.hasPreviousPage).toBe('boolean');
  
  // Validate each order node
  orders.edges.forEach((edge: any) => {
    expect(edge.cursor).toBeDefined();
    expect(edge.node).toBeDefined();
    assertOrderNode(edge.node);
  });
  
  console.log('\n========== CUSTOMER ORDERS ==========');
  console.log(`Total Count: ${orders.totalCount}`);
  console.log(`Page Size: ${orders.edges.length}`);
  console.log(`Has Next Page: ${orders.pageInfo.hasNextPage}`);
  console.log('=====================================\n');
};

/**
 * Validates a single order node
 */
export const assertOrderNode = (order: any) => {
  expect(order).not.toBeNull();
  
  // Validate required fields
  expect(order._id).toBeDefined();
  expect(order.incrementId).toBeDefined();
  expect(order.status).toBeDefined();
  
  // Validate field types
  if (order.channelName !== null && order.channelName !== undefined) {
    expect(typeof order.channelName).toBe('string');
  }
  if (order.customerEmail !== null && order.customerEmail !== undefined) {
    expect(typeof order.customerEmail).toBe('string');
  }
  if (order.customerFirstName !== null && order.customerFirstName !== undefined) {
    expect(typeof order.customerFirstName).toBe('string');
  }
  if (order.customerLastName !== null && order.customerLastName !== undefined) {
    expect(typeof order.customerLastName).toBe('string');
  }
  if (order.grandTotal !== null && order.grandTotal !== undefined) {
    expect(typeof order.grandTotal).toBe('string');
  }
  if (order.createdAt !== null && order.createdAt !== undefined) {
    expect(typeof order.createdAt).toBe('string');
    expect(order.createdAt).toMatch(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/);
  }
};

/**
 * Validates customer orders filtered by status
 */
export const assertCustomerOrdersByStatus = (body: any, expectedStatus: string) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('customerOrders');
  expect(body.data.customerOrders).not.toBeNull();
  
  const orders = body.data.customerOrders;
  
  // Validate each order has the expected status
  orders.edges.forEach((edge: any) => {
    expect(edge.node.status).toBe(expectedStatus);
  });
  
  console.log('\n========== ORDERS FILTERED BY STATUS ==========');
  console.log(`Status Filter: ${expectedStatus}`);
  console.log(`Orders Found: ${orders.edges.length}`);
  console.log('===============================================\n');
};

/**
 * Validates customer orders pagination
 */
export const assertCustomerOrdersPagination = (body: any, previousEndCursor?: string) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('customerOrders');
  expect(body.data.customerOrders).not.toBeNull();
  
  const orders = body.data.customerOrders;
  
  // Validate pageInfo
  expect(orders.pageInfo).toBeDefined();
  expect(orders.pageInfo.endCursor).toBeDefined();
  expect(typeof orders.pageInfo.hasNextPage).toBe('boolean');
  
  // If we have a previous cursor, verify we got different results
  if (previousEndCursor) {
    expect(orders.pageInfo.endCursor).not.toBe(previousEndCursor);
  }
  
  console.log('\n========== PAGINATION INFO ==========');
  console.log(`End Cursor: ${orders.pageInfo.endCursor}`);
  console.log(`Has Next Page: ${orders.pageInfo.hasNextPage}`);
  console.log(`Items on Page: ${orders.edges.length}`);
  console.log('=====================================\n');
};

/**
 * Validates a single customer order response
 */
export const assertCustomerOrderByIdResponse = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('customerOrder');
  expect(body.data.customerOrder).not.toBeNull();
  
  const order = body.data.customerOrder;
  
  // Validate basic order fields
  expect(order.incrementId).toBeDefined();
  expect(order.status).toBeDefined();
  expect(order.channelName).toBeDefined();
  expect(order.customerEmail).toBeDefined();
  
  // Validate currency fields
  if (order.baseCurrencyCode !== null && order.baseCurrencyCode !== undefined) {
    expect(typeof order.baseCurrencyCode).toBe('string');
  }
  if (order.orderCurrencyCode !== null && order.orderCurrencyCode !== undefined) {
    expect(typeof order.orderCurrencyCode).toBe('string');
  }
  
  // Validate monetary fields
  const monetaryFields = ['grandTotal', 'baseGrandTotal', 'subTotal', 'baseSubTotal', 
                         'taxAmount', 'shippingAmount', 'discountAmount'];
  monetaryFields.forEach(field => {
    if (order[field] !== null && order[field] !== undefined) {
      expect(typeof order[field]).toBe('string');
    }
  });
  
  // Validate items
  if (order.items) {
    expect(Array.isArray(order.items.edges)).toBeTruthy();
    order.items.edges.forEach((edge: any) => {
      assertOrderItemNode(edge.node);
    });
  }
  
  // Validate addresses
  if (order.addresses) {
    expect(Array.isArray(order.addresses.edges)).toBeTruthy();
    order.addresses.edges.forEach((edge: any) => {
      assertOrderAddressNode(edge.node);
    });
  }
  
  // Validate timestamps
  if (order.createdAt !== null && order.createdAt !== undefined) {
    expect(typeof order.createdAt).toBe('string');
    expect(order.createdAt).toMatch(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/);
  }
  if (order.updatedAt !== null && order.updatedAt !== undefined) {
    expect(typeof order.updatedAt).toBe('string');
    expect(order.updatedAt).toMatch(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/);
  }
  
  console.log('\n========== CUSTOMER ORDER DETAILS ==========');
  console.log(`Increment ID: ${order.incrementId}`);
  console.log(`Status: ${order.status}`);
  console.log(`Channel: ${order.channelName}`);
  console.log(`Customer: ${order.customerFirstName} ${order.customerLastName}`);
  console.log(`Email: ${order.customerEmail}`);
  console.log(`Grand Total: ${order.grandTotal}`);
  console.log(`Items Count: ${order.items?.edges?.length || 0}`);
  console.log(`Addresses Count: ${order.addresses?.edges?.length || 0}`);
  console.log('============================================\n');
};

/**
 * Validates an order item node
 */
export const assertOrderItemNode = (item: any) => {
  expect(item).not.toBeNull();
  expect(item.id).toBeDefined();
  expect(item.sku).toBeDefined();
  expect(item.name).toBeDefined();
  
  // Validate quantity fields
  const qtyFields = ['qtyOrdered', 'qtyShipped', 'qtyInvoiced', 'qtyCanceled', 'qtyRefunded'];
  qtyFields.forEach(field => {
    if (item[field] !== null && item[field] !== undefined) {
      expect(typeof item[field]).toBe('number');
    }
  });
  
  console.log(`  Item: ${item.name} (${item.sku}) - Qty: ${item.qtyOrdered}`);
};

/**
 * Validates an order address node
 */
export const assertOrderAddressNode = (address: any) => {
  expect(address).not.toBeNull();
  expect(address.id).toBeDefined();
  expect(address.addressType).toBeDefined();
  
  // Validate address fields
  if (address.firstName !== null && address.firstName !== undefined) {
    expect(typeof address.firstName).toBe('string');
  }
  if (address.lastName !== null && address.lastName !== undefined) {
    expect(typeof address.lastName).toBe('string');
  }
  if (address.address !== null && address.address !== undefined) {
    expect(typeof address.address).toBe('string');
  }
  if (address.city !== null && address.city !== undefined) {
    expect(typeof address.city).toBe('string');
  }
  if (address.country !== null && address.country !== undefined) {
    expect(typeof address.country).toBe('string');
  }
  if (address.postcode !== null && address.postcode !== undefined) {
    expect(typeof address.postcode).toBe('string');
  }
  if (address.phone !== null && address.phone !== undefined) {
    expect(typeof address.phone).toBe('string');
  }
  if (address.email !== null && address.email !== undefined) {
    expect(typeof address.email).toBe('string');
  }
  
  console.log(`  Address (${address.addressType}): ${address.firstName} ${address.lastName}, ${address.city}, ${address.country}`);
};

/**
 * Validates customer order with shipments
 */
export const assertCustomerOrderWithShipments = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('customerOrder');
  expect(body.data.customerOrder).not.toBeNull();
  
  const order = body.data.customerOrder;
  
  // Validate basic order fields
  expect(order._id).toBeDefined();
  expect(order.incrementId).toBeDefined();
  expect(order.status).toBeDefined();
  
  // Validate shipments
  if (order.shipments) {
    expect(Array.isArray(order.shipments.edges)).toBeTruthy();
    
    // Validate pageInfo
    expect(order.shipments.pageInfo).toBeDefined();
    expect(typeof order.shipments.pageInfo.hasNextPage).toBe('boolean');
    expect(typeof order.shipments.pageInfo.hasPreviousPage).toBe('boolean');
    
    // Validate totalCount
    expect(typeof order.shipments.totalCount).toBe('number');
    
    // Validate each shipment
    order.shipments.edges.forEach((edge: any) => {
      assertShipmentNode(edge.node);
    });
  }
  
  console.log('\n========== ORDER WITH SHIPMENTS ==========');
  console.log(`Order ID: ${order._id}`);
  console.log(`Increment ID: ${order.incrementId}`);
  console.log(`Status: ${order.status}`);
  console.log(`Shipments Count: ${order.shipments?.totalCount || 0}`);
  console.log('==========================================\n');
};

/**
 * Validates a shipment node
 */
export const assertShipmentNode = (shipment: any) => {
  expect(shipment).not.toBeNull();
  expect(shipment._id).toBeDefined();
  expect(shipment.status).toBeDefined();
  
  // Validate shipment fields
  if (shipment.totalQty !== null && shipment.totalQty !== undefined) {
    expect(typeof shipment.totalQty).toBe('number');
  }
  if (shipment.totalWeight !== null && shipment.totalWeight !== undefined) {
    expect(typeof shipment.totalWeight).toBe('number');
  }
  if (shipment.carrierCode !== null && shipment.carrierCode !== undefined) {
    expect(typeof shipment.carrierCode).toBe('string');
  }
  if (shipment.carrierTitle !== null && shipment.carrierTitle !== undefined) {
    expect(typeof shipment.carrierTitle).toBe('string');
  }
  if (shipment.trackNumber !== null && shipment.trackNumber !== undefined) {
    expect(typeof shipment.trackNumber).toBe('string');
  }
  if (shipment.createdAt !== null && shipment.createdAt !== undefined) {
    expect(typeof shipment.createdAt).toBe('string');
    expect(shipment.createdAt).toMatch(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/);
  }
  
  // Validate shipment items
  if (shipment.items) {
    expect(Array.isArray(shipment.items.edges)).toBeTruthy();
    shipment.items.edges.forEach((edge: any) => {
      assertShipmentItemNode(edge.node);
    });
  }
  
  console.log(`  Shipment: ${shipment._id} - Status: ${shipment.status} - Qty: ${shipment.totalQty}`);
};

/**
 * Validates a shipment item node
 */
export const assertShipmentItemNode = (item: any) => {
  expect(item).not.toBeNull();
  expect(item._id).toBeDefined();
  expect(item.sku).toBeDefined();
  expect(item.name).toBeDefined();
  
  if (item.qty !== null && item.qty !== undefined) {
    expect(typeof item.qty).toBe('number');
  }
  if (item.weight !== null && item.weight !== undefined) {
    expect(typeof item.weight).toBe('number');
  }
  
  console.log(`    Shipment Item: ${item.name} (${item.sku}) - Qty: ${item.qty}`);
};

/**
 * Validates GraphQL error response
 */
export const assertCustomerGraphQLError = (body: any, expectedMessage?: string) => {
  expect(body.errors, 'Expected GraphQL errors but none were returned').toBeDefined();
  
  const messages = body.errors.map((e: any) => e.message);
  
  console.log('\n===== GRAPHQL ERROR =====');
  body.errors.forEach((error: any, index: number) => {
    console.log(`Error ${index + 1}:`);
    console.log('Message:', error.message);
    console.log('Path:', error.path);
    console.log('-------------------------');
  });
  console.log('=========================\n');
  
  if (expectedMessage) {
    expect(messages.join(' ')).toContain(expectedMessage);
  }
};

/**
 * Validates empty customer orders response
 */
export const assertEmptyCustomerOrders = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('customerOrders');
  expect(body.data.customerOrders).not.toBeNull();
  
  const orders = body.data.customerOrders;
  expect(orders.edges.length).toBe(0);
  expect(orders.totalCount).toBe(0);
  
  console.log('\n===== NO CUSTOMER ORDERS FOUND =====\n');
};

/**
 * Validates order not found response
 */
export const assertOrderNotFound = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data.customerOrder).toBeNull();
  
  console.log('\n===== ORDER NOT FOUND =====\n');
};

/**
 * Validates order shipments by order ID response
 */
export const assertOrderShipmentsByOrderId = (body: any, expectedOrderId?: number) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('customerOrderShipments');
  expect(body.data.customerOrderShipments).not.toBeNull();
  
  const shipments = body.data.customerOrderShipments;
  
  // Validate edges array
  expect(Array.isArray(shipments.edges)).toBeTruthy();
  
  // Validate totalCount
  expect(typeof shipments.totalCount).toBe('number');
  expect(shipments.totalCount).toBeGreaterThanOrEqual(0);
  
  // Validate each shipment node
  shipments.edges.forEach((edge: any) => {
    expect(edge.node).toBeDefined();
    assertOrderShipmentNode(edge.node);
  });
  
  console.log('\n========== ORDER SHIPMENTS BY ORDER ID ==========');
  console.log(`Order ID: ${expectedOrderId}`);
  console.log(`Shipments Count: ${shipments.totalCount}`);
  console.log('=================================================\n');
};

/**
 * Validates a single order shipment response
 */
export const assertSingleOrderShipment = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('customerOrderShipment');
  expect(body.data.customerOrderShipment).not.toBeNull();
  
  const shipment = body.data.customerOrderShipment;
  assertOrderShipmentNode(shipment);
  
  console.log('\n========== SINGLE ORDER SHIPMENT ==========');
  console.log(`ID: ${shipment.id}`);
  console.log(`Status: ${shipment.status}`);
  console.log(`Track Number: ${shipment.trackNumber}`);
  console.log(`Carrier: ${shipment.carrierTitle}`);
  console.log(`Total Qty: ${shipment.totalQty}`);
  console.log(`Shipping Number: ${shipment.shippingNumber}`);
  console.log('============================================\n');
};

/**
 * Validates an order shipment node
 */
export const assertOrderShipmentNode = (shipment: any) => {
  expect(shipment).not.toBeNull();
  
  // Validate required fields
  expect(shipment.id).toBeDefined();
  expect(shipment._id).toBeDefined();
  expect(shipment.status).toBeDefined();
  
  // Validate optional fields
  if (shipment.trackNumber !== null && shipment.trackNumber !== undefined) {
    expect(typeof shipment.trackNumber).toBe('string');
  }
  if (shipment.carrierTitle !== null && shipment.carrierTitle !== undefined) {
    expect(typeof shipment.carrierTitle).toBe('string');
  }
  if (shipment.totalQty !== null && shipment.totalQty !== undefined) {
    expect(typeof shipment.totalQty).toBe('number');
  }
  if (shipment.createdAt !== null && shipment.createdAt !== undefined) {
    expect(typeof shipment.createdAt).toBe('string');
  }
  if (shipment.shippingNumber !== null && shipment.shippingNumber !== undefined) {
    expect(typeof shipment.shippingNumber).toBe('string');
  }
  
  // Validate items
  if (shipment.items) {
    expect(Array.isArray(shipment.items.edges)).toBeTruthy();
    shipment.items.edges.forEach((edge: any) => {
      assertOrderShipmentItemNode(edge.node);
    });
  }
  
  console.log(`  Shipment: ${shipment.id} - Status: ${shipment.status} - Qty: ${shipment.totalQty}`);
};

/**
 * Validates an order shipment item node
 */
export const assertOrderShipmentItemNode = (item: any) => {
  expect(item).not.toBeNull();
  
  // Validate required fields
  expect(item.id).toBeDefined();
  expect(item.name).toBeDefined();
  expect(item.sku).toBeDefined();
  
  if (item.qty !== null && item.qty !== undefined) {
    expect(typeof item.qty).toBe('number');
  }
  
  console.log(`    Shipment Item: ${item.name} (${item.sku}) - Qty: ${item.qty}`);
};

/**
 * Validates order shipment not found response
 */
export const assertOrderShipmentNotFound = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data.customerOrderShipment).toBeNull();
  
  console.log('\n===== ORDER SHIPMENT NOT FOUND =====\n');
};

/**
 * Validates empty order shipments response
 */
export const assertEmptyOrderShipments = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('customerOrderShipments');
  expect(body.data.customerOrderShipments).not.toBeNull();
  
  const shipments = body.data.customerOrderShipments;
  expect(shipments.edges.length).toBe(0);
  expect(shipments.totalCount).toBe(0);
  
  console.log('\n===== NO ORDER SHIPMENTS FOUND =====\n');
};
