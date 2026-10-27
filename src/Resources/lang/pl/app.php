<?php

return [

    'graphql' => [
        'headers' => [
            'unknown-channel' => 'Kanał ":code" w nagłówku X-Channel nie istnieje.',
            'storefront-key-required' => 'Nagłówek X-STOREFRONT-KEY jest wymagany',
            'storefront-key-invalid' => 'Nieprawidłowy klucz sklepu',
            'storefront-key-rate-limited' => 'Przekroczono limit żądań. Spróbuj ponownie za :seconds s',
        ],

        'cart' => [
            'authentication-required' => 'Token uwierzytelniający jest wymagany',
            'invalid-token' => 'Nieprawidłowy lub wygasły token uwierzytelniający',
            'unauthorized-access' => 'Nieautoryzowany dostęp do koszyka',
            'authenticated-only' => 'Tylko uwierzytelnieni użytkownicy mogą pobierać swoje koszyki',
            'merge-requires-auth' => 'Scalanie koszyka gościa wymaga uwierzytelnienia',
            'unknown-operation' => 'Nieznana operacja koszyka',

            'cart-not-found' => 'Koszyk nie znaleziony',
            'guest-cart-not-found' => 'Koszyk gościa nie znaleziony',
            'product-not-found' => 'Produkt nie znaleziony',

            'product-id-quantity-required' => 'Identyfikator produktu i ilość są wymagane',
            'cart-item-id-quantity-required' => 'Identyfikator pozycji koszyka i ilość są wymagane',

            'file-token-invalid' => 'Odwołanie do przesłanego pliku jest nieprawidłowe lub wygasło. Prześlij plik ponownie.',
            'file-token-forbidden' => 'Ten przesłany plik nie należy do Twojego koszyka.',
            'customizable-option-required' => 'Opcja konfigurowalna ":label" jest wymagana.',
            'customizable-file-option-invalid' => 'Wybrana opcja nie jest opcją plikową dla tego produktu.',
            'customizable-file-invalid' => 'Brak pliku, plik ma nieobsługiwane rozszerzenie lub jest zbyt duży.',
            'cart-item-id-required' => 'Identyfikator pozycji koszyka jest wymagany',
            'item-ids-required' => 'Tablica identyfikatorów pozycji jest wymagana',

            'event-booking-quantity-not-changeable' => 'Nie można zmienić ilości produktu rezerwacji wydarzenia. Ilość wynika z wyboru biletów.',
            'appointment-booking-quantity-not-changeable' => 'Nie można zmienić ilości produktu rezerwacji wizyty.',
            'coupon-code-required' => 'Kod kuponu jest wymagany',
            'address-data-required' => 'Kraj, województwo i kod pocztowy są wymagane',
            'grouped-qty-required' => 'Produkt grupowy wymaga wybranych ilości. Przekaż groupedQty jako ciąg JSON, np. {"101":2,"102":1}.',
            'grouped-qty-must-include-all' => 'Produkt grupowy wymaga ilości dla wszystkich powiązanych produktów. Brakujące identyfikatory: :ids.',
            'grouped-qty-invalid-associated' => 'Ilości produktu grupowego zawierają nieprawidłowe identyfikatory powiązanych produktów: :ids.',
            'grouped-qty-invalid-quantity' => 'Podano nieprawidłową ilość dla powiązanego produktu o identyfikatorze :id. Ilość musi być nieujemną liczbą całkowitą.',
            'bundle-qty-not-changeable' => 'Nie można zmienić ilości dla opcji pakietu ":option". Dozwolona ilość to :qty.',

            'add-product-failed' => 'Nie udało się dodać produktu do koszyka',
            'update-item-failed' => 'Nie udało się zaktualizować pozycji koszyka',
            'remove-item-failed' => 'Nie udało się usunąć pozycji koszyka',
            'remove-coupon-failed' => 'Nie udało się usunąć kuponu',
            'coupon-applied' => 'Kupon pomyślnie zastosowany',
            'coupon-removed' => 'Kupon pomyślnie usunięty',
            'move-to-wishlist-failed' => 'Nie udało się przenieść pozycji do listy życzeń',
            'estimate-shipping-failed' => 'Nie udało się oszacować kosztów wysyłki',

            'product-added-successfully' => 'Produkt pomyślnie dodany do koszyka',
            'cart-item-updated-successfully' => 'Pozycja koszyka pomyślnie zaktualizowana',
            'guest-cart-merged' => 'Koszyk gościa pomyślnie scalony',
            'using-authenticated-cart' => 'Używanie koszyka uwierzytelnionego klienta',
            'cart-item-not-found' => 'Pozycja koszyka nie znaleziona',
            'new-guest-cart-created' => 'Utworzono nowy koszyk gościa z unikalnym tokenem sesji',
            'select-items-to-remove' => 'Wybierz pozycje do usunięcia',
            'select-items-to-move-wishlist' => 'Wybierz pozycje do przeniesienia na listę życzeń',
            'invalid-or-expired-token' => 'Token koszyka jest nieprawidłowy lub wygasł. Utwórz nowy koszyk.',
            'invalid-token-of-login-user' => 'Token zalogowanego użytkownika jest nieprawidłowy.',
            'booking-note-required' => 'booking.note jest wymagane dla rezerwacji stolika.',
            'booking-date-required' => 'booking.date jest wymagane, gdy booking.slot jest sformatowanym przedziałem czasowym.',
            'booking-qty-required' => 'booking.qty jest wymagane dla rezerwacji wydarzenia.',
            'booking-ticket-qty-invalid' => 'Liczba biletów na wydarzenie musi być liczbą nieujemną.',
            'booking-ticket-required' => 'Wybierz ilość co najmniej jednego biletu dla rezerwacji wydarzenia.',
            'booking-slot-unavailable' => 'Wybrany termin nie jest dostępny w podanym dniu.',
            'invalid-quantity' => 'Ilość musi wynosić co najmniej 1',
            'product-id-required' => 'Identyfikator produktu jest wymagany',
            'shipping-address-required' => 'Kraj i kod pocztowy są wymagane do oszacowania wysyłki',
        ],

        'token-verification' => [
            'invalid-operation' => 'Nieprawidłowa operacja',
            'invalid-input-data' => 'Nieprawidłowe dane wejściowe',
            'token-required' => 'Token jest wymagany',
            'invalid-token-format' => 'Nieprawidłowy format tokena',
            'token-not-found-or-expired' => 'Token nie znaleziony lub wygasł',
            'customer-not-found' => 'Klient nie znaleziony',
            'customer-account-suspended' => 'Konto klienta jest zawieszone',
            'error-verifying-token' => 'Błąd podczas weryfikacji tokena',
            'token-is-valid' => 'Token jest prawidłowy',
            'unauthenticated' => 'Nieuwierzytelniony. Zaloguj się, aby wykonać tę akcję',
        ],

        'forgot-password' => [
            'invalid-operation' => 'Nieprawidłowa operacja',
            'invalid-input-data' => 'Nieprawidłowe dane wejściowe',
            'email-required' => 'Adres e-mail jest wymagany',
            'reset-link-sent' => 'Link do resetowania został pomyślnie wysłany na Twój adres e-mail',
            'email-not-found' => 'Adres e-mail nie znaleziony',
            'throttled' => 'Zbyt wiele prób resetowania. Odczekaj chwilę przed ponowną próbą.',
            'error-sending-reset-link' => 'Wystąpił błąd podczas wysyłania linku resetującego',
        ],

        'reset-password' => [
            'failed' => 'Nie udało się zresetować hasła. Spróbuj ponownie.',
        ],

        'rate-limit' => [
            'too-many-attempts' => 'Zbyt wiele prób. Spróbuj ponownie za :seconds s.',
        ],

        'logout' => [
            'invalid-operation' => 'Nieprawidłowa operacja',
            'invalid-input-data' => 'Nieprawidłowe dane wejściowe',
            'token-required' => 'Token jest wymagany',
            'invalid-token-format' => 'Nieprawidłowy format tokena',
            'logged-out-successfully' => 'Pomyślnie wylogowano',
            'token-not-found-or-expired' => 'Token nie znaleziony lub już wygasł',
            'error-during-logout' => 'Błąd podczas wylogowywania',
            'unauthenticated' => 'Nieuwierzytelniony. Zaloguj się, aby wykonać tę akcję',
        ],

        'address' => [
            'deleted-successfully' => 'Adres pomyślnie usunięty',
            'authentication-required' => 'Token uwierzytelniający jest wymagany',
            'invalid-token' => 'Nieprawidłowy lub wygasły token',
            'unknown-operation' => 'Nieznana operacja',
            'address-id-required' => 'Identyfikator adresu jest wymagany',
            'address-not-found' => 'Adres nie znaleziony lub nie należy do tego klienta',
            'retrieved' => 'Adresy pomyślnie pobrane',
            'fetch-failed' => 'Nie udało się pobrać adresów:',
            'field-required' => 'Pole :field jest wymagane',
        ],

        'customer-profile' => [
            'authentication-required' => 'Token uwierzytelniający jest wymagany. Podaj token w danych wejściowych zapytania',
            'invalid-token' => 'Nieprawidłowy lub wygasły token',
            'profile-updated' => 'Profil klienta pomyślnie zaktualizowany',
        ],

        'customer' => [
            'password-mismatch' => 'Hasło i potwierdzenie hasła nie są zgodne',
            'confirm-password-required' => 'Potwierdzenie hasła jest wymagane przy zmianie hasła',
            'current-password-required' => 'Aby zmienić hasło, wymagane jest aktualne hasło',
            'current-password-incorrect' => 'Wprowadzone aktualne hasło jest nieprawidłowe',
            'verify-email-to-login' => 'Twoje konto zostało utworzone. Zweryfikuj swój adres e-mail, aby się zalogować.',
            'delete-password-required' => 'Do usunięcia konta wymagane jest Twoje hasło',
            'delete-password-incorrect' => 'Wprowadzone hasło jest nieprawidłowe',
            'delete-has-active-orders' => 'Nie można usunąć konta, dopóki masz zamówienia oczekujące lub w trakcie realizacji',
            'unauthenticated' => 'Nieuwierzytelniony. Zaloguj się, aby wykonać tę akcję',
            'id-required' => 'Identyfikator klienta jest wymagany',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/admin/customers/1" lub identyfikator liczbowy',
            'not-found' => 'Klient nie znaleziony',
            'phone-special-chars-not-allowed' => 'Numer telefonu komórkowego może zawierać tylko cyfry. Znaki specjalne są niedozwolone',
            'email-already-taken' => 'Ten adres e-mail jest już zarejestrowany na innym koncie',
            'invalid-gender' => 'Nieprawidłowa wartość płci ":gender". Dozwolone wartości: :valid',
        ],

        'product-review' => [
            'id-required' => 'Identyfikator recenzji produktu jest wymagany',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/reviews/1" lub identyfikator liczbowy',
            'not-found' => 'Recenzja produktu o identyfikatorze ":id" nie znaleziona',
            'item-not-found' => 'Nie znaleziono elementu ":id".',
            'product-id-required' => 'Identyfikator produktu jest wymagany',
            'product-not-found' => 'Produkt nie znaleziony',
            'rating-invalid' => 'Ocena musi być pomiędzy 1 a 5',
            'title-required' => 'Tytuł recenzji jest wymagany',
            'comment-required' => 'Komentarz recenzji jest wymagany',
            'review-disabled' => 'Recenzje produktów są obecnie wyłączone',
            'guest-review-disabled' => 'Recenzje gości są niedozwolone. Zaloguj się, aby dodać recenzję',
            'owner-required' => 'Zaloguj się, aby zarządzać swoją recenzją',
            'not-owned' => 'Ta recenzja nie została napisana przez Ciebie i nie może zostać zmodyfikowana ani usunięta',
            'invalid-attachments' => 'Nieprawidłowy format załączników.',
            'invalid-product-id-format' => 'Nieprawidłowy format identyfikatora produktu. Oczekiwany format IRI taki jak "/api/shop/products/1" lub identyfikator liczbowy',
        ],

        'customer-review' => [
            'id-required' => 'Identyfikator recenzji klienta jest wymagany',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/customer-reviews/1" lub identyfikator liczbowy',
            'not-found' => 'Recenzja klienta o identyfikatorze ":id" nie znaleziona',
        ],

        'newsletter' => [
            'error-during-subscription' => 'Nie można teraz dokończyć subskrypcji. Spróbuj ponownie później',
        ],

        'contact-us' => [
            'success' => 'Twoje zapytanie zostało pomyślnie wysłane. Wkrótce się z Tobą skontaktujemy',
            'failed' => 'Nie można teraz wysłać Twojego zapytania. Spróbuj ponownie później',
            'invalid-input' => 'Nieprawidłowe dane formularza kontaktowego',
        ],

        'customer-order' => [
            'id-required' => 'Identyfikator zamówienia klienta jest wymagany',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/customer-orders/1" lub identyfikator liczbowy',
            'not-found' => 'Zamówienie klienta o identyfikatorze ":id" nie znalezione',
        ],

        'return' => [
            'not-found' => 'Zgłoszenie zwrotu nie znalezione.',
            'disabled' => 'Zwroty nie są włączone w tym sklepie.',
            'invalid-input' => 'Brakujące lub nieprawidłowe dane zwrotu.',
            'invalid-resolution-type' => 'Typ rozwiązania musi mieć wartość "return" lub "cancel_items".',
            'agreement-required' => 'Musisz zaakceptować politykę zwrotów.',
            'invalid-order' => 'Wybrane zamówienie jest nieprawidłowe.',
            'invalid-item' => 'Wybrana pozycja nie kwalifikuje się do zwrotu.',
            'qty-exceeds' => 'Żądana ilość przekracza ilość możliwą do zwrotu.',
            'invalid-package-condition' => 'Stan opakowania musi mieć wartość "open" lub "packed".',
            'invalid-image' => 'Co najmniej jeden z przesłanych plików ma nieobsługiwany typ.',
            'invalid-custom-field' => 'Nieznane pole niestandardowe ":id".',
            'custom-field-required' => 'Pole ":field" jest wymagane.',
            'invalid-custom-field-value' => 'Wartość pola ":field" nie należy do dozwolonych opcji.',
            'created' => 'Zgłoszenie zwrotu pomyślnie utworzone.',
            'canceled' => 'Zgłoszenie zwrotu pomyślnie anulowane.',
            'already-canceled' => 'Zgłoszenie zwrotu zostało już anulowane.',
            'cancel-not-allowed' => 'Tego zgłoszenia zwrotu nie można anulować.',
            'close-not-allowed' => 'Tego zgłoszenia zwrotu nie można zamknąć.',
            'reopened' => 'Zgłoszenie zwrotu pomyślnie otwarte ponownie.',
            'reopen-not-allowed' => 'Tego zgłoszenia zwrotu nie można ponownie otworzyć.',
            'closed' => 'Zgłoszenie zwrotu pomyślnie zamknięte.',
            'message-sent' => 'Wiadomość pomyślnie wysłana.',
            'attachment-not-found' => 'Załącznik wiadomości nie znaleziony.',
        ],

        'eu-withdrawal' => [
            'not-found' => 'Oświadczenie o odstąpieniu nie znalezione.',
            'order-not-found' => 'Nie znaleziono zamówienia lub nie kwalifikuje się ono do odstąpienia.',
            'too-many-attempts' => 'Zbyt wiele prób. Spróbuj ponownie za :seconds s.',
            'created' => 'Oświadczenie o odstąpieniu pomyślnie złożone.',
        ],

        'customer-order-shipment' => [
            'id-required' => 'Identyfikator przesyłki jest wymagany',
            'not-found' => 'Przesyłka o identyfikatorze ":id" nie znaleziona lub nie należy do tego klienta',
            'item-not-found' => 'Pozycja przesyłki o identyfikatorze ":id" nie znaleziona lub nie należy do tego klienta',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/customer-order-shipments/1" lub identyfikator liczbowy',
        ],

        'cancel-order' => [
            'order-id-required' => 'Identyfikator zamówienia jest wymagany do anulowania zamówienia',
            'not-found' => 'Zamówienie o identyfikatorze ":id" nie znalezione lub nie należy do tego klienta',
            'cancel-success' => 'Zamówienie zostało pomyślnie anulowane',
            'cancel-failed' => 'Nie można anulować zamówienia. Mogło już zostać przetworzone, wysłane lub anulowane',
        ],

        'reorder' => [
            'order-id-required' => 'Identyfikator zamówienia jest wymagany do ponownego zamówienia',
            'not-found' => 'Zamówienie o identyfikatorze ":id" nie znalezione lub nie należy do tego klienta',
            'reorder-success' => 'Do koszyka dodano pozycje z poprzedniego zamówienia (:count)',
            'no-items-added' => 'Nie udało się dodać żadnych pozycji do koszyka. Produkty mogą być niedostępne lub wyprzedane',
        ],

        'customer-downloadable-product' => [
            'id-required' => 'Identyfikator produktu do pobrania klienta jest wymagany',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/customer-downloadable-products/1" lub identyfikator liczbowy',
            'not-found' => 'Produkt do pobrania klienta o identyfikatorze ":id" nie znaleziony',

            'download' => [
                'unauthenticated' => 'Brak autoryzacji: wymagane jest uwierzytelnienie klienta.',
                'not-found' => 'Nie znaleziono produktu do pobrania.',
                'pending' => 'Pobieranie oczekuje. Poczekaj na wystawienie faktury dla zamówienia.',
                'limit-exceeded' => 'Przekroczono limit pobrań.',
                'no-downloads-remaining' => 'Brak dostępnych pobrań.',
                'file-not-found' => 'Plik nie znaleziony.',
                'link-not-found' => 'Link do pobrania nie znaleziony.',
            ],
        ],

        'customer-invoice' => [
            'id-required' => 'Identyfikator faktury klienta jest wymagany',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/customer-invoices/1" lub identyfikator liczbowy',
            'not-found' => 'Faktura klienta o identyfikatorze ":id" nie znaleziona',
        ],

        'auth' => [
            'no-token-provided' => 'Nie podano tokena uwierzytelniającego. Podaj token w nagłówku Authorization jako "Bearer <token>" lub w polu input.token',
            'invalid-or-expired-token' => 'Nieprawidłowy lub wygasły token',
            'request-not-found' => 'Żądanie nie znalezione w kontekście',
            'token-required' => 'Authentication token is required. Please provide the token either in the GraphQL mutation input field or in the Authorization header as "Bearer <token>"',
            'unknown-resource' => 'Nieznany zasób',
            'cannot-update-other-profile' => 'Brak autoryzacji: Nie można zaktualizować profilu innego klienta',
            'refresh-token-invalid' => 'Token odświeżania jest nieprawidłowy lub wygasł. Zaloguj się ponownie.',
            'too-many-login-attempts' => 'Zbyt wiele prób logowania. Spróbuj ponownie za :seconds s.',
            'too-many-refresh-requests' => 'Zbyt wiele odświeżeń tokenu. Spróbuj ponownie za :seconds sekund.',
            'rate-limit-exceeded' => 'Zbyt wiele prób.',
            'access-token-expired' => 'Token dostępu wygasł. Odśwież go, aby kontynuować.',
            'logged-out-all' => 'Wylogowano ze wszystkich sesji.',
            'session-not-found' => 'Nie znaleziono sesji.',
            'session-revoked' => 'Sesja została unieważniona.',
        ],

        'upload' => [
            'invalid-base64' => 'Nieprawidłowe dane obrazu zakodowane w base64',
            'size-exceeds-limit' => 'Rozmiar obrazu nie może przekraczać 5 MB',
            'invalid-format' => 'Nieprawidłowy format obrazu. Podaj obraz zakodowany w base64 ze schematem data URI (data:image/jpeg;base64,...)',
            'failed' => 'Przesyłanie obrazu nie powiodło się',
        ],

        'attribute' => [
            'code-already-exists' => 'Kod atrybutu już istnieje',
            'option-id-required' => 'Parametr "attributeId" jest wymagany przy bezpośrednim pobieraniu opcji atrybutu',
            'id-required' => 'Identyfikator atrybutu jest wymagany',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/attributes/1" lub identyfikator liczbowy',
            'not-found' => 'Atrybut nie znaleziony',
        ],

        'attribute-option' => [
            'id-required' => 'Identyfikator opcji atrybutu jest wymagany',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/attribute-options/1" lub identyfikator liczbowy',
            'not-found' => 'Opcja atrybutu nie znaleziona',
        ],

        'channel' => [
            'id-required' => 'Identyfikator kanału jest wymagany',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/channels/1" lub identyfikator liczbowy',
            'not-found' => 'Kanał nie znaleziony',
        ],

        'country-state' => [
            'id-required' => 'Identyfikator województwa/regionu jest wymagany',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/country-states/1" lub identyfikator liczbowy',
            'not-found' => 'Województwo/region nie znalezione',
            'country-id-required' => 'Identyfikator kraju jest wymagany',
        ],

        'country' => [
            'id-required' => 'Identyfikator kraju jest wymagany',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/countries/1" lub identyfikator liczbowy',
            'not-found' => 'Kraj nie znaleziony',
        ],

        'login' => [
            'invalid-credentials' => 'Nieprawidłowy adres e-mail lub hasło',
            'account-suspended' => 'Twoje konto zostało zawieszone',
            'account-inactive' => 'Twoja aktywacja oczekuje na zatwierdzenie administratora',
            'email-not-verified' => 'Najpierw zweryfikuj swoje konto e-mail.',
            'successful' => 'Zalogowano pomyślnie',
            'invalid-request' => 'Nieprawidłowe żądanie logowania',
        ],

        'social-login' => [
            'signed-in' => 'Zalogowano pomyślnie.',
            'token-required' => 'Wymagany jest token logowania społecznościowego.',
            'invalid-token' => 'Token logowania społecznościowego jest nieprawidłowy lub wygasł. Spróbuj ponownie.',
            'google-not-configured' => 'Logowanie przez Google nie jest skonfigurowane dla tego sklepu.',
            'wrong-audience' => 'Ten token został wydany dla innej aplikacji.',
            'email-required' => 'Dostawca nie udostępnił adresu e-mail. Zarejestruj się za pomocą adresu e-mail.',
            'account-inactive' => 'Twoja aktywacja oczekuje na zatwierdzenie administratora',
            'provider-not-supported' => 'Ten dostawca logowania społecznościowego nie jest obsługiwany.',
            'provider-disabled' => 'Ten dostawca logowania społecznościowego nie jest włączony.',
        ],

        'checkout' => [
            'invalid-input' => 'Nieprawidłowe dane wejściowe dla operacji realizacji zamówienia',
            'billing-address-required' => 'Adres rozliczeniowy jest wymagany',
            'shipping-address-required' => 'Adres wysyłki jest wymagany dla przesyłek',
            'address-save-failed' => 'Nie udało się zapisać adresu',
            'address-saved' => 'Adres pomyślnie zapisany',
            'shipping-method-required' => 'Metoda wysyłki jest wymagana',
            'invalid-shipping-method' => 'Nieprawidłowa lub niedostępna metoda wysyłki',
            'shipping-method-save-failed' => 'Nie udało się zapisać metody wysyłki',
            'shipping-method-saved' => 'Metoda wysyłki pomyślnie zapisana',
            'shipping-method-error' => 'Błąd podczas zapisywania metody wysyłki',
            'payment-method-required' => 'Metoda płatności jest wymagana',
            'invalid-payment-method' => 'Nieprawidłowa lub niedostępna metoda płatności',
            'payment-method-save-failed' => 'Nie udało się zapisać metody płatności',
            'payment-method-saved' => 'Metoda płatności pomyślnie zapisana',
            'payment-method-error' => 'Błąd podczas zapisywania metody płatności',
            'order-placed' => 'Zamówienie pomyślnie złożone',
            'order-redirect-required' => 'Ta metoda płatności wymaga, aby kupujący dokończył płatność w bramce płatniczej. Przekieruj go na redirectUrl; zamówienie zostanie utworzone, gdy bramka potwierdzi płatność.',
            'order-creation-failed' => 'Tworzenie zamówienia nie powiodło się: identyfikator zamówienia jest pusty lub zamówienie nie zostało zapisane',
            'order-retrieval-failed' => 'Nie udało się pobrać utworzonego zamówienia',
            'order-creation-error' => 'Nie udało się utworzyć zamówienia',
            'cart-empty' => 'Koszyk jest pusty',
            'account-suspended' => 'Twoje konto zostało zawieszone. Skontaktuj się z pomocą techniczną.',
            'account-inactive' => 'Twoje konto jest nieaktywne. Skontaktuj się z pomocą techniczną.',
            'minimum-order-not-met' => 'Minimalna kwota zamówienia wynosi :amount',
            'guest-checkout-not-allowed' => 'Zaloguj się, aby złożyć zamówienie. Zakup jako gość nie jest dostępny dla tego koszyka.',
            'email-required' => 'Adres e-mail jest wymagany do utworzenia zamówienia',
            'unknown-operation' => 'Nieznana operacja realizacji zamówienia',
        ],

        'customer-addresses' => [
            'token-required' => 'Token jest wymagany do pobrania adresów klienta',
            'invalid-or-expired-token' => 'Nieprawidłowy lub wygasły token',
            'token-validation-failed' => 'Weryfikacja tokena nie powiodła się',
        ],

        'product' => [
            'not-found' => 'Produkt nie znaleziony',
            'not-found-with-sku' => 'Nie znaleziono produktu o podanym SKU',
            'not-found-with-url-key' => 'Nie znaleziono produktu o podanym kluczu URL',
            'parameters-required' => 'Należy podać co najmniej jeden z następujących parametrów: "sku", "id", "urlKey"',
            'type' => 'Typ produktu',
            'attribute-family' => 'Rodzina atrybutów',
            'sku' => 'SKU',
            'name' => 'Nazwa',
            'description' => 'Opis',
            'short-description' => 'Krótki opis',
            'status' => 'Status',
            'new' => 'Nowy',
            'featured' => 'Wyróżniony',
            'price' => 'Cena',
            'special-price' => 'Cena specjalna',
            'weight' => 'Waga',
            'cost' => 'Koszt',
            'length' => 'Długość',
            'width' => 'Szerokość',
            'height' => 'Wysokość',
            'color' => 'Kolor',
            'size' => 'Rozmiar',
            'brand' => 'Marka',
            'super-attributes' => 'Superatrybuty',
            'id-required' => 'Identyfikator produktu jest wymagany',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/products/1" lub identyfikator liczbowy',
        ],

        'downloadable-product' => [
            'download-link-not-found' => 'Link pobierania nie znaleziony lub wygasł',
            'purchased-link-not-found' => 'Zakupiony link nie znaleziony',
            'file-not-found' => 'Plik nie znaleziony',
            'download-successful' => 'Plik gotowy do pobrania',
            'token-required' => 'Wymagany jest token pobierania',
            'invalid-token' => 'Token pobierania jest nieprawidłowy lub wygasł',
            'token-expired' => 'Token pobierania wygasł. Proszę wygenerować nowy',
            'access-denied' => 'Dostęp odmówiony: Nie masz uprawnień do pobrania tego pliku',
            'redirect-external-url' => 'Przekierowanie do zewnętrznego adresu URL pobierania',
            'file-error' => 'Podczas przetwarzania żądania pobierania wystąpił błąd',
            'unauthorized-access' => 'Nieautoryzowany dostęp do zasobu pobierania',
            'unauthenticated' => 'Brak autoryzacji: wymagane jest uwierzytelnienie klienta',
            'link-id-required' => 'downloadableLinkPurchasedId jest wymagany',
            'link-not-found' => 'Link do pobrania nie znaleziony',
            'not-owner' => 'Brak autoryzacji: ten link do pobrania nie należy do Ciebie',
            'pending' => 'Pobieranie oczekuje. Poczekaj na wystawienie faktury dla zamówienia.',
            'limit-exceeded' => 'Przekroczono limit pobrań',
            'no-downloads-left' => 'Brak dostępnych pobrań',
        ],

        'booking-slot' => [
            'id-required' => 'Identyfikator produktu rezerwacji jest wymagany.',
            'date-required' => 'Data jest wymagana.',
            'product-not-found' => 'Produkt rezerwacji nie znaleziony.',
            'invalid-type' => 'Ten typ rezerwacji nie jest obsługiwany.',
            'invalid-time-range' => 'Nieprawidłowy przedział czasowy.',
            'invalid-time-format' => 'Nieprawidłowy format czasu.',
        ],

        'resolver' => [
            'no-resolve-info' => 'GraphQL ResolveInfo nie jest dostępne w kontekście. Nie można określić klasy modelu.',
            'unknown-resource-class' => 'Nie można określić klasy zasobu na podstawie typu zwracanego GraphQL',
        ],

        'docs' => [
            'openapi-unavailable' => 'Nie można utworzyć instancji OpenAPI Factory',
        ],

        'locale' => [
            'id-required' => 'Identyfikator ustawień regionalnych jest wymagany',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/locales/1" lub identyfikator liczbowy',
            'not-found' => 'Ustawienia regionalne nie znalezione',
        ],

        'category' => [
            'id-required' => 'Identyfikator kategorii jest wymagany',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/categories/1" lub identyfikator liczbowy',
            'not-found' => 'Kategoria nie znaleziona',
        ],

        'compare-item' => [
            'id-required' => 'Identyfikator przedmiotu porównawczego jest wymagany',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/compare-items/1" lub identyfikator liczbowy',
            'not-found' => 'Przedmiot porównawczy nie znaleziony',
            'product-id-required' => 'Identyfikator produktu jest wymagany',
            'customer-id-required' => 'Identyfikator klienta jest wymagany',
            'product-not-found' => 'Produkt nie znaleziony',
            'customer-not-found' => 'Klient nie znaleziony',
            'already-exists' => 'Ten produkt jest już na Twojej liście porównawczej',
            'delete-all-success' => 'Wszystkie przedmioty porównawcze zostały pomyślnie usunięte',
        ],

        'wishlist' => [
            'id-required' => 'Identyfikator pozycji listy życzeń jest wymagany',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/wishlists/1" lub identyfikator liczbowy',
            'not-found' => 'Pozycja listy życzeń nie znaleziona',
            'product-id-required' => 'Identyfikator produktu jest wymagany',
            'customer-id-required' => 'Identyfikator klienta jest wymagany',
            'product-not-found' => 'Produkt nie znaleziony',
            'product-disabled' => 'Ten produkt jest obecnie wyłączony',
            'customer-not-found' => 'Klient nie znaleziony',
            'already-exists' => 'Ten produkt jest już na Twojej liście życzeń',
            'added' => 'Pozycja pomyślnie dodana do listy życzeń',
            'removed' => 'Pozycja pomyślnie usunięta z listy życzeń',
            'product-removed' => 'Produkt został usunięty',
            'wishlist-item-id-required' => 'Identyfikator pozycji listy życzeń jest wymagany',
            'invalid-quantity' => 'Ilość musi być większa niż 0',
            'move-to-cart-missing-options' => 'W produkcie brakuje wymaganych opcji. Skonfiguruj go ręcznie',
            'moved-to-cart-success' => 'Pozycja pomyślnie przeniesiona do koszyka',
            'delete-all-success' => 'Wszystkie pozycje listy życzeń zostały pomyślnie usunięte',
        ],

        'gdpr' => [
            'disabled' => 'Żądania dotyczące danych RODO są wyłączone. Włącz RODO w konfiguracji panelu administracyjnego.',
            'unauthenticated' => 'Musisz być zalogowany, aby zarządzać żądaniami dotyczącymi danych RODO.',
            'not-found' => 'Żądanie dotyczące danych RODO nie znalezione',
            'type-required' => 'Typ żądania jest wymagany',
            'type-invalid' => 'Typ żądania musi mieć wartość "delete" lub "update"',
            'message-required' => 'Wiadomość jest wymagana',
            'raised' => 'Twoje żądanie dotyczące danych RODO zostało pomyślnie złożone.',
            'revoked' => 'Twoje żądanie dotyczące danych RODO zostało pomyślnie wycofane.',
            'revoke-not-allowed' => 'Tego żądania dotyczącego danych RODO nie można już wycofać.',
            'deleted' => 'Twoje żądanie dotyczące danych RODO zostało pomyślnie usunięte.',
        ],

        'section' => [
            'id-required' => 'Identyfikator sekcji jest wymagany',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/sections/1" lub identyfikator liczbowy',
            'not-found' => 'Sekcja nie znaleziona',
        ],

        'install' => [
            'starting' => 'Rozpoczynanie instalacji API Platform...',
            'completed-success' => '✓ Instalacja API Platform zakończona pomyślnie!',
            'failed' => '✗ Instalacja nie powiodła się: ',

            'provider-already-registered' => 'ℹ Service Provider jest już zarejestrowany',
            'provider-not-registered' => '⚠ Nie udało się automatycznie zarejestrować Service Provider. Dodaj :provider ręcznie do bootstrap/providers.php.',
            'provider-registered' => '✓ Service Provider zarejestrowany w bootstrap/providers.php',
            'provider-file-not-found' => 'Plik nie znaleziony: :file',
            'provider-permission-denied' => 'Odmowa dostępu: nie można zapisać do :file. Sprawdź uprawnienia pliku.',

            'config-already-exists' => 'ℹ Plik konfiguracyjny już istnieje',
            'config-published' => '✓ Konfiguracja API Platform opublikowana w config/',
            'config-source-not-found' => 'Źródłowy plik konfiguracyjny nie znaleziony: :file',
            'config-permission-denied' => 'Odmowa dostępu: nie można zapisać do :directory. Sprawdź uprawnienia katalogu.',

            'translatable-not-found' => 'ℹ TranslatableModel nie znaleziony - pomijanie',
            'translatable-already-abstract' => 'ℹ TranslatableModel jest już abstrakcyjny',
            'translatable-made-abstract' => '✓ TranslatableModel ustawiony jako abstrakcyjny',
            'translatable-permission-denied' => 'Odmowa dostępu: nie można zapisać do :file. Sprawdź uprawnienia pliku.',

            'providers-already-registered' => 'ℹ Dostawcy API Platform są już zarejestrowani',
            'providers-registered' => '✓ Dostawcy API Platform zarejestrowani w bootstrap/app.php',
            'providers-file-not-found' => 'Plik nie znaleziony: :file',
            'providers-permission-denied' => 'Odmowa dostępu: nie można zapisać do :file. Sprawdź uprawnienia pliku.',
            'providers-not-found' => 'Nie znaleziono tworzenia Application w bootstrap/app.php',

            'assets-published' => '✓ Zasoby pakietu opublikowane',
            'running-migrations' => 'Uruchamianie migracji bazy danych...',
            'migrations-completed' => '✓ Migracje bazy danych zakończone',
            'clearing-caches' => 'Czyszczenie i optymalizacja pamięci podręcznej...',
            'caches-optimized' => '✓ Pamięć podręczna wyczyszczona i zoptymalizowana',
            'generating-api-key' => 'Generowanie klucza API...',
            'api-key-generated' => '✓ Klucz API pomyślnie wygenerowany',
            'api-key-already-exists' => 'ℹ Klucz API domyślnego sklepu już istnieje - pomijanie generowania',

            'migrations-error' => 'Migracje bazy danych nie powiodły się.',
            'migrations-error-running' => 'Błąd podczas uruchamiania migracji bazy danych: :error',
            'cache-clear-error' => 'Czyszczenie pamięci podręcznej nie powiodło się.',
            'cache-optimize-error' => 'Optymalizacja nie powiodła się.',
            'cache-error' => 'Błąd podczas czyszczenia i optymalizacji pamięci podręcznej: :error',
            'api-key-generation-error' => 'Generowanie klucza API nie powiodło się.',
            'api-key-error' => 'Błąd podczas generowania klucza API: :error',

            'key-management-required' => '--key jest wymagany dla akcji :action',
            'key-rotation-error' => 'Błąd podczas rotacji klucza: :error',
            'key-deactivation-error' => 'Błąd podczas dezaktywacji klucza: :error',
            'key-not-found' => 'Klucz API nie znaleziony: :identifier',

            'maintenance-starting' => '🔄 Rozpoczynanie konserwacji kluczy API...',
            'maintenance-complete' => '✅ Konserwacja kluczy API zakończona',
            'cleanup-expired-keys' => '🧹 Czyszczenie wygasłych kluczy...',
            'cleanup-expired-none' => '   ℹ️ Brak wygasłych kluczy do wyczyszczenia',
            'invalidate-deprecated' => '⚠️ Unieważnianie przestarzałych kluczy...',
            'invalidate-deprecated-none' => '   ℹ️ Brak przestarzałych kluczy do unieważnienia',
            'notify-expiring' => '📧 Wysyłanie powiadomień o wygaśnięciu...',
            'notify-expiring-none' => '   ℹ️ Brak kluczy wymagających powiadomień',

            'key-rotated-success' => '✅ Klucz pomyślnie poddany rotacji!',
            'key-deactivated-success' => '✅ Klucz pomyślnie dezaktywowany!',
            'deactivation-cancelled' => 'Dezaktywacja anulowana.',
            'cleanup-cancelled' => 'Czyszczenie anulowane.',

            'key-status-title' => 'Status klucza: :name',
            'key-status-active' => 'Aktywny: :status',
            'key-status-usable' => 'Możliwy do użycia: :status',
            'key-status-expired' => 'Wygasły: :status',
            'key-status-deprecated' => 'Przestarzały: :status',
            'key-status-expires-at' => 'Wygasa: :expires',
            'key-status-days-until-expiry' => 'Dni do wygaśnięcia: :days',
            'key-status-last-used' => 'Ostatnio używany: :date',
            'key-status-rotated-from' => 'Rotacja z klucza: :key',
            'key-status-rotated-keys' => 'Klucze utworzone przez rotację z tego klucza: :count',

            'policy-compliance-summary' => 'Podsumowanie zgodności z polityką rotacji kluczy API',
            'available-actions' => 'Dostępne akcje: rotate, deactivate, cleanup, status, expiring, unused, summary',

            'asset-linked-success' => '✓ Zasoby API Platform pomyślnie podlinkowane',
            'symlink-create-failed' => 'Nie udało się utworzyć dowiązania symbolicznego, zamiast tego kopiowanie zasobów...',
            'asset-copy-warning' => 'Ostrzeżenie: nie udało się podlinkować ani skopiować zasobów API Platform. Może być wymagana ręczna konfiguracja.',
            'asset-copied-success' => '✓ Zasoby API Platform pomyślnie skopiowane',

            'key-name-required' => 'Nazwa klucza sklepu nie może być pusta.',
            'key-generated-success' => 'Klucz sklepu pomyślnie wygenerowany!',
            'key-details' => 'Szczegóły klucza:',
            'key-secure-warning' => 'Przechowuj ten klucz bezpiecznie! Będzie używany w nagłówku X-STOREFRONT-KEY.',
            'key-share-warning' => 'Nie udostępniaj tego klucza publicznie ani nie zapisuj go w systemie kontroli wersji.',

            'key-already-exists' => 'Klucz sklepu o nazwie \':name\' już istnieje.',
            'cleanup-success' => '✅ Wyczyszczono wygasłe klucze: :count',
            'confirm-cleanup' => 'Spowoduje to miękkie usunięcie wszystkich wygasłych kluczy. Kontynuować?',
            'confirm-deactivate' => 'Czy na pewno chcesz dezaktywować klucz: :name?',

            'old-key' => 'Stary klucz: :name',
            'old-key-id' => 'Identyfikator starego klucza: :id',
            'deprecation-date' => 'Data wycofania: :date',
            'new-key' => 'Nowy klucz: :name',
            'new-key-id' => 'Identyfikator nowego klucza: :id',
            'new-key-value' => 'Wartość nowego klucza: :key',
            'expires-at' => 'Wygasa: :date',
            'invalid-action' => 'Nieprawidłowa akcja: :action',
            'key-active' => 'Aktywny: :status',
            'key-usable' => 'Możliwy do użycia: :status',
            'key-expired' => 'Wygasły: :status',
            'key-deprecated' => 'Przestarzały: :status',
            'key-field-id' => 'ID',
            'key-field-name' => 'Nazwa',
            'key-field-key' => 'Klucz',
            'key-field-rate-limit' => 'Limit żądań',
            'key-field-status' => 'Status',
            'key-days' => ' dni',
            'key-never' => 'Nigdy',
            'key-requests-minute' => ' żądań/minutę',
            'key-unlimited' => 'Bez limitu',
            'rate-limit-exceeded' => 'Żądany limit przekracza maksymalną dozwoloną wartość :max. Ustawiono :max żądań/minutę.',

            'vendor-path-not-found' => 'Nie znaleziono ścieżki vendor API Platform: :path',
            'assets-already-linked' => 'Zasoby API Platform są już podlinkowane w: :path',
            'no-keys-expiring' => 'Brak kluczy wygasających w ciągu najbliższych :days dni',
            'keys-expiring-title' => 'Klucze wygasające w ciągu najbliższych :days dni:',
            'key-display-format' => '• :name (ID: :id)',
            'key-expires-display' => '  Wygasa: :date (pozostało dni: :days)',
            'no-unused-keys' => 'Nie znaleziono nieużywanych kluczy (> :days dni)',
            'unused-keys-title' => 'Nieużywane klucze (> :days dni):',
            'key-last-used-display' => '  Ostatnio używany: :date',
            'total-keys' => 'Łącznie kluczy: :count',
            'valid-keys' => 'Prawidłowe klucze: :count',
            'expired-keys' => 'Wygasłe klucze: :count',
            'deprecated-keys' => 'Przestarzałe klucze: :count',
            'keys-expiring-soon' => 'Klucze wkrótce wygasające (7 dni): :count',
            'unused-keys-summary' => 'Nieużywane klucze (90 dni): :count',
            'recently-rotated' => 'Niedawno poddane rotacji (30 dni): :count',
            'cleanup-success-message' => '   ✅ Wyczyszczono wygasłe klucze: :count',
            'invalidate-success-message' => '   ✅ Unieważniono przestarzałe klucze: :count',
            'notify-success-message' => '   ✅ Wysłano powiadomienia o wygaśnięciu: :count',
            'notify-failed-message' => '   ⚠️ Nie udało się wysłać powiadomienia o :key: :error',
            'publish-assets-warning' => 'Ostrzeżenie: nie udało się opublikować zasobów pakietu. :error',
            'env-file-not-found' => 'Nie znaleziono pliku .env. Utwórz go przed instalacją.',
            'env-permission-denied' => 'Nie można zapisać pliku .env. Sprawdź jego uprawnienia.',
            'env-config-saved' => '✓ Konfiguracja środowiska API Platform pomyślnie zapisana',
            'api-endpoints' => 'Punkty końcowe API:',
            'api-documentation' => 'Dokumentacja API: :url',
            'api-landing-page' => 'Strona startowa API: :url',
            'graphql-playground' => 'GraphQL Playground: :url',
            'rest-api-storefront' => 'Rest API Storefront Swagger: :url',
            'rest-api-admin' => 'Rest API Admin Swagger: :url',
            'generating-jwt-keys' => 'Generowanie kluczy podpisu JWT dla tokenów API administratora...',
            'jwt-keys-generated' => '✓ Klucze podpisu JWT są gotowe',
            'jwt-keys-warning' => 'Ostrzeżenie: nie udało się wygenerować kluczy podpisu JWT. Uruchom ręcznie php artisan bagisto-api:jwt-keys. :error',
            'admin-login' => 'Logowanie do API administratora: :url',
            'admin-api-tokens' => 'Tokeny API administratora (integracje serwerowe): :url',
            'key-rotation-invalid-key' => 'Nie można poddać rotacji nieprawidłowego lub wygasłego klucza',
            'env-file-not-found' => 'Nie znaleziono pliku .env',
            'env-permission-denied' => 'Odmowa dostępu: nie można zapisać pliku .env. Sprawdź uprawnienia pliku.',
        ],

        'attribute-translation' => [
            'id-required' => 'Identyfikator tłumaczenia atrybutu jest wymagany',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/attribute-translations/1" lub identyfikator liczbowy',
            'not-found' => 'Nie znaleziono tłumaczenia atrybutu',
        ],

        'currency' => [
            'id-required' => 'Identyfikator waluty jest wymagany',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/currencies/1" lub identyfikator liczbowy',
            'not-found' => 'Nie znaleziono waluty',
        ],

        'customer-address' => [
            'id-required' => 'Identyfikator adresu klienta jest wymagany',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/customer-addresses/1" lub identyfikator liczbowy',
        ],

        'customer-order-shipment-item' => [
            'id-required' => 'Identyfikator pozycji przesyłki jest wymagany',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/customer-order-shipment-items/1" lub identyfikator liczbowy',
            'not-found' => 'Nie znaleziono pozycji przesyłki',
        ],

        'page' => [
            'id-required' => 'Identyfikator strony jest wymagany',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/pages/1" lub identyfikator liczbowy',
            'not-found' => 'Nie znaleziono strony',
        ],

        'page-translation' => [
            'id-required' => 'Identyfikator tłumaczenia strony jest wymagany',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/page-translations/1" lub identyfikator liczbowy',
            'not-found' => 'Nie znaleziono tłumaczenia strony',
        ],

        'product-bundle-option' => [
            'id-required' => 'Identyfikator opcji pakietu jest wymagany',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/product-bundle-options/1" lub identyfikator liczbowy',
            'not-found' => 'Nie znaleziono opcji pakietu',
        ],

        'product-bundle-option-product' => [
            'id-required' => 'Identyfikator produktu opcji pakietu jest wymagany',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/product-bundle-option-products/1" lub identyfikator liczbowy',
            'not-found' => 'Nie znaleziono produktu opcji pakietu',
        ],

        'product-downloadable-link' => [
            'id-required' => 'Identyfikator linku do pobrania jest wymagany',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/product-downloadable-links/1" lub identyfikator liczbowy',
            'not-found' => 'Nie znaleziono linku do pobrania',
        ],

        'product-downloadable-sample' => [
            'id-required' => 'Identyfikator próbki do pobrania jest wymagany',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/product-downloadable-samples/1" lub identyfikator liczbowy',
            'not-found' => 'Nie znaleziono próbki do pobrania',
        ],

        'product-grouped-product' => [
            'id-required' => 'Identyfikator produktu grupowego jest wymagany',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/product-grouped-products/1" lub identyfikator liczbowy',
            'not-found' => 'Nie znaleziono produktu grupowego',
        ],

        'product-images' => [
            'id-required' => 'Identyfikator zdjęcia produktu jest wymagany',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/product-images/1" lub identyfikator liczbowy',
            'not-found' => 'Nie znaleziono zdjęcia produktu',
        ],

        'product-videos' => [
            'id-required' => 'Identyfikator filmu produktu jest wymagany',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/product-videos/1" lub identyfikator liczbowy',
            'not-found' => 'Nie znaleziono filmu produktu',
        ],

        'theme-customization' => [
            'id-required' => 'Identyfikator dostosowania motywu jest wymagany',
            'invalid-id-format' => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/theme-customizations/1" lub identyfikator liczbowy',
            'not-found' => 'Nie znaleziono dostosowania motywu',
        ],

        'unknown-resource' => [
            'id-required' => 'Identyfikator zasobu jest wymagany',
            'not-found' => 'Nie znaleziono zasobu',
        ],
    ],

    'admin' => [
        'profile' => [
            'unauthenticated' => 'Nie jesteś uwierzytelniony.',
        ],

        'dashboard' => [
            'invalid-type' => 'Nieznany typ statystyki pulpitu. Prawidłowe typy: over-all, today, stock-threshold-products, total-sales, top-selling-products, top-customers.',
        ],

        'rma' => [
            'not-found' => 'Zgłoszenie zwrotu nie znalezione.',
            'invalid-input' => 'Brakujące lub nieprawidłowe dane zwrotu.',
            'invalid-order' => 'Wybrane zamówienie jest nieprawidłowe.',
            'invalid-item' => 'Wybrana pozycja nie kwalifikuje się do zwrotu.',
            'invalid-resolution-type' => 'Typ rozwiązania musi mieć wartość "return" lub "cancel_items".',
            'qty-exceeds' => 'Żądana ilość przekracza ilość możliwą do zwrotu.',
            'created' => 'Zgłoszenie zwrotu pomyślnie utworzone.',
            'status-updated' => 'Status zwrotu pomyślnie zaktualizowany.',
            'invalid-status' => 'Nieprawidłowy status zwrotu.',
            'reopened' => 'Zgłoszenie zwrotu pomyślnie otwarte ponownie.',
            'reopen-not-allowed' => 'Tego zgłoszenia zwrotu nie można ponownie otworzyć.',
            'refund-failed' => 'Nie udało się utworzyć zwrotu środków dla tego zwrotu.',
            'message-sent' => 'Wiadomość pomyślnie wysłana.',
            'no-permission' => 'Nie masz uprawnień do wykonania tej akcji.',
            'reason-not-found' => 'Powód zwrotu nie znaleziony.',
            'reason-deleted' => 'Powód zwrotu pomyślnie usunięty.',
            'status-not-found' => 'Status zwrotu nie znaleziony.',
            'status-deleted' => 'Status zwrotu pomyślnie usunięty.',
            'status-default-undeletable' => 'Nie można usunąć domyślnego statusu zwrotu.',
            'rule-not-found' => 'Reguła zwrotu nie znaleziona.',
            'rule-deleted' => 'Reguła zwrotu pomyślnie usunięta.',
            'custom-field-not-found' => 'Pole niestandardowe zwrotu nie znalezione.',
            'custom-field-deleted' => 'Pole niestandardowe zwrotu pomyślnie usunięte.',
            'custom-field-options-required' => 'Dla tego typu pola wymagana jest co najmniej jedna opcja.',
            'mass-indices-required' => 'Wymagany jest co najmniej jeden identyfikator.',
            'mass-delete-success' => 'Wybrane pozycje usunięte.',
            'mass-update-success' => 'Wybrane pozycje zaktualizowane.',
            'mass-value-invalid' => 'Wartość statusu musi wynosić 0 lub 1.',
            'image-not-found' => 'Obraz zwrotu nie znaleziony.',
            'attachment-not-found' => 'Załącznik wiadomości nie znaleziony.',
        ],

        'eu-withdrawal' => [
            'not-found' => 'Oświadczenie o odstąpieniu nie znalezione.',
            'no-permission' => 'Nie masz uprawnień do zarządzania odstąpieniami UE.',
            'declined' => 'Odstąpienie oznaczone jako odrzucone.',
            'refunded' => 'Odstąpienie oznaczone jako zwrócone.',
            'confirmation-resent' => 'E-mail z potwierdzeniem pomyślnie wysłany ponownie.',
            'confirmation-failed' => 'Nie udało się wysłać e-maila z potwierdzeniem.',
        ],

        'reporting' => [
            'invalid-entity' => 'Nieznana encja raportowania.',
            'invalid-type' => 'Nieznany typ statystyki raportowania dla tej encji.',
            'export-format-unsupported' => 'Obsługiwane są tylko formaty eksportu csv, xls i xlsx.',
        ],

        'configuration' => [
            'unauthenticated' => 'Nie jesteś uwierzytelniony.',
            'no-permission' => 'Nie masz uprawnień do aktualizacji konfiguracji.',
            'slug-required' => 'Parametr zapytania `slug` jest wymagany.',
            'slug-not-found' => 'Dla podanego sluga nie zarejestrowano żadnej sekcji konfiguracji.',
            'values-required' => 'W `values` wymagana jest co najmniej jedna wartość.',
            'scope-escape' => 'Klucz `:key` znajduje się poza poddrzewem żądanego sluga.',
            'unknown-field' => 'Klucz `:key` nie jest zarejestrowanym polem konfiguracji.',
            'validation-failed' => 'Co najmniej jedna wartość konfiguracji nie przeszła walidacji.',
            'file-upload-rest-only' => 'Przesyłanie plików binarnych dla pola `:field` jest obsługiwane tylko przez punkt końcowy REST multipart, a nie przez GraphQL.',
            'custom-view-readonly' => 'Pole `:field` jest niestandardowym polem renderowanym przez Blade i nie może być aktualizowane przez API.',
            'update-success' => 'Konfiguracja pomyślnie zaktualizowana.',
        ],

        'customer' => [
            'not-found' => 'Klient nie znaleziony.',
            'no-permission' => 'Nie masz uprawnień do wykonania tej akcji.',
            'has-active-orders' => 'Klient ma zamówienia oczekujące lub w trakcie realizacji i nie może zostać usunięty.',
            'deleted' => 'Klient pomyślnie usunięty.',
            'mass-delete-indices-required' => 'Wymagany jest co najmniej jeden identyfikator klienta.',
            'mass-delete-success' => 'Klienci usunięci.',
            'mass-update-indices-required' => 'Wymagany jest co najmniej jeden identyfikator klienta.',
            'mass-update-value-invalid' => 'Wartość statusu musi wynosić 0 lub 1.',
            'mass-update-success' => 'Status klientów zaktualizowany.',

            'address' => [
                'not-found' => 'Adres nie znaleziony.',
                'not-owned' => 'Ten adres nie należy do wskazanego klienta.',
                'customer-id-required' => 'customerId jest wymagany do identyfikacji właściciela adresu.',
                'deleted' => 'Adres pomyślnie usunięty.',
            ],

            'note' => [
                'empty' => 'Treść notatki jest wymagana.',
                'created' => 'Notatka pomyślnie dodana.',
            ],

            'review' => [
                'not-found' => 'Recenzja nie znaleziona.',
                'no-permission' => 'Nie masz uprawnień do wykonania tej akcji.',
                'invalid-status' => 'Status musi mieć jedną z wartości: pending, approved, disapproved.',
                'updated' => 'Recenzja pomyślnie zaktualizowana.',
                'deleted' => 'Recenzja pomyślnie usunięta.',
                'mass-delete-indices-required' => 'Wymagany jest co najmniej jeden identyfikator recenzji.',
                'mass-delete-success' => 'Recenzje usunięte.',
                'mass-update-indices-required' => 'Wymagany jest co najmniej jeden identyfikator recenzji.',
                'mass-update-value-invalid' => 'Wartość statusu musi mieć jedną z wartości: pending, approved, disapproved.',
                'mass-update-success' => 'Status recenzji zaktualizowany.',
            ],

            'group' => [
                'not-found' => 'Grupa klientów nie znaleziona.',
                'deleted' => 'Grupa klientów pomyślnie usunięta.',
                'is-system' => 'Systemowych grup klientów nie można usunąć.',
                'has-customers' => 'Do grupy klientów są przypisani klienci, więc nie można jej usunąć.',
                'system-code-immutable' => 'Nie można zmienić kodu systemowej grupy klientów.',
                'system-flag-immutable' => 'Nie można zmienić flagi is_user_defined systemowej grupy klientów.',
                'mass-delete-indices-required' => 'Wymagany jest co najmniej jeden identyfikator grupy klientów.',
                'mass-delete-success' => 'Grupy klientów usunięte.',
            ],

            'gdpr' => [
                'not-found' => 'Żądanie RODO nie znalezione.',
                'no-permission' => 'Nie masz uprawnień do wykonania tej akcji.',
                'deleted' => 'Żądanie RODO pomyślnie usunięte.',
                'invalid-status' => 'Status musi mieć jedną z wartości: pending, processing, declined, approved, revoked.',
                'no-changes' => 'Wymagany jest co najmniej status lub wiadomość.',
                'already-processed' => 'To żądanie RODO zostało już przetworzone i nie może zostać uruchomione ponownie.',

                'process' => [
                    'approved' => 'Żądanie RODO zatwierdzone.',
                    'deleted' => 'Żądanie RODO zatwierdzone, a klient usunięty.',
                ],
            ],
        ],

        'order' => [
            'not-found' => 'Zamówienie nie znalezione.',

            'reorder' => [
                'success' => 'Ponowne zamówienie zakończone pomyślnie. Utworzono nowy roboczy koszyk.',
                'cannot-reorder' => 'Tego zamówienia nie można zamówić ponownie (zamówienie gościa lub co najmniej jedna pozycja nie jest już dostępna w sprzedaży).',
                'failed' => 'Ponowne zamówienie nie powiodło się. Spróbuj ponownie.',
                'guest-not-supported' => 'Ponowne zamówienie nie jest obsługiwane dla zamówień gości.',
                'items-not-saleable' => 'Co najmniej jedna pozycja w tym zamówieniu nie jest już dostępna do zakupu.',
                'no-permission' => 'Nie masz uprawnień do tworzenia zamówień.',
                'disabled-in-settings' => 'Ponowne zamówienie przez administratora jest wyłączone w ustawieniach sklepu.',
            ],

            'actions' => [
                'cancel' => [
                    'closed' => 'Zamkniętych zamówień nie można anulować.',
                    'fraud' => 'Zamówień oznaczonych jako oszustwo nie można anulować.',
                    'nothing-to-cancel' => 'W tym zamówieniu nie ma nic do anulowania.',
                    'already-processed' => 'To zamówienie zostało już zafakturowane lub wysłane i nie można go już anulować. Zamiast tego możesz wystawić zwrot środków.',
                    'no-permission' => 'Nie masz uprawnień do anulowania zamówień.',
                    'success' => 'Zamówienie pomyślnie anulowane.',
                    'failed' => 'Nie udało się anulować zamówienia.',
                ],

                'comment' => [
                    'empty' => 'Komentarz jest wymagany.',
                    'success' => 'Komentarz dodany do zamówienia.',
                    'failed' => 'Nie udało się dodać komentarza.',
                ],

                'invoice' => [
                    'closed' => 'Dla zamkniętych zamówień nie można wystawić faktury.',
                    'fraud' => 'Dla zamówień oznaczonych jako oszustwo nie można wystawić faktury.',
                    'nothing-to-invoice' => 'W tym zamówieniu nie ma nic do zafakturowania.',
                    'already-invoiced' => 'Dla tego zamówienia wygenerowano już fakturę.',
                    'paypal-standard-blocked' => 'Nie można tworzyć faktur dla zamówień opłaconych przez PayPal Standard.',
                    'no-permission' => 'Nie masz uprawnień do tworzenia faktur.',
                    'items-required' => 'Wymagana jest co najmniej jedna pozycja z dodatnią ilością.',
                    'qty-exceeds' => 'Żądana ilość (:requested) przekracza dostępną ilość (:available) dla SKU :sku.',
                    'not-found' => 'Faktura nie znaleziona.',
                    'success' => 'Faktura pomyślnie utworzona.',
                    'failed' => 'Nie udało się utworzyć faktury.',
                    'pdf-failed' => 'Nie udało się wygenerować pliku PDF faktury.',
                    'email-invalid' => 'Wymagany jest prawidłowy adres e-mail odbiorcy.',
                    'duplicate-sent' => 'E-mail z fakturą wysłany na adres :email.',
                    'mass-status-indices-required' => 'Wymagany jest co najmniej jeden identyfikator faktury.',
                    'mass-status-value-invalid' => 'Status musi mieć jedną z wartości: pending, paid, overdue.',
                    'mass-status-success' => 'Status faktury pomyślnie zaktualizowany.',
                ],

                'shipment' => [
                    'closed' => 'Zamkniętych zamówień nie można wysłać.',
                    'fraud' => 'Zamówień oznaczonych jako oszustwo nie można wysłać.',
                    'nothing-to-ship' => 'W tym zamówieniu nie ma nic do wysłania.',
                    'no-permission' => 'Nie masz uprawnień do wysyłania zamówień.',
                    'source-required' => 'Źródło magazynowe jest wymagane.',
                    'items-required' => 'Wymagana jest co najmniej jedna pozycja z dodatnią ilością.',
                    'qty-exceeds' => 'Żądana ilość (:requested) przekracza dostępną ilość (:available) dla SKU :sku.',
                    'inventory-insufficient' => 'Stan magazynowy w wybranym źródle jest niewystarczający dla SKU :sku.',
                    'not-found' => 'Przesyłka nie znaleziona.',
                    'success' => 'Przesyłka pomyślnie utworzona.',
                    'failed' => 'Nie udało się utworzyć przesyłki.',
                ],

                'refund' => [
                    'closed' => 'Dla zamkniętych zamówień nie można dokonać zwrotu środków.',
                    'fraud' => 'Dla zamówień oznaczonych jako oszustwo nie można dokonać zwrotu środków.',
                    'nothing-to-refund' => 'W tym zamówieniu nie pozostało nic do zwrotu.',
                    'no-permission' => 'Nie masz uprawnień do tworzenia zwrotów środków.',
                    'qty-exceeds' => 'Żądana ilość (:requested) przekracza dostępną ilość (:available) dla SKU :sku.',
                    'amount-zero' => 'Obliczona kwota zwrotu wynosi zero. Dostosuj ilość, koszt wysyłki lub wartości korekty.',
                    'amount-exceeds-max' => 'Kwota zwrotu (:amount) przekracza maksymalną kwotę możliwą do zwrotu (:max).',
                    'not-found' => 'Zwrot środków nie znaleziony.',
                    'success' => 'Zwrot środków pomyślnie utworzony.',
                    'failed' => 'Nie udało się utworzyć zwrotu środków.',
                ],
            ],
        ],

        'appearance' => [
            'no-permission' => 'Nie masz uprawnień do zarządzania wyglądem.',

            'theme' => [
                'not-found' => 'Motyw nie znaleziony.',
                'not-installed' => 'Ten motyw nie jest zainstalowany.',
                'channel-ids-required' => 'Wymagany jest co najmniej jeden kanał.',
                'unknown-channel' => 'Co najmniej jeden kanał nie istnieje.',
                'activated' => 'Motyw pomyślnie aktywowany.',
            ],

            'section' => [
                'not-found' => 'Sekcja nie znaleziona.',
                'invalid-payload' => 'Nieobsługiwane dane sekcji.',
                'options-required' => 'Opcje są wymagane.',
                'status-required' => 'Status jest wymagany.',
                'section-ids-required' => 'Wymagana jest co najmniej jedna sekcja.',
                'reorder-single-scope' => 'Kolejność sekcji można zmieniać jednocześnie tylko w obrębie jednego motywu i kanału.',
                'reorder-incomplete' => 'Zmiana kolejności wymaga wszystkich sekcji motywu i kanału, w kolejności, w jakiej mają być renderowane.',
                'unknown-section' => 'Co najmniej jedna sekcja nie istnieje.',
                'file-required' => 'Plik jest wymagany.',
                'created' => 'Sekcja pomyślnie utworzona.',
                'updated' => 'Sekcja pomyślnie zaktualizowana.',
                'deleted' => 'Sekcja pomyślnie usunięta.',
                'draft-saved' => 'Zmiany sekcji pomyślnie przygotowane.',
                'status-staged' => 'Status sekcji pomyślnie przygotowany.',
                'order-staged' => 'Kolejność sekcji pomyślnie przygotowana.',
                'duplicated' => 'Sekcja pomyślnie skopiowana.',
                'published' => 'Zmiany sekcji pomyślnie opublikowane.',
                'discarded' => 'Zmiany sekcji pomyślnie odrzucone.',
                'media-uploaded' => 'Multimedia pomyślnie przesłane.',
            ],
        ],

        'sales' => [
            'no-permission' => 'Nie masz uprawnień do wyświetlania tego zasobu sprzedaży.',

            'invoice' => [
                'not-found' => 'Faktura nie znaleziona.',
                'no-permission' => 'Nie masz uprawnień do wyświetlania faktur.',
            ],

            'shipment' => [
                'not-found' => 'Przesyłka nie znaleziona.',
                'no-permission' => 'Nie masz uprawnień do wyświetlania przesyłek.',
            ],

            'refund' => [
                'not-found' => 'Zwrot środków nie znaleziony.',
                'no-permission' => 'Nie masz uprawnień do wyświetlania zwrotów środków.',
                'export-format-unsupported' => 'Obsługiwane są tylko eksporty csv, xls i xlsx.',
            ],

            'transaction' => [
                'not-found' => 'Transakcja nie znaleziona.',
                'no-permission' => 'Nie masz uprawnień do wyświetlania transakcji.',

                'create' => [
                    'invoice-required' => 'Pole invoiceId jest wymagane.',
                    'payment-method-required' => 'Pole paymentMethod jest wymagane.',
                    'amount-invalid' => 'Pole amount jest wymagane i musi być liczbą.',
                    'invoice-missing' => 'Nie znaleziono wskazanej faktury.',
                    'already-paid' => 'Ta faktura jest już w pełni opłacona.',
                    'amount-exceeds' => 'Kwota płatności przekracza łączną kwotę faktury.',
                    'amount-zero' => 'Kwota płatności musi być większa od zera.',
                ],
            ],

            'booking' => [
                'not-found' => 'Rezerwacja nie znaleziona.',
                'no-permission' => 'Nie masz uprawnień do wyświetlania rezerwacji.',
            ],

            'export' => [
                'format-unsupported' => 'Obsługiwane są tylko eksporty csv, xls i xlsx.',
            ],
        ],

        'cart' => [
            'not-found' => 'Koszyk nie znaleziony.',
            'not-draft' => 'Tego koszyka nie można modyfikować przez API administratora.',
            'product-required' => 'productId jest wymagany.',
            'product-not-found' => 'Produkt nie znaleziony.',
            'booking-unsupported' => 'Produktów rezerwacji nie można dodawać do roboczego zamówienia administratora. Zakupy rezerwacji muszą być dokonywane w sklepie klienta.',
            'product-not-saleable' => 'Ten produkt nie jest dostępny w sprzedaży (brak w magazynie lub wyłączony) i nie może zostać dodany do zamówienia.',
            'qty-required' => 'qty jest wymagane i musi być niepustym obiektem itemId => ilość.',
            'cart-item-required' => 'cartItemId jest wymagany.',
            'item-added' => 'Pozycja dodana do koszyka.',
            'item-add-failed' => 'Nie udało się dodać pozycji do koszyka.',
            'item-updated' => 'Pozycje koszyka zaktualizowane.',
            'item-update-failed' => 'Nie udało się zaktualizować pozycji koszyka.',
            'item-removed' => 'Pozycja usunięta z koszyka.',
            'item-remove-failed' => 'Nie udało się usunąć pozycji koszyka.',
            'address-saved' => 'Adres zapisany.',
            'address-required' => 'Adres rozliczeniowy jest wymagany.',
            'address-incomplete' => 'Pole :field jest wymagane do zapisania adresu.',
            'coupon-code-required' => 'Kod kuponu jest wymagany.',
            'coupon-applied' => 'Kupon zastosowany.',
            'coupon-already-applied' => 'Kupon jest już zastosowany do tego koszyka.',
            'coupon-not-found' => 'Kupon nie znaleziony lub nieaktywny.',
            'coupon-removed' => 'Kupon usunięty.',
            'coupon-error' => 'Coś poszło nie tak podczas stosowania kuponu.',
            'unknown-error' => 'Coś poszło nie tak.',
            'draft-created' => 'Roboczy koszyk utworzony.',
            'draft-failed' => 'Nie udało się utworzyć roboczego koszyka.',
            'addresses-required-for-shipping' => 'Przed wyborem metody wysyłki należy zapisać adresy.',
            'shipping-required-for-payment' => 'Metodę wysyłki należy wybrać przed metodą płatności.',
            'shipping-method-required' => 'Metoda wysyłki jest wymagana.',
            'shipping-method-saved' => 'Metoda wysyłki zapisana.',
            'shipping-method-failed' => 'Nie udało się zapisać metody wysyłki.',
            'payment-method-required' => 'Metoda płatności jest wymagana.',
            'payment-method-saved' => 'Metoda płatności zapisana.',
            'payment-method-failed' => 'Nie udało się zapisać metody płatności.',

            'place-order' => [
                'success' => 'Zamówienie pomyślnie złożone.',
                'failed' => 'Nie udało się złożyć zamówienia.',
                'error' => 'Nie udało się złożyć zamówienia: koszyk zawiera błędy.',
                'minimum-order-error' => 'Minimalna kwota zamówienia wynosi :amount. Dodaj więcej pozycji przed złożeniem zamówienia.',
                'empty-cart' => 'Koszyk jest pusty — dodaj pozycje przed złożeniem zamówienia.',
                'addresses-required' => 'Przed złożeniem zamówienia należy zapisać adres rozliczeniowy i adres wysyłki.',
                'shipping-required' => 'Przed złożeniem zamówienia należy wybrać metodę wysyłki.',
                'payment-required' => 'Przed złożeniem zamówienia należy wybrać metodę płatności.',
                'payment-method-unsupported' => 'Dla zamówień składanych przez administratora obsługiwane są tylko cashondelivery i moneytransfer.',
            ],
        ],

        'product' => [
            'not-found' => 'Produkt nie znaleziony.',

            'mass-delete-success' => 'Produkty pomyślnie usunięte.',
            'mass-update-status-success' => 'Status produktów pomyślnie zaktualizowany.',
            'indices-required' => 'Pole indices jest wymagane i musi być niepustą tablicą.',
            'indices-invalid' => 'Pole indices musi być tablicą liczbowych identyfikatorów produktów.',
            'value-invalid' => 'Pole value jest wymagane i musi mieć wartość 0 lub 1.',
            'mass-delete-failed' => 'Nie udało się usunąć co najmniej jednego produktu.',
            'no-permission' => 'Nie masz uprawnień do zarządzania produktami.',

            'copy-success' => 'Produkt pomyślnie skopiowany.',
            'copy-failed' => 'Nie udało się skopiować produktu.',
            'copy-variant-not-supported' => 'Nie można kopiować wariantów produktów konfigurowalnych. Zamiast tego skopiuj nadrzędny produkt konfigurowalny.',

            'create' => [
                'created' => 'Produkt pomyślnie utworzony.',
                'create-failed' => 'Nie udało się utworzyć produktu.',
                'sku-required' => 'Pole sku jest wymagane.',
                'sku-unique' => 'Podane sku jest już zajęte.',
                'sku-invalid' => 'Pole sku może zawierać tylko litery, cyfry i myślniki (np. "sp-001").',
                'attribute-family-required' => 'Pole attribute_family_id jest wymagane.',
                'attribute-family-not-found' => 'Wybrana rodzina atrybutów nie istnieje.',
                'type-required' => 'Pole type jest wymagane.',
                'type-not-yet-supported' => 'Typ produktu ":type" nie jest obsługiwany przez to API. Dozwolone typy: simple, virtual, downloadable, grouped, bundle, configurable, booking.',
                'super-attributes-required' => 'Pole super_attributes jest wymagane, gdy type=configurable. Podaj mapę kodu atrybutu (lub id) do option_ids, np. { "color": [1,2], "size": [4,5] }.',
                'super-attributes-invalid' => 'Pole super_attributes musi być niepustą mapą kodu atrybutu (lub id) do niepustej listy option_ids.',
                'no-permission' => 'Nie masz uprawnień do tworzenia produktów.',
            ],

            'update' => [
                'updated' => 'Produkt pomyślnie zaktualizowany.',
                'update-failed' => 'Nie udało się zaktualizować produktu.',
                'id-required' => 'Identyfikator produktu jest wymagany.',
                'url-key-required' => 'Pole url_key nie może być puste.',
                'url-key-unique' => 'Podany url_key jest już zajęty przez inny produkt.',
                'special-price-invalid' => 'Pole special_price musi być liczbą mniejszą niż price.',
                'special-price-date-range-invalid' => 'Data special_price_to musi być taka sama lub późniejsza niż special_price_from.',
                'boolean-field-invalid' => 'Pole :field musi mieć wartość 0 lub 1.',
                'categories-invalid' => 'Pole categories musi być tablicą identyfikatorów kategorii.',
                'channels-invalid' => 'Pole channels musi być tablicą identyfikatorów kanałów.',
                'super-attributes-cannot-change' => 'Nie można zmienić super_attributes po utworzeniu produktu.',
                'sub-resource-stripped-images' => 'Obrazami należy zarządzać przez POST /api/admin/catalog/products/{id}/images. Pole images zostało zignorowane.',
                'sub-resource-stripped-inventories' => 'Stanami magazynowymi należy zarządzać przez PUT /api/admin/catalog/products/{id}/inventories. Pole inventories zostało zignorowane.',
                'sub-resource-stripped-customer-group-prices' => 'Cenami dla grup klientów należy zarządzać przez /api/admin/catalog/products/{id}/customer-group-prices. Pole customer_group_prices zostało zignorowane.',
                'translations-single-locale' => 'Zaktualizowano tylko żądane ustawienia regionalne. Aby zaktualizować ustawienia regionalne :locales, wyślij osobne żądanie z ?locale=<code> dla każdego z nich.',
            ],

            'delete' => [
                'deleted' => 'Produkt pomyślnie usunięty.',
                'delete-failed' => 'Nie udało się usunąć produktu.',
            ],

            'image' => [
                'uploaded' => 'Obraz produktu pomyślnie przesłany.',
                'updated' => 'Obraz produktu pomyślnie zaktualizowany.',
                'update-empty' => 'Prześlij tekst alternatywny lub pozycję do aktualizacji.',
                'reordered' => 'Kolejność obrazów produktu pomyślnie zmieniona.',
                'deleted' => 'Obraz produktu pomyślnie usunięty.',
                'image-required' => 'Wymagany jest plik obrazu (pole multipart "image").',
                'image-invalid-type' => 'Typ obrazu musi być jednym z: bmp, jpeg, jpg, png, webp.',
                'image-too-large' => 'Obraz jest zbyt duży. Maksymalny dozwolony rozmiar to 4 MB.',
                'not-found' => 'Obraz produktu nie znaleziony.',
                'no-permission' => 'Nie masz uprawnień do zarządzania obrazami produktów.',
                'upload-failed' => 'Nie udało się przesłać obrazu produktu.',
                'reorder-failed' => 'Nie udało się zmienić kolejności obrazów produktu.',
                'delete-failed' => 'Nie udało się usunąć obrazu produktu.',
                'order-required' => 'Tablica "order" jest wymagana i nie może być pusta.',
                'order-invalid' => 'Każdy wpis w "order" musi być obiektem z id i position.',
                'id-not-on-product' => 'Obraz :id nie należy do tego produktu.',
                'graphql-upload-unsupported' => 'Przesyłanie obrazów przez GraphQL nie jest obsługiwane. Użyj POST /api/admin/catalog/products/{productId}/images z multipart/form-data.',
            ],

            'downloadable' => [
                'file-required' => 'Plik jest wymagany (pole multipart "file").',
                'file-too-large' => 'Plik jest zbyt duży. Maksymalny dozwolony rozmiar to 128 MB.',
                'upload-failed' => 'Nie udało się przesłać pliku do pobrania.',
                'file-not-found' => 'Nie znaleziono zapisanego pliku dla tego produktu i atrybutu.',
                'no-permission' => 'Nie masz uprawnień do zarządzania plikami produktów do pobrania.',
            ],

            'video' => [
                'uploaded' => 'Film produktu pomyślnie przesłany.',
                'deleted' => 'Film produktu pomyślnie usunięty.',
                'video-required' => 'Wymagany jest plik wideo (pole multipart "video").',
                'video-invalid-type' => 'Typ wideo musi być jednym z: mp4, webm, ogg, mov.',
                'video-too-large' => 'Film jest zbyt duży. Maksymalny dozwolony rozmiar to 100 MB.',
                'not-found' => 'Film produktu nie znaleziony.',
                'no-permission' => 'Nie masz uprawnień do zarządzania filmami produktów.',
                'upload-failed' => 'Nie udało się przesłać filmu produktu.',
                'delete-failed' => 'Nie udało się usunąć filmu produktu.',
                'graphql-upload-unsupported' => 'Przesyłanie filmów przez GraphQL nie jest obsługiwane. Użyj POST /api/admin/catalog/products/{productId}/videos z multipart/form-data.',
            ],

            'inventory' => [
                'updated' => 'Stany magazynowe produktu pomyślnie zapisane.',
                'inventories-required' => 'Pole inventories jest wymagane i musi być niepustą mapą inventory_source_id do ilości.',
                'inventories-invalid' => 'Pole inventories musi być mapą inventory_source_id (dodatnia liczba całkowita) do ilości (nieujemna liczba całkowita).',
                'source-not-found' => 'Źródło magazynowe :id nie istnieje.',
                'qty-invalid' => 'Każda ilość musi być nieujemną liczbą całkowitą.',
                'no-permission' => 'Nie masz uprawnień do zarządzania stanami magazynowymi produktów.',
                'not-found' => 'Produkt nie znaleziony.',
            ],

            'customer-group-price' => [
                'created' => 'Cena dla grupy klientów pomyślnie dodana.',
                'updated' => 'Cena dla grupy klientów pomyślnie zaktualizowana.',
                'deleted' => 'Cena dla grupy klientów pomyślnie usunięta.',
                'qty-required' => 'Pole qty jest wymagane.',
                'qty-invalid' => 'Pole qty musi być liczbą całkowitą większą lub równą 1.',
                'value-type-required' => 'Pole value_type jest wymagane.',
                'value-type-invalid' => 'Pole value_type musi mieć wartość "fixed" lub "discount".',
                'value-required' => 'Pole value jest wymagane.',
                'value-invalid' => 'Pole value musi być liczbą nieujemną.',
                'customer-group-not-found' => 'Wybrana grupa klientów nie istnieje.',
                'duplicate-qty-group' => 'Cena dla grupy klientów dla tej kombinacji (qty, customer_group_id) już istnieje dla tego produktu.',
                'not-found' => 'Cena dla grupy klientów nie znaleziona.',
                'no-permission' => 'Nie masz uprawnień do zarządzania cenami produktów dla grup klientów.',
            ],
        ],

        'category' => [
            'not-found' => 'Kategoria nie znaleziona.',

            'created' => 'Kategoria pomyślnie utworzona.',
            'updated' => 'Kategoria pomyślnie zaktualizowana.',
            'deleted' => 'Kategoria pomyślnie usunięta.',
            'slug-required' => 'Pole slug jest wymagane.',
            'slug-unique' => 'Podany slug jest już zajęty.',
            'name-required' => 'Pole name jest wymagane.',
            'position-required' => 'Pole position jest wymagane.',
            'attributes-required' => 'Wymagany jest co najmniej jeden atrybut filtrowalny.',
            'description-required-for-mode' => 'Dla wybranego trybu wyświetlania wymagany jest opis.',
            'cannot-delete-root' => 'Nie można usunąć kategorii głównej ani kategorii głównych kanałów.',
            'cannot-delete-channel-root' => 'Ta kategoria jest kategorią główną kanału i nie może zostać usunięta.',
            'delete-failed' => 'Nie udało się usunąć kategorii.',
            'no-permission' => 'Nie masz uprawnień do zarządzania kategoriami.',
            'mass-delete-success' => 'Kategorie pomyślnie usunięte.',
            'mass-update-status-success' => 'Status kategorii pomyślnie zaktualizowany.',
            'mass-delete-indices-required' => 'Pole indices jest wymagane i musi być niepustą tablicą.',
            'mass-update-status-value-required' => 'Pole value jest wymagane i musi mieć wartość 0 lub 1.',
        ],

        'attribute' => [
            'not-found' => 'Atrybut nie znaleziony.',

            'create-success' => 'Atrybut pomyślnie utworzony.',
            'update-success' => 'Atrybut pomyślnie zaktualizowany.',
            'delete-success' => 'Atrybut pomyślnie usunięty.',
            'delete-failed' => 'Nie udało się usunąć atrybutu.',
            'mass-delete-success' => 'Atrybuty pomyślnie usunięte.',
            'mass-delete-indices-required' => 'Pole indices jest wymagane i musi być niepustą tablicą.',

            'code-immutable' => 'Kodu atrybutu nie można zmienić po utworzeniu atrybutu.',
            'type-immutable' => 'Nie można zmienić typu atrybutu, dopóki ma on wartości produktów.',
            'locale-scope-immutable' => 'Nie można zmienić zakresu ustawień regionalnych, dopóki atrybut ma wartości produktów.',
            'system-attribute' => 'Atrybutów systemowych nie można usunąć.',
            'in-use-family' => 'Atrybut należy do co najmniej jednej rodziny atrybutów (identyfikatory grup: :ids). Najpierw usuń go z tych rodzin.',

            'option-not-found' => 'Opcja atrybutu nie znaleziona.',
            'option-not-supported' => 'Typ atrybutu ":type" nie obsługuje opcji. Opcje mogą mieć tylko atrybuty select, multiselect i checkbox.',
            'option-in-use' => 'Ta opcja jest używana przez produkty (:count) i nie może zostać usunięta.',
            'option-delete-success' => 'Opcja atrybutu pomyślnie usunięta.',
        ],

        'family' => [
            'not-found' => 'Rodzina atrybutów nie znaleziona.',

            'created' => 'Rodzina atrybutów pomyślnie utworzona.',
            'updated' => 'Rodzina atrybutów pomyślnie zaktualizowana.',
            'deleted' => 'Rodzina atrybutów pomyślnie usunięta.',
            'code-required' => 'Kod rodziny jest wymagany.',
            'code-unique' => 'Kod rodziny musi być unikalny.',
            'name-required' => 'Nazwa rodziny jest wymagana.',
            'group-code-required' => 'Każda grupa atrybutów musi mieć kod.',
            'group-name-required' => 'Każda grupa atrybutów musi mieć nazwę.',
            'group-column-invalid' => 'Kolumna każdej grupy atrybutów musi mieć wartość 1 lub 2.',
            'group-name-duplicate' => 'Nazwa grupy atrybutów musi być unikalna w obrębie rodziny atrybutów.',
            'update-failed' => 'Nie udało się zaktualizować rodziny atrybutów.',
            'create-failed' => 'Nie udało się utworzyć rodziny atrybutów.',
            'default-delete-error' => 'Nie można usunąć domyślnej rodziny atrybutów.',
            'last-delete-error' => 'Wymagana jest co najmniej jedna rodzina atrybutów.',
            'attribute-product-error' => 'Ta rodzina atrybutów jest używana przez co najmniej jeden produkt. Przypisz te produkty do innej rodziny przed jej usunięciem.',
            'delete-failed' => 'Nie udało się usunąć rodziny atrybutów.',
            'no-permission' => 'Nie masz uprawnień do zarządzania rodzinami atrybutów.',
        ],

        'settings' => [
            'locale' => [
                'not-found' => 'Ustawienia regionalne nie znalezione.',
                'created' => 'Ustawienia regionalne pomyślnie utworzone.',
                'updated' => 'Ustawienia regionalne pomyślnie zaktualizowane.',
                'deleted' => 'Ustawienia regionalne pomyślnie usunięte.',
                'cannot-delete-last' => 'Wymagane są co najmniej jedne ustawienia regionalne — nie można usunąć ostatnich pozostałych ustawień regionalnych.',
                'cannot-delete-channel-default' => 'Te ustawienia regionalne są domyślne dla co najmniej jednego kanału i nie mogą zostać usunięte.',
                'delete-failed' => 'Nie udało się usunąć ustawień regionalnych.',
                'no-permission' => 'Nie masz uprawnień do zarządzania ustawieniami regionalnymi.',
                'mass-delete-success' => 'Ustawienia regionalne pomyślnie usunięte.',
                'mass-delete-indices-required' => 'Pole indices jest wymagane i musi być niepustą tablicą.',
            ],

            'theme' => [
                'not-found' => 'Dostosowanie motywu nie znalezione.',
                'created' => 'Dostosowanie motywu pomyślnie utworzone.',
                'updated' => 'Dostosowanie motywu pomyślnie zaktualizowane.',
                'deleted' => 'Dostosowanie motywu pomyślnie usunięte.',
                'delete-failed' => 'Nie udało się usunąć dostosowania motywu.',
                'no-permission' => 'Nie masz uprawnień do zarządzania dostosowaniami motywu.',
                'mass-delete-success' => 'Dostosowania motywu pomyślnie usunięte.',
                'mass-delete-indices-required' => 'Pole indices jest wymagane i musi być niepustą tablicą.',
                'mass-update-success' => 'Dostosowania motywu pomyślnie zaktualizowane.',
                'mass-update-indices-required' => 'Pole indices jest wymagane i musi być niepustą tablicą.',
                'mass-update-value-invalid' => 'Pole value musi mieć wartość 0 lub 1.',
            ],

            'inventory-source' => [
                'not-found' => 'Źródło magazynowe nie znalezione.',
                'created' => 'Źródło magazynowe pomyślnie utworzone.',
                'updated' => 'Źródło magazynowe pomyślnie zaktualizowane.',
                'deleted' => 'Źródło magazynowe pomyślnie usunięte.',
                'code-unique' => 'Źródło magazynowe z tym kodem już istnieje.',
                'last-delete-error' => 'Wymagane jest co najmniej jedno źródło magazynowe — nie można usunąć ostatniego pozostałego źródła.',
                'in-use' => 'To źródło magazynowe jest używane przez stany magazynowe produktów i nie może zostać usunięte.',
                'delete-failed' => 'Nie udało się usunąć źródła magazynowego.',
                'no-permission' => 'Nie masz uprawnień do zarządzania źródłami magazynowymi.',
                'mass-delete-success' => 'Źródła magazynowe pomyślnie usunięte.',
                'mass-delete-indices-required' => 'Pole indices jest wymagane i musi być niepustą tablicą.',
            ],

            'exchange-rate' => [
                'not-found' => 'Kurs wymiany nie znaleziony.',
                'created' => 'Kurs wymiany pomyślnie utworzony.',
                'updated' => 'Kurs wymiany pomyślnie zaktualizowany.',
                'deleted' => 'Kurs wymiany pomyślnie usunięty.',
                'duplicate-pair' => 'Kurs wymiany dla tej waluty docelowej już istnieje. Zaktualizuj istniejący wiersz lub najpierw go usuń.',
                'delete-failed' => 'Nie udało się usunąć kursu wymiany.',
                'no-permission' => 'Nie masz uprawnień do zarządzania kursami wymiany.',
                'mass-delete-success' => 'Kursy wymiany pomyślnie usunięte.',
                'mass-delete-indices-required' => 'Pole indices jest wymagane i musi być niepustą tablicą.',
                'update-rates-success' => 'Kursy wymiany pomyślnie zaktualizowane.',
                'update-rates-failed' => 'Nie udało się zaktualizować kursów wymiany od zewnętrznego dostawcy.',
                'provider-not-configured' => 'Nie skonfigurowano zewnętrznego dostawcy kursów wymiany.',
            ],

            'tax-rate' => [
                'not-found' => 'Stawka podatkowa nie znaleziona.',
                'created' => 'Stawka podatkowa pomyślnie utworzona.',
                'updated' => 'Stawka podatkowa pomyślnie zaktualizowana.',
                'deleted' => 'Stawka podatkowa pomyślnie usunięta.',
                'delete-failed' => 'Nie udało się usunąć stawki podatkowej.',
                'no-permission' => 'Nie masz uprawnień do zarządzania stawkami podatkowymi.',
                'zip-code-required' => 'Pole zip_code jest wymagane, gdy is_zip ma wartość false.',
                'zip-range-required' => 'Pola zip_from i zip_to są wymagane, gdy is_zip ma wartość true.',
            ],

            'tax-category' => [
                'not-found' => 'Kategoria podatkowa nie znaleziona.',
                'created' => 'Kategoria podatkowa pomyślnie utworzona.',
                'updated' => 'Kategoria podatkowa pomyślnie zaktualizowana.',
                'deleted' => 'Kategoria podatkowa pomyślnie usunięta.',
                'code-unique' => 'Podany kod jest już zajęty.',
                'cannot-delete-in-use' => 'Do tej kategorii podatkowej są nadal przypisane stawki podatkowe, więc nie można jej usunąć.',
                'delete-failed' => 'Nie udało się usunąć kategorii podatkowej.',
                'no-permission' => 'Nie masz uprawnień do zarządzania kategoriami podatkowymi.',
            ],

            'role' => [
                'not-found' => 'Rola nie znaleziona.',
                'created' => 'Rola pomyślnie utworzona.',
                'updated' => 'Rola pomyślnie zaktualizowana.',
                'deleted' => 'Rola pomyślnie usunięta.',
                'permissions-required' => 'Pole permissions jest wymagane, gdy permission_type ma wartość custom.',
                'cannot-delete-in-use' => 'Ta rola jest przypisana do co najmniej jednego administratora i nie może zostać usunięta.',
                'cannot-delete-last-role' => 'Wymagana jest co najmniej jedna rola — nie można usunąć ostatniej pozostałej roli.',
                'cannot-change-last-all-access' => 'Jedynej roli z pełnym dostępem nie można zmienić na rolę niestandardową.',
                'delete-failed' => 'Nie udało się usunąć roli.',
                'no-permission' => 'Nie masz uprawnień do zarządzania rolami.',
            ],

            'currency' => [
                'not-found' => 'Waluta nie znaleziona.',
                'created' => 'Waluta pomyślnie utworzona.',
                'updated' => 'Waluta pomyślnie zaktualizowana.',
                'deleted' => 'Waluta pomyślnie usunięta.',
                'code-required' => 'Pole code jest wymagane.',
                'code-size' => 'Kod musi składać się dokładnie z 3 liter.',
                'code-unique' => 'Podany kod jest już zajęty.',
                'name-required' => 'Pole name jest wymagane.',
                'cannot-delete-last' => 'Wymagana jest co najmniej jedna waluta — nie można usunąć ostatniej pozostałej waluty.',
                'cannot-delete-channel-base' => 'Ta waluta jest walutą bazową co najmniej jednego kanału i nie może zostać usunięta.',
                'delete-failed' => 'Nie udało się usunąć waluty.',
                'no-permission' => 'Nie masz uprawnień do zarządzania walutami.',

                'mass-delete-success' => 'Waluty pomyślnie usunięte.',
                'mass-delete-indices-required' => 'Pole indices jest wymagane i musi być niepustą tablicą.',
            ],

            'channel' => [
                'not-found' => 'Kanał nie znaleziony.',

                'created' => 'Kanał pomyślnie utworzony.',
                'updated' => 'Kanał pomyślnie zaktualizowany.',
                'deleted' => 'Kanał pomyślnie usunięty.',
                'code-unique' => 'Podany kod jest już zajęty.',
                'hostname-unique' => 'Podana nazwa hosta jest już zajęta.',
                'default-locale-mismatch' => 'Wartość default_locale_id musi znajdować się na liście locales.',
                'base-currency-mismatch' => 'Wartość base_currency_id musi znajdować się na liście currencies.',
                'unknown-locale' => 'Co najmniej jeden identyfikator ustawień regionalnych nie istnieje.',
                'unknown-currency' => 'Co najmniej jeden identyfikator waluty nie istnieje.',
                'unknown-inventory-source' => 'Co najmniej jeden identyfikator inventory_source nie istnieje.',
                'unknown-root-category' => 'Podany root_category_id nie istnieje.',
                'cannot-delete-last' => 'Wymagany jest co najmniej jeden kanał — nie można usunąć ostatniego pozostałego kanału.',
                'cannot-delete-default' => 'Nie można usunąć domyślnego kanału aplikacji.',
                'delete-failed' => 'Nie udało się usunąć kanału.',
                'no-permission' => 'Nie masz uprawnień do zarządzania kanałami.',
            ],

            'user' => [
                'not-found' => 'Użytkownik administracyjny nie znaleziony.',
                'created' => 'Użytkownik administracyjny pomyślnie utworzony.',
                'updated' => 'Użytkownik administracyjny pomyślnie zaktualizowany.',
                'deleted' => 'Użytkownik administracyjny pomyślnie usunięty.',
                'name-required' => 'Pole name jest wymagane.',
                'email-required' => 'Pole email jest wymagane.',
                'email-invalid' => 'Adres e-mail musi być prawidłowy.',
                'email-unique' => 'Podany adres e-mail jest już zajęty.',
                'password-required' => 'Pole password jest wymagane.',
                'password-min' => 'Hasło musi mieć co najmniej 6 znaków.',
                'role-required' => 'Pole role_id jest wymagane.',
                'role-not-found' => 'Wybrana rola nie istnieje.',
                'status-invalid' => 'Status musi mieć wartość 0 lub 1.',
                'cannot-delete-self' => 'Nie możesz usunąć własnego konta administratora.',
                'cannot-delete-last-admin' => 'Wymagany jest co najmniej jeden użytkownik administracyjny — nie można usunąć ostatniego pozostałego administratora.',
                'cannot-change' => 'Nie można zmienić pola :name ostatniego administratora z pełnym dostępem.',
                'delete-failed' => 'Nie udało się usunąć użytkownika administracyjnego.',
                'no-permission' => 'Nie masz uprawnień do zarządzania użytkownikami administracyjnymi.',
                'self-delete-password-required' => 'Pole password jest wymagane do usunięcia własnego konta.',
                'self-delete-incorrect-password' => 'Hasło jest nieprawidłowe.',
                'self-deleted' => 'Twoje konto administratora zostało usunięte.',
            ],

            'data-transfer' => [
                'import' => [
                    'not-found' => 'Import nie znaleziony.',
                    'deleted' => 'Import pomyślnie usunięty.',
                    'cancelled' => 'Import pomyślnie anulowany.',
                    'cannot-cancel' => 'Anulować można tylko importy oczekujące lub w trakcie przetwarzania. Bieżący stan: :state.',
                    'no-permission' => 'Nie masz uprawnień do zarządzania importami danych.',
                    'delete-failed' => 'Nie udało się usunąć importu.',
                    'created' => 'Import pomyślnie utworzony.',
                    'updated' => 'Import pomyślnie zaktualizowany.',
                    'type-invalid' => 'Wybrany typ jest nieprawidłowy.',
                    'action-invalid' => 'Akcja musi mieć jedną z wartości: append, delete.',
                    'validation-strategy-invalid' => 'Strategia walidacji musi mieć jedną z wartości: stop-on-errors, skip-errors.',
                    'allowed-errors-invalid' => 'Liczba dozwolonych błędów musi być liczbą całkowitą równą 0 lub większą.',
                    'field-separator-required' => 'Separator pól jest wymagany.',
                    'file-required' => 'Plik jest wymagany.',
                    'file-invalid-type' => 'Plik musi mieć jeden z formatów: csv, xls, xlsx, xml.',
                    'nothing-to-import' => 'Nie ma nic do zaimportowania.',
                    'not-valid' => 'Import jest nieprawidłowy.',
                    'setup-queue-error' => 'Połączenie kolejki jest ustawione na sync; skonfiguruj rzeczywistą kolejkę, aby przetwarzać importy w tle.',
                    'file-not-found' => 'Brak pliku do pobrania.',
                    'error-report-not-found' => 'Brak raportu błędów do pobrania.',
                    'sample-not-found' => 'Brak przykładowego pliku dla żądanego typu i formatu.',
                    'sample-format-invalid' => 'Format przykładu musi być jednym z: csv, xls, xlsx, xml.',
                    'images-archive-too-large' => 'Archiwum obrazów może zawierać maksymalnie :entries plików, a po rozpakowaniu zajmować maksymalnie :size MB.',
                ],
            ],
        ],

        'cms' => [
            'page' => [
                'not-found' => 'Strona CMS nie znaleziona.',

                'created' => 'Strona CMS pomyślnie utworzona.',
                'url-key-required' => 'Pole url_key jest wymagane.',
                'url-key-unique' => 'Podany url_key jest już zajęty.',
                'url-key-invalid' => 'Format url_key jest nieprawidłowy. Używaj wyłącznie małych liter, cyfr i myślników.',
                'page-title-required' => 'Pole page_title jest wymagane.',
                'html-content-required' => 'Pole html_content jest wymagane.',
                'channels-required' => 'Należy wybrać co najmniej jeden kanał.',
                'channels-invalid' => 'Co najmniej jeden kanał jest nieprawidłowy.',

                'updated' => 'Strona CMS pomyślnie zaktualizowana.',

                'deleted' => 'Strona CMS pomyślnie usunięta.',
                'delete-failed' => 'Nie udało się usunąć strony CMS.',

                'mass-deleted' => 'Strony CMS pomyślnie usunięte.',
                'indices-required' => 'Pole indices jest wymagane i musi być niepustą tablicą.',
                'indices-invalid' => 'Pole indices musi być tablicą identyfikatorów stron CMS.',

                'no-permission' => 'Nie masz uprawnień do zarządzania stronami CMS.',
            ],
        ],

        'marketing' => [
            'catalog-rule' => [
                'not-found' => 'Reguła katalogowa nie znaleziona.',
                'created' => 'Reguła katalogowa pomyślnie utworzona.',
                'updated' => 'Reguła katalogowa pomyślnie zaktualizowana.',
                'deleted' => 'Reguła katalogowa pomyślnie usunięta.',
                'delete-failed' => 'Nie udało się usunąć reguły katalogowej.',
                'mass-delete-indices-required' => 'Nie podano identyfikatorów reguł katalogowych do masowego usunięcia.',
                'mass-delete-success' => 'Wybrane reguły katalogowe pomyślnie usunięte.',
                'no-permission' => 'Nie masz uprawnień do zarządzania regułami katalogowymi.',
            ],

            'campaign' => [
                'not-found' => 'Kampania nie znaleziona.',
                'created' => 'Kampania pomyślnie utworzona.',
                'updated' => 'Kampania pomyślnie zaktualizowana.',
                'deleted' => 'Kampania pomyślnie usunięta.',
                'delete-failed' => 'Nie udało się usunąć kampanii.',
                'no-permission' => 'Nie masz uprawnień do zarządzania kampaniami.',

                'send' => [
                    'id-required' => 'Identyfikator kampanii jest wymagany.',
                    'inactive' => 'Nie można wysłać nieaktywnej kampanii. Najpierw ją włącz.',
                    'queued' => 'Kampania dodana do kolejki dla odbiorców (:count).',
                ],
            ],

            'sitemap' => [
                'not-found' => 'Mapa witryny nie znaleziona.',
                'created' => 'Mapa witryny pomyślnie utworzona.',
                'updated' => 'Mapa witryny pomyślnie zaktualizowana.',
                'deleted' => 'Mapa witryny pomyślnie usunięta.',
                'delete-failed' => 'Nie udało się usunąć mapy witryny.',
                'no-permission' => 'Nie masz uprawnień do zarządzania mapami witryny.',

                'generate' => [
                    'id-required' => 'Identyfikator mapy witryny jest wymagany.',
                    'no-channels' => 'Ta mapa witryny nie obejmuje żadnego kanału, więc nie ma czego generować. Najpierw przypisz do niej co najmniej jeden kanał.',
                    'success' => 'Mapa witryny pomyślnie wygenerowana ponownie.',
                    'failed' => 'Generowanie mapy witryny nie powiodło się: :message',
                ],
            ],

            'cart-rule' => [
                'not-found' => 'Reguła koszyka nie znaleziona.',
                'id-required' => 'Identyfikator reguły koszyka jest wymagany.',
                'copy-of' => 'Kopia :value',
                'copied' => 'Reguła koszyka pomyślnie skopiowana.',
                'created' => 'Reguła koszyka pomyślnie utworzona.',
                'updated' => 'Reguła koszyka pomyślnie zaktualizowana.',
                'deleted' => 'Reguła koszyka pomyślnie usunięta.',
                'delete-failed' => 'Nie udało się usunąć reguły koszyka.',
                'no-permission' => 'Nie masz uprawnień do zarządzania regułami koszyka.',
                'mass-delete-success' => 'Reguły koszyka pomyślnie usunięte.',
                'mass-delete-indices-required' => 'Pole indices jest wymagane i musi być niepustą tablicą.',
                'action-type-invalid' => 'Pole action_type musi mieć jedną z wartości: by_percent, by_fixed, cart_fixed, buy_x_get_y.',
                'coupon-code-required' => 'Pole coupon_code jest wymagane, gdy coupon_type=1 i use_auto_generation=0.',
                'coupon-code-unique' => 'Ten kod kuponu jest już używany.',
            ],

            'template' => [
                'campaign-associated' => 'Ten szablon e-mail jest używany przez kampanię, więc nie można go usunąć. Najpierw usuń go z kampanii.',
                'not-found' => 'Szablon e-mail nie znaleziony.',
                'created' => 'Szablon e-mail pomyślnie utworzony.',
                'updated' => 'Szablon e-mail pomyślnie zaktualizowany.',
                'deleted' => 'Szablon e-mail pomyślnie usunięty.',
                'delete-failed' => 'Nie udało się usunąć szablonu e-mail.',
                'status-invalid' => 'Status musi mieć jedną z wartości: active, inactive, draft.',
                'no-permission' => 'Nie masz uprawnień do zarządzania szablonami e-mail.',
            ],

            'event' => [
                'campaign-associated' => 'To wydarzenie jest używane przez kampanię, więc nie można go usunąć. Najpierw usuń je z kampanii.',
                'not-found' => 'Wydarzenie marketingowe nie znalezione.',
                'created' => 'Wydarzenie marketingowe pomyślnie utworzone.',
                'updated' => 'Wydarzenie marketingowe pomyślnie zaktualizowane.',
                'deleted' => 'Wydarzenie marketingowe pomyślnie usunięte.',
                'delete-failed' => 'Nie udało się usunąć wydarzenia marketingowego.',
                'no-permission' => 'Nie masz uprawnień do zarządzania wydarzeniami marketingowymi.',
            ],

            'search-synonym' => [
                'not-found' => 'Synonim wyszukiwania nie znaleziony.',
                'created' => 'Synonim wyszukiwania pomyślnie utworzony.',
                'updated' => 'Synonim wyszukiwania pomyślnie zaktualizowany.',
                'deleted' => 'Synonim wyszukiwania pomyślnie usunięty.',
                'mass-deleted' => 'Synonimy wyszukiwania pomyślnie usunięte.',
                'delete-failed' => 'Nie udało się usunąć synonimu wyszukiwania.',
                'indices-required' => 'Pole indices jest wymagane i musi być niepustą tablicą identyfikatorów synonimów wyszukiwania.',
                'no-permission' => 'Nie masz uprawnień do zarządzania synonimami wyszukiwania.',
            ],

            'url-rewrite' => [
                'not-found' => 'Przepisanie adresu URL nie znalezione.',
                'created' => 'Przepisanie adresu URL pomyślnie utworzone.',
                'updated' => 'Przepisanie adresu URL pomyślnie zaktualizowane.',
                'deleted' => 'Przepisanie adresu URL pomyślnie usunięte.',
                'delete-failed' => 'Nie udało się usunąć przepisania adresu URL.',
                'mass-deleted' => 'Przepisania adresów URL pomyślnie usunięte.',
                'indices-required' => 'Wymagany jest co najmniej jeden identyfikator przepisania adresu URL.',
                'no-permission' => 'Nie masz uprawnień do zarządzania przepisaniami adresów URL.',
            ],

            'cart-rule-coupon' => [
                'cart-rule-not-found' => 'Reguła koszyka nie znaleziona.',
                'not-found' => 'Kupon nie znaleziony.',
                'code-taken' => 'Kupon z tym kodem już istnieje.',
                'format-invalid' => 'Format musi mieć jedną z wartości: alphabetic, alphanumeric, numeric.',
                'indices-required' => 'Pole indices jest wymagane i musi być niepustą tablicą identyfikatorów kuponów.',
                'created' => 'Kupon pomyślnie utworzony.',
                'generated' => 'Pomyślnie wygenerowano kupony (:count).',
                'deleted' => 'Kupon pomyślnie usunięty.',
                'mass-deleted' => 'Pomyślnie usunięto kupony (:count).',
                'no-permission' => 'Nie masz uprawnień do zarządzania kuponami reguł koszyka.',
            ],

            'subscriber' => [
                'not-found' => 'Subskrybent newslettera nie znaleziony.',
                'updated' => 'Subskrypcja pomyślnie zaktualizowana.',
                'deleted' => 'Subskrypcja pomyślnie usunięta.',
                'is-subscribed-required' => 'Pole is_subscribed jest wymagane.',
                'no-permission' => 'Nie masz uprawnień do zarządzania subskrybentami newslettera.',
            ],

            'search-term' => [
                'not-found' => 'Wyszukiwana fraza nie znaleziona.',
                'updated' => 'Wyszukiwana fraza pomyślnie zaktualizowana.',
                'deleted' => 'Wyszukiwana fraza pomyślnie usunięta.',
                'mass-deleted' => 'Wyszukiwane frazy pomyślnie usunięte.',
                'term-required' => 'Pole term jest wymagane.',
                'redirect-url-invalid' => 'Pole redirect_url musi być prawidłowym adresem URL.',
                'indices-required' => 'W indices należy podać co najmniej jeden identyfikator.',
                'no-permission' => 'Nie masz uprawnień do zarządzania wyszukiwanymi frazami.',
            ],
        ],
        'customer-api-sessions' => [
            'menu' => [
                'title' => 'Sesje API',
            ],

            'acl' => [
                'title' => 'Sesje API',
                'delete' => 'Unieważnij',
            ],

            'index' => [
                'title' => 'Sesje API klientów',
                'info' => 'Klienci zalogowani do API sklepu z aplikacji webowych, mobilnych, POS lub desktopowych. Unieważnienie sesji natychmiast wylogowuje to urządzenie.',
            ],

            'fields' => [
                'customer' => 'Klient',
                'email' => 'E-mail',
                'channel' => 'Kanał',
            ],

            'datagrid' => [
                'revoke-all' => 'Unieważnij wszystkie sesje klienta',
            ],

            'card' => [
                'title' => 'Sesje API (:count)',
                'empty' => 'Ten klient nie ma aktywnych sesji API.',
            ],

            'errors' => [
                'no-permission' => 'Nie masz uprawnień do zarządzania sesjami API klientów.',
            ],
        ],

        'customer-auth' => [
            'configuration' => [
                'title' => 'Uwierzytelnianie klientów',
                'info' => 'Czas ważności tokenów, limity i zasady sesji dla logowań klientów sklepu.',

                'tokens' => [
                    'title' => 'Czas ważności tokenów',
                    'info' => 'Jak długo ważne są tokeny dostępu, tokeny odświeżania i sesje. Zmiany dotyczą tokenów wydanych później.',
                    'access-token-ttl' => 'Czas ważności tokenu dostępu (minuty)',
                    'access-token-ttl-info' => 'Od 5 do 60 minut. Domyślnie 15.',
                    'refresh-token-ttl' => 'Czas ważności tokenu odświeżania (dni)',
                    'refresh-token-ttl-info' => 'Od 1 do 90 dni. Domyślnie 30.',
                    'session-lifetime' => 'Maksymalny czas trwania sesji (dni)',
                    'session-lifetime-info' => 'Od 1 do 365 dni. Domyślnie 90. Potem klient musi zalogować się ponownie, niezależnie od liczby odświeżeń sesji.',
                    'max-sessions' => 'Maksymalna liczba aktywnych sesji na klienta',
                    'max-sessions-info' => 'Po osiągnięciu limitu najstarsza sesja jest kończona. Od 0 do 100. 0 oznacza brak limitu.',
                ],

                'rate-limits' => [
                    'title' => 'Limity żądań',
                    'info' => 'Maksymalna liczba żądań API sklepu na minutę dla każdego zalogowanego klienta.',
                    'requests-per-minute' => 'Żądania na minutę na zalogowanego klienta',
                    'requests-per-minute-info' => 'Od 1 do 100000. Domyślnie 120.',
                ],

                'login-protection' => [
                    'title' => 'Ochrona logowania',
                    'info' => 'Ile nieudanych logowań i odświeżeń tokenu jest dozwolonych, zanim klient musi poczekać.',
                    'login-max-attempts' => 'Nieudane logowania przed blokadą',
                    'login-max-attempts-info' => 'Na adres e-mail i adres IP. Od 3 do 20. Domyślnie 5.',
                    'login-lockout-seconds' => 'Blokada logowania (sekundy)',
                    'login-lockout-seconds-info' => 'Od 60 do 3600. Domyślnie 60.',
                    'refresh-requests-per-minute' => 'Odświeżenia tokenu na minutę na sesję',
                    'refresh-requests-per-minute-info' => 'Od 1 do 60. Domyślnie 10.',
                ],

                'security' => [
                    'title' => 'Bezpieczeństwo',
                    'info' => 'Co kończy pozostałe sesje klienta.',
                    'revoke-sessions-on-password-change' => 'Unieważniaj sesje przy zmianie hasła',
                ],

                'retention' => [
                    'title' => 'Przechowywanie danych',
                    'info' => 'Jak długo przechowywane są zakończone sesje klientów.',
                    'session-prune-days' => 'Przechowuj zakończone sesje (dni)',
                    'session-prune-days-info' => 'Unieważnione lub wygasłe sesje są usuwane po tej liczbie dni. Od 1 do 365. Domyślnie 30.',
                ],
            ],
        ],
        'auth' => [
            'configuration' => [
                'title' => 'Uwierzytelnianie administratora',
                'info' => 'Czas ważności tokenów, limity żądań i reguły bezpieczeństwa dla logowań do API administratora i tokenów API.',

                'tokens' => [
                    'title' => 'Czas ważności tokenów',
                    'info' => 'Jak długo ważne są tokeny dostępu, tokeny odświeżania i sesje. Zmiany dotyczą tokenów wydanych później.',
                    'access-token-ttl' => 'Czas ważności tokenu dostępu (minuty)',
                    'access-token-ttl-info' => 'Od 5 do 60 minut. Domyślnie 15.',
                    'refresh-token-ttl' => 'Czas ważności tokenu odświeżania (dni)',
                    'refresh-token-ttl-info' => 'Od 1 do 90 dni. Domyślnie 30.',
                    'session-lifetime' => 'Maksymalny czas trwania sesji (dni)',
                    'session-lifetime-info' => 'Od 1 do 365 dni. Domyślnie 90. Potem administrator musi zalogować się ponownie, niezależnie od liczby odświeżeń sesji.',
                ],

                'rate-limits' => [
                    'title' => 'Limity żądań',
                    'info' => 'Maksymalna liczba żądań do API administratora na minutę.',
                    'session-requests-per-minute' => 'Żądania na minutę na zalogowanego administratora',
                    'api-token-requests-per-minute' => 'Żądania na minutę na token API',
                    'session-requests-per-minute-info' => 'Od 1 do 100000. Domyślnie 60.',
                    'api-token-requests-per-minute-info' => 'Od 1 do 100000. Domyślnie 60.',
                    'unauthenticated-requests-per-minute' => 'Żądania na minutę bez tokenu, na adres IP',
                    'unauthenticated-requests-per-minute-info' => 'Od 10 do 1000. Domyślnie 60. Obejmuje żądania logowania, uwierzytelniania dwuskładnikowego, odświeżania i hasła.',
                ],

                'security' => [
                    'title' => 'Bezpieczeństwo',
                    'info' => 'Reguły unieważniania, wygasanie tokenów API i powiązanie z domeną.',
                    'revoke-sessions-on-password-change' => 'Unieważniaj sesje przy zmianie hasła',
                    'revoke-api-tokens-on-password-change' => 'Unieważniaj tokeny API przy zmianie hasła',
                    'allow-non-expiring-api-tokens' => 'Zezwalaj na tokeny API bez daty wygaśnięcia',
                    'domain-binding-mode' => 'Tryb powiązania z domeną',
                    'domain-binding-mode-info' => 'DNS sprawdza, czy żądanie pochodzi z adresu IP, na który wskazuje domena tokenu. Origin sprawdza nagłówek Origin lub Referer i nadaje się tylko dla przeglądarek.',
                    'domain-binding-dns' => 'DNS (adres IP klienta)',
                    'domain-binding-origin' => 'Nagłówek Origin',
                ],

                'login-protection' => [
                    'title' => 'Ochrona logowania',
                    'info' => 'Ile nieudanych logowań, błędnych kodów dwuskładnikowych i żądań hasła jest dozwolonych, zanim klient musi poczekać.',
                    'login-max-attempts' => 'Nieudane logowania przed blokadą',
                    'login-max-attempts-info' => 'Na adres e-mail i adres IP. Od 3 do 20. Domyślnie 5.',
                    'login-lockout-seconds' => 'Blokada logowania (sekundy)',
                    'login-lockout-seconds-info' => 'Od 60 do 3600. Domyślnie 60.',
                    'two-factor-challenge-lifetime' => 'Ważność wyzwania dwuskładnikowego (minuty)',
                    'two-factor-challenge-lifetime-info' => 'Od 1 do 15. Domyślnie 5.',
                    'two-factor-max-failures' => 'Błędne kody dwuskładnikowe na minutę',
                    'two-factor-max-failures-info' => 'Na administratora, we wszystkich wyzwaniach. Od 3 do 20. Domyślnie 5.',
                    'password-requests-per-minute' => 'Żądania hasła na minutę',
                    'password-requests-per-minute-info' => 'Żądania przypomnienia i resetu hasła na adres IP. Od 1 do 20. Domyślnie 5.',
                ],

                'retention' => [
                    'title' => 'Przechowywanie danych',
                    'info' => 'Jak długo przechowywane są zakończone sesje i historia audytu API.',
                    'session-prune-days' => 'Przechowuj zakończone sesje (dni)',
                    'session-prune-days-info' => 'Unieważnione lub wygasłe sesje są usuwane po tej liczbie dni. Od 1 do 365. Domyślnie 30.',
                    'audit-retention-days' => 'Przechowuj historię audytu API (dni)',
                    'audit-retention-days-info' => 'Starsze wpisy są usuwane codziennie. Od 0 do 3650. Puste lub 0 przechowuje je na zawsze.',
                ],
            ],

            'errors' => [
                'unauthenticated' => 'Nie jesteś uwierzytelniony.',
                'access-token-expired' => 'Token dostępu wygasł. Odśwież go, aby kontynuować.',
                'api-token-expired' => 'Token API wygasł.',
                'refresh-token-invalid' => 'Token odświeżania jest nieprawidłowy lub wygasł. Zaloguj się ponownie.',
                'api-token-revoked' => 'Token API został unieważniony.',
                'api-token-domain-mismatch' => 'Tokenu API nie można używać z tej domeny.',
                'access-token-required' => 'Ta operacja wymaga tokenu dostępu sesji logowania; tokeny API nie mogą z niej korzystać.',
                'two-factor-required' => 'Wprowadź 6-cyfrowy kod z aplikacji uwierzytelniającej lub jeden z kodów zapasowych, aby dokończyć logowanie.',
                'too-many-login-attempts' => 'Zbyt wiele prób logowania. Spróbuj ponownie za :seconds s.',
                'two-factor-challenge-invalid' => 'Wyzwanie uwierzytelniania dwuskładnikowego jest nieprawidłowe lub wygasło. Zaloguj się ponownie.',
                'two-factor-attempts-exceeded' => 'Zbyt wiele błędnych kodów. Zaloguj się ponownie.',
                'password-reset-token-invalid' => 'Ten token resetowania hasła jest nieprawidłowy lub wygasł.',
                'too-many-password-requests' => 'Zbyt wiele żądań dotyczących hasła. Spróbuj ponownie za :seconds s.',
                'rate-limit-exceeded' => 'Zbyt wiele prób.',
                'no-permission' => 'Nie masz uprawnień do zarządzania sesjami API administratorów.',
            ],

            'sessions' => [
                'logged-out' => 'Wylogowano.',
                'logged-out-all' => 'Wylogowano ze wszystkich sesji.',
                'not-found' => 'Nie znaleziono sesji.',
                'revoked' => 'Sesja została unieważniona.',
                'revoked-for-admin' => 'Unieważnione sesje: :count.',
            ],

            'password' => [
                'reset' => 'Twoje hasło zostało zresetowane. Zaloguj się ponownie.',
            ],
        ],

        'api-tokens' => [
            'menu' => [
                'title' => 'Tokeny API',
            ],

            'acl' => [
                'title' => 'Tokeny API',
                'create' => 'Utwórz',
                'delete' => 'Unieważnij',
                'manage-others' => 'Zarządzaj tokenami innych administratorów',
            ],

            'index' => [
                'title' => 'Tokeny API',
                'info' => 'Integracje serwerowe, takie jak system ERP, PIM, magazynowy lub księgowy, uwierzytelniają się tokenami API. Każdy token działa jako jeden administrator z uprawnieniami jego roli i działa tylko z domeny, do której jest przypisany.',
                'create-btn' => 'Utwórz token API',
            ],

            'create' => [
                'title' => 'Utwórz token API',
                'back-btn' => 'Wstecz',
                'save-btn' => 'Utwórz token',
                'general' => 'Ogólne',
                'expiry' => 'Wygaśnięcie',
                'admin-info' => 'Token działa jako ten administrator i ma dokładnie uprawnienia jego roli.',
                'domain-info' => 'Host, z którego wywołuje integracja, np. erp.example.com. Adres URL jest skracany do hosta. Adresy IP i symbole wieloznaczne nie są akceptowane, a domeny nie można później zmienić.',
                'expires-on-date' => 'Wygasa w dniu',
                'never-expires' => 'Nigdy nie wygasa',
                'no-expiry-warning' => 'Token bez daty wygaśnięcia działa, dopóki nie zostanie unieważniony. Przechowuj go w sejfie sekretów i unieważnij, gdy tylko przestanie być potrzebny.',
                'no-expiry-confirm' => 'Rozumiem, że ten token nigdy nie wygasa.',
                'no-admins' => 'Nie ma administratora, dla którego możesz utworzyć token API.',
            ],

            'view' => [
                'title' => 'Token API',
                'back-btn' => 'Wstecz',
                'save-btn' => 'Zapisz',
                'revoke-btn' => 'Unieważnij',
                'details' => 'Szczegóły',
                'usage' => 'Użycie',
                'token-once-title' => 'Skopiuj teraz swój token API',
                'token-once-info' => 'To jedyny raz, kiedy token jest wyświetlany. Przechowuj go w sejfie sekretów; jeśli go zgubisz, unieważnij go i utwórz nowy.',
                'copy-btn' => 'Kopiuj',
                'copied' => 'Token został skopiowany.',
                'usage-info' => 'Wysyłaj go jako token Bearer w nagłówku Authorization żądań REST i GraphQL.',
                'not-yet' => 'Jeszcze nie',
                'revoke-confirm' => 'Unieważnić ten token API? Integracja, która go używa, natychmiast przestanie działać.',
            ],

            'fields' => [
                'id' => 'ID',
                'name' => 'Nazwa',
                'admin' => 'Administrator',
                'token' => 'Token',
                'domain' => 'Domena',
                'expiry' => 'Wygaśnięcie',
                'expires-at' => 'Wygasa',
                'no-expiry-confirmation' => 'Potwierdzenie braku wygaśnięcia',
                'status' => 'Status',
                'created-by' => 'Utworzony przez',
                'created-at' => 'Utworzono',
                'last-used-at' => 'Ostatnio użyty',
                'last-ip-address' => 'Ostatni adres IP',
                'last-user-agent' => 'Ostatni user agent',
                'last-channel' => 'Ostatni kanał',
                'resolved-ips' => 'Rozwiązane adresy IP',
                'resolved-at' => 'Rozwiązano',
                'domain-mismatches' => 'Żądania z innych domen',
                'last-domain-mismatch-at' => 'Ostatnie żądanie z innej domeny',
                'revoked-at' => 'Unieważniono',
                'revoked-by' => 'Unieważnił',
            ],

            'datagrid' => [
                'never' => 'Nigdy',
                'console' => 'Polecenie konsoli',
                'view' => 'Wyświetl',
                'revoke' => 'Unieważnij',
            ],

            'status' => [
                'active' => 'Aktywny',
                'expired' => 'Wygasły',
                'revoked' => 'Unieważniony',
            ],

            'channels' => [
                'rest' => 'REST',
                'graphql' => 'GraphQL',
            ],

            'messages' => [
                'created' => 'Token API został utworzony.',
                'updated' => 'Token API został zaktualizowany.',
                'revoked' => 'Token API został unieważniony.',
            ],

            'errors' => [
                'no-permission' => 'Nie masz uprawnień do zarządzania tokenami API.',
                'not-found' => 'Nie znaleziono tokenu API.',
                'cannot-create-for-admin' => 'Nie możesz utworzyć tokenu API dla tego administratora: jego rola ma uprawnienia, których nie ma Twoja, albo nie możesz zarządzać tokenami innych administratorów.',
                'cannot-manage' => 'Nie możesz zarządzać tym tokenem API.',
                'already-revoked' => 'Token API jest już unieważniony.',
                'invalid-name' => 'Nazwa jest wymagana i może mieć najwyżej 100 znaków.',
                'invalid-domain' => 'Wpisz nazwę domeny, np. erp.example.com. Adresy IP i symbole wieloznaczne nie są akceptowane.',
                'expiry-required' => 'Tokeny API muszą mieć datę wygaśnięcia.',
                'expiry-in-past' => 'Data wygaśnięcia musi być w przyszłości.',
            ],
        ],

        'api-sessions' => [
            'menu' => [
                'title' => 'Sesje API',
            ],

            'acl' => [
                'title' => 'Sesje API',
                'delete' => 'Unieważnij',
            ],

            'index' => [
                'title' => 'Sesje API',
                'info' => 'Administratorzy zalogowani do API administracyjnego z aplikacji webowych, mobilnych, POS lub desktopowych. Unieważnienie sesji natychmiast wylogowuje to urządzenie.',
            ],

            'fields' => [
                'id' => 'ID',
                'admin' => 'Administrator',
                'device' => 'Urządzenie',
                'client-type' => 'Typ klienta',
                'ip-address' => 'Adres IP logowania',
                'user-agent' => 'User agent logowania',
                'last-ip-address' => 'Ostatni adres IP',
                'last-user-agent' => 'Ostatni user agent',
                'last-used-at' => 'Ostatnio użyta',
                'last-channel' => 'Ostatni kanał',
                'refresh-expires-at' => 'Odświeżenie wygasa',
                'expires-at' => 'Wygasa',
                'status' => 'Status',
                'created-at' => 'Zalogowano',
            ],

            'datagrid' => [
                'revoke' => 'Unieważnij',
                'revoke-all' => 'Unieważnij wszystkie sesje tego administratora',
            ],

            'client-types' => [
                'web' => 'Web',
                'mobile' => 'Mobilna',
                'pos' => 'POS',
                'desktop' => 'Desktop',
                'other' => 'Inna',
            ],

            'errors' => [
                'not-active' => 'Ta sesja jest już unieważniona lub wygasła.',
            ],
        ],

        'api-audit' => [
            'menu' => [
                'title' => 'Audyt API',
            ],

            'acl' => [
                'title' => 'Historia zmian API',
                'delete' => 'Usuń historię',
            ],

            'index' => [
                'title' => 'Historia zmian API',
                'info' => 'Każde utworzenie, aktualizacja i usunięcie dokonane za pośrednictwem interfejsu API administratora, kto to zrobił, jaki token i co się zmieniło.',
                'cleanup-btn' => 'Usuń starsze logi',
                'cleanup-days' => 'Usuń logi starsze niż ta liczba dni',
                'cleanup-confirm' => 'Usunąć całą historię starszą niż podana liczba dni? Tego nie można cofnąć.',
            ],

            'view' => [
                'title' => 'Zmień',
                'back-btn' => 'Powrót',
                'admin' => 'Administrator',
                'token' => 'Żeton',
                'action' => 'Akcja',
                'resource' => 'Zasób',
                'method' => 'Metoda',
                'ip' => 'Adres IP',
                'date' => 'Data',
                'version' => 'Wersja',
                'url' => 'Punkt końcowy',
                'request-details' => 'Szczegóły żądania',
                'changes' => 'Zmiany',
                'field' => 'Pole',
                'old' => 'Stara wartość',
                'new' => 'Nowa wartość',
                'no-field-changes' => 'Dla tego wpisu nie zarejestrowano żadnych zmian na poziomie pola.',
                'same-request' => 'Inne zmiany w tym samym żądaniu',
                'version-chain' => 'Historia wersji tego rekordu',
            ],

            'datagrid' => [
                'id' => 'Identyfikator',
                'date' => 'Data',
                'admin' => 'Administrator',
                'token' => 'Żeton',
                'token-kind' => 'Rodzaj tokenu',
                'channel' => 'Kanał',
                'operation' => 'Operacja',
                'resource' => 'Zasób',
                'method' => 'Metoda',
                'ip' => 'IP',
                'view' => 'Zobacz',
                'delete' => 'Usuń',
            ],

            'events' => [
                'created' => 'Utworzono',
                'updated' => 'Zaktualizowano',
                'deleted' => 'Usunięto',
            ],

            'token-kinds' => [
                'session' => 'Sesja logowania',
                'api-token' => 'Token API',
                'integration' => 'Token integracji (usunięty)',
                'customer-session' => 'Sesja logowania klienta',
            ],

            'channels' => [
                'rest' => 'REST',
                'graphql' => 'GraphQL',
                'panel' => 'Panel administracyjny',
                'cli' => 'Polecenie konsoli',
            ],

            'deleted' => 'Usunięto :count rekordów historii.',
            'cleanup-input-required' => 'Podaj liczbę dni lub datę sprzątania.',
        ],

        'api-configuration' => [
            'title' => 'API',
            'info' => 'Ustawienia interfejsu API Bagisto i jego modułów administracyjnych.',
        ],
    ],
];
