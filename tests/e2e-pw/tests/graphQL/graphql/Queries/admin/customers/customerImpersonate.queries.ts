// tests/graphQL/graphql/Queries/admin/customers/customerImpersonate.queries.ts
//
// Admin Customer Impersonate — POST only. Surfaces the issued customer
// Sanctum token, customer id/email, expiresAt, and impersonatedByAdminId.
// Verified live 2026-05-26 that token + customerId + customerEmail +
// expiresAt + impersonatedByAdminId all resolve as scalars on the payload.

export const ADMIN_CUSTOMER_IMPERSONATE = `
  mutation createAdminCustomerImpersonate($customerId: Int!) {
    createAdminCustomerImpersonate(input: { customerId: $customerId }) {
      adminCustomerImpersonate {
        id
        _id
        token
        customerId
        customerEmail
        expiresAt
        impersonatedByAdminId
      }
    }
  }
`;
