// tests/restAPI/api/automation/channels.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

test.describe('Channels REST API', () => {
  test('Should return all channels', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CHANNELS);
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(Array.isArray(body)).toBeTruthy();
    console.log('Channels count:', body.length);

    if (body.length > 0) {
      console.log('First channel:', JSON.stringify(body[0], null, 2));
    }
  });

  test('Should return single channel by ID', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CHANNELS);
    const body = await response.json();
    expect(body.length).toBeGreaterThan(0);

    const channelId = body[0].id;
    const single = await sendRestRequest(request, ENDPOINTS.CHANNEL(channelId));
    expect(single.status()).toBe(200);
    const sb = await single.json();
    expect(sb.id).toBe(channelId);
    expect(sb).toHaveProperty('locales');
    expect(sb).toHaveProperty('currencies');
    console.log('Channel:', JSON.stringify({ id: sb.id, defaultLocale: sb.defaultLocale, baseCurrency: sb.baseCurrency }));
  });

  test('Should return 404 for non-existent channel', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CHANNEL(999999));
    expect(response.status()).toBe(404);
    console.log('404 non-existent channel');
  });

  test('Should return channel translations', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CHANNEL_TRANSLATIONS);
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(Array.isArray(body)).toBeTruthy();
    console.log('Channel translations:', body.length);

    if (body.length > 0) {
      console.log('First translation:', JSON.stringify({ id: body[0].id, name: body[0].name }));
    }
  });

  test('Should return single channel translation', async ({ request }) => {
    const list = await sendRestRequest(request, ENDPOINTS.CHANNEL_TRANSLATIONS);
    const listBody = await list.json();
    if (listBody.length === 0) {
      test.skip(true, 'No channel translations');
      return;
    }
    const transId = listBody[0].id;
    const response = await sendRestRequest(request, ENDPOINTS.CHANNEL_TRANSLATION(transId));
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(body).toHaveProperty('id', transId);
    expect(body).toHaveProperty('name');
    console.log('Channel translation:', JSON.stringify({ id: body.id, name: body.name }));
  });
});
