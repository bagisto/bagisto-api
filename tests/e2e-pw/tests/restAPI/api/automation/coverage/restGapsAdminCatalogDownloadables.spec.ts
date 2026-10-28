import { test, expect } from '@playwright/test';
import { sendAdminMultipartRequest, sendAdminRequest } from '../../../rest/helpers/adminClient';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import {
  BINARY_HEADERS,
  REST_GAPS,
  REST_GAPS_MESSAGES as MSG,
  UNKNOWN_ID,
  createAdminProduct,
  deleteAdminProducts,
  expectAdminUnauthenticated,
  expectDetail,
  postMultipartWithoutToken,
  readAdminProduct,
  safeJson,
  uniqueStamp,
  updateAdminProduct,
} from '../../../rest/helpers/coverage/restGaps.helpers';

test.describe.configure({ timeout: 120_000 });

const SKU_ATTRIBUTE_ID = 1;

interface UploadCase {
  label: string;
  kind: 'link' | 'sample';
  endpoint: (productId: number) => string;
  directory: string;
}

const UPLOADS: UploadCase[] = [
  { label: 'downloadable link', kind: 'link', endpoint: REST_GAPS.ADMIN_DOWNLOADABLE_LINK_UPLOAD, directory: 'product_downloadable_links' },
  { label: 'downloadable sample', kind: 'sample', endpoint: REST_GAPS.ADMIN_DOWNLOADABLE_SAMPLE_UPLOAD, directory: 'product_downloadable_samples' },
];

function textFile(name: string) {
  return { name, mimeType: 'text/plain', buffer: Buffer.from(`e2e downloadable content ${name}`) };
}

test.describe('admin catalog downloadable file upload rest api', () => {
  let createdIds: number[];
  let productId: number;

  test.beforeEach(async ({ request }) => {
    createdIds = [];
    productId = (await createAdminProduct(request, 'downloadable')).id;
    createdIds.push(productId);
  });

  test.afterEach(async ({ request }) => {
    await deleteAdminProducts(request, createdIds);
  });

  test('should store an uploaded link file that the product link then points at', async ({ request }) => {
    const name = `link-${uniqueStamp()}.txt`;

    const response = await sendAdminMultipartRequest(request, REST_GAPS.ADMIN_DOWNLOADABLE_LINK_UPLOAD(productId), { file: textFile(name) });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.type).toBe('link');
    expect(body.name).toBe(name);
    expect(body.path).toMatch(new RegExp(`^product_downloadable_links/${productId}/[^/]+\\.txt$`));

    const title = `E2E Link ${uniqueStamp()}`;
    const updated = await updateAdminProduct(request, productId, {
      downloadable_links: {
        link_0: { en: { title }, price: '5.00', downloads: '2', sort_order: '1', type: 'file', file: body.path, file_name: name },
      },
    });

    expect(updated.downloadableLinks).toHaveLength(1);
    expect(updated.downloadableLinks[0].type).toBe('file');
    expect(updated.downloadableLinks[0].file).toBe(body.path);

    const stored = await readAdminProduct(request, productId);
    expect(stored.downloadableLinks.map((link: any) => link.file)).toEqual([body.path]);
  });

  test('should store an uploaded sample file that the product sample then points at', async ({ request }) => {
    const name = `sample-${uniqueStamp()}.txt`;

    const response = await sendAdminMultipartRequest(request, REST_GAPS.ADMIN_DOWNLOADABLE_SAMPLE_UPLOAD(productId), { file: textFile(name) });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.type).toBe('sample');
    expect(body.name).toBe(name);
    expect(body.path).toMatch(new RegExp(`^product_downloadable_samples/${productId}/[^/]+\\.txt$`));

    const title = `E2E Sample ${uniqueStamp()}`;
    const updated = await updateAdminProduct(request, productId, {
      downloadable_samples: {
        sample_0: { en: { title }, sort_order: '1', type: 'file', file: body.path, file_name: name },
      },
    });

    expect(updated.downloadableSamples).toHaveLength(1);
    expect(updated.downloadableSamples[0].type).toBe('file');
    expect(updated.downloadableSamples[0].file).toBe(body.path);

    const stored = await readAdminProduct(request, productId);
    expect(stored.downloadableSamples.map((sample: any) => sample.file)).toEqual([body.path]);
  });

  for (const upload of UPLOADS) {
    test(`should reject a ${upload.label} upload without a file`, async ({ request }) => {
      await expectDetail(
        await sendAdminMultipartRequest(request, upload.endpoint(productId), { note: 'no file attached' }),
        422,
        MSG.DOWNLOADABLE_FILE_REQUIRED
      );
    });

    test(`should report an unknown product for a ${upload.label} upload`, async ({ request }) => {
      await expectDetail(
        await sendAdminMultipartRequest(request, upload.endpoint(UNKNOWN_ID), { file: textFile(`${upload.kind}.txt`) }),
        404,
        MSG.ADMIN_PRODUCT_NOT_FOUND
      );
    });

    test(`should refuse a ${upload.label} upload without an admin token`, async ({ request }) => {
      await expectAdminUnauthenticated(
        await postMultipartWithoutToken(request, upload.endpoint(productId), { file: textFile(`${upload.kind}.txt`) })
      );
    });
  }
});

test.describe('admin catalog downloadable file download rest api', () => {
  let createdIds: number[];
  let productId: number;

  test.beforeEach(async ({ request }) => {
    createdIds = [];
    productId = (await createAdminProduct(request, 'simple')).id;
    createdIds.push(productId);
  });

  test.afterEach(async ({ request }) => {
    await deleteAdminProducts(request, createdIds);
  });

  test('should report no stored file for an attribute whose value is not a file', async ({ request }) => {
    const response = await sendAdminRequest(request, REST_GAPS.ADMIN_DOWNLOADABLE_DOWNLOAD(productId, SKU_ATTRIBUTE_ID), {
      headers: BINARY_HEADERS,
    });

    expect(response.headers()['content-disposition']).toBeUndefined();
    await expectDetail(response, 404, MSG.DOWNLOADABLE_FILE_NOT_FOUND);
  });

  test('should report no stored file for a product that does not exist', async ({ request }) => {
    const response = await sendAdminRequest(request, REST_GAPS.ADMIN_DOWNLOADABLE_DOWNLOAD(UNKNOWN_ID, SKU_ATTRIBUTE_ID), {
      headers: BINARY_HEADERS,
    });

    expect(response.headers()['content-disposition']).toBeUndefined();
    await expectDetail(response, 404, MSG.DOWNLOADABLE_FILE_NOT_FOUND);
  });

  test('should refuse a downloadable file download without an admin token', async ({ request }) => {
    const response = await sendRestRequest(request, REST_GAPS.ADMIN_DOWNLOADABLE_DOWNLOAD(productId, SKU_ATTRIBUTE_ID), {
      headers: BINARY_HEADERS,
    });

    expect(response.headers()['content-disposition']).toBeUndefined();
    await expectAdminUnauthenticated(response);
  });
});
