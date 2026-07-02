<?php

return [
    'graphql' => [
        'cart' => [
            'authentication-required'           => 'Kimlik doğrulama jetonu gereklidir',
            'invalid-token'                     => 'Geçersiz veya süresi dolmuş kimlik doğrulama jetonu',
            'unauthorized-access'               => 'Sepete yetkisiz erişim',
            'authenticated-only'                => 'Yalnızca kimliği doğrulanmış kullanıcılar sepetlerini getirebilir',
            'merge-requires-auth'               => 'Misafir birleştirme için kimlik doğrulama gereklidir',
            'unknown-operation'                 => 'Bilinmeyen sepet işlemi',

            'cart-not-found'                    => 'Sepet bulunamadı',
            'guest-cart-not-found'              => 'Misafir sepeti bulunamadı',
            'product-not-found'                 => 'Ürün bulunamadı',

            'product-id-quantity-required'      => 'Ürün kimliği ve miktarı gereklidir',
            'cart-item-id-quantity-required'    => 'Sepet öğesi kimliği ve miktarı gereklidir',
            'cart-item-id-required'             => 'Sepet öğesi kimliği gereklidir',
            'item-ids-required'                 => 'Öğe kimlikleri dizisi gereklidir',
            'coupon-code-required'              => 'Kupon kodu gereklidir',
            'address-data-required'             => 'Ülke, il ve posta kodu gereklidir',

            'add-product-failed'                => 'Ürün sepete eklenemedi',
            'update-item-failed'                => 'Sepet öğesi güncellenemedi',
            'remove-item-failed'                => 'Sepet öğesi kaldırılamadı',
            'apply-coupon-failed'               => 'Kupon uygulanamadı',
            'remove-coupon-failed'              => 'Kupon kaldırılamadı',
            'move-to-wishlist-failed'           => 'Öğe istek listesine taşınamadı',
            'estimate-shipping-failed'          => 'Kargo tahmini yapılamadı',

            'product-added-successfully'         => 'Ürün sepete başarıyla eklendi',
            'guest-cart-merged'                  => 'Misafir sepeti başarıyla birleştirildi',
            'using-authenticated-cart'           => 'Kimliği doğrulanmış müşteri sepeti kullanılıyor',
            'cart-item-not-found'                => 'Sepet öğesi bulunamadı',
            'new-guest-cart-created'             => 'Benzersiz oturum jetonu ile yeni misafir sepeti oluşturuldu',
            'select-items-to-remove'             => 'Lütfen kaldırılacak öğeleri seçin',
            'select-items-to-move-wishlist'      => 'Lütfen istek listesine taşınacak öğeleri seçin',
            'invalid-or-expired-token'           => 'Sepet jetonu geçersiz veya süresi dolmuş. Lütfen yeni bir sepet oluşturun.',
            'invalid-token-of-login-user'        => 'Giriş yapan kullanıcının jetonu geçersiz.',
        ],

        'token-verification' => [
            'invalid-operation'                 => 'Geçersiz işlem',
            'invalid-input-data'                => 'Geçersiz giriş verileri',
            'token-required'                    => 'Jeton gereklidir',
            'invalid-token-format'              => 'Geçersiz jeton biçimi',
            'token-not-found-or-expired'        => 'Jeton bulunamadı veya süresi doldu',
            'customer-not-found'                => 'Müşteri bulunamadı',
            'customer-account-suspended'        => 'Müşteri hesabı askıya alındı',
            'error-verifying-token'             => 'Jeton doğrulanırken hata oluştu',
            'token-is-valid'                    => 'Jeton geçerli',
        ],

        'forgot-password' => [
            'invalid-operation'                 => 'Geçersiz işlem',
            'invalid-input-data'                => 'Geçersiz giriş verileri',
            'email-required'                    => 'E-posta gereklidir',
            'reset-link-sent'                   => 'Sıfırlama bağlantısı e-postanıza başarıyla gönderildi',
            'email-not-found'                   => 'E-posta adresi bulunamadı',
            'error-sending-reset-link'          => 'Sıfırlama bağlantısı gönderilirken bir hata oluştu',
        ],

        'logout' => [
            'invalid-operation'                 => 'Geçersiz işlem',
            'invalid-input-data'                => 'Geçersiz giriş verileri',
            'token-required'                    => 'Jeton gereklidir',
            'invalid-token-format'              => 'Geçersiz jeton biçimi',
            'logged-out-successfully'           => 'Başarıyla çıkış yapıldı',
            'token-not-found-or-expired'        => 'Jeton bulunamadı veya süresi zaten doldu',
            'error-during-logout'               => 'Çıkış sırasında hata oluştu',
        ],

        'address' => [
            'deleted-successfully'              => 'Adres başarıyla silindi',
            'authentication-required'           => 'Kimlik doğrulama jetonu gereklidir',
            'invalid-token'                     => 'Geçersiz veya süresi dolmuş jeton',
            'unknown-operation'                 => 'Bilinmeyen işlem',
            'address-id-required'               => 'Adres kimliği gereklidir',
            'address-not-found'                 => 'Adres bulunamadı veya bu müşteriye ait değil',
            'retrieved'                         => 'Adresler başarıyla alındı',
            'fetch-failed'                      => 'Adresler alınamadı:',
        ],

        'customer-profile' => [
            'authentication-required'           => 'Kimlik doğrulama jetonu gereklidir. Lütfen jetonu sorgu girişinde sağlayın',
            'invalid-token'                     => 'Geçersiz veya süresi dolmuş jeton',
        ],

        'customer' => [
            'password-mismatch'                 => 'Parola ve parola onayı eşleşmiyor',
            'confirm-password-required'         => 'Parola değiştirilirken parola onayı gereklidir',
            'unauthenticated'                   => 'Kimlik doğrulanmadı. Bu işlemi gerçekleştirmek için lütfen giriş yapın',
        ],

        'product-review' => [
            'product-id-required'               => 'Ürün kimliği gereklidir',
            'product-not-found'                 => 'Ürün bulunamadı',
            'rating-invalid'                    => 'Değerlendirme 1 ile 5 arasında olmalıdır',
            'title-required'                    => 'Değerlendirme başlığı gereklidir',
            'comment-required'                  => 'Değerlendirme yorumu gereklidir',
        ],

        'product' => [
            'not-found-with-sku'                => 'No product found with SKU',
            'not-found-with-url-key'            => 'No product found with URL key',
            'parameters-required'               => 'At least one of the following parameters must be provided: "sku", "id", "urlKey"',
        ],

        'auth' => [
            'no-token-provided'                 => 'Kimlik doğrulama jetonu sağlanmadı. Lütfen jetonu Authorization başlığında "Bearer <token>" olarak veya input.token alanında sağlayın',
            'invalid-or-expired-token'          => 'Geçersiz veya süresi dolmuş jeton',
            'request-not-found'                 => 'İstek bağlamda bulunamadı',
            'token-required'                    => 'Authentication token is required. Please provide the token either in the GraphQL mutation input field or in the Authorization header as "Bearer <token>"',
            'unknown-resource'                  => 'Bilinmeyen kaynak',
            'cannot-update-other-profile'       => 'Yetkisiz: Başka bir müşteri profili güncellenemez',
        ],

        'upload' => [
            'invalid-base64'                    => 'Geçersiz base64 kodlu görsel verisi',
            'size-exceeds-limit'                => 'Görsel boyutu 5MB\'ı geçmemelidir',
            'invalid-format'                    => 'Geçersiz görsel biçimi. Lütfen veri URI şeması ile base64 kodlu görsel sağlayın (data:image/jpeg;base64,...)',
            'failed'                            => 'Görsel yüklemesi başarısız oldu',
        ],

        'attribute' => [
            'code-already-exists'               => 'Öznitelik kodu zaten mevcut',
        ],

        'login' => [
            'invalid-credentials'               => 'Geçersiz e-posta veya parola',
            'account-suspended'                 => 'Hesabınız askıya alındı',
            'successful'                        => 'Başarıyla giriş yaptınız',
            'invalid-request'                   => 'Geçersiz giriş isteği',
        ],

        'checkout' => [
            'invalid-input'                     => 'Ödeme işlemi için geçersiz giriş verileri',
            'billing-address-required'          => 'Fatura adresi gereklidir',
            'shipping-address-required'         => 'Gönderiler için teslimat adresi gereklidir',
            'address-save-failed'               => 'Adres kaydedilemedi',
            'address-saved'                     => 'Adres başarıyla kaydedildi',
            'shipping-method-required'          => 'Kargo yöntemi gereklidir',
            'invalid-shipping-method'           => 'Geçersiz veya kullanılamayan kargo yöntemi',
            'shipping-method-save-failed'       => 'Kargo yöntemi kaydedilemedi',
            'shipping-method-saved'             => 'Kargo yöntemi başarıyla kaydedildi',
            'shipping-method-error'             => 'Kargo yöntemi kaydedilirken hata oluştu',
            'payment-method-required'           => 'Ödeme yöntemi gereklidir',
            'invalid-payment-method'            => 'Geçersiz veya kullanılamayan ödeme yöntemi',
            'payment-method-save-failed'        => 'Ödeme yöntemi kaydedilemedi',
            'payment-method-saved'              => 'Ödeme yöntemi başarıyla kaydedildi',
            'payment-method-error'              => 'Ödeme yöntemi kaydedilirken hata oluştu',
            'order-creation-failed'             => 'Sipariş oluşturma başarısız: Sipariş kimliği boş veya sipariş kaydedilmedi',
            'order-retrieval-failed'            => 'Oluşturulan sipariş alınamadı',
            'order-creation-error'              => 'Sipariş oluşturulamadı',
            'cart-empty'                        => 'Sepet boş',
            'account-suspended'                 => 'Hesabınız askıya alındı. Lütfen destek ile iletişime geçin.',
            'account-inactive'                  => 'Hesabınız etkin değil. Lütfen destek ile iletişime geçin.',
            'minimum-order-not-met'             => 'Minimum sipariş tutarı :amount',
            'email-required'                    => 'Sipariş oluşturmak için e-posta adresi gereklidir',
            'unknown-operation'                 => 'Bilinmeyen ödeme işlemi',
        ],

        'customer-addresses' => [
            'token-required'                    => 'Müşteri adreslerini getirmek için jeton gereklidir',
            'invalid-or-expired-token'          => 'Geçersiz veya süresi dolmuş jeton',
            'token-validation-failed'           => 'Jeton doğrulaması başarısız oldu',
        ],

        'product' => [
            'type'                              => 'Ürün Türü',
            'attribute-family'                  => 'Öznitelik Ailesi',
            'sku'                               => 'SKU',
            'name'                              => 'Ad',
            'description'                       => 'Açıklama',
            'short-description'                 => 'Kısa Açıklama',
            'status'                            => 'Durum',
            'new'                               => 'Yeni',
            'featured'                          => 'Öne Çıkan',
            'price'                             => 'Fiyat',
            'special-price'                     => 'Özel Fiyat',
            'weight'                            => 'Ağırlık',
            'cost'                              => 'Maliyet',
            'length'                            => 'Uzunluk',
            'width'                             => 'Genişlik',
            'height'                            => 'Yükseklik',
            'color'                             => 'Renk',
            'size'                              => 'Boyut',
            'brand'                             => 'Marka',
            'super-attributes'                  => 'Süper Öznitelikler',
        ],

        'compare-item' => [
            'id-required'                       => 'Karşılaştır öğesi kimliği gereklidir',
            'invalid-id-format'                 => 'Geçersiz kimlik biçimi. "/api/shop/compare-items/1" veya sayısal kimlik gibi IRI biçimi beklenir',
            'not-found'                         => 'Karşılaştır öğesi bulunamadı',
            'product-id-required'               => 'Ürün kimliği gereklidir',
            'customer-id-required'              => 'Müşteri kimliği gereklidir',
            'product-not-found'                 => 'Ürün bulunamadı',
            'customer-not-found'                => 'Müşteri bulunamadı',
            'already-exists'                    => 'Bu ürün zaten karşılaştırma listenizde var',
        ],

        'downloadable-product' => [
            'download-link-not-found'           => 'İndirme bağlantısı bulunamadı veya süresi dolmuş',
            'purchased-link-not-found'          => 'Satın alınan bağlantı bulunamadı',
            'file-not-found'                    => 'Dosya bulunamadı',
            'download-successful'               => 'Dosya indirilmeye hazır',
            'token-required'                    => 'İndirme jetonu gereklidir',
            'invalid-token'                     => 'İndirme jetonu geçersiz veya süresi dolmuş',
            'token-expired'                     => 'İndirme jetonu süresi dolmuş. Lütfen yeni bir tane oluşturun',
            'access-denied'                     => 'Erişim reddedildi: Bu dosyayı indirme izniniz yok',
            'redirect-external-url'             => 'Harici indirme URL\'sine yeniden yönlendiriliyor',
            'file-error'                        => 'İndirme isteğiniz işlenirken bir hata oluştu',
            'unauthorized-access'               => 'İndirme kaynağına yetkisiz erişim',
        ],
    ],

    'integration' => [
        'menu' => [
            'title'  => 'Entegrasyon',
            'tokens' => 'Jetonlar',
        ],

        'history' => [
            'menu' => [
                'title' => 'Tarih',
            ],

            'acl' => [
                'title'  => 'API Değişiklik Geçmişi',
                'delete' => 'Geçmişi Sil',
            ],

            'index' => [
                'title'           => 'API Değişiklik Geçmişi',
                'info'            => 'Admin API aracılığıyla yapılan her oluşturma, güncelleme ve silme işlemi, bunu kimin yaptığı, hangi jetonla yapıldığı ve nelerin değiştiği.',
                'cleanup-btn'     => 'Eski günlükleri silin',
                'cleanup-days'    => 'Bu sayıdan daha eski günlükleri silin',
                'cleanup-confirm' => 'Belirtilen gün sayısından daha eski olan tüm geçmiş silinsin mi? Bu geri alınamaz.',
            ],

            'view' => [
                'title'             => 'Değiştir',
                'back-btn'          => 'Geri',
                'admin'             => 'Yönetici',
                'token'             => 'Jeton',
                'action'            => 'Eylem',
                'resource'          => 'Kaynak',
                'method'            => 'Yöntem',
                'ip'                => 'IP Adresi',
                'date'              => 'Tarih',
                'version'           => 'Sürüm',
                'url'               => 'Uç nokta',
                'request-details'   => 'Ayrıntıları Talep Et',
                'changes'           => 'Değişiklikler',
                'field'             => 'Alan',
                'old'               => 'Eski değer',
                'new'               => 'Yeni değer',
                'no-field-changes'  => 'Bu giriş için alan düzeyinde değişiklik kaydedilmedi.',
                'same-request'      => 'Aynı istekteki diğer değişiklikler',
                'version-chain'     => 'Bu kaydın sürüm geçmişi',
            ],

            'datagrid' => [
                'id'        => 'kimlik',
                'date'      => 'Tarih',
                'admin'     => 'Yönetici',
                'token'     => 'Jeton',
                'action'    => 'Eylem',
                'operation' => 'Operasyon',
                'resource'  => 'Kaynak',
                'version'   => 'Sürüm',
                'method'    => 'Yöntem',
                'ip'        => 'IP',
                'view'      => 'Görüntüle',
                'delete'    => 'Sil',
            ],

            'events' => [
                'created' => 'Oluşturuldu',
                'updated' => 'Güncellendi',
                'deleted' => 'Silindi',
            ],

            'deleted'                => ':count geçmiş kaydı silindi.',
            'cleanup-input-required' => 'Temizlemek için birkaç gün veya tarih belirtin.',
        ],

        'acl' => [
            'title'      => 'Entegrasyon',
            'create'     => 'Entegrasyon Oluştur',
            'edit'       => 'Entegrasyonu Düzenle',
            'delete'     => 'Entegrasyon Simgesini İptal Et',
            'generate'   => 'Entegrasyon Jetonu Oluştur',
            'regenerate' => 'Entegrasyon Simgesini Yeniden Oluştur',
        ],

        'index' => [
            'title'      => 'Entegrasyonlar',
            'create-btn' => 'Entegrasyon Oluştur',
        ],

        'create' => [
            'title'    => 'Entegrasyon Oluştur',
            'save-btn' => 'Kaydet',
            'back-btn' => 'Geri',
        ],

        'edit' => [
            'title'          => 'Entegrasyonu Düzenle',
            'save-btn'       => 'Kaydet',
            'back-btn'       => 'Geri',
            'generate-btn'   => 'Jeton Oluştur',
            'regenerate-btn' => 'Jetonu Yeniden Oluştur',
            'revoke-btn'     => 'Jetonu İptal Et',
            'copy-btn'       => 'Kopyala',
            'token-warning'  => 'Bu jetonu şimdi kaydedin; bir daha gösterilmeyecek.',
            'token-label'    => 'Jeton',
            'not-generated'  => 'Henüz oluşturulmadı',
            'masked'         => '(Saklanır — oluşturma sırasında yalnızca bir kez gösterilir)',
            'history-banner' => 'Bu jeton artık aktif değil.',
        ],

        'fields' => [
            'name'                  => 'İsim',
            'description'           => 'Açıklama',
            'assign-user'           => 'Kullanıcı Ata',
            'permission-type'       => 'İzin Türü',
            'access-control'        => 'Erişim Kontrolü',
            'general'               => 'Genel',
            'token-settings'        => 'Jeton Ayarları',
            'valid-till'            => 'Geçerlilik Tarihi:',
            'rate-limit-per-minute' => 'Hız Sınırı (dakika başına)',
            'rate-limit-per-day'    => 'Fiyat Sınırı (günlük)',
            'never-expires'         => 'Hiçbir zaman süresi dolmaz',
            'expires-on'            => 'Süresi doluyor',
            'unlimited'             => 'Sınırsız',
            'limit-to'              => 'Sınırla',
            'requests-per-minute'   => 'istek / dakika',
            'requests-per-day'      => 'istekler / gün',
            'select-admin'          => 'Bir yönetici seçin',
            'no-available-admins'   => 'Yönetici yok; her yöneticinin zaten etkin bir jetonu vardır.',
            'same-as-web-hint'      => 'Token, atanan yöneticinin mevcut rol izinlerini canlı olarak yansıtacaktır.',
            'ip-allowlist'          => 'IP İzin Verilenler Listesi',
            'ip-any'                => 'Herhangi bir IP (varsayılan)',
            'ip-restricted'         => 'Belirli IP\'lerle sınırlıdır',
            'ip-list-hint'          => 'Her satıra bir giriş. IPv4, IPv6 ve CIDR\'yi destekler (örn. 10.0.0.0/24 veya 2001:db8::/32). Tüm IP\'lere izin vermek için boş bırakın.',
        ],

        'permission_type' => [
            'all'         => 'Hepsi',
            'custom'      => 'Özel',
            'same_as_web' => 'Web İzni ile aynı',
        ],

        'status' => [
            'draft'       => 'Taslak',
            'active'      => 'Aktif',
            'revoked'     => 'İptal edildi',
            'regenerated' => 'Yenilendi',
        ],

        'datagrid' => [
            'id'              => 'kimlik',
            'name'            => 'İsim',
            'admin'           => 'Yönetici',
            'token'           => 'Jeton',
            'status'          => 'Durum',
            'permission-type' => 'İzin Türü',
            'expires-at'      => 'Geçerlilik Tarihi:',
            'last-used-at'    => 'Son Kullanılan',
            'created-at'      => 'Oluşturulma Tarihi',
            'edit'            => 'Düzenle',
            'revoke'          => 'İptal et',
        ],

        'messages' => [
            'draft-created'          => 'Entegrasyon oluşturuldu. Kullanmaya başlamak için jetonu oluşturun.',
            'updated'                => 'Entegrasyon başarıyla güncellendi.',
            'generated'              => 'Belirteç oluşturuldu. Şimdi kopyalayın; bir daha gösterilmeyecek.',
            'regenerated'            => 'Jeton yeniden oluşturuldu. Yeni jetonu şimdi kopyalayın; bir daha gösterilmeyecek.',
            'revoked'                => 'Jeton başarıyla iptal edildi.',
            'generate-only-draft'    => 'Yalnızca taslak entegrasyonların jetonları oluşturulabilir.',
            'regenerate-only-active' => 'Yalnızca aktif jetonlar yeniden oluşturulabilir.',
            'cannot-edit-historic'   => 'İptal edilen veya yeniden oluşturulan jetonlar düzenlenemez.',
            'already-inactive'       => 'Bu jeton zaten etkin değil.',
        ],

        'errors' => [
            'admin-has-token' => 'Seçilen yöneticinin zaten etkin bir entegrasyon jetonu var.',
        ],

        'validation' => [
            'ip-invalid'           => 'İzin verilen her IP, geçerli bir IPv4 veya IPv6 adresi olmalıdır (CIDR gösterimi desteklenir).',
            'cidr-prefix-invalid'  => 'Belirtilen IP sürümü için CIDR öneki geçersiz.',
        ],

        'configuration' => [
            'api' => [
                'title' => 'API\'si',
                'info'  => 'Bagisto API ve yönetici modüllerine ilişkin ayarlar.',
            ],
            'integration' => [
                'title' => 'Entegrasyon',
                'info'  => 'Yönetici API belirteçlerini vermek için kullanılan API Entegrasyonu eklentisini yönetin.',
            ],
            'settings' => [
                'title'  => 'Modül Ayarları',
                'info'   => 'API Entegrasyonu eklentisini etkinleştirin veya devre dışı bırakın. Devre dışı bırakıldığında kenar çubuğu menüsü gizlenir ve sayfaları 404 değerini döndürür.',
                'enable' => 'API Entegrasyon Modülünü Etkinleştir',
            ],
        ],

        'emails' => [
            'generated' => [
                'subject'  => 'Yeni bir API jetonu oluşturuldu: :name',
                'greeting' => 'Hesabınızda az önce ":name" adlı bir API entegrasyon jetonu oluşturuldu.',
            ],
            'regenerated' => [
                'subject'  => 'API jetonunuz yeniden oluşturuldu: :name',
                'greeting' => '":name" adlı API entegrasyon jetonu az önce yeniden oluşturuldu. Önceki jeton çalışmayı durdurdu; yalnızca yenisi geçerli.',
            ],
            'revoked' => [
                'subject'  => 'API jetonunuz iptal edildi: :name',
                'greeting' => '":name" adlı API entegrasyon jetonu iptal edildi. Bunu kullanan herhangi bir istemci erişimi kaybetti.',
            ],

            'details' => [
                'name' => 'Jeton Adı',
                'date' => 'Tarih',
                'ip'   => 'IP\'den',
            ],

            'revoke-hint'   => 'Bunu beklemiyorsanız aşağıdaki düğmeyi kullanarak jetonu hemen iptal edin.',
            'revoke-btn'    => 'Bu Jetonu İptal Et',
            'revoke-expiry' => 'Bu iptal bağlantısı 7 gün boyunca geçerlidir. Bundan sonra belirteci yönetmek için yönetici panelinde oturum açın.',
            'no-action'     => 'Herhangi bir işlem yapılmasına gerek yoktur; bu e-posta yalnızca bir onay niteliğindedir.',
        ],

        'revoke-confirmation' => [
            'title'                    => 'API Simgesini İptal Et',
            'success-title'            => 'Jeton İptal Edildi',
            'success-message'          => '":name" jetonu iptal edildi. Bunu kullanan herhangi bir istemci erişimi anında kaybetti.',
            'already-inactive-title'   => 'Jeton Zaten Aktif Değil',
            'already-inactive-message' => '":name" jetonu zaten iptal edildi veya yeniden oluşturuldu. Başka bir işleme gerek yoktur.',
        ],

        'confirm' => [
            'generate' => [
                'title'   => 'Jeton Oluştur',
                'message' => 'Belirteç şimdi oluşturulsun mu? Düz metin yalnızca bir kez gösterilecektir; sayfadan ayrılmadan önce kopyalayın.',
            ],
            'regenerate' => [
                'title'   => 'Jetonu Yeniden Oluştur',
                'message' => 'Jeton yeniden oluşturulsun mu? Eski jeton hemen çalışmayı durduracak ve yeni düz metin yalnızca bir kez gösterilecek.',
            ],
            'revoke' => [
                'title'   => 'Jetonu İptal Et',
                'message' => 'Bu jeton iptal edilsin mi? Bunu kullanan herhangi bir istemci erişimi anında kaybedecektir. Bu eylem geri alınamaz.',
            ],
        ],
    ],
];
