export const env = {
  baseUrl: process.env.BAGISTO_URL,
  graphqlEndpoint: '/api/graphql',
  storefrontAccessKey: process.env.STOREFRONT_ACCESS_KEY,
};

if (!env.baseUrl) {
  throw new Error('❌ BAGISTO_URL is not defined');
}

if (!env.storefrontAccessKey) {
  throw new Error('❌ STOREFRONT_ACCESS_KEY is not defined');
}
