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

    'integration' => [
        'menu' => [
            'title'  => 'التكامل',
            'tokens' => 'الرموز',
        ],

        'history' => [
            'menu' => [
                'title' => 'السجل',
            ],

            'acl' => [
                'title'  => 'سجل تغييرات واجهة برمجة التطبيقات',
                'delete' => 'حذف السجل',
            ],

            'index' => [
                'title'           => 'سجل تغييرات واجهة برمجة التطبيقات',
                'info'            => 'كل عملية إنشاء وتحديث وحذف تتم عبر واجهة برمجة تطبيقات المسؤول، مع من قام بها، وأي رمز، وما الذي تغير.',
                'cleanup-btn'     => 'حذف السجلات القديمة',
                'cleanup-days'    => 'حذف السجلات الأقدم من هذه الأيام',
                'cleanup-confirm' => 'هل تريد حذف جميع السجلات الأقدم من عدد الأيام المحدد؟ لا يمكن التراجع عن هذا الإجراء.',
            ],

            'view' => [
                'title'             => 'تغيير',
                'back-btn'          => 'رجوع',
                'admin'             => 'المسؤول',
                'token'             => 'الرمز',
                'action'            => 'الإجراء',
                'resource'          => 'المورد',
                'method'            => 'الطريقة',
                'ip'                => 'عنوان IP',
                'date'              => 'التاريخ',
                'version'           => 'الإصدار',
                'url'               => 'نقطة النهاية',
                'request-details'   => 'تفاصيل الطلب',
                'changes'           => 'التغييرات',
                'field'             => 'الحقل',
                'old'               => 'القيمة القديمة',
                'new'               => 'القيمة الجديدة',
                'no-field-changes'  => 'لم يتم تسجيل تغييرات على مستوى الحقل لهذا الإدخال.',
                'same-request'      => 'تغييرات أخرى في نفس الطلب',
                'version-chain'     => 'سجل إصدارات هذا السجل',
            ],

            'datagrid' => [
                'id'        => 'المعرف',
                'date'      => 'التاريخ',
                'admin'     => 'المسؤول',
                'token'     => 'الرمز',
                'action'    => 'الإجراء',
                'operation' => 'العملية',
                'resource'  => 'المورد',
                'version'   => 'الإصدار',
                'method'    => 'الطريقة',
                'ip'        => 'IP',
                'view'      => 'عرض',
                'delete'    => 'حذف',
            ],

            'events' => [
                'created' => 'تم الإنشاء',
                'updated' => 'تم التحديث',
                'deleted' => 'تم الحذف',
            ],

            'deleted'                => 'تم حذف :count سجل(سجلات).',
            'cleanup-input-required' => 'أدخل عدد الأيام أو تاريخاً للتنظيف.',
        ],

        'acl' => [
            'title'      => 'التكامل',
            'create'     => 'إنشاء تكامل',
            'edit'       => 'تعديل التكامل',
            'delete'     => 'إبطال رمز التكامل',
            'generate'   => 'إنشاء رمز التكامل',
            'regenerate' => 'إعادة إنشاء رمز التكامل',
        ],

        'index' => [
            'title'      => 'التكاملات',
            'create-btn' => 'إنشاء تكامل',
        ],

        'create' => [
            'title'    => 'إنشاء تكامل',
            'save-btn' => 'حفظ',
            'back-btn' => 'رجوع',
        ],

        'edit' => [
            'title'          => 'تعديل التكامل',
            'save-btn'       => 'حفظ',
            'back-btn'       => 'رجوع',
            'generate-btn'   => 'إنشاء الرمز',
            'regenerate-btn' => 'إعادة إنشاء الرمز',
            'revoke-btn'     => 'إبطال الرمز',
            'copy-btn'       => 'نسخ',
            'token-warning'  => 'احفظ هذا الرمز الآن — لن يتم عرضه مرة أخرى.',
            'token-label'    => 'الرمز',
            'not-generated'  => 'لم يتم إنشاؤه بعد',
            'masked'         => '(محفوظ — يظهر مرة واحدة فقط عند الإنشاء)',
            'history-banner' => 'هذا الرمز لم يعد نشطاً.',
        ],

        'fields' => [
            'name'                  => 'الاسم',
            'description'           => 'الوصف',
            'assign-user'           => 'تعيين المستخدم',
            'permission-type'       => 'نوع الإذن',
            'access-control'        => 'التحكم في الوصول',
            'general'               => 'عام',
            'token-settings'        => 'إعدادات الرمز',
            'valid-till'            => 'صالح حتى',
            'rate-limit-per-minute' => 'حد المعدل (في الدقيقة)',
            'rate-limit-per-day'    => 'حد المعدل (في اليوم)',
            'never-expires'         => 'لا تنتهي صلاحيته',
            'expires-on'            => 'ينتهي في',
            'unlimited'             => 'غير محدود',
            'limit-to'              => 'مقتصر على',
            'requests-per-minute'   => 'طلبات / دقيقة',
            'requests-per-day'      => 'طلبات / يوم',
            'select-admin'          => 'حدد مسؤولاً',
            'no-available-admins'   => 'لا يوجد مسؤولون متاحون — كل مسؤول لديه رمز نشط بالفعل.',
            'same-as-web-hint'      => 'سيعكس الرمز أذونات دور المسؤول المعين بشكل مباشر.',
            'ip-allowlist'          => 'القائمة المسموح بها لعناوين IP',
            'ip-any'                => 'أي عنوان IP (افتراضي)',
            'ip-restricted'         => 'مقتصر على عناوين IP محددة',
            'ip-list-hint'          => 'إدخال واحد لكل سطر. يدعم IPv4 و IPv6 و CIDR (مثل 10.0.0.0/24 أو 2001:db8::/32). اتركه فارغاً للسماح لجميع عناوين IP.',
        ],

        'permission_type' => [
            'all'         => 'الكل',
            'custom'      => 'مخصص',
            'same_as_web' => 'نفس أذونات الويب',
        ],

        'status' => [
            'draft'       => 'مسودة',
            'active'      => 'نشط',
            'revoked'     => 'تم الإبطال',
            'regenerated' => 'تمت إعادة الإنشاء',
        ],

        'datagrid' => [
            'id'              => 'المعرف',
            'name'            => 'الاسم',
            'admin'           => 'المسؤول',
            'token'           => 'الرمز',
            'status'          => 'الحالة',
            'permission-type' => 'نوع الإذن',
            'expires-at'      => 'صالح حتى',
            'last-used-at'    => 'آخر استخدام',
            'created-at'      => 'تاريخ الإنشاء',
            'edit'            => 'تعديل',
            'revoke'          => 'إبطال',
        ],

        'messages' => [
            'draft-created'          => 'تم إنشاء التكامل. قم بإنشاء الرمز للبدء في استخدامه.',
            'updated'                => 'تم تحديث التكامل بنجاح.',
            'generated'              => 'تم إنشاء الرمز. انسخه الآن — لن يتم عرضه مرة أخرى.',
            'regenerated'            => 'تمت إعادة إنشاء الرمز. انسخ الرمز الجديد الآن — لن يتم عرضه مرة أخرى.',
            'revoked'                => 'تم إبطال الرمز بنجاح.',
            'generate-only-draft'    => 'فقط التكاملات في حالة مسودة يمكن إنشاء رمز لها.',
            'regenerate-only-active' => 'فقط الرموز النشطة يمكن إعادة إنشائها.',
            'cannot-edit-historic'   => 'لا يمكن تعديل الرموز المبطلة أو المعاد إنشاؤها.',
            'already-inactive'       => 'هذا الرمز غير نشط بالفعل.',
        ],

        'errors' => [
            'admin-has-token' => 'المسؤول المحدد لديه بالفعل رمز تكامل نشط.',
        ],

        'validation' => [
            'ip-invalid'           => 'يجب أن يكون كل عنوان IP مسموح به عنوان IPv4 أو IPv6 صالحاً (يدعم ترميز CIDR).',
            'cidr-prefix-invalid'  => 'بادئة CIDR غير صالحة لإصدار IP المحدد.',
        ],

        'configuration' => [
            'api' => [
                'title' => 'واجهة برمجة التطبيقات',
                'info'  => 'إعدادات واجهة برمجة تطبيقات Bagisto ووحدات المسؤول الخاصة بها.',
            ],
            'integration' => [
                'title' => 'التكامل',
                'info'  => 'إدارة إضافة تكامل واجهة برمجة التطبيقات المستخدمة لإصدار رموز واجهة برمجة تطبيقات المسؤول.',
            ],
            'settings' => [
                'title'  => 'إعدادات الوحدة',
                'info'   => 'تمكين أو تعطيل إضافة تكامل واجهة برمجة التطبيقات. عند التعطيل، يتم إخفاء قائمة الشريط الجانبي الخاصة بها وتعيد صفحاتها الخطأ 404.',
                'enable' => 'تمكين وحدة تكامل واجهة برمجة التطبيقات',
            ],
        ],

        'emails' => [
            'generated' => [
                'subject'  => 'تم إنشاء رمز جديد لواجهة برمجة التطبيقات: :name',
                'greeting' => 'تم للتو إنشاء رمز تكامل لواجهة برمجة التطبيقات باسم ":name" في حسابك.',
            ],
            'regenerated' => [
                'subject'  => 'تمت إعادة إنشاء رمز واجهة برمجة التطبيقات الخاص بك: :name',
                'greeting' => 'تمت للتو إعادة إنشاء رمز تكامل واجهة برمجة التطبيقات باسم ":name". توقف الرمز السابق عن العمل — الرمز الجديد فقط هو الصالح.',
            ],
            'revoked' => [
                'subject'  => 'تم إبطال رمز واجهة برمجة التطبيقات الخاص بك: :name',
                'greeting' => 'تم إبطال رمز تكامل واجهة برمجة التطبيقات باسم ":name". فقد أي عميل يستخدمه حق الوصول.',
            ],

            'details' => [
                'name' => 'اسم الرمز',
                'date' => 'التاريخ',
                'ip'   => 'من عنوان IP',
            ],

            'revoke-hint'   => 'إذا لم تكن تتوقع ذلك، فقم بإبطال الرمز فوراً باستخدام الزر أدناه.',
            'revoke-btn'    => 'إبطال هذا الرمز',
            'revoke-expiry' => 'رابط الإبطال هذا صالح لمدة 7 أيام. بعد ذلك، قم بتسجيل الدخول إلى لوحة الإدارة لإدارة الرمز.',
            'no-action'     => 'لا يلزم اتخاذ أي إجراء — هذه الرسالة الإلكترونية هي للتأكيد فقط.',
        ],

        'revoke-confirmation' => [
            'title'                    => 'إبطال رمز واجهة برمجة التطبيقات',
            'success-title'            => 'تم إبطال الرمز',
            'success-message'          => 'تم إبطال الرمز ":name". فقد أي عميل يستخدمه حق الوصول فوراً.',
            'already-inactive-title'   => 'الرمز غير نشط بالفعل',
            'already-inactive-message' => 'تم بالفعل إبطال الرمز ":name" أو إعادة إنشائه. لا يلزم اتخاذ أي إجراء إضافي.',
        ],

        'confirm' => [
            'generate' => [
                'title'   => 'إنشاء الرمز',
                'message' => 'إنشاء الرمز الآن؟ سيتم عرض النص العادي مرة واحدة فقط — انسخه قبل مغادرة الصفحة.',
            ],
            'regenerate' => [
                'title'   => 'إعادة إنشاء الرمز',
                'message' => 'إعادة إنشاء الرمز؟ سيتوقف الرمز القديم عن العمل فوراً وسيتم عرض النص العادي الجديد مرة واحدة فقط.',
            ],
            'revoke' => [
                'title'   => 'إبطال الرمز',
                'message' => 'إبطال هذا الرمز؟ سيفقد أي عميل يستخدمه حق الوصول فوراً. لا يمكن التراجع عن هذا الإجراء.',
            ],
        ],
    ],
];
