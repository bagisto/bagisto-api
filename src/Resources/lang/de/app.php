<?php

return [

    'graphql' => [
        'headers' => [
            'unknown-channel' => 'Der Kanal ":code" im X-Channel-Header existiert nicht.',
            'storefront-key-required' => 'Der X-STOREFRONT-KEY-Header ist erforderlich',
            'storefront-key-invalid' => 'Ungültiger Storefront-Schlüssel',
            'storefront-key-rate-limited' => 'Ratenlimit überschritten. Bitte versuchen Sie es in :seconds Sekunden erneut',
        ],

        'cart' => [
            'authentication-required' => 'Authentifizierungstoken ist erforderlich',
            'invalid-token' => 'Ungültiges oder abgelaufenes Authentifizierungstoken',
            'unauthorized-access' => 'Nicht autorisierter Zugriff auf den Warenkorb',
            'authenticated-only' => 'Nur authentifizierte Benutzer können ihre Warenkörbe abrufen',
            'merge-requires-auth' => 'Das Zusammenführen von Gastwarenkörben erfordert eine Authentifizierung',
            'unknown-operation' => 'Unbekannte Warenkorb-Operation',

            'cart-not-found' => 'Warenkorb nicht gefunden',
            'guest-cart-not-found' => 'Gastwarenkorb nicht gefunden',
            'product-not-found' => 'Produkt nicht gefunden',

            'product-id-quantity-required' => 'Produkt-ID und Menge sind erforderlich',
            'cart-item-id-quantity-required' => 'Warenkorbartikel-ID und Menge sind erforderlich',

            'file-token-invalid' => 'Die Referenz auf die hochgeladene Datei ist ungültig oder abgelaufen. Laden Sie die Datei erneut hoch.',
            'file-token-forbidden' => 'Diese hochgeladene Datei gehört nicht zu Ihrem Warenkorb.',
            'customizable-option-required' => 'Die anpassbare Option ":label" ist erforderlich.',
            'customizable-file-option-invalid' => 'Die ausgewählte Option ist keine Dateioption für dieses Produkt.',
            'customizable-file-invalid' => 'Die Datei fehlt, hat eine nicht unterstützte Erweiterung oder ist zu groß.',
            'cart-item-id-required' => 'Warenkorbartikel-ID ist erforderlich',
            'item-ids-required' => 'Ein Array von Artikel-IDs ist erforderlich',

            'event-booking-quantity-not-changeable' => 'Die Menge eines Event-Buchungsprodukts kann nicht geändert werden. Die Menge wird durch die Ticketauswahl bestimmt.',
            'appointment-booking-quantity-not-changeable' => 'Die Menge eines Terminbuchungsprodukts kann nicht geändert werden.',
            'coupon-code-required' => 'Gutscheincode ist erforderlich',
            'address-data-required' => 'Land, Bundesland und Postleitzahl sind erforderlich',
            'grouped-qty-required' => 'Ein gruppiertes Produkt erfordert ausgewählte Mengen. Übergeben Sie groupedQty als JSON-String, z. B. {"101":2,"102":1}.',
            'grouped-qty-must-include-all' => 'Ein gruppiertes Produkt erfordert Mengen für alle zugehörigen Produkte. Fehlende IDs: :ids.',
            'grouped-qty-invalid-associated' => 'Die Mengen des gruppierten Produkts enthalten ungültige IDs zugehöriger Produkte: :ids.',
            'grouped-qty-invalid-quantity' => 'Ungültige Menge für die zugehörige Produkt-ID :id. Die Menge muss eine nicht negative ganze Zahl sein.',
            'bundle-qty-not-changeable' => 'Die Menge für die Bundle-Option ":option" kann nicht geändert werden. Die zulässige Menge ist :qty.',

            'add-product-failed' => 'Produkt konnte nicht zum Warenkorb hinzugefügt werden',
            'update-item-failed' => 'Warenkorbartikel konnte nicht aktualisiert werden',
            'remove-item-failed' => 'Warenkorbartikel konnte nicht entfernt werden',
            'remove-coupon-failed' => 'Gutschein konnte nicht entfernt werden',
            'coupon-applied' => 'Gutschein erfolgreich angewendet',
            'coupon-removed' => 'Gutschein erfolgreich entfernt',
            'move-to-wishlist-failed' => 'Artikel konnte nicht auf die Wunschliste verschoben werden',
            'estimate-shipping-failed' => 'Versandkosten konnten nicht geschätzt werden',

            'product-added-successfully' => 'Produkt erfolgreich zum Warenkorb hinzugefügt',
            'cart-item-updated-successfully' => 'Warenkorbartikel erfolgreich aktualisiert',
            'guest-cart-merged' => 'Gastwarenkorb erfolgreich zusammengeführt',
            'using-authenticated-cart' => 'Authentifizierter Kundenwarenkorb wird verwendet',
            'cart-item-not-found' => 'Warenkorbartikel nicht gefunden',
            'new-guest-cart-created' => 'Neuer Gastwarenkorb mit eindeutigem Sitzungstoken erstellt',
            'select-items-to-remove' => 'Bitte wählen Sie die zu entfernenden Artikel aus',
            'select-items-to-move-wishlist' => 'Bitte wählen Sie die Artikel aus, die auf die Wunschliste verschoben werden sollen',
            'invalid-or-expired-token' => 'Das Warenkorb-Token ist ungültig oder abgelaufen. Bitte erstellen Sie einen neuen Warenkorb.',
            'invalid-token-of-login-user' => 'Das Token des angemeldeten Benutzers ist ungültig.',
            'booking-note-required' => 'booking.note ist für eine Tischreservierung erforderlich.',
            'booking-date-required' => 'booking.date ist erforderlich, wenn booking.slot ein formatierter Zeitraum ist.',
            'booking-qty-required' => 'booking.qty ist für eine Event-Buchung erforderlich.',
            'booking-ticket-qty-invalid' => 'Die Event-Ticketmenge muss eine nicht negative Zahl sein.',
            'booking-ticket-required' => 'Wählen Sie für die Event-Buchung mindestens eine Ticketmenge aus.',
            'booking-slot-unavailable' => 'Der ausgewählte Zeitslot ist für das angegebene Datum nicht verfügbar.',
            'invalid-quantity' => 'Die Menge muss mindestens 1 betragen',
            'product-id-required' => 'Produkt-ID ist erforderlich',
            'shipping-address-required' => 'Land und Postleitzahl sind für die Versandschätzung erforderlich',
        ],

        'token-verification' => [
            'invalid-operation' => 'Ungültige Operation',
            'invalid-input-data' => 'Ungültige Eingabedaten',
            'token-required' => 'Token ist erforderlich',
            'invalid-token-format' => 'Ungültiges Token-Format',
            'token-not-found-or-expired' => 'Token nicht gefunden oder abgelaufen',
            'customer-not-found' => 'Kunde nicht gefunden',
            'customer-account-suspended' => 'Das Kundenkonto ist gesperrt',
            'error-verifying-token' => 'Fehler bei der Überprüfung des Tokens',
            'token-is-valid' => 'Token ist gültig',
            'unauthenticated' => 'Nicht authentifiziert. Bitte melden Sie sich an, um diese Aktion auszuführen',
        ],

        'forgot-password' => [
            'invalid-operation' => 'Ungültige Operation',
            'invalid-input-data' => 'Ungültige Eingabedaten',
            'email-required' => 'E-Mail ist erforderlich',
            'reset-link-sent' => 'Der Link zum Zurücksetzen wurde erfolgreich an Ihre E-Mail-Adresse gesendet',
            'email-not-found' => 'E-Mail-Adresse nicht gefunden',
            'throttled' => 'Zu viele Versuche zum Zurücksetzen. Bitte warten Sie, bevor Sie es erneut versuchen.',
            'error-sending-reset-link' => 'Beim Senden des Links zum Zurücksetzen ist ein Fehler aufgetreten',
        ],

        'reset-password' => [
            'failed' => 'Das Passwort konnte nicht zurückgesetzt werden. Bitte versuchen Sie es erneut.',
        ],

        'rate-limit' => [
            'too-many-attempts' => 'Zu viele Versuche. Bitte versuchen Sie es in :seconds Sekunden erneut.',
        ],

        'logout' => [
            'invalid-operation' => 'Ungültige Operation',
            'invalid-input-data' => 'Ungültige Eingabedaten',
            'token-required' => 'Token ist erforderlich',
            'invalid-token-format' => 'Ungültiges Token-Format',
            'logged-out-successfully' => 'Erfolgreich abgemeldet',
            'token-not-found-or-expired' => 'Token nicht gefunden oder bereits abgelaufen',
            'error-during-logout' => 'Fehler beim Abmelden',
            'unauthenticated' => 'Nicht authentifiziert. Bitte melden Sie sich an, um diese Aktion auszuführen',
        ],

        'address' => [
            'deleted-successfully' => 'Adresse erfolgreich gelöscht',
            'authentication-required' => 'Authentifizierungstoken ist erforderlich',
            'invalid-token' => 'Ungültiges oder abgelaufenes Token',
            'unknown-operation' => 'Unbekannte Operation',
            'address-id-required' => 'Adress-ID ist erforderlich',
            'address-not-found' => 'Adresse nicht gefunden oder gehört nicht zu diesem Kunden',
            'retrieved' => 'Adressen erfolgreich abgerufen',
            'fetch-failed' => 'Adressen konnten nicht abgerufen werden:',
            'field-required' => 'Das Feld :field ist erforderlich',
        ],

        'customer-profile' => [
            'authentication-required' => 'Authentifizierungstoken ist erforderlich. Bitte geben Sie das Token in der Abfrageeingabe an',
            'invalid-token' => 'Ungültiges oder abgelaufenes Token',
            'profile-updated' => 'Kundenprofil erfolgreich aktualisiert',
        ],

        'customer' => [
            'password-mismatch' => 'Passwort und Passwortbestätigung stimmen nicht überein',
            'confirm-password-required' => 'Bei einer Passwortänderung ist die Passwortbestätigung erforderlich',
            'current-password-required' => 'Zum Ändern Ihres Passworts ist das aktuelle Passwort erforderlich',
            'current-password-incorrect' => 'Das eingegebene aktuelle Passwort ist falsch',
            'verify-email-to-login' => 'Ihr Konto wurde erstellt. Bitte verifizieren Sie Ihre E-Mail-Adresse, um sich anzumelden.',
            'delete-password-required' => 'Zum Löschen Ihres Kontos ist Ihr Passwort erforderlich',
            'delete-password-incorrect' => 'Das eingegebene Passwort ist falsch',
            'delete-has-active-orders' => 'Ihr Konto kann nicht gelöscht werden, solange Sie ausstehende oder in Bearbeitung befindliche Bestellungen haben',
            'unauthenticated' => 'Nicht authentifiziert. Bitte melden Sie sich an, um diese Aktion auszuführen',
            'id-required' => 'Kunden-ID ist erforderlich',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet wird ein IRI-Format wie "/api/admin/customers/1" oder eine numerische ID',
            'not-found' => 'Kunde nicht gefunden',
            'phone-special-chars-not-allowed' => 'Die Mobilnummer darf nur Ziffern enthalten. Sonderzeichen sind nicht erlaubt',
            'email-already-taken' => 'Diese E-Mail-Adresse ist bereits für ein anderes Konto registriert',
            'invalid-gender' => 'Ungültiger Geschlechtswert ":gender". Zulässige Werte sind: :valid',
        ],

        'product-review' => [
            'id-required' => 'Produktbewertungs-ID ist erforderlich',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet wird ein IRI-Format wie "/api/shop/reviews/1" oder eine numerische ID',
            'not-found' => 'Produktbewertung mit der ID ":id" nicht gefunden',
            'item-not-found' => 'Element ":id" nicht gefunden.',
            'product-id-required' => 'Produkt-ID ist erforderlich',
            'product-not-found' => 'Produkt nicht gefunden',
            'rating-invalid' => 'Die Bewertung muss zwischen 1 und 5 liegen',
            'title-required' => 'Der Titel der Bewertung ist erforderlich',
            'comment-required' => 'Der Kommentar zur Bewertung ist erforderlich',
            'review-disabled' => 'Produktbewertungen sind derzeit deaktiviert',
            'guest-review-disabled' => 'Gastbewertungen sind nicht erlaubt. Bitte melden Sie sich an, um eine Bewertung abzugeben',
            'owner-required' => 'Bitte melden Sie sich an, um Ihre Bewertung zu verwalten',
            'not-owned' => 'Diese Bewertung wurde nicht von Ihnen verfasst und kann nicht geändert oder gelöscht werden',
            'invalid-attachments' => 'Ungültiges Format der Anhänge.',
            'invalid-product-id-format' => 'Ungültiges Produkt-ID-Format. Erwartet wird ein IRI-Format wie "/api/shop/products/1" oder eine numerische ID',
        ],

        'customer-review' => [
            'id-required' => 'Kundenbewertungs-ID ist erforderlich',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet wird ein IRI-Format wie "/api/shop/customer-reviews/1" oder eine numerische ID',
            'not-found' => 'Kundenbewertung mit der ID ":id" nicht gefunden',
        ],

        'newsletter' => [
            'error-during-subscription' => 'Das Abonnement kann derzeit nicht abgeschlossen werden. Bitte versuchen Sie es später erneut',
        ],

        'contact-us' => [
            'success' => 'Ihre Anfrage wurde erfolgreich übermittelt. Wir melden uns in Kürze bei Ihnen',
            'failed' => 'Ihre Anfrage kann derzeit nicht gesendet werden. Bitte versuchen Sie es später erneut',
            'invalid-input' => 'Ungültige Daten im Kontaktformular',
        ],

        'customer-order' => [
            'id-required' => 'Kundenbestellungs-ID ist erforderlich',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet wird ein IRI-Format wie "/api/shop/customer-orders/1" oder eine numerische ID',
            'not-found' => 'Kundenbestellung mit der ID ":id" nicht gefunden',
        ],

        'return' => [
            'not-found' => 'Rücksendeanfrage nicht gefunden.',
            'disabled' => 'Rücksendungen sind für diesen Shop nicht aktiviert.',
            'invalid-input' => 'Fehlende oder ungültige Rücksendedaten.',
            'invalid-resolution-type' => 'Die Lösungsart muss "return" oder "cancel_items" sein.',
            'agreement-required' => 'Sie müssen die Rückgaberichtlinie akzeptieren.',
            'invalid-order' => 'Die ausgewählte Bestellung ist nicht gültig.',
            'invalid-item' => 'Der ausgewählte Artikel ist nicht für eine Rücksendung berechtigt.',
            'qty-exceeds' => 'Die angeforderte Menge übersteigt die rücksendbare Menge.',
            'invalid-package-condition' => 'Der Verpackungszustand muss "open" oder "packed" sein.',
            'invalid-image' => 'Eine oder mehrere der hochgeladenen Dateien haben einen nicht unterstützten Dateityp.',
            'invalid-custom-field' => 'Unbekanntes benutzerdefiniertes Feld ":id".',
            'custom-field-required' => 'Das Feld ":field" ist erforderlich.',
            'invalid-custom-field-value' => 'Der Wert für ":field" gehört nicht zu den zulässigen Optionen.',
            'created' => 'Rücksendeanfrage erfolgreich erstellt.',
            'canceled' => 'Rücksendeanfrage erfolgreich storniert.',
            'already-canceled' => 'Die Rücksendeanfrage ist bereits storniert.',
            'cancel-not-allowed' => 'Diese Rücksendeanfrage kann nicht storniert werden.',
            'close-not-allowed' => 'Diese Rücksendeanfrage kann nicht geschlossen werden.',
            'reopened' => 'Rücksendeanfrage erfolgreich wieder geöffnet.',
            'reopen-not-allowed' => 'Diese Rücksendeanfrage kann nicht wieder geöffnet werden.',
            'closed' => 'Rücksendeanfrage erfolgreich geschlossen.',
            'message-sent' => 'Nachricht erfolgreich gesendet.',
            'attachment-not-found' => 'Nachrichtenanhang nicht gefunden.',
        ],

        'eu-withdrawal' => [
            'not-found' => 'Widerrufserklärung nicht gefunden.',
            'order-not-found' => 'Die Bestellung wurde nicht gefunden oder ist nicht für einen Widerruf berechtigt.',
            'too-many-attempts' => 'Zu viele Versuche. Bitte versuchen Sie es in :seconds Sekunden erneut.',
            'created' => 'Widerrufserklärung erfolgreich eingereicht.',
        ],

        'customer-order-shipment' => [
            'id-required' => 'Sendungs-ID ist erforderlich',
            'not-found' => 'Sendung mit der ID ":id" nicht gefunden oder gehört nicht zu diesem Kunden',
            'item-not-found' => 'Sendungsartikel mit der ID ":id" nicht gefunden oder gehört nicht zu diesem Kunden',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet wird ein IRI-Format wie "/api/shop/customer-order-shipments/1" oder eine numerische ID',
        ],

        'cancel-order' => [
            'order-id-required' => 'Zum Stornieren einer Bestellung ist die Bestell-ID erforderlich',
            'not-found' => 'Bestellung mit der ID ":id" nicht gefunden oder gehört nicht zu diesem Kunden',
            'cancel-success' => 'Die Bestellung wurde erfolgreich storniert',
            'cancel-failed' => 'Die Bestellung kann nicht storniert werden. Sie wurde möglicherweise bereits bearbeitet, versandt oder storniert',
        ],

        'reorder' => [
            'order-id-required' => 'Für eine erneute Bestellung ist die Bestell-ID erforderlich',
            'not-found' => 'Bestellung mit der ID ":id" nicht gefunden oder gehört nicht zu diesem Kunden',
            'reorder-success' => ':count Artikel aus Ihrer vorherigen Bestellung wurden Ihrem Warenkorb hinzugefügt',
            'no-items-added' => 'Es konnten keine Artikel zum Warenkorb hinzugefügt werden. Die Produkte sind möglicherweise nicht vorrätig oder nicht verfügbar',
        ],

        'customer-downloadable-product' => [
            'id-required' => 'ID des herunterladbaren Kundenprodukts ist erforderlich',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet wird ein IRI-Format wie "/api/shop/customer-downloadable-products/1" oder eine numerische ID',
            'not-found' => 'Herunterladbares Kundenprodukt mit der ID ":id" nicht gefunden',

            'download' => [
                'unauthenticated' => 'Nicht autorisiert: Kundenauthentifizierung erforderlich.',
                'not-found' => 'Downloadbares Produkt nicht gefunden.',
                'pending' => 'Der Download steht aus. Bitte warten Sie, bis die Bestellung in Rechnung gestellt wurde.',
                'limit-exceeded' => 'Download-Limit überschritten.',
                'no-downloads-remaining' => 'Keine weiteren Downloads verfügbar.',
                'file-not-found' => 'Datei nicht gefunden.',
                'link-not-found' => 'Download-Link nicht gefunden.',
            ],
        ],

        'customer-invoice' => [
            'id-required' => 'Kundenrechnungs-ID ist erforderlich',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet wird ein IRI-Format wie "/api/shop/customer-invoices/1" oder eine numerische ID',
            'not-found' => 'Kundenrechnung mit der ID ":id" nicht gefunden',
        ],

        'auth' => [
            'no-token-provided' => 'Kein Authentifizierungstoken angegeben. Bitte geben Sie das Token im Authorization-Header als "Bearer <token>" oder im Feld input.token an',
            'invalid-or-expired-token' => 'Ungültiges oder abgelaufenes Token',
            'request-not-found' => 'Anfrage im Kontext nicht gefunden',
            'token-required' => 'Authentication token is required. Please provide the token either in the GraphQL mutation input field or in the Authorization header as "Bearer <token>"',
            'unknown-resource' => 'Unbekannte Ressource',
            'cannot-update-other-profile' => 'Nicht autorisiert: Das Profil eines anderen Kunden kann nicht aktualisiert werden',
            'refresh-token-invalid' => 'Das Aktualisierungstoken ist ungültig oder abgelaufen. Bitte melden Sie sich erneut an.',
            'too-many-login-attempts' => 'Zu viele Anmeldeversuche. Bitte versuchen Sie es in :seconds Sekunden erneut.',
            'too-many-refresh-requests' => 'Zu viele Token-Aktualisierungen. Bitte versuchen Sie es in :seconds Sekunden erneut.',
            'rate-limit-exceeded' => 'Zu viele Versuche.',
            'access-token-expired' => 'Das Zugriffstoken ist abgelaufen. Aktualisieren Sie es, um fortzufahren.',
            'logged-out-all' => 'Sie wurden von allen Sitzungen abgemeldet.',
            'session-not-found' => 'Sitzung nicht gefunden.',
            'session-revoked' => 'Die Sitzung wurde widerrufen.',
        ],

        'upload' => [
            'invalid-base64' => 'Ungültige base64-kodierte Bilddaten',
            'size-exceeds-limit' => 'Die Bildgröße darf 5 MB nicht überschreiten',
            'invalid-format' => 'Ungültiges Bildformat. Bitte geben Sie ein base64-kodiertes Bild mit Daten-URI-Schema an (data:image/jpeg;base64,...)',
            'failed' => 'Bild-Upload fehlgeschlagen',
        ],

        'attribute' => [
            'code-already-exists' => 'Der Attributcode existiert bereits',
            'option-id-required' => 'Der Parameter "attributeId" ist erforderlich, wenn Attributoptionen direkt abgefragt werden',
            'id-required' => 'Attribut-ID ist erforderlich',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet wird ein IRI-Format wie "/api/shop/attributes/1" oder eine numerische ID',
            'not-found' => 'Attribut nicht gefunden',
        ],

        'attribute-option' => [
            'id-required' => 'Attributoptions-ID ist erforderlich',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet wird ein IRI-Format wie "/api/shop/attribute-options/1" oder eine numerische ID',
            'not-found' => 'Attributoption nicht gefunden',
        ],

        'channel' => [
            'id-required' => 'Kanal-ID ist erforderlich',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet wird ein IRI-Format wie "/api/shop/channels/1" oder eine numerische ID',
            'not-found' => 'Kanal nicht gefunden',
        ],

        'country-state' => [
            'id-required' => 'Bundesland-ID ist erforderlich',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet wird ein IRI-Format wie "/api/shop/country-states/1" oder eine numerische ID',
            'not-found' => 'Bundesland nicht gefunden',
            'country-id-required' => 'Länder-ID ist erforderlich',
        ],

        'country' => [
            'id-required' => 'Länder-ID ist erforderlich',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet wird ein IRI-Format wie "/api/shop/countries/1" oder eine numerische ID',
            'not-found' => 'Land nicht gefunden',
        ],

        'login' => [
            'invalid-credentials' => 'Ungültige E-Mail-Adresse oder Passwort',
            'account-suspended' => 'Ihr Konto wurde gesperrt',
            'account-inactive' => 'Ihre Aktivierung erfordert die Zustimmung des Administrators',
            'email-not-verified' => 'Bitte verifizieren Sie zuerst Ihr E-Mail-Konto.',
            'successful' => 'Sie haben sich erfolgreich angemeldet',
            'invalid-request' => 'Ungültige Anmeldeanfrage',
        ],

        'social-login' => [
            'signed-in' => 'Erfolgreich angemeldet.',
            'token-required' => 'Ein Social-Login-Token ist erforderlich.',
            'invalid-token' => 'Das Social-Login-Token ist ungültig oder abgelaufen. Bitte versuchen Sie es erneut.',
            'google-not-configured' => 'Die Google-Anmeldung ist für diesen Shop nicht konfiguriert.',
            'wrong-audience' => 'Dieses Token wurde für eine andere App ausgestellt.',
            'email-required' => 'Der Anbieter hat keine E-Mail-Adresse übermittelt. Bitte registrieren Sie sich stattdessen mit E-Mail.',
            'account-inactive' => 'Ihre Aktivierung erfordert die Zustimmung des Administrators',
            'provider-not-supported' => 'Dieser Social-Login-Anbieter wird nicht unterstützt.',
            'provider-disabled' => 'Dieser Social-Login-Anbieter ist nicht aktiviert.',
        ],

        'checkout' => [
            'invalid-input' => 'Ungültige Eingabedaten für den Checkout-Vorgang',
            'billing-address-required' => 'Die Rechnungsadresse ist erforderlich',
            'shipping-address-required' => 'Für Sendungen ist eine Lieferadresse erforderlich',
            'address-save-failed' => 'Adresse konnte nicht gespeichert werden',
            'address-saved' => 'Adresse erfolgreich gespeichert',
            'shipping-method-required' => 'Die Versandart ist erforderlich',
            'invalid-shipping-method' => 'Ungültige oder nicht verfügbare Versandart',
            'shipping-method-save-failed' => 'Versandart konnte nicht gespeichert werden',
            'shipping-method-saved' => 'Versandart erfolgreich gespeichert',
            'shipping-method-error' => 'Fehler beim Speichern der Versandart',
            'payment-method-required' => 'Die Zahlungsart ist erforderlich',
            'invalid-payment-method' => 'Ungültige oder nicht verfügbare Zahlungsart',
            'payment-method-save-failed' => 'Zahlungsart konnte nicht gespeichert werden',
            'payment-method-saved' => 'Zahlungsart erfolgreich gespeichert',
            'payment-method-error' => 'Fehler beim Speichern der Zahlungsart',
            'order-placed' => 'Bestellung erfolgreich aufgegeben',
            'order-redirect-required' => 'Bei dieser Zahlungsart muss der Käufer die Zahlung beim Zahlungsanbieter abschließen. Leiten Sie ihn zu redirectUrl weiter; die Bestellung wird erstellt, sobald der Zahlungsanbieter die Zahlung bestätigt.',
            'order-creation-failed' => 'Bestellungserstellung fehlgeschlagen: Die Bestell-ID ist null oder die Bestellung wurde nicht gespeichert',
            'order-retrieval-failed' => 'Die erstellte Bestellung konnte nicht abgerufen werden',
            'order-creation-error' => 'Bestellung konnte nicht erstellt werden',
            'cart-empty' => 'Der Warenkorb ist leer',
            'account-suspended' => 'Ihr Konto wurde gesperrt. Bitte wenden Sie sich an den Support.',
            'account-inactive' => 'Ihr Konto ist inaktiv. Bitte wenden Sie sich an den Support.',
            'minimum-order-not-met' => 'Der Mindestbestellwert beträgt :amount',
            'guest-checkout-not-allowed' => 'Bitte melden Sie sich an, um zur Kasse zu gehen. Der Gast-Checkout ist für diesen Warenkorb nicht verfügbar.',
            'email-required' => 'Für die Bestellungserstellung ist eine E-Mail-Adresse erforderlich',
            'unknown-operation' => 'Unbekannte Checkout-Operation',
        ],

        'customer-addresses' => [
            'token-required' => 'Zum Abrufen der Kundenadressen ist ein Token erforderlich',
            'invalid-or-expired-token' => 'Ungültiges oder abgelaufenes Token',
            'token-validation-failed' => 'Token-Validierung fehlgeschlagen',
        ],

        'product' => [
            'not-found' => 'Produkt nicht gefunden',
            'not-found-with-sku' => 'Kein Produkt mit dieser SKU gefunden',
            'not-found-with-url-key' => 'Kein Produkt mit diesem URL-Schlüssel gefunden',
            'parameters-required' => 'Mindestens einer der folgenden Parameter muss angegeben werden: "sku", "id", "urlKey"',
            'type' => 'Produkttyp',
            'attribute-family' => 'Attributfamilie',
            'sku' => 'SKU',
            'name' => 'Name',
            'description' => 'Beschreibung',
            'short-description' => 'Kurzbeschreibung',
            'status' => 'Status',
            'new' => 'Neu',
            'featured' => 'Hervorgehoben',
            'price' => 'Preis',
            'special-price' => 'Sonderpreis',
            'weight' => 'Gewicht',
            'cost' => 'Kosten',
            'length' => 'Länge',
            'width' => 'Breite',
            'height' => 'Höhe',
            'color' => 'Farbe',
            'size' => 'Größe',
            'brand' => 'Marke',
            'super-attributes' => 'Superattribute',
            'id-required' => 'Produkt-ID ist erforderlich',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet wird ein IRI-Format wie "/api/shop/products/1" oder eine numerische ID',
        ],

        'downloadable-product' => [
            'download-link-not-found' => 'Download-Link nicht gefunden oder abgelaufen',
            'purchased-link-not-found' => 'Gekaufter Link nicht gefunden',
            'file-not-found' => 'Datei nicht gefunden',
            'download-successful' => 'Datei bereit zum Download',
            'token-required' => 'Download-Token erforderlich',
            'invalid-token' => 'Download-Token ungültig oder abgelaufen',
            'token-expired' => 'Das Download-Token ist abgelaufen. Bitte generieren Sie ein neues',
            'access-denied' => 'Zugriff verweigert: Sie haben keine Berechtigung, diese Datei herunterzuladen',
            'redirect-external-url' => 'Weiterleitung zur externen Download-URL',
            'file-error' => 'Ein Fehler ist bei der Verarbeitung Ihrer Download-Anfrage aufgetreten',
            'unauthorized-access' => 'Nicht autorisierter Zugriff auf Download-Ressource',
            'unauthenticated' => 'Nicht autorisiert: Kundenauthentifizierung erforderlich',
            'link-id-required' => 'downloadableLinkPurchasedId ist erforderlich',
            'link-not-found' => 'Download-Link nicht gefunden',
            'not-owner' => 'Nicht autorisiert: Dieser Download-Link gehört nicht Ihnen',
            'pending' => 'Der Download steht aus. Bitte warten Sie, bis die Bestellung in Rechnung gestellt wurde.',
            'limit-exceeded' => 'Download-Limit überschritten',
            'no-downloads-left' => 'Keine weiteren Downloads verfügbar',
        ],

        'booking-slot' => [
            'id-required' => 'Die ID des Buchungsprodukts ist erforderlich.',
            'date-required' => 'Das Datum ist erforderlich.',
            'product-not-found' => 'Buchungsprodukt nicht gefunden.',
            'invalid-type' => 'Dieser Buchungstyp wird nicht unterstützt.',
            'invalid-time-range' => 'Ungültiger Zeitraum.',
            'invalid-time-format' => 'Ungültiges Zeitformat.',
        ],

        'resolver' => [
            'no-resolve-info' => 'GraphQL ResolveInfo ist im Kontext nicht verfügbar. Die Modellklasse kann nicht bestimmt werden.',
            'unknown-resource-class' => 'Die Ressourcenklasse konnte aus dem GraphQL-Rückgabetyp nicht bestimmt werden',
        ],

        'docs' => [
            'openapi-unavailable' => 'Die OpenAPI Factory konnte nicht instanziiert werden',
        ],

        'locale' => [
            'id-required' => 'Gebietsschema-ID ist erforderlich',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet wird ein IRI-Format wie "/api/shop/locales/1" oder eine numerische ID',
            'not-found' => 'Gebietsschema nicht gefunden',
        ],

        'category' => [
            'id-required' => 'Kategorie-ID ist erforderlich',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet wird ein IRI-Format wie "/api/shop/categories/1" oder eine numerische ID',
            'not-found' => 'Kategorie nicht gefunden',
        ],

        'compare-item' => [
            'id-required' => 'Vergleichselements-ID ist erforderlich',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet IRI-Format wie "/api/shop/compare-items/1" oder numerische ID',
            'not-found' => 'Vergleichselement nicht gefunden',
            'product-id-required' => 'Produkt-ID ist erforderlich',
            'customer-id-required' => 'Kunden-ID ist erforderlich',
            'product-not-found' => 'Produkt nicht gefunden',
            'customer-not-found' => 'Kunde nicht gefunden',
            'already-exists' => 'Dieses Produkt befindet sich bereits in Ihrer Vergleichsliste',
            'delete-all-success' => 'Alle Vergleichsartikel wurden erfolgreich entfernt',
        ],

        'wishlist' => [
            'id-required' => 'Wunschlistenartikel-ID ist erforderlich',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet wird ein IRI-Format wie "/api/shop/wishlists/1" oder eine numerische ID',
            'not-found' => 'Wunschlistenartikel nicht gefunden',
            'product-id-required' => 'Produkt-ID ist erforderlich',
            'customer-id-required' => 'Kunden-ID ist erforderlich',
            'product-not-found' => 'Produkt nicht gefunden',
            'product-disabled' => 'Dieses Produkt ist derzeit deaktiviert',
            'customer-not-found' => 'Kunde nicht gefunden',
            'already-exists' => 'Dieses Produkt befindet sich bereits auf Ihrer Wunschliste',
            'added' => 'Artikel erfolgreich zur Wunschliste hinzugefügt',
            'removed' => 'Artikel erfolgreich von der Wunschliste entfernt',
            'product-removed' => 'Das Produkt wurde entfernt',
            'wishlist-item-id-required' => 'Wunschlistenartikel-ID ist erforderlich',
            'invalid-quantity' => 'Die Menge muss größer als 0 sein',
            'move-to-cart-missing-options' => 'Beim Produkt fehlen erforderliche Optionen. Bitte konfigurieren Sie es manuell',
            'moved-to-cart-success' => 'Artikel erfolgreich in den Warenkorb verschoben',
            'delete-all-success' => 'Alle Wunschlistenartikel wurden erfolgreich entfernt',
        ],

        'gdpr' => [
            'disabled' => 'DSGVO-Datenanfragen sind deaktiviert. Bitte aktivieren Sie die DSGVO in der Admin-Konfiguration.',
            'unauthenticated' => 'Sie müssen angemeldet sein, um DSGVO-Datenanfragen zu verwalten.',
            'not-found' => 'DSGVO-Datenanfrage nicht gefunden',
            'type-required' => 'Der Anfragetyp ist erforderlich',
            'type-invalid' => 'Der Anfragetyp muss entweder "delete" oder "update" sein',
            'message-required' => 'Die Nachricht ist erforderlich',
            'raised' => 'Ihre DSGVO-Datenanfrage wurde erfolgreich gestellt.',
            'revoked' => 'Ihre DSGVO-Datenanfrage wurde erfolgreich widerrufen.',
            'revoke-not-allowed' => 'Diese DSGVO-Datenanfrage kann nicht mehr widerrufen werden.',
            'deleted' => 'Ihre DSGVO-Datenanfrage wurde erfolgreich gelöscht.',
        ],

        'section' => [
            'id-required' => 'Abschnitts-ID ist erforderlich',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet wird ein IRI-Format wie "/api/shop/sections/1" oder eine numerische ID',
            'not-found' => 'Abschnitt nicht gefunden',
        ],

        'install' => [
            'starting' => 'API-Platform-Installation wird gestartet...',
            'completed-success' => '✓ API-Platform-Installation erfolgreich abgeschlossen!',
            'failed' => '✗ Installation fehlgeschlagen: ',

            'provider-already-registered' => 'ℹ Service Provider bereits registriert',
            'provider-not-registered' => '⚠ Der Service Provider konnte nicht automatisch registriert werden. Fügen Sie :provider manuell zu bootstrap/providers.php hinzu.',
            'provider-registered' => '✓ Service Provider in bootstrap/providers.php registriert',
            'provider-file-not-found' => 'Datei nicht gefunden: :file',
            'provider-permission-denied' => 'Zugriff verweigert: In :file kann nicht geschrieben werden. Bitte überprüfen Sie die Dateiberechtigungen.',

            'config-already-exists' => 'ℹ Konfigurationsdatei existiert bereits',
            'config-published' => '✓ API-Platform-Konfiguration nach config/ veröffentlicht',
            'config-source-not-found' => 'Quell-Konfigurationsdatei nicht gefunden: :file',
            'config-permission-denied' => 'Zugriff verweigert: In :directory kann nicht geschrieben werden. Bitte überprüfen Sie die Verzeichnisberechtigungen.',

            'translatable-not-found' => 'ℹ TranslatableModel nicht gefunden - wird übersprungen',
            'translatable-already-abstract' => 'ℹ TranslatableModel ist bereits abstrakt',
            'translatable-made-abstract' => '✓ TranslatableModel wurde abstrakt gemacht',
            'translatable-permission-denied' => 'Zugriff verweigert: In :file kann nicht geschrieben werden. Bitte überprüfen Sie die Dateiberechtigungen.',

            'providers-already-registered' => 'ℹ API-Platform-Provider sind bereits registriert',
            'providers-registered' => '✓ API-Platform-Provider in bootstrap/app.php registriert',
            'providers-file-not-found' => 'Datei nicht gefunden: :file',
            'providers-permission-denied' => 'Zugriff verweigert: In :file kann nicht geschrieben werden. Bitte überprüfen Sie die Dateiberechtigungen.',
            'providers-not-found' => 'Die Erstellung der Application wurde in bootstrap/app.php nicht gefunden',

            'assets-published' => '✓ Paket-Assets veröffentlicht',
            'running-migrations' => 'Datenbankmigrationen werden ausgeführt...',
            'migrations-completed' => '✓ Datenbankmigrationen abgeschlossen',
            'clearing-caches' => 'Caches werden geleert und optimiert...',
            'caches-optimized' => '✓ Caches geleert und optimiert',
            'generating-api-key' => 'API-Schlüssel wird generiert...',
            'api-key-generated' => '✓ API-Schlüssel erfolgreich generiert',
            'api-key-already-exists' => 'ℹ API-Schlüssel für den Standard-Shop existiert bereits - Generierung wird übersprungen',

            'migrations-error' => 'Datenbankmigrationen fehlgeschlagen.',
            'migrations-error-running' => 'Fehler beim Ausführen der Datenbankmigrationen: :error',
            'cache-clear-error' => 'Leeren des Caches fehlgeschlagen.',
            'cache-optimize-error' => 'Optimierung fehlgeschlagen.',
            'cache-error' => 'Fehler beim Leeren und Optimieren der Caches: :error',
            'api-key-generation-error' => 'Generierung des API-Schlüssels fehlgeschlagen.',
            'api-key-error' => 'Fehler beim Generieren des API-Schlüssels: :error',

            'key-management-required' => '--key ist für die Aktion :action erforderlich',
            'key-rotation-error' => 'Fehler beim Rotieren des Schlüssels: :error',
            'key-deactivation-error' => 'Fehler beim Deaktivieren des Schlüssels: :error',
            'key-not-found' => 'API-Schlüssel nicht gefunden: :identifier',

            'maintenance-starting' => '🔄 API-Schlüssel-Wartung wird gestartet...',
            'maintenance-complete' => '✅ API-Schlüssel-Wartung abgeschlossen',
            'cleanup-expired-keys' => '🧹 Abgelaufene Schlüssel werden bereinigt...',
            'cleanup-expired-none' => '   ℹ️ Keine abgelaufenen Schlüssel zu bereinigen',
            'invalidate-deprecated' => '⚠️ Veraltete Schlüssel werden ungültig gemacht...',
            'invalidate-deprecated-none' => '   ℹ️ Keine veralteten Schlüssel zum Ungültigmachen',
            'notify-expiring' => '📧 Ablaufbenachrichtigungen werden gesendet...',
            'notify-expiring-none' => '   ℹ️ Keine Schlüssel, die Benachrichtigungen erfordern',

            'key-rotated-success' => '✅ Schlüssel erfolgreich rotiert!',
            'key-deactivated-success' => '✅ Schlüssel erfolgreich deaktiviert!',
            'deactivation-cancelled' => 'Deaktivierung abgebrochen.',
            'cleanup-cancelled' => 'Bereinigung abgebrochen.',

            'key-status-title' => 'Schlüsselstatus: :name',
            'key-status-active' => 'Aktiv: :status',
            'key-status-usable' => 'Verwendbar: :status',
            'key-status-expired' => 'Abgelaufen: :status',
            'key-status-deprecated' => 'Veraltet: :status',
            'key-status-expires-at' => 'Läuft ab am: :expires',
            'key-status-days-until-expiry' => 'Tage bis zum Ablauf: :days',
            'key-status-last-used' => 'Zuletzt verwendet: :date',
            'key-status-rotated-from' => 'Rotiert von: :key',
            'key-status-rotated-keys' => 'Aus diesem Schlüssel rotierte Schlüssel: :count',

            'policy-compliance-summary' => 'Zusammenfassung der Einhaltung der API-Schlüssel-Rotationsrichtlinie',
            'available-actions' => 'Verfügbare Aktionen: rotate, deactivate, cleanup, status, expiring, unused, summary',

            'asset-linked-success' => '✓ API-Platform-Assets erfolgreich verknüpft',
            'symlink-create-failed' => 'Symlink konnte nicht erstellt werden, Assets werden stattdessen kopiert...',
            'asset-copy-warning' => 'Warnung: API-Platform-Assets konnten weder verknüpft noch kopiert werden. Möglicherweise ist eine manuelle Einrichtung erforderlich.',
            'asset-copied-success' => '✓ API-Platform-Assets erfolgreich kopiert',

            'key-name-required' => 'Der Name des Storefront-Schlüssels darf nicht leer sein.',
            'key-generated-success' => 'Storefront-Schlüssel erfolgreich generiert!',
            'key-details' => 'Schlüsseldetails:',
            'key-secure-warning' => 'Bewahren Sie diesen Schlüssel sicher auf! Er wird im X-STOREFRONT-KEY-Header verwendet.',
            'key-share-warning' => 'Geben Sie diesen Schlüssel nicht öffentlich weiter und committen Sie ihn nicht in die Versionskontrolle.',

            'key-already-exists' => 'Ein Storefront-Schlüssel mit dem Namen \':name\' existiert bereits.',
            'cleanup-success' => '✅ :count abgelaufene Schlüssel bereinigt',
            'confirm-cleanup' => 'Dadurch werden alle abgelaufenen Schlüssel vorläufig gelöscht (Soft Delete). Fortfahren?',
            'confirm-deactivate' => 'Möchten Sie den Schlüssel :name wirklich deaktivieren?',

            'old-key' => 'Alter Schlüssel: :name',
            'old-key-id' => 'ID des alten Schlüssels: :id',
            'deprecation-date' => 'Abkündigungsdatum: :date',
            'new-key' => 'Neuer Schlüssel: :name',
            'new-key-id' => 'ID des neuen Schlüssels: :id',
            'new-key-value' => 'Wert des neuen Schlüssels: :key',
            'expires-at' => 'Läuft ab am: :date',
            'invalid-action' => 'Ungültige Aktion: :action',
            'key-active' => 'Aktiv: :status',
            'key-usable' => 'Verwendbar: :status',
            'key-expired' => 'Abgelaufen: :status',
            'key-deprecated' => 'Veraltet: :status',
            'key-field-id' => 'ID',
            'key-field-name' => 'Name',
            'key-field-key' => 'Schlüssel',
            'key-field-rate-limit' => 'Ratenlimit',
            'key-field-status' => 'Status',
            'key-days' => ' Tage',
            'key-never' => 'Nie',
            'key-requests-minute' => ' Anfragen/Minute',
            'key-unlimited' => 'Unbegrenzt',
            'rate-limit-exceeded' => 'Das angeforderte Ratenlimit überschreitet das zulässige Maximum von :max. Es wird auf :max Anfragen/Minute gesetzt.',

            'vendor-path-not-found' => 'API-Platform-Vendor-Pfad nicht gefunden unter: :path',
            'assets-already-linked' => 'API-Platform-Assets sind bereits verknüpft unter: :path',
            'no-keys-expiring' => 'Keine Schlüssel, die in den nächsten :days Tagen ablaufen',
            'keys-expiring-title' => 'Schlüssel, die in den nächsten :days Tagen ablaufen:',
            'key-display-format' => '• :name (ID: :id)',
            'key-expires-display' => '  Läuft ab: :date (noch :days Tage)',
            'no-unused-keys' => 'Keine ungenutzten Schlüssel gefunden (> :days Tage)',
            'unused-keys-title' => 'Ungenutzte Schlüssel (> :days Tage):',
            'key-last-used-display' => '  Zuletzt verwendet: :date',
            'total-keys' => 'Schlüssel gesamt: :count',
            'valid-keys' => 'Gültige Schlüssel: :count',
            'expired-keys' => 'Abgelaufene Schlüssel: :count',
            'deprecated-keys' => 'Veraltete Schlüssel: :count',
            'keys-expiring-soon' => 'Bald ablaufende Schlüssel (7 Tage): :count',
            'unused-keys-summary' => 'Ungenutzte Schlüssel (90 Tage): :count',
            'recently-rotated' => 'Kürzlich rotiert (30 Tage): :count',
            'cleanup-success-message' => '   ✅ :count abgelaufene Schlüssel bereinigt',
            'invalidate-success-message' => '   ✅ :count veraltete Schlüssel ungültig gemacht',
            'notify-success-message' => '   ✅ :count Ablaufbenachrichtigungen gesendet',
            'notify-failed-message' => '   ⚠️ Benachrichtigung zu :key fehlgeschlagen: :error',
            'publish-assets-warning' => 'Warnung: Paket-Assets konnten nicht veröffentlicht werden. :error',
            'env-file-not-found' => 'Die .env-Datei wurde nicht gefunden. Legen Sie sie vor der Installation an.',
            'env-permission-denied' => 'Die .env-Datei ist nicht beschreibbar. Prüfen Sie ihre Berechtigungen.',
            'env-config-saved' => '✓ API-Platform-Umgebungskonfiguration erfolgreich gespeichert',
            'api-endpoints' => 'API-Endpunkte:',
            'api-documentation' => 'API-Dokumentation: :url',
            'api-landing-page' => 'API-Startseite: :url',
            'graphql-playground' => 'GraphQL Playground: :url',
            'rest-api-storefront' => 'REST-API-Storefront-Swagger: :url',
            'rest-api-admin' => 'REST-API-Admin-Swagger: :url',
            'generating-jwt-keys' => 'JWT-Signaturschlüssel für Admin-API-Token werden erzeugt...',
            'jwt-keys-generated' => '✓ JWT-Signaturschlüssel sind bereit',
            'jwt-keys-warning' => 'Warnung: Die JWT-Signaturschlüssel konnten nicht erzeugt werden. Führen Sie php artisan bagisto-api:jwt-keys manuell aus. :error',
            'admin-login' => 'Admin-API-Anmeldung: :url',
            'admin-api-tokens' => 'Admin-API-Token (Server-Integrationen): :url',
            'key-rotation-invalid-key' => 'Ein ungültiger oder abgelaufener Schlüssel kann nicht rotiert werden',
            'env-file-not-found' => 'Die .env-Datei wurde nicht gefunden',
            'env-permission-denied' => 'Zugriff verweigert: In die .env-Datei kann nicht geschrieben werden. Bitte überprüfen Sie die Dateiberechtigungen.',
        ],

        'attribute-translation' => [
            'id-required' => 'Attributübersetzungs-ID ist erforderlich',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet wird ein IRI-Format wie "/api/shop/attribute-translations/1" oder eine numerische ID',
            'not-found' => 'Attributübersetzung nicht gefunden',
        ],

        'currency' => [
            'id-required' => 'Währungs-ID ist erforderlich',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet wird ein IRI-Format wie "/api/shop/currencies/1" oder eine numerische ID',
            'not-found' => 'Währung nicht gefunden',
        ],

        'customer-address' => [
            'id-required' => 'Kundenadress-ID ist erforderlich',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet wird ein IRI-Format wie "/api/shop/customer-addresses/1" oder eine numerische ID',
        ],

        'customer-order-shipment-item' => [
            'id-required' => 'Sendungsartikel-ID ist erforderlich',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet wird ein IRI-Format wie "/api/shop/customer-order-shipment-items/1" oder eine numerische ID',
            'not-found' => 'Sendungsartikel nicht gefunden',
        ],

        'page' => [
            'id-required' => 'Seiten-ID ist erforderlich',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet wird ein IRI-Format wie "/api/shop/pages/1" oder eine numerische ID',
            'not-found' => 'Seite nicht gefunden',
        ],

        'page-translation' => [
            'id-required' => 'Seitenübersetzungs-ID ist erforderlich',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet wird ein IRI-Format wie "/api/shop/page-translations/1" oder eine numerische ID',
            'not-found' => 'Seitenübersetzung nicht gefunden',
        ],

        'product-bundle-option' => [
            'id-required' => 'Bundle-Options-ID ist erforderlich',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet wird ein IRI-Format wie "/api/shop/product-bundle-options/1" oder eine numerische ID',
            'not-found' => 'Bundle-Option nicht gefunden',
        ],

        'product-bundle-option-product' => [
            'id-required' => 'ID des Bundle-Options-Produkts ist erforderlich',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet wird ein IRI-Format wie "/api/shop/product-bundle-option-products/1" oder eine numerische ID',
            'not-found' => 'Bundle-Options-Produkt nicht gefunden',
        ],

        'product-downloadable-link' => [
            'id-required' => 'Download-Link-ID ist erforderlich',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet wird ein IRI-Format wie "/api/shop/product-downloadable-links/1" oder eine numerische ID',
            'not-found' => 'Download-Link nicht gefunden',
        ],

        'product-downloadable-sample' => [
            'id-required' => 'Download-Beispiel-ID ist erforderlich',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet wird ein IRI-Format wie "/api/shop/product-downloadable-samples/1" oder eine numerische ID',
            'not-found' => 'Download-Beispiel nicht gefunden',
        ],

        'product-grouped-product' => [
            'id-required' => 'ID des gruppierten Produkts ist erforderlich',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet wird ein IRI-Format wie "/api/shop/product-grouped-products/1" oder eine numerische ID',
            'not-found' => 'Gruppiertes Produkt nicht gefunden',
        ],

        'product-images' => [
            'id-required' => 'Produktbild-ID ist erforderlich',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet wird ein IRI-Format wie "/api/shop/product-images/1" oder eine numerische ID',
            'not-found' => 'Produktbild nicht gefunden',
        ],

        'product-videos' => [
            'id-required' => 'Produktvideo-ID ist erforderlich',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet wird ein IRI-Format wie "/api/shop/product-videos/1" oder eine numerische ID',
            'not-found' => 'Produktvideo nicht gefunden',
        ],

        'theme-customization' => [
            'id-required' => 'Theme-Anpassungs-ID ist erforderlich',
            'invalid-id-format' => 'Ungültiges ID-Format. Erwartet wird ein IRI-Format wie "/api/shop/theme-customizations/1" oder eine numerische ID',
            'not-found' => 'Theme-Anpassung nicht gefunden',
        ],

        'unknown-resource' => [
            'id-required' => 'Ressourcen-ID ist erforderlich',
            'not-found' => 'Ressource nicht gefunden',
        ],
    ],

    'admin' => [
        'profile' => [
            'unauthenticated' => 'Sie sind nicht authentifiziert.',
        ],

        'dashboard' => [
            'invalid-type' => 'Unbekannter Dashboard-Statistiktyp. Gültige Typen: over-all, today, stock-threshold-products, total-sales, top-selling-products, top-customers.',
        ],

        'rma' => [
            'not-found' => 'Rücksendeanfrage nicht gefunden.',
            'invalid-input' => 'Fehlende oder ungültige Rücksendedaten.',
            'invalid-order' => 'Die ausgewählte Bestellung ist nicht gültig.',
            'invalid-item' => 'Der ausgewählte Artikel ist nicht für eine Rücksendung berechtigt.',
            'invalid-resolution-type' => 'Die Lösungsart muss "return" oder "cancel_items" sein.',
            'qty-exceeds' => 'Die angeforderte Menge übersteigt die rücksendbare Menge.',
            'created' => 'Rücksendeanfrage erfolgreich erstellt.',
            'status-updated' => 'Rücksendestatus erfolgreich aktualisiert.',
            'invalid-status' => 'Ungültiger Rücksendestatus.',
            'reopened' => 'Rücksendeanfrage erfolgreich wieder geöffnet.',
            'reopen-not-allowed' => 'Diese Rücksendeanfrage kann nicht wieder geöffnet werden.',
            'refund-failed' => 'Für diese Rücksendung konnte keine Rückerstattung erstellt werden.',
            'message-sent' => 'Nachricht erfolgreich gesendet.',
            'no-permission' => 'Sie haben keine Berechtigung, diese Aktion auszuführen.',
            'reason-not-found' => 'Rücksendegrund nicht gefunden.',
            'reason-deleted' => 'Rücksendegrund erfolgreich gelöscht.',
            'status-not-found' => 'Rücksendestatus nicht gefunden.',
            'status-deleted' => 'Rücksendestatus erfolgreich gelöscht.',
            'status-default-undeletable' => 'Ein Standard-Rücksendestatus kann nicht gelöscht werden.',
            'rule-not-found' => 'Rücksenderegel nicht gefunden.',
            'rule-deleted' => 'Rücksenderegel erfolgreich gelöscht.',
            'custom-field-not-found' => 'Benutzerdefiniertes Rücksendefeld nicht gefunden.',
            'custom-field-deleted' => 'Benutzerdefiniertes Rücksendefeld erfolgreich gelöscht.',
            'custom-field-options-required' => 'Für diesen Feldtyp ist mindestens eine Option erforderlich.',
            'mass-indices-required' => 'Mindestens eine ID ist erforderlich.',
            'mass-delete-success' => 'Ausgewählte Elemente gelöscht.',
            'mass-update-success' => 'Ausgewählte Elemente aktualisiert.',
            'mass-value-invalid' => 'Der Statuswert muss 0 oder 1 sein.',
            'image-not-found' => 'Rücksendebild nicht gefunden.',
            'attachment-not-found' => 'Nachrichtenanhang nicht gefunden.',
        ],

        'eu-withdrawal' => [
            'not-found' => 'Widerrufserklärung nicht gefunden.',
            'no-permission' => 'Sie haben keine Berechtigung, EU-Widerrufe zu verwalten.',
            'declined' => 'Widerruf als abgelehnt markiert.',
            'refunded' => 'Widerruf als erstattet markiert.',
            'confirmation-resent' => 'Bestätigungs-E-Mail erfolgreich erneut gesendet.',
            'confirmation-failed' => 'Die Bestätigungs-E-Mail konnte nicht gesendet werden.',
        ],

        'reporting' => [
            'invalid-entity' => 'Unbekannte Berichtsentität.',
            'invalid-type' => 'Unbekannter Berichtsstatistiktyp für diese Entität.',
            'export-format-unsupported' => 'Es werden nur die Exportformate csv, xls und xlsx unterstützt.',
        ],

        'configuration' => [
            'unauthenticated' => 'Sie sind nicht authentifiziert.',
            'no-permission' => 'Sie haben keine Berechtigung, die Konfiguration zu aktualisieren.',
            'slug-required' => 'Der Abfrageparameter `slug` ist erforderlich.',
            'slug-not-found' => 'Für den angegebenen Slug ist kein Konfigurationsabschnitt registriert.',
            'values-required' => 'Unter `values` ist mindestens ein Wert erforderlich.',
            'scope-escape' => 'Der Schlüssel `:key` liegt außerhalb des angeforderten Slug-Teilbaums.',
            'unknown-field' => 'Der Schlüssel `:key` ist kein registriertes Konfigurationsfeld.',
            'validation-failed' => 'Ein oder mehrere Konfigurationswerte haben die Validierung nicht bestanden.',
            'file-upload-rest-only' => 'Binäre Datei-Uploads für das Feld `:field` werden nur über den REST-Multipart-Endpunkt unterstützt, nicht über GraphQL.',
            'custom-view-readonly' => 'Das Feld `:field` ist ein benutzerdefiniertes, per Blade gerendertes Feld und kann nicht über die API aktualisiert werden.',
            'update-success' => 'Konfiguration erfolgreich aktualisiert.',
        ],

        'customer' => [
            'not-found' => 'Kunde nicht gefunden.',
            'no-permission' => 'Sie haben keine Berechtigung, diese Aktion auszuführen.',
            'has-active-orders' => 'Der Kunde hat ausstehende oder in Bearbeitung befindliche Bestellungen und kann nicht gelöscht werden.',
            'deleted' => 'Kunde erfolgreich gelöscht.',
            'mass-delete-indices-required' => 'Mindestens eine Kunden-ID ist erforderlich.',
            'mass-delete-success' => 'Kunden gelöscht.',
            'mass-update-indices-required' => 'Mindestens eine Kunden-ID ist erforderlich.',
            'mass-update-value-invalid' => 'Der Statuswert muss 0 oder 1 sein.',
            'mass-update-success' => 'Kundenstatus aktualisiert.',

            'address' => [
                'not-found' => 'Adresse nicht gefunden.',
                'not-owned' => 'Diese Adresse gehört nicht zum angegebenen Kunden.',
                'customer-id-required' => 'customerId ist erforderlich, um den Inhaber der Adresse zu ermitteln.',
                'deleted' => 'Adresse erfolgreich gelöscht.',
            ],

            'note' => [
                'empty' => 'Der Notiztext ist erforderlich.',
                'created' => 'Notiz erfolgreich hinzugefügt.',
            ],

            'review' => [
                'not-found' => 'Bewertung nicht gefunden.',
                'no-permission' => 'Sie haben keine Berechtigung, diese Aktion auszuführen.',
                'invalid-status' => 'Der Status muss einer der folgenden sein: pending, approved, disapproved.',
                'updated' => 'Bewertung erfolgreich aktualisiert.',
                'deleted' => 'Bewertung erfolgreich gelöscht.',
                'mass-delete-indices-required' => 'Mindestens eine Bewertungs-ID ist erforderlich.',
                'mass-delete-success' => 'Bewertungen gelöscht.',
                'mass-update-indices-required' => 'Mindestens eine Bewertungs-ID ist erforderlich.',
                'mass-update-value-invalid' => 'Der Statuswert muss einer der folgenden sein: pending, approved, disapproved.',
                'mass-update-success' => 'Bewertungsstatus aktualisiert.',
            ],

            'group' => [
                'not-found' => 'Kundengruppe nicht gefunden.',
                'deleted' => 'Kundengruppe erfolgreich gelöscht.',
                'is-system' => 'System-Kundengruppen können nicht gelöscht werden.',
                'has-customers' => 'Der Kundengruppe sind Kunden zugeordnet, daher kann sie nicht gelöscht werden.',
                'system-code-immutable' => 'Der Code einer System-Kundengruppe kann nicht geändert werden.',
                'system-flag-immutable' => 'Das Flag is_user_defined einer System-Kundengruppe kann nicht geändert werden.',
                'mass-delete-indices-required' => 'Mindestens eine Kundengruppen-ID ist erforderlich.',
                'mass-delete-success' => 'Kundengruppen gelöscht.',
            ],

            'gdpr' => [
                'not-found' => 'DSGVO-Anfrage nicht gefunden.',
                'no-permission' => 'Sie haben keine Berechtigung, diese Aktion auszuführen.',
                'deleted' => 'DSGVO-Anfrage erfolgreich gelöscht.',
                'invalid-status' => 'Der Status muss einer der folgenden sein: pending, processing, declined, approved, revoked.',
                'no-changes' => 'Mindestens Status oder Nachricht ist erforderlich.',
                'already-processed' => 'Diese DSGVO-Anfrage wurde bereits bearbeitet und kann nicht erneut ausgeführt werden.',

                'process' => [
                    'approved' => 'DSGVO-Anfrage genehmigt.',
                    'deleted' => 'DSGVO-Anfrage genehmigt und Kunde gelöscht.',
                ],
            ],
        ],

        'order' => [
            'not-found' => 'Bestellung nicht gefunden.',

            'reorder' => [
                'success' => 'Erneute Bestellung erfolgreich. Ein neuer Entwurfswarenkorb wurde erstellt.',
                'cannot-reorder' => 'Diese Bestellung kann nicht erneut bestellt werden (Gastbestellung oder ein oder mehrere Artikel sind nicht mehr verkäuflich).',
                'failed' => 'Erneute Bestellung fehlgeschlagen. Bitte versuchen Sie es erneut.',
                'guest-not-supported' => 'Eine erneute Bestellung wird für Gastbestellungen nicht unterstützt.',
                'items-not-saleable' => 'Ein oder mehrere Artikel dieser Bestellung sind nicht mehr käuflich erhältlich.',
                'no-permission' => 'Sie haben keine Berechtigung, Bestellungen zu erstellen.',
                'disabled-in-settings' => 'Die erneute Bestellung durch den Administrator ist in den Shop-Einstellungen deaktiviert.',
            ],

            'actions' => [
                'cancel' => [
                    'closed' => 'Geschlossene Bestellungen können nicht storniert werden.',
                    'fraud' => 'Betrugsbestellungen können nicht storniert werden.',
                    'nothing-to-cancel' => 'Bei dieser Bestellung gibt es nichts zu stornieren.',
                    'already-processed' => 'Diese Bestellung wurde bereits in Rechnung gestellt oder versandt und kann nicht mehr storniert werden. Sie können stattdessen eine Rückerstattung veranlassen.',
                    'no-permission' => 'Sie haben keine Berechtigung, Bestellungen zu stornieren.',
                    'success' => 'Bestellung erfolgreich storniert.',
                    'failed' => 'Die Bestellung konnte nicht storniert werden.',
                ],

                'comment' => [
                    'empty' => 'Ein Kommentar ist erforderlich.',
                    'success' => 'Kommentar zur Bestellung hinzugefügt.',
                    'failed' => 'Der Kommentar konnte nicht hinzugefügt werden.',
                ],

                'invoice' => [
                    'closed' => 'Für geschlossene Bestellungen können keine Rechnungen erstellt werden.',
                    'fraud' => 'Für Betrugsbestellungen können keine Rechnungen erstellt werden.',
                    'nothing-to-invoice' => 'Bei dieser Bestellung gibt es nichts in Rechnung zu stellen.',
                    'already-invoiced' => 'Für diese Bestellung wurde bereits eine Rechnung erstellt.',
                    'paypal-standard-blocked' => 'Für Bestellungen, die über PayPal Standard bezahlt wurden, können keine Rechnungen erstellt werden.',
                    'no-permission' => 'Sie haben keine Berechtigung, Rechnungen zu erstellen.',
                    'items-required' => 'Mindestens ein Artikel mit einer positiven Menge ist erforderlich.',
                    'qty-exceeds' => 'Die angeforderte Menge (:requested) übersteigt die verfügbare Menge (:available) für SKU :sku.',
                    'not-found' => 'Rechnung nicht gefunden.',
                    'success' => 'Rechnung erfolgreich erstellt.',
                    'failed' => 'Die Rechnung konnte nicht erstellt werden.',
                    'pdf-failed' => 'Das Rechnungs-PDF konnte nicht generiert werden.',
                    'email-invalid' => 'Eine gültige Empfänger-E-Mail-Adresse ist erforderlich.',
                    'duplicate-sent' => 'Rechnungs-E-Mail an :email gesendet.',
                    'mass-status-indices-required' => 'Mindestens eine Rechnungs-ID ist erforderlich.',
                    'mass-status-value-invalid' => 'Der Status muss einer der folgenden sein: pending, paid, overdue.',
                    'mass-status-success' => 'Rechnungsstatus erfolgreich aktualisiert.',
                ],

                'shipment' => [
                    'closed' => 'Geschlossene Bestellungen können nicht versandt werden.',
                    'fraud' => 'Betrugsbestellungen können nicht versandt werden.',
                    'nothing-to-ship' => 'Bei dieser Bestellung gibt es nichts zu versenden.',
                    'no-permission' => 'Sie haben keine Berechtigung, Bestellungen zu versenden.',
                    'source-required' => 'Die Lagerquelle ist erforderlich.',
                    'items-required' => 'Mindestens ein Artikel mit einer positiven Menge ist erforderlich.',
                    'qty-exceeds' => 'Die angeforderte Menge (:requested) übersteigt die verfügbare Menge (:available) für SKU :sku.',
                    'inventory-insufficient' => 'Der Bestand in der ausgewählten Quelle reicht für SKU :sku nicht aus.',
                    'not-found' => 'Sendung nicht gefunden.',
                    'success' => 'Sendung erfolgreich erstellt.',
                    'failed' => 'Die Sendung konnte nicht erstellt werden.',
                ],

                'refund' => [
                    'closed' => 'Für geschlossene Bestellungen ist keine Rückerstattung möglich.',
                    'fraud' => 'Für Betrugsbestellungen ist keine Rückerstattung möglich.',
                    'nothing-to-refund' => 'Bei dieser Bestellung gibt es nichts mehr zu erstatten.',
                    'no-permission' => 'Sie haben keine Berechtigung, Rückerstattungen zu erstellen.',
                    'qty-exceeds' => 'Die angeforderte Menge (:requested) übersteigt die verfügbare Menge (:available) für SKU :sku.',
                    'amount-zero' => 'Der berechnete Rückerstattungsbetrag ist null. Passen Sie Menge, Versand- oder Anpassungswerte an.',
                    'amount-exceeds-max' => 'Der Rückerstattungsbetrag (:amount) übersteigt den maximal erstattungsfähigen Betrag (:max).',
                    'not-found' => 'Rückerstattung nicht gefunden.',
                    'success' => 'Rückerstattung erfolgreich erstellt.',
                    'failed' => 'Die Rückerstattung konnte nicht erstellt werden.',
                ],
            ],
        ],

        'appearance' => [
            'no-permission' => 'Sie haben keine Berechtigung, das Erscheinungsbild zu verwalten.',

            'theme' => [
                'not-found' => 'Theme nicht gefunden.',
                'not-installed' => 'Dieses Theme ist nicht installiert.',
                'channel-ids-required' => 'Mindestens ein Kanal ist erforderlich.',
                'unknown-channel' => 'Ein oder mehrere Kanäle existieren nicht.',
                'activated' => 'Theme erfolgreich aktiviert.',
            ],

            'section' => [
                'not-found' => 'Abschnitt nicht gefunden.',
                'invalid-payload' => 'Nicht unterstützte Abschnittsdaten.',
                'options-required' => 'Optionen sind erforderlich.',
                'status-required' => 'Der Status ist erforderlich.',
                'section-ids-required' => 'Mindestens ein Abschnitt ist erforderlich.',
                'reorder-single-scope' => 'Abschnitte können jeweils nur innerhalb eines Themes und Kanals neu angeordnet werden.',
                'reorder-incomplete' => 'Für die Neuanordnung werden alle Abschnitte des Themes und Kanals in der Reihenfolge benötigt, in der sie gerendert werden sollen.',
                'unknown-section' => 'Ein oder mehrere Abschnitte existieren nicht.',
                'file-required' => 'Eine Datei ist erforderlich.',
                'created' => 'Abschnitt erfolgreich erstellt.',
                'updated' => 'Abschnitt erfolgreich aktualisiert.',
                'deleted' => 'Abschnitt erfolgreich gelöscht.',
                'draft-saved' => 'Abschnittsänderungen erfolgreich vorgemerkt.',
                'status-staged' => 'Abschnittsstatus erfolgreich vorgemerkt.',
                'order-staged' => 'Abschnittsreihenfolge erfolgreich vorgemerkt.',
                'duplicated' => 'Abschnitt erfolgreich kopiert.',
                'published' => 'Abschnittsänderungen erfolgreich veröffentlicht.',
                'discarded' => 'Abschnittsänderungen erfolgreich verworfen.',
                'media-uploaded' => 'Medien erfolgreich hochgeladen.',
            ],
        ],

        'sales' => [
            'no-permission' => 'Sie haben keine Berechtigung, diese Verkaufsressource anzuzeigen.',

            'invoice' => [
                'not-found' => 'Rechnung nicht gefunden.',
                'no-permission' => 'Sie haben keine Berechtigung, Rechnungen anzuzeigen.',
            ],

            'shipment' => [
                'not-found' => 'Sendung nicht gefunden.',
                'no-permission' => 'Sie haben keine Berechtigung, Sendungen anzuzeigen.',
            ],

            'refund' => [
                'not-found' => 'Rückerstattung nicht gefunden.',
                'no-permission' => 'Sie haben keine Berechtigung, Rückerstattungen anzuzeigen.',
                'export-format-unsupported' => 'Es werden nur Exporte im Format csv, xls und xlsx unterstützt.',
            ],

            'transaction' => [
                'not-found' => 'Transaktion nicht gefunden.',
                'no-permission' => 'Sie haben keine Berechtigung, Transaktionen anzuzeigen.',

                'create' => [
                    'invoice-required' => 'Das Feld invoiceId ist erforderlich.',
                    'payment-method-required' => 'Das Feld paymentMethod ist erforderlich.',
                    'amount-invalid' => 'Das Feld amount ist erforderlich und muss numerisch sein.',
                    'invoice-missing' => 'Die angegebene Rechnung wurde nicht gefunden.',
                    'already-paid' => 'Diese Rechnung ist bereits vollständig bezahlt.',
                    'amount-exceeds' => 'Der Zahlungsbetrag übersteigt die Gesamtsumme der Rechnung.',
                    'amount-zero' => 'Der Zahlungsbetrag muss größer als null sein.',
                ],
            ],

            'booking' => [
                'not-found' => 'Buchung nicht gefunden.',
                'no-permission' => 'Sie haben keine Berechtigung, Buchungen anzuzeigen.',
            ],

            'export' => [
                'format-unsupported' => 'Es werden nur Exporte im Format csv, xls und xlsx unterstützt.',
            ],
        ],

        'cart' => [
            'not-found' => 'Warenkorb nicht gefunden.',
            'not-draft' => 'Dieser Warenkorb kann nicht über die Admin-API geändert werden.',
            'product-required' => 'productId ist erforderlich.',
            'product-not-found' => 'Produkt nicht gefunden.',
            'booking-unsupported' => 'Buchungsprodukte können nicht zu einer Admin-Entwurfsbestellung hinzugefügt werden. Buchungen müssen über die Kunden-Storefront erfolgen.',
            'product-not-saleable' => 'Dieses Produkt ist nicht verkäuflich (nicht vorrätig oder deaktiviert) und kann nicht zur Bestellung hinzugefügt werden.',
            'qty-required' => 'qty ist erforderlich und muss ein nicht leeres Objekt aus itemId => Menge sein.',
            'cart-item-required' => 'cartItemId ist erforderlich.',
            'item-added' => 'Artikel zum Warenkorb hinzugefügt.',
            'item-add-failed' => 'Der Artikel konnte nicht zum Warenkorb hinzugefügt werden.',
            'item-updated' => 'Warenkorbartikel aktualisiert.',
            'item-update-failed' => 'Die Warenkorbartikel konnten nicht aktualisiert werden.',
            'item-removed' => 'Artikel aus dem Warenkorb entfernt.',
            'item-remove-failed' => 'Der Warenkorbartikel konnte nicht entfernt werden.',
            'address-saved' => 'Adresse gespeichert.',
            'address-required' => 'Eine Rechnungsadresse ist erforderlich.',
            'address-incomplete' => 'Das Feld :field ist erforderlich, um die Adresse zu speichern.',
            'coupon-code-required' => 'Ein Gutscheincode ist erforderlich.',
            'coupon-applied' => 'Gutschein angewendet.',
            'coupon-already-applied' => 'Der Gutschein wurde bereits auf diesen Warenkorb angewendet.',
            'coupon-not-found' => 'Gutschein nicht gefunden oder inaktiv.',
            'coupon-removed' => 'Gutschein entfernt.',
            'coupon-error' => 'Beim Anwenden des Gutscheins ist ein Fehler aufgetreten.',
            'unknown-error' => 'Etwas ist schiefgelaufen.',
            'draft-created' => 'Entwurfswarenkorb erstellt.',
            'draft-failed' => 'Der Entwurfswarenkorb konnte nicht erstellt werden.',
            'addresses-required-for-shipping' => 'Adressen müssen gespeichert werden, bevor eine Versandart ausgewählt wird.',
            'shipping-required-for-payment' => 'Die Versandart muss vor der Zahlungsart ausgewählt werden.',
            'shipping-method-required' => 'Die Versandart ist erforderlich.',
            'shipping-method-saved' => 'Versandart gespeichert.',
            'shipping-method-failed' => 'Die Versandart konnte nicht gespeichert werden.',
            'payment-method-required' => 'Die Zahlungsart ist erforderlich.',
            'payment-method-saved' => 'Zahlungsart gespeichert.',
            'payment-method-failed' => 'Die Zahlungsart konnte nicht gespeichert werden.',

            'place-order' => [
                'success' => 'Bestellung erfolgreich aufgegeben.',
                'failed' => 'Die Bestellung konnte nicht aufgegeben werden.',
                'error' => 'Die Bestellung konnte nicht aufgegeben werden: Der Warenkorb enthält Fehler.',
                'minimum-order-error' => 'Der Mindestbestellwert beträgt :amount. Fügen Sie weitere Artikel hinzu, bevor Sie die Bestellung aufgeben.',
                'empty-cart' => 'Der Warenkorb ist leer — fügen Sie Artikel hinzu, bevor Sie eine Bestellung aufgeben.',
                'addresses-required' => 'Rechnungs- und Lieferadresse müssen gespeichert werden, bevor die Bestellung aufgegeben wird.',
                'shipping-required' => 'Vor dem Aufgeben der Bestellung muss eine Versandart ausgewählt werden.',
                'payment-required' => 'Vor dem Aufgeben der Bestellung muss eine Zahlungsart ausgewählt werden.',
                'payment-method-unsupported' => 'Für vom Administrator aufgegebene Bestellungen werden nur cashondelivery und moneytransfer unterstützt.',
            ],
        ],

        'product' => [
            'not-found' => 'Produkt nicht gefunden.',

            'mass-delete-success' => 'Produkte erfolgreich gelöscht.',
            'mass-update-status-success' => 'Produktstatus erfolgreich aktualisiert.',
            'indices-required' => 'Das Feld indices ist erforderlich und muss ein nicht leeres Array sein.',
            'indices-invalid' => 'Das Feld indices muss ein Array aus ganzzahligen Produkt-IDs sein.',
            'value-invalid' => 'Das Feld value ist erforderlich und muss 0 oder 1 sein.',
            'mass-delete-failed' => 'Ein oder mehrere Produkte konnten nicht gelöscht werden.',
            'no-permission' => 'Sie haben keine Berechtigung, Produkte zu verwalten.',

            'copy-success' => 'Produkt erfolgreich kopiert.',
            'copy-failed' => 'Das Produkt konnte nicht kopiert werden.',
            'copy-variant-not-supported' => 'Varianten konfigurierbarer Produkte können nicht kopiert werden. Kopieren Sie stattdessen das übergeordnete konfigurierbare Produkt.',

            'create' => [
                'created' => 'Produkt erfolgreich erstellt.',
                'create-failed' => 'Das Produkt konnte nicht erstellt werden.',
                'sku-required' => 'Das Feld sku ist erforderlich.',
                'sku-unique' => 'Die sku ist bereits vergeben.',
                'sku-invalid' => 'Die sku darf nur Buchstaben, Zahlen und Bindestriche enthalten (z. B. "sp-001").',
                'attribute-family-required' => 'Das Feld attribute_family_id ist erforderlich.',
                'attribute-family-not-found' => 'Die ausgewählte Attributfamilie existiert nicht.',
                'type-required' => 'Das Feld type ist erforderlich.',
                'type-not-yet-supported' => 'Der Produkttyp ":type" wird von dieser API nicht unterstützt. Zulässige Typen: simple, virtual, downloadable, grouped, bundle, configurable, booking.',
                'super-attributes-required' => 'Das Feld super_attributes ist erforderlich, wenn type=configurable. Geben Sie eine Zuordnung von Attributcode (oder ID) zu option_ids an, z. B. { "color": [1,2], "size": [4,5] }.',
                'super-attributes-invalid' => 'Das Feld super_attributes muss eine nicht leere Zuordnung von Attributcode (oder ID) zu einer nicht leeren Liste von option_ids sein.',
                'no-permission' => 'Sie haben keine Berechtigung, Produkte zu erstellen.',
            ],

            'update' => [
                'updated' => 'Produkt erfolgreich aktualisiert.',
                'update-failed' => 'Das Produkt konnte nicht aktualisiert werden.',
                'id-required' => 'Eine Produkt-ID ist erforderlich.',
                'url-key-required' => 'Das Feld url_key darf nicht leer sein.',
                'url-key-unique' => 'Der url_key wird bereits von einem anderen Produkt verwendet.',
                'special-price-invalid' => 'Der special_price muss eine Zahl kleiner als price sein.',
                'special-price-date-range-invalid' => 'special_price_to muss am oder nach special_price_from liegen.',
                'boolean-field-invalid' => 'Das Feld :field muss 0 oder 1 sein.',
                'categories-invalid' => 'Das Feld categories muss ein Array aus Kategorie-IDs sein.',
                'channels-invalid' => 'Das Feld channels muss ein Array aus Kanal-IDs sein.',
                'super-attributes-cannot-change' => 'super_attributes können nach dem Erstellen des Produkts nicht mehr geändert werden.',
                'sub-resource-stripped-images' => 'Bilder müssen über POST /api/admin/catalog/products/{id}/images verwaltet werden. Das Feld images wurde ignoriert.',
                'sub-resource-stripped-inventories' => 'Bestände müssen über PUT /api/admin/catalog/products/{id}/inventories verwaltet werden. Das Feld inventories wurde ignoriert.',
                'sub-resource-stripped-customer-group-prices' => 'Kundengruppenpreise müssen über /api/admin/catalog/products/{id}/customer-group-prices verwaltet werden. Das Feld customer_group_prices wurde ignoriert.',
                'translations-single-locale' => 'Nur das angeforderte Gebietsschema wurde aktualisiert. Um die Gebietsschemas :locales zu aktualisieren, senden Sie für jedes eine separate Anfrage mit ?locale=<code>.',
            ],

            'delete' => [
                'deleted' => 'Produkt erfolgreich gelöscht.',
                'delete-failed' => 'Das Produkt konnte nicht gelöscht werden.',
            ],

            'image' => [
                'uploaded' => 'Produktbild erfolgreich hochgeladen.',
                'updated' => 'Produktbild erfolgreich aktualisiert.',
                'update-empty' => 'Senden Sie einen Alt-Text oder eine Position zum Aktualisieren.',
                'reordered' => 'Produktbilder erfolgreich neu angeordnet.',
                'deleted' => 'Produktbild erfolgreich gelöscht.',
                'image-required' => 'Eine Bilddatei ist erforderlich (Multipart-Feld "image").',
                'image-invalid-type' => 'Der Bildtyp muss einer der folgenden sein: bmp, jpeg, jpg, png, webp.',
                'image-too-large' => 'Das Bild ist zu groß. Die maximal zulässige Größe beträgt 4 MB.',
                'not-found' => 'Produktbild nicht gefunden.',
                'no-permission' => 'Sie haben keine Berechtigung, Produktbilder zu verwalten.',
                'upload-failed' => 'Das Produktbild konnte nicht hochgeladen werden.',
                'reorder-failed' => 'Die Produktbilder konnten nicht neu angeordnet werden.',
                'delete-failed' => 'Das Produktbild konnte nicht gelöscht werden.',
                'order-required' => 'Das Array "order" ist erforderlich und darf nicht leer sein.',
                'order-invalid' => 'Jeder Eintrag in "order" muss ein Objekt mit id und position sein.',
                'id-not-on-product' => 'Das Bild :id gehört nicht zu diesem Produkt.',
                'graphql-upload-unsupported' => 'Das Hochladen von Bildern über GraphQL wird nicht unterstützt. Verwenden Sie POST /api/admin/catalog/products/{productId}/images mit multipart/form-data.',
            ],

            'downloadable' => [
                'file-required' => 'Eine Datei ist erforderlich (Multipart-Feld "file").',
                'file-too-large' => 'Die Datei ist zu groß. Die maximal zulässige Größe beträgt 128 MB.',
                'upload-failed' => 'Die herunterladbare Datei konnte nicht hochgeladen werden.',
                'file-not-found' => 'Für dieses Produkt und Attribut wurde keine gespeicherte Datei gefunden.',
                'no-permission' => 'Sie haben keine Berechtigung, Dateien herunterladbarer Produkte zu verwalten.',
            ],

            'video' => [
                'uploaded' => 'Produktvideo erfolgreich hochgeladen.',
                'deleted' => 'Produktvideo erfolgreich gelöscht.',
                'video-required' => 'Eine Videodatei ist erforderlich (Multipart-Feld "video").',
                'video-invalid-type' => 'Der Videotyp muss einer der folgenden sein: mp4, webm, ogg, mov.',
                'video-too-large' => 'Das Video ist zu groß. Die maximal zulässige Größe beträgt 100 MB.',
                'not-found' => 'Produktvideo nicht gefunden.',
                'no-permission' => 'Sie haben keine Berechtigung, Produktvideos zu verwalten.',
                'upload-failed' => 'Das Produktvideo konnte nicht hochgeladen werden.',
                'delete-failed' => 'Das Produktvideo konnte nicht gelöscht werden.',
                'graphql-upload-unsupported' => 'Das Hochladen von Videos über GraphQL wird nicht unterstützt. Verwenden Sie POST /api/admin/catalog/products/{productId}/videos mit multipart/form-data.',
            ],

            'inventory' => [
                'updated' => 'Produktbestände erfolgreich gespeichert.',
                'inventories-required' => 'Das Feld inventories ist erforderlich und muss eine nicht leere Zuordnung von inventory_source_id zu Menge sein.',
                'inventories-invalid' => 'Das Feld inventories muss eine Zuordnung von inventory_source_id (positive ganze Zahl) zu Menge (nicht negative ganze Zahl) sein.',
                'source-not-found' => 'Die Lagerquelle :id existiert nicht.',
                'qty-invalid' => 'Jede Menge muss eine nicht negative ganze Zahl sein.',
                'no-permission' => 'Sie haben keine Berechtigung, Produktbestände zu verwalten.',
                'not-found' => 'Produkt nicht gefunden.',
            ],

            'customer-group-price' => [
                'created' => 'Kundengruppenpreis erfolgreich hinzugefügt.',
                'updated' => 'Kundengruppenpreis erfolgreich aktualisiert.',
                'deleted' => 'Kundengruppenpreis erfolgreich gelöscht.',
                'qty-required' => 'Das Feld qty ist erforderlich.',
                'qty-invalid' => 'Das Feld qty muss eine ganze Zahl größer oder gleich 1 sein.',
                'value-type-required' => 'Das Feld value_type ist erforderlich.',
                'value-type-invalid' => 'Das Feld value_type muss entweder "fixed" oder "discount" sein.',
                'value-required' => 'Das Feld value ist erforderlich.',
                'value-invalid' => 'Das Feld value muss eine nicht negative Zahl sein.',
                'customer-group-not-found' => 'Die ausgewählte Kundengruppe existiert nicht.',
                'duplicate-qty-group' => 'Für dieses Produkt existiert bereits ein Kundengruppenpreis für diese Kombination (qty, customer_group_id).',
                'not-found' => 'Kundengruppenpreis nicht gefunden.',
                'no-permission' => 'Sie haben keine Berechtigung, Kundengruppenpreise von Produkten zu verwalten.',
            ],
        ],

        'category' => [
            'not-found' => 'Kategorie nicht gefunden.',

            'created' => 'Kategorie erfolgreich erstellt.',
            'updated' => 'Kategorie erfolgreich aktualisiert.',
            'deleted' => 'Kategorie erfolgreich gelöscht.',
            'slug-required' => 'Das Feld slug ist erforderlich.',
            'slug-unique' => 'Der slug ist bereits vergeben.',
            'name-required' => 'Das Feld name ist erforderlich.',
            'position-required' => 'Das Feld position ist erforderlich.',
            'attributes-required' => 'Mindestens ein filterbares Attribut ist erforderlich.',
            'description-required-for-mode' => 'Für den gewählten Anzeigemodus ist eine Beschreibung erforderlich.',
            'cannot-delete-root' => 'Stammkategorien und Kanal-Stammkategorien können nicht gelöscht werden.',
            'cannot-delete-channel-root' => 'Diese Kategorie ist eine Kanal-Stammkategorie und kann nicht gelöscht werden.',
            'delete-failed' => 'Die Kategorie konnte nicht gelöscht werden.',
            'no-permission' => 'Sie haben keine Berechtigung, Kategorien zu verwalten.',
            'mass-delete-success' => 'Kategorien erfolgreich gelöscht.',
            'mass-update-status-success' => 'Kategoriestatus erfolgreich aktualisiert.',
            'mass-delete-indices-required' => 'Das Feld indices ist erforderlich und muss ein nicht leeres Array sein.',
            'mass-update-status-value-required' => 'Das Feld value ist erforderlich und muss 0 oder 1 sein.',
        ],

        'attribute' => [
            'not-found' => 'Attribut nicht gefunden.',

            'create-success' => 'Attribut erfolgreich erstellt.',
            'update-success' => 'Attribut erfolgreich aktualisiert.',
            'delete-success' => 'Attribut erfolgreich gelöscht.',
            'delete-failed' => 'Das Attribut konnte nicht gelöscht werden.',
            'mass-delete-success' => 'Attribute erfolgreich gelöscht.',
            'mass-delete-indices-required' => 'Das Feld indices ist erforderlich und muss ein nicht leeres Array sein.',

            'code-immutable' => 'Der Attributcode kann nach dem Erstellen des Attributs nicht mehr geändert werden.',
            'type-immutable' => 'Der Attributtyp kann nicht geändert werden, solange Produktwerte vorhanden sind.',
            'locale-scope-immutable' => 'Der Gebietsschema-Geltungsbereich kann nicht geändert werden, solange das Attribut Produktwerte hat.',
            'system-attribute' => 'Systemattribute können nicht gelöscht werden.',
            'in-use-family' => 'Das Attribut ist Teil einer oder mehrerer Attributfamilien (Gruppen-IDs: :ids). Entfernen Sie es zuerst aus diesen Familien.',

            'option-not-found' => 'Attributoption nicht gefunden.',
            'option-not-supported' => 'Der Attributtyp ":type" unterstützt keine Optionen. Nur Attribute vom Typ select, multiselect und checkbox können Optionen haben.',
            'option-in-use' => 'Diese Option wird von :count Produkt(en) verwendet und kann nicht gelöscht werden.',
            'option-delete-success' => 'Attributoption erfolgreich gelöscht.',
        ],

        'family' => [
            'not-found' => 'Attributfamilie nicht gefunden.',

            'created' => 'Attributfamilie erfolgreich erstellt.',
            'updated' => 'Attributfamilie erfolgreich aktualisiert.',
            'deleted' => 'Attributfamilie erfolgreich gelöscht.',
            'code-required' => 'Der Familiencode ist erforderlich.',
            'code-unique' => 'Der Familiencode muss eindeutig sein.',
            'name-required' => 'Der Familienname ist erforderlich.',
            'group-code-required' => 'Jede Attributgruppe muss einen Code haben.',
            'group-name-required' => 'Jede Attributgruppe muss einen Namen haben.',
            'group-column-invalid' => 'Jede Spalte einer Attributgruppe muss 1 oder 2 sein.',
            'group-name-duplicate' => 'Der Name einer Attributgruppe muss innerhalb der Attributfamilie eindeutig sein.',
            'update-failed' => 'Die Attributfamilie konnte nicht aktualisiert werden.',
            'create-failed' => 'Die Attributfamilie konnte nicht erstellt werden.',
            'default-delete-error' => 'Die Standard-Attributfamilie kann nicht gelöscht werden.',
            'last-delete-error' => 'Mindestens eine Attributfamilie ist erforderlich.',
            'attribute-product-error' => 'Diese Attributfamilie wird von einem oder mehreren Produkten verwendet. Weisen Sie diese Produkte neu zu, bevor Sie sie löschen.',
            'delete-failed' => 'Die Attributfamilie konnte nicht gelöscht werden.',
            'no-permission' => 'Sie haben keine Berechtigung, Attributfamilien zu verwalten.',
        ],

        'settings' => [
            'locale' => [
                'not-found' => 'Gebietsschema nicht gefunden.',
                'created' => 'Gebietsschema erfolgreich erstellt.',
                'updated' => 'Gebietsschema erfolgreich aktualisiert.',
                'deleted' => 'Gebietsschema erfolgreich gelöscht.',
                'cannot-delete-last' => 'Mindestens ein Gebietsschema ist erforderlich — das letzte verbleibende Gebietsschema kann nicht gelöscht werden.',
                'cannot-delete-channel-default' => 'Dieses Gebietsschema ist das Standard-Gebietsschema eines oder mehrerer Kanäle und kann nicht gelöscht werden.',
                'delete-failed' => 'Das Gebietsschema konnte nicht gelöscht werden.',
                'no-permission' => 'Sie haben keine Berechtigung, Gebietsschemas zu verwalten.',
                'mass-delete-success' => 'Gebietsschemas erfolgreich gelöscht.',
                'mass-delete-indices-required' => 'Das Feld indices ist erforderlich und muss ein nicht leeres Array sein.',
            ],

            'theme' => [
                'not-found' => 'Theme-Anpassung nicht gefunden.',
                'created' => 'Theme-Anpassung erfolgreich erstellt.',
                'updated' => 'Theme-Anpassung erfolgreich aktualisiert.',
                'deleted' => 'Theme-Anpassung erfolgreich gelöscht.',
                'delete-failed' => 'Die Theme-Anpassung konnte nicht gelöscht werden.',
                'no-permission' => 'Sie haben keine Berechtigung, Theme-Anpassungen zu verwalten.',
                'mass-delete-success' => 'Theme-Anpassungen erfolgreich gelöscht.',
                'mass-delete-indices-required' => 'Das Feld indices ist erforderlich und muss ein nicht leeres Array sein.',
                'mass-update-success' => 'Theme-Anpassungen erfolgreich aktualisiert.',
                'mass-update-indices-required' => 'Das Feld indices ist erforderlich und muss ein nicht leeres Array sein.',
                'mass-update-value-invalid' => 'Das Feld value muss 0 oder 1 sein.',
            ],

            'inventory-source' => [
                'not-found' => 'Lagerquelle nicht gefunden.',
                'created' => 'Lagerquelle erfolgreich erstellt.',
                'updated' => 'Lagerquelle erfolgreich aktualisiert.',
                'deleted' => 'Lagerquelle erfolgreich gelöscht.',
                'code-unique' => 'Eine Lagerquelle mit diesem Code existiert bereits.',
                'last-delete-error' => 'Mindestens eine Lagerquelle ist erforderlich — die letzte verbleibende Quelle kann nicht gelöscht werden.',
                'in-use' => 'Diese Lagerquelle wird von Produktbeständen referenziert und kann nicht gelöscht werden.',
                'delete-failed' => 'Die Lagerquelle konnte nicht gelöscht werden.',
                'no-permission' => 'Sie haben keine Berechtigung, Lagerquellen zu verwalten.',
                'mass-delete-success' => 'Lagerquellen erfolgreich gelöscht.',
                'mass-delete-indices-required' => 'Das Feld indices ist erforderlich und muss ein nicht leeres Array sein.',
            ],

            'exchange-rate' => [
                'not-found' => 'Wechselkurs nicht gefunden.',
                'created' => 'Wechselkurs erfolgreich erstellt.',
                'updated' => 'Wechselkurs erfolgreich aktualisiert.',
                'deleted' => 'Wechselkurs erfolgreich gelöscht.',
                'duplicate-pair' => 'Für diese Zielwährung existiert bereits ein Wechselkurs. Aktualisieren Sie den vorhandenen Eintrag oder löschen Sie ihn zuerst.',
                'delete-failed' => 'Der Wechselkurs konnte nicht gelöscht werden.',
                'no-permission' => 'Sie haben keine Berechtigung, Wechselkurse zu verwalten.',
                'mass-delete-success' => 'Wechselkurse erfolgreich gelöscht.',
                'mass-delete-indices-required' => 'Das Feld indices ist erforderlich und muss ein nicht leeres Array sein.',
                'update-rates-success' => 'Wechselkurse erfolgreich aktualisiert.',
                'update-rates-failed' => 'Die Wechselkurse konnten nicht vom externen Anbieter aktualisiert werden.',
                'provider-not-configured' => 'Es ist kein externer Wechselkursanbieter konfiguriert.',
            ],

            'tax-rate' => [
                'not-found' => 'Steuersatz nicht gefunden.',
                'created' => 'Steuersatz erfolgreich erstellt.',
                'updated' => 'Steuersatz erfolgreich aktualisiert.',
                'deleted' => 'Steuersatz erfolgreich gelöscht.',
                'delete-failed' => 'Der Steuersatz konnte nicht gelöscht werden.',
                'no-permission' => 'Sie haben keine Berechtigung, Steuersätze zu verwalten.',
                'zip-code-required' => 'Das Feld zip_code ist erforderlich, wenn is_zip false ist.',
                'zip-range-required' => 'Sowohl zip_from als auch zip_to sind erforderlich, wenn is_zip true ist.',
            ],

            'tax-category' => [
                'not-found' => 'Steuerkategorie nicht gefunden.',
                'created' => 'Steuerkategorie erfolgreich erstellt.',
                'updated' => 'Steuerkategorie erfolgreich aktualisiert.',
                'deleted' => 'Steuerkategorie erfolgreich gelöscht.',
                'code-unique' => 'Der Code ist bereits vergeben.',
                'cannot-delete-in-use' => 'Dieser Steuerkategorie sind noch Steuersätze zugeordnet, daher kann sie nicht gelöscht werden.',
                'delete-failed' => 'Die Steuerkategorie konnte nicht gelöscht werden.',
                'no-permission' => 'Sie haben keine Berechtigung, Steuerkategorien zu verwalten.',
            ],

            'role' => [
                'not-found' => 'Rolle nicht gefunden.',
                'created' => 'Rolle erfolgreich erstellt.',
                'updated' => 'Rolle erfolgreich aktualisiert.',
                'deleted' => 'Rolle erfolgreich gelöscht.',
                'permissions-required' => 'Das Feld permissions ist erforderlich, wenn permission_type custom ist.',
                'cannot-delete-in-use' => 'Diese Rolle ist einem oder mehreren Administratoren zugewiesen und kann nicht gelöscht werden.',
                'cannot-delete-last-role' => 'Mindestens eine Rolle ist erforderlich — die letzte verbleibende Rolle kann nicht gelöscht werden.',
                'cannot-change-last-all-access' => 'Die einzige Rolle mit Vollzugriff kann nicht in eine benutzerdefinierte Rolle geändert werden.',
                'delete-failed' => 'Die Rolle konnte nicht gelöscht werden.',
                'no-permission' => 'Sie haben keine Berechtigung, Rollen zu verwalten.',
            ],

            'currency' => [
                'not-found' => 'Währung nicht gefunden.',
                'created' => 'Währung erfolgreich erstellt.',
                'updated' => 'Währung erfolgreich aktualisiert.',
                'deleted' => 'Währung erfolgreich gelöscht.',
                'code-required' => 'Das Feld code ist erforderlich.',
                'code-size' => 'Der Code muss aus genau 3 Buchstaben bestehen.',
                'code-unique' => 'Der Code ist bereits vergeben.',
                'name-required' => 'Das Feld name ist erforderlich.',
                'cannot-delete-last' => 'Mindestens eine Währung ist erforderlich — die letzte verbleibende Währung kann nicht gelöscht werden.',
                'cannot-delete-channel-base' => 'Diese Währung ist die Basiswährung eines oder mehrerer Kanäle und kann nicht gelöscht werden.',
                'delete-failed' => 'Die Währung konnte nicht gelöscht werden.',
                'no-permission' => 'Sie haben keine Berechtigung, Währungen zu verwalten.',

                'mass-delete-success' => 'Währungen erfolgreich gelöscht.',
                'mass-delete-indices-required' => 'Das Feld indices ist erforderlich und muss ein nicht leeres Array sein.',
            ],

            'channel' => [
                'not-found' => 'Kanal nicht gefunden.',

                'created' => 'Kanal erfolgreich erstellt.',
                'updated' => 'Kanal erfolgreich aktualisiert.',
                'deleted' => 'Kanal erfolgreich gelöscht.',
                'code-unique' => 'Der Code ist bereits vergeben.',
                'hostname-unique' => 'Der Hostname ist bereits vergeben.',
                'default-locale-mismatch' => 'Die default_locale_id muss in der Liste locales enthalten sein.',
                'base-currency-mismatch' => 'Die base_currency_id muss in der Liste currencies enthalten sein.',
                'unknown-locale' => 'Eine oder mehrere Gebietsschema-IDs existieren nicht.',
                'unknown-currency' => 'Eine oder mehrere Währungs-IDs existieren nicht.',
                'unknown-inventory-source' => 'Eine oder mehrere inventory_source-IDs existieren nicht.',
                'unknown-root-category' => 'Die root_category_id existiert nicht.',
                'cannot-delete-last' => 'Mindestens ein Kanal ist erforderlich — der letzte verbleibende Kanal kann nicht gelöscht werden.',
                'cannot-delete-default' => 'Der Standardkanal der Anwendung kann nicht gelöscht werden.',
                'delete-failed' => 'Der Kanal konnte nicht gelöscht werden.',
                'no-permission' => 'Sie haben keine Berechtigung, Kanäle zu verwalten.',
            ],

            'user' => [
                'not-found' => 'Administrator nicht gefunden.',
                'created' => 'Administrator erfolgreich erstellt.',
                'updated' => 'Administrator erfolgreich aktualisiert.',
                'deleted' => 'Administrator erfolgreich gelöscht.',
                'name-required' => 'Das Feld name ist erforderlich.',
                'email-required' => 'Das Feld email ist erforderlich.',
                'email-invalid' => 'Die E-Mail muss eine gültige E-Mail-Adresse sein.',
                'email-unique' => 'Die E-Mail ist bereits vergeben.',
                'password-required' => 'Das Feld password ist erforderlich.',
                'password-min' => 'Das Passwort muss mindestens 6 Zeichen lang sein.',
                'role-required' => 'Das Feld role_id ist erforderlich.',
                'role-not-found' => 'Die ausgewählte Rolle existiert nicht.',
                'status-invalid' => 'Der Status muss 0 oder 1 sein.',
                'cannot-delete-self' => 'Sie können Ihr eigenes Administratorkonto nicht löschen.',
                'cannot-delete-last-admin' => 'Mindestens ein Administrator ist erforderlich — der letzte verbleibende Administrator kann nicht gelöscht werden.',
                'cannot-change' => 'Das Feld :name des letzten Administrators mit Vollzugriff kann nicht geändert werden.',
                'delete-failed' => 'Der Administrator konnte nicht gelöscht werden.',
                'no-permission' => 'Sie haben keine Berechtigung, Admin-Benutzer zu verwalten.',
                'self-delete-password-required' => 'Das Feld password ist erforderlich, um Ihr eigenes Konto zu löschen.',
                'self-delete-incorrect-password' => 'Das Passwort ist falsch.',
                'self-deleted' => 'Ihr Administratorkonto wurde gelöscht.',
            ],

            'data-transfer' => [
                'import' => [
                    'not-found' => 'Import nicht gefunden.',
                    'deleted' => 'Import erfolgreich gelöscht.',
                    'cancelled' => 'Import erfolgreich abgebrochen.',
                    'cannot-cancel' => 'Nur ausstehende oder in Bearbeitung befindliche Importe können abgebrochen werden. Aktueller Status: :state.',
                    'no-permission' => 'Sie haben keine Berechtigung, Datenübertragungs-Importe zu verwalten.',
                    'delete-failed' => 'Der Import konnte nicht gelöscht werden.',
                    'created' => 'Import erfolgreich erstellt.',
                    'updated' => 'Import erfolgreich aktualisiert.',
                    'type-invalid' => 'Der ausgewählte Typ ist ungültig.',
                    'action-invalid' => 'Die Aktion muss eine der folgenden sein: append, delete.',
                    'validation-strategy-invalid' => 'Die Validierungsstrategie muss eine der folgenden sein: stop-on-errors, skip-errors.',
                    'allowed-errors-invalid' => 'Die zulässigen Fehler müssen eine ganze Zahl von 0 oder mehr sein.',
                    'field-separator-required' => 'Das Feldtrennzeichen ist erforderlich.',
                    'file-required' => 'Eine Datei ist erforderlich.',
                    'file-invalid-type' => 'Die Datei muss einen der folgenden Typen haben: csv, xls, xlsx, xml.',
                    'nothing-to-import' => 'Es gibt nichts zu importieren.',
                    'not-valid' => 'Der Import ist nicht gültig.',
                    'setup-queue-error' => 'Die Queue-Verbindung ist auf sync eingestellt; konfigurieren Sie eine echte Queue, um Importe im Hintergrund zu verarbeiten.',
                    'file-not-found' => 'Es ist keine Datei zum Herunterladen verfügbar.',
                    'error-report-not-found' => 'Es ist kein Fehlerbericht zum Herunterladen verfügbar.',
                    'sample-not-found' => 'Für den angeforderten Typ und das angeforderte Format ist keine Beispieldatei verfügbar.',
                    'sample-format-invalid' => 'Das Beispielformat muss eines der folgenden sein: csv, xls, xlsx, xml.',
                    'images-archive-too-large' => 'Das Bilderarchiv darf höchstens :entries Dateien enthalten und entpackt höchstens :size MB groß sein.',
                ],
            ],
        ],

        'cms' => [
            'page' => [
                'not-found' => 'CMS-Seite nicht gefunden.',

                'created' => 'CMS-Seite erfolgreich erstellt.',
                'url-key-required' => 'Das Feld url_key ist erforderlich.',
                'url-key-unique' => 'Der url_key ist bereits vergeben.',
                'url-key-invalid' => 'Das Format des url_key ist ungültig. Verwenden Sie nur Kleinbuchstaben, Zahlen und Bindestriche.',
                'page-title-required' => 'Das Feld page_title ist erforderlich.',
                'html-content-required' => 'Das Feld html_content ist erforderlich.',
                'channels-required' => 'Mindestens ein Kanal muss ausgewählt werden.',
                'channels-invalid' => 'Ein oder mehrere Kanäle sind ungültig.',

                'updated' => 'CMS-Seite erfolgreich aktualisiert.',

                'deleted' => 'CMS-Seite erfolgreich gelöscht.',
                'delete-failed' => 'Die CMS-Seite konnte nicht gelöscht werden.',

                'mass-deleted' => 'CMS-Seiten erfolgreich gelöscht.',
                'indices-required' => 'Das Feld indices ist erforderlich und muss ein nicht leeres Array sein.',
                'indices-invalid' => 'Das Feld indices muss ein Array aus CMS-Seiten-IDs sein.',

                'no-permission' => 'Sie haben keine Berechtigung, CMS-Seiten zu verwalten.',
            ],
        ],

        'marketing' => [
            'catalog-rule' => [
                'not-found' => 'Katalogregel nicht gefunden.',
                'created' => 'Katalogregel erfolgreich erstellt.',
                'updated' => 'Katalogregel erfolgreich aktualisiert.',
                'deleted' => 'Katalogregel erfolgreich gelöscht.',
                'delete-failed' => 'Die Katalogregel konnte nicht gelöscht werden.',
                'mass-delete-indices-required' => 'Für die Massenlöschung wurden keine Katalogregel-IDs angegeben.',
                'mass-delete-success' => 'Ausgewählte Katalogregeln erfolgreich gelöscht.',
                'no-permission' => 'Sie haben keine Berechtigung, Katalogregeln zu verwalten.',
            ],

            'campaign' => [
                'not-found' => 'Kampagne nicht gefunden.',
                'created' => 'Kampagne erfolgreich erstellt.',
                'updated' => 'Kampagne erfolgreich aktualisiert.',
                'deleted' => 'Kampagne erfolgreich gelöscht.',
                'delete-failed' => 'Die Kampagne konnte nicht gelöscht werden.',
                'no-permission' => 'Sie haben keine Berechtigung, Kampagnen zu verwalten.',

                'send' => [
                    'id-required' => 'Die Kampagnen-ID ist erforderlich.',
                    'inactive' => 'Eine inaktive Kampagne kann nicht gesendet werden. Aktivieren Sie sie zuerst.',
                    'queued' => 'Kampagne für :count Empfänger in die Warteschlange gestellt.',
                ],
            ],

            'sitemap' => [
                'not-found' => 'Sitemap nicht gefunden.',
                'created' => 'Sitemap erfolgreich erstellt.',
                'updated' => 'Sitemap erfolgreich aktualisiert.',
                'deleted' => 'Sitemap erfolgreich gelöscht.',
                'delete-failed' => 'Die Sitemap konnte nicht gelöscht werden.',
                'no-permission' => 'Sie haben keine Berechtigung, Sitemaps zu verwalten.',

                'generate' => [
                    'id-required' => 'Die Sitemap-ID ist erforderlich.',
                    'no-channels' => 'Diese Sitemap umfasst keinen Kanal, daher gibt es nichts zu generieren. Weisen Sie ihr zuerst mindestens einen Kanal zu.',
                    'success' => 'Sitemap erfolgreich neu generiert.',
                    'failed' => 'Sitemap-Generierung fehlgeschlagen: :message',
                ],
            ],

            'cart-rule' => [
                'not-found' => 'Warenkorbregel nicht gefunden.',
                'id-required' => 'Eine Warenkorbregel-ID ist erforderlich.',
                'copy-of' => 'Kopie von :value',
                'copied' => 'Warenkorbregel erfolgreich kopiert.',
                'created' => 'Warenkorbregel erfolgreich erstellt.',
                'updated' => 'Warenkorbregel erfolgreich aktualisiert.',
                'deleted' => 'Warenkorbregel erfolgreich gelöscht.',
                'delete-failed' => 'Die Warenkorbregel konnte nicht gelöscht werden.',
                'no-permission' => 'Sie haben keine Berechtigung, Warenkorbregeln zu verwalten.',
                'mass-delete-success' => 'Warenkorbregeln erfolgreich gelöscht.',
                'mass-delete-indices-required' => 'Das Feld indices ist erforderlich und muss ein nicht leeres Array sein.',
                'action-type-invalid' => 'Der action_type muss einer der folgenden sein: by_percent, by_fixed, cart_fixed, buy_x_get_y.',
                'coupon-code-required' => 'Ein coupon_code ist erforderlich, wenn coupon_type=1 und use_auto_generation=0.',
                'coupon-code-unique' => 'Dieser Gutscheincode wird bereits verwendet.',
            ],

            'template' => [
                'campaign-associated' => 'Diese E-Mail-Vorlage wird von einer Kampagne verwendet und kann daher nicht gelöscht werden. Entfernen Sie sie zuerst aus der Kampagne.',
                'not-found' => 'E-Mail-Vorlage nicht gefunden.',
                'created' => 'E-Mail-Vorlage erfolgreich erstellt.',
                'updated' => 'E-Mail-Vorlage erfolgreich aktualisiert.',
                'deleted' => 'E-Mail-Vorlage erfolgreich gelöscht.',
                'delete-failed' => 'Die E-Mail-Vorlage konnte nicht gelöscht werden.',
                'status-invalid' => 'Der Status muss einer der folgenden sein: active, inactive, draft.',
                'no-permission' => 'Sie haben keine Berechtigung, E-Mail-Vorlagen zu verwalten.',
            ],

            'event' => [
                'campaign-associated' => 'Dieses Ereignis wird von einer Kampagne verwendet und kann daher nicht gelöscht werden. Entfernen Sie es zuerst aus der Kampagne.',
                'not-found' => 'Marketing-Ereignis nicht gefunden.',
                'created' => 'Marketing-Ereignis erfolgreich erstellt.',
                'updated' => 'Marketing-Ereignis erfolgreich aktualisiert.',
                'deleted' => 'Marketing-Ereignis erfolgreich gelöscht.',
                'delete-failed' => 'Das Marketing-Ereignis konnte nicht gelöscht werden.',
                'no-permission' => 'Sie haben keine Berechtigung, Marketing-Ereignisse zu verwalten.',
            ],

            'search-synonym' => [
                'not-found' => 'Suchsynonym nicht gefunden.',
                'created' => 'Suchsynonym erfolgreich erstellt.',
                'updated' => 'Suchsynonym erfolgreich aktualisiert.',
                'deleted' => 'Suchsynonym erfolgreich gelöscht.',
                'mass-deleted' => 'Suchsynonyme erfolgreich gelöscht.',
                'delete-failed' => 'Das Suchsynonym konnte nicht gelöscht werden.',
                'indices-required' => 'Das Feld indices ist erforderlich und muss ein nicht leeres Array aus Suchsynonym-IDs sein.',
                'no-permission' => 'Sie haben keine Berechtigung, Suchsynonyme zu verwalten.',
            ],

            'url-rewrite' => [
                'not-found' => 'URL-Umschreibung nicht gefunden.',
                'created' => 'URL-Umschreibung erfolgreich erstellt.',
                'updated' => 'URL-Umschreibung erfolgreich aktualisiert.',
                'deleted' => 'URL-Umschreibung erfolgreich gelöscht.',
                'delete-failed' => 'Die URL-Umschreibung konnte nicht gelöscht werden.',
                'mass-deleted' => 'URL-Umschreibungen erfolgreich gelöscht.',
                'indices-required' => 'Mindestens eine URL-Umschreibungs-ID ist erforderlich.',
                'no-permission' => 'Sie haben keine Berechtigung, URL-Umschreibungen zu verwalten.',
            ],

            'cart-rule-coupon' => [
                'cart-rule-not-found' => 'Warenkorbregel nicht gefunden.',
                'not-found' => 'Gutschein nicht gefunden.',
                'code-taken' => 'Ein Gutschein mit diesem Code existiert bereits.',
                'format-invalid' => 'Das Format muss eines der folgenden sein: alphabetic, alphanumeric, numeric.',
                'indices-required' => 'Das Feld indices ist erforderlich und muss ein nicht leeres Array aus Gutschein-IDs sein.',
                'created' => 'Gutschein erfolgreich erstellt.',
                'generated' => ':count Gutschein(e) erfolgreich generiert.',
                'deleted' => 'Gutschein erfolgreich gelöscht.',
                'mass-deleted' => ':count Gutschein(e) erfolgreich gelöscht.',
                'no-permission' => 'Sie haben keine Berechtigung, Gutscheine von Warenkorbregeln zu verwalten.',
            ],

            'subscriber' => [
                'not-found' => 'Newsletter-Abonnent nicht gefunden.',
                'updated' => 'Abonnement erfolgreich aktualisiert.',
                'deleted' => 'Abonnement erfolgreich gelöscht.',
                'is-subscribed-required' => 'Das Feld is_subscribed ist erforderlich.',
                'no-permission' => 'Sie haben keine Berechtigung, Newsletter-Abonnenten zu verwalten.',
            ],

            'search-term' => [
                'not-found' => 'Suchbegriff nicht gefunden.',
                'updated' => 'Suchbegriff erfolgreich aktualisiert.',
                'deleted' => 'Suchbegriff erfolgreich gelöscht.',
                'mass-deleted' => 'Suchbegriffe erfolgreich gelöscht.',
                'term-required' => 'Das Feld term ist erforderlich.',
                'redirect-url-invalid' => 'Die redirect_url muss eine gültige URL sein.',
                'indices-required' => 'In indices muss mindestens eine ID angegeben werden.',
                'no-permission' => 'Sie haben keine Berechtigung, Suchbegriffe zu verwalten.',
            ],
        ],
        'customer-api-sessions' => [
            'menu' => [
                'title' => 'API-Sitzungen',
            ],

            'acl' => [
                'title' => 'API-Sitzungen',
                'delete' => 'Widerrufen',
            ],

            'index' => [
                'title' => 'Kunden-API-Sitzungen',
                'info' => 'Kunden, die über Apps wie Web-, Mobil-, Kassen- oder Desktop-Apps an der Shop-API angemeldet sind. Das Widerrufen einer Sitzung meldet das Gerät sofort ab.',
            ],

            'fields' => [
                'customer' => 'Kunde',
                'email' => 'E-Mail',
                'channel' => 'Kanal',
            ],

            'datagrid' => [
                'revoke-all' => 'Alle Sitzungen des Kunden widerrufen',
            ],

            'card' => [
                'title' => 'API-Sitzungen (:count)',
                'empty' => 'Dieser Kunde hat keine aktiven API-Sitzungen.',
            ],

            'errors' => [
                'no-permission' => 'Sie haben keine Berechtigung, Kunden-API-Sitzungen zu verwalten.',
            ],
        ],

        'customer-auth' => [
            'configuration' => [
                'title' => 'Kundenauthentifizierung',
                'info' => 'Token-Laufzeiten, Grenzwerte und Sitzungsregeln für Kundenanmeldungen im Shop.',

                'tokens' => [
                    'title' => 'Token-Laufzeiten',
                    'info' => 'Wie lange Zugriffstokens, Aktualisierungstokens und Sitzungen gültig bleiben. Änderungen gelten für danach ausgestellte Tokens.',
                    'access-token-ttl' => 'Laufzeit des Zugriffstokens (Minuten)',
                    'access-token-ttl-info' => 'Zwischen 5 und 60 Minuten. Standard 15.',
                    'refresh-token-ttl' => 'Laufzeit des Aktualisierungstokens (Tage)',
                    'refresh-token-ttl-info' => 'Zwischen 1 und 90 Tagen. Standard 30.',
                    'session-lifetime' => 'Maximale Sitzungsdauer (Tage)',
                    'session-lifetime-info' => 'Zwischen 1 und 365 Tagen. Standard 90. Danach muss sich der Kunde erneut anmelden, egal wie oft die Sitzung aktualisiert wurde.',
                    'max-sessions' => 'Maximale aktive Sitzungen je Kunde',
                    'max-sessions-info' => 'Wird das Limit erreicht, wird die älteste Sitzung beendet. Zwischen 0 und 100. 0 bedeutet kein Limit.',
                ],

                'rate-limits' => [
                    'title' => 'Ratenbegrenzungen',
                    'info' => 'Maximale Shop-API-Anfragen pro Minute je angemeldetem Kunden.',
                    'requests-per-minute' => 'Anfragen pro Minute je angemeldetem Kunden',
                    'requests-per-minute-info' => 'Zwischen 1 und 100000. Standard 120.',
                ],

                'login-protection' => [
                    'title' => 'Anmeldeschutz',
                    'info' => 'Wie viele fehlgeschlagene Anmeldungen und Token-Aktualisierungen erlaubt sind, bevor der Client warten muss.',
                    'login-max-attempts' => 'Fehlgeschlagene Anmeldungen bis zur Sperre',
                    'login-max-attempts-info' => 'Je E-Mail-Adresse und IP-Adresse. Zwischen 3 und 20. Standard 5.',
                    'login-lockout-seconds' => 'Anmeldesperre (Sekunden)',
                    'login-lockout-seconds-info' => 'Zwischen 60 und 3600. Standard 60.',
                    'refresh-requests-per-minute' => 'Token-Aktualisierungen pro Minute je Sitzung',
                    'refresh-requests-per-minute-info' => 'Zwischen 1 und 60. Standard 10.',
                ],

                'security' => [
                    'title' => 'Sicherheit',
                    'info' => 'Was die anderen Sitzungen eines Kunden beendet.',
                    'revoke-sessions-on-password-change' => 'Sitzungen bei Passwortänderung widerrufen',
                ],

                'retention' => [
                    'title' => 'Datenaufbewahrung',
                    'info' => 'Wie lange beendete Kundensitzungen aufbewahrt werden.',
                    'session-prune-days' => 'Beendete Sitzungen aufbewahren (Tage)',
                    'session-prune-days-info' => 'Widerrufene oder abgelaufene Sitzungen werden nach so vielen Tagen gelöscht. Zwischen 1 und 365. Standard 30.',
                ],
            ],
        ],
        'auth' => [
            'configuration' => [
                'title' => 'Admin-Authentifizierung',
                'info' => 'Token-Laufzeiten, Ratenbegrenzungen und Sicherheitsregeln für Admin-API-Anmeldungen und API-Tokens.',

                'tokens' => [
                    'title' => 'Token-Laufzeiten',
                    'info' => 'Wie lange Zugriffstokens, Aktualisierungstokens und Sitzungen gültig bleiben. Änderungen gelten für danach ausgestellte Tokens.',
                    'access-token-ttl' => 'Laufzeit des Zugriffstokens (Minuten)',
                    'access-token-ttl-info' => 'Zwischen 5 und 60 Minuten. Standard 15.',
                    'refresh-token-ttl' => 'Laufzeit des Aktualisierungstokens (Tage)',
                    'refresh-token-ttl-info' => 'Zwischen 1 und 90 Tagen. Standard 30.',
                    'session-lifetime' => 'Maximale Sitzungsdauer (Tage)',
                    'session-lifetime-info' => 'Zwischen 1 und 365 Tagen. Standard 90. Danach muss sich der Admin erneut anmelden, egal wie oft die Sitzung aktualisiert wurde.',
                ],

                'rate-limits' => [
                    'title' => 'Ratenbegrenzungen',
                    'info' => 'Maximale Admin-API-Anfragen pro Minute.',
                    'session-requests-per-minute' => 'Anfragen pro Minute je angemeldetem Admin',
                    'api-token-requests-per-minute' => 'Anfragen pro Minute je API-Token',
                    'session-requests-per-minute-info' => 'Zwischen 1 und 100000. Standard 60.',
                    'api-token-requests-per-minute-info' => 'Zwischen 1 und 100000. Standard 60.',
                    'unauthenticated-requests-per-minute' => 'Anfragen pro Minute ohne Token je IP-Adresse',
                    'unauthenticated-requests-per-minute-info' => 'Zwischen 10 und 1000. Standard 60. Umfasst Anmelde-, Zwei-Faktor-, Aktualisierungs- und Passwortanfragen.',
                ],

                'security' => [
                    'title' => 'Sicherheit',
                    'info' => 'Widerrufsregeln, Ablauf von API-Tokens und Domainbindung.',
                    'revoke-sessions-on-password-change' => 'Sitzungen bei Passwortänderung widerrufen',
                    'revoke-api-tokens-on-password-change' => 'API-Tokens bei Passwortänderung widerrufen',
                    'allow-non-expiring-api-tokens' => 'API-Tokens ohne Ablaufdatum erlauben',
                    'domain-binding-mode' => 'Domainbindungsmodus',
                    'domain-binding-mode-info' => 'DNS prüft, dass die Anfrage von einer IP-Adresse kommt, auf die die Domain des Tokens auflöst. Origin prüft den Origin- oder Referer-Header und eignet sich nur für Browser.',
                    'domain-binding-dns' => 'DNS (IP-Adresse des Clients)',
                    'domain-binding-origin' => 'Origin-Header',
                ],

                'login-protection' => [
                    'title' => 'Anmeldeschutz',
                    'info' => 'Wie viele fehlgeschlagene Anmeldungen, falsche Zwei-Faktor-Codes und Passwortanfragen erlaubt sind, bevor der Client warten muss.',
                    'login-max-attempts' => 'Fehlgeschlagene Anmeldungen bis zur Sperre',
                    'login-max-attempts-info' => 'Je E-Mail-Adresse und IP-Adresse. Zwischen 3 und 20. Standard 5.',
                    'login-lockout-seconds' => 'Anmeldesperre (Sekunden)',
                    'login-lockout-seconds-info' => 'Zwischen 60 und 3600. Standard 60.',
                    'two-factor-challenge-lifetime' => 'Gültigkeit der Zwei-Faktor-Abfrage (Minuten)',
                    'two-factor-challenge-lifetime-info' => 'Zwischen 1 und 15. Standard 5.',
                    'two-factor-max-failures' => 'Falsche Zwei-Faktor-Codes pro Minute',
                    'two-factor-max-failures-info' => 'Je Admin, über alle Abfragen hinweg. Zwischen 3 und 20. Standard 5.',
                    'password-requests-per-minute' => 'Passwortanfragen pro Minute',
                    'password-requests-per-minute-info' => 'Anfragen zum Vergessen und Zurücksetzen des Passworts je IP-Adresse. Zwischen 1 und 20. Standard 5.',
                ],

                'retention' => [
                    'title' => 'Datenaufbewahrung',
                    'info' => 'Wie lange beendete Sitzungen und der API-Prüfverlauf aufbewahrt werden.',
                    'session-prune-days' => 'Beendete Sitzungen aufbewahren (Tage)',
                    'session-prune-days-info' => 'Widerrufene oder abgelaufene Sitzungen werden nach so vielen Tagen gelöscht. Zwischen 1 und 365. Standard 30.',
                    'audit-retention-days' => 'API-Prüfverlauf aufbewahren (Tage)',
                    'audit-retention-days-info' => 'Ältere Einträge werden täglich gelöscht. Zwischen 0 und 3650. Leer oder 0 bewahrt sie dauerhaft auf.',
                ],
            ],

            'errors' => [
                'unauthenticated' => 'Sie sind nicht authentifiziert.',
                'access-token-expired' => 'Das Zugriffstoken ist abgelaufen. Aktualisieren Sie es, um fortzufahren.',
                'api-token-expired' => 'Das API-Token ist abgelaufen.',
                'refresh-token-invalid' => 'Das Aktualisierungstoken ist ungültig oder abgelaufen. Bitte melden Sie sich erneut an.',
                'api-token-revoked' => 'Das API-Token wurde widerrufen.',
                'api-token-domain-mismatch' => 'Das API-Token kann von dieser Domain aus nicht verwendet werden.',
                'access-token-required' => 'Diese Aktion erfordert das Zugriffstoken einer Anmeldesitzung; API-Tokens können sie nicht verwenden.',
                'two-factor-required' => 'Geben Sie den 6-stelligen Code aus Ihrer Authentifizierungs-App oder einen Ihrer Backup-Codes ein, um die Anmeldung abzuschließen.',
                'too-many-login-attempts' => 'Zu viele Anmeldeversuche. Bitte versuchen Sie es in :seconds Sekunden erneut.',
                'two-factor-challenge-invalid' => 'Die Zwei-Faktor-Anfrage ist ungültig oder abgelaufen. Bitte melden Sie sich erneut an.',
                'two-factor-attempts-exceeded' => 'Zu viele falsche Codes. Bitte melden Sie sich erneut an.',
                'password-reset-token-invalid' => 'Dieses Token zum Zurücksetzen des Passworts ist ungültig oder abgelaufen.',
                'too-many-password-requests' => 'Zu viele Passwortanfragen. Bitte versuchen Sie es in :seconds Sekunden erneut.',
                'rate-limit-exceeded' => 'Zu viele Versuche.',
                'no-permission' => 'Sie haben keine Berechtigung, Admin-API-Sitzungen zu verwalten.',
            ],

            'sessions' => [
                'logged-out' => 'Sie wurden abgemeldet.',
                'logged-out-all' => 'Sie wurden von allen Sitzungen abgemeldet.',
                'not-found' => 'Sitzung nicht gefunden.',
                'revoked' => 'Die Sitzung wurde widerrufen.',
                'revoked-for-admin' => 'Widerrufene Sitzungen: :count.',
            ],

            'password' => [
                'reset' => 'Ihr Passwort wurde zurückgesetzt. Bitte melden Sie sich erneut an.',
            ],
        ],

        'api-tokens' => [
            'menu' => [
                'title' => 'API-Token',
            ],

            'acl' => [
                'title' => 'API-Token',
                'create' => 'Erstellen',
                'delete' => 'Widerrufen',
                'manage-others' => 'Token anderer Administratoren verwalten',
            ],

            'index' => [
                'title' => 'API-Token',
                'info' => 'Server-Integrationen wie ein ERP-, PIM-, Lager- oder Buchhaltungssystem authentifizieren sich mit API-Token. Jedes Token handelt als ein Administrator mit den Berechtigungen seiner Rolle und funktioniert nur von der Domain, an die es gebunden ist.',
                'create-btn' => 'API-Token erstellen',
            ],

            'create' => [
                'title' => 'API-Token erstellen',
                'back-btn' => 'Zurück',
                'save-btn' => 'Token erstellen',
                'general' => 'Allgemein',
                'expiry' => 'Ablauf',
                'admin-info' => 'Das Token handelt als dieser Administrator und hat genau die Berechtigungen seiner Rolle.',
                'domain-info' => 'Der Host, von dem die Integration aus aufruft, z. B. erp.example.com. Eine URL wird auf ihren Host gekürzt. IP-Adressen und Platzhalter werden nicht akzeptiert, und die Domain kann später nicht geändert werden.',
                'expires-on-date' => 'Läuft an einem Datum ab',
                'never-expires' => 'Läuft nie ab',
                'no-expiry-warning' => 'Ein Token ohne Ablauf funktioniert, bis es widerrufen wird. Bewahren Sie es in einem Geheimnisspeicher auf und widerrufen Sie es, sobald es nicht mehr benötigt wird.',
                'no-expiry-confirm' => 'Mir ist bewusst, dass dieses Token nie abläuft.',
                'no-admins' => 'Es gibt keinen Administrator, für den Sie ein API-Token erstellen dürfen.',
            ],

            'view' => [
                'title' => 'API-Token',
                'back-btn' => 'Zurück',
                'save-btn' => 'Speichern',
                'revoke-btn' => 'Widerrufen',
                'details' => 'Details',
                'usage' => 'Nutzung',
                'token-once-title' => 'Kopieren Sie jetzt Ihr API-Token',
                'token-once-info' => 'Das Token wird nur dieses eine Mal angezeigt. Bewahren Sie es in einem Geheimnisspeicher auf; geht es verloren, widerrufen Sie es und erstellen Sie ein neues.',
                'copy-btn' => 'Kopieren',
                'copied' => 'Das Token wurde kopiert.',
                'usage-info' => 'Senden Sie es als Bearer-Token im Authorization-Header von REST- und GraphQL-Anfragen.',
                'not-yet' => 'Noch nicht',
                'revoke-confirm' => 'Dieses API-Token widerrufen? Die Integration, die es nutzt, funktioniert sofort nicht mehr.',
            ],

            'fields' => [
                'id' => 'ID',
                'name' => 'Name',
                'admin' => 'Administrator',
                'token' => 'Token',
                'domain' => 'Domain',
                'expiry' => 'Ablauf',
                'expires-at' => 'Läuft ab am',
                'no-expiry-confirmation' => 'Bestätigung ohne Ablauf',
                'status' => 'Status',
                'created-by' => 'Erstellt von',
                'created-at' => 'Erstellt am',
                'last-used-at' => 'Zuletzt verwendet am',
                'last-ip-address' => 'Letzte IP-Adresse',
                'last-user-agent' => 'Letzter User-Agent',
                'last-channel' => 'Letzter Kanal',
                'resolved-ips' => 'Aufgelöste IP-Adressen',
                'resolved-at' => 'Aufgelöst am',
                'domain-mismatches' => 'Anfragen von anderen Domains',
                'last-domain-mismatch-at' => 'Letzte Anfrage von einer anderen Domain',
                'revoked-at' => 'Widerrufen am',
                'revoked-by' => 'Widerrufen von',
            ],

            'datagrid' => [
                'never' => 'Nie',
                'console' => 'Konsolenbefehl',
                'view' => 'Ansehen',
                'revoke' => 'Widerrufen',
            ],

            'status' => [
                'active' => 'Aktiv',
                'expired' => 'Abgelaufen',
                'revoked' => 'Widerrufen',
            ],

            'channels' => [
                'rest' => 'REST',
                'graphql' => 'GraphQL',
            ],

            'messages' => [
                'created' => 'Das API-Token wurde erstellt.',
                'updated' => 'Das API-Token wurde aktualisiert.',
                'revoked' => 'Das API-Token wurde widerrufen.',
            ],

            'errors' => [
                'no-permission' => 'Sie haben keine Berechtigung, API-Token zu verwalten.',
                'not-found' => 'API-Token nicht gefunden.',
                'cannot-create-for-admin' => 'Sie können für diesen Administrator kein API-Token erstellen: Seine Rolle hat Berechtigungen, die Ihre nicht hat, oder Sie dürfen die Token anderer Administratoren nicht verwalten.',
                'cannot-manage' => 'Sie können dieses API-Token nicht verwalten.',
                'already-revoked' => 'Das API-Token ist bereits widerrufen.',
                'invalid-name' => 'Der Name ist erforderlich und darf höchstens 100 Zeichen lang sein.',
                'invalid-domain' => 'Geben Sie einen Domainnamen wie erp.example.com ein. IP-Adressen und Platzhalter werden nicht akzeptiert.',
                'expiry-required' => 'API-Token müssen ein Ablaufdatum haben.',
                'expiry-in-past' => 'Das Ablaufdatum muss in der Zukunft liegen.',
            ],
        ],

        'api-sessions' => [
            'menu' => [
                'title' => 'API-Sitzungen',
            ],

            'acl' => [
                'title' => 'API-Sitzungen',
                'delete' => 'Widerrufen',
            ],

            'index' => [
                'title' => 'API-Sitzungen',
                'info' => 'Administratoren, die über Apps wie eine Web-, Mobil-, Kassen- oder Desktop-App an der Admin-API angemeldet sind. Das Widerrufen einer Sitzung meldet dieses Gerät sofort ab.',
            ],

            'fields' => [
                'id' => 'ID',
                'admin' => 'Administrator',
                'device' => 'Gerät',
                'client-type' => 'Client-Typ',
                'ip-address' => 'Anmelde-IP-Adresse',
                'user-agent' => 'Anmelde-User-Agent',
                'last-ip-address' => 'Letzte IP-Adresse',
                'last-user-agent' => 'Letzter User-Agent',
                'last-used-at' => 'Zuletzt verwendet am',
                'last-channel' => 'Letzter Kanal',
                'refresh-expires-at' => 'Refresh läuft ab am',
                'expires-at' => 'Läuft ab am',
                'status' => 'Status',
                'created-at' => 'Angemeldet am',
            ],

            'datagrid' => [
                'revoke' => 'Widerrufen',
                'revoke-all' => 'Alle Sitzungen dieses Administrators widerrufen',
            ],

            'client-types' => [
                'web' => 'Web',
                'mobile' => 'Mobil',
                'pos' => 'Kasse',
                'desktop' => 'Desktop',
                'other' => 'Sonstige',
            ],

            'errors' => [
                'not-active' => 'Diese Sitzung ist bereits widerrufen oder abgelaufen.',
            ],
        ],

        'api-audit' => [
            'menu' => [
                'title' => 'API-Audit',
            ],

            'acl' => [
                'title' => 'API-Änderungsverlauf',
                'delete' => 'Verlauf löschen',
            ],

            'index' => [
                'title' => 'API-Änderungsverlauf',
                'info' => 'Bei jeder Erstellung, Aktualisierung und Löschung, die über die Admin-API erfolgt, wird angegeben, wer sie durchgeführt hat, welches Token und was sich geändert hat.',
                'cleanup-btn' => 'Ältere Protokolle löschen',
                'cleanup-days' => 'Löschen Sie Protokolle, die älter als diese Anzahl von Tagen sind',
                'cleanup-confirm' => 'Sämtlichen Verlauf löschen, der älter als die angegebene Anzahl an Tagen ist? Dies kann nicht rückgängig gemacht werden.',
            ],

            'view' => [
                'title' => 'Veränderung',
                'back-btn' => 'Zurück',
                'admin' => 'Admin',
                'token' => 'Token',
                'action' => 'Aktion',
                'resource' => 'Ressource',
                'method' => 'Methode',
                'ip' => 'IP-Adresse',
                'date' => 'Datum',
                'version' => 'Version',
                'url' => 'Endpunkt',
                'request-details' => 'Details anfordern',
                'changes' => 'Änderungen',
                'field' => 'Feld',
                'old' => 'Alter Wert',
                'new' => 'Neuer Wert',
                'no-field-changes' => 'Für diesen Eintrag wurden keine Änderungen auf Feldebene aufgezeichnet.',
                'same-request' => 'Weitere Änderungen in derselben Anfrage',
                'version-chain' => 'Versionsverlauf dieses Datensatzes',
            ],

            'datagrid' => [
                'id' => 'Ausweis',
                'date' => 'Datum',
                'admin' => 'Admin',
                'token' => 'Token',
                'token-kind' => 'Token-Art',
                'channel' => 'Kanal',
                'operation' => 'Betrieb',
                'resource' => 'Ressource',
                'method' => 'Methode',
                'ip' => 'IP',
                'view' => 'Ansicht',
                'delete' => 'Löschen',
            ],

            'events' => [
                'created' => 'Erstellt',
                'updated' => 'Aktualisiert',
                'deleted' => 'Gelöscht',
            ],

            'token-kinds' => [
                'session' => 'Anmeldesitzung',
                'api-token' => 'API-Token',
                'integration' => 'Integrations-Token (entfernt)',
                'customer-session' => 'Kunden-Anmeldesitzung',
            ],

            'channels' => [
                'rest' => 'REST',
                'graphql' => 'GraphQL',
                'panel' => 'Admin-Bereich',
                'cli' => 'Konsolenbefehl',
            ],

            'deleted' => ':count Verlaufseintrag(e) gelöscht.',
            'cleanup-input-required' => 'Geben Sie eine Anzahl von Tagen oder ein Datum für die Reinigung an.',
        ],

        'api-configuration' => [
            'title' => 'API',
            'info' => 'Einstellungen für die Bagisto API und ihre Admin-Module.',
        ],
    ],
];
