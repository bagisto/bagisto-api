# Bagisto Playwright Test Suite Overview

This project contains Playwright-based API automation for the Bagisto GraphQL shop API.

The goal of the project is to:
- keep GraphQL query coverage organized
- validate real API behavior
- handle auth-required and guest flows cleanly
- make the suite easier to maintain as Bagisto docs and schema change

## Main Structure

### `tests/api/automation`
These files contain the actual Playwright test cases.

In simple terms:
- `*.spec.ts` files are the test files
- each file usually focuses on one feature area
- examples:
  - product tests
  - customer tests
  - cart tests
  - locale/currency/channel/category tests

These files are where requests are sent and assertions are made.

## `tests/graphql/Queries`
These files contain reusable GraphQL queries and mutations.

In simple terms:
- they store the raw GraphQL operations
- test files import them instead of writing long queries again and again
- each file is grouped by feature

Examples:
- product query file contains product-related queries
- customer query file contains customer-related queries
- currency query file contains currency-related queries

This keeps the specs cleaner and makes query updates easier when docs or schema change.

## `tests/graphql/assertions`
These files contain reusable validation logic for API responses.

In simple terms:
- they check response structure and important fields
- they reduce repeated `expect(...)` blocks in test files
- they help keep validation consistent across tests

So instead of rewriting the same checks in many specs, common assertions are kept here.

## `tests/graphql/helpers`
These files contain shared utilities used by the tests.

### `graphqlClient.ts`
This is the common request sender for GraphQL.

What it does:
- sends GraphQL requests to the Bagisto API
- attaches common headers like `X-STOREFRONT-KEY`
- uses the configured base URL and endpoint

Why it is used:
- avoids repeating request setup in every test
- keeps API calls consistent across the suite

### `testSupport.ts`
This contains common helper functions for reading and validating GraphQL responses.

What it does:
- safely reads nested response values
- extracts GraphQL error messages
- prints real API messages in terminal output
- provides reusable success/auth-aware assertions

Why it is used:
- GraphQL responses often have nested data and errors
- many tests need the same response handling
- this makes tests shorter, cleaner, and more consistent

## `tests/config`
These files contain configuration helpers used across the suite.

### `env.ts`
This reads values from `.env`.

What it does:
- loads project environment values like:
  - Bagisto base URL
  - storefront key
  - optional customer credentials
  - optional booking test values

Why it is used:
- keeps configuration in one place
- avoids hardcoding environment-specific values inside tests

### `auth.ts`
This contains reusable auth helpers.

What it does:
- creates customer auth headers when customer credentials are available
- creates guest cart auth headers for guest checkout/cart flows

Why it is used:
- many queries need auth
- some tests need guest auth, some need customer auth
- this avoids duplicating login/cart-token setup in many spec files

## `.env`
This file contains environment-specific values for running the suite locally.

Typical values include:
- Bagisto URL
- storefront access key
- optional customer login credentials
- optional booking product/date values

This file allows the same test suite to run in different environments with minimal code change.

## `playwright.config.ts`
This is the main Playwright configuration file.

What it does:
- tells Playwright where the tests are
- configures reporter, workers, retries, and base settings
- controls how the suite runs

## How The Flow Works

In general, the project works like this:
1. A spec file imports a query from `tests/graphql/Queries`
2. The spec sends the request using `graphqlClient.ts`
3. Auth helpers are used when required
4. Response helpers/assertions validate the result
5. Real API messages are logged where useful

## Why The Project Is Organized This Way

This structure is used to make the suite:
- easier to read
- easier to update when Bagisto docs change
- easier to debug
- less repetitive
- more reusable across many GraphQL features

## Short Summary

If someone is new to this repo:
- `tests/api/automation` = test cases
- `tests/graphql/Queries` = GraphQL queries/mutations
- `tests/graphql/assertions` = reusable validations
- `tests/graphql/helpers` = shared test utilities
- `tests/config` = environment and auth setup
- `.env` = local configuration values
- `playwright.config.ts` = Playwright runtime config

That is the basic structure of the project.
