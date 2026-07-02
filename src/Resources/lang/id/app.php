<?php

return [
    'graphql' => [
        'cart' => [
            'authentication-required'           => 'Token autentikasi diperlukan',
            'invalid-token'                     => 'Token autentikasi tidak valid atau telah kedaluwarsa',
            'unauthorized-access'               => 'Akses tidak sah ke keranjang',
            'authenticated-only'                => 'Hanya pengguna terautentikasi yang dapat mengambil keranjang mereka',
            'merge-requires-auth'               => 'Penggabungan tamu memerlukan autentikasi',
            'unknown-operation'                 => 'Operasi keranjang tidak dikenal',

            'cart-not-found'                    => 'Keranjang tidak ditemukan',
            'guest-cart-not-found'              => 'Keranjang tamu tidak ditemukan',
            'product-not-found'                 => 'Produk tidak ditemukan',

            'product-id-quantity-required'      => 'ID produk dan jumlah diperlukan',
            'cart-item-id-quantity-required'    => 'ID item keranjang dan jumlah diperlukan',
            'cart-item-id-required'             => 'ID item keranjang diperlukan',
            'item-ids-required'                 => 'Larik ID item diperlukan',
            'coupon-code-required'              => 'Kode kupon diperlukan',
            'address-data-required'             => 'Negara, provinsi, dan kode pos diperlukan',

            'add-product-failed'                => 'Gagal menambahkan produk ke keranjang',
            'update-item-failed'                => 'Gagal memperbarui item keranjang',
            'remove-item-failed'                => 'Gagal menghapus item keranjang',
            'apply-coupon-failed'               => 'Gagal menerapkan kupon',
            'remove-coupon-failed'              => 'Gagal menghapus kupon',
            'move-to-wishlist-failed'           => 'Gagal memindahkan item ke daftar keinginan',
            'estimate-shipping-failed'          => 'Gagal memperkirakan pengiriman',

            'product-added-successfully'         => 'Produk berhasil ditambahkan ke keranjang',
            'guest-cart-merged'                  => 'Keranjang tamu berhasil digabungkan',
            'using-authenticated-cart'           => 'Menggunakan keranjang pelanggan terautentikasi',
            'cart-item-not-found'                => 'Item keranjang tidak ditemukan',
            'new-guest-cart-created'             => 'Keranjang tamu baru dibuat dengan token sesi unik',
            'select-items-to-remove'             => 'Silakan pilih item untuk dihapus',
            'select-items-to-move-wishlist'      => 'Silakan pilih item untuk dipindahkan ke daftar keinginan',
            'invalid-or-expired-token'           => 'Token keranjang tidak valid atau telah kedaluwarsa. Silakan buat keranjang baru.',
            'invalid-token-of-login-user'        => 'Token pengguna login tidak valid.',
        ],

        'token-verification' => [
            'invalid-operation'                 => 'Operasi tidak valid',
            'invalid-input-data'                => 'Data masukan tidak valid',
            'token-required'                    => 'Token diperlukan',
            'invalid-token-format'              => 'Format token tidak valid',
            'token-not-found-or-expired'        => 'Token tidak ditemukan atau telah kedaluwarsa',
            'customer-not-found'                => 'Pelanggan tidak ditemukan',
            'customer-account-suspended'        => 'Akun pelanggan ditangguhkan',
            'error-verifying-token'             => 'Kesalahan saat memverifikasi token',
            'token-is-valid'                    => 'Token valid',
        ],

        'forgot-password' => [
            'invalid-operation'                 => 'Operasi tidak valid',
            'invalid-input-data'                => 'Data masukan tidak valid',
            'email-required'                    => 'Email diperlukan',
            'reset-link-sent'                   => 'Tautan reset berhasil dikirim ke email Anda',
            'email-not-found'                   => 'Alamat email tidak ditemukan',
            'error-sending-reset-link'          => 'Terjadi kesalahan saat mengirim tautan reset',
        ],

        'logout' => [
            'invalid-operation'                 => 'Operasi tidak valid',
            'invalid-input-data'                => 'Data masukan tidak valid',
            'token-required'                    => 'Token diperlukan',
            'invalid-token-format'              => 'Format token tidak valid',
            'logged-out-successfully'           => 'Berhasil keluar',
            'token-not-found-or-expired'        => 'Token tidak ditemukan atau sudah kedaluwarsa',
            'error-during-logout'               => 'Kesalahan saat keluar',
        ],

        'address' => [
            'deleted-successfully'              => 'Alamat berhasil dihapus',
            'authentication-required'           => 'Token autentikasi diperlukan',
            'invalid-token'                     => 'Token tidak valid atau telah kedaluwarsa',
            'unknown-operation'                 => 'Operasi tidak dikenal',
            'address-id-required'               => 'ID alamat diperlukan',
            'address-not-found'                 => 'Alamat tidak ditemukan atau bukan milik pelanggan ini',
            'retrieved'                         => 'Alamat berhasil diambil',
            'fetch-failed'                      => 'Gagal mengambil alamat:',
        ],

        'customer-profile' => [
            'authentication-required'           => 'Token autentikasi diperlukan. Silakan berikan token dalam masukan kueri',
            'invalid-token'                     => 'Token tidak valid atau telah kedaluwarsa',
        ],

        'customer' => [
            'password-mismatch'                 => 'Kata sandi dan konfirmasi kata sandi tidak cocok',
            'confirm-password-required'         => 'Konfirmasi kata sandi diperlukan saat mengubah kata sandi',
            'unauthenticated'                   => 'Tidak terautentikasi. Silakan masuk untuk melakukan tindakan ini',
        ],

        'product-review' => [
            'product-id-required'               => 'ID produk diperlukan',
            'product-not-found'                 => 'Produk tidak ditemukan',
            'rating-invalid'                    => 'Peringkat harus antara 1 dan 5',
            'title-required'                    => 'Judul ulasan diperlukan',
            'comment-required'                  => 'Komentar ulasan diperlukan',
        ],

        'product' => [
            'not-found-with-sku'                => 'No product found with SKU',
            'not-found-with-url-key'            => 'No product found with URL key',
            'parameters-required'               => 'At least one of the following parameters must be provided: "sku", "id", "urlKey"',
        ],

        'auth' => [
            'no-token-provided'                 => 'Tidak ada token autentikasi yang diberikan. Silakan berikan token dalam header Authorization sebagai "Bearer <token>" atau di kolom input.token',
            'invalid-or-expired-token'          => 'Token tidak valid atau telah kedaluwarsa',
            'request-not-found'                 => 'Permintaan tidak ditemukan dalam konteks',
            'token-required'                    => 'Authentication token is required. Please provide the token either in the GraphQL mutation input field or in the Authorization header as "Bearer <token>"',
            'unknown-resource'                  => 'Sumber daya tidak dikenal',
            'cannot-update-other-profile'       => 'Tidak sah: Tidak dapat memperbarui profil pelanggan lain',
        ],

        'upload' => [
            'invalid-base64'                    => 'Data gambar berkode base64 tidak valid',
            'size-exceeds-limit'                => 'Ukuran gambar tidak boleh melebihi 5MB',
            'invalid-format'                    => 'Format gambar tidak valid. Silakan berikan gambar berkode base64 dengan skema URI data (data:image/jpeg;base64,...)',
            'failed'                            => 'Unggah gambar gagal',
        ],

        'attribute' => [
            'code-already-exists'               => 'Kode atribut sudah ada',
        ],

        'login' => [
            'invalid-credentials'               => 'Email atau kata sandi tidak valid',
            'account-suspended'                 => 'Akun Anda telah ditangguhkan',
            'successful'                        => 'Anda telah berhasil masuk',
            'invalid-request'                   => 'Permintaan masuk tidak valid',
        ],

        'checkout' => [
            'invalid-input'                     => 'Data masukan tidak valid untuk operasi checkout',
            'billing-address-required'          => 'Alamat penagihan diperlukan',
            'shipping-address-required'         => 'Alamat pengiriman diperlukan untuk pengiriman',
            'address-save-failed'               => 'Gagal menyimpan alamat',
            'address-saved'                     => 'Alamat berhasil disimpan',
            'shipping-method-required'          => 'Metode pengiriman diperlukan',
            'invalid-shipping-method'           => 'Metode pengiriman tidak valid atau tidak tersedia',
            'shipping-method-save-failed'       => 'Gagal menyimpan metode pengiriman',
            'shipping-method-saved'             => 'Metode pengiriman berhasil disimpan',
            'shipping-method-error'             => 'Kesalahan saat menyimpan metode pengiriman',
            'payment-method-required'           => 'Metode pembayaran diperlukan',
            'invalid-payment-method'            => 'Metode pembayaran tidak valid atau tidak tersedia',
            'payment-method-save-failed'        => 'Gagal menyimpan metode pembayaran',
            'payment-method-saved'              => 'Metode pembayaran berhasil disimpan',
            'payment-method-error'              => 'Kesalahan saat menyimpan metode pembayaran',
            'order-creation-failed'             => 'Pembuatan pesanan gagal: ID pesanan null atau pesanan tidak tersimpan',
            'order-retrieval-failed'            => 'Gagal mengambil pesanan yang dibuat',
            'order-creation-error'              => 'Gagal membuat pesanan',
            'cart-empty'                        => 'Keranjang kosong',
            'account-suspended'                 => 'Akun Anda telah ditangguhkan. Silakan hubungi dukungan.',
            'account-inactive'                  => 'Akun Anda tidak aktif. Silakan hubungi dukungan.',
            'minimum-order-not-met'             => 'Jumlah pesanan minimum adalah :amount',
            'email-required'                    => 'Alamat email diperlukan untuk pembuatan pesanan',
            'unknown-operation'                 => 'Operasi checkout tidak dikenal',
        ],

        'customer-addresses' => [
            'token-required'                    => 'Token diperlukan untuk mengambil alamat pelanggan',
            'invalid-or-expired-token'          => 'Token tidak valid atau telah kedaluwarsa',
            'token-validation-failed'           => 'Validasi token gagal',
        ],

        'product' => [
            'type'                              => 'Jenis Produk',
            'attribute-family'                  => 'Keluarga Atribut',
            'sku'                               => 'SKU',
            'name'                              => 'Nama',
            'description'                       => 'Deskripsi',
            'short-description'                 => 'Deskripsi Singkat',
            'status'                            => 'Status',
            'new'                               => 'Baru',
            'featured'                          => 'Unggulan',
            'price'                             => 'Harga',
            'special-price'                     => 'Harga Khusus',
            'weight'                            => 'Berat',
            'cost'                              => 'Biaya',
            'length'                            => 'Panjang',
            'width'                             => 'Lebar',
            'height'                            => 'Tinggi',
            'color'                             => 'Warna',
            'size'                              => 'Ukuran',
            'brand'                             => 'Merek',
            'super-attributes'                  => 'Atribut Super',
        ],

        'compare-item' => [
            'id-required'                       => 'ID item perbandingan diperlukan',
            'invalid-id-format'                 => 'Format ID tidak valid. Format IRI yang diharapkan seperti "/api/shop/compare-items/1" atau ID numerik',
            'not-found'                         => 'Item perbandingan tidak ditemukan',
            'product-id-required'               => 'ID produk diperlukan',
            'customer-id-required'              => 'ID pelanggan diperlukan',
            'product-not-found'                 => 'Produk tidak ditemukan',
            'customer-not-found'                => 'Pelanggan tidak ditemukan',
            'already-exists'                    => 'Produk ini sudah ada di daftar perbandingan Anda',
        ],

        'downloadable-product' => [
            'download-link-not-found'           => 'Link unduhan tidak ditemukan atau telah kadaluarsa',
            'purchased-link-not-found'          => 'Link yang dibeli tidak ditemukan',
            'file-not-found'                    => 'File tidak ditemukan',
            'download-successful'               => 'File siap untuk diunduh',
            'token-required'                    => 'Token unduhan diperlukan',
            'invalid-token'                     => 'Token unduhan tidak valid atau telah kadaluarsa',
            'token-expired'                     => 'Token unduhan telah kadaluarsa. Harap buat yang baru',
            'access-denied'                     => 'Akses ditolak: Anda tidak memiliki izin untuk mengunduh file ini',
            'redirect-external-url'             => 'Mengalihkan ke URL unduhan eksternal',
            'file-error'                        => 'Terjadi kesalahan saat memproses permintaan unduhan Anda',
            'unauthorized-access'               => 'Akses tidak sah ke sumber unduhan',
        ],
    ],

    'integration' => [
        'menu' => [
            'title'  => 'Integrasi',
            'tokens' => 'Token',
        ],

        'history' => [
            'menu' => [
                'title' => 'Sejarah',
            ],

            'acl' => [
                'title'  => 'Riwayat Perubahan API',
                'delete' => 'Hapus Riwayat',
            ],

            'index' => [
                'title'           => 'Riwayat Perubahan API',
                'info'            => 'Setiap pembuatan, pembaruan, dan penghapusan dilakukan melalui admin API, dengan siapa yang melakukannya, token apa, dan apa yang berubah.',
                'cleanup-btn'     => 'Hapus log lama',
                'cleanup-days'    => 'Hapus log yang lebih lama dari beberapa hari ini',
                'cleanup-confirm' => 'Hapus semua riwayat yang lebih lama dari jumlah hari yang ditentukan? Hal ini tidak dapat dibatalkan.',
            ],

            'view' => [
                'title'             => 'Perubahan',
                'back-btn'          => 'Kembali',
                'admin'             => 'Admin',
                'token'             => 'Tanda',
                'action'            => 'Tindakan',
                'resource'          => 'Sumber daya',
                'method'            => 'Metode',
                'ip'                => 'Alamat IP',
                'date'              => 'Tanggal',
                'version'           => 'Versi',
                'url'               => 'Titik akhir',
                'request-details'   => 'Detail Permintaan',
                'changes'           => 'Perubahan',
                'field'             => 'Bidang',
                'old'               => 'Nilai lama',
                'new'               => 'Nilai baru',
                'no-field-changes'  => 'Tidak ada perubahan tingkat bidang yang dicatat untuk entri ini.',
                'same-request'      => 'Perubahan lain dalam permintaan yang sama',
                'version-chain'     => 'Riwayat versi catatan ini',
            ],

            'datagrid' => [
                'id'        => 'tanda pengenal',
                'date'      => 'Tanggal',
                'admin'     => 'Admin',
                'token'     => 'Tanda',
                'action'    => 'Tindakan',
                'operation' => 'Operasi',
                'resource'  => 'Sumber daya',
                'version'   => 'Versi',
                'method'    => 'Metode',
                'ip'        => 'IP',
                'view'      => 'Lihat',
                'delete'    => 'Hapus',
            ],

            'events' => [
                'created' => 'Dibuat',
                'updated' => 'Diperbarui',
                'deleted' => 'Dihapus',
            ],

            'deleted'                => ':count catatan riwayat dihapus.',
            'cleanup-input-required' => 'Cantumkan jumlah hari atau tanggal untuk membersihkan.',
        ],

        'acl' => [
            'title'      => 'Integrasi',
            'create'     => 'Buat Integrasi',
            'edit'       => 'Sunting Integrasi',
            'delete'     => 'Cabut Token Integrasi',
            'generate'   => 'Hasilkan Token Integrasi',
            'regenerate' => 'Regenerasi Token Integrasi',
        ],

        'index' => [
            'title'      => 'Integrasi',
            'create-btn' => 'Buat Integrasi',
        ],

        'create' => [
            'title'    => 'Buat Integrasi',
            'save-btn' => 'Simpan',
            'back-btn' => 'Kembali',
        ],

        'edit' => [
            'title'          => 'Sunting Integrasi',
            'save-btn'       => 'Simpan',
            'back-btn'       => 'Kembali',
            'generate-btn'   => 'Hasilkan Token',
            'regenerate-btn' => 'Regenerasi Token',
            'revoke-btn'     => 'Cabut Token',
            'copy-btn'       => 'Salin',
            'token-warning'  => 'Simpan token ini sekarang — token ini tidak akan ditampilkan lagi.',
            'token-label'    => 'Tanda',
            'not-generated'  => 'Belum dihasilkan',
            'masked'         => '(Disimpan — hanya ditampilkan sekali pada generasi)',
            'history-banner' => 'Token ini sudah tidak aktif.',
        ],

        'fields' => [
            'name'                  => 'Nama',
            'description'           => 'Deskripsi',
            'assign-user'           => 'Tetapkan Pengguna',
            'permission-type'       => 'Jenis Izin',
            'access-control'        => 'Kontrol Akses',
            'general'               => 'Umum',
            'token-settings'        => 'Pengaturan Token',
            'valid-till'            => 'Berlaku Sampai',
            'rate-limit-per-minute' => 'Batas Tarif (per menit)',
            'rate-limit-per-day'    => 'Batas Tarif (per hari)',
            'never-expires'         => 'Tidak pernah kadaluarsa',
            'expires-on'            => 'Kedaluwarsa pada',
            'unlimited'             => 'Tidak terbatas',
            'limit-to'              => 'Batasi hingga',
            'requests-per-minute'   => 'permintaan / menit',
            'requests-per-day'      => 'permintaan / hari',
            'select-admin'          => 'Pilih admin',
            'no-available-admins'   => 'Tidak ada admin yang tersedia — setiap admin sudah memiliki token aktif.',
            'same-as-web-hint'      => 'Token akan mencerminkan izin peran admin yang ditugaskan saat ini secara langsung.',
            'ip-allowlist'          => 'Daftar IP yang Diizinkan',
            'ip-any'                => 'IP apa pun (default)',
            'ip-restricted'         => 'Dibatasi untuk IP tertentu',
            'ip-list-hint'          => 'Satu entri per baris. Mendukung IPv4, IPv6 dan CIDR (misalnya 10.0.0.0/24 atau 2001:db8::/32). Biarkan kosong untuk mengizinkan semua IP.',
        ],

        'permission_type' => [
            'all'         => 'Semua',
            'custom'      => 'Adat',
            'same_as_web' => 'Sama seperti Izin Web',
        ],

        'status' => [
            'draft'       => 'Draf',
            'active'      => 'Aktif',
            'revoked'     => 'Dicabut',
            'regenerated' => 'Diregenerasi',
        ],

        'datagrid' => [
            'id'              => 'tanda pengenal',
            'name'            => 'Nama',
            'admin'           => 'Admin',
            'token'           => 'Tanda',
            'status'          => 'Status',
            'permission-type' => 'Jenis Izin',
            'expires-at'      => 'Berlaku Sampai',
            'last-used-at'    => 'Terakhir Digunakan',
            'created-at'      => 'Dibuat Pada',
            'edit'            => 'Sunting',
            'revoke'          => 'Cabut',
        ],

        'messages' => [
            'draft-created'          => 'Integrasi tercipta. Hasilkan token untuk mulai menggunakannya.',
            'updated'                => 'Integrasi berhasil diperbarui.',
            'generated'              => 'Token dihasilkan. Salin sekarang — itu tidak akan ditampilkan lagi.',
            'regenerated'            => 'Token dibuat ulang. Salin token baru sekarang — token tidak akan ditampilkan lagi.',
            'revoked'                => 'Token berhasil dicabut.',
            'generate-only-draft'    => 'Hanya draf integrasi yang dapat membuat tokennya.',
            'regenerate-only-active' => 'Hanya token aktif yang dapat dibuat ulang.',
            'cannot-edit-historic'   => 'Token yang dicabut atau dibuat ulang tidak dapat diedit.',
            'already-inactive'       => 'Token ini sudah tidak aktif.',
        ],

        'errors' => [
            'admin-has-token' => 'Admin yang dipilih sudah memiliki token integrasi aktif.',
        ],

        'validation' => [
            'ip-invalid'           => 'Setiap IP yang diizinkan harus berupa alamat IPv4 atau IPv6 yang valid (didukung notasi CIDR).',
            'cidr-prefix-invalid'  => 'Awalan CIDR tidak valid untuk versi IP yang diberikan.',
        ],

        'configuration' => [
            'api' => [
                'title' => 'API',
                'info'  => 'Pengaturan Bagisto API dan modul adminnya.',
            ],
            'integration' => [
                'title' => 'Integrasi',
                'info'  => 'Kelola plugin Integrasi API yang digunakan untuk menerbitkan token API admin.',
            ],
            'settings' => [
                'title'  => 'Pengaturan Modul',
                'info'   => 'Mengaktifkan atau menonaktifkan plugin Integrasi API. Saat dinonaktifkan, menu sidebarnya disembunyikan dan halamannya kembali menjadi 404.',
                'enable' => 'Aktifkan Modul Integrasi API',
            ],
        ],

        'emails' => [
            'generated' => [
                'subject'  => 'Token API baru telah dibuat: :name',
                'greeting' => 'Token integrasi API bernama ":name" baru saja dibuat di akun Anda.',
            ],
            'regenerated' => [
                'subject'  => 'Token API Anda telah dibuat ulang: :name',
                'greeting' => 'Token integrasi API bernama ":name" baru saja dibuat ulang. Token sebelumnya telah berhenti berfungsi — hanya token baru yang valid.',
            ],
            'revoked' => [
                'subject'  => 'Token API Anda telah dicabut: :name',
                'greeting' => 'Token integrasi API bernama ":name" telah dicabut. Setiap klien yang menggunakannya telah kehilangan akses.',
            ],

            'details' => [
                'name' => 'Nama Token',
                'date' => 'Tanggal',
                'ip'   => 'Dari pengusaha perorangan',
            ],

            'revoke-hint'   => 'Jika Anda tidak menduganya, segera cabut token menggunakan tombol di bawah.',
            'revoke-btn'    => 'Cabut Token Ini',
            'revoke-expiry' => 'Tautan pencabutan ini berlaku selama 7 hari. Setelah itu, masuk ke panel admin untuk mengelola token.',
            'no-action'     => 'Tidak diperlukan tindakan apa pun — email ini hanya konfirmasi.',
        ],

        'revoke-confirmation' => [
            'title'                    => 'Cabut Token API',
            'success-title'            => 'Token Dicabut',
            'success-message'          => 'Token ":name" telah dicabut. Setiap klien yang menggunakannya akan segera kehilangan akses.',
            'already-inactive-title'   => 'Token Sudah Tidak Aktif',
            'already-inactive-message' => 'Token ":name" telah dicabut atau dibuat ulang. Tidak diperlukan tindakan lebih lanjut.',
        ],

        'confirm' => [
            'generate' => [
                'title'   => 'Hasilkan Token',
                'message' => 'Hasilkan token sekarang? Teks biasa hanya akan ditampilkan sekali — salin sebelum meninggalkan halaman.',
            ],
            'regenerate' => [
                'title'   => 'Regenerasi Token',
                'message' => 'Regenerasi tokennya? Token lama akan segera berhenti berfungsi dan teks biasa baru hanya akan ditampilkan satu kali.',
            ],
            'revoke' => [
                'title'   => 'Cabut Token',
                'message' => 'Cabut token ini? Setiap klien yang menggunakannya akan segera kehilangan akses. Tindakan ini tidak dapat dibatalkan.',
            ],
        ],
    ],
];
