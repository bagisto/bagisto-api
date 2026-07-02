<?php

return [
    'graphql' => [
        'cart' => [
            'authentication-required'           => '認証トークンが必要です',
            'invalid-token'                     => '認証トークンが無効か期限切れです',
            'unauthorized-access'               => 'カートへの不正なアクセスです',
            'authenticated-only'                => '認証済みのユーザーのみが自分のカートを取得できます',
            'merge-requires-auth'               => 'ゲストカートの統合には認証が必要です',
            'unknown-operation'                 => '不明なカート操作です',

            'cart-not-found'                    => 'カートが見つかりません',
            'guest-cart-not-found'              => 'ゲストカートが見つかりません',
            'product-not-found'                 => '商品が見つかりません',

            'product-id-quantity-required'      => '商品IDと数量が必要です',
            'cart-item-id-quantity-required'    => 'カートアイテムIDと数量が必要です',
            'cart-item-id-required'             => 'カートアイテムIDが必要です',
            'item-ids-required'                 => 'アイテムIDの配列が必要です',
            'coupon-code-required'              => 'クーポンコードが必要です',
            'address-data-required'             => '国、都道府県、郵便番号が必要です',

            'add-product-failed'                => '商品をカートに追加できませんでした',
            'update-item-failed'                => 'カートアイテムを更新できませんでした',
            'remove-item-failed'                => 'カートアイテムを削除できませんでした',
            'apply-coupon-failed'               => 'クーポンを適用できませんでした',
            'remove-coupon-failed'              => 'クーポンを削除できませんでした',
            'move-to-wishlist-failed'           => 'アイテムをほしい物リストに移動できませんでした',
            'estimate-shipping-failed'          => '送料を見積もることができませんでした',

            'product-added-successfully'         => '商品がカートに正常に追加されました',
            'guest-cart-merged'                  => 'ゲストカートが正常に統合されました',
            'using-authenticated-cart'           => '認証済みの顧客カートを使用しています',
            'cart-item-not-found'                => 'カートアイテムが見つかりません',
            'new-guest-cart-created'             => '一意のセッショントークンを持つ新しいゲストカートが作成されました',
            'select-items-to-remove'             => '削除するアイテムを選択してください',
            'select-items-to-move-wishlist'      => 'ほしい物リストに移動するアイテムを選択してください',
            'invalid-or-expired-token'           => 'カートトークンが無効か期限切れです。新しいカートを作成してください。',
            'invalid-token-of-login-user'        => 'ログインユーザーのトークンが無効です。',
        ],

        'token-verification' => [
            'invalid-operation'                 => '無効な操作です',
            'invalid-input-data'                => '無効な入力データです',
            'token-required'                    => 'トークンが必要です',
            'invalid-token-format'              => 'トークンの形式が無効です',
            'token-not-found-or-expired'        => 'トークンが見つからないか期限切れです',
            'customer-not-found'                => '顧客が見つかりません',
            'customer-account-suspended'        => '顧客アカウントは停止されています',
            'error-verifying-token'             => 'トークンの検証中にエラーが発生しました',
            'token-is-valid'                    => 'トークンは有効です',
        ],

        'forgot-password' => [
            'invalid-operation'                 => '無効な操作です',
            'invalid-input-data'                => '無効な入力データです',
            'email-required'                    => 'メールアドレスが必要です',
            'reset-link-sent'                   => 'リセットリンクがメールに正常に送信されました',
            'email-not-found'                   => 'メールアドレスが見つかりません',
            'error-sending-reset-link'          => 'リセットリンクの送信中にエラーが発生しました',
        ],

        'logout' => [
            'invalid-operation'                 => '無効な操作です',
            'invalid-input-data'                => '無効な入力データです',
            'token-required'                    => 'トークンが必要です',
            'invalid-token-format'              => 'トークンの形式が無効です',
            'logged-out-successfully'           => '正常にログアウトしました',
            'token-not-found-or-expired'        => 'トークンが見つからないか、すでに期限切れです',
            'error-during-logout'               => 'ログアウト中にエラーが発生しました',
        ],

        'address' => [
            'deleted-successfully'              => '住所が正常に削除されました',
            'authentication-required'           => '認証トークンが必要です',
            'invalid-token'                     => 'トークンが無効か期限切れです',
            'unknown-operation'                 => '不明な操作です',
            'address-id-required'               => '住所IDが必要です',
            'address-not-found'                 => '住所が見つからないか、この顧客に属していません',
            'retrieved'                         => '住所が正常に取得されました',
            'fetch-failed'                      => '住所の取得に失敗しました:',
        ],

        'customer-profile' => [
            'authentication-required'           => '認証トークンが必要です。クエリの入力にトークンを指定してください',
            'invalid-token'                     => 'トークンが無効か期限切れです',
        ],

        'customer' => [
            'password-mismatch'                 => 'パスワードと確認用パスワードが一致しません',
            'confirm-password-required'         => 'パスワードを変更する際は確認用パスワードが必要です',
            'unauthenticated'                   => '認証されていません。この操作を行うにはログインしてください',
        ],

        'product-review' => [
            'product-id-required'               => '商品IDが必要です',
            'product-not-found'                 => '商品が見つかりません',
            'rating-invalid'                    => '評価は1から5の間である必要があります',
            'title-required'                    => 'レビューのタイトルが必要です',
            'comment-required'                  => 'レビューのコメントが必要です',
        ],

        'product' => [
            'not-found-with-sku'                => 'No product found with SKU',
            'not-found-with-url-key'            => 'No product found with URL key',
            'parameters-required'               => 'At least one of the following parameters must be provided: "sku", "id", "urlKey"',
        ],

        'auth' => [
            'no-token-provided'                 => '認証トークンが提供されていません。Authorizationヘッダーに "Bearer <token>" として、または input.token フィールドにトークンを指定してください',
            'invalid-or-expired-token'          => 'トークンが無効か期限切れです',
            'request-not-found'                 => 'コンテキスト内にリクエストが見つかりません',
            'token-required'                    => 'Authentication token is required. Please provide the token either in the GraphQL mutation input field or in the Authorization header as "Bearer <token>"',
            'unknown-resource'                  => '不明なリソースです',
            'cannot-update-other-profile'       => '権限がありません: 他の顧客のプロフィールは更新できません',
        ],

        'upload' => [
            'invalid-base64'                    => 'base64でエンコードされた画像データが無効です',
            'size-exceeds-limit'                => '画像サイズは5MBを超えることはできません',
            'invalid-format'                    => '画像形式が無効です。データURIスキーム (data:image/jpeg;base64,...) を使用したbase64エンコード画像を指定してください',
            'failed'                            => '画像のアップロードに失敗しました',
        ],

        'attribute' => [
            'code-already-exists'               => '属性コードはすでに存在します',
        ],

        'login' => [
            'invalid-credentials'               => 'メールアドレスまたはパスワードが無効です',
            'account-suspended'                 => 'お客様のアカウントは停止されています',
            'successful'                        => '正常にログインしました',
            'invalid-request'                   => '無効なログインリクエストです',
        ],

        'checkout' => [
            'invalid-input'                     => 'チェックアウト操作の入力データが無効です',
            'billing-address-required'          => '請求先住所が必要です',
            'shipping-address-required'         => '配送には配送先住所が必要です',
            'address-save-failed'               => '住所を保存できませんでした',
            'address-saved'                     => '住所が正常に保存されました',
            'shipping-method-required'          => '配送方法が必要です',
            'invalid-shipping-method'           => '配送方法が無効か利用できません',
            'shipping-method-save-failed'       => '配送方法を保存できませんでした',
            'shipping-method-saved'             => '配送方法が正常に保存されました',
            'shipping-method-error'             => '配送方法の保存中にエラーが発生しました',
            'payment-method-required'           => '支払い方法が必要です',
            'invalid-payment-method'            => '支払い方法が無効か利用できません',
            'payment-method-save-failed'        => '支払い方法を保存できませんでした',
            'payment-method-saved'              => '支払い方法が正常に保存されました',
            'payment-method-error'              => '支払い方法の保存中にエラーが発生しました',
            'order-creation-failed'             => '注文の作成に失敗しました: 注文IDがnullであるか、注文が保存されていません',
            'order-retrieval-failed'            => '作成された注文を取得できませんでした',
            'order-creation-error'              => '注文を作成できませんでした',
            'cart-empty'                        => 'カートは空です',
            'account-suspended'                 => 'お客様のアカウントは停止されています。サポートにお問い合わせください。',
            'account-inactive'                  => 'お客様のアカウントは無効です。サポートにお問い合わせください。',
            'minimum-order-not-met'             => '最低注文金額は :amount です',
            'email-required'                    => '注文の作成にはメールアドレスが必要です',
            'unknown-operation'                 => '不明なチェックアウト操作です',
        ],

        'customer-addresses' => [
            'token-required'                    => '顧客の住所を取得するにはトークンが必要です',
            'invalid-or-expired-token'          => 'トークンが無効か期限切れです',
            'token-validation-failed'           => 'トークンの検証に失敗しました',
        ],

        'product' => [
            'type'                              => '商品タイプ',
            'attribute-family'                  => '属性ファミリー',
            'sku'                               => 'SKU',
            'name'                              => '名前',
            'description'                       => '説明',
            'short-description'                 => '短い説明',
            'status'                            => 'ステータス',
            'new'                               => '新着',
            'featured'                          => 'おすすめ',
            'price'                             => '価格',
            'special-price'                     => '特別価格',
            'weight'                            => '重量',
            'cost'                              => '原価',
            'length'                            => '長さ',
            'width'                             => '幅',
            'height'                            => '高さ',
            'color'                             => '色',
            'size'                              => 'サイズ',
            'brand'                             => 'ブランド',
            'super-attributes'                  => 'スーパー属性',
        ],

        'compare-item' => [
            'id-required'                       => '比較アイテムIDが必要です',
            'invalid-id-format'                 => '無効なID形式です。"/api/shop/compare-items/1"のようなIRI形式または数値IDが想定されています',
            'not-found'                         => '比較アイテムが見つかりません',
            'product-id-required'               => '商品IDが必要です',
            'customer-id-required'              => '顧客IDが必要です',
            'product-not-found'                 => '商品が見つかりません',
            'customer-not-found'                => '顧客が見つかりません',
            'already-exists'                    => 'この商品は既に比較リストに含まれています',
        ],

        'downloadable-product' => [
            'download-link-not-found'           => 'ダウンロードリンクが見つからないか、期限切れです',
            'purchased-link-not-found'          => '購入済みリンクが見つかりません',
            'file-not-found'                    => 'ファイルが見つかりません',
            'download-successful'               => 'ファイルはダウンロード可能です',
            'token-required'                    => 'ダウンロードトークンが必要です',
            'invalid-token'                     => 'ダウンロードトークンが無効か期限切れです',
            'token-expired'                     => 'ダウンロードトークンの有効期限が切れています。新しいものを生成してください',
            'access-denied'                     => 'アクセスが拒否されました：このファイルをダウンロードする権限がありません',
            'redirect-external-url'             => '外部ダウンロードURLにリダイレクト',
            'file-error'                        => 'ダウンロードリクエストの処理中にエラーが発生しました',
            'unauthorized-access'               => 'ダウンロードリソースへの不正なアクセス',
        ],
    ],

    'integration' => [
        'menu' => [
            'title'  => '統合',
            'tokens' => 'トークン',
        ],

        'history' => [
            'menu' => [
                'title' => '歴史',
            ],

            'acl' => [
                'title'  => 'API変更履歴',
                'delete' => '履歴の削除',
            ],

            'index' => [
                'title'           => 'API変更履歴',
                'info'            => '管理 API を通じて行われたすべての作成、更新、削除。誰が実行したのか、どのトークンが変更されたのか。',
                'cleanup-btn'     => '古いログを削除する',
                'cleanup-days'    => 'この日数より古いログを削除します',
                'cleanup-confirm' => '指定された日数より古い履歴をすべて削除しますか?これを元に戻すことはできません。',
            ],

            'view' => [
                'title'             => '変更',
                'back-btn'          => '戻る',
                'admin'             => '管理者',
                'token'             => 'トークン',
                'action'            => 'アクション',
                'resource'          => 'リソース',
                'method'            => '方法',
                'ip'                => 'IPアドレス',
                'date'              => '日付',
                'version'           => 'バージョン',
                'url'               => 'エンドポイント',
                'request-details'   => 'リクエストの詳細',
                'changes'           => '変更点',
                'field'             => 'フィールド',
                'old'               => '古い値',
                'new'               => '新しい価値',
                'no-field-changes'  => 'このエントリにはフィールドレベルの変更は記録されませんでした。',
                'same-request'      => '同じリクエスト内の他の変更',
                'version-chain'     => 'このレコードのバージョン履歴',
            ],

            'datagrid' => [
                'id'        => 'ID',
                'date'      => '日付',
                'admin'     => '管理者',
                'token'     => 'トークン',
                'action'    => 'アクション',
                'operation' => '操作',
                'resource'  => 'リソース',
                'version'   => 'バージョン',
                'method'    => '方法',
                'ip'        => 'IP',
                'view'      => '見る',
                'delete'    => '削除',
            ],

            'events' => [
                'created' => '作成されました',
                'updated' => '更新されました',
                'deleted' => '削除されました',
            ],

            'deleted'                => ':count 件の履歴レコードが削除されました。',
            'cleanup-input-required' => 'クリーンアップする日数または日付を指定します。',
        ],

        'acl' => [
            'title'      => '統合',
            'create'     => '統合の作成',
            'edit'       => '統合の編集',
            'delete'     => '統合トークンの取り消し',
            'generate'   => '統合トークンの生成',
            'regenerate' => '統合トークンを再生成する',
        ],

        'index' => [
            'title'      => '統合',
            'create-btn' => '統合の作成',
        ],

        'create' => [
            'title'    => '統合の作成',
            'save-btn' => '保存',
            'back-btn' => '戻る',
        ],

        'edit' => [
            'title'          => '統合の編集',
            'save-btn'       => '保存',
            'back-btn'       => '戻る',
            'generate-btn'   => 'トークンの生成',
            'regenerate-btn' => 'トークンを再生成する',
            'revoke-btn'     => 'トークンの取り消し',
            'copy-btn'       => 'コピー',
            'token-warning'  => 'このトークンを今すぐ保存してください。再度表示されることはありません。',
            'token-label'    => 'トークン',
            'not-generated'  => 'まだ生成されていません',
            'masked'         => '(保存 — 生成時に 1 回のみ表示されます)',
            'history-banner' => 'このトークンはもうアクティブではありません。',
        ],

        'fields' => [
            'name'                  => '名前',
            'description'           => '説明',
            'assign-user'           => 'ユーザーの割り当て',
            'permission-type'       => '権限の種類',
            'access-control'        => 'アクセス制御',
            'general'               => '一般',
            'token-settings'        => 'トークン設定',
            'valid-till'            => '有効期限',
            'rate-limit-per-minute' => 'レート制限 (1 分あたり)',
            'rate-limit-per-day'    => 'レート制限 (1 日あたり)',
            'never-expires'         => '有効期限はありません',
            'expires-on'            => '有効期限は次のとおりです',
            'unlimited'             => '無制限',
            'limit-to'              => 'に制限する',
            'requests-per-minute'   => 'リクエスト/分',
            'requests-per-day'      => 'リクエスト/日',
            'select-admin'          => '管理者を選択してください',
            'no-available-admins'   => '利用可能な管理者がいません - すべての管理者がすでにアクティブなトークンを持っています。',
            'same-as-web-hint'      => 'トークンは、割り当てられた管理者の現在の役割の権限をライブでミラーリングします。',
            'ip-allowlist'          => 'IP許可リスト',
            'ip-any'                => '任意の IP (デフォルト)',
            'ip-restricted'         => '特定のIPに制限される',
            'ip-list-hint'          => '1 行に 1 つのエントリ。 IPv4、IPv6、CIDR (例: 10.0.0.0/24 または 2001:db8::/32) をサポートします。すべての IP を許可するには、空白のままにします。',
        ],

        'permission_type' => [
            'all'         => 'すべて',
            'custom'      => 'カスタム',
            'same_as_web' => 'Web権限と同じ',
        ],

        'status' => [
            'draft'       => '草案',
            'active'      => 'アクティブ',
            'revoked'     => '取り消されました',
            'regenerated' => '再生された',
        ],

        'datagrid' => [
            'id'              => 'ID',
            'name'            => '名前',
            'admin'           => '管理者',
            'token'           => 'トークン',
            'status'          => 'ステータス',
            'permission-type' => '権限の種類',
            'expires-at'      => '有効期限',
            'last-used-at'    => '最後に使用したもの',
            'created-at'      => '作成日',
            'edit'            => '編集',
            'revoke'          => '取り消し',
        ],

        'messages' => [
            'draft-created'          => '統合が作成されました。トークンを生成して使用を開始します。',
            'updated'                => '統合は正常に更新されました。',
            'generated'              => 'トークンが生成されました。今すぐコピーしてください。再度表示されることはありません。',
            'regenerated'            => 'トークンが再生成されました。新しいトークンを今すぐコピーします。再度表示されることはありません。',
            'revoked'                => 'トークンは正常に取り消されました。',
            'generate-only-draft'    => 'ドラフト統合のみがトークンを生成できます。',
            'regenerate-only-active' => 'アクティブなトークンのみを再生成できます。',
            'cannot-edit-historic'   => '取り消されたトークンまたは再生成されたトークンは編集できません。',
            'already-inactive'       => 'このトークンはすでに非アクティブです。',
        ],

        'errors' => [
            'admin-has-token' => '選択した管理者はすでにアクティブな統合トークンを持っています。',
        ],

        'validation' => [
            'ip-invalid'           => '許可される各 IP は、有効な IPv4 または IPv6 アドレスである必要があります (CIDR 表記がサポートされています)。',
            'cidr-prefix-invalid'  => 'CIDR プレフィックスは、指定された IP バージョンでは無効です。',
        ],

        'configuration' => [
            'api' => [
                'title' => 'API',
                'info'  => 'Bagisto API とその管理モジュールの設定。',
            ],
            'integration' => [
                'title' => '統合',
                'info'  => '管理 API トークンの発行に使用される API 統合プラグインを管理します。',
            ],
            'settings' => [
                'title'  => 'モジュール設定',
                'info'   => 'API 統合プラグインを有効または無効にします。無効にすると、サイドバー メニューが非表示になり、ページは 404 を返します。',
                'enable' => 'API統合モジュールを有効にする',
            ],
        ],

        'emails' => [
            'generated' => [
                'subject'  => '新しい API トークンが生成されました: :name',
                'greeting' => '「:name」という名前の API 統合トークンがアカウントに生成されました。',
            ],
            'regenerated' => [
                'subject'  => 'API トークンが再生成されました: :name',
                'greeting' => '「:name」という名前の API 統合トークンが再生成されました。以前のトークンは動作を停止しました。新しいトークンのみが有効です。',
            ],
            'revoked' => [
                'subject'  => 'API トークンが取り消されました: :name',
                'greeting' => '「:name」という名前の API 統合トークンが取り消されました。これを使用しているクライアントはアクセスできなくなります。',
            ],

            'details' => [
                'name' => 'トークン名',
                'date' => '日付',
                'ip'   => 'IPから',
            ],

            'revoke-hint'   => 'これが予想外の場合は、下のボタンを使用して直ちにトークンを取り消してください。',
            'revoke-btn'    => 'このトークンを取り消す',
            'revoke-expiry' => 'この取り消しリンクは 7 日間有効です。その後、管理パネルにサインインしてトークンを管理します。',
            'no-action'     => '特別な対応は必要ありません。このメールは単なる確認です。',
        ],

        'revoke-confirmation' => [
            'title'                    => 'APIトークンを取り消す',
            'success-title'            => 'トークンが取り消されました',
            'success-message'          => 'トークン「:name」は取り消されました。これを使用しているクライアントはすぐにアクセスできなくなります。',
            'already-inactive-title'   => 'トークンはすでに非アクティブです',
            'already-inactive-message' => 'トークン「:name」はすでに取り消されているか、再生成されています。それ以上のアクションは必要ありません。',
        ],

        'confirm' => [
            'generate' => [
                'title'   => 'トークンの生成',
                'message' => '今すぐトークンを生成しますか?プレーンテキストは 1 回だけ表示されます。ページを離れる前にコピーしてください。',
            ],
            'regenerate' => [
                'title'   => 'トークンを再生成する',
                'message' => 'トークンを再生成しますか?古いトークンはすぐに動作を停止し、新しいプレーンテキストは 1 回だけ表示されます。',
            ],
            'revoke' => [
                'title'   => 'トークンの取り消し',
                'message' => 'このトークンを取り消しますか?これを使用しているクライアントはすぐにアクセスできなくなります。この操作は元に戻すことができません。',
            ],
        ],
    ],
];
