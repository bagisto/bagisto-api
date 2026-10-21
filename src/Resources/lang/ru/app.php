<?php

return [

    'graphql' => [
        'headers' => [
            'unknown-channel' => 'Канал ":code" из заголовка X-Channel не существует.',
            'storefront-key-required' => 'Требуется заголовок X-STOREFRONT-KEY',
            'storefront-key-invalid' => 'Недействительный ключ витрины',
            'storefront-key-rate-limited' => 'Превышен лимит запросов. Повторите попытку через :seconds сек.',
        ],

        'cart' => [
            'authentication-required' => 'Требуется токен аутентификации',
            'invalid-token' => 'Недействительный или истекший токен аутентификации',
            'unauthorized-access' => 'Несанкционированный доступ к корзине',
            'authenticated-only' => 'Только аутентифицированные пользователи могут получить свои корзины',
            'merge-requires-auth' => 'Для объединения гостевой корзины требуется аутентификация',
            'unknown-operation' => 'Неизвестная операция с корзиной',

            'cart-not-found' => 'Корзина не найдена',
            'guest-cart-not-found' => 'Гостевая корзина не найдена',
            'product-not-found' => 'Продукт не найден',

            'product-id-quantity-required' => 'Требуются ID продукта и количество',
            'cart-item-id-quantity-required' => 'Требуются ID товара в корзине и количество',

            'file-token-invalid' => 'Ссылка на загруженный файл недействительна или истекла. Загрузите файл повторно.',
            'file-token-forbidden' => 'Этот загруженный файл не принадлежит вашей корзине.',
            'customizable-option-required' => 'Настраиваемая опция ":label" обязательна.',
            'customizable-file-option-invalid' => 'Выбранная опция не является файловой опцией для этого продукта.',
            'customizable-file-invalid' => 'Файл отсутствует, имеет неподдерживаемое расширение или слишком велик.',
            'cart-item-id-required' => 'Требуется ID товара в корзине',
            'item-ids-required' => 'Требуется список ID товаров',

            'event-booking-quantity-not-changeable' => 'Количество продукта бронирования мероприятия нельзя изменить. Количество определяется выбором билетов.',
            'appointment-booking-quantity-not-changeable' => 'Количество продукта бронирования встречи нельзя изменить.',
            'coupon-code-required' => 'Требуется код купона',
            'address-data-required' => 'Требуются страна, область и почтовый индекс',
            'grouped-qty-required' => 'Для сгруппированного продукта требуются выбранные количества. Передайте groupedQty в виде строки JSON, например {"101":2,"102":1}.',
            'grouped-qty-must-include-all' => 'Для сгруппированного продукта требуются количества для всех связанных продуктов. Отсутствующие ID: :ids.',
            'grouped-qty-invalid-associated' => 'Количества сгруппированного продукта содержат недействительные ID связанных продуктов: :ids.',
            'grouped-qty-invalid-quantity' => 'Указано недопустимое количество для связанного продукта с ID :id. Количество должно быть неотрицательным целым числом.',
            'bundle-qty-not-changeable' => 'Количество для опции комплекта ":option" нельзя изменить. Допустимое количество: :qty.',

            'add-product-failed' => 'Не удалось добавить продукт в корзину',
            'update-item-failed' => 'Не удалось обновить товар в корзине',
            'remove-item-failed' => 'Не удалось удалить товар из корзины',
            'apply-coupon-failed' => 'Не удалось применить купон',
            'remove-coupon-failed' => 'Не удалось удалить купон',
            'coupon-applied' => 'Купон успешно применен',
            'coupon-removed' => 'Купон успешно удален',
            'move-to-wishlist-failed' => 'Не удалось переместить товар в список желаний',
            'estimate-shipping-failed' => 'Не удалось рассчитать стоимость доставки',

            'product-added-successfully' => 'Продукт успешно добавлен в корзину',
            'cart-item-updated-successfully' => 'Товар в корзине успешно обновлен',
            'guest-cart-merged' => 'Гостевая корзина успешно объединена',
            'using-authenticated-cart' => 'Используется корзина аутентифицированного клиента',
            'cart-item-not-found' => 'Товар в корзине не найден',
            'new-guest-cart-created' => 'Создана новая гостевая корзина с уникальным токеном сессии',
            'select-items-to-remove' => 'Пожалуйста, выберите товары для удаления',
            'select-items-to-move-wishlist' => 'Пожалуйста, выберите товары для перемещения в список желаний',
            'invalid-or-expired-token' => 'Токен корзины недействителен или истек. Пожалуйста, создайте новую корзину.',
            'invalid-token-of-login-user' => 'Токен вошедшего пользователя недействителен.',
            'booking-note-required' => 'booking.note обязателен для бронирования столика.',
            'booking-date-required' => 'booking.date обязателен, если booking.slot задан как отформатированный интервал времени.',
            'booking-qty-required' => 'booking.qty обязателен для бронирования мероприятия.',
            'booking-ticket-qty-invalid' => 'Количество билетов на мероприятие должно быть неотрицательным числом.',
            'booking-ticket-required' => 'Выберите количество хотя бы для одного билета для бронирования мероприятия.',
            'booking-slot-unavailable' => 'Выбранный слот недоступен на указанную дату.',
            'invalid-quantity' => 'Количество должно быть не менее 1',
            'product-id-required' => 'Требуется ID товара',
            'shipping-address-required' => 'Для расчёта доставки требуются страна и почтовый индекс',
        ],

        'token-verification' => [
            'invalid-operation' => 'Недействительная операция',
            'invalid-input-data' => 'Недействительные входные данные',
            'token-required' => 'Требуется токен',
            'invalid-token-format' => 'Недействительный формат токена',
            'token-not-found-or-expired' => 'Токен не найден или истек',
            'customer-not-found' => 'Клиент не найден',
            'customer-account-suspended' => 'Учетная запись клиента приостановлена',
            'error-verifying-token' => 'Ошибка при проверке токена',
            'token-is-valid' => 'Токен действителен',
            'unauthenticated' => 'Не выполнена аутентификация. Пожалуйста, войдите, чтобы выполнить это действие',
        ],

        'forgot-password' => [
            'invalid-operation' => 'Недействительная операция',
            'invalid-input-data' => 'Недействительные входные данные',
            'email-required' => 'Требуется адрес электронной почты',
            'reset-link-sent' => 'Ссылка для сброса успешно отправлена на вашу электронную почту',
            'email-not-found' => 'Адрес электронной почты не найден',
            'throttled' => 'Слишком много попыток сброса. Пожалуйста, подождите, прежде чем повторить попытку.',
            'error-sending-reset-link' => 'Произошла ошибка при отправке ссылки для сброса',
        ],

        'reset-password' => [
            'failed' => 'Не удалось сбросить пароль. Пожалуйста, попробуйте ещё раз.',
        ],

        'rate-limit' => [
            'too-many-attempts' => 'Слишком много попыток. Повторите попытку через :seconds сек.',
        ],

        'logout' => [
            'invalid-operation' => 'Недействительная операция',
            'invalid-input-data' => 'Недействительные входные данные',
            'token-required' => 'Требуется токен',
            'invalid-token-format' => 'Недействительный формат токена',
            'logged-out-successfully' => 'Выход выполнен успешно',
            'token-not-found-or-expired' => 'Токен не найден или уже истек',
            'error-during-logout' => 'Ошибка при выходе из системы',
            'unauthenticated' => 'Не выполнена аутентификация. Пожалуйста, войдите, чтобы выполнить это действие',
        ],

        'address' => [
            'deleted-successfully' => 'Адрес успешно удален',
            'authentication-required' => 'Требуется токен аутентификации',
            'invalid-token' => 'Недействительный или истекший токен',
            'unknown-operation' => 'Неизвестная операция',
            'address-id-required' => 'Требуется ID адреса',
            'address-not-found' => 'Адрес не найден или не принадлежит этому клиенту',
            'retrieved' => 'Адреса успешно получены',
            'fetch-failed' => 'Не удалось получить адреса:',
            'field-required' => 'Поле :field обязательно',
        ],

        'customer-profile' => [
            'authentication-required' => 'Требуется токен аутентификации. Пожалуйста, укажите токен во входных данных запроса',
            'invalid-token' => 'Недействительный или истекший токен',
            'profile-updated' => 'Профиль клиента успешно обновлен',
        ],

        'customer' => [
            'password-mismatch' => 'Пароль и подтверждение пароля не совпадают',
            'confirm-password-required' => 'При изменении пароля требуется подтверждение пароля',
            'current-password-required' => 'Для смены пароля требуется текущий пароль',
            'current-password-incorrect' => 'Введенный текущий пароль неверен',
            'verify-email-to-login' => 'Ваша учетная запись создана. Пожалуйста, подтвердите адрес электронной почты, чтобы войти.',
            'delete-password-required' => 'Для удаления учетной записи требуется ваш пароль',
            'delete-password-incorrect' => 'Введенный пароль неверен',
            'delete-has-active-orders' => 'Вашу учетную запись нельзя удалить, пока у вас есть ожидающие или обрабатываемые заказы',
            'unauthenticated' => 'Не аутентифицирован. Пожалуйста, войдите в систему, чтобы выполнить это действие',
            'id-required' => 'Требуется ID клиента',
            'invalid-id-format' => 'Недопустимый формат ID. Ожидается формат IRI, например "/api/admin/customers/1", или числовой ID',
            'not-found' => 'Клиент не найден',
            'phone-special-chars-not-allowed' => 'Номер мобильного телефона может содержать только цифры. Специальные символы не допускаются',
            'email-already-taken' => 'Этот адрес электронной почты уже зарегистрирован в другой учетной записи',
            'invalid-gender' => 'Недопустимое значение пола ":gender". Допустимые значения: :valid',
        ],

        'product-review' => [
            'id-required' => 'Требуется ID отзыва о продукте',
            'invalid-id-format' => 'Недопустимый формат ID. Ожидается формат IRI, например "/api/shop/reviews/1", или числовой ID',
            'not-found' => 'Отзыв о продукте с ID ":id" не найден',
            'item-not-found' => 'Элемент ":id" не найден.',
            'product-id-required' => 'Требуется ID продукта',
            'product-not-found' => 'Продукт не найден',
            'rating-invalid' => 'Оценка должна быть от 1 до 5',
            'title-required' => 'Требуется заголовок отзыва',
            'comment-required' => 'Требуется текст отзыва',
            'review-disabled' => 'Отзывы о продуктах в настоящее время отключены',
            'guest-review-disabled' => 'Гостевые отзывы не разрешены. Пожалуйста, войдите, чтобы оставить отзыв',
            'owner-required' => 'Пожалуйста, войдите, чтобы управлять своим отзывом',
            'not-owned' => 'Этот отзыв написан не вами, и его нельзя изменить или удалить',
            'invalid-attachments' => 'Недопустимый формат вложений.',
            'invalid-product-id-format' => 'Недопустимый формат ID товара. Ожидается формат IRI, например "/api/shop/products/1", или числовой ID',
        ],

        'customer-review' => [
            'id-required' => 'Требуется ID отзыва клиента',
            'invalid-id-format' => 'Недопустимый формат ID. Ожидается формат IRI, например "/api/shop/customer-reviews/1", или числовой ID',
            'not-found' => 'Отзыв клиента с ID ":id" не найден',
        ],

        'newsletter' => [
            'error-during-subscription' => 'Сейчас не удается оформить подписку. Пожалуйста, повторите попытку позже',
        ],

        'contact-us' => [
            'success' => 'Ваш запрос успешно отправлен. Мы скоро свяжемся с вами',
            'failed' => 'Сейчас не удается отправить ваш запрос. Пожалуйста, повторите попытку позже',
            'invalid-input' => 'Недопустимые данные контактной формы',
        ],

        'customer-order' => [
            'id-required' => 'Требуется ID заказа клиента',
            'invalid-id-format' => 'Недопустимый формат ID. Ожидается формат IRI, например "/api/shop/customer-orders/1", или числовой ID',
            'not-found' => 'Заказ клиента с ID ":id" не найден',
        ],

        'return' => [
            'not-found' => 'Запрос на возврат не найден.',
            'disabled' => 'Возвраты не включены для этого магазина.',
            'invalid-input' => 'Сведения о возврате отсутствуют или недействительны.',
            'invalid-resolution-type' => 'Тип решения должен быть "return" или "cancel_items".',
            'agreement-required' => 'Вы должны принять политику возврата.',
            'invalid-order' => 'Выбранный заказ недействителен.',
            'invalid-item' => 'Выбранный товар не подлежит возврату.',
            'qty-exceeds' => 'Запрошенное количество превышает количество, доступное для возврата.',
            'invalid-package-condition' => 'Состояние упаковки должно быть "open" или "packed".',
            'invalid-image' => 'Один или несколько загруженных файлов имеют неподдерживаемый тип.',
            'invalid-custom-field' => 'Неизвестное пользовательское поле ":id".',
            'custom-field-required' => 'Поле ":field" обязательно.',
            'invalid-custom-field-value' => 'Значение для ":field" не входит в число допустимых вариантов.',
            'created' => 'Запрос на возврат успешно создан.',
            'canceled' => 'Запрос на возврат успешно отменен.',
            'already-canceled' => 'Запрос на возврат уже отменен.',
            'cancel-not-allowed' => 'Этот запрос на возврат нельзя отменить.',
            'close-not-allowed' => 'Этот запрос на возврат нельзя закрыть.',
            'reopened' => 'Запрос на возврат успешно открыт повторно.',
            'reopen-not-allowed' => 'Этот запрос на возврат нельзя открыть повторно.',
            'closed' => 'Запрос на возврат успешно закрыт.',
            'message-sent' => 'Сообщение успешно отправлено.',
            'attachment-not-found' => 'Вложение сообщения не найдено.',
        ],

        'eu-withdrawal' => [
            'not-found' => 'Заявление об отказе не найдено.',
            'order-not-found' => 'Заказ не найден или не подлежит отказу.',
            'too-many-attempts' => 'Слишком много попыток. Пожалуйста, повторите попытку через :seconds сек.',
            'created' => 'Заявление об отказе успешно подано.',
        ],

        'customer-order-shipment' => [
            'id-required' => 'Требуется ID отгрузки',
            'not-found' => 'Отгрузка с ID ":id" не найдена или не принадлежит этому клиенту',
            'item-not-found' => 'Позиция отгрузки с ID ":id" не найдена или не принадлежит этому клиенту',
            'invalid-id-format' => 'Недопустимый формат ID. Ожидается формат IRI, например "/api/shop/customer-order-shipments/1", или числовой ID',
        ],

        'cancel-order' => [
            'order-id-required' => 'Для отмены заказа требуется ID заказа',
            'not-found' => 'Заказ с ID ":id" не найден или не принадлежит этому клиенту',
            'cancel-success' => 'Заказ успешно отменен',
            'cancel-failed' => 'Заказ нельзя отменить. Возможно, он уже обработан, отправлен или отменен',
        ],

        'reorder' => [
            'order-id-required' => 'Для повторного заказа требуется ID заказа',
            'not-found' => 'Заказ с ID ":id" не найден или не принадлежит этому клиенту',
            'reorder-success' => 'Товары из вашего предыдущего заказа (:count) добавлены в корзину',
            'no-items-added' => 'Не удалось добавить товары в корзину. Возможно, продуктов нет в наличии или они недоступны',
        ],

        'customer-downloadable-product' => [
            'id-required' => 'Требуется ID загружаемого продукта клиента',
            'invalid-id-format' => 'Недопустимый формат ID. Ожидается формат IRI, например "/api/shop/customer-downloadable-products/1", или числовой ID',
            'not-found' => 'Загружаемый продукт клиента с ID ":id" не найден',

            'download' => [
                'unauthenticated' => 'Нет доступа: требуется аутентификация клиента.',
                'not-found' => 'Загружаемый товар не найден.',
                'pending' => 'Загрузка ожидает. Пожалуйста, дождитесь выставления счета по заказу.',
                'limit-exceeded' => 'Превышен лимит загрузок.',
                'no-downloads-remaining' => 'Больше нет доступных загрузок.',
                'file-not-found' => 'Файл не найден.',
                'link-not-found' => 'Ссылка для загрузки не найдена.',
            ],
        ],

        'customer-invoice' => [
            'id-required' => 'Требуется ID счета клиента',
            'invalid-id-format' => 'Недопустимый формат ID. Ожидается формат IRI, например "/api/shop/customer-invoices/1", или числовой ID',
            'not-found' => 'Счет клиента с ID ":id" не найден',
        ],

        'auth' => [
            'no-token-provided' => 'Токен аутентификации не предоставлен. Пожалуйста, укажите токен в заголовке Authorization как "Bearer <token>" или в поле input.token',
            'invalid-or-expired-token' => 'Недействительный или истекший токен',
            'request-not-found' => 'Запрос не найден в контексте',
            'token-required' => 'Authentication token is required. Please provide the token either in the GraphQL mutation input field or in the Authorization header as "Bearer <token>"',
            'unknown-resource' => 'Неизвестный ресурс',
            'cannot-update-other-profile' => 'Не авторизовано: невозможно обновить профиль другого клиента',
        ],

        'upload' => [
            'invalid-base64' => 'Недействительные данные изображения в кодировке base64',
            'size-exceeds-limit' => 'Размер изображения не должен превышать 5 МБ',
            'invalid-format' => 'Недействительный формат изображения. Пожалуйста, предоставьте изображение в кодировке base64 со схемой data URI (data:image/jpeg;base64,...)',
            'failed' => 'Не удалось загрузить изображение',
        ],

        'attribute' => [
            'code-already-exists' => 'Код атрибута уже существует',
            'option-id-required' => 'Параметр "attributeId" обязателен при прямом запросе опций атрибута',
            'id-required' => 'Требуется ID атрибута',
            'invalid-id-format' => 'Недопустимый формат ID. Ожидается формат IRI, например "/api/shop/attributes/1", или числовой ID',
            'not-found' => 'Атрибут не найден',
        ],

        'attribute-option' => [
            'id-required' => 'Требуется ID опции атрибута',
            'invalid-id-format' => 'Недопустимый формат ID. Ожидается формат IRI, например "/api/shop/attribute-options/1", или числовой ID',
            'not-found' => 'Опция атрибута не найдена',
        ],

        'channel' => [
            'id-required' => 'Требуется ID канала',
            'invalid-id-format' => 'Недопустимый формат ID. Ожидается формат IRI, например "/api/shop/channels/1", или числовой ID',
            'not-found' => 'Канал не найден',
        ],

        'country-state' => [
            'id-required' => 'Требуется ID области страны',
            'invalid-id-format' => 'Недопустимый формат ID. Ожидается формат IRI, например "/api/shop/country-states/1", или числовой ID',
            'not-found' => 'Область страны не найдена',
            'country-id-required' => 'Требуется ID страны',
        ],

        'country' => [
            'id-required' => 'Требуется ID страны',
            'invalid-id-format' => 'Недопустимый формат ID. Ожидается формат IRI, например "/api/shop/countries/1", или числовой ID',
            'not-found' => 'Страна не найдена',
        ],

        'login' => [
            'invalid-credentials' => 'Недействительный адрес электронной почты или пароль',
            'account-suspended' => 'Ваша учетная запись приостановлена',
            'account-inactive' => 'Ваш аккаунт ожидает одобрения администратора',
            'email-not-verified' => 'Сначала подтвердите свой адрес электронной почты.',
            'successful' => 'Вы успешно вошли в систему',
            'invalid-request' => 'Недействительный запрос на вход',
        ],

        'social-login' => [
            'signed-in' => 'Вход выполнен успешно.',
            'token-required' => 'Требуется токен социального входа.',
            'invalid-token' => 'Токен социального входа недействителен или истёк. Пожалуйста, попробуйте снова.',
            'google-not-configured' => 'Вход через Google не настроен для этого магазина.',
            'wrong-audience' => 'Этот токен был выдан для другого приложения.',
            'email-required' => 'Провайдер не предоставил адрес электронной почты. Зарегистрируйтесь по электронной почте.',
            'account-inactive' => 'Ваш аккаунт ожидает одобрения администратора',
            'provider-not-supported' => 'Этот провайдер социального входа не поддерживается.',
            'provider-disabled' => 'Этот провайдер социального входа не включён.',
        ],

        'checkout' => [
            'invalid-input' => 'Недействительные входные данные для операции оформления заказа',
            'billing-address-required' => 'Требуется платежный адрес',
            'shipping-address-required' => 'Для отправлений требуется адрес доставки',
            'address-save-failed' => 'Не удалось сохранить адрес',
            'address-saved' => 'Адрес успешно сохранен',
            'shipping-method-required' => 'Требуется способ доставки',
            'invalid-shipping-method' => 'Недействительный или недоступный способ доставки',
            'shipping-method-save-failed' => 'Не удалось сохранить способ доставки',
            'shipping-method-saved' => 'Способ доставки успешно сохранен',
            'shipping-method-error' => 'Ошибка при сохранении способа доставки',
            'payment-method-required' => 'Требуется способ оплаты',
            'invalid-payment-method' => 'Недействительный или недоступный способ оплаты',
            'payment-method-save-failed' => 'Не удалось сохранить способ оплаты',
            'payment-method-saved' => 'Способ оплаты успешно сохранен',
            'payment-method-error' => 'Ошибка при сохранении способа оплаты',
            'order-placed' => 'Заказ успешно оформлен',
            'order-redirect-required' => 'Этот способ оплаты требует, чтобы покупатель завершил оплату на платежном шлюзе. Перенаправьте его на redirectUrl; заказ будет создан после подтверждения оплаты шлюзом.',
            'order-creation-failed' => 'Не удалось создать заказ: ID заказа равен null или заказ не сохранен',
            'order-retrieval-failed' => 'Не удалось получить созданный заказ',
            'order-creation-error' => 'Не удалось создать заказ',
            'cart-empty' => 'Корзина пуста',
            'account-suspended' => 'Ваша учетная запись приостановлена. Пожалуйста, обратитесь в службу поддержки.',
            'account-inactive' => 'Ваша учетная запись неактивна. Пожалуйста, обратитесь в службу поддержки.',
            'minimum-order-not-met' => 'Минимальная сумма заказа составляет :amount',
            'guest-checkout-not-allowed' => 'Пожалуйста, войдите, чтобы оформить заказ. Оформление заказа без регистрации недоступно для этой корзины.',
            'email-required' => 'Для создания заказа требуется адрес электронной почты',
            'unknown-operation' => 'Неизвестная операция оформления заказа',
        ],

        'customer-addresses' => [
            'token-required' => 'Для получения адресов клиента требуется токен',
            'invalid-or-expired-token' => 'Недействительный или истекший токен',
            'token-validation-failed' => 'Не удалось проверить токен',
        ],

        'product' => [
            'not-found' => 'Продукт не найден',
            'not-found-with-sku' => 'Продукт с таким SKU не найден',
            'not-found-with-url-key' => 'Продукт с таким URL-ключом не найден',
            'parameters-required' => 'Необходимо указать хотя бы один из следующих параметров: "sku", "id", "urlKey"',
            'type' => 'Тип продукта',
            'attribute-family' => 'Семейство атрибутов',
            'sku' => 'Артикул',
            'name' => 'Название',
            'description' => 'Описание',
            'short-description' => 'Краткое описание',
            'status' => 'Статус',
            'new' => 'Новинка',
            'featured' => 'Рекомендуемое',
            'price' => 'Цена',
            'special-price' => 'Специальная цена',
            'weight' => 'Вес',
            'cost' => 'Себестоимость',
            'length' => 'Длина',
            'width' => 'Ширина',
            'height' => 'Высота',
            'color' => 'Цвет',
            'size' => 'Размер',
            'brand' => 'Бренд',
            'super-attributes' => 'Суператрибуты',
            'id-required' => 'Требуется ID товара',
            'invalid-id-format' => 'Недопустимый формат ID. Ожидается формат IRI, например "/api/shop/products/1", или числовой ID',
        ],

        'downloadable-product' => [
            'download-link-not-found' => 'Ссылка для скачивания не найдена или истекла',
            'purchased-link-not-found' => 'Купленная ссылка не найдена',
            'file-not-found' => 'Файл не найден',
            'download-successful' => 'Файл готов к скачиванию',
            'token-required' => 'Требуется токен скачивания',
            'invalid-token' => 'Неверный или истекший токен скачивания',
            'token-expired' => 'Токен скачивания истек. Пожалуйста, создайте новый',
            'access-denied' => 'Доступ запрещен: У вас нет разрешения на скачивание этого файла',
            'redirect-external-url' => 'Перенаправление на внешний URL скачивания',
            'file-error' => 'При обработке вашего запроса на скачивание произошла ошибка',
            'unauthorized-access' => 'Несанкционированный доступ к ресурсу скачивания',
            'unauthenticated' => 'Нет доступа: требуется аутентификация клиента',
            'link-id-required' => 'Требуется downloadableLinkPurchasedId',
            'link-not-found' => 'Ссылка для загрузки не найдена',
            'not-owner' => 'Нет доступа: эта ссылка для загрузки не принадлежит вам',
            'pending' => 'Загрузка ожидает. Пожалуйста, дождитесь выставления счета по заказу.',
            'limit-exceeded' => 'Превышен лимит загрузок',
            'no-downloads-left' => 'Больше нет доступных загрузок',
        ],

        'booking-slot' => [
            'id-required' => 'Требуется ID продукта бронирования.',
            'date-required' => 'Требуется дата.',
            'product-not-found' => 'Продукт бронирования не найден.',
            'invalid-type' => 'Этот тип бронирования не поддерживается.',
            'invalid-time-range' => 'Недопустимый интервал времени.',
            'invalid-time-format' => 'Недопустимый формат времени.',
        ],

        'resolver' => [
            'no-resolve-info' => 'GraphQL ResolveInfo недоступен в контексте. Невозможно определить класс модели.',
            'unknown-resource-class' => 'Невозможно определить класс ресурса по возвращаемому типу GraphQL',
        ],

        'docs' => [
            'openapi-unavailable' => 'Не удалось создать экземпляр OpenAPI Factory',
        ],

        'locale' => [
            'id-required' => 'Требуется ID локали',
            'invalid-id-format' => 'Недопустимый формат ID. Ожидается формат IRI, например "/api/shop/locales/1", или числовой ID',
            'not-found' => 'Локаль не найдена',
        ],

        'category' => [
            'id-required' => 'Требуется ID категории',
            'invalid-id-format' => 'Недопустимый формат ID. Ожидается формат IRI, например "/api/shop/categories/1", или числовой ID',
            'not-found' => 'Категория не найдена',
        ],

        'compare-item' => [
            'id-required' => 'ID элемента сравнения обязателен',
            'invalid-id-format' => 'Недействительный формат ID. Ожидается формат IRI как "/api/shop/compare-items/1" или числовой ID',
            'not-found' => 'Элемент сравнения не найден',
            'product-id-required' => 'ID продукта обязателен',
            'customer-id-required' => 'ID клиента обязателен',
            'product-not-found' => 'Продукт не найден',
            'customer-not-found' => 'Клиент не найден',
            'already-exists' => 'Этот продукт уже в вашем списке сравнения',
            'delete-all-success' => 'Все товары для сравнения успешно удалены',
        ],

        'wishlist' => [
            'id-required' => 'Требуется ID товара в списке желаний',
            'invalid-id-format' => 'Недопустимый формат ID. Ожидается формат IRI, например "/api/shop/wishlists/1", или числовой ID',
            'not-found' => 'Товар в списке желаний не найден',
            'product-id-required' => 'Требуется ID продукта',
            'customer-id-required' => 'Требуется ID клиента',
            'product-not-found' => 'Продукт не найден',
            'product-disabled' => 'Этот продукт в настоящее время отключен',
            'customer-not-found' => 'Клиент не найден',
            'already-exists' => 'Этот продукт уже есть в вашем списке желаний',
            'added' => 'Товар успешно добавлен в список желаний',
            'removed' => 'Товар успешно удален из списка желаний',
            'product-removed' => 'Продукт удален',
            'wishlist-item-id-required' => 'Требуется ID товара в списке желаний',
            'invalid-quantity' => 'Количество должно быть больше 0',
            'move-to-cart-missing-options' => 'У продукта не заданы обязательные опции. Пожалуйста, настройте его вручную',
            'moved-to-cart-success' => 'Товар успешно перемещен в корзину',
            'delete-all-success' => 'Все товары из списка желаний успешно удалены',
        ],

        'gdpr' => [
            'disabled' => 'Запросы данных GDPR отключены. Пожалуйста, включите GDPR в конфигурации администратора.',
            'unauthenticated' => 'Чтобы управлять запросами данных GDPR, необходимо войти в систему.',
            'not-found' => 'Запрос данных GDPR не найден',
            'type-required' => 'Требуется тип запроса',
            'type-invalid' => 'Тип запроса должен быть "delete" или "update"',
            'message-required' => 'Требуется сообщение',
            'raised' => 'Ваш запрос данных GDPR успешно создан.',
            'revoked' => 'Ваш запрос данных GDPR успешно отозван.',
            'revoke-not-allowed' => 'Этот запрос данных GDPR больше нельзя отозвать.',
            'deleted' => 'Ваш запрос данных GDPR успешно удален.',
        ],

        'section' => [
            'id-required' => 'Требуется ID раздела',
            'invalid-id-format' => 'Недопустимый формат ID. Ожидается формат IRI, например "/api/shop/sections/1", или числовой ID',
            'not-found' => 'Раздел не найден',
        ],

        'install' => [
            'starting' => 'Запуск установки API Platform...',
            'completed-success' => '✓ Установка API Platform успешно завершена!',
            'failed' => '✗ Ошибка установки: ',

            'provider-already-registered' => 'ℹ Service Provider уже зарегистрирован',
            'provider-not-registered' => '⚠ Не удалось автоматически зарегистрировать Service Provider. Добавьте :provider в bootstrap/providers.php вручную.',
            'provider-registered' => '✓ Service Provider зарегистрирован в bootstrap/providers.php',
            'provider-file-not-found' => 'Файл не найден: :file',
            'provider-permission-denied' => 'Доступ запрещен: невозможно записать в :file. Пожалуйста, проверьте права доступа к файлу.',

            'config-already-exists' => 'ℹ Файл конфигурации уже существует',
            'config-published' => '✓ Конфигурация API Platform опубликована в config/',
            'config-source-not-found' => 'Исходный файл конфигурации не найден: :file',
            'config-permission-denied' => 'Доступ запрещен: невозможно записать в :directory. Пожалуйста, проверьте права доступа к каталогу.',

            'translatable-not-found' => 'ℹ TranslatableModel не найден - пропуск',
            'translatable-already-abstract' => 'ℹ TranslatableModel уже является абстрактным',
            'translatable-made-abstract' => '✓ TranslatableModel сделан абстрактным',
            'translatable-permission-denied' => 'Доступ запрещен: невозможно записать в :file. Пожалуйста, проверьте права доступа к файлу.',

            'providers-already-registered' => 'ℹ Провайдеры API Platform уже зарегистрированы',
            'providers-registered' => '✓ Провайдеры API Platform зарегистрированы в bootstrap/app.php',
            'providers-file-not-found' => 'Файл не найден: :file',
            'providers-permission-denied' => 'Доступ запрещен: невозможно записать в :file. Пожалуйста, проверьте права доступа к файлу.',
            'providers-not-found' => 'Не удалось найти создание Application в bootstrap/app.php',

            'assets-published' => '✓ Ресурсы пакета опубликованы',
            'running-migrations' => 'Выполнение миграций базы данных...',
            'migrations-completed' => '✓ Миграции базы данных завершены',
            'clearing-caches' => 'Очистка и оптимизация кэша...',
            'caches-optimized' => '✓ Кэш очищен и оптимизирован',
            'generating-api-key' => 'Генерация API-ключа...',
            'api-key-generated' => '✓ API-ключ успешно сгенерирован',
            'api-key-already-exists' => 'ℹ API-ключ магазина по умолчанию уже существует - генерация пропущена',

            'migrations-error' => 'Не удалось выполнить миграции базы данных.',
            'migrations-error-running' => 'Ошибка выполнения миграций базы данных: :error',
            'cache-clear-error' => 'Не удалось очистить кэш.',
            'cache-optimize-error' => 'Не удалось выполнить оптимизацию.',
            'cache-error' => 'Ошибка при очистке и оптимизации кэша: :error',
            'api-key-generation-error' => 'Не удалось сгенерировать API-ключ.',
            'api-key-error' => 'Ошибка генерации API-ключа: :error',

            'key-management-required' => 'Для действия :action требуется --key',
            'key-rotation-error' => 'Ошибка ротации ключа: :error',
            'key-deactivation-error' => 'Ошибка деактивации ключа: :error',
            'key-not-found' => 'API-ключ не найден: :identifier',

            'maintenance-starting' => '🔄 Запуск обслуживания API-ключей...',
            'maintenance-complete' => '✅ Обслуживание API-ключей завершено',
            'cleanup-expired-keys' => '🧹 Очистка истекших ключей...',
            'cleanup-expired-none' => '   ℹ️ Нет истекших ключей для очистки',
            'invalidate-deprecated' => '⚠️ Аннулирование устаревших ключей...',
            'invalidate-deprecated-none' => '   ℹ️ Нет устаревших ключей для аннулирования',
            'notify-expiring' => '📧 Отправка уведомлений об истечении срока...',
            'notify-expiring-none' => '   ℹ️ Нет ключей, требующих уведомлений',

            'key-rotated-success' => '✅ Ключ успешно ротирован!',
            'key-deactivated-success' => '✅ Ключ успешно деактивирован!',
            'deactivation-cancelled' => 'Деактивация отменена.',
            'cleanup-cancelled' => 'Очистка отменена.',

            'key-status-title' => 'Статус ключа: :name',
            'key-status-active' => 'Активен: :status',
            'key-status-usable' => 'Пригоден к использованию: :status',
            'key-status-expired' => 'Истек: :status',
            'key-status-deprecated' => 'Устарел: :status',
            'key-status-expires-at' => 'Истекает: :expires',
            'key-status-days-until-expiry' => 'Дней до истечения: :days',
            'key-status-last-used' => 'Последнее использование: :date',
            'key-status-rotated-from' => 'Ротирован из: :key',
            'key-status-rotated-keys' => 'Ключей, ротированных из этого: :count',

            'policy-compliance-summary' => 'Сводка соответствия политике ротации API-ключей',
            'available-actions' => 'Доступные действия: rotate, deactivate, cleanup, status, expiring, unused, summary',

            'asset-linked-success' => '✓ Ресурсы API Platform успешно связаны',
            'symlink-create-failed' => 'Не удалось создать символическую ссылку, вместо этого ресурсы копируются...',
            'asset-copy-warning' => 'Предупреждение: не удалось связать или скопировать ресурсы API Platform. Может потребоваться ручная настройка.',
            'asset-copied-success' => '✓ Ресурсы API Platform успешно скопированы',

            'key-name-required' => 'Имя ключа витрины не может быть пустым.',
            'key-generated-success' => 'Ключ витрины успешно сгенерирован!',
            'key-details' => 'Сведения о ключе:',
            'key-secure-warning' => 'Храните этот ключ в безопасности! Он будет использоваться в заголовке X-STOREFRONT-KEY.',
            'key-share-warning' => 'Не публикуйте этот ключ и не фиксируйте его в системе контроля версий.',

            'key-already-exists' => 'Ключ витрины с именем \':name\' уже существует.',
            'cleanup-success' => '✅ Очищено истекших ключей: :count',
            'confirm-cleanup' => 'Все истекшие ключи будут мягко удалены. Продолжить?',
            'confirm-deactivate' => 'Вы уверены, что хотите деактивировать ключ: :name?',

            'old-key' => 'Старый ключ: :name',
            'old-key-id' => 'ID старого ключа: :id',
            'deprecation-date' => 'Дата устаревания: :date',
            'new-key' => 'Новый ключ: :name',
            'new-key-id' => 'ID нового ключа: :id',
            'new-key-value' => 'Значение нового ключа: :key',
            'expires-at' => 'Истекает: :date',
            'invalid-action' => 'Недопустимое действие: :action',
            'key-active' => 'Активен: :status',
            'key-usable' => 'Пригоден к использованию: :status',
            'key-expired' => 'Истек: :status',
            'key-deprecated' => 'Устарел: :status',
            'key-field-id' => 'ID',
            'key-field-name' => 'Имя',
            'key-field-key' => 'Ключ',
            'key-field-rate-limit' => 'Лимит запросов',
            'key-field-status' => 'Статус',
            'key-days' => ' дн.',
            'key-never' => 'Никогда',
            'key-requests-minute' => ' запросов/минуту',
            'key-unlimited' => 'Без ограничений',
            'rate-limit-exceeded' => 'Запрошенный лимит запросов превышает максимально допустимый :max. Устанавливается :max запросов/минуту.',

            'vendor-path-not-found' => 'Путь vendor API Platform не найден: :path',
            'assets-already-linked' => 'Ресурсы API Platform уже связаны: :path',
            'no-keys-expiring' => 'Нет ключей, истекающих в ближайшие :days дн.',
            'keys-expiring-title' => 'Ключи, истекающие в ближайшие :days дн.:',
            'key-display-format' => '• :name (ID: :id)',
            'key-expires-display' => '  Истекает: :date (осталось :days дн.)',
            'no-unused-keys' => 'Неиспользуемые ключи не найдены (> :days дн.)',
            'unused-keys-title' => 'Неиспользуемые ключи (> :days дн.):',
            'key-last-used-display' => '  Последнее использование: :date',
            'total-keys' => 'Всего ключей: :count',
            'valid-keys' => 'Действительных ключей: :count',
            'expired-keys' => 'Истекших ключей: :count',
            'deprecated-keys' => 'Устаревших ключей: :count',
            'keys-expiring-soon' => 'Скоро истекающих ключей (7 дней): :count',
            'unused-keys-summary' => 'Неиспользуемых ключей (90 дней): :count',
            'recently-rotated' => 'Недавно ротированных (30 дней): :count',
            'cleanup-success-message' => '   ✅ Очищено истекших ключей: :count',
            'invalidate-success-message' => '   ✅ Аннулировано устаревших ключей: :count',
            'notify-success-message' => '   ✅ Отправлено уведомлений об истечении срока: :count',
            'notify-failed-message' => '   ⚠️ Не удалось отправить уведомление о :key: :error',
            'publish-assets-warning' => 'Предупреждение: не удалось опубликовать ресурсы пакета. :error',
            'env-config-saved' => '✓ Конфигурация окружения API Platform успешно сохранена',
            'api-endpoints' => 'Конечные точки API:',
            'api-documentation' => 'Документация API: :url',
            'api-landing-page' => 'Стартовая страница API: :url',
            'graphql-playground' => 'GraphQL Playground: :url',
            'rest-api-storefront' => 'Swagger REST API витрины: :url',
            'rest-api-admin' => 'Swagger REST API администратора: :url',
            'key-rotation-invalid-key' => 'Невозможно ротировать недействительный или истекший ключ',
            'env-file-not-found' => 'Файл .env не найден',
            'env-permission-denied' => 'Доступ запрещён: невозможно записать в файл .env. Проверьте права доступа к файлу.',
        ],

        'attribute-translation' => [
            'id-required' => 'Требуется ID перевода атрибута',
            'invalid-id-format' => 'Недопустимый формат ID. Ожидается формат IRI, например "/api/shop/attribute-translations/1", или числовой ID',
            'not-found' => 'Перевод атрибута не найден',
        ],

        'currency' => [
            'id-required' => 'Требуется ID валюты',
            'invalid-id-format' => 'Недопустимый формат ID. Ожидается формат IRI, например "/api/shop/currencies/1", или числовой ID',
            'not-found' => 'Валюта не найдена',
        ],

        'customer-address' => [
            'id-required' => 'Требуется ID адреса клиента',
            'invalid-id-format' => 'Недопустимый формат ID. Ожидается формат IRI, например "/api/shop/customer-addresses/1", или числовой ID',
        ],

        'customer-order-shipment-item' => [
            'id-required' => 'Требуется ID позиции отгрузки',
            'invalid-id-format' => 'Недопустимый формат ID. Ожидается формат IRI, например "/api/shop/customer-order-shipment-items/1", или числовой ID',
            'not-found' => 'Позиция отгрузки не найдена',
        ],

        'page' => [
            'id-required' => 'Требуется ID страницы',
            'invalid-id-format' => 'Недопустимый формат ID. Ожидается формат IRI, например "/api/shop/pages/1", или числовой ID',
            'not-found' => 'Страница не найдена',
        ],

        'page-translation' => [
            'id-required' => 'Требуется ID перевода страницы',
            'invalid-id-format' => 'Недопустимый формат ID. Ожидается формат IRI, например "/api/shop/page-translations/1", или числовой ID',
            'not-found' => 'Перевод страницы не найден',
        ],

        'product-bundle-option' => [
            'id-required' => 'Требуется ID опции комплекта',
            'invalid-id-format' => 'Недопустимый формат ID. Ожидается формат IRI, например "/api/shop/product-bundle-options/1", или числовой ID',
            'not-found' => 'Опция комплекта не найдена',
        ],

        'product-bundle-option-product' => [
            'id-required' => 'Требуется ID товара опции комплекта',
            'invalid-id-format' => 'Недопустимый формат ID. Ожидается формат IRI, например "/api/shop/product-bundle-option-products/1", или числовой ID',
            'not-found' => 'Товар опции комплекта не найден',
        ],

        'product-downloadable-link' => [
            'id-required' => 'Требуется ID ссылки для скачивания',
            'invalid-id-format' => 'Недопустимый формат ID. Ожидается формат IRI, например "/api/shop/product-downloadable-links/1", или числовой ID',
            'not-found' => 'Ссылка для скачивания не найдена',
        ],

        'product-downloadable-sample' => [
            'id-required' => 'Требуется ID образца для скачивания',
            'invalid-id-format' => 'Недопустимый формат ID. Ожидается формат IRI, например "/api/shop/product-downloadable-samples/1", или числовой ID',
            'not-found' => 'Образец для скачивания не найден',
        ],

        'product-grouped-product' => [
            'id-required' => 'Требуется ID сгруппированного товара',
            'invalid-id-format' => 'Недопустимый формат ID. Ожидается формат IRI, например "/api/shop/product-grouped-products/1", или числовой ID',
            'not-found' => 'Сгруппированный товар не найден',
        ],

        'product-images' => [
            'id-required' => 'Требуется ID изображения товара',
            'invalid-id-format' => 'Недопустимый формат ID. Ожидается формат IRI, например "/api/shop/product-images/1", или числовой ID',
            'not-found' => 'Изображение товара не найдено',
        ],

        'product-videos' => [
            'id-required' => 'Требуется ID видео товара',
            'invalid-id-format' => 'Недопустимый формат ID. Ожидается формат IRI, например "/api/shop/product-videos/1", или числовой ID',
            'not-found' => 'Видео товара не найдено',
        ],

        'theme-customization' => [
            'id-required' => 'Требуется ID настройки темы',
            'invalid-id-format' => 'Недопустимый формат ID. Ожидается формат IRI, например "/api/shop/theme-customizations/1", или числовой ID',
            'not-found' => 'Настройка темы не найдена',
        ],

        'unknown-resource' => [
            'id-required' => 'Требуется ID ресурса',
            'not-found' => 'Ресурс не найден',
        ],
    ],

    'admin' => [
        // login / logout / forgot-password / profile-update keys removed in the
        // 2026-05-27 auth refactor — admin clients now authenticate via
        // pre-issued integration tokens (Bearer header, AdminApiGuard).
        'profile' => [
            'unauthenticated' => 'Вы не прошли аутентификацию.',
        ],

        'dashboard' => [
            'invalid-type' => 'Неизвестный тип статистики панели управления. Допустимые типы: over-all, today, stock-threshold-products, total-sales, top-selling-products, top-customers.',
        ],

        'rma' => [
            'not-found' => 'Запрос на возврат не найден.',
            'invalid-input' => 'Сведения о возврате отсутствуют или недействительны.',
            'invalid-order' => 'Выбранный заказ недействителен.',
            'invalid-item' => 'Выбранный товар не подлежит возврату.',
            'invalid-resolution-type' => 'Тип решения должен быть "return" или "cancel_items".',
            'qty-exceeds' => 'Запрошенное количество превышает количество, доступное для возврата.',
            'created' => 'Запрос на возврат успешно создан.',
            'status-updated' => 'Статус возврата успешно обновлен.',
            'invalid-status' => 'Недопустимый статус возврата.',
            'reopened' => 'Запрос на возврат успешно открыт повторно.',
            'reopen-not-allowed' => 'Этот запрос на возврат нельзя открыть повторно.',
            'refund-failed' => 'Не удалось создать возврат средств для этого возврата.',
            'message-sent' => 'Сообщение успешно отправлено.',
            'no-permission' => 'У вас нет прав на выполнение этого действия.',
            'reason-not-found' => 'Причина возврата не найдена.',
            'reason-deleted' => 'Причина возврата успешно удалена.',
            'status-not-found' => 'Статус возврата не найден.',
            'status-deleted' => 'Статус возврата успешно удален.',
            'status-default-undeletable' => 'Статус возврата по умолчанию нельзя удалить.',
            'rule-not-found' => 'Правило возврата не найдено.',
            'rule-deleted' => 'Правило возврата успешно удалено.',
            'custom-field-not-found' => 'Пользовательское поле возврата не найдено.',
            'custom-field-deleted' => 'Пользовательское поле возврата успешно удалено.',
            'custom-field-options-required' => 'Для этого типа поля требуется хотя бы одна опция.',
            'mass-indices-required' => 'Требуется хотя бы один id.',
            'mass-delete-success' => 'Выбранные элементы удалены.',
            'mass-update-success' => 'Выбранные элементы обновлены.',
            'mass-value-invalid' => 'Значение статуса должно быть 0 или 1.',
            'image-not-found' => 'Изображение возврата не найдено.',
            'attachment-not-found' => 'Вложение сообщения не найдено.',
        ],

        'eu-withdrawal' => [
            'not-found' => 'Заявление об отказе не найдено.',
            'no-permission' => 'У вас нет прав на управление отказами по законодательству ЕС.',
            'declined' => 'Отказ отмечен как отклоненный.',
            'refunded' => 'Отказ отмечен как возмещенный.',
            'confirmation-resent' => 'Письмо с подтверждением успешно отправлено повторно.',
            'confirmation-failed' => 'Не удалось отправить письмо с подтверждением.',
        ],

        'reporting' => [
            'invalid-entity' => 'Неизвестная сущность отчета.',
            'invalid-type' => 'Неизвестный тип статистики отчета для этой сущности.',
            'export-format-unsupported' => 'Поддерживаются только форматы экспорта csv, xls и xlsx.',
        ],

        'configuration' => [
            'unauthenticated' => 'Вы не прошли аутентификацию.',
            'no-permission' => 'У вас нет прав на обновление конфигурации.',
            'slug-required' => 'Требуется параметр запроса `slug`.',
            'slug-not-found' => 'Для указанного slug не зарегистрирован раздел конфигурации.',
            'values-required' => 'В `values` требуется хотя бы одно значение.',
            'scope-escape' => 'Ключ `:key` находится за пределами запрошенного поддерева slug.',
            'unknown-field' => 'Ключ `:key` не является зарегистрированным полем конфигурации.',
            'validation-failed' => 'Одно или несколько значений конфигурации не прошли проверку.',
            'file-upload-rest-only' => 'Загрузка двоичных файлов для поля `:field` поддерживается только через multipart-эндпоинт REST, но не через GraphQL.',
            'custom-view-readonly' => 'Поле `:field` является пользовательским полем, отображаемым через blade, и не может быть обновлено через API.',
            'update-success' => 'Конфигурация успешно обновлена.',
        ],

        'customer' => [
            'not-found' => 'Клиент не найден.',
            'no-permission' => 'У вас нет прав на выполнение этого действия.',
            'has-active-orders' => 'У клиента есть ожидающие или обрабатываемые заказы, поэтому его нельзя удалить.',
            'deleted' => 'Клиент успешно удален.',
            'mass-delete-indices-required' => 'Требуется хотя бы один id клиента.',
            'mass-delete-success' => 'Клиенты удалены.',
            'mass-update-indices-required' => 'Требуется хотя бы один id клиента.',
            'mass-update-value-invalid' => 'Значение статуса должно быть 0 или 1.',
            'mass-update-success' => 'Статус клиентов обновлен.',

            'address' => [
                'not-found' => 'Адрес не найден.',
                'not-owned' => 'Этот адрес не принадлежит указанному клиенту.',
                'customer-id-required' => 'Для определения владельца адреса требуется customerId.',
                'deleted' => 'Адрес успешно удален.',
            ],

            'note' => [
                'empty' => 'Требуется текст заметки.',
                'created' => 'Заметка успешно добавлена.',
            ],

            'impersonate' => [
                'success' => 'Токен входа от имени клиента выдан.',
            ],

            'review' => [
                'not-found' => 'Отзыв не найден.',
                'no-permission' => 'У вас нет прав на выполнение этого действия.',
                'invalid-status' => 'Статус должен быть одним из: pending, approved, disapproved.',
                'updated' => 'Отзыв успешно обновлен.',
                'deleted' => 'Отзыв успешно удален.',
                'mass-delete-indices-required' => 'Требуется хотя бы один id отзыва.',
                'mass-delete-success' => 'Отзывы удалены.',
                'mass-update-indices-required' => 'Требуется хотя бы один id отзыва.',
                'mass-update-value-invalid' => 'Значение статуса должно быть одним из: pending, approved, disapproved.',
                'mass-update-success' => 'Статус отзывов обновлен.',
            ],

            'group' => [
                'not-found' => 'Группа клиентов не найдена.',
                'deleted' => 'Группа клиентов успешно удалена.',
                'is-system' => 'Системные группы клиентов нельзя удалить.',
                'has-customers' => 'К группе клиентов привязаны клиенты, поэтому ее нельзя удалить.',
                'system-code-immutable' => 'Код системной группы клиентов нельзя изменить.',
                'system-flag-immutable' => 'Флаг is_user_defined системной группы клиентов нельзя изменить.',
                'mass-delete-indices-required' => 'Требуется хотя бы один id группы клиентов.',
                'mass-delete-success' => 'Группы клиентов удалены.',
            ],

            'gdpr' => [
                'not-found' => 'Запрос GDPR не найден.',
                'no-permission' => 'У вас нет прав на выполнение этого действия.',
                'deleted' => 'Запрос GDPR успешно удален.',
                'invalid-status' => 'Статус должен быть одним из: pending, processing, declined, approved, revoked.',
                'no-changes' => 'Требуется хотя бы одно из полей: статус или сообщение.',
                'already-processed' => 'Этот запрос GDPR уже обработан и не может быть выполнен повторно.',

                'process' => [
                    'approved' => 'Запрос GDPR одобрен.',
                    'deleted' => 'Запрос GDPR одобрен, клиент удален.',
                ],
            ],
        ],

        'order' => [
            'not-found' => 'Заказ не найден.',

            'reorder' => [
                'success' => 'Повторный заказ выполнен. Создана новая черновая корзина.',
                'cannot-reorder' => 'Этот заказ нельзя повторить (гостевой заказ или один или несколько товаров больше недоступны для продажи).',
                'failed' => 'Не удалось выполнить повторный заказ. Пожалуйста, повторите попытку.',
                'guest-not-supported' => 'Повторный заказ не поддерживается для гостевых заказов.',
                'items-not-saleable' => 'Один или несколько товаров в этом заказе больше недоступны для покупки.',
                'no-permission' => 'У вас нет прав на создание заказов.',
                'disabled-in-settings' => 'Повторный заказ администратором отключен в настройках магазина.',
            ],

            'actions' => [
                'cancel' => [
                    'closed' => 'Закрытые заказы нельзя отменить.',
                    'fraud' => 'Мошеннические заказы нельзя отменить.',
                    'nothing-to-cancel' => 'В этом заказе нечего отменять.',
                    'already-processed' => 'По этому заказу уже выставлен счет или выполнена отгрузка, и его больше нельзя отменить. Вместо этого вы можете оформить возврат средств.',
                    'no-permission' => 'У вас нет прав на отмену заказов.',
                    'success' => 'Заказ успешно отменен.',
                    'failed' => 'Не удалось отменить заказ.',
                ],

                'comment' => [
                    'empty' => 'Требуется комментарий.',
                    'success' => 'Комментарий добавлен к заказу.',
                    'failed' => 'Не удалось добавить комментарий.',
                ],

                'invoice' => [
                    'closed' => 'По закрытым заказам нельзя выставить счет.',
                    'fraud' => 'По мошенническим заказам нельзя выставить счет.',
                    'nothing-to-invoice' => 'В этом заказе нет позиций для выставления счета.',
                    'already-invoiced' => 'Счет по этому заказу уже сформирован.',
                    'paypal-standard-blocked' => 'Нельзя создавать счета для заказов, оплаченных через PayPal Standard.',
                    'no-permission' => 'У вас нет прав на создание счетов.',
                    'items-required' => 'Требуется хотя бы один товар с положительным количеством.',
                    'qty-exceeds' => 'Запрошенное количество (:requested) превышает доступное количество (:available) для SKU :sku.',
                    'not-found' => 'Счет не найден.',
                    'success' => 'Счет успешно создан.',
                    'failed' => 'Не удалось создать счет.',
                    'pdf-failed' => 'Не удалось сформировать PDF счета.',
                    'email-invalid' => 'Требуется действительный адрес электронной почты получателя.',
                    'duplicate-sent' => 'Письмо со счетом отправлено на :email.',
                    'mass-status-indices-required' => 'Требуется хотя бы один id счета.',
                    'mass-status-value-invalid' => 'Статус должен быть одним из: pending, paid, overdue.',
                    'mass-status-success' => 'Статус счетов успешно обновлен.',
                ],

                'shipment' => [
                    'closed' => 'Закрытые заказы нельзя отгрузить.',
                    'fraud' => 'Мошеннические заказы нельзя отгрузить.',
                    'nothing-to-ship' => 'В этом заказе нечего отгружать.',
                    'no-permission' => 'У вас нет прав на отгрузку заказов.',
                    'source-required' => 'Требуется источник запасов.',
                    'items-required' => 'Требуется хотя бы один товар с положительным количеством.',
                    'qty-exceeds' => 'Запрошенное количество (:requested) превышает доступное количество (:available) для SKU :sku.',
                    'inventory-insufficient' => 'Запасов в выбранном источнике недостаточно для SKU :sku.',
                    'not-found' => 'Отгрузка не найдена.',
                    'success' => 'Отгрузка успешно создана.',
                    'failed' => 'Не удалось создать отгрузку.',
                ],

                'refund' => [
                    'closed' => 'По закрытым заказам нельзя выполнить возврат средств.',
                    'fraud' => 'По мошенническим заказам нельзя выполнить возврат средств.',
                    'nothing-to-refund' => 'В этом заказе не осталось ничего для возврата средств.',
                    'no-permission' => 'У вас нет прав на создание возвратов средств.',
                    'qty-exceeds' => 'Запрошенное количество (:requested) превышает доступное количество (:available) для SKU :sku.',
                    'amount-zero' => 'Рассчитанная сумма возврата равна нулю. Измените количество, стоимость доставки или значения корректировки.',
                    'amount-exceeds-max' => 'Сумма возврата (:amount) превышает максимальную сумму, доступную для возврата (:max).',
                    'not-found' => 'Возврат средств не найден.',
                    'success' => 'Возврат средств успешно создан.',
                    'failed' => 'Не удалось создать возврат средств.',
                ],
            ],
        ],

        'appearance' => [
            'no-permission' => 'У вас нет прав на управление оформлением.',

            'theme' => [
                'not-found' => 'Тема не найдена.',
                'not-installed' => 'Эта тема не установлена.',
                'channel-ids-required' => 'Требуется хотя бы один канал.',
                'unknown-channel' => 'Один или несколько каналов не существуют.',
                'activated' => 'Тема успешно активирована.',
            ],

            'section' => [
                'not-found' => 'Раздел не найден.',
                'invalid-payload' => 'Неподдерживаемые данные раздела.',
                'footer-links-exists' => 'У этого канала уже есть раздел ссылок в подвале.',
                'options-required' => 'Требуются опции.',
                'status-required' => 'Требуется статус.',
                'section-ids-required' => 'Требуется хотя бы один раздел.',
                'reorder-single-scope' => 'Разделы можно переупорядочивать только в пределах одной темы и одного канала за раз.',
                'reorder-incomplete' => 'Для переупорядочивания нужны все разделы темы и канала в том порядке, в котором они должны отображаться.',
                'unknown-section' => 'Один или несколько разделов не существуют.',
                'file-required' => 'Требуется файл.',
                'created' => 'Раздел успешно создан.',
                'updated' => 'Раздел успешно обновлен.',
                'deleted' => 'Раздел успешно удален.',
                'draft-saved' => 'Изменения раздела успешно подготовлены.',
                'status-staged' => 'Статус раздела успешно подготовлен к публикации.',
                'order-staged' => 'Порядок разделов успешно подготовлен к публикации.',
                'duplicated' => 'Раздел успешно скопирован.',
                'published' => 'Изменения раздела успешно опубликованы.',
                'discarded' => 'Изменения раздела успешно отменены.',
                'media-uploaded' => 'Медиафайл успешно загружен.',
            ],
        ],

        'sales' => [
            'no-permission' => 'У вас нет прав на просмотр этого ресурса продаж.',

            'invoice' => [
                'not-found' => 'Счёт не найден.',
                'no-permission' => 'У вас нет прав на просмотр счетов.',
            ],

            'shipment' => [
                'not-found' => 'Отгрузка не найдена.',
                'no-permission' => 'У вас нет прав на просмотр отгрузок.',
            ],

            'refund' => [
                'not-found' => 'Возврат не найден.',
                'no-permission' => 'У вас нет прав на просмотр возвратов.',
                'export-format-unsupported' => 'Поддерживается только экспорт в csv, xls и xlsx.',
            ],

            'transaction' => [
                'not-found' => 'Транзакция не найдена.',
                'no-permission' => 'У вас нет прав на просмотр транзакций.',

                'create' => [
                    'invoice-required' => 'Поле invoiceId обязательно.',
                    'payment-method-required' => 'Поле paymentMethod обязательно.',
                    'amount-invalid' => 'Поле amount обязательно и должно быть числом.',
                    'invoice-missing' => 'Указанный счёт не найден.',
                    'already-paid' => 'Этот счёт уже полностью оплачен.',
                    'amount-exceeds' => 'Сумма платежа превышает общую сумму счёта.',
                    'amount-zero' => 'Сумма платежа должна быть больше нуля.',
                ],
            ],

            'booking' => [
                'not-found' => 'Бронирование не найдено.',
                'no-permission' => 'У вас нет прав на просмотр бронирований.',
            ],

            'export' => [
                'format-unsupported' => 'Поддерживается только экспорт в csv, xls и xlsx.',
            ],
        ],

        'cart' => [
            'not-found' => 'Корзина не найдена.',
            'not-draft' => 'Эту корзину нельзя изменить через административный API.',
            'product-required' => 'Требуется productId.',
            'product-not-found' => 'Продукт не найден.',
            'booking-unsupported' => 'Продукты бронирования нельзя добавить в черновик заказа администратора. Бронирования должны оформляться через витрину магазина клиентом.',
            'product-not-saleable' => 'Этот продукт недоступен для продажи (нет в наличии или отключён) и не может быть добавлен в заказ.',
            'qty-required' => 'Поле qty обязательно и должно быть непустым объектом вида itemId => количество.',
            'cart-item-required' => 'Требуется cartItemId.',
            'item-added' => 'Товар добавлен в корзину.',
            'item-add-failed' => 'Не удалось добавить товар в корзину.',
            'item-updated' => 'Товары в корзине обновлены.',
            'item-update-failed' => 'Не удалось обновить товары в корзине.',
            'item-removed' => 'Товар удалён из корзины.',
            'item-remove-failed' => 'Не удалось удалить товар из корзины.',
            'address-saved' => 'Адрес сохранён.',
            'address-required' => 'Требуется платёжный адрес.',
            'address-incomplete' => 'Для сохранения адреса требуется :field.',
            'coupon-code-required' => 'Требуется код купона.',
            'coupon-applied' => 'Купон применён.',
            'coupon-already-applied' => 'Купон уже применён к этой корзине.',
            'coupon-not-found' => 'Купон не найден или неактивен.',
            'coupon-removed' => 'Купон удалён.',
            'coupon-error' => 'Что-то пошло не так при применении купона.',
            'unknown-error' => 'Что-то пошло не так.',
            'draft-created' => 'Черновик корзины создан.',
            'draft-failed' => 'Не удалось создать черновик корзины.',
            'addresses-required-for-shipping' => 'Перед выбором способа доставки необходимо сохранить адреса.',
            'shipping-required-for-payment' => 'Способ доставки должен быть выбран до способа оплаты.',
            'shipping-method-required' => 'Требуется способ доставки.',
            'shipping-method-saved' => 'Способ доставки сохранён.',
            'shipping-method-failed' => 'Не удалось сохранить способ доставки.',
            'payment-method-required' => 'Требуется способ оплаты.',
            'payment-method-saved' => 'Способ оплаты сохранён.',
            'payment-method-failed' => 'Не удалось сохранить способ оплаты.',

            'place-order' => [
                'success' => 'Заказ успешно оформлен.',
                'failed' => 'Не удалось оформить заказ.',
                'error' => 'Не удалось оформить заказ: в корзине есть ошибки.',
                'minimum-order-error' => 'Минимальная сумма заказа — :amount. Добавьте больше товаров перед оформлением заказа.',
                'empty-cart' => 'Корзина пуста — добавьте товары перед оформлением заказа.',
                'addresses-required' => 'Перед оформлением заказа необходимо сохранить платёжный адрес и адрес доставки.',
                'shipping-required' => 'Перед оформлением заказа необходимо выбрать способ доставки.',
                'payment-required' => 'Перед оформлением заказа необходимо выбрать способ оплаты.',
                'payment-method-unsupported' => 'Для заказов, оформленных администратором, поддерживаются только cashondelivery и moneytransfer.',
            ],
        ],

        'product' => [
            'not-found' => 'Продукт не найден.',

            'mass-delete-success' => 'Продукты успешно удалены.',
            'mass-update-status-success' => 'Статус продуктов успешно обновлён.',
            'indices-required' => 'Поле indices обязательно и должно быть непустым массивом.',
            'indices-invalid' => 'Поле indices должно быть массивом целочисленных ID продуктов.',
            'value-invalid' => 'Поле value обязательно и должно быть 0 или 1.',
            'mass-delete-failed' => 'Один или несколько продуктов не удалось удалить.',
            'no-permission' => 'У вас нет прав на управление продуктами.',

            'copy-success' => 'Продукт успешно скопирован.',
            'copy-failed' => 'Не удалось скопировать продукт.',
            'copy-variant-not-supported' => 'Варианты configurable-продуктов нельзя копировать. Скопируйте вместо этого родительский configurable-продукт.',

            'create' => [
                'created' => 'Продукт успешно создан.',
                'create-failed' => 'Не удалось создать продукт.',
                'sku-required' => 'Поле sku обязательно.',
                'sku-unique' => 'Такой sku уже занят.',
                'sku-invalid' => 'sku может содержать только буквы, цифры и дефисы (например, "sp-001").',
                'attribute-family-required' => 'Поле attribute_family_id обязательно.',
                'attribute-family-not-found' => 'Выбранное семейство атрибутов не существует.',
                'type-required' => 'Поле type обязательно.',
                'type-not-yet-supported' => 'Тип продукта ":type" не поддерживается этим API. Допустимые типы: simple, virtual, downloadable, grouped, bundle, configurable, booking.',
                'super-attributes-required' => 'Поле super_attributes обязательно при type=configurable. Передайте сопоставление кода (или id) атрибута со списком option_ids, например { "color": [1,2], "size": [4,5] }.',
                'super-attributes-invalid' => 'Поле super_attributes должно быть непустым сопоставлением кода (или id) атрибута с непустым списком option_ids.',
                'no-permission' => 'У вас нет прав на создание продуктов.',
            ],

            'update' => [
                'updated' => 'Продукт успешно обновлён.',
                'update-failed' => 'Не удалось обновить продукт.',
                'id-required' => 'Требуется id продукта.',
                'url-key-required' => 'Поле url_key не может быть пустым.',
                'url-key-unique' => 'Такой url_key уже используется другим продуктом.',
                'special-price-invalid' => 'special_price должна быть числом меньше price.',
                'special-price-date-range-invalid' => 'special_price_to должна быть не раньше special_price_from.',
                'boolean-field-invalid' => 'Поле :field должно быть 0 или 1.',
                'categories-invalid' => 'Поле categories должно быть массивом id категорий.',
                'channels-invalid' => 'Поле channels должно быть массивом id каналов.',
                'super-attributes-cannot-change' => 'super_attributes нельзя изменить после создания продукта.',
                'sub-resource-stripped-images' => 'Изображениями нужно управлять через POST /api/admin/catalog/products/{id}/images. Поле images было проигнорировано.',
                'sub-resource-stripped-inventories' => 'Запасами нужно управлять через PUT /api/admin/catalog/products/{id}/inventories. Поле inventories было проигнорировано.',
                'sub-resource-stripped-customer-group-prices' => 'Цены для групп клиентов нужно управлять через /api/admin/catalog/products/{id}/customer-group-prices. Поле customer_group_prices было проигнорировано.',
                'translations-single-locale' => 'Обновлена только запрошенная локаль. Чтобы обновить локаль(и) :locales, отправьте отдельный запрос с ?locale=<code> для каждой.',
            ],

            'delete' => [
                'deleted' => 'Продукт успешно удалён.',
                'delete-failed' => 'Не удалось удалить продукт.',
            ],

            'image' => [
                'uploaded' => 'Изображение продукта успешно загружено.',
                'updated' => 'Изображение продукта успешно обновлено.',
                'update-empty' => 'Для обновления передайте альтернативный текст или позицию.',
                'reordered' => 'Порядок изображений продукта успешно изменён.',
                'deleted' => 'Изображение продукта успешно удалено.',
                'image-required' => 'Требуется файл изображения (поле multipart "image").',
                'image-invalid-type' => 'Тип изображения должен быть одним из: bmp, jpeg, jpg, png, webp.',
                'image-too-large' => 'Изображение слишком большое. Максимально допустимый размер — 4 МБ.',
                'not-found' => 'Изображение продукта не найдено.',
                'no-permission' => 'У вас нет прав на управление изображениями продуктов.',
                'upload-failed' => 'Не удалось загрузить изображение продукта.',
                'reorder-failed' => 'Не удалось изменить порядок изображений продукта.',
                'delete-failed' => 'Не удалось удалить изображение продукта.',
                'order-required' => 'Массив "order" обязателен и не должен быть пустым.',
                'order-invalid' => 'Каждый элемент в "order" должен быть объектом с id и position.',
                'id-not-on-product' => 'Изображение :id не принадлежит этому продукту.',
                'graphql-upload-unsupported' => 'Загрузка изображений через GraphQL не поддерживается. Используйте POST /api/admin/catalog/products/{productId}/images с multipart/form-data.',
            ],

            'downloadable' => [
                'file-required' => 'Требуется файл (поле multipart "file").',
                'file-too-large' => 'Файл слишком большой. Максимально допустимый размер — 128 МБ.',
                'upload-failed' => 'Не удалось загрузить скачиваемый файл.',
                'file-not-found' => 'Для этого продукта и атрибута не найден сохранённый файл.',
                'no-permission' => 'У вас нет прав на управление файлами скачиваемых продуктов.',
            ],

            'video' => [
                'uploaded' => 'Видео продукта успешно загружено.',
                'deleted' => 'Видео продукта успешно удалено.',
                'video-required' => 'Требуется видеофайл (поле multipart "video").',
                'video-invalid-type' => 'Тип видео должен быть одним из: mp4, webm, ogg, mov.',
                'video-too-large' => 'Видео слишком большое. Максимально допустимый размер — 100 МБ.',
                'not-found' => 'Видео продукта не найдено.',
                'no-permission' => 'У вас нет прав на управление видео продуктов.',
                'upload-failed' => 'Не удалось загрузить видео продукта.',
                'delete-failed' => 'Не удалось удалить видео продукта.',
                'graphql-upload-unsupported' => 'Загрузка видео через GraphQL не поддерживается. Используйте POST /api/admin/catalog/products/{productId}/videos с multipart/form-data.',
            ],

            'inventory' => [
                'updated' => 'Запасы продукта успешно сохранены.',
                'inventories-required' => 'Поле inventories обязательно и должно быть непустым сопоставлением inventory_source_id с количеством.',
                'inventories-invalid' => 'Поле inventories должно быть сопоставлением inventory_source_id (положительное целое число) с количеством (неотрицательное целое число).',
                'source-not-found' => 'Источник запасов :id не существует.',
                'qty-invalid' => 'Каждое количество должно быть неотрицательным целым числом.',
                'no-permission' => 'У вас нет прав на управление запасами продуктов.',
                'not-found' => 'Продукт не найден.',
            ],

            'customer-group-price' => [
                'created' => 'Цена для группы клиентов успешно добавлена.',
                'updated' => 'Цена для группы клиентов успешно обновлена.',
                'deleted' => 'Цена для группы клиентов успешно удалена.',
                'qty-required' => 'Поле qty обязательно.',
                'qty-invalid' => 'Поле qty должно быть целым числом, большим или равным 1.',
                'value-type-required' => 'Поле value_type обязательно.',
                'value-type-invalid' => 'Поле value_type должно быть либо "fixed", либо "discount".',
                'value-required' => 'Поле value обязательно.',
                'value-invalid' => 'Поле value должно быть неотрицательным числом.',
                'customer-group-not-found' => 'Выбранная группа клиентов не существует.',
                'duplicate-qty-group' => 'Цена для группы клиентов с такой комбинацией (qty, customer_group_id) уже существует для этого продукта.',
                'not-found' => 'Цена для группы клиентов не найдена.',
                'no-permission' => 'У вас нет прав на управление ценами продуктов для групп клиентов.',
            ],
        ],

        'category' => [
            'not-found' => 'Категория не найдена.',

            'created' => 'Категория успешно создана.',
            'updated' => 'Категория успешно обновлена.',
            'deleted' => 'Категория успешно удалена.',
            'slug-required' => 'Поле slug обязательно.',
            'slug-unique' => 'Такой slug уже занят.',
            'name-required' => 'Поле name обязательно.',
            'position-required' => 'Поле position обязательно.',
            'attributes-required' => 'Требуется хотя бы один фильтруемый атрибут.',
            'description-required-for-mode' => 'Для выбранного режима отображения требуется описание.',
            'cannot-delete-root' => 'Корневые категории и корневые категории каналов нельзя удалить.',
            'cannot-delete-channel-root' => 'Эта категория является корневой для канала и не может быть удалена.',
            'delete-failed' => 'Не удалось удалить категорию.',
            'no-permission' => 'У вас нет прав на управление категориями.',
            'mass-delete-success' => 'Категории успешно удалены.',
            'mass-update-status-success' => 'Статус категорий успешно обновлён.',
            'mass-delete-indices-required' => 'Поле indices обязательно и должно быть непустым массивом.',
            'mass-update-status-value-required' => 'Поле value обязательно и должно быть 0 или 1.',
        ],

        'attribute' => [
            'not-found' => 'Атрибут не найден.',

            'create-success' => 'Атрибут успешно создан.',
            'update-success' => 'Атрибут успешно обновлён.',
            'delete-success' => 'Атрибут успешно удалён.',
            'delete-failed' => 'Не удалось удалить атрибут.',
            'mass-delete-success' => 'Атрибуты успешно удалены.',
            'mass-delete-indices-required' => 'Поле indices обязательно и должно быть непустым массивом.',

            'code-immutable' => 'Код атрибута нельзя изменить после создания атрибута.',
            'type-immutable' => 'Тип атрибута нельзя изменить, пока у него есть значения в продуктах.',
            'locale-scope-immutable' => 'Нельзя изменить область локали, пока у атрибута есть значения в продуктах.',
            'system-attribute' => 'Системные атрибуты нельзя удалить.',
            'in-use-family' => 'Атрибут входит в одно или несколько семейств атрибутов (ID групп: :ids). Сначала удалите его из этих семейств.',

            'option-not-found' => 'Опция атрибута не найдена.',
            'option-not-supported' => 'Тип атрибута ":type" не поддерживает опции. Опции могут быть только у атрибутов select, multiselect и checkbox.',
            'option-in-use' => 'Эта опция используется в :count продукт(ах) и не может быть удалена.',
            'option-delete-success' => 'Опция атрибута успешно удалена.',
        ],

        'family' => [
            'not-found' => 'Семейство атрибутов не найдено.',

            'created' => 'Семейство атрибутов успешно создано.',
            'updated' => 'Семейство атрибутов успешно обновлено.',
            'deleted' => 'Семейство атрибутов успешно удалено.',
            'code-required' => 'Требуется код семейства.',
            'code-unique' => 'Код семейства должен быть уникальным.',
            'name-required' => 'Требуется название семейства.',
            'group-code-required' => 'У каждой группы атрибутов должен быть код.',
            'group-name-required' => 'У каждой группы атрибутов должно быть название.',
            'group-column-invalid' => 'Колонка каждой группы атрибутов должна быть 1 или 2.',
            'group-name-duplicate' => 'Название группы атрибутов должно быть уникальным в пределах семейства атрибутов.',
            'update-failed' => 'Не удалось обновить семейство атрибутов.',
            'create-failed' => 'Не удалось создать семейство атрибутов.',
            'default-delete-error' => 'Семейство атрибутов по умолчанию нельзя удалить.',
            'last-delete-error' => 'Требуется хотя бы одно семейство атрибутов.',
            'attribute-product-error' => 'Это семейство атрибутов используется одним или несколькими продуктами. Переназначьте эти продукты перед его удалением.',
            'delete-failed' => 'Не удалось удалить семейство атрибутов.',
            'no-permission' => 'У вас нет прав на управление семействами атрибутов.',
        ],

        'settings' => [
            'locale' => [
                'not-found' => 'Локаль не найдена.',
                'created' => 'Локаль успешно создана.',
                'updated' => 'Локаль успешно обновлена.',
                'deleted' => 'Локаль успешно удалена.',
                'cannot-delete-last' => 'Требуется хотя бы одна локаль — нельзя удалить последнюю оставшуюся локаль.',
                'cannot-delete-channel-default' => 'Эта локаль является локалью по умолчанию для одного или нескольких каналов и не может быть удалена.',
                'delete-failed' => 'Не удалось удалить локаль.',
                'no-permission' => 'У вас нет прав на управление локалями.',
                'mass-delete-success' => 'Локали успешно удалены.',
                'mass-delete-indices-required' => 'Поле indices обязательно и должно быть непустым массивом.',
            ],

            'theme' => [
                'not-found' => 'Настройка темы не найдена.',
                'created' => 'Настройка темы успешно создана.',
                'updated' => 'Настройка темы успешно обновлена.',
                'deleted' => 'Настройка темы успешно удалена.',
                'delete-failed' => 'Не удалось удалить настройку темы.',
                'no-permission' => 'У вас нет прав на управление настройками темы.',
                'mass-delete-success' => 'Настройки темы успешно удалены.',
                'mass-delete-indices-required' => 'Поле indices обязательно и должно быть непустым массивом.',
                'mass-update-success' => 'Настройки темы успешно обновлены.',
                'mass-update-indices-required' => 'Поле indices обязательно и должно быть непустым массивом.',
                'mass-update-value-invalid' => 'Поле value должно быть 0 или 1.',
            ],

            'inventory-source' => [
                'not-found' => 'Источник запасов не найден.',
                'created' => 'Источник запасов успешно создан.',
                'updated' => 'Источник запасов успешно обновлён.',
                'deleted' => 'Источник запасов успешно удалён.',
                'code-unique' => 'Источник запасов с таким кодом уже существует.',
                'last-delete-error' => 'Требуется хотя бы один источник запасов — нельзя удалить последний оставшийся источник.',
                'in-use' => 'На этот источник запасов ссылаются запасы продуктов, поэтому его нельзя удалить.',
                'delete-failed' => 'Не удалось удалить источник запасов.',
                'no-permission' => 'У вас нет прав на управление источниками запасов.',
                'mass-delete-success' => 'Источники запасов успешно удалены.',
                'mass-delete-indices-required' => 'Поле indices обязательно и должно быть непустым массивом.',
            ],

            'exchange-rate' => [
                'not-found' => 'Курс обмена не найден.',
                'created' => 'Курс обмена успешно создан.',
                'updated' => 'Курс обмена успешно обновлён.',
                'deleted' => 'Курс обмена успешно удалён.',
                'duplicate-pair' => 'Курс обмена для этой целевой валюты уже существует. Обновите существующую запись или сначала удалите её.',
                'delete-failed' => 'Не удалось удалить курс обмена.',
                'no-permission' => 'У вас нет прав на управление курсами обмена.',
                'mass-delete-success' => 'Курсы обмена успешно удалены.',
                'mass-delete-indices-required' => 'Поле indices обязательно и должно быть непустым массивом.',
                'update-rates-success' => 'Курсы обмена успешно обновлены.',
                'update-rates-failed' => 'Не удалось обновить курсы обмена от внешнего поставщика.',
                'provider-not-configured' => 'Внешний поставщик курсов обмена не настроен.',
            ],

            'tax-rate' => [
                'not-found' => 'Налоговая ставка не найдена.',
                'created' => 'Налоговая ставка успешно создана.',
                'updated' => 'Налоговая ставка успешно обновлена.',
                'deleted' => 'Налоговая ставка успешно удалена.',
                'delete-failed' => 'Не удалось удалить налоговую ставку.',
                'no-permission' => 'У вас нет прав на управление налоговыми ставками.',
                'zip-code-required' => 'Поле zip_code обязательно, если is_zip имеет значение false.',
                'zip-range-required' => 'Поля zip_from и zip_to обязательны, если is_zip имеет значение true.',
            ],

            'tax-category' => [
                'not-found' => 'Налоговая категория не найдена.',
                'created' => 'Налоговая категория успешно создана.',
                'updated' => 'Налоговая категория успешно обновлена.',
                'deleted' => 'Налоговая категория успешно удалена.',
                'code-unique' => 'Такой код уже занят.',
                'cannot-delete-in-use' => 'К этой налоговой категории всё ещё привязаны налоговые ставки, поэтому её нельзя удалить.',
                'delete-failed' => 'Не удалось удалить налоговую категорию.',
                'no-permission' => 'У вас нет прав на управление налоговыми категориями.',
            ],

            'role' => [
                'not-found' => 'Роль не найдена.',
                'created' => 'Роль успешно создана.',
                'updated' => 'Роль успешно обновлена.',
                'deleted' => 'Роль успешно удалена.',
                'permissions-required' => 'Поле permissions обязательно, если permission_type имеет значение custom.',
                'cannot-delete-in-use' => 'Эта роль назначена одному или нескольким администраторам и не может быть удалена.',
                'cannot-delete-last-role' => 'Требуется хотя бы одна роль — нельзя удалить последнюю оставшуюся роль.',
                'cannot-change-last-all-access' => 'Единственную роль с полным доступом нельзя изменить на пользовательскую роль.',
                'delete-failed' => 'Не удалось удалить роль.',
                'no-permission' => 'У вас нет прав на управление ролями.',
            ],

            'currency' => [
                // Read
                'not-found' => 'Валюта не найдена.',

                // Create / Update / Delete
                'created' => 'Валюта успешно создана.',
                'updated' => 'Валюта успешно обновлена.',
                'deleted' => 'Валюта успешно удалена.',
                'code-required' => 'Поле code обязательно.',
                'code-size' => 'Код должен состоять ровно из 3 буквенных символов.',
                'code-unique' => 'Такой код уже занят.',
                'name-required' => 'Поле name обязательно.',
                'cannot-delete-last' => 'Требуется хотя бы одна валюта — нельзя удалить последнюю оставшуюся валюту.',
                'cannot-delete-channel-base' => 'Эта валюта является базовой валютой одного или нескольких каналов и не может быть удалена.',
                'delete-failed' => 'Не удалось удалить валюту.',
                'no-permission' => 'У вас нет прав на управление валютами.',

                'mass-delete-success' => 'Валюты успешно удалены.',
                'mass-delete-indices-required' => 'Поле indices обязательно и должно быть непустым массивом.',
            ],

            'channel' => [
                'not-found' => 'Канал не найден.',

                'created' => 'Канал успешно создан.',
                'updated' => 'Канал успешно обновлён.',
                'deleted' => 'Канал успешно удалён.',
                'code-unique' => 'Такой код уже занят.',
                'hostname-unique' => 'Такое имя хоста уже занято.',
                'default-locale-mismatch' => 'default_locale_id должен присутствовать в списке локалей.',
                'base-currency-mismatch' => 'base_currency_id должен присутствовать в списке валют.',
                'unknown-locale' => 'Один или несколько id локалей не существуют.',
                'unknown-currency' => 'Один или несколько id валют не существуют.',
                'unknown-inventory-source' => 'Один или несколько id inventory_source не существуют.',
                'unknown-root-category' => 'root_category_id не существует.',
                'cannot-delete-last' => 'Требуется хотя бы один канал — нельзя удалить последний оставшийся канал.',
                'cannot-delete-default' => 'Нельзя удалить канал приложения по умолчанию.',
                'delete-failed' => 'Не удалось удалить канал.',
                'no-permission' => 'У вас нет прав на управление каналами.',
            ],

            'user' => [
                'not-found' => 'Администратор не найден.',
                'created' => 'Администратор успешно создан.',
                'updated' => 'Администратор успешно обновлён.',
                'deleted' => 'Администратор успешно удалён.',
                'name-required' => 'Поле name обязательно.',
                'email-required' => 'Поле email обязательно.',
                'email-invalid' => 'email должен быть действительным адресом электронной почты.',
                'email-unique' => 'Такой email уже занят.',
                'password-required' => 'Поле password обязательно.',
                'password-min' => 'Пароль должен содержать не менее 6 символов.',
                'role-required' => 'Поле role_id обязательно.',
                'role-not-found' => 'Выбранная роль не существует.',
                'status-invalid' => 'Статус должен быть 0 или 1.',
                'cannot-delete-self' => 'Вы не можете удалить собственную учётную запись администратора.',
                'cannot-delete-last-admin' => 'Требуется хотя бы один администратор — нельзя удалить последнего оставшегося администратора.',
                'cannot-change' => ':name последнего администратора с полным доступом нельзя изменить.',
                'delete-failed' => 'Не удалось удалить администратора.',
                'no-permission' => 'У вас нет прав на управление администраторами.',
                'self-delete-password-required' => 'Для удаления собственной учётной записи требуется поле password.',
                'self-delete-incorrect-password' => 'Неверный пароль.',
                'self-deleted' => 'Ваша учётная запись администратора удалена.',
            ],

            'data-transfer' => [
                'import' => [
                    'not-found' => 'Импорт не найден.',
                    'deleted' => 'Импорт успешно удалён.',
                    'cancelled' => 'Импорт успешно отменён.',
                    'cannot-cancel' => 'Отменить можно только импорт в состоянии ожидания или обработки. Текущее состояние: :state.',
                    'no-permission' => 'У вас нет прав на управление импортом данных.',
                    'delete-failed' => 'Не удалось удалить импорт.',
                    'created' => 'Импорт успешно создан.',
                    'updated' => 'Импорт успешно обновлён.',
                    'type-invalid' => 'Выбранный тип недопустим.',
                    'action-invalid' => 'Действие должно быть одним из: append, delete.',
                    'validation-strategy-invalid' => 'Стратегия проверки должна быть одной из: stop-on-errors, skip-errors.',
                    'allowed-errors-invalid' => 'Допустимое количество ошибок должно быть целым числом, равным 0 или больше.',
                    'field-separator-required' => 'Требуется разделитель полей.',
                    'file-required' => 'Требуется файл.',
                    'file-invalid-type' => 'Файл должен быть одного из форматов: csv, xls, xlsx, xml.',
                    'nothing-to-import' => 'Нечего импортировать.',
                    'not-valid' => 'Импорт недействителен.',
                    'setup-queue-error' => 'Для подключения очереди задано значение sync; настройте настоящую очередь для фоновой обработки импорта.',
                    'file-not-found' => 'Нет файла, доступного для скачивания.',
                    'error-report-not-found' => 'Нет отчёта об ошибках, доступного для скачивания.',
                    'sample-not-found' => 'Для запрошенного типа и формата нет файла-образца.',
                    'sample-format-invalid' => 'Формат образца должен быть одним из: csv, xls, xlsx, xml.',
                    'images-archive-too-large' => 'Архив изображений может содержать не более :entries файлов и распаковываться не более чем в :size МБ.',
                ],
            ],
        ],

        'cms' => [
            'page' => [
                'not-found' => 'CMS-страница не найдена.',

                'created' => 'CMS-страница успешно создана.',
                'url-key-required' => 'Поле url_key обязательно.',
                'url-key-unique' => 'Такой url_key уже занят.',
                'url-key-invalid' => 'Неверный формат url_key. Используйте только строчные буквы, цифры и дефисы.',
                'page-title-required' => 'Поле page_title обязательно.',
                'html-content-required' => 'Поле html_content обязательно.',
                'channels-required' => 'Необходимо выбрать хотя бы один канал.',
                'channels-invalid' => 'Один или несколько каналов недопустимы.',

                'updated' => 'CMS-страница успешно обновлена.',

                'deleted' => 'CMS-страница успешно удалена.',
                'delete-failed' => 'Не удалось удалить CMS-страницу.',

                'mass-deleted' => 'CMS-страницы успешно удалены.',
                'indices-required' => 'Поле indices обязательно и должно быть непустым массивом.',
                'indices-invalid' => 'Поле indices должно быть массивом ID CMS-страниц.',

                'no-permission' => 'У вас нет прав на управление CMS-страницами.',
            ],
        ],

        'marketing' => [
            'catalog-rule' => [
                'not-found' => 'Правило каталога не найдено.',
                'created' => 'Правило каталога успешно создано.',
                'updated' => 'Правило каталога успешно обновлено.',
                'deleted' => 'Правило каталога успешно удалено.',
                'delete-failed' => 'Не удалось удалить правило каталога.',
                'mass-delete-indices-required' => 'Не переданы ID правил каталога для массового удаления.',
                'mass-delete-success' => 'Выбранные правила каталога успешно удалены.',
                'no-permission' => 'У вас нет прав на управление правилами каталога.',
            ],

            'campaign' => [
                'not-found' => 'Кампания не найдена.',
                'created' => 'Кампания успешно создана.',
                'updated' => 'Кампания успешно обновлена.',
                'deleted' => 'Кампания успешно удалена.',
                'delete-failed' => 'Не удалось удалить кампанию.',
                'no-permission' => 'У вас нет прав на управление кампаниями.',

                'send' => [
                    'id-required' => 'Требуется id кампании.',
                    'inactive' => 'Нельзя отправить неактивную кампанию. Сначала включите её.',
                    'queued' => 'Кампания поставлена в очередь для :count получател(ей).',
                ],
            ],

            'sitemap' => [
                'not-found' => 'Карта сайта не найдена.',
                'created' => 'Карта сайта успешно создана.',
                'updated' => 'Карта сайта успешно обновлена.',
                'deleted' => 'Карта сайта успешно удалена.',
                'delete-failed' => 'Не удалось удалить карту сайта.',
                'no-permission' => 'У вас нет прав на управление картами сайта.',

                'generate' => [
                    'id-required' => 'Требуется id карты сайта.',
                    'no-channels' => 'Эта карта сайта не охватывает ни одного канала, поэтому генерировать нечего. Сначала назначьте ей хотя бы один канал.',
                    'success' => 'Карта сайта успешно сгенерирована заново.',
                    'failed' => 'Не удалось сгенерировать карту сайта: :message',
                ],
            ],

            'cart-rule' => [
                'not-found' => 'Правило корзины не найдено.',
                'id-required' => 'Требуется id правила корзины.',
                'copy-of' => 'Копия :value',
                'copied' => 'Правило корзины успешно скопировано.',
                'created' => 'Правило корзины успешно создано.',
                'updated' => 'Правило корзины успешно обновлено.',
                'deleted' => 'Правило корзины успешно удалено.',
                'delete-failed' => 'Не удалось удалить правило корзины.',
                'no-permission' => 'У вас нет прав на управление правилами корзины.',
                'mass-delete-success' => 'Правила корзины успешно удалены.',
                'mass-delete-indices-required' => 'Поле indices обязательно и должно быть непустым массивом.',
                'action-type-invalid' => 'action_type должен быть одним из: by_percent, by_fixed, cart_fixed, buy_x_get_y.',
                'coupon-code-required' => 'coupon_code обязателен, если coupon_type=1 и use_auto_generation=0.',
                'coupon-code-unique' => 'Этот код купона уже используется.',
            ],

            'template' => [
                'campaign-associated' => 'Этот шаблон письма используется кампанией, поэтому его нельзя удалить. Сначала удалите его из кампании.',
                'not-found' => 'Шаблон письма не найден.',
                'created' => 'Шаблон письма успешно создан.',
                'updated' => 'Шаблон письма успешно обновлён.',
                'deleted' => 'Шаблон письма успешно удалён.',
                'delete-failed' => 'Не удалось удалить шаблон письма.',
                'status-invalid' => 'Статус должен быть одним из: active, inactive, draft.',
                'no-permission' => 'У вас нет прав на управление шаблонами писем.',
            ],

            'event' => [
                'campaign-associated' => 'Это событие используется кампанией, поэтому его нельзя удалить. Сначала удалите его из кампании.',
                'not-found' => 'Маркетинговое событие не найдено.',
                'created' => 'Маркетинговое событие успешно создано.',
                'updated' => 'Маркетинговое событие успешно обновлено.',
                'deleted' => 'Маркетинговое событие успешно удалено.',
                'delete-failed' => 'Не удалось удалить маркетинговое событие.',
                'no-permission' => 'У вас нет прав на управление маркетинговыми событиями.',
            ],

            'search-synonym' => [
                'not-found' => 'Синоним поиска не найден.',
                'created' => 'Синоним поиска успешно создан.',
                'updated' => 'Синоним поиска успешно обновлён.',
                'deleted' => 'Синоним поиска успешно удалён.',
                'mass-deleted' => 'Синонимы поиска успешно удалены.',
                'delete-failed' => 'Не удалось удалить синоним поиска.',
                'indices-required' => 'Поле indices обязательно и должно быть непустым массивом ID синонимов поиска.',
                'no-permission' => 'У вас нет прав на управление синонимами поиска.',
            ],

            'url-rewrite' => [
                'not-found' => 'Перезапись URL не найдена.',
                'created' => 'Перезапись URL успешно создана.',
                'updated' => 'Перезапись URL успешно обновлена.',
                'deleted' => 'Перезапись URL успешно удалена.',
                'delete-failed' => 'Не удалось удалить перезапись URL.',
                'mass-deleted' => 'Перезаписи URL успешно удалены.',
                'indices-required' => 'Требуется хотя бы один id перезаписи URL.',
                'no-permission' => 'У вас нет прав на управление перезаписями URL.',
            ],

            'cart-rule-coupon' => [
                'cart-rule-not-found' => 'Правило корзины не найдено.',
                'not-found' => 'Купон не найден.',
                'code-taken' => 'Купон с таким кодом уже существует.',
                'format-invalid' => 'Формат должен быть одним из: alphabetic, alphanumeric, numeric.',
                'indices-required' => 'Поле indices обязательно и должно быть непустым массивом ID купонов.',
                'created' => 'Купон успешно создан.',
                'generated' => 'Успешно сгенерировано купонов: :count.',
                'deleted' => 'Купон успешно удалён.',
                'mass-deleted' => 'Успешно удалено купонов: :count.',
                'no-permission' => 'У вас нет прав на управление купонами правил корзины.',
            ],

            'subscriber' => [
                'not-found' => 'Подписчик рассылки не найден.',
                'updated' => 'Подписка успешно обновлена.',
                'deleted' => 'Подписка успешно удалена.',
                'is-subscribed-required' => 'Поле is_subscribed обязательно.',
                'no-permission' => 'У вас нет прав на управление подписчиками рассылки.',
            ],

            'search-term' => [
                'not-found' => 'Поисковый запрос не найден.',
                'updated' => 'Поисковый запрос успешно обновлён.',
                'deleted' => 'Поисковый запрос успешно удалён.',
                'mass-deleted' => 'Поисковые запросы успешно удалены.',
                'term-required' => 'Поле term обязательно.',
                'redirect-url-invalid' => 'redirect_url должен быть действительным URL.',
                'indices-required' => 'В indices необходимо указать хотя бы один ID.',
                'no-permission' => 'У вас нет прав на управление поисковыми запросами.',
            ],
        ],
        'auth' => [
            'configuration' => [
                'title' => 'Аутентификация администратора',
                'info' => 'Сроки действия токенов, ограничения частоты запросов и правила безопасности для входа в API администратора и API-токенов.',

                'tokens' => [
                    'title' => 'Сроки действия токенов',
                    'info' => 'Как долго действуют токены доступа, токены обновления и сессии. Изменения применяются к токенам, выданным после них.',
                    'access-token-ttl' => 'Срок действия токена доступа (минуты)',
                    'access-token-ttl-info' => 'От 5 до 60 минут. По умолчанию 15.',
                    'refresh-token-ttl' => 'Срок действия токена обновления (дни)',
                    'refresh-token-ttl-info' => 'От 1 до 90 дней. По умолчанию 30.',
                    'session-lifetime' => 'Максимальная длительность сессии (дни)',
                    'session-lifetime-info' => 'От 1 до 365 дней. По умолчанию 90. После этого администратор должен войти снова, сколько бы раз ни обновлялась сессия.',
                ],

                'rate-limits' => [
                    'title' => 'Ограничения частоты',
                    'info' => 'Максимум запросов к API администратора в минуту.',
                    'session-requests-per-minute' => 'Запросов в минуту на вошедшего администратора',
                    'api-token-requests-per-minute' => 'Запросов в минуту на API-токен',
                ],

                'security' => [
                    'title' => 'Безопасность',
                    'info' => 'Правила отзыва, срок действия API-токенов и привязка к домену.',
                    'revoke-sessions-on-password-change' => 'Отзывать сессии при смене пароля',
                    'revoke-api-tokens-on-password-change' => 'Отзывать API-токены при смене пароля',
                    'allow-non-expiring-api-tokens' => 'Разрешить API-токены без срока действия',
                    'domain-binding-mode' => 'Режим привязки к домену',
                    'domain-binding-mode-info' => 'DNS проверяет, что запрос приходит с IP-адреса, на который указывает домен токена. Origin проверяет заголовок Origin или Referer и подходит только для браузеров.',
                    'domain-binding-dns' => 'DNS (IP-адрес клиента)',
                    'domain-binding-origin' => 'Заголовок Origin',
                ],
            ],

            'errors' => [
                'unauthenticated' => 'Вы не прошли аутентификацию.',
                'access-token-expired' => 'Срок действия токена доступа истёк. Обновите его, чтобы продолжить.',
                'api-token-expired' => 'Срок действия API-токена истёк.',
                'refresh-token-invalid' => 'Токен обновления недействителен или истёк. Войдите снова.',
                'api-token-revoked' => 'API-токен отозван.',
                'api-token-domain-mismatch' => 'API-токен нельзя использовать с этого домена.',
                'access-token-required' => 'Для этого действия нужен токен доступа сеанса входа; API-токены не могут его использовать.',
                'two-factor-required' => 'Введите 6-значный код из приложения-аутентификатора или один из резервных кодов, чтобы завершить вход.',
                'too-many-login-attempts' => 'Слишком много попыток входа. Повторите попытку через :seconds сек.',
                'two-factor-challenge-invalid' => 'Запрос двухфакторной проверки недействителен или истёк. Войдите снова.',
                'two-factor-attempts-exceeded' => 'Слишком много неверных кодов. Войдите снова.',
                'password-reset-token-invalid' => 'Этот токен сброса пароля недействителен или истёк.',
                'too-many-password-requests' => 'Слишком много запросов пароля. Повторите попытку через :seconds с.',
                'rate-limit-exceeded' => 'Слишком много попыток.',
                'no-permission' => 'У вас нет прав на управление сеансами API администраторов.',
            ],

            'sessions' => [
                'logged-out' => 'Вы вышли из системы.',
                'logged-out-all' => 'Вы вышли из всех сеансов.',
                'not-found' => 'Сеанс не найден.',
                'revoked' => 'Сеанс отозван.',
                'revoked-for-admin' => 'Отозвано сеансов: :count.',
            ],

            'password' => [
                'reset' => 'Ваш пароль сброшен. Войдите снова.',
            ],
        ],

        'api-tokens' => [
            'menu' => [
                'title' => 'API-токены',
            ],

            'acl' => [
                'title' => 'API-токены',
                'create' => 'Создание',
                'delete' => 'Отзыв',
                'manage-others' => 'Управление токенами других администраторов',
            ],

            'index' => [
                'title' => 'API-токены',
                'info' => 'Серверные интеграции, такие как ERP, PIM, складская или бухгалтерская система, проходят аутентификацию с помощью API-токенов. Каждый токен действует от имени одного администратора с правами его роли и работает только с домена, к которому привязан.',
                'create-btn' => 'Создать API-токен',
            ],

            'create' => [
                'title' => 'Создание API-токена',
                'back-btn' => 'Назад',
                'save-btn' => 'Создать токен',
                'general' => 'Общие',
                'expiry' => 'Срок действия',
                'admin-info' => 'Токен действует от имени этого администратора и имеет ровно права его роли.',
                'domain-info' => 'Хост, с которого обращается интеграция, например erp.example.com. URL сокращается до хоста. IP-адреса и подстановочные знаки не принимаются, а домен нельзя изменить позже.',
                'expires-on-date' => 'Истекает в указанную дату',
                'never-expires' => 'Не истекает никогда',
                'no-expiry-warning' => 'Токен без срока действия работает, пока его не отзовут. Храните его в хранилище секретов и отзовите, как только он станет не нужен.',
                'no-expiry-confirm' => 'Я понимаю, что этот токен никогда не истекает.',
                'no-admins' => 'Нет администратора, для которого вы можете создать API-токен.',
            ],

            'view' => [
                'title' => 'API-токен',
                'back-btn' => 'Назад',
                'save-btn' => 'Сохранить',
                'revoke-btn' => 'Отозвать',
                'details' => 'Сведения',
                'usage' => 'Использование',
                'token-once-title' => 'Скопируйте API-токен сейчас',
                'token-once-info' => 'Токен показывается только один раз. Храните его в хранилище секретов; если он потерян, отзовите его и создайте новый.',
                'copy-btn' => 'Копировать',
                'copied' => 'Токен скопирован.',
                'usage-info' => 'Передавайте его как Bearer-токен в заголовке Authorization запросов REST и GraphQL.',
                'not-yet' => 'Пока нет',
                'revoke-confirm' => 'Отозвать этот API-токен? Использующая его интеграция сразу перестанет работать.',
            ],

            'fields' => [
                'id' => 'ID',
                'name' => 'Название',
                'admin' => 'Администратор',
                'token' => 'Токен',
                'domain' => 'Домен',
                'expiry' => 'Срок действия',
                'expires-at' => 'Истекает',
                'no-expiry-confirmation' => 'Подтверждение бессрочности',
                'status' => 'Статус',
                'created-by' => 'Создал',
                'created-at' => 'Создан',
                'last-used-at' => 'Последнее использование',
                'last-ip-address' => 'Последний IP-адрес',
                'last-user-agent' => 'Последний user agent',
                'last-channel' => 'Последний канал',
                'resolved-ips' => 'Разрешённые IP-адреса',
                'resolved-at' => 'Разрешено',
                'domain-mismatches' => 'Запросы с других доменов',
                'last-domain-mismatch-at' => 'Последний запрос с другого домена',
                'revoked-at' => 'Отозван',
                'revoked-by' => 'Отозвал',
            ],

            'datagrid' => [
                'never' => 'Никогда',
                'console' => 'Консольная команда',
                'view' => 'Просмотр',
                'revoke' => 'Отозвать',
            ],

            'status' => [
                'active' => 'Активен',
                'expired' => 'Истёк',
                'revoked' => 'Отозван',
            ],

            'channels' => [
                'rest' => 'REST',
                'graphql' => 'GraphQL',
            ],

            'messages' => [
                'created' => 'API-токен создан.',
                'updated' => 'API-токен обновлён.',
                'revoked' => 'API-токен отозван.',
            ],

            'errors' => [
                'no-permission' => 'У вас нет прав на управление API-токенами.',
                'not-found' => 'API-токен не найден.',
                'cannot-create-for-admin' => 'Вы не можете создать API-токен для этого администратора: у его роли есть права, которых нет у вашей, или вы не можете управлять токенами других администраторов.',
                'cannot-manage' => 'Вы не можете управлять этим API-токеном.',
                'already-revoked' => 'API-токен уже отозван.',
                'invalid-name' => 'Название обязательно и может содержать не более 100 символов.',
                'invalid-domain' => 'Введите доменное имя, например erp.example.com. IP-адреса и подстановочные знаки не принимаются.',
                'expiry-required' => 'У API-токенов должна быть дата истечения.',
                'expiry-in-past' => 'Дата истечения должна быть в будущем.',
            ],
        ],

        'api-sessions' => [
            'menu' => [
                'title' => 'API-сеансы',
            ],

            'acl' => [
                'title' => 'API-сеансы',
                'delete' => 'Отзыв',
            ],

            'index' => [
                'title' => 'API-сеансы',
                'info' => 'Администраторы, вошедшие в административный API из веб-, мобильных, кассовых или настольных приложений. Отзыв сеанса сразу выводит это устройство из системы.',
            ],

            'fields' => [
                'id' => 'ID',
                'admin' => 'Администратор',
                'device' => 'Устройство',
                'client-type' => 'Тип клиента',
                'ip-address' => 'IP-адрес входа',
                'user-agent' => 'User agent при входе',
                'last-ip-address' => 'Последний IP-адрес',
                'last-user-agent' => 'Последний user agent',
                'last-used-at' => 'Последнее использование',
                'last-channel' => 'Последний канал',
                'refresh-expires-at' => 'Обновление истекает',
                'expires-at' => 'Истекает',
                'status' => 'Статус',
                'created-at' => 'Вход выполнен',
            ],

            'datagrid' => [
                'revoke' => 'Отозвать',
                'revoke-all' => 'Отозвать все сеансы этого администратора',
            ],

            'client-types' => [
                'web' => 'Веб',
                'mobile' => 'Мобильный',
                'pos' => 'Касса',
                'desktop' => 'Настольный',
                'other' => 'Другой',
            ],

            'errors' => [
                'not-active' => 'Этот сеанс уже отозван или истёк.',
            ],
        ],
    ],

    'integration' => [
        'menu' => [
            'title' => 'Интеграция',
            'tokens' => 'Токены',
        ],

        'history' => [
            'menu' => [
                'title' => 'История',
            ],

            'acl' => [
                'title' => 'История изменений API',
                'view' => 'Просмотр',
                'delete' => 'Удалить историю',
            ],

            'index' => [
                'title' => 'История изменений API',
                'info' => 'Каждое создание, обновление и удаление, выполненное через API администратора, с указанием того, кто это сделал, какой токен и что изменилось.',
                'cleanup-btn' => 'Удалить старые журналы',
                'cleanup-days' => 'Удалить журналы старше этого количества дней.',
                'cleanup-confirm' => 'Удалить всю историю старше указанного количества дней? Это невозможно отменить.',
            ],

            'view' => [
                'title' => 'Изменить',
                'back-btn' => 'Назад',
                'admin' => 'Админ',
                'token' => 'Токен',
                'action' => 'Действие',
                'resource' => 'Ресурс',
                'method' => 'Метод',
                'ip' => 'IP-адрес',
                'date' => 'Дата',
                'version' => 'Версия',
                'url' => 'Конечная точка',
                'request-details' => 'Запросить детали',
                'changes' => 'Изменения',
                'field' => 'Поле',
                'old' => 'Старое значение',
                'new' => 'Новое значение',
                'no-field-changes' => 'Для этой записи не было зафиксировано никаких изменений на уровне поля.',
                'same-request' => 'Другие изменения в том же запросе',
                'version-chain' => 'История версий этой записи',
            ],

            'datagrid' => [
                'id' => 'идентификатор',
                'date' => 'Дата',
                'admin' => 'Админ',
                'token' => 'Токен',
                'action' => 'Действие',
                'operation' => 'Операция',
                'resource' => 'Ресурс',
                'version' => 'Версия',
                'method' => 'Метод',
                'ip' => 'ИП',
                'view' => 'Посмотреть',
                'delete' => 'Удалить',
            ],

            'events' => [
                'created' => 'Создано',
                'updated' => 'Обновлено',
                'deleted' => 'Удалено',
            ],

            'deleted' => ':count записей истории удалено.',
            'cleanup-input-required' => 'Укажите количество дней или дату для уборки.',
        ],

        'acl' => [
            'title' => 'Интеграция',
            'view' => 'Просмотр',
            'create' => 'Создать интеграцию',
            'edit' => 'Редактировать интеграцию',
            'delete' => 'Отозвать токен интеграции',
            'generate' => 'Создать токен интеграции',
            'regenerate' => 'Восстановить токен интеграции',
        ],

        'index' => [
            'title' => 'Интеграции',
            'create-btn' => 'Создать интеграцию',
        ],

        'create' => [
            'title' => 'Создать интеграцию',
            'save-btn' => 'Сохранить',
            'back-btn' => 'Назад',
        ],

        'edit' => [
            'title' => 'Редактировать интеграцию',
            'save-btn' => 'Сохранить',
            'back-btn' => 'Назад',
            'generate-btn' => 'Создать токен',
            'regenerate-btn' => 'Восстановить токен',
            'revoke-btn' => 'Отозвать токен',
            'copy-btn' => 'Копировать',
            'token-warning' => 'Сохраните этот токен сейчас — он больше не будет отображаться.',
            'token-label' => 'Токен',
            'not-generated' => 'Еще не создано',
            'masked' => '(Сохраняется — отображается только один раз при создании)',
            'history-banner' => 'Этот токен больше не активен.',
        ],

        'fields' => [
            'name' => 'Имя',
            'description' => 'Описание',
            'assign-user' => 'Назначить пользователя',
            'permission-type' => 'Тип разрешения',
            'access-control' => 'Контроль доступа',
            'general' => 'Общий',
            'token-settings' => 'Настройки токена',
            'valid-till' => 'Действует до',
            'rate-limit-per-minute' => 'Ограничение скорости (в минуту)',
            'rate-limit-per-day' => 'Лимит ставки (в день)',
            'never-expires' => 'Никогда не истекает',
            'expires-on' => 'Срок действия истекает',
            'unlimited' => 'Безлимитный',
            'limit-to' => 'Ограничить до',
            'requests-per-minute' => 'запросов/минуту',
            'requests-per-day' => 'запросов/день',
            'select-admin' => 'Выберите администратора',
            'no-available-admins' => 'Нет доступных администраторов — у каждого администратора уже есть активный токен.',
            'same-as-web-hint' => 'Токен будет отражать текущие права назначенной роли администратора в реальном времени.',
            'ip-allowlist' => 'Белый список IP-адресов',
            'ip-any' => 'Любой IP (по умолчанию)',
            'ip-restricted' => 'Ограничено определенными IP-адресами',
            'ip-list-hint' => 'Одна запись в строке. Поддерживает IPv4, IPv6 и CIDR (например, 10.0.0.0/24 или 2001:db8::/32). Оставьте пустым, чтобы разрешить все IP-адреса.',
        ],

        'permission-type' => [
            'all' => 'Все',
            'custom' => 'Пользовательский',
            'same-as-web' => 'То же, что и веб-разрешение',
        ],

        'status' => [
            'draft' => 'Черновик',
            'active' => 'Активный',
            'revoked' => 'Отозван',
            'regenerated' => 'регенерированный',
        ],

        'datagrid' => [
            'id' => 'идентификатор',
            'name' => 'Имя',
            'admin' => 'Админ',
            'token' => 'Токен',
            'status' => 'Статус',
            'permission-type' => 'Тип разрешения',
            'expires-at' => 'Действует до',
            'last-used-at' => 'Последнее использование',
            'created-at' => 'Создано в',
            'edit' => 'Редактировать',
            'revoke' => 'Отозвать',
        ],

        'messages' => [
            'draft-created' => 'Интеграция создана. Создайте токен, чтобы начать его использовать.',
            'updated' => 'Интеграция успешно обновлена.',
            'generated' => 'Токен создан. Скопируйте его сейчас — он больше не будет отображаться.',
            'regenerated' => 'Токен восстановлен. Скопируйте новый токен сейчас — он больше не будет отображаться.',
            'revoked' => 'Токен успешно отозван.',
            'generate-only-draft' => 'Только проекты интеграций могут генерировать свои токены.',
            'regenerate-only-active' => 'Восстановлению подлежат только активные токены.',
            'cannot-edit-historic' => 'Отозванные или восстановленные токены нельзя редактировать.',
            'already-inactive' => 'Этот токен уже неактивен.',
        ],

        'errors' => [
            'admin-has-token' => 'У выбранного администратора уже есть активный токен интеграции.',
            'only-draft-generate' => 'Генерировать можно только черновые токены.',
        ],

        'validation' => [
            'ip-invalid' => 'Каждый разрешенный IP-адрес должен быть действительным адресом IPv4 или IPv6 (поддерживается нотация CIDR).',
            'cidr-prefix-invalid' => 'Префикс CIDR недействителен для данной версии IP.',
        ],

        'configuration' => [
            'api' => [
                'title' => 'API',
                'info' => 'Настройки API Bagisto и его модулей администрирования.',
            ],

            'integration' => [
                'title' => 'Интеграция',
                'info' => 'Управляйте плагином интеграции API, используемым для выдачи токенов API администратора.',
            ],

            'settings' => [
                'title' => 'Настройки модуля',
                'info' => 'Включите или отключите плагин интеграции API. Если этот параметр отключен, его боковое меню скрыто, а страницы возвращают ошибку 404.',
                'enable' => 'Включить модуль интеграции API',
            ],
        ],

        'emails' => [
            'generated' => [
                'subject' => 'Был создан новый токен API: :name.',
                'greeting' => 'В вашей учетной записи только что был создан токен интеграции API с именем «:name».',
            ],

            'regenerated' => [
                'subject' => 'Ваш токен API был восстановлен: :name',
                'greeting' => 'Токен интеграции API с именем «:name» был только что создан заново. Предыдущий токен перестал работать — действителен только новый.',
            ],

            'revoked' => [
                'subject' => 'Ваш токен API был отозван: :name',
                'greeting' => 'Токен интеграции API с именем «:name» был отозван. Любой клиент, использующий его, потерял доступ.',
            ],

            'details' => [
                'name' => 'Имя токена',
                'date' => 'Дата',
                'ip' => 'С IP',
            ],

            'revoke-hint' => 'Если вы этого не ожидали, немедленно отзовите токен, используя кнопку ниже.',
            'revoke-btn' => 'Отозвать этот токен',
            'revoke-expiry' => 'Эта ссылка для отзыва действительна в течение 7 дней. После этого войдите в панель администратора для управления токеном.',
            'no-action' => 'Никаких действий не требуется — это письмо является лишь подтверждением.',
        ],

        'revoke-confirmation' => [
            'title' => 'Отозвать токен API',
            'confirm-title' => 'Отозвать этот токен?',
            'confirm-message' => 'Токен ":name" немедленно перестанет работать для всех клиентов, которые его используют.',
            'confirm-button' => 'Отозвать токен',
            'success-title' => 'Токен отозван',
            'success-message' => 'Токен «:name» отозван. Любой клиент, использующий его, немедленно теряет доступ.',
            'already-inactive-title' => 'Токен уже неактивен',
            'already-inactive-message' => 'Токен «:name» уже был отозван или восстановлен. Никаких дальнейших действий не требуется.',
        ],

        'confirm' => [
            'generate' => [
                'title' => 'Создать токен',
                'message' => 'Создать токен сейчас? Открытый текст будет показан только один раз — скопируйте его, прежде чем покинуть страницу.',
            ],

            'regenerate' => [
                'title' => 'Восстановить токен',
                'message' => 'Восстановить токен? Старый токен немедленно перестанет работать, а новый открытый текст будет показан только один раз.',
            ],

            'revoke' => [
                'title' => 'Отозвать токен',
                'message' => 'Отозвать этот токен? Любой клиент, использующий его, немедленно потеряет доступ. Это действие невозможно отменить.',
            ],
        ],
    ],
];
