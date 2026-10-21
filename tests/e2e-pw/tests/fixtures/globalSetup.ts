import fs from 'fs';
import path from 'path';
import dotenv from 'dotenv';
import { request } from '@playwright/test';
import { E2E_FIXTURES_FILE, E2eFixtures } from './e2eFixtures';
import { FixtureSeeder } from './FixtureSeeder';
import { SeedClient } from './SeedClient';

const REQUEST_TIMEOUT_MS = 120_000;

function readEnv(name: string): string | undefined {
  const value = process.env[name]?.trim();

  return value ? value : undefined;
}

function appTimezone(): string {
  const fromEnv = readEnv('APP_TIMEZONE');

  if (fromEnv) {
    return fromEnv;
  }

  const appEnvFile = path.join(readEnv('BAGISTO_PATH') ?? path.resolve(__dirname, '../../../../../../..'), '.env');

  try {
    return dotenv.parse(fs.readFileSync(appEnvFile)).APP_TIMEZONE?.trim() || 'UTC';
  } catch {
    return 'UTC';
  }
}

function writeFixtures(fixtures: E2eFixtures): void {
  try {
    fs.writeFileSync(E2E_FIXTURES_FILE, `${JSON.stringify(fixtures, null, 2)}\n`);
    console.log(`[e2e fixtures] wrote ${Object.keys(fixtures).join(', ') || 'no fixtures'} to ${E2E_FIXTURES_FILE}`);
  } catch (error) {
    console.log(`[e2e fixtures] could not write ${E2E_FIXTURES_FILE}: ${error instanceof Error ? error.message : String(error)}`);
  }
}

export default async function globalSetup(): Promise<void> {
  const baseUrl = readEnv('BAGISTO_URL') ?? 'http://127.0.0.1:8000';
  const storefrontKey = readEnv('STOREFRONT_ACCESS_KEY');
  const adminToken = readEnv('ADMIN_INTEGRATION_TOKEN');

  if (
    !storefrontKey
    || !adminToken
  ) {
    console.log('[e2e fixtures] STOREFRONT_ACCESS_KEY or ADMIN_INTEGRATION_TOKEN is not set; no fixtures seeded');
    writeFixtures({});

    return;
  }

  let fixtures: E2eFixtures = {};

  try {
    const context = await request.newContext({ timeout: REQUEST_TIMEOUT_MS, ignoreHTTPSErrors: true });

    try {
      const client = new SeedClient(context, baseUrl.replace(/\/+$/, ''), storefrontKey, adminToken);

      fixtures = await new FixtureSeeder(client, appTimezone()).seed();
    } finally {
      await context.dispose();
    }
  } catch (error) {
    console.log(`[e2e fixtures] seeding stopped: ${error instanceof Error ? error.message : String(error)}`);
  }

  writeFixtures(fixtures);
}
