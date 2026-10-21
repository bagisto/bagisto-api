<?php

return [

    'graphql' => [
        'headers' => [
            'unknown-channel' => 'El canal ":code" de la capçalera X-Channel no existeix.',
            'storefront-key-required' => 'Cal la capçalera X-STOREFRONT-KEY',
            'storefront-key-invalid' => 'Clau de botiga no vàlida',
            'storefront-key-rate-limited' => 'S\'ha superat el límit de sol·licituds. Torneu-ho a provar d\'aquí a :seconds segons',
        ],

        'cart' => [
            'authentication-required' => 'Es requereix un testimoni d\'autenticació',
            'invalid-token' => 'Testimoni d\'autenticació no vàlid o caducat',
            'unauthorized-access' => 'Accés no autoritzat al carretó',
            'authenticated-only' => 'Només els usuaris autenticats poden obtenir els seus carretons',
            'merge-requires-auth' => 'La fusió del carretó de convidat requereix autenticació',
            'unknown-operation' => 'Operació de carretó desconeguda',

            'cart-not-found' => 'Carretó no trobat',
            'guest-cart-not-found' => 'Carretó de convidat no trobat',
            'product-not-found' => 'Producte no trobat',

            'product-id-quantity-required' => 'Es requereixen l\'ID del producte i la quantitat',
            'cart-item-id-quantity-required' => 'Es requereixen l\'ID de l\'element del carretó i la quantitat',

            'file-token-invalid' => 'La referència del fitxer carregat no és vàlida o ha caducat. Torneu a carregar el fitxer.',
            'file-token-forbidden' => 'Aquest fitxer carregat no pertany al vostre carretó.',
            'customizable-option-required' => 'L\'opció personalitzable ":label" és obligatòria.',
            'customizable-file-option-invalid' => 'L\'opció seleccionada no és una opció de fitxer per a aquest producte.',
            'customizable-file-invalid' => 'Falta el fitxer, té una extensió no admesa o és massa gran.',
            'cart-item-id-required' => 'Es requereix l\'ID de l\'element del carretó',
            'item-ids-required' => 'Es requereix una matriu d\'IDs d\'elements',

            'event-booking-quantity-not-changeable' => 'No es pot canviar la quantitat d\'un producte de reserva d\'esdeveniment. La quantitat la determina la selecció d\'entrades.',
            'appointment-booking-quantity-not-changeable' => 'No es pot canviar la quantitat d\'un producte de reserva de cita.',
            'coupon-code-required' => 'Es requereix el codi del cupó',
            'address-data-required' => 'Es requereixen el país, l\'estat i el codi postal',
            'grouped-qty-required' => 'El producte agrupat requereix les quantitats seleccionades. Passeu groupedQty com a cadena JSON, p. ex. {"101":2,"102":1}.',
            'grouped-qty-must-include-all' => 'El producte agrupat requereix quantitats per a tots els productes associats. IDs que falten: :ids.',
            'grouped-qty-invalid-associated' => 'Les quantitats del producte agrupat contenen IDs de productes associats no vàlids: :ids.',
            'grouped-qty-invalid-quantity' => 'S\'ha proporcionat una quantitat no vàlida per a l\'ID de producte associat :id. La quantitat ha de ser un enter no negatiu.',
            'bundle-qty-not-changeable' => 'No es pot canviar la quantitat de l\'opció del paquet ":option". La quantitat permesa és :qty.',

            'add-product-failed' => 'No s\'ha pogut afegir el producte al carretó',
            'update-item-failed' => 'No s\'ha pogut actualitzar l\'element del carretó',
            'remove-item-failed' => 'No s\'ha pogut eliminar l\'element del carretó',
            'apply-coupon-failed' => 'No s\'ha pogut aplicar el cupó',
            'remove-coupon-failed' => 'No s\'ha pogut eliminar el cupó',
            'coupon-applied' => 'Cupó aplicat correctament',
            'coupon-removed' => 'Cupó eliminat correctament',
            'move-to-wishlist-failed' => 'No s\'ha pogut moure l\'element a la llista de desitjos',
            'estimate-shipping-failed' => 'No s\'ha pogut estimar l\'enviament',

            'product-added-successfully' => 'Producte afegit al carretó correctament',
            'cart-item-updated-successfully' => 'Element del carretó actualitzat correctament',
            'guest-cart-merged' => 'Carretó de convidat fusionat correctament',
            'using-authenticated-cart' => 'S\'utilitza el carretó del client autenticat',
            'cart-item-not-found' => 'Element del carretó no trobat',
            'new-guest-cart-created' => 'S\'ha creat un carretó de convidat nou amb un testimoni de sessió únic',
            'select-items-to-remove' => 'Si us plau, seleccioneu els elements que voleu eliminar',
            'select-items-to-move-wishlist' => 'Si us plau, seleccioneu els elements que voleu moure a la llista de desitjos',
            'invalid-or-expired-token' => 'El testimoni del carretó no és vàlid o ha caducat. Si us plau, creeu un carretó nou.',
            'invalid-token-of-login-user' => 'El testimoni de l\'usuari connectat no és vàlid.',
            'booking-note-required' => 'booking.note és obligatori per a la reserva de taula.',
            'booking-date-required' => 'booking.date és obligatori quan booking.slot és un interval horari amb format.',
            'booking-qty-required' => 'booking.qty és obligatori per a la reserva d\'esdeveniment.',
            'booking-ticket-qty-invalid' => 'La quantitat d\'entrades de l\'esdeveniment ha de ser un nombre no negatiu.',
            'booking-ticket-required' => 'Seleccioneu almenys una quantitat d\'entrades per a la reserva de l\'esdeveniment.',
            'booking-slot-unavailable' => 'La franja seleccionada no està disponible per a la data indicada.',
            'invalid-quantity' => 'La quantitat ha de ser com a mínim 1',
            'product-id-required' => 'Cal l\'ID del producte',
            'shipping-address-required' => 'Cal el país i el codi postal per estimar l\'enviament',
        ],

        'token-verification' => [
            'invalid-operation' => 'Operació no vàlida',
            'invalid-input-data' => 'Dades d\'entrada no vàlides',
            'token-required' => 'Es requereix el testimoni',
            'invalid-token-format' => 'Format del testimoni no vàlid',
            'token-not-found-or-expired' => 'Testimoni no trobat o caducat',
            'customer-not-found' => 'Client no trobat',
            'customer-account-suspended' => 'El compte del client està suspès',
            'error-verifying-token' => 'Error en verificar el testimoni',
            'token-is-valid' => 'El testimoni és vàlid',
            'unauthenticated' => 'No autenticat. Si us plau, inicieu la sessió per realitzar aquesta acció',
        ],

        'forgot-password' => [
            'invalid-operation' => 'Operació no vàlida',
            'invalid-input-data' => 'Dades d\'entrada no vàlides',
            'email-required' => 'Es requereix el correu electrònic',
            'reset-link-sent' => 'Enllaç de restabliment enviat correctament al vostre correu electrònic',
            'email-not-found' => 'Adreça de correu electrònic no trobada',
            'throttled' => 'Massa intents de restabliment. Espereu abans de tornar-ho a provar.',
            'error-sending-reset-link' => 'S\'ha produït un error en enviar l\'enllaç de restabliment',
        ],

        'reset-password' => [
            'failed' => 'No s\'ha pogut restablir la contrasenya. Torneu-ho a provar.',
        ],

        'rate-limit' => [
            'too-many-attempts' => 'Massa intents. Torneu-ho a provar d\'aquí a :seconds segons.',
        ],

        'logout' => [
            'invalid-operation' => 'Operació no vàlida',
            'invalid-input-data' => 'Dades d\'entrada no vàlides',
            'token-required' => 'Es requereix el testimoni',
            'invalid-token-format' => 'Format del testimoni no vàlid',
            'logged-out-successfully' => 'Sessió tancada correctament',
            'token-not-found-or-expired' => 'Testimoni no trobat o ja caducat',
            'error-during-logout' => 'Error en tancar la sessió',
            'unauthenticated' => 'No autenticat. Si us plau, inicieu la sessió per realitzar aquesta acció',
        ],

        'address' => [
            'deleted-successfully' => 'Adreça suprimida correctament',
            'authentication-required' => 'Es requereix un testimoni d\'autenticació',
            'invalid-token' => 'Testimoni no vàlid o caducat',
            'unknown-operation' => 'Operació desconeguda',
            'address-id-required' => 'Es requereix l\'ID de l\'adreça',
            'address-not-found' => 'Adreça no trobada o no pertany a aquest client',
            'retrieved' => 'Adreces obtingudes correctament',
            'fetch-failed' => 'No s\'han pogut obtenir les adreces:',
            'field-required' => 'El camp :field és obligatori',
        ],

        'customer-profile' => [
            'authentication-required' => 'Es requereix un testimoni d\'autenticació. Si us plau, proporcioneu el testimoni a l\'entrada de la consulta',
            'invalid-token' => 'Testimoni no vàlid o caducat',
            'profile-updated' => 'Perfil del client actualitzat correctament',
        ],

        'customer' => [
            'password-mismatch' => 'La contrasenya i la confirmació de la contrasenya no coincideixen',
            'confirm-password-required' => 'Es requereix la confirmació de la contrasenya quan es canvia la contrasenya',
            'current-password-required' => 'Cal la contrasenya actual per canviar la contrasenya',
            'current-password-incorrect' => 'La contrasenya actual que heu introduït és incorrecta',
            'verify-email-to-login' => 'S\'ha creat el vostre compte. Verifiqueu la vostra adreça de correu electrònic per iniciar la sessió.',
            'delete-password-required' => 'Cal la vostra contrasenya per suprimir el compte',
            'delete-password-incorrect' => 'La contrasenya que heu introduït és incorrecta',
            'delete-has-active-orders' => 'No es pot suprimir el vostre compte mentre tingueu comandes pendents o en procés',
            'unauthenticated' => 'No autenticat. Si us plau, inicieu la sessió per realitzar aquesta acció',
            'id-required' => 'Cal l\'ID del client',
            'invalid-id-format' => 'Format d\'ID no vàlid. S\'espera el format IRI com "/api/admin/customers/1" o un ID numèric',
            'not-found' => 'Client no trobat',
            'phone-special-chars-not-allowed' => 'El número de mòbil només pot contenir dígits. No es permeten caràcters especials',
            'email-already-taken' => 'Aquesta adreça de correu electrònic ja està registrada en un altre compte',
            'invalid-gender' => 'Valor de gènere no vàlid ":gender". Els valors permesos són: :valid',
        ],

        'product-review' => [
            'id-required' => 'Cal l\'ID de la ressenya del producte',
            'invalid-id-format' => 'Format d\'ID no vàlid. S\'espera el format IRI com "/api/shop/reviews/1" o un ID numèric',
            'not-found' => 'No s\'ha trobat la ressenya del producte amb l\'ID ":id"',
            'item-not-found' => 'No s\'ha trobat l\'element ":id".',
            'product-id-required' => 'Es requereix l\'ID del producte',
            'product-not-found' => 'Producte no trobat',
            'rating-invalid' => 'La valoració ha d\'estar entre 1 i 5',
            'title-required' => 'Es requereix el títol de la ressenya',
            'comment-required' => 'Es requereix el comentari de la ressenya',
            'review-disabled' => 'Les ressenyes de productes estan desactivades actualment',
            'guest-review-disabled' => 'No es permeten ressenyes de convidats. Inicieu la sessió per enviar una ressenya',
            'owner-required' => 'Inicieu la sessió per gestionar la vostra ressenya',
            'not-owned' => 'Aquesta ressenya no l\'heu escrita vosaltres i no es pot modificar ni suprimir',
            'invalid-attachments' => 'Format de fitxers adjunts no vàlid.',
            'invalid-product-id-format' => 'Format d\'ID de producte no vàlid. S\'espera el format IRI com "/api/shop/products/1" o un ID numèric',
        ],

        'customer-review' => [
            'id-required' => 'Cal l\'ID de la ressenya del client',
            'invalid-id-format' => 'Format d\'ID no vàlid. S\'espera el format IRI com "/api/shop/customer-reviews/1" o un ID numèric',
            'not-found' => 'No s\'ha trobat la ressenya del client amb l\'ID ":id"',
        ],

        'newsletter' => [
            'error-during-subscription' => 'Ara mateix no es pot completar la subscripció. Torneu-ho a provar més tard',
        ],

        'contact-us' => [
            'success' => 'La vostra consulta s\'ha enviat correctament. Us respondrem aviat',
            'failed' => 'Ara mateix no es pot enviar la vostra consulta. Torneu-ho a provar més tard',
            'invalid-input' => 'Dades del formulari de contacte no vàlides',
        ],

        'customer-order' => [
            'id-required' => 'Cal l\'ID de la comanda del client',
            'invalid-id-format' => 'Format d\'ID no vàlid. S\'espera el format IRI com "/api/shop/customer-orders/1" o un ID numèric',
            'not-found' => 'No s\'ha trobat la comanda del client amb l\'ID ":id"',
        ],

        'return' => [
            'not-found' => 'Sol·licitud de devolució no trobada.',
            'disabled' => 'Les devolucions no estan activades per a aquesta botiga.',
            'invalid-input' => 'Falten dades de la devolució o no són vàlides.',
            'invalid-resolution-type' => 'El tipus de resolució ha de ser "return" o "cancel_items".',
            'agreement-required' => 'Heu d\'acceptar la política de devolucions.',
            'invalid-order' => 'La comanda seleccionada no és vàlida.',
            'invalid-item' => 'L\'element seleccionat no es pot retornar.',
            'qty-exceeds' => 'La quantitat sol·licitada supera la quantitat retornable.',
            'invalid-package-condition' => 'L\'estat del paquet ha de ser "open" o "packed".',
            'invalid-image' => 'Un o més dels fitxers carregats tenen un tipus de fitxer no admès.',
            'invalid-custom-field' => 'Camp personalitzat desconegut ":id".',
            'custom-field-required' => 'El camp ":field" és obligatori.',
            'invalid-custom-field-value' => 'El valor de ":field" no és cap de les opcions permeses.',
            'created' => 'Sol·licitud de devolució creada correctament.',
            'canceled' => 'Sol·licitud de devolució cancel·lada correctament.',
            'already-canceled' => 'La sol·licitud de devolució ja està cancel·lada.',
            'cancel-not-allowed' => 'Aquesta sol·licitud de devolució no es pot cancel·lar.',
            'close-not-allowed' => 'Aquesta sol·licitud de devolució no es pot tancar.',
            'reopened' => 'Sol·licitud de devolució reoberta correctament.',
            'reopen-not-allowed' => 'Aquesta sol·licitud de devolució no es pot reobrir.',
            'closed' => 'Sol·licitud de devolució tancada correctament.',
            'message-sent' => 'Missatge enviat correctament.',
            'attachment-not-found' => 'Fitxer adjunt del missatge no trobat.',
        ],

        'eu-withdrawal' => [
            'not-found' => 'Declaració de desistiment no trobada.',
            'order-not-found' => 'No s\'ha trobat la comanda o no és apta per al desistiment.',
            'too-many-attempts' => 'Massa intents. Torneu-ho a provar d\'aquí a :seconds segons.',
            'created' => 'Declaració de desistiment presentada correctament.',
        ],

        'customer-order-shipment' => [
            'id-required' => 'Cal l\'ID de l\'enviament',
            'not-found' => 'L\'enviament amb l\'ID ":id" no s\'ha trobat o no pertany a aquest client',
            'item-not-found' => 'L\'element d\'enviament amb l\'ID ":id" no s\'ha trobat o no pertany a aquest client',
            'invalid-id-format' => 'Format d\'ID no vàlid. S\'espera el format IRI com "/api/shop/customer-order-shipments/1" o un ID numèric',
        ],

        'cancel-order' => [
            'order-id-required' => 'Cal l\'ID de la comanda per cancel·lar una comanda',
            'not-found' => 'La comanda amb l\'ID ":id" no s\'ha trobat o no pertany a aquest client',
            'cancel-success' => 'La comanda s\'ha cancel·lat correctament',
            'cancel-failed' => 'La comanda no es pot cancel·lar. Pot ser que ja s\'hagi processat, enviat o cancel·lat',
        ],

        'reorder' => [
            'order-id-required' => 'Cal l\'ID de la comanda per tornar a demanar',
            'not-found' => 'La comanda amb l\'ID ":id" no s\'ha trobat o no pertany a aquest client',
            'reorder-success' => 'S\'han afegit al vostre carretó :count element(s) de la vostra comanda anterior',
            'no-items-added' => 'No s\'ha pogut afegir cap element al carretó. Pot ser que els productes estiguin esgotats o no estiguin disponibles',
        ],

        'customer-downloadable-product' => [
            'id-required' => 'Cal l\'ID del producte descarregable del client',
            'invalid-id-format' => 'Format d\'ID no vàlid. S\'espera el format IRI com "/api/shop/customer-downloadable-products/1" o un ID numèric',
            'not-found' => 'No s\'ha trobat el producte descarregable del client amb l\'ID ":id"',

            'download' => [
                'unauthenticated' => 'No autoritzat: cal l\'autenticació del client.',
                'not-found' => 'Producte descarregable no trobat.',
                'pending' => 'La descàrrega està pendent. Espereu que la comanda es facturi.',
                'limit-exceeded' => 'S\'ha superat el límit de descàrregues.',
                'no-downloads-remaining' => 'No queden més descàrregues disponibles.',
                'file-not-found' => 'Fitxer no trobat.',
                'link-not-found' => 'Enllaç descarregable no trobat.',
            ],
        ],

        'customer-invoice' => [
            'id-required' => 'Cal l\'ID de la factura del client',
            'invalid-id-format' => 'Format d\'ID no vàlid. S\'espera el format IRI com "/api/shop/customer-invoices/1" o un ID numèric',
            'not-found' => 'No s\'ha trobat la factura del client amb l\'ID ":id"',
        ],

        'auth' => [
            'no-token-provided' => 'No s\'ha proporcionat cap testimoni d\'autenticació. Si us plau, proporcioneu el testimoni a la capçalera d\'autorització com a "Bearer <token>" o al camp input.token',
            'invalid-or-expired-token' => 'Testimoni no vàlid o caducat',
            'request-not-found' => 'Sol·licitud no trobada al context',
            'token-required' => 'Authentication token is required. Please provide the token either in the GraphQL mutation input field or in the Authorization header as "Bearer <token>"',
            'unknown-resource' => 'Recurs desconegut',
            'cannot-update-other-profile' => 'No autoritzat: No es pot actualitzar el perfil d\'un altre client',
        ],

        'upload' => [
            'invalid-base64' => 'Dades d\'imatge codificades en base64 no vàlides',
            'size-exceeds-limit' => 'La mida de la imatge no ha de superar els 5MB',
            'invalid-format' => 'Format d\'imatge no vàlid. Si us plau, proporcioneu una imatge codificada en base64 amb l\'esquema URI de dades (data:image/jpeg;base64,...)',
            'failed' => 'Ha fallat la càrrega de la imatge',
        ],

        'attribute' => [
            'code-already-exists' => 'El codi de l\'atribut ja existeix',
            'option-id-required' => 'El paràmetre "attributeId" és obligatori quan es consulten directament les opcions d\'atribut',
            'id-required' => 'Cal l\'ID de l\'atribut',
            'invalid-id-format' => 'Format d\'ID no vàlid. S\'espera el format IRI com "/api/shop/attributes/1" o un ID numèric',
            'not-found' => 'Atribut no trobat',
        ],

        'attribute-option' => [
            'id-required' => 'Cal l\'ID de l\'opció d\'atribut',
            'invalid-id-format' => 'Format d\'ID no vàlid. S\'espera el format IRI com "/api/shop/attribute-options/1" o un ID numèric',
            'not-found' => 'Opció d\'atribut no trobada',
        ],

        'channel' => [
            'id-required' => 'Cal l\'ID del canal',
            'invalid-id-format' => 'Format d\'ID no vàlid. S\'espera el format IRI com "/api/shop/channels/1" o un ID numèric',
            'not-found' => 'Canal no trobat',
        ],

        'country-state' => [
            'id-required' => 'Cal l\'ID de l\'estat del país',
            'invalid-id-format' => 'Format d\'ID no vàlid. S\'espera el format IRI com "/api/shop/country-states/1" o un ID numèric',
            'not-found' => 'Estat del país no trobat',
            'country-id-required' => 'Cal l\'ID del país',
        ],

        'country' => [
            'id-required' => 'Cal l\'ID del país',
            'invalid-id-format' => 'Format d\'ID no vàlid. S\'espera el format IRI com "/api/shop/countries/1" o un ID numèric',
            'not-found' => 'País no trobat',
        ],

        'login' => [
            'invalid-credentials' => 'Correu electrònic o contrasenya no vàlids',
            'account-suspended' => 'El vostre compte ha estat suspès',
            'account-inactive' => 'La teva activació requereix l’aprovació de l’administrador.',
            'email-not-verified' => 'Verifica primer el teu compte de correu electrònic.',
            'successful' => 'Heu iniciat la sessió correctament',
            'invalid-request' => 'Sol·licitud d\'inici de sessió no vàlida',
        ],

        'social-login' => [
            'signed-in' => 'Sessió iniciada correctament.',
            'token-required' => 'Cal un testimoni d’inici de sessió social.',
            'invalid-token' => 'El testimoni d’inici de sessió social no és vàlid o ha caducat. Torneu-ho a provar.',
            'google-not-configured' => 'L\'inici de sessió amb Google no està configurat per a aquesta botiga.',
            'wrong-audience' => 'Aquest testimoni es va emetre per a una altra aplicació.',
            'email-required' => 'El proveïdor no ha compartit cap adreça de correu electrònic. Registreu-vos amb correu electrònic.',
            'account-inactive' => 'La teva activació requereix l’aprovació de l’administrador.',
            'provider-not-supported' => 'Aquest proveïdor d’inici de sessió social no és compatible.',
            'provider-disabled' => 'Aquest proveïdor d’inici de sessió social no està activat.',
        ],

        'checkout' => [
            'invalid-input' => 'Dades d\'entrada no vàlides per a l\'operació de compra',
            'billing-address-required' => 'Es requereix l\'adreça de facturació',
            'shipping-address-required' => 'Es requereix l\'adreça d\'enviament per als enviaments',
            'address-save-failed' => 'No s\'ha pogut desar l\'adreça',
            'address-saved' => 'Adreça desada correctament',
            'shipping-method-required' => 'Es requereix el mètode d\'enviament',
            'invalid-shipping-method' => 'Mètode d\'enviament no vàlid o no disponible',
            'shipping-method-save-failed' => 'No s\'ha pogut desar el mètode d\'enviament',
            'shipping-method-saved' => 'Mètode d\'enviament desat correctament',
            'shipping-method-error' => 'Error en desar el mètode d\'enviament',
            'payment-method-required' => 'Es requereix el mètode de pagament',
            'invalid-payment-method' => 'Mètode de pagament no vàlid o no disponible',
            'payment-method-save-failed' => 'No s\'ha pogut desar el mètode de pagament',
            'payment-method-saved' => 'Mètode de pagament desat correctament',
            'payment-method-error' => 'Error en desar el mètode de pagament',
            'order-placed' => 'Comanda realitzada correctament',
            'order-redirect-required' => 'Aquest mètode de pagament requereix que el comprador completi el pagament a la passarel·la. Envieu-lo a redirectUrl; la comanda es crea un cop la passarel·la confirma el pagament.',
            'order-creation-failed' => 'Ha fallat la creació de la comanda: l\'ID de la comanda és nul o la comanda no s\'ha desat',
            'order-retrieval-failed' => 'No s\'ha pogut recuperar la comanda creada',
            'order-creation-error' => 'No s\'ha pogut crear la comanda',
            'cart-empty' => 'El carretó està buit',
            'account-suspended' => 'El vostre compte ha estat suspès. Si us plau, contacteu amb el suport.',
            'account-inactive' => 'El vostre compte està inactiu. Si us plau, contacteu amb el suport.',
            'minimum-order-not-met' => 'L\'import mínim de la comanda és :amount',
            'guest-checkout-not-allowed' => 'Si us plau, inicieu sessió per finalitzar la compra. La compra com a convidat no està disponible per a aquesta cistella.',
            'email-required' => 'Es requereix l\'adreça de correu electrònic per crear la comanda',
            'unknown-operation' => 'Operació de compra desconeguda',
        ],

        'customer-addresses' => [
            'token-required' => 'Es requereix el testimoni per obtenir les adreces del client',
            'invalid-or-expired-token' => 'Testimoni no vàlid o caducat',
            'token-validation-failed' => 'Ha fallat la validació del testimoni',
        ],

        'product' => [
            'not-found' => 'Producte no trobat',
            'not-found-with-sku' => 'No s\'ha trobat cap producte amb l\'SKU',
            'not-found-with-url-key' => 'No s\'ha trobat cap producte amb la clau d\'URL',
            'parameters-required' => 'Cal proporcionar almenys un dels paràmetres següents: "sku", "id", "urlKey"',
            'type' => 'Tipus de producte',
            'attribute-family' => 'Família d\'atributs',
            'sku' => 'SKU',
            'name' => 'Nom',
            'description' => 'Descripció',
            'short-description' => 'Descripció curta',
            'status' => 'Estat',
            'new' => 'Nou',
            'featured' => 'Destacat',
            'price' => 'Preu',
            'special-price' => 'Preu especial',
            'weight' => 'Pes',
            'cost' => 'Cost',
            'length' => 'Longitud',
            'width' => 'Amplada',
            'height' => 'Alçada',
            'color' => 'Color',
            'size' => 'Mida',
            'brand' => 'Marca',
            'super-attributes' => 'Superatributs',
            'id-required' => 'Cal l\'ID del producte',
            'invalid-id-format' => 'Format d\'ID no vàlid. S\'espera el format IRI com "/api/shop/products/1" o un ID numèric',
        ],

        'downloadable-product' => [
            'download-link-not-found' => 'Enllaç de descàrrega no trobat o caducat',
            'purchased-link-not-found' => 'Enllaç de compra no trobat',
            'file-not-found' => 'Fitxer no trobat',
            'download-successful' => 'Fitxer llest per descarregar',
            'token-required' => 'Es requeix un token de descàrrega',
            'invalid-token' => 'Token de descàrrega no vàlid o caducat',
            'token-expired' => 'El token de descàrrega ha caducat. Si us plau, generi un de nou',
            'access-denied' => 'Accés denegat: No teniu permís per descarregar aquest fitxer',
            'redirect-external-url' => 'Redirigint a URL de descàrrega externa',
            'file-error' => 'Ha ocorregut un error en processar la vostra sol·licitud de descàrrega',
            'unauthorized-access' => 'Accés no autoritzat al recurs de descàrrega',
            'unauthenticated' => 'No autoritzat: cal l\'autenticació del client',
            'link-id-required' => 'Cal downloadableLinkPurchasedId',
            'link-not-found' => 'Enllaç descarregable no trobat',
            'not-owner' => 'No autoritzat: aquest enllaç de descàrrega no us pertany',
            'pending' => 'La descàrrega està pendent. Espereu que la comanda es facturi.',
            'limit-exceeded' => 'S\'ha superat el límit de descàrregues',
            'no-downloads-left' => 'No queden més descàrregues disponibles',
        ],

        'booking-slot' => [
            'id-required' => 'Cal l\'ID del producte de reserva.',
            'date-required' => 'Cal la data.',
            'product-not-found' => 'Producte de reserva no trobat.',
            'invalid-type' => 'Aquest tipus de reserva no és compatible.',
            'invalid-time-range' => 'Interval horari no vàlid.',
            'invalid-time-format' => 'Format d\'hora no vàlid.',
        ],

        'resolver' => [
            'no-resolve-info' => 'GraphQL ResolveInfo no està disponible al context. No es pot determinar la classe del model.',
            'unknown-resource-class' => 'No es pot determinar la classe del recurs a partir del tipus de retorn de GraphQL',
        ],

        'docs' => [
            'openapi-unavailable' => 'No s\'ha pogut instanciar l\'OpenAPI Factory',
        ],

        'locale' => [
            'id-required' => 'Cal l\'ID de la configuració regional',
            'invalid-id-format' => 'Format d\'ID no vàlid. S\'espera el format IRI com "/api/shop/locales/1" o un ID numèric',
            'not-found' => 'Configuració regional no trobada',
        ],

        'category' => [
            'id-required' => 'Cal l\'ID de la categoria',
            'invalid-id-format' => 'Format d\'ID no vàlid. S\'espera el format IRI com "/api/shop/categories/1" o un ID numèric',
            'not-found' => 'Categoria no trobada',
        ],

        'compare-item' => [
            'id-required' => 'L\'ID de l\'element de comparació és obligatori',
            'invalid-id-format' => 'Format d\'ID no vàlid. Es s\'espera el format IRI com a "/api/shop/compare-items/1" o ID numèric',
            'not-found' => 'Element de comparació no trobat',
            'product-id-required' => 'L\'ID del producte és obligatori',
            'customer-id-required' => 'L\'ID del client és obligatori',
            'product-not-found' => 'Producte no trobat',
            'customer-not-found' => 'Client no trobat',
            'already-exists' => 'Aquest producte ja està a la vostra llista de comparació',
            'delete-all-success' => 'S\'han eliminat correctament tots els elements de comparació',
        ],

        'wishlist' => [
            'id-required' => 'Cal l\'ID de l\'element de la llista de desitjos',
            'invalid-id-format' => 'Format d\'ID no vàlid. S\'espera el format IRI com "/api/shop/wishlists/1" o un ID numèric',
            'not-found' => 'Element de la llista de desitjos no trobat',
            'product-id-required' => 'Cal l\'ID del producte',
            'customer-id-required' => 'Cal l\'ID del client',
            'product-not-found' => 'Producte no trobat',
            'product-disabled' => 'Aquest producte està desactivat actualment',
            'customer-not-found' => 'Client no trobat',
            'already-exists' => 'Aquest producte ja és a la vostra llista de desitjos',
            'added' => 'Element afegit correctament a la llista de desitjos',
            'removed' => 'Element eliminat correctament de la llista de desitjos',
            'product-removed' => 'El producte s\'ha eliminat',
            'wishlist-item-id-required' => 'Cal l\'ID de l\'element de la llista de desitjos',
            'invalid-quantity' => 'La quantitat ha de ser superior a 0',
            'move-to-cart-missing-options' => 'Al producte li falten opcions obligatòries. Configureu-lo manualment',
            'moved-to-cart-success' => 'Element mogut al carretó correctament',
            'delete-all-success' => 'S\'han eliminat correctament tots els elements de la llista de desitjos',
        ],

        'gdpr' => [
            'disabled' => 'Les sol·licituds de dades RGPD estan desactivades. Activeu el RGPD des de la configuració d\'administració.',
            'unauthenticated' => 'Heu d\'haver iniciat la sessió per gestionar les sol·licituds de dades RGPD.',
            'not-found' => 'Sol·licitud de dades RGPD no trobada',
            'type-required' => 'Cal el tipus de sol·licitud',
            'type-invalid' => 'El tipus de sol·licitud ha de ser "delete" o "update"',
            'message-required' => 'Cal el missatge',
            'raised' => 'La vostra sol·licitud de dades RGPD s\'ha presentat correctament.',
            'revoked' => 'La vostra sol·licitud de dades RGPD s\'ha revocat correctament.',
            'revoke-not-allowed' => 'Aquesta sol·licitud de dades RGPD ja no es pot revocar.',
            'deleted' => 'La vostra sol·licitud de dades RGPD s\'ha suprimit correctament.',
        ],

        'section' => [
            'id-required' => 'Cal l\'ID de la secció',
            'invalid-id-format' => 'Format d\'ID no vàlid. S\'espera el format IRI com "/api/shop/sections/1" o un ID numèric',
            'not-found' => 'Secció no trobada',
        ],

        'install' => [
            'starting' => 'S\'està iniciant la instal·lació d\'API Platform...',
            'completed-success' => '✓ La instal·lació d\'API Platform s\'ha completat correctament!',
            'failed' => '✗ Ha fallat la instal·lació: ',

            'provider-already-registered' => 'ℹ El Service Provider ja està registrat',
            'provider-not-registered' => '⚠ No s\'ha pogut registrar el Service Provider automàticament. Afegiu :provider a bootstrap/providers.php manualment.',
            'provider-registered' => '✓ Service Provider registrat a bootstrap/providers.php',
            'provider-file-not-found' => 'Fitxer no trobat: :file',
            'provider-permission-denied' => 'Permís denegat: no es pot escriure a :file. Comproveu els permisos del fitxer.',

            'config-already-exists' => 'ℹ El fitxer de configuració ja existeix',
            'config-published' => '✓ Configuració d\'API Platform publicada a config/',
            'config-source-not-found' => 'Fitxer de configuració d\'origen no trobat: :file',
            'config-permission-denied' => 'Permís denegat: no es pot escriure a :directory. Comproveu els permisos del directori.',

            'translatable-not-found' => 'ℹ TranslatableModel no trobat - s\'omet',
            'translatable-already-abstract' => 'ℹ TranslatableModel ja és abstracte',
            'translatable-made-abstract' => '✓ TranslatableModel convertit en abstracte',
            'translatable-permission-denied' => 'Permís denegat: no es pot escriure a :file. Comproveu els permisos del fitxer.',

            'providers-already-registered' => 'ℹ Els proveïdors d\'API Platform ja estan registrats',
            'providers-registered' => '✓ Proveïdors d\'API Platform registrats a bootstrap/app.php',
            'providers-file-not-found' => 'Fitxer no trobat: :file',
            'providers-permission-denied' => 'Permís denegat: no es pot escriure a :file. Comproveu els permisos del fitxer.',
            'providers-not-found' => 'No s\'ha trobat la creació de l\'Application a bootstrap/app.php',

            'assets-published' => '✓ Recursos del paquet publicats',
            'running-migrations' => 'S\'estan executant les migracions de la base de dades...',
            'migrations-completed' => '✓ Migracions de la base de dades completades',
            'clearing-caches' => 'Netejant i optimitzant la memòria cau...',
            'caches-optimized' => '✓ Memòria cau netejada i optimitzada',
            'generating-api-key' => 'S\'està generant la clau d\'API...',
            'api-key-generated' => '✓ Clau d\'API generada correctament',
            'api-key-already-exists' => 'ℹ La clau d\'API de la botiga predeterminada ja existeix - s\'omet la generació',

            'migrations-error' => 'Han fallat les migracions de la base de dades.',
            'migrations-error-running' => 'Error en executar les migracions de la base de dades: :error',
            'cache-clear-error' => 'No s\'ha pogut netejar la memòria cau.',
            'cache-optimize-error' => 'L\'optimització ha fallat.',
            'cache-error' => 'Error en netejar i optimitzar la memòria cau: :error',
            'api-key-generation-error' => 'Ha fallat la generació de la clau d\'API.',
            'api-key-error' => 'Error en generar la clau d\'API: :error',

            'key-management-required' => 'Cal --key per a l\'acció :action',
            'key-rotation-error' => 'Error en rotar la clau: :error',
            'key-deactivation-error' => 'Error en desactivar la clau: :error',
            'key-not-found' => 'Clau d\'API no trobada: :identifier',

            'maintenance-starting' => '🔄 S\'està iniciant el manteniment de claus d\'API...',
            'maintenance-complete' => '✅ Manteniment de claus d\'API completat',
            'cleanup-expired-keys' => '🧹 S\'estan netejant les claus caducades...',
            'cleanup-expired-none' => '   ℹ️ No hi ha claus caducades per netejar',
            'invalidate-deprecated' => '⚠️ S\'estan invalidant les claus obsoletes...',
            'invalidate-deprecated-none' => '   ℹ️ No hi ha claus obsoletes per invalidar',
            'notify-expiring' => '📧 S\'estan enviant les notificacions de caducitat...',
            'notify-expiring-none' => '   ℹ️ No hi ha claus que requereixin notificacions',

            'key-rotated-success' => '✅ Clau rotada correctament!',
            'key-deactivated-success' => '✅ Clau desactivada correctament!',
            'deactivation-cancelled' => 'Desactivació cancel·lada.',
            'cleanup-cancelled' => 'Neteja cancel·lada.',

            'key-status-title' => 'Estat de la clau: :name',
            'key-status-active' => 'Activa: :status',
            'key-status-usable' => 'Utilitzable: :status',
            'key-status-expired' => 'Caducada: :status',
            'key-status-deprecated' => 'Obsoleta: :status',
            'key-status-expires-at' => 'Caduca el: :expires',
            'key-status-days-until-expiry' => 'Dies fins a la caducitat: :days',
            'key-status-last-used' => 'Últim ús: :date',
            'key-status-rotated-from' => 'Rotada des de: :key',
            'key-status-rotated-keys' => 'Claus rotades a partir d\'aquesta: :count',

            'policy-compliance-summary' => 'Resum del compliment de la política de rotació de claus d\'API',
            'available-actions' => 'Accions disponibles: rotate, deactivate, cleanup, status, expiring, unused, summary',

            'asset-linked-success' => '✓ Recursos d\'API Platform enllaçats correctament',
            'symlink-create-failed' => 'No s\'ha pogut crear l\'enllaç simbòlic; es copien els recursos...',
            'asset-copy-warning' => 'Avís: no s\'han pogut enllaçar ni copiar els recursos d\'API Platform. Pot caldre una configuració manual.',
            'asset-copied-success' => '✓ Recursos d\'API Platform copiats correctament',

            'key-name-required' => 'El nom de la clau de botiga no pot estar buit.',
            'key-generated-success' => 'Clau de botiga generada correctament!',
            'key-details' => 'Detalls de la clau:',
            'key-secure-warning' => 'Mantingueu aquesta clau segura! S\'utilitzarà a la capçalera X-STOREFRONT-KEY.',
            'key-share-warning' => 'No compartiu aquesta clau públicament ni la confirmeu al control de versions.',

            'key-already-exists' => 'Ja existeix una clau de botiga amb el nom \':name\'.',
            'cleanup-success' => '✅ S\'han netejat :count claus caducades',
            'confirm-cleanup' => 'Això suprimirà de manera reversible totes les claus caducades. Voleu continuar?',
            'confirm-deactivate' => 'Segur que voleu desactivar la clau: :name?',

            'old-key' => 'Clau antiga: :name',
            'old-key-id' => 'ID de la clau antiga: :id',
            'deprecation-date' => 'Data d\'obsolescència: :date',
            'new-key' => 'Clau nova: :name',
            'new-key-id' => 'ID de la clau nova: :id',
            'new-key-value' => 'Valor de la clau nova: :key',
            'expires-at' => 'Caduca el: :date',
            'invalid-action' => 'Acció no vàlida: :action',
            'key-active' => 'Activa: :status',
            'key-usable' => 'Utilitzable: :status',
            'key-expired' => 'Caducada: :status',
            'key-deprecated' => 'Obsoleta: :status',
            'key-field-id' => 'ID',
            'key-field-name' => 'Nom',
            'key-field-key' => 'Clau',
            'key-field-rate-limit' => 'Límit de sol·licituds',
            'key-field-status' => 'Estat',
            'key-days' => ' dies',
            'key-never' => 'Mai',
            'key-requests-minute' => ' sol·licituds/minut',
            'key-unlimited' => 'Il·limitat',
            'rate-limit-exceeded' => 'El límit de sol·licituds demanat supera el màxim permès de :max. S\'estableix a :max sol·licituds/minut.',

            'vendor-path-not-found' => 'No s\'ha trobat el camí de vendor d\'API Platform a: :path',
            'assets-already-linked' => 'Els recursos d\'API Platform ja estan enllaçats a: :path',
            'no-keys-expiring' => 'No hi ha claus que caduquin en els propers :days dies',
            'keys-expiring-title' => 'Claus que caduquen en els propers :days dies:',
            'key-display-format' => '• :name (ID: :id)',
            'key-expires-display' => '  Caduca: :date (queden :days dies)',
            'no-unused-keys' => 'No s\'han trobat claus sense ús (> :days dies)',
            'unused-keys-title' => 'Claus sense ús (> :days dies):',
            'key-last-used-display' => '  Últim ús: :date',
            'total-keys' => 'Total de claus: :count',
            'valid-keys' => 'Claus vàlides: :count',
            'expired-keys' => 'Claus caducades: :count',
            'deprecated-keys' => 'Claus obsoletes: :count',
            'keys-expiring-soon' => 'Claus que caduquen aviat (7 dies): :count',
            'unused-keys-summary' => 'Claus sense ús (90 dies): :count',
            'recently-rotated' => 'Rotades recentment (30 dies): :count',
            'cleanup-success-message' => '   ✅ S\'han netejat :count claus caducades',
            'invalidate-success-message' => '   ✅ S\'han invalidat :count claus obsoletes',
            'notify-success-message' => '   ✅ S\'han enviat :count notificacions de caducitat',
            'notify-failed-message' => '   ⚠️ No s\'ha pogut notificar sobre :key: :error',
            'publish-assets-warning' => 'Avís: no s\'han pogut publicar els recursos del paquet. :error',
            'env-config-saved' => '✓ Configuració d\'entorn d\'API Platform desada correctament',
            'api-endpoints' => 'Punts d\'accés de l\'API:',
            'api-documentation' => 'Documentació de l\'API: :url',
            'api-landing-page' => 'Pàgina d\'inici de l\'API: :url',
            'graphql-playground' => 'GraphQL Playground: :url',
            'rest-api-storefront' => 'Swagger de l\'API REST de la botiga: :url',
            'rest-api-admin' => 'Swagger de l\'API REST d\'administració: :url',
            'key-rotation-invalid-key' => 'No es pot rotar una clau no vàlida o caducada',
            'env-file-not-found' => 'No s\'ha trobat el fitxer .env',
            'env-permission-denied' => 'Permís denegat: no es pot escriure al fitxer .env. Comproveu els permisos del fitxer.',
        ],

        'attribute-translation' => [
            'id-required' => 'Cal l\'ID de la traducció de l\'atribut',
            'invalid-id-format' => 'Format d\'ID no vàlid. S\'espera el format IRI com "/api/shop/attribute-translations/1" o un ID numèric',
            'not-found' => 'Traducció de l\'atribut no trobada',
        ],

        'currency' => [
            'id-required' => 'Cal l\'ID de la moneda',
            'invalid-id-format' => 'Format d\'ID no vàlid. S\'espera el format IRI com "/api/shop/currencies/1" o un ID numèric',
            'not-found' => 'Moneda no trobada',
        ],

        'customer-address' => [
            'id-required' => 'Cal l\'ID de l\'adreça del client',
            'invalid-id-format' => 'Format d\'ID no vàlid. S\'espera el format IRI com "/api/shop/customer-addresses/1" o un ID numèric',
        ],

        'customer-order-shipment-item' => [
            'id-required' => 'Cal l\'ID de l\'article de l\'enviament',
            'invalid-id-format' => 'Format d\'ID no vàlid. S\'espera el format IRI com "/api/shop/customer-order-shipment-items/1" o un ID numèric',
            'not-found' => 'Article de l\'enviament no trobat',
        ],

        'page' => [
            'id-required' => 'Cal l\'ID de la pàgina',
            'invalid-id-format' => 'Format d\'ID no vàlid. S\'espera el format IRI com "/api/shop/pages/1" o un ID numèric',
            'not-found' => 'Pàgina no trobada',
        ],

        'page-translation' => [
            'id-required' => 'Cal l\'ID de la traducció de la pàgina',
            'invalid-id-format' => 'Format d\'ID no vàlid. S\'espera el format IRI com "/api/shop/page-translations/1" o un ID numèric',
            'not-found' => 'Traducció de la pàgina no trobada',
        ],

        'product-bundle-option' => [
            'id-required' => 'Cal l\'ID de l\'opció del paquet',
            'invalid-id-format' => 'Format d\'ID no vàlid. S\'espera el format IRI com "/api/shop/product-bundle-options/1" o un ID numèric',
            'not-found' => 'Opció del paquet no trobada',
        ],

        'product-bundle-option-product' => [
            'id-required' => 'Cal l\'ID del producte de l\'opció del paquet',
            'invalid-id-format' => 'Format d\'ID no vàlid. S\'espera el format IRI com "/api/shop/product-bundle-option-products/1" o un ID numèric',
            'not-found' => 'Producte de l\'opció del paquet no trobat',
        ],

        'product-downloadable-link' => [
            'id-required' => 'Cal l\'ID de l\'enllaç descarregable',
            'invalid-id-format' => 'Format d\'ID no vàlid. S\'espera el format IRI com "/api/shop/product-downloadable-links/1" o un ID numèric',
            'not-found' => 'Enllaç descarregable no trobat',
        ],

        'product-downloadable-sample' => [
            'id-required' => 'Cal l\'ID de la mostra descarregable',
            'invalid-id-format' => 'Format d\'ID no vàlid. S\'espera el format IRI com "/api/shop/product-downloadable-samples/1" o un ID numèric',
            'not-found' => 'Mostra descarregable no trobada',
        ],

        'product-grouped-product' => [
            'id-required' => 'Cal l\'ID del producte agrupat',
            'invalid-id-format' => 'Format d\'ID no vàlid. S\'espera el format IRI com "/api/shop/product-grouped-products/1" o un ID numèric',
            'not-found' => 'Producte agrupat no trobat',
        ],

        'product-images' => [
            'id-required' => 'Cal l\'ID de la imatge del producte',
            'invalid-id-format' => 'Format d\'ID no vàlid. S\'espera el format IRI com "/api/shop/product-images/1" o un ID numèric',
            'not-found' => 'Imatge del producte no trobada',
        ],

        'product-videos' => [
            'id-required' => 'Cal l\'ID del vídeo del producte',
            'invalid-id-format' => 'Format d\'ID no vàlid. S\'espera el format IRI com "/api/shop/product-videos/1" o un ID numèric',
            'not-found' => 'Vídeo del producte no trobat',
        ],

        'theme-customization' => [
            'id-required' => 'Cal l\'ID de la personalització del tema',
            'invalid-id-format' => 'Format d\'ID no vàlid. S\'espera el format IRI com "/api/shop/theme-customizations/1" o un ID numèric',
            'not-found' => 'Personalització del tema no trobada',
        ],

        'unknown-resource' => [
            'id-required' => 'Cal l\'ID del recurs',
            'not-found' => 'Recurs no trobat',
        ],
    ],

    'admin' => [
        // login / logout / forgot-password / profile-update keys removed in the
        // 2026-05-27 auth refactor — admin clients now authenticate via
        // pre-issued integration tokens (Bearer header, AdminApiGuard).
        'profile' => [
            'unauthenticated' => 'No esteu autenticat.',
        ],

        'dashboard' => [
            'invalid-type' => 'Tipus d\'estadística del tauler desconegut. Tipus vàlids: over-all, today, stock-threshold-products, total-sales, top-selling-products, top-customers.',
        ],

        'rma' => [
            'not-found' => 'Sol·licitud de devolució no trobada.',
            'invalid-input' => 'Falten dades de la devolució o no són vàlides.',
            'invalid-order' => 'La comanda seleccionada no és vàlida.',
            'invalid-item' => 'L\'element seleccionat no es pot retornar.',
            'invalid-resolution-type' => 'El tipus de resolució ha de ser "return" o "cancel_items".',
            'qty-exceeds' => 'La quantitat sol·licitada supera la quantitat retornable.',
            'created' => 'Sol·licitud de devolució creada correctament.',
            'status-updated' => 'Estat de la devolució actualitzat correctament.',
            'invalid-status' => 'Estat de devolució no vàlid.',
            'reopened' => 'Sol·licitud de devolució reoberta correctament.',
            'reopen-not-allowed' => 'Aquesta sol·licitud de devolució no es pot reobrir.',
            'refund-failed' => 'No s\'ha pogut crear el reemborsament per a aquesta devolució.',
            'message-sent' => 'Missatge enviat correctament.',
            'no-permission' => 'No teniu permís per realitzar aquesta acció.',
            'reason-not-found' => 'Motiu de devolució no trobat.',
            'reason-deleted' => 'Motiu de devolució suprimit correctament.',
            'status-not-found' => 'Estat de devolució no trobat.',
            'status-deleted' => 'Estat de devolució suprimit correctament.',
            'status-default-undeletable' => 'No es pot suprimir un estat de devolució predeterminat.',
            'rule-not-found' => 'Regla de devolució no trobada.',
            'rule-deleted' => 'Regla de devolució suprimida correctament.',
            'custom-field-not-found' => 'Camp personalitzat de devolució no trobat.',
            'custom-field-deleted' => 'Camp personalitzat de devolució suprimit correctament.',
            'custom-field-options-required' => 'Cal almenys una opció per a aquest tipus de camp.',
            'mass-indices-required' => 'Cal almenys un ID.',
            'mass-delete-success' => 'Elements seleccionats suprimits.',
            'mass-update-success' => 'Elements seleccionats actualitzats.',
            'mass-value-invalid' => 'El valor de l\'estat ha de ser 0 o 1.',
            'image-not-found' => 'Imatge de la devolució no trobada.',
            'attachment-not-found' => 'Fitxer adjunt del missatge no trobat.',
        ],

        'eu-withdrawal' => [
            'not-found' => 'Declaració de desistiment no trobada.',
            'no-permission' => 'No teniu permís per gestionar els desistiments de la UE.',
            'declined' => 'Desistiment marcat com a rebutjat.',
            'refunded' => 'Desistiment marcat com a reemborsat.',
            'confirmation-resent' => 'Correu electrònic de confirmació reenviat correctament.',
            'confirmation-failed' => 'No s\'ha pogut enviar el correu electrònic de confirmació.',
        ],

        'reporting' => [
            'invalid-entity' => 'Entitat d\'informe desconeguda.',
            'invalid-type' => 'Tipus d\'estadística d\'informe desconegut per a aquesta entitat.',
            'export-format-unsupported' => 'Només s\'admeten els formats d\'exportació csv, xls i xlsx.',
        ],

        'configuration' => [
            'unauthenticated' => 'No esteu autenticat.',
            'no-permission' => 'No teniu permís per actualitzar la configuració.',
            'slug-required' => 'Cal el paràmetre de consulta `slug`.',
            'slug-not-found' => 'No hi ha cap secció de configuració registrada per al slug indicat.',
            'values-required' => 'Cal almenys un valor a `values`.',
            'scope-escape' => 'La clau `:key` és fora del subarbre del slug sol·licitat.',
            'unknown-field' => 'La clau `:key` no és un camp de configuració registrat.',
            'validation-failed' => 'Un o més valors de configuració no han superat la validació.',
            'file-upload-rest-only' => 'Les càrregues de fitxers binaris per al camp `:field` només s\'admeten a través del punt d\'accés REST multipart, no amb GraphQL.',
            'custom-view-readonly' => 'El camp `:field` és un camp personalitzat renderitzat amb Blade i no es pot actualitzar mitjançant l\'API.',
            'update-success' => 'Configuració actualitzada correctament.',
        ],

        'customer' => [
            'not-found' => 'Client no trobat.',
            'no-permission' => 'No teniu permís per realitzar aquesta acció.',
            'has-active-orders' => 'El client té comandes pendents o en procés i no es pot suprimir.',
            'deleted' => 'Client suprimit correctament.',
            'mass-delete-indices-required' => 'Cal almenys un ID de client.',
            'mass-delete-success' => 'Clients suprimits.',
            'mass-update-indices-required' => 'Cal almenys un ID de client.',
            'mass-update-value-invalid' => 'El valor de l\'estat ha de ser 0 o 1.',
            'mass-update-success' => 'Estat del client actualitzat.',

            'address' => [
                'not-found' => 'Adreça no trobada.',
                'not-owned' => 'Aquesta adreça no pertany al client especificat.',
                'customer-id-required' => 'Cal customerId per identificar el propietari de l\'adreça.',
                'deleted' => 'Adreça suprimida correctament.',
            ],

            'note' => [
                'empty' => 'Cal el text de la nota.',
                'created' => 'Nota afegida correctament.',
            ],

            'impersonate' => [
                'success' => 'S\'ha emès el testimoni de suplantació.',
            ],

            'review' => [
                'not-found' => 'Ressenya no trobada.',
                'no-permission' => 'No teniu permís per realitzar aquesta acció.',
                'invalid-status' => 'L\'estat ha de ser un dels següents: pending, approved, disapproved.',
                'updated' => 'Ressenya actualitzada correctament.',
                'deleted' => 'Ressenya suprimida correctament.',
                'mass-delete-indices-required' => 'Cal almenys un ID de ressenya.',
                'mass-delete-success' => 'Ressenyes suprimides.',
                'mass-update-indices-required' => 'Cal almenys un ID de ressenya.',
                'mass-update-value-invalid' => 'El valor de l\'estat ha de ser un dels següents: pending, approved, disapproved.',
                'mass-update-success' => 'Estat de la ressenya actualitzat.',
            ],

            'group' => [
                'not-found' => 'Grup de clients no trobat.',
                'deleted' => 'Grup de clients suprimit correctament.',
                'is-system' => 'Els grups de clients del sistema no es poden suprimir.',
                'has-customers' => 'El grup de clients té clients associats i no es pot suprimir.',
                'system-code-immutable' => 'No es pot canviar el codi d\'un grup de clients del sistema.',
                'system-flag-immutable' => 'No es pot canviar l\'indicador is_user_defined d\'un grup de clients del sistema.',
                'mass-delete-indices-required' => 'Cal almenys un ID de grup de clients.',
                'mass-delete-success' => 'Grups de clients suprimits.',
            ],

            'gdpr' => [
                'not-found' => 'Sol·licitud RGPD no trobada.',
                'no-permission' => 'No teniu permís per realitzar aquesta acció.',
                'deleted' => 'Sol·licitud RGPD suprimida correctament.',
                'invalid-status' => 'L\'estat ha de ser un dels següents: pending, processing, declined, approved, revoked.',
                'no-changes' => 'Cal almenys l\'estat o el missatge.',
                'already-processed' => 'Aquesta sol·licitud RGPD ja s\'ha processat i no es pot tornar a executar.',

                'process' => [
                    'approved' => 'Sol·licitud RGPD aprovada.',
                    'deleted' => 'Sol·licitud RGPD aprovada i client suprimit.',
                ],
            ],
        ],

        'order' => [
            'not-found' => 'Comanda no trobada.',

            'reorder' => [
                'success' => 'S\'ha tornat a demanar correctament. S\'ha creat un carretó d\'esborrany nou.',
                'cannot-reorder' => 'Aquesta comanda no es pot tornar a demanar (comanda de convidat o un o més elements ja no es poden vendre).',
                'failed' => 'No s\'ha pogut tornar a demanar. Torneu-ho a provar.',
                'guest-not-supported' => 'No es pot tornar a demanar una comanda de convidat.',
                'items-not-saleable' => 'Un o més elements d\'aquesta comanda ja no estan disponibles per a la compra.',
                'no-permission' => 'No teniu permís per crear comandes.',
                'disabled-in-settings' => 'La funció de tornar a demanar per part de l\'administrador està desactivada a la configuració de la botiga.',
            ],

            'actions' => [
                'cancel' => [
                    'closed' => 'Les comandes tancades no es poden cancel·lar.',
                    'fraud' => 'Les comandes fraudulentes no es poden cancel·lar.',
                    'nothing-to-cancel' => 'No hi ha res a cancel·lar en aquesta comanda.',
                    'already-processed' => 'Aquesta comanda ja s\'ha facturat o enviat i ja no es pot cancel·lar. En lloc d\'això, podeu emetre un reemborsament.',
                    'no-permission' => 'No teniu permís per cancel·lar comandes.',
                    'success' => 'Comanda cancel·lada correctament.',
                    'failed' => 'No s\'ha pogut cancel·lar la comanda.',
                ],

                'comment' => [
                    'empty' => 'Cal el comentari.',
                    'success' => 'Comentari afegit a la comanda.',
                    'failed' => 'No s\'ha pogut afegir el comentari.',
                ],

                'invoice' => [
                    'closed' => 'Les comandes tancades no es poden facturar.',
                    'fraud' => 'Les comandes fraudulentes no es poden facturar.',
                    'nothing-to-invoice' => 'No hi ha res a facturar en aquesta comanda.',
                    'already-invoiced' => 'Ja s\'ha generat una factura per a aquesta comanda.',
                    'paypal-standard-blocked' => 'No es poden crear factures per a comandes pagades amb PayPal Standard.',
                    'no-permission' => 'No teniu permís per crear factures.',
                    'items-required' => 'Cal almenys un element amb una quantitat positiva.',
                    'qty-exceeds' => 'La quantitat sol·licitada (:requested) supera la quantitat disponible (:available) per a l\'SKU :sku.',
                    'not-found' => 'Factura no trobada.',
                    'success' => 'Factura creada correctament.',
                    'failed' => 'No s\'ha pogut crear la factura.',
                    'pdf-failed' => 'No s\'ha pogut generar el PDF de la factura.',
                    'email-invalid' => 'Cal un correu electrònic de destinatari vàlid.',
                    'duplicate-sent' => 'Correu electrònic de la factura enviat a :email.',
                    'mass-status-indices-required' => 'Cal almenys un ID de factura.',
                    'mass-status-value-invalid' => 'L\'estat ha de ser un dels següents: pending, paid, overdue.',
                    'mass-status-success' => 'Estat de la factura actualitzat correctament.',
                ],

                'shipment' => [
                    'closed' => 'Les comandes tancades no es poden enviar.',
                    'fraud' => 'Les comandes fraudulentes no es poden enviar.',
                    'nothing-to-ship' => 'No hi ha res a enviar en aquesta comanda.',
                    'no-permission' => 'No teniu permís per enviar comandes.',
                    'source-required' => 'Cal l\'origen d\'inventari.',
                    'items-required' => 'Cal almenys un element amb una quantitat positiva.',
                    'qty-exceeds' => 'La quantitat sol·licitada (:requested) supera la quantitat disponible (:available) per a l\'SKU :sku.',
                    'inventory-insufficient' => 'L\'inventari de l\'origen seleccionat és insuficient per a l\'SKU :sku.',
                    'not-found' => 'Enviament no trobat.',
                    'success' => 'Enviament creat correctament.',
                    'failed' => 'No s\'ha pogut crear l\'enviament.',
                ],

                'refund' => [
                    'closed' => 'Les comandes tancades no es poden reemborsar.',
                    'fraud' => 'Les comandes fraudulentes no es poden reemborsar.',
                    'nothing-to-refund' => 'No queda res a reemborsar en aquesta comanda.',
                    'no-permission' => 'No teniu permís per crear reemborsaments.',
                    'qty-exceeds' => 'La quantitat sol·licitada (:requested) supera la quantitat disponible (:available) per a l\'SKU :sku.',
                    'amount-zero' => 'L\'import calculat del reemborsament és zero. Ajusteu la quantitat, l\'enviament o els valors d\'ajust.',
                    'amount-exceeds-max' => 'L\'import del reemborsament (:amount) supera l\'import màxim reemborsable (:max).',
                    'not-found' => 'Reemborsament no trobat.',
                    'success' => 'Reemborsament creat correctament.',
                    'failed' => 'No s\'ha pogut crear el reemborsament.',
                ],
            ],
        ],

        'appearance' => [
            'no-permission' => 'No teniu permís per gestionar l\'aparença.',

            'theme' => [
                'not-found' => 'Tema no trobat.',
                'not-installed' => 'Aquest tema no està instal·lat.',
                'channel-ids-required' => 'Cal almenys un canal.',
                'unknown-channel' => 'Un o més canals no existeixen.',
                'activated' => 'Tema activat correctament.',
            ],

            'section' => [
                'not-found' => 'Secció no trobada.',
                'invalid-payload' => 'Contingut de la secció no admès.',
                'footer-links-exists' => 'Aquest canal ja té una secció d\'enllaços del peu de pàgina.',
                'options-required' => 'Calen les opcions.',
                'status-required' => 'Cal l\'estat.',
                'section-ids-required' => 'Cal almenys una secció.',
                'reorder-single-scope' => 'Les seccions només es poden reordenar dins d\'un tema i un canal alhora.',
                'reorder-incomplete' => 'Per reordenar calen totes les seccions del tema i del canal, en l\'ordre en què s\'han de mostrar.',
                'unknown-section' => 'Una o més seccions no existeixen.',
                'file-required' => 'Cal un fitxer.',
                'created' => 'Secció creada correctament.',
                'updated' => 'Secció actualitzada correctament.',
                'deleted' => 'Secció suprimida correctament.',
                'draft-saved' => 'Canvis de la secció preparats correctament.',
                'status-staged' => 'Estat de la secció preparat correctament.',
                'order-staged' => 'Ordre de la secció preparat correctament.',
                'duplicated' => 'Secció copiada correctament.',
                'published' => 'Canvis de la secció publicats correctament.',
                'discarded' => 'Canvis de la secció descartats correctament.',
                'media-uploaded' => 'Fitxer multimèdia carregat correctament.',
            ],
        ],

        'sales' => [
            'no-permission' => 'No teniu permís per veure aquest recurs de vendes.',

            'invoice' => [
                'not-found' => 'Factura no trobada.',
                'no-permission' => 'No teniu permís per veure les factures.',
            ],

            'shipment' => [
                'not-found' => 'Enviament no trobat.',
                'no-permission' => 'No teniu permís per veure els enviaments.',
            ],

            'refund' => [
                'not-found' => 'Reemborsament no trobat.',
                'no-permission' => 'No teniu permís per veure els reemborsaments.',
                'export-format-unsupported' => 'Només s\'admeten exportacions en csv, xls i xlsx.',
            ],

            'transaction' => [
                'not-found' => 'Transacció no trobada.',
                'no-permission' => 'No teniu permís per veure les transaccions.',

                'create' => [
                    'invoice-required' => 'El camp invoiceId és obligatori.',
                    'payment-method-required' => 'El camp paymentMethod és obligatori.',
                    'amount-invalid' => 'El camp de l\'import és obligatori i ha de ser numèric.',
                    'invoice-missing' => 'No s\'ha trobat la factura especificada.',
                    'already-paid' => 'Aquesta factura ja està totalment pagada.',
                    'amount-exceeds' => 'L\'import del pagament supera el total general de la factura.',
                    'amount-zero' => 'L\'import del pagament ha de ser superior a zero.',
                ],
            ],

            'booking' => [
                'not-found' => 'Reserva no trobada.',
                'no-permission' => 'No teniu permís per veure les reserves.',
            ],

            'export' => [
                'format-unsupported' => 'Només s\'admeten exportacions en csv, xls i xlsx.',
            ],
        ],

        'cart' => [
            'not-found' => 'Carretó no trobat.',
            'not-draft' => 'Aquest carretó no es pot modificar mitjançant l\'API d\'administració.',
            'product-required' => 'Cal productId.',
            'product-not-found' => 'Producte no trobat.',
            'booking-unsupported' => 'Els productes de reserva no es poden afegir a una comanda d\'esborrany de l\'administració. Les compres de reserves s\'han de fer a través de la botiga del client.',
            'product-not-saleable' => 'Aquest producte no està disponible per a la venda (esgotat o desactivat) i no es pot afegir a la comanda.',
            'qty-required' => 'Cal qty i ha de ser un objecte no buit de itemId => quantitat.',
            'cart-item-required' => 'Cal cartItemId.',
            'item-added' => 'Element afegit al carretó.',
            'item-add-failed' => 'No s\'ha pogut afegir l\'element al carretó.',
            'item-updated' => 'Elements del carretó actualitzats.',
            'item-update-failed' => 'No s\'han pogut actualitzar els elements del carretó.',
            'item-removed' => 'Element eliminat del carretó.',
            'item-remove-failed' => 'No s\'ha pogut eliminar l\'element del carretó.',
            'address-saved' => 'Adreça desada.',
            'address-required' => 'Cal una adreça de facturació.',
            'address-incomplete' => 'Cal :field per desar l\'adreça.',
            'coupon-code-required' => 'Cal un codi de cupó.',
            'coupon-applied' => 'Cupó aplicat.',
            'coupon-already-applied' => 'El cupó ja està aplicat a aquest carretó.',
            'coupon-not-found' => 'Cupó no trobat o inactiu.',
            'coupon-removed' => 'Cupó eliminat.',
            'coupon-error' => 'S\'ha produït un error en aplicar el cupó.',
            'unknown-error' => 'S\'ha produït un error.',
            'draft-created' => 'Carretó d\'esborrany creat.',
            'draft-failed' => 'No s\'ha pogut crear el carretó d\'esborrany.',
            'addresses-required-for-shipping' => 'Cal desar les adreces abans de seleccionar un mètode d\'enviament.',
            'shipping-required-for-payment' => 'Cal seleccionar el mètode d\'enviament abans del mètode de pagament.',
            'shipping-method-required' => 'Cal el mètode d\'enviament.',
            'shipping-method-saved' => 'Mètode d\'enviament desat.',
            'shipping-method-failed' => 'No s\'ha pogut desar el mètode d\'enviament.',
            'payment-method-required' => 'Cal el mètode de pagament.',
            'payment-method-saved' => 'Mètode de pagament desat.',
            'payment-method-failed' => 'No s\'ha pogut desar el mètode de pagament.',

            'place-order' => [
                'success' => 'Comanda realitzada correctament.',
                'failed' => 'No s\'ha pogut realitzar la comanda.',
                'error' => 'No s\'ha pogut realitzar la comanda: el carretó té errors.',
                'minimum-order-error' => 'L\'import mínim de la comanda és :amount. Afegiu més elements abans de realitzar la comanda.',
                'empty-cart' => 'El carretó està buit — afegiu elements abans de realitzar una comanda.',
                'addresses-required' => 'Cal desar les adreces de facturació i d\'enviament abans de realitzar la comanda.',
                'shipping-required' => 'Cal seleccionar un mètode d\'enviament abans de realitzar la comanda.',
                'payment-required' => 'Cal seleccionar un mètode de pagament abans de realitzar la comanda.',
                'payment-method-unsupported' => 'Només s\'admeten cashondelivery i moneytransfer per a les comandes realitzades per l\'administració.',
            ],
        ],

        'product' => [
            'not-found' => 'Producte no trobat.',

            'mass-delete-success' => 'Productes suprimits correctament.',
            'mass-update-status-success' => 'Estat dels productes actualitzat correctament.',
            'indices-required' => 'El camp indices és obligatori i ha de ser una matriu no buida.',
            'indices-invalid' => 'El camp indices ha de ser una matriu d\'IDs de producte enters.',
            'value-invalid' => 'El camp value és obligatori i ha de ser 0 o 1.',
            'mass-delete-failed' => 'No s\'han pogut suprimir un o més productes.',
            'no-permission' => 'No teniu permís per gestionar productes.',

            'copy-success' => 'Producte copiat correctament.',
            'copy-failed' => 'No s\'ha pogut copiar el producte.',
            'copy-variant-not-supported' => 'Les variants dels productes configurables no es poden copiar. En lloc d\'això, copieu el producte configurable pare.',

            'create' => [
                'created' => 'Producte creat correctament.',
                'create-failed' => 'No s\'ha pogut crear el producte.',
                'sku-required' => 'El camp sku és obligatori.',
                'sku-unique' => 'El sku ja està en ús.',
                'sku-invalid' => 'El sku només pot contenir lletres, números i guions (p. ex. "sp-001").',
                'attribute-family-required' => 'El camp attribute_family_id és obligatori.',
                'attribute-family-not-found' => 'La família d\'atributs seleccionada no existeix.',
                'type-required' => 'El camp type és obligatori.',
                'type-not-yet-supported' => 'El tipus de producte ":type" no és compatible amb aquesta API. Tipus permesos: simple, virtual, downloadable, grouped, bundle, configurable, booking.',
                'super-attributes-required' => 'El camp super_attributes és obligatori quan type=configurable. Proporcioneu un mapa de codi (o ID) d\'atribut a option_ids, p. ex. { "color": [1,2], "size": [4,5] }.',
                'super-attributes-invalid' => 'El camp super_attributes ha de ser un mapa no buit de codi (o ID) d\'atribut a una llista no buida d\'option_ids.',
                'no-permission' => 'No teniu permís per crear productes.',
            ],

            'update' => [
                'updated' => 'Producte actualitzat correctament.',
                'update-failed' => 'No s\'ha pogut actualitzar el producte.',
                'id-required' => 'Cal un ID de producte.',
                'url-key-required' => 'El camp url_key no pot estar buit.',
                'url-key-unique' => 'El url_key ja l\'utilitza un altre producte.',
                'special-price-invalid' => 'El special_price ha de ser un nombre inferior al preu.',
                'special-price-date-range-invalid' => 'El special_price_to ha de ser igual o posterior a special_price_from.',
                'boolean-field-invalid' => 'El camp :field ha de ser 0 o 1.',
                'categories-invalid' => 'El camp categories ha de ser una matriu d\'IDs de categoria.',
                'channels-invalid' => 'El camp channels ha de ser una matriu d\'IDs de canal.',
                'super-attributes-cannot-change' => 'super_attributes no es pot canviar després de crear el producte.',
                'sub-resource-stripped-images' => 'Les imatges s\'han de gestionar mitjançant POST /api/admin/catalog/products/{id}/images. S\'ha ignorat el camp images.',
                'sub-resource-stripped-inventories' => 'Els inventaris s\'han de gestionar mitjançant PUT /api/admin/catalog/products/{id}/inventories. S\'ha ignorat el camp inventories.',
                'sub-resource-stripped-customer-group-prices' => 'Els preus per grup de clients s\'han de gestionar mitjançant /api/admin/catalog/products/{id}/customer-group-prices. S\'ha ignorat el camp customer_group_prices.',
                'translations-single-locale' => 'Només s\'ha actualitzat la configuració regional sol·licitada. Per actualitzar les configuracions regionals :locales, envieu una sol·licitud independent amb ?locale=<code> per a cadascuna.',
            ],

            'delete' => [
                'deleted' => 'Producte suprimit correctament.',
                'delete-failed' => 'No s\'ha pogut suprimir el producte.',
            ],

            'image' => [
                'uploaded' => 'Imatge del producte carregada correctament.',
                'updated' => 'Imatge del producte actualitzada correctament.',
                'update-empty' => 'Envieu un text alternatiu o una posició per actualitzar.',
                'reordered' => 'Imatges del producte reordenades correctament.',
                'deleted' => 'Imatge del producte suprimida correctament.',
                'image-required' => 'Cal un fitxer d\'imatge (camp multipart "image").',
                'image-invalid-type' => 'El tipus d\'imatge ha de ser un dels següents: bmp, jpeg, jpg, png, webp.',
                'image-too-large' => 'La imatge és massa gran. La mida màxima permesa és de 4 MB.',
                'not-found' => 'Imatge del producte no trobada.',
                'no-permission' => 'No teniu permís per gestionar les imatges dels productes.',
                'upload-failed' => 'No s\'ha pogut carregar la imatge del producte.',
                'reorder-failed' => 'No s\'han pogut reordenar les imatges del producte.',
                'delete-failed' => 'No s\'ha pogut suprimir la imatge del producte.',
                'order-required' => 'La matriu "order" és obligatòria i no pot estar buida.',
                'order-invalid' => 'Cada entrada de "order" ha de ser un objecte amb id i position.',
                'id-not-on-product' => 'La imatge :id no pertany a aquest producte.',
                'graphql-upload-unsupported' => 'La càrrega d\'imatges amb GraphQL no és compatible. Utilitzeu POST /api/admin/catalog/products/{productId}/images amb multipart/form-data.',
            ],

            'downloadable' => [
                'file-required' => 'Cal un fitxer (camp multipart "file").',
                'file-too-large' => 'El fitxer és massa gran. La mida màxima permesa és de 128 MB.',
                'upload-failed' => 'No s\'ha pogut carregar el fitxer descarregable.',
                'file-not-found' => 'No s\'ha trobat cap fitxer emmagatzemat per a aquest producte i atribut.',
                'no-permission' => 'No teniu permís per gestionar els fitxers dels productes descarregables.',
            ],

            'video' => [
                'uploaded' => 'Vídeo del producte carregat correctament.',
                'deleted' => 'Vídeo del producte suprimit correctament.',
                'video-required' => 'Cal un fitxer de vídeo (camp multipart "video").',
                'video-invalid-type' => 'El tipus de vídeo ha de ser un dels següents: mp4, webm, ogg, mov.',
                'video-too-large' => 'El vídeo és massa gran. La mida màxima permesa és de 100 MB.',
                'not-found' => 'Vídeo del producte no trobat.',
                'no-permission' => 'No teniu permís per gestionar els vídeos dels productes.',
                'upload-failed' => 'No s\'ha pogut carregar el vídeo del producte.',
                'delete-failed' => 'No s\'ha pogut suprimir el vídeo del producte.',
                'graphql-upload-unsupported' => 'La càrrega de vídeos amb GraphQL no és compatible. Utilitzeu POST /api/admin/catalog/products/{productId}/videos amb multipart/form-data.',
            ],

            'inventory' => [
                'updated' => 'Inventaris del producte desats correctament.',
                'inventories-required' => 'El camp inventories és obligatori i ha de ser un mapa no buit de inventory_source_id a quantitat.',
                'inventories-invalid' => 'El camp inventories ha de ser un mapa de inventory_source_id (enter positiu) a quantitat (enter no negatiu).',
                'source-not-found' => 'L\'origen d\'inventari :id no existeix.',
                'qty-invalid' => 'Cada quantitat ha de ser un enter no negatiu.',
                'no-permission' => 'No teniu permís per gestionar els inventaris dels productes.',
                'not-found' => 'Producte no trobat.',
            ],

            'customer-group-price' => [
                'created' => 'Preu per grup de clients afegit correctament.',
                'updated' => 'Preu per grup de clients actualitzat correctament.',
                'deleted' => 'Preu per grup de clients suprimit correctament.',
                'qty-required' => 'El camp qty és obligatori.',
                'qty-invalid' => 'El camp qty ha de ser un enter superior o igual a 1.',
                'value-type-required' => 'El camp value_type és obligatori.',
                'value-type-invalid' => 'El camp value_type ha de ser "fixed" o "discount".',
                'value-required' => 'El camp value és obligatori.',
                'value-invalid' => 'El camp value ha de ser un nombre no negatiu.',
                'customer-group-not-found' => 'El grup de clients seleccionat no existeix.',
                'duplicate-qty-group' => 'Ja existeix un preu per grup de clients per a aquesta combinació (qty, customer_group_id) en el producte.',
                'not-found' => 'Preu per grup de clients no trobat.',
                'no-permission' => 'No teniu permís per gestionar els preus per grup de clients dels productes.',
            ],
        ],

        'category' => [
            'not-found' => 'Categoria no trobada.',

            'created' => 'Categoria creada correctament.',
            'updated' => 'Categoria actualitzada correctament.',
            'deleted' => 'Categoria suprimida correctament.',
            'slug-required' => 'El camp slug és obligatori.',
            'slug-unique' => 'El slug ja està en ús.',
            'name-required' => 'El camp name és obligatori.',
            'position-required' => 'El camp position és obligatori.',
            'attributes-required' => 'Cal almenys un atribut filtrable.',
            'description-required-for-mode' => 'Cal una descripció per al mode de visualització escollit.',
            'cannot-delete-root' => 'Les categories arrel i arrel de canal no es poden suprimir.',
            'cannot-delete-channel-root' => 'Aquesta categoria és l\'arrel d\'un canal i no es pot suprimir.',
            'delete-failed' => 'No s\'ha pogut suprimir la categoria.',
            'no-permission' => 'No teniu permís per gestionar les categories.',
            'mass-delete-success' => 'Categories suprimides correctament.',
            'mass-update-status-success' => 'Estat de les categories actualitzat correctament.',
            'mass-delete-indices-required' => 'El camp indices és obligatori i ha de ser una matriu no buida.',
            'mass-update-status-value-required' => 'El camp value és obligatori i ha de ser 0 o 1.',
        ],

        'attribute' => [
            'not-found' => 'Atribut no trobat.',

            'create-success' => 'Atribut creat correctament.',
            'update-success' => 'Atribut actualitzat correctament.',
            'delete-success' => 'Atribut suprimit correctament.',
            'delete-failed' => 'No s\'ha pogut suprimir l\'atribut.',
            'mass-delete-success' => 'Atributs suprimits correctament.',
            'mass-delete-indices-required' => 'El camp indices és obligatori i ha de ser una matriu no buida.',

            'code-immutable' => 'El codi de l\'atribut no es pot canviar un cop creat l\'atribut.',
            'type-immutable' => 'El tipus de l\'atribut no es pot canviar mentre tingui valors de producte.',
            'locale-scope-immutable' => 'No es pot canviar l\'abast de configuració regional mentre l\'atribut tingui valors de producte.',
            'system-attribute' => 'Els atributs del sistema no es poden suprimir.',
            'in-use-family' => 'L\'atribut forma part d\'una o més famílies d\'atributs (IDs de grup: :ids). Elimineu-lo primer d\'aquestes famílies.',

            'option-not-found' => 'Opció d\'atribut no trobada.',
            'option-not-supported' => 'El tipus d\'atribut ":type" no admet opcions. Només els atributs select, multiselect i checkbox poden tenir opcions.',
            'option-in-use' => 'Aquesta opció l\'utilitzen :count producte(s) i no es pot suprimir.',
            'option-delete-success' => 'Opció d\'atribut suprimida correctament.',
        ],

        'family' => [
            'not-found' => 'Família d\'atributs no trobada.',

            'created' => 'Família d\'atributs creada correctament.',
            'updated' => 'Família d\'atributs actualitzada correctament.',
            'deleted' => 'Família d\'atributs suprimida correctament.',
            'code-required' => 'Cal el codi de la família.',
            'code-unique' => 'El codi de la família ha de ser únic.',
            'name-required' => 'Cal el nom de la família.',
            'group-code-required' => 'Cada grup d\'atributs ha de tenir un codi.',
            'group-name-required' => 'Cada grup d\'atributs ha de tenir un nom.',
            'group-column-invalid' => 'Cada columna del grup d\'atributs ha de ser 1 o 2.',
            'group-name-duplicate' => 'El nom d\'un grup d\'atributs ha de ser únic dins de la família d\'atributs.',
            'update-failed' => 'No s\'ha pogut actualitzar la família d\'atributs.',
            'create-failed' => 'No s\'ha pogut crear la família d\'atributs.',
            'default-delete-error' => 'La família d\'atributs predeterminada no es pot suprimir.',
            'last-delete-error' => 'Cal almenys una família d\'atributs.',
            'attribute-product-error' => 'Aquesta família d\'atributs l\'utilitzen un o més productes. Reassigneu aquests productes abans de suprimir-la.',
            'delete-failed' => 'No s\'ha pogut suprimir la família d\'atributs.',
            'no-permission' => 'No teniu permís per gestionar les famílies d\'atributs.',
        ],

        'settings' => [
            'locale' => [
                'not-found' => 'Configuració regional no trobada.',
                'created' => 'Configuració regional creada correctament.',
                'updated' => 'Configuració regional actualitzada correctament.',
                'deleted' => 'Configuració regional suprimida correctament.',
                'cannot-delete-last' => 'Cal almenys una configuració regional — no es pot suprimir l\'última configuració regional que queda.',
                'cannot-delete-channel-default' => 'Aquesta configuració regional és la predeterminada d\'un o més canals i no es pot suprimir.',
                'delete-failed' => 'No s\'ha pogut suprimir la configuració regional.',
                'no-permission' => 'No teniu permís per gestionar les configuracions regionals.',
                'mass-delete-success' => 'Configuracions regionals suprimides correctament.',
                'mass-delete-indices-required' => 'El camp indices és obligatori i ha de ser una matriu no buida.',
            ],

            'theme' => [
                'not-found' => 'Personalització del tema no trobada.',
                'created' => 'Personalització del tema creada correctament.',
                'updated' => 'Personalització del tema actualitzada correctament.',
                'deleted' => 'Personalització del tema suprimida correctament.',
                'delete-failed' => 'No s\'ha pogut suprimir la personalització del tema.',
                'no-permission' => 'No teniu permís per gestionar les personalitzacions del tema.',
                'mass-delete-success' => 'Personalitzacions del tema suprimides correctament.',
                'mass-delete-indices-required' => 'El camp indices és obligatori i ha de ser una matriu no buida.',
                'mass-update-success' => 'Personalitzacions del tema actualitzades correctament.',
                'mass-update-indices-required' => 'El camp indices és obligatori i ha de ser una matriu no buida.',
                'mass-update-value-invalid' => 'El camp value ha de ser 0 o 1.',
            ],

            'inventory-source' => [
                'not-found' => 'Origen d\'inventari no trobat.',
                'created' => 'Origen d\'inventari creat correctament.',
                'updated' => 'Origen d\'inventari actualitzat correctament.',
                'deleted' => 'Origen d\'inventari suprimit correctament.',
                'code-unique' => 'Ja existeix un origen d\'inventari amb aquest codi.',
                'last-delete-error' => 'Cal almenys un origen d\'inventari — no es pot suprimir l\'últim origen que queda.',
                'in-use' => 'Aquest origen d\'inventari està referenciat per inventaris de productes i no es pot suprimir.',
                'delete-failed' => 'No s\'ha pogut suprimir l\'origen d\'inventari.',
                'no-permission' => 'No teniu permís per gestionar els orígens d\'inventari.',
                'mass-delete-success' => 'Orígens d\'inventari suprimits correctament.',
                'mass-delete-indices-required' => 'El camp indices és obligatori i ha de ser una matriu no buida.',
            ],

            'exchange-rate' => [
                'not-found' => 'Tipus de canvi no trobat.',
                'created' => 'Tipus de canvi creat correctament.',
                'updated' => 'Tipus de canvi actualitzat correctament.',
                'deleted' => 'Tipus de canvi suprimit correctament.',
                'duplicate-pair' => 'Ja existeix un tipus de canvi per a aquesta moneda de destinació. Actualitzeu la fila existent o suprimiu-la primer.',
                'delete-failed' => 'No s\'ha pogut suprimir el tipus de canvi.',
                'no-permission' => 'No teniu permís per gestionar els tipus de canvi.',
                'mass-delete-success' => 'Tipus de canvi suprimits correctament.',
                'mass-delete-indices-required' => 'El camp indices és obligatori i ha de ser una matriu no buida.',
                'update-rates-success' => 'Tipus de canvi actualitzats correctament.',
                'update-rates-failed' => 'No s\'han pogut actualitzar els tipus de canvi des del proveïdor extern.',
                'provider-not-configured' => 'No hi ha cap proveïdor extern de tipus de canvi configurat.',
            ],

            'tax-rate' => [
                'not-found' => 'Tipus impositiu no trobat.',
                'created' => 'Tipus impositiu creat correctament.',
                'updated' => 'Tipus impositiu actualitzat correctament.',
                'deleted' => 'Tipus impositiu suprimit correctament.',
                'delete-failed' => 'No s\'ha pogut suprimir el tipus impositiu.',
                'no-permission' => 'No teniu permís per gestionar els tipus impositius.',
                'zip-code-required' => 'El camp zip_code és obligatori quan is_zip és false.',
                'zip-range-required' => 'Calen tant zip_from com zip_to quan is_zip és true.',
            ],

            'tax-category' => [
                'not-found' => 'Categoria d\'impost no trobada.',
                'created' => 'Categoria d\'impost creada correctament.',
                'updated' => 'Categoria d\'impost actualitzada correctament.',
                'deleted' => 'Categoria d\'impost suprimida correctament.',
                'code-unique' => 'El codi ja està en ús.',
                'cannot-delete-in-use' => 'Aquesta categoria d\'impost encara té tipus impositius associats i no es pot suprimir.',
                'delete-failed' => 'No s\'ha pogut suprimir la categoria d\'impost.',
                'no-permission' => 'No teniu permís per gestionar les categories d\'impost.',
            ],

            'role' => [
                'not-found' => 'Rol no trobat.',
                'created' => 'Rol creat correctament.',
                'updated' => 'Rol actualitzat correctament.',
                'deleted' => 'Rol suprimit correctament.',
                'permissions-required' => 'El camp permissions és obligatori quan permission_type és custom.',
                'cannot-delete-in-use' => 'Aquest rol està assignat a un o més administradors i no es pot suprimir.',
                'cannot-delete-last-role' => 'Cal almenys un rol — no es pot suprimir l\'últim rol que queda.',
                'cannot-change-last-all-access' => 'L\'únic rol amb accés total no es pot canviar a un rol personalitzat.',
                'delete-failed' => 'No s\'ha pogut suprimir el rol.',
                'no-permission' => 'No teniu permís per gestionar els rols.',
            ],

            'currency' => [
                // Read
                'not-found' => 'Moneda no trobada.',

                // Create / Update / Delete
                'created' => 'Moneda creada correctament.',
                'updated' => 'Moneda actualitzada correctament.',
                'deleted' => 'Moneda suprimida correctament.',
                'code-required' => 'El camp code és obligatori.',
                'code-size' => 'El codi ha de tenir exactament 3 caràcters alfabètics.',
                'code-unique' => 'El codi ja està en ús.',
                'name-required' => 'El camp name és obligatori.',
                'cannot-delete-last' => 'Cal almenys una moneda — no es pot suprimir l\'última moneda que queda.',
                'cannot-delete-channel-base' => 'Aquesta moneda és la moneda base d\'un o més canals i no es pot suprimir.',
                'delete-failed' => 'No s\'ha pogut suprimir la moneda.',
                'no-permission' => 'No teniu permís per gestionar les monedes.',

                'mass-delete-success' => 'Monedes suprimides correctament.',
                'mass-delete-indices-required' => 'El camp indices és obligatori i ha de ser una matriu no buida.',
            ],

            'channel' => [
                'not-found' => 'Canal no trobat.',

                'created' => 'Canal creat correctament.',
                'updated' => 'Canal actualitzat correctament.',
                'deleted' => 'Canal suprimit correctament.',
                'code-unique' => 'El codi ja està en ús.',
                'hostname-unique' => 'El nom d\'amfitrió ja està en ús.',
                'default-locale-mismatch' => 'El default_locale_id ha d\'aparèixer a la llista de configuracions regionals.',
                'base-currency-mismatch' => 'El base_currency_id ha d\'aparèixer a la llista de monedes.',
                'unknown-locale' => 'Un o més IDs de configuració regional no existeixen.',
                'unknown-currency' => 'Un o més IDs de moneda no existeixen.',
                'unknown-inventory-source' => 'Un o més IDs de inventory_source no existeixen.',
                'unknown-root-category' => 'El root_category_id no existeix.',
                'cannot-delete-last' => 'Cal almenys un canal — no es pot suprimir l\'últim canal que queda.',
                'cannot-delete-default' => 'No es pot suprimir el canal predeterminat de l\'aplicació.',
                'delete-failed' => 'No s\'ha pogut suprimir el canal.',
                'no-permission' => 'No teniu permís per gestionar els canals.',
            ],

            'user' => [
                'not-found' => 'Usuari administrador no trobat.',
                'created' => 'Usuari administrador creat correctament.',
                'updated' => 'Usuari administrador actualitzat correctament.',
                'deleted' => 'Usuari administrador suprimit correctament.',
                'name-required' => 'El camp name és obligatori.',
                'email-required' => 'El camp email és obligatori.',
                'email-invalid' => 'El correu electrònic ha de ser una adreça de correu electrònic vàlida.',
                'email-unique' => 'El correu electrònic ja està en ús.',
                'password-required' => 'El camp password és obligatori.',
                'password-min' => 'La contrasenya ha de tenir almenys 6 caràcters.',
                'role-required' => 'El camp role_id és obligatori.',
                'role-not-found' => 'El rol seleccionat no existeix.',
                'status-invalid' => 'L\'estat ha de ser 0 o 1.',
                'cannot-delete-self' => 'No podeu suprimir el vostre propi compte d\'administrador.',
                'cannot-delete-last-admin' => 'Cal almenys un usuari administrador — no es pot suprimir l\'últim administrador que queda.',
                'cannot-change' => 'No es pot canviar el :name de l\'últim administrador amb accés total.',
                'delete-failed' => 'No s\'ha pogut suprimir l\'usuari administrador.',
                'no-permission' => 'No teniu permís per gestionar els usuaris administradors.',
                'self-delete-password-required' => 'El camp password és obligatori per suprimir el vostre propi compte.',
                'self-delete-incorrect-password' => 'La contrasenya és incorrecta.',
                'self-deleted' => 'S\'ha suprimit el vostre compte d\'administrador.',
            ],

            'data-transfer' => [
                'import' => [
                    'not-found' => 'Importació no trobada.',
                    'deleted' => 'Importació suprimida correctament.',
                    'cancelled' => 'Importació cancel·lada correctament.',
                    'cannot-cancel' => 'Només es poden cancel·lar les importacions pendents o en procés. Estat actual: :state.',
                    'no-permission' => 'No teniu permís per gestionar les importacions de transferència de dades.',
                    'delete-failed' => 'No s\'ha pogut suprimir la importació.',
                    'created' => 'Importació creada correctament.',
                    'updated' => 'Importació actualitzada correctament.',
                    'type-invalid' => 'El tipus seleccionat no és vàlid.',
                    'action-invalid' => 'L\'acció ha de ser una de les següents: append, delete.',
                    'validation-strategy-invalid' => 'L\'estratègia de validació ha de ser una de les següents: stop-on-errors, skip-errors.',
                    'allowed-errors-invalid' => 'Els errors permesos han de ser un enter igual o superior a 0.',
                    'field-separator-required' => 'Cal el separador de camps.',
                    'file-required' => 'Cal un fitxer.',
                    'file-invalid-type' => 'El fitxer ha de ser d\'un dels tipus següents: csv, xls, xlsx, xml.',
                    'nothing-to-import' => 'No hi ha res a importar.',
                    'not-valid' => 'La importació no és vàlida.',
                    'setup-queue-error' => 'La connexió de la cua està configurada com a sync; configureu una cua real per processar les importacions en segon pla.',
                    'file-not-found' => 'No hi ha cap fitxer disponible per descarregar.',
                    'error-report-not-found' => 'No hi ha cap informe d\'errors disponible per descarregar.',
                    'sample-not-found' => 'No hi ha cap fitxer de mostra disponible per al tipus i el format sol·licitats.',
                    'sample-format-invalid' => 'El format de mostra ha de ser un dels següents: csv, xls, xlsx, xml.',
                    'images-archive-too-large' => 'L\'arxiu d\'imatges pot contenir com a màxim :entries fitxers i ocupar com a màxim :size MB un cop descomprimit.',
                ],
            ],
        ],

        'cms' => [
            'page' => [
                'not-found' => 'Pàgina CMS no trobada.',

                'created' => 'Pàgina CMS creada correctament.',
                'url-key-required' => 'El camp url_key és obligatori.',
                'url-key-unique' => 'El url_key ja està en ús.',
                'url-key-invalid' => 'El format del url_key no és vàlid. Utilitzeu només lletres minúscules, números i guions.',
                'page-title-required' => 'El camp page_title és obligatori.',
                'html-content-required' => 'El camp html_content és obligatori.',
                'channels-required' => 'Cal seleccionar almenys un canal.',
                'channels-invalid' => 'Un o més canals no són vàlids.',

                'updated' => 'Pàgina CMS actualitzada correctament.',

                'deleted' => 'Pàgina CMS suprimida correctament.',
                'delete-failed' => 'No s\'ha pogut suprimir la pàgina CMS.',

                'mass-deleted' => 'Pàgines CMS suprimides correctament.',
                'indices-required' => 'El camp indices és obligatori i ha de ser una matriu no buida.',
                'indices-invalid' => 'El camp indices ha de ser una matriu d\'IDs de pàgines CMS.',

                'no-permission' => 'No teniu permís per gestionar les pàgines CMS.',
            ],
        ],

        'marketing' => [
            'catalog-rule' => [
                'not-found' => 'Regla de catàleg no trobada.',
                'created' => 'Regla de catàleg creada correctament.',
                'updated' => 'Regla de catàleg actualitzada correctament.',
                'deleted' => 'Regla de catàleg suprimida correctament.',
                'delete-failed' => 'No s\'ha pogut suprimir la regla de catàleg.',
                'mass-delete-indices-required' => 'No s\'ha proporcionat cap ID de regla de catàleg per a la supressió massiva.',
                'mass-delete-success' => 'Regles de catàleg seleccionades suprimides correctament.',
                'no-permission' => 'No teniu permís per gestionar les regles de catàleg.',
            ],

            'campaign' => [
                'not-found' => 'Campanya no trobada.',
                'created' => 'Campanya creada correctament.',
                'updated' => 'Campanya actualitzada correctament.',
                'deleted' => 'Campanya suprimida correctament.',
                'delete-failed' => 'No s\'ha pogut suprimir la campanya.',
                'no-permission' => 'No teniu permís per gestionar les campanyes.',

                'send' => [
                    'id-required' => 'Cal l\'ID de la campanya.',
                    'inactive' => 'No es pot enviar una campanya inactiva. Activeu-la primer.',
                    'queued' => 'Campanya posada a la cua per a :count destinatari(s).',
                ],
            ],

            'sitemap' => [
                'not-found' => 'Mapa del lloc no trobat.',
                'created' => 'Mapa del lloc creat correctament.',
                'updated' => 'Mapa del lloc actualitzat correctament.',
                'deleted' => 'Mapa del lloc suprimit correctament.',
                'delete-failed' => 'No s\'ha pogut suprimir el mapa del lloc.',
                'no-permission' => 'No teniu permís per gestionar els mapes del lloc.',

                'generate' => [
                    'id-required' => 'Cal l\'ID del mapa del lloc.',
                    'no-channels' => 'Aquest mapa del lloc no cobreix cap canal, de manera que no hi ha res a generar. Assigneu-li primer almenys un canal.',
                    'success' => 'Mapa del lloc regenerat correctament.',
                    'failed' => 'Ha fallat la generació del mapa del lloc: :message',
                ],
            ],

            'cart-rule' => [
                'not-found' => 'Regla del carretó no trobada.',
                'id-required' => 'Cal un ID de regla del carretó.',
                'copy-of' => 'Còpia de :value',
                'copied' => 'Regla del carretó copiada correctament.',
                'created' => 'Regla del carretó creada correctament.',
                'updated' => 'Regla del carretó actualitzada correctament.',
                'deleted' => 'Regla del carretó suprimida correctament.',
                'delete-failed' => 'No s\'ha pogut suprimir la regla del carretó.',
                'no-permission' => 'No teniu permís per gestionar les regles del carretó.',
                'mass-delete-success' => 'Regles del carretó suprimides correctament.',
                'mass-delete-indices-required' => 'El camp indices és obligatori i ha de ser una matriu no buida.',
                'action-type-invalid' => 'El action_type ha de ser un dels següents: by_percent, by_fixed, cart_fixed, buy_x_get_y.',
                'coupon-code-required' => 'Cal un coupon_code quan coupon_type=1 i use_auto_generation=0.',
                'coupon-code-unique' => 'Aquest codi de cupó ja està en ús.',
            ],

            'template' => [
                'campaign-associated' => 'Aquesta plantilla de correu electrònic l\'utilitza una campanya, de manera que no es pot suprimir. Elimineu-la primer de la campanya.',
                'not-found' => 'Plantilla de correu electrònic no trobada.',
                'created' => 'Plantilla de correu electrònic creada correctament.',
                'updated' => 'Plantilla de correu electrònic actualitzada correctament.',
                'deleted' => 'Plantilla de correu electrònic suprimida correctament.',
                'delete-failed' => 'No s\'ha pogut suprimir la plantilla de correu electrònic.',
                'status-invalid' => 'L\'estat ha de ser un dels següents: active, inactive, draft.',
                'no-permission' => 'No teniu permís per gestionar les plantilles de correu electrònic.',
            ],

            'event' => [
                'campaign-associated' => 'Aquest esdeveniment l\'utilitza una campanya, de manera que no es pot suprimir. Elimineu-lo primer de la campanya.',
                'not-found' => 'Esdeveniment de màrqueting no trobat.',
                'created' => 'Esdeveniment de màrqueting creat correctament.',
                'updated' => 'Esdeveniment de màrqueting actualitzat correctament.',
                'deleted' => 'Esdeveniment de màrqueting suprimit correctament.',
                'delete-failed' => 'No s\'ha pogut suprimir l\'esdeveniment de màrqueting.',
                'no-permission' => 'No teniu permís per gestionar els esdeveniments de màrqueting.',
            ],

            'search-synonym' => [
                'not-found' => 'Sinònim de cerca no trobat.',
                'created' => 'Sinònim de cerca creat correctament.',
                'updated' => 'Sinònim de cerca actualitzat correctament.',
                'deleted' => 'Sinònim de cerca suprimit correctament.',
                'mass-deleted' => 'Sinònims de cerca suprimits correctament.',
                'delete-failed' => 'No s\'ha pogut suprimir el sinònim de cerca.',
                'indices-required' => 'El camp indices és obligatori i ha de ser una matriu no buida d\'IDs de sinònims de cerca.',
                'no-permission' => 'No teniu permís per gestionar els sinònims de cerca.',
            ],

            'url-rewrite' => [
                'not-found' => 'Reescriptura d\'URL no trobada.',
                'created' => 'Reescriptura d\'URL creada correctament.',
                'updated' => 'Reescriptura d\'URL actualitzada correctament.',
                'deleted' => 'Reescriptura d\'URL suprimida correctament.',
                'delete-failed' => 'No s\'ha pogut suprimir la reescriptura d\'URL.',
                'mass-deleted' => 'Reescriptures d\'URL suprimides correctament.',
                'indices-required' => 'Cal almenys un ID de reescriptura d\'URL.',
                'no-permission' => 'No teniu permís per gestionar les reescriptures d\'URL.',
            ],

            'cart-rule-coupon' => [
                'cart-rule-not-found' => 'Regla del carretó no trobada.',
                'not-found' => 'Cupó no trobat.',
                'code-taken' => 'Ja existeix un cupó amb aquest codi.',
                'format-invalid' => 'El format ha de ser un dels següents: alphabetic, alphanumeric, numeric.',
                'indices-required' => 'El camp indices és obligatori i ha de ser una matriu no buida d\'IDs de cupons.',
                'created' => 'Cupó creat correctament.',
                'generated' => 'S\'han generat :count cupó(ns) correctament.',
                'deleted' => 'Cupó suprimit correctament.',
                'mass-deleted' => 'S\'han suprimit :count cupó(ns) correctament.',
                'no-permission' => 'No teniu permís per gestionar els cupons de les regles del carretó.',
            ],

            'subscriber' => [
                'not-found' => 'Subscriptor del butlletí no trobat.',
                'updated' => 'Subscripció actualitzada correctament.',
                'deleted' => 'Subscripció suprimida correctament.',
                'is-subscribed-required' => 'El camp is_subscribed és obligatori.',
                'no-permission' => 'No teniu permís per gestionar els subscriptors del butlletí.',
            ],

            'search-term' => [
                'not-found' => 'Terme de cerca no trobat.',
                'updated' => 'Terme de cerca actualitzat correctament.',
                'deleted' => 'Terme de cerca suprimit correctament.',
                'mass-deleted' => 'Termes de cerca suprimits correctament.',
                'term-required' => 'El camp term és obligatori.',
                'redirect-url-invalid' => 'El redirect_url ha de ser un URL vàlid.',
                'indices-required' => 'Cal proporcionar almenys un ID a indices.',
                'no-permission' => 'No teniu permís per gestionar els termes de cerca.',
            ],
        ],
        'auth' => [
            'configuration' => [
                'title' => 'Autenticació d\'administrador',
                'info' => 'Durada dels tokens, límits de peticions i regles de seguretat per als inicis de sessió de l\'API d\'administració i els tokens d\'API.',

                'tokens' => [
                    'title' => 'Durada dels tokens',
                    'info' => 'Quant de temps són vàlids els tokens d\'accés, els tokens de refresc i les sessions. Els canvis s\'apliquen als tokens emesos després.',
                    'access-token-ttl' => 'Durada del token d\'accés (minuts)',
                    'access-token-ttl-info' => 'Entre 5 i 60 minuts. Per defecte 15.',
                    'refresh-token-ttl' => 'Durada del token de refresc (dies)',
                    'refresh-token-ttl-info' => 'Entre 1 i 90 dies. Per defecte 30.',
                    'session-lifetime' => 'Durada màxima de la sessió (dies)',
                    'session-lifetime-info' => 'Entre 1 i 365 dies. Per defecte 90. Després, l\'administrador ha de tornar a iniciar sessió, per molt que s\'hagi refrescat la sessió.',
                ],

                'rate-limits' => [
                    'title' => 'Límits de peticions',
                    'info' => 'Màxim de peticions a l\'API d\'administració per minut.',
                    'session-requests-per-minute' => 'Peticions per minut per administrador connectat',
                    'api-token-requests-per-minute' => 'Peticions per minut per token d\'API',
                ],

                'security' => [
                    'title' => 'Seguretat',
                    'info' => 'Regles de revocació, caducitat dels tokens d\'API i vinculació de domini.',
                    'revoke-sessions-on-password-change' => 'Revocar les sessions en canviar la contrasenya',
                    'revoke-api-tokens-on-password-change' => 'Revocar els tokens d\'API en canviar la contrasenya',
                    'allow-non-expiring-api-tokens' => 'Permetre tokens d\'API sense caducitat',
                    'domain-binding-mode' => 'Mode de vinculació de domini',
                    'domain-binding-mode-info' => 'DNS comprova que la petició ve d\'una adreça IP a la qual resol el domini del token. Origin comprova la capçalera Origin o Referer i només és adequat per a navegadors.',
                    'domain-binding-dns' => 'DNS (adreça IP del client)',
                    'domain-binding-origin' => 'Capçalera Origin',
                ],
            ],

            'errors' => [
                'unauthenticated' => 'No esteu autenticat.',
                'access-token-expired' => 'El token d\'accés ha caducat. Refresca\'l per continuar.',
                'api-token-expired' => 'El token d\'API ha caducat.',
                'refresh-token-invalid' => 'El token de refresc no és vàlid o ha caducat. Torna a iniciar sessió.',
            ],
        ],
    ],

    'integration' => [
        'menu' => [
            'title' => 'Integració',
            'tokens' => 'Fitxes',
        ],

        'history' => [
            'menu' => [
                'title' => 'Història',
            ],

            'acl' => [
                'title' => 'Historial de canvis de l\'API',
                'view' => 'Veure',
                'delete' => 'Suprimeix l\'historial',
            ],

            'index' => [
                'title' => 'Historial de canvis de l\'API',
                'info' => 'Cada creació, actualització i supressió feta a través de l\'API d\'administració, amb qui ho va fer, quin testimoni i què ha canviat.',
                'cleanup-btn' => 'Suprimeix els registres més antics',
                'cleanup-days' => 'Suprimeix els registres més antics que aquests dies',
                'cleanup-confirm' => 'Vols suprimir tot l\'historial anterior al nombre de dies indicat? Això no es pot desfer.',
            ],

            'view' => [
                'title' => 'Canviar',
                'back-btn' => 'Enrere',
                'admin' => 'Admin',
                'token' => 'Token',
                'action' => 'Acció',
                'resource' => 'Recurs',
                'method' => 'Mètode',
                'ip' => 'Adreça IP',
                'date' => 'Data',
                'version' => 'Versió',
                'url' => 'Punt final',
                'request-details' => 'Detalls de la sol·licitud',
                'changes' => 'Canvis',
                'field' => 'Camp',
                'old' => 'Antic valor',
                'new' => 'Nou valor',
                'no-field-changes' => 'No s\'han registrat canvis a nivell de camp per a aquesta entrada.',
                'same-request' => 'Altres canvis en la mateixa sol·licitud',
                'version-chain' => 'Historial de versions d\'aquest registre',
            ],

            'datagrid' => [
                'id' => 'ID',
                'date' => 'Data',
                'admin' => 'Admin',
                'token' => 'Token',
                'action' => 'Acció',
                'operation' => 'Funcionament',
                'resource' => 'Recurs',
                'version' => 'Versió',
                'method' => 'Mètode',
                'ip' => 'IP',
                'view' => 'Veure',
                'delete' => 'Suprimeix',
            ],

            'events' => [
                'created' => 'Creat',
                'updated' => 'Actualitzat',
                'deleted' => 'S\'ha suprimit',
            ],

            'deleted' => 'S\'han suprimit :count registres d\'historial.',
            'cleanup-input-required' => 'Proporcioneu un nombre de dies o una data per netejar.',
        ],

        'acl' => [
            'title' => 'Integració',
            'view' => 'Veure',
            'create' => 'Crea integració',
            'edit' => 'Edita la integració',
            'delete' => 'Revoca el testimoni d\'integració',
            'generate' => 'Generar testimoni d\'integració',
            'regenerate' => 'Regenera el testimoni d\'integració',
        ],

        'index' => [
            'title' => 'Integracions',
            'create-btn' => 'Crea integració',
        ],

        'create' => [
            'title' => 'Crea integració',
            'save-btn' => 'Desa',
            'back-btn' => 'Enrere',
        ],

        'edit' => [
            'title' => 'Edita la integració',
            'save-btn' => 'Desa',
            'back-btn' => 'Enrere',
            'generate-btn' => 'Genera un testimoni',
            'regenerate-btn' => 'Regenera el testimoni',
            'revoke-btn' => 'Revoca el testimoni',
            'copy-btn' => 'Còpia',
            'token-warning' => 'Desa aquest testimoni ara; no es tornarà a mostrar.',
            'token-label' => 'Token',
            'not-generated' => 'Encara no s\'ha generat',
            'masked' => '(Es emmagatzema: només es mostra una vegada a la generació)',
            'history-banner' => 'Aquest testimoni ja no està actiu.',
        ],

        'fields' => [
            'name' => 'Nom',
            'description' => 'Descripció',
            'assign-user' => 'Assigna usuari',
            'permission-type' => 'Tipus de permís',
            'access-control' => 'Control d\'accés',
            'general' => 'General',
            'token-settings' => 'Configuració del testimoni',
            'valid-till' => 'Vàlid fins a',
            'rate-limit-per-minute' => 'Límit de velocitat (per minut)',
            'rate-limit-per-day' => 'Límit de tarifa (per dia)',
            'never-expires' => 'Mai caduca',
            'expires-on' => 'Caduca el',
            'unlimited' => 'Il·limitat',
            'limit-to' => 'Limitar a',
            'requests-per-minute' => 'peticions / minut',
            'requests-per-day' => 'sol·licituds/dia',
            'select-admin' => 'Seleccioneu un administrador',
            'no-available-admins' => 'No hi ha administradors disponibles: tots els administradors ja tenen un testimoni actiu.',
            'same-as-web-hint' => 'El testimoni reflectirà en directe els permisos actuals del rol de l\'administrador assignat.',
            'ip-allowlist' => 'Llista d\'IP permeses',
            'ip-any' => 'Qualsevol IP (per defecte)',
            'ip-restricted' => 'Restringit a IP específiques',
            'ip-list-hint' => 'Una entrada per línia. Admet IPv4, IPv6 i CIDR (per exemple, 10.0.0.0/24 o 2001:db8::/32). Deixeu-lo en blanc per permetre totes les IP.',
        ],

        'permission-type' => [
            'all' => 'Tots',
            'custom' => 'Personalitzat',
            'same-as-web' => 'Igual que el permís web',
        ],

        'status' => [
            'draft' => 'Esborrany',
            'active' => 'Actius',
            'revoked' => 'Revocat',
            'regenerated' => 'Regenerat',
        ],

        'datagrid' => [
            'id' => 'ID',
            'name' => 'Nom',
            'admin' => 'Admin',
            'token' => 'Token',
            'status' => 'Estat',
            'permission-type' => 'Tipus de permís',
            'expires-at' => 'Vàlid fins a',
            'last-used-at' => 'Darrer ús',
            'created-at' => 'Creat a',
            'edit' => 'Edita',
            'revoke' => 'Revocar',
        ],

        'messages' => [
            'draft-created' => 'Integració creada. Genereu el testimoni per començar a utilitzar-lo.',
            'updated' => 'La integració s\'ha actualitzat correctament.',
            'generated' => 'Token generat. Copieu-lo ara; no es tornarà a mostrar.',
            'regenerated' => 'Token regenerat. Copieu ara el testimoni nou; no es tornarà a mostrar.',
            'revoked' => 'El testimoni s\'ha revocat correctament.',
            'generate-only-draft' => 'Només es poden generar el seu testimoni d\'integracions esborranys.',
            'regenerate-only-active' => 'Només es poden regenerar fitxes actives.',
            'cannot-edit-historic' => 'Les fitxes revocades o regenerades no es poden editar.',
            'already-inactive' => 'Aquest testimoni ja està inactiu.',
        ],

        'errors' => [
            'admin-has-token' => 'L\'administrador seleccionat ja té un testimoni d\'integració actiu.',
            'only-draft-generate' => 'Només es poden generar testimonis en esborrany.',
        ],

        'validation' => [
            'ip-invalid' => 'Cada IP permesa ha de ser una adreça IPv4 o IPv6 vàlida (admet la notació CIDR).',
            'cidr-prefix-invalid' => 'El prefix CIDR no és vàlid per a la versió IP determinada.',
        ],

        'configuration' => [
            'api' => [
                'title' => 'API',
                'info' => 'Configuració de l\'API Bagisto i els seus mòduls d\'administració.',
            ],

            'integration' => [
                'title' => 'Integració',
                'info' => 'Gestioneu el connector d\'integració de l\'API utilitzat per emetre testimonis d\'API d\'administrador.',
            ],

            'settings' => [
                'title' => 'Configuració del mòdul',
                'info' => 'Activeu o desactiveu el connector d\'integració de l\'API. Quan està desactivat, el seu menú de la barra lateral s\'amaga i les seves pàgines tornen 404.',
                'enable' => 'Activa el mòdul d\'integració de l\'API',
            ],
        ],

        'emails' => [
            'generated' => [
                'subject' => 'S\'ha generat un testimoni d\'API nou: :name',
                'greeting' => 'S\'acaba de generar un testimoni d\'integració de l\'API anomenat ":name" al vostre compte.',
            ],

            'regenerated' => [
                'subject' => 'El vostre testimoni de l\'API s\'ha regenerat: :name',
                'greeting' => 'El testimoni d\'integració de l\'API anomenat ":name" s\'acaba de regenerar. El testimoni anterior ha deixat de funcionar; només el nou és vàlid.',
            ],

            'revoked' => [
                'subject' => 'S\'ha revocat el vostre testimoni de l\'API: :name',
                'greeting' => 'El testimoni d\'integració de l\'API anomenat ":name" s\'ha revocat. Qualsevol client que l\'utilitzi ha perdut l\'accés.',
            ],

            'details' => [
                'name' => 'Nom del testimoni',
                'date' => 'Data',
                'ip' => 'Des de la IP',
            ],

            'revoke-hint' => 'Si no us esperàveu això, revoqueu el testimoni immediatament utilitzant el botó següent.',
            'revoke-btn' => 'Revoca aquest testimoni',
            'revoke-expiry' => 'Aquest enllaç de revocació és vàlid durant 7 dies. Després d\'això, inicieu la sessió al tauler d\'administració per gestionar el testimoni.',
            'no-action' => 'No cal cap acció: aquest correu electrònic només és una confirmació.',
        ],

        'revoke-confirmation' => [
            'title' => 'Revoca el testimoni de l\'API',
            'confirm-title' => 'Voleu revocar aquest testimoni?',
            'confirm-message' => 'El testimoni ":name" deixarà de funcionar immediatament per a tots els clients que l\'utilitzin.',
            'confirm-button' => 'Revoca el testimoni',
            'success-title' => 'Token revocat',
            'success-message' => 'El testimoni ":name" s\'ha revocat. Qualsevol client que l\'utilitzi ha perdut l\'accés immediatament.',
            'already-inactive-title' => 'El testimoni ja està inactiu',
            'already-inactive-message' => 'El testimoni ":name" ja s\'ha revocat o regenerat. No cal més acció.',
        ],

        'confirm' => [
            'generate' => [
                'title' => 'Genera un testimoni',
                'message' => 'Voleu generar el testimoni ara? El text sense format es mostrarà només una vegada; copieu-lo abans de sortir de la pàgina.',
            ],

            'regenerate' => [
                'title' => 'Regenera el testimoni',
                'message' => 'Voleu regenerar el testimoni? El testimoni antic deixarà de funcionar immediatament i el nou text sense format només es mostrarà una vegada.',
            ],

            'revoke' => [
                'title' => 'Revoca el testimoni',
                'message' => 'Vols revocar aquest testimoni? Qualsevol client que l\'utilitzi perdrà l\'accés immediatament. Aquesta acció no es pot desfer.',
            ],
        ],
    ],
];
