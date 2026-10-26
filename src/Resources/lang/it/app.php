<?php

return [

    'graphql' => [
        'headers' => [
            'unknown-channel' => 'Il canale ":code" nell\'intestazione X-Channel non esiste.',
            'storefront-key-required' => 'L\'intestazione X-STOREFRONT-KEY è obbligatoria',
            'storefront-key-invalid' => 'Chiave storefront non valida',
            'storefront-key-rate-limited' => 'Limite di richieste superato. Riprova tra :seconds secondi',
        ],

        'cart' => [
            'authentication-required' => 'È richiesto un token di autenticazione',
            'invalid-token' => 'Token di autenticazione non valido o scaduto',
            'unauthorized-access' => 'Accesso non autorizzato al carrello',
            'authenticated-only' => 'Solo gli utenti autenticati possono recuperare i propri carrelli',
            'merge-requires-auth' => 'L\'unione del carrello ospite richiede l\'autenticazione',
            'unknown-operation' => 'Operazione sul carrello sconosciuta',

            'cart-not-found' => 'Carrello non trovato',
            'guest-cart-not-found' => 'Carrello ospite non trovato',
            'product-not-found' => 'Prodotto non trovato',

            'product-id-quantity-required' => 'L\'ID del prodotto e la quantità sono obbligatori',
            'cart-item-id-quantity-required' => 'L\'ID dell\'articolo del carrello e la quantità sono obbligatori',

            'file-token-invalid' => 'Il riferimento al file caricato non è valido o è scaduto. Carica di nuovo il file.',
            'file-token-forbidden' => 'Questo file caricato non appartiene al tuo carrello.',
            'customizable-option-required' => 'L\'opzione personalizzabile ":label" è obbligatoria.',
            'customizable-file-option-invalid' => 'L\'opzione selezionata non è un\'opzione file per questo prodotto.',
            'customizable-file-invalid' => 'Il file è mancante, ha un\'estensione non supportata o è troppo grande.',
            'cart-item-id-required' => 'L\'ID dell\'articolo del carrello è obbligatorio',
            'item-ids-required' => 'L\'array degli ID degli articoli è obbligatorio',

            'event-booking-quantity-not-changeable' => 'La quantità del prodotto di prenotazione evento non può essere modificata. La quantità è determinata dalla selezione dei biglietti.',
            'appointment-booking-quantity-not-changeable' => 'La quantità del prodotto di prenotazione appuntamento non può essere modificata.',
            'coupon-code-required' => 'Il codice del coupon è obbligatorio',
            'address-data-required' => 'Paese, provincia e codice postale sono obbligatori',
            'grouped-qty-required' => 'Il prodotto raggruppato richiede le quantità selezionate. Passa groupedQty come stringa JSON, ad es. {"101":2,"102":1}.',
            'grouped-qty-must-include-all' => 'Il prodotto raggruppato richiede le quantità per tutti i prodotti associati. ID mancanti: :ids.',
            'grouped-qty-invalid-associated' => 'Le quantità del prodotto raggruppato contengono ID di prodotti associati non validi: :ids.',
            'grouped-qty-invalid-quantity' => 'Quantità non valida per l\'ID del prodotto associato :id. La quantità deve essere un numero intero non negativo.',
            'bundle-qty-not-changeable' => 'La quantità per l\'opzione del bundle ":option" non può essere modificata. La quantità consentita è :qty.',

            'add-product-failed' => 'Impossibile aggiungere il prodotto al carrello',
            'update-item-failed' => 'Impossibile aggiornare l\'articolo del carrello',
            'remove-item-failed' => 'Impossibile rimuovere l\'articolo dal carrello',
            'remove-coupon-failed' => 'Impossibile rimuovere il coupon',
            'coupon-applied' => 'Coupon applicato con successo',
            'coupon-removed' => 'Coupon rimosso con successo',
            'move-to-wishlist-failed' => 'Impossibile spostare l\'articolo nella lista dei desideri',
            'estimate-shipping-failed' => 'Impossibile stimare la spedizione',

            'product-added-successfully' => 'Prodotto aggiunto al carrello con successo',
            'cart-item-updated-successfully' => 'Articolo del carrello aggiornato con successo',
            'guest-cart-merged' => 'Carrello ospite unito con successo',
            'using-authenticated-cart' => 'Utilizzo del carrello del cliente autenticato',
            'cart-item-not-found' => 'Articolo del carrello non trovato',
            'new-guest-cart-created' => 'Nuovo carrello ospite creato con un token di sessione univoco',
            'select-items-to-remove' => 'Seleziona gli articoli da rimuovere',
            'select-items-to-move-wishlist' => 'Seleziona gli articoli da spostare nella lista dei desideri',
            'invalid-or-expired-token' => 'Il token del carrello non è valido o è scaduto. Crea un nuovo carrello.',
            'invalid-token-of-login-user' => 'Il token dell\'utente connesso non è valido.',
            'booking-note-required' => 'booking.note è obbligatorio per la prenotazione del tavolo.',
            'booking-date-required' => 'booking.date è obbligatorio quando booking.slot è un intervallo orario formattato.',
            'booking-qty-required' => 'booking.qty è obbligatorio per la prenotazione di eventi.',
            'booking-ticket-qty-invalid' => 'La quantità di biglietti per l\'evento deve essere un numero non negativo.',
            'booking-ticket-required' => 'Seleziona la quantità di almeno un biglietto per la prenotazione dell\'evento.',
            'booking-slot-unavailable' => 'La fascia oraria selezionata non è disponibile per la data indicata.',
            'invalid-quantity' => 'La quantità deve essere almeno 1',
            'product-id-required' => 'L\'ID del prodotto è obbligatorio',
            'shipping-address-required' => 'Paese e CAP sono obbligatori per stimare la spedizione',
        ],

        'token-verification' => [
            'invalid-operation' => 'Operazione non valida',
            'invalid-input-data' => 'Dati di input non validi',
            'token-required' => 'Il token è obbligatorio',
            'invalid-token-format' => 'Formato del token non valido',
            'token-not-found-or-expired' => 'Token non trovato o scaduto',
            'customer-not-found' => 'Cliente non trovato',
            'customer-account-suspended' => 'L\'account del cliente è sospeso',
            'error-verifying-token' => 'Errore durante la verifica del token',
            'token-is-valid' => 'Il token è valido',
            'unauthenticated' => 'Non autenticato. Accedi per eseguire questa azione',
        ],

        'forgot-password' => [
            'invalid-operation' => 'Operazione non valida',
            'invalid-input-data' => 'Dati di input non validi',
            'email-required' => 'L\'email è obbligatoria',
            'reset-link-sent' => 'Link di reimpostazione inviato con successo alla tua email',
            'email-not-found' => 'Indirizzo email non trovato',
            'throttled' => 'Troppi tentativi di reimpostazione. Attendi prima di riprovare.',
            'error-sending-reset-link' => 'Si è verificato un errore durante l\'invio del link di reimpostazione',
        ],

        'reset-password' => [
            'failed' => 'Non è stato possibile reimpostare la password. Riprova.',
        ],

        'rate-limit' => [
            'too-many-attempts' => 'Troppi tentativi. Riprova tra :seconds secondi.',
        ],

        'logout' => [
            'invalid-operation' => 'Operazione non valida',
            'invalid-input-data' => 'Dati di input non validi',
            'token-required' => 'Il token è obbligatorio',
            'invalid-token-format' => 'Formato del token non valido',
            'logged-out-successfully' => 'Disconnessione avvenuta con successo',
            'token-not-found-or-expired' => 'Token non trovato o già scaduto',
            'error-during-logout' => 'Errore durante la disconnessione',
            'unauthenticated' => 'Non autenticato. Accedi per eseguire questa azione',
        ],

        'address' => [
            'deleted-successfully' => 'Indirizzo eliminato con successo',
            'authentication-required' => 'È richiesto un token di autenticazione',
            'invalid-token' => 'Token non valido o scaduto',
            'unknown-operation' => 'Operazione sconosciuta',
            'address-id-required' => 'L\'ID dell\'indirizzo è obbligatorio',
            'address-not-found' => 'Indirizzo non trovato o non appartenente a questo cliente',
            'retrieved' => 'Indirizzi recuperati con successo',
            'fetch-failed' => 'Impossibile recuperare gli indirizzi:',
            'field-required' => 'Il campo :field è obbligatorio',
        ],

        'customer-profile' => [
            'authentication-required' => 'È richiesto un token di autenticazione. Fornisci il token nell\'input della query',
            'invalid-token' => 'Token non valido o scaduto',
            'profile-updated' => 'Profilo del cliente aggiornato con successo',
        ],

        'customer' => [
            'password-mismatch' => 'La password e la conferma della password non corrispondono',
            'confirm-password-required' => 'La conferma della password è obbligatoria quando si cambia la password',
            'current-password-required' => 'La password attuale è obbligatoria per cambiare la password',
            'current-password-incorrect' => 'La password attuale inserita non è corretta',
            'verify-email-to-login' => 'Il tuo account è stato creato. Verifica il tuo indirizzo email per accedere.',
            'delete-password-required' => 'La tua password è obbligatoria per eliminare l\'account',
            'delete-password-incorrect' => 'La password inserita non è corretta',
            'delete-has-active-orders' => 'Il tuo account non può essere eliminato finché hai ordini in sospeso o in elaborazione',
            'unauthenticated' => 'Non autenticato. Effettua l\'accesso per eseguire questa azione',
            'id-required' => 'L\'ID del cliente è obbligatorio',
            'invalid-id-format' => 'Formato ID non valido. Previsto formato IRI come "/api/admin/customers/1" o ID numerico',
            'not-found' => 'Cliente non trovato',
            'phone-special-chars-not-allowed' => 'Il numero di cellulare può contenere solo cifre. I caratteri speciali non sono consentiti',
            'email-already-taken' => 'Questo indirizzo email è già registrato con un altro account',
            'invalid-gender' => 'Valore di genere ":gender" non valido. I valori consentiti sono: :valid',
        ],

        'product-review' => [
            'id-required' => 'L\'ID della recensione del prodotto è obbligatorio',
            'invalid-id-format' => 'Formato ID non valido. Previsto formato IRI come "/api/shop/reviews/1" o ID numerico',
            'not-found' => 'Recensione del prodotto con ID ":id" non trovata',
            'item-not-found' => 'Elemento ":id" non trovato.',
            'product-id-required' => 'L\'ID del prodotto è obbligatorio',
            'product-not-found' => 'Prodotto non trovato',
            'rating-invalid' => 'La valutazione deve essere compresa tra 1 e 5',
            'title-required' => 'Il titolo della recensione è obbligatorio',
            'comment-required' => 'Il commento della recensione è obbligatorio',
            'review-disabled' => 'Le recensioni dei prodotti sono attualmente disabilitate',
            'guest-review-disabled' => 'Le recensioni degli ospiti non sono consentite. Accedi per inviare una recensione',
            'owner-required' => 'Accedi per gestire la tua recensione',
            'not-owned' => 'Questa recensione non è stata scritta da te e non può essere modificata o eliminata',
            'invalid-attachments' => 'Formato degli allegati non valido.',
            'invalid-product-id-format' => 'Formato ID prodotto non valido. Previsto formato IRI come "/api/shop/products/1" o ID numerico',
        ],

        'customer-review' => [
            'id-required' => 'L\'ID della recensione del cliente è obbligatorio',
            'invalid-id-format' => 'Formato ID non valido. Previsto formato IRI come "/api/shop/customer-reviews/1" o ID numerico',
            'not-found' => 'Recensione del cliente con ID ":id" non trovata',
        ],

        'newsletter' => [
            'error-during-subscription' => 'Impossibile completare l\'iscrizione in questo momento. Riprova più tardi',
        ],

        'contact-us' => [
            'success' => 'La tua richiesta è stata inviata con successo. Ti risponderemo al più presto',
            'failed' => 'Impossibile inviare la tua richiesta in questo momento. Riprova più tardi',
            'invalid-input' => 'Dati del modulo di contatto non validi',
        ],

        'customer-order' => [
            'id-required' => 'L\'ID dell\'ordine del cliente è obbligatorio',
            'invalid-id-format' => 'Formato ID non valido. Previsto formato IRI come "/api/shop/customer-orders/1" o ID numerico',
            'not-found' => 'Ordine del cliente con ID ":id" non trovato',
        ],

        'return' => [
            'not-found' => 'Richiesta di reso non trovata.',
            'disabled' => 'I resi non sono abilitati per questo negozio.',
            'invalid-input' => 'Dettagli del reso mancanti o non validi.',
            'invalid-resolution-type' => 'Il tipo di risoluzione deve essere "return" o "cancel_items".',
            'agreement-required' => 'Devi accettare la politica di reso.',
            'invalid-order' => 'L\'ordine selezionato non è valido.',
            'invalid-item' => 'L\'articolo selezionato non è idoneo al reso.',
            'qty-exceeds' => 'La quantità richiesta supera la quantità restituibile.',
            'invalid-package-condition' => 'La condizione della confezione deve essere "open" o "packed".',
            'invalid-image' => 'Uno o più file caricati hanno un tipo di file non supportato.',
            'invalid-custom-field' => 'Campo personalizzato ":id" sconosciuto.',
            'custom-field-required' => 'Il campo ":field" è obbligatorio.',
            'invalid-custom-field-value' => 'Il valore per ":field" non è tra le opzioni consentite.',
            'created' => 'Richiesta di reso creata con successo.',
            'canceled' => 'Richiesta di reso annullata con successo.',
            'already-canceled' => 'La richiesta di reso è già stata annullata.',
            'cancel-not-allowed' => 'Questa richiesta di reso non può essere annullata.',
            'close-not-allowed' => 'Questa richiesta di reso non può essere chiusa.',
            'reopened' => 'Richiesta di reso riaperta con successo.',
            'reopen-not-allowed' => 'Questa richiesta di reso non può essere riaperta.',
            'closed' => 'Richiesta di reso chiusa con successo.',
            'message-sent' => 'Messaggio inviato con successo.',
            'attachment-not-found' => 'Allegato del messaggio non trovato.',
        ],

        'eu-withdrawal' => [
            'not-found' => 'Dichiarazione di recesso non trovata.',
            'order-not-found' => 'L\'ordine non è stato trovato o non è idoneo al recesso.',
            'too-many-attempts' => 'Troppi tentativi. Riprova tra :seconds secondi.',
            'created' => 'Dichiarazione di recesso presentata con successo.',
        ],

        'customer-order-shipment' => [
            'id-required' => 'L\'ID della spedizione è obbligatorio',
            'not-found' => 'Spedizione con ID ":id" non trovata o non appartenente a questo cliente',
            'item-not-found' => 'Articolo della spedizione con ID ":id" non trovato o non appartenente a questo cliente',
            'invalid-id-format' => 'Formato ID non valido. Previsto formato IRI come "/api/shop/customer-order-shipments/1" o ID numerico',
        ],

        'cancel-order' => [
            'order-id-required' => 'L\'ID dell\'ordine è obbligatorio per annullare un ordine',
            'not-found' => 'Ordine con ID ":id" non trovato o non appartenente a questo cliente',
            'cancel-success' => 'L\'ordine è stato annullato con successo',
            'cancel-failed' => 'L\'ordine non può essere annullato. Potrebbe essere già stato elaborato, spedito o annullato',
        ],

        'reorder' => [
            'order-id-required' => 'L\'ID dell\'ordine è obbligatorio per riordinare',
            'not-found' => 'Ordine con ID ":id" non trovato o non appartenente a questo cliente',
            'reorder-success' => ':count articolo/i del tuo ordine precedente sono stati aggiunti al carrello',
            'no-items-added' => 'Nessun articolo è stato aggiunto al carrello. I prodotti potrebbero essere esauriti o non disponibili',
        ],

        'customer-downloadable-product' => [
            'id-required' => 'L\'ID del prodotto scaricabile del cliente è obbligatorio',
            'invalid-id-format' => 'Formato ID non valido. Previsto formato IRI come "/api/shop/customer-downloadable-products/1" o ID numerico',
            'not-found' => 'Prodotto scaricabile del cliente con ID ":id" non trovato',

            'download' => [
                'unauthenticated' => 'Non autorizzato: è richiesta l\'autenticazione del cliente.',
                'not-found' => 'Prodotto scaricabile non trovato.',
                'pending' => 'Il download è in sospeso. Attendi che l\'ordine venga fatturato.',
                'limit-exceeded' => 'Limite di download superato.',
                'no-downloads-remaining' => 'Nessun altro download disponibile.',
                'file-not-found' => 'File non trovato.',
                'link-not-found' => 'Link scaricabile non trovato.',
            ],
        ],

        'customer-invoice' => [
            'id-required' => 'L\'ID della fattura del cliente è obbligatorio',
            'invalid-id-format' => 'Formato ID non valido. Previsto formato IRI come "/api/shop/customer-invoices/1" o ID numerico',
            'not-found' => 'Fattura del cliente con ID ":id" non trovata',
        ],

        'auth' => [
            'no-token-provided' => 'Nessun token di autenticazione fornito. Fornisci il token nell\'intestazione Authorization come "Bearer <token>" o nel campo input.token',
            'invalid-or-expired-token' => 'Token non valido o scaduto',
            'request-not-found' => 'Richiesta non trovata nel contesto',
            'token-required' => 'Authentication token is required. Please provide the token either in the GraphQL mutation input field or in the Authorization header as "Bearer <token>"',
            'unknown-resource' => 'Risorsa sconosciuta',
            'cannot-update-other-profile' => 'Non autorizzato: impossibile aggiornare il profilo di un altro cliente',
            'refresh-token-invalid' => 'Il token di aggiornamento non è valido o è scaduto. Accedi di nuovo.',
            'too-many-login-attempts' => 'Troppi tentativi di accesso. Riprova tra :seconds secondi.',
            'too-many-refresh-requests' => 'Troppi rinnovi del token. Riprova tra :seconds secondi.',
            'rate-limit-exceeded' => 'Troppi tentativi.',
            'access-token-expired' => 'Il token di accesso è scaduto. Aggiornalo per continuare.',
            'logged-out-all' => 'Sei stato disconnesso da tutte le sessioni.',
            'session-not-found' => 'Sessione non trovata.',
            'session-revoked' => 'La sessione è stata revocata.',
        ],

        'upload' => [
            'invalid-base64' => 'Dati immagine codificati in base64 non validi',
            'size-exceeds-limit' => 'La dimensione dell\'immagine non deve superare i 5 MB',
            'invalid-format' => 'Formato immagine non valido. Fornisci un\'immagine codificata in base64 con schema URI dati (data:image/jpeg;base64,...)',
            'failed' => 'Caricamento dell\'immagine non riuscito',
        ],

        'attribute' => [
            'code-already-exists' => 'Il codice dell\'attributo esiste già',
            'option-id-required' => 'Il parametro "attributeId" è obbligatorio quando si interrogano direttamente le opzioni dell\'attributo',
            'id-required' => 'L\'ID dell\'attributo è obbligatorio',
            'invalid-id-format' => 'Formato ID non valido. Previsto formato IRI come "/api/shop/attributes/1" o ID numerico',
            'not-found' => 'Attributo non trovato',
        ],

        'attribute-option' => [
            'id-required' => 'L\'ID dell\'opzione dell\'attributo è obbligatorio',
            'invalid-id-format' => 'Formato ID non valido. Previsto formato IRI come "/api/shop/attribute-options/1" o ID numerico',
            'not-found' => 'Opzione dell\'attributo non trovata',
        ],

        'channel' => [
            'id-required' => 'L\'ID del canale è obbligatorio',
            'invalid-id-format' => 'Formato ID non valido. Previsto formato IRI come "/api/shop/channels/1" o ID numerico',
            'not-found' => 'Canale non trovato',
        ],

        'country-state' => [
            'id-required' => 'L\'ID della provincia è obbligatorio',
            'invalid-id-format' => 'Formato ID non valido. Previsto formato IRI come "/api/shop/country-states/1" o ID numerico',
            'not-found' => 'Provincia non trovata',
            'country-id-required' => 'L\'ID del paese è obbligatorio',
        ],

        'country' => [
            'id-required' => 'L\'ID del paese è obbligatorio',
            'invalid-id-format' => 'Formato ID non valido. Previsto formato IRI come "/api/shop/countries/1" o ID numerico',
            'not-found' => 'Paese non trovato',
        ],

        'login' => [
            'invalid-credentials' => 'Email o password non validi',
            'account-suspended' => 'Il tuo account è stato sospeso',
            'account-inactive' => 'La tua attivazione richiede l’approvazione dell’amministratore',
            'email-not-verified' => 'Verifica prima il tuo account email.',
            'successful' => 'Accesso effettuato con successo',
            'invalid-request' => 'Richiesta di accesso non valida',
        ],

        'social-login' => [
            'signed-in' => 'Accesso effettuato con successo.',
            'token-required' => 'È richiesto un token di accesso social.',
            'invalid-token' => 'Il token di accesso social non è valido o è scaduto. Riprova.',
            'google-not-configured' => 'L\'accesso con Google non è configurato per questo negozio.',
            'wrong-audience' => 'Questo token è stato emesso per un’altra app.',
            'email-required' => 'Il provider non ha condiviso un indirizzo email. Registrati con l’email.',
            'account-inactive' => 'La tua attivazione richiede l’approvazione dell’amministratore',
            'provider-not-supported' => 'Questo provider di accesso social non è supportato.',
            'provider-disabled' => 'Questo provider di accesso social non è abilitato.',
        ],

        'checkout' => [
            'invalid-input' => 'Dati di input non validi per l\'operazione di checkout',
            'billing-address-required' => 'L\'indirizzo di fatturazione è obbligatorio',
            'shipping-address-required' => 'L\'indirizzo di spedizione è obbligatorio per le spedizioni',
            'address-save-failed' => 'Impossibile salvare l\'indirizzo',
            'address-saved' => 'Indirizzo salvato con successo',
            'shipping-method-required' => 'Il metodo di spedizione è obbligatorio',
            'invalid-shipping-method' => 'Metodo di spedizione non valido o non disponibile',
            'shipping-method-save-failed' => 'Impossibile salvare il metodo di spedizione',
            'shipping-method-saved' => 'Metodo di spedizione salvato con successo',
            'shipping-method-error' => 'Errore durante il salvataggio del metodo di spedizione',
            'payment-method-required' => 'Il metodo di pagamento è obbligatorio',
            'invalid-payment-method' => 'Metodo di pagamento non valido o non disponibile',
            'payment-method-save-failed' => 'Impossibile salvare il metodo di pagamento',
            'payment-method-saved' => 'Metodo di pagamento salvato con successo',
            'payment-method-error' => 'Errore durante il salvataggio del metodo di pagamento',
            'order-placed' => 'Ordine effettuato con successo',
            'order-redirect-required' => 'Questo metodo di pagamento richiede che l\'acquirente completi il pagamento sul gateway. Indirizzalo a redirectUrl; l\'ordine viene creato una volta che il gateway conferma il pagamento.',
            'order-creation-failed' => 'Creazione dell\'ordine non riuscita: l\'ID dell\'ordine è nullo o l\'ordine non è stato salvato',
            'order-retrieval-failed' => 'Impossibile recuperare l\'ordine creato',
            'order-creation-error' => 'Impossibile creare l\'ordine',
            'cart-empty' => 'Il carrello è vuoto',
            'account-suspended' => 'Il tuo account è stato sospeso. Contatta l\'assistenza.',
            'account-inactive' => 'Il tuo account non è attivo. Contatta l\'assistenza.',
            'minimum-order-not-met' => 'L\'importo minimo dell\'ordine è :amount',
            'guest-checkout-not-allowed' => 'Accedi per completare l\'acquisto. Il checkout come ospite non è disponibile per questo carrello.',
            'email-required' => 'L\'indirizzo email è obbligatorio per la creazione dell\'ordine',
            'unknown-operation' => 'Operazione di checkout sconosciuta',
        ],

        'customer-addresses' => [
            'token-required' => 'Il token è obbligatorio per recuperare gli indirizzi del cliente',
            'invalid-or-expired-token' => 'Token non valido o scaduto',
            'token-validation-failed' => 'Convalida del token non riuscita',
        ],

        'product' => [
            'not-found' => 'Prodotto non trovato',
            'not-found-with-sku' => 'Nessun prodotto trovato con lo SKU',
            'not-found-with-url-key' => 'Nessun prodotto trovato con la chiave URL',
            'parameters-required' => 'È necessario fornire almeno uno dei seguenti parametri: "sku", "id", "urlKey"',
            'type' => 'Tipo di prodotto',
            'attribute-family' => 'Famiglia di attributi',
            'sku' => 'SKU',
            'name' => 'Nome',
            'description' => 'Descrizione',
            'short-description' => 'Descrizione breve',
            'status' => 'Stato',
            'new' => 'Nuovo',
            'featured' => 'In evidenza',
            'price' => 'Prezzo',
            'special-price' => 'Prezzo speciale',
            'weight' => 'Peso',
            'cost' => 'Costo',
            'length' => 'Lunghezza',
            'width' => 'Larghezza',
            'height' => 'Altezza',
            'color' => 'Colore',
            'size' => 'Taglia',
            'brand' => 'Marca',
            'super-attributes' => 'Super attributi',
            'id-required' => 'L\'ID del prodotto è obbligatorio',
            'invalid-id-format' => 'Formato ID non valido. Previsto formato IRI come "/api/shop/products/1" o ID numerico',
        ],

        'downloadable-product' => [
            'download-link-not-found' => 'Link di download non trovato o scaduto',
            'purchased-link-not-found' => 'Link acquistato non trovato',
            'file-not-found' => 'File non trovato',
            'download-successful' => 'File pronto per il download',
            'token-required' => 'Token di download richiesto',
            'invalid-token' => 'Token di download non valido o scaduto',
            'token-expired' => 'Il token di download è scaduto. Si prega di generare un nuovo token',
            'access-denied' => 'Accesso negato: Non sei autorizzato a scaricare questo file',
            'redirect-external-url' => 'Reindirizzamento all\'URL di download esterno',
            'file-error' => 'Si è verificato un errore durante l\'elaborazione della richiesta di download',
            'unauthorized-access' => 'Accesso non autorizzato alla risorsa di download',
            'unauthenticated' => 'Non autorizzato: è richiesta l\'autenticazione del cliente',
            'link-id-required' => 'downloadableLinkPurchasedId è obbligatorio',
            'link-not-found' => 'Link scaricabile non trovato',
            'not-owner' => 'Non autorizzato: questo link di download non ti appartiene',
            'pending' => 'Il download è in sospeso. Attendi che l\'ordine venga fatturato.',
            'limit-exceeded' => 'Limite di download superato',
            'no-downloads-left' => 'Nessun altro download disponibile',
        ],

        'booking-slot' => [
            'id-required' => 'L\'id del prodotto di prenotazione è obbligatorio.',
            'date-required' => 'La data è obbligatoria.',
            'product-not-found' => 'Prodotto di prenotazione non trovato.',
            'invalid-type' => 'Questo tipo di prenotazione non è supportato.',
            'invalid-time-range' => 'Intervallo orario non valido.',
            'invalid-time-format' => 'Formato orario non valido.',
        ],

        'resolver' => [
            'no-resolve-info' => 'GraphQL ResolveInfo non disponibile nel contesto. Impossibile determinare la classe del modello.',
            'unknown-resource-class' => 'Impossibile determinare la classe della risorsa dal tipo di ritorno GraphQL',
        ],

        'docs' => [
            'openapi-unavailable' => 'Impossibile istanziare la OpenAPI Factory',
        ],

        'locale' => [
            'id-required' => 'L\'ID della lingua è obbligatorio',
            'invalid-id-format' => 'Formato ID non valido. Previsto formato IRI come "/api/shop/locales/1" o ID numerico',
            'not-found' => 'Lingua non trovata',
        ],

        'category' => [
            'id-required' => 'L\'ID della categoria è obbligatorio',
            'invalid-id-format' => 'Formato ID non valido. Previsto formato IRI come "/api/shop/categories/1" o ID numerico',
            'not-found' => 'Categoria non trovata',
        ],

        'compare-item' => [
            'id-required' => 'L\'ID dell\'elemento di confronto è obbligatorio',
            'invalid-id-format' => 'Formato ID non valido. Formato IRI previsto come "/api/shop/compare-items/1" o ID numerico',
            'not-found' => 'Elemento di confronto non trovato',
            'product-id-required' => 'L\'ID del prodotto è obbligatorio',
            'customer-id-required' => 'L\'ID del cliente è obbligatorio',
            'product-not-found' => 'Prodotto non trovato',
            'customer-not-found' => 'Cliente non trovato',
            'already-exists' => 'Questo prodotto è già nel tuo elenco di confronto',
            'delete-all-success' => 'Tutti gli articoli in confronto sono stati rimossi con successo',
        ],

        'wishlist' => [
            'id-required' => 'L\'ID dell\'articolo della lista dei desideri è obbligatorio',
            'invalid-id-format' => 'Formato ID non valido. Previsto formato IRI come "/api/shop/wishlists/1" o ID numerico',
            'not-found' => 'Articolo della lista dei desideri non trovato',
            'product-id-required' => 'L\'ID del prodotto è obbligatorio',
            'customer-id-required' => 'L\'ID del cliente è obbligatorio',
            'product-not-found' => 'Prodotto non trovato',
            'product-disabled' => 'Questo prodotto è attualmente disabilitato',
            'customer-not-found' => 'Cliente non trovato',
            'already-exists' => 'Questo prodotto è già nella tua lista dei desideri',
            'added' => 'Articolo aggiunto alla lista dei desideri con successo',
            'removed' => 'Articolo rimosso dalla lista dei desideri con successo',
            'product-removed' => 'Il prodotto è stato rimosso',
            'wishlist-item-id-required' => 'L\'ID dell\'articolo della lista dei desideri è obbligatorio',
            'invalid-quantity' => 'La quantità deve essere maggiore di 0',
            'move-to-cart-missing-options' => 'Al prodotto mancano opzioni obbligatorie. Configuralo manualmente',
            'moved-to-cart-success' => 'Articolo spostato nel carrello con successo',
            'delete-all-success' => 'Tutti gli articoli della lista dei desideri sono stati rimossi con successo',
        ],

        'gdpr' => [
            'disabled' => 'Le richieste di dati GDPR sono disabilitate. Abilita il GDPR dalla configurazione di amministrazione.',
            'unauthenticated' => 'Devi aver effettuato l\'accesso per gestire le richieste di dati GDPR.',
            'not-found' => 'Richiesta di dati GDPR non trovata',
            'type-required' => 'Il tipo di richiesta è obbligatorio',
            'type-invalid' => 'Il tipo di richiesta deve essere "delete" o "update"',
            'message-required' => 'Il messaggio è obbligatorio',
            'raised' => 'La tua richiesta di dati GDPR è stata inoltrata con successo.',
            'revoked' => 'La tua richiesta di dati GDPR è stata revocata con successo.',
            'revoke-not-allowed' => 'Questa richiesta di dati GDPR non può più essere revocata.',
            'deleted' => 'La tua richiesta di dati GDPR è stata eliminata con successo.',
        ],

        'section' => [
            'id-required' => 'L\'ID della sezione è obbligatorio',
            'invalid-id-format' => 'Formato ID non valido. Previsto formato IRI come "/api/shop/sections/1" o ID numerico',
            'not-found' => 'Sezione non trovata',
        ],

        'install' => [
            'starting' => 'Avvio dell\'installazione di API Platform...',
            'completed-success' => '✓ Installazione di API Platform completata con successo!',
            'failed' => '✗ Installazione non riuscita: ',

            'provider-already-registered' => 'ℹ Service Provider già registrato',
            'provider-not-registered' => '⚠ Impossibile registrare automaticamente il Service Provider. Aggiungi :provider a bootstrap/providers.php manualmente.',
            'provider-registered' => '✓ Service Provider registrato in bootstrap/providers.php',
            'provider-file-not-found' => 'File non trovato: :file',
            'provider-permission-denied' => 'Permesso negato: impossibile scrivere in :file. Controlla i permessi del file.',

            'config-already-exists' => 'ℹ Il file di configurazione esiste già',
            'config-published' => '✓ Configurazione di API Platform pubblicata in config/',
            'config-source-not-found' => 'File di configurazione sorgente non trovato: :file',
            'config-permission-denied' => 'Permesso negato: impossibile scrivere in :directory. Controlla i permessi della directory.',

            'translatable-not-found' => 'ℹ TranslatableModel non trovato - operazione saltata',
            'translatable-already-abstract' => 'ℹ TranslatableModel è già astratto',
            'translatable-made-abstract' => '✓ TranslatableModel reso astratto',
            'translatable-permission-denied' => 'Permesso negato: impossibile scrivere in :file. Controlla i permessi del file.',

            'providers-already-registered' => 'ℹ I provider di API Platform sono già registrati',
            'providers-registered' => '✓ Provider di API Platform registrati in bootstrap/app.php',
            'providers-file-not-found' => 'File non trovato: :file',
            'providers-permission-denied' => 'Permesso negato: impossibile scrivere in :file. Controlla i permessi del file.',
            'providers-not-found' => 'Impossibile trovare la creazione dell\'Application in bootstrap/app.php',

            'assets-published' => '✓ Asset del pacchetto pubblicati',
            'running-migrations' => 'Esecuzione delle migrazioni del database...',
            'migrations-completed' => '✓ Migrazioni del database completate',
            'clearing-caches' => 'Pulizia e ottimizzazione delle cache in corso...',
            'caches-optimized' => '✓ Cache pulite e ottimizzate',
            'generating-api-key' => 'Generazione della chiave API...',
            'api-key-generated' => '✓ Chiave API generata con successo',
            'api-key-already-exists' => 'ℹ La chiave API predefinita del negozio esiste già - generazione saltata',

            'migrations-error' => 'Migrazioni del database non riuscite.',
            'migrations-error-running' => 'Errore durante l\'esecuzione delle migrazioni del database: :error',
            'cache-clear-error' => 'Pulizia della cache non riuscita.',
            'cache-optimize-error' => 'Ottimizzazione non riuscita.',
            'cache-error' => 'Errore durante la pulizia e l\'ottimizzazione delle cache: :error',
            'api-key-generation-error' => 'Generazione della chiave API non riuscita.',
            'api-key-error' => 'Errore durante la generazione della chiave API: :error',

            'key-management-required' => '--key è obbligatorio per l\'azione :action',
            'key-rotation-error' => 'Errore durante la rotazione della chiave: :error',
            'key-deactivation-error' => 'Errore durante la disattivazione della chiave: :error',
            'key-not-found' => 'Chiave API non trovata: :identifier',

            'maintenance-starting' => '🔄 Avvio della manutenzione delle chiavi API...',
            'maintenance-complete' => '✅ Manutenzione delle chiavi API completata',
            'cleanup-expired-keys' => '🧹 Pulizia delle chiavi scadute...',
            'cleanup-expired-none' => '   ℹ️ Nessuna chiave scaduta da eliminare',
            'invalidate-deprecated' => '⚠️ Invalidazione delle chiavi deprecate...',
            'invalidate-deprecated-none' => '   ℹ️ Nessuna chiave deprecata da invalidare',
            'notify-expiring' => '📧 Invio delle notifiche di scadenza...',
            'notify-expiring-none' => '   ℹ️ Nessuna chiave richiede notifiche',

            'key-rotated-success' => '✅ Chiave ruotata con successo!',
            'key-deactivated-success' => '✅ Chiave disattivata con successo!',
            'deactivation-cancelled' => 'Disattivazione annullata.',
            'cleanup-cancelled' => 'Pulizia annullata.',

            'key-status-title' => 'Stato della chiave: :name',
            'key-status-active' => 'Attiva: :status',
            'key-status-usable' => 'Utilizzabile: :status',
            'key-status-expired' => 'Scaduta: :status',
            'key-status-deprecated' => 'Deprecata: :status',
            'key-status-expires-at' => 'Scade il: :expires',
            'key-status-days-until-expiry' => 'Giorni alla scadenza: :days',
            'key-status-last-used' => 'Ultimo utilizzo: :date',
            'key-status-rotated-from' => 'Ruotata da: :key',
            'key-status-rotated-keys' => 'Chiavi ruotate da questa: :count',

            'policy-compliance-summary' => 'Riepilogo della conformità alla politica di rotazione delle chiavi API',
            'available-actions' => 'Azioni disponibili: rotate, deactivate, cleanup, status, expiring, unused, summary',

            'asset-linked-success' => '✓ Asset di API Platform collegati con successo',
            'symlink-create-failed' => 'Impossibile creare il collegamento simbolico, copia degli asset in corso...',
            'asset-copy-warning' => 'Attenzione: impossibile collegare o copiare gli asset di API Platform. Potrebbe essere necessaria una configurazione manuale.',
            'asset-copied-success' => '✓ Asset di API Platform copiati con successo',

            'key-name-required' => 'Il nome della chiave storefront non può essere vuoto.',
            'key-generated-success' => 'Chiave storefront generata con successo!',
            'key-details' => 'Dettagli della chiave:',
            'key-secure-warning' => 'Conserva questa chiave in modo sicuro! Verrà utilizzata nell\'intestazione X-STOREFRONT-KEY.',
            'key-share-warning' => 'Non condividere questa chiave pubblicamente e non inserirla nel controllo di versione.',

            'key-already-exists' => 'Esiste già una chiave storefront con nome \':name\'.',
            'cleanup-success' => '✅ Eliminate :count chiavi scadute',
            'confirm-cleanup' => 'Questa operazione eliminerà in modo soft tutte le chiavi scadute. Continuare?',
            'confirm-deactivate' => 'Sei sicuro di voler disattivare la chiave: :name?',

            'old-key' => 'Vecchia chiave: :name',
            'old-key-id' => 'ID vecchia chiave: :id',
            'deprecation-date' => 'Data di deprecazione: :date',
            'new-key' => 'Nuova chiave: :name',
            'new-key-id' => 'ID nuova chiave: :id',
            'new-key-value' => 'Valore nuova chiave: :key',
            'expires-at' => 'Scade il: :date',
            'invalid-action' => 'Azione non valida: :action',
            'key-active' => 'Attiva: :status',
            'key-usable' => 'Utilizzabile: :status',
            'key-expired' => 'Scaduta: :status',
            'key-deprecated' => 'Deprecata: :status',
            'key-field-id' => 'ID',
            'key-field-name' => 'Nome',
            'key-field-key' => 'Chiave',
            'key-field-rate-limit' => 'Limite di richieste',
            'key-field-status' => 'Stato',
            'key-days' => ' giorni',
            'key-never' => 'Mai',
            'key-requests-minute' => ' richieste/minuto',
            'key-unlimited' => 'Illimitato',
            'rate-limit-exceeded' => 'Il limite di richieste indicato supera il massimo consentito di :max. Impostato a :max richieste/minuto.',

            'vendor-path-not-found' => 'Percorso vendor di API Platform non trovato in: :path',
            'assets-already-linked' => 'Asset di API Platform già collegati in: :path',
            'no-keys-expiring' => 'Nessuna chiave in scadenza nei prossimi :days giorni',
            'keys-expiring-title' => 'Chiavi in scadenza nei prossimi :days giorni:',
            'key-display-format' => '• :name (ID: :id)',
            'key-expires-display' => '  Scade il: :date (:days giorni rimanenti)',
            'no-unused-keys' => 'Nessuna chiave inutilizzata trovata (> :days giorni)',
            'unused-keys-title' => 'Chiavi inutilizzate (> :days giorni):',
            'key-last-used-display' => '  Ultimo utilizzo: :date',
            'total-keys' => 'Chiavi totali: :count',
            'valid-keys' => 'Chiavi valide: :count',
            'expired-keys' => 'Chiavi scadute: :count',
            'deprecated-keys' => 'Chiavi deprecate: :count',
            'keys-expiring-soon' => 'Chiavi in scadenza a breve (7 giorni): :count',
            'unused-keys-summary' => 'Chiavi inutilizzate (90 giorni): :count',
            'recently-rotated' => 'Ruotate di recente (30 giorni): :count',
            'cleanup-success-message' => '   ✅ Eliminate :count chiavi scadute',
            'invalidate-success-message' => '   ✅ Invalidate :count chiavi deprecate',
            'notify-success-message' => '   ✅ Inviate :count notifiche di scadenza',
            'notify-failed-message' => '   ⚠️ Impossibile inviare la notifica per :key: :error',
            'publish-assets-warning' => 'Attenzione: impossibile pubblicare gli asset del pacchetto. :error',
            'env-file-not-found' => 'Il file .env non è stato trovato. Crealo prima dell\'installazione.',
            'env-permission-denied' => 'Il file .env non è scrivibile. Controlla i suoi permessi.',
            'env-config-saved' => '✓ Configurazione dell\'ambiente di API Platform salvata con successo',
            'api-endpoints' => 'Endpoint API:',
            'api-documentation' => 'Documentazione API: :url',
            'api-landing-page' => 'Pagina iniziale API: :url',
            'graphql-playground' => 'GraphQL Playground: :url',
            'rest-api-storefront' => 'Swagger REST API Storefront: :url',
            'rest-api-admin' => 'Swagger REST API Admin: :url',
            'generating-jwt-keys' => 'Generazione delle chiavi di firma JWT per i token dell\'API di amministrazione...',
            'jwt-keys-generated' => '✓ Le chiavi di firma JWT sono pronte',
            'jwt-keys-warning' => 'Attenzione: impossibile generare le chiavi di firma JWT. Eseguire manualmente php artisan bagisto-api:jwt-keys. :error',
            'admin-login' => 'Accesso all\'API di amministrazione: :url',
            'admin-api-tokens' => 'Token dell\'API di amministrazione (integrazioni server): :url',
            'key-rotation-invalid-key' => 'Impossibile ruotare una chiave non valida o scaduta',
            'env-file-not-found' => 'File .env non trovato',
            'env-permission-denied' => 'Permesso negato: impossibile scrivere nel file .env. Controlla i permessi del file.',
        ],

        'attribute-translation' => [
            'id-required' => 'L\'ID della traduzione dell\'attributo è obbligatorio',
            'invalid-id-format' => 'Formato ID non valido. Previsto formato IRI come "/api/shop/attribute-translations/1" o ID numerico',
            'not-found' => 'Traduzione dell\'attributo non trovata',
        ],

        'currency' => [
            'id-required' => 'L\'ID della valuta è obbligatorio',
            'invalid-id-format' => 'Formato ID non valido. Previsto formato IRI come "/api/shop/currencies/1" o ID numerico',
            'not-found' => 'Valuta non trovata',
        ],

        'customer-address' => [
            'id-required' => 'L\'ID dell\'indirizzo del cliente è obbligatorio',
            'invalid-id-format' => 'Formato ID non valido. Previsto formato IRI come "/api/shop/customer-addresses/1" o ID numerico',
        ],

        'customer-order-shipment-item' => [
            'id-required' => 'L\'ID dell\'articolo della spedizione è obbligatorio',
            'invalid-id-format' => 'Formato ID non valido. Previsto formato IRI come "/api/shop/customer-order-shipment-items/1" o ID numerico',
            'not-found' => 'Articolo della spedizione non trovato',
        ],

        'page' => [
            'id-required' => 'L\'ID della pagina è obbligatorio',
            'invalid-id-format' => 'Formato ID non valido. Previsto formato IRI come "/api/shop/pages/1" o ID numerico',
            'not-found' => 'Pagina non trovata',
        ],

        'page-translation' => [
            'id-required' => 'L\'ID della traduzione della pagina è obbligatorio',
            'invalid-id-format' => 'Formato ID non valido. Previsto formato IRI come "/api/shop/page-translations/1" o ID numerico',
            'not-found' => 'Traduzione della pagina non trovata',
        ],

        'product-bundle-option' => [
            'id-required' => 'L\'ID dell\'opzione del bundle è obbligatorio',
            'invalid-id-format' => 'Formato ID non valido. Previsto formato IRI come "/api/shop/product-bundle-options/1" o ID numerico',
            'not-found' => 'Opzione del bundle non trovata',
        ],

        'product-bundle-option-product' => [
            'id-required' => 'L\'ID del prodotto dell\'opzione del bundle è obbligatorio',
            'invalid-id-format' => 'Formato ID non valido. Previsto formato IRI come "/api/shop/product-bundle-option-products/1" o ID numerico',
            'not-found' => 'Prodotto dell\'opzione del bundle non trovato',
        ],

        'product-downloadable-link' => [
            'id-required' => 'L\'ID del link scaricabile è obbligatorio',
            'invalid-id-format' => 'Formato ID non valido. Previsto formato IRI come "/api/shop/product-downloadable-links/1" o ID numerico',
            'not-found' => 'Link scaricabile non trovato',
        ],

        'product-downloadable-sample' => [
            'id-required' => 'L\'ID del campione scaricabile è obbligatorio',
            'invalid-id-format' => 'Formato ID non valido. Previsto formato IRI come "/api/shop/product-downloadable-samples/1" o ID numerico',
            'not-found' => 'Campione scaricabile non trovato',
        ],

        'product-grouped-product' => [
            'id-required' => 'L\'ID del prodotto raggruppato è obbligatorio',
            'invalid-id-format' => 'Formato ID non valido. Previsto formato IRI come "/api/shop/product-grouped-products/1" o ID numerico',
            'not-found' => 'Prodotto raggruppato non trovato',
        ],

        'product-images' => [
            'id-required' => 'L\'ID dell\'immagine del prodotto è obbligatorio',
            'invalid-id-format' => 'Formato ID non valido. Previsto formato IRI come "/api/shop/product-images/1" o ID numerico',
            'not-found' => 'Immagine del prodotto non trovata',
        ],

        'product-videos' => [
            'id-required' => 'L\'ID del video del prodotto è obbligatorio',
            'invalid-id-format' => 'Formato ID non valido. Previsto formato IRI come "/api/shop/product-videos/1" o ID numerico',
            'not-found' => 'Video del prodotto non trovato',
        ],

        'theme-customization' => [
            'id-required' => 'L\'ID della personalizzazione del tema è obbligatorio',
            'invalid-id-format' => 'Formato ID non valido. Previsto formato IRI come "/api/shop/theme-customizations/1" o ID numerico',
            'not-found' => 'Personalizzazione del tema non trovata',
        ],

        'unknown-resource' => [
            'id-required' => 'L\'ID della risorsa è obbligatorio',
            'not-found' => 'Risorsa non trovata',
        ],
    ],

    'admin' => [
        'profile' => [
            'unauthenticated' => 'Non sei autenticato.',
        ],

        'dashboard' => [
            'invalid-type' => 'Tipo di statistica della dashboard sconosciuto. Tipi validi: over-all, today, stock-threshold-products, total-sales, top-selling-products, top-customers.',
        ],

        'rma' => [
            'not-found' => 'Richiesta di reso non trovata.',
            'invalid-input' => 'Dettagli del reso mancanti o non validi.',
            'invalid-order' => 'L\'ordine selezionato non è valido.',
            'invalid-item' => 'L\'articolo selezionato non è idoneo al reso.',
            'invalid-resolution-type' => 'Il tipo di risoluzione deve essere "return" o "cancel_items".',
            'qty-exceeds' => 'La quantità richiesta supera la quantità restituibile.',
            'created' => 'Richiesta di reso creata con successo.',
            'status-updated' => 'Stato del reso aggiornato con successo.',
            'invalid-status' => 'Stato del reso non valido.',
            'reopened' => 'Richiesta di reso riaperta con successo.',
            'reopen-not-allowed' => 'Questa richiesta di reso non può essere riaperta.',
            'refund-failed' => 'Impossibile creare il rimborso per questo reso.',
            'message-sent' => 'Messaggio inviato con successo.',
            'no-permission' => 'Non hai l\'autorizzazione per eseguire questa azione.',
            'reason-not-found' => 'Motivo del reso non trovato.',
            'reason-deleted' => 'Motivo del reso eliminato con successo.',
            'status-not-found' => 'Stato del reso non trovato.',
            'status-deleted' => 'Stato del reso eliminato con successo.',
            'status-default-undeletable' => 'Uno stato di reso predefinito non può essere eliminato.',
            'rule-not-found' => 'Regola di reso non trovata.',
            'rule-deleted' => 'Regola di reso eliminata con successo.',
            'custom-field-not-found' => 'Campo personalizzato del reso non trovato.',
            'custom-field-deleted' => 'Campo personalizzato del reso eliminato con successo.',
            'custom-field-options-required' => 'È richiesta almeno un\'opzione per questo tipo di campo.',
            'mass-indices-required' => 'È richiesto almeno un id.',
            'mass-delete-success' => 'Elementi selezionati eliminati.',
            'mass-update-success' => 'Elementi selezionati aggiornati.',
            'mass-value-invalid' => 'Il valore dello stato deve essere 0 o 1.',
            'image-not-found' => 'Immagine del reso non trovata.',
            'attachment-not-found' => 'Allegato del messaggio non trovato.',
        ],

        'eu-withdrawal' => [
            'not-found' => 'Dichiarazione di recesso non trovata.',
            'no-permission' => 'Non hai l\'autorizzazione per gestire i recessi UE.',
            'declined' => 'Recesso contrassegnato come rifiutato.',
            'refunded' => 'Recesso contrassegnato come rimborsato.',
            'confirmation-resent' => 'Email di conferma inviata di nuovo con successo.',
            'confirmation-failed' => 'Impossibile inviare l\'email di conferma.',
        ],

        'reporting' => [
            'invalid-entity' => 'Entità di reportistica sconosciuta.',
            'invalid-type' => 'Tipo di statistica di reportistica sconosciuto per questa entità.',
            'export-format-unsupported' => 'Sono supportati solo i formati di esportazione csv, xls e xlsx.',
        ],

        'configuration' => [
            'unauthenticated' => 'Non sei autenticato.',
            'no-permission' => 'Non hai l\'autorizzazione per aggiornare la configurazione.',
            'slug-required' => 'Il parametro di query `slug` è obbligatorio.',
            'slug-not-found' => 'Nessuna sezione di configurazione è registrata per lo slug indicato.',
            'values-required' => 'È richiesto almeno un valore in `values`.',
            'scope-escape' => 'La chiave `:key` è al di fuori del sottoalbero dello slug richiesto.',
            'unknown-field' => 'La chiave `:key` non è un campo di configurazione registrato.',
            'validation-failed' => 'Uno o più valori di configurazione non hanno superato la convalida.',
            'file-upload-rest-only' => 'I caricamenti di file binari per il campo `:field` sono supportati solo tramite l\'endpoint REST multipart, non tramite GraphQL.',
            'custom-view-readonly' => 'Il campo `:field` è un campo personalizzato renderizzato con blade e non può essere aggiornato tramite l\'API.',
            'update-success' => 'Configurazione aggiornata con successo.',
        ],

        'customer' => [
            'not-found' => 'Cliente non trovato.',
            'no-permission' => 'Non hai l\'autorizzazione per eseguire questa azione.',
            'has-active-orders' => 'Il cliente ha ordini in sospeso o in elaborazione e non può essere eliminato.',
            'deleted' => 'Cliente eliminato con successo.',
            'mass-delete-indices-required' => 'È richiesto almeno un id cliente.',
            'mass-delete-success' => 'Clienti eliminati.',
            'mass-update-indices-required' => 'È richiesto almeno un id cliente.',
            'mass-update-value-invalid' => 'Il valore dello stato deve essere 0 o 1.',
            'mass-update-success' => 'Stato del cliente aggiornato.',

            'address' => [
                'not-found' => 'Indirizzo non trovato.',
                'not-owned' => 'Questo indirizzo non appartiene al cliente specificato.',
                'customer-id-required' => 'customerId è obbligatorio per identificare il proprietario dell\'indirizzo.',
                'deleted' => 'Indirizzo eliminato con successo.',
            ],

            'note' => [
                'empty' => 'Il testo della nota è obbligatorio.',
                'created' => 'Nota aggiunta con successo.',
            ],

            'review' => [
                'not-found' => 'Recensione non trovata.',
                'no-permission' => 'Non hai l\'autorizzazione per eseguire questa azione.',
                'invalid-status' => 'Lo stato deve essere uno tra: pending, approved, disapproved.',
                'updated' => 'Recensione aggiornata con successo.',
                'deleted' => 'Recensione eliminata con successo.',
                'mass-delete-indices-required' => 'È richiesto almeno un id recensione.',
                'mass-delete-success' => 'Recensioni eliminate.',
                'mass-update-indices-required' => 'È richiesto almeno un id recensione.',
                'mass-update-value-invalid' => 'Il valore dello stato deve essere uno tra: pending, approved, disapproved.',
                'mass-update-success' => 'Stato della recensione aggiornato.',
            ],

            'group' => [
                'not-found' => 'Gruppo di clienti non trovato.',
                'deleted' => 'Gruppo di clienti eliminato con successo.',
                'is-system' => 'I gruppi di clienti di sistema non possono essere eliminati.',
                'has-customers' => 'Il gruppo di clienti ha clienti associati e non può essere eliminato.',
                'system-code-immutable' => 'Il codice di un gruppo di clienti di sistema non può essere modificato.',
                'system-flag-immutable' => 'Il flag is_user_defined di un gruppo di clienti di sistema non può essere modificato.',
                'mass-delete-indices-required' => 'È richiesto almeno un id gruppo di clienti.',
                'mass-delete-success' => 'Gruppi di clienti eliminati.',
            ],

            'gdpr' => [
                'not-found' => 'Richiesta GDPR non trovata.',
                'no-permission' => 'Non hai l\'autorizzazione per eseguire questa azione.',
                'deleted' => 'Richiesta GDPR eliminata con successo.',
                'invalid-status' => 'Lo stato deve essere uno tra: pending, processing, declined, approved, revoked.',
                'no-changes' => 'È richiesto almeno uno tra stato e messaggio.',
                'already-processed' => 'Questa richiesta GDPR è già stata elaborata e non può essere rieseguita.',

                'process' => [
                    'approved' => 'Richiesta GDPR approvata.',
                    'deleted' => 'Richiesta GDPR approvata e cliente eliminato.',
                ],
            ],
        ],

        'order' => [
            'not-found' => 'Ordine non trovato.',

            'reorder' => [
                'success' => 'Riordino riuscito. È stato creato un nuovo carrello in bozza.',
                'cannot-reorder' => 'Questo ordine non può essere riordinato (ordine ospite o uno o più articoli non sono più vendibili).',
                'failed' => 'Riordino non riuscito. Riprova.',
                'guest-not-supported' => 'Il riordino non è supportato per gli ordini degli ospiti.',
                'items-not-saleable' => 'Uno o più articoli di questo ordine non sono più disponibili per l\'acquisto.',
                'no-permission' => 'Non hai l\'autorizzazione per creare ordini.',
                'disabled-in-settings' => 'Il riordino da parte dell\'amministratore è disabilitato nelle impostazioni del negozio.',
            ],

            'actions' => [
                'cancel' => [
                    'closed' => 'Gli ordini chiusi non possono essere annullati.',
                    'fraud' => 'Gli ordini fraudolenti non possono essere annullati.',
                    'nothing-to-cancel' => 'Non c\'è nulla da annullare in questo ordine.',
                    'already-processed' => 'Questo ordine è già stato fatturato o spedito e non può più essere annullato. Puoi invece emettere un rimborso.',
                    'no-permission' => 'Non hai l\'autorizzazione per annullare gli ordini.',
                    'success' => 'Ordine annullato con successo.',
                    'failed' => 'Impossibile annullare l\'ordine.',
                ],

                'comment' => [
                    'empty' => 'Il commento è obbligatorio.',
                    'success' => 'Commento aggiunto all\'ordine.',
                    'failed' => 'Impossibile aggiungere il commento.',
                ],

                'invoice' => [
                    'closed' => 'Gli ordini chiusi non possono essere fatturati.',
                    'fraud' => 'Gli ordini fraudolenti non possono essere fatturati.',
                    'nothing-to-invoice' => 'Non c\'è nulla da fatturare in questo ordine.',
                    'already-invoiced' => 'È già stata generata una fattura per questo ordine.',
                    'paypal-standard-blocked' => 'Non è possibile creare fatture per ordini pagati tramite PayPal Standard.',
                    'no-permission' => 'Non hai l\'autorizzazione per creare fatture.',
                    'items-required' => 'È richiesto almeno un articolo con quantità positiva.',
                    'qty-exceeds' => 'La quantità richiesta (:requested) supera la quantità disponibile (:available) per lo SKU :sku.',
                    'not-found' => 'Fattura non trovata.',
                    'success' => 'Fattura creata con successo.',
                    'failed' => 'Impossibile creare la fattura.',
                    'pdf-failed' => 'Impossibile generare il PDF della fattura.',
                    'email-invalid' => 'È richiesta un\'email del destinatario valida.',
                    'duplicate-sent' => 'Email della fattura inviata a :email.',
                    'mass-status-indices-required' => 'È richiesto almeno un id fattura.',
                    'mass-status-value-invalid' => 'Lo stato deve essere uno tra: pending, paid, overdue.',
                    'mass-status-success' => 'Stato della fattura aggiornato con successo.',
                ],

                'shipment' => [
                    'closed' => 'Gli ordini chiusi non possono essere spediti.',
                    'fraud' => 'Gli ordini fraudolenti non possono essere spediti.',
                    'nothing-to-ship' => 'Non c\'è nulla da spedire in questo ordine.',
                    'no-permission' => 'Non hai l\'autorizzazione per spedire gli ordini.',
                    'source-required' => 'La sorgente di inventario è obbligatoria.',
                    'items-required' => 'È richiesto almeno un articolo con quantità positiva.',
                    'qty-exceeds' => 'La quantità richiesta (:requested) supera la quantità disponibile (:available) per lo SKU :sku.',
                    'inventory-insufficient' => 'L\'inventario nella sorgente selezionata è insufficiente per lo SKU :sku.',
                    'not-found' => 'Spedizione non trovata.',
                    'success' => 'Spedizione creata con successo.',
                    'failed' => 'Impossibile creare la spedizione.',
                ],

                'refund' => [
                    'closed' => 'Gli ordini chiusi non possono essere rimborsati.',
                    'fraud' => 'Gli ordini fraudolenti non possono essere rimborsati.',
                    'nothing-to-refund' => 'Non è rimasto nulla da rimborsare in questo ordine.',
                    'no-permission' => 'Non hai l\'autorizzazione per creare rimborsi.',
                    'qty-exceeds' => 'La quantità richiesta (:requested) supera la quantità disponibile (:available) per lo SKU :sku.',
                    'amount-zero' => 'L\'importo del rimborso calcolato è zero. Modifica la quantità, la spedizione o i valori di rettifica.',
                    'amount-exceeds-max' => 'L\'importo del rimborso (:amount) supera l\'importo massimo rimborsabile (:max).',
                    'not-found' => 'Rimborso non trovato.',
                    'success' => 'Rimborso creato con successo.',
                    'failed' => 'Impossibile creare il rimborso.',
                ],
            ],
        ],

        'appearance' => [
            'no-permission' => 'Non hai l\'autorizzazione per gestire l\'aspetto.',

            'theme' => [
                'not-found' => 'Tema non trovato.',
                'not-installed' => 'Questo tema non è installato.',
                'channel-ids-required' => 'È richiesto almeno un canale.',
                'unknown-channel' => 'Uno o più canali non esistono.',
                'activated' => 'Tema attivato con successo.',
            ],

            'section' => [
                'not-found' => 'Sezione non trovata.',
                'invalid-payload' => 'Payload della sezione non supportato.',
                'footer-links-exists' => 'Questo canale ha già una sezione di link del footer.',
                'options-required' => 'Le opzioni sono obbligatorie.',
                'status-required' => 'Lo stato è obbligatorio.',
                'section-ids-required' => 'È richiesta almeno una sezione.',
                'reorder-single-scope' => 'Le sezioni possono essere riordinate solo all\'interno di un tema e di un canale alla volta.',
                'reorder-incomplete' => 'Il riordino richiede tutte le sezioni del tema e del canale, nell\'ordine in cui devono essere visualizzate.',
                'unknown-section' => 'Una o più sezioni non esistono.',
                'file-required' => 'È richiesto un file.',
                'created' => 'Sezione creata con successo.',
                'updated' => 'Sezione aggiornata con successo.',
                'deleted' => 'Sezione eliminata con successo.',
                'draft-saved' => 'Modifiche alla sezione preparate con successo.',
                'status-staged' => 'Stato della sezione preparato con successo.',
                'order-staged' => 'Ordine delle sezioni preparato con successo.',
                'duplicated' => 'Sezione copiata con successo.',
                'published' => 'Modifiche alla sezione pubblicate con successo.',
                'discarded' => 'Modifiche alla sezione scartate con successo.',
                'media-uploaded' => 'Media caricato con successo.',
            ],
        ],

        'sales' => [
            'no-permission' => 'Non hai l\'autorizzazione per visualizzare questa risorsa di vendita.',

            'invoice' => [
                'not-found' => 'Fattura non trovata.',
                'no-permission' => 'Non hai l\'autorizzazione per visualizzare le fatture.',
            ],

            'shipment' => [
                'not-found' => 'Spedizione non trovata.',
                'no-permission' => 'Non hai l\'autorizzazione per visualizzare le spedizioni.',
            ],

            'refund' => [
                'not-found' => 'Rimborso non trovato.',
                'no-permission' => 'Non hai l\'autorizzazione per visualizzare i rimborsi.',
                'export-format-unsupported' => 'Sono supportate solo le esportazioni csv, xls e xlsx.',
            ],

            'transaction' => [
                'not-found' => 'Transazione non trovata.',
                'no-permission' => 'Non hai l\'autorizzazione per visualizzare le transazioni.',

                'create' => [
                    'invoice-required' => 'Il campo invoiceId è obbligatorio.',
                    'payment-method-required' => 'Il campo paymentMethod è obbligatorio.',
                    'amount-invalid' => 'Il campo amount è obbligatorio e deve essere numerico.',
                    'invoice-missing' => 'Impossibile trovare la fattura specificata.',
                    'already-paid' => 'Questa fattura è già stata interamente pagata.',
                    'amount-exceeds' => 'L\'importo del pagamento supera il totale complessivo della fattura.',
                    'amount-zero' => 'L\'importo del pagamento deve essere maggiore di zero.',
                ],
            ],

            'booking' => [
                'not-found' => 'Prenotazione non trovata.',
                'no-permission' => 'Non hai l\'autorizzazione per visualizzare le prenotazioni.',
            ],

            'export' => [
                'format-unsupported' => 'Sono supportate solo le esportazioni csv, xls e xlsx.',
            ],
        ],

        'cart' => [
            'not-found' => 'Carrello non trovato.',
            'not-draft' => 'Questo carrello non può essere modificato tramite l\'API di amministrazione.',
            'product-required' => 'productId è obbligatorio.',
            'product-not-found' => 'Prodotto non trovato.',
            'booking-unsupported' => 'I prodotti di prenotazione non possono essere aggiunti a un ordine in bozza dell\'amministratore. Gli acquisti di prenotazioni devono essere effettuati tramite lo storefront del cliente.',
            'product-not-saleable' => 'Questo prodotto non è disponibile per la vendita (esaurito o disabilitato) e non può essere aggiunto all\'ordine.',
            'qty-required' => 'qty è obbligatorio e deve essere un oggetto non vuoto di itemId => quantità.',
            'cart-item-required' => 'cartItemId è obbligatorio.',
            'item-added' => 'Articolo aggiunto al carrello.',
            'item-add-failed' => 'Impossibile aggiungere l\'articolo al carrello.',
            'item-updated' => 'Articoli del carrello aggiornati.',
            'item-update-failed' => 'Impossibile aggiornare gli articoli del carrello.',
            'item-removed' => 'Articolo rimosso dal carrello.',
            'item-remove-failed' => 'Impossibile rimuovere l\'articolo del carrello.',
            'address-saved' => 'Indirizzo salvato.',
            'address-required' => 'È richiesto un indirizzo di fatturazione.',
            'address-incomplete' => 'Il campo :field è obbligatorio per salvare l\'indirizzo.',
            'coupon-code-required' => 'È richiesto un codice coupon.',
            'coupon-applied' => 'Coupon applicato.',
            'coupon-already-applied' => 'Il coupon è già applicato a questo carrello.',
            'coupon-not-found' => 'Coupon non trovato o non attivo.',
            'coupon-removed' => 'Coupon rimosso.',
            'coupon-error' => 'Si è verificato un errore durante l\'applicazione del coupon.',
            'unknown-error' => 'Si è verificato un errore.',
            'draft-created' => 'Carrello in bozza creato.',
            'draft-failed' => 'Impossibile creare il carrello in bozza.',
            'addresses-required-for-shipping' => 'Gli indirizzi devono essere salvati prima di selezionare un metodo di spedizione.',
            'shipping-required-for-payment' => 'Il metodo di spedizione deve essere selezionato prima del metodo di pagamento.',
            'shipping-method-required' => 'Il metodo di spedizione è obbligatorio.',
            'shipping-method-saved' => 'Metodo di spedizione salvato.',
            'shipping-method-failed' => 'Impossibile salvare il metodo di spedizione.',
            'payment-method-required' => 'Il metodo di pagamento è obbligatorio.',
            'payment-method-saved' => 'Metodo di pagamento salvato.',
            'payment-method-failed' => 'Impossibile salvare il metodo di pagamento.',

            'place-order' => [
                'success' => 'Ordine effettuato con successo.',
                'failed' => 'Impossibile effettuare l\'ordine.',
                'error' => 'Impossibile effettuare l\'ordine: il carrello contiene errori.',
                'minimum-order-error' => 'L\'importo minimo dell\'ordine è :amount. Aggiungi altri articoli prima di effettuare l\'ordine.',
                'empty-cart' => 'Il carrello è vuoto — aggiungi articoli prima di effettuare un ordine.',
                'addresses-required' => 'Gli indirizzi di fatturazione e di spedizione devono essere salvati prima di effettuare l\'ordine.',
                'shipping-required' => 'È necessario selezionare un metodo di spedizione prima di effettuare l\'ordine.',
                'payment-required' => 'È necessario selezionare un metodo di pagamento prima di effettuare l\'ordine.',
                'payment-method-unsupported' => 'Per gli ordini effettuati dall\'amministratore sono supportati solo cashondelivery e moneytransfer.',
            ],
        ],

        'product' => [
            'not-found' => 'Prodotto non trovato.',

            'mass-delete-success' => 'Prodotti eliminati con successo.',
            'mass-update-status-success' => 'Stato dei prodotti aggiornato con successo.',
            'indices-required' => 'Il campo indices è obbligatorio e deve essere un array non vuoto.',
            'indices-invalid' => 'Il campo indices deve essere un array di ID prodotto interi.',
            'value-invalid' => 'Il campo value è obbligatorio e deve essere 0 o 1.',
            'mass-delete-failed' => 'Impossibile eliminare uno o più prodotti.',
            'no-permission' => 'Non hai l\'autorizzazione per gestire i prodotti.',

            'copy-success' => 'Prodotto copiato con successo.',
            'copy-failed' => 'Impossibile copiare il prodotto.',
            'copy-variant-not-supported' => 'Le varianti dei prodotti configurabili non possono essere copiate. Copia invece il prodotto configurabile principale.',

            'create' => [
                'created' => 'Prodotto creato con successo.',
                'create-failed' => 'Impossibile creare il prodotto.',
                'sku-required' => 'Il campo sku è obbligatorio.',
                'sku-unique' => 'Lo sku è già in uso.',
                'sku-invalid' => 'Lo sku può contenere solo lettere, numeri e trattini (ad es. "sp-001").',
                'attribute-family-required' => 'Il campo attribute_family_id è obbligatorio.',
                'attribute-family-not-found' => 'La famiglia di attributi selezionata non esiste.',
                'type-required' => 'Il campo type è obbligatorio.',
                'type-not-yet-supported' => 'Il tipo di prodotto ":type" non è supportato da questa API. Tipi consentiti: simple, virtual, downloadable, grouped, bundle, configurable, booking.',
                'super-attributes-required' => 'Il campo super_attributes è obbligatorio quando type=configurable. Fornisci una mappa dal codice (o id) dell\'attributo agli option_ids, ad es. { "color": [1,2], "size": [4,5] }.',
                'super-attributes-invalid' => 'Il campo super_attributes deve essere una mappa non vuota dal codice (o id) dell\'attributo a un elenco non vuoto di option_ids.',
                'no-permission' => 'Non hai l\'autorizzazione per creare prodotti.',
            ],

            'update' => [
                'updated' => 'Prodotto aggiornato con successo.',
                'update-failed' => 'Impossibile aggiornare il prodotto.',
                'id-required' => 'È richiesto un id prodotto.',
                'url-key-required' => 'Il campo url_key non può essere vuoto.',
                'url-key-unique' => 'La url_key è già utilizzata da un altro prodotto.',
                'special-price-invalid' => 'Lo special_price deve essere un numero inferiore al prezzo.',
                'special-price-date-range-invalid' => 'special_price_to deve essere uguale o successivo a special_price_from.',
                'boolean-field-invalid' => 'Il campo :field deve essere 0 o 1.',
                'categories-invalid' => 'Il campo categories deve essere un array di id di categorie.',
                'channels-invalid' => 'Il campo channels deve essere un array di id di canali.',
                'super-attributes-cannot-change' => 'super_attributes non può essere modificato dopo la creazione del prodotto.',
                'sub-resource-stripped-images' => 'Le immagini devono essere gestite tramite POST /api/admin/catalog/products/{id}/images. Il campo images è stato ignorato.',
                'sub-resource-stripped-inventories' => 'Gli inventari devono essere gestiti tramite PUT /api/admin/catalog/products/{id}/inventories. Il campo inventories è stato ignorato.',
                'sub-resource-stripped-customer-group-prices' => 'I prezzi per gruppo di clienti devono essere gestiti tramite /api/admin/catalog/products/{id}/customer-group-prices. Il campo customer_group_prices è stato ignorato.',
                'translations-single-locale' => 'È stata aggiornata solo la lingua richiesta. Per aggiornare le lingue :locales, invia una richiesta separata con ?locale=<code> per ciascuna.',
            ],

            'delete' => [
                'deleted' => 'Prodotto eliminato con successo.',
                'delete-failed' => 'Impossibile eliminare il prodotto.',
            ],

            'image' => [
                'uploaded' => 'Immagine del prodotto caricata con successo.',
                'updated' => 'Immagine del prodotto aggiornata con successo.',
                'update-empty' => 'Invia un testo alternativo o una posizione da aggiornare.',
                'reordered' => 'Immagini del prodotto riordinate con successo.',
                'deleted' => 'Immagine del prodotto eliminata con successo.',
                'image-required' => 'È richiesto un file immagine (campo multipart "image").',
                'image-invalid-type' => 'Il tipo di immagine deve essere uno tra: bmp, jpeg, jpg, png, webp.',
                'image-too-large' => 'L\'immagine è troppo grande. La dimensione massima consentita è 4 MB.',
                'not-found' => 'Immagine del prodotto non trovata.',
                'no-permission' => 'Non hai l\'autorizzazione per gestire le immagini dei prodotti.',
                'upload-failed' => 'Impossibile caricare l\'immagine del prodotto.',
                'reorder-failed' => 'Impossibile riordinare le immagini del prodotto.',
                'delete-failed' => 'Impossibile eliminare l\'immagine del prodotto.',
                'order-required' => 'L\'array "order" è obbligatorio e non deve essere vuoto.',
                'order-invalid' => 'Ogni voce in "order" deve essere un oggetto con id e position.',
                'id-not-on-product' => 'L\'immagine :id non appartiene a questo prodotto.',
                'graphql-upload-unsupported' => 'Il caricamento di immagini tramite GraphQL non è supportato. Usa POST /api/admin/catalog/products/{productId}/images con multipart/form-data.',
            ],

            'downloadable' => [
                'file-required' => 'È richiesto un file (campo multipart "file").',
                'file-too-large' => 'Il file è troppo grande. La dimensione massima consentita è 128 MB.',
                'upload-failed' => 'Impossibile caricare il file scaricabile.',
                'file-not-found' => 'Nessun file memorizzato trovato per questo prodotto e attributo.',
                'no-permission' => 'Non hai l\'autorizzazione per gestire i file dei prodotti scaricabili.',
            ],

            'video' => [
                'uploaded' => 'Video del prodotto caricato con successo.',
                'deleted' => 'Video del prodotto eliminato con successo.',
                'video-required' => 'È richiesto un file video (campo multipart "video").',
                'video-invalid-type' => 'Il tipo di video deve essere uno tra: mp4, webm, ogg, mov.',
                'video-too-large' => 'Il video è troppo grande. La dimensione massima consentita è 100 MB.',
                'not-found' => 'Video del prodotto non trovato.',
                'no-permission' => 'Non hai l\'autorizzazione per gestire i video dei prodotti.',
                'upload-failed' => 'Impossibile caricare il video del prodotto.',
                'delete-failed' => 'Impossibile eliminare il video del prodotto.',
                'graphql-upload-unsupported' => 'Il caricamento di video tramite GraphQL non è supportato. Usa POST /api/admin/catalog/products/{productId}/videos con multipart/form-data.',
            ],

            'inventory' => [
                'updated' => 'Inventari del prodotto salvati con successo.',
                'inventories-required' => 'Il campo inventories è obbligatorio e deve essere una mappa non vuota da inventory_source_id a quantità.',
                'inventories-invalid' => 'Il campo inventories deve essere una mappa da inventory_source_id (intero positivo) a quantità (intero non negativo).',
                'source-not-found' => 'La sorgente di inventario :id non esiste.',
                'qty-invalid' => 'Ogni quantità deve essere un numero intero non negativo.',
                'no-permission' => 'Non hai l\'autorizzazione per gestire gli inventari dei prodotti.',
                'not-found' => 'Prodotto non trovato.',
            ],

            'customer-group-price' => [
                'created' => 'Prezzo per gruppo di clienti aggiunto con successo.',
                'updated' => 'Prezzo per gruppo di clienti aggiornato con successo.',
                'deleted' => 'Prezzo per gruppo di clienti eliminato con successo.',
                'qty-required' => 'Il campo qty è obbligatorio.',
                'qty-invalid' => 'Il campo qty deve essere un numero intero maggiore o uguale a 1.',
                'value-type-required' => 'Il campo value_type è obbligatorio.',
                'value-type-invalid' => 'Il campo value_type deve essere "fixed" o "discount".',
                'value-required' => 'Il campo value è obbligatorio.',
                'value-invalid' => 'Il campo value deve essere un numero non negativo.',
                'customer-group-not-found' => 'Il gruppo di clienti selezionato non esiste.',
                'duplicate-qty-group' => 'Esiste già un prezzo per gruppo di clienti per questa combinazione (qty, customer_group_id) per il prodotto.',
                'not-found' => 'Prezzo per gruppo di clienti non trovato.',
                'no-permission' => 'Non hai l\'autorizzazione per gestire i prezzi per gruppo di clienti dei prodotti.',
            ],
        ],

        'category' => [
            'not-found' => 'Categoria non trovata.',

            'created' => 'Categoria creata con successo.',
            'updated' => 'Categoria aggiornata con successo.',
            'deleted' => 'Categoria eliminata con successo.',
            'slug-required' => 'Il campo slug è obbligatorio.',
            'slug-unique' => 'Lo slug è già in uso.',
            'name-required' => 'Il campo name è obbligatorio.',
            'position-required' => 'Il campo position è obbligatorio.',
            'attributes-required' => 'È richiesto almeno un attributo filtrabile.',
            'description-required-for-mode' => 'È richiesta una descrizione per la modalità di visualizzazione scelta.',
            'cannot-delete-root' => 'Le categorie radice e radice di canale non possono essere eliminate.',
            'cannot-delete-channel-root' => 'Questa categoria è la radice di un canale e non può essere eliminata.',
            'delete-failed' => 'Impossibile eliminare la categoria.',
            'no-permission' => 'Non hai l\'autorizzazione per gestire le categorie.',
            'mass-delete-success' => 'Categorie eliminate con successo.',
            'mass-update-status-success' => 'Stato delle categorie aggiornato con successo.',
            'mass-delete-indices-required' => 'Il campo indices è obbligatorio e deve essere un array non vuoto.',
            'mass-update-status-value-required' => 'Il campo value è obbligatorio e deve essere 0 o 1.',
        ],

        'attribute' => [
            'not-found' => 'Attributo non trovato.',

            'create-success' => 'Attributo creato con successo.',
            'update-success' => 'Attributo aggiornato con successo.',
            'delete-success' => 'Attributo eliminato con successo.',
            'delete-failed' => 'Impossibile eliminare l\'attributo.',
            'mass-delete-success' => 'Attributi eliminati con successo.',
            'mass-delete-indices-required' => 'Il campo indices è obbligatorio e deve essere un array non vuoto.',

            'code-immutable' => 'Il codice dell\'attributo non può essere modificato dopo la creazione dell\'attributo.',
            'type-immutable' => 'Il tipo di attributo non può essere modificato finché ha valori di prodotto.',
            'locale-scope-immutable' => 'Impossibile modificare l\'ambito della lingua finché l\'attributo ha valori di prodotto.',
            'system-attribute' => 'Gli attributi di sistema non possono essere eliminati.',
            'in-use-family' => 'L\'attributo fa parte di una o più famiglie di attributi (ID dei gruppi: :ids). Rimuovilo prima da quelle famiglie.',

            'option-not-found' => 'Opzione dell\'attributo non trovata.',
            'option-not-supported' => 'Il tipo di attributo ":type" non supporta le opzioni. Solo gli attributi select, multiselect e checkbox possono avere opzioni.',
            'option-in-use' => 'Questa opzione è utilizzata da :count prodotto/i e non può essere eliminata.',
            'option-delete-success' => 'Opzione dell\'attributo eliminata con successo.',
        ],

        'family' => [
            'not-found' => 'Famiglia di attributi non trovata.',

            'created' => 'Famiglia di attributi creata con successo.',
            'updated' => 'Famiglia di attributi aggiornata con successo.',
            'deleted' => 'Famiglia di attributi eliminata con successo.',
            'code-required' => 'Il codice della famiglia è obbligatorio.',
            'code-unique' => 'Il codice della famiglia deve essere univoco.',
            'name-required' => 'Il nome della famiglia è obbligatorio.',
            'group-code-required' => 'Ogni gruppo di attributi deve avere un codice.',
            'group-name-required' => 'Ogni gruppo di attributi deve avere un nome.',
            'group-column-invalid' => 'La colonna di ogni gruppo di attributi deve essere 1 o 2.',
            'group-name-duplicate' => 'Il nome di un gruppo di attributi deve essere univoco all\'interno della famiglia di attributi.',
            'update-failed' => 'Impossibile aggiornare la famiglia di attributi.',
            'create-failed' => 'Impossibile creare la famiglia di attributi.',
            'default-delete-error' => 'La famiglia di attributi predefinita non può essere eliminata.',
            'last-delete-error' => 'È richiesta almeno una famiglia di attributi.',
            'attribute-product-error' => 'Questa famiglia di attributi è utilizzata da uno o più prodotti. Riassegna tali prodotti prima di eliminarla.',
            'delete-failed' => 'Impossibile eliminare la famiglia di attributi.',
            'no-permission' => 'Non hai l\'autorizzazione per gestire le famiglie di attributi.',
        ],

        'settings' => [
            'locale' => [
                'not-found' => 'Lingua non trovata.',
                'created' => 'Lingua creata con successo.',
                'updated' => 'Lingua aggiornata con successo.',
                'deleted' => 'Lingua eliminata con successo.',
                'cannot-delete-last' => 'È richiesta almeno una lingua — impossibile eliminare l\'ultima lingua rimasta.',
                'cannot-delete-channel-default' => 'Questa lingua è la lingua predefinita di uno o più canali e non può essere eliminata.',
                'delete-failed' => 'Impossibile eliminare la lingua.',
                'no-permission' => 'Non hai l\'autorizzazione per gestire le lingue.',
                'mass-delete-success' => 'Lingue eliminate con successo.',
                'mass-delete-indices-required' => 'Il campo indices è obbligatorio e deve essere un array non vuoto.',
            ],

            'theme' => [
                'not-found' => 'Personalizzazione del tema non trovata.',
                'created' => 'Personalizzazione del tema creata con successo.',
                'updated' => 'Personalizzazione del tema aggiornata con successo.',
                'deleted' => 'Personalizzazione del tema eliminata con successo.',
                'delete-failed' => 'Impossibile eliminare la personalizzazione del tema.',
                'no-permission' => 'Non hai l\'autorizzazione per gestire le personalizzazioni del tema.',
                'mass-delete-success' => 'Personalizzazioni del tema eliminate con successo.',
                'mass-delete-indices-required' => 'Il campo indices è obbligatorio e deve essere un array non vuoto.',
                'mass-update-success' => 'Personalizzazioni del tema aggiornate con successo.',
                'mass-update-indices-required' => 'Il campo indices è obbligatorio e deve essere un array non vuoto.',
                'mass-update-value-invalid' => 'Il campo value deve essere 0 o 1.',
            ],

            'inventory-source' => [
                'not-found' => 'Sorgente di inventario non trovata.',
                'created' => 'Sorgente di inventario creata con successo.',
                'updated' => 'Sorgente di inventario aggiornata con successo.',
                'deleted' => 'Sorgente di inventario eliminata con successo.',
                'code-unique' => 'Esiste già una sorgente di inventario con questo codice.',
                'last-delete-error' => 'È richiesta almeno una sorgente di inventario — impossibile eliminare l\'ultima sorgente rimasta.',
                'in-use' => 'Questa sorgente di inventario è referenziata dagli inventari dei prodotti e non può essere eliminata.',
                'delete-failed' => 'Impossibile eliminare la sorgente di inventario.',
                'no-permission' => 'Non hai l\'autorizzazione per gestire le sorgenti di inventario.',
                'mass-delete-success' => 'Sorgenti di inventario eliminate con successo.',
                'mass-delete-indices-required' => 'Il campo indices è obbligatorio e deve essere un array non vuoto.',
            ],

            'exchange-rate' => [
                'not-found' => 'Tasso di cambio non trovato.',
                'created' => 'Tasso di cambio creato con successo.',
                'updated' => 'Tasso di cambio aggiornato con successo.',
                'deleted' => 'Tasso di cambio eliminato con successo.',
                'duplicate-pair' => 'Esiste già un tasso di cambio per questa valuta di destinazione. Aggiorna la riga esistente o eliminala prima.',
                'delete-failed' => 'Impossibile eliminare il tasso di cambio.',
                'no-permission' => 'Non hai l\'autorizzazione per gestire i tassi di cambio.',
                'mass-delete-success' => 'Tassi di cambio eliminati con successo.',
                'mass-delete-indices-required' => 'Il campo indices è obbligatorio e deve essere un array non vuoto.',
                'update-rates-success' => 'Tassi di cambio aggiornati con successo.',
                'update-rates-failed' => 'Impossibile aggiornare i tassi di cambio dal provider esterno.',
                'provider-not-configured' => 'Nessun provider esterno di tassi di cambio è configurato.',
            ],

            'tax-rate' => [
                'not-found' => 'Aliquota fiscale non trovata.',
                'created' => 'Aliquota fiscale creata con successo.',
                'updated' => 'Aliquota fiscale aggiornata con successo.',
                'deleted' => 'Aliquota fiscale eliminata con successo.',
                'delete-failed' => 'Impossibile eliminare l\'aliquota fiscale.',
                'no-permission' => 'Non hai l\'autorizzazione per gestire le aliquote fiscali.',
                'zip-code-required' => 'Il campo zip_code è obbligatorio quando is_zip è false.',
                'zip-range-required' => 'Sia zip_from che zip_to sono obbligatori quando is_zip è true.',
            ],

            'tax-category' => [
                'not-found' => 'Categoria fiscale non trovata.',
                'created' => 'Categoria fiscale creata con successo.',
                'updated' => 'Categoria fiscale aggiornata con successo.',
                'deleted' => 'Categoria fiscale eliminata con successo.',
                'code-unique' => 'Il codice è già in uso.',
                'cannot-delete-in-use' => 'Questa categoria fiscale ha ancora aliquote associate e non può essere eliminata.',
                'delete-failed' => 'Impossibile eliminare la categoria fiscale.',
                'no-permission' => 'Non hai l\'autorizzazione per gestire le categorie fiscali.',
            ],

            'role' => [
                'not-found' => 'Ruolo non trovato.',
                'created' => 'Ruolo creato con successo.',
                'updated' => 'Ruolo aggiornato con successo.',
                'deleted' => 'Ruolo eliminato con successo.',
                'permissions-required' => 'Il campo permissions è obbligatorio quando permission_type è custom.',
                'cannot-delete-in-use' => 'Questo ruolo è assegnato a uno o più amministratori e non può essere eliminato.',
                'cannot-delete-last-role' => 'È richiesto almeno un ruolo — impossibile eliminare l\'ultimo ruolo rimasto.',
                'cannot-change-last-all-access' => 'L\'unico ruolo con accesso completo non può essere trasformato in un ruolo personalizzato.',
                'delete-failed' => 'Impossibile eliminare il ruolo.',
                'no-permission' => 'Non hai il permesso di gestire i ruoli.',
            ],

            'currency' => [
                'not-found' => 'Valuta non trovata.',
                'created' => 'Valuta creata con successo.',
                'updated' => 'Valuta aggiornata con successo.',
                'deleted' => 'Valuta eliminata con successo.',
                'code-required' => 'Il campo code è obbligatorio.',
                'code-size' => 'Il codice deve essere composto esattamente da 3 caratteri alfabetici.',
                'code-unique' => 'Il codice è già in uso.',
                'name-required' => 'Il campo name è obbligatorio.',
                'cannot-delete-last' => 'È richiesta almeno una valuta — impossibile eliminare l\'ultima valuta rimasta.',
                'cannot-delete-channel-base' => 'Questa valuta è la valuta base di uno o più canali e non può essere eliminata.',
                'delete-failed' => 'Impossibile eliminare la valuta.',
                'no-permission' => 'Non hai l\'autorizzazione per gestire le valute.',

                'mass-delete-success' => 'Valute eliminate con successo.',
                'mass-delete-indices-required' => 'Il campo indices è obbligatorio e deve essere un array non vuoto.',
            ],

            'channel' => [
                'not-found' => 'Canale non trovato.',

                'created' => 'Canale creato con successo.',
                'updated' => 'Canale aggiornato con successo.',
                'deleted' => 'Canale eliminato con successo.',
                'code-unique' => 'Il codice è già in uso.',
                'hostname-unique' => 'Il nome host è già in uso.',
                'default-locale-mismatch' => 'default_locale_id deve comparire nell\'elenco delle lingue.',
                'base-currency-mismatch' => 'base_currency_id deve comparire nell\'elenco delle valute.',
                'unknown-locale' => 'Uno o più id di lingua non esistono.',
                'unknown-currency' => 'Uno o più id di valuta non esistono.',
                'unknown-inventory-source' => 'Uno o più id di inventory_source non esistono.',
                'unknown-root-category' => 'Il root_category_id non esiste.',
                'cannot-delete-last' => 'È richiesto almeno un canale — impossibile eliminare l\'ultimo canale rimasto.',
                'cannot-delete-default' => 'Impossibile eliminare il canale predefinito dell\'applicazione.',
                'delete-failed' => 'Impossibile eliminare il canale.',
                'no-permission' => 'Non hai l\'autorizzazione per gestire i canali.',
            ],

            'user' => [
                'not-found' => 'Utente amministratore non trovato.',
                'created' => 'Utente amministratore creato con successo.',
                'updated' => 'Utente amministratore aggiornato con successo.',
                'deleted' => 'Utente amministratore eliminato con successo.',
                'name-required' => 'Il campo name è obbligatorio.',
                'email-required' => 'Il campo email è obbligatorio.',
                'email-invalid' => 'L\'email deve essere un indirizzo email valido.',
                'email-unique' => 'L\'email è già in uso.',
                'password-required' => 'Il campo password è obbligatorio.',
                'password-min' => 'La password deve contenere almeno 6 caratteri.',
                'role-required' => 'Il campo role_id è obbligatorio.',
                'role-not-found' => 'Il ruolo selezionato non esiste.',
                'status-invalid' => 'Lo stato deve essere 0 o 1.',
                'cannot-delete-self' => 'Non puoi eliminare il tuo account amministratore.',
                'cannot-delete-last-admin' => 'È richiesto almeno un utente amministratore — impossibile eliminare l\'ultimo amministratore rimasto.',
                'cannot-change' => 'Il campo :name dell\'ultimo amministratore con accesso completo non può essere modificato.',
                'delete-failed' => 'Impossibile eliminare l\'utente amministratore.',
                'no-permission' => 'Non hai il permesso di gestire gli utenti amministratori.',
                'self-delete-password-required' => 'Il campo password è obbligatorio per eliminare il tuo account.',
                'self-delete-incorrect-password' => 'La password non è corretta.',
                'self-deleted' => 'Il tuo account amministratore è stato eliminato.',
            ],

            'data-transfer' => [
                'import' => [
                    'not-found' => 'Importazione non trovata.',
                    'deleted' => 'Importazione eliminata con successo.',
                    'cancelled' => 'Importazione annullata con successo.',
                    'cannot-cancel' => 'Solo le importazioni in sospeso o in elaborazione possono essere annullate. Stato attuale: :state.',
                    'no-permission' => 'Non hai l\'autorizzazione per gestire le importazioni di trasferimento dati.',
                    'delete-failed' => 'Impossibile eliminare l\'importazione.',
                    'created' => 'Importazione creata con successo.',
                    'updated' => 'Importazione aggiornata con successo.',
                    'type-invalid' => 'Il tipo selezionato non è valido.',
                    'action-invalid' => 'L\'azione deve essere una tra: append, delete.',
                    'validation-strategy-invalid' => 'La strategia di convalida deve essere una tra: stop-on-errors, skip-errors.',
                    'allowed-errors-invalid' => 'Gli errori consentiti devono essere un numero intero pari o superiore a 0.',
                    'field-separator-required' => 'Il separatore di campo è obbligatorio.',
                    'file-required' => 'È richiesto un file.',
                    'file-invalid-type' => 'Il file deve essere uno tra: csv, xls, xlsx, xml.',
                    'nothing-to-import' => 'Non c\'è nulla da importare.',
                    'not-valid' => 'L\'importazione non è valida.',
                    'setup-queue-error' => 'La connessione della coda è impostata su sync; configura una coda reale per elaborare le importazioni in background.',
                    'file-not-found' => 'Nessun file disponibile per il download.',
                    'error-report-not-found' => 'Nessun rapporto degli errori disponibile per il download.',
                    'sample-not-found' => 'Nessun file di esempio disponibile per il tipo e il formato richiesti.',
                    'sample-format-invalid' => 'Il formato di esempio deve essere uno tra: csv, xls, xlsx, xml.',
                    'images-archive-too-large' => 'L\'archivio delle immagini può contenere al massimo :entries file e occupare al massimo :size MB una volta estratto.',
                ],
            ],
        ],

        'cms' => [
            'page' => [
                'not-found' => 'Pagina CMS non trovata.',

                'created' => 'Pagina CMS creata con successo.',
                'url-key-required' => 'Il campo url_key è obbligatorio.',
                'url-key-unique' => 'La url_key è già in uso.',
                'url-key-invalid' => 'Il formato della url_key non è valido. Usa solo lettere minuscole, numeri e trattini.',
                'page-title-required' => 'Il campo page_title è obbligatorio.',
                'html-content-required' => 'Il campo html_content è obbligatorio.',
                'channels-required' => 'È necessario selezionare almeno un canale.',
                'channels-invalid' => 'Uno o più canali non sono validi.',

                'updated' => 'Pagina CMS aggiornata con successo.',

                'deleted' => 'Pagina CMS eliminata con successo.',
                'delete-failed' => 'Impossibile eliminare la pagina CMS.',

                'mass-deleted' => 'Pagine CMS eliminate con successo.',
                'indices-required' => 'Il campo indices è obbligatorio e deve essere un array non vuoto.',
                'indices-invalid' => 'Il campo indices deve essere un array di ID di pagine CMS.',

                'no-permission' => 'Non hai l\'autorizzazione per gestire le pagine CMS.',
            ],
        ],

        'marketing' => [
            'catalog-rule' => [
                'not-found' => 'Regola del catalogo non trovata.',
                'created' => 'Regola del catalogo creata con successo.',
                'updated' => 'Regola del catalogo aggiornata con successo.',
                'deleted' => 'Regola del catalogo eliminata con successo.',
                'delete-failed' => 'Impossibile eliminare la regola del catalogo.',
                'mass-delete-indices-required' => 'Nessun ID di regola del catalogo fornito per l\'eliminazione di massa.',
                'mass-delete-success' => 'Regole del catalogo selezionate eliminate con successo.',
                'no-permission' => 'Non hai l\'autorizzazione per gestire le regole del catalogo.',
            ],

            'campaign' => [
                'not-found' => 'Campagna non trovata.',
                'created' => 'Campagna creata con successo.',
                'updated' => 'Campagna aggiornata con successo.',
                'deleted' => 'Campagna eliminata con successo.',
                'delete-failed' => 'Impossibile eliminare la campagna.',
                'no-permission' => 'Non hai l\'autorizzazione per gestire le campagne.',

                'send' => [
                    'id-required' => 'L\'id della campagna è obbligatorio.',
                    'inactive' => 'Impossibile inviare una campagna non attiva. Attivala prima.',
                    'queued' => 'Campagna in coda per :count destinatario/i.',
                ],
            ],

            'sitemap' => [
                'not-found' => 'Sitemap non trovata.',
                'created' => 'Sitemap creata con successo.',
                'updated' => 'Sitemap aggiornata con successo.',
                'deleted' => 'Sitemap eliminata con successo.',
                'delete-failed' => 'Impossibile eliminare la sitemap.',
                'no-permission' => 'Non hai l\'autorizzazione per gestire le sitemap.',

                'generate' => [
                    'id-required' => 'L\'id della sitemap è obbligatorio.',
                    'no-channels' => 'Questa sitemap non copre alcun canale, quindi non c\'è nulla da generare. Assegnale prima almeno un canale.',
                    'success' => 'Sitemap rigenerata con successo.',
                    'failed' => 'Generazione della sitemap non riuscita: :message',
                ],
            ],

            'cart-rule' => [
                'not-found' => 'Regola del carrello non trovata.',
                'id-required' => 'È richiesto un id di regola del carrello.',
                'copy-of' => 'Copia di :value',
                'copied' => 'Regola del carrello copiata con successo.',
                'created' => 'Regola del carrello creata con successo.',
                'updated' => 'Regola del carrello aggiornata con successo.',
                'deleted' => 'Regola del carrello eliminata con successo.',
                'delete-failed' => 'Impossibile eliminare la regola del carrello.',
                'no-permission' => 'Non hai l\'autorizzazione per gestire le regole del carrello.',
                'mass-delete-success' => 'Regole del carrello eliminate con successo.',
                'mass-delete-indices-required' => 'Il campo indices è obbligatorio e deve essere un array non vuoto.',
                'action-type-invalid' => 'action_type deve essere uno tra: by_percent, by_fixed, cart_fixed, buy_x_get_y.',
                'coupon-code-required' => 'È richiesto un coupon_code quando coupon_type=1 e use_auto_generation=0.',
                'coupon-code-unique' => 'Questo codice coupon è già in uso.',
            ],

            'template' => [
                'campaign-associated' => 'Questo modello email è utilizzato da una campagna, quindi non può essere eliminato. Rimuovilo prima dalla campagna.',
                'not-found' => 'Modello email non trovato.',
                'created' => 'Modello email creato con successo.',
                'updated' => 'Modello email aggiornato con successo.',
                'deleted' => 'Modello email eliminato con successo.',
                'delete-failed' => 'Impossibile eliminare il modello email.',
                'status-invalid' => 'Lo stato deve essere uno tra: active, inactive, draft.',
                'no-permission' => 'Non hai l\'autorizzazione per gestire i modelli email.',
            ],

            'event' => [
                'campaign-associated' => 'Questo evento è utilizzato da una campagna, quindi non può essere eliminato. Rimuovilo prima dalla campagna.',
                'not-found' => 'Evento di marketing non trovato.',
                'created' => 'Evento di marketing creato con successo.',
                'updated' => 'Evento di marketing aggiornato con successo.',
                'deleted' => 'Evento di marketing eliminato con successo.',
                'delete-failed' => 'Impossibile eliminare l\'evento di marketing.',
                'no-permission' => 'Non hai l\'autorizzazione per gestire gli eventi di marketing.',
            ],

            'search-synonym' => [
                'not-found' => 'Sinonimo di ricerca non trovato.',
                'created' => 'Sinonimo di ricerca creato con successo.',
                'updated' => 'Sinonimo di ricerca aggiornato con successo.',
                'deleted' => 'Sinonimo di ricerca eliminato con successo.',
                'mass-deleted' => 'Sinonimi di ricerca eliminati con successo.',
                'delete-failed' => 'Impossibile eliminare il sinonimo di ricerca.',
                'indices-required' => 'Il campo indices è obbligatorio e deve essere un array non vuoto di ID di sinonimi di ricerca.',
                'no-permission' => 'Non hai l\'autorizzazione per gestire i sinonimi di ricerca.',
            ],

            'url-rewrite' => [
                'not-found' => 'Riscrittura URL non trovata.',
                'created' => 'Riscrittura URL creata con successo.',
                'updated' => 'Riscrittura URL aggiornata con successo.',
                'deleted' => 'Riscrittura URL eliminata con successo.',
                'delete-failed' => 'Impossibile eliminare la riscrittura URL.',
                'mass-deleted' => 'Riscritture URL eliminate con successo.',
                'indices-required' => 'È richiesto almeno un id di riscrittura URL.',
                'no-permission' => 'Non hai l\'autorizzazione per gestire le riscritture URL.',
            ],

            'cart-rule-coupon' => [
                'cart-rule-not-found' => 'Regola del carrello non trovata.',
                'not-found' => 'Coupon non trovato.',
                'code-taken' => 'Esiste già un coupon con questo codice.',
                'format-invalid' => 'Il formato deve essere uno tra: alphabetic, alphanumeric, numeric.',
                'indices-required' => 'Il campo indices è obbligatorio e deve essere un array non vuoto di ID di coupon.',
                'created' => 'Coupon creato con successo.',
                'generated' => 'Generati :count coupon con successo.',
                'deleted' => 'Coupon eliminato con successo.',
                'mass-deleted' => 'Eliminati :count coupon con successo.',
                'no-permission' => 'Non hai l\'autorizzazione per gestire i coupon delle regole del carrello.',
            ],

            'subscriber' => [
                'not-found' => 'Iscritto alla newsletter non trovato.',
                'updated' => 'Iscrizione aggiornata con successo.',
                'deleted' => 'Iscrizione eliminata con successo.',
                'is-subscribed-required' => 'Il campo is_subscribed è obbligatorio.',
                'no-permission' => 'Non hai l\'autorizzazione per gestire gli iscritti alla newsletter.',
            ],

            'search-term' => [
                'not-found' => 'Termine di ricerca non trovato.',
                'updated' => 'Termine di ricerca aggiornato con successo.',
                'deleted' => 'Termine di ricerca eliminato con successo.',
                'mass-deleted' => 'Termini di ricerca eliminati con successo.',
                'term-required' => 'Il campo term è obbligatorio.',
                'redirect-url-invalid' => 'redirect_url deve essere un URL valido.',
                'indices-required' => 'È necessario fornire almeno un ID in indices.',
                'no-permission' => 'Non hai l\'autorizzazione per gestire i termini di ricerca.',
            ],
        ],
        'customer-api-sessions' => [
            'menu' => [
                'title' => 'Sessioni API',
            ],

            'acl' => [
                'title' => 'Sessioni API',
                'delete' => 'Revoca',
            ],

            'index' => [
                'title' => 'Sessioni API dei clienti',
                'info' => 'Clienti connessi all\'API del negozio da app web, mobili, POS o desktop. Revocare una sessione disconnette subito quel dispositivo.',
            ],

            'fields' => [
                'customer' => 'Cliente',
                'email' => 'Email',
                'channel' => 'Canale',
            ],

            'datagrid' => [
                'revoke-all' => 'Revoca tutte le sessioni del cliente',
            ],

            'card' => [
                'title' => 'Sessioni API (:count)',
                'empty' => 'Questo cliente non ha sessioni API attive.',
            ],

            'errors' => [
                'no-permission' => 'Non hai l\'autorizzazione per gestire le sessioni API dei clienti.',
            ],
        ],

        'customer-auth' => [
            'configuration' => [
                'title' => 'Autenticazione dei clienti',
                'info' => 'Durata dei token, limiti e regole di sessione per gli accessi dei clienti del negozio.',

                'tokens' => [
                    'title' => 'Durata dei token',
                    'info' => 'Per quanto tempo restano validi i token di accesso, i token di aggiornamento e le sessioni. Le modifiche si applicano ai token emessi in seguito.',
                    'access-token-ttl' => 'Durata del token di accesso (minuti)',
                    'access-token-ttl-info' => 'Tra 5 e 60 minuti. Predefinito 15.',
                    'refresh-token-ttl' => 'Durata del token di aggiornamento (giorni)',
                    'refresh-token-ttl-info' => 'Tra 1 e 90 giorni. Predefinito 30.',
                    'session-lifetime' => 'Durata massima della sessione (giorni)',
                    'session-lifetime-info' => 'Tra 1 e 365 giorni. Predefinito 90. Dopo, l\'amministratore deve accedere di nuovo, indipendentemente da quante volte la sessione è stata aggiornata.',
                    'max-sessions' => 'Sessioni attive massime per cliente',
                    'max-sessions-info' => 'Raggiunto il limite, la sessione più vecchia viene chiusa. Tra 0 e 100. 0 significa nessun limite.',
                ],

                'rate-limits' => [
                    'title' => 'Limiti di richieste',
                    'info' => 'Richieste massime all\'API del negozio al minuto per ogni cliente connesso.',
                    'requests-per-minute' => 'Richieste al minuto per cliente connesso',
                    'requests-per-minute-info' => 'Tra 1 e 100000. Predefinito 120.',
                ],

                'login-protection' => [
                    'title' => 'Protezione dell\'accesso',
                    'info' => 'Quanti accessi non riusciti e rinnovi del token sono consentiti prima che il client debba attendere.',
                    'login-max-attempts' => 'Accessi non riusciti prima del blocco',
                    'login-max-attempts-info' => 'Per email e indirizzo IP. Tra 3 e 20. Predefinito 5.',
                    'login-lockout-seconds' => 'Blocco dell\'accesso (secondi)',
                    'login-lockout-seconds-info' => 'Tra 60 e 3600. Predefinito 60.',
                    'refresh-requests-per-minute' => 'Rinnovi del token al minuto per sessione',
                    'refresh-requests-per-minute-info' => 'Tra 1 e 60. Predefinito 10.',
                ],

                'security' => [
                    'title' => 'Sicurezza',
                    'info' => 'Cosa chiude le altre sessioni di un cliente.',
                    'revoke-sessions-on-password-change' => 'Revoca le sessioni al cambio password',
                ],

                'retention' => [
                    'title' => 'Conservazione dei dati',
                    'info' => 'Per quanto tempo vengono conservate le sessioni terminate dei clienti.',
                    'session-prune-days' => 'Conserva le sessioni terminate (giorni)',
                    'session-prune-days-info' => 'Le sessioni revocate o scadute vengono eliminate dopo questo numero di giorni. Tra 1 e 365. Predefinito 30.',
                ],
            ],
        ],
        'auth' => [
            'configuration' => [
                'title' => 'Autenticazione amministratore',
                'info' => 'Durata dei token, limiti di richieste e regole di sicurezza per gli accessi all\'API di amministrazione e i token API.',

                'tokens' => [
                    'title' => 'Durata dei token',
                    'info' => 'Per quanto tempo restano validi i token di accesso, i token di aggiornamento e le sessioni. Le modifiche si applicano ai token emessi in seguito.',
                    'access-token-ttl' => 'Durata del token di accesso (minuti)',
                    'access-token-ttl-info' => 'Tra 5 e 60 minuti. Predefinito 15.',
                    'refresh-token-ttl' => 'Durata del token di aggiornamento (giorni)',
                    'refresh-token-ttl-info' => 'Tra 1 e 90 giorni. Predefinito 30.',
                    'session-lifetime' => 'Durata massima della sessione (giorni)',
                    'session-lifetime-info' => 'Tra 1 e 365 giorni. Predefinito 90. Dopo, l\'amministratore deve accedere di nuovo, indipendentemente da quante volte la sessione è stata aggiornata.',
                ],

                'rate-limits' => [
                    'title' => 'Limiti di richieste',
                    'info' => 'Numero massimo di richieste all\'API di amministrazione al minuto.',
                    'session-requests-per-minute' => 'Richieste al minuto per amministratore connesso',
                    'api-token-requests-per-minute' => 'Richieste al minuto per token API',
                    'session-requests-per-minute-info' => 'Tra 1 e 100000. Predefinito 60.',
                    'api-token-requests-per-minute-info' => 'Tra 1 e 100000. Predefinito 60.',
                    'unauthenticated-requests-per-minute' => 'Richieste al minuto senza token, per indirizzo IP',
                    'unauthenticated-requests-per-minute-info' => 'Tra 10 e 1000. Predefinito 60. Comprende le richieste di accesso, doppio fattore, rinnovo e password.',
                ],

                'security' => [
                    'title' => 'Sicurezza',
                    'info' => 'Regole di revoca, scadenza dei token API e associazione al dominio.',
                    'revoke-sessions-on-password-change' => 'Revoca le sessioni al cambio password',
                    'revoke-api-tokens-on-password-change' => 'Revoca i token API al cambio password',
                    'allow-non-expiring-api-tokens' => 'Consenti token API senza scadenza',
                    'domain-binding-mode' => 'Modalità di associazione al dominio',
                    'domain-binding-mode-info' => 'DNS verifica che la richiesta provenga da un indirizzo IP a cui punta il dominio del token. Origin verifica l\'intestazione Origin o Referer ed è adatto solo ai browser.',
                    'domain-binding-dns' => 'DNS (indirizzo IP del client)',
                    'domain-binding-origin' => 'Intestazione Origin',
                ],

                'login-protection' => [
                    'title' => 'Protezione dell\'accesso',
                    'info' => 'Quanti accessi non riusciti, codici a doppio fattore errati e richieste di password sono consentiti prima che il client debba attendere.',
                    'login-max-attempts' => 'Accessi non riusciti prima del blocco',
                    'login-max-attempts-info' => 'Per email e indirizzo IP. Tra 3 e 20. Predefinito 5.',
                    'login-lockout-seconds' => 'Blocco dell\'accesso (secondi)',
                    'login-lockout-seconds-info' => 'Tra 60 e 3600. Predefinito 60.',
                    'two-factor-challenge-lifetime' => 'Durata della verifica a doppio fattore (minuti)',
                    'two-factor-challenge-lifetime-info' => 'Tra 1 e 15. Predefinito 5.',
                    'two-factor-max-failures' => 'Codici a doppio fattore errati al minuto',
                    'two-factor-max-failures-info' => 'Per amministratore, su tutte le verifiche. Tra 3 e 20. Predefinito 5.',
                    'password-requests-per-minute' => 'Richieste di password al minuto',
                    'password-requests-per-minute-info' => 'Richieste di recupero e reimpostazione della password per indirizzo IP. Tra 1 e 20. Predefinito 5.',
                ],

                'retention' => [
                    'title' => 'Conservazione dei dati',
                    'info' => 'Per quanto tempo vengono conservate le sessioni terminate e la cronologia di audit dell\'API.',
                    'session-prune-days' => 'Conserva le sessioni terminate (giorni)',
                    'session-prune-days-info' => 'Le sessioni revocate o scadute vengono eliminate dopo questo numero di giorni. Tra 1 e 365. Predefinito 30.',
                    'audit-retention-days' => 'Conserva la cronologia di audit dell\'API (giorni)',
                    'audit-retention-days-info' => 'Le voci più vecchie vengono eliminate ogni giorno. Tra 0 e 3650. Vuoto o 0 le conserva per sempre.',
                ],
            ],

            'errors' => [
                'unauthenticated' => 'Non sei autenticato.',
                'access-token-expired' => 'Il token di accesso è scaduto. Aggiornalo per continuare.',
                'api-token-expired' => 'Il token API è scaduto.',
                'refresh-token-invalid' => 'Il token di aggiornamento non è valido o è scaduto. Accedi di nuovo.',
                'api-token-revoked' => 'Il token API è stato revocato.',
                'api-token-domain-mismatch' => 'Il token API non può essere usato da questo dominio.',
                'access-token-required' => 'Questa azione richiede il token di accesso di una sessione di login; i token API non possono usarla.',
                'two-factor-required' => 'Inserisci il codice a 6 cifre dell\'app di autenticazione, o uno dei tuoi codici di backup, per completare l\'accesso.',
                'too-many-login-attempts' => 'Troppi tentativi di accesso. Riprova tra :seconds secondi.',
                'two-factor-challenge-invalid' => 'La verifica a due fattori non è valida o è scaduta. Accedi di nuovo.',
                'two-factor-attempts-exceeded' => 'Troppi codici errati. Accedi di nuovo.',
                'password-reset-token-invalid' => 'Questo token di reimpostazione della password non è valido o è scaduto.',
                'too-many-password-requests' => 'Troppe richieste di password. Riprova tra :seconds secondi.',
                'rate-limit-exceeded' => 'Troppi tentativi.',
                'no-permission' => 'Non hai il permesso di gestire le sessioni API degli amministratori.',
            ],

            'sessions' => [
                'logged-out' => 'Sei stato disconnesso.',
                'logged-out-all' => 'Sei stato disconnesso da tutte le sessioni.',
                'not-found' => 'Sessione non trovata.',
                'revoked' => 'La sessione è stata revocata.',
                'revoked-for-admin' => 'Sessioni revocate: :count.',
            ],

            'password' => [
                'reset' => 'La tua password è stata reimpostata. Accedi di nuovo.',
            ],
        ],

        'api-tokens' => [
            'menu' => [
                'title' => 'Token API',
            ],

            'acl' => [
                'title' => 'Token API',
                'create' => 'Crea',
                'delete' => 'Revoca',
                'manage-others' => 'Gestisci i token degli altri amministratori',
            ],

            'index' => [
                'title' => 'Token API',
                'info' => 'Le integrazioni server, come un ERP, un PIM, un magazzino o un sistema contabile, si autenticano con token API. Ogni token agisce come un amministratore con i permessi del suo ruolo e funziona solo dal dominio a cui è associato.',
                'create-btn' => 'Crea token API',
            ],

            'create' => [
                'title' => 'Crea token API',
                'back-btn' => 'Indietro',
                'save-btn' => 'Crea token',
                'general' => 'Generale',
                'expiry' => 'Scadenza',
                'admin-info' => 'Il token agisce come questo amministratore e ha esattamente i permessi del suo ruolo.',
                'domain-info' => 'L\'host da cui chiama l\'integrazione, ad esempio erp.example.com. Un URL viene ridotto al suo host. Indirizzi IP e caratteri jolly non sono accettati e il dominio non può essere modificato in seguito.',
                'expires-on-date' => 'Scade in una data',
                'never-expires' => 'Non scade mai',
                'no-expiry-warning' => 'Un token senza scadenza funziona finché non viene revocato. Conservalo in un archivio di segreti e revocalo appena non serve più.',
                'no-expiry-confirm' => 'Ho capito che questo token non scade mai.',
                'no-admins' => 'Non c\'è alcun amministratore per cui puoi creare un token API.',
            ],

            'view' => [
                'title' => 'Token API',
                'back-btn' => 'Indietro',
                'save-btn' => 'Salva',
                'revoke-btn' => 'Revoca',
                'details' => 'Dettagli',
                'usage' => 'Utilizzo',
                'token-once-title' => 'Copia ora il tuo token API',
                'token-once-info' => 'È l\'unica volta in cui il token viene mostrato. Conservalo in un archivio di segreti; se viene perso, revocalo e creane uno nuovo.',
                'copy-btn' => 'Copia',
                'copied' => 'Il token è stato copiato.',
                'usage-info' => 'Invialo come token Bearer nell\'intestazione Authorization delle richieste REST e GraphQL.',
                'not-yet' => 'Non ancora',
                'revoke-confirm' => 'Revocare questo token API? L\'integrazione che lo usa smette subito di funzionare.',
            ],

            'fields' => [
                'id' => 'ID',
                'name' => 'Nome',
                'admin' => 'Amministratore',
                'token' => 'Token',
                'domain' => 'Dominio',
                'expiry' => 'Scadenza',
                'expires-at' => 'Scade il',
                'no-expiry-confirmation' => 'Conferma senza scadenza',
                'status' => 'Stato',
                'created-by' => 'Creato da',
                'created-at' => 'Creato il',
                'last-used-at' => 'Ultimo utilizzo il',
                'last-ip-address' => 'Ultimo indirizzo IP',
                'last-user-agent' => 'Ultimo user agent',
                'last-channel' => 'Ultimo canale',
                'resolved-ips' => 'Indirizzi IP risolti',
                'resolved-at' => 'Risolto il',
                'domain-mismatches' => 'Richieste da altri domini',
                'last-domain-mismatch-at' => 'Ultima richiesta da un altro dominio',
                'revoked-at' => 'Revocato il',
                'revoked-by' => 'Revocato da',
            ],

            'datagrid' => [
                'never' => 'Mai',
                'console' => 'Comando da console',
                'view' => 'Visualizza',
                'revoke' => 'Revoca',
            ],

            'status' => [
                'active' => 'Attivo',
                'expired' => 'Scaduto',
                'revoked' => 'Revocato',
            ],

            'channels' => [
                'rest' => 'REST',
                'graphql' => 'GraphQL',
            ],

            'messages' => [
                'created' => 'Il token API è stato creato.',
                'updated' => 'Il token API è stato aggiornato.',
                'revoked' => 'Il token API è stato revocato.',
            ],

            'errors' => [
                'no-permission' => 'Non hai il permesso di gestire i token API.',
                'not-found' => 'Token API non trovato.',
                'cannot-create-for-admin' => 'Non puoi creare un token API per questo amministratore: il suo ruolo ha permessi che il tuo non ha, oppure non puoi gestire i token degli altri amministratori.',
                'cannot-manage' => 'Non puoi gestire questo token API.',
                'already-revoked' => 'Il token API è già revocato.',
                'invalid-name' => 'Il nome è obbligatorio e può avere al massimo 100 caratteri.',
                'invalid-domain' => 'Inserisci un nome di dominio come erp.example.com. Indirizzi IP e caratteri jolly non sono accettati.',
                'expiry-required' => 'I token API devono avere una data di scadenza.',
                'expiry-in-past' => 'La data di scadenza deve essere nel futuro.',
            ],
        ],

        'api-sessions' => [
            'menu' => [
                'title' => 'Sessioni API',
            ],

            'acl' => [
                'title' => 'Sessioni API',
                'delete' => 'Revoca',
            ],

            'index' => [
                'title' => 'Sessioni API',
                'info' => 'Amministratori collegati all\'API di amministrazione da app web, mobili, POS o desktop. Revocare una sessione disconnette subito quel dispositivo.',
            ],

            'fields' => [
                'id' => 'ID',
                'admin' => 'Amministratore',
                'device' => 'Dispositivo',
                'client-type' => 'Tipo di client',
                'ip-address' => 'IP di accesso',
                'user-agent' => 'User agent di accesso',
                'last-ip-address' => 'Ultimo indirizzo IP',
                'last-user-agent' => 'Ultimo user agent',
                'last-used-at' => 'Ultimo utilizzo il',
                'last-channel' => 'Ultimo canale',
                'refresh-expires-at' => 'Refresh scade il',
                'expires-at' => 'Scade il',
                'status' => 'Stato',
                'created-at' => 'Accesso il',
            ],

            'datagrid' => [
                'revoke' => 'Revoca',
                'revoke-all' => 'Revoca tutte le sessioni di questo amministratore',
            ],

            'client-types' => [
                'web' => 'Web',
                'mobile' => 'Mobile',
                'pos' => 'POS',
                'desktop' => 'Desktop',
                'other' => 'Altro',
            ],

            'errors' => [
                'not-active' => 'Questa sessione è già revocata o scaduta.',
            ],
        ],

        'api-audit' => [
            'menu' => [
                'title' => 'Audit API',
            ],

            'acl' => [
                'title' => 'Cronologia modifiche API',
                'delete' => 'Elimina cronologia',
            ],

            'index' => [
                'title' => 'Cronologia modifiche API',
                'info' => 'Ogni creazione, aggiornamento ed eliminazione effettuata tramite l\'API di amministrazione, con chi l\'ha fatto, quale token e cosa è cambiato.',
                'cleanup-btn' => 'Elimina i registri più vecchi',
                'cleanup-days' => 'Elimina i log più vecchi di questo numero di giorni',
                'cleanup-confirm' => 'Eliminare tutta la cronologia precedente al numero di giorni specificato? Questa operazione non può essere annullata.',
            ],

            'view' => [
                'title' => 'Cambiare',
                'back-btn' => 'Indietro',
                'admin' => 'Ammin',
                'token' => 'Gettone',
                'action' => 'Azione',
                'resource' => 'Risorsa',
                'method' => 'Metodo',
                'ip' => 'Indirizzo IP',
                'date' => 'Data',
                'version' => 'Versione',
                'url' => 'Punto finale',
                'request-details' => 'Richiedi dettagli',
                'changes' => 'Cambiamenti',
                'field' => 'Campo',
                'old' => 'Vecchio valore',
                'new' => 'Nuovo valore',
                'no-field-changes' => 'Per questa voce non sono state registrate modifiche a livello di campo.',
                'same-request' => 'Altre modifiche nella stessa richiesta',
                'version-chain' => 'Cronologia delle versioni di questo record',
            ],

            'datagrid' => [
                'id' => 'ID',
                'date' => 'Data',
                'admin' => 'Ammin',
                'token' => 'Gettone',
                'token-kind' => 'Tipo di token',
                'channel' => 'Canale',
                'operation' => 'Operazione',
                'resource' => 'Risorsa',
                'method' => 'Metodo',
                'ip' => 'IP',
                'view' => 'Visualizza',
                'delete' => 'Elimina',
            ],

            'events' => [
                'created' => 'Creato',
                'updated' => 'Aggiornato',
                'deleted' => 'Eliminato',
            ],

            'token-kinds' => [
                'session' => 'Sessione di accesso',
                'api-token' => 'Token API',
                'integration' => 'Token di integrazione (rimosso)',
                'customer-session' => 'Sessione di accesso del cliente',
            ],

            'channels' => [
                'rest' => 'REST',
                'graphql' => 'GraphQL',
                'panel' => 'Pannello di amministrazione',
                'cli' => 'Comando da console',
            ],

            'deleted' => ':count record della cronologia eliminati.',
            'cleanup-input-required' => 'Fornire un numero di giorni o una data per la pulizia.',
        ],

        'api-configuration' => [
            'title' => 'API',
            'info' => 'Impostazioni per l\'API Bagisto e i suoi moduli di amministrazione.',
        ],
    ],
];
