<?php

return [

    'graphql' => [
        'headers' => [
            'unknown-channel' => 'X-Channel 请求头中的渠道 ":code" 不存在。',
            'storefront-key-required' => '需要 X-STOREFRONT-KEY 请求头',
            'storefront-key-invalid' => '店面密钥无效',
            'storefront-key-rate-limited' => '已超出速率限制。请在 :seconds 秒后重试',
        ],

        'cart' => [
            'authentication-required' => '需要身份验证令牌',
            'invalid-token' => '身份验证令牌无效或已过期',
            'unauthorized-access' => '未授权访问购物车',
            'authenticated-only' => '只有已通过身份验证的用户才能获取其购物车',
            'merge-requires-auth' => '合并访客购物车需要身份验证',
            'unknown-operation' => '未知的购物车操作',

            'cart-not-found' => '未找到购物车',
            'guest-cart-not-found' => '未找到访客购物车',
            'product-not-found' => '未找到产品',

            'product-id-quantity-required' => '需要产品ID和数量',
            'cart-item-id-quantity-required' => '需要购物车项目ID和数量',

            'file-token-invalid' => '上传的文件引用无效或已过期。请重新上传文件。',
            'file-token-forbidden' => '此上传文件不属于您的购物车。',
            'customizable-option-required' => '可定制选项":label"为必填项。',
            'customizable-file-option-invalid' => '所选选项不是此产品的文件选项。',
            'customizable-file-invalid' => '文件缺失、扩展名不受支持或文件过大。',
            'cart-item-id-required' => '需要购物车项目ID',
            'item-ids-required' => '需要项目ID数组',

            'event-booking-quantity-not-changeable' => '活动预订产品的数量无法更改。数量由所选门票决定。',
            'appointment-booking-quantity-not-changeable' => '预约预订产品的数量无法更改。',
            'coupon-code-required' => '需要优惠券代码',
            'address-data-required' => '需要国家/地区、省份和邮政编码',
            'grouped-qty-required' => '组合产品需要指定所选数量。请以 JSON 字符串形式传递 groupedQty，例如 {"101":2,"102":1}。',
            'grouped-qty-must-include-all' => '组合产品需要为所有关联产品指定数量。缺少的ID：:ids。',
            'grouped-qty-invalid-associated' => '组合产品数量中包含无效的关联产品ID：:ids。',
            'grouped-qty-invalid-quantity' => '为关联产品ID :id 提供的数量无效。数量必须为非负整数。',
            'bundle-qty-not-changeable' => '捆绑选项":option"的数量无法更改。允许的数量为 :qty。',

            'add-product-failed' => '未能将产品添加到购物车',
            'update-item-failed' => '未能更新购物车项目',
            'remove-item-failed' => '未能移除购物车项目',
            'remove-coupon-failed' => '未能移除优惠券',
            'coupon-applied' => '优惠券已成功应用',
            'coupon-removed' => '优惠券已成功移除',
            'move-to-wishlist-failed' => '未能将项目移至心愿单',
            'estimate-shipping-failed' => '未能估算运费',

            'product-added-successfully' => '产品已成功添加到购物车',
            'cart-item-updated-successfully' => '购物车项目已成功更新',
            'guest-cart-merged' => '访客购物车已成功合并',
            'using-authenticated-cart' => '正在使用已通过身份验证的客户购物车',
            'cart-item-not-found' => '未找到购物车项目',
            'new-guest-cart-created' => '已创建带有唯一会话令牌的新访客购物车',
            'select-items-to-remove' => '请选择要移除的项目',
            'select-items-to-move-wishlist' => '请选择要移至心愿单的项目',
            'invalid-or-expired-token' => '购物车令牌无效或已过期。请创建新的购物车。',
            'invalid-token-of-login-user' => '登录用户令牌无效。',
            'booking-note-required' => '餐桌预订需要 booking.note。',
            'booking-date-required' => '当 booking.slot 为格式化的时间范围时，需要 booking.date。',
            'booking-qty-required' => '活动预订需要 booking.qty。',
            'booking-ticket-qty-invalid' => '活动门票数量必须为非负数。',
            'booking-ticket-required' => '活动预订请至少选择一种门票的数量。',
            'booking-slot-unavailable' => '所选时段在指定日期不可用。',
            'invalid-quantity' => '数量必须至少为1',
            'product-id-required' => '需要商品ID',
            'shipping-address-required' => '估算运费需要国家和邮政编码',
        ],

        'token-verification' => [
            'invalid-operation' => '无效操作',
            'invalid-input-data' => '无效的输入数据',
            'token-required' => '需要令牌',
            'invalid-token-format' => '无效的令牌格式',
            'token-not-found-or-expired' => '未找到令牌或令牌已过期',
            'customer-not-found' => '未找到客户',
            'customer-account-suspended' => '客户账户已被暂停',
            'error-verifying-token' => '验证令牌时出错',
            'token-is-valid' => '令牌有效',
            'unauthenticated' => '未通过身份验证。请登录以执行此操作',
        ],

        'forgot-password' => [
            'invalid-operation' => '无效操作',
            'invalid-input-data' => '无效的输入数据',
            'email-required' => '需要电子邮箱',
            'reset-link-sent' => '重置链接已成功发送至您的电子邮箱',
            'email-not-found' => '未找到电子邮箱地址',
            'throttled' => '重置尝试次数过多。请稍候再试。',
            'error-sending-reset-link' => '发送重置链接时发生错误',
        ],

        'reset-password' => [
            'failed' => '无法重置密码，请重试。',
        ],

        'rate-limit' => [
            'too-many-attempts' => '尝试次数过多，请在 :seconds 秒后重试。',
        ],

        'logout' => [
            'invalid-operation' => '无效操作',
            'invalid-input-data' => '无效的输入数据',
            'token-required' => '需要令牌',
            'invalid-token-format' => '无效的令牌格式',
            'logged-out-successfully' => '已成功注销',
            'token-not-found-or-expired' => '未找到令牌或令牌已过期',
            'error-during-logout' => '注销时出错',
            'unauthenticated' => '未通过身份验证。请登录以执行此操作',
        ],

        'address' => [
            'deleted-successfully' => '地址已成功删除',
            'authentication-required' => '需要身份验证令牌',
            'invalid-token' => '令牌无效或已过期',
            'unknown-operation' => '未知操作',
            'address-id-required' => '需要地址ID',
            'address-not-found' => '未找到地址或该地址不属于此客户',
            'retrieved' => '地址已成功获取',
            'fetch-failed' => '未能获取地址：',
            'field-required' => ':field 字段为必填项',
        ],

        'customer-profile' => [
            'authentication-required' => '需要身份验证令牌。请在查询输入中提供令牌',
            'invalid-token' => '令牌无效或已过期',
            'profile-updated' => '客户资料已成功更新',
        ],

        'customer' => [
            'password-mismatch' => '密码和确认密码不匹配',
            'confirm-password-required' => '更改密码时需要确认密码',
            'current-password-required' => '更改密码需要提供当前密码',
            'current-password-incorrect' => '您输入的当前密码不正确',
            'verify-email-to-login' => '您的账户已创建。请验证您的电子邮箱地址后登录。',
            'delete-password-required' => '删除账户需要提供您的密码',
            'delete-password-incorrect' => '您输入的密码不正确',
            'delete-has-active-orders' => '您有待处理或处理中的订单，无法删除账户',
            'unauthenticated' => '未通过身份验证。请登录以执行此操作',
            'id-required' => '需要客户ID',
            'invalid-id-format' => 'ID格式无效。应为类似"/api/admin/customers/1"的IRI格式或数字ID',
            'not-found' => '未找到客户',
            'phone-special-chars-not-allowed' => '手机号码只能包含数字。不允许使用特殊字符',
            'email-already-taken' => '此电子邮箱地址已被其他账户注册',
            'invalid-gender' => '性别值":gender"无效。允许的值为：:valid',
        ],

        'product-review' => [
            'id-required' => '需要产品评价ID',
            'invalid-id-format' => 'ID格式无效。应为类似"/api/shop/reviews/1"的IRI格式或数字ID',
            'not-found' => '未找到ID为":id"的产品评价',
            'item-not-found' => '未找到项目":id"。',
            'product-id-required' => '需要产品ID',
            'product-not-found' => '未找到产品',
            'rating-invalid' => '评分必须介于1到5之间',
            'title-required' => '需要评价标题',
            'comment-required' => '需要评价内容',
            'review-disabled' => '产品评价功能当前已禁用',
            'guest-review-disabled' => '不允许访客评价。请登录后提交评价',
            'owner-required' => '请登录以管理您的评价',
            'not-owned' => '此评价并非由您撰写，无法修改或删除',
            'invalid-attachments' => '附件格式无效。',
            'invalid-product-id-format' => '商品ID格式无效。应为类似"/api/shop/products/1"的IRI格式或数字ID',
        ],

        'customer-review' => [
            'id-required' => '需要客户评价ID',
            'invalid-id-format' => 'ID格式无效。应为类似"/api/shop/customer-reviews/1"的IRI格式或数字ID',
            'not-found' => '未找到ID为":id"的客户评价',
        ],

        'newsletter' => [
            'error-during-subscription' => '目前无法完成订阅。请稍后重试',
        ],

        'contact-us' => [
            'success' => '您的咨询已成功提交。我们会尽快回复您',
            'failed' => '目前无法发送您的咨询。请稍后重试',
            'invalid-input' => '联系表单数据无效',
        ],

        'customer-order' => [
            'id-required' => '需要客户订单ID',
            'invalid-id-format' => 'ID格式无效。应为类似"/api/shop/customer-orders/1"的IRI格式或数字ID',
            'not-found' => '未找到ID为":id"的客户订单',
        ],

        'return' => [
            'not-found' => '未找到退货申请。',
            'disabled' => '此商店未启用退货功能。',
            'invalid-input' => '退货详情缺失或无效。',
            'invalid-resolution-type' => '处理方式必须为"return"或"cancel_items"。',
            'agreement-required' => '您必须接受退货政策。',
            'invalid-order' => '所选订单无效。',
            'invalid-item' => '所选项目不符合退货条件。',
            'qty-exceeds' => '申请的数量超过了可退货数量。',
            'invalid-package-condition' => '包裹状态必须为"open"或"packed"。',
            'invalid-image' => '一个或多个上传文件的文件类型不受支持。',
            'invalid-custom-field' => '未知的自定义字段":id"。',
            'custom-field-required' => '字段":field"为必填项。',
            'invalid-custom-field-value' => '":field"的值不在允许的选项中。',
            'created' => '退货申请已成功创建。',
            'canceled' => '退货申请已成功取消。',
            'already-canceled' => '退货申请已被取消。',
            'cancel-not-allowed' => '此退货申请无法取消。',
            'close-not-allowed' => '此退货申请无法关闭。',
            'reopened' => '退货申请已成功重新打开。',
            'reopen-not-allowed' => '此退货申请无法重新打开。',
            'closed' => '退货申请已成功关闭。',
            'message-sent' => '消息已成功发送。',
            'attachment-not-found' => '未找到消息附件。',
        ],

        'eu-withdrawal' => [
            'not-found' => '未找到撤回声明。',
            'order-not-found' => '找不到该订单或该订单不符合撤回条件。',
            'too-many-attempts' => '尝试次数过多。请在 :seconds 秒后重试。',
            'created' => '撤回声明已成功提交。',
        ],

        'customer-order-shipment' => [
            'id-required' => '需要发货ID',
            'not-found' => '未找到ID为":id"的发货记录，或该记录不属于此客户',
            'item-not-found' => '未找到ID为":id"的发货项目，或该项目不属于此客户',
            'invalid-id-format' => 'ID格式无效。应为类似"/api/shop/customer-order-shipments/1"的IRI格式或数字ID',
        ],

        'cancel-order' => [
            'order-id-required' => '取消订单需要订单ID',
            'not-found' => '未找到ID为":id"的订单，或该订单不属于此客户',
            'cancel-success' => '订单已成功取消',
            'cancel-failed' => '订单无法取消。该订单可能已被处理、发货或取消',
        ],

        'reorder' => [
            'order-id-required' => '重新下单需要订单ID',
            'not-found' => '未找到ID为":id"的订单，或该订单不属于此客户',
            'reorder-success' => '您之前订单中的 :count 个项目已添加到购物车',
            'no-items-added' => '没有项目能添加到购物车。产品可能缺货或不可用',
        ],

        'customer-downloadable-product' => [
            'id-required' => '需要客户可下载产品ID',
            'invalid-id-format' => 'ID格式无效。应为类似"/api/shop/customer-downloadable-products/1"的IRI格式或数字ID',
            'not-found' => '未找到ID为":id"的客户可下载产品',

            'download' => [
                'unauthenticated' => '未授权：需要客户身份验证。',
                'not-found' => '未找到可下载商品。',
                'pending' => '下载待处理。请等待订单开具发票。',
                'limit-exceeded' => '已超出下载次数限制。',
                'no-downloads-remaining' => '没有剩余的下载次数。',
                'file-not-found' => '文件未找到。',
                'link-not-found' => '未找到下载链接。',
            ],
        ],

        'customer-invoice' => [
            'id-required' => '需要客户发票ID',
            'invalid-id-format' => 'ID格式无效。应为类似"/api/shop/customer-invoices/1"的IRI格式或数字ID',
            'not-found' => '未找到ID为":id"的客户发票',
        ],

        'auth' => [
            'no-token-provided' => '未提供身份验证令牌。请在 Authorization 请求头中以"Bearer <token>"的形式提供令牌，或在 input.token 字段中提供',
            'invalid-or-expired-token' => '令牌无效或已过期',
            'request-not-found' => '在上下文中未找到请求',
            'token-required' => 'Authentication token is required. Please provide the token either in the GraphQL mutation input field or in the Authorization header as "Bearer <token>"',
            'unknown-resource' => '未知资源',
            'cannot-update-other-profile' => '未授权：无法更新其他客户的个人资料',
            'refresh-token-invalid' => '刷新令牌无效或已过期。请重新登录。',
            'too-many-login-attempts' => '登录尝试次数过多。请在 :seconds 秒后重试。',
            'too-many-refresh-requests' => '令牌刷新过于频繁。请在 :seconds 秒后重试。',
            'rate-limit-exceeded' => '尝试次数过多。',
            'access-token-expired' => '访问令牌已过期。请刷新后继续。',
            'logged-out-all' => '您已退出所有会话。',
            'session-not-found' => '未找到会话。',
            'session-revoked' => '会话已被撤销。',
        ],

        'upload' => [
            'invalid-base64' => '无效的 base64 编码图片数据',
            'size-exceeds-limit' => '图片大小不得超过5MB',
            'invalid-format' => '无效的图片格式。请提供采用 data URI 方案的 base64 编码图片（data:image/jpeg;base64,...）',
            'failed' => '图片上传失败',
        ],

        'attribute' => [
            'code-already-exists' => '该属性代码已存在',
            'option-id-required' => '直接查询属性选项时需要"attributeId"参数',
            'id-required' => '需要属性ID',
            'invalid-id-format' => 'ID格式无效。应为类似"/api/shop/attributes/1"的IRI格式或数字ID',
            'not-found' => '未找到属性',
        ],

        'attribute-option' => [
            'id-required' => '需要属性选项ID',
            'invalid-id-format' => 'ID格式无效。应为类似"/api/shop/attribute-options/1"的IRI格式或数字ID',
            'not-found' => '未找到属性选项',
        ],

        'channel' => [
            'id-required' => '需要渠道ID',
            'invalid-id-format' => 'ID格式无效。应为类似"/api/shop/channels/1"的IRI格式或数字ID',
            'not-found' => '未找到渠道',
        ],

        'country-state' => [
            'id-required' => '需要国家/地区省份ID',
            'invalid-id-format' => 'ID格式无效。应为类似"/api/shop/country-states/1"的IRI格式或数字ID',
            'not-found' => '未找到国家/地区省份',
            'country-id-required' => '需要国家/地区ID',
        ],

        'country' => [
            'id-required' => '需要国家/地区ID',
            'invalid-id-format' => 'ID格式无效。应为类似"/api/shop/countries/1"的IRI格式或数字ID',
            'not-found' => '未找到国家/地区',
        ],

        'login' => [
            'invalid-credentials' => '电子邮箱或密码无效',
            'account-suspended' => '您的账户已被暂停',
            'account-inactive' => '您的帐户请求已提交，等待管理员批准。',
            'email-not-verified' => '请先验证您的电子邮件。',
            'successful' => '您已成功登录',
            'invalid-request' => '无效的登录请求',
        ],

        'social-login' => [
            'signed-in' => '登录成功。',
            'token-required' => '需要社交登录令牌。',
            'invalid-token' => '社交登录令牌无效或已过期。请重试。',
            'google-not-configured' => '此商店未配置 Google 登录。',
            'wrong-audience' => '此令牌是为其他应用签发的。',
            'email-required' => '提供商未共享电子邮件地址。请改用电子邮件注册。',
            'account-inactive' => '您的帐户请求已提交，等待管理员批准。',
            'provider-not-supported' => '不支持此社交登录提供商。',
            'provider-disabled' => '此社交登录提供商未启用。',
        ],

        'checkout' => [
            'invalid-input' => '结账操作的输入数据无效',
            'billing-address-required' => '需要账单地址',
            'shipping-address-required' => '发货需要收货地址',
            'address-save-failed' => '未能保存地址',
            'address-saved' => '地址已成功保存',
            'shipping-method-required' => '需要配送方式',
            'invalid-shipping-method' => '配送方式无效或不可用',
            'shipping-method-save-failed' => '未能保存配送方式',
            'shipping-method-saved' => '配送方式已成功保存',
            'shipping-method-error' => '保存配送方式时出错',
            'payment-method-required' => '需要付款方式',
            'invalid-payment-method' => '付款方式无效或不可用',
            'payment-method-save-failed' => '未能保存付款方式',
            'payment-method-saved' => '付款方式已成功保存',
            'payment-method-error' => '保存付款方式时出错',
            'order-placed' => '订单已成功提交',
            'order-redirect-required' => '此支付方式需要购物者在支付网关上完成付款。请将其引导至 redirectUrl；网关确认付款后才会创建订单。',
            'order-creation-failed' => '订单创建失败：订单ID为空或订单未保存',
            'order-retrieval-failed' => '未能获取已创建的订单',
            'order-creation-error' => '未能创建订单',
            'cart-empty' => '购物车为空',
            'account-suspended' => '您的账户已被暂停。请联系客服。',
            'account-inactive' => '您的账户处于非活动状态。请联系客服。',
            'minimum-order-not-met' => '最低订单金额为 :amount',
            'guest-checkout-not-allowed' => '请登录后结账。此购物车不支持访客结账。',
            'email-required' => '创建订单需要电子邮箱地址',
            'unknown-operation' => '未知的结账操作',
        ],

        'customer-addresses' => [
            'token-required' => '获取客户地址需要令牌',
            'invalid-or-expired-token' => '令牌无效或已过期',
            'token-validation-failed' => '令牌验证失败',
        ],

        'product' => [
            'not-found' => '未找到产品',
            'not-found-with-sku' => '未找到具有该 SKU 的产品',
            'not-found-with-url-key' => '未找到具有该 URL 键的产品',
            'parameters-required' => '必须至少提供以下参数之一："sku"、"id"、"urlKey"',
            'type' => '产品类型',
            'attribute-family' => '属性族',
            'sku' => 'SKU',
            'name' => '名称',
            'description' => '描述',
            'short-description' => '简短描述',
            'status' => '状态',
            'new' => '新品',
            'featured' => '推荐',
            'price' => '价格',
            'special-price' => '特价',
            'weight' => '重量',
            'cost' => '成本',
            'length' => '长度',
            'width' => '宽度',
            'height' => '高度',
            'color' => '颜色',
            'size' => '尺寸',
            'brand' => '品牌',
            'super-attributes' => '超级属性',
            'id-required' => '需要商品ID',
            'invalid-id-format' => 'ID格式无效。应为类似"/api/shop/products/1"的IRI格式或数字ID',
        ],

        'downloadable-product' => [
            'download-link-not-found' => '下载链接未找到或已过期',
            'purchased-link-not-found' => '购买的链接未找到',
            'file-not-found' => '文件未找到',
            'download-successful' => '文件已准备好下载',
            'token-required' => '需要下载令牌',
            'invalid-token' => '下载令牌无效或已过期',
            'token-expired' => '下载令牌已过期。请生成新令牌',
            'access-denied' => '访问被拒绝：您无权下载此文件',
            'redirect-external-url' => '重定向到外部下载URL',
            'file-error' => '处理您的下载请求时发生错误',
            'unauthorized-access' => '对下载资源的未授权访问',
            'unauthenticated' => '未授权：需要客户身份验证',
            'link-id-required' => '需要 downloadableLinkPurchasedId',
            'link-not-found' => '未找到下载链接',
            'not-owner' => '未授权：此下载链接不属于您',
            'pending' => '下载待处理。请等待订单开具发票。',
            'limit-exceeded' => '已超出下载次数限制',
            'no-downloads-left' => '没有剩余的下载次数',
        ],

        'booking-slot' => [
            'id-required' => '需要预订产品ID。',
            'date-required' => '需要日期。',
            'product-not-found' => '未找到预订产品。',
            'invalid-type' => '不支持此预订类型。',
            'invalid-time-range' => '时间范围无效。',
            'invalid-time-format' => '时间格式无效。',
        ],

        'resolver' => [
            'no-resolve-info' => '上下文中没有可用的 GraphQL ResolveInfo。无法确定模型类。',
            'unknown-resource-class' => '无法从 GraphQL 返回类型确定资源类',
        ],

        'docs' => [
            'openapi-unavailable' => '无法实例化 OpenAPI Factory',
        ],

        'locale' => [
            'id-required' => '需要语言区域ID',
            'invalid-id-format' => 'ID格式无效。应为类似"/api/shop/locales/1"的IRI格式或数字ID',
            'not-found' => '未找到语言区域',
        ],

        'category' => [
            'id-required' => '需要分类ID',
            'invalid-id-format' => 'ID格式无效。应为类似"/api/shop/categories/1"的IRI格式或数字ID',
            'not-found' => '未找到分类',
        ],

        'compare-item' => [
            'id-required' => '比较项目ID是必需的',
            'invalid-id-format' => '无效的ID格式。预期的IRI格式如"/api/shop/compare-items/1"或数字ID',
            'not-found' => '比较项目未找到',
            'product-id-required' => '产品ID是必需的',
            'customer-id-required' => '客户ID是必需的',
            'product-not-found' => '产品未找到',
            'customer-not-found' => '客户未找到',
            'already-exists' => '该产品已在您的比较列表中',
            'delete-all-success' => '所有比较项目已成功移除',
        ],

        'wishlist' => [
            'id-required' => '需要心愿单项目ID',
            'invalid-id-format' => 'ID格式无效。应为类似"/api/shop/wishlists/1"的IRI格式或数字ID',
            'not-found' => '未找到心愿单项目',
            'product-id-required' => '需要产品ID',
            'customer-id-required' => '需要客户ID',
            'product-not-found' => '未找到产品',
            'product-disabled' => '此产品当前已禁用',
            'customer-not-found' => '未找到客户',
            'already-exists' => '此产品已在您的心愿单中',
            'added' => '项目已成功添加到心愿单',
            'removed' => '项目已成功从心愿单中移除',
            'product-removed' => '产品已被移除',
            'wishlist-item-id-required' => '需要心愿单项目ID',
            'invalid-quantity' => '数量必须大于0',
            'move-to-cart-missing-options' => '产品缺少必填选项。请手动配置',
            'moved-to-cart-success' => '项目已成功移至购物车',
            'delete-all-success' => '所有心愿单项目已成功移除',
        ],

        'gdpr' => [
            'disabled' => 'GDPR 数据请求已禁用。请在后台配置中启用 GDPR。',
            'unauthenticated' => '您必须登录才能管理 GDPR 数据请求。',
            'not-found' => '未找到 GDPR 数据请求',
            'type-required' => '需要请求类型',
            'type-invalid' => '请求类型必须为"delete"或"update"',
            'message-required' => '需要消息内容',
            'raised' => '您的 GDPR 数据请求已成功提交。',
            'revoked' => '您的 GDPR 数据请求已成功撤销。',
            'revoke-not-allowed' => '此 GDPR 数据请求已无法撤销。',
            'deleted' => '您的 GDPR 数据请求已成功删除。',
        ],

        'section' => [
            'id-required' => '需要版块ID',
            'invalid-id-format' => 'ID格式无效。应为类似"/api/shop/sections/1"的IRI格式或数字ID',
            'not-found' => '未找到版块',
        ],

        'install' => [
            'starting' => '正在开始安装 API Platform...',
            'completed-success' => '✓ API Platform 安装已成功完成！',
            'failed' => '✗ 安装失败：',

            'provider-already-registered' => 'ℹ Service Provider 已注册',
            'provider-not-registered' => '⚠ 无法自动注册服务提供者。请手动将 :provider 添加到 bootstrap/providers.php。',
            'provider-registered' => '✓ 服务提供者已在 bootstrap/providers.php 中注册',
            'provider-file-not-found' => '未找到文件：:file',
            'provider-permission-denied' => '权限被拒绝：无法写入 :file。请检查文件权限。',

            'config-already-exists' => 'ℹ 配置文件已存在',
            'config-published' => '✓ API Platform 配置已发布到 config/',
            'config-source-not-found' => '未找到源配置文件：:file',
            'config-permission-denied' => '权限被拒绝：无法写入 :directory。请检查目录权限。',

            'translatable-not-found' => 'ℹ 未找到 TranslatableModel - 跳过',
            'translatable-already-abstract' => 'ℹ TranslatableModel 已经是抽象类',
            'translatable-made-abstract' => '✓ TranslatableModel 已设为抽象类',
            'translatable-permission-denied' => '权限被拒绝：无法写入 :file。请检查文件权限。',

            'providers-already-registered' => 'ℹ API Platform 提供者已注册',
            'providers-registered' => '✓ API Platform 提供者已在 bootstrap/app.php 中注册',
            'providers-file-not-found' => '未找到文件：:file',
            'providers-permission-denied' => '权限被拒绝：无法写入 :file。请检查文件权限。',
            'providers-not-found' => '在 bootstrap/app.php 中找不到 Application 创建代码',

            'assets-published' => '✓ 扩展包资源已发布',
            'running-migrations' => '正在运行数据库迁移...',
            'migrations-completed' => '✓ 数据库迁移已完成',
            'clearing-caches' => '正在清除并优化缓存...',
            'caches-optimized' => '✓ 缓存已清除并优化',
            'generating-api-key' => '正在生成 API 密钥...',
            'api-key-generated' => '✓ API 密钥已成功生成',
            'api-key-already-exists' => 'ℹ 默认商店 API 密钥已存在 - 跳过生成',

            'migrations-error' => '数据库迁移失败。',
            'migrations-error-running' => '运行数据库迁移时出错：:error',
            'cache-clear-error' => '清除缓存失败。',
            'cache-optimize-error' => '优化失败。',
            'cache-error' => '清除并优化缓存时出错：:error',
            'api-key-generation-error' => 'API 密钥生成失败。',
            'api-key-error' => '生成 API 密钥时出错：:error',

            'key-management-required' => ':action 操作需要 --key',
            'key-rotation-error' => '轮换密钥时出错：:error',
            'key-deactivation-error' => '停用密钥时出错：:error',
            'key-not-found' => '未找到 API 密钥：:identifier',

            'maintenance-starting' => '🔄 正在开始 API 密钥维护...',
            'maintenance-complete' => '✅ API 密钥维护已完成',
            'cleanup-expired-keys' => '🧹 正在清理过期密钥...',
            'cleanup-expired-none' => '   ℹ️ 没有需要清理的过期密钥',
            'invalidate-deprecated' => '⚠️ 正在使已弃用的密钥失效...',
            'invalidate-deprecated-none' => '   ℹ️ 没有需要失效的已弃用密钥',
            'notify-expiring' => '📧 正在发送到期通知...',
            'notify-expiring-none' => '   ℹ️ 没有需要通知的密钥',

            'key-rotated-success' => '✅ 密钥已成功轮换！',
            'key-deactivated-success' => '✅ 密钥已成功停用！',
            'deactivation-cancelled' => '已取消停用。',
            'cleanup-cancelled' => '已取消清理。',

            'key-status-title' => '密钥状态：:name',
            'key-status-active' => '启用：:status',
            'key-status-usable' => '可用：:status',
            'key-status-expired' => '已过期：:status',
            'key-status-deprecated' => '已弃用：:status',
            'key-status-expires-at' => '到期时间：:expires',
            'key-status-days-until-expiry' => '距到期天数：:days',
            'key-status-last-used' => '上次使用：:date',
            'key-status-rotated-from' => '轮换自：:key',
            'key-status-rotated-keys' => '由此密钥轮换出的密钥数：:count',

            'policy-compliance-summary' => 'API 密钥轮换策略合规摘要',
            'available-actions' => '可用操作：rotate、deactivate、cleanup、status、expiring、unused、summary',

            'asset-linked-success' => '✓ API Platform 资源已成功链接',
            'symlink-create-failed' => '无法创建符号链接，改为复制资源...',
            'asset-copy-warning' => '警告：无法链接或复制 API Platform 资源。可能需要手动设置。',
            'asset-copied-success' => '✓ API Platform 资源已成功复制',

            'key-name-required' => '店面密钥名称不能为空。',
            'key-generated-success' => '店面密钥已成功生成！',
            'key-details' => '密钥详情：',
            'key-secure-warning' => '请妥善保管此密钥！它将用于 X-STOREFRONT-KEY 请求头。',
            'key-share-warning' => '请勿公开分享此密钥或将其提交到版本控制系统。',

            'key-already-exists' => '名称为 \':name\' 的店面密钥已存在。',
            'cleanup-success' => '✅ 已清理 :count 个过期密钥',
            'confirm-cleanup' => '这将软删除所有过期密钥。是否继续？',
            'confirm-deactivate' => '确定要停用密钥 :name 吗？',

            'old-key' => '旧密钥：:name',
            'old-key-id' => '旧密钥ID：:id',
            'deprecation-date' => '弃用日期：:date',
            'new-key' => '新密钥：:name',
            'new-key-id' => '新密钥ID：:id',
            'new-key-value' => '新密钥值：:key',
            'expires-at' => '到期时间：:date',
            'invalid-action' => '无效操作：:action',
            'key-active' => '启用：:status',
            'key-usable' => '可用：:status',
            'key-expired' => '已过期：:status',
            'key-deprecated' => '已弃用：:status',
            'key-field-id' => 'ID',
            'key-field-name' => '名称',
            'key-field-key' => '密钥',
            'key-field-rate-limit' => '速率限制',
            'key-field-status' => '状态',
            'key-days' => ' 天',
            'key-never' => '从不',
            'key-requests-minute' => ' 次请求/分钟',
            'key-unlimited' => '无限制',
            'rate-limit-exceeded' => '请求的速率限制超过了允许的最大值 :max。将设置为 :max 次请求/分钟。',

            'vendor-path-not-found' => '未找到 API Platform vendor 路径：:path',
            'assets-already-linked' => 'API Platform 资源已链接至：:path',
            'no-keys-expiring' => '未来 :days 天内没有即将到期的密钥',
            'keys-expiring-title' => '未来 :days 天内即将到期的密钥：',
            'key-display-format' => '• :name（ID：:id）',
            'key-expires-display' => '  到期：:date（剩余 :days 天）',
            'no-unused-keys' => '未找到未使用的密钥（> :days 天）',
            'unused-keys-title' => '未使用的密钥（> :days 天）：',
            'key-last-used-display' => '  上次使用：:date',
            'total-keys' => '密钥总数：:count',
            'valid-keys' => '有效密钥：:count',
            'expired-keys' => '过期密钥：:count',
            'deprecated-keys' => '已弃用密钥：:count',
            'keys-expiring-soon' => '即将到期的密钥（7 天）：:count',
            'unused-keys-summary' => '未使用的密钥（90 天）：:count',
            'recently-rotated' => '最近轮换（30 天）：:count',
            'cleanup-success-message' => '   ✅ 已清理 :count 个过期密钥',
            'invalidate-success-message' => '   ✅ 已使 :count 个已弃用密钥失效',
            'notify-success-message' => '   ✅ 已发送 :count 条到期通知',
            'notify-failed-message' => '   ⚠️ 未能发送关于 :key 的通知：:error',
            'publish-assets-warning' => '警告：无法发布扩展包资源。:error',
            'env-file-not-found' => '未找到 .env 文件。请在安装前创建它。',
            'env-permission-denied' => '.env 文件不可写。请检查其权限。',
            'env-config-saved' => '✓ API Platform 环境配置已成功保存',
            'api-endpoints' => 'API 端点：',
            'api-documentation' => 'API 文档：:url',
            'api-landing-page' => 'API 首页：:url',
            'graphql-playground' => 'GraphQL Playground：:url',
            'rest-api-storefront' => 'REST API 店面 Swagger：:url',
            'rest-api-admin' => 'REST API 后台 Swagger：:url',
            'generating-jwt-keys' => '正在为管理 API 令牌生成 JWT 签名密钥...',
            'jwt-keys-generated' => '✓ JWT 签名密钥已就绪',
            'jwt-keys-warning' => '警告：无法生成 JWT 签名密钥。请手动运行 php artisan bagisto-api:jwt-keys。:error',
            'admin-login' => '管理 API 登录：:url',
            'admin-api-tokens' => '管理 API 令牌（服务器集成）：:url',
            'key-rotation-invalid-key' => '无法轮换无效或已过期的密钥',
            'env-file-not-found' => '未找到.env文件',
            'env-permission-denied' => '权限被拒绝：无法写入.env文件。请检查文件权限。',
        ],

        'attribute-translation' => [
            'id-required' => '需要属性翻译ID',
            'invalid-id-format' => 'ID格式无效。应为类似"/api/shop/attribute-translations/1"的IRI格式或数字ID',
            'not-found' => '未找到属性翻译',
        ],

        'currency' => [
            'id-required' => '需要货币ID',
            'invalid-id-format' => 'ID格式无效。应为类似"/api/shop/currencies/1"的IRI格式或数字ID',
            'not-found' => '未找到货币',
        ],

        'customer-address' => [
            'id-required' => '需要客户地址ID',
            'invalid-id-format' => 'ID格式无效。应为类似"/api/shop/customer-addresses/1"的IRI格式或数字ID',
        ],

        'customer-order-shipment-item' => [
            'id-required' => '需要发货项目ID',
            'invalid-id-format' => 'ID格式无效。应为类似"/api/shop/customer-order-shipment-items/1"的IRI格式或数字ID',
            'not-found' => '未找到发货项目',
        ],

        'page' => [
            'id-required' => '需要页面ID',
            'invalid-id-format' => 'ID格式无效。应为类似"/api/shop/pages/1"的IRI格式或数字ID',
            'not-found' => '未找到页面',
        ],

        'page-translation' => [
            'id-required' => '需要页面翻译ID',
            'invalid-id-format' => 'ID格式无效。应为类似"/api/shop/page-translations/1"的IRI格式或数字ID',
            'not-found' => '未找到页面翻译',
        ],

        'product-bundle-option' => [
            'id-required' => '需要捆绑选项ID',
            'invalid-id-format' => 'ID格式无效。应为类似"/api/shop/product-bundle-options/1"的IRI格式或数字ID',
            'not-found' => '未找到捆绑选项',
        ],

        'product-bundle-option-product' => [
            'id-required' => '需要捆绑选项商品ID',
            'invalid-id-format' => 'ID格式无效。应为类似"/api/shop/product-bundle-option-products/1"的IRI格式或数字ID',
            'not-found' => '未找到捆绑选项商品',
        ],

        'product-downloadable-link' => [
            'id-required' => '需要下载链接ID',
            'invalid-id-format' => 'ID格式无效。应为类似"/api/shop/product-downloadable-links/1"的IRI格式或数字ID',
            'not-found' => '未找到下载链接',
        ],

        'product-downloadable-sample' => [
            'id-required' => '需要下载样例ID',
            'invalid-id-format' => 'ID格式无效。应为类似"/api/shop/product-downloadable-samples/1"的IRI格式或数字ID',
            'not-found' => '未找到下载样例',
        ],

        'product-grouped-product' => [
            'id-required' => '需要组合商品ID',
            'invalid-id-format' => 'ID格式无效。应为类似"/api/shop/product-grouped-products/1"的IRI格式或数字ID',
            'not-found' => '未找到组合商品',
        ],

        'product-images' => [
            'id-required' => '需要商品图片ID',
            'invalid-id-format' => 'ID格式无效。应为类似"/api/shop/product-images/1"的IRI格式或数字ID',
            'not-found' => '未找到商品图片',
        ],

        'product-videos' => [
            'id-required' => '需要商品视频ID',
            'invalid-id-format' => 'ID格式无效。应为类似"/api/shop/product-videos/1"的IRI格式或数字ID',
            'not-found' => '未找到商品视频',
        ],

        'theme-customization' => [
            'id-required' => '需要主题定制ID',
            'invalid-id-format' => 'ID格式无效。应为类似"/api/shop/theme-customizations/1"的IRI格式或数字ID',
            'not-found' => '未找到主题定制',
        ],

        'unknown-resource' => [
            'id-required' => '需要资源ID',
            'not-found' => '未找到资源',
        ],
    ],

    'admin' => [
        'profile' => [
            'unauthenticated' => '您尚未通过身份验证。',
        ],

        'dashboard' => [
            'invalid-type' => '未知的仪表板统计类型。有效类型：over-all, today, stock-threshold-products, total-sales, top-selling-products, top-customers。',
        ],

        'rma' => [
            'not-found' => '未找到退货申请。',
            'invalid-input' => '退货详情缺失或无效。',
            'invalid-order' => '所选订单无效。',
            'invalid-item' => '所选项目不符合退货条件。',
            'invalid-resolution-type' => '处理方式必须为"return"或"cancel_items"。',
            'qty-exceeds' => '申请的数量超过了可退货数量。',
            'created' => '退货申请已成功创建。',
            'status-updated' => '退货状态已成功更新。',
            'invalid-status' => '退货状态无效。',
            'reopened' => '退货申请已成功重新打开。',
            'reopen-not-allowed' => '此退货申请无法重新打开。',
            'refund-failed' => '无法为此退货创建退款。',
            'message-sent' => '消息已成功发送。',
            'no-permission' => '您没有执行此操作的权限。',
            'reason-not-found' => '未找到退货原因。',
            'reason-deleted' => '退货原因已成功删除。',
            'status-not-found' => '未找到退货状态。',
            'status-deleted' => '退货状态已成功删除。',
            'status-default-undeletable' => '默认退货状态无法删除。',
            'rule-not-found' => '未找到退货规则。',
            'rule-deleted' => '退货规则已成功删除。',
            'custom-field-not-found' => '未找到退货自定义字段。',
            'custom-field-deleted' => '退货自定义字段已成功删除。',
            'custom-field-options-required' => '此字段类型至少需要一个选项。',
            'mass-indices-required' => '至少需要一个ID。',
            'mass-delete-success' => '所选项目已删除。',
            'mass-update-success' => '所选项目已更新。',
            'mass-value-invalid' => '状态值必须为0或1。',
            'image-not-found' => '未找到退货图片。',
            'attachment-not-found' => '未找到消息附件。',
        ],

        'eu-withdrawal' => [
            'not-found' => '未找到撤回声明。',
            'no-permission' => '您没有管理欧盟撤回的权限。',
            'declined' => '撤回已标记为拒绝。',
            'refunded' => '撤回已标记为已退款。',
            'confirmation-resent' => '确认邮件已成功重新发送。',
            'confirmation-failed' => '无法发送确认邮件。',
        ],

        'reporting' => [
            'invalid-entity' => '未知的报表实体。',
            'invalid-type' => '此实体的报表统计类型未知。',
            'export-format-unsupported' => '仅支持 csv、xls 和 xlsx 导出格式。',
        ],

        'configuration' => [
            'unauthenticated' => '您尚未通过身份验证。',
            'no-permission' => '您没有更新配置的权限。',
            'slug-required' => '需要查询参数 `slug`。',
            'slug-not-found' => '未针对给定的 slug 注册任何配置部分。',
            'values-required' => '`values` 下至少需要一个值。',
            'scope-escape' => '键 `:key` 不在所请求的 slug 子树范围内。',
            'unknown-field' => '键 `:key` 不是已注册的配置字段。',
            'validation-failed' => '一个或多个配置值未通过验证。',
            'file-upload-rest-only' => '字段 `:field` 的二进制文件上传仅支持通过 REST multipart 端点进行，不支持 GraphQL。',
            'custom-view-readonly' => '字段 `:field` 是通过 blade 自定义渲染的字段，无法通过 API 更新。',
            'update-success' => '配置已成功更新。',
        ],

        'customer' => [
            'not-found' => '未找到客户。',
            'no-permission' => '您没有执行此操作的权限。',
            'has-active-orders' => '该客户有待处理或处理中的订单，无法删除。',
            'deleted' => '客户已成功删除。',
            'mass-delete-indices-required' => '至少需要一个客户ID。',
            'mass-delete-success' => '客户已删除。',
            'mass-update-indices-required' => '至少需要一个客户ID。',
            'mass-update-value-invalid' => '状态值必须为0或1。',
            'mass-update-success' => '客户状态已更新。',

            'address' => [
                'not-found' => '未找到地址。',
                'not-owned' => '此地址不属于指定的客户。',
                'customer-id-required' => '需要 customerId 来识别地址所有者。',
                'deleted' => '地址已成功删除。',
            ],

            'note' => [
                'empty' => '需要备注内容。',
                'created' => '备注已成功添加。',
            ],

            'review' => [
                'not-found' => '未找到评价。',
                'no-permission' => '您没有执行此操作的权限。',
                'invalid-status' => '状态必须为以下之一：pending、approved、disapproved。',
                'updated' => '评价已成功更新。',
                'deleted' => '评价已成功删除。',
                'mass-delete-indices-required' => '至少需要一个评价ID。',
                'mass-delete-success' => '评价已删除。',
                'mass-update-indices-required' => '至少需要一个评价ID。',
                'mass-update-value-invalid' => '状态值必须为以下之一：pending、approved、disapproved。',
                'mass-update-success' => '评价状态已更新。',
            ],

            'group' => [
                'not-found' => '未找到客户组。',
                'deleted' => '客户组已成功删除。',
                'is-system' => '系统客户组无法删除。',
                'has-customers' => '该客户组下有关联客户，无法删除。',
                'system-code-immutable' => '系统客户组的代码无法更改。',
                'system-flag-immutable' => '系统客户组的 is_user_defined 标志无法更改。',
                'mass-delete-indices-required' => '至少需要一个客户组ID。',
                'mass-delete-success' => '客户组已删除。',
            ],

            'gdpr' => [
                'not-found' => '未找到 GDPR 请求。',
                'no-permission' => '您没有执行此操作的权限。',
                'deleted' => 'GDPR 请求已成功删除。',
                'invalid-status' => '状态必须为以下之一：pending、processing、declined、approved、revoked。',
                'no-changes' => '至少需要提供状态或消息之一。',
                'already-processed' => '此 GDPR 请求已处理，无法再次执行。',

                'process' => [
                    'approved' => 'GDPR 请求已批准。',
                    'deleted' => 'GDPR 请求已批准，客户已删除。',
                ],
            ],
        ],

        'order' => [
            'not-found' => '未找到订单。',

            'reorder' => [
                'success' => '重新下单成功。已创建新的草稿购物车。',
                'cannot-reorder' => '此订单无法重新下单（访客订单，或一个或多个项目已不可售）。',
                'failed' => '重新下单失败。请重试。',
                'guest-not-supported' => '访客订单不支持重新下单。',
                'items-not-saleable' => '此订单中的一个或多个项目已无法购买。',
                'no-permission' => '您没有创建订单的权限。',
                'disabled-in-settings' => '商店设置中已禁用管理员重新下单。',
            ],

            'actions' => [
                'cancel' => [
                    'closed' => '已关闭的订单无法取消。',
                    'fraud' => '欺诈订单无法取消。',
                    'nothing-to-cancel' => '此订单没有可取消的内容。',
                    'already-processed' => '此订单已开具发票或已发货，无法再取消。您可以改为发起退款。',
                    'no-permission' => '您没有取消订单的权限。',
                    'success' => '订单已成功取消。',
                    'failed' => '无法取消订单。',
                ],

                'comment' => [
                    'empty' => '需要评论内容。',
                    'success' => '评论已添加到订单。',
                    'failed' => '无法添加评论。',
                ],

                'invoice' => [
                    'closed' => '已关闭的订单无法开具发票。',
                    'fraud' => '欺诈订单无法开具发票。',
                    'nothing-to-invoice' => '此订单没有可开具发票的内容。',
                    'already-invoiced' => '此订单已生成发票。',
                    'paypal-standard-blocked' => '通过 PayPal Standard 支付的订单无法创建发票。',
                    'no-permission' => '您没有创建发票的权限。',
                    'items-required' => '至少需要一个数量为正数的项目。',
                    'qty-exceeds' => 'SKU :sku 的请求数量（:requested）超过了可用数量（:available）。',
                    'not-found' => '未找到发票。',
                    'success' => '发票已成功创建。',
                    'failed' => '无法创建发票。',
                    'pdf-failed' => '无法生成发票 PDF。',
                    'email-invalid' => '需要有效的收件人电子邮箱。',
                    'duplicate-sent' => '发票邮件已发送至 :email。',
                    'mass-status-indices-required' => '至少需要一个发票ID。',
                    'mass-status-value-invalid' => '状态必须为以下之一：pending、paid、overdue。',
                    'mass-status-success' => '发票状态已成功更新。',
                ],

                'shipment' => [
                    'closed' => '已关闭的订单无法发货。',
                    'fraud' => '欺诈订单无法发货。',
                    'nothing-to-ship' => '此订单没有可发货的内容。',
                    'no-permission' => '您没有订单发货的权限。',
                    'source-required' => '需要库存来源。',
                    'items-required' => '至少需要一个数量为正数的项目。',
                    'qty-exceeds' => 'SKU :sku 的请求数量（:requested）超过了可用数量（:available）。',
                    'inventory-insufficient' => '所选来源中 SKU :sku 的库存不足。',
                    'not-found' => '未找到发货记录。',
                    'success' => '发货记录已成功创建。',
                    'failed' => '无法创建发货记录。',
                ],

                'refund' => [
                    'closed' => '已关闭的订单无法退款。',
                    'fraud' => '欺诈订单无法退款。',
                    'nothing-to-refund' => '此订单已没有可退款的内容。',
                    'no-permission' => '您没有创建退款的权限。',
                    'qty-exceeds' => 'SKU :sku 的请求数量（:requested）超过了可用数量（:available）。',
                    'amount-zero' => '计算出的退款金额为零。请调整数量、运费或调整金额。',
                    'amount-exceeds-max' => '退款金额（:amount）超过了最大可退款金额（:max）。',
                    'not-found' => '未找到退款。',
                    'success' => '退款已成功创建。',
                    'failed' => '无法创建退款。',
                ],
            ],
        ],

        'appearance' => [
            'no-permission' => '您没有管理外观的权限。',

            'theme' => [
                'not-found' => '未找到主题。',
                'not-installed' => '此主题未安装。',
                'channel-ids-required' => '至少需要一个渠道。',
                'unknown-channel' => '一个或多个渠道不存在。',
                'activated' => '主题已成功激活。',
            ],

            'section' => [
                'not-found' => '未找到版块。',
                'invalid-payload' => '不支持的版块数据。',
                'options-required' => '需要选项。',
                'status-required' => '需要状态。',
                'section-ids-required' => '至少需要一个版块。',
                'reorder-single-scope' => '每次只能在一个主题和渠道内对版块重新排序。',
                'reorder-incomplete' => '重新排序需要提供该主题和渠道的所有版块，并按其应呈现的顺序排列。',
                'unknown-section' => '一个或多个版块不存在。',
                'file-required' => '需要文件。',
                'created' => '版块已成功创建。',
                'updated' => '版块已成功更新。',
                'deleted' => '版块已成功删除。',
                'draft-saved' => '版块更改已成功暂存。',
                'status-staged' => '版块状态已成功暂存。',
                'order-staged' => '版块顺序已成功暂存。',
                'duplicated' => '版块已成功复制。',
                'published' => '版块更改已成功发布。',
                'discarded' => '版块更改已成功放弃。',
                'media-uploaded' => '媒体已成功上传。',
            ],
        ],

        'sales' => [
            'no-permission' => '您没有查看此销售资源的权限。',

            'invoice' => [
                'not-found' => '未找到发票。',
                'no-permission' => '您没有查看发票的权限。',
            ],

            'shipment' => [
                'not-found' => '未找到发货记录。',
                'no-permission' => '您没有查看发货记录的权限。',
            ],

            'refund' => [
                'not-found' => '未找到退款。',
                'no-permission' => '您没有查看退款的权限。',
                'export-format-unsupported' => '仅支持 csv、xls 和 xlsx 导出。',
            ],

            'transaction' => [
                'not-found' => '未找到交易。',
                'no-permission' => '您没有查看交易的权限。',

                'create' => [
                    'invoice-required' => 'invoiceId 字段为必填项。',
                    'payment-method-required' => 'paymentMethod 字段为必填项。',
                    'amount-invalid' => 'amount 字段为必填项且必须为数字。',
                    'invoice-missing' => '找不到指定的发票。',
                    'already-paid' => '此发票已全额支付。',
                    'amount-exceeds' => '支付金额超过了发票总计。',
                    'amount-zero' => '支付金额必须大于零。',
                ],
            ],

            'booking' => [
                'not-found' => '未找到预订。',
                'no-permission' => '您没有查看预订的权限。',
            ],

            'export' => [
                'format-unsupported' => '仅支持 csv、xls 和 xlsx 导出。',
            ],
        ],

        'cart' => [
            'not-found' => '未找到购物车。',
            'not-draft' => '无法通过管理 API 修改此购物车。',
            'product-required' => '需要 productId。',
            'product-not-found' => '未找到产品。',
            'booking-unsupported' => '预订产品无法添加到后台草稿订单中。预订购买必须通过客户店面进行。',
            'product-not-saleable' => '此产品不可销售（缺货或已禁用），无法添加到订单中。',
            'qty-required' => '需要 qty，且必须为 itemId => 数量 形式的非空对象。',
            'cart-item-required' => '需要 cartItemId。',
            'item-added' => '项目已添加到购物车。',
            'item-add-failed' => '无法将项目添加到购物车。',
            'item-updated' => '购物车项目已更新。',
            'item-update-failed' => '无法更新购物车项目。',
            'item-removed' => '项目已从购物车中移除。',
            'item-remove-failed' => '无法移除购物车项目。',
            'address-saved' => '地址已保存。',
            'address-required' => '需要账单地址。',
            'address-incomplete' => '保存地址需要 :field。',
            'coupon-code-required' => '需要优惠券代码。',
            'coupon-applied' => '优惠券已应用。',
            'coupon-already-applied' => '此购物车已应用该优惠券。',
            'coupon-not-found' => '未找到优惠券或优惠券未激活。',
            'coupon-removed' => '优惠券已移除。',
            'coupon-error' => '应用优惠券时出错。',
            'unknown-error' => '出现错误。',
            'draft-created' => '草稿购物车已创建。',
            'draft-failed' => '无法创建草稿购物车。',
            'addresses-required-for-shipping' => '选择配送方式前必须先保存地址。',
            'shipping-required-for-payment' => '选择支付方式前必须先选择配送方式。',
            'shipping-method-required' => '需要配送方式。',
            'shipping-method-saved' => '配送方式已保存。',
            'shipping-method-failed' => '无法保存配送方式。',
            'payment-method-required' => '需要支付方式。',
            'payment-method-saved' => '支付方式已保存。',
            'payment-method-failed' => '无法保存支付方式。',

            'place-order' => [
                'success' => '订单已成功提交。',
                'failed' => '无法提交订单。',
                'error' => '无法提交订单：购物车存在错误。',
                'minimum-order-error' => '最低订单金额为 :amount。请在下单前添加更多项目。',
                'empty-cart' => '购物车为空 — 请在下单前添加项目。',
                'addresses-required' => '下单前必须保存账单地址和收货地址。',
                'shipping-required' => '下单前必须选择配送方式。',
                'payment-required' => '下单前必须选择支付方式。',
                'payment-method-unsupported' => '后台下单仅支持 cashondelivery 和 moneytransfer。',
            ],
        ],

        'product' => [
            'not-found' => '未找到产品。',

            'mass-delete-success' => '产品已成功删除。',
            'mass-update-status-success' => '产品状态已成功更新。',
            'indices-required' => 'indices 字段为必填项，且必须为非空数组。',
            'indices-invalid' => 'indices 字段必须为整数产品ID数组。',
            'value-invalid' => 'value 字段为必填项，且必须为0或1。',
            'mass-delete-failed' => '一个或多个产品无法删除。',
            'no-permission' => '您没有管理产品的权限。',

            'copy-success' => '产品已成功复制。',
            'copy-failed' => '复制产品失败。',
            'copy-variant-not-supported' => '可配置产品的变体无法复制。请改为复制父级可配置产品。',

            'create' => [
                'created' => '产品已成功创建。',
                'create-failed' => '创建产品失败。',
                'sku-required' => 'sku 字段为必填项。',
                'sku-unique' => '该 sku 已被使用。',
                'sku-invalid' => 'sku 只能包含字母、数字和连字符（例如"sp-001"）。',
                'attribute-family-required' => 'attribute_family_id 字段为必填项。',
                'attribute-family-not-found' => '所选属性族不存在。',
                'type-required' => 'type 字段为必填项。',
                'type-not-yet-supported' => '此 API 不支持产品类型":type"。允许的类型：simple、virtual、downloadable、grouped、bundle、configurable、booking。',
                'super-attributes-required' => '当 type=configurable 时，super_attributes 字段为必填项。请提供属性代码（或ID）到 option_ids 的映射，例如 { "color": [1,2], "size": [4,5] }。',
                'super-attributes-invalid' => 'super_attributes 字段必须为属性代码（或ID）到非空 option_ids 列表的非空映射。',
                'no-permission' => '您没有创建产品的权限。',
            ],

            'update' => [
                'updated' => '产品已成功更新。',
                'update-failed' => '更新产品失败。',
                'id-required' => '需要产品ID。',
                'url-key-required' => 'url_key 字段不能为空。',
                'url-key-unique' => '该 url_key 已被其他产品使用。',
                'special-price-invalid' => 'special_price 必须为小于 price 的数字。',
                'special-price-date-range-invalid' => 'special_price_to 必须等于或晚于 special_price_from。',
                'boolean-field-invalid' => ':field 字段必须为0或1。',
                'categories-invalid' => 'categories 字段必须为分类ID数组。',
                'channels-invalid' => 'channels 字段必须为渠道ID数组。',
                'super-attributes-cannot-change' => '产品创建后无法更改 super_attributes。',
                'sub-resource-stripped-images' => '图片必须通过 POST /api/admin/catalog/products/{id}/images 进行管理。images 字段已被忽略。',
                'sub-resource-stripped-inventories' => '库存必须通过 PUT /api/admin/catalog/products/{id}/inventories 进行管理。inventories 字段已被忽略。',
                'sub-resource-stripped-customer-group-prices' => '客户组价格必须通过 /api/admin/catalog/products/{id}/customer-group-prices 进行管理。customer_group_prices 字段已被忽略。',
                'translations-single-locale' => '仅更新了所请求的语言区域。要更新语言区域 :locales，请针对每个语言区域使用 ?locale=<code> 单独发送请求。',
            ],

            'delete' => [
                'deleted' => '产品已成功删除。',
                'delete-failed' => '删除产品失败。',
            ],

            'image' => [
                'uploaded' => '产品图片已成功上传。',
                'updated' => '产品图片已成功更新。',
                'update-empty' => '请发送要更新的替代文本或位置。',
                'reordered' => '产品图片已成功重新排序。',
                'deleted' => '产品图片已成功删除。',
                'image-required' => '需要图片文件（multipart 字段"image"）。',
                'image-invalid-type' => '图片类型必须为以下之一：bmp、jpeg、jpg、png、webp。',
                'image-too-large' => '图片过大。允许的最大大小为 4 MB。',
                'not-found' => '未找到产品图片。',
                'no-permission' => '您没有管理产品图片的权限。',
                'upload-failed' => '上传产品图片失败。',
                'reorder-failed' => '重新排序产品图片失败。',
                'delete-failed' => '删除产品图片失败。',
                'order-required' => '"order"数组为必填项且不能为空。',
                'order-invalid' => '"order"中的每个条目都必须是包含 id 和 position 的对象。',
                'id-not-on-product' => '图片 :id 不属于此产品。',
                'graphql-upload-unsupported' => '不支持通过 GraphQL 上传图片。请使用 multipart/form-data 调用 POST /api/admin/catalog/products/{productId}/images。',
            ],

            'downloadable' => [
                'file-required' => '需要文件（multipart 字段"file"）。',
                'file-too-large' => '文件过大。允许的最大大小为 128 MB。',
                'upload-failed' => '上传可下载文件失败。',
                'file-not-found' => '未找到此产品和属性的已存储文件。',
                'no-permission' => '您没有管理可下载产品文件的权限。',
            ],

            'video' => [
                'uploaded' => '产品视频已成功上传。',
                'deleted' => '产品视频已成功删除。',
                'video-required' => '需要视频文件（multipart 字段"video"）。',
                'video-invalid-type' => '视频类型必须为以下之一：mp4、webm、ogg、mov。',
                'video-too-large' => '视频过大。允许的最大大小为 100 MB。',
                'not-found' => '未找到产品视频。',
                'no-permission' => '您没有管理产品视频的权限。',
                'upload-failed' => '上传产品视频失败。',
                'delete-failed' => '删除产品视频失败。',
                'graphql-upload-unsupported' => '不支持通过 GraphQL 上传视频。请使用 multipart/form-data 调用 POST /api/admin/catalog/products/{productId}/videos。',
            ],

            'inventory' => [
                'updated' => '产品库存已成功保存。',
                'inventories-required' => 'inventories 字段为必填项，且必须为 inventory_source_id 到数量的非空映射。',
                'inventories-invalid' => 'inventories 字段必须为 inventory_source_id（正整数）到数量（非负整数）的映射。',
                'source-not-found' => '库存来源 :id 不存在。',
                'qty-invalid' => '每个数量都必须为非负整数。',
                'no-permission' => '您没有管理产品库存的权限。',
                'not-found' => '未找到产品。',
            ],

            'customer-group-price' => [
                'created' => '客户组价格已成功添加。',
                'updated' => '客户组价格已成功更新。',
                'deleted' => '客户组价格已成功删除。',
                'qty-required' => 'qty 字段为必填项。',
                'qty-invalid' => 'qty 字段必须为大于或等于1的整数。',
                'value-type-required' => 'value_type 字段为必填项。',
                'value-type-invalid' => 'value_type 字段必须为"fixed"或"discount"。',
                'value-required' => 'value 字段为必填项。',
                'value-invalid' => 'value 字段必须为非负数。',
                'customer-group-not-found' => '所选客户组不存在。',
                'duplicate-qty-group' => '该产品已存在此（qty, customer_group_id）组合的客户组价格。',
                'not-found' => '未找到客户组价格。',
                'no-permission' => '您没有管理产品客户组价格的权限。',
            ],
        ],

        'category' => [
            'not-found' => '未找到分类。',

            'created' => '分类已成功创建。',
            'updated' => '分类已成功更新。',
            'deleted' => '分类已成功删除。',
            'slug-required' => 'slug 字段为必填项。',
            'slug-unique' => '该 slug 已被使用。',
            'name-required' => 'name 字段为必填项。',
            'position-required' => 'position 字段为必填项。',
            'attributes-required' => '至少需要一个可筛选属性。',
            'description-required-for-mode' => '所选显示模式需要描述。',
            'cannot-delete-root' => '根分类和渠道根分类无法删除。',
            'cannot-delete-channel-root' => '此分类是渠道根分类，无法删除。',
            'delete-failed' => '无法删除分类。',
            'no-permission' => '您没有管理分类的权限。',
            'mass-delete-success' => '分类已成功删除。',
            'mass-update-status-success' => '分类状态已成功更新。',
            'mass-delete-indices-required' => 'indices 字段为必填项，且必须为非空数组。',
            'mass-update-status-value-required' => 'value 字段为必填项，且必须为0或1。',
        ],

        'attribute' => [
            'not-found' => '未找到属性。',

            'create-success' => '属性已成功创建。',
            'update-success' => '属性已成功更新。',
            'delete-success' => '属性已成功删除。',
            'delete-failed' => '无法删除属性。',
            'mass-delete-success' => '属性已成功删除。',
            'mass-delete-indices-required' => 'indices 字段为必填项，且必须为非空数组。',

            'code-immutable' => '属性创建后无法更改属性代码。',
            'type-immutable' => '属性存在产品值时无法更改属性类型。',
            'locale-scope-immutable' => '属性存在产品值时无法更改语言区域范围。',
            'system-attribute' => '系统属性无法删除。',
            'in-use-family' => '该属性属于一个或多个属性族（属性组ID：:ids）。请先将其从这些属性族中移除。',

            'option-not-found' => '未找到属性选项。',
            'option-not-supported' => '属性类型":type"不支持选项。只有 select、multiselect 和 checkbox 属性可以拥有选项。',
            'option-in-use' => '此选项已被 :count 个产品使用，无法删除。',
            'option-delete-success' => '属性选项已成功删除。',
        ],

        'family' => [
            'not-found' => '未找到属性族。',

            'created' => '属性族已成功创建。',
            'updated' => '属性族已成功更新。',
            'deleted' => '属性族已成功删除。',
            'code-required' => '需要属性族代码。',
            'code-unique' => '属性族代码必须唯一。',
            'name-required' => '需要属性族名称。',
            'group-code-required' => '每个属性组都必须有代码。',
            'group-name-required' => '每个属性组都必须有名称。',
            'group-column-invalid' => '每个属性组的列必须为1或2。',
            'group-name-duplicate' => '属性组名称在属性族内必须唯一。',
            'update-failed' => '无法更新属性族。',
            'create-failed' => '无法创建属性族。',
            'default-delete-error' => '默认属性族无法删除。',
            'last-delete-error' => '至少需要一个属性族。',
            'attribute-product-error' => '此属性族正被一个或多个产品使用。请在删除前为这些产品重新分配属性族。',
            'delete-failed' => '无法删除属性族。',
            'no-permission' => '您没有管理属性族的权限。',
        ],

        'settings' => [
            'locale' => [
                'not-found' => '未找到语言区域。',
                'created' => '语言区域已成功创建。',
                'updated' => '语言区域已成功更新。',
                'deleted' => '语言区域已成功删除。',
                'cannot-delete-last' => '至少需要一个语言区域 — 无法删除最后剩余的语言区域。',
                'cannot-delete-channel-default' => '此语言区域是一个或多个渠道的默认语言区域，无法删除。',
                'delete-failed' => '无法删除语言区域。',
                'no-permission' => '您没有管理语言区域的权限。',
                'mass-delete-success' => '语言区域已成功删除。',
                'mass-delete-indices-required' => 'indices 字段为必填项，且必须为非空数组。',
            ],

            'theme' => [
                'not-found' => '未找到主题定制。',
                'created' => '主题定制已成功创建。',
                'updated' => '主题定制已成功更新。',
                'deleted' => '主题定制已成功删除。',
                'delete-failed' => '无法删除主题定制。',
                'no-permission' => '您没有管理主题定制的权限。',
                'mass-delete-success' => '主题定制已成功删除。',
                'mass-delete-indices-required' => 'indices 字段为必填项，且必须为非空数组。',
                'mass-update-success' => '主题定制已成功更新。',
                'mass-update-indices-required' => 'indices 字段为必填项，且必须为非空数组。',
                'mass-update-value-invalid' => 'value 字段必须为0或1。',
            ],

            'inventory-source' => [
                'not-found' => '未找到库存来源。',
                'created' => '库存来源已成功创建。',
                'updated' => '库存来源已成功更新。',
                'deleted' => '库存来源已成功删除。',
                'code-unique' => '已存在使用此代码的库存来源。',
                'last-delete-error' => '至少需要一个库存来源 — 无法删除最后剩余的来源。',
                'in-use' => '此库存来源已被产品库存引用，无法删除。',
                'delete-failed' => '无法删除库存来源。',
                'no-permission' => '您没有管理库存来源的权限。',
                'mass-delete-success' => '库存来源已成功删除。',
                'mass-delete-indices-required' => 'indices 字段为必填项，且必须为非空数组。',
            ],

            'exchange-rate' => [
                'not-found' => '未找到汇率。',
                'created' => '汇率已成功创建。',
                'updated' => '汇率已成功更新。',
                'deleted' => '汇率已成功删除。',
                'duplicate-pair' => '此目标货币的汇率已存在。请更新现有记录或先将其删除。',
                'delete-failed' => '无法删除汇率。',
                'no-permission' => '您没有管理汇率的权限。',
                'mass-delete-success' => '汇率已成功删除。',
                'mass-delete-indices-required' => 'indices 字段为必填项，且必须为非空数组。',
                'update-rates-success' => '汇率已成功更新。',
                'update-rates-failed' => '无法从外部提供商更新汇率。',
                'provider-not-configured' => '未配置外部汇率提供商。',
            ],

            'tax-rate' => [
                'not-found' => '未找到税率。',
                'created' => '税率已成功创建。',
                'updated' => '税率已成功更新。',
                'deleted' => '税率已成功删除。',
                'delete-failed' => '无法删除税率。',
                'no-permission' => '您没有管理税率的权限。',
                'zip-code-required' => '当 is_zip 为 false 时，zip_code 字段为必填项。',
                'zip-range-required' => '当 is_zip 为 true 时，zip_from 和 zip_to 均为必填项。',
            ],

            'tax-category' => [
                'not-found' => '未找到税种。',
                'created' => '税种已成功创建。',
                'updated' => '税种已成功更新。',
                'deleted' => '税种已成功删除。',
                'code-unique' => '该代码已被使用。',
                'cannot-delete-in-use' => '此税种仍关联有税率，无法删除。',
                'delete-failed' => '无法删除税种。',
                'no-permission' => '您没有管理税种的权限。',
            ],

            'role' => [
                'not-found' => '未找到角色。',
                'created' => '角色已成功创建。',
                'updated' => '角色已成功更新。',
                'deleted' => '角色已成功删除。',
                'permissions-required' => '当 permission_type 为 custom 时，permissions 字段为必填项。',
                'cannot-delete-in-use' => '此角色已分配给一个或多个管理员，无法删除。',
                'cannot-delete-last-role' => '至少需要一个角色 — 无法删除最后剩余的角色。',
                'cannot-change-last-all-access' => '唯一的完全访问角色无法更改为自定义角色。',
                'delete-failed' => '无法删除角色。',
                'no-permission' => '您没有管理角色的权限。',
            ],

            'currency' => [
                'not-found' => '未找到货币。',
                'created' => '货币已成功创建。',
                'updated' => '货币已成功更新。',
                'deleted' => '货币已成功删除。',
                'code-required' => 'code 字段为必填项。',
                'code-size' => '代码必须恰好为3个字母。',
                'code-unique' => '该代码已被使用。',
                'name-required' => 'name 字段为必填项。',
                'cannot-delete-last' => '至少需要一种货币 — 无法删除最后剩余的货币。',
                'cannot-delete-channel-base' => '此货币是一个或多个渠道的基础货币，无法删除。',
                'delete-failed' => '无法删除货币。',
                'no-permission' => '您没有管理货币的权限。',

                'mass-delete-success' => '货币已成功删除。',
                'mass-delete-indices-required' => 'indices 字段为必填项，且必须为非空数组。',
            ],

            'channel' => [
                'not-found' => '未找到渠道。',

                'created' => '渠道已成功创建。',
                'updated' => '渠道已成功更新。',
                'deleted' => '渠道已成功删除。',
                'code-unique' => '该代码已被使用。',
                'hostname-unique' => '该主机名已被使用。',
                'default-locale-mismatch' => 'default_locale_id 必须出现在语言区域列表中。',
                'base-currency-mismatch' => 'base_currency_id 必须出现在货币列表中。',
                'unknown-locale' => '一个或多个语言区域ID不存在。',
                'unknown-currency' => '一个或多个货币ID不存在。',
                'unknown-inventory-source' => '一个或多个 inventory_source ID不存在。',
                'unknown-root-category' => 'root_category_id 不存在。',
                'cannot-delete-last' => '至少需要一个渠道 — 无法删除最后剩余的渠道。',
                'cannot-delete-default' => '无法删除应用程序的默认渠道。',
                'delete-failed' => '无法删除渠道。',
                'no-permission' => '您没有管理渠道的权限。',
            ],

            'user' => [
                'not-found' => '未找到管理员用户。',
                'created' => '管理员用户已成功创建。',
                'updated' => '管理员用户已成功更新。',
                'deleted' => '管理员用户已成功删除。',
                'name-required' => 'name 字段为必填项。',
                'email-required' => 'email 字段为必填项。',
                'email-invalid' => '电子邮箱必须为有效的电子邮箱地址。',
                'email-unique' => '该电子邮箱已被使用。',
                'password-required' => 'password 字段为必填项。',
                'password-min' => '密码至少需要6个字符。',
                'role-required' => 'role_id 字段为必填项。',
                'role-not-found' => '所选角色不存在。',
                'status-invalid' => '状态必须为0或1。',
                'cannot-delete-self' => '您无法删除自己的管理员账户。',
                'cannot-delete-last-admin' => '至少需要一个管理员用户 — 无法删除最后剩余的管理员。',
                'cannot-change' => '无法更改最后一个完全访问管理员的 :name。',
                'delete-failed' => '无法删除管理员用户。',
                'no-permission' => '您没有管理管理员用户的权限。',
                'self-delete-password-required' => '删除您自己的账户需要填写 password 字段。',
                'self-delete-incorrect-password' => '密码不正确。',
                'self-deleted' => '您的管理员账户已被删除。',
            ],

            'data-transfer' => [
                'import' => [
                    'not-found' => '未找到导入。',
                    'deleted' => '导入已成功删除。',
                    'cancelled' => '导入已成功取消。',
                    'cannot-cancel' => '只能取消待处理或处理中的导入。当前状态：:state。',
                    'no-permission' => '您没有管理数据传输导入的权限。',
                    'delete-failed' => '无法删除导入。',
                    'created' => '导入已成功创建。',
                    'updated' => '导入已成功更新。',
                    'type-invalid' => '所选类型无效。',
                    'action-invalid' => '操作必须为以下之一：append、delete。',
                    'validation-strategy-invalid' => '验证策略必须为以下之一：stop-on-errors、skip-errors。',
                    'allowed-errors-invalid' => '允许的错误数必须为0或更大的整数。',
                    'field-separator-required' => '需要字段分隔符。',
                    'file-required' => '需要文件。',
                    'file-invalid-type' => '文件必须为以下格式之一：csv、xls、xlsx、xml。',
                    'nothing-to-import' => '没有可导入的内容。',
                    'not-valid' => '导入无效。',
                    'setup-queue-error' => '队列连接设置为 sync；请配置真实的队列以在后台处理导入。',
                    'file-not-found' => '没有可供下载的文件。',
                    'error-report-not-found' => '没有可供下载的错误报告。',
                    'sample-not-found' => '所请求的类型和格式没有可用的示例文件。',
                    'sample-format-invalid' => '示例格式必须为以下之一：csv、xls、xlsx、xml。',
                    'images-archive-too-large' => '图片压缩包最多只能包含 :entries 个文件，解压后最多 :size MB。',
                ],
            ],
        ],

        'cms' => [
            'page' => [
                'not-found' => '未找到 CMS 页面。',

                'created' => 'CMS 页面已成功创建。',
                'url-key-required' => 'url_key 字段为必填项。',
                'url-key-unique' => '该 url_key 已被使用。',
                'url-key-invalid' => 'url_key 格式无效。只能使用小写字母、数字和连字符。',
                'page-title-required' => 'page_title 字段为必填项。',
                'html-content-required' => 'html_content 字段为必填项。',
                'channels-required' => '必须至少选择一个渠道。',
                'channels-invalid' => '一个或多个渠道无效。',

                'updated' => 'CMS 页面已成功更新。',

                'deleted' => 'CMS 页面已成功删除。',
                'delete-failed' => '无法删除 CMS 页面。',

                'mass-deleted' => 'CMS 页面已成功删除。',
                'indices-required' => 'indices 字段为必填项，且必须为非空数组。',
                'indices-invalid' => 'indices 字段必须为 CMS 页面ID数组。',

                'no-permission' => '您没有管理 CMS 页面的权限。',
            ],
        ],

        'marketing' => [
            'catalog-rule' => [
                'not-found' => '未找到目录规则。',
                'created' => '目录规则已成功创建。',
                'updated' => '目录规则已成功更新。',
                'deleted' => '目录规则已成功删除。',
                'delete-failed' => '无法删除目录规则。',
                'mass-delete-indices-required' => '未提供要批量删除的目录规则ID。',
                'mass-delete-success' => '所选目录规则已成功删除。',
                'no-permission' => '您没有管理目录规则的权限。',
            ],

            'campaign' => [
                'not-found' => '未找到营销活动。',
                'created' => '营销活动已成功创建。',
                'updated' => '营销活动已成功更新。',
                'deleted' => '营销活动已成功删除。',
                'delete-failed' => '无法删除营销活动。',
                'no-permission' => '您没有管理营销活动的权限。',

                'send' => [
                    'id-required' => '需要营销活动ID。',
                    'inactive' => '无法发送未激活的营销活动。请先启用它。',
                    'queued' => '营销活动已加入队列，将发送给 :count 位收件人。',
                ],
            ],

            'sitemap' => [
                'not-found' => '未找到站点地图。',
                'created' => '站点地图已成功创建。',
                'updated' => '站点地图已成功更新。',
                'deleted' => '站点地图已成功删除。',
                'delete-failed' => '无法删除站点地图。',
                'no-permission' => '您没有管理站点地图的权限。',

                'generate' => [
                    'id-required' => '需要站点地图ID。',
                    'no-channels' => '此站点地图未覆盖任何渠道，因此没有可生成的内容。请先为其分配至少一个渠道。',
                    'success' => '站点地图已成功重新生成。',
                    'failed' => '站点地图生成失败：:message',
                ],
            ],

            'cart-rule' => [
                'not-found' => '未找到购物车规则。',
                'id-required' => '需要购物车规则ID。',
                'copy-of' => ':value 的副本',
                'copied' => '购物车规则已成功复制。',
                'created' => '购物车规则已成功创建。',
                'updated' => '购物车规则已成功更新。',
                'deleted' => '购物车规则已成功删除。',
                'delete-failed' => '无法删除购物车规则。',
                'no-permission' => '您没有管理购物车规则的权限。',
                'mass-delete-success' => '购物车规则已成功删除。',
                'mass-delete-indices-required' => 'indices 字段为必填项，且必须为非空数组。',
                'action-type-invalid' => 'action_type 必须为以下之一：by_percent、by_fixed、cart_fixed、buy_x_get_y。',
                'coupon-code-required' => '当 coupon_type=1 且 use_auto_generation=0 时，需要 coupon_code。',
                'coupon-code-unique' => '此优惠券代码已被使用。',
            ],

            'template' => [
                'campaign-associated' => '此电子邮件模板正被营销活动使用，因此无法删除。请先将其从营销活动中移除。',
                'not-found' => '未找到电子邮件模板。',
                'created' => '电子邮件模板已成功创建。',
                'updated' => '电子邮件模板已成功更新。',
                'deleted' => '电子邮件模板已成功删除。',
                'delete-failed' => '无法删除电子邮件模板。',
                'status-invalid' => '状态必须为以下之一：active、inactive、draft。',
                'no-permission' => '您没有管理电子邮件模板的权限。',
            ],

            'event' => [
                'campaign-associated' => '此事件正被营销活动使用，因此无法删除。请先将其从营销活动中移除。',
                'not-found' => '未找到营销事件。',
                'created' => '营销事件已成功创建。',
                'updated' => '营销事件已成功更新。',
                'deleted' => '营销事件已成功删除。',
                'delete-failed' => '无法删除营销事件。',
                'no-permission' => '您没有管理营销事件的权限。',
            ],

            'search-synonym' => [
                'not-found' => '未找到搜索同义词。',
                'created' => '搜索同义词已成功创建。',
                'updated' => '搜索同义词已成功更新。',
                'deleted' => '搜索同义词已成功删除。',
                'mass-deleted' => '搜索同义词已成功删除。',
                'delete-failed' => '无法删除搜索同义词。',
                'indices-required' => 'indices 字段为必填项，且必须为搜索同义词ID的非空数组。',
                'no-permission' => '您没有管理搜索同义词的权限。',
            ],

            'url-rewrite' => [
                'not-found' => '未找到 URL 重写。',
                'created' => 'URL 重写已成功创建。',
                'updated' => 'URL 重写已成功更新。',
                'deleted' => 'URL 重写已成功删除。',
                'delete-failed' => '无法删除 URL 重写。',
                'mass-deleted' => 'URL 重写已成功删除。',
                'indices-required' => '至少需要一个 URL 重写ID。',
                'no-permission' => '您没有管理 URL 重写的权限。',
            ],

            'cart-rule-coupon' => [
                'cart-rule-not-found' => '未找到购物车规则。',
                'not-found' => '未找到优惠券。',
                'code-taken' => '已存在使用此代码的优惠券。',
                'format-invalid' => '格式必须为以下之一：alphabetic、alphanumeric、numeric。',
                'indices-required' => 'indices 字段为必填项，且必须为优惠券ID的非空数组。',
                'created' => '优惠券已成功创建。',
                'generated' => '已成功生成 :count 张优惠券。',
                'deleted' => '优惠券已成功删除。',
                'mass-deleted' => '已成功删除 :count 张优惠券。',
                'no-permission' => '您没有管理购物车规则优惠券的权限。',
            ],

            'subscriber' => [
                'not-found' => '未找到新闻通讯订阅者。',
                'updated' => '订阅已成功更新。',
                'deleted' => '订阅已成功删除。',
                'is-subscribed-required' => 'is_subscribed 字段为必填项。',
                'no-permission' => '您没有管理新闻通讯订阅者的权限。',
            ],

            'search-term' => [
                'not-found' => '未找到搜索词。',
                'updated' => '搜索词已成功更新。',
                'deleted' => '搜索词已成功删除。',
                'mass-deleted' => '搜索词已成功删除。',
                'term-required' => 'term 字段为必填项。',
                'redirect-url-invalid' => 'redirect_url 必须为有效的 URL。',
                'indices-required' => 'indices 中必须至少提供一个ID。',
                'no-permission' => '您没有管理搜索词的权限。',
            ],
        ],
        'customer-api-sessions' => [
            'menu' => [
                'title' => 'API 会话',
            ],

            'acl' => [
                'title' => 'API 会话',
                'delete' => '撤销',
            ],

            'index' => [
                'title' => '客户 API 会话',
                'info' => '通过网页、移动、POS 或桌面等应用登录店面 API 的客户。撤销会话会立即让该设备退出登录。',
            ],

            'fields' => [
                'customer' => '客户',
                'email' => '邮箱',
                'channel' => '渠道',
            ],

            'datagrid' => [
                'revoke-all' => '撤销该客户的所有会话',
            ],

            'card' => [
                'title' => 'API 会话（:count）',
                'empty' => '该客户没有活跃的 API 会话。',
            ],

            'errors' => [
                'no-permission' => '您没有管理客户 API 会话的权限。',
            ],
        ],

        'customer-auth' => [
            'configuration' => [
                'title' => '客户认证',
                'info' => '店面客户登录的令牌有效期、限制和会话规则。',

                'tokens' => [
                    'title' => '令牌有效期',
                    'info' => '访问令牌、刷新令牌和会话的有效时长。更改适用于之后签发的令牌。',
                    'access-token-ttl' => '访问令牌有效期（分钟）',
                    'access-token-ttl-info' => '5 到 60 分钟。默认 15。',
                    'refresh-token-ttl' => '刷新令牌有效期（天）',
                    'refresh-token-ttl-info' => '1 到 90 天。默认 30。',
                    'session-lifetime' => '会话最长有效期（天）',
                    'session-lifetime-info' => '1 到 365 天。默认 90。之后无论会话刷新多少次，客户都必须重新登录。',
                    'max-sessions' => '每个客户最大活跃会话数',
                    'max-sessions-info' => '达到上限时，最早的会话将被结束。0 到 100。0 表示不限制。',
                ],

                'rate-limits' => [
                    'title' => '速率限制',
                    'info' => '每个已登录客户每分钟的店面 API 请求上限。',
                    'requests-per-minute' => '每个已登录客户每分钟请求数',
                    'requests-per-minute-info' => '1 到 100000。默认 120。',
                ],

                'login-protection' => [
                    'title' => '登录保护',
                    'info' => '在客户端必须等待之前允许的登录失败次数和令牌刷新次数。',
                    'login-max-attempts' => '锁定前的登录失败次数',
                    'login-max-attempts-info' => '按邮箱和 IP 地址计算。3 到 20。默认 5。',
                    'login-lockout-seconds' => '登录锁定时长（秒）',
                    'login-lockout-seconds-info' => '60 到 3600。默认 60。',
                    'refresh-requests-per-minute' => '每个会话每分钟令牌刷新次数',
                    'refresh-requests-per-minute-info' => '1 到 60。默认 10。',
                ],

                'security' => [
                    'title' => '安全',
                    'info' => '哪些情况会结束客户的其他会话。',
                    'revoke-sessions-on-password-change' => '修改密码时撤销会话',
                ],

                'retention' => [
                    'title' => '数据保留',
                    'info' => '已结束客户会话的保留时长。',
                    'session-prune-days' => '保留已结束会话（天）',
                    'session-prune-days-info' => '已撤销或已过期的会话在此天数后删除。1 到 365。默认 30。',
                ],
            ],
        ],
        'auth' => [
            'configuration' => [
                'title' => '管理员认证',
                'info' => '管理员 API 登录和 API 令牌的令牌有效期、速率限制和安全规则。',

                'tokens' => [
                    'title' => '令牌有效期',
                    'info' => '访问令牌、刷新令牌和会话的有效时长。更改适用于之后签发的令牌。',
                    'access-token-ttl' => '访问令牌有效期（分钟）',
                    'access-token-ttl-info' => '5 到 60 分钟。默认 15。',
                    'refresh-token-ttl' => '刷新令牌有效期（天）',
                    'refresh-token-ttl-info' => '1 到 90 天。默认 30。',
                    'session-lifetime' => '会话最长有效期（天）',
                    'session-lifetime-info' => '1 到 365 天。默认 90。之后无论会话刷新多少次，管理员都必须重新登录。',
                ],

                'rate-limits' => [
                    'title' => '速率限制',
                    'info' => '每分钟管理员 API 请求的最大次数。',
                    'session-requests-per-minute' => '每个已登录管理员每分钟请求数',
                    'api-token-requests-per-minute' => '每个 API 令牌每分钟请求数',
                    'session-requests-per-minute-info' => '1 到 100000。默认 60。',
                    'api-token-requests-per-minute-info' => '1 到 100000。默认 60。',
                    'unauthenticated-requests-per-minute' => '无令牌时每个 IP 地址每分钟请求数',
                    'unauthenticated-requests-per-minute-info' => '10 到 1000。默认 60。包括登录、双因素验证、刷新和密码请求。',
                ],

                'security' => [
                    'title' => '安全',
                    'info' => '撤销规则、API 令牌过期和域名绑定。',
                    'revoke-sessions-on-password-change' => '修改密码时撤销会话',
                    'revoke-api-tokens-on-password-change' => '修改密码时撤销 API 令牌',
                    'allow-non-expiring-api-tokens' => '允许永不过期的 API 令牌',
                    'domain-binding-mode' => '域名绑定模式',
                    'domain-binding-mode-info' => 'DNS 检查请求是否来自令牌域名解析到的 IP 地址。Origin 检查 Origin 或 Referer 请求头，仅适用于浏览器调用。',
                    'domain-binding-dns' => 'DNS（客户端 IP 地址）',
                    'domain-binding-origin' => 'Origin 请求头',
                ],

                'login-protection' => [
                    'title' => '登录保护',
                    'info' => '在客户端必须等待之前允许的登录失败次数、错误双因素验证码次数和密码请求次数。',
                    'login-max-attempts' => '锁定前的登录失败次数',
                    'login-max-attempts-info' => '按邮箱和 IP 地址计算。3 到 20。默认 5。',
                    'login-lockout-seconds' => '登录锁定时长（秒）',
                    'login-lockout-seconds-info' => '60 到 3600。默认 60。',
                    'two-factor-challenge-lifetime' => '双因素验证挑战有效期（分钟）',
                    'two-factor-challenge-lifetime-info' => '1 到 15。默认 5。',
                    'two-factor-max-failures' => '每分钟错误双因素验证码次数',
                    'two-factor-max-failures-info' => '按管理员计算，涵盖所有挑战。3 到 20。默认 5。',
                    'password-requests-per-minute' => '每分钟密码请求数',
                    'password-requests-per-minute-info' => '每个 IP 地址的忘记密码和重置密码请求。1 到 20。默认 5。',
                ],

                'retention' => [
                    'title' => '数据保留',
                    'info' => '已结束会话和 API 审计历史的保留时长。',
                    'session-prune-days' => '保留已结束会话（天）',
                    'session-prune-days-info' => '已撤销或已过期的会话在此天数后删除。1 到 365。默认 30。',
                    'audit-retention-days' => '保留 API 审计历史（天）',
                    'audit-retention-days-info' => '较旧的记录每天删除。0 到 3650。留空或 0 表示永久保留。',
                ],
            ],

            'errors' => [
                'unauthenticated' => '您尚未通过身份验证。',
                'access-token-expired' => '访问令牌已过期。请刷新后继续。',
                'api-token-expired' => 'API 令牌已过期。',
                'refresh-token-invalid' => '刷新令牌无效或已过期。请重新登录。',
                'api-token-revoked' => 'API 令牌已被撤销。',
                'api-token-domain-mismatch' => '无法从此域名使用该 API 令牌。',
                'access-token-required' => '此操作需要登录会话的访问令牌；API 令牌无法使用。',
                'two-factor-required' => '请输入身份验证器应用中的 6 位验证码，或任一备用码，以完成登录。',
                'too-many-login-attempts' => '登录尝试次数过多。请在 :seconds 秒后重试。',
                'two-factor-challenge-invalid' => '双重验证请求无效或已过期。请重新登录。',
                'two-factor-attempts-exceeded' => '错误验证码次数过多。请重新登录。',
                'password-reset-token-invalid' => '此密码重置令牌无效或已过期。',
                'too-many-password-requests' => '密码请求过多。请在 :seconds 秒后重试。',
                'rate-limit-exceeded' => '尝试次数过多。',
                'no-permission' => '您没有管理管理员 API 会话的权限。',
            ],

            'sessions' => [
                'logged-out' => '您已退出登录。',
                'logged-out-all' => '您已退出所有会话。',
                'not-found' => '未找到会话。',
                'revoked' => '会话已被撤销。',
                'revoked-for-admin' => '已撤销的会话：:count。',
            ],

            'password' => [
                'reset' => '您的密码已重置。请重新登录。',
            ],
        ],

        'api-tokens' => [
            'menu' => [
                'title' => 'API 令牌',
            ],

            'acl' => [
                'title' => 'API 令牌',
                'create' => '创建',
                'delete' => '撤销',
                'manage-others' => '管理其他管理员的令牌',
            ],

            'index' => [
                'title' => 'API 令牌',
                'info' => 'ERP、PIM、仓库或财务系统等服务器集成使用 API 令牌进行认证。每个令牌以一位管理员的身份、按其角色权限执行操作,并且只能从绑定的域名使用。',
                'create-btn' => '创建 API 令牌',
            ],

            'create' => [
                'title' => '创建 API 令牌',
                'back-btn' => '返回',
                'save-btn' => '创建令牌',
                'general' => '常规',
                'expiry' => '有效期',
                'admin-info' => '令牌以该管理员身份执行操作,权限与其角色完全一致。',
                'domain-info' => '集成发起调用的主机,例如 erp.example.com。URL 只保留主机部分。不接受 IP 地址和通配符,域名创建后无法修改。',
                'expires-on-date' => '在指定日期过期',
                'never-expires' => '永不过期',
                'no-expiry-warning' => '没有有效期的令牌在被撤销前一直有效。请将其保存在密钥库中,不再需要时立即撤销。',
                'no-expiry-confirm' => '我了解此令牌永不过期。',
                'no-admins' => '没有您可以为其创建 API 令牌的管理员。',
            ],

            'view' => [
                'title' => 'API 令牌',
                'back-btn' => '返回',
                'save-btn' => '保存',
                'revoke-btn' => '撤销',
                'details' => '详情',
                'usage' => '使用情况',
                'token-once-title' => '请立即复制您的 API 令牌',
                'token-once-info' => '令牌仅显示这一次。请将其保存在密钥库中;如果丢失,请撤销并重新创建。',
                'copy-btn' => '复制',
                'copied' => '令牌已复制。',
                'usage-info' => '在 REST 和 GraphQL 请求的 Authorization 请求头中以 Bearer 令牌的形式发送。',
                'not-yet' => '暂无',
                'revoke-confirm' => '撤销此 API 令牌?使用它的集成将立即停止工作。',
            ],

            'fields' => [
                'id' => 'ID',
                'name' => '名称',
                'admin' => '管理员',
                'token' => '令牌',
                'domain' => '域名',
                'expiry' => '有效期',
                'expires-at' => '过期时间',
                'no-expiry-confirmation' => '永不过期确认',
                'status' => '状态',
                'created-by' => '创建者',
                'created-at' => '创建时间',
                'last-used-at' => '最后使用时间',
                'last-ip-address' => '最后 IP 地址',
                'last-user-agent' => '最后用户代理',
                'last-channel' => '最后渠道',
                'resolved-ips' => '解析的 IP 地址',
                'resolved-at' => '解析时间',
                'domain-mismatches' => '来自其他域名的请求',
                'last-domain-mismatch-at' => '最后一次来自其他域名的请求',
                'revoked-at' => '撤销时间',
                'revoked-by' => '撤销者',
            ],

            'datagrid' => [
                'never' => '永不',
                'console' => '控制台命令',
                'view' => '查看',
                'revoke' => '撤销',
            ],

            'status' => [
                'active' => '有效',
                'expired' => '已过期',
                'revoked' => '已撤销',
            ],

            'channels' => [
                'rest' => 'REST',
                'graphql' => 'GraphQL',
            ],

            'messages' => [
                'created' => 'API 令牌已创建。',
                'updated' => 'API 令牌已更新。',
                'revoked' => 'API 令牌已撤销。',
            ],

            'errors' => [
                'no-permission' => '您没有管理 API 令牌的权限。',
                'not-found' => '未找到 API 令牌。',
                'cannot-create-for-admin' => '您无法为该管理员创建 API 令牌:其角色拥有您的角色没有的权限,或您无权管理其他管理员的令牌。',
                'cannot-manage' => '您无法管理此 API 令牌。',
                'already-revoked' => '该 API 令牌已被撤销。',
                'invalid-name' => '名称为必填项,最多 100 个字符。',
                'invalid-domain' => '请输入域名,例如 erp.example.com。不接受 IP 地址和通配符。',
                'expiry-required' => 'API 令牌必须设置过期日期。',
                'expiry-in-past' => '过期日期必须是将来的日期。',
            ],
        ],

        'api-sessions' => [
            'menu' => [
                'title' => 'API 会话',
            ],

            'acl' => [
                'title' => 'API 会话',
                'delete' => '撤销',
            ],

            'index' => [
                'title' => 'API 会话',
                'info' => '通过网页、移动、POS 或桌面应用登录管理 API 的管理员。撤销会话会立即让该设备退出登录。',
            ],

            'fields' => [
                'id' => 'ID',
                'admin' => '管理员',
                'device' => '设备',
                'client-type' => '客户端类型',
                'ip-address' => '登录 IP 地址',
                'user-agent' => '登录用户代理',
                'last-ip-address' => '最后 IP 地址',
                'last-user-agent' => '最后用户代理',
                'last-used-at' => '最后使用时间',
                'last-channel' => '最后渠道',
                'refresh-expires-at' => '刷新过期时间',
                'expires-at' => '过期时间',
                'status' => '状态',
                'created-at' => '登录时间',
            ],

            'datagrid' => [
                'revoke' => '撤销',
                'revoke-all' => '撤销该管理员的所有会话',
            ],

            'client-types' => [
                'web' => '网页',
                'mobile' => '移动',
                'pos' => 'POS',
                'desktop' => '桌面',
                'other' => '其他',
            ],

            'errors' => [
                'not-active' => '该会话已被撤销或已过期。',
            ],
        ],

        'api-audit' => [
            'menu' => [
                'title' => 'API 审计',
            ],

            'acl' => [
                'title' => 'API变更历史',
                'delete' => '删除历史记录',
            ],

            'index' => [
                'title' => 'API变更历史',
                'info' => '通过管理 API 进行的每次创建、更新和删除，都由谁执行、使用哪个令牌以及更改了什么。',
                'cleanup-btn' => '删除旧日志',
                'cleanup-days' => '删除早于此天数的日志',
                'cleanup-confirm' => '删除所有早于给定天数的历史记录？此操作无法撤消。',
            ],

            'view' => [
                'title' => '改变',
                'back-btn' => '返回',
                'admin' => '管理员',
                'token' => '代币',
                'action' => '行动',
                'resource' => '资源',
                'method' => '方法',
                'ip' => 'IP地址',
                'date' => '日期',
                'version' => '版本',
                'url' => '端点',
                'request-details' => '请求详情',
                'changes' => '变化',
                'field' => '领域',
                'old' => '旧值',
                'new' => '新价值',
                'no-field-changes' => '该条目没有记录任何字段级更改。',
                'same-request' => '同一请求中的其他更改',
                'version-chain' => '该记录的版本历史',
            ],

            'datagrid' => [
                'id' => '身份证号',
                'date' => '日期',
                'admin' => '管理员',
                'token' => '代币',
                'token-kind' => '令牌类型',
                'channel' => '渠道',
                'operation' => '操作',
                'resource' => '资源',
                'method' => '方法',
                'ip' => '知识产权',
                'view' => '查看',
                'delete' => '删除',
            ],

            'events' => [
                'created' => '已创建',
                'updated' => '已更新',
                'deleted' => '已删除',
            ],

            'token-kinds' => [
                'session' => '登录会话',
                'api-token' => 'API 令牌',
                'integration' => '集成令牌(已移除)',
                'customer-session' => '客户登录会话',
            ],

            'channels' => [
                'rest' => 'REST',
                'graphql' => 'GraphQL',
                'panel' => '管理面板',
                'cli' => '控制台命令',
            ],

            'deleted' => ':count 条历史记录已删除。',
            'cleanup-input-required' => '提供清理的天数或日期。',
        ],

        'api-configuration' => [
            'title' => '应用程序编程接口',
            'info' => 'Bagisto API 及其管理模块的设置。',
        ],
    ],
];
