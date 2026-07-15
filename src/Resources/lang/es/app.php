<?php

return [
    'graphql' => [
        'cart' => [
            'authentication-required' => 'Se requiere el token de autenticación',
            'invalid-token' => 'Token de autenticación inválido o caducado',
            'unauthorized-access' => 'Acceso no autorizado al carrito',
            'authenticated-only' => 'Solo los usuarios autenticados pueden obtener sus carritos',
            'merge-requires-auth' => 'La fusión de invitado requiere autenticación',
            'unknown-operation' => 'Operación de carrito desconocida',

            'cart-not-found' => 'Carrito no encontrado',
            'guest-cart-not-found' => 'Carrito de invitado no encontrado',
            'product-not-found' => 'Producto no encontrado',

            'product-id-quantity-required' => 'El ID del producto y la cantidad son obligatorios',
            'cart-item-id-quantity-required' => 'El ID del artículo del carrito y la cantidad son obligatorios',
            'cart-item-id-required' => 'El ID del artículo del carrito es obligatorio',
            'item-ids-required' => 'Se requiere la matriz de ID de artículos',
            'coupon-code-required' => 'El código de cupón es obligatorio',
            'address-data-required' => 'El país, el estado y el código postal son obligatorios',

            'add-product-failed' => 'No se pudo agregar el producto al carrito',
            'update-item-failed' => 'No se pudo actualizar el artículo del carrito',
            'remove-item-failed' => 'No se pudo eliminar el artículo del carrito',
            'apply-coupon-failed' => 'No se pudo aplicar el cupón',
            'remove-coupon-failed' => 'No se pudo eliminar el cupón',
            'move-to-wishlist-failed' => 'No se pudo mover el artículo a la lista de deseos',
            'estimate-shipping-failed' => 'No se pudo estimar el envío',

            'product-added-successfully' => 'Producto agregado al carrito correctamente',
            'guest-cart-merged' => 'Carrito de invitado fusionado correctamente',
            'using-authenticated-cart' => 'Usando el carrito del cliente autenticado',
            'cart-item-not-found' => 'Artículo del carrito no encontrado',
            'new-guest-cart-created' => 'Nuevo carrito de invitado creado con un token de sesión único',
            'select-items-to-remove' => 'Por favor, seleccione los artículos que desea eliminar',
            'select-items-to-move-wishlist' => 'Por favor, seleccione los artículos que desea mover a la lista de deseos',
            'invalid-or-expired-token' => 'El token del carrito es inválido o ha caducado. Por favor, cree un nuevo carrito.',
            'invalid-token-of-login-user' => 'El token del usuario que ha iniciado sesión es inválido.',
        ],

        'token-verification' => [
            'invalid-operation' => 'Operación inválida',
            'invalid-input-data' => 'Datos de entrada inválidos',
            'token-required' => 'El token es obligatorio',
            'invalid-token-format' => 'Formato de token inválido',
            'token-not-found-or-expired' => 'Token no encontrado o caducado',
            'customer-not-found' => 'Cliente no encontrado',
            'customer-account-suspended' => 'La cuenta del cliente está suspendida',
            'error-verifying-token' => 'Error al verificar el token',
            'token-is-valid' => 'El token es válido',
        ],

        'forgot-password' => [
            'invalid-operation' => 'Operación inválida',
            'invalid-input-data' => 'Datos de entrada inválidos',
            'email-required' => 'El correo electrónico es obligatorio',
            'reset-link-sent' => 'Enlace de restablecimiento enviado correctamente a su correo electrónico',
            'email-not-found' => 'Dirección de correo electrónico no encontrada',
            'error-sending-reset-link' => 'Ocurrió un error al enviar el enlace de restablecimiento',
        ],

        'logout' => [
            'invalid-operation' => 'Operación inválida',
            'invalid-input-data' => 'Datos de entrada inválidos',
            'token-required' => 'El token es obligatorio',
            'invalid-token-format' => 'Formato de token inválido',
            'logged-out-successfully' => 'Sesión cerrada correctamente',
            'token-not-found-or-expired' => 'Token no encontrado o ya caducado',
            'error-during-logout' => 'Error durante el cierre de sesión',
        ],

        'address' => [
            'deleted-successfully' => 'Dirección eliminada correctamente',
            'authentication-required' => 'Se requiere el token de autenticación',
            'invalid-token' => 'Token inválido o caducado',
            'unknown-operation' => 'Operación desconocida',
            'address-id-required' => 'El ID de la dirección es obligatorio',
            'address-not-found' => 'Dirección no encontrada o no pertenece a este cliente',
            'retrieved' => 'Direcciones obtenidas correctamente',
            'fetch-failed' => 'No se pudieron obtener las direcciones:',
        ],

        'customer-profile' => [
            'authentication-required' => 'Se requiere el token de autenticación. Por favor, proporcione el token en la entrada de la consulta',
            'invalid-token' => 'Token inválido o caducado',
        ],

        'customer' => [
            'password-mismatch' => 'La contraseña y la confirmación de la contraseña no coinciden',
            'confirm-password-required' => 'Se requiere confirmar la contraseña al cambiarla',
            'unauthenticated' => 'No autenticado. Por favor, inicie sesión para realizar esta acción',
        ],

        'product-review' => [
            'product-id-required' => 'El ID del producto es obligatorio',
            'product-not-found' => 'Producto no encontrado',
            'rating-invalid' => 'La calificación debe estar entre 1 y 5',
            'title-required' => 'El título de la reseña es obligatorio',
            'comment-required' => 'El comentario de la reseña es obligatorio',
        ],

        'product' => [
            'not-found-with-sku' => 'No product found with SKU',
            'not-found-with-url-key' => 'No product found with URL key',
            'parameters-required' => 'At least one of the following parameters must be provided: "sku", "id", "urlKey"',
        ],

        'auth' => [
            'no-token-provided' => 'No se proporcionó ningún token de autenticación. Por favor, proporcione el token en el encabezado de autorización como "Bearer <token>" o en el campo input.token',
            'invalid-or-expired-token' => 'Token inválido o caducado',
            'request-not-found' => 'Solicitud no encontrada en el contexto',
            'token-required' => 'Authentication token is required. Please provide the token either in the GraphQL mutation input field or in the Authorization header as "Bearer <token>"',
            'unknown-resource' => 'Recurso desconocido',
            'cannot-update-other-profile' => 'No autorizado: No se puede actualizar el perfil de otro cliente',
        ],

        'upload' => [
            'invalid-base64' => 'Datos de imagen codificados en base64 inválidos',
            'size-exceeds-limit' => 'El tamaño de la imagen no debe superar los 5 MB',
            'invalid-format' => 'Formato de imagen inválido. Por favor, proporcione una imagen codificada en base64 con el esquema de URI de datos (data:image/jpeg;base64,...)',
            'failed' => 'Error al subir la imagen',
        ],

        'attribute' => [
            'code-already-exists' => 'El código de atributo ya existe',
        ],

        'login' => [
            'invalid-credentials' => 'Correo electrónico o contraseña inválidos',
            'account-suspended' => 'Su cuenta ha sido suspendida',
            'successful' => 'Ha iniciado sesión correctamente',
            'invalid-request' => 'Solicitud de inicio de sesión inválida',
        ],

        'checkout' => [
            'invalid-input' => 'Datos de entrada inválidos para la operación de pago',
            'billing-address-required' => 'La dirección de facturación es obligatoria',
            'shipping-address-required' => 'La dirección de envío es obligatoria para los envíos',
            'address-save-failed' => 'No se pudo guardar la dirección',
            'address-saved' => 'Dirección guardada correctamente',
            'shipping-method-required' => 'El método de envío es obligatorio',
            'invalid-shipping-method' => 'Método de envío inválido o no disponible',
            'shipping-method-save-failed' => 'No se pudo guardar el método de envío',
            'shipping-method-saved' => 'Método de envío guardado correctamente',
            'shipping-method-error' => 'Error al guardar el método de envío',
            'payment-method-required' => 'El método de pago es obligatorio',
            'invalid-payment-method' => 'Método de pago inválido o no disponible',
            'payment-method-save-failed' => 'No se pudo guardar el método de pago',
            'payment-method-saved' => 'Método de pago guardado correctamente',
            'payment-method-error' => 'Error al guardar el método de pago',
            'order-creation-failed' => 'Error al crear el pedido: el ID del pedido es nulo o el pedido no se guardó',
            'order-retrieval-failed' => 'No se pudo recuperar el pedido creado',
            'order-creation-error' => 'No se pudo crear el pedido',
            'cart-empty' => 'El carrito está vacío',
            'account-suspended' => 'Su cuenta ha sido suspendida. Por favor, contacte con soporte.',
            'account-inactive' => 'Su cuenta está inactiva. Por favor, contacte con soporte.',
            'minimum-order-not-met' => 'El monto mínimo del pedido es :amount',
            'email-required' => 'La dirección de correo electrónico es obligatoria para crear el pedido',
            'unknown-operation' => 'Operación de pago desconocida',
        ],

        'customer-addresses' => [
            'token-required' => 'Se requiere el token para obtener las direcciones del cliente',
            'invalid-or-expired-token' => 'Token inválido o caducado',
            'token-validation-failed' => 'Falló la validación del token',
        ],

        'product' => [
            'type' => 'Tipo de producto',
            'attribute-family' => 'Familia de atributos',
            'sku' => 'SKU',
            'name' => 'Nombre',
            'description' => 'Descripción',
            'short-description' => 'Descripción corta',
            'status' => 'Estado',
            'new' => 'Nuevo',
            'featured' => 'Destacado',
            'price' => 'Precio',
            'special-price' => 'Precio especial',
            'weight' => 'Peso',
            'cost' => 'Costo',
            'length' => 'Longitud',
            'width' => 'Ancho',
            'height' => 'Altura',
            'color' => 'Color',
            'size' => 'Tamaño',
            'brand' => 'Marca',
            'super-attributes' => 'Superatributos',
        ],

        'compare-item' => [
            'id-required' => 'El ID del artículo de comparación es obligatorio',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/shop/compare-items/1" o ID numérico',
            'not-found' => 'Artículo de comparación no encontrado',
            'product-id-required' => 'El ID del producto es obligatorio',
            'customer-id-required' => 'El ID del cliente es obligatorio',
            'product-not-found' => 'Producto no encontrado',
            'customer-not-found' => 'Cliente no encontrado',
            'already-exists' => 'Este producto ya está en su lista de comparación',
        ],

        'downloadable-product' => [
            'download-link-not-found' => 'Enlace de descarga no encontrado o caducado',
            'purchased-link-not-found' => 'Enlace de compra no encontrado',
            'file-not-found' => 'Archivo no encontrado',
            'download-successful' => 'Archivo listo para descargar',
            'token-required' => 'Se requiere el token de descarga',
            'invalid-token' => 'Token de descarga inválido o caducado',
            'token-expired' => 'El token de descarga ha caducado. Por favor, genera uno nuevo',
            'access-denied' => 'Acceso denegado: No tienes permiso para descargar este archivo',
            'redirect-external-url' => 'Redirigiendo a la URL de descarga externa',
            'file-error' => 'Ocurrió un error al procesar su solicitud de descarga',
            'unauthorized-access' => 'Acceso no autorizado al recurso de descarga',
        ],
    ],

    'integration' => [
        'menu' => [
            'title' => 'Integración',
            'tokens' => 'Fichas',
        ],

        'history' => [
            'menu' => [
                'title' => 'Historia',
            ],

            'acl' => [
                'title' => 'Historial de cambios de API',
                'delete' => 'Eliminar historial',
            ],

            'index' => [
                'title' => 'Historial de cambios de API',
                'info' => 'Cada creación, actualización y eliminación realizada a través de la API de administración, con quién lo hizo, qué token y qué cambió.',
                'cleanup-btn' => 'Eliminar registros antiguos',
                'cleanup-days' => 'Eliminar registros que tengan más de estos días',
                'cleanup-confirm' => '¿Eliminar todo el historial anterior al número de días indicado? Esto no se puede deshacer.',
            ],

            'view' => [
                'title' => 'Cambiar',
                'back-btn' => 'Atrás',
                'admin' => 'administrador',
                'token' => 'ficha',
                'action' => 'acción',
                'resource' => 'Recurso',
                'method' => 'Método',
                'ip' => 'Dirección IP',
                'date' => 'Fecha',
                'version' => 'Versión',
                'url' => 'Punto final',
                'request-details' => 'Detalles de la solicitud',
                'changes' => 'Cambios',
                'field' => 'campo',
                'old' => 'valor antiguo',
                'new' => 'Nuevo valor',
                'no-field-changes' => 'No se registraron cambios a nivel de campo para esta entrada.',
                'same-request' => 'Otros cambios en la misma solicitud',
                'version-chain' => 'Historial de versiones de este registro',
            ],

            'datagrid' => [
                'id' => 'identificación',
                'date' => 'Fecha',
                'admin' => 'administrador',
                'token' => 'ficha',
                'action' => 'acción',
                'operation' => 'Operación',
                'resource' => 'Recurso',
                'version' => 'Versión',
                'method' => 'Método',
                'ip' => 'IP',
                'view' => 'Ver',
                'delete' => 'Eliminar',
            ],

            'events' => [
                'created' => 'Creado',
                'updated' => 'Actualizado',
                'deleted' => 'Eliminado',
            ],

            'deleted' => ':count registros del historial eliminados.',
            'cleanup-input-required' => 'Proporcione una cantidad de días o una fecha para limpiar.',
        ],

        'acl' => [
            'title' => 'Integración',
            'create' => 'Crear integración',
            'edit' => 'Editar integración',
            'delete' => 'Revocar token de integración',
            'generate' => 'Generar token de integración',
            'regenerate' => 'Regenerar token de integración',
        ],

        'index' => [
            'title' => 'Integraciones',
            'create-btn' => 'Crear integración',
        ],

        'create' => [
            'title' => 'Crear integración',
            'save-btn' => 'Guardar',
            'back-btn' => 'Atrás',
        ],

        'edit' => [
            'title' => 'Editar integración',
            'save-btn' => 'Guardar',
            'back-btn' => 'Atrás',
            'generate-btn' => 'Generar token',
            'regenerate-btn' => 'Regenerar token',
            'revoke-btn' => 'Revocar token',
            'copy-btn' => 'Copiar',
            'token-warning' => 'Guarde este token ahora; no se volverá a mostrar.',
            'token-label' => 'ficha',
            'not-generated' => 'Aún no generado',
            'masked' => '(Almacenado: solo se muestra una vez en la generación)',
            'history-banner' => 'Este token ya no está activo.',
        ],

        'fields' => [
            'name' => 'Nombre',
            'description' => 'Descripción',
            'assign-user' => 'Asignar usuario',
            'permission-type' => 'Tipo de permiso',
            'access-control' => 'Control de acceso',
            'general' => 'generales',
            'token-settings' => 'Configuración de tokens',
            'valid-till' => 'Válido hasta',
            'rate-limit-per-minute' => 'Límite de velocidad (por minuto)',
            'rate-limit-per-day' => 'Límite de tarifa (por día)',
            'never-expires' => 'Nunca caduca',
            'expires-on' => 'Expira el',
            'unlimited' => 'Ilimitado',
            'limit-to' => 'Limitar a',
            'requests-per-minute' => 'solicitudes / minuto',
            'requests-per-day' => 'solicitudes / día',
            'select-admin' => 'Seleccione un administrador',
            'no-available-admins' => 'No hay administradores disponibles: cada administrador ya tiene un token activo.',
            'same-as-web-hint' => 'El token reflejará en vivo los permisos de función actuales del administrador asignado.',
            'ip-allowlist' => 'Lista de direcciones IP permitidas',
            'ip-any' => 'Cualquier IP (predeterminada)',
            'ip-restricted' => 'Restringido a IP específicas',
            'ip-list-hint' => 'Una entrada por línea. Admite IPv4, IPv6 y CIDR (por ejemplo, 10.0.0.0/24 o 2001:db8::/32). Déjelo en blanco para permitir todas las IP.',
        ],

        'permission_type' => [
            'all' => 'Todos',
            'custom' => 'personalizado',
            'same_as_web' => 'Igual que el permiso web',
        ],

        'status' => [
            'draft' => 'Borrador',
            'active' => 'Activo',
            'revoked' => 'Revocado',
            'regenerated' => 'regenerado',
        ],

        'datagrid' => [
            'id' => 'identificación',
            'name' => 'Nombre',
            'admin' => 'administrador',
            'token' => 'ficha',
            'status' => 'Estado',
            'permission-type' => 'Tipo de permiso',
            'expires-at' => 'Válido hasta',
            'last-used-at' => 'Usado por última vez',
            'created-at' => 'Creado en',
            'edit' => 'Editar',
            'revoke' => 'Revocar',
        ],

        'messages' => [
            'draft-created' => 'Integración creada. Genera el token para comenzar a usarlo.',
            'updated' => 'La integración se actualizó correctamente.',
            'generated' => 'Token generado. Cópialo ahora; no se volverá a mostrar.',
            'regenerated' => 'Token regenerado. Copie el nuevo token ahora; no se volverá a mostrar.',
            'revoked' => 'Token revocado exitosamente.',
            'generate-only-draft' => 'Solo se pueden generar tokens en borradores de integraciones.',
            'regenerate-only-active' => 'Sólo se pueden regenerar tokens activos.',
            'cannot-edit-historic' => 'Los tokens revocados o regenerados no se pueden editar.',
            'already-inactive' => 'Este token ya está inactivo.',
        ],

        'errors' => [
            'admin-has-token' => 'El administrador seleccionado ya tiene un token de integración activo.',
        ],

        'validation' => [
            'ip-invalid' => 'Cada IP permitida debe ser una dirección IPv4 o IPv6 válida (se admite la notación CIDR).',
            'cidr-prefix-invalid' => 'El prefijo CIDR no es válido para la versión de IP determinada.',
        ],

        'configuration' => [
            'api' => [
                'title' => 'API',
                'info' => 'Configuraciones para la API de Bagisto y sus módulos de administración.',
            ],
            'integration' => [
                'title' => 'Integración',
                'info' => 'Administre el complemento de integración de API utilizado para emitir tokens de API de administrador.',
            ],
            'settings' => [
                'title' => 'Configuración del módulo',
                'info' => 'Habilite o deshabilite el complemento de integración API. Cuando está deshabilitado, su menú de la barra lateral está oculto y sus páginas devuelven 404.',
                'enable' => 'Habilitar el módulo de integración API',
            ],
        ],

        'emails' => [
            'generated' => [
                'subject' => 'Se generó un nuevo token API: :name',
                'greeting' => 'Se acaba de generar un token de integración de API llamado ":name" en su cuenta.',
            ],
            'regenerated' => [
                'subject' => 'Su token API fue regenerado: :name',
                'greeting' => 'El token de integración de API denominado ":name" acaba de regenerarse. El token anterior dejó de funcionar; solo el nuevo es válido.',
            ],
            'revoked' => [
                'subject' => 'Su token API fue revocado: :name',
                'greeting' => 'Se revocó el token de integración de API denominado ":name". Cualquier cliente que lo utilice ha perdido el acceso.',
            ],

            'details' => [
                'name' => 'Nombre del token',
                'date' => 'Fecha',
                'ip' => 'Desde IP',
            ],

            'revoke-hint' => 'Si no esperaba esto, revoque el token inmediatamente usando el botón a continuación.',
            'revoke-btn' => 'Revocar este token',
            'revoke-expiry' => 'Este enlace de revocación es válido por 7 días. Después de eso, inicie sesión en el panel de administración para administrar el token.',
            'no-action' => 'No es necesario realizar ninguna acción: este correo electrónico es solo una confirmación.',
        ],

        'revoke-confirmation' => [
            'title' => 'Revocar token API',
            'success-title' => 'Token revocado',
            'success-message' => 'El token ":name" ha sido revocado. Cualquier cliente que lo utilice ha perdido el acceso inmediatamente.',
            'already-inactive-title' => 'Token ya inactivo',
            'already-inactive-message' => 'El token ":name" ya fue revocado o regenerado. No se necesita ninguna otra acción.',
        ],

        'confirm' => [
            'generate' => [
                'title' => 'Generar token',
                'message' => '¿Generar el token ahora? El texto sin formato se mostrará solo una vez; cópielo antes de salir de la página.',
            ],
            'regenerate' => [
                'title' => 'Regenerar token',
                'message' => '¿Regenerar el token? El token antiguo dejará de funcionar inmediatamente y el nuevo texto sin formato se mostrará solo una vez.',
            ],
            'revoke' => [
                'title' => 'Revocar token',
                'message' => '¿Revocar este token? Cualquier cliente que lo utilice perderá el acceso inmediatamente. Esta acción no se puede deshacer.',
            ],
        ],
    ],
];
