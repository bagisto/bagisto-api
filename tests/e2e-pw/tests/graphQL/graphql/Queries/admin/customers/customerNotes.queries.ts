// tests/graphQL/graphql/Queries/admin/customers/customerNotes.queries.ts
//
// Admin Customer Notes — POST only. AdminCustomerNote payload type has no
// resolvable fields (per introspection 2026-05-26); only the wrapper
// adminCustomerNote { id } is selectable. Errors surface in `errors[]`.

export const ADMIN_CUSTOMER_NOTE_CREATE = `
  mutation createAdminCustomerNote(
    $customerId: Int!
    $note: String!
    $customerNotified: Boolean
  ) {
    createAdminCustomerNote(
      input: {
        customerId: $customerId
        note: $note
        customerNotified: $customerNotified
      }
    ) {
      adminCustomerNote {
        id
      }
    }
  }
`;
