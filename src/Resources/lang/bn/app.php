<?php

return [
    'graphql' => [
        'cart' => [
            'authentication-required'           => 'প্রমাণীকরণ টোকেন প্রয়োজন',
            'invalid-token'                     => 'অবৈধ বা মেয়াদোত্তীর্ণ প্রমাণীকরণ টোকেন',
            'unauthorized-access'               => 'কার্টে অননুমোদিত অ্যাক্সেস',
            'authenticated-only'                => 'শুধুমাত্র প্রমাণিত ব্যবহারকারীরা তাদের কার্ট আনতে পারেন',
            'merge-requires-auth'               => 'অতিথি মার্জ প্রমাণীকরণ প্রয়োজন',
            'unknown-operation'                 => 'অজানা কার্ট অপারেশন',

            'cart-not-found'                    => 'কার্ট পাওয়া যায়নি',
            'guest-cart-not-found'              => 'অতিথি কার্ট পাওয়া যায়নি',
            'product-not-found'                 => 'পণ্য পাওয়া যায়নি',

            'product-id-quantity-required'      => 'পণ্য আইডি এবং পরিমাণ প্রয়োজন',
            'cart-item-id-quantity-required'    => 'কার্ট আইটেম আইডি এবং পরিমাণ প্রয়োজন',
            'cart-item-id-required'             => 'কার্ট আইটেম আইডি প্রয়োজন',
            'item-ids-required'                 => 'আইটেম আইডি অ্যারে প্রয়োজন',
            'coupon-code-required'              => 'কুপন কোড প্রয়োজন',
            'address-data-required'             => 'দেশ, অঞ্চল এবং পোস্টকোড প্রয়োজন',

            'add-product-failed'                => 'কার্টে পণ্য যোগ করতে ব্যর্থ',
            'update-item-failed'                => 'কার্ট আইটেম আপডেট করতে ব্যর্থ',
            'remove-item-failed'                => 'কার্ট আইটেম সরাতে ব্যর্থ',
            'apply-coupon-failed'               => 'কুপন প্রয়োগ করতে ব্যর্থ',
            'remove-coupon-failed'              => 'কুপন সরাতে ব্যর্থ',
            'move-to-wishlist-failed'           => 'উইশলিস্টে আইটেম সরাতে ব্যর্থ',
            'estimate-shipping-failed'          => 'শিপিং অনুমান করতে ব্যর্থ',

            'product-added-successfully'         => 'পণ্য কার্টে সফলভাবে যোগ করা হয়েছে',
            'guest-cart-merged'                  => 'অতিথি কার্ট সফলভাবে মার্জ করা হয়েছে',
            'using-authenticated-cart'           => 'প্রমাণিত গ্রাহক কার্ট ব্যবহার করা হচ্ছে',
            'cart-item-not-found'                => 'কার্ট আইটেম পাওয়া যায়নি',
            'new-guest-cart-created'             => 'অনন্য সেশন টোকেন সহ নতুন অতিথি কার্ট তৈরি করা হয়েছে',
            'select-items-to-remove'             => 'অনুগ্রহ করে সরাতে আইটেম নির্বাচন করুন',
            'select-items-to-move-wishlist'      => 'অনুগ্রহ করে উইশলিস্টে সরাতে আইটেম নির্বাচন করুন',
            'invalid-or-expired-token'           => 'কার্ট টোকেন অবৈধ বা মেয়াদোত্তীর্ণ। অনুগ্রহ করে নতুন কার্ট তৈরি করুন।',
            'invalid-token-of-login-user'        => 'লগইন ব্যবহারকারীর টোকেন অবৈধ।',
        ],

        'token-verification' => [
            'invalid-operation'                 => 'অবৈধ অপারেশন',
            'invalid-input-data'                => 'অবৈধ ইনপুট ডেটা',
            'token-required'                    => 'টোকেন প্রয়োজন',
            'invalid-token-format'              => 'অবৈধ টোকেন ফর্ম্যাট',
            'token-not-found-or-expired'        => 'টোকেন পাওয়া যায়নি বা মেয়াদ শেষ হয়েছে',
            'customer-not-found'                => 'গ্রাহক পাওয়া যায়নি',
            'customer-account-suspended'        => 'গ্রাহক অ্যাকাউন্ট স্থগিত',
            'error-verifying-token'             => 'টোকেন যাচাই করতে ত্রুটি',
            'token-is-valid'                    => 'টোকেন বৈধ',
        ],

        'forgot-password' => [
            'invalid-operation'                 => 'অবৈধ অপারেশন',
            'invalid-input-data'                => 'অবৈধ ইনপুট ডেটা',
            'email-required'                    => 'ইমেল প্রয়োজন',
            'reset-link-sent'                   => 'রিসেট লিঙ্ক আপনার ইমেলে সফলভাবে পাঠানো হয়েছে',
            'email-not-found'                   => 'ইমেল ঠিকানা পাওয়া যায়নি',
            'error-sending-reset-link'          => 'রিসেট লিঙ্ক পাঠাতে ত্রুটি হয়েছে',
        ],

        'logout' => [
            'invalid-operation'                 => 'অবৈধ অপারেশন',
            'invalid-input-data'                => 'অবৈধ ইনপুট ডেটা',
            'token-required'                    => 'টোকেন প্রয়োজন',
            'invalid-token-format'              => 'অবৈধ টোকেন ফর্ম্যাট',
            'logged-out-successfully'           => 'সফলভাবে লগ আউট করা হয়েছে',
            'token-not-found-or-expired'        => 'টোকেন পাওয়া যায়নি বা ইতিমধ্যে মেয়াদ শেষ হয়েছে',
            'error-during-logout'               => 'লগ আউট করার সময় ত্রুটি',
        ],

        'address' => [
            'deleted-successfully'              => 'ঠিকানা সফলভাবে মুছে ফেলা হয়েছে',
            'authentication-required'           => 'প্রমাণীকরণ টোকেন প্রয়োজন',
            'invalid-token'                     => 'অবৈধ বা মেয়াদোত্তীর্ণ টোকেন',
            'unknown-operation'                 => 'অজানা অপারেশন',
            'address-id-required'               => 'ঠিকানা আইডি প্রয়োজন',
            'address-not-found'                 => 'ঠিকানা পাওয়া যায়নি বা এই গ্রাহকের অন্তর্গত নয়',
            'retrieved'                         => 'ঠিকানা সফলভাবে পুনরুদ্ধার করা হয়েছে',
            'fetch-failed'                      => 'ঠিকানা আনতে ব্যর্থ:',
        ],

        'customer-profile' => [
            'authentication-required'           => 'প্রমাণীকরণ টোকেন প্রয়োজন। অনুগ্রহ করে কোয়েরি ইনপুটে টোকেন প্রদান করুন',
            'invalid-token'                     => 'অবৈধ বা মেয়াদোত্তীর্ণ টোকেন',
        ],

        'customer' => [
            'password-mismatch'                 => 'পাসওয়ার্ড এবং নিশ্চিত করুন পাসওয়ার্ড মেলে না',
            'confirm-password-required'         => 'পাসওয়ার্ড পরিবর্তন করার সময় পাসওয়ার্ড নিশ্চিত করা প্রয়োজন',
            'unauthenticated'                   => 'অপ্রমাণিত। এই অ্যাকশন সম্পাদনের জন্য অনুগ্রহ করে লগইন করুন',
        ],

        'product-review' => [
            'product-id-required'               => 'পণ্য আইডি প্রয়োজন',
            'product-not-found'                 => 'পণ্য পাওয়া যায়নি',
            'rating-invalid'                    => 'রেটিং ১ এবং ৫ এর মধ্যে হতে হবে',
            'title-required'                    => 'রিভিউ শিরোনাম প্রয়োজন',
            'comment-required'                  => 'রিভিউ মন্তব্য প্রয়োজন',
        ],

        'product' => [
            'not-found-with-sku'                => 'SKU সহ কোনো পণ্য পাওয়া যায়নি',
            'not-found-with-url-key'            => 'URL কী সহ কোনো পণ্য পাওয়া যায়নি',
            'parameters-required'               => 'নিম্নলিখিত পরামিতিগুলির মধ্যে কমপক্ষে একটি প্রদান করতে হবে: "sku", "id", "urlKey"',
        ],

        'auth' => [
            'no-token-provided'                 => 'কোনো প্রমাণীকরণ টোকেন প্রদান করা হয়নি। অনুগ্রহ করে "Bearer <token>" হিসাবে Authorization হেডারে টোকেন প্রদান করুন বা input.token ফিল্ডে',
            'invalid-or-expired-token'          => 'অবৈধ বা মেয়াদোত্তীর্ণ টোকেন',
            'request-not-found'                 => 'কন্টেক্সটে অনুরোধ পাওয়া যায়নি',
            'token-required'                    => 'প্রমাণীকরণ টোকেন প্রয়োজন। অনুগ্রহ করে GraphQL মিউটেশন ইনপুট ফিল্ডে বা "Bearer <token>" হিসাবে Authorization হেডারে টোকেন প্রদান করুন',
            'unknown-resource'                  => 'অজানা সংস্থান',
            'cannot-update-other-profile'       => 'অননুমোদিত: অন্য গ্রাহক প্রোফাইল আপডেট করতে পারে না',
        ],

        'upload' => [
            'invalid-base64'                    => 'অবৈধ base64 এনকোডেড ইমেজ ডেটা',
            'size-exceeds-limit'                => 'ছবির আকার ৫MB অতিক্রম করতে পারে না',
            'invalid-format'                    => 'অবৈধ ইমেজ ফর্ম্যাট। অনুগ্রহ করে ডেটা URI স্কিম সহ base64 এনকোডেড ইমেজ প্রদান করুন (data:image/jpeg;base64,...)',
            'failed'                            => 'ইমেজ আপলোড ব্যর্থ',
        ],

        'attribute' => [
            'code-already-exists'               => 'অ্যাট্রিবিউট কোড ইতিমধ্যে বিদ্যমান',
        ],

        'login' => [
            'invalid-credentials'               => 'অবৈধ ইমেল বা পাসওয়ার্ড',
            'account-suspended'                 => 'আপনার অ্যাকাউন্ট স্থগিত করা হয়েছে',
            'successful'                        => 'আপনি সফলভাবে লগইন করেছেন',
            'invalid-request'                   => 'অবৈধ লগইন অনুরোধ',
        ],

        'checkout' => [
            'invalid-input'                     => 'চেকআউট অপারেশনের জন্য অবৈধ ইনপুট ডেটা',
            'billing-address-required'          => 'বিলিং ঠিকানা প্রয়োজন',
            'shipping-address-required'         => 'শিপমেন্টের জন্য শিপিং ঠিকানা প্রয়োজন',
            'address-save-failed'               => 'ঠিকানা সংরক্ষণ ব্যর্থ',
            'address-saved'                     => 'ঠিকানা সফলভাবে সংরক্ষিত',
            'shipping-method-required'          => 'শিপিং পদ্ধতি প্রয়োজন',
            'invalid-shipping-method'           => 'অবৈধ বা অপ্রাপ্ত শিপিং পদ্ধতি',
            'shipping-method-save-failed'       => 'শিপিং পদ্ধতি সংরক্ষণ ব্যর্থ',
            'shipping-method-saved'             => 'শিপিং পদ্ধতি সফলভাবে সংরক্ষিত',
            'shipping-method-error'             => 'শিপিং পদ্ধতি সংরক্ষণে ত্রুটি',
            'payment-method-required'           => 'পেমেন্ট পদ্ধতি প্রয়োজন',
            'invalid-payment-method'            => 'অবৈধ বা অপ্রাপ্ত পেমেন্ট পদ্ধতি',
            'payment-method-save-failed'        => 'পেমেন্ট পদ্ধতি সংরক্ষণ ব্যর্থ',
            'payment-method-saved'              => 'পেমেন্ট পদ্ধতি সফলভাবে সংরক্ষিত',
            'payment-method-error'              => 'পেমেন্ট পদ্ধতি সংরক্ষণে ত্রুটি',
            'order-creation-failed'             => 'অর্ডার তৈরি ব্যর্থ: অর্ডার আইডি খালি বা অর্ডার স্থায়ী নয়',
            'order-retrieval-failed'            => 'তৈরি অর্ডার পুনরুদ্ধার করতে ব্যর্থ',
            'order-creation-error'              => 'অর্ডার তৈরিতে ব্যর্থ',
            'cart-empty'                        => 'কার্ট খালি',
            'account-suspended'                 => 'আপনার অ্যাকাউন্ট স্থগিত করা হয়েছে। অনুগ্রহ করে সহায়তার সাথে যোগাযোগ করুন।',
            'account-inactive'                  => 'আপনার অ্যাকাউন্ট নিষ্ক্রিয়। অনুগ্রহ করে সহায়তার সাথে যোগাযোগ করুন।',
            'minimum-order-not-met'             => 'ন্যূনতম অর্ডার পরিমাণ :amount',
            'email-required'                    => 'অর্ডার তৈরির জন্য ইমেল ঠিকানা প্রয়োজন',
            'unknown-operation'                 => 'অজানা চেকআউট অপারেশন',
        ],

        'customer-addresses' => [
            'token-required'                    => 'গ্রাহক ঠিকানা আনতে টোকেন প্রয়োজন',
            'invalid-or-expired-token'          => 'অবৈধ বা মেয়াদোত্তীর্ণ টোকেন',
            'token-validation-failed'           => 'টোকেন যাচাইকরণ ব্যর্থ',
        ],

        'product' => [
            'type'                              => 'পণ্য প্রকার',
            'attribute-family'                  => 'অ্যাট্রিবিউট পরিবার',
            'sku'                               => 'SKU',
            'name'                              => 'নাম',
            'description'                       => 'বর্ণনা',
            'short-description'                 => 'সংক্ষিপ্ত বর্ণনা',
            'status'                            => 'অবস্থা',
            'new'                               => 'নতুন',
            'featured'                          => 'বৈশিষ্ট্যযুক্ত',
            'price'                             => 'মূল্য',
            'special-price'                     => 'বিশেষ মূল্য',
            'weight'                            => 'ওজন',
            'cost'                              => 'খরচ',
            'length'                            => 'দৈর্ঘ্য',
            'width'                             => 'প্রস্থ',
            'height'                            => 'উচ্চতা',
            'color'                             => 'রঙ',
            'size'                              => 'আকার',
            'brand'                             => 'ব্র্যান্ড',
            'super-attributes'                  => 'সুপার অ্যাট্রিবিউট',
        ],

        'compare-item' => [
            'id-required'                       => 'তুলনা আইটেম আইডি প্রয়োজন',
            'invalid-id-format'                 => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/compare-items/1" বা সংখ্যাসূচক আইডির মতো IRI ফর্ম্যাট প্রত্যাশিত',
            'not-found'                         => 'তুলনা আইটেম পাওয়া যায়নি',
            'product-id-required'               => 'পণ্য আইডি প্রয়োজন',
            'customer-id-required'              => 'গ্রাহক আইডি প্রয়োজন',
            'product-not-found'                 => 'পণ্য পাওয়া যায়নি',
            'customer-not-found'                => 'গ্রাহক পাওয়া যায়নি',
            'already-exists'                    => 'এই পণ্যটি ইতিমধ্যে আপনার তুলনা তালিকায় রয়েছে',
        ],

        'downloadable-product' => [
            'download-link-not-found'           => 'ডাউনলোড লিঙ্ক খুঁজে পাওয়া যায় নি বা মেয়াদ উত্তীর্ণ',
            'purchased-link-not-found'          => 'ক্রয়কৃত লিঙ্ক খুঁজে পাওয়া যায় নি',
            'file-not-found'                    => 'ফাইল খুঁজে পাওয়া যায় নি',
            'download-successful'               => 'ফাইল ডাউনলোডের জন্য প্রস্তুত',
            'token-required'                    => 'ডাউনলোড টোকেন প্রয়োজন',
            'invalid-token'                     => 'ডাউনলোড টোকেন অবৈধ বা মেয়াদ উত্তীর্ণ',
            'token-expired'                     => 'ডাউনলোড টোকেন মেয়াদ উত্তীর্ণ হয়েছে। দয়া করে একটি নতুন তৈরি করুন',
            'access-denied'                     => 'অ্যাক্সেস অস্বীকার: আপনি এই ফাইল ডাউনলোড করার অনুমতি পাননি',
            'redirect-external-url'             => 'বাহ্যিক ডাউনলোড URL এ পুনঃনির্দেশনা',
            'file-error'                        => 'আপনার ডাউনলোড অনুরোধ প্রক্রিয়া করার সময় একটি ত্রুটি ঘটেছে',
            'unauthorized-access'               => 'ডাউনলোড সম্পদে অননুমোদিত অ্যাক্সেস',
        ],
    ],
];
