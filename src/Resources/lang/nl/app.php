<?php

return [

    'graphql' => [
        'headers' => [
            'unknown-channel' => 'Het kanaal ":code" in de X-Channel-header bestaat niet.',
            'storefront-key-required' => 'X-STOREFRONT-KEY-header is vereist',
            'storefront-key-invalid' => 'Ongeldige storefront-sleutel',
            'storefront-key-rate-limited' => 'Limiet voor aantal verzoeken overschreden. Probeer het over :seconds seconden opnieuw',
        ],

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

            'file-token-invalid' => 'De verwijzing naar het geüploade bestand is ongeldig of verlopen. Upload het bestand opnieuw.',
            'file-token-forbidden' => 'Dit geüploade bestand hoort niet bij uw winkelwagen.',
            'customizable-option-required' => 'De aanpasbare optie ":label" is vereist.',
            'customizable-file-option-invalid' => 'De geselecteerde optie is geen bestandsoptie voor dit product.',
            'customizable-file-invalid' => 'Het bestand ontbreekt, heeft een niet-ondersteunde extensie of is te groot.',
            'cart-item-id-required' => 'Winkelwagenitem-ID is vereist',
            'item-ids-required' => 'Array met item-ID\'s is vereist',

            'event-booking-quantity-not-changeable' => 'Het aantal van een evenementboekingsproduct kan niet worden gewijzigd. Het aantal wordt bepaald door de ticketselectie.',
            'appointment-booking-quantity-not-changeable' => 'Het aantal van een afspraakboekingsproduct kan niet worden gewijzigd.',
            'coupon-code-required' => 'Couponcode is vereist',
            'address-data-required' => 'Land, provincie en postcode zijn vereist',
            'grouped-qty-required' => 'Een gegroepeerd product vereist geselecteerde aantallen. Geef groupedQty door als JSON-string, bijv. {"101":2,"102":1}.',
            'grouped-qty-must-include-all' => 'Een gegroepeerd product vereist aantallen voor alle gekoppelde producten. Ontbrekende ID\'s: :ids.',
            'grouped-qty-invalid-associated' => 'De aantallen van het gegroepeerde product bevatten ongeldige ID\'s van gekoppelde producten: :ids.',
            'grouped-qty-invalid-quantity' => 'Ongeldig aantal opgegeven voor gekoppeld product-ID :id. Het aantal moet een niet-negatief geheel getal zijn.',
            'bundle-qty-not-changeable' => 'Het aantal voor bundeloptie ":option" kan niet worden gewijzigd. Het toegestane aantal is :qty.',

            'add-product-failed' => 'Kan product niet aan winkelwagen toevoegen',
            'update-item-failed' => 'Kan winkelwagenitem niet bijwerken',
            'remove-item-failed' => 'Kan winkelwagenitem niet verwijderen',
            'apply-coupon-failed' => 'Kan coupon niet toepassen',
            'remove-coupon-failed' => 'Kan coupon niet verwijderen',
            'coupon-applied' => 'Coupon succesvol toegepast',
            'coupon-removed' => 'Coupon succesvol verwijderd',
            'move-to-wishlist-failed' => 'Kan item niet naar verlanglijst verplaatsen',
            'estimate-shipping-failed' => 'Kan verzendkosten niet schatten',

            'product-added-successfully' => 'Product succesvol aan winkelwagen toegevoegd',
            'cart-item-updated-successfully' => 'Winkelwagenitem succesvol bijgewerkt',
            'guest-cart-merged' => 'Gastwinkelwagen succesvol samengevoegd',
            'using-authenticated-cart' => 'Geverifieerde klantwinkelwagen wordt gebruikt',
            'cart-item-not-found' => 'Winkelwagenitem niet gevonden',
            'new-guest-cart-created' => 'Nieuwe gastwinkelwagen aangemaakt met uniek sessietoken',
            'select-items-to-remove' => 'Selecteer items om te verwijderen',
            'select-items-to-move-wishlist' => 'Selecteer items om naar de verlanglijst te verplaatsen',
            'invalid-or-expired-token' => 'Winkelwagentoken is ongeldig of verlopen. Maak een nieuwe winkelwagen aan.',
            'invalid-token-of-login-user' => 'Token van ingelogde gebruiker is ongeldig.',
            'booking-note-required' => 'booking.note is vereist voor een tafelreservering.',
            'booking-date-required' => 'booking.date is vereist wanneer booking.slot een opgemaakt tijdsbereik is.',
            'booking-qty-required' => 'booking.qty is vereist voor een evenementboeking.',
            'booking-ticket-qty-invalid' => 'Het aantal evenementtickets moet een niet-negatief getal zijn.',
            'booking-ticket-required' => 'Selecteer voor een evenementboeking een aantal voor ten minste één ticket.',
            'booking-slot-unavailable' => 'Het geselecteerde tijdslot is niet beschikbaar voor de opgegeven datum.',
            'invalid-quantity' => 'De hoeveelheid moet minimaal 1 zijn',
            'product-id-required' => 'Product-ID is vereist',
            'shipping-address-required' => 'Land en postcode zijn vereist om de verzending te schatten',
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
            'unauthenticated' => 'Niet geverifieerd. Log in om deze actie uit te voeren',
        ],

        'forgot-password' => [
            'invalid-operation' => 'Ongeldige bewerking',
            'invalid-input-data' => 'Ongeldige invoergegevens',
            'email-required' => 'E-mailadres is vereist',
            'reset-link-sent' => 'Resetlink succesvol naar uw e-mailadres verzonden',
            'email-not-found' => 'E-mailadres niet gevonden',
            'throttled' => 'Te veel resetpogingen. Wacht even voordat u het opnieuw probeert.',
            'error-sending-reset-link' => 'Er is een fout opgetreden bij het verzenden van de resetlink',
        ],

        'reset-password' => [
            'failed' => 'Het wachtwoord kon niet worden gereset. Probeer het opnieuw.',
        ],

        'rate-limit' => [
            'too-many-attempts' => 'Te veel pogingen. Probeer het over :seconds seconden opnieuw.',
        ],

        'logout' => [
            'invalid-operation' => 'Ongeldige bewerking',
            'invalid-input-data' => 'Ongeldige invoergegevens',
            'token-required' => 'Token is vereist',
            'invalid-token-format' => 'Ongeldig tokenformaat',
            'logged-out-successfully' => 'Succesvol uitgelogd',
            'token-not-found-or-expired' => 'Token niet gevonden of al verlopen',
            'error-during-logout' => 'Fout tijdens het uitloggen',
            'unauthenticated' => 'Niet geverifieerd. Log in om deze actie uit te voeren',
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
            'field-required' => 'Het veld :field is vereist',
        ],

        'customer-profile' => [
            'authentication-required' => 'Authenticatietoken is vereist. Geef het token op in de query-invoer',
            'invalid-token' => 'Ongeldig of verlopen token',
            'profile-updated' => 'Klantprofiel succesvol bijgewerkt',
        ],

        'customer' => [
            'password-mismatch' => 'Wachtwoord en bevestigingswachtwoord komen niet overeen',
            'confirm-password-required' => 'Bevestigingswachtwoord is vereist bij het wijzigen van het wachtwoord',
            'current-password-required' => 'Het huidige wachtwoord is vereist om uw wachtwoord te wijzigen',
            'current-password-incorrect' => 'Het huidige wachtwoord dat u hebt ingevoerd is onjuist',
            'verify-email-to-login' => 'Uw account is aangemaakt. Verifieer uw e-mailadres om in te loggen.',
            'delete-password-required' => 'Uw wachtwoord is vereist om uw account te verwijderen',
            'delete-password-incorrect' => 'Het wachtwoord dat u hebt ingevoerd is onjuist',
            'delete-has-active-orders' => 'Uw account kan niet worden verwijderd zolang u openstaande of in behandeling zijnde bestellingen hebt',
            'unauthenticated' => 'Niet geverifieerd. Log in om deze actie uit te voeren',
            'id-required' => 'Klant-ID is vereist',
            'invalid-id-format' => 'Ongeldig ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/admin/customers/1" of een numerieke ID',
            'not-found' => 'Klant niet gevonden',
            'phone-special-chars-not-allowed' => 'Het mobiele nummer mag alleen cijfers bevatten. Speciale tekens zijn niet toegestaan',
            'email-already-taken' => 'Dit e-mailadres is al geregistreerd bij een ander account',
            'invalid-gender' => 'Ongeldige waarde voor geslacht ":gender". Toegestane waarden zijn: :valid',
        ],

        'product-review' => [
            'id-required' => 'Productbeoordeling-ID is vereist',
            'invalid-id-format' => 'Ongeldig ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/shop/reviews/1" of een numerieke ID',
            'not-found' => 'Productbeoordeling met ID ":id" niet gevonden',
            'item-not-found' => 'Item ":id" niet gevonden.',
            'product-id-required' => 'Product-ID is vereist',
            'product-not-found' => 'Product niet gevonden',
            'rating-invalid' => 'Beoordeling moet tussen 1 en 5 liggen',
            'title-required' => 'Titel van beoordeling is vereist',
            'comment-required' => 'Opmerking bij beoordeling is vereist',
            'review-disabled' => 'Productbeoordelingen zijn momenteel uitgeschakeld',
            'guest-review-disabled' => 'Beoordelingen door gasten zijn niet toegestaan. Log in om een beoordeling in te dienen',
            'owner-required' => 'Log in om uw beoordeling te beheren',
            'not-owned' => 'Deze beoordeling is niet door u geschreven en kan niet worden gewijzigd of verwijderd',
            'invalid-attachments' => 'Ongeldig formaat van bijlagen.',
            'invalid-product-id-format' => 'Ongeldig product-ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/shop/products/1" of een numerieke ID',
        ],

        'customer-review' => [
            'id-required' => 'Klantbeoordeling-ID is vereist',
            'invalid-id-format' => 'Ongeldig ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/shop/customer-reviews/1" of een numerieke ID',
            'not-found' => 'Klantbeoordeling met ID ":id" niet gevonden',
        ],

        'newsletter' => [
            'error-during-subscription' => 'De inschrijving kan op dit moment niet worden voltooid. Probeer het later opnieuw',
        ],

        'contact-us' => [
            'success' => 'Uw vraag is succesvol verzonden. We nemen binnenkort contact met u op',
            'failed' => 'Uw vraag kan op dit moment niet worden verzonden. Probeer het later opnieuw',
            'invalid-input' => 'Ongeldige gegevens in het contactformulier',
        ],

        'customer-order' => [
            'id-required' => 'Klantbestelling-ID is vereist',
            'invalid-id-format' => 'Ongeldig ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/shop/customer-orders/1" of een numerieke ID',
            'not-found' => 'Klantbestelling met ID ":id" niet gevonden',
        ],

        'return' => [
            'not-found' => 'Retourverzoek niet gevonden.',
            'disabled' => 'Retourneren is niet ingeschakeld voor deze winkel.',
            'invalid-input' => 'Ontbrekende of ongeldige retourgegevens.',
            'invalid-resolution-type' => 'Het afhandelingstype moet "return" of "cancel_items" zijn.',
            'agreement-required' => 'U moet het retourbeleid accepteren.',
            'invalid-order' => 'De geselecteerde bestelling is niet geldig.',
            'invalid-item' => 'Het geselecteerde item komt niet in aanmerking voor retour.',
            'qty-exceeds' => 'Het gevraagde aantal overschrijdt het aantal dat kan worden geretourneerd.',
            'invalid-package-condition' => 'De staat van het pakket moet "open" of "packed" zijn.',
            'invalid-image' => 'Een of meer van de geüploade bestanden hebben een niet-ondersteund bestandstype.',
            'invalid-custom-field' => 'Onbekend aangepast veld ":id".',
            'custom-field-required' => 'Het veld ":field" is vereist.',
            'invalid-custom-field-value' => 'De waarde voor ":field" is geen van de toegestane opties.',
            'created' => 'Retourverzoek succesvol aangemaakt.',
            'canceled' => 'Retourverzoek succesvol geannuleerd.',
            'already-canceled' => 'Het retourverzoek is al geannuleerd.',
            'cancel-not-allowed' => 'Dit retourverzoek kan niet worden geannuleerd.',
            'close-not-allowed' => 'Dit retourverzoek kan niet worden gesloten.',
            'reopened' => 'Retourverzoek succesvol heropend.',
            'reopen-not-allowed' => 'Dit retourverzoek kan niet worden heropend.',
            'closed' => 'Retourverzoek succesvol gesloten.',
            'message-sent' => 'Bericht succesvol verzonden.',
            'attachment-not-found' => 'Bijlage van bericht niet gevonden.',
        ],

        'eu-withdrawal' => [
            'not-found' => 'Herroepingsverklaring niet gevonden.',
            'order-not-found' => 'De bestelling is niet gevonden of komt niet in aanmerking voor herroeping.',
            'too-many-attempts' => 'Te veel pogingen. Probeer het over :seconds seconden opnieuw.',
            'created' => 'Herroepingsverklaring succesvol ingediend.',
        ],

        'customer-order-shipment' => [
            'id-required' => 'Verzending-ID is vereist',
            'not-found' => 'Verzending met ID ":id" niet gevonden of behoort niet toe aan deze klant',
            'item-not-found' => 'Verzenditem met ID ":id" niet gevonden of behoort niet toe aan deze klant',
            'invalid-id-format' => 'Ongeldig ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/shop/customer-order-shipments/1" of een numerieke ID',
        ],

        'cancel-order' => [
            'order-id-required' => 'Bestellings-ID is vereist om een bestelling te annuleren',
            'not-found' => 'Bestelling met ID ":id" niet gevonden of behoort niet toe aan deze klant',
            'cancel-success' => 'De bestelling is succesvol geannuleerd',
            'cancel-failed' => 'De bestelling kan niet worden geannuleerd. Deze is mogelijk al verwerkt, verzonden of geannuleerd',
        ],

        'reorder' => [
            'order-id-required' => 'Bestellings-ID is vereist om opnieuw te bestellen',
            'not-found' => 'Bestelling met ID ":id" niet gevonden of behoort niet toe aan deze klant',
            'reorder-success' => ':count item(s) uit uw vorige bestelling zijn aan uw winkelwagen toegevoegd',
            'no-items-added' => 'Er konden geen items aan de winkelwagen worden toegevoegd. Producten zijn mogelijk niet op voorraad of niet beschikbaar',
        ],

        'customer-downloadable-product' => [
            'id-required' => 'ID van downloadbaar klantproduct is vereist',
            'invalid-id-format' => 'Ongeldig ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/shop/customer-downloadable-products/1" of een numerieke ID',
            'not-found' => 'Downloadbaar klantproduct met ID ":id" niet gevonden',

            'download' => [
                'unauthenticated' => 'Niet geautoriseerd: klantauthenticatie vereist.',
                'not-found' => 'Downloadbaar product niet gevonden.',
                'pending' => 'De download is in behandeling. Wacht tot de bestelling is gefactureerd.',
                'limit-exceeded' => 'Downloadlimiet overschreden.',
                'no-downloads-remaining' => 'Geen downloads meer beschikbaar.',
                'file-not-found' => 'Bestand niet gevonden.',
                'link-not-found' => 'Downloadlink niet gevonden.',
            ],
        ],

        'customer-invoice' => [
            'id-required' => 'Klantfactuur-ID is vereist',
            'invalid-id-format' => 'Ongeldig ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/shop/customer-invoices/1" of een numerieke ID',
            'not-found' => 'Klantfactuur met ID ":id" niet gevonden',
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
            'option-id-required' => 'De parameter "attributeId" is vereist bij het rechtstreeks opvragen van attribuutopties',
            'id-required' => 'Attribuut-ID is vereist',
            'invalid-id-format' => 'Ongeldig ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/shop/attributes/1" of een numerieke ID',
            'not-found' => 'Attribuut niet gevonden',
        ],

        'attribute-option' => [
            'id-required' => 'Attribuutoptie-ID is vereist',
            'invalid-id-format' => 'Ongeldig ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/shop/attribute-options/1" of een numerieke ID',
            'not-found' => 'Attribuutoptie niet gevonden',
        ],

        'channel' => [
            'id-required' => 'Kanaal-ID is vereist',
            'invalid-id-format' => 'Ongeldig ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/shop/channels/1" of een numerieke ID',
            'not-found' => 'Kanaal niet gevonden',
        ],

        'country-state' => [
            'id-required' => 'Provincie-ID is vereist',
            'invalid-id-format' => 'Ongeldig ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/shop/country-states/1" of een numerieke ID',
            'not-found' => 'Provincie niet gevonden',
            'country-id-required' => 'Land-ID is vereist',
        ],

        'country' => [
            'id-required' => 'Land-ID is vereist',
            'invalid-id-format' => 'Ongeldig ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/shop/countries/1" of een numerieke ID',
            'not-found' => 'Land niet gevonden',
        ],

        'login' => [
            'invalid-credentials' => 'Ongeldig e-mailadres of wachtwoord',
            'account-suspended' => 'Uw account is opgeschort',
            'account-inactive' => 'Uw activering wacht op goedkeuring van de beheerder',
            'email-not-verified' => 'Verifieer eerst uw e-mailaccount.',
            'successful' => 'U bent succesvol ingelogd',
            'invalid-request' => 'Ongeldig inlogverzoek',
        ],

        'social-login' => [
            'signed-in' => 'Succesvol aangemeld.',
            'token-required' => 'Een social login-token is vereist.',
            'invalid-token' => 'Het social login-token is ongeldig of verlopen. Probeer het opnieuw.',
            'google-not-configured' => 'Inloggen met Google is niet geconfigureerd voor deze winkel.',
            'wrong-audience' => 'Dit token is uitgegeven voor een andere app.',
            'email-required' => 'De provider heeft geen e-mailadres gedeeld. Registreer u met een e-mailadres.',
            'account-inactive' => 'Uw activering wacht op goedkeuring van de beheerder',
            'provider-not-supported' => 'Deze social login-provider wordt niet ondersteund.',
            'provider-disabled' => 'Deze social login-provider is niet ingeschakeld.',
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
            'order-placed' => 'Bestelling succesvol geplaatst',
            'order-redirect-required' => 'Bij deze betaalmethode moet de klant de betaling via de gateway voltooien. Stuur de klant naar redirectUrl; de bestelling wordt aangemaakt zodra de gateway de betaling bevestigt.',
            'order-creation-failed' => 'Aanmaken van bestelling mislukt: bestellings-ID is null of bestelling is niet opgeslagen',
            'order-retrieval-failed' => 'Kan aangemaakte bestelling niet ophalen',
            'order-creation-error' => 'Kan bestelling niet aanmaken',
            'cart-empty' => 'Winkelwagen is leeg',
            'account-suspended' => 'Uw account is opgeschort. Neem contact op met de klantenservice.',
            'account-inactive' => 'Uw account is inactief. Neem contact op met de klantenservice.',
            'minimum-order-not-met' => 'Minimaal bestelbedrag is :amount',
            'guest-checkout-not-allowed' => 'Log in om af te rekenen. Afrekenen als gast is niet beschikbaar voor deze winkelwagen.',
            'email-required' => 'E-mailadres is vereist voor het aanmaken van een bestelling',
            'unknown-operation' => 'Onbekende afrekenbewerking',
        ],

        'customer-addresses' => [
            'token-required' => 'Token is vereist om klantadressen op te halen',
            'invalid-or-expired-token' => 'Ongeldig of verlopen token',
            'token-validation-failed' => 'Tokenvalidatie mislukt',
        ],

        'product' => [
            'not-found' => 'Product niet gevonden',
            'not-found-with-sku' => 'Geen product gevonden met SKU',
            'not-found-with-url-key' => 'Geen product gevonden met URL-sleutel',
            'parameters-required' => 'Ten minste een van de volgende parameters moet worden opgegeven: "sku", "id", "urlKey"',
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
            'id-required' => 'Product-ID is vereist',
            'invalid-id-format' => 'Ongeldig ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/shop/products/1" of een numerieke ID',
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
            'unauthenticated' => 'Niet geautoriseerd: klantauthenticatie vereist',
            'link-id-required' => 'downloadableLinkPurchasedId is vereist',
            'link-not-found' => 'Downloadlink niet gevonden',
            'not-owner' => 'Niet geautoriseerd: deze downloadlink behoort niet aan u toe',
            'pending' => 'De download is in behandeling. Wacht tot de bestelling is gefactureerd.',
            'limit-exceeded' => 'Downloadlimiet overschreden',
            'no-downloads-left' => 'Geen downloads meer beschikbaar',
        ],

        'booking-slot' => [
            'id-required' => 'De ID van het boekingsproduct is vereist.',
            'date-required' => 'De datum is vereist.',
            'product-not-found' => 'Boekingsproduct niet gevonden.',
            'invalid-type' => 'Dit boekingstype wordt niet ondersteund.',
            'invalid-time-range' => 'Ongeldig tijdsbereik.',
            'invalid-time-format' => 'Ongeldig tijdformaat.',
        ],

        'resolver' => [
            'no-resolve-info' => 'GraphQL ResolveInfo is niet beschikbaar in de context. Kan de modelklasse niet bepalen.',
            'unknown-resource-class' => 'Kan de resourceklasse niet bepalen op basis van het GraphQL-retourtype',
        ],

        'docs' => [
            'openapi-unavailable' => 'OpenAPI Factory kon niet worden geïnstantieerd',
        ],

        'locale' => [
            'id-required' => 'Taal-ID is vereist',
            'invalid-id-format' => 'Ongeldig ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/shop/locales/1" of een numerieke ID',
            'not-found' => 'Taal niet gevonden',
        ],

        'category' => [
            'id-required' => 'Categorie-ID is vereist',
            'invalid-id-format' => 'Ongeldig ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/shop/categories/1" of een numerieke ID',
            'not-found' => 'Categorie niet gevonden',
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
            'delete-all-success' => 'Alle vergelijkingsitems zijn succesvol verwijderd',
        ],

        'wishlist' => [
            'id-required' => 'Verlanglijstitem-ID is vereist',
            'invalid-id-format' => 'Ongeldig ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/shop/wishlists/1" of een numerieke ID',
            'not-found' => 'Verlanglijstitem niet gevonden',
            'product-id-required' => 'Product-ID is vereist',
            'customer-id-required' => 'Klant-ID is vereist',
            'product-not-found' => 'Product niet gevonden',
            'product-disabled' => 'Dit product is momenteel uitgeschakeld',
            'customer-not-found' => 'Klant niet gevonden',
            'already-exists' => 'Dit product staat al op uw verlanglijst',
            'added' => 'Item succesvol aan verlanglijst toegevoegd',
            'removed' => 'Item succesvol van verlanglijst verwijderd',
            'product-removed' => 'Product is verwijderd',
            'wishlist-item-id-required' => 'Verlanglijstitem-ID is vereist',
            'invalid-quantity' => 'Het aantal moet groter zijn dan 0',
            'move-to-cart-missing-options' => 'Bij het product ontbreken vereiste opties. Configureer het handmatig',
            'moved-to-cart-success' => 'Item succesvol naar winkelwagen verplaatst',
            'delete-all-success' => 'Alle verlanglijstitems zijn succesvol verwijderd',
        ],

        'gdpr' => [
            'disabled' => 'AVG-gegevensverzoeken zijn uitgeschakeld. Schakel de AVG in via de beheerdersconfiguratie.',
            'unauthenticated' => 'U moet ingelogd zijn om AVG-gegevensverzoeken te beheren.',
            'not-found' => 'AVG-gegevensverzoek niet gevonden',
            'type-required' => 'Het verzoektype is vereist',
            'type-invalid' => 'Het verzoektype moet "delete" of "update" zijn',
            'message-required' => 'Het bericht is vereist',
            'raised' => 'Uw AVG-gegevensverzoek is succesvol ingediend.',
            'revoked' => 'Uw AVG-gegevensverzoek is succesvol ingetrokken.',
            'revoke-not-allowed' => 'Dit AVG-gegevensverzoek kan niet langer worden ingetrokken.',
            'deleted' => 'Uw AVG-gegevensverzoek is succesvol verwijderd.',
        ],

        'section' => [
            'id-required' => 'Sectie-ID is vereist',
            'invalid-id-format' => 'Ongeldig ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/shop/sections/1" of een numerieke ID',
            'not-found' => 'Sectie niet gevonden',
        ],

        'install' => [
            'starting' => 'Installatie van API Platform wordt gestart...',
            'completed-success' => '✓ Installatie van API Platform succesvol voltooid!',
            'failed' => '✗ Installatie mislukt: ',

            'provider-already-registered' => 'ℹ Service Provider is al geregistreerd',
            'provider-not-registered' => '⚠ De Service Provider kon niet automatisch worden geregistreerd. Voeg :provider handmatig toe aan bootstrap/providers.php.',
            'provider-registered' => '✓ Service Provider geregistreerd in bootstrap/providers.php',
            'provider-file-not-found' => 'Bestand niet gevonden: :file',
            'provider-permission-denied' => 'Toegang geweigerd: kan niet schrijven naar :file. Controleer de bestandsrechten.',

            'config-already-exists' => 'ℹ Configuratiebestand bestaat al',
            'config-published' => '✓ API Platform-configuratie gepubliceerd naar config/',
            'config-source-not-found' => 'Bronconfiguratiebestand niet gevonden: :file',
            'config-permission-denied' => 'Toegang geweigerd: kan niet schrijven naar :directory. Controleer de maprechten.',

            'translatable-not-found' => 'ℹ TranslatableModel niet gevonden - wordt overgeslagen',
            'translatable-already-abstract' => 'ℹ TranslatableModel is al abstract',
            'translatable-made-abstract' => '✓ TranslatableModel abstract gemaakt',
            'translatable-permission-denied' => 'Toegang geweigerd: kan niet schrijven naar :file. Controleer de bestandsrechten.',

            'providers-already-registered' => 'ℹ API Platform-providers zijn al geregistreerd',
            'providers-registered' => '✓ API Platform-providers geregistreerd in bootstrap/app.php',
            'providers-file-not-found' => 'Bestand niet gevonden: :file',
            'providers-permission-denied' => 'Toegang geweigerd: kan niet schrijven naar :file. Controleer de bestandsrechten.',
            'providers-not-found' => 'Kan het aanmaken van de Application niet vinden in bootstrap/app.php',

            'assets-published' => '✓ Pakketassets gepubliceerd',
            'running-migrations' => 'Databasemigraties worden uitgevoerd...',
            'migrations-completed' => '✓ Databasemigraties voltooid',
            'clearing-caches' => 'Caches worden gewist en geoptimaliseerd...',
            'caches-optimized' => '✓ Caches gewist en geoptimaliseerd',
            'generating-api-key' => 'API-sleutel wordt gegenereerd...',
            'api-key-generated' => '✓ API-sleutel succesvol gegenereerd',
            'api-key-already-exists' => 'ℹ API-sleutel voor de standaardwinkel bestaat al - genereren wordt overgeslagen',

            'migrations-error' => 'Databasemigraties mislukt.',
            'migrations-error-running' => 'Fout bij het uitvoeren van databasemigraties: :error',
            'cache-clear-error' => 'Wissen van de cache mislukt.',
            'cache-optimize-error' => 'Optimalisatie mislukt.',
            'cache-error' => 'Fout bij het wissen en optimaliseren van de caches: :error',
            'api-key-generation-error' => 'Genereren van API-sleutel mislukt.',
            'api-key-error' => 'Fout bij het genereren van de API-sleutel: :error',

            'key-management-required' => '--key is vereist voor de actie :action',
            'key-rotation-error' => 'Fout bij het roteren van de sleutel: :error',
            'key-deactivation-error' => 'Fout bij het deactiveren van de sleutel: :error',
            'key-not-found' => 'API-sleutel niet gevonden: :identifier',

            'maintenance-starting' => '🔄 Onderhoud van API-sleutels wordt gestart...',
            'maintenance-complete' => '✅ Onderhoud van API-sleutels voltooid',
            'cleanup-expired-keys' => '🧹 Verlopen sleutels worden opgeruimd...',
            'cleanup-expired-none' => '   ℹ️ Geen verlopen sleutels om op te ruimen',
            'invalidate-deprecated' => '⚠️ Verouderde sleutels worden ongeldig gemaakt...',
            'invalidate-deprecated-none' => '   ℹ️ Geen verouderde sleutels om ongeldig te maken',
            'notify-expiring' => '📧 Meldingen over verloop worden verzonden...',
            'notify-expiring-none' => '   ℹ️ Geen sleutels waarvoor meldingen nodig zijn',

            'key-rotated-success' => '✅ Sleutel succesvol geroteerd!',
            'key-deactivated-success' => '✅ Sleutel succesvol gedeactiveerd!',
            'deactivation-cancelled' => 'Deactivering geannuleerd.',
            'cleanup-cancelled' => 'Opruimen geannuleerd.',

            'key-status-title' => 'Sleutelstatus: :name',
            'key-status-active' => 'Actief: :status',
            'key-status-usable' => 'Bruikbaar: :status',
            'key-status-expired' => 'Verlopen: :status',
            'key-status-deprecated' => 'Verouderd: :status',
            'key-status-expires-at' => 'Verloopt op: :expires',
            'key-status-days-until-expiry' => 'Dagen tot verloop: :days',
            'key-status-last-used' => 'Laatst gebruikt: :date',
            'key-status-rotated-from' => 'Geroteerd van: :key',
            'key-status-rotated-keys' => 'Sleutels hiervan geroteerd: :count',

            'policy-compliance-summary' => 'Overzicht naleving rotatiebeleid voor API-sleutels',
            'available-actions' => 'Beschikbare acties: rotate, deactivate, cleanup, status, expiring, unused, summary',

            'asset-linked-success' => '✓ API Platform-assets succesvol gekoppeld',
            'symlink-create-failed' => 'Kan symlink niet aanmaken, assets worden in plaats daarvan gekopieerd...',
            'asset-copy-warning' => 'Waarschuwing: kan API Platform-assets niet koppelen of kopiëren. Handmatige installatie kan nodig zijn.',
            'asset-copied-success' => '✓ API Platform-assets succesvol gekopieerd',

            'key-name-required' => 'De naam van de storefront-sleutel mag niet leeg zijn.',
            'key-generated-success' => 'Storefront-sleutel succesvol gegenereerd!',
            'key-details' => 'Sleutelgegevens:',
            'key-secure-warning' => 'Bewaar deze sleutel veilig! Deze wordt gebruikt in de X-STOREFRONT-KEY-header.',
            'key-share-warning' => 'Deel deze sleutel niet openbaar en commit hem niet naar versiebeheer.',

            'key-already-exists' => 'Er bestaat al een storefront-sleutel met de naam \':name\'.',
            'cleanup-success' => '✅ :count verlopen sleutels opgeruimd',
            'confirm-cleanup' => 'Hiermee worden alle verlopen sleutels soft-deleted. Doorgaan?',
            'confirm-deactivate' => 'Weet u zeker dat u de sleutel :name wilt deactiveren?',

            'old-key' => 'Oude sleutel: :name',
            'old-key-id' => 'Oude sleutel-ID: :id',
            'deprecation-date' => 'Datum van veroudering: :date',
            'new-key' => 'Nieuwe sleutel: :name',
            'new-key-id' => 'Nieuwe sleutel-ID: :id',
            'new-key-value' => 'Nieuwe sleutelwaarde: :key',
            'expires-at' => 'Verloopt op: :date',
            'invalid-action' => 'Ongeldige actie: :action',
            'key-active' => 'Actief: :status',
            'key-usable' => 'Bruikbaar: :status',
            'key-expired' => 'Verlopen: :status',
            'key-deprecated' => 'Verouderd: :status',
            'key-field-id' => 'ID',
            'key-field-name' => 'Naam',
            'key-field-key' => 'Sleutel',
            'key-field-rate-limit' => 'Limiet voor verzoeken',
            'key-field-status' => 'Status',
            'key-days' => ' dagen',
            'key-never' => 'Nooit',
            'key-requests-minute' => ' verzoeken/minuut',
            'key-unlimited' => 'Onbeperkt',
            'rate-limit-exceeded' => 'De gevraagde limiet overschrijdt het toegestane maximum van :max. Wordt ingesteld op :max verzoeken/minuut.',

            'vendor-path-not-found' => 'Vendor-pad van API Platform niet gevonden op: :path',
            'assets-already-linked' => 'API Platform-assets zijn al gekoppeld op: :path',
            'no-keys-expiring' => 'Geen sleutels die de komende :days dagen verlopen',
            'keys-expiring-title' => 'Sleutels die de komende :days dagen verlopen:',
            'key-display-format' => '• :name (ID: :id)',
            'key-expires-display' => '  Verloopt: :date (nog :days dagen)',
            'no-unused-keys' => 'Geen ongebruikte sleutels gevonden (> :days dagen)',
            'unused-keys-title' => 'Ongebruikte sleutels (> :days dagen):',
            'key-last-used-display' => '  Laatst gebruikt: :date',
            'total-keys' => 'Totaal aantal sleutels: :count',
            'valid-keys' => 'Geldige sleutels: :count',
            'expired-keys' => 'Verlopen sleutels: :count',
            'deprecated-keys' => 'Verouderde sleutels: :count',
            'keys-expiring-soon' => 'Binnenkort verlopende sleutels (7 dagen): :count',
            'unused-keys-summary' => 'Ongebruikte sleutels (90 dagen): :count',
            'recently-rotated' => 'Recent geroteerd (30 dagen): :count',
            'cleanup-success-message' => '   ✅ :count verlopen sleutels opgeruimd',
            'invalidate-success-message' => '   ✅ :count verouderde sleutels ongeldig gemaakt',
            'notify-success-message' => '   ✅ :count meldingen over verloop verzonden',
            'notify-failed-message' => '   ⚠️ Melding over :key mislukt: :error',
            'publish-assets-warning' => 'Waarschuwing: kan pakketassets niet publiceren. :error',
            'env-config-saved' => '✓ Omgevingsconfiguratie van API Platform succesvol opgeslagen',
            'api-endpoints' => 'API-endpoints:',
            'api-documentation' => 'API-documentatie: :url',
            'api-landing-page' => 'API-landingspagina: :url',
            'graphql-playground' => 'GraphQL Playground: :url',
            'rest-api-storefront' => 'REST API Storefront Swagger: :url',
            'rest-api-admin' => 'REST API Admin Swagger: :url',
            'key-rotation-invalid-key' => 'Een ongeldige of verlopen sleutel kan niet worden geroteerd',
            'env-file-not-found' => 'Het .env-bestand is niet gevonden',
            'env-permission-denied' => 'Toegang geweigerd: kan niet naar het .env-bestand schrijven. Controleer de bestandsrechten.',
        ],

        'attribute-translation' => [
            'id-required' => 'Attribuutvertaling-ID is vereist',
            'invalid-id-format' => 'Ongeldig ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/shop/attribute-translations/1" of een numerieke ID',
            'not-found' => 'Attribuutvertaling niet gevonden',
        ],

        'currency' => [
            'id-required' => 'Valuta-ID is vereist',
            'invalid-id-format' => 'Ongeldig ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/shop/currencies/1" of een numerieke ID',
            'not-found' => 'Valuta niet gevonden',
        ],

        'customer-address' => [
            'id-required' => 'Klantadres-ID is vereist',
            'invalid-id-format' => 'Ongeldig ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/shop/customer-addresses/1" of een numerieke ID',
        ],

        'customer-order-shipment-item' => [
            'id-required' => 'Zendingsartikel-ID is vereist',
            'invalid-id-format' => 'Ongeldig ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/shop/customer-order-shipment-items/1" of een numerieke ID',
            'not-found' => 'Zendingsartikel niet gevonden',
        ],

        'page' => [
            'id-required' => 'Pagina-ID is vereist',
            'invalid-id-format' => 'Ongeldig ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/shop/pages/1" of een numerieke ID',
            'not-found' => 'Pagina niet gevonden',
        ],

        'page-translation' => [
            'id-required' => 'Paginavertaling-ID is vereist',
            'invalid-id-format' => 'Ongeldig ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/shop/page-translations/1" of een numerieke ID',
            'not-found' => 'Paginavertaling niet gevonden',
        ],

        'product-bundle-option' => [
            'id-required' => 'Bundeloptie-ID is vereist',
            'invalid-id-format' => 'Ongeldig ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/shop/product-bundle-options/1" of een numerieke ID',
            'not-found' => 'Bundeloptie niet gevonden',
        ],

        'product-bundle-option-product' => [
            'id-required' => 'ID van het bundeloptieproduct is vereist',
            'invalid-id-format' => 'Ongeldig ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/shop/product-bundle-option-products/1" of een numerieke ID',
            'not-found' => 'Bundeloptieproduct niet gevonden',
        ],

        'product-downloadable-link' => [
            'id-required' => 'Downloadlink-ID is vereist',
            'invalid-id-format' => 'Ongeldig ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/shop/product-downloadable-links/1" of een numerieke ID',
            'not-found' => 'Downloadlink niet gevonden',
        ],

        'product-downloadable-sample' => [
            'id-required' => 'Downloadvoorbeeld-ID is vereist',
            'invalid-id-format' => 'Ongeldig ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/shop/product-downloadable-samples/1" of een numerieke ID',
            'not-found' => 'Downloadvoorbeeld niet gevonden',
        ],

        'product-grouped-product' => [
            'id-required' => 'ID van het gegroepeerde product is vereist',
            'invalid-id-format' => 'Ongeldig ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/shop/product-grouped-products/1" of een numerieke ID',
            'not-found' => 'Gegroepeerd product niet gevonden',
        ],

        'product-images' => [
            'id-required' => 'Productafbeelding-ID is vereist',
            'invalid-id-format' => 'Ongeldig ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/shop/product-images/1" of een numerieke ID',
            'not-found' => 'Productafbeelding niet gevonden',
        ],

        'product-videos' => [
            'id-required' => 'Productvideo-ID is vereist',
            'invalid-id-format' => 'Ongeldig ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/shop/product-videos/1" of een numerieke ID',
            'not-found' => 'Productvideo niet gevonden',
        ],

        'theme-customization' => [
            'id-required' => 'Thema-aanpassing-ID is vereist',
            'invalid-id-format' => 'Ongeldig ID-formaat. Verwacht wordt een IRI-formaat zoals "/api/shop/theme-customizations/1" of een numerieke ID',
            'not-found' => 'Thema-aanpassing niet gevonden',
        ],

        'unknown-resource' => [
            'id-required' => 'Resource-ID is vereist',
            'not-found' => 'Resource niet gevonden',
        ],
    ],

    'admin' => [
        // login / logout / forgot-password / profile-update keys removed in the
        // 2026-05-27 auth refactor — admin clients now authenticate via
        // pre-issued integration tokens (Bearer header, AdminApiGuard).
        'profile' => [
            'unauthenticated' => 'U bent niet geverifieerd.',
        ],

        'dashboard' => [
            'invalid-type' => 'Onbekend type dashboardstatistiek. Geldige typen: over-all, today, stock-threshold-products, total-sales, top-selling-products, top-customers.',
        ],

        'rma' => [
            'not-found' => 'Retourverzoek niet gevonden.',
            'invalid-input' => 'Ontbrekende of ongeldige retourgegevens.',
            'invalid-order' => 'De geselecteerde bestelling is niet geldig.',
            'invalid-item' => 'Het geselecteerde item komt niet in aanmerking voor retour.',
            'invalid-resolution-type' => 'Het afhandelingstype moet "return" of "cancel_items" zijn.',
            'qty-exceeds' => 'Het gevraagde aantal overschrijdt het aantal dat kan worden geretourneerd.',
            'created' => 'Retourverzoek succesvol aangemaakt.',
            'status-updated' => 'Retourstatus succesvol bijgewerkt.',
            'invalid-status' => 'Ongeldige retourstatus.',
            'reopened' => 'Retourverzoek succesvol heropend.',
            'reopen-not-allowed' => 'Dit retourverzoek kan niet worden heropend.',
            'refund-failed' => 'De terugbetaling voor deze retour kon niet worden aangemaakt.',
            'message-sent' => 'Bericht succesvol verzonden.',
            'no-permission' => 'U hebt geen toestemming om deze actie uit te voeren.',
            'reason-not-found' => 'Retourreden niet gevonden.',
            'reason-deleted' => 'Retourreden succesvol verwijderd.',
            'status-not-found' => 'Retourstatus niet gevonden.',
            'status-deleted' => 'Retourstatus succesvol verwijderd.',
            'status-default-undeletable' => 'Een standaardretourstatus kan niet worden verwijderd.',
            'rule-not-found' => 'Retourregel niet gevonden.',
            'rule-deleted' => 'Retourregel succesvol verwijderd.',
            'custom-field-not-found' => 'Aangepast retourveld niet gevonden.',
            'custom-field-deleted' => 'Aangepast retourveld succesvol verwijderd.',
            'custom-field-options-required' => 'Voor dit veldtype is ten minste één optie vereist.',
            'mass-indices-required' => 'Ten minste één id is vereist.',
            'mass-delete-success' => 'Geselecteerde items verwijderd.',
            'mass-update-success' => 'Geselecteerde items bijgewerkt.',
            'mass-value-invalid' => 'De statuswaarde moet 0 of 1 zijn.',
            'image-not-found' => 'Retourafbeelding niet gevonden.',
            'attachment-not-found' => 'Bijlage van bericht niet gevonden.',
        ],

        'eu-withdrawal' => [
            'not-found' => 'Herroepingsverklaring niet gevonden.',
            'no-permission' => 'U hebt geen toestemming om EU-herroepingen te beheren.',
            'declined' => 'Herroeping gemarkeerd als afgewezen.',
            'refunded' => 'Herroeping gemarkeerd als terugbetaald.',
            'confirmation-resent' => 'Bevestigingsmail succesvol opnieuw verzonden.',
            'confirmation-failed' => 'De bevestigingsmail kon niet worden verzonden.',
        ],

        'reporting' => [
            'invalid-entity' => 'Onbekende rapportage-entiteit.',
            'invalid-type' => 'Onbekend type rapportagestatistiek voor deze entiteit.',
            'export-format-unsupported' => 'Alleen de exportformaten csv, xls en xlsx worden ondersteund.',
        ],

        'configuration' => [
            'unauthenticated' => 'U bent niet geverifieerd.',
            'no-permission' => 'U hebt geen toestemming om de configuratie bij te werken.',
            'slug-required' => 'Queryparameter `slug` is vereist.',
            'slug-not-found' => 'Er is geen configuratiesectie geregistreerd voor de opgegeven slug.',
            'values-required' => 'Ten minste één waarde is vereist onder `values`.',
            'scope-escape' => 'De sleutel `:key` valt buiten de subboom van de gevraagde slug.',
            'unknown-field' => 'De sleutel `:key` is geen geregistreerd configuratieveld.',
            'validation-failed' => 'Een of meer configuratiewaarden zijn niet door de validatie gekomen.',
            'file-upload-rest-only' => 'Uploads van binaire bestanden voor het veld `:field` worden alleen ondersteund via het REST-multipart-endpoint, niet via GraphQL.',
            'custom-view-readonly' => 'Het veld `:field` is een aangepast, via Blade weergegeven veld en kan niet via de API worden bijgewerkt.',
            'update-success' => 'Configuratie succesvol bijgewerkt.',
        ],

        'customer' => [
            'not-found' => 'Klant niet gevonden.',
            'no-permission' => 'U hebt geen toestemming om deze actie uit te voeren.',
            'has-active-orders' => 'De klant heeft openstaande of in behandeling zijnde bestellingen en kan niet worden verwijderd.',
            'deleted' => 'Klant succesvol verwijderd.',
            'mass-delete-indices-required' => 'Ten minste één klant-id is vereist.',
            'mass-delete-success' => 'Klanten verwijderd.',
            'mass-update-indices-required' => 'Ten minste één klant-id is vereist.',
            'mass-update-value-invalid' => 'De statuswaarde moet 0 of 1 zijn.',
            'mass-update-success' => 'Klantstatus bijgewerkt.',

            'address' => [
                'not-found' => 'Adres niet gevonden.',
                'not-owned' => 'Dit adres behoort niet toe aan de opgegeven klant.',
                'customer-id-required' => 'customerId is vereist om de eigenaar van het adres te bepalen.',
                'deleted' => 'Adres succesvol verwijderd.',
            ],

            'note' => [
                'empty' => 'Notitietekst is vereist.',
                'created' => 'Notitie succesvol toegevoegd.',
            ],

            'impersonate' => [
                'success' => 'Token voor imitatie uitgegeven.',
            ],

            'review' => [
                'not-found' => 'Beoordeling niet gevonden.',
                'no-permission' => 'U hebt geen toestemming om deze actie uit te voeren.',
                'invalid-status' => 'De status moet een van de volgende zijn: pending, approved, disapproved.',
                'updated' => 'Beoordeling succesvol bijgewerkt.',
                'deleted' => 'Beoordeling succesvol verwijderd.',
                'mass-delete-indices-required' => 'Ten minste één beoordeling-id is vereist.',
                'mass-delete-success' => 'Beoordelingen verwijderd.',
                'mass-update-indices-required' => 'Ten minste één beoordeling-id is vereist.',
                'mass-update-value-invalid' => 'De statuswaarde moet een van de volgende zijn: pending, approved, disapproved.',
                'mass-update-success' => 'Beoordelingsstatus bijgewerkt.',
            ],

            'group' => [
                'not-found' => 'Klantgroep niet gevonden.',
                'deleted' => 'Klantgroep succesvol verwijderd.',
                'is-system' => 'Systeemklantgroepen kunnen niet worden verwijderd.',
                'has-customers' => 'Aan de klantgroep zijn klanten gekoppeld; deze kan niet worden verwijderd.',
                'system-code-immutable' => 'De code van een systeemklantgroep kan niet worden gewijzigd.',
                'system-flag-immutable' => 'De vlag is_user_defined van een systeemklantgroep kan niet worden gewijzigd.',
                'mass-delete-indices-required' => 'Ten minste één klantgroep-id is vereist.',
                'mass-delete-success' => 'Klantgroepen verwijderd.',
            ],

            'gdpr' => [
                'not-found' => 'AVG-verzoek niet gevonden.',
                'no-permission' => 'U hebt geen toestemming om deze actie uit te voeren.',
                'deleted' => 'AVG-verzoek succesvol verwijderd.',
                'invalid-status' => 'De status moet een van de volgende zijn: pending, processing, declined, approved, revoked.',
                'no-changes' => 'Ten minste een status of bericht is vereist.',
                'already-processed' => 'Dit AVG-verzoek is al verwerkt en kan niet opnieuw worden uitgevoerd.',

                'process' => [
                    'approved' => 'AVG-verzoek goedgekeurd.',
                    'deleted' => 'AVG-verzoek goedgekeurd en klant verwijderd.',
                ],
            ],
        ],

        'order' => [
            'not-found' => 'Bestelling niet gevonden.',

            'reorder' => [
                'success' => 'Opnieuw bestellen gelukt. Er is een nieuwe conceptwinkelwagen aangemaakt.',
                'cannot-reorder' => 'Deze bestelling kan niet opnieuw worden besteld (gastbestelling, of een of meer items zijn niet meer verkoopbaar).',
                'failed' => 'Opnieuw bestellen mislukt. Probeer het opnieuw.',
                'guest-not-supported' => 'Opnieuw bestellen wordt niet ondersteund voor gastbestellingen.',
                'items-not-saleable' => 'Een of meer items in deze bestelling zijn niet meer beschikbaar voor aankoop.',
                'no-permission' => 'U hebt geen toestemming om bestellingen aan te maken.',
                'disabled-in-settings' => 'Opnieuw bestellen door de beheerder is uitgeschakeld in de winkelinstellingen.',
            ],

            'actions' => [
                'cancel' => [
                    'closed' => 'Gesloten bestellingen kunnen niet worden geannuleerd.',
                    'fraud' => 'Frauduleuze bestellingen kunnen niet worden geannuleerd.',
                    'nothing-to-cancel' => 'Er valt niets te annuleren in deze bestelling.',
                    'already-processed' => 'Deze bestelling is al gefactureerd of verzonden en kan niet meer worden geannuleerd. U kunt in plaats daarvan een terugbetaling uitvoeren.',
                    'no-permission' => 'U hebt geen toestemming om bestellingen te annuleren.',
                    'success' => 'Bestelling succesvol geannuleerd.',
                    'failed' => 'Kan de bestelling niet annuleren.',
                ],

                'comment' => [
                    'empty' => 'Opmerking is vereist.',
                    'success' => 'Opmerking aan de bestelling toegevoegd.',
                    'failed' => 'Kan de opmerking niet toevoegen.',
                ],

                'invoice' => [
                    'closed' => 'Voor gesloten bestellingen kan geen factuur worden gemaakt.',
                    'fraud' => 'Voor frauduleuze bestellingen kan geen factuur worden gemaakt.',
                    'nothing-to-invoice' => 'Er valt niets te factureren in deze bestelling.',
                    'already-invoiced' => 'Er is al een factuur voor deze bestelling gegenereerd.',
                    'paypal-standard-blocked' => 'Er kunnen geen facturen worden aangemaakt voor bestellingen die via PayPal Standard zijn betaald.',
                    'no-permission' => 'U hebt geen toestemming om facturen aan te maken.',
                    'items-required' => 'Ten minste één item met een positief aantal is vereist.',
                    'qty-exceeds' => 'Het gevraagde aantal (:requested) overschrijdt het beschikbare aantal (:available) voor SKU :sku.',
                    'not-found' => 'Factuur niet gevonden.',
                    'success' => 'Factuur succesvol aangemaakt.',
                    'failed' => 'Kan de factuur niet aanmaken.',
                    'pdf-failed' => 'Kan de factuur-PDF niet genereren.',
                    'email-invalid' => 'Een geldig e-mailadres van de ontvanger is vereist.',
                    'duplicate-sent' => 'Factuurmail verzonden naar :email.',
                    'mass-status-indices-required' => 'Ten minste één factuur-id is vereist.',
                    'mass-status-value-invalid' => 'De status moet een van de volgende zijn: pending, paid, overdue.',
                    'mass-status-success' => 'Factuurstatus succesvol bijgewerkt.',
                ],

                'shipment' => [
                    'closed' => 'Gesloten bestellingen kunnen niet worden verzonden.',
                    'fraud' => 'Frauduleuze bestellingen kunnen niet worden verzonden.',
                    'nothing-to-ship' => 'Er valt niets te verzenden in deze bestelling.',
                    'no-permission' => 'U hebt geen toestemming om bestellingen te verzenden.',
                    'source-required' => 'Voorraadbron is vereist.',
                    'items-required' => 'Ten minste één item met een positief aantal is vereist.',
                    'qty-exceeds' => 'Het gevraagde aantal (:requested) overschrijdt het beschikbare aantal (:available) voor SKU :sku.',
                    'inventory-insufficient' => 'De voorraad bij de geselecteerde bron is onvoldoende voor SKU :sku.',
                    'not-found' => 'Verzending niet gevonden.',
                    'success' => 'Verzending succesvol aangemaakt.',
                    'failed' => 'Kan de verzending niet aanmaken.',
                ],

                'refund' => [
                    'closed' => 'Gesloten bestellingen kunnen niet worden terugbetaald.',
                    'fraud' => 'Frauduleuze bestellingen kunnen niet worden terugbetaald.',
                    'nothing-to-refund' => 'Er valt niets meer terug te betalen in deze bestelling.',
                    'no-permission' => 'U hebt geen toestemming om terugbetalingen aan te maken.',
                    'qty-exceeds' => 'Het gevraagde aantal (:requested) overschrijdt het beschikbare aantal (:available) voor SKU :sku.',
                    'amount-zero' => 'Het berekende terugbetalingsbedrag is nul. Pas het aantal, de verzendkosten of de correctiewaarden aan.',
                    'amount-exceeds-max' => 'Het terugbetalingsbedrag (:amount) overschrijdt het maximaal terug te betalen bedrag (:max).',
                    'not-found' => 'Terugbetaling niet gevonden.',
                    'success' => 'Terugbetaling succesvol aangemaakt.',
                    'failed' => 'Kan de terugbetaling niet aanmaken.',
                ],
            ],
        ],

        'appearance' => [
            'no-permission' => 'U hebt geen toestemming om de weergave te beheren.',

            'theme' => [
                'not-found' => 'Thema niet gevonden.',
                'not-installed' => 'Dit thema is niet geïnstalleerd.',
                'channel-ids-required' => 'Ten minste één kanaal is vereist.',
                'unknown-channel' => 'Een of meer kanalen bestaan niet.',
                'activated' => 'Thema succesvol geactiveerd.',
            ],

            'section' => [
                'not-found' => 'Sectie niet gevonden.',
                'invalid-payload' => 'Niet-ondersteunde sectiepayload.',
                'footer-links-exists' => 'Dit kanaal heeft al een sectie met footerlinks.',
                'options-required' => 'Opties zijn vereist.',
                'status-required' => 'Status is vereist.',
                'section-ids-required' => 'Ten minste één sectie is vereist.',
                'reorder-single-scope' => 'Secties kunnen slechts binnen één thema en kanaal tegelijk opnieuw worden gerangschikt.',
                'reorder-incomplete' => 'Voor het herschikken zijn alle secties van het thema en kanaal nodig, in de volgorde waarin ze moeten worden weergegeven.',
                'unknown-section' => 'Een of meer secties bestaan niet.',
                'file-required' => 'Een bestand is vereist.',
                'created' => 'Sectie succesvol aangemaakt.',
                'updated' => 'Sectie succesvol bijgewerkt.',
                'deleted' => 'Sectie succesvol verwijderd.',
                'draft-saved' => 'Sectiewijzigingen succesvol klaargezet.',
                'status-staged' => 'Sectiestatus succesvol klaargezet.',
                'order-staged' => 'Sectievolgorde succesvol klaargezet.',
                'duplicated' => 'Sectie succesvol gekopieerd.',
                'published' => 'Sectiewijzigingen succesvol gepubliceerd.',
                'discarded' => 'Sectiewijzigingen succesvol verworpen.',
                'media-uploaded' => 'Media succesvol geüpload.',
            ],
        ],

        'sales' => [
            'no-permission' => 'U hebt geen toestemming om deze verkoopbron te bekijken.',

            'invoice' => [
                'not-found' => 'Factuur niet gevonden.',
                'no-permission' => 'U hebt geen toestemming om facturen te bekijken.',
            ],

            'shipment' => [
                'not-found' => 'Verzending niet gevonden.',
                'no-permission' => 'U hebt geen toestemming om verzendingen te bekijken.',
            ],

            'refund' => [
                'not-found' => 'Terugbetaling niet gevonden.',
                'no-permission' => 'U hebt geen toestemming om terugbetalingen te bekijken.',
                'export-format-unsupported' => 'Alleen exports in csv, xls en xlsx worden ondersteund.',
            ],

            'transaction' => [
                'not-found' => 'Transactie niet gevonden.',
                'no-permission' => 'U hebt geen toestemming om transacties te bekijken.',

                'create' => [
                    'invoice-required' => 'Het veld invoiceId is vereist.',
                    'payment-method-required' => 'Het veld paymentMethod is vereist.',
                    'amount-invalid' => 'Het veld amount is vereist en moet numeriek zijn.',
                    'invoice-missing' => 'De opgegeven factuur kon niet worden gevonden.',
                    'already-paid' => 'Deze factuur is al volledig betaald.',
                    'amount-exceeds' => 'Het betalingsbedrag overschrijdt het eindtotaal van de factuur.',
                    'amount-zero' => 'Het betalingsbedrag moet groter zijn dan nul.',
                ],
            ],

            'booking' => [
                'not-found' => 'Boeking niet gevonden.',
                'no-permission' => 'U hebt geen toestemming om boekingen te bekijken.',
            ],

            'export' => [
                'format-unsupported' => 'Alleen exports in csv, xls en xlsx worden ondersteund.',
            ],
        ],

        'cart' => [
            'not-found' => 'Winkelwagen niet gevonden.',
            'not-draft' => 'Deze winkelwagen kan niet via de admin-API worden gewijzigd.',
            'product-required' => 'productId is vereist.',
            'product-not-found' => 'Product niet gevonden.',
            'booking-unsupported' => 'Boekingsproducten kunnen niet aan een conceptbestelling van de beheerder worden toegevoegd. Boekingen moeten via de storefront van de klant worden gedaan.',
            'product-not-saleable' => 'Dit product is niet te koop (niet op voorraad of uitgeschakeld) en kan niet aan de bestelling worden toegevoegd.',
            'qty-required' => 'qty is vereist en moet een niet-leeg object van itemId => aantal zijn.',
            'cart-item-required' => 'cartItemId is vereist.',
            'item-added' => 'Item aan winkelwagen toegevoegd.',
            'item-add-failed' => 'Kan het item niet aan de winkelwagen toevoegen.',
            'item-updated' => 'Winkelwagenitems bijgewerkt.',
            'item-update-failed' => 'Kan de winkelwagenitems niet bijwerken.',
            'item-removed' => 'Item uit winkelwagen verwijderd.',
            'item-remove-failed' => 'Kan het winkelwagenitem niet verwijderen.',
            'address-saved' => 'Adres opgeslagen.',
            'address-required' => 'Een factuuradres is vereist.',
            'address-incomplete' => ':field is vereist om het adres op te slaan.',
            'coupon-code-required' => 'Een couponcode is vereist.',
            'coupon-applied' => 'Coupon toegepast.',
            'coupon-already-applied' => 'De coupon is al op deze winkelwagen toegepast.',
            'coupon-not-found' => 'Coupon niet gevonden of inactief.',
            'coupon-removed' => 'Coupon verwijderd.',
            'coupon-error' => 'Er is iets misgegaan bij het toepassen van de coupon.',
            'unknown-error' => 'Er is iets misgegaan.',
            'draft-created' => 'Conceptwinkelwagen aangemaakt.',
            'draft-failed' => 'Kan de conceptwinkelwagen niet aanmaken.',
            'addresses-required-for-shipping' => 'Adressen moeten worden opgeslagen voordat een verzendmethode wordt gekozen.',
            'shipping-required-for-payment' => 'Er moet een verzendmethode worden gekozen vóór de betaalmethode.',
            'shipping-method-required' => 'Verzendmethode is vereist.',
            'shipping-method-saved' => 'Verzendmethode opgeslagen.',
            'shipping-method-failed' => 'Kan de verzendmethode niet opslaan.',
            'payment-method-required' => 'Betaalmethode is vereist.',
            'payment-method-saved' => 'Betaalmethode opgeslagen.',
            'payment-method-failed' => 'Kan de betaalmethode niet opslaan.',

            'place-order' => [
                'success' => 'Bestelling succesvol geplaatst.',
                'failed' => 'Kan de bestelling niet plaatsen.',
                'error' => 'Kan de bestelling niet plaatsen: de winkelwagen bevat fouten.',
                'minimum-order-error' => 'Het minimale bestelbedrag is :amount. Voeg meer items toe voordat u de bestelling plaatst.',
                'empty-cart' => 'De winkelwagen is leeg — voeg items toe voordat u een bestelling plaatst.',
                'addresses-required' => 'Factuur- en verzendadres moeten worden opgeslagen voordat de bestelling wordt geplaatst.',
                'shipping-required' => 'Er moet een verzendmethode worden gekozen voordat de bestelling wordt geplaatst.',
                'payment-required' => 'Er moet een betaalmethode worden gekozen voordat de bestelling wordt geplaatst.',
                'payment-method-unsupported' => 'Alleen cashondelivery en moneytransfer worden ondersteund voor bestellingen die door de beheerder worden geplaatst.',
            ],
        ],

        'product' => [
            'not-found' => 'Product niet gevonden.',

            'mass-delete-success' => 'Producten succesvol verwijderd.',
            'mass-update-status-success' => 'Productstatus succesvol bijgewerkt.',
            'indices-required' => 'Het veld indices is vereist en moet een niet-lege array zijn.',
            'indices-invalid' => 'Het veld indices moet een array van gehele product-ID\'s zijn.',
            'value-invalid' => 'Het veld value is vereist en moet 0 of 1 zijn.',
            'mass-delete-failed' => 'Een of meer producten konden niet worden verwijderd.',
            'no-permission' => 'U hebt geen toestemming om producten te beheren.',

            'copy-success' => 'Product succesvol gekopieerd.',
            'copy-failed' => 'Kopiëren van product mislukt.',
            'copy-variant-not-supported' => 'Varianten van configureerbare producten kunnen niet worden gekopieerd. Kopieer in plaats daarvan het bovenliggende configureerbare product.',

            'create' => [
                'created' => 'Product succesvol aangemaakt.',
                'create-failed' => 'Aanmaken van product mislukt.',
                'sku-required' => 'Het veld sku is vereist.',
                'sku-unique' => 'De sku is al in gebruik.',
                'sku-invalid' => 'De sku mag alleen letters, cijfers en koppeltekens bevatten (bijv. "sp-001").',
                'attribute-family-required' => 'Het veld attribute_family_id is vereist.',
                'attribute-family-not-found' => 'De geselecteerde attribuutfamilie bestaat niet.',
                'type-required' => 'Het veld type is vereist.',
                'type-not-yet-supported' => 'Producttype ":type" wordt niet ondersteund door deze API. Toegestane typen: simple, virtual, downloadable, grouped, bundle, configurable, booking.',
                'super-attributes-required' => 'Het veld super_attributes is vereist wanneer type=configurable. Geef een map van attribuutcode (of id) naar option_ids op, bijv. { "color": [1,2], "size": [4,5] }.',
                'super-attributes-invalid' => 'Het veld super_attributes moet een niet-lege map zijn van attribuutcode (of id) naar een niet-lege lijst van option_ids.',
                'no-permission' => 'U hebt geen toestemming om producten aan te maken.',
            ],

            'update' => [
                'updated' => 'Product succesvol bijgewerkt.',
                'update-failed' => 'Bijwerken van product mislukt.',
                'id-required' => 'Een product-id is vereist.',
                'url-key-required' => 'Het veld url_key mag niet leeg zijn.',
                'url-key-unique' => 'De url_key is al in gebruik door een ander product.',
                'special-price-invalid' => 'De special_price moet een getal zijn dat lager is dan price.',
                'special-price-date-range-invalid' => 'De special_price_to moet op of na special_price_from liggen.',
                'boolean-field-invalid' => 'Het veld :field moet 0 of 1 zijn.',
                'categories-invalid' => 'Het veld categories moet een array van categorie-id\'s zijn.',
                'channels-invalid' => 'Het veld channels moet een array van kanaal-id\'s zijn.',
                'super-attributes-cannot-change' => 'super_attributes kan niet worden gewijzigd nadat het product is aangemaakt.',
                'sub-resource-stripped-images' => 'Afbeeldingen moeten worden beheerd via POST /api/admin/catalog/products/{id}/images. Het veld images is genegeerd.',
                'sub-resource-stripped-inventories' => 'Voorraden moeten worden beheerd via PUT /api/admin/catalog/products/{id}/inventories. Het veld inventories is genegeerd.',
                'sub-resource-stripped-customer-group-prices' => 'Klantgroepprijzen moeten worden beheerd via /api/admin/catalog/products/{id}/customer-group-prices. Het veld customer_group_prices is genegeerd.',
                'translations-single-locale' => 'Alleen de gevraagde taal is bijgewerkt. Om taal/talen :locales bij te werken, stuurt u voor elke taal een afzonderlijk verzoek met ?locale=<code>.',
            ],

            'delete' => [
                'deleted' => 'Product succesvol verwijderd.',
                'delete-failed' => 'Verwijderen van product mislukt.',
            ],

            'image' => [
                'uploaded' => 'Productafbeelding succesvol geüpload.',
                'updated' => 'Productafbeelding succesvol bijgewerkt.',
                'update-empty' => 'Stuur een alt-tekst of een positie om bij te werken.',
                'reordered' => 'Productafbeeldingen succesvol opnieuw gerangschikt.',
                'deleted' => 'Productafbeelding succesvol verwijderd.',
                'image-required' => 'Een afbeeldingsbestand is vereist (multipart-veld "image").',
                'image-invalid-type' => 'Het afbeeldingstype moet een van de volgende zijn: bmp, jpeg, jpg, png, webp.',
                'image-too-large' => 'De afbeelding is te groot. De maximaal toegestane grootte is 4 MB.',
                'not-found' => 'Productafbeelding niet gevonden.',
                'no-permission' => 'U hebt geen toestemming om productafbeeldingen te beheren.',
                'upload-failed' => 'Uploaden van productafbeelding mislukt.',
                'reorder-failed' => 'Opnieuw rangschikken van productafbeeldingen mislukt.',
                'delete-failed' => 'Verwijderen van productafbeelding mislukt.',
                'order-required' => 'De array "order" is vereist en mag niet leeg zijn.',
                'order-invalid' => 'Elk item in "order" moet een object met id en position zijn.',
                'id-not-on-product' => 'Afbeelding :id behoort niet tot dit product.',
                'graphql-upload-unsupported' => 'Het uploaden van afbeeldingen via GraphQL wordt niet ondersteund. Gebruik POST /api/admin/catalog/products/{productId}/images met multipart/form-data.',
            ],

            'downloadable' => [
                'file-required' => 'Een bestand is vereist (multipart-veld "file").',
                'file-too-large' => 'Het bestand is te groot. De maximaal toegestane grootte is 128 MB.',
                'upload-failed' => 'Uploaden van het downloadbare bestand mislukt.',
                'file-not-found' => 'Geen opgeslagen bestand gevonden voor dit product en attribuut.',
                'no-permission' => 'U hebt geen toestemming om bestanden van downloadbare producten te beheren.',
            ],

            'video' => [
                'uploaded' => 'Productvideo succesvol geüpload.',
                'deleted' => 'Productvideo succesvol verwijderd.',
                'video-required' => 'Een videobestand is vereist (multipart-veld "video").',
                'video-invalid-type' => 'Het videotype moet een van de volgende zijn: mp4, webm, ogg, mov.',
                'video-too-large' => 'De video is te groot. De maximaal toegestane grootte is 100 MB.',
                'not-found' => 'Productvideo niet gevonden.',
                'no-permission' => 'U hebt geen toestemming om productvideo\'s te beheren.',
                'upload-failed' => 'Uploaden van productvideo mislukt.',
                'delete-failed' => 'Verwijderen van productvideo mislukt.',
                'graphql-upload-unsupported' => 'Het uploaden van video\'s via GraphQL wordt niet ondersteund. Gebruik POST /api/admin/catalog/products/{productId}/videos met multipart/form-data.',
            ],

            'inventory' => [
                'updated' => 'Productvoorraden succesvol opgeslagen.',
                'inventories-required' => 'Het veld inventories is vereist en moet een niet-lege map van inventory_source_id naar aantal zijn.',
                'inventories-invalid' => 'Het veld inventories moet een map zijn van inventory_source_id (positief geheel getal) naar aantal (niet-negatief geheel getal).',
                'source-not-found' => 'Voorraadbron :id bestaat niet.',
                'qty-invalid' => 'Elk aantal moet een niet-negatief geheel getal zijn.',
                'no-permission' => 'U hebt geen toestemming om productvoorraden te beheren.',
                'not-found' => 'Product niet gevonden.',
            ],

            'customer-group-price' => [
                'created' => 'Klantgroepprijs succesvol toegevoegd.',
                'updated' => 'Klantgroepprijs succesvol bijgewerkt.',
                'deleted' => 'Klantgroepprijs succesvol verwijderd.',
                'qty-required' => 'Het veld qty is vereist.',
                'qty-invalid' => 'Het veld qty moet een geheel getal groter dan of gelijk aan 1 zijn.',
                'value-type-required' => 'Het veld value_type is vereist.',
                'value-type-invalid' => 'Het veld value_type moet "fixed" of "discount" zijn.',
                'value-required' => 'Het veld value is vereist.',
                'value-invalid' => 'Het veld value moet een niet-negatief getal zijn.',
                'customer-group-not-found' => 'De geselecteerde klantgroep bestaat niet.',
                'duplicate-qty-group' => 'Er bestaat al een klantgroepprijs voor deze combinatie (qty, customer_group_id) bij het product.',
                'not-found' => 'Klantgroepprijs niet gevonden.',
                'no-permission' => 'U hebt geen toestemming om klantgroepprijzen van producten te beheren.',
            ],
        ],

        'category' => [
            'not-found' => 'Categorie niet gevonden.',

            'created' => 'Categorie succesvol aangemaakt.',
            'updated' => 'Categorie succesvol bijgewerkt.',
            'deleted' => 'Categorie succesvol verwijderd.',
            'slug-required' => 'Het veld slug is vereist.',
            'slug-unique' => 'De slug is al in gebruik.',
            'name-required' => 'Het veld name is vereist.',
            'position-required' => 'Het veld position is vereist.',
            'attributes-required' => 'Ten minste één filterbaar attribuut is vereist.',
            'description-required-for-mode' => 'Voor de gekozen weergavemodus is een beschrijving vereist.',
            'cannot-delete-root' => 'Hoofdcategorieën en hoofdcategorieën van kanalen kunnen niet worden verwijderd.',
            'cannot-delete-channel-root' => 'Deze categorie is de hoofdcategorie van een kanaal en kan niet worden verwijderd.',
            'delete-failed' => 'De categorie kon niet worden verwijderd.',
            'no-permission' => 'U hebt geen toestemming om categorieën te beheren.',
            'mass-delete-success' => 'Categorieën succesvol verwijderd.',
            'mass-update-status-success' => 'Categoriestatus succesvol bijgewerkt.',
            'mass-delete-indices-required' => 'Het veld indices is vereist en moet een niet-lege array zijn.',
            'mass-update-status-value-required' => 'Het veld value is vereist en moet 0 of 1 zijn.',
        ],

        'attribute' => [
            'not-found' => 'Attribuut niet gevonden.',

            'create-success' => 'Attribuut succesvol aangemaakt.',
            'update-success' => 'Attribuut succesvol bijgewerkt.',
            'delete-success' => 'Attribuut succesvol verwijderd.',
            'delete-failed' => 'Het attribuut kon niet worden verwijderd.',
            'mass-delete-success' => 'Attributen succesvol verwijderd.',
            'mass-delete-indices-required' => 'Het veld indices is vereist en moet een niet-lege array zijn.',

            'code-immutable' => 'De attribuutcode kan niet worden gewijzigd nadat het attribuut is aangemaakt.',
            'type-immutable' => 'Het attribuuttype kan niet worden gewijzigd zolang er productwaarden zijn.',
            'locale-scope-immutable' => 'Het taalbereik kan niet worden gewijzigd zolang het attribuut productwaarden heeft.',
            'system-attribute' => 'Systeemattributen kunnen niet worden verwijderd.',
            'in-use-family' => 'Het attribuut maakt deel uit van een of meer attribuutfamilies (groep-ID\'s: :ids). Verwijder het eerst uit die families.',

            'option-not-found' => 'Attribuutoptie niet gevonden.',
            'option-not-supported' => 'Attribuuttype ":type" ondersteunt geen opties. Alleen attributen van het type select, multiselect en checkbox kunnen opties hebben.',
            'option-in-use' => 'Deze optie wordt gebruikt door :count product(en) en kan niet worden verwijderd.',
            'option-delete-success' => 'Attribuutoptie succesvol verwijderd.',
        ],

        'family' => [
            'not-found' => 'Attribuutfamilie niet gevonden.',

            'created' => 'Attribuutfamilie succesvol aangemaakt.',
            'updated' => 'Attribuutfamilie succesvol bijgewerkt.',
            'deleted' => 'Attribuutfamilie succesvol verwijderd.',
            'code-required' => 'De familiecode is vereist.',
            'code-unique' => 'De familiecode moet uniek zijn.',
            'name-required' => 'De familienaam is vereist.',
            'group-code-required' => 'Elke attribuutgroep moet een code hebben.',
            'group-name-required' => 'Elke attribuutgroep moet een naam hebben.',
            'group-column-invalid' => 'Elke kolom van een attribuutgroep moet 1 of 2 zijn.',
            'group-name-duplicate' => 'De naam van een attribuutgroep moet uniek zijn binnen de attribuutfamilie.',
            'update-failed' => 'De attribuutfamilie kon niet worden bijgewerkt.',
            'create-failed' => 'De attribuutfamilie kon niet worden aangemaakt.',
            'default-delete-error' => 'De standaardattribuutfamilie kan niet worden verwijderd.',
            'last-delete-error' => 'Ten minste één attribuutfamilie is vereist.',
            'attribute-product-error' => 'Deze attribuutfamilie wordt door een of meer producten gebruikt. Wijs die producten opnieuw toe voordat u deze verwijdert.',
            'delete-failed' => 'De attribuutfamilie kon niet worden verwijderd.',
            'no-permission' => 'U hebt geen toestemming om attribuutfamilies te beheren.',
        ],

        'settings' => [
            'locale' => [
                'not-found' => 'Taal niet gevonden.',
                'created' => 'Taal succesvol aangemaakt.',
                'updated' => 'Taal succesvol bijgewerkt.',
                'deleted' => 'Taal succesvol verwijderd.',
                'cannot-delete-last' => 'Ten minste één taal is vereist — de laatst overgebleven taal kan niet worden verwijderd.',
                'cannot-delete-channel-default' => 'Deze taal is de standaardtaal van een of meer kanalen en kan niet worden verwijderd.',
                'delete-failed' => 'De taal kon niet worden verwijderd.',
                'no-permission' => 'U hebt geen toestemming om talen te beheren.',
                'mass-delete-success' => 'Talen succesvol verwijderd.',
                'mass-delete-indices-required' => 'Het veld indices is vereist en moet een niet-lege array zijn.',
            ],

            'theme' => [
                'not-found' => 'Thema-aanpassing niet gevonden.',
                'created' => 'Thema-aanpassing succesvol aangemaakt.',
                'updated' => 'Thema-aanpassing succesvol bijgewerkt.',
                'deleted' => 'Thema-aanpassing succesvol verwijderd.',
                'delete-failed' => 'De thema-aanpassing kon niet worden verwijderd.',
                'no-permission' => 'U hebt geen toestemming om thema-aanpassingen te beheren.',
                'mass-delete-success' => 'Thema-aanpassingen succesvol verwijderd.',
                'mass-delete-indices-required' => 'Het veld indices is vereist en moet een niet-lege array zijn.',
                'mass-update-success' => 'Thema-aanpassingen succesvol bijgewerkt.',
                'mass-update-indices-required' => 'Het veld indices is vereist en moet een niet-lege array zijn.',
                'mass-update-value-invalid' => 'Het veld value moet 0 of 1 zijn.',
            ],

            'inventory-source' => [
                'not-found' => 'Voorraadbron niet gevonden.',
                'created' => 'Voorraadbron succesvol aangemaakt.',
                'updated' => 'Voorraadbron succesvol bijgewerkt.',
                'deleted' => 'Voorraadbron succesvol verwijderd.',
                'code-unique' => 'Er bestaat al een voorraadbron met deze code.',
                'last-delete-error' => 'Ten minste één voorraadbron is vereist — de laatst overgebleven bron kan niet worden verwijderd.',
                'in-use' => 'Naar deze voorraadbron wordt verwezen door productvoorraden; deze kan niet worden verwijderd.',
                'delete-failed' => 'De voorraadbron kon niet worden verwijderd.',
                'no-permission' => 'U hebt geen toestemming om voorraadbronnen te beheren.',
                'mass-delete-success' => 'Voorraadbronnen succesvol verwijderd.',
                'mass-delete-indices-required' => 'Het veld indices is vereist en moet een niet-lege array zijn.',
            ],

            'exchange-rate' => [
                'not-found' => 'Wisselkoers niet gevonden.',
                'created' => 'Wisselkoers succesvol aangemaakt.',
                'updated' => 'Wisselkoers succesvol bijgewerkt.',
                'deleted' => 'Wisselkoers succesvol verwijderd.',
                'duplicate-pair' => 'Er bestaat al een wisselkoers voor deze doelvaluta. Werk de bestaande rij bij of verwijder deze eerst.',
                'delete-failed' => 'De wisselkoers kon niet worden verwijderd.',
                'no-permission' => 'U hebt geen toestemming om wisselkoersen te beheren.',
                'mass-delete-success' => 'Wisselkoersen succesvol verwijderd.',
                'mass-delete-indices-required' => 'Het veld indices is vereist en moet een niet-lege array zijn.',
                'update-rates-success' => 'Wisselkoersen succesvol bijgewerkt.',
                'update-rates-failed' => 'De wisselkoersen konden niet worden bijgewerkt via de externe provider.',
                'provider-not-configured' => 'Er is geen externe wisselkoersprovider geconfigureerd.',
            ],

            'tax-rate' => [
                'not-found' => 'Belastingtarief niet gevonden.',
                'created' => 'Belastingtarief succesvol aangemaakt.',
                'updated' => 'Belastingtarief succesvol bijgewerkt.',
                'deleted' => 'Belastingtarief succesvol verwijderd.',
                'delete-failed' => 'Het belastingtarief kon niet worden verwijderd.',
                'no-permission' => 'U hebt geen toestemming om belastingtarieven te beheren.',
                'zip-code-required' => 'Het veld zip_code is vereist wanneer is_zip false is.',
                'zip-range-required' => 'Zowel zip_from als zip_to zijn vereist wanneer is_zip true is.',
            ],

            'tax-category' => [
                'not-found' => 'Belastingcategorie niet gevonden.',
                'created' => 'Belastingcategorie succesvol aangemaakt.',
                'updated' => 'Belastingcategorie succesvol bijgewerkt.',
                'deleted' => 'Belastingcategorie succesvol verwijderd.',
                'code-unique' => 'De code is al in gebruik.',
                'cannot-delete-in-use' => 'Aan deze belastingcategorie zijn nog belastingtarieven gekoppeld; deze kan niet worden verwijderd.',
                'delete-failed' => 'De belastingcategorie kon niet worden verwijderd.',
                'no-permission' => 'U hebt geen toestemming om belastingcategorieën te beheren.',
            ],

            'role' => [
                'not-found' => 'Rol niet gevonden.',
                'created' => 'Rol succesvol aangemaakt.',
                'updated' => 'Rol succesvol bijgewerkt.',
                'deleted' => 'Rol succesvol verwijderd.',
                'permissions-required' => 'Het veld permissions is vereist wanneer permission_type custom is.',
                'cannot-delete-in-use' => 'Deze rol is toegewezen aan een of meer beheerders en kan niet worden verwijderd.',
                'cannot-delete-last-role' => 'Ten minste één rol is vereist — de laatst overgebleven rol kan niet worden verwijderd.',
                'cannot-change-last-all-access' => 'De enige rol met volledige toegang kan niet worden gewijzigd in een aangepaste rol.',
                'delete-failed' => 'De rol kon niet worden verwijderd.',
                'no-permission' => 'U hebt geen toestemming om rollen te beheren.',
            ],

            'currency' => [
                // Read
                'not-found' => 'Valuta niet gevonden.',

                // Create / Update / Delete
                'created' => 'Valuta succesvol aangemaakt.',
                'updated' => 'Valuta succesvol bijgewerkt.',
                'deleted' => 'Valuta succesvol verwijderd.',
                'code-required' => 'Het veld code is vereist.',
                'code-size' => 'De code moet uit precies 3 letters bestaan.',
                'code-unique' => 'De code is al in gebruik.',
                'name-required' => 'Het veld name is vereist.',
                'cannot-delete-last' => 'Ten minste één valuta is vereist — de laatst overgebleven valuta kan niet worden verwijderd.',
                'cannot-delete-channel-base' => 'Deze valuta is de basisvaluta van een of meer kanalen en kan niet worden verwijderd.',
                'delete-failed' => 'De valuta kon niet worden verwijderd.',
                'no-permission' => 'U hebt geen toestemming om valuta\'s te beheren.',

                'mass-delete-success' => 'Valuta\'s succesvol verwijderd.',
                'mass-delete-indices-required' => 'Het veld indices is vereist en moet een niet-lege array zijn.',
            ],

            'channel' => [
                'not-found' => 'Kanaal niet gevonden.',

                'created' => 'Kanaal succesvol aangemaakt.',
                'updated' => 'Kanaal succesvol bijgewerkt.',
                'deleted' => 'Kanaal succesvol verwijderd.',
                'code-unique' => 'De code is al in gebruik.',
                'hostname-unique' => 'De hostnaam is al in gebruik.',
                'default-locale-mismatch' => 'De default_locale_id moet in de lijst met talen voorkomen.',
                'base-currency-mismatch' => 'De base_currency_id moet in de lijst met valuta\'s voorkomen.',
                'unknown-locale' => 'Een of meer taal-id\'s bestaan niet.',
                'unknown-currency' => 'Een of meer valuta-id\'s bestaan niet.',
                'unknown-inventory-source' => 'Een of meer inventory_source-id\'s bestaan niet.',
                'unknown-root-category' => 'De root_category_id bestaat niet.',
                'cannot-delete-last' => 'Ten minste één kanaal is vereist — het laatst overgebleven kanaal kan niet worden verwijderd.',
                'cannot-delete-default' => 'Het standaardkanaal van de applicatie kan niet worden verwijderd.',
                'delete-failed' => 'Het kanaal kon niet worden verwijderd.',
                'no-permission' => 'U hebt geen toestemming om kanalen te beheren.',
            ],

            'user' => [
                'not-found' => 'Beheerder niet gevonden.',
                'created' => 'Beheerder succesvol aangemaakt.',
                'updated' => 'Beheerder succesvol bijgewerkt.',
                'deleted' => 'Beheerder succesvol verwijderd.',
                'name-required' => 'Het veld name is vereist.',
                'email-required' => 'Het veld email is vereist.',
                'email-invalid' => 'Het e-mailadres moet een geldig e-mailadres zijn.',
                'email-unique' => 'Het e-mailadres is al in gebruik.',
                'password-required' => 'Het veld password is vereist.',
                'password-min' => 'Het wachtwoord moet ten minste 6 tekens bevatten.',
                'role-required' => 'Het veld role_id is vereist.',
                'role-not-found' => 'De geselecteerde rol bestaat niet.',
                'status-invalid' => 'De status moet 0 of 1 zijn.',
                'cannot-delete-self' => 'U kunt uw eigen beheerdersaccount niet verwijderen.',
                'cannot-delete-last-admin' => 'Ten minste één beheerder is vereist — de laatst overgebleven beheerder kan niet worden verwijderd.',
                'cannot-change' => 'De :name van de laatste beheerder met volledige toegang kan niet worden gewijzigd.',
                'delete-failed' => 'De beheerder kon niet worden verwijderd.',
                'no-permission' => 'U hebt geen toestemming om admingebruikers te beheren.',
                'self-delete-password-required' => 'Het veld password is vereist om uw eigen account te verwijderen.',
                'self-delete-incorrect-password' => 'Het wachtwoord is onjuist.',
                'self-deleted' => 'Uw beheerdersaccount is verwijderd.',
            ],

            'data-transfer' => [
                'import' => [
                    'not-found' => 'Import niet gevonden.',
                    'deleted' => 'Import succesvol verwijderd.',
                    'cancelled' => 'Import succesvol geannuleerd.',
                    'cannot-cancel' => 'Alleen openstaande of in behandeling zijnde imports kunnen worden geannuleerd. Huidige status: :state.',
                    'no-permission' => 'U hebt geen toestemming om imports voor gegevensoverdracht te beheren.',
                    'delete-failed' => 'De import kon niet worden verwijderd.',
                    'created' => 'Import succesvol aangemaakt.',
                    'updated' => 'Import succesvol bijgewerkt.',
                    'type-invalid' => 'Het geselecteerde type is ongeldig.',
                    'action-invalid' => 'De actie moet een van de volgende zijn: append, delete.',
                    'validation-strategy-invalid' => 'De validatiestrategie moet een van de volgende zijn: stop-on-errors, skip-errors.',
                    'allowed-errors-invalid' => 'Het aantal toegestane fouten moet een geheel getal van 0 of meer zijn.',
                    'field-separator-required' => 'Het veldscheidingsteken is vereist.',
                    'file-required' => 'Een bestand is vereist.',
                    'file-invalid-type' => 'Het bestand moet een van de volgende typen zijn: csv, xls, xlsx, xml.',
                    'nothing-to-import' => 'Er is niets om te importeren.',
                    'not-valid' => 'De import is niet geldig.',
                    'setup-queue-error' => 'De queue-verbinding is ingesteld op sync; configureer een echte queue om imports op de achtergrond te verwerken.',
                    'file-not-found' => 'Er is geen bestand beschikbaar om te downloaden.',
                    'error-report-not-found' => 'Er is geen foutrapport beschikbaar om te downloaden.',
                    'sample-not-found' => 'Er is geen voorbeeldbestand beschikbaar voor het gevraagde type en formaat.',
                    'sample-format-invalid' => 'Het voorbeeldformaat moet een van de volgende zijn: csv, xls, xlsx, xml.',
                    'images-archive-too-large' => 'Het afbeeldingsarchief mag maximaal :entries bestanden bevatten en uitgepakt maximaal :size MB groot zijn.',
                ],
            ],
        ],

        'cms' => [
            'page' => [
                'not-found' => 'CMS-pagina niet gevonden.',

                'created' => 'CMS-pagina succesvol aangemaakt.',
                'url-key-required' => 'Het veld url_key is vereist.',
                'url-key-unique' => 'De url_key is al in gebruik.',
                'url-key-invalid' => 'Het formaat van url_key is ongeldig. Gebruik alleen kleine letters, cijfers en koppeltekens.',
                'page-title-required' => 'Het veld page_title is vereist.',
                'html-content-required' => 'Het veld html_content is vereist.',
                'channels-required' => 'Er moet ten minste één kanaal worden geselecteerd.',
                'channels-invalid' => 'Een of meer kanalen zijn ongeldig.',

                'updated' => 'CMS-pagina succesvol bijgewerkt.',

                'deleted' => 'CMS-pagina succesvol verwijderd.',
                'delete-failed' => 'De CMS-pagina kon niet worden verwijderd.',

                'mass-deleted' => 'CMS-pagina\'s succesvol verwijderd.',
                'indices-required' => 'Het veld indices is vereist en moet een niet-lege array zijn.',
                'indices-invalid' => 'Het veld indices moet een array van CMS-pagina-ID\'s zijn.',

                'no-permission' => 'U hebt geen toestemming om CMS-pagina\'s te beheren.',
            ],
        ],

        'marketing' => [
            'catalog-rule' => [
                'not-found' => 'Catalogusregel niet gevonden.',
                'created' => 'Catalogusregel succesvol aangemaakt.',
                'updated' => 'Catalogusregel succesvol bijgewerkt.',
                'deleted' => 'Catalogusregel succesvol verwijderd.',
                'delete-failed' => 'De catalogusregel kon niet worden verwijderd.',
                'mass-delete-indices-required' => 'Er zijn geen ID\'s van catalogusregels opgegeven voor massaal verwijderen.',
                'mass-delete-success' => 'Geselecteerde catalogusregels succesvol verwijderd.',
                'no-permission' => 'U hebt geen toestemming om catalogusregels te beheren.',
            ],

            'campaign' => [
                'not-found' => 'Campagne niet gevonden.',
                'created' => 'Campagne succesvol aangemaakt.',
                'updated' => 'Campagne succesvol bijgewerkt.',
                'deleted' => 'Campagne succesvol verwijderd.',
                'delete-failed' => 'De campagne kon niet worden verwijderd.',
                'no-permission' => 'U hebt geen toestemming om campagnes te beheren.',

                'send' => [
                    'id-required' => 'Campagne-id is vereist.',
                    'inactive' => 'Een inactieve campagne kan niet worden verzonden. Schakel deze eerst in.',
                    'queued' => 'Campagne in de wachtrij geplaatst voor :count ontvanger(s).',
                ],
            ],

            'sitemap' => [
                'not-found' => 'Sitemap niet gevonden.',
                'created' => 'Sitemap succesvol aangemaakt.',
                'updated' => 'Sitemap succesvol bijgewerkt.',
                'deleted' => 'Sitemap succesvol verwijderd.',
                'delete-failed' => 'De sitemap kon niet worden verwijderd.',
                'no-permission' => 'U hebt geen toestemming om sitemaps te beheren.',

                'generate' => [
                    'id-required' => 'Sitemap-id is vereist.',
                    'no-channels' => 'Deze sitemap omvat geen enkel kanaal, dus er valt niets te genereren. Wijs er eerst ten minste één kanaal aan toe.',
                    'success' => 'Sitemap succesvol opnieuw gegenereerd.',
                    'failed' => 'Genereren van sitemap mislukt: :message',
                ],
            ],

            'cart-rule' => [
                'not-found' => 'Winkelwagenregel niet gevonden.',
                'id-required' => 'Een winkelwagenregel-id is vereist.',
                'copy-of' => 'Kopie van :value',
                'copied' => 'Winkelwagenregel succesvol gekopieerd.',
                'created' => 'Winkelwagenregel succesvol aangemaakt.',
                'updated' => 'Winkelwagenregel succesvol bijgewerkt.',
                'deleted' => 'Winkelwagenregel succesvol verwijderd.',
                'delete-failed' => 'De winkelwagenregel kon niet worden verwijderd.',
                'no-permission' => 'U hebt geen toestemming om winkelwagenregels te beheren.',
                'mass-delete-success' => 'Winkelwagenregels succesvol verwijderd.',
                'mass-delete-indices-required' => 'Het veld indices is vereist en moet een niet-lege array zijn.',
                'action-type-invalid' => 'De action_type moet een van de volgende zijn: by_percent, by_fixed, cart_fixed, buy_x_get_y.',
                'coupon-code-required' => 'Een coupon_code is vereist wanneer coupon_type=1 en use_auto_generation=0.',
                'coupon-code-unique' => 'Deze couponcode is al in gebruik.',
            ],

            'template' => [
                'campaign-associated' => 'Deze e-mailsjabloon wordt door een campagne gebruikt en kan daarom niet worden verwijderd. Verwijder deze eerst uit de campagne.',
                'not-found' => 'E-mailsjabloon niet gevonden.',
                'created' => 'E-mailsjabloon succesvol aangemaakt.',
                'updated' => 'E-mailsjabloon succesvol bijgewerkt.',
                'deleted' => 'E-mailsjabloon succesvol verwijderd.',
                'delete-failed' => 'De e-mailsjabloon kon niet worden verwijderd.',
                'status-invalid' => 'De status moet een van de volgende zijn: active, inactive, draft.',
                'no-permission' => 'U hebt geen toestemming om e-mailsjablonen te beheren.',
            ],

            'event' => [
                'campaign-associated' => 'Dit evenement wordt door een campagne gebruikt en kan daarom niet worden verwijderd. Verwijder het eerst uit de campagne.',
                'not-found' => 'Marketingevenement niet gevonden.',
                'created' => 'Marketingevenement succesvol aangemaakt.',
                'updated' => 'Marketingevenement succesvol bijgewerkt.',
                'deleted' => 'Marketingevenement succesvol verwijderd.',
                'delete-failed' => 'Het marketingevenement kon niet worden verwijderd.',
                'no-permission' => 'U hebt geen toestemming om marketingevenementen te beheren.',
            ],

            'search-synonym' => [
                'not-found' => 'Zoeksynoniem niet gevonden.',
                'created' => 'Zoeksynoniem succesvol aangemaakt.',
                'updated' => 'Zoeksynoniem succesvol bijgewerkt.',
                'deleted' => 'Zoeksynoniem succesvol verwijderd.',
                'mass-deleted' => 'Zoeksynoniemen succesvol verwijderd.',
                'delete-failed' => 'Het zoeksynoniem kon niet worden verwijderd.',
                'indices-required' => 'Het veld indices is vereist en moet een niet-lege array van zoeksynoniem-ID\'s zijn.',
                'no-permission' => 'U hebt geen toestemming om zoeksynoniemen te beheren.',
            ],

            'url-rewrite' => [
                'not-found' => 'URL-herschrijving niet gevonden.',
                'created' => 'URL-herschrijving succesvol aangemaakt.',
                'updated' => 'URL-herschrijving succesvol bijgewerkt.',
                'deleted' => 'URL-herschrijving succesvol verwijderd.',
                'delete-failed' => 'De URL-herschrijving kon niet worden verwijderd.',
                'mass-deleted' => 'URL-herschrijvingen succesvol verwijderd.',
                'indices-required' => 'Ten minste één URL-herschrijving-id is vereist.',
                'no-permission' => 'U hebt geen toestemming om URL-herschrijvingen te beheren.',
            ],

            'cart-rule-coupon' => [
                'cart-rule-not-found' => 'Winkelwagenregel niet gevonden.',
                'not-found' => 'Coupon niet gevonden.',
                'code-taken' => 'Er bestaat al een coupon met deze code.',
                'format-invalid' => 'Het formaat moet een van de volgende zijn: alphabetic, alphanumeric, numeric.',
                'indices-required' => 'Het veld indices is vereist en moet een niet-lege array van coupon-ID\'s zijn.',
                'created' => 'Coupon succesvol aangemaakt.',
                'generated' => ':count coupon(s) succesvol gegenereerd.',
                'deleted' => 'Coupon succesvol verwijderd.',
                'mass-deleted' => ':count coupon(s) succesvol verwijderd.',
                'no-permission' => 'U hebt geen toestemming om coupons van winkelwagenregels te beheren.',
            ],

            'subscriber' => [
                'not-found' => 'Nieuwsbriefabonnee niet gevonden.',
                'updated' => 'Abonnement succesvol bijgewerkt.',
                'deleted' => 'Abonnement succesvol verwijderd.',
                'is-subscribed-required' => 'Het veld is_subscribed is vereist.',
                'no-permission' => 'U hebt geen toestemming om nieuwsbriefabonnees te beheren.',
            ],

            'search-term' => [
                'not-found' => 'Zoekterm niet gevonden.',
                'updated' => 'Zoekterm succesvol bijgewerkt.',
                'deleted' => 'Zoekterm succesvol verwijderd.',
                'mass-deleted' => 'Zoektermen succesvol verwijderd.',
                'term-required' => 'Het veld term is vereist.',
                'redirect-url-invalid' => 'De redirect_url moet een geldige URL zijn.',
                'indices-required' => 'Er moet ten minste één ID worden opgegeven in indices.',
                'no-permission' => 'U hebt geen toestemming om zoektermen te beheren.',
            ],
        ],
        'auth' => [
            'configuration' => [
                'title' => 'Beheerdersauthenticatie',
                'info' => 'Tokenlooptijden, verzoeklimieten en beveiligingsregels voor admin-API-aanmeldingen en API-tokens.',

                'tokens' => [
                    'title' => 'Tokenlooptijden',
                    'info' => 'Hoe lang toegangstokens, vernieuwingstokens en sessies geldig blijven. Wijzigingen gelden voor tokens die daarna worden uitgegeven.',
                    'access-token-ttl' => 'Looptijd toegangstoken (minuten)',
                    'access-token-ttl-info' => 'Tussen 5 en 60 minuten. Standaard 15.',
                    'refresh-token-ttl' => 'Looptijd vernieuwingstoken (dagen)',
                    'refresh-token-ttl-info' => 'Tussen 1 en 90 dagen. Standaard 30.',
                    'session-lifetime' => 'Maximale sessieduur (dagen)',
                    'session-lifetime-info' => 'Tussen 1 en 365 dagen. Standaard 90. Daarna moet de beheerder opnieuw inloggen, hoe vaak de sessie ook is vernieuwd.',
                ],

                'rate-limits' => [
                    'title' => 'Verzoeklimieten',
                    'info' => 'Maximaal aantal admin-API-verzoeken per minuut.',
                    'session-requests-per-minute' => 'Verzoeken per minuut per ingelogde beheerder',
                    'api-token-requests-per-minute' => 'Verzoeken per minuut per API-token',
                ],

                'security' => [
                    'title' => 'Beveiliging',
                    'info' => 'Intrekkingsregels, verloop van API-tokens en domeinbinding.',
                    'revoke-sessions-on-password-change' => 'Sessies intrekken bij wachtwoordwijziging',
                    'revoke-api-tokens-on-password-change' => 'API-tokens intrekken bij wachtwoordwijziging',
                    'allow-non-expiring-api-tokens' => 'API-tokens zonder vervaldatum toestaan',
                    'domain-binding-mode' => 'Domeinbindingsmodus',
                    'domain-binding-mode-info' => 'DNS controleert of het verzoek komt van een IP-adres waarnaar het domein van het token verwijst. Origin controleert de Origin- of Referer-header en is alleen geschikt voor browsers.',
                    'domain-binding-dns' => 'DNS (IP-adres van de client)',
                    'domain-binding-origin' => 'Origin-header',
                ],
            ],

            'errors' => [
                'unauthenticated' => 'U bent niet geverifieerd.',
                'access-token-expired' => 'Het toegangstoken is verlopen. Vernieuw het om door te gaan.',
                'api-token-expired' => 'Het API-token is verlopen.',
                'refresh-token-invalid' => 'Het vernieuwingstoken is ongeldig of verlopen. Log opnieuw in.',
                'api-token-revoked' => 'Het API-token is ingetrokken.',
                'api-token-domain-mismatch' => 'Het API-token kan niet vanaf dit domein worden gebruikt.',
                'access-token-required' => 'Deze actie vereist het toegangstoken van een inlogsessie; API-tokens kunnen deze niet gebruiken.',
                'two-factor-required' => 'Voer de 6-cijferige code uit je authenticator-app of een van je back-upcodes in om het inloggen af te ronden.',
                'too-many-login-attempts' => 'Te veel inlogpogingen. Probeer het over :seconds seconden opnieuw.',
                'two-factor-challenge-invalid' => 'De tweestapsverificatie is ongeldig of verlopen. Log opnieuw in.',
                'two-factor-attempts-exceeded' => 'Te veel onjuiste codes. Log opnieuw in.',
                'password-reset-token-invalid' => 'Dit token voor het herstellen van het wachtwoord is ongeldig of verlopen.',
                'too-many-password-requests' => 'Te veel wachtwoordverzoeken. Probeer het over :seconds seconden opnieuw.',
                'rate-limit-exceeded' => 'Te veel pogingen.',
                'no-permission' => 'U hebt geen toestemming om admin-API-sessies te beheren.',
            ],

            'sessions' => [
                'logged-out' => 'U bent uitgelogd.',
                'logged-out-all' => 'U bent uitgelogd van alle sessies.',
                'not-found' => 'Sessie niet gevonden.',
                'revoked' => 'De sessie is ingetrokken.',
                'revoked-for-admin' => 'Ingetrokken sessies: :count.',
            ],

            'password' => [
                'reset' => 'Je wachtwoord is hersteld. Log opnieuw in.',
            ],

            'acl' => [
                'api-sessions' => 'API-sessies',
                'revoke' => 'Sessies intrekken',
            ],
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
                'view' => 'Bekijken',
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
            'view' => 'Bekijken',
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

        'permission-type' => [
            'all' => 'Allemaal',
            'custom' => 'Aangepast',
            'same-as-web' => 'Hetzelfde als Webtoestemming',
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
            'only-draft-generate' => 'Alleen concepttokens kunnen worden gegenereerd.',
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
            'confirm-title' => 'Dit token intrekken?',
            'confirm-message' => 'Het token ":name" werkt onmiddellijk niet meer voor alle clients die het gebruiken.',
            'confirm-button' => 'Token intrekken',
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
