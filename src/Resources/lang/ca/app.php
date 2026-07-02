<?php

return [
    'graphql' => [
        'cart' => [
            'authentication-required'           => 'Es requereix un testimoni d\'autenticació',
            'invalid-token'                     => 'Testimoni d\'autenticació no vàlid o caducat',
            'unauthorized-access'               => 'Accés no autoritzat al carretó',
            'authenticated-only'                => 'Només els usuaris autenticats poden obtenir els seus carretons',
            'merge-requires-auth'               => 'La fusió del carretó de convidat requereix autenticació',
            'unknown-operation'                 => 'Operació de carretó desconeguda',

            'cart-not-found'                    => 'Carretó no trobat',
            'guest-cart-not-found'              => 'Carretó de convidat no trobat',
            'product-not-found'                 => 'Producte no trobat',

            'product-id-quantity-required'      => 'Es requereixen l\'ID del producte i la quantitat',
            'cart-item-id-quantity-required'    => 'Es requereixen l\'ID de l\'element del carretó i la quantitat',
            'cart-item-id-required'             => 'Es requereix l\'ID de l\'element del carretó',
            'item-ids-required'                 => 'Es requereix una matriu d\'IDs d\'elements',
            'coupon-code-required'              => 'Es requereix el codi del cupó',
            'address-data-required'             => 'Es requereixen el país, l\'estat i el codi postal',

            'add-product-failed'                => 'No s\'ha pogut afegir el producte al carretó',
            'update-item-failed'                => 'No s\'ha pogut actualitzar l\'element del carretó',
            'remove-item-failed'                => 'No s\'ha pogut eliminar l\'element del carretó',
            'apply-coupon-failed'               => 'No s\'ha pogut aplicar el cupó',
            'remove-coupon-failed'              => 'No s\'ha pogut eliminar el cupó',
            'move-to-wishlist-failed'           => 'No s\'ha pogut moure l\'element a la llista de desitjos',
            'estimate-shipping-failed'          => 'No s\'ha pogut estimar l\'enviament',

            'product-added-successfully'         => 'Producte afegit al carretó correctament',
            'guest-cart-merged'                  => 'Carretó de convidat fusionat correctament',
            'using-authenticated-cart'           => 'S\'utilitza el carretó del client autenticat',
            'cart-item-not-found'                => 'Element del carretó no trobat',
            'new-guest-cart-created'             => 'S\'ha creat un carretó de convidat nou amb un testimoni de sessió únic',
            'select-items-to-remove'             => 'Si us plau, seleccioneu els elements que voleu eliminar',
            'select-items-to-move-wishlist'      => 'Si us plau, seleccioneu els elements que voleu moure a la llista de desitjos',
            'invalid-or-expired-token'           => 'El testimoni del carretó no és vàlid o ha caducat. Si us plau, creeu un carretó nou.',
            'invalid-token-of-login-user'        => 'El testimoni de l\'usuari connectat no és vàlid.',
        ],

        'token-verification' => [
            'invalid-operation'                 => 'Operació no vàlida',
            'invalid-input-data'                => 'Dades d\'entrada no vàlides',
            'token-required'                    => 'Es requereix el testimoni',
            'invalid-token-format'              => 'Format del testimoni no vàlid',
            'token-not-found-or-expired'        => 'Testimoni no trobat o caducat',
            'customer-not-found'                => 'Client no trobat',
            'customer-account-suspended'        => 'El compte del client està suspès',
            'error-verifying-token'             => 'Error en verificar el testimoni',
            'token-is-valid'                    => 'El testimoni és vàlid',
        ],

        'forgot-password' => [
            'invalid-operation'                 => 'Operació no vàlida',
            'invalid-input-data'                => 'Dades d\'entrada no vàlides',
            'email-required'                    => 'Es requereix el correu electrònic',
            'reset-link-sent'                   => 'Enllaç de restabliment enviat correctament al vostre correu electrònic',
            'email-not-found'                   => 'Adreça de correu electrònic no trobada',
            'error-sending-reset-link'          => 'S\'ha produït un error en enviar l\'enllaç de restabliment',
        ],

        'logout' => [
            'invalid-operation'                 => 'Operació no vàlida',
            'invalid-input-data'                => 'Dades d\'entrada no vàlides',
            'token-required'                    => 'Es requereix el testimoni',
            'invalid-token-format'              => 'Format del testimoni no vàlid',
            'logged-out-successfully'           => 'Sessió tancada correctament',
            'token-not-found-or-expired'        => 'Testimoni no trobat o ja caducat',
            'error-during-logout'               => 'Error en tancar la sessió',
        ],

        'address' => [
            'deleted-successfully'              => 'Adreça suprimida correctament',
            'authentication-required'           => 'Es requereix un testimoni d\'autenticació',
            'invalid-token'                     => 'Testimoni no vàlid o caducat',
            'unknown-operation'                 => 'Operació desconeguda',
            'address-id-required'               => 'Es requereix l\'ID de l\'adreça',
            'address-not-found'                 => 'Adreça no trobada o no pertany a aquest client',
            'retrieved'                         => 'Adreces obtingudes correctament',
            'fetch-failed'                      => 'No s\'han pogut obtenir les adreces:',
        ],

        'customer-profile' => [
            'authentication-required'           => 'Es requereix un testimoni d\'autenticació. Si us plau, proporcioneu el testimoni a l\'entrada de la consulta',
            'invalid-token'                     => 'Testimoni no vàlid o caducat',
        ],

        'customer' => [
            'password-mismatch'                 => 'La contrasenya i la confirmació de la contrasenya no coincideixen',
            'confirm-password-required'         => 'Es requereix la confirmació de la contrasenya quan es canvia la contrasenya',
            'unauthenticated'                   => 'No autenticat. Si us plau, inicieu la sessió per realitzar aquesta acció',
        ],

        'product-review' => [
            'product-id-required'               => 'Es requereix l\'ID del producte',
            'product-not-found'                 => 'Producte no trobat',
            'rating-invalid'                    => 'La valoració ha d\'estar entre 1 i 5',
            'title-required'                    => 'Es requereix el títol de la ressenya',
            'comment-required'                  => 'Es requereix el comentari de la ressenya',
        ],

        'product' => [
            'not-found-with-sku'                => 'No product found with SKU',
            'not-found-with-url-key'            => 'No product found with URL key',
            'parameters-required'               => 'At least one of the following parameters must be provided: "sku", "id", "urlKey"',
        ],

        'auth' => [
            'no-token-provided'                 => 'No s\'ha proporcionat cap testimoni d\'autenticació. Si us plau, proporcioneu el testimoni a la capçalera d\'autorització com a "Bearer <token>" o al camp input.token',
            'invalid-or-expired-token'          => 'Testimoni no vàlid o caducat',
            'request-not-found'                 => 'Sol·licitud no trobada al context',
            'token-required'                    => 'Authentication token is required. Please provide the token either in the GraphQL mutation input field or in the Authorization header as "Bearer <token>"',
            'unknown-resource'                  => 'Recurs desconegut',
            'cannot-update-other-profile'       => 'No autoritzat: No es pot actualitzar el perfil d\'un altre client',
        ],

        'upload' => [
            'invalid-base64'                    => 'Dades d\'imatge codificades en base64 no vàlides',
            'size-exceeds-limit'                => 'La mida de la imatge no ha de superar els 5MB',
            'invalid-format'                    => 'Format d\'imatge no vàlid. Si us plau, proporcioneu una imatge codificada en base64 amb l\'esquema URI de dades (data:image/jpeg;base64,...)',
            'failed'                            => 'Ha fallat la càrrega de la imatge',
        ],

        'attribute' => [
            'code-already-exists'               => 'El codi de l\'atribut ja existeix',
        ],

        'login' => [
            'invalid-credentials'               => 'Correu electrònic o contrasenya no vàlids',
            'account-suspended'                 => 'El vostre compte ha estat suspès',
            'successful'                        => 'Heu iniciat la sessió correctament',
            'invalid-request'                   => 'Sol·licitud d\'inici de sessió no vàlida',
        ],

        'checkout' => [
            'invalid-input'                     => 'Dades d\'entrada no vàlides per a l\'operació de compra',
            'billing-address-required'          => 'Es requereix l\'adreça de facturació',
            'shipping-address-required'         => 'Es requereix l\'adreça d\'enviament per als enviaments',
            'address-save-failed'               => 'No s\'ha pogut desar l\'adreça',
            'address-saved'                     => 'Adreça desada correctament',
            'shipping-method-required'          => 'Es requereix el mètode d\'enviament',
            'invalid-shipping-method'           => 'Mètode d\'enviament no vàlid o no disponible',
            'shipping-method-save-failed'       => 'No s\'ha pogut desar el mètode d\'enviament',
            'shipping-method-saved'             => 'Mètode d\'enviament desat correctament',
            'shipping-method-error'             => 'Error en desar el mètode d\'enviament',
            'payment-method-required'           => 'Es requereix el mètode de pagament',
            'invalid-payment-method'            => 'Mètode de pagament no vàlid o no disponible',
            'payment-method-save-failed'        => 'No s\'ha pogut desar el mètode de pagament',
            'payment-method-saved'              => 'Mètode de pagament desat correctament',
            'payment-method-error'              => 'Error en desar el mètode de pagament',
            'order-creation-failed'             => 'Ha fallat la creació de la comanda: l\'ID de la comanda és nul o la comanda no s\'ha desat',
            'order-retrieval-failed'            => 'No s\'ha pogut recuperar la comanda creada',
            'order-creation-error'              => 'No s\'ha pogut crear la comanda',
            'cart-empty'                        => 'El carretó està buit',
            'account-suspended'                 => 'El vostre compte ha estat suspès. Si us plau, contacteu amb el suport.',
            'account-inactive'                  => 'El vostre compte està inactiu. Si us plau, contacteu amb el suport.',
            'minimum-order-not-met'             => 'L\'import mínim de la comanda és :amount',
            'email-required'                    => 'Es requereix l\'adreça de correu electrònic per crear la comanda',
            'unknown-operation'                 => 'Operació de compra desconeguda',
        ],

        'customer-addresses' => [
            'token-required'                    => 'Es requereix el testimoni per obtenir les adreces del client',
            'invalid-or-expired-token'          => 'Testimoni no vàlid o caducat',
            'token-validation-failed'           => 'Ha fallat la validació del testimoni',
        ],

        'product' => [
            'type'                              => 'Tipus de producte',
            'attribute-family'                  => 'Família d\'atributs',
            'sku'                               => 'SKU',
            'name'                              => 'Nom',
            'description'                       => 'Descripció',
            'short-description'                 => 'Descripció curta',
            'status'                            => 'Estat',
            'new'                               => 'Nou',
            'featured'                          => 'Destacat',
            'price'                             => 'Preu',
            'special-price'                     => 'Preu especial',
            'weight'                            => 'Pes',
            'cost'                              => 'Cost',
            'length'                            => 'Longitud',
            'width'                             => 'Amplada',
            'height'                            => 'Alçada',
            'color'                             => 'Color',
            'size'                              => 'Mida',
            'brand'                             => 'Marca',
            'super-attributes'                  => 'Superatributs',
        ],

        'compare-item' => [
            'id-required'                       => 'L\'ID de l\'element de comparació és obligatori',
            'invalid-id-format'                 => 'Format d\'ID no vàlid. Es s\'espera el format IRI com a "/api/shop/compare-items/1" o ID numèric',
            'not-found'                         => 'Element de comparació no trobat',
            'product-id-required'               => 'L\'ID del producte és obligatori',
            'customer-id-required'              => 'L\'ID del client és obligatori',
            'product-not-found'                 => 'Producte no trobat',
            'customer-not-found'                => 'Client no trobat',
            'already-exists'                    => 'Aquest producte ja està a la vostra llista de comparació',
        ],

        'downloadable-product' => [
            'download-link-not-found'           => 'Enllaç de descàrrega no trobat o caducat',
            'purchased-link-not-found'          => 'Enllaç de compra no trobat',
            'file-not-found'                    => 'Fitxer no trobat',
            'download-successful'               => 'Fitxer llest per descarregar',
            'token-required'                    => 'Es requeix un token de descàrrega',
            'invalid-token'                     => 'Token de descàrrega no vàlid o caducat',
            'token-expired'                     => 'El token de descàrrega ha caducat. Si us plau, generi un de nou',
            'access-denied'                     => 'Accés denegat: No teniu permís per descarregar aquest fitxer',
            'redirect-external-url'             => 'Redirigint a URL de descàrrega externa',
            'file-error'                        => 'Ha ocorregut un error en processar la vostra sol·licitud de descàrrega',
            'unauthorized-access'               => 'Accés no autoritzat al recurs de descàrrega',
        ],
    ],

    'integration' => [
        'menu' => [
            'title'  => 'Integració',
            'tokens' => 'Fitxes',
        ],

        'history' => [
            'menu' => [
                'title' => 'Història',
            ],

            'acl' => [
                'title'  => 'Historial de canvis de l\'API',
                'delete' => 'Suprimeix l\'historial',
            ],

            'index' => [
                'title'           => 'Historial de canvis de l\'API',
                'info'            => 'Cada creació, actualització i supressió feta a través de l\'API d\'administració, amb qui ho va fer, quin testimoni i què ha canviat.',
                'cleanup-btn'     => 'Suprimeix els registres més antics',
                'cleanup-days'    => 'Suprimeix els registres més antics que aquests dies',
                'cleanup-confirm' => 'Vols suprimir tot l\'historial anterior al nombre de dies indicat? Això no es pot desfer.',
            ],

            'view' => [
                'title'             => 'Canviar',
                'back-btn'          => 'Enrere',
                'admin'             => 'Admin',
                'token'             => 'Token',
                'action'            => 'Acció',
                'resource'          => 'Recurs',
                'method'            => 'Mètode',
                'ip'                => 'Adreça IP',
                'date'              => 'Data',
                'version'           => 'Versió',
                'url'               => 'Punt final',
                'request-details'   => 'Detalls de la sol·licitud',
                'changes'           => 'Canvis',
                'field'             => 'Camp',
                'old'               => 'Antic valor',
                'new'               => 'Nou valor',
                'no-field-changes'  => 'No s\'han registrat canvis a nivell de camp per a aquesta entrada.',
                'same-request'      => 'Altres canvis en la mateixa sol·licitud',
                'version-chain'     => 'Historial de versions d\'aquest registre',
            ],

            'datagrid' => [
                'id'        => 'ID',
                'date'      => 'Data',
                'admin'     => 'Admin',
                'token'     => 'Token',
                'action'    => 'Acció',
                'operation' => 'Funcionament',
                'resource'  => 'Recurs',
                'version'   => 'Versió',
                'method'    => 'Mètode',
                'ip'        => 'IP',
                'view'      => 'Veure',
                'delete'    => 'Suprimeix',
            ],

            'events' => [
                'created' => 'Creat',
                'updated' => 'Actualitzat',
                'deleted' => 'S\'ha suprimit',
            ],

            'deleted'                => 'S\'han suprimit :count registres d\'historial.',
            'cleanup-input-required' => 'Proporcioneu un nombre de dies o una data per netejar.',
        ],

        'acl' => [
            'title'      => 'Integració',
            'create'     => 'Crea integració',
            'edit'       => 'Edita la integració',
            'delete'     => 'Revoca el testimoni d\'integració',
            'generate'   => 'Generar testimoni d\'integració',
            'regenerate' => 'Regenera el testimoni d\'integració',
        ],

        'index' => [
            'title'      => 'Integracions',
            'create-btn' => 'Crea integració',
        ],

        'create' => [
            'title'    => 'Crea integració',
            'save-btn' => 'Desa',
            'back-btn' => 'Enrere',
        ],

        'edit' => [
            'title'          => 'Edita la integració',
            'save-btn'       => 'Desa',
            'back-btn'       => 'Enrere',
            'generate-btn'   => 'Genera un testimoni',
            'regenerate-btn' => 'Regenera el testimoni',
            'revoke-btn'     => 'Revoca el testimoni',
            'copy-btn'       => 'Còpia',
            'token-warning'  => 'Desa aquest testimoni ara; no es tornarà a mostrar.',
            'token-label'    => 'Token',
            'not-generated'  => 'Encara no s\'ha generat',
            'masked'         => '(Es emmagatzema: només es mostra una vegada a la generació)',
            'history-banner' => 'Aquest testimoni ja no està actiu.',
        ],

        'fields' => [
            'name'                  => 'Nom',
            'description'           => 'Descripció',
            'assign-user'           => 'Assigna usuari',
            'permission-type'       => 'Tipus de permís',
            'access-control'        => 'Control d\'accés',
            'general'               => 'General',
            'token-settings'        => 'Configuració del testimoni',
            'valid-till'            => 'Vàlid fins a',
            'rate-limit-per-minute' => 'Límit de velocitat (per minut)',
            'rate-limit-per-day'    => 'Límit de tarifa (per dia)',
            'never-expires'         => 'Mai caduca',
            'expires-on'            => 'Caduca el',
            'unlimited'             => 'Il·limitat',
            'limit-to'              => 'Limitar a',
            'requests-per-minute'   => 'peticions / minut',
            'requests-per-day'      => 'sol·licituds/dia',
            'select-admin'          => 'Seleccioneu un administrador',
            'no-available-admins'   => 'No hi ha administradors disponibles: tots els administradors ja tenen un testimoni actiu.',
            'same-as-web-hint'      => 'El testimoni reflectirà en directe els permisos actuals del rol de l\'administrador assignat.',
            'ip-allowlist'          => 'Llista d\'IP permeses',
            'ip-any'                => 'Qualsevol IP (per defecte)',
            'ip-restricted'         => 'Restringit a IP específiques',
            'ip-list-hint'          => 'Una entrada per línia. Admet IPv4, IPv6 i CIDR (per exemple, 10.0.0.0/24 o 2001:db8::/32). Deixeu-lo en blanc per permetre totes les IP.',
        ],

        'permission_type' => [
            'all'         => 'Tots',
            'custom'      => 'Personalitzat',
            'same_as_web' => 'Igual que el permís web',
        ],

        'status' => [
            'draft'       => 'Esborrany',
            'active'      => 'Actius',
            'revoked'     => 'Revocat',
            'regenerated' => 'Regenerat',
        ],

        'datagrid' => [
            'id'              => 'ID',
            'name'            => 'Nom',
            'admin'           => 'Admin',
            'token'           => 'Token',
            'status'          => 'Estat',
            'permission-type' => 'Tipus de permís',
            'expires-at'      => 'Vàlid fins a',
            'last-used-at'    => 'Darrer ús',
            'created-at'      => 'Creat a',
            'edit'            => 'Edita',
            'revoke'          => 'Revocar',
        ],

        'messages' => [
            'draft-created'          => 'Integració creada. Genereu el testimoni per començar a utilitzar-lo.',
            'updated'                => 'La integració s\'ha actualitzat correctament.',
            'generated'              => 'Token generat. Copieu-lo ara; no es tornarà a mostrar.',
            'regenerated'            => 'Token regenerat. Copieu ara el testimoni nou; no es tornarà a mostrar.',
            'revoked'                => 'El testimoni s\'ha revocat correctament.',
            'generate-only-draft'    => 'Només es poden generar el seu testimoni d\'integracions esborranys.',
            'regenerate-only-active' => 'Només es poden regenerar fitxes actives.',
            'cannot-edit-historic'   => 'Les fitxes revocades o regenerades no es poden editar.',
            'already-inactive'       => 'Aquest testimoni ja està inactiu.',
        ],

        'errors' => [
            'admin-has-token' => 'L\'administrador seleccionat ja té un testimoni d\'integració actiu.',
        ],

        'validation' => [
            'ip-invalid'           => 'Cada IP permesa ha de ser una adreça IPv4 o IPv6 vàlida (admet la notació CIDR).',
            'cidr-prefix-invalid'  => 'El prefix CIDR no és vàlid per a la versió IP determinada.',
        ],

        'configuration' => [
            'api' => [
                'title' => 'API',
                'info'  => 'Configuració de l\'API Bagisto i els seus mòduls d\'administració.',
            ],
            'integration' => [
                'title' => 'Integració',
                'info'  => 'Gestioneu el connector d\'integració de l\'API utilitzat per emetre testimonis d\'API d\'administrador.',
            ],
            'settings' => [
                'title'  => 'Configuració del mòdul',
                'info'   => 'Activeu o desactiveu el connector d\'integració de l\'API. Quan està desactivat, el seu menú de la barra lateral s\'amaga i les seves pàgines tornen 404.',
                'enable' => 'Activa el mòdul d\'integració de l\'API',
            ],
        ],

        'emails' => [
            'generated' => [
                'subject'  => 'S\'ha generat un testimoni d\'API nou: :name',
                'greeting' => 'S\'acaba de generar un testimoni d\'integració de l\'API anomenat ":name" al vostre compte.',
            ],
            'regenerated' => [
                'subject'  => 'El vostre testimoni de l\'API s\'ha regenerat: :name',
                'greeting' => 'El testimoni d\'integració de l\'API anomenat ":name" s\'acaba de regenerar. El testimoni anterior ha deixat de funcionar; només el nou és vàlid.',
            ],
            'revoked' => [
                'subject'  => 'S\'ha revocat el vostre testimoni de l\'API: :name',
                'greeting' => 'El testimoni d\'integració de l\'API anomenat ":name" s\'ha revocat. Qualsevol client que l\'utilitzi ha perdut l\'accés.',
            ],

            'details' => [
                'name' => 'Nom del testimoni',
                'date' => 'Data',
                'ip'   => 'Des de la IP',
            ],

            'revoke-hint'   => 'Si no us esperàveu això, revoqueu el testimoni immediatament utilitzant el botó següent.',
            'revoke-btn'    => 'Revoca aquest testimoni',
            'revoke-expiry' => 'Aquest enllaç de revocació és vàlid durant 7 dies. Després d\'això, inicieu la sessió al tauler d\'administració per gestionar el testimoni.',
            'no-action'     => 'No cal cap acció: aquest correu electrònic només és una confirmació.',
        ],

        'revoke-confirmation' => [
            'title'                    => 'Revoca el testimoni de l\'API',
            'success-title'            => 'Token revocat',
            'success-message'          => 'El testimoni ":name" s\'ha revocat. Qualsevol client que l\'utilitzi ha perdut l\'accés immediatament.',
            'already-inactive-title'   => 'El testimoni ja està inactiu',
            'already-inactive-message' => 'El testimoni ":name" ja s\'ha revocat o regenerat. No cal més acció.',
        ],

        'confirm' => [
            'generate' => [
                'title'   => 'Genera un testimoni',
                'message' => 'Voleu generar el testimoni ara? El text sense format es mostrarà només una vegada; copieu-lo abans de sortir de la pàgina.',
            ],
            'regenerate' => [
                'title'   => 'Regenera el testimoni',
                'message' => 'Voleu regenerar el testimoni? El testimoni antic deixarà de funcionar immediatament i el nou text sense format només es mostrarà una vegada.',
            ],
            'revoke' => [
                'title'   => 'Revoca el testimoni',
                'message' => 'Vols revocar aquest testimoni? Qualsevol client que l\'utilitzi perdrà l\'accés immediatament. Aquesta acció no es pot desfer.',
            ],
        ],
    ],
];
