<?php

return [
    'graphql' => [
        'customer-refund' => [
            'id-required' => 'ID-ul rambursării clientului este obligatoriu',
            'invalid-id-format' => 'Format ID nevalid. Se așteaptă formatul IRI, de exemplu "/api/shop/customer-refunds/1", sau un ID numeric',
            'not-found' => 'Rambursarea clientului cu ID-ul ":id" nu a fost găsită',
        ],

        'reorder' => [
            'disabled-in-settings' => 'Plasarea din nou a comenzilor este dezactivată în setările magazinului.',
            'cannot-reorder' => 'Această comandă nu poate fi plasată din nou (comandă de vizitator sau unul sau mai multe articole nu mai sunt disponibile pentru cumpărare).',
        ],

        'gdpr' => [
            'message-too-long' => 'Mesajul nu poate depăși :max caractere.',
        ],

        'headers' => [
            'unknown-channel' => 'Canalul ":code" din antetul X-Channel nu există.',
        ],

        'cart' => [
            'authentication-required' => 'Tokenul de autentificare este obligatoriu',
            'invalid-token' => 'Token de autentificare nevalid sau expirat',
            'unauthorized-access' => 'Acces neautorizat la coș',
            'authenticated-only' => 'Doar utilizatorii autentificați își pot prelua coșurile',
            'merge-requires-auth' => 'Îmbinarea coșului de vizitator necesită autentificare',
            'unknown-operation' => 'Operațiune de coș necunoscută',

            'cart-not-found' => 'Coșul nu a fost găsit',
            'guest-cart-not-found' => 'Coșul de vizitator nu a fost găsit',
            'product-not-found' => 'Produsul nu a fost găsit',

            'product-id-quantity-required' => 'ID-ul produsului și cantitatea sunt obligatorii',
            'cart-item-id-quantity-required' => 'ID-ul articolului din coș și cantitatea sunt obligatorii',
            'cart-item-id-required' => 'ID-ul articolului din coș este obligatoriu',
            'item-ids-required' => 'Lista de ID-uri ale articolelor este obligatorie',
            'coupon-code-required' => 'Codul de cupon este obligatoriu',
            'address-data-required' => 'Țara, județul și codul poștal sunt obligatorii',

            'add-product-failed' => 'Adăugarea produsului în coș a eșuat',
            'update-item-failed' => 'Actualizarea articolului din coș a eșuat',
            'remove-item-failed' => 'Eliminarea articolului din coș a eșuat',
            'apply-coupon-failed' => 'Aplicarea cuponului a eșuat',
            'remove-coupon-failed' => 'Eliminarea cuponului a eșuat',
            'move-to-wishlist-failed' => 'Mutarea articolului în lista de dorințe a eșuat',
            'estimate-shipping-failed' => 'Estimarea costului de livrare a eșuat',

            'product-added-successfully' => 'Produsul a fost adăugat în coș cu succes',
            'guest-cart-merged' => 'Coșul de vizitator a fost îmbinat cu succes',
            'using-authenticated-cart' => 'Se folosește coșul clientului autentificat',
            'cart-item-not-found' => 'Articolul din coș nu a fost găsit',
            'new-guest-cart-created' => 'A fost creat un nou coș de vizitator cu un token de sesiune unic',
            'select-items-to-remove' => 'Vă rugăm să selectați articolele de eliminat',
            'select-items-to-move-wishlist' => 'Vă rugăm să selectați articolele de mutat în lista de dorințe',
            'invalid-or-expired-token' => 'Tokenul coșului este nevalid sau expirat. Vă rugăm să creați un coș nou.',
            'invalid-token-of-login-user' => 'Tokenul utilizatorului autentificat este nevalid.',
        ],

        'token-verification' => [
            'invalid-operation' => 'Operațiune nevalidă',
            'invalid-input-data' => 'Date de intrare nevalide',
            'token-required' => 'Tokenul este obligatoriu',
            'invalid-token-format' => 'Format de token nevalid',
            'token-not-found-or-expired' => 'Tokenul nu a fost găsit sau a expirat',
            'customer-not-found' => 'Clientul nu a fost găsit',
            'customer-account-suspended' => 'Contul clientului este suspendat',
            'error-verifying-token' => 'Eroare la verificarea tokenului',
            'token-is-valid' => 'Tokenul este valid',
        ],

        'forgot-password' => [
            'invalid-operation' => 'Operațiune nevalidă',
            'invalid-input-data' => 'Date de intrare nevalide',
            'email-required' => 'Adresa de email este obligatorie',
            'reset-link-sent' => 'Linkul de resetare a fost trimis cu succes pe adresa dvs. de email',
            'email-not-found' => 'Adresa de email nu a fost găsită',
            'error-sending-reset-link' => 'A apărut o eroare la trimiterea linkului de resetare',
        ],

        'logout' => [
            'invalid-operation' => 'Operațiune nevalidă',
            'invalid-input-data' => 'Date de intrare nevalide',
            'token-required' => 'Tokenul este obligatoriu',
            'invalid-token-format' => 'Format de token nevalid',
            'logged-out-successfully' => 'V-ați deconectat cu succes',
            'token-not-found-or-expired' => 'Tokenul nu a fost găsit sau a expirat deja',
            'error-during-logout' => 'Eroare la deconectare',
        ],

        'address' => [
            'deleted-successfully' => 'Adresa a fost ștearsă cu succes',
            'authentication-required' => 'Tokenul de autentificare este obligatoriu',
            'invalid-token' => 'Token nevalid sau expirat',
            'unknown-operation' => 'Operațiune necunoscută',
            'address-id-required' => 'ID-ul adresei este obligatoriu',
            'address-not-found' => 'Adresa nu a fost găsită sau nu aparține acestui client',
            'retrieved' => 'Adresele au fost preluate cu succes',
            'fetch-failed' => 'Preluarea adreselor a eșuat:',
        ],

        'customer-profile' => [
            'authentication-required' => 'Tokenul de autentificare este obligatoriu. Vă rugăm să furnizați tokenul în datele de intrare ale interogării',
            'invalid-token' => 'Token nevalid sau expirat',
        ],

        'customer' => [
            'password-mismatch' => 'Parola și confirmarea parolei nu coincid',
            'confirm-password-required' => 'Confirmarea parolei este obligatorie la schimbarea parolei',
            'unauthenticated' => 'Neautentificat. Vă rugăm să vă autentificați pentru a efectua această acțiune',
        ],

        'product-review' => [
            'product-id-required' => 'ID-ul produsului este obligatoriu',
            'product-not-found' => 'Produsul nu a fost găsit',
            'rating-invalid' => 'Evaluarea trebuie să fie între 1 și 5',
            'title-required' => 'Titlul recenziei este obligatoriu',
            'comment-required' => 'Comentariul recenziei este obligatoriu',
        ],

        'product' => [
            'not-found' => 'Produsul nu a fost găsit',
            'not-found-with-sku' => 'Nu a fost găsit niciun produs cu SKU-ul',
            'not-found-with-url-key' => 'Nu a fost găsit niciun produs cu cheia URL',
            'parameters-required' => 'Trebuie furnizat cel puțin unul dintre următorii parametri: "sku", "id", "urlKey"',
        ],

        'auth' => [
            'no-token-provided' => 'Nu a fost furnizat niciun token de autentificare. Vă rugăm să furnizați tokenul în antetul Authorization ca "Bearer <token>" sau în câmpul input.token',
            'invalid-or-expired-token' => 'Token nevalid sau expirat',
            'request-not-found' => 'Cererea nu a fost găsită în context',
            'token-required' => 'Tokenul de autentificare este obligatoriu. Vă rugăm să furnizați tokenul în antetul Authorization ca "Bearer <token>"',
            'unknown-resource' => 'Resursă necunoscută',
            'cannot-update-other-profile' => 'Neautorizat: nu puteți actualiza profilul altui client',
        ],

        'upload' => [
            'invalid-base64' => 'Date de imagine codificate base64 nevalide',
            'size-exceeds-limit' => 'Dimensiunea imaginii nu trebuie să depășească 5MB',
            'invalid-format' => 'Format de imagine nevalid. Vă rugăm să furnizați o imagine codificată base64 cu schema data URI (data:image/jpeg;base64,...)',
            'failed' => 'Încărcarea imaginii a eșuat',
        ],

        'attribute' => [
            'code-already-exists' => 'Codul atributului există deja',
        ],

        'login' => [
            'invalid-credentials' => 'Email sau parolă nevalidă',
            'account-suspended' => 'Contul dvs. a fost suspendat',
            'account-inactive' => 'Contul dvs. nu este activat. Vă rugăm să contactați asistența.',
            'email-not-verified' => 'Vă rugăm să vă verificați adresa de email înainte de a vă autentifica.',
            'successful' => 'V-ați autentificat cu succes',
            'invalid-request' => 'Cerere de autentificare nevalidă',
        ],

        'social-login' => [
            'signed-in' => 'Autentificare reușită.',
            'token-required' => 'Este necesar un token de autentificare socială.',
            'invalid-token' => 'Tokenul de autentificare socială este nevalid sau expirat. Vă rugăm să încercați din nou.',
            'wrong-audience' => 'Acest token a fost emis pentru o altă aplicație.',
            'not-configured' => 'Acest furnizor de autentificare socială nu este configurat. Vă rugăm să contactați asistența.',
            'email-required' => 'Furnizorul nu a partajat o adresă de email. Vă rugăm să vă înregistrați folosind adresa de email.',
            'account-inactive' => 'Contul dvs. nu este activat. Vă rugăm să contactați asistența.',
            'provider-not-supported' => 'Acest furnizor de autentificare socială nu este acceptat.',
            'provider-disabled' => 'Acest furnizor de autentificare socială nu este activat.',
        ],

        'checkout' => [
            'invalid-input' => 'Date de intrare nevalide pentru operațiunea de finalizare a comenzii',
            'billing-address-required' => 'Adresa de facturare este obligatorie',
            'shipping-address-required' => 'Adresa de livrare este obligatorie pentru expedieri',
            'address-save-failed' => 'Salvarea adresei a eșuat',
            'address-saved' => 'Adresa a fost salvată cu succes',
            'shipping-method-required' => 'Metoda de livrare este obligatorie',
            'invalid-shipping-method' => 'Metodă de livrare nevalidă sau indisponibilă',
            'shipping-method-save-failed' => 'Salvarea metodei de livrare a eșuat',
            'shipping-method-saved' => 'Metoda de livrare a fost salvată cu succes',
            'shipping-method-error' => 'Eroare la salvarea metodei de livrare',
            'payment-method-required' => 'Metoda de plată este obligatorie',
            'invalid-payment-method' => 'Metodă de plată nevalidă sau indisponibilă',
            'paypal-order-id-required' => 'ID-ul comenzii PayPal este obligatoriu pentru a confirma o plată PayPal Smart Button.',
            'paypal-smart-button-payment-flow' => 'Plățile PayPal Smart Button sunt încasate prin checkout-payments/initiate și checkout-payments/confirm, nu prin plasarea directă a comenzii.',
            'payment-method-save-failed' => 'Salvarea metodei de plată a eșuat',
            'payment-method-saved' => 'Metoda de plată a fost salvată cu succes',
            'payment-method-error' => 'Eroare la salvarea metodei de plată',
            'order-creation-failed' => 'Crearea comenzii a eșuat: ID-ul comenzii este nul sau comanda nu a fost salvată',
            'order-retrieval-failed' => 'Preluarea comenzii create a eșuat',
            'order-creation-error' => 'Crearea comenzii a eșuat',
            'cart-empty' => 'Coșul este gol',
            'account-suspended' => 'Contul dvs. a fost suspendat. Vă rugăm să contactați asistența.',
            'account-inactive' => 'Contul dvs. este inactiv. Vă rugăm să contactați asistența.',
            'minimum-order-not-met' => 'Valoarea minimă a comenzii este :amount',
            'guest-checkout-not-allowed' => 'Vă rugăm să vă autentificați pentru a finaliza comanda. Finalizarea comenzii ca vizitator nu este disponibilă pentru acest coș.',
            'email-required' => 'Adresa de email este obligatorie pentru crearea comenzii',
            'unknown-operation' => 'Operațiune de finalizare a comenzii necunoscută',
        ],

        'customer-addresses' => [
            'token-required' => 'Tokenul este obligatoriu pentru a prelua adresele clientului',
            'invalid-or-expired-token' => 'Token nevalid sau expirat',
            'token-validation-failed' => 'Validarea tokenului a eșuat',
        ],

        'product' => [
            'type' => 'Tip produs',
            'attribute-family' => 'Familie de atribute',
            'sku' => 'SKU',
            'name' => 'Nume',
            'description' => 'Descriere',
            'short-description' => 'Descriere scurtă',
            'status' => 'Stare',
            'new' => 'Nou',
            'featured' => 'Recomandat',
            'price' => 'Preț',
            'special-price' => 'Preț special',
            'weight' => 'Greutate',
            'cost' => 'Cost',
            'length' => 'Lungime',
            'width' => 'Lățime',
            'height' => 'Înălțime',
            'color' => 'Culoare',
            'size' => 'Mărime',
            'brand' => 'Marcă',
            'super-attributes' => 'Super atribute',
        ],

        'compare-item' => [
            'id-required' => 'ID-ul articolului de comparare este obligatoriu',
            'invalid-id-format' => 'Format ID nevalid. Se așteaptă formatul IRI, de exemplu "/api/shop/compare-items/1", sau un ID numeric',
            'not-found' => 'Articolul de comparare nu a fost găsit',
            'product-id-required' => 'ID-ul produsului este obligatoriu',
            'customer-id-required' => 'ID-ul clientului este obligatoriu',
            'product-not-found' => 'Produsul nu a fost găsit',
            'customer-not-found' => 'Clientul nu a fost găsit',
            'already-exists' => 'Acest produs se află deja în lista dvs. de comparare',
            'disabled' => 'Compararea produselor este dezactivată pentru acest magazin.',
        ],

        'wishlist' => [
            'disabled' => 'Lista de dorințe este dezactivată pentru acest magazin.',
        ],

        'downloadable-product' => [
            'download-link-not-found' => 'Linkul de descărcare nu a fost găsit sau a expirat',
            'purchased-link-not-found' => 'Linkul achiziționat nu a fost găsit',
            'file-not-found' => 'Fișierul nu a fost găsit',
            'download-successful' => 'Fișierul este gata pentru descărcare',
            'token-required' => 'Tokenul de descărcare este obligatoriu',
            'invalid-token' => 'Token de descărcare nevalid sau expirat',
            'token-expired' => 'Tokenul de descărcare a expirat. Vă rugăm să generați un token nou',
            'access-denied' => 'Acces refuzat: nu aveți permisiunea de a descărca acest fișier',
            'redirect-external-url' => 'Redirecționare către URL-ul extern de descărcare',
            'file-error' => 'A apărut o eroare la procesarea cererii dvs. de descărcare',
            'unauthorized-access' => 'Acces neautorizat la resursa de descărcare',
        ],
    ],

    'integration' => [
        'menu' => [
            'title' => 'Integrare',
            'tokens' => 'Tokenuri',
        ],

        'history' => [
            'menu' => [
                'title' => 'Istoric',
            ],

            'acl' => [
                'title' => 'Istoric modificări API',
                'view' => 'Vizualizare',
                'delete' => 'Șterge istoricul',
            ],

            'index' => [
                'title' => 'Istoric modificări API',
                'info' => 'Fiecare creare, actualizare și ștergere efectuată prin API-ul de administrare, cu cine a făcut-o, ce token a fost folosit și ce s-a modificat.',
                'cleanup-btn' => 'Șterge jurnalele mai vechi',
                'cleanup-days' => 'Șterge jurnalele mai vechi de acest număr de zile',
                'cleanup-confirm' => 'Ștergeți tot istoricul mai vechi decât numărul de zile indicat? Această acțiune nu poate fi anulată.',
            ],

            'view' => [
                'title' => 'Modificare',
                'back-btn' => 'Înapoi',
                'admin' => 'Administrator',
                'token' => 'Token',
                'action' => 'Acțiune',
                'resource' => 'Resursă',
                'method' => 'Metodă',
                'ip' => 'Adresă IP',
                'date' => 'Dată',
                'version' => 'Versiune',
                'url' => 'Endpoint',
                'request-details' => 'Detalii cerere',
                'changes' => 'Modificări',
                'field' => 'Câmp',
                'old' => 'Valoare veche',
                'new' => 'Valoare nouă',
                'no-field-changes' => 'Nu au fost înregistrate modificări la nivel de câmp pentru această intrare.',
                'same-request' => 'Alte modificări din aceeași cerere',
                'version-chain' => 'Istoricul versiunilor acestei înregistrări',
            ],

            'datagrid' => [
                'id' => 'ID',
                'date' => 'Dată',
                'admin' => 'Administrator',
                'token' => 'Token',
                'action' => 'Acțiune',
                'operation' => 'Operațiune',
                'resource' => 'Resursă',
                'version' => 'Versiune',
                'method' => 'Metodă',
                'ip' => 'IP',
                'view' => 'Vizualizare',
                'delete' => 'Șterge',
            ],

            'events' => [
                'created' => 'Creat',
                'updated' => 'Actualizat',
                'deleted' => 'Șters',
            ],

            'deleted' => ':count înregistrare/înregistrări de istoric au fost șterse.',
            'cleanup-input-required' => 'Furnizați un număr de zile sau o dată pentru curățare.',
        ],

        'acl' => [
            'title' => 'Integrare',
            'view' => 'Vizualizare',
            'create' => 'Creează integrare',
            'edit' => 'Editează integrarea',
            'delete' => 'Revocă tokenul de integrare',
            'generate' => 'Generează token de integrare',
            'regenerate' => 'Regenerează tokenul de integrare',
        ],

        'index' => [
            'title' => 'Integrări',
            'create-btn' => 'Creează integrare',
        ],

        'create' => [
            'title' => 'Creează integrare',
            'save-btn' => 'Salvează',
            'back-btn' => 'Înapoi',
        ],

        'edit' => [
            'title' => 'Editează integrarea',
            'save-btn' => 'Salvează',
            'back-btn' => 'Înapoi',
            'generate-btn' => 'Generează token',
            'regenerate-btn' => 'Regenerează tokenul',
            'revoke-btn' => 'Revocă tokenul',
            'copy-btn' => 'Copiază',
            'token-warning' => 'Salvați acest token acum — nu va mai fi afișat din nou.',
            'token-label' => 'Token',
            'not-generated' => 'Încă negenerat',
            'masked' => '(Stocat — afișat o singură dată, la generare)',
            'history-banner' => 'Acest token nu mai este activ.',
        ],

        'fields' => [
            'name' => 'Nume',
            'description' => 'Descriere',
            'assign-user' => 'Atribuie utilizator',
            'permission-type' => 'Tip de permisiune',
            'access-control' => 'Control acces',
            'general' => 'General',
            'token-settings' => 'Setări token',
            'valid-till' => 'Valabil până la',
            'rate-limit-per-minute' => 'Limită de cereri (pe minut)',
            'rate-limit-per-day' => 'Limită de cereri (pe zi)',
            'never-expires' => 'Nu expiră niciodată',
            'expires-on' => 'Expiră la',
            'unlimited' => 'Nelimitat',
            'limit-to' => 'Limitează la',
            'requests-per-minute' => 'cereri / minut',
            'requests-per-day' => 'cereri / zi',
            'select-admin' => 'Selectați un administrator',
            'no-available-admins' => 'Nu există administratori disponibili — fiecare administrator are deja un token activ.',
            'same-as-web-hint' => 'Tokenul va reflecta în timp real permisiunile rolului curent al administratorului atribuit.',
            'ip-allowlist' => 'Listă de IP-uri permise',
            'ip-any' => 'Orice IP (implicit)',
            'ip-restricted' => 'Restricționat la anumite IP-uri',
            'ip-list-hint' => 'O intrare pe linie. Acceptă IPv4, IPv6 și CIDR (de ex. 10.0.0.0/24 sau 2001:db8::/32). Lăsați gol pentru a permite toate IP-urile.',
        ],

        'permission_type' => [
            'all' => 'Toate',
            'custom' => 'Personalizat',
            'same_as_web' => 'La fel ca permisiunile web',
        ],

        'status' => [
            'draft' => 'Ciornă',
            'active' => 'Activ',
            'revoked' => 'Revocat',
            'regenerated' => 'Regenerat',
        ],

        'datagrid' => [
            'id' => 'ID',
            'name' => 'Nume',
            'admin' => 'Administrator',
            'token' => 'Token',
            'status' => 'Stare',
            'permission-type' => 'Tip de permisiune',
            'expires-at' => 'Valabil până la',
            'last-used-at' => 'Ultima utilizare',
            'created-at' => 'Creat la',
            'edit' => 'Editează',
            'revoke' => 'Revocă',
        ],

        'messages' => [
            'draft-created' => 'Integrarea a fost creată. Generați tokenul pentru a începe să o folosiți.',
            'updated' => 'Integrarea a fost actualizată cu succes.',
            'generated' => 'Tokenul a fost generat. Copiați-l acum — nu va mai fi afișat din nou.',
            'regenerated' => 'Tokenul a fost regenerat. Copiați noul token acum — nu va mai fi afișat din nou.',
            'revoked' => 'Tokenul a fost revocat cu succes.',
            'generate-only-draft' => 'Tokenul poate fi generat doar pentru integrările aflate în stadiul de ciornă.',
            'regenerate-only-active' => 'Doar tokenurile active pot fi regenerate.',
            'cannot-edit-historic' => 'Tokenurile revocate sau regenerate nu pot fi editate.',
            'already-inactive' => 'Acest token este deja inactiv.',
        ],

        'errors' => [
            'admin-has-token' => 'Administratorul selectat are deja un token de integrare activ.',
        ],

        'validation' => [
            'ip-invalid' => 'Fiecare IP permis trebuie să fie o adresă IPv4 sau IPv6 validă (notația CIDR este acceptată).',
            'cidr-prefix-invalid' => 'Prefixul CIDR este nevalid pentru versiunea IP indicată.',
        ],

        'configuration' => [
            'api' => [
                'title' => 'API',
                'info' => 'Setări pentru API-ul Bagisto și modulele sale de administrare.',
            ],
            'integration' => [
                'title' => 'Integrare',
                'info' => 'Gestionați pluginul API Integration folosit pentru emiterea tokenurilor API de administrare.',
            ],
            'settings' => [
                'title' => 'Setări modul',
                'info' => 'Activați sau dezactivați pluginul API Integration. Când este dezactivat, meniul său din bara laterală este ascuns, iar paginile sale returnează 404.',
                'enable' => 'Activează modulul API Integration',
            ],
        ],

        'emails' => [
            'generated' => [
                'subject' => 'A fost generat un nou token API: :name',
                'greeting' => 'Un token de integrare API numit ":name" tocmai a fost generat în contul dvs.',
            ],
            'regenerated' => [
                'subject' => 'Tokenul dvs. API a fost regenerat: :name',
                'greeting' => 'Tokenul de integrare API numit ":name" tocmai a fost regenerat. Tokenul anterior nu mai funcționează — doar cel nou este valid.',
            ],
            'revoked' => [
                'subject' => 'Tokenul dvs. API a fost revocat: :name',
                'greeting' => 'Tokenul de integrare API numit ":name" a fost revocat. Orice client care îl folosea și-a pierdut accesul.',
            ],

            'details' => [
                'name' => 'Nume token',
                'date' => 'Dată',
                'ip' => 'De la IP',
            ],

            'revoke-hint' => 'Dacă nu vă așteptați la acest lucru, revocați imediat tokenul folosind butonul de mai jos.',
            'revoke-btn' => 'Revocă acest token',
            'revoke-expiry' => 'Acest link de revocare este valabil 7 zile. După aceea, autentificați-vă în panoul de administrare pentru a gestiona tokenul.',
            'no-action' => 'Nu este necesară nicio acțiune — acest email este doar o confirmare.',
        ],

        'revoke-confirmation' => [
            'title' => 'Revocă tokenul API',
            'success-title' => 'Token revocat',
            'success-message' => 'Tokenul ":name" a fost revocat. Orice client care îl folosea și-a pierdut imediat accesul.',
            'already-inactive-title' => 'Token deja inactiv',
            'already-inactive-message' => 'Tokenul ":name" a fost deja revocat sau regenerat. Nu este necesară nicio altă acțiune.',
        ],

        'confirm' => [
            'generate' => [
                'title' => 'Generează token',
                'message' => 'Generați tokenul acum? Textul în clar va fi afișat o singură dată — copiați-l înainte de a părăsi pagina.',
            ],
            'regenerate' => [
                'title' => 'Regenerează tokenul',
                'message' => 'Regenerați tokenul? Tokenul vechi va înceta imediat să funcționeze, iar noul text în clar va fi afișat o singură dată.',
            ],
            'revoke' => [
                'title' => 'Revocă tokenul',
                'message' => 'Revocați acest token? Orice client care îl folosește își va pierde imediat accesul. Această acțiune nu poate fi anulată.',
            ],
        ],
    ],
];
