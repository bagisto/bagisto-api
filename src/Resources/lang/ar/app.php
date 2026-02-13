<?php

return [
    'graphql' => [
        'cart' => [
            'authentication-required'           => 'مطلوب رمز المصادقة',
            'invalid-token'                     => 'رمز المصادقة غير صحيح أو منتهي الصلاحية',
            'unauthorized-access'               => 'وصول غير مصرح به إلى السلة',
            'authenticated-only'                => 'يمكن للمستخدمين المصرح لهم فقط جلب عرباتهم',
            'merge-requires-auth'               => 'دمج السلة يتطلب المصادقة',
            'unknown-operation'                 => 'عملية سلة غير معروفة',

            'cart-not-found'                    => 'لم يتم العثور على السلة',
            'guest-cart-not-found'              => 'لم يتم العثور على سلة الضيف',
            'product-not-found'                 => 'لم يتم العثور على المنتج',

            'product-id-quantity-required'      => 'معرف المنتج والكمية مطلوبان',
            'cart-item-id-quantity-required'    => 'معرف عنصر السلة والكمية مطلوبان',
            'cart-item-id-required'             => 'معرف عنصر السلة مطلوب',
            'item-ids-required'                 => 'مصفوفة معرفات العناصر مطلوبة',
            'coupon-code-required'              => 'رمز القسيمة مطلوب',
            'address-data-required'             => 'البلد والولاية والرمز البريدي مطلوبة',

            'add-product-failed'                => 'فشل إضافة المنتج إلى السلة',
            'update-item-failed'                => 'فشل تحديث عنصر السلة',
            'remove-item-failed'                => 'فشل إزالة عنصر السلة',
            'apply-coupon-failed'               => 'فشل تطبيق القسيمة',
            'remove-coupon-failed'              => 'فشل إزالة القسيمة',
            'move-to-wishlist-failed'           => 'فشل نقل العنصر إلى قائمة الرغبات',
            'estimate-shipping-failed'          => 'فشل تقدير الشحن',

            'product-added-successfully'         => 'تمت إضافة المنتج إلى السلة بنجاح',
            'guest-cart-merged'                  => 'تم دمج سلة الضيف بنجاح',
            'using-authenticated-cart'           => 'استخدام سلة العميل المصرح له',
            'cart-item-not-found'                => 'لم يتم العثور على عنصر السلة',
            'new-guest-cart-created'             => 'تم إنشاء سلة ضيف جديدة برمز جلسة فريد',
            'select-items-to-remove'             => 'يرجى تحديد العناصر المراد إزالتها',
            'select-items-to-move-wishlist'      => 'يرجى تحديد العناصر المراد نقلها إلى قائمة الرغبات',
            'invalid-or-expired-token'           => 'رمز السلة غير صحيح أو منتهي الصلاحية. يرجى إنشاء سلة جديدة.',
            'invalid-token-of-login-user'        => 'رمز المستخدم المسجل دخوله غير صحيح.',
        ],

        'token-verification' => [
            'invalid-operation'                 => 'عملية غير صحيحة',
            'invalid-input-data'                => 'بيانات إدخال غير صحيحة',
            'token-required'                    => 'مطلوب الرمز',
            'invalid-token-format'              => 'تنسيق رمز غير صحيح',
            'token-not-found-or-expired'        => 'الرمز غير موجود أو انتهت صلاحيته',
            'customer-not-found'                => 'لم يتم العثور على العميل',
            'customer-account-suspended'        => 'حساب العميل معلق',
            'error-verifying-token'             => 'خطأ في التحقق من الرمز',
            'token-is-valid'                    => 'الرمز صحيح',
        ],

        'forgot-password' => [
            'invalid-operation'                 => 'عملية غير صحيحة',
            'invalid-input-data'                => 'بيانات إدخال غير صحيحة',
            'email-required'                    => 'البريد الإلكتروني مطلوب',
            'reset-link-sent'                   => 'تم إرسال رابط إعادة التعيين بنجاح إلى بريدك الإلكتروني',
            'email-not-found'                   => 'عنوان البريد الإلكتروني غير موجود',
            'error-sending-reset-link'          => 'حدث خطأ أثناء إرسال رابط إعادة التعيين',
        ],

        'logout' => [
            'invalid-operation'                 => 'عملية غير صحيحة',
            'invalid-input-data'                => 'بيانات إدخال غير صحيحة',
            'token-required'                    => 'مطلوب الرمز',
            'invalid-token-format'              => 'تنسيق رمز غير صحيح',
            'logged-out-successfully'           => 'تم تسجيل الخروج بنجاح',
            'token-not-found-or-expired'        => 'الرمز غير موجود أو انتهت صلاحيته بالفعل',
            'error-during-logout'               => 'خطأ أثناء تسجيل الخروج',
        ],

        'address' => [
            'deleted-successfully'              => 'تم حذف العنوان بنجاح',
            'authentication-required'           => 'مطلوب رمز المصادقة',
            'invalid-token'                     => 'رمز غير صحيح أو منتهي الصلاحية',
            'unknown-operation'                 => 'عملية غير معروفة',
            'address-id-required'               => 'معرف العنوان مطلوب',
            'address-not-found'                 => 'لم يتم العثور على العنوان أو لا ينتمي إلى هذا العميل',
            'retrieved'                         => 'تم استرجاع العناوين بنجاح',
            'fetch-failed'                      => 'فشل في جلب العناوين:',
        ],

        'customer-profile' => [
            'authentication-required'           => 'مطلوب رمز المصادقة. يرجى توفير الرمز في إدخال الاستعلام',
            'invalid-token'                     => 'رمز غير صحيح أو منتهي الصلاحية',
        ],

        'customer' => [
            'password-mismatch'                 => 'كلمة المرور وتأكيد كلمة المرور غير متطابقة',
            'confirm-password-required'         => 'تأكيد كلمة المرور مطلوب عند تغيير كلمة المرور',
            'unauthenticated'                   => 'غير مصرح. يرجى تسجيل الدخول لتنفيذ هذا الإجراء',
        ],

        'product-review' => [
            'product-id-required'               => 'معرف المنتج مطلوب',
            'product-not-found'                 => 'لم يتم العثور على المنتج',
            'rating-invalid'                    => 'يجب أن تكون التقييمات بين 1 و 5',
            'title-required'                    => 'عنوان المراجعة مطلوب',
            'comment-required'                  => 'تعليق المراجعة مطلوب',
        ],

        'product' => [
            'not-found-with-sku'                => 'لم يتم العثور على منتج برمز SKU',
            'not-found-with-url-key'            => 'لم يتم العثور على منتج برمز URL',
            'parameters-required'               => 'يجب توفير واحد على الأقل من المعاملات التالية: "sku" أو "id" أو "urlKey"',
        ],

        'auth' => [
            'no-token-provided'                 => 'لم يتم توفير رمز المصادقة. يرجى توفير الرمز في رأس Authorization كـ "Bearer <token>" أو في حقل input.token',
            'invalid-or-expired-token'          => 'رمز غير صحيح أو منتهي الصلاحية',
            'request-not-found'                 => 'الطلب غير موجود في السياق',
            'token-required'                    => 'مطلوب رمز المصادقة. يرجى توفير الرمز إما في حقل إدخال الطفرة أو في رأس Authorization كـ "Bearer <token>"',
            'unknown-resource'                  => 'مورد غير معروف',
            'cannot-update-other-profile'       => 'غير مصرح: لا يمكن تحديث ملف شخصي آخر',
        ],

        'upload' => [
            'invalid-base64'                    => 'بيانات صورة base64 مشفرة غير صحيحة',
            'size-exceeds-limit'                => 'يجب ألا يتجاوز حجم الصورة 5 ميجابايت',
            'invalid-format'                    => 'تنسيق صورة غير صحيح. يرجى توفير صورة مشفرة بـ base64 مع نظام data URI (data:image/jpeg;base64,...)',
            'failed'                            => 'فشل تحميل الصورة',
        ],

        'attribute' => [
            'code-already-exists'               => 'رمز السمة موجود بالفعل',
        ],

        'login' => [
            'invalid-credentials'               => 'بريد إلكتروني أو كلمة مرور غير صحيحة',
            'account-suspended'                 => 'تم تعليق حسابك',
            'successful'                        => 'لقد قمت بتسجيل الدخول بنجاح',
            'invalid-request'                   => 'طلب تسجيل دخول غير صحيح',
        ],

        'checkout' => [
            'invalid-input'                     => 'بيانات إدخال غير صحيحة لعملية الدفع',
            'billing-address-required'          => 'عنوان الفاتورة مطلوب',
            'shipping-address-required'         => 'عنوان الشحن مطلوب للشحنات',
            'address-save-failed'               => 'فشل حفظ العنوان',
            'address-saved'                     => 'تم حفظ العنوان بنجاح',
            'shipping-method-required'          => 'طريقة الشحن مطلوبة',
            'invalid-shipping-method'           => 'طريقة شحن غير صحيحة أو غير متاحة',
            'shipping-method-save-failed'       => 'فشل حفظ طريقة الشحن',
            'shipping-method-saved'             => 'تم حفظ طريقة الشحن بنجاح',
            'shipping-method-error'             => 'خطأ في حفظ طريقة الشحن',
            'payment-method-required'           => 'طريقة الدفع مطلوبة',
            'invalid-payment-method'            => 'طريقة دفع غير صحيحة أو غير متاحة',
            'payment-method-save-failed'        => 'فشل حفظ طريقة الدفع',
            'payment-method-saved'              => 'تم حفظ طريقة الدفع بنجاح',
            'payment-method-error'              => 'خطأ في حفظ طريقة الدفع',
            'order-creation-failed'             => 'فشل إنشاء الطلب: معرف الطلب فارغ أو لم يتم الاحتفاظ بالطلب',
            'order-retrieval-failed'            => 'فشل في استرجاع الطلب المنشأ',
            'order-creation-error'              => 'فشل في إنشاء الطلب',
            'cart-empty'                        => 'السلة فارغة',
            'account-suspended'                 => 'تم تعليق حسابك. يرجى التواصل مع الدعم.',
            'account-inactive'                  => 'حسابك غير نشط. يرجى التواصل مع الدعم.',
            'minimum-order-not-met'             => 'الحد الأدنى لمبلغ الطلب هو :amount',
            'email-required'                    => 'عنوان البريد الإلكتروني مطلوب لإنشاء الطلب',
            'unknown-operation'                 => 'عملية دفع غير معروفة',
        ],

        'customer-addresses' => [
            'token-required'                    => 'الرمز مطلوب لجلب عناوين العميل',
            'invalid-or-expired-token'          => 'رمز غير صحيح أو منتهي الصلاحية',
            'token-validation-failed'           => 'فشل التحقق من الرمز',
        ],

        'product' => [
            'type'                              => 'نوع المنتج',
            'attribute-family'                  => 'عائلة السمات',
            'sku'                               => 'رمز SKU',
            'name'                              => 'الاسم',
            'description'                       => 'الوصف',
            'short-description'                 => 'وصف مختصر',
            'status'                            => 'الحالة',
            'new'                               => 'جديد',
            'featured'                          => 'مميز',
            'price'                             => 'السعر',
            'special-price'                     => 'سعر خاص',
            'weight'                            => 'الوزن',
            'cost'                              => 'التكلفة',
            'length'                            => 'الطول',
            'width'                             => 'العرض',
            'height'                            => 'الارتفاع',
            'color'                             => 'اللون',
            'size'                              => 'الحجم',
            'brand'                             => 'العلامة التجارية',
            'super-attributes'                  => 'السمات العليا',
        ],

        'compare-item' => [
            'id-required'                       => 'معرف عنصر المقارنة مطلوب',
            'invalid-id-format'                 => 'صيغة معرف غير صحيحة. صيغة IRI المتوقعة مثل "/api/shop/compare-items/1" أو معرف رقمي',
            'not-found'                         => 'عنصر المقارنة غير موجود',
            'product-id-required'               => 'معرف المنتج مطلوب',
            'customer-id-required'              => 'معرف العميل مطلوب',
            'product-not-found'                 => 'المنتج غير موجود',
            'customer-not-found'                => 'العميل غير موجود',
            'already-exists'                    => 'هذا المنتج موجود بالفعل في قائمة المقارنة الخاصة بك',
        ],

        'downloadable-product' => [
            'download-link-not-found'           => 'رابط التنزيل غير موجود أو منتهي الصلاحية',
            'purchased-link-not-found'          => 'لم يتم العثور على الرابط المشترى',
            'file-not-found'                    => 'الملف غير موجود',
            'download-successful'               => 'الملف جاهز للتنزيل',
            'token-required'                    => 'مطلوب رمز التنزيل',
            'invalid-token'                     => 'رمز التنزيل غير صحيح أو منتهي الصلاحية',
            'token-expired'                     => 'انتهت صلاحية رمز التنزيل. يرجى إنشاء رمز جديد',
            'access-denied'                     => 'تم رفض الوصول: ليس لديك إذن لتنزيل هذا الملف',
            'redirect-external-url'             => 'إعادة التوجيه إلى عنوان URL التنزيل الخارجي',
            'file-error'                        => 'حدث خطأ أثناء معالجة طلب التنزيل الخاص بك',
            'unauthorized-access'               => 'الوصول غير المصرح به إلى مورد التنزيل',
        ],
    ],
];
