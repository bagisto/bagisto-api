<?php

return [

    'graphql' => [
        'headers' => [
            'unknown-channel' => 'X-Channel হেডারে থাকা ":code" চ্যানেলটি বিদ্যমান নেই।',
            'storefront-key-required' => 'X-STOREFRONT-KEY হেডার প্রয়োজন',
            'storefront-key-invalid' => 'অবৈধ স্টোরফ্রন্ট কী',
            'storefront-key-rate-limited' => 'রেট লিমিট অতিক্রম করেছে। অনুগ্রহ করে :seconds সেকেন্ড পরে আবার চেষ্টা করুন',
        ],

        'cart' => [
            'authentication-required' => 'প্রমাণীকরণ টোকেন প্রয়োজন',
            'invalid-token' => 'অবৈধ বা মেয়াদোত্তীর্ণ প্রমাণীকরণ টোকেন',
            'unauthorized-access' => 'কার্টে অননুমোদিত অ্যাক্সেস',
            'authenticated-only' => 'শুধুমাত্র প্রমাণিত ব্যবহারকারীরা তাদের কার্ট আনতে পারেন',
            'merge-requires-auth' => 'অতিথি মার্জ প্রমাণীকরণ প্রয়োজন',
            'unknown-operation' => 'অজানা কার্ট অপারেশন',

            'cart-not-found' => 'কার্ট পাওয়া যায়নি',
            'guest-cart-not-found' => 'অতিথি কার্ট পাওয়া যায়নি',
            'product-not-found' => 'পণ্য পাওয়া যায়নি',

            'product-id-quantity-required' => 'পণ্য আইডি এবং পরিমাণ প্রয়োজন',
            'cart-item-id-quantity-required' => 'কার্ট আইটেম আইডি এবং পরিমাণ প্রয়োজন',

            'file-token-invalid' => 'আপলোড করা ফাইলের রেফারেন্স অবৈধ বা মেয়াদোত্তীর্ণ। ফাইলটি আবার আপলোড করুন।',
            'file-token-forbidden' => 'এই আপলোড করা ফাইলটি আপনার কার্টের নয়।',
            'customizable-option-required' => 'কাস্টমাইজযোগ্য অপশন ":label" প্রয়োজন।',
            'customizable-file-option-invalid' => 'নির্বাচিত অপশনটি এই পণ্যের জন্য কোনো ফাইল অপশন নয়।',
            'customizable-file-invalid' => 'ফাইলটি অনুপস্থিত, এর এক্সটেনশন অসমর্থিত, অথবা এটি খুব বড়।',
            'cart-item-id-required' => 'কার্ট আইটেম আইডি প্রয়োজন',
            'item-ids-required' => 'আইটেম আইডি অ্যারে প্রয়োজন',

            'event-booking-quantity-not-changeable' => 'ইভেন্ট বুকিং পণ্যের পরিমাণ পরিবর্তন করা যাবে না। পরিমাণ টিকিট নির্বাচনের মাধ্যমে নির্ধারিত হয়।',
            'appointment-booking-quantity-not-changeable' => 'অ্যাপয়েন্টমেন্ট বুকিং পণ্যের পরিমাণ পরিবর্তন করা যাবে না।',
            'coupon-code-required' => 'কুপন কোড প্রয়োজন',
            'address-data-required' => 'দেশ, অঞ্চল এবং পোস্টকোড প্রয়োজন',
            'grouped-qty-required' => 'গ্রুপড পণ্যের জন্য নির্বাচিত পরিমাণ প্রয়োজন। groupedQty JSON স্ট্রিং হিসেবে পাঠান, যেমন {"101":2,"102":1}।',
            'grouped-qty-must-include-all' => 'গ্রুপড পণ্যের জন্য সমস্ত সংশ্লিষ্ট পণ্যের পরিমাণ প্রয়োজন। অনুপস্থিত আইডি: :ids।',
            'grouped-qty-invalid-associated' => 'গ্রুপড পণ্যের পরিমাণে অবৈধ সংশ্লিষ্ট পণ্য আইডি রয়েছে: :ids।',
            'grouped-qty-invalid-quantity' => 'সংশ্লিষ্ট পণ্য আইডি :id-এর জন্য অবৈধ পরিমাণ দেওয়া হয়েছে। পরিমাণ অবশ্যই একটি অ-ঋণাত্মক পূর্ণসংখ্যা হতে হবে।',
            'bundle-qty-not-changeable' => 'বান্ডেল অপশন ":option"-এর পরিমাণ পরিবর্তন করা যাবে না। অনুমোদিত পরিমাণ হলো :qty।',

            'add-product-failed' => 'কার্টে পণ্য যোগ করতে ব্যর্থ',
            'update-item-failed' => 'কার্ট আইটেম আপডেট করতে ব্যর্থ',
            'remove-item-failed' => 'কার্ট আইটেম সরাতে ব্যর্থ',
            'apply-coupon-failed' => 'কুপন প্রয়োগ করতে ব্যর্থ',
            'remove-coupon-failed' => 'কুপন সরাতে ব্যর্থ',
            'coupon-applied' => 'কুপন সফলভাবে প্রয়োগ করা হয়েছে',
            'coupon-removed' => 'কুপন সফলভাবে সরানো হয়েছে',
            'move-to-wishlist-failed' => 'উইশলিস্টে আইটেম সরাতে ব্যর্থ',
            'estimate-shipping-failed' => 'শিপিং অনুমান করতে ব্যর্থ',

            'product-added-successfully' => 'পণ্য কার্টে সফলভাবে যোগ করা হয়েছে',
            'cart-item-updated-successfully' => 'কার্ট আইটেম সফলভাবে আপডেট করা হয়েছে',
            'guest-cart-merged' => 'অতিথি কার্ট সফলভাবে মার্জ করা হয়েছে',
            'using-authenticated-cart' => 'প্রমাণিত গ্রাহক কার্ট ব্যবহার করা হচ্ছে',
            'cart-item-not-found' => 'কার্ট আইটেম পাওয়া যায়নি',
            'new-guest-cart-created' => 'অনন্য সেশন টোকেন সহ নতুন অতিথি কার্ট তৈরি করা হয়েছে',
            'select-items-to-remove' => 'অনুগ্রহ করে সরাতে আইটেম নির্বাচন করুন',
            'select-items-to-move-wishlist' => 'অনুগ্রহ করে উইশলিস্টে সরাতে আইটেম নির্বাচন করুন',
            'invalid-or-expired-token' => 'কার্ট টোকেন অবৈধ বা মেয়াদোত্তীর্ণ। অনুগ্রহ করে নতুন কার্ট তৈরি করুন।',
            'invalid-token-of-login-user' => 'লগইন ব্যবহারকারীর টোকেন অবৈধ।',
            'booking-note-required' => 'টেবিল বুকিংয়ের জন্য booking.note প্রয়োজন।',
            'booking-date-required' => 'booking.slot একটি ফরম্যাট করা সময়সীমা হলে booking.date প্রয়োজন।',
            'booking-qty-required' => 'ইভেন্ট বুকিংয়ের জন্য booking.qty প্রয়োজন।',
            'booking-ticket-qty-invalid' => 'ইভেন্ট টিকিটের পরিমাণ অবশ্যই একটি অ-ঋণাত্মক সংখ্যা হতে হবে।',
            'booking-ticket-required' => 'ইভেন্ট বুকিংয়ের জন্য অন্তত একটি টিকিটের পরিমাণ নির্বাচন করুন।',
            'booking-slot-unavailable' => 'নির্বাচিত স্লটটি প্রদত্ত তারিখে উপলব্ধ নয়।',
            'invalid-quantity' => 'পরিমাণ কমপক্ষে 1 হতে হবে',
            'product-id-required' => 'পণ্য আইডি প্রয়োজন',
            'shipping-address-required' => 'শিপিং অনুমান করতে দেশ এবং পোস্টকোড প্রয়োজন',
        ],

        'token-verification' => [
            'invalid-operation' => 'অবৈধ অপারেশন',
            'invalid-input-data' => 'অবৈধ ইনপুট ডেটা',
            'token-required' => 'টোকেন প্রয়োজন',
            'invalid-token-format' => 'অবৈধ টোকেন ফর্ম্যাট',
            'token-not-found-or-expired' => 'টোকেন পাওয়া যায়নি বা মেয়াদ শেষ হয়েছে',
            'customer-not-found' => 'গ্রাহক পাওয়া যায়নি',
            'customer-account-suspended' => 'গ্রাহক অ্যাকাউন্ট স্থগিত',
            'error-verifying-token' => 'টোকেন যাচাই করতে ত্রুটি',
            'token-is-valid' => 'টোকেন বৈধ',
            'unauthenticated' => 'প্রমাণীকৃত নয়। এই কাজটি করতে অনুগ্রহ করে লগইন করুন',
        ],

        'forgot-password' => [
            'invalid-operation' => 'অবৈধ অপারেশন',
            'invalid-input-data' => 'অবৈধ ইনপুট ডেটা',
            'email-required' => 'ইমেল প্রয়োজন',
            'reset-link-sent' => 'রিসেট লিঙ্ক আপনার ইমেলে সফলভাবে পাঠানো হয়েছে',
            'email-not-found' => 'ইমেল ঠিকানা পাওয়া যায়নি',
            'throttled' => 'অনেকবার রিসেটের চেষ্টা করা হয়েছে। আবার চেষ্টা করার আগে অনুগ্রহ করে অপেক্ষা করুন।',
            'error-sending-reset-link' => 'রিসেট লিঙ্ক পাঠাতে ত্রুটি হয়েছে',
        ],

        'reset-password' => [
            'failed' => 'পাসওয়ার্ড রিসেট করা যায়নি। অনুগ্রহ করে আবার চেষ্টা করুন।',
        ],

        'rate-limit' => [
            'too-many-attempts' => 'অনেকবার চেষ্টা করা হয়েছে। অনুগ্রহ করে :seconds সেকেন্ড পরে আবার চেষ্টা করুন।',
        ],

        'logout' => [
            'invalid-operation' => 'অবৈধ অপারেশন',
            'invalid-input-data' => 'অবৈধ ইনপুট ডেটা',
            'token-required' => 'টোকেন প্রয়োজন',
            'invalid-token-format' => 'অবৈধ টোকেন ফর্ম্যাট',
            'logged-out-successfully' => 'সফলভাবে লগ আউট করা হয়েছে',
            'token-not-found-or-expired' => 'টোকেন পাওয়া যায়নি বা ইতিমধ্যে মেয়াদ শেষ হয়েছে',
            'error-during-logout' => 'লগ আউট করার সময় ত্রুটি',
            'unauthenticated' => 'প্রমাণীকৃত নয়। এই কাজটি করতে অনুগ্রহ করে লগইন করুন',
        ],

        'address' => [
            'deleted-successfully' => 'ঠিকানা সফলভাবে মুছে ফেলা হয়েছে',
            'authentication-required' => 'প্রমাণীকরণ টোকেন প্রয়োজন',
            'invalid-token' => 'অবৈধ বা মেয়াদোত্তীর্ণ টোকেন',
            'unknown-operation' => 'অজানা অপারেশন',
            'address-id-required' => 'ঠিকানা আইডি প্রয়োজন',
            'address-not-found' => 'ঠিকানা পাওয়া যায়নি বা এই গ্রাহকের অন্তর্গত নয়',
            'retrieved' => 'ঠিকানা সফলভাবে পুনরুদ্ধার করা হয়েছে',
            'fetch-failed' => 'ঠিকানা আনতে ব্যর্থ:',
            'field-required' => ':field ফিল্ডটি প্রয়োজন',
        ],

        'customer-profile' => [
            'authentication-required' => 'প্রমাণীকরণ টোকেন প্রয়োজন। অনুগ্রহ করে কোয়েরি ইনপুটে টোকেন প্রদান করুন',
            'invalid-token' => 'অবৈধ বা মেয়াদোত্তীর্ণ টোকেন',
            'profile-updated' => 'গ্রাহক প্রোফাইল সফলভাবে আপডেট করা হয়েছে',
        ],

        'customer' => [
            'password-mismatch' => 'পাসওয়ার্ড এবং নিশ্চিত করুন পাসওয়ার্ড মেলে না',
            'confirm-password-required' => 'পাসওয়ার্ড পরিবর্তন করার সময় পাসওয়ার্ড নিশ্চিত করা প্রয়োজন',
            'current-password-required' => 'পাসওয়ার্ড পরিবর্তন করতে বর্তমান পাসওয়ার্ড প্রয়োজন',
            'current-password-incorrect' => 'আপনার দেওয়া বর্তমান পাসওয়ার্ডটি ভুল',
            'verify-email-to-login' => 'আপনার অ্যাকাউন্ট তৈরি হয়েছে। লগইন করতে অনুগ্রহ করে আপনার ইমেল ঠিকানা যাচাই করুন।',
            'delete-password-required' => 'আপনার অ্যাকাউন্ট মুছতে আপনার পাসওয়ার্ড প্রয়োজন',
            'delete-password-incorrect' => 'আপনার দেওয়া পাসওয়ার্ডটি ভুল',
            'delete-has-active-orders' => 'আপনার মুলতুবি বা প্রক্রিয়াধীন অর্ডার থাকা অবস্থায় আপনার অ্যাকাউন্ট মোছা যাবে না',
            'unauthenticated' => 'অপ্রমাণিত। এই অ্যাকশন সম্পাদনের জন্য অনুগ্রহ করে লগইন করুন',
            'id-required' => 'গ্রাহক আইডি প্রয়োজন',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/admin/customers/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
            'not-found' => 'গ্রাহক পাওয়া যায়নি',
            'phone-special-chars-not-allowed' => 'মোবাইল নম্বরে শুধুমাত্র অঙ্ক থাকতে পারে। বিশেষ অক্ষর অনুমোদিত নয়',
            'email-already-taken' => 'এই ইমেল ঠিকানাটি ইতিমধ্যে অন্য একটি অ্যাকাউন্টে নিবন্ধিত',
            'invalid-gender' => 'অবৈধ লিঙ্গ মান ":gender"। অনুমোদিত মানগুলো হলো: :valid',
        ],

        'product-review' => [
            'id-required' => 'পণ্য রিভিউ আইডি প্রয়োজন',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/reviews/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
            'not-found' => 'আইডি ":id" সহ পণ্য রিভিউ পাওয়া যায়নি',
            'item-not-found' => 'আইটেম ":id" পাওয়া যায়নি।',
            'product-id-required' => 'পণ্য আইডি প্রয়োজন',
            'product-not-found' => 'পণ্য পাওয়া যায়নি',
            'rating-invalid' => 'রেটিং ১ এবং ৫ এর মধ্যে হতে হবে',
            'title-required' => 'রিভিউ শিরোনাম প্রয়োজন',
            'comment-required' => 'রিভিউ মন্তব্য প্রয়োজন',
            'review-disabled' => 'পণ্য রিভিউ বর্তমানে নিষ্ক্রিয় করা আছে',
            'guest-review-disabled' => 'অতিথি রিভিউ অনুমোদিত নয়। রিভিউ জমা দিতে অনুগ্রহ করে লগইন করুন',
            'owner-required' => 'আপনার রিভিউ পরিচালনা করতে অনুগ্রহ করে লগইন করুন',
            'not-owned' => 'এই রিভিউটি আপনার লেখা নয় এবং এটি পরিবর্তন বা মোছা যাবে না',
            'invalid-attachments' => 'অবৈধ সংযুক্তি ফর্ম্যাট।',
            'invalid-product-id-format' => 'অবৈধ পণ্য আইডি ফর্ম্যাট। "/api/shop/products/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
        ],

        'customer-review' => [
            'id-required' => 'গ্রাহক রিভিউ আইডি প্রয়োজন',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/customer-reviews/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
            'not-found' => 'আইডি ":id" সহ গ্রাহক রিভিউ পাওয়া যায়নি',
        ],

        'newsletter' => [
            'error-during-subscription' => 'এই মুহূর্তে সাবস্ক্রিপশন সম্পন্ন করা যাচ্ছে না। অনুগ্রহ করে পরে আবার চেষ্টা করুন',
        ],

        'contact-us' => [
            'success' => 'আপনার জিজ্ঞাসা সফলভাবে জমা দেওয়া হয়েছে। আমরা শীঘ্রই আপনার সাথে যোগাযোগ করব',
            'failed' => 'এই মুহূর্তে আপনার জিজ্ঞাসা পাঠানো যাচ্ছে না। অনুগ্রহ করে পরে আবার চেষ্টা করুন',
            'invalid-input' => 'অবৈধ যোগাযোগ ফর্মের ডেটা',
        ],

        'customer-order' => [
            'id-required' => 'গ্রাহক অর্ডার আইডি প্রয়োজন',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/customer-orders/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
            'not-found' => 'আইডি ":id" সহ গ্রাহক অর্ডার পাওয়া যায়নি',
        ],

        'return' => [
            'not-found' => 'রিটার্ন অনুরোধ পাওয়া যায়নি।',
            'disabled' => 'এই স্টোরের জন্য রিটার্ন সক্রিয় নেই।',
            'invalid-input' => 'রিটার্নের বিবরণ অনুপস্থিত বা অবৈধ।',
            'invalid-resolution-type' => 'সমাধানের ধরন অবশ্যই "return" বা "cancel_items" হতে হবে।',
            'agreement-required' => 'আপনাকে অবশ্যই রিটার্ন নীতি গ্রহণ করতে হবে।',
            'invalid-order' => 'নির্বাচিত অর্ডারটি বৈধ নয়।',
            'invalid-item' => 'নির্বাচিত আইটেমটি রিটার্নের যোগ্য নয়।',
            'qty-exceeds' => 'অনুরোধ করা পরিমাণ রিটার্নযোগ্য পরিমাণের চেয়ে বেশি।',
            'invalid-package-condition' => 'প্যাকেজের অবস্থা অবশ্যই "open" বা "packed" হতে হবে।',
            'invalid-image' => 'আপলোড করা এক বা একাধিক ফাইলের ফাইল টাইপ অসমর্থিত।',
            'invalid-custom-field' => 'অজানা কাস্টম ফিল্ড ":id"।',
            'custom-field-required' => '":field" ফিল্ডটি প্রয়োজন।',
            'invalid-custom-field-value' => '":field"-এর মানটি অনুমোদিত বিকল্পগুলোর মধ্যে নেই।',
            'created' => 'রিটার্ন অনুরোধ সফলভাবে তৈরি করা হয়েছে।',
            'canceled' => 'রিটার্ন অনুরোধ সফলভাবে বাতিল করা হয়েছে।',
            'already-canceled' => 'রিটার্ন অনুরোধটি ইতিমধ্যে বাতিল করা হয়েছে।',
            'cancel-not-allowed' => 'এই রিটার্ন অনুরোধটি বাতিল করা যাবে না।',
            'close-not-allowed' => 'এই রিটার্ন অনুরোধটি বন্ধ করা যাবে না।',
            'reopened' => 'রিটার্ন অনুরোধ সফলভাবে পুনরায় খোলা হয়েছে।',
            'reopen-not-allowed' => 'এই রিটার্ন অনুরোধটি পুনরায় খোলা যাবে না।',
            'closed' => 'রিটার্ন অনুরোধ সফলভাবে বন্ধ করা হয়েছে।',
            'message-sent' => 'বার্তা সফলভাবে পাঠানো হয়েছে।',
            'attachment-not-found' => 'বার্তার সংযুক্তি পাওয়া যায়নি।',
        ],

        'eu-withdrawal' => [
            'not-found' => 'প্রত্যাহারের ঘোষণা পাওয়া যায়নি।',
            'order-not-found' => 'অর্ডারটি পাওয়া যায়নি অথবা এটি প্রত্যাহারের যোগ্য নয়।',
            'too-many-attempts' => 'অনেকবার চেষ্টা করা হয়েছে। অনুগ্রহ করে :seconds সেকেন্ড পরে আবার চেষ্টা করুন।',
            'created' => 'প্রত্যাহারের ঘোষণা সফলভাবে দাখিল করা হয়েছে।',
        ],

        'customer-order-shipment' => [
            'id-required' => 'শিপমেন্ট আইডি প্রয়োজন',
            'not-found' => 'আইডি ":id" সহ শিপমেন্ট পাওয়া যায়নি অথবা এটি এই গ্রাহকের নয়',
            'item-not-found' => 'আইডি ":id" সহ শিপমেন্ট আইটেম পাওয়া যায়নি অথবা এটি এই গ্রাহকের নয়',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/customer-order-shipments/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
        ],

        'cancel-order' => [
            'order-id-required' => 'অর্ডার বাতিল করতে অর্ডার আইডি প্রয়োজন',
            'not-found' => 'আইডি ":id" সহ অর্ডার পাওয়া যায়নি অথবা এটি এই গ্রাহকের নয়',
            'cancel-success' => 'অর্ডার সফলভাবে বাতিল করা হয়েছে',
            'cancel-failed' => 'অর্ডারটি বাতিল করা যাবে না। এটি সম্ভবত ইতিমধ্যে প্রক্রিয়া, শিপ বা বাতিল করা হয়েছে',
        ],

        'reorder' => [
            'order-id-required' => 'পুনরায় অর্ডার করতে অর্ডার আইডি প্রয়োজন',
            'not-found' => 'আইডি ":id" সহ অর্ডার পাওয়া যায়নি অথবা এটি এই গ্রাহকের নয়',
            'reorder-success' => 'আপনার আগের অর্ডারের :count টি আইটেম আপনার কার্টে যোগ করা হয়েছে',
            'no-items-added' => 'কার্টে কোনো আইটেম যোগ করা যায়নি। পণ্যগুলো স্টকে নেই বা অনুপলব্ধ হতে পারে',
        ],

        'customer-downloadable-product' => [
            'id-required' => 'গ্রাহকের ডাউনলোডযোগ্য পণ্য আইডি প্রয়োজন',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/customer-downloadable-products/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
            'not-found' => 'আইডি ":id" সহ গ্রাহকের ডাউনলোডযোগ্য পণ্য পাওয়া যায়নি',

            'download' => [
                'unauthenticated' => 'অননুমোদিত: গ্রাহক প্রমাণীকরণ প্রয়োজন।',
                'not-found' => 'ডাউনলোডযোগ্য পণ্য পাওয়া যায়নি।',
                'pending' => 'ডাউনলোড মুলতুবি আছে। অর্ডারের ইনভয়েস তৈরি না হওয়া পর্যন্ত অনুগ্রহ করে অপেক্ষা করুন।',
                'limit-exceeded' => 'ডাউনলোডের সীমা অতিক্রম করেছে।',
                'no-downloads-remaining' => 'আর কোনো ডাউনলোড উপলব্ধ নেই।',
                'file-not-found' => 'ফাইল খুঁজে পাওয়া যায় নি।',
                'link-not-found' => 'ডাউনলোডযোগ্য লিংক পাওয়া যায়নি।',
            ],
        ],

        'customer-invoice' => [
            'id-required' => 'গ্রাহক ইনভয়েস আইডি প্রয়োজন',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/customer-invoices/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
            'not-found' => 'আইডি ":id" সহ গ্রাহক ইনভয়েস পাওয়া যায়নি',
        ],

        'auth' => [
            'no-token-provided' => 'কোনো প্রমাণীকরণ টোকেন প্রদান করা হয়নি। অনুগ্রহ করে "Bearer <token>" হিসাবে Authorization হেডারে টোকেন প্রদান করুন বা input.token ফিল্ডে',
            'invalid-or-expired-token' => 'অবৈধ বা মেয়াদোত্তীর্ণ টোকেন',
            'request-not-found' => 'কন্টেক্সটে অনুরোধ পাওয়া যায়নি',
            'token-required' => 'প্রমাণীকরণ টোকেন প্রয়োজন। অনুগ্রহ করে GraphQL মিউটেশন ইনপুট ফিল্ডে বা "Bearer <token>" হিসাবে Authorization হেডারে টোকেন প্রদান করুন',
            'unknown-resource' => 'অজানা সংস্থান',
            'cannot-update-other-profile' => 'অননুমোদিত: অন্য গ্রাহক প্রোফাইল আপডেট করতে পারে না',
        ],

        'upload' => [
            'invalid-base64' => 'অবৈধ base64 এনকোডেড ইমেজ ডেটা',
            'size-exceeds-limit' => 'ছবির আকার ৫MB অতিক্রম করতে পারে না',
            'invalid-format' => 'অবৈধ ইমেজ ফর্ম্যাট। অনুগ্রহ করে ডেটা URI স্কিম সহ base64 এনকোডেড ইমেজ প্রদান করুন (data:image/jpeg;base64,...)',
            'failed' => 'ইমেজ আপলোড ব্যর্থ',
        ],

        'attribute' => [
            'code-already-exists' => 'অ্যাট্রিবিউট কোড ইতিমধ্যে বিদ্যমান',
            'option-id-required' => 'সরাসরি অ্যাট্রিবিউট অপশন কোয়েরি করার সময় "attributeId" প্যারামিটার প্রয়োজন',
            'id-required' => 'অ্যাট্রিবিউট আইডি প্রয়োজন',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/attributes/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
            'not-found' => 'অ্যাট্রিবিউট পাওয়া যায়নি',
        ],

        'attribute-option' => [
            'id-required' => 'অ্যাট্রিবিউট অপশন আইডি প্রয়োজন',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/attribute-options/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
            'not-found' => 'অ্যাট্রিবিউট অপশন পাওয়া যায়নি',
        ],

        'channel' => [
            'id-required' => 'চ্যানেল আইডি প্রয়োজন',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/channels/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
            'not-found' => 'চ্যানেল পাওয়া যায়নি',
        ],

        'country-state' => [
            'id-required' => 'দেশের রাজ্য আইডি প্রয়োজন',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/country-states/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
            'not-found' => 'দেশের রাজ্য পাওয়া যায়নি',
            'country-id-required' => 'দেশ আইডি প্রয়োজন',
        ],

        'country' => [
            'id-required' => 'দেশ আইডি প্রয়োজন',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/countries/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
            'not-found' => 'দেশ পাওয়া যায়নি',
        ],

        'login' => [
            'invalid-credentials' => 'অবৈধ ইমেল বা পাসওয়ার্ড',
            'account-suspended' => 'আপনার অ্যাকাউন্ট স্থগিত করা হয়েছে',
            'account-inactive' => 'আপনার সক্রিয়ণ অ্যাডমিনের অনুমোদন প্রয়োজন',
            'email-not-verified' => 'প্রথমে আপনার ইমেইল অ্যাকাউন্ট যাচাই করুন।',
            'successful' => 'আপনি সফলভাবে লগইন করেছেন',
            'invalid-request' => 'অবৈধ লগইন অনুরোধ',
        ],

        'social-login' => [
            'signed-in' => 'সফলভাবে সাইন ইন হয়েছে।',
            'token-required' => 'একটি সোশ্যাল লগইন টোকেন প্রয়োজন।',
            'invalid-token' => 'সোশ্যাল লগইন টোকেনটি অবৈধ বা মেয়াদোত্তীর্ণ। আবার চেষ্টা করুন।',
            'google-not-configured' => 'এই স্টোরের জন্য Google সাইন-ইন কনফিগার করা নেই।',
            'wrong-audience' => 'এই টোকেনটি অন্য একটি অ্যাপের জন্য জারি করা হয়েছে।',
            'email-required' => 'প্রদানকারী কোনো ইমেল ঠিকানা শেয়ার করেনি। পরিবর্তে ইমেল দিয়ে সাইন আপ করুন।',
            'account-inactive' => 'আপনার সক্রিয়ণ অ্যাডমিনের অনুমোদন প্রয়োজন',
            'provider-not-supported' => 'এই সোশ্যাল লগইন প্রদানকারী সমর্থিত নয়।',
            'provider-disabled' => 'এই সোশ্যাল লগইন প্রদানকারী সক্রিয় নয়।',
        ],

        'checkout' => [
            'invalid-input' => 'চেকআউট অপারেশনের জন্য অবৈধ ইনপুট ডেটা',
            'billing-address-required' => 'বিলিং ঠিকানা প্রয়োজন',
            'shipping-address-required' => 'শিপমেন্টের জন্য শিপিং ঠিকানা প্রয়োজন',
            'address-save-failed' => 'ঠিকানা সংরক্ষণ ব্যর্থ',
            'address-saved' => 'ঠিকানা সফলভাবে সংরক্ষিত',
            'shipping-method-required' => 'শিপিং পদ্ধতি প্রয়োজন',
            'invalid-shipping-method' => 'অবৈধ বা অপ্রাপ্ত শিপিং পদ্ধতি',
            'shipping-method-save-failed' => 'শিপিং পদ্ধতি সংরক্ষণ ব্যর্থ',
            'shipping-method-saved' => 'শিপিং পদ্ধতি সফলভাবে সংরক্ষিত',
            'shipping-method-error' => 'শিপিং পদ্ধতি সংরক্ষণে ত্রুটি',
            'payment-method-required' => 'পেমেন্ট পদ্ধতি প্রয়োজন',
            'invalid-payment-method' => 'অবৈধ বা অপ্রাপ্ত পেমেন্ট পদ্ধতি',
            'payment-method-save-failed' => 'পেমেন্ট পদ্ধতি সংরক্ষণ ব্যর্থ',
            'payment-method-saved' => 'পেমেন্ট পদ্ধতি সফলভাবে সংরক্ষিত',
            'payment-method-error' => 'পেমেন্ট পদ্ধতি সংরক্ষণে ত্রুটি',
            'order-placed' => 'অর্ডার সফলভাবে দেওয়া হয়েছে',
            'order-redirect-required' => 'এই পেমেন্ট পদ্ধতিতে ক্রেতাকে গেটওয়েতে পেমেন্ট সম্পন্ন করতে হবে। তাদের redirectUrl-এ পাঠান; গেটওয়ে পেমেন্ট নিশ্চিত করলে অর্ডার তৈরি হবে।',
            'order-creation-failed' => 'অর্ডার তৈরি ব্যর্থ: অর্ডার আইডি খালি বা অর্ডার স্থায়ী নয়',
            'order-retrieval-failed' => 'তৈরি অর্ডার পুনরুদ্ধার করতে ব্যর্থ',
            'order-creation-error' => 'অর্ডার তৈরিতে ব্যর্থ',
            'cart-empty' => 'কার্ট খালি',
            'account-suspended' => 'আপনার অ্যাকাউন্ট স্থগিত করা হয়েছে। অনুগ্রহ করে সহায়তার সাথে যোগাযোগ করুন।',
            'account-inactive' => 'আপনার অ্যাকাউন্ট নিষ্ক্রিয়। অনুগ্রহ করে সহায়তার সাথে যোগাযোগ করুন।',
            'minimum-order-not-met' => 'ন্যূনতম অর্ডার পরিমাণ :amount',
            'guest-checkout-not-allowed' => 'চেকআউট করতে অনুগ্রহ করে লগ ইন করুন। এই কার্টের জন্য অতিথি চেকআউট উপলব্ধ নয়।',
            'email-required' => 'অর্ডার তৈরির জন্য ইমেল ঠিকানা প্রয়োজন',
            'unknown-operation' => 'অজানা চেকআউট অপারেশন',
        ],

        'customer-addresses' => [
            'token-required' => 'গ্রাহক ঠিকানা আনতে টোকেন প্রয়োজন',
            'invalid-or-expired-token' => 'অবৈধ বা মেয়াদোত্তীর্ণ টোকেন',
            'token-validation-failed' => 'টোকেন যাচাইকরণ ব্যর্থ',
        ],

        'product' => [
            'not-found' => 'পণ্য পাওয়া যায়নি',
            'not-found-with-sku' => 'এই SKU সহ কোনো পণ্য পাওয়া যায়নি',
            'not-found-with-url-key' => 'এই URL কী সহ কোনো পণ্য পাওয়া যায়নি',
            'parameters-required' => 'নিম্নলিখিত প্যারামিটারগুলোর অন্তত একটি দিতে হবে: "sku", "id", "urlKey"',
            'type' => 'পণ্য প্রকার',
            'attribute-family' => 'অ্যাট্রিবিউট পরিবার',
            'sku' => 'SKU',
            'name' => 'নাম',
            'description' => 'বর্ণনা',
            'short-description' => 'সংক্ষিপ্ত বর্ণনা',
            'status' => 'অবস্থা',
            'new' => 'নতুন',
            'featured' => 'বৈশিষ্ট্যযুক্ত',
            'price' => 'মূল্য',
            'special-price' => 'বিশেষ মূল্য',
            'weight' => 'ওজন',
            'cost' => 'খরচ',
            'length' => 'দৈর্ঘ্য',
            'width' => 'প্রস্থ',
            'height' => 'উচ্চতা',
            'color' => 'রঙ',
            'size' => 'আকার',
            'brand' => 'ব্র্যান্ড',
            'super-attributes' => 'সুপার অ্যাট্রিবিউট',
            'id-required' => 'পণ্য আইডি প্রয়োজন',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/products/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
        ],

        'downloadable-product' => [
            'download-link-not-found' => 'ডাউনলোড লিঙ্ক খুঁজে পাওয়া যায় নি বা মেয়াদ উত্তীর্ণ',
            'purchased-link-not-found' => 'ক্রয়কৃত লিঙ্ক খুঁজে পাওয়া যায় নি',
            'file-not-found' => 'ফাইল খুঁজে পাওয়া যায় নি',
            'download-successful' => 'ফাইল ডাউনলোডের জন্য প্রস্তুত',
            'token-required' => 'ডাউনলোড টোকেন প্রয়োজন',
            'invalid-token' => 'ডাউনলোড টোকেন অবৈধ বা মেয়াদ উত্তীর্ণ',
            'token-expired' => 'ডাউনলোড টোকেন মেয়াদ উত্তীর্ণ হয়েছে। দয়া করে একটি নতুন তৈরি করুন',
            'access-denied' => 'অ্যাক্সেস অস্বীকার: আপনি এই ফাইল ডাউনলোড করার অনুমতি পাননি',
            'redirect-external-url' => 'বাহ্যিক ডাউনলোড URL এ পুনঃনির্দেশনা',
            'file-error' => 'আপনার ডাউনলোড অনুরোধ প্রক্রিয়া করার সময় একটি ত্রুটি ঘটেছে',
            'unauthorized-access' => 'ডাউনলোড সম্পদে অননুমোদিত অ্যাক্সেস',
            'unauthenticated' => 'অননুমোদিত: গ্রাহক প্রমাণীকরণ প্রয়োজন',
            'link-id-required' => 'downloadableLinkPurchasedId প্রয়োজন',
            'link-not-found' => 'ডাউনলোডযোগ্য লিংক পাওয়া যায়নি',
            'not-owner' => 'অননুমোদিত: এই ডাউনলোড লিংকটি আপনার নয়',
            'pending' => 'ডাউনলোড মুলতুবি আছে। অর্ডারের ইনভয়েস তৈরি না হওয়া পর্যন্ত অনুগ্রহ করে অপেক্ষা করুন।',
            'limit-exceeded' => 'ডাউনলোডের সীমা অতিক্রম করেছে',
            'no-downloads-left' => 'আর কোনো ডাউনলোড উপলব্ধ নেই',
        ],

        'booking-slot' => [
            'id-required' => 'বুকিং পণ্য আইডি প্রয়োজন।',
            'date-required' => 'তারিখ প্রয়োজন।',
            'product-not-found' => 'বুকিং পণ্য পাওয়া যায়নি।',
            'invalid-type' => 'এই বুকিং ধরন সমর্থিত নয়।',
            'invalid-time-range' => 'অবৈধ সময়সীমা।',
            'invalid-time-format' => 'অবৈধ সময় ফর্ম্যাট।',
        ],

        'resolver' => [
            'no-resolve-info' => 'কনটেক্সটে GraphQL ResolveInfo উপলব্ধ নেই। মডেল ক্লাস নির্ধারণ করা যাচ্ছে না।',
            'unknown-resource-class' => 'GraphQL রিটার্ন টাইপ থেকে রিসোর্স ক্লাস নির্ধারণ করা যাচ্ছে না',
        ],

        'docs' => [
            'openapi-unavailable' => 'OpenAPI Factory ইনস্ট্যানশিয়েট করা যায়নি',
        ],

        'locale' => [
            'id-required' => 'লোকেল আইডি প্রয়োজন',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/locales/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
            'not-found' => 'লোকেল পাওয়া যায়নি',
        ],

        'category' => [
            'id-required' => 'ক্যাটাগরি আইডি প্রয়োজন',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/categories/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
            'not-found' => 'ক্যাটাগরি পাওয়া যায়নি',
        ],

        'compare-item' => [
            'id-required' => 'তুলনা আইটেম আইডি প্রয়োজন',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/compare-items/1" বা সংখ্যাসূচক আইডির মতো IRI ফর্ম্যাট প্রত্যাশিত',
            'not-found' => 'তুলনা আইটেম পাওয়া যায়নি',
            'product-id-required' => 'পণ্য আইডি প্রয়োজন',
            'customer-id-required' => 'গ্রাহক আইডি প্রয়োজন',
            'product-not-found' => 'পণ্য পাওয়া যায়নি',
            'customer-not-found' => 'গ্রাহক পাওয়া যায়নি',
            'already-exists' => 'এই পণ্যটি ইতিমধ্যে আপনার তুলনা তালিকায় রয়েছে',
            'delete-all-success' => 'সমস্ত তুলনা আইটেম সফলভাবে সরানো হয়েছে',
        ],

        'wishlist' => [
            'id-required' => 'উইশলিস্ট আইটেম আইডি প্রয়োজন',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/wishlists/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
            'not-found' => 'উইশলিস্ট আইটেম পাওয়া যায়নি',
            'product-id-required' => 'পণ্য আইডি প্রয়োজন',
            'customer-id-required' => 'গ্রাহক আইডি প্রয়োজন',
            'product-not-found' => 'পণ্য পাওয়া যায়নি',
            'product-disabled' => 'এই পণ্যটি বর্তমানে নিষ্ক্রিয় করা আছে',
            'customer-not-found' => 'গ্রাহক পাওয়া যায়নি',
            'already-exists' => 'এই পণ্যটি ইতিমধ্যে আপনার উইশলিস্টে আছে',
            'added' => 'আইটেম সফলভাবে উইশলিস্টে যোগ করা হয়েছে',
            'removed' => 'আইটেম সফলভাবে উইশলিস্ট থেকে সরানো হয়েছে',
            'product-removed' => 'পণ্যটি সরানো হয়েছে',
            'wishlist-item-id-required' => 'উইশলিস্ট আইটেম আইডি প্রয়োজন',
            'invalid-quantity' => 'পরিমাণ অবশ্যই 0-এর বেশি হতে হবে',
            'move-to-cart-missing-options' => 'পণ্যটিতে প্রয়োজনীয় অপশন অনুপস্থিত। অনুগ্রহ করে এটি ম্যানুয়ালি কনফিগার করুন',
            'moved-to-cart-success' => 'আইটেম সফলভাবে কার্টে সরানো হয়েছে',
            'delete-all-success' => 'সমস্ত উইশলিস্ট আইটেম সফলভাবে সরানো হয়েছে',
        ],

        'gdpr' => [
            'disabled' => 'GDPR ডেটা অনুরোধ নিষ্ক্রিয় করা আছে। অনুগ্রহ করে অ্যাডমিন কনফিগারেশন থেকে GDPR সক্রিয় করুন।',
            'unauthenticated' => 'GDPR ডেটা অনুরোধ পরিচালনা করতে আপনাকে লগইন করতে হবে।',
            'not-found' => 'GDPR ডেটা অনুরোধ পাওয়া যায়নি',
            'type-required' => 'অনুরোধের ধরন প্রয়োজন',
            'type-invalid' => 'অনুরোধের ধরন অবশ্যই "delete" বা "update" হতে হবে',
            'message-required' => 'বার্তা প্রয়োজন',
            'raised' => 'আপনার GDPR ডেটা অনুরোধ সফলভাবে উত্থাপন করা হয়েছে।',
            'revoked' => 'আপনার GDPR ডেটা অনুরোধ সফলভাবে প্রত্যাহার করা হয়েছে।',
            'revoke-not-allowed' => 'এই GDPR ডেটা অনুরোধটি আর প্রত্যাহার করা যাবে না।',
            'deleted' => 'আপনার GDPR ডেটা অনুরোধ সফলভাবে মুছে ফেলা হয়েছে।',
        ],

        'section' => [
            'id-required' => 'সেকশন আইডি প্রয়োজন',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/sections/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
            'not-found' => 'সেকশন পাওয়া যায়নি',
        ],

        'install' => [
            'starting' => 'API Platform ইনস্টলেশন শুরু হচ্ছে...',
            'completed-success' => '✓ API Platform ইনস্টলেশন সফলভাবে সম্পন্ন হয়েছে!',
            'failed' => '✗ ইনস্টলেশন ব্যর্থ হয়েছে: ',

            'provider-already-registered' => 'ℹ Service Provider ইতিমধ্যে নিবন্ধিত',
            'provider-not-registered' => '⚠ Service Provider স্বয়ংক্রিয়ভাবে নিবন্ধন করা যায়নি। :provider ম্যানুয়ালি bootstrap/providers.php-এ যোগ করুন।',
            'provider-registered' => '✓ Service Provider bootstrap/providers.php-এ নিবন্ধিত হয়েছে',
            'provider-file-not-found' => 'ফাইল পাওয়া যায়নি: :file',
            'provider-permission-denied' => 'অনুমতি অস্বীকৃত: :file-এ লেখা যাচ্ছে না। অনুগ্রহ করে ফাইলের অনুমতি পরীক্ষা করুন।',

            'config-already-exists' => 'ℹ কনফিগারেশন ফাইল ইতিমধ্যে বিদ্যমান',
            'config-published' => '✓ API Platform কনফিগারেশন config/-এ প্রকাশিত হয়েছে',
            'config-source-not-found' => 'সোর্স কনফিগ ফাইল পাওয়া যায়নি: :file',
            'config-permission-denied' => 'অনুমতি অস্বীকৃত: :directory-তে লেখা যাচ্ছে না। অনুগ্রহ করে ডিরেক্টরির অনুমতি পরীক্ষা করুন।',

            'translatable-not-found' => 'ℹ TranslatableModel পাওয়া যায়নি - এড়িয়ে যাওয়া হচ্ছে',
            'translatable-already-abstract' => 'ℹ TranslatableModel ইতিমধ্যে abstract',
            'translatable-made-abstract' => '✓ TranslatableModel abstract করা হয়েছে',
            'translatable-permission-denied' => 'অনুমতি অস্বীকৃত: :file-এ লেখা যাচ্ছে না। অনুগ্রহ করে ফাইলের অনুমতি পরীক্ষা করুন।',

            'providers-already-registered' => 'ℹ API Platform প্রোভাইডার ইতিমধ্যে নিবন্ধিত',
            'providers-registered' => '✓ API Platform প্রোভাইডার bootstrap/app.php-এ নিবন্ধিত হয়েছে',
            'providers-file-not-found' => 'ফাইল পাওয়া যায়নি: :file',
            'providers-permission-denied' => 'অনুমতি অস্বীকৃত: :file-এ লেখা যাচ্ছে না। অনুগ্রহ করে ফাইলের অনুমতি পরীক্ষা করুন।',
            'providers-not-found' => 'bootstrap/app.php-এ Application তৈরির অংশ খুঁজে পাওয়া যায়নি',

            'assets-published' => '✓ প্যাকেজ অ্যাসেট প্রকাশিত হয়েছে',
            'running-migrations' => 'ডাটাবেস মাইগ্রেশন চালানো হচ্ছে...',
            'migrations-completed' => '✓ ডাটাবেস মাইগ্রেশন সম্পন্ন হয়েছে',
            'clearing-caches' => 'ক্যাশ পরিষ্কার ও অপ্টিমাইজ করা হচ্ছে...',
            'caches-optimized' => '✓ ক্যাশ পরিষ্কার ও অপ্টিমাইজ করা হয়েছে',
            'generating-api-key' => 'API কী তৈরি করা হচ্ছে...',
            'api-key-generated' => '✓ API কী সফলভাবে তৈরি হয়েছে',
            'api-key-already-exists' => 'ℹ ডিফল্ট স্টোর API কী ইতিমধ্যে বিদ্যমান - তৈরি এড়িয়ে যাওয়া হচ্ছে',

            'migrations-error' => 'ডাটাবেস মাইগ্রেশন ব্যর্থ হয়েছে।',
            'migrations-error-running' => 'ডাটাবেস মাইগ্রেশন চালাতে ত্রুটি: :error',
            'cache-clear-error' => 'ক্যাশ পরিষ্কার করতে ব্যর্থ হয়েছে।',
            'cache-optimize-error' => 'অপ্টিমাইজেশন ব্যর্থ হয়েছে।',
            'cache-error' => 'ক্যাশ পরিষ্কার ও অপ্টিমাইজ করার সময় ত্রুটি: :error',
            'api-key-generation-error' => 'API কী তৈরি ব্যর্থ হয়েছে।',
            'api-key-error' => 'API কী তৈরি করতে ত্রুটি: :error',

            'key-management-required' => ':action অ্যাকশনের জন্য --key প্রয়োজন',
            'key-rotation-error' => 'কী রোটেট করতে ত্রুটি: :error',
            'key-deactivation-error' => 'কী নিষ্ক্রিয় করতে ত্রুটি: :error',
            'key-not-found' => 'API কী পাওয়া যায়নি: :identifier',

            'maintenance-starting' => '🔄 API কী রক্ষণাবেক্ষণ শুরু হচ্ছে...',
            'maintenance-complete' => '✅ API কী রক্ষণাবেক্ষণ সম্পন্ন',
            'cleanup-expired-keys' => '🧹 মেয়াদোত্তীর্ণ কী পরিষ্কার করা হচ্ছে...',
            'cleanup-expired-none' => '   ℹ️ পরিষ্কার করার মতো কোনো মেয়াদোত্তীর্ণ কী নেই',
            'invalidate-deprecated' => '⚠️ অপ্রচলিত কী অবৈধ করা হচ্ছে...',
            'invalidate-deprecated-none' => '   ℹ️ অবৈধ করার মতো কোনো অপ্রচলিত কী নেই',
            'notify-expiring' => '📧 মেয়াদোত্তীর্ণের বিজ্ঞপ্তি পাঠানো হচ্ছে...',
            'notify-expiring-none' => '   ℹ️ বিজ্ঞপ্তি প্রয়োজন এমন কোনো কী নেই',

            'key-rotated-success' => '✅ কী সফলভাবে রোটেট করা হয়েছে!',
            'key-deactivated-success' => '✅ কী সফলভাবে নিষ্ক্রিয় করা হয়েছে!',
            'deactivation-cancelled' => 'নিষ্ক্রিয়করণ বাতিল করা হয়েছে।',
            'cleanup-cancelled' => 'পরিষ্কারকরণ বাতিল করা হয়েছে।',

            'key-status-title' => 'কী-এর অবস্থা: :name',
            'key-status-active' => 'সক্রিয়: :status',
            'key-status-usable' => 'ব্যবহারযোগ্য: :status',
            'key-status-expired' => 'মেয়াদোত্তীর্ণ: :status',
            'key-status-deprecated' => 'অপ্রচলিত: :status',
            'key-status-expires-at' => 'মেয়াদ শেষ হবে: :expires',
            'key-status-days-until-expiry' => 'মেয়াদ শেষ হতে বাকি দিন: :days',
            'key-status-last-used' => 'সর্বশেষ ব্যবহার: :date',
            'key-status-rotated-from' => 'যেখান থেকে রোটেট করা হয়েছে: :key',
            'key-status-rotated-keys' => 'এটি থেকে রোটেট করা কী: :count',

            'policy-compliance-summary' => 'API কী রোটেশন নীতি সম্মতির সারসংক্ষেপ',
            'available-actions' => 'উপলব্ধ অ্যাকশন: rotate, deactivate, cleanup, status, expiring, unused, summary',

            'asset-linked-success' => '✓ API Platform অ্যাসেট সফলভাবে লিংক করা হয়েছে',
            'symlink-create-failed' => 'সিমলিংক তৈরি করা যায়নি, পরিবর্তে অ্যাসেট কপি করা হচ্ছে...',
            'asset-copy-warning' => 'সতর্কতা: API Platform অ্যাসেট লিংক বা কপি করা যায়নি। ম্যানুয়াল সেটআপের প্রয়োজন হতে পারে।',
            'asset-copied-success' => '✓ API Platform অ্যাসেট সফলভাবে কপি করা হয়েছে',

            'key-name-required' => 'স্টোরফ্রন্ট কী-এর নাম খালি রাখা যাবে না।',
            'key-generated-success' => 'স্টোরফ্রন্ট কী সফলভাবে তৈরি হয়েছে!',
            'key-details' => 'কী-এর বিবরণ:',
            'key-secure-warning' => 'এই কী-টি নিরাপদে রাখুন! এটি X-STOREFRONT-KEY হেডারে ব্যবহার করা হবে।',
            'key-share-warning' => 'এই কী-টি প্রকাশ্যে শেয়ার করবেন না বা ভার্সন কন্ট্রোলে কমিট করবেন না।',

            'key-already-exists' => '\':name\' নামের একটি স্টোরফ্রন্ট কী ইতিমধ্যে বিদ্যমান।',
            'cleanup-success' => '✅ :count টি মেয়াদোত্তীর্ণ কী পরিষ্কার করা হয়েছে',
            'confirm-cleanup' => 'এটি সমস্ত মেয়াদোত্তীর্ণ কী সফট-ডিলিট করবে। চালিয়ে যাবেন?',
            'confirm-deactivate' => 'আপনি কি নিশ্চিত যে আপনি কী নিষ্ক্রিয় করতে চান: :name?',

            'old-key' => 'পুরোনো কী: :name',
            'old-key-id' => 'পুরোনো কী আইডি: :id',
            'deprecation-date' => 'অপ্রচলনের তারিখ: :date',
            'new-key' => 'নতুন কী: :name',
            'new-key-id' => 'নতুন কী আইডি: :id',
            'new-key-value' => 'নতুন কী-এর মান: :key',
            'expires-at' => 'মেয়াদ শেষ হবে: :date',
            'invalid-action' => 'অবৈধ অ্যাকশন: :action',
            'key-active' => 'সক্রিয়: :status',
            'key-usable' => 'ব্যবহারযোগ্য: :status',
            'key-expired' => 'মেয়াদোত্তীর্ণ: :status',
            'key-deprecated' => 'অপ্রচলিত: :status',
            'key-field-id' => 'আইডি',
            'key-field-name' => 'নাম',
            'key-field-key' => 'কী',
            'key-field-rate-limit' => 'রেট লিমিট',
            'key-field-status' => 'অবস্থা',
            'key-days' => ' দিন',
            'key-never' => 'কখনো না',
            'key-requests-minute' => ' অনুরোধ/মিনিট',
            'key-unlimited' => 'সীমাহীন',
            'rate-limit-exceeded' => 'অনুরোধ করা রেট লিমিট সর্বোচ্চ অনুমোদিত :max অতিক্রম করেছে। :max অনুরোধ/মিনিটে সেট করা হচ্ছে।',

            'vendor-path-not-found' => 'API Platform vendor পাথ পাওয়া যায়নি: :path',
            'assets-already-linked' => 'API Platform অ্যাসেট ইতিমধ্যে লিংক করা আছে: :path',
            'no-keys-expiring' => 'আগামী :days দিনের মধ্যে কোনো কী-এর মেয়াদ শেষ হচ্ছে না',
            'keys-expiring-title' => 'আগামী :days দিনের মধ্যে যেসব কী-এর মেয়াদ শেষ হচ্ছে:',
            'key-display-format' => '• :name (আইডি: :id)',
            'key-expires-display' => '  মেয়াদ শেষ: :date (:days দিন বাকি)',
            'no-unused-keys' => 'কোনো অব্যবহৃত কী পাওয়া যায়নি (> :days দিন)',
            'unused-keys-title' => 'অব্যবহৃত কী (> :days দিন):',
            'key-last-used-display' => '  সর্বশেষ ব্যবহার: :date',
            'total-keys' => 'মোট কী: :count',
            'valid-keys' => 'বৈধ কী: :count',
            'expired-keys' => 'মেয়াদোত্তীর্ণ কী: :count',
            'deprecated-keys' => 'অপ্রচলিত কী: :count',
            'keys-expiring-soon' => 'শীঘ্রই মেয়াদ শেষ হবে এমন কী (7 দিন): :count',
            'unused-keys-summary' => 'অব্যবহৃত কী (90 দিন): :count',
            'recently-rotated' => 'সম্প্রতি রোটেট করা (30 দিন): :count',
            'cleanup-success-message' => '   ✅ :count টি মেয়াদোত্তীর্ণ কী পরিষ্কার করা হয়েছে',
            'invalidate-success-message' => '   ✅ :count টি অপ্রচলিত কী অবৈধ করা হয়েছে',
            'notify-success-message' => '   ✅ মেয়াদোত্তীর্ণের :count টি বিজ্ঞপ্তি পাঠানো হয়েছে',
            'notify-failed-message' => '   ⚠️ :key সম্পর্কে বিজ্ঞপ্তি পাঠাতে ব্যর্থ: :error',
            'publish-assets-warning' => 'সতর্কতা: প্যাকেজ অ্যাসেট প্রকাশ করা যায়নি। :error',
            'env-config-saved' => '✓ API Platform এনভায়রনমেন্ট কনফিগারেশন সফলভাবে সংরক্ষিত হয়েছে',
            'api-endpoints' => 'API এন্ডপয়েন্ট:',
            'api-documentation' => 'API ডকুমেন্টেশন: :url',
            'api-landing-page' => 'API ল্যান্ডিং পেজ: :url',
            'graphql-playground' => 'GraphQL Playground: :url',
            'rest-api-storefront' => 'Rest API Storefront Swagger: :url',
            'rest-api-admin' => 'Rest API Admin Swagger: :url',
            'key-rotation-invalid-key' => 'অবৈধ বা মেয়াদোত্তীর্ণ কী রোটেট করা যাবে না',
            'env-file-not-found' => '.env ফাইল পাওয়া যায়নি',
            'env-permission-denied' => 'অনুমতি অস্বীকৃত: .env ফাইলে লেখা যাচ্ছে না। অনুগ্রহ করে ফাইলের অনুমতি পরীক্ষা করুন।',
        ],

        'attribute-translation' => [
            'id-required' => 'অ্যাট্রিবিউট অনুবাদ আইডি প্রয়োজন',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/attribute-translations/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
            'not-found' => 'অ্যাট্রিবিউট অনুবাদ পাওয়া যায়নি',
        ],

        'currency' => [
            'id-required' => 'মুদ্রা আইডি প্রয়োজন',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/currencies/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
            'not-found' => 'মুদ্রা পাওয়া যায়নি',
        ],

        'customer-address' => [
            'id-required' => 'গ্রাহকের ঠিকানা আইডি প্রয়োজন',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/customer-addresses/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
        ],

        'customer-order-shipment-item' => [
            'id-required' => 'শিপমেন্ট আইটেম আইডি প্রয়োজন',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/customer-order-shipment-items/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
            'not-found' => 'শিপমেন্ট আইটেম পাওয়া যায়নি',
        ],

        'page' => [
            'id-required' => 'পৃষ্ঠা আইডি প্রয়োজন',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/pages/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
            'not-found' => 'পৃষ্ঠা পাওয়া যায়নি',
        ],

        'page-translation' => [
            'id-required' => 'পৃষ্ঠা অনুবাদ আইডি প্রয়োজন',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/page-translations/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
            'not-found' => 'পৃষ্ঠা অনুবাদ পাওয়া যায়নি',
        ],

        'product-bundle-option' => [
            'id-required' => 'বান্ডেল অপশন আইডি প্রয়োজন',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/product-bundle-options/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
            'not-found' => 'বান্ডেল অপশন পাওয়া যায়নি',
        ],

        'product-bundle-option-product' => [
            'id-required' => 'বান্ডেল অপশন পণ্য আইডি প্রয়োজন',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/product-bundle-option-products/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
            'not-found' => 'বান্ডেল অপশন পণ্য পাওয়া যায়নি',
        ],

        'product-downloadable-link' => [
            'id-required' => 'ডাউনলোডযোগ্য লিঙ্ক আইডি প্রয়োজন',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/product-downloadable-links/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
            'not-found' => 'ডাউনলোডযোগ্য লিঙ্ক পাওয়া যায়নি',
        ],

        'product-downloadable-sample' => [
            'id-required' => 'ডাউনলোডযোগ্য নমুনা আইডি প্রয়োজন',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/product-downloadable-samples/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
            'not-found' => 'ডাউনলোডযোগ্য নমুনা পাওয়া যায়নি',
        ],

        'product-grouped-product' => [
            'id-required' => 'গ্রুপ পণ্য আইডি প্রয়োজন',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/product-grouped-products/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
            'not-found' => 'গ্রুপ পণ্য পাওয়া যায়নি',
        ],

        'product-images' => [
            'id-required' => 'পণ্যের ছবি আইডি প্রয়োজন',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/product-images/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
            'not-found' => 'পণ্যের ছবি পাওয়া যায়নি',
        ],

        'product-videos' => [
            'id-required' => 'পণ্যের ভিডিও আইডি প্রয়োজন',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/product-videos/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
            'not-found' => 'পণ্যের ভিডিও পাওয়া যায়নি',
        ],

        'theme-customization' => [
            'id-required' => 'থিম কাস্টমাইজেশন আইডি প্রয়োজন',
            'invalid-id-format' => 'অবৈধ আইডি ফর্ম্যাট। "/api/shop/theme-customizations/1"-এর মতো IRI ফর্ম্যাট বা সংখ্যাসূচক আইডি প্রত্যাশিত',
            'not-found' => 'থিম কাস্টমাইজেশন পাওয়া যায়নি',
        ],

        'unknown-resource' => [
            'id-required' => 'রিসোর্স আইডি প্রয়োজন',
            'not-found' => 'রিসোর্স পাওয়া যায়নি',
        ],
    ],

    'admin' => [
        // login / logout / forgot-password / profile-update keys removed in the
        // 2026-05-27 auth refactor — admin clients now authenticate via
        // pre-issued integration tokens (Bearer header, AdminApiGuard).
        'profile' => [
            'unauthenticated' => 'আপনি প্রমাণীকৃত নন।',
        ],

        'dashboard' => [
            'invalid-type' => 'অজানা ড্যাশবোর্ড পরিসংখ্যানের ধরন। বৈধ ধরনসমূহ: over-all, today, stock-threshold-products, total-sales, top-selling-products, top-customers।',
        ],

        'rma' => [
            'not-found' => 'রিটার্ন অনুরোধ পাওয়া যায়নি।',
            'invalid-input' => 'রিটার্নের বিবরণ অনুপস্থিত বা অবৈধ।',
            'invalid-order' => 'নির্বাচিত অর্ডারটি বৈধ নয়।',
            'invalid-item' => 'নির্বাচিত আইটেমটি রিটার্নের যোগ্য নয়।',
            'invalid-resolution-type' => 'সমাধানের ধরন অবশ্যই "return" বা "cancel_items" হতে হবে।',
            'qty-exceeds' => 'অনুরোধ করা পরিমাণ রিটার্নযোগ্য পরিমাণের চেয়ে বেশি।',
            'created' => 'রিটার্ন অনুরোধ সফলভাবে তৈরি করা হয়েছে।',
            'status-updated' => 'রিটার্নের অবস্থা সফলভাবে আপডেট করা হয়েছে।',
            'invalid-status' => 'অবৈধ রিটার্ন অবস্থা।',
            'reopened' => 'রিটার্ন অনুরোধ সফলভাবে পুনরায় খোলা হয়েছে।',
            'reopen-not-allowed' => 'এই রিটার্ন অনুরোধটি পুনরায় খোলা যাবে না।',
            'refund-failed' => 'এই রিটার্নের জন্য রিফান্ড তৈরি করা যায়নি।',
            'message-sent' => 'বার্তা সফলভাবে পাঠানো হয়েছে।',
            'no-permission' => 'এই কাজটি করার অনুমতি আপনার নেই।',
            'reason-not-found' => 'রিটার্নের কারণ পাওয়া যায়নি।',
            'reason-deleted' => 'রিটার্নের কারণ সফলভাবে মুছে ফেলা হয়েছে।',
            'status-not-found' => 'রিটার্ন অবস্থা পাওয়া যায়নি।',
            'status-deleted' => 'রিটার্ন অবস্থা সফলভাবে মুছে ফেলা হয়েছে।',
            'status-default-undeletable' => 'ডিফল্ট রিটার্ন অবস্থা মুছে ফেলা যাবে না।',
            'rule-not-found' => 'রিটার্ন নিয়ম পাওয়া যায়নি।',
            'rule-deleted' => 'রিটার্ন নিয়ম সফলভাবে মুছে ফেলা হয়েছে।',
            'custom-field-not-found' => 'রিটার্ন কাস্টম ফিল্ড পাওয়া যায়নি।',
            'custom-field-deleted' => 'রিটার্ন কাস্টম ফিল্ড সফলভাবে মুছে ফেলা হয়েছে।',
            'custom-field-options-required' => 'এই ফিল্ড টাইপের জন্য অন্তত একটি অপশন প্রয়োজন।',
            'mass-indices-required' => 'অন্তত একটি আইডি প্রয়োজন।',
            'mass-delete-success' => 'নির্বাচিত আইটেমগুলো মুছে ফেলা হয়েছে।',
            'mass-update-success' => 'নির্বাচিত আইটেমগুলো আপডেট করা হয়েছে।',
            'mass-value-invalid' => 'অবস্থার মান অবশ্যই 0 বা 1 হতে হবে।',
            'image-not-found' => 'রিটার্নের ছবি পাওয়া যায়নি।',
            'attachment-not-found' => 'বার্তার সংযুক্তি পাওয়া যায়নি।',
        ],

        'eu-withdrawal' => [
            'not-found' => 'প্রত্যাহারের ঘোষণা পাওয়া যায়নি।',
            'no-permission' => 'EU প্রত্যাহার পরিচালনা করার অনুমতি আপনার নেই।',
            'declined' => 'প্রত্যাহার প্রত্যাখ্যাত হিসেবে চিহ্নিত করা হয়েছে।',
            'refunded' => 'প্রত্যাহার রিফান্ডকৃত হিসেবে চিহ্নিত করা হয়েছে।',
            'confirmation-resent' => 'নিশ্চিতকরণ ইমেল সফলভাবে পুনরায় পাঠানো হয়েছে।',
            'confirmation-failed' => 'নিশ্চিতকরণ ইমেল পাঠানো যায়নি।',
        ],

        'reporting' => [
            'invalid-entity' => 'অজানা রিপোর্টিং এন্টিটি।',
            'invalid-type' => 'এই এন্টিটির জন্য অজানা রিপোর্টিং পরিসংখ্যানের ধরন।',
            'export-format-unsupported' => 'শুধুমাত্র csv, xls এবং xlsx এক্সপোর্ট ফর্ম্যাট সমর্থিত।',
        ],

        'configuration' => [
            'unauthenticated' => 'আপনি প্রমাণীকৃত নন।',
            'no-permission' => 'কনফিগারেশন আপডেট করার অনুমতি আপনার নেই।',
            'slug-required' => 'কোয়েরি প্যারামিটার `slug` প্রয়োজন।',
            'slug-not-found' => 'প্রদত্ত slug-এর জন্য কোনো কনফিগারেশন সেকশন নিবন্ধিত নেই।',
            'values-required' => '`values`-এর অধীনে অন্তত একটি মান প্রয়োজন।',
            'scope-escape' => '`:key` কী-টি অনুরোধ করা slug সাবট্রির বাইরে।',
            'unknown-field' => '`:key` কী-টি কোনো নিবন্ধিত কনফিগারেশন ফিল্ড নয়।',
            'validation-failed' => 'এক বা একাধিক কনফিগারেশন মান যাচাইয়ে ব্যর্থ হয়েছে।',
            'file-upload-rest-only' => '`:field` ফিল্ডের জন্য বাইনারি ফাইল আপলোড শুধুমাত্র REST multipart এন্ডপয়েন্টে সমর্থিত, GraphQL-এ নয়।',
            'custom-view-readonly' => '`:field` ফিল্ডটি একটি কাস্টম blade-রেন্ডারড ফিল্ড এবং API-এর মাধ্যমে আপডেট করা যাবে না।',
            'update-success' => 'কনফিগারেশন সফলভাবে আপডেট করা হয়েছে।',
        ],

        'customer' => [
            'not-found' => 'গ্রাহক পাওয়া যায়নি।',
            'no-permission' => 'এই কাজটি করার অনুমতি আপনার নেই।',
            'has-active-orders' => 'গ্রাহকের মুলতুবি বা প্রক্রিয়াধীন অর্ডার রয়েছে এবং তাকে মুছে ফেলা যাবে না।',
            'deleted' => 'গ্রাহক সফলভাবে মুছে ফেলা হয়েছে।',
            'mass-delete-indices-required' => 'অন্তত একটি গ্রাহক আইডি প্রয়োজন।',
            'mass-delete-success' => 'গ্রাহকদের মুছে ফেলা হয়েছে।',
            'mass-update-indices-required' => 'অন্তত একটি গ্রাহক আইডি প্রয়োজন।',
            'mass-update-value-invalid' => 'অবস্থার মান অবশ্যই 0 বা 1 হতে হবে।',
            'mass-update-success' => 'গ্রাহকের অবস্থা আপডেট করা হয়েছে।',

            'address' => [
                'not-found' => 'ঠিকানা পাওয়া যায়নি।',
                'not-owned' => 'এই ঠিকানাটি নির্দিষ্ট গ্রাহকের নয়।',
                'customer-id-required' => 'ঠিকানার মালিক শনাক্ত করতে customerId প্রয়োজন।',
                'deleted' => 'ঠিকানা সফলভাবে মুছে ফেলা হয়েছে।',
            ],

            'note' => [
                'empty' => 'নোটের টেক্সট প্রয়োজন।',
                'created' => 'নোট সফলভাবে যোগ করা হয়েছে।',
            ],

            'impersonate' => [
                'success' => 'ইমপারসোনেশন টোকেন ইস্যু করা হয়েছে।',
            ],

            'review' => [
                'not-found' => 'রিভিউ পাওয়া যায়নি।',
                'no-permission' => 'এই কাজটি করার অনুমতি আপনার নেই।',
                'invalid-status' => 'অবস্থা অবশ্যই এগুলোর একটি হতে হবে: pending, approved, disapproved।',
                'updated' => 'রিভিউ সফলভাবে আপডেট করা হয়েছে।',
                'deleted' => 'রিভিউ সফলভাবে মুছে ফেলা হয়েছে।',
                'mass-delete-indices-required' => 'অন্তত একটি রিভিউ আইডি প্রয়োজন।',
                'mass-delete-success' => 'রিভিউগুলো মুছে ফেলা হয়েছে।',
                'mass-update-indices-required' => 'অন্তত একটি রিভিউ আইডি প্রয়োজন।',
                'mass-update-value-invalid' => 'অবস্থার মান অবশ্যই এগুলোর একটি হতে হবে: pending, approved, disapproved।',
                'mass-update-success' => 'রিভিউয়ের অবস্থা আপডেট করা হয়েছে।',
            ],

            'group' => [
                'not-found' => 'গ্রাহক গ্রুপ পাওয়া যায়নি।',
                'deleted' => 'গ্রাহক গ্রুপ সফলভাবে মুছে ফেলা হয়েছে।',
                'is-system' => 'সিস্টেম গ্রাহক গ্রুপ মুছে ফেলা যাবে না।',
                'has-customers' => 'গ্রাহক গ্রুপে গ্রাহক যুক্ত আছে এবং এটি মুছে ফেলা যাবে না।',
                'system-code-immutable' => 'সিস্টেম গ্রাহক গ্রুপের কোড পরিবর্তন করা যাবে না।',
                'system-flag-immutable' => 'সিস্টেম গ্রাহক গ্রুপের is_user_defined ফ্ল্যাগ পরিবর্তন করা যাবে না।',
                'mass-delete-indices-required' => 'অন্তত একটি গ্রাহক গ্রুপ আইডি প্রয়োজন।',
                'mass-delete-success' => 'গ্রাহক গ্রুপগুলো মুছে ফেলা হয়েছে।',
            ],

            'gdpr' => [
                'not-found' => 'GDPR অনুরোধ পাওয়া যায়নি।',
                'no-permission' => 'এই কাজটি করার অনুমতি আপনার নেই।',
                'deleted' => 'GDPR অনুরোধ সফলভাবে মুছে ফেলা হয়েছে।',
                'invalid-status' => 'অবস্থা অবশ্যই এগুলোর একটি হতে হবে: pending, processing, declined, approved, revoked।',
                'no-changes' => 'অবস্থা বা বার্তার অন্তত একটি প্রয়োজন।',
                'already-processed' => 'এই GDPR অনুরোধটি ইতিমধ্যে প্রক্রিয়া করা হয়েছে এবং পুনরায় চালানো যাবে না।',

                'process' => [
                    'approved' => 'GDPR অনুরোধ অনুমোদিত হয়েছে।',
                    'deleted' => 'GDPR অনুরোধ অনুমোদিত হয়েছে এবং গ্রাহককে মুছে ফেলা হয়েছে।',
                ],
            ],
        ],

        'order' => [
            'not-found' => 'অর্ডার পাওয়া যায়নি।',

            'reorder' => [
                'success' => 'পুনরায় অর্ডার সফল। একটি নতুন ড্রাফট কার্ট তৈরি করা হয়েছে।',
                'cannot-reorder' => 'এই অর্ডারটি পুনরায় করা যাবে না (অতিথি অর্ডার অথবা এক বা একাধিক আইটেম আর বিক্রয়যোগ্য নয়)।',
                'failed' => 'পুনরায় অর্ডার ব্যর্থ হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।',
                'guest-not-supported' => 'অতিথি অর্ডারের জন্য পুনরায় অর্ডার সমর্থিত নয়।',
                'items-not-saleable' => 'এই অর্ডারের এক বা একাধিক আইটেম আর কেনার জন্য উপলব্ধ নেই।',
                'no-permission' => 'অর্ডার তৈরি করার অনুমতি আপনার নেই।',
                'disabled-in-settings' => 'স্টোর সেটিংসে অ্যাডমিনের মাধ্যমে পুনরায় অর্ডার নিষ্ক্রিয় করা আছে।',
            ],

            'actions' => [
                'cancel' => [
                    'closed' => 'বন্ধ অর্ডার বাতিল করা যাবে না।',
                    'fraud' => 'প্রতারণামূলক অর্ডার বাতিল করা যাবে না।',
                    'nothing-to-cancel' => 'এই অর্ডারে বাতিল করার মতো কিছু নেই।',
                    'already-processed' => 'এই অর্ডারটির ইতিমধ্যে ইনভয়েস তৈরি বা শিপ করা হয়েছে এবং এটি আর বাতিল করা যাবে না। এর পরিবর্তে আপনি রিফান্ড দিতে পারেন।',
                    'no-permission' => 'অর্ডার বাতিল করার অনুমতি আপনার নেই।',
                    'success' => 'অর্ডার সফলভাবে বাতিল করা হয়েছে।',
                    'failed' => 'অর্ডারটি বাতিল করা যায়নি।',
                ],

                'comment' => [
                    'empty' => 'মন্তব্য প্রয়োজন।',
                    'success' => 'অর্ডারে মন্তব্য যোগ করা হয়েছে।',
                    'failed' => 'মন্তব্য যোগ করা যায়নি।',
                ],

                'invoice' => [
                    'closed' => 'বন্ধ অর্ডারের ইনভয়েস তৈরি করা যাবে না।',
                    'fraud' => 'প্রতারণামূলক অর্ডারের ইনভয়েস তৈরি করা যাবে না।',
                    'nothing-to-invoice' => 'এই অর্ডারে ইনভয়েস করার মতো কিছু নেই।',
                    'already-invoiced' => 'এই অর্ডারের জন্য ইতিমধ্যে একটি ইনভয়েস তৈরি করা হয়েছে।',
                    'paypal-standard-blocked' => 'PayPal Standard-এর মাধ্যমে পরিশোধিত অর্ডারের জন্য ইনভয়েস তৈরি করা যাবে না।',
                    'no-permission' => 'ইনভয়েস তৈরি করার অনুমতি আপনার নেই।',
                    'items-required' => 'ধনাত্মক পরিমাণসহ অন্তত একটি আইটেম প্রয়োজন।',
                    'qty-exceeds' => 'SKU :sku-এর জন্য অনুরোধ করা পরিমাণ (:requested) উপলব্ধ পরিমাণ (:available)-এর চেয়ে বেশি।',
                    'not-found' => 'ইনভয়েস পাওয়া যায়নি।',
                    'success' => 'ইনভয়েস সফলভাবে তৈরি করা হয়েছে।',
                    'failed' => 'ইনভয়েস তৈরি করা যায়নি।',
                    'pdf-failed' => 'ইনভয়েসের PDF তৈরি করা যায়নি।',
                    'email-invalid' => 'একটি বৈধ প্রাপকের ইমেল প্রয়োজন।',
                    'duplicate-sent' => 'ইনভয়েস ইমেল :email-এ পাঠানো হয়েছে।',
                    'mass-status-indices-required' => 'অন্তত একটি ইনভয়েস আইডি প্রয়োজন।',
                    'mass-status-value-invalid' => 'অবস্থা অবশ্যই এগুলোর একটি হতে হবে: pending, paid, overdue।',
                    'mass-status-success' => 'ইনভয়েসের অবস্থা সফলভাবে আপডেট করা হয়েছে।',
                ],

                'shipment' => [
                    'closed' => 'বন্ধ অর্ডার শিপ করা যাবে না।',
                    'fraud' => 'প্রতারণামূলক অর্ডার শিপ করা যাবে না।',
                    'nothing-to-ship' => 'এই অর্ডারে শিপ করার মতো কিছু নেই।',
                    'no-permission' => 'অর্ডার শিপ করার অনুমতি আপনার নেই।',
                    'source-required' => 'ইনভেন্টরি সোর্স প্রয়োজন।',
                    'items-required' => 'ধনাত্মক পরিমাণসহ অন্তত একটি আইটেম প্রয়োজন।',
                    'qty-exceeds' => 'SKU :sku-এর জন্য অনুরোধ করা পরিমাণ (:requested) উপলব্ধ পরিমাণ (:available)-এর চেয়ে বেশি।',
                    'inventory-insufficient' => 'নির্বাচিত সোর্সে SKU :sku-এর জন্য পর্যাপ্ত ইনভেন্টরি নেই।',
                    'not-found' => 'শিপমেন্ট পাওয়া যায়নি।',
                    'success' => 'শিপমেন্ট সফলভাবে তৈরি করা হয়েছে।',
                    'failed' => 'শিপমেন্ট তৈরি করা যায়নি।',
                ],

                'refund' => [
                    'closed' => 'বন্ধ অর্ডারের রিফান্ড দেওয়া যাবে না।',
                    'fraud' => 'প্রতারণামূলক অর্ডারের রিফান্ড দেওয়া যাবে না।',
                    'nothing-to-refund' => 'এই অর্ডারে রিফান্ড দেওয়ার মতো আর কিছু বাকি নেই।',
                    'no-permission' => 'রিফান্ড তৈরি করার অনুমতি আপনার নেই।',
                    'qty-exceeds' => 'SKU :sku-এর জন্য অনুরোধ করা পরিমাণ (:requested) উপলব্ধ পরিমাণ (:available)-এর চেয়ে বেশি।',
                    'amount-zero' => 'গণনা করা রিফান্ডের পরিমাণ শূন্য। পরিমাণ, শিপিং বা সমন্বয়ের মান পরিবর্তন করুন।',
                    'amount-exceeds-max' => 'রিফান্ডের পরিমাণ (:amount) সর্বোচ্চ রিফান্ডযোগ্য পরিমাণ (:max) অতিক্রম করেছে।',
                    'not-found' => 'রিফান্ড পাওয়া যায়নি।',
                    'success' => 'রিফান্ড সফলভাবে তৈরি করা হয়েছে।',
                    'failed' => 'রিফান্ড তৈরি করা যায়নি।',
                ],
            ],
        ],

        'appearance' => [
            'no-permission' => 'অ্যাপিয়ারেন্স পরিচালনা করার অনুমতি আপনার নেই।',

            'theme' => [
                'not-found' => 'থিম পাওয়া যায়নি।',
                'not-installed' => 'এই থিমটি ইনস্টল করা নেই।',
                'channel-ids-required' => 'অন্তত একটি চ্যানেল প্রয়োজন।',
                'unknown-channel' => 'এক বা একাধিক চ্যানেল বিদ্যমান নেই।',
                'activated' => 'থিম সফলভাবে সক্রিয় করা হয়েছে।',
            ],

            'section' => [
                'not-found' => 'সেকশন পাওয়া যায়নি।',
                'invalid-payload' => 'অসমর্থিত সেকশন পেলোড।',
                'footer-links-exists' => 'এই চ্যানেলে ইতিমধ্যে একটি ফুটার লিংক সেকশন রয়েছে।',
                'options-required' => 'অপশন প্রয়োজন।',
                'status-required' => 'অবস্থা প্রয়োজন।',
                'section-ids-required' => 'অন্তত একটি সেকশন প্রয়োজন।',
                'reorder-single-scope' => 'সেকশনগুলো একবারে শুধুমাত্র একটি থিম ও চ্যানেলের মধ্যে পুনর্বিন্যাস করা যায়।',
                'reorder-incomplete' => 'পুনর্বিন্যাসের জন্য থিম ও চ্যানেলের প্রতিটি সেকশন প্রয়োজন, যে ক্রমে সেগুলো রেন্ডার হওয়া উচিত সেই ক্রমে।',
                'unknown-section' => 'এক বা একাধিক সেকশন বিদ্যমান নেই।',
                'file-required' => 'একটি ফাইল প্রয়োজন।',
                'created' => 'সেকশন সফলভাবে তৈরি করা হয়েছে।',
                'updated' => 'সেকশন সফলভাবে আপডেট করা হয়েছে।',
                'deleted' => 'সেকশন সফলভাবে মুছে ফেলা হয়েছে।',
                'draft-saved' => 'সেকশনের পরিবর্তনগুলো সফলভাবে প্রকাশের জন্য প্রস্তুত করা হয়েছে।',
                'status-staged' => 'সেকশনের অবস্থা সফলভাবে প্রকাশের জন্য প্রস্তুত করা হয়েছে।',
                'order-staged' => 'সেকশনের ক্রম সফলভাবে প্রকাশের জন্য প্রস্তুত করা হয়েছে।',
                'duplicated' => 'সেকশন সফলভাবে কপি করা হয়েছে।',
                'published' => 'সেকশনের পরিবর্তনগুলো সফলভাবে প্রকাশিত হয়েছে।',
                'discarded' => 'সেকশনের পরিবর্তনগুলো সফলভাবে বাতিল করা হয়েছে।',
                'media-uploaded' => 'মিডিয়া সফলভাবে আপলোড করা হয়েছে।',
            ],
        ],

        'sales' => [
            'no-permission' => 'এই বিক্রয় রিসোর্স দেখার অনুমতি আপনার নেই।',

            'invoice' => [
                'not-found' => 'ইনভয়েস পাওয়া যায়নি।',
                'no-permission' => 'ইনভয়েস দেখার অনুমতি আপনার নেই।',
            ],

            'shipment' => [
                'not-found' => 'শিপমেন্ট পাওয়া যায়নি।',
                'no-permission' => 'শিপমেন্ট দেখার অনুমতি আপনার নেই।',
            ],

            'refund' => [
                'not-found' => 'রিফান্ড পাওয়া যায়নি।',
                'no-permission' => 'রিফান্ড দেখার অনুমতি আপনার নেই।',
                'export-format-unsupported' => 'শুধুমাত্র csv, xls এবং xlsx এক্সপোর্ট সমর্থিত।',
            ],

            'transaction' => [
                'not-found' => 'লেনদেন পাওয়া যায়নি।',
                'no-permission' => 'লেনদেন দেখার অনুমতি আপনার নেই।',

                'create' => [
                    'invoice-required' => 'invoiceId ফিল্ডটি প্রয়োজন।',
                    'payment-method-required' => 'paymentMethod ফিল্ডটি প্রয়োজন।',
                    'amount-invalid' => 'amount ফিল্ডটি প্রয়োজন এবং এটি অবশ্যই সংখ্যাসূচক হতে হবে।',
                    'invoice-missing' => 'নির্দিষ্ট ইনভয়েসটি পাওয়া যায়নি।',
                    'already-paid' => 'এই ইনভয়েসটি ইতিমধ্যে সম্পূর্ণ পরিশোধিত।',
                    'amount-exceeds' => 'পেমেন্টের পরিমাণ ইনভয়েসের সর্বমোট পরিমাণের চেয়ে বেশি।',
                    'amount-zero' => 'পেমেন্টের পরিমাণ অবশ্যই শূন্যের চেয়ে বেশি হতে হবে।',
                ],
            ],

            'booking' => [
                'not-found' => 'বুকিং পাওয়া যায়নি।',
                'no-permission' => 'বুকিং দেখার অনুমতি আপনার নেই।',
            ],

            'export' => [
                'format-unsupported' => 'শুধুমাত্র csv, xls এবং xlsx এক্সপোর্ট সমর্থিত।',
            ],
        ],

        'cart' => [
            'not-found' => 'কার্ট পাওয়া যায়নি।',
            'not-draft' => 'এই কার্টটি অ্যাডমিন API-এর মাধ্যমে পরিবর্তন করা যাবে না।',
            'product-required' => 'productId প্রয়োজন।',
            'product-not-found' => 'পণ্য পাওয়া যায়নি।',
            'booking-unsupported' => 'বুকিং পণ্য অ্যাডমিনের ড্রাফট অর্ডারে যোগ করা যাবে না। বুকিং কেনাকাটা অবশ্যই গ্রাহকের স্টোরফ্রন্টের মাধ্যমে করতে হবে।',
            'product-not-saleable' => 'এই পণ্যটি বিক্রয়ের জন্য উপলব্ধ নয় (স্টকে নেই বা নিষ্ক্রিয়) এবং অর্ডারে যোগ করা যাবে না।',
            'qty-required' => 'qty প্রয়োজন এবং এটি অবশ্যই itemId => পরিমাণ-এর একটি খালি নয় এমন অবজেক্ট হতে হবে।',
            'cart-item-required' => 'cartItemId প্রয়োজন।',
            'item-added' => 'আইটেম কার্টে যোগ করা হয়েছে।',
            'item-add-failed' => 'আইটেমটি কার্টে যোগ করা যায়নি।',
            'item-updated' => 'কার্টের আইটেমগুলো আপডেট করা হয়েছে।',
            'item-update-failed' => 'কার্টের আইটেমগুলো আপডেট করা যায়নি।',
            'item-removed' => 'আইটেম কার্ট থেকে সরানো হয়েছে।',
            'item-remove-failed' => 'কার্ট আইটেমটি সরানো যায়নি।',
            'address-saved' => 'ঠিকানা সংরক্ষিত হয়েছে।',
            'address-required' => 'একটি বিলিং ঠিকানা প্রয়োজন।',
            'address-incomplete' => 'ঠিকানা সংরক্ষণ করতে :field প্রয়োজন।',
            'coupon-code-required' => 'একটি কুপন কোড প্রয়োজন।',
            'coupon-applied' => 'কুপন প্রয়োগ করা হয়েছে।',
            'coupon-already-applied' => 'কুপনটি ইতিমধ্যে এই কার্টে প্রয়োগ করা হয়েছে।',
            'coupon-not-found' => 'কুপন পাওয়া যায়নি বা নিষ্ক্রিয়।',
            'coupon-removed' => 'কুপন সরানো হয়েছে।',
            'coupon-error' => 'কুপন প্রয়োগ করার সময় কিছু ভুল হয়েছে।',
            'unknown-error' => 'কিছু ভুল হয়েছে।',
            'draft-created' => 'ড্রাফট কার্ট তৈরি করা হয়েছে।',
            'draft-failed' => 'ড্রাফট কার্ট তৈরি করা যায়নি।',
            'addresses-required-for-shipping' => 'শিপিং পদ্ধতি নির্বাচনের আগে ঠিকানা সংরক্ষণ করতে হবে।',
            'shipping-required-for-payment' => 'পেমেন্ট পদ্ধতির আগে শিপিং পদ্ধতি নির্বাচন করতে হবে।',
            'shipping-method-required' => 'শিপিং পদ্ধতি প্রয়োজন।',
            'shipping-method-saved' => 'শিপিং পদ্ধতি সংরক্ষিত হয়েছে।',
            'shipping-method-failed' => 'শিপিং পদ্ধতি সংরক্ষণ করা যায়নি।',
            'payment-method-required' => 'পেমেন্ট পদ্ধতি প্রয়োজন।',
            'payment-method-saved' => 'পেমেন্ট পদ্ধতি সংরক্ষিত হয়েছে।',
            'payment-method-failed' => 'পেমেন্ট পদ্ধতি সংরক্ষণ করা যায়নি।',

            'place-order' => [
                'success' => 'অর্ডার সফলভাবে দেওয়া হয়েছে।',
                'failed' => 'অর্ডার দেওয়া যায়নি।',
                'error' => 'অর্ডার দেওয়া যায়নি: কার্টে ত্রুটি রয়েছে।',
                'minimum-order-error' => 'ন্যূনতম অর্ডারের পরিমাণ :amount। অর্ডার দেওয়ার আগে আরও আইটেম যোগ করুন।',
                'empty-cart' => 'কার্ট খালি — অর্ডার দেওয়ার আগে আইটেম যোগ করুন।',
                'addresses-required' => 'অর্ডার দেওয়ার আগে বিলিং ও শিপিং ঠিকানা সংরক্ষণ করতে হবে।',
                'shipping-required' => 'অর্ডার দেওয়ার আগে একটি শিপিং পদ্ধতি নির্বাচন করতে হবে।',
                'payment-required' => 'অর্ডার দেওয়ার আগে একটি পেমেন্ট পদ্ধতি নির্বাচন করতে হবে।',
                'payment-method-unsupported' => 'অ্যাডমিনের দেওয়া অর্ডারের জন্য শুধুমাত্র cashondelivery এবং moneytransfer সমর্থিত।',
            ],
        ],

        'product' => [
            'not-found' => 'পণ্য পাওয়া যায়নি।',

            'mass-delete-success' => 'পণ্যগুলো সফলভাবে মুছে ফেলা হয়েছে।',
            'mass-update-status-success' => 'পণ্যগুলোর অবস্থা সফলভাবে আপডেট করা হয়েছে।',
            'indices-required' => 'indices ফিল্ডটি প্রয়োজন এবং এটি অবশ্যই একটি খালি নয় এমন অ্যারে হতে হবে।',
            'indices-invalid' => 'indices ফিল্ডটি অবশ্যই পূর্ণসংখ্যা পণ্য আইডির একটি অ্যারে হতে হবে।',
            'value-invalid' => 'value ফিল্ডটি প্রয়োজন এবং এটি অবশ্যই 0 বা 1 হতে হবে।',
            'mass-delete-failed' => 'এক বা একাধিক পণ্য মুছে ফেলা যায়নি।',
            'no-permission' => 'পণ্য পরিচালনা করার অনুমতি আপনার নেই।',

            'copy-success' => 'পণ্য সফলভাবে কপি করা হয়েছে।',
            'copy-failed' => 'পণ্য কপি করতে ব্যর্থ।',
            'copy-variant-not-supported' => 'configurable পণ্যের ভ্যারিয়েন্ট কপি করা যাবে না। এর পরিবর্তে প্যারেন্ট configurable পণ্যটি কপি করুন।',

            'create' => [
                'created' => 'পণ্য সফলভাবে তৈরি করা হয়েছে।',
                'create-failed' => 'পণ্য তৈরি করতে ব্যর্থ।',
                'sku-required' => 'sku ফিল্ডটি প্রয়োজন।',
                'sku-unique' => 'এই sku ইতিমধ্যে নেওয়া হয়েছে।',
                'sku-invalid' => 'sku-তে শুধুমাত্র অক্ষর, সংখ্যা এবং হাইফেন থাকতে পারে (যেমন "sp-001")।',
                'attribute-family-required' => 'attribute_family_id ফিল্ডটি প্রয়োজন।',
                'attribute-family-not-found' => 'নির্বাচিত অ্যাট্রিবিউট ফ্যামিলি বিদ্যমান নেই।',
                'type-required' => 'type ফিল্ডটি প্রয়োজন।',
                'type-not-yet-supported' => 'পণ্যের ধরন ":type" এই API দ্বারা সমর্থিত নয়। অনুমোদিত ধরন: simple, virtual, downloadable, grouped, bundle, configurable, booking।',
                'super-attributes-required' => 'type=configurable হলে super_attributes ফিল্ডটি প্রয়োজন। অ্যাট্রিবিউট কোড (বা id) থেকে option_ids-এর একটি ম্যাপ দিন, যেমন { "color": [1,2], "size": [4,5] }।',
                'super-attributes-invalid' => 'super_attributes ফিল্ডটি অবশ্যই অ্যাট্রিবিউট কোড (বা id) থেকে option_ids-এর খালি নয় এমন তালিকার একটি খালি নয় এমন ম্যাপ হতে হবে।',
                'no-permission' => 'পণ্য তৈরি করার অনুমতি আপনার নেই।',
            ],

            'update' => [
                'updated' => 'পণ্য সফলভাবে আপডেট করা হয়েছে।',
                'update-failed' => 'পণ্য আপডেট করতে ব্যর্থ।',
                'id-required' => 'একটি পণ্য id প্রয়োজন।',
                'url-key-required' => 'url_key ফিল্ডটি খালি রাখা যাবে না।',
                'url-key-unique' => 'এই url_key ইতিমধ্যে অন্য একটি পণ্য নিয়েছে।',
                'special-price-invalid' => 'special_price অবশ্যই price-এর চেয়ে কম একটি সংখ্যা হতে হবে।',
                'special-price-date-range-invalid' => 'special_price_to অবশ্যই special_price_from-এর দিন বা তার পরে হতে হবে।',
                'boolean-field-invalid' => ':field ফিল্ডটি অবশ্যই 0 বা 1 হতে হবে।',
                'categories-invalid' => 'categories ফিল্ডটি অবশ্যই ক্যাটাগরি id-এর একটি অ্যারে হতে হবে।',
                'channels-invalid' => 'channels ফিল্ডটি অবশ্যই চ্যানেল id-এর একটি অ্যারে হতে হবে।',
                'super-attributes-cannot-change' => 'পণ্য তৈরির পরে super_attributes পরিবর্তন করা যাবে না।',
                'sub-resource-stripped-images' => 'ছবিগুলো POST /api/admin/catalog/products/{id}/images-এর মাধ্যমে পরিচালনা করতে হবে। images ফিল্ডটি উপেক্ষা করা হয়েছে।',
                'sub-resource-stripped-inventories' => 'ইনভেন্টরি PUT /api/admin/catalog/products/{id}/inventories-এর মাধ্যমে পরিচালনা করতে হবে। inventories ফিল্ডটি উপেক্ষা করা হয়েছে।',
                'sub-resource-stripped-customer-group-prices' => 'গ্রাহক-গ্রুপ মূল্য /api/admin/catalog/products/{id}/customer-group-prices-এর মাধ্যমে পরিচালনা করতে হবে। customer_group_prices ফিল্ডটি উপেক্ষা করা হয়েছে।',
                'translations-single-locale' => 'শুধুমাত্র অনুরোধ করা লোকেল আপডেট করা হয়েছে। :locales লোকেল আপডেট করতে, প্রতিটির জন্য ?locale=<code> সহ আলাদা অনুরোধ পাঠান।',
            ],

            'delete' => [
                'deleted' => 'পণ্য সফলভাবে মুছে ফেলা হয়েছে।',
                'delete-failed' => 'পণ্য মুছতে ব্যর্থ।',
            ],

            'image' => [
                'uploaded' => 'পণ্যের ছবি সফলভাবে আপলোড করা হয়েছে।',
                'updated' => 'পণ্যের ছবি সফলভাবে আপডেট করা হয়েছে।',
                'update-empty' => 'আপডেট করতে একটি alt টেক্সট বা অবস্থান পাঠান।',
                'reordered' => 'পণ্যের ছবিগুলো সফলভাবে পুনর্বিন্যাস করা হয়েছে।',
                'deleted' => 'পণ্যের ছবি সফলভাবে মুছে ফেলা হয়েছে।',
                'image-required' => 'একটি ছবির ফাইল প্রয়োজন (multipart ফিল্ড "image")।',
                'image-invalid-type' => 'ছবির ধরন অবশ্যই এগুলোর একটি হতে হবে: bmp, jpeg, jpg, png, webp।',
                'image-too-large' => 'ছবিটি খুব বড়। সর্বোচ্চ অনুমোদিত আকার 4 MB।',
                'not-found' => 'পণ্যের ছবি পাওয়া যায়নি।',
                'no-permission' => 'পণ্যের ছবি পরিচালনা করার অনুমতি আপনার নেই।',
                'upload-failed' => 'পণ্যের ছবি আপলোড করতে ব্যর্থ।',
                'reorder-failed' => 'পণ্যের ছবিগুলো পুনর্বিন্যাস করতে ব্যর্থ।',
                'delete-failed' => 'পণ্যের ছবি মুছতে ব্যর্থ।',
                'order-required' => '"order" অ্যারে প্রয়োজন এবং এটি খালি হওয়া চলবে না।',
                'order-invalid' => '"order"-এর প্রতিটি এন্ট্রি অবশ্যই id এবং position সহ একটি অবজেক্ট হতে হবে।',
                'id-not-on-product' => 'ছবি :id এই পণ্যের নয়।',
                'graphql-upload-unsupported' => 'GraphQL-এর মাধ্যমে ছবি আপলোড সমর্থিত নয়। multipart/form-data সহ POST /api/admin/catalog/products/{productId}/images ব্যবহার করুন।',
            ],

            'downloadable' => [
                'file-required' => 'একটি ফাইল প্রয়োজন (multipart ফিল্ড "file")।',
                'file-too-large' => 'ফাইলটি খুব বড়। সর্বোচ্চ অনুমোদিত আকার 128 MB।',
                'upload-failed' => 'ডাউনলোডযোগ্য ফাইল আপলোড করতে ব্যর্থ।',
                'file-not-found' => 'এই পণ্য ও অ্যাট্রিবিউটের জন্য কোনো সংরক্ষিত ফাইল পাওয়া যায়নি।',
                'no-permission' => 'ডাউনলোডযোগ্য পণ্যের ফাইল পরিচালনা করার অনুমতি আপনার নেই।',
            ],

            'video' => [
                'uploaded' => 'পণ্যের ভিডিও সফলভাবে আপলোড করা হয়েছে।',
                'deleted' => 'পণ্যের ভিডিও সফলভাবে মুছে ফেলা হয়েছে।',
                'video-required' => 'একটি ভিডিও ফাইল প্রয়োজন (multipart ফিল্ড "video")।',
                'video-invalid-type' => 'ভিডিওর ধরন অবশ্যই এগুলোর একটি হতে হবে: mp4, webm, ogg, mov।',
                'video-too-large' => 'ভিডিওটি খুব বড়। সর্বোচ্চ অনুমোদিত আকার 100 MB।',
                'not-found' => 'পণ্যের ভিডিও পাওয়া যায়নি।',
                'no-permission' => 'পণ্যের ভিডিও পরিচালনা করার অনুমতি আপনার নেই।',
                'upload-failed' => 'পণ্যের ভিডিও আপলোড করতে ব্যর্থ।',
                'delete-failed' => 'পণ্যের ভিডিও মুছতে ব্যর্থ।',
                'graphql-upload-unsupported' => 'GraphQL-এর মাধ্যমে ভিডিও আপলোড সমর্থিত নয়। multipart/form-data সহ POST /api/admin/catalog/products/{productId}/videos ব্যবহার করুন।',
            ],

            'inventory' => [
                'updated' => 'পণ্যের ইনভেন্টরি সফলভাবে সংরক্ষিত হয়েছে।',
                'inventories-required' => 'inventories ফিল্ডটি প্রয়োজন এবং এটি অবশ্যই inventory_source_id থেকে পরিমাণের একটি খালি নয় এমন ম্যাপ হতে হবে।',
                'inventories-invalid' => 'inventories ফিল্ডটি অবশ্যই inventory_source_id (ধনাত্মক পূর্ণসংখ্যা) থেকে পরিমাণ (অ-ঋণাত্মক পূর্ণসংখ্যা)-এর একটি ম্যাপ হতে হবে।',
                'source-not-found' => 'ইনভেন্টরি সোর্স :id বিদ্যমান নেই।',
                'qty-invalid' => 'প্রতিটি পরিমাণ অবশ্যই একটি অ-ঋণাত্মক পূর্ণসংখ্যা হতে হবে।',
                'no-permission' => 'পণ্যের ইনভেন্টরি পরিচালনা করার অনুমতি আপনার নেই।',
                'not-found' => 'পণ্য পাওয়া যায়নি।',
            ],

            'customer-group-price' => [
                'created' => 'গ্রাহক-গ্রুপ মূল্য সফলভাবে যোগ করা হয়েছে।',
                'updated' => 'গ্রাহক-গ্রুপ মূল্য সফলভাবে আপডেট করা হয়েছে।',
                'deleted' => 'গ্রাহক-গ্রুপ মূল্য সফলভাবে মুছে ফেলা হয়েছে।',
                'qty-required' => 'qty ফিল্ডটি প্রয়োজন।',
                'qty-invalid' => 'qty ফিল্ডটি অবশ্যই 1 বা তার বেশি একটি পূর্ণসংখ্যা হতে হবে।',
                'value-type-required' => 'value_type ফিল্ডটি প্রয়োজন।',
                'value-type-invalid' => 'value_type ফিল্ডটি অবশ্যই "fixed" অথবা "discount" হতে হবে।',
                'value-required' => 'value ফিল্ডটি প্রয়োজন।',
                'value-invalid' => 'value ফিল্ডটি অবশ্যই একটি অ-ঋণাত্মক সংখ্যা হতে হবে।',
                'customer-group-not-found' => 'নির্বাচিত গ্রাহক গ্রুপ বিদ্যমান নেই।',
                'duplicate-qty-group' => 'এই পণ্যের জন্য এই (qty, customer_group_id) সমন্বয়ের একটি গ্রাহক-গ্রুপ মূল্য ইতিমধ্যে বিদ্যমান।',
                'not-found' => 'গ্রাহক-গ্রুপ মূল্য পাওয়া যায়নি।',
                'no-permission' => 'পণ্যের গ্রাহক-গ্রুপ মূল্য পরিচালনা করার অনুমতি আপনার নেই।',
            ],
        ],

        'category' => [
            'not-found' => 'ক্যাটাগরি পাওয়া যায়নি।',

            'created' => 'ক্যাটাগরি সফলভাবে তৈরি করা হয়েছে।',
            'updated' => 'ক্যাটাগরি সফলভাবে আপডেট করা হয়েছে।',
            'deleted' => 'ক্যাটাগরি সফলভাবে মুছে ফেলা হয়েছে।',
            'slug-required' => 'slug ফিল্ডটি প্রয়োজন।',
            'slug-unique' => 'এই slug ইতিমধ্যে নেওয়া হয়েছে।',
            'name-required' => 'name ফিল্ডটি প্রয়োজন।',
            'position-required' => 'position ফিল্ডটি প্রয়োজন।',
            'attributes-required' => 'অন্তত একটি ফিল্টারযোগ্য অ্যাট্রিবিউট প্রয়োজন।',
            'description-required-for-mode' => 'নির্বাচিত ডিসপ্লে মোডের জন্য একটি বিবরণ প্রয়োজন।',
            'cannot-delete-root' => 'রুট এবং চ্যানেল-রুট ক্যাটাগরি মুছে ফেলা যাবে না।',
            'cannot-delete-channel-root' => 'এই ক্যাটাগরিটি একটি চ্যানেল রুট এবং এটি মুছে ফেলা যাবে না।',
            'delete-failed' => 'ক্যাটাগরি মুছে ফেলা যায়নি।',
            'no-permission' => 'ক্যাটাগরি পরিচালনা করার অনুমতি আপনার নেই।',
            'mass-delete-success' => 'ক্যাটাগরিগুলো সফলভাবে মুছে ফেলা হয়েছে।',
            'mass-update-status-success' => 'ক্যাটাগরিগুলোর অবস্থা সফলভাবে আপডেট করা হয়েছে।',
            'mass-delete-indices-required' => 'indices ফিল্ডটি প্রয়োজন এবং এটি অবশ্যই একটি খালি নয় এমন অ্যারে হতে হবে।',
            'mass-update-status-value-required' => 'value ফিল্ডটি প্রয়োজন এবং এটি অবশ্যই 0 বা 1 হতে হবে।',
        ],

        'attribute' => [
            'not-found' => 'অ্যাট্রিবিউট পাওয়া যায়নি।',

            'create-success' => 'অ্যাট্রিবিউট সফলভাবে তৈরি করা হয়েছে।',
            'update-success' => 'অ্যাট্রিবিউট সফলভাবে আপডেট করা হয়েছে।',
            'delete-success' => 'অ্যাট্রিবিউট সফলভাবে মুছে ফেলা হয়েছে।',
            'delete-failed' => 'অ্যাট্রিবিউট মুছে ফেলা যায়নি।',
            'mass-delete-success' => 'অ্যাট্রিবিউটগুলো সফলভাবে মুছে ফেলা হয়েছে।',
            'mass-delete-indices-required' => 'indices ফিল্ডটি প্রয়োজন এবং এটি অবশ্যই একটি খালি নয় এমন অ্যারে হতে হবে।',

            'code-immutable' => 'অ্যাট্রিবিউট তৈরি হওয়ার পরে অ্যাট্রিবিউট কোড পরিবর্তন করা যাবে না।',
            'type-immutable' => 'পণ্যের মান থাকা অবস্থায় অ্যাট্রিবিউটের ধরন পরিবর্তন করা যাবে না।',
            'locale-scope-immutable' => 'অ্যাট্রিবিউটের পণ্যের মান থাকা অবস্থায় লোকেল-স্কোপ পরিবর্তন করা যাবে না।',
            'system-attribute' => 'সিস্টেম অ্যাট্রিবিউট মুছে ফেলা যাবে না।',
            'in-use-family' => 'অ্যাট্রিবিউটটি এক বা একাধিক অ্যাট্রিবিউট ফ্যামিলির অংশ (গ্রুপ আইডি: :ids)। প্রথমে সেই ফ্যামিলিগুলো থেকে এটি সরান।',

            'option-not-found' => 'অ্যাট্রিবিউট অপশন পাওয়া যায়নি।',
            'option-not-supported' => 'অ্যাট্রিবিউটের ধরন ":type" অপশন সমর্থন করে না। শুধুমাত্র select, multiselect এবং checkbox অ্যাট্রিবিউটের অপশন থাকতে পারে।',
            'option-in-use' => 'এই অপশনটি :count টি পণ্যে ব্যবহৃত হচ্ছে এবং এটি মুছে ফেলা যাবে না।',
            'option-delete-success' => 'অ্যাট্রিবিউট অপশন সফলভাবে মুছে ফেলা হয়েছে।',
        ],

        'family' => [
            'not-found' => 'অ্যাট্রিবিউট ফ্যামিলি পাওয়া যায়নি।',

            'created' => 'অ্যাট্রিবিউট ফ্যামিলি সফলভাবে তৈরি করা হয়েছে।',
            'updated' => 'অ্যাট্রিবিউট ফ্যামিলি সফলভাবে আপডেট করা হয়েছে।',
            'deleted' => 'অ্যাট্রিবিউট ফ্যামিলি সফলভাবে মুছে ফেলা হয়েছে।',
            'code-required' => 'ফ্যামিলি কোড প্রয়োজন।',
            'code-unique' => 'ফ্যামিলি কোড অবশ্যই অনন্য হতে হবে।',
            'name-required' => 'ফ্যামিলির নাম প্রয়োজন।',
            'group-code-required' => 'প্রতিটি অ্যাট্রিবিউট গ্রুপের অবশ্যই একটি কোড থাকতে হবে।',
            'group-name-required' => 'প্রতিটি অ্যাট্রিবিউট গ্রুপের অবশ্যই একটি নাম থাকতে হবে।',
            'group-column-invalid' => 'প্রতিটি অ্যাট্রিবিউট গ্রুপের কলাম অবশ্যই 1 বা 2 হতে হবে।',
            'group-name-duplicate' => 'অ্যাট্রিবিউট ফ্যামিলির মধ্যে অ্যাট্রিবিউট গ্রুপের নাম অবশ্যই অনন্য হতে হবে।',
            'update-failed' => 'অ্যাট্রিবিউট ফ্যামিলি আপডেট করা যায়নি।',
            'create-failed' => 'অ্যাট্রিবিউট ফ্যামিলি তৈরি করা যায়নি।',
            'default-delete-error' => 'ডিফল্ট অ্যাট্রিবিউট ফ্যামিলি মুছে ফেলা যাবে না।',
            'last-delete-error' => 'অন্তত একটি অ্যাট্রিবিউট ফ্যামিলি প্রয়োজন।',
            'attribute-product-error' => 'এই অ্যাট্রিবিউট ফ্যামিলিটি এক বা একাধিক পণ্য ব্যবহার করছে। মুছে ফেলার আগে সেই পণ্যগুলো পুনরায় বরাদ্দ করুন।',
            'delete-failed' => 'অ্যাট্রিবিউট ফ্যামিলি মুছে ফেলা যায়নি।',
            'no-permission' => 'অ্যাট্রিবিউট ফ্যামিলি পরিচালনা করার অনুমতি আপনার নেই।',
        ],

        'settings' => [
            'locale' => [
                'not-found' => 'লোকেল পাওয়া যায়নি।',
                'created' => 'লোকেল সফলভাবে তৈরি করা হয়েছে।',
                'updated' => 'লোকেল সফলভাবে আপডেট করা হয়েছে।',
                'deleted' => 'লোকেল সফলভাবে মুছে ফেলা হয়েছে।',
                'cannot-delete-last' => 'অন্তত একটি লোকেল প্রয়োজন — শেষ অবশিষ্ট লোকেলটি মুছে ফেলা যাবে না।',
                'cannot-delete-channel-default' => 'এই লোকেলটি এক বা একাধিক চ্যানেলের ডিফল্ট লোকেল এবং এটি মুছে ফেলা যাবে না।',
                'delete-failed' => 'লোকেল মুছে ফেলা যায়নি।',
                'no-permission' => 'লোকেল পরিচালনা করার অনুমতি আপনার নেই।',
                'mass-delete-success' => 'লোকেলগুলো সফলভাবে মুছে ফেলা হয়েছে।',
                'mass-delete-indices-required' => 'indices ফিল্ডটি প্রয়োজন এবং এটি অবশ্যই একটি খালি নয় এমন অ্যারে হতে হবে।',
            ],

            'theme' => [
                'not-found' => 'থিম কাস্টমাইজেশন পাওয়া যায়নি।',
                'created' => 'থিম কাস্টমাইজেশন সফলভাবে তৈরি করা হয়েছে।',
                'updated' => 'থিম কাস্টমাইজেশন সফলভাবে আপডেট করা হয়েছে।',
                'deleted' => 'থিম কাস্টমাইজেশন সফলভাবে মুছে ফেলা হয়েছে।',
                'delete-failed' => 'থিম কাস্টমাইজেশন মুছে ফেলা যায়নি।',
                'no-permission' => 'থিম কাস্টমাইজেশন পরিচালনা করার অনুমতি আপনার নেই।',
                'mass-delete-success' => 'থিম কাস্টমাইজেশনগুলো সফলভাবে মুছে ফেলা হয়েছে।',
                'mass-delete-indices-required' => 'indices ফিল্ডটি প্রয়োজন এবং এটি অবশ্যই একটি খালি নয় এমন অ্যারে হতে হবে।',
                'mass-update-success' => 'থিম কাস্টমাইজেশনগুলো সফলভাবে আপডেট করা হয়েছে।',
                'mass-update-indices-required' => 'indices ফিল্ডটি প্রয়োজন এবং এটি অবশ্যই একটি খালি নয় এমন অ্যারে হতে হবে।',
                'mass-update-value-invalid' => 'value ফিল্ডটি অবশ্যই 0 বা 1 হতে হবে।',
            ],

            'inventory-source' => [
                'not-found' => 'ইনভেন্টরি সোর্স পাওয়া যায়নি।',
                'created' => 'ইনভেন্টরি সোর্স সফলভাবে তৈরি করা হয়েছে।',
                'updated' => 'ইনভেন্টরি সোর্স সফলভাবে আপডেট করা হয়েছে।',
                'deleted' => 'ইনভেন্টরি সোর্স সফলভাবে মুছে ফেলা হয়েছে।',
                'code-unique' => 'এই কোডসহ একটি ইনভেন্টরি সোর্স ইতিমধ্যে বিদ্যমান।',
                'last-delete-error' => 'অন্তত একটি ইনভেন্টরি সোর্স প্রয়োজন — শেষ অবশিষ্ট সোর্সটি মুছে ফেলা যাবে না।',
                'in-use' => 'এই ইনভেন্টরি সোর্সটি পণ্যের ইনভেন্টরিতে উল্লেখিত আছে এবং এটি মুছে ফেলা যাবে না।',
                'delete-failed' => 'ইনভেন্টরি সোর্স মুছে ফেলা যায়নি।',
                'no-permission' => 'ইনভেন্টরি সোর্স পরিচালনা করার অনুমতি আপনার নেই।',
                'mass-delete-success' => 'ইনভেন্টরি সোর্সগুলো সফলভাবে মুছে ফেলা হয়েছে।',
                'mass-delete-indices-required' => 'indices ফিল্ডটি প্রয়োজন এবং এটি অবশ্যই একটি খালি নয় এমন অ্যারে হতে হবে।',
            ],

            'exchange-rate' => [
                'not-found' => 'বিনিময় হার পাওয়া যায়নি।',
                'created' => 'বিনিময় হার সফলভাবে তৈরি করা হয়েছে।',
                'updated' => 'বিনিময় হার সফলভাবে আপডেট করা হয়েছে।',
                'deleted' => 'বিনিময় হার সফলভাবে মুছে ফেলা হয়েছে।',
                'duplicate-pair' => 'এই লক্ষ্য মুদ্রার জন্য একটি বিনিময় হার ইতিমধ্যে বিদ্যমান। বিদ্যমান সারিটি আপডেট করুন বা প্রথমে এটি মুছুন।',
                'delete-failed' => 'বিনিময় হার মুছে ফেলা যায়নি।',
                'no-permission' => 'বিনিময় হার পরিচালনা করার অনুমতি আপনার নেই।',
                'mass-delete-success' => 'বিনিময় হারগুলো সফলভাবে মুছে ফেলা হয়েছে।',
                'mass-delete-indices-required' => 'indices ফিল্ডটি প্রয়োজন এবং এটি অবশ্যই একটি খালি নয় এমন অ্যারে হতে হবে।',
                'update-rates-success' => 'বিনিময় হারগুলো সফলভাবে আপডেট করা হয়েছে।',
                'update-rates-failed' => 'বাহ্যিক প্রদানকারী থেকে বিনিময় হার আপডেট করা যায়নি।',
                'provider-not-configured' => 'কোনো বাহ্যিক বিনিময় হার প্রদানকারী কনফিগার করা নেই।',
            ],

            'tax-rate' => [
                'not-found' => 'ট্যাক্স রেট পাওয়া যায়নি।',
                'created' => 'ট্যাক্স রেট সফলভাবে তৈরি করা হয়েছে।',
                'updated' => 'ট্যাক্স রেট সফলভাবে আপডেট করা হয়েছে।',
                'deleted' => 'ট্যাক্স রেট সফলভাবে মুছে ফেলা হয়েছে।',
                'delete-failed' => 'ট্যাক্স রেট মুছে ফেলা যায়নি।',
                'no-permission' => 'ট্যাক্স রেট পরিচালনা করার অনুমতি আপনার নেই।',
                'zip-code-required' => 'is_zip false হলে zip_code ফিল্ডটি প্রয়োজন।',
                'zip-range-required' => 'is_zip true হলে zip_from এবং zip_to উভয়ই প্রয়োজন।',
            ],

            'tax-category' => [
                'not-found' => 'ট্যাক্স ক্যাটাগরি পাওয়া যায়নি।',
                'created' => 'ট্যাক্স ক্যাটাগরি সফলভাবে তৈরি করা হয়েছে।',
                'updated' => 'ট্যাক্স ক্যাটাগরি সফলভাবে আপডেট করা হয়েছে।',
                'deleted' => 'ট্যাক্স ক্যাটাগরি সফলভাবে মুছে ফেলা হয়েছে।',
                'code-unique' => 'এই কোডটি ইতিমধ্যে নেওয়া হয়েছে।',
                'cannot-delete-in-use' => 'এই ট্যাক্স ক্যাটাগরিতে এখনও ট্যাক্স রেট সংযুক্ত আছে এবং এটি মুছে ফেলা যাবে না।',
                'delete-failed' => 'ট্যাক্স ক্যাটাগরি মুছে ফেলা যায়নি।',
                'no-permission' => 'ট্যাক্স ক্যাটাগরি পরিচালনা করার অনুমতি আপনার নেই।',
            ],

            'role' => [
                'not-found' => 'রোল পাওয়া যায়নি।',
                'created' => 'রোল সফলভাবে তৈরি করা হয়েছে।',
                'updated' => 'রোল সফলভাবে আপডেট করা হয়েছে।',
                'deleted' => 'রোল সফলভাবে মুছে ফেলা হয়েছে।',
                'permissions-required' => 'permission_type custom হলে permissions ফিল্ডটি প্রয়োজন।',
                'cannot-delete-in-use' => 'এই রোলটি এক বা একাধিক অ্যাডমিনকে বরাদ্দ করা আছে এবং এটি মুছে ফেলা যাবে না।',
                'cannot-delete-last-role' => 'অন্তত একটি রোল প্রয়োজন — শেষ অবশিষ্ট রোলটি মুছে ফেলা যাবে না।',
                'cannot-change-last-all-access' => 'একমাত্র পূর্ণ-অ্যাক্সেস রোলটিকে কাস্টম রোলে পরিবর্তন করা যাবে না।',
                'delete-failed' => 'রোল মুছে ফেলা যায়নি।',
                'no-permission' => 'রোল পরিচালনা করার অনুমতি আপনার নেই।',
            ],

            'currency' => [
                // Read
                'not-found' => 'মুদ্রা পাওয়া যায়নি।',

                // Create / Update / Delete
                'created' => 'মুদ্রা সফলভাবে তৈরি করা হয়েছে।',
                'updated' => 'মুদ্রা সফলভাবে আপডেট করা হয়েছে।',
                'deleted' => 'মুদ্রা সফলভাবে মুছে ফেলা হয়েছে।',
                'code-required' => 'code ফিল্ডটি প্রয়োজন।',
                'code-size' => 'কোডটি অবশ্যই ঠিক 3টি বর্ণমালার অক্ষরের হতে হবে।',
                'code-unique' => 'এই কোডটি ইতিমধ্যে নেওয়া হয়েছে।',
                'name-required' => 'name ফিল্ডটি প্রয়োজন।',
                'cannot-delete-last' => 'অন্তত একটি মুদ্রা প্রয়োজন — শেষ অবশিষ্ট মুদ্রাটি মুছে ফেলা যাবে না।',
                'cannot-delete-channel-base' => 'এই মুদ্রাটি এক বা একাধিক চ্যানেলের বেস মুদ্রা এবং এটি মুছে ফেলা যাবে না।',
                'delete-failed' => 'মুদ্রা মুছে ফেলা যায়নি।',
                'no-permission' => 'মুদ্রা পরিচালনা করার অনুমতি আপনার নেই।',

                'mass-delete-success' => 'মুদ্রাগুলো সফলভাবে মুছে ফেলা হয়েছে।',
                'mass-delete-indices-required' => 'indices ফিল্ডটি প্রয়োজন এবং এটি অবশ্যই একটি খালি নয় এমন অ্যারে হতে হবে।',
            ],

            'channel' => [
                'not-found' => 'চ্যানেল পাওয়া যায়নি।',

                'created' => 'চ্যানেল সফলভাবে তৈরি করা হয়েছে।',
                'updated' => 'চ্যানেল সফলভাবে আপডেট করা হয়েছে।',
                'deleted' => 'চ্যানেল সফলভাবে মুছে ফেলা হয়েছে।',
                'code-unique' => 'এই কোডটি ইতিমধ্যে নেওয়া হয়েছে।',
                'hostname-unique' => 'এই হোস্টনেমটি ইতিমধ্যে নেওয়া হয়েছে।',
                'default-locale-mismatch' => 'default_locale_id অবশ্যই লোকেল তালিকায় থাকতে হবে।',
                'base-currency-mismatch' => 'base_currency_id অবশ্যই মুদ্রার তালিকায় থাকতে হবে।',
                'unknown-locale' => 'এক বা একাধিক লোকেল id বিদ্যমান নেই।',
                'unknown-currency' => 'এক বা একাধিক মুদ্রা id বিদ্যমান নেই।',
                'unknown-inventory-source' => 'এক বা একাধিক inventory_source id বিদ্যমান নেই।',
                'unknown-root-category' => 'root_category_id বিদ্যমান নেই।',
                'cannot-delete-last' => 'অন্তত একটি চ্যানেল প্রয়োজন — শেষ অবশিষ্ট চ্যানেলটি মুছে ফেলা যাবে না।',
                'cannot-delete-default' => 'ডিফল্ট অ্যাপ্লিকেশন চ্যানেল মুছে ফেলা যাবে না।',
                'delete-failed' => 'চ্যানেল মুছে ফেলা যায়নি।',
                'no-permission' => 'চ্যানেল পরিচালনা করার অনুমতি আপনার নেই।',
            ],

            'user' => [
                'not-found' => 'অ্যাডমিন ব্যবহারকারী পাওয়া যায়নি।',
                'created' => 'অ্যাডমিন ব্যবহারকারী সফলভাবে তৈরি করা হয়েছে।',
                'updated' => 'অ্যাডমিন ব্যবহারকারী সফলভাবে আপডেট করা হয়েছে।',
                'deleted' => 'অ্যাডমিন ব্যবহারকারী সফলভাবে মুছে ফেলা হয়েছে।',
                'name-required' => 'name ফিল্ডটি প্রয়োজন।',
                'email-required' => 'email ফিল্ডটি প্রয়োজন।',
                'email-invalid' => 'email অবশ্যই একটি বৈধ ইমেল ঠিকানা হতে হবে।',
                'email-unique' => 'এই email ইতিমধ্যে নেওয়া হয়েছে।',
                'password-required' => 'password ফিল্ডটি প্রয়োজন।',
                'password-min' => 'পাসওয়ার্ড অবশ্যই অন্তত 6 অক্ষরের হতে হবে।',
                'role-required' => 'role_id ফিল্ডটি প্রয়োজন।',
                'role-not-found' => 'নির্বাচিত রোল বিদ্যমান নেই।',
                'status-invalid' => 'অবস্থা অবশ্যই 0 বা 1 হতে হবে।',
                'cannot-delete-self' => 'আপনি নিজের অ্যাডমিন অ্যাকাউন্ট মুছতে পারবেন না।',
                'cannot-delete-last-admin' => 'অন্তত একজন অ্যাডমিন ব্যবহারকারী প্রয়োজন — শেষ অবশিষ্ট অ্যাডমিনকে মুছে ফেলা যাবে না।',
                'cannot-change' => 'শেষ পূর্ণ-অ্যাক্সেস অ্যাডমিনের :name পরিবর্তন করা যাবে না।',
                'delete-failed' => 'অ্যাডমিন ব্যবহারকারী মুছে ফেলা যায়নি।',
                'no-permission' => 'অ্যাডমিন ব্যবহারকারীদের পরিচালনা করার অনুমতি আপনার নেই।',
                'self-delete-password-required' => 'নিজের অ্যাকাউন্ট মুছতে password ফিল্ডটি প্রয়োজন।',
                'self-delete-incorrect-password' => 'পাসওয়ার্ডটি ভুল।',
                'self-deleted' => 'আপনার অ্যাডমিন অ্যাকাউন্ট মুছে ফেলা হয়েছে।',
            ],

            'data-transfer' => [
                'import' => [
                    'not-found' => 'ইমপোর্ট পাওয়া যায়নি।',
                    'deleted' => 'ইমপোর্ট সফলভাবে মুছে ফেলা হয়েছে।',
                    'cancelled' => 'ইমপোর্ট সফলভাবে বাতিল করা হয়েছে।',
                    'cannot-cancel' => 'শুধুমাত্র মুলতুবি বা প্রক্রিয়াধীন ইমপোর্ট বাতিল করা যায়। বর্তমান অবস্থা: :state।',
                    'no-permission' => 'ডেটা-ট্রান্সফার ইমপোর্ট পরিচালনা করার অনুমতি আপনার নেই।',
                    'delete-failed' => 'ইমপোর্ট মুছে ফেলা যায়নি।',
                    'created' => 'ইমপোর্ট সফলভাবে তৈরি করা হয়েছে।',
                    'updated' => 'ইমপোর্ট সফলভাবে আপডেট করা হয়েছে।',
                    'type-invalid' => 'নির্বাচিত ধরনটি অবৈধ।',
                    'action-invalid' => 'অ্যাকশন অবশ্যই এগুলোর একটি হতে হবে: append, delete।',
                    'validation-strategy-invalid' => 'যাচাইকরণ কৌশল অবশ্যই এগুলোর একটি হতে হবে: stop-on-errors, skip-errors।',
                    'allowed-errors-invalid' => 'অনুমোদিত ত্রুটির সংখ্যা অবশ্যই 0 বা তার বেশি একটি পূর্ণসংখ্যা হতে হবে।',
                    'field-separator-required' => 'ফিল্ড বিভাজক প্রয়োজন।',
                    'file-required' => 'একটি ফাইল প্রয়োজন।',
                    'file-invalid-type' => 'ফাইলটি অবশ্যই এগুলোর একটি হতে হবে: csv, xls, xlsx, xml।',
                    'nothing-to-import' => 'ইমপোর্ট করার মতো কিছু নেই।',
                    'not-valid' => 'ইমপোর্টটি বৈধ নয়।',
                    'setup-queue-error' => 'কিউ সংযোগ sync-এ সেট করা আছে; ব্যাকগ্রাউন্ডে ইমপোর্ট প্রক্রিয়া করতে একটি প্রকৃত কিউ কনফিগার করুন।',
                    'file-not-found' => 'ডাউনলোডের জন্য কোনো ফাইল উপলব্ধ নেই।',
                    'error-report-not-found' => 'ডাউনলোডের জন্য কোনো ত্রুটি রিপোর্ট উপলব্ধ নেই।',
                    'sample-not-found' => 'অনুরোধ করা ধরন ও ফর্ম্যাটের জন্য কোনো নমুনা ফাইল উপলব্ধ নেই।',
                    'sample-format-invalid' => 'নমুনা ফর্ম্যাট অবশ্যই এগুলোর একটি হতে হবে: csv, xls, xlsx, xml।',
                    'images-archive-too-large' => 'ছবির আর্কাইভে সর্বোচ্চ :entries টি ফাইল থাকতে পারে এবং আনপ্যাক করলে সর্বোচ্চ :size MB হতে পারে।',
                ],
            ],
        ],

        'cms' => [
            'page' => [
                'not-found' => 'CMS পেজ পাওয়া যায়নি।',

                'created' => 'CMS পেজ সফলভাবে তৈরি করা হয়েছে।',
                'url-key-required' => 'url_key ফিল্ডটি প্রয়োজন।',
                'url-key-unique' => 'এই url_key ইতিমধ্যে নেওয়া হয়েছে।',
                'url-key-invalid' => 'url_key ফর্ম্যাট অবৈধ। শুধুমাত্র ছোট হাতের অক্ষর, সংখ্যা এবং হাইফেন ব্যবহার করুন।',
                'page-title-required' => 'page_title ফিল্ডটি প্রয়োজন।',
                'html-content-required' => 'html_content ফিল্ডটি প্রয়োজন।',
                'channels-required' => 'অন্তত একটি চ্যানেল নির্বাচন করতে হবে।',
                'channels-invalid' => 'এক বা একাধিক চ্যানেল অবৈধ।',

                'updated' => 'CMS পেজ সফলভাবে আপডেট করা হয়েছে।',

                'deleted' => 'CMS পেজ সফলভাবে মুছে ফেলা হয়েছে।',
                'delete-failed' => 'CMS পেজ মুছে ফেলা যায়নি।',

                'mass-deleted' => 'CMS পেজগুলো সফলভাবে মুছে ফেলা হয়েছে।',
                'indices-required' => 'indices ফিল্ডটি প্রয়োজন এবং এটি অবশ্যই একটি খালি নয় এমন অ্যারে হতে হবে।',
                'indices-invalid' => 'indices ফিল্ডটি অবশ্যই CMS পেজ আইডির একটি অ্যারে হতে হবে।',

                'no-permission' => 'CMS পেজ পরিচালনা করার অনুমতি আপনার নেই।',
            ],
        ],

        'marketing' => [
            'catalog-rule' => [
                'not-found' => 'ক্যাটালগ নিয়ম পাওয়া যায়নি।',
                'created' => 'ক্যাটালগ নিয়ম সফলভাবে তৈরি করা হয়েছে।',
                'updated' => 'ক্যাটালগ নিয়ম সফলভাবে আপডেট করা হয়েছে।',
                'deleted' => 'ক্যাটালগ নিয়ম সফলভাবে মুছে ফেলা হয়েছে।',
                'delete-failed' => 'ক্যাটালগ নিয়ম মুছে ফেলা যায়নি।',
                'mass-delete-indices-required' => 'একসাথে মোছার জন্য কোনো ক্যাটালগ নিয়ম আইডি দেওয়া হয়নি।',
                'mass-delete-success' => 'নির্বাচিত ক্যাটালগ নিয়মগুলো সফলভাবে মুছে ফেলা হয়েছে।',
                'no-permission' => 'ক্যাটালগ নিয়ম পরিচালনা করার অনুমতি আপনার নেই।',
            ],

            'campaign' => [
                'not-found' => 'ক্যাম্পেইন পাওয়া যায়নি।',
                'created' => 'ক্যাম্পেইন সফলভাবে তৈরি করা হয়েছে।',
                'updated' => 'ক্যাম্পেইন সফলভাবে আপডেট করা হয়েছে।',
                'deleted' => 'ক্যাম্পেইন সফলভাবে মুছে ফেলা হয়েছে।',
                'delete-failed' => 'ক্যাম্পেইন মুছে ফেলা যায়নি।',
                'no-permission' => 'ক্যাম্পেইন পরিচালনা করার অনুমতি আপনার নেই।',

                'send' => [
                    'id-required' => 'ক্যাম্পেইন id প্রয়োজন।',
                    'inactive' => 'নিষ্ক্রিয় ক্যাম্পেইন পাঠানো যাবে না। প্রথমে এটি সক্রিয় করুন।',
                    'queued' => ':count জন প্রাপকের জন্য ক্যাম্পেইন কিউতে রাখা হয়েছে।',
                ],
            ],

            'sitemap' => [
                'not-found' => 'সাইটম্যাপ পাওয়া যায়নি।',
                'created' => 'সাইটম্যাপ সফলভাবে তৈরি করা হয়েছে।',
                'updated' => 'সাইটম্যাপ সফলভাবে আপডেট করা হয়েছে।',
                'deleted' => 'সাইটম্যাপ সফলভাবে মুছে ফেলা হয়েছে।',
                'delete-failed' => 'সাইটম্যাপ মুছে ফেলা যায়নি।',
                'no-permission' => 'সাইটম্যাপ পরিচালনা করার অনুমতি আপনার নেই।',

                'generate' => [
                    'id-required' => 'সাইটম্যাপ id প্রয়োজন।',
                    'no-channels' => 'এই সাইটম্যাপ কোনো চ্যানেল কভার করে না, তাই তৈরি করার মতো কিছু নেই। প্রথমে এতে অন্তত একটি চ্যানেল বরাদ্দ করুন।',
                    'success' => 'সাইটম্যাপ সফলভাবে পুনরায় তৈরি করা হয়েছে।',
                    'failed' => 'সাইটম্যাপ তৈরি ব্যর্থ হয়েছে: :message',
                ],
            ],

            'cart-rule' => [
                'not-found' => 'কার্ট নিয়ম পাওয়া যায়নি।',
                'id-required' => 'একটি কার্ট নিয়ম id প্রয়োজন।',
                'copy-of' => ':value-এর কপি',
                'copied' => 'কার্ট নিয়ম সফলভাবে কপি করা হয়েছে।',
                'created' => 'কার্ট নিয়ম সফলভাবে তৈরি করা হয়েছে।',
                'updated' => 'কার্ট নিয়ম সফলভাবে আপডেট করা হয়েছে।',
                'deleted' => 'কার্ট নিয়ম সফলভাবে মুছে ফেলা হয়েছে।',
                'delete-failed' => 'কার্ট নিয়ম মুছে ফেলা যায়নি।',
                'no-permission' => 'কার্ট নিয়ম পরিচালনা করার অনুমতি আপনার নেই।',
                'mass-delete-success' => 'কার্ট নিয়মগুলো সফলভাবে মুছে ফেলা হয়েছে।',
                'mass-delete-indices-required' => 'indices ফিল্ডটি প্রয়োজন এবং এটি অবশ্যই একটি খালি নয় এমন অ্যারে হতে হবে।',
                'action-type-invalid' => 'action_type অবশ্যই এগুলোর একটি হতে হবে: by_percent, by_fixed, cart_fixed, buy_x_get_y।',
                'coupon-code-required' => 'coupon_type=1 এবং use_auto_generation=0 হলে একটি coupon_code প্রয়োজন।',
                'coupon-code-unique' => 'এই কুপন কোডটি ইতিমধ্যে ব্যবহৃত হচ্ছে।',
            ],

            'template' => [
                'campaign-associated' => 'এই ইমেল টেমপ্লেটটি একটি ক্যাম্পেইনে ব্যবহৃত হচ্ছে, তাই এটি মুছে ফেলা যাবে না। প্রথমে ক্যাম্পেইন থেকে এটি সরান।',
                'not-found' => 'ইমেল টেমপ্লেট পাওয়া যায়নি।',
                'created' => 'ইমেল টেমপ্লেট সফলভাবে তৈরি করা হয়েছে।',
                'updated' => 'ইমেল টেমপ্লেট সফলভাবে আপডেট করা হয়েছে।',
                'deleted' => 'ইমেল টেমপ্লেট সফলভাবে মুছে ফেলা হয়েছে।',
                'delete-failed' => 'ইমেল টেমপ্লেট মুছে ফেলা যায়নি।',
                'status-invalid' => 'অবস্থা অবশ্যই এগুলোর একটি হতে হবে: active, inactive, draft।',
                'no-permission' => 'ইমেল টেমপ্লেট পরিচালনা করার অনুমতি আপনার নেই।',
            ],

            'event' => [
                'campaign-associated' => 'এই ইভেন্টটি একটি ক্যাম্পেইনে ব্যবহৃত হচ্ছে, তাই এটি মুছে ফেলা যাবে না। প্রথমে ক্যাম্পেইন থেকে এটি সরান।',
                'not-found' => 'মার্কেটিং ইভেন্ট পাওয়া যায়নি।',
                'created' => 'মার্কেটিং ইভেন্ট সফলভাবে তৈরি করা হয়েছে।',
                'updated' => 'মার্কেটিং ইভেন্ট সফলভাবে আপডেট করা হয়েছে।',
                'deleted' => 'মার্কেটিং ইভেন্ট সফলভাবে মুছে ফেলা হয়েছে।',
                'delete-failed' => 'মার্কেটিং ইভেন্ট মুছে ফেলা যায়নি।',
                'no-permission' => 'মার্কেটিং ইভেন্ট পরিচালনা করার অনুমতি আপনার নেই।',
            ],

            'search-synonym' => [
                'not-found' => 'সার্চ সমার্থক শব্দ পাওয়া যায়নি।',
                'created' => 'সার্চ সমার্থক শব্দ সফলভাবে তৈরি করা হয়েছে।',
                'updated' => 'সার্চ সমার্থক শব্দ সফলভাবে আপডেট করা হয়েছে।',
                'deleted' => 'সার্চ সমার্থক শব্দ সফলভাবে মুছে ফেলা হয়েছে।',
                'mass-deleted' => 'সার্চ সমার্থক শব্দগুলো সফলভাবে মুছে ফেলা হয়েছে।',
                'delete-failed' => 'সার্চ সমার্থক শব্দ মুছে ফেলা যায়নি।',
                'indices-required' => 'indices ফিল্ডটি প্রয়োজন এবং এটি অবশ্যই সার্চ সমার্থক শব্দ আইডির একটি খালি নয় এমন অ্যারে হতে হবে।',
                'no-permission' => 'সার্চ সমার্থক শব্দ পরিচালনা করার অনুমতি আপনার নেই।',
            ],

            'url-rewrite' => [
                'not-found' => 'URL রিরাইট পাওয়া যায়নি।',
                'created' => 'URL রিরাইট সফলভাবে তৈরি করা হয়েছে।',
                'updated' => 'URL রিরাইট সফলভাবে আপডেট করা হয়েছে।',
                'deleted' => 'URL রিরাইট সফলভাবে মুছে ফেলা হয়েছে।',
                'delete-failed' => 'URL রিরাইট মুছে ফেলা যায়নি।',
                'mass-deleted' => 'URL রিরাইটগুলো সফলভাবে মুছে ফেলা হয়েছে।',
                'indices-required' => 'অন্তত একটি URL রিরাইট id প্রয়োজন।',
                'no-permission' => 'URL রিরাইট পরিচালনা করার অনুমতি আপনার নেই।',
            ],

            'cart-rule-coupon' => [
                'cart-rule-not-found' => 'কার্ট নিয়ম পাওয়া যায়নি।',
                'not-found' => 'কুপন পাওয়া যায়নি।',
                'code-taken' => 'এই কোডসহ একটি কুপন ইতিমধ্যে বিদ্যমান।',
                'format-invalid' => 'ফর্ম্যাট অবশ্যই এগুলোর একটি হতে হবে: alphabetic, alphanumeric, numeric।',
                'indices-required' => 'indices ফিল্ডটি প্রয়োজন এবং এটি অবশ্যই কুপন আইডির একটি খালি নয় এমন অ্যারে হতে হবে।',
                'created' => 'কুপন সফলভাবে তৈরি করা হয়েছে।',
                'generated' => 'সফলভাবে :count টি কুপন তৈরি করা হয়েছে।',
                'deleted' => 'কুপন সফলভাবে মুছে ফেলা হয়েছে।',
                'mass-deleted' => 'সফলভাবে :count টি কুপন মুছে ফেলা হয়েছে।',
                'no-permission' => 'কার্ট নিয়মের কুপন পরিচালনা করার অনুমতি আপনার নেই।',
            ],

            'subscriber' => [
                'not-found' => 'নিউজলেটার সাবস্ক্রাইবার পাওয়া যায়নি।',
                'updated' => 'সাবস্ক্রিপশন সফলভাবে আপডেট করা হয়েছে।',
                'deleted' => 'সাবস্ক্রিপশন সফলভাবে মুছে ফেলা হয়েছে।',
                'is-subscribed-required' => 'is_subscribed ফিল্ডটি প্রয়োজন।',
                'no-permission' => 'নিউজলেটার সাবস্ক্রাইবার পরিচালনা করার অনুমতি আপনার নেই।',
            ],

            'search-term' => [
                'not-found' => 'সার্চ টার্ম পাওয়া যায়নি।',
                'updated' => 'সার্চ টার্ম সফলভাবে আপডেট করা হয়েছে।',
                'deleted' => 'সার্চ টার্ম সফলভাবে মুছে ফেলা হয়েছে।',
                'mass-deleted' => 'সার্চ টার্মগুলো সফলভাবে মুছে ফেলা হয়েছে।',
                'term-required' => 'term ফিল্ডটি প্রয়োজন।',
                'redirect-url-invalid' => 'redirect_url অবশ্যই একটি বৈধ URL হতে হবে।',
                'indices-required' => 'indices-এ অন্তত একটি আইডি দিতে হবে।',
                'no-permission' => 'সার্চ টার্ম পরিচালনা করার অনুমতি আপনার নেই।',
            ],
        ],
        'auth' => [
            'configuration' => [
                'title' => 'অ্যাডমিন প্রমাণীকরণ',
                'info' => 'অ্যাডমিন API লগইন ও API টোকেনের জন্য টোকেনের মেয়াদ, রেট লিমিট ও নিরাপত্তা নিয়ম।',

                'tokens' => [
                    'title' => 'টোকেনের মেয়াদ',
                    'info' => 'অ্যাক্সেস টোকেন, রিফ্রেশ টোকেন ও সেশন কতক্ষণ বৈধ থাকবে। পরিবর্তন পরে ইস্যু হওয়া টোকেনে প্রযোজ্য।',
                    'access-token-ttl' => 'অ্যাক্সেস টোকেনের মেয়াদ (মিনিট)',
                    'access-token-ttl-info' => '৫ থেকে ৬০ মিনিট। ডিফল্ট ১৫।',
                    'refresh-token-ttl' => 'রিফ্রেশ টোকেনের মেয়াদ (দিন)',
                    'refresh-token-ttl-info' => '১ থেকে ৯০ দিন। ডিফল্ট ৩০।',
                    'session-lifetime' => 'সেশনের সর্বোচ্চ মেয়াদ (দিন)',
                    'session-lifetime-info' => '১ থেকে ৩৬৫ দিন। ডিফল্ট ৯০। এরপর সেশন যতবারই রিফ্রেশ হোক, অ্যাডমিনকে আবার লগইন করতে হবে।',
                ],

                'rate-limits' => [
                    'title' => 'রেট লিমিট',
                    'info' => 'প্রতি মিনিটে সর্বোচ্চ অ্যাডমিন API অনুরোধ।',
                    'session-requests-per-minute' => 'প্রতি লগইন করা অ্যাডমিনের জন্য প্রতি মিনিটে অনুরোধ',
                    'api-token-requests-per-minute' => 'প্রতি API টোকেনের জন্য প্রতি মিনিটে অনুরোধ',
                ],

                'security' => [
                    'title' => 'নিরাপত্তা',
                    'info' => 'বাতিলের নিয়ম, API টোকেনের মেয়াদ ও ডোমেইন বাইন্ডিং।',
                    'revoke-sessions-on-password-change' => 'পাসওয়ার্ড পরিবর্তনে সেশন বাতিল করুন',
                    'revoke-api-tokens-on-password-change' => 'পাসওয়ার্ড পরিবর্তনে API টোকেন বাতিল করুন',
                    'allow-non-expiring-api-tokens' => 'মেয়াদহীন API টোকেন অনুমোদন করুন',
                    'domain-binding-mode' => 'ডোমেইন বাইন্ডিং মোড',
                    'domain-binding-mode-info' => 'DNS যাচাই করে অনুরোধটি টোকেনের ডোমেইন যে IP ঠিকানায় রিজলভ হয় সেখান থেকে এসেছে কিনা। Origin, Origin বা Referer হেডার যাচাই করে এবং শুধু ব্রাউজারের জন্য উপযুক্ত।',
                    'domain-binding-dns' => 'DNS (ক্লায়েন্টের IP ঠিকানা)',
                    'domain-binding-origin' => 'Origin হেডার',
                ],
            ],

            'errors' => [
                'unauthenticated' => 'আপনি প্রমাণীকৃত নন।',
                'access-token-expired' => 'অ্যাক্সেস টোকেনের মেয়াদ শেষ হয়েছে। চালিয়ে যেতে এটি রিফ্রেশ করুন।',
                'api-token-expired' => 'API টোকেনের মেয়াদ শেষ হয়েছে।',
                'refresh-token-invalid' => 'রিফ্রেশ টোকেনটি অবৈধ বা মেয়াদোত্তীর্ণ। অনুগ্রহ করে আবার লগইন করুন।',
                'api-token-revoked' => 'API টোকেনটি প্রত্যাহার করা হয়েছে।',
                'api-token-domain-mismatch' => 'এই ডোমেইন থেকে API টোকেনটি ব্যবহার করা যাবে না।',
                'access-token-required' => 'এই কাজের জন্য একটি লগইন সেশনের অ্যাক্সেস টোকেন প্রয়োজন; API টোকেন এটি ব্যবহার করতে পারে না।',
                'two-factor-required' => 'লগইন সম্পূর্ণ করতে আপনার অথেনটিকেটর অ্যাপের ৬-অঙ্কের কোড, অথবা আপনার একটি ব্যাকআপ কোড লিখুন।',
                'too-many-login-attempts' => 'অনেকবার লগইনের চেষ্টা করা হয়েছে। অনুগ্রহ করে :seconds সেকেন্ড পরে আবার চেষ্টা করুন।',
                'two-factor-challenge-invalid' => 'টু-ফ্যাক্টর চ্যালেঞ্জটি অবৈধ বা মেয়াদোত্তীর্ণ। অনুগ্রহ করে আবার লগইন করুন।',
                'two-factor-attempts-exceeded' => 'অনেকবার ভুল কোড দেওয়া হয়েছে। অনুগ্রহ করে আবার লগইন করুন।',
                'password-reset-token-invalid' => 'এই পাসওয়ার্ড রিসেট টোকেনটি অবৈধ বা মেয়াদোত্তীর্ণ।',
                'too-many-password-requests' => 'অনেক বেশি পাসওয়ার্ড অনুরোধ। অনুগ্রহ করে :seconds সেকেন্ড পরে আবার চেষ্টা করুন।',
                'rate-limit-exceeded' => 'অনেকবার চেষ্টা করা হয়েছে।',
                'no-permission' => 'অ্যাডমিন API সেশন পরিচালনা করার অনুমতি আপনার নেই।',
            ],

            'sessions' => [
                'logged-out' => 'আপনি লগআউট হয়েছেন।',
                'logged-out-all' => 'আপনি সব সেশন থেকে লগআউট হয়েছেন।',
                'not-found' => 'সেশন পাওয়া যায়নি।',
                'revoked' => 'সেশনটি প্রত্যাহার করা হয়েছে।',
                'revoked-for-admin' => 'প্রত্যাহার করা সেশন: :count।',
            ],

            'password' => [
                'reset' => 'আপনার পাসওয়ার্ড রিসেট করা হয়েছে। অনুগ্রহ করে আবার লগইন করুন।',
            ],
        ],

        'api-tokens' => [
            'menu' => [
                'title' => 'API টোকেন',
            ],

            'acl' => [
                'title' => 'API টোকেন',
                'create' => 'তৈরি করুন',
                'delete' => 'বাতিল করুন',
                'manage-others' => 'অন্য অ্যাডমিনদের টোকেন পরিচালনা করুন',
            ],

            'index' => [
                'title' => 'API টোকেন',
                'info' => 'ERP, PIM, গুদাম বা হিসাবরক্ষণ সিস্টেমের মতো সার্ভার ইন্টিগ্রেশন API টোকেন দিয়ে প্রমাণীকরণ করে। প্রতিটি টোকেন একজন অ্যাডমিন হিসেবে তার ভূমিকার অনুমতি নিয়ে কাজ করে এবং কেবল যে ডোমেনের সাথে যুক্ত সেখান থেকেই চলে।',
                'create-btn' => 'API টোকেন তৈরি করুন',
            ],

            'create' => [
                'title' => 'API টোকেন তৈরি করুন',
                'back-btn' => 'ফিরে যান',
                'save-btn' => 'টোকেন তৈরি করুন',
                'general' => 'সাধারণ',
                'expiry' => 'মেয়াদ',
                'admin-info' => 'টোকেনটি এই অ্যাডমিন হিসেবে কাজ করে এবং ঠিক তার ভূমিকার অনুমতিগুলো পায়।',
                'domain-info' => 'যে হোস্ট থেকে ইন্টিগ্রেশন কল করে, যেমন erp.example.com। URL তার হোস্টে ছোট করা হয়। IP ঠিকানা ও ওয়াইল্ডকার্ড গ্রহণযোগ্য নয়, এবং ডোমেন পরে বদলানো যায় না।',
                'expires-on-date' => 'একটি তারিখে মেয়াদ শেষ হবে',
                'never-expires' => 'কখনো মেয়াদ শেষ হবে না',
                'no-expiry-warning' => 'মেয়াদহীন টোকেন বাতিল না করা পর্যন্ত কাজ করে। এটি একটি সিক্রেট স্টোরে রাখুন এবং প্রয়োজন ফুরোলেই বাতিল করুন।',
                'no-expiry-confirm' => 'আমি বুঝি যে এই টোকেনের মেয়াদ কখনো শেষ হবে না।',
                'no-admins' => 'এমন কোনো অ্যাডমিন নেই যার জন্য আপনি API টোকেন তৈরি করতে পারেন।',
            ],

            'view' => [
                'title' => 'API টোকেন',
                'back-btn' => 'ফিরে যান',
                'save-btn' => 'সংরক্ষণ করুন',
                'revoke-btn' => 'বাতিল করুন',
                'details' => 'বিবরণ',
                'usage' => 'ব্যবহার',
                'token-once-title' => 'এখনই আপনার API টোকেন কপি করুন',
                'token-once-info' => 'টোকেনটি কেবল এইবারই দেখানো হয়। এটি একটি সিক্রেট স্টোরে রাখুন; হারিয়ে গেলে বাতিল করে নতুন একটি তৈরি করুন।',
                'copy-btn' => 'কপি করুন',
                'copied' => 'টোকেন কপি করা হয়েছে।',
                'usage-info' => 'REST ও GraphQL অনুরোধের Authorization হেডারে এটি Bearer টোকেন হিসেবে পাঠান।',
                'not-yet' => 'এখনো নয়',
                'revoke-confirm' => 'এই API টোকেন বাতিল করবেন? যে ইন্টিগ্রেশন এটি ব্যবহার করে তা সঙ্গে সঙ্গে কাজ বন্ধ করবে।',
            ],

            'fields' => [
                'id' => 'ID',
                'name' => 'নাম',
                'admin' => 'অ্যাডমিন',
                'token' => 'টোকেন',
                'domain' => 'ডোমেন',
                'expiry' => 'মেয়াদ',
                'expires-at' => 'মেয়াদ শেষের তারিখ',
                'no-expiry-confirmation' => 'মেয়াদহীন নিশ্চিতকরণ',
                'status' => 'অবস্থা',
                'created-by' => 'তৈরি করেছেন',
                'created-at' => 'তৈরির তারিখ',
                'last-used-at' => 'শেষ ব্যবহার',
                'last-ip-address' => 'শেষ IP ঠিকানা',
                'last-user-agent' => 'শেষ ইউজার এজেন্ট',
                'last-channel' => 'শেষ চ্যানেল',
                'resolved-ips' => 'রিজলভ করা IP ঠিকানা',
                'resolved-at' => 'রিজলভের তারিখ',
                'domain-mismatches' => 'অন্য ডোমেন থেকে অনুরোধ',
                'last-domain-mismatch-at' => 'অন্য ডোমেন থেকে শেষ অনুরোধ',
                'revoked-at' => 'বাতিলের তারিখ',
                'revoked-by' => 'বাতিল করেছেন',
            ],

            'datagrid' => [
                'never' => 'কখনো না',
                'console' => 'কনসোল কমান্ড',
                'view' => 'দেখুন',
                'revoke' => 'বাতিল করুন',
            ],

            'status' => [
                'active' => 'সক্রিয়',
                'expired' => 'মেয়াদোত্তীর্ণ',
                'revoked' => 'বাতিল',
            ],

            'channels' => [
                'rest' => 'REST',
                'graphql' => 'GraphQL',
            ],

            'messages' => [
                'created' => 'API টোকেন তৈরি হয়েছে।',
                'updated' => 'API টোকেন হালনাগাদ হয়েছে।',
                'revoked' => 'API টোকেন বাতিল হয়েছে।',
            ],

            'errors' => [
                'no-permission' => 'API টোকেন পরিচালনার অনুমতি আপনার নেই।',
                'not-found' => 'API টোকেন পাওয়া যায়নি।',
                'cannot-create-for-admin' => 'আপনি এই অ্যাডমিনের জন্য API টোকেন তৈরি করতে পারবেন না: তার ভূমিকায় এমন অনুমতি আছে যা আপনার নেই, অথবা আপনি অন্য অ্যাডমিনদের টোকেন পরিচালনা করতে পারেন না।',
                'cannot-manage' => 'আপনি এই API টোকেন পরিচালনা করতে পারবেন না।',
                'already-revoked' => 'API টোকেনটি আগেই বাতিল হয়েছে।',
                'invalid-name' => 'নাম আবশ্যক এবং সর্বোচ্চ 100 অক্ষরের হতে পারে।',
                'invalid-domain' => 'erp.example.com-এর মতো একটি ডোমেন নাম লিখুন। IP ঠিকানা ও ওয়াইল্ডকার্ড গ্রহণযোগ্য নয়।',
                'expiry-required' => 'API টোকেনের একটি মেয়াদ শেষের তারিখ থাকতে হবে।',
                'expiry-in-past' => 'মেয়াদ শেষের তারিখ ভবিষ্যতের হতে হবে।',
            ],
        ],

        'api-sessions' => [
            'menu' => [
                'title' => 'API সেশন',
            ],

            'acl' => [
                'title' => 'API সেশন',
                'delete' => 'বাতিল করুন',
            ],

            'index' => [
                'title' => 'API সেশন',
                'info' => 'ওয়েব, মোবাইল, POS বা ডেস্কটপ অ্যাপ থেকে অ্যাডমিন API-তে সাইন ইন করা অ্যাডমিন। একটি সেশন বাতিল করলে সেই ডিভাইস সঙ্গে সঙ্গে সাইন আউট হয়।',
            ],

            'fields' => [
                'id' => 'ID',
                'admin' => 'অ্যাডমিন',
                'device' => 'ডিভাইস',
                'client-type' => 'ক্লায়েন্টের ধরন',
                'ip-address' => 'লগইন IP ঠিকানা',
                'user-agent' => 'লগইন ইউজার এজেন্ট',
                'last-ip-address' => 'শেষ IP ঠিকানা',
                'last-user-agent' => 'শেষ ইউজার এজেন্ট',
                'last-used-at' => 'শেষ ব্যবহার',
                'last-channel' => 'শেষ চ্যানেল',
                'refresh-expires-at' => 'রিফ্রেশের মেয়াদ শেষ',
                'expires-at' => 'মেয়াদ শেষের তারিখ',
                'status' => 'অবস্থা',
                'created-at' => 'লগইনের সময়',
            ],

            'datagrid' => [
                'revoke' => 'বাতিল করুন',
                'revoke-all' => 'এই অ্যাডমিনের সব সেশন বাতিল করুন',
            ],

            'client-types' => [
                'web' => 'ওয়েব',
                'mobile' => 'মোবাইল',
                'pos' => 'POS',
                'desktop' => 'ডেস্কটপ',
                'other' => 'অন্যান্য',
            ],

            'errors' => [
                'not-active' => 'এই সেশন আগেই বাতিল বা মেয়াদোত্তীর্ণ হয়েছে।',
            ],
        ],
    ],

    'integration' => [
        'menu' => [
            'title' => 'ইন্টিগ্রেশন',
            'tokens' => 'টোকেন',
        ],

        'history' => [
            'menu' => [
                'title' => 'ইতিহাস',
            ],

            'acl' => [
                'title' => 'API পরিবর্তনের ইতিহাস',
                'view' => 'দেখুন',
                'delete' => 'ইতিহাস মুছুন',
            ],

            'index' => [
                'title' => 'API পরিবর্তনের ইতিহাস',
                'info' => 'প্রশাসক API-এর মাধ্যমে করা প্রতিটি তৈরি, আপডেট এবং মুছে ফেলা, কে এটি করেছে, কোন টোকেন এবং কী পরিবর্তন হয়েছে।',
                'cleanup-btn' => 'পুরানো লগ মুছুন',
                'cleanup-days' => 'এই অনেক দিনের চেয়ে পুরানো লগ মুছুন',
                'cleanup-confirm' => 'প্রদত্ত দিনের সংখ্যার চেয়ে পুরোনো সমস্ত ইতিহাস মুছবেন? এটি পূর্বাবস্থায় ফেরানো যাবে না।',
            ],

            'view' => [
                'title' => 'পরিবর্তন',
                'back-btn' => 'ব্যাক',
                'admin' => 'অ্যাডমিন',
                'token' => 'টোকেন',
                'action' => 'অ্যাকশন',
                'resource' => 'সম্পদ',
                'method' => 'পদ্ধতি',
                'ip' => 'আইপি ঠিকানা',
                'date' => 'তারিখ',
                'version' => 'সংস্করণ',
                'url' => 'শেষবিন্দু',
                'request-details' => 'বিস্তারিত অনুরোধ করুন',
                'changes' => 'পরিবর্তন',
                'field' => 'মাঠ',
                'old' => 'পুরাতন মান',
                'new' => 'নতুন মান',
                'no-field-changes' => 'এই এন্ট্রির জন্য কোনো ক্ষেত্র-স্তরের পরিবর্তন রেকর্ড করা হয়নি।',
                'same-request' => 'একই অনুরোধ অন্যান্য পরিবর্তন',
                'version-chain' => 'এই রেকর্ডের সংস্করণ ইতিহাস',
            ],

            'datagrid' => [
                'id' => 'আইডি',
                'date' => 'তারিখ',
                'admin' => 'অ্যাডমিন',
                'token' => 'টোকেন',
                'action' => 'অ্যাকশন',
                'operation' => 'অপারেশন',
                'resource' => 'সম্পদ',
                'version' => 'সংস্করণ',
                'method' => 'পদ্ধতি',
                'ip' => 'আইপি',
                'view' => 'দেখুন',
                'delete' => 'মুছে দিন',
            ],

            'events' => [
                'created' => 'তৈরি হয়েছে',
                'updated' => 'আপডেট করা হয়েছে',
                'deleted' => 'মুছে ফেলা হয়েছে',
            ],

            'deleted' => ':count ইতিহাস রেকর্ড(গুলি) মুছে ফেলা হয়েছে৷',
            'cleanup-input-required' => 'পরিষ্কার করার জন্য কয়েক দিন বা একটি তারিখ দিন।',
        ],

        'acl' => [
            'title' => 'ইন্টিগ্রেশন',
            'view' => 'দেখুন',
            'create' => 'ইন্টিগ্রেশন তৈরি করুন',
            'edit' => 'ইন্টিগ্রেশন সম্পাদনা করুন',
            'delete' => 'ইন্টিগ্রেশন টোকেন প্রত্যাহার করুন',
            'generate' => 'ইন্টিগ্রেশন টোকেন জেনারেট করুন',
            'regenerate' => 'ইন্টিগ্রেশন টোকেন পুনরায় তৈরি করুন',
        ],

        'index' => [
            'title' => 'ইন্টিগ্রেশন',
            'create-btn' => 'ইন্টিগ্রেশন তৈরি করুন',
        ],

        'create' => [
            'title' => 'ইন্টিগ্রেশন তৈরি করুন',
            'save-btn' => 'সংরক্ষণ করুন',
            'back-btn' => 'ব্যাক',
        ],

        'edit' => [
            'title' => 'ইন্টিগ্রেশন সম্পাদনা করুন',
            'save-btn' => 'সংরক্ষণ করুন',
            'back-btn' => 'ব্যাক',
            'generate-btn' => 'টোকেন জেনারেট করুন',
            'regenerate-btn' => 'টোকেন পুনরায় তৈরি করুন',
            'revoke-btn' => 'টোকেন প্রত্যাহার করুন',
            'copy-btn' => 'কপি',
            'token-warning' => 'এখন এই টোকেনটি সংরক্ষণ করুন - এটি আর দেখানো হবে না।',
            'token-label' => 'টোকেন',
            'not-generated' => 'এখনো তৈরি হয়নি',
            'masked' => '(সংরক্ষিত — শুধুমাত্র প্রজন্মে একবার দেখানো হয়েছে)',
            'history-banner' => 'এই টোকেন আর সক্রিয় নেই.',
        ],

        'fields' => [
            'name' => 'নাম',
            'description' => 'বর্ণনা',
            'assign-user' => 'ব্যবহারকারীকে বরাদ্দ করুন',
            'permission-type' => 'অনুমতির ধরন',
            'access-control' => 'অ্যাক্সেস কন্ট্রোল',
            'general' => 'সাধারণ',
            'token-settings' => 'টোকেন সেটিংস',
            'valid-till' => 'পর্যন্ত বৈধ',
            'rate-limit-per-minute' => 'হার সীমা (প্রতি মিনিট)',
            'rate-limit-per-day' => 'হারের সীমা (প্রতিদিন)',
            'never-expires' => 'কখনই মেয়াদ শেষ হয় না',
            'expires-on' => 'মেয়াদ শেষ হবে',
            'unlimited' => 'আনলিমিটেড',
            'limit-to' => 'সীমাবদ্ধ',
            'requests-per-minute' => 'অনুরোধ / মিনিট',
            'requests-per-day' => 'অনুরোধ / দিন',
            'select-admin' => 'একজন প্রশাসক নির্বাচন করুন',
            'no-available-admins' => 'কোন প্রশাসক উপলব্ধ নেই — প্রতিটি প্রশাসকের ইতিমধ্যে একটি সক্রিয় টোকেন আছে.',
            'same-as-web-hint' => 'টোকেন নির্ধারিত প্রশাসকের বর্তমান ভূমিকার অনুমতি লাইভ মিরর করবে।',
            'ip-allowlist' => 'আইপি অনুমোদিত তালিকা',
            'ip-any' => 'যেকোনো আইপি (ডিফল্ট)',
            'ip-restricted' => 'নির্দিষ্ট আইপিতে সীমাবদ্ধ',
            'ip-list-hint' => 'প্রতি লাইনে একটি এন্ট্রি। IPv4, IPv6 এবং CIDR সমর্থন করে (যেমন 10.0.0.0/24 বা 2001:db8::/32)। সমস্ত আইপি অনুমতি দিতে ফাঁকা ছেড়ে দিন।',
        ],

        'permission-type' => [
            'all' => 'সব',
            'custom' => 'কাস্টম',
            'same-as-web' => 'ওয়েব অনুমতি হিসাবে একই',
        ],

        'status' => [
            'draft' => 'খসড়া',
            'active' => 'সক্রিয়',
            'revoked' => 'প্রত্যাহার করা হয়েছে',
            'regenerated' => 'পুনরুজ্জীবিত',
        ],

        'datagrid' => [
            'id' => 'আইডি',
            'name' => 'নাম',
            'admin' => 'অ্যাডমিন',
            'token' => 'টোকেন',
            'status' => 'স্ট্যাটাস',
            'permission-type' => 'অনুমতির ধরন',
            'expires-at' => 'পর্যন্ত বৈধ',
            'last-used-at' => 'সর্বশেষ ব্যবহৃত',
            'created-at' => 'এ তৈরি করা হয়েছে',
            'edit' => 'সম্পাদনা করুন',
            'revoke' => 'প্রত্যাহার করুন',
        ],

        'messages' => [
            'draft-created' => 'ইন্টিগ্রেশন তৈরি হয়েছে। এটি ব্যবহার শুরু করতে টোকেন তৈরি করুন।',
            'updated' => 'ইন্টিগ্রেশন সফলভাবে আপডেট হয়েছে।',
            'generated' => 'টোকেন তৈরি হয়েছে। এটি এখন অনুলিপি করুন - এটি আবার দেখানো হবে না।',
            'regenerated' => 'টোকেন পুনরুত্থিত। এখন নতুন টোকেনটি অনুলিপি করুন - এটি আবার দেখানো হবে না।',
            'revoked' => 'টোকেন সফলভাবে প্রত্যাহার করা হয়েছে৷',
            'generate-only-draft' => 'শুধুমাত্র খসড়া ইন্টিগ্রেশন তাদের টোকেন তৈরি করতে পারে।',
            'regenerate-only-active' => 'শুধুমাত্র সক্রিয় টোকেন পুনরায় তৈরি করা যেতে পারে।',
            'cannot-edit-historic' => 'প্রত্যাহার করা বা পুনরুত্পাদিত টোকেন সম্পাদনা করা যাবে না।',
            'already-inactive' => 'এই টোকেন ইতিমধ্যে নিষ্ক্রিয়.',
        ],

        'errors' => [
            'admin-has-token' => 'নির্বাচিত প্রশাসক ইতিমধ্যে একটি সক্রিয় ইন্টিগ্রেশন টোকেন আছে.',
            'only-draft-generate' => 'শুধুমাত্র ড্রাফট টোকেন তৈরি করা যায়।',
        ],

        'validation' => [
            'ip-invalid' => 'প্রতিটি অনুমোদিত IP একটি বৈধ IPv4 বা IPv6 ঠিকানা হতে হবে (CIDR স্বরলিপি সমর্থিত)।',
            'cidr-prefix-invalid' => 'প্রদত্ত IP সংস্করণের জন্য CIDR উপসর্গটি অবৈধ৷',
        ],

        'configuration' => [
            'api' => [
                'title' => 'API',
                'info' => 'Bagisto API এবং এর অ্যাডমিন মডিউলগুলির জন্য সেটিংস৷',
            ],

            'integration' => [
                'title' => 'ইন্টিগ্রেশন',
                'info' => 'অ্যাডমিন API টোকেন ইস্যু করতে ব্যবহৃত API ইন্টিগ্রেশন প্লাগইন পরিচালনা করুন।',
            ],

            'settings' => [
                'title' => 'মডিউল সেটিংস',
                'info' => 'API ইন্টিগ্রেশন প্লাগইন সক্ষম বা নিষ্ক্রিয় করুন৷ অক্ষম করা হলে, এর সাইডবার মেনু লুকানো থাকে এবং এর পৃষ্ঠাগুলি 404 ফেরত দেয়।',
                'enable' => 'API ইন্টিগ্রেশন মডিউল সক্ষম করুন৷',
            ],
        ],

        'emails' => [
            'generated' => [
                'subject' => 'একটি নতুন API টোকেন তৈরি হয়েছে: :name',
                'greeting' => '":name" নামের একটি API ইন্টিগ্রেশন টোকেন এইমাত্র আপনার অ্যাকাউন্টে তৈরি হয়েছে৷',
            ],

            'regenerated' => [
                'subject' => 'আপনার API টোকেন পুনরায় তৈরি করা হয়েছে: :name',
                'greeting' => '":name" নামের API ইন্টিগ্রেশন টোকেনটি এইমাত্র পুনরুত্থিত হয়েছে৷ আগের টোকেনটি কাজ করা বন্ধ করে দিয়েছে — শুধুমাত্র নতুনটি বৈধ।',
            ],

            'revoked' => [
                'subject' => 'আপনার API টোকেন প্রত্যাহার করা হয়েছে: :name',
                'greeting' => '":name" নামের API ইন্টিগ্রেশন টোকেনটি প্রত্যাহার করা হয়েছে৷ এটি ব্যবহার করা যেকোনো ক্লায়েন্ট অ্যাক্সেস হারিয়েছে।',
            ],

            'details' => [
                'name' => 'টোকেন নাম',
                'date' => 'তারিখ',
                'ip' => 'আইপি থেকে',
            ],

            'revoke-hint' => 'আপনি যদি এটি আশা না করে থাকেন তবে নীচের বোতামটি ব্যবহার করে অবিলম্বে টোকেনটি প্রত্যাহার করুন৷',
            'revoke-btn' => 'এই টোকেন প্রত্যাহার করুন',
            'revoke-expiry' => 'এই প্রত্যাহার লিঙ্কটি 7 দিনের জন্য বৈধ। এর পরে, টোকেন পরিচালনা করতে অ্যাডমিন প্যানেলে সাইন ইন করুন।',
            'no-action' => 'কোন কর্মের প্রয়োজন নেই — এই ইমেল শুধুমাত্র একটি নিশ্চিতকরণ.',
        ],

        'revoke-confirmation' => [
            'title' => 'API টোকেন প্রত্যাহার করুন',
            'confirm-title' => 'এই টোকেনটি প্রত্যাহার করবেন?',
            'confirm-message' => 'টোকেন ":name" এটি ব্যবহারকারী প্রতিটি ক্লায়েন্টের জন্য অবিলম্বে কাজ করা বন্ধ করবে।',
            'confirm-button' => 'টোকেন প্রত্যাহার করুন',
            'success-title' => 'টোকেন প্রত্যাহার করা হয়েছে৷',
            'success-message' => 'টোকেন ":name" প্রত্যাহার করা হয়েছে৷ যে কোনো ক্লায়েন্ট এটি ব্যবহার করে অবিলম্বে অ্যাক্সেস হারিয়েছে।',
            'already-inactive-title' => 'টোকেন ইতিমধ্যেই নিষ্ক্রিয়৷',
            'already-inactive-message' => 'টোকেন ":name" ইতিমধ্যেই প্রত্যাহার করা হয়েছে বা পুনরুত্থিত হয়েছে৷ আর কোনো পদক্ষেপের প্রয়োজন নেই।',
        ],

        'confirm' => [
            'generate' => [
                'title' => 'টোকেন জেনারেট করুন',
                'message' => 'এখন টোকেন জেনারেট করবেন? প্লেইনটেক্সট শুধুমাত্র একবার দেখানো হবে — পৃষ্ঠা ছাড়ার আগে এটি অনুলিপি করুন।',
            ],

            'regenerate' => [
                'title' => 'টোকেন পুনরায় তৈরি করুন',
                'message' => 'টোকেন পুনর্জন্ম? পুরানো টোকেন অবিলম্বে কাজ করা বন্ধ করবে এবং নতুন প্লেইনটেক্সট শুধুমাত্র একবার দেখানো হবে।',
            ],

            'revoke' => [
                'title' => 'টোকেন প্রত্যাহার করুন',
                'message' => 'এই টোকেন প্রত্যাহার করবেন? যেকোনো ক্লায়েন্ট এটি ব্যবহার করে অবিলম্বে অ্যাক্সেস হারাবে। এই ক্রিয়াটি পূর্বাবস্থায় ফেরানো যাবে না৷',
            ],
        ],
    ],
];
