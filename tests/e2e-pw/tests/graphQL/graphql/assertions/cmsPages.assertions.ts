// utils/assertions/cmsPages.assertions.ts

import { expect } from '@playwright/test';

/**
 * Asserts that CMS pages response is valid
 */
export const assertCmsPagesResponse = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('pages');
  expect(body.data.pages).not.toBeNull();

  const pages = body.data.pages;
  
  // Validate page info
  expect(pages.pageInfo).toBeDefined();
  expect(typeof pages.pageInfo.hasNextPage).toBe('boolean');
  expect(typeof pages.pageInfo.endCursor).toBe('string');

  // Validate total count
  expect(typeof pages.totalCount).toBe('number');
  expect(pages.totalCount).toBeGreaterThanOrEqual(0);

  // Validate edges
  expect(Array.isArray(pages.edges)).toBeTruthy();

  pages.edges.forEach((edge: any) => {
    // Validate cursor
    expect(typeof edge.cursor).toBe('string');
    
    // Validate page node
    const page = edge.node;
    expect(page).not.toBeNull();
    
    expect(page.id).toBeDefined();
    expect(['string', 'number']).toContain(typeof page._id);
    expect(['string', 'number', 'object']).toContain(typeof page.layout);
    expect(page.createdAt).toBeDefined();
    expect(page.createdAt).toMatch(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/);
    expect(page.updatedAt).toBeDefined();
    expect(page.updatedAt).toMatch(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/);
    
    // Validate translation if present
    if (page.translation) {
      expect(page.translation).toHaveProperty('id');
      expect(page.translation).toHaveProperty('pageTitle');
      expect(page.translation).toHaveProperty('urlKey');
      expect(page.translation).toHaveProperty('htmlContent');
      expect(page.translation).toHaveProperty('metaTitle');
      expect(page.translation).toHaveProperty('metaDescription');
      expect(page.translation).toHaveProperty('metaKeywords');
      expect(page.translation).toHaveProperty('locale');
    }
  });

  // Print page count for debugging
  console.log(`\n========== CMS PAGES FOUND ==========`);
  console.log(`Total Pages: ${pages.totalCount}`);
  console.log(`Pages Returned: ${pages.edges.length}`);
  console.log(`Has Next Page: ${pages.pageInfo.hasNextPage}`);
  if (pages.pageInfo.hasNextPage) {
    console.log(`End Cursor: ${pages.pageInfo.endCursor}`);
  }
  console.log('====================================\n');
};

/**
 * Asserts that no CMS pages are found
 */
export const assertCmsPagesNoResults = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('pages');
  expect(body.data.pages).not.toBeNull();

  const pages = body.data.pages;
  
  expect(pages.totalCount).toBe(0);
  expect(pages.edges.length).toBe(0);

  console.log('\n===== NO CMS PAGES FOUND =====\n');
};

/**
 * Asserts that a single CMS page response is valid
 */
export const assertCmsPageResponse = (body: any, expectedId?: string) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('page');
  
  const page = body.data.page;
  
  // Page might be null if not found
  if (page === null) {
    console.log('\n===== CMS PAGE NOT FOUND =====\n');
    return;
  }
  
  expect(page).not.toBeNull();
  expect(page.id).toBeDefined();
  
  if (expectedId) {
    expect(page.id).toEqual(expectedId);
  }
  
  expect(['string', 'number']).toContain(typeof page._id);
  expect(['string', 'object']).toContain(typeof page.layout);
  expect(page.createdAt).toBeDefined();
  expect(page.createdAt).toMatch(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/);
  expect(page.updatedAt).toBeDefined();
  expect(page.updatedAt).toMatch(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/);
  
  // Validate translation if present
  if (page.translation) {
    expect(page.translation).toHaveProperty('id');
    expect(page.translation).toHaveProperty('pageTitle');
    expect(page.translation).toHaveProperty('urlKey');
    expect(page.translation).toHaveProperty('htmlContent');
    expect(typeof page.translation.htmlContent).toBe('string');
    expect(page.translation).toHaveProperty('metaTitle');
    expect(page.translation).toHaveProperty('metaDescription');
    expect(page.translation).toHaveProperty('metaKeywords');
    expect(page.translation).toHaveProperty('locale');
  }

  // Print page details for debugging
  console.log('\n========== CMS PAGE DETAILS ==========');
  console.log(`Page ID: ${page.id}`);
  console.log(`Layout: ${page.layout}`);
  if (page.translation) {
    console.log(`Page Title: ${page.translation.pageTitle}`);
    console.log(`URL Key: ${page.translation.urlKey}`);
    console.log(`Locale: ${page.translation.locale}`);
  }
  console.log(`Created At: ${page.createdAt}`);
  console.log(`Updated At: ${page.updatedAt}`);
  console.log('======================================\n');
};

/**
 * Asserts that CMS page is not found
 */
export const assertCmsPageNotFound = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('page');
  expect(body.data.page).toBeNull();

  console.log('\n===== CMS PAGE NOT FOUND (as expected) =====\n');
};

/**
 * Asserts GraphQL error response
 */
export const assertGraphQLError = (body: any, expectedMessage?: string) => {
  expect(body).toHaveProperty('errors');
  expect(Array.isArray(body.errors)).toBeTruthy();
  expect(body.errors.length).toBeGreaterThan(0);
  
  if (expectedMessage) {
    const errorMessages = body.errors.map((e: any) => e.message).join(' ');
    expect(errorMessages).toContain(expectedMessage);
  }
  
  console.log('\n========== GRAPHQL ERROR ==========');
  console.log(JSON.stringify(body.errors, null, 2));
  console.log('==================================\n');
};

/**
 * Asserts that required field is missing
 */
export const assertRequiredFieldError = (body: any, fieldName: string) => {
  assertGraphQLError(body, fieldName);
};

/**
 * Validates CMS page structure
 */
export const validateCmsPageStructure = (page: any) => {
  expect(page).toHaveProperty('id');
  expect(page).toHaveProperty('_id');
  expect(page).toHaveProperty('layout');
  expect(page).toHaveProperty('createdAt');
  expect(page).toHaveProperty('updatedAt');
  
  // createdAt and updatedAt should be valid ISO dates
  expect(page.createdAt).toMatch(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/);
  expect(page.updatedAt).toMatch(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/);
  
  // Layout should be a valid string or null (could be empty)
  expect(['string', 'object']).toContain(typeof page.layout);
};

/**
 * Validates CMS page translation structure
 */
export const validateCmsPageTranslation = (translation: any) => {
  expect(translation).toHaveProperty('id');
  expect(translation).toHaveProperty('pageTitle');
  expect(translation).toHaveProperty('urlKey');
  expect(translation).toHaveProperty('htmlContent');
  expect(translation).toHaveProperty('metaTitle');
  expect(translation).toHaveProperty('metaDescription');
  expect(translation).toHaveProperty('metaKeywords');
  expect(translation).toHaveProperty('locale');
  
  // Validate types
  expect(typeof translation.pageTitle).toBe('string');
  expect(typeof translation.urlKey).toBe('string');
  expect(typeof translation.htmlContent).toBe('string');
  expect(typeof translation.metaTitle).toBe('string');
  expect(typeof translation.metaDescription).toBe('string');
  expect(typeof translation.metaKeywords).toBe('string');
  expect(typeof translation.locale).toBe('string');
};
