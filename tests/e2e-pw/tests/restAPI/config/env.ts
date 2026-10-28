function readEnv(name: string, required = false): string | undefined {
  const value = process.env[name]?.trim();

  if (required && !value) {
    throw new Error(`❌ ${name} is not defined`);
  }

  return value || undefined;
}

export const env = {
  baseUrl: readEnv('BAGISTO_URL', true)!,
  storefrontAccessKey: readEnv('STOREFRONT_ACCESS_KEY', true)!,
  adminEmail: readEnv('ADMIN_EMAIL', true)!,
  adminPassword: readEnv('ADMIN_PASSWORD', true)!,
  adminApiToken: readEnv('ADMIN_API_TOKEN'),
  adminApiTokenOtherDomain: readEnv('ADMIN_API_TOKEN_OTHER_DOMAIN'),
  adminApiTokenRevocable: readEnv('ADMIN_API_TOKEN_REVOCABLE_REST'),
};
