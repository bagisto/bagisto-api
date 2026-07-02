<?php

return [
    'graphql' => [
        'cart' => [
            'authentication-required'           => 'توکن احراز هویت مورد نیاز است',
            'invalid-token'                     => 'توکن احراز هویت نامعتبر یا منقضی شده است',
            'unauthorized-access'               => 'دسترسی غیرمجاز به سبد خرید',
            'authenticated-only'                => 'فقط کاربران احراز هویت‌شده می‌توانند سبد خرید خود را دریافت کنند',
            'merge-requires-auth'               => 'ادغام مهمان به احراز هویت نیاز دارد',
            'unknown-operation'                 => 'عملیات ناشناخته سبد خرید',

            'cart-not-found'                    => 'سبد خرید یافت نشد',
            'guest-cart-not-found'              => 'سبد خرید مهمان یافت نشد',
            'product-not-found'                 => 'محصول یافت نشد',

            'product-id-quantity-required'      => 'شناسه محصول و تعداد الزامی است',
            'cart-item-id-quantity-required'    => 'شناسه آیتم سبد خرید و تعداد الزامی است',
            'cart-item-id-required'             => 'شناسه آیتم سبد خرید الزامی است',
            'item-ids-required'                 => 'آرایه شناسه‌های آیتم الزامی است',
            'coupon-code-required'              => 'کد کوپن الزامی است',
            'address-data-required'             => 'کشور، استان و کد پستی الزامی است',

            'add-product-failed'                => 'افزودن محصول به سبد خرید ناموفق بود',
            'update-item-failed'                => 'به‌روزرسانی آیتم سبد خرید ناموفق بود',
            'remove-item-failed'                => 'حذف آیتم سبد خرید ناموفق بود',
            'apply-coupon-failed'               => 'اعمال کوپن ناموفق بود',
            'remove-coupon-failed'              => 'حذف کوپن ناموفق بود',
            'move-to-wishlist-failed'           => 'انتقال آیتم به فهرست علاقه‌مندی‌ها ناموفق بود',
            'estimate-shipping-failed'          => 'برآورد هزینه ارسال ناموفق بود',

            'product-added-successfully'         => 'محصول با موفقیت به سبد خرید اضافه شد',
            'guest-cart-merged'                  => 'سبد خرید مهمان با موفقیت ادغام شد',
            'using-authenticated-cart'           => 'استفاده از سبد خرید مشتری احراز هویت‌شده',
            'cart-item-not-found'                => 'آیتم سبد خرید یافت نشد',
            'new-guest-cart-created'             => 'سبد خرید مهمان جدید با توکن نشست منحصربه‌فرد ایجاد شد',
            'select-items-to-remove'             => 'لطفاً آیتم‌هایی را برای حذف انتخاب کنید',
            'select-items-to-move-wishlist'      => 'لطفاً آیتم‌هایی را برای انتقال به فهرست علاقه‌مندی‌ها انتخاب کنید',
            'invalid-or-expired-token'           => 'توکن سبد خرید نامعتبر یا منقضی شده است. لطفاً یک سبد خرید جدید ایجاد کنید.',
            'invalid-token-of-login-user'        => 'توکن کاربر وارد شده نامعتبر است.',
        ],

        'token-verification' => [
            'invalid-operation'                 => 'عملیات نامعتبر',
            'invalid-input-data'                => 'داده‌های ورودی نامعتبر',
            'token-required'                    => 'توکن الزامی است',
            'invalid-token-format'              => 'قالب توکن نامعتبر',
            'token-not-found-or-expired'        => 'توکن یافت نشد یا منقضی شده است',
            'customer-not-found'                => 'مشتری یافت نشد',
            'customer-account-suspended'        => 'حساب مشتری معلق شده است',
            'error-verifying-token'             => 'خطا در تأیید توکن',
            'token-is-valid'                    => 'توکن معتبر است',
        ],

        'forgot-password' => [
            'invalid-operation'                 => 'عملیات نامعتبر',
            'invalid-input-data'                => 'داده‌های ورودی نامعتبر',
            'email-required'                    => 'ایمیل الزامی است',
            'reset-link-sent'                   => 'لینک بازنشانی با موفقیت به ایمیل شما ارسال شد',
            'email-not-found'                   => 'آدرس ایمیل یافت نشد',
            'error-sending-reset-link'          => 'هنگام ارسال لینک بازنشانی خطایی رخ داد',
        ],

        'logout' => [
            'invalid-operation'                 => 'عملیات نامعتبر',
            'invalid-input-data'                => 'داده‌های ورودی نامعتبر',
            'token-required'                    => 'توکن الزامی است',
            'invalid-token-format'              => 'قالب توکن نامعتبر',
            'logged-out-successfully'           => 'با موفقیت خارج شدید',
            'token-not-found-or-expired'        => 'توکن یافت نشد یا قبلاً منقضی شده است',
            'error-during-logout'               => 'خطا هنگام خروج از سیستم',
        ],

        'address' => [
            'deleted-successfully'              => 'آدرس با موفقیت حذف شد',
            'authentication-required'           => 'توکن احراز هویت مورد نیاز است',
            'invalid-token'                     => 'توکن نامعتبر یا منقضی شده است',
            'unknown-operation'                 => 'عملیات ناشناخته',
            'address-id-required'               => 'شناسه آدرس الزامی است',
            'address-not-found'                 => 'آدرس یافت نشد یا متعلق به این مشتری نیست',
            'retrieved'                         => 'آدرس‌ها با موفقیت بازیابی شدند',
            'fetch-failed'                      => 'بازیابی آدرس‌ها ناموفق بود:',
        ],

        'customer-profile' => [
            'authentication-required'           => 'توکن احراز هویت مورد نیاز است. لطفاً توکن را در ورودی پرس‌وجو ارائه دهید',
            'invalid-token'                     => 'توکن نامعتبر یا منقضی شده است',
        ],

        'customer' => [
            'password-mismatch'                 => 'رمز عبور و تأیید رمز عبور مطابقت ندارند',
            'confirm-password-required'         => 'هنگام تغییر رمز عبور، تأیید رمز عبور الزامی است',
            'unauthenticated'                   => 'احراز هویت نشده. لطفاً برای انجام این عمل وارد شوید',
        ],

        'product-review' => [
            'product-id-required'               => 'شناسه محصول الزامی است',
            'product-not-found'                 => 'محصول یافت نشد',
            'rating-invalid'                    => 'امتیاز باید بین 1 و 5 باشد',
            'title-required'                    => 'عنوان بررسی الزامی است',
            'comment-required'                  => 'نظر بررسی الزامی است',
        ],

        'product' => [
            'not-found-with-sku'                => 'No product found with SKU',
            'not-found-with-url-key'            => 'No product found with URL key',
            'parameters-required'               => 'At least one of the following parameters must be provided: "sku", "id", "urlKey"',
        ],

        'auth' => [
            'no-token-provided'                 => 'هیچ توکن احراز هویتی ارائه نشده است. لطفاً توکن را در هدر Authorization به صورت "Bearer <token>" یا در فیلد input.token ارائه دهید',
            'invalid-or-expired-token'          => 'توکن نامعتبر یا منقضی شده است',
            'request-not-found'                 => 'درخواست در زمینه یافت نشد',
            'token-required'                    => 'Authentication token is required. Please provide the token either in the GraphQL mutation input field or in the Authorization header as "Bearer <token>"',
            'unknown-resource'                  => 'منبع ناشناخته',
            'cannot-update-other-profile'       => 'غیرمجاز: نمی‌توان پروفایل مشتری دیگری را به‌روزرسانی کرد',
        ],

        'upload' => [
            'invalid-base64'                    => 'داده‌های تصویر کدگذاری‌شده با base64 نامعتبر است',
            'size-exceeds-limit'                => 'اندازه تصویر نباید از 5 مگابایت بیشتر باشد',
            'invalid-format'                    => 'قالب تصویر نامعتبر. لطفاً تصویر کدگذاری‌شده با base64 را با طرح data URI ارائه دهید (data:image/jpeg;base64,...)',
            'failed'                            => 'بارگذاری تصویر ناموفق بود',
        ],

        'attribute' => [
            'code-already-exists'               => 'کد ویژگی از قبل وجود دارد',
        ],

        'login' => [
            'invalid-credentials'               => 'ایمیل یا رمز عبور نامعتبر',
            'account-suspended'                 => 'حساب شما معلق شده است',
            'successful'                        => 'شما با موفقیت وارد شدید',
            'invalid-request'                   => 'درخواست ورود نامعتبر',
        ],

        'checkout' => [
            'invalid-input'                     => 'داده‌های ورودی نامعتبر برای عملیات تسویه‌حساب',
            'billing-address-required'          => 'آدرس صورت‌حساب الزامی است',
            'shipping-address-required'         => 'آدرس ارسال برای محموله‌ها الزامی است',
            'address-save-failed'               => 'ذخیره آدرس ناموفق بود',
            'address-saved'                     => 'آدرس با موفقیت ذخیره شد',
            'shipping-method-required'          => 'روش ارسال الزامی است',
            'invalid-shipping-method'           => 'روش ارسال نامعتبر یا غیرقابل دسترس',
            'shipping-method-save-failed'       => 'ذخیره روش ارسال ناموفق بود',
            'shipping-method-saved'             => 'روش ارسال با موفقیت ذخیره شد',
            'shipping-method-error'             => 'خطا در ذخیره روش ارسال',
            'payment-method-required'           => 'روش پرداخت الزامی است',
            'invalid-payment-method'            => 'روش پرداخت نامعتبر یا غیرقابل دسترس',
            'payment-method-save-failed'        => 'ذخیره روش پرداخت ناموفق بود',
            'payment-method-saved'              => 'روش پرداخت با موفقیت ذخیره شد',
            'payment-method-error'              => 'خطا در ذخیره روش پرداخت',
            'order-creation-failed'             => 'ایجاد سفارش ناموفق بود: شناسه سفارش تهی است یا سفارش ذخیره نشده است',
            'order-retrieval-failed'            => 'بازیابی سفارش ایجاد شده ناموفق بود',
            'order-creation-error'              => 'ایجاد سفارش ناموفق بود',
            'cart-empty'                        => 'سبد خرید خالی است',
            'account-suspended'                 => 'حساب شما معلق شده است. لطفاً با پشتیبانی تماس بگیرید.',
            'account-inactive'                  => 'حساب شما غیرفعال است. لطفاً با پشتیبانی تماس بگیرید.',
            'minimum-order-not-met'             => 'حداقل مبلغ سفارش :amount است',
            'email-required'                    => 'آدرس ایمیل برای ایجاد سفارش الزامی است',
            'unknown-operation'                 => 'عملیات ناشناخته تسویه‌حساب',
        ],

        'customer-addresses' => [
            'token-required'                    => 'توکن برای دریافت آدرس‌های مشتری الزامی است',
            'invalid-or-expired-token'          => 'توکن نامعتبر یا منقضی شده است',
            'token-validation-failed'           => 'اعتبارسنجی توکن ناموفق بود',
        ],

        'product' => [
            'type'                              => 'نوع محصول',
            'attribute-family'                  => 'خانواده ویژگی',
            'sku'                               => 'SKU',
            'name'                              => 'نام',
            'description'                       => 'توضیحات',
            'short-description'                 => 'توضیحات کوتاه',
            'status'                            => 'وضعیت',
            'new'                               => 'جدید',
            'featured'                          => 'ویژه',
            'price'                             => 'قیمت',
            'special-price'                     => 'قیمت ویژه',
            'weight'                            => 'وزن',
            'cost'                              => 'هزینه',
            'length'                            => 'طول',
            'width'                             => 'عرض',
            'height'                            => 'ارتفاع',
            'color'                             => 'رنگ',
            'size'                              => 'اندازه',
            'brand'                             => 'برند',
            'super-attributes'                  => 'ابرویژگی‌ها',
        ],

        'compare-item' => [
            'id-required'                       => 'شناسه مورد مقایسه الزامی است',
            'invalid-id-format'                 => 'قالب شناسه نامعتبر. قالب IRI مانند "/api/shop/compare-items/1" یا شناسه عددی انتظار می رود',
            'not-found'                         => 'مورد مقایسه یافت نشد',
            'product-id-required'               => 'شناسه محصول الزامی است',
            'customer-id-required'              => 'شناسه مشتری الزامی است',
            'product-not-found'                 => 'محصول یافت نشد',
            'customer-not-found'                => 'مشتری یافت نشد',
            'already-exists'                    => 'این محصول قبلاً در فهرست مقایسه شما وجود دارد',
        ],

        'downloadable-product' => [
            'download-link-not-found'           => 'لینک دانلود یافت نشد یا منقضی شده است',
            'purchased-link-not-found'          => 'لینک خریداری شده یافت نشد',
            'file-not-found'                    => 'فایل یافت نشد',
            'download-successful'               => 'فایل برای دانلود آماده است',
            'token-required'                    => 'توکن دانلود مورد نیاز است',
            'invalid-token'                     => 'توکن دانلود نامعتبر یا منقضی شده است',
            'token-expired'                     => 'توکن دانلود منقضی شده است. لطفاً توکن جدیدی ایجاد کنید',
            'access-denied'                     => 'دسترسی رد شد: شما مجاز به دانلود این فایل نیستید',
            'redirect-external-url'             => 'تغییر مسیر به URL دانلود خارجی',
            'file-error'                        => 'هنگام پردازش درخواست دانلود شما خطایی رخ داد',
            'unauthorized-access'               => 'دسترسی غیرمجاز به منبع دانلود',
        ],
    ],

    'integration' => [
        'menu' => [
            'title'  => 'یکپارچه سازی',
            'tokens' => 'توکن ها',
        ],

        'history' => [
            'menu' => [
                'title' => 'تاریخچه',
            ],

            'acl' => [
                'title'  => 'تاریخچه تغییرات API',
                'delete' => 'حذف تاریخچه',
            ],

            'index' => [
                'title'           => 'تاریخچه تغییرات API',
                'info'            => 'هر ایجاد، به‌روزرسانی و حذفی که از طریق API مدیریت انجام می‌شود، با چه کسی آن را انجام داده است، کدام نشانه و چه چیزی تغییر کرده است.',
                'cleanup-btn'     => 'لاگ های قدیمی را حذف کنید',
                'cleanup-days'    => 'گزارش های قدیمی تر از این چند روز را حذف کنید',
                'cleanup-confirm' => 'تمام سابقه قدیمی‌تر از تعداد روزهای معین حذف شود؟ این قابل واگرد نیست.',
            ],

            'view' => [
                'title'             => 'تغییر دهید',
                'back-btn'          => 'برگشت',
                'admin'             => 'مدیر',
                'token'             => 'رمز',
                'action'            => 'اقدام',
                'resource'          => 'منبع',
                'method'            => 'روش',
                'ip'                => 'آدرس IP',
                'date'              => 'تاریخ',
                'version'           => 'نسخه',
                'url'               => 'نقطه پایانی',
                'request-details'   => 'جزئیات درخواست',
                'changes'           => 'تغییرات',
                'field'             => 'میدان',
                'old'               => 'ارزش قدیمی',
                'new'               => 'ارزش جدید',
                'no-field-changes'  => 'هیچ تغییری در سطح زمینه برای این ورودی ثبت نشد.',
                'same-request'      => 'تغییرات دیگر در همین درخواست',
                'version-chain'     => 'تاریخچه نسخه این رکورد',
            ],

            'datagrid' => [
                'id'        => 'شناسه',
                'date'      => 'تاریخ',
                'admin'     => 'مدیر',
                'token'     => 'رمز',
                'action'    => 'اقدام',
                'operation' => 'عملیات',
                'resource'  => 'منبع',
                'version'   => 'نسخه',
                'method'    => 'روش',
                'ip'        => 'IP',
                'view'      => 'مشاهده کنید',
                'delete'    => 'حذف کنید',
            ],

            'events' => [
                'created' => 'ایجاد شد',
                'updated' => 'به روز شد',
                'deleted' => 'حذف شد',
            ],

            'deleted'                => ':count سابقه(های) سابقه حذف شد.',
            'cleanup-input-required' => 'تعدادی روز یا تاریخی برای پاکسازی ارائه دهید.',
        ],

        'acl' => [
            'title'      => 'یکپارچه سازی',
            'create'     => 'ایجاد یکپارچگی',
            'edit'       => 'ویرایش یکپارچه سازی',
            'delete'     => 'لغو رمز ادغام',
            'generate'   => 'توکن ادغام ایجاد کنید',
            'regenerate' => 'Regenerate Token ادغام',
        ],

        'index' => [
            'title'      => 'ادغام ها',
            'create-btn' => 'ایجاد یکپارچگی',
        ],

        'create' => [
            'title'    => 'ایجاد یکپارچگی',
            'save-btn' => 'ذخیره کنید',
            'back-btn' => 'برگشت',
        ],

        'edit' => [
            'title'          => 'ویرایش یکپارچه سازی',
            'save-btn'       => 'ذخیره کنید',
            'back-btn'       => 'برگشت',
            'generate-btn'   => 'توکن تولید کنید',
            'regenerate-btn' => 'بازسازی توکن',
            'revoke-btn'     => 'لغو توکن',
            'copy-btn'       => 'کپی کنید',
            'token-warning'  => 'اکنون این نشانه را ذخیره کنید - دیگر نشان داده نخواهد شد.',
            'token-label'    => 'رمز',
            'not-generated'  => 'هنوز تولید نشده است',
            'masked'         => '(ذخیره شده - فقط یک بار در هر نسل نشان داده می شود)',
            'history-banner' => 'این نشانه دیگر فعال نیست.',
        ],

        'fields' => [
            'name'                  => 'نام',
            'description'           => 'توضیحات',
            'assign-user'           => 'اختصاص کاربر',
            'permission-type'       => 'نوع مجوز',
            'access-control'        => 'کنترل دسترسی',
            'general'               => 'ژنرال',
            'token-settings'        => 'تنظیمات توکن',
            'valid-till'            => 'معتبر تا',
            'rate-limit-per-minute' => 'محدودیت نرخ (در دقیقه)',
            'rate-limit-per-day'    => 'محدودیت نرخ (در روز)',
            'never-expires'         => 'هرگز منقضی نمی شود',
            'expires-on'            => 'منقضی می شود',
            'unlimited'             => 'نامحدود',
            'limit-to'              => 'محدود به',
            'requests-per-minute'   => 'درخواست ها / دقیقه',
            'requests-per-day'      => 'درخواست ها / روز',
            'select-admin'          => 'یک مدیر انتخاب کنید',
            'no-available-admins'   => 'هیچ مدیری در دسترس نیست - هر سرپرست قبلاً یک توکن فعال دارد.',
            'same-as-web-hint'      => 'Token مجوزهای نقش فعلی سرپرست اختصاص داده شده را به صورت زنده منعکس می کند.',
            'ip-allowlist'          => 'لیست مجاز IP',
            'ip-any'                => 'هر IP (پیش‌فرض)',
            'ip-restricted'         => 'محدود به IP های خاص',
            'ip-list-hint'          => 'یک ورودی در هر خط پشتیبانی از IPv4، IPv6 و CIDR (به عنوان مثال 10.0.0.0/24 یا 2001:db8::/32). برای اجازه دادن به همه IP ها خالی بگذارید.',
        ],

        'permission_type' => [
            'all'         => 'همه',
            'custom'      => 'سفارشی',
            'same_as_web' => 'همان Web Permission',
        ],

        'status' => [
            'draft'       => 'پیش نویس',
            'active'      => 'فعال',
            'revoked'     => 'لغو شد',
            'regenerated' => 'بازسازی شد',
        ],

        'datagrid' => [
            'id'              => 'شناسه',
            'name'            => 'نام',
            'admin'           => 'مدیر',
            'token'           => 'رمز',
            'status'          => 'وضعیت',
            'permission-type' => 'نوع مجوز',
            'expires-at'      => 'معتبر تا',
            'last-used-at'    => 'آخرین استفاده',
            'created-at'      => 'ایجاد شده در',
            'edit'            => 'ویرایش کنید',
            'revoke'          => 'لغو',
        ],

        'messages' => [
            'draft-created'          => 'یکپارچه سازی ایجاد شد. توکن را برای شروع استفاده از آن تولید کنید.',
            'updated'                => 'ادغام با موفقیت به روز شد.',
            'generated'              => 'توکن تولید شد. اکنون آن را کپی کنید - دیگر نشان داده نخواهد شد.',
            'regenerated'            => 'توکن بازسازی شد. اکنون توکن جدید را کپی کنید - دیگر نشان داده نخواهد شد.',
            'revoked'                => 'توکن با موفقیت لغو شد.',
            'generate-only-draft'    => 'فقط پیش نویس های یکپارچه می توانند توکن خود را تولید کنند.',
            'regenerate-only-active' => 'فقط توکن های فعال قابل بازسازی هستند.',
            'cannot-edit-historic'   => 'توکن های باطل شده یا بازسازی شده قابل ویرایش نیستند.',
            'already-inactive'       => 'این نشانه در حال حاضر غیرفعال است.',
        ],

        'errors' => [
            'admin-has-token' => 'سرپرست انتخاب شده از قبل یک نشانه ادغام فعال دارد.',
        ],

        'validation' => [
            'ip-invalid'           => 'هر IP مجاز باید یک آدرس IPv4 یا IPv6 معتبر باشد (نماد CIDR پشتیبانی می شود).',
            'cidr-prefix-invalid'  => 'پیشوند CIDR برای نسخه IP داده شده نامعتبر است.',
        ],

        'configuration' => [
            'api' => [
                'title' => 'API',
                'info'  => 'تنظیمات API Bagisto و ماژول‌های مدیریت آن.',
            ],
            'integration' => [
                'title' => 'یکپارچه سازی',
                'info'  => 'افزونه یکپارچه سازی API را که برای صدور توکن های API مدیریت استفاده می شود، مدیریت کنید.',
            ],
            'settings' => [
                'title'  => 'تنظیمات ماژول',
                'info'   => 'افزونه API Integration را فعال یا غیرفعال کنید. در صورت غیرفعال شدن، منوی نوار کناری آن پنهان می شود و صفحات آن 404 را برمی گرداند.',
                'enable' => 'ماژول یکپارچه سازی API را فعال کنید',
            ],
        ],

        'emails' => [
            'generated' => [
                'subject'  => 'یک نشانه API جدید ایجاد شد: :name',
                'greeting' => 'یک کد ادغام API به نام ":name" به تازگی در حساب شما ایجاد شده است.',
            ],
            'regenerated' => [
                'subject'  => 'کد API شما بازسازی شد: :name',
                'greeting' => 'رمز ادغام API با نام ":name" به تازگی بازسازی شد. توکن قبلی از کار افتاده است - فقط نشانه جدید معتبر است.',
            ],
            'revoked' => [
                'subject'  => 'کد API شما باطل شد: :name',
                'greeting' => 'رمز ادغام API با نام ":name" لغو شد. هر سرویس گیرنده ای که از آن استفاده می کند، دسترسی خود را از دست داده است.',
            ],

            'details' => [
                'name' => 'نام رمز',
                'date' => 'تاریخ',
                'ip'   => 'از IP',
            ],

            'revoke-hint'   => 'اگر انتظار این را نداشتید، با استفاده از دکمه زیر فورا توکن را باطل کنید.',
            'revoke-btn'    => 'این توکن را لغو کنید',
            'revoke-expiry' => 'این پیوند لغو به مدت 7 روز معتبر است. پس از آن، برای مدیریت توکن، وارد پنل مدیریت شوید.',
            'no-action'     => 'هیچ اقدامی لازم نیست - این ایمیل فقط یک تایید است.',
        ],

        'revoke-confirmation' => [
            'title'                    => 'لغو توکن API',
            'success-title'            => 'توکن باطل شد',
            'success-message'          => 'نشانه ":name" باطل شده است. هر سرویس گیرنده ای که از آن استفاده می کند بلافاصله دسترسی خود را از دست داده است.',
            'already-inactive-title'   => 'توکن قبلاً غیرفعال است',
            'already-inactive-message' => 'رمز ":name" قبلاً لغو یا ایجاد شده بود. هیچ اقدام دیگری لازم نیست.',
        ],

        'confirm' => [
            'generate' => [
                'title'   => 'توکن تولید کنید',
                'message' => 'اکنون توکن تولید شود؟ متن ساده فقط یک بار نشان داده می شود - قبل از ترک صفحه آن را کپی کنید.',
            ],
            'regenerate' => [
                'title'   => 'بازسازی توکن',
                'message' => 'توکن را بازسازی کنیم؟ توکن قدیمی فوراً کار نمی کند و متن ساده جدید فقط یک بار نشان داده می شود.',
            ],
            'revoke' => [
                'title'   => 'لغو توکن',
                'message' => 'این نشانه لغو شود؟ هر سرویس گیرنده ای که از آن استفاده کند بلافاصله دسترسی خود را از دست می دهد. این عمل قابل لغو نیست.',
            ],
        ],
    ],
];
