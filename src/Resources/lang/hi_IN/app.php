<?php

return [

    'graphql' => [
        'headers' => [
            'unknown-channel' => 'X-Channel हेडर में दिया गया चैनल ":code" मौजूद नहीं है।',
            'storefront-key-required' => 'X-STOREFRONT-KEY हेडर आवश्यक है',
            'storefront-key-invalid' => 'अमान्य स्टोरफ्रंट कुंजी',
            'storefront-key-rate-limited' => 'दर सीमा पार हो गई। कृपया :seconds सेकंड बाद पुनः प्रयास करें',
        ],

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

            'file-token-invalid' => 'अपलोड की गई फ़ाइल का संदर्भ अमान्य है या समाप्त हो गया है। फ़ाइल फिर से अपलोड करें।',
            'file-token-forbidden' => 'यह अपलोड की गई फ़ाइल आपकी कार्ट से संबंधित नहीं है।',
            'customizable-option-required' => 'अनुकूलन योग्य विकल्प ":label" आवश्यक है।',
            'customizable-file-option-invalid' => 'चयनित विकल्प इस उत्पाद के लिए फ़ाइल विकल्प नहीं है।',
            'customizable-file-invalid' => 'फ़ाइल अनुपलब्ध है, उसका एक्सटेंशन असमर्थित है, या वह बहुत बड़ी है।',
            'cart-item-id-required' => 'कार्ट आइटम ID आवश्यक है',
            'item-ids-required' => 'आइटम ID सरणी आवश्यक है',

            'event-booking-quantity-not-changeable' => 'इवेंट बुकिंग उत्पाद की मात्रा बदली नहीं जा सकती। मात्रा टिकट चयन से निर्धारित होती है।',
            'appointment-booking-quantity-not-changeable' => 'अपॉइंटमेंट बुकिंग उत्पाद की मात्रा बदली नहीं जा सकती।',
            'coupon-code-required' => 'कूपन कोड आवश्यक है',
            'address-data-required' => 'देश, राज्य और पिनकोड आवश्यक हैं',
            'grouped-qty-required' => 'समूहीकृत उत्पाद के लिए चयनित मात्राएँ आवश्यक हैं। groupedQty को JSON स्ट्रिंग के रूप में भेजें, उदा. {"101":2,"102":1}।',
            'grouped-qty-must-include-all' => 'समूहीकृत उत्पाद के लिए सभी संबद्ध उत्पादों की मात्राएँ आवश्यक हैं। अनुपलब्ध ID: :ids।',
            'grouped-qty-invalid-associated' => 'समूहीकृत उत्पाद की मात्राओं में अमान्य संबद्ध उत्पाद ID हैं: :ids।',
            'grouped-qty-invalid-quantity' => 'संबद्ध उत्पाद ID :id के लिए अमान्य मात्रा दी गई है। मात्रा एक गैर-ऋणात्मक पूर्णांक होनी चाहिए।',
            'bundle-qty-not-changeable' => 'बंडल विकल्प ":option" की मात्रा बदली नहीं जा सकती। अनुमत मात्रा :qty है।',

            'add-product-failed' => 'उत्पाद को कार्ट में जोड़ने में विफल',
            'update-item-failed' => 'कार्ट आइटम अपडेट करने में विफल',
            'remove-item-failed' => 'कार्ट आइटम हटाने में विफल',
            'remove-coupon-failed' => 'कूपन हटाने में विफल',
            'coupon-applied' => 'कूपन सफलतापूर्वक लागू किया गया',
            'coupon-removed' => 'कूपन सफलतापूर्वक हटाया गया',
            'move-to-wishlist-failed' => 'आइटम को इच्छा-सूची में ले जाने में विफल',
            'estimate-shipping-failed' => 'शिपिंग अनुमान लगाने में विफल',

            'product-added-successfully' => 'उत्पाद सफलतापूर्वक कार्ट में जोड़ा गया',
            'cart-item-updated-successfully' => 'कार्ट आइटम सफलतापूर्वक अपडेट किया गया',
            'guest-cart-merged' => 'अतिथि कार्ट सफलतापूर्वक विलय किया गया',
            'using-authenticated-cart' => 'प्रमाणित ग्राहक कार्ट का उपयोग किया जा रहा है',
            'cart-item-not-found' => 'कार्ट आइटम नहीं मिला',
            'new-guest-cart-created' => 'अद्वितीय सत्र टोकन के साथ नई अतिथि कार्ट बनाई गई',
            'select-items-to-remove' => 'कृपया हटाने के लिए आइटम चुनें',
            'select-items-to-move-wishlist' => 'कृपया इच्छा-सूची में ले जाने के लिए आइटम चुनें',
            'invalid-or-expired-token' => 'कार्ट टोकन अमान्य या समाप्त हो गया है। कृपया एक नई कार्ट बनाएँ।',
            'invalid-token-of-login-user' => 'लॉगिन उपयोगकर्ता टोकन अमान्य है।',
            'booking-note-required' => 'टेबल बुकिंग के लिए booking.note आवश्यक है।',
            'booking-date-required' => 'जब booking.slot एक स्वरूपित समय सीमा हो, तब booking.date आवश्यक है।',
            'booking-qty-required' => 'इवेंट बुकिंग के लिए booking.qty आवश्यक है।',
            'booking-ticket-qty-invalid' => 'इवेंट टिकट की मात्रा एक गैर-ऋणात्मक संख्या होनी चाहिए।',
            'booking-ticket-required' => 'इवेंट बुकिंग के लिए कम से कम एक टिकट की मात्रा चुनें।',
            'booking-slot-unavailable' => 'चयनित स्लॉट दी गई तिथि के लिए उपलब्ध नहीं है।',
            'invalid-quantity' => 'मात्रा कम से कम 1 होनी चाहिए',
            'product-id-required' => 'उत्पाद ID आवश्यक है',
            'shipping-address-required' => 'शिपिंग का अनुमान लगाने के लिए देश और पोस्टकोड आवश्यक हैं',
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
            'unauthenticated' => 'अप्रमाणित। इस क्रिया को करने के लिए कृपया लॉगिन करें',
        ],

        'forgot-password' => [
            'invalid-operation' => 'अमान्य ऑपरेशन',
            'invalid-input-data' => 'अमान्य इनपुट डेटा',
            'email-required' => 'ईमेल आवश्यक है',
            'reset-link-sent' => 'रीसेट लिंक सफलतापूर्वक आपके ईमेल पर भेजा गया',
            'email-not-found' => 'ईमेल पता नहीं मिला',
            'throttled' => 'बहुत अधिक रीसेट प्रयास। कृपया पुनः प्रयास करने से पहले प्रतीक्षा करें।',
            'error-sending-reset-link' => 'रीसेट लिंक भेजते समय एक त्रुटि हुई',
        ],

        'reset-password' => [
            'failed' => 'पासवर्ड रीसेट नहीं किया जा सका। कृपया पुनः प्रयास करें।',
        ],

        'rate-limit' => [
            'too-many-attempts' => 'बहुत अधिक प्रयास। कृपया :seconds सेकंड बाद पुनः प्रयास करें।',
        ],

        'logout' => [
            'invalid-operation' => 'अमान्य ऑपरेशन',
            'invalid-input-data' => 'अमान्य इनपुट डेटा',
            'token-required' => 'टोकन आवश्यक है',
            'invalid-token-format' => 'अमान्य टोकन प्रारूप',
            'logged-out-successfully' => 'सफलतापूर्वक लॉग आउट किया गया',
            'token-not-found-or-expired' => 'टोकन नहीं मिला या पहले ही समाप्त हो गया है',
            'error-during-logout' => 'लॉगआउट के दौरान त्रुटि',
            'unauthenticated' => 'अप्रमाणित। इस क्रिया को करने के लिए कृपया लॉगिन करें',
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
            'field-required' => ':field फ़ील्ड आवश्यक है',
        ],

        'customer-profile' => [
            'authentication-required' => 'प्रमाणीकरण टोकन आवश्यक है। कृपया क्वेरी इनपुट में टोकन प्रदान करें',
            'invalid-token' => 'अमान्य या समाप्त टोकन',
            'profile-updated' => 'ग्राहक प्रोफ़ाइल सफलतापूर्वक अपडेट की गई',
        ],

        'customer' => [
            'password-mismatch' => 'पासवर्ड और पुष्टि पासवर्ड मेल नहीं खाते',
            'confirm-password-required' => 'पासवर्ड बदलते समय पुष्टि पासवर्ड आवश्यक है',
            'current-password-required' => 'अपना पासवर्ड बदलने के लिए वर्तमान पासवर्ड आवश्यक है',
            'current-password-incorrect' => 'आपके द्वारा दर्ज किया गया वर्तमान पासवर्ड गलत है',
            'verify-email-to-login' => 'आपका खाता बना दिया गया है। लॉग इन करने के लिए कृपया अपना ईमेल पता सत्यापित करें।',
            'delete-password-required' => 'अपना खाता हटाने के लिए आपका पासवर्ड आवश्यक है',
            'delete-password-incorrect' => 'आपके द्वारा दर्ज किया गया पासवर्ड गलत है',
            'delete-has-active-orders' => 'लंबित या प्रोसेसिंग ऑर्डर होने पर आपका खाता हटाया नहीं जा सकता',
            'unauthenticated' => 'अप्रमाणित। इस क्रिया को करने के लिए कृपया लॉगिन करें',
            'id-required' => 'ग्राहक ID आवश्यक है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/admin/customers/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
            'not-found' => 'ग्राहक नहीं मिला',
            'phone-special-chars-not-allowed' => 'मोबाइल नंबर में केवल अंक हो सकते हैं। विशेष वर्णों की अनुमति नहीं है',
            'email-already-taken' => 'यह ईमेल पता पहले से किसी अन्य खाते में पंजीकृत है',
            'invalid-gender' => 'अमान्य लिंग मान ":gender"। अनुमत मान हैं: :valid',
        ],

        'product-review' => [
            'id-required' => 'उत्पाद समीक्षा ID आवश्यक है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/shop/reviews/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
            'not-found' => 'ID ":id" वाली उत्पाद समीक्षा नहीं मिली',
            'item-not-found' => 'आइटम ":id" नहीं मिला।',
            'product-id-required' => 'उत्पाद ID आवश्यक है',
            'product-not-found' => 'उत्पाद नहीं मिला',
            'rating-invalid' => 'रेटिंग 1 और 5 के बीच होनी चाहिए',
            'title-required' => 'समीक्षा शीर्षक आवश्यक है',
            'comment-required' => 'समीक्षा टिप्पणी आवश्यक है',
            'review-disabled' => 'उत्पाद समीक्षाएँ वर्तमान में अक्षम हैं',
            'guest-review-disabled' => 'अतिथि समीक्षाओं की अनुमति नहीं है। समीक्षा सबमिट करने के लिए कृपया लॉगिन करें',
            'owner-required' => 'अपनी समीक्षा प्रबंधित करने के लिए कृपया लॉगिन करें',
            'not-owned' => 'यह समीक्षा आपके द्वारा नहीं लिखी गई थी और इसे संशोधित या हटाया नहीं जा सकता',
            'invalid-attachments' => 'अमान्य अनुलग्नक प्रारूप।',
            'invalid-product-id-format' => 'अमान्य उत्पाद ID प्रारूप। "/api/shop/products/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
        ],

        'customer-review' => [
            'id-required' => 'ग्राहक समीक्षा ID आवश्यक है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/shop/customer-reviews/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
            'not-found' => 'ID ":id" वाली ग्राहक समीक्षा नहीं मिली',
        ],

        'newsletter' => [
            'error-during-subscription' => 'इस समय सदस्यता पूरी नहीं की जा सकी। कृपया बाद में पुनः प्रयास करें',
        ],

        'contact-us' => [
            'success' => 'आपकी पूछताछ सफलतापूर्वक सबमिट कर दी गई है। हम जल्द ही आपसे संपर्क करेंगे',
            'failed' => 'इस समय आपकी पूछताछ भेजी नहीं जा सकी। कृपया बाद में पुनः प्रयास करें',
            'invalid-input' => 'अमान्य संपर्क फ़ॉर्म डेटा',
        ],

        'customer-order' => [
            'id-required' => 'ग्राहक ऑर्डर ID आवश्यक है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/shop/customer-orders/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
            'not-found' => 'ID ":id" वाला ग्राहक ऑर्डर नहीं मिला',
        ],

        'return' => [
            'not-found' => 'वापसी अनुरोध नहीं मिला।',
            'disabled' => 'इस स्टोर के लिए वापसी सक्षम नहीं है।',
            'invalid-input' => 'वापसी विवरण अनुपलब्ध या अमान्य हैं।',
            'invalid-resolution-type' => 'समाधान प्रकार "return" या "cancel_items" होना चाहिए।',
            'agreement-required' => 'आपको वापसी नीति स्वीकार करनी होगी।',
            'invalid-order' => 'चयनित ऑर्डर मान्य नहीं है।',
            'invalid-item' => 'चयनित आइटम वापसी के लिए पात्र नहीं है।',
            'qty-exceeds' => 'अनुरोधित मात्रा वापसी योग्य मात्रा से अधिक है।',
            'invalid-package-condition' => 'पैकेज की स्थिति "open" या "packed" होनी चाहिए।',
            'invalid-image' => 'अपलोड की गई एक या अधिक फ़ाइलों का प्रकार असमर्थित है।',
            'invalid-custom-field' => 'अज्ञात कस्टम फ़ील्ड ":id"।',
            'custom-field-required' => 'फ़ील्ड ":field" आवश्यक है।',
            'invalid-custom-field-value' => '":field" का मान अनुमत विकल्पों में से एक नहीं है।',
            'created' => 'वापसी अनुरोध सफलतापूर्वक बनाया गया।',
            'canceled' => 'वापसी अनुरोध सफलतापूर्वक रद्द किया गया।',
            'already-canceled' => 'वापसी अनुरोध पहले ही रद्द किया जा चुका है।',
            'cancel-not-allowed' => 'यह वापसी अनुरोध रद्द नहीं किया जा सकता।',
            'close-not-allowed' => 'यह वापसी अनुरोध बंद नहीं किया जा सकता।',
            'reopened' => 'वापसी अनुरोध सफलतापूर्वक फिर से खोला गया।',
            'reopen-not-allowed' => 'यह वापसी अनुरोध फिर से नहीं खोला जा सकता।',
            'closed' => 'वापसी अनुरोध सफलतापूर्वक बंद किया गया।',
            'message-sent' => 'संदेश सफलतापूर्वक भेजा गया।',
            'attachment-not-found' => 'संदेश अनुलग्नक नहीं मिला।',
        ],

        'eu-withdrawal' => [
            'not-found' => 'निकासी घोषणा नहीं मिली।',
            'order-not-found' => 'ऑर्डर नहीं मिला या निकासी के लिए पात्र नहीं है।',
            'too-many-attempts' => 'बहुत अधिक प्रयास। कृपया :seconds सेकंड बाद पुनः प्रयास करें।',
            'created' => 'निकासी घोषणा सफलतापूर्वक दर्ज की गई।',
        ],

        'customer-order-shipment' => [
            'id-required' => 'शिपमेंट ID आवश्यक है',
            'not-found' => 'ID ":id" वाला शिपमेंट नहीं मिला या इस ग्राहक से संबंधित नहीं है',
            'item-not-found' => 'ID ":id" वाला शिपमेंट आइटम नहीं मिला या इस ग्राहक से संबंधित नहीं है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/shop/customer-order-shipments/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
        ],

        'cancel-order' => [
            'order-id-required' => 'ऑर्डर रद्द करने के लिए ऑर्डर ID आवश्यक है',
            'not-found' => 'ID ":id" वाला ऑर्डर नहीं मिला या इस ग्राहक से संबंधित नहीं है',
            'cancel-success' => 'ऑर्डर सफलतापूर्वक रद्द कर दिया गया है',
            'cancel-failed' => 'ऑर्डर रद्द नहीं किया जा सकता। हो सकता है कि यह पहले ही प्रोसेस, शिप या रद्द किया जा चुका हो',
        ],

        'reorder' => [
            'order-id-required' => 'पुनः ऑर्डर करने के लिए ऑर्डर ID आवश्यक है',
            'not-found' => 'ID ":id" वाला ऑर्डर नहीं मिला या इस ग्राहक से संबंधित नहीं है',
            'reorder-success' => 'आपके पिछले ऑर्डर के :count आइटम आपकी कार्ट में जोड़ दिए गए हैं',
            'no-items-added' => 'कोई भी आइटम कार्ट में नहीं जोड़ा जा सका। उत्पाद स्टॉक में नहीं हो सकते या अनुपलब्ध हो सकते हैं',
        ],

        'customer-downloadable-product' => [
            'id-required' => 'ग्राहक डाउनलोड योग्य उत्पाद ID आवश्यक है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/shop/customer-downloadable-products/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
            'not-found' => 'ID ":id" वाला ग्राहक डाउनलोड योग्य उत्पाद नहीं मिला',

            'download' => [
                'unauthenticated' => 'अनधिकृत: ग्राहक प्रमाणीकरण आवश्यक है।',
                'not-found' => 'डाउनलोड योग्य उत्पाद नहीं मिला।',
                'pending' => 'डाउनलोड लंबित है। कृपया ऑर्डर का चालान बनने तक प्रतीक्षा करें।',
                'limit-exceeded' => 'डाउनलोड सीमा पार हो गई।',
                'no-downloads-remaining' => 'अब और डाउनलोड उपलब्ध नहीं हैं।',
                'file-not-found' => 'फ़ाइल नहीं मिली।',
                'link-not-found' => 'डाउनलोड योग्य लिंक नहीं मिला।',
            ],
        ],

        'customer-invoice' => [
            'id-required' => 'ग्राहक चालान ID आवश्यक है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/shop/customer-invoices/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
            'not-found' => 'ID ":id" वाला ग्राहक चालान नहीं मिला',
        ],

        'auth' => [
            'no-token-provided' => 'कोई प्रमाणीकरण टोकन प्रदान नहीं किया गया। कृपया Authorization हेडर में "Bearer <token>" के रूप में या input.token फ़ील्ड में टोकन प्रदान करें',
            'invalid-or-expired-token' => 'अमान्य या समाप्त टोकन',
            'request-not-found' => 'संदर्भ में अनुरोध नहीं मिला',
            'token-required' => 'Authentication token is required. Please provide the token either in the GraphQL mutation input field or in the Authorization header as "Bearer <token>"',
            'unknown-resource' => 'अज्ञात संसाधन',
            'cannot-update-other-profile' => 'अनधिकृत: किसी अन्य ग्राहक प्रोफ़ाइल को अपडेट नहीं कर सकते',
            'refresh-token-invalid' => 'रिफ्रेश टोकन अमान्य है या उसकी अवधि समाप्त हो गई है। कृपया फिर से लॉगिन करें।',
            'too-many-login-attempts' => 'बहुत अधिक लॉगिन प्रयास। कृपया :seconds सेकंड बाद पुनः प्रयास करें।',
            'too-many-refresh-requests' => 'बहुत अधिक टोकन रिफ़्रेश। कृपया :seconds सेकंड बाद पुनः प्रयास करें।',
            'rate-limit-exceeded' => 'बहुत अधिक प्रयास।',
            'access-token-expired' => 'एक्सेस टोकन की अवधि समाप्त हो गई है। जारी रखने के लिए इसे रिफ्रेश करें।',
            'logged-out-all' => 'आप सभी सत्रों से लॉग आउट हो गए हैं।',
            'session-not-found' => 'सत्र नहीं मिला।',
            'session-revoked' => 'सत्र रद्द कर दिया गया है।',
        ],

        'upload' => [
            'invalid-base64' => 'अमान्य base64 एन्कोडेड छवि डेटा',
            'size-exceeds-limit' => 'छवि का आकार 5MB से अधिक नहीं होना चाहिए',
            'invalid-format' => 'अमान्य छवि प्रारूप। कृपया डेटा URI स्कीम के साथ base64 एन्कोडेड छवि प्रदान करें (data:image/jpeg;base64,...)',
            'failed' => 'छवि अपलोड विफल',
        ],

        'attribute' => [
            'code-already-exists' => 'विशेषता कोड पहले से मौजूद है',
            'option-id-required' => 'विशेषता विकल्पों को सीधे क्वेरी करते समय "attributeId" पैरामीटर आवश्यक है',
            'id-required' => 'विशेषता ID आवश्यक है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/shop/attributes/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
            'not-found' => 'विशेषता नहीं मिली',
        ],

        'attribute-option' => [
            'id-required' => 'विशेषता विकल्प ID आवश्यक है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/shop/attribute-options/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
            'not-found' => 'विशेषता विकल्प नहीं मिला',
        ],

        'channel' => [
            'id-required' => 'चैनल ID आवश्यक है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/shop/channels/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
            'not-found' => 'चैनल नहीं मिला',
        ],

        'country-state' => [
            'id-required' => 'देश राज्य ID आवश्यक है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/shop/country-states/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
            'not-found' => 'देश राज्य नहीं मिला',
            'country-id-required' => 'देश ID आवश्यक है',
        ],

        'country' => [
            'id-required' => 'देश ID आवश्यक है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/shop/countries/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
            'not-found' => 'देश नहीं मिला',
        ],

        'login' => [
            'invalid-credentials' => 'अमान्य ईमेल या पासवर्ड',
            'account-suspended' => 'आपका खाता निलंबित कर दिया गया है',
            'account-inactive' => 'आपका सक्रियण व्यवस्थापक स्वीकृति की प्रतीक्षा कर रहा है',
            'email-not-verified' => 'पहले अपना ईमेल खाता सत्यापित करें।',
            'successful' => 'आपने सफलतापूर्वक लॉग इन कर लिया है',
            'invalid-request' => 'अमान्य लॉगिन अनुरोध',
        ],

        'social-login' => [
            'signed-in' => 'सफलतापूर्वक साइन इन किया गया।',
            'token-required' => 'एक सोशल लॉगिन टोकन आवश्यक है।',
            'invalid-token' => 'सोशल लॉगिन टोकन अमान्य या समाप्त हो गया है। कृपया पुनः प्रयास करें।',
            'google-not-configured' => 'इस स्टोर के लिए Google साइन-इन कॉन्फ़िगर नहीं है।',
            'wrong-audience' => 'यह टोकन किसी अन्य ऐप के लिए जारी किया गया था।',
            'email-required' => 'प्रदाता ने कोई ईमेल पता साझा नहीं किया। कृपया इसके बजाय ईमेल से साइन अप करें।',
            'account-inactive' => 'आपका सक्रियण व्यवस्थापक स्वीकृति की प्रतीक्षा कर रहा है',
            'provider-not-supported' => 'यह सोशल लॉगिन प्रदाता समर्थित नहीं है।',
            'provider-disabled' => 'यह सोशल लॉगिन प्रदाता सक्षम नहीं है।',
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
            'order-placed' => 'ऑर्डर सफलतापूर्वक दिया गया',
            'order-redirect-required' => 'इस भुगतान विधि में खरीदार को गेटवे पर भुगतान पूरा करना होता है। उन्हें redirectUrl पर भेजें; गेटवे द्वारा भुगतान की पुष्टि होने पर ऑर्डर बनाया जाता है।',
            'order-creation-failed' => 'ऑर्डर निर्माण विफल: ऑर्डर ID शून्य है या ऑर्डर सहेजा नहीं गया',
            'order-retrieval-failed' => 'बनाए गए ऑर्डर को प्राप्त करने में विफल',
            'order-creation-error' => 'ऑर्डर बनाने में विफल',
            'cart-empty' => 'कार्ट खाली है',
            'account-suspended' => 'आपका खाता निलंबित कर दिया गया है। कृपया सहायता से संपर्क करें।',
            'account-inactive' => 'आपका खाता निष्क्रिय है। कृपया सहायता से संपर्क करें।',
            'minimum-order-not-met' => 'न्यूनतम ऑर्डर राशि :amount है',
            'guest-checkout-not-allowed' => 'चेकआउट करने के लिए कृपया लॉग इन करें। इस कार्ट के लिए अतिथि चेकआउट उपलब्ध नहीं है।',
            'email-required' => 'ऑर्डर निर्माण के लिए ईमेल पता आवश्यक है',
            'unknown-operation' => 'अज्ञात चेकआउट ऑपरेशन',
        ],

        'customer-addresses' => [
            'token-required' => 'ग्राहक पते प्राप्त करने के लिए टोकन आवश्यक है',
            'invalid-or-expired-token' => 'अमान्य या समाप्त टोकन',
            'token-validation-failed' => 'टोकन सत्यापन विफल',
        ],

        'product' => [
            'not-found' => 'उत्पाद नहीं मिला',
            'not-found-with-sku' => 'इस SKU वाला कोई उत्पाद नहीं मिला',
            'not-found-with-url-key' => 'इस URL कुंजी वाला कोई उत्पाद नहीं मिला',
            'parameters-required' => 'निम्नलिखित पैरामीटर में से कम से कम एक प्रदान किया जाना चाहिए: "sku", "id", "urlKey"',
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
            'id-required' => 'उत्पाद ID आवश्यक है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/shop/products/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
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
            'unauthenticated' => 'अनधिकृत: ग्राहक प्रमाणीकरण आवश्यक है',
            'link-id-required' => 'downloadableLinkPurchasedId आवश्यक है',
            'link-not-found' => 'डाउनलोड योग्य लिंक नहीं मिला',
            'not-owner' => 'अनधिकृत: यह डाउनलोड लिंक आपसे संबंधित नहीं है',
            'pending' => 'डाउनलोड लंबित है। कृपया ऑर्डर का चालान बनने तक प्रतीक्षा करें।',
            'limit-exceeded' => 'डाउनलोड सीमा पार हो गई',
            'no-downloads-left' => 'अब और डाउनलोड उपलब्ध नहीं हैं',
        ],

        'booking-slot' => [
            'id-required' => 'बुकिंग उत्पाद id आवश्यक है।',
            'date-required' => 'तिथि आवश्यक है।',
            'product-not-found' => 'बुकिंग उत्पाद नहीं मिला।',
            'invalid-type' => 'यह बुकिंग प्रकार समर्थित नहीं है।',
            'invalid-time-range' => 'अमान्य समय सीमा।',
            'invalid-time-format' => 'अमान्य समय प्रारूप।',
        ],

        'resolver' => [
            'no-resolve-info' => 'संदर्भ में GraphQL ResolveInfo उपलब्ध नहीं है। मॉडल क्लास निर्धारित नहीं की जा सकती।',
            'unknown-resource-class' => 'GraphQL रिटर्न प्रकार से संसाधन क्लास निर्धारित करने में असमर्थ',
        ],

        'docs' => [
            'openapi-unavailable' => 'OpenAPI Factory को इंस्टैंशिएट नहीं किया जा सका',
        ],

        'locale' => [
            'id-required' => 'लोकेल ID आवश्यक है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/shop/locales/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
            'not-found' => 'लोकेल नहीं मिला',
        ],

        'category' => [
            'id-required' => 'श्रेणी ID आवश्यक है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/shop/categories/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
            'not-found' => 'श्रेणी नहीं मिली',
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
            'delete-all-success' => 'सभी तुलना आइटम सफलतापूर्वक हटा दिए गए हैं',
        ],

        'wishlist' => [
            'id-required' => 'इच्छा-सूची आइटम ID आवश्यक है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/shop/wishlists/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
            'not-found' => 'इच्छा-सूची आइटम नहीं मिला',
            'product-id-required' => 'उत्पाद ID आवश्यक है',
            'customer-id-required' => 'ग्राहक ID आवश्यक है',
            'product-not-found' => 'उत्पाद नहीं मिला',
            'product-disabled' => 'यह उत्पाद वर्तमान में अक्षम है',
            'customer-not-found' => 'ग्राहक नहीं मिला',
            'already-exists' => 'यह उत्पाद पहले से आपकी इच्छा-सूची में है',
            'added' => 'आइटम सफलतापूर्वक इच्छा-सूची में जोड़ा गया',
            'removed' => 'आइटम सफलतापूर्वक इच्छा-सूची से हटाया गया',
            'product-removed' => 'उत्पाद हटा दिया गया है',
            'wishlist-item-id-required' => 'इच्छा-सूची आइटम ID आवश्यक है',
            'invalid-quantity' => 'मात्रा 0 से अधिक होनी चाहिए',
            'move-to-cart-missing-options' => 'उत्पाद में आवश्यक विकल्प अनुपलब्ध हैं। कृपया इसे मैन्युअल रूप से कॉन्फ़िगर करें',
            'moved-to-cart-success' => 'आइटम सफलतापूर्वक कार्ट में ले जाया गया',
            'delete-all-success' => 'सभी इच्छा-सूची आइटम सफलतापूर्वक हटा दिए गए हैं',
        ],

        'gdpr' => [
            'disabled' => 'GDPR डेटा अनुरोध अक्षम हैं। कृपया व्यवस्थापक कॉन्फ़िगरेशन से GDPR सक्षम करें।',
            'unauthenticated' => 'GDPR डेटा अनुरोध प्रबंधित करने के लिए आपको लॉग इन होना चाहिए।',
            'not-found' => 'GDPR डेटा अनुरोध नहीं मिला',
            'type-required' => 'अनुरोध प्रकार आवश्यक है',
            'type-invalid' => 'अनुरोध प्रकार "delete" या "update" होना चाहिए',
            'message-required' => 'संदेश आवश्यक है',
            'raised' => 'आपका GDPR डेटा अनुरोध सफलतापूर्वक दर्ज किया गया है।',
            'revoked' => 'आपका GDPR डेटा अनुरोध सफलतापूर्वक वापस ले लिया गया है।',
            'revoke-not-allowed' => 'यह GDPR डेटा अनुरोध अब वापस नहीं लिया जा सकता।',
            'deleted' => 'आपका GDPR डेटा अनुरोध सफलतापूर्वक हटा दिया गया है।',
        ],

        'section' => [
            'id-required' => 'सेक्शन ID आवश्यक है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/shop/sections/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
            'not-found' => 'सेक्शन नहीं मिला',
        ],

        'install' => [
            'starting' => 'API Platform इंस्टॉलेशन शुरू हो रहा है...',
            'completed-success' => '✓ API Platform इंस्टॉलेशन सफलतापूर्वक पूरा हुआ!',
            'failed' => '✗ इंस्टॉलेशन विफल: ',

            'provider-already-registered' => 'ℹ Service Provider पहले से पंजीकृत है',
            'provider-not-registered' => '⚠ Service Provider को स्वचालित रूप से पंजीकृत नहीं किया जा सका। :provider को मैन्युअल रूप से bootstrap/providers.php में जोड़ें।',
            'provider-registered' => '✓ Service Provider bootstrap/providers.php में पंजीकृत किया गया',
            'provider-file-not-found' => 'फ़ाइल नहीं मिली: :file',
            'provider-permission-denied' => 'अनुमति अस्वीकृत: :file में लिखा नहीं जा सकता। कृपया फ़ाइल अनुमतियाँ जाँचें।',

            'config-already-exists' => 'ℹ कॉन्फ़िगरेशन फ़ाइल पहले से मौजूद है',
            'config-published' => '✓ API Platform कॉन्फ़िगरेशन config/ में प्रकाशित किया गया',
            'config-source-not-found' => 'स्रोत कॉन्फ़िग फ़ाइल नहीं मिली: :file',
            'config-permission-denied' => 'अनुमति अस्वीकृत: :directory में लिखा नहीं जा सकता। कृपया निर्देशिका अनुमतियाँ जाँचें।',

            'translatable-not-found' => 'ℹ TranslatableModel नहीं मिला - छोड़ा जा रहा है',
            'translatable-already-abstract' => 'ℹ TranslatableModel पहले से abstract है',
            'translatable-made-abstract' => '✓ TranslatableModel को abstract बनाया गया',
            'translatable-permission-denied' => 'अनुमति अस्वीकृत: :file में लिखा नहीं जा सकता। कृपया फ़ाइल अनुमतियाँ जाँचें।',

            'providers-already-registered' => 'ℹ API Platform प्रदाता पहले से पंजीकृत हैं',
            'providers-registered' => '✓ API Platform प्रदाता bootstrap/app.php में पंजीकृत किए गए',
            'providers-file-not-found' => 'फ़ाइल नहीं मिली: :file',
            'providers-permission-denied' => 'अनुमति अस्वीकृत: :file में लिखा नहीं जा सकता। कृपया फ़ाइल अनुमतियाँ जाँचें।',
            'providers-not-found' => 'bootstrap/app.php में Application निर्माण नहीं मिला',

            'assets-published' => '✓ पैकेज एसेट्स प्रकाशित किए गए',
            'running-migrations' => 'डेटाबेस माइग्रेशन चलाए जा रहे हैं...',
            'migrations-completed' => '✓ डेटाबेस माइग्रेशन पूरे हुए',
            'clearing-caches' => 'कैश साफ़ और अनुकूलित किए जा रहे हैं...',
            'caches-optimized' => '✓ कैश साफ़ और अनुकूलित किए गए',
            'generating-api-key' => 'API कुंजी जनरेट की जा रही है...',
            'api-key-generated' => '✓ API कुंजी सफलतापूर्वक जनरेट की गई',
            'api-key-already-exists' => 'ℹ डिफ़ॉल्ट स्टोर API कुंजी पहले से मौजूद है - जनरेशन छोड़ा जा रहा है',

            'migrations-error' => 'डेटाबेस माइग्रेशन विफल हुए।',
            'migrations-error-running' => 'डेटाबेस माइग्रेशन चलाने में त्रुटि: :error',
            'cache-clear-error' => 'कैश साफ़ करना विफल रहा।',
            'cache-optimize-error' => 'अनुकूलन विफल रहा।',
            'cache-error' => 'कैश साफ़ और अनुकूलित करने में त्रुटि: :error',
            'api-key-generation-error' => 'API कुंजी जनरेशन विफल हुआ।',
            'api-key-error' => 'API कुंजी जनरेट करने में त्रुटि: :error',

            'key-management-required' => ':action क्रिया के लिए --key आवश्यक है',
            'key-rotation-error' => 'कुंजी रोटेट करने में त्रुटि: :error',
            'key-deactivation-error' => 'कुंजी निष्क्रिय करने में त्रुटि: :error',
            'key-not-found' => 'API कुंजी नहीं मिली: :identifier',

            'maintenance-starting' => '🔄 API कुंजी रखरखाव शुरू हो रहा है...',
            'maintenance-complete' => '✅ API कुंजी रखरखाव पूरा हुआ',
            'cleanup-expired-keys' => '🧹 समाप्त कुंजियाँ साफ़ की जा रही हैं...',
            'cleanup-expired-none' => '   ℹ️ साफ़ करने के लिए कोई समाप्त कुंजी नहीं है',
            'invalidate-deprecated' => '⚠️ अप्रचलित कुंजियाँ अमान्य की जा रही हैं...',
            'invalidate-deprecated-none' => '   ℹ️ अमान्य करने के लिए कोई अप्रचलित कुंजी नहीं है',
            'notify-expiring' => '📧 समाप्ति सूचनाएँ भेजी जा रही हैं...',
            'notify-expiring-none' => '   ℹ️ किसी कुंजी के लिए सूचना की आवश्यकता नहीं है',

            'key-rotated-success' => '✅ कुंजी सफलतापूर्वक रोटेट की गई!',
            'key-deactivated-success' => '✅ कुंजी सफलतापूर्वक निष्क्रिय की गई!',
            'deactivation-cancelled' => 'निष्क्रियकरण रद्द किया गया।',
            'cleanup-cancelled' => 'सफ़ाई रद्द की गई।',

            'key-status-title' => 'कुंजी स्थिति: :name',
            'key-status-active' => 'सक्रिय: :status',
            'key-status-usable' => 'उपयोग योग्य: :status',
            'key-status-expired' => 'समाप्त: :status',
            'key-status-deprecated' => 'अप्रचलित: :status',
            'key-status-expires-at' => 'समाप्ति समय: :expires',
            'key-status-days-until-expiry' => 'समाप्ति तक दिन: :days',
            'key-status-last-used' => 'अंतिम उपयोग: :date',
            'key-status-rotated-from' => 'इससे रोटेट की गई: :key',
            'key-status-rotated-keys' => 'इससे रोटेट की गई कुंजियाँ: :count',

            'policy-compliance-summary' => 'API कुंजी रोटेशन नीति अनुपालन सारांश',
            'available-actions' => 'उपलब्ध क्रियाएँ: rotate, deactivate, cleanup, status, expiring, unused, summary',

            'asset-linked-success' => '✓ API Platform एसेट्स सफलतापूर्वक लिंक किए गए',
            'symlink-create-failed' => 'सिमलिंक नहीं बनाया जा सका, इसके बजाय एसेट्स कॉपी किए जा रहे हैं...',
            'asset-copy-warning' => 'चेतावनी: API Platform एसेट्स लिंक या कॉपी नहीं किए जा सके। मैन्युअल सेटअप की आवश्यकता हो सकती है।',
            'asset-copied-success' => '✓ API Platform एसेट्स सफलतापूर्वक कॉपी किए गए',

            'key-name-required' => 'स्टोरफ्रंट कुंजी का नाम खाली नहीं हो सकता।',
            'key-generated-success' => 'स्टोरफ्रंट कुंजी सफलतापूर्वक जनरेट की गई!',
            'key-details' => 'कुंजी विवरण:',
            'key-secure-warning' => 'इस कुंजी को सुरक्षित रखें! इसका उपयोग X-STOREFRONT-KEY हेडर में किया जाएगा।',
            'key-share-warning' => 'इस कुंजी को सार्वजनिक रूप से साझा न करें या संस्करण नियंत्रण में कमिट न करें।',

            'key-already-exists' => '\':name\' नाम वाली स्टोरफ्रंट कुंजी पहले से मौजूद है।',
            'cleanup-success' => '✅ :count समाप्त कुंजियाँ साफ़ की गईं',
            'confirm-cleanup' => 'इससे सभी समाप्त कुंजियाँ सॉफ़्ट-डिलीट हो जाएँगी। जारी रखें?',
            'confirm-deactivate' => 'क्या आप वाकई कुंजी :name को निष्क्रिय करना चाहते हैं?',

            'old-key' => 'पुरानी कुंजी: :name',
            'old-key-id' => 'पुरानी कुंजी ID: :id',
            'deprecation-date' => 'अप्रचलन तिथि: :date',
            'new-key' => 'नई कुंजी: :name',
            'new-key-id' => 'नई कुंजी ID: :id',
            'new-key-value' => 'नई कुंजी का मान: :key',
            'expires-at' => 'समाप्ति समय: :date',
            'invalid-action' => 'अमान्य क्रिया: :action',
            'key-active' => 'सक्रिय: :status',
            'key-usable' => 'उपयोग योग्य: :status',
            'key-expired' => 'समाप्त: :status',
            'key-deprecated' => 'अप्रचलित: :status',
            'key-field-id' => 'ID',
            'key-field-name' => 'नाम',
            'key-field-key' => 'कुंजी',
            'key-field-rate-limit' => 'दर सीमा',
            'key-field-status' => 'स्थिति',
            'key-days' => ' दिन',
            'key-never' => 'कभी नहीं',
            'key-requests-minute' => ' अनुरोध/मिनट',
            'key-unlimited' => 'असीमित',
            'rate-limit-exceeded' => 'अनुरोधित दर सीमा अधिकतम अनुमत :max से अधिक है। :max अनुरोध/मिनट पर सेट किया जा रहा है।',

            'vendor-path-not-found' => 'API Platform vendor पथ यहाँ नहीं मिला: :path',
            'assets-already-linked' => 'API Platform एसेट्स पहले से यहाँ लिंक हैं: :path',
            'no-keys-expiring' => 'अगले :days दिनों में कोई कुंजी समाप्त नहीं हो रही है',
            'keys-expiring-title' => 'अगले :days दिनों में समाप्त होने वाली कुंजियाँ:',
            'key-display-format' => '• :name (ID: :id)',
            'key-expires-display' => '  समाप्ति: :date (:days दिन शेष)',
            'no-unused-keys' => 'कोई अप्रयुक्त कुंजी नहीं मिली (> :days दिन)',
            'unused-keys-title' => 'अप्रयुक्त कुंजियाँ (> :days दिन):',
            'key-last-used-display' => '  अंतिम उपयोग: :date',
            'total-keys' => 'कुल कुंजियाँ: :count',
            'valid-keys' => 'मान्य कुंजियाँ: :count',
            'expired-keys' => 'समाप्त कुंजियाँ: :count',
            'deprecated-keys' => 'अप्रचलित कुंजियाँ: :count',
            'keys-expiring-soon' => 'जल्द समाप्त होने वाली कुंजियाँ (7 दिन): :count',
            'unused-keys-summary' => 'अप्रयुक्त कुंजियाँ (90 दिन): :count',
            'recently-rotated' => 'हाल ही में रोटेट की गईं (30 दिन): :count',
            'cleanup-success-message' => '   ✅ :count समाप्त कुंजियाँ साफ़ की गईं',
            'invalidate-success-message' => '   ✅ :count अप्रचलित कुंजियाँ अमान्य की गईं',
            'notify-success-message' => '   ✅ :count समाप्ति सूचनाएँ भेजी गईं',
            'notify-failed-message' => '   ⚠️ :key के बारे में सूचित करने में विफल: :error',
            'publish-assets-warning' => 'चेतावनी: पैकेज एसेट्स प्रकाशित नहीं किए जा सके। :error',
            'env-file-not-found' => '.env फ़ाइल नहीं मिली। इंस्टॉल करने से पहले इसे बनाएँ।',
            'env-permission-denied' => '.env फ़ाइल में लिखा नहीं जा सकता। इसकी अनुमतियाँ जाँचें।',
            'env-config-saved' => '✓ API Platform एनवायरनमेंट कॉन्फ़िगरेशन सफलतापूर्वक सहेजा गया',
            'api-endpoints' => 'API एंडपॉइंट:',
            'api-documentation' => 'API दस्तावेज़ीकरण: :url',
            'api-landing-page' => 'API लैंडिंग पेज: :url',
            'graphql-playground' => 'GraphQL Playground: :url',
            'rest-api-storefront' => 'Rest API स्टोरफ्रंट Swagger: :url',
            'rest-api-admin' => 'Rest API व्यवस्थापक Swagger: :url',
            'generating-jwt-keys' => 'एडमिन API टोकन के लिए JWT साइनिंग कुंजियाँ बनाई जा रही हैं...',
            'jwt-keys-generated' => '✓ JWT साइनिंग कुंजियाँ तैयार हैं',
            'jwt-keys-warning' => 'चेतावनी: JWT साइनिंग कुंजियाँ नहीं बन सकीं। php artisan bagisto-api:jwt-keys स्वयं चलाएँ। :error',
            'admin-login' => 'एडमिन API लॉगिन: :url',
            'admin-api-tokens' => 'एडमिन API टोकन (सर्वर इंटीग्रेशन): :url',
            'key-rotation-invalid-key' => 'अमान्य या समाप्त कुंजी को रोटेट नहीं किया जा सकता',
            'env-file-not-found' => '.env फ़ाइल नहीं मिली',
            'env-permission-denied' => 'अनुमति अस्वीकृत: .env फ़ाइल में लिखा नहीं जा सकता। कृपया फ़ाइल अनुमतियाँ जाँचें।',
        ],

        'attribute-translation' => [
            'id-required' => 'एट्रिब्यूट अनुवाद ID आवश्यक है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/shop/attribute-translations/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
            'not-found' => 'एट्रिब्यूट अनुवाद नहीं मिला',
        ],

        'currency' => [
            'id-required' => 'मुद्रा ID आवश्यक है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/shop/currencies/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
            'not-found' => 'मुद्रा नहीं मिली',
        ],

        'customer-address' => [
            'id-required' => 'ग्राहक पता ID आवश्यक है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/shop/customer-addresses/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
        ],

        'customer-order-shipment-item' => [
            'id-required' => 'शिपमेंट आइटम ID आवश्यक है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/shop/customer-order-shipment-items/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
            'not-found' => 'शिपमेंट आइटम नहीं मिला',
        ],

        'page' => [
            'id-required' => 'पेज ID आवश्यक है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/shop/pages/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
            'not-found' => 'पेज नहीं मिला',
        ],

        'page-translation' => [
            'id-required' => 'पेज अनुवाद ID आवश्यक है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/shop/page-translations/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
            'not-found' => 'पेज अनुवाद नहीं मिला',
        ],

        'product-bundle-option' => [
            'id-required' => 'बंडल विकल्प ID आवश्यक है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/shop/product-bundle-options/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
            'not-found' => 'बंडल विकल्प नहीं मिला',
        ],

        'product-bundle-option-product' => [
            'id-required' => 'बंडल विकल्प उत्पाद ID आवश्यक है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/shop/product-bundle-option-products/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
            'not-found' => 'बंडल विकल्प उत्पाद नहीं मिला',
        ],

        'product-downloadable-link' => [
            'id-required' => 'डाउनलोड योग्य लिंक ID आवश्यक है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/shop/product-downloadable-links/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
            'not-found' => 'डाउनलोड योग्य लिंक नहीं मिला',
        ],

        'product-downloadable-sample' => [
            'id-required' => 'डाउनलोड योग्य नमूना ID आवश्यक है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/shop/product-downloadable-samples/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
            'not-found' => 'डाउनलोड योग्य नमूना नहीं मिला',
        ],

        'product-grouped-product' => [
            'id-required' => 'समूहीकृत उत्पाद ID आवश्यक है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/shop/product-grouped-products/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
            'not-found' => 'समूहीकृत उत्पाद नहीं मिला',
        ],

        'product-images' => [
            'id-required' => 'उत्पाद छवि ID आवश्यक है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/shop/product-images/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
            'not-found' => 'उत्पाद छवि नहीं मिली',
        ],

        'product-videos' => [
            'id-required' => 'उत्पाद वीडियो ID आवश्यक है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/shop/product-videos/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
            'not-found' => 'उत्पाद वीडियो नहीं मिला',
        ],

        'theme-customization' => [
            'id-required' => 'थीम अनुकूलन ID आवश्यक है',
            'invalid-id-format' => 'अमान्य ID प्रारूप। "/api/shop/theme-customizations/1" जैसा IRI प्रारूप या संख्यात्मक ID अपेक्षित है',
            'not-found' => 'थीम अनुकूलन नहीं मिला',
        ],

        'unknown-resource' => [
            'id-required' => 'संसाधन ID आवश्यक है',
            'not-found' => 'संसाधन नहीं मिला',
        ],
    ],

    'admin' => [
        'profile' => [
            'unauthenticated' => 'आप प्रमाणित नहीं हैं।',
        ],

        'dashboard' => [
            'invalid-type' => 'अज्ञात डैशबोर्ड आँकड़ा प्रकार। मान्य प्रकार: over-all, today, stock-threshold-products, total-sales, top-selling-products, top-customers।',
        ],

        'rma' => [
            'not-found' => 'वापसी अनुरोध नहीं मिला।',
            'invalid-input' => 'वापसी विवरण अनुपलब्ध या अमान्य हैं।',
            'invalid-order' => 'चयनित ऑर्डर मान्य नहीं है।',
            'invalid-item' => 'चयनित आइटम वापसी के लिए पात्र नहीं है।',
            'invalid-resolution-type' => 'समाधान प्रकार "return" या "cancel_items" होना चाहिए।',
            'qty-exceeds' => 'अनुरोधित मात्रा वापसी योग्य मात्रा से अधिक है।',
            'created' => 'वापसी अनुरोध सफलतापूर्वक बनाया गया।',
            'status-updated' => 'वापसी स्थिति सफलतापूर्वक अपडेट की गई।',
            'invalid-status' => 'अमान्य वापसी स्थिति।',
            'reopened' => 'वापसी अनुरोध सफलतापूर्वक फिर से खोला गया।',
            'reopen-not-allowed' => 'यह वापसी अनुरोध फिर से नहीं खोला जा सकता।',
            'refund-failed' => 'इस वापसी के लिए रिफ़ंड नहीं बनाया जा सका।',
            'message-sent' => 'संदेश सफलतापूर्वक भेजा गया।',
            'no-permission' => 'आपको यह क्रिया करने की अनुमति नहीं है।',
            'reason-not-found' => 'वापसी कारण नहीं मिला।',
            'reason-deleted' => 'वापसी कारण सफलतापूर्वक हटाया गया।',
            'status-not-found' => 'वापसी स्थिति नहीं मिली।',
            'status-deleted' => 'वापसी स्थिति सफलतापूर्वक हटाई गई।',
            'status-default-undeletable' => 'डिफ़ॉल्ट वापसी स्थिति को हटाया नहीं जा सकता।',
            'rule-not-found' => 'वापसी नियम नहीं मिला।',
            'rule-deleted' => 'वापसी नियम सफलतापूर्वक हटाया गया।',
            'custom-field-not-found' => 'वापसी कस्टम फ़ील्ड नहीं मिली।',
            'custom-field-deleted' => 'वापसी कस्टम फ़ील्ड सफलतापूर्वक हटाई गई।',
            'custom-field-options-required' => 'इस फ़ील्ड प्रकार के लिए कम से कम एक विकल्प आवश्यक है।',
            'mass-indices-required' => 'कम से कम एक id आवश्यक है।',
            'mass-delete-success' => 'चयनित आइटम हटाए गए।',
            'mass-update-success' => 'चयनित आइटम अपडेट किए गए।',
            'mass-value-invalid' => 'स्थिति मान 0 या 1 होना चाहिए।',
            'image-not-found' => 'वापसी छवि नहीं मिली।',
            'attachment-not-found' => 'संदेश अनुलग्नक नहीं मिला।',
        ],

        'eu-withdrawal' => [
            'not-found' => 'निकासी घोषणा नहीं मिली।',
            'no-permission' => 'आपको EU निकासी प्रबंधित करने की अनुमति नहीं है।',
            'declined' => 'निकासी को अस्वीकृत के रूप में चिह्नित किया गया।',
            'refunded' => 'निकासी को रिफ़ंड किए गए के रूप में चिह्नित किया गया।',
            'confirmation-resent' => 'पुष्टिकरण ईमेल सफलतापूर्वक फिर से भेजा गया।',
            'confirmation-failed' => 'पुष्टिकरण ईमेल नहीं भेजा जा सका।',
        ],

        'reporting' => [
            'invalid-entity' => 'अज्ञात रिपोर्टिंग इकाई।',
            'invalid-type' => 'इस इकाई के लिए अज्ञात रिपोर्टिंग आँकड़ा प्रकार।',
            'export-format-unsupported' => 'केवल csv, xls और xlsx निर्यात प्रारूप समर्थित हैं।',
        ],

        'configuration' => [
            'unauthenticated' => 'आप प्रमाणित नहीं हैं।',
            'no-permission' => 'आपको कॉन्फ़िगरेशन अपडेट करने की अनुमति नहीं है।',
            'slug-required' => 'क्वेरी पैरामीटर `slug` आवश्यक है।',
            'slug-not-found' => 'दिए गए slug के लिए कोई कॉन्फ़िगरेशन सेक्शन पंजीकृत नहीं है।',
            'values-required' => '`values` के अंतर्गत कम से कम एक मान आवश्यक है।',
            'scope-escape' => 'कुंजी `:key` अनुरोधित slug उप-ट्री से बाहर है।',
            'unknown-field' => 'कुंजी `:key` एक पंजीकृत कॉन्फ़िगरेशन फ़ील्ड नहीं है।',
            'validation-failed' => 'एक या अधिक कॉन्फ़िगरेशन मान सत्यापन में विफल रहे।',
            'file-upload-rest-only' => 'फ़ील्ड `:field` के लिए बाइनरी फ़ाइल अपलोड केवल REST multipart एंडपॉइंट पर समर्थित हैं, GraphQL पर नहीं।',
            'custom-view-readonly' => 'फ़ील्ड `:field` एक कस्टम blade-रेंडर्ड फ़ील्ड है और इसे API के माध्यम से अपडेट नहीं किया जा सकता।',
            'update-success' => 'कॉन्फ़िगरेशन सफलतापूर्वक अपडेट किया गया।',
        ],

        'customer' => [
            'not-found' => 'ग्राहक नहीं मिला।',
            'no-permission' => 'आपको यह क्रिया करने की अनुमति नहीं है।',
            'has-active-orders' => 'ग्राहक के लंबित या प्रोसेसिंग ऑर्डर हैं और उसे हटाया नहीं जा सकता।',
            'deleted' => 'ग्राहक सफलतापूर्वक हटाया गया।',
            'mass-delete-indices-required' => 'कम से कम एक ग्राहक id आवश्यक है।',
            'mass-delete-success' => 'ग्राहक हटाए गए।',
            'mass-update-indices-required' => 'कम से कम एक ग्राहक id आवश्यक है।',
            'mass-update-value-invalid' => 'स्थिति मान 0 या 1 होना चाहिए।',
            'mass-update-success' => 'ग्राहक स्थिति अपडेट की गई।',

            'address' => [
                'not-found' => 'पता नहीं मिला।',
                'not-owned' => 'यह पता निर्दिष्ट ग्राहक से संबंधित नहीं है।',
                'customer-id-required' => 'पते के स्वामी की पहचान के लिए customerId आवश्यक है।',
                'deleted' => 'पता सफलतापूर्वक हटाया गया।',
            ],

            'note' => [
                'empty' => 'नोट का पाठ आवश्यक है।',
                'created' => 'नोट सफलतापूर्वक जोड़ा गया।',
            ],

            'review' => [
                'not-found' => 'समीक्षा नहीं मिली।',
                'no-permission' => 'आपको यह क्रिया करने की अनुमति नहीं है।',
                'invalid-status' => 'स्थिति इनमें से एक होनी चाहिए: pending, approved, disapproved।',
                'updated' => 'समीक्षा सफलतापूर्वक अपडेट की गई।',
                'deleted' => 'समीक्षा सफलतापूर्वक हटाई गई।',
                'mass-delete-indices-required' => 'कम से कम एक समीक्षा id आवश्यक है।',
                'mass-delete-success' => 'समीक्षाएँ हटाई गईं।',
                'mass-update-indices-required' => 'कम से कम एक समीक्षा id आवश्यक है।',
                'mass-update-value-invalid' => 'स्थिति मान इनमें से एक होना चाहिए: pending, approved, disapproved।',
                'mass-update-success' => 'समीक्षा स्थिति अपडेट की गई।',
            ],

            'group' => [
                'not-found' => 'ग्राहक समूह नहीं मिला।',
                'deleted' => 'ग्राहक समूह सफलतापूर्वक हटाया गया।',
                'is-system' => 'सिस्टम ग्राहक समूहों को हटाया नहीं जा सकता।',
                'has-customers' => 'ग्राहक समूह से ग्राहक जुड़े हैं और इसे हटाया नहीं जा सकता।',
                'system-code-immutable' => 'सिस्टम ग्राहक समूह का कोड बदला नहीं जा सकता।',
                'system-flag-immutable' => 'सिस्टम ग्राहक समूह का is_user_defined फ़्लैग बदला नहीं जा सकता।',
                'mass-delete-indices-required' => 'कम से कम एक ग्राहक समूह id आवश्यक है।',
                'mass-delete-success' => 'ग्राहक समूह हटाए गए।',
            ],

            'gdpr' => [
                'not-found' => 'GDPR अनुरोध नहीं मिला।',
                'no-permission' => 'आपको यह क्रिया करने की अनुमति नहीं है।',
                'deleted' => 'GDPR अनुरोध सफलतापूर्वक हटाया गया।',
                'invalid-status' => 'स्थिति इनमें से एक होनी चाहिए: pending, processing, declined, approved, revoked।',
                'no-changes' => 'स्थिति या संदेश में से कम से कम एक आवश्यक है।',
                'already-processed' => 'यह GDPR अनुरोध पहले ही संसाधित हो चुका है और इसे फिर से नहीं चलाया जा सकता।',

                'process' => [
                    'approved' => 'GDPR अनुरोध स्वीकृत किया गया।',
                    'deleted' => 'GDPR अनुरोध स्वीकृत किया गया और ग्राहक हटाया गया।',
                ],
            ],
        ],

        'order' => [
            'not-found' => 'ऑर्डर नहीं मिला।',

            'reorder' => [
                'success' => 'पुनः ऑर्डर सफल। एक नई ड्राफ़्ट कार्ट बनाई गई है।',
                'cannot-reorder' => 'इस ऑर्डर को पुनः ऑर्डर नहीं किया जा सकता (अतिथि ऑर्डर या एक या अधिक आइटम अब बिक्री योग्य नहीं हैं)।',
                'failed' => 'पुनः ऑर्डर विफल। कृपया पुनः प्रयास करें।',
                'guest-not-supported' => 'अतिथि ऑर्डर के लिए पुनः ऑर्डर समर्थित नहीं है।',
                'items-not-saleable' => 'इस ऑर्डर के एक या अधिक आइटम अब खरीद के लिए उपलब्ध नहीं हैं।',
                'no-permission' => 'आपको ऑर्डर बनाने की अनुमति नहीं है।',
                'disabled-in-settings' => 'स्टोर सेटिंग्स में व्यवस्थापक द्वारा पुनः ऑर्डर अक्षम है।',
            ],

            'actions' => [
                'cancel' => [
                    'closed' => 'बंद ऑर्डर रद्द नहीं किए जा सकते।',
                    'fraud' => 'धोखाधड़ी वाले ऑर्डर रद्द नहीं किए जा सकते।',
                    'nothing-to-cancel' => 'इस ऑर्डर में रद्द करने के लिए कुछ नहीं है।',
                    'already-processed' => 'इस ऑर्डर का चालान पहले ही बन चुका है या यह शिप हो चुका है और अब रद्द नहीं किया जा सकता। इसके बजाय आप रिफ़ंड जारी कर सकते हैं।',
                    'no-permission' => 'आपको ऑर्डर रद्द करने की अनुमति नहीं है।',
                    'success' => 'ऑर्डर सफलतापूर्वक रद्द किया गया।',
                    'failed' => 'ऑर्डर रद्द नहीं किया जा सका।',
                ],

                'comment' => [
                    'empty' => 'टिप्पणी आवश्यक है।',
                    'success' => 'ऑर्डर में टिप्पणी जोड़ी गई।',
                    'failed' => 'टिप्पणी नहीं जोड़ी जा सकी।',
                ],

                'invoice' => [
                    'closed' => 'बंद ऑर्डर का चालान नहीं बनाया जा सकता।',
                    'fraud' => 'धोखाधड़ी वाले ऑर्डर का चालान नहीं बनाया जा सकता।',
                    'nothing-to-invoice' => 'इस ऑर्डर में चालान बनाने के लिए कुछ नहीं है।',
                    'already-invoiced' => 'इस ऑर्डर के लिए चालान पहले ही जनरेट किया जा चुका है।',
                    'paypal-standard-blocked' => 'PayPal Standard के माध्यम से भुगतान किए गए ऑर्डर के लिए चालान नहीं बनाए जा सकते।',
                    'no-permission' => 'आपको चालान बनाने की अनुमति नहीं है।',
                    'items-required' => 'धनात्मक मात्रा वाला कम से कम एक आइटम आवश्यक है।',
                    'qty-exceeds' => 'SKU :sku के लिए अनुरोधित मात्रा (:requested) उपलब्ध मात्रा (:available) से अधिक है।',
                    'not-found' => 'चालान नहीं मिला।',
                    'success' => 'चालान सफलतापूर्वक बनाया गया।',
                    'failed' => 'चालान नहीं बनाया जा सका।',
                    'pdf-failed' => 'चालान PDF जनरेट नहीं की जा सकी।',
                    'email-invalid' => 'एक मान्य प्राप्तकर्ता ईमेल आवश्यक है।',
                    'duplicate-sent' => 'चालान ईमेल :email पर भेजा गया।',
                    'mass-status-indices-required' => 'कम से कम एक चालान id आवश्यक है।',
                    'mass-status-value-invalid' => 'स्थिति इनमें से एक होनी चाहिए: pending, paid, overdue।',
                    'mass-status-success' => 'चालान स्थिति सफलतापूर्वक अपडेट की गई।',
                ],

                'shipment' => [
                    'closed' => 'बंद ऑर्डर शिप नहीं किए जा सकते।',
                    'fraud' => 'धोखाधड़ी वाले ऑर्डर शिप नहीं किए जा सकते।',
                    'nothing-to-ship' => 'इस ऑर्डर में शिप करने के लिए कुछ नहीं है।',
                    'no-permission' => 'आपको ऑर्डर शिप करने की अनुमति नहीं है।',
                    'source-required' => 'इन्वेंटरी स्रोत आवश्यक है।',
                    'items-required' => 'धनात्मक मात्रा वाला कम से कम एक आइटम आवश्यक है।',
                    'qty-exceeds' => 'SKU :sku के लिए अनुरोधित मात्रा (:requested) उपलब्ध मात्रा (:available) से अधिक है।',
                    'inventory-insufficient' => 'चयनित स्रोत पर SKU :sku के लिए इन्वेंटरी अपर्याप्त है।',
                    'not-found' => 'शिपमेंट नहीं मिला।',
                    'success' => 'शिपमेंट सफलतापूर्वक बनाया गया।',
                    'failed' => 'शिपमेंट नहीं बनाया जा सका।',
                ],

                'refund' => [
                    'closed' => 'बंद ऑर्डर का रिफ़ंड नहीं किया जा सकता।',
                    'fraud' => 'धोखाधड़ी वाले ऑर्डर का रिफ़ंड नहीं किया जा सकता।',
                    'nothing-to-refund' => 'इस ऑर्डर में रिफ़ंड करने के लिए कुछ भी शेष नहीं है।',
                    'no-permission' => 'आपको रिफ़ंड बनाने की अनुमति नहीं है।',
                    'qty-exceeds' => 'SKU :sku के लिए अनुरोधित मात्रा (:requested) उपलब्ध मात्रा (:available) से अधिक है।',
                    'amount-zero' => 'गणना की गई रिफ़ंड राशि शून्य है। मात्रा, शिपिंग या समायोजन मान बदलें।',
                    'amount-exceeds-max' => 'रिफ़ंड राशि (:amount) अधिकतम रिफ़ंड योग्य राशि (:max) से अधिक है।',
                    'not-found' => 'रिफ़ंड नहीं मिला।',
                    'success' => 'रिफ़ंड सफलतापूर्वक बनाया गया।',
                    'failed' => 'रिफ़ंड नहीं बनाया जा सका।',
                ],
            ],
        ],

        'appearance' => [
            'no-permission' => 'आपको रूप-रंग प्रबंधित करने की अनुमति नहीं है।',

            'theme' => [
                'not-found' => 'थीम नहीं मिली।',
                'not-installed' => 'यह थीम इंस्टॉल नहीं है।',
                'channel-ids-required' => 'कम से कम एक चैनल आवश्यक है।',
                'unknown-channel' => 'एक या अधिक चैनल मौजूद नहीं हैं।',
                'activated' => 'थीम सफलतापूर्वक सक्रिय की गई।',
            ],

            'section' => [
                'not-found' => 'सेक्शन नहीं मिला।',
                'invalid-payload' => 'असमर्थित सेक्शन पेलोड।',
                'footer-links-exists' => 'इस चैनल में पहले से एक फ़ुटर लिंक सेक्शन है।',
                'options-required' => 'विकल्प आवश्यक हैं।',
                'status-required' => 'स्थिति आवश्यक है।',
                'section-ids-required' => 'कम से कम एक सेक्शन आवश्यक है।',
                'reorder-single-scope' => 'सेक्शन को एक समय में केवल एक थीम और चैनल के भीतर ही पुनः क्रमित किया जा सकता है।',
                'reorder-incomplete' => 'पुनः क्रमित करने के लिए थीम और चैनल के सभी सेक्शन आवश्यक हैं, उसी क्रम में जिसमें उन्हें रेंडर होना चाहिए।',
                'unknown-section' => 'एक या अधिक सेक्शन मौजूद नहीं हैं।',
                'file-required' => 'एक फ़ाइल आवश्यक है।',
                'created' => 'सेक्शन सफलतापूर्वक बनाया गया।',
                'updated' => 'सेक्शन सफलतापूर्वक अपडेट किया गया।',
                'deleted' => 'सेक्शन सफलतापूर्वक हटाया गया।',
                'draft-saved' => 'सेक्शन परिवर्तन सफलतापूर्वक स्टेज किए गए।',
                'status-staged' => 'सेक्शन स्थिति सफलतापूर्वक स्टेज की गई।',
                'order-staged' => 'सेक्शन क्रम सफलतापूर्वक स्टेज किया गया।',
                'duplicated' => 'सेक्शन सफलतापूर्वक कॉपी किया गया।',
                'published' => 'सेक्शन परिवर्तन सफलतापूर्वक प्रकाशित किए गए।',
                'discarded' => 'सेक्शन परिवर्तन सफलतापूर्वक त्याग दिए गए।',
                'media-uploaded' => 'मीडिया सफलतापूर्वक अपलोड किया गया।',
            ],
        ],

        'sales' => [
            'no-permission' => 'आपको यह बिक्री संसाधन देखने की अनुमति नहीं है।',

            'invoice' => [
                'not-found' => 'चालान नहीं मिला।',
                'no-permission' => 'आपको चालान देखने की अनुमति नहीं है।',
            ],

            'shipment' => [
                'not-found' => 'शिपमेंट नहीं मिला।',
                'no-permission' => 'आपको शिपमेंट देखने की अनुमति नहीं है।',
            ],

            'refund' => [
                'not-found' => 'रिफ़ंड नहीं मिला।',
                'no-permission' => 'आपको रिफ़ंड देखने की अनुमति नहीं है।',
                'export-format-unsupported' => 'केवल csv, xls और xlsx निर्यात समर्थित हैं।',
            ],

            'transaction' => [
                'not-found' => 'लेन-देन नहीं मिला।',
                'no-permission' => 'आपको लेन-देन देखने की अनुमति नहीं है।',

                'create' => [
                    'invoice-required' => 'invoiceId फ़ील्ड आवश्यक है।',
                    'payment-method-required' => 'paymentMethod फ़ील्ड आवश्यक है।',
                    'amount-invalid' => 'amount फ़ील्ड आवश्यक है और संख्यात्मक होनी चाहिए।',
                    'invoice-missing' => 'निर्दिष्ट चालान नहीं मिला।',
                    'already-paid' => 'इस चालान का पहले ही पूरा भुगतान हो चुका है।',
                    'amount-exceeds' => 'भुगतान राशि चालान के कुल योग से अधिक है।',
                    'amount-zero' => 'भुगतान राशि शून्य से अधिक होनी चाहिए।',
                ],
            ],

            'booking' => [
                'not-found' => 'बुकिंग नहीं मिली।',
                'no-permission' => 'आपको बुकिंग देखने की अनुमति नहीं है।',
            ],

            'export' => [
                'format-unsupported' => 'केवल csv, xls और xlsx निर्यात समर्थित हैं।',
            ],
        ],

        'cart' => [
            'not-found' => 'कार्ट नहीं मिली।',
            'not-draft' => 'इस कार्ट को व्यवस्थापक API के माध्यम से संशोधित नहीं किया जा सकता।',
            'product-required' => 'productId आवश्यक है।',
            'product-not-found' => 'उत्पाद नहीं मिला।',
            'booking-unsupported' => 'बुकिंग उत्पादों को व्यवस्थापक ड्राफ़्ट ऑर्डर में नहीं जोड़ा जा सकता। बुकिंग खरीद ग्राहक स्टोरफ्रंट के माध्यम से ही की जानी चाहिए।',
            'product-not-saleable' => 'यह उत्पाद बिक्री के लिए उपलब्ध नहीं है (स्टॉक में नहीं या अक्षम) और इसे ऑर्डर में नहीं जोड़ा जा सकता।',
            'qty-required' => 'qty आवश्यक है और यह itemId => quantity का एक गैर-रिक्त ऑब्जेक्ट होना चाहिए।',
            'cart-item-required' => 'cartItemId आवश्यक है।',
            'item-added' => 'आइटम कार्ट में जोड़ा गया।',
            'item-add-failed' => 'आइटम को कार्ट में नहीं जोड़ा जा सका।',
            'item-updated' => 'कार्ट आइटम अपडेट किए गए।',
            'item-update-failed' => 'कार्ट आइटम अपडेट नहीं किए जा सके।',
            'item-removed' => 'आइटम कार्ट से हटाया गया।',
            'item-remove-failed' => 'कार्ट आइटम हटाया नहीं जा सका।',
            'address-saved' => 'पता सहेजा गया।',
            'address-required' => 'बिलिंग पता आवश्यक है।',
            'address-incomplete' => 'पता सहेजने के लिए :field आवश्यक है।',
            'coupon-code-required' => 'कूपन कोड आवश्यक है।',
            'coupon-applied' => 'कूपन लागू किया गया।',
            'coupon-already-applied' => 'कूपन इस कार्ट पर पहले से लागू है।',
            'coupon-not-found' => 'कूपन नहीं मिला या निष्क्रिय है।',
            'coupon-removed' => 'कूपन हटाया गया।',
            'coupon-error' => 'कूपन लागू करते समय कुछ गलत हो गया।',
            'unknown-error' => 'कुछ गलत हो गया।',
            'draft-created' => 'ड्राफ़्ट कार्ट बनाई गई।',
            'draft-failed' => 'ड्राफ़्ट कार्ट नहीं बनाई जा सकी।',
            'addresses-required-for-shipping' => 'शिपिंग विधि चुनने से पहले पते सहेजे जाने चाहिए।',
            'shipping-required-for-payment' => 'भुगतान विधि से पहले शिपिंग विधि चुनी जानी चाहिए।',
            'shipping-method-required' => 'शिपिंग विधि आवश्यक है।',
            'shipping-method-saved' => 'शिपिंग विधि सहेजी गई।',
            'shipping-method-failed' => 'शिपिंग विधि सहेजी नहीं जा सकी।',
            'payment-method-required' => 'भुगतान विधि आवश्यक है।',
            'payment-method-saved' => 'भुगतान विधि सहेजी गई।',
            'payment-method-failed' => 'भुगतान विधि सहेजी नहीं जा सकी।',

            'place-order' => [
                'success' => 'ऑर्डर सफलतापूर्वक दिया गया।',
                'failed' => 'ऑर्डर नहीं दिया जा सका।',
                'error' => 'ऑर्डर नहीं दिया जा सका: कार्ट में त्रुटियाँ हैं।',
                'minimum-order-error' => 'न्यूनतम ऑर्डर राशि :amount है। ऑर्डर देने से पहले और आइटम जोड़ें।',
                'empty-cart' => 'कार्ट खाली है — ऑर्डर देने से पहले आइटम जोड़ें।',
                'addresses-required' => 'ऑर्डर देने से पहले बिलिंग और शिपिंग पते सहेजे जाने चाहिए।',
                'shipping-required' => 'ऑर्डर देने से पहले एक शिपिंग विधि चुनी जानी चाहिए।',
                'payment-required' => 'ऑर्डर देने से पहले एक भुगतान विधि चुनी जानी चाहिए।',
                'payment-method-unsupported' => 'व्यवस्थापक द्वारा दिए गए ऑर्डर के लिए केवल cashondelivery और moneytransfer समर्थित हैं।',
            ],
        ],

        'product' => [
            'not-found' => 'उत्पाद नहीं मिला।',

            'mass-delete-success' => 'उत्पाद सफलतापूर्वक हटाए गए।',
            'mass-update-status-success' => 'उत्पादों की स्थिति सफलतापूर्वक अपडेट की गई।',
            'indices-required' => 'indices फ़ील्ड आवश्यक है और यह एक गैर-रिक्त सरणी होनी चाहिए।',
            'indices-invalid' => 'indices फ़ील्ड पूर्णांक उत्पाद ID की सरणी होनी चाहिए।',
            'value-invalid' => 'value फ़ील्ड आवश्यक है और यह 0 या 1 होनी चाहिए।',
            'mass-delete-failed' => 'एक या अधिक उत्पाद हटाए नहीं जा सके।',
            'no-permission' => 'आपको उत्पाद प्रबंधित करने की अनुमति नहीं है।',

            'copy-success' => 'उत्पाद सफलतापूर्वक कॉपी किया गया।',
            'copy-failed' => 'उत्पाद कॉपी करने में विफल।',
            'copy-variant-not-supported' => 'कॉन्फ़िगर करने योग्य उत्पादों के वेरिएंट कॉपी नहीं किए जा सकते। इसके बजाय मूल कॉन्फ़िगर करने योग्य उत्पाद कॉपी करें।',

            'create' => [
                'created' => 'उत्पाद सफलतापूर्वक बनाया गया।',
                'create-failed' => 'उत्पाद बनाने में विफल।',
                'sku-required' => 'sku फ़ील्ड आवश्यक है।',
                'sku-unique' => 'यह sku पहले ही लिया जा चुका है।',
                'sku-invalid' => 'sku में केवल अक्षर, संख्याएँ और हाइफ़न हो सकते हैं (उदा. "sp-001")।',
                'attribute-family-required' => 'attribute_family_id फ़ील्ड आवश्यक है।',
                'attribute-family-not-found' => 'चयनित विशेषता परिवार मौजूद नहीं है।',
                'type-required' => 'type फ़ील्ड आवश्यक है।',
                'type-not-yet-supported' => 'उत्पाद प्रकार ":type" इस API द्वारा समर्थित नहीं है। अनुमत प्रकार: simple, virtual, downloadable, grouped, bundle, configurable, booking।',
                'super-attributes-required' => 'type=configurable होने पर super_attributes फ़ील्ड आवश्यक है। विशेषता कोड (या id) से option_ids का मैप प्रदान करें, उदा. { "color": [1,2], "size": [4,5] }।',
                'super-attributes-invalid' => 'super_attributes फ़ील्ड विशेषता कोड (या id) से option_ids की गैर-रिक्त सूची का एक गैर-रिक्त मैप होनी चाहिए।',
                'no-permission' => 'आपको उत्पाद बनाने की अनुमति नहीं है।',
            ],

            'update' => [
                'updated' => 'उत्पाद सफलतापूर्वक अपडेट किया गया।',
                'update-failed' => 'उत्पाद अपडेट करने में विफल।',
                'id-required' => 'उत्पाद id आवश्यक है।',
                'url-key-required' => 'url_key फ़ील्ड खाली नहीं हो सकती।',
                'url-key-unique' => 'यह url_key पहले ही किसी अन्य उत्पाद द्वारा लिया जा चुका है।',
                'special-price-invalid' => 'special_price एक संख्या होनी चाहिए जो price से कम हो।',
                'special-price-date-range-invalid' => 'special_price_to, special_price_from के बराबर या उसके बाद होनी चाहिए।',
                'boolean-field-invalid' => ':field फ़ील्ड 0 या 1 होनी चाहिए।',
                'categories-invalid' => 'categories फ़ील्ड श्रेणी id की सरणी होनी चाहिए।',
                'channels-invalid' => 'channels फ़ील्ड चैनल id की सरणी होनी चाहिए।',
                'super-attributes-cannot-change' => 'उत्पाद बनने के बाद super_attributes बदले नहीं जा सकते।',
                'sub-resource-stripped-images' => 'छवियों का प्रबंधन POST /api/admin/catalog/products/{id}/images के माध्यम से किया जाना चाहिए। images फ़ील्ड को अनदेखा किया गया।',
                'sub-resource-stripped-inventories' => 'इन्वेंटरी का प्रबंधन PUT /api/admin/catalog/products/{id}/inventories के माध्यम से किया जाना चाहिए। inventories फ़ील्ड को अनदेखा किया गया।',
                'sub-resource-stripped-customer-group-prices' => 'ग्राहक-समूह मूल्यों का प्रबंधन /api/admin/catalog/products/{id}/customer-group-prices के माध्यम से किया जाना चाहिए। customer_group_prices फ़ील्ड को अनदेखा किया गया।',
                'translations-single-locale' => 'केवल अनुरोधित लोकेल अपडेट किया गया। लोकेल :locales अपडेट करने के लिए, प्रत्येक के लिए ?locale=<code> के साथ एक अलग अनुरोध भेजें।',
            ],

            'delete' => [
                'deleted' => 'उत्पाद सफलतापूर्वक हटाया गया।',
                'delete-failed' => 'उत्पाद हटाने में विफल।',
            ],

            'image' => [
                'uploaded' => 'उत्पाद छवि सफलतापूर्वक अपलोड की गई।',
                'updated' => 'उत्पाद छवि सफलतापूर्वक अपडेट की गई।',
                'update-empty' => 'अपडेट करने के लिए alt टेक्स्ट या स्थान भेजें।',
                'reordered' => 'उत्पाद छवियाँ सफलतापूर्वक पुनः क्रमित की गईं।',
                'deleted' => 'उत्पाद छवि सफलतापूर्वक हटाई गई।',
                'image-required' => 'एक छवि फ़ाइल आवश्यक है (multipart फ़ील्ड "image")।',
                'image-invalid-type' => 'छवि का प्रकार इनमें से एक होना चाहिए: bmp, jpeg, jpg, png, webp।',
                'image-too-large' => 'छवि बहुत बड़ी है। अधिकतम अनुमत आकार 4 MB है।',
                'not-found' => 'उत्पाद छवि नहीं मिली।',
                'no-permission' => 'आपको उत्पाद छवियाँ प्रबंधित करने की अनुमति नहीं है।',
                'upload-failed' => 'उत्पाद छवि अपलोड करने में विफल।',
                'reorder-failed' => 'उत्पाद छवियों को पुनः क्रमित करने में विफल।',
                'delete-failed' => 'उत्पाद छवि हटाने में विफल।',
                'order-required' => '"order" सरणी आवश्यक है और यह खाली नहीं होनी चाहिए।',
                'order-invalid' => '"order" की प्रत्येक प्रविष्टि id और position वाला एक ऑब्जेक्ट होनी चाहिए।',
                'id-not-on-product' => 'छवि :id इस उत्पाद से संबंधित नहीं है।',
                'graphql-upload-unsupported' => 'GraphQL पर छवि अपलोड समर्थित नहीं है। multipart/form-data के साथ POST /api/admin/catalog/products/{productId}/images का उपयोग करें।',
            ],

            'downloadable' => [
                'file-required' => 'एक फ़ाइल आवश्यक है (multipart फ़ील्ड "file")।',
                'file-too-large' => 'फ़ाइल बहुत बड़ी है। अधिकतम अनुमत आकार 128 MB है।',
                'upload-failed' => 'डाउनलोड योग्य फ़ाइल अपलोड करने में विफल।',
                'file-not-found' => 'इस उत्पाद और विशेषता के लिए कोई संग्रहीत फ़ाइल नहीं मिली।',
                'no-permission' => 'आपको डाउनलोड योग्य उत्पाद फ़ाइलें प्रबंधित करने की अनुमति नहीं है।',
            ],

            'video' => [
                'uploaded' => 'उत्पाद वीडियो सफलतापूर्वक अपलोड किया गया।',
                'deleted' => 'उत्पाद वीडियो सफलतापूर्वक हटाया गया।',
                'video-required' => 'एक वीडियो फ़ाइल आवश्यक है (multipart फ़ील्ड "video")।',
                'video-invalid-type' => 'वीडियो का प्रकार इनमें से एक होना चाहिए: mp4, webm, ogg, mov।',
                'video-too-large' => 'वीडियो बहुत बड़ा है। अधिकतम अनुमत आकार 100 MB है।',
                'not-found' => 'उत्पाद वीडियो नहीं मिला।',
                'no-permission' => 'आपको उत्पाद वीडियो प्रबंधित करने की अनुमति नहीं है।',
                'upload-failed' => 'उत्पाद वीडियो अपलोड करने में विफल।',
                'delete-failed' => 'उत्पाद वीडियो हटाने में विफल।',
                'graphql-upload-unsupported' => 'GraphQL पर वीडियो अपलोड समर्थित नहीं है। multipart/form-data के साथ POST /api/admin/catalog/products/{productId}/videos का उपयोग करें।',
            ],

            'inventory' => [
                'updated' => 'उत्पाद इन्वेंटरी सफलतापूर्वक सहेजी गई।',
                'inventories-required' => 'inventories फ़ील्ड आवश्यक है और यह inventory_source_id से मात्रा का एक गैर-रिक्त मैप होनी चाहिए।',
                'inventories-invalid' => 'inventories फ़ील्ड inventory_source_id (धनात्मक पूर्णांक) से मात्रा (गैर-ऋणात्मक पूर्णांक) का मैप होनी चाहिए।',
                'source-not-found' => 'इन्वेंटरी स्रोत :id मौजूद नहीं है।',
                'qty-invalid' => 'प्रत्येक मात्रा एक गैर-ऋणात्मक पूर्णांक होनी चाहिए।',
                'no-permission' => 'आपको उत्पाद इन्वेंटरी प्रबंधित करने की अनुमति नहीं है।',
                'not-found' => 'उत्पाद नहीं मिला।',
            ],

            'customer-group-price' => [
                'created' => 'ग्राहक-समूह मूल्य सफलतापूर्वक जोड़ा गया।',
                'updated' => 'ग्राहक-समूह मूल्य सफलतापूर्वक अपडेट किया गया।',
                'deleted' => 'ग्राहक-समूह मूल्य सफलतापूर्वक हटाया गया।',
                'qty-required' => 'qty फ़ील्ड आवश्यक है।',
                'qty-invalid' => 'qty फ़ील्ड 1 के बराबर या उससे बड़ा पूर्णांक होनी चाहिए।',
                'value-type-required' => 'value_type फ़ील्ड आवश्यक है।',
                'value-type-invalid' => 'value_type फ़ील्ड "fixed" या "discount" होनी चाहिए।',
                'value-required' => 'value फ़ील्ड आवश्यक है।',
                'value-invalid' => 'value फ़ील्ड एक गैर-ऋणात्मक संख्या होनी चाहिए।',
                'customer-group-not-found' => 'चयनित ग्राहक समूह मौजूद नहीं है।',
                'duplicate-qty-group' => 'इस उत्पाद के लिए इस (qty, customer_group_id) संयोजन का ग्राहक-समूह मूल्य पहले से मौजूद है।',
                'not-found' => 'ग्राहक-समूह मूल्य नहीं मिला।',
                'no-permission' => 'आपको उत्पाद के ग्राहक-समूह मूल्य प्रबंधित करने की अनुमति नहीं है।',
            ],
        ],

        'category' => [
            'not-found' => 'श्रेणी नहीं मिली।',

            'created' => 'श्रेणी सफलतापूर्वक बनाई गई।',
            'updated' => 'श्रेणी सफलतापूर्वक अपडेट की गई।',
            'deleted' => 'श्रेणी सफलतापूर्वक हटाई गई।',
            'slug-required' => 'slug फ़ील्ड आवश्यक है।',
            'slug-unique' => 'यह slug पहले ही लिया जा चुका है।',
            'name-required' => 'name फ़ील्ड आवश्यक है।',
            'position-required' => 'position फ़ील्ड आवश्यक है।',
            'attributes-required' => 'कम से कम एक फ़िल्टर करने योग्य विशेषता आवश्यक है।',
            'description-required-for-mode' => 'चुने गए प्रदर्शन मोड के लिए विवरण आवश्यक है।',
            'cannot-delete-root' => 'रूट और चैनल-रूट श्रेणियाँ हटाई नहीं जा सकतीं।',
            'cannot-delete-channel-root' => 'यह श्रेणी एक चैनल रूट है और इसे हटाया नहीं जा सकता।',
            'delete-failed' => 'श्रेणी हटाई नहीं जा सकी।',
            'no-permission' => 'आपको श्रेणियाँ प्रबंधित करने की अनुमति नहीं है।',
            'mass-delete-success' => 'श्रेणियाँ सफलतापूर्वक हटाई गईं।',
            'mass-update-status-success' => 'श्रेणियों की स्थिति सफलतापूर्वक अपडेट की गई।',
            'mass-delete-indices-required' => 'indices फ़ील्ड आवश्यक है और यह एक गैर-रिक्त सरणी होनी चाहिए।',
            'mass-update-status-value-required' => 'value फ़ील्ड आवश्यक है और यह 0 या 1 होनी चाहिए।',
        ],

        'attribute' => [
            'not-found' => 'विशेषता नहीं मिली।',

            'create-success' => 'विशेषता सफलतापूर्वक बनाई गई।',
            'update-success' => 'विशेषता सफलतापूर्वक अपडेट की गई।',
            'delete-success' => 'विशेषता सफलतापूर्वक हटाई गई।',
            'delete-failed' => 'विशेषता हटाई नहीं जा सकी।',
            'mass-delete-success' => 'विशेषताएँ सफलतापूर्वक हटाई गईं।',
            'mass-delete-indices-required' => 'indices फ़ील्ड आवश्यक है और यह एक गैर-रिक्त सरणी होनी चाहिए।',

            'code-immutable' => 'विशेषता बनने के बाद उसका कोड बदला नहीं जा सकता।',
            'type-immutable' => 'जब तक विशेषता के उत्पाद मान मौजूद हैं, उसका प्रकार बदला नहीं जा सकता।',
            'locale-scope-immutable' => 'जब तक विशेषता के उत्पाद मान मौजूद हैं, locale-scope बदला नहीं जा सकता।',
            'system-attribute' => 'सिस्टम विशेषताएँ हटाई नहीं जा सकतीं।',
            'in-use-family' => 'विशेषता एक या अधिक विशेषता परिवारों का हिस्सा है (समूह ID: :ids)। पहले इसे उन परिवारों से हटाएँ।',

            'option-not-found' => 'विशेषता विकल्प नहीं मिला।',
            'option-not-supported' => 'विशेषता प्रकार ":type" विकल्पों का समर्थन नहीं करता। केवल select, multiselect और checkbox विशेषताओं में विकल्प हो सकते हैं।',
            'option-in-use' => 'इस विकल्प का उपयोग :count उत्पाद कर रहे हैं और इसे हटाया नहीं जा सकता।',
            'option-delete-success' => 'विशेषता विकल्प सफलतापूर्वक हटाया गया।',
        ],

        'family' => [
            'not-found' => 'विशेषता परिवार नहीं मिला।',

            'created' => 'विशेषता परिवार सफलतापूर्वक बनाया गया।',
            'updated' => 'विशेषता परिवार सफलतापूर्वक अपडेट किया गया।',
            'deleted' => 'विशेषता परिवार सफलतापूर्वक हटाया गया।',
            'code-required' => 'परिवार कोड आवश्यक है।',
            'code-unique' => 'परिवार कोड अद्वितीय होना चाहिए।',
            'name-required' => 'परिवार का नाम आवश्यक है।',
            'group-code-required' => 'प्रत्येक विशेषता समूह का एक कोड होना चाहिए।',
            'group-name-required' => 'प्रत्येक विशेषता समूह का एक नाम होना चाहिए।',
            'group-column-invalid' => 'प्रत्येक विशेषता समूह का कॉलम 1 या 2 होना चाहिए।',
            'group-name-duplicate' => 'विशेषता समूह का नाम विशेषता परिवार के भीतर अद्वितीय होना चाहिए।',
            'update-failed' => 'विशेषता परिवार अपडेट नहीं किया जा सका।',
            'create-failed' => 'विशेषता परिवार नहीं बनाया जा सका।',
            'default-delete-error' => 'डिफ़ॉल्ट विशेषता परिवार हटाया नहीं जा सकता।',
            'last-delete-error' => 'कम से कम एक विशेषता परिवार आवश्यक है।',
            'attribute-product-error' => 'यह विशेषता परिवार एक या अधिक उत्पादों द्वारा उपयोग में है। इसे हटाने से पहले उन उत्पादों को पुनः असाइन करें।',
            'delete-failed' => 'विशेषता परिवार हटाया नहीं जा सका।',
            'no-permission' => 'आपको विशेषता परिवार प्रबंधित करने की अनुमति नहीं है।',
        ],

        'settings' => [
            'locale' => [
                'not-found' => 'लोकेल नहीं मिला।',
                'created' => 'लोकेल सफलतापूर्वक बनाया गया।',
                'updated' => 'लोकेल सफलतापूर्वक अपडेट किया गया।',
                'deleted' => 'लोकेल सफलतापूर्वक हटाया गया।',
                'cannot-delete-last' => 'कम से कम एक लोकेल आवश्यक है — अंतिम शेष लोकेल हटाया नहीं जा सकता।',
                'cannot-delete-channel-default' => 'यह लोकेल एक या अधिक चैनलों का डिफ़ॉल्ट लोकेल है और इसे हटाया नहीं जा सकता।',
                'delete-failed' => 'लोकेल हटाया नहीं जा सका।',
                'no-permission' => 'आपको लोकेल प्रबंधित करने की अनुमति नहीं है।',
                'mass-delete-success' => 'लोकेल सफलतापूर्वक हटाए गए।',
                'mass-delete-indices-required' => 'indices फ़ील्ड आवश्यक है और यह एक गैर-रिक्त सरणी होनी चाहिए।',
            ],

            'theme' => [
                'not-found' => 'थीम अनुकूलन नहीं मिला।',
                'created' => 'थीम अनुकूलन सफलतापूर्वक बनाया गया।',
                'updated' => 'थीम अनुकूलन सफलतापूर्वक अपडेट किया गया।',
                'deleted' => 'थीम अनुकूलन सफलतापूर्वक हटाया गया।',
                'delete-failed' => 'थीम अनुकूलन हटाया नहीं जा सका।',
                'no-permission' => 'आपको थीम अनुकूलन प्रबंधित करने की अनुमति नहीं है।',
                'mass-delete-success' => 'थीम अनुकूलन सफलतापूर्वक हटाए गए।',
                'mass-delete-indices-required' => 'indices फ़ील्ड आवश्यक है और यह एक गैर-रिक्त सरणी होनी चाहिए।',
                'mass-update-success' => 'थीम अनुकूलन सफलतापूर्वक अपडेट किए गए।',
                'mass-update-indices-required' => 'indices फ़ील्ड आवश्यक है और यह एक गैर-रिक्त सरणी होनी चाहिए।',
                'mass-update-value-invalid' => 'value फ़ील्ड 0 या 1 होनी चाहिए।',
            ],

            'inventory-source' => [
                'not-found' => 'इन्वेंटरी स्रोत नहीं मिला।',
                'created' => 'इन्वेंटरी स्रोत सफलतापूर्वक बनाया गया।',
                'updated' => 'इन्वेंटरी स्रोत सफलतापूर्वक अपडेट किया गया।',
                'deleted' => 'इन्वेंटरी स्रोत सफलतापूर्वक हटाया गया।',
                'code-unique' => 'इस कोड वाला इन्वेंटरी स्रोत पहले से मौजूद है।',
                'last-delete-error' => 'कम से कम एक इन्वेंटरी स्रोत आवश्यक है — अंतिम शेष स्रोत हटाया नहीं जा सकता।',
                'in-use' => 'यह इन्वेंटरी स्रोत उत्पाद इन्वेंटरी द्वारा संदर्भित है और इसे हटाया नहीं जा सकता।',
                'delete-failed' => 'इन्वेंटरी स्रोत हटाया नहीं जा सका।',
                'no-permission' => 'आपको इन्वेंटरी स्रोत प्रबंधित करने की अनुमति नहीं है।',
                'mass-delete-success' => 'इन्वेंटरी स्रोत सफलतापूर्वक हटाए गए।',
                'mass-delete-indices-required' => 'indices फ़ील्ड आवश्यक है और यह एक गैर-रिक्त सरणी होनी चाहिए।',
            ],

            'exchange-rate' => [
                'not-found' => 'विनिमय दर नहीं मिली।',
                'created' => 'विनिमय दर सफलतापूर्वक बनाई गई।',
                'updated' => 'विनिमय दर सफलतापूर्वक अपडेट की गई।',
                'deleted' => 'विनिमय दर सफलतापूर्वक हटाई गई।',
                'duplicate-pair' => 'इस लक्ष्य मुद्रा के लिए विनिमय दर पहले से मौजूद है। मौजूदा पंक्ति को अपडेट करें या पहले उसे हटाएँ।',
                'delete-failed' => 'विनिमय दर हटाई नहीं जा सकी।',
                'no-permission' => 'आपको विनिमय दरें प्रबंधित करने की अनुमति नहीं है।',
                'mass-delete-success' => 'विनिमय दरें सफलतापूर्वक हटाई गईं।',
                'mass-delete-indices-required' => 'indices फ़ील्ड आवश्यक है और यह एक गैर-रिक्त सरणी होनी चाहिए।',
                'update-rates-success' => 'विनिमय दरें सफलतापूर्वक अपडेट की गईं।',
                'update-rates-failed' => 'बाहरी प्रदाता से विनिमय दरें अपडेट नहीं की जा सकीं।',
                'provider-not-configured' => 'कोई बाहरी विनिमय-दर प्रदाता कॉन्फ़िगर नहीं है।',
            ],

            'tax-rate' => [
                'not-found' => 'कर दर नहीं मिली।',
                'created' => 'कर दर सफलतापूर्वक बनाई गई।',
                'updated' => 'कर दर सफलतापूर्वक अपडेट की गई।',
                'deleted' => 'कर दर सफलतापूर्वक हटाई गई।',
                'delete-failed' => 'कर दर हटाई नहीं जा सकी।',
                'no-permission' => 'आपको कर दरें प्रबंधित करने की अनुमति नहीं है।',
                'zip-code-required' => 'is_zip के false होने पर zip_code फ़ील्ड आवश्यक है।',
                'zip-range-required' => 'is_zip के true होने पर zip_from और zip_to दोनों आवश्यक हैं।',
            ],

            'tax-category' => [
                'not-found' => 'कर श्रेणी नहीं मिली।',
                'created' => 'कर श्रेणी सफलतापूर्वक बनाई गई।',
                'updated' => 'कर श्रेणी सफलतापूर्वक अपडेट की गई।',
                'deleted' => 'कर श्रेणी सफलतापूर्वक हटाई गई।',
                'code-unique' => 'यह कोड पहले ही लिया जा चुका है।',
                'cannot-delete-in-use' => 'इस कर श्रेणी से अभी भी कर दरें जुड़ी हैं और इसे हटाया नहीं जा सकता।',
                'delete-failed' => 'कर श्रेणी हटाई नहीं जा सकी।',
                'no-permission' => 'आपको कर श्रेणियाँ प्रबंधित करने की अनुमति नहीं है।',
            ],

            'role' => [
                'not-found' => 'भूमिका नहीं मिली।',
                'created' => 'भूमिका सफलतापूर्वक बनाई गई।',
                'updated' => 'भूमिका सफलतापूर्वक अपडेट की गई।',
                'deleted' => 'भूमिका सफलतापूर्वक हटाई गई।',
                'permissions-required' => 'permission_type के custom होने पर permissions फ़ील्ड आवश्यक है।',
                'cannot-delete-in-use' => 'यह भूमिका एक या अधिक व्यवस्थापकों को असाइन है और इसे हटाया नहीं जा सकता।',
                'cannot-delete-last-role' => 'कम से कम एक भूमिका आवश्यक है — अंतिम शेष भूमिका हटाई नहीं जा सकती।',
                'cannot-change-last-all-access' => 'एकमात्र पूर्ण-पहुँच भूमिका को कस्टम भूमिका में नहीं बदला जा सकता।',
                'delete-failed' => 'भूमिका हटाई नहीं जा सकी।',
                'no-permission' => 'आपको भूमिकाएँ प्रबंधित करने की अनुमति नहीं है।',
            ],

            'currency' => [
                'not-found' => 'मुद्रा नहीं मिली।',
                'created' => 'मुद्रा सफलतापूर्वक बनाई गई।',
                'updated' => 'मुद्रा सफलतापूर्वक अपडेट की गई।',
                'deleted' => 'मुद्रा सफलतापूर्वक हटाई गई।',
                'code-required' => 'code फ़ील्ड आवश्यक है।',
                'code-size' => 'कोड में ठीक 3 वर्णमाला अक्षर होने चाहिए।',
                'code-unique' => 'यह कोड पहले ही लिया जा चुका है।',
                'name-required' => 'name फ़ील्ड आवश्यक है।',
                'cannot-delete-last' => 'कम से कम एक मुद्रा आवश्यक है — अंतिम शेष मुद्रा हटाई नहीं जा सकती।',
                'cannot-delete-channel-base' => 'यह मुद्रा एक या अधिक चैनलों की आधार मुद्रा है और इसे हटाया नहीं जा सकता।',
                'delete-failed' => 'मुद्रा हटाई नहीं जा सकी।',
                'no-permission' => 'आपको मुद्राएँ प्रबंधित करने की अनुमति नहीं है।',

                'mass-delete-success' => 'मुद्राएँ सफलतापूर्वक हटाई गईं।',
                'mass-delete-indices-required' => 'indices फ़ील्ड आवश्यक है और यह एक गैर-रिक्त सरणी होनी चाहिए।',
            ],

            'channel' => [
                'not-found' => 'चैनल नहीं मिला।',

                'created' => 'चैनल सफलतापूर्वक बनाया गया।',
                'updated' => 'चैनल सफलतापूर्वक अपडेट किया गया।',
                'deleted' => 'चैनल सफलतापूर्वक हटाया गया।',
                'code-unique' => 'यह कोड पहले ही लिया जा चुका है।',
                'hostname-unique' => 'यह होस्टनाम पहले ही लिया जा चुका है।',
                'default-locale-mismatch' => 'default_locale_id लोकेल सूची में होना चाहिए।',
                'base-currency-mismatch' => 'base_currency_id मुद्रा सूची में होना चाहिए।',
                'unknown-locale' => 'एक या अधिक लोकेल id मौजूद नहीं हैं।',
                'unknown-currency' => 'एक या अधिक मुद्रा id मौजूद नहीं हैं।',
                'unknown-inventory-source' => 'एक या अधिक inventory_source id मौजूद नहीं हैं।',
                'unknown-root-category' => 'root_category_id मौजूद नहीं है।',
                'cannot-delete-last' => 'कम से कम एक चैनल आवश्यक है — अंतिम शेष चैनल हटाया नहीं जा सकता।',
                'cannot-delete-default' => 'डिफ़ॉल्ट एप्लिकेशन चैनल हटाया नहीं जा सकता।',
                'delete-failed' => 'चैनल हटाया नहीं जा सका।',
                'no-permission' => 'आपको चैनल प्रबंधित करने की अनुमति नहीं है।',
            ],

            'user' => [
                'not-found' => 'व्यवस्थापक उपयोगकर्ता नहीं मिला।',
                'created' => 'व्यवस्थापक उपयोगकर्ता सफलतापूर्वक बनाया गया।',
                'updated' => 'व्यवस्थापक उपयोगकर्ता सफलतापूर्वक अपडेट किया गया।',
                'deleted' => 'व्यवस्थापक उपयोगकर्ता सफलतापूर्वक हटाया गया।',
                'name-required' => 'name फ़ील्ड आवश्यक है।',
                'email-required' => 'email फ़ील्ड आवश्यक है।',
                'email-invalid' => 'ईमेल एक मान्य ईमेल पता होना चाहिए।',
                'email-unique' => 'यह ईमेल पहले ही लिया जा चुका है।',
                'password-required' => 'password फ़ील्ड आवश्यक है।',
                'password-min' => 'पासवर्ड कम से कम 6 वर्णों का होना चाहिए।',
                'role-required' => 'role_id फ़ील्ड आवश्यक है।',
                'role-not-found' => 'चयनित भूमिका मौजूद नहीं है।',
                'status-invalid' => 'स्थिति 0 या 1 होनी चाहिए।',
                'cannot-delete-self' => 'आप अपना स्वयं का व्यवस्थापक खाता नहीं हटा सकते।',
                'cannot-delete-last-admin' => 'कम से कम एक व्यवस्थापक उपयोगकर्ता आवश्यक है — अंतिम शेष व्यवस्थापक हटाया नहीं जा सकता।',
                'cannot-change' => 'अंतिम पूर्ण-पहुँच व्यवस्थापक का :name बदला नहीं जा सकता।',
                'delete-failed' => 'व्यवस्थापक उपयोगकर्ता हटाया नहीं जा सका।',
                'no-permission' => 'आपको एडमिन उपयोगकर्ताओं को प्रबंधित करने की अनुमति नहीं है।',
                'self-delete-password-required' => 'अपना स्वयं का खाता हटाने के लिए password फ़ील्ड आवश्यक है।',
                'self-delete-incorrect-password' => 'पासवर्ड गलत है।',
                'self-deleted' => 'आपका व्यवस्थापक खाता हटा दिया गया है।',
            ],

            'data-transfer' => [
                'import' => [
                    'not-found' => 'आयात नहीं मिला।',
                    'deleted' => 'आयात सफलतापूर्वक हटाया गया।',
                    'cancelled' => 'आयात सफलतापूर्वक रद्द किया गया।',
                    'cannot-cancel' => 'केवल लंबित या प्रोसेसिंग आयात ही रद्द किए जा सकते हैं। वर्तमान स्थिति: :state।',
                    'no-permission' => 'आपको डेटा-स्थानांतरण आयात प्रबंधित करने की अनुमति नहीं है।',
                    'delete-failed' => 'आयात हटाया नहीं जा सका।',
                    'created' => 'आयात सफलतापूर्वक बनाया गया।',
                    'updated' => 'आयात सफलतापूर्वक अपडेट किया गया।',
                    'type-invalid' => 'चयनित प्रकार अमान्य है।',
                    'action-invalid' => 'क्रिया इनमें से एक होनी चाहिए: append, delete।',
                    'validation-strategy-invalid' => 'सत्यापन रणनीति इनमें से एक होनी चाहिए: stop-on-errors, skip-errors।',
                    'allowed-errors-invalid' => 'अनुमत त्रुटियाँ 0 या उससे अधिक का पूर्णांक होनी चाहिए।',
                    'field-separator-required' => 'फ़ील्ड विभाजक आवश्यक है।',
                    'file-required' => 'एक फ़ाइल आवश्यक है।',
                    'file-invalid-type' => 'फ़ाइल इनमें से एक होनी चाहिए: csv, xls, xlsx, xml।',
                    'nothing-to-import' => 'आयात करने के लिए कुछ नहीं है।',
                    'not-valid' => 'आयात मान्य नहीं है।',
                    'setup-queue-error' => 'क्यू कनेक्शन sync पर सेट है; आयात को पृष्ठभूमि में संसाधित करने के लिए एक वास्तविक क्यू कॉन्फ़िगर करें।',
                    'file-not-found' => 'डाउनलोड के लिए कोई फ़ाइल उपलब्ध नहीं है।',
                    'error-report-not-found' => 'डाउनलोड के लिए कोई त्रुटि रिपोर्ट उपलब्ध नहीं है।',
                    'sample-not-found' => 'अनुरोधित प्रकार और प्रारूप के लिए कोई नमूना फ़ाइल उपलब्ध नहीं है।',
                    'sample-format-invalid' => 'नमूना प्रारूप इनमें से एक होना चाहिए: csv, xls, xlsx, xml।',
                    'images-archive-too-large' => 'छवि आर्काइव में अधिकतम :entries फ़ाइलें हो सकती हैं और खोलने पर अधिकतम :size MB हो सकता है।',
                ],
            ],
        ],

        'cms' => [
            'page' => [
                'not-found' => 'CMS पेज नहीं मिला।',

                'created' => 'CMS पेज सफलतापूर्वक बनाया गया।',
                'url-key-required' => 'url_key फ़ील्ड आवश्यक है।',
                'url-key-unique' => 'यह url_key पहले ही लिया जा चुका है।',
                'url-key-invalid' => 'url_key का प्रारूप अमान्य है। केवल छोटे अक्षर, संख्याएँ और हाइफ़न का उपयोग करें।',
                'page-title-required' => 'page_title फ़ील्ड आवश्यक है।',
                'html-content-required' => 'html_content फ़ील्ड आवश्यक है।',
                'channels-required' => 'कम से कम एक चैनल चुना जाना चाहिए।',
                'channels-invalid' => 'एक या अधिक चैनल अमान्य हैं।',

                'updated' => 'CMS पेज सफलतापूर्वक अपडेट किया गया।',

                'deleted' => 'CMS पेज सफलतापूर्वक हटाया गया।',
                'delete-failed' => 'CMS पेज हटाया नहीं जा सका।',

                'mass-deleted' => 'CMS पेज सफलतापूर्वक हटाए गए।',
                'indices-required' => 'indices फ़ील्ड आवश्यक है और यह एक गैर-रिक्त सरणी होनी चाहिए।',
                'indices-invalid' => 'indices फ़ील्ड CMS पेज ID की सरणी होनी चाहिए।',

                'no-permission' => 'आपको CMS पेज प्रबंधित करने की अनुमति नहीं है।',
            ],
        ],

        'marketing' => [
            'catalog-rule' => [
                'not-found' => 'कैटलॉग नियम नहीं मिला।',
                'created' => 'कैटलॉग नियम सफलतापूर्वक बनाया गया।',
                'updated' => 'कैटलॉग नियम सफलतापूर्वक अपडेट किया गया।',
                'deleted' => 'कैटलॉग नियम सफलतापूर्वक हटाया गया।',
                'delete-failed' => 'कैटलॉग नियम हटाया नहीं जा सका।',
                'mass-delete-indices-required' => 'सामूहिक हटाने के लिए कोई कैटलॉग नियम ID नहीं दी गई।',
                'mass-delete-success' => 'चयनित कैटलॉग नियम सफलतापूर्वक हटाए गए।',
                'no-permission' => 'आपको कैटलॉग नियम प्रबंधित करने की अनुमति नहीं है।',
            ],

            'campaign' => [
                'not-found' => 'अभियान नहीं मिला।',
                'created' => 'अभियान सफलतापूर्वक बनाया गया।',
                'updated' => 'अभियान सफलतापूर्वक अपडेट किया गया।',
                'deleted' => 'अभियान सफलतापूर्वक हटाया गया।',
                'delete-failed' => 'अभियान हटाया नहीं जा सका।',
                'no-permission' => 'आपको अभियान प्रबंधित करने की अनुमति नहीं है।',

                'send' => [
                    'id-required' => 'अभियान id आवश्यक है।',
                    'inactive' => 'निष्क्रिय अभियान नहीं भेजा जा सकता। पहले इसे सक्षम करें।',
                    'queued' => 'अभियान :count प्राप्तकर्ता(ओं) के लिए क्यू में डाला गया।',
                ],
            ],

            'sitemap' => [
                'not-found' => 'साइटमैप नहीं मिला।',
                'created' => 'साइटमैप सफलतापूर्वक बनाया गया।',
                'updated' => 'साइटमैप सफलतापूर्वक अपडेट किया गया।',
                'deleted' => 'साइटमैप सफलतापूर्वक हटाया गया।',
                'delete-failed' => 'साइटमैप हटाया नहीं जा सका।',
                'no-permission' => 'आपको साइटमैप प्रबंधित करने की अनुमति नहीं है।',

                'generate' => [
                    'id-required' => 'साइटमैप id आवश्यक है।',
                    'no-channels' => 'यह साइटमैप किसी चैनल को कवर नहीं करता, इसलिए जनरेट करने के लिए कुछ नहीं है। पहले इसे कम से कम एक चैनल असाइन करें।',
                    'success' => 'साइटमैप सफलतापूर्वक पुनः जनरेट किया गया।',
                    'failed' => 'साइटमैप जनरेशन विफल: :message',
                ],
            ],

            'cart-rule' => [
                'not-found' => 'कार्ट नियम नहीं मिला।',
                'id-required' => 'कार्ट नियम id आवश्यक है।',
                'copy-of' => ':value की प्रति',
                'copied' => 'कार्ट नियम सफलतापूर्वक कॉपी किया गया।',
                'created' => 'कार्ट नियम सफलतापूर्वक बनाया गया।',
                'updated' => 'कार्ट नियम सफलतापूर्वक अपडेट किया गया।',
                'deleted' => 'कार्ट नियम सफलतापूर्वक हटाया गया।',
                'delete-failed' => 'कार्ट नियम हटाया नहीं जा सका।',
                'no-permission' => 'आपको कार्ट नियम प्रबंधित करने की अनुमति नहीं है।',
                'mass-delete-success' => 'कार्ट नियम सफलतापूर्वक हटाए गए।',
                'mass-delete-indices-required' => 'indices फ़ील्ड आवश्यक है और यह एक गैर-रिक्त सरणी होनी चाहिए।',
                'action-type-invalid' => 'action_type इनमें से एक होना चाहिए: by_percent, by_fixed, cart_fixed, buy_x_get_y।',
                'coupon-code-required' => 'coupon_type=1 और use_auto_generation=0 होने पर coupon_code आवश्यक है।',
                'coupon-code-unique' => 'यह कूपन कोड पहले से उपयोग में है।',
            ],

            'template' => [
                'campaign-associated' => 'यह ईमेल टेम्पलेट एक अभियान द्वारा उपयोग किया जा रहा है, इसलिए इसे हटाया नहीं जा सकता। पहले इसे अभियान से हटाएँ।',
                'not-found' => 'ईमेल टेम्पलेट नहीं मिला।',
                'created' => 'ईमेल टेम्पलेट सफलतापूर्वक बनाया गया।',
                'updated' => 'ईमेल टेम्पलेट सफलतापूर्वक अपडेट किया गया।',
                'deleted' => 'ईमेल टेम्पलेट सफलतापूर्वक हटाया गया।',
                'delete-failed' => 'ईमेल टेम्पलेट हटाया नहीं जा सका।',
                'status-invalid' => 'स्थिति इनमें से एक होनी चाहिए: active, inactive, draft।',
                'no-permission' => 'आपको ईमेल टेम्पलेट प्रबंधित करने की अनुमति नहीं है।',
            ],

            'event' => [
                'campaign-associated' => 'यह इवेंट एक अभियान द्वारा उपयोग किया जा रहा है, इसलिए इसे हटाया नहीं जा सकता। पहले इसे अभियान से हटाएँ।',
                'not-found' => 'मार्केटिंग इवेंट नहीं मिला।',
                'created' => 'मार्केटिंग इवेंट सफलतापूर्वक बनाया गया।',
                'updated' => 'मार्केटिंग इवेंट सफलतापूर्वक अपडेट किया गया।',
                'deleted' => 'मार्केटिंग इवेंट सफलतापूर्वक हटाया गया।',
                'delete-failed' => 'मार्केटिंग इवेंट हटाया नहीं जा सका।',
                'no-permission' => 'आपको मार्केटिंग इवेंट प्रबंधित करने की अनुमति नहीं है।',
            ],

            'search-synonym' => [
                'not-found' => 'खोज पर्यायवाची नहीं मिला।',
                'created' => 'खोज पर्यायवाची सफलतापूर्वक बनाया गया।',
                'updated' => 'खोज पर्यायवाची सफलतापूर्वक अपडेट किया गया।',
                'deleted' => 'खोज पर्यायवाची सफलतापूर्वक हटाया गया।',
                'mass-deleted' => 'खोज पर्यायवाची सफलतापूर्वक हटाए गए।',
                'delete-failed' => 'खोज पर्यायवाची हटाया नहीं जा सका।',
                'indices-required' => 'indices फ़ील्ड आवश्यक है और यह खोज पर्यायवाची ID की एक गैर-रिक्त सरणी होनी चाहिए।',
                'no-permission' => 'आपको खोज पर्यायवाची प्रबंधित करने की अनुमति नहीं है।',
            ],

            'url-rewrite' => [
                'not-found' => 'URL रीराइट नहीं मिला।',
                'created' => 'URL रीराइट सफलतापूर्वक बनाया गया।',
                'updated' => 'URL रीराइट सफलतापूर्वक अपडेट किया गया।',
                'deleted' => 'URL रीराइट सफलतापूर्वक हटाया गया।',
                'delete-failed' => 'URL रीराइट हटाया नहीं जा सका।',
                'mass-deleted' => 'URL रीराइट सफलतापूर्वक हटाए गए।',
                'indices-required' => 'कम से कम एक URL रीराइट id आवश्यक है।',
                'no-permission' => 'आपको URL रीराइट प्रबंधित करने की अनुमति नहीं है।',
            ],

            'cart-rule-coupon' => [
                'cart-rule-not-found' => 'कार्ट नियम नहीं मिला।',
                'not-found' => 'कूपन नहीं मिला।',
                'code-taken' => 'इस कोड वाला कूपन पहले से मौजूद है।',
                'format-invalid' => 'प्रारूप इनमें से एक होना चाहिए: alphabetic, alphanumeric, numeric।',
                'indices-required' => 'indices फ़ील्ड आवश्यक है और यह कूपन ID की एक गैर-रिक्त सरणी होनी चाहिए।',
                'created' => 'कूपन सफलतापूर्वक बनाया गया।',
                'generated' => ':count कूपन सफलतापूर्वक जनरेट किए गए।',
                'deleted' => 'कूपन सफलतापूर्वक हटाया गया।',
                'mass-deleted' => ':count कूपन सफलतापूर्वक हटाए गए।',
                'no-permission' => 'आपको कार्ट नियम कूपन प्रबंधित करने की अनुमति नहीं है।',
            ],

            'subscriber' => [
                'not-found' => 'न्यूज़लेटर सब्सक्राइबर नहीं मिला।',
                'updated' => 'सदस्यता सफलतापूर्वक अपडेट की गई।',
                'deleted' => 'सदस्यता सफलतापूर्वक हटाई गई।',
                'is-subscribed-required' => 'is_subscribed फ़ील्ड आवश्यक है।',
                'no-permission' => 'आपको न्यूज़लेटर सब्सक्राइबर प्रबंधित करने की अनुमति नहीं है।',
            ],

            'search-term' => [
                'not-found' => 'खोज शब्द नहीं मिला।',
                'updated' => 'खोज शब्द सफलतापूर्वक अपडेट किया गया।',
                'deleted' => 'खोज शब्द सफलतापूर्वक हटाया गया।',
                'mass-deleted' => 'खोज शब्द सफलतापूर्वक हटाए गए।',
                'term-required' => 'term फ़ील्ड आवश्यक है।',
                'redirect-url-invalid' => 'redirect_url एक मान्य URL होना चाहिए।',
                'indices-required' => 'indices में कम से कम एक ID प्रदान की जानी चाहिए।',
                'no-permission' => 'आपको खोज शब्द प्रबंधित करने की अनुमति नहीं है।',
            ],
        ],
        'customer-api-sessions' => [
            'menu' => [
                'title' => 'API सत्र',
            ],

            'acl' => [
                'title' => 'API सत्र',
                'delete' => 'रद्द करें',
            ],

            'index' => [
                'title' => 'ग्राहक API सेशन',
                'info' => 'वेब, मोबाइल, POS या डेस्कटॉप ऐप से स्टोरफ़्रंट API में साइन इन किए हुए ग्राहक। सेशन रद्द करने पर वह डिवाइस तुरंत साइन आउट हो जाता है।',
            ],

            'fields' => [
                'customer' => 'ग्राहक',
                'email' => 'ईमेल',
                'channel' => 'चैनल',
            ],

            'datagrid' => [
                'revoke-all' => 'ग्राहक के सभी सेशन रद्द करें',
            ],

            'card' => [
                'title' => 'API सेशन (:count)',
                'empty' => 'इस ग्राहक का कोई सक्रिय API सेशन नहीं है।',
            ],

            'errors' => [
                'no-permission' => 'आपको ग्राहक API सेशन प्रबंधित करने की अनुमति नहीं है।',
            ],
        ],

        'customer-auth' => [
            'configuration' => [
                'title' => 'ग्राहक प्रमाणीकरण',
                'info' => 'स्टोरफ़्रंट ग्राहक लॉगिन के लिए टोकन अवधि, सीमाएँ और सेशन नियम।',

                'tokens' => [
                    'title' => 'टोकन की अवधि',
                    'info' => 'एक्सेस टोकन, रिफ्रेश टोकन और सेशन कितने समय तक मान्य रहते हैं। बदलाव बाद में जारी होने वाले टोकन पर लागू होते हैं।',
                    'access-token-ttl' => 'एक्सेस टोकन की अवधि (मिनट)',
                    'access-token-ttl-info' => '5 से 60 मिनट के बीच। डिफ़ॉल्ट 15।',
                    'refresh-token-ttl' => 'रिफ्रेश टोकन की अवधि (दिन)',
                    'refresh-token-ttl-info' => '1 से 90 दिन के बीच। डिफ़ॉल्ट 30।',
                    'session-lifetime' => 'सेशन की अधिकतम अवधि (दिन)',
                    'session-lifetime-info' => '1 से 365 दिन के बीच। डिफ़ॉल्ट 90। इसके बाद एडमिन को फिर से लॉगिन करना होगा, चाहे सेशन कितनी भी बार रिफ्रेश हुआ हो।',
                    'max-sessions' => 'प्रति ग्राहक अधिकतम सक्रिय सेशन',
                    'max-sessions-info' => 'सीमा पूरी होने पर सबसे पुराना सेशन समाप्त हो जाता है। 0 से 100 के बीच। 0 का अर्थ है कोई सीमा नहीं।',
                ],

                'rate-limits' => [
                    'title' => 'रेट लिमिट',
                    'info' => 'प्रति लॉग-इन ग्राहक प्रति मिनट अधिकतम स्टोरफ़्रंट API अनुरोध।',
                    'requests-per-minute' => 'प्रति लॉग-इन ग्राहक प्रति मिनट अनुरोध',
                    'requests-per-minute-info' => '1 से 100000 के बीच। डिफ़ॉल्ट 120।',
                ],

                'login-protection' => [
                    'title' => 'लॉगिन सुरक्षा',
                    'info' => 'क्लाइंट को प्रतीक्षा कराने से पहले कितने विफल लॉगिन और टोकन रिफ़्रेश अनुमत हैं।',
                    'login-max-attempts' => 'लॉकआउट से पहले विफल लॉगिन',
                    'login-max-attempts-info' => 'प्रति ईमेल और IP पता। 3 से 20 के बीच। डिफ़ॉल्ट 5।',
                    'login-lockout-seconds' => 'लॉगिन लॉकआउट (सेकंड)',
                    'login-lockout-seconds-info' => '60 से 3600 के बीच। डिफ़ॉल्ट 60।',
                    'refresh-requests-per-minute' => 'प्रति सेशन प्रति मिनट टोकन रिफ़्रेश',
                    'refresh-requests-per-minute-info' => '1 से 60 के बीच। डिफ़ॉल्ट 10।',
                ],

                'security' => [
                    'title' => 'सुरक्षा',
                    'info' => 'ग्राहक के अन्य सेशन किससे समाप्त होते हैं।',
                    'revoke-sessions-on-password-change' => 'पासवर्ड बदलने पर सेशन रद्द करें',
                ],

                'retention' => [
                    'title' => 'डेटा प्रतिधारण',
                    'info' => 'समाप्त ग्राहक सेशन कितने समय तक रखे जाते हैं।',
                    'session-prune-days' => 'समाप्त सेशन रखें (दिन)',
                    'session-prune-days-info' => 'रद्द या समाप्त सेशन इतने दिनों बाद हटा दिए जाते हैं। 1 से 365 के बीच। डिफ़ॉल्ट 30।',
                ],
            ],
        ],
        'auth' => [
            'configuration' => [
                'title' => 'एडमिन प्रमाणीकरण',
                'info' => 'एडमिन API लॉगिन और API टोकन के लिए टोकन की अवधि, रेट लिमिट और सुरक्षा नियम।',

                'tokens' => [
                    'title' => 'टोकन की अवधि',
                    'info' => 'एक्सेस टोकन, रिफ्रेश टोकन और सेशन कितने समय तक मान्य रहते हैं। बदलाव बाद में जारी होने वाले टोकन पर लागू होते हैं।',
                    'access-token-ttl' => 'एक्सेस टोकन की अवधि (मिनट)',
                    'access-token-ttl-info' => '5 से 60 मिनट के बीच। डिफ़ॉल्ट 15।',
                    'refresh-token-ttl' => 'रिफ्रेश टोकन की अवधि (दिन)',
                    'refresh-token-ttl-info' => '1 से 90 दिन के बीच। डिफ़ॉल्ट 30।',
                    'session-lifetime' => 'सेशन की अधिकतम अवधि (दिन)',
                    'session-lifetime-info' => '1 से 365 दिन के बीच। डिफ़ॉल्ट 90। इसके बाद एडमिन को फिर से लॉगिन करना होगा, चाहे सेशन कितनी भी बार रिफ्रेश हुआ हो।',
                ],

                'rate-limits' => [
                    'title' => 'रेट लिमिट',
                    'info' => 'प्रति मिनट अधिकतम एडमिन API अनुरोध।',
                    'session-requests-per-minute' => 'प्रति लॉग-इन एडमिन प्रति मिनट अनुरोध',
                    'api-token-requests-per-minute' => 'प्रति API टोकन प्रति मिनट अनुरोध',
                    'session-requests-per-minute-info' => '1 से 100000 के बीच। डिफ़ॉल्ट 60।',
                    'api-token-requests-per-minute-info' => '1 से 100000 के बीच। डिफ़ॉल्ट 60।',
                    'unauthenticated-requests-per-minute' => 'बिना टोकन प्रति IP पता प्रति मिनट अनुरोध',
                    'unauthenticated-requests-per-minute-info' => '10 से 1000 के बीच। डिफ़ॉल्ट 60। इसमें लॉगिन, टू-फ़ैक्टर, रिफ़्रेश और पासवर्ड अनुरोध शामिल हैं।',
                ],

                'security' => [
                    'title' => 'सुरक्षा',
                    'info' => 'रद्द करने के नियम, API टोकन की समाप्ति और डोमेन बाइंडिंग।',
                    'revoke-sessions-on-password-change' => 'पासवर्ड बदलने पर सेशन रद्द करें',
                    'revoke-api-tokens-on-password-change' => 'पासवर्ड बदलने पर API टोकन रद्द करें',
                    'allow-non-expiring-api-tokens' => 'बिना समाप्ति वाले API टोकन की अनुमति दें',
                    'domain-binding-mode' => 'डोमेन बाइंडिंग मोड',
                    'domain-binding-mode-info' => 'DNS जाँचता है कि अनुरोध उस IP पते से आया है जिस पर टोकन का डोमेन रिज़ॉल्व होता है। Origin, Origin या Referer हेडर जाँचता है और केवल ब्राउज़र के लिए उपयुक्त है।',
                    'domain-binding-dns' => 'DNS (क्लाइंट का IP पता)',
                    'domain-binding-origin' => 'Origin हेडर',
                ],

                'login-protection' => [
                    'title' => 'लॉगिन सुरक्षा',
                    'info' => 'क्लाइंट को प्रतीक्षा कराने से पहले कितने विफल लॉगिन, गलत टू-फ़ैक्टर कोड और पासवर्ड अनुरोध अनुमत हैं।',
                    'login-max-attempts' => 'लॉकआउट से पहले विफल लॉगिन',
                    'login-max-attempts-info' => 'प्रति ईमेल और IP पता। 3 से 20 के बीच। डिफ़ॉल्ट 5।',
                    'login-lockout-seconds' => 'लॉगिन लॉकआउट (सेकंड)',
                    'login-lockout-seconds-info' => '60 से 3600 के बीच। डिफ़ॉल्ट 60।',
                    'two-factor-challenge-lifetime' => 'टू-फ़ैक्टर चुनौती की अवधि (मिनट)',
                    'two-factor-challenge-lifetime-info' => '1 से 15 के बीच। डिफ़ॉल्ट 5।',
                    'two-factor-max-failures' => 'प्रति मिनट गलत टू-फ़ैक्टर कोड',
                    'two-factor-max-failures-info' => 'प्रति एडमिन, सभी चुनौतियों में। 3 से 20 के बीच। डिफ़ॉल्ट 5।',
                    'password-requests-per-minute' => 'प्रति मिनट पासवर्ड अनुरोध',
                    'password-requests-per-minute-info' => 'प्रति IP पता पासवर्ड भूलने और रीसेट करने के अनुरोध। 1 से 20 के बीच। डिफ़ॉल्ट 5।',
                ],

                'retention' => [
                    'title' => 'डेटा प्रतिधारण',
                    'info' => 'समाप्त सेशन और API ऑडिट इतिहास कितने समय तक रखे जाते हैं।',
                    'session-prune-days' => 'समाप्त सेशन रखें (दिन)',
                    'session-prune-days-info' => 'रद्द या समाप्त सेशन इतने दिनों बाद हटा दिए जाते हैं। 1 से 365 के बीच। डिफ़ॉल्ट 30।',
                    'audit-retention-days' => 'API ऑडिट इतिहास रखें (दिन)',
                    'audit-retention-days-info' => 'पुरानी प्रविष्टियाँ प्रतिदिन हटाई जाती हैं। 0 से 3650 के बीच। खाली या 0 उन्हें हमेशा रखता है।',
                ],
            ],

            'errors' => [
                'unauthenticated' => 'आप प्रमाणित नहीं हैं।',
                'access-token-expired' => 'एक्सेस टोकन की अवधि समाप्त हो गई है। जारी रखने के लिए इसे रिफ्रेश करें।',
                'api-token-expired' => 'API टोकन की अवधि समाप्त हो गई है।',
                'refresh-token-invalid' => 'रिफ्रेश टोकन अमान्य है या उसकी अवधि समाप्त हो गई है। कृपया फिर से लॉगिन करें।',
                'api-token-revoked' => 'API टोकन रद्द कर दिया गया है।',
                'api-token-domain-mismatch' => 'इस डोमेन से API टोकन का उपयोग नहीं किया जा सकता।',
                'access-token-required' => 'इस कार्य के लिए लॉगिन सत्र का एक्सेस टोकन आवश्यक है; API टोकन इसका उपयोग नहीं कर सकते।',
                'two-factor-required' => 'लॉगिन पूरा करने के लिए अपने ऑथेंटिकेटर ऐप का 6 अंकों का कोड, या अपने बैकअप कोड में से कोई एक दर्ज करें।',
                'too-many-login-attempts' => 'बहुत अधिक लॉगिन प्रयास। कृपया :seconds सेकंड बाद पुनः प्रयास करें।',
                'two-factor-challenge-invalid' => 'टू-फ़ैक्टर चुनौती अमान्य है या उसकी समय-सीमा समाप्त हो गई है। कृपया फिर से लॉगिन करें।',
                'two-factor-attempts-exceeded' => 'बहुत अधिक गलत कोड। कृपया फिर से लॉगिन करें।',
                'password-reset-token-invalid' => 'यह पासवर्ड रीसेट टोकन अमान्य है या इसकी समय-सीमा समाप्त हो गई है।',
                'too-many-password-requests' => 'बहुत अधिक पासवर्ड अनुरोध। कृपया :seconds सेकंड बाद फिर से प्रयास करें।',
                'rate-limit-exceeded' => 'बहुत अधिक प्रयास।',
                'no-permission' => 'आपको एडमिन API सत्र प्रबंधित करने की अनुमति नहीं है।',
            ],

            'sessions' => [
                'logged-out' => 'आप लॉग आउट हो गए हैं।',
                'logged-out-all' => 'आप सभी सत्रों से लॉग आउट हो गए हैं।',
                'not-found' => 'सत्र नहीं मिला।',
                'revoked' => 'सत्र रद्द कर दिया गया है।',
                'revoked-for-admin' => 'रद्द किए गए सत्र: :count।',
            ],

            'password' => [
                'reset' => 'आपका पासवर्ड रीसेट कर दिया गया है। कृपया फिर से लॉगिन करें।',
            ],
        ],

        'api-tokens' => [
            'menu' => [
                'title' => 'API टोकन',
            ],

            'acl' => [
                'title' => 'API टोकन',
                'create' => 'बनाएँ',
                'delete' => 'रद्द करें',
                'manage-others' => 'अन्य एडमिन के टोकन प्रबंधित करें',
            ],

            'index' => [
                'title' => 'API टोकन',
                'info' => 'ERP, PIM, वेयरहाउस या अकाउंटिंग सिस्टम जैसे सर्वर इंटीग्रेशन API टोकन से प्रमाणित होते हैं। हर टोकन एक एडमिन के रूप में उसकी भूमिका की अनुमतियों के साथ काम करता है और केवल उसी डोमेन से चलता है जिससे वह जुड़ा है।',
                'create-btn' => 'API टोकन बनाएँ',
            ],

            'create' => [
                'title' => 'API टोकन बनाएँ',
                'back-btn' => 'वापस',
                'save-btn' => 'टोकन बनाएँ',
                'general' => 'सामान्य',
                'expiry' => 'समाप्ति',
                'admin-info' => 'टोकन इस एडमिन के रूप में काम करता है और उसके पास ठीक उसकी भूमिका की अनुमतियाँ होती हैं।',
                'domain-info' => 'वह होस्ट जिससे इंटीग्रेशन कॉल करता है, जैसे erp.example.com। URL को उसके होस्ट तक छोटा कर दिया जाता है। IP पते और वाइल्डकार्ड स्वीकार नहीं हैं, और डोमेन बाद में बदला नहीं जा सकता।',
                'expires-on-date' => 'किसी तारीख को समाप्त होगा',
                'never-expires' => 'कभी समाप्त नहीं होगा',
                'no-expiry-warning' => 'बिना समाप्ति वाला टोकन रद्द किए जाने तक काम करता है। इसे सीक्रेट स्टोर में रखें और ज़रूरत खत्म होते ही रद्द करें।',
                'no-expiry-confirm' => 'मैं समझता/समझती हूँ कि यह टोकन कभी समाप्त नहीं होगा।',
                'no-admins' => 'ऐसा कोई एडमिन नहीं है जिसके लिए आप API टोकन बना सकें।',
            ],

            'view' => [
                'title' => 'API टोकन',
                'back-btn' => 'वापस',
                'save-btn' => 'सहेजें',
                'revoke-btn' => 'रद्द करें',
                'details' => 'विवरण',
                'usage' => 'उपयोग',
                'token-once-title' => 'अपना API टोकन अभी कॉपी करें',
                'token-once-info' => 'टोकन केवल इसी बार दिखाया जाता है। इसे सीक्रेट स्टोर में रखें; खो जाने पर इसे रद्द करके नया बनाएँ।',
                'copy-btn' => 'कॉपी करें',
                'copied' => 'टोकन कॉपी हो गया।',
                'usage-info' => 'इसे REST और GraphQL अनुरोधों के Authorization हेडर में Bearer टोकन के रूप में भेजें।',
                'not-yet' => 'अभी नहीं',
                'revoke-confirm' => 'यह API टोकन रद्द करें? इसका उपयोग करने वाला इंटीग्रेशन तुरंत काम करना बंद कर देगा।',
            ],

            'fields' => [
                'id' => 'ID',
                'name' => 'नाम',
                'admin' => 'एडमिन',
                'token' => 'टोकन',
                'domain' => 'डोमेन',
                'expiry' => 'समाप्ति',
                'expires-at' => 'समाप्ति तिथि',
                'no-expiry-confirmation' => 'बिना समाप्ति की पुष्टि',
                'status' => 'स्थिति',
                'created-by' => 'बनाने वाला',
                'created-at' => 'बनाने की तिथि',
                'last-used-at' => 'अंतिम उपयोग',
                'last-ip-address' => 'अंतिम IP पता',
                'last-user-agent' => 'अंतिम यूज़र एजेंट',
                'last-channel' => 'अंतिम चैनल',
                'resolved-ips' => 'रिज़ॉल्व किए गए IP पते',
                'resolved-at' => 'रिज़ॉल्व करने की तिथि',
                'domain-mismatches' => 'अन्य डोमेन से अनुरोध',
                'last-domain-mismatch-at' => 'अन्य डोमेन से अंतिम अनुरोध',
                'revoked-at' => 'रद्द करने की तिथि',
                'revoked-by' => 'रद्द करने वाला',
            ],

            'datagrid' => [
                'never' => 'कभी नहीं',
                'console' => 'कंसोल कमांड',
                'view' => 'देखें',
                'revoke' => 'रद्द करें',
            ],

            'status' => [
                'active' => 'सक्रिय',
                'expired' => 'समाप्त',
                'revoked' => 'रद्द',
            ],

            'channels' => [
                'rest' => 'REST',
                'graphql' => 'GraphQL',
            ],

            'messages' => [
                'created' => 'API टोकन बना दिया गया है।',
                'updated' => 'API टोकन अपडेट कर दिया गया है।',
                'revoked' => 'API टोकन रद्द कर दिया गया है।',
            ],

            'errors' => [
                'no-permission' => 'आपको API टोकन प्रबंधित करने की अनुमति नहीं है।',
                'not-found' => 'API टोकन नहीं मिला।',
                'cannot-create-for-admin' => 'आप इस एडमिन के लिए API टोकन नहीं बना सकते: उसकी भूमिका में ऐसी अनुमतियाँ हैं जो आपकी भूमिका में नहीं हैं, या आप अन्य एडमिन के टोकन प्रबंधित नहीं कर सकते।',
                'cannot-manage' => 'आप इस API टोकन को प्रबंधित नहीं कर सकते।',
                'already-revoked' => 'API टोकन पहले ही रद्द हो चुका है।',
                'invalid-name' => 'नाम आवश्यक है और अधिकतम 100 अक्षरों का हो सकता है।',
                'invalid-domain' => 'erp.example.com जैसा डोमेन नाम दर्ज करें। IP पते और वाइल्डकार्ड स्वीकार नहीं हैं।',
                'expiry-required' => 'API टोकन की समाप्ति तिथि होनी चाहिए।',
                'expiry-in-past' => 'समाप्ति तिथि भविष्य की होनी चाहिए।',
            ],
        ],

        'api-sessions' => [
            'menu' => [
                'title' => 'API सत्र',
            ],

            'acl' => [
                'title' => 'API सत्र',
                'delete' => 'रद्द करें',
            ],

            'index' => [
                'title' => 'API सत्र',
                'info' => 'वेब, मोबाइल, POS या डेस्कटॉप ऐप से एडमिन API में साइन इन किए हुए एडमिन। किसी सत्र को रद्द करने से वह डिवाइस तुरंत साइन आउट हो जाता है।',
            ],

            'fields' => [
                'id' => 'ID',
                'admin' => 'एडमिन',
                'device' => 'डिवाइस',
                'client-type' => 'क्लाइंट प्रकार',
                'ip-address' => 'लॉगिन IP पता',
                'user-agent' => 'लॉगिन यूज़र एजेंट',
                'last-ip-address' => 'अंतिम IP पता',
                'last-user-agent' => 'अंतिम यूज़र एजेंट',
                'last-used-at' => 'अंतिम उपयोग',
                'last-channel' => 'अंतिम चैनल',
                'refresh-expires-at' => 'रिफ़्रेश समाप्ति',
                'expires-at' => 'समाप्ति तिथि',
                'status' => 'स्थिति',
                'created-at' => 'लॉगिन समय',
            ],

            'datagrid' => [
                'revoke' => 'रद्द करें',
                'revoke-all' => 'इस एडमिन के सभी सत्र रद्द करें',
            ],

            'client-types' => [
                'web' => 'वेब',
                'mobile' => 'मोबाइल',
                'pos' => 'POS',
                'desktop' => 'डेस्कटॉप',
                'other' => 'अन्य',
            ],

            'errors' => [
                'not-active' => 'यह सत्र पहले ही रद्द या समाप्त हो चुका है।',
            ],
        ],

        'api-audit' => [
            'menu' => [
                'title' => 'API ऑडिट',
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
                'token-kind' => 'टोकन का प्रकार',
                'channel' => 'चैनल',
                'operation' => 'ऑपरेशन',
                'resource' => 'संसाधन',
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

            'token-kinds' => [
                'session' => 'लॉगिन सत्र',
                'api-token' => 'API टोकन',
                'integration' => 'इंटीग्रेशन टोकन (हटाया गया)',
                'customer-session' => 'ग्राहक लॉगिन सेशन',
            ],

            'channels' => [
                'rest' => 'REST',
                'graphql' => 'GraphQL',
                'panel' => 'एडमिन पैनल',
                'cli' => 'कंसोल कमांड',
            ],

            'deleted' => ':count इतिहास रिकॉर्ड हटा दिया गया।',
            'cleanup-input-required' => 'साफ़ करने के लिए दिनों की संख्या या एक तिथि प्रदान करें।',
        ],

        'api-configuration' => [
            'title' => 'एपीआई',
            'info' => 'बैगिस्टो एपीआई और इसके व्यवस्थापक मॉड्यूल के लिए सेटिंग्स।',
        ],
    ],
];
