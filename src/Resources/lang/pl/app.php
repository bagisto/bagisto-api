<?php

return [
    'graphql' => [
        'cart' => [
            'authentication-required'           => 'Token uwierzytelniający jest wymagany',
            'invalid-token'                     => 'Nieprawidłowy lub wygasły token uwierzytelniający',
            'unauthorized-access'               => 'Nieautoryzowany dostęp do koszyka',
            'authenticated-only'                => 'Tylko uwierzytelnieni użytkownicy mogą pobierać swoje koszyki',
            'merge-requires-auth'               => 'Scalanie koszyka gościa wymaga uwierzytelnienia',
            'unknown-operation'                 => 'Nieznana operacja koszyka',

            'cart-not-found'                    => 'Koszyk nie znaleziony',
            'guest-cart-not-found'              => 'Koszyk gościa nie znaleziony',
            'product-not-found'                 => 'Produkt nie znaleziony',

            'product-id-quantity-required'      => 'Identyfikator produktu i ilość są wymagane',
            'cart-item-id-quantity-required'    => 'Identyfikator pozycji koszyka i ilość są wymagane',
            'cart-item-id-required'             => 'Identyfikator pozycji koszyka jest wymagany',
            'item-ids-required'                 => 'Tablica identyfikatorów pozycji jest wymagana',
            'coupon-code-required'              => 'Kod kuponu jest wymagany',
            'address-data-required'             => 'Kraj, województwo i kod pocztowy są wymagane',

            'add-product-failed'                => 'Nie udało się dodać produktu do koszyka',
            'update-item-failed'                => 'Nie udało się zaktualizować pozycji koszyka',
            'remove-item-failed'                => 'Nie udało się usunąć pozycji koszyka',
            'apply-coupon-failed'               => 'Nie udało się zastosować kuponu',
            'remove-coupon-failed'              => 'Nie udało się usunąć kuponu',
            'move-to-wishlist-failed'           => 'Nie udało się przenieść pozycji do listy życzeń',
            'estimate-shipping-failed'          => 'Nie udało się oszacować kosztów wysyłki',

            'product-added-successfully'         => 'Produkt pomyślnie dodany do koszyka',
            'guest-cart-merged'                  => 'Koszyk gościa pomyślnie scalony',
            'using-authenticated-cart'           => 'Używanie koszyka uwierzytelnionego klienta',
            'cart-item-not-found'                => 'Pozycja koszyka nie znaleziona',
            'new-guest-cart-created'             => 'Utworzono nowy koszyk gościa z unikalnym tokenem sesji',
            'select-items-to-remove'             => 'Wybierz pozycje do usunięcia',
            'select-items-to-move-wishlist'      => 'Wybierz pozycje do przeniesienia na listę życzeń',
            'invalid-or-expired-token'           => 'Token koszyka jest nieprawidłowy lub wygasł. Utwórz nowy koszyk.',
            'invalid-token-of-login-user'        => 'Token zalogowanego użytkownika jest nieprawidłowy.',
        ],

        'token-verification' => [
            'invalid-operation'                 => 'Nieprawidłowa operacja',
            'invalid-input-data'                => 'Nieprawidłowe dane wejściowe',
            'token-required'                    => 'Token jest wymagany',
            'invalid-token-format'              => 'Nieprawidłowy format tokena',
            'token-not-found-or-expired'        => 'Token nie znaleziony lub wygasł',
            'customer-not-found'                => 'Klient nie znaleziony',
            'customer-account-suspended'        => 'Konto klienta jest zawieszone',
            'error-verifying-token'             => 'Błąd podczas weryfikacji tokena',
            'token-is-valid'                    => 'Token jest prawidłowy',
        ],

        'forgot-password' => [
            'invalid-operation'                 => 'Nieprawidłowa operacja',
            'invalid-input-data'                => 'Nieprawidłowe dane wejściowe',
            'email-required'                    => 'Adres e-mail jest wymagany',
            'reset-link-sent'                   => 'Link do resetowania został pomyślnie wysłany na Twój adres e-mail',
            'email-not-found'                   => 'Adres e-mail nie znaleziony',
            'error-sending-reset-link'          => 'Wystąpił błąd podczas wysyłania linku resetującego',
        ],

        'logout' => [
            'invalid-operation'                 => 'Nieprawidłowa operacja',
            'invalid-input-data'                => 'Nieprawidłowe dane wejściowe',
            'token-required'                    => 'Token jest wymagany',
            'invalid-token-format'              => 'Nieprawidłowy format tokena',
            'logged-out-successfully'           => 'Pomyślnie wylogowano',
            'token-not-found-or-expired'        => 'Token nie znaleziony lub już wygasł',
            'error-during-logout'               => 'Błąd podczas wylogowywania',
        ],

        'address' => [
            'deleted-successfully'              => 'Adres pomyślnie usunięty',
            'authentication-required'           => 'Token uwierzytelniający jest wymagany',
            'invalid-token'                     => 'Nieprawidłowy lub wygasły token',
            'unknown-operation'                 => 'Nieznana operacja',
            'address-id-required'               => 'Identyfikator adresu jest wymagany',
            'address-not-found'                 => 'Adres nie znaleziony lub nie należy do tego klienta',
            'retrieved'                         => 'Adresy pomyślnie pobrane',
            'fetch-failed'                      => 'Nie udało się pobrać adresów:',
        ],

        'customer-profile' => [
            'authentication-required'           => 'Token uwierzytelniający jest wymagany. Podaj token w danych wejściowych zapytania',
            'invalid-token'                     => 'Nieprawidłowy lub wygasły token',
        ],

        'customer' => [
            'password-mismatch'                 => 'Hasło i potwierdzenie hasła nie są zgodne',
            'confirm-password-required'         => 'Potwierdzenie hasła jest wymagane przy zmianie hasła',
            'unauthenticated'                   => 'Nieuwierzytelniony. Zaloguj się, aby wykonać tę akcję',
        ],

        'product-review' => [
            'product-id-required'               => 'Identyfikator produktu jest wymagany',
            'product-not-found'                 => 'Produkt nie znaleziony',
            'rating-invalid'                    => 'Ocena musi być pomiędzy 1 a 5',
            'title-required'                    => 'Tytuł recenzji jest wymagany',
            'comment-required'                  => 'Komentarz recenzji jest wymagany',
        ],

        'product' => [
            'not-found-with-sku'                => 'No product found with SKU',
            'not-found-with-url-key'            => 'No product found with URL key',
            'parameters-required'               => 'At least one of the following parameters must be provided: "sku", "id", "urlKey"',
        ],

        'auth' => [
            'no-token-provided'                 => 'Nie podano tokena uwierzytelniającego. Podaj token w nagłówku Authorization jako "Bearer <token>" lub w polu input.token',
            'invalid-or-expired-token'          => 'Nieprawidłowy lub wygasły token',
            'request-not-found'                 => 'Żądanie nie znalezione w kontekście',
            'token-required'                    => 'Authentication token is required. Please provide the token either in the GraphQL mutation input field or in the Authorization header as "Bearer <token>"',
            'unknown-resource'                  => 'Nieznany zasób',
            'cannot-update-other-profile'       => 'Brak autoryzacji: Nie można zaktualizować profilu innego klienta',
        ],

        'upload' => [
            'invalid-base64'                    => 'Nieprawidłowe dane obrazu zakodowane w base64',
            'size-exceeds-limit'                => 'Rozmiar obrazu nie może przekraczać 5 MB',
            'invalid-format'                    => 'Nieprawidłowy format obrazu. Podaj obraz zakodowany w base64 ze schematem data URI (data:image/jpeg;base64,...)',
            'failed'                            => 'Przesyłanie obrazu nie powiodło się',
        ],

        'attribute' => [
            'code-already-exists'               => 'Kod atrybutu już istnieje',
        ],

        'login' => [
            'invalid-credentials'               => 'Nieprawidłowy adres e-mail lub hasło',
            'account-suspended'                 => 'Twoje konto zostało zawieszone',
            'successful'                        => 'Zalogowano pomyślnie',
            'invalid-request'                   => 'Nieprawidłowe żądanie logowania',
        ],

        'checkout' => [
            'invalid-input'                     => 'Nieprawidłowe dane wejściowe dla operacji realizacji zamówienia',
            'billing-address-required'          => 'Adres rozliczeniowy jest wymagany',
            'shipping-address-required'         => 'Adres wysyłki jest wymagany dla przesyłek',
            'address-save-failed'               => 'Nie udało się zapisać adresu',
            'address-saved'                     => 'Adres pomyślnie zapisany',
            'shipping-method-required'          => 'Metoda wysyłki jest wymagana',
            'invalid-shipping-method'           => 'Nieprawidłowa lub niedostępna metoda wysyłki',
            'shipping-method-save-failed'       => 'Nie udało się zapisać metody wysyłki',
            'shipping-method-saved'             => 'Metoda wysyłki pomyślnie zapisana',
            'shipping-method-error'             => 'Błąd podczas zapisywania metody wysyłki',
            'payment-method-required'           => 'Metoda płatności jest wymagana',
            'invalid-payment-method'            => 'Nieprawidłowa lub niedostępna metoda płatności',
            'payment-method-save-failed'        => 'Nie udało się zapisać metody płatności',
            'payment-method-saved'              => 'Metoda płatności pomyślnie zapisana',
            'payment-method-error'              => 'Błąd podczas zapisywania metody płatności',
            'order-creation-failed'             => 'Tworzenie zamówienia nie powiodło się: identyfikator zamówienia jest pusty lub zamówienie nie zostało zapisane',
            'order-retrieval-failed'            => 'Nie udało się pobrać utworzonego zamówienia',
            'order-creation-error'              => 'Nie udało się utworzyć zamówienia',
            'cart-empty'                        => 'Koszyk jest pusty',
            'account-suspended'                 => 'Twoje konto zostało zawieszone. Skontaktuj się z pomocą techniczną.',
            'account-inactive'                  => 'Twoje konto jest nieaktywne. Skontaktuj się z pomocą techniczną.',
            'minimum-order-not-met'             => 'Minimalna kwota zamówienia wynosi :amount',
            'email-required'                    => 'Adres e-mail jest wymagany do utworzenia zamówienia',
            'unknown-operation'                 => 'Nieznana operacja realizacji zamówienia',
        ],

        'customer-addresses' => [
            'token-required'                    => 'Token jest wymagany do pobrania adresów klienta',
            'invalid-or-expired-token'          => 'Nieprawidłowy lub wygasły token',
            'token-validation-failed'           => 'Weryfikacja tokena nie powiodła się',
        ],

        'product' => [
            'type'                              => 'Typ produktu',
            'attribute-family'                  => 'Rodzina atrybutów',
            'sku'                               => 'SKU',
            'name'                              => 'Nazwa',
            'description'                       => 'Opis',
            'short-description'                 => 'Krótki opis',
            'status'                            => 'Status',
            'new'                               => 'Nowy',
            'featured'                          => 'Wyróżniony',
            'price'                             => 'Cena',
            'special-price'                     => 'Cena specjalna',
            'weight'                            => 'Waga',
            'cost'                              => 'Koszt',
            'length'                            => 'Długość',
            'width'                             => 'Szerokość',
            'height'                            => 'Wysokość',
            'color'                             => 'Kolor',
            'size'                              => 'Rozmiar',
            'brand'                             => 'Marka',
            'super-attributes'                  => 'Superatrybuty',
        ],

        'compare-item' => [
            'id-required'                       => 'Identyfikator przedmiotu porównawczego jest wymagany',
            'invalid-id-format'                 => 'Nieprawidłowy format identyfikatora. Oczekiwany format IRI taki jak "/api/shop/compare-items/1" lub identyfikator liczbowy',
            'not-found'                         => 'Przedmiot porównawczy nie znaleziony',
            'product-id-required'               => 'Identyfikator produktu jest wymagany',
            'customer-id-required'              => 'Identyfikator klienta jest wymagany',
            'product-not-found'                 => 'Produkt nie znaleziony',
            'customer-not-found'                => 'Klient nie znaleziony',
            'already-exists'                    => 'Ten produkt jest już na Twojej liście porównawczej',
        ],

        'downloadable-product' => [
            'download-link-not-found'           => 'Link pobierania nie znaleziony lub wygasł',
            'purchased-link-not-found'          => 'Zakupiony link nie znaleziony',
            'file-not-found'                    => 'Plik nie znaleziony',
            'download-successful'               => 'Plik gotowy do pobrania',
            'token-required'                    => 'Wymagany jest token pobierania',
            'invalid-token'                     => 'Token pobierania jest nieprawidłowy lub wygasł',
            'token-expired'                     => 'Token pobierania wygasł. Proszę wygenerować nowy',
            'access-denied'                     => 'Dostęp odmówiony: Nie masz uprawnień do pobrania tego pliku',
            'redirect-external-url'             => 'Przekierowanie do zewnętrznego adresu URL pobierania',
            'file-error'                        => 'Podczas przetwarzania żądania pobierania wystąpił błąd',
            'unauthorized-access'               => 'Nieautoryzowany dostęp do zasobu pobierania',
        ],
    ],

    'integration' => [
        'menu' => [
            'title'  => 'Integracja',
            'tokens' => 'Żetony',
        ],

        'history' => [
            'menu' => [
                'title' => 'Historia',
            ],

            'acl' => [
                'title'  => 'Historia zmian API',
                'delete' => 'Usuń historię',
            ],

            'index' => [
                'title'           => 'Historia zmian API',
                'info'            => 'Każde utworzenie, aktualizacja i usunięcie dokonane za pośrednictwem interfejsu API administratora, kto to zrobił, jaki token i co się zmieniło.',
                'cleanup-btn'     => 'Usuń starsze logi',
                'cleanup-days'    => 'Usuń logi starsze niż ta liczba dni',
                'cleanup-confirm' => 'Usunąć całą historię starszą niż podana liczba dni? Tego nie można cofnąć.',
            ],

            'view' => [
                'title'             => 'Zmień',
                'back-btn'          => 'Powrót',
                'admin'             => 'Administrator',
                'token'             => 'Żeton',
                'action'            => 'Akcja',
                'resource'          => 'Zasób',
                'method'            => 'Metoda',
                'ip'                => 'Adres IP',
                'date'              => 'Data',
                'version'           => 'Wersja',
                'url'               => 'Punkt końcowy',
                'request-details'   => 'Szczegóły żądania',
                'changes'           => 'Zmiany',
                'field'             => 'Pole',
                'old'               => 'Stara wartość',
                'new'               => 'Nowa wartość',
                'no-field-changes'  => 'Dla tego wpisu nie zarejestrowano żadnych zmian na poziomie pola.',
                'same-request'      => 'Inne zmiany w tym samym żądaniu',
                'version-chain'     => 'Historia wersji tego rekordu',
            ],

            'datagrid' => [
                'id'        => 'Identyfikator',
                'date'      => 'Data',
                'admin'     => 'Administrator',
                'token'     => 'Żeton',
                'action'    => 'Akcja',
                'operation' => 'Operacja',
                'resource'  => 'Zasób',
                'version'   => 'Wersja',
                'method'    => 'Metoda',
                'ip'        => 'IP',
                'view'      => 'Zobacz',
                'delete'    => 'Usuń',
            ],

            'events' => [
                'created' => 'Utworzono',
                'updated' => 'Zaktualizowano',
                'deleted' => 'Usunięto',
            ],

            'deleted'                => 'Usunięto :count rekordów historii.',
            'cleanup-input-required' => 'Podaj liczbę dni lub datę sprzątania.',
        ],

        'acl' => [
            'title'      => 'Integracja',
            'create'     => 'Stwórz Integrację',
            'edit'       => 'Edytuj integrację',
            'delete'     => 'Unieważnij token integracji',
            'generate'   => 'Wygeneruj token integracji',
            'regenerate' => 'Wygeneruj ponownie token integracji',
        ],

        'index' => [
            'title'      => 'Integracje',
            'create-btn' => 'Stwórz Integrację',
        ],

        'create' => [
            'title'    => 'Stwórz Integrację',
            'save-btn' => 'Zapisz',
            'back-btn' => 'Powrót',
        ],

        'edit' => [
            'title'          => 'Edytuj integrację',
            'save-btn'       => 'Zapisz',
            'back-btn'       => 'Powrót',
            'generate-btn'   => 'Wygeneruj token',
            'regenerate-btn' => 'Zregeneruj token',
            'revoke-btn'     => 'Unieważnij token',
            'copy-btn'       => 'Kopiuj',
            'token-warning'  => 'Zapisz teraz ten token — nie będzie on wyświetlany ponownie.',
            'token-label'    => 'Żeton',
            'not-generated'  => 'Jeszcze nie wygenerowany',
            'masked'         => '(Przechowywane — wyświetlane tylko raz podczas generowania)',
            'history-banner' => 'Ten token nie jest już aktywny.',
        ],

        'fields' => [
            'name'                  => 'Imię',
            'description'           => 'Opis',
            'assign-user'           => 'Przypisz użytkownika',
            'permission-type'       => 'Typ zezwolenia',
            'access-control'        => 'Kontrola dostępu',
            'general'               => 'Generał',
            'token-settings'        => 'Ustawienia tokena',
            'valid-till'            => 'Ważne do',
            'rate-limit-per-minute' => 'Limit szybkości (na minutę)',
            'rate-limit-per-day'    => 'Limit stawki (na dzień)',
            'never-expires'         => 'Nigdy nie wygasa',
            'expires-on'            => 'Wygasa w dniu',
            'unlimited'             => 'Nieograniczony',
            'limit-to'              => 'Ogranicz do',
            'requests-per-minute'   => 'żądania / minutę',
            'requests-per-day'      => 'żądania / dzień',
            'select-admin'          => 'Wybierz administratora',
            'no-available-admins'   => 'Brak dostępnych administratorów — każdy administrator ma już aktywny token.',
            'same-as-web-hint'      => 'Token będzie na żywo odzwierciedlał bieżące uprawnienia roli przypisanego administratora.',
            'ip-allowlist'          => 'Lista dozwolonych adresów IP',
            'ip-any'                => 'Dowolny adres IP (domyślny)',
            'ip-restricted'         => 'Ograniczone do określonych adresów IP',
            'ip-list-hint'          => 'Jeden wpis w każdym wierszu. Obsługuje IPv4, IPv6 i CIDR (np. 10.0.0.0/24 lub 2001:db8::/32). Pozostaw puste, aby zezwolić na wszystkie adresy IP.',
        ],

        'permission_type' => [
            'all'         => 'Wszystko',
            'custom'      => 'Niestandardowe',
            'same_as_web' => 'Takie same jak uprawnienia internetowe',
        ],

        'status' => [
            'draft'       => 'Wersja robocza',
            'active'      => 'Aktywny',
            'revoked'     => 'Unieważnione',
            'regenerated' => 'Zregenerowany',
        ],

        'datagrid' => [
            'id'              => 'Identyfikator',
            'name'            => 'Imię',
            'admin'           => 'Administrator',
            'token'           => 'Żeton',
            'status'          => 'Stan',
            'permission-type' => 'Typ zezwolenia',
            'expires-at'      => 'Ważne do',
            'last-used-at'    => 'Ostatnio używany',
            'created-at'      => 'Utworzono o godz',
            'edit'            => 'Edytuj',
            'revoke'          => 'Odwołaj',
        ],

        'messages' => [
            'draft-created'          => 'Integracja utworzona. Wygeneruj token, aby zacząć z niego korzystać.',
            'updated'                => 'Integracja została pomyślnie zaktualizowana.',
            'generated'              => 'Wygenerowano token. Skopiuj go teraz — nie będzie wyświetlany ponownie.',
            'regenerated'            => 'Token został zregenerowany. Skopiuj teraz nowy token — nie będzie on wyświetlany ponownie.',
            'revoked'                => 'Token został pomyślnie unieważniony.',
            'generate-only-draft'    => 'Tylko wersje robocze integracji mogą mieć wygenerowany token.',
            'regenerate-only-active' => 'Regenerować można tylko aktywne tokeny.',
            'cannot-edit-historic'   => 'Nie można edytować unieważnionych lub zregenerowanych tokenów.',
            'already-inactive'       => 'Ten token jest już nieaktywny.',
        ],

        'errors' => [
            'admin-has-token' => 'Wybrany administrator posiada już aktywny token integracji.',
        ],

        'validation' => [
            'ip-invalid'           => 'Każdy dozwolony adres IP musi być prawidłowym adresem IPv4 lub IPv6 (obsługiwana notacja CIDR).',
            'cidr-prefix-invalid'  => 'Prefiks CIDR jest nieprawidłowy dla danej wersji IP.',
        ],

        'configuration' => [
            'api' => [
                'title' => 'API',
                'info'  => 'Ustawienia interfejsu API Bagisto i jego modułów administracyjnych.',
            ],
            'integration' => [
                'title' => 'Integracja',
                'info'  => 'Zarządzaj wtyczką integracji API używaną do wydawania tokenów API administratora.',
            ],
            'settings' => [
                'title'  => 'Ustawienia modułu',
                'info'   => 'Włącz lub wyłącz wtyczkę Integracja API. Po wyłączeniu menu na pasku bocznym jest ukryte, a strony zwracają 404.',
                'enable' => 'Włącz moduł integracji API',
            ],
        ],

        'emails' => [
            'generated' => [
                'subject'  => 'Wygenerowano nowy token API: :name',
                'greeting' => 'Na Twoim koncie właśnie wygenerowano token integracji API o nazwie „:name”.',
            ],
            'regenerated' => [
                'subject'  => 'Twój token API został zregenerowany: :name',
                'greeting' => 'Token integracji API o nazwie „:name” został właśnie zregenerowany. Poprzedni token przestał działać – ważny jest tylko nowy.',
            ],
            'revoked' => [
                'subject'  => 'Twój token API został unieważniony: :name',
                'greeting' => 'Token integracji API o nazwie „:name” został unieważniony. Każdy klient korzystający z niego utracił dostęp.',
            ],

            'details' => [
                'name' => 'Nazwa tokenu',
                'date' => 'Data',
                'ip'   => 'Z IP',
            ],

            'revoke-hint'   => 'Jeśli się tego nie spodziewałeś, natychmiast unieważnij token za pomocą przycisku poniżej.',
            'revoke-btn'    => 'Unieważnij ten token',
            'revoke-expiry' => 'Ten link odwoławczy jest ważny przez 7 dni. Następnie zaloguj się do panelu administracyjnego, aby zarządzać tokenem.',
            'no-action'     => 'Nie jest wymagane żadne działanie — ten e-mail stanowi jedynie potwierdzenie.',
        ],

        'revoke-confirmation' => [
            'title'                    => 'Unieważnij token API',
            'success-title'            => 'Token unieważniony',
            'success-message'          => 'Token „:name” został unieważniony. Każdy klient korzystający z niego natychmiast traci dostęp.',
            'already-inactive-title'   => 'Token jest już nieaktywny',
            'already-inactive-message' => 'Token „:name” został już unieważniony lub zregenerowany. Żadne dalsze działania nie są potrzebne.',
        ],

        'confirm' => [
            'generate' => [
                'title'   => 'Wygeneruj token',
                'message' => 'Wygenerować token teraz? Tekst jawny zostanie wyświetlony tylko raz — skopiuj go przed opuszczeniem strony.',
            ],
            'regenerate' => [
                'title'   => 'Zregeneruj token',
                'message' => 'Zregenerować token? Stary token natychmiast przestanie działać, a nowy tekst jawny zostanie wyświetlony tylko raz.',
            ],
            'revoke' => [
                'title'   => 'Unieważnij token',
                'message' => 'Unieważnić ten token? Każdy klient korzystający z niego natychmiast utraci dostęp. Tej akcji nie można cofnąć.',
            ],
        ],
    ],
];
