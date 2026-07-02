<?php

return [
    'graphql' => [
        'cart' => [
            'authentication-required'           => 'È richiesto un token di autenticazione',
            'invalid-token'                     => 'Token di autenticazione non valido o scaduto',
            'unauthorized-access'               => 'Accesso non autorizzato al carrello',
            'authenticated-only'                => 'Solo gli utenti autenticati possono recuperare i propri carrelli',
            'merge-requires-auth'               => 'L\'unione del carrello ospite richiede l\'autenticazione',
            'unknown-operation'                 => 'Operazione sul carrello sconosciuta',

            'cart-not-found'                    => 'Carrello non trovato',
            'guest-cart-not-found'              => 'Carrello ospite non trovato',
            'product-not-found'                 => 'Prodotto non trovato',

            'product-id-quantity-required'      => 'L\'ID del prodotto e la quantità sono obbligatori',
            'cart-item-id-quantity-required'    => 'L\'ID dell\'articolo del carrello e la quantità sono obbligatori',
            'cart-item-id-required'             => 'L\'ID dell\'articolo del carrello è obbligatorio',
            'item-ids-required'                 => 'L\'array degli ID degli articoli è obbligatorio',
            'coupon-code-required'              => 'Il codice del coupon è obbligatorio',
            'address-data-required'             => 'Paese, provincia e codice postale sono obbligatori',

            'add-product-failed'                => 'Impossibile aggiungere il prodotto al carrello',
            'update-item-failed'                => 'Impossibile aggiornare l\'articolo del carrello',
            'remove-item-failed'                => 'Impossibile rimuovere l\'articolo dal carrello',
            'apply-coupon-failed'               => 'Impossibile applicare il coupon',
            'remove-coupon-failed'              => 'Impossibile rimuovere il coupon',
            'move-to-wishlist-failed'           => 'Impossibile spostare l\'articolo nella lista dei desideri',
            'estimate-shipping-failed'          => 'Impossibile stimare la spedizione',

            'product-added-successfully'         => 'Prodotto aggiunto al carrello con successo',
            'guest-cart-merged'                  => 'Carrello ospite unito con successo',
            'using-authenticated-cart'           => 'Utilizzo del carrello del cliente autenticato',
            'cart-item-not-found'                => 'Articolo del carrello non trovato',
            'new-guest-cart-created'             => 'Nuovo carrello ospite creato con un token di sessione univoco',
            'select-items-to-remove'             => 'Seleziona gli articoli da rimuovere',
            'select-items-to-move-wishlist'      => 'Seleziona gli articoli da spostare nella lista dei desideri',
            'invalid-or-expired-token'           => 'Il token del carrello non è valido o è scaduto. Crea un nuovo carrello.',
            'invalid-token-of-login-user'        => 'Il token dell\'utente connesso non è valido.',
        ],

        'token-verification' => [
            'invalid-operation'                 => 'Operazione non valida',
            'invalid-input-data'                => 'Dati di input non validi',
            'token-required'                    => 'Il token è obbligatorio',
            'invalid-token-format'              => 'Formato del token non valido',
            'token-not-found-or-expired'        => 'Token non trovato o scaduto',
            'customer-not-found'                => 'Cliente non trovato',
            'customer-account-suspended'        => 'L\'account del cliente è sospeso',
            'error-verifying-token'             => 'Errore durante la verifica del token',
            'token-is-valid'                    => 'Il token è valido',
        ],

        'forgot-password' => [
            'invalid-operation'                 => 'Operazione non valida',
            'invalid-input-data'                => 'Dati di input non validi',
            'email-required'                    => 'L\'email è obbligatoria',
            'reset-link-sent'                   => 'Link di reimpostazione inviato con successo alla tua email',
            'email-not-found'                   => 'Indirizzo email non trovato',
            'error-sending-reset-link'          => 'Si è verificato un errore durante l\'invio del link di reimpostazione',
        ],

        'logout' => [
            'invalid-operation'                 => 'Operazione non valida',
            'invalid-input-data'                => 'Dati di input non validi',
            'token-required'                    => 'Il token è obbligatorio',
            'invalid-token-format'              => 'Formato del token non valido',
            'logged-out-successfully'           => 'Disconnessione avvenuta con successo',
            'token-not-found-or-expired'        => 'Token non trovato o già scaduto',
            'error-during-logout'               => 'Errore durante la disconnessione',
        ],

        'address' => [
            'deleted-successfully'              => 'Indirizzo eliminato con successo',
            'authentication-required'           => 'È richiesto un token di autenticazione',
            'invalid-token'                     => 'Token non valido o scaduto',
            'unknown-operation'                 => 'Operazione sconosciuta',
            'address-id-required'               => 'L\'ID dell\'indirizzo è obbligatorio',
            'address-not-found'                 => 'Indirizzo non trovato o non appartenente a questo cliente',
            'retrieved'                         => 'Indirizzi recuperati con successo',
            'fetch-failed'                      => 'Impossibile recuperare gli indirizzi:',
        ],

        'customer-profile' => [
            'authentication-required'           => 'È richiesto un token di autenticazione. Fornisci il token nell\'input della query',
            'invalid-token'                     => 'Token non valido o scaduto',
        ],

        'customer' => [
            'password-mismatch'                 => 'La password e la conferma della password non corrispondono',
            'confirm-password-required'         => 'La conferma della password è obbligatoria quando si cambia la password',
            'unauthenticated'                   => 'Non autenticato. Effettua l\'accesso per eseguire questa azione',
        ],

        'product-review' => [
            'product-id-required'               => 'L\'ID del prodotto è obbligatorio',
            'product-not-found'                 => 'Prodotto non trovato',
            'rating-invalid'                    => 'La valutazione deve essere compresa tra 1 e 5',
            'title-required'                    => 'Il titolo della recensione è obbligatorio',
            'comment-required'                  => 'Il commento della recensione è obbligatorio',
        ],

        'product' => [
            'not-found-with-sku'                => 'No product found with SKU',
            'not-found-with-url-key'            => 'No product found with URL key',
            'parameters-required'               => 'At least one of the following parameters must be provided: "sku", "id", "urlKey"',
        ],

        'auth' => [
            'no-token-provided'                 => 'Nessun token di autenticazione fornito. Fornisci il token nell\'intestazione Authorization come "Bearer <token>" o nel campo input.token',
            'invalid-or-expired-token'          => 'Token non valido o scaduto',
            'request-not-found'                 => 'Richiesta non trovata nel contesto',
            'token-required'                    => 'Authentication token is required. Please provide the token either in the GraphQL mutation input field or in the Authorization header as "Bearer <token>"',
            'unknown-resource'                  => 'Risorsa sconosciuta',
            'cannot-update-other-profile'       => 'Non autorizzato: impossibile aggiornare il profilo di un altro cliente',
        ],

        'upload' => [
            'invalid-base64'                    => 'Dati immagine codificati in base64 non validi',
            'size-exceeds-limit'                => 'La dimensione dell\'immagine non deve superare i 5 MB',
            'invalid-format'                    => 'Formato immagine non valido. Fornisci un\'immagine codificata in base64 con schema URI dati (data:image/jpeg;base64,...)',
            'failed'                            => 'Caricamento dell\'immagine non riuscito',
        ],

        'attribute' => [
            'code-already-exists'               => 'Il codice dell\'attributo esiste già',
        ],

        'login' => [
            'invalid-credentials'               => 'Email o password non validi',
            'account-suspended'                 => 'Il tuo account è stato sospeso',
            'successful'                        => 'Accesso effettuato con successo',
            'invalid-request'                   => 'Richiesta di accesso non valida',
        ],

        'checkout' => [
            'invalid-input'                     => 'Dati di input non validi per l\'operazione di checkout',
            'billing-address-required'          => 'L\'indirizzo di fatturazione è obbligatorio',
            'shipping-address-required'         => 'L\'indirizzo di spedizione è obbligatorio per le spedizioni',
            'address-save-failed'               => 'Impossibile salvare l\'indirizzo',
            'address-saved'                     => 'Indirizzo salvato con successo',
            'shipping-method-required'          => 'Il metodo di spedizione è obbligatorio',
            'invalid-shipping-method'           => 'Metodo di spedizione non valido o non disponibile',
            'shipping-method-save-failed'       => 'Impossibile salvare il metodo di spedizione',
            'shipping-method-saved'             => 'Metodo di spedizione salvato con successo',
            'shipping-method-error'             => 'Errore durante il salvataggio del metodo di spedizione',
            'payment-method-required'           => 'Il metodo di pagamento è obbligatorio',
            'invalid-payment-method'            => 'Metodo di pagamento non valido o non disponibile',
            'payment-method-save-failed'        => 'Impossibile salvare il metodo di pagamento',
            'payment-method-saved'              => 'Metodo di pagamento salvato con successo',
            'payment-method-error'              => 'Errore durante il salvataggio del metodo di pagamento',
            'order-creation-failed'             => 'Creazione dell\'ordine non riuscita: l\'ID dell\'ordine è nullo o l\'ordine non è stato salvato',
            'order-retrieval-failed'            => 'Impossibile recuperare l\'ordine creato',
            'order-creation-error'              => 'Impossibile creare l\'ordine',
            'cart-empty'                        => 'Il carrello è vuoto',
            'account-suspended'                 => 'Il tuo account è stato sospeso. Contatta l\'assistenza.',
            'account-inactive'                  => 'Il tuo account non è attivo. Contatta l\'assistenza.',
            'minimum-order-not-met'             => 'L\'importo minimo dell\'ordine è :amount',
            'email-required'                    => 'L\'indirizzo email è obbligatorio per la creazione dell\'ordine',
            'unknown-operation'                 => 'Operazione di checkout sconosciuta',
        ],

        'customer-addresses' => [
            'token-required'                    => 'Il token è obbligatorio per recuperare gli indirizzi del cliente',
            'invalid-or-expired-token'          => 'Token non valido o scaduto',
            'token-validation-failed'           => 'Convalida del token non riuscita',
        ],

        'product' => [
            'type'                              => 'Tipo di prodotto',
            'attribute-family'                  => 'Famiglia di attributi',
            'sku'                               => 'SKU',
            'name'                              => 'Nome',
            'description'                       => 'Descrizione',
            'short-description'                 => 'Descrizione breve',
            'status'                            => 'Stato',
            'new'                               => 'Nuovo',
            'featured'                          => 'In evidenza',
            'price'                             => 'Prezzo',
            'special-price'                     => 'Prezzo speciale',
            'weight'                            => 'Peso',
            'cost'                              => 'Costo',
            'length'                            => 'Lunghezza',
            'width'                             => 'Larghezza',
            'height'                            => 'Altezza',
            'color'                             => 'Colore',
            'size'                              => 'Taglia',
            'brand'                             => 'Marca',
            'super-attributes'                  => 'Super attributi',
        ],

        'compare-item' => [
            'id-required'                       => 'L\'ID dell\'elemento di confronto è obbligatorio',
            'invalid-id-format'                 => 'Formato ID non valido. Formato IRI previsto come "/api/shop/compare-items/1" o ID numerico',
            'not-found'                         => 'Elemento di confronto non trovato',
            'product-id-required'               => 'L\'ID del prodotto è obbligatorio',
            'customer-id-required'              => 'L\'ID del cliente è obbligatorio',
            'product-not-found'                 => 'Prodotto non trovato',
            'customer-not-found'                => 'Cliente non trovato',
            'already-exists'                    => 'Questo prodotto è già nel tuo elenco di confronto',
        ],

        'downloadable-product' => [
            'download-link-not-found'           => 'Link di download non trovato o scaduto',
            'purchased-link-not-found'          => 'Link acquistato non trovato',
            'file-not-found'                    => 'File non trovato',
            'download-successful'               => 'File pronto per il download',
            'token-required'                    => 'Token di download richiesto',
            'invalid-token'                     => 'Token di download non valido o scaduto',
            'token-expired'                     => 'Il token di download è scaduto. Si prega di generare un nuovo token',
            'access-denied'                     => 'Accesso negato: Non sei autorizzato a scaricare questo file',
            'redirect-external-url'             => 'Reindirizzamento all\'URL di download esterno',
            'file-error'                        => 'Si è verificato un errore durante l\'elaborazione della richiesta di download',
            'unauthorized-access'               => 'Accesso non autorizzato alla risorsa di download',
        ],
    ],

    'integration' => [
        'menu' => [
            'title'  => 'Integrazione',
            'tokens' => 'Gettoni',
        ],

        'history' => [
            'menu' => [
                'title' => 'Storia',
            ],

            'acl' => [
                'title'  => 'Cronologia modifiche API',
                'delete' => 'Elimina cronologia',
            ],

            'index' => [
                'title'           => 'Cronologia modifiche API',
                'info'            => 'Ogni creazione, aggiornamento ed eliminazione effettuata tramite l\'API di amministrazione, con chi l\'ha fatto, quale token e cosa è cambiato.',
                'cleanup-btn'     => 'Elimina i registri più vecchi',
                'cleanup-days'    => 'Elimina i log più vecchi di questo numero di giorni',
                'cleanup-confirm' => 'Eliminare tutta la cronologia precedente al numero di giorni specificato? Questa operazione non può essere annullata.',
            ],

            'view' => [
                'title'             => 'Cambiare',
                'back-btn'          => 'Indietro',
                'admin'             => 'Ammin',
                'token'             => 'Gettone',
                'action'            => 'Azione',
                'resource'          => 'Risorsa',
                'method'            => 'Metodo',
                'ip'                => 'Indirizzo IP',
                'date'              => 'Data',
                'version'           => 'Versione',
                'url'               => 'Punto finale',
                'request-details'   => 'Richiedi dettagli',
                'changes'           => 'Cambiamenti',
                'field'             => 'Campo',
                'old'               => 'Vecchio valore',
                'new'               => 'Nuovo valore',
                'no-field-changes'  => 'Per questa voce non sono state registrate modifiche a livello di campo.',
                'same-request'      => 'Altre modifiche nella stessa richiesta',
                'version-chain'     => 'Cronologia delle versioni di questo record',
            ],

            'datagrid' => [
                'id'        => 'ID',
                'date'      => 'Data',
                'admin'     => 'Ammin',
                'token'     => 'Gettone',
                'action'    => 'Azione',
                'operation' => 'Operazione',
                'resource'  => 'Risorsa',
                'version'   => 'Versione',
                'method'    => 'Metodo',
                'ip'        => 'IP',
                'view'      => 'Visualizza',
                'delete'    => 'Elimina',
            ],

            'events' => [
                'created' => 'Creato',
                'updated' => 'Aggiornato',
                'deleted' => 'Eliminato',
            ],

            'deleted'                => ':count record della cronologia eliminati.',
            'cleanup-input-required' => 'Fornire un numero di giorni o una data per la pulizia.',
        ],

        'acl' => [
            'title'      => 'Integrazione',
            'create'     => 'Crea integrazione',
            'edit'       => 'Modifica integrazione',
            'delete'     => 'Revoca token di integrazione',
            'generate'   => 'Genera token di integrazione',
            'regenerate' => 'Rigenera token di integrazione',
        ],

        'index' => [
            'title'      => 'Integrazioni',
            'create-btn' => 'Crea integrazione',
        ],

        'create' => [
            'title'    => 'Crea integrazione',
            'save-btn' => 'Salva',
            'back-btn' => 'Indietro',
        ],

        'edit' => [
            'title'          => 'Modifica integrazione',
            'save-btn'       => 'Salva',
            'back-btn'       => 'Indietro',
            'generate-btn'   => 'Genera token',
            'regenerate-btn' => 'Gettone rigenera',
            'revoke-btn'     => 'Revoca token',
            'copy-btn'       => 'Copia',
            'token-warning'  => 'Salva questo token adesso: non verrà più mostrato.',
            'token-label'    => 'Gettone',
            'not-generated'  => 'Non ancora generato',
            'masked'         => '(Memorizzato: mostrato solo una volta alla generazione)',
            'history-banner' => 'Questo token non è più attivo.',
        ],

        'fields' => [
            'name'                  => 'Nome',
            'description'           => 'Descrizione',
            'assign-user'           => 'Assegna utente',
            'permission-type'       => 'Tipo di autorizzazione',
            'access-control'        => 'Controllo degli accessi',
            'general'               => 'Generale',
            'token-settings'        => 'Impostazioni dei token',
            'valid-till'            => 'Valido fino al',
            'rate-limit-per-minute' => 'Limite di velocità (al minuto)',
            'rate-limit-per-day'    => 'Limite di tariffa (al giorno)',
            'never-expires'         => 'Non scade mai',
            'expires-on'            => 'Scade il',
            'unlimited'             => 'Illimitato',
            'limit-to'              => 'Limitare a',
            'requests-per-minute'   => 'richieste/minuto',
            'requests-per-day'      => 'richieste/giorno',
            'select-admin'          => 'Seleziona un amministratore',
            'no-available-admins'   => 'Nessun amministratore disponibile: ogni amministratore ha già un token attivo.',
            'same-as-web-hint'      => 'Il token rispecchierà in tempo reale le autorizzazioni del ruolo corrente dell\'amministratore assegnato.',
            'ip-allowlist'          => 'Lista consentita IP',
            'ip-any'                => 'Qualsiasi IP (predefinito)',
            'ip-restricted'         => 'Limitato a IP specifici',
            'ip-list-hint'          => 'Una voce per riga. Supporta IPv4, IPv6 e CIDR (ad esempio 10.0.0.0/24 o 2001:db8::/32). Lascia vuoto per consentire tutti gli IP.',
        ],

        'permission_type' => [
            'all'         => 'Tutto',
            'custom'      => 'Personalizzato',
            'same_as_web' => 'Uguale all\'autorizzazione Web',
        ],

        'status' => [
            'draft'       => 'Bozza',
            'active'      => 'Attivo',
            'revoked'     => 'Revocato',
            'regenerated' => 'Rigenerato',
        ],

        'datagrid' => [
            'id'              => 'ID',
            'name'            => 'Nome',
            'admin'           => 'Ammin',
            'token'           => 'Gettone',
            'status'          => 'Stato',
            'permission-type' => 'Tipo di autorizzazione',
            'expires-at'      => 'Valido fino al',
            'last-used-at'    => 'Ultimo utilizzo',
            'created-at'      => 'Creato a',
            'edit'            => 'Modifica',
            'revoke'          => 'Revoca',
        ],

        'messages' => [
            'draft-created'          => 'Integrazione creata. Genera il token per iniziare a usarlo.',
            'updated'                => 'Integrazione aggiornata con successo.',
            'generated'              => 'Gettone generato. Copialo adesso: non verrà più mostrato.',
            'regenerated'            => 'Gettone rigenerato. Copia ora il nuovo token: non verrà più mostrato.',
            'revoked'                => 'Token revocato con successo.',
            'generate-only-draft'    => 'Solo le bozze di integrazione possono generare il proprio token.',
            'regenerate-only-active' => 'Solo i token attivi possono essere rigenerati.',
            'cannot-edit-historic'   => 'I token revocati o rigenerati non possono essere modificati.',
            'already-inactive'       => 'Questo token è già inattivo.',
        ],

        'errors' => [
            'admin-has-token' => 'L\'amministratore selezionato ha già un token di integrazione attivo.',
        ],

        'validation' => [
            'ip-invalid'           => 'Ogni IP consentito deve essere un indirizzo IPv4 o IPv6 valido (notazione CIDR supportata).',
            'cidr-prefix-invalid'  => 'Il prefisso CIDR non è valido per la versione IP specificata.',
        ],

        'configuration' => [
            'api' => [
                'title' => 'API',
                'info'  => 'Impostazioni per l\'API Bagisto e i suoi moduli di amministrazione.',
            ],
            'integration' => [
                'title' => 'Integrazione',
                'info'  => 'Gestisci il plug-in di integrazione API utilizzato per emettere token API di amministrazione.',
            ],
            'settings' => [
                'title'  => 'Impostazioni del modulo',
                'info'   => 'Abilita o disabilita il plugin di integrazione API. Quando disabilitato, il menu della barra laterale è nascosto e le sue pagine restituiscono 404.',
                'enable' => 'Abilita il modulo di integrazione API',
            ],
        ],

        'emails' => [
            'generated' => [
                'subject'  => 'È stato generato un nuovo token API: :name',
                'greeting' => 'Sul tuo account è stato appena generato un token di integrazione API denominato ":name".',
            ],
            'regenerated' => [
                'subject'  => 'Il tuo token API è stato rigenerato: :name',
                'greeting' => 'Il token di integrazione API denominato ":name" è stato appena rigenerato. Il token precedente ha smesso di funzionare: solo quello nuovo è valido.',
            ],
            'revoked' => [
                'subject'  => 'Il tuo token API è stato revocato: :name',
                'greeting' => 'Il token di integrazione API denominato ":name" è stato revocato. Qualsiasi client che lo utilizza ha perso l\'accesso.',
            ],

            'details' => [
                'name' => 'Nome del token',
                'date' => 'Data',
                'ip'   => 'Dall\'IP',
            ],

            'revoke-hint'   => 'Se non te lo aspettavi, revoca immediatamente il token utilizzando il pulsante qui sotto.',
            'revoke-btn'    => 'Revoca questo token',
            'revoke-expiry' => 'Questo collegamento di revoca è valido per 7 giorni. Successivamente, accedi al pannello di amministrazione per gestire il token.',
            'no-action'     => 'Non è necessaria alcuna azione: questa email è solo una conferma.',
        ],

        'revoke-confirmation' => [
            'title'                    => 'Revoca il token API',
            'success-title'            => 'Gettone revocato',
            'success-message'          => 'Il token ":name" è stato revocato. Qualsiasi client che lo utilizza perde immediatamente l\'accesso.',
            'already-inactive-title'   => 'Token già inattivo',
            'already-inactive-message' => 'Il token ":name" è già stato revocato o rigenerato. Non sono necessarie ulteriori azioni.',
        ],

        'confirm' => [
            'generate' => [
                'title'   => 'Genera token',
                'message' => 'Generare il token adesso? Il testo in chiaro verrà mostrato solo una volta: copialo prima di lasciare la pagina.',
            ],
            'regenerate' => [
                'title'   => 'Gettone rigenera',
                'message' => 'Rigenerare il token? Il vecchio token smetterà di funzionare immediatamente e il nuovo testo in chiaro verrà mostrato solo una volta.',
            ],
            'revoke' => [
                'title'   => 'Revoca token',
                'message' => 'Revocare questo token? Qualsiasi client che lo utilizzi perderà immediatamente l\'accesso. Questa azione non può essere annullata.',
            ],
        ],
    ],
];
