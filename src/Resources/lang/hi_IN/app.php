<?php

return [
    'graphql' => [
        'cart' => [
            'authentication-required' => 'प्रमाणीकरण टोकन आवश्यक है',
            'invalid-token' => 'अमान्य या समाप्त प्रमाणीकरण टोकन',
            'unauthorized-access' => 'कार्ट तक अनधिकृत पहुँच',
            'authenticated-only' => 'केवल प्रमाणित उपयोगकर्ता ही अपनी कार्ट प्राप्त कर सकते हैं',
            'merge-requires-auth' => 'अतिथि विलय के लिए प्रमाणीकरण आवश्यक है',
            'unknown-operation' => 'अज्ञात कार्ट ऑपरेशन',

            'cart-not-found' => 'कार्ट नहीं मिली',
            'guest-cart-not-found' => 'अतिथि कार्ट नहीं मिली',
            'product-not-found' => 'उत्पाद नहीं मिला',

            'product-id-quantity-required' => 'उत्पाद ID और मात्रा आवश्यक है',
            'cart-item-id-quantity-required' => 'कार्ट आइटम ID और मात्रा आवश्यक है',
            'cart-item-id-required' => 'कार्ट आइटम ID आवश्यक है',
            'item-ids-required' => 'आइटम ID सरणी आवश्यक है',
            'coupon-code-required' => 'कूपन कोड आवश्यक है',
            'address-data-required' => 'देश, राज्य और पिनकोड आवश्यक हैं',

            'add-product-failed' => 'उत्पाद को कार्ट में जोड़ने में विफल',
            'update-item-failed' => 'कार्ट आइटम अपडेट करने में विफल',
            'remove-item-failed' => 'कार्ट आइटम हटाने में विफल',
            'apply-coupon-failed' => 'कूपन लागू करने में विफल',
            'remove-coupon-failed' => 'कूपन हटाने में विफल',
            'move-to-wishlist-failed' => 'आइटम को इच्छा-सूची में ले जाने में विफल',
            'estimate-shipping-failed' => 'शिपिंग अनुमान लगाने में विफल',

            'product-added-successfully' => 'उत्पाद सफलतापूर्वक कार्ट में जोड़ा गया',
            'guest-cart-merged' => 'अतिथि कार्ट सफलतापूर्वक विलय किया गया',
            'using-authenticated-cart' => 'प्रमाणित ग्राहक कार्ट का उपयोग किया जा रहा है',
            'cart-item-not-found' => 'कार्ट आइटम नहीं मिला',
            'new-guest-cart-created' => 'अद्वितीय सत्र टोकन के साथ नई अतिथि कार्ट बनाई गई',
            'select-items-to-remove' => 'कृपया हटाने के लिए आइटम चुनें',
            'select-items-to-move-wishlist' => 'कृपया इच्छा-सूची में ले जाने के लिए आइटम चुनें',
            'invalid-or-expired-token' => 'कार्ट टोकन अमान्य या समाप्त हो गया है। कृपया एक नई कार्ट बनाएँ।',
            'invalid-token-of-login-user' => 'लॉगिन उपयोगकर्ता टोकन अमान्य है।',
        ],

        'token-verification' => [
            'invalid-operation' => 'अमान्य ऑपरेशन',
            'invalid-input-data' => 'अमान्य इनपुट डेटा',
            'token-required' => 'टोकन आवश्यक है',
            'invalid-token-format' => 'अमान्य टोकन प्रारूप',
            'token-not-found-or-expired' => 'टोकन नहीं मिला या समाप्त हो गया है',
            'customer-not-found' => 'ग्राहक नहीं मिला',
            'customer-account-suspended' => 'ग्राहक खाता निलंबित है',
            'error-verifying-token' => 'टोकन सत्यापित करने में त्रुटि',
            'token-is-valid' => 'टोकन मान्य है',
        ],

        'forgot-password' => [
            'invalid-operation' => 'अमान्य ऑपरेशन',
            'invalid-input-data' => 'अमान्य इनपुट डेटा',
            'email-required' => 'ईमेल आवश्यक है',
            'reset-link-sent' => 'रीसेट लिंक सफलतापूर्वक आपके ईमेल पर भेजा गया',
            'email-not-found' => 'ईमेल पता नहीं मिला',
            'error-sending-reset-link' => 'रीसेट लिंक भेजते समय एक त्रुटि हुई',
        ],

        'logout' => [
            'invalid-operation' => 'अमान्य ऑपरेशन',
            'invalid-input-data' => 'अमान्य इनपुट डेटा',
            'token-required' => 'टोकन आवश्यक है',
            'invalid-token-format' => 'अमान्य टोकन प्रारूप',
            'logged-out-successfully' => 'सफलतापूर्वक लॉग आउट किया गया',
            'token-not-found-or-expired' => 'टोकन नहीं मिला या पहले ही समाप्त हो गया है',
            'error-during-logout' => 'लॉगआउट के दौरान त्रुटि',
        ],

        'address' => [
            'deleted-successfully' => 'पता सफलतापूर्वक हटाया गया',
            'authentication-required' => 'प्रमाणीकरण टोकन आवश्यक है',
            'invalid-token' => 'अमान्य या समाप्त टोकन',
            'unknown-operation' => 'अज्ञात ऑपरेशन',
            'address-id-required' => 'पता ID आवश्यक है',
            'address-not-found' => 'पता नहीं मिला या इस ग्राहक से संबंधित नहीं है',
            'retrieved' => 'पते सफलतापूर्वक प्राप्त किए गए',
            'fetch-failed' => 'पते प्राप्त करने में विफल:',
        ],

        'customer-profile' => [
            'authentication-required' => 'प्रमाणीकरण टोकन आवश्यक है। कृपया क्वेरी इनपुट में टोकन प्रदान करें',
            'invalid-token' => 'अमान्य या समाप्त टोकन',
        ],

        'customer' => [
            'password-mismatch' => 'पासवर्ड और पुष्टि पासवर्ड मेल नहीं खाते',
            'confirm-password-required' => 'पासवर्ड बदलते समय पुष्टि पासवर्ड आवश्यक है',
            'unauthenticated' => 'अप्रमाणित। इस क्रिया को करने के लिए कृपया लॉगिन करें',
        ],

        'product-review' => [
            'product-id-required' => 'उत्पाद ID आवश्यक है',
            'product-not-found' => 'उत्पाद नहीं मिला',
            'rating-invalid' => 'रेटिंग 1 और 5 के बीच होनी चाहिए',
            'title-required' => 'समीक्षा शीर्षक आवश्यक है',
            'comment-required' => 'समीक्षा टिप्पणी आवश्यक है',
        ],

        'product' => [
            'not-found-with-sku' => 'No product found with SKU',
            'not-found-with-url-key' => 'No product found with URL key',
            'parameters-required' => 'At least one of the following parameters must be provided: "sku", "id", "urlKey"',
        ],

        'auth' => [
            'no-token-provided' => 'कोई प्रमाणीकरण टोकन प्रदान नहीं किया गया। कृपया Authorization हेडर में "Bearer <token>" के रूप में या input.token फ़ील्ड में टोकन प्रदान करें',
            'invalid-or-expired-token' => 'अमान्य या समाप्त टोकन',
            'request-not-found' => 'संदर्भ में अनुरोध नहीं मिला',
            'token-required' => 'Authentication token is required. Please provide the token either in the GraphQL mutation input field or in the Authorization header as "Bearer <token>"',
            'unknown-resource' => 'अज्ञात संसाधन',
            'cannot-update-other-profile' => 'अनधिकृत: किसी अन्य ग्राहक प्रोफ़ाइल को अपडेट नहीं कर सकते',
        ],

        'upload' => [
            'invalid-base64' => 'अमान्य base64 एन्कोडेड छवि डेटा',
            'size-exceeds-limit' => 'छवि का आकार 5MB से अधिक नहीं होना चाहिए',
            'invalid-format' => 'अमान्य छवि प्रारूप। कृपया डेटा URI स्कीम के साथ base64 एन्कोडेड छवि प्रदान करें (data:image/jpeg;base64,...)',
            'failed' => 'छवि अपलोड विफल',
        ],

        'attribute' => [
            'code-already-exists' => 'विशेषता कोड पहले से मौजूद है',
        ],

        'login' => [
            'invalid-credentials' => 'अमान्य ईमेल या पासवर्ड',
            'account-suspended' => 'आपका खाता निलंबित कर दिया गया है',
            'successful' => 'आपने सफलतापूर्वक लॉग इन कर लिया है',
            'invalid-request' => 'अमान्य लॉगिन अनुरोध',
        ],

        'checkout' => [
            'invalid-input' => 'चेकआउट ऑपरेशन के लिए अमान्य इनपुट डेटा',
            'billing-address-required' => 'बिलिंग पता आवश्यक है',
            'shipping-address-required' => 'शिपमेंट के लिए शिपिंग पता आवश्यक है',
            'address-save-failed' => 'पता सहेजने में विफल',
            'address-saved' => 'पता सफलतापूर्वक सहेजा गया',
            'shipping-method-required' => 'शिपिंग विधि आवश्यक है',
            'invalid-shipping-method' => 'अमान्य या अनुपलब्ध शिपिंग विधि',
            'shipping-method-save-failed' => 'शिपिंग विधि सहेजने में विफल',
            'shipping-method-saved' => 'शिपिंग विधि सफलतापूर्वक सहेजी गई',
            'shipping-method-error' => 'शिपिंग विधि सहेजने में त्रुटि',
            'payment-method-required' => 'भुगतान विधि आवश्यक है',
            'invalid-payment-method' => 'अमान्य या अनुपलब्ध भुगतान विधि',
            'payment-method-save-failed' => 'भुगतान विधि सहेजने में विफल',
            'payment-method-saved' => 'भुगतान विधि सफलतापूर्वक सहेजी गई',
            'payment-method-error' => 'भुगतान विधि सहेजने में त्रुटि',
            'order-creation-failed' => 'ऑर्डर निर्माण विफल: ऑर्डर ID शून्य है या ऑर्डर सहेजा नहीं गया',
            'order-retrieval-failed' => 'बनाए गए ऑर्डर को प्राप्त करने में विफल',
            'order-creation-error' => 'ऑर्डर बनाने में विफल',
            'cart-empty' => 'कार्ट खाली है',
            'account-suspended' => 'आपका खाता निलंबित कर दिया गया है। कृपया सहायता से संपर्क करें।',
            'account-inactive' => 'आपका खाता निष्क्रिय है। कृपया सहायता से संपर्क करें।',
            'minimum-order-not-met' => 'न्यूनतम ऑर्डर राशि :amount है',
            'email-required' => 'ऑर्डर निर्माण के लिए ईमेल पता आवश्यक है',
            'unknown-operation' => 'अज्ञात चेकआउट ऑपरेशन',
        ],

        'customer-addresses' => [
            'token-required' => 'ग्राहक पते प्राप्त करने के लिए टोकन आवश्यक है',
            'invalid-or-expired-token' => 'अमान्य या समाप्त टोकन',
            'token-validation-failed' => 'टोकन सत्यापन विफल',
        ],

        'product' => [
            'type' => 'उत्पाद प्रकार',
            'attribute-family' => 'विशेषता परिवार',
            'sku' => 'SKU',
            'name' => 'नाम',
            'description' => 'विवरण',
            'short-description' => 'संक्षिप्त विवरण',
            'status' => 'स्थिति',
            'new' => 'नया',
            'featured' => 'विशेष रुप से प्रदर्शित',
            'price' => 'कीमत',
            'special-price' => 'विशेष कीमत',
            'weight' => 'वजन',
            'cost' => 'लागत',
            'length' => 'लंबाई',
            'width' => 'चौड़ाई',
            'height' => 'ऊँचाई',
            'color' => 'रंग',
            'size' => 'आकार',
            'brand' => 'ब्रांड',
            'super-attributes' => 'सुपर विशेषताएँ',
        ],

        'compare-item' => [
            'id-required' => 'तुलना आइटम ID आवश्यक है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/shop/compare-items/1" या संख्यात्मक ID जैसे IRI प्रारूप की अपेक्षा है',
            'not-found' => 'तुलना आइटम नहीं मिला',
            'product-id-required' => 'उत्पाद ID आवश्यक है',
            'customer-id-required' => 'ग्राहक ID आवश्यक है',
            'product-not-found' => 'उत्पाद नहीं मिला',
            'customer-not-found' => 'ग्राहक नहीं मिला',
            'already-exists' => 'यह उत्पाद पहले से ही आपकी तुलना सूची में है',
        ],

        'downloadable-product' => [
            'download-link-not-found' => 'डाउनलोड लिंक नहीं मिला या समाप्त हो गया है',
            'purchased-link-not-found' => 'खरीदा गया लिंक नहीं मिला',
            'file-not-found' => 'फ़ाइल नहीं मिली',
            'download-successful' => 'फ़ाइल डाउनलोड के लिए तैयार है',
            'token-required' => 'डाउनलोड टोकन आवश्यक है',
            'invalid-token' => 'डाउनलोड टोकन अमान्य या समाप्त है',
            'token-expired' => 'डाउनलोड टोकन समाप्त हो गया है। कृपया एक नया बनाएं',
            'access-denied' => 'एक्सेस अस्वीकृत: आपके पास इस फ़ाइल को डाउनलोड करने की अनुमति नहीं है',
            'redirect-external-url' => 'बाहरी डाउनलोड URL पर पुनः निर्देशन',
            'file-error' => 'आपके डाउनलोड अनुरोध को संसाधित करते समय एक त्रुटि हुई',
            'unauthorized-access' => 'डाउनलोड संसाधन के लिए अनुपलब्ध प्रवेश',
        ],
    ],

    'integration' => [
        'menu' => [
            'title' => 'एकीकरण',
            'tokens' => 'टोकन',
        ],

        'history' => [
            'menu' => [
                'title' => 'इतिहास',
            ],

            'acl' => [
                'title' => 'एपीआई परिवर्तन इतिहास',
                'delete' => 'इतिहास हटाएँ',
            ],

            'index' => [
                'title' => 'एपीआई परिवर्तन इतिहास',
                'info' => 'एडमिन एपीआई के माध्यम से किया गया प्रत्येक निर्माण, अपडेट और डिलीट, इसे किसने किया, किस टोकन के साथ किया और क्या बदल गया।',
                'cleanup-btn' => 'पुराने लॉग हटाएँ',
                'cleanup-days' => 'इतने दिनों से पुराने लॉग हटाएँ',
                'cleanup-confirm' => 'दिए गए दिनों की संख्या से पुराना सारा इतिहास हटा दें? इसे असंपादित नहीं किया जा सकता है।',
            ],

            'view' => [
                'title' => 'परिवर्तन',
                'back-btn' => 'वापस',
                'admin' => 'व्यवस्थापक',
                'token' => 'टोकन',
                'action' => 'कार्रवाई',
                'resource' => 'संसाधन',
                'method' => 'विधि',
                'ip' => 'आईपी पता',
                'date' => 'दिनांक',
                'version' => 'संस्करण',
                'url' => 'एंडपॉइंट',
                'request-details' => 'विवरण का अनुरोध करें',
                'changes' => 'परिवर्तन',
                'field' => 'मैदान',
                'old' => 'पुराना मान',
                'new' => 'नया मूल्य',
                'no-field-changes' => 'इस प्रविष्टि के लिए कोई फ़ील्ड-स्तरीय परिवर्तन दर्ज नहीं किया गया।',
                'same-request' => 'उसी अनुरोध में अन्य परिवर्तन',
                'version-chain' => 'इस रिकॉर्ड का संस्करण इतिहास',
            ],

            'datagrid' => [
                'id' => 'आईडी',
                'date' => 'दिनांक',
                'admin' => 'व्यवस्थापक',
                'token' => 'टोकन',
                'action' => 'कार्रवाई',
                'operation' => 'ऑपरेशन',
                'resource' => 'संसाधन',
                'version' => 'संस्करण',
                'method' => 'विधि',
                'ip' => 'आईपी',
                'view' => 'देखें',
                'delete' => 'हटाएँ',
            ],

            'events' => [
                'created' => 'बनाया गया',
                'updated' => 'अद्यतन किया गया',
                'deleted' => 'हटा दिया गया',
            ],

            'deleted' => ':count इतिहास रिकॉर्ड हटा दिया गया।',
            'cleanup-input-required' => 'साफ़ करने के लिए दिनों की संख्या या एक तिथि प्रदान करें।',
        ],

        'acl' => [
            'title' => 'एकीकरण',
            'create' => 'एकीकरण बनाएँ',
            'edit' => 'एकीकरण संपादित करें',
            'delete' => 'एकीकरण टोकन निरस्त करें',
            'generate' => 'एकीकरण टोकन जनरेट करें',
            'regenerate' => 'एकीकरण टोकन पुनर्जीवित करें',
        ],

        'index' => [
            'title' => 'एकीकरण',
            'create-btn' => 'एकीकरण बनाएँ',
        ],

        'create' => [
            'title' => 'एकीकरण बनाएँ',
            'save-btn' => 'सहेजें',
            'back-btn' => 'वापस',
        ],

        'edit' => [
            'title' => 'एकीकरण संपादित करें',
            'save-btn' => 'सहेजें',
            'back-btn' => 'वापस',
            'generate-btn' => 'टोकन जनरेट करें',
            'regenerate-btn' => 'टोकन पुन: उत्पन्न करें',
            'revoke-btn' => 'टोकन निरस्त करें',
            'copy-btn' => 'प्रतिलिपि',
            'token-warning' => 'इस टोकन को अभी सहेजें - यह दोबारा नहीं दिखाया जाएगा।',
            'token-label' => 'टोकन',
            'not-generated' => 'अभी तक उत्पन्न नहीं हुआ',
            'masked' => '(संग्रहीत - पीढ़ी में केवल एक बार दिखाया गया)',
            'history-banner' => 'यह टोकन अब सक्रिय नहीं है.',
        ],

        'fields' => [
            'name' => 'नाम',
            'description' => 'विवरण',
            'assign-user' => 'उपयोगकर्ता असाइन करें',
            'permission-type' => 'अनुमति प्रकार',
            'access-control' => 'अभिगम नियंत्रण',
            'general' => 'सामान्य',
            'token-settings' => 'टोकन सेटिंग्स',
            'valid-till' => 'तक वैध',
            'rate-limit-per-minute' => 'दर सीमा (प्रति मिनट)',
            'rate-limit-per-day' => 'दर सीमा (प्रति दिन)',
            'never-expires' => 'कभी समाप्त नहीं होता',
            'expires-on' => 'पर समाप्त हो रहा है',
            'unlimited' => 'असीमित',
            'limit-to' => 'तक सीमित',
            'requests-per-minute' => 'अनुरोध/मिनट',
            'requests-per-day' => 'अनुरोध/दिन',
            'select-admin' => 'एक व्यवस्थापक चुनें',
            'no-available-admins' => 'कोई व्यवस्थापक उपलब्ध नहीं है - प्रत्येक व्यवस्थापक के पास पहले से ही एक सक्रिय टोकन है।',
            'same-as-web-hint' => 'टोकन निर्दिष्ट व्यवस्थापक की वर्तमान भूमिका अनुमतियों को लाइव प्रदर्शित करेगा।',
            'ip-allowlist' => 'आईपी अनुमति सूची',
            'ip-any' => 'कोई भी आईपी (डिफ़ॉल्ट)',
            'ip-restricted' => 'विशिष्ट आईपी तक सीमित',
            'ip-list-hint' => 'प्रति पंक्ति एक प्रविष्टि. IPv4, IPv6 और CIDR को सपोर्ट करता है (जैसे 10.0.0.0/24 या 2001:db8::/32)। सभी आईपी को अनुमति देने के लिए खाली छोड़ें।',
        ],

        'permission_type' => [
            'all' => 'सब',
            'custom' => 'कस्टम',
            'same_as_web' => 'वेब अनुमति के समान',
        ],

        'status' => [
            'draft' => 'ड्राफ्ट',
            'active' => 'सक्रिय',
            'revoked' => 'निरस्त किया गया',
            'regenerated' => 'पुनर्जीवित',
        ],

        'datagrid' => [
            'id' => 'आईडी',
            'name' => 'नाम',
            'admin' => 'व्यवस्थापक',
            'token' => 'टोकन',
            'status' => 'स्थिति',
            'permission-type' => 'अनुमति प्रकार',
            'expires-at' => 'तक वैध',
            'last-used-at' => 'अंतिम बार उपयोग किया गया',
            'created-at' => 'पर बनाया गया',
            'edit' => 'संपादित करें',
            'revoke' => 'निरस्त करें',
        ],

        'messages' => [
            'draft-created' => 'एकीकरण बनाया गया. इसका उपयोग शुरू करने के लिए टोकन जेनरेट करें।',
            'updated' => 'एकीकरण सफलतापूर्वक अद्यतन किया गया.',
            'generated' => 'टोकन जनरेट हुआ. इसे अभी कॉपी करें - यह दोबारा नहीं दिखाया जाएगा।',
            'regenerated' => 'टोकन पुनर्जीवित. अब नया टोकन कॉपी करें - यह दोबारा नहीं दिखाया जाएगा।',
            'revoked' => 'टोकन सफलतापूर्वक निरस्त कर दिया गया.',
            'generate-only-draft' => 'केवल ड्राफ्ट एकीकरण ही अपना टोकन जेनरेट कर सकते हैं।',
            'regenerate-only-active' => 'केवल सक्रिय टोकन ही पुन: उत्पन्न किये जा सकते हैं।',
            'cannot-edit-historic' => 'निरस्त या पुनर्जीवित टोकन संपादित नहीं किए जा सकते।',
            'already-inactive' => 'यह टोकन पहले से ही निष्क्रिय है.',
        ],

        'errors' => [
            'admin-has-token' => 'चयनित व्यवस्थापक के पास पहले से ही एक सक्रिय एकीकरण टोकन है।',
        ],

        'validation' => [
            'ip-invalid' => 'प्रत्येक अनुमत IP एक वैध IPv4 या IPv6 पता (CIDR नोटेशन समर्थित) होना चाहिए।',
            'cidr-prefix-invalid' => 'दिए गए आईपी संस्करण के लिए सीआईडीआर उपसर्ग अमान्य है।',
        ],

        'configuration' => [
            'api' => [
                'title' => 'एपीआई',
                'info' => 'बैगिस्टो एपीआई और इसके व्यवस्थापक मॉड्यूल के लिए सेटिंग्स।',
            ],
            'integration' => [
                'title' => 'एकीकरण',
                'info' => 'व्यवस्थापक एपीआई टोकन जारी करने के लिए उपयोग किए जाने वाले एपीआई एकीकरण प्लगइन को प्रबंधित करें।',
            ],
            'settings' => [
                'title' => 'मॉड्यूल सेटिंग्स',
                'info' => 'एपीआई एकीकरण प्लगइन को सक्षम या अक्षम करें। अक्षम होने पर, इसका साइडबार मेनू छिपा रहता है और इसके पेज 404 पर लौट आते हैं।',
                'enable' => 'एपीआई एकीकरण मॉड्यूल सक्षम करें',
            ],
        ],

        'emails' => [
            'generated' => [
                'subject' => 'एक नया एपीआई टोकन उत्पन्न हुआ: :name',
                'greeting' => 'आपके खाते पर अभी-अभी ":name" नामक एक एपीआई एकीकरण टोकन उत्पन्न हुआ था।',
            ],
            'regenerated' => [
                'subject' => 'आपका एपीआई टोकन पुनर्जीवित किया गया था: :name',
                'greeting' => '":name" नाम का एपीआई एकीकरण टोकन अभी पुनर्जीवित किया गया था। पिछले टोकन ने काम करना बंद कर दिया है - केवल नया ही मान्य है।',
            ],
            'revoked' => [
                'subject' => 'आपका एपीआई टोकन निरस्त कर दिया गया: :name',
                'greeting' => '":name" नामक एपीआई एकीकरण टोकन निरस्त कर दिया गया था। इसका उपयोग करने वाले किसी भी ग्राहक ने पहुंच खो दी है।',
            ],

            'details' => [
                'name' => 'टोकन नाम',
                'date' => 'दिनांक',
                'ip' => 'आईपी से',
            ],

            'revoke-hint' => 'यदि आपको इसकी उम्मीद नहीं थी, तो नीचे दिए गए बटन का उपयोग करके तुरंत टोकन रद्द करें।',
            'revoke-btn' => 'इस टोकन को निरस्त करें',
            'revoke-expiry' => 'यह निरस्त लिंक 7 दिनों के लिए वैध है। उसके बाद, टोकन प्रबंधित करने के लिए व्यवस्थापक पैनल में साइन इन करें।',
            'no-action' => 'किसी कार्रवाई की आवश्यकता नहीं है - यह ईमेल केवल एक पुष्टिकरण है।',
        ],

        'revoke-confirmation' => [
            'title' => 'एपीआई टोकन निरस्त करें',
            'success-title' => 'टोकन निरस्त कर दिया गया',
            'success-message' => 'टोकन ":name" निरस्त कर दिया गया है। इसका उपयोग करने वाला कोई भी ग्राहक तुरंत पहुंच खो देता है।',
            'already-inactive-title' => 'टोकन पहले से ही निष्क्रिय है',
            'already-inactive-message' => 'टोकन ":name" पहले ही निरस्त कर दिया गया था या पुनर्जीवित किया गया था। किसी और कार्रवाई की आवश्यकता नहीं है.',
        ],

        'confirm' => [
            'generate' => [
                'title' => 'टोकन जनरेट करें',
                'message' => 'अभी टोकन जनरेट करें? सादा पाठ केवल एक बार दिखाया जाएगा - पृष्ठ छोड़ने से पहले इसे कॉपी करें।',
            ],
            'regenerate' => [
                'title' => 'टोकन पुन: उत्पन्न करें',
                'message' => 'टोकन पुनः जनरेट करें? पुराना टोकन तुरंत काम करना बंद कर देगा और नया प्लेनटेक्स्ट केवल एक बार दिखाया जाएगा।',
            ],
            'revoke' => [
                'title' => 'टोकन निरस्त करें',
                'message' => 'यह टोकन रद्द करें? इसका उपयोग करने वाला कोई भी ग्राहक तुरंत पहुंच खो देगा। इस एक्शन को वापस नहीं किया जा सकता।',
            ],
        ],
    ],
];
