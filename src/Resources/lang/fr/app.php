<?php

return [
    'graphql' => [
        'cart' => [
            'authentication-required' => 'Le jeton d\'authentification est requis',
            'invalid-token' => 'Jeton d\'authentification invalide ou expiré',
            'unauthorized-access' => 'Accès non autorisé au panier',
            'authenticated-only' => 'Seuls les utilisateurs authentifiés peuvent récupérer leur panier',
            'merge-requires-auth' => 'La fusion du panier invité nécessite une authentification',
            'unknown-operation' => 'Opération de panier inconnue',

            'cart-not-found' => 'Panier introuvable',
            'guest-cart-not-found' => 'Panier invité introuvable',
            'product-not-found' => 'Produit introuvable',

            'product-id-quantity-required' => 'L\'ID du produit et la quantité sont requis',
            'cart-item-id-quantity-required' => 'L\'ID de l\'article du panier et la quantité sont requis',
            'cart-item-id-required' => 'L\'ID de l\'article du panier est requis',
            'item-ids-required' => 'Le tableau des ID d\'articles est requis',
            'coupon-code-required' => 'Le code de coupon est requis',
            'address-data-required' => 'Le pays, la région et le code postal sont requis',

            'add-product-failed' => 'Échec de l\'ajout du produit au panier',
            'update-item-failed' => 'Échec de la mise à jour de l\'article du panier',
            'remove-item-failed' => 'Échec de la suppression de l\'article du panier',
            'apply-coupon-failed' => 'Échec de l\'application du coupon',
            'remove-coupon-failed' => 'Échec de la suppression du coupon',
            'move-to-wishlist-failed' => 'Échec du déplacement de l\'article vers la liste de souhaits',
            'estimate-shipping-failed' => 'Échec de l\'estimation des frais de livraison',

            'product-added-successfully' => 'Produit ajouté au panier avec succès',
            'guest-cart-merged' => 'Panier invité fusionné avec succès',
            'using-authenticated-cart' => 'Utilisation du panier du client authentifié',
            'cart-item-not-found' => 'Article du panier introuvable',
            'new-guest-cart-created' => 'Nouveau panier invité créé avec un jeton de session unique',
            'select-items-to-remove' => 'Veuillez sélectionner les articles à supprimer',
            'select-items-to-move-wishlist' => 'Veuillez sélectionner les articles à déplacer vers la liste de souhaits',
            'invalid-or-expired-token' => 'Le jeton du panier est invalide ou expiré. Veuillez créer un nouveau panier.',
            'invalid-token-of-login-user' => 'Le jeton de l\'utilisateur connecté est invalide.',
        ],

        'token-verification' => [
            'invalid-operation' => 'Opération invalide',
            'invalid-input-data' => 'Données d\'entrée invalides',
            'token-required' => 'Le jeton est requis',
            'invalid-token-format' => 'Format de jeton invalide',
            'token-not-found-or-expired' => 'Jeton introuvable ou expiré',
            'customer-not-found' => 'Client introuvable',
            'customer-account-suspended' => 'Le compte client est suspendu',
            'error-verifying-token' => 'Erreur lors de la vérification du jeton',
            'token-is-valid' => 'Le jeton est valide',
        ],

        'forgot-password' => [
            'invalid-operation' => 'Opération invalide',
            'invalid-input-data' => 'Données d\'entrée invalides',
            'email-required' => 'L\'adresse e-mail est requise',
            'reset-link-sent' => 'Lien de réinitialisation envoyé avec succès à votre adresse e-mail',
            'email-not-found' => 'Adresse e-mail introuvable',
            'error-sending-reset-link' => 'Une erreur s\'est produite lors de l\'envoi du lien de réinitialisation',
        ],

        'logout' => [
            'invalid-operation' => 'Opération invalide',
            'invalid-input-data' => 'Données d\'entrée invalides',
            'token-required' => 'Le jeton est requis',
            'invalid-token-format' => 'Format de jeton invalide',
            'logged-out-successfully' => 'Déconnexion réussie',
            'token-not-found-or-expired' => 'Jeton introuvable ou déjà expiré',
            'error-during-logout' => 'Erreur lors de la déconnexion',
        ],

        'address' => [
            'deleted-successfully' => 'Adresse supprimée avec succès',
            'authentication-required' => 'Le jeton d\'authentification est requis',
            'invalid-token' => 'Jeton invalide ou expiré',
            'unknown-operation' => 'Opération inconnue',
            'address-id-required' => 'L\'ID de l\'adresse est requis',
            'address-not-found' => 'Adresse introuvable ou n\'appartenant pas à ce client',
            'retrieved' => 'Adresses récupérées avec succès',
            'fetch-failed' => 'Échec de la récupération des adresses :',
        ],

        'customer-profile' => [
            'authentication-required' => 'Le jeton d\'authentification est requis. Veuillez fournir le jeton dans l\'entrée de la requête',
            'invalid-token' => 'Jeton invalide ou expiré',
        ],

        'customer' => [
            'password-mismatch' => 'Le mot de passe et sa confirmation ne correspondent pas',
            'confirm-password-required' => 'La confirmation du mot de passe est requise lors du changement de mot de passe',
            'unauthenticated' => 'Non authentifié. Veuillez vous connecter pour effectuer cette action',
        ],

        'product-review' => [
            'product-id-required' => 'L\'ID du produit est requis',
            'product-not-found' => 'Produit introuvable',
            'rating-invalid' => 'La note doit être comprise entre 1 et 5',
            'title-required' => 'Le titre de l\'avis est requis',
            'comment-required' => 'Le commentaire de l\'avis est requis',
        ],

        'product' => [
            'not-found-with-sku' => 'No product found with SKU',
            'not-found-with-url-key' => 'No product found with URL key',
            'parameters-required' => 'At least one of the following parameters must be provided: "sku", "id", "urlKey"',
        ],

        'auth' => [
            'no-token-provided' => 'Aucun jeton d\'authentification fourni. Veuillez fournir le jeton dans l\'en-tête Authorization sous la forme "Bearer <token>" ou dans le champ input.token',
            'invalid-or-expired-token' => 'Jeton invalide ou expiré',
            'request-not-found' => 'Requête introuvable dans le contexte',
            'token-required' => 'Authentication token is required. Please provide the token either in the GraphQL mutation input field or in the Authorization header as "Bearer <token>"',
            'unknown-resource' => 'Ressource inconnue',
            'cannot-update-other-profile' => 'Non autorisé : impossible de mettre à jour le profil d\'un autre client',
        ],

        'upload' => [
            'invalid-base64' => 'Données d\'image encodées en base64 invalides',
            'size-exceeds-limit' => 'La taille de l\'image ne doit pas dépasser 5 Mo',
            'invalid-format' => 'Format d\'image invalide. Veuillez fournir une image encodée en base64 avec le schéma d\'URI de données (data:image/jpeg;base64,...)',
            'failed' => 'Échec du téléchargement de l\'image',
        ],

        'attribute' => [
            'code-already-exists' => 'Le code d\'attribut existe déjà',
        ],

        'login' => [
            'invalid-credentials' => 'E-mail ou mot de passe invalide',
            'account-suspended' => 'Votre compte a été suspendu',
            'successful' => 'Vous vous êtes connecté avec succès',
            'invalid-request' => 'Demande de connexion invalide',
        ],

        'checkout' => [
            'invalid-input' => 'Données d\'entrée invalides pour l\'opération de paiement',
            'billing-address-required' => 'L\'adresse de facturation est requise',
            'shipping-address-required' => 'L\'adresse de livraison est requise pour les expéditions',
            'address-save-failed' => 'Échec de l\'enregistrement de l\'adresse',
            'address-saved' => 'Adresse enregistrée avec succès',
            'shipping-method-required' => 'La méthode de livraison est requise',
            'invalid-shipping-method' => 'Méthode de livraison invalide ou indisponible',
            'shipping-method-save-failed' => 'Échec de l\'enregistrement de la méthode de livraison',
            'shipping-method-saved' => 'Méthode de livraison enregistrée avec succès',
            'shipping-method-error' => 'Erreur lors de l\'enregistrement de la méthode de livraison',
            'payment-method-required' => 'La méthode de paiement est requise',
            'invalid-payment-method' => 'Méthode de paiement invalide ou indisponible',
            'payment-method-save-failed' => 'Échec de l\'enregistrement de la méthode de paiement',
            'payment-method-saved' => 'Méthode de paiement enregistrée avec succès',
            'payment-method-error' => 'Erreur lors de l\'enregistrement de la méthode de paiement',
            'order-creation-failed' => 'Échec de la création de la commande : l\'ID de commande est nul ou la commande n\'a pas été enregistrée',
            'order-retrieval-failed' => 'Échec de la récupération de la commande créée',
            'order-creation-error' => 'Échec de la création de la commande',
            'cart-empty' => 'Le panier est vide',
            'account-suspended' => 'Votre compte a été suspendu. Veuillez contacter le support.',
            'account-inactive' => 'Votre compte est inactif. Veuillez contacter le support.',
            'minimum-order-not-met' => 'Le montant minimum de commande est de :amount',
            'email-required' => 'L\'adresse e-mail est requise pour la création de la commande',
            'unknown-operation' => 'Opération de paiement inconnue',
        ],

        'customer-addresses' => [
            'token-required' => 'Le jeton est requis pour récupérer les adresses du client',
            'invalid-or-expired-token' => 'Jeton invalide ou expiré',
            'token-validation-failed' => 'Échec de la validation du jeton',
        ],

        'product' => [
            'type' => 'Type de produit',
            'attribute-family' => 'Famille d\'attributs',
            'sku' => 'SKU',
            'name' => 'Nom',
            'description' => 'Description',
            'short-description' => 'Description courte',
            'status' => 'Statut',
            'new' => 'Nouveau',
            'featured' => 'En vedette',
            'price' => 'Prix',
            'special-price' => 'Prix spécial',
            'weight' => 'Poids',
            'cost' => 'Coût',
            'length' => 'Longueur',
            'width' => 'Largeur',
            'height' => 'Hauteur',
            'color' => 'Couleur',
            'size' => 'Taille',
            'brand' => 'Marque',
            'super-attributes' => 'Super attributs',
        ],

        'compare-item' => [
            'id-required' => 'L\'ID de l\'article de comparaison est requis',
            'invalid-id-format' => 'Format d\'ID invalide. Format IRI attendu comme "/api/shop/compare-items/1" ou ID numérique',
            'not-found' => 'Article de comparaison non trouvé',
            'product-id-required' => 'L\'ID du produit est requis',
            'customer-id-required' => 'L\'ID du client est requis',
            'product-not-found' => 'Produit non trouvé',
            'customer-not-found' => 'Client non trouvé',
            'already-exists' => 'Ce produit se trouve déjà dans votre liste de comparaison',
        ],

        'downloadable-product' => [
            'download-link-not-found' => 'Lien de téléchargement introuvable ou expiré',
            'purchased-link-not-found' => 'Lien d\'achat introuvable',
            'file-not-found' => 'Fichier introuvable',
            'download-successful' => 'Fichier prêt pour téléchargement',
            'token-required' => 'Le jeton de téléchargement est requis',
            'invalid-token' => 'Jeton de téléchargement invalide ou expiré',
            'token-expired' => 'Le jeton de téléchargement a expiré. Veuillez générer un nouveau',
            'access-denied' => 'Accès refusé : Vous n\'avez pas la permission de télécharger ce fichier',
            'redirect-external-url' => 'Redirection vers l\'URL de téléchargement externe',
            'file-error' => 'Une erreur s\'est produite lors du traitement de votre demande de téléchargement',
            'unauthorized-access' => 'Accès non autorisé à la ressource de téléchargement',
        ],
    ],

    'integration' => [
        'menu' => [
            'title' => 'Intégration',
            'tokens' => 'Jetons',
        ],

        'history' => [
            'menu' => [
                'title' => 'Histoire',
            ],

            'acl' => [
                'title' => 'Historique des modifications de l\'API',
                'delete' => 'Supprimer l\'historique',
            ],

            'index' => [
                'title' => 'Historique des modifications de l\'API',
                'info' => 'Chaque création, mise à jour et suppression effectuée via l\'API d\'administration, avec qui l\'a fait, quel jeton et ce qui a changé.',
                'cleanup-btn' => 'Supprimer les anciens journaux',
                'cleanup-days' => 'Supprimer les journaux datant de plus de ce nombre de jours',
                'cleanup-confirm' => 'Supprimer tout l\'historique antérieur au nombre de jours indiqué ? Cela ne peut pas être annulé.',
            ],

            'view' => [
                'title' => 'Changement',
                'back-btn' => 'Retour',
                'admin' => 'Administrateur',
                'token' => 'Jeton',
                'action' => 'Action',
                'resource' => 'Ressource',
                'method' => 'Méthode',
                'ip' => 'Adresse IP',
                'date' => 'Date',
                'version' => 'Version',
                'url' => 'Point de terminaison',
                'request-details' => 'Détails de la demande',
                'changes' => 'Changements',
                'field' => 'Champ',
                'old' => 'Ancienne valeur',
                'new' => 'Nouvelle valeur',
                'no-field-changes' => 'Aucune modification au niveau du champ n’a été enregistrée pour cette entrée.',
                'same-request' => 'Autres changements dans la même demande',
                'version-chain' => 'Historique des versions de cet enregistrement',
            ],

            'datagrid' => [
                'id' => 'pièce d\'identité',
                'date' => 'Date',
                'admin' => 'Administrateur',
                'token' => 'Jeton',
                'action' => 'Action',
                'operation' => 'Fonctionnement',
                'resource' => 'Ressource',
                'version' => 'Version',
                'method' => 'Méthode',
                'ip' => 'PI',
                'view' => 'Voir',
                'delete' => 'Supprimer',
            ],

            'events' => [
                'created' => 'Créé',
                'updated' => 'Mis à jour',
                'deleted' => 'Supprimé',
            ],

            'deleted' => ':count enregistrement(s) d\'historique supprimé(s).',
            'cleanup-input-required' => 'Fournissez un certain nombre de jours ou une date pour nettoyer.',
        ],

        'acl' => [
            'title' => 'Intégration',
            'create' => 'Créer une intégration',
            'edit' => 'Modifier l\'intégration',
            'delete' => 'Révoquer le jeton d\'intégration',
            'generate' => 'Générer un jeton d\'intégration',
            'regenerate' => 'Régénérer le jeton d\'intégration',
        ],

        'index' => [
            'title' => 'Intégrations',
            'create-btn' => 'Créer une intégration',
        ],

        'create' => [
            'title' => 'Créer une intégration',
            'save-btn' => 'Enregistrer',
            'back-btn' => 'Retour',
        ],

        'edit' => [
            'title' => 'Modifier l\'intégration',
            'save-btn' => 'Enregistrer',
            'back-btn' => 'Retour',
            'generate-btn' => 'Générer un jeton',
            'regenerate-btn' => 'Régénérer le jeton',
            'revoke-btn' => 'Révoquer le jeton',
            'copy-btn' => 'Copier',
            'token-warning' => 'Enregistrez ce jeton maintenant – il ne sera plus affiché.',
            'token-label' => 'Jeton',
            'not-generated' => 'Pas encore généré',
            'masked' => '(Stocké - affiché une seule fois à la génération)',
            'history-banner' => 'Ce jeton n\'est plus actif.',
        ],

        'fields' => [
            'name' => 'Nom',
            'description' => 'Descriptif',
            'assign-user' => 'Attribuer un utilisateur',
            'permission-type' => 'Type d\'autorisation',
            'access-control' => 'Contrôle d\'accès',
            'general' => 'Général',
            'token-settings' => 'Paramètres des jetons',
            'valid-till' => 'Valable jusqu\'à',
            'rate-limit-per-minute' => 'Limite de débit (par minute)',
            'rate-limit-per-day' => 'Limite de tarif (par jour)',
            'never-expires' => 'N\'expire jamais',
            'expires-on' => 'Expire le',
            'unlimited' => 'Illimité',
            'limit-to' => 'Limiter à',
            'requests-per-minute' => 'requêtes / minute',
            'requests-per-day' => 'demandes / jour',
            'select-admin' => 'Sélectionnez un administrateur',
            'no-available-admins' => 'Aucun administrateur disponible : chaque administrateur dispose déjà d\'un jeton actif.',
            'same-as-web-hint' => 'Le jeton reflétera en direct les autorisations de rôle actuelles de l\'administrateur attribué.',
            'ip-allowlist' => 'Liste d\'adresses IP autorisées',
            'ip-any' => 'N\'importe quelle adresse IP (par défaut)',
            'ip-restricted' => 'Limité à des adresses IP spécifiques',
            'ip-list-hint' => 'Une entrée par ligne. Prend en charge IPv4, IPv6 et CIDR (par exemple 10.0.0.0/24 ou 2001:db8::/32). Laissez vide pour autoriser toutes les adresses IP.',
        ],

        'permission_type' => [
            'all' => 'Tout',
            'custom' => 'Personnalisé',
            'same_as_web' => 'Identique à l\'autorisation Web',
        ],

        'status' => [
            'draft' => 'Brouillon',
            'active' => 'Actif',
            'revoked' => 'Révoqué',
            'regenerated' => 'Régénéré',
        ],

        'datagrid' => [
            'id' => 'pièce d\'identité',
            'name' => 'Nom',
            'admin' => 'Administrateur',
            'token' => 'Jeton',
            'status' => 'Statut',
            'permission-type' => 'Type d\'autorisation',
            'expires-at' => 'Valable jusqu\'à',
            'last-used-at' => 'Dernière utilisation',
            'created-at' => 'Créé à',
            'edit' => 'Modifier',
            'revoke' => 'Révoquer',
        ],

        'messages' => [
            'draft-created' => 'Intégration créée. Générez le jeton pour commencer à l\'utiliser.',
            'updated' => 'Intégration mise à jour avec succès.',
            'generated' => 'Jeton généré. Copiez-le maintenant – il ne sera plus affiché.',
            'regenerated' => 'Jeton régénéré. Copiez le nouveau jeton maintenant : il ne sera plus affiché.',
            'revoked' => 'Jeton révoqué avec succès.',
            'generate-only-draft' => 'Seuls les brouillons d\'intégrations peuvent voir leur jeton généré.',
            'regenerate-only-active' => 'Seuls les jetons actifs peuvent être régénérés.',
            'cannot-edit-historic' => 'Les jetons révoqués ou régénérés ne peuvent pas être modifiés.',
            'already-inactive' => 'Ce jeton est déjà inactif.',
        ],

        'errors' => [
            'admin-has-token' => 'L\'administrateur sélectionné dispose déjà d\'un jeton d\'intégration actif.',
        ],

        'validation' => [
            'ip-invalid' => 'Chaque adresse IP autorisée doit être une adresse IPv4 ou IPv6 valide (notation CIDR prise en charge).',
            'cidr-prefix-invalid' => 'Le préfixe CIDR n\'est pas valide pour la version IP donnée.',
        ],

        'configuration' => [
            'api' => [
                'title' => 'API',
                'info' => 'Paramètres de l\'API Bagisto et de ses modules d\'administration.',
            ],
            'integration' => [
                'title' => 'Intégration',
                'info' => 'Gérez le plug-in d\'intégration d\'API utilisé pour émettre des jetons d\'API d\'administrateur.',
            ],
            'settings' => [
                'title' => 'Paramètres des modules',
                'info' => 'Activez ou désactivez le plug-in d\'intégration API. Lorsqu\'il est désactivé, son menu de barre latérale est masqué et ses pages renvoient 404.',
                'enable' => 'Activer le module d\'intégration API',
            ],
        ],

        'emails' => [
            'generated' => [
                'subject' => 'Un nouveau jeton API a été généré : :name',
                'greeting' => 'Un jeton d\'intégration API nommé ":name" vient d\'être généré sur votre compte.',
            ],
            'regenerated' => [
                'subject' => 'Votre jeton API a été régénéré : :name',
                'greeting' => 'Le jeton d\'intégration d\'API nommé ":name" vient d\'être régénéré. Le jeton précédent a cessé de fonctionner : seul le nouveau est valide.',
            ],
            'revoked' => [
                'subject' => 'Votre jeton API a été révoqué : :name',
                'greeting' => 'Le jeton d\'intégration d\'API nommé ":name" a été révoqué. Tout client qui l\'utilise a perdu l\'accès.',
            ],

            'details' => [
                'name' => 'Nom du jeton',
                'date' => 'Date',
                'ip' => 'Depuis la propriété intellectuelle',
            ],

            'revoke-hint' => 'Si vous ne vous y attendiez pas, révoquez immédiatement le token en utilisant le bouton ci-dessous.',
            'revoke-btn' => 'Révoquer ce jeton',
            'revoke-expiry' => 'Ce lien de révocation est valable 7 jours. Après cela, connectez-vous au panneau d\'administration pour gérer le jeton.',
            'no-action' => 'Aucune action n\'est nécessaire : cet e-mail n\'est qu\'une confirmation.',
        ],

        'revoke-confirmation' => [
            'title' => 'Révoquer le jeton API',
            'success-title' => 'Jeton révoqué',
            'success-message' => 'Le jeton ":name" a été révoqué. Tout client qui l\'utilise perd immédiatement l\'accès.',
            'already-inactive-title' => 'Jeton déjà inactif',
            'already-inactive-message' => 'Le jeton « :name » a déjà été révoqué ou régénéré. Aucune autre action n’est nécessaire.',
        ],

        'confirm' => [
            'generate' => [
                'title' => 'Générer un jeton',
                'message' => 'Générer le jeton maintenant ? Le texte brut ne sera affiché qu\'une seule fois : copiez-le avant de quitter la page.',
            ],
            'regenerate' => [
                'title' => 'Régénérer le jeton',
                'message' => 'Régénérer le jeton ? L\'ancien jeton cessera de fonctionner immédiatement et le nouveau texte brut ne sera affiché qu\'une seule fois.',
            ],
            'revoke' => [
                'title' => 'Révoquer le jeton',
                'message' => 'Révoquer ce jeton ? Tout client l’utilisant perdra immédiatement l’accès. Cette action ne peut pas être annulée.',
            ],
        ],
    ],
];
