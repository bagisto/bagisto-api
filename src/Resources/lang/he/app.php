<?php

return [
    'graphql' => [
        'cart' => [
            'authentication-required'           => 'נדרש אסימון אימות',
            'invalid-token'                     => 'אסימון אימות לא תקף או שפג תוקפו',
            'unauthorized-access'               => 'גישה לא מורשית לעגלה',
            'authenticated-only'                => 'רק משתמשים מאומתים יכולים לאחזר את העגלות שלהם',
            'merge-requires-auth'               => 'מיזוג עגלת אורח דורש אימות',
            'unknown-operation'                 => 'פעולת עגלה לא ידועה',

            'cart-not-found'                    => 'העגלה לא נמצאה',
            'guest-cart-not-found'              => 'עגלת האורח לא נמצאה',
            'product-not-found'                 => 'המוצר לא נמצא',

            'product-id-quantity-required'      => 'מזהה המוצר והכמות נדרשים',
            'cart-item-id-quantity-required'    => 'מזהה פריט העגלה והכמות נדרשים',
            'cart-item-id-required'             => 'מזהה פריט העגלה נדרש',
            'item-ids-required'                 => 'נדרש מערך של מזהי פריטים',
            'coupon-code-required'              => 'קוד הקופון נדרש',
            'address-data-required'             => 'מדינה, מחוז ומיקוד נדרשים',

            'add-product-failed'                => 'הוספת המוצר לעגלה נכשלה',
            'update-item-failed'                => 'עדכון פריט העגלה נכשל',
            'remove-item-failed'                => 'הסרת פריט העגלה נכשלה',
            'apply-coupon-failed'               => 'החלת הקופון נכשלה',
            'remove-coupon-failed'              => 'הסרת הקופון נכשלה',
            'move-to-wishlist-failed'           => 'העברת הפריט לרשימת המשאלות נכשלה',
            'estimate-shipping-failed'          => 'הערכת המשלוח נכשלה',

            'product-added-successfully'         => 'המוצר נוסף לעגלה בהצלחה',
            'guest-cart-merged'                  => 'עגלת האורח מוזגה בהצלחה',
            'using-authenticated-cart'           => 'שימוש בעגלת הלקוח המאומת',
            'cart-item-not-found'                => 'פריט העגלה לא נמצא',
            'new-guest-cart-created'             => 'עגלת אורח חדשה נוצרה עם אסימון הפעלה ייחודי',
            'select-items-to-remove'             => 'אנא בחר פריטים להסרה',
            'select-items-to-move-wishlist'      => 'אנא בחר פריטים להעברה לרשימת המשאלות',
            'invalid-or-expired-token'           => 'אסימון העגלה אינו תקף או שפג תוקפו. אנא צור עגלה חדשה.',
            'invalid-token-of-login-user'        => 'אסימון המשתמש המחובר אינו תקף.',
        ],

        'token-verification' => [
            'invalid-operation'                 => 'פעולה לא חוקית',
            'invalid-input-data'                => 'נתוני קלט לא חוקיים',
            'token-required'                    => 'האסימון נדרש',
            'invalid-token-format'              => 'פורמט אסימון לא חוקי',
            'token-not-found-or-expired'        => 'האסימון לא נמצא או שפג תוקפו',
            'customer-not-found'                => 'הלקוח לא נמצא',
            'customer-account-suspended'        => 'חשבון הלקוח מושעה',
            'error-verifying-token'             => 'שגיאה באימות האסימון',
            'token-is-valid'                    => 'האסימון תקף',
        ],

        'forgot-password' => [
            'invalid-operation'                 => 'פעולה לא חוקית',
            'invalid-input-data'                => 'נתוני קלט לא חוקיים',
            'email-required'                    => 'כתובת אימייל נדרשת',
            'reset-link-sent'                   => 'קישור לאיפוס נשלח בהצלחה לאימייל שלך',
            'email-not-found'                   => 'כתובת האימייל לא נמצאה',
            'error-sending-reset-link'          => 'אירעה שגיאה בעת שליחת קישור האיפוס',
        ],

        'logout' => [
            'invalid-operation'                 => 'פעולה לא חוקית',
            'invalid-input-data'                => 'נתוני קלט לא חוקיים',
            'token-required'                    => 'האסימון נדרש',
            'invalid-token-format'              => 'פורמט אסימון לא חוקי',
            'logged-out-successfully'           => 'התנתקת בהצלחה',
            'token-not-found-or-expired'        => 'האסימון לא נמצא או שכבר פג תוקפו',
            'error-during-logout'               => 'שגיאה במהלך ההתנתקות',
        ],

        'address' => [
            'deleted-successfully'              => 'הכתובת נמחקה בהצלחה',
            'authentication-required'           => 'נדרש אסימון אימות',
            'invalid-token'                     => 'אסימון לא תקף או שפג תוקפו',
            'unknown-operation'                 => 'פעולה לא ידועה',
            'address-id-required'               => 'מזהה הכתובת נדרש',
            'address-not-found'                 => 'הכתובת לא נמצאה או אינה שייכת ללקוח זה',
            'retrieved'                         => 'הכתובות אוחזרו בהצלחה',
            'fetch-failed'                      => 'אחזור הכתובות נכשל:',
        ],

        'customer-profile' => [
            'authentication-required'           => 'נדרש אסימון אימות. אנא ספק את האסימון בקלט השאילתה',
            'invalid-token'                     => 'אסימון לא תקף או שפג תוקפו',
        ],

        'customer' => [
            'password-mismatch'                 => 'הסיסמה ואישור הסיסמה אינם תואמים',
            'confirm-password-required'         => 'אישור סיסמה נדרש בעת שינוי הסיסמה',
            'unauthenticated'                   => 'לא מאומת. אנא התחבר כדי לבצע פעולה זו',
        ],

        'product-review' => [
            'product-id-required'               => 'מזהה המוצר נדרש',
            'product-not-found'                 => 'המוצר לא נמצא',
            'rating-invalid'                    => 'הדירוג חייב להיות בין 1 ל-5',
            'title-required'                    => 'כותרת הביקורת נדרשת',
            'comment-required'                  => 'תגובת הביקורת נדרשת',
        ],

        'product' => [
            'not-found-with-sku'                => 'No product found with SKU',
            'not-found-with-url-key'            => 'No product found with URL key',
            'parameters-required'               => 'At least one of the following parameters must be provided: "sku", "id", "urlKey"',
        ],

        'auth' => [
            'no-token-provided'                 => 'לא סופק אסימון אימות. אנא ספק את האסימון בכותרת Authorization בפורמט "Bearer <token>" או בשדה input.token',
            'invalid-or-expired-token'          => 'אסימון לא תקף או שפג תוקפו',
            'request-not-found'                 => 'הבקשה לא נמצאה בהקשר',
            'token-required'                    => 'Authentication token is required. Please provide the token either in the GraphQL mutation input field or in the Authorization header as "Bearer <token>"',
            'unknown-resource'                  => 'משאב לא ידוע',
            'cannot-update-other-profile'       => 'לא מורשה: לא ניתן לעדכן פרופיל לקוח אחר',
        ],

        'upload' => [
            'invalid-base64'                    => 'נתוני תמונה מקודדים ב-base64 אינם תקינים',
            'size-exceeds-limit'                => 'גודל התמונה לא יכול לעלות על 5MB',
            'invalid-format'                    => 'פורמט תמונה לא תקין. אנא ספק תמונה מקודדת ב-base64 עם סכימת data URI (data:image/jpeg;base64,...)',
            'failed'                            => 'העלאת התמונה נכשלה',
        ],

        'attribute' => [
            'code-already-exists'               => 'קוד המאפיין כבר קיים',
        ],

        'login' => [
            'invalid-credentials'               => 'אימייל או סיסמה שגויים',
            'account-suspended'                 => 'חשבונך הושעה',
            'successful'                        => 'התחברת בהצלחה',
            'invalid-request'                   => 'בקשת התחברות לא חוקית',
        ],

        'checkout' => [
            'invalid-input'                     => 'נתוני קלט לא חוקיים עבור פעולת התשלום',
            'billing-address-required'          => 'כתובת החיוב נדרשת',
            'shipping-address-required'         => 'כתובת המשלוח נדרשת עבור משלוחים',
            'address-save-failed'               => 'שמירת הכתובת נכשלה',
            'address-saved'                     => 'הכתובת נשמרה בהצלחה',
            'shipping-method-required'          => 'שיטת המשלוח נדרשת',
            'invalid-shipping-method'           => 'שיטת משלוח לא תקינה או לא זמינה',
            'shipping-method-save-failed'       => 'שמירת שיטת המשלוח נכשלה',
            'shipping-method-saved'             => 'שיטת המשלוח נשמרה בהצלחה',
            'shipping-method-error'             => 'שגיאה בשמירת שיטת המשלוח',
            'payment-method-required'           => 'שיטת התשלום נדרשת',
            'invalid-payment-method'            => 'שיטת תשלום לא תקינה או לא זמינה',
            'payment-method-save-failed'        => 'שמירת שיטת התשלום נכשלה',
            'payment-method-saved'              => 'שיטת התשלום נשמרה בהצלחה',
            'payment-method-error'              => 'שגיאה בשמירת שיטת התשלום',
            'order-creation-failed'             => 'יצירת ההזמנה נכשלה: מזהה ההזמנה ריק או שההזמנה לא נשמרה',
            'order-retrieval-failed'            => 'אחזור ההזמנה שנוצרה נכשל',
            'order-creation-error'              => 'יצירת ההזמנה נכשלה',
            'cart-empty'                        => 'העגלה ריקה',
            'account-suspended'                 => 'חשבונך הושעה. אנא צור קשר עם התמיכה.',
            'account-inactive'                  => 'חשבונך אינו פעיל. אנא צור קשר עם התמיכה.',
            'minimum-order-not-met'             => 'סכום ההזמנה המינימלי הוא :amount',
            'email-required'                    => 'כתובת אימייל נדרשת ליצירת ההזמנה',
            'unknown-operation'                 => 'פעולת תשלום לא ידועה',
        ],

        'customer-addresses' => [
            'token-required'                    => 'האסימון נדרש לאחזור כתובות הלקוח',
            'invalid-or-expired-token'          => 'אסימון לא תקף או שפג תוקפו',
            'token-validation-failed'           => 'אימות האסימון נכשל',
        ],

        'product' => [
            'type'                              => 'סוג מוצר',
            'attribute-family'                  => 'משפחת מאפיינים',
            'sku'                               => 'מק"ט',
            'name'                              => 'שם',
            'description'                       => 'תיאור',
            'short-description'                 => 'תיאור קצר',
            'status'                            => 'סטטוס',
            'new'                               => 'חדש',
            'featured'                          => 'מומלץ',
            'price'                             => 'מחיר',
            'special-price'                     => 'מחיר מיוחד',
            'weight'                            => 'משקל',
            'cost'                              => 'עלות',
            'length'                            => 'אורך',
            'width'                             => 'רוחב',
            'height'                            => 'גובה',
            'color'                             => 'צבע',
            'size'                              => 'גודל',
            'brand'                             => 'מותג',
            'super-attributes'                  => 'מאפייני-על',
        ],

        'compare-item' => [
            'id-required'                       => 'מזהה פריט השוואה נדרש',
            'invalid-id-format'                 => 'פורמט מזהה לא תקין. פורמט IRI צפוי כמו "/api/shop/compare-items/1" או מזהה מספרי',
            'not-found'                         => 'פריט השוואה לא נמצא',
            'product-id-required'               => 'מזהה המוצר נדרש',
            'customer-id-required'              => 'מזהה הלקוח נדרש',
            'product-not-found'                 => 'המוצר לא נמצא',
            'customer-not-found'                => 'הלקוח לא נמצא',
            'already-exists'                    => 'מוצר זה כבר ברשימת ההשוואה שלך',
        ],

        'downloadable-product' => [
            'download-link-not-found'           => 'קישור הורדה לא נמצא או פג תוקף',
            'purchased-link-not-found'          => 'קישור שנרכש לא נמצא',
            'file-not-found'                    => 'הקובץ לא נמצא',
            'download-successful'               => 'הקובץ מוכן להורדה',
            'token-required'                    => 'נדרש אסימון הורדה',
            'invalid-token'                     => 'אסימון הורדה לא תקף או פג תוקף',
            'token-expired'                     => 'אסימון ההורדה פג תוקף. אנא צור חדש',
            'access-denied'                     => 'הגישה נדחתה: אין לך הרשאה להוריד קובץ זה',
            'redirect-external-url'             => 'ניתוב מחדש לכתובת URL הורדה חיצונית',
            'file-error'                        => 'אירעה שגיאה בעת עיבוד בקשת ההורדה שלך',
            'unauthorized-access'               => 'גישה לא מורשה למשאב הורדה',
        ],
    ],

    'integration' => [
        'menu' => [
            'title'  => 'אינטגרציה',
            'tokens' => 'אסימונים',
        ],

        'history' => [
            'menu' => [
                'title' => 'היסטוריה',
            ],

            'acl' => [
                'title'  => 'היסטוריית שינויים ב-API',
                'delete' => 'מחק היסטוריה',
            ],

            'index' => [
                'title'           => 'היסטוריית שינויים ב-API',
                'info'            => 'כל יצירה, עדכון ומחיקה שנעשו דרך ממשק ה-API של הניהול, עם מי עשה זאת, איזה אסימון ומה השתנה.',
                'cleanup-btn'     => 'מחק יומנים ישנים יותר',
                'cleanup-days'    => 'מחק יומנים ישנים מזה ימים רבים',
                'cleanup-confirm' => 'למחוק את כל ההיסטוריה הישנה ממספר הימים הנתון? לא ניתן לבטל זאת.',
            ],

            'view' => [
                'title'             => 'שנה',
                'back-btn'          => 'חזרה',
                'admin'             => 'מנהל מערכת',
                'token'             => 'אסימון',
                'action'            => 'פעולה',
                'resource'          => 'משאב',
                'method'            => 'שיטה',
                'ip'                => 'כתובת IP',
                'date'              => 'תאריך',
                'version'           => 'גרסה',
                'url'               => 'נקודת קצה',
                'request-details'   => 'בקש פרטים',
                'changes'           => 'שינויים',
                'field'             => 'שדה',
                'old'               => 'ערך ישן',
                'new'               => 'ערך חדש',
                'no-field-changes'  => 'לא נרשמו שינויים ברמת השדה עבור ערך זה.',
                'same-request'      => 'שינויים נוספים באותה בקשה',
                'version-chain'     => 'היסטוריית גרסאות של רשומה זו',
            ],

            'datagrid' => [
                'id'        => 'תעודה מזהה',
                'date'      => 'תאריך',
                'admin'     => 'מנהל מערכת',
                'token'     => 'אסימון',
                'action'    => 'פעולה',
                'operation' => 'מבצע',
                'resource'  => 'משאב',
                'version'   => 'גרסה',
                'method'    => 'שיטה',
                'ip'        => 'IP',
                'view'      => 'הצג',
                'delete'    => 'מחק',
            ],

            'events' => [
                'created' => 'נוצר',
                'updated' => 'עודכן',
                'deleted' => 'נמחק',
            ],

            'deleted'                => ':count רשומות היסטוריה נמחקו.',
            'cleanup-input-required' => 'ציין מספר ימים או תאריך לניקוי.',
        ],

        'acl' => [
            'title'      => 'אינטגרציה',
            'create'     => 'צור אינטגרציה',
            'edit'       => 'ערוך אינטגרציה',
            'delete'     => 'בטל אסימון אינטגרציה',
            'generate'   => 'צור אסימון אינטגרציה',
            'regenerate' => 'אסימון אינטגרציה מחדש',
        ],

        'index' => [
            'title'      => 'אינטגרציות',
            'create-btn' => 'צור אינטגרציה',
        ],

        'create' => [
            'title'    => 'צור אינטגרציה',
            'save-btn' => 'שמור',
            'back-btn' => 'חזרה',
        ],

        'edit' => [
            'title'          => 'ערוך אינטגרציה',
            'save-btn'       => 'שמור',
            'back-btn'       => 'חזרה',
            'generate-btn'   => 'צור אסימון',
            'regenerate-btn' => 'צור מחדש את האסימון',
            'revoke-btn'     => 'בטל אסימון',
            'copy-btn'       => 'העתק',
            'token-warning'  => 'שמור את האסימון הזה עכשיו - הוא לא יוצג שוב.',
            'token-label'    => 'אסימון',
            'not-generated'  => 'עדיין לא נוצר',
            'masked'         => '(מאוחסן - מוצג רק פעם אחת בדור)',
            'history-banner' => 'אסימון זה אינו פעיל יותר.',
        ],

        'fields' => [
            'name'                  => 'שם',
            'description'           => 'תיאור',
            'assign-user'           => 'הקצה משתמש',
            'permission-type'       => 'סוג הרשאה',
            'access-control'        => 'בקרת גישה',
            'general'               => 'כללי',
            'token-settings'        => 'הגדרות אסימון',
            'valid-till'            => 'תקף עד',
            'rate-limit-per-minute' => 'מגבלת קצב (לדקה)',
            'rate-limit-per-day'    => 'מגבלת תעריף (ליום)',
            'never-expires'         => 'לעולם לא יפוג',
            'expires-on'            => 'יפוג בתאריך',
            'unlimited'             => 'ללא הגבלה',
            'limit-to'              => 'הגבל ל',
            'requests-per-minute'   => 'בקשות / דקה',
            'requests-per-day'      => 'בקשות / יום',
            'select-admin'          => 'בחר מנהל מערכת',
            'no-available-admins'   => 'אין מנהלי מערכת זמינים - לכל מנהל כבר יש אסימון פעיל.',
            'same-as-web-hint'      => 'האסימון ישקף את הרשאות התפקיד הנוכחיות של המנהל שהוקצה בזמן אמת.',
            'ip-allowlist'          => 'רשימת הרשאות IP',
            'ip-any'                => 'כל IP (ברירת מחדל)',
            'ip-restricted'         => 'מוגבל לכתובות IP ספציפיות',
            'ip-list-hint'          => 'ערך אחד בכל שורה. תומך ב-IPv4, IPv6 ו-CIDR (למשל 10.0.0.0/24 או 2001:db8::/32). השאר ריק כדי לאפשר את כל כתובות ה-IP.',
        ],

        'permission_type' => [
            'all'         => 'הכל',
            'custom'      => 'מותאם אישית',
            'same_as_web' => 'זהה להרשאת אינטרנט',
        ],

        'status' => [
            'draft'       => 'טיוטה',
            'active'      => 'פעיל',
            'revoked'     => 'בוטל',
            'regenerated' => 'התחדש',
        ],

        'datagrid' => [
            'id'              => 'תעודה מזהה',
            'name'            => 'שם',
            'admin'           => 'מנהל מערכת',
            'token'           => 'אסימון',
            'status'          => 'סטטוס',
            'permission-type' => 'סוג הרשאה',
            'expires-at'      => 'תקף עד',
            'last-used-at'    => 'בשימוש אחרון',
            'created-at'      => 'נוצר ב-At',
            'edit'            => 'ערוך',
            'revoke'          => 'בטל',
        ],

        'messages' => [
            'draft-created'          => 'נוצרה אינטגרציה. צור את האסימון כדי להתחיל להשתמש בו.',
            'updated'                => 'האינטגרציה עודכנה בהצלחה.',
            'generated'              => 'נוצר אסימון. העתק אותו עכשיו - הוא לא יוצג שוב.',
            'regenerated'            => 'אסימון התחדש. העתק את האסימון החדש כעת - הוא לא יוצג שוב.',
            'revoked'                => 'האסימון בוטל בהצלחה.',
            'generate-only-draft'    => 'רק טיוטות אינטגרציות יכולות להפיק את האסימון שלהן.',
            'regenerate-only-active' => 'ניתן ליצור מחדש רק אסימונים פעילים.',
            'cannot-edit-historic'   => 'לא ניתן לערוך אסימונים שבוטלו או שנוצרו מחדש.',
            'already-inactive'       => 'האסימון הזה כבר לא פעיל.',
        ],

        'errors' => [
            'admin-has-token' => 'למנהל המערכת שנבחר כבר יש אסימון אינטגרציה פעיל.',
        ],

        'validation' => [
            'ip-invalid'           => 'כל כתובת IP מותרת חייבת להיות כתובת IPv4 או IPv6 חוקית (תמיכת CIDR נתמכת).',
            'cidr-prefix-invalid'  => 'קידומת CIDR אינה חוקית עבור גרסת ה-IP הנתונה.',
        ],

        'configuration' => [
            'api' => [
                'title' => 'ממשק API',
                'info'  => 'הגדרות עבור Bagisto API ומודול הניהול שלו.',
            ],
            'integration' => [
                'title' => 'אינטגרציה',
                'info'  => 'נהל את הפלאגין של שילוב API המשמש להנפקת אסימוני ממשק API של מנהל מערכת.',
            ],
            'settings' => [
                'title'  => 'הגדרות מודול',
                'info'   => 'הפעל או השבת את הפלאגין של שילוב API. כשהוא מושבת, תפריט סרגל הצד שלו מוסתר והעמודים שלו מחזירים 404.',
                'enable' => 'אפשר את מודול שילוב API',
            ],
        ],

        'emails' => [
            'generated' => [
                'subject'  => 'נוצר אסימון API חדש: :name',
                'greeting' => 'אסימון שילוב API בשם ":name" נוצר זה עתה בחשבון שלך.',
            ],
            'regenerated' => [
                'subject'  => 'אסימון ה-API שלך נוצר מחדש: :name',
                'greeting' => 'אסימון שילוב ה-API בשם ":name" נוצר מחדש. האסימון הקודם הפסיק לעבוד - רק החדש תקף.',
            ],
            'revoked' => [
                'subject'  => 'אסימון ה-API שלך בוטל: :name',
                'greeting' => 'אסימון שילוב ה-API בשם ":name" בוטל. כל לקוח שמשתמש בו איבד גישה.',
            ],

            'details' => [
                'name' => 'שם אסימון',
                'date' => 'תאריך',
                'ip'   => 'מ-IP',
            ],

            'revoke-hint'   => 'אם לא ציפיתם לכך, שללו את האסימון מיד באמצעות הכפתור למטה.',
            'revoke-btn'    => 'בטל את האסימון הזה',
            'revoke-expiry' => 'קישור ביטול זה תקף ל-7 ימים. לאחר מכן, היכנס ללוח הניהול כדי לנהל את האסימון.',
            'no-action'     => 'אין צורך בפעולה - אימייל זה הוא רק אישור.',
        ],

        'revoke-confirmation' => [
            'title'                    => 'בטל אסימון API',
            'success-title'            => 'אסימון בוטל',
            'success-message'          => 'האסימון ":name" בוטל. כל לקוח שמשתמש בו איבד גישה מיד.',
            'already-inactive-title'   => 'אסימון כבר לא פעיל',
            'already-inactive-message' => 'האסימון ":name" כבר בוטל או נוצר מחדש. אין צורך בפעולה נוספת.',
        ],

        'confirm' => [
            'generate' => [
                'title'   => 'צור אסימון',
                'message' => 'ליצור את האסימון עכשיו? הטקסט הפשוט יוצג פעם אחת בלבד - העתק אותו לפני היציאה מהעמוד.',
            ],
            'regenerate' => [
                'title'   => 'צור מחדש את האסימון',
                'message' => 'ליצור מחדש את האסימון? האסימון הישן יפסיק לפעול מיד והטקסט הפשוט החדש יוצג פעם אחת בלבד.',
            ],
            'revoke' => [
                'title'   => 'בטל אסימון',
                'message' => 'לבטל את האסימון הזה? כל לקוח שישתמש בו יאבד גישה מיידית. לא ניתן לבטל פעולה זו.',
            ],
        ],
    ],
];
