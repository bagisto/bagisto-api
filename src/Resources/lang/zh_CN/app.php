<?php

return [
    'graphql' => [
        'cart' => [
            'authentication-required'           => '需要身份验证令牌',
            'invalid-token'                     => '身份验证令牌无效或已过期',
            'unauthorized-access'               => '未授权访问购物车',
            'authenticated-only'                => '只有已通过身份验证的用户才能获取其购物车',
            'merge-requires-auth'               => '合并访客购物车需要身份验证',
            'unknown-operation'                 => '未知的购物车操作',

            'cart-not-found'                    => '未找到购物车',
            'guest-cart-not-found'              => '未找到访客购物车',
            'product-not-found'                 => '未找到产品',

            'product-id-quantity-required'      => '需要产品ID和数量',
            'cart-item-id-quantity-required'    => '需要购物车项目ID和数量',
            'cart-item-id-required'             => '需要购物车项目ID',
            'item-ids-required'                 => '需要项目ID数组',
            'coupon-code-required'              => '需要优惠券代码',
            'address-data-required'             => '需要国家/地区、省份和邮政编码',

            'add-product-failed'                => '未能将产品添加到购物车',
            'update-item-failed'                => '未能更新购物车项目',
            'remove-item-failed'                => '未能移除购物车项目',
            'apply-coupon-failed'               => '未能应用优惠券',
            'remove-coupon-failed'              => '未能移除优惠券',
            'move-to-wishlist-failed'           => '未能将项目移至心愿单',
            'estimate-shipping-failed'          => '未能估算运费',

            'product-added-successfully'         => '产品已成功添加到购物车',
            'guest-cart-merged'                  => '访客购物车已成功合并',
            'using-authenticated-cart'           => '正在使用已通过身份验证的客户购物车',
            'cart-item-not-found'                => '未找到购物车项目',
            'new-guest-cart-created'             => '已创建带有唯一会话令牌的新访客购物车',
            'select-items-to-remove'             => '请选择要移除的项目',
            'select-items-to-move-wishlist'      => '请选择要移至心愿单的项目',
            'invalid-or-expired-token'           => '购物车令牌无效或已过期。请创建新的购物车。',
            'invalid-token-of-login-user'        => '登录用户令牌无效。',
        ],

        'token-verification' => [
            'invalid-operation'                 => '无效操作',
            'invalid-input-data'                => '无效的输入数据',
            'token-required'                    => '需要令牌',
            'invalid-token-format'              => '无效的令牌格式',
            'token-not-found-or-expired'        => '未找到令牌或令牌已过期',
            'customer-not-found'                => '未找到客户',
            'customer-account-suspended'        => '客户账户已被暂停',
            'error-verifying-token'             => '验证令牌时出错',
            'token-is-valid'                    => '令牌有效',
        ],

        'forgot-password' => [
            'invalid-operation'                 => '无效操作',
            'invalid-input-data'                => '无效的输入数据',
            'email-required'                    => '需要电子邮箱',
            'reset-link-sent'                   => '重置链接已成功发送至您的电子邮箱',
            'email-not-found'                   => '未找到电子邮箱地址',
            'error-sending-reset-link'          => '发送重置链接时发生错误',
        ],

        'logout' => [
            'invalid-operation'                 => '无效操作',
            'invalid-input-data'                => '无效的输入数据',
            'token-required'                    => '需要令牌',
            'invalid-token-format'              => '无效的令牌格式',
            'logged-out-successfully'           => '已成功注销',
            'token-not-found-or-expired'        => '未找到令牌或令牌已过期',
            'error-during-logout'               => '注销时出错',
        ],

        'address' => [
            'deleted-successfully'              => '地址已成功删除',
            'authentication-required'           => '需要身份验证令牌',
            'invalid-token'                     => '令牌无效或已过期',
            'unknown-operation'                 => '未知操作',
            'address-id-required'               => '需要地址ID',
            'address-not-found'                 => '未找到地址或该地址不属于此客户',
            'retrieved'                         => '地址已成功获取',
            'fetch-failed'                      => '未能获取地址：',
        ],

        'customer-profile' => [
            'authentication-required'           => '需要身份验证令牌。请在查询输入中提供令牌',
            'invalid-token'                     => '令牌无效或已过期',
        ],

        'customer' => [
            'password-mismatch'                 => '密码和确认密码不匹配',
            'confirm-password-required'         => '更改密码时需要确认密码',
            'unauthenticated'                   => '未通过身份验证。请登录以执行此操作',
        ],

        'product-review' => [
            'product-id-required'               => '需要产品ID',
            'product-not-found'                 => '未找到产品',
            'rating-invalid'                    => '评分必须介于1到5之间',
            'title-required'                    => '需要评价标题',
            'comment-required'                  => '需要评价内容',
        ],

        'product' => [
            'not-found-with-sku'                => 'No product found with SKU',
            'not-found-with-url-key'            => 'No product found with URL key',
            'parameters-required'               => 'At least one of the following parameters must be provided: "sku", "id", "urlKey"',
        ],

        'auth' => [
            'no-token-provided'                 => '未提供身份验证令牌。请在 Authorization 请求头中以"Bearer <token>"的形式提供令牌，或在 input.token 字段中提供',
            'invalid-or-expired-token'          => '令牌无效或已过期',
            'request-not-found'                 => '在上下文中未找到请求',
            'token-required'                    => 'Authentication token is required. Please provide the token either in the GraphQL mutation input field or in the Authorization header as "Bearer <token>"',
            'unknown-resource'                  => '未知资源',
            'cannot-update-other-profile'       => '未授权：无法更新其他客户的个人资料',
        ],

        'upload' => [
            'invalid-base64'                    => '无效的 base64 编码图片数据',
            'size-exceeds-limit'                => '图片大小不得超过5MB',
            'invalid-format'                    => '无效的图片格式。请提供采用 data URI 方案的 base64 编码图片（data:image/jpeg;base64,...）',
            'failed'                            => '图片上传失败',
        ],

        'attribute' => [
            'code-already-exists'               => '该属性代码已存在',
        ],

        'login' => [
            'invalid-credentials'               => '电子邮箱或密码无效',
            'account-suspended'                 => '您的账户已被暂停',
            'successful'                        => '您已成功登录',
            'invalid-request'                   => '无效的登录请求',
        ],

        'checkout' => [
            'invalid-input'                     => '结账操作的输入数据无效',
            'billing-address-required'          => '需要账单地址',
            'shipping-address-required'         => '发货需要收货地址',
            'address-save-failed'               => '未能保存地址',
            'address-saved'                     => '地址已成功保存',
            'shipping-method-required'          => '需要配送方式',
            'invalid-shipping-method'           => '配送方式无效或不可用',
            'shipping-method-save-failed'       => '未能保存配送方式',
            'shipping-method-saved'             => '配送方式已成功保存',
            'shipping-method-error'             => '保存配送方式时出错',
            'payment-method-required'           => '需要付款方式',
            'invalid-payment-method'            => '付款方式无效或不可用',
            'payment-method-save-failed'        => '未能保存付款方式',
            'payment-method-saved'              => '付款方式已成功保存',
            'payment-method-error'              => '保存付款方式时出错',
            'order-creation-failed'             => '订单创建失败：订单ID为空或订单未保存',
            'order-retrieval-failed'            => '未能获取已创建的订单',
            'order-creation-error'              => '未能创建订单',
            'cart-empty'                        => '购物车为空',
            'account-suspended'                 => '您的账户已被暂停。请联系客服。',
            'account-inactive'                  => '您的账户处于非活动状态。请联系客服。',
            'minimum-order-not-met'             => '最低订单金额为 :amount',
            'email-required'                    => '创建订单需要电子邮箱地址',
            'unknown-operation'                 => '未知的结账操作',
        ],

        'customer-addresses' => [
            'token-required'                    => '获取客户地址需要令牌',
            'invalid-or-expired-token'          => '令牌无效或已过期',
            'token-validation-failed'           => '令牌验证失败',
        ],

        'product' => [
            'type'                              => '产品类型',
            'attribute-family'                  => '属性族',
            'sku'                               => 'SKU',
            'name'                              => '名称',
            'description'                       => '描述',
            'short-description'                 => '简短描述',
            'status'                            => '状态',
            'new'                               => '新品',
            'featured'                          => '推荐',
            'price'                             => '价格',
            'special-price'                     => '特价',
            'weight'                            => '重量',
            'cost'                              => '成本',
            'length'                            => '长度',
            'width'                             => '宽度',
            'height'                            => '高度',
            'color'                             => '颜色',
            'size'                              => '尺寸',
            'brand'                             => '品牌',
            'super-attributes'                  => '超级属性',
        ],

        'compare-item' => [
            'id-required'                       => '比较项目ID是必需的',
            'invalid-id-format'                 => '无效的ID格式。预期的IRI格式如"/api/shop/compare-items/1"或数字ID',
            'not-found'                         => '比较项目未找到',
            'product-id-required'               => '产品ID是必需的',
            'customer-id-required'              => '客户ID是必需的',
            'product-not-found'                 => '产品未找到',
            'customer-not-found'                => '客户未找到',
            'already-exists'                    => '该产品已在您的比较列表中',
        ],

        'downloadable-product' => [
            'download-link-not-found'           => '下载链接未找到或已过期',
            'purchased-link-not-found'          => '购买的链接未找到',
            'file-not-found'                    => '文件未找到',
            'download-successful'               => '文件已准备好下载',
            'token-required'                    => '需要下载令牌',
            'invalid-token'                     => '下载令牌无效或已过期',
            'token-expired'                     => '下载令牌已过期。请生成新令牌',
            'access-denied'                     => '访问被拒绝：您无权下载此文件',
            'redirect-external-url'             => '重定向到外部下载URL',
            'file-error'                        => '处理您的下载请求时发生错误',
            'unauthorized-access'               => '对下载资源的未授权访问',
        ],
    ],

    'integration' => [
        'menu' => [
            'title'  => '整合',
            'tokens' => '代币',
        ],

        'history' => [
            'menu' => [
                'title' => '历史',
            ],

            'acl' => [
                'title'  => 'API变更历史',
                'delete' => '删除历史记录',
            ],

            'index' => [
                'title'           => 'API变更历史',
                'info'            => '通过管理 API 进行的每次创建、更新和删除，都由谁执行、使用哪个令牌以及更改了什么。',
                'cleanup-btn'     => '删除旧日志',
                'cleanup-days'    => '删除早于此天数的日志',
                'cleanup-confirm' => '删除所有早于给定天数的历史记录？此操作无法撤消。',
            ],

            'view' => [
                'title'             => '改变',
                'back-btn'          => '返回',
                'admin'             => '管理员',
                'token'             => '代币',
                'action'            => '行动',
                'resource'          => '资源',
                'method'            => '方法',
                'ip'                => 'IP地址',
                'date'              => '日期',
                'version'           => '版本',
                'url'               => '端点',
                'request-details'   => '请求详情',
                'changes'           => '变化',
                'field'             => '领域',
                'old'               => '旧值',
                'new'               => '新价值',
                'no-field-changes'  => '该条目没有记录任何字段级更改。',
                'same-request'      => '同一请求中的其他更改',
                'version-chain'     => '该记录的版本历史',
            ],

            'datagrid' => [
                'id'        => '身份证号',
                'date'      => '日期',
                'admin'     => '管理员',
                'token'     => '代币',
                'action'    => '行动',
                'operation' => '操作',
                'resource'  => '资源',
                'version'   => '版本',
                'method'    => '方法',
                'ip'        => '知识产权',
                'view'      => '查看',
                'delete'    => '删除',
            ],

            'events' => [
                'created' => '已创建',
                'updated' => '已更新',
                'deleted' => '已删除',
            ],

            'deleted'                => ':count 条历史记录已删除。',
            'cleanup-input-required' => '提供清理的天数或日期。',
        ],

        'acl' => [
            'title'      => '整合',
            'create'     => '创建集成',
            'edit'       => '编辑集成',
            'delete'     => '撤销集成令牌',
            'generate'   => '生成集成令牌',
            'regenerate' => '重新生成集成令牌',
        ],

        'index' => [
            'title'      => '集成',
            'create-btn' => '创建集成',
        ],

        'create' => [
            'title'    => '创建集成',
            'save-btn' => '保存',
            'back-btn' => '返回',
        ],

        'edit' => [
            'title'          => '编辑集成',
            'save-btn'       => '保存',
            'back-btn'       => '返回',
            'generate-btn'   => '生成令牌',
            'regenerate-btn' => '重新生成令牌',
            'revoke-btn'     => '撤销令牌',
            'copy-btn'       => '复制',
            'token-warning'  => '立即保存此令牌 - 它不会再次显示。',
            'token-label'    => '代币',
            'not-generated'  => '尚未生成',
            'masked'         => '（已存储 - 仅在生成时显示一次）',
            'history-banner' => '该令牌不再有效。',
        ],

        'fields' => [
            'name'                  => '名称',
            'description'           => '描述',
            'assign-user'           => '分配用户',
            'permission-type'       => '权限类型',
            'access-control'        => '访问控制',
            'general'               => '一般',
            'token-settings'        => '令牌设置',
            'valid-till'            => '有效期至',
            'rate-limit-per-minute' => '速率限制（每分钟）',
            'rate-limit-per-day'    => '速率限制（每天）',
            'never-expires'         => '永不过期',
            'expires-on'            => '到期日',
            'unlimited'             => '无限',
            'limit-to'              => '限制为',
            'requests-per-minute'   => '请求/分钟',
            'requests-per-day'      => '请求/天',
            'select-admin'          => '选择管理员',
            'no-available-admins'   => '没有可用的管理员 - 每个管理员都已经拥有一个活动令牌。',
            'same-as-web-hint'      => '令牌将实时反映分配的管理员的当前角色权限。',
            'ip-allowlist'          => 'IP白名单',
            'ip-any'                => '任意IP（默认）',
            'ip-restricted'         => '限制特定IP',
            'ip-list-hint'          => '每行一个条目。支持 IPv4、IPv6 和 CIDR（例如 10.0.0.0/24 或 2001:db8::/32）。留空以允许所有 IP。',
        ],

        'permission_type' => [
            'all'         => '全部',
            'custom'      => '定制',
            'same_as_web' => '与 Web 权限相同',
        ],

        'status' => [
            'draft'       => '吃水',
            'active'      => '活跃',
            'revoked'     => '已撤销',
            'regenerated' => '再生',
        ],

        'datagrid' => [
            'id'              => '身份证号',
            'name'            => '名称',
            'admin'           => '管理员',
            'token'           => '代币',
            'status'          => '状态',
            'permission-type' => '权限类型',
            'expires-at'      => '有效期至',
            'last-used-at'    => '最后使用',
            'created-at'      => '创建于',
            'edit'            => '编辑',
            'revoke'          => '撤销',
        ],

        'messages' => [
            'draft-created'          => '创建集成。生成令牌以开始使用它。',
            'updated'                => '集成更新成功。',
            'generated'              => '生成令牌。立即复制它 - 它不会再次显示。',
            'regenerated'            => '令牌已重新生成。立即复制新令牌 - 它不会再次显示。',
            'revoked'                => '令牌已成功撤销。',
            'generate-only-draft'    => '只有草稿集成才能生成其令牌。',
            'regenerate-only-active' => '只有活跃的代币才能重新生成。',
            'cannot-edit-historic'   => '无法编辑已撤销或重新生成的令牌。',
            'already-inactive'       => '该令牌已处于非活动状态。',
        ],

        'errors' => [
            'admin-has-token' => '所选管理员已拥有有效的集成令牌。',
        ],

        'validation' => [
            'ip-invalid'           => '每个允许的 IP 必须是有效的 IPv4 或 IPv6 地址（支持 CIDR 表示法）。',
            'cidr-prefix-invalid'  => 'CIDR 前缀对于给定的 IP 版本无效。',
        ],

        'configuration' => [
            'api' => [
                'title' => '应用程序编程接口',
                'info'  => 'Bagisto API 及其管理模块的设置。',
            ],
            'integration' => [
                'title' => '整合',
                'info'  => '管理用于颁发管理 API 令牌的 API 集成插件。',
            ],
            'settings' => [
                'title'  => '模块设置',
                'info'   => '启用或禁用 API 集成插件。禁用后，其侧边栏菜单将被隐藏，其页面将返回 404。',
                'enable' => '启用API集成模块',
            ],
        ],

        'emails' => [
            'generated' => [
                'subject'  => '生成了新的 API 令牌：:name',
                'greeting' => '刚刚在您的帐户上生成了名为“:name”的 API 集成令牌。',
            ],
            'regenerated' => [
                'subject'  => '您的 API 令牌已重新生成：:name',
                'greeting' => '名为“:name”的 API 集成令牌刚刚重新生成。之前的令牌已停止工作 - 只有新的令牌才有效。',
            ],
            'revoked' => [
                'subject'  => '您的 API 令牌已撤销：:name',
                'greeting' => '名为“:name”的 API 集成令牌已被撤销。任何使用它的客户端都将失去访问权限。',
            ],

            'details' => [
                'name' => '代币名称',
                'date' => '日期',
                'ip'   => '来自IP',
            ],

            'revoke-hint'   => '如果您没有预料到这一点，请立即使用下面的按钮撤销令牌。',
            'revoke-btn'    => '撤销此令牌',
            'revoke-expiry' => '该撤销链接的有效期为 7 天。之后，登录管理面板来管理令牌。',
            'no-action'     => '无需采取任何行动——这封电子邮件只是一个确认。',
        ],

        'revoke-confirmation' => [
            'title'                    => '撤销 API 令牌',
            'success-title'            => '令牌已撤销',
            'success-message'          => '令牌“:name”已被撤销。任何使用它的客户端都会立即失去访问权限。',
            'already-inactive-title'   => '令牌已处于非活动状态',
            'already-inactive-message' => '令牌“:name”已被撤销或重新生成。无需采取进一步行动。',
        ],

        'confirm' => [
            'generate' => [
                'title'   => '生成令牌',
                'message' => '现在生成令牌吗？明文只会显示一次 - 在离开页面之前复制它。',
            ],
            'regenerate' => [
                'title'   => '重新生成令牌',
                'message' => '重新生成令牌？旧的令牌将立即停止工作，新的明文将仅显示一次。',
            ],
            'revoke' => [
                'title'   => '撤销令牌',
                'message' => '撤销这个令牌？任何使用它的客户端都将立即失去访问权限。此操作无法撤消。',
            ],
        ],
    ],
];
