<?php

return [
    'graphql' => [
        'cart' => [
            'authentication-required'           => 'Authentifizierungstoken ist erforderlich',
            'invalid-token'                     => 'Ungültiges oder abgelaufenes Authentifizierungstoken',
            'unauthorized-access'               => 'Nicht autorisierter Zugriff auf den Warenkorb',
            'authenticated-only'                => 'Nur authentifizierte Benutzer können ihre Warenkörbe abrufen',
            'merge-requires-auth'               => 'Das Zusammenführen von Gastwarenkörben erfordert eine Authentifizierung',
            'unknown-operation'                 => 'Unbekannte Warenkorb-Operation',

            'cart-not-found'                    => 'Warenkorb nicht gefunden',
            'guest-cart-not-found'              => 'Gastwarenkorb nicht gefunden',
            'product-not-found'                 => 'Produkt nicht gefunden',

            'product-id-quantity-required'      => 'Produkt-ID und Menge sind erforderlich',
            'cart-item-id-quantity-required'    => 'Warenkorbartikel-ID und Menge sind erforderlich',
            'cart-item-id-required'             => 'Warenkorbartikel-ID ist erforderlich',
            'item-ids-required'                 => 'Ein Array von Artikel-IDs ist erforderlich',
            'coupon-code-required'              => 'Gutscheincode ist erforderlich',
            'address-data-required'             => 'Land, Bundesland und Postleitzahl sind erforderlich',

            'add-product-failed'                => 'Produkt konnte nicht zum Warenkorb hinzugefügt werden',
            'update-item-failed'                => 'Warenkorbartikel konnte nicht aktualisiert werden',
            'remove-item-failed'                => 'Warenkorbartikel konnte nicht entfernt werden',
            'apply-coupon-failed'               => 'Gutschein konnte nicht angewendet werden',
            'remove-coupon-failed'              => 'Gutschein konnte nicht entfernt werden',
            'move-to-wishlist-failed'           => 'Artikel konnte nicht auf die Wunschliste verschoben werden',
            'estimate-shipping-failed'          => 'Versandkosten konnten nicht geschätzt werden',

            'product-added-successfully'         => 'Produkt erfolgreich zum Warenkorb hinzugefügt',
            'guest-cart-merged'                  => 'Gastwarenkorb erfolgreich zusammengeführt',
            'using-authenticated-cart'           => 'Authentifizierter Kundenwarenkorb wird verwendet',
            'cart-item-not-found'                => 'Warenkorbartikel nicht gefunden',
            'new-guest-cart-created'             => 'Neuer Gastwarenkorb mit eindeutigem Sitzungstoken erstellt',
            'select-items-to-remove'             => 'Bitte wählen Sie die zu entfernenden Artikel aus',
            'select-items-to-move-wishlist'      => 'Bitte wählen Sie die Artikel aus, die auf die Wunschliste verschoben werden sollen',
            'invalid-or-expired-token'           => 'Das Warenkorb-Token ist ungültig oder abgelaufen. Bitte erstellen Sie einen neuen Warenkorb.',
            'invalid-token-of-login-user'        => 'Das Token des angemeldeten Benutzers ist ungültig.',
        ],

        'token-verification' => [
            'invalid-operation'                 => 'Ungültige Operation',
            'invalid-input-data'                => 'Ungültige Eingabedaten',
            'token-required'                    => 'Token ist erforderlich',
            'invalid-token-format'              => 'Ungültiges Token-Format',
            'token-not-found-or-expired'        => 'Token nicht gefunden oder abgelaufen',
            'customer-not-found'                => 'Kunde nicht gefunden',
            'customer-account-suspended'        => 'Das Kundenkonto ist gesperrt',
            'error-verifying-token'             => 'Fehler bei der Überprüfung des Tokens',
            'token-is-valid'                    => 'Token ist gültig',
        ],

        'forgot-password' => [
            'invalid-operation'                 => 'Ungültige Operation',
            'invalid-input-data'                => 'Ungültige Eingabedaten',
            'email-required'                    => 'E-Mail ist erforderlich',
            'reset-link-sent'                   => 'Der Link zum Zurücksetzen wurde erfolgreich an Ihre E-Mail-Adresse gesendet',
            'email-not-found'                   => 'E-Mail-Adresse nicht gefunden',
            'error-sending-reset-link'          => 'Beim Senden des Links zum Zurücksetzen ist ein Fehler aufgetreten',
        ],

        'logout' => [
            'invalid-operation'                 => 'Ungültige Operation',
            'invalid-input-data'                => 'Ungültige Eingabedaten',
            'token-required'                    => 'Token ist erforderlich',
            'invalid-token-format'              => 'Ungültiges Token-Format',
            'logged-out-successfully'           => 'Erfolgreich abgemeldet',
            'token-not-found-or-expired'        => 'Token nicht gefunden oder bereits abgelaufen',
            'error-during-logout'               => 'Fehler beim Abmelden',
        ],

        'address' => [
            'deleted-successfully'              => 'Adresse erfolgreich gelöscht',
            'authentication-required'           => 'Authentifizierungstoken ist erforderlich',
            'invalid-token'                     => 'Ungültiges oder abgelaufenes Token',
            'unknown-operation'                 => 'Unbekannte Operation',
            'address-id-required'               => 'Adress-ID ist erforderlich',
            'address-not-found'                 => 'Adresse nicht gefunden oder gehört nicht zu diesem Kunden',
            'retrieved'                         => 'Adressen erfolgreich abgerufen',
            'fetch-failed'                      => 'Adressen konnten nicht abgerufen werden:',
        ],

        'customer-profile' => [
            'authentication-required'           => 'Authentifizierungstoken ist erforderlich. Bitte geben Sie das Token in der Abfrageeingabe an',
            'invalid-token'                     => 'Ungültiges oder abgelaufenes Token',
        ],

        'customer' => [
            'password-mismatch'                 => 'Passwort und Passwortbestätigung stimmen nicht überein',
            'confirm-password-required'         => 'Bei einer Passwortänderung ist die Passwortbestätigung erforderlich',
            'unauthenticated'                   => 'Nicht authentifiziert. Bitte melden Sie sich an, um diese Aktion auszuführen',
        ],

        'product-review' => [
            'product-id-required'               => 'Produkt-ID ist erforderlich',
            'product-not-found'                 => 'Produkt nicht gefunden',
            'rating-invalid'                    => 'Die Bewertung muss zwischen 1 und 5 liegen',
            'title-required'                    => 'Der Titel der Bewertung ist erforderlich',
            'comment-required'                  => 'Der Kommentar zur Bewertung ist erforderlich',
        ],

        'product' => [
            'not-found-with-sku'                => 'No product found with SKU',
            'not-found-with-url-key'            => 'No product found with URL key',
            'parameters-required'               => 'At least one of the following parameters must be provided: "sku", "id", "urlKey"',
        ],

        'auth' => [
            'no-token-provided'                 => 'Kein Authentifizierungstoken angegeben. Bitte geben Sie das Token im Authorization-Header als "Bearer <token>" oder im Feld input.token an',
            'invalid-or-expired-token'          => 'Ungültiges oder abgelaufenes Token',
            'request-not-found'                 => 'Anfrage im Kontext nicht gefunden',
            'token-required'                    => 'Authentication token is required. Please provide the token either in the GraphQL mutation input field or in the Authorization header as "Bearer <token>"',
            'unknown-resource'                  => 'Unbekannte Ressource',
            'cannot-update-other-profile'       => 'Nicht autorisiert: Das Profil eines anderen Kunden kann nicht aktualisiert werden',
        ],

        'upload' => [
            'invalid-base64'                    => 'Ungültige base64-kodierte Bilddaten',
            'size-exceeds-limit'                => 'Die Bildgröße darf 5 MB nicht überschreiten',
            'invalid-format'                    => 'Ungültiges Bildformat. Bitte geben Sie ein base64-kodiertes Bild mit Daten-URI-Schema an (data:image/jpeg;base64,...)',
            'failed'                            => 'Bild-Upload fehlgeschlagen',
        ],

        'attribute' => [
            'code-already-exists'               => 'Der Attributcode existiert bereits',
        ],

        'login' => [
            'invalid-credentials'               => 'Ungültige E-Mail-Adresse oder Passwort',
            'account-suspended'                 => 'Ihr Konto wurde gesperrt',
            'successful'                        => 'Sie haben sich erfolgreich angemeldet',
            'invalid-request'                   => 'Ungültige Anmeldeanfrage',
        ],

        'checkout' => [
            'invalid-input'                     => 'Ungültige Eingabedaten für den Checkout-Vorgang',
            'billing-address-required'          => 'Die Rechnungsadresse ist erforderlich',
            'shipping-address-required'         => 'Für Sendungen ist eine Lieferadresse erforderlich',
            'address-save-failed'               => 'Adresse konnte nicht gespeichert werden',
            'address-saved'                     => 'Adresse erfolgreich gespeichert',
            'shipping-method-required'          => 'Die Versandart ist erforderlich',
            'invalid-shipping-method'           => 'Ungültige oder nicht verfügbare Versandart',
            'shipping-method-save-failed'       => 'Versandart konnte nicht gespeichert werden',
            'shipping-method-saved'             => 'Versandart erfolgreich gespeichert',
            'shipping-method-error'             => 'Fehler beim Speichern der Versandart',
            'payment-method-required'           => 'Die Zahlungsart ist erforderlich',
            'invalid-payment-method'            => 'Ungültige oder nicht verfügbare Zahlungsart',
            'payment-method-save-failed'        => 'Zahlungsart konnte nicht gespeichert werden',
            'payment-method-saved'              => 'Zahlungsart erfolgreich gespeichert',
            'payment-method-error'              => 'Fehler beim Speichern der Zahlungsart',
            'order-creation-failed'             => 'Bestellungserstellung fehlgeschlagen: Die Bestell-ID ist null oder die Bestellung wurde nicht gespeichert',
            'order-retrieval-failed'            => 'Die erstellte Bestellung konnte nicht abgerufen werden',
            'order-creation-error'              => 'Bestellung konnte nicht erstellt werden',
            'cart-empty'                        => 'Der Warenkorb ist leer',
            'account-suspended'                 => 'Ihr Konto wurde gesperrt. Bitte wenden Sie sich an den Support.',
            'account-inactive'                  => 'Ihr Konto ist inaktiv. Bitte wenden Sie sich an den Support.',
            'minimum-order-not-met'             => 'Der Mindestbestellwert beträgt :amount',
            'email-required'                    => 'Für die Bestellungserstellung ist eine E-Mail-Adresse erforderlich',
            'unknown-operation'                 => 'Unbekannte Checkout-Operation',
        ],

        'customer-addresses' => [
            'token-required'                    => 'Zum Abrufen der Kundenadressen ist ein Token erforderlich',
            'invalid-or-expired-token'          => 'Ungültiges oder abgelaufenes Token',
            'token-validation-failed'           => 'Token-Validierung fehlgeschlagen',
        ],

        'product' => [
            'type'                              => 'Produkttyp',
            'attribute-family'                  => 'Attributfamilie',
            'sku'                               => 'SKU',
            'name'                              => 'Name',
            'description'                       => 'Beschreibung',
            'short-description'                 => 'Kurzbeschreibung',
            'status'                            => 'Status',
            'new'                               => 'Neu',
            'featured'                          => 'Hervorgehoben',
            'price'                             => 'Preis',
            'special-price'                     => 'Sonderpreis',
            'weight'                            => 'Gewicht',
            'cost'                              => 'Kosten',
            'length'                            => 'Länge',
            'width'                             => 'Breite',
            'height'                            => 'Höhe',
            'color'                             => 'Farbe',
            'size'                              => 'Größe',
            'brand'                             => 'Marke',
            'super-attributes'                  => 'Superattribute',
        ],

        'compare-item' => [
            'id-required'                       => 'Vergleichselements-ID ist erforderlich',
            'invalid-id-format'                 => 'Ungültiges ID-Format. Erwartet IRI-Format wie "/api/shop/compare-items/1" oder numerische ID',
            'not-found'                         => 'Vergleichselement nicht gefunden',
            'product-id-required'               => 'Produkt-ID ist erforderlich',
            'customer-id-required'              => 'Kunden-ID ist erforderlich',
            'product-not-found'                 => 'Produkt nicht gefunden',
            'customer-not-found'                => 'Kunde nicht gefunden',
            'already-exists'                    => 'Dieses Produkt befindet sich bereits in Ihrer Vergleichsliste',
        ],

        'downloadable-product' => [
            'download-link-not-found'           => 'Download-Link nicht gefunden oder abgelaufen',
            'purchased-link-not-found'          => 'Gekaufter Link nicht gefunden',
            'file-not-found'                    => 'Datei nicht gefunden',
            'download-successful'               => 'Datei bereit zum Download',
            'token-required'                    => 'Download-Token erforderlich',
            'invalid-token'                     => 'Download-Token ungültig oder abgelaufen',
            'token-expired'                     => 'Das Download-Token ist abgelaufen. Bitte generieren Sie ein neues',
            'access-denied'                     => 'Zugriff verweigert: Sie haben keine Berechtigung, diese Datei herunterzuladen',
            'redirect-external-url'             => 'Weiterleitung zur externen Download-URL',
            'file-error'                        => 'Ein Fehler ist bei der Verarbeitung Ihrer Download-Anfrage aufgetreten',
            'unauthorized-access'               => 'Nicht autorisierter Zugriff auf Download-Ressource',
        ],
    ],

    'integration' => [
        'menu' => [
            'title'  => 'Integration',
            'tokens' => 'Token',
        ],

        'history' => [
            'menu' => [
                'title' => 'Geschichte',
            ],

            'acl' => [
                'title'  => 'API-Änderungsverlauf',
                'delete' => 'Verlauf löschen',
            ],

            'index' => [
                'title'           => 'API-Änderungsverlauf',
                'info'            => 'Bei jeder Erstellung, Aktualisierung und Löschung, die über die Admin-API erfolgt, wird angegeben, wer sie durchgeführt hat, welches Token und was sich geändert hat.',
                'cleanup-btn'     => 'Ältere Protokolle löschen',
                'cleanup-days'    => 'Löschen Sie Protokolle, die älter als diese Anzahl von Tagen sind',
                'cleanup-confirm' => 'Sämtlichen Verlauf löschen, der älter als die angegebene Anzahl an Tagen ist? Dies kann nicht rückgängig gemacht werden.',
            ],

            'view' => [
                'title'             => 'Veränderung',
                'back-btn'          => 'Zurück',
                'admin'             => 'Admin',
                'token'             => 'Token',
                'action'            => 'Aktion',
                'resource'          => 'Ressource',
                'method'            => 'Methode',
                'ip'                => 'IP-Adresse',
                'date'              => 'Datum',
                'version'           => 'Version',
                'url'               => 'Endpunkt',
                'request-details'   => 'Details anfordern',
                'changes'           => 'Änderungen',
                'field'             => 'Feld',
                'old'               => 'Alter Wert',
                'new'               => 'Neuer Wert',
                'no-field-changes'  => 'Für diesen Eintrag wurden keine Änderungen auf Feldebene aufgezeichnet.',
                'same-request'      => 'Weitere Änderungen in derselben Anfrage',
                'version-chain'     => 'Versionsverlauf dieses Datensatzes',
            ],

            'datagrid' => [
                'id'        => 'Ausweis',
                'date'      => 'Datum',
                'admin'     => 'Admin',
                'token'     => 'Token',
                'action'    => 'Aktion',
                'operation' => 'Betrieb',
                'resource'  => 'Ressource',
                'version'   => 'Version',
                'method'    => 'Methode',
                'ip'        => 'IP',
                'view'      => 'Ansicht',
                'delete'    => 'Löschen',
            ],

            'events' => [
                'created' => 'Erstellt',
                'updated' => 'Aktualisiert',
                'deleted' => 'Gelöscht',
            ],

            'deleted'                => ':count Verlaufseintrag(e) gelöscht.',
            'cleanup-input-required' => 'Geben Sie eine Anzahl von Tagen oder ein Datum für die Reinigung an.',
        ],

        'acl' => [
            'title'      => 'Integration',
            'create'     => 'Integration schaffen',
            'edit'       => 'Integration bearbeiten',
            'delete'     => 'Integrationstoken widerrufen',
            'generate'   => 'Integrationstoken generieren',
            'regenerate' => 'Integrationstoken neu generieren',
        ],

        'index' => [
            'title'      => 'Integrationen',
            'create-btn' => 'Integration schaffen',
        ],

        'create' => [
            'title'    => 'Integration schaffen',
            'save-btn' => 'Speichern',
            'back-btn' => 'Zurück',
        ],

        'edit' => [
            'title'          => 'Integration bearbeiten',
            'save-btn'       => 'Speichern',
            'back-btn'       => 'Zurück',
            'generate-btn'   => 'Token generieren',
            'regenerate-btn' => 'Token neu generieren',
            'revoke-btn'     => 'Token widerrufen',
            'copy-btn'       => 'Kopieren',
            'token-warning'  => 'Speichern Sie dieses Token jetzt – es wird nicht mehr angezeigt.',
            'token-label'    => 'Token',
            'not-generated'  => 'Noch nicht generiert',
            'masked'         => '(Gespeichert – wird bei der Generierung nur einmal angezeigt)',
            'history-banner' => 'Dieses Token ist nicht mehr aktiv.',
        ],

        'fields' => [
            'name'                  => 'Name',
            'description'           => 'Beschreibung',
            'assign-user'           => 'Benutzer zuweisen',
            'permission-type'       => 'Berechtigungstyp',
            'access-control'        => 'Zugangskontrolle',
            'general'               => 'Allgemein',
            'token-settings'        => 'Token-Einstellungen',
            'valid-till'            => 'Gültig bis',
            'rate-limit-per-minute' => 'Ratenlimit (pro Minute)',
            'rate-limit-per-day'    => 'Ratenlimit (pro Tag)',
            'never-expires'         => 'Läuft nie ab',
            'expires-on'            => 'Läuft ab am',
            'unlimited'             => 'Unbegrenzt',
            'limit-to'              => 'Beschränken Sie sich auf',
            'requests-per-minute'   => 'Anfragen / Minute',
            'requests-per-day'      => 'Anfragen / Tag',
            'select-admin'          => 'Wählen Sie einen Administrator aus',
            'no-available-admins'   => 'Keine Administratoren verfügbar – jeder Administrator hat bereits ein aktives Token.',
            'same-as-web-hint'      => 'Das Token spiegelt die aktuellen Rollenberechtigungen des zugewiesenen Administrators live wider.',
            'ip-allowlist'          => 'IP-Zulassungsliste',
            'ip-any'                => 'Beliebige IP (Standard)',
            'ip-restricted'         => 'Auf bestimmte IPs beschränkt',
            'ip-list-hint'          => 'Ein Eintrag pro Zeile. Unterstützt IPv4, IPv6 und CIDR (z. B. 10.0.0.0/24 oder 2001:db8::/32). Lassen Sie das Feld leer, um alle IPs zuzulassen.',
        ],

        'permission_type' => [
            'all'         => 'Alle',
            'custom'      => 'Benutzerdefiniert',
            'same_as_web' => 'Identisch mit Web-Berechtigung',
        ],

        'status' => [
            'draft'       => 'Entwurf',
            'active'      => 'Aktiv',
            'revoked'     => 'Widerrufen',
            'regenerated' => 'Regeneriert',
        ],

        'datagrid' => [
            'id'              => 'Ausweis',
            'name'            => 'Name',
            'admin'           => 'Admin',
            'token'           => 'Token',
            'status'          => 'Status',
            'permission-type' => 'Berechtigungstyp',
            'expires-at'      => 'Gültig bis',
            'last-used-at'    => 'Zuletzt verwendet',
            'created-at'      => 'Erstellt am',
            'edit'            => 'Bearbeiten',
            'revoke'          => 'Widerrufen',
        ],

        'messages' => [
            'draft-created'          => 'Integration geschaffen. Generieren Sie das Token, um es zu verwenden.',
            'updated'                => 'Integration erfolgreich aktualisiert.',
            'generated'              => 'Token generiert. Kopieren Sie es jetzt – es wird nicht mehr angezeigt.',
            'regenerated'            => 'Token regeneriert. Kopieren Sie jetzt das neue Token – es wird nicht mehr angezeigt.',
            'revoked'                => 'Token erfolgreich widerrufen.',
            'generate-only-draft'    => 'Nur Entwurfsintegrationen können ihr Token generieren lassen.',
            'regenerate-only-active' => 'Es können nur aktive Token regeneriert werden.',
            'cannot-edit-historic'   => 'Widerrufene oder neu generierte Token können nicht bearbeitet werden.',
            'already-inactive'       => 'Dieses Token ist bereits inaktiv.',
        ],

        'errors' => [
            'admin-has-token' => 'Der ausgewählte Administrator verfügt bereits über ein aktives Integrationstoken.',
        ],

        'validation' => [
            'ip-invalid'           => 'Jede zulässige IP muss eine gültige IPv4- oder IPv6-Adresse sein (CIDR-Notation wird unterstützt).',
            'cidr-prefix-invalid'  => 'Das CIDR-Präfix ist für die angegebene IP-Version ungültig.',
        ],

        'configuration' => [
            'api' => [
                'title' => 'API',
                'info'  => 'Einstellungen für die Bagisto API und ihre Admin-Module.',
            ],
            'integration' => [
                'title' => 'Integration',
                'info'  => 'Verwalten Sie das API-Integrations-Plugin, das zum Ausstellen von Admin-API-Tokens verwendet wird.',
            ],
            'settings' => [
                'title'  => 'Moduleinstellungen',
                'info'   => 'Aktivieren oder deaktivieren Sie das API-Integrations-Plugin. Wenn es deaktiviert ist, wird das Seitenleistenmenü ausgeblendet und die Seiten geben 404 zurück.',
                'enable' => 'Aktivieren Sie das API-Integrationsmodul',
            ],
        ],

        'emails' => [
            'generated' => [
                'subject'  => 'Ein neues API-Token wurde generiert: :name',
                'greeting' => 'Für Ihr Konto wurde gerade ein API-Integrationstoken mit dem Namen „:name“ generiert.',
            ],
            'regenerated' => [
                'subject'  => 'Ihr API-Token wurde neu generiert: :name',
                'greeting' => 'Das API-Integrationstoken mit dem Namen „:name“ wurde gerade neu generiert. Der vorherige Token funktioniert nicht mehr – nur der neue ist gültig.',
            ],
            'revoked' => [
                'subject'  => 'Ihr API-Token wurde widerrufen: :name',
                'greeting' => 'Das API-Integrationstoken mit dem Namen „:name“ wurde widerrufen. Jeder Client, der es verwendet, hat den Zugriff verloren.',
            ],

            'details' => [
                'name' => 'Tokenname',
                'date' => 'Datum',
                'ip'   => 'Von IP',
            ],

            'revoke-hint'   => 'Wenn Sie damit nicht gerechnet haben, widerrufen Sie den Token umgehend über die Schaltfläche unten.',
            'revoke-btn'    => 'Dieses Token widerrufen',
            'revoke-expiry' => 'Dieser Widerrufslink ist 7 Tage lang gültig. Melden Sie sich anschließend im Admin-Bereich an, um das Token zu verwalten.',
            'no-action'     => 'Es sind keine Maßnahmen erforderlich – diese E-Mail ist lediglich eine Bestätigung.',
        ],

        'revoke-confirmation' => [
            'title'                    => 'API-Token widerrufen',
            'success-title'            => 'Token widerrufen',
            'success-message'          => 'Das Token „:name“ wurde widerrufen. Jeder Client, der es nutzt, hat sofort den Zugriff verloren.',
            'already-inactive-title'   => 'Token bereits inaktiv',
            'already-inactive-message' => 'Das Token „:name“ wurde bereits widerrufen oder neu generiert. Es sind keine weiteren Maßnahmen erforderlich.',
        ],

        'confirm' => [
            'generate' => [
                'title'   => 'Token generieren',
                'message' => 'Jetzt den Token generieren? Der Klartext wird nur einmal angezeigt – kopieren Sie ihn, bevor Sie die Seite verlassen.',
            ],
            'regenerate' => [
                'title'   => 'Token neu generieren',
                'message' => 'Token neu generieren? Das alte Token funktioniert sofort nicht mehr und der neue Klartext wird nur einmal angezeigt.',
            ],
            'revoke' => [
                'title'   => 'Token widerrufen',
                'message' => 'Dieses Token widerrufen? Jeder Client, der es nutzt, verliert sofort den Zugriff. Diese Aktion kann nicht rückgängig gemacht werden.',
            ],
        ],
    ],
];
