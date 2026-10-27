<?php

return [

    'graphql' => [
        'headers' => [
            'unknown-channel' => 'X-Channel başlığındaki ":code" kanalı mevcut değil.',
            'storefront-key-required' => 'X-STOREFRONT-KEY başlığı gereklidir',
            'storefront-key-invalid' => 'Geçersiz mağaza anahtarı',
            'storefront-key-rate-limited' => 'İstek sınırı aşıldı. Lütfen :seconds saniye sonra tekrar deneyin',
        ],

        'cart' => [
            'authentication-required' => 'Kimlik doğrulama jetonu gereklidir',
            'invalid-token' => 'Geçersiz veya süresi dolmuş kimlik doğrulama jetonu',
            'unauthorized-access' => 'Sepete yetkisiz erişim',
            'authenticated-only' => 'Yalnızca kimliği doğrulanmış kullanıcılar sepetlerini getirebilir',
            'merge-requires-auth' => 'Misafir birleştirme için kimlik doğrulama gereklidir',
            'unknown-operation' => 'Bilinmeyen sepet işlemi',

            'cart-not-found' => 'Sepet bulunamadı',
            'guest-cart-not-found' => 'Misafir sepeti bulunamadı',
            'product-not-found' => 'Ürün bulunamadı',

            'product-id-quantity-required' => 'Ürün kimliği ve miktarı gereklidir',
            'cart-item-id-quantity-required' => 'Sepet öğesi kimliği ve miktarı gereklidir',

            'file-token-invalid' => 'Yüklenen dosya referansı geçersiz veya süresi dolmuş. Dosyayı yeniden yükleyin.',
            'file-token-forbidden' => 'Bu yüklenen dosya sepetinize ait değil.',
            'customizable-option-required' => '":label" özelleştirilebilir seçeneği gereklidir.',
            'customizable-file-option-invalid' => 'Seçilen seçenek bu ürün için bir dosya seçeneği değil.',
            'customizable-file-invalid' => 'Dosya eksik, desteklenmeyen bir uzantıya sahip veya çok büyük.',
            'cart-item-id-required' => 'Sepet öğesi kimliği gereklidir',
            'item-ids-required' => 'Öğe kimlikleri dizisi gereklidir',

            'event-booking-quantity-not-changeable' => 'Etkinlik rezervasyonu ürününün miktarı değiştirilemez. Miktar, bilet seçimine göre belirlenir.',
            'appointment-booking-quantity-not-changeable' => 'Randevu rezervasyonu ürününün miktarı değiştirilemez.',
            'coupon-code-required' => 'Kupon kodu gereklidir',
            'address-data-required' => 'Ülke, il ve posta kodu gereklidir',
            'grouped-qty-required' => 'Gruplanmış ürün için seçilen miktarlar gereklidir. groupedQty değerini JSON dizesi olarak gönderin, ör. {"101":2,"102":1}.',
            'grouped-qty-must-include-all' => 'Gruplanmış ürün, ilişkili tüm ürünler için miktar gerektirir. Eksik kimlikler: :ids.',
            'grouped-qty-invalid-associated' => 'Gruplanmış ürün miktarları geçersiz ilişkili ürün kimlikleri içeriyor: :ids.',
            'grouped-qty-invalid-quantity' => ':id kimlikli ilişkili ürün için geçersiz miktar girildi. Miktar negatif olmayan bir tam sayı olmalıdır.',
            'bundle-qty-not-changeable' => '":option" paket seçeneğinin miktarı değiştirilemez. İzin verilen miktar :qty.',

            'add-product-failed' => 'Ürün sepete eklenemedi',
            'update-item-failed' => 'Sepet öğesi güncellenemedi',
            'remove-item-failed' => 'Sepet öğesi kaldırılamadı',
            'remove-coupon-failed' => 'Kupon kaldırılamadı',
            'coupon-applied' => 'Kupon başarıyla uygulandı',
            'coupon-removed' => 'Kupon başarıyla kaldırıldı',
            'move-to-wishlist-failed' => 'Öğe istek listesine taşınamadı',
            'estimate-shipping-failed' => 'Kargo tahmini yapılamadı',

            'product-added-successfully' => 'Ürün sepete başarıyla eklendi',
            'cart-item-updated-successfully' => 'Sepet öğesi başarıyla güncellendi',
            'guest-cart-merged' => 'Misafir sepeti başarıyla birleştirildi',
            'using-authenticated-cart' => 'Kimliği doğrulanmış müşteri sepeti kullanılıyor',
            'cart-item-not-found' => 'Sepet öğesi bulunamadı',
            'new-guest-cart-created' => 'Benzersiz oturum jetonu ile yeni misafir sepeti oluşturuldu',
            'select-items-to-remove' => 'Lütfen kaldırılacak öğeleri seçin',
            'select-items-to-move-wishlist' => 'Lütfen istek listesine taşınacak öğeleri seçin',
            'invalid-or-expired-token' => 'Sepet jetonu geçersiz veya süresi dolmuş. Lütfen yeni bir sepet oluşturun.',
            'invalid-token-of-login-user' => 'Giriş yapan kullanıcının jetonu geçersiz.',
            'booking-note-required' => 'Masa rezervasyonu için booking.note gereklidir.',
            'booking-date-required' => 'booking.slot biçimlendirilmiş bir zaman aralığı olduğunda booking.date gereklidir.',
            'booking-qty-required' => 'Etkinlik rezervasyonu için booking.qty gereklidir.',
            'booking-ticket-qty-invalid' => 'Etkinlik bileti miktarı negatif olmayan bir sayı olmalıdır.',
            'booking-ticket-required' => 'Etkinlik rezervasyonu için en az bir bilet miktarı seçin.',
            'booking-slot-unavailable' => 'Seçilen zaman dilimi verilen tarih için uygun değil.',
            'invalid-quantity' => 'Miktar en az 1 olmalıdır',
            'product-id-required' => 'Ürün kimliği gereklidir',
            'shipping-address-required' => 'Kargo tahmini için ülke ve posta kodu gereklidir',
        ],

        'token-verification' => [
            'invalid-operation' => 'Geçersiz işlem',
            'invalid-input-data' => 'Geçersiz giriş verileri',
            'token-required' => 'Jeton gereklidir',
            'invalid-token-format' => 'Geçersiz jeton biçimi',
            'token-not-found-or-expired' => 'Jeton bulunamadı veya süresi doldu',
            'customer-not-found' => 'Müşteri bulunamadı',
            'customer-account-suspended' => 'Müşteri hesabı askıya alındı',
            'error-verifying-token' => 'Jeton doğrulanırken hata oluştu',
            'token-is-valid' => 'Jeton geçerli',
            'unauthenticated' => 'Kimlik doğrulanmadı. Bu işlemi gerçekleştirmek için lütfen giriş yapın',
        ],

        'forgot-password' => [
            'invalid-operation' => 'Geçersiz işlem',
            'invalid-input-data' => 'Geçersiz giriş verileri',
            'email-required' => 'E-posta gereklidir',
            'reset-link-sent' => 'Sıfırlama bağlantısı e-postanıza başarıyla gönderildi',
            'email-not-found' => 'E-posta adresi bulunamadı',
            'throttled' => 'Çok fazla sıfırlama denemesi. Lütfen tekrar denemeden önce bekleyin.',
            'error-sending-reset-link' => 'Sıfırlama bağlantısı gönderilirken bir hata oluştu',
        ],

        'reset-password' => [
            'failed' => 'Parola sıfırlanamadı. Lütfen tekrar deneyin.',
        ],

        'rate-limit' => [
            'too-many-attempts' => 'Çok fazla deneme. Lütfen :seconds saniye sonra tekrar deneyin.',
        ],

        'logout' => [
            'invalid-operation' => 'Geçersiz işlem',
            'invalid-input-data' => 'Geçersiz giriş verileri',
            'token-required' => 'Jeton gereklidir',
            'invalid-token-format' => 'Geçersiz jeton biçimi',
            'logged-out-successfully' => 'Başarıyla çıkış yapıldı',
            'token-not-found-or-expired' => 'Jeton bulunamadı veya süresi zaten doldu',
            'error-during-logout' => 'Çıkış sırasında hata oluştu',
            'unauthenticated' => 'Kimlik doğrulanmadı. Bu işlemi gerçekleştirmek için lütfen giriş yapın',
        ],

        'address' => [
            'deleted-successfully' => 'Adres başarıyla silindi',
            'authentication-required' => 'Kimlik doğrulama jetonu gereklidir',
            'invalid-token' => 'Geçersiz veya süresi dolmuş jeton',
            'unknown-operation' => 'Bilinmeyen işlem',
            'address-id-required' => 'Adres kimliği gereklidir',
            'address-not-found' => 'Adres bulunamadı veya bu müşteriye ait değil',
            'retrieved' => 'Adresler başarıyla alındı',
            'fetch-failed' => 'Adresler alınamadı:',
            'field-required' => ':field alanı gereklidir',
        ],

        'customer-profile' => [
            'authentication-required' => 'Kimlik doğrulama jetonu gereklidir. Lütfen jetonu sorgu girişinde sağlayın',
            'invalid-token' => 'Geçersiz veya süresi dolmuş jeton',
            'profile-updated' => 'Müşteri profili başarıyla güncellendi',
        ],

        'customer' => [
            'password-mismatch' => 'Parola ve parola onayı eşleşmiyor',
            'confirm-password-required' => 'Parola değiştirilirken parola onayı gereklidir',
            'current-password-required' => 'Parolanızı değiştirmek için mevcut parola gereklidir',
            'current-password-incorrect' => 'Girdiğiniz mevcut parola yanlış',
            'verify-email-to-login' => 'Hesabınız oluşturuldu. Giriş yapmak için lütfen e-posta adresinizi doğrulayın.',
            'delete-password-required' => 'Hesabınızı silmek için parolanız gereklidir',
            'delete-password-incorrect' => 'Girdiğiniz parola yanlış',
            'delete-has-active-orders' => 'Bekleyen veya işlenmekte olan siparişleriniz varken hesabınız silinemez',
            'unauthenticated' => 'Kimlik doğrulanmadı. Bu işlemi gerçekleştirmek için lütfen giriş yapın',
            'id-required' => 'Müşteri kimliği gereklidir',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/admin/customers/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
            'not-found' => 'Müşteri bulunamadı',
            'phone-special-chars-not-allowed' => 'Cep telefonu numarası yalnızca rakam içerebilir. Özel karakterlere izin verilmez',
            'email-already-taken' => 'Bu e-posta adresi başka bir hesaba zaten kayıtlı',
            'invalid-gender' => 'Geçersiz cinsiyet değeri ":gender". İzin verilen değerler: :valid',
        ],

        'product-review' => [
            'id-required' => 'Ürün değerlendirmesi kimliği gereklidir',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/shop/reviews/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
            'not-found' => '":id" kimlikli ürün değerlendirmesi bulunamadı',
            'item-not-found' => '":id" öğesi bulunamadı.',
            'product-id-required' => 'Ürün kimliği gereklidir',
            'product-not-found' => 'Ürün bulunamadı',
            'rating-invalid' => 'Değerlendirme 1 ile 5 arasında olmalıdır',
            'title-required' => 'Değerlendirme başlığı gereklidir',
            'comment-required' => 'Değerlendirme yorumu gereklidir',
            'review-disabled' => 'Ürün değerlendirmeleri şu anda devre dışı',
            'guest-review-disabled' => 'Misafir değerlendirmelerine izin verilmiyor. Değerlendirme göndermek için lütfen giriş yapın',
            'owner-required' => 'Değerlendirmenizi yönetmek için lütfen giriş yapın',
            'not-owned' => 'Bu değerlendirme size ait değil; değiştirilemez veya silinemez',
            'invalid-attachments' => 'Geçersiz ek biçimi.',
            'invalid-product-id-format' => 'Geçersiz ürün kimliği biçimi. "/api/shop/products/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
        ],

        'customer-review' => [
            'id-required' => 'Müşteri değerlendirmesi kimliği gereklidir',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/shop/customer-reviews/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
            'not-found' => '":id" kimlikli müşteri değerlendirmesi bulunamadı',
        ],

        'newsletter' => [
            'error-during-subscription' => 'Abonelik şu anda tamamlanamıyor. Lütfen daha sonra tekrar deneyin',
        ],

        'contact-us' => [
            'success' => 'Talebiniz başarıyla gönderildi. En kısa sürede size dönüş yapacağız',
            'failed' => 'Talebiniz şu anda gönderilemiyor. Lütfen daha sonra tekrar deneyin',
            'invalid-input' => 'Geçersiz iletişim formu verileri',
        ],

        'customer-order' => [
            'id-required' => 'Müşteri siparişi kimliği gereklidir',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/shop/customer-orders/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
            'not-found' => '":id" kimlikli müşteri siparişi bulunamadı',
        ],

        'return' => [
            'not-found' => 'İade talebi bulunamadı.',
            'disabled' => 'Bu mağaza için iadeler etkin değil.',
            'invalid-input' => 'Eksik veya geçersiz iade bilgileri.',
            'invalid-resolution-type' => 'Çözüm türü "return" veya "cancel_items" olmalıdır.',
            'agreement-required' => 'İade politikasını kabul etmelisiniz.',
            'invalid-order' => 'Seçilen sipariş geçerli değil.',
            'invalid-item' => 'Seçilen öğe iade için uygun değil.',
            'qty-exceeds' => 'Talep edilen miktar iade edilebilir miktarı aşıyor.',
            'invalid-package-condition' => 'Paket durumu "open" veya "packed" olmalıdır.',
            'invalid-image' => 'Yüklenen dosyalardan biri veya birkaçı desteklenmeyen bir dosya türüne sahip.',
            'invalid-custom-field' => 'Bilinmeyen özel alan ":id".',
            'custom-field-required' => '":field" alanı gereklidir.',
            'invalid-custom-field-value' => '":field" için girilen değer izin verilen seçeneklerden biri değil.',
            'created' => 'İade talebi başarıyla oluşturuldu.',
            'canceled' => 'İade talebi başarıyla iptal edildi.',
            'already-canceled' => 'İade talebi zaten iptal edilmiş.',
            'cancel-not-allowed' => 'Bu iade talebi iptal edilemez.',
            'close-not-allowed' => 'Bu iade talebi kapatılamaz.',
            'reopened' => 'İade talebi başarıyla yeniden açıldı.',
            'reopen-not-allowed' => 'Bu iade talebi yeniden açılamaz.',
            'closed' => 'İade talebi başarıyla kapatıldı.',
            'message-sent' => 'Mesaj başarıyla gönderildi.',
            'attachment-not-found' => 'Mesaj eki bulunamadı.',
        ],

        'eu-withdrawal' => [
            'not-found' => 'Cayma beyanı bulunamadı.',
            'order-not-found' => 'Sipariş bulunamadı veya cayma için uygun değil.',
            'too-many-attempts' => 'Çok fazla deneme. Lütfen :seconds saniye sonra tekrar deneyin.',
            'created' => 'Cayma beyanı başarıyla iletildi.',
        ],

        'customer-order-shipment' => [
            'id-required' => 'Gönderi kimliği gereklidir',
            'not-found' => '":id" kimlikli gönderi bulunamadı veya bu müşteriye ait değil',
            'item-not-found' => '":id" kimlikli gönderi öğesi bulunamadı veya bu müşteriye ait değil',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/shop/customer-order-shipments/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
        ],

        'cancel-order' => [
            'order-id-required' => 'Siparişi iptal etmek için sipariş kimliği gereklidir',
            'not-found' => '":id" kimlikli sipariş bulunamadı veya bu müşteriye ait değil',
            'cancel-success' => 'Sipariş başarıyla iptal edildi',
            'cancel-failed' => 'Sipariş iptal edilemez. Zaten işlenmiş, kargoya verilmiş veya iptal edilmiş olabilir',
        ],

        'reorder' => [
            'order-id-required' => 'Yeniden sipariş vermek için sipariş kimliği gereklidir',
            'not-found' => '":id" kimlikli sipariş bulunamadı veya bu müşteriye ait değil',
            'reorder-success' => 'Önceki siparişinizden :count öğe sepetinize eklendi',
            'no-items-added' => 'Sepete hiçbir öğe eklenemedi. Ürünler stokta olmayabilir veya kullanılamıyor olabilir',
        ],

        'customer-downloadable-product' => [
            'id-required' => 'Müşteri indirilebilir ürün kimliği gereklidir',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/shop/customer-downloadable-products/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
            'not-found' => '":id" kimlikli müşteri indirilebilir ürünü bulunamadı',

            'download' => [
                'unauthenticated' => 'Yetkisiz: Müşteri kimlik doğrulaması gereklidir.',
                'not-found' => 'İndirilebilir ürün bulunamadı.',
                'pending' => 'İndirme beklemede. Lütfen siparişin faturalandırılmasını bekleyin.',
                'limit-exceeded' => 'İndirme sınırı aşıldı.',
                'no-downloads-remaining' => 'Kullanılabilir indirme hakkı kalmadı.',
                'file-not-found' => 'Dosya bulunamadı.',
                'link-not-found' => 'İndirilebilir bağlantı bulunamadı.',
            ],
        ],

        'customer-invoice' => [
            'id-required' => 'Müşteri faturası kimliği gereklidir',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/shop/customer-invoices/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
            'not-found' => '":id" kimlikli müşteri faturası bulunamadı',
        ],

        'auth' => [
            'no-token-provided' => 'Kimlik doğrulama jetonu sağlanmadı. Lütfen jetonu Authorization başlığında "Bearer <token>" olarak veya input.token alanında sağlayın',
            'invalid-or-expired-token' => 'Geçersiz veya süresi dolmuş jeton',
            'request-not-found' => 'İstek bağlamda bulunamadı',
            'token-required' => 'Authentication token is required. Please provide the token either in the GraphQL mutation input field or in the Authorization header as "Bearer <token>"',
            'unknown-resource' => 'Bilinmeyen kaynak',
            'cannot-update-other-profile' => 'Yetkisiz: Başka bir müşteri profili güncellenemez',
            'refresh-token-invalid' => 'Yenileme belirteci geçersiz veya süresi dolmuş. Lütfen yeniden giriş yapın.',
            'too-many-login-attempts' => 'Çok fazla giriş denemesi. Lütfen :seconds saniye sonra tekrar deneyin.',
            'too-many-refresh-requests' => 'Çok fazla belirteç yenilemesi. Lütfen :seconds saniye sonra tekrar deneyin.',
            'rate-limit-exceeded' => 'Çok fazla deneme.',
            'access-token-expired' => 'Erişim belirtecinin süresi doldu. Devam etmek için yenileyin.',
            'logged-out-all' => 'Tüm oturumlarınız kapatıldı.',
            'session-not-found' => 'Oturum bulunamadı.',
            'session-revoked' => 'Oturum iptal edildi.',
        ],

        'upload' => [
            'invalid-base64' => 'Geçersiz base64 kodlu görsel verisi',
            'size-exceeds-limit' => 'Görsel boyutu 5MB\'ı geçmemelidir',
            'invalid-format' => 'Geçersiz görsel biçimi. Lütfen veri URI şeması ile base64 kodlu görsel sağlayın (data:image/jpeg;base64,...)',
            'failed' => 'Görsel yüklemesi başarısız oldu',
        ],

        'attribute' => [
            'code-already-exists' => 'Öznitelik kodu zaten mevcut',
            'option-id-required' => 'Nitelik seçenekleri doğrudan sorgulanırken "attributeId" parametresi gereklidir',
            'id-required' => 'Nitelik kimliği gereklidir',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/shop/attributes/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
            'not-found' => 'Nitelik bulunamadı',
        ],

        'attribute-option' => [
            'id-required' => 'Nitelik seçeneği kimliği gereklidir',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/shop/attribute-options/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
            'not-found' => 'Nitelik seçeneği bulunamadı',
        ],

        'channel' => [
            'id-required' => 'Kanal kimliği gereklidir',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/shop/channels/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
            'not-found' => 'Kanal bulunamadı',
        ],

        'country-state' => [
            'id-required' => 'Ülke ili kimliği gereklidir',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/shop/country-states/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
            'not-found' => 'Ülke ili bulunamadı',
            'country-id-required' => 'Ülke kimliği gereklidir',
        ],

        'country' => [
            'id-required' => 'Ülke kimliği gereklidir',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/shop/countries/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
            'not-found' => 'Ülke bulunamadı',
        ],

        'login' => [
            'invalid-credentials' => 'Geçersiz e-posta veya parola',
            'account-suspended' => 'Hesabınız askıya alındı',
            'account-inactive' => 'Hesabınızın aktif edilmesi için yönetici onayı gerekiyor.',
            'email-not-verified' => 'Lütfen önce e-posta adresinizi doğrulayın.',
            'successful' => 'Başarıyla giriş yaptınız',
            'invalid-request' => 'Geçersiz giriş isteği',
        ],

        'social-login' => [
            'signed-in' => 'Başarıyla giriş yapıldı.',
            'token-required' => 'Sosyal giriş belirteci gerekli.',
            'invalid-token' => 'Sosyal giriş belirteci geçersiz veya süresi dolmuş. Lütfen tekrar deneyin.',
            'google-not-configured' => 'Bu mağaza için Google ile oturum açma yapılandırılmamış.',
            'wrong-audience' => 'Bu belirteç başka bir uygulama için verildi.',
            'email-required' => 'Sağlayıcı bir e-posta adresi paylaşmadı. Lütfen e-posta ile kaydolun.',
            'account-inactive' => 'Hesabınızın aktif edilmesi için yönetici onayı gerekiyor.',
            'provider-not-supported' => 'Bu sosyal giriş sağlayıcısı desteklenmiyor.',
            'provider-disabled' => 'Bu sosyal giriş sağlayıcısı etkin değil.',
        ],

        'checkout' => [
            'invalid-input' => 'Ödeme işlemi için geçersiz giriş verileri',
            'billing-address-required' => 'Fatura adresi gereklidir',
            'shipping-address-required' => 'Gönderiler için teslimat adresi gereklidir',
            'address-save-failed' => 'Adres kaydedilemedi',
            'address-saved' => 'Adres başarıyla kaydedildi',
            'shipping-method-required' => 'Kargo yöntemi gereklidir',
            'invalid-shipping-method' => 'Geçersiz veya kullanılamayan kargo yöntemi',
            'shipping-method-save-failed' => 'Kargo yöntemi kaydedilemedi',
            'shipping-method-saved' => 'Kargo yöntemi başarıyla kaydedildi',
            'shipping-method-error' => 'Kargo yöntemi kaydedilirken hata oluştu',
            'payment-method-required' => 'Ödeme yöntemi gereklidir',
            'invalid-payment-method' => 'Geçersiz veya kullanılamayan ödeme yöntemi',
            'payment-method-save-failed' => 'Ödeme yöntemi kaydedilemedi',
            'payment-method-saved' => 'Ödeme yöntemi başarıyla kaydedildi',
            'payment-method-error' => 'Ödeme yöntemi kaydedilirken hata oluştu',
            'order-placed' => 'Sipariş başarıyla verildi',
            'order-redirect-required' => 'Bu ödeme yöntemi, alışverişçinin ödemeyi ödeme ağ geçidinde tamamlamasını gerektirir. Onu redirectUrl adresine yönlendirin; sipariş, ağ geçidi ödemeyi onayladığında oluşturulur.',
            'order-creation-failed' => 'Sipariş oluşturma başarısız: Sipariş kimliği boş veya sipariş kaydedilmedi',
            'order-retrieval-failed' => 'Oluşturulan sipariş alınamadı',
            'order-creation-error' => 'Sipariş oluşturulamadı',
            'cart-empty' => 'Sepet boş',
            'account-suspended' => 'Hesabınız askıya alındı. Lütfen destek ile iletişime geçin.',
            'account-inactive' => 'Hesabınız etkin değil. Lütfen destek ile iletişime geçin.',
            'minimum-order-not-met' => 'Minimum sipariş tutarı :amount',
            'guest-checkout-not-allowed' => 'Ödeme yapmak için lütfen giriş yapın. Bu sepet için misafir ödemesi kullanılamaz.',
            'email-required' => 'Sipariş oluşturmak için e-posta adresi gereklidir',
            'unknown-operation' => 'Bilinmeyen ödeme işlemi',
        ],

        'customer-addresses' => [
            'token-required' => 'Müşteri adreslerini getirmek için jeton gereklidir',
            'invalid-or-expired-token' => 'Geçersiz veya süresi dolmuş jeton',
            'token-validation-failed' => 'Jeton doğrulaması başarısız oldu',
        ],

        'product' => [
            'not-found' => 'Ürün bulunamadı',
            'not-found-with-sku' => 'Bu SKU ile ürün bulunamadı',
            'not-found-with-url-key' => 'Bu URL anahtarı ile ürün bulunamadı',
            'parameters-required' => 'Şu parametrelerden en az biri sağlanmalıdır: "sku", "id", "urlKey"',
            'type' => 'Ürün Türü',
            'attribute-family' => 'Öznitelik Ailesi',
            'sku' => 'SKU',
            'name' => 'Ad',
            'description' => 'Açıklama',
            'short-description' => 'Kısa Açıklama',
            'status' => 'Durum',
            'new' => 'Yeni',
            'featured' => 'Öne Çıkan',
            'price' => 'Fiyat',
            'special-price' => 'Özel Fiyat',
            'weight' => 'Ağırlık',
            'cost' => 'Maliyet',
            'length' => 'Uzunluk',
            'width' => 'Genişlik',
            'height' => 'Yükseklik',
            'color' => 'Renk',
            'size' => 'Boyut',
            'brand' => 'Marka',
            'super-attributes' => 'Süper Öznitelikler',
            'id-required' => 'Ürün kimliği gereklidir',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/shop/products/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
        ],

        'downloadable-product' => [
            'download-link-not-found' => 'İndirme bağlantısı bulunamadı veya süresi dolmuş',
            'purchased-link-not-found' => 'Satın alınan bağlantı bulunamadı',
            'file-not-found' => 'Dosya bulunamadı',
            'download-successful' => 'Dosya indirilmeye hazır',
            'token-required' => 'İndirme jetonu gereklidir',
            'invalid-token' => 'İndirme jetonu geçersiz veya süresi dolmuş',
            'token-expired' => 'İndirme jetonu süresi dolmuş. Lütfen yeni bir tane oluşturun',
            'access-denied' => 'Erişim reddedildi: Bu dosyayı indirme izniniz yok',
            'redirect-external-url' => 'Harici indirme URL\'sine yeniden yönlendiriliyor',
            'file-error' => 'İndirme isteğiniz işlenirken bir hata oluştu',
            'unauthorized-access' => 'İndirme kaynağına yetkisiz erişim',
            'unauthenticated' => 'Yetkisiz: Müşteri kimlik doğrulaması gereklidir',
            'link-id-required' => 'downloadableLinkPurchasedId gereklidir',
            'link-not-found' => 'İndirilebilir bağlantı bulunamadı',
            'not-owner' => 'Yetkisiz: Bu indirme bağlantısı size ait değil',
            'pending' => 'İndirme beklemede. Lütfen siparişin faturalandırılmasını bekleyin.',
            'limit-exceeded' => 'İndirme sınırı aşıldı',
            'no-downloads-left' => 'Kullanılabilir indirme hakkı kalmadı',
        ],

        'booking-slot' => [
            'id-required' => 'Rezervasyon ürünü kimliği gereklidir.',
            'date-required' => 'Tarih gereklidir.',
            'product-not-found' => 'Rezervasyon ürünü bulunamadı.',
            'invalid-type' => 'Bu rezervasyon türü desteklenmiyor.',
            'invalid-time-range' => 'Geçersiz zaman aralığı.',
            'invalid-time-format' => 'Geçersiz saat biçimi.',
        ],

        'resolver' => [
            'no-resolve-info' => 'GraphQL ResolveInfo bağlamda mevcut değil. Model sınıfı belirlenemiyor.',
            'unknown-resource-class' => 'GraphQL dönüş türünden kaynak sınıfı belirlenemiyor',
        ],

        'docs' => [
            'openapi-unavailable' => 'OpenAPI Factory örneği oluşturulamadı',
        ],

        'locale' => [
            'id-required' => 'Yerel ayar kimliği gereklidir',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/shop/locales/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
            'not-found' => 'Yerel ayar bulunamadı',
        ],

        'category' => [
            'id-required' => 'Kategori kimliği gereklidir',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/shop/categories/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
            'not-found' => 'Kategori bulunamadı',
        ],

        'compare-item' => [
            'id-required' => 'Karşılaştır öğesi kimliği gereklidir',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/shop/compare-items/1" veya sayısal kimlik gibi IRI biçimi beklenir',
            'not-found' => 'Karşılaştır öğesi bulunamadı',
            'product-id-required' => 'Ürün kimliği gereklidir',
            'customer-id-required' => 'Müşteri kimliği gereklidir',
            'product-not-found' => 'Ürün bulunamadı',
            'customer-not-found' => 'Müşteri bulunamadı',
            'already-exists' => 'Bu ürün zaten karşılaştırma listenizde var',
            'delete-all-success' => 'Tüm karşılaştırma öğeleri başarıyla kaldırıldı',
        ],

        'wishlist' => [
            'id-required' => 'İstek listesi öğesi kimliği gereklidir',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/shop/wishlists/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
            'not-found' => 'İstek listesi öğesi bulunamadı',
            'product-id-required' => 'Ürün kimliği gereklidir',
            'customer-id-required' => 'Müşteri kimliği gereklidir',
            'product-not-found' => 'Ürün bulunamadı',
            'product-disabled' => 'Bu ürün şu anda devre dışı',
            'customer-not-found' => 'Müşteri bulunamadı',
            'already-exists' => 'Bu ürün zaten istek listenizde',
            'added' => 'Öğe İstek Listesine Başarıyla Eklendi',
            'removed' => 'Öğe İstek Listesinden Başarıyla Kaldırıldı',
            'product-removed' => 'Ürün kaldırıldı',
            'wishlist-item-id-required' => 'İstek listesi öğesi kimliği gereklidir',
            'invalid-quantity' => 'Miktar 0\'dan büyük olmalıdır',
            'move-to-cart-missing-options' => 'Üründe eksik zorunlu seçenekler var. Lütfen ürünü elle yapılandırın',
            'moved-to-cart-success' => 'Öğe sepete başarıyla taşındı',
            'delete-all-success' => 'Tüm istek listesi öğeleri başarıyla kaldırıldı',
        ],

        'gdpr' => [
            'disabled' => 'GDPR veri talepleri devre dışı. Lütfen yönetici yapılandırmasından GDPR\'yi etkinleştirin.',
            'unauthenticated' => 'GDPR veri taleplerini yönetmek için giriş yapmış olmalısınız.',
            'not-found' => 'GDPR veri talebi bulunamadı',
            'type-required' => 'Talep türü gereklidir',
            'type-invalid' => 'Talep türü "delete" veya "update" olmalıdır',
            'message-required' => 'Mesaj gereklidir',
            'raised' => 'GDPR veri talebiniz başarıyla oluşturuldu.',
            'revoked' => 'GDPR veri talebiniz başarıyla geri çekildi.',
            'revoke-not-allowed' => 'Bu GDPR veri talebi artık geri çekilemez.',
            'deleted' => 'GDPR veri talebiniz başarıyla silindi.',
        ],

        'section' => [
            'id-required' => 'Bölüm kimliği gereklidir',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/shop/sections/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
            'not-found' => 'Bölüm bulunamadı',
        ],

        'install' => [
            'starting' => 'API Platform kurulumu başlatılıyor...',
            'completed-success' => '✓ API Platform kurulumu başarıyla tamamlandı!',
            'failed' => '✗ Kurulum başarısız oldu: ',

            'provider-already-registered' => 'ℹ Service Provider zaten kayıtlı',
            'provider-not-registered' => '⚠ Service Provider otomatik olarak kaydedilemedi. :provider sınıfını bootstrap/providers.php dosyasına elle ekleyin.',
            'provider-registered' => '✓ Service Provider bootstrap/providers.php dosyasına kaydedildi',
            'provider-file-not-found' => 'Dosya bulunamadı: :file',
            'provider-permission-denied' => 'İzin reddedildi: :file dosyasına yazılamıyor. Lütfen dosya izinlerini kontrol edin.',

            'config-already-exists' => 'ℹ Yapılandırma dosyası zaten mevcut',
            'config-published' => '✓ API Platform yapılandırması config/ dizinine yayımlandı',
            'config-source-not-found' => 'Kaynak yapılandırma dosyası bulunamadı: :file',
            'config-permission-denied' => 'İzin reddedildi: :directory dizinine yazılamıyor. Lütfen dizin izinlerini kontrol edin.',

            'translatable-not-found' => 'ℹ TranslatableModel bulunamadı - atlanıyor',
            'translatable-already-abstract' => 'ℹ TranslatableModel zaten soyut',
            'translatable-made-abstract' => '✓ TranslatableModel soyut hale getirildi',
            'translatable-permission-denied' => 'İzin reddedildi: :file dosyasına yazılamıyor. Lütfen dosya izinlerini kontrol edin.',

            'providers-already-registered' => 'ℹ API Platform sağlayıcıları zaten kayıtlı',
            'providers-registered' => '✓ API Platform sağlayıcıları bootstrap/app.php dosyasına kaydedildi',
            'providers-file-not-found' => 'Dosya bulunamadı: :file',
            'providers-permission-denied' => 'İzin reddedildi: :file dosyasına yazılamıyor. Lütfen dosya izinlerini kontrol edin.',
            'providers-not-found' => 'bootstrap/app.php dosyasında Application oluşturma kodu bulunamadı',

            'assets-published' => '✓ Paket varlıkları yayımlandı',
            'running-migrations' => 'Veritabanı geçişleri çalıştırılıyor...',
            'migrations-completed' => '✓ Veritabanı geçişleri tamamlandı',
            'clearing-caches' => 'Önbellekler temizleniyor ve optimize ediliyor...',
            'caches-optimized' => '✓ Önbellekler temizlendi ve optimize edildi',
            'generating-api-key' => 'API anahtarı oluşturuluyor...',
            'api-key-generated' => '✓ API anahtarı başarıyla oluşturuldu',
            'api-key-already-exists' => 'ℹ Varsayılan mağaza API anahtarı zaten mevcut - oluşturma atlanıyor',

            'migrations-error' => 'Veritabanı geçişleri başarısız oldu.',
            'migrations-error-running' => 'Veritabanı geçişleri çalıştırılırken hata oluştu: :error',
            'cache-clear-error' => 'Önbellek temizleme başarısız oldu.',
            'cache-optimize-error' => 'Optimizasyon başarısız oldu.',
            'cache-error' => 'Önbellekler temizlenirken ve optimize edilirken hata oluştu: :error',
            'api-key-generation-error' => 'API anahtarı oluşturulamadı.',
            'api-key-error' => 'API anahtarı oluşturulurken hata oluştu: :error',

            'key-management-required' => ':action işlemi için --key gereklidir',
            'key-rotation-error' => 'Anahtar yenilenirken hata oluştu: :error',
            'key-deactivation-error' => 'Anahtar devre dışı bırakılırken hata oluştu: :error',
            'key-not-found' => 'API anahtarı bulunamadı: :identifier',

            'maintenance-starting' => '🔄 API Anahtarı Bakımı Başlatılıyor...',
            'maintenance-complete' => '✅ API Anahtarı Bakımı Tamamlandı',
            'cleanup-expired-keys' => '🧹 Süresi dolmuş anahtarlar temizleniyor...',
            'cleanup-expired-none' => '   ℹ️ Temizlenecek süresi dolmuş anahtar yok',
            'invalidate-deprecated' => '⚠️ Kullanımdan kaldırılmış anahtarlar geçersiz kılınıyor...',
            'invalidate-deprecated-none' => '   ℹ️ Geçersiz kılınacak kullanımdan kaldırılmış anahtar yok',
            'notify-expiring' => '📧 Süre dolumu bildirimleri gönderiliyor...',
            'notify-expiring-none' => '   ℹ️ Bildirim gerektiren anahtar yok',

            'key-rotated-success' => '✅ Anahtar başarıyla yenilendi!',
            'key-deactivated-success' => '✅ Anahtar başarıyla devre dışı bırakıldı!',
            'deactivation-cancelled' => 'Devre dışı bırakma iptal edildi.',
            'cleanup-cancelled' => 'Temizleme iptal edildi.',

            'key-status-title' => 'Anahtar Durumu: :name',
            'key-status-active' => 'Etkin: :status',
            'key-status-usable' => 'Kullanılabilir: :status',
            'key-status-expired' => 'Süresi Dolmuş: :status',
            'key-status-deprecated' => 'Kullanımdan Kaldırılmış: :status',
            'key-status-expires-at' => 'Bitiş Tarihi: :expires',
            'key-status-days-until-expiry' => 'Süre Dolumuna Kalan Gün: :days',
            'key-status-last-used' => 'Son Kullanım: :date',
            'key-status-rotated-from' => 'Yenilendiği Anahtar: :key',
            'key-status-rotated-keys' => 'Bundan Yenilenen Anahtarlar: :count',

            'policy-compliance-summary' => 'API Anahtarı Yenileme Politikası Uyumluluk Özeti',
            'available-actions' => 'Kullanılabilir işlemler: rotate, deactivate, cleanup, status, expiring, unused, summary',

            'asset-linked-success' => '✓ API Platform varlıkları başarıyla bağlandı',
            'symlink-create-failed' => 'Sembolik bağlantı oluşturulamadı, bunun yerine varlıklar kopyalanıyor...',
            'asset-copy-warning' => 'Uyarı: API Platform varlıkları bağlanamadı veya kopyalanamadı. Elle kurulum gerekebilir.',
            'asset-copied-success' => '✓ API Platform varlıkları başarıyla kopyalandı',

            'key-name-required' => 'Mağaza anahtarı adı boş olamaz.',
            'key-generated-success' => 'Mağaza anahtarı başarıyla oluşturuldu!',
            'key-details' => 'Anahtar Ayrıntıları:',
            'key-secure-warning' => 'Bu anahtarı güvende tutun! X-STOREFRONT-KEY başlığında kullanılacaktır.',
            'key-share-warning' => 'Bu anahtarı herkese açık olarak paylaşmayın veya sürüm denetimine eklemeyin.',

            'key-already-exists' => '\':name\' adlı bir mağaza anahtarı zaten mevcut.',
            'cleanup-success' => '✅ Süresi dolmuş :count anahtar temizlendi',
            'confirm-cleanup' => 'Bu işlem süresi dolmuş tüm anahtarları geçici olarak silecek. Devam edilsin mi?',
            'confirm-deactivate' => ':name anahtarını devre dışı bırakmak istediğinizden emin misiniz?',

            'old-key' => 'Eski Anahtar: :name',
            'old-key-id' => 'Eski Anahtar Kimliği: :id',
            'deprecation-date' => 'Kullanımdan Kaldırma Tarihi: :date',
            'new-key' => 'Yeni Anahtar: :name',
            'new-key-id' => 'Yeni Anahtar Kimliği: :id',
            'new-key-value' => 'Yeni Anahtar Değeri: :key',
            'expires-at' => 'Bitiş Tarihi: :date',
            'invalid-action' => 'Geçersiz işlem: :action',
            'key-active' => 'Etkin: :status',
            'key-usable' => 'Kullanılabilir: :status',
            'key-expired' => 'Süresi Dolmuş: :status',
            'key-deprecated' => 'Kullanımdan Kaldırılmış: :status',
            'key-field-id' => 'Kimlik',
            'key-field-name' => 'Ad',
            'key-field-key' => 'Anahtar',
            'key-field-rate-limit' => 'İstek Sınırı',
            'key-field-status' => 'Durum',
            'key-days' => ' gün',
            'key-never' => 'Hiçbir zaman',
            'key-requests-minute' => ' istek/dakika',
            'key-unlimited' => 'Sınırsız',
            'rate-limit-exceeded' => 'İstenen istek sınırı izin verilen en yüksek değer olan :max değerini aşıyor. :max istek/dakika olarak ayarlanıyor.',

            'vendor-path-not-found' => 'API Platform vendor yolu bulunamadı: :path',
            'assets-already-linked' => 'API Platform varlıkları zaten bağlı: :path',
            'no-keys-expiring' => 'Önümüzdeki :days gün içinde süresi dolacak anahtar yok',
            'keys-expiring-title' => 'Önümüzdeki :days gün içinde süresi dolacak anahtarlar:',
            'key-display-format' => '• :name (Kimlik: :id)',
            'key-expires-display' => '  Bitiş: :date (:days gün kaldı)',
            'no-unused-keys' => 'Kullanılmayan anahtar bulunamadı (> :days gün)',
            'unused-keys-title' => 'Kullanılmayan anahtarlar (> :days gün):',
            'key-last-used-display' => '  Son Kullanım: :date',
            'total-keys' => 'Toplam Anahtar: :count',
            'valid-keys' => 'Geçerli Anahtar: :count',
            'expired-keys' => 'Süresi Dolmuş Anahtar: :count',
            'deprecated-keys' => 'Kullanımdan Kaldırılmış Anahtar: :count',
            'keys-expiring-soon' => 'Yakında Süresi Dolacak Anahtar (7 gün): :count',
            'unused-keys-summary' => 'Kullanılmayan Anahtar (90 gün): :count',
            'recently-rotated' => 'Yakın Zamanda Yenilenen (30 gün): :count',
            'cleanup-success-message' => '   ✅ Süresi dolmuş :count anahtar temizlendi',
            'invalidate-success-message' => '   ✅ Kullanımdan kaldırılmış :count anahtar geçersiz kılındı',
            'notify-success-message' => '   ✅ :count süre dolumu bildirimi gönderildi',
            'notify-failed-message' => '   ⚠️ :key hakkında bildirim gönderilemedi: :error',
            'publish-assets-warning' => 'Uyarı: Paket varlıkları yayımlanamadı. :error',
            'env-file-not-found' => '.env dosyası bulunamadı. Kurulumdan önce oluşturun.',
            'env-permission-denied' => '.env dosyasına yazılamıyor. İzinlerini kontrol edin.',
            'env-config-saved' => '✓ API Platform ortam yapılandırması başarıyla kaydedildi',
            'api-endpoints' => 'API Uç Noktaları:',
            'api-documentation' => 'API Belgeleri: :url',
            'api-landing-page' => 'API Açılış Sayfası: :url',
            'graphql-playground' => 'GraphQL Playground: :url',
            'rest-api-storefront' => 'Rest API Mağaza Swagger: :url',
            'rest-api-admin' => 'Rest API Yönetici Swagger: :url',
            'generating-jwt-keys' => 'Yönetici API belirteçleri için JWT imzalama anahtarları oluşturuluyor...',
            'jwt-keys-generated' => '✓ JWT imzalama anahtarları hazır',
            'jwt-keys-warning' => 'Uyarı: JWT imzalama anahtarları oluşturulamadı. php artisan bagisto-api:jwt-keys komutunu elle çalıştırın. :error',
            'admin-login' => 'Yönetici API Girişi: :url',
            'admin-api-tokens' => 'Yönetici API Belirteçleri (sunucu entegrasyonları): :url',
            'key-rotation-invalid-key' => 'Geçersiz veya süresi dolmuş bir anahtar yenilenemez',
            'env-file-not-found' => '.env dosyası bulunamadı',
            'env-permission-denied' => 'İzin reddedildi: .env dosyasına yazılamıyor. Lütfen dosya izinlerini kontrol edin.',
        ],

        'attribute-translation' => [
            'id-required' => 'Özellik çevirisi kimliği gereklidir',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/shop/attribute-translations/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
            'not-found' => 'Özellik çevirisi bulunamadı',
        ],

        'currency' => [
            'id-required' => 'Para birimi kimliği gereklidir',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/shop/currencies/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
            'not-found' => 'Para birimi bulunamadı',
        ],

        'customer-address' => [
            'id-required' => 'Müşteri adresi kimliği gereklidir',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/shop/customer-addresses/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
        ],

        'customer-order-shipment-item' => [
            'id-required' => 'Gönderi kalemi kimliği gereklidir',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/shop/customer-order-shipment-items/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
            'not-found' => 'Gönderi kalemi bulunamadı',
        ],

        'page' => [
            'id-required' => 'Sayfa kimliği gereklidir',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/shop/pages/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
            'not-found' => 'Sayfa bulunamadı',
        ],

        'page-translation' => [
            'id-required' => 'Sayfa çevirisi kimliği gereklidir',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/shop/page-translations/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
            'not-found' => 'Sayfa çevirisi bulunamadı',
        ],

        'product-bundle-option' => [
            'id-required' => 'Paket seçeneği kimliği gereklidir',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/shop/product-bundle-options/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
            'not-found' => 'Paket seçeneği bulunamadı',
        ],

        'product-bundle-option-product' => [
            'id-required' => 'Paket seçeneği ürünü kimliği gereklidir',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/shop/product-bundle-option-products/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
            'not-found' => 'Paket seçeneği ürünü bulunamadı',
        ],

        'product-downloadable-link' => [
            'id-required' => 'İndirilebilir bağlantı kimliği gereklidir',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/shop/product-downloadable-links/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
            'not-found' => 'İndirilebilir bağlantı bulunamadı',
        ],

        'product-downloadable-sample' => [
            'id-required' => 'İndirilebilir örnek kimliği gereklidir',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/shop/product-downloadable-samples/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
            'not-found' => 'İndirilebilir örnek bulunamadı',
        ],

        'product-grouped-product' => [
            'id-required' => 'Gruplanmış ürün kimliği gereklidir',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/shop/product-grouped-products/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
            'not-found' => 'Gruplanmış ürün bulunamadı',
        ],

        'product-images' => [
            'id-required' => 'Ürün görseli kimliği gereklidir',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/shop/product-images/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
            'not-found' => 'Ürün görseli bulunamadı',
        ],

        'product-videos' => [
            'id-required' => 'Ürün videosu kimliği gereklidir',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/shop/product-videos/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
            'not-found' => 'Ürün videosu bulunamadı',
        ],

        'theme-customization' => [
            'id-required' => 'Tema özelleştirmesi kimliği gereklidir',
            'invalid-id-format' => 'Geçersiz kimlik biçimi. "/api/shop/theme-customizations/1" gibi IRI biçimi veya sayısal kimlik bekleniyor',
            'not-found' => 'Tema özelleştirmesi bulunamadı',
        ],

        'unknown-resource' => [
            'id-required' => 'Kaynak kimliği gereklidir',
            'not-found' => 'Kaynak bulunamadı',
        ],
    ],

    'admin' => [
        'profile' => [
            'unauthenticated' => 'Kimliğiniz doğrulanmadı.',
        ],

        'dashboard' => [
            'invalid-type' => 'Bilinmeyen gösterge paneli istatistik türü. Geçerli türler: over-all, today, stock-threshold-products, total-sales, top-selling-products, top-customers.',
        ],

        'rma' => [
            'not-found' => 'İade talebi bulunamadı.',
            'invalid-input' => 'Eksik veya geçersiz iade bilgileri.',
            'invalid-order' => 'Seçilen sipariş geçerli değil.',
            'invalid-item' => 'Seçilen öğe iade için uygun değil.',
            'invalid-resolution-type' => 'Çözüm türü "return" veya "cancel_items" olmalıdır.',
            'qty-exceeds' => 'Talep edilen miktar iade edilebilir miktarı aşıyor.',
            'created' => 'İade talebi başarıyla oluşturuldu.',
            'status-updated' => 'İade durumu başarıyla güncellendi.',
            'invalid-status' => 'Geçersiz iade durumu.',
            'reopened' => 'İade talebi başarıyla yeniden açıldı.',
            'reopen-not-allowed' => 'Bu iade talebi yeniden açılamaz.',
            'refund-failed' => 'Bu iade için geri ödeme oluşturulamadı.',
            'message-sent' => 'Mesaj başarıyla gönderildi.',
            'no-permission' => 'Bu işlemi gerçekleştirme izniniz yok.',
            'reason-not-found' => 'İade nedeni bulunamadı.',
            'reason-deleted' => 'İade nedeni başarıyla silindi.',
            'status-not-found' => 'İade durumu bulunamadı.',
            'status-deleted' => 'İade durumu başarıyla silindi.',
            'status-default-undeletable' => 'Varsayılan bir iade durumu silinemez.',
            'rule-not-found' => 'İade kuralı bulunamadı.',
            'rule-deleted' => 'İade kuralı başarıyla silindi.',
            'custom-field-not-found' => 'İade özel alanı bulunamadı.',
            'custom-field-deleted' => 'İade özel alanı başarıyla silindi.',
            'custom-field-options-required' => 'Bu alan türü için en az bir seçenek gereklidir.',
            'mass-indices-required' => 'En az bir kimlik gereklidir.',
            'mass-delete-success' => 'Seçilen öğeler silindi.',
            'mass-update-success' => 'Seçilen öğeler güncellendi.',
            'mass-value-invalid' => 'Durum değeri 0 veya 1 olmalıdır.',
            'image-not-found' => 'İade görseli bulunamadı.',
            'attachment-not-found' => 'Mesaj eki bulunamadı.',
        ],

        'eu-withdrawal' => [
            'not-found' => 'Cayma beyanı bulunamadı.',
            'no-permission' => 'AB cayma taleplerini yönetme izniniz yok.',
            'declined' => 'Cayma talebi reddedildi olarak işaretlendi.',
            'refunded' => 'Cayma talebi iade edildi olarak işaretlendi.',
            'confirmation-resent' => 'Onay e-postası başarıyla yeniden gönderildi.',
            'confirmation-failed' => 'Onay e-postası gönderilemedi.',
        ],

        'reporting' => [
            'invalid-entity' => 'Bilinmeyen raporlama varlığı.',
            'invalid-type' => 'Bu varlık için bilinmeyen raporlama istatistik türü.',
            'export-format-unsupported' => 'Yalnızca csv, xls ve xlsx dışa aktarma biçimleri desteklenir.',
        ],

        'configuration' => [
            'unauthenticated' => 'Kimliğiniz doğrulanmadı.',
            'no-permission' => 'Yapılandırmayı güncelleme izniniz yok.',
            'slug-required' => '`slug` sorgu parametresi gereklidir.',
            'slug-not-found' => 'Verilen slug için kayıtlı bir yapılandırma bölümü yok.',
            'values-required' => '`values` altında en az bir değer gereklidir.',
            'scope-escape' => '`:key` anahtarı istenen slug alt ağacının dışında.',
            'unknown-field' => '`:key` anahtarı kayıtlı bir yapılandırma alanı değil.',
            'validation-failed' => 'Bir veya daha fazla yapılandırma değeri doğrulamadan geçemedi.',
            'file-upload-rest-only' => '`:field` alanı için ikili dosya yüklemeleri yalnızca REST multipart uç noktası üzerinden desteklenir, GraphQL üzerinden desteklenmez.',
            'custom-view-readonly' => '`:field` alanı blade ile oluşturulan özel bir alandır ve API üzerinden güncellenemez.',
            'update-success' => 'Yapılandırma başarıyla güncellendi.',
        ],

        'customer' => [
            'not-found' => 'Müşteri bulunamadı.',
            'no-permission' => 'Bu işlemi gerçekleştirme izniniz yok.',
            'has-active-orders' => 'Müşterinin bekleyen veya işlenmekte olan siparişleri olduğundan silinemez.',
            'deleted' => 'Müşteri başarıyla silindi.',
            'mass-delete-indices-required' => 'En az bir müşteri kimliği gereklidir.',
            'mass-delete-success' => 'Müşteriler silindi.',
            'mass-update-indices-required' => 'En az bir müşteri kimliği gereklidir.',
            'mass-update-value-invalid' => 'Durum değeri 0 veya 1 olmalıdır.',
            'mass-update-success' => 'Müşteri durumu güncellendi.',

            'address' => [
                'not-found' => 'Adres bulunamadı.',
                'not-owned' => 'Bu adres belirtilen müşteriye ait değil.',
                'customer-id-required' => 'Adres sahibini belirlemek için customerId gereklidir.',
                'deleted' => 'Adres başarıyla silindi.',
            ],

            'note' => [
                'empty' => 'Not metni gereklidir.',
                'created' => 'Not başarıyla eklendi.',
            ],

            'review' => [
                'not-found' => 'Değerlendirme bulunamadı.',
                'no-permission' => 'Bu işlemi gerçekleştirme izniniz yok.',
                'invalid-status' => 'Durum şunlardan biri olmalıdır: pending, approved, disapproved.',
                'updated' => 'Değerlendirme başarıyla güncellendi.',
                'deleted' => 'Değerlendirme başarıyla silindi.',
                'mass-delete-indices-required' => 'En az bir değerlendirme kimliği gereklidir.',
                'mass-delete-success' => 'Değerlendirmeler silindi.',
                'mass-update-indices-required' => 'En az bir değerlendirme kimliği gereklidir.',
                'mass-update-value-invalid' => 'Durum değeri şunlardan biri olmalıdır: pending, approved, disapproved.',
                'mass-update-success' => 'Değerlendirme durumu güncellendi.',
            ],

            'group' => [
                'not-found' => 'Müşteri grubu bulunamadı.',
                'deleted' => 'Müşteri grubu başarıyla silindi.',
                'is-system' => 'Sistem müşteri grupları silinemez.',
                'has-customers' => 'Müşteri grubuna bağlı müşteriler olduğundan silinemez.',
                'system-code-immutable' => 'Bir sistem müşteri grubunun kodu değiştirilemez.',
                'system-flag-immutable' => 'Bir sistem müşteri grubunun is_user_defined işareti değiştirilemez.',
                'mass-delete-indices-required' => 'En az bir müşteri grubu kimliği gereklidir.',
                'mass-delete-success' => 'Müşteri grupları silindi.',
            ],

            'gdpr' => [
                'not-found' => 'GDPR talebi bulunamadı.',
                'no-permission' => 'Bu işlemi gerçekleştirme izniniz yok.',
                'deleted' => 'GDPR talebi başarıyla silindi.',
                'invalid-status' => 'Durum şunlardan biri olmalıdır: pending, processing, declined, approved, revoked.',
                'no-changes' => 'Durum veya mesajdan en az biri gereklidir.',
                'already-processed' => 'Bu GDPR talebi zaten işlendi ve yeniden çalıştırılamaz.',

                'process' => [
                    'approved' => 'GDPR talebi onaylandı.',
                    'deleted' => 'GDPR talebi onaylandı ve müşteri silindi.',
                ],
            ],
        ],

        'order' => [
            'not-found' => 'Sipariş bulunamadı.',

            'reorder' => [
                'success' => 'Yeniden sipariş başarılı. Yeni bir taslak sepet oluşturuldu.',
                'cannot-reorder' => 'Bu sipariş yeniden verilemez (misafir siparişi veya bir ya da daha fazla öğe artık satılabilir değil).',
                'failed' => 'Yeniden sipariş başarısız oldu. Lütfen tekrar deneyin.',
                'guest-not-supported' => 'Misafir siparişleri için yeniden sipariş desteklenmez.',
                'items-not-saleable' => 'Bu siparişteki bir veya daha fazla öğe artık satın alınamaz.',
                'no-permission' => 'Sipariş oluşturma izniniz yok.',
                'disabled-in-settings' => 'Yönetici tarafından yeniden sipariş, mağaza ayarlarında devre dışı bırakılmış.',
            ],

            'actions' => [
                'cancel' => [
                    'closed' => 'Kapatılmış siparişler iptal edilemez.',
                    'fraud' => 'Dolandırıcılık olarak işaretlenmiş siparişler iptal edilemez.',
                    'nothing-to-cancel' => 'Bu siparişte iptal edilecek bir şey yok.',
                    'already-processed' => 'Bu sipariş zaten faturalandırılmış veya kargoya verilmiş olduğundan artık iptal edilemez. Bunun yerine geri ödeme yapabilirsiniz.',
                    'no-permission' => 'Siparişleri iptal etme izniniz yok.',
                    'success' => 'Sipariş başarıyla iptal edildi.',
                    'failed' => 'Sipariş iptal edilemedi.',
                ],

                'comment' => [
                    'empty' => 'Yorum gereklidir.',
                    'success' => 'Yorum siparişe eklendi.',
                    'failed' => 'Yorum eklenemedi.',
                ],

                'invoice' => [
                    'closed' => 'Kapatılmış siparişler faturalandırılamaz.',
                    'fraud' => 'Dolandırıcılık olarak işaretlenmiş siparişler faturalandırılamaz.',
                    'nothing-to-invoice' => 'Bu siparişte faturalandırılacak bir şey yok.',
                    'already-invoiced' => 'Bu sipariş için zaten bir fatura oluşturulmuş.',
                    'paypal-standard-blocked' => 'PayPal Standard ile ödenen siparişler için fatura oluşturulamaz.',
                    'no-permission' => 'Fatura oluşturma izniniz yok.',
                    'items-required' => 'Pozitif miktara sahip en az bir öğe gereklidir.',
                    'qty-exceeds' => 'Talep edilen miktar (:requested), :sku SKU\'su için mevcut miktarı (:available) aşıyor.',
                    'not-found' => 'Fatura bulunamadı.',
                    'success' => 'Fatura başarıyla oluşturuldu.',
                    'failed' => 'Fatura oluşturulamadı.',
                    'pdf-failed' => 'Fatura PDF\'i oluşturulamadı.',
                    'email-invalid' => 'Geçerli bir alıcı e-postası gereklidir.',
                    'duplicate-sent' => 'Fatura e-postası :email adresine gönderildi.',
                    'mass-status-indices-required' => 'En az bir fatura kimliği gereklidir.',
                    'mass-status-value-invalid' => 'Durum şunlardan biri olmalıdır: pending, paid, overdue.',
                    'mass-status-success' => 'Fatura durumu başarıyla güncellendi.',
                ],

                'shipment' => [
                    'closed' => 'Kapatılmış siparişler kargoya verilemez.',
                    'fraud' => 'Dolandırıcılık olarak işaretlenmiş siparişler kargoya verilemez.',
                    'nothing-to-ship' => 'Bu siparişte kargoya verilecek bir şey yok.',
                    'no-permission' => 'Siparişleri kargoya verme izniniz yok.',
                    'source-required' => 'Envanter kaynağı gereklidir.',
                    'items-required' => 'Pozitif miktara sahip en az bir öğe gereklidir.',
                    'qty-exceeds' => 'Talep edilen miktar (:requested), :sku SKU\'su için mevcut miktarı (:available) aşıyor.',
                    'inventory-insufficient' => 'Seçilen kaynaktaki envanter :sku SKU\'su için yetersiz.',
                    'not-found' => 'Gönderi bulunamadı.',
                    'success' => 'Gönderi başarıyla oluşturuldu.',
                    'failed' => 'Gönderi oluşturulamadı.',
                ],

                'refund' => [
                    'closed' => 'Kapatılmış siparişler için geri ödeme yapılamaz.',
                    'fraud' => 'Dolandırıcılık olarak işaretlenmiş siparişler için geri ödeme yapılamaz.',
                    'nothing-to-refund' => 'Bu siparişte geri ödenecek bir şey kalmadı.',
                    'no-permission' => 'Geri ödeme oluşturma izniniz yok.',
                    'qty-exceeds' => 'Talep edilen miktar (:requested), :sku SKU\'su için mevcut miktarı (:available) aşıyor.',
                    'amount-zero' => 'Hesaplanan geri ödeme tutarı sıfır. Miktar, kargo veya düzeltme değerlerini ayarlayın.',
                    'amount-exceeds-max' => 'Geri ödeme tutarı (:amount), geri ödenebilecek en yüksek tutarı (:max) aşıyor.',
                    'not-found' => 'Geri ödeme bulunamadı.',
                    'success' => 'Geri ödeme başarıyla oluşturuldu.',
                    'failed' => 'Geri ödeme oluşturulamadı.',
                ],
            ],
        ],

        'appearance' => [
            'no-permission' => 'Görünümü yönetme izniniz yok.',

            'theme' => [
                'not-found' => 'Tema bulunamadı.',
                'not-installed' => 'Bu tema kurulu değil.',
                'channel-ids-required' => 'En az bir kanal gereklidir.',
                'unknown-channel' => 'Bir veya daha fazla kanal mevcut değil.',
                'activated' => 'Tema başarıyla etkinleştirildi.',
            ],

            'section' => [
                'not-found' => 'Bölüm bulunamadı.',
                'invalid-payload' => 'Desteklenmeyen bölüm verisi.',
                'options-required' => 'Seçenekler gereklidir.',
                'status-required' => 'Durum gereklidir.',
                'section-ids-required' => 'En az bir bölüm gereklidir.',
                'reorder-single-scope' => 'Bölümler aynı anda yalnızca tek bir tema ve kanal içinde yeniden sıralanabilir.',
                'reorder-incomplete' => 'Yeniden sıralama, temanın ve kanalın tüm bölümlerini görüntülenmeleri gereken sırayla gerektirir.',
                'unknown-section' => 'Bir veya daha fazla bölüm mevcut değil.',
                'file-required' => 'Bir dosya gereklidir.',
                'created' => 'Bölüm başarıyla oluşturuldu.',
                'updated' => 'Bölüm başarıyla güncellendi.',
                'deleted' => 'Bölüm başarıyla silindi.',
                'draft-saved' => 'Bölüm değişiklikleri başarıyla hazırlandı.',
                'status-staged' => 'Bölüm durumu başarıyla hazırlandı.',
                'order-staged' => 'Bölüm sırası başarıyla hazırlandı.',
                'duplicated' => 'Bölüm başarıyla kopyalandı.',
                'published' => 'Bölüm değişiklikleri başarıyla yayımlandı.',
                'discarded' => 'Bölüm değişiklikleri başarıyla iptal edildi.',
                'media-uploaded' => 'Medya başarıyla yüklendi.',
            ],
        ],

        'sales' => [
            'no-permission' => 'Bu satış kaynağını görüntüleme izniniz yok.',

            'invoice' => [
                'not-found' => 'Fatura bulunamadı.',
                'no-permission' => 'Faturaları görüntüleme izniniz yok.',
            ],

            'shipment' => [
                'not-found' => 'Gönderi bulunamadı.',
                'no-permission' => 'Gönderileri görüntüleme izniniz yok.',
            ],

            'refund' => [
                'not-found' => 'Geri ödeme bulunamadı.',
                'no-permission' => 'Geri ödemeleri görüntüleme izniniz yok.',
                'export-format-unsupported' => 'Yalnızca csv, xls ve xlsx dışa aktarmaları desteklenir.',
            ],

            'transaction' => [
                'not-found' => 'İşlem bulunamadı.',
                'no-permission' => 'İşlemleri görüntüleme izniniz yok.',

                'create' => [
                    'invoice-required' => 'invoiceId alanı gereklidir.',
                    'payment-method-required' => 'paymentMethod alanı gereklidir.',
                    'amount-invalid' => 'Tutar alanı gereklidir ve sayısal olmalıdır.',
                    'invoice-missing' => 'Belirtilen fatura bulunamadı.',
                    'already-paid' => 'Bu fatura zaten tamamen ödenmiş.',
                    'amount-exceeds' => 'Ödeme tutarı fatura genel toplamını aşıyor.',
                    'amount-zero' => 'Ödeme tutarı sıfırdan büyük olmalıdır.',
                ],
            ],

            'booking' => [
                'not-found' => 'Rezervasyon bulunamadı.',
                'no-permission' => 'Rezervasyonları görüntüleme izniniz yok.',
            ],

            'export' => [
                'format-unsupported' => 'Yalnızca csv, xls ve xlsx dışa aktarmaları desteklenir.',
            ],
        ],

        'cart' => [
            'not-found' => 'Sepet bulunamadı.',
            'not-draft' => 'Bu sepet yönetici API\'si üzerinden değiştirilemez.',
            'product-required' => 'productId gereklidir.',
            'product-not-found' => 'Ürün bulunamadı.',
            'booking-unsupported' => 'Rezervasyon ürünleri yönetici taslak siparişine eklenemez. Rezervasyon satın alımları müşteri mağazası üzerinden yapılmalıdır.',
            'product-not-saleable' => 'Bu ürün satışa uygun değil (stokta yok veya devre dışı) ve siparişe eklenemez.',
            'qty-required' => 'qty gereklidir ve itemId => miktar biçiminde boş olmayan bir nesne olmalıdır.',
            'cart-item-required' => 'cartItemId gereklidir.',
            'item-added' => 'Öğe sepete eklendi.',
            'item-add-failed' => 'Öğe sepete eklenemedi.',
            'item-updated' => 'Sepet öğeleri güncellendi.',
            'item-update-failed' => 'Sepet öğeleri güncellenemedi.',
            'item-removed' => 'Öğe sepetten kaldırıldı.',
            'item-remove-failed' => 'Sepet öğesi kaldırılamadı.',
            'address-saved' => 'Adres kaydedildi.',
            'address-required' => 'Fatura adresi gereklidir.',
            'address-incomplete' => 'Adresi kaydetmek için :field gereklidir.',
            'coupon-code-required' => 'Kupon kodu gereklidir.',
            'coupon-applied' => 'Kupon uygulandı.',
            'coupon-already-applied' => 'Kupon bu sepete zaten uygulanmış.',
            'coupon-not-found' => 'Kupon bulunamadı veya etkin değil.',
            'coupon-removed' => 'Kupon kaldırıldı.',
            'coupon-error' => 'Kupon uygulanırken bir hata oluştu.',
            'unknown-error' => 'Bir hata oluştu.',
            'draft-created' => 'Taslak sepet oluşturuldu.',
            'draft-failed' => 'Taslak sepet oluşturulamadı.',
            'addresses-required-for-shipping' => 'Kargo yöntemi seçilmeden önce adresler kaydedilmelidir.',
            'shipping-required-for-payment' => 'Ödeme yönteminden önce kargo yöntemi seçilmelidir.',
            'shipping-method-required' => 'Kargo yöntemi gereklidir.',
            'shipping-method-saved' => 'Kargo yöntemi kaydedildi.',
            'shipping-method-failed' => 'Kargo yöntemi kaydedilemedi.',
            'payment-method-required' => 'Ödeme yöntemi gereklidir.',
            'payment-method-saved' => 'Ödeme yöntemi kaydedildi.',
            'payment-method-failed' => 'Ödeme yöntemi kaydedilemedi.',

            'place-order' => [
                'success' => 'Sipariş başarıyla verildi.',
                'failed' => 'Sipariş verilemedi.',
                'error' => 'Sipariş verilemedi: sepette hatalar var.',
                'minimum-order-error' => 'Minimum sipariş tutarı :amount. Siparişi vermeden önce daha fazla öğe ekleyin.',
                'empty-cart' => 'Sepet boş — sipariş vermeden önce öğe ekleyin.',
                'addresses-required' => 'Sipariş vermeden önce fatura ve teslimat adresleri kaydedilmelidir.',
                'shipping-required' => 'Sipariş vermeden önce bir kargo yöntemi seçilmelidir.',
                'payment-required' => 'Sipariş vermeden önce bir ödeme yöntemi seçilmelidir.',
                'payment-method-unsupported' => 'Yönetici tarafından verilen siparişler için yalnızca cashondelivery ve moneytransfer desteklenir.',
            ],
        ],

        'product' => [
            'not-found' => 'Ürün bulunamadı.',

            'mass-delete-success' => 'Ürünler başarıyla silindi.',
            'mass-update-status-success' => 'Ürün durumları başarıyla güncellendi.',
            'indices-required' => 'indices alanı gereklidir ve boş olmayan bir dizi olmalıdır.',
            'indices-invalid' => 'indices alanı tam sayı ürün kimliklerinden oluşan bir dizi olmalıdır.',
            'value-invalid' => 'value alanı gereklidir ve 0 veya 1 olmalıdır.',
            'mass-delete-failed' => 'Bir veya daha fazla ürün silinemedi.',
            'no-permission' => 'Ürünleri yönetme izniniz yok.',

            'copy-success' => 'Ürün başarıyla kopyalandı.',
            'copy-failed' => 'Ürün kopyalanamadı.',
            'copy-variant-not-supported' => 'Yapılandırılabilir ürünlerin varyantları kopyalanamaz. Bunun yerine üst yapılandırılabilir ürünü kopyalayın.',

            'create' => [
                'created' => 'Ürün başarıyla oluşturuldu.',
                'create-failed' => 'Ürün oluşturulamadı.',
                'sku-required' => 'sku alanı gereklidir.',
                'sku-unique' => 'Bu sku zaten kullanılıyor.',
                'sku-invalid' => 'sku yalnızca harf, rakam ve kısa çizgi içerebilir (ör. "sp-001").',
                'attribute-family-required' => 'attribute_family_id alanı gereklidir.',
                'attribute-family-not-found' => 'Seçilen nitelik ailesi mevcut değil.',
                'type-required' => 'type alanı gereklidir.',
                'type-not-yet-supported' => '":type" ürün türü bu API tarafından desteklenmiyor. İzin verilen türler: simple, virtual, downloadable, grouped, bundle, configurable, booking.',
                'super-attributes-required' => 'type=configurable olduğunda super_attributes alanı gereklidir. Nitelik kodundan (veya kimliğinden) option_ids değerlerine bir eşleme sağlayın, ör. { "color": [1,2], "size": [4,5] }.',
                'super-attributes-invalid' => 'super_attributes alanı, nitelik kodundan (veya kimliğinden) boş olmayan option_ids listelerine giden boş olmayan bir eşleme olmalıdır.',
                'no-permission' => 'Ürün oluşturma izniniz yok.',
            ],

            'update' => [
                'updated' => 'Ürün başarıyla güncellendi.',
                'update-failed' => 'Ürün güncellenemedi.',
                'id-required' => 'Bir ürün kimliği gereklidir.',
                'url-key-required' => 'url_key alanı boş olamaz.',
                'url-key-unique' => 'Bu url_key başka bir ürün tarafından zaten kullanılıyor.',
                'special-price-invalid' => 'special_price, price değerinden küçük bir sayı olmalıdır.',
                'special-price-date-range-invalid' => 'special_price_to, special_price_from ile aynı veya sonraki bir tarih olmalıdır.',
                'boolean-field-invalid' => ':field alanı 0 veya 1 olmalıdır.',
                'categories-invalid' => 'categories alanı kategori kimliklerinden oluşan bir dizi olmalıdır.',
                'channels-invalid' => 'channels alanı kanal kimliklerinden oluşan bir dizi olmalıdır.',
                'super-attributes-cannot-change' => 'super_attributes, ürün oluşturulduktan sonra değiştirilemez.',
                'sub-resource-stripped-images' => 'Görseller POST /api/admin/catalog/products/{id}/images üzerinden yönetilmelidir. images alanı yok sayıldı.',
                'sub-resource-stripped-inventories' => 'Envanterler PUT /api/admin/catalog/products/{id}/inventories üzerinden yönetilmelidir. inventories alanı yok sayıldı.',
                'sub-resource-stripped-customer-group-prices' => 'Müşteri grubu fiyatları /api/admin/catalog/products/{id}/customer-group-prices üzerinden yönetilmelidir. customer_group_prices alanı yok sayıldı.',
                'translations-single-locale' => 'Yalnızca istenen yerel ayar güncellendi. :locales yerel ayar(lar)ını güncellemek için her biri için ?locale=<code> ile ayrı bir istek gönderin.',
            ],

            'delete' => [
                'deleted' => 'Ürün başarıyla silindi.',
                'delete-failed' => 'Ürün silinemedi.',
            ],

            'image' => [
                'uploaded' => 'Ürün görseli başarıyla yüklendi.',
                'updated' => 'Ürün görseli başarıyla güncellendi.',
                'update-empty' => 'Güncellemek için bir alternatif metin veya konum gönderin.',
                'reordered' => 'Ürün görselleri başarıyla yeniden sıralandı.',
                'deleted' => 'Ürün görseli başarıyla silindi.',
                'image-required' => 'Bir görsel dosyası gereklidir (multipart alanı "image").',
                'image-invalid-type' => 'Görsel türü şunlardan biri olmalıdır: bmp, jpeg, jpg, png, webp.',
                'image-too-large' => 'Görsel çok büyük. İzin verilen en büyük boyut 4 MB.',
                'not-found' => 'Ürün görseli bulunamadı.',
                'no-permission' => 'Ürün görsellerini yönetme izniniz yok.',
                'upload-failed' => 'Ürün görseli yüklenemedi.',
                'reorder-failed' => 'Ürün görselleri yeniden sıralanamadı.',
                'delete-failed' => 'Ürün görseli silinemedi.',
                'order-required' => '"order" dizisi gereklidir ve boş olmamalıdır.',
                'order-invalid' => '"order" içindeki her girdi id ve position içeren bir nesne olmalıdır.',
                'id-not-on-product' => ':id görseli bu ürüne ait değil.',
                'graphql-upload-unsupported' => 'GraphQL üzerinden görsel yükleme desteklenmiyor. multipart/form-data ile POST /api/admin/catalog/products/{productId}/images kullanın.',
            ],

            'downloadable' => [
                'file-required' => 'Bir dosya gereklidir (multipart alanı "file").',
                'file-too-large' => 'Dosya çok büyük. İzin verilen en büyük boyut 128 MB.',
                'upload-failed' => 'İndirilebilir dosya yüklenemedi.',
                'file-not-found' => 'Bu ürün ve nitelik için kayıtlı dosya bulunamadı.',
                'no-permission' => 'İndirilebilir ürün dosyalarını yönetme izniniz yok.',
            ],

            'video' => [
                'uploaded' => 'Ürün videosu başarıyla yüklendi.',
                'deleted' => 'Ürün videosu başarıyla silindi.',
                'video-required' => 'Bir video dosyası gereklidir (multipart alanı "video").',
                'video-invalid-type' => 'Video türü şunlardan biri olmalıdır: mp4, webm, ogg, mov.',
                'video-too-large' => 'Video çok büyük. İzin verilen en büyük boyut 100 MB.',
                'not-found' => 'Ürün videosu bulunamadı.',
                'no-permission' => 'Ürün videolarını yönetme izniniz yok.',
                'upload-failed' => 'Ürün videosu yüklenemedi.',
                'delete-failed' => 'Ürün videosu silinemedi.',
                'graphql-upload-unsupported' => 'GraphQL üzerinden video yükleme desteklenmiyor. multipart/form-data ile POST /api/admin/catalog/products/{productId}/videos kullanın.',
            ],

            'inventory' => [
                'updated' => 'Ürün envanterleri başarıyla kaydedildi.',
                'inventories-required' => 'inventories alanı gereklidir ve inventory_source_id değerinden miktara giden boş olmayan bir eşleme olmalıdır.',
                'inventories-invalid' => 'inventories alanı, inventory_source_id (pozitif tam sayı) değerinden miktara (negatif olmayan tam sayı) giden bir eşleme olmalıdır.',
                'source-not-found' => ':id envanter kaynağı mevcut değil.',
                'qty-invalid' => 'Her miktar negatif olmayan bir tam sayı olmalıdır.',
                'no-permission' => 'Ürün envanterlerini yönetme izniniz yok.',
                'not-found' => 'Ürün bulunamadı.',
            ],

            'customer-group-price' => [
                'created' => 'Müşteri grubu fiyatı başarıyla eklendi.',
                'updated' => 'Müşteri grubu fiyatı başarıyla güncellendi.',
                'deleted' => 'Müşteri grubu fiyatı başarıyla silindi.',
                'qty-required' => 'qty alanı gereklidir.',
                'qty-invalid' => 'qty alanı 1\'e eşit veya 1\'den büyük bir tam sayı olmalıdır.',
                'value-type-required' => 'value_type alanı gereklidir.',
                'value-type-invalid' => 'value_type alanı "fixed" veya "discount" olmalıdır.',
                'value-required' => 'value alanı gereklidir.',
                'value-invalid' => 'value alanı negatif olmayan bir sayı olmalıdır.',
                'customer-group-not-found' => 'Seçilen müşteri grubu mevcut değil.',
                'duplicate-qty-group' => 'Bu ürün için bu (qty, customer_group_id) birleşimine ait bir müşteri grubu fiyatı zaten mevcut.',
                'not-found' => 'Müşteri grubu fiyatı bulunamadı.',
                'no-permission' => 'Ürün müşteri grubu fiyatlarını yönetme izniniz yok.',
            ],
        ],

        'category' => [
            'not-found' => 'Kategori bulunamadı.',

            'created' => 'Kategori başarıyla oluşturuldu.',
            'updated' => 'Kategori başarıyla güncellendi.',
            'deleted' => 'Kategori başarıyla silindi.',
            'slug-required' => 'slug alanı gereklidir.',
            'slug-unique' => 'Bu slug zaten kullanılıyor.',
            'name-required' => 'name alanı gereklidir.',
            'position-required' => 'position alanı gereklidir.',
            'attributes-required' => 'En az bir filtrelenebilir nitelik gereklidir.',
            'description-required-for-mode' => 'Seçilen görüntüleme modu için açıklama gereklidir.',
            'cannot-delete-root' => 'Kök ve kanal kökü kategorileri silinemez.',
            'cannot-delete-channel-root' => 'Bu kategori bir kanal köküdür ve silinemez.',
            'delete-failed' => 'Kategori silinemedi.',
            'no-permission' => 'Kategorileri yönetme izniniz yok.',
            'mass-delete-success' => 'Kategoriler başarıyla silindi.',
            'mass-update-status-success' => 'Kategori durumları başarıyla güncellendi.',
            'mass-delete-indices-required' => 'indices alanı gereklidir ve boş olmayan bir dizi olmalıdır.',
            'mass-update-status-value-required' => 'value alanı gereklidir ve 0 veya 1 olmalıdır.',
        ],

        'attribute' => [
            'not-found' => 'Nitelik bulunamadı.',

            'create-success' => 'Nitelik başarıyla oluşturuldu.',
            'update-success' => 'Nitelik başarıyla güncellendi.',
            'delete-success' => 'Nitelik başarıyla silindi.',
            'delete-failed' => 'Nitelik silinemedi.',
            'mass-delete-success' => 'Nitelikler başarıyla silindi.',
            'mass-delete-indices-required' => 'indices alanı gereklidir ve boş olmayan bir dizi olmalıdır.',

            'code-immutable' => 'Nitelik oluşturulduktan sonra nitelik kodu değiştirilemez.',
            'type-immutable' => 'Niteliğin ürün değerleri varken nitelik türü değiştirilemez.',
            'locale-scope-immutable' => 'Niteliğin ürün değerleri varken yerel ayar kapsamı değiştirilemez.',
            'system-attribute' => 'Sistem nitelikleri silinemez.',
            'in-use-family' => 'Nitelik bir veya daha fazla nitelik ailesinin parçası (grup kimlikleri: :ids). Önce onu bu ailelerden kaldırın.',

            'option-not-found' => 'Nitelik seçeneği bulunamadı.',
            'option-not-supported' => '":type" nitelik türü seçenekleri desteklemiyor. Yalnızca select, multiselect ve checkbox nitelikleri seçeneklere sahip olabilir.',
            'option-in-use' => 'Bu seçenek :count ürün tarafından kullanılıyor ve silinemez.',
            'option-delete-success' => 'Nitelik seçeneği başarıyla silindi.',
        ],

        'family' => [
            'not-found' => 'Nitelik ailesi bulunamadı.',

            'created' => 'Nitelik ailesi başarıyla oluşturuldu.',
            'updated' => 'Nitelik ailesi başarıyla güncellendi.',
            'deleted' => 'Nitelik ailesi başarıyla silindi.',
            'code-required' => 'Aile kodu gereklidir.',
            'code-unique' => 'Aile kodu benzersiz olmalıdır.',
            'name-required' => 'Aile adı gereklidir.',
            'group-code-required' => 'Her nitelik grubunun bir kodu olmalıdır.',
            'group-name-required' => 'Her nitelik grubunun bir adı olmalıdır.',
            'group-column-invalid' => 'Her nitelik grubu sütunu 1 veya 2 olmalıdır.',
            'group-name-duplicate' => 'Nitelik grubu adı nitelik ailesi içinde benzersiz olmalıdır.',
            'update-failed' => 'Nitelik ailesi güncellenemedi.',
            'create-failed' => 'Nitelik ailesi oluşturulamadı.',
            'default-delete-error' => 'Varsayılan nitelik ailesi silinemez.',
            'last-delete-error' => 'En az bir nitelik ailesi gereklidir.',
            'attribute-product-error' => 'Bu nitelik ailesi bir veya daha fazla ürün tarafından kullanılıyor. Silmeden önce bu ürünleri yeniden atayın.',
            'delete-failed' => 'Nitelik ailesi silinemedi.',
            'no-permission' => 'Nitelik ailelerini yönetme izniniz yok.',
        ],

        'settings' => [
            'locale' => [
                'not-found' => 'Yerel ayar bulunamadı.',
                'created' => 'Yerel ayar başarıyla oluşturuldu.',
                'updated' => 'Yerel ayar başarıyla güncellendi.',
                'deleted' => 'Yerel ayar başarıyla silindi.',
                'cannot-delete-last' => 'En az bir yerel ayar gereklidir — kalan son yerel ayar silinemez.',
                'cannot-delete-channel-default' => 'Bu yerel ayar bir veya daha fazla kanalın varsayılan yerel ayarıdır ve silinemez.',
                'delete-failed' => 'Yerel ayar silinemedi.',
                'no-permission' => 'Yerel ayarları yönetme izniniz yok.',
                'mass-delete-success' => 'Yerel ayarlar başarıyla silindi.',
                'mass-delete-indices-required' => 'indices alanı gereklidir ve boş olmayan bir dizi olmalıdır.',
            ],

            'theme' => [
                'not-found' => 'Tema özelleştirmesi bulunamadı.',
                'created' => 'Tema özelleştirmesi başarıyla oluşturuldu.',
                'updated' => 'Tema özelleştirmesi başarıyla güncellendi.',
                'deleted' => 'Tema özelleştirmesi başarıyla silindi.',
                'delete-failed' => 'Tema özelleştirmesi silinemedi.',
                'no-permission' => 'Tema özelleştirmelerini yönetme izniniz yok.',
                'mass-delete-success' => 'Tema özelleştirmeleri başarıyla silindi.',
                'mass-delete-indices-required' => 'indices alanı gereklidir ve boş olmayan bir dizi olmalıdır.',
                'mass-update-success' => 'Tema özelleştirmeleri başarıyla güncellendi.',
                'mass-update-indices-required' => 'indices alanı gereklidir ve boş olmayan bir dizi olmalıdır.',
                'mass-update-value-invalid' => 'value alanı 0 veya 1 olmalıdır.',
            ],

            'inventory-source' => [
                'not-found' => 'Envanter kaynağı bulunamadı.',
                'created' => 'Envanter kaynağı başarıyla oluşturuldu.',
                'updated' => 'Envanter kaynağı başarıyla güncellendi.',
                'deleted' => 'Envanter kaynağı başarıyla silindi.',
                'code-unique' => 'Bu koda sahip bir envanter kaynağı zaten mevcut.',
                'last-delete-error' => 'En az bir envanter kaynağı gereklidir — kalan son kaynak silinemez.',
                'in-use' => 'Bu envanter kaynağına ürün envanterleri tarafından başvuruluyor ve silinemez.',
                'delete-failed' => 'Envanter kaynağı silinemedi.',
                'no-permission' => 'Envanter kaynaklarını yönetme izniniz yok.',
                'mass-delete-success' => 'Envanter kaynakları başarıyla silindi.',
                'mass-delete-indices-required' => 'indices alanı gereklidir ve boş olmayan bir dizi olmalıdır.',
            ],

            'exchange-rate' => [
                'not-found' => 'Döviz kuru bulunamadı.',
                'created' => 'Döviz kuru başarıyla oluşturuldu.',
                'updated' => 'Döviz kuru başarıyla güncellendi.',
                'deleted' => 'Döviz kuru başarıyla silindi.',
                'duplicate-pair' => 'Bu hedef para birimi için zaten bir döviz kuru mevcut. Mevcut satırı güncelleyin veya önce silin.',
                'delete-failed' => 'Döviz kuru silinemedi.',
                'no-permission' => 'Döviz kurlarını yönetme izniniz yok.',
                'mass-delete-success' => 'Döviz kurları başarıyla silindi.',
                'mass-delete-indices-required' => 'indices alanı gereklidir ve boş olmayan bir dizi olmalıdır.',
                'update-rates-success' => 'Döviz kurları başarıyla güncellendi.',
                'update-rates-failed' => 'Döviz kurları harici sağlayıcıdan güncellenemedi.',
                'provider-not-configured' => 'Yapılandırılmış harici bir döviz kuru sağlayıcısı yok.',
            ],

            'tax-rate' => [
                'not-found' => 'Vergi oranı bulunamadı.',
                'created' => 'Vergi oranı başarıyla oluşturuldu.',
                'updated' => 'Vergi oranı başarıyla güncellendi.',
                'deleted' => 'Vergi oranı başarıyla silindi.',
                'delete-failed' => 'Vergi oranı silinemedi.',
                'no-permission' => 'Vergi oranlarını yönetme izniniz yok.',
                'zip-code-required' => 'is_zip false olduğunda zip_code alanı gereklidir.',
                'zip-range-required' => 'is_zip true olduğunda hem zip_from hem de zip_to gereklidir.',
            ],

            'tax-category' => [
                'not-found' => 'Vergi kategorisi bulunamadı.',
                'created' => 'Vergi kategorisi başarıyla oluşturuldu.',
                'updated' => 'Vergi kategorisi başarıyla güncellendi.',
                'deleted' => 'Vergi kategorisi başarıyla silindi.',
                'code-unique' => 'Bu kod zaten kullanılıyor.',
                'cannot-delete-in-use' => 'Bu vergi kategorisine hâlâ bağlı vergi oranları var ve silinemez.',
                'delete-failed' => 'Vergi kategorisi silinemedi.',
                'no-permission' => 'Vergi kategorilerini yönetme izniniz yok.',
            ],

            'role' => [
                'not-found' => 'Rol bulunamadı.',
                'created' => 'Rol başarıyla oluşturuldu.',
                'updated' => 'Rol başarıyla güncellendi.',
                'deleted' => 'Rol başarıyla silindi.',
                'permissions-required' => 'permission_type custom olduğunda permissions alanı gereklidir.',
                'cannot-delete-in-use' => 'Bu rol bir veya daha fazla yöneticiye atanmış ve silinemez.',
                'cannot-delete-last-role' => 'En az bir rol gereklidir — kalan son rol silinemez.',
                'cannot-change-last-all-access' => 'Tam erişimli tek rol özel bir role dönüştürülemez.',
                'delete-failed' => 'Rol silinemedi.',
                'no-permission' => 'Rolleri yönetme izniniz yok.',
            ],

            'currency' => [
                'not-found' => 'Para birimi bulunamadı.',
                'created' => 'Para birimi başarıyla oluşturuldu.',
                'updated' => 'Para birimi başarıyla güncellendi.',
                'deleted' => 'Para birimi başarıyla silindi.',
                'code-required' => 'code alanı gereklidir.',
                'code-size' => 'Kod tam olarak 3 alfabetik karakterden oluşmalıdır.',
                'code-unique' => 'Bu kod zaten kullanılıyor.',
                'name-required' => 'name alanı gereklidir.',
                'cannot-delete-last' => 'En az bir para birimi gereklidir — kalan son para birimi silinemez.',
                'cannot-delete-channel-base' => 'Bu para birimi bir veya daha fazla kanalın temel para birimidir ve silinemez.',
                'delete-failed' => 'Para birimi silinemedi.',
                'no-permission' => 'Para birimlerini yönetme izniniz yok.',

                'mass-delete-success' => 'Para birimleri başarıyla silindi.',
                'mass-delete-indices-required' => 'indices alanı gereklidir ve boş olmayan bir dizi olmalıdır.',
            ],

            'channel' => [
                'not-found' => 'Kanal bulunamadı.',

                'created' => 'Kanal başarıyla oluşturuldu.',
                'updated' => 'Kanal başarıyla güncellendi.',
                'deleted' => 'Kanal başarıyla silindi.',
                'code-unique' => 'Bu kod zaten kullanılıyor.',
                'hostname-unique' => 'Bu ana makine adı zaten kullanılıyor.',
                'default-locale-mismatch' => 'default_locale_id, locales listesinde yer almalıdır.',
                'base-currency-mismatch' => 'base_currency_id, currencies listesinde yer almalıdır.',
                'unknown-locale' => 'Bir veya daha fazla yerel ayar kimliği mevcut değil.',
                'unknown-currency' => 'Bir veya daha fazla para birimi kimliği mevcut değil.',
                'unknown-inventory-source' => 'Bir veya daha fazla inventory_source kimliği mevcut değil.',
                'unknown-root-category' => 'root_category_id mevcut değil.',
                'cannot-delete-last' => 'En az bir kanal gereklidir — kalan son kanal silinemez.',
                'cannot-delete-default' => 'Varsayılan uygulama kanalı silinemez.',
                'delete-failed' => 'Kanal silinemedi.',
                'no-permission' => 'Kanalları yönetme izniniz yok.',
            ],

            'user' => [
                'not-found' => 'Yönetici kullanıcı bulunamadı.',
                'created' => 'Yönetici kullanıcı başarıyla oluşturuldu.',
                'updated' => 'Yönetici kullanıcı başarıyla güncellendi.',
                'deleted' => 'Yönetici kullanıcı başarıyla silindi.',
                'name-required' => 'name alanı gereklidir.',
                'email-required' => 'email alanı gereklidir.',
                'email-invalid' => 'E-posta geçerli bir e-posta adresi olmalıdır.',
                'email-unique' => 'Bu e-posta zaten kullanılıyor.',
                'password-required' => 'password alanı gereklidir.',
                'password-min' => 'Parola en az 6 karakter olmalıdır.',
                'role-required' => 'role_id alanı gereklidir.',
                'role-not-found' => 'Seçilen rol mevcut değil.',
                'status-invalid' => 'Durum 0 veya 1 olmalıdır.',
                'cannot-delete-self' => 'Kendi yönetici hesabınızı silemezsiniz.',
                'cannot-delete-last-admin' => 'En az bir yönetici kullanıcı gereklidir — kalan son yönetici silinemez.',
                'cannot-change' => 'Tam erişimli son yöneticinin :name değeri değiştirilemez.',
                'delete-failed' => 'Yönetici kullanıcı silinemedi.',
                'no-permission' => 'Yönetici kullanıcıları yönetme izniniz yok.',
                'self-delete-password-required' => 'Kendi hesabınızı silmek için password alanı gereklidir.',
                'self-delete-incorrect-password' => 'Parola yanlış.',
                'self-deleted' => 'Yönetici hesabınız silindi.',
            ],

            'data-transfer' => [
                'import' => [
                    'not-found' => 'İçe aktarma bulunamadı.',
                    'deleted' => 'İçe aktarma başarıyla silindi.',
                    'cancelled' => 'İçe aktarma başarıyla iptal edildi.',
                    'cannot-cancel' => 'Yalnızca bekleyen veya işlenmekte olan içe aktarmalar iptal edilebilir. Geçerli durum: :state.',
                    'no-permission' => 'Veri aktarımı içe aktarmalarını yönetme izniniz yok.',
                    'delete-failed' => 'İçe aktarma silinemedi.',
                    'created' => 'İçe aktarma başarıyla oluşturuldu.',
                    'updated' => 'İçe aktarma başarıyla güncellendi.',
                    'type-invalid' => 'Seçilen tür geçersiz.',
                    'action-invalid' => 'İşlem şunlardan biri olmalıdır: append, delete.',
                    'validation-strategy-invalid' => 'Doğrulama stratejisi şunlardan biri olmalıdır: stop-on-errors, skip-errors.',
                    'allowed-errors-invalid' => 'İzin verilen hata sayısı 0 veya daha büyük bir tam sayı olmalıdır.',
                    'field-separator-required' => 'Alan ayırıcı gereklidir.',
                    'file-required' => 'Bir dosya gereklidir.',
                    'file-invalid-type' => 'Dosya şunlardan biri olmalıdır: csv, xls, xlsx, xml.',
                    'nothing-to-import' => 'İçe aktarılacak bir şey yok.',
                    'not-valid' => 'İçe aktarma geçerli değil.',
                    'setup-queue-error' => 'Kuyruk bağlantısı sync olarak ayarlı; içe aktarmaları arka planda işlemek için gerçek bir kuyruk yapılandırın.',
                    'file-not-found' => 'İndirilebilecek bir dosya yok.',
                    'error-report-not-found' => 'İndirilebilecek bir hata raporu yok.',
                    'sample-not-found' => 'İstenen tür ve biçim için örnek dosya yok.',
                    'sample-format-invalid' => 'Örnek biçimi şunlardan biri olmalıdır: csv, xls, xlsx, xml.',
                    'images-archive-too-large' => 'Görsel arşivi en fazla :entries dosya içerebilir ve açıldığında en fazla :size MB olabilir.',
                ],
            ],
        ],

        'cms' => [
            'page' => [
                'not-found' => 'CMS sayfası bulunamadı.',

                'created' => 'CMS sayfası başarıyla oluşturuldu.',
                'url-key-required' => 'url_key alanı gereklidir.',
                'url-key-unique' => 'Bu url_key zaten kullanılıyor.',
                'url-key-invalid' => 'url_key biçimi geçersiz. Yalnızca küçük harf, rakam ve kısa çizgi kullanın.',
                'page-title-required' => 'page_title alanı gereklidir.',
                'html-content-required' => 'html_content alanı gereklidir.',
                'channels-required' => 'En az bir kanal seçilmelidir.',
                'channels-invalid' => 'Bir veya daha fazla kanal geçersiz.',

                'updated' => 'CMS sayfası başarıyla güncellendi.',

                'deleted' => 'CMS sayfası başarıyla silindi.',
                'delete-failed' => 'CMS sayfası silinemedi.',

                'mass-deleted' => 'CMS sayfaları başarıyla silindi.',
                'indices-required' => 'indices alanı gereklidir ve boş olmayan bir dizi olmalıdır.',
                'indices-invalid' => 'indices alanı CMS sayfası kimliklerinden oluşan bir dizi olmalıdır.',

                'no-permission' => 'CMS sayfalarını yönetme izniniz yok.',
            ],
        ],

        'marketing' => [
            'catalog-rule' => [
                'not-found' => 'Katalog kuralı bulunamadı.',
                'created' => 'Katalog kuralı başarıyla oluşturuldu.',
                'updated' => 'Katalog kuralı başarıyla güncellendi.',
                'deleted' => 'Katalog kuralı başarıyla silindi.',
                'delete-failed' => 'Katalog kuralı silinemedi.',
                'mass-delete-indices-required' => 'Toplu silme için katalog kuralı kimliği sağlanmadı.',
                'mass-delete-success' => 'Seçilen katalog kuralları başarıyla silindi.',
                'no-permission' => 'Katalog kurallarını yönetme izniniz yok.',
            ],

            'campaign' => [
                'not-found' => 'Kampanya bulunamadı.',
                'created' => 'Kampanya başarıyla oluşturuldu.',
                'updated' => 'Kampanya başarıyla güncellendi.',
                'deleted' => 'Kampanya başarıyla silindi.',
                'delete-failed' => 'Kampanya silinemedi.',
                'no-permission' => 'Kampanyaları yönetme izniniz yok.',

                'send' => [
                    'id-required' => 'Kampanya kimliği gereklidir.',
                    'inactive' => 'Etkin olmayan bir kampanya gönderilemez. Önce kampanyayı etkinleştirin.',
                    'queued' => 'Kampanya :count alıcı için kuyruğa alındı.',
                ],
            ],

            'sitemap' => [
                'not-found' => 'Site haritası bulunamadı.',
                'created' => 'Site haritası başarıyla oluşturuldu.',
                'updated' => 'Site haritası başarıyla güncellendi.',
                'deleted' => 'Site haritası başarıyla silindi.',
                'delete-failed' => 'Site haritası silinemedi.',
                'no-permission' => 'Site haritalarını yönetme izniniz yok.',

                'generate' => [
                    'id-required' => 'Site haritası kimliği gereklidir.',
                    'no-channels' => 'Bu site haritası hiçbir kanalı kapsamadığından oluşturulacak bir şey yok. Önce ona en az bir kanal atayın.',
                    'success' => 'Site haritası başarıyla yeniden oluşturuldu.',
                    'failed' => 'Site haritası oluşturulamadı: :message',
                ],
            ],

            'cart-rule' => [
                'not-found' => 'Sepet kuralı bulunamadı.',
                'id-required' => 'Bir sepet kuralı kimliği gereklidir.',
                'copy-of' => ':value kopyası',
                'copied' => 'Sepet kuralı başarıyla kopyalandı.',
                'created' => 'Sepet kuralı başarıyla oluşturuldu.',
                'updated' => 'Sepet kuralı başarıyla güncellendi.',
                'deleted' => 'Sepet kuralı başarıyla silindi.',
                'delete-failed' => 'Sepet kuralı silinemedi.',
                'no-permission' => 'Sepet kurallarını yönetme izniniz yok.',
                'mass-delete-success' => 'Sepet kuralları başarıyla silindi.',
                'mass-delete-indices-required' => 'indices alanı gereklidir ve boş olmayan bir dizi olmalıdır.',
                'action-type-invalid' => 'action_type şunlardan biri olmalıdır: by_percent, by_fixed, cart_fixed, buy_x_get_y.',
                'coupon-code-required' => 'coupon_type=1 ve use_auto_generation=0 olduğunda coupon_code gereklidir.',
                'coupon-code-unique' => 'Bu kupon kodu zaten kullanılıyor.',
            ],

            'template' => [
                'campaign-associated' => 'Bu e-posta şablonu bir kampanya tarafından kullanıldığından silinemez. Önce onu kampanyadan kaldırın.',
                'not-found' => 'E-posta şablonu bulunamadı.',
                'created' => 'E-posta şablonu başarıyla oluşturuldu.',
                'updated' => 'E-posta şablonu başarıyla güncellendi.',
                'deleted' => 'E-posta şablonu başarıyla silindi.',
                'delete-failed' => 'E-posta şablonu silinemedi.',
                'status-invalid' => 'Durum şunlardan biri olmalıdır: active, inactive, draft.',
                'no-permission' => 'E-posta şablonlarını yönetme izniniz yok.',
            ],

            'event' => [
                'campaign-associated' => 'Bu etkinlik bir kampanya tarafından kullanıldığından silinemez. Önce onu kampanyadan kaldırın.',
                'not-found' => 'Pazarlama etkinliği bulunamadı.',
                'created' => 'Pazarlama etkinliği başarıyla oluşturuldu.',
                'updated' => 'Pazarlama etkinliği başarıyla güncellendi.',
                'deleted' => 'Pazarlama etkinliği başarıyla silindi.',
                'delete-failed' => 'Pazarlama etkinliği silinemedi.',
                'no-permission' => 'Pazarlama etkinliklerini yönetme izniniz yok.',
            ],

            'search-synonym' => [
                'not-found' => 'Arama eş anlamlısı bulunamadı.',
                'created' => 'Arama eş anlamlısı başarıyla oluşturuldu.',
                'updated' => 'Arama eş anlamlısı başarıyla güncellendi.',
                'deleted' => 'Arama eş anlamlısı başarıyla silindi.',
                'mass-deleted' => 'Arama eş anlamlıları başarıyla silindi.',
                'delete-failed' => 'Arama eş anlamlısı silinemedi.',
                'indices-required' => 'indices alanı gereklidir ve arama eş anlamlısı kimliklerinden oluşan boş olmayan bir dizi olmalıdır.',
                'no-permission' => 'Arama eş anlamlılarını yönetme izniniz yok.',
            ],

            'url-rewrite' => [
                'not-found' => 'URL yeniden yazma kuralı bulunamadı.',
                'created' => 'URL yeniden yazma kuralı başarıyla oluşturuldu.',
                'updated' => 'URL yeniden yazma kuralı başarıyla güncellendi.',
                'deleted' => 'URL yeniden yazma kuralı başarıyla silindi.',
                'delete-failed' => 'URL yeniden yazma kuralı silinemedi.',
                'mass-deleted' => 'URL yeniden yazma kuralları başarıyla silindi.',
                'indices-required' => 'En az bir URL yeniden yazma kuralı kimliği gereklidir.',
                'no-permission' => 'URL yeniden yazma kurallarını yönetme izniniz yok.',
            ],

            'cart-rule-coupon' => [
                'cart-rule-not-found' => 'Sepet kuralı bulunamadı.',
                'not-found' => 'Kupon bulunamadı.',
                'code-taken' => 'Bu koda sahip bir kupon zaten mevcut.',
                'format-invalid' => 'Biçim şunlardan biri olmalıdır: alphabetic, alphanumeric, numeric.',
                'indices-required' => 'indices alanı gereklidir ve kupon kimliklerinden oluşan boş olmayan bir dizi olmalıdır.',
                'created' => 'Kupon başarıyla oluşturuldu.',
                'generated' => ':count kupon başarıyla oluşturuldu.',
                'deleted' => 'Kupon başarıyla silindi.',
                'mass-deleted' => ':count kupon başarıyla silindi.',
                'no-permission' => 'Sepet kuralı kuponlarını yönetme izniniz yok.',
            ],

            'subscriber' => [
                'not-found' => 'Bülten abonesi bulunamadı.',
                'updated' => 'Abonelik başarıyla güncellendi.',
                'deleted' => 'Abonelik başarıyla silindi.',
                'is-subscribed-required' => 'is_subscribed alanı gereklidir.',
                'no-permission' => 'Bülten abonelerini yönetme izniniz yok.',
            ],

            'search-term' => [
                'not-found' => 'Arama terimi bulunamadı.',
                'updated' => 'Arama terimi başarıyla güncellendi.',
                'deleted' => 'Arama terimi başarıyla silindi.',
                'mass-deleted' => 'Arama terimleri başarıyla silindi.',
                'term-required' => 'term alanı gereklidir.',
                'redirect-url-invalid' => 'redirect_url geçerli bir URL olmalıdır.',
                'indices-required' => 'indices içinde en az bir kimlik sağlanmalıdır.',
                'no-permission' => 'Arama terimlerini yönetme izniniz yok.',
            ],
        ],
        'customer-api-sessions' => [
            'menu' => [
                'title' => 'API Oturumları',
            ],

            'acl' => [
                'title' => 'API Oturumları',
                'delete' => 'İptal Et',
            ],

            'index' => [
                'title' => 'Müşteri API Oturumları',
                'info' => 'Web, mobil, POS veya masaüstü gibi uygulamalardan mağaza API\'sine giriş yapmış müşteriler. Bir oturumu iptal etmek o cihazın oturumunu hemen kapatır.',
            ],

            'fields' => [
                'customer' => 'Müşteri',
                'email' => 'E-posta',
                'channel' => 'Kanal',
            ],

            'datagrid' => [
                'revoke-all' => 'Müşterinin Tüm Oturumlarını İptal Et',
            ],

            'card' => [
                'title' => 'API Oturumları (:count)',
                'empty' => 'Bu müşterinin etkin API oturumu yok.',
            ],

            'errors' => [
                'no-permission' => 'Müşteri API oturumlarını yönetme izniniz yok.',
            ],
        ],

        'customer-auth' => [
            'configuration' => [
                'title' => 'Müşteri Kimlik Doğrulaması',
                'info' => 'Mağaza müşteri girişleri için belirteç süreleri, sınırlar ve oturum kuralları.',

                'tokens' => [
                    'title' => 'Belirteç Süreleri',
                    'info' => 'Erişim belirteçlerinin, yenileme belirteçlerinin ve oturumların ne kadar geçerli kalacağı. Değişiklikler sonradan verilen belirteçlere uygulanır.',
                    'access-token-ttl' => 'Erişim Belirteci Süresi (dakika)',
                    'access-token-ttl-info' => '5 ile 60 dakika arası. Varsayılan 15.',
                    'refresh-token-ttl' => 'Yenileme Belirteci Süresi (gün)',
                    'refresh-token-ttl-info' => '1 ile 90 gün arası. Varsayılan 30.',
                    'session-lifetime' => 'En Uzun Oturum Süresi (gün)',
                    'session-lifetime-info' => '1 ile 365 gün arası. Varsayılan 90. Bundan sonra oturum kaç kez yenilenmiş olursa olsun müşteri yeniden giriş yapmalıdır.',
                    'max-sessions' => 'Müşteri Başına En Fazla Etkin Oturum',
                    'max-sessions-info' => 'Sınıra ulaşıldığında en eski oturum sonlandırılır. 0 ile 100 arası. 0 sınır yok demektir.',
                ],

                'rate-limits' => [
                    'title' => 'İstek Sınırları',
                    'info' => 'Giriş yapmış müşteri başına dakikada en fazla mağaza API isteği.',
                    'requests-per-minute' => 'Giriş Yapmış Müşteri Başına Dakikalık İstek',
                    'requests-per-minute-info' => '1 ile 100000 arası. Varsayılan 120.',
                ],

                'login-protection' => [
                    'title' => 'Giriş Koruması',
                    'info' => 'İstemci beklemek zorunda kalmadan önce kaç başarısız giriş ve belirteç yenilemesine izin verildiği.',
                    'login-max-attempts' => 'Kilitlenmeden Önce Başarısız Giriş',
                    'login-max-attempts-info' => 'E-posta ve IP adresi başına. 3 ile 20 arası. Varsayılan 5.',
                    'login-lockout-seconds' => 'Giriş Kilidi (saniye)',
                    'login-lockout-seconds-info' => '60 ile 3600 arası. Varsayılan 60.',
                    'refresh-requests-per-minute' => 'Oturum Başına Dakikalık Belirteç Yenileme',
                    'refresh-requests-per-minute-info' => '1 ile 60 arası. Varsayılan 10.',
                ],

                'security' => [
                    'title' => 'Güvenlik',
                    'info' => 'Bir müşterinin diğer oturumlarını neyin sonlandırdığı.',
                    'revoke-sessions-on-password-change' => 'Parola Değişince Oturumları İptal Et',
                ],

                'retention' => [
                    'title' => 'Veri Saklama',
                    'info' => 'Sona eren müşteri oturumlarının ne kadar süre saklanacağı.',
                    'session-prune-days' => 'Sona Eren Oturumları Sakla (gün)',
                    'session-prune-days-info' => 'İptal edilen veya süresi dolan oturumlar bu kadar gün sonra silinir. 1 ile 365 arası. Varsayılan 30.',
                ],
            ],
        ],
        'auth' => [
            'configuration' => [
                'title' => 'Yönetici Kimlik Doğrulaması',
                'info' => 'Yönetici API girişleri ve API belirteçleri için belirteç süreleri, istek sınırları ve güvenlik kuralları.',

                'tokens' => [
                    'title' => 'Belirteç Süreleri',
                    'info' => 'Erişim belirteçlerinin, yenileme belirteçlerinin ve oturumların ne kadar geçerli kalacağı. Değişiklikler sonradan verilen belirteçlere uygulanır.',
                    'access-token-ttl' => 'Erişim Belirteci Süresi (dakika)',
                    'access-token-ttl-info' => '5 ile 60 dakika arası. Varsayılan 15.',
                    'refresh-token-ttl' => 'Yenileme Belirteci Süresi (gün)',
                    'refresh-token-ttl-info' => '1 ile 90 gün arası. Varsayılan 30.',
                    'session-lifetime' => 'En Uzun Oturum Süresi (gün)',
                    'session-lifetime-info' => '1 ile 365 gün arası. Varsayılan 90. Bundan sonra oturum kaç kez yenilenmiş olursa olsun yönetici yeniden giriş yapmalıdır.',
                ],

                'rate-limits' => [
                    'title' => 'İstek Sınırları',
                    'info' => 'Dakika başına en fazla yönetici API isteği.',
                    'session-requests-per-minute' => 'Giriş Yapmış Yönetici Başına Dakikalık İstek',
                    'api-token-requests-per-minute' => 'API Belirteci Başına Dakikalık İstek',
                    'session-requests-per-minute-info' => '1 ile 100000 arası. Varsayılan 60.',
                    'api-token-requests-per-minute-info' => '1 ile 100000 arası. Varsayılan 60.',
                    'unauthenticated-requests-per-minute' => 'Belirteç Olmadan IP Adresi Başına Dakikalık İstek',
                    'unauthenticated-requests-per-minute-info' => '10 ile 1000 arası. Varsayılan 60. Giriş, iki faktörlü doğrulama, yenileme ve parola isteklerini kapsar.',
                ],

                'security' => [
                    'title' => 'Güvenlik',
                    'info' => 'İptal kuralları, API belirteci süre sonu ve alan adı bağlama.',
                    'revoke-sessions-on-password-change' => 'Parola Değişince Oturumları İptal Et',
                    'revoke-api-tokens-on-password-change' => 'Parola Değişince API Belirteçlerini İptal Et',
                    'allow-non-expiring-api-tokens' => 'Süresiz API Belirteçlerine İzin Ver',
                    'domain-binding-mode' => 'Alan Adı Bağlama Modu',
                    'domain-binding-mode-info' => 'DNS, isteğin belirtecin alan adının çözümlendiği bir IP adresinden geldiğini denetler. Origin, Origin veya Referer başlığını denetler ve yalnızca tarayıcılar için uygundur.',
                    'domain-binding-dns' => 'DNS (istemci IP adresi)',
                    'domain-binding-origin' => 'Origin başlığı',
                ],

                'login-protection' => [
                    'title' => 'Giriş Koruması',
                    'info' => 'İstemci beklemek zorunda kalmadan önce kaç başarısız giriş, yanlış iki faktörlü kod ve parola isteğine izin verildiği.',
                    'login-max-attempts' => 'Kilitlenmeden Önce Başarısız Giriş',
                    'login-max-attempts-info' => 'E-posta ve IP adresi başına. 3 ile 20 arası. Varsayılan 5.',
                    'login-lockout-seconds' => 'Giriş Kilidi (saniye)',
                    'login-lockout-seconds-info' => '60 ile 3600 arası. Varsayılan 60.',
                    'two-factor-challenge-lifetime' => 'İki Faktörlü Doğrulama Süresi (dakika)',
                    'two-factor-challenge-lifetime-info' => '1 ile 15 arası. Varsayılan 5.',
                    'two-factor-max-failures' => 'Dakika Başına Yanlış İki Faktörlü Kod',
                    'two-factor-max-failures-info' => 'Yönetici başına, tüm doğrulamalarda. 3 ile 20 arası. Varsayılan 5.',
                    'password-requests-per-minute' => 'Dakika Başına Parola İsteği',
                    'password-requests-per-minute-info' => 'IP adresi başına parolamı unuttum ve sıfırlama istekleri. 1 ile 20 arası. Varsayılan 5.',
                ],

                'retention' => [
                    'title' => 'Veri Saklama',
                    'info' => 'Sona eren oturumların ve API denetim geçmişinin ne kadar süre saklanacağı.',
                    'session-prune-days' => 'Sona Eren Oturumları Sakla (gün)',
                    'session-prune-days-info' => 'İptal edilen veya süresi dolan oturumlar bu kadar gün sonra silinir. 1 ile 365 arası. Varsayılan 30.',
                    'audit-retention-days' => 'API Denetim Geçmişini Sakla (gün)',
                    'audit-retention-days-info' => 'Daha eski kayıtlar her gün silinir. 0 ile 3650 arası. Boş veya 0 bunları süresiz saklar.',
                ],
            ],

            'errors' => [
                'unauthenticated' => 'Kimliğiniz doğrulanmadı.',
                'access-token-expired' => 'Erişim belirtecinin süresi doldu. Devam etmek için yenileyin.',
                'api-token-expired' => 'API belirtecinin süresi doldu.',
                'refresh-token-invalid' => 'Yenileme belirteci geçersiz veya süresi dolmuş. Lütfen yeniden giriş yapın.',
                'api-token-revoked' => 'API belirteci iptal edildi.',
                'api-token-domain-mismatch' => 'API belirteci bu alan adından kullanılamaz.',
                'access-token-required' => 'Bu işlem bir oturum açma oturumunun erişim belirtecini gerektirir; API belirteçleri bunu kullanamaz.',
                'two-factor-required' => 'Girişi tamamlamak için kimlik doğrulama uygulamanızdaki 6 haneli kodu veya yedek kodlarınızdan birini girin.',
                'too-many-login-attempts' => 'Çok fazla giriş denemesi. Lütfen :seconds saniye sonra tekrar deneyin.',
                'two-factor-challenge-invalid' => 'İki faktörlü doğrulama isteği geçersiz veya süresi dolmuş. Lütfen yeniden giriş yapın.',
                'two-factor-attempts-exceeded' => 'Çok fazla hatalı kod. Lütfen yeniden giriş yapın.',
                'password-reset-token-invalid' => 'Bu parola sıfırlama belirteci geçersiz veya süresi dolmuş.',
                'too-many-password-requests' => 'Çok fazla parola isteği. Lütfen :seconds saniye sonra tekrar deneyin.',
                'rate-limit-exceeded' => 'Çok fazla deneme.',
                'no-permission' => 'Yönetici API oturumlarını yönetme izniniz yok.',
            ],

            'sessions' => [
                'logged-out' => 'Oturumunuz kapatıldı.',
                'logged-out-all' => 'Tüm oturumlarınız kapatıldı.',
                'not-found' => 'Oturum bulunamadı.',
                'revoked' => 'Oturum iptal edildi.',
                'revoked-for-admin' => 'İptal edilen oturumlar: :count.',
            ],

            'password' => [
                'reset' => 'Parolanız sıfırlandı. Lütfen yeniden giriş yapın.',
            ],
        ],

        'api-tokens' => [
            'menu' => [
                'title' => 'API Belirteçleri',
            ],

            'acl' => [
                'title' => 'API Belirteçleri',
                'create' => 'Oluştur',
                'delete' => 'İptal Et',
                'manage-others' => 'Diğer Yöneticilerin Belirteçlerini Yönet',
            ],

            'index' => [
                'title' => 'API Belirteçleri',
                'info' => 'ERP, PIM, depo veya muhasebe sistemi gibi sunucu entegrasyonları API belirteçleriyle kimlik doğrular. Her belirteç, rolünün izinleriyle tek bir yönetici olarak davranır ve yalnızca bağlı olduğu alan adından çalışır.',
                'create-btn' => 'API Belirteci Oluştur',
            ],

            'create' => [
                'title' => 'API Belirteci Oluştur',
                'back-btn' => 'Geri',
                'save-btn' => 'Belirteç Oluştur',
                'general' => 'Genel',
                'expiry' => 'Geçerlilik Süresi',
                'admin-info' => 'Belirteç bu yönetici olarak davranır ve tam olarak rolünün izinlerine sahiptir.',
                'domain-info' => 'Entegrasyonun istek gönderdiği ana makine, örneğin erp.example.com. Bir URL ana makinesine indirgenir. IP adresleri ve joker karakterler kabul edilmez ve alan adı sonradan değiştirilemez.',
                'expires-on-date' => 'Bir tarihte sona erer',
                'never-expires' => 'Hiçbir zaman sona ermez',
                'no-expiry-warning' => 'Süresi olmayan bir belirteç iptal edilene kadar çalışır. Bir gizli bilgi kasasında saklayın ve artık gerekmediğinde hemen iptal edin.',
                'no-expiry-confirm' => 'Bu belirtecin hiçbir zaman sona ermeyeceğini anlıyorum.',
                'no-admins' => 'API belirteci oluşturabileceğiniz bir yönetici yok.',
            ],

            'view' => [
                'title' => 'API Belirteci',
                'back-btn' => 'Geri',
                'save-btn' => 'Kaydet',
                'revoke-btn' => 'İptal Et',
                'details' => 'Ayrıntılar',
                'usage' => 'Kullanım',
                'token-once-title' => 'API belirtecinizi şimdi kopyalayın',
                'token-once-info' => 'Belirteç yalnızca bu kez gösterilir. Bir gizli bilgi kasasında saklayın; kaybolursa iptal edip yenisini oluşturun.',
                'copy-btn' => 'Kopyala',
                'copied' => 'Belirteç kopyalandı.',
                'usage-info' => 'REST ve GraphQL isteklerinin Authorization başlığında Bearer belirteci olarak gönderin.',
                'not-yet' => 'Henüz değil',
                'revoke-confirm' => 'Bu API belirteci iptal edilsin mi? Onu kullanan entegrasyon hemen çalışmayı durdurur.',
            ],

            'fields' => [
                'id' => 'ID',
                'name' => 'Ad',
                'admin' => 'Yönetici',
                'token' => 'Belirteç',
                'domain' => 'Alan Adı',
                'expiry' => 'Geçerlilik Süresi',
                'expires-at' => 'Sona Erme Tarihi',
                'no-expiry-confirmation' => 'Süresiz onay',
                'status' => 'Durum',
                'created-by' => 'Oluşturan',
                'created-at' => 'Oluşturulma Tarihi',
                'last-used-at' => 'Son Kullanım',
                'last-ip-address' => 'Son IP Adresi',
                'last-user-agent' => 'Son Kullanıcı Aracısı',
                'last-channel' => 'Son Kanal',
                'resolved-ips' => 'Çözümlenen IP Adresleri',
                'resolved-at' => 'Çözümlenme Tarihi',
                'domain-mismatches' => 'Diğer Alan Adlarından İstekler',
                'last-domain-mismatch-at' => 'Başka Alan Adından Son İstek',
                'revoked-at' => 'İptal Tarihi',
                'revoked-by' => 'İptal Eden',
            ],

            'datagrid' => [
                'never' => 'Hiçbir zaman',
                'console' => 'Konsol komutu',
                'view' => 'Görüntüle',
                'revoke' => 'İptal Et',
            ],

            'status' => [
                'active' => 'Etkin',
                'expired' => 'Süresi Dolmuş',
                'revoked' => 'İptal Edilmiş',
            ],

            'channels' => [
                'rest' => 'REST',
                'graphql' => 'GraphQL',
            ],

            'messages' => [
                'created' => 'API belirteci oluşturuldu.',
                'updated' => 'API belirteci güncellendi.',
                'revoked' => 'API belirteci iptal edildi.',
            ],

            'errors' => [
                'no-permission' => 'API belirteçlerini yönetme izniniz yok.',
                'not-found' => 'API belirteci bulunamadı.',
                'cannot-create-for-admin' => 'Bu yönetici için API belirteci oluşturamazsınız: rolünde sizinkinde olmayan izinler var veya diğer yöneticilerin belirteçlerini yönetemezsiniz.',
                'cannot-manage' => 'Bu API belirtecini yönetemezsiniz.',
                'already-revoked' => 'API belirteci zaten iptal edilmiş.',
                'invalid-name' => 'Ad zorunludur ve en fazla 100 karakter olabilir.',
                'invalid-domain' => 'erp.example.com gibi bir alan adı girin. IP adresleri ve joker karakterler kabul edilmez.',
                'expiry-required' => 'API belirteçlerinin bir sona erme tarihi olmalıdır.',
                'expiry-in-past' => 'Sona erme tarihi gelecekte olmalıdır.',
            ],
        ],

        'api-sessions' => [
            'menu' => [
                'title' => 'API Oturumları',
            ],

            'acl' => [
                'title' => 'API Oturumları',
                'delete' => 'İptal Et',
            ],

            'index' => [
                'title' => 'API Oturumları',
                'info' => 'Web, mobil, POS veya masaüstü uygulamalarından yönetim API\'sine giriş yapmış yöneticiler. Bir oturumu iptal etmek o cihazın oturumunu hemen kapatır.',
            ],

            'fields' => [
                'id' => 'ID',
                'admin' => 'Yönetici',
                'device' => 'Cihaz',
                'client-type' => 'İstemci Türü',
                'ip-address' => 'Giriş IP Adresi',
                'user-agent' => 'Giriş Kullanıcı Aracısı',
                'last-ip-address' => 'Son IP Adresi',
                'last-user-agent' => 'Son Kullanıcı Aracısı',
                'last-used-at' => 'Son Kullanım',
                'last-channel' => 'Son Kanal',
                'refresh-expires-at' => 'Yenileme Bitiş Tarihi',
                'expires-at' => 'Sona Erme Tarihi',
                'status' => 'Durum',
                'created-at' => 'Giriş Tarihi',
            ],

            'datagrid' => [
                'revoke' => 'İptal Et',
                'revoke-all' => 'Bu Yöneticinin Tüm Oturumlarını İptal Et',
            ],

            'client-types' => [
                'web' => 'Web',
                'mobile' => 'Mobil',
                'pos' => 'POS',
                'desktop' => 'Masaüstü',
                'other' => 'Diğer',
            ],

            'errors' => [
                'not-active' => 'Bu oturum zaten iptal edilmiş veya süresi dolmuş.',
            ],
        ],

        'api-audit' => [
            'menu' => [
                'title' => 'API Denetimi',
            ],

            'acl' => [
                'title' => 'API Değişiklik Geçmişi',
                'delete' => 'Geçmişi Sil',
            ],

            'index' => [
                'title' => 'API Değişiklik Geçmişi',
                'info' => 'Admin API aracılığıyla yapılan her oluşturma, güncelleme ve silme işlemi, bunu kimin yaptığı, hangi jetonla yapıldığı ve nelerin değiştiği.',
                'cleanup-btn' => 'Eski günlükleri silin',
                'cleanup-days' => 'Bu sayıdan daha eski günlükleri silin',
                'cleanup-confirm' => 'Belirtilen gün sayısından daha eski olan tüm geçmiş silinsin mi? Bu geri alınamaz.',
            ],

            'view' => [
                'title' => 'Değiştir',
                'back-btn' => 'Geri',
                'admin' => 'Yönetici',
                'token' => 'Jeton',
                'action' => 'Eylem',
                'resource' => 'Kaynak',
                'method' => 'Yöntem',
                'ip' => 'IP Adresi',
                'date' => 'Tarih',
                'version' => 'Sürüm',
                'url' => 'Uç nokta',
                'request-details' => 'Ayrıntıları Talep Et',
                'changes' => 'Değişiklikler',
                'field' => 'Alan',
                'old' => 'Eski değer',
                'new' => 'Yeni değer',
                'no-field-changes' => 'Bu giriş için alan düzeyinde değişiklik kaydedilmedi.',
                'same-request' => 'Aynı istekteki diğer değişiklikler',
                'version-chain' => 'Bu kaydın sürüm geçmişi',
            ],

            'datagrid' => [
                'id' => 'kimlik',
                'date' => 'Tarih',
                'admin' => 'Yönetici',
                'token' => 'Jeton',
                'token-kind' => 'Belirteç Türü',
                'channel' => 'Kanal',
                'operation' => 'Operasyon',
                'resource' => 'Kaynak',
                'method' => 'Yöntem',
                'ip' => 'IP',
                'view' => 'Görüntüle',
                'delete' => 'Sil',
            ],

            'events' => [
                'created' => 'Oluşturuldu',
                'updated' => 'Güncellendi',
                'deleted' => 'Silindi',
            ],

            'token-kinds' => [
                'session' => 'Oturum Açma Oturumu',
                'api-token' => 'API Belirteci',
                'integration' => 'Entegrasyon Belirteci (kaldırıldı)',
                'customer-session' => 'Müşteri Giriş Oturumu',
            ],

            'channels' => [
                'rest' => 'REST',
                'graphql' => 'GraphQL',
                'panel' => 'Yönetim Paneli',
                'cli' => 'Konsol Komutu',
            ],

            'deleted' => ':count geçmiş kaydı silindi.',
            'cleanup-input-required' => 'Temizlemek için birkaç gün veya tarih belirtin.',
        ],

        'api-configuration' => [
            'title' => 'API\'si',
            'info' => 'Bagisto API ve yönetici modüllerine ilişkin ayarlar.',
        ],
    ],
];
