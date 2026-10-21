import { env } from './env';

export function getAdminToken(): string {
  return env.adminIntegrationToken;
}

export function adminGraphQLHeaders(token?: string): Record<string, string> {
  return {
    'Authorization': `Bearer ${token ?? getAdminToken()}`,
  };
}
