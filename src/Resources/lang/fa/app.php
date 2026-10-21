<?php

return [

    'graphql' => [
        'headers' => [
            'unknown-channel' => 'کانال ":code" در هدر X-Channel وجود ندارد.',
            'storefront-key-required' => 'هدر X-STOREFRONT-KEY الزامی است',
            'storefront-key-invalid' => 'کلید ویترین فروشگاه نامعتبر است',
            'storefront-key-rate-limited' => 'از حد مجاز درخواست‌ها فراتر رفته‌اید. لطفاً پس از :seconds ثانیه دوباره تلاش کنید',
        ],

        'cart' => [
            'authentication-required' => 'توکن احراز هویت مورد نیاز است',
            'invalid-token' => 'توکن احراز هویت نامعتبر یا منقضی شده است',
            'unauthorized-access' => 'دسترسی غیرمجاز به سبد خرید',
            'authenticated-only' => 'فقط کاربران احراز هویت‌شده می‌توانند سبد خرید خود را دریافت کنند',
            'merge-requires-auth' => 'ادغام مهمان به احراز هویت نیاز دارد',
            'unknown-operation' => 'عملیات ناشناخته سبد خرید',

            'cart-not-found' => 'سبد خرید یافت نشد',
            'guest-cart-not-found' => 'سبد خرید مهمان یافت نشد',
            'product-not-found' => 'محصول یافت نشد',

            'product-id-quantity-required' => 'شناسه محصول و تعداد الزامی است',
            'cart-item-id-quantity-required' => 'شناسه آیتم سبد خرید و تعداد الزامی است',

            'file-token-invalid' => 'مرجع فایل بارگذاری‌شده نامعتبر یا منقضی شده است. فایل را دوباره بارگذاری کنید.',
            'file-token-forbidden' => 'این فایل بارگذاری‌شده متعلق به سبد خرید شما نیست.',
            'customizable-option-required' => 'گزینه قابل سفارشی‌سازی ":label" الزامی است.',
            'customizable-file-option-invalid' => 'گزینه انتخاب‌شده یک گزینه فایل برای این محصول نیست.',
            'customizable-file-invalid' => 'فایل وجود ندارد، پسوند پشتیبانی‌نشده دارد یا بیش از حد بزرگ است.',
            'cart-item-id-required' => 'شناسه آیتم سبد خرید الزامی است',
            'item-ids-required' => 'آرایه شناسه‌های آیتم الزامی است',

            'event-booking-quantity-not-changeable' => 'تعداد محصول رزرو رویداد قابل تغییر نیست. تعداد بر اساس انتخاب بلیت تعیین می‌شود.',
            'appointment-booking-quantity-not-changeable' => 'تعداد محصول رزرو نوبت قابل تغییر نیست.',
            'coupon-code-required' => 'کد کوپن الزامی است',
            'address-data-required' => 'کشور، استان و کد پستی الزامی است',
            'grouped-qty-required' => 'محصول گروهی به تعدادهای انتخاب‌شده نیاز دارد. groupedQty را به‌صورت رشته JSON ارسال کنید، برای مثال {"101":2,"102":1}.',
            'grouped-qty-must-include-all' => 'محصول گروهی برای همه محصولات مرتبط به تعداد نیاز دارد. شناسه‌های ناموجود: :ids.',
            'grouped-qty-invalid-associated' => 'تعدادهای محصول گروهی شامل شناسه‌های محصول مرتبط نامعتبر است: :ids.',
            'grouped-qty-invalid-quantity' => 'تعداد نامعتبر برای شناسه محصول مرتبط :id ارائه شده است. تعداد باید یک عدد صحیح نامنفی باشد.',
            'bundle-qty-not-changeable' => 'تعداد گزینه بسته ":option" قابل تغییر نیست. تعداد مجاز :qty است.',

            'add-product-failed' => 'افزودن محصول به سبد خرید ناموفق بود',
            'update-item-failed' => 'به‌روزرسانی آیتم سبد خرید ناموفق بود',
            'remove-item-failed' => 'حذف آیتم سبد خرید ناموفق بود',
            'apply-coupon-failed' => 'اعمال کوپن ناموفق بود',
            'remove-coupon-failed' => 'حذف کوپن ناموفق بود',
            'coupon-applied' => 'کوپن با موفقیت اعمال شد',
            'coupon-removed' => 'کوپن با موفقیت حذف شد',
            'move-to-wishlist-failed' => 'انتقال آیتم به فهرست علاقه‌مندی‌ها ناموفق بود',
            'estimate-shipping-failed' => 'برآورد هزینه ارسال ناموفق بود',

            'product-added-successfully' => 'محصول با موفقیت به سبد خرید اضافه شد',
            'cart-item-updated-successfully' => 'آیتم سبد خرید با موفقیت به‌روزرسانی شد',
            'guest-cart-merged' => 'سبد خرید مهمان با موفقیت ادغام شد',
            'using-authenticated-cart' => 'استفاده از سبد خرید مشتری احراز هویت‌شده',
            'cart-item-not-found' => 'آیتم سبد خرید یافت نشد',
            'new-guest-cart-created' => 'سبد خرید مهمان جدید با توکن نشست منحصربه‌فرد ایجاد شد',
            'select-items-to-remove' => 'لطفاً آیتم‌هایی را برای حذف انتخاب کنید',
            'select-items-to-move-wishlist' => 'لطفاً آیتم‌هایی را برای انتقال به فهرست علاقه‌مندی‌ها انتخاب کنید',
            'invalid-or-expired-token' => 'توکن سبد خرید نامعتبر یا منقضی شده است. لطفاً یک سبد خرید جدید ایجاد کنید.',
            'invalid-token-of-login-user' => 'توکن کاربر وارد شده نامعتبر است.',
            'booking-note-required' => 'booking.note برای رزرو میز الزامی است.',
            'booking-date-required' => 'هنگامی که booking.slot یک بازه زمانی قالب‌بندی‌شده است، booking.date الزامی است.',
            'booking-qty-required' => 'booking.qty برای رزرو رویداد الزامی است.',
            'booking-ticket-qty-invalid' => 'تعداد بلیت رویداد باید یک عدد نامنفی باشد.',
            'booking-ticket-required' => 'برای رزرو رویداد حداقل تعداد یک بلیت را انتخاب کنید.',
            'booking-slot-unavailable' => 'بازه زمانی انتخاب‌شده برای تاریخ داده‌شده در دسترس نیست.',
            'invalid-quantity' => 'مقدار باید حداقل 1 باشد',
            'product-id-required' => 'شناسه محصول الزامی است',
            'shipping-address-required' => 'کشور و کد پستی برای تخمین هزینه ارسال الزامی است',
        ],

        'token-verification' => [
            'invalid-operation' => 'عملیات نامعتبر',
            'invalid-input-data' => 'داده‌های ورودی نامعتبر',
            'token-required' => 'توکن الزامی است',
            'invalid-token-format' => 'قالب توکن نامعتبر',
            'token-not-found-or-expired' => 'توکن یافت نشد یا منقضی شده است',
            'customer-not-found' => 'مشتری یافت نشد',
            'customer-account-suspended' => 'حساب مشتری معلق شده است',
            'error-verifying-token' => 'خطا در تأیید توکن',
            'token-is-valid' => 'توکن معتبر است',
            'unauthenticated' => 'احراز هویت نشده‌اید. لطفاً برای انجام این عمل وارد شوید',
        ],

        'forgot-password' => [
            'invalid-operation' => 'عملیات نامعتبر',
            'invalid-input-data' => 'داده‌های ورودی نامعتبر',
            'email-required' => 'ایمیل الزامی است',
            'reset-link-sent' => 'لینک بازنشانی با موفقیت به ایمیل شما ارسال شد',
            'email-not-found' => 'آدرس ایمیل یافت نشد',
            'throttled' => 'تلاش‌های بازنشانی بیش از حد. لطفاً قبل از تلاش مجدد صبر کنید.',
            'error-sending-reset-link' => 'هنگام ارسال لینک بازنشانی خطایی رخ داد',
        ],

        'reset-password' => [
            'failed' => 'بازنشانی رمز عبور انجام نشد. لطفاً دوباره تلاش کنید.',
        ],

        'rate-limit' => [
            'too-many-attempts' => 'تلاش‌های بیش از حد. لطفاً پس از :seconds ثانیه دوباره تلاش کنید.',
        ],

        'logout' => [
            'invalid-operation' => 'عملیات نامعتبر',
            'invalid-input-data' => 'داده‌های ورودی نامعتبر',
            'token-required' => 'توکن الزامی است',
            'invalid-token-format' => 'قالب توکن نامعتبر',
            'logged-out-successfully' => 'با موفقیت خارج شدید',
            'token-not-found-or-expired' => 'توکن یافت نشد یا قبلاً منقضی شده است',
            'error-during-logout' => 'خطا هنگام خروج از سیستم',
            'unauthenticated' => 'احراز هویت نشده‌اید. لطفاً برای انجام این عمل وارد شوید',
        ],

        'address' => [
            'deleted-successfully' => 'آدرس با موفقیت حذف شد',
            'authentication-required' => 'توکن احراز هویت مورد نیاز است',
            'invalid-token' => 'توکن نامعتبر یا منقضی شده است',
            'unknown-operation' => 'عملیات ناشناخته',
            'address-id-required' => 'شناسه آدرس الزامی است',
            'address-not-found' => 'آدرس یافت نشد یا متعلق به این مشتری نیست',
            'retrieved' => 'آدرس‌ها با موفقیت بازیابی شدند',
            'fetch-failed' => 'بازیابی آدرس‌ها ناموفق بود:',
            'field-required' => 'فیلد :field الزامی است',
        ],

        'customer-profile' => [
            'authentication-required' => 'توکن احراز هویت مورد نیاز است. لطفاً توکن را در ورودی پرس‌وجو ارائه دهید',
            'invalid-token' => 'توکن نامعتبر یا منقضی شده است',
            'profile-updated' => 'نمایه مشتری با موفقیت به‌روزرسانی شد',
        ],

        'customer' => [
            'password-mismatch' => 'رمز عبور و تأیید رمز عبور مطابقت ندارند',
            'confirm-password-required' => 'هنگام تغییر رمز عبور، تأیید رمز عبور الزامی است',
            'current-password-required' => 'برای تغییر رمز عبور، رمز عبور فعلی الزامی است',
            'current-password-incorrect' => 'رمز عبور فعلی که وارد کردید نادرست است',
            'verify-email-to-login' => 'حساب شما ایجاد شد. لطفاً برای ورود، آدرس ایمیل خود را تأیید کنید.',
            'delete-password-required' => 'برای حذف حساب، رمز عبور شما الزامی است',
            'delete-password-incorrect' => 'رمز عبوری که وارد کردید نادرست است',
            'delete-has-active-orders' => 'تا زمانی که سفارش در انتظار یا در حال پردازش دارید، حساب شما قابل حذف نیست',
            'unauthenticated' => 'احراز هویت نشده. لطفاً برای انجام این عمل وارد شوید',
            'id-required' => 'شناسه مشتری الزامی است',
            'invalid-id-format' => 'قالب شناسه نامعتبر است. قالب IRI مانند "/api/admin/customers/1" یا شناسه عددی انتظار می‌رود',
            'not-found' => 'مشتری یافت نشد',
            'phone-special-chars-not-allowed' => 'شماره موبایل فقط می‌تواند شامل ارقام باشد. نویسه‌های خاص مجاز نیستند',
            'email-already-taken' => 'این آدرس ایمیل قبلاً برای حساب دیگری ثبت شده است',
            'invalid-gender' => 'مقدار جنسیت ":gender" نامعتبر است. مقادیر مجاز عبارت‌اند از: :valid',
        ],

        'product-review' => [
            'id-required' => 'شناسه نظر محصول الزامی است',
            'invalid-id-format' => 'قالب شناسه نامعتبر است. قالب IRI مانند "/api/shop/reviews/1" یا شناسه عددی انتظار می‌رود',
            'not-found' => 'نظر محصول با شناسه ":id" یافت نشد',
            'item-not-found' => 'مورد ":id" یافت نشد.',
            'product-id-required' => 'شناسه محصول الزامی است',
            'product-not-found' => 'محصول یافت نشد',
            'rating-invalid' => 'امتیاز باید بین 1 و 5 باشد',
            'title-required' => 'عنوان بررسی الزامی است',
            'comment-required' => 'نظر بررسی الزامی است',
            'review-disabled' => 'نظرات محصول در حال حاضر غیرفعال است',
            'guest-review-disabled' => 'نظرات مهمان مجاز نیست. لطفاً برای ثبت نظر وارد شوید',
            'owner-required' => 'لطفاً برای مدیریت نظر خود وارد شوید',
            'not-owned' => 'این نظر توسط شما نوشته نشده است و قابل ویرایش یا حذف نیست',
            'invalid-attachments' => 'قالب پیوست‌ها نامعتبر است.',
            'invalid-product-id-format' => 'قالب شناسه محصول نامعتبر است. قالب IRI مانند "/api/shop/products/1" یا شناسه عددی انتظار می‌رود',
        ],

        'customer-review' => [
            'id-required' => 'شناسه نظر مشتری الزامی است',
            'invalid-id-format' => 'قالب شناسه نامعتبر است. قالب IRI مانند "/api/shop/customer-reviews/1" یا شناسه عددی انتظار می‌رود',
            'not-found' => 'نظر مشتری با شناسه ":id" یافت نشد',
        ],

        'newsletter' => [
            'error-during-subscription' => 'در حال حاضر امکان تکمیل اشتراک وجود ندارد. لطفاً بعداً دوباره تلاش کنید',
        ],

        'contact-us' => [
            'success' => 'درخواست شما با موفقیت ارسال شد. به‌زودی با شما تماس خواهیم گرفت',
            'failed' => 'در حال حاضر امکان ارسال درخواست شما وجود ندارد. لطفاً بعداً دوباره تلاش کنید',
            'invalid-input' => 'داده‌های فرم تماس نامعتبر است',
        ],

        'customer-order' => [
            'id-required' => 'شناسه سفارش مشتری الزامی است',
            'invalid-id-format' => 'قالب شناسه نامعتبر است. قالب IRI مانند "/api/shop/customer-orders/1" یا شناسه عددی انتظار می‌رود',
            'not-found' => 'سفارش مشتری با شناسه ":id" یافت نشد',
        ],

        'return' => [
            'not-found' => 'درخواست مرجوعی یافت نشد.',
            'disabled' => 'مرجوعی برای این فروشگاه فعال نیست.',
            'invalid-input' => 'جزئیات مرجوعی وجود ندارد یا نامعتبر است.',
            'invalid-resolution-type' => 'نوع راه‌حل باید "return" یا "cancel_items" باشد.',
            'agreement-required' => 'باید سیاست مرجوعی را بپذیرید.',
            'invalid-order' => 'سفارش انتخاب‌شده معتبر نیست.',
            'invalid-item' => 'آیتم انتخاب‌شده واجد شرایط مرجوعی نیست.',
            'qty-exceeds' => 'تعداد درخواستی از تعداد قابل مرجوع بیشتر است.',
            'invalid-package-condition' => 'وضعیت بسته باید "open" یا "packed" باشد.',
            'invalid-image' => 'یک یا چند فایل بارگذاری‌شده نوع فایل پشتیبانی‌نشده دارند.',
            'invalid-custom-field' => 'فیلد سفارشی ناشناخته ":id".',
            'custom-field-required' => 'فیلد ":field" الزامی است.',
            'invalid-custom-field-value' => 'مقدار ":field" جزو گزینه‌های مجاز نیست.',
            'created' => 'درخواست مرجوعی با موفقیت ایجاد شد.',
            'canceled' => 'درخواست مرجوعی با موفقیت لغو شد.',
            'already-canceled' => 'درخواست مرجوعی قبلاً لغو شده است.',
            'cancel-not-allowed' => 'این درخواست مرجوعی قابل لغو نیست.',
            'close-not-allowed' => 'این درخواست مرجوعی قابل بستن نیست.',
            'reopened' => 'درخواست مرجوعی با موفقیت بازگشایی شد.',
            'reopen-not-allowed' => 'این درخواست مرجوعی قابل بازگشایی نیست.',
            'closed' => 'درخواست مرجوعی با موفقیت بسته شد.',
            'message-sent' => 'پیام با موفقیت ارسال شد.',
            'attachment-not-found' => 'پیوست پیام یافت نشد.',
        ],

        'eu-withdrawal' => [
            'not-found' => 'اعلامیه انصراف یافت نشد.',
            'order-not-found' => 'سفارش یافت نشد یا واجد شرایط انصراف نیست.',
            'too-many-attempts' => 'تلاش‌های بیش از حد. لطفاً پس از :seconds ثانیه دوباره تلاش کنید.',
            'created' => 'اعلامیه انصراف با موفقیت ثبت شد.',
        ],

        'customer-order-shipment' => [
            'id-required' => 'شناسه مرسوله الزامی است',
            'not-found' => 'مرسوله با شناسه ":id" یافت نشد یا متعلق به این مشتری نیست',
            'item-not-found' => 'آیتم مرسوله با شناسه ":id" یافت نشد یا متعلق به این مشتری نیست',
            'invalid-id-format' => 'قالب شناسه نامعتبر است. قالب IRI مانند "/api/shop/customer-order-shipments/1" یا شناسه عددی انتظار می‌رود',
        ],

        'cancel-order' => [
            'order-id-required' => 'برای لغو سفارش، شناسه سفارش الزامی است',
            'not-found' => 'سفارش با شناسه ":id" یافت نشد یا متعلق به این مشتری نیست',
            'cancel-success' => 'سفارش با موفقیت لغو شد',
            'cancel-failed' => 'سفارش قابل لغو نیست. ممکن است قبلاً پردازش، ارسال یا لغو شده باشد',
        ],

        'reorder' => [
            'order-id-required' => 'برای سفارش مجدد، شناسه سفارش الزامی است',
            'not-found' => 'سفارش با شناسه ":id" یافت نشد یا متعلق به این مشتری نیست',
            'reorder-success' => ':count آیتم از سفارش قبلی شما به سبد خرید اضافه شد',
            'no-items-added' => 'هیچ آیتمی به سبد خرید اضافه نشد. ممکن است محصولات ناموجود یا در دسترس نباشند',
        ],

        'customer-downloadable-product' => [
            'id-required' => 'شناسه محصول قابل دانلود مشتری الزامی است',
            'invalid-id-format' => 'قالب شناسه نامعتبر است. قالب IRI مانند "/api/shop/customer-downloadable-products/1" یا شناسه عددی انتظار می‌رود',
            'not-found' => 'محصول قابل دانلود مشتری با شناسه ":id" یافت نشد',

            'download' => [
                'unauthenticated' => 'غیرمجاز: احراز هویت مشتری الزامی است.',
                'not-found' => 'محصول دانلودی یافت نشد.',
                'pending' => 'دانلود در انتظار است. لطفاً تا صدور فاکتور سفارش صبر کنید.',
                'limit-exceeded' => 'از حد مجاز دانلود فراتر رفته‌اید.',
                'no-downloads-remaining' => 'دانلود دیگری در دسترس نیست.',
                'file-not-found' => 'فایل یافت نشد.',
                'link-not-found' => 'پیوند دانلود یافت نشد.',
            ],
        ],

        'customer-invoice' => [
            'id-required' => 'شناسه فاکتور مشتری الزامی است',
            'invalid-id-format' => 'قالب شناسه نامعتبر است. قالب IRI مانند "/api/shop/customer-invoices/1" یا شناسه عددی انتظار می‌رود',
            'not-found' => 'فاکتور مشتری با شناسه ":id" یافت نشد',
        ],

        'auth' => [
            'no-token-provided' => 'هیچ توکن احراز هویتی ارائه نشده است. لطفاً توکن را در هدر Authorization به صورت "Bearer <token>" یا در فیلد input.token ارائه دهید',
            'invalid-or-expired-token' => 'توکن نامعتبر یا منقضی شده است',
            'request-not-found' => 'درخواست در زمینه یافت نشد',
            'token-required' => 'Authentication token is required. Please provide the token either in the GraphQL mutation input field or in the Authorization header as "Bearer <token>"',
            'unknown-resource' => 'منبع ناشناخته',
            'cannot-update-other-profile' => 'غیرمجاز: نمی‌توان پروفایل مشتری دیگری را به‌روزرسانی کرد',
        ],

        'upload' => [
            'invalid-base64' => 'داده‌های تصویر کدگذاری‌شده با base64 نامعتبر است',
            'size-exceeds-limit' => 'اندازه تصویر نباید از 5 مگابایت بیشتر باشد',
            'invalid-format' => 'قالب تصویر نامعتبر. لطفاً تصویر کدگذاری‌شده با base64 را با طرح data URI ارائه دهید (data:image/jpeg;base64,...)',
            'failed' => 'بارگذاری تصویر ناموفق بود',
        ],

        'attribute' => [
            'code-already-exists' => 'کد ویژگی از قبل وجود دارد',
            'option-id-required' => 'هنگام پرس‌وجوی مستقیم گزینه‌های ویژگی، پارامتر "attributeId" الزامی است',
            'id-required' => 'شناسه ویژگی الزامی است',
            'invalid-id-format' => 'قالب شناسه نامعتبر است. قالب IRI مانند "/api/shop/attributes/1" یا شناسه عددی انتظار می‌رود',
            'not-found' => 'ویژگی یافت نشد',
        ],

        'attribute-option' => [
            'id-required' => 'شناسه گزینه ویژگی الزامی است',
            'invalid-id-format' => 'قالب شناسه نامعتبر است. قالب IRI مانند "/api/shop/attribute-options/1" یا شناسه عددی انتظار می‌رود',
            'not-found' => 'گزینه ویژگی یافت نشد',
        ],

        'channel' => [
            'id-required' => 'شناسه کانال الزامی است',
            'invalid-id-format' => 'قالب شناسه نامعتبر است. قالب IRI مانند "/api/shop/channels/1" یا شناسه عددی انتظار می‌رود',
            'not-found' => 'کانال یافت نشد',
        ],

        'country-state' => [
            'id-required' => 'شناسه استان کشور الزامی است',
            'invalid-id-format' => 'قالب شناسه نامعتبر است. قالب IRI مانند "/api/shop/country-states/1" یا شناسه عددی انتظار می‌رود',
            'not-found' => 'استان کشور یافت نشد',
            'country-id-required' => 'شناسه کشور الزامی است',
        ],

        'country' => [
            'id-required' => 'شناسه کشور الزامی است',
            'invalid-id-format' => 'قالب شناسه نامعتبر است. قالب IRI مانند "/api/shop/countries/1" یا شناسه عددی انتظار می‌رود',
            'not-found' => 'کشور یافت نشد',
        ],

        'login' => [
            'invalid-credentials' => 'ایمیل یا رمز عبور نامعتبر',
            'account-suspended' => 'حساب شما معلق شده است',
            'account-inactive' => 'درخواست فعال‌سازی حساب شما منتظر تأیید مدیر است',
            'email-not-verified' => 'ابتدا حساب ایمیل خود را تأیید کنید.',
            'successful' => 'شما با موفقیت وارد شدید',
            'invalid-request' => 'درخواست ورود نامعتبر',
        ],

        'social-login' => [
            'signed-in' => 'با موفقیت وارد شدید.',
            'token-required' => 'توکن ورود اجتماعی لازم است.',
            'invalid-token' => 'توکن ورود اجتماعی نامعتبر یا منقضی شده است. لطفاً دوباره تلاش کنید.',
            'google-not-configured' => 'ورود با Google برای این فروشگاه پیکربندی نشده است.',
            'wrong-audience' => 'این توکن برای برنامه دیگری صادر شده است.',
            'email-required' => 'ارائه‌دهنده هیچ آدرس ایمیلی به اشتراک نگذاشت. لطفاً با ایمیل ثبت‌نام کنید.',
            'account-inactive' => 'درخواست فعال‌سازی حساب شما منتظر تأیید مدیر است',
            'provider-not-supported' => 'این ارائه‌دهنده ورود اجتماعی پشتیبانی نمی‌شود.',
            'provider-disabled' => 'این ارائه‌دهنده ورود اجتماعی فعال نیست.',
        ],

        'checkout' => [
            'invalid-input' => 'داده‌های ورودی نامعتبر برای عملیات تسویه‌حساب',
            'billing-address-required' => 'آدرس صورت‌حساب الزامی است',
            'shipping-address-required' => 'آدرس ارسال برای محموله‌ها الزامی است',
            'address-save-failed' => 'ذخیره آدرس ناموفق بود',
            'address-saved' => 'آدرس با موفقیت ذخیره شد',
            'shipping-method-required' => 'روش ارسال الزامی است',
            'invalid-shipping-method' => 'روش ارسال نامعتبر یا غیرقابل دسترس',
            'shipping-method-save-failed' => 'ذخیره روش ارسال ناموفق بود',
            'shipping-method-saved' => 'روش ارسال با موفقیت ذخیره شد',
            'shipping-method-error' => 'خطا در ذخیره روش ارسال',
            'payment-method-required' => 'روش پرداخت الزامی است',
            'invalid-payment-method' => 'روش پرداخت نامعتبر یا غیرقابل دسترس',
            'payment-method-save-failed' => 'ذخیره روش پرداخت ناموفق بود',
            'payment-method-saved' => 'روش پرداخت با موفقیت ذخیره شد',
            'payment-method-error' => 'خطا در ذخیره روش پرداخت',
            'order-placed' => 'سفارش با موفقیت ثبت شد',
            'order-redirect-required' => 'این روش پرداخت نیاز دارد که خریدار پرداخت را در درگاه تکمیل کند. او را به redirectUrl هدایت کنید؛ سفارش پس از تأیید پرداخت توسط درگاه ایجاد می‌شود.',
            'order-creation-failed' => 'ایجاد سفارش ناموفق بود: شناسه سفارش تهی است یا سفارش ذخیره نشده است',
            'order-retrieval-failed' => 'بازیابی سفارش ایجاد شده ناموفق بود',
            'order-creation-error' => 'ایجاد سفارش ناموفق بود',
            'cart-empty' => 'سبد خرید خالی است',
            'account-suspended' => 'حساب شما معلق شده است. لطفاً با پشتیبانی تماس بگیرید.',
            'account-inactive' => 'حساب شما غیرفعال است. لطفاً با پشتیبانی تماس بگیرید.',
            'minimum-order-not-met' => 'حداقل مبلغ سفارش :amount است',
            'guest-checkout-not-allowed' => 'لطفاً برای تسویه حساب وارد شوید. تسویه حساب مهمان برای این سبد خرید در دسترس نیست.',
            'email-required' => 'آدرس ایمیل برای ایجاد سفارش الزامی است',
            'unknown-operation' => 'عملیات ناشناخته تسویه‌حساب',
        ],

        'customer-addresses' => [
            'token-required' => 'توکن برای دریافت آدرس‌های مشتری الزامی است',
            'invalid-or-expired-token' => 'توکن نامعتبر یا منقضی شده است',
            'token-validation-failed' => 'اعتبارسنجی توکن ناموفق بود',
        ],

        'product' => [
            'not-found' => 'محصول یافت نشد',
            'not-found-with-sku' => 'هیچ محصولی با این SKU یافت نشد',
            'not-found-with-url-key' => 'هیچ محصولی با این کلید URL یافت نشد',
            'parameters-required' => 'حداقل یکی از پارامترهای زیر باید ارائه شود: "sku"، "id"، "urlKey"',
            'type' => 'نوع محصول',
            'attribute-family' => 'خانواده ویژگی',
            'sku' => 'SKU',
            'name' => 'نام',
            'description' => 'توضیحات',
            'short-description' => 'توضیحات کوتاه',
            'status' => 'وضعیت',
            'new' => 'جدید',
            'featured' => 'ویژه',
            'price' => 'قیمت',
            'special-price' => 'قیمت ویژه',
            'weight' => 'وزن',
            'cost' => 'هزینه',
            'length' => 'طول',
            'width' => 'عرض',
            'height' => 'ارتفاع',
            'color' => 'رنگ',
            'size' => 'اندازه',
            'brand' => 'برند',
            'super-attributes' => 'ابرویژگی‌ها',
            'id-required' => 'شناسه محصول الزامی است',
            'invalid-id-format' => 'قالب شناسه نامعتبر است. قالب IRI مانند "/api/shop/products/1" یا شناسه عددی انتظار می‌رود',
        ],

        'downloadable-product' => [
            'download-link-not-found' => 'لینک دانلود یافت نشد یا منقضی شده است',
            'purchased-link-not-found' => 'لینک خریداری شده یافت نشد',
            'file-not-found' => 'فایل یافت نشد',
            'download-successful' => 'فایل برای دانلود آماده است',
            'token-required' => 'توکن دانلود مورد نیاز است',
            'invalid-token' => 'توکن دانلود نامعتبر یا منقضی شده است',
            'token-expired' => 'توکن دانلود منقضی شده است. لطفاً توکن جدیدی ایجاد کنید',
            'access-denied' => 'دسترسی رد شد: شما مجاز به دانلود این فایل نیستید',
            'redirect-external-url' => 'تغییر مسیر به URL دانلود خارجی',
            'file-error' => 'هنگام پردازش درخواست دانلود شما خطایی رخ داد',
            'unauthorized-access' => 'دسترسی غیرمجاز به منبع دانلود',
            'unauthenticated' => 'غیرمجاز: احراز هویت مشتری الزامی است',
            'link-id-required' => 'downloadableLinkPurchasedId الزامی است',
            'link-not-found' => 'پیوند دانلود یافت نشد',
            'not-owner' => 'غیرمجاز: این پیوند دانلود متعلق به شما نیست',
            'pending' => 'دانلود در انتظار است. لطفاً تا صدور فاکتور سفارش صبر کنید.',
            'limit-exceeded' => 'از حد مجاز دانلود فراتر رفته‌اید',
            'no-downloads-left' => 'دانلود دیگری در دسترس نیست',
        ],

        'booking-slot' => [
            'id-required' => 'شناسه محصول رزرو الزامی است.',
            'date-required' => 'تاریخ الزامی است.',
            'product-not-found' => 'محصول رزرو یافت نشد.',
            'invalid-type' => 'این نوع رزرو پشتیبانی نمی‌شود.',
            'invalid-time-range' => 'بازه زمانی نامعتبر است.',
            'invalid-time-format' => 'قالب زمان نامعتبر است.',
        ],

        'resolver' => [
            'no-resolve-info' => 'ResolveInfo مربوط به GraphQL در زمینه در دسترس نیست. تعیین کلاس مدل ممکن نیست.',
            'unknown-resource-class' => 'تعیین کلاس منبع از نوع بازگشتی GraphQL ممکن نیست',
        ],

        'docs' => [
            'openapi-unavailable' => 'نمونه‌سازی OpenAPI Factory ممکن نبود',
        ],

        'locale' => [
            'id-required' => 'شناسه زبان الزامی است',
            'invalid-id-format' => 'قالب شناسه نامعتبر است. قالب IRI مانند "/api/shop/locales/1" یا شناسه عددی انتظار می‌رود',
            'not-found' => 'زبان یافت نشد',
        ],

        'category' => [
            'id-required' => 'شناسه دسته‌بندی الزامی است',
            'invalid-id-format' => 'قالب شناسه نامعتبر است. قالب IRI مانند "/api/shop/categories/1" یا شناسه عددی انتظار می‌رود',
            'not-found' => 'دسته‌بندی یافت نشد',
        ],

        'compare-item' => [
            'id-required' => 'شناسه مورد مقایسه الزامی است',
            'invalid-id-format' => 'قالب شناسه نامعتبر. قالب IRI مانند "/api/shop/compare-items/1" یا شناسه عددی انتظار می رود',
            'not-found' => 'مورد مقایسه یافت نشد',
            'product-id-required' => 'شناسه محصول الزامی است',
            'customer-id-required' => 'شناسه مشتری الزامی است',
            'product-not-found' => 'محصول یافت نشد',
            'customer-not-found' => 'مشتری یافت نشد',
            'already-exists' => 'این محصول قبلاً در فهرست مقایسه شما وجود دارد',
            'delete-all-success' => 'همه آیتم‌های مقایسه با موفقیت حذف شدند',
        ],

        'wishlist' => [
            'id-required' => 'شناسه آیتم فهرست علاقه‌مندی‌ها الزامی است',
            'invalid-id-format' => 'قالب شناسه نامعتبر است. قالب IRI مانند "/api/shop/wishlists/1" یا شناسه عددی انتظار می‌رود',
            'not-found' => 'آیتم فهرست علاقه‌مندی‌ها یافت نشد',
            'product-id-required' => 'شناسه محصول الزامی است',
            'customer-id-required' => 'شناسه مشتری الزامی است',
            'product-not-found' => 'محصول یافت نشد',
            'product-disabled' => 'این محصول در حال حاضر غیرفعال است',
            'customer-not-found' => 'مشتری یافت نشد',
            'already-exists' => 'این محصول قبلاً در فهرست علاقه‌مندی‌های شما وجود دارد',
            'added' => 'آیتم با موفقیت به فهرست علاقه‌مندی‌ها اضافه شد',
            'removed' => 'آیتم با موفقیت از فهرست علاقه‌مندی‌ها حذف شد',
            'product-removed' => 'محصول حذف شد',
            'wishlist-item-id-required' => 'شناسه آیتم فهرست علاقه‌مندی‌ها الزامی است',
            'invalid-quantity' => 'تعداد باید بیشتر از 0 باشد',
            'move-to-cart-missing-options' => 'محصول گزینه‌های الزامی ناقص دارد. لطفاً آن را به‌صورت دستی پیکربندی کنید',
            'moved-to-cart-success' => 'آیتم با موفقیت به سبد خرید منتقل شد',
            'delete-all-success' => 'همه آیتم‌های فهرست علاقه‌مندی‌ها با موفقیت حذف شدند',
        ],

        'gdpr' => [
            'disabled' => 'درخواست‌های داده GDPR غیرفعال است. لطفاً GDPR را از پیکربندی مدیریت فعال کنید.',
            'unauthenticated' => 'برای مدیریت درخواست‌های داده GDPR باید وارد شوید.',
            'not-found' => 'درخواست داده GDPR یافت نشد',
            'type-required' => 'نوع درخواست الزامی است',
            'type-invalid' => 'نوع درخواست باید "delete" یا "update" باشد',
            'message-required' => 'پیام الزامی است',
            'raised' => 'درخواست داده GDPR شما با موفقیت ثبت شد.',
            'revoked' => 'درخواست داده GDPR شما با موفقیت لغو شد.',
            'revoke-not-allowed' => 'این درخواست داده GDPR دیگر قابل لغو نیست.',
            'deleted' => 'درخواست داده GDPR شما با موفقیت حذف شد.',
        ],

        'section' => [
            'id-required' => 'شناسه بخش الزامی است',
            'invalid-id-format' => 'قالب شناسه نامعتبر است. قالب IRI مانند "/api/shop/sections/1" یا شناسه عددی انتظار می‌رود',
            'not-found' => 'بخش یافت نشد',
        ],

        'install' => [
            'starting' => 'در حال شروع نصب API Platform...',
            'completed-success' => '✓ نصب API Platform با موفقیت انجام شد!',
            'failed' => '✗ نصب ناموفق بود: ',

            'provider-already-registered' => 'ℹ Service Provider قبلاً ثبت شده است',
            'provider-not-registered' => '⚠ ثبت خودکار Service Provider ممکن نبود. :provider را به‌صورت دستی به bootstrap/providers.php اضافه کنید.',
            'provider-registered' => '✓ Service Provider در bootstrap/providers.php ثبت شد',
            'provider-file-not-found' => 'فایل یافت نشد: :file',
            'provider-permission-denied' => 'دسترسی رد شد: نوشتن در :file ممکن نیست. لطفاً مجوزهای فایل را بررسی کنید.',

            'config-already-exists' => 'ℹ فایل پیکربندی از قبل وجود دارد',
            'config-published' => '✓ پیکربندی API Platform در config/ منتشر شد',
            'config-source-not-found' => 'فایل پیکربندی منبع یافت نشد: :file',
            'config-permission-denied' => 'دسترسی رد شد: نوشتن در :directory ممکن نیست. لطفاً مجوزهای پوشه را بررسی کنید.',

            'translatable-not-found' => 'ℹ TranslatableModel یافت نشد - رد شد',
            'translatable-already-abstract' => 'ℹ TranslatableModel از قبل abstract است',
            'translatable-made-abstract' => '✓ TranslatableModel به abstract تبدیل شد',
            'translatable-permission-denied' => 'دسترسی رد شد: نوشتن در :file ممکن نیست. لطفاً مجوزهای فایل را بررسی کنید.',

            'providers-already-registered' => 'ℹ ارائه‌دهندگان API Platform از قبل ثبت شده‌اند',
            'providers-registered' => '✓ ارائه‌دهندگان API Platform در bootstrap/app.php ثبت شدند',
            'providers-file-not-found' => 'فایل یافت نشد: :file',
            'providers-permission-denied' => 'دسترسی رد شد: نوشتن در :file ممکن نیست. لطفاً مجوزهای فایل را بررسی کنید.',
            'providers-not-found' => 'بخش ایجاد Application در bootstrap/app.php یافت نشد',

            'assets-published' => '✓ دارایی‌های بسته منتشر شدند',
            'running-migrations' => 'در حال اجرای مایگریشن‌های پایگاه داده...',
            'migrations-completed' => '✓ مایگریشن‌های پایگاه داده تکمیل شد',
            'clearing-caches' => 'در حال پاک‌سازی و بهینه‌سازی حافظه‌های پنهان...',
            'caches-optimized' => '✓ حافظه‌های پنهان پاک‌سازی و بهینه‌سازی شدند',
            'generating-api-key' => 'در حال تولید کلید API...',
            'api-key-generated' => '✓ کلید API با موفقیت تولید شد',
            'api-key-already-exists' => 'ℹ کلید API پیش‌فرض فروشگاه از قبل وجود دارد - تولید رد شد',

            'migrations-error' => 'مایگریشن‌های پایگاه داده ناموفق بود.',
            'migrations-error-running' => 'خطا در اجرای مایگریشن‌های پایگاه داده: :error',
            'cache-clear-error' => 'پاک‌سازی حافظه پنهان ناموفق بود.',
            'cache-optimize-error' => 'بهینه‌سازی ناموفق بود.',
            'cache-error' => 'خطا در پاک‌سازی و بهینه‌سازی حافظه‌های پنهان: :error',
            'api-key-generation-error' => 'تولید کلید API ناموفق بود.',
            'api-key-error' => 'خطا در تولید کلید API: :error',

            'key-management-required' => '--key برای عملیات :action الزامی است',
            'key-rotation-error' => 'خطا در چرخش کلید: :error',
            'key-deactivation-error' => 'خطا در غیرفعال‌سازی کلید: :error',
            'key-not-found' => 'کلید API یافت نشد: :identifier',

            'maintenance-starting' => '🔄 در حال شروع نگهداری کلیدهای API...',
            'maintenance-complete' => '✅ نگهداری کلیدهای API تکمیل شد',
            'cleanup-expired-keys' => '🧹 در حال پاک‌سازی کلیدهای منقضی‌شده...',
            'cleanup-expired-none' => '   ℹ️ کلید منقضی‌شده‌ای برای پاک‌سازی وجود ندارد',
            'invalidate-deprecated' => '⚠️ در حال باطل کردن کلیدهای منسوخ...',
            'invalidate-deprecated-none' => '   ℹ️ کلید منسوخی برای باطل کردن وجود ندارد',
            'notify-expiring' => '📧 در حال ارسال اعلان‌های انقضا...',
            'notify-expiring-none' => '   ℹ️ کلیدی نیازمند اعلان نیست',

            'key-rotated-success' => '✅ کلید با موفقیت چرخانده شد!',
            'key-deactivated-success' => '✅ کلید با موفقیت غیرفعال شد!',
            'deactivation-cancelled' => 'غیرفعال‌سازی لغو شد.',
            'cleanup-cancelled' => 'پاک‌سازی لغو شد.',

            'key-status-title' => 'وضعیت کلید: :name',
            'key-status-active' => 'فعال: :status',
            'key-status-usable' => 'قابل استفاده: :status',
            'key-status-expired' => 'منقضی‌شده: :status',
            'key-status-deprecated' => 'منسوخ: :status',
            'key-status-expires-at' => 'تاریخ انقضا: :expires',
            'key-status-days-until-expiry' => 'روزهای باقی‌مانده تا انقضا: :days',
            'key-status-last-used' => 'آخرین استفاده: :date',
            'key-status-rotated-from' => 'چرخانده‌شده از: :key',
            'key-status-rotated-keys' => 'کلیدهای چرخانده‌شده از این کلید: :count',

            'policy-compliance-summary' => 'خلاصه انطباق با سیاست چرخش کلید API',
            'available-actions' => 'عملیات‌های موجود: rotate, deactivate, cleanup, status, expiring, unused, summary',

            'asset-linked-success' => '✓ دارایی‌های API Platform با موفقیت پیوند داده شدند',
            'symlink-create-failed' => 'ایجاد پیوند نمادین ممکن نبود، به‌جای آن دارایی‌ها کپی می‌شوند...',
            'asset-copy-warning' => 'هشدار: پیوند یا کپی دارایی‌های API Platform ممکن نبود. ممکن است راه‌اندازی دستی لازم باشد.',
            'asset-copied-success' => '✓ دارایی‌های API Platform با موفقیت کپی شدند',

            'key-name-required' => 'نام کلید ویترین فروشگاه نمی‌تواند خالی باشد.',
            'key-generated-success' => 'کلید ویترین فروشگاه با موفقیت تولید شد!',
            'key-details' => 'جزئیات کلید:',
            'key-secure-warning' => 'این کلید را ایمن نگه دارید! از آن در هدر X-STOREFRONT-KEY استفاده خواهد شد.',
            'key-share-warning' => 'این کلید را به‌صورت عمومی به اشتراک نگذارید و آن را در سیستم کنترل نسخه کامیت نکنید.',

            'key-already-exists' => 'کلید ویترین فروشگاهی با نام \':name\' از قبل وجود دارد.',
            'cleanup-success' => '✅ :count کلید منقضی‌شده پاک‌سازی شد',
            'confirm-cleanup' => 'این کار همه کلیدهای منقضی‌شده را به‌صورت نرم حذف می‌کند. ادامه می‌دهید؟',
            'confirm-deactivate' => 'آیا مطمئن هستید که می‌خواهید کلید :name را غیرفعال کنید؟',

            'old-key' => 'کلید قدیمی: :name',
            'old-key-id' => 'شناسه کلید قدیمی: :id',
            'deprecation-date' => 'تاریخ منسوخ شدن: :date',
            'new-key' => 'کلید جدید: :name',
            'new-key-id' => 'شناسه کلید جدید: :id',
            'new-key-value' => 'مقدار کلید جدید: :key',
            'expires-at' => 'تاریخ انقضا: :date',
            'invalid-action' => 'عملیات نامعتبر: :action',
            'key-active' => 'فعال: :status',
            'key-usable' => 'قابل استفاده: :status',
            'key-expired' => 'منقضی‌شده: :status',
            'key-deprecated' => 'منسوخ: :status',
            'key-field-id' => 'شناسه',
            'key-field-name' => 'نام',
            'key-field-key' => 'کلید',
            'key-field-rate-limit' => 'محدودیت نرخ',
            'key-field-status' => 'وضعیت',
            'key-days' => ' روز',
            'key-never' => 'هرگز',
            'key-requests-minute' => ' درخواست/دقیقه',
            'key-unlimited' => 'نامحدود',
            'rate-limit-exceeded' => 'محدودیت نرخ درخواستی از حداکثر مجاز :max بیشتر است. روی :max درخواست/دقیقه تنظیم می‌شود.',

            'vendor-path-not-found' => 'مسیر vendor مربوط به API Platform یافت نشد در: :path',
            'assets-already-linked' => 'دارایی‌های API Platform از قبل پیوند داده شده‌اند در: :path',
            'no-keys-expiring' => 'هیچ کلیدی در :days روز آینده منقضی نمی‌شود',
            'keys-expiring-title' => 'کلیدهایی که در :days روز آینده منقضی می‌شوند:',
            'key-display-format' => '• :name (شناسه: :id)',
            'key-expires-display' => '  انقضا: :date (:days روز باقی‌مانده)',
            'no-unused-keys' => 'کلید استفاده‌نشده‌ای یافت نشد (> :days روز)',
            'unused-keys-title' => 'کلیدهای استفاده‌نشده (> :days روز):',
            'key-last-used-display' => '  آخرین استفاده: :date',
            'total-keys' => 'کل کلیدها: :count',
            'valid-keys' => 'کلیدهای معتبر: :count',
            'expired-keys' => 'کلیدهای منقضی‌شده: :count',
            'deprecated-keys' => 'کلیدهای منسوخ: :count',
            'keys-expiring-soon' => 'کلیدهایی که به‌زودی منقضی می‌شوند (7 روز): :count',
            'unused-keys-summary' => 'کلیدهای استفاده‌نشده (90 روز): :count',
            'recently-rotated' => 'اخیراً چرخانده‌شده (30 روز): :count',
            'cleanup-success-message' => '   ✅ :count کلید منقضی‌شده پاک‌سازی شد',
            'invalidate-success-message' => '   ✅ :count کلید منسوخ باطل شد',
            'notify-success-message' => '   ✅ :count اعلان انقضا ارسال شد',
            'notify-failed-message' => '   ⚠️ ارسال اعلان درباره :key ناموفق بود: :error',
            'publish-assets-warning' => 'هشدار: انتشار دارایی‌های بسته ممکن نبود. :error',
            'env-config-saved' => '✓ پیکربندی محیط API Platform با موفقیت ذخیره شد',
            'api-endpoints' => 'نقاط پایانی API:',
            'api-documentation' => 'مستندات API: :url',
            'api-landing-page' => 'صفحه اصلی API: :url',
            'graphql-playground' => 'GraphQL Playground: :url',
            'rest-api-storefront' => 'Swagger رابط REST API فروشگاه: :url',
            'rest-api-admin' => 'Swagger رابط REST API مدیریت: :url',
            'key-rotation-invalid-key' => 'چرخش کلید نامعتبر یا منقضی‌شده ممکن نیست',
            'env-file-not-found' => 'فایل .env یافت نشد',
            'env-permission-denied' => 'دسترسی رد شد: امکان نوشتن در فایل .env وجود ندارد. لطفاً مجوزهای فایل را بررسی کنید.',
        ],

        'attribute-translation' => [
            'id-required' => 'شناسه ترجمه ویژگی الزامی است',
            'invalid-id-format' => 'قالب شناسه نامعتبر است. قالب IRI مانند "/api/shop/attribute-translations/1" یا شناسه عددی انتظار می‌رود',
            'not-found' => 'ترجمه ویژگی یافت نشد',
        ],

        'currency' => [
            'id-required' => 'شناسه ارز الزامی است',
            'invalid-id-format' => 'قالب شناسه نامعتبر است. قالب IRI مانند "/api/shop/currencies/1" یا شناسه عددی انتظار می‌رود',
            'not-found' => 'ارز یافت نشد',
        ],

        'customer-address' => [
            'id-required' => 'شناسه آدرس مشتری الزامی است',
            'invalid-id-format' => 'قالب شناسه نامعتبر است. قالب IRI مانند "/api/shop/customer-addresses/1" یا شناسه عددی انتظار می‌رود',
        ],

        'customer-order-shipment-item' => [
            'id-required' => 'شناسه قلم محموله الزامی است',
            'invalid-id-format' => 'قالب شناسه نامعتبر است. قالب IRI مانند "/api/shop/customer-order-shipment-items/1" یا شناسه عددی انتظار می‌رود',
            'not-found' => 'قلم محموله یافت نشد',
        ],

        'page' => [
            'id-required' => 'شناسه صفحه الزامی است',
            'invalid-id-format' => 'قالب شناسه نامعتبر است. قالب IRI مانند "/api/shop/pages/1" یا شناسه عددی انتظار می‌رود',
            'not-found' => 'صفحه یافت نشد',
        ],

        'page-translation' => [
            'id-required' => 'شناسه ترجمه صفحه الزامی است',
            'invalid-id-format' => 'قالب شناسه نامعتبر است. قالب IRI مانند "/api/shop/page-translations/1" یا شناسه عددی انتظار می‌رود',
            'not-found' => 'ترجمه صفحه یافت نشد',
        ],

        'product-bundle-option' => [
            'id-required' => 'شناسه گزینه بسته الزامی است',
            'invalid-id-format' => 'قالب شناسه نامعتبر است. قالب IRI مانند "/api/shop/product-bundle-options/1" یا شناسه عددی انتظار می‌رود',
            'not-found' => 'گزینه بسته یافت نشد',
        ],

        'product-bundle-option-product' => [
            'id-required' => 'شناسه محصول گزینه بسته الزامی است',
            'invalid-id-format' => 'قالب شناسه نامعتبر است. قالب IRI مانند "/api/shop/product-bundle-option-products/1" یا شناسه عددی انتظار می‌رود',
            'not-found' => 'محصول گزینه بسته یافت نشد',
        ],

        'product-downloadable-link' => [
            'id-required' => 'شناسه لینک دانلودی الزامی است',
            'invalid-id-format' => 'قالب شناسه نامعتبر است. قالب IRI مانند "/api/shop/product-downloadable-links/1" یا شناسه عددی انتظار می‌رود',
            'not-found' => 'لینک دانلودی یافت نشد',
        ],

        'product-downloadable-sample' => [
            'id-required' => 'شناسه نمونه دانلودی الزامی است',
            'invalid-id-format' => 'قالب شناسه نامعتبر است. قالب IRI مانند "/api/shop/product-downloadable-samples/1" یا شناسه عددی انتظار می‌رود',
            'not-found' => 'نمونه دانلودی یافت نشد',
        ],

        'product-grouped-product' => [
            'id-required' => 'شناسه محصول گروهی الزامی است',
            'invalid-id-format' => 'قالب شناسه نامعتبر است. قالب IRI مانند "/api/shop/product-grouped-products/1" یا شناسه عددی انتظار می‌رود',
            'not-found' => 'محصول گروهی یافت نشد',
        ],

        'product-images' => [
            'id-required' => 'شناسه تصویر محصول الزامی است',
            'invalid-id-format' => 'قالب شناسه نامعتبر است. قالب IRI مانند "/api/shop/product-images/1" یا شناسه عددی انتظار می‌رود',
            'not-found' => 'تصویر محصول یافت نشد',
        ],

        'product-videos' => [
            'id-required' => 'شناسه ویدیوی محصول الزامی است',
            'invalid-id-format' => 'قالب شناسه نامعتبر است. قالب IRI مانند "/api/shop/product-videos/1" یا شناسه عددی انتظار می‌رود',
            'not-found' => 'ویدیوی محصول یافت نشد',
        ],

        'theme-customization' => [
            'id-required' => 'شناسه سفارشی‌سازی پوسته الزامی است',
            'invalid-id-format' => 'قالب شناسه نامعتبر است. قالب IRI مانند "/api/shop/theme-customizations/1" یا شناسه عددی انتظار می‌رود',
            'not-found' => 'سفارشی‌سازی پوسته یافت نشد',
        ],

        'unknown-resource' => [
            'id-required' => 'شناسه منبع الزامی است',
            'not-found' => 'منبع یافت نشد',
        ],
    ],

    'admin' => [
        // login / logout / forgot-password / profile-update keys removed in the
        // 2026-05-27 auth refactor — admin clients now authenticate via
        // pre-issued integration tokens (Bearer header, AdminApiGuard).
        'profile' => [
            'unauthenticated' => 'احراز هویت نشده‌اید.',
        ],

        'dashboard' => [
            'invalid-type' => 'نوع آمار داشبورد ناشناخته است. انواع معتبر: over-all, today, stock-threshold-products, total-sales, top-selling-products, top-customers.',
        ],

        'rma' => [
            'not-found' => 'درخواست مرجوعی یافت نشد.',
            'invalid-input' => 'جزئیات مرجوعی وجود ندارد یا نامعتبر است.',
            'invalid-order' => 'سفارش انتخاب‌شده معتبر نیست.',
            'invalid-item' => 'آیتم انتخاب‌شده واجد شرایط مرجوعی نیست.',
            'invalid-resolution-type' => 'نوع راه‌حل باید "return" یا "cancel_items" باشد.',
            'qty-exceeds' => 'تعداد درخواستی از تعداد قابل مرجوع بیشتر است.',
            'created' => 'درخواست مرجوعی با موفقیت ایجاد شد.',
            'status-updated' => 'وضعیت مرجوعی با موفقیت به‌روزرسانی شد.',
            'invalid-status' => 'وضعیت مرجوعی نامعتبر است.',
            'reopened' => 'درخواست مرجوعی با موفقیت بازگشایی شد.',
            'reopen-not-allowed' => 'این درخواست مرجوعی قابل بازگشایی نیست.',
            'refund-failed' => 'ایجاد بازپرداخت برای این مرجوعی ممکن نبود.',
            'message-sent' => 'پیام با موفقیت ارسال شد.',
            'no-permission' => 'شما مجوز انجام این عمل را ندارید.',
            'reason-not-found' => 'دلیل مرجوعی یافت نشد.',
            'reason-deleted' => 'دلیل مرجوعی با موفقیت حذف شد.',
            'status-not-found' => 'وضعیت مرجوعی یافت نشد.',
            'status-deleted' => 'وضعیت مرجوعی با موفقیت حذف شد.',
            'status-default-undeletable' => 'وضعیت مرجوعی پیش‌فرض قابل حذف نیست.',
            'rule-not-found' => 'قانون مرجوعی یافت نشد.',
            'rule-deleted' => 'قانون مرجوعی با موفقیت حذف شد.',
            'custom-field-not-found' => 'فیلد سفارشی مرجوعی یافت نشد.',
            'custom-field-deleted' => 'فیلد سفارشی مرجوعی با موفقیت حذف شد.',
            'custom-field-options-required' => 'برای این نوع فیلد حداقل یک گزینه الزامی است.',
            'mass-indices-required' => 'حداقل یک شناسه الزامی است.',
            'mass-delete-success' => 'آیتم‌های انتخاب‌شده حذف شدند.',
            'mass-update-success' => 'آیتم‌های انتخاب‌شده به‌روزرسانی شدند.',
            'mass-value-invalid' => 'مقدار وضعیت باید 0 یا 1 باشد.',
            'image-not-found' => 'تصویر مرجوعی یافت نشد.',
            'attachment-not-found' => 'پیوست پیام یافت نشد.',
        ],

        'eu-withdrawal' => [
            'not-found' => 'اعلامیه انصراف یافت نشد.',
            'no-permission' => 'شما مجوز مدیریت انصراف‌های اتحادیه اروپا را ندارید.',
            'declined' => 'انصراف به‌عنوان ردشده علامت‌گذاری شد.',
            'refunded' => 'انصراف به‌عنوان بازپرداخت‌شده علامت‌گذاری شد.',
            'confirmation-resent' => 'ایمیل تأیید با موفقیت دوباره ارسال شد.',
            'confirmation-failed' => 'ارسال ایمیل تأیید ممکن نبود.',
        ],

        'reporting' => [
            'invalid-entity' => 'موجودیت گزارش‌گیری ناشناخته.',
            'invalid-type' => 'نوع آمار گزارش‌گیری برای این موجودیت ناشناخته است.',
            'export-format-unsupported' => 'فقط قالب‌های خروجی csv، xls و xlsx پشتیبانی می‌شوند.',
        ],

        'configuration' => [
            'unauthenticated' => 'احراز هویت نشده‌اید.',
            'no-permission' => 'شما مجوز به‌روزرسانی پیکربندی را ندارید.',
            'slug-required' => 'پارامتر پرس‌وجوی `slug` الزامی است.',
            'slug-not-found' => 'هیچ بخش پیکربندی برای slug داده‌شده ثبت نشده است.',
            'values-required' => 'حداقل یک مقدار در `values` الزامی است.',
            'scope-escape' => 'کلید `:key` خارج از زیردرخت slug درخواستی است.',
            'unknown-field' => 'کلید `:key` یک فیلد پیکربندی ثبت‌شده نیست.',
            'validation-failed' => 'اعتبارسنجی یک یا چند مقدار پیکربندی ناموفق بود.',
            'file-upload-rest-only' => 'بارگذاری فایل باینری برای فیلد `:field` فقط از طریق نقطه پایانی multipart در REST پشتیبانی می‌شود، نه GraphQL.',
            'custom-view-readonly' => 'فیلد `:field` یک فیلد سفارشی رندرشده با blade است و از طریق API قابل به‌روزرسانی نیست.',
            'update-success' => 'پیکربندی با موفقیت به‌روزرسانی شد.',
        ],

        'customer' => [
            'not-found' => 'مشتری یافت نشد.',
            'no-permission' => 'شما مجوز انجام این عمل را ندارید.',
            'has-active-orders' => 'مشتری سفارش در انتظار یا در حال پردازش دارد و قابل حذف نیست.',
            'deleted' => 'مشتری با موفقیت حذف شد.',
            'mass-delete-indices-required' => 'حداقل یک شناسه مشتری الزامی است.',
            'mass-delete-success' => 'مشتریان حذف شدند.',
            'mass-update-indices-required' => 'حداقل یک شناسه مشتری الزامی است.',
            'mass-update-value-invalid' => 'مقدار وضعیت باید 0 یا 1 باشد.',
            'mass-update-success' => 'وضعیت مشتری به‌روزرسانی شد.',

            'address' => [
                'not-found' => 'آدرس یافت نشد.',
                'not-owned' => 'این آدرس متعلق به مشتری مشخص‌شده نیست.',
                'customer-id-required' => 'برای شناسایی مالک آدرس، customerId الزامی است.',
                'deleted' => 'آدرس با موفقیت حذف شد.',
            ],

            'note' => [
                'empty' => 'متن یادداشت الزامی است.',
                'created' => 'یادداشت با موفقیت اضافه شد.',
            ],

            'impersonate' => [
                'success' => 'توکن جعل هویت صادر شد.',
            ],

            'review' => [
                'not-found' => 'نظر یافت نشد.',
                'no-permission' => 'شما مجوز انجام این عمل را ندارید.',
                'invalid-status' => 'وضعیت باید یکی از این موارد باشد: pending, approved, disapproved.',
                'updated' => 'نظر با موفقیت به‌روزرسانی شد.',
                'deleted' => 'نظر با موفقیت حذف شد.',
                'mass-delete-indices-required' => 'حداقل یک شناسه نظر الزامی است.',
                'mass-delete-success' => 'نظرات حذف شدند.',
                'mass-update-indices-required' => 'حداقل یک شناسه نظر الزامی است.',
                'mass-update-value-invalid' => 'مقدار وضعیت باید یکی از این موارد باشد: pending, approved, disapproved.',
                'mass-update-success' => 'وضعیت نظر به‌روزرسانی شد.',
            ],

            'group' => [
                'not-found' => 'گروه مشتری یافت نشد.',
                'deleted' => 'گروه مشتری با موفقیت حذف شد.',
                'is-system' => 'گروه‌های مشتری سیستمی قابل حذف نیستند.',
                'has-customers' => 'گروه مشتری دارای مشتریان مرتبط است و قابل حذف نیست.',
                'system-code-immutable' => 'کد گروه مشتری سیستمی قابل تغییر نیست.',
                'system-flag-immutable' => 'پرچم is_user_defined گروه مشتری سیستمی قابل تغییر نیست.',
                'mass-delete-indices-required' => 'حداقل یک شناسه گروه مشتری الزامی است.',
                'mass-delete-success' => 'گروه‌های مشتری حذف شدند.',
            ],

            'gdpr' => [
                'not-found' => 'درخواست GDPR یافت نشد.',
                'no-permission' => 'شما مجوز انجام این عمل را ندارید.',
                'deleted' => 'درخواست GDPR با موفقیت حذف شد.',
                'invalid-status' => 'وضعیت باید یکی از این موارد باشد: pending, processing, declined, approved, revoked.',
                'no-changes' => 'حداقل یکی از وضعیت یا پیام الزامی است.',
                'already-processed' => 'این درخواست GDPR قبلاً پردازش شده است و قابل اجرای مجدد نیست.',

                'process' => [
                    'approved' => 'درخواست GDPR تأیید شد.',
                    'deleted' => 'درخواست GDPR تأیید و مشتری حذف شد.',
                ],
            ],
        ],

        'order' => [
            'not-found' => 'سفارش یافت نشد.',

            'reorder' => [
                'success' => 'سفارش مجدد با موفقیت انجام شد. یک سبد خرید پیش‌نویس جدید ایجاد شد.',
                'cannot-reorder' => 'این سفارش قابل سفارش مجدد نیست (سفارش مهمان است یا یک یا چند آیتم دیگر قابل فروش نیستند).',
                'failed' => 'سفارش مجدد ناموفق بود. لطفاً دوباره تلاش کنید.',
                'guest-not-supported' => 'سفارش مجدد برای سفارش‌های مهمان پشتیبانی نمی‌شود.',
                'items-not-saleable' => 'یک یا چند آیتم در این سفارش دیگر برای خرید در دسترس نیستند.',
                'no-permission' => 'شما مجوز ایجاد سفارش را ندارید.',
                'disabled-in-settings' => 'سفارش مجدد توسط مدیر در تنظیمات فروشگاه غیرفعال است.',
            ],

            'actions' => [
                'cancel' => [
                    'closed' => 'سفارش‌های بسته‌شده قابل لغو نیستند.',
                    'fraud' => 'سفارش‌های متقلبانه قابل لغو نیستند.',
                    'nothing-to-cancel' => 'چیزی برای لغو در این سفارش وجود ندارد.',
                    'already-processed' => 'برای این سفارش قبلاً فاکتور صادر شده یا ارسال شده است و دیگر قابل لغو نیست. می‌توانید به‌جای آن بازپرداخت صادر کنید.',
                    'no-permission' => 'شما مجوز لغو سفارش‌ها را ندارید.',
                    'success' => 'سفارش با موفقیت لغو شد.',
                    'failed' => 'لغو سفارش ممکن نبود.',
                ],

                'comment' => [
                    'empty' => 'نظر الزامی است.',
                    'success' => 'نظر به سفارش اضافه شد.',
                    'failed' => 'افزودن نظر ممکن نبود.',
                ],

                'invoice' => [
                    'closed' => 'برای سفارش‌های بسته‌شده نمی‌توان فاکتور صادر کرد.',
                    'fraud' => 'برای سفارش‌های متقلبانه نمی‌توان فاکتور صادر کرد.',
                    'nothing-to-invoice' => 'چیزی برای صدور فاکتور در این سفارش وجود ندارد.',
                    'already-invoiced' => 'قبلاً برای این سفارش فاکتور ایجاد شده است.',
                    'paypal-standard-blocked' => 'برای سفارش‌هایی که از طریق PayPal Standard پرداخت شده‌اند نمی‌توان فاکتور ایجاد کرد.',
                    'no-permission' => 'شما مجوز ایجاد فاکتور را ندارید.',
                    'items-required' => 'حداقل یک آیتم با تعداد مثبت الزامی است.',
                    'qty-exceeds' => 'تعداد درخواستی (:requested) از تعداد موجود (:available) برای SKU :sku بیشتر است.',
                    'not-found' => 'فاکتور یافت نشد.',
                    'success' => 'فاکتور با موفقیت ایجاد شد.',
                    'failed' => 'ایجاد فاکتور ممکن نبود.',
                    'pdf-failed' => 'تولید PDF فاکتور ممکن نبود.',
                    'email-invalid' => 'یک ایمیل گیرنده معتبر الزامی است.',
                    'duplicate-sent' => 'ایمیل فاکتور به :email ارسال شد.',
                    'mass-status-indices-required' => 'حداقل یک شناسه فاکتور الزامی است.',
                    'mass-status-value-invalid' => 'وضعیت باید یکی از این موارد باشد: pending, paid, overdue.',
                    'mass-status-success' => 'وضعیت فاکتور با موفقیت به‌روزرسانی شد.',
                ],

                'shipment' => [
                    'closed' => 'سفارش‌های بسته‌شده قابل ارسال نیستند.',
                    'fraud' => 'سفارش‌های متقلبانه قابل ارسال نیستند.',
                    'nothing-to-ship' => 'چیزی برای ارسال در این سفارش وجود ندارد.',
                    'no-permission' => 'شما مجوز ارسال سفارش‌ها را ندارید.',
                    'source-required' => 'منبع موجودی الزامی است.',
                    'items-required' => 'حداقل یک آیتم با تعداد مثبت الزامی است.',
                    'qty-exceeds' => 'تعداد درخواستی (:requested) از تعداد موجود (:available) برای SKU :sku بیشتر است.',
                    'inventory-insufficient' => 'موجودی در منبع انتخاب‌شده برای SKU :sku کافی نیست.',
                    'not-found' => 'مرسوله یافت نشد.',
                    'success' => 'مرسوله با موفقیت ایجاد شد.',
                    'failed' => 'ایجاد مرسوله ممکن نبود.',
                ],

                'refund' => [
                    'closed' => 'سفارش‌های بسته‌شده قابل بازپرداخت نیستند.',
                    'fraud' => 'سفارش‌های متقلبانه قابل بازپرداخت نیستند.',
                    'nothing-to-refund' => 'چیزی برای بازپرداخت در این سفارش باقی نمانده است.',
                    'no-permission' => 'شما مجوز ایجاد بازپرداخت را ندارید.',
                    'qty-exceeds' => 'تعداد درخواستی (:requested) از تعداد موجود (:available) برای SKU :sku بیشتر است.',
                    'amount-zero' => 'مبلغ بازپرداخت محاسبه‌شده صفر است. تعداد، هزینه ارسال یا مقادیر تعدیل را تنظیم کنید.',
                    'amount-exceeds-max' => 'مبلغ بازپرداخت (:amount) از حداکثر مبلغ قابل بازپرداخت (:max) بیشتر است.',
                    'not-found' => 'بازپرداخت یافت نشد.',
                    'success' => 'بازپرداخت با موفقیت ایجاد شد.',
                    'failed' => 'ایجاد بازپرداخت ممکن نبود.',
                ],
            ],
        ],

        'appearance' => [
            'no-permission' => 'شما مجوز مدیریت ظاهر را ندارید.',

            'theme' => [
                'not-found' => 'پوسته یافت نشد.',
                'not-installed' => 'این پوسته نصب نشده است.',
                'channel-ids-required' => 'حداقل یک کانال الزامی است.',
                'unknown-channel' => 'یک یا چند کانال وجود ندارند.',
                'activated' => 'پوسته با موفقیت فعال شد.',
            ],

            'section' => [
                'not-found' => 'بخش یافت نشد.',
                'invalid-payload' => 'محتوای بخش پشتیبانی نمی‌شود.',
                'footer-links-exists' => 'این کانال قبلاً یک بخش پیوندهای پاورقی دارد.',
                'options-required' => 'گزینه‌ها الزامی هستند.',
                'status-required' => 'وضعیت الزامی است.',
                'section-ids-required' => 'حداقل یک بخش الزامی است.',
                'reorder-single-scope' => 'بخش‌ها را فقط می‌توان در هر بار درون یک پوسته و کانال مرتب‌سازی مجدد کرد.',
                'reorder-incomplete' => 'مرتب‌سازی مجدد به همه بخش‌های پوسته و کانال، به ترتیبی که باید نمایش داده شوند، نیاز دارد.',
                'unknown-section' => 'یک یا چند بخش وجود ندارند.',
                'file-required' => 'فایل الزامی است.',
                'created' => 'بخش با موفقیت ایجاد شد.',
                'updated' => 'بخش با موفقیت به‌روزرسانی شد.',
                'deleted' => 'بخش با موفقیت حذف شد.',
                'draft-saved' => 'تغییرات بخش با موفقیت آماده انتشار شد.',
                'status-staged' => 'وضعیت بخش با موفقیت آماده انتشار شد.',
                'order-staged' => 'ترتیب بخش‌ها با موفقیت آماده انتشار شد.',
                'duplicated' => 'بخش با موفقیت کپی شد.',
                'published' => 'تغییرات بخش با موفقیت منتشر شد.',
                'discarded' => 'تغییرات بخش با موفقیت کنار گذاشته شد.',
                'media-uploaded' => 'رسانه با موفقیت بارگذاری شد.',
            ],
        ],

        'sales' => [
            'no-permission' => 'شما مجوز مشاهده این منبع فروش را ندارید.',

            'invoice' => [
                'not-found' => 'فاکتور یافت نشد.',
                'no-permission' => 'شما مجوز مشاهده فاکتورها را ندارید.',
            ],

            'shipment' => [
                'not-found' => 'مرسوله یافت نشد.',
                'no-permission' => 'شما مجوز مشاهده مرسوله‌ها را ندارید.',
            ],

            'refund' => [
                'not-found' => 'بازپرداخت یافت نشد.',
                'no-permission' => 'شما مجوز مشاهده بازپرداخت‌ها را ندارید.',
                'export-format-unsupported' => 'فقط خروجی‌های csv، xls و xlsx پشتیبانی می‌شوند.',
            ],

            'transaction' => [
                'not-found' => 'تراکنش یافت نشد.',
                'no-permission' => 'شما مجوز مشاهده تراکنش‌ها را ندارید.',

                'create' => [
                    'invoice-required' => 'فیلد invoiceId الزامی است.',
                    'payment-method-required' => 'فیلد paymentMethod الزامی است.',
                    'amount-invalid' => 'فیلد amount الزامی است و باید عددی باشد.',
                    'invoice-missing' => 'فاکتور مشخص‌شده یافت نشد.',
                    'already-paid' => 'این فاکتور قبلاً به‌طور کامل پرداخت شده است.',
                    'amount-exceeds' => 'مبلغ پرداخت از جمع کل فاکتور بیشتر است.',
                    'amount-zero' => 'مبلغ پرداخت باید بیشتر از صفر باشد.',
                ],
            ],

            'booking' => [
                'not-found' => 'رزرو یافت نشد.',
                'no-permission' => 'شما مجوز مشاهده رزروها را ندارید.',
            ],

            'export' => [
                'format-unsupported' => 'فقط خروجی‌های csv، xls و xlsx پشتیبانی می‌شوند.',
            ],
        ],

        'cart' => [
            'not-found' => 'سبد خرید یافت نشد.',
            'not-draft' => 'این سبد خرید از طریق API مدیریت قابل ویرایش نیست.',
            'product-required' => 'productId الزامی است.',
            'product-not-found' => 'محصول یافت نشد.',
            'booking-unsupported' => 'محصولات رزرو را نمی‌توان به سفارش پیش‌نویس مدیر اضافه کرد. خرید رزرو باید از طریق ویترین فروشگاه مشتری انجام شود.',
            'product-not-saleable' => 'این محصول برای فروش در دسترس نیست (ناموجود یا غیرفعال) و قابل افزودن به سفارش نیست.',
            'qty-required' => 'qty الزامی است و باید یک شیء غیرخالی به‌صورت itemId => quantity باشد.',
            'cart-item-required' => 'cartItemId الزامی است.',
            'item-added' => 'آیتم به سبد خرید اضافه شد.',
            'item-add-failed' => 'افزودن آیتم به سبد خرید ممکن نبود.',
            'item-updated' => 'آیتم‌های سبد خرید به‌روزرسانی شدند.',
            'item-update-failed' => 'به‌روزرسانی آیتم‌های سبد خرید ممکن نبود.',
            'item-removed' => 'آیتم از سبد خرید حذف شد.',
            'item-remove-failed' => 'حذف آیتم سبد خرید ممکن نبود.',
            'address-saved' => 'آدرس ذخیره شد.',
            'address-required' => 'آدرس صورت‌حساب الزامی است.',
            'address-incomplete' => 'برای ذخیره آدرس، :field الزامی است.',
            'coupon-code-required' => 'کد کوپن الزامی است.',
            'coupon-applied' => 'کوپن اعمال شد.',
            'coupon-already-applied' => 'کوپن قبلاً روی این سبد خرید اعمال شده است.',
            'coupon-not-found' => 'کوپن یافت نشد یا غیرفعال است.',
            'coupon-removed' => 'کوپن حذف شد.',
            'coupon-error' => 'هنگام اعمال کوپن مشکلی پیش آمد.',
            'unknown-error' => 'مشکلی پیش آمد.',
            'draft-created' => 'سبد خرید پیش‌نویس ایجاد شد.',
            'draft-failed' => 'ایجاد سبد خرید پیش‌نویس ممکن نبود.',
            'addresses-required-for-shipping' => 'پیش از انتخاب روش ارسال، آدرس‌ها باید ذخیره شوند.',
            'shipping-required-for-payment' => 'روش ارسال باید پیش از روش پرداخت انتخاب شود.',
            'shipping-method-required' => 'روش ارسال الزامی است.',
            'shipping-method-saved' => 'روش ارسال ذخیره شد.',
            'shipping-method-failed' => 'ذخیره روش ارسال ممکن نبود.',
            'payment-method-required' => 'روش پرداخت الزامی است.',
            'payment-method-saved' => 'روش پرداخت ذخیره شد.',
            'payment-method-failed' => 'ذخیره روش پرداخت ممکن نبود.',

            'place-order' => [
                'success' => 'سفارش با موفقیت ثبت شد.',
                'failed' => 'ثبت سفارش ممکن نبود.',
                'error' => 'ثبت سفارش ممکن نبود: سبد خرید دارای خطا است.',
                'minimum-order-error' => 'حداقل مبلغ سفارش :amount است. پیش از ثبت سفارش آیتم‌های بیشتری اضافه کنید.',
                'empty-cart' => 'سبد خرید خالی است — پیش از ثبت سفارش آیتم اضافه کنید.',
                'addresses-required' => 'پیش از ثبت سفارش، آدرس‌های صورت‌حساب و ارسال باید ذخیره شوند.',
                'shipping-required' => 'پیش از ثبت سفارش باید یک روش ارسال انتخاب شود.',
                'payment-required' => 'پیش از ثبت سفارش باید یک روش پرداخت انتخاب شود.',
                'payment-method-unsupported' => 'برای سفارش‌های ثبت‌شده توسط مدیر فقط cashondelivery و moneytransfer پشتیبانی می‌شوند.',
            ],
        ],

        'product' => [
            'not-found' => 'محصول یافت نشد.',

            'mass-delete-success' => 'محصولات با موفقیت حذف شدند.',
            'mass-update-status-success' => 'وضعیت محصولات با موفقیت به‌روزرسانی شد.',
            'indices-required' => 'فیلد indices الزامی است و باید یک آرایه غیرخالی باشد.',
            'indices-invalid' => 'فیلد indices باید آرایه‌ای از شناسه‌های عددی صحیح محصولات باشد.',
            'value-invalid' => 'فیلد value الزامی است و باید 0 یا 1 باشد.',
            'mass-delete-failed' => 'حذف یک یا چند محصول ممکن نبود.',
            'no-permission' => 'شما مجوز مدیریت محصولات را ندارید.',

            'copy-success' => 'محصول با موفقیت کپی شد.',
            'copy-failed' => 'کپی محصول ناموفق بود.',
            'copy-variant-not-supported' => 'انواع محصولات قابل پیکربندی قابل کپی نیستند. به‌جای آن محصول قابل پیکربندی والد را کپی کنید.',

            'create' => [
                'created' => 'محصول با موفقیت ایجاد شد.',
                'create-failed' => 'ایجاد محصول ناموفق بود.',
                'sku-required' => 'فیلد sku الزامی است.',
                'sku-unique' => 'این sku قبلاً استفاده شده است.',
                'sku-invalid' => 'sku فقط می‌تواند شامل حروف، اعداد و خط تیره باشد (مثلاً "sp-001").',
                'attribute-family-required' => 'فیلد attribute_family_id الزامی است.',
                'attribute-family-not-found' => 'خانواده ویژگی انتخاب‌شده وجود ندارد.',
                'type-required' => 'فیلد type الزامی است.',
                'type-not-yet-supported' => 'نوع محصول ":type" توسط این API پشتیبانی نمی‌شود. انواع مجاز: simple, virtual, downloadable, grouped, bundle, configurable, booking.',
                'super-attributes-required' => 'هنگامی که type=configurable است، فیلد super_attributes الزامی است. نگاشتی از کد ویژگی (یا شناسه) به option_ids ارائه دهید، برای مثال { "color": [1,2], "size": [4,5] }.',
                'super-attributes-invalid' => 'فیلد super_attributes باید نگاشتی غیرخالی از کد ویژگی (یا شناسه) به فهرستی غیرخالی از option_ids باشد.',
                'no-permission' => 'شما مجوز ایجاد محصولات را ندارید.',
            ],

            'update' => [
                'updated' => 'محصول با موفقیت به‌روزرسانی شد.',
                'update-failed' => 'به‌روزرسانی محصول ناموفق بود.',
                'id-required' => 'شناسه محصول الزامی است.',
                'url-key-required' => 'فیلد url_key نمی‌تواند خالی باشد.',
                'url-key-unique' => 'این url_key قبلاً توسط محصول دیگری استفاده شده است.',
                'special-price-invalid' => 'special_price باید عددی کمتر از price باشد.',
                'special-price-date-range-invalid' => 'special_price_to باید برابر یا پس از special_price_from باشد.',
                'boolean-field-invalid' => 'فیلد :field باید 0 یا 1 باشد.',
                'categories-invalid' => 'فیلد categories باید آرایه‌ای از شناسه‌های دسته‌بندی باشد.',
                'channels-invalid' => 'فیلد channels باید آرایه‌ای از شناسه‌های کانال باشد.',
                'super-attributes-cannot-change' => 'پس از ایجاد محصول، super_attributes قابل تغییر نیست.',
                'sub-resource-stripped-images' => 'تصاویر باید از طریق POST /api/admin/catalog/products/{id}/images مدیریت شوند. فیلد images نادیده گرفته شد.',
                'sub-resource-stripped-inventories' => 'موجودی‌ها باید از طریق PUT /api/admin/catalog/products/{id}/inventories مدیریت شوند. فیلد inventories نادیده گرفته شد.',
                'sub-resource-stripped-customer-group-prices' => 'قیمت‌های گروه مشتری باید از طریق /api/admin/catalog/products/{id}/customer-group-prices مدیریت شوند. فیلد customer_group_prices نادیده گرفته شد.',
                'translations-single-locale' => 'فقط زبان درخواستی به‌روزرسانی شد. برای به‌روزرسانی زبان(های) :locales، برای هر کدام یک درخواست جداگانه با ?locale=<code> ارسال کنید.',
            ],

            'delete' => [
                'deleted' => 'محصول با موفقیت حذف شد.',
                'delete-failed' => 'حذف محصول ناموفق بود.',
            ],

            'image' => [
                'uploaded' => 'تصویر محصول با موفقیت بارگذاری شد.',
                'updated' => 'تصویر محصول با موفقیت به‌روزرسانی شد.',
                'update-empty' => 'برای به‌روزرسانی، یک متن جایگزین یا موقعیت ارسال کنید.',
                'reordered' => 'ترتیب تصاویر محصول با موفقیت تغییر کرد.',
                'deleted' => 'تصویر محصول با موفقیت حذف شد.',
                'image-required' => 'یک فایل تصویر الزامی است (فیلد multipart "image").',
                'image-invalid-type' => 'نوع تصویر باید یکی از این موارد باشد: bmp, jpeg, jpg, png, webp.',
                'image-too-large' => 'تصویر بیش از حد بزرگ است. حداکثر اندازه مجاز 4 مگابایت است.',
                'not-found' => 'تصویر محصول یافت نشد.',
                'no-permission' => 'شما مجوز مدیریت تصاویر محصول را ندارید.',
                'upload-failed' => 'بارگذاری تصویر محصول ناموفق بود.',
                'reorder-failed' => 'تغییر ترتیب تصاویر محصول ناموفق بود.',
                'delete-failed' => 'حذف تصویر محصول ناموفق بود.',
                'order-required' => 'آرایه "order" الزامی است و نباید خالی باشد.',
                'order-invalid' => 'هر ورودی در "order" باید یک شیء با id و position باشد.',
                'id-not-on-product' => 'تصویر :id متعلق به این محصول نیست.',
                'graphql-upload-unsupported' => 'بارگذاری تصویر از طریق GraphQL پشتیبانی نمی‌شود. از POST /api/admin/catalog/products/{productId}/images با multipart/form-data استفاده کنید.',
            ],

            'downloadable' => [
                'file-required' => 'یک فایل الزامی است (فیلد multipart "file").',
                'file-too-large' => 'فایل بیش از حد بزرگ است. حداکثر اندازه مجاز 128 مگابایت است.',
                'upload-failed' => 'بارگذاری فایل قابل دانلود ناموفق بود.',
                'file-not-found' => 'هیچ فایل ذخیره‌شده‌ای برای این محصول و ویژگی یافت نشد.',
                'no-permission' => 'شما مجوز مدیریت فایل‌های محصولات قابل دانلود را ندارید.',
            ],

            'video' => [
                'uploaded' => 'ویدیوی محصول با موفقیت بارگذاری شد.',
                'deleted' => 'ویدیوی محصول با موفقیت حذف شد.',
                'video-required' => 'یک فایل ویدیو الزامی است (فیلد multipart "video").',
                'video-invalid-type' => 'نوع ویدیو باید یکی از این موارد باشد: mp4, webm, ogg, mov.',
                'video-too-large' => 'ویدیو بیش از حد بزرگ است. حداکثر اندازه مجاز 100 مگابایت است.',
                'not-found' => 'ویدیوی محصول یافت نشد.',
                'no-permission' => 'شما مجوز مدیریت ویدیوهای محصول را ندارید.',
                'upload-failed' => 'بارگذاری ویدیوی محصول ناموفق بود.',
                'delete-failed' => 'حذف ویدیوی محصول ناموفق بود.',
                'graphql-upload-unsupported' => 'بارگذاری ویدیو از طریق GraphQL پشتیبانی نمی‌شود. از POST /api/admin/catalog/products/{productId}/videos با multipart/form-data استفاده کنید.',
            ],

            'inventory' => [
                'updated' => 'موجودی‌های محصول با موفقیت ذخیره شدند.',
                'inventories-required' => 'فیلد inventories الزامی است و باید نگاشتی غیرخالی از inventory_source_id به تعداد باشد.',
                'inventories-invalid' => 'فیلد inventories باید نگاشتی از inventory_source_id (عدد صحیح مثبت) به تعداد (عدد صحیح نامنفی) باشد.',
                'source-not-found' => 'منبع موجودی :id وجود ندارد.',
                'qty-invalid' => 'هر تعداد باید یک عدد صحیح نامنفی باشد.',
                'no-permission' => 'شما مجوز مدیریت موجودی‌های محصول را ندارید.',
                'not-found' => 'محصول یافت نشد.',
            ],

            'customer-group-price' => [
                'created' => 'قیمت گروه مشتری با موفقیت اضافه شد.',
                'updated' => 'قیمت گروه مشتری با موفقیت به‌روزرسانی شد.',
                'deleted' => 'قیمت گروه مشتری با موفقیت حذف شد.',
                'qty-required' => 'فیلد qty الزامی است.',
                'qty-invalid' => 'فیلد qty باید یک عدد صحیح بزرگ‌تر یا مساوی 1 باشد.',
                'value-type-required' => 'فیلد value_type الزامی است.',
                'value-type-invalid' => 'فیلد value_type باید "fixed" یا "discount" باشد.',
                'value-required' => 'فیلد value الزامی است.',
                'value-invalid' => 'فیلد value باید یک عدد نامنفی باشد.',
                'customer-group-not-found' => 'گروه مشتری انتخاب‌شده وجود ندارد.',
                'duplicate-qty-group' => 'یک قیمت گروه مشتری برای این ترکیب (qty, customer_group_id) قبلاً برای این محصول وجود دارد.',
                'not-found' => 'قیمت گروه مشتری یافت نشد.',
                'no-permission' => 'شما مجوز مدیریت قیمت‌های گروه مشتری محصول را ندارید.',
            ],
        ],

        'category' => [
            'not-found' => 'دسته‌بندی یافت نشد.',

            'created' => 'دسته‌بندی با موفقیت ایجاد شد.',
            'updated' => 'دسته‌بندی با موفقیت به‌روزرسانی شد.',
            'deleted' => 'دسته‌بندی با موفقیت حذف شد.',
            'slug-required' => 'فیلد slug الزامی است.',
            'slug-unique' => 'این slug قبلاً استفاده شده است.',
            'name-required' => 'فیلد name الزامی است.',
            'position-required' => 'فیلد position الزامی است.',
            'attributes-required' => 'حداقل یک ویژگی قابل فیلتر الزامی است.',
            'description-required-for-mode' => 'برای حالت نمایش انتخاب‌شده، توضیحات الزامی است.',
            'cannot-delete-root' => 'دسته‌بندی ریشه و دسته‌بندی‌های ریشه کانال قابل حذف نیستند.',
            'cannot-delete-channel-root' => 'این دسته‌بندی ریشه یک کانال است و قابل حذف نیست.',
            'delete-failed' => 'حذف دسته‌بندی ممکن نبود.',
            'no-permission' => 'شما مجوز مدیریت دسته‌بندی‌ها را ندارید.',
            'mass-delete-success' => 'دسته‌بندی‌ها با موفقیت حذف شدند.',
            'mass-update-status-success' => 'وضعیت دسته‌بندی‌ها با موفقیت به‌روزرسانی شد.',
            'mass-delete-indices-required' => 'فیلد indices الزامی است و باید یک آرایه غیرخالی باشد.',
            'mass-update-status-value-required' => 'فیلد value الزامی است و باید 0 یا 1 باشد.',
        ],

        'attribute' => [
            'not-found' => 'ویژگی یافت نشد.',

            'create-success' => 'ویژگی با موفقیت ایجاد شد.',
            'update-success' => 'ویژگی با موفقیت به‌روزرسانی شد.',
            'delete-success' => 'ویژگی با موفقیت حذف شد.',
            'delete-failed' => 'حذف ویژگی ممکن نبود.',
            'mass-delete-success' => 'ویژگی‌ها با موفقیت حذف شدند.',
            'mass-delete-indices-required' => 'فیلد indices الزامی است و باید یک آرایه غیرخالی باشد.',

            'code-immutable' => 'پس از ایجاد ویژگی، کد آن قابل تغییر نیست.',
            'type-immutable' => 'تا زمانی که ویژگی دارای مقادیر محصول است، نوع آن قابل تغییر نیست.',
            'locale-scope-immutable' => 'تا زمانی که ویژگی دارای مقادیر محصول است، دامنه زبان قابل تغییر نیست.',
            'system-attribute' => 'ویژگی‌های سیستمی قابل حذف نیستند.',
            'in-use-family' => 'ویژگی بخشی از یک یا چند خانواده ویژگی است (شناسه‌های گروه: :ids). ابتدا آن را از آن خانواده‌ها حذف کنید.',

            'option-not-found' => 'گزینه ویژگی یافت نشد.',
            'option-not-supported' => 'نوع ویژگی ":type" از گزینه‌ها پشتیبانی نمی‌کند. فقط ویژگی‌های select، multiselect و checkbox می‌توانند گزینه داشته باشند.',
            'option-in-use' => 'این گزینه توسط :count محصول استفاده شده است و قابل حذف نیست.',
            'option-delete-success' => 'گزینه ویژگی با موفقیت حذف شد.',
        ],

        'family' => [
            'not-found' => 'خانواده ویژگی یافت نشد.',

            'created' => 'خانواده ویژگی با موفقیت ایجاد شد.',
            'updated' => 'خانواده ویژگی با موفقیت به‌روزرسانی شد.',
            'deleted' => 'خانواده ویژگی با موفقیت حذف شد.',
            'code-required' => 'کد خانواده الزامی است.',
            'code-unique' => 'کد خانواده باید یکتا باشد.',
            'name-required' => 'نام خانواده الزامی است.',
            'group-code-required' => 'هر گروه ویژگی باید یک کد داشته باشد.',
            'group-name-required' => 'هر گروه ویژگی باید یک نام داشته باشد.',
            'group-column-invalid' => 'ستون هر گروه ویژگی باید 1 یا 2 باشد.',
            'group-name-duplicate' => 'نام گروه ویژگی باید در خانواده ویژگی یکتا باشد.',
            'update-failed' => 'به‌روزرسانی خانواده ویژگی ممکن نبود.',
            'create-failed' => 'ایجاد خانواده ویژگی ممکن نبود.',
            'default-delete-error' => 'خانواده ویژگی پیش‌فرض قابل حذف نیست.',
            'last-delete-error' => 'حداقل یک خانواده ویژگی الزامی است.',
            'attribute-product-error' => 'این خانواده ویژگی توسط یک یا چند محصول استفاده می‌شود. پیش از حذف، آن محصولات را به خانواده دیگری اختصاص دهید.',
            'delete-failed' => 'حذف خانواده ویژگی ممکن نبود.',
            'no-permission' => 'شما مجوز مدیریت خانواده‌های ویژگی را ندارید.',
        ],

        'settings' => [
            'locale' => [
                'not-found' => 'زبان یافت نشد.',
                'created' => 'زبان با موفقیت ایجاد شد.',
                'updated' => 'زبان با موفقیت به‌روزرسانی شد.',
                'deleted' => 'زبان با موفقیت حذف شد.',
                'cannot-delete-last' => 'حداقل یک زبان الزامی است — آخرین زبان باقی‌مانده قابل حذف نیست.',
                'cannot-delete-channel-default' => 'این زبان، زبان پیش‌فرض یک یا چند کانال است و قابل حذف نیست.',
                'delete-failed' => 'حذف زبان ممکن نبود.',
                'no-permission' => 'شما مجوز مدیریت زبان‌ها را ندارید.',
                'mass-delete-success' => 'زبان‌ها با موفقیت حذف شدند.',
                'mass-delete-indices-required' => 'فیلد indices الزامی است و باید یک آرایه غیرخالی باشد.',
            ],

            'theme' => [
                'not-found' => 'سفارشی‌سازی پوسته یافت نشد.',
                'created' => 'سفارشی‌سازی پوسته با موفقیت ایجاد شد.',
                'updated' => 'سفارشی‌سازی پوسته با موفقیت به‌روزرسانی شد.',
                'deleted' => 'سفارشی‌سازی پوسته با موفقیت حذف شد.',
                'delete-failed' => 'حذف سفارشی‌سازی پوسته ممکن نبود.',
                'no-permission' => 'شما مجوز مدیریت سفارشی‌سازی‌های پوسته را ندارید.',
                'mass-delete-success' => 'سفارشی‌سازی‌های پوسته با موفقیت حذف شدند.',
                'mass-delete-indices-required' => 'فیلد indices الزامی است و باید یک آرایه غیرخالی باشد.',
                'mass-update-success' => 'سفارشی‌سازی‌های پوسته با موفقیت به‌روزرسانی شدند.',
                'mass-update-indices-required' => 'فیلد indices الزامی است و باید یک آرایه غیرخالی باشد.',
                'mass-update-value-invalid' => 'فیلد value باید 0 یا 1 باشد.',
            ],

            'inventory-source' => [
                'not-found' => 'منبع موجودی یافت نشد.',
                'created' => 'منبع موجودی با موفقیت ایجاد شد.',
                'updated' => 'منبع موجودی با موفقیت به‌روزرسانی شد.',
                'deleted' => 'منبع موجودی با موفقیت حذف شد.',
                'code-unique' => 'منبع موجودی با این کد از قبل وجود دارد.',
                'last-delete-error' => 'حداقل یک منبع موجودی الزامی است — آخرین منبع باقی‌مانده قابل حذف نیست.',
                'in-use' => 'این منبع موجودی در موجودی‌های محصول ارجاع شده است و قابل حذف نیست.',
                'delete-failed' => 'حذف منبع موجودی ممکن نبود.',
                'no-permission' => 'شما مجوز مدیریت منابع موجودی را ندارید.',
                'mass-delete-success' => 'منابع موجودی با موفقیت حذف شدند.',
                'mass-delete-indices-required' => 'فیلد indices الزامی است و باید یک آرایه غیرخالی باشد.',
            ],

            'exchange-rate' => [
                'not-found' => 'نرخ ارز یافت نشد.',
                'created' => 'نرخ ارز با موفقیت ایجاد شد.',
                'updated' => 'نرخ ارز با موفقیت به‌روزرسانی شد.',
                'deleted' => 'نرخ ارز با موفقیت حذف شد.',
                'duplicate-pair' => 'نرخ ارز برای این ارز مقصد از قبل وجود دارد. ردیف موجود را به‌روزرسانی کنید یا ابتدا آن را حذف کنید.',
                'delete-failed' => 'حذف نرخ ارز ممکن نبود.',
                'no-permission' => 'شما مجوز مدیریت نرخ‌های ارز را ندارید.',
                'mass-delete-success' => 'نرخ‌های ارز با موفقیت حذف شدند.',
                'mass-delete-indices-required' => 'فیلد indices الزامی است و باید یک آرایه غیرخالی باشد.',
                'update-rates-success' => 'نرخ‌های ارز با موفقیت به‌روزرسانی شدند.',
                'update-rates-failed' => 'به‌روزرسانی نرخ‌های ارز از ارائه‌دهنده خارجی ممکن نبود.',
                'provider-not-configured' => 'هیچ ارائه‌دهنده خارجی نرخ ارز پیکربندی نشده است.',
            ],

            'tax-rate' => [
                'not-found' => 'نرخ مالیات یافت نشد.',
                'created' => 'نرخ مالیات با موفقیت ایجاد شد.',
                'updated' => 'نرخ مالیات با موفقیت به‌روزرسانی شد.',
                'deleted' => 'نرخ مالیات با موفقیت حذف شد.',
                'delete-failed' => 'حذف نرخ مالیات ممکن نبود.',
                'no-permission' => 'شما مجوز مدیریت نرخ‌های مالیات را ندارید.',
                'zip-code-required' => 'هنگامی که is_zip برابر false است، فیلد zip_code الزامی است.',
                'zip-range-required' => 'هنگامی که is_zip برابر true است، هر دو فیلد zip_from و zip_to الزامی هستند.',
            ],

            'tax-category' => [
                'not-found' => 'دسته مالیاتی یافت نشد.',
                'created' => 'دسته مالیاتی با موفقیت ایجاد شد.',
                'updated' => 'دسته مالیاتی با موفقیت به‌روزرسانی شد.',
                'deleted' => 'دسته مالیاتی با موفقیت حذف شد.',
                'code-unique' => 'این کد قبلاً استفاده شده است.',
                'cannot-delete-in-use' => 'این دسته مالیاتی هنوز دارای نرخ‌های مالیات مرتبط است و قابل حذف نیست.',
                'delete-failed' => 'حذف دسته مالیاتی ممکن نبود.',
                'no-permission' => 'شما مجوز مدیریت دسته‌های مالیاتی را ندارید.',
            ],

            'role' => [
                'not-found' => 'نقش یافت نشد.',
                'created' => 'نقش با موفقیت ایجاد شد.',
                'updated' => 'نقش با موفقیت به‌روزرسانی شد.',
                'deleted' => 'نقش با موفقیت حذف شد.',
                'permissions-required' => 'هنگامی که permission_type برابر custom است، فیلد permissions الزامی است.',
                'cannot-delete-in-use' => 'این نقش به یک یا چند مدیر اختصاص داده شده است و قابل حذف نیست.',
                'cannot-delete-last-role' => 'حداقل یک نقش الزامی است — آخرین نقش باقی‌مانده قابل حذف نیست.',
                'cannot-change-last-all-access' => 'تنها نقش با دسترسی کامل را نمی‌توان به نقش سفارشی تغییر داد.',
                'delete-failed' => 'حذف نقش ممکن نبود.',
                'no-permission' => 'شما اجازه مدیریت نقش‌ها را ندارید.',
            ],

            'currency' => [
                // Read
                'not-found' => 'ارز یافت نشد.',

                // Create / Update / Delete
                'created' => 'ارز با موفقیت ایجاد شد.',
                'updated' => 'ارز با موفقیت به‌روزرسانی شد.',
                'deleted' => 'ارز با موفقیت حذف شد.',
                'code-required' => 'فیلد code الزامی است.',
                'code-size' => 'کد باید دقیقاً 3 حرف الفبایی باشد.',
                'code-unique' => 'این کد قبلاً استفاده شده است.',
                'name-required' => 'فیلد name الزامی است.',
                'cannot-delete-last' => 'حداقل یک ارز الزامی است — آخرین ارز باقی‌مانده قابل حذف نیست.',
                'cannot-delete-channel-base' => 'این ارز، ارز پایه یک یا چند کانال است و قابل حذف نیست.',
                'delete-failed' => 'حذف ارز ممکن نبود.',
                'no-permission' => 'شما مجوز مدیریت ارزها را ندارید.',

                'mass-delete-success' => 'ارزها با موفقیت حذف شدند.',
                'mass-delete-indices-required' => 'فیلد indices الزامی است و باید یک آرایه غیرخالی باشد.',
            ],

            'channel' => [
                'not-found' => 'کانال یافت نشد.',

                'created' => 'کانال با موفقیت ایجاد شد.',
                'updated' => 'کانال با موفقیت به‌روزرسانی شد.',
                'deleted' => 'کانال با موفقیت حذف شد.',
                'code-unique' => 'این کد قبلاً استفاده شده است.',
                'hostname-unique' => 'این نام میزبان قبلاً استفاده شده است.',
                'default-locale-mismatch' => 'default_locale_id باید در فهرست زبان‌ها وجود داشته باشد.',
                'base-currency-mismatch' => 'base_currency_id باید در فهرست ارزها وجود داشته باشد.',
                'unknown-locale' => 'یک یا چند شناسه زبان وجود ندارند.',
                'unknown-currency' => 'یک یا چند شناسه ارز وجود ندارند.',
                'unknown-inventory-source' => 'یک یا چند شناسه inventory_source وجود ندارند.',
                'unknown-root-category' => 'root_category_id وجود ندارد.',
                'cannot-delete-last' => 'حداقل یک کانال الزامی است — آخرین کانال باقی‌مانده قابل حذف نیست.',
                'cannot-delete-default' => 'کانال پیش‌فرض برنامه قابل حذف نیست.',
                'delete-failed' => 'حذف کانال ممکن نبود.',
                'no-permission' => 'شما مجوز مدیریت کانال‌ها را ندارید.',
            ],

            'user' => [
                'not-found' => 'کاربر مدیر یافت نشد.',
                'created' => 'کاربر مدیر با موفقیت ایجاد شد.',
                'updated' => 'کاربر مدیر با موفقیت به‌روزرسانی شد.',
                'deleted' => 'کاربر مدیر با موفقیت حذف شد.',
                'name-required' => 'فیلد name الزامی است.',
                'email-required' => 'فیلد email الزامی است.',
                'email-invalid' => 'ایمیل باید یک آدرس ایمیل معتبر باشد.',
                'email-unique' => 'این ایمیل قبلاً استفاده شده است.',
                'password-required' => 'فیلد password الزامی است.',
                'password-min' => 'رمز عبور باید حداقل 6 نویسه باشد.',
                'role-required' => 'فیلد role_id الزامی است.',
                'role-not-found' => 'نقش انتخاب‌شده وجود ندارد.',
                'status-invalid' => 'وضعیت باید 0 یا 1 باشد.',
                'cannot-delete-self' => 'نمی‌توانید حساب مدیر خود را حذف کنید.',
                'cannot-delete-last-admin' => 'حداقل یک کاربر مدیر الزامی است — آخرین مدیر باقی‌مانده قابل حذف نیست.',
                'cannot-change' => ':name آخرین مدیر با دسترسی کامل قابل تغییر نیست.',
                'delete-failed' => 'حذف کاربر مدیر ممکن نبود.',
                'no-permission' => 'شما اجازه مدیریت کاربران مدیر را ندارید.',
                'self-delete-password-required' => 'برای حذف حساب خود، فیلد password الزامی است.',
                'self-delete-incorrect-password' => 'رمز عبور نادرست است.',
                'self-deleted' => 'حساب مدیر شما حذف شد.',
            ],

            'data-transfer' => [
                'import' => [
                    'not-found' => 'درون‌ریزی یافت نشد.',
                    'deleted' => 'درون‌ریزی با موفقیت حذف شد.',
                    'cancelled' => 'درون‌ریزی با موفقیت لغو شد.',
                    'cannot-cancel' => 'فقط درون‌ریزی‌های در انتظار یا در حال پردازش قابل لغو هستند. وضعیت فعلی: :state.',
                    'no-permission' => 'شما مجوز مدیریت درون‌ریزی‌های انتقال داده را ندارید.',
                    'delete-failed' => 'حذف درون‌ریزی ممکن نبود.',
                    'created' => 'درون‌ریزی با موفقیت ایجاد شد.',
                    'updated' => 'درون‌ریزی با موفقیت به‌روزرسانی شد.',
                    'type-invalid' => 'نوع انتخاب‌شده نامعتبر است.',
                    'action-invalid' => 'عملیات باید یکی از این موارد باشد: append, delete.',
                    'validation-strategy-invalid' => 'راهبرد اعتبارسنجی باید یکی از این موارد باشد: stop-on-errors, skip-errors.',
                    'allowed-errors-invalid' => 'تعداد خطاهای مجاز باید یک عدد صحیح 0 یا بیشتر باشد.',
                    'field-separator-required' => 'جداکننده فیلد الزامی است.',
                    'file-required' => 'فایل الزامی است.',
                    'file-invalid-type' => 'فایل باید یکی از این موارد باشد: csv, xls, xlsx, xml.',
                    'nothing-to-import' => 'چیزی برای درون‌ریزی وجود ندارد.',
                    'not-valid' => 'درون‌ریزی معتبر نیست.',
                    'setup-queue-error' => 'اتصال صف روی sync تنظیم شده است؛ برای پردازش درون‌ریزی‌ها در پس‌زمینه، یک صف واقعی پیکربندی کنید.',
                    'file-not-found' => 'هیچ فایلی برای دانلود در دسترس نیست.',
                    'error-report-not-found' => 'هیچ گزارش خطایی برای دانلود در دسترس نیست.',
                    'sample-not-found' => 'هیچ فایل نمونه‌ای برای نوع و قالب درخواستی در دسترس نیست.',
                    'sample-format-invalid' => 'قالب نمونه باید یکی از این موارد باشد: csv, xls, xlsx, xml.',
                    'images-archive-too-large' => 'بایگانی تصاویر می‌تواند حداکثر :entries فایل داشته باشد و پس از استخراج حداکثر :size مگابایت باشد.',
                ],
            ],
        ],

        'cms' => [
            'page' => [
                'not-found' => 'صفحه CMS یافت نشد.',

                'created' => 'صفحه CMS با موفقیت ایجاد شد.',
                'url-key-required' => 'فیلد url_key الزامی است.',
                'url-key-unique' => 'این url_key قبلاً استفاده شده است.',
                'url-key-invalid' => 'قالب url_key نامعتبر است. فقط از حروف کوچک، اعداد و خط تیره استفاده کنید.',
                'page-title-required' => 'فیلد page_title الزامی است.',
                'html-content-required' => 'فیلد html_content الزامی است.',
                'channels-required' => 'حداقل یک کانال باید انتخاب شود.',
                'channels-invalid' => 'یک یا چند کانال نامعتبر هستند.',

                'updated' => 'صفحه CMS با موفقیت به‌روزرسانی شد.',

                'deleted' => 'صفحه CMS با موفقیت حذف شد.',
                'delete-failed' => 'حذف صفحه CMS ممکن نبود.',

                'mass-deleted' => 'صفحات CMS با موفقیت حذف شدند.',
                'indices-required' => 'فیلد indices الزامی است و باید یک آرایه غیرخالی باشد.',
                'indices-invalid' => 'فیلد indices باید آرایه‌ای از شناسه‌های صفحات CMS باشد.',

                'no-permission' => 'شما مجوز مدیریت صفحات CMS را ندارید.',
            ],
        ],

        'marketing' => [
            'catalog-rule' => [
                'not-found' => 'قانون کاتالوگ یافت نشد.',
                'created' => 'قانون کاتالوگ با موفقیت ایجاد شد.',
                'updated' => 'قانون کاتالوگ با موفقیت به‌روزرسانی شد.',
                'deleted' => 'قانون کاتالوگ با موفقیت حذف شد.',
                'delete-failed' => 'حذف قانون کاتالوگ ممکن نبود.',
                'mass-delete-indices-required' => 'هیچ شناسه قانون کاتالوگی برای حذف گروهی ارائه نشده است.',
                'mass-delete-success' => 'قوانین کاتالوگ انتخاب‌شده با موفقیت حذف شدند.',
                'no-permission' => 'شما مجوز مدیریت قوانین کاتالوگ را ندارید.',
            ],

            'campaign' => [
                'not-found' => 'کمپین یافت نشد.',
                'created' => 'کمپین با موفقیت ایجاد شد.',
                'updated' => 'کمپین با موفقیت به‌روزرسانی شد.',
                'deleted' => 'کمپین با موفقیت حذف شد.',
                'delete-failed' => 'حذف کمپین ممکن نبود.',
                'no-permission' => 'شما مجوز مدیریت کمپین‌ها را ندارید.',

                'send' => [
                    'id-required' => 'شناسه کمپین الزامی است.',
                    'inactive' => 'ارسال کمپین غیرفعال ممکن نیست. ابتدا آن را فعال کنید.',
                    'queued' => 'کمپین برای :count گیرنده در صف قرار گرفت.',
                ],
            ],

            'sitemap' => [
                'not-found' => 'نقشه سایت یافت نشد.',
                'created' => 'نقشه سایت با موفقیت ایجاد شد.',
                'updated' => 'نقشه سایت با موفقیت به‌روزرسانی شد.',
                'deleted' => 'نقشه سایت با موفقیت حذف شد.',
                'delete-failed' => 'حذف نقشه سایت ممکن نبود.',
                'no-permission' => 'شما مجوز مدیریت نقشه‌های سایت را ندارید.',

                'generate' => [
                    'id-required' => 'شناسه نقشه سایت الزامی است.',
                    'no-channels' => 'این نقشه سایت هیچ کانالی را پوشش نمی‌دهد، بنابراین چیزی برای تولید وجود ندارد. ابتدا حداقل یک کانال به آن اختصاص دهید.',
                    'success' => 'نقشه سایت با موفقیت دوباره تولید شد.',
                    'failed' => 'تولید نقشه سایت ناموفق بود: :message',
                ],
            ],

            'cart-rule' => [
                'not-found' => 'قانون سبد خرید یافت نشد.',
                'id-required' => 'شناسه قانون سبد خرید الزامی است.',
                'copy-of' => 'کپی :value',
                'copied' => 'قانون سبد خرید با موفقیت کپی شد.',
                'created' => 'قانون سبد خرید با موفقیت ایجاد شد.',
                'updated' => 'قانون سبد خرید با موفقیت به‌روزرسانی شد.',
                'deleted' => 'قانون سبد خرید با موفقیت حذف شد.',
                'delete-failed' => 'حذف قانون سبد خرید ممکن نبود.',
                'no-permission' => 'شما مجوز مدیریت قوانین سبد خرید را ندارید.',
                'mass-delete-success' => 'قوانین سبد خرید با موفقیت حذف شدند.',
                'mass-delete-indices-required' => 'فیلد indices الزامی است و باید یک آرایه غیرخالی باشد.',
                'action-type-invalid' => 'action_type باید یکی از این موارد باشد: by_percent, by_fixed, cart_fixed, buy_x_get_y.',
                'coupon-code-required' => 'هنگامی که coupon_type=1 و use_auto_generation=0 است، coupon_code الزامی است.',
                'coupon-code-unique' => 'این کد کوپن قبلاً استفاده شده است.',
            ],

            'template' => [
                'campaign-associated' => 'این قالب ایمیل توسط یک کمپین استفاده می‌شود، بنابراین قابل حذف نیست. ابتدا آن را از کمپین حذف کنید.',
                'not-found' => 'قالب ایمیل یافت نشد.',
                'created' => 'قالب ایمیل با موفقیت ایجاد شد.',
                'updated' => 'قالب ایمیل با موفقیت به‌روزرسانی شد.',
                'deleted' => 'قالب ایمیل با موفقیت حذف شد.',
                'delete-failed' => 'حذف قالب ایمیل ممکن نبود.',
                'status-invalid' => 'وضعیت باید یکی از این موارد باشد: active, inactive, draft.',
                'no-permission' => 'شما مجوز مدیریت قالب‌های ایمیل را ندارید.',
            ],

            'event' => [
                'campaign-associated' => 'این رویداد توسط یک کمپین استفاده می‌شود، بنابراین قابل حذف نیست. ابتدا آن را از کمپین حذف کنید.',
                'not-found' => 'رویداد بازاریابی یافت نشد.',
                'created' => 'رویداد بازاریابی با موفقیت ایجاد شد.',
                'updated' => 'رویداد بازاریابی با موفقیت به‌روزرسانی شد.',
                'deleted' => 'رویداد بازاریابی با موفقیت حذف شد.',
                'delete-failed' => 'حذف رویداد بازاریابی ممکن نبود.',
                'no-permission' => 'شما مجوز مدیریت رویدادهای بازاریابی را ندارید.',
            ],

            'search-synonym' => [
                'not-found' => 'مترادف جستجو یافت نشد.',
                'created' => 'مترادف جستجو با موفقیت ایجاد شد.',
                'updated' => 'مترادف جستجو با موفقیت به‌روزرسانی شد.',
                'deleted' => 'مترادف جستجو با موفقیت حذف شد.',
                'mass-deleted' => 'مترادف‌های جستجو با موفقیت حذف شدند.',
                'delete-failed' => 'حذف مترادف جستجو ممکن نبود.',
                'indices-required' => 'فیلد indices الزامی است و باید آرایه‌ای غیرخالی از شناسه‌های مترادف جستجو باشد.',
                'no-permission' => 'شما مجوز مدیریت مترادف‌های جستجو را ندارید.',
            ],

            'url-rewrite' => [
                'not-found' => 'بازنویسی URL یافت نشد.',
                'created' => 'بازنویسی URL با موفقیت ایجاد شد.',
                'updated' => 'بازنویسی URL با موفقیت به‌روزرسانی شد.',
                'deleted' => 'بازنویسی URL با موفقیت حذف شد.',
                'delete-failed' => 'حذف بازنویسی URL ممکن نبود.',
                'mass-deleted' => 'بازنویسی‌های URL با موفقیت حذف شدند.',
                'indices-required' => 'حداقل یک شناسه بازنویسی URL الزامی است.',
                'no-permission' => 'شما مجوز مدیریت بازنویسی‌های URL را ندارید.',
            ],

            'cart-rule-coupon' => [
                'cart-rule-not-found' => 'قانون سبد خرید یافت نشد.',
                'not-found' => 'کوپن یافت نشد.',
                'code-taken' => 'کوپنی با این کد از قبل وجود دارد.',
                'format-invalid' => 'قالب باید یکی از این موارد باشد: alphabetic, alphanumeric, numeric.',
                'indices-required' => 'فیلد indices الزامی است و باید آرایه‌ای غیرخالی از شناسه‌های کوپن باشد.',
                'created' => 'کوپن با موفقیت ایجاد شد.',
                'generated' => ':count کوپن با موفقیت تولید شد.',
                'deleted' => 'کوپن با موفقیت حذف شد.',
                'mass-deleted' => ':count کوپن با موفقیت حذف شد.',
                'no-permission' => 'شما مجوز مدیریت کوپن‌های قانون سبد خرید را ندارید.',
            ],

            'subscriber' => [
                'not-found' => 'مشترک خبرنامه یافت نشد.',
                'updated' => 'اشتراک با موفقیت به‌روزرسانی شد.',
                'deleted' => 'اشتراک با موفقیت حذف شد.',
                'is-subscribed-required' => 'فیلد is_subscribed الزامی است.',
                'no-permission' => 'شما مجوز مدیریت مشترکان خبرنامه را ندارید.',
            ],

            'search-term' => [
                'not-found' => 'عبارت جستجو یافت نشد.',
                'updated' => 'عبارت جستجو با موفقیت به‌روزرسانی شد.',
                'deleted' => 'عبارت جستجو با موفقیت حذف شد.',
                'mass-deleted' => 'عبارت‌های جستجو با موفقیت حذف شدند.',
                'term-required' => 'فیلد term الزامی است.',
                'redirect-url-invalid' => 'redirect_url باید یک URL معتبر باشد.',
                'indices-required' => 'حداقل یک شناسه باید در indices ارائه شود.',
                'no-permission' => 'شما مجوز مدیریت عبارت‌های جستجو را ندارید.',
            ],
        ],
        'auth' => [
            'configuration' => [
                'title' => 'احراز هویت مدیر',
                'info' => 'مدت اعتبار توکن‌ها، محدودیت نرخ و قوانین امنیتی برای ورود مدیر به API و توکن‌های API.',

                'tokens' => [
                    'title' => 'مدت اعتبار توکن‌ها',
                    'info' => 'مدت اعتبار توکن‌های دسترسی، توکن‌های تازه‌سازی و نشست‌ها. تغییرات برای توکن‌های صادرشده پس از آن اعمال می‌شود.',
                    'access-token-ttl' => 'مدت اعتبار توکن دسترسی (دقیقه)',
                    'access-token-ttl-info' => 'بین ۵ تا ۶۰ دقیقه. پیش‌فرض ۱۵.',
                    'refresh-token-ttl' => 'مدت اعتبار توکن تازه‌سازی (روز)',
                    'refresh-token-ttl-info' => 'بین ۱ تا ۹۰ روز. پیش‌فرض ۳۰.',
                    'session-lifetime' => 'حداکثر مدت نشست (روز)',
                    'session-lifetime-info' => 'بین ۱ تا ۳۶۵ روز. پیش‌فرض ۹۰. پس از آن مدیر باید دوباره وارد شود، هر چند بار که نشست تازه‌سازی شده باشد.',
                ],

                'rate-limits' => [
                    'title' => 'محدودیت نرخ',
                    'info' => 'حداکثر درخواست‌های API مدیر در دقیقه.',
                    'session-requests-per-minute' => 'درخواست در دقیقه برای هر مدیر واردشده',
                    'api-token-requests-per-minute' => 'درخواست در دقیقه برای هر توکن API',
                ],

                'security' => [
                    'title' => 'امنیت',
                    'info' => 'قوانین لغو، انقضای توکن‌های API و اتصال دامنه.',
                    'revoke-sessions-on-password-change' => 'لغو نشست‌ها هنگام تغییر رمز عبور',
                    'revoke-api-tokens-on-password-change' => 'لغو توکن‌های API هنگام تغییر رمز عبور',
                    'allow-non-expiring-api-tokens' => 'اجازه توکن‌های API بدون انقضا',
                    'domain-binding-mode' => 'حالت اتصال دامنه',
                    'domain-binding-mode-info' => 'DNS بررسی می‌کند که درخواست از نشانی IP‌ای باشد که دامنه توکن به آن اشاره می‌کند. Origin سرآیند Origin یا Referer را بررسی می‌کند و فقط برای مرورگرها مناسب است.',
                    'domain-binding-dns' => 'DNS (نشانی IP کلاینت)',
                    'domain-binding-origin' => 'سرآیند Origin',
                ],
            ],

            'errors' => [
                'unauthenticated' => 'احراز هویت نشده‌اید.',
                'access-token-expired' => 'توکن دسترسی منقضی شده است. برای ادامه آن را تازه‌سازی کنید.',
                'api-token-expired' => 'توکن API منقضی شده است.',
                'refresh-token-invalid' => 'توکن تازه‌سازی نامعتبر یا منقضی است. لطفاً دوباره وارد شوید.',
                'api-token-revoked' => 'توکن API باطل شده است.',
                'api-token-domain-mismatch' => 'توکن API را نمی‌توان از این دامنه استفاده کرد.',
                'access-token-required' => 'این عملیات به توکن دسترسی یک نشست ورود نیاز دارد؛ توکن‌های API نمی‌توانند از آن استفاده کنند.',
                'two-factor-required' => 'احراز هویت دومرحله‌ای برای این حساب فعال است و ورود به API با عامل دوم هنوز در دسترس نیست.',
                'too-many-login-attempts' => 'تلاش‌های ورود بیش از حد. لطفاً پس از :seconds ثانیه دوباره تلاش کنید.',
                'rate-limit-exceeded' => 'تلاش‌های بیش از حد.',
                'no-permission' => 'شما اجازه مدیریت نشست‌های API مدیران را ندارید.',
            ],

            'sessions' => [
                'logged-out' => 'از حساب خارج شدید.',
                'logged-out-all' => 'از همه نشست‌ها خارج شدید.',
                'not-found' => 'نشست یافت نشد.',
                'revoked' => 'نشست باطل شد.',
                'revoked-for-admin' => 'نشست‌های باطل‌شده: :count.',
            ],

            'acl' => [
                'api-sessions' => 'نشست‌های API',
                'revoke' => 'ابطال نشست‌ها',
            ],
        ],
    ],

    'integration' => [
        'menu' => [
            'title' => 'یکپارچه سازی',
            'tokens' => 'توکن ها',
        ],

        'history' => [
            'menu' => [
                'title' => 'تاریخچه',
            ],

            'acl' => [
                'title' => 'تاریخچه تغییرات API',
                'view' => 'مشاهده',
                'delete' => 'حذف تاریخچه',
            ],

            'index' => [
                'title' => 'تاریخچه تغییرات API',
                'info' => 'هر ایجاد، به‌روزرسانی و حذفی که از طریق API مدیریت انجام می‌شود، با چه کسی آن را انجام داده است، کدام نشانه و چه چیزی تغییر کرده است.',
                'cleanup-btn' => 'لاگ های قدیمی را حذف کنید',
                'cleanup-days' => 'گزارش های قدیمی تر از این چند روز را حذف کنید',
                'cleanup-confirm' => 'تمام سابقه قدیمی‌تر از تعداد روزهای معین حذف شود؟ این قابل واگرد نیست.',
            ],

            'view' => [
                'title' => 'تغییر دهید',
                'back-btn' => 'برگشت',
                'admin' => 'مدیر',
                'token' => 'رمز',
                'action' => 'اقدام',
                'resource' => 'منبع',
                'method' => 'روش',
                'ip' => 'آدرس IP',
                'date' => 'تاریخ',
                'version' => 'نسخه',
                'url' => 'نقطه پایانی',
                'request-details' => 'جزئیات درخواست',
                'changes' => 'تغییرات',
                'field' => 'میدان',
                'old' => 'ارزش قدیمی',
                'new' => 'ارزش جدید',
                'no-field-changes' => 'هیچ تغییری در سطح زمینه برای این ورودی ثبت نشد.',
                'same-request' => 'تغییرات دیگر در همین درخواست',
                'version-chain' => 'تاریخچه نسخه این رکورد',
            ],

            'datagrid' => [
                'id' => 'شناسه',
                'date' => 'تاریخ',
                'admin' => 'مدیر',
                'token' => 'رمز',
                'action' => 'اقدام',
                'operation' => 'عملیات',
                'resource' => 'منبع',
                'version' => 'نسخه',
                'method' => 'روش',
                'ip' => 'IP',
                'view' => 'مشاهده کنید',
                'delete' => 'حذف کنید',
            ],

            'events' => [
                'created' => 'ایجاد شد',
                'updated' => 'به روز شد',
                'deleted' => 'حذف شد',
            ],

            'deleted' => ':count سابقه(های) سابقه حذف شد.',
            'cleanup-input-required' => 'تعدادی روز یا تاریخی برای پاکسازی ارائه دهید.',
        ],

        'acl' => [
            'title' => 'یکپارچه سازی',
            'view' => 'مشاهده',
            'create' => 'ایجاد یکپارچگی',
            'edit' => 'ویرایش یکپارچه سازی',
            'delete' => 'لغو رمز ادغام',
            'generate' => 'توکن ادغام ایجاد کنید',
            'regenerate' => 'Regenerate Token ادغام',
        ],

        'index' => [
            'title' => 'ادغام ها',
            'create-btn' => 'ایجاد یکپارچگی',
        ],

        'create' => [
            'title' => 'ایجاد یکپارچگی',
            'save-btn' => 'ذخیره کنید',
            'back-btn' => 'برگشت',
        ],

        'edit' => [
            'title' => 'ویرایش یکپارچه سازی',
            'save-btn' => 'ذخیره کنید',
            'back-btn' => 'برگشت',
            'generate-btn' => 'توکن تولید کنید',
            'regenerate-btn' => 'بازسازی توکن',
            'revoke-btn' => 'لغو توکن',
            'copy-btn' => 'کپی کنید',
            'token-warning' => 'اکنون این نشانه را ذخیره کنید - دیگر نشان داده نخواهد شد.',
            'token-label' => 'رمز',
            'not-generated' => 'هنوز تولید نشده است',
            'masked' => '(ذخیره شده - فقط یک بار در هر نسل نشان داده می شود)',
            'history-banner' => 'این نشانه دیگر فعال نیست.',
        ],

        'fields' => [
            'name' => 'نام',
            'description' => 'توضیحات',
            'assign-user' => 'اختصاص کاربر',
            'permission-type' => 'نوع مجوز',
            'access-control' => 'کنترل دسترسی',
            'general' => 'ژنرال',
            'token-settings' => 'تنظیمات توکن',
            'valid-till' => 'معتبر تا',
            'rate-limit-per-minute' => 'محدودیت نرخ (در دقیقه)',
            'rate-limit-per-day' => 'محدودیت نرخ (در روز)',
            'never-expires' => 'هرگز منقضی نمی شود',
            'expires-on' => 'منقضی می شود',
            'unlimited' => 'نامحدود',
            'limit-to' => 'محدود به',
            'requests-per-minute' => 'درخواست ها / دقیقه',
            'requests-per-day' => 'درخواست ها / روز',
            'select-admin' => 'یک مدیر انتخاب کنید',
            'no-available-admins' => 'هیچ مدیری در دسترس نیست - هر سرپرست قبلاً یک توکن فعال دارد.',
            'same-as-web-hint' => 'Token مجوزهای نقش فعلی سرپرست اختصاص داده شده را به صورت زنده منعکس می کند.',
            'ip-allowlist' => 'لیست مجاز IP',
            'ip-any' => 'هر IP (پیش‌فرض)',
            'ip-restricted' => 'محدود به IP های خاص',
            'ip-list-hint' => 'یک ورودی در هر خط پشتیبانی از IPv4، IPv6 و CIDR (به عنوان مثال 10.0.0.0/24 یا 2001:db8::/32). برای اجازه دادن به همه IP ها خالی بگذارید.',
        ],

        'permission-type' => [
            'all' => 'همه',
            'custom' => 'سفارشی',
            'same-as-web' => 'همان Web Permission',
        ],

        'status' => [
            'draft' => 'پیش نویس',
            'active' => 'فعال',
            'revoked' => 'لغو شد',
            'regenerated' => 'بازسازی شد',
        ],

        'datagrid' => [
            'id' => 'شناسه',
            'name' => 'نام',
            'admin' => 'مدیر',
            'token' => 'رمز',
            'status' => 'وضعیت',
            'permission-type' => 'نوع مجوز',
            'expires-at' => 'معتبر تا',
            'last-used-at' => 'آخرین استفاده',
            'created-at' => 'ایجاد شده در',
            'edit' => 'ویرایش کنید',
            'revoke' => 'لغو',
        ],

        'messages' => [
            'draft-created' => 'یکپارچه سازی ایجاد شد. توکن را برای شروع استفاده از آن تولید کنید.',
            'updated' => 'ادغام با موفقیت به روز شد.',
            'generated' => 'توکن تولید شد. اکنون آن را کپی کنید - دیگر نشان داده نخواهد شد.',
            'regenerated' => 'توکن بازسازی شد. اکنون توکن جدید را کپی کنید - دیگر نشان داده نخواهد شد.',
            'revoked' => 'توکن با موفقیت لغو شد.',
            'generate-only-draft' => 'فقط پیش نویس های یکپارچه می توانند توکن خود را تولید کنند.',
            'regenerate-only-active' => 'فقط توکن های فعال قابل بازسازی هستند.',
            'cannot-edit-historic' => 'توکن های باطل شده یا بازسازی شده قابل ویرایش نیستند.',
            'already-inactive' => 'این نشانه در حال حاضر غیرفعال است.',
        ],

        'errors' => [
            'admin-has-token' => 'سرپرست انتخاب شده از قبل یک نشانه ادغام فعال دارد.',
            'only-draft-generate' => 'فقط توکن‌های پیش‌نویس قابل تولید هستند.',
        ],

        'validation' => [
            'ip-invalid' => 'هر IP مجاز باید یک آدرس IPv4 یا IPv6 معتبر باشد (نماد CIDR پشتیبانی می شود).',
            'cidr-prefix-invalid' => 'پیشوند CIDR برای نسخه IP داده شده نامعتبر است.',
        ],

        'configuration' => [
            'api' => [
                'title' => 'API',
                'info' => 'تنظیمات API Bagisto و ماژول‌های مدیریت آن.',
            ],

            'integration' => [
                'title' => 'یکپارچه سازی',
                'info' => 'افزونه یکپارچه سازی API را که برای صدور توکن های API مدیریت استفاده می شود، مدیریت کنید.',
            ],

            'settings' => [
                'title' => 'تنظیمات ماژول',
                'info' => 'افزونه API Integration را فعال یا غیرفعال کنید. در صورت غیرفعال شدن، منوی نوار کناری آن پنهان می شود و صفحات آن 404 را برمی گرداند.',
                'enable' => 'ماژول یکپارچه سازی API را فعال کنید',
            ],
        ],

        'emails' => [
            'generated' => [
                'subject' => 'یک نشانه API جدید ایجاد شد: :name',
                'greeting' => 'یک کد ادغام API به نام ":name" به تازگی در حساب شما ایجاد شده است.',
            ],

            'regenerated' => [
                'subject' => 'کد API شما بازسازی شد: :name',
                'greeting' => 'رمز ادغام API با نام ":name" به تازگی بازسازی شد. توکن قبلی از کار افتاده است - فقط نشانه جدید معتبر است.',
            ],

            'revoked' => [
                'subject' => 'کد API شما باطل شد: :name',
                'greeting' => 'رمز ادغام API با نام ":name" لغو شد. هر سرویس گیرنده ای که از آن استفاده می کند، دسترسی خود را از دست داده است.',
            ],

            'details' => [
                'name' => 'نام رمز',
                'date' => 'تاریخ',
                'ip' => 'از IP',
            ],

            'revoke-hint' => 'اگر انتظار این را نداشتید، با استفاده از دکمه زیر فورا توکن را باطل کنید.',
            'revoke-btn' => 'این توکن را لغو کنید',
            'revoke-expiry' => 'این پیوند لغو به مدت 7 روز معتبر است. پس از آن، برای مدیریت توکن، وارد پنل مدیریت شوید.',
            'no-action' => 'هیچ اقدامی لازم نیست - این ایمیل فقط یک تایید است.',
        ],

        'revoke-confirmation' => [
            'title' => 'لغو توکن API',
            'confirm-title' => 'این توکن لغو شود؟',
            'confirm-message' => 'توکن ":name" بلافاصله برای همه کلاینت‌هایی که از آن استفاده می‌کنند از کار می‌افتد.',
            'confirm-button' => 'لغو توکن',
            'success-title' => 'توکن باطل شد',
            'success-message' => 'نشانه ":name" باطل شده است. هر سرویس گیرنده ای که از آن استفاده می کند بلافاصله دسترسی خود را از دست داده است.',
            'already-inactive-title' => 'توکن قبلاً غیرفعال است',
            'already-inactive-message' => 'رمز ":name" قبلاً لغو یا ایجاد شده بود. هیچ اقدام دیگری لازم نیست.',
        ],

        'confirm' => [
            'generate' => [
                'title' => 'توکن تولید کنید',
                'message' => 'اکنون توکن تولید شود؟ متن ساده فقط یک بار نشان داده می شود - قبل از ترک صفحه آن را کپی کنید.',
            ],

            'regenerate' => [
                'title' => 'بازسازی توکن',
                'message' => 'توکن را بازسازی کنیم؟ توکن قدیمی فوراً کار نمی کند و متن ساده جدید فقط یک بار نشان داده می شود.',
            ],

            'revoke' => [
                'title' => 'لغو توکن',
                'message' => 'این نشانه لغو شود؟ هر سرویس گیرنده ای که از آن استفاده کند بلافاصله دسترسی خود را از دست می دهد. این عمل قابل لغو نیست.',
            ],
        ],
    ],
];
