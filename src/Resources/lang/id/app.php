<?php

return [

    'graphql' => [
        'headers' => [
            'unknown-channel' => 'Channel ":code" pada header X-Channel tidak ada.',
            'storefront-key-required' => 'Header X-STOREFRONT-KEY diperlukan',
            'storefront-key-invalid' => 'Kunci storefront tidak valid',
            'storefront-key-rate-limited' => 'Batas laju terlampaui. Silakan coba lagi setelah :seconds detik',
        ],

        'cart' => [
            'authentication-required' => 'Token autentikasi diperlukan',
            'invalid-token' => 'Token autentikasi tidak valid atau telah kedaluwarsa',
            'unauthorized-access' => 'Akses tidak sah ke keranjang',
            'authenticated-only' => 'Hanya pengguna terautentikasi yang dapat mengambil keranjang mereka',
            'merge-requires-auth' => 'Penggabungan tamu memerlukan autentikasi',
            'unknown-operation' => 'Operasi keranjang tidak dikenal',

            'cart-not-found' => 'Keranjang tidak ditemukan',
            'guest-cart-not-found' => 'Keranjang tamu tidak ditemukan',
            'product-not-found' => 'Produk tidak ditemukan',

            'product-id-quantity-required' => 'ID produk dan jumlah diperlukan',
            'cart-item-id-quantity-required' => 'ID item keranjang dan jumlah diperlukan',

            'file-token-invalid' => 'Referensi file yang diunggah tidak valid atau telah kedaluwarsa. Unggah file lagi.',
            'file-token-forbidden' => 'File yang diunggah ini bukan milik keranjang Anda.',
            'customizable-option-required' => 'Opsi kustomisasi ":label" diperlukan.',
            'customizable-file-option-invalid' => 'Opsi yang dipilih bukan opsi file untuk produk ini.',
            'customizable-file-invalid' => 'File tidak ada, memiliki ekstensi yang tidak didukung, atau terlalu besar.',
            'cart-item-id-required' => 'ID item keranjang diperlukan',
            'item-ids-required' => 'Larik ID item diperlukan',

            'event-booking-quantity-not-changeable' => 'Jumlah produk pemesanan acara tidak dapat diubah. Jumlah ditentukan oleh pilihan tiket.',
            'appointment-booking-quantity-not-changeable' => 'Jumlah produk pemesanan janji temu tidak dapat diubah.',
            'coupon-code-required' => 'Kode kupon diperlukan',
            'address-data-required' => 'Negara, provinsi, dan kode pos diperlukan',
            'grouped-qty-required' => 'Produk grup memerlukan jumlah yang dipilih. Kirim groupedQty sebagai string JSON, mis. {"101":2,"102":1}.',
            'grouped-qty-must-include-all' => 'Produk grup memerlukan jumlah untuk semua produk terkait. ID yang hilang: :ids.',
            'grouped-qty-invalid-associated' => 'Jumlah produk grup berisi ID produk terkait yang tidak valid: :ids.',
            'grouped-qty-invalid-quantity' => 'Jumlah yang diberikan untuk ID produk terkait :id tidak valid. Jumlah harus berupa bilangan bulat non-negatif.',
            'bundle-qty-not-changeable' => 'Jumlah untuk opsi bundel ":option" tidak dapat diubah. Jumlah yang diizinkan adalah :qty.',

            'add-product-failed' => 'Gagal menambahkan produk ke keranjang',
            'update-item-failed' => 'Gagal memperbarui item keranjang',
            'remove-item-failed' => 'Gagal menghapus item keranjang',
            'apply-coupon-failed' => 'Gagal menerapkan kupon',
            'remove-coupon-failed' => 'Gagal menghapus kupon',
            'coupon-applied' => 'Kupon berhasil diterapkan',
            'coupon-removed' => 'Kupon berhasil dihapus',
            'move-to-wishlist-failed' => 'Gagal memindahkan item ke daftar keinginan',
            'estimate-shipping-failed' => 'Gagal memperkirakan pengiriman',

            'product-added-successfully' => 'Produk berhasil ditambahkan ke keranjang',
            'cart-item-updated-successfully' => 'Item keranjang berhasil diperbarui',
            'guest-cart-merged' => 'Keranjang tamu berhasil digabungkan',
            'using-authenticated-cart' => 'Menggunakan keranjang pelanggan terautentikasi',
            'cart-item-not-found' => 'Item keranjang tidak ditemukan',
            'new-guest-cart-created' => 'Keranjang tamu baru dibuat dengan token sesi unik',
            'select-items-to-remove' => 'Silakan pilih item untuk dihapus',
            'select-items-to-move-wishlist' => 'Silakan pilih item untuk dipindahkan ke daftar keinginan',
            'invalid-or-expired-token' => 'Token keranjang tidak valid atau telah kedaluwarsa. Silakan buat keranjang baru.',
            'invalid-token-of-login-user' => 'Token pengguna login tidak valid.',
            'booking-note-required' => 'booking.note diperlukan untuk pemesanan meja.',
            'booking-date-required' => 'booking.date diperlukan jika booking.slot berupa rentang waktu yang diformat.',
            'booking-qty-required' => 'booking.qty diperlukan untuk pemesanan acara.',
            'booking-ticket-qty-invalid' => 'Jumlah tiket acara harus berupa angka non-negatif.',
            'booking-ticket-required' => 'Pilih setidaknya satu jumlah tiket untuk pemesanan acara.',
            'booking-slot-unavailable' => 'Slot yang dipilih tidak tersedia untuk tanggal tersebut.',
            'invalid-quantity' => 'Jumlah minimal harus 1',
            'product-id-required' => 'ID produk diperlukan',
            'shipping-address-required' => 'Negara dan kode pos diperlukan untuk memperkirakan pengiriman',
        ],

        'token-verification' => [
            'invalid-operation' => 'Operasi tidak valid',
            'invalid-input-data' => 'Data masukan tidak valid',
            'token-required' => 'Token diperlukan',
            'invalid-token-format' => 'Format token tidak valid',
            'token-not-found-or-expired' => 'Token tidak ditemukan atau telah kedaluwarsa',
            'customer-not-found' => 'Pelanggan tidak ditemukan',
            'customer-account-suspended' => 'Akun pelanggan ditangguhkan',
            'error-verifying-token' => 'Kesalahan saat memverifikasi token',
            'token-is-valid' => 'Token valid',
            'unauthenticated' => 'Tidak terautentikasi. Silakan masuk untuk melakukan tindakan ini',
        ],

        'forgot-password' => [
            'invalid-operation' => 'Operasi tidak valid',
            'invalid-input-data' => 'Data masukan tidak valid',
            'email-required' => 'Email diperlukan',
            'reset-link-sent' => 'Tautan reset berhasil dikirim ke email Anda',
            'email-not-found' => 'Alamat email tidak ditemukan',
            'throttled' => 'Terlalu banyak percobaan reset. Silakan tunggu sebelum mencoba lagi.',
            'error-sending-reset-link' => 'Terjadi kesalahan saat mengirim tautan reset',
        ],

        'reset-password' => [
            'failed' => 'Kata sandi tidak dapat diatur ulang. Silakan coba lagi.',
        ],

        'rate-limit' => [
            'too-many-attempts' => 'Terlalu banyak percobaan. Silakan coba lagi dalam :seconds detik.',
        ],

        'logout' => [
            'invalid-operation' => 'Operasi tidak valid',
            'invalid-input-data' => 'Data masukan tidak valid',
            'token-required' => 'Token diperlukan',
            'invalid-token-format' => 'Format token tidak valid',
            'logged-out-successfully' => 'Berhasil keluar',
            'token-not-found-or-expired' => 'Token tidak ditemukan atau sudah kedaluwarsa',
            'error-during-logout' => 'Kesalahan saat keluar',
            'unauthenticated' => 'Tidak terautentikasi. Silakan masuk untuk melakukan tindakan ini',
        ],

        'address' => [
            'deleted-successfully' => 'Alamat berhasil dihapus',
            'authentication-required' => 'Token autentikasi diperlukan',
            'invalid-token' => 'Token tidak valid atau telah kedaluwarsa',
            'unknown-operation' => 'Operasi tidak dikenal',
            'address-id-required' => 'ID alamat diperlukan',
            'address-not-found' => 'Alamat tidak ditemukan atau bukan milik pelanggan ini',
            'retrieved' => 'Alamat berhasil diambil',
            'fetch-failed' => 'Gagal mengambil alamat:',
            'field-required' => 'Kolom :field diperlukan',
        ],

        'customer-profile' => [
            'authentication-required' => 'Token autentikasi diperlukan. Silakan berikan token dalam masukan kueri',
            'invalid-token' => 'Token tidak valid atau telah kedaluwarsa',
            'profile-updated' => 'Profil pelanggan berhasil diperbarui',
        ],

        'customer' => [
            'password-mismatch' => 'Kata sandi dan konfirmasi kata sandi tidak cocok',
            'confirm-password-required' => 'Konfirmasi kata sandi diperlukan saat mengubah kata sandi',
            'current-password-required' => 'Kata sandi saat ini diperlukan untuk mengubah kata sandi Anda',
            'current-password-incorrect' => 'Kata sandi saat ini yang Anda masukkan salah',
            'verify-email-to-login' => 'Akun Anda telah dibuat. Silakan verifikasi alamat email Anda untuk masuk.',
            'delete-password-required' => 'Kata sandi Anda diperlukan untuk menghapus akun Anda',
            'delete-password-incorrect' => 'Kata sandi yang Anda masukkan salah',
            'delete-has-active-orders' => 'Akun Anda tidak dapat dihapus selama Anda memiliki pesanan yang tertunda atau sedang diproses',
            'unauthenticated' => 'Tidak terautentikasi. Silakan masuk untuk melakukan tindakan ini',
            'id-required' => 'ID pelanggan diperlukan',
            'invalid-id-format' => 'Format ID tidak valid. Diharapkan format IRI seperti "/api/admin/customers/1" atau ID numerik',
            'not-found' => 'Pelanggan tidak ditemukan',
            'phone-special-chars-not-allowed' => 'Nomor ponsel hanya boleh berisi angka. Karakter khusus tidak diizinkan',
            'email-already-taken' => 'Alamat email ini sudah terdaftar pada akun lain',
            'invalid-gender' => 'Nilai jenis kelamin ":gender" tidak valid. Nilai yang diizinkan adalah: :valid',
        ],

        'product-review' => [
            'id-required' => 'ID ulasan produk diperlukan',
            'invalid-id-format' => 'Format ID tidak valid. Diharapkan format IRI seperti "/api/shop/reviews/1" atau ID numerik',
            'not-found' => 'Ulasan produk dengan ID ":id" tidak ditemukan',
            'item-not-found' => 'Item ":id" tidak ditemukan.',
            'product-id-required' => 'ID produk diperlukan',
            'product-not-found' => 'Produk tidak ditemukan',
            'rating-invalid' => 'Peringkat harus antara 1 dan 5',
            'title-required' => 'Judul ulasan diperlukan',
            'comment-required' => 'Komentar ulasan diperlukan',
            'review-disabled' => 'Ulasan produk saat ini dinonaktifkan',
            'guest-review-disabled' => 'Ulasan tamu tidak diizinkan. Silakan masuk untuk mengirim ulasan',
            'owner-required' => 'Silakan masuk untuk mengelola ulasan Anda',
            'not-owned' => 'Ulasan ini tidak ditulis oleh Anda dan tidak dapat diubah atau dihapus',
            'invalid-attachments' => 'Format lampiran tidak valid.',
            'invalid-product-id-format' => 'Format ID produk tidak valid. Diharapkan format IRI seperti "/api/shop/products/1" atau ID numerik',
        ],

        'customer-review' => [
            'id-required' => 'ID ulasan pelanggan diperlukan',
            'invalid-id-format' => 'Format ID tidak valid. Diharapkan format IRI seperti "/api/shop/customer-reviews/1" atau ID numerik',
            'not-found' => 'Ulasan pelanggan dengan ID ":id" tidak ditemukan',
        ],

        'newsletter' => [
            'error-during-subscription' => 'Tidak dapat menyelesaikan langganan saat ini. Silakan coba lagi nanti',
        ],

        'contact-us' => [
            'success' => 'Pertanyaan Anda berhasil dikirim. Kami akan segera menghubungi Anda',
            'failed' => 'Tidak dapat mengirim pertanyaan Anda saat ini. Silakan coba lagi nanti',
            'invalid-input' => 'Data formulir kontak tidak valid',
        ],

        'customer-order' => [
            'id-required' => 'ID pesanan pelanggan diperlukan',
            'invalid-id-format' => 'Format ID tidak valid. Diharapkan format IRI seperti "/api/shop/customer-orders/1" atau ID numerik',
            'not-found' => 'Pesanan pelanggan dengan ID ":id" tidak ditemukan',
        ],

        'return' => [
            'not-found' => 'Permintaan pengembalian tidak ditemukan.',
            'disabled' => 'Pengembalian tidak diaktifkan untuk toko ini.',
            'invalid-input' => 'Detail pengembalian tidak ada atau tidak valid.',
            'invalid-resolution-type' => 'Jenis resolusi harus "return" atau "cancel_items".',
            'agreement-required' => 'Anda harus menyetujui kebijakan pengembalian.',
            'invalid-order' => 'Pesanan yang dipilih tidak valid.',
            'invalid-item' => 'Item yang dipilih tidak memenuhi syarat untuk dikembalikan.',
            'qty-exceeds' => 'Jumlah yang diminta melebihi jumlah yang dapat dikembalikan.',
            'invalid-package-condition' => 'Kondisi paket harus "open" atau "packed".',
            'invalid-image' => 'Satu atau lebih file yang diunggah memiliki jenis file yang tidak didukung.',
            'invalid-custom-field' => 'Kolom kustom ":id" tidak dikenal.',
            'custom-field-required' => 'Kolom ":field" diperlukan.',
            'invalid-custom-field-value' => 'Nilai untuk ":field" bukan salah satu opsi yang diizinkan.',
            'created' => 'Permintaan pengembalian berhasil dibuat.',
            'canceled' => 'Permintaan pengembalian berhasil dibatalkan.',
            'already-canceled' => 'Permintaan pengembalian sudah dibatalkan.',
            'cancel-not-allowed' => 'Permintaan pengembalian ini tidak dapat dibatalkan.',
            'close-not-allowed' => 'Permintaan pengembalian ini tidak dapat ditutup.',
            'reopened' => 'Permintaan pengembalian berhasil dibuka kembali.',
            'reopen-not-allowed' => 'Permintaan pengembalian ini tidak dapat dibuka kembali.',
            'closed' => 'Permintaan pengembalian berhasil ditutup.',
            'message-sent' => 'Pesan berhasil dikirim.',
            'attachment-not-found' => 'Lampiran pesan tidak ditemukan.',
        ],

        'eu-withdrawal' => [
            'not-found' => 'Pernyataan penarikan tidak ditemukan.',
            'order-not-found' => 'Pesanan tidak dapat ditemukan atau tidak memenuhi syarat untuk penarikan.',
            'too-many-attempts' => 'Terlalu banyak percobaan. Silakan coba lagi dalam :seconds detik.',
            'created' => 'Pernyataan penarikan berhasil diajukan.',
        ],

        'customer-order-shipment' => [
            'id-required' => 'ID pengiriman diperlukan',
            'not-found' => 'Pengiriman dengan ID ":id" tidak ditemukan atau bukan milik pelanggan ini',
            'item-not-found' => 'Item pengiriman dengan ID ":id" tidak ditemukan atau bukan milik pelanggan ini',
            'invalid-id-format' => 'Format ID tidak valid. Diharapkan format IRI seperti "/api/shop/customer-order-shipments/1" atau ID numerik',
        ],

        'cancel-order' => [
            'order-id-required' => 'ID pesanan diperlukan untuk membatalkan pesanan',
            'not-found' => 'Pesanan dengan ID ":id" tidak ditemukan atau bukan milik pelanggan ini',
            'cancel-success' => 'Pesanan berhasil dibatalkan',
            'cancel-failed' => 'Pesanan tidak dapat dibatalkan. Pesanan mungkin sudah diproses, dikirim, atau dibatalkan',
        ],

        'reorder' => [
            'order-id-required' => 'ID pesanan diperlukan untuk memesan ulang',
            'not-found' => 'Pesanan dengan ID ":id" tidak ditemukan atau bukan milik pelanggan ini',
            'reorder-success' => ':count item dari pesanan Anda sebelumnya telah ditambahkan ke keranjang Anda',
            'no-items-added' => 'Tidak ada item yang dapat ditambahkan ke keranjang. Produk mungkin habis atau tidak tersedia',
        ],

        'customer-downloadable-product' => [
            'id-required' => 'ID produk unduhan pelanggan diperlukan',
            'invalid-id-format' => 'Format ID tidak valid. Diharapkan format IRI seperti "/api/shop/customer-downloadable-products/1" atau ID numerik',
            'not-found' => 'Produk unduhan pelanggan dengan ID ":id" tidak ditemukan',

            'download' => [
                'unauthenticated' => 'Tidak sah: Autentikasi pelanggan diperlukan.',
                'not-found' => 'Produk unduhan tidak ditemukan.',
                'pending' => 'Unduhan tertunda. Silakan tunggu hingga faktur pesanan dibuat.',
                'limit-exceeded' => 'Batas unduhan terlampaui.',
                'no-downloads-remaining' => 'Tidak ada lagi unduhan yang tersedia.',
                'file-not-found' => 'File tidak ditemukan.',
                'link-not-found' => 'Tautan unduhan tidak ditemukan.',
            ],
        ],

        'customer-invoice' => [
            'id-required' => 'ID faktur pelanggan diperlukan',
            'invalid-id-format' => 'Format ID tidak valid. Diharapkan format IRI seperti "/api/shop/customer-invoices/1" atau ID numerik',
            'not-found' => 'Faktur pelanggan dengan ID ":id" tidak ditemukan',
        ],

        'auth' => [
            'no-token-provided' => 'Tidak ada token autentikasi yang diberikan. Silakan berikan token dalam header Authorization sebagai "Bearer <token>" atau di kolom input.token',
            'invalid-or-expired-token' => 'Token tidak valid atau telah kedaluwarsa',
            'request-not-found' => 'Permintaan tidak ditemukan dalam konteks',
            'token-required' => 'Authentication token is required. Please provide the token either in the GraphQL mutation input field or in the Authorization header as "Bearer <token>"',
            'unknown-resource' => 'Sumber daya tidak dikenal',
            'cannot-update-other-profile' => 'Tidak sah: Tidak dapat memperbarui profil pelanggan lain',
        ],

        'upload' => [
            'invalid-base64' => 'Data gambar berkode base64 tidak valid',
            'size-exceeds-limit' => 'Ukuran gambar tidak boleh melebihi 5MB',
            'invalid-format' => 'Format gambar tidak valid. Silakan berikan gambar berkode base64 dengan skema URI data (data:image/jpeg;base64,...)',
            'failed' => 'Unggah gambar gagal',
        ],

        'attribute' => [
            'code-already-exists' => 'Kode atribut sudah ada',
            'option-id-required' => 'Parameter "attributeId" diperlukan saat mengkueri opsi atribut secara langsung',
            'id-required' => 'ID atribut diperlukan',
            'invalid-id-format' => 'Format ID tidak valid. Diharapkan format IRI seperti "/api/shop/attributes/1" atau ID numerik',
            'not-found' => 'Atribut tidak ditemukan',
        ],

        'attribute-option' => [
            'id-required' => 'ID opsi atribut diperlukan',
            'invalid-id-format' => 'Format ID tidak valid. Diharapkan format IRI seperti "/api/shop/attribute-options/1" atau ID numerik',
            'not-found' => 'Opsi atribut tidak ditemukan',
        ],

        'channel' => [
            'id-required' => 'ID channel diperlukan',
            'invalid-id-format' => 'Format ID tidak valid. Diharapkan format IRI seperti "/api/shop/channels/1" atau ID numerik',
            'not-found' => 'Channel tidak ditemukan',
        ],

        'country-state' => [
            'id-required' => 'ID provinsi negara diperlukan',
            'invalid-id-format' => 'Format ID tidak valid. Diharapkan format IRI seperti "/api/shop/country-states/1" atau ID numerik',
            'not-found' => 'Provinsi negara tidak ditemukan',
            'country-id-required' => 'ID negara diperlukan',
        ],

        'country' => [
            'id-required' => 'ID negara diperlukan',
            'invalid-id-format' => 'Format ID tidak valid. Diharapkan format IRI seperti "/api/shop/countries/1" atau ID numerik',
            'not-found' => 'Negara tidak ditemukan',
        ],

        'login' => [
            'invalid-credentials' => 'Email atau kata sandi tidak valid',
            'account-suspended' => 'Akun Anda telah ditangguhkan',
            'account-inactive' => 'Akun Anda memerlukan persetujuan admin untuk aktivasi.',
            'email-not-verified' => 'Harap verifikasi akun email Anda terlebih dahulu.',
            'successful' => 'Anda telah berhasil masuk',
            'invalid-request' => 'Permintaan masuk tidak valid',
        ],

        'social-login' => [
            'signed-in' => 'Berhasil masuk.',
            'token-required' => 'Token login sosial diperlukan.',
            'invalid-token' => 'Token login sosial tidak valid atau kedaluwarsa. Silakan coba lagi.',
            'google-not-configured' => 'Masuk dengan Google tidak dikonfigurasi untuk toko ini.',
            'wrong-audience' => 'Token ini diterbitkan untuk aplikasi lain.',
            'email-required' => 'Penyedia tidak membagikan alamat email. Silakan daftar dengan email.',
            'account-inactive' => 'Akun Anda memerlukan persetujuan admin untuk aktivasi.',
            'provider-not-supported' => 'Penyedia login sosial ini tidak didukung.',
            'provider-disabled' => 'Penyedia login sosial ini tidak diaktifkan.',
        ],

        'checkout' => [
            'invalid-input' => 'Data masukan tidak valid untuk operasi checkout',
            'billing-address-required' => 'Alamat penagihan diperlukan',
            'shipping-address-required' => 'Alamat pengiriman diperlukan untuk pengiriman',
            'address-save-failed' => 'Gagal menyimpan alamat',
            'address-saved' => 'Alamat berhasil disimpan',
            'shipping-method-required' => 'Metode pengiriman diperlukan',
            'invalid-shipping-method' => 'Metode pengiriman tidak valid atau tidak tersedia',
            'shipping-method-save-failed' => 'Gagal menyimpan metode pengiriman',
            'shipping-method-saved' => 'Metode pengiriman berhasil disimpan',
            'shipping-method-error' => 'Kesalahan saat menyimpan metode pengiriman',
            'payment-method-required' => 'Metode pembayaran diperlukan',
            'invalid-payment-method' => 'Metode pembayaran tidak valid atau tidak tersedia',
            'payment-method-save-failed' => 'Gagal menyimpan metode pembayaran',
            'payment-method-saved' => 'Metode pembayaran berhasil disimpan',
            'payment-method-error' => 'Kesalahan saat menyimpan metode pembayaran',
            'order-placed' => 'Pesanan berhasil dibuat',
            'order-redirect-required' => 'Metode pembayaran ini mengharuskan pembeli menyelesaikan pembayaran di gateway. Arahkan mereka ke redirectUrl; pesanan dibuat setelah gateway mengonfirmasi pembayaran.',
            'order-creation-failed' => 'Pembuatan pesanan gagal: ID pesanan null atau pesanan tidak tersimpan',
            'order-retrieval-failed' => 'Gagal mengambil pesanan yang dibuat',
            'order-creation-error' => 'Gagal membuat pesanan',
            'cart-empty' => 'Keranjang kosong',
            'account-suspended' => 'Akun Anda telah ditangguhkan. Silakan hubungi dukungan.',
            'account-inactive' => 'Akun Anda tidak aktif. Silakan hubungi dukungan.',
            'minimum-order-not-met' => 'Jumlah pesanan minimum adalah :amount',
            'guest-checkout-not-allowed' => 'Silakan masuk untuk checkout. Checkout sebagai tamu tidak tersedia untuk keranjang ini.',
            'email-required' => 'Alamat email diperlukan untuk pembuatan pesanan',
            'unknown-operation' => 'Operasi checkout tidak dikenal',
        ],

        'customer-addresses' => [
            'token-required' => 'Token diperlukan untuk mengambil alamat pelanggan',
            'invalid-or-expired-token' => 'Token tidak valid atau telah kedaluwarsa',
            'token-validation-failed' => 'Validasi token gagal',
        ],

        'product' => [
            'not-found' => 'Produk tidak ditemukan',
            'not-found-with-sku' => 'Tidak ada produk yang ditemukan dengan SKU',
            'not-found-with-url-key' => 'Tidak ada produk yang ditemukan dengan kunci URL',
            'parameters-required' => 'Setidaknya salah satu parameter berikut harus diberikan: "sku", "id", "urlKey"',
            'type' => 'Jenis Produk',
            'attribute-family' => 'Keluarga Atribut',
            'sku' => 'SKU',
            'name' => 'Nama',
            'description' => 'Deskripsi',
            'short-description' => 'Deskripsi Singkat',
            'status' => 'Status',
            'new' => 'Baru',
            'featured' => 'Unggulan',
            'price' => 'Harga',
            'special-price' => 'Harga Khusus',
            'weight' => 'Berat',
            'cost' => 'Biaya',
            'length' => 'Panjang',
            'width' => 'Lebar',
            'height' => 'Tinggi',
            'color' => 'Warna',
            'size' => 'Ukuran',
            'brand' => 'Merek',
            'super-attributes' => 'Atribut Super',
            'id-required' => 'ID produk diperlukan',
            'invalid-id-format' => 'Format ID tidak valid. Diharapkan format IRI seperti "/api/shop/products/1" atau ID numerik',
        ],

        'downloadable-product' => [
            'download-link-not-found' => 'Link unduhan tidak ditemukan atau telah kadaluarsa',
            'purchased-link-not-found' => 'Link yang dibeli tidak ditemukan',
            'file-not-found' => 'File tidak ditemukan',
            'download-successful' => 'File siap untuk diunduh',
            'token-required' => 'Token unduhan diperlukan',
            'invalid-token' => 'Token unduhan tidak valid atau telah kadaluarsa',
            'token-expired' => 'Token unduhan telah kadaluarsa. Harap buat yang baru',
            'access-denied' => 'Akses ditolak: Anda tidak memiliki izin untuk mengunduh file ini',
            'redirect-external-url' => 'Mengalihkan ke URL unduhan eksternal',
            'file-error' => 'Terjadi kesalahan saat memproses permintaan unduhan Anda',
            'unauthorized-access' => 'Akses tidak sah ke sumber unduhan',
            'unauthenticated' => 'Tidak sah: Autentikasi pelanggan diperlukan',
            'link-id-required' => 'downloadableLinkPurchasedId diperlukan',
            'link-not-found' => 'Tautan unduhan tidak ditemukan',
            'not-owner' => 'Tidak sah: Tautan unduhan ini bukan milik Anda',
            'pending' => 'Unduhan tertunda. Silakan tunggu hingga faktur pesanan dibuat.',
            'limit-exceeded' => 'Batas unduhan terlampaui',
            'no-downloads-left' => 'Tidak ada lagi unduhan yang tersedia',
        ],

        'booking-slot' => [
            'id-required' => 'ID produk pemesanan diperlukan.',
            'date-required' => 'Tanggal diperlukan.',
            'product-not-found' => 'Produk pemesanan tidak ditemukan.',
            'invalid-type' => 'Jenis pemesanan ini tidak didukung.',
            'invalid-time-range' => 'Rentang waktu tidak valid.',
            'invalid-time-format' => 'Format waktu tidak valid.',
        ],

        'resolver' => [
            'no-resolve-info' => 'ResolveInfo GraphQL tidak tersedia dalam konteks. Tidak dapat menentukan kelas model.',
            'unknown-resource-class' => 'Tidak dapat menentukan kelas sumber daya dari tipe kembalian GraphQL',
        ],

        'docs' => [
            'openapi-unavailable' => 'OpenAPI Factory tidak dapat diinstansiasi',
        ],

        'locale' => [
            'id-required' => 'ID lokal diperlukan',
            'invalid-id-format' => 'Format ID tidak valid. Diharapkan format IRI seperti "/api/shop/locales/1" atau ID numerik',
            'not-found' => 'Lokal tidak ditemukan',
        ],

        'category' => [
            'id-required' => 'ID kategori diperlukan',
            'invalid-id-format' => 'Format ID tidak valid. Diharapkan format IRI seperti "/api/shop/categories/1" atau ID numerik',
            'not-found' => 'Kategori tidak ditemukan',
        ],

        'compare-item' => [
            'id-required' => 'ID item perbandingan diperlukan',
            'invalid-id-format' => 'Format ID tidak valid. Format IRI yang diharapkan seperti "/api/shop/compare-items/1" atau ID numerik',
            'not-found' => 'Item perbandingan tidak ditemukan',
            'product-id-required' => 'ID produk diperlukan',
            'customer-id-required' => 'ID pelanggan diperlukan',
            'product-not-found' => 'Produk tidak ditemukan',
            'customer-not-found' => 'Pelanggan tidak ditemukan',
            'already-exists' => 'Produk ini sudah ada di daftar perbandingan Anda',
            'delete-all-success' => 'Semua item perbandingan berhasil dihapus',
        ],

        'wishlist' => [
            'id-required' => 'ID item daftar keinginan diperlukan',
            'invalid-id-format' => 'Format ID tidak valid. Diharapkan format IRI seperti "/api/shop/wishlists/1" atau ID numerik',
            'not-found' => 'Item daftar keinginan tidak ditemukan',
            'product-id-required' => 'ID produk diperlukan',
            'customer-id-required' => 'ID pelanggan diperlukan',
            'product-not-found' => 'Produk tidak ditemukan',
            'product-disabled' => 'Produk ini saat ini dinonaktifkan',
            'customer-not-found' => 'Pelanggan tidak ditemukan',
            'already-exists' => 'Produk ini sudah ada di daftar keinginan Anda',
            'added' => 'Item Berhasil Ditambahkan ke Daftar Keinginan',
            'removed' => 'Item Berhasil Dihapus dari Daftar Keinginan',
            'product-removed' => 'Produk telah dihapus',
            'wishlist-item-id-required' => 'ID item daftar keinginan diperlukan',
            'invalid-quantity' => 'Jumlah harus lebih besar dari 0',
            'move-to-cart-missing-options' => 'Produk tidak memiliki opsi wajib. Silakan konfigurasikan secara manual',
            'moved-to-cart-success' => 'Item berhasil dipindahkan ke keranjang',
            'delete-all-success' => 'Semua item daftar keinginan berhasil dihapus',
        ],

        'gdpr' => [
            'disabled' => 'Permintaan data GDPR dinonaktifkan. Silakan aktifkan GDPR dari konfigurasi admin.',
            'unauthenticated' => 'Anda harus masuk untuk mengelola permintaan data GDPR.',
            'not-found' => 'Permintaan data GDPR tidak ditemukan',
            'type-required' => 'Jenis permintaan diperlukan',
            'type-invalid' => 'Jenis permintaan harus "delete" atau "update"',
            'message-required' => 'Pesan diperlukan',
            'raised' => 'Permintaan data GDPR Anda berhasil diajukan.',
            'revoked' => 'Permintaan data GDPR Anda berhasil dicabut.',
            'revoke-not-allowed' => 'Permintaan data GDPR ini tidak dapat dicabut lagi.',
            'deleted' => 'Permintaan data GDPR Anda berhasil dihapus.',
        ],

        'section' => [
            'id-required' => 'ID bagian diperlukan',
            'invalid-id-format' => 'Format ID tidak valid. Diharapkan format IRI seperti "/api/shop/sections/1" atau ID numerik',
            'not-found' => 'Bagian tidak ditemukan',
        ],

        'install' => [
            'starting' => 'Memulai instalasi API Platform...',
            'completed-success' => '✓ Instalasi API Platform berhasil diselesaikan!',
            'failed' => '✗ Instalasi gagal: ',

            'provider-already-registered' => 'ℹ Service Provider sudah terdaftar',
            'provider-not-registered' => '⚠ Tidak dapat mendaftarkan Service Provider secara otomatis. Tambahkan :provider ke bootstrap/providers.php secara manual.',
            'provider-registered' => '✓ Service Provider terdaftar di bootstrap/providers.php',
            'provider-file-not-found' => 'File tidak ditemukan: :file',
            'provider-permission-denied' => 'Izin ditolak: Tidak dapat menulis ke :file. Silakan periksa izin file.',

            'config-already-exists' => 'ℹ File konfigurasi sudah ada',
            'config-published' => '✓ Konfigurasi API Platform dipublikasikan ke config/',
            'config-source-not-found' => 'File konfigurasi sumber tidak ditemukan: :file',
            'config-permission-denied' => 'Izin ditolak: Tidak dapat menulis ke :directory. Silakan periksa izin direktori.',

            'translatable-not-found' => 'ℹ TranslatableModel tidak ditemukan - dilewati',
            'translatable-already-abstract' => 'ℹ TranslatableModel sudah abstrak',
            'translatable-made-abstract' => '✓ TranslatableModel dijadikan abstrak',
            'translatable-permission-denied' => 'Izin ditolak: Tidak dapat menulis ke :file. Silakan periksa izin file.',

            'providers-already-registered' => 'ℹ Provider API Platform sudah terdaftar',
            'providers-registered' => '✓ Provider API Platform terdaftar di bootstrap/app.php',
            'providers-file-not-found' => 'File tidak ditemukan: :file',
            'providers-permission-denied' => 'Izin ditolak: Tidak dapat menulis ke :file. Silakan periksa izin file.',
            'providers-not-found' => 'Tidak dapat menemukan pembuatan Application di bootstrap/app.php',

            'assets-published' => '✓ Aset paket dipublikasikan',
            'running-migrations' => 'Menjalankan migrasi basis data...',
            'migrations-completed' => '✓ Migrasi basis data selesai',
            'clearing-caches' => 'Membersihkan dan mengoptimalkan cache...',
            'caches-optimized' => '✓ Cache berhasil dibersihkan dan dioptimalkan',
            'generating-api-key' => 'Membuat kunci API...',
            'api-key-generated' => '✓ Kunci API berhasil dibuat',
            'api-key-already-exists' => 'ℹ Kunci API Toko Default sudah ada - pembuatan dilewati',

            'migrations-error' => 'Migrasi basis data gagal.',
            'migrations-error-running' => 'Kesalahan saat menjalankan migrasi basis data: :error',
            'cache-clear-error' => 'Gagal membersihkan cache.',
            'cache-optimize-error' => 'Optimasi gagal.',
            'cache-error' => 'Kesalahan saat membersihkan dan mengoptimalkan cache: :error',
            'api-key-generation-error' => 'Pembuatan kunci API gagal.',
            'api-key-error' => 'Kesalahan saat membuat kunci API: :error',

            'key-management-required' => '--key diperlukan untuk tindakan :action',
            'key-rotation-error' => 'Kesalahan saat merotasi kunci: :error',
            'key-deactivation-error' => 'Kesalahan saat menonaktifkan kunci: :error',
            'key-not-found' => 'Kunci API tidak ditemukan: :identifier',

            'maintenance-starting' => '🔄 Memulai Pemeliharaan Kunci API...',
            'maintenance-complete' => '✅ Pemeliharaan Kunci API Selesai',
            'cleanup-expired-keys' => '🧹 Membersihkan kunci yang kedaluwarsa...',
            'cleanup-expired-none' => '   ℹ️ Tidak ada kunci kedaluwarsa untuk dibersihkan',
            'invalidate-deprecated' => '⚠️ Membatalkan kunci yang usang...',
            'invalidate-deprecated-none' => '   ℹ️ Tidak ada kunci usang untuk dibatalkan',
            'notify-expiring' => '📧 Mengirim notifikasi kedaluwarsa...',
            'notify-expiring-none' => '   ℹ️ Tidak ada kunci yang memerlukan notifikasi',

            'key-rotated-success' => '✅ Kunci berhasil dirotasi!',
            'key-deactivated-success' => '✅ Kunci berhasil dinonaktifkan!',
            'deactivation-cancelled' => 'Penonaktifan dibatalkan.',
            'cleanup-cancelled' => 'Pembersihan dibatalkan.',

            'key-status-title' => 'Status Kunci: :name',
            'key-status-active' => 'Aktif: :status',
            'key-status-usable' => 'Dapat Digunakan: :status',
            'key-status-expired' => 'Kedaluwarsa: :status',
            'key-status-deprecated' => 'Usang: :status',
            'key-status-expires-at' => 'Kedaluwarsa Pada: :expires',
            'key-status-days-until-expiry' => 'Hari Hingga Kedaluwarsa: :days',
            'key-status-last-used' => 'Terakhir Digunakan: :date',
            'key-status-rotated-from' => 'Dirotasi Dari: :key',
            'key-status-rotated-keys' => 'Kunci yang Dirotasi Dari Kunci Ini: :count',

            'policy-compliance-summary' => 'Ringkasan Kepatuhan Kebijakan Rotasi Kunci API',
            'available-actions' => 'Tindakan yang tersedia: rotate, deactivate, cleanup, status, expiring, unused, summary',

            'asset-linked-success' => '✓ Aset API Platform berhasil ditautkan',
            'symlink-create-failed' => 'Tidak dapat membuat symlink, menyalin aset sebagai gantinya...',
            'asset-copy-warning' => 'Peringatan: Tidak dapat menautkan atau menyalin aset API Platform. Penyiapan manual mungkin diperlukan.',
            'asset-copied-success' => '✓ Aset API Platform berhasil disalin',

            'key-name-required' => 'Nama kunci storefront tidak boleh kosong.',
            'key-generated-success' => 'Kunci storefront berhasil dibuat!',
            'key-details' => 'Detail Kunci:',
            'key-secure-warning' => 'Jaga keamanan kunci ini! Kunci ini akan digunakan di header X-STOREFRONT-KEY.',
            'key-share-warning' => 'Jangan bagikan kunci ini secara publik atau meng-commit-nya ke kontrol versi.',

            'key-already-exists' => 'Kunci storefront dengan nama \':name\' sudah ada.',
            'cleanup-success' => '✅ Membersihkan :count kunci yang kedaluwarsa',
            'confirm-cleanup' => 'Ini akan menghapus sementara (soft-delete) semua kunci yang kedaluwarsa. Lanjutkan?',
            'confirm-deactivate' => 'Apakah Anda yakin ingin menonaktifkan kunci: :name?',

            'old-key' => 'Kunci Lama: :name',
            'old-key-id' => 'ID Kunci Lama: :id',
            'deprecation-date' => 'Tanggal Penghentian: :date',
            'new-key' => 'Kunci Baru: :name',
            'new-key-id' => 'ID Kunci Baru: :id',
            'new-key-value' => 'Nilai Kunci Baru: :key',
            'expires-at' => 'Kedaluwarsa Pada: :date',
            'invalid-action' => 'Tindakan tidak valid: :action',
            'key-active' => 'Aktif: :status',
            'key-usable' => 'Dapat Digunakan: :status',
            'key-expired' => 'Kedaluwarsa: :status',
            'key-deprecated' => 'Usang: :status',
            'key-field-id' => 'ID',
            'key-field-name' => 'Nama',
            'key-field-key' => 'Kunci',
            'key-field-rate-limit' => 'Batas Laju',
            'key-field-status' => 'Status',
            'key-days' => ' hari',
            'key-never' => 'Tidak Pernah',
            'key-requests-minute' => ' permintaan/menit',
            'key-unlimited' => 'Tidak Terbatas',
            'rate-limit-exceeded' => 'Batas laju yang diminta melebihi batas maksimum yang diizinkan :max. Diatur ke :max permintaan/menit.',

            'vendor-path-not-found' => 'Path vendor API Platform tidak ditemukan di: :path',
            'assets-already-linked' => 'Aset API Platform sudah ditautkan di: :path',
            'no-keys-expiring' => 'Tidak ada kunci yang kedaluwarsa dalam :days hari ke depan',
            'keys-expiring-title' => 'Kunci yang kedaluwarsa dalam :days hari ke depan:',
            'key-display-format' => '• :name (ID: :id)',
            'key-expires-display' => '  Kedaluwarsa: :date (:days hari tersisa)',
            'no-unused-keys' => 'Tidak ditemukan kunci yang tidak digunakan (> :days hari)',
            'unused-keys-title' => 'Kunci yang tidak digunakan (> :days hari):',
            'key-last-used-display' => '  Terakhir Digunakan: :date',
            'total-keys' => 'Total Kunci: :count',
            'valid-keys' => 'Kunci Valid: :count',
            'expired-keys' => 'Kunci Kedaluwarsa: :count',
            'deprecated-keys' => 'Kunci Usang: :count',
            'keys-expiring-soon' => 'Kunci yang Segera Kedaluwarsa (7 hari): :count',
            'unused-keys-summary' => 'Kunci Tidak Digunakan (90 hari): :count',
            'recently-rotated' => 'Baru Dirotasi (30 hari): :count',
            'cleanup-success-message' => '   ✅ Membersihkan :count kunci yang kedaluwarsa',
            'invalidate-success-message' => '   ✅ Membatalkan :count kunci yang usang',
            'notify-success-message' => '   ✅ Mengirim :count notifikasi kedaluwarsa',
            'notify-failed-message' => '   ⚠️ Gagal memberi notifikasi tentang :key: :error',
            'publish-assets-warning' => 'Peringatan: Tidak dapat memublikasikan aset paket. :error',
            'env-config-saved' => '✓ Konfigurasi lingkungan API Platform berhasil disimpan',
            'api-endpoints' => 'Endpoint API:',
            'api-documentation' => 'Dokumentasi API: :url',
            'api-landing-page' => 'Halaman Utama API: :url',
            'graphql-playground' => 'GraphQL Playground: :url',
            'rest-api-storefront' => 'Swagger Rest API Storefront: :url',
            'rest-api-admin' => 'Swagger Rest API Admin: :url',
            'key-rotation-invalid-key' => 'Tidak dapat merotasi kunci yang tidak valid atau kedaluwarsa',
            'env-file-not-found' => 'File .env tidak ditemukan',
            'env-permission-denied' => 'Izin ditolak: Tidak dapat menulis ke file .env. Silakan periksa izin file.',
        ],

        'attribute-translation' => [
            'id-required' => 'ID terjemahan atribut diperlukan',
            'invalid-id-format' => 'Format ID tidak valid. Diharapkan format IRI seperti "/api/shop/attribute-translations/1" atau ID numerik',
            'not-found' => 'Terjemahan atribut tidak ditemukan',
        ],

        'currency' => [
            'id-required' => 'ID mata uang diperlukan',
            'invalid-id-format' => 'Format ID tidak valid. Diharapkan format IRI seperti "/api/shop/currencies/1" atau ID numerik',
            'not-found' => 'Mata uang tidak ditemukan',
        ],

        'customer-address' => [
            'id-required' => 'ID alamat pelanggan diperlukan',
            'invalid-id-format' => 'Format ID tidak valid. Diharapkan format IRI seperti "/api/shop/customer-addresses/1" atau ID numerik',
        ],

        'customer-order-shipment-item' => [
            'id-required' => 'ID item pengiriman diperlukan',
            'invalid-id-format' => 'Format ID tidak valid. Diharapkan format IRI seperti "/api/shop/customer-order-shipment-items/1" atau ID numerik',
            'not-found' => 'Item pengiriman tidak ditemukan',
        ],

        'page' => [
            'id-required' => 'ID halaman diperlukan',
            'invalid-id-format' => 'Format ID tidak valid. Diharapkan format IRI seperti "/api/shop/pages/1" atau ID numerik',
            'not-found' => 'Halaman tidak ditemukan',
        ],

        'page-translation' => [
            'id-required' => 'ID terjemahan halaman diperlukan',
            'invalid-id-format' => 'Format ID tidak valid. Diharapkan format IRI seperti "/api/shop/page-translations/1" atau ID numerik',
            'not-found' => 'Terjemahan halaman tidak ditemukan',
        ],

        'product-bundle-option' => [
            'id-required' => 'ID opsi bundel diperlukan',
            'invalid-id-format' => 'Format ID tidak valid. Diharapkan format IRI seperti "/api/shop/product-bundle-options/1" atau ID numerik',
            'not-found' => 'Opsi bundel tidak ditemukan',
        ],

        'product-bundle-option-product' => [
            'id-required' => 'ID produk opsi bundel diperlukan',
            'invalid-id-format' => 'Format ID tidak valid. Diharapkan format IRI seperti "/api/shop/product-bundle-option-products/1" atau ID numerik',
            'not-found' => 'Produk opsi bundel tidak ditemukan',
        ],

        'product-downloadable-link' => [
            'id-required' => 'ID tautan unduhan diperlukan',
            'invalid-id-format' => 'Format ID tidak valid. Diharapkan format IRI seperti "/api/shop/product-downloadable-links/1" atau ID numerik',
            'not-found' => 'Tautan unduhan tidak ditemukan',
        ],

        'product-downloadable-sample' => [
            'id-required' => 'ID sampel unduhan diperlukan',
            'invalid-id-format' => 'Format ID tidak valid. Diharapkan format IRI seperti "/api/shop/product-downloadable-samples/1" atau ID numerik',
            'not-found' => 'Sampel unduhan tidak ditemukan',
        ],

        'product-grouped-product' => [
            'id-required' => 'ID produk grup diperlukan',
            'invalid-id-format' => 'Format ID tidak valid. Diharapkan format IRI seperti "/api/shop/product-grouped-products/1" atau ID numerik',
            'not-found' => 'Produk grup tidak ditemukan',
        ],

        'product-images' => [
            'id-required' => 'ID gambar produk diperlukan',
            'invalid-id-format' => 'Format ID tidak valid. Diharapkan format IRI seperti "/api/shop/product-images/1" atau ID numerik',
            'not-found' => 'Gambar produk tidak ditemukan',
        ],

        'product-videos' => [
            'id-required' => 'ID video produk diperlukan',
            'invalid-id-format' => 'Format ID tidak valid. Diharapkan format IRI seperti "/api/shop/product-videos/1" atau ID numerik',
            'not-found' => 'Video produk tidak ditemukan',
        ],

        'theme-customization' => [
            'id-required' => 'ID kustomisasi tema diperlukan',
            'invalid-id-format' => 'Format ID tidak valid. Diharapkan format IRI seperti "/api/shop/theme-customizations/1" atau ID numerik',
            'not-found' => 'Kustomisasi tema tidak ditemukan',
        ],

        'unknown-resource' => [
            'id-required' => 'ID sumber daya diperlukan',
            'not-found' => 'Sumber daya tidak ditemukan',
        ],
    ],

    'admin' => [
        // login / logout / forgot-password / profile-update keys removed in the
        // 2026-05-27 auth refactor — admin clients now authenticate via
        // pre-issued integration tokens (Bearer header, AdminApiGuard).
        'profile' => [
            'unauthenticated' => 'Anda tidak terautentikasi.',
        ],

        'dashboard' => [
            'invalid-type' => 'Jenis statistik dasbor tidak dikenal. Jenis yang valid: over-all, today, stock-threshold-products, total-sales, top-selling-products, top-customers.',
        ],

        'rma' => [
            'not-found' => 'Permintaan pengembalian tidak ditemukan.',
            'invalid-input' => 'Detail pengembalian tidak ada atau tidak valid.',
            'invalid-order' => 'Pesanan yang dipilih tidak valid.',
            'invalid-item' => 'Item yang dipilih tidak memenuhi syarat untuk dikembalikan.',
            'invalid-resolution-type' => 'Jenis resolusi harus "return" atau "cancel_items".',
            'qty-exceeds' => 'Jumlah yang diminta melebihi jumlah yang dapat dikembalikan.',
            'created' => 'Permintaan pengembalian berhasil dibuat.',
            'status-updated' => 'Status pengembalian berhasil diperbarui.',
            'invalid-status' => 'Status pengembalian tidak valid.',
            'reopened' => 'Permintaan pengembalian berhasil dibuka kembali.',
            'reopen-not-allowed' => 'Permintaan pengembalian ini tidak dapat dibuka kembali.',
            'refund-failed' => 'Pengembalian dana tidak dapat dibuat untuk pengembalian ini.',
            'message-sent' => 'Pesan berhasil dikirim.',
            'no-permission' => 'Anda tidak memiliki izin untuk melakukan tindakan ini.',
            'reason-not-found' => 'Alasan pengembalian tidak ditemukan.',
            'reason-deleted' => 'Alasan pengembalian berhasil dihapus.',
            'status-not-found' => 'Status pengembalian tidak ditemukan.',
            'status-deleted' => 'Status pengembalian berhasil dihapus.',
            'status-default-undeletable' => 'Status pengembalian default tidak dapat dihapus.',
            'rule-not-found' => 'Aturan pengembalian tidak ditemukan.',
            'rule-deleted' => 'Aturan pengembalian berhasil dihapus.',
            'custom-field-not-found' => 'Kolom kustom pengembalian tidak ditemukan.',
            'custom-field-deleted' => 'Kolom kustom pengembalian berhasil dihapus.',
            'custom-field-options-required' => 'Setidaknya satu opsi diperlukan untuk jenis kolom ini.',
            'mass-indices-required' => 'Setidaknya satu id diperlukan.',
            'mass-delete-success' => 'Item yang dipilih telah dihapus.',
            'mass-update-success' => 'Item yang dipilih telah diperbarui.',
            'mass-value-invalid' => 'Nilai status harus 0 atau 1.',
            'image-not-found' => 'Gambar pengembalian tidak ditemukan.',
            'attachment-not-found' => 'Lampiran pesan tidak ditemukan.',
        ],

        'eu-withdrawal' => [
            'not-found' => 'Pernyataan penarikan tidak ditemukan.',
            'no-permission' => 'Anda tidak memiliki izin untuk mengelola penarikan UE.',
            'declined' => 'Penarikan ditandai sebagai ditolak.',
            'refunded' => 'Penarikan ditandai sebagai dikembalikan dananya.',
            'confirmation-resent' => 'Email konfirmasi berhasil dikirim ulang.',
            'confirmation-failed' => 'Email konfirmasi tidak dapat dikirim.',
        ],

        'reporting' => [
            'invalid-entity' => 'Entitas laporan tidak dikenal.',
            'invalid-type' => 'Jenis statistik laporan tidak dikenal untuk entitas ini.',
            'export-format-unsupported' => 'Hanya format ekspor csv, xls, dan xlsx yang didukung.',
        ],

        'configuration' => [
            'unauthenticated' => 'Anda tidak terautentikasi.',
            'no-permission' => 'Anda tidak memiliki izin untuk memperbarui konfigurasi.',
            'slug-required' => 'Parameter kueri `slug` diperlukan.',
            'slug-not-found' => 'Tidak ada bagian konfigurasi yang terdaftar untuk slug yang diberikan.',
            'values-required' => 'Setidaknya satu nilai diperlukan di bawah `values`.',
            'scope-escape' => 'Kunci `:key` berada di luar subpohon slug yang diminta.',
            'unknown-field' => 'Kunci `:key` bukan kolom konfigurasi yang terdaftar.',
            'validation-failed' => 'Satu atau lebih nilai konfigurasi gagal divalidasi.',
            'file-upload-rest-only' => 'Unggahan file biner untuk kolom `:field` hanya didukung melalui endpoint multipart REST, bukan GraphQL.',
            'custom-view-readonly' => 'Kolom `:field` adalah kolom kustom yang dirender dengan blade dan tidak dapat diperbarui melalui API.',
            'update-success' => 'Konfigurasi berhasil diperbarui.',
        ],

        'customer' => [
            'not-found' => 'Pelanggan tidak ditemukan.',
            'no-permission' => 'Anda tidak memiliki izin untuk melakukan tindakan ini.',
            'has-active-orders' => 'Pelanggan memiliki pesanan yang tertunda atau sedang diproses dan tidak dapat dihapus.',
            'deleted' => 'Pelanggan berhasil dihapus.',
            'mass-delete-indices-required' => 'Setidaknya satu id pelanggan diperlukan.',
            'mass-delete-success' => 'Pelanggan telah dihapus.',
            'mass-update-indices-required' => 'Setidaknya satu id pelanggan diperlukan.',
            'mass-update-value-invalid' => 'Nilai status harus 0 atau 1.',
            'mass-update-success' => 'Status pelanggan diperbarui.',

            'address' => [
                'not-found' => 'Alamat tidak ditemukan.',
                'not-owned' => 'Alamat ini bukan milik pelanggan yang ditentukan.',
                'customer-id-required' => 'customerId diperlukan untuk mengidentifikasi pemilik alamat.',
                'deleted' => 'Alamat berhasil dihapus.',
            ],

            'note' => [
                'empty' => 'Teks catatan diperlukan.',
                'created' => 'Catatan berhasil ditambahkan.',
            ],

            'impersonate' => [
                'success' => 'Token peniruan identitas diterbitkan.',
            ],

            'review' => [
                'not-found' => 'Ulasan tidak ditemukan.',
                'no-permission' => 'Anda tidak memiliki izin untuk melakukan tindakan ini.',
                'invalid-status' => 'Status harus salah satu dari: pending, approved, disapproved.',
                'updated' => 'Ulasan berhasil diperbarui.',
                'deleted' => 'Ulasan berhasil dihapus.',
                'mass-delete-indices-required' => 'Setidaknya satu id ulasan diperlukan.',
                'mass-delete-success' => 'Ulasan telah dihapus.',
                'mass-update-indices-required' => 'Setidaknya satu id ulasan diperlukan.',
                'mass-update-value-invalid' => 'Nilai status harus salah satu dari: pending, approved, disapproved.',
                'mass-update-success' => 'Status ulasan diperbarui.',
            ],

            'group' => [
                'not-found' => 'Grup pelanggan tidak ditemukan.',
                'deleted' => 'Grup pelanggan berhasil dihapus.',
                'is-system' => 'Grup pelanggan sistem tidak dapat dihapus.',
                'has-customers' => 'Grup pelanggan memiliki pelanggan terkait dan tidak dapat dihapus.',
                'system-code-immutable' => 'Kode grup pelanggan sistem tidak dapat diubah.',
                'system-flag-immutable' => 'Penanda is_user_defined pada grup pelanggan sistem tidak dapat diubah.',
                'mass-delete-indices-required' => 'Setidaknya satu id grup pelanggan diperlukan.',
                'mass-delete-success' => 'Grup pelanggan telah dihapus.',
            ],

            'gdpr' => [
                'not-found' => 'Permintaan GDPR tidak ditemukan.',
                'no-permission' => 'Anda tidak memiliki izin untuk melakukan tindakan ini.',
                'deleted' => 'Permintaan GDPR berhasil dihapus.',
                'invalid-status' => 'Status harus salah satu dari: pending, processing, declined, approved, revoked.',
                'no-changes' => 'Setidaknya salah satu dari status atau pesan diperlukan.',
                'already-processed' => 'Permintaan GDPR ini sudah diproses dan tidak dapat dijalankan ulang.',

                'process' => [
                    'approved' => 'Permintaan GDPR disetujui.',
                    'deleted' => 'Permintaan GDPR disetujui dan pelanggan dihapus.',
                ],
            ],
        ],

        'order' => [
            'not-found' => 'Pesanan tidak ditemukan.',

            'reorder' => [
                'success' => 'Pemesanan ulang berhasil. Keranjang draf baru telah dibuat.',
                'cannot-reorder' => 'Pesanan ini tidak dapat dipesan ulang (pesanan tamu atau satu atau lebih item tidak lagi dapat dijual).',
                'failed' => 'Pemesanan ulang gagal. Silakan coba lagi.',
                'guest-not-supported' => 'Pemesanan ulang tidak didukung untuk pesanan tamu.',
                'items-not-saleable' => 'Satu atau lebih item dalam pesanan ini tidak lagi tersedia untuk dibeli.',
                'no-permission' => 'Anda tidak memiliki izin untuk membuat pesanan.',
                'disabled-in-settings' => 'Pemesanan ulang oleh admin dinonaktifkan di pengaturan toko.',
            ],

            'actions' => [
                'cancel' => [
                    'closed' => 'Pesanan yang ditutup tidak dapat dibatalkan.',
                    'fraud' => 'Pesanan penipuan tidak dapat dibatalkan.',
                    'nothing-to-cancel' => 'Tidak ada yang dapat dibatalkan pada pesanan ini.',
                    'already-processed' => 'Pesanan ini sudah dibuatkan faktur atau dikirim dan tidak dapat dibatalkan lagi. Anda dapat melakukan pengembalian dana sebagai gantinya.',
                    'no-permission' => 'Anda tidak memiliki izin untuk membatalkan pesanan.',
                    'success' => 'Pesanan berhasil dibatalkan.',
                    'failed' => 'Tidak dapat membatalkan pesanan.',
                ],

                'comment' => [
                    'empty' => 'Komentar diperlukan.',
                    'success' => 'Komentar ditambahkan ke pesanan.',
                    'failed' => 'Tidak dapat menambahkan komentar.',
                ],

                'invoice' => [
                    'closed' => 'Pesanan yang ditutup tidak dapat dibuatkan faktur.',
                    'fraud' => 'Pesanan penipuan tidak dapat dibuatkan faktur.',
                    'nothing-to-invoice' => 'Tidak ada yang dapat dibuatkan faktur pada pesanan ini.',
                    'already-invoiced' => 'Faktur sudah dibuat untuk pesanan ini.',
                    'paypal-standard-blocked' => 'Faktur tidak dapat dibuat untuk pesanan yang dibayar melalui PayPal Standard.',
                    'no-permission' => 'Anda tidak memiliki izin untuk membuat faktur.',
                    'items-required' => 'Setidaknya satu item dengan jumlah positif diperlukan.',
                    'qty-exceeds' => 'Jumlah yang diminta (:requested) melebihi jumlah yang tersedia (:available) untuk SKU :sku.',
                    'not-found' => 'Faktur tidak ditemukan.',
                    'success' => 'Faktur berhasil dibuat.',
                    'failed' => 'Tidak dapat membuat faktur.',
                    'pdf-failed' => 'Tidak dapat membuat PDF faktur.',
                    'email-invalid' => 'Email penerima yang valid diperlukan.',
                    'duplicate-sent' => 'Email faktur dikirim ke :email.',
                    'mass-status-indices-required' => 'Setidaknya satu id faktur diperlukan.',
                    'mass-status-value-invalid' => 'Status harus salah satu dari: pending, paid, overdue.',
                    'mass-status-success' => 'Status faktur berhasil diperbarui.',
                ],

                'shipment' => [
                    'closed' => 'Pesanan yang ditutup tidak dapat dikirim.',
                    'fraud' => 'Pesanan penipuan tidak dapat dikirim.',
                    'nothing-to-ship' => 'Tidak ada yang dapat dikirim pada pesanan ini.',
                    'no-permission' => 'Anda tidak memiliki izin untuk mengirim pesanan.',
                    'source-required' => 'Sumber inventaris diperlukan.',
                    'items-required' => 'Setidaknya satu item dengan jumlah positif diperlukan.',
                    'qty-exceeds' => 'Jumlah yang diminta (:requested) melebihi jumlah yang tersedia (:available) untuk SKU :sku.',
                    'inventory-insufficient' => 'Inventaris di sumber yang dipilih tidak mencukupi untuk SKU :sku.',
                    'not-found' => 'Pengiriman tidak ditemukan.',
                    'success' => 'Pengiriman berhasil dibuat.',
                    'failed' => 'Tidak dapat membuat pengiriman.',
                ],

                'refund' => [
                    'closed' => 'Pesanan yang ditutup tidak dapat dikembalikan dananya.',
                    'fraud' => 'Pesanan penipuan tidak dapat dikembalikan dananya.',
                    'nothing-to-refund' => 'Tidak ada lagi yang dapat dikembalikan dananya pada pesanan ini.',
                    'no-permission' => 'Anda tidak memiliki izin untuk membuat pengembalian dana.',
                    'qty-exceeds' => 'Jumlah yang diminta (:requested) melebihi jumlah yang tersedia (:available) untuk SKU :sku.',
                    'amount-zero' => 'Jumlah pengembalian dana yang dihitung adalah nol. Sesuaikan nilai jumlah, pengiriman, atau penyesuaian.',
                    'amount-exceeds-max' => 'Jumlah pengembalian dana (:amount) melebihi jumlah maksimum yang dapat dikembalikan (:max).',
                    'not-found' => 'Pengembalian dana tidak ditemukan.',
                    'success' => 'Pengembalian dana berhasil dibuat.',
                    'failed' => 'Tidak dapat membuat pengembalian dana.',
                ],
            ],
        ],

        'appearance' => [
            'no-permission' => 'Anda tidak memiliki izin untuk mengelola tampilan.',

            'theme' => [
                'not-found' => 'Tema tidak ditemukan.',
                'not-installed' => 'Tema ini tidak terpasang.',
                'channel-ids-required' => 'Setidaknya satu channel diperlukan.',
                'unknown-channel' => 'Satu atau lebih channel tidak ada.',
                'activated' => 'Tema berhasil diaktifkan.',
            ],

            'section' => [
                'not-found' => 'Bagian tidak ditemukan.',
                'invalid-payload' => 'Payload bagian tidak didukung.',
                'footer-links-exists' => 'Channel ini sudah memiliki bagian tautan footer.',
                'options-required' => 'Opsi diperlukan.',
                'status-required' => 'Status diperlukan.',
                'section-ids-required' => 'Setidaknya satu bagian diperlukan.',
                'reorder-single-scope' => 'Bagian hanya dapat diurutkan ulang dalam satu tema dan channel pada satu waktu.',
                'reorder-incomplete' => 'Pengurutan ulang memerlukan setiap bagian dari tema dan channel, dalam urutan yang seharusnya dirender.',
                'unknown-section' => 'Satu atau lebih bagian tidak ada.',
                'file-required' => 'File diperlukan.',
                'created' => 'Bagian berhasil dibuat.',
                'updated' => 'Bagian berhasil diperbarui.',
                'deleted' => 'Bagian berhasil dihapus.',
                'draft-saved' => 'Perubahan bagian berhasil disiapkan.',
                'status-staged' => 'Status bagian berhasil disiapkan.',
                'order-staged' => 'Urutan bagian berhasil disiapkan.',
                'duplicated' => 'Bagian berhasil disalin.',
                'published' => 'Perubahan bagian berhasil dipublikasikan.',
                'discarded' => 'Perubahan bagian berhasil dibuang.',
                'media-uploaded' => 'Media berhasil diunggah.',
            ],
        ],

        'sales' => [
            'no-permission' => 'Anda tidak memiliki izin untuk melihat sumber daya penjualan ini.',

            'invoice' => [
                'not-found' => 'Faktur tidak ditemukan.',
                'no-permission' => 'Anda tidak memiliki izin untuk melihat faktur.',
            ],

            'shipment' => [
                'not-found' => 'Pengiriman tidak ditemukan.',
                'no-permission' => 'Anda tidak memiliki izin untuk melihat pengiriman.',
            ],

            'refund' => [
                'not-found' => 'Pengembalian dana tidak ditemukan.',
                'no-permission' => 'Anda tidak memiliki izin untuk melihat pengembalian dana.',
                'export-format-unsupported' => 'Hanya ekspor csv, xls, dan xlsx yang didukung.',
            ],

            'transaction' => [
                'not-found' => 'Transaksi tidak ditemukan.',
                'no-permission' => 'Anda tidak memiliki izin untuk melihat transaksi.',

                'create' => [
                    'invoice-required' => 'Kolom invoiceId diperlukan.',
                    'payment-method-required' => 'Kolom paymentMethod diperlukan.',
                    'amount-invalid' => 'Kolom amount diperlukan dan harus berupa angka.',
                    'invoice-missing' => 'Faktur yang ditentukan tidak dapat ditemukan.',
                    'already-paid' => 'Faktur ini sudah dibayar lunas.',
                    'amount-exceeds' => 'Jumlah pembayaran melebihi total keseluruhan faktur.',
                    'amount-zero' => 'Jumlah pembayaran harus lebih besar dari nol.',
                ],
            ],

            'booking' => [
                'not-found' => 'Pemesanan tidak ditemukan.',
                'no-permission' => 'Anda tidak memiliki izin untuk melihat pemesanan.',
            ],

            'export' => [
                'format-unsupported' => 'Hanya ekspor csv, xls, dan xlsx yang didukung.',
            ],
        ],

        'cart' => [
            'not-found' => 'Keranjang tidak ditemukan.',
            'not-draft' => 'Keranjang ini tidak dapat diubah melalui API admin.',
            'product-required' => 'productId diperlukan.',
            'product-not-found' => 'Produk tidak ditemukan.',
            'booking-unsupported' => 'Produk pemesanan tidak dapat ditambahkan ke pesanan draf admin. Pembelian pemesanan harus dilakukan melalui storefront pelanggan.',
            'product-not-saleable' => 'Produk ini tidak tersedia untuk dijual (stok habis atau dinonaktifkan) dan tidak dapat ditambahkan ke pesanan.',
            'qty-required' => 'qty diperlukan dan harus berupa objek tidak kosong itemId => jumlah.',
            'cart-item-required' => 'cartItemId diperlukan.',
            'item-added' => 'Item ditambahkan ke keranjang.',
            'item-add-failed' => 'Tidak dapat menambahkan item ke keranjang.',
            'item-updated' => 'Item keranjang diperbarui.',
            'item-update-failed' => 'Tidak dapat memperbarui item keranjang.',
            'item-removed' => 'Item dihapus dari keranjang.',
            'item-remove-failed' => 'Tidak dapat menghapus item keranjang.',
            'address-saved' => 'Alamat disimpan.',
            'address-required' => 'Alamat penagihan diperlukan.',
            'address-incomplete' => ':field diperlukan untuk menyimpan alamat.',
            'coupon-code-required' => 'Kode kupon diperlukan.',
            'coupon-applied' => 'Kupon diterapkan.',
            'coupon-already-applied' => 'Kupon sudah diterapkan pada keranjang ini.',
            'coupon-not-found' => 'Kupon tidak ditemukan atau tidak aktif.',
            'coupon-removed' => 'Kupon dihapus.',
            'coupon-error' => 'Terjadi kesalahan saat menerapkan kupon.',
            'unknown-error' => 'Terjadi kesalahan.',
            'draft-created' => 'Keranjang draf dibuat.',
            'draft-failed' => 'Tidak dapat membuat keranjang draf.',
            'addresses-required-for-shipping' => 'Alamat harus disimpan sebelum memilih metode pengiriman.',
            'shipping-required-for-payment' => 'Metode pengiriman harus dipilih sebelum metode pembayaran.',
            'shipping-method-required' => 'Metode pengiriman diperlukan.',
            'shipping-method-saved' => 'Metode pengiriman disimpan.',
            'shipping-method-failed' => 'Tidak dapat menyimpan metode pengiriman.',
            'payment-method-required' => 'Metode pembayaran diperlukan.',
            'payment-method-saved' => 'Metode pembayaran disimpan.',
            'payment-method-failed' => 'Tidak dapat menyimpan metode pembayaran.',

            'place-order' => [
                'success' => 'Pesanan berhasil dibuat.',
                'failed' => 'Tidak dapat membuat pesanan.',
                'error' => 'Tidak dapat membuat pesanan: keranjang memiliki kesalahan.',
                'minimum-order-error' => 'Jumlah pesanan minimum adalah :amount. Tambahkan lebih banyak item sebelum membuat pesanan.',
                'empty-cart' => 'Keranjang kosong — tambahkan item sebelum membuat pesanan.',
                'addresses-required' => 'Alamat penagihan dan pengiriman harus disimpan sebelum membuat pesanan.',
                'shipping-required' => 'Metode pengiriman harus dipilih sebelum membuat pesanan.',
                'payment-required' => 'Metode pembayaran harus dipilih sebelum membuat pesanan.',
                'payment-method-unsupported' => 'Hanya cashondelivery dan moneytransfer yang didukung untuk pesanan yang dibuat oleh admin.',
            ],
        ],

        'product' => [
            'not-found' => 'Produk tidak ditemukan.',

            'mass-delete-success' => 'Produk berhasil dihapus.',
            'mass-update-status-success' => 'Status produk berhasil diperbarui.',
            'indices-required' => 'Kolom indices diperlukan dan harus berupa larik yang tidak kosong.',
            'indices-invalid' => 'Kolom indices harus berupa larik ID produk bilangan bulat.',
            'value-invalid' => 'Kolom value diperlukan dan harus 0 atau 1.',
            'mass-delete-failed' => 'Satu atau lebih produk tidak dapat dihapus.',
            'no-permission' => 'Anda tidak memiliki izin untuk mengelola produk.',

            'copy-success' => 'Produk berhasil disalin.',
            'copy-failed' => 'Gagal menyalin produk.',
            'copy-variant-not-supported' => 'Varian produk konfigurabel tidak dapat disalin. Salin produk konfigurabel induknya sebagai gantinya.',

            'create' => [
                'created' => 'Produk berhasil dibuat.',
                'create-failed' => 'Gagal membuat produk.',
                'sku-required' => 'Kolom sku diperlukan.',
                'sku-unique' => 'sku sudah digunakan.',
                'sku-invalid' => 'sku hanya boleh berisi huruf, angka, dan tanda hubung (mis. "sp-001").',
                'attribute-family-required' => 'Kolom attribute_family_id diperlukan.',
                'attribute-family-not-found' => 'Keluarga atribut yang dipilih tidak ada.',
                'type-required' => 'Kolom type diperlukan.',
                'type-not-yet-supported' => 'Jenis produk ":type" tidak didukung oleh API ini. Jenis yang diizinkan: simple, virtual, downloadable, grouped, bundle, configurable, booking.',
                'super-attributes-required' => 'Kolom super_attributes diperlukan jika type=configurable. Berikan peta kode atribut (atau id) ke option_ids, mis. { "color": [1,2], "size": [4,5] }.',
                'super-attributes-invalid' => 'Kolom super_attributes harus berupa peta tidak kosong dari kode atribut (atau id) ke daftar option_ids yang tidak kosong.',
                'no-permission' => 'Anda tidak memiliki izin untuk membuat produk.',
            ],

            'update' => [
                'updated' => 'Produk berhasil diperbarui.',
                'update-failed' => 'Gagal memperbarui produk.',
                'id-required' => 'Id produk diperlukan.',
                'url-key-required' => 'Kolom url_key tidak boleh kosong.',
                'url-key-unique' => 'url_key sudah digunakan oleh produk lain.',
                'special-price-invalid' => 'special_price harus berupa angka yang lebih kecil dari price.',
                'special-price-date-range-invalid' => 'special_price_to harus sama dengan atau setelah special_price_from.',
                'boolean-field-invalid' => 'Kolom :field harus 0 atau 1.',
                'categories-invalid' => 'Kolom categories harus berupa larik id kategori.',
                'channels-invalid' => 'Kolom channels harus berupa larik id channel.',
                'super-attributes-cannot-change' => 'super_attributes tidak dapat diubah setelah produk dibuat.',
                'sub-resource-stripped-images' => 'Gambar harus dikelola melalui POST /api/admin/catalog/products/{id}/images. Kolom images diabaikan.',
                'sub-resource-stripped-inventories' => 'Inventaris harus dikelola melalui PUT /api/admin/catalog/products/{id}/inventories. Kolom inventories diabaikan.',
                'sub-resource-stripped-customer-group-prices' => 'Harga grup pelanggan harus dikelola melalui /api/admin/catalog/products/{id}/customer-group-prices. Kolom customer_group_prices diabaikan.',
                'translations-single-locale' => 'Hanya lokal yang diminta yang diperbarui. Untuk memperbarui lokal :locales, kirim permintaan terpisah dengan ?locale=<code> untuk masing-masing.',
            ],

            'delete' => [
                'deleted' => 'Produk berhasil dihapus.',
                'delete-failed' => 'Gagal menghapus produk.',
            ],

            'image' => [
                'uploaded' => 'Gambar produk berhasil diunggah.',
                'updated' => 'Gambar produk berhasil diperbarui.',
                'update-empty' => 'Kirim teks alt atau posisi untuk diperbarui.',
                'reordered' => 'Gambar produk berhasil diurutkan ulang.',
                'deleted' => 'Gambar produk berhasil dihapus.',
                'image-required' => 'File gambar diperlukan (kolom multipart "image").',
                'image-invalid-type' => 'Jenis gambar harus salah satu dari: bmp, jpeg, jpg, png, webp.',
                'image-too-large' => 'Gambar terlalu besar. Ukuran maksimum yang diizinkan adalah 4 MB.',
                'not-found' => 'Gambar produk tidak ditemukan.',
                'no-permission' => 'Anda tidak memiliki izin untuk mengelola gambar produk.',
                'upload-failed' => 'Gagal mengunggah gambar produk.',
                'reorder-failed' => 'Gagal mengurutkan ulang gambar produk.',
                'delete-failed' => 'Gagal menghapus gambar produk.',
                'order-required' => 'Larik "order" diperlukan dan tidak boleh kosong.',
                'order-invalid' => 'Setiap entri dalam "order" harus berupa objek dengan id dan position.',
                'id-not-on-product' => 'Gambar :id bukan milik produk ini.',
                'graphql-upload-unsupported' => 'Unggahan gambar melalui GraphQL tidak didukung. Gunakan POST /api/admin/catalog/products/{productId}/images dengan multipart/form-data.',
            ],

            'downloadable' => [
                'file-required' => 'File diperlukan (kolom multipart "file").',
                'file-too-large' => 'File terlalu besar. Ukuran maksimum yang diizinkan adalah 128 MB.',
                'upload-failed' => 'Gagal mengunggah file unduhan.',
                'file-not-found' => 'Tidak ditemukan file tersimpan untuk produk dan atribut ini.',
                'no-permission' => 'Anda tidak memiliki izin untuk mengelola file produk unduhan.',
            ],

            'video' => [
                'uploaded' => 'Video produk berhasil diunggah.',
                'deleted' => 'Video produk berhasil dihapus.',
                'video-required' => 'File video diperlukan (kolom multipart "video").',
                'video-invalid-type' => 'Jenis video harus salah satu dari: mp4, webm, ogg, mov.',
                'video-too-large' => 'Video terlalu besar. Ukuran maksimum yang diizinkan adalah 100 MB.',
                'not-found' => 'Video produk tidak ditemukan.',
                'no-permission' => 'Anda tidak memiliki izin untuk mengelola video produk.',
                'upload-failed' => 'Gagal mengunggah video produk.',
                'delete-failed' => 'Gagal menghapus video produk.',
                'graphql-upload-unsupported' => 'Unggahan video melalui GraphQL tidak didukung. Gunakan POST /api/admin/catalog/products/{productId}/videos dengan multipart/form-data.',
            ],

            'inventory' => [
                'updated' => 'Inventaris produk berhasil disimpan.',
                'inventories-required' => 'Kolom inventories diperlukan dan harus berupa peta tidak kosong dari inventory_source_id ke jumlah.',
                'inventories-invalid' => 'Kolom inventories harus berupa peta dari inventory_source_id (bilangan bulat positif) ke jumlah (bilangan bulat non-negatif).',
                'source-not-found' => 'Sumber inventaris :id tidak ada.',
                'qty-invalid' => 'Setiap jumlah harus berupa bilangan bulat non-negatif.',
                'no-permission' => 'Anda tidak memiliki izin untuk mengelola inventaris produk.',
                'not-found' => 'Produk tidak ditemukan.',
            ],

            'customer-group-price' => [
                'created' => 'Harga grup pelanggan berhasil ditambahkan.',
                'updated' => 'Harga grup pelanggan berhasil diperbarui.',
                'deleted' => 'Harga grup pelanggan berhasil dihapus.',
                'qty-required' => 'Kolom qty diperlukan.',
                'qty-invalid' => 'Kolom qty harus berupa bilangan bulat yang lebih besar dari atau sama dengan 1.',
                'value-type-required' => 'Kolom value_type diperlukan.',
                'value-type-invalid' => 'Kolom value_type harus "fixed" atau "discount".',
                'value-required' => 'Kolom value diperlukan.',
                'value-invalid' => 'Kolom value harus berupa angka non-negatif.',
                'customer-group-not-found' => 'Grup pelanggan yang dipilih tidak ada.',
                'duplicate-qty-group' => 'Harga grup pelanggan untuk kombinasi (qty, customer_group_id) ini sudah ada untuk produk tersebut.',
                'not-found' => 'Harga grup pelanggan tidak ditemukan.',
                'no-permission' => 'Anda tidak memiliki izin untuk mengelola harga grup pelanggan produk.',
            ],
        ],

        'category' => [
            'not-found' => 'Kategori tidak ditemukan.',

            'created' => 'Kategori berhasil dibuat.',
            'updated' => 'Kategori berhasil diperbarui.',
            'deleted' => 'Kategori berhasil dihapus.',
            'slug-required' => 'Kolom slug diperlukan.',
            'slug-unique' => 'slug sudah digunakan.',
            'name-required' => 'Kolom name diperlukan.',
            'position-required' => 'Kolom position diperlukan.',
            'attributes-required' => 'Setidaknya satu atribut yang dapat difilter diperlukan.',
            'description-required-for-mode' => 'Deskripsi diperlukan untuk mode tampilan yang dipilih.',
            'cannot-delete-root' => 'Kategori root dan root channel tidak dapat dihapus.',
            'cannot-delete-channel-root' => 'Kategori ini adalah root channel dan tidak dapat dihapus.',
            'delete-failed' => 'Kategori tidak dapat dihapus.',
            'no-permission' => 'Anda tidak memiliki izin untuk mengelola kategori.',
            'mass-delete-success' => 'Kategori berhasil dihapus.',
            'mass-update-status-success' => 'Status kategori berhasil diperbarui.',
            'mass-delete-indices-required' => 'Kolom indices diperlukan dan harus berupa larik yang tidak kosong.',
            'mass-update-status-value-required' => 'Kolom value diperlukan dan harus 0 atau 1.',
        ],

        'attribute' => [
            'not-found' => 'Atribut tidak ditemukan.',

            'create-success' => 'Atribut berhasil dibuat.',
            'update-success' => 'Atribut berhasil diperbarui.',
            'delete-success' => 'Atribut berhasil dihapus.',
            'delete-failed' => 'Atribut tidak dapat dihapus.',
            'mass-delete-success' => 'Atribut berhasil dihapus.',
            'mass-delete-indices-required' => 'Kolom indices diperlukan dan harus berupa larik yang tidak kosong.',

            'code-immutable' => 'Kode atribut tidak dapat diubah setelah atribut dibuat.',
            'type-immutable' => 'Jenis atribut tidak dapat diubah selama atribut memiliki nilai produk.',
            'locale-scope-immutable' => 'Tidak dapat mengubah cakupan lokal selama atribut memiliki nilai produk.',
            'system-attribute' => 'Atribut sistem tidak dapat dihapus.',
            'in-use-family' => 'Atribut merupakan bagian dari satu atau lebih keluarga atribut (ID grup: :ids). Hapus atribut dari keluarga tersebut terlebih dahulu.',

            'option-not-found' => 'Opsi atribut tidak ditemukan.',
            'option-not-supported' => 'Jenis atribut ":type" tidak mendukung opsi. Hanya atribut select, multiselect, dan checkbox yang dapat memiliki opsi.',
            'option-in-use' => 'Opsi ini digunakan oleh :count produk dan tidak dapat dihapus.',
            'option-delete-success' => 'Opsi atribut berhasil dihapus.',
        ],

        'family' => [
            'not-found' => 'Keluarga atribut tidak ditemukan.',

            'created' => 'Keluarga atribut berhasil dibuat.',
            'updated' => 'Keluarga atribut berhasil diperbarui.',
            'deleted' => 'Keluarga atribut berhasil dihapus.',
            'code-required' => 'Kode keluarga diperlukan.',
            'code-unique' => 'Kode keluarga harus unik.',
            'name-required' => 'Nama keluarga diperlukan.',
            'group-code-required' => 'Setiap grup atribut harus memiliki kode.',
            'group-name-required' => 'Setiap grup atribut harus memiliki nama.',
            'group-column-invalid' => 'Setiap kolom grup atribut harus 1 atau 2.',
            'group-name-duplicate' => 'Nama grup atribut harus unik dalam keluarga atribut.',
            'update-failed' => 'Keluarga atribut tidak dapat diperbarui.',
            'create-failed' => 'Keluarga atribut tidak dapat dibuat.',
            'default-delete-error' => 'Keluarga atribut default tidak dapat dihapus.',
            'last-delete-error' => 'Setidaknya satu keluarga atribut diperlukan.',
            'attribute-product-error' => 'Keluarga atribut ini digunakan oleh satu atau lebih produk. Tetapkan ulang produk tersebut sebelum menghapusnya.',
            'delete-failed' => 'Keluarga atribut tidak dapat dihapus.',
            'no-permission' => 'Anda tidak memiliki izin untuk mengelola keluarga atribut.',
        ],

        'settings' => [
            'locale' => [
                'not-found' => 'Lokal tidak ditemukan.',
                'created' => 'Lokal berhasil dibuat.',
                'updated' => 'Lokal berhasil diperbarui.',
                'deleted' => 'Lokal berhasil dihapus.',
                'cannot-delete-last' => 'Setidaknya satu lokal diperlukan — tidak dapat menghapus lokal terakhir yang tersisa.',
                'cannot-delete-channel-default' => 'Lokal ini adalah lokal default dari satu atau lebih channel dan tidak dapat dihapus.',
                'delete-failed' => 'Lokal tidak dapat dihapus.',
                'no-permission' => 'Anda tidak memiliki izin untuk mengelola lokal.',
                'mass-delete-success' => 'Lokal berhasil dihapus.',
                'mass-delete-indices-required' => 'Kolom indices diperlukan dan harus berupa larik yang tidak kosong.',
            ],

            'theme' => [
                'not-found' => 'Kustomisasi tema tidak ditemukan.',
                'created' => 'Kustomisasi tema berhasil dibuat.',
                'updated' => 'Kustomisasi tema berhasil diperbarui.',
                'deleted' => 'Kustomisasi tema berhasil dihapus.',
                'delete-failed' => 'Kustomisasi tema tidak dapat dihapus.',
                'no-permission' => 'Anda tidak memiliki izin untuk mengelola kustomisasi tema.',
                'mass-delete-success' => 'Kustomisasi tema berhasil dihapus.',
                'mass-delete-indices-required' => 'Kolom indices diperlukan dan harus berupa larik yang tidak kosong.',
                'mass-update-success' => 'Kustomisasi tema berhasil diperbarui.',
                'mass-update-indices-required' => 'Kolom indices diperlukan dan harus berupa larik yang tidak kosong.',
                'mass-update-value-invalid' => 'Kolom value harus 0 atau 1.',
            ],

            'inventory-source' => [
                'not-found' => 'Sumber inventaris tidak ditemukan.',
                'created' => 'Sumber inventaris berhasil dibuat.',
                'updated' => 'Sumber inventaris berhasil diperbarui.',
                'deleted' => 'Sumber inventaris berhasil dihapus.',
                'code-unique' => 'Sumber inventaris dengan kode ini sudah ada.',
                'last-delete-error' => 'Setidaknya satu sumber inventaris diperlukan — tidak dapat menghapus sumber terakhir yang tersisa.',
                'in-use' => 'Sumber inventaris ini dirujuk oleh inventaris produk dan tidak dapat dihapus.',
                'delete-failed' => 'Sumber inventaris tidak dapat dihapus.',
                'no-permission' => 'Anda tidak memiliki izin untuk mengelola sumber inventaris.',
                'mass-delete-success' => 'Sumber inventaris berhasil dihapus.',
                'mass-delete-indices-required' => 'Kolom indices diperlukan dan harus berupa larik yang tidak kosong.',
            ],

            'exchange-rate' => [
                'not-found' => 'Nilai tukar tidak ditemukan.',
                'created' => 'Nilai tukar berhasil dibuat.',
                'updated' => 'Nilai tukar berhasil diperbarui.',
                'deleted' => 'Nilai tukar berhasil dihapus.',
                'duplicate-pair' => 'Nilai tukar untuk mata uang target ini sudah ada. Perbarui baris yang ada atau hapus terlebih dahulu.',
                'delete-failed' => 'Nilai tukar tidak dapat dihapus.',
                'no-permission' => 'Anda tidak memiliki izin untuk mengelola nilai tukar.',
                'mass-delete-success' => 'Nilai tukar berhasil dihapus.',
                'mass-delete-indices-required' => 'Kolom indices diperlukan dan harus berupa larik yang tidak kosong.',
                'update-rates-success' => 'Nilai tukar berhasil diperbarui.',
                'update-rates-failed' => 'Nilai tukar tidak dapat diperbarui dari penyedia eksternal.',
                'provider-not-configured' => 'Tidak ada penyedia nilai tukar eksternal yang dikonfigurasi.',
            ],

            'tax-rate' => [
                'not-found' => 'Tarif pajak tidak ditemukan.',
                'created' => 'Tarif pajak berhasil dibuat.',
                'updated' => 'Tarif pajak berhasil diperbarui.',
                'deleted' => 'Tarif pajak berhasil dihapus.',
                'delete-failed' => 'Tarif pajak tidak dapat dihapus.',
                'no-permission' => 'Anda tidak memiliki izin untuk mengelola tarif pajak.',
                'zip-code-required' => 'Kolom zip_code diperlukan jika is_zip bernilai false.',
                'zip-range-required' => 'zip_from dan zip_to keduanya diperlukan jika is_zip bernilai true.',
            ],

            'tax-category' => [
                'not-found' => 'Kategori pajak tidak ditemukan.',
                'created' => 'Kategori pajak berhasil dibuat.',
                'updated' => 'Kategori pajak berhasil diperbarui.',
                'deleted' => 'Kategori pajak berhasil dihapus.',
                'code-unique' => 'Kode sudah digunakan.',
                'cannot-delete-in-use' => 'Kategori pajak ini masih memiliki tarif pajak terkait dan tidak dapat dihapus.',
                'delete-failed' => 'Kategori pajak tidak dapat dihapus.',
                'no-permission' => 'Anda tidak memiliki izin untuk mengelola kategori pajak.',
            ],

            'role' => [
                'not-found' => 'Peran tidak ditemukan.',
                'created' => 'Peran berhasil dibuat.',
                'updated' => 'Peran berhasil diperbarui.',
                'deleted' => 'Peran berhasil dihapus.',
                'permissions-required' => 'Kolom permissions diperlukan jika permission_type adalah custom.',
                'cannot-delete-in-use' => 'Peran ini ditetapkan ke satu atau lebih admin dan tidak dapat dihapus.',
                'cannot-delete-last-role' => 'Setidaknya satu peran diperlukan — tidak dapat menghapus peran terakhir yang tersisa.',
                'cannot-change-last-all-access' => 'Satu-satunya peran akses penuh tidak dapat diubah menjadi peran kustom.',
                'delete-failed' => 'Peran tidak dapat dihapus.',
                'no-permission' => 'Anda tidak memiliki izin untuk mengelola peran.',
            ],

            'currency' => [
                // Read
                'not-found' => 'Mata uang tidak ditemukan.',

                // Create / Update / Delete
                'created' => 'Mata uang berhasil dibuat.',
                'updated' => 'Mata uang berhasil diperbarui.',
                'deleted' => 'Mata uang berhasil dihapus.',
                'code-required' => 'Kolom code diperlukan.',
                'code-size' => 'Kode harus terdiri dari tepat 3 karakter alfabet.',
                'code-unique' => 'Kode sudah digunakan.',
                'name-required' => 'Kolom name diperlukan.',
                'cannot-delete-last' => 'Setidaknya satu mata uang diperlukan — tidak dapat menghapus mata uang terakhir yang tersisa.',
                'cannot-delete-channel-base' => 'Mata uang ini adalah mata uang dasar dari satu atau lebih channel dan tidak dapat dihapus.',
                'delete-failed' => 'Mata uang tidak dapat dihapus.',
                'no-permission' => 'Anda tidak memiliki izin untuk mengelola mata uang.',

                'mass-delete-success' => 'Mata uang berhasil dihapus.',
                'mass-delete-indices-required' => 'Kolom indices diperlukan dan harus berupa larik yang tidak kosong.',
            ],

            'channel' => [
                'not-found' => 'Channel tidak ditemukan.',

                'created' => 'Channel berhasil dibuat.',
                'updated' => 'Channel berhasil diperbarui.',
                'deleted' => 'Channel berhasil dihapus.',
                'code-unique' => 'Kode sudah digunakan.',
                'hostname-unique' => 'Hostname sudah digunakan.',
                'default-locale-mismatch' => 'default_locale_id harus ada dalam daftar lokal.',
                'base-currency-mismatch' => 'base_currency_id harus ada dalam daftar mata uang.',
                'unknown-locale' => 'Satu atau lebih id lokal tidak ada.',
                'unknown-currency' => 'Satu atau lebih id mata uang tidak ada.',
                'unknown-inventory-source' => 'Satu atau lebih id inventory_source tidak ada.',
                'unknown-root-category' => 'root_category_id tidak ada.',
                'cannot-delete-last' => 'Setidaknya satu channel diperlukan — tidak dapat menghapus channel terakhir yang tersisa.',
                'cannot-delete-default' => 'Tidak dapat menghapus channel aplikasi default.',
                'delete-failed' => 'Channel tidak dapat dihapus.',
                'no-permission' => 'Anda tidak memiliki izin untuk mengelola channel.',
            ],

            'user' => [
                'not-found' => 'Pengguna admin tidak ditemukan.',
                'created' => 'Pengguna admin berhasil dibuat.',
                'updated' => 'Pengguna admin berhasil diperbarui.',
                'deleted' => 'Pengguna admin berhasil dihapus.',
                'name-required' => 'Kolom name diperlukan.',
                'email-required' => 'Kolom email diperlukan.',
                'email-invalid' => 'Email harus berupa alamat email yang valid.',
                'email-unique' => 'Email sudah digunakan.',
                'password-required' => 'Kolom password diperlukan.',
                'password-min' => 'Kata sandi harus terdiri dari minimal 6 karakter.',
                'role-required' => 'Kolom role_id diperlukan.',
                'role-not-found' => 'Peran yang dipilih tidak ada.',
                'status-invalid' => 'Status harus 0 atau 1.',
                'cannot-delete-self' => 'Anda tidak dapat menghapus akun admin Anda sendiri.',
                'cannot-delete-last-admin' => 'Setidaknya satu pengguna admin diperlukan — tidak dapat menghapus admin terakhir yang tersisa.',
                'cannot-change' => ':name dari admin akses penuh terakhir tidak dapat diubah.',
                'delete-failed' => 'Pengguna admin tidak dapat dihapus.',
                'no-permission' => 'Anda tidak memiliki izin untuk mengelola pengguna admin.',
                'self-delete-password-required' => 'Kolom password diperlukan untuk menghapus akun Anda sendiri.',
                'self-delete-incorrect-password' => 'Kata sandi salah.',
                'self-deleted' => 'Akun admin Anda telah dihapus.',
            ],

            'data-transfer' => [
                'import' => [
                    'not-found' => 'Impor tidak ditemukan.',
                    'deleted' => 'Impor berhasil dihapus.',
                    'cancelled' => 'Impor berhasil dibatalkan.',
                    'cannot-cancel' => 'Hanya impor yang tertunda atau sedang diproses yang dapat dibatalkan. Status saat ini: :state.',
                    'no-permission' => 'Anda tidak memiliki izin untuk mengelola impor transfer data.',
                    'delete-failed' => 'Impor tidak dapat dihapus.',
                    'created' => 'Impor berhasil dibuat.',
                    'updated' => 'Impor berhasil diperbarui.',
                    'type-invalid' => 'Jenis yang dipilih tidak valid.',
                    'action-invalid' => 'Tindakan harus salah satu dari: append, delete.',
                    'validation-strategy-invalid' => 'Strategi validasi harus salah satu dari: stop-on-errors, skip-errors.',
                    'allowed-errors-invalid' => 'Jumlah kesalahan yang diizinkan harus berupa bilangan bulat 0 atau lebih.',
                    'field-separator-required' => 'Pemisah kolom diperlukan.',
                    'file-required' => 'File diperlukan.',
                    'file-invalid-type' => 'File harus salah satu dari: csv, xls, xlsx, xml.',
                    'nothing-to-import' => 'Tidak ada yang dapat diimpor.',
                    'not-valid' => 'Impor tidak valid.',
                    'setup-queue-error' => 'Koneksi antrean diatur ke sync; konfigurasikan antrean yang sebenarnya untuk memproses impor di latar belakang.',
                    'file-not-found' => 'Tidak ada file yang tersedia untuk diunduh.',
                    'error-report-not-found' => 'Tidak ada laporan kesalahan yang tersedia untuk diunduh.',
                    'sample-not-found' => 'Tidak ada file contoh yang tersedia untuk jenis dan format yang diminta.',
                    'sample-format-invalid' => 'Format contoh harus salah satu dari: csv, xls, xlsx, xml.',
                    'images-archive-too-large' => 'Arsip gambar dapat berisi paling banyak :entries file dan terekstrak menjadi paling besar :size MB.',
                ],
            ],
        ],

        'cms' => [
            'page' => [
                'not-found' => 'Halaman CMS tidak ditemukan.',

                'created' => 'Halaman CMS berhasil dibuat.',
                'url-key-required' => 'Kolom url_key diperlukan.',
                'url-key-unique' => 'url_key sudah digunakan.',
                'url-key-invalid' => 'Format url_key tidak valid. Gunakan hanya huruf kecil, angka, dan tanda hubung.',
                'page-title-required' => 'Kolom page_title diperlukan.',
                'html-content-required' => 'Kolom html_content diperlukan.',
                'channels-required' => 'Setidaknya satu channel harus dipilih.',
                'channels-invalid' => 'Satu atau lebih channel tidak valid.',

                'updated' => 'Halaman CMS berhasil diperbarui.',

                'deleted' => 'Halaman CMS berhasil dihapus.',
                'delete-failed' => 'Halaman CMS tidak dapat dihapus.',

                'mass-deleted' => 'Halaman CMS berhasil dihapus.',
                'indices-required' => 'Kolom indices diperlukan dan harus berupa larik yang tidak kosong.',
                'indices-invalid' => 'Kolom indices harus berupa larik ID halaman CMS.',

                'no-permission' => 'Anda tidak memiliki izin untuk mengelola halaman CMS.',
            ],
        ],

        'marketing' => [
            'catalog-rule' => [
                'not-found' => 'Aturan katalog tidak ditemukan.',
                'created' => 'Aturan katalog berhasil dibuat.',
                'updated' => 'Aturan katalog berhasil diperbarui.',
                'deleted' => 'Aturan katalog berhasil dihapus.',
                'delete-failed' => 'Aturan katalog tidak dapat dihapus.',
                'mass-delete-indices-required' => 'Tidak ada ID aturan katalog yang diberikan untuk penghapusan massal.',
                'mass-delete-success' => 'Aturan katalog yang dipilih berhasil dihapus.',
                'no-permission' => 'Anda tidak memiliki izin untuk mengelola aturan katalog.',
            ],

            'campaign' => [
                'not-found' => 'Kampanye tidak ditemukan.',
                'created' => 'Kampanye berhasil dibuat.',
                'updated' => 'Kampanye berhasil diperbarui.',
                'deleted' => 'Kampanye berhasil dihapus.',
                'delete-failed' => 'Kampanye tidak dapat dihapus.',
                'no-permission' => 'Anda tidak memiliki izin untuk mengelola kampanye.',

                'send' => [
                    'id-required' => 'Id kampanye diperlukan.',
                    'inactive' => 'Tidak dapat mengirim kampanye yang tidak aktif. Aktifkan terlebih dahulu.',
                    'queued' => 'Kampanye dimasukkan ke antrean untuk :count penerima.',
                ],
            ],

            'sitemap' => [
                'not-found' => 'Sitemap tidak ditemukan.',
                'created' => 'Sitemap berhasil dibuat.',
                'updated' => 'Sitemap berhasil diperbarui.',
                'deleted' => 'Sitemap berhasil dihapus.',
                'delete-failed' => 'Sitemap tidak dapat dihapus.',
                'no-permission' => 'Anda tidak memiliki izin untuk mengelola sitemap.',

                'generate' => [
                    'id-required' => 'Id sitemap diperlukan.',
                    'no-channels' => 'Sitemap ini tidak mencakup channel apa pun, sehingga tidak ada yang dapat dibuat. Tetapkan setidaknya satu channel terlebih dahulu.',
                    'success' => 'Sitemap berhasil dibuat ulang.',
                    'failed' => 'Pembuatan sitemap gagal: :message',
                ],
            ],

            'cart-rule' => [
                'not-found' => 'Aturan keranjang tidak ditemukan.',
                'id-required' => 'Id aturan keranjang diperlukan.',
                'copy-of' => 'Salinan dari :value',
                'copied' => 'Aturan keranjang berhasil disalin.',
                'created' => 'Aturan keranjang berhasil dibuat.',
                'updated' => 'Aturan keranjang berhasil diperbarui.',
                'deleted' => 'Aturan keranjang berhasil dihapus.',
                'delete-failed' => 'Aturan keranjang tidak dapat dihapus.',
                'no-permission' => 'Anda tidak memiliki izin untuk mengelola aturan keranjang.',
                'mass-delete-success' => 'Aturan keranjang berhasil dihapus.',
                'mass-delete-indices-required' => 'Kolom indices diperlukan dan harus berupa larik yang tidak kosong.',
                'action-type-invalid' => 'action_type harus salah satu dari: by_percent, by_fixed, cart_fixed, buy_x_get_y.',
                'coupon-code-required' => 'coupon_code diperlukan jika coupon_type=1 dan use_auto_generation=0.',
                'coupon-code-unique' => 'Kode kupon ini sudah digunakan.',
            ],

            'template' => [
                'campaign-associated' => 'Templat email ini digunakan oleh kampanye, sehingga tidak dapat dihapus. Hapus dari kampanye terlebih dahulu.',
                'not-found' => 'Templat email tidak ditemukan.',
                'created' => 'Templat email berhasil dibuat.',
                'updated' => 'Templat email berhasil diperbarui.',
                'deleted' => 'Templat email berhasil dihapus.',
                'delete-failed' => 'Templat email tidak dapat dihapus.',
                'status-invalid' => 'Status harus salah satu dari: active, inactive, draft.',
                'no-permission' => 'Anda tidak memiliki izin untuk mengelola templat email.',
            ],

            'event' => [
                'campaign-associated' => 'Acara ini digunakan oleh kampanye, sehingga tidak dapat dihapus. Hapus dari kampanye terlebih dahulu.',
                'not-found' => 'Acara pemasaran tidak ditemukan.',
                'created' => 'Acara pemasaran berhasil dibuat.',
                'updated' => 'Acara pemasaran berhasil diperbarui.',
                'deleted' => 'Acara pemasaran berhasil dihapus.',
                'delete-failed' => 'Acara pemasaran tidak dapat dihapus.',
                'no-permission' => 'Anda tidak memiliki izin untuk mengelola acara pemasaran.',
            ],

            'search-synonym' => [
                'not-found' => 'Sinonim pencarian tidak ditemukan.',
                'created' => 'Sinonim pencarian berhasil dibuat.',
                'updated' => 'Sinonim pencarian berhasil diperbarui.',
                'deleted' => 'Sinonim pencarian berhasil dihapus.',
                'mass-deleted' => 'Sinonim pencarian berhasil dihapus.',
                'delete-failed' => 'Sinonim pencarian tidak dapat dihapus.',
                'indices-required' => 'Kolom indices diperlukan dan harus berupa larik ID sinonim pencarian yang tidak kosong.',
                'no-permission' => 'Anda tidak memiliki izin untuk mengelola sinonim pencarian.',
            ],

            'url-rewrite' => [
                'not-found' => 'Penulisan ulang URL tidak ditemukan.',
                'created' => 'Penulisan ulang URL berhasil dibuat.',
                'updated' => 'Penulisan ulang URL berhasil diperbarui.',
                'deleted' => 'Penulisan ulang URL berhasil dihapus.',
                'delete-failed' => 'Penulisan ulang URL tidak dapat dihapus.',
                'mass-deleted' => 'Penulisan ulang URL berhasil dihapus.',
                'indices-required' => 'Setidaknya satu id penulisan ulang URL diperlukan.',
                'no-permission' => 'Anda tidak memiliki izin untuk mengelola penulisan ulang URL.',
            ],

            'cart-rule-coupon' => [
                'cart-rule-not-found' => 'Aturan keranjang tidak ditemukan.',
                'not-found' => 'Kupon tidak ditemukan.',
                'code-taken' => 'Kupon dengan kode ini sudah ada.',
                'format-invalid' => 'Format harus salah satu dari: alphabetic, alphanumeric, numeric.',
                'indices-required' => 'Kolom indices diperlukan dan harus berupa larik ID kupon yang tidak kosong.',
                'created' => 'Kupon berhasil dibuat.',
                'generated' => 'Berhasil membuat :count kupon.',
                'deleted' => 'Kupon berhasil dihapus.',
                'mass-deleted' => 'Berhasil menghapus :count kupon.',
                'no-permission' => 'Anda tidak memiliki izin untuk mengelola kupon aturan keranjang.',
            ],

            'subscriber' => [
                'not-found' => 'Pelanggan newsletter tidak ditemukan.',
                'updated' => 'Langganan berhasil diperbarui.',
                'deleted' => 'Langganan berhasil dihapus.',
                'is-subscribed-required' => 'Kolom is_subscribed diperlukan.',
                'no-permission' => 'Anda tidak memiliki izin untuk mengelola pelanggan newsletter.',
            ],

            'search-term' => [
                'not-found' => 'Istilah pencarian tidak ditemukan.',
                'updated' => 'Istilah pencarian berhasil diperbarui.',
                'deleted' => 'Istilah pencarian berhasil dihapus.',
                'mass-deleted' => 'Istilah pencarian berhasil dihapus.',
                'term-required' => 'Kolom term diperlukan.',
                'redirect-url-invalid' => 'redirect_url harus berupa URL yang valid.',
                'indices-required' => 'Setidaknya satu ID harus diberikan dalam indices.',
                'no-permission' => 'Anda tidak memiliki izin untuk mengelola istilah pencarian.',
            ],
        ],
        'auth' => [
            'configuration' => [
                'title' => 'Autentikasi Admin',
                'info' => 'Masa berlaku token, batas laju, dan aturan keamanan untuk login API admin dan token API.',

                'tokens' => [
                    'title' => 'Masa Berlaku Token',
                    'info' => 'Berapa lama token akses, token penyegaran, dan sesi tetap berlaku. Perubahan berlaku untuk token yang diterbitkan setelahnya.',
                    'access-token-ttl' => 'Masa Berlaku Token Akses (menit)',
                    'access-token-ttl-info' => 'Antara 5 dan 60 menit. Bawaan 15.',
                    'refresh-token-ttl' => 'Masa Berlaku Token Penyegaran (hari)',
                    'refresh-token-ttl-info' => 'Antara 1 dan 90 hari. Bawaan 30.',
                    'session-lifetime' => 'Masa Berlaku Maksimum Sesi (hari)',
                    'session-lifetime-info' => 'Antara 1 dan 365 hari. Bawaan 90. Setelah itu admin harus login lagi, berapa kali pun sesi disegarkan.',
                ],

                'rate-limits' => [
                    'title' => 'Batas Laju',
                    'info' => 'Jumlah maksimum permintaan API admin per menit.',
                    'session-requests-per-minute' => 'Permintaan per Menit per Admin yang Login',
                    'api-token-requests-per-minute' => 'Permintaan per Menit per Token API',
                ],

                'security' => [
                    'title' => 'Keamanan',
                    'info' => 'Aturan pencabutan, kedaluwarsa token API, dan pengikatan domain.',
                    'revoke-sessions-on-password-change' => 'Cabut Sesi Saat Kata Sandi Diubah',
                    'revoke-api-tokens-on-password-change' => 'Cabut Token API Saat Kata Sandi Diubah',
                    'allow-non-expiring-api-tokens' => 'Izinkan Token API Tanpa Kedaluwarsa',
                    'domain-binding-mode' => 'Mode Pengikatan Domain',
                    'domain-binding-mode-info' => 'DNS memeriksa bahwa permintaan berasal dari alamat IP tujuan domain token. Origin memeriksa header Origin atau Referer dan hanya cocok untuk browser.',
                    'domain-binding-dns' => 'DNS (alamat IP klien)',
                    'domain-binding-origin' => 'Header Origin',
                ],
            ],

            'errors' => [
                'unauthenticated' => 'Anda tidak terautentikasi.',
                'access-token-expired' => 'Token akses telah kedaluwarsa. Segarkan untuk melanjutkan.',
                'api-token-expired' => 'Token API telah kedaluwarsa.',
                'refresh-token-invalid' => 'Token penyegaran tidak valid atau telah kedaluwarsa. Silakan login kembali.',
                'api-token-revoked' => 'Token API telah dicabut.',
                'api-token-domain-mismatch' => 'Token API tidak dapat digunakan dari domain ini.',
                'access-token-required' => 'Tindakan ini memerlukan token akses dari sesi login; token API tidak dapat menggunakannya.',
                'two-factor-required' => 'Autentikasi dua faktor aktif untuk akun ini, dan login API dengan faktor kedua belum tersedia.',
                'too-many-login-attempts' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam :seconds detik.',
                'rate-limit-exceeded' => 'Terlalu banyak percobaan.',
                'no-permission' => 'Anda tidak memiliki izin untuk mengelola sesi API admin.',
            ],

            'sessions' => [
                'logged-out' => 'Anda telah keluar.',
                'logged-out-all' => 'Anda telah keluar dari semua sesi.',
                'not-found' => 'Sesi tidak ditemukan.',
                'revoked' => 'Sesi telah dicabut.',
                'revoked-for-admin' => 'Sesi yang dicabut: :count.',
            ],

            'acl' => [
                'api-sessions' => 'Sesi API',
                'revoke' => 'Cabut Sesi',
            ],
        ],
    ],

    'integration' => [
        'menu' => [
            'title' => 'Integrasi',
            'tokens' => 'Token',
        ],

        'history' => [
            'menu' => [
                'title' => 'Sejarah',
            ],

            'acl' => [
                'title' => 'Riwayat Perubahan API',
                'view' => 'Lihat',
                'delete' => 'Hapus Riwayat',
            ],

            'index' => [
                'title' => 'Riwayat Perubahan API',
                'info' => 'Setiap pembuatan, pembaruan, dan penghapusan dilakukan melalui admin API, dengan siapa yang melakukannya, token apa, dan apa yang berubah.',
                'cleanup-btn' => 'Hapus log lama',
                'cleanup-days' => 'Hapus log yang lebih lama dari beberapa hari ini',
                'cleanup-confirm' => 'Hapus semua riwayat yang lebih lama dari jumlah hari yang ditentukan? Hal ini tidak dapat dibatalkan.',
            ],

            'view' => [
                'title' => 'Perubahan',
                'back-btn' => 'Kembali',
                'admin' => 'Admin',
                'token' => 'Tanda',
                'action' => 'Tindakan',
                'resource' => 'Sumber daya',
                'method' => 'Metode',
                'ip' => 'Alamat IP',
                'date' => 'Tanggal',
                'version' => 'Versi',
                'url' => 'Titik akhir',
                'request-details' => 'Detail Permintaan',
                'changes' => 'Perubahan',
                'field' => 'Bidang',
                'old' => 'Nilai lama',
                'new' => 'Nilai baru',
                'no-field-changes' => 'Tidak ada perubahan tingkat bidang yang dicatat untuk entri ini.',
                'same-request' => 'Perubahan lain dalam permintaan yang sama',
                'version-chain' => 'Riwayat versi catatan ini',
            ],

            'datagrid' => [
                'id' => 'tanda pengenal',
                'date' => 'Tanggal',
                'admin' => 'Admin',
                'token' => 'Tanda',
                'action' => 'Tindakan',
                'operation' => 'Operasi',
                'resource' => 'Sumber daya',
                'version' => 'Versi',
                'method' => 'Metode',
                'ip' => 'IP',
                'view' => 'Lihat',
                'delete' => 'Hapus',
            ],

            'events' => [
                'created' => 'Dibuat',
                'updated' => 'Diperbarui',
                'deleted' => 'Dihapus',
            ],

            'deleted' => ':count catatan riwayat dihapus.',
            'cleanup-input-required' => 'Cantumkan jumlah hari atau tanggal untuk membersihkan.',
        ],

        'acl' => [
            'title' => 'Integrasi',
            'view' => 'Lihat',
            'create' => 'Buat Integrasi',
            'edit' => 'Sunting Integrasi',
            'delete' => 'Cabut Token Integrasi',
            'generate' => 'Hasilkan Token Integrasi',
            'regenerate' => 'Regenerasi Token Integrasi',
        ],

        'index' => [
            'title' => 'Integrasi',
            'create-btn' => 'Buat Integrasi',
        ],

        'create' => [
            'title' => 'Buat Integrasi',
            'save-btn' => 'Simpan',
            'back-btn' => 'Kembali',
        ],

        'edit' => [
            'title' => 'Sunting Integrasi',
            'save-btn' => 'Simpan',
            'back-btn' => 'Kembali',
            'generate-btn' => 'Hasilkan Token',
            'regenerate-btn' => 'Regenerasi Token',
            'revoke-btn' => 'Cabut Token',
            'copy-btn' => 'Salin',
            'token-warning' => 'Simpan token ini sekarang — token ini tidak akan ditampilkan lagi.',
            'token-label' => 'Tanda',
            'not-generated' => 'Belum dihasilkan',
            'masked' => '(Disimpan — hanya ditampilkan sekali pada generasi)',
            'history-banner' => 'Token ini sudah tidak aktif.',
        ],

        'fields' => [
            'name' => 'Nama',
            'description' => 'Deskripsi',
            'assign-user' => 'Tetapkan Pengguna',
            'permission-type' => 'Jenis Izin',
            'access-control' => 'Kontrol Akses',
            'general' => 'Umum',
            'token-settings' => 'Pengaturan Token',
            'valid-till' => 'Berlaku Sampai',
            'rate-limit-per-minute' => 'Batas Tarif (per menit)',
            'rate-limit-per-day' => 'Batas Tarif (per hari)',
            'never-expires' => 'Tidak pernah kadaluarsa',
            'expires-on' => 'Kedaluwarsa pada',
            'unlimited' => 'Tidak terbatas',
            'limit-to' => 'Batasi hingga',
            'requests-per-minute' => 'permintaan / menit',
            'requests-per-day' => 'permintaan / hari',
            'select-admin' => 'Pilih admin',
            'no-available-admins' => 'Tidak ada admin yang tersedia — setiap admin sudah memiliki token aktif.',
            'same-as-web-hint' => 'Token akan mencerminkan izin peran admin yang ditugaskan saat ini secara langsung.',
            'ip-allowlist' => 'Daftar IP yang Diizinkan',
            'ip-any' => 'IP apa pun (default)',
            'ip-restricted' => 'Dibatasi untuk IP tertentu',
            'ip-list-hint' => 'Satu entri per baris. Mendukung IPv4, IPv6 dan CIDR (misalnya 10.0.0.0/24 atau 2001:db8::/32). Biarkan kosong untuk mengizinkan semua IP.',
        ],

        'permission-type' => [
            'all' => 'Semua',
            'custom' => 'Adat',
            'same-as-web' => 'Sama seperti Izin Web',
        ],

        'status' => [
            'draft' => 'Draf',
            'active' => 'Aktif',
            'revoked' => 'Dicabut',
            'regenerated' => 'Diregenerasi',
        ],

        'datagrid' => [
            'id' => 'tanda pengenal',
            'name' => 'Nama',
            'admin' => 'Admin',
            'token' => 'Tanda',
            'status' => 'Status',
            'permission-type' => 'Jenis Izin',
            'expires-at' => 'Berlaku Sampai',
            'last-used-at' => 'Terakhir Digunakan',
            'created-at' => 'Dibuat Pada',
            'edit' => 'Sunting',
            'revoke' => 'Cabut',
        ],

        'messages' => [
            'draft-created' => 'Integrasi tercipta. Hasilkan token untuk mulai menggunakannya.',
            'updated' => 'Integrasi berhasil diperbarui.',
            'generated' => 'Token dihasilkan. Salin sekarang — itu tidak akan ditampilkan lagi.',
            'regenerated' => 'Token dibuat ulang. Salin token baru sekarang — token tidak akan ditampilkan lagi.',
            'revoked' => 'Token berhasil dicabut.',
            'generate-only-draft' => 'Hanya draf integrasi yang dapat membuat tokennya.',
            'regenerate-only-active' => 'Hanya token aktif yang dapat dibuat ulang.',
            'cannot-edit-historic' => 'Token yang dicabut atau dibuat ulang tidak dapat diedit.',
            'already-inactive' => 'Token ini sudah tidak aktif.',
        ],

        'errors' => [
            'admin-has-token' => 'Admin yang dipilih sudah memiliki token integrasi aktif.',
            'only-draft-generate' => 'Hanya token draf yang dapat dibuat.',
        ],

        'validation' => [
            'ip-invalid' => 'Setiap IP yang diizinkan harus berupa alamat IPv4 atau IPv6 yang valid (didukung notasi CIDR).',
            'cidr-prefix-invalid' => 'Awalan CIDR tidak valid untuk versi IP yang diberikan.',
        ],

        'configuration' => [
            'api' => [
                'title' => 'API',
                'info' => 'Pengaturan Bagisto API dan modul adminnya.',
            ],

            'integration' => [
                'title' => 'Integrasi',
                'info' => 'Kelola plugin Integrasi API yang digunakan untuk menerbitkan token API admin.',
            ],

            'settings' => [
                'title' => 'Pengaturan Modul',
                'info' => 'Mengaktifkan atau menonaktifkan plugin Integrasi API. Saat dinonaktifkan, menu sidebarnya disembunyikan dan halamannya kembali menjadi 404.',
                'enable' => 'Aktifkan Modul Integrasi API',
            ],
        ],

        'emails' => [
            'generated' => [
                'subject' => 'Token API baru telah dibuat: :name',
                'greeting' => 'Token integrasi API bernama ":name" baru saja dibuat di akun Anda.',
            ],

            'regenerated' => [
                'subject' => 'Token API Anda telah dibuat ulang: :name',
                'greeting' => 'Token integrasi API bernama ":name" baru saja dibuat ulang. Token sebelumnya telah berhenti berfungsi — hanya token baru yang valid.',
            ],

            'revoked' => [
                'subject' => 'Token API Anda telah dicabut: :name',
                'greeting' => 'Token integrasi API bernama ":name" telah dicabut. Setiap klien yang menggunakannya telah kehilangan akses.',
            ],

            'details' => [
                'name' => 'Nama Token',
                'date' => 'Tanggal',
                'ip' => 'Dari pengusaha perorangan',
            ],

            'revoke-hint' => 'Jika Anda tidak menduganya, segera cabut token menggunakan tombol di bawah.',
            'revoke-btn' => 'Cabut Token Ini',
            'revoke-expiry' => 'Tautan pencabutan ini berlaku selama 7 hari. Setelah itu, masuk ke panel admin untuk mengelola token.',
            'no-action' => 'Tidak diperlukan tindakan apa pun — email ini hanya konfirmasi.',
        ],

        'revoke-confirmation' => [
            'title' => 'Cabut Token API',
            'confirm-title' => 'Cabut token ini?',
            'confirm-message' => 'Token ":name" akan segera berhenti berfungsi untuk setiap klien yang menggunakannya.',
            'confirm-button' => 'Cabut token',
            'success-title' => 'Token Dicabut',
            'success-message' => 'Token ":name" telah dicabut. Setiap klien yang menggunakannya akan segera kehilangan akses.',
            'already-inactive-title' => 'Token Sudah Tidak Aktif',
            'already-inactive-message' => 'Token ":name" telah dicabut atau dibuat ulang. Tidak diperlukan tindakan lebih lanjut.',
        ],

        'confirm' => [
            'generate' => [
                'title' => 'Hasilkan Token',
                'message' => 'Hasilkan token sekarang? Teks biasa hanya akan ditampilkan sekali — salin sebelum meninggalkan halaman.',
            ],

            'regenerate' => [
                'title' => 'Regenerasi Token',
                'message' => 'Regenerasi tokennya? Token lama akan segera berhenti berfungsi dan teks biasa baru hanya akan ditampilkan satu kali.',
            ],

            'revoke' => [
                'title' => 'Cabut Token',
                'message' => 'Cabut token ini? Setiap klien yang menggunakannya akan segera kehilangan akses. Tindakan ini tidak dapat dibatalkan.',
            ],
        ],
    ],
];
