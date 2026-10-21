<?php

return [

    'graphql' => [
        'headers' => [
            'unknown-channel' => 'X-Channel ශීර්ෂයේ ඇති ":code" නාලිකාව නොපවතී.',
            'storefront-key-required' => 'X-STOREFRONT-KEY ශීර්ෂය අවශ්‍ය වේ',
            'storefront-key-invalid' => 'අවලංගු storefront යතුර',
            'storefront-key-rate-limited' => 'අනුපාත සීමාව ඉක්මවා ඇත. කරුණාකර තත්පර :seconds කට පසු නැවත උත්සාහ කරන්න',
        ],

        'cart' => [
            'authentication-required' => 'සත්‍යාපන ටෝකනයක් අවශ්‍ය වේ',
            'invalid-token' => 'අවලංගු හෝ කල් ඉකුත් වූ සත්‍යාපන ටෝකනය',
            'unauthorized-access' => 'කරත්තයට අනවසර ප්‍රවේශය',
            'authenticated-only' => 'තම කරත්ත ලබා ගත හැක්කේ සත්‍යාපිත පරිශීලකයින්ට පමණි',
            'merge-requires-auth' => 'අමුත්තන් ඒකාබද්ධ කිරීමට සත්‍යාපනය අවශ්‍ය වේ',
            'unknown-operation' => 'නොදන්නා කරත්ත මෙහෙයුම',

            'cart-not-found' => 'කරත්තය හමු නොවිණි',
            'guest-cart-not-found' => 'අමුත්තාගේ කරත්තය හමු නොවිණි',
            'product-not-found' => 'නිෂ්පාදනය හමු නොවිණි',

            'product-id-quantity-required' => 'නිෂ්පාදන ID සහ ප්‍රමාණය අවශ්‍ය වේ',
            'cart-item-id-quantity-required' => 'කරත්ත අයිතම ID සහ ප්‍රමාණය අවශ්‍ය වේ',

            'file-token-invalid' => 'උඩුගත කළ ගොනු යොමුව අවලංගු හෝ කල් ඉකුත් වී ඇත. ගොනුව නැවත උඩුගත කරන්න.',
            'file-token-forbidden' => 'මෙම උඩුගත කළ ගොනුව ඔබගේ කරත්තයට අයත් නොවේ.',
            'customizable-option-required' => '":label" අභිරුචිකරණය කළ හැකි විකල්පය අවශ්‍ය වේ.',
            'customizable-file-option-invalid' => 'තෝරාගත් විකල්පය මෙම නිෂ්පාදනය සඳහා ගොනු විකල්පයක් නොවේ.',
            'customizable-file-invalid' => 'ගොනුව නොමැත, සහාය නොදක්වන දිගුවක් ඇත, නැතහොත් ඉතා විශාලය.',
            'cart-item-id-required' => 'කරත්ත අයිතම ID අවශ්‍ය වේ',
            'item-ids-required' => 'අයිතම ID අරාව අවශ්‍ය වේ',

            'event-booking-quantity-not-changeable' => 'සිදුවීම් වෙන්කිරීම් නිෂ්පාදනයේ ප්‍රමාණය වෙනස් කළ නොහැක. ප්‍රමාණය තීරණය වන්නේ ටිකට්පත් තේරීම අනුවය.',
            'appointment-booking-quantity-not-changeable' => 'හමුවීම් වෙන්කිරීම් නිෂ්පාදනයේ ප්‍රමාණය වෙනස් කළ නොහැක.',
            'coupon-code-required' => 'කූපන් කේතය අවශ්‍ය වේ',
            'address-data-required' => 'රට, ප්‍රාන්තය සහ තැපැල් කේතය අවශ්‍ය වේ',
            'grouped-qty-required' => 'සමූහගත නිෂ්පාදනයට තෝරාගත් ප්‍රමාණ අවශ්‍ය වේ. groupedQty JSON තන්තුවක් ලෙස යවන්න, උදා. {"101":2,"102":1}.',
            'grouped-qty-must-include-all' => 'සමූහගත නිෂ්පාදනයට සියලුම ආශ්‍රිත නිෂ්පාදන සඳහා ප්‍රමාණ අවශ්‍ය වේ. නොමැති ID: :ids.',
            'grouped-qty-invalid-associated' => 'සමූහගත නිෂ්පාදන ප්‍රමාණවල අවලංගු ආශ්‍රිත නිෂ්පාදන ID අඩංගු වේ: :ids.',
            'grouped-qty-invalid-quantity' => 'ආශ්‍රිත නිෂ්පාදන ID :id සඳහා අවලංගු ප්‍රමාණයක් සපයා ඇත. ප්‍රමාණය සෘණ නොවන පූර්ණ සංඛ්‍යාවක් විය යුතුය.',
            'bundle-qty-not-changeable' => '":option" බණ්ඩල විකල්පය සඳහා ප්‍රමාණය වෙනස් කළ නොහැක. අවසර ලත් ප්‍රමාණය :qty වේ.',

            'add-product-failed' => 'නිෂ්පාදනය කරත්තයට එකතු කිරීමට අසමත් විය',
            'update-item-failed' => 'කරත්ත අයිතමය යාවත්කාලීන කිරීමට අසමත් විය',
            'remove-item-failed' => 'කරත්ත අයිතමය ඉවත් කිරීමට අසමත් විය',
            'apply-coupon-failed' => 'කූපනය යෙදීමට අසමත් විය',
            'remove-coupon-failed' => 'කූපනය ඉවත් කිරීමට අසමත් විය',
            'coupon-applied' => 'කූපනය සාර්ථකව යොදන ලදී',
            'coupon-removed' => 'කූපනය සාර්ථකව ඉවත් කරන ලදී',
            'move-to-wishlist-failed' => 'අයිතමය කැමති ලැයිස්තුවට ගෙනයාමට අසමත් විය',
            'estimate-shipping-failed' => 'නැව්ගත කිරීම ඇස්තමේන්තු කිරීමට අසමත් විය',

            'product-added-successfully' => 'නිෂ්පාදනය සාර්ථකව කරත්තයට එකතු කරන ලදී',
            'cart-item-updated-successfully' => 'කරත්ත අයිතමය සාර්ථකව යාවත්කාලීන කරන ලදී',
            'guest-cart-merged' => 'අමුත්තාගේ කරත්තය සාර්ථකව ඒකාබද්ධ කරන ලදී',
            'using-authenticated-cart' => 'සත්‍යාපිත පාරිභෝගික කරත්තය භාවිතා කරමින්',
            'cart-item-not-found' => 'කරත්ත අයිතමය හමු නොවිණි',
            'new-guest-cart-created' => 'අද්විතීය සැසි ටෝකනයක් සමඟ නව අමුත්තාගේ කරත්තයක් සාදන ලදී',
            'select-items-to-remove' => 'ඉවත් කිරීමට අයිතම තෝරන්න',
            'select-items-to-move-wishlist' => 'කැමති ලැයිස්තුවට ගෙනයාමට අයිතම තෝරන්න',
            'invalid-or-expired-token' => 'කරත්ත ටෝකනය අවලංගු හෝ කල් ඉකුත් වී ඇත. කරුණාකර නව කරත්තයක් සාදන්න.',
            'invalid-token-of-login-user' => 'පුරනය වූ පරිශීලක ටෝකනය අවලංගුයි.',
            'booking-note-required' => 'මේස වෙන්කිරීම සඳහා booking.note අවශ්‍ය වේ.',
            'booking-date-required' => 'booking.slot ආකෘතිගත කාල පරාසයක් වන විට booking.date අවශ්‍ය වේ.',
            'booking-qty-required' => 'සිදුවීම් වෙන්කිරීම සඳහා booking.qty අවශ්‍ය වේ.',
            'booking-ticket-qty-invalid' => 'සිදුවීම් ටිකට්පත් ප්‍රමාණය සෘණ නොවන සංඛ්‍යාවක් විය යුතුය.',
            'booking-ticket-required' => 'සිදුවීම් වෙන්කිරීම සඳහා අවම වශයෙන් එක් ටිකට්පත් ප්‍රමාණයක් තෝරන්න.',
            'booking-slot-unavailable' => 'තෝරාගත් කාල සටහන ලබා දී ඇති දිනය සඳහා ලබා ගත නොහැක.',
            'invalid-quantity' => 'ප්‍රමාණය අවම වශයෙන් 1 විය යුතුය',
            'product-id-required' => 'නිෂ්පාදන ID අවශ්‍ය වේ',
            'shipping-address-required' => 'නැව්ගත කිරීම ඇස්තමේන්තු කිරීමට රට සහ තැපැල් කේතය අවශ්‍ය වේ',
        ],

        'token-verification' => [
            'invalid-operation' => 'අවලංගු මෙහෙයුම',
            'invalid-input-data' => 'අවලංගු ආදාන දත්ත',
            'token-required' => 'ටෝකනය අවශ්‍ය වේ',
            'invalid-token-format' => 'අවලංගු ටෝකන ආකෘතිය',
            'token-not-found-or-expired' => 'ටෝකනය හමු නොවිණි හෝ කල් ඉකුත් වී ඇත',
            'customer-not-found' => 'පාරිභෝගිකයා හමු නොවිණි',
            'customer-account-suspended' => 'පාරිභෝගික ගිණුම අත්හිටුවා ඇත',
            'error-verifying-token' => 'ටෝකනය සත්‍යාපනය කිරීමේ දෝෂයකි',
            'token-is-valid' => 'ටෝකනය වලංගුයි',
            'unauthenticated' => 'සත්‍යාපනය නොකළ. මෙම ක්‍රියාව සිදු කිරීමට කරුණාකර පුරනය වන්න',
        ],

        'forgot-password' => [
            'invalid-operation' => 'අවලංගු මෙහෙයුම',
            'invalid-input-data' => 'අවලංගු ආදාන දත්ත',
            'email-required' => 'ඊමේල් ලිපිනය අවශ්‍ය වේ',
            'reset-link-sent' => 'යළි සැකසුම් සබැඳිය ඔබගේ ඊමේල් වෙත සාර්ථකව යවන ලදී',
            'email-not-found' => 'ඊමේල් ලිපිනය හමු නොවිණි',
            'throttled' => 'යළි සැකසීමේ උත්සාහයන් ඉතා වැඩිය. නැවත උත්සාහ කිරීමට පෙර කරුණාකර රැඳී සිටින්න.',
            'error-sending-reset-link' => 'යළි සැකසුම් සබැඳිය යැවීමේදී දෝෂයක් ඇතිවිය',
        ],

        'reset-password' => [
            'failed' => 'මුරපදය යළි සැකසිය නොහැකි විය. කරුණාකර නැවත උත්සාහ කරන්න.',
        ],

        'rate-limit' => [
            'too-many-attempts' => 'උත්සාහයන් ඉතා වැඩිය. කරුණාකර තත්පර :seconds කින් නැවත උත්සාහ කරන්න.',
        ],

        'logout' => [
            'invalid-operation' => 'අවලංගු මෙහෙයුම',
            'invalid-input-data' => 'අවලංගු ආදාන දත්ත',
            'token-required' => 'ටෝකනය අවශ්‍ය වේ',
            'invalid-token-format' => 'අවලංගු ටෝකන ආකෘතිය',
            'logged-out-successfully' => 'සාර්ථකව පිටවිය',
            'token-not-found-or-expired' => 'ටෝකනය හමු නොවිණි හෝ දැනටමත් කල් ඉකුත් වී ඇත',
            'error-during-logout' => 'පිටවීමේදී දෝෂයකි',
            'unauthenticated' => 'සත්‍යාපනය නොකළ. මෙම ක්‍රියාව සිදු කිරීමට කරුණාකර පුරනය වන්න',
        ],

        'address' => [
            'deleted-successfully' => 'ලිපිනය සාර්ථකව මකා දමන ලදී',
            'authentication-required' => 'සත්‍යාපන ටෝකනයක් අවශ්‍ය වේ',
            'invalid-token' => 'අවලංගු හෝ කල් ඉකුත් වූ ටෝකනය',
            'unknown-operation' => 'නොදන්නා මෙහෙයුම',
            'address-id-required' => 'ලිපින ID අවශ්‍ය වේ',
            'address-not-found' => 'ලිපිනය හමු නොවිණි හෝ මෙම පාරිභෝගිකයාට අයත් නොවේ',
            'retrieved' => 'ලිපින සාර්ථකව ලබා ගන්නා ලදී',
            'fetch-failed' => 'ලිපින ලබා ගැනීමට අසමත් විය:',
            'field-required' => ':field ක්ෂේත්‍රය අවශ්‍ය වේ',
        ],

        'customer-profile' => [
            'authentication-required' => 'සත්‍යාපන ටෝකනයක් අවශ්‍ය වේ. කරුණාකර විමසුම් ආදානයේ ටෝකනය සපයන්න',
            'invalid-token' => 'අවලංගු හෝ කල් ඉකුත් වූ ටෝකනය',
            'profile-updated' => 'පාරිභෝගික පැතිකඩ සාර්ථකව යාවත්කාලීන කරන ලදී',
        ],

        'customer' => [
            'password-mismatch' => 'මුරපදය සහ තහවුරු කිරීමේ මුරපදය නොගැලපේ',
            'confirm-password-required' => 'මුරපදය වෙනස් කිරීමේදී තහවුරු කිරීමේ මුරපදය අවශ්‍ය වේ',
            'current-password-required' => 'ඔබගේ මුරපදය වෙනස් කිරීමට වත්මන් මුරපදය අවශ්‍ය වේ',
            'current-password-incorrect' => 'ඔබ ඇතුළත් කළ වත්මන් මුරපදය වැරදිය',
            'verify-email-to-login' => 'ඔබගේ ගිණුම සාදා ඇත. පුරනය වීමට කරුණාකර ඔබගේ ඊමේල් ලිපිනය තහවුරු කරන්න.',
            'delete-password-required' => 'ඔබගේ ගිණුම මකා දැමීමට ඔබගේ මුරපදය අවශ්‍ය වේ',
            'delete-password-incorrect' => 'ඔබ ඇතුළත් කළ මුරපදය වැරදිය',
            'delete-has-active-orders' => 'ඔබට පොරොත්තු හෝ සැකසෙමින් පවතින ඇණවුම් ඇති විට ඔබගේ ගිණුම මකා දැමිය නොහැක',
            'unauthenticated' => 'සත්‍යාපනය නොකළ. මෙම ක්‍රියාව සිදු කිරීමට කරුණාකර පුරනය වන්න',
            'id-required' => 'පාරිභෝගික ID අවශ්‍ය වේ',
            'invalid-id-format' => 'අවලංගු ID ආකෘතිය. "/api/admin/customers/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
            'not-found' => 'පාරිභෝගිකයා හමු නොවිණි',
            'phone-special-chars-not-allowed' => 'ජංගම දුරකථන අංකයේ අඩංගු විය හැක්කේ ඉලක්කම් පමණි. විශේෂ අක්ෂරවලට අවසර නැත',
            'email-already-taken' => 'මෙම ඊමේල් ලිපිනය දැනටමත් වෙනත් ගිණුමකට ලියාපදිංචි කර ඇත',
            'invalid-gender' => 'අවලංගු ස්ත්‍රී පුරුෂ භාව අගය ":gender". අවසර ලත් අගයන්: :valid',
        ],

        'product-review' => [
            'id-required' => 'නිෂ්පාදන සමාලෝචන ID අවශ්‍ය වේ',
            'invalid-id-format' => 'අවලංගු ID ආකෘතිය. "/api/shop/reviews/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
            'not-found' => '":id" ID සහිත නිෂ්පාදන සමාලෝචනය හමු නොවිණි',
            'item-not-found' => '":id" අයිතමය හමු නොවිණි.',
            'product-id-required' => 'නිෂ්පාදන ID අවශ්‍ය වේ',
            'product-not-found' => 'නිෂ්පාදනය හමු නොවිණි',
            'rating-invalid' => 'ශ්‍රේණිගත කිරීම 1 සහ 5 අතර විය යුතුය',
            'title-required' => 'සමාලෝචන මාතෘකාව අවශ්‍ය වේ',
            'comment-required' => 'සමාලෝචන අදහස අවශ්‍ය වේ',
            'review-disabled' => 'නිෂ්පාදන සමාලෝචන දැනට අබල කර ඇත',
            'guest-review-disabled' => 'අමුත්තන්ගේ සමාලෝචනවලට අවසර නැත. සමාලෝචනයක් ඉදිරිපත් කිරීමට කරුණාකර පුරනය වන්න',
            'owner-required' => 'ඔබගේ සමාලෝචනය කළමනාකරණය කිරීමට කරුණාකර පුරනය වන්න',
            'not-owned' => 'මෙම සමාලෝචනය ඔබ විසින් ලියා නොමැති බැවින් එය වෙනස් කිරීමට හෝ මකා දැමීමට නොහැක',
            'invalid-attachments' => 'අවලංගු ඇමුණුම් ආකෘතිය.',
            'invalid-product-id-format' => 'අවලංගු නිෂ්පාදන ID ආකෘතිය. "/api/shop/products/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
        ],

        'customer-review' => [
            'id-required' => 'පාරිභෝගික සමාලෝචන ID අවශ්‍ය වේ',
            'invalid-id-format' => 'අවලංගු ID ආකෘතිය. "/api/shop/customer-reviews/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
            'not-found' => '":id" ID සහිත පාරිභෝගික සමාලෝචනය හමු නොවිණි',
        ],

        'newsletter' => [
            'error-during-subscription' => 'මෙම අවස්ථාවේ දායකත්වය සම්පූර්ණ කළ නොහැක. කරුණාකර පසුව නැවත උත්සාහ කරන්න',
        ],

        'contact-us' => [
            'success' => 'ඔබගේ විමසීම සාර්ථකව ඉදිරිපත් කරන ලදී. අපි ඉක්මනින් ඔබව සම්බන්ධ කර ගන්නෙමු',
            'failed' => 'මෙම අවස්ථාවේ ඔබගේ විමසීම යැවිය නොහැක. කරුණාකර පසුව නැවත උත්සාහ කරන්න',
            'invalid-input' => 'අවලංගු සම්බන්ධතා පෝරම දත්ත',
        ],

        'customer-order' => [
            'id-required' => 'පාරිභෝගික ඇණවුම් ID අවශ්‍ය වේ',
            'invalid-id-format' => 'අවලංගු ID ආකෘතිය. "/api/shop/customer-orders/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
            'not-found' => '":id" ID සහිත පාරිභෝගික ඇණවුම හමු නොවිණි',
        ],

        'return' => [
            'not-found' => 'ආපසු යැවීමේ ඉල්ලීම හමු නොවිණි.',
            'disabled' => 'මෙම වෙළඳසැල සඳහා ආපසු යැවීම් සක්‍රීය කර නොමැත.',
            'invalid-input' => 'ආපසු යැවීමේ විස්තර නොමැත හෝ අවලංගුය.',
            'invalid-resolution-type' => 'විසඳුම් වර්ගය "return" හෝ "cancel_items" විය යුතුය.',
            'agreement-required' => 'ඔබ ආපසු යැවීමේ ප්‍රතිපත්තිය පිළිගත යුතුය.',
            'invalid-order' => 'තෝරාගත් ඇණවුම වලංගු නොවේ.',
            'invalid-item' => 'තෝරාගත් අයිතමය ආපසු යැවීම සඳහා සුදුසුකම් නොලබයි.',
            'qty-exceeds' => 'ඉල්ලූ ප්‍රමාණය ආපසු යැවිය හැකි ප්‍රමාණය ඉක්මවයි.',
            'invalid-package-condition' => 'පැකේජයේ තත්ත්වය "open" හෝ "packed" විය යුතුය.',
            'invalid-image' => 'උඩුගත කළ ගොනු එකක හෝ කිහිපයක සහාය නොදක්වන ගොනු වර්ගයක් ඇත.',
            'invalid-custom-field' => 'නොදන්නා අභිරුචි ක්ෂේත්‍රය ":id".',
            'custom-field-required' => '":field" ක්ෂේත්‍රය අවශ්‍ය වේ.',
            'invalid-custom-field-value' => '":field" සඳහා වූ අගය අවසර ලත් විකල්පවලින් එකක් නොවේ.',
            'created' => 'ආපසු යැවීමේ ඉල්ලීම සාර්ථකව සාදන ලදී.',
            'canceled' => 'ආපසු යැවීමේ ඉල්ලීම සාර්ථකව අවලංගු කරන ලදී.',
            'already-canceled' => 'ආපසු යැවීමේ ඉල්ලීම දැනටමත් අවලංගු කර ඇත.',
            'cancel-not-allowed' => 'මෙම ආපසු යැවීමේ ඉල්ලීම අවලංගු කළ නොහැක.',
            'close-not-allowed' => 'මෙම ආපසු යැවීමේ ඉල්ලීම වසා දැමිය නොහැක.',
            'reopened' => 'ආපසු යැවීමේ ඉල්ලීම සාර්ථකව නැවත විවෘත කරන ලදී.',
            'reopen-not-allowed' => 'මෙම ආපසු යැවීමේ ඉල්ලීම නැවත විවෘත කළ නොහැක.',
            'closed' => 'ආපසු යැවීමේ ඉල්ලීම සාර්ථකව වසා දමන ලදී.',
            'message-sent' => 'පණිවිඩය සාර්ථකව යවන ලදී.',
            'attachment-not-found' => 'පණිවිඩ ඇමුණුම හමු නොවිණි.',
        ],

        'eu-withdrawal' => [
            'not-found' => 'ඉවත්වීමේ ප්‍රකාශය හමු නොවිණි.',
            'order-not-found' => 'ඇණවුම සොයාගත නොහැකි විය හෝ ඉවත්වීම සඳහා සුදුසුකම් නොලබයි.',
            'too-many-attempts' => 'උත්සාහයන් ඉතා වැඩිය. කරුණාකර තත්පර :seconds කින් නැවත උත්සාහ කරන්න.',
            'created' => 'ඉවත්වීමේ ප්‍රකාශය සාර්ථකව ගොනු කරන ලදී.',
        ],

        'customer-order-shipment' => [
            'id-required' => 'නැව්ගත කිරීමේ ID අවශ්‍ය වේ',
            'not-found' => '":id" ID සහිත නැව්ගත කිරීම හමු නොවිණි හෝ මෙම පාරිභෝගිකයාට අයත් නොවේ',
            'item-not-found' => '":id" ID සහිත නැව්ගත කිරීමේ අයිතමය හමු නොවිණි හෝ මෙම පාරිභෝගිකයාට අයත් නොවේ',
            'invalid-id-format' => 'අවලංගු ID ආකෘතිය. "/api/shop/customer-order-shipments/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
        ],

        'cancel-order' => [
            'order-id-required' => 'ඇණවුමක් අවලංගු කිරීමට ඇණවුම් ID අවශ්‍ය වේ',
            'not-found' => '":id" ID සහිත ඇණවුම හමු නොවිණි හෝ මෙම පාරිභෝගිකයාට අයත් නොවේ',
            'cancel-success' => 'ඇණවුම සාර්ථකව අවලංගු කරන ලදී',
            'cancel-failed' => 'ඇණවුම අවලංගු කළ නොහැක. එය දැනටමත් සකසා, නැව්ගත කර හෝ අවලංගු කර තිබිය හැක',
        ],

        'reorder' => [
            'order-id-required' => 'නැවත ඇණවුම් කිරීමට ඇණවුම් ID අවශ්‍ය වේ',
            'not-found' => '":id" ID සහිත ඇණවුම හමු නොවිණි හෝ මෙම පාරිභෝගිකයාට අයත් නොවේ',
            'reorder-success' => 'ඔබගේ පෙර ඇණවුමේ අයිතම :count ක් ඔබගේ කරත්තයට එකතු කර ඇත',
            'no-items-added' => 'කරත්තයට කිසිදු අයිතමයක් එකතු කළ නොහැකි විය. නිෂ්පාදන තොගයේ නොමැති හෝ ලබා ගත නොහැකි විය හැක',
        ],

        'customer-downloadable-product' => [
            'id-required' => 'පාරිභෝගික බාගත කළ හැකි නිෂ්පාදන ID අවශ්‍ය වේ',
            'invalid-id-format' => 'අවලංගු ID ආකෘතිය. "/api/shop/customer-downloadable-products/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
            'not-found' => '":id" ID සහිත පාරිභෝගික බාගත කළ හැකි නිෂ්පාදනය හමු නොවිණි',

            'download' => [
                'unauthenticated' => 'අනවසර: පාරිභෝගික සත්‍යාපනය අවශ්‍ය වේ.',
                'not-found' => 'බාගත කළ හැකි නිෂ්පාදනය හමු නොවිණි.',
                'pending' => 'බාගැනීම පොරොත්තුවෙන් පවතී. කරුණාකර ඇණවුම ඉන්වොයිස් කරන තෙක් රැඳී සිටින්න.',
                'limit-exceeded' => 'බාගැනීම් සීමාව ඉක්මවා ඇත.',
                'no-downloads-remaining' => 'තවත් බාගැනීම් ලබා ගත නොහැක.',
                'file-not-found' => 'ගොනුව හමු නොවිණි.',
                'link-not-found' => 'බාගත කළ හැකි සබැඳිය හමු නොවිණි.',
            ],
        ],

        'customer-invoice' => [
            'id-required' => 'පාරිභෝගික ඉන්වොයිස් ID අවශ්‍ය වේ',
            'invalid-id-format' => 'අවලංගු ID ආකෘතිය. "/api/shop/customer-invoices/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
            'not-found' => '":id" ID සහිත පාරිභෝගික ඉන්වොයිසිය හමු නොවිණි',
        ],

        'auth' => [
            'no-token-provided' => 'සත්‍යාපන ටෝකනයක් සපයා නොමැත. කරුණාකර Authorization ශීර්ෂකයේ "Bearer <token>" ලෙස හෝ input.token ක්ෂේත්‍රයේ ටෝකනය සපයන්න',
            'invalid-or-expired-token' => 'අවලංගු හෝ කල් ඉකුත් වූ ටෝකනය',
            'request-not-found' => 'සන්දර්භය තුළ ඉල්ලීම හමු නොවිණි',
            'token-required' => 'Authentication token is required. Please provide the token either in the GraphQL mutation input field or in the Authorization header as "Bearer <token>"',
            'unknown-resource' => 'නොදන්නා සම්පත',
            'cannot-update-other-profile' => 'අනවසර: වෙනත් පාරිභෝගික පැතිකඩක් යාවත්කාලීන කළ නොහැක',
        ],

        'upload' => [
            'invalid-base64' => 'අවලංගු base64 කේතාංකන රූප දත්ත',
            'size-exceeds-limit' => 'රූප ප්‍රමාණය 5MB ඉක්මවිය නොහැක',
            'invalid-format' => 'අවලංගු රූප ආකෘතිය. කරුණාකර data URI යෝජනා ක්‍රමය සමඟ base64 කේතාංකන රූපයක් සපයන්න (data:image/jpeg;base64,...)',
            'failed' => 'රූප උඩුගත කිරීම අසමත් විය',
        ],

        'attribute' => [
            'code-already-exists' => 'ගුණාංග කේතය දැනටමත් පවතී',
            'option-id-required' => 'ගුණාංග විකල්ප සෘජුවම විමසන විට "attributeId" පරාමිතිය අවශ්‍ය වේ',
            'id-required' => 'ගුණාංග ID අවශ්‍ය වේ',
            'invalid-id-format' => 'අවලංගු ID ආකෘතිය. "/api/shop/attributes/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
            'not-found' => 'ගුණාංගය හමු නොවිණි',
        ],

        'attribute-option' => [
            'id-required' => 'ගුණාංග විකල්ප ID අවශ්‍ය වේ',
            'invalid-id-format' => 'අවලංගු ID ආකෘතිය. "/api/shop/attribute-options/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
            'not-found' => 'ගුණාංග විකල්පය හමු නොවිණි',
        ],

        'channel' => [
            'id-required' => 'නාලිකා ID අවශ්‍ය වේ',
            'invalid-id-format' => 'අවලංගු ID ආකෘතිය. "/api/shop/channels/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
            'not-found' => 'නාලිකාව හමු නොවිණි',
        ],

        'country-state' => [
            'id-required' => 'රටේ ප්‍රාන්ත ID අවශ්‍ය වේ',
            'invalid-id-format' => 'අවලංගු ID ආකෘතිය. "/api/shop/country-states/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
            'not-found' => 'රටේ ප්‍රාන්තය හමු නොවිණි',
            'country-id-required' => 'රටේ ID අවශ්‍ය වේ',
        ],

        'country' => [
            'id-required' => 'රටේ ID අවශ්‍ය වේ',
            'invalid-id-format' => 'අවලංගු ID ආකෘතිය. "/api/shop/countries/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
            'not-found' => 'රට හමු නොවිණි',
        ],

        'login' => [
            'invalid-credentials' => 'අවලංගු ඊමේල් හෝ මුරපදය',
            'account-suspended' => 'ඔබගේ ගිණුම අත්හිටුවා ඇත',
            'account-inactive' => 'ඔබගේ සක්රියක් පරපුරක් සෝදන අනුමානයට හෝදන ලදි',
            'email-not-verified' => 'කරුණාකර ඔබගේ ඊමේල් ගිණුමක් පෙනෙන පෙනුමට පිළිගනිමු.',
            'successful' => 'ඔබ සාර්ථකව පුරනය වී ඇත',
            'invalid-request' => 'අවලංගු පුරනය වීමේ ඉල්ලීම',
        ],

        'social-login' => [
            'signed-in' => 'සාර්ථකව පිවිසුණි.',
            'token-required' => 'සමාජ පිවිසුම් ටෝකනයක් අවශ්‍යයි.',
            'invalid-token' => 'සමාජ පිවිසුම් ටෝකනය වලංගු නොවේ හෝ කල් ඉකුත් වී ඇත. කරුණාකර නැවත උත්සාහ කරන්න.',
            'google-not-configured' => 'මෙම වෙළඳසැල සඳහා Google පුරනය වීම වින්‍යාස කර නොමැත.',
            'wrong-audience' => 'මෙම ටෝකනය වෙනත් යෙදුමක් සඳහා නිකුත් කර ඇත.',
            'email-required' => 'සැපයුම්කරු විද්‍යුත් තැපැල් ලිපිනයක් බෙදා නොගත්තේය. කරුණාකර ඒ වෙනුවට විද්‍යුත් තැපෑලෙන් ලියාපදිංචි වන්න.',
            'account-inactive' => 'ඔබගේ සක්රියක් පරපුරක් සෝදන අනුමානයට හෝදන ලදි',
            'provider-not-supported' => 'මෙම සමාජ පිවිසුම් සැපයුම්කරු සහාය නොදක්වයි.',
            'provider-disabled' => 'මෙම සමාජ පිවිසුම් සැපයුම්කරු සක්‍රීය කර නැත.',
        ],

        'checkout' => [
            'invalid-input' => 'ගෙවීම් මෙහෙයුම සඳහා අවලංගු ආදාන දත්ත',
            'billing-address-required' => 'බිල්පත් ලිපිනය අවශ්‍ය වේ',
            'shipping-address-required' => 'නැව්ගත කිරීම් සඳහා නැව්ගත ලිපිනය අවශ්‍ය වේ',
            'address-save-failed' => 'ලිපිනය සුරැකීමට අසමත් විය',
            'address-saved' => 'ලිපිනය සාර්ථකව සුරැකිණි',
            'shipping-method-required' => 'නැව්ගත කිරීමේ ක්‍රමය අවශ්‍ය වේ',
            'invalid-shipping-method' => 'අවලංගු හෝ නොමැති නැව්ගත කිරීමේ ක්‍රමය',
            'shipping-method-save-failed' => 'නැව්ගත කිරීමේ ක්‍රමය සුරැකීමට අසමත් විය',
            'shipping-method-saved' => 'නැව්ගත කිරීමේ ක්‍රමය සාර්ථකව සුරැකිණි',
            'shipping-method-error' => 'නැව්ගත කිරීමේ ක්‍රමය සුරැකීමේ දෝෂයකි',
            'payment-method-required' => 'ගෙවීම් ක්‍රමය අවශ්‍ය වේ',
            'invalid-payment-method' => 'අවලංගු හෝ නොමැති ගෙවීම් ක්‍රමය',
            'payment-method-save-failed' => 'ගෙවීම් ක්‍රමය සුරැකීමට අසමත් විය',
            'payment-method-saved' => 'ගෙවීම් ක්‍රමය සාර්ථකව සුරැකිණි',
            'payment-method-error' => 'ගෙවීම් ක්‍රමය සුරැකීමේ දෝෂයකි',
            'order-placed' => 'ඇණවුම සාර්ථකව තබන ලදී',
            'order-redirect-required' => 'මෙම ගෙවීම් ක්‍රමයට අනුව සාප්පු යන්නා ගෙවීම් ද්වාරයේදී ගෙවීම සම්පූර්ණ කළ යුතුය. ඔවුන්ව redirectUrl වෙත යොමු කරන්න; ද්වාරය ගෙවීම තහවුරු කළ පසු ඇණවුම සාදනු ලැබේ.',
            'order-creation-failed' => 'ඇණවුම සෑදීම අසමත් විය: ඇණවුම් ID හිස් හෝ ඇණවුම සුරැකී නැත',
            'order-retrieval-failed' => 'සාදන ලද ඇණවුම ලබා ගැනීමට අසමත් විය',
            'order-creation-error' => 'ඇණවුම සෑදීමට අසමත් විය',
            'cart-empty' => 'කරත්තය හිස්ය',
            'account-suspended' => 'ඔබගේ ගිණුම අත්හිටුවා ඇත. කරුණාකර සහාය අමතන්න.',
            'account-inactive' => 'ඔබගේ ගිණුම අක්‍රියයි. කරුණාකර සහාය අමතන්න.',
            'minimum-order-not-met' => 'අවම ඇණවුම් මුදල :amount වේ',
            'guest-checkout-not-allowed' => 'ගෙවීම සඳහා කරුණාකර ලොග් වන්න. මෙම කරත්තය සඳහා අමුත්තන් ලෙස ගෙවීම ලබා ගත නොහැක.',
            'email-required' => 'ඇණවුම සෑදීම සඳහා ඊමේල් ලිපිනය අවශ්‍ය වේ',
            'unknown-operation' => 'නොදන්නා ගෙවීම් මෙහෙයුම',
        ],

        'customer-addresses' => [
            'token-required' => 'පාරිභෝගික ලිපින ලබා ගැනීමට ටෝකනය අවශ්‍ය වේ',
            'invalid-or-expired-token' => 'අවලංගු හෝ කල් ඉකුත් වූ ටෝකනය',
            'token-validation-failed' => 'ටෝකන සත්‍යාපනය අසමත් විය',
        ],

        'product' => [
            'not-found' => 'නිෂ්පාදනය හමු නොවිණි',
            'not-found-with-sku' => 'SKU සහිත නිෂ්පාදනයක් හමු නොවිණි',
            'not-found-with-url-key' => 'URL යතුර සහිත නිෂ්පාදනයක් හමු නොවිණි',
            'parameters-required' => 'පහත පරාමිතිවලින් අවම වශයෙන් එකක් සැපයිය යුතුය: "sku", "id", "urlKey"',
            'type' => 'නිෂ්පාදන වර්ගය',
            'attribute-family' => 'ගුණාංග පවුල',
            'sku' => 'SKU',
            'name' => 'නම',
            'description' => 'විස්තරය',
            'short-description' => 'කෙටි විස්තරය',
            'status' => 'තත්ත්වය',
            'new' => 'නව',
            'featured' => 'විශේෂාංගගත',
            'price' => 'මිල',
            'special-price' => 'විශේෂ මිල',
            'weight' => 'බර',
            'cost' => 'පිරිවැය',
            'length' => 'දිග',
            'width' => 'පළල',
            'height' => 'උස',
            'color' => 'වර්ණය',
            'size' => 'ප්‍රමාණය',
            'brand' => 'වෙළඳ නාමය',
            'super-attributes' => 'සුපිරි ගුණාංග',
            'id-required' => 'නිෂ්පාදන ID අවශ්‍ය වේ',
            'invalid-id-format' => 'අවලංගු ID ආකෘතිය. "/api/shop/products/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
        ],

        'downloadable-product' => [
            'download-link-not-found' => 'බාගැනීම් සබැඳුව හමු නොවිණි හෝ කල් ඉකුත් වී ඇත',
            'purchased-link-not-found' => 'ඉ購නුගත සබැඳුව හමු නොවිණි',
            'file-not-found' => 'ගොනුව හමු නොවිණි',
            'download-successful' => 'ගොනුව බාගැනීමට සූදානම්',
            'token-required' => 'බාගැනීම් ටෝකනය අවශ්ය වේ',
            'invalid-token' => 'බාගැනීම් ටෝකනය অবලංගු හෝ කල් ඉකුත්',
            'token-expired' => 'බාගැනීම් ටෝකනය කල් ඉකුත් වී ඇත. කරුණාකර නව එකක් සාදන්න',
            'access-denied' => 'ප්‍රවේශ ප්‍රතික්ෂේප කරන ලදි: ඔබ මෙම ගොනුව බාගැනීමට අවසර නොමាន',
            'redirect-external-url' => 'බාහිර බාගැනීම් URL එකට යළි යොමු කිරීම',
            'file-error' => 'ඔබගේ බාගැනීම් ඉල්ලීම සකසා ගන්නා අතරතුර දෝෂයක් ඇතිවිය',
            'unauthorized-access' => 'බාගැනීම් සම්පතට අননුමතිකරණ ප්‍රවේශ',
            'unauthenticated' => 'අනවසර: පාරිභෝගික සත්‍යාපනය අවශ්‍ය වේ',
            'link-id-required' => 'downloadableLinkPurchasedId අවශ්‍ය වේ',
            'link-not-found' => 'බාගත කළ හැකි සබැඳිය හමු නොවිණි',
            'not-owner' => 'අනවසර: මෙම බාගැනීම් සබැඳිය ඔබට අයත් නොවේ',
            'pending' => 'බාගැනීම පොරොත්තුවෙන් පවතී. කරුණාකර ඇණවුම ඉන්වොයිස් කරන තෙක් රැඳී සිටින්න.',
            'limit-exceeded' => 'බාගැනීම් සීමාව ඉක්මවා ඇත',
            'no-downloads-left' => 'තවත් බාගැනීම් ලබා ගත නොහැක',
        ],

        'booking-slot' => [
            'id-required' => 'වෙන්කිරීම් නිෂ්පාදන id අවශ්‍ය වේ.',
            'date-required' => 'දිනය අවශ්‍ය වේ.',
            'product-not-found' => 'වෙන්කිරීම් නිෂ්පාදනය හමු නොවිණි.',
            'invalid-type' => 'මෙම වෙන්කිරීම් වර්ගයට සහාය නොදක්වයි.',
            'invalid-time-range' => 'අවලංගු කාල පරාසය.',
            'invalid-time-format' => 'අවලංගු කාල ආකෘතිය.',
        ],

        'resolver' => [
            'no-resolve-info' => 'සන්දර්භය තුළ GraphQL ResolveInfo ලබා ගත නොහැක. මාදිලි පන්තිය තීරණය කළ නොහැක.',
            'unknown-resource-class' => 'GraphQL ප්‍රතිදාන වර්ගයෙන් සම්පත් පන්තිය තීරණය කළ නොහැක',
        ],

        'docs' => [
            'openapi-unavailable' => 'OpenAPI Factory නිර්මාණය කළ නොහැකි විය',
        ],

        'locale' => [
            'id-required' => 'භාෂා ප්‍රදේශ ID අවශ්‍ය වේ',
            'invalid-id-format' => 'අවලංගු ID ආකෘතිය. "/api/shop/locales/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
            'not-found' => 'භාෂා ප්‍රදේශය හමු නොවිණි',
        ],

        'category' => [
            'id-required' => 'ප්‍රවර්ග ID අවශ්‍ය වේ',
            'invalid-id-format' => 'අවලංගු ID ආකෘතිය. "/api/shop/categories/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
            'not-found' => 'ප්‍රවර්ගය හමු නොවිණි',
        ],

        'compare-item' => [
            'id-required' => 'සැසඳුම් අයිතමයට ID අවශ්ය है.',
            'invalid-id-format' => 'අවලංගු ID ස්වරූපය. "/api/shop/compare-items/1" හෝ සංඛ්යාත්මක ID වැනි IRI ස්වරූපය අපේක්ෂා කෙරේ',
            'not-found' => 'සැසඳුම් අයිතමය හමු නොවිණි',
            'product-id-required' => 'නිෂ්පාදන ID අවශ්ය है.',
            'customer-id-required' => 'ගස්සා ගැනීමේ ID අවශ්ය है.',
            'product-not-found' => 'නිෂ්පාදනය හමු නොවිණි',
            'customer-not-found' => 'ගස්සා ගැනීම හමු නොවිණි',
            'already-exists' => 'මෙම නිෂ්පාදනය ඔබගේ සැසඳුම් ලැයිස්තුවේ දැනටමත් පවතී',
            'delete-all-success' => 'සියලුම සංසන්දන අයිතම සාර්ථකව ඉවත් කරන ලදී',
        ],

        'wishlist' => [
            'id-required' => 'කැමති ලැයිස්තු අයිතම ID අවශ්‍ය වේ',
            'invalid-id-format' => 'අවලංගු ID ආකෘතිය. "/api/shop/wishlists/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
            'not-found' => 'කැමති ලැයිස්තු අයිතමය හමු නොවිණි',
            'product-id-required' => 'නිෂ්පාදන ID අවශ්‍ය වේ',
            'customer-id-required' => 'පාරිභෝගික ID අවශ්‍ය වේ',
            'product-not-found' => 'නිෂ්පාදනය හමු නොවිණි',
            'product-disabled' => 'මෙම නිෂ්පාදනය දැනට අබල කර ඇත',
            'customer-not-found' => 'පාරිභෝගිකයා හමු නොවිණි',
            'already-exists' => 'මෙම නිෂ්පාදනය දැනටමත් ඔබගේ කැමති ලැයිස්තුවේ ඇත',
            'added' => 'අයිතමය සාර්ථකව කැමති ලැයිස්තුවට එකතු කරන ලදී',
            'removed' => 'අයිතමය සාර්ථකව කැමති ලැයිස්තුවෙන් ඉවත් කරන ලදී',
            'product-removed' => 'නිෂ්පාදනය ඉවත් කර ඇත',
            'wishlist-item-id-required' => 'කැමති ලැයිස්තු අයිතම ID අවශ්‍ය වේ',
            'invalid-quantity' => 'ප්‍රමාණය 0 ට වඩා වැඩි විය යුතුය',
            'move-to-cart-missing-options' => 'නිෂ්පාදනයේ අවශ්‍ය විකල්ප නොමැත. කරුණාකර එය අතින් වින්‍යාස කරන්න',
            'moved-to-cart-success' => 'අයිතමය සාර්ථකව කරත්තයට ගෙන යන ලදී',
            'delete-all-success' => 'සියලුම කැමති ලැයිස්තු අයිතම සාර්ථකව ඉවත් කරන ලදී',
        ],

        'gdpr' => [
            'disabled' => 'GDPR දත්ත ඉල්ලීම් අබල කර ඇත. කරුණාකර පරිපාලක වින්‍යාසයෙන් GDPR සක්‍රීය කරන්න.',
            'unauthenticated' => 'GDPR දත්ත ඉල්ලීම් කළමනාකරණය කිරීමට ඔබ පුරනය වී සිටිය යුතුය.',
            'not-found' => 'GDPR දත්ත ඉල්ලීම හමු නොවිණි',
            'type-required' => 'ඉල්ලීම් වර්ගය අවශ්‍ය වේ',
            'type-invalid' => 'ඉල්ලීම් වර්ගය "delete" හෝ "update" විය යුතුය',
            'message-required' => 'පණිවිඩය අවශ්‍ය වේ',
            'raised' => 'ඔබගේ GDPR දත්ත ඉල්ලීම සාර්ථකව ඉදිරිපත් කරන ලදී.',
            'revoked' => 'ඔබගේ GDPR දත්ත ඉල්ලීම සාර්ථකව අහෝසි කරන ලදී.',
            'revoke-not-allowed' => 'මෙම GDPR දත්ත ඉල්ලීම තවදුරටත් අහෝසි කළ නොහැක.',
            'deleted' => 'ඔබගේ GDPR දත්ත ඉල්ලීම සාර්ථකව මකා දමන ලදී.',
        ],

        'section' => [
            'id-required' => 'කොටස් ID අවශ්‍ය වේ',
            'invalid-id-format' => 'අවලංගු ID ආකෘතිය. "/api/shop/sections/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
            'not-found' => 'කොටස හමු නොවිණි',
        ],

        'install' => [
            'starting' => 'API Platform ස්ථාපනය ආරම්භ කරමින්...',
            'completed-success' => '✓ API Platform ස්ථාපනය සාර්ථකව සම්පූර්ණ විය!',
            'failed' => '✗ ස්ථාපනය අසමත් විය: ',

            'provider-already-registered' => 'ℹ Service Provider දැනටමත් ලියාපදිංචි කර ඇත',
            'provider-not-registered' => '⚠ Service Provider ස්වයංක්‍රීයව ලියාපදිංචි කළ නොහැකි විය. :provider අතින් bootstrap/providers.php වෙත එක් කරන්න.',
            'provider-registered' => '✓ Service Provider bootstrap/providers.php හි ලියාපදිංචි කරන ලදී',
            'provider-file-not-found' => 'ගොනුව හමු නොවිණි: :file',
            'provider-permission-denied' => 'අවසරය ප්‍රතික්ෂේප විය: :file වෙත ලිවිය නොහැක. කරුණාකර ගොනු අවසර පරීක්ෂා කරන්න.',

            'config-already-exists' => 'ℹ වින්‍යාස ගොනුව දැනටමත් පවතී',
            'config-published' => '✓ API Platform වින්‍යාසය config/ වෙත ප්‍රකාශයට පත් කරන ලදී',
            'config-source-not-found' => 'මූලාශ්‍ර වින්‍යාස ගොනුව හමු නොවිණි: :file',
            'config-permission-denied' => 'අවසරය ප්‍රතික්ෂේප විය: :directory වෙත ලිවිය නොහැක. කරුණාකර නාමාවලි අවසර පරීක්ෂා කරන්න.',

            'translatable-not-found' => 'ℹ TranslatableModel හමු නොවිණි - මඟ හරිමින්',
            'translatable-already-abstract' => 'ℹ TranslatableModel දැනටමත් abstract වේ',
            'translatable-made-abstract' => '✓ TranslatableModel abstract කරන ලදී',
            'translatable-permission-denied' => 'අවසරය ප්‍රතික්ෂේප විය: :file වෙත ලිවිය නොහැක. කරුණාකර ගොනු අවසර පරීක්ෂා කරන්න.',

            'providers-already-registered' => 'ℹ API Platform සපයන්නන් දැනටමත් ලියාපදිංචි කර ඇත',
            'providers-registered' => '✓ API Platform සපයන්නන් bootstrap/app.php හි ලියාපදිංචි කරන ලදී',
            'providers-file-not-found' => 'ගොනුව හමු නොවිණි: :file',
            'providers-permission-denied' => 'අවසරය ප්‍රතික්ෂේප විය: :file වෙත ලිවිය නොහැක. කරුණාකර ගොනු අවසර පරීක්ෂා කරන්න.',
            'providers-not-found' => 'bootstrap/app.php හි Application නිර්මාණය සොයාගත නොහැකි විය',

            'assets-published' => '✓ පැකේජ වත්කම් ප්‍රකාශයට පත් කරන ලදී',
            'running-migrations' => 'දත්ත සමුදා සංක්‍රමණ ධාවනය කරමින්...',
            'migrations-completed' => '✓ දත්ත සමුදා සංක්‍රමණ සම්පූර්ණ විය',
            'clearing-caches' => 'හැඹිලි හිස් කර ප්‍රශස්ත කරමින්...',
            'caches-optimized' => '✓ හැඹිලි හිස් කර ප්‍රශස්ත කරන ලදී',
            'generating-api-key' => 'API යතුර ජනනය කරමින්...',
            'api-key-generated' => '✓ API යතුර සාර්ථකව ජනනය කරන ලදී',
            'api-key-already-exists' => 'ℹ පෙරනිමි Store API යතුර දැනටමත් පවතී - ජනනය මඟ හරිමින්',

            'migrations-error' => 'දත්ත සමුදා සංක්‍රමණ අසමත් විය.',
            'migrations-error-running' => 'දත්ත සමුදා සංක්‍රමණ ධාවනය කිරීමේ දෝෂයකි: :error',
            'cache-clear-error' => 'හැඹිලිය හිස් කිරීම අසාර්ථක විය.',
            'cache-optimize-error' => 'ප්‍රශස්තකරණය අසාර්ථක විය.',
            'cache-error' => 'හැඹිලි හිස් කිරීමේදී සහ ප්‍රශස්ත කිරීමේදී දෝෂයක්: :error',
            'api-key-generation-error' => 'API යතුර ජනනය අසමත් විය.',
            'api-key-error' => 'API යතුර ජනනය කිරීමේ දෝෂයකි: :error',

            'key-management-required' => ':action ක්‍රියාව සඳහා --key අවශ්‍ය වේ',
            'key-rotation-error' => 'යතුර භ්‍රමණය කිරීමේ දෝෂයකි: :error',
            'key-deactivation-error' => 'යතුර අක්‍රිය කිරීමේ දෝෂයකි: :error',
            'key-not-found' => 'API යතුර හමු නොවිණි: :identifier',

            'maintenance-starting' => '🔄 API යතුරු නඩත්තුව ආරම්භ කරමින්...',
            'maintenance-complete' => '✅ API යතුරු නඩත්තුව සම්පූර්ණයි',
            'cleanup-expired-keys' => '🧹 කල් ඉකුත් වූ යතුරු පිරිසිදු කරමින්...',
            'cleanup-expired-none' => '   ℹ️ පිරිසිදු කිරීමට කල් ඉකුත් වූ යතුරු නොමැත',
            'invalidate-deprecated' => '⚠️ අත්හැර දැමූ යතුරු අවලංගු කරමින්...',
            'invalidate-deprecated-none' => '   ℹ️ අවලංගු කිරීමට අත්හැර දැමූ යතුරු නොමැත',
            'notify-expiring' => '📧 කල් ඉකුත්වීමේ දැනුම්දීම් යවමින්...',
            'notify-expiring-none' => '   ℹ️ දැනුම්දීම් අවශ්‍ය යතුරු නොමැත',

            'key-rotated-success' => '✅ යතුර සාර්ථකව භ්‍රමණය කරන ලදී!',
            'key-deactivated-success' => '✅ යතුර සාර්ථකව අක්‍රිය කරන ලදී!',
            'deactivation-cancelled' => 'අක්‍රිය කිරීම අවලංගු කරන ලදී.',
            'cleanup-cancelled' => 'පිරිසිදු කිරීම අවලංගු කරන ලදී.',

            'key-status-title' => 'යතුරේ තත්ත්වය: :name',
            'key-status-active' => 'සක්‍රීය: :status',
            'key-status-usable' => 'භාවිතා කළ හැකි: :status',
            'key-status-expired' => 'කල් ඉකුත් වූ: :status',
            'key-status-deprecated' => 'අත්හැර දැමූ: :status',
            'key-status-expires-at' => 'කල් ඉකුත් වන්නේ: :expires',
            'key-status-days-until-expiry' => 'කල් ඉකුත් වීමට ඇති දින: :days',
            'key-status-last-used' => 'අවසන් වරට භාවිතා කළේ: :date',
            'key-status-rotated-from' => 'භ්‍රමණය කළේ මෙයින්: :key',
            'key-status-rotated-keys' => 'මෙයින් භ්‍රමණය කළ යතුරු: :count',

            'policy-compliance-summary' => 'API යතුරු භ්‍රමණ ප්‍රතිපත්ති අනුකූලතා සාරාංශය',
            'available-actions' => 'ලබා ගත හැකි ක්‍රියා: rotate, deactivate, cleanup, status, expiring, unused, summary',

            'asset-linked-success' => '✓ API Platform වත්කම් සාර්ථකව සම්බන්ධ කරන ලදී',
            'symlink-create-failed' => 'symlink සෑදිය නොහැකි විය, ඒ වෙනුවට වත්කම් පිටපත් කරමින්...',
            'asset-copy-warning' => 'අවවාදයයි: API Platform වත්කම් සම්බන්ධ කිරීමට හෝ පිටපත් කිරීමට නොහැකි විය. අතින් සැකසීම අවශ්‍ය විය හැක.',
            'asset-copied-success' => '✓ API Platform වත්කම් සාර්ථකව පිටපත් කරන ලදී',

            'key-name-required' => 'Storefront යතුරේ නම හිස් විය නොහැක.',
            'key-generated-success' => 'Storefront යතුර සාර්ථකව ජනනය කරන ලදී!',
            'key-details' => 'යතුරේ විස්තර:',
            'key-secure-warning' => 'මෙම යතුර ආරක්ෂිතව තබා ගන්න! එය X-STOREFRONT-KEY ශීර්ෂයේ භාවිතා කෙරේ.',
            'key-share-warning' => 'මෙම යතුර ප්‍රසිද්ධියේ බෙදා නොගන්න හෝ අනුවාද පාලනයට commit නොකරන්න.',

            'key-already-exists' => '\':name\' නම සහිත storefront යතුරක් දැනටමත් පවතී.',
            'cleanup-success' => '✅ කල් ඉකුත් වූ යතුරු :count ක් පිරිසිදු කරන ලදී',
            'confirm-cleanup' => 'මෙය කල් ඉකුත් වූ සියලුම යතුරු මෘදු ලෙස මකා දමනු ඇත. දිගටම කරගෙන යන්නද?',
            'confirm-deactivate' => 'ඔබට :name යතුර අක්‍රිය කිරීමට අවශ්‍ය බව විශ්වාසද?',

            'old-key' => 'පැරණි යතුර: :name',
            'old-key-id' => 'පැරණි යතුරු ID: :id',
            'deprecation-date' => 'අත්හැරීමේ දිනය: :date',
            'new-key' => 'නව යතුර: :name',
            'new-key-id' => 'නව යතුරු ID: :id',
            'new-key-value' => 'නව යතුරු අගය: :key',
            'expires-at' => 'කල් ඉකුත් වන්නේ: :date',
            'invalid-action' => 'අවලංගු ක්‍රියාව: :action',
            'key-active' => 'සක්‍රීය: :status',
            'key-usable' => 'භාවිතා කළ හැකි: :status',
            'key-expired' => 'කල් ඉකුත් වූ: :status',
            'key-deprecated' => 'අත්හැර දැමූ: :status',
            'key-field-id' => 'ID',
            'key-field-name' => 'නම',
            'key-field-key' => 'යතුර',
            'key-field-rate-limit' => 'අනුපාත සීමාව',
            'key-field-status' => 'තත්ත්වය',
            'key-days' => ' දින',
            'key-never' => 'කිසිදා නැත',
            'key-requests-minute' => ' ඉල්ලීම්/මිනිත්තුව',
            'key-unlimited' => 'අසීමිත',
            'rate-limit-exceeded' => 'ඉල්ලූ අනුපාත සීමාව අවසර ලත් උපරිමය :max ඉක්මවයි. ඉල්ලීම්/මිනිත්තුව :max ලෙස සකසමින්.',

            'vendor-path-not-found' => 'API Platform vendor මාර්ගය හමු නොවිණි: :path',
            'assets-already-linked' => 'API Platform වත්කම් දැනටමත් සම්බන්ධ කර ඇත: :path',
            'no-keys-expiring' => 'ඉදිරි දින :days තුළ කල් ඉකුත් වන යතුරු නොමැත',
            'keys-expiring-title' => 'ඉදිරි දින :days තුළ කල් ඉකුත් වන යතුරු:',
            'key-display-format' => '• :name (ID: :id)',
            'key-expires-display' => '  කල් ඉකුත් වන්නේ: :date (දින :days ක් ඉතිරිව ඇත)',
            'no-unused-keys' => 'භාවිතා නොකළ යතුරු හමු නොවිණි (> දින :days)',
            'unused-keys-title' => 'භාවිතා නොකළ යතුරු (> දින :days):',
            'key-last-used-display' => '  අවසන් වරට භාවිතා කළේ: :date',
            'total-keys' => 'මුළු යතුරු: :count',
            'valid-keys' => 'වලංගු යතුරු: :count',
            'expired-keys' => 'කල් ඉකුත් වූ යතුරු: :count',
            'deprecated-keys' => 'අත්හැර දැමූ යතුරු: :count',
            'keys-expiring-soon' => 'ඉක්මනින් කල් ඉකුත් වන යතුරු (දින 7): :count',
            'unused-keys-summary' => 'භාවිතා නොකළ යතුරු (දින 90): :count',
            'recently-rotated' => 'මෑතකදී භ්‍රමණය කළ (දින 30): :count',
            'cleanup-success-message' => '   ✅ කල් ඉකුත් වූ යතුරු :count ක් පිරිසිදු කරන ලදී',
            'invalidate-success-message' => '   ✅ අත්හැර දැමූ යතුරු :count ක් අවලංගු කරන ලදී',
            'notify-success-message' => '   ✅ කල් ඉකුත්වීමේ දැනුම්දීම් :count ක් යවන ලදී',
            'notify-failed-message' => '   ⚠️ :key පිළිබඳව දැනුම් දීමට අසමත් විය: :error',
            'publish-assets-warning' => 'අවවාදයයි: පැකේජ වත්කම් ප්‍රකාශයට පත් කළ නොහැකි විය. :error',
            'env-config-saved' => '✓ API Platform පරිසර වින්‍යාසය සාර්ථකව සුරකින ලදී',
            'api-endpoints' => 'API අන්ත ලක්ෂ්‍ය:',
            'api-documentation' => 'API ලේඛනගත කිරීම: :url',
            'api-landing-page' => 'API ගොඩබෑමේ පිටුව: :url',
            'graphql-playground' => 'GraphQL Playground: :url',
            'rest-api-storefront' => 'Rest API Storefront Swagger: :url',
            'rest-api-admin' => 'Rest API Admin Swagger: :url',
            'key-rotation-invalid-key' => 'අවලංගු හෝ කල් ඉකුත් වූ යතුරක් භ්‍රමණය කළ නොහැක',
            'env-file-not-found' => '.env ගොනුව හමු නොවිණි',
            'env-permission-denied' => 'අවසරය ප්‍රතික්ෂේප විය: .env ගොනුවට ලිවිය නොහැක. කරුණාකර ගොනු අවසර පරීක්ෂා කරන්න.',
        ],

        'attribute-translation' => [
            'id-required' => 'ගුණාංග පරිවර්තන ID අවශ්‍ය වේ',
            'invalid-id-format' => 'අවලංගු ID ආකෘතිය. "/api/shop/attribute-translations/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
            'not-found' => 'ගුණාංග පරිවර්තනය හමු නොවිණි',
        ],

        'currency' => [
            'id-required' => 'මුදල් ඒකක ID අවශ්‍ය වේ',
            'invalid-id-format' => 'අවලංගු ID ආකෘතිය. "/api/shop/currencies/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
            'not-found' => 'මුදල් ඒකකය හමු නොවිණි',
        ],

        'customer-address' => [
            'id-required' => 'පාරිභෝගික ලිපින ID අවශ්‍ය වේ',
            'invalid-id-format' => 'අවලංගු ID ආකෘතිය. "/api/shop/customer-addresses/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
        ],

        'customer-order-shipment-item' => [
            'id-required' => 'නැව්ගත කිරීමේ අයිතම ID අවශ්‍ය වේ',
            'invalid-id-format' => 'අවලංගු ID ආකෘතිය. "/api/shop/customer-order-shipment-items/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
            'not-found' => 'නැව්ගත කිරීමේ අයිතමය හමු නොවිණි',
        ],

        'page' => [
            'id-required' => 'පිටු ID අවශ්‍ය වේ',
            'invalid-id-format' => 'අවලංගු ID ආකෘතිය. "/api/shop/pages/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
            'not-found' => 'පිටුව හමු නොවිණි',
        ],

        'page-translation' => [
            'id-required' => 'පිටු පරිවර්තන ID අවශ්‍ය වේ',
            'invalid-id-format' => 'අවලංගු ID ආකෘතිය. "/api/shop/page-translations/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
            'not-found' => 'පිටු පරිවර්තනය හමු නොවිණි',
        ],

        'product-bundle-option' => [
            'id-required' => 'පැකේජ විකල්ප ID අවශ්‍ය වේ',
            'invalid-id-format' => 'අවලංගු ID ආකෘතිය. "/api/shop/product-bundle-options/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
            'not-found' => 'පැකේජ විකල්පය හමු නොවිණි',
        ],

        'product-bundle-option-product' => [
            'id-required' => 'පැකේජ විකල්ප නිෂ්පාදන ID අවශ්‍ය වේ',
            'invalid-id-format' => 'අවලංගු ID ආකෘතිය. "/api/shop/product-bundle-option-products/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
            'not-found' => 'පැකේජ විකල්ප නිෂ්පාදනය හමු නොවිණි',
        ],

        'product-downloadable-link' => [
            'id-required' => 'බාගත කළ හැකි සබැඳි ID අවශ්‍ය වේ',
            'invalid-id-format' => 'අවලංගු ID ආකෘතිය. "/api/shop/product-downloadable-links/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
            'not-found' => 'බාගත කළ හැකි සබැඳිය හමු නොවිණි',
        ],

        'product-downloadable-sample' => [
            'id-required' => 'බාගත කළ හැකි නියැදි ID අවශ්‍ය වේ',
            'invalid-id-format' => 'අවලංගු ID ආකෘතිය. "/api/shop/product-downloadable-samples/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
            'not-found' => 'බාගත කළ හැකි නියැදිය හමු නොවිණි',
        ],

        'product-grouped-product' => [
            'id-required' => 'සමූහගත නිෂ්පාදන ID අවශ්‍ය වේ',
            'invalid-id-format' => 'අවලංගු ID ආකෘතිය. "/api/shop/product-grouped-products/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
            'not-found' => 'සමූහගත නිෂ්පාදනය හමු නොවිණි',
        ],

        'product-images' => [
            'id-required' => 'නිෂ්පාදන රූප ID අවශ්‍ය වේ',
            'invalid-id-format' => 'අවලංගු ID ආකෘතිය. "/api/shop/product-images/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
            'not-found' => 'නිෂ්පාදන රූපය හමු නොවිණි',
        ],

        'product-videos' => [
            'id-required' => 'නිෂ්පාදන වීඩියෝ ID අවශ්‍ය වේ',
            'invalid-id-format' => 'අවලංගු ID ආකෘතිය. "/api/shop/product-videos/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
            'not-found' => 'නිෂ්පාදන වීඩියෝව හමු නොවිණි',
        ],

        'theme-customization' => [
            'id-required' => 'තේමා අභිරුචිකරණ ID අවශ්‍ය වේ',
            'invalid-id-format' => 'අවලංගු ID ආකෘතිය. "/api/shop/theme-customizations/1" වැනි IRI ආකෘතියක් හෝ සංඛ්‍යාත්මක ID එකක් අපේක්ෂා කෙරේ',
            'not-found' => 'තේමා අභිරුචිකරණය හමු නොවිණි',
        ],

        'unknown-resource' => [
            'id-required' => 'සම්පත් ID අවශ්‍ය වේ',
            'not-found' => 'සම්පත හමු නොවිණි',
        ],
    ],

    'admin' => [
        // login / logout / forgot-password / profile-update keys removed in the
        // 2026-05-27 auth refactor — admin clients now authenticate via
        // pre-issued integration tokens (Bearer header, AdminApiGuard).
        'profile' => [
            'unauthenticated' => 'ඔබ සත්‍යාපනය වී නොමැත.',
        ],

        'dashboard' => [
            'invalid-type' => 'නොදන්නා උපකරණ පුවරු සංඛ්‍යාලේඛන වර්ගයකි. වලංගු වර්ග: over-all, today, stock-threshold-products, total-sales, top-selling-products, top-customers.',
        ],

        'rma' => [
            'not-found' => 'ආපසු යැවීමේ ඉල්ලීම හමු නොවිණි.',
            'invalid-input' => 'ආපසු යැවීමේ විස්තර නොමැත හෝ අවලංගුය.',
            'invalid-order' => 'තෝරාගත් ඇණවුම වලංගු නොවේ.',
            'invalid-item' => 'තෝරාගත් අයිතමය ආපසු යැවීම සඳහා සුදුසුකම් නොලබයි.',
            'invalid-resolution-type' => 'විසඳුම් වර්ගය "return" හෝ "cancel_items" විය යුතුය.',
            'qty-exceeds' => 'ඉල්ලූ ප්‍රමාණය ආපසු යැවිය හැකි ප්‍රමාණය ඉක්මවයි.',
            'created' => 'ආපසු යැවීමේ ඉල්ලීම සාර්ථකව සාදන ලදී.',
            'status-updated' => 'ආපසු යැවීමේ තත්ත්වය සාර්ථකව යාවත්කාලීන කරන ලදී.',
            'invalid-status' => 'අවලංගු ආපසු යැවීමේ තත්ත්වය.',
            'reopened' => 'ආපසු යැවීමේ ඉල්ලීම සාර්ථකව නැවත විවෘත කරන ලදී.',
            'reopen-not-allowed' => 'මෙම ආපසු යැවීමේ ඉල්ලීම නැවත විවෘත කළ නොහැක.',
            'refund-failed' => 'මෙම ආපසු යැවීම සඳහා මුදල් ආපසු ගෙවීම සෑදිය නොහැකි විය.',
            'message-sent' => 'පණිවිඩය සාර්ථකව යවන ලදී.',
            'no-permission' => 'මෙම ක්‍රියාව සිදු කිරීමට ඔබට අවසර නැත.',
            'reason-not-found' => 'ආපසු යැවීමේ හේතුව හමු නොවිණි.',
            'reason-deleted' => 'ආපසු යැවීමේ හේතුව සාර්ථකව මකා දමන ලදී.',
            'status-not-found' => 'ආපසු යැවීමේ තත්ත්වය හමු නොවිණි.',
            'status-deleted' => 'ආපසු යැවීමේ තත්ත්වය සාර්ථකව මකා දමන ලදී.',
            'status-default-undeletable' => 'පෙරනිමි ආපසු යැවීමේ තත්ත්වයක් මකා දැමිය නොහැක.',
            'rule-not-found' => 'ආපසු යැවීමේ නීතිය හමු නොවිණි.',
            'rule-deleted' => 'ආපසු යැවීමේ නීතිය සාර්ථකව මකා දමන ලදී.',
            'custom-field-not-found' => 'ආපසු යැවීමේ අභිරුචි ක්ෂේත්‍රය හමු නොවිණි.',
            'custom-field-deleted' => 'ආපසු යැවීමේ අභිරුචි ක්ෂේත්‍රය සාර්ථකව මකා දමන ලදී.',
            'custom-field-options-required' => 'මෙම ක්ෂේත්‍ර වර්ගය සඳහා අවම වශයෙන් එක් විකල්පයක් අවශ්‍ය වේ.',
            'mass-indices-required' => 'අවම වශයෙන් එක් id එකක් අවශ්‍ය වේ.',
            'mass-delete-success' => 'තෝරාගත් අයිතම මකා දමන ලදී.',
            'mass-update-success' => 'තෝරාගත් අයිතම යාවත්කාලීන කරන ලදී.',
            'mass-value-invalid' => 'තත්ත්ව අගය 0 හෝ 1 විය යුතුය.',
            'image-not-found' => 'ආපසු යැවීමේ රූපය හමු නොවිණි.',
            'attachment-not-found' => 'පණිවිඩ ඇමුණුම හමු නොවිණි.',
        ],

        'eu-withdrawal' => [
            'not-found' => 'ඉවත්වීමේ ප්‍රකාශය හමු නොවිණි.',
            'no-permission' => 'EU ඉවත්වීම් කළමනාකරණය කිරීමට ඔබට අවසර නැත.',
            'declined' => 'ඉවත්වීම ප්‍රතික්ෂේප කළ ලෙස සලකුණු කරන ලදී.',
            'refunded' => 'ඉවත්වීම මුදල් ආපසු ගෙවූ ලෙස සලකුණු කරන ලදී.',
            'confirmation-resent' => 'තහවුරු කිරීමේ ඊමේල් පණිවිඩය සාර්ථකව නැවත යවන ලදී.',
            'confirmation-failed' => 'තහවුරු කිරීමේ ඊමේල් පණිවිඩය යැවිය නොහැකි විය.',
        ],

        'reporting' => [
            'invalid-entity' => 'නොදන්නා වාර්තාකරණ ඒකකය.',
            'invalid-type' => 'මෙම ඒකකය සඳහා නොදන්නා වාර්තාකරණ සංඛ්‍යාලේඛන වර්ගය.',
            'export-format-unsupported' => 'සහාය දක්වන්නේ csv, xls සහ xlsx අපනයන ආකෘති පමණි.',
        ],

        'configuration' => [
            'unauthenticated' => 'ඔබ සත්‍යාපනය වී නොමැත.',
            'no-permission' => 'වින්‍යාසය යාවත්කාලීන කිරීමට ඔබට අවසර නැත.',
            'slug-required' => '`slug` විමසුම් පරාමිතිය අවශ්‍ය වේ.',
            'slug-not-found' => 'ලබා දී ඇති slug සඳහා කිසිදු වින්‍යාස කොටසක් ලියාපදිංචි කර නොමැත.',
            'values-required' => '`values` යටතේ අවම වශයෙන් එක් අගයක් අවශ්‍ය වේ.',
            'scope-escape' => '`:key` යතුර ඉල්ලූ slug උප ගසෙන් පිටත ඇත.',
            'unknown-field' => '`:key` යතුර ලියාපදිංචි වින්‍යාස ක්ෂේත්‍රයක් නොවේ.',
            'validation-failed' => 'වින්‍යාස අගයන් එකක් හෝ කිහිපයක් වලංගුකරණයෙන් අසමත් විය.',
            'file-upload-rest-only' => '`:field` ක්ෂේත්‍රය සඳහා ද්විමය ගොනු උඩුගත කිරීම් සහාය දක්වන්නේ REST multipart අන්ත ලක්ෂ්‍යය හරහා පමණි, GraphQL හරහා නොවේ.',
            'custom-view-readonly' => '`:field` ක්ෂේත්‍රය blade මගින් විදැහුම් කරන අභිරුචි ක්ෂේත්‍රයක් වන අතර API හරහා යාවත්කාලීන කළ නොහැක.',
            'update-success' => 'වින්‍යාසය සාර්ථකව යාවත්කාලීන කරන ලදී.',
        ],

        'customer' => [
            'not-found' => 'පාරිභෝගිකයා හමු නොවිණි.',
            'no-permission' => 'මෙම ක්‍රියාව සිදු කිරීමට ඔබට අවසර නැත.',
            'has-active-orders' => 'පාරිභෝගිකයාට පොරොත්තු හෝ සැකසෙමින් පවතින ඇණවුම් ඇති බැවින් මකා දැමිය නොහැක.',
            'deleted' => 'පාරිභෝගිකයා සාර්ථකව මකා දමන ලදී.',
            'mass-delete-indices-required' => 'අවම වශයෙන් එක් පාරිභෝගික id එකක් අවශ්‍ය වේ.',
            'mass-delete-success' => 'පාරිභෝගිකයින් මකා දමන ලදී.',
            'mass-update-indices-required' => 'අවම වශයෙන් එක් පාරිභෝගික id එකක් අවශ්‍ය වේ.',
            'mass-update-value-invalid' => 'තත්ත්ව අගය 0 හෝ 1 විය යුතුය.',
            'mass-update-success' => 'පාරිභෝගික තත්ත්වය යාවත්කාලීන කරන ලදී.',

            'address' => [
                'not-found' => 'ලිපිනය හමු නොවිණි.',
                'not-owned' => 'මෙම ලිපිනය නිශ්චිත පාරිභෝගිකයාට අයත් නොවේ.',
                'customer-id-required' => 'ලිපිනයේ හිමිකරු හඳුනා ගැනීමට customerId අවශ්‍ය වේ.',
                'deleted' => 'ලිපිනය සාර්ථකව මකා දමන ලදී.',
            ],

            'note' => [
                'empty' => 'සටහන් පෙළ අවශ්‍ය වේ.',
                'created' => 'සටහන සාර්ථකව එක් කරන ලදී.',
            ],

            'impersonate' => [
                'success' => 'අනුකරණ ටෝකනය නිකුත් කරන ලදී.',
            ],

            'review' => [
                'not-found' => 'සමාලෝචනය හමු නොවිණි.',
                'no-permission' => 'මෙම ක්‍රියාව සිදු කිරීමට ඔබට අවසර නැත.',
                'invalid-status' => 'තත්ත්වය මේවායින් එකක් විය යුතුය: pending, approved, disapproved.',
                'updated' => 'සමාලෝචනය සාර්ථකව යාවත්කාලීන කරන ලදී.',
                'deleted' => 'සමාලෝචනය සාර්ථකව මකා දමන ලදී.',
                'mass-delete-indices-required' => 'අවම වශයෙන් එක් සමාලෝචන id එකක් අවශ්‍ය වේ.',
                'mass-delete-success' => 'සමාලෝචන මකා දමන ලදී.',
                'mass-update-indices-required' => 'අවම වශයෙන් එක් සමාලෝචන id එකක් අවශ්‍ය වේ.',
                'mass-update-value-invalid' => 'තත්ත්ව අගය මේවායින් එකක් විය යුතුය: pending, approved, disapproved.',
                'mass-update-success' => 'සමාලෝචන තත්ත්වය යාවත්කාලීන කරන ලදී.',
            ],

            'group' => [
                'not-found' => 'පාරිභෝගික කණ්ඩායම හමු නොවිණි.',
                'deleted' => 'පාරිභෝගික කණ්ඩායම සාර්ථකව මකා දමන ලදී.',
                'is-system' => 'පද්ධති පාරිභෝගික කණ්ඩායම් මකා දැමිය නොහැක.',
                'has-customers' => 'පාරිභෝගික කණ්ඩායමට පාරිභෝගිකයින් සම්බන්ධ කර ඇති බැවින් මකා දැමිය නොහැක.',
                'system-code-immutable' => 'පද්ධති පාරිභෝගික කණ්ඩායමක කේතය වෙනස් කළ නොහැක.',
                'system-flag-immutable' => 'පද්ධති පාරිභෝගික කණ්ඩායමක is_user_defined ධජය වෙනස් කළ නොහැක.',
                'mass-delete-indices-required' => 'අවම වශයෙන් එක් පාරිභෝගික කණ්ඩායම් id එකක් අවශ්‍ය වේ.',
                'mass-delete-success' => 'පාරිභෝගික කණ්ඩායම් මකා දමන ලදී.',
            ],

            'gdpr' => [
                'not-found' => 'GDPR ඉල්ලීම හමු නොවිණි.',
                'no-permission' => 'මෙම ක්‍රියාව සිදු කිරීමට ඔබට අවසර නැත.',
                'deleted' => 'GDPR ඉල්ලීම සාර්ථකව මකා දමන ලදී.',
                'invalid-status' => 'තත්ත්වය මේවායින් එකක් විය යුතුය: pending, processing, declined, approved, revoked.',
                'no-changes' => 'තත්ත්වය හෝ පණිවිඩය යන දෙකින් අවම වශයෙන් එකක් අවශ්‍ය වේ.',
                'already-processed' => 'මෙම GDPR ඉල්ලීම දැනටමත් සකසා ඇති බැවින් නැවත ධාවනය කළ නොහැක.',

                'process' => [
                    'approved' => 'GDPR ඉල්ලීම අනුමත කරන ලදී.',
                    'deleted' => 'GDPR ඉල්ලීම අනුමත කර පාරිභෝගිකයා මකා දමන ලදී.',
                ],
            ],
        ],

        'order' => [
            'not-found' => 'ඇණවුම හමු නොවිණි.',

            'reorder' => [
                'success' => 'නැවත ඇණවුම් කිරීම සාර්ථකයි. නව කෙටුම්පත් කරත්තයක් සාදා ඇත.',
                'cannot-reorder' => 'මෙම ඇණවුම නැවත ඇණවුම් කළ නොහැක (අමුත්තාගේ ඇණවුමක් හෝ අයිතම එකක් හෝ කිහිපයක් තවදුරටත් විකිණිය නොහැක).',
                'failed' => 'නැවත ඇණවුම් කිරීම අසමත් විය. කරුණාකර නැවත උත්සාහ කරන්න.',
                'guest-not-supported' => 'අමුත්තන්ගේ ඇණවුම් සඳහා නැවත ඇණවුම් කිරීමට සහාය නොදක්වයි.',
                'items-not-saleable' => 'මෙම ඇණවුමේ අයිතම එකක් හෝ කිහිපයක් තවදුරටත් මිලදී ගැනීමට ලබා ගත නොහැක.',
                'no-permission' => 'ඇණවුම් සෑදීමට ඔබට අවසර නැත.',
                'disabled-in-settings' => 'පරිපාලක විසින් නැවත ඇණවුම් කිරීම වෙළඳසැල් සැකසුම්වල අබල කර ඇත.',
            ],

            'actions' => [
                'cancel' => [
                    'closed' => 'වසා දැමූ ඇණවුම් අවලංගු කළ නොහැක.',
                    'fraud' => 'වංචනික ඇණවුම් අවලංගු කළ නොහැක.',
                    'nothing-to-cancel' => 'මෙම ඇණවුමේ අවලංගු කිරීමට කිසිවක් නැත.',
                    'already-processed' => 'මෙම ඇණවුම දැනටමත් ඉන්වොයිස් කර හෝ නැව්ගත කර ඇති බැවින් තවදුරටත් අවලංගු කළ නොහැක. ඒ වෙනුවට ඔබට මුදල් ආපසු ගෙවීමක් නිකුත් කළ හැක.',
                    'no-permission' => 'ඇණවුම් අවලංගු කිරීමට ඔබට අවසර නැත.',
                    'success' => 'ඇණවුම සාර්ථකව අවලංගු කරන ලදී.',
                    'failed' => 'ඇණවුම අවලංගු කළ නොහැකි විය.',
                ],

                'comment' => [
                    'empty' => 'අදහස අවශ්‍ය වේ.',
                    'success' => 'අදහස ඇණවුමට එක් කරන ලදී.',
                    'failed' => 'අදහස එක් කළ නොහැකි විය.',
                ],

                'invoice' => [
                    'closed' => 'වසා දැමූ ඇණවුම් ඉන්වොයිස් කළ නොහැක.',
                    'fraud' => 'වංචනික ඇණවුම් ඉන්වොයිස් කළ නොහැක.',
                    'nothing-to-invoice' => 'මෙම ඇණවුමේ ඉන්වොයිස් කිරීමට කිසිවක් නැත.',
                    'already-invoiced' => 'මෙම ඇණවුම සඳහා දැනටමත් ඉන්වොයිසියක් ජනනය කර ඇත.',
                    'paypal-standard-blocked' => 'PayPal Standard හරහා ගෙවූ ඇණවුම් සඳහා ඉන්වොයිස් සෑදිය නොහැක.',
                    'no-permission' => 'ඉන්වොයිස් සෑදීමට ඔබට අවසර නැත.',
                    'items-required' => 'ධන ප්‍රමාණයක් සහිත අවම වශයෙන් එක් අයිතමයක් අවශ්‍ය වේ.',
                    'qty-exceeds' => 'SKU :sku සඳහා ඉල්ලූ ප්‍රමාණය (:requested) ලබා ගත හැකි ප්‍රමාණය (:available) ඉක්මවයි.',
                    'not-found' => 'ඉන්වොයිසිය හමු නොවිණි.',
                    'success' => 'ඉන්වොයිසිය සාර්ථකව සාදන ලදී.',
                    'failed' => 'ඉන්වොයිසිය සෑදිය නොහැකි විය.',
                    'pdf-failed' => 'ඉන්වොයිස් PDF ජනනය කළ නොහැකි විය.',
                    'email-invalid' => 'වලංගු ලබන්නාගේ ඊමේල් ලිපිනයක් අවශ්‍ය වේ.',
                    'duplicate-sent' => 'ඉන්වොයිස් ඊමේල් පණිවිඩය :email වෙත යවන ලදී.',
                    'mass-status-indices-required' => 'අවම වශයෙන් එක් ඉන්වොයිස් id එකක් අවශ්‍ය වේ.',
                    'mass-status-value-invalid' => 'තත්ත්වය මේවායින් එකක් විය යුතුය: pending, paid, overdue.',
                    'mass-status-success' => 'ඉන්වොයිස් තත්ත්වය සාර්ථකව යාවත්කාලීන කරන ලදී.',
                ],

                'shipment' => [
                    'closed' => 'වසා දැමූ ඇණවුම් නැව්ගත කළ නොහැක.',
                    'fraud' => 'වංචනික ඇණවුම් නැව්ගත කළ නොහැක.',
                    'nothing-to-ship' => 'මෙම ඇණවුමේ නැව්ගත කිරීමට කිසිවක් නැත.',
                    'no-permission' => 'ඇණවුම් නැව්ගත කිරීමට ඔබට අවසර නැත.',
                    'source-required' => 'තොග මූලාශ්‍රය අවශ්‍ය වේ.',
                    'items-required' => 'ධන ප්‍රමාණයක් සහිත අවම වශයෙන් එක් අයිතමයක් අවශ්‍ය වේ.',
                    'qty-exceeds' => 'SKU :sku සඳහා ඉල්ලූ ප්‍රමාණය (:requested) ලබා ගත හැකි ප්‍රමාණය (:available) ඉක්මවයි.',
                    'inventory-insufficient' => 'SKU :sku සඳහා තෝරාගත් මූලාශ්‍රයේ තොගය ප්‍රමාණවත් නොවේ.',
                    'not-found' => 'නැව්ගත කිරීම හමු නොවිණි.',
                    'success' => 'නැව්ගත කිරීම සාර්ථකව සාදන ලදී.',
                    'failed' => 'නැව්ගත කිරීම සෑදිය නොහැකි විය.',
                ],

                'refund' => [
                    'closed' => 'වසා දැමූ ඇණවුම් සඳහා මුදල් ආපසු ගෙවිය නොහැක.',
                    'fraud' => 'වංචනික ඇණවුම් සඳහා මුදල් ආපසු ගෙවිය නොහැක.',
                    'nothing-to-refund' => 'මෙම ඇණවුමේ ආපසු ගෙවීමට තවත් කිසිවක් ඉතිරිව නැත.',
                    'no-permission' => 'මුදල් ආපසු ගෙවීම් සෑදීමට ඔබට අවසර නැත.',
                    'qty-exceeds' => 'SKU :sku සඳහා ඉල්ලූ ප්‍රමාණය (:requested) ලබා ගත හැකි ප්‍රමාණය (:available) ඉක්මවයි.',
                    'amount-zero' => 'ගණනය කළ ආපසු ගෙවීමේ මුදල ශුන්‍ය වේ. ප්‍රමාණය, නැව්ගත කිරීම හෝ ගැලපුම් අගයන් සකසන්න.',
                    'amount-exceeds-max' => 'ආපසු ගෙවීමේ මුදල (:amount) ආපසු ගෙවිය හැකි උපරිම මුදල (:max) ඉක්මවයි.',
                    'not-found' => 'මුදල් ආපසු ගෙවීම හමු නොවිණි.',
                    'success' => 'මුදල් ආපසු ගෙවීම සාර්ථකව සාදන ලදී.',
                    'failed' => 'මුදල් ආපසු ගෙවීම සෑදිය නොහැකි විය.',
                ],
            ],
        ],

        'appearance' => [
            'no-permission' => 'පෙනුම කළමනාකරණය කිරීමට ඔබට අවසර නැත.',

            'theme' => [
                'not-found' => 'තේමාව හමු නොවිණි.',
                'not-installed' => 'මෙම තේමාව ස්ථාපනය කර නොමැත.',
                'channel-ids-required' => 'අවම වශයෙන් එක් නාලිකාවක් අවශ්‍ය වේ.',
                'unknown-channel' => 'නාලිකා එකක් හෝ කිහිපයක් නොපවතී.',
                'activated' => 'තේමාව සාර්ථකව සක්‍රීය කරන ලදී.',
            ],

            'section' => [
                'not-found' => 'කොටස හමු නොවිණි.',
                'invalid-payload' => 'සහාය නොදක්වන කොටස් දත්ත.',
                'footer-links-exists' => 'මෙම නාලිකාවට දැනටමත් පාදක සබැඳි කොටසක් ඇත.',
                'options-required' => 'විකල්ප අවශ්‍ය වේ.',
                'status-required' => 'තත්ත්වය අවශ්‍ය වේ.',
                'section-ids-required' => 'අවම වශයෙන් එක් කොටසක් අවශ්‍ය වේ.',
                'reorder-single-scope' => 'කොටස් නැවත පෙළගැස්විය හැක්කේ වරකට එක් තේමාවක් සහ නාලිකාවක් තුළ පමණි.',
                'reorder-incomplete' => 'නැවත පෙළගැස්වීමට තේමාවේ සහ නාලිකාවේ සෑම කොටසක්ම, ඒවා විදැහුම් විය යුතු අනුපිළිවෙලට අවශ්‍ය වේ.',
                'unknown-section' => 'කොටස් එකක් හෝ කිහිපයක් නොපවතී.',
                'file-required' => 'ගොනුවක් අවශ්‍ය වේ.',
                'created' => 'කොටස සාර්ථකව සාදන ලදී.',
                'updated' => 'කොටස සාර්ථකව යාවත්කාලීන කරන ලදී.',
                'deleted' => 'කොටස සාර්ථකව මකා දමන ලදී.',
                'draft-saved' => 'කොටස් වෙනස්කම් සාර්ථකව සූදානම් කරන ලදී.',
                'status-staged' => 'කොටස් තත්ත්වය සාර්ථකව සූදානම් කරන ලදී.',
                'order-staged' => 'කොටස් අනුපිළිවෙල සාර්ථකව සූදානම් කරන ලදී.',
                'duplicated' => 'කොටස සාර්ථකව පිටපත් කරන ලදී.',
                'published' => 'කොටස් වෙනස්කම් සාර්ථකව ප්‍රකාශයට පත් කරන ලදී.',
                'discarded' => 'කොටස් වෙනස්කම් සාර්ථකව ඉවත දමන ලදී.',
                'media-uploaded' => 'මාධ්‍ය සාර්ථකව උඩුගත කරන ලදී.',
            ],
        ],

        'sales' => [
            'no-permission' => 'මෙම විකුණුම් සම්පත බැලීමට ඔබට අවසර නැත.',

            'invoice' => [
                'not-found' => 'ඉන්වොයිසිය හමු නොවිණි.',
                'no-permission' => 'ඉන්වොයිස් බැලීමට ඔබට අවසර නැත.',
            ],

            'shipment' => [
                'not-found' => 'නැව්ගත කිරීම හමු නොවිණි.',
                'no-permission' => 'නැව්ගත කිරීම් බැලීමට ඔබට අවසර නැත.',
            ],

            'refund' => [
                'not-found' => 'මුදල් ආපසු ගෙවීම හමු නොවිණි.',
                'no-permission' => 'මුදල් ආපසු ගෙවීම් බැලීමට ඔබට අවසර නැත.',
                'export-format-unsupported' => 'සහාය දක්වන්නේ csv, xls සහ xlsx අපනයන පමණි.',
            ],

            'transaction' => [
                'not-found' => 'ගනුදෙනුව හමු නොවිණි.',
                'no-permission' => 'ගනුදෙනු බැලීමට ඔබට අවසර නැත.',

                'create' => [
                    'invoice-required' => 'invoiceId ක්ෂේත්‍රය අවශ්‍ය වේ.',
                    'payment-method-required' => 'paymentMethod ක්ෂේත්‍රය අවශ්‍ය වේ.',
                    'amount-invalid' => 'amount ක්ෂේත්‍රය අවශ්‍ය වන අතර සංඛ්‍යාත්මක විය යුතුය.',
                    'invoice-missing' => 'නිශ්චිත ඉන්වොයිසිය සොයාගත නොහැකි විය.',
                    'already-paid' => 'මෙම ඉන්වොයිසිය දැනටමත් සම්පූර්ණයෙන් ගෙවා ඇත.',
                    'amount-exceeds' => 'ගෙවීම් මුදල ඉන්වොයිසියේ මුළු එකතුව ඉක්මවයි.',
                    'amount-zero' => 'ගෙවීම් මුදල ශුන්‍යයට වඩා වැඩි විය යුතුය.',
                ],
            ],

            'booking' => [
                'not-found' => 'වෙන්කිරීම හමු නොවිණි.',
                'no-permission' => 'වෙන්කිරීම් බැලීමට ඔබට අවසර නැත.',
            ],

            'export' => [
                'format-unsupported' => 'සහාය දක්වන්නේ csv, xls සහ xlsx අපනයන පමණි.',
            ],
        ],

        'cart' => [
            'not-found' => 'කරත්තය හමු නොවිණි.',
            'not-draft' => 'මෙම කරත්තය පරිපාලක API හරහා වෙනස් කළ නොහැක.',
            'product-required' => 'productId අවශ්‍ය වේ.',
            'product-not-found' => 'නිෂ්පාදනය හමු නොවිණි.',
            'booking-unsupported' => 'වෙන්කිරීම් නිෂ්පාදන පරිපාලක කෙටුම්පත් ඇණවුමකට එක් කළ නොහැක. වෙන්කිරීම් මිලදී ගැනීම් පාරිභෝගික storefront හරහා කළ යුතුය.',
            'product-not-saleable' => 'මෙම නිෂ්පාදනය විකිණීමට ලබා ගත නොහැක (තොගයේ නොමැත හෝ අබල කර ඇත) එබැවින් ඇණවුමට එක් කළ නොහැක.',
            'qty-required' => 'qty අවශ්‍ය වන අතර එය itemId => quantity ආකාරයේ හිස් නොවන වස්තුවක් විය යුතුය.',
            'cart-item-required' => 'cartItemId අවශ්‍ය වේ.',
            'item-added' => 'අයිතමය කරත්තයට එක් කරන ලදී.',
            'item-add-failed' => 'අයිතමය කරත්තයට එක් කළ නොහැකි විය.',
            'item-updated' => 'කරත්ත අයිතම යාවත්කාලීන කරන ලදී.',
            'item-update-failed' => 'කරත්ත අයිතම යාවත්කාලීන කළ නොහැකි විය.',
            'item-removed' => 'අයිතමය කරත්තයෙන් ඉවත් කරන ලදී.',
            'item-remove-failed' => 'කරත්ත අයිතමය ඉවත් කළ නොහැකි විය.',
            'address-saved' => 'ලිපිනය සුරකින ලදී.',
            'address-required' => 'බිල්පත් ලිපිනයක් අවශ්‍ය වේ.',
            'address-incomplete' => 'ලිපිනය සුරැකීමට :field අවශ්‍ය වේ.',
            'coupon-code-required' => 'කූපන් කේතයක් අවශ්‍ය වේ.',
            'coupon-applied' => 'කූපනය යොදන ලදී.',
            'coupon-already-applied' => 'කූපනය දැනටමත් මෙම කරත්තයට යොදා ඇත.',
            'coupon-not-found' => 'කූපනය හමු නොවිණි හෝ අක්‍රියයි.',
            'coupon-removed' => 'කූපනය ඉවත් කරන ලදී.',
            'coupon-error' => 'කූපනය යෙදීමේදී යම් දෝෂයක් ඇති විය.',
            'unknown-error' => 'යම් දෝෂයක් ඇති විය.',
            'draft-created' => 'කෙටුම්පත් කරත්තය සාදන ලදී.',
            'draft-failed' => 'කෙටුම්පත් කරත්තය සෑදිය නොහැකි විය.',
            'addresses-required-for-shipping' => 'නැව්ගත කිරීමේ ක්‍රමයක් තේරීමට පෙර ලිපින සුරැකිය යුතුය.',
            'shipping-required-for-payment' => 'ගෙවීම් ක්‍රමයට පෙර නැව්ගත කිරීමේ ක්‍රමය තෝරාගත යුතුය.',
            'shipping-method-required' => 'නැව්ගත කිරීමේ ක්‍රමය අවශ්‍ය වේ.',
            'shipping-method-saved' => 'නැව්ගත කිරීමේ ක්‍රමය සුරකින ලදී.',
            'shipping-method-failed' => 'නැව්ගත කිරීමේ ක්‍රමය සුරැකිය නොහැකි විය.',
            'payment-method-required' => 'ගෙවීම් ක්‍රමය අවශ්‍ය වේ.',
            'payment-method-saved' => 'ගෙවීම් ක්‍රමය සුරකින ලදී.',
            'payment-method-failed' => 'ගෙවීම් ක්‍රමය සුරැකිය නොහැකි විය.',

            'place-order' => [
                'success' => 'ඇණවුම සාර්ථකව තබන ලදී.',
                'failed' => 'ඇණවුම තැබිය නොහැකි විය.',
                'error' => 'ඇණවුම තැබිය නොහැකි විය: කරත්තයේ දෝෂ ඇත.',
                'minimum-order-error' => 'අවම ඇණවුම් මුදල :amount වේ. ඇණවුම තැබීමට පෙර තවත් අයිතම එක් කරන්න.',
                'empty-cart' => 'කරත්තය හිස්ය — ඇණවුමක් තැබීමට පෙර අයිතම එක් කරන්න.',
                'addresses-required' => 'ඇණවුම තැබීමට පෙර බිල්පත් සහ නැව්ගත කිරීමේ ලිපින සුරැකිය යුතුය.',
                'shipping-required' => 'ඇණවුම තැබීමට පෙර නැව්ගත කිරීමේ ක්‍රමයක් තෝරාගත යුතුය.',
                'payment-required' => 'ඇණවුම තැබීමට පෙර ගෙවීම් ක්‍රමයක් තෝරාගත යුතුය.',
                'payment-method-unsupported' => 'පරිපාලක විසින් තබන ඇණවුම් සඳහා සහාය දක්වන්නේ cashondelivery සහ moneytransfer පමණි.',
            ],
        ],

        'product' => [
            'not-found' => 'නිෂ්පාදනය හමු නොවිණි.',

            'mass-delete-success' => 'නිෂ්පාදන සාර්ථකව මකා දමන ලදී.',
            'mass-update-status-success' => 'නිෂ්පාදනවල තත්ත්වය සාර්ථකව යාවත්කාලීන කරන ලදී.',
            'indices-required' => 'indices ක්ෂේත්‍රය අවශ්‍ය වන අතර එය හිස් නොවන අරාවක් විය යුතුය.',
            'indices-invalid' => 'indices ක්ෂේත්‍රය පූර්ණ සංඛ්‍යා නිෂ්පාදන ID අරාවක් විය යුතුය.',
            'value-invalid' => 'value ක්ෂේත්‍රය අවශ්‍ය වන අතර එය 0 හෝ 1 විය යුතුය.',
            'mass-delete-failed' => 'නිෂ්පාදන එකක් හෝ කිහිපයක් මකා දැමිය නොහැකි විය.',
            'no-permission' => 'නිෂ්පාදන කළමනාකරණය කිරීමට ඔබට අවසර නැත.',

            'copy-success' => 'නිෂ්පාදනය සාර්ථකව පිටපත් කරන ලදී.',
            'copy-failed' => 'නිෂ්පාදනය පිටපත් කිරීමට අසමත් විය.',
            'copy-variant-not-supported' => 'වින්‍යාසගත කළ හැකි නිෂ්පාදනවල ප්‍රභේද පිටපත් කළ නොහැක. ඒ වෙනුවට මව් වින්‍යාසගත කළ හැකි නිෂ්පාදනය පිටපත් කරන්න.',

            'create' => [
                'created' => 'නිෂ්පාදනය සාර්ථකව සාදන ලදී.',
                'create-failed' => 'නිෂ්පාදනය සෑදීමට අසමත් විය.',
                'sku-required' => 'sku ක්ෂේත්‍රය අවශ්‍ය වේ.',
                'sku-unique' => 'sku දැනටමත් භාවිතයේ ඇත.',
                'sku-invalid' => 'sku හි අඩංගු විය හැක්කේ අකුරු, ඉලක්කම් සහ යටි ඉරි පමණි (උදා. "sp-001").',
                'attribute-family-required' => 'attribute_family_id ක්ෂේත්‍රය අවශ්‍ය වේ.',
                'attribute-family-not-found' => 'තෝරාගත් ගුණාංග පවුල නොපවතී.',
                'type-required' => 'type ක්ෂේත්‍රය අවශ්‍ය වේ.',
                'type-not-yet-supported' => 'නිෂ්පාදන වර්ගය ":type" මෙම API මගින් සහාය නොදක්වයි. අවසර ලත් වර්ග: simple, virtual, downloadable, grouped, bundle, configurable, booking.',
                'super-attributes-required' => 'type=configurable වන විට super_attributes ක්ෂේත්‍රය අවශ්‍ය වේ. ගුණාංග කේතය (හෝ id) සිට option_ids දක්වා සිතියමක් සපයන්න, උදා. { "color": [1,2], "size": [4,5] }.',
                'super-attributes-invalid' => 'super_attributes ක්ෂේත්‍රය ගුණාංග කේතය (හෝ id) සිට option_ids හි හිස් නොවන ලැයිස්තුවක් දක්වා වූ හිස් නොවන සිතියමක් විය යුතුය.',
                'no-permission' => 'නිෂ්පාදන සෑදීමට ඔබට අවසර නැත.',
            ],

            'update' => [
                'updated' => 'නිෂ්පාදනය සාර්ථකව යාවත්කාලීන කරන ලදී.',
                'update-failed' => 'නිෂ්පාදනය යාවත්කාලීන කිරීමට අසමත් විය.',
                'id-required' => 'නිෂ්පාදන id එකක් අවශ්‍ය වේ.',
                'url-key-required' => 'url_key ක්ෂේත්‍රය හිස් විය නොහැක.',
                'url-key-unique' => 'url_key දැනටමත් වෙනත් නිෂ්පාදනයක් විසින් භාවිතා කර ඇත.',
                'special-price-invalid' => 'special_price මිලට වඩා අඩු සංඛ්‍යාවක් විය යුතුය.',
                'special-price-date-range-invalid' => 'special_price_to, special_price_from දිනයේ හෝ ඉන් පසුව විය යුතුය.',
                'boolean-field-invalid' => ':field ක්ෂේත්‍රය 0 හෝ 1 විය යුතුය.',
                'categories-invalid' => 'categories ක්ෂේත්‍රය ප්‍රවර්ග id අරාවක් විය යුතුය.',
                'channels-invalid' => 'channels ක්ෂේත්‍රය නාලිකා id අරාවක් විය යුතුය.',
                'super-attributes-cannot-change' => 'නිෂ්පාදනය සෑදූ පසු super_attributes වෙනස් කළ නොහැක.',
                'sub-resource-stripped-images' => 'රූප POST /api/admin/catalog/products/{id}/images හරහා කළමනාකරණය කළ යුතුය. images ක්ෂේත්‍රය නොසලකා හරින ලදී.',
                'sub-resource-stripped-inventories' => 'තොග PUT /api/admin/catalog/products/{id}/inventories හරහා කළමනාකරණය කළ යුතුය. inventories ක්ෂේත්‍රය නොසලකා හරින ලදී.',
                'sub-resource-stripped-customer-group-prices' => 'පාරිභෝගික කණ්ඩායම් මිල /api/admin/catalog/products/{id}/customer-group-prices හරහා කළමනාකරණය කළ යුතුය. customer_group_prices ක්ෂේත්‍රය නොසලකා හරින ලදී.',
                'translations-single-locale' => 'යාවත්කාලීන කළේ ඉල්ලූ භාෂා ප්‍රදේශය පමණි. :locales භාෂා ප්‍රදේශ යාවත්කාලීන කිරීමට, එක් එක් සඳහා ?locale=<code> සමඟ වෙනම ඉල්ලීමක් යවන්න.',
            ],

            'delete' => [
                'deleted' => 'නිෂ්පාදනය සාර්ථකව මකා දමන ලදී.',
                'delete-failed' => 'නිෂ්පාදනය මකා දැමීමට අසමත් විය.',
            ],

            'image' => [
                'uploaded' => 'නිෂ්පාදන රූපය සාර්ථකව උඩුගත කරන ලදී.',
                'updated' => 'නිෂ්පාදන රූපය සාර්ථකව යාවත්කාලීන කරන ලදී.',
                'update-empty' => 'යාවත්කාලීන කිරීමට alt පෙළක් හෝ ස්ථානයක් යවන්න.',
                'reordered' => 'නිෂ්පාදන රූප සාර්ථකව නැවත පෙළගස්වන ලදී.',
                'deleted' => 'නිෂ්පාදන රූපය සාර්ථකව මකා දමන ලදී.',
                'image-required' => 'රූප ගොනුවක් අවශ්‍ය වේ (multipart ක්ෂේත්‍රය "image").',
                'image-invalid-type' => 'රූප වර්ගය මේවායින් එකක් විය යුතුය: bmp, jpeg, jpg, png, webp.',
                'image-too-large' => 'රූපය ඉතා විශාලය. අවසර ලත් උපරිම ප්‍රමාණය 4 MB වේ.',
                'not-found' => 'නිෂ්පාදන රූපය හමු නොවිණි.',
                'no-permission' => 'නිෂ්පාදන රූප කළමනාකරණය කිරීමට ඔබට අවසර නැත.',
                'upload-failed' => 'නිෂ්පාදන රූපය උඩුගත කිරීමට අසමත් විය.',
                'reorder-failed' => 'නිෂ්පාදන රූප නැවත පෙළගැස්වීමට අසමත් විය.',
                'delete-failed' => 'නිෂ්පාදන රූපය මකා දැමීමට අසමත් විය.',
                'order-required' => '"order" අරාව අවශ්‍ය වන අතර හිස් නොවිය යුතුය.',
                'order-invalid' => '"order" හි සෑම ඇතුළත් කිරීමක්ම id සහ position සහිත වස්තුවක් විය යුතුය.',
                'id-not-on-product' => 'රූපය :id මෙම නිෂ්පාදනයට අයත් නොවේ.',
                'graphql-upload-unsupported' => 'GraphQL හරහා රූප උඩුගත කිරීමට සහාය නොදක්වයි. multipart/form-data සමඟ POST /api/admin/catalog/products/{productId}/images භාවිතා කරන්න.',
            ],

            'downloadable' => [
                'file-required' => 'ගොනුවක් අවශ්‍ය වේ (multipart ක්ෂේත්‍රය "file").',
                'file-too-large' => 'ගොනුව ඉතා විශාලය. අවසර ලත් උපරිම ප්‍රමාණය 128 MB වේ.',
                'upload-failed' => 'බාගත කළ හැකි ගොනුව උඩුගත කිරීමට අසමත් විය.',
                'file-not-found' => 'මෙම නිෂ්පාදනය සහ ගුණාංගය සඳහා ගබඩා කළ ගොනුවක් හමු නොවිණි.',
                'no-permission' => 'බාගත කළ හැකි නිෂ්පාදන ගොනු කළමනාකරණය කිරීමට ඔබට අවසර නැත.',
            ],

            'video' => [
                'uploaded' => 'නිෂ්පාදන වීඩියෝව සාර්ථකව උඩුගත කරන ලදී.',
                'deleted' => 'නිෂ්පාදන වීඩියෝව සාර්ථකව මකා දමන ලදී.',
                'video-required' => 'වීඩියෝ ගොනුවක් අවශ්‍ය වේ (multipart ක්ෂේත්‍රය "video").',
                'video-invalid-type' => 'වීඩියෝ වර්ගය මේවායින් එකක් විය යුතුය: mp4, webm, ogg, mov.',
                'video-too-large' => 'වීඩියෝව ඉතා විශාලය. අවසර ලත් උපරිම ප්‍රමාණය 100 MB වේ.',
                'not-found' => 'නිෂ්පාදන වීඩියෝව හමු නොවිණි.',
                'no-permission' => 'නිෂ්පාදන වීඩියෝ කළමනාකරණය කිරීමට ඔබට අවසර නැත.',
                'upload-failed' => 'නිෂ්පාදන වීඩියෝව උඩුගත කිරීමට අසමත් විය.',
                'delete-failed' => 'නිෂ්පාදන වීඩියෝව මකා දැමීමට අසමත් විය.',
                'graphql-upload-unsupported' => 'GraphQL හරහා වීඩියෝ උඩුගත කිරීමට සහාය නොදක්වයි. multipart/form-data සමඟ POST /api/admin/catalog/products/{productId}/videos භාවිතා කරන්න.',
            ],

            'inventory' => [
                'updated' => 'නිෂ්පාදන තොග සාර්ථකව සුරකින ලදී.',
                'inventories-required' => 'inventories ක්ෂේත්‍රය අවශ්‍ය වන අතර එය inventory_source_id සිට ප්‍රමාණය දක්වා වූ හිස් නොවන සිතියමක් විය යුතුය.',
                'inventories-invalid' => 'inventories ක්ෂේත්‍රය inventory_source_id (ධන පූර්ණ සංඛ්‍යාව) සිට ප්‍රමාණය (සෘණ නොවන පූර්ණ සංඛ්‍යාව) දක්වා වූ සිතියමක් විය යුතුය.',
                'source-not-found' => 'තොග මූලාශ්‍රය :id නොපවතී.',
                'qty-invalid' => 'සෑම ප්‍රමාණයක්ම සෘණ නොවන පූර්ණ සංඛ්‍යාවක් විය යුතුය.',
                'no-permission' => 'නිෂ්පාදන තොග කළමනාකරණය කිරීමට ඔබට අවසර නැත.',
                'not-found' => 'නිෂ්පාදනය හමු නොවිණි.',
            ],

            'customer-group-price' => [
                'created' => 'පාරිභෝගික කණ්ඩායම් මිල සාර්ථකව එක් කරන ලදී.',
                'updated' => 'පාරිභෝගික කණ්ඩායම් මිල සාර්ථකව යාවත්කාලීන කරන ලදී.',
                'deleted' => 'පාරිභෝගික කණ්ඩායම් මිල සාර්ථකව මකා දමන ලදී.',
                'qty-required' => 'qty ක්ෂේත්‍රය අවශ්‍ය වේ.',
                'qty-invalid' => 'qty ක්ෂේත්‍රය 1 ට වඩා වැඩි හෝ සමාන පූර්ණ සංඛ්‍යාවක් විය යුතුය.',
                'value-type-required' => 'value_type ක්ෂේත්‍රය අවශ්‍ය වේ.',
                'value-type-invalid' => 'value_type ක්ෂේත්‍රය "fixed" හෝ "discount" විය යුතුය.',
                'value-required' => 'value ක්ෂේත්‍රය අවශ්‍ය වේ.',
                'value-invalid' => 'value ක්ෂේත්‍රය සෘණ නොවන සංඛ්‍යාවක් විය යුතුය.',
                'customer-group-not-found' => 'තෝරාගත් පාරිභෝගික කණ්ඩායම නොපවතී.',
                'duplicate-qty-group' => 'මෙම (qty, customer_group_id) සංයෝජනය සඳහා පාරිභෝගික කණ්ඩායම් මිලක් දැනටමත් මෙම නිෂ්පාදනයට පවතී.',
                'not-found' => 'පාරිභෝගික කණ්ඩායම් මිල හමු නොවිණි.',
                'no-permission' => 'නිෂ්පාදන පාරිභෝගික කණ්ඩායම් මිල කළමනාකරණය කිරීමට ඔබට අවසර නැත.',
            ],
        ],

        'category' => [
            'not-found' => 'ප්‍රවර්ගය හමු නොවිණි.',

            'created' => 'ප්‍රවර්ගය සාර්ථකව සාදන ලදී.',
            'updated' => 'ප්‍රවර්ගය සාර්ථකව යාවත්කාලීන කරන ලදී.',
            'deleted' => 'ප්‍රවර්ගය සාර්ථකව මකා දමන ලදී.',
            'slug-required' => 'slug ක්ෂේත්‍රය අවශ්‍ය වේ.',
            'slug-unique' => 'slug දැනටමත් භාවිතයේ ඇත.',
            'name-required' => 'name ක්ෂේත්‍රය අවශ්‍ය වේ.',
            'position-required' => 'position ක්ෂේත්‍රය අවශ්‍ය වේ.',
            'attributes-required' => 'අවම වශයෙන් එක් පෙරහන් කළ හැකි ගුණාංගයක් අවශ්‍ය වේ.',
            'description-required-for-mode' => 'තෝරාගත් සංදර්ශක ප්‍රකාරය සඳහා විස්තරයක් අවශ්‍ය වේ.',
            'cannot-delete-root' => 'මූල සහ නාලිකා-මූල ප්‍රවර්ග මකා දැමිය නොහැක.',
            'cannot-delete-channel-root' => 'මෙම ප්‍රවර්ගය නාලිකා මූලයක් වන බැවින් මකා දැමිය නොහැක.',
            'delete-failed' => 'ප්‍රවර්ගය මකා දැමිය නොහැකි විය.',
            'no-permission' => 'ප්‍රවර්ග කළමනාකරණය කිරීමට ඔබට අවසර නැත.',
            'mass-delete-success' => 'ප්‍රවර්ග සාර්ථකව මකා දමන ලදී.',
            'mass-update-status-success' => 'ප්‍රවර්ගවල තත්ත්වය සාර්ථකව යාවත්කාලීන කරන ලදී.',
            'mass-delete-indices-required' => 'indices ක්ෂේත්‍රය අවශ්‍ය වන අතර එය හිස් නොවන අරාවක් විය යුතුය.',
            'mass-update-status-value-required' => 'value ක්ෂේත්‍රය අවශ්‍ය වන අතර එය 0 හෝ 1 විය යුතුය.',
        ],

        'attribute' => [
            'not-found' => 'ගුණාංගය හමු නොවිණි.',

            'create-success' => 'ගුණාංගය සාර්ථකව සාදන ලදී.',
            'update-success' => 'ගුණාංගය සාර්ථකව යාවත්කාලීන කරන ලදී.',
            'delete-success' => 'ගුණාංගය සාර්ථකව මකා දමන ලදී.',
            'delete-failed' => 'ගුණාංගය මකා දැමිය නොහැකි විය.',
            'mass-delete-success' => 'ගුණාංග සාර්ථකව මකා දමන ලදී.',
            'mass-delete-indices-required' => 'indices ක්ෂේත්‍රය අවශ්‍ය වන අතර එය හිස් නොවන අරාවක් විය යුතුය.',

            'code-immutable' => 'ගුණාංගය සෑදූ පසු ගුණාංග කේතය වෙනස් කළ නොහැක.',
            'type-immutable' => 'ගුණාංගයට නිෂ්පාදන අගයන් ඇති අතරතුර ගුණාංග වර්ගය වෙනස් කළ නොහැක.',
            'locale-scope-immutable' => 'ගුණාංගයට නිෂ්පාදන අගයන් ඇති අතරතුර භාෂා ප්‍රදේශ විෂය පථය වෙනස් කළ නොහැක.',
            'system-attribute' => 'පද්ධති ගුණාංග මකා දැමිය නොහැක.',
            'in-use-family' => 'ගුණාංගය ගුණාංග පවුල් එකක හෝ කිහිපයක කොටසකි (කණ්ඩායම් ID: :ids). පළමුව එය එම පවුල්වලින් ඉවත් කරන්න.',

            'option-not-found' => 'ගුණාංග විකල්පය හමු නොවිණි.',
            'option-not-supported' => 'ගුණාංග වර්ගය ":type" විකල්පවලට සහාය නොදක්වයි. විකල්ප තිබිය හැක්කේ select, multiselect සහ checkbox ගුණාංගවලට පමණි.',
            'option-in-use' => 'මෙම විකල්පය නිෂ්පාදන :count ක් විසින් භාවිතා කරන බැවින් මකා දැමිය නොහැක.',
            'option-delete-success' => 'ගුණාංග විකල්පය සාර්ථකව මකා දමන ලදී.',
        ],

        'family' => [
            'not-found' => 'ගුණාංග පවුල හමු නොවිණි.',

            'created' => 'ගුණාංග පවුල සාර්ථකව සාදන ලදී.',
            'updated' => 'ගුණාංග පවුල සාර්ථකව යාවත්කාලීන කරන ලදී.',
            'deleted' => 'ගුණාංග පවුල සාර්ථකව මකා දමන ලදී.',
            'code-required' => 'පවුල් කේතය අවශ්‍ය වේ.',
            'code-unique' => 'පවුල් කේතය අද්විතීය විය යුතුය.',
            'name-required' => 'පවුලේ නම අවශ්‍ය වේ.',
            'group-code-required' => 'සෑම ගුණාංග කණ්ඩායමකටම කේතයක් තිබිය යුතුය.',
            'group-name-required' => 'සෑම ගුණාංග කණ්ඩායමකටම නමක් තිබිය යුතුය.',
            'group-column-invalid' => 'සෑම ගුණාංග කණ්ඩායම් තීරුවක්ම 1 හෝ 2 විය යුතුය.',
            'group-name-duplicate' => 'ගුණාංග කණ්ඩායමක නම ගුණාංග පවුල තුළ අද්විතීය විය යුතුය.',
            'update-failed' => 'ගුණාංග පවුල යාවත්කාලීන කළ නොහැකි විය.',
            'create-failed' => 'ගුණාංග පවුල සෑදිය නොහැකි විය.',
            'default-delete-error' => 'පෙරනිමි ගුණාංග පවුල මකා දැමිය නොහැක.',
            'last-delete-error' => 'අවම වශයෙන් එක් ගුණාංග පවුලක් අවශ්‍ය වේ.',
            'attribute-product-error' => 'මෙම ගුණාංග පවුල නිෂ්පාදන එකක් හෝ කිහිපයක් විසින් භාවිතා කරයි. එය මකා දැමීමට පෙර එම නිෂ්පාදන නැවත පවරන්න.',
            'delete-failed' => 'ගුණාංග පවුල මකා දැමිය නොහැකි විය.',
            'no-permission' => 'ගුණාංග පවුල් කළමනාකරණය කිරීමට ඔබට අවසර නැත.',
        ],

        'settings' => [
            'locale' => [
                'not-found' => 'භාෂා ප්‍රදේශය හමු නොවිණි.',
                'created' => 'භාෂා ප්‍රදේශය සාර්ථකව සාදන ලදී.',
                'updated' => 'භාෂා ප්‍රදේශය සාර්ථකව යාවත්කාලීන කරන ලදී.',
                'deleted' => 'භාෂා ප්‍රදේශය සාර්ථකව මකා දමන ලදී.',
                'cannot-delete-last' => 'අවම වශයෙන් එක් භාෂා ප්‍රදේශයක් අවශ්‍ය වේ — ඉතිරිව ඇති අවසාන භාෂා ප්‍රදේශය මකා දැමිය නොහැක.',
                'cannot-delete-channel-default' => 'මෙම භාෂා ප්‍රදේශය නාලිකා එකක හෝ කිහිපයක පෙරනිමි භාෂා ප්‍රදේශය වන බැවින් මකා දැමිය නොහැක.',
                'delete-failed' => 'භාෂා ප්‍රදේශය මකා දැමිය නොහැකි විය.',
                'no-permission' => 'භාෂා ප්‍රදේශ කළමනාකරණය කිරීමට ඔබට අවසර නැත.',
                'mass-delete-success' => 'භාෂා ප්‍රදේශ සාර්ථකව මකා දමන ලදී.',
                'mass-delete-indices-required' => 'indices ක්ෂේත්‍රය අවශ්‍ය වන අතර එය හිස් නොවන අරාවක් විය යුතුය.',
            ],

            'theme' => [
                'not-found' => 'තේමා අභිරුචිකරණය හමු නොවිණි.',
                'created' => 'තේමා අභිරුචිකරණය සාර්ථකව සාදන ලදී.',
                'updated' => 'තේමා අභිරුචිකරණය සාර්ථකව යාවත්කාලීන කරන ලදී.',
                'deleted' => 'තේමා අභිරුචිකරණය සාර්ථකව මකා දමන ලදී.',
                'delete-failed' => 'තේමා අභිරුචිකරණය මකා දැමිය නොහැකි විය.',
                'no-permission' => 'තේමා අභිරුචිකරණ කළමනාකරණය කිරීමට ඔබට අවසර නැත.',
                'mass-delete-success' => 'තේමා අභිරුචිකරණ සාර්ථකව මකා දමන ලදී.',
                'mass-delete-indices-required' => 'indices ක්ෂේත්‍රය අවශ්‍ය වන අතර එය හිස් නොවන අරාවක් විය යුතුය.',
                'mass-update-success' => 'තේමා අභිරුචිකරණ සාර්ථකව යාවත්කාලීන කරන ලදී.',
                'mass-update-indices-required' => 'indices ක්ෂේත්‍රය අවශ්‍ය වන අතර එය හිස් නොවන අරාවක් විය යුතුය.',
                'mass-update-value-invalid' => 'value ක්ෂේත්‍රය 0 හෝ 1 විය යුතුය.',
            ],

            'inventory-source' => [
                'not-found' => 'තොග මූලාශ්‍රය හමු නොවිණි.',
                'created' => 'තොග මූලාශ්‍රය සාර්ථකව සාදන ලදී.',
                'updated' => 'තොග මූලාශ්‍රය සාර්ථකව යාවත්කාලීන කරන ලදී.',
                'deleted' => 'තොග මූලාශ්‍රය සාර්ථකව මකා දමන ලදී.',
                'code-unique' => 'මෙම කේතය සහිත තොග මූලාශ්‍රයක් දැනටමත් පවතී.',
                'last-delete-error' => 'අවම වශයෙන් එක් තොග මූලාශ්‍රයක් අවශ්‍ය වේ — ඉතිරිව ඇති අවසාන මූලාශ්‍රය මකා දැමිය නොහැක.',
                'in-use' => 'මෙම තොග මූලාශ්‍රය නිෂ්පාදන තොග මගින් යොමු කර ඇති බැවින් මකා දැමිය නොහැක.',
                'delete-failed' => 'තොග මූලාශ්‍රය මකා දැමිය නොහැකි විය.',
                'no-permission' => 'තොග මූලාශ්‍ර කළමනාකරණය කිරීමට ඔබට අවසර නැත.',
                'mass-delete-success' => 'තොග මූලාශ්‍ර සාර්ථකව මකා දමන ලදී.',
                'mass-delete-indices-required' => 'indices ක්ෂේත්‍රය අවශ්‍ය වන අතර එය හිස් නොවන අරාවක් විය යුතුය.',
            ],

            'exchange-rate' => [
                'not-found' => 'විනිමය අනුපාතය හමු නොවිණි.',
                'created' => 'විනිමය අනුපාතය සාර්ථකව සාදන ලදී.',
                'updated' => 'විනිමය අනුපාතය සාර්ථකව යාවත්කාලීන කරන ලදී.',
                'deleted' => 'විනිමය අනුපාතය සාර්ථකව මකා දමන ලදී.',
                'duplicate-pair' => 'මෙම ඉලක්ක මුදල් ඒකකය සඳහා විනිමය අනුපාතයක් දැනටමත් පවතී. පවතින පේළිය යාවත්කාලීන කරන්න හෝ පළමුව එය මකා දමන්න.',
                'delete-failed' => 'විනිමය අනුපාතය මකා දැමිය නොහැකි විය.',
                'no-permission' => 'විනිමය අනුපාත කළමනාකරණය කිරීමට ඔබට අවසර නැත.',
                'mass-delete-success' => 'විනිමය අනුපාත සාර්ථකව මකා දමන ලදී.',
                'mass-delete-indices-required' => 'indices ක්ෂේත්‍රය අවශ්‍ය වන අතර එය හිස් නොවන අරාවක් විය යුතුය.',
                'update-rates-success' => 'විනිමය අනුපාත සාර්ථකව යාවත්කාලීන කරන ලදී.',
                'update-rates-failed' => 'බාහිර සපයන්නාගෙන් විනිමය අනුපාත යාවත්කාලීන කළ නොහැකි විය.',
                'provider-not-configured' => 'බාහිර විනිමය අනුපාත සපයන්නෙකු වින්‍යාස කර නොමැත.',
            ],

            'tax-rate' => [
                'not-found' => 'බදු අනුපාතය හමු නොවිණි.',
                'created' => 'බදු අනුපාතය සාර්ථකව සාදන ලදී.',
                'updated' => 'බදු අනුපාතය සාර්ථකව යාවත්කාලීන කරන ලදී.',
                'deleted' => 'බදු අනුපාතය සාර්ථකව මකා දමන ලදී.',
                'delete-failed' => 'බදු අනුපාතය මකා දැමිය නොහැකි විය.',
                'no-permission' => 'බදු අනුපාත කළමනාකරණය කිරීමට ඔබට අවසර නැත.',
                'zip-code-required' => 'is_zip false වන විට zip_code ක්ෂේත්‍රය අවශ්‍ය වේ.',
                'zip-range-required' => 'is_zip true වන විට zip_from සහ zip_to යන දෙකම අවශ්‍ය වේ.',
            ],

            'tax-category' => [
                'not-found' => 'බදු ප්‍රවර්ගය හමු නොවිණි.',
                'created' => 'බදු ප්‍රවර්ගය සාර්ථකව සාදන ලදී.',
                'updated' => 'බදු ප්‍රවර්ගය සාර්ථකව යාවත්කාලීන කරන ලදී.',
                'deleted' => 'බදු ප්‍රවර්ගය සාර්ථකව මකා දමන ලදී.',
                'code-unique' => 'කේතය දැනටමත් භාවිතයේ ඇත.',
                'cannot-delete-in-use' => 'මෙම බදු ප්‍රවර්ගයට තවමත් බදු අනුපාත සම්බන්ධ කර ඇති බැවින් මකා දැමිය නොහැක.',
                'delete-failed' => 'බදු ප්‍රවර්ගය මකා දැමිය නොහැකි විය.',
                'no-permission' => 'බදු ප්‍රවර්ග කළමනාකරණය කිරීමට ඔබට අවසර නැත.',
            ],

            'role' => [
                'not-found' => 'භූමිකාව හමු නොවිණි.',
                'created' => 'භූමිකාව සාර්ථකව සාදන ලදී.',
                'updated' => 'භූමිකාව සාර්ථකව යාවත්කාලීන කරන ලදී.',
                'deleted' => 'භූමිකාව සාර්ථකව මකා දමන ලදී.',
                'permissions-required' => 'permission_type custom වන විට permissions ක්ෂේත්‍රය අවශ්‍ය වේ.',
                'cannot-delete-in-use' => 'මෙම භූමිකාව පරිපාලකයින් එක් අයෙකුට හෝ කිහිප දෙනෙකුට පවරා ඇති බැවින් මකා දැමිය නොහැක.',
                'cannot-delete-last-role' => 'අවම වශයෙන් එක් භූමිකාවක් අවශ්‍ය වේ — ඉතිරිව ඇති අවසාන භූමිකාව මකා දැමිය නොහැක.',
                'cannot-change-last-all-access' => 'එකම සම්පූර්ණ ප්‍රවේශ භූමිකාව අභිරුචි භූමිකාවක් ලෙස වෙනස් කළ නොහැක.',
                'delete-failed' => 'භූමිකාව මකා දැමිය නොහැකි විය.',
                'no-permission' => 'භූමිකා කළමනාකරණය කිරීමට ඔබට අවසර නැත.',
            ],

            'currency' => [
                // Read
                'not-found' => 'මුදල් ඒකකය හමු නොවිණි.',

                // Create / Update / Delete
                'created' => 'මුදල් ඒකකය සාර්ථකව සාදන ලදී.',
                'updated' => 'මුදල් ඒකකය සාර්ථකව යාවත්කාලීන කරන ලදී.',
                'deleted' => 'මුදල් ඒකකය සාර්ථකව මකා දමන ලදී.',
                'code-required' => 'code ක්ෂේත්‍රය අවශ්‍ය වේ.',
                'code-size' => 'කේතය හරියටම අකාරාදී අක්ෂර 3 ක් විය යුතුය.',
                'code-unique' => 'කේතය දැනටමත් භාවිතයේ ඇත.',
                'name-required' => 'name ක්ෂේත්‍රය අවශ්‍ය වේ.',
                'cannot-delete-last' => 'අවම වශයෙන් එක් මුදල් ඒකකයක් අවශ්‍ය වේ — ඉතිරිව ඇති අවසාන මුදල් ඒකකය මකා දැමිය නොහැක.',
                'cannot-delete-channel-base' => 'මෙම මුදල් ඒකකය නාලිකා එකක හෝ කිහිපයක මූලික මුදල් ඒකකය වන බැවින් මකා දැමිය නොහැක.',
                'delete-failed' => 'මුදල් ඒකකය මකා දැමිය නොහැකි විය.',
                'no-permission' => 'මුදල් ඒකක කළමනාකරණය කිරීමට ඔබට අවසර නැත.',

                'mass-delete-success' => 'මුදල් ඒකක සාර්ථකව මකා දමන ලදී.',
                'mass-delete-indices-required' => 'indices ක්ෂේත්‍රය අවශ්‍ය වන අතර එය හිස් නොවන අරාවක් විය යුතුය.',
            ],

            'channel' => [
                'not-found' => 'නාලිකාව හමු නොවිණි.',

                'created' => 'නාලිකාව සාර්ථකව සාදන ලදී.',
                'updated' => 'නාලිකාව සාර්ථකව යාවත්කාලීන කරන ලදී.',
                'deleted' => 'නාලිකාව සාර්ථකව මකා දමන ලදී.',
                'code-unique' => 'කේතය දැනටමත් භාවිතයේ ඇත.',
                'hostname-unique' => 'සත්කාරක නාමය දැනටමත් භාවිතයේ ඇත.',
                'default-locale-mismatch' => 'default_locale_id, locales ලැයිස්තුවේ තිබිය යුතුය.',
                'base-currency-mismatch' => 'base_currency_id, currencies ලැයිස්තුවේ තිබිය යුතුය.',
                'unknown-locale' => 'භාෂා ප්‍රදේශ id එකක් හෝ කිහිපයක් නොපවතී.',
                'unknown-currency' => 'මුදල් ඒකක id එකක් හෝ කිහිපයක් නොපවතී.',
                'unknown-inventory-source' => 'inventory_source id එකක් හෝ කිහිපයක් නොපවතී.',
                'unknown-root-category' => 'root_category_id නොපවතී.',
                'cannot-delete-last' => 'අවම වශයෙන් එක් නාලිකාවක් අවශ්‍ය වේ — ඉතිරිව ඇති අවසාන නාලිකාව මකා දැමිය නොහැක.',
                'cannot-delete-default' => 'පෙරනිමි යෙදුම් නාලිකාව මකා දැමිය නොහැක.',
                'delete-failed' => 'නාලිකාව මකා දැමිය නොහැකි විය.',
                'no-permission' => 'නාලිකා කළමනාකරණය කිරීමට ඔබට අවසර නැත.',
            ],

            'user' => [
                'not-found' => 'පරිපාලක පරිශීලකයා හමු නොවිණි.',
                'created' => 'පරිපාලක පරිශීලකයා සාර්ථකව සාදන ලදී.',
                'updated' => 'පරිපාලක පරිශීලකයා සාර්ථකව යාවත්කාලීන කරන ලදී.',
                'deleted' => 'පරිපාලක පරිශීලකයා සාර්ථකව මකා දමන ලදී.',
                'name-required' => 'name ක්ෂේත්‍රය අවශ්‍ය වේ.',
                'email-required' => 'email ක්ෂේත්‍රය අවශ්‍ය වේ.',
                'email-invalid' => 'ඊමේල් ලිපිනය වලංගු ඊමේල් ලිපිනයක් විය යුතුය.',
                'email-unique' => 'ඊමේල් ලිපිනය දැනටමත් භාවිතයේ ඇත.',
                'password-required' => 'password ක්ෂේත්‍රය අවශ්‍ය වේ.',
                'password-min' => 'මුරපදය අවම වශයෙන් අක්ෂර 6 ක් විය යුතුය.',
                'role-required' => 'role_id ක්ෂේත්‍රය අවශ්‍ය වේ.',
                'role-not-found' => 'තෝරාගත් භූමිකාව නොපවතී.',
                'status-invalid' => 'තත්ත්වය 0 හෝ 1 විය යුතුය.',
                'cannot-delete-self' => 'ඔබට ඔබගේම පරිපාලක ගිණුම මකා දැමිය නොහැක.',
                'cannot-delete-last-admin' => 'අවම වශයෙන් එක් පරිපාලක පරිශීලකයෙකු අවශ්‍ය වේ — ඉතිරිව ඇති අවසාන පරිපාලකයා මකා දැමිය නොහැක.',
                'cannot-change' => 'අවසාන සම්පූර්ණ ප්‍රවේශ පරිපාලකයාගේ :name වෙනස් කළ නොහැක.',
                'delete-failed' => 'පරිපාලක පරිශීලකයා මකා දැමිය නොහැකි විය.',
                'no-permission' => 'පරිපාලක පරිශීලකයින් කළමනාකරණය කිරීමට ඔබට අවසර නැත.',
                'self-delete-password-required' => 'ඔබගේම ගිණුම මකා දැමීමට password ක්ෂේත්‍රය අවශ්‍ය වේ.',
                'self-delete-incorrect-password' => 'මුරපදය වැරදිය.',
                'self-deleted' => 'ඔබගේ පරිපාලක ගිණුම මකා දමා ඇත.',
            ],

            'data-transfer' => [
                'import' => [
                    'not-found' => 'ආයාතය හමු නොවිණි.',
                    'deleted' => 'ආයාතය සාර්ථකව මකා දමන ලදී.',
                    'cancelled' => 'ආයාතය සාර්ථකව අවලංගු කරන ලදී.',
                    'cannot-cancel' => 'අවලංගු කළ හැක්කේ පොරොත්තු හෝ සැකසෙමින් පවතින ආයාත පමණි. වත්මන් තත්ත්වය: :state.',
                    'no-permission' => 'දත්ත හුවමාරු ආයාත කළමනාකරණය කිරීමට ඔබට අවසර නැත.',
                    'delete-failed' => 'ආයාතය මකා දැමිය නොහැකි විය.',
                    'created' => 'ආයාතය සාර්ථකව සාදන ලදී.',
                    'updated' => 'ආයාතය සාර්ථකව යාවත්කාලීන කරන ලදී.',
                    'type-invalid' => 'තෝරාගත් වර්ගය අවලංගුය.',
                    'action-invalid' => 'ක්‍රියාව මේවායින් එකක් විය යුතුය: append, delete.',
                    'validation-strategy-invalid' => 'වලංගුකරණ උපාය මාර්ගය මේවායින් එකක් විය යුතුය: stop-on-errors, skip-errors.',
                    'allowed-errors-invalid' => 'අවසර ලත් දෝෂ ගණන 0 හෝ ඊට වැඩි පූර්ණ සංඛ්‍යාවක් විය යුතුය.',
                    'field-separator-required' => 'ක්ෂේත්‍ර වෙන්කරනය අවශ්‍ය වේ.',
                    'file-required' => 'ගොනුවක් අවශ්‍ය වේ.',
                    'file-invalid-type' => 'ගොනුව මේවායින් එකක් විය යුතුය: csv, xls, xlsx, xml.',
                    'nothing-to-import' => 'ආයාත කිරීමට කිසිවක් නැත.',
                    'not-valid' => 'ආයාතය වලංගු නොවේ.',
                    'setup-queue-error' => 'පෝලිම් සම්බන්ධතාවය sync ලෙස සකසා ඇත; පසුබිමේ ආයාත සැකසීමට සැබෑ පෝලිමක් වින්‍යාස කරන්න.',
                    'file-not-found' => 'බාගත කිරීමට ගොනුවක් නොමැත.',
                    'error-report-not-found' => 'බාගත කිරීමට දෝෂ වාර්තාවක් නොමැත.',
                    'sample-not-found' => 'ඉල්ලූ වර්ගය සහ ආකෘතිය සඳහා නියැදි ගොනුවක් නොමැත.',
                    'sample-format-invalid' => 'නියැදි ආකෘතිය මේවායින් එකක් විය යුතුය: csv, xls, xlsx, xml.',
                    'images-archive-too-large' => 'රූප සංරක්ෂිතයේ තිබිය හැක්කේ උපරිම ගොනු :entries ක් වන අතර දිග හැරුණු විට උපරිම :size MB විය යුතුය.',
                ],
            ],
        ],

        'cms' => [
            'page' => [
                'not-found' => 'CMS පිටුව හමු නොවිණි.',

                'created' => 'CMS පිටුව සාර්ථකව සාදන ලදී.',
                'url-key-required' => 'url_key ක්ෂේත්‍රය අවශ්‍ය වේ.',
                'url-key-unique' => 'url_key දැනටමත් භාවිතයේ ඇත.',
                'url-key-invalid' => 'url_key ආකෘතිය අවලංගුය. කුඩා අකුරු, ඉලක්කම් සහ යටි ඉරි පමණක් භාවිතා කරන්න.',
                'page-title-required' => 'page_title ක්ෂේත්‍රය අවශ්‍ය වේ.',
                'html-content-required' => 'html_content ක්ෂේත්‍රය අවශ්‍ය වේ.',
                'channels-required' => 'අවම වශයෙන් එක් නාලිකාවක් තෝරාගත යුතුය.',
                'channels-invalid' => 'නාලිකා එකක් හෝ කිහිපයක් අවලංගුය.',

                'updated' => 'CMS පිටුව සාර්ථකව යාවත්කාලීන කරන ලදී.',

                'deleted' => 'CMS පිටුව සාර්ථකව මකා දමන ලදී.',
                'delete-failed' => 'CMS පිටුව මකා දැමිය නොහැකි විය.',

                'mass-deleted' => 'CMS පිටු සාර්ථකව මකා දමන ලදී.',
                'indices-required' => 'indices ක්ෂේත්‍රය අවශ්‍ය වන අතර එය හිස් නොවන අරාවක් විය යුතුය.',
                'indices-invalid' => 'indices ක්ෂේත්‍රය CMS පිටු ID අරාවක් විය යුතුය.',

                'no-permission' => 'CMS පිටු කළමනාකරණය කිරීමට ඔබට අවසර නැත.',
            ],
        ],

        'marketing' => [
            'catalog-rule' => [
                'not-found' => 'නාමාවලි නීතිය හමු නොවිණි.',
                'created' => 'නාමාවලි නීතිය සාර්ථකව සාදන ලදී.',
                'updated' => 'නාමාවලි නීතිය සාර්ථකව යාවත්කාලීන කරන ලදී.',
                'deleted' => 'නාමාවලි නීතිය සාර්ථකව මකා දමන ලදී.',
                'delete-failed' => 'නාමාවලි නීතිය මකා දැමිය නොහැකි විය.',
                'mass-delete-indices-required' => 'සමූහ මකා දැමීම සඳහා නාමාවලි නීති ID කිසිවක් සපයා නැත.',
                'mass-delete-success' => 'තෝරාගත් නාමාවලි නීති සාර්ථකව මකා දමන ලදී.',
                'no-permission' => 'නාමාවලි නීති කළමනාකරණය කිරීමට ඔබට අවසර නැත.',
            ],

            'campaign' => [
                'not-found' => 'ප්‍රචාරණ ව්‍යාපාරය හමු නොවිණි.',
                'created' => 'ප්‍රචාරණ ව්‍යාපාරය සාර්ථකව සාදන ලදී.',
                'updated' => 'ප්‍රචාරණ ව්‍යාපාරය සාර්ථකව යාවත්කාලීන කරන ලදී.',
                'deleted' => 'ප්‍රචාරණ ව්‍යාපාරය සාර්ථකව මකා දමන ලදී.',
                'delete-failed' => 'ප්‍රචාරණ ව්‍යාපාරය මකා දැමිය නොහැකි විය.',
                'no-permission' => 'ප්‍රචාරණ ව්‍යාපාර කළමනාකරණය කිරීමට ඔබට අවසර නැත.',

                'send' => [
                    'id-required' => 'ප්‍රචාරණ ව්‍යාපාර id අවශ්‍ය වේ.',
                    'inactive' => 'අක්‍රිය ප්‍රචාරණ ව්‍යාපාරයක් යැවිය නොහැක. පළමුව එය සක්‍රීය කරන්න.',
                    'queued' => 'ලබන්නන් :count දෙනෙකු සඳහා ප්‍රචාරණ ව්‍යාපාරය පෝලිමට එක් කරන ලදී.',
                ],
            ],

            'sitemap' => [
                'not-found' => 'අඩවි සිතියම හමු නොවිණි.',
                'created' => 'අඩවි සිතියම සාර්ථකව සාදන ලදී.',
                'updated' => 'අඩවි සිතියම සාර්ථකව යාවත්කාලීන කරන ලදී.',
                'deleted' => 'අඩවි සිතියම සාර්ථකව මකා දමන ලදී.',
                'delete-failed' => 'අඩවි සිතියම මකා දැමිය නොහැකි විය.',
                'no-permission' => 'අඩවි සිතියම් කළමනාකරණය කිරීමට ඔබට අවසර නැත.',

                'generate' => [
                    'id-required' => 'අඩවි සිතියම් id අවශ්‍ය වේ.',
                    'no-channels' => 'මෙම අඩවි සිතියම කිසිදු නාලිකාවක් ආවරණය නොකරන බැවින් ජනනය කිරීමට කිසිවක් නැත. පළමුව එයට අවම වශයෙන් එක් නාලිකාවක් පවරන්න.',
                    'success' => 'අඩවි සිතියම සාර්ථකව නැවත ජනනය කරන ලදී.',
                    'failed' => 'අඩවි සිතියම් ජනනය අසමත් විය: :message',
                ],
            ],

            'cart-rule' => [
                'not-found' => 'කරත්ත නීතිය හමු නොවිණි.',
                'id-required' => 'කරත්ත නීති id එකක් අවශ්‍ය වේ.',
                'copy-of' => ':value හි පිටපත',
                'copied' => 'කරත්ත නීතිය සාර්ථකව පිටපත් කරන ලදී.',
                'created' => 'කරත්ත නීතිය සාර්ථකව සාදන ලදී.',
                'updated' => 'කරත්ත නීතිය සාර්ථකව යාවත්කාලීන කරන ලදී.',
                'deleted' => 'කරත්ත නීතිය සාර්ථකව මකා දමන ලදී.',
                'delete-failed' => 'කරත්ත නීතිය මකා දැමිය නොහැකි විය.',
                'no-permission' => 'කරත්ත නීති කළමනාකරණය කිරීමට ඔබට අවසර නැත.',
                'mass-delete-success' => 'කරත්ත නීති සාර්ථකව මකා දමන ලදී.',
                'mass-delete-indices-required' => 'indices ක්ෂේත්‍රය අවශ්‍ය වන අතර එය හිස් නොවන අරාවක් විය යුතුය.',
                'action-type-invalid' => 'action_type මේවායින් එකක් විය යුතුය: by_percent, by_fixed, cart_fixed, buy_x_get_y.',
                'coupon-code-required' => 'coupon_type=1 සහ use_auto_generation=0 වන විට coupon_code එකක් අවශ්‍ය වේ.',
                'coupon-code-unique' => 'මෙම කූපන් කේතය දැනටමත් භාවිතයේ ඇත.',
            ],

            'template' => [
                'campaign-associated' => 'මෙම ඊමේල් සැකිල්ල ප්‍රචාරණ ව්‍යාපාරයක් විසින් භාවිතා කරන බැවින් මකා දැමිය නොහැක. පළමුව එය ප්‍රචාරණ ව්‍යාපාරයෙන් ඉවත් කරන්න.',
                'not-found' => 'ඊමේල් සැකිල්ල හමු නොවිණි.',
                'created' => 'ඊමේල් සැකිල්ල සාර්ථකව සාදන ලදී.',
                'updated' => 'ඊමේල් සැකිල්ල සාර්ථකව යාවත්කාලීන කරන ලදී.',
                'deleted' => 'ඊමේල් සැකිල්ල සාර්ථකව මකා දමන ලදී.',
                'delete-failed' => 'ඊමේල් සැකිල්ල මකා දැමිය නොහැකි විය.',
                'status-invalid' => 'තත්ත්වය මේවායින් එකක් විය යුතුය: active, inactive, draft.',
                'no-permission' => 'ඊමේල් සැකිලි කළමනාකරණය කිරීමට ඔබට අවසර නැත.',
            ],

            'event' => [
                'campaign-associated' => 'මෙම සිදුවීම ප්‍රචාරණ ව්‍යාපාරයක් විසින් භාවිතා කරන බැවින් මකා දැමිය නොහැක. පළමුව එය ප්‍රචාරණ ව්‍යාපාරයෙන් ඉවත් කරන්න.',
                'not-found' => 'අලෙවිකරණ සිදුවීම හමු නොවිණි.',
                'created' => 'අලෙවිකරණ සිදුවීම සාර්ථකව සාදන ලදී.',
                'updated' => 'අලෙවිකරණ සිදුවීම සාර්ථකව යාවත්කාලීන කරන ලදී.',
                'deleted' => 'අලෙවිකරණ සිදුවීම සාර්ථකව මකා දමන ලදී.',
                'delete-failed' => 'අලෙවිකරණ සිදුවීම මකා දැමිය නොහැකි විය.',
                'no-permission' => 'අලෙවිකරණ සිදුවීම් කළමනාකරණය කිරීමට ඔබට අවසර නැත.',
            ],

            'search-synonym' => [
                'not-found' => 'සෙවුම් සමාන පදය හමු නොවිණි.',
                'created' => 'සෙවුම් සමාන පදය සාර්ථකව සාදන ලදී.',
                'updated' => 'සෙවුම් සමාන පදය සාර්ථකව යාවත්කාලීන කරන ලදී.',
                'deleted' => 'සෙවුම් සමාන පදය සාර්ථකව මකා දමන ලදී.',
                'mass-deleted' => 'සෙවුම් සමාන පද සාර්ථකව මකා දමන ලදී.',
                'delete-failed' => 'සෙවුම් සමාන පදය මකා දැමිය නොහැකි විය.',
                'indices-required' => 'indices ක්ෂේත්‍රය අවශ්‍ය වන අතර එය සෙවුම් සමාන පද ID වල හිස් නොවන අරාවක් විය යුතුය.',
                'no-permission' => 'සෙවුම් සමාන පද කළමනාකරණය කිරීමට ඔබට අවසර නැත.',
            ],

            'url-rewrite' => [
                'not-found' => 'URL නැවත ලිවීම හමු නොවිණි.',
                'created' => 'URL නැවත ලිවීම සාර්ථකව සාදන ලදී.',
                'updated' => 'URL නැවත ලිවීම සාර්ථකව යාවත්කාලීන කරන ලදී.',
                'deleted' => 'URL නැවත ලිවීම සාර්ථකව මකා දමන ලදී.',
                'delete-failed' => 'URL නැවත ලිවීම මකා දැමිය නොහැකි විය.',
                'mass-deleted' => 'URL නැවත ලිවීම් සාර්ථකව මකා දමන ලදී.',
                'indices-required' => 'අවම වශයෙන් එක් URL නැවත ලිවීමේ id එකක් අවශ්‍ය වේ.',
                'no-permission' => 'URL නැවත ලිවීම් කළමනාකරණය කිරීමට ඔබට අවසර නැත.',
            ],

            'cart-rule-coupon' => [
                'cart-rule-not-found' => 'කරත්ත නීතිය හමු නොවිණි.',
                'not-found' => 'කූපනය හමු නොවිණි.',
                'code-taken' => 'මෙම කේතය සහිත කූපනයක් දැනටමත් පවතී.',
                'format-invalid' => 'ආකෘතිය මේවායින් එකක් විය යුතුය: alphabetic, alphanumeric, numeric.',
                'indices-required' => 'indices ක්ෂේත්‍රය අවශ්‍ය වන අතර එය කූපන් ID වල හිස් නොවන අරාවක් විය යුතුය.',
                'created' => 'කූපනය සාර්ථකව සාදන ලදී.',
                'generated' => 'කූපන :count ක් සාර්ථකව ජනනය කරන ලදී.',
                'deleted' => 'කූපනය සාර්ථකව මකා දමන ලදී.',
                'mass-deleted' => 'කූපන :count ක් සාර්ථකව මකා දමන ලදී.',
                'no-permission' => 'කරත්ත නීති කූපන කළමනාකරණය කිරීමට ඔබට අවසර නැත.',
            ],

            'subscriber' => [
                'not-found' => 'පුවත් පත්‍රිකා දායකයා හමු නොවිණි.',
                'updated' => 'දායකත්වය සාර්ථකව යාවත්කාලීන කරන ලදී.',
                'deleted' => 'දායකත්වය සාර්ථකව මකා දමන ලදී.',
                'is-subscribed-required' => 'is_subscribed ක්ෂේත්‍රය අවශ්‍ය වේ.',
                'no-permission' => 'පුවත් පත්‍රිකා දායකයින් කළමනාකරණය කිරීමට ඔබට අවසර නැත.',
            ],

            'search-term' => [
                'not-found' => 'සෙවුම් පදය හමු නොවිණි.',
                'updated' => 'සෙවුම් පදය සාර්ථකව යාවත්කාලීන කරන ලදී.',
                'deleted' => 'සෙවුම් පදය සාර්ථකව මකා දමන ලදී.',
                'mass-deleted' => 'සෙවුම් පද සාර්ථකව මකා දමන ලදී.',
                'term-required' => 'term ක්ෂේත්‍රය අවශ්‍ය වේ.',
                'redirect-url-invalid' => 'redirect_url වලංගු URL එකක් විය යුතුය.',
                'indices-required' => 'indices තුළ අවම වශයෙන් එක් ID එකක් සැපයිය යුතුය.',
                'no-permission' => 'සෙවුම් පද කළමනාකරණය කිරීමට ඔබට අවසර නැත.',
            ],
        ],
        'auth' => [
            'configuration' => [
                'title' => 'පරිපාලක සත්‍යාපනය',
                'info' => 'පරිපාලක API පිවිසුම් සහ API ටෝකන සඳහා ටෝකන වලංගු කාලය, අනුපාත සීමා සහ ආරක්ෂක නීති.',

                'tokens' => [
                    'title' => 'ටෝකන වලංගු කාලය',
                    'info' => 'ප්‍රවේශ ටෝකන, නැවුම් කිරීමේ ටෝකන සහ සැසි කොපමණ කාලයක් වලංගු වේද. වෙනස්කම් පසුව නිකුත් කරන ටෝකන සඳහා අදාළ වේ.',
                    'access-token-ttl' => 'ප්‍රවේශ ටෝකනයේ වලංගු කාලය (මිනිත්තු)',
                    'access-token-ttl-info' => 'මිනිත්තු 5 සිට 60 දක්වා. පෙරනිමිය 15.',
                    'refresh-token-ttl' => 'නැවුම් කිරීමේ ටෝකනයේ වලංගු කාලය (දින)',
                    'refresh-token-ttl-info' => 'දින 1 සිට 90 දක්වා. පෙරනිමිය 30.',
                    'session-lifetime' => 'සැසියේ උපරිම කාලය (දින)',
                    'session-lifetime-info' => 'දින 1 සිට 365 දක්වා. පෙරනිමිය 90. ඉන්පසු සැසිය කී වතාවක් නැවුම් කළත් පරිපාලක නැවත පිවිසිය යුතුය.',
                ],

                'rate-limits' => [
                    'title' => 'අනුපාත සීමා',
                    'info' => 'මිනිත්තුවකට උපරිම පරිපාලක API ඉල්ලීම්.',
                    'session-requests-per-minute' => 'පිවිසි එක් පරිපාලකයෙකුට මිනිත්තුවකට ඉල්ලීම්',
                    'api-token-requests-per-minute' => 'එක් API ටෝකනයකට මිනිත්තුවකට ඉල්ලීම්',
                ],

                'security' => [
                    'title' => 'ආරක්ෂාව',
                    'info' => 'අවලංගු කිරීමේ නීති, API ටෝකන කල් ඉකුත්වීම සහ වසම් බැඳීම.',
                    'revoke-sessions-on-password-change' => 'මුරපදය වෙනස් කළ විට සැසි අවලංගු කරන්න',
                    'revoke-api-tokens-on-password-change' => 'මුරපදය වෙනස් කළ විට API ටෝකන අවලංගු කරන්න',
                    'allow-non-expiring-api-tokens' => 'කල් ඉකුත් නොවන API ටෝකනවලට ඉඩ දෙන්න',
                    'domain-binding-mode' => 'වසම් බැඳීමේ ප්‍රකාරය',
                    'domain-binding-mode-info' => 'DNS මඟින් ඉල්ලීම ටෝකනයේ වසම යොමු වන IP ලිපිනයකින් පැමිණේදැයි පරීක්ෂා කරයි. Origin මඟින් Origin හෝ Referer ශීර්ෂය පරීක්ෂා කරන අතර එය බ්‍රව්සර් සඳහා පමණක් සුදුසුය.',
                    'domain-binding-dns' => 'DNS (සේවාලාභී IP ලිපිනය)',
                    'domain-binding-origin' => 'Origin ශීර්ෂය',
                ],
            ],

            'errors' => [
                'unauthenticated' => 'ඔබ සත්‍යාපනය වී නොමැත.',
                'access-token-expired' => 'ප්‍රවේශ ටෝකනය කල් ඉකුත් වී ඇත. ඉදිරියට යාමට එය නැවුම් කරන්න.',
                'api-token-expired' => 'API ටෝකනය කල් ඉකුත් වී ඇත.',
                'refresh-token-invalid' => 'නැවුම් කිරීමේ ටෝකනය අවලංගු හෝ කල් ඉකුත් වී ඇත. කරුණාකර නැවත පිවිසෙන්න.',
                'api-token-revoked' => 'API ටෝකනය අවලංගු කර ඇත.',
                'api-token-domain-mismatch' => 'මෙම වසමෙන් API ටෝකනය භාවිත කළ නොහැක.',
                'access-token-required' => 'මෙම ක්‍රියාවට පිවිසුම් සැසියක ප්‍රවේශ ටෝකනය අවශ්‍යයි; API ටෝකනවලට එය භාවිත කළ නොහැක.',
                'two-factor-required' => 'මෙම ගිණුම සඳහා ද්වි-සාධක සත්‍යාපනය සක්‍රිය කර ඇති අතර, දෙවන සාධකයක් සමඟ API පිවිසුම තවමත් ලබා ගත නොහැක.',
                'too-many-login-attempts' => 'පිවිසුම් උත්සාහයන් ඕනෑවට වඩා වැඩිය. කරුණාකර තත්පර :seconds කින් නැවත උත්සාහ කරන්න.',
                'rate-limit-exceeded' => 'උත්සාහයන් ඕනෑවට වඩා වැඩිය.',
                'no-permission' => 'පරිපාලක API සැසි කළමනාකරණය කිරීමට ඔබට අවසර නැත.',
            ],

            'sessions' => [
                'logged-out' => 'ඔබ ඉවත් වී ඇත.',
                'logged-out-all' => 'ඔබ සියලු සැසිවලින් ඉවත් වී ඇත.',
                'not-found' => 'සැසිය හමු නොවීය.',
                'revoked' => 'සැසිය අවලංගු කර ඇත.',
                'revoked-for-admin' => 'අවලංගු කළ සැසි: :count.',
            ],

            'acl' => [
                'api-sessions' => 'API සැසි',
                'revoke' => 'සැසි අවලංගු කරන්න',
            ],
        ],
    ],

    'integration' => [
        'menu' => [
            'title' => 'ඒකාබද්ධ කිරීම',
            'tokens' => 'ටෝකන',
        ],

        'history' => [
            'menu' => [
                'title' => 'ඉතිහාසය',
            ],

            'acl' => [
                'title' => 'API වෙනස් කිරීමේ ඉතිහාසය',
                'view' => 'බලන්න',
                'delete' => 'ඉතිහාසය මකන්න',
            ],

            'index' => [
                'title' => 'API වෙනස් කිරීමේ ඉතිහාසය',
                'info' => 'පරිපාලක API හරහා සාදන ලද සෑම නිර්මාණයක්ම, යාවත්කාලීන කිරීම සහ මකා දැමීම, එය කළේ කවුරුන්ද, කුමන ටෝකනය සහ වෙනස් කළ දේ සමඟද.',
                'cleanup-btn' => 'පැරණි ලොග මකන්න',
                'cleanup-days' => 'මෙම දින ගණනට වඩා පැරණි ලොග මකන්න',
                'cleanup-confirm' => 'දී ඇති දින ගණනට වඩා පැරණි සියලුම ඉතිහාසය මකන්නද? මෙය ආපසු හැරවිය නොහැක.',
            ],

            'view' => [
                'title' => 'වෙනස් කරන්න',
                'back-btn' => 'ආපසු',
                'admin' => 'පරිපාලක',
                'token' => 'ටෝකනය',
                'action' => 'ක්‍රියාව',
                'resource' => 'සම්පත්',
                'method' => 'ක්රමය',
                'ip' => 'IP ලිපිනය',
                'date' => 'දිනය',
                'version' => 'අනුවාදය',
                'url' => 'අන්ත ලක්ෂ්‍යය',
                'request-details' => 'ඉල්ලීම් විස්තර',
                'changes' => 'වෙනස්කම්',
                'field' => 'ක්ෂේත්රය',
                'old' => 'පැරණි වටිනාකම',
                'new' => 'නව වටිනාකමක්',
                'no-field-changes' => 'මෙම ප්‍රවේශය සඳහා ක්ෂේත්‍ර මට්ටමේ වෙනස්කම් කිසිවක් වාර්තා කර නොමැත.',
                'same-request' => 'එකම ඉල්ලීමේ වෙනත් වෙනස්කම්',
                'version-chain' => 'මෙම වාර්තාවේ අනුවාද ඉතිහාසය',
            ],

            'datagrid' => [
                'id' => 'හැඳුනුම්පත',
                'date' => 'දිනය',
                'admin' => 'පරිපාලක',
                'token' => 'ටෝකනය',
                'action' => 'ක්‍රියාව',
                'operation' => 'මෙහෙයුම',
                'resource' => 'සම්පත්',
                'version' => 'අනුවාදය',
                'method' => 'ක්රමය',
                'ip' => 'IP',
                'view' => 'බලන්න',
                'delete' => 'මකන්න',
            ],

            'events' => [
                'created' => 'නිර්මාණය කළා',
                'updated' => 'යාවත්කාලීන කරන ලදී',
                'deleted' => 'මකා දමන ලදී',
            ],

            'deleted' => ':count ඉතිහාස වාර්තාව(ය) මකා ඇත.',
            'cleanup-input-required' => 'පිරිසිදු කිරීමට දින කිහිපයක් හෝ දිනයක් ලබා දෙන්න.',
        ],

        'acl' => [
            'title' => 'ඒකාබද්ධ කිරීම',
            'view' => 'බලන්න',
            'create' => 'ඒකාබද්ධ කිරීම සාදන්න',
            'edit' => 'අනුකලනය සංස්කරණය කරන්න',
            'delete' => 'ඒකාබද්ධතා ටෝකනය අවලංගු කරන්න',
            'generate' => 'ඒකාබද්ධතා ටෝකනය ජනනය කරන්න',
            'regenerate' => 'ඒකාබද්ධතා ටෝකනය නැවත උත්පාදනය කරන්න',
        ],

        'index' => [
            'title' => 'ඒකාබද්ධ කිරීම්',
            'create-btn' => 'ඒකාබද්ධ කිරීම සාදන්න',
        ],

        'create' => [
            'title' => 'ඒකාබද්ධ කිරීම සාදන්න',
            'save-btn' => 'සුරකින්න',
            'back-btn' => 'ආපසු',
        ],

        'edit' => [
            'title' => 'අනුකලනය සංස්කරණය කරන්න',
            'save-btn' => 'සුරකින්න',
            'back-btn' => 'ආපසු',
            'generate-btn' => 'ටෝකනය ජනනය කරන්න',
            'regenerate-btn' => 'ටෝකනය නැවත උත්පාදනය කරන්න',
            'revoke-btn' => 'ටෝකනය අවලංගු කරන්න',
            'copy-btn' => 'පිටපත් කරන්න',
            'token-warning' => 'මෙම ටෝකනය දැන් සුරකින්න - එය නැවත නොපෙන්වයි.',
            'token-label' => 'ටෝකනය',
            'not-generated' => 'තවමත් ජනනය කර නැත',
            'masked' => '(ගබඩා - පරම්පරාවෙන් එක් වරක් පමණක් පෙන්වනු ලැබේ)',
            'history-banner' => 'මෙම ටෝකනය තවදුරටත් සක්‍රිය නොවේ.',
        ],

        'fields' => [
            'name' => 'නම',
            'description' => 'විස්තරය',
            'assign-user' => 'පරිශීලක පවරන්න',
            'permission-type' => 'අවසර වර්ගය',
            'access-control' => 'ප්රවේශ පාලනය',
            'general' => 'ජෙනරාල්',
            'token-settings' => 'ටෝකන් සැකසුම්',
            'valid-till' => 'දක්වා වලංගු වේ',
            'rate-limit-per-minute' => 'ගාස්තු සීමාව (විනාඩියකට)',
            'rate-limit-per-day' => 'ගාස්තු සීමාව (දිනකට)',
            'never-expires' => 'කිසිදා කල් ඉකුත් නොවේ',
            'expires-on' => 'කල් ඉකුත් වේ',
            'unlimited' => 'අසීමිතයි',
            'limit-to' => 'දක්වා සීමා කරන්න',
            'requests-per-minute' => 'ඉල්ලීම් / මිනිත්තුව',
            'requests-per-day' => 'ඉල්ලීම් / දින',
            'select-admin' => 'පරිපාලකයෙකු තෝරන්න',
            'no-available-admins' => 'පරිපාලකයින් නොමැත - සෑම පරිපාලකයෙකුටම දැනටමත් ක්‍රියාකාරී ටෝකනයක් ඇත.',
            'same-as-web-hint' => 'ටෝකනය පවරා ඇති පරිපාලකගේ වත්මන් භූමිකාව අවසර සජීවීව පිළිබිඹු කරයි.',
            'ip-allowlist' => 'IP අවසර ලැයිස්තුව',
            'ip-any' => 'ඕනෑම IP (පෙරනිමි)',
            'ip-restricted' => 'විශේෂිත IP වලට සීමා කර ඇත',
            'ip-list-hint' => 'පේළියකට එක් ඇතුල්වීමක්. IPv4, IPv6 සහ CIDR සඳහා සහය දක්වයි (උදා. 10.0.0.0/24 හෝ 2001:db8::/32). සියලුම IP වලට ඉඩ දීමට හිස්ව තබන්න.',
        ],

        'permission-type' => [
            'all' => 'සියල්ල',
            'custom' => 'අභිරුචි',
            'same-as-web' => 'වෙබ් අවසරය හා සමානයි',
        ],

        'status' => [
            'draft' => 'කෙටුම්පත',
            'active' => 'ක්රියාකාරී',
            'revoked' => 'අවලංගු කළා',
            'regenerated' => 'නැවත උත්පාදනය කරන ලදී',
        ],

        'datagrid' => [
            'id' => 'හැඳුනුම්පත',
            'name' => 'නම',
            'admin' => 'පරිපාලක',
            'token' => 'ටෝකනය',
            'status' => 'තත්ත්වය',
            'permission-type' => 'අවසර වර්ගය',
            'expires-at' => 'දක්වා වලංගු වේ',
            'last-used-at' => 'අවසන් වරට භාවිතා කරන ලදී',
            'created-at' => 'දී නිර්මාණය කරන ලදී',
            'edit' => 'සංස්කරණය කරන්න',
            'revoke' => 'අවලංගු කරන්න',
        ],

        'messages' => [
            'draft-created' => 'ඒකාබද්ධ කිරීම නිර්මාණය කරන ලදී. එය භාවිතා කිරීම ආරම්භ කිරීමට ටෝකනය ජනනය කරන්න.',
            'updated' => 'ඒකාබද්ධ කිරීම සාර්ථකව යාවත්කාලීන කරන ලදී.',
            'generated' => 'ටෝකනය ජනනය කරන ලදී. දැන් එය පිටපත් කරන්න - එය නැවත නොපෙන්වයි.',
            'regenerated' => 'ටෝකනය නැවත උත්පාදනය කරන ලදී. දැන් නව ටෝකනය පිටපත් කරන්න - එය නැවත නොපෙන්වයි.',
            'revoked' => 'ටෝකනය සාර්ථකව අවලංගු කරන ලදී.',
            'generate-only-draft' => 'ඒවායේ ටෝකනය ජනනය කළ හැක්කේ කෙටුම්පත් ඒකාබද්ධ කිරීම්වලට පමණි.',
            'regenerate-only-active' => 'නැවත උත්පාදනය කළ හැක්කේ සක්‍රීය ටෝකන පමණි.',
            'cannot-edit-historic' => 'අවලංගු කළ හෝ නැවත උත්පාදනය කළ ටෝකන සංස්කරණය කළ නොහැක.',
            'already-inactive' => 'මෙම ටෝකනය දැනටමත් අක්‍රියයි.',
        ],

        'errors' => [
            'admin-has-token' => 'තෝරාගත් පරිපාලකයාට දැනටමත් ක්‍රියාකාරී ඒකාබද්ධ කිරීමේ ටෝකනයක් ඇත.',
            'only-draft-generate' => 'ජනනය කළ හැක්කේ කෙටුම්පත් ටෝකන පමණි.',
        ],

        'validation' => [
            'ip-invalid' => 'සෑම අවසර ලත් IP එකක්ම වලංගු IPv4 හෝ IPv6 ලිපිනයක් විය යුතුය (CIDR අංකනයට සහය දක්වයි).',
            'cidr-prefix-invalid' => 'ලබා දී ඇති IP අනුවාදය සඳහා CIDR උපසර්ගය වලංගු නොවේ.',
        ],

        'configuration' => [
            'api' => [
                'title' => 'API',
                'info' => 'Bagisto API සහ එහි පරිපාලක මොඩියුල සඳහා සැකසීම්.',
            ],

            'integration' => [
                'title' => 'ඒකාබද්ධ කිරීම',
                'info' => 'පරිපාලක API ටෝකන නිකුත් කිරීමට භාවිතා කරන API ඒකාබද්ධතා ප්ලගිනය කළමනාකරණය කරන්න.',
            ],

            'settings' => [
                'title' => 'මොඩියුල සැකසුම්',
                'info' => 'API Integration ප්ලගිනය සබල කරන්න හෝ අක්‍රිය කරන්න. අබල කළ විට, එහි පැති තීරු මෙනුව සඟවා ඇති අතර එහි පිටු 404 ලබා දෙයි.',
                'enable' => 'API ඒකාබද්ධතා මොඩියුලය සබල කරන්න',
            ],
        ],

        'emails' => [
            'generated' => [
                'subject' => 'නව API ටෝකනයක් උත්පාදනය කරන ලදී: :name',
                'greeting' => '":name" නමින් API ඒකාබද්ධතා ටෝකනයක් ඔබගේ ගිණුමේ ජනනය කර ඇත.',
            ],

            'regenerated' => [
                'subject' => 'ඔබගේ API ටෝකනය නැවත උත්පාදනය කරන ලදී: :name',
                'greeting' => '":name" නම් API ඒකාබද්ධතා ටෝකනය මේ දැන් නැවත උත්පාදනය කරන ලදී. පෙර ටෝකනය වැඩ කිරීම නතර කර ඇත - නව එක පමණක් වලංගු වේ.',
            ],

            'revoked' => [
                'subject' => 'ඔබගේ API ටෝකනය අවලංගු කරන ලදී: :name',
                'greeting' => '":name" නම් වූ API ඒකාබද්ධතා ටෝකනය අවලංගු කරන ලදී. එය භාවිතා කරන ඕනෑම සේවාදායකයකුට ප්‍රවේශය අහිමි වී ඇත.',
            ],

            'details' => [
                'name' => 'සංකේත නාමය',
                'date' => 'දිනය',
                'ip' => 'IP වලින්',
            ],

            'revoke-hint' => 'ඔබ මෙය අපේක්ෂා නොකළේ නම්, පහත බොත්තම භාවිතයෙන් වහාම ටෝකනය අවලංගු කරන්න.',
            'revoke-btn' => 'මෙම ටෝකනය අවලංගු කරන්න',
            'revoke-expiry' => 'මෙම අවලංගු කිරීමේ සබැඳිය දින 7ක් සඳහා වලංගු වේ. ඊට පසු, ටෝකනය කළමනාකරණය කිරීමට පරිපාලක පැනලය වෙත පුරනය වන්න.',
            'no-action' => 'කිසිදු ක්‍රියාමාර්ගයක් අවශ්‍ය නොවේ - මෙම විද්‍යුත් තැපෑල තහවුරු කිරීමක් පමණි.',
        ],

        'revoke-confirmation' => [
            'title' => 'API ටෝකනය අවලංගු කරන්න',
            'confirm-title' => 'මෙම ටෝකනය අහෝසි කරන්නද?',
            'confirm-message' => '":name" ටෝකනය එය භාවිතා කරන සෑම සේවාදායකයෙකු සඳහාම වහාම ක්‍රියා කිරීම නවත්වනු ඇත.',
            'confirm-button' => 'ටෝකනය අහෝසි කරන්න',
            'success-title' => 'ටෝකනය අවලංගු කරන ලදී',
            'success-message' => '":name" ටෝකනය අවලංගු කර ඇත. එය භාවිතා කරන ඕනෑම සේවාදායකයකුට වහාම ප්‍රවේශය අහිමි වී ඇත.',
            'already-inactive-title' => 'ටෝකනය දැනටමත් අක්‍රියයි',
            'already-inactive-message' => '":name" ටෝකනය දැනටමත් අවලංගු කර හෝ නැවත උත්පාදනය කර ඇත. වැඩිදුර ක්‍රියාමාර්ග අවශ්‍ය නොවේ.',
        ],

        'confirm' => [
            'generate' => [
                'title' => 'ටෝකනය ජනනය කරන්න',
                'message' => 'දැන් ටෝකනය ජනනය කරන්නද? සරල පාඨය එක් වරක් පමණක් පෙන්වනු ඇත - පිටුවෙන් පිටවීමට පෙර එය පිටපත් කරන්න.',
            ],

            'regenerate' => [
                'title' => 'ටෝකනය නැවත උත්පාදනය කරන්න',
                'message' => 'ටෝකනය නැවත උත්පාදනය කරන්නද? පැරණි ටෝකනය වහාම ක්‍රියා විරහිත වන අතර නව සරල පාඨය එක් වරක් පමණක් පෙන්වනු ඇත.',
            ],

            'revoke' => [
                'title' => 'ටෝකනය අවලංගු කරන්න',
                'message' => 'මෙම ටෝකනය අවලංගු කරන්නද? එය භාවිතා කරන ඕනෑම සේවාදායකයෙක් වහාම ප්‍රවේශය අහිමි වනු ඇත. මෙම ක්‍රියාව පසුගමනය කළ නොහැක.',
            ],
        ],
    ],
];
