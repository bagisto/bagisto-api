import { APIRequestContext, APIResponse } from '@playwright/test';

export interface SeedFile {
  name: string;
  mimeType: string;
  buffer: Buffer;
}

export type SeedMultipart = Record<string, string | number | boolean | SeedFile>;

export interface SeedRequest {
  method?: 'GET' | 'POST' | 'PUT';
  data?: unknown;
  multipart?: SeedMultipart;
  params?: Record<string, string>;
  token?: string;
}

export class SeedRequestError extends Error {
  constructor(
    readonly method: string,
    readonly path: string,
    readonly status: number,
    readonly bodyExcerpt: string,
  ) {
    super(`${method} ${path} answered ${status}: ${bodyExcerpt}`);
  }
}

export class SeedClient {
  constructor(
    private readonly request: APIRequestContext,
    private readonly baseUrl: string,
    private readonly storefrontKey: string,
    private readonly adminToken: string,
  ) {}

  async shop<T = any>(path: string, options: SeedRequest = {}): Promise<T> {
    return this.send<T>(`/api/shop${path}`, options);
  }

  async admin<T = any>(path: string, options: Omit<SeedRequest, 'token'> = {}): Promise<T> {
    return this.send<T>(`/api/admin${path}`, { ...options, token: this.adminToken });
  }

  async graphql<T = any>(query: string, variables: Record<string, unknown>, token?: string): Promise<T> {
    const body = await this.send<any>('/api/graphql', { method: 'POST', data: { query, variables }, token });

    if (Array.isArray(body?.errors) && body.errors.length > 0) {
      throw new SeedRequestError('POST', '/api/graphql', 200, SeedClient.excerpt(JSON.stringify(body.errors)));
    }

    return body?.data as T;
  }

  private async send<T>(path: string, options: SeedRequest): Promise<T> {
    const method = options.method ?? 'GET';
    const url = this.urlFor(path, options.params);
    const response = await this.fetchOnce(url, method, options);

    const finalResponse = response.status() === 429
      ? await this.retryAfterRateLimit(url, method, options)
      : response;

    const text = await finalResponse.text();

    if (!finalResponse.ok()) {
      throw new SeedRequestError(method, path, finalResponse.status(), SeedClient.excerpt(text));
    }

    return SeedClient.parse(text) as T;
  }

  private async retryAfterRateLimit(url: string, method: string, options: SeedRequest): Promise<APIResponse> {
    await new Promise(resolve => setTimeout(resolve, 2000));

    return this.fetchOnce(url, method, options);
  }

  private fetchOnce(url: string, method: string, options: SeedRequest): Promise<APIResponse> {
    const headers: Record<string, string> = {
      'Accept': 'application/json',
      'X-STOREFRONT-KEY': this.storefrontKey,
    };

    if (options.token) {
      headers['Authorization'] = `Bearer ${options.token}`;
    }

    if (options.multipart) {
      return this.request.fetch(url, { method, headers, multipart: SeedClient.multipartFields(options.multipart) });
    }

    if (options.data !== undefined) {
      headers['Content-Type'] = 'application/json';

      return this.request.fetch(url, { method, headers, data: options.data });
    }

    return this.request.fetch(url, { method, headers });
  }

  private urlFor(path: string, params?: Record<string, string>): string {
    const query = params ? `?${new URLSearchParams(params).toString()}` : '';

    return `${this.baseUrl}${path}${query}`;
  }

  private static multipartFields(fields: SeedMultipart): Record<string, string | number | boolean | SeedFile> {
    return Object.fromEntries(
      Object.entries(fields).map(([key, value]) => [key, typeof value === 'boolean' ? (value ? '1' : '0') : value]),
    );
  }

  private static parse(text: string): unknown {
    if (!text) {
      return null;
    }

    try {
      return JSON.parse(text);
    } catch {
      return text;
    }
  }

  static excerpt(text: string): string {
    return text.replace(/\s+/g, ' ').slice(0, 300);
  }
}
