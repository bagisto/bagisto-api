import { env } from '../../config/env';

export function getAdminToken(): string {
  return env.adminIntegrationToken;
}

export function adminHeaders(token?: string): Record<string, string> {
  return {
    'Authorization': `Bearer ${token ?? getAdminToken()}`,
  };
}
