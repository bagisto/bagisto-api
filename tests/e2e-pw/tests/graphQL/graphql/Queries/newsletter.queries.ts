// graphql/Queries/newsletter.queries.ts

export const CREATE_NEWSLETTER = `
  mutation createNewsletter($input: createNewsletterInput!) {
    createNewsletter(input: $input) {
      newsletter {
        success
        message
      }
    }
  }
`;
