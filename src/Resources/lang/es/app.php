<?php

return [

    'graphql' => [
        'headers' => [
            'unknown-channel' => 'El canal ":code" del encabezado X-Channel no existe.',
            'storefront-key-required' => 'El encabezado X-STOREFRONT-KEY es obligatorio',
            'storefront-key-invalid' => 'Clave de tienda inválida',
            'storefront-key-rate-limited' => 'Límite de solicitudes excedido. Vuelva a intentarlo en :seconds segundos',
        ],

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

            'file-token-invalid' => 'La referencia del archivo subido es inválida o ha caducado. Vuelva a subir el archivo.',
            'file-token-forbidden' => 'Este archivo subido no pertenece a su carrito.',
            'customizable-option-required' => 'La opción personalizable ":label" es obligatoria.',
            'customizable-file-option-invalid' => 'La opción seleccionada no es una opción de archivo para este producto.',
            'customizable-file-invalid' => 'Falta el archivo, tiene una extensión no admitida o es demasiado grande.',
            'cart-item-id-required' => 'El ID del artículo del carrito es obligatorio',
            'item-ids-required' => 'Se requiere la matriz de ID de artículos',

            'event-booking-quantity-not-changeable' => 'No se puede cambiar la cantidad del producto de reserva de evento. La cantidad se determina según la selección de entradas.',
            'appointment-booking-quantity-not-changeable' => 'No se puede cambiar la cantidad del producto de reserva de cita.',
            'coupon-code-required' => 'El código de cupón es obligatorio',
            'address-data-required' => 'El país, el estado y el código postal son obligatorios',
            'grouped-qty-required' => 'El producto agrupado requiere las cantidades seleccionadas. Envíe groupedQty como cadena JSON, p. ej. {"101":2,"102":1}.',
            'grouped-qty-must-include-all' => 'El producto agrupado requiere cantidades para todos los productos asociados. IDs faltantes: :ids.',
            'grouped-qty-invalid-associated' => 'Las cantidades del producto agrupado contienen IDs de productos asociados inválidos: :ids.',
            'grouped-qty-invalid-quantity' => 'Cantidad inválida para el producto asociado con ID :id. La cantidad debe ser un número entero no negativo.',
            'bundle-qty-not-changeable' => 'No se puede cambiar la cantidad de la opción de paquete ":option". La cantidad permitida es :qty.',

            'add-product-failed' => 'No se pudo agregar el producto al carrito',
            'update-item-failed' => 'No se pudo actualizar el artículo del carrito',
            'remove-item-failed' => 'No se pudo eliminar el artículo del carrito',
            'remove-coupon-failed' => 'No se pudo eliminar el cupón',
            'coupon-applied' => 'Cupón aplicado correctamente',
            'coupon-removed' => 'Cupón eliminado correctamente',
            'move-to-wishlist-failed' => 'No se pudo mover el artículo a la lista de deseos',
            'estimate-shipping-failed' => 'No se pudo estimar el envío',

            'product-added-successfully' => 'Producto agregado al carrito correctamente',
            'cart-item-updated-successfully' => 'Artículo del carrito actualizado correctamente',
            'guest-cart-merged' => 'Carrito de invitado fusionado correctamente',
            'using-authenticated-cart' => 'Usando el carrito del cliente autenticado',
            'cart-item-not-found' => 'Artículo del carrito no encontrado',
            'new-guest-cart-created' => 'Nuevo carrito de invitado creado con un token de sesión único',
            'select-items-to-remove' => 'Por favor, seleccione los artículos que desea eliminar',
            'select-items-to-move-wishlist' => 'Por favor, seleccione los artículos que desea mover a la lista de deseos',
            'invalid-or-expired-token' => 'El token del carrito es inválido o ha caducado. Por favor, cree un nuevo carrito.',
            'invalid-token-of-login-user' => 'El token del usuario que ha iniciado sesión es inválido.',
            'booking-note-required' => 'booking.note es obligatorio para la reserva de mesa.',
            'booking-date-required' => 'booking.date es obligatorio cuando booking.slot es un rango horario con formato.',
            'booking-qty-required' => 'booking.qty es obligatorio para la reserva de evento.',
            'booking-ticket-qty-invalid' => 'La cantidad de entradas del evento debe ser un número no negativo.',
            'booking-ticket-required' => 'Seleccione la cantidad de al menos una entrada para la reserva de evento.',
            'booking-slot-unavailable' => 'El horario seleccionado no está disponible para la fecha indicada.',
            'invalid-quantity' => 'La cantidad debe ser al menos 1',
            'product-id-required' => 'El ID del producto es obligatorio',
            'shipping-address-required' => 'El país y el código postal son obligatorios para estimar el envío',
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
            'unauthenticated' => 'No autenticado. Por favor, inicie sesión para realizar esta acción',
        ],

        'forgot-password' => [
            'invalid-operation' => 'Operación inválida',
            'invalid-input-data' => 'Datos de entrada inválidos',
            'email-required' => 'El correo electrónico es obligatorio',
            'reset-link-sent' => 'Enlace de restablecimiento enviado correctamente a su correo electrónico',
            'email-not-found' => 'Dirección de correo electrónico no encontrada',
            'throttled' => 'Demasiados intentos de restablecimiento. Espere antes de volver a intentarlo.',
            'error-sending-reset-link' => 'Ocurrió un error al enviar el enlace de restablecimiento',
        ],

        'reset-password' => [
            'failed' => 'No se pudo restablecer la contraseña. Inténtelo de nuevo.',
        ],

        'rate-limit' => [
            'too-many-attempts' => 'Demasiados intentos. Inténtelo de nuevo en :seconds segundos.',
        ],

        'logout' => [
            'invalid-operation' => 'Operación inválida',
            'invalid-input-data' => 'Datos de entrada inválidos',
            'token-required' => 'El token es obligatorio',
            'invalid-token-format' => 'Formato de token inválido',
            'logged-out-successfully' => 'Sesión cerrada correctamente',
            'token-not-found-or-expired' => 'Token no encontrado o ya caducado',
            'error-during-logout' => 'Error durante el cierre de sesión',
            'unauthenticated' => 'No autenticado. Por favor, inicie sesión para realizar esta acción',
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
            'field-required' => 'El campo :field es obligatorio',
        ],

        'customer-profile' => [
            'authentication-required' => 'Se requiere el token de autenticación. Por favor, proporcione el token en la entrada de la consulta',
            'invalid-token' => 'Token inválido o caducado',
            'profile-updated' => 'Perfil del cliente actualizado correctamente',
        ],

        'customer' => [
            'password-mismatch' => 'La contraseña y la confirmación de la contraseña no coinciden',
            'confirm-password-required' => 'Se requiere confirmar la contraseña al cambiarla',
            'current-password-required' => 'Se requiere la contraseña actual para cambiar su contraseña',
            'current-password-incorrect' => 'La contraseña actual que ingresó es incorrecta',
            'verify-email-to-login' => 'Su cuenta ha sido creada. Por favor, verifique su dirección de correo electrónico para iniciar sesión.',
            'delete-password-required' => 'Se requiere su contraseña para eliminar su cuenta',
            'delete-password-incorrect' => 'La contraseña que ingresó es incorrecta',
            'delete-has-active-orders' => 'Su cuenta no se puede eliminar mientras tenga pedidos pendientes o en proceso',
            'unauthenticated' => 'No autenticado. Por favor, inicie sesión para realizar esta acción',
            'id-required' => 'El ID del cliente es obligatorio',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/admin/customers/1" o ID numérico',
            'not-found' => 'Cliente no encontrado',
            'phone-special-chars-not-allowed' => 'El número de móvil solo puede contener dígitos. No se permiten caracteres especiales',
            'email-already-taken' => 'Esta dirección de correo electrónico ya está registrada en otra cuenta',
            'invalid-gender' => 'Valor de género inválido ":gender". Los valores permitidos son: :valid',
        ],

        'product-review' => [
            'id-required' => 'El ID de la reseña del producto es obligatorio',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/shop/reviews/1" o ID numérico',
            'not-found' => 'Reseña del producto con ID ":id" no encontrada',
            'item-not-found' => 'Elemento ":id" no encontrado.',
            'product-id-required' => 'El ID del producto es obligatorio',
            'product-not-found' => 'Producto no encontrado',
            'rating-invalid' => 'La calificación debe estar entre 1 y 5',
            'title-required' => 'El título de la reseña es obligatorio',
            'comment-required' => 'El comentario de la reseña es obligatorio',
            'review-disabled' => 'Las reseñas de productos están deshabilitadas actualmente',
            'guest-review-disabled' => 'No se permiten reseñas de invitados. Por favor, inicie sesión para enviar una reseña',
            'owner-required' => 'Por favor, inicie sesión para gestionar su reseña',
            'not-owned' => 'Esta reseña no fue escrita por usted y no se puede modificar ni eliminar',
            'invalid-attachments' => 'Formato de archivos adjuntos inválido.',
            'invalid-product-id-format' => 'Formato de ID de producto inválido. Se esperaba formato IRI como "/api/shop/products/1" o ID numérico',
        ],

        'customer-review' => [
            'id-required' => 'El ID de la reseña del cliente es obligatorio',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/shop/customer-reviews/1" o ID numérico',
            'not-found' => 'Reseña del cliente con ID ":id" no encontrada',
        ],

        'newsletter' => [
            'error-during-subscription' => 'No se puede completar la suscripción en este momento. Inténtelo de nuevo más tarde',
        ],

        'contact-us' => [
            'success' => 'Su consulta se ha enviado correctamente. Nos pondremos en contacto con usted pronto',
            'failed' => 'No se puede enviar su consulta en este momento. Inténtelo de nuevo más tarde',
            'invalid-input' => 'Datos del formulario de contacto inválidos',
        ],

        'customer-order' => [
            'id-required' => 'El ID del pedido del cliente es obligatorio',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/shop/customer-orders/1" o ID numérico',
            'not-found' => 'Pedido del cliente con ID ":id" no encontrado',
        ],

        'return' => [
            'not-found' => 'Solicitud de devolución no encontrada.',
            'disabled' => 'Las devoluciones no están habilitadas para esta tienda.',
            'invalid-input' => 'Faltan detalles de la devolución o son inválidos.',
            'invalid-resolution-type' => 'El tipo de resolución debe ser "return" o "cancel_items".',
            'agreement-required' => 'Debe aceptar la política de devoluciones.',
            'invalid-order' => 'El pedido seleccionado no es válido.',
            'invalid-item' => 'El artículo seleccionado no es apto para devolución.',
            'qty-exceeds' => 'La cantidad solicitada supera la cantidad que se puede devolver.',
            'invalid-package-condition' => 'El estado del paquete debe ser "open" o "packed".',
            'invalid-image' => 'Uno o más de los archivos subidos tienen un tipo de archivo no admitido.',
            'invalid-custom-field' => 'Campo personalizado desconocido ":id".',
            'custom-field-required' => 'El campo ":field" es obligatorio.',
            'invalid-custom-field-value' => 'El valor de ":field" no es una de las opciones permitidas.',
            'created' => 'Solicitud de devolución creada correctamente.',
            'canceled' => 'Solicitud de devolución cancelada correctamente.',
            'already-canceled' => 'La solicitud de devolución ya está cancelada.',
            'cancel-not-allowed' => 'Esta solicitud de devolución no se puede cancelar.',
            'close-not-allowed' => 'Esta solicitud de devolución no se puede cerrar.',
            'reopened' => 'Solicitud de devolución reabierta correctamente.',
            'reopen-not-allowed' => 'Esta solicitud de devolución no se puede reabrir.',
            'closed' => 'Solicitud de devolución cerrada correctamente.',
            'message-sent' => 'Mensaje enviado correctamente.',
            'attachment-not-found' => 'Archivo adjunto del mensaje no encontrado.',
        ],

        'eu-withdrawal' => [
            'not-found' => 'Declaración de desistimiento no encontrada.',
            'order-not-found' => 'No se encontró el pedido o no es apto para desistimiento.',
            'too-many-attempts' => 'Demasiados intentos. Inténtelo de nuevo en :seconds segundos.',
            'created' => 'Declaración de desistimiento presentada correctamente.',
        ],

        'customer-order-shipment' => [
            'id-required' => 'El ID del envío es obligatorio',
            'not-found' => 'Envío con ID ":id" no encontrado o no pertenece a este cliente',
            'item-not-found' => 'Artículo del envío con ID ":id" no encontrado o no pertenece a este cliente',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/shop/customer-order-shipments/1" o ID numérico',
        ],

        'cancel-order' => [
            'order-id-required' => 'El ID del pedido es obligatorio para cancelar un pedido',
            'not-found' => 'Pedido con ID ":id" no encontrado o no pertenece a este cliente',
            'cancel-success' => 'El pedido se ha cancelado correctamente',
            'cancel-failed' => 'No se puede cancelar el pedido. Es posible que ya se haya procesado, enviado o cancelado',
        ],

        'reorder' => [
            'order-id-required' => 'El ID del pedido es obligatorio para volver a pedir',
            'not-found' => 'Pedido con ID ":id" no encontrado o no pertenece a este cliente',
            'reorder-success' => 'Se han agregado a su carrito :count artículo(s) de su pedido anterior',
            'no-items-added' => 'No se pudo agregar ningún artículo al carrito. Es posible que los productos estén agotados o no disponibles',
        ],

        'customer-downloadable-product' => [
            'id-required' => 'El ID del producto descargable del cliente es obligatorio',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/shop/customer-downloadable-products/1" o ID numérico',
            'not-found' => 'Producto descargable del cliente con ID ":id" no encontrado',

            'download' => [
                'unauthenticated' => 'No autorizado: se requiere la autenticación del cliente.',
                'not-found' => 'Producto descargable no encontrado.',
                'pending' => 'La descarga está pendiente. Espere a que se facture el pedido.',
                'limit-exceeded' => 'Se superó el límite de descargas.',
                'no-downloads-remaining' => 'No quedan más descargas disponibles.',
                'file-not-found' => 'Archivo no encontrado.',
                'link-not-found' => 'Enlace descargable no encontrado.',
            ],
        ],

        'customer-invoice' => [
            'id-required' => 'El ID de la factura del cliente es obligatorio',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/shop/customer-invoices/1" o ID numérico',
            'not-found' => 'Factura del cliente con ID ":id" no encontrada',
        ],

        'auth' => [
            'no-token-provided' => 'No se proporcionó ningún token de autenticación. Por favor, proporcione el token en el encabezado de autorización como "Bearer <token>" o en el campo input.token',
            'invalid-or-expired-token' => 'Token inválido o caducado',
            'request-not-found' => 'Solicitud no encontrada en el contexto',
            'token-required' => 'Authentication token is required. Please provide the token either in the GraphQL mutation input field or in the Authorization header as "Bearer <token>"',
            'unknown-resource' => 'Recurso desconocido',
            'cannot-update-other-profile' => 'No autorizado: No se puede actualizar el perfil de otro cliente',
            'refresh-token-invalid' => 'El token de actualización no es válido o ha caducado. Vuelve a iniciar sesión.',
            'too-many-login-attempts' => 'Demasiados intentos de inicio de sesión. Vuelva a intentarlo en :seconds segundos.',
            'too-many-refresh-requests' => 'Demasiadas renovaciones de token. Inténtelo de nuevo en :seconds segundos.',
            'rate-limit-exceeded' => 'Demasiados intentos.',
            'access-token-expired' => 'El token de acceso ha caducado. Actualízalo para continuar.',
            'logged-out-all' => 'Ha cerrado todas las sesiones.',
            'session-not-found' => 'Sesión no encontrada.',
            'session-revoked' => 'La sesión ha sido revocada.',
        ],

        'upload' => [
            'invalid-base64' => 'Datos de imagen codificados en base64 inválidos',
            'size-exceeds-limit' => 'El tamaño de la imagen no debe superar los 5 MB',
            'invalid-format' => 'Formato de imagen inválido. Por favor, proporcione una imagen codificada en base64 con el esquema de URI de datos (data:image/jpeg;base64,...)',
            'failed' => 'Error al subir la imagen',
        ],

        'attribute' => [
            'code-already-exists' => 'El código de atributo ya existe',
            'option-id-required' => 'El parámetro "attributeId" es obligatorio al consultar directamente las opciones de atributo',
            'id-required' => 'El ID del atributo es obligatorio',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/shop/attributes/1" o ID numérico',
            'not-found' => 'Atributo no encontrado',
        ],

        'attribute-option' => [
            'id-required' => 'El ID de la opción de atributo es obligatorio',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/shop/attribute-options/1" o ID numérico',
            'not-found' => 'Opción de atributo no encontrada',
        ],

        'channel' => [
            'id-required' => 'El ID del canal es obligatorio',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/shop/channels/1" o ID numérico',
            'not-found' => 'Canal no encontrado',
        ],

        'country-state' => [
            'id-required' => 'El ID del estado del país es obligatorio',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/shop/country-states/1" o ID numérico',
            'not-found' => 'Estado del país no encontrado',
            'country-id-required' => 'El ID del país es obligatorio',
        ],

        'country' => [
            'id-required' => 'El ID del país es obligatorio',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/shop/countries/1" o ID numérico',
            'not-found' => 'País no encontrado',
        ],

        'login' => [
            'invalid-credentials' => 'Correo electrónico o contraseña inválidos',
            'account-suspended' => 'Su cuenta ha sido suspendida',
            'account-inactive' => 'Tu activación requiere la aprobación del administrador.',
            'email-not-verified' => 'Verifica primero tu cuenta de correo electrónico.',
            'successful' => 'Ha iniciado sesión correctamente',
            'invalid-request' => 'Solicitud de inicio de sesión inválida',
        ],

        'social-login' => [
            'signed-in' => 'Sesión iniciada correctamente.',
            'token-required' => 'Se requiere un token de inicio de sesión social.',
            'invalid-token' => 'El token de inicio de sesión social no es válido o ha caducado. Inténtalo de nuevo.',
            'google-not-configured' => 'El inicio de sesión con Google no está configurado para esta tienda.',
            'wrong-audience' => 'Este token se emitió para otra aplicación.',
            'email-required' => 'El proveedor no compartió una dirección de correo electrónico. Regístrate con correo electrónico.',
            'account-inactive' => 'Tu activación requiere la aprobación del administrador.',
            'provider-not-supported' => 'Este proveedor de inicio de sesión social no es compatible.',
            'provider-disabled' => 'Este proveedor de inicio de sesión social no está habilitado.',
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
            'order-placed' => 'Pedido realizado correctamente',
            'order-redirect-required' => 'Este método de pago requiere que el comprador complete el pago en la pasarela. Envíelo a redirectUrl; el pedido se crea una vez que la pasarela confirma el pago.',
            'order-creation-failed' => 'Error al crear el pedido: el ID del pedido es nulo o el pedido no se guardó',
            'order-retrieval-failed' => 'No se pudo recuperar el pedido creado',
            'order-creation-error' => 'No se pudo crear el pedido',
            'cart-empty' => 'El carrito está vacío',
            'account-suspended' => 'Su cuenta ha sido suspendida. Por favor, contacte con soporte.',
            'account-inactive' => 'Su cuenta está inactiva. Por favor, contacte con soporte.',
            'minimum-order-not-met' => 'El monto mínimo del pedido es :amount',
            'guest-checkout-not-allowed' => 'Inicie sesión para finalizar la compra. La compra como invitado no está disponible para este carrito.',
            'email-required' => 'La dirección de correo electrónico es obligatoria para crear el pedido',
            'unknown-operation' => 'Operación de pago desconocida',
        ],

        'customer-addresses' => [
            'token-required' => 'Se requiere el token para obtener las direcciones del cliente',
            'invalid-or-expired-token' => 'Token inválido o caducado',
            'token-validation-failed' => 'Falló la validación del token',
        ],

        'product' => [
            'not-found' => 'Producto no encontrado',
            'not-found-with-sku' => 'No se encontró ningún producto con el SKU',
            'not-found-with-url-key' => 'No se encontró ningún producto con la clave de URL',
            'parameters-required' => 'Se debe proporcionar al menos uno de los siguientes parámetros: "sku", "id", "urlKey"',
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
            'id-required' => 'El ID del producto es obligatorio',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/shop/products/1" o ID numérico',
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
            'unauthenticated' => 'No autorizado: se requiere la autenticación del cliente',
            'link-id-required' => 'downloadableLinkPurchasedId es obligatorio',
            'link-not-found' => 'Enlace descargable no encontrado',
            'not-owner' => 'No autorizado: este enlace de descarga no le pertenece',
            'pending' => 'La descarga está pendiente. Espere a que se facture el pedido.',
            'limit-exceeded' => 'Se superó el límite de descargas',
            'no-downloads-left' => 'No quedan más descargas disponibles',
        ],

        'booking-slot' => [
            'id-required' => 'El ID del producto de reserva es obligatorio.',
            'date-required' => 'La fecha es obligatoria.',
            'product-not-found' => 'Producto de reserva no encontrado.',
            'invalid-type' => 'Este tipo de reserva no es compatible.',
            'invalid-time-range' => 'Rango horario inválido.',
            'invalid-time-format' => 'Formato de hora inválido.',
        ],

        'resolver' => [
            'no-resolve-info' => 'GraphQL ResolveInfo no está disponible en el contexto. No se puede determinar la clase del modelo.',
            'unknown-resource-class' => 'No se puede determinar la clase del recurso a partir del tipo de retorno de GraphQL',
        ],

        'docs' => [
            'openapi-unavailable' => 'No se pudo instanciar OpenAPI Factory',
        ],

        'locale' => [
            'id-required' => 'El ID del idioma es obligatorio',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/shop/locales/1" o ID numérico',
            'not-found' => 'Idioma no encontrado',
        ],

        'category' => [
            'id-required' => 'El ID de la categoría es obligatorio',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/shop/categories/1" o ID numérico',
            'not-found' => 'Categoría no encontrada',
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
            'delete-all-success' => 'Todos los artículos de comparación se han eliminado correctamente',
        ],

        'wishlist' => [
            'id-required' => 'El ID del artículo de la lista de deseos es obligatorio',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/shop/wishlists/1" o ID numérico',
            'not-found' => 'Artículo de la lista de deseos no encontrado',
            'product-id-required' => 'El ID del producto es obligatorio',
            'customer-id-required' => 'El ID del cliente es obligatorio',
            'product-not-found' => 'Producto no encontrado',
            'product-disabled' => 'Este producto está deshabilitado actualmente',
            'customer-not-found' => 'Cliente no encontrado',
            'already-exists' => 'Este producto ya está en su lista de deseos',
            'added' => 'Artículo agregado correctamente a la lista de deseos',
            'removed' => 'Artículo eliminado correctamente de la lista de deseos',
            'product-removed' => 'El producto ha sido eliminado',
            'wishlist-item-id-required' => 'El ID del artículo de la lista de deseos es obligatorio',
            'invalid-quantity' => 'La cantidad debe ser mayor que 0',
            'move-to-cart-missing-options' => 'Al producto le faltan opciones obligatorias. Por favor, configúrelo manualmente',
            'moved-to-cart-success' => 'Artículo movido al carrito correctamente',
            'delete-all-success' => 'Todos los artículos de la lista de deseos se han eliminado correctamente',
        ],

        'gdpr' => [
            'disabled' => 'Las solicitudes de datos del RGPD están deshabilitadas. Habilite el RGPD desde la configuración de administración.',
            'unauthenticated' => 'Debe iniciar sesión para gestionar las solicitudes de datos del RGPD.',
            'not-found' => 'Solicitud de datos del RGPD no encontrada',
            'type-required' => 'El tipo de solicitud es obligatorio',
            'type-invalid' => 'El tipo de solicitud debe ser "delete" o "update"',
            'message-required' => 'El mensaje es obligatorio',
            'raised' => 'Su solicitud de datos del RGPD se ha creado correctamente.',
            'revoked' => 'Su solicitud de datos del RGPD se ha revocado correctamente.',
            'revoke-not-allowed' => 'Esta solicitud de datos del RGPD ya no se puede revocar.',
            'deleted' => 'Su solicitud de datos del RGPD se ha eliminado correctamente.',
        ],

        'section' => [
            'id-required' => 'El ID de la sección es obligatorio',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/shop/sections/1" o ID numérico',
            'not-found' => 'Sección no encontrada',
        ],

        'install' => [
            'starting' => 'Iniciando la instalación de API Platform...',
            'completed-success' => '✓ ¡La instalación de API Platform se completó correctamente!',
            'failed' => '✗ La instalación falló: ',

            'provider-already-registered' => 'ℹ El Service Provider ya está registrado',
            'provider-not-registered' => '⚠ No se pudo registrar el Service Provider automáticamente. Agregue :provider a bootstrap/providers.php manualmente.',
            'provider-registered' => '✓ Service Provider registrado en bootstrap/providers.php',
            'provider-file-not-found' => 'Archivo no encontrado: :file',
            'provider-permission-denied' => 'Permiso denegado: no se puede escribir en :file. Por favor, verifique los permisos del archivo.',

            'config-already-exists' => 'ℹ El archivo de configuración ya existe',
            'config-published' => '✓ Configuración de API Platform publicada en config/',
            'config-source-not-found' => 'Archivo de configuración de origen no encontrado: :file',
            'config-permission-denied' => 'Permiso denegado: no se puede escribir en :directory. Por favor, verifique los permisos del directorio.',

            'translatable-not-found' => 'ℹ TranslatableModel no encontrado - omitiendo',
            'translatable-already-abstract' => 'ℹ TranslatableModel ya es abstracto',
            'translatable-made-abstract' => '✓ TranslatableModel convertido en abstracto',
            'translatable-permission-denied' => 'Permiso denegado: no se puede escribir en :file. Por favor, verifique los permisos del archivo.',

            'providers-already-registered' => 'ℹ Los proveedores de API Platform ya están registrados',
            'providers-registered' => '✓ Proveedores de API Platform registrados en bootstrap/app.php',
            'providers-file-not-found' => 'Archivo no encontrado: :file',
            'providers-permission-denied' => 'Permiso denegado: no se puede escribir en :file. Por favor, verifique los permisos del archivo.',
            'providers-not-found' => 'No se encontró la creación de Application en bootstrap/app.php',

            'assets-published' => '✓ Recursos del paquete publicados',
            'running-migrations' => 'Ejecutando las migraciones de la base de datos...',
            'migrations-completed' => '✓ Migraciones de la base de datos completadas',
            'clearing-caches' => 'Limpiando y optimizando cachés...',
            'caches-optimized' => '✓ Cachés limpiadas y optimizadas',
            'generating-api-key' => 'Generando la clave de API...',
            'api-key-generated' => '✓ Clave de API generada correctamente',
            'api-key-already-exists' => 'ℹ La clave de API de la tienda predeterminada ya existe - omitiendo la generación',

            'migrations-error' => 'Las migraciones de la base de datos fallaron.',
            'migrations-error-running' => 'Error al ejecutar las migraciones de la base de datos: :error',
            'cache-clear-error' => 'Error al limpiar la caché.',
            'cache-optimize-error' => 'La optimización falló.',
            'cache-error' => 'Error al limpiar y optimizar las cachés: :error',
            'api-key-generation-error' => 'La generación de la clave de API falló.',
            'api-key-error' => 'Error al generar la clave de API: :error',

            'key-management-required' => '--key es obligatorio para la acción :action',
            'key-rotation-error' => 'Error al rotar la clave: :error',
            'key-deactivation-error' => 'Error al desactivar la clave: :error',
            'key-not-found' => 'Clave de API no encontrada: :identifier',

            'maintenance-starting' => '🔄 Iniciando el mantenimiento de claves de API...',
            'maintenance-complete' => '✅ Mantenimiento de claves de API completado',
            'cleanup-expired-keys' => '🧹 Limpiando las claves caducadas...',
            'cleanup-expired-none' => '   ℹ️ No hay claves caducadas que limpiar',
            'invalidate-deprecated' => '⚠️ Invalidando las claves obsoletas...',
            'invalidate-deprecated-none' => '   ℹ️ No hay claves obsoletas que invalidar',
            'notify-expiring' => '📧 Enviando notificaciones de caducidad...',
            'notify-expiring-none' => '   ℹ️ No hay claves que requieran notificaciones',

            'key-rotated-success' => '✅ ¡Clave rotada correctamente!',
            'key-deactivated-success' => '✅ ¡Clave desactivada correctamente!',
            'deactivation-cancelled' => 'Desactivación cancelada.',
            'cleanup-cancelled' => 'Limpieza cancelada.',

            'key-status-title' => 'Estado de la clave: :name',
            'key-status-active' => 'Activa: :status',
            'key-status-usable' => 'Utilizable: :status',
            'key-status-expired' => 'Caducada: :status',
            'key-status-deprecated' => 'Obsoleta: :status',
            'key-status-expires-at' => 'Caduca el: :expires',
            'key-status-days-until-expiry' => 'Días hasta la caducidad: :days',
            'key-status-last-used' => 'Último uso: :date',
            'key-status-rotated-from' => 'Rotada desde: :key',
            'key-status-rotated-keys' => 'Claves rotadas desde esta: :count',

            'policy-compliance-summary' => 'Resumen de cumplimiento de la política de rotación de claves de API',
            'available-actions' => 'Acciones disponibles: rotate, deactivate, cleanup, status, expiring, unused, summary',

            'asset-linked-success' => '✓ Recursos de API Platform enlazados correctamente',
            'symlink-create-failed' => 'No se pudo crear el enlace simbólico; copiando los recursos en su lugar...',
            'asset-copy-warning' => 'Advertencia: no se pudieron enlazar ni copiar los recursos de API Platform. Es posible que se requiera una configuración manual.',
            'asset-copied-success' => '✓ Recursos de API Platform copiados correctamente',

            'key-name-required' => 'El nombre de la clave de tienda no puede estar vacío.',
            'key-generated-success' => '¡Clave de tienda generada correctamente!',
            'key-details' => 'Detalles de la clave:',
            'key-secure-warning' => '¡Mantenga esta clave segura! Se utilizará en el encabezado X-STOREFRONT-KEY.',
            'key-share-warning' => 'No comparta esta clave públicamente ni la incluya en el control de versiones.',

            'key-already-exists' => 'Ya existe una clave de tienda con el nombre \':name\'.',
            'cleanup-success' => '✅ Se limpiaron :count claves caducadas',
            'confirm-cleanup' => 'Esto eliminará de forma lógica todas las claves caducadas. ¿Continuar?',
            'confirm-deactivate' => '¿Está seguro de que desea desactivar la clave: :name?',

            'old-key' => 'Clave anterior: :name',
            'old-key-id' => 'ID de la clave anterior: :id',
            'deprecation-date' => 'Fecha de obsolescencia: :date',
            'new-key' => 'Nueva clave: :name',
            'new-key-id' => 'ID de la nueva clave: :id',
            'new-key-value' => 'Valor de la nueva clave: :key',
            'expires-at' => 'Caduca el: :date',
            'invalid-action' => 'Acción inválida: :action',
            'key-active' => 'Activa: :status',
            'key-usable' => 'Utilizable: :status',
            'key-expired' => 'Caducada: :status',
            'key-deprecated' => 'Obsoleta: :status',
            'key-field-id' => 'ID',
            'key-field-name' => 'Nombre',
            'key-field-key' => 'Clave',
            'key-field-rate-limit' => 'Límite de solicitudes',
            'key-field-status' => 'Estado',
            'key-days' => ' días',
            'key-never' => 'Nunca',
            'key-requests-minute' => ' solicitudes/minuto',
            'key-unlimited' => 'Ilimitado',
            'rate-limit-exceeded' => 'El límite de solicitudes indicado supera el máximo permitido de :max. Se establecerá en :max solicitudes/minuto.',

            'vendor-path-not-found' => 'No se encontró la ruta de vendor de API Platform en: :path',
            'assets-already-linked' => 'Los recursos de API Platform ya están enlazados en: :path',
            'no-keys-expiring' => 'No hay claves que caduquen en los próximos :days días',
            'keys-expiring-title' => 'Claves que caducan en los próximos :days días:',
            'key-display-format' => '• :name (ID: :id)',
            'key-expires-display' => '  Caduca: :date (quedan :days días)',
            'no-unused-keys' => 'No se encontraron claves sin usar (> :days días)',
            'unused-keys-title' => 'Claves sin usar (> :days días):',
            'key-last-used-display' => '  Último uso: :date',
            'total-keys' => 'Total de claves: :count',
            'valid-keys' => 'Claves válidas: :count',
            'expired-keys' => 'Claves caducadas: :count',
            'deprecated-keys' => 'Claves obsoletas: :count',
            'keys-expiring-soon' => 'Claves que caducan pronto (7 días): :count',
            'unused-keys-summary' => 'Claves sin usar (90 días): :count',
            'recently-rotated' => 'Rotadas recientemente (30 días): :count',
            'cleanup-success-message' => '   ✅ Se limpiaron :count claves caducadas',
            'invalidate-success-message' => '   ✅ Se invalidaron :count claves obsoletas',
            'notify-success-message' => '   ✅ Se enviaron :count notificaciones de caducidad',
            'notify-failed-message' => '   ⚠️ No se pudo notificar sobre :key: :error',
            'publish-assets-warning' => 'Advertencia: no se pudieron publicar los recursos del paquete. :error',
            'env-file-not-found' => 'No se encontró el archivo .env. Créelo antes de instalar.',
            'env-permission-denied' => 'No se puede escribir en el archivo .env. Compruebe sus permisos.',
            'env-config-saved' => '✓ Configuración del entorno de API Platform guardada correctamente',
            'api-endpoints' => 'Endpoints de la API:',
            'api-documentation' => 'Documentación de la API: :url',
            'api-landing-page' => 'Página de inicio de la API: :url',
            'graphql-playground' => 'GraphQL Playground: :url',
            'rest-api-storefront' => 'Swagger de la API REST de la tienda: :url',
            'rest-api-admin' => 'Swagger de la API REST de administración: :url',
            'generating-jwt-keys' => 'Generando las claves de firma JWT para los tokens de la API de administración...',
            'jwt-keys-generated' => '✓ Las claves de firma JWT están listas',
            'jwt-keys-warning' => 'Advertencia: no se pudieron generar las claves de firma JWT. Ejecute php artisan bagisto-api:jwt-keys manualmente. :error',
            'admin-login' => 'Inicio de sesión de la API de administración: :url',
            'admin-api-tokens' => 'Tokens de la API de administración (integraciones de servidor): :url',
            'key-rotation-invalid-key' => 'No se puede rotar una clave inválida o caducada',
            'env-file-not-found' => 'No se encontró el archivo .env',
            'env-permission-denied' => 'Permiso denegado: no se puede escribir en el archivo .env. Compruebe los permisos del archivo.',
        ],

        'attribute-translation' => [
            'id-required' => 'El ID de la traducción del atributo es obligatorio',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/shop/attribute-translations/1" o ID numérico',
            'not-found' => 'Traducción del atributo no encontrada',
        ],

        'currency' => [
            'id-required' => 'El ID de la moneda es obligatorio',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/shop/currencies/1" o ID numérico',
            'not-found' => 'Moneda no encontrada',
        ],

        'customer-address' => [
            'id-required' => 'El ID de la dirección del cliente es obligatorio',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/shop/customer-addresses/1" o ID numérico',
        ],

        'customer-order-shipment-item' => [
            'id-required' => 'El ID del artículo del envío es obligatorio',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/shop/customer-order-shipment-items/1" o ID numérico',
            'not-found' => 'Artículo del envío no encontrado',
        ],

        'page' => [
            'id-required' => 'El ID de la página es obligatorio',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/shop/pages/1" o ID numérico',
            'not-found' => 'Página no encontrada',
        ],

        'page-translation' => [
            'id-required' => 'El ID de la traducción de la página es obligatorio',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/shop/page-translations/1" o ID numérico',
            'not-found' => 'Traducción de la página no encontrada',
        ],

        'product-bundle-option' => [
            'id-required' => 'El ID de la opción del paquete es obligatorio',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/shop/product-bundle-options/1" o ID numérico',
            'not-found' => 'Opción del paquete no encontrada',
        ],

        'product-bundle-option-product' => [
            'id-required' => 'El ID del producto de la opción del paquete es obligatorio',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/shop/product-bundle-option-products/1" o ID numérico',
            'not-found' => 'Producto de la opción del paquete no encontrado',
        ],

        'product-downloadable-link' => [
            'id-required' => 'El ID del enlace descargable es obligatorio',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/shop/product-downloadable-links/1" o ID numérico',
            'not-found' => 'Enlace descargable no encontrado',
        ],

        'product-downloadable-sample' => [
            'id-required' => 'El ID de la muestra descargable es obligatorio',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/shop/product-downloadable-samples/1" o ID numérico',
            'not-found' => 'Muestra descargable no encontrada',
        ],

        'product-grouped-product' => [
            'id-required' => 'El ID del producto agrupado es obligatorio',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/shop/product-grouped-products/1" o ID numérico',
            'not-found' => 'Producto agrupado no encontrado',
        ],

        'product-images' => [
            'id-required' => 'El ID de la imagen del producto es obligatorio',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/shop/product-images/1" o ID numérico',
            'not-found' => 'Imagen del producto no encontrada',
        ],

        'product-videos' => [
            'id-required' => 'El ID del vídeo del producto es obligatorio',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/shop/product-videos/1" o ID numérico',
            'not-found' => 'Vídeo del producto no encontrado',
        ],

        'theme-customization' => [
            'id-required' => 'El ID de la personalización del tema es obligatorio',
            'invalid-id-format' => 'Formato de ID inválido. Se esperaba formato IRI como "/api/shop/theme-customizations/1" o ID numérico',
            'not-found' => 'Personalización del tema no encontrada',
        ],

        'unknown-resource' => [
            'id-required' => 'El ID del recurso es obligatorio',
            'not-found' => 'Recurso no encontrado',
        ],
    ],

    'admin' => [
        'profile' => [
            'unauthenticated' => 'No está autenticado.',
        ],

        'dashboard' => [
            'invalid-type' => 'Tipo de estadística del panel desconocido. Tipos válidos: over-all, today, stock-threshold-products, total-sales, top-selling-products, top-customers.',
        ],

        'rma' => [
            'not-found' => 'Solicitud de devolución no encontrada.',
            'invalid-input' => 'Faltan detalles de la devolución o son inválidos.',
            'invalid-order' => 'El pedido seleccionado no es válido.',
            'invalid-item' => 'El artículo seleccionado no es apto para devolución.',
            'invalid-resolution-type' => 'El tipo de resolución debe ser "return" o "cancel_items".',
            'qty-exceeds' => 'La cantidad solicitada supera la cantidad que se puede devolver.',
            'created' => 'Solicitud de devolución creada correctamente.',
            'status-updated' => 'Estado de la devolución actualizado correctamente.',
            'invalid-status' => 'Estado de devolución inválido.',
            'reopened' => 'Solicitud de devolución reabierta correctamente.',
            'reopen-not-allowed' => 'Esta solicitud de devolución no se puede reabrir.',
            'refund-failed' => 'No se pudo crear el reembolso para esta devolución.',
            'message-sent' => 'Mensaje enviado correctamente.',
            'no-permission' => 'No tiene permiso para realizar esta acción.',
            'reason-not-found' => 'Motivo de devolución no encontrado.',
            'reason-deleted' => 'Motivo de devolución eliminado correctamente.',
            'status-not-found' => 'Estado de devolución no encontrado.',
            'status-deleted' => 'Estado de devolución eliminado correctamente.',
            'status-default-undeletable' => 'No se puede eliminar un estado de devolución predeterminado.',
            'rule-not-found' => 'Regla de devolución no encontrada.',
            'rule-deleted' => 'Regla de devolución eliminada correctamente.',
            'custom-field-not-found' => 'Campo personalizado de devolución no encontrado.',
            'custom-field-deleted' => 'Campo personalizado de devolución eliminado correctamente.',
            'custom-field-options-required' => 'Se requiere al menos una opción para este tipo de campo.',
            'mass-indices-required' => 'Se requiere al menos un id.',
            'mass-delete-success' => 'Elementos seleccionados eliminados.',
            'mass-update-success' => 'Elementos seleccionados actualizados.',
            'mass-value-invalid' => 'El valor del estado debe ser 0 o 1.',
            'image-not-found' => 'Imagen de la devolución no encontrada.',
            'attachment-not-found' => 'Archivo adjunto del mensaje no encontrado.',
        ],

        'eu-withdrawal' => [
            'not-found' => 'Declaración de desistimiento no encontrada.',
            'no-permission' => 'No tiene permiso para gestionar los desistimientos de la UE.',
            'declined' => 'Desistimiento marcado como rechazado.',
            'refunded' => 'Desistimiento marcado como reembolsado.',
            'confirmation-resent' => 'Correo electrónico de confirmación reenviado correctamente.',
            'confirmation-failed' => 'No se pudo enviar el correo electrónico de confirmación.',
        ],

        'reporting' => [
            'invalid-entity' => 'Entidad de informes desconocida.',
            'invalid-type' => 'Tipo de estadística de informes desconocido para esta entidad.',
            'export-format-unsupported' => 'Solo se admiten los formatos de exportación csv, xls y xlsx.',
        ],

        'configuration' => [
            'unauthenticated' => 'No está autenticado.',
            'no-permission' => 'No tiene permiso para actualizar la configuración.',
            'slug-required' => 'El parámetro de consulta `slug` es obligatorio.',
            'slug-not-found' => 'No hay ninguna sección de configuración registrada para el slug indicado.',
            'values-required' => 'Se requiere al menos un valor en `values`.',
            'scope-escape' => 'La clave `:key` está fuera del subárbol del slug solicitado.',
            'unknown-field' => 'La clave `:key` no es un campo de configuración registrado.',
            'validation-failed' => 'Uno o más valores de configuración no superaron la validación.',
            'file-upload-rest-only' => 'La subida de archivos binarios para el campo `:field` solo se admite a través del endpoint REST multipart, no mediante GraphQL.',
            'custom-view-readonly' => 'El campo `:field` es un campo personalizado renderizado con Blade y no se puede actualizar mediante la API.',
            'update-success' => 'Configuración actualizada correctamente.',
        ],

        'customer' => [
            'not-found' => 'Cliente no encontrado.',
            'no-permission' => 'No tiene permiso para realizar esta acción.',
            'has-active-orders' => 'El cliente tiene pedidos pendientes o en proceso y no se puede eliminar.',
            'deleted' => 'Cliente eliminado correctamente.',
            'mass-delete-indices-required' => 'Se requiere al menos un id de cliente.',
            'mass-delete-success' => 'Clientes eliminados.',
            'mass-update-indices-required' => 'Se requiere al menos un id de cliente.',
            'mass-update-value-invalid' => 'El valor del estado debe ser 0 o 1.',
            'mass-update-success' => 'Estado del cliente actualizado.',

            'address' => [
                'not-found' => 'Dirección no encontrada.',
                'not-owned' => 'Esta dirección no pertenece al cliente especificado.',
                'customer-id-required' => 'customerId es obligatorio para identificar al propietario de la dirección.',
                'deleted' => 'Dirección eliminada correctamente.',
            ],

            'note' => [
                'empty' => 'El texto de la nota es obligatorio.',
                'created' => 'Nota agregada correctamente.',
            ],

            'review' => [
                'not-found' => 'Reseña no encontrada.',
                'no-permission' => 'No tiene permiso para realizar esta acción.',
                'invalid-status' => 'El estado debe ser uno de los siguientes: pending, approved, disapproved.',
                'updated' => 'Reseña actualizada correctamente.',
                'deleted' => 'Reseña eliminada correctamente.',
                'mass-delete-indices-required' => 'Se requiere al menos un id de reseña.',
                'mass-delete-success' => 'Reseñas eliminadas.',
                'mass-update-indices-required' => 'Se requiere al menos un id de reseña.',
                'mass-update-value-invalid' => 'El valor del estado debe ser uno de los siguientes: pending, approved, disapproved.',
                'mass-update-success' => 'Estado de la reseña actualizado.',
            ],

            'group' => [
                'not-found' => 'Grupo de clientes no encontrado.',
                'deleted' => 'Grupo de clientes eliminado correctamente.',
                'is-system' => 'Los grupos de clientes del sistema no se pueden eliminar.',
                'has-customers' => 'El grupo de clientes tiene clientes asociados y no se puede eliminar.',
                'system-code-immutable' => 'No se puede cambiar el código de un grupo de clientes del sistema.',
                'system-flag-immutable' => 'No se puede cambiar el indicador is_user_defined de un grupo de clientes del sistema.',
                'mass-delete-indices-required' => 'Se requiere al menos un id de grupo de clientes.',
                'mass-delete-success' => 'Grupos de clientes eliminados.',
            ],

            'gdpr' => [
                'not-found' => 'Solicitud del RGPD no encontrada.',
                'no-permission' => 'No tiene permiso para realizar esta acción.',
                'deleted' => 'Solicitud del RGPD eliminada correctamente.',
                'invalid-status' => 'El estado debe ser uno de los siguientes: pending, processing, declined, approved, revoked.',
                'no-changes' => 'Se requiere al menos el estado o el mensaje.',
                'already-processed' => 'Esta solicitud del RGPD ya se ha procesado y no se puede volver a ejecutar.',

                'process' => [
                    'approved' => 'Solicitud del RGPD aprobada.',
                    'deleted' => 'Solicitud del RGPD aprobada y cliente eliminado.',
                ],
            ],
        ],

        'order' => [
            'not-found' => 'Pedido no encontrado.',

            'reorder' => [
                'success' => 'Pedido repetido correctamente. Se ha creado un nuevo carrito en borrador.',
                'cannot-reorder' => 'Este pedido no se puede repetir (pedido de invitado o uno o más artículos ya no están a la venta).',
                'failed' => 'No se pudo repetir el pedido. Inténtelo de nuevo.',
                'guest-not-supported' => 'No se admite repetir pedidos de invitados.',
                'items-not-saleable' => 'Uno o más artículos de este pedido ya no están disponibles para la compra.',
                'no-permission' => 'No tiene permiso para crear pedidos.',
                'disabled-in-settings' => 'La repetición de pedidos por parte del administrador está deshabilitada en la configuración de la tienda.',
            ],

            'actions' => [
                'cancel' => [
                    'closed' => 'Los pedidos cerrados no se pueden cancelar.',
                    'fraud' => 'Los pedidos fraudulentos no se pueden cancelar.',
                    'nothing-to-cancel' => 'No hay nada que cancelar en este pedido.',
                    'already-processed' => 'Este pedido ya se ha facturado o enviado y ya no se puede cancelar. En su lugar, puede emitir un reembolso.',
                    'no-permission' => 'No tiene permiso para cancelar pedidos.',
                    'success' => 'Pedido cancelado correctamente.',
                    'failed' => 'No se pudo cancelar el pedido.',
                ],

                'comment' => [
                    'empty' => 'El comentario es obligatorio.',
                    'success' => 'Comentario agregado al pedido.',
                    'failed' => 'No se pudo agregar el comentario.',
                ],

                'invoice' => [
                    'closed' => 'Los pedidos cerrados no se pueden facturar.',
                    'fraud' => 'Los pedidos fraudulentos no se pueden facturar.',
                    'nothing-to-invoice' => 'No hay nada que facturar en este pedido.',
                    'already-invoiced' => 'Ya se ha generado una factura para este pedido.',
                    'paypal-standard-blocked' => 'No se pueden crear facturas para pedidos pagados mediante PayPal Standard.',
                    'no-permission' => 'No tiene permiso para crear facturas.',
                    'items-required' => 'Se requiere al menos un artículo con una cantidad positiva.',
                    'qty-exceeds' => 'La cantidad solicitada (:requested) supera la cantidad disponible (:available) para el SKU :sku.',
                    'not-found' => 'Factura no encontrada.',
                    'success' => 'Factura creada correctamente.',
                    'failed' => 'No se pudo crear la factura.',
                    'pdf-failed' => 'No se pudo generar el PDF de la factura.',
                    'email-invalid' => 'Se requiere un correo electrónico de destinatario válido.',
                    'duplicate-sent' => 'Correo electrónico de la factura enviado a :email.',
                    'mass-status-indices-required' => 'Se requiere al menos un id de factura.',
                    'mass-status-value-invalid' => 'El estado debe ser uno de los siguientes: pending, paid, overdue.',
                    'mass-status-success' => 'Estado de la factura actualizado correctamente.',
                ],

                'shipment' => [
                    'closed' => 'Los pedidos cerrados no se pueden enviar.',
                    'fraud' => 'Los pedidos fraudulentos no se pueden enviar.',
                    'nothing-to-ship' => 'No hay nada que enviar en este pedido.',
                    'no-permission' => 'No tiene permiso para enviar pedidos.',
                    'source-required' => 'El origen de inventario es obligatorio.',
                    'items-required' => 'Se requiere al menos un artículo con una cantidad positiva.',
                    'qty-exceeds' => 'La cantidad solicitada (:requested) supera la cantidad disponible (:available) para el SKU :sku.',
                    'inventory-insufficient' => 'El inventario del origen seleccionado es insuficiente para el SKU :sku.',
                    'not-found' => 'Envío no encontrado.',
                    'success' => 'Envío creado correctamente.',
                    'failed' => 'No se pudo crear el envío.',
                ],

                'refund' => [
                    'closed' => 'Los pedidos cerrados no se pueden reembolsar.',
                    'fraud' => 'Los pedidos fraudulentos no se pueden reembolsar.',
                    'nothing-to-refund' => 'No queda nada por reembolsar en este pedido.',
                    'no-permission' => 'No tiene permiso para crear reembolsos.',
                    'qty-exceeds' => 'La cantidad solicitada (:requested) supera la cantidad disponible (:available) para el SKU :sku.',
                    'amount-zero' => 'El importe calculado del reembolso es cero. Ajuste la cantidad, el envío o los valores de ajuste.',
                    'amount-exceeds-max' => 'El importe del reembolso (:amount) supera el importe máximo reembolsable (:max).',
                    'not-found' => 'Reembolso no encontrado.',
                    'success' => 'Reembolso creado correctamente.',
                    'failed' => 'No se pudo crear el reembolso.',
                ],
            ],
        ],

        'appearance' => [
            'no-permission' => 'No tiene permiso para gestionar la apariencia.',

            'theme' => [
                'not-found' => 'Tema no encontrado.',
                'not-installed' => 'Este tema no está instalado.',
                'channel-ids-required' => 'Se requiere al menos un canal.',
                'unknown-channel' => 'Uno o más canales no existen.',
                'activated' => 'Tema activado correctamente.',
            ],

            'section' => [
                'not-found' => 'Sección no encontrada.',
                'invalid-payload' => 'Contenido de la sección no admitido.',
                'options-required' => 'Las opciones son obligatorias.',
                'status-required' => 'El estado es obligatorio.',
                'section-ids-required' => 'Se requiere al menos una sección.',
                'reorder-single-scope' => 'Las secciones solo se pueden reordenar dentro de un tema y un canal a la vez.',
                'reorder-incomplete' => 'Para reordenar se necesitan todas las secciones del tema y del canal, en el orden en que deben mostrarse.',
                'unknown-section' => 'Una o más secciones no existen.',
                'file-required' => 'Se requiere un archivo.',
                'created' => 'Sección creada correctamente.',
                'updated' => 'Sección actualizada correctamente.',
                'deleted' => 'Sección eliminada correctamente.',
                'draft-saved' => 'Cambios de la sección preparados correctamente.',
                'status-staged' => 'Estado de la sección preparado correctamente.',
                'order-staged' => 'Orden de las secciones preparado correctamente.',
                'duplicated' => 'Sección copiada correctamente.',
                'published' => 'Cambios de la sección publicados correctamente.',
                'discarded' => 'Cambios de la sección descartados correctamente.',
                'media-uploaded' => 'Archivo multimedia subido correctamente.',
            ],
        ],

        'sales' => [
            'no-permission' => 'No tiene permiso para ver este recurso de ventas.',

            'invoice' => [
                'not-found' => 'Factura no encontrada.',
                'no-permission' => 'No tiene permiso para ver las facturas.',
            ],

            'shipment' => [
                'not-found' => 'Envío no encontrado.',
                'no-permission' => 'No tiene permiso para ver los envíos.',
            ],

            'refund' => [
                'not-found' => 'Reembolso no encontrado.',
                'no-permission' => 'No tiene permiso para ver los reembolsos.',
                'export-format-unsupported' => 'Solo se admiten exportaciones en csv, xls y xlsx.',
            ],

            'transaction' => [
                'not-found' => 'Transacción no encontrada.',
                'no-permission' => 'No tiene permiso para ver las transacciones.',

                'create' => [
                    'invoice-required' => 'El campo invoiceId es obligatorio.',
                    'payment-method-required' => 'El campo paymentMethod es obligatorio.',
                    'amount-invalid' => 'El campo amount es obligatorio y debe ser numérico.',
                    'invoice-missing' => 'No se encontró la factura especificada.',
                    'already-paid' => 'Esta factura ya está pagada en su totalidad.',
                    'amount-exceeds' => 'El importe del pago supera el total general de la factura.',
                    'amount-zero' => 'El importe del pago debe ser mayor que cero.',
                ],
            ],

            'booking' => [
                'not-found' => 'Reserva no encontrada.',
                'no-permission' => 'No tiene permiso para ver las reservas.',
            ],

            'export' => [
                'format-unsupported' => 'Solo se admiten exportaciones en csv, xls y xlsx.',
            ],
        ],

        'cart' => [
            'not-found' => 'Carrito no encontrado.',
            'not-draft' => 'Este carrito no se puede modificar a través de la API de administración.',
            'product-required' => 'productId es obligatorio.',
            'product-not-found' => 'Producto no encontrado.',
            'booking-unsupported' => 'Los productos de reserva no se pueden agregar a un pedido en borrador del administrador. Las compras de reservas deben realizarse a través de la tienda del cliente.',
            'product-not-saleable' => 'Este producto no está disponible para la venta (agotado o deshabilitado) y no se puede agregar al pedido.',
            'qty-required' => 'qty es obligatorio y debe ser un objeto no vacío de itemId => cantidad.',
            'cart-item-required' => 'cartItemId es obligatorio.',
            'item-added' => 'Artículo agregado al carrito.',
            'item-add-failed' => 'No se pudo agregar el artículo al carrito.',
            'item-updated' => 'Artículos del carrito actualizados.',
            'item-update-failed' => 'No se pudieron actualizar los artículos del carrito.',
            'item-removed' => 'Artículo eliminado del carrito.',
            'item-remove-failed' => 'No se pudo eliminar el artículo del carrito.',
            'address-saved' => 'Dirección guardada.',
            'address-required' => 'Se requiere una dirección de facturación.',
            'address-incomplete' => 'Se requiere :field para guardar la dirección.',
            'coupon-code-required' => 'Se requiere un código de cupón.',
            'coupon-applied' => 'Cupón aplicado.',
            'coupon-already-applied' => 'El cupón ya está aplicado a este carrito.',
            'coupon-not-found' => 'Cupón no encontrado o inactivo.',
            'coupon-removed' => 'Cupón eliminado.',
            'coupon-error' => 'Se produjo un error al aplicar el cupón.',
            'unknown-error' => 'Se produjo un error.',
            'draft-created' => 'Carrito en borrador creado.',
            'draft-failed' => 'No se pudo crear el carrito en borrador.',
            'addresses-required-for-shipping' => 'Las direcciones deben guardarse antes de seleccionar un método de envío.',
            'shipping-required-for-payment' => 'El método de envío debe seleccionarse antes del método de pago.',
            'shipping-method-required' => 'El método de envío es obligatorio.',
            'shipping-method-saved' => 'Método de envío guardado.',
            'shipping-method-failed' => 'No se pudo guardar el método de envío.',
            'payment-method-required' => 'El método de pago es obligatorio.',
            'payment-method-saved' => 'Método de pago guardado.',
            'payment-method-failed' => 'No se pudo guardar el método de pago.',

            'place-order' => [
                'success' => 'Pedido realizado correctamente.',
                'failed' => 'No se pudo realizar el pedido.',
                'error' => 'No se pudo realizar el pedido: el carrito tiene errores.',
                'minimum-order-error' => 'El importe mínimo del pedido es :amount. Agregue más artículos antes de realizar el pedido.',
                'empty-cart' => 'El carrito está vacío: agregue artículos antes de realizar un pedido.',
                'addresses-required' => 'Las direcciones de facturación y de envío deben guardarse antes de realizar el pedido.',
                'shipping-required' => 'Se debe seleccionar un método de envío antes de realizar el pedido.',
                'payment-required' => 'Se debe seleccionar un método de pago antes de realizar el pedido.',
                'payment-method-unsupported' => 'Solo se admiten cashondelivery y moneytransfer para los pedidos realizados por el administrador.',
            ],
        ],

        'product' => [
            'not-found' => 'Producto no encontrado.',

            'mass-delete-success' => 'Productos eliminados correctamente.',
            'mass-update-status-success' => 'Estado de los productos actualizado correctamente.',
            'indices-required' => 'El campo indices es obligatorio y debe ser una matriz no vacía.',
            'indices-invalid' => 'El campo indices debe ser una matriz de IDs de producto enteros.',
            'value-invalid' => 'El campo value es obligatorio y debe ser 0 o 1.',
            'mass-delete-failed' => 'No se pudieron eliminar uno o más productos.',
            'no-permission' => 'No tiene permiso para gestionar productos.',

            'copy-success' => 'Producto copiado correctamente.',
            'copy-failed' => 'No se pudo copiar el producto.',
            'copy-variant-not-supported' => 'Las variantes de los productos configurables no se pueden copiar. Copie en su lugar el producto configurable principal.',

            'create' => [
                'created' => 'Producto creado correctamente.',
                'create-failed' => 'No se pudo crear el producto.',
                'sku-required' => 'El campo sku es obligatorio.',
                'sku-unique' => 'El sku ya está en uso.',
                'sku-invalid' => 'El sku solo puede contener letras, números y guiones (p. ej. "sp-001").',
                'attribute-family-required' => 'El campo attribute_family_id es obligatorio.',
                'attribute-family-not-found' => 'La familia de atributos seleccionada no existe.',
                'type-required' => 'El campo type es obligatorio.',
                'type-not-yet-supported' => 'El tipo de producto ":type" no es compatible con esta API. Tipos permitidos: simple, virtual, downloadable, grouped, bundle, configurable, booking.',
                'super-attributes-required' => 'El campo super_attributes es obligatorio cuando type=configurable. Proporcione un mapa de código (o id) de atributo a option_ids, p. ej. { "color": [1,2], "size": [4,5] }.',
                'super-attributes-invalid' => 'El campo super_attributes debe ser un mapa no vacío de código (o id) de atributo a una lista no vacía de option_ids.',
                'no-permission' => 'No tiene permiso para crear productos.',
            ],

            'update' => [
                'updated' => 'Producto actualizado correctamente.',
                'update-failed' => 'No se pudo actualizar el producto.',
                'id-required' => 'Se requiere un id de producto.',
                'url-key-required' => 'El campo url_key no puede estar vacío.',
                'url-key-unique' => 'El url_key ya está en uso por otro producto.',
                'special-price-invalid' => 'El special_price debe ser un número menor que price.',
                'special-price-date-range-invalid' => 'El special_price_to debe ser igual o posterior a special_price_from.',
                'boolean-field-invalid' => 'El campo :field debe ser 0 o 1.',
                'categories-invalid' => 'El campo categories debe ser una matriz de ids de categoría.',
                'channels-invalid' => 'El campo channels debe ser una matriz de ids de canal.',
                'super-attributes-cannot-change' => 'super_attributes no se puede cambiar después de crear el producto.',
                'sub-resource-stripped-images' => 'Las imágenes deben gestionarse mediante POST /api/admin/catalog/products/{id}/images. Se ignoró el campo images.',
                'sub-resource-stripped-inventories' => 'Los inventarios deben gestionarse mediante PUT /api/admin/catalog/products/{id}/inventories. Se ignoró el campo inventories.',
                'sub-resource-stripped-customer-group-prices' => 'Los precios por grupo de clientes deben gestionarse mediante /api/admin/catalog/products/{id}/customer-group-prices. Se ignoró el campo customer_group_prices.',
                'translations-single-locale' => 'Solo se actualizó el idioma solicitado. Para actualizar los idiomas :locales, envíe una solicitud independiente con ?locale=<code> para cada uno.',
            ],

            'delete' => [
                'deleted' => 'Producto eliminado correctamente.',
                'delete-failed' => 'No se pudo eliminar el producto.',
            ],

            'image' => [
                'uploaded' => 'Imagen del producto subida correctamente.',
                'updated' => 'Imagen del producto actualizada correctamente.',
                'update-empty' => 'Envíe un texto alternativo o una posición para actualizar.',
                'reordered' => 'Imágenes del producto reordenadas correctamente.',
                'deleted' => 'Imagen del producto eliminada correctamente.',
                'image-required' => 'Se requiere un archivo de imagen (campo multipart "image").',
                'image-invalid-type' => 'El tipo de imagen debe ser uno de los siguientes: bmp, jpeg, jpg, png, webp.',
                'image-too-large' => 'La imagen es demasiado grande. El tamaño máximo permitido es de 4 MB.',
                'not-found' => 'Imagen del producto no encontrada.',
                'no-permission' => 'No tiene permiso para gestionar las imágenes de productos.',
                'upload-failed' => 'No se pudo subir la imagen del producto.',
                'reorder-failed' => 'No se pudieron reordenar las imágenes del producto.',
                'delete-failed' => 'No se pudo eliminar la imagen del producto.',
                'order-required' => 'La matriz "order" es obligatoria y no debe estar vacía.',
                'order-invalid' => 'Cada entrada de "order" debe ser un objeto con id y position.',
                'id-not-on-product' => 'La imagen :id no pertenece a este producto.',
                'graphql-upload-unsupported' => 'No se admite la subida de imágenes mediante GraphQL. Use POST /api/admin/catalog/products/{productId}/images con multipart/form-data.',
            ],

            'downloadable' => [
                'file-required' => 'Se requiere un archivo (campo multipart "file").',
                'file-too-large' => 'El archivo es demasiado grande. El tamaño máximo permitido es de 128 MB.',
                'upload-failed' => 'No se pudo subir el archivo descargable.',
                'file-not-found' => 'No se encontró ningún archivo almacenado para este producto y atributo.',
                'no-permission' => 'No tiene permiso para gestionar los archivos de productos descargables.',
            ],

            'video' => [
                'uploaded' => 'Video del producto subido correctamente.',
                'deleted' => 'Video del producto eliminado correctamente.',
                'video-required' => 'Se requiere un archivo de video (campo multipart "video").',
                'video-invalid-type' => 'El tipo de video debe ser uno de los siguientes: mp4, webm, ogg, mov.',
                'video-too-large' => 'El video es demasiado grande. El tamaño máximo permitido es de 100 MB.',
                'not-found' => 'Video del producto no encontrado.',
                'no-permission' => 'No tiene permiso para gestionar los videos de productos.',
                'upload-failed' => 'No se pudo subir el video del producto.',
                'delete-failed' => 'No se pudo eliminar el video del producto.',
                'graphql-upload-unsupported' => 'No se admite la subida de videos mediante GraphQL. Use POST /api/admin/catalog/products/{productId}/videos con multipart/form-data.',
            ],

            'inventory' => [
                'updated' => 'Inventarios del producto guardados correctamente.',
                'inventories-required' => 'El campo inventories es obligatorio y debe ser un mapa no vacío de inventory_source_id a cantidad.',
                'inventories-invalid' => 'El campo inventories debe ser un mapa de inventory_source_id (entero positivo) a cantidad (entero no negativo).',
                'source-not-found' => 'El origen de inventario :id no existe.',
                'qty-invalid' => 'Cada cantidad debe ser un número entero no negativo.',
                'no-permission' => 'No tiene permiso para gestionar los inventarios de productos.',
                'not-found' => 'Producto no encontrado.',
            ],

            'customer-group-price' => [
                'created' => 'Precio por grupo de clientes agregado correctamente.',
                'updated' => 'Precio por grupo de clientes actualizado correctamente.',
                'deleted' => 'Precio por grupo de clientes eliminado correctamente.',
                'qty-required' => 'El campo qty es obligatorio.',
                'qty-invalid' => 'El campo qty debe ser un número entero mayor o igual que 1.',
                'value-type-required' => 'El campo value_type es obligatorio.',
                'value-type-invalid' => 'El campo value_type debe ser "fixed" o "discount".',
                'value-required' => 'El campo value es obligatorio.',
                'value-invalid' => 'El campo value debe ser un número no negativo.',
                'customer-group-not-found' => 'El grupo de clientes seleccionado no existe.',
                'duplicate-qty-group' => 'Ya existe un precio por grupo de clientes para esta combinación (qty, customer_group_id) en el producto.',
                'not-found' => 'Precio por grupo de clientes no encontrado.',
                'no-permission' => 'No tiene permiso para gestionar los precios por grupo de clientes de los productos.',
            ],
        ],

        'category' => [
            'not-found' => 'Categoría no encontrada.',

            'created' => 'Categoría creada correctamente.',
            'updated' => 'Categoría actualizada correctamente.',
            'deleted' => 'Categoría eliminada correctamente.',
            'slug-required' => 'El campo slug es obligatorio.',
            'slug-unique' => 'El slug ya está en uso.',
            'name-required' => 'El campo name es obligatorio.',
            'position-required' => 'El campo position es obligatorio.',
            'attributes-required' => 'Se requiere al menos un atributo filtrable.',
            'description-required-for-mode' => 'Se requiere una descripción para el modo de visualización elegido.',
            'cannot-delete-root' => 'Las categorías raíz y raíz de canal no se pueden eliminar.',
            'cannot-delete-channel-root' => 'Esta categoría es la raíz de un canal y no se puede eliminar.',
            'delete-failed' => 'No se pudo eliminar la categoría.',
            'no-permission' => 'No tiene permiso para gestionar categorías.',
            'mass-delete-success' => 'Categorías eliminadas correctamente.',
            'mass-update-status-success' => 'Estado de las categorías actualizado correctamente.',
            'mass-delete-indices-required' => 'El campo indices es obligatorio y debe ser una matriz no vacía.',
            'mass-update-status-value-required' => 'El campo value es obligatorio y debe ser 0 o 1.',
        ],

        'attribute' => [
            'not-found' => 'Atributo no encontrado.',

            'create-success' => 'Atributo creado correctamente.',
            'update-success' => 'Atributo actualizado correctamente.',
            'delete-success' => 'Atributo eliminado correctamente.',
            'delete-failed' => 'No se pudo eliminar el atributo.',
            'mass-delete-success' => 'Atributos eliminados correctamente.',
            'mass-delete-indices-required' => 'El campo indices es obligatorio y debe ser una matriz no vacía.',

            'code-immutable' => 'El código del atributo no se puede cambiar una vez creado el atributo.',
            'type-immutable' => 'El tipo de atributo no se puede cambiar mientras tenga valores de producto.',
            'locale-scope-immutable' => 'No se puede cambiar el ámbito de idioma mientras el atributo tenga valores de producto.',
            'system-attribute' => 'Los atributos del sistema no se pueden eliminar.',
            'in-use-family' => 'El atributo forma parte de una o más familias de atributos (IDs de grupo: :ids). Elimínelo primero de esas familias.',

            'option-not-found' => 'Opción de atributo no encontrada.',
            'option-not-supported' => 'El tipo de atributo ":type" no admite opciones. Solo los atributos select, multiselect y checkbox pueden tener opciones.',
            'option-in-use' => 'Esta opción la utilizan :count producto(s) y no se puede eliminar.',
            'option-delete-success' => 'Opción de atributo eliminada correctamente.',
        ],

        'family' => [
            'not-found' => 'Familia de atributos no encontrada.',

            'created' => 'Familia de atributos creada correctamente.',
            'updated' => 'Familia de atributos actualizada correctamente.',
            'deleted' => 'Familia de atributos eliminada correctamente.',
            'code-required' => 'El código de la familia es obligatorio.',
            'code-unique' => 'El código de la familia debe ser único.',
            'name-required' => 'El nombre de la familia es obligatorio.',
            'group-code-required' => 'Cada grupo de atributos debe tener un código.',
            'group-name-required' => 'Cada grupo de atributos debe tener un nombre.',
            'group-column-invalid' => 'La columna de cada grupo de atributos debe ser 1 o 2.',
            'group-name-duplicate' => 'El nombre de un grupo de atributos debe ser único dentro de la familia de atributos.',
            'update-failed' => 'No se pudo actualizar la familia de atributos.',
            'create-failed' => 'No se pudo crear la familia de atributos.',
            'default-delete-error' => 'La familia de atributos predeterminada no se puede eliminar.',
            'last-delete-error' => 'Se requiere al menos una familia de atributos.',
            'attribute-product-error' => 'Esta familia de atributos está en uso por uno o más productos. Reasigne esos productos antes de eliminarla.',
            'delete-failed' => 'No se pudo eliminar la familia de atributos.',
            'no-permission' => 'No tiene permiso para gestionar las familias de atributos.',
        ],

        'settings' => [
            'locale' => [
                'not-found' => 'Idioma no encontrado.',
                'created' => 'Idioma creado correctamente.',
                'updated' => 'Idioma actualizado correctamente.',
                'deleted' => 'Idioma eliminado correctamente.',
                'cannot-delete-last' => 'Se requiere al menos un idioma: no se puede eliminar el último idioma restante.',
                'cannot-delete-channel-default' => 'Este idioma es el idioma predeterminado de uno o más canales y no se puede eliminar.',
                'delete-failed' => 'No se pudo eliminar el idioma.',
                'no-permission' => 'No tiene permiso para gestionar idiomas.',
                'mass-delete-success' => 'Idiomas eliminados correctamente.',
                'mass-delete-indices-required' => 'El campo indices es obligatorio y debe ser una matriz no vacía.',
            ],

            'theme' => [
                'not-found' => 'Personalización del tema no encontrada.',
                'created' => 'Personalización del tema creada correctamente.',
                'updated' => 'Personalización del tema actualizada correctamente.',
                'deleted' => 'Personalización del tema eliminada correctamente.',
                'delete-failed' => 'No se pudo eliminar la personalización del tema.',
                'no-permission' => 'No tiene permiso para gestionar las personalizaciones del tema.',
                'mass-delete-success' => 'Personalizaciones del tema eliminadas correctamente.',
                'mass-delete-indices-required' => 'El campo indices es obligatorio y debe ser una matriz no vacía.',
                'mass-update-success' => 'Personalizaciones del tema actualizadas correctamente.',
                'mass-update-indices-required' => 'El campo indices es obligatorio y debe ser una matriz no vacía.',
                'mass-update-value-invalid' => 'El campo value debe ser 0 o 1.',
            ],

            'inventory-source' => [
                'not-found' => 'Origen de inventario no encontrado.',
                'created' => 'Origen de inventario creado correctamente.',
                'updated' => 'Origen de inventario actualizado correctamente.',
                'deleted' => 'Origen de inventario eliminado correctamente.',
                'code-unique' => 'Ya existe un origen de inventario con este código.',
                'last-delete-error' => 'Se requiere al menos un origen de inventario: no se puede eliminar el último origen restante.',
                'in-use' => 'Este origen de inventario está referenciado por inventarios de productos y no se puede eliminar.',
                'delete-failed' => 'No se pudo eliminar el origen de inventario.',
                'no-permission' => 'No tiene permiso para gestionar los orígenes de inventario.',
                'mass-delete-success' => 'Orígenes de inventario eliminados correctamente.',
                'mass-delete-indices-required' => 'El campo indices es obligatorio y debe ser una matriz no vacía.',
            ],

            'exchange-rate' => [
                'not-found' => 'Tipo de cambio no encontrado.',
                'created' => 'Tipo de cambio creado correctamente.',
                'updated' => 'Tipo de cambio actualizado correctamente.',
                'deleted' => 'Tipo de cambio eliminado correctamente.',
                'duplicate-pair' => 'Ya existe un tipo de cambio para esta moneda de destino. Actualice el registro existente o elimínelo primero.',
                'delete-failed' => 'No se pudo eliminar el tipo de cambio.',
                'no-permission' => 'No tiene permiso para gestionar los tipos de cambio.',
                'mass-delete-success' => 'Tipos de cambio eliminados correctamente.',
                'mass-delete-indices-required' => 'El campo indices es obligatorio y debe ser una matriz no vacía.',
                'update-rates-success' => 'Tipos de cambio actualizados correctamente.',
                'update-rates-failed' => 'No se pudieron actualizar los tipos de cambio desde el proveedor externo.',
                'provider-not-configured' => 'No hay ningún proveedor externo de tipos de cambio configurado.',
            ],

            'tax-rate' => [
                'not-found' => 'Tasa de impuesto no encontrada.',
                'created' => 'Tasa de impuesto creada correctamente.',
                'updated' => 'Tasa de impuesto actualizada correctamente.',
                'deleted' => 'Tasa de impuesto eliminada correctamente.',
                'delete-failed' => 'No se pudo eliminar la tasa de impuesto.',
                'no-permission' => 'No tiene permiso para gestionar las tasas de impuestos.',
                'zip-code-required' => 'El campo zip_code es obligatorio cuando is_zip es false.',
                'zip-range-required' => 'Tanto zip_from como zip_to son obligatorios cuando is_zip es true.',
            ],

            'tax-category' => [
                'not-found' => 'Categoría de impuestos no encontrada.',
                'created' => 'Categoría de impuestos creada correctamente.',
                'updated' => 'Categoría de impuestos actualizada correctamente.',
                'deleted' => 'Categoría de impuestos eliminada correctamente.',
                'code-unique' => 'El código ya está en uso.',
                'cannot-delete-in-use' => 'Esta categoría de impuestos aún tiene tasas de impuestos asociadas y no se puede eliminar.',
                'delete-failed' => 'No se pudo eliminar la categoría de impuestos.',
                'no-permission' => 'No tiene permiso para gestionar las categorías de impuestos.',
            ],

            'role' => [
                'not-found' => 'Rol no encontrado.',
                'created' => 'Rol creado correctamente.',
                'updated' => 'Rol actualizado correctamente.',
                'deleted' => 'Rol eliminado correctamente.',
                'permissions-required' => 'El campo permissions es obligatorio cuando permission_type es custom.',
                'cannot-delete-in-use' => 'Este rol está asignado a uno o más administradores y no se puede eliminar.',
                'cannot-delete-last-role' => 'Se requiere al menos un rol: no se puede eliminar el último rol restante.',
                'cannot-change-last-all-access' => 'El único rol con acceso total no se puede cambiar a un rol personalizado.',
                'delete-failed' => 'No se pudo eliminar el rol.',
                'no-permission' => 'No tiene permiso para gestionar roles.',
            ],

            'currency' => [
                'not-found' => 'Moneda no encontrada.',
                'created' => 'Moneda creada correctamente.',
                'updated' => 'Moneda actualizada correctamente.',
                'deleted' => 'Moneda eliminada correctamente.',
                'code-required' => 'El campo code es obligatorio.',
                'code-size' => 'El código debe tener exactamente 3 caracteres alfabéticos.',
                'code-unique' => 'El código ya está en uso.',
                'name-required' => 'El campo name es obligatorio.',
                'cannot-delete-last' => 'Se requiere al menos una moneda: no se puede eliminar la última moneda restante.',
                'cannot-delete-channel-base' => 'Esta moneda es la moneda base de uno o más canales y no se puede eliminar.',
                'delete-failed' => 'No se pudo eliminar la moneda.',
                'no-permission' => 'No tiene permiso para gestionar monedas.',

                'mass-delete-success' => 'Monedas eliminadas correctamente.',
                'mass-delete-indices-required' => 'El campo indices es obligatorio y debe ser una matriz no vacía.',
            ],

            'channel' => [
                'not-found' => 'Canal no encontrado.',

                'created' => 'Canal creado correctamente.',
                'updated' => 'Canal actualizado correctamente.',
                'deleted' => 'Canal eliminado correctamente.',
                'code-unique' => 'El código ya está en uso.',
                'hostname-unique' => 'El nombre de host ya está en uso.',
                'default-locale-mismatch' => 'El default_locale_id debe figurar en la lista de idiomas.',
                'base-currency-mismatch' => 'El base_currency_id debe figurar en la lista de monedas.',
                'unknown-locale' => 'Uno o más ids de idioma no existen.',
                'unknown-currency' => 'Uno o más ids de moneda no existen.',
                'unknown-inventory-source' => 'Uno o más ids de inventory_source no existen.',
                'unknown-root-category' => 'El root_category_id no existe.',
                'cannot-delete-last' => 'Se requiere al menos un canal: no se puede eliminar el último canal restante.',
                'cannot-delete-default' => 'No se puede eliminar el canal predeterminado de la aplicación.',
                'delete-failed' => 'No se pudo eliminar el canal.',
                'no-permission' => 'No tiene permiso para gestionar canales.',
            ],

            'user' => [
                'not-found' => 'Usuario administrador no encontrado.',
                'created' => 'Usuario administrador creado correctamente.',
                'updated' => 'Usuario administrador actualizado correctamente.',
                'deleted' => 'Usuario administrador eliminado correctamente.',
                'name-required' => 'El campo name es obligatorio.',
                'email-required' => 'El campo email es obligatorio.',
                'email-invalid' => 'El correo electrónico debe ser una dirección de correo electrónico válida.',
                'email-unique' => 'El correo electrónico ya está en uso.',
                'password-required' => 'El campo password es obligatorio.',
                'password-min' => 'La contraseña debe tener al menos 6 caracteres.',
                'role-required' => 'El campo role_id es obligatorio.',
                'role-not-found' => 'El rol seleccionado no existe.',
                'status-invalid' => 'El estado debe ser 0 o 1.',
                'cannot-delete-self' => 'No puede eliminar su propia cuenta de administrador.',
                'cannot-delete-last-admin' => 'Se requiere al menos un usuario administrador: no se puede eliminar el último administrador restante.',
                'cannot-change' => 'No se puede cambiar el :name del último administrador con acceso total.',
                'delete-failed' => 'No se pudo eliminar el usuario administrador.',
                'no-permission' => 'No tiene permiso para gestionar usuarios administradores.',
                'self-delete-password-required' => 'El campo password es obligatorio para eliminar su propia cuenta.',
                'self-delete-incorrect-password' => 'La contraseña es incorrecta.',
                'self-deleted' => 'Su cuenta de administrador ha sido eliminada.',
            ],

            'data-transfer' => [
                'import' => [
                    'not-found' => 'Importación no encontrada.',
                    'deleted' => 'Importación eliminada correctamente.',
                    'cancelled' => 'Importación cancelada correctamente.',
                    'cannot-cancel' => 'Solo se pueden cancelar las importaciones pendientes o en proceso. Estado actual: :state.',
                    'no-permission' => 'No tiene permiso para gestionar las importaciones de transferencia de datos.',
                    'delete-failed' => 'No se pudo eliminar la importación.',
                    'created' => 'Importación creada correctamente.',
                    'updated' => 'Importación actualizada correctamente.',
                    'type-invalid' => 'El tipo seleccionado no es válido.',
                    'action-invalid' => 'La acción debe ser una de las siguientes: append, delete.',
                    'validation-strategy-invalid' => 'La estrategia de validación debe ser una de las siguientes: stop-on-errors, skip-errors.',
                    'allowed-errors-invalid' => 'Los errores permitidos deben ser un número entero igual o mayor que 0.',
                    'field-separator-required' => 'El separador de campos es obligatorio.',
                    'file-required' => 'Se requiere un archivo.',
                    'file-invalid-type' => 'El archivo debe ser de uno de los siguientes tipos: csv, xls, xlsx, xml.',
                    'nothing-to-import' => 'No hay nada que importar.',
                    'not-valid' => 'La importación no es válida.',
                    'setup-queue-error' => 'La conexión de la cola está configurada como sync; configure una cola real para procesar las importaciones en segundo plano.',
                    'file-not-found' => 'No hay ningún archivo disponible para descargar.',
                    'error-report-not-found' => 'No hay ningún informe de errores disponible para descargar.',
                    'sample-not-found' => 'No hay ningún archivo de ejemplo disponible para el tipo y formato solicitados.',
                    'sample-format-invalid' => 'El formato de ejemplo debe ser uno de los siguientes: csv, xls, xlsx, xml.',
                    'images-archive-too-large' => 'El archivo comprimido de imágenes puede contener como máximo :entries archivos y descomprimirse en un máximo de :size MB.',
                ],
            ],
        ],

        'cms' => [
            'page' => [
                'not-found' => 'Página CMS no encontrada.',

                'created' => 'Página CMS creada correctamente.',
                'url-key-required' => 'El campo url_key es obligatorio.',
                'url-key-unique' => 'El url_key ya está en uso.',
                'url-key-invalid' => 'El formato de url_key no es válido. Use solo letras minúsculas, números y guiones.',
                'page-title-required' => 'El campo page_title es obligatorio.',
                'html-content-required' => 'El campo html_content es obligatorio.',
                'channels-required' => 'Se debe seleccionar al menos un canal.',
                'channels-invalid' => 'Uno o más canales no son válidos.',

                'updated' => 'Página CMS actualizada correctamente.',

                'deleted' => 'Página CMS eliminada correctamente.',
                'delete-failed' => 'No se pudo eliminar la página CMS.',

                'mass-deleted' => 'Páginas CMS eliminadas correctamente.',
                'indices-required' => 'El campo indices es obligatorio y debe ser una matriz no vacía.',
                'indices-invalid' => 'El campo indices debe ser una matriz de IDs de páginas CMS.',

                'no-permission' => 'No tiene permiso para gestionar las páginas CMS.',
            ],
        ],

        'marketing' => [
            'catalog-rule' => [
                'not-found' => 'Regla de catálogo no encontrada.',
                'created' => 'Regla de catálogo creada correctamente.',
                'updated' => 'Regla de catálogo actualizada correctamente.',
                'deleted' => 'Regla de catálogo eliminada correctamente.',
                'delete-failed' => 'No se pudo eliminar la regla de catálogo.',
                'mass-delete-indices-required' => 'No se proporcionaron IDs de reglas de catálogo para la eliminación masiva.',
                'mass-delete-success' => 'Reglas de catálogo seleccionadas eliminadas correctamente.',
                'no-permission' => 'No tiene permiso para gestionar las reglas de catálogo.',
            ],

            'campaign' => [
                'not-found' => 'Campaña no encontrada.',
                'created' => 'Campaña creada correctamente.',
                'updated' => 'Campaña actualizada correctamente.',
                'deleted' => 'Campaña eliminada correctamente.',
                'delete-failed' => 'No se pudo eliminar la campaña.',
                'no-permission' => 'No tiene permiso para gestionar campañas.',

                'send' => [
                    'id-required' => 'El id de la campaña es obligatorio.',
                    'inactive' => 'No se puede enviar una campaña inactiva. Actívela primero.',
                    'queued' => 'Campaña puesta en cola para :count destinatario(s).',
                ],
            ],

            'sitemap' => [
                'not-found' => 'Mapa del sitio no encontrado.',
                'created' => 'Mapa del sitio creado correctamente.',
                'updated' => 'Mapa del sitio actualizado correctamente.',
                'deleted' => 'Mapa del sitio eliminado correctamente.',
                'delete-failed' => 'No se pudo eliminar el mapa del sitio.',
                'no-permission' => 'No tiene permiso para gestionar los mapas del sitio.',

                'generate' => [
                    'id-required' => 'El id del mapa del sitio es obligatorio.',
                    'no-channels' => 'Este mapa del sitio no abarca ningún canal, por lo que no hay nada que generar. Asígnele primero al menos un canal.',
                    'success' => 'Mapa del sitio regenerado correctamente.',
                    'failed' => 'Error al generar el mapa del sitio: :message',
                ],
            ],

            'cart-rule' => [
                'not-found' => 'Regla de carrito no encontrada.',
                'id-required' => 'Se requiere un id de regla de carrito.',
                'copy-of' => 'Copia de :value',
                'copied' => 'Regla de carrito copiada correctamente.',
                'created' => 'Regla de carrito creada correctamente.',
                'updated' => 'Regla de carrito actualizada correctamente.',
                'deleted' => 'Regla de carrito eliminada correctamente.',
                'delete-failed' => 'No se pudo eliminar la regla de carrito.',
                'no-permission' => 'No tiene permiso para gestionar las reglas de carrito.',
                'mass-delete-success' => 'Reglas de carrito eliminadas correctamente.',
                'mass-delete-indices-required' => 'El campo indices es obligatorio y debe ser una matriz no vacía.',
                'action-type-invalid' => 'El action_type debe ser uno de los siguientes: by_percent, by_fixed, cart_fixed, buy_x_get_y.',
                'coupon-code-required' => 'Se requiere un coupon_code cuando coupon_type=1 y use_auto_generation=0.',
                'coupon-code-unique' => 'Este código de cupón ya está en uso.',
            ],

            'template' => [
                'campaign-associated' => 'Esta plantilla de correo electrónico la utiliza una campaña, por lo que no se puede eliminar. Quítela primero de la campaña.',
                'not-found' => 'Plantilla de correo electrónico no encontrada.',
                'created' => 'Plantilla de correo electrónico creada correctamente.',
                'updated' => 'Plantilla de correo electrónico actualizada correctamente.',
                'deleted' => 'Plantilla de correo electrónico eliminada correctamente.',
                'delete-failed' => 'No se pudo eliminar la plantilla de correo electrónico.',
                'status-invalid' => 'El estado debe ser uno de los siguientes: active, inactive, draft.',
                'no-permission' => 'No tiene permiso para gestionar las plantillas de correo electrónico.',
            ],

            'event' => [
                'campaign-associated' => 'Este evento lo utiliza una campaña, por lo que no se puede eliminar. Quítelo primero de la campaña.',
                'not-found' => 'Evento de marketing no encontrado.',
                'created' => 'Evento de marketing creado correctamente.',
                'updated' => 'Evento de marketing actualizado correctamente.',
                'deleted' => 'Evento de marketing eliminado correctamente.',
                'delete-failed' => 'No se pudo eliminar el evento de marketing.',
                'no-permission' => 'No tiene permiso para gestionar los eventos de marketing.',
            ],

            'search-synonym' => [
                'not-found' => 'Sinónimo de búsqueda no encontrado.',
                'created' => 'Sinónimo de búsqueda creado correctamente.',
                'updated' => 'Sinónimo de búsqueda actualizado correctamente.',
                'deleted' => 'Sinónimo de búsqueda eliminado correctamente.',
                'mass-deleted' => 'Sinónimos de búsqueda eliminados correctamente.',
                'delete-failed' => 'No se pudo eliminar el sinónimo de búsqueda.',
                'indices-required' => 'El campo indices es obligatorio y debe ser una matriz no vacía de IDs de sinónimos de búsqueda.',
                'no-permission' => 'No tiene permiso para gestionar los sinónimos de búsqueda.',
            ],

            'url-rewrite' => [
                'not-found' => 'Reescritura de URL no encontrada.',
                'created' => 'Reescritura de URL creada correctamente.',
                'updated' => 'Reescritura de URL actualizada correctamente.',
                'deleted' => 'Reescritura de URL eliminada correctamente.',
                'delete-failed' => 'No se pudo eliminar la reescritura de URL.',
                'mass-deleted' => 'Reescrituras de URL eliminadas correctamente.',
                'indices-required' => 'Se requiere al menos un id de reescritura de URL.',
                'no-permission' => 'No tiene permiso para gestionar las reescrituras de URL.',
            ],

            'cart-rule-coupon' => [
                'cart-rule-not-found' => 'Regla de carrito no encontrada.',
                'not-found' => 'Cupón no encontrado.',
                'code-taken' => 'Ya existe un cupón con este código.',
                'format-invalid' => 'El formato debe ser uno de los siguientes: alphabetic, alphanumeric, numeric.',
                'indices-required' => 'El campo indices es obligatorio y debe ser una matriz no vacía de IDs de cupones.',
                'created' => 'Cupón creado correctamente.',
                'generated' => 'Se generaron :count cupón(es) correctamente.',
                'deleted' => 'Cupón eliminado correctamente.',
                'mass-deleted' => 'Se eliminaron :count cupón(es) correctamente.',
                'no-permission' => 'No tiene permiso para gestionar los cupones de las reglas de carrito.',
            ],

            'subscriber' => [
                'not-found' => 'Suscriptor del boletín no encontrado.',
                'updated' => 'Suscripción actualizada correctamente.',
                'deleted' => 'Suscripción eliminada correctamente.',
                'is-subscribed-required' => 'El campo is_subscribed es obligatorio.',
                'no-permission' => 'No tiene permiso para gestionar los suscriptores del boletín.',
            ],

            'search-term' => [
                'not-found' => 'Término de búsqueda no encontrado.',
                'updated' => 'Término de búsqueda actualizado correctamente.',
                'deleted' => 'Término de búsqueda eliminado correctamente.',
                'mass-deleted' => 'Términos de búsqueda eliminados correctamente.',
                'term-required' => 'El campo term es obligatorio.',
                'redirect-url-invalid' => 'El redirect_url debe ser una URL válida.',
                'indices-required' => 'Se debe proporcionar al menos un ID en indices.',
                'no-permission' => 'No tiene permiso para gestionar los términos de búsqueda.',
            ],
        ],
        'customer-api-sessions' => [
            'menu' => [
                'title' => 'Sesiones de API',
            ],

            'acl' => [
                'title' => 'Sesiones de API',
                'delete' => 'Revocar',
            ],

            'index' => [
                'title' => 'Sesiones de API de clientes',
                'info' => 'Clientes conectados a la API de la tienda desde aplicaciones web, móviles, de TPV o de escritorio. Revocar una sesión desconecta ese dispositivo al instante.',
            ],

            'fields' => [
                'customer' => 'Cliente',
                'email' => 'Correo electrónico',
                'channel' => 'Canal',
            ],

            'datagrid' => [
                'revoke-all' => 'Revocar todas las sesiones del cliente',
            ],

            'card' => [
                'title' => 'Sesiones de API (:count)',
                'empty' => 'Este cliente no tiene sesiones de API activas.',
            ],

            'errors' => [
                'no-permission' => 'No tiene permiso para gestionar las sesiones de API de los clientes.',
            ],
        ],

        'customer-auth' => [
            'configuration' => [
                'title' => 'Autenticación de clientes',
                'info' => 'Duración de los tokens, límites y reglas de sesión para los inicios de sesión de clientes de la tienda.',

                'tokens' => [
                    'title' => 'Duración de los tokens',
                    'info' => 'Cuánto tiempo son válidos los tokens de acceso, los tokens de actualización y las sesiones. Los cambios se aplican a los tokens emitidos después.',
                    'access-token-ttl' => 'Duración del token de acceso (minutos)',
                    'access-token-ttl-info' => 'Entre 5 y 60 minutos. Por defecto 15.',
                    'refresh-token-ttl' => 'Duración del token de actualización (días)',
                    'refresh-token-ttl-info' => 'Entre 1 y 90 días. Por defecto 30.',
                    'session-lifetime' => 'Duración máxima de la sesión (días)',
                    'session-lifetime-info' => 'Entre 1 y 365 días. Por defecto 90. Después, el cliente debe iniciar sesión de nuevo, sin importar cuántas veces se haya actualizado la sesión.',
                    'max-sessions' => 'Máximo de sesiones activas por cliente',
                    'max-sessions-info' => 'Al alcanzar el límite, se cierra la sesión más antigua. Entre 0 y 100. 0 significa sin límite.',
                ],

                'rate-limits' => [
                    'title' => 'Límites de solicitudes',
                    'info' => 'Máximo de solicitudes a la API de la tienda por minuto para cada cliente conectado.',
                    'requests-per-minute' => 'Solicitudes por minuto por cliente conectado',
                    'requests-per-minute-info' => 'Entre 1 y 100000. Por defecto 120.',
                ],

                'login-protection' => [
                    'title' => 'Protección del inicio de sesión',
                    'info' => 'Cuántos inicios de sesión fallidos y renovaciones de token se permiten antes de que el cliente deba esperar.',
                    'login-max-attempts' => 'Inicios de sesión fallidos antes del bloqueo',
                    'login-max-attempts-info' => 'Por correo electrónico y dirección IP. Entre 3 y 20. Por defecto 5.',
                    'login-lockout-seconds' => 'Bloqueo del inicio de sesión (segundos)',
                    'login-lockout-seconds-info' => 'Entre 60 y 3600. Por defecto 60.',
                    'refresh-requests-per-minute' => 'Renovaciones de token por minuto por sesión',
                    'refresh-requests-per-minute-info' => 'Entre 1 y 60. Por defecto 10.',
                ],

                'security' => [
                    'title' => 'Seguridad',
                    'info' => 'Qué cierra las demás sesiones de un cliente.',
                    'revoke-sessions-on-password-change' => 'Revocar las sesiones al cambiar la contraseña',
                ],

                'retention' => [
                    'title' => 'Conservación de datos',
                    'info' => 'Cuánto tiempo se conservan las sesiones finalizadas de los clientes.',
                    'session-prune-days' => 'Conservar sesiones finalizadas (días)',
                    'session-prune-days-info' => 'Las sesiones revocadas o caducadas se eliminan después de estos días. Entre 1 y 365. Por defecto 30.',
                ],
            ],
        ],
        'auth' => [
            'configuration' => [
                'title' => 'Autenticación de administrador',
                'info' => 'Duración de los tokens, límites de solicitudes y reglas de seguridad para los inicios de sesión de la API de administración y los tokens de API.',

                'tokens' => [
                    'title' => 'Duración de los tokens',
                    'info' => 'Cuánto tiempo son válidos los tokens de acceso, los tokens de actualización y las sesiones. Los cambios se aplican a los tokens emitidos después.',
                    'access-token-ttl' => 'Duración del token de acceso (minutos)',
                    'access-token-ttl-info' => 'Entre 5 y 60 minutos. Por defecto 15.',
                    'refresh-token-ttl' => 'Duración del token de actualización (días)',
                    'refresh-token-ttl-info' => 'Entre 1 y 90 días. Por defecto 30.',
                    'session-lifetime' => 'Duración máxima de la sesión (días)',
                    'session-lifetime-info' => 'Entre 1 y 365 días. Por defecto 90. Después, el administrador debe iniciar sesión de nuevo, sin importar cuántas veces se haya actualizado la sesión.',
                ],

                'rate-limits' => [
                    'title' => 'Límites de solicitudes',
                    'info' => 'Máximo de solicitudes a la API de administración por minuto.',
                    'session-requests-per-minute' => 'Solicitudes por minuto por administrador conectado',
                    'api-token-requests-per-minute' => 'Solicitudes por minuto por token de API',
                    'session-requests-per-minute-info' => 'Entre 1 y 100000. Por defecto 60.',
                    'api-token-requests-per-minute-info' => 'Entre 1 y 100000. Por defecto 60.',
                    'unauthenticated-requests-per-minute' => 'Solicitudes por minuto sin token, por dirección IP',
                    'unauthenticated-requests-per-minute-info' => 'Entre 10 y 1000. Por defecto 60. Incluye las solicitudes de inicio de sesión, doble factor, renovación y contraseña.',
                ],

                'security' => [
                    'title' => 'Seguridad',
                    'info' => 'Reglas de revocación, caducidad de los tokens de API y vinculación de dominio.',
                    'revoke-sessions-on-password-change' => 'Revocar las sesiones al cambiar la contraseña',
                    'revoke-api-tokens-on-password-change' => 'Revocar los tokens de API al cambiar la contraseña',
                    'allow-non-expiring-api-tokens' => 'Permitir tokens de API sin caducidad',
                    'domain-binding-mode' => 'Modo de vinculación de dominio',
                    'domain-binding-mode-info' => 'DNS comprueba que la solicitud procede de una dirección IP a la que resuelve el dominio del token. Origin comprueba la cabecera Origin o Referer y solo sirve para navegadores.',
                    'domain-binding-dns' => 'DNS (dirección IP del cliente)',
                    'domain-binding-origin' => 'Cabecera Origin',
                ],

                'login-protection' => [
                    'title' => 'Protección del inicio de sesión',
                    'info' => 'Cuántos inicios de sesión fallidos, códigos de doble factor erróneos y solicitudes de contraseña se permiten antes de que el cliente deba esperar.',
                    'login-max-attempts' => 'Inicios de sesión fallidos antes del bloqueo',
                    'login-max-attempts-info' => 'Por correo electrónico y dirección IP. Entre 3 y 20. Por defecto 5.',
                    'login-lockout-seconds' => 'Bloqueo del inicio de sesión (segundos)',
                    'login-lockout-seconds-info' => 'Entre 60 y 3600. Por defecto 60.',
                    'two-factor-challenge-lifetime' => 'Duración del desafío de doble factor (minutos)',
                    'two-factor-challenge-lifetime-info' => 'Entre 1 y 15. Por defecto 5.',
                    'two-factor-max-failures' => 'Códigos de doble factor erróneos por minuto',
                    'two-factor-max-failures-info' => 'Por administrador, en todos los desafíos. Entre 3 y 20. Por defecto 5.',
                    'password-requests-per-minute' => 'Solicitudes de contraseña por minuto',
                    'password-requests-per-minute-info' => 'Solicitudes de olvido y restablecimiento de contraseña por dirección IP. Entre 1 y 20. Por defecto 5.',
                ],

                'retention' => [
                    'title' => 'Conservación de datos',
                    'info' => 'Cuánto tiempo se conservan las sesiones finalizadas y el historial de auditoría de la API.',
                    'session-prune-days' => 'Conservar sesiones finalizadas (días)',
                    'session-prune-days-info' => 'Las sesiones revocadas o caducadas se eliminan después de estos días. Entre 1 y 365. Por defecto 30.',
                    'audit-retention-days' => 'Conservar el historial de auditoría de la API (días)',
                    'audit-retention-days-info' => 'Las entradas más antiguas se eliminan a diario. Entre 0 y 3650. Vacío o 0 las conserva para siempre.',
                ],
            ],

            'errors' => [
                'unauthenticated' => 'No está autenticado.',
                'access-token-expired' => 'El token de acceso ha caducado. Actualízalo para continuar.',
                'api-token-expired' => 'El token de API ha caducado.',
                'refresh-token-invalid' => 'El token de actualización no es válido o ha caducado. Vuelve a iniciar sesión.',
                'api-token-revoked' => 'El token de API ha sido revocado.',
                'api-token-domain-mismatch' => 'El token de API no se puede usar desde este dominio.',
                'access-token-required' => 'Esta acción necesita el token de acceso de una sesión iniciada; los tokens de API no pueden usarla.',
                'two-factor-required' => 'Introduce el código de 6 dígitos de tu aplicación de autenticación, o uno de tus códigos de respaldo, para terminar de iniciar sesión.',
                'too-many-login-attempts' => 'Demasiados intentos de inicio de sesión. Vuelva a intentarlo en :seconds segundos.',
                'two-factor-challenge-invalid' => 'El desafío de dos factores no es válido o ha caducado. Vuelve a iniciar sesión.',
                'two-factor-attempts-exceeded' => 'Demasiados códigos incorrectos. Vuelve a iniciar sesión.',
                'password-reset-token-invalid' => 'Este token de restablecimiento de contraseña no es válido o ha caducado.',
                'too-many-password-requests' => 'Demasiadas solicitudes de contraseña. Inténtalo de nuevo en :seconds segundos.',
                'rate-limit-exceeded' => 'Demasiados intentos.',
                'no-permission' => 'No tiene permiso para gestionar las sesiones de la API de administración.',
            ],

            'sessions' => [
                'logged-out' => 'Ha cerrado la sesión.',
                'logged-out-all' => 'Ha cerrado todas las sesiones.',
                'not-found' => 'Sesión no encontrada.',
                'revoked' => 'La sesión ha sido revocada.',
                'revoked-for-admin' => 'Sesiones revocadas: :count.',
            ],

            'password' => [
                'reset' => 'Tu contraseña se ha restablecido. Vuelve a iniciar sesión.',
            ],
        ],

        'api-tokens' => [
            'menu' => [
                'title' => 'Tokens de API',
            ],

            'acl' => [
                'title' => 'Tokens de API',
                'create' => 'Crear',
                'delete' => 'Revocar',
                'manage-others' => 'Gestionar los tokens de otros administradores',
            ],

            'index' => [
                'title' => 'Tokens de API',
                'info' => 'Las integraciones de servidor, como un ERP, PIM, almacén o sistema contable, se autentican con tokens de API. Cada token actúa como un administrador con los permisos de su rol y solo funciona desde el dominio al que está vinculado.',
                'create-btn' => 'Crear token de API',
            ],

            'create' => [
                'title' => 'Crear token de API',
                'back-btn' => 'Volver',
                'save-btn' => 'Crear token',
                'general' => 'General',
                'expiry' => 'Caducidad',
                'admin-info' => 'El token actúa como este administrador y tiene exactamente los permisos de su rol.',
                'domain-info' => 'El host desde el que llama la integración, como erp.example.com. Una URL se reduce a su host. No se aceptan direcciones IP ni comodines, y el dominio no se puede cambiar después.',
                'expires-on-date' => 'Caduca en una fecha',
                'never-expires' => 'Nunca caduca',
                'no-expiry-warning' => 'Un token sin caducidad funciona hasta que se revoca. Guárdelo en un almacén de secretos y revóquelo en cuanto deje de necesitarse.',
                'no-expiry-confirm' => 'Entiendo que este token nunca caduca.',
                'no-admins' => 'No hay ningún administrador para el que pueda crear un token de API.',
            ],

            'view' => [
                'title' => 'Token de API',
                'back-btn' => 'Volver',
                'save-btn' => 'Guardar',
                'revoke-btn' => 'Revocar',
                'details' => 'Detalles',
                'usage' => 'Uso',
                'token-once-title' => 'Copie ahora su token de API',
                'token-once-info' => 'Esta es la única vez que se muestra el token. Guárdelo en un almacén de secretos; si se pierde, revóquelo y cree uno nuevo.',
                'copy-btn' => 'Copiar',
                'copied' => 'El token se ha copiado.',
                'usage-info' => 'Envíelo como token Bearer en la cabecera Authorization de las solicitudes REST y GraphQL.',
                'not-yet' => 'Todavía no',
                'revoke-confirm' => '¿Revocar este token de API? La integración que lo usa deja de funcionar de inmediato.',
            ],

            'fields' => [
                'id' => 'ID',
                'name' => 'Nombre',
                'admin' => 'Administrador',
                'token' => 'Token',
                'domain' => 'Dominio',
                'expiry' => 'Caducidad',
                'expires-at' => 'Caduca el',
                'no-expiry-confirmation' => 'Confirmación sin caducidad',
                'status' => 'Estado',
                'created-by' => 'Creado por',
                'created-at' => 'Creado el',
                'last-used-at' => 'Último uso el',
                'last-ip-address' => 'Última dirección IP',
                'last-user-agent' => 'Último agente de usuario',
                'last-channel' => 'Último canal',
                'resolved-ips' => 'Direcciones IP resueltas',
                'resolved-at' => 'Resuelto el',
                'domain-mismatches' => 'Solicitudes desde otros dominios',
                'last-domain-mismatch-at' => 'Última solicitud desde otro dominio',
                'revoked-at' => 'Revocado el',
                'revoked-by' => 'Revocado por',
            ],

            'datagrid' => [
                'never' => 'Nunca',
                'console' => 'Comando de consola',
                'view' => 'Ver',
                'revoke' => 'Revocar',
            ],

            'status' => [
                'active' => 'Activo',
                'expired' => 'Caducado',
                'revoked' => 'Revocado',
            ],

            'channels' => [
                'rest' => 'REST',
                'graphql' => 'GraphQL',
            ],

            'messages' => [
                'created' => 'El token de API se ha creado.',
                'updated' => 'El token de API se ha actualizado.',
                'revoked' => 'El token de API se ha revocado.',
            ],

            'errors' => [
                'no-permission' => 'No tiene permiso para gestionar tokens de API.',
                'not-found' => 'Token de API no encontrado.',
                'cannot-create-for-admin' => 'No puede crear un token de API para este administrador: su rol tiene permisos que el suyo no tiene, o no puede gestionar los tokens de otros administradores.',
                'cannot-manage' => 'No puede gestionar este token de API.',
                'already-revoked' => 'El token de API ya está revocado.',
                'invalid-name' => 'El nombre es obligatorio y puede tener como máximo 100 caracteres.',
                'invalid-domain' => 'Introduzca un nombre de dominio como erp.example.com. No se aceptan direcciones IP ni comodines.',
                'expiry-required' => 'Los tokens de API deben tener una fecha de caducidad.',
                'expiry-in-past' => 'La fecha de caducidad debe ser futura.',
            ],
        ],

        'api-sessions' => [
            'menu' => [
                'title' => 'Sesiones de API',
            ],

            'acl' => [
                'title' => 'Sesiones de API',
                'delete' => 'Revocar',
            ],

            'index' => [
                'title' => 'Sesiones de API',
                'info' => 'Administradores que han iniciado sesión en la API de administración desde aplicaciones web, móviles, de punto de venta o de escritorio. Revocar una sesión cierra la sesión de ese dispositivo de inmediato.',
            ],

            'fields' => [
                'id' => 'ID',
                'admin' => 'Administrador',
                'device' => 'Dispositivo',
                'client-type' => 'Tipo de cliente',
                'ip-address' => 'IP de inicio de sesión',
                'user-agent' => 'Agente de usuario de inicio de sesión',
                'last-ip-address' => 'Última dirección IP',
                'last-user-agent' => 'Último agente de usuario',
                'last-used-at' => 'Último uso el',
                'last-channel' => 'Último canal',
                'refresh-expires-at' => 'Actualización caduca el',
                'expires-at' => 'Caduca el',
                'status' => 'Estado',
                'created-at' => 'Sesión iniciada el',
            ],

            'datagrid' => [
                'revoke' => 'Revocar',
                'revoke-all' => 'Revocar todas las sesiones de este administrador',
            ],

            'client-types' => [
                'web' => 'Web',
                'mobile' => 'Móvil',
                'pos' => 'Punto de venta',
                'desktop' => 'Escritorio',
                'other' => 'Otro',
            ],

            'errors' => [
                'not-active' => 'Esta sesión ya está revocada o ha caducado.',
            ],
        ],

        'api-audit' => [
            'menu' => [
                'title' => 'Auditoría de API',
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
                'token-kind' => 'Tipo de token',
                'channel' => 'Canal',
                'operation' => 'Operación',
                'resource' => 'Recurso',
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

            'token-kinds' => [
                'session' => 'Sesión de inicio de sesión',
                'api-token' => 'Token de API',
                'integration' => 'Token de integración (eliminado)',
                'customer-session' => 'Sesión de inicio de sesión del cliente',
            ],

            'channels' => [
                'rest' => 'REST',
                'graphql' => 'GraphQL',
                'panel' => 'Panel de administración',
                'cli' => 'Comando de consola',
            ],

            'deleted' => ':count registros del historial eliminados.',
            'cleanup-input-required' => 'Proporcione una cantidad de días o una fecha para limpiar.',
        ],

        'api-configuration' => [
            'title' => 'API',
            'info' => 'Configuraciones para la API de Bagisto y sus módulos de administración.',
        ],
    ],
];
