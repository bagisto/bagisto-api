<?php

return [

    'graphql' => [
        'headers' => [
            'unknown-channel' => 'القناة ":code" في ترويسة X-Channel غير موجودة.',
            'storefront-key-required' => 'ترويسة X-STOREFRONT-KEY مطلوبة',
            'storefront-key-invalid' => 'مفتاح واجهة المتجر غير صحيح',
            'storefront-key-rate-limited' => 'تم تجاوز حد المعدل. يرجى إعادة المحاولة بعد :seconds ثانية',
        ],

        'cart' => [
            'authentication-required' => 'مطلوب رمز المصادقة',
            'invalid-token' => 'رمز المصادقة غير صحيح أو منتهي الصلاحية',
            'unauthorized-access' => 'وصول غير مصرح به إلى السلة',
            'authenticated-only' => 'يمكن للمستخدمين المصرح لهم فقط جلب عرباتهم',
            'merge-requires-auth' => 'دمج السلة يتطلب المصادقة',
            'unknown-operation' => 'عملية سلة غير معروفة',

            'cart-not-found' => 'لم يتم العثور على السلة',
            'guest-cart-not-found' => 'لم يتم العثور على سلة الضيف',
            'product-not-found' => 'لم يتم العثور على المنتج',

            'product-id-quantity-required' => 'معرف المنتج والكمية مطلوبان',
            'cart-item-id-quantity-required' => 'معرف عنصر السلة والكمية مطلوبان',

            'file-token-invalid' => 'مرجع الملف المرفوع غير صحيح أو منتهي الصلاحية. ارفع الملف مرة أخرى.',
            'file-token-forbidden' => 'هذا الملف المرفوع لا ينتمي إلى سلتك.',
            'customizable-option-required' => 'الخيار القابل للتخصيص ":label" مطلوب.',
            'customizable-file-option-invalid' => 'الخيار المحدد ليس خيار ملف لهذا المنتج.',
            'customizable-file-invalid' => 'الملف مفقود، أو له امتداد غير مدعوم، أو أنه كبير جدًا.',
            'cart-item-id-required' => 'معرف عنصر السلة مطلوب',
            'item-ids-required' => 'مصفوفة معرفات العناصر مطلوبة',

            'event-booking-quantity-not-changeable' => 'لا يمكن تغيير كمية منتج حجز الفعاليات. تُحدَّد الكمية من خلال اختيار التذاكر.',
            'appointment-booking-quantity-not-changeable' => 'لا يمكن تغيير كمية منتج حجز المواعيد.',
            'coupon-code-required' => 'رمز القسيمة مطلوب',
            'address-data-required' => 'البلد والولاية والرمز البريدي مطلوبة',
            'grouped-qty-required' => 'يتطلب المنتج المجمّع كميات محددة. مرّر groupedQty كسلسلة JSON، مثلًا {"101":2,"102":1}.',
            'grouped-qty-must-include-all' => 'يتطلب المنتج المجمّع كميات لجميع المنتجات المرتبطة. المعرفات المفقودة: :ids.',
            'grouped-qty-invalid-associated' => 'تحتوي كميات المنتج المجمّع على معرفات منتجات مرتبطة غير صحيحة: :ids.',
            'grouped-qty-invalid-quantity' => 'تم تقديم كمية غير صحيحة لمعرف المنتج المرتبط :id. يجب أن تكون الكمية عددًا صحيحًا غير سالب.',
            'bundle-qty-not-changeable' => 'لا يمكن تغيير كمية خيار الحزمة ":option". الكمية المسموح بها هي :qty.',

            'add-product-failed' => 'فشل إضافة المنتج إلى السلة',
            'update-item-failed' => 'فشل تحديث عنصر السلة',
            'remove-item-failed' => 'فشل إزالة عنصر السلة',
            'apply-coupon-failed' => 'فشل تطبيق القسيمة',
            'remove-coupon-failed' => 'فشل إزالة القسيمة',
            'coupon-applied' => 'تم تطبيق القسيمة بنجاح',
            'coupon-removed' => 'تمت إزالة القسيمة بنجاح',
            'move-to-wishlist-failed' => 'فشل نقل العنصر إلى قائمة الرغبات',
            'estimate-shipping-failed' => 'فشل تقدير الشحن',

            'product-added-successfully' => 'تمت إضافة المنتج إلى السلة بنجاح',
            'cart-item-updated-successfully' => 'تم تحديث عنصر السلة بنجاح',
            'guest-cart-merged' => 'تم دمج سلة الضيف بنجاح',
            'using-authenticated-cart' => 'استخدام سلة العميل المصرح له',
            'cart-item-not-found' => 'لم يتم العثور على عنصر السلة',
            'new-guest-cart-created' => 'تم إنشاء سلة ضيف جديدة برمز جلسة فريد',
            'select-items-to-remove' => 'يرجى تحديد العناصر المراد إزالتها',
            'select-items-to-move-wishlist' => 'يرجى تحديد العناصر المراد نقلها إلى قائمة الرغبات',
            'invalid-or-expired-token' => 'رمز السلة غير صحيح أو منتهي الصلاحية. يرجى إنشاء سلة جديدة.',
            'invalid-token-of-login-user' => 'رمز المستخدم المسجل دخوله غير صحيح.',
            'booking-note-required' => 'booking.note مطلوب لحجز الطاولات.',
            'booking-date-required' => 'booking.date مطلوب عندما يكون booking.slot نطاقًا زمنيًا منسقًا.',
            'booking-qty-required' => 'booking.qty مطلوب لحجز الفعاليات.',
            'booking-ticket-qty-invalid' => 'يجب أن تكون كمية تذاكر الفعالية رقمًا غير سالب.',
            'booking-ticket-required' => 'حدد كمية تذكرة واحدة على الأقل لحجز الفعالية.',
            'booking-slot-unavailable' => 'الفترة الزمنية المحددة غير متاحة في التاريخ المحدد.',
            'invalid-quantity' => 'يجب أن تكون الكمية 1 على الأقل',
            'product-id-required' => 'معرف المنتج مطلوب',
            'shipping-address-required' => 'الدولة والرمز البريدي مطلوبان لتقدير الشحن',
        ],

        'token-verification' => [
            'invalid-operation' => 'عملية غير صحيحة',
            'invalid-input-data' => 'بيانات إدخال غير صحيحة',
            'token-required' => 'مطلوب الرمز',
            'invalid-token-format' => 'تنسيق رمز غير صحيح',
            'token-not-found-or-expired' => 'الرمز غير موجود أو انتهت صلاحيته',
            'customer-not-found' => 'لم يتم العثور على العميل',
            'customer-account-suspended' => 'حساب العميل معلق',
            'error-verifying-token' => 'خطأ في التحقق من الرمز',
            'token-is-valid' => 'الرمز صحيح',
            'unauthenticated' => 'غير مصادق. يرجى تسجيل الدخول لتنفيذ هذا الإجراء',
        ],

        'forgot-password' => [
            'invalid-operation' => 'عملية غير صحيحة',
            'invalid-input-data' => 'بيانات إدخال غير صحيحة',
            'email-required' => 'البريد الإلكتروني مطلوب',
            'reset-link-sent' => 'تم إرسال رابط إعادة التعيين بنجاح إلى بريدك الإلكتروني',
            'email-not-found' => 'عنوان البريد الإلكتروني غير موجود',
            'throttled' => 'محاولات إعادة تعيين كثيرة جدًا. يرجى الانتظار قبل المحاولة مرة أخرى.',
            'error-sending-reset-link' => 'حدث خطأ أثناء إرسال رابط إعادة التعيين',
        ],

        'reset-password' => [
            'failed' => 'تعذّرت إعادة تعيين كلمة المرور. يرجى المحاولة مرة أخرى.',
        ],

        'rate-limit' => [
            'too-many-attempts' => 'محاولات كثيرة جدًا. يرجى المحاولة مرة أخرى بعد :seconds ثانية.',
        ],

        'logout' => [
            'invalid-operation' => 'عملية غير صحيحة',
            'invalid-input-data' => 'بيانات إدخال غير صحيحة',
            'token-required' => 'مطلوب الرمز',
            'invalid-token-format' => 'تنسيق رمز غير صحيح',
            'logged-out-successfully' => 'تم تسجيل الخروج بنجاح',
            'token-not-found-or-expired' => 'الرمز غير موجود أو انتهت صلاحيته بالفعل',
            'error-during-logout' => 'خطأ أثناء تسجيل الخروج',
            'unauthenticated' => 'غير مصادق. يرجى تسجيل الدخول لتنفيذ هذا الإجراء',
        ],

        'address' => [
            'deleted-successfully' => 'تم حذف العنوان بنجاح',
            'authentication-required' => 'مطلوب رمز المصادقة',
            'invalid-token' => 'رمز غير صحيح أو منتهي الصلاحية',
            'unknown-operation' => 'عملية غير معروفة',
            'address-id-required' => 'معرف العنوان مطلوب',
            'address-not-found' => 'لم يتم العثور على العنوان أو لا ينتمي إلى هذا العميل',
            'retrieved' => 'تم استرجاع العناوين بنجاح',
            'fetch-failed' => 'فشل في جلب العناوين:',
            'field-required' => 'الحقل :field مطلوب',
        ],

        'customer-profile' => [
            'authentication-required' => 'مطلوب رمز المصادقة. يرجى توفير الرمز في إدخال الاستعلام',
            'invalid-token' => 'رمز غير صحيح أو منتهي الصلاحية',
            'profile-updated' => 'تم تحديث ملف العميل بنجاح',
        ],

        'customer' => [
            'password-mismatch' => 'كلمة المرور وتأكيد كلمة المرور غير متطابقة',
            'confirm-password-required' => 'تأكيد كلمة المرور مطلوب عند تغيير كلمة المرور',
            'current-password-required' => 'كلمة المرور الحالية مطلوبة لتغيير كلمة المرور',
            'current-password-incorrect' => 'كلمة المرور الحالية التي أدخلتها غير صحيحة',
            'verify-email-to-login' => 'تم إنشاء حسابك. يرجى تأكيد عنوان بريدك الإلكتروني لتسجيل الدخول.',
            'delete-password-required' => 'كلمة المرور مطلوبة لحذف حسابك',
            'delete-password-incorrect' => 'كلمة المرور التي أدخلتها غير صحيحة',
            'delete-has-active-orders' => 'لا يمكن حذف حسابك أثناء وجود طلبات معلقة أو قيد المعالجة',
            'unauthenticated' => 'غير مصرح. يرجى تسجيل الدخول لتنفيذ هذا الإجراء',
            'id-required' => 'معرف العميل مطلوب',
            'invalid-id-format' => 'تنسيق المعرف غير صحيح. المتوقع تنسيق IRI مثل "/api/admin/customers/1" أو معرف رقمي',
            'not-found' => 'لم يتم العثور على العميل',
            'phone-special-chars-not-allowed' => 'يمكن أن يحتوي رقم الجوال على أرقام فقط. الأحرف الخاصة غير مسموح بها',
            'email-already-taken' => 'عنوان البريد الإلكتروني هذا مسجل بالفعل لحساب آخر',
            'invalid-gender' => 'قيمة الجنس ":gender" غير صحيحة. القيم المسموح بها هي: :valid',
        ],

        'product-review' => [
            'id-required' => 'معرف مراجعة المنتج مطلوب',
            'invalid-id-format' => 'تنسيق المعرف غير صحيح. المتوقع تنسيق IRI مثل "/api/shop/reviews/1" أو معرف رقمي',
            'not-found' => 'لم يتم العثور على مراجعة المنتج ذات المعرف ":id"',
            'item-not-found' => 'العنصر ":id" غير موجود.',
            'product-id-required' => 'معرف المنتج مطلوب',
            'product-not-found' => 'لم يتم العثور على المنتج',
            'rating-invalid' => 'يجب أن تكون التقييمات بين 1 و 5',
            'title-required' => 'عنوان المراجعة مطلوب',
            'comment-required' => 'تعليق المراجعة مطلوب',
            'review-disabled' => 'مراجعات المنتجات معطلة حاليًا',
            'guest-review-disabled' => 'مراجعات الضيوف غير مسموح بها. يرجى تسجيل الدخول لإرسال مراجعة',
            'owner-required' => 'يرجى تسجيل الدخول لإدارة مراجعتك',
            'not-owned' => 'لم تكتب هذه المراجعة ولا يمكن تعديلها أو حذفها',
            'invalid-attachments' => 'تنسيق المرفقات غير صحيح.',
            'invalid-product-id-format' => 'تنسيق معرف المنتج غير صحيح. المتوقع تنسيق IRI مثل "/api/shop/products/1" أو معرف رقمي',
        ],

        'customer-review' => [
            'id-required' => 'معرف مراجعة العميل مطلوب',
            'invalid-id-format' => 'تنسيق المعرف غير صحيح. المتوقع تنسيق IRI مثل "/api/shop/customer-reviews/1" أو معرف رقمي',
            'not-found' => 'لم يتم العثور على مراجعة العميل ذات المعرف ":id"',
        ],

        'newsletter' => [
            'error-during-subscription' => 'تعذر إكمال الاشتراك في الوقت الحالي. يرجى المحاولة مرة أخرى لاحقًا',
        ],

        'contact-us' => [
            'success' => 'تم إرسال استفسارك بنجاح. سنتواصل معك قريبًا',
            'failed' => 'تعذر إرسال استفسارك في الوقت الحالي. يرجى المحاولة مرة أخرى لاحقًا',
            'invalid-input' => 'بيانات نموذج الاتصال غير صحيحة',
        ],

        'customer-order' => [
            'id-required' => 'معرف طلب العميل مطلوب',
            'invalid-id-format' => 'تنسيق المعرف غير صحيح. المتوقع تنسيق IRI مثل "/api/shop/customer-orders/1" أو معرف رقمي',
            'not-found' => 'لم يتم العثور على طلب العميل ذي المعرف ":id"',
        ],

        'return' => [
            'not-found' => 'لم يتم العثور على طلب الإرجاع.',
            'disabled' => 'الإرجاع غير مفعّل لهذا المتجر.',
            'invalid-input' => 'تفاصيل الإرجاع مفقودة أو غير صحيحة.',
            'invalid-resolution-type' => 'يجب أن يكون نوع الحل "return" أو "cancel_items".',
            'agreement-required' => 'يجب عليك قبول سياسة الإرجاع.',
            'invalid-order' => 'الطلب المحدد غير صالح.',
            'invalid-item' => 'العنصر المحدد غير مؤهل للإرجاع.',
            'qty-exceeds' => 'الكمية المطلوبة تتجاوز الكمية القابلة للإرجاع.',
            'invalid-package-condition' => 'يجب أن تكون حالة الطرد "open" أو "packed".',
            'invalid-image' => 'واحد أو أكثر من الملفات المرفوعة له نوع ملف غير مدعوم.',
            'invalid-custom-field' => 'حقل مخصص غير معروف ":id".',
            'custom-field-required' => 'الحقل ":field" مطلوب.',
            'invalid-custom-field-value' => 'قيمة ":field" ليست من الخيارات المسموح بها.',
            'created' => 'تم إنشاء طلب الإرجاع بنجاح.',
            'canceled' => 'تم إلغاء طلب الإرجاع بنجاح.',
            'already-canceled' => 'طلب الإرجاع ملغى بالفعل.',
            'cancel-not-allowed' => 'لا يمكن إلغاء طلب الإرجاع هذا.',
            'close-not-allowed' => 'لا يمكن إغلاق طلب الإرجاع هذا.',
            'reopened' => 'تمت إعادة فتح طلب الإرجاع بنجاح.',
            'reopen-not-allowed' => 'لا يمكن إعادة فتح طلب الإرجاع هذا.',
            'closed' => 'تم إغلاق طلب الإرجاع بنجاح.',
            'message-sent' => 'تم إرسال الرسالة بنجاح.',
            'attachment-not-found' => 'لم يتم العثور على مرفق الرسالة.',
        ],

        'eu-withdrawal' => [
            'not-found' => 'لم يتم العثور على إقرار الانسحاب.',
            'order-not-found' => 'تعذر العثور على الطلب أو أنه غير مؤهل للانسحاب.',
            'too-many-attempts' => 'محاولات كثيرة جدًا. يرجى المحاولة مرة أخرى بعد :seconds ثانية.',
            'created' => 'تم تقديم إقرار الانسحاب بنجاح.',
        ],

        'customer-order-shipment' => [
            'id-required' => 'معرف الشحنة مطلوب',
            'not-found' => 'لم يتم العثور على الشحنة ذات المعرف ":id" أو أنها لا تنتمي إلى هذا العميل',
            'item-not-found' => 'لم يتم العثور على عنصر الشحنة ذي المعرف ":id" أو أنه لا ينتمي إلى هذا العميل',
            'invalid-id-format' => 'تنسيق المعرف غير صحيح. المتوقع تنسيق IRI مثل "/api/shop/customer-order-shipments/1" أو معرف رقمي',
        ],

        'cancel-order' => [
            'order-id-required' => 'معرف الطلب مطلوب لإلغاء الطلب',
            'not-found' => 'لم يتم العثور على الطلب ذي المعرف ":id" أو أنه لا ينتمي إلى هذا العميل',
            'cancel-success' => 'تم إلغاء الطلب بنجاح',
            'cancel-failed' => 'لا يمكن إلغاء الطلب. ربما تمت معالجته أو شحنه أو إلغاؤه بالفعل',
        ],

        'reorder' => [
            'order-id-required' => 'معرف الطلب مطلوب لإعادة الطلب',
            'not-found' => 'لم يتم العثور على الطلب ذي المعرف ":id" أو أنه لا ينتمي إلى هذا العميل',
            'reorder-success' => 'تمت إضافة :count عنصر (عناصر) من طلبك السابق إلى سلتك',
            'no-items-added' => 'تعذرت إضافة أي عناصر إلى السلة. قد تكون المنتجات غير متوفرة في المخزون أو غير متاحة',
        ],

        'customer-downloadable-product' => [
            'id-required' => 'معرف المنتج القابل للتنزيل الخاص بالعميل مطلوب',
            'invalid-id-format' => 'تنسيق المعرف غير صحيح. المتوقع تنسيق IRI مثل "/api/shop/customer-downloadable-products/1" أو معرف رقمي',
            'not-found' => 'لم يتم العثور على المنتج القابل للتنزيل الخاص بالعميل ذي المعرف ":id"',

            'download' => [
                'unauthenticated' => 'غير مصرح: مصادقة العميل مطلوبة.',
                'not-found' => 'لم يتم العثور على المنتج القابل للتنزيل.',
                'pending' => 'التنزيل معلق. يرجى الانتظار حتى تتم فوترة الطلب.',
                'limit-exceeded' => 'تم تجاوز حد التنزيل.',
                'no-downloads-remaining' => 'لا توجد تنزيلات متاحة أخرى.',
                'file-not-found' => 'الملف غير موجود.',
                'link-not-found' => 'لم يتم العثور على رابط التنزيل.',
            ],
        ],

        'customer-invoice' => [
            'id-required' => 'معرف فاتورة العميل مطلوب',
            'invalid-id-format' => 'تنسيق المعرف غير صحيح. المتوقع تنسيق IRI مثل "/api/shop/customer-invoices/1" أو معرف رقمي',
            'not-found' => 'لم يتم العثور على فاتورة العميل ذات المعرف ":id"',
        ],

        'auth' => [
            'no-token-provided' => 'لم يتم توفير رمز المصادقة. يرجى توفير الرمز في رأس Authorization كـ "Bearer <token>" أو في حقل input.token',
            'invalid-or-expired-token' => 'رمز غير صحيح أو منتهي الصلاحية',
            'request-not-found' => 'الطلب غير موجود في السياق',
            'token-required' => 'مطلوب رمز المصادقة. يرجى توفير الرمز إما في حقل إدخال الطفرة أو في رأس Authorization كـ "Bearer <token>"',
            'unknown-resource' => 'مورد غير معروف',
            'cannot-update-other-profile' => 'غير مصرح: لا يمكن تحديث ملف شخصي آخر',
        ],

        'upload' => [
            'invalid-base64' => 'بيانات صورة base64 مشفرة غير صحيحة',
            'size-exceeds-limit' => 'يجب ألا يتجاوز حجم الصورة 5 ميجابايت',
            'invalid-format' => 'تنسيق صورة غير صحيح. يرجى توفير صورة مشفرة بـ base64 مع نظام data URI (data:image/jpeg;base64,...)',
            'failed' => 'فشل تحميل الصورة',
        ],

        'attribute' => [
            'code-already-exists' => 'رمز السمة موجود بالفعل',
            'option-id-required' => 'المعامل "attributeId" مطلوب عند الاستعلام عن خيارات السمة مباشرةً',
            'id-required' => 'معرف السمة مطلوب',
            'invalid-id-format' => 'تنسيق المعرف غير صحيح. المتوقع تنسيق IRI مثل "/api/shop/attributes/1" أو معرف رقمي',
            'not-found' => 'لم يتم العثور على السمة',
        ],

        'attribute-option' => [
            'id-required' => 'معرف خيار السمة مطلوب',
            'invalid-id-format' => 'تنسيق المعرف غير صحيح. المتوقع تنسيق IRI مثل "/api/shop/attribute-options/1" أو معرف رقمي',
            'not-found' => 'لم يتم العثور على خيار السمة',
        ],

        'channel' => [
            'id-required' => 'معرف القناة مطلوب',
            'invalid-id-format' => 'تنسيق المعرف غير صحيح. المتوقع تنسيق IRI مثل "/api/shop/channels/1" أو معرف رقمي',
            'not-found' => 'لم يتم العثور على القناة',
        ],

        'country-state' => [
            'id-required' => 'معرف ولاية الدولة مطلوب',
            'invalid-id-format' => 'تنسيق المعرف غير صحيح. المتوقع تنسيق IRI مثل "/api/shop/country-states/1" أو معرف رقمي',
            'not-found' => 'لم يتم العثور على ولاية الدولة',
            'country-id-required' => 'معرف الدولة مطلوب',
        ],

        'country' => [
            'id-required' => 'معرف الدولة مطلوب',
            'invalid-id-format' => 'تنسيق المعرف غير صحيح. المتوقع تنسيق IRI مثل "/api/shop/countries/1" أو معرف رقمي',
            'not-found' => 'لم يتم العثور على الدولة',
        ],

        'login' => [
            'invalid-credentials' => 'بريد إلكتروني أو كلمة مرور غير صحيحة',
            'account-suspended' => 'تم تعليق حسابك',
            'account-inactive' => 'يتطلب تنشيط حسابك موافقة المسؤول',
            'email-not-verified' => 'الرجاء التحقق من حساب البريد الإلكتروني الخاص بك أولاً.',
            'successful' => 'لقد قمت بتسجيل الدخول بنجاح',
            'invalid-request' => 'طلب تسجيل دخول غير صحيح',
        ],

        'social-login' => [
            'signed-in' => 'تم تسجيل الدخول بنجاح.',
            'token-required' => 'مطلوب رمز تسجيل الدخول عبر مواقع التواصل.',
            'invalid-token' => 'رمز تسجيل الدخول غير صالح أو منتهي الصلاحية. يرجى المحاولة مرة أخرى.',
            'google-not-configured' => 'تسجيل الدخول عبر Google غير مهيأ لهذا المتجر.',
            'wrong-audience' => 'تم إصدار هذا الرمز لتطبيق آخر.',
            'email-required' => 'لم يشارك المزود عنوان بريد إلكتروني. يرجى التسجيل بالبريد الإلكتروني بدلاً من ذلك.',
            'account-inactive' => 'يتطلب تنشيط حسابك موافقة المسؤول',
            'provider-not-supported' => 'مزود تسجيل الدخول الاجتماعي هذا غير مدعوم.',
            'provider-disabled' => 'مزود تسجيل الدخول الاجتماعي هذا غير مُفعّل.',
        ],

        'checkout' => [
            'invalid-input' => 'بيانات إدخال غير صحيحة لعملية الدفع',
            'billing-address-required' => 'عنوان الفاتورة مطلوب',
            'shipping-address-required' => 'عنوان الشحن مطلوب للشحنات',
            'address-save-failed' => 'فشل حفظ العنوان',
            'address-saved' => 'تم حفظ العنوان بنجاح',
            'shipping-method-required' => 'طريقة الشحن مطلوبة',
            'invalid-shipping-method' => 'طريقة شحن غير صحيحة أو غير متاحة',
            'shipping-method-save-failed' => 'فشل حفظ طريقة الشحن',
            'shipping-method-saved' => 'تم حفظ طريقة الشحن بنجاح',
            'shipping-method-error' => 'خطأ في حفظ طريقة الشحن',
            'payment-method-required' => 'طريقة الدفع مطلوبة',
            'invalid-payment-method' => 'طريقة دفع غير صحيحة أو غير متاحة',
            'payment-method-save-failed' => 'فشل حفظ طريقة الدفع',
            'payment-method-saved' => 'تم حفظ طريقة الدفع بنجاح',
            'payment-method-error' => 'خطأ في حفظ طريقة الدفع',
            'order-placed' => 'تم تقديم الطلب بنجاح',
            'order-redirect-required' => 'تتطلب طريقة الدفع هذه أن يكمل المتسوق الدفع على بوابة الدفع. أرسله إلى redirectUrl؛ يتم إنشاء الطلب بمجرد أن تؤكد البوابة الدفع.',
            'order-creation-failed' => 'فشل إنشاء الطلب: معرف الطلب فارغ أو لم يتم الاحتفاظ بالطلب',
            'order-retrieval-failed' => 'فشل في استرجاع الطلب المنشأ',
            'order-creation-error' => 'فشل في إنشاء الطلب',
            'cart-empty' => 'السلة فارغة',
            'account-suspended' => 'تم تعليق حسابك. يرجى التواصل مع الدعم.',
            'account-inactive' => 'حسابك غير نشط. يرجى التواصل مع الدعم.',
            'minimum-order-not-met' => 'الحد الأدنى لمبلغ الطلب هو :amount',
            'guest-checkout-not-allowed' => 'يرجى تسجيل الدخول لإتمام الشراء. الدفع كضيف غير متاح لهذه السلة.',
            'email-required' => 'عنوان البريد الإلكتروني مطلوب لإنشاء الطلب',
            'unknown-operation' => 'عملية دفع غير معروفة',
        ],

        'customer-addresses' => [
            'token-required' => 'الرمز مطلوب لجلب عناوين العميل',
            'invalid-or-expired-token' => 'رمز غير صحيح أو منتهي الصلاحية',
            'token-validation-failed' => 'فشل التحقق من الرمز',
        ],

        'product' => [
            'not-found' => 'لم يتم العثور على المنتج',
            'not-found-with-sku' => 'لم يتم العثور على منتج بهذا SKU',
            'not-found-with-url-key' => 'لم يتم العثور على منتج بمفتاح URL هذا',
            'parameters-required' => 'يجب تقديم واحد على الأقل من المعاملات التالية: "sku" أو "id" أو "urlKey"',
            'type' => 'نوع المنتج',
            'attribute-family' => 'عائلة السمات',
            'sku' => 'رمز SKU',
            'name' => 'الاسم',
            'description' => 'الوصف',
            'short-description' => 'وصف مختصر',
            'status' => 'الحالة',
            'new' => 'جديد',
            'featured' => 'مميز',
            'price' => 'السعر',
            'special-price' => 'سعر خاص',
            'weight' => 'الوزن',
            'cost' => 'التكلفة',
            'length' => 'الطول',
            'width' => 'العرض',
            'height' => 'الارتفاع',
            'color' => 'اللون',
            'size' => 'الحجم',
            'brand' => 'العلامة التجارية',
            'super-attributes' => 'السمات العليا',
            'id-required' => 'معرف المنتج مطلوب',
            'invalid-id-format' => 'تنسيق المعرف غير صحيح. المتوقع تنسيق IRI مثل "/api/shop/products/1" أو معرف رقمي',
        ],

        'downloadable-product' => [
            'download-link-not-found' => 'رابط التنزيل غير موجود أو منتهي الصلاحية',
            'purchased-link-not-found' => 'لم يتم العثور على الرابط المشترى',
            'file-not-found' => 'الملف غير موجود',
            'download-successful' => 'الملف جاهز للتنزيل',
            'token-required' => 'مطلوب رمز التنزيل',
            'invalid-token' => 'رمز التنزيل غير صحيح أو منتهي الصلاحية',
            'token-expired' => 'انتهت صلاحية رمز التنزيل. يرجى إنشاء رمز جديد',
            'access-denied' => 'تم رفض الوصول: ليس لديك إذن لتنزيل هذا الملف',
            'redirect-external-url' => 'إعادة التوجيه إلى عنوان URL التنزيل الخارجي',
            'file-error' => 'حدث خطأ أثناء معالجة طلب التنزيل الخاص بك',
            'unauthorized-access' => 'الوصول غير المصرح به إلى مورد التنزيل',
            'unauthenticated' => 'غير مصرح: مصادقة العميل مطلوبة',
            'link-id-required' => 'downloadableLinkPurchasedId مطلوب',
            'link-not-found' => 'لم يتم العثور على رابط التنزيل',
            'not-owner' => 'غير مصرح: رابط التنزيل هذا لا ينتمي إليك',
            'pending' => 'التنزيل معلق. يرجى الانتظار حتى تتم فوترة الطلب.',
            'limit-exceeded' => 'تم تجاوز حد التنزيل',
            'no-downloads-left' => 'لا توجد تنزيلات متاحة أخرى',
        ],

        'booking-slot' => [
            'id-required' => 'معرف منتج الحجز مطلوب.',
            'date-required' => 'التاريخ مطلوب.',
            'product-not-found' => 'لم يتم العثور على منتج الحجز.',
            'invalid-type' => 'نوع الحجز هذا غير مدعوم.',
            'invalid-time-range' => 'نطاق زمني غير صحيح.',
            'invalid-time-format' => 'تنسيق وقت غير صحيح.',
        ],

        'resolver' => [
            'no-resolve-info' => 'ResolveInfo الخاص بـ GraphQL غير متاح في السياق. لا يمكن تحديد فئة النموذج.',
            'unknown-resource-class' => 'تعذر تحديد فئة المورد من نوع الإرجاع في GraphQL',
        ],

        'docs' => [
            'openapi-unavailable' => 'تعذر إنشاء OpenAPI Factory',
        ],

        'locale' => [
            'id-required' => 'معرف اللغة مطلوب',
            'invalid-id-format' => 'تنسيق المعرف غير صحيح. المتوقع تنسيق IRI مثل "/api/shop/locales/1" أو معرف رقمي',
            'not-found' => 'لم يتم العثور على اللغة',
        ],

        'category' => [
            'id-required' => 'معرف الفئة مطلوب',
            'invalid-id-format' => 'تنسيق المعرف غير صحيح. المتوقع تنسيق IRI مثل "/api/shop/categories/1" أو معرف رقمي',
            'not-found' => 'لم يتم العثور على الفئة',
        ],

        'compare-item' => [
            'id-required' => 'معرف عنصر المقارنة مطلوب',
            'invalid-id-format' => 'صيغة معرف غير صحيحة. صيغة IRI المتوقعة مثل "/api/shop/compare-items/1" أو معرف رقمي',
            'not-found' => 'عنصر المقارنة غير موجود',
            'product-id-required' => 'معرف المنتج مطلوب',
            'customer-id-required' => 'معرف العميل مطلوب',
            'product-not-found' => 'المنتج غير موجود',
            'customer-not-found' => 'العميل غير موجود',
            'already-exists' => 'هذا المنتج موجود بالفعل في قائمة المقارنة الخاصة بك',
            'delete-all-success' => 'تمت إزالة جميع عناصر المقارنة بنجاح',
        ],

        'wishlist' => [
            'id-required' => 'معرف عنصر قائمة الرغبات مطلوب',
            'invalid-id-format' => 'تنسيق المعرف غير صحيح. المتوقع تنسيق IRI مثل "/api/shop/wishlists/1" أو معرف رقمي',
            'not-found' => 'لم يتم العثور على عنصر قائمة الرغبات',
            'product-id-required' => 'معرف المنتج مطلوب',
            'customer-id-required' => 'معرف العميل مطلوب',
            'product-not-found' => 'لم يتم العثور على المنتج',
            'product-disabled' => 'هذا المنتج معطل حاليًا',
            'customer-not-found' => 'لم يتم العثور على العميل',
            'already-exists' => 'هذا المنتج موجود بالفعل في قائمة رغباتك',
            'added' => 'تمت إضافة العنصر إلى قائمة الرغبات بنجاح',
            'removed' => 'تمت إزالة العنصر من قائمة الرغبات بنجاح',
            'product-removed' => 'تمت إزالة المنتج',
            'wishlist-item-id-required' => 'معرف عنصر قائمة الرغبات مطلوب',
            'invalid-quantity' => 'يجب أن تكون الكمية أكبر من 0',
            'move-to-cart-missing-options' => 'المنتج يفتقد خيارات مطلوبة. يرجى تهيئته يدويًا',
            'moved-to-cart-success' => 'تم نقل العنصر إلى السلة بنجاح',
            'delete-all-success' => 'تمت إزالة جميع عناصر قائمة الرغبات بنجاح',
        ],

        'gdpr' => [
            'disabled' => 'طلبات بيانات GDPR معطلة. يرجى تفعيل GDPR من إعدادات المسؤول.',
            'unauthenticated' => 'يجب عليك تسجيل الدخول لإدارة طلبات بيانات GDPR.',
            'not-found' => 'لم يتم العثور على طلب بيانات GDPR',
            'type-required' => 'نوع الطلب مطلوب',
            'type-invalid' => 'يجب أن يكون نوع الطلب إما "delete" أو "update"',
            'message-required' => 'الرسالة مطلوبة',
            'raised' => 'تم تقديم طلب بيانات GDPR الخاص بك بنجاح.',
            'revoked' => 'تم سحب طلب بيانات GDPR الخاص بك بنجاح.',
            'revoke-not-allowed' => 'لم يعد من الممكن سحب طلب بيانات GDPR هذا.',
            'deleted' => 'تم حذف طلب بيانات GDPR الخاص بك بنجاح.',
        ],

        'section' => [
            'id-required' => 'معرف القسم مطلوب',
            'invalid-id-format' => 'تنسيق المعرف غير صحيح. المتوقع تنسيق IRI مثل "/api/shop/sections/1" أو معرف رقمي',
            'not-found' => 'لم يتم العثور على القسم',
        ],

        'install' => [
            'starting' => 'جارٍ بدء تثبيت API Platform...',
            'completed-success' => '✓ اكتمل تثبيت API Platform بنجاح!',
            'failed' => '✗ فشل التثبيت: ',

            'provider-already-registered' => 'ℹ موفر الخدمة (Service Provider) مسجل بالفعل',
            'provider-not-registered' => '⚠ تعذر تسجيل مزود الخدمة تلقائيًا. أضف :provider إلى bootstrap/providers.php يدويًا.',
            'provider-registered' => '✓ تم تسجيل مزود الخدمة في bootstrap/providers.php',
            'provider-file-not-found' => 'الملف غير موجود: :file',
            'provider-permission-denied' => 'تم رفض الإذن: لا يمكن الكتابة إلى :file. يرجى التحقق من أذونات الملف.',

            'config-already-exists' => 'ℹ ملف الإعدادات موجود بالفعل',
            'config-published' => '✓ تم نشر إعدادات API Platform إلى config/',
            'config-source-not-found' => 'ملف الإعدادات المصدر غير موجود: :file',
            'config-permission-denied' => 'تم رفض الإذن: لا يمكن الكتابة إلى :directory. يرجى التحقق من أذونات المجلد.',

            'translatable-not-found' => 'ℹ لم يتم العثور على TranslatableModel - تم التخطي',
            'translatable-already-abstract' => 'ℹ TranslatableModel مجرد (abstract) بالفعل',
            'translatable-made-abstract' => '✓ تم جعل TranslatableModel مجردًا (abstract)',
            'translatable-permission-denied' => 'تم رفض الإذن: لا يمكن الكتابة إلى :file. يرجى التحقق من أذونات الملف.',

            'providers-already-registered' => 'ℹ مزودو API Platform مسجلون بالفعل',
            'providers-registered' => '✓ تم تسجيل مزودي API Platform في bootstrap/app.php',
            'providers-file-not-found' => 'الملف غير موجود: :file',
            'providers-permission-denied' => 'تم رفض الإذن: لا يمكن الكتابة إلى :file. يرجى التحقق من أذونات الملف.',
            'providers-not-found' => 'تعذر العثور على إنشاء Application في bootstrap/app.php',

            'assets-published' => '✓ تم نشر أصول الحزمة',
            'running-migrations' => 'جارٍ تشغيل ترحيلات قاعدة البيانات...',
            'migrations-completed' => '✓ اكتملت ترحيلات قاعدة البيانات',
            'clearing-caches' => 'جارٍ مسح ذاكرة التخزين المؤقت وتحسينها...',
            'caches-optimized' => '✓ تم مسح ذاكرة التخزين المؤقت وتحسينها',
            'generating-api-key' => 'جارٍ إنشاء مفتاح API...',
            'api-key-generated' => '✓ تم إنشاء مفتاح API بنجاح',
            'api-key-already-exists' => 'ℹ مفتاح API الافتراضي للمتجر موجود بالفعل - تم تخطي الإنشاء',

            'migrations-error' => 'فشلت ترحيلات قاعدة البيانات.',
            'migrations-error-running' => 'خطأ في تشغيل ترحيلات قاعدة البيانات: :error',
            'cache-clear-error' => 'فشل مسح ذاكرة التخزين المؤقت.',
            'cache-optimize-error' => 'فشل التحسين.',
            'cache-error' => 'خطأ أثناء مسح ذاكرة التخزين المؤقت وتحسينها: :error',
            'api-key-generation-error' => 'فشل إنشاء مفتاح API.',
            'api-key-error' => 'خطأ في إنشاء مفتاح API: :error',

            'key-management-required' => '--key مطلوب للإجراء :action',
            'key-rotation-error' => 'خطأ في تدوير المفتاح: :error',
            'key-deactivation-error' => 'خطأ في إلغاء تنشيط المفتاح: :error',
            'key-not-found' => 'لم يتم العثور على مفتاح API: :identifier',

            'maintenance-starting' => '🔄 جارٍ بدء صيانة مفاتيح API...',
            'maintenance-complete' => '✅ اكتملت صيانة مفاتيح API',
            'cleanup-expired-keys' => '🧹 جارٍ تنظيف المفاتيح المنتهية الصلاحية...',
            'cleanup-expired-none' => '   ℹ️ لا توجد مفاتيح منتهية الصلاحية لتنظيفها',
            'invalidate-deprecated' => '⚠️ جارٍ إبطال المفاتيح المهملة...',
            'invalidate-deprecated-none' => '   ℹ️ لا توجد مفاتيح مهملة لإبطالها',
            'notify-expiring' => '📧 جارٍ إرسال إشعارات انتهاء الصلاحية...',
            'notify-expiring-none' => '   ℹ️ لا توجد مفاتيح تتطلب إشعارات',

            'key-rotated-success' => '✅ تم تدوير المفتاح بنجاح!',
            'key-deactivated-success' => '✅ تم إلغاء تنشيط المفتاح بنجاح!',
            'deactivation-cancelled' => 'تم إلغاء إلغاء التنشيط.',
            'cleanup-cancelled' => 'تم إلغاء التنظيف.',

            'key-status-title' => 'حالة المفتاح: :name',
            'key-status-active' => 'نشط: :status',
            'key-status-usable' => 'قابل للاستخدام: :status',
            'key-status-expired' => 'منتهي الصلاحية: :status',
            'key-status-deprecated' => 'مهمل: :status',
            'key-status-expires-at' => 'تنتهي صلاحيته في: :expires',
            'key-status-days-until-expiry' => 'الأيام المتبقية حتى انتهاء الصلاحية: :days',
            'key-status-last-used' => 'آخر استخدام: :date',
            'key-status-rotated-from' => 'مُدوَّر من: :key',
            'key-status-rotated-keys' => 'المفاتيح المُدوَّرة من هذا المفتاح: :count',

            'policy-compliance-summary' => 'ملخص الامتثال لسياسة تدوير مفاتيح API',
            'available-actions' => 'الإجراءات المتاحة: rotate, deactivate, cleanup, status, expiring, unused, summary',

            'asset-linked-success' => '✓ تم ربط أصول API Platform بنجاح',
            'symlink-create-failed' => 'تعذر إنشاء الرابط الرمزي، جارٍ نسخ الأصول بدلًا من ذلك...',
            'asset-copy-warning' => 'تحذير: تعذر ربط أو نسخ أصول API Platform. قد يلزم الإعداد اليدوي.',
            'asset-copied-success' => '✓ تم نسخ أصول API Platform بنجاح',

            'key-name-required' => 'لا يمكن أن يكون اسم مفتاح واجهة المتجر فارغًا.',
            'key-generated-success' => 'تم إنشاء مفتاح واجهة المتجر بنجاح!',
            'key-details' => 'تفاصيل المفتاح:',
            'key-secure-warning' => 'حافظ على أمان هذا المفتاح! سيتم استخدامه في ترويسة X-STOREFRONT-KEY.',
            'key-share-warning' => 'لا تشارك هذا المفتاح علنًا ولا تضفه إلى نظام التحكم في الإصدارات.',

            'key-already-exists' => 'يوجد بالفعل مفتاح واجهة متجر بالاسم \':name\'.',
            'cleanup-success' => '✅ تم تنظيف :count من المفاتيح المنتهية الصلاحية',
            'confirm-cleanup' => 'سيؤدي هذا إلى الحذف المؤقت لجميع المفاتيح المنتهية الصلاحية. هل تريد المتابعة؟',
            'confirm-deactivate' => 'هل أنت متأكد من أنك تريد إلغاء تنشيط المفتاح: :name؟',

            'old-key' => 'المفتاح القديم: :name',
            'old-key-id' => 'معرف المفتاح القديم: :id',
            'deprecation-date' => 'تاريخ الإهمال: :date',
            'new-key' => 'المفتاح الجديد: :name',
            'new-key-id' => 'معرف المفتاح الجديد: :id',
            'new-key-value' => 'قيمة المفتاح الجديد: :key',
            'expires-at' => 'تنتهي صلاحيته في: :date',
            'invalid-action' => 'إجراء غير صحيح: :action',
            'key-active' => 'نشط: :status',
            'key-usable' => 'قابل للاستخدام: :status',
            'key-expired' => 'منتهي الصلاحية: :status',
            'key-deprecated' => 'مهمل: :status',
            'key-field-id' => 'المعرف',
            'key-field-name' => 'الاسم',
            'key-field-key' => 'المفتاح',
            'key-field-rate-limit' => 'حد المعدل',
            'key-field-status' => 'الحالة',
            'key-days' => ' أيام',
            'key-never' => 'أبدًا',
            'key-requests-minute' => ' طلب/دقيقة',
            'key-unlimited' => 'غير محدود',
            'rate-limit-exceeded' => 'حد المعدل المطلوب يتجاوز الحد الأقصى المسموح به :max. سيتم ضبطه على :max طلب/دقيقة.',

            'vendor-path-not-found' => 'لم يتم العثور على مسار API Platform في vendor في: :path',
            'assets-already-linked' => 'أصول API Platform مرتبطة بالفعل في: :path',
            'no-keys-expiring' => 'لا توجد مفاتيح تنتهي صلاحيتها خلال الأيام الـ :days القادمة',
            'keys-expiring-title' => 'المفاتيح التي تنتهي صلاحيتها خلال الأيام الـ :days القادمة:',
            'key-display-format' => '• :name (المعرف: :id)',
            'key-expires-display' => '  تنتهي في: :date (متبقي :days يوم)',
            'no-unused-keys' => 'لم يتم العثور على مفاتيح غير مستخدمة (> :days يوم)',
            'unused-keys-title' => 'المفاتيح غير المستخدمة (> :days يوم):',
            'key-last-used-display' => '  آخر استخدام: :date',
            'total-keys' => 'إجمالي المفاتيح: :count',
            'valid-keys' => 'المفاتيح الصالحة: :count',
            'expired-keys' => 'المفاتيح المنتهية الصلاحية: :count',
            'deprecated-keys' => 'المفاتيح المهملة: :count',
            'keys-expiring-soon' => 'المفاتيح التي تنتهي صلاحيتها قريبًا (7 أيام): :count',
            'unused-keys-summary' => 'المفاتيح غير المستخدمة (90 يومًا): :count',
            'recently-rotated' => 'المُدوَّرة مؤخرًا (30 يومًا): :count',
            'cleanup-success-message' => '   ✅ تم تنظيف :count من المفاتيح المنتهية الصلاحية',
            'invalidate-success-message' => '   ✅ تم إبطال :count من المفاتيح المهملة',
            'notify-success-message' => '   ✅ تم إرسال :count من إشعارات انتهاء الصلاحية',
            'notify-failed-message' => '   ⚠️ فشل الإشعار بشأن :key: :error',
            'publish-assets-warning' => 'تحذير: تعذر نشر أصول الحزمة. :error',
            'env-config-saved' => '✓ تم حفظ إعدادات بيئة API Platform بنجاح',
            'api-endpoints' => 'نقاط نهاية API:',
            'api-documentation' => 'توثيق API: :url',
            'api-landing-page' => 'الصفحة الرئيسية لـ API: :url',
            'graphql-playground' => 'GraphQL Playground: :url',
            'rest-api-storefront' => 'Swagger لواجهة REST API للمتجر: :url',
            'rest-api-admin' => 'Swagger لواجهة REST API للمسؤول: :url',
            'key-rotation-invalid-key' => 'لا يمكن تدوير مفتاح غير صالح أو منتهي الصلاحية',
            'env-file-not-found' => 'لم يتم العثور على ملف .env',
            'env-permission-denied' => 'تم رفض الإذن: لا يمكن الكتابة في ملف .env. يرجى التحقق من أذونات الملف.',
        ],

        'attribute-translation' => [
            'id-required' => 'معرف ترجمة السمة مطلوب',
            'invalid-id-format' => 'تنسيق المعرف غير صحيح. المتوقع تنسيق IRI مثل "/api/shop/attribute-translations/1" أو معرف رقمي',
            'not-found' => 'لم يتم العثور على ترجمة السمة',
        ],

        'currency' => [
            'id-required' => 'معرف العملة مطلوب',
            'invalid-id-format' => 'تنسيق المعرف غير صحيح. المتوقع تنسيق IRI مثل "/api/shop/currencies/1" أو معرف رقمي',
            'not-found' => 'لم يتم العثور على العملة',
        ],

        'customer-address' => [
            'id-required' => 'معرف عنوان العميل مطلوب',
            'invalid-id-format' => 'تنسيق المعرف غير صحيح. المتوقع تنسيق IRI مثل "/api/shop/customer-addresses/1" أو معرف رقمي',
        ],

        'customer-order-shipment-item' => [
            'id-required' => 'معرف عنصر الشحنة مطلوب',
            'invalid-id-format' => 'تنسيق المعرف غير صحيح. المتوقع تنسيق IRI مثل "/api/shop/customer-order-shipment-items/1" أو معرف رقمي',
            'not-found' => 'لم يتم العثور على عنصر الشحنة',
        ],

        'page' => [
            'id-required' => 'معرف الصفحة مطلوب',
            'invalid-id-format' => 'تنسيق المعرف غير صحيح. المتوقع تنسيق IRI مثل "/api/shop/pages/1" أو معرف رقمي',
            'not-found' => 'لم يتم العثور على الصفحة',
        ],

        'page-translation' => [
            'id-required' => 'معرف ترجمة الصفحة مطلوب',
            'invalid-id-format' => 'تنسيق المعرف غير صحيح. المتوقع تنسيق IRI مثل "/api/shop/page-translations/1" أو معرف رقمي',
            'not-found' => 'لم يتم العثور على ترجمة الصفحة',
        ],

        'product-bundle-option' => [
            'id-required' => 'معرف خيار الحزمة مطلوب',
            'invalid-id-format' => 'تنسيق المعرف غير صحيح. المتوقع تنسيق IRI مثل "/api/shop/product-bundle-options/1" أو معرف رقمي',
            'not-found' => 'لم يتم العثور على خيار الحزمة',
        ],

        'product-bundle-option-product' => [
            'id-required' => 'معرف منتج خيار الحزمة مطلوب',
            'invalid-id-format' => 'تنسيق المعرف غير صحيح. المتوقع تنسيق IRI مثل "/api/shop/product-bundle-option-products/1" أو معرف رقمي',
            'not-found' => 'لم يتم العثور على منتج خيار الحزمة',
        ],

        'product-downloadable-link' => [
            'id-required' => 'معرف رابط التنزيل مطلوب',
            'invalid-id-format' => 'تنسيق المعرف غير صحيح. المتوقع تنسيق IRI مثل "/api/shop/product-downloadable-links/1" أو معرف رقمي',
            'not-found' => 'لم يتم العثور على رابط التنزيل',
        ],

        'product-downloadable-sample' => [
            'id-required' => 'معرف عينة التنزيل مطلوب',
            'invalid-id-format' => 'تنسيق المعرف غير صحيح. المتوقع تنسيق IRI مثل "/api/shop/product-downloadable-samples/1" أو معرف رقمي',
            'not-found' => 'لم يتم العثور على عينة التنزيل',
        ],

        'product-grouped-product' => [
            'id-required' => 'معرف المنتج المجمّع مطلوب',
            'invalid-id-format' => 'تنسيق المعرف غير صحيح. المتوقع تنسيق IRI مثل "/api/shop/product-grouped-products/1" أو معرف رقمي',
            'not-found' => 'لم يتم العثور على المنتج المجمّع',
        ],

        'product-images' => [
            'id-required' => 'معرف صورة المنتج مطلوب',
            'invalid-id-format' => 'تنسيق المعرف غير صحيح. المتوقع تنسيق IRI مثل "/api/shop/product-images/1" أو معرف رقمي',
            'not-found' => 'لم يتم العثور على صورة المنتج',
        ],

        'product-videos' => [
            'id-required' => 'معرف فيديو المنتج مطلوب',
            'invalid-id-format' => 'تنسيق المعرف غير صحيح. المتوقع تنسيق IRI مثل "/api/shop/product-videos/1" أو معرف رقمي',
            'not-found' => 'لم يتم العثور على فيديو المنتج',
        ],

        'theme-customization' => [
            'id-required' => 'معرف تخصيص القالب مطلوب',
            'invalid-id-format' => 'تنسيق المعرف غير صحيح. المتوقع تنسيق IRI مثل "/api/shop/theme-customizations/1" أو معرف رقمي',
            'not-found' => 'لم يتم العثور على تخصيص القالب',
        ],

        'unknown-resource' => [
            'id-required' => 'معرف المورد مطلوب',
            'not-found' => 'لم يتم العثور على المورد',
        ],
    ],

    'admin' => [
        // login / logout / forgot-password / profile-update keys removed in the
        // 2026-05-27 auth refactor — admin clients now authenticate via
        // pre-issued integration tokens (Bearer header, AdminApiGuard).
        'profile' => [
            'unauthenticated' => 'أنت غير مصادق.',
        ],

        'dashboard' => [
            'invalid-type' => 'نوع إحصائية لوحة التحكم غير معروف. الأنواع الصالحة: over-all, today, stock-threshold-products, total-sales, top-selling-products, top-customers.',
        ],

        'rma' => [
            'not-found' => 'لم يتم العثور على طلب الإرجاع.',
            'invalid-input' => 'تفاصيل الإرجاع مفقودة أو غير صحيحة.',
            'invalid-order' => 'الطلب المحدد غير صالح.',
            'invalid-item' => 'العنصر المحدد غير مؤهل للإرجاع.',
            'invalid-resolution-type' => 'يجب أن يكون نوع الحل "return" أو "cancel_items".',
            'qty-exceeds' => 'الكمية المطلوبة تتجاوز الكمية القابلة للإرجاع.',
            'created' => 'تم إنشاء طلب الإرجاع بنجاح.',
            'status-updated' => 'تم تحديث حالة الإرجاع بنجاح.',
            'invalid-status' => 'حالة الإرجاع غير صحيحة.',
            'reopened' => 'تمت إعادة فتح طلب الإرجاع بنجاح.',
            'reopen-not-allowed' => 'لا يمكن إعادة فتح طلب الإرجاع هذا.',
            'refund-failed' => 'تعذر إنشاء استرداد المبلغ لهذا الإرجاع.',
            'message-sent' => 'تم إرسال الرسالة بنجاح.',
            'no-permission' => 'ليس لديك إذن لتنفيذ هذا الإجراء.',
            'reason-not-found' => 'لم يتم العثور على سبب الإرجاع.',
            'reason-deleted' => 'تم حذف سبب الإرجاع بنجاح.',
            'status-not-found' => 'لم يتم العثور على حالة الإرجاع.',
            'status-deleted' => 'تم حذف حالة الإرجاع بنجاح.',
            'status-default-undeletable' => 'لا يمكن حذف حالة الإرجاع الافتراضية.',
            'rule-not-found' => 'لم يتم العثور على قاعدة الإرجاع.',
            'rule-deleted' => 'تم حذف قاعدة الإرجاع بنجاح.',
            'custom-field-not-found' => 'لم يتم العثور على الحقل المخصص للإرجاع.',
            'custom-field-deleted' => 'تم حذف الحقل المخصص للإرجاع بنجاح.',
            'custom-field-options-required' => 'مطلوب خيار واحد على الأقل لنوع الحقل هذا.',
            'mass-indices-required' => 'مطلوب معرف واحد على الأقل.',
            'mass-delete-success' => 'تم حذف العناصر المحددة.',
            'mass-update-success' => 'تم تحديث العناصر المحددة.',
            'mass-value-invalid' => 'يجب أن تكون قيمة الحالة 0 أو 1.',
            'image-not-found' => 'لم يتم العثور على صورة الإرجاع.',
            'attachment-not-found' => 'لم يتم العثور على مرفق الرسالة.',
        ],

        'eu-withdrawal' => [
            'not-found' => 'لم يتم العثور على إقرار الانسحاب.',
            'no-permission' => 'ليس لديك إذن لإدارة طلبات الانسحاب الأوروبية.',
            'declined' => 'تم وضع علامة مرفوض على الانسحاب.',
            'refunded' => 'تم وضع علامة مسترد على الانسحاب.',
            'confirmation-resent' => 'تمت إعادة إرسال بريد التأكيد بنجاح.',
            'confirmation-failed' => 'تعذر إرسال بريد التأكيد.',
        ],

        'reporting' => [
            'invalid-entity' => 'كيان تقارير غير معروف.',
            'invalid-type' => 'نوع إحصائية تقارير غير معروف لهذا الكيان.',
            'export-format-unsupported' => 'تنسيقات التصدير المدعومة هي csv و xls و xlsx فقط.',
        ],

        'configuration' => [
            'unauthenticated' => 'أنت غير مصادق.',
            'no-permission' => 'ليس لديك إذن لتحديث الإعدادات.',
            'slug-required' => 'معامل الاستعلام `slug` مطلوب.',
            'slug-not-found' => 'لا يوجد قسم إعدادات مسجل للـ slug المحدد.',
            'values-required' => 'مطلوب قيمة واحدة على الأقل ضمن `values`.',
            'scope-escape' => 'المفتاح `:key` خارج الشجرة الفرعية للـ slug المطلوب.',
            'unknown-field' => 'المفتاح `:key` ليس حقل إعدادات مسجلًا.',
            'validation-failed' => 'فشل التحقق من صحة قيمة أو أكثر من قيم الإعدادات.',
            'file-upload-rest-only' => 'رفع الملفات الثنائية للحقل `:field` مدعوم فقط عبر نقطة نهاية REST متعددة الأجزاء، وليس GraphQL.',
            'custom-view-readonly' => 'الحقل `:field` حقل مخصص يُعرض عبر blade ولا يمكن تحديثه عبر API.',
            'update-success' => 'تم تحديث الإعدادات بنجاح.',
        ],

        'customer' => [
            'not-found' => 'لم يتم العثور على العميل.',
            'no-permission' => 'ليس لديك إذن لتنفيذ هذا الإجراء.',
            'has-active-orders' => 'لدى العميل طلبات معلقة أو قيد المعالجة ولا يمكن حذفه.',
            'deleted' => 'تم حذف العميل بنجاح.',
            'mass-delete-indices-required' => 'مطلوب معرف عميل واحد على الأقل.',
            'mass-delete-success' => 'تم حذف العملاء.',
            'mass-update-indices-required' => 'مطلوب معرف عميل واحد على الأقل.',
            'mass-update-value-invalid' => 'يجب أن تكون قيمة الحالة 0 أو 1.',
            'mass-update-success' => 'تم تحديث حالة العميل.',

            'address' => [
                'not-found' => 'لم يتم العثور على العنوان.',
                'not-owned' => 'هذا العنوان لا ينتمي إلى العميل المحدد.',
                'customer-id-required' => 'customerId مطلوب لتحديد مالك العنوان.',
                'deleted' => 'تم حذف العنوان بنجاح.',
            ],

            'note' => [
                'empty' => 'نص الملاحظة مطلوب.',
                'created' => 'تمت إضافة الملاحظة بنجاح.',
            ],

            'impersonate' => [
                'success' => 'تم إصدار رمز انتحال الهوية.',
            ],

            'review' => [
                'not-found' => 'لم يتم العثور على المراجعة.',
                'no-permission' => 'ليس لديك إذن لتنفيذ هذا الإجراء.',
                'invalid-status' => 'يجب أن تكون الحالة واحدة من: pending, approved, disapproved.',
                'updated' => 'تم تحديث المراجعة بنجاح.',
                'deleted' => 'تم حذف المراجعة بنجاح.',
                'mass-delete-indices-required' => 'مطلوب معرف مراجعة واحد على الأقل.',
                'mass-delete-success' => 'تم حذف المراجعات.',
                'mass-update-indices-required' => 'مطلوب معرف مراجعة واحد على الأقل.',
                'mass-update-value-invalid' => 'يجب أن تكون قيمة الحالة واحدة من: pending, approved, disapproved.',
                'mass-update-success' => 'تم تحديث حالة المراجعة.',
            ],

            'group' => [
                'not-found' => 'لم يتم العثور على مجموعة العملاء.',
                'deleted' => 'تم حذف مجموعة العملاء بنجاح.',
                'is-system' => 'لا يمكن حذف مجموعات العملاء الخاصة بالنظام.',
                'has-customers' => 'مجموعة العملاء مرتبطة بعملاء ولا يمكن حذفها.',
                'system-code-immutable' => 'لا يمكن تغيير رمز مجموعة عملاء خاصة بالنظام.',
                'system-flag-immutable' => 'لا يمكن تغيير العلامة is_user_defined لمجموعة عملاء خاصة بالنظام.',
                'mass-delete-indices-required' => 'مطلوب معرف مجموعة عملاء واحد على الأقل.',
                'mass-delete-success' => 'تم حذف مجموعات العملاء.',
            ],

            'gdpr' => [
                'not-found' => 'لم يتم العثور على طلب GDPR.',
                'no-permission' => 'ليس لديك إذن لتنفيذ هذا الإجراء.',
                'deleted' => 'تم حذف طلب GDPR بنجاح.',
                'invalid-status' => 'يجب أن تكون الحالة واحدة من: pending, processing, declined, approved, revoked.',
                'no-changes' => 'مطلوب الحالة أو الرسالة على الأقل.',
                'already-processed' => 'تمت معالجة طلب GDPR هذا بالفعل ولا يمكن إعادة تشغيله.',

                'process' => [
                    'approved' => 'تمت الموافقة على طلب GDPR.',
                    'deleted' => 'تمت الموافقة على طلب GDPR وحذف العميل.',
                ],
            ],
        ],

        'order' => [
            'not-found' => 'لم يتم العثور على الطلب.',

            'reorder' => [
                'success' => 'تمت إعادة الطلب بنجاح. تم إنشاء سلة مسودة جديدة.',
                'cannot-reorder' => 'لا يمكن إعادة طلب هذا الطلب (طلب ضيف أو أن عنصرًا أو أكثر لم يعد قابلًا للبيع).',
                'failed' => 'فشلت إعادة الطلب. يرجى المحاولة مرة أخرى.',
                'guest-not-supported' => 'إعادة الطلب غير مدعومة لطلبات الضيوف.',
                'items-not-saleable' => 'عنصر أو أكثر في هذا الطلب لم يعد متاحًا للشراء.',
                'no-permission' => 'ليس لديك إذن لإنشاء طلبات.',
                'disabled-in-settings' => 'إعادة الطلب من قِبل المسؤول معطلة في إعدادات المتجر.',
            ],

            'actions' => [
                'cancel' => [
                    'closed' => 'لا يمكن إلغاء الطلبات المغلقة.',
                    'fraud' => 'لا يمكن إلغاء الطلبات الاحتيالية.',
                    'nothing-to-cancel' => 'لا يوجد ما يمكن إلغاؤه في هذا الطلب.',
                    'already-processed' => 'تمت فوترة هذا الطلب أو شحنه بالفعل ولم يعد من الممكن إلغاؤه. يمكنك إصدار استرداد بدلًا من ذلك.',
                    'no-permission' => 'ليس لديك إذن لإلغاء الطلبات.',
                    'success' => 'تم إلغاء الطلب بنجاح.',
                    'failed' => 'تعذر إلغاء الطلب.',
                ],

                'comment' => [
                    'empty' => 'التعليق مطلوب.',
                    'success' => 'تمت إضافة التعليق إلى الطلب.',
                    'failed' => 'تعذرت إضافة التعليق.',
                ],

                'invoice' => [
                    'closed' => 'لا يمكن فوترة الطلبات المغلقة.',
                    'fraud' => 'لا يمكن فوترة الطلبات الاحتيالية.',
                    'nothing-to-invoice' => 'لا يوجد ما يمكن فوترته في هذا الطلب.',
                    'already-invoiced' => 'تم إنشاء فاتورة لهذا الطلب بالفعل.',
                    'paypal-standard-blocked' => 'لا يمكن إنشاء فواتير للطلبات المدفوعة عبر PayPal Standard.',
                    'no-permission' => 'ليس لديك إذن لإنشاء الفواتير.',
                    'items-required' => 'مطلوب عنصر واحد على الأقل بكمية موجبة.',
                    'qty-exceeds' => 'الكمية المطلوبة (:requested) تتجاوز الكمية المتاحة (:available) لرمز SKU :sku.',
                    'not-found' => 'لم يتم العثور على الفاتورة.',
                    'success' => 'تم إنشاء الفاتورة بنجاح.',
                    'failed' => 'تعذر إنشاء الفاتورة.',
                    'pdf-failed' => 'تعذر إنشاء ملف PDF للفاتورة.',
                    'email-invalid' => 'مطلوب بريد إلكتروني صالح للمستلم.',
                    'duplicate-sent' => 'تم إرسال بريد الفاتورة إلى :email.',
                    'mass-status-indices-required' => 'مطلوب معرف فاتورة واحد على الأقل.',
                    'mass-status-value-invalid' => 'يجب أن تكون الحالة واحدة من: pending, paid, overdue.',
                    'mass-status-success' => 'تم تحديث حالة الفاتورة بنجاح.',
                ],

                'shipment' => [
                    'closed' => 'لا يمكن شحن الطلبات المغلقة.',
                    'fraud' => 'لا يمكن شحن الطلبات الاحتيالية.',
                    'nothing-to-ship' => 'لا يوجد ما يمكن شحنه في هذا الطلب.',
                    'no-permission' => 'ليس لديك إذن لشحن الطلبات.',
                    'source-required' => 'مصدر المخزون مطلوب.',
                    'items-required' => 'مطلوب عنصر واحد على الأقل بكمية موجبة.',
                    'qty-exceeds' => 'الكمية المطلوبة (:requested) تتجاوز الكمية المتاحة (:available) لرمز SKU :sku.',
                    'inventory-insufficient' => 'المخزون في المصدر المحدد غير كافٍ لرمز SKU :sku.',
                    'not-found' => 'لم يتم العثور على الشحنة.',
                    'success' => 'تم إنشاء الشحنة بنجاح.',
                    'failed' => 'تعذر إنشاء الشحنة.',
                ],

                'refund' => [
                    'closed' => 'لا يمكن استرداد مبالغ الطلبات المغلقة.',
                    'fraud' => 'لا يمكن استرداد مبالغ الطلبات الاحتيالية.',
                    'nothing-to-refund' => 'لم يتبقَّ شيء لاسترداده في هذا الطلب.',
                    'no-permission' => 'ليس لديك إذن لإنشاء عمليات الاسترداد.',
                    'qty-exceeds' => 'الكمية المطلوبة (:requested) تتجاوز الكمية المتاحة (:available) لرمز SKU :sku.',
                    'amount-zero' => 'مبلغ الاسترداد المحسوب صفر. اضبط الكمية أو الشحن أو قيم التعديل.',
                    'amount-exceeds-max' => 'مبلغ الاسترداد (:amount) يتجاوز الحد الأقصى القابل للاسترداد (:max).',
                    'not-found' => 'لم يتم العثور على الاسترداد.',
                    'success' => 'تم إنشاء الاسترداد بنجاح.',
                    'failed' => 'تعذر إنشاء الاسترداد.',
                ],
            ],
        ],

        'appearance' => [
            'no-permission' => 'ليس لديك إذن لإدارة المظهر.',

            'theme' => [
                'not-found' => 'لم يتم العثور على القالب.',
                'not-installed' => 'هذا القالب غير مثبت.',
                'channel-ids-required' => 'مطلوب قناة واحدة على الأقل.',
                'unknown-channel' => 'قناة أو أكثر غير موجودة.',
                'activated' => 'تم تفعيل القالب بنجاح.',
            ],

            'section' => [
                'not-found' => 'لم يتم العثور على القسم.',
                'invalid-payload' => 'حمولة قسم غير مدعومة.',
                'footer-links-exists' => 'تحتوي هذه القناة بالفعل على قسم روابط التذييل.',
                'options-required' => 'الخيارات مطلوبة.',
                'status-required' => 'الحالة مطلوبة.',
                'section-ids-required' => 'مطلوب قسم واحد على الأقل.',
                'reorder-single-scope' => 'يمكن إعادة ترتيب الأقسام ضمن قالب وقناة واحدة فقط في كل مرة.',
                'reorder-incomplete' => 'تتطلب إعادة الترتيب جميع أقسام القالب والقناة، بالترتيب الذي يجب أن تُعرض به.',
                'unknown-section' => 'قسم أو أكثر غير موجود.',
                'file-required' => 'الملف مطلوب.',
                'created' => 'تم إنشاء القسم بنجاح.',
                'updated' => 'تم تحديث القسم بنجاح.',
                'deleted' => 'تم حذف القسم بنجاح.',
                'draft-saved' => 'تم تجهيز تغييرات القسم بنجاح.',
                'status-staged' => 'تم تجهيز حالة القسم بنجاح.',
                'order-staged' => 'تم تجهيز ترتيب الأقسام بنجاح.',
                'duplicated' => 'تم نسخ القسم بنجاح.',
                'published' => 'تم نشر تغييرات القسم بنجاح.',
                'discarded' => 'تم تجاهل تغييرات القسم بنجاح.',
                'media-uploaded' => 'تم رفع الوسائط بنجاح.',
            ],
        ],

        'sales' => [
            'no-permission' => 'ليس لديك إذن لعرض مورد المبيعات هذا.',

            'invoice' => [
                'not-found' => 'لم يتم العثور على الفاتورة.',
                'no-permission' => 'ليس لديك إذن لعرض الفواتير.',
            ],

            'shipment' => [
                'not-found' => 'لم يتم العثور على الشحنة.',
                'no-permission' => 'ليس لديك إذن لعرض الشحنات.',
            ],

            'refund' => [
                'not-found' => 'لم يتم العثور على الاسترداد.',
                'no-permission' => 'ليس لديك إذن لعرض عمليات الاسترداد.',
                'export-format-unsupported' => 'عمليات التصدير المدعومة هي csv و xls و xlsx فقط.',
            ],

            'transaction' => [
                'not-found' => 'لم يتم العثور على المعاملة.',
                'no-permission' => 'ليس لديك إذن لعرض المعاملات.',

                'create' => [
                    'invoice-required' => 'الحقل invoiceId مطلوب.',
                    'payment-method-required' => 'الحقل paymentMethod مطلوب.',
                    'amount-invalid' => 'الحقل amount مطلوب ويجب أن يكون رقميًا.',
                    'invoice-missing' => 'تعذر العثور على الفاتورة المحددة.',
                    'already-paid' => 'هذه الفاتورة مدفوعة بالكامل بالفعل.',
                    'amount-exceeds' => 'مبلغ الدفع يتجاوز الإجمالي الكلي للفاتورة.',
                    'amount-zero' => 'يجب أن يكون مبلغ الدفع أكبر من صفر.',
                ],
            ],

            'booking' => [
                'not-found' => 'لم يتم العثور على الحجز.',
                'no-permission' => 'ليس لديك إذن لعرض الحجوزات.',
            ],

            'export' => [
                'format-unsupported' => 'عمليات التصدير المدعومة هي csv و xls و xlsx فقط.',
            ],
        ],

        'cart' => [
            'not-found' => 'لم يتم العثور على السلة.',
            'not-draft' => 'لا يمكن تعديل هذه السلة عبر API الخاص بالمسؤول.',
            'product-required' => 'productId مطلوب.',
            'product-not-found' => 'لم يتم العثور على المنتج.',
            'booking-unsupported' => 'لا يمكن إضافة منتجات الحجز إلى طلب مسودة من المسؤول. يجب إجراء عمليات شراء الحجز من خلال واجهة متجر العميل.',
            'product-not-saleable' => 'هذا المنتج غير متاح للبيع (نفد من المخزون أو معطل) ولا يمكن إضافته إلى الطلب.',
            'qty-required' => 'qty مطلوب ويجب أن يكون كائنًا غير فارغ بصيغة itemId => quantity.',
            'cart-item-required' => 'cartItemId مطلوب.',
            'item-added' => 'تمت إضافة العنصر إلى السلة.',
            'item-add-failed' => 'تعذرت إضافة العنصر إلى السلة.',
            'item-updated' => 'تم تحديث عناصر السلة.',
            'item-update-failed' => 'تعذر تحديث عناصر السلة.',
            'item-removed' => 'تمت إزالة العنصر من السلة.',
            'item-remove-failed' => 'تعذرت إزالة عنصر السلة.',
            'address-saved' => 'تم حفظ العنوان.',
            'address-required' => 'عنوان الفوترة مطلوب.',
            'address-incomplete' => 'الحقل :field مطلوب لحفظ العنوان.',
            'coupon-code-required' => 'رمز القسيمة مطلوب.',
            'coupon-applied' => 'تم تطبيق القسيمة.',
            'coupon-already-applied' => 'القسيمة مطبقة بالفعل على هذه السلة.',
            'coupon-not-found' => 'القسيمة غير موجودة أو غير نشطة.',
            'coupon-removed' => 'تمت إزالة القسيمة.',
            'coupon-error' => 'حدث خطأ ما أثناء تطبيق القسيمة.',
            'unknown-error' => 'حدث خطأ ما.',
            'draft-created' => 'تم إنشاء سلة المسودة.',
            'draft-failed' => 'تعذر إنشاء سلة المسودة.',
            'addresses-required-for-shipping' => 'يجب حفظ العناوين قبل اختيار طريقة الشحن.',
            'shipping-required-for-payment' => 'يجب اختيار طريقة الشحن قبل طريقة الدفع.',
            'shipping-method-required' => 'طريقة الشحن مطلوبة.',
            'shipping-method-saved' => 'تم حفظ طريقة الشحن.',
            'shipping-method-failed' => 'تعذر حفظ طريقة الشحن.',
            'payment-method-required' => 'طريقة الدفع مطلوبة.',
            'payment-method-saved' => 'تم حفظ طريقة الدفع.',
            'payment-method-failed' => 'تعذر حفظ طريقة الدفع.',

            'place-order' => [
                'success' => 'تم تقديم الطلب بنجاح.',
                'failed' => 'تعذر تقديم الطلب.',
                'error' => 'تعذر تقديم الطلب: تحتوي السلة على أخطاء.',
                'minimum-order-error' => 'الحد الأدنى لمبلغ الطلب هو :amount. أضف المزيد من العناصر قبل تقديم الطلب.',
                'empty-cart' => 'السلة فارغة — أضف عناصر قبل تقديم الطلب.',
                'addresses-required' => 'يجب حفظ عنواني الفوترة والشحن قبل تقديم الطلب.',
                'shipping-required' => 'يجب اختيار طريقة شحن قبل تقديم الطلب.',
                'payment-required' => 'يجب اختيار طريقة دفع قبل تقديم الطلب.',
                'payment-method-unsupported' => 'لا يتم دعم سوى cashondelivery و moneytransfer للطلبات المقدمة من المسؤول.',
            ],
        ],

        'product' => [
            'not-found' => 'لم يتم العثور على المنتج.',

            'mass-delete-success' => 'تم حذف المنتجات بنجاح.',
            'mass-update-status-success' => 'تم تحديث حالة المنتجات بنجاح.',
            'indices-required' => 'الحقل indices مطلوب ويجب أن يكون مصفوفة غير فارغة.',
            'indices-invalid' => 'يجب أن يكون الحقل indices مصفوفة من معرفات المنتجات الصحيحة.',
            'value-invalid' => 'الحقل value مطلوب ويجب أن يكون 0 أو 1.',
            'mass-delete-failed' => 'تعذر حذف منتج أو أكثر.',
            'no-permission' => 'ليس لديك إذن لإدارة المنتجات.',

            'copy-success' => 'تم نسخ المنتج بنجاح.',
            'copy-failed' => 'فشل نسخ المنتج.',
            'copy-variant-not-supported' => 'لا يمكن نسخ متغيرات المنتجات القابلة للتهيئة. انسخ المنتج الأصلي القابل للتهيئة بدلًا من ذلك.',

            'create' => [
                'created' => 'تم إنشاء المنتج بنجاح.',
                'create-failed' => 'فشل إنشاء المنتج.',
                'sku-required' => 'الحقل sku مطلوب.',
                'sku-unique' => 'قيمة sku مستخدمة بالفعل.',
                'sku-invalid' => 'يمكن أن يحتوي sku على أحرف وأرقام وشرطات فقط (مثل "sp-001").',
                'attribute-family-required' => 'الحقل attribute_family_id مطلوب.',
                'attribute-family-not-found' => 'عائلة السمات المحددة غير موجودة.',
                'type-required' => 'الحقل type مطلوب.',
                'type-not-yet-supported' => 'نوع المنتج ":type" غير مدعوم في هذا API. الأنواع المسموح بها: simple, virtual, downloadable, grouped, bundle, configurable, booking.',
                'super-attributes-required' => 'الحقل super_attributes مطلوب عندما يكون type=configurable. قدّم خريطة من رمز السمة (أو معرفها) إلى option_ids، مثل { "color": [1,2], "size": [4,5] }.',
                'super-attributes-invalid' => 'يجب أن يكون الحقل super_attributes خريطة غير فارغة من رمز السمة (أو معرفها) إلى قائمة غير فارغة من option_ids.',
                'no-permission' => 'ليس لديك إذن لإنشاء المنتجات.',
            ],

            'update' => [
                'updated' => 'تم تحديث المنتج بنجاح.',
                'update-failed' => 'فشل تحديث المنتج.',
                'id-required' => 'معرف المنتج مطلوب.',
                'url-key-required' => 'لا يمكن أن يكون الحقل url_key فارغًا.',
                'url-key-unique' => 'قيمة url_key مستخدمة بالفعل من قِبل منتج آخر.',
                'special-price-invalid' => 'يجب أن يكون special_price رقمًا أقل من price.',
                'special-price-date-range-invalid' => 'يجب أن يكون special_price_to في تاريخ special_price_from أو بعده.',
                'boolean-field-invalid' => 'يجب أن يكون الحقل :field مساويًا لـ 0 أو 1.',
                'categories-invalid' => 'يجب أن يكون الحقل categories مصفوفة من معرفات الفئات.',
                'channels-invalid' => 'يجب أن يكون الحقل channels مصفوفة من معرفات القنوات.',
                'super-attributes-cannot-change' => 'لا يمكن تغيير super_attributes بعد إنشاء المنتج.',
                'sub-resource-stripped-images' => 'يجب إدارة الصور عبر POST /api/admin/catalog/products/{id}/images. تم تجاهل الحقل images.',
                'sub-resource-stripped-inventories' => 'يجب إدارة المخزون عبر PUT /api/admin/catalog/products/{id}/inventories. تم تجاهل الحقل inventories.',
                'sub-resource-stripped-customer-group-prices' => 'يجب إدارة أسعار مجموعات العملاء عبر /api/admin/catalog/products/{id}/customer-group-prices. تم تجاهل الحقل customer_group_prices.',
                'translations-single-locale' => 'تم تحديث اللغة المطلوبة فقط. لتحديث اللغة (اللغات) :locales، أرسل طلبًا منفصلًا مع ?locale=<code> لكل منها.',
            ],

            'delete' => [
                'deleted' => 'تم حذف المنتج بنجاح.',
                'delete-failed' => 'فشل حذف المنتج.',
            ],

            'image' => [
                'uploaded' => 'تم رفع صورة المنتج بنجاح.',
                'updated' => 'تم تحديث صورة المنتج بنجاح.',
                'update-empty' => 'أرسل نصًا بديلًا أو موضعًا للتحديث.',
                'reordered' => 'تمت إعادة ترتيب صور المنتج بنجاح.',
                'deleted' => 'تم حذف صورة المنتج بنجاح.',
                'image-required' => 'ملف صورة مطلوب (حقل multipart "image").',
                'image-invalid-type' => 'يجب أن يكون نوع الصورة واحدًا من: bmp, jpeg, jpg, png, webp.',
                'image-too-large' => 'الصورة كبيرة جدًا. الحد الأقصى المسموح به هو 4 ميغابايت.',
                'not-found' => 'لم يتم العثور على صورة المنتج.',
                'no-permission' => 'ليس لديك إذن لإدارة صور المنتجات.',
                'upload-failed' => 'فشل رفع صورة المنتج.',
                'reorder-failed' => 'فشلت إعادة ترتيب صور المنتج.',
                'delete-failed' => 'فشل حذف صورة المنتج.',
                'order-required' => 'المصفوفة "order" مطلوبة ويجب ألا تكون فارغة.',
                'order-invalid' => 'يجب أن يكون كل إدخال في "order" كائنًا يحتوي على id و position.',
                'id-not-on-product' => 'الصورة :id لا تنتمي إلى هذا المنتج.',
                'graphql-upload-unsupported' => 'رفع الصور عبر GraphQL غير مدعوم. استخدم POST /api/admin/catalog/products/{productId}/images مع multipart/form-data.',
            ],

            'downloadable' => [
                'file-required' => 'ملف مطلوب (حقل multipart "file").',
                'file-too-large' => 'الملف كبير جدًا. الحد الأقصى المسموح به هو 128 ميغابايت.',
                'upload-failed' => 'فشل رفع الملف القابل للتنزيل.',
                'file-not-found' => 'لم يتم العثور على ملف مخزن لهذا المنتج والسمة.',
                'no-permission' => 'ليس لديك إذن لإدارة ملفات المنتجات القابلة للتنزيل.',
            ],

            'video' => [
                'uploaded' => 'تم رفع فيديو المنتج بنجاح.',
                'deleted' => 'تم حذف فيديو المنتج بنجاح.',
                'video-required' => 'ملف فيديو مطلوب (حقل multipart "video").',
                'video-invalid-type' => 'يجب أن يكون نوع الفيديو واحدًا من: mp4, webm, ogg, mov.',
                'video-too-large' => 'الفيديو كبير جدًا. الحد الأقصى المسموح به هو 100 ميغابايت.',
                'not-found' => 'لم يتم العثور على فيديو المنتج.',
                'no-permission' => 'ليس لديك إذن لإدارة فيديوهات المنتجات.',
                'upload-failed' => 'فشل رفع فيديو المنتج.',
                'delete-failed' => 'فشل حذف فيديو المنتج.',
                'graphql-upload-unsupported' => 'رفع الفيديو عبر GraphQL غير مدعوم. استخدم POST /api/admin/catalog/products/{productId}/videos مع multipart/form-data.',
            ],

            'inventory' => [
                'updated' => 'تم حفظ مخزون المنتج بنجاح.',
                'inventories-required' => 'الحقل inventories مطلوب ويجب أن يكون خريطة غير فارغة من inventory_source_id إلى الكمية.',
                'inventories-invalid' => 'يجب أن يكون الحقل inventories خريطة من inventory_source_id (عدد صحيح موجب) إلى الكمية (عدد صحيح غير سالب).',
                'source-not-found' => 'مصدر المخزون :id غير موجود.',
                'qty-invalid' => 'يجب أن تكون كل كمية عددًا صحيحًا غير سالب.',
                'no-permission' => 'ليس لديك إذن لإدارة مخزون المنتجات.',
                'not-found' => 'لم يتم العثور على المنتج.',
            ],

            'customer-group-price' => [
                'created' => 'تمت إضافة سعر مجموعة العملاء بنجاح.',
                'updated' => 'تم تحديث سعر مجموعة العملاء بنجاح.',
                'deleted' => 'تم حذف سعر مجموعة العملاء بنجاح.',
                'qty-required' => 'الحقل qty مطلوب.',
                'qty-invalid' => 'يجب أن يكون الحقل qty عددًا صحيحًا أكبر من أو يساوي 1.',
                'value-type-required' => 'الحقل value_type مطلوب.',
                'value-type-invalid' => 'يجب أن يكون الحقل value_type إما "fixed" أو "discount".',
                'value-required' => 'الحقل value مطلوب.',
                'value-invalid' => 'يجب أن يكون الحقل value رقمًا غير سالب.',
                'customer-group-not-found' => 'مجموعة العملاء المحددة غير موجودة.',
                'duplicate-qty-group' => 'يوجد بالفعل سعر مجموعة عملاء لهذه التركيبة (qty, customer_group_id) لهذا المنتج.',
                'not-found' => 'لم يتم العثور على سعر مجموعة العملاء.',
                'no-permission' => 'ليس لديك إذن لإدارة أسعار مجموعات العملاء للمنتجات.',
            ],
        ],

        'category' => [
            'not-found' => 'لم يتم العثور على الفئة.',

            'created' => 'تم إنشاء الفئة بنجاح.',
            'updated' => 'تم تحديث الفئة بنجاح.',
            'deleted' => 'تم حذف الفئة بنجاح.',
            'slug-required' => 'الحقل slug مطلوب.',
            'slug-unique' => 'قيمة slug مستخدمة بالفعل.',
            'name-required' => 'الحقل name مطلوب.',
            'position-required' => 'الحقل position مطلوب.',
            'attributes-required' => 'مطلوب سمة واحدة قابلة للتصفية على الأقل.',
            'description-required-for-mode' => 'الوصف مطلوب لوضع العرض المختار.',
            'cannot-delete-root' => 'لا يمكن حذف الفئة الجذرية والفئات الجذرية للقنوات.',
            'cannot-delete-channel-root' => 'هذه الفئة هي الجذر لقناة ولا يمكن حذفها.',
            'delete-failed' => 'تعذر حذف الفئة.',
            'no-permission' => 'ليس لديك إذن لإدارة الفئات.',
            'mass-delete-success' => 'تم حذف الفئات بنجاح.',
            'mass-update-status-success' => 'تم تحديث حالة الفئات بنجاح.',
            'mass-delete-indices-required' => 'الحقل indices مطلوب ويجب أن يكون مصفوفة غير فارغة.',
            'mass-update-status-value-required' => 'الحقل value مطلوب ويجب أن يكون 0 أو 1.',
        ],

        'attribute' => [
            'not-found' => 'لم يتم العثور على السمة.',

            'create-success' => 'تم إنشاء السمة بنجاح.',
            'update-success' => 'تم تحديث السمة بنجاح.',
            'delete-success' => 'تم حذف السمة بنجاح.',
            'delete-failed' => 'تعذر حذف السمة.',
            'mass-delete-success' => 'تم حذف السمات بنجاح.',
            'mass-delete-indices-required' => 'الحقل indices مطلوب ويجب أن يكون مصفوفة غير فارغة.',

            'code-immutable' => 'لا يمكن تغيير رمز السمة بعد إنشائها.',
            'type-immutable' => 'لا يمكن تغيير نوع السمة أثناء وجود قيم لها في المنتجات.',
            'locale-scope-immutable' => 'لا يمكن تغيير نطاق اللغة أثناء وجود قيم للسمة في المنتجات.',
            'system-attribute' => 'لا يمكن حذف سمات النظام.',
            'in-use-family' => 'السمة جزء من عائلة سمات واحدة أو أكثر (معرفات المجموعات: :ids). أزلها من تلك العائلات أولًا.',

            'option-not-found' => 'لم يتم العثور على خيار السمة.',
            'option-not-supported' => 'نوع السمة ":type" لا يدعم الخيارات. فقط سمات select و multiselect و checkbox يمكن أن تحتوي على خيارات.',
            'option-in-use' => 'هذا الخيار مستخدم من قِبل :count منتج (منتجات) ولا يمكن حذفه.',
            'option-delete-success' => 'تم حذف خيار السمة بنجاح.',
        ],

        'family' => [
            'not-found' => 'لم يتم العثور على عائلة السمات.',

            'created' => 'تم إنشاء عائلة السمات بنجاح.',
            'updated' => 'تم تحديث عائلة السمات بنجاح.',
            'deleted' => 'تم حذف عائلة السمات بنجاح.',
            'code-required' => 'رمز العائلة مطلوب.',
            'code-unique' => 'يجب أن يكون رمز العائلة فريدًا.',
            'name-required' => 'اسم العائلة مطلوب.',
            'group-code-required' => 'يجب أن يكون لكل مجموعة سمات رمز.',
            'group-name-required' => 'يجب أن يكون لكل مجموعة سمات اسم.',
            'group-column-invalid' => 'يجب أن يكون عمود كل مجموعة سمات 1 أو 2.',
            'group-name-duplicate' => 'يجب أن يكون اسم مجموعة السمات فريدًا داخل عائلة السمات.',
            'update-failed' => 'تعذر تحديث عائلة السمات.',
            'create-failed' => 'تعذر إنشاء عائلة السمات.',
            'default-delete-error' => 'لا يمكن حذف عائلة السمات الافتراضية.',
            'last-delete-error' => 'مطلوب عائلة سمات واحدة على الأقل.',
            'attribute-product-error' => 'عائلة السمات هذه مستخدمة من قِبل منتج أو أكثر. أعد تعيين تلك المنتجات قبل حذفها.',
            'delete-failed' => 'تعذر حذف عائلة السمات.',
            'no-permission' => 'ليس لديك إذن لإدارة عائلات السمات.',
        ],

        'settings' => [
            'locale' => [
                'not-found' => 'لم يتم العثور على اللغة.',
                'created' => 'تم إنشاء اللغة بنجاح.',
                'updated' => 'تم تحديث اللغة بنجاح.',
                'deleted' => 'تم حذف اللغة بنجاح.',
                'cannot-delete-last' => 'مطلوب لغة واحدة على الأقل — لا يمكن حذف آخر لغة متبقية.',
                'cannot-delete-channel-default' => 'هذه اللغة هي اللغة الافتراضية لقناة أو أكثر ولا يمكن حذفها.',
                'delete-failed' => 'تعذر حذف اللغة.',
                'no-permission' => 'ليس لديك إذن لإدارة اللغات.',
                'mass-delete-success' => 'تم حذف اللغات بنجاح.',
                'mass-delete-indices-required' => 'الحقل indices مطلوب ويجب أن يكون مصفوفة غير فارغة.',
            ],

            'theme' => [
                'not-found' => 'لم يتم العثور على تخصيص القالب.',
                'created' => 'تم إنشاء تخصيص القالب بنجاح.',
                'updated' => 'تم تحديث تخصيص القالب بنجاح.',
                'deleted' => 'تم حذف تخصيص القالب بنجاح.',
                'delete-failed' => 'تعذر حذف تخصيص القالب.',
                'no-permission' => 'ليس لديك إذن لإدارة تخصيصات القالب.',
                'mass-delete-success' => 'تم حذف تخصيصات القالب بنجاح.',
                'mass-delete-indices-required' => 'الحقل indices مطلوب ويجب أن يكون مصفوفة غير فارغة.',
                'mass-update-success' => 'تم تحديث تخصيصات القالب بنجاح.',
                'mass-update-indices-required' => 'الحقل indices مطلوب ويجب أن يكون مصفوفة غير فارغة.',
                'mass-update-value-invalid' => 'يجب أن يكون الحقل value مساويًا لـ 0 أو 1.',
            ],

            'inventory-source' => [
                'not-found' => 'لم يتم العثور على مصدر المخزون.',
                'created' => 'تم إنشاء مصدر المخزون بنجاح.',
                'updated' => 'تم تحديث مصدر المخزون بنجاح.',
                'deleted' => 'تم حذف مصدر المخزون بنجاح.',
                'code-unique' => 'يوجد بالفعل مصدر مخزون بهذا الرمز.',
                'last-delete-error' => 'مطلوب مصدر مخزون واحد على الأقل — لا يمكن حذف آخر مصدر متبقٍ.',
                'in-use' => 'مصدر المخزون هذا مُشار إليه في مخزون المنتجات ولا يمكن حذفه.',
                'delete-failed' => 'تعذر حذف مصدر المخزون.',
                'no-permission' => 'ليس لديك إذن لإدارة مصادر المخزون.',
                'mass-delete-success' => 'تم حذف مصادر المخزون بنجاح.',
                'mass-delete-indices-required' => 'الحقل indices مطلوب ويجب أن يكون مصفوفة غير فارغة.',
            ],

            'exchange-rate' => [
                'not-found' => 'لم يتم العثور على سعر الصرف.',
                'created' => 'تم إنشاء سعر الصرف بنجاح.',
                'updated' => 'تم تحديث سعر الصرف بنجاح.',
                'deleted' => 'تم حذف سعر الصرف بنجاح.',
                'duplicate-pair' => 'يوجد بالفعل سعر صرف لهذه العملة المستهدفة. حدّث الصف الموجود أو احذفه أولًا.',
                'delete-failed' => 'تعذر حذف سعر الصرف.',
                'no-permission' => 'ليس لديك إذن لإدارة أسعار الصرف.',
                'mass-delete-success' => 'تم حذف أسعار الصرف بنجاح.',
                'mass-delete-indices-required' => 'الحقل indices مطلوب ويجب أن يكون مصفوفة غير فارغة.',
                'update-rates-success' => 'تم تحديث أسعار الصرف بنجاح.',
                'update-rates-failed' => 'تعذر تحديث أسعار الصرف من المزود الخارجي.',
                'provider-not-configured' => 'لم تتم تهيئة أي مزود خارجي لأسعار الصرف.',
            ],

            'tax-rate' => [
                'not-found' => 'لم يتم العثور على معدل الضريبة.',
                'created' => 'تم إنشاء معدل الضريبة بنجاح.',
                'updated' => 'تم تحديث معدل الضريبة بنجاح.',
                'deleted' => 'تم حذف معدل الضريبة بنجاح.',
                'delete-failed' => 'تعذر حذف معدل الضريبة.',
                'no-permission' => 'ليس لديك إذن لإدارة معدلات الضريبة.',
                'zip-code-required' => 'الحقل zip_code مطلوب عندما تكون قيمة is_zip هي false.',
                'zip-range-required' => 'كل من zip_from و zip_to مطلوبان عندما تكون قيمة is_zip هي true.',
            ],

            'tax-category' => [
                'not-found' => 'لم يتم العثور على فئة الضريبة.',
                'created' => 'تم إنشاء فئة الضريبة بنجاح.',
                'updated' => 'تم تحديث فئة الضريبة بنجاح.',
                'deleted' => 'تم حذف فئة الضريبة بنجاح.',
                'code-unique' => 'الرمز مستخدم بالفعل.',
                'cannot-delete-in-use' => 'لا تزال فئة الضريبة هذه مرتبطة بمعدلات ضريبة ولا يمكن حذفها.',
                'delete-failed' => 'تعذر حذف فئة الضريبة.',
                'no-permission' => 'ليس لديك إذن لإدارة فئات الضريبة.',
            ],

            'role' => [
                'not-found' => 'لم يتم العثور على الدور.',
                'created' => 'تم إنشاء الدور بنجاح.',
                'updated' => 'تم تحديث الدور بنجاح.',
                'deleted' => 'تم حذف الدور بنجاح.',
                'permissions-required' => 'الحقل permissions مطلوب عندما تكون قيمة permission_type هي custom.',
                'cannot-delete-in-use' => 'هذا الدور مُسند إلى مسؤول واحد أو أكثر ولا يمكن حذفه.',
                'cannot-delete-last-role' => 'مطلوب دور واحد على الأقل — لا يمكن حذف آخر دور متبقٍ.',
                'cannot-change-last-all-access' => 'لا يمكن تغيير الدور الوحيد ذي الصلاحيات الكاملة إلى دور مخصص.',
                'delete-failed' => 'تعذر حذف الدور.',
                'no-permission' => 'ليس لديك صلاحية لإدارة الأدوار.',
            ],

            'currency' => [
                // Read
                'not-found' => 'لم يتم العثور على العملة.',

                // Create / Update / Delete
                'created' => 'تم إنشاء العملة بنجاح.',
                'updated' => 'تم تحديث العملة بنجاح.',
                'deleted' => 'تم حذف العملة بنجاح.',
                'code-required' => 'الحقل code مطلوب.',
                'code-size' => 'يجب أن يتكون الرمز من 3 أحرف أبجدية بالضبط.',
                'code-unique' => 'الرمز مستخدم بالفعل.',
                'name-required' => 'الحقل name مطلوب.',
                'cannot-delete-last' => 'مطلوب عملة واحدة على الأقل — لا يمكن حذف آخر عملة متبقية.',
                'cannot-delete-channel-base' => 'هذه العملة هي العملة الأساسية لقناة أو أكثر ولا يمكن حذفها.',
                'delete-failed' => 'تعذر حذف العملة.',
                'no-permission' => 'ليس لديك إذن لإدارة العملات.',

                'mass-delete-success' => 'تم حذف العملات بنجاح.',
                'mass-delete-indices-required' => 'الحقل indices مطلوب ويجب أن يكون مصفوفة غير فارغة.',
            ],

            'channel' => [
                'not-found' => 'لم يتم العثور على القناة.',

                'created' => 'تم إنشاء القناة بنجاح.',
                'updated' => 'تم تحديث القناة بنجاح.',
                'deleted' => 'تم حذف القناة بنجاح.',
                'code-unique' => 'الرمز مستخدم بالفعل.',
                'hostname-unique' => 'اسم المضيف مستخدم بالفعل.',
                'default-locale-mismatch' => 'يجب أن يظهر default_locale_id في قائمة اللغات.',
                'base-currency-mismatch' => 'يجب أن يظهر base_currency_id في قائمة العملات.',
                'unknown-locale' => 'معرف لغة واحد أو أكثر غير موجود.',
                'unknown-currency' => 'معرف عملة واحد أو أكثر غير موجود.',
                'unknown-inventory-source' => 'معرف inventory_source واحد أو أكثر غير موجود.',
                'unknown-root-category' => 'root_category_id غير موجود.',
                'cannot-delete-last' => 'مطلوب قناة واحدة على الأقل — لا يمكن حذف آخر قناة متبقية.',
                'cannot-delete-default' => 'لا يمكن حذف القناة الافتراضية للتطبيق.',
                'delete-failed' => 'تعذر حذف القناة.',
                'no-permission' => 'ليس لديك إذن لإدارة القنوات.',
            ],

            'user' => [
                'not-found' => 'لم يتم العثور على المستخدم المسؤول.',
                'created' => 'تم إنشاء المستخدم المسؤول بنجاح.',
                'updated' => 'تم تحديث المستخدم المسؤول بنجاح.',
                'deleted' => 'تم حذف المستخدم المسؤول بنجاح.',
                'name-required' => 'الحقل name مطلوب.',
                'email-required' => 'الحقل email مطلوب.',
                'email-invalid' => 'يجب أن يكون البريد الإلكتروني عنوان بريد إلكتروني صالحًا.',
                'email-unique' => 'البريد الإلكتروني مستخدم بالفعل.',
                'password-required' => 'الحقل password مطلوب.',
                'password-min' => 'يجب أن تتكون كلمة المرور من 6 أحرف على الأقل.',
                'role-required' => 'الحقل role_id مطلوب.',
                'role-not-found' => 'الدور المحدد غير موجود.',
                'status-invalid' => 'يجب أن تكون الحالة 0 أو 1.',
                'cannot-delete-self' => 'لا يمكنك حذف حساب المسؤول الخاص بك.',
                'cannot-delete-last-admin' => 'مطلوب مستخدم مسؤول واحد على الأقل — لا يمكن حذف آخر مسؤول متبقٍ.',
                'cannot-change' => 'لا يمكن تغيير :name لآخر مسؤول ذي صلاحيات كاملة.',
                'delete-failed' => 'تعذر حذف المستخدم المسؤول.',
                'no-permission' => 'ليس لديك صلاحية لإدارة المستخدمين المسؤولين.',
                'self-delete-password-required' => 'الحقل password مطلوب لحذف حسابك.',
                'self-delete-incorrect-password' => 'كلمة المرور غير صحيحة.',
                'self-deleted' => 'تم حذف حساب المسؤول الخاص بك.',
            ],

            'data-transfer' => [
                'import' => [
                    'not-found' => 'لم يتم العثور على عملية الاستيراد.',
                    'deleted' => 'تم حذف عملية الاستيراد بنجاح.',
                    'cancelled' => 'تم إلغاء عملية الاستيراد بنجاح.',
                    'cannot-cancel' => 'يمكن إلغاء عمليات الاستيراد المعلقة أو قيد المعالجة فقط. الحالة الحالية: :state.',
                    'no-permission' => 'ليس لديك إذن لإدارة عمليات استيراد نقل البيانات.',
                    'delete-failed' => 'تعذر حذف عملية الاستيراد.',
                    'created' => 'تم إنشاء عملية الاستيراد بنجاح.',
                    'updated' => 'تم تحديث عملية الاستيراد بنجاح.',
                    'type-invalid' => 'النوع المحدد غير صالح.',
                    'action-invalid' => 'يجب أن يكون الإجراء واحدًا من: append, delete.',
                    'validation-strategy-invalid' => 'يجب أن تكون استراتيجية التحقق واحدة من: stop-on-errors, skip-errors.',
                    'allowed-errors-invalid' => 'يجب أن يكون عدد الأخطاء المسموح بها عددًا صحيحًا يساوي 0 أو أكثر.',
                    'field-separator-required' => 'فاصل الحقول مطلوب.',
                    'file-required' => 'الملف مطلوب.',
                    'file-invalid-type' => 'يجب أن يكون الملف واحدًا من: csv, xls, xlsx, xml.',
                    'nothing-to-import' => 'لا يوجد ما يمكن استيراده.',
                    'not-valid' => 'عملية الاستيراد غير صالحة.',
                    'setup-queue-error' => 'تم ضبط اتصال قائمة الانتظار على sync؛ قم بتهيئة قائمة انتظار حقيقية لمعالجة عمليات الاستيراد في الخلفية.',
                    'file-not-found' => 'لا يوجد ملف متاح للتنزيل.',
                    'error-report-not-found' => 'لا يوجد تقرير أخطاء متاح للتنزيل.',
                    'sample-not-found' => 'لا يوجد ملف نموذجي متاح للنوع والتنسيق المطلوبين.',
                    'sample-format-invalid' => 'يجب أن يكون تنسيق النموذج واحدًا من: csv, xls, xlsx, xml.',
                    'images-archive-too-large' => 'يمكن أن يحتوي أرشيف الصور على :entries ملف كحد أقصى وأن يبلغ حجمه بعد فك الضغط :size ميغابايت كحد أقصى.',
                ],
            ],
        ],

        'cms' => [
            'page' => [
                'not-found' => 'لم يتم العثور على صفحة CMS.',

                'created' => 'تم إنشاء صفحة CMS بنجاح.',
                'url-key-required' => 'الحقل url_key مطلوب.',
                'url-key-unique' => 'قيمة url_key مستخدمة بالفعل.',
                'url-key-invalid' => 'تنسيق url_key غير صالح. استخدم الأحرف الصغيرة والأرقام والشرطات فقط.',
                'page-title-required' => 'الحقل page_title مطلوب.',
                'html-content-required' => 'الحقل html_content مطلوب.',
                'channels-required' => 'يجب اختيار قناة واحدة على الأقل.',
                'channels-invalid' => 'قناة أو أكثر غير صالحة.',

                'updated' => 'تم تحديث صفحة CMS بنجاح.',

                'deleted' => 'تم حذف صفحة CMS بنجاح.',
                'delete-failed' => 'تعذر حذف صفحة CMS.',

                'mass-deleted' => 'تم حذف صفحات CMS بنجاح.',
                'indices-required' => 'الحقل indices مطلوب ويجب أن يكون مصفوفة غير فارغة.',
                'indices-invalid' => 'يجب أن يكون الحقل indices مصفوفة من معرفات صفحات CMS.',

                'no-permission' => 'ليس لديك إذن لإدارة صفحات CMS.',
            ],
        ],

        'marketing' => [
            'catalog-rule' => [
                'not-found' => 'لم يتم العثور على قاعدة الكتالوج.',
                'created' => 'تم إنشاء قاعدة الكتالوج بنجاح.',
                'updated' => 'تم تحديث قاعدة الكتالوج بنجاح.',
                'deleted' => 'تم حذف قاعدة الكتالوج بنجاح.',
                'delete-failed' => 'تعذر حذف قاعدة الكتالوج.',
                'mass-delete-indices-required' => 'لم يتم تقديم معرفات قواعد الكتالوج للحذف الجماعي.',
                'mass-delete-success' => 'تم حذف قواعد الكتالوج المحددة بنجاح.',
                'no-permission' => 'ليس لديك إذن لإدارة قواعد الكتالوج.',
            ],

            'campaign' => [
                'not-found' => 'لم يتم العثور على الحملة.',
                'created' => 'تم إنشاء الحملة بنجاح.',
                'updated' => 'تم تحديث الحملة بنجاح.',
                'deleted' => 'تم حذف الحملة بنجاح.',
                'delete-failed' => 'تعذر حذف الحملة.',
                'no-permission' => 'ليس لديك إذن لإدارة الحملات.',

                'send' => [
                    'id-required' => 'معرف الحملة مطلوب.',
                    'inactive' => 'لا يمكن إرسال حملة غير نشطة. قم بتفعيلها أولًا.',
                    'queued' => 'تمت إضافة الحملة إلى قائمة الانتظار لـ :count مستلم (مستلمين).',
                ],
            ],

            'sitemap' => [
                'not-found' => 'لم يتم العثور على خريطة الموقع.',
                'created' => 'تم إنشاء خريطة الموقع بنجاح.',
                'updated' => 'تم تحديث خريطة الموقع بنجاح.',
                'deleted' => 'تم حذف خريطة الموقع بنجاح.',
                'delete-failed' => 'تعذر حذف خريطة الموقع.',
                'no-permission' => 'ليس لديك إذن لإدارة خرائط الموقع.',

                'generate' => [
                    'id-required' => 'معرف خريطة الموقع مطلوب.',
                    'no-channels' => 'لا تغطي خريطة الموقع هذه أي قناة، لذا لا يوجد ما يمكن إنشاؤه. قم بتعيين قناة واحدة على الأقل لها أولًا.',
                    'success' => 'تمت إعادة إنشاء خريطة الموقع بنجاح.',
                    'failed' => 'فشل إنشاء خريطة الموقع: :message',
                ],
            ],

            'cart-rule' => [
                'not-found' => 'لم يتم العثور على قاعدة السلة.',
                'id-required' => 'معرف قاعدة السلة مطلوب.',
                'copy-of' => 'نسخة من :value',
                'copied' => 'تم نسخ قاعدة السلة بنجاح.',
                'created' => 'تم إنشاء قاعدة السلة بنجاح.',
                'updated' => 'تم تحديث قاعدة السلة بنجاح.',
                'deleted' => 'تم حذف قاعدة السلة بنجاح.',
                'delete-failed' => 'تعذر حذف قاعدة السلة.',
                'no-permission' => 'ليس لديك إذن لإدارة قواعد السلة.',
                'mass-delete-success' => 'تم حذف قواعد السلة بنجاح.',
                'mass-delete-indices-required' => 'الحقل indices مطلوب ويجب أن يكون مصفوفة غير فارغة.',
                'action-type-invalid' => 'يجب أن يكون action_type واحدًا من: by_percent, by_fixed, cart_fixed, buy_x_get_y.',
                'coupon-code-required' => 'coupon_code مطلوب عندما يكون coupon_type=1 و use_auto_generation=0.',
                'coupon-code-unique' => 'رمز القسيمة هذا مستخدم بالفعل.',
            ],

            'template' => [
                'campaign-associated' => 'قالب البريد الإلكتروني هذا مستخدم في حملة، لذا لا يمكن حذفه. أزله من الحملة أولًا.',
                'not-found' => 'لم يتم العثور على قالب البريد الإلكتروني.',
                'created' => 'تم إنشاء قالب البريد الإلكتروني بنجاح.',
                'updated' => 'تم تحديث قالب البريد الإلكتروني بنجاح.',
                'deleted' => 'تم حذف قالب البريد الإلكتروني بنجاح.',
                'delete-failed' => 'تعذر حذف قالب البريد الإلكتروني.',
                'status-invalid' => 'يجب أن تكون الحالة واحدة من: active, inactive, draft.',
                'no-permission' => 'ليس لديك إذن لإدارة قوالب البريد الإلكتروني.',
            ],

            'event' => [
                'campaign-associated' => 'هذا الحدث مستخدم في حملة، لذا لا يمكن حذفه. أزله من الحملة أولًا.',
                'not-found' => 'لم يتم العثور على الحدث التسويقي.',
                'created' => 'تم إنشاء الحدث التسويقي بنجاح.',
                'updated' => 'تم تحديث الحدث التسويقي بنجاح.',
                'deleted' => 'تم حذف الحدث التسويقي بنجاح.',
                'delete-failed' => 'تعذر حذف الحدث التسويقي.',
                'no-permission' => 'ليس لديك إذن لإدارة الأحداث التسويقية.',
            ],

            'search-synonym' => [
                'not-found' => 'لم يتم العثور على مرادف البحث.',
                'created' => 'تم إنشاء مرادف البحث بنجاح.',
                'updated' => 'تم تحديث مرادف البحث بنجاح.',
                'deleted' => 'تم حذف مرادف البحث بنجاح.',
                'mass-deleted' => 'تم حذف مرادفات البحث بنجاح.',
                'delete-failed' => 'تعذر حذف مرادف البحث.',
                'indices-required' => 'الحقل indices مطلوب ويجب أن يكون مصفوفة غير فارغة من معرفات مرادفات البحث.',
                'no-permission' => 'ليس لديك إذن لإدارة مرادفات البحث.',
            ],

            'url-rewrite' => [
                'not-found' => 'لم يتم العثور على إعادة كتابة URL.',
                'created' => 'تم إنشاء إعادة كتابة URL بنجاح.',
                'updated' => 'تم تحديث إعادة كتابة URL بنجاح.',
                'deleted' => 'تم حذف إعادة كتابة URL بنجاح.',
                'delete-failed' => 'تعذر حذف إعادة كتابة URL.',
                'mass-deleted' => 'تم حذف عمليات إعادة كتابة URL بنجاح.',
                'indices-required' => 'مطلوب معرف إعادة كتابة URL واحد على الأقل.',
                'no-permission' => 'ليس لديك إذن لإدارة عمليات إعادة كتابة URL.',
            ],

            'cart-rule-coupon' => [
                'cart-rule-not-found' => 'لم يتم العثور على قاعدة السلة.',
                'not-found' => 'لم يتم العثور على القسيمة.',
                'code-taken' => 'توجد بالفعل قسيمة بهذا الرمز.',
                'format-invalid' => 'يجب أن يكون التنسيق واحدًا من: alphabetic, alphanumeric, numeric.',
                'indices-required' => 'الحقل indices مطلوب ويجب أن يكون مصفوفة غير فارغة من معرفات القسائم.',
                'created' => 'تم إنشاء القسيمة بنجاح.',
                'generated' => 'تم إنشاء :count قسيمة (قسائم) بنجاح.',
                'deleted' => 'تم حذف القسيمة بنجاح.',
                'mass-deleted' => 'تم حذف :count قسيمة (قسائم) بنجاح.',
                'no-permission' => 'ليس لديك إذن لإدارة قسائم قواعد السلة.',
            ],

            'subscriber' => [
                'not-found' => 'لم يتم العثور على مشترك النشرة الإخبارية.',
                'updated' => 'تم تحديث الاشتراك بنجاح.',
                'deleted' => 'تم حذف الاشتراك بنجاح.',
                'is-subscribed-required' => 'الحقل is_subscribed مطلوب.',
                'no-permission' => 'ليس لديك إذن لإدارة مشتركي النشرة الإخبارية.',
            ],

            'search-term' => [
                'not-found' => 'لم يتم العثور على مصطلح البحث.',
                'updated' => 'تم تحديث مصطلح البحث بنجاح.',
                'deleted' => 'تم حذف مصطلح البحث بنجاح.',
                'mass-deleted' => 'تم حذف مصطلحات البحث بنجاح.',
                'term-required' => 'الحقل term مطلوب.',
                'redirect-url-invalid' => 'يجب أن يكون redirect_url عنوان URL صالحًا.',
                'indices-required' => 'يجب تقديم معرف واحد على الأقل في indices.',
                'no-permission' => 'ليس لديك إذن لإدارة مصطلحات البحث.',
            ],
        ],
        'auth' => [
            'configuration' => [
                'title' => 'مصادقة المسؤول',
                'info' => 'مدد صلاحية الرموز وحدود المعدل وقواعد الأمان لتسجيل دخول المسؤول عبر API ورموز API.',

                'tokens' => [
                    'title' => 'مدد صلاحية الرموز',
                    'info' => 'مدة بقاء رموز الوصول ورموز التحديث والجلسات صالحة. تُطبَّق التغييرات على الرموز الصادرة بعدها.',
                    'access-token-ttl' => 'مدة صلاحية رمز الوصول (بالدقائق)',
                    'access-token-ttl-info' => 'بين 5 و60 دقيقة. القيمة الافتراضية 15.',
                    'refresh-token-ttl' => 'مدة صلاحية رمز التحديث (بالأيام)',
                    'refresh-token-ttl-info' => 'بين 1 و90 يومًا. القيمة الافتراضية 30.',
                    'session-lifetime' => 'المدة القصوى للجلسة (بالأيام)',
                    'session-lifetime-info' => 'بين 1 و365 يومًا. القيمة الافتراضية 90. بعدها يجب على المسؤول تسجيل الدخول مجددًا مهما تم تحديث الجلسة.',
                ],

                'rate-limits' => [
                    'title' => 'حدود المعدل',
                    'info' => 'الحد الأقصى لطلبات API المسؤول في الدقيقة.',
                    'session-requests-per-minute' => 'الطلبات في الدقيقة لكل مسؤول مسجَّل الدخول',
                    'api-token-requests-per-minute' => 'الطلبات في الدقيقة لكل رمز API',
                ],

                'security' => [
                    'title' => 'الأمان',
                    'info' => 'قواعد الإلغاء وانتهاء صلاحية رموز API وربط النطاق.',
                    'revoke-sessions-on-password-change' => 'إلغاء الجلسات عند تغيير كلمة المرور',
                    'revoke-api-tokens-on-password-change' => 'إلغاء رموز API عند تغيير كلمة المرور',
                    'allow-non-expiring-api-tokens' => 'السماح برموز API بدون انتهاء صلاحية',
                    'domain-binding-mode' => 'وضع ربط النطاق',
                    'domain-binding-mode-info' => 'يتحقق DNS من أن الطلب صادر من عنوان IP يشير إليه نطاق الرمز. يتحقق Origin من ترويسة Origin أو Referer ويناسب المتصفحات فقط.',
                    'domain-binding-dns' => 'DNS (عنوان IP للعميل)',
                    'domain-binding-origin' => 'ترويسة Origin',
                ],
            ],

            'errors' => [
                'unauthenticated' => 'أنت غير مصادق.',
                'access-token-expired' => 'انتهت صلاحية رمز الوصول. قم بتحديثه للمتابعة.',
                'api-token-expired' => 'انتهت صلاحية رمز API.',
                'refresh-token-invalid' => 'رمز التحديث غير صالح أو منتهي الصلاحية. يرجى تسجيل الدخول مجددًا.',
                'api-token-revoked' => 'تم إلغاء رمز API.',
                'api-token-domain-mismatch' => 'لا يمكن استخدام رمز API من هذا النطاق.',
                'access-token-required' => 'يتطلب هذا الإجراء رمز الوصول الخاص بجلسة تسجيل دخول؛ ولا يمكن لرموز API استخدامه.',
                'two-factor-required' => 'المصادقة الثنائية مفعّلة لهذا الحساب، وتسجيل الدخول عبر API بعامل ثانٍ غير متاح بعد.',
                'too-many-login-attempts' => 'محاولات تسجيل دخول كثيرة جدًا. يرجى المحاولة مرة أخرى بعد :seconds ثانية.',
                'rate-limit-exceeded' => 'محاولات كثيرة جدًا.',
                'no-permission' => 'ليست لديك صلاحية لإدارة جلسات API للمسؤولين.',
            ],

            'sessions' => [
                'logged-out' => 'تم تسجيل خروجك.',
                'logged-out-all' => 'تم تسجيل خروجك من جميع الجلسات.',
                'not-found' => 'الجلسة غير موجودة.',
                'revoked' => 'تم إلغاء الجلسة.',
                'revoked-for-admin' => 'الجلسات الملغاة: :count.',
            ],

            'acl' => [
                'api-sessions' => 'جلسات API',
                'revoke' => 'إلغاء الجلسات',
            ],
        ],
    ],

    'integration' => [
        'menu' => [
            'title' => 'التكامل',
            'tokens' => 'الرموز',
        ],

        'history' => [
            'menu' => [
                'title' => 'السجل',
            ],

            'acl' => [
                'title' => 'سجل تغييرات واجهة برمجة التطبيقات',
                'view' => 'عرض',
                'delete' => 'حذف السجل',
            ],

            'index' => [
                'title' => 'سجل تغييرات واجهة برمجة التطبيقات',
                'info' => 'كل عملية إنشاء وتحديث وحذف تتم عبر واجهة برمجة تطبيقات المسؤول، مع من قام بها، وأي رمز، وما الذي تغير.',
                'cleanup-btn' => 'حذف السجلات القديمة',
                'cleanup-days' => 'حذف السجلات الأقدم من هذه الأيام',
                'cleanup-confirm' => 'هل تريد حذف جميع السجلات الأقدم من عدد الأيام المحدد؟ لا يمكن التراجع عن هذا الإجراء.',
            ],

            'view' => [
                'title' => 'تغيير',
                'back-btn' => 'رجوع',
                'admin' => 'المسؤول',
                'token' => 'الرمز',
                'action' => 'الإجراء',
                'resource' => 'المورد',
                'method' => 'الطريقة',
                'ip' => 'عنوان IP',
                'date' => 'التاريخ',
                'version' => 'الإصدار',
                'url' => 'نقطة النهاية',
                'request-details' => 'تفاصيل الطلب',
                'changes' => 'التغييرات',
                'field' => 'الحقل',
                'old' => 'القيمة القديمة',
                'new' => 'القيمة الجديدة',
                'no-field-changes' => 'لم يتم تسجيل تغييرات على مستوى الحقل لهذا الإدخال.',
                'same-request' => 'تغييرات أخرى في نفس الطلب',
                'version-chain' => 'سجل إصدارات هذا السجل',
            ],

            'datagrid' => [
                'id' => 'المعرف',
                'date' => 'التاريخ',
                'admin' => 'المسؤول',
                'token' => 'الرمز',
                'action' => 'الإجراء',
                'operation' => 'العملية',
                'resource' => 'المورد',
                'version' => 'الإصدار',
                'method' => 'الطريقة',
                'ip' => 'IP',
                'view' => 'عرض',
                'delete' => 'حذف',
            ],

            'events' => [
                'created' => 'تم الإنشاء',
                'updated' => 'تم التحديث',
                'deleted' => 'تم الحذف',
            ],

            'deleted' => 'تم حذف :count سجل(سجلات).',
            'cleanup-input-required' => 'أدخل عدد الأيام أو تاريخاً للتنظيف.',
        ],

        'acl' => [
            'title' => 'التكامل',
            'view' => 'عرض',
            'create' => 'إنشاء تكامل',
            'edit' => 'تعديل التكامل',
            'delete' => 'إبطال رمز التكامل',
            'generate' => 'إنشاء رمز التكامل',
            'regenerate' => 'إعادة إنشاء رمز التكامل',
        ],

        'index' => [
            'title' => 'التكاملات',
            'create-btn' => 'إنشاء تكامل',
        ],

        'create' => [
            'title' => 'إنشاء تكامل',
            'save-btn' => 'حفظ',
            'back-btn' => 'رجوع',
        ],

        'edit' => [
            'title' => 'تعديل التكامل',
            'save-btn' => 'حفظ',
            'back-btn' => 'رجوع',
            'generate-btn' => 'إنشاء الرمز',
            'regenerate-btn' => 'إعادة إنشاء الرمز',
            'revoke-btn' => 'إبطال الرمز',
            'copy-btn' => 'نسخ',
            'token-warning' => 'احفظ هذا الرمز الآن — لن يتم عرضه مرة أخرى.',
            'token-label' => 'الرمز',
            'not-generated' => 'لم يتم إنشاؤه بعد',
            'masked' => '(محفوظ — يظهر مرة واحدة فقط عند الإنشاء)',
            'history-banner' => 'هذا الرمز لم يعد نشطاً.',
        ],

        'fields' => [
            'name' => 'الاسم',
            'description' => 'الوصف',
            'assign-user' => 'تعيين المستخدم',
            'permission-type' => 'نوع الإذن',
            'access-control' => 'التحكم في الوصول',
            'general' => 'عام',
            'token-settings' => 'إعدادات الرمز',
            'valid-till' => 'صالح حتى',
            'rate-limit-per-minute' => 'حد المعدل (في الدقيقة)',
            'rate-limit-per-day' => 'حد المعدل (في اليوم)',
            'never-expires' => 'لا تنتهي صلاحيته',
            'expires-on' => 'ينتهي في',
            'unlimited' => 'غير محدود',
            'limit-to' => 'مقتصر على',
            'requests-per-minute' => 'طلبات / دقيقة',
            'requests-per-day' => 'طلبات / يوم',
            'select-admin' => 'حدد مسؤولاً',
            'no-available-admins' => 'لا يوجد مسؤولون متاحون — كل مسؤول لديه رمز نشط بالفعل.',
            'same-as-web-hint' => 'سيعكس الرمز أذونات دور المسؤول المعين بشكل مباشر.',
            'ip-allowlist' => 'القائمة المسموح بها لعناوين IP',
            'ip-any' => 'أي عنوان IP (افتراضي)',
            'ip-restricted' => 'مقتصر على عناوين IP محددة',
            'ip-list-hint' => 'إدخال واحد لكل سطر. يدعم IPv4 و IPv6 و CIDR (مثل 10.0.0.0/24 أو 2001:db8::/32). اتركه فارغاً للسماح لجميع عناوين IP.',
        ],

        'permission-type' => [
            'all' => 'الكل',
            'custom' => 'مخصص',
            'same-as-web' => 'نفس أذونات الويب',
        ],

        'status' => [
            'draft' => 'مسودة',
            'active' => 'نشط',
            'revoked' => 'تم الإبطال',
            'regenerated' => 'تمت إعادة الإنشاء',
        ],

        'datagrid' => [
            'id' => 'المعرف',
            'name' => 'الاسم',
            'admin' => 'المسؤول',
            'token' => 'الرمز',
            'status' => 'الحالة',
            'permission-type' => 'نوع الإذن',
            'expires-at' => 'صالح حتى',
            'last-used-at' => 'آخر استخدام',
            'created-at' => 'تاريخ الإنشاء',
            'edit' => 'تعديل',
            'revoke' => 'إبطال',
        ],

        'messages' => [
            'draft-created' => 'تم إنشاء التكامل. قم بإنشاء الرمز للبدء في استخدامه.',
            'updated' => 'تم تحديث التكامل بنجاح.',
            'generated' => 'تم إنشاء الرمز. انسخه الآن — لن يتم عرضه مرة أخرى.',
            'regenerated' => 'تمت إعادة إنشاء الرمز. انسخ الرمز الجديد الآن — لن يتم عرضه مرة أخرى.',
            'revoked' => 'تم إبطال الرمز بنجاح.',
            'generate-only-draft' => 'فقط التكاملات في حالة مسودة يمكن إنشاء رمز لها.',
            'regenerate-only-active' => 'فقط الرموز النشطة يمكن إعادة إنشائها.',
            'cannot-edit-historic' => 'لا يمكن تعديل الرموز المبطلة أو المعاد إنشاؤها.',
            'already-inactive' => 'هذا الرمز غير نشط بالفعل.',
        ],

        'errors' => [
            'admin-has-token' => 'المسؤول المحدد لديه بالفعل رمز تكامل نشط.',
            'only-draft-generate' => 'يمكن إنشاء الرموز المسودة فقط.',
        ],

        'validation' => [
            'ip-invalid' => 'يجب أن يكون كل عنوان IP مسموح به عنوان IPv4 أو IPv6 صالحاً (يدعم ترميز CIDR).',
            'cidr-prefix-invalid' => 'بادئة CIDR غير صالحة لإصدار IP المحدد.',
        ],

        'configuration' => [
            'api' => [
                'title' => 'واجهة برمجة التطبيقات',
                'info' => 'إعدادات واجهة برمجة تطبيقات Bagisto ووحدات المسؤول الخاصة بها.',
            ],

            'integration' => [
                'title' => 'التكامل',
                'info' => 'إدارة إضافة تكامل واجهة برمجة التطبيقات المستخدمة لإصدار رموز واجهة برمجة تطبيقات المسؤول.',
            ],

            'settings' => [
                'title' => 'إعدادات الوحدة',
                'info' => 'تمكين أو تعطيل إضافة تكامل واجهة برمجة التطبيقات. عند التعطيل، يتم إخفاء قائمة الشريط الجانبي الخاصة بها وتعيد صفحاتها الخطأ 404.',
                'enable' => 'تمكين وحدة تكامل واجهة برمجة التطبيقات',
            ],
        ],

        'emails' => [
            'generated' => [
                'subject' => 'تم إنشاء رمز جديد لواجهة برمجة التطبيقات: :name',
                'greeting' => 'تم للتو إنشاء رمز تكامل لواجهة برمجة التطبيقات باسم ":name" في حسابك.',
            ],

            'regenerated' => [
                'subject' => 'تمت إعادة إنشاء رمز واجهة برمجة التطبيقات الخاص بك: :name',
                'greeting' => 'تمت للتو إعادة إنشاء رمز تكامل واجهة برمجة التطبيقات باسم ":name". توقف الرمز السابق عن العمل — الرمز الجديد فقط هو الصالح.',
            ],

            'revoked' => [
                'subject' => 'تم إبطال رمز واجهة برمجة التطبيقات الخاص بك: :name',
                'greeting' => 'تم إبطال رمز تكامل واجهة برمجة التطبيقات باسم ":name". فقد أي عميل يستخدمه حق الوصول.',
            ],

            'details' => [
                'name' => 'اسم الرمز',
                'date' => 'التاريخ',
                'ip' => 'من عنوان IP',
            ],

            'revoke-hint' => 'إذا لم تكن تتوقع ذلك، فقم بإبطال الرمز فوراً باستخدام الزر أدناه.',
            'revoke-btn' => 'إبطال هذا الرمز',
            'revoke-expiry' => 'رابط الإبطال هذا صالح لمدة 7 أيام. بعد ذلك، قم بتسجيل الدخول إلى لوحة الإدارة لإدارة الرمز.',
            'no-action' => 'لا يلزم اتخاذ أي إجراء — هذه الرسالة الإلكترونية هي للتأكيد فقط.',
        ],

        'revoke-confirmation' => [
            'title' => 'إبطال رمز واجهة برمجة التطبيقات',
            'confirm-title' => 'هل تريد إبطال هذا الرمز؟',
            'confirm-message' => 'سيتوقف الرمز ":name" عن العمل فورًا لكل عميل يستخدمه.',
            'confirm-button' => 'إبطال الرمز',
            'success-title' => 'تم إبطال الرمز',
            'success-message' => 'تم إبطال الرمز ":name". فقد أي عميل يستخدمه حق الوصول فوراً.',
            'already-inactive-title' => 'الرمز غير نشط بالفعل',
            'already-inactive-message' => 'تم بالفعل إبطال الرمز ":name" أو إعادة إنشائه. لا يلزم اتخاذ أي إجراء إضافي.',
        ],

        'confirm' => [
            'generate' => [
                'title' => 'إنشاء الرمز',
                'message' => 'إنشاء الرمز الآن؟ سيتم عرض النص العادي مرة واحدة فقط — انسخه قبل مغادرة الصفحة.',
            ],

            'regenerate' => [
                'title' => 'إعادة إنشاء الرمز',
                'message' => 'إعادة إنشاء الرمز؟ سيتوقف الرمز القديم عن العمل فوراً وسيتم عرض النص العادي الجديد مرة واحدة فقط.',
            ],

            'revoke' => [
                'title' => 'إبطال الرمز',
                'message' => 'إبطال هذا الرمز؟ سيفقد أي عميل يستخدمه حق الوصول فوراً. لا يمكن التراجع عن هذا الإجراء.',
            ],
        ],
    ],
];
