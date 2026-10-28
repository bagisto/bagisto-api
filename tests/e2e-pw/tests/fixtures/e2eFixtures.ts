import fs from 'fs';
import path from 'path';

export const E2E_FIXTURES_FILE = path.resolve(__dirname, '../../.e2e-fixtures.json');

export interface E2eCustomerFixture {
  id: number;
  email: string;
  password: string;
}

export interface E2eOrderFixture {
  id: number;
  incrementId: string;
  productId: number;
  invoiceId: number;
  shipmentId: number;
  shipmentItemIds: number[];
}

export interface E2eDownloadableFixture {
  orderId: number;
  productId: number;
  purchasedLinkId: number;
}

export interface E2eReturnFixture {
  id: number;
  orderId: number;
  imageId: number;
  messageId: number;
  attachmentName: string;
}

export interface E2eGuestOrderFixture {
  id: number;
  incrementId: string;
  email: string;
}

export interface E2eBookingFixture {
  bookingProductId: number;
  productId: number;
  type: string;
  date: string;
}

export interface E2eFixtures {
  customer?: E2eCustomerFixture;
  otherCustomer?: E2eCustomerFixture;
  order?: E2eOrderFixture;
  downloadable?: E2eDownloadableFixture;
  rma?: E2eReturnFixture;
  booking?: E2eBookingFixture;
  guestOrder?: E2eGuestOrderFixture;
}

let cached: E2eFixtures | undefined;

export function e2eFixtures(): E2eFixtures {
  if (cached) {
    return cached;
  }

  cached = fs.existsSync(E2E_FIXTURES_FILE)
    ? (JSON.parse(fs.readFileSync(E2E_FIXTURES_FILE, 'utf8')) as E2eFixtures)
    : {};

  return cached;
}

export function missingFixtureReason(name: keyof E2eFixtures): string {
  return `The ${name} fixture is not seeded; run the suite through playwright.config.ts globalSetup or provide ${E2E_FIXTURES_FILE}.`;
}
