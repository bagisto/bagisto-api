export const CREATE_CONTACT_US = `
  mutation createContactUs($input: createContactUsInput!) {
    createContactUs(input: $input) {
      contactUs {
        success
        message
      }
      clientMutationId
    }
  }
`;
