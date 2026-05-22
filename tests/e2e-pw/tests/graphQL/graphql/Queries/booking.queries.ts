export const GET_BOOKING_SLOTS = `
  query getBookingSlots($id: Int!, $date: String!) {
    bookingSlots(id: $id, date: $date) {
      slotId
      from
      to
      timestamp
      qty
    }
  }
`;

export const GET_BOOKING_SLOTS_RENTAL_HOURLY = `
  query getBookingSlotsRentalHourly($id: Int!, $date: String!) {
    bookingSlots(id: $id, date: $date) {
      slotId
      time
      slots
    }
  }
`;
