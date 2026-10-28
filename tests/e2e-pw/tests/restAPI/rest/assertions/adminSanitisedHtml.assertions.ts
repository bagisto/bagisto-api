import { expect } from '@playwright/test';

export const UNSAFE_HTML =
  '<p>Safe text</p><script>alert(1)</script>{{ $secret }}{!! $raw !!}@php echo 1; @endphp<?php echo 2; ?>';

export function assertSanitisedHtml(value: unknown): void {
  expect(typeof value, 'stored html is a string').toBe('string');

  const html = value as string;

  expect(html).toContain('<p>Safe text</p>');
  expect(html).not.toContain('<script');
  expect(html).not.toContain('alert(1)');
  expect(html).not.toContain('{{');
  expect(html).not.toContain('{!!');
  expect(html).not.toContain('@php');
  expect(html).not.toContain('<?php');
}
