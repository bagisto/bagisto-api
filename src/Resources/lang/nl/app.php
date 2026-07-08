<?php

return [
    'graphql' => [
        'cart' => [
            'authentication-required' => 'Authenticatietoken is vereist',
            'invalid-token' => 'Ongeldig of verlopen authenticatietoken',
            'unauthorized-access' => 'Ongeautoriseerde toegang tot winkelwagen',
            'authenticated-only' => 'Alleen geverifieerde gebruikers kunnen hun winkelwagen ophalen',
            'merge-requires-auth' => 'Samenvoegen van gastwinkelwagen vereist authenticatie',
            'unknown-operation' => 'Onbekende winkelwagenbewerking',

            'cart-not-found' => 'Winkelwagen niet gevonden',
            'guest-cart-not-found' => 'Gastwinkelwagen niet gevonden',
            'product-not-found' => 'Product niet gevonden',

            'product-id-quantity-required' => 'Product-ID en aantal zijn vereist',
            'cart-item-id-quantity-required' => 'Winkelwagenitem-ID en aantal zijn vereist',
            'cart-item-id-required' => 'Winkelwagenitem-ID is vereist',
            'item-ids-required' => 'Array met item-ID\'s is vereist',
            'coupon-code-required' => 'Couponcode is vereist',
            'address-data-required' => 'Land, provincie en postcode zijn vereist',

            'add-product-failed' => 'Kan product niet aan winkelwagen toevoegen',
            'update-item-failed' => 'Kan winkelwagenitem niet bijwerken',
            'remove-item-failed' => 'Kan winkelwagenitem niet verwijderen',
            'apply-coupon-failed' => 'Kan coupon niet toepassen',
            'remove-coupon-failed' => 'Kan coupon niet verwijderen',
            'move-to-wishlist-failed' => 'Kan item niet naar verlanglijst verplaatsen',
            'estimate-shipping-failed' => 'Kan verzendkosten niet schatten',

            'product-added-successfully' => 'Product succesvol aan winkelwagen toegevoegd',
            'guest-cart-merged' => 'Gastwinkelwagen succesvol samengevoegd',
            'using-authenticated-cart' => 'Geverifieerde klantwinkelwagen wordt gebruikt',
            'cart-item-not-found' => 'Winkelwagenitem niet gevonden',
            'new-guest-cart-created' => 'Nieuwe gastwinkelwagen aangemaakt met uniek sessietoken',
            'select-items-to-remove' => 'Selecteer items om te verwijderen',
            'select-items-to-move-wishlist' => 'Selecteer items om naar de verlanglijst te verplaatsen',
            'invalid-or-expired-token' => 'Winkelwagentoken is ongeldig of verlopen. Maak een nieuwe winkelwagen aan.',
            'invalid-token-of-login-user' => 'Token van ingelogde gebruiker is ongeldig.',
        ],

        'token-verification' => [
            'invalid-operation' => 'Ongeldige bewerking',
            'invalid-input-data' => 'Ongeldige invoergegevens',
            'token-required' => 'Token is vereist',
            'invalid-token-format' => 'Ongeldig tokenformaat',
            'token-not-found-or-expired' => 'Token niet gevonden of verlopen',
            'customer-not-found' => 'Klant niet gevonden',
            'customer-account-suspended' => 'Klantaccount is opgeschort',
            'error-verifying-token' => 'Fout bij het verifiëren van token',
            'token-is-valid' => 'Token is geldig',
        ],

        'forgot-password' => [
            'invalid-operation' => 'Ongeldige bewerking',
            'invalid-input-data' => 'Ongeldige invoergegevens',
            'email-required' => 'E-mailadres is vereist',
            'reset-link-sent' => 'Resetlink succesvol naar uw e-mailadres verzonden',
            'email-not-found' => 'E-mailadres niet gevonden',
            'error-sending-reset-link' => 'Er is een fout opgetreden bij het verzenden van de resetlink',
        ],

        'logout' => [
            'invalid-operation' => 'Ongeldige bewerking',
            'invalid-input-data' => 'Ongeldige invoergegevens',
            'token-required' => 'Token is vereist',
            'invalid-token-format' => 'Ongeldig tokenformaat',
            'logged-out-successfully' => 'Succesvol uitgelogd',
            'token-not-found-or-expired' => 'Token niet gevonden of al verlopen',
            'error-during-logout' => 'Fout tijdens het uitloggen',
        ],

        'address' => [
            'deleted-successfully' => 'Adres succesvol verwijderd',
            'authentication-required' => 'Authenticatietoken is vereist',
            'invalid-token' => 'Ongeldig of verlopen token',
            'unknown-operation' => 'Onbekende bewerking',
            'address-id-required' => 'Adres-ID is vereist',
            'address-not-found' => 'Adres niet gevonden of behoort niet toe aan deze klant',
            'retrieved' => 'Adressen succesvol opgehaald',
            'fetch-failed' => 'Kan adressen niet ophalen:',
        ],

        'customer-profile' => [
            'authentication-required' => 'Authenticatietoken is vereist. Geef het token op in de query-invoer',
            'invalid-token' => 'Ongeldig of verlopen token',
        ],

        'customer' => [
            'password-mismatch' => 'Wachtwoord en bevestigingswachtwoord komen niet overeen',
            'confirm-password-required' => 'Bevestigingswachtwoord is vereist bij het wijzigen van het wachtwoord',
            'unauthenticated' => 'Niet geverifieerd. Log in om deze actie uit te voeren',
        ],

        'product-review' => [
            'product-id-required' => 'Product-ID is vereist',
            'product-not-found' => 'Product niet gevonden',
            'rating-invalid' => 'Beoordeling moet tussen 1 en 5 liggen',
            'title-required' => 'Titel van beoordeling is vereist',
            'comment-required' => 'Opmerking bij beoordeling is vereist',
        ],

        'product' => [
            'not-found-with-sku' => 'No product found with SKU',
            'not-found-with-url-key' => 'No product found with URL key',
            'parameters-required' => 'At least one of the following parameters must be provided: "sku", "id", "urlKey"',
        ],

        'auth' => [
            'no-token-provided' => 'Geen authenticatietoken opgegeven. Geef het token op in de Authorization-header als "Bearer <token>" of in het veld input.token',
            'invalid-or-expired-token' => 'Ongeldig of verlopen token',
            'request-not-found' => 'Verzoek niet gevonden in context',
            'token-required' => 'Authentication token is required. Please provide the token either in the GraphQL mutation input field or in the Authorization header as "Bearer <token>"',
            'unknown-resource' => 'Onbekende bron',
            'cannot-update-other-profile' => 'Niet geautoriseerd: kan het profiel van een andere klant niet bijwerken',
        ],

        'upload' => [
            'invalid-base64' => 'Ongeldige base64-gecodeerde afbeeldingsgegevens',
            'size-exceeds-limit' => 'Afbeeldingsgrootte mag niet groter zijn dan 5 MB',
            'invalid-format' => 'Ongeldig afbeeldingsformaat. Geef een base64-gecodeerde afbeelding op met data-URI-schema (data:image/jpeg;base64,...)',
            'failed' => 'Uploaden van afbeelding mislukt',
        ],

        'attribute' => [
            'code-already-exists' => 'De attribuutcode bestaat al',
        ],

        'login' => [
            'invalid-credentials' => 'Ongeldig e-mailadres of wachtwoord',
            'account-suspended' => 'Uw account is opgeschort',
            'successful' => 'U bent succesvol ingelogd',
            'invalid-request' => 'Ongeldig inlogverzoek',
        ],

        'checkout' => [
            'invalid-input' => 'Ongeldige invoergegevens voor afrekenbewerking',
            'billing-address-required' => 'Factuuradres is vereist',
            'shipping-address-required' => 'Verzendadres is vereist voor zendingen',
            'address-save-failed' => 'Kan adres niet opslaan',
            'address-saved' => 'Adres succesvol opgeslagen',
            'shipping-method-required' => 'Verzendmethode is vereist',
            'invalid-shipping-method' => 'Ongeldige of niet-beschikbare verzendmethode',
            'shipping-method-save-failed' => 'Kan verzendmethode niet opslaan',
            'shipping-method-saved' => 'Verzendmethode succesvol opgeslagen',
            'shipping-method-error' => 'Fout bij het opslaan van verzendmethode',
            'payment-method-required' => 'Betaalmethode is vereist',
            'invalid-payment-method' => 'Ongeldige of niet-beschikbare betaalmethode',
            'payment-method-save-failed' => 'Kan betaalmethode niet opslaan',
            'payment-method-saved' => 'Betaalmethode succesvol opgeslagen',
            'payment-method-error' => 'Fout bij het opslaan van betaalmethode',
            'order-creation-failed' => 'Aanmaken van bestelling mislukt: bestellings-ID is null of bestelling is niet opgeslagen',
            'order-retrieval-failed' => 'Kan aangemaakte bestelling niet ophalen',
            'order-creation-error' => 'Kan bestelling niet aanmaken',
            'cart-empty' => 'Winkelwagen is leeg',
            'account-suspended' => 'Uw account is opgeschort. Neem contact op met de klantenservice.',
            'account-inactive' => 'Uw account is inactief. Neem contact op met de klantenservice.',
            'minimum-order-not-met' => 'Minimaal bestelbedrag is :amount',
            'email-required' => 'E-mailadres is vereist voor het aanmaken van een bestelling',
            'unknown-operation' => 'Onbekende afrekenbewerking',
        ],

        'customer-addresses' => [
            'token-required' => 'Token is vereist om klantadressen op te halen',
            'invalid-or-expired-token' => 'Ongeldig of verlopen token',
            'token-validation-failed' => 'Tokenvalidatie mislukt',
        ],

        'product' => [
            'type' => 'Producttype',
            'attribute-family' => 'Attribuutfamilie',
            'sku' => 'SKU',
            'name' => 'Naam',
            'description' => 'Beschrijving',
            'short-description' => 'Korte beschrijving',
            'status' => 'Status',
            'new' => 'Nieuw',
            'featured' => 'Uitgelicht',
            'price' => 'Prijs',
            'special-price' => 'Speciale prijs',
            'weight' => 'Gewicht',
            'cost' => 'Kostprijs',
            'length' => 'Lengte',
            'width' => 'Breedte',
            'height' => 'Hoogte',
            'color' => 'Kleur',
            'size' => 'Maat',
            'brand' => 'Merk',
            'super-attributes' => 'Superattributen',
        ],

        'compare-item' => [
            'id-required' => 'Vergelijk item-ID is vereist',
            'invalid-id-format' => 'Ongeldig ID-formaat. IRI-indeling verwacht zoals "/api/shop/compare-items/1" of numerieke ID',
            'not-found' => 'Vergelijk item niet gevonden',
            'product-id-required' => 'Product-ID is vereist',
            'customer-id-required' => 'Klant-ID is vereist',
            'product-not-found' => 'Product niet gevonden',
            'customer-not-found' => 'Klant niet gevonden',
            'already-exists' => 'Dit product staat al op uw vergelijkingslijst',
        ],

        'downloadable-product' => [
            'download-link-not-found' => 'Downloadlink niet gevonden of verlopen',
            'purchased-link-not-found' => 'Aankoop link niet gevonden',
            'file-not-found' => 'Bestand niet gevonden',
            'download-successful' => 'Bestand gereed voor download',
            'token-required' => 'Download token is vereist',
            'invalid-token' => 'Download token ongeldig of verlopen',
            'token-expired' => 'Download token is verlopen. Genereer alstublieft een nieuwe',
            'access-denied' => 'Toegang geweigerd: U bent niet gemachtigd dit bestand te downloaden',
            'redirect-external-url' => 'Omleiden naar externe download-URL',
            'file-error' => 'Er is een fout opgetreden bij het verwerken van uw downloadverzoek',
            'unauthorized-access' => 'Ongeautoriseerde toegang tot downloadresource',
        ],
    ],

    'integration' => [
        'menu' => [
            'title' => 'Integratie',
            'tokens' => 'Tokens',
        ],

        'history' => [
            'menu' => [
                'title' => 'Geschiedenis',
            ],

            'acl' => [
                'title' => 'Geschiedenis van API-wijzigingen',
                'delete' => 'Geschiedenis verwijderen',
            ],

            'index' => [
                'title' => 'Geschiedenis van API-wijzigingen',
                'info' => 'Elke creatie, update en verwijdering gebeurt via de admin API, met wie het heeft gedaan, welk token en wat er is veranderd.',
                'cleanup-btn' => 'Verwijder oudere logboeken',
                'cleanup-days' => 'Verwijder logboeken die ouder zijn dan dit aantal dagen',
                'cleanup-confirm' => 'Alle geschiedenis verwijderen die ouder is dan het opgegeven aantal dagen? Dit kan niet ongedaan worden gemaakt.',
            ],

            'view' => [
                'title' => 'Verandering',
                'back-btn' => 'Terug',
                'admin' => 'Beheerder',
                'token' => 'Token',
                'action' => 'Actie',
                'resource' => 'Bron',
                'method' => 'Methode',
                'ip' => 'IP-adres',
                'date' => 'Datum',
                'version' => 'Versie',
                'url' => 'Eindpunt',
                'request-details' => 'Details aanvragen',
                'changes' => 'Veranderingen',
                'field' => 'Veld',
                'old' => 'Oude waarde',
                'new' => 'Nieuwe waarde',
                'no-field-changes' => 'Voor deze invoer zijn geen wijzigingen op veldniveau geregistreerd.',
                'same-request' => 'Andere wijzigingen in hetzelfde verzoek',
                'version-chain' => 'Versiegeschiedenis van deze plaat',
            ],

            'datagrid' => [
                'id' => 'Identiteitskaart',
                'date' => 'Datum',
                'admin' => 'Beheerder',
                'token' => 'Token',
                'action' => 'Actie',
                'operation' => 'Operatie',
                'resource' => 'Bron',
                'version' => 'Versie',
                'method' => 'Methode',
                'ip' => 'IP',
                'view' => 'Bekijk',
                'delete' => 'Verwijderen',
            ],

            'events' => [
                'created' => 'Gemaakt',
                'updated' => 'Bijgewerkt',
                'deleted' => 'Verwijderd',
            ],

            'deleted' => ':count geschiedenisrecord(s) verwijderd.',
            'cleanup-input-required' => 'Geef een aantal dagen of een datum op voor de opruiming.',
        ],

        'acl' => [
            'title' => 'Integratie',
            'create' => 'Integratie creëren',
            'edit' => 'Integratie bewerken',
            'delete' => 'Integratietoken intrekken',
            'generate' => 'Integratietoken genereren',
            'regenerate' => 'Integratietoken opnieuw genereren',
        ],

        'index' => [
            'title' => 'Integraties',
            'create-btn' => 'Integratie creëren',
        ],

        'create' => [
            'title' => 'Integratie creëren',
            'save-btn' => 'Opslaan',
            'back-btn' => 'Terug',
        ],

        'edit' => [
            'title' => 'Integratie bewerken',
            'save-btn' => 'Opslaan',
            'back-btn' => 'Terug',
            'generate-btn' => 'Token genereren',
            'regenerate-btn' => 'Token opnieuw genereren',
            'revoke-btn' => 'Token intrekken',
            'copy-btn' => 'Kopieer',
            'token-warning' => 'Bewaar dit token nu. Het wordt niet meer getoond.',
            'token-label' => 'Token',
            'not-generated' => 'Nog niet gegenereerd',
            'masked' => '(Opgeslagen — slechts één keer getoond bij generatie)',
            'history-banner' => 'Dit token is niet langer actief.',
        ],

        'fields' => [
            'name' => 'Naam',
            'description' => 'Beschrijving',
            'assign-user' => 'Gebruiker toewijzen',
            'permission-type' => 'Toestemmingstype',
            'access-control' => 'Toegangscontrole',
            'general' => 'Algemeen',
            'token-settings' => 'Token-instellingen',
            'valid-till' => 'Geldig tot',
            'rate-limit-per-minute' => 'Tarieflimiet (per minuut)',
            'rate-limit-per-day' => 'Tarieflimiet (per dag)',
            'never-expires' => 'Verloopt nooit',
            'expires-on' => 'Verloopt op',
            'unlimited' => 'Onbeperkt',
            'limit-to' => 'Beperk tot',
            'requests-per-minute' => 'verzoeken / minuut',
            'requests-per-day' => 'aanvragen/dag',
            'select-admin' => 'Selecteer een beheerder',
            'no-available-admins' => 'Geen beheerders beschikbaar: elke beheerder heeft al een actief token.',
            'same-as-web-hint' => 'Token weerspiegelt live de huidige rolrechten van de toegewezen beheerder.',
            'ip-allowlist' => 'Toegestane IP-lijst',
            'ip-any' => 'Elk IP-adres (standaard)',
            'ip-restricted' => 'Beperkt tot specifieke IP\'s',
            'ip-list-hint' => 'Eén invoer per regel. Ondersteunt IPv4, IPv6 en CIDR (bijvoorbeeld 10.0.0.0/24 of 2001:db8::/32). Laat dit leeg om alle IP\'s toe te staan.',
        ],

        'permission_type' => [
            'all' => 'Allemaal',
            'custom' => 'Aangepast',
            'same_as_web' => 'Hetzelfde als Webtoestemming',
        ],

        'status' => [
            'draft' => 'Diepgang',
            'active' => 'Actief',
            'revoked' => 'Ingetrokken',
            'regenerated' => 'Geregenereerd',
        ],

        'datagrid' => [
            'id' => 'Identiteitskaart',
            'name' => 'Naam',
            'admin' => 'Beheerder',
            'token' => 'Token',
            'status' => 'Status',
            'permission-type' => 'Toestemmingstype',
            'expires-at' => 'Geldig tot',
            'last-used-at' => 'Laatst gebruikt',
            'created-at' => 'Gemaakt op',
            'edit' => 'Bewerken',
            'revoke' => 'Intrekken',
        ],

        'messages' => [
            'draft-created' => 'Integratie gecreëerd. Genereer het token om het te gaan gebruiken.',
            'updated' => 'Integratie is succesvol bijgewerkt.',
            'generated' => 'Token gegenereerd. Kopieer het nu. Het wordt niet meer weergegeven.',
            'regenerated' => 'Token opnieuw gegenereerd. Kopieer nu het nieuwe token. Het wordt niet meer getoond.',
            'revoked' => 'Token is succesvol ingetrokken.',
            'generate-only-draft' => 'Alleen conceptintegraties kunnen hun token laten genereren.',
            'regenerate-only-active' => 'Alleen actieve tokens kunnen opnieuw worden gegenereerd.',
            'cannot-edit-historic' => 'Ingetrokken of opnieuw gegenereerde tokens kunnen niet worden bewerkt.',
            'already-inactive' => 'Dit token is al inactief.',
        ],

        'errors' => [
            'admin-has-token' => 'De geselecteerde beheerder heeft al een actief integratietoken.',
        ],

        'validation' => [
            'ip-invalid' => 'Elk toegestaan ​​IP-adres moet een geldig IPv4- of IPv6-adres zijn (CIDR-notatie ondersteund).',
            'cidr-prefix-invalid' => 'Het CIDR-voorvoegsel is ongeldig voor de opgegeven IP-versie.',
        ],

        'configuration' => [
            'api' => [
                'title' => 'API',
                'info' => 'Instellingen voor de Bagisto API en zijn beheerdersmodules.',
            ],
            'integration' => [
                'title' => 'Integratie',
                'info' => 'Beheer de API-integratieplug-in die wordt gebruikt om beheerders-API-tokens uit te geven.',
            ],
            'settings' => [
                'title' => 'Module-instellingen',
                'info' => 'Schakel de API-integratieplug-in in of uit. Indien uitgeschakeld, is het zijbalkmenu verborgen en retourneren de pagina\'s 404.',
                'enable' => 'Schakel API-integratiemodule in',
            ],
        ],

        'emails' => [
            'generated' => [
                'subject' => 'Er is een nieuw API-token gegenereerd: :name',
                'greeting' => 'Er is zojuist een API-integratietoken met de naam \':name\' gegenereerd voor uw account.',
            ],
            'regenerated' => [
                'subject' => 'Uw API-token is opnieuw gegenereerd: :name',
                'greeting' => 'Het API-integratietoken met de naam \':name\' is zojuist opnieuw gegenereerd. Het vorige token werkt niet meer; alleen het nieuwe is geldig.',
            ],
            'revoked' => [
                'subject' => 'Uw API-token is ingetrokken: :name',
                'greeting' => 'Het API-integratietoken met de naam \':name\' is ingetrokken. Elke client die er gebruik van maakt, heeft geen toegang meer.',
            ],

            'details' => [
                'name' => 'Tokennaam',
                'date' => 'Datum',
                'ip' => 'Vanaf IP',
            ],

            'revoke-hint' => 'Als u dit niet had verwacht, trekt u het token onmiddellijk in via onderstaande knop.',
            'revoke-btn' => 'Trek dit token in',
            'revoke-expiry' => 'Deze intrekkingslink is 7 dagen geldig. Meld u daarna aan bij het beheerdersdashboard om het token te beheren.',
            'no-action' => 'Er is geen actie nodig; deze e-mail is slechts een bevestiging.',
        ],

        'revoke-confirmation' => [
            'title' => 'API-token intrekken',
            'success-title' => 'Token ingetrokken',
            'success-message' => 'Het token ":name" is ingetrokken. Elke client die er gebruik van maakt, heeft onmiddellijk geen toegang meer.',
            'already-inactive-title' => 'Token is al inactief',
            'already-inactive-message' => 'Het token \':name\' is al ingetrokken of opnieuw gegenereerd. Er is geen verdere actie nodig.',
        ],

        'confirm' => [
            'generate' => [
                'title' => 'Token genereren',
                'message' => 'Nu het token genereren? De leesbare tekst wordt slechts één keer weergegeven: kopieer deze voordat u de pagina verlaat.',
            ],
            'regenerate' => [
                'title' => 'Token opnieuw genereren',
                'message' => 'Het token opnieuw genereren? Het oude token werkt onmiddellijk niet meer en de nieuwe leesbare tekst wordt slechts één keer weergegeven.',
            ],
            'revoke' => [
                'title' => 'Token intrekken',
                'message' => 'Dit token intrekken? Elke client die hiervan gebruik maakt, verliest onmiddellijk de toegang. Deze actie kan niet ongedaan worden gemaakt.',
            ],
        ],
    ],
];
