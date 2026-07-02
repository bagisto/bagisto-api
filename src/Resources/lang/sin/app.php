<?php

return [
    'graphql' => [
        'cart' => [
            'authentication-required'           => 'සත්‍යාපන ටෝකනයක් අවශ්‍ය වේ',
            'invalid-token'                     => 'අවලංගු හෝ කල් ඉකුත් වූ සත්‍යාපන ටෝකනය',
            'unauthorized-access'               => 'කරත්තයට අනවසර ප්‍රවේශය',
            'authenticated-only'                => 'තම කරත්ත ලබා ගත හැක්කේ සත්‍යාපිත පරිශීලකයින්ට පමණි',
            'merge-requires-auth'               => 'අමුත්තන් ඒකාබද්ධ කිරීමට සත්‍යාපනය අවශ්‍ය වේ',
            'unknown-operation'                 => 'නොදන්නා කරත්ත මෙහෙයුම',

            'cart-not-found'                    => 'කරත්තය හමු නොවිණි',
            'guest-cart-not-found'              => 'අමුත්තාගේ කරත්තය හමු නොවිණි',
            'product-not-found'                 => 'නිෂ්පාදනය හමු නොවිණි',

            'product-id-quantity-required'      => 'නිෂ්පාදන ID සහ ප්‍රමාණය අවශ්‍ය වේ',
            'cart-item-id-quantity-required'    => 'කරත්ත අයිතම ID සහ ප්‍රමාණය අවශ්‍ය වේ',
            'cart-item-id-required'             => 'කරත්ත අයිතම ID අවශ්‍ය වේ',
            'item-ids-required'                 => 'අයිතම ID අරාව අවශ්‍ය වේ',
            'coupon-code-required'              => 'කූපන් කේතය අවශ්‍ය වේ',
            'address-data-required'             => 'රට, ප්‍රාන්තය සහ තැපැල් කේතය අවශ්‍ය වේ',

            'add-product-failed'                => 'නිෂ්පාදනය කරත්තයට එකතු කිරීමට අසමත් විය',
            'update-item-failed'                => 'කරත්ත අයිතමය යාවත්කාලීන කිරීමට අසමත් විය',
            'remove-item-failed'                => 'කරත්ත අයිතමය ඉවත් කිරීමට අසමත් විය',
            'apply-coupon-failed'               => 'කූපනය යෙදීමට අසමත් විය',
            'remove-coupon-failed'              => 'කූපනය ඉවත් කිරීමට අසමත් විය',
            'move-to-wishlist-failed'           => 'අයිතමය කැමති ලැයිස්තුවට ගෙනයාමට අසමත් විය',
            'estimate-shipping-failed'          => 'නැව්ගත කිරීම ඇස්තමේන්තු කිරීමට අසමත් විය',

            'product-added-successfully'         => 'නිෂ්පාදනය සාර්ථකව කරත්තයට එකතු කරන ලදී',
            'guest-cart-merged'                  => 'අමුත්තාගේ කරත්තය සාර්ථකව ඒකාබද්ධ කරන ලදී',
            'using-authenticated-cart'           => 'සත්‍යාපිත පාරිභෝගික කරත්තය භාවිතා කරමින්',
            'cart-item-not-found'                => 'කරත්ත අයිතමය හමු නොවිණි',
            'new-guest-cart-created'             => 'අද්විතීය සැසි ටෝකනයක් සමඟ නව අමුත්තාගේ කරත්තයක් සාදන ලදී',
            'select-items-to-remove'             => 'ඉවත් කිරීමට අයිතම තෝරන්න',
            'select-items-to-move-wishlist'      => 'කැමති ලැයිස්තුවට ගෙනයාමට අයිතම තෝරන්න',
            'invalid-or-expired-token'           => 'කරත්ත ටෝකනය අවලංගු හෝ කල් ඉකුත් වී ඇත. කරුණාකර නව කරත්තයක් සාදන්න.',
            'invalid-token-of-login-user'        => 'පුරනය වූ පරිශීලක ටෝකනය අවලංගුයි.',
        ],

        'token-verification' => [
            'invalid-operation'                 => 'අවලංගු මෙහෙයුම',
            'invalid-input-data'                => 'අවලංගු ආදාන දත්ත',
            'token-required'                    => 'ටෝකනය අවශ්‍ය වේ',
            'invalid-token-format'              => 'අවලංගු ටෝකන ආකෘතිය',
            'token-not-found-or-expired'        => 'ටෝකනය හමු නොවිණි හෝ කල් ඉකුත් වී ඇත',
            'customer-not-found'                => 'පාරිභෝගිකයා හමු නොවිණි',
            'customer-account-suspended'        => 'පාරිභෝගික ගිණුම අත්හිටුවා ඇත',
            'error-verifying-token'             => 'ටෝකනය සත්‍යාපනය කිරීමේ දෝෂයකි',
            'token-is-valid'                    => 'ටෝකනය වලංගුයි',
        ],

        'forgot-password' => [
            'invalid-operation'                 => 'අවලංගු මෙහෙයුම',
            'invalid-input-data'                => 'අවලංගු ආදාන දත්ත',
            'email-required'                    => 'ඊමේල් ලිපිනය අවශ්‍ය වේ',
            'reset-link-sent'                   => 'යළි සැකසුම් සබැඳිය ඔබගේ ඊමේල් වෙත සාර්ථකව යවන ලදී',
            'email-not-found'                   => 'ඊමේල් ලිපිනය හමු නොවිණි',
            'error-sending-reset-link'          => 'යළි සැකසුම් සබැඳිය යැවීමේදී දෝෂයක් ඇතිවිය',
        ],

        'logout' => [
            'invalid-operation'                 => 'අවලංගු මෙහෙයුම',
            'invalid-input-data'                => 'අවලංගු ආදාන දත්ත',
            'token-required'                    => 'ටෝකනය අවශ්‍ය වේ',
            'invalid-token-format'              => 'අවලංගු ටෝකන ආකෘතිය',
            'logged-out-successfully'           => 'සාර්ථකව පිටවිය',
            'token-not-found-or-expired'        => 'ටෝකනය හමු නොවිණි හෝ දැනටමත් කල් ඉකුත් වී ඇත',
            'error-during-logout'               => 'පිටවීමේදී දෝෂයකි',
        ],

        'address' => [
            'deleted-successfully'              => 'ලිපිනය සාර්ථකව මකා දමන ලදී',
            'authentication-required'           => 'සත්‍යාපන ටෝකනයක් අවශ්‍ය වේ',
            'invalid-token'                     => 'අවලංගු හෝ කල් ඉකුත් වූ ටෝකනය',
            'unknown-operation'                 => 'නොදන්නා මෙහෙයුම',
            'address-id-required'               => 'ලිපින ID අවශ්‍ය වේ',
            'address-not-found'                 => 'ලිපිනය හමු නොවිණි හෝ මෙම පාරිභෝගිකයාට අයත් නොවේ',
            'retrieved'                         => 'ලිපින සාර්ථකව ලබා ගන්නා ලදී',
            'fetch-failed'                      => 'ලිපින ලබා ගැනීමට අසමත් විය:',
        ],

        'customer-profile' => [
            'authentication-required'           => 'සත්‍යාපන ටෝකනයක් අවශ්‍ය වේ. කරුණාකර විමසුම් ආදානයේ ටෝකනය සපයන්න',
            'invalid-token'                     => 'අවලංගු හෝ කල් ඉකුත් වූ ටෝකනය',
        ],

        'customer' => [
            'password-mismatch'                 => 'මුරපදය සහ තහවුරු කිරීමේ මුරපදය නොගැලපේ',
            'confirm-password-required'         => 'මුරපදය වෙනස් කිරීමේදී තහවුරු කිරීමේ මුරපදය අවශ්‍ය වේ',
            'unauthenticated'                   => 'සත්‍යාපනය නොකළ. මෙම ක්‍රියාව සිදු කිරීමට කරුණාකර පුරනය වන්න',
        ],

        'product-review' => [
            'product-id-required'               => 'නිෂ්පාදන ID අවශ්‍ය වේ',
            'product-not-found'                 => 'නිෂ්පාදනය හමු නොවිණි',
            'rating-invalid'                    => 'ශ්‍රේණිගත කිරීම 1 සහ 5 අතර විය යුතුය',
            'title-required'                    => 'සමාලෝචන මාතෘකාව අවශ්‍ය වේ',
            'comment-required'                  => 'සමාලෝචන අදහස අවශ්‍ය වේ',
        ],

        'product' => [
            'not-found-with-sku'                => 'No product found with SKU',
            'not-found-with-url-key'            => 'No product found with URL key',
            'parameters-required'               => 'At least one of the following parameters must be provided: "sku", "id", "urlKey"',
        ],

        'auth' => [
            'no-token-provided'                 => 'සත්‍යාපන ටෝකනයක් සපයා නොමැත. කරුණාකර Authorization ශීර්ෂකයේ "Bearer <token>" ලෙස හෝ input.token ක්ෂේත්‍රයේ ටෝකනය සපයන්න',
            'invalid-or-expired-token'          => 'අවලංගු හෝ කල් ඉකුත් වූ ටෝකනය',
            'request-not-found'                 => 'සන්දර්භය තුළ ඉල්ලීම හමු නොවිණි',
            'token-required'                    => 'Authentication token is required. Please provide the token either in the GraphQL mutation input field or in the Authorization header as "Bearer <token>"',
            'unknown-resource'                  => 'නොදන්නා සම්පත',
            'cannot-update-other-profile'       => 'අනවසර: වෙනත් පාරිභෝගික පැතිකඩක් යාවත්කාලීන කළ නොහැක',
        ],

        'upload' => [
            'invalid-base64'                    => 'අවලංගු base64 කේතාංකන රූප දත්ත',
            'size-exceeds-limit'                => 'රූප ප්‍රමාණය 5MB ඉක්මවිය නොහැක',
            'invalid-format'                    => 'අවලංගු රූප ආකෘතිය. කරුණාකර data URI යෝජනා ක්‍රමය සමඟ base64 කේතාංකන රූපයක් සපයන්න (data:image/jpeg;base64,...)',
            'failed'                            => 'රූප උඩුගත කිරීම අසමත් විය',
        ],

        'attribute' => [
            'code-already-exists'               => 'ගුණාංග කේතය දැනටමත් පවතී',
        ],

        'login' => [
            'invalid-credentials'               => 'අවලංගු ඊමේල් හෝ මුරපදය',
            'account-suspended'                 => 'ඔබගේ ගිණුම අත්හිටුවා ඇත',
            'successful'                        => 'ඔබ සාර්ථකව පුරනය වී ඇත',
            'invalid-request'                   => 'අවලංගු පුරනය වීමේ ඉල්ලීම',
        ],

        'checkout' => [
            'invalid-input'                     => 'ගෙවීම් මෙහෙයුම සඳහා අවලංගු ආදාන දත්ත',
            'billing-address-required'          => 'බිල්පත් ලිපිනය අවශ්‍ය වේ',
            'shipping-address-required'         => 'නැව්ගත කිරීම් සඳහා නැව්ගත ලිපිනය අවශ්‍ය වේ',
            'address-save-failed'               => 'ලිපිනය සුරැකීමට අසමත් විය',
            'address-saved'                     => 'ලිපිනය සාර්ථකව සුරැකිණි',
            'shipping-method-required'          => 'නැව්ගත කිරීමේ ක්‍රමය අවශ්‍ය වේ',
            'invalid-shipping-method'           => 'අවලංගු හෝ නොමැති නැව්ගත කිරීමේ ක්‍රමය',
            'shipping-method-save-failed'       => 'නැව්ගත කිරීමේ ක්‍රමය සුරැකීමට අසමත් විය',
            'shipping-method-saved'             => 'නැව්ගත කිරීමේ ක්‍රමය සාර්ථකව සුරැකිණි',
            'shipping-method-error'             => 'නැව්ගත කිරීමේ ක්‍රමය සුරැකීමේ දෝෂයකි',
            'payment-method-required'           => 'ගෙවීම් ක්‍රමය අවශ්‍ය වේ',
            'invalid-payment-method'            => 'අවලංගු හෝ නොමැති ගෙවීම් ක්‍රමය',
            'payment-method-save-failed'        => 'ගෙවීම් ක්‍රමය සුරැකීමට අසමත් විය',
            'payment-method-saved'              => 'ගෙවීම් ක්‍රමය සාර්ථකව සුරැකිණි',
            'payment-method-error'              => 'ගෙවීම් ක්‍රමය සුරැකීමේ දෝෂයකි',
            'order-creation-failed'             => 'ඇණවුම සෑදීම අසමත් විය: ඇණවුම් ID හිස් හෝ ඇණවුම සුරැකී නැත',
            'order-retrieval-failed'            => 'සාදන ලද ඇණවුම ලබා ගැනීමට අසමත් විය',
            'order-creation-error'              => 'ඇණවුම සෑදීමට අසමත් විය',
            'cart-empty'                        => 'කරත්තය හිස්ය',
            'account-suspended'                 => 'ඔබගේ ගිණුම අත්හිටුවා ඇත. කරුණාකර සහාය අමතන්න.',
            'account-inactive'                  => 'ඔබගේ ගිණුම අක්‍රියයි. කරුණාකර සහාය අමතන්න.',
            'minimum-order-not-met'             => 'අවම ඇණවුම් මුදල :amount වේ',
            'email-required'                    => 'ඇණවුම සෑදීම සඳහා ඊමේල් ලිපිනය අවශ්‍ය වේ',
            'unknown-operation'                 => 'නොදන්නා ගෙවීම් මෙහෙයුම',
        ],

        'customer-addresses' => [
            'token-required'                    => 'පාරිභෝගික ලිපින ලබා ගැනීමට ටෝකනය අවශ්‍ය වේ',
            'invalid-or-expired-token'          => 'අවලංගු හෝ කල් ඉකුත් වූ ටෝකනය',
            'token-validation-failed'           => 'ටෝකන සත්‍යාපනය අසමත් විය',
        ],

        'product' => [
            'type'                              => 'නිෂ්පාදන වර්ගය',
            'attribute-family'                  => 'ගුණාංග පවුල',
            'sku'                               => 'SKU',
            'name'                              => 'නම',
            'description'                       => 'විස්තරය',
            'short-description'                 => 'කෙටි විස්තරය',
            'status'                            => 'තත්ත්වය',
            'new'                               => 'නව',
            'featured'                          => 'විශේෂාංගගත',
            'price'                             => 'මිල',
            'special-price'                     => 'විශේෂ මිල',
            'weight'                            => 'බර',
            'cost'                              => 'පිරිවැය',
            'length'                            => 'දිග',
            'width'                             => 'පළල',
            'height'                            => 'උස',
            'color'                             => 'වර්ණය',
            'size'                              => 'ප්‍රමාණය',
            'brand'                             => 'වෙළඳ නාමය',
            'super-attributes'                  => 'සුපිරි ගුණාංග',
        ],

        'compare-item' => [
            'id-required'                       => 'සැසඳුම් අයිතමයට ID අවශ්ය है.',
            'invalid-id-format'                 => 'අවලංගු ID ස්වරූපය. "/api/shop/compare-items/1" හෝ සංඛ්යාත්මක ID වැනි IRI ස්වරූපය අපේක්ෂා කෙරේ',
            'not-found'                         => 'සැසඳුම් අයිතමය හමු නොවිණි',
            'product-id-required'               => 'නිෂ්පාදන ID අවශ්ය है.',
            'customer-id-required'              => 'ගස්සා ගැනීමේ ID අවශ්ය है.',
            'product-not-found'                 => 'නිෂ්පාදනය හමු නොවිණි',
            'customer-not-found'                => 'ගස්සා ගැනීම හමු නොවිණි',
            'already-exists'                    => 'මෙම නිෂ්පාදනය ඔබගේ සැසඳුම් ලැයිස්තුවේ දැනටමත් පවතී',
        ],

        'downloadable-product' => [
            'download-link-not-found'           => 'බාගැනීම් සබැඳුව හමු නොවිණි හෝ කල් ඉකුත් වී ඇත',
            'purchased-link-not-found'          => 'ඉ購නුගත සබැඳුව හමු නොවිණි',
            'file-not-found'                    => 'ගොනුව හමු නොවිණි',
            'download-successful'               => 'ගොනුව බාගැනීමට සූදානම්',
            'token-required'                    => 'බාගැනීම් ටෝකනය අවශ්ය වේ',
            'invalid-token'                     => 'බාගැනීම් ටෝකනය অবලංගු හෝ කල් ඉකුත්',
            'token-expired'                     => 'බාගැනීම් ටෝකනය කල් ඉකුත් වී ඇත. කරුණාකර නව එකක් සාදන්න',
            'access-denied'                     => 'ප්‍රවේශ ප්‍රතික්ෂේප කරන ලදි: ඔබ මෙම ගොනුව බාගැනීමට අවසර නොමាន',
            'redirect-external-url'             => 'බාහිර බාගැනීම් URL එකට යළි යොමු කිරීම',
            'file-error'                        => 'ඔබගේ බාගැනීම් ඉල්ලීම සකසා ගන්නා අතරතුර දෝෂයක් ඇතිවිය',
            'unauthorized-access'               => 'බාගැනීම් සම්පතට අননුමතිකරණ ප්‍රවේශ',
        ],
    ],

    'integration' => [
        'menu' => [
            'title'  => 'ඒකාබද්ධ කිරීම',
            'tokens' => 'ටෝකන',
        ],

        'history' => [
            'menu' => [
                'title' => 'ඉතිහාසය',
            ],

            'acl' => [
                'title'  => 'API වෙනස් කිරීමේ ඉතිහාසය',
                'delete' => 'ඉතිහාසය මකන්න',
            ],

            'index' => [
                'title'           => 'API වෙනස් කිරීමේ ඉතිහාසය',
                'info'            => 'පරිපාලක API හරහා සාදන ලද සෑම නිර්මාණයක්ම, යාවත්කාලීන කිරීම සහ මකා දැමීම, එය කළේ කවුරුන්ද, කුමන ටෝකනය සහ වෙනස් කළ දේ සමඟද.',
                'cleanup-btn'     => 'පැරණි ලොග මකන්න',
                'cleanup-days'    => 'මෙම දින ගණනට වඩා පැරණි ලොග මකන්න',
                'cleanup-confirm' => 'දී ඇති දින ගණනට වඩා පැරණි සියලුම ඉතිහාසය මකන්නද? මෙය ආපසු හැරවිය නොහැක.',
            ],

            'view' => [
                'title'             => 'වෙනස් කරන්න',
                'back-btn'          => 'ආපසු',
                'admin'             => 'පරිපාලක',
                'token'             => 'ටෝකනය',
                'action'            => 'ක්‍රියාව',
                'resource'          => 'සම්පත්',
                'method'            => 'ක්රමය',
                'ip'                => 'IP ලිපිනය',
                'date'              => 'දිනය',
                'version'           => 'අනුවාදය',
                'url'               => 'අන්ත ලක්ෂ්‍යය',
                'request-details'   => 'ඉල්ලීම් විස්තර',
                'changes'           => 'වෙනස්කම්',
                'field'             => 'ක්ෂේත්රය',
                'old'               => 'පැරණි වටිනාකම',
                'new'               => 'නව වටිනාකමක්',
                'no-field-changes'  => 'මෙම ප්‍රවේශය සඳහා ක්ෂේත්‍ර මට්ටමේ වෙනස්කම් කිසිවක් වාර්තා කර නොමැත.',
                'same-request'      => 'එකම ඉල්ලීමේ වෙනත් වෙනස්කම්',
                'version-chain'     => 'මෙම වාර්තාවේ අනුවාද ඉතිහාසය',
            ],

            'datagrid' => [
                'id'        => 'හැඳුනුම්පත',
                'date'      => 'දිනය',
                'admin'     => 'පරිපාලක',
                'token'     => 'ටෝකනය',
                'action'    => 'ක්‍රියාව',
                'operation' => 'මෙහෙයුම',
                'resource'  => 'සම්පත්',
                'version'   => 'අනුවාදය',
                'method'    => 'ක්රමය',
                'ip'        => 'IP',
                'view'      => 'බලන්න',
                'delete'    => 'මකන්න',
            ],

            'events' => [
                'created' => 'නිර්මාණය කළා',
                'updated' => 'යාවත්කාලීන කරන ලදී',
                'deleted' => 'මකා දමන ලදී',
            ],

            'deleted'                => ':count ඉතිහාස වාර්තාව(ය) මකා ඇත.',
            'cleanup-input-required' => 'පිරිසිදු කිරීමට දින කිහිපයක් හෝ දිනයක් ලබා දෙන්න.',
        ],

        'acl' => [
            'title'      => 'ඒකාබද්ධ කිරීම',
            'create'     => 'ඒකාබද්ධ කිරීම සාදන්න',
            'edit'       => 'අනුකලනය සංස්කරණය කරන්න',
            'delete'     => 'ඒකාබද්ධතා ටෝකනය අවලංගු කරන්න',
            'generate'   => 'ඒකාබද්ධතා ටෝකනය ජනනය කරන්න',
            'regenerate' => 'ඒකාබද්ධතා ටෝකනය නැවත උත්පාදනය කරන්න',
        ],

        'index' => [
            'title'      => 'ඒකාබද්ධ කිරීම්',
            'create-btn' => 'ඒකාබද්ධ කිරීම සාදන්න',
        ],

        'create' => [
            'title'    => 'ඒකාබද්ධ කිරීම සාදන්න',
            'save-btn' => 'සුරකින්න',
            'back-btn' => 'ආපසු',
        ],

        'edit' => [
            'title'          => 'අනුකලනය සංස්කරණය කරන්න',
            'save-btn'       => 'සුරකින්න',
            'back-btn'       => 'ආපසු',
            'generate-btn'   => 'ටෝකනය ජනනය කරන්න',
            'regenerate-btn' => 'ටෝකනය නැවත උත්පාදනය කරන්න',
            'revoke-btn'     => 'ටෝකනය අවලංගු කරන්න',
            'copy-btn'       => 'පිටපත් කරන්න',
            'token-warning'  => 'මෙම ටෝකනය දැන් සුරකින්න - එය නැවත නොපෙන්වයි.',
            'token-label'    => 'ටෝකනය',
            'not-generated'  => 'තවමත් ජනනය කර නැත',
            'masked'         => '(ගබඩා - පරම්පරාවෙන් එක් වරක් පමණක් පෙන්වනු ලැබේ)',
            'history-banner' => 'මෙම ටෝකනය තවදුරටත් සක්‍රිය නොවේ.',
        ],

        'fields' => [
            'name'                  => 'නම',
            'description'           => 'විස්තරය',
            'assign-user'           => 'පරිශීලක පවරන්න',
            'permission-type'       => 'අවසර වර්ගය',
            'access-control'        => 'ප්රවේශ පාලනය',
            'general'               => 'ජෙනරාල්',
            'token-settings'        => 'ටෝකන් සැකසුම්',
            'valid-till'            => 'දක්වා වලංගු වේ',
            'rate-limit-per-minute' => 'ගාස්තු සීමාව (විනාඩියකට)',
            'rate-limit-per-day'    => 'ගාස්තු සීමාව (දිනකට)',
            'never-expires'         => 'කිසිදා කල් ඉකුත් නොවේ',
            'expires-on'            => 'කල් ඉකුත් වේ',
            'unlimited'             => 'අසීමිතයි',
            'limit-to'              => 'දක්වා සීමා කරන්න',
            'requests-per-minute'   => 'ඉල්ලීම් / මිනිත්තුව',
            'requests-per-day'      => 'ඉල්ලීම් / දින',
            'select-admin'          => 'පරිපාලකයෙකු තෝරන්න',
            'no-available-admins'   => 'පරිපාලකයින් නොමැත - සෑම පරිපාලකයෙකුටම දැනටමත් ක්‍රියාකාරී ටෝකනයක් ඇත.',
            'same-as-web-hint'      => 'ටෝකනය පවරා ඇති පරිපාලකගේ වත්මන් භූමිකාව අවසර සජීවීව පිළිබිඹු කරයි.',
            'ip-allowlist'          => 'IP අවසර ලැයිස්තුව',
            'ip-any'                => 'ඕනෑම IP (පෙරනිමි)',
            'ip-restricted'         => 'විශේෂිත IP වලට සීමා කර ඇත',
            'ip-list-hint'          => 'පේළියකට එක් ඇතුල්වීමක්. IPv4, IPv6 සහ CIDR සඳහා සහය දක්වයි (උදා. 10.0.0.0/24 හෝ 2001:db8::/32). සියලුම IP වලට ඉඩ දීමට හිස්ව තබන්න.',
        ],

        'permission_type' => [
            'all'         => 'සියල්ල',
            'custom'      => 'අභිරුචි',
            'same_as_web' => 'වෙබ් අවසරය හා සමානයි',
        ],

        'status' => [
            'draft'       => 'කෙටුම්පත',
            'active'      => 'ක්රියාකාරී',
            'revoked'     => 'අවලංගු කළා',
            'regenerated' => 'නැවත උත්පාදනය කරන ලදී',
        ],

        'datagrid' => [
            'id'              => 'හැඳුනුම්පත',
            'name'            => 'නම',
            'admin'           => 'පරිපාලක',
            'token'           => 'ටෝකනය',
            'status'          => 'තත්ත්වය',
            'permission-type' => 'අවසර වර්ගය',
            'expires-at'      => 'දක්වා වලංගු වේ',
            'last-used-at'    => 'අවසන් වරට භාවිතා කරන ලදී',
            'created-at'      => 'දී නිර්මාණය කරන ලදී',
            'edit'            => 'සංස්කරණය කරන්න',
            'revoke'          => 'අවලංගු කරන්න',
        ],

        'messages' => [
            'draft-created'          => 'ඒකාබද්ධ කිරීම නිර්මාණය කරන ලදී. එය භාවිතා කිරීම ආරම්භ කිරීමට ටෝකනය ජනනය කරන්න.',
            'updated'                => 'ඒකාබද්ධ කිරීම සාර්ථකව යාවත්කාලීන කරන ලදී.',
            'generated'              => 'ටෝකනය ජනනය කරන ලදී. දැන් එය පිටපත් කරන්න - එය නැවත නොපෙන්වයි.',
            'regenerated'            => 'ටෝකනය නැවත උත්පාදනය කරන ලදී. දැන් නව ටෝකනය පිටපත් කරන්න - එය නැවත නොපෙන්වයි.',
            'revoked'                => 'ටෝකනය සාර්ථකව අවලංගු කරන ලදී.',
            'generate-only-draft'    => 'ඒවායේ ටෝකනය ජනනය කළ හැක්කේ කෙටුම්පත් ඒකාබද්ධ කිරීම්වලට පමණි.',
            'regenerate-only-active' => 'නැවත උත්පාදනය කළ හැක්කේ සක්‍රීය ටෝකන පමණි.',
            'cannot-edit-historic'   => 'අවලංගු කළ හෝ නැවත උත්පාදනය කළ ටෝකන සංස්කරණය කළ නොහැක.',
            'already-inactive'       => 'මෙම ටෝකනය දැනටමත් අක්‍රියයි.',
        ],

        'errors' => [
            'admin-has-token' => 'තෝරාගත් පරිපාලකයාට දැනටමත් ක්‍රියාකාරී ඒකාබද්ධ කිරීමේ ටෝකනයක් ඇත.',
        ],

        'validation' => [
            'ip-invalid'           => 'සෑම අවසර ලත් IP එකක්ම වලංගු IPv4 හෝ IPv6 ලිපිනයක් විය යුතුය (CIDR අංකනයට සහය දක්වයි).',
            'cidr-prefix-invalid'  => 'ලබා දී ඇති IP අනුවාදය සඳහා CIDR උපසර්ගය වලංගු නොවේ.',
        ],

        'configuration' => [
            'api' => [
                'title' => 'API',
                'info'  => 'Bagisto API සහ එහි පරිපාලක මොඩියුල සඳහා සැකසීම්.',
            ],
            'integration' => [
                'title' => 'ඒකාබද්ධ කිරීම',
                'info'  => 'පරිපාලක API ටෝකන නිකුත් කිරීමට භාවිතා කරන API ඒකාබද්ධතා ප්ලගිනය කළමනාකරණය කරන්න.',
            ],
            'settings' => [
                'title'  => 'මොඩියුල සැකසුම්',
                'info'   => 'API Integration ප්ලගිනය සබල කරන්න හෝ අක්‍රිය කරන්න. අබල කළ විට, එහි පැති තීරු මෙනුව සඟවා ඇති අතර එහි පිටු 404 ලබා දෙයි.',
                'enable' => 'API ඒකාබද්ධතා මොඩියුලය සබල කරන්න',
            ],
        ],

        'emails' => [
            'generated' => [
                'subject'  => 'නව API ටෝකනයක් උත්පාදනය කරන ලදී: :name',
                'greeting' => '":name" නමින් API ඒකාබද්ධතා ටෝකනයක් ඔබගේ ගිණුමේ ජනනය කර ඇත.',
            ],
            'regenerated' => [
                'subject'  => 'ඔබගේ API ටෝකනය නැවත උත්පාදනය කරන ලදී: :name',
                'greeting' => '":name" නම් API ඒකාබද්ධතා ටෝකනය මේ දැන් නැවත උත්පාදනය කරන ලදී. පෙර ටෝකනය වැඩ කිරීම නතර කර ඇත - නව එක පමණක් වලංගු වේ.',
            ],
            'revoked' => [
                'subject'  => 'ඔබගේ API ටෝකනය අවලංගු කරන ලදී: :name',
                'greeting' => '":name" නම් වූ API ඒකාබද්ධතා ටෝකනය අවලංගු කරන ලදී. එය භාවිතා කරන ඕනෑම සේවාදායකයකුට ප්‍රවේශය අහිමි වී ඇත.',
            ],

            'details' => [
                'name' => 'සංකේත නාමය',
                'date' => 'දිනය',
                'ip'   => 'IP වලින්',
            ],

            'revoke-hint'   => 'ඔබ මෙය අපේක්ෂා නොකළේ නම්, පහත බොත්තම භාවිතයෙන් වහාම ටෝකනය අවලංගු කරන්න.',
            'revoke-btn'    => 'මෙම ටෝකනය අවලංගු කරන්න',
            'revoke-expiry' => 'මෙම අවලංගු කිරීමේ සබැඳිය දින 7ක් සඳහා වලංගු වේ. ඊට පසු, ටෝකනය කළමනාකරණය කිරීමට පරිපාලක පැනලය වෙත පුරනය වන්න.',
            'no-action'     => 'කිසිදු ක්‍රියාමාර්ගයක් අවශ්‍ය නොවේ - මෙම විද්‍යුත් තැපෑල තහවුරු කිරීමක් පමණි.',
        ],

        'revoke-confirmation' => [
            'title'                    => 'API ටෝකනය අවලංගු කරන්න',
            'success-title'            => 'ටෝකනය අවලංගු කරන ලදී',
            'success-message'          => '":name" ටෝකනය අවලංගු කර ඇත. එය භාවිතා කරන ඕනෑම සේවාදායකයකුට වහාම ප්‍රවේශය අහිමි වී ඇත.',
            'already-inactive-title'   => 'ටෝකනය දැනටමත් අක්‍රියයි',
            'already-inactive-message' => '":name" ටෝකනය දැනටමත් අවලංගු කර හෝ නැවත උත්පාදනය කර ඇත. වැඩිදුර ක්‍රියාමාර්ග අවශ්‍ය නොවේ.',
        ],

        'confirm' => [
            'generate' => [
                'title'   => 'ටෝකනය ජනනය කරන්න',
                'message' => 'දැන් ටෝකනය ජනනය කරන්නද? සරල පාඨය එක් වරක් පමණක් පෙන්වනු ඇත - පිටුවෙන් පිටවීමට පෙර එය පිටපත් කරන්න.',
            ],
            'regenerate' => [
                'title'   => 'ටෝකනය නැවත උත්පාදනය කරන්න',
                'message' => 'ටෝකනය නැවත උත්පාදනය කරන්නද? පැරණි ටෝකනය වහාම ක්‍රියා විරහිත වන අතර නව සරල පාඨය එක් වරක් පමණක් පෙන්වනු ඇත.',
            ],
            'revoke' => [
                'title'   => 'ටෝකනය අවලංගු කරන්න',
                'message' => 'මෙම ටෝකනය අවලංගු කරන්නද? එය භාවිතා කරන ඕනෑම සේවාදායකයෙක් වහාම ප්‍රවේශය අහිමි වනු ඇත. මෙම ක්‍රියාව පසුගමනය කළ නොහැක.',
            ],
        ],
    ],
];
