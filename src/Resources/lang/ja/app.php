<?php

return [

    'graphql' => [
        'headers' => [
            'unknown-channel' => 'X-Channel ヘッダーのチャネル ":code" は存在しません。',
            'storefront-key-required' => 'X-STOREFRONT-KEY ヘッダーが必要です',
            'storefront-key-invalid' => 'ストアフロントキーが無効です',
            'storefront-key-rate-limited' => 'レート制限を超えました。:seconds 秒後に再試行してください',
        ],

        'cart' => [
            'authentication-required' => '認証トークンが必要です',
            'invalid-token' => '認証トークンが無効か期限切れです',
            'unauthorized-access' => 'カートへの不正なアクセスです',
            'authenticated-only' => '認証済みのユーザーのみが自分のカートを取得できます',
            'merge-requires-auth' => 'ゲストカートの統合には認証が必要です',
            'unknown-operation' => '不明なカート操作です',

            'cart-not-found' => 'カートが見つかりません',
            'guest-cart-not-found' => 'ゲストカートが見つかりません',
            'product-not-found' => '商品が見つかりません',

            'product-id-quantity-required' => '商品IDと数量が必要です',
            'cart-item-id-quantity-required' => 'カートアイテムIDと数量が必要です',

            'file-token-invalid' => 'アップロードされたファイルの参照が無効か期限切れです。ファイルを再度アップロードしてください。',
            'file-token-forbidden' => 'このアップロードファイルはお客様のカートに属していません。',
            'customizable-option-required' => 'カスタマイズオプション「:label」は必須です。',
            'customizable-file-option-invalid' => '選択されたオプションは、この商品のファイルオプションではありません。',
            'customizable-file-invalid' => 'ファイルがないか、サポートされていない拡張子であるか、サイズが大きすぎます。',
            'cart-item-id-required' => 'カートアイテムIDが必要です',
            'item-ids-required' => 'アイテムIDの配列が必要です',

            'event-booking-quantity-not-changeable' => 'イベント予約商品の数量は変更できません。数量はチケットの選択によって決まります。',
            'appointment-booking-quantity-not-changeable' => 'アポイントメント予約商品の数量は変更できません。',
            'coupon-code-required' => 'クーポンコードが必要です',
            'address-data-required' => '国、都道府県、郵便番号が必要です',
            'grouped-qty-required' => 'グループ商品には数量の選択が必要です。groupedQty を JSON 文字列で渡してください。例: {"101":2,"102":1}。',
            'grouped-qty-must-include-all' => 'グループ商品にはすべての関連商品の数量が必要です。不足している ID: :ids。',
            'grouped-qty-invalid-associated' => 'グループ商品の数量に無効な関連商品 ID が含まれています: :ids。',
            'grouped-qty-invalid-quantity' => '関連商品 ID :id に指定された数量が無効です。数量は 0 以上の整数である必要があります。',
            'bundle-qty-not-changeable' => 'バンドルオプション「:option」の数量は変更できません。許可されている数量は :qty です。',

            'add-product-failed' => '商品をカートに追加できませんでした',
            'update-item-failed' => 'カートアイテムを更新できませんでした',
            'remove-item-failed' => 'カートアイテムを削除できませんでした',
            'apply-coupon-failed' => 'クーポンを適用できませんでした',
            'remove-coupon-failed' => 'クーポンを削除できませんでした',
            'coupon-applied' => 'クーポンが正常に適用されました',
            'coupon-removed' => 'クーポンが正常に削除されました',
            'move-to-wishlist-failed' => 'アイテムをほしい物リストに移動できませんでした',
            'estimate-shipping-failed' => '送料を見積もることができませんでした',

            'product-added-successfully' => '商品がカートに正常に追加されました',
            'cart-item-updated-successfully' => 'カートアイテムが正常に更新されました',
            'guest-cart-merged' => 'ゲストカートが正常に統合されました',
            'using-authenticated-cart' => '認証済みの顧客カートを使用しています',
            'cart-item-not-found' => 'カートアイテムが見つかりません',
            'new-guest-cart-created' => '一意のセッショントークンを持つ新しいゲストカートが作成されました',
            'select-items-to-remove' => '削除するアイテムを選択してください',
            'select-items-to-move-wishlist' => 'ほしい物リストに移動するアイテムを選択してください',
            'invalid-or-expired-token' => 'カートトークンが無効か期限切れです。新しいカートを作成してください。',
            'invalid-token-of-login-user' => 'ログインユーザーのトークンが無効です。',
            'booking-note-required' => 'テーブル予約には booking.note が必要です。',
            'booking-date-required' => 'booking.slot が書式設定された時間範囲の場合、booking.date が必要です。',
            'booking-qty-required' => 'イベント予約には booking.qty が必要です。',
            'booking-ticket-qty-invalid' => 'イベントチケットの数量は 0 以上の数値である必要があります。',
            'booking-ticket-required' => 'イベント予約のチケット数量を少なくとも 1 つ選択してください。',
            'booking-slot-unavailable' => '選択されたスロットは指定された日付では利用できません。',
            'invalid-quantity' => '数量は1以上である必要があります',
            'product-id-required' => '商品IDが必要です',
            'shipping-address-required' => '配送料の見積もりには国と郵便番号が必要です',
        ],

        'token-verification' => [
            'invalid-operation' => '無効な操作です',
            'invalid-input-data' => '無効な入力データです',
            'token-required' => 'トークンが必要です',
            'invalid-token-format' => 'トークンの形式が無効です',
            'token-not-found-or-expired' => 'トークンが見つからないか期限切れです',
            'customer-not-found' => '顧客が見つかりません',
            'customer-account-suspended' => '顧客アカウントは停止されています',
            'error-verifying-token' => 'トークンの検証中にエラーが発生しました',
            'token-is-valid' => 'トークンは有効です',
            'unauthenticated' => '認証されていません。この操作を行うにはログインしてください',
        ],

        'forgot-password' => [
            'invalid-operation' => '無効な操作です',
            'invalid-input-data' => '無効な入力データです',
            'email-required' => 'メールアドレスが必要です',
            'reset-link-sent' => 'リセットリンクがメールに正常に送信されました',
            'email-not-found' => 'メールアドレスが見つかりません',
            'throttled' => 'リセットの試行回数が多すぎます。しばらく待ってから再度お試しください。',
            'error-sending-reset-link' => 'リセットリンクの送信中にエラーが発生しました',
        ],

        'reset-password' => [
            'failed' => 'パスワードをリセットできませんでした。もう一度お試しください。',
        ],

        'rate-limit' => [
            'too-many-attempts' => '試行回数が多すぎます。:seconds 秒後にもう一度お試しください。',
        ],

        'logout' => [
            'invalid-operation' => '無効な操作です',
            'invalid-input-data' => '無効な入力データです',
            'token-required' => 'トークンが必要です',
            'invalid-token-format' => 'トークンの形式が無効です',
            'logged-out-successfully' => '正常にログアウトしました',
            'token-not-found-or-expired' => 'トークンが見つからないか、すでに期限切れです',
            'error-during-logout' => 'ログアウト中にエラーが発生しました',
            'unauthenticated' => '認証されていません。この操作を行うにはログインしてください',
        ],

        'address' => [
            'deleted-successfully' => '住所が正常に削除されました',
            'authentication-required' => '認証トークンが必要です',
            'invalid-token' => 'トークンが無効か期限切れです',
            'unknown-operation' => '不明な操作です',
            'address-id-required' => '住所IDが必要です',
            'address-not-found' => '住所が見つからないか、この顧客に属していません',
            'retrieved' => '住所が正常に取得されました',
            'fetch-failed' => '住所の取得に失敗しました:',
            'field-required' => ':field フィールドは必須です',
        ],

        'customer-profile' => [
            'authentication-required' => '認証トークンが必要です。クエリの入力にトークンを指定してください',
            'invalid-token' => 'トークンが無効か期限切れです',
            'profile-updated' => '顧客プロフィールが正常に更新されました',
        ],

        'customer' => [
            'password-mismatch' => 'パスワードと確認用パスワードが一致しません',
            'confirm-password-required' => 'パスワードを変更する際は確認用パスワードが必要です',
            'current-password-required' => 'パスワードを変更するには現在のパスワードが必要です',
            'current-password-incorrect' => '入力された現在のパスワードが正しくありません',
            'verify-email-to-login' => 'アカウントが作成されました。ログインするにはメールアドレスを確認してください。',
            'delete-password-required' => 'アカウントを削除するにはパスワードが必要です',
            'delete-password-incorrect' => '入力されたパスワードが正しくありません',
            'delete-has-active-orders' => '保留中または処理中の注文がある間はアカウントを削除できません',
            'unauthenticated' => '認証されていません。この操作を行うにはログインしてください',
            'id-required' => '顧客IDが必要です',
            'invalid-id-format' => '無効なID形式です。"/api/admin/customers/1"のようなIRI形式または数値IDが想定されています',
            'not-found' => '顧客が見つかりません',
            'phone-special-chars-not-allowed' => '携帯電話番号には数字のみ使用できます。特殊文字は使用できません',
            'email-already-taken' => 'このメールアドレスは既に別のアカウントで登録されています',
            'invalid-gender' => '性別の値「:gender」が無効です。使用できる値: :valid',
        ],

        'product-review' => [
            'id-required' => '商品レビューIDが必要です',
            'invalid-id-format' => '無効なID形式です。"/api/shop/reviews/1"のようなIRI形式または数値IDが想定されています',
            'not-found' => 'ID「:id」の商品レビューが見つかりません',
            'item-not-found' => 'アイテム「:id」が見つかりません。',
            'product-id-required' => '商品IDが必要です',
            'product-not-found' => '商品が見つかりません',
            'rating-invalid' => '評価は1から5の間である必要があります',
            'title-required' => 'レビューのタイトルが必要です',
            'comment-required' => 'レビューのコメントが必要です',
            'review-disabled' => '商品レビューは現在無効になっています',
            'guest-review-disabled' => 'ゲストによるレビューは許可されていません。レビューを投稿するにはログインしてください',
            'owner-required' => 'レビューを管理するにはログインしてください',
            'not-owned' => 'このレビューはお客様が書いたものではないため、変更または削除できません',
            'invalid-attachments' => '添付ファイルの形式が無効です。',
            'invalid-product-id-format' => '無効な商品ID形式です。"/api/shop/products/1"のようなIRI形式または数値IDが想定されています',
        ],

        'customer-review' => [
            'id-required' => '顧客レビューIDが必要です',
            'invalid-id-format' => '無効なID形式です。"/api/shop/customer-reviews/1"のようなIRI形式または数値IDが想定されています',
            'not-found' => 'ID「:id」の顧客レビューが見つかりません',
        ],

        'newsletter' => [
            'error-during-subscription' => '現在、購読を完了できません。後でもう一度お試しください',
        ],

        'contact-us' => [
            'success' => 'お問い合わせは正常に送信されました。折り返しご連絡いたします',
            'failed' => '現在、お問い合わせを送信できません。後でもう一度お試しください',
            'invalid-input' => 'お問い合わせフォームのデータが無効です',
        ],

        'customer-order' => [
            'id-required' => '顧客の注文IDが必要です',
            'invalid-id-format' => '無効なID形式です。"/api/shop/customer-orders/1"のようなIRI形式または数値IDが想定されています',
            'not-found' => 'ID「:id」の顧客注文が見つかりません',
        ],

        'return' => [
            'not-found' => '返品リクエストが見つかりません。',
            'disabled' => 'このストアでは返品が有効になっていません。',
            'invalid-input' => '返品の詳細がないか無効です。',
            'invalid-resolution-type' => '解決タイプは "return" または "cancel_items" である必要があります。',
            'agreement-required' => '返品ポリシーに同意する必要があります。',
            'invalid-order' => '選択された注文は無効です。',
            'invalid-item' => '選択されたアイテムは返品の対象外です。',
            'qty-exceeds' => 'リクエストされた数量が返品可能な数量を超えています。',
            'invalid-package-condition' => '梱包状態は "open" または "packed" である必要があります。',
            'invalid-image' => 'アップロードされたファイルの1つ以上がサポートされていないファイル形式です。',
            'invalid-custom-field' => '不明なカスタムフィールド「:id」です。',
            'custom-field-required' => 'フィールド「:field」は必須です。',
            'invalid-custom-field-value' => '「:field」の値は許可されたオプションのいずれでもありません。',
            'created' => '返品リクエストが正常に作成されました。',
            'canceled' => '返品リクエストが正常にキャンセルされました。',
            'already-canceled' => '返品リクエストは既にキャンセルされています。',
            'cancel-not-allowed' => 'この返品リクエストはキャンセルできません。',
            'close-not-allowed' => 'この返品リクエストはクローズできません。',
            'reopened' => '返品リクエストが正常に再開されました。',
            'reopen-not-allowed' => 'この返品リクエストは再開できません。',
            'closed' => '返品リクエストが正常にクローズされました。',
            'message-sent' => 'メッセージが正常に送信されました。',
            'attachment-not-found' => 'メッセージの添付ファイルが見つかりません。',
        ],

        'eu-withdrawal' => [
            'not-found' => '撤回申告が見つかりません。',
            'order-not-found' => '注文が見つからないか、撤回の対象外です。',
            'too-many-attempts' => '試行回数が多すぎます。:seconds 秒後にもう一度お試しください。',
            'created' => '撤回申告が正常に提出されました。',
        ],

        'customer-order-shipment' => [
            'id-required' => '出荷IDが必要です',
            'not-found' => 'ID「:id」の出荷が見つからないか、この顧客に属していません',
            'item-not-found' => 'ID「:id」の出荷アイテムが見つからないか、この顧客に属していません',
            'invalid-id-format' => '無効なID形式です。"/api/shop/customer-order-shipments/1"のようなIRI形式または数値IDが想定されています',
        ],

        'cancel-order' => [
            'order-id-required' => '注文をキャンセルするには注文IDが必要です',
            'not-found' => 'ID「:id」の注文が見つからないか、この顧客に属していません',
            'cancel-success' => '注文が正常にキャンセルされました',
            'cancel-failed' => '注文をキャンセルできません。既に処理済み、出荷済み、またはキャンセル済みの可能性があります',
        ],

        'reorder' => [
            'order-id-required' => '再注文するには注文IDが必要です',
            'not-found' => 'ID「:id」の注文が見つからないか、この顧客に属していません',
            'reorder-success' => '以前の注文から :count 件のアイテムがカートに追加されました',
            'no-items-added' => 'カートにアイテムを追加できませんでした。商品が在庫切れか利用できない可能性があります',
        ],

        'customer-downloadable-product' => [
            'id-required' => '顧客のダウンロード可能商品IDが必要です',
            'invalid-id-format' => '無効なID形式です。"/api/shop/customer-downloadable-products/1"のようなIRI形式または数値IDが想定されています',
            'not-found' => 'ID「:id」の顧客のダウンロード可能商品が見つかりません',

            'download' => [
                'unauthenticated' => '権限がありません: 顧客認証が必要です。',
                'not-found' => 'ダウンロード商品が見つかりません。',
                'pending' => 'ダウンロードは保留中です。注文の請求書が発行されるまでお待ちください。',
                'limit-exceeded' => 'ダウンロード回数の上限を超えました。',
                'no-downloads-remaining' => 'これ以上ダウンロードできません。',
                'file-not-found' => 'ファイルが見つかりません。',
                'link-not-found' => 'ダウンロードリンクが見つかりません。',
            ],
        ],

        'customer-invoice' => [
            'id-required' => '顧客の請求書IDが必要です',
            'invalid-id-format' => '無効なID形式です。"/api/shop/customer-invoices/1"のようなIRI形式または数値IDが想定されています',
            'not-found' => 'ID「:id」の顧客請求書が見つかりません',
        ],

        'auth' => [
            'no-token-provided' => '認証トークンが提供されていません。Authorizationヘッダーに "Bearer <token>" として、または input.token フィールドにトークンを指定してください',
            'invalid-or-expired-token' => 'トークンが無効か期限切れです',
            'request-not-found' => 'コンテキスト内にリクエストが見つかりません',
            'token-required' => 'Authentication token is required. Please provide the token either in the GraphQL mutation input field or in the Authorization header as "Bearer <token>"',
            'unknown-resource' => '不明なリソースです',
            'cannot-update-other-profile' => '権限がありません: 他の顧客のプロフィールは更新できません',
        ],

        'upload' => [
            'invalid-base64' => 'base64でエンコードされた画像データが無効です',
            'size-exceeds-limit' => '画像サイズは5MBを超えることはできません',
            'invalid-format' => '画像形式が無効です。データURIスキーム (data:image/jpeg;base64,...) を使用したbase64エンコード画像を指定してください',
            'failed' => '画像のアップロードに失敗しました',
        ],

        'attribute' => [
            'code-already-exists' => '属性コードはすでに存在します',
            'option-id-required' => '属性オプションを直接クエリする場合は "attributeId" パラメーターが必要です',
            'id-required' => '属性IDが必要です',
            'invalid-id-format' => '無効なID形式です。"/api/shop/attributes/1"のようなIRI形式または数値IDが想定されています',
            'not-found' => '属性が見つかりません',
        ],

        'attribute-option' => [
            'id-required' => '属性オプションIDが必要です',
            'invalid-id-format' => '無効なID形式です。"/api/shop/attribute-options/1"のようなIRI形式または数値IDが想定されています',
            'not-found' => '属性オプションが見つかりません',
        ],

        'channel' => [
            'id-required' => 'チャネルIDが必要です',
            'invalid-id-format' => '無効なID形式です。"/api/shop/channels/1"のようなIRI形式または数値IDが想定されています',
            'not-found' => 'チャネルが見つかりません',
        ],

        'country-state' => [
            'id-required' => '都道府県IDが必要です',
            'invalid-id-format' => '無効なID形式です。"/api/shop/country-states/1"のようなIRI形式または数値IDが想定されています',
            'not-found' => '都道府県が見つかりません',
            'country-id-required' => '国IDが必要です',
        ],

        'country' => [
            'id-required' => '国IDが必要です',
            'invalid-id-format' => '無効なID形式です。"/api/shop/countries/1"のようなIRI形式または数値IDが想定されています',
            'not-found' => '国が見つかりません',
        ],

        'login' => [
            'invalid-credentials' => 'メールアドレスまたはパスワードが無効です',
            'account-suspended' => 'お客様のアカウントは停止されています',
            'account-inactive' => 'アクティベーションは管理者の承認が必要です',
            'email-not-verified' => 'まずメールアカウントを確認してください。',
            'successful' => '正常にログインしました',
            'invalid-request' => '無効なログインリクエストです',
        ],

        'social-login' => [
            'signed-in' => 'サインインしました。',
            'token-required' => 'ソーシャルログイントークンが必要です。',
            'invalid-token' => 'ソーシャルログイントークンが無効または期限切れです。もう一度お試しください。',
            'google-not-configured' => 'このストアでは Google サインインが設定されていません。',
            'wrong-audience' => 'このトークンは別のアプリ用に発行されています。',
            'email-required' => 'プロバイダーがメールアドレスを共有しませんでした。メールで登録してください。',
            'account-inactive' => 'アクティベーションは管理者の承認が必要です',
            'provider-not-supported' => 'このソーシャルログインプロバイダーはサポートされていません。',
            'provider-disabled' => 'このソーシャルログインプロバイダーは有効になっていません。',
        ],

        'checkout' => [
            'invalid-input' => 'チェックアウト操作の入力データが無効です',
            'billing-address-required' => '請求先住所が必要です',
            'shipping-address-required' => '配送には配送先住所が必要です',
            'address-save-failed' => '住所を保存できませんでした',
            'address-saved' => '住所が正常に保存されました',
            'shipping-method-required' => '配送方法が必要です',
            'invalid-shipping-method' => '配送方法が無効か利用できません',
            'shipping-method-save-failed' => '配送方法を保存できませんでした',
            'shipping-method-saved' => '配送方法が正常に保存されました',
            'shipping-method-error' => '配送方法の保存中にエラーが発生しました',
            'payment-method-required' => '支払い方法が必要です',
            'invalid-payment-method' => '支払い方法が無効か利用できません',
            'payment-method-save-failed' => '支払い方法を保存できませんでした',
            'payment-method-saved' => '支払い方法が正常に保存されました',
            'payment-method-error' => '支払い方法の保存中にエラーが発生しました',
            'order-placed' => '注文が正常に完了しました',
            'order-redirect-required' => 'この支払い方法では、購入者がゲートウェイで支払いを完了する必要があります。購入者を redirectUrl に送ってください。注文はゲートウェイが支払いを確認した時点で作成されます。',
            'order-creation-failed' => '注文の作成に失敗しました: 注文IDがnullであるか、注文が保存されていません',
            'order-retrieval-failed' => '作成された注文を取得できませんでした',
            'order-creation-error' => '注文を作成できませんでした',
            'cart-empty' => 'カートは空です',
            'account-suspended' => 'お客様のアカウントは停止されています。サポートにお問い合わせください。',
            'account-inactive' => 'お客様のアカウントは無効です。サポートにお問い合わせください。',
            'minimum-order-not-met' => '最低注文金額は :amount です',
            'guest-checkout-not-allowed' => 'チェックアウトするにはログインしてください。このカートではゲストチェックアウトをご利用いただけません。',
            'email-required' => '注文の作成にはメールアドレスが必要です',
            'unknown-operation' => '不明なチェックアウト操作です',
        ],

        'customer-addresses' => [
            'token-required' => '顧客の住所を取得するにはトークンが必要です',
            'invalid-or-expired-token' => 'トークンが無効か期限切れです',
            'token-validation-failed' => 'トークンの検証に失敗しました',
        ],

        'product' => [
            'not-found' => '商品が見つかりません',
            'not-found-with-sku' => '該当する SKU の商品が見つかりません',
            'not-found-with-url-key' => '該当する URL キーの商品が見つかりません',
            'parameters-required' => '次のパラメーターのうち少なくとも1つを指定する必要があります: "sku"、"id"、"urlKey"',
            'type' => '商品タイプ',
            'attribute-family' => '属性ファミリー',
            'sku' => 'SKU',
            'name' => '名前',
            'description' => '説明',
            'short-description' => '短い説明',
            'status' => 'ステータス',
            'new' => '新着',
            'featured' => 'おすすめ',
            'price' => '価格',
            'special-price' => '特別価格',
            'weight' => '重量',
            'cost' => '原価',
            'length' => '長さ',
            'width' => '幅',
            'height' => '高さ',
            'color' => '色',
            'size' => 'サイズ',
            'brand' => 'ブランド',
            'super-attributes' => 'スーパー属性',
            'id-required' => '商品IDが必要です',
            'invalid-id-format' => '無効なID形式です。"/api/shop/products/1"のようなIRI形式または数値IDが想定されています',
        ],

        'downloadable-product' => [
            'download-link-not-found' => 'ダウンロードリンクが見つからないか、期限切れです',
            'purchased-link-not-found' => '購入済みリンクが見つかりません',
            'file-not-found' => 'ファイルが見つかりません',
            'download-successful' => 'ファイルはダウンロード可能です',
            'token-required' => 'ダウンロードトークンが必要です',
            'invalid-token' => 'ダウンロードトークンが無効か期限切れです',
            'token-expired' => 'ダウンロードトークンの有効期限が切れています。新しいものを生成してください',
            'access-denied' => 'アクセスが拒否されました：このファイルをダウンロードする権限がありません',
            'redirect-external-url' => '外部ダウンロードURLにリダイレクト',
            'file-error' => 'ダウンロードリクエストの処理中にエラーが発生しました',
            'unauthorized-access' => 'ダウンロードリソースへの不正なアクセス',
            'unauthenticated' => '権限がありません: 顧客認証が必要です',
            'link-id-required' => 'downloadableLinkPurchasedId が必要です',
            'link-not-found' => 'ダウンロードリンクが見つかりません',
            'not-owner' => '権限がありません: このダウンロードリンクはお客様のものではありません',
            'pending' => 'ダウンロードは保留中です。注文の請求書が発行されるまでお待ちください。',
            'limit-exceeded' => 'ダウンロード回数の上限を超えました',
            'no-downloads-left' => 'これ以上ダウンロードできません',
        ],

        'booking-slot' => [
            'id-required' => '予約商品のIDが必要です。',
            'date-required' => '日付が必要です。',
            'product-not-found' => '予約商品が見つかりません。',
            'invalid-type' => 'この予約タイプはサポートされていません。',
            'invalid-time-range' => '時間範囲が無効です。',
            'invalid-time-format' => '時間の形式が無効です。',
        ],

        'resolver' => [
            'no-resolve-info' => 'コンテキストで GraphQL ResolveInfo を利用できません。モデルクラスを特定できません。',
            'unknown-resource-class' => 'GraphQL の戻り値の型からリソースクラスを特定できません',
        ],

        'docs' => [
            'openapi-unavailable' => 'OpenAPI Factory をインスタンス化できませんでした',
        ],

        'locale' => [
            'id-required' => 'ロケールIDが必要です',
            'invalid-id-format' => '無効なID形式です。"/api/shop/locales/1"のようなIRI形式または数値IDが想定されています',
            'not-found' => 'ロケールが見つかりません',
        ],

        'category' => [
            'id-required' => 'カテゴリIDが必要です',
            'invalid-id-format' => '無効なID形式です。"/api/shop/categories/1"のようなIRI形式または数値IDが想定されています',
            'not-found' => 'カテゴリが見つかりません',
        ],

        'compare-item' => [
            'id-required' => '比較アイテムIDが必要です',
            'invalid-id-format' => '無効なID形式です。"/api/shop/compare-items/1"のようなIRI形式または数値IDが想定されています',
            'not-found' => '比較アイテムが見つかりません',
            'product-id-required' => '商品IDが必要です',
            'customer-id-required' => '顧客IDが必要です',
            'product-not-found' => '商品が見つかりません',
            'customer-not-found' => '顧客が見つかりません',
            'already-exists' => 'この商品は既に比較リストに含まれています',
            'delete-all-success' => 'すべての比較アイテムが正常に削除されました',
        ],

        'wishlist' => [
            'id-required' => 'ほしい物リストのアイテムIDが必要です',
            'invalid-id-format' => '無効なID形式です。"/api/shop/wishlists/1"のようなIRI形式または数値IDが想定されています',
            'not-found' => 'ほしい物リストのアイテムが見つかりません',
            'product-id-required' => '商品IDが必要です',
            'customer-id-required' => '顧客IDが必要です',
            'product-not-found' => '商品が見つかりません',
            'product-disabled' => 'この商品は現在無効になっています',
            'customer-not-found' => '顧客が見つかりません',
            'already-exists' => 'この商品は既にほしい物リストに含まれています',
            'added' => 'アイテムがほしい物リストに正常に追加されました',
            'removed' => 'アイテムがほしい物リストから正常に削除されました',
            'product-removed' => '商品が削除されました',
            'wishlist-item-id-required' => 'ほしい物リストのアイテムIDが必要です',
            'invalid-quantity' => '数量は 0 より大きくなければなりません',
            'move-to-cart-missing-options' => '商品に必須オプションが不足しています。手動で設定してください',
            'moved-to-cart-success' => 'アイテムがカートに正常に移動されました',
            'delete-all-success' => 'ほしい物リストのすべてのアイテムが正常に削除されました',
        ],

        'gdpr' => [
            'disabled' => 'GDPR データリクエストは無効になっています。管理画面の設定から GDPR を有効にしてください。',
            'unauthenticated' => 'GDPR データリクエストを管理するにはログインする必要があります。',
            'not-found' => 'GDPR データリクエストが見つかりません',
            'type-required' => 'リクエストタイプは必須です',
            'type-invalid' => 'リクエストタイプは "delete" または "update" のいずれかである必要があります',
            'message-required' => 'メッセージは必須です',
            'raised' => 'GDPR データリクエストが正常に送信されました。',
            'revoked' => 'GDPR データリクエストが正常に取り消されました。',
            'revoke-not-allowed' => 'この GDPR データリクエストはもう取り消すことができません。',
            'deleted' => 'GDPR データリクエストが正常に削除されました。',
        ],

        'section' => [
            'id-required' => 'セクションIDが必要です',
            'invalid-id-format' => '無効なID形式です。"/api/shop/sections/1"のようなIRI形式または数値IDが想定されています',
            'not-found' => 'セクションが見つかりません',
        ],

        'install' => [
            'starting' => 'API Platform のインストールを開始しています...',
            'completed-success' => '✓ API Platform のインストールが正常に完了しました!',
            'failed' => '✗ インストールに失敗しました: ',

            'provider-already-registered' => 'ℹ Service Provider は既に登録されています',
            'provider-not-registered' => '⚠ Service Provider を自動登録できませんでした。:provider を bootstrap/providers.php に手動で追加してください。',
            'provider-registered' => '✓ Service Provider を bootstrap/providers.php に登録しました',
            'provider-file-not-found' => 'ファイルが見つかりません: :file',
            'provider-permission-denied' => '権限がありません: :file に書き込めません。ファイルの権限を確認してください。',

            'config-already-exists' => 'ℹ 設定ファイルは既に存在します',
            'config-published' => '✓ API Platform の設定を config/ に公開しました',
            'config-source-not-found' => 'ソース設定ファイルが見つかりません: :file',
            'config-permission-denied' => '権限がありません: :directory に書き込めません。ディレクトリの権限を確認してください。',

            'translatable-not-found' => 'ℹ TranslatableModel が見つかりません - スキップします',
            'translatable-already-abstract' => 'ℹ TranslatableModel は既に abstract です',
            'translatable-made-abstract' => '✓ TranslatableModel を abstract にしました',
            'translatable-permission-denied' => '権限がありません: :file に書き込めません。ファイルの権限を確認してください。',

            'providers-already-registered' => 'ℹ API Platform のプロバイダーは既に登録されています',
            'providers-registered' => '✓ API Platform のプロバイダーを bootstrap/app.php に登録しました',
            'providers-file-not-found' => 'ファイルが見つかりません: :file',
            'providers-permission-denied' => '権限がありません: :file に書き込めません。ファイルの権限を確認してください。',
            'providers-not-found' => 'bootstrap/app.php に Application の作成処理が見つかりませんでした',

            'assets-published' => '✓ パッケージのアセットを公開しました',
            'running-migrations' => 'データベースマイグレーションを実行しています...',
            'migrations-completed' => '✓ データベースマイグレーションが完了しました',
            'clearing-caches' => 'キャッシュをクリアして最適化しています...',
            'caches-optimized' => '✓ キャッシュをクリアして最適化しました',
            'generating-api-key' => 'API キーを生成しています...',
            'api-key-generated' => '✓ API キーが正常に生成されました',
            'api-key-already-exists' => 'ℹ デフォルトストアの API キーは既に存在します - 生成をスキップします',

            'migrations-error' => 'データベースマイグレーションに失敗しました。',
            'migrations-error-running' => 'データベースマイグレーションの実行中にエラーが発生しました: :error',
            'cache-clear-error' => 'キャッシュのクリアに失敗しました。',
            'cache-optimize-error' => '最適化に失敗しました。',
            'cache-error' => 'キャッシュのクリアと最適化中にエラーが発生しました: :error',
            'api-key-generation-error' => 'API キーの生成に失敗しました。',
            'api-key-error' => 'API キーの生成中にエラーが発生しました: :error',

            'key-management-required' => ':action アクションには --key が必要です',
            'key-rotation-error' => 'キーのローテーション中にエラーが発生しました: :error',
            'key-deactivation-error' => 'キーの無効化中にエラーが発生しました: :error',
            'key-not-found' => 'API キーが見つかりません: :identifier',

            'maintenance-starting' => '🔄 API キーのメンテナンスを開始しています...',
            'maintenance-complete' => '✅ API キーのメンテナンスが完了しました',
            'cleanup-expired-keys' => '🧹 期限切れのキーをクリーンアップしています...',
            'cleanup-expired-none' => '   ℹ️ クリーンアップする期限切れのキーはありません',
            'invalidate-deprecated' => '⚠️ 非推奨のキーを無効化しています...',
            'invalidate-deprecated-none' => '   ℹ️ 無効化する非推奨のキーはありません',
            'notify-expiring' => '📧 有効期限の通知を送信しています...',
            'notify-expiring-none' => '   ℹ️ 通知が必要なキーはありません',

            'key-rotated-success' => '✅ キーが正常にローテーションされました!',
            'key-deactivated-success' => '✅ キーが正常に無効化されました!',
            'deactivation-cancelled' => '無効化はキャンセルされました。',
            'cleanup-cancelled' => 'クリーンアップはキャンセルされました。',

            'key-status-title' => 'キーのステータス: :name',
            'key-status-active' => '有効: :status',
            'key-status-usable' => '使用可能: :status',
            'key-status-expired' => '期限切れ: :status',
            'key-status-deprecated' => '非推奨: :status',
            'key-status-expires-at' => '有効期限: :expires',
            'key-status-days-until-expiry' => '有効期限までの日数: :days',
            'key-status-last-used' => '最終使用日: :date',
            'key-status-rotated-from' => 'ローテーション元: :key',
            'key-status-rotated-keys' => 'このキーからローテーションされたキー: :count',

            'policy-compliance-summary' => 'API キーローテーションポリシー準拠の概要',
            'available-actions' => '利用可能なアクション: rotate, deactivate, cleanup, status, expiring, unused, summary',

            'asset-linked-success' => '✓ API Platform のアセットが正常にリンクされました',
            'symlink-create-failed' => 'シンボリックリンクを作成できませんでした。代わりにアセットをコピーしています...',
            'asset-copy-warning' => '警告: API Platform のアセットをリンクまたはコピーできませんでした。手動での設定が必要な場合があります。',
            'asset-copied-success' => '✓ API Platform のアセットが正常にコピーされました',

            'key-name-required' => 'ストアフロントキーの名前は空にできません。',
            'key-generated-success' => 'ストアフロントキーが正常に生成されました!',
            'key-details' => 'キーの詳細:',
            'key-secure-warning' => 'このキーは安全に保管してください! X-STOREFRONT-KEY ヘッダーで使用されます。',
            'key-share-warning' => 'このキーを公開したり、バージョン管理にコミットしたりしないでください。',

            'key-already-exists' => '名前が \':name\' のストアフロントキーは既に存在します。',
            'cleanup-success' => '✅ 期限切れのキーを :count 件クリーンアップしました',
            'confirm-cleanup' => '期限切れのキーをすべてソフト削除します。続行しますか?',
            'confirm-deactivate' => 'キー :name を無効化してもよろしいですか?',

            'old-key' => '旧キー: :name',
            'old-key-id' => '旧キー ID: :id',
            'deprecation-date' => '非推奨日: :date',
            'new-key' => '新しいキー: :name',
            'new-key-id' => '新しいキー ID: :id',
            'new-key-value' => '新しいキーの値: :key',
            'expires-at' => '有効期限: :date',
            'invalid-action' => '無効なアクションです: :action',
            'key-active' => '有効: :status',
            'key-usable' => '使用可能: :status',
            'key-expired' => '期限切れ: :status',
            'key-deprecated' => '非推奨: :status',
            'key-field-id' => 'ID',
            'key-field-name' => '名前',
            'key-field-key' => 'キー',
            'key-field-rate-limit' => 'レート制限',
            'key-field-status' => 'ステータス',
            'key-days' => ' 日',
            'key-never' => 'なし',
            'key-requests-minute' => ' リクエスト/分',
            'key-unlimited' => '無制限',
            'rate-limit-exceeded' => '要求されたレート制限が許可されている最大値 :max を超えています。:max リクエスト/分に設定します。',

            'vendor-path-not-found' => 'API Platform の vendor パスが見つかりません: :path',
            'assets-already-linked' => 'API Platform のアセットは既にリンクされています: :path',
            'no-keys-expiring' => '今後 :days 日以内に期限切れになるキーはありません',
            'keys-expiring-title' => '今後 :days 日以内に期限切れになるキー:',
            'key-display-format' => '• :name (ID: :id)',
            'key-expires-display' => '  有効期限: :date (残り :days 日)',
            'no-unused-keys' => '未使用のキーは見つかりません (> :days 日)',
            'unused-keys-title' => '未使用のキー (> :days 日):',
            'key-last-used-display' => '  最終使用日: :date',
            'total-keys' => 'キーの総数: :count',
            'valid-keys' => '有効なキー: :count',
            'expired-keys' => '期限切れのキー: :count',
            'deprecated-keys' => '非推奨のキー: :count',
            'keys-expiring-soon' => 'まもなく期限切れになるキー (7 日): :count',
            'unused-keys-summary' => '未使用のキー (90 日): :count',
            'recently-rotated' => '最近ローテーションされたキー (30 日): :count',
            'cleanup-success-message' => '   ✅ 期限切れのキーを :count 件クリーンアップしました',
            'invalidate-success-message' => '   ✅ 非推奨のキーを :count 件無効化しました',
            'notify-success-message' => '   ✅ 有効期限の通知を :count 件送信しました',
            'notify-failed-message' => '   ⚠️ :key に関する通知に失敗しました: :error',
            'publish-assets-warning' => '警告: パッケージのアセットを公開できませんでした。:error',
            'env-config-saved' => '✓ API Platform の環境設定が正常に保存されました',
            'api-endpoints' => 'API エンドポイント:',
            'api-documentation' => 'API ドキュメント: :url',
            'api-landing-page' => 'API ランディングページ: :url',
            'graphql-playground' => 'GraphQL Playground: :url',
            'rest-api-storefront' => 'REST API ストアフロント Swagger: :url',
            'rest-api-admin' => 'REST API 管理 Swagger: :url',
            'key-rotation-invalid-key' => '無効または期限切れのキーはローテーションできません',
            'env-file-not-found' => '.envファイルが見つかりません',
            'env-permission-denied' => '権限が拒否されました: .envファイルに書き込めません。ファイルの権限を確認してください。',
        ],

        'attribute-translation' => [
            'id-required' => '属性翻訳IDが必要です',
            'invalid-id-format' => '無効なID形式です。"/api/shop/attribute-translations/1"のようなIRI形式または数値IDが想定されています',
            'not-found' => '属性翻訳が見つかりません',
        ],

        'currency' => [
            'id-required' => '通貨IDが必要です',
            'invalid-id-format' => '無効なID形式です。"/api/shop/currencies/1"のようなIRI形式または数値IDが想定されています',
            'not-found' => '通貨が見つかりません',
        ],

        'customer-address' => [
            'id-required' => '顧客住所IDが必要です',
            'invalid-id-format' => '無効なID形式です。"/api/shop/customer-addresses/1"のようなIRI形式または数値IDが想定されています',
        ],

        'customer-order-shipment-item' => [
            'id-required' => '出荷アイテムIDが必要です',
            'invalid-id-format' => '無効なID形式です。"/api/shop/customer-order-shipment-items/1"のようなIRI形式または数値IDが想定されています',
            'not-found' => '出荷アイテムが見つかりません',
        ],

        'page' => [
            'id-required' => 'ページIDが必要です',
            'invalid-id-format' => '無効なID形式です。"/api/shop/pages/1"のようなIRI形式または数値IDが想定されています',
            'not-found' => 'ページが見つかりません',
        ],

        'page-translation' => [
            'id-required' => 'ページ翻訳IDが必要です',
            'invalid-id-format' => '無効なID形式です。"/api/shop/page-translations/1"のようなIRI形式または数値IDが想定されています',
            'not-found' => 'ページ翻訳が見つかりません',
        ],

        'product-bundle-option' => [
            'id-required' => 'バンドルオプションIDが必要です',
            'invalid-id-format' => '無効なID形式です。"/api/shop/product-bundle-options/1"のようなIRI形式または数値IDが想定されています',
            'not-found' => 'バンドルオプションが見つかりません',
        ],

        'product-bundle-option-product' => [
            'id-required' => 'バンドルオプション商品IDが必要です',
            'invalid-id-format' => '無効なID形式です。"/api/shop/product-bundle-option-products/1"のようなIRI形式または数値IDが想定されています',
            'not-found' => 'バンドルオプション商品が見つかりません',
        ],

        'product-downloadable-link' => [
            'id-required' => 'ダウンロードリンクIDが必要です',
            'invalid-id-format' => '無効なID形式です。"/api/shop/product-downloadable-links/1"のようなIRI形式または数値IDが想定されています',
            'not-found' => 'ダウンロードリンクが見つかりません',
        ],

        'product-downloadable-sample' => [
            'id-required' => 'ダウンロードサンプルIDが必要です',
            'invalid-id-format' => '無効なID形式です。"/api/shop/product-downloadable-samples/1"のようなIRI形式または数値IDが想定されています',
            'not-found' => 'ダウンロードサンプルが見つかりません',
        ],

        'product-grouped-product' => [
            'id-required' => 'グループ商品IDが必要です',
            'invalid-id-format' => '無効なID形式です。"/api/shop/product-grouped-products/1"のようなIRI形式または数値IDが想定されています',
            'not-found' => 'グループ商品が見つかりません',
        ],

        'product-images' => [
            'id-required' => '商品画像IDが必要です',
            'invalid-id-format' => '無効なID形式です。"/api/shop/product-images/1"のようなIRI形式または数値IDが想定されています',
            'not-found' => '商品画像が見つかりません',
        ],

        'product-videos' => [
            'id-required' => '商品動画IDが必要です',
            'invalid-id-format' => '無効なID形式です。"/api/shop/product-videos/1"のようなIRI形式または数値IDが想定されています',
            'not-found' => '商品動画が見つかりません',
        ],

        'theme-customization' => [
            'id-required' => 'テーマカスタマイズIDが必要です',
            'invalid-id-format' => '無効なID形式です。"/api/shop/theme-customizations/1"のようなIRI形式または数値IDが想定されています',
            'not-found' => 'テーマカスタマイズが見つかりません',
        ],

        'unknown-resource' => [
            'id-required' => 'リソースIDが必要です',
            'not-found' => 'リソースが見つかりません',
        ],
    ],

    'admin' => [
        // login / logout / forgot-password / profile-update keys removed in the
        // 2026-05-27 auth refactor — admin clients now authenticate via
        // pre-issued integration tokens (Bearer header, AdminApiGuard).
        'profile' => [
            'unauthenticated' => '認証されていません。',
        ],

        'dashboard' => [
            'invalid-type' => '不明なダッシュボード統計タイプです。有効なタイプ: over-all, today, stock-threshold-products, total-sales, top-selling-products, top-customers。',
        ],

        'rma' => [
            'not-found' => '返品リクエストが見つかりません。',
            'invalid-input' => '返品の詳細がないか無効です。',
            'invalid-order' => '選択された注文は無効です。',
            'invalid-item' => '選択されたアイテムは返品の対象外です。',
            'invalid-resolution-type' => '解決タイプは "return" または "cancel_items" である必要があります。',
            'qty-exceeds' => 'リクエストされた数量が返品可能な数量を超えています。',
            'created' => '返品リクエストが正常に作成されました。',
            'status-updated' => '返品ステータスが正常に更新されました。',
            'invalid-status' => '返品ステータスが無効です。',
            'reopened' => '返品リクエストが正常に再開されました。',
            'reopen-not-allowed' => 'この返品リクエストは再開できません。',
            'refund-failed' => 'この返品の返金を作成できませんでした。',
            'message-sent' => 'メッセージが正常に送信されました。',
            'no-permission' => 'この操作を実行する権限がありません。',
            'reason-not-found' => '返品理由が見つかりません。',
            'reason-deleted' => '返品理由が正常に削除されました。',
            'status-not-found' => '返品ステータスが見つかりません。',
            'status-deleted' => '返品ステータスが正常に削除されました。',
            'status-default-undeletable' => 'デフォルトの返品ステータスは削除できません。',
            'rule-not-found' => '返品ルールが見つかりません。',
            'rule-deleted' => '返品ルールが正常に削除されました。',
            'custom-field-not-found' => '返品カスタムフィールドが見つかりません。',
            'custom-field-deleted' => '返品カスタムフィールドが正常に削除されました。',
            'custom-field-options-required' => 'このフィールドタイプには少なくとも1つのオプションが必要です。',
            'mass-indices-required' => '少なくとも1つの ID が必要です。',
            'mass-delete-success' => '選択したアイテムを削除しました。',
            'mass-update-success' => '選択したアイテムを更新しました。',
            'mass-value-invalid' => 'ステータスの値は 0 または 1 である必要があります。',
            'image-not-found' => '返品画像が見つかりません。',
            'attachment-not-found' => 'メッセージの添付ファイルが見つかりません。',
        ],

        'eu-withdrawal' => [
            'not-found' => '撤回申告が見つかりません。',
            'no-permission' => 'EU 撤回を管理する権限がありません。',
            'declined' => '撤回を却下済みにしました。',
            'refunded' => '撤回を返金済みにしました。',
            'confirmation-resent' => '確認メールが正常に再送信されました。',
            'confirmation-failed' => '確認メールを送信できませんでした。',
        ],

        'reporting' => [
            'invalid-entity' => '不明なレポートエンティティです。',
            'invalid-type' => 'このエンティティに対する不明なレポート統計タイプです。',
            'export-format-unsupported' => 'csv、xls、xlsx のエクスポート形式のみサポートされています。',
        ],

        'configuration' => [
            'unauthenticated' => '認証されていません。',
            'no-permission' => '設定を更新する権限がありません。',
            'slug-required' => 'クエリパラメーター `slug` は必須です。',
            'slug-not-found' => '指定された slug に登録されている設定セクションはありません。',
            'values-required' => '`values` に少なくとも1つの値が必要です。',
            'scope-escape' => 'キー `:key` はリクエストされた slug のサブツリーの外にあります。',
            'unknown-field' => 'キー `:key` は登録されている設定フィールドではありません。',
            'validation-failed' => '1つ以上の設定値が検証に失敗しました。',
            'file-upload-rest-only' => 'フィールド `:field` のバイナリファイルアップロードは、GraphQL ではなく REST の multipart エンドポイントでのみサポートされています。',
            'custom-view-readonly' => 'フィールド `:field` は Blade でレンダリングされるカスタムフィールドのため、API から更新できません。',
            'update-success' => '設定が正常に更新されました。',
        ],

        'customer' => [
            'not-found' => '顧客が見つかりません。',
            'no-permission' => 'この操作を実行する権限がありません。',
            'has-active-orders' => '顧客に保留中または処理中の注文があるため、削除できません。',
            'deleted' => '顧客が正常に削除されました。',
            'mass-delete-indices-required' => '少なくとも1つの顧客 ID が必要です。',
            'mass-delete-success' => '顧客を削除しました。',
            'mass-update-indices-required' => '少なくとも1つの顧客 ID が必要です。',
            'mass-update-value-invalid' => 'ステータスの値は 0 または 1 である必要があります。',
            'mass-update-success' => '顧客のステータスを更新しました。',

            'address' => [
                'not-found' => '住所が見つかりません。',
                'not-owned' => 'この住所は指定された顧客に属していません。',
                'customer-id-required' => '住所の所有者を特定するには customerId が必要です。',
                'deleted' => '住所が正常に削除されました。',
            ],

            'note' => [
                'empty' => 'メモのテキストは必須です。',
                'created' => 'メモが正常に追加されました。',
            ],

            'impersonate' => [
                'success' => 'なりすましトークンが発行されました。',
            ],

            'review' => [
                'not-found' => 'レビューが見つかりません。',
                'no-permission' => 'この操作を実行する権限がありません。',
                'invalid-status' => 'ステータスは pending、approved、disapproved のいずれかである必要があります。',
                'updated' => 'レビューが正常に更新されました。',
                'deleted' => 'レビューが正常に削除されました。',
                'mass-delete-indices-required' => '少なくとも1つのレビュー ID が必要です。',
                'mass-delete-success' => 'レビューを削除しました。',
                'mass-update-indices-required' => '少なくとも1つのレビュー ID が必要です。',
                'mass-update-value-invalid' => 'ステータスの値は pending、approved、disapproved のいずれかである必要があります。',
                'mass-update-success' => 'レビューのステータスを更新しました。',
            ],

            'group' => [
                'not-found' => '顧客グループが見つかりません。',
                'deleted' => '顧客グループが正常に削除されました。',
                'is-system' => 'システムの顧客グループは削除できません。',
                'has-customers' => '顧客グループに顧客が紐付いているため、削除できません。',
                'system-code-immutable' => 'システムの顧客グループのコードは変更できません。',
                'system-flag-immutable' => 'システムの顧客グループの is_user_defined フラグは変更できません。',
                'mass-delete-indices-required' => '少なくとも1つの顧客グループ ID が必要です。',
                'mass-delete-success' => '顧客グループを削除しました。',
            ],

            'gdpr' => [
                'not-found' => 'GDPR リクエストが見つかりません。',
                'no-permission' => 'この操作を実行する権限がありません。',
                'deleted' => 'GDPR リクエストが正常に削除されました。',
                'invalid-status' => 'ステータスは pending、processing、declined、approved、revoked のいずれかである必要があります。',
                'no-changes' => 'ステータスまたはメッセージの少なくとも一方が必要です。',
                'already-processed' => 'この GDPR リクエストは既に処理済みのため、再実行できません。',

                'process' => [
                    'approved' => 'GDPR リクエストを承認しました。',
                    'deleted' => 'GDPR リクエストを承認し、顧客を削除しました。',
                ],
            ],
        ],

        'order' => [
            'not-found' => '注文が見つかりません。',

            'reorder' => [
                'success' => '再注文に成功しました。新しい下書きカートが作成されました。',
                'cannot-reorder' => 'この注文は再注文できません (ゲスト注文であるか、1つ以上のアイテムが販売できなくなっています)。',
                'failed' => '再注文に失敗しました。もう一度お試しください。',
                'guest-not-supported' => 'ゲスト注文の再注文はサポートされていません。',
                'items-not-saleable' => 'この注文の1つ以上のアイテムは購入できなくなっています。',
                'no-permission' => '注文を作成する権限がありません。',
                'disabled-in-settings' => '管理者による再注文はストア設定で無効になっています。',
            ],

            'actions' => [
                'cancel' => [
                    'closed' => 'クローズされた注文はキャンセルできません。',
                    'fraud' => '不正な注文はキャンセルできません。',
                    'nothing-to-cancel' => 'この注文にはキャンセルできるものがありません。',
                    'already-processed' => 'この注文は既に請求書の発行または出荷が行われているため、キャンセルできません。代わりに返金を行うことができます。',
                    'no-permission' => '注文をキャンセルする権限がありません。',
                    'success' => '注文が正常にキャンセルされました。',
                    'failed' => '注文をキャンセルできませんでした。',
                ],

                'comment' => [
                    'empty' => 'コメントは必須です。',
                    'success' => '注文にコメントを追加しました。',
                    'failed' => 'コメントを追加できませんでした。',
                ],

                'invoice' => [
                    'closed' => 'クローズされた注文には請求書を発行できません。',
                    'fraud' => '不正な注文には請求書を発行できません。',
                    'nothing-to-invoice' => 'この注文には請求書を発行できるものがありません。',
                    'already-invoiced' => 'この注文には既に請求書が発行されています。',
                    'paypal-standard-blocked' => 'PayPal Standard で支払われた注文には請求書を作成できません。',
                    'no-permission' => '請求書を作成する権限がありません。',
                    'items-required' => '数量が正の値のアイテムが少なくとも1つ必要です。',
                    'qty-exceeds' => 'SKU :sku のリクエスト数量 (:requested) が利用可能な数量 (:available) を超えています。',
                    'not-found' => '請求書が見つかりません。',
                    'success' => '請求書が正常に作成されました。',
                    'failed' => '請求書を作成できませんでした。',
                    'pdf-failed' => '請求書の PDF を生成できませんでした。',
                    'email-invalid' => '有効な受信者のメールアドレスが必要です。',
                    'duplicate-sent' => '請求書のメールを :email に送信しました。',
                    'mass-status-indices-required' => '少なくとも1つの請求書 ID が必要です。',
                    'mass-status-value-invalid' => 'ステータスは pending、paid、overdue のいずれかである必要があります。',
                    'mass-status-success' => '請求書のステータスが正常に更新されました。',
                ],

                'shipment' => [
                    'closed' => 'クローズされた注文は出荷できません。',
                    'fraud' => '不正な注文は出荷できません。',
                    'nothing-to-ship' => 'この注文には出荷できるものがありません。',
                    'no-permission' => '注文を出荷する権限がありません。',
                    'source-required' => '在庫ソースは必須です。',
                    'items-required' => '数量が正の値のアイテムが少なくとも1つ必要です。',
                    'qty-exceeds' => 'SKU :sku のリクエスト数量 (:requested) が利用可能な数量 (:available) を超えています。',
                    'inventory-insufficient' => '選択された在庫ソースでは SKU :sku の在庫が不足しています。',
                    'not-found' => '出荷が見つかりません。',
                    'success' => '出荷が正常に作成されました。',
                    'failed' => '出荷を作成できませんでした。',
                ],

                'refund' => [
                    'closed' => 'クローズされた注文は返金できません。',
                    'fraud' => '不正な注文は返金できません。',
                    'nothing-to-refund' => 'この注文には返金できるものが残っていません。',
                    'no-permission' => '返金を作成する権限がありません。',
                    'qty-exceeds' => 'SKU :sku のリクエスト数量 (:requested) が利用可能な数量 (:available) を超えています。',
                    'amount-zero' => '計算された返金額が 0 です。数量、送料、または調整額を見直してください。',
                    'amount-exceeds-max' => '返金額 (:amount) が返金可能な最大額 (:max) を超えています。',
                    'not-found' => '返金が見つかりません。',
                    'success' => '返金が正常に作成されました。',
                    'failed' => '返金を作成できませんでした。',
                ],
            ],
        ],

        'appearance' => [
            'no-permission' => '外観を管理する権限がありません。',

            'theme' => [
                'not-found' => 'テーマが見つかりません。',
                'not-installed' => 'このテーマはインストールされていません。',
                'channel-ids-required' => '少なくとも1つのチャネルが必要です。',
                'unknown-channel' => '1つ以上のチャネルが存在しません。',
                'activated' => 'テーマが正常に有効化されました。',
            ],

            'section' => [
                'not-found' => 'セクションが見つかりません。',
                'invalid-payload' => 'サポートされていないセクションのペイロードです。',
                'footer-links-exists' => 'このチャネルには既にフッターリンクのセクションがあります。',
                'options-required' => 'オプションは必須です。',
                'status-required' => 'ステータスは必須です。',
                'section-ids-required' => '少なくとも1つのセクションが必要です。',
                'reorder-single-scope' => 'セクションの並べ替えは、一度に1つのテーマとチャネル内でのみ行えます。',
                'reorder-incomplete' => '並べ替えには、テーマとチャネルのすべてのセクションを表示する順序で指定する必要があります。',
                'unknown-section' => '1つ以上のセクションが存在しません。',
                'file-required' => 'ファイルが必要です。',
                'created' => 'セクションが正常に作成されました。',
                'updated' => 'セクションが正常に更新されました。',
                'deleted' => 'セクションが正常に削除されました。',
                'draft-saved' => 'セクションの変更が正常にステージングされました。',
                'status-staged' => 'セクションのステータスが正常にステージングされました。',
                'order-staged' => 'セクションの順序が正常にステージングされました。',
                'duplicated' => 'セクションが正常にコピーされました。',
                'published' => 'セクションの変更が正常に公開されました。',
                'discarded' => 'セクションの変更が正常に破棄されました。',
                'media-uploaded' => 'メディアが正常にアップロードされました。',
            ],
        ],

        'sales' => [
            'no-permission' => 'この販売リソースを表示する権限がありません。',

            'invoice' => [
                'not-found' => '請求書が見つかりません。',
                'no-permission' => '請求書を表示する権限がありません。',
            ],

            'shipment' => [
                'not-found' => '出荷が見つかりません。',
                'no-permission' => '出荷を表示する権限がありません。',
            ],

            'refund' => [
                'not-found' => '返金が見つかりません。',
                'no-permission' => '返金を表示する権限がありません。',
                'export-format-unsupported' => 'csv、xls、xlsx のエクスポートのみサポートされています。',
            ],

            'transaction' => [
                'not-found' => '取引が見つかりません。',
                'no-permission' => '取引を表示する権限がありません。',

                'create' => [
                    'invoice-required' => 'invoiceId フィールドは必須です。',
                    'payment-method-required' => 'paymentMethod フィールドは必須です。',
                    'amount-invalid' => 'amount フィールドは必須で、数値である必要があります。',
                    'invoice-missing' => '指定された請求書が見つかりませんでした。',
                    'already-paid' => 'この請求書は既に全額支払われています。',
                    'amount-exceeds' => '支払額が請求書の総額を超えています。',
                    'amount-zero' => '支払額は 0 より大きくなければなりません。',
                ],
            ],

            'booking' => [
                'not-found' => '予約が見つかりません。',
                'no-permission' => '予約を表示する権限がありません。',
            ],

            'export' => [
                'format-unsupported' => 'csv、xls、xlsx のエクスポートのみサポートされています。',
            ],
        ],

        'cart' => [
            'not-found' => 'カートが見つかりません。',
            'not-draft' => 'このカートは管理 API から変更できません。',
            'product-required' => 'productId は必須です。',
            'product-not-found' => '商品が見つかりません。',
            'booking-unsupported' => '予約商品は管理者の下書き注文に追加できません。予約商品の購入は顧客のストアフロントから行う必要があります。',
            'product-not-saleable' => 'この商品は販売できない状態 (在庫切れまたは無効) のため、注文に追加できません。',
            'qty-required' => 'qty は必須で、itemId => 数量 の空でないオブジェクトである必要があります。',
            'cart-item-required' => 'cartItemId は必須です。',
            'item-added' => 'アイテムをカートに追加しました。',
            'item-add-failed' => 'アイテムをカートに追加できませんでした。',
            'item-updated' => 'カートアイテムを更新しました。',
            'item-update-failed' => 'カートアイテムを更新できませんでした。',
            'item-removed' => 'アイテムをカートから削除しました。',
            'item-remove-failed' => 'カートアイテムを削除できませんでした。',
            'address-saved' => '住所を保存しました。',
            'address-required' => '請求先住所が必要です。',
            'address-incomplete' => '住所を保存するには :field が必要です。',
            'coupon-code-required' => 'クーポンコードが必要です。',
            'coupon-applied' => 'クーポンを適用しました。',
            'coupon-already-applied' => 'クーポンは既にこのカートに適用されています。',
            'coupon-not-found' => 'クーポンが見つからないか、無効です。',
            'coupon-removed' => 'クーポンを削除しました。',
            'coupon-error' => 'クーポンの適用中に問題が発生しました。',
            'unknown-error' => '問題が発生しました。',
            'draft-created' => '下書きカートを作成しました。',
            'draft-failed' => '下書きカートを作成できませんでした。',
            'addresses-required-for-shipping' => '配送方法を選択する前に住所を保存する必要があります。',
            'shipping-required-for-payment' => '支払い方法の前に配送方法を選択する必要があります。',
            'shipping-method-required' => '配送方法は必須です。',
            'shipping-method-saved' => '配送方法を保存しました。',
            'shipping-method-failed' => '配送方法を保存できませんでした。',
            'payment-method-required' => '支払い方法は必須です。',
            'payment-method-saved' => '支払い方法を保存しました。',
            'payment-method-failed' => '支払い方法を保存できませんでした。',

            'place-order' => [
                'success' => '注文が正常に完了しました。',
                'failed' => '注文できませんでした。',
                'error' => '注文できませんでした: カートにエラーがあります。',
                'minimum-order-error' => '最低注文金額は :amount です。注文する前にアイテムを追加してください。',
                'empty-cart' => 'カートが空です — 注文する前にアイテムを追加してください。',
                'addresses-required' => '注文する前に請求先住所と配送先住所を保存する必要があります。',
                'shipping-required' => '注文する前に配送方法を選択する必要があります。',
                'payment-required' => '注文する前に支払い方法を選択する必要があります。',
                'payment-method-unsupported' => '管理者による注文では cashondelivery と moneytransfer のみサポートされています。',
            ],
        ],

        'product' => [
            'not-found' => '商品が見つかりません。',

            'mass-delete-success' => '商品が正常に削除されました。',
            'mass-update-status-success' => '商品のステータスが正常に更新されました。',
            'indices-required' => 'indices フィールドは必須で、空でない配列である必要があります。',
            'indices-invalid' => 'indices フィールドは整数の商品 ID の配列である必要があります。',
            'value-invalid' => 'value フィールドは必須で、0 または 1 である必要があります。',
            'mass-delete-failed' => '1つ以上の商品を削除できませんでした。',
            'no-permission' => '商品を管理する権限がありません。',

            'copy-success' => '商品が正常にコピーされました。',
            'copy-failed' => '商品のコピーに失敗しました。',
            'copy-variant-not-supported' => 'コンフィギュラブル商品のバリアントはコピーできません。代わりに親のコンフィギュラブル商品をコピーしてください。',

            'create' => [
                'created' => '商品が正常に作成されました。',
                'create-failed' => '商品の作成に失敗しました。',
                'sku-required' => 'sku フィールドは必須です。',
                'sku-unique' => 'この sku は既に使用されています。',
                'sku-invalid' => 'sku には英字、数字、ハイフンのみ使用できます (例: "sp-001")。',
                'attribute-family-required' => 'attribute_family_id フィールドは必須です。',
                'attribute-family-not-found' => '選択された属性ファミリーは存在しません。',
                'type-required' => 'type フィールドは必須です。',
                'type-not-yet-supported' => '商品タイプ「:type」はこの API ではサポートされていません。使用できるタイプ: simple, virtual, downloadable, grouped, bundle, configurable, booking。',
                'super-attributes-required' => 'type=configurable の場合、super_attributes フィールドは必須です。属性コード (または id) から option_ids へのマップを指定してください。例: { "color": [1,2], "size": [4,5] }。',
                'super-attributes-invalid' => 'super_attributes フィールドは、属性コード (または id) から空でない option_ids のリストへの、空でないマップである必要があります。',
                'no-permission' => '商品を作成する権限がありません。',
            ],

            'update' => [
                'updated' => '商品が正常に更新されました。',
                'update-failed' => '商品の更新に失敗しました。',
                'id-required' => '商品 ID が必要です。',
                'url-key-required' => 'url_key フィールドは空にできません。',
                'url-key-unique' => 'この url_key は既に別の商品で使用されています。',
                'special-price-invalid' => 'special_price は price より小さい数値である必要があります。',
                'special-price-date-range-invalid' => 'special_price_to は special_price_from と同じ日かそれ以降である必要があります。',
                'boolean-field-invalid' => ':field フィールドは 0 または 1 である必要があります。',
                'categories-invalid' => 'categories フィールドはカテゴリ ID の配列である必要があります。',
                'channels-invalid' => 'channels フィールドはチャネル ID の配列である必要があります。',
                'super-attributes-cannot-change' => 'super_attributes は商品の作成後に変更できません。',
                'sub-resource-stripped-images' => '画像は POST /api/admin/catalog/products/{id}/images で管理する必要があります。images フィールドは無視されました。',
                'sub-resource-stripped-inventories' => '在庫は PUT /api/admin/catalog/products/{id}/inventories で管理する必要があります。inventories フィールドは無視されました。',
                'sub-resource-stripped-customer-group-prices' => '顧客グループ価格は /api/admin/catalog/products/{id}/customer-group-prices で管理する必要があります。customer_group_prices フィールドは無視されました。',
                'translations-single-locale' => 'リクエストされたロケールのみ更新されました。ロケール :locales を更新するには、それぞれについて ?locale=<code> を付けて個別にリクエストを送信してください。',
            ],

            'delete' => [
                'deleted' => '商品が正常に削除されました。',
                'delete-failed' => '商品の削除に失敗しました。',
            ],

            'image' => [
                'uploaded' => '商品画像が正常にアップロードされました。',
                'updated' => '商品画像が正常に更新されました。',
                'update-empty' => '更新する代替テキストまたは位置を送信してください。',
                'reordered' => '商品画像が正常に並べ替えられました。',
                'deleted' => '商品画像が正常に削除されました。',
                'image-required' => '画像ファイルが必要です (multipart フィールド "image")。',
                'image-invalid-type' => '画像の形式は bmp、jpeg、jpg、png、webp のいずれかである必要があります。',
                'image-too-large' => '画像が大きすぎます。許可されている最大サイズは 4 MB です。',
                'not-found' => '商品画像が見つかりません。',
                'no-permission' => '商品画像を管理する権限がありません。',
                'upload-failed' => '商品画像のアップロードに失敗しました。',
                'reorder-failed' => '商品画像の並べ替えに失敗しました。',
                'delete-failed' => '商品画像の削除に失敗しました。',
                'order-required' => '"order" 配列は必須で、空にできません。',
                'order-invalid' => '"order" の各エントリは id と position を持つオブジェクトである必要があります。',
                'id-not-on-product' => '画像 :id はこの商品に属していません。',
                'graphql-upload-unsupported' => 'GraphQL での画像アップロードはサポートされていません。multipart/form-data で POST /api/admin/catalog/products/{productId}/images を使用してください。',
            ],

            'downloadable' => [
                'file-required' => 'ファイルが必要です (multipart フィールド "file")。',
                'file-too-large' => 'ファイルが大きすぎます。許可されている最大サイズは 128 MB です。',
                'upload-failed' => 'ダウンロード可能ファイルのアップロードに失敗しました。',
                'file-not-found' => 'この商品と属性に保存されているファイルが見つかりません。',
                'no-permission' => 'ダウンロード可能商品のファイルを管理する権限がありません。',
            ],

            'video' => [
                'uploaded' => '商品動画が正常にアップロードされました。',
                'deleted' => '商品動画が正常に削除されました。',
                'video-required' => '動画ファイルが必要です (multipart フィールド "video")。',
                'video-invalid-type' => '動画の形式は mp4、webm、ogg、mov のいずれかである必要があります。',
                'video-too-large' => '動画が大きすぎます。許可されている最大サイズは 100 MB です。',
                'not-found' => '商品動画が見つかりません。',
                'no-permission' => '商品動画を管理する権限がありません。',
                'upload-failed' => '商品動画のアップロードに失敗しました。',
                'delete-failed' => '商品動画の削除に失敗しました。',
                'graphql-upload-unsupported' => 'GraphQL での動画アップロードはサポートされていません。multipart/form-data で POST /api/admin/catalog/products/{productId}/videos を使用してください。',
            ],

            'inventory' => [
                'updated' => '商品の在庫が正常に保存されました。',
                'inventories-required' => 'inventories フィールドは必須で、inventory_source_id から数量への空でないマップである必要があります。',
                'inventories-invalid' => 'inventories フィールドは、inventory_source_id (正の整数) から数量 (0 以上の整数) へのマップである必要があります。',
                'source-not-found' => '在庫ソース :id は存在しません。',
                'qty-invalid' => '各数量は 0 以上の整数である必要があります。',
                'no-permission' => '商品の在庫を管理する権限がありません。',
                'not-found' => '商品が見つかりません。',
            ],

            'customer-group-price' => [
                'created' => '顧客グループ価格が正常に追加されました。',
                'updated' => '顧客グループ価格が正常に更新されました。',
                'deleted' => '顧客グループ価格が正常に削除されました。',
                'qty-required' => 'qty フィールドは必須です。',
                'qty-invalid' => 'qty フィールドは 1 以上の整数である必要があります。',
                'value-type-required' => 'value_type フィールドは必須です。',
                'value-type-invalid' => 'value_type フィールドは "fixed" または "discount" のいずれかである必要があります。',
                'value-required' => 'value フィールドは必須です。',
                'value-invalid' => 'value フィールドは 0 以上の数値である必要があります。',
                'customer-group-not-found' => '選択された顧客グループは存在しません。',
                'duplicate-qty-group' => 'この商品には、この (qty, customer_group_id) の組み合わせの顧客グループ価格が既に存在します。',
                'not-found' => '顧客グループ価格が見つかりません。',
                'no-permission' => '商品の顧客グループ価格を管理する権限がありません。',
            ],
        ],

        'category' => [
            'not-found' => 'カテゴリが見つかりません。',

            'created' => 'カテゴリが正常に作成されました。',
            'updated' => 'カテゴリが正常に更新されました。',
            'deleted' => 'カテゴリが正常に削除されました。',
            'slug-required' => 'slug フィールドは必須です。',
            'slug-unique' => 'この slug は既に使用されています。',
            'name-required' => 'name フィールドは必須です。',
            'position-required' => 'position フィールドは必須です。',
            'attributes-required' => '少なくとも1つのフィルター可能な属性が必要です。',
            'description-required-for-mode' => '選択された表示モードには説明が必要です。',
            'cannot-delete-root' => 'ルートカテゴリとチャネルのルートカテゴリは削除できません。',
            'cannot-delete-channel-root' => 'このカテゴリはチャネルのルートであるため、削除できません。',
            'delete-failed' => 'カテゴリを削除できませんでした。',
            'no-permission' => 'カテゴリを管理する権限がありません。',
            'mass-delete-success' => 'カテゴリが正常に削除されました。',
            'mass-update-status-success' => 'カテゴリのステータスが正常に更新されました。',
            'mass-delete-indices-required' => 'indices フィールドは必須で、空でない配列である必要があります。',
            'mass-update-status-value-required' => 'value フィールドは必須で、0 または 1 である必要があります。',
        ],

        'attribute' => [
            'not-found' => '属性が見つかりません。',

            'create-success' => '属性が正常に作成されました。',
            'update-success' => '属性が正常に更新されました。',
            'delete-success' => '属性が正常に削除されました。',
            'delete-failed' => '属性を削除できませんでした。',
            'mass-delete-success' => '属性が正常に削除されました。',
            'mass-delete-indices-required' => 'indices フィールドは必須で、空でない配列である必要があります。',

            'code-immutable' => '属性コードは属性の作成後に変更できません。',
            'type-immutable' => '商品の値がある間は属性タイプを変更できません。',
            'locale-scope-immutable' => '属性に商品の値がある間はロケールスコープを変更できません。',
            'system-attribute' => 'システム属性は削除できません。',
            'in-use-family' => 'この属性は1つ以上の属性ファミリー (グループ ID: :ids) に含まれています。先にそれらのファミリーから削除してください。',

            'option-not-found' => '属性オプションが見つかりません。',
            'option-not-supported' => '属性タイプ「:type」はオプションをサポートしていません。オプションを持てるのは select、multiselect、checkbox の属性のみです。',
            'option-in-use' => 'このオプションは :count 件の商品で使用されているため、削除できません。',
            'option-delete-success' => '属性オプションが正常に削除されました。',
        ],

        'family' => [
            'not-found' => '属性ファミリーが見つかりません。',

            'created' => '属性ファミリーが正常に作成されました。',
            'updated' => '属性ファミリーが正常に更新されました。',
            'deleted' => '属性ファミリーが正常に削除されました。',
            'code-required' => 'ファミリーコードは必須です。',
            'code-unique' => 'ファミリーコードは一意である必要があります。',
            'name-required' => 'ファミリー名は必須です。',
            'group-code-required' => '各属性グループにはコードが必要です。',
            'group-name-required' => '各属性グループには名前が必要です。',
            'group-column-invalid' => '各属性グループの列は 1 または 2 である必要があります。',
            'group-name-duplicate' => '属性グループ名は属性ファミリー内で一意である必要があります。',
            'update-failed' => '属性ファミリーを更新できませんでした。',
            'create-failed' => '属性ファミリーを作成できませんでした。',
            'default-delete-error' => 'デフォルトの属性ファミリーは削除できません。',
            'last-delete-error' => '少なくとも1つの属性ファミリーが必要です。',
            'attribute-product-error' => 'この属性ファミリーは1つ以上の商品で使用されています。削除する前にそれらの商品を再割り当てしてください。',
            'delete-failed' => '属性ファミリーを削除できませんでした。',
            'no-permission' => '属性ファミリーを管理する権限がありません。',
        ],

        'settings' => [
            'locale' => [
                'not-found' => 'ロケールが見つかりません。',
                'created' => 'ロケールが正常に作成されました。',
                'updated' => 'ロケールが正常に更新されました。',
                'deleted' => 'ロケールが正常に削除されました。',
                'cannot-delete-last' => '少なくとも1つのロケールが必要です — 最後に残ったロケールは削除できません。',
                'cannot-delete-channel-default' => 'このロケールは1つ以上のチャネルのデフォルトロケールであるため、削除できません。',
                'delete-failed' => 'ロケールを削除できませんでした。',
                'no-permission' => 'ロケールを管理する権限がありません。',
                'mass-delete-success' => 'ロケールが正常に削除されました。',
                'mass-delete-indices-required' => 'indices フィールドは必須で、空でない配列である必要があります。',
            ],

            'theme' => [
                'not-found' => 'テーマのカスタマイズが見つかりません。',
                'created' => 'テーマのカスタマイズが正常に作成されました。',
                'updated' => 'テーマのカスタマイズが正常に更新されました。',
                'deleted' => 'テーマのカスタマイズが正常に削除されました。',
                'delete-failed' => 'テーマのカスタマイズを削除できませんでした。',
                'no-permission' => 'テーマのカスタマイズを管理する権限がありません。',
                'mass-delete-success' => 'テーマのカスタマイズが正常に削除されました。',
                'mass-delete-indices-required' => 'indices フィールドは必須で、空でない配列である必要があります。',
                'mass-update-success' => 'テーマのカスタマイズが正常に更新されました。',
                'mass-update-indices-required' => 'indices フィールドは必須で、空でない配列である必要があります。',
                'mass-update-value-invalid' => 'value フィールドは 0 または 1 である必要があります。',
            ],

            'inventory-source' => [
                'not-found' => '在庫ソースが見つかりません。',
                'created' => '在庫ソースが正常に作成されました。',
                'updated' => '在庫ソースが正常に更新されました。',
                'deleted' => '在庫ソースが正常に削除されました。',
                'code-unique' => 'このコードの在庫ソースは既に存在します。',
                'last-delete-error' => '少なくとも1つの在庫ソースが必要です — 最後に残ったソースは削除できません。',
                'in-use' => 'この在庫ソースは商品の在庫で参照されているため、削除できません。',
                'delete-failed' => '在庫ソースを削除できませんでした。',
                'no-permission' => '在庫ソースを管理する権限がありません。',
                'mass-delete-success' => '在庫ソースが正常に削除されました。',
                'mass-delete-indices-required' => 'indices フィールドは必須で、空でない配列である必要があります。',
            ],

            'exchange-rate' => [
                'not-found' => '為替レートが見つかりません。',
                'created' => '為替レートが正常に作成されました。',
                'updated' => '為替レートが正常に更新されました。',
                'deleted' => '為替レートが正常に削除されました。',
                'duplicate-pair' => 'この対象通貨の為替レートは既に存在します。既存の行を更新するか、先に削除してください。',
                'delete-failed' => '為替レートを削除できませんでした。',
                'no-permission' => '為替レートを管理する権限がありません。',
                'mass-delete-success' => '為替レートが正常に削除されました。',
                'mass-delete-indices-required' => 'indices フィールドは必須で、空でない配列である必要があります。',
                'update-rates-success' => '為替レートが正常に更新されました。',
                'update-rates-failed' => '外部プロバイダーから為替レートを更新できませんでした。',
                'provider-not-configured' => '外部の為替レートプロバイダーが設定されていません。',
            ],

            'tax-rate' => [
                'not-found' => '税率が見つかりません。',
                'created' => '税率が正常に作成されました。',
                'updated' => '税率が正常に更新されました。',
                'deleted' => '税率が正常に削除されました。',
                'delete-failed' => '税率を削除できませんでした。',
                'no-permission' => '税率を管理する権限がありません。',
                'zip-code-required' => 'is_zip が false の場合、zip_code フィールドは必須です。',
                'zip-range-required' => 'is_zip が true の場合、zip_from と zip_to の両方が必須です。',
            ],

            'tax-category' => [
                'not-found' => '税カテゴリが見つかりません。',
                'created' => '税カテゴリが正常に作成されました。',
                'updated' => '税カテゴリが正常に更新されました。',
                'deleted' => '税カテゴリが正常に削除されました。',
                'code-unique' => 'このコードは既に使用されています。',
                'cannot-delete-in-use' => 'この税カテゴリにはまだ税率が紐付いているため、削除できません。',
                'delete-failed' => '税カテゴリを削除できませんでした。',
                'no-permission' => '税カテゴリを管理する権限がありません。',
            ],

            'role' => [
                'not-found' => 'ロールが見つかりません。',
                'created' => 'ロールが正常に作成されました。',
                'updated' => 'ロールが正常に更新されました。',
                'deleted' => 'ロールが正常に削除されました。',
                'permissions-required' => 'permission_type が custom の場合、permissions フィールドは必須です。',
                'cannot-delete-in-use' => 'このロールは1人以上の管理者に割り当てられているため、削除できません。',
                'cannot-delete-last-role' => '少なくとも1つのロールが必要です — 最後に残ったロールは削除できません。',
                'cannot-change-last-all-access' => '唯一のフルアクセスロールをカスタムロールに変更することはできません。',
                'delete-failed' => 'ロールを削除できませんでした。',
                'no-permission' => 'ロールを管理する権限がありません。',
            ],

            'currency' => [
                // Read
                'not-found' => '通貨が見つかりません。',

                // Create / Update / Delete
                'created' => '通貨が正常に作成されました。',
                'updated' => '通貨が正常に更新されました。',
                'deleted' => '通貨が正常に削除されました。',
                'code-required' => 'code フィールドは必須です。',
                'code-size' => 'コードはちょうど 3 文字の英字である必要があります。',
                'code-unique' => 'このコードは既に使用されています。',
                'name-required' => 'name フィールドは必須です。',
                'cannot-delete-last' => '少なくとも1つの通貨が必要です — 最後に残った通貨は削除できません。',
                'cannot-delete-channel-base' => 'この通貨は1つ以上のチャネルの基本通貨であるため、削除できません。',
                'delete-failed' => '通貨を削除できませんでした。',
                'no-permission' => '通貨を管理する権限がありません。',

                'mass-delete-success' => '通貨が正常に削除されました。',
                'mass-delete-indices-required' => 'indices フィールドは必須で、空でない配列である必要があります。',
            ],

            'channel' => [
                'not-found' => 'チャネルが見つかりません。',

                'created' => 'チャネルが正常に作成されました。',
                'updated' => 'チャネルが正常に更新されました。',
                'deleted' => 'チャネルが正常に削除されました。',
                'code-unique' => 'このコードは既に使用されています。',
                'hostname-unique' => 'このホスト名は既に使用されています。',
                'default-locale-mismatch' => 'default_locale_id はロケールのリストに含まれている必要があります。',
                'base-currency-mismatch' => 'base_currency_id は通貨のリストに含まれている必要があります。',
                'unknown-locale' => '1つ以上のロケール ID が存在しません。',
                'unknown-currency' => '1つ以上の通貨 ID が存在しません。',
                'unknown-inventory-source' => '1つ以上の inventory_source ID が存在しません。',
                'unknown-root-category' => 'root_category_id が存在しません。',
                'cannot-delete-last' => '少なくとも1つのチャネルが必要です — 最後に残ったチャネルは削除できません。',
                'cannot-delete-default' => 'アプリケーションのデフォルトチャネルは削除できません。',
                'delete-failed' => 'チャネルを削除できませんでした。',
                'no-permission' => 'チャネルを管理する権限がありません。',
            ],

            'user' => [
                'not-found' => '管理者ユーザーが見つかりません。',
                'created' => '管理者ユーザーが正常に作成されました。',
                'updated' => '管理者ユーザーが正常に更新されました。',
                'deleted' => '管理者ユーザーが正常に削除されました。',
                'name-required' => 'name フィールドは必須です。',
                'email-required' => 'email フィールドは必須です。',
                'email-invalid' => 'メールアドレスは有効な形式である必要があります。',
                'email-unique' => 'このメールアドレスは既に使用されています。',
                'password-required' => 'password フィールドは必須です。',
                'password-min' => 'パスワードは 6 文字以上である必要があります。',
                'role-required' => 'role_id フィールドは必須です。',
                'role-not-found' => '選択されたロールは存在しません。',
                'status-invalid' => 'ステータスは 0 または 1 である必要があります。',
                'cannot-delete-self' => '自分自身の管理者アカウントは削除できません。',
                'cannot-delete-last-admin' => '少なくとも1人の管理者ユーザーが必要です — 最後に残った管理者は削除できません。',
                'cannot-change' => '最後のフルアクセス管理者の :name は変更できません。',
                'delete-failed' => '管理者ユーザーを削除できませんでした。',
                'no-permission' => '管理者ユーザーを管理する権限がありません。',
                'self-delete-password-required' => '自分のアカウントを削除するには password フィールドが必須です。',
                'self-delete-incorrect-password' => 'パスワードが正しくありません。',
                'self-deleted' => '管理者アカウントが削除されました。',
            ],

            'data-transfer' => [
                'import' => [
                    'not-found' => 'インポートが見つかりません。',
                    'deleted' => 'インポートが正常に削除されました。',
                    'cancelled' => 'インポートが正常にキャンセルされました。',
                    'cannot-cancel' => 'キャンセルできるのは保留中または処理中のインポートのみです。現在の状態: :state。',
                    'no-permission' => 'データ転送のインポートを管理する権限がありません。',
                    'delete-failed' => 'インポートを削除できませんでした。',
                    'created' => 'インポートが正常に作成されました。',
                    'updated' => 'インポートが正常に更新されました。',
                    'type-invalid' => '選択されたタイプは無効です。',
                    'action-invalid' => 'アクションは append、delete のいずれかである必要があります。',
                    'validation-strategy-invalid' => '検証戦略は stop-on-errors、skip-errors のいずれかである必要があります。',
                    'allowed-errors-invalid' => '許容エラー数は 0 以上の整数である必要があります。',
                    'field-separator-required' => 'フィールド区切り文字は必須です。',
                    'file-required' => 'ファイルが必要です。',
                    'file-invalid-type' => 'ファイルは csv、xls、xlsx、xml のいずれかである必要があります。',
                    'nothing-to-import' => 'インポートするものがありません。',
                    'not-valid' => 'インポートは無効です。',
                    'setup-queue-error' => 'キュー接続が sync に設定されています。インポートをバックグラウンドで処理するには、実際のキューを設定してください。',
                    'file-not-found' => 'ダウンロードできるファイルがありません。',
                    'error-report-not-found' => 'ダウンロードできるエラーレポートがありません。',
                    'sample-not-found' => 'リクエストされたタイプと形式のサンプルファイルはありません。',
                    'sample-format-invalid' => 'サンプルの形式は csv、xls、xlsx、xml のいずれかである必要があります。',
                    'images-archive-too-large' => '画像アーカイブに含められるファイルは最大 :entries 個、展開後のサイズは最大 :size MB です。',
                ],
            ],
        ],

        'cms' => [
            'page' => [
                'not-found' => 'CMS ページが見つかりません。',

                'created' => 'CMS ページが正常に作成されました。',
                'url-key-required' => 'url_key フィールドは必須です。',
                'url-key-unique' => 'この url_key は既に使用されています。',
                'url-key-invalid' => 'url_key の形式が無効です。小文字の英字、数字、ハイフンのみを使用してください。',
                'page-title-required' => 'page_title フィールドは必須です。',
                'html-content-required' => 'html_content フィールドは必須です。',
                'channels-required' => '少なくとも1つのチャネルを選択する必要があります。',
                'channels-invalid' => '1つ以上のチャネルが無効です。',

                'updated' => 'CMS ページが正常に更新されました。',

                'deleted' => 'CMS ページが正常に削除されました。',
                'delete-failed' => 'CMS ページを削除できませんでした。',

                'mass-deleted' => 'CMS ページが正常に削除されました。',
                'indices-required' => 'indices フィールドは必須で、空でない配列である必要があります。',
                'indices-invalid' => 'indices フィールドは CMS ページ ID の配列である必要があります。',

                'no-permission' => 'CMS ページを管理する権限がありません。',
            ],
        ],

        'marketing' => [
            'catalog-rule' => [
                'not-found' => 'カタログルールが見つかりません。',
                'created' => 'カタログルールが正常に作成されました。',
                'updated' => 'カタログルールが正常に更新されました。',
                'deleted' => 'カタログルールが正常に削除されました。',
                'delete-failed' => 'カタログルールを削除できませんでした。',
                'mass-delete-indices-required' => '一括削除するカタログルールの ID が指定されていません。',
                'mass-delete-success' => '選択したカタログルールが正常に削除されました。',
                'no-permission' => 'カタログルールを管理する権限がありません。',
            ],

            'campaign' => [
                'not-found' => 'キャンペーンが見つかりません。',
                'created' => 'キャンペーンが正常に作成されました。',
                'updated' => 'キャンペーンが正常に更新されました。',
                'deleted' => 'キャンペーンが正常に削除されました。',
                'delete-failed' => 'キャンペーンを削除できませんでした。',
                'no-permission' => 'キャンペーンを管理する権限がありません。',

                'send' => [
                    'id-required' => 'キャンペーン ID が必要です。',
                    'inactive' => '無効なキャンペーンは送信できません。先に有効にしてください。',
                    'queued' => 'キャンペーンを :count 件の受信者に向けてキューに追加しました。',
                ],
            ],

            'sitemap' => [
                'not-found' => 'サイトマップが見つかりません。',
                'created' => 'サイトマップが正常に作成されました。',
                'updated' => 'サイトマップが正常に更新されました。',
                'deleted' => 'サイトマップが正常に削除されました。',
                'delete-failed' => 'サイトマップを削除できませんでした。',
                'no-permission' => 'サイトマップを管理する権限がありません。',

                'generate' => [
                    'id-required' => 'サイトマップ ID が必要です。',
                    'no-channels' => 'このサイトマップはどのチャネルも対象にしていないため、生成するものがありません。先に少なくとも1つのチャネルを割り当ててください。',
                    'success' => 'サイトマップが正常に再生成されました。',
                    'failed' => 'サイトマップの生成に失敗しました: :message',
                ],
            ],

            'cart-rule' => [
                'not-found' => 'カートルールが見つかりません。',
                'id-required' => 'カートルール ID が必要です。',
                'copy-of' => ':value のコピー',
                'copied' => 'カートルールが正常にコピーされました。',
                'created' => 'カートルールが正常に作成されました。',
                'updated' => 'カートルールが正常に更新されました。',
                'deleted' => 'カートルールが正常に削除されました。',
                'delete-failed' => 'カートルールを削除できませんでした。',
                'no-permission' => 'カートルールを管理する権限がありません。',
                'mass-delete-success' => 'カートルールが正常に削除されました。',
                'mass-delete-indices-required' => 'indices フィールドは必須で、空でない配列である必要があります。',
                'action-type-invalid' => 'action_type は by_percent、by_fixed、cart_fixed、buy_x_get_y のいずれかである必要があります。',
                'coupon-code-required' => 'coupon_type=1 かつ use_auto_generation=0 の場合、coupon_code が必要です。',
                'coupon-code-unique' => 'このクーポンコードは既に使用されています。',
            ],

            'template' => [
                'campaign-associated' => 'このメールテンプレートはキャンペーンで使用されているため、削除できません。先にキャンペーンから外してください。',
                'not-found' => 'メールテンプレートが見つかりません。',
                'created' => 'メールテンプレートが正常に作成されました。',
                'updated' => 'メールテンプレートが正常に更新されました。',
                'deleted' => 'メールテンプレートが正常に削除されました。',
                'delete-failed' => 'メールテンプレートを削除できませんでした。',
                'status-invalid' => 'ステータスは active、inactive、draft のいずれかである必要があります。',
                'no-permission' => 'メールテンプレートを管理する権限がありません。',
            ],

            'event' => [
                'campaign-associated' => 'このイベントはキャンペーンで使用されているため、削除できません。先にキャンペーンから外してください。',
                'not-found' => 'マーケティングイベントが見つかりません。',
                'created' => 'マーケティングイベントが正常に作成されました。',
                'updated' => 'マーケティングイベントが正常に更新されました。',
                'deleted' => 'マーケティングイベントが正常に削除されました。',
                'delete-failed' => 'マーケティングイベントを削除できませんでした。',
                'no-permission' => 'マーケティングイベントを管理する権限がありません。',
            ],

            'search-synonym' => [
                'not-found' => '検索類義語が見つかりません。',
                'created' => '検索類義語が正常に作成されました。',
                'updated' => '検索類義語が正常に更新されました。',
                'deleted' => '検索類義語が正常に削除されました。',
                'mass-deleted' => '検索類義語が正常に削除されました。',
                'delete-failed' => '検索類義語を削除できませんでした。',
                'indices-required' => 'indices フィールドは必須で、検索類義語 ID の空でない配列である必要があります。',
                'no-permission' => '検索類義語を管理する権限がありません。',
            ],

            'url-rewrite' => [
                'not-found' => 'URL リライトが見つかりません。',
                'created' => 'URL リライトが正常に作成されました。',
                'updated' => 'URL リライトが正常に更新されました。',
                'deleted' => 'URL リライトが正常に削除されました。',
                'delete-failed' => 'URL リライトを削除できませんでした。',
                'mass-deleted' => 'URL リライトが正常に削除されました。',
                'indices-required' => '少なくとも1つの URL リライト ID が必要です。',
                'no-permission' => 'URL リライトを管理する権限がありません。',
            ],

            'cart-rule-coupon' => [
                'cart-rule-not-found' => 'カートルールが見つかりません。',
                'not-found' => 'クーポンが見つかりません。',
                'code-taken' => 'このコードのクーポンは既に存在します。',
                'format-invalid' => '形式は alphabetic、alphanumeric、numeric のいずれかである必要があります。',
                'indices-required' => 'indices フィールドは必須で、クーポン ID の空でない配列である必要があります。',
                'created' => 'クーポンが正常に作成されました。',
                'generated' => ':count 件のクーポンが正常に生成されました。',
                'deleted' => 'クーポンが正常に削除されました。',
                'mass-deleted' => ':count 件のクーポンが正常に削除されました。',
                'no-permission' => 'カートルールのクーポンを管理する権限がありません。',
            ],

            'subscriber' => [
                'not-found' => 'ニュースレターの購読者が見つかりません。',
                'updated' => '購読が正常に更新されました。',
                'deleted' => '購読が正常に削除されました。',
                'is-subscribed-required' => 'is_subscribed フィールドは必須です。',
                'no-permission' => 'ニュースレターの購読者を管理する権限がありません。',
            ],

            'search-term' => [
                'not-found' => '検索語が見つかりません。',
                'updated' => '検索語が正常に更新されました。',
                'deleted' => '検索語が正常に削除されました。',
                'mass-deleted' => '検索語が正常に削除されました。',
                'term-required' => 'term フィールドは必須です。',
                'redirect-url-invalid' => 'redirect_url は有効な URL である必要があります。',
                'indices-required' => 'indices に少なくとも1つの ID を指定する必要があります。',
                'no-permission' => '検索語を管理する権限がありません。',
            ],
        ],
        'auth' => [
            'configuration' => [
                'title' => '管理者認証',
                'info' => '管理者 API ログインと API トークンのトークン有効期間、レート制限、セキュリティルール。',

                'tokens' => [
                    'title' => 'トークンの有効期間',
                    'info' => 'アクセストークン、リフレッシュトークン、セッションの有効期間。変更はその後に発行されるトークンに適用されます。',
                    'access-token-ttl' => 'アクセストークンの有効期間（分）',
                    'access-token-ttl-info' => '5～60 分。既定値 15。',
                    'refresh-token-ttl' => 'リフレッシュトークンの有効期間（日）',
                    'refresh-token-ttl-info' => '1～90 日。既定値 30。',
                    'session-lifetime' => 'セッションの最大有効期間（日）',
                    'session-lifetime-info' => '1～365 日。既定値 90。この期間を過ぎると、何度リフレッシュしても管理者は再ログインが必要です。',
                ],

                'rate-limits' => [
                    'title' => 'レート制限',
                    'info' => '1 分あたりの管理者 API リクエストの上限。',
                    'session-requests-per-minute' => 'ログイン中の管理者ごとの 1 分あたりのリクエスト数',
                    'api-token-requests-per-minute' => 'API トークンごとの 1 分あたりのリクエスト数',
                ],

                'security' => [
                    'title' => 'セキュリティ',
                    'info' => '失効ルール、API トークンの有効期限、ドメインバインディング。',
                    'revoke-sessions-on-password-change' => 'パスワード変更時にセッションを失効させる',
                    'revoke-api-tokens-on-password-change' => 'パスワード変更時に API トークンを失効させる',
                    'allow-non-expiring-api-tokens' => '有効期限のない API トークンを許可する',
                    'domain-binding-mode' => 'ドメインバインディングモード',
                    'domain-binding-mode-info' => 'DNS は、トークンのドメインが解決される IP アドレスからのリクエストかを確認します。Origin は Origin または Referer ヘッダーを確認し、ブラウザーからの呼び出しにのみ適しています。',
                    'domain-binding-dns' => 'DNS（クライアントの IP アドレス）',
                    'domain-binding-origin' => 'Origin ヘッダー',
                ],
            ],

            'errors' => [
                'unauthenticated' => '認証されていません。',
                'access-token-expired' => 'アクセストークンの有効期限が切れました。続行するにはリフレッシュしてください。',
                'api-token-expired' => 'API トークンの有効期限が切れました。',
                'refresh-token-invalid' => 'リフレッシュトークンが無効か期限切れです。再度ログインしてください。',
            ],
        ],
    ],

    'integration' => [
        'menu' => [
            'title' => '統合',
            'tokens' => 'トークン',
        ],

        'history' => [
            'menu' => [
                'title' => '歴史',
            ],

            'acl' => [
                'title' => 'API変更履歴',
                'view' => '表示',
                'delete' => '履歴の削除',
            ],

            'index' => [
                'title' => 'API変更履歴',
                'info' => '管理 API を通じて行われたすべての作成、更新、削除。誰が実行したのか、どのトークンが変更されたのか。',
                'cleanup-btn' => '古いログを削除する',
                'cleanup-days' => 'この日数より古いログを削除します',
                'cleanup-confirm' => '指定された日数より古い履歴をすべて削除しますか?これを元に戻すことはできません。',
            ],

            'view' => [
                'title' => '変更',
                'back-btn' => '戻る',
                'admin' => '管理者',
                'token' => 'トークン',
                'action' => 'アクション',
                'resource' => 'リソース',
                'method' => '方法',
                'ip' => 'IPアドレス',
                'date' => '日付',
                'version' => 'バージョン',
                'url' => 'エンドポイント',
                'request-details' => 'リクエストの詳細',
                'changes' => '変更点',
                'field' => 'フィールド',
                'old' => '古い値',
                'new' => '新しい価値',
                'no-field-changes' => 'このエントリにはフィールドレベルの変更は記録されませんでした。',
                'same-request' => '同じリクエスト内の他の変更',
                'version-chain' => 'このレコードのバージョン履歴',
            ],

            'datagrid' => [
                'id' => 'ID',
                'date' => '日付',
                'admin' => '管理者',
                'token' => 'トークン',
                'action' => 'アクション',
                'operation' => '操作',
                'resource' => 'リソース',
                'version' => 'バージョン',
                'method' => '方法',
                'ip' => 'IP',
                'view' => '見る',
                'delete' => '削除',
            ],

            'events' => [
                'created' => '作成されました',
                'updated' => '更新されました',
                'deleted' => '削除されました',
            ],

            'deleted' => ':count 件の履歴レコードが削除されました。',
            'cleanup-input-required' => 'クリーンアップする日数または日付を指定します。',
        ],

        'acl' => [
            'title' => '統合',
            'view' => '表示',
            'create' => '統合の作成',
            'edit' => '統合の編集',
            'delete' => '統合トークンの取り消し',
            'generate' => '統合トークンの生成',
            'regenerate' => '統合トークンを再生成する',
        ],

        'index' => [
            'title' => '統合',
            'create-btn' => '統合の作成',
        ],

        'create' => [
            'title' => '統合の作成',
            'save-btn' => '保存',
            'back-btn' => '戻る',
        ],

        'edit' => [
            'title' => '統合の編集',
            'save-btn' => '保存',
            'back-btn' => '戻る',
            'generate-btn' => 'トークンの生成',
            'regenerate-btn' => 'トークンを再生成する',
            'revoke-btn' => 'トークンの取り消し',
            'copy-btn' => 'コピー',
            'token-warning' => 'このトークンを今すぐ保存してください。再度表示されることはありません。',
            'token-label' => 'トークン',
            'not-generated' => 'まだ生成されていません',
            'masked' => '(保存 — 生成時に 1 回のみ表示されます)',
            'history-banner' => 'このトークンはもうアクティブではありません。',
        ],

        'fields' => [
            'name' => '名前',
            'description' => '説明',
            'assign-user' => 'ユーザーの割り当て',
            'permission-type' => '権限の種類',
            'access-control' => 'アクセス制御',
            'general' => '一般',
            'token-settings' => 'トークン設定',
            'valid-till' => '有効期限',
            'rate-limit-per-minute' => 'レート制限 (1 分あたり)',
            'rate-limit-per-day' => 'レート制限 (1 日あたり)',
            'never-expires' => '有効期限はありません',
            'expires-on' => '有効期限は次のとおりです',
            'unlimited' => '無制限',
            'limit-to' => 'に制限する',
            'requests-per-minute' => 'リクエスト/分',
            'requests-per-day' => 'リクエスト/日',
            'select-admin' => '管理者を選択してください',
            'no-available-admins' => '利用可能な管理者がいません - すべての管理者がすでにアクティブなトークンを持っています。',
            'same-as-web-hint' => 'トークンは、割り当てられた管理者の現在の役割の権限をライブでミラーリングします。',
            'ip-allowlist' => 'IP許可リスト',
            'ip-any' => '任意の IP (デフォルト)',
            'ip-restricted' => '特定のIPに制限される',
            'ip-list-hint' => '1 行に 1 つのエントリ。 IPv4、IPv6、CIDR (例: 10.0.0.0/24 または 2001:db8::/32) をサポートします。すべての IP を許可するには、空白のままにします。',
        ],

        'permission-type' => [
            'all' => 'すべて',
            'custom' => 'カスタム',
            'same-as-web' => 'Web権限と同じ',
        ],

        'status' => [
            'draft' => '草案',
            'active' => 'アクティブ',
            'revoked' => '取り消されました',
            'regenerated' => '再生された',
        ],

        'datagrid' => [
            'id' => 'ID',
            'name' => '名前',
            'admin' => '管理者',
            'token' => 'トークン',
            'status' => 'ステータス',
            'permission-type' => '権限の種類',
            'expires-at' => '有効期限',
            'last-used-at' => '最後に使用したもの',
            'created-at' => '作成日',
            'edit' => '編集',
            'revoke' => '取り消し',
        ],

        'messages' => [
            'draft-created' => '統合が作成されました。トークンを生成して使用を開始します。',
            'updated' => '統合は正常に更新されました。',
            'generated' => 'トークンが生成されました。今すぐコピーしてください。再度表示されることはありません。',
            'regenerated' => 'トークンが再生成されました。新しいトークンを今すぐコピーします。再度表示されることはありません。',
            'revoked' => 'トークンは正常に取り消されました。',
            'generate-only-draft' => 'ドラフト統合のみがトークンを生成できます。',
            'regenerate-only-active' => 'アクティブなトークンのみを再生成できます。',
            'cannot-edit-historic' => '取り消されたトークンまたは再生成されたトークンは編集できません。',
            'already-inactive' => 'このトークンはすでに非アクティブです。',
        ],

        'errors' => [
            'admin-has-token' => '選択した管理者はすでにアクティブな統合トークンを持っています。',
            'only-draft-generate' => '生成できるのは下書きのトークンのみです。',
        ],

        'validation' => [
            'ip-invalid' => '許可される各 IP は、有効な IPv4 または IPv6 アドレスである必要があります (CIDR 表記がサポートされています)。',
            'cidr-prefix-invalid' => 'CIDR プレフィックスは、指定された IP バージョンでは無効です。',
        ],

        'configuration' => [
            'api' => [
                'title' => 'API',
                'info' => 'Bagisto API とその管理モジュールの設定。',
            ],

            'integration' => [
                'title' => '統合',
                'info' => '管理 API トークンの発行に使用される API 統合プラグインを管理します。',
            ],

            'settings' => [
                'title' => 'モジュール設定',
                'info' => 'API 統合プラグインを有効または無効にします。無効にすると、サイドバー メニューが非表示になり、ページは 404 を返します。',
                'enable' => 'API統合モジュールを有効にする',
            ],
        ],

        'emails' => [
            'generated' => [
                'subject' => '新しい API トークンが生成されました: :name',
                'greeting' => '「:name」という名前の API 統合トークンがアカウントに生成されました。',
            ],

            'regenerated' => [
                'subject' => 'API トークンが再生成されました: :name',
                'greeting' => '「:name」という名前の API 統合トークンが再生成されました。以前のトークンは動作を停止しました。新しいトークンのみが有効です。',
            ],

            'revoked' => [
                'subject' => 'API トークンが取り消されました: :name',
                'greeting' => '「:name」という名前の API 統合トークンが取り消されました。これを使用しているクライアントはアクセスできなくなります。',
            ],

            'details' => [
                'name' => 'トークン名',
                'date' => '日付',
                'ip' => 'IPから',
            ],

            'revoke-hint' => 'これが予想外の場合は、下のボタンを使用して直ちにトークンを取り消してください。',
            'revoke-btn' => 'このトークンを取り消す',
            'revoke-expiry' => 'この取り消しリンクは 7 日間有効です。その後、管理パネルにサインインしてトークンを管理します。',
            'no-action' => '特別な対応は必要ありません。このメールは単なる確認です。',
        ],

        'revoke-confirmation' => [
            'title' => 'APIトークンを取り消す',
            'confirm-title' => 'このトークンを取り消しますか?',
            'confirm-message' => 'トークン「:name」は、それを使用しているすべてのクライアントで直ちに使用できなくなります。',
            'confirm-button' => 'トークンを取り消す',
            'success-title' => 'トークンが取り消されました',
            'success-message' => 'トークン「:name」は取り消されました。これを使用しているクライアントはすぐにアクセスできなくなります。',
            'already-inactive-title' => 'トークンはすでに非アクティブです',
            'already-inactive-message' => 'トークン「:name」はすでに取り消されているか、再生成されています。それ以上のアクションは必要ありません。',
        ],

        'confirm' => [
            'generate' => [
                'title' => 'トークンの生成',
                'message' => '今すぐトークンを生成しますか?プレーンテキストは 1 回だけ表示されます。ページを離れる前にコピーしてください。',
            ],

            'regenerate' => [
                'title' => 'トークンを再生成する',
                'message' => 'トークンを再生成しますか?古いトークンはすぐに動作を停止し、新しいプレーンテキストは 1 回だけ表示されます。',
            ],

            'revoke' => [
                'title' => 'トークンの取り消し',
                'message' => 'このトークンを取り消しますか?これを使用しているクライアントはすぐにアクセスできなくなります。この操作は元に戻すことができません。',
            ],
        ],
    ],
];
