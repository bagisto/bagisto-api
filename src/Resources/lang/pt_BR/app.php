<?php

return [

    'graphql' => [
        'headers' => [
            'unknown-channel' => 'O canal ":code" no cabeçalho X-Channel não existe.',
            'storefront-key-required' => 'O cabeçalho X-STOREFRONT-KEY é obrigatório',
            'storefront-key-invalid' => 'Chave de loja inválida',
            'storefront-key-rate-limited' => 'Limite de requisições excedido. Tente novamente após :seconds segundos',
        ],

        'cart' => [
            'authentication-required' => 'É necessário um token de autenticação',
            'invalid-token' => 'Token de autenticação inválido ou expirado',
            'unauthorized-access' => 'Acesso não autorizado ao carrinho',
            'authenticated-only' => 'Apenas usuários autenticados podem acessar seus carrinhos',
            'merge-requires-auth' => 'A mesclagem de convidado requer autenticação',
            'unknown-operation' => 'Operação de carrinho desconhecida',

            'cart-not-found' => 'Carrinho não encontrado',
            'guest-cart-not-found' => 'Carrinho de convidado não encontrado',
            'product-not-found' => 'Produto não encontrado',

            'product-id-quantity-required' => 'O ID do produto e a quantidade são obrigatórios',
            'cart-item-id-quantity-required' => 'O ID do item do carrinho e a quantidade são obrigatórios',

            'file-token-invalid' => 'A referência do arquivo enviado é inválida ou expirou. Envie o arquivo novamente.',
            'file-token-forbidden' => 'Este arquivo enviado não pertence ao seu carrinho.',
            'customizable-option-required' => 'A opção personalizável ":label" é obrigatória.',
            'customizable-file-option-invalid' => 'A opção selecionada não é uma opção de arquivo para este produto.',
            'customizable-file-invalid' => 'O arquivo está ausente, tem uma extensão não suportada ou é grande demais.',
            'cart-item-id-required' => 'O ID do item do carrinho é obrigatório',
            'item-ids-required' => 'A lista de IDs de itens é obrigatória',

            'event-booking-quantity-not-changeable' => 'A quantidade do produto de reserva de evento não pode ser alterada. A quantidade é determinada pela seleção de ingressos.',
            'appointment-booking-quantity-not-changeable' => 'A quantidade do produto de reserva de agendamento não pode ser alterada.',
            'coupon-code-required' => 'O código do cupom é obrigatório',
            'address-data-required' => 'País, estado e CEP são obrigatórios',
            'grouped-qty-required' => 'O produto agrupado requer as quantidades selecionadas. Envie groupedQty como string JSON, por exemplo {"101":2,"102":1}.',
            'grouped-qty-must-include-all' => 'O produto agrupado requer quantidades para todos os produtos associados. IDs ausentes: :ids.',
            'grouped-qty-invalid-associated' => 'As quantidades do produto agrupado contêm IDs de produtos associados inválidos: :ids.',
            'grouped-qty-invalid-quantity' => 'Quantidade inválida informada para o produto associado de ID :id. A quantidade deve ser um número inteiro não negativo.',
            'bundle-qty-not-changeable' => 'A quantidade da opção do pacote ":option" não pode ser alterada. A quantidade permitida é :qty.',

            'add-product-failed' => 'Falha ao adicionar o produto ao carrinho',
            'update-item-failed' => 'Falha ao atualizar o item do carrinho',
            'remove-item-failed' => 'Falha ao remover o item do carrinho',
            'remove-coupon-failed' => 'Falha ao remover o cupom',
            'coupon-applied' => 'Cupom aplicado com sucesso',
            'coupon-removed' => 'Cupom removido com sucesso',
            'move-to-wishlist-failed' => 'Falha ao mover o item para a lista de desejos',
            'estimate-shipping-failed' => 'Falha ao estimar o frete',

            'product-added-successfully' => 'Produto adicionado ao carrinho com sucesso',
            'cart-item-updated-successfully' => 'Item do carrinho atualizado com sucesso',
            'guest-cart-merged' => 'Carrinho de convidado mesclado com sucesso',
            'using-authenticated-cart' => 'Usando o carrinho do cliente autenticado',
            'cart-item-not-found' => 'Item do carrinho não encontrado',
            'new-guest-cart-created' => 'Novo carrinho de convidado criado com token de sessão exclusivo',
            'select-items-to-remove' => 'Selecione os itens para remover',
            'select-items-to-move-wishlist' => 'Selecione os itens para mover para a lista de desejos',
            'invalid-or-expired-token' => 'O token do carrinho é inválido ou expirou. Crie um novo carrinho.',
            'invalid-token-of-login-user' => 'O token do usuário conectado é inválido.',
            'booking-note-required' => 'booking.note é obrigatório para reserva de mesa.',
            'booking-date-required' => 'booking.date é obrigatório quando booking.slot é um intervalo de horário formatado.',
            'booking-qty-required' => 'booking.qty é obrigatório para reserva de evento.',
            'booking-ticket-qty-invalid' => 'A quantidade de ingressos do evento deve ser um número não negativo.',
            'booking-ticket-required' => 'Selecione a quantidade de pelo menos um ingresso para a reserva de evento.',
            'booking-slot-unavailable' => 'O horário selecionado não está disponível para a data informada.',
            'invalid-quantity' => 'A quantidade deve ser pelo menos 1',
            'product-id-required' => 'O ID do produto é obrigatório',
            'shipping-address-required' => 'País e CEP são obrigatórios para estimar o frete',
        ],

        'token-verification' => [
            'invalid-operation' => 'Operação inválida',
            'invalid-input-data' => 'Dados de entrada inválidos',
            'token-required' => 'O token é obrigatório',
            'invalid-token-format' => 'Formato de token inválido',
            'token-not-found-or-expired' => 'Token não encontrado ou expirado',
            'customer-not-found' => 'Cliente não encontrado',
            'customer-account-suspended' => 'A conta do cliente está suspensa',
            'error-verifying-token' => 'Erro ao verificar o token',
            'token-is-valid' => 'O token é válido',
            'unauthenticated' => 'Não autenticado. Faça login para realizar esta ação',
        ],

        'forgot-password' => [
            'invalid-operation' => 'Operação inválida',
            'invalid-input-data' => 'Dados de entrada inválidos',
            'email-required' => 'O e-mail é obrigatório',
            'reset-link-sent' => 'Link de redefinição enviado com sucesso para o seu e-mail',
            'email-not-found' => 'Endereço de e-mail não encontrado',
            'throttled' => 'Muitas tentativas de redefinição. Aguarde antes de tentar novamente.',
            'error-sending-reset-link' => 'Ocorreu um erro ao enviar o link de redefinição',
        ],

        'reset-password' => [
            'failed' => 'Não foi possível redefinir a senha. Tente novamente.',
        ],

        'rate-limit' => [
            'too-many-attempts' => 'Muitas tentativas. Tente novamente em :seconds segundos.',
        ],

        'logout' => [
            'invalid-operation' => 'Operação inválida',
            'invalid-input-data' => 'Dados de entrada inválidos',
            'token-required' => 'O token é obrigatório',
            'invalid-token-format' => 'Formato de token inválido',
            'logged-out-successfully' => 'Sessão encerrada com sucesso',
            'token-not-found-or-expired' => 'Token não encontrado ou já expirado',
            'error-during-logout' => 'Erro ao encerrar a sessão',
            'unauthenticated' => 'Não autenticado. Faça login para realizar esta ação',
        ],

        'address' => [
            'deleted-successfully' => 'Endereço excluído com sucesso',
            'authentication-required' => 'É necessário um token de autenticação',
            'invalid-token' => 'Token inválido ou expirado',
            'unknown-operation' => 'Operação desconhecida',
            'address-id-required' => 'O ID do endereço é obrigatório',
            'address-not-found' => 'Endereço não encontrado ou não pertence a este cliente',
            'retrieved' => 'Endereços recuperados com sucesso',
            'fetch-failed' => 'Falha ao buscar os endereços:',
            'field-required' => 'O campo :field é obrigatório',
        ],

        'customer-profile' => [
            'authentication-required' => 'É necessário um token de autenticação. Forneça o token na entrada da consulta',
            'invalid-token' => 'Token inválido ou expirado',
            'profile-updated' => 'Perfil do cliente atualizado com sucesso',
        ],

        'customer' => [
            'password-mismatch' => 'A senha e a confirmação de senha não coincidem',
            'confirm-password-required' => 'A confirmação de senha é obrigatória ao alterar a senha',
            'current-password-required' => 'A senha atual é obrigatória para alterar sua senha',
            'current-password-incorrect' => 'A senha atual informada está incorreta',
            'verify-email-to-login' => 'Sua conta foi criada. Verifique seu endereço de e-mail para fazer login.',
            'delete-password-required' => 'Sua senha é obrigatória para excluir sua conta',
            'delete-password-incorrect' => 'A senha informada está incorreta',
            'delete-has-active-orders' => 'Sua conta não pode ser excluída enquanto houver pedidos pendentes ou em processamento',
            'unauthenticated' => 'Não autenticado. Faça login para realizar esta ação',
            'id-required' => 'O ID do cliente é obrigatório',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/admin/customers/1" ou ID numérico',
            'not-found' => 'Cliente não encontrado',
            'phone-special-chars-not-allowed' => 'O número de celular pode conter apenas dígitos. Caracteres especiais não são permitidos',
            'email-already-taken' => 'Este endereço de e-mail já está cadastrado em outra conta',
            'invalid-gender' => 'Valor de gênero inválido ":gender". Os valores permitidos são: :valid',
        ],

        'product-review' => [
            'id-required' => 'O ID da avaliação do produto é obrigatório',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/reviews/1" ou ID numérico',
            'not-found' => 'Avaliação de produto com ID ":id" não encontrada',
            'item-not-found' => 'Item ":id" não encontrado.',
            'product-id-required' => 'O ID do produto é obrigatório',
            'product-not-found' => 'Produto não encontrado',
            'rating-invalid' => 'A avaliação deve estar entre 1 e 5',
            'title-required' => 'O título da avaliação é obrigatório',
            'comment-required' => 'O comentário da avaliação é obrigatório',
            'review-disabled' => 'As avaliações de produtos estão desativadas no momento',
            'guest-review-disabled' => 'Avaliações de visitantes não são permitidas. Faça login para enviar uma avaliação',
            'owner-required' => 'Faça login para gerenciar sua avaliação',
            'not-owned' => 'Esta avaliação não foi escrita por você e não pode ser modificada nem excluída',
            'invalid-attachments' => 'Formato de anexos inválido.',
            'invalid-product-id-format' => 'Formato de ID de produto inválido. Esperado formato IRI como "/api/shop/products/1" ou ID numérico',
        ],

        'customer-review' => [
            'id-required' => 'O ID da avaliação do cliente é obrigatório',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/customer-reviews/1" ou ID numérico',
            'not-found' => 'Avaliação do cliente com ID ":id" não encontrada',
        ],

        'newsletter' => [
            'error-during-subscription' => 'Não foi possível concluir a inscrição no momento. Tente novamente mais tarde',
        ],

        'contact-us' => [
            'success' => 'Sua mensagem foi enviada com sucesso. Entraremos em contato em breve',
            'failed' => 'Não foi possível enviar sua mensagem no momento. Tente novamente mais tarde',
            'invalid-input' => 'Dados do formulário de contato inválidos',
        ],

        'customer-order' => [
            'id-required' => 'O ID do pedido do cliente é obrigatório',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/customer-orders/1" ou ID numérico',
            'not-found' => 'Pedido do cliente com ID ":id" não encontrado',
        ],

        'return' => [
            'not-found' => 'Solicitação de devolução não encontrada.',
            'disabled' => 'As devoluções não estão habilitadas para esta loja.',
            'invalid-input' => 'Detalhes da devolução ausentes ou inválidos.',
            'invalid-resolution-type' => 'O tipo de resolução deve ser "return" ou "cancel_items".',
            'agreement-required' => 'Você deve aceitar a política de devolução.',
            'invalid-order' => 'O pedido selecionado não é válido.',
            'invalid-item' => 'O item selecionado não está elegível para devolução.',
            'qty-exceeds' => 'A quantidade solicitada excede a quantidade que pode ser devolvida.',
            'invalid-package-condition' => 'A condição da embalagem deve ser "open" ou "packed".',
            'invalid-image' => 'Um ou mais arquivos enviados têm um tipo de arquivo não suportado.',
            'invalid-custom-field' => 'Campo personalizado desconhecido ":id".',
            'custom-field-required' => 'O campo ":field" é obrigatório.',
            'invalid-custom-field-value' => 'O valor de ":field" não é uma das opções permitidas.',
            'created' => 'Solicitação de devolução criada com sucesso.',
            'canceled' => 'Solicitação de devolução cancelada com sucesso.',
            'already-canceled' => 'A solicitação de devolução já está cancelada.',
            'cancel-not-allowed' => 'Esta solicitação de devolução não pode ser cancelada.',
            'close-not-allowed' => 'Esta solicitação de devolução não pode ser encerrada.',
            'reopened' => 'Solicitação de devolução reaberta com sucesso.',
            'reopen-not-allowed' => 'Esta solicitação de devolução não pode ser reaberta.',
            'closed' => 'Solicitação de devolução encerrada com sucesso.',
            'message-sent' => 'Mensagem enviada com sucesso.',
            'attachment-not-found' => 'Anexo da mensagem não encontrado.',
        ],

        'eu-withdrawal' => [
            'not-found' => 'Declaração de desistência não encontrada.',
            'order-not-found' => 'O pedido não foi encontrado ou não está elegível para desistência.',
            'too-many-attempts' => 'Muitas tentativas. Tente novamente em :seconds segundos.',
            'created' => 'Declaração de desistência registrada com sucesso.',
        ],

        'customer-order-shipment' => [
            'id-required' => 'O ID da remessa é obrigatório',
            'not-found' => 'Remessa com ID ":id" não encontrada ou não pertence a este cliente',
            'item-not-found' => 'Item de remessa com ID ":id" não encontrado ou não pertence a este cliente',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/customer-order-shipments/1" ou ID numérico',
        ],

        'cancel-order' => [
            'order-id-required' => 'O ID do pedido é obrigatório para cancelar um pedido',
            'not-found' => 'Pedido com ID ":id" não encontrado ou não pertence a este cliente',
            'cancel-success' => 'O pedido foi cancelado com sucesso',
            'cancel-failed' => 'O pedido não pode ser cancelado. Ele pode já ter sido processado, enviado ou cancelado',
        ],

        'reorder' => [
            'order-id-required' => 'O ID do pedido é obrigatório para refazer o pedido',
            'not-found' => 'Pedido com ID ":id" não encontrado ou não pertence a este cliente',
            'reorder-success' => ':count item(ns) do seu pedido anterior foram adicionados ao seu carrinho',
            'no-items-added' => 'Nenhum item pôde ser adicionado ao carrinho. Os produtos podem estar fora de estoque ou indisponíveis',
        ],

        'customer-downloadable-product' => [
            'id-required' => 'O ID do produto para download do cliente é obrigatório',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/customer-downloadable-products/1" ou ID numérico',
            'not-found' => 'Produto para download do cliente com ID ":id" não encontrado',

            'download' => [
                'unauthenticated' => 'Não autorizado: é necessária a autenticação do cliente.',
                'not-found' => 'Produto para download não encontrado.',
                'pending' => 'O download está pendente. Aguarde até que o pedido seja faturado.',
                'limit-exceeded' => 'Limite de downloads excedido.',
                'no-downloads-remaining' => 'Não há mais downloads disponíveis.',
                'file-not-found' => 'Arquivo não encontrado.',
                'link-not-found' => 'Link para download não encontrado.',
            ],
        ],

        'customer-invoice' => [
            'id-required' => 'O ID da fatura do cliente é obrigatório',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/customer-invoices/1" ou ID numérico',
            'not-found' => 'Fatura do cliente com ID ":id" não encontrada',
        ],

        'auth' => [
            'no-token-provided' => 'Nenhum token de autenticação fornecido. Forneça o token no cabeçalho Authorization como "Bearer <token>" ou no campo input.token',
            'invalid-or-expired-token' => 'Token inválido ou expirado',
            'request-not-found' => 'Solicitação não encontrada no contexto',
            'token-required' => 'Authentication token is required. Please provide the token either in the GraphQL mutation input field or in the Authorization header as "Bearer <token>"',
            'unknown-resource' => 'Recurso desconhecido',
            'cannot-update-other-profile' => 'Não autorizado: não é possível atualizar o perfil de outro cliente',
            'refresh-token-invalid' => 'O token de atualização é inválido ou expirou. Entre novamente.',
            'too-many-login-attempts' => 'Muitas tentativas de login. Tente novamente em :seconds segundos.',
            'too-many-refresh-requests' => 'Muitas renovações de token. Tente novamente em :seconds segundos.',
            'rate-limit-exceeded' => 'Muitas tentativas.',
            'access-token-expired' => 'O token de acesso expirou. Atualize-o para continuar.',
            'logged-out-all' => 'Você saiu de todas as sessões.',
            'session-not-found' => 'Sessão não encontrada.',
            'session-revoked' => 'A sessão foi revogada.',
        ],

        'upload' => [
            'invalid-base64' => 'Dados de imagem codificados em base64 inválidos',
            'size-exceeds-limit' => 'O tamanho da imagem não deve exceder 5 MB',
            'invalid-format' => 'Formato de imagem inválido. Forneça a imagem codificada em base64 com esquema de URI de dados (data:image/jpeg;base64,...)',
            'failed' => 'Falha no envio da imagem',
        ],

        'attribute' => [
            'code-already-exists' => 'O código do atributo já existe',
            'option-id-required' => 'O parâmetro "attributeId" é obrigatório ao consultar opções de atributo diretamente',
            'id-required' => 'O ID do atributo é obrigatório',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/attributes/1" ou ID numérico',
            'not-found' => 'Atributo não encontrado',
        ],

        'attribute-option' => [
            'id-required' => 'O ID da opção de atributo é obrigatório',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/attribute-options/1" ou ID numérico',
            'not-found' => 'Opção de atributo não encontrada',
        ],

        'channel' => [
            'id-required' => 'O ID do canal é obrigatório',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/channels/1" ou ID numérico',
            'not-found' => 'Canal não encontrado',
        ],

        'country-state' => [
            'id-required' => 'O ID do estado é obrigatório',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/country-states/1" ou ID numérico',
            'not-found' => 'Estado não encontrado',
            'country-id-required' => 'O ID do país é obrigatório',
        ],

        'country' => [
            'id-required' => 'O ID do país é obrigatório',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/countries/1" ou ID numérico',
            'not-found' => 'País não encontrado',
        ],

        'login' => [
            'invalid-credentials' => 'E-mail ou senha inválidos',
            'account-suspended' => 'Sua conta foi suspensa',
            'account-inactive' => 'Sua ativação aguarda aprovação do administrador',
            'email-not-verified' => 'Verifique primeiro sua conta de e-mail.',
            'successful' => 'Você fez login com sucesso',
            'invalid-request' => 'Solicitação de login inválida',
        ],

        'social-login' => [
            'signed-in' => 'Login efetuado com sucesso.',
            'token-required' => 'É necessário um token de login social.',
            'invalid-token' => 'O token de login social é inválido ou expirou. Tente novamente.',
            'google-not-configured' => 'O login com Google não está configurado para esta loja.',
            'wrong-audience' => 'Este token foi emitido para outro aplicativo.',
            'email-required' => 'O provedor não compartilhou um endereço de e-mail. Cadastre-se com e-mail.',
            'account-inactive' => 'Sua ativação aguarda aprovação do administrador',
            'provider-not-supported' => 'Este provedor de login social não é compatível.',
            'provider-disabled' => 'Este provedor de login social não está habilitado.',
        ],

        'checkout' => [
            'invalid-input' => 'Dados de entrada inválidos para a operação de checkout',
            'billing-address-required' => 'O endereço de cobrança é obrigatório',
            'shipping-address-required' => 'O endereço de entrega é obrigatório para envios',
            'address-save-failed' => 'Falha ao salvar o endereço',
            'address-saved' => 'Endereço salvo com sucesso',
            'shipping-method-required' => 'O método de envio é obrigatório',
            'invalid-shipping-method' => 'Método de envio inválido ou indisponível',
            'shipping-method-save-failed' => 'Falha ao salvar o método de envio',
            'shipping-method-saved' => 'Método de envio salvo com sucesso',
            'shipping-method-error' => 'Erro ao salvar o método de envio',
            'payment-method-required' => 'O método de pagamento é obrigatório',
            'invalid-payment-method' => 'Método de pagamento inválido ou indisponível',
            'payment-method-save-failed' => 'Falha ao salvar o método de pagamento',
            'payment-method-saved' => 'Método de pagamento salvo com sucesso',
            'payment-method-error' => 'Erro ao salvar o método de pagamento',
            'order-placed' => 'Pedido realizado com sucesso',
            'order-redirect-required' => 'Este método de pagamento exige que o comprador conclua o pagamento no gateway. Envie-o para redirectUrl; o pedido é criado assim que o gateway confirmar o pagamento.',
            'order-creation-failed' => 'Falha na criação do pedido: o ID do pedido é nulo ou o pedido não foi persistido',
            'order-retrieval-failed' => 'Falha ao recuperar o pedido criado',
            'order-creation-error' => 'Falha ao criar o pedido',
            'cart-empty' => 'O carrinho está vazio',
            'account-suspended' => 'Sua conta foi suspensa. Entre em contato com o suporte.',
            'account-inactive' => 'Sua conta está inativa. Entre em contato com o suporte.',
            'minimum-order-not-met' => 'O valor mínimo do pedido é :amount',
            'guest-checkout-not-allowed' => 'Faça login para finalizar a compra. O checkout como visitante não está disponível para este carrinho.',
            'email-required' => 'O endereço de e-mail é obrigatório para a criação do pedido',
            'unknown-operation' => 'Operação de checkout desconhecida',
        ],

        'customer-addresses' => [
            'token-required' => 'É necessário um token para buscar os endereços do cliente',
            'invalid-or-expired-token' => 'Token inválido ou expirado',
            'token-validation-failed' => 'Falha na validação do token',
        ],

        'product' => [
            'not-found' => 'Produto não encontrado',
            'not-found-with-sku' => 'Nenhum produto encontrado com o SKU',
            'not-found-with-url-key' => 'Nenhum produto encontrado com a chave de URL',
            'parameters-required' => 'Pelo menos um dos seguintes parâmetros deve ser informado: "sku", "id", "urlKey"',
            'type' => 'Tipo de produto',
            'attribute-family' => 'Família de atributos',
            'sku' => 'SKU',
            'name' => 'Nome',
            'description' => 'Descrição',
            'short-description' => 'Descrição curta',
            'status' => 'Status',
            'new' => 'Novo',
            'featured' => 'Destaque',
            'price' => 'Preço',
            'special-price' => 'Preço especial',
            'weight' => 'Peso',
            'cost' => 'Custo',
            'length' => 'Comprimento',
            'width' => 'Largura',
            'height' => 'Altura',
            'color' => 'Cor',
            'size' => 'Tamanho',
            'brand' => 'Marca',
            'super-attributes' => 'Superatributos',
            'id-required' => 'O ID do produto é obrigatório',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/products/1" ou ID numérico',
        ],

        'downloadable-product' => [
            'download-link-not-found' => 'Link de download não encontrado ou expirado',
            'purchased-link-not-found' => 'Link de compra não encontrado',
            'file-not-found' => 'Arquivo não encontrado',
            'download-successful' => 'Arquivo pronto para download',
            'token-required' => 'Token de download é obrigatório',
            'invalid-token' => 'Token de download inválido ou expirado',
            'token-expired' => 'O token de download expirou. Por favor, gere um novo',
            'access-denied' => 'Acesso negado: Você não tem permissão para baixar este arquivo',
            'redirect-external-url' => 'Redirecionando para URL de download externo',
            'file-error' => 'Ocorreu um erro ao processar sua solicitação de download',
            'unauthorized-access' => 'Acesso não autorizado ao recurso de download',
            'unauthenticated' => 'Não autorizado: é necessária a autenticação do cliente',
            'link-id-required' => 'downloadableLinkPurchasedId é obrigatório',
            'link-not-found' => 'Link para download não encontrado',
            'not-owner' => 'Não autorizado: este link de download não pertence a você',
            'pending' => 'O download está pendente. Aguarde até que o pedido seja faturado.',
            'limit-exceeded' => 'Limite de downloads excedido',
            'no-downloads-left' => 'Não há mais downloads disponíveis',
        ],

        'booking-slot' => [
            'id-required' => 'O ID do produto de reserva é obrigatório.',
            'date-required' => 'A data é obrigatória.',
            'product-not-found' => 'Produto de reserva não encontrado.',
            'invalid-type' => 'Este tipo de reserva não é suportado.',
            'invalid-time-range' => 'Intervalo de horário inválido.',
            'invalid-time-format' => 'Formato de horário inválido.',
        ],

        'resolver' => [
            'no-resolve-info' => 'ResolveInfo do GraphQL não disponível no contexto. Não é possível determinar a classe do modelo.',
            'unknown-resource-class' => 'Não foi possível determinar a classe do recurso a partir do tipo de retorno do GraphQL',
        ],

        'docs' => [
            'openapi-unavailable' => 'Não foi possível instanciar a OpenAPI Factory',
        ],

        'locale' => [
            'id-required' => 'O ID do idioma é obrigatório',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/locales/1" ou ID numérico',
            'not-found' => 'Idioma não encontrado',
        ],

        'category' => [
            'id-required' => 'O ID da categoria é obrigatório',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/categories/1" ou ID numérico',
            'not-found' => 'Categoria não encontrada',
        ],

        'compare-item' => [
            'id-required' => 'ID do item de comparação é obrigatório',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/compare-items/1" ou ID numérico',
            'not-found' => 'Item de comparação não encontrado',
            'product-id-required' => 'ID do produto é obrigatório',
            'customer-id-required' => 'ID do cliente é obrigatório',
            'product-not-found' => 'Produto não encontrado',
            'customer-not-found' => 'Cliente não encontrado',
            'already-exists' => 'Este produto já está em sua lista de comparação',
            'delete-all-success' => 'Todos os itens de comparação foram removidos com sucesso',
        ],

        'wishlist' => [
            'id-required' => 'O ID do item da lista de desejos é obrigatório',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/wishlists/1" ou ID numérico',
            'not-found' => 'Item da lista de desejos não encontrado',
            'product-id-required' => 'O ID do produto é obrigatório',
            'customer-id-required' => 'O ID do cliente é obrigatório',
            'product-not-found' => 'Produto não encontrado',
            'product-disabled' => 'Este produto está desativado no momento',
            'customer-not-found' => 'Cliente não encontrado',
            'already-exists' => 'Este produto já está na sua lista de desejos',
            'added' => 'Item adicionado à lista de desejos com sucesso',
            'removed' => 'Item removido da lista de desejos com sucesso',
            'product-removed' => 'O produto foi removido',
            'wishlist-item-id-required' => 'O ID do item da lista de desejos é obrigatório',
            'invalid-quantity' => 'A quantidade deve ser maior que 0',
            'move-to-cart-missing-options' => 'O produto tem opções obrigatórias não preenchidas. Configure-o manualmente',
            'moved-to-cart-success' => 'Item movido para o carrinho com sucesso',
            'delete-all-success' => 'Todos os itens da lista de desejos foram removidos com sucesso',
        ],

        'gdpr' => [
            'disabled' => 'As solicitações de dados da GDPR estão desativadas. Habilite a GDPR na configuração do administrador.',
            'unauthenticated' => 'Você precisa estar conectado para gerenciar solicitações de dados da GDPR.',
            'not-found' => 'Solicitação de dados da GDPR não encontrada',
            'type-required' => 'O tipo de solicitação é obrigatório',
            'type-invalid' => 'O tipo de solicitação deve ser "delete" ou "update"',
            'message-required' => 'A mensagem é obrigatória',
            'raised' => 'Sua solicitação de dados da GDPR foi registrada com sucesso.',
            'revoked' => 'Sua solicitação de dados da GDPR foi revogada com sucesso.',
            'revoke-not-allowed' => 'Esta solicitação de dados da GDPR não pode mais ser revogada.',
            'deleted' => 'Sua solicitação de dados da GDPR foi excluída com sucesso.',
        ],

        'section' => [
            'id-required' => 'O ID da seção é obrigatório',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/sections/1" ou ID numérico',
            'not-found' => 'Seção não encontrada',
        ],

        'install' => [
            'starting' => 'Iniciando a instalação da API Platform...',
            'completed-success' => '✓ Instalação da API Platform concluída com sucesso!',
            'failed' => '✗ Falha na instalação: ',

            'provider-already-registered' => 'ℹ Service Provider já registrado',
            'provider-not-registered' => '⚠ Não foi possível registrar o Service Provider automaticamente. Adicione :provider ao bootstrap/providers.php manualmente.',
            'provider-registered' => '✓ Service Provider registrado em bootstrap/providers.php',
            'provider-file-not-found' => 'Arquivo não encontrado: :file',
            'provider-permission-denied' => 'Permissão negada: não é possível gravar em :file. Verifique as permissões do arquivo.',

            'config-already-exists' => 'ℹ O arquivo de configuração já existe',
            'config-published' => '✓ Configuração da API Platform publicada em config/',
            'config-source-not-found' => 'Arquivo de configuração de origem não encontrado: :file',
            'config-permission-denied' => 'Permissão negada: não é possível gravar em :directory. Verifique as permissões do diretório.',

            'translatable-not-found' => 'ℹ TranslatableModel não encontrado - ignorando',
            'translatable-already-abstract' => 'ℹ TranslatableModel já é abstrato',
            'translatable-made-abstract' => '✓ TranslatableModel tornado abstrato',
            'translatable-permission-denied' => 'Permissão negada: não é possível gravar em :file. Verifique as permissões do arquivo.',

            'providers-already-registered' => 'ℹ Os providers da API Platform já estão registrados',
            'providers-registered' => '✓ Providers da API Platform registrados em bootstrap/app.php',
            'providers-file-not-found' => 'Arquivo não encontrado: :file',
            'providers-permission-denied' => 'Permissão negada: não é possível gravar em :file. Verifique as permissões do arquivo.',
            'providers-not-found' => 'Não foi possível encontrar a criação da Application em bootstrap/app.php',

            'assets-published' => '✓ Assets do pacote publicados',
            'running-migrations' => 'Executando as migrações do banco de dados...',
            'migrations-completed' => '✓ Migrações do banco de dados concluídas',
            'clearing-caches' => 'Limpando e otimizando caches...',
            'caches-optimized' => '✓ Caches limpos e otimizados',
            'generating-api-key' => 'Gerando a chave de API...',
            'api-key-generated' => '✓ Chave de API gerada com sucesso',
            'api-key-already-exists' => 'ℹ A chave de API da loja padrão já existe - ignorando a geração',

            'migrations-error' => 'Falha nas migrações do banco de dados.',
            'migrations-error-running' => 'Erro ao executar as migrações do banco de dados: :error',
            'cache-clear-error' => 'Falha ao limpar o cache.',
            'cache-optimize-error' => 'Falha na otimização.',
            'cache-error' => 'Erro ao limpar e otimizar os caches: :error',
            'api-key-generation-error' => 'Falha na geração da chave de API.',
            'api-key-error' => 'Erro ao gerar a chave de API: :error',

            'key-management-required' => '--key é obrigatório para a ação :action',
            'key-rotation-error' => 'Erro ao rotacionar a chave: :error',
            'key-deactivation-error' => 'Erro ao desativar a chave: :error',
            'key-not-found' => 'Chave de API não encontrada: :identifier',

            'maintenance-starting' => '🔄 Iniciando a manutenção das chaves de API...',
            'maintenance-complete' => '✅ Manutenção das chaves de API concluída',
            'cleanup-expired-keys' => '🧹 Limpando chaves expiradas...',
            'cleanup-expired-none' => '   ℹ️ Nenhuma chave expirada para limpar',
            'invalidate-deprecated' => '⚠️ Invalidando chaves obsoletas...',
            'invalidate-deprecated-none' => '   ℹ️ Nenhuma chave obsoleta para invalidar',
            'notify-expiring' => '📧 Enviando notificações de expiração...',
            'notify-expiring-none' => '   ℹ️ Nenhuma chave precisa de notificação',

            'key-rotated-success' => '✅ Chave rotacionada com sucesso!',
            'key-deactivated-success' => '✅ Chave desativada com sucesso!',
            'deactivation-cancelled' => 'Desativação cancelada.',
            'cleanup-cancelled' => 'Limpeza cancelada.',

            'key-status-title' => 'Status da chave: :name',
            'key-status-active' => 'Ativa: :status',
            'key-status-usable' => 'Utilizável: :status',
            'key-status-expired' => 'Expirada: :status',
            'key-status-deprecated' => 'Obsoleta: :status',
            'key-status-expires-at' => 'Expira em: :expires',
            'key-status-days-until-expiry' => 'Dias até a expiração: :days',
            'key-status-last-used' => 'Último uso: :date',
            'key-status-rotated-from' => 'Rotacionada a partir de: :key',
            'key-status-rotated-keys' => 'Chaves rotacionadas a partir desta: :count',

            'policy-compliance-summary' => 'Resumo de conformidade com a política de rotação de chaves de API',
            'available-actions' => 'Ações disponíveis: rotate, deactivate, cleanup, status, expiring, unused, summary',

            'asset-linked-success' => '✓ Assets da API Platform vinculados com sucesso',
            'symlink-create-failed' => 'Não foi possível criar o link simbólico, copiando os assets...',
            'asset-copy-warning' => 'Aviso: não foi possível vincular nem copiar os assets da API Platform. Pode ser necessária uma configuração manual.',
            'asset-copied-success' => '✓ Assets da API Platform copiados com sucesso',

            'key-name-required' => 'O nome da chave da loja não pode ficar vazio.',
            'key-generated-success' => 'Chave da loja gerada com sucesso!',
            'key-details' => 'Detalhes da chave:',
            'key-secure-warning' => 'Mantenha esta chave em segurança! Ela será usada no cabeçalho X-STOREFRONT-KEY.',
            'key-share-warning' => 'Não compartilhe esta chave publicamente nem a inclua no controle de versão.',

            'key-already-exists' => 'Já existe uma chave da loja com o nome \':name\'.',
            'cleanup-success' => '✅ :count chaves expiradas foram limpas',
            'confirm-cleanup' => 'Isso fará a exclusão lógica de todas as chaves expiradas. Continuar?',
            'confirm-deactivate' => 'Tem certeza de que deseja desativar a chave: :name?',

            'old-key' => 'Chave antiga: :name',
            'old-key-id' => 'ID da chave antiga: :id',
            'deprecation-date' => 'Data de descontinuação: :date',
            'new-key' => 'Nova chave: :name',
            'new-key-id' => 'ID da nova chave: :id',
            'new-key-value' => 'Valor da nova chave: :key',
            'expires-at' => 'Expira em: :date',
            'invalid-action' => 'Ação inválida: :action',
            'key-active' => 'Ativa: :status',
            'key-usable' => 'Utilizável: :status',
            'key-expired' => 'Expirada: :status',
            'key-deprecated' => 'Obsoleta: :status',
            'key-field-id' => 'ID',
            'key-field-name' => 'Nome',
            'key-field-key' => 'Chave',
            'key-field-rate-limit' => 'Limite de requisições',
            'key-field-status' => 'Status',
            'key-days' => ' dias',
            'key-never' => 'Nunca',
            'key-requests-minute' => ' requisições/minuto',
            'key-unlimited' => 'Ilimitado',
            'rate-limit-exceeded' => 'O limite de requisições solicitado excede o máximo permitido de :max. Definindo para :max requisições/minuto.',

            'vendor-path-not-found' => 'Caminho vendor da API Platform não encontrado em: :path',
            'assets-already-linked' => 'Os assets da API Platform já estão vinculados em: :path',
            'no-keys-expiring' => 'Nenhuma chave expira nos próximos :days dias',
            'keys-expiring-title' => 'Chaves que expiram nos próximos :days dias:',
            'key-display-format' => '• :name (ID: :id)',
            'key-expires-display' => '  Expira em: :date (:days dias restantes)',
            'no-unused-keys' => 'Nenhuma chave sem uso encontrada (> :days dias)',
            'unused-keys-title' => 'Chaves sem uso (> :days dias):',
            'key-last-used-display' => '  Último uso: :date',
            'total-keys' => 'Total de chaves: :count',
            'valid-keys' => 'Chaves válidas: :count',
            'expired-keys' => 'Chaves expiradas: :count',
            'deprecated-keys' => 'Chaves obsoletas: :count',
            'keys-expiring-soon' => 'Chaves que expiram em breve (7 dias): :count',
            'unused-keys-summary' => 'Chaves sem uso (90 dias): :count',
            'recently-rotated' => 'Rotacionadas recentemente (30 dias): :count',
            'cleanup-success-message' => '   ✅ :count chaves expiradas foram limpas',
            'invalidate-success-message' => '   ✅ :count chaves obsoletas foram invalidadas',
            'notify-success-message' => '   ✅ :count notificações de expiração enviadas',
            'notify-failed-message' => '   ⚠️ Falha ao notificar sobre :key: :error',
            'publish-assets-warning' => 'Aviso: não foi possível publicar os assets do pacote. :error',
            'env-file-not-found' => 'O arquivo .env não foi encontrado. Crie-o antes de instalar.',
            'env-permission-denied' => 'O arquivo .env não pode ser gravado. Verifique as permissões.',
            'env-config-saved' => '✓ Configuração de ambiente da API Platform salva com sucesso',
            'api-endpoints' => 'Endpoints da API:',
            'api-documentation' => 'Documentação da API: :url',
            'api-landing-page' => 'Página inicial da API: :url',
            'graphql-playground' => 'GraphQL Playground: :url',
            'rest-api-storefront' => 'Swagger da API REST da loja: :url',
            'rest-api-admin' => 'Swagger da API REST do administrador: :url',
            'generating-jwt-keys' => 'Gerando as chaves de assinatura JWT para os tokens da API administrativa...',
            'jwt-keys-generated' => '✓ As chaves de assinatura JWT estão prontas',
            'jwt-keys-warning' => 'Aviso: não foi possível gerar as chaves de assinatura JWT. Execute php artisan bagisto-api:jwt-keys manualmente. :error',
            'admin-login' => 'Login da API administrativa: :url',
            'admin-api-tokens' => 'Tokens da API administrativa (integrações de servidor): :url',
            'key-rotation-invalid-key' => 'Não é possível rotacionar uma chave inválida ou expirada',
            'env-file-not-found' => 'O arquivo .env não foi encontrado',
            'env-permission-denied' => 'Permissão negada: não é possível gravar no arquivo .env. Verifique as permissões do arquivo.',
        ],

        'attribute-translation' => [
            'id-required' => 'O ID da tradução do atributo é obrigatório',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/attribute-translations/1" ou ID numérico',
            'not-found' => 'Tradução do atributo não encontrada',
        ],

        'currency' => [
            'id-required' => 'O ID da moeda é obrigatório',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/currencies/1" ou ID numérico',
            'not-found' => 'Moeda não encontrada',
        ],

        'customer-address' => [
            'id-required' => 'O ID do endereço do cliente é obrigatório',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/customer-addresses/1" ou ID numérico',
        ],

        'customer-order-shipment-item' => [
            'id-required' => 'O ID do item da remessa é obrigatório',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/customer-order-shipment-items/1" ou ID numérico',
            'not-found' => 'Item da remessa não encontrado',
        ],

        'page' => [
            'id-required' => 'O ID da página é obrigatório',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/pages/1" ou ID numérico',
            'not-found' => 'Página não encontrada',
        ],

        'page-translation' => [
            'id-required' => 'O ID da tradução da página é obrigatório',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/page-translations/1" ou ID numérico',
            'not-found' => 'Tradução da página não encontrada',
        ],

        'product-bundle-option' => [
            'id-required' => 'O ID da opção do pacote é obrigatório',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/product-bundle-options/1" ou ID numérico',
            'not-found' => 'Opção do pacote não encontrada',
        ],

        'product-bundle-option-product' => [
            'id-required' => 'O ID do produto da opção do pacote é obrigatório',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/product-bundle-option-products/1" ou ID numérico',
            'not-found' => 'Produto da opção do pacote não encontrado',
        ],

        'product-downloadable-link' => [
            'id-required' => 'O ID do link para download é obrigatório',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/product-downloadable-links/1" ou ID numérico',
            'not-found' => 'Link para download não encontrado',
        ],

        'product-downloadable-sample' => [
            'id-required' => 'O ID da amostra para download é obrigatório',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/product-downloadable-samples/1" ou ID numérico',
            'not-found' => 'Amostra para download não encontrada',
        ],

        'product-grouped-product' => [
            'id-required' => 'O ID do produto agrupado é obrigatório',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/product-grouped-products/1" ou ID numérico',
            'not-found' => 'Produto agrupado não encontrado',
        ],

        'product-images' => [
            'id-required' => 'O ID da imagem do produto é obrigatório',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/product-images/1" ou ID numérico',
            'not-found' => 'Imagem do produto não encontrada',
        ],

        'product-videos' => [
            'id-required' => 'O ID do vídeo do produto é obrigatório',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/product-videos/1" ou ID numérico',
            'not-found' => 'Vídeo do produto não encontrado',
        ],

        'theme-customization' => [
            'id-required' => 'O ID da personalização do tema é obrigatório',
            'invalid-id-format' => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/theme-customizations/1" ou ID numérico',
            'not-found' => 'Personalização do tema não encontrada',
        ],

        'unknown-resource' => [
            'id-required' => 'O ID do recurso é obrigatório',
            'not-found' => 'Recurso não encontrado',
        ],
    ],

    'admin' => [
        'profile' => [
            'unauthenticated' => 'Você não está autenticado.',
        ],

        'dashboard' => [
            'invalid-type' => 'Tipo de estatística do painel desconhecido. Tipos válidos: over-all, today, stock-threshold-products, total-sales, top-selling-products, top-customers.',
        ],

        'rma' => [
            'not-found' => 'Solicitação de devolução não encontrada.',
            'invalid-input' => 'Detalhes da devolução ausentes ou inválidos.',
            'invalid-order' => 'O pedido selecionado não é válido.',
            'invalid-item' => 'O item selecionado não está elegível para devolução.',
            'invalid-resolution-type' => 'O tipo de resolução deve ser "return" ou "cancel_items".',
            'qty-exceeds' => 'A quantidade solicitada excede a quantidade que pode ser devolvida.',
            'created' => 'Solicitação de devolução criada com sucesso.',
            'status-updated' => 'Status da devolução atualizado com sucesso.',
            'invalid-status' => 'Status de devolução inválido.',
            'reopened' => 'Solicitação de devolução reaberta com sucesso.',
            'reopen-not-allowed' => 'Esta solicitação de devolução não pode ser reaberta.',
            'refund-failed' => 'Não foi possível criar o reembolso para esta devolução.',
            'message-sent' => 'Mensagem enviada com sucesso.',
            'no-permission' => 'Você não tem permissão para realizar esta ação.',
            'reason-not-found' => 'Motivo de devolução não encontrado.',
            'reason-deleted' => 'Motivo de devolução excluído com sucesso.',
            'status-not-found' => 'Status de devolução não encontrado.',
            'status-deleted' => 'Status de devolução excluído com sucesso.',
            'status-default-undeletable' => 'Um status de devolução padrão não pode ser excluído.',
            'rule-not-found' => 'Regra de devolução não encontrada.',
            'rule-deleted' => 'Regra de devolução excluída com sucesso.',
            'custom-field-not-found' => 'Campo personalizado de devolução não encontrado.',
            'custom-field-deleted' => 'Campo personalizado de devolução excluído com sucesso.',
            'custom-field-options-required' => 'Pelo menos uma opção é obrigatória para este tipo de campo.',
            'mass-indices-required' => 'Pelo menos um ID é obrigatório.',
            'mass-delete-success' => 'Itens selecionados excluídos.',
            'mass-update-success' => 'Itens selecionados atualizados.',
            'mass-value-invalid' => 'O valor do status deve ser 0 ou 1.',
            'image-not-found' => 'Imagem da devolução não encontrada.',
            'attachment-not-found' => 'Anexo da mensagem não encontrado.',
        ],

        'eu-withdrawal' => [
            'not-found' => 'Declaração de desistência não encontrada.',
            'no-permission' => 'Você não tem permissão para gerenciar desistências da UE.',
            'declined' => 'Desistência marcada como recusada.',
            'refunded' => 'Desistência marcada como reembolsada.',
            'confirmation-resent' => 'E-mail de confirmação reenviado com sucesso.',
            'confirmation-failed' => 'Não foi possível enviar o e-mail de confirmação.',
        ],

        'reporting' => [
            'invalid-entity' => 'Entidade de relatório desconhecida.',
            'invalid-type' => 'Tipo de estatística de relatório desconhecido para esta entidade.',
            'export-format-unsupported' => 'Somente os formatos de exportação csv, xls e xlsx são suportados.',
        ],

        'configuration' => [
            'unauthenticated' => 'Você não está autenticado.',
            'no-permission' => 'Você não tem permissão para atualizar a configuração.',
            'slug-required' => 'O parâmetro de consulta `slug` é obrigatório.',
            'slug-not-found' => 'Nenhuma seção de configuração está registrada para o slug informado.',
            'values-required' => 'Pelo menos um valor é obrigatório em `values`.',
            'scope-escape' => 'A chave `:key` está fora da subárvore do slug solicitado.',
            'unknown-field' => 'A chave `:key` não é um campo de configuração registrado.',
            'validation-failed' => 'Um ou mais valores de configuração falharam na validação.',
            'file-upload-rest-only' => 'O envio de arquivos binários para o campo `:field` só é suportado pelo endpoint REST multipart, não pelo GraphQL.',
            'custom-view-readonly' => 'O campo `:field` é um campo personalizado renderizado em blade e não pode ser atualizado pela API.',
            'update-success' => 'Configuração atualizada com sucesso.',
        ],

        'customer' => [
            'not-found' => 'Cliente não encontrado.',
            'no-permission' => 'Você não tem permissão para realizar esta ação.',
            'has-active-orders' => 'O cliente possui pedidos pendentes ou em processamento e não pode ser excluído.',
            'deleted' => 'Cliente excluído com sucesso.',
            'mass-delete-indices-required' => 'Pelo menos um ID de cliente é obrigatório.',
            'mass-delete-success' => 'Clientes excluídos.',
            'mass-update-indices-required' => 'Pelo menos um ID de cliente é obrigatório.',
            'mass-update-value-invalid' => 'O valor do status deve ser 0 ou 1.',
            'mass-update-success' => 'Status do cliente atualizado.',

            'address' => [
                'not-found' => 'Endereço não encontrado.',
                'not-owned' => 'Este endereço não pertence ao cliente especificado.',
                'customer-id-required' => 'customerId é obrigatório para identificar o proprietário do endereço.',
                'deleted' => 'Endereço excluído com sucesso.',
            ],

            'note' => [
                'empty' => 'O texto da observação é obrigatório.',
                'created' => 'Observação adicionada com sucesso.',
            ],

            'review' => [
                'not-found' => 'Avaliação não encontrada.',
                'no-permission' => 'Você não tem permissão para realizar esta ação.',
                'invalid-status' => 'O status deve ser um dos seguintes: pending, approved, disapproved.',
                'updated' => 'Avaliação atualizada com sucesso.',
                'deleted' => 'Avaliação excluída com sucesso.',
                'mass-delete-indices-required' => 'Pelo menos um ID de avaliação é obrigatório.',
                'mass-delete-success' => 'Avaliações excluídas.',
                'mass-update-indices-required' => 'Pelo menos um ID de avaliação é obrigatório.',
                'mass-update-value-invalid' => 'O valor do status deve ser um dos seguintes: pending, approved, disapproved.',
                'mass-update-success' => 'Status da avaliação atualizado.',
            ],

            'group' => [
                'not-found' => 'Grupo de clientes não encontrado.',
                'deleted' => 'Grupo de clientes excluído com sucesso.',
                'is-system' => 'Grupos de clientes do sistema não podem ser excluídos.',
                'has-customers' => 'O grupo de clientes possui clientes vinculados e não pode ser excluído.',
                'system-code-immutable' => 'O código de um grupo de clientes do sistema não pode ser alterado.',
                'system-flag-immutable' => 'O indicador is_user_defined de um grupo de clientes do sistema não pode ser alterado.',
                'mass-delete-indices-required' => 'Pelo menos um ID de grupo de clientes é obrigatório.',
                'mass-delete-success' => 'Grupos de clientes excluídos.',
            ],

            'gdpr' => [
                'not-found' => 'Solicitação da GDPR não encontrada.',
                'no-permission' => 'Você não tem permissão para realizar esta ação.',
                'deleted' => 'Solicitação da GDPR excluída com sucesso.',
                'invalid-status' => 'O status deve ser um dos seguintes: pending, processing, declined, approved, revoked.',
                'no-changes' => 'É obrigatório informar pelo menos o status ou a mensagem.',
                'already-processed' => 'Esta solicitação da GDPR já foi processada e não pode ser executada novamente.',

                'process' => [
                    'approved' => 'Solicitação da GDPR aprovada.',
                    'deleted' => 'Solicitação da GDPR aprovada e cliente excluído.',
                ],
            ],
        ],

        'order' => [
            'not-found' => 'Pedido não encontrado.',

            'reorder' => [
                'success' => 'Pedido refeito com sucesso. Um novo carrinho de rascunho foi criado.',
                'cannot-reorder' => 'Este pedido não pode ser refeito (pedido de visitante ou um ou mais itens não estão mais à venda).',
                'failed' => 'Falha ao refazer o pedido. Tente novamente.',
                'guest-not-supported' => 'Refazer pedido não é suportado para pedidos de visitantes.',
                'items-not-saleable' => 'Um ou mais itens deste pedido não estão mais disponíveis para compra.',
                'no-permission' => 'Você não tem permissão para criar pedidos.',
                'disabled-in-settings' => 'Refazer pedido pelo administrador está desativado nas configurações da loja.',
            ],

            'actions' => [
                'cancel' => [
                    'closed' => 'Pedidos encerrados não podem ser cancelados.',
                    'fraud' => 'Pedidos fraudulentos não podem ser cancelados.',
                    'nothing-to-cancel' => 'Não há nada para cancelar neste pedido.',
                    'already-processed' => 'Este pedido já foi faturado ou enviado e não pode mais ser cancelado. Em vez disso, você pode emitir um reembolso.',
                    'no-permission' => 'Você não tem permissão para cancelar pedidos.',
                    'success' => 'Pedido cancelado com sucesso.',
                    'failed' => 'Não foi possível cancelar o pedido.',
                ],

                'comment' => [
                    'empty' => 'O comentário é obrigatório.',
                    'success' => 'Comentário adicionado ao pedido.',
                    'failed' => 'Não foi possível adicionar o comentário.',
                ],

                'invoice' => [
                    'closed' => 'Pedidos encerrados não podem ser faturados.',
                    'fraud' => 'Pedidos fraudulentos não podem ser faturados.',
                    'nothing-to-invoice' => 'Não há nada para faturar neste pedido.',
                    'already-invoiced' => 'Uma fatura já foi gerada para este pedido.',
                    'paypal-standard-blocked' => 'Não é possível criar faturas para pedidos pagos pelo PayPal Standard.',
                    'no-permission' => 'Você não tem permissão para criar faturas.',
                    'items-required' => 'É obrigatório pelo menos um item com quantidade positiva.',
                    'qty-exceeds' => 'A quantidade solicitada (:requested) excede a quantidade disponível (:available) para o SKU :sku.',
                    'not-found' => 'Fatura não encontrada.',
                    'success' => 'Fatura criada com sucesso.',
                    'failed' => 'Não foi possível criar a fatura.',
                    'pdf-failed' => 'Não foi possível gerar o PDF da fatura.',
                    'email-invalid' => 'É obrigatório um e-mail de destinatário válido.',
                    'duplicate-sent' => 'E-mail da fatura enviado para :email.',
                    'mass-status-indices-required' => 'Pelo menos um ID de fatura é obrigatório.',
                    'mass-status-value-invalid' => 'O status deve ser um dos seguintes: pending, paid, overdue.',
                    'mass-status-success' => 'Status da fatura atualizado com sucesso.',
                ],

                'shipment' => [
                    'closed' => 'Pedidos encerrados não podem ser enviados.',
                    'fraud' => 'Pedidos fraudulentos não podem ser enviados.',
                    'nothing-to-ship' => 'Não há nada para enviar neste pedido.',
                    'no-permission' => 'Você não tem permissão para enviar pedidos.',
                    'source-required' => 'A origem de estoque é obrigatória.',
                    'items-required' => 'É obrigatório pelo menos um item com quantidade positiva.',
                    'qty-exceeds' => 'A quantidade solicitada (:requested) excede a quantidade disponível (:available) para o SKU :sku.',
                    'inventory-insufficient' => 'O estoque na origem selecionada é insuficiente para o SKU :sku.',
                    'not-found' => 'Remessa não encontrada.',
                    'success' => 'Remessa criada com sucesso.',
                    'failed' => 'Não foi possível criar a remessa.',
                ],

                'refund' => [
                    'closed' => 'Pedidos encerrados não podem ser reembolsados.',
                    'fraud' => 'Pedidos fraudulentos não podem ser reembolsados.',
                    'nothing-to-refund' => 'Não resta nada a reembolsar neste pedido.',
                    'no-permission' => 'Você não tem permissão para criar reembolsos.',
                    'qty-exceeds' => 'A quantidade solicitada (:requested) excede a quantidade disponível (:available) para o SKU :sku.',
                    'amount-zero' => 'O valor calculado do reembolso é zero. Ajuste a quantidade, o frete ou os valores de ajuste.',
                    'amount-exceeds-max' => 'O valor do reembolso (:amount) excede o valor máximo reembolsável (:max).',
                    'not-found' => 'Reembolso não encontrado.',
                    'success' => 'Reembolso criado com sucesso.',
                    'failed' => 'Não foi possível criar o reembolso.',
                ],
            ],
        ],

        'appearance' => [
            'no-permission' => 'Você não tem permissão para gerenciar a aparência.',

            'theme' => [
                'not-found' => 'Tema não encontrado.',
                'not-installed' => 'Este tema não está instalado.',
                'channel-ids-required' => 'Pelo menos um canal é obrigatório.',
                'unknown-channel' => 'Um ou mais canais não existem.',
                'activated' => 'Tema ativado com sucesso.',
            ],

            'section' => [
                'not-found' => 'Seção não encontrada.',
                'invalid-payload' => 'Payload de seção não suportado.',
                'options-required' => 'As opções são obrigatórias.',
                'status-required' => 'O status é obrigatório.',
                'section-ids-required' => 'Pelo menos uma seção é obrigatória.',
                'reorder-single-scope' => 'As seções só podem ser reordenadas dentro de um tema e canal por vez.',
                'reorder-incomplete' => 'A reordenação requer todas as seções do tema e do canal, na ordem em que devem ser renderizadas.',
                'unknown-section' => 'Uma ou mais seções não existem.',
                'file-required' => 'Um arquivo é obrigatório.',
                'created' => 'Seção criada com sucesso.',
                'updated' => 'Seção atualizada com sucesso.',
                'deleted' => 'Seção excluída com sucesso.',
                'draft-saved' => 'Alterações da seção preparadas com sucesso.',
                'status-staged' => 'Status da seção preparado com sucesso.',
                'order-staged' => 'Ordem da seção preparada com sucesso.',
                'duplicated' => 'Seção copiada com sucesso.',
                'published' => 'Alterações da seção publicadas com sucesso.',
                'discarded' => 'Alterações da seção descartadas com sucesso.',
                'media-uploaded' => 'Mídia enviada com sucesso.',
            ],
        ],

        'sales' => [
            'no-permission' => 'Você não tem permissão para visualizar este recurso de vendas.',

            'invoice' => [
                'not-found' => 'Fatura não encontrada.',
                'no-permission' => 'Você não tem permissão para visualizar faturas.',
            ],

            'shipment' => [
                'not-found' => 'Remessa não encontrada.',
                'no-permission' => 'Você não tem permissão para visualizar remessas.',
            ],

            'refund' => [
                'not-found' => 'Reembolso não encontrado.',
                'no-permission' => 'Você não tem permissão para visualizar reembolsos.',
                'export-format-unsupported' => 'Somente exportações em csv, xls e xlsx são suportadas.',
            ],

            'transaction' => [
                'not-found' => 'Transação não encontrada.',
                'no-permission' => 'Você não tem permissão para visualizar transações.',

                'create' => [
                    'invoice-required' => 'O campo invoiceId é obrigatório.',
                    'payment-method-required' => 'O campo paymentMethod é obrigatório.',
                    'amount-invalid' => 'O campo amount é obrigatório e deve ser numérico.',
                    'invoice-missing' => 'A fatura especificada não foi encontrada.',
                    'already-paid' => 'Esta fatura já está totalmente paga.',
                    'amount-exceeds' => 'O valor do pagamento excede o total geral da fatura.',
                    'amount-zero' => 'O valor do pagamento deve ser maior que zero.',
                ],
            ],

            'booking' => [
                'not-found' => 'Reserva não encontrada.',
                'no-permission' => 'Você não tem permissão para visualizar reservas.',
            ],

            'export' => [
                'format-unsupported' => 'Somente exportações em csv, xls e xlsx são suportadas.',
            ],
        ],

        'cart' => [
            'not-found' => 'Carrinho não encontrado.',
            'not-draft' => 'Este carrinho não pode ser modificado pela API de administração.',
            'product-required' => 'productId é obrigatório.',
            'product-not-found' => 'Produto não encontrado.',
            'booking-unsupported' => 'Produtos de reserva não podem ser adicionados a um pedido de rascunho do administrador. Compras de reservas devem ser feitas pela loja do cliente.',
            'product-not-saleable' => 'Este produto não está disponível para venda (fora de estoque ou desativado) e não pode ser adicionado ao pedido.',
            'qty-required' => 'qty é obrigatório e deve ser um objeto não vazio de itemId => quantidade.',
            'cart-item-required' => 'cartItemId é obrigatório.',
            'item-added' => 'Item adicionado ao carrinho.',
            'item-add-failed' => 'Não foi possível adicionar o item ao carrinho.',
            'item-updated' => 'Itens do carrinho atualizados.',
            'item-update-failed' => 'Não foi possível atualizar os itens do carrinho.',
            'item-removed' => 'Item removido do carrinho.',
            'item-remove-failed' => 'Não foi possível remover o item do carrinho.',
            'address-saved' => 'Endereço salvo.',
            'address-required' => 'O endereço de cobrança é obrigatório.',
            'address-incomplete' => 'O :field é obrigatório para salvar o endereço.',
            'coupon-code-required' => 'O código do cupom é obrigatório.',
            'coupon-applied' => 'Cupom aplicado.',
            'coupon-already-applied' => 'O cupom já está aplicado a este carrinho.',
            'coupon-not-found' => 'Cupom não encontrado ou inativo.',
            'coupon-removed' => 'Cupom removido.',
            'coupon-error' => 'Ocorreu um erro ao aplicar o cupom.',
            'unknown-error' => 'Ocorreu um erro.',
            'draft-created' => 'Carrinho de rascunho criado.',
            'draft-failed' => 'Não foi possível criar o carrinho de rascunho.',
            'addresses-required-for-shipping' => 'Os endereços devem ser salvos antes de selecionar um método de envio.',
            'shipping-required-for-payment' => 'O método de envio deve ser selecionado antes do método de pagamento.',
            'shipping-method-required' => 'O método de envio é obrigatório.',
            'shipping-method-saved' => 'Método de envio salvo.',
            'shipping-method-failed' => 'Não foi possível salvar o método de envio.',
            'payment-method-required' => 'O método de pagamento é obrigatório.',
            'payment-method-saved' => 'Método de pagamento salvo.',
            'payment-method-failed' => 'Não foi possível salvar o método de pagamento.',

            'place-order' => [
                'success' => 'Pedido realizado com sucesso.',
                'failed' => 'Não foi possível realizar o pedido.',
                'error' => 'Não foi possível realizar o pedido: o carrinho contém erros.',
                'minimum-order-error' => 'O valor mínimo do pedido é :amount. Adicione mais itens antes de realizar o pedido.',
                'empty-cart' => 'O carrinho está vazio — adicione itens antes de realizar um pedido.',
                'addresses-required' => 'Os endereços de cobrança e de entrega devem ser salvos antes de realizar o pedido.',
                'shipping-required' => 'Um método de envio deve ser selecionado antes de realizar o pedido.',
                'payment-required' => 'Um método de pagamento deve ser selecionado antes de realizar o pedido.',
                'payment-method-unsupported' => 'Somente cashondelivery e moneytransfer são suportados para pedidos feitos pelo administrador.',
            ],
        ],

        'product' => [
            'not-found' => 'Produto não encontrado.',

            'mass-delete-success' => 'Produtos excluídos com sucesso.',
            'mass-update-status-success' => 'Status dos produtos atualizado com sucesso.',
            'indices-required' => 'O campo indices é obrigatório e deve ser um array não vazio.',
            'indices-invalid' => 'O campo indices deve ser um array de IDs de produtos inteiros.',
            'value-invalid' => 'O campo value é obrigatório e deve ser 0 ou 1.',
            'mass-delete-failed' => 'Um ou mais produtos não puderam ser excluídos.',
            'no-permission' => 'Você não tem permissão para gerenciar produtos.',

            'copy-success' => 'Produto copiado com sucesso.',
            'copy-failed' => 'Falha ao copiar o produto.',
            'copy-variant-not-supported' => 'Variantes de produtos configuráveis não podem ser copiadas. Copie o produto configurável pai.',

            'create' => [
                'created' => 'Produto criado com sucesso.',
                'create-failed' => 'Falha ao criar o produto.',
                'sku-required' => 'O campo sku é obrigatório.',
                'sku-unique' => 'O sku já está em uso.',
                'sku-invalid' => 'O sku pode conter apenas letras, números e hifens (por exemplo, "sp-001").',
                'attribute-family-required' => 'O campo attribute_family_id é obrigatório.',
                'attribute-family-not-found' => 'A família de atributos selecionada não existe.',
                'type-required' => 'O campo type é obrigatório.',
                'type-not-yet-supported' => 'O tipo de produto ":type" não é suportado por esta API. Tipos permitidos: simple, virtual, downloadable, grouped, bundle, configurable, booking.',
                'super-attributes-required' => 'O campo super_attributes é obrigatório quando type=configurable. Informe um mapa de código (ou id) de atributo para option_ids, por exemplo { "color": [1,2], "size": [4,5] }.',
                'super-attributes-invalid' => 'O campo super_attributes deve ser um mapa não vazio de código (ou id) de atributo para uma lista não vazia de option_ids.',
                'no-permission' => 'Você não tem permissão para criar produtos.',
            ],

            'update' => [
                'updated' => 'Produto atualizado com sucesso.',
                'update-failed' => 'Falha ao atualizar o produto.',
                'id-required' => 'Um ID de produto é obrigatório.',
                'url-key-required' => 'O campo url_key não pode ficar vazio.',
                'url-key-unique' => 'O url_key já está em uso por outro produto.',
                'special-price-invalid' => 'O special_price deve ser um número menor que price.',
                'special-price-date-range-invalid' => 'O special_price_to deve ser igual ou posterior a special_price_from.',
                'boolean-field-invalid' => 'O campo :field deve ser 0 ou 1.',
                'categories-invalid' => 'O campo categories deve ser um array de IDs de categorias.',
                'channels-invalid' => 'O campo channels deve ser um array de IDs de canais.',
                'super-attributes-cannot-change' => 'super_attributes não pode ser alterado após a criação do produto.',
                'sub-resource-stripped-images' => 'As imagens devem ser gerenciadas via POST /api/admin/catalog/products/{id}/images. O campo images foi ignorado.',
                'sub-resource-stripped-inventories' => 'Os estoques devem ser gerenciados via PUT /api/admin/catalog/products/{id}/inventories. O campo inventories foi ignorado.',
                'sub-resource-stripped-customer-group-prices' => 'Os preços por grupo de clientes devem ser gerenciados via /api/admin/catalog/products/{id}/customer-group-prices. O campo customer_group_prices foi ignorado.',
                'translations-single-locale' => 'Somente o idioma solicitado foi atualizado. Para atualizar o(s) idioma(s) :locales, envie uma requisição separada com ?locale=<code> para cada um.',
            ],

            'delete' => [
                'deleted' => 'Produto excluído com sucesso.',
                'delete-failed' => 'Falha ao excluir o produto.',
            ],

            'image' => [
                'uploaded' => 'Imagem do produto enviada com sucesso.',
                'updated' => 'Imagem do produto atualizada com sucesso.',
                'update-empty' => 'Envie um texto alternativo ou uma posição para atualizar.',
                'reordered' => 'Imagens do produto reordenadas com sucesso.',
                'deleted' => 'Imagem do produto excluída com sucesso.',
                'image-required' => 'Um arquivo de imagem é obrigatório (campo multipart "image").',
                'image-invalid-type' => 'O tipo de imagem deve ser um dos seguintes: bmp, jpeg, jpg, png, webp.',
                'image-too-large' => 'A imagem é grande demais. O tamanho máximo permitido é 4 MB.',
                'not-found' => 'Imagem do produto não encontrada.',
                'no-permission' => 'Você não tem permissão para gerenciar imagens de produtos.',
                'upload-failed' => 'Falha ao enviar a imagem do produto.',
                'reorder-failed' => 'Falha ao reordenar as imagens do produto.',
                'delete-failed' => 'Falha ao excluir a imagem do produto.',
                'order-required' => 'O array "order" é obrigatório e não pode estar vazio.',
                'order-invalid' => 'Cada entrada em "order" deve ser um objeto com id e position.',
                'id-not-on-product' => 'A imagem :id não pertence a este produto.',
                'graphql-upload-unsupported' => 'O envio de imagens via GraphQL não é suportado. Use POST /api/admin/catalog/products/{productId}/images com multipart/form-data.',
            ],

            'downloadable' => [
                'file-required' => 'Um arquivo é obrigatório (campo multipart "file").',
                'file-too-large' => 'O arquivo é grande demais. O tamanho máximo permitido é 128 MB.',
                'upload-failed' => 'Falha ao enviar o arquivo para download.',
                'file-not-found' => 'Nenhum arquivo armazenado encontrado para este produto e atributo.',
                'no-permission' => 'Você não tem permissão para gerenciar arquivos de produtos para download.',
            ],

            'video' => [
                'uploaded' => 'Vídeo do produto enviado com sucesso.',
                'deleted' => 'Vídeo do produto excluído com sucesso.',
                'video-required' => 'Um arquivo de vídeo é obrigatório (campo multipart "video").',
                'video-invalid-type' => 'O tipo de vídeo deve ser um dos seguintes: mp4, webm, ogg, mov.',
                'video-too-large' => 'O vídeo é grande demais. O tamanho máximo permitido é 100 MB.',
                'not-found' => 'Vídeo do produto não encontrado.',
                'no-permission' => 'Você não tem permissão para gerenciar vídeos de produtos.',
                'upload-failed' => 'Falha ao enviar o vídeo do produto.',
                'delete-failed' => 'Falha ao excluir o vídeo do produto.',
                'graphql-upload-unsupported' => 'O envio de vídeos via GraphQL não é suportado. Use POST /api/admin/catalog/products/{productId}/videos com multipart/form-data.',
            ],

            'inventory' => [
                'updated' => 'Estoques do produto salvos com sucesso.',
                'inventories-required' => 'O campo inventories é obrigatório e deve ser um mapa não vazio de inventory_source_id para quantidade.',
                'inventories-invalid' => 'O campo inventories deve ser um mapa de inventory_source_id (inteiro positivo) para quantidade (inteiro não negativo).',
                'source-not-found' => 'A origem de estoque :id não existe.',
                'qty-invalid' => 'Cada quantidade deve ser um número inteiro não negativo.',
                'no-permission' => 'Você não tem permissão para gerenciar os estoques de produtos.',
                'not-found' => 'Produto não encontrado.',
            ],

            'customer-group-price' => [
                'created' => 'Preço por grupo de clientes adicionado com sucesso.',
                'updated' => 'Preço por grupo de clientes atualizado com sucesso.',
                'deleted' => 'Preço por grupo de clientes excluído com sucesso.',
                'qty-required' => 'O campo qty é obrigatório.',
                'qty-invalid' => 'O campo qty deve ser um número inteiro maior ou igual a 1.',
                'value-type-required' => 'O campo value_type é obrigatório.',
                'value-type-invalid' => 'O campo value_type deve ser "fixed" ou "discount".',
                'value-required' => 'O campo value é obrigatório.',
                'value-invalid' => 'O campo value deve ser um número não negativo.',
                'customer-group-not-found' => 'O grupo de clientes selecionado não existe.',
                'duplicate-qty-group' => 'Já existe um preço por grupo de clientes para esta combinação (qty, customer_group_id) neste produto.',
                'not-found' => 'Preço por grupo de clientes não encontrado.',
                'no-permission' => 'Você não tem permissão para gerenciar preços por grupo de clientes dos produtos.',
            ],
        ],

        'category' => [
            'not-found' => 'Categoria não encontrada.',

            'created' => 'Categoria criada com sucesso.',
            'updated' => 'Categoria atualizada com sucesso.',
            'deleted' => 'Categoria excluída com sucesso.',
            'slug-required' => 'O campo slug é obrigatório.',
            'slug-unique' => 'O slug já está em uso.',
            'name-required' => 'O campo name é obrigatório.',
            'position-required' => 'O campo position é obrigatório.',
            'attributes-required' => 'Pelo menos um atributo filtrável é obrigatório.',
            'description-required-for-mode' => 'Uma descrição é obrigatória para o modo de exibição escolhido.',
            'cannot-delete-root' => 'Categorias raiz e categorias raiz de canal não podem ser excluídas.',
            'cannot-delete-channel-root' => 'Esta categoria é a raiz de um canal e não pode ser excluída.',
            'delete-failed' => 'A categoria não pôde ser excluída.',
            'no-permission' => 'Você não tem permissão para gerenciar categorias.',
            'mass-delete-success' => 'Categorias excluídas com sucesso.',
            'mass-update-status-success' => 'Status das categorias atualizado com sucesso.',
            'mass-delete-indices-required' => 'O campo indices é obrigatório e deve ser um array não vazio.',
            'mass-update-status-value-required' => 'O campo value é obrigatório e deve ser 0 ou 1.',
        ],

        'attribute' => [
            'not-found' => 'Atributo não encontrado.',

            'create-success' => 'Atributo criado com sucesso.',
            'update-success' => 'Atributo atualizado com sucesso.',
            'delete-success' => 'Atributo excluído com sucesso.',
            'delete-failed' => 'O atributo não pôde ser excluído.',
            'mass-delete-success' => 'Atributos excluídos com sucesso.',
            'mass-delete-indices-required' => 'O campo indices é obrigatório e deve ser um array não vazio.',

            'code-immutable' => 'O código do atributo não pode ser alterado depois que o atributo é criado.',
            'type-immutable' => 'O tipo do atributo não pode ser alterado enquanto ele tiver valores de produto.',
            'locale-scope-immutable' => 'Não é possível alterar o escopo de idioma enquanto o atributo tiver valores de produto.',
            'system-attribute' => 'Atributos do sistema não podem ser excluídos.',
            'in-use-family' => 'O atributo faz parte de uma ou mais famílias de atributos (IDs dos grupos: :ids). Remova-o dessas famílias primeiro.',

            'option-not-found' => 'Opção de atributo não encontrada.',
            'option-not-supported' => 'O tipo de atributo ":type" não suporta opções. Somente atributos select, multiselect e checkbox podem ter opções.',
            'option-in-use' => 'Esta opção é usada por :count produto(s) e não pode ser excluída.',
            'option-delete-success' => 'Opção de atributo excluída com sucesso.',
        ],

        'family' => [
            'not-found' => 'Família de atributos não encontrada.',

            'created' => 'Família de atributos criada com sucesso.',
            'updated' => 'Família de atributos atualizada com sucesso.',
            'deleted' => 'Família de atributos excluída com sucesso.',
            'code-required' => 'O código da família é obrigatório.',
            'code-unique' => 'O código da família deve ser único.',
            'name-required' => 'O nome da família é obrigatório.',
            'group-code-required' => 'Cada grupo de atributos deve ter um código.',
            'group-name-required' => 'Cada grupo de atributos deve ter um nome.',
            'group-column-invalid' => 'A coluna de cada grupo de atributos deve ser 1 ou 2.',
            'group-name-duplicate' => 'O nome de um grupo de atributos deve ser único dentro da família de atributos.',
            'update-failed' => 'A família de atributos não pôde ser atualizada.',
            'create-failed' => 'A família de atributos não pôde ser criada.',
            'default-delete-error' => 'A família de atributos padrão não pode ser excluída.',
            'last-delete-error' => 'Pelo menos uma família de atributos é obrigatória.',
            'attribute-product-error' => 'Esta família de atributos está em uso por um ou mais produtos. Reatribua esses produtos antes de excluí-la.',
            'delete-failed' => 'A família de atributos não pôde ser excluída.',
            'no-permission' => 'Você não tem permissão para gerenciar famílias de atributos.',
        ],

        'settings' => [
            'locale' => [
                'not-found' => 'Idioma não encontrado.',
                'created' => 'Idioma criado com sucesso.',
                'updated' => 'Idioma atualizado com sucesso.',
                'deleted' => 'Idioma excluído com sucesso.',
                'cannot-delete-last' => 'Pelo menos um idioma é obrigatório — não é possível excluir o último idioma restante.',
                'cannot-delete-channel-default' => 'Este idioma é o idioma padrão de um ou mais canais e não pode ser excluído.',
                'delete-failed' => 'O idioma não pôde ser excluído.',
                'no-permission' => 'Você não tem permissão para gerenciar idiomas.',
                'mass-delete-success' => 'Idiomas excluídos com sucesso.',
                'mass-delete-indices-required' => 'O campo indices é obrigatório e deve ser um array não vazio.',
            ],

            'theme' => [
                'not-found' => 'Personalização de tema não encontrada.',
                'created' => 'Personalização de tema criada com sucesso.',
                'updated' => 'Personalização de tema atualizada com sucesso.',
                'deleted' => 'Personalização de tema excluída com sucesso.',
                'delete-failed' => 'A personalização de tema não pôde ser excluída.',
                'no-permission' => 'Você não tem permissão para gerenciar personalizações de tema.',
                'mass-delete-success' => 'Personalizações de tema excluídas com sucesso.',
                'mass-delete-indices-required' => 'O campo indices é obrigatório e deve ser um array não vazio.',
                'mass-update-success' => 'Personalizações de tema atualizadas com sucesso.',
                'mass-update-indices-required' => 'O campo indices é obrigatório e deve ser um array não vazio.',
                'mass-update-value-invalid' => 'O campo value deve ser 0 ou 1.',
            ],

            'inventory-source' => [
                'not-found' => 'Origem de estoque não encontrada.',
                'created' => 'Origem de estoque criada com sucesso.',
                'updated' => 'Origem de estoque atualizada com sucesso.',
                'deleted' => 'Origem de estoque excluída com sucesso.',
                'code-unique' => 'Já existe uma origem de estoque com este código.',
                'last-delete-error' => 'Pelo menos uma origem de estoque é obrigatória — não é possível excluir a última origem restante.',
                'in-use' => 'Esta origem de estoque é referenciada por estoques de produtos e não pode ser excluída.',
                'delete-failed' => 'A origem de estoque não pôde ser excluída.',
                'no-permission' => 'Você não tem permissão para gerenciar origens de estoque.',
                'mass-delete-success' => 'Origens de estoque excluídas com sucesso.',
                'mass-delete-indices-required' => 'O campo indices é obrigatório e deve ser um array não vazio.',
            ],

            'exchange-rate' => [
                'not-found' => 'Taxa de câmbio não encontrada.',
                'created' => 'Taxa de câmbio criada com sucesso.',
                'updated' => 'Taxa de câmbio atualizada com sucesso.',
                'deleted' => 'Taxa de câmbio excluída com sucesso.',
                'duplicate-pair' => 'Já existe uma taxa de câmbio para esta moeda de destino. Atualize o registro existente ou exclua-o primeiro.',
                'delete-failed' => 'A taxa de câmbio não pôde ser excluída.',
                'no-permission' => 'Você não tem permissão para gerenciar taxas de câmbio.',
                'mass-delete-success' => 'Taxas de câmbio excluídas com sucesso.',
                'mass-delete-indices-required' => 'O campo indices é obrigatório e deve ser um array não vazio.',
                'update-rates-success' => 'Taxas de câmbio atualizadas com sucesso.',
                'update-rates-failed' => 'Não foi possível atualizar as taxas de câmbio a partir do provedor externo.',
                'provider-not-configured' => 'Nenhum provedor externo de taxas de câmbio está configurado.',
            ],

            'tax-rate' => [
                'not-found' => 'Alíquota de imposto não encontrada.',
                'created' => 'Alíquota de imposto criada com sucesso.',
                'updated' => 'Alíquota de imposto atualizada com sucesso.',
                'deleted' => 'Alíquota de imposto excluída com sucesso.',
                'delete-failed' => 'A alíquota de imposto não pôde ser excluída.',
                'no-permission' => 'Você não tem permissão para gerenciar alíquotas de imposto.',
                'zip-code-required' => 'O campo zip_code é obrigatório quando is_zip é false.',
                'zip-range-required' => 'zip_from e zip_to são obrigatórios quando is_zip é true.',
            ],

            'tax-category' => [
                'not-found' => 'Categoria de imposto não encontrada.',
                'created' => 'Categoria de imposto criada com sucesso.',
                'updated' => 'Categoria de imposto atualizada com sucesso.',
                'deleted' => 'Categoria de imposto excluída com sucesso.',
                'code-unique' => 'O código já está em uso.',
                'cannot-delete-in-use' => 'Esta categoria de imposto ainda possui alíquotas vinculadas e não pode ser excluída.',
                'delete-failed' => 'A categoria de imposto não pôde ser excluída.',
                'no-permission' => 'Você não tem permissão para gerenciar categorias de imposto.',
            ],

            'role' => [
                'not-found' => 'Função não encontrada.',
                'created' => 'Função criada com sucesso.',
                'updated' => 'Função atualizada com sucesso.',
                'deleted' => 'Função excluída com sucesso.',
                'permissions-required' => 'O campo permissions é obrigatório quando permission_type é custom.',
                'cannot-delete-in-use' => 'Esta função está atribuída a um ou mais administradores e não pode ser excluída.',
                'cannot-delete-last-role' => 'Pelo menos uma função é obrigatória — não é possível excluir a última função restante.',
                'cannot-change-last-all-access' => 'A única função com acesso total não pode ser alterada para uma função personalizada.',
                'delete-failed' => 'A função não pôde ser excluída.',
                'no-permission' => 'Você não tem permissão para gerenciar funções.',
            ],

            'currency' => [
                'not-found' => 'Moeda não encontrada.',
                'created' => 'Moeda criada com sucesso.',
                'updated' => 'Moeda atualizada com sucesso.',
                'deleted' => 'Moeda excluída com sucesso.',
                'code-required' => 'O campo code é obrigatório.',
                'code-size' => 'O código deve ter exatamente 3 caracteres alfabéticos.',
                'code-unique' => 'O código já está em uso.',
                'name-required' => 'O campo name é obrigatório.',
                'cannot-delete-last' => 'Pelo menos uma moeda é obrigatória — não é possível excluir a última moeda restante.',
                'cannot-delete-channel-base' => 'Esta moeda é a moeda base de um ou mais canais e não pode ser excluída.',
                'delete-failed' => 'A moeda não pôde ser excluída.',
                'no-permission' => 'Você não tem permissão para gerenciar moedas.',

                'mass-delete-success' => 'Moedas excluídas com sucesso.',
                'mass-delete-indices-required' => 'O campo indices é obrigatório e deve ser um array não vazio.',
            ],

            'channel' => [
                'not-found' => 'Canal não encontrado.',

                'created' => 'Canal criado com sucesso.',
                'updated' => 'Canal atualizado com sucesso.',
                'deleted' => 'Canal excluído com sucesso.',
                'code-unique' => 'O código já está em uso.',
                'hostname-unique' => 'O hostname já está em uso.',
                'default-locale-mismatch' => 'O default_locale_id deve constar na lista de idiomas.',
                'base-currency-mismatch' => 'O base_currency_id deve constar na lista de moedas.',
                'unknown-locale' => 'Um ou mais IDs de idioma não existem.',
                'unknown-currency' => 'Um ou mais IDs de moeda não existem.',
                'unknown-inventory-source' => 'Um ou mais IDs de inventory_source não existem.',
                'unknown-root-category' => 'O root_category_id não existe.',
                'cannot-delete-last' => 'Pelo menos um canal é obrigatório — não é possível excluir o último canal restante.',
                'cannot-delete-default' => 'Não é possível excluir o canal padrão da aplicação.',
                'delete-failed' => 'O canal não pôde ser excluído.',
                'no-permission' => 'Você não tem permissão para gerenciar canais.',
            ],

            'user' => [
                'not-found' => 'Usuário administrador não encontrado.',
                'created' => 'Usuário administrador criado com sucesso.',
                'updated' => 'Usuário administrador atualizado com sucesso.',
                'deleted' => 'Usuário administrador excluído com sucesso.',
                'name-required' => 'O campo name é obrigatório.',
                'email-required' => 'O campo email é obrigatório.',
                'email-invalid' => 'O e-mail deve ser um endereço de e-mail válido.',
                'email-unique' => 'O e-mail já está em uso.',
                'password-required' => 'O campo password é obrigatório.',
                'password-min' => 'A senha deve ter pelo menos 6 caracteres.',
                'role-required' => 'O campo role_id é obrigatório.',
                'role-not-found' => 'A função selecionada não existe.',
                'status-invalid' => 'O status deve ser 0 ou 1.',
                'cannot-delete-self' => 'Você não pode excluir sua própria conta de administrador.',
                'cannot-delete-last-admin' => 'Pelo menos um usuário administrador é obrigatório — não é possível excluir o último administrador restante.',
                'cannot-change' => 'O :name do último administrador com acesso total não pode ser alterado.',
                'delete-failed' => 'O usuário administrador não pôde ser excluído.',
                'no-permission' => 'Você não tem permissão para gerenciar usuários administradores.',
                'self-delete-password-required' => 'O campo password é obrigatório para excluir sua própria conta.',
                'self-delete-incorrect-password' => 'A senha está incorreta.',
                'self-deleted' => 'Sua conta de administrador foi excluída.',
            ],

            'data-transfer' => [
                'import' => [
                    'not-found' => 'Importação não encontrada.',
                    'deleted' => 'Importação excluída com sucesso.',
                    'cancelled' => 'Importação cancelada com sucesso.',
                    'cannot-cancel' => 'Somente importações pendentes ou em processamento podem ser canceladas. Estado atual: :state.',
                    'no-permission' => 'Você não tem permissão para gerenciar importações de transferência de dados.',
                    'delete-failed' => 'A importação não pôde ser excluída.',
                    'created' => 'Importação criada com sucesso.',
                    'updated' => 'Importação atualizada com sucesso.',
                    'type-invalid' => 'O tipo selecionado é inválido.',
                    'action-invalid' => 'A ação deve ser uma das seguintes: append, delete.',
                    'validation-strategy-invalid' => 'A estratégia de validação deve ser uma das seguintes: stop-on-errors, skip-errors.',
                    'allowed-errors-invalid' => 'Os erros permitidos devem ser um número inteiro igual ou maior que 0.',
                    'field-separator-required' => 'O separador de campos é obrigatório.',
                    'file-required' => 'Um arquivo é obrigatório.',
                    'file-invalid-type' => 'O arquivo deve ser de um dos seguintes tipos: csv, xls, xlsx, xml.',
                    'nothing-to-import' => 'Não há nada para importar.',
                    'not-valid' => 'A importação não é válida.',
                    'setup-queue-error' => 'A conexão da fila está definida como sync; configure uma fila real para processar importações em segundo plano.',
                    'file-not-found' => 'Nenhum arquivo está disponível para download.',
                    'error-report-not-found' => 'Nenhum relatório de erros está disponível para download.',
                    'sample-not-found' => 'Nenhum arquivo de exemplo está disponível para o tipo e formato solicitados.',
                    'sample-format-invalid' => 'O formato do exemplo deve ser um dos seguintes: csv, xls, xlsx, xml.',
                    'images-archive-too-large' => 'O arquivo compactado de imagens pode conter no máximo :entries arquivos e ocupar no máximo :size MB descompactado.',
                ],
            ],
        ],

        'cms' => [
            'page' => [
                'not-found' => 'Página CMS não encontrada.',

                'created' => 'Página CMS criada com sucesso.',
                'url-key-required' => 'O campo url_key é obrigatório.',
                'url-key-unique' => 'O url_key já está em uso.',
                'url-key-invalid' => 'O formato do url_key é inválido. Use apenas letras minúsculas, números e hifens.',
                'page-title-required' => 'O campo page_title é obrigatório.',
                'html-content-required' => 'O campo html_content é obrigatório.',
                'channels-required' => 'Pelo menos um canal deve ser selecionado.',
                'channels-invalid' => 'Um ou mais canais são inválidos.',

                'updated' => 'Página CMS atualizada com sucesso.',

                'deleted' => 'Página CMS excluída com sucesso.',
                'delete-failed' => 'A página CMS não pôde ser excluída.',

                'mass-deleted' => 'Páginas CMS excluídas com sucesso.',
                'indices-required' => 'O campo indices é obrigatório e deve ser um array não vazio.',
                'indices-invalid' => 'O campo indices deve ser um array de IDs de páginas CMS.',

                'no-permission' => 'Você não tem permissão para gerenciar páginas CMS.',
            ],
        ],

        'marketing' => [
            'catalog-rule' => [
                'not-found' => 'Regra de catálogo não encontrada.',
                'created' => 'Regra de catálogo criada com sucesso.',
                'updated' => 'Regra de catálogo atualizada com sucesso.',
                'deleted' => 'Regra de catálogo excluída com sucesso.',
                'delete-failed' => 'A regra de catálogo não pôde ser excluída.',
                'mass-delete-indices-required' => 'Nenhum ID de regra de catálogo informado para a exclusão em massa.',
                'mass-delete-success' => 'Regras de catálogo selecionadas excluídas com sucesso.',
                'no-permission' => 'Você não tem permissão para gerenciar regras de catálogo.',
            ],

            'campaign' => [
                'not-found' => 'Campanha não encontrada.',
                'created' => 'Campanha criada com sucesso.',
                'updated' => 'Campanha atualizada com sucesso.',
                'deleted' => 'Campanha excluída com sucesso.',
                'delete-failed' => 'A campanha não pôde ser excluída.',
                'no-permission' => 'Você não tem permissão para gerenciar campanhas.',

                'send' => [
                    'id-required' => 'O ID da campanha é obrigatório.',
                    'inactive' => 'Não é possível enviar uma campanha inativa. Ative-a primeiro.',
                    'queued' => 'Campanha colocada na fila para :count destinatário(s).',
                ],
            ],

            'sitemap' => [
                'not-found' => 'Sitemap não encontrado.',
                'created' => 'Sitemap criado com sucesso.',
                'updated' => 'Sitemap atualizado com sucesso.',
                'deleted' => 'Sitemap excluído com sucesso.',
                'delete-failed' => 'O sitemap não pôde ser excluído.',
                'no-permission' => 'Você não tem permissão para gerenciar sitemaps.',

                'generate' => [
                    'id-required' => 'O ID do sitemap é obrigatório.',
                    'no-channels' => 'Este sitemap não abrange nenhum canal, portanto não há nada para gerar. Atribua pelo menos um canal a ele primeiro.',
                    'success' => 'Sitemap gerado novamente com sucesso.',
                    'failed' => 'Falha na geração do sitemap: :message',
                ],
            ],

            'cart-rule' => [
                'not-found' => 'Regra de carrinho não encontrada.',
                'id-required' => 'Um ID de regra de carrinho é obrigatório.',
                'copy-of' => 'Cópia de :value',
                'copied' => 'Regra de carrinho copiada com sucesso.',
                'created' => 'Regra de carrinho criada com sucesso.',
                'updated' => 'Regra de carrinho atualizada com sucesso.',
                'deleted' => 'Regra de carrinho excluída com sucesso.',
                'delete-failed' => 'A regra de carrinho não pôde ser excluída.',
                'no-permission' => 'Você não tem permissão para gerenciar regras de carrinho.',
                'mass-delete-success' => 'Regras de carrinho excluídas com sucesso.',
                'mass-delete-indices-required' => 'O campo indices é obrigatório e deve ser um array não vazio.',
                'action-type-invalid' => 'O action_type deve ser um dos seguintes: by_percent, by_fixed, cart_fixed, buy_x_get_y.',
                'coupon-code-required' => 'Um coupon_code é obrigatório quando coupon_type=1 e use_auto_generation=0.',
                'coupon-code-unique' => 'Este código de cupom já está em uso.',
            ],

            'template' => [
                'campaign-associated' => 'Este modelo de e-mail é usado por uma campanha e, portanto, não pode ser excluído. Remova-o da campanha primeiro.',
                'not-found' => 'Modelo de e-mail não encontrado.',
                'created' => 'Modelo de e-mail criado com sucesso.',
                'updated' => 'Modelo de e-mail atualizado com sucesso.',
                'deleted' => 'Modelo de e-mail excluído com sucesso.',
                'delete-failed' => 'O modelo de e-mail não pôde ser excluído.',
                'status-invalid' => 'O status deve ser um dos seguintes: active, inactive, draft.',
                'no-permission' => 'Você não tem permissão para gerenciar modelos de e-mail.',
            ],

            'event' => [
                'campaign-associated' => 'Este evento é usado por uma campanha e, portanto, não pode ser excluído. Remova-o da campanha primeiro.',
                'not-found' => 'Evento de marketing não encontrado.',
                'created' => 'Evento de marketing criado com sucesso.',
                'updated' => 'Evento de marketing atualizado com sucesso.',
                'deleted' => 'Evento de marketing excluído com sucesso.',
                'delete-failed' => 'O evento de marketing não pôde ser excluído.',
                'no-permission' => 'Você não tem permissão para gerenciar eventos de marketing.',
            ],

            'search-synonym' => [
                'not-found' => 'Sinônimo de pesquisa não encontrado.',
                'created' => 'Sinônimo de pesquisa criado com sucesso.',
                'updated' => 'Sinônimo de pesquisa atualizado com sucesso.',
                'deleted' => 'Sinônimo de pesquisa excluído com sucesso.',
                'mass-deleted' => 'Sinônimos de pesquisa excluídos com sucesso.',
                'delete-failed' => 'O sinônimo de pesquisa não pôde ser excluído.',
                'indices-required' => 'O campo indices é obrigatório e deve ser um array não vazio de IDs de sinônimos de pesquisa.',
                'no-permission' => 'Você não tem permissão para gerenciar sinônimos de pesquisa.',
            ],

            'url-rewrite' => [
                'not-found' => 'Reescrita de URL não encontrada.',
                'created' => 'Reescrita de URL criada com sucesso.',
                'updated' => 'Reescrita de URL atualizada com sucesso.',
                'deleted' => 'Reescrita de URL excluída com sucesso.',
                'delete-failed' => 'A reescrita de URL não pôde ser excluída.',
                'mass-deleted' => 'Reescritas de URL excluídas com sucesso.',
                'indices-required' => 'Pelo menos um ID de reescrita de URL é obrigatório.',
                'no-permission' => 'Você não tem permissão para gerenciar reescritas de URL.',
            ],

            'cart-rule-coupon' => [
                'cart-rule-not-found' => 'Regra de carrinho não encontrada.',
                'not-found' => 'Cupom não encontrado.',
                'code-taken' => 'Já existe um cupom com este código.',
                'format-invalid' => 'O formato deve ser um dos seguintes: alphabetic, alphanumeric, numeric.',
                'indices-required' => 'O campo indices é obrigatório e deve ser um array não vazio de IDs de cupons.',
                'created' => 'Cupom criado com sucesso.',
                'generated' => ':count cupom(ns) gerado(s) com sucesso.',
                'deleted' => 'Cupom excluído com sucesso.',
                'mass-deleted' => ':count cupom(ns) excluído(s) com sucesso.',
                'no-permission' => 'Você não tem permissão para gerenciar cupons de regras de carrinho.',
            ],

            'subscriber' => [
                'not-found' => 'Assinante da newsletter não encontrado.',
                'updated' => 'Inscrição atualizada com sucesso.',
                'deleted' => 'Inscrição excluída com sucesso.',
                'is-subscribed-required' => 'O campo is_subscribed é obrigatório.',
                'no-permission' => 'Você não tem permissão para gerenciar assinantes da newsletter.',
            ],

            'search-term' => [
                'not-found' => 'Termo de pesquisa não encontrado.',
                'updated' => 'Termo de pesquisa atualizado com sucesso.',
                'deleted' => 'Termo de pesquisa excluído com sucesso.',
                'mass-deleted' => 'Termos de pesquisa excluídos com sucesso.',
                'term-required' => 'O campo term é obrigatório.',
                'redirect-url-invalid' => 'O redirect_url deve ser uma URL válida.',
                'indices-required' => 'Pelo menos um ID deve ser informado em indices.',
                'no-permission' => 'Você não tem permissão para gerenciar termos de pesquisa.',
            ],
        ],
        'customer-api-sessions' => [
            'menu' => [
                'title' => 'Sessões de API',
            ],

            'acl' => [
                'title' => 'Sessões de API',
                'delete' => 'Revogar',
            ],

            'index' => [
                'title' => 'Sessões de API dos clientes',
                'info' => 'Clientes conectados à API da loja por aplicativos web, móveis, de PDV ou de desktop. Revogar uma sessão desconecta esse dispositivo na hora.',
            ],

            'fields' => [
                'customer' => 'Cliente',
                'email' => 'E-mail',
                'channel' => 'Canal',
            ],

            'datagrid' => [
                'revoke-all' => 'Revogar todas as sessões do cliente',
            ],

            'card' => [
                'title' => 'Sessões de API (:count)',
                'empty' => 'Este cliente não tem sessões de API ativas.',
            ],

            'errors' => [
                'no-permission' => 'Você não tem permissão para gerenciar as sessões de API dos clientes.',
            ],
        ],

        'customer-auth' => [
            'configuration' => [
                'title' => 'Autenticação de clientes',
                'info' => 'Duração dos tokens, limites e regras de sessão para os logins dos clientes da loja.',

                'tokens' => [
                    'title' => 'Duração dos tokens',
                    'info' => 'Por quanto tempo tokens de acesso, tokens de atualização e sessões permanecem válidos. As alterações valem para tokens emitidos depois.',
                    'access-token-ttl' => 'Duração do token de acesso (minutos)',
                    'access-token-ttl-info' => 'Entre 5 e 60 minutos. Padrão 15.',
                    'refresh-token-ttl' => 'Duração do token de atualização (dias)',
                    'refresh-token-ttl-info' => 'Entre 1 e 90 dias. Padrão 30.',
                    'session-lifetime' => 'Duração máxima da sessão (dias)',
                    'session-lifetime-info' => 'Entre 1 e 365 dias. Padrão 90. Depois disso o administrador precisa entrar novamente, não importa quantas vezes a sessão foi atualizada.',
                    'max-sessions' => 'Máximo de sessões ativas por cliente',
                    'max-sessions-info' => 'Ao atingir o limite, a sessão mais antiga é encerrada. Entre 0 e 100. 0 significa sem limite.',
                ],

                'rate-limits' => [
                    'title' => 'Limites de requisições',
                    'info' => 'Máximo de requisições à API da loja por minuto para cada cliente conectado.',
                    'requests-per-minute' => 'Requisições por minuto por cliente conectado',
                    'requests-per-minute-info' => 'Entre 1 e 100000. Padrão 120.',
                ],

                'login-protection' => [
                    'title' => 'Proteção de login',
                    'info' => 'Quantos logins com falha e renovações de token são permitidos antes que o cliente precise esperar.',
                    'login-max-attempts' => 'Logins com falha antes do bloqueio',
                    'login-max-attempts-info' => 'Por e-mail e endereço IP. Entre 3 e 20. Padrão 5.',
                    'login-lockout-seconds' => 'Bloqueio de login (segundos)',
                    'login-lockout-seconds-info' => 'Entre 60 e 3600. Padrão 60.',
                    'refresh-requests-per-minute' => 'Renovações de token por minuto por sessão',
                    'refresh-requests-per-minute-info' => 'Entre 1 e 60. Padrão 10.',
                ],

                'security' => [
                    'title' => 'Segurança',
                    'info' => 'O que encerra as outras sessões de um cliente.',
                    'revoke-sessions-on-password-change' => 'Revogar sessões ao alterar a senha',
                ],

                'retention' => [
                    'title' => 'Retenção de dados',
                    'info' => 'Por quanto tempo as sessões encerradas dos clientes são mantidas.',
                    'session-prune-days' => 'Manter sessões encerradas (dias)',
                    'session-prune-days-info' => 'Sessões revogadas ou expiradas são excluídas após esse número de dias. Entre 1 e 365. Padrão 30.',
                ],
            ],
        ],
        'auth' => [
            'configuration' => [
                'title' => 'Autenticação de administrador',
                'info' => 'Duração dos tokens, limites de requisições e regras de segurança para logins na API de administração e tokens de API.',

                'tokens' => [
                    'title' => 'Duração dos tokens',
                    'info' => 'Por quanto tempo tokens de acesso, tokens de atualização e sessões permanecem válidos. As alterações valem para tokens emitidos depois.',
                    'access-token-ttl' => 'Duração do token de acesso (minutos)',
                    'access-token-ttl-info' => 'Entre 5 e 60 minutos. Padrão 15.',
                    'refresh-token-ttl' => 'Duração do token de atualização (dias)',
                    'refresh-token-ttl-info' => 'Entre 1 e 90 dias. Padrão 30.',
                    'session-lifetime' => 'Duração máxima da sessão (dias)',
                    'session-lifetime-info' => 'Entre 1 e 365 dias. Padrão 90. Depois disso o administrador precisa entrar novamente, não importa quantas vezes a sessão foi atualizada.',
                ],

                'rate-limits' => [
                    'title' => 'Limites de requisições',
                    'info' => 'Máximo de requisições à API de administração por minuto.',
                    'session-requests-per-minute' => 'Requisições por minuto por administrador conectado',
                    'api-token-requests-per-minute' => 'Requisições por minuto por token de API',
                    'session-requests-per-minute-info' => 'Entre 1 e 100000. Padrão 60.',
                    'api-token-requests-per-minute-info' => 'Entre 1 e 100000. Padrão 60.',
                    'unauthenticated-requests-per-minute' => 'Requisições por minuto sem token, por endereço IP',
                    'unauthenticated-requests-per-minute-info' => 'Entre 10 e 1000. Padrão 60. Inclui as requisições de login, dois fatores, renovação e senha.',
                ],

                'security' => [
                    'title' => 'Segurança',
                    'info' => 'Regras de revogação, expiração dos tokens de API e vínculo de domínio.',
                    'revoke-sessions-on-password-change' => 'Revogar sessões ao alterar a senha',
                    'revoke-api-tokens-on-password-change' => 'Revogar tokens de API ao alterar a senha',
                    'allow-non-expiring-api-tokens' => 'Permitir tokens de API sem expiração',
                    'domain-binding-mode' => 'Modo de vínculo de domínio',
                    'domain-binding-mode-info' => 'DNS verifica se a requisição vem de um endereço IP para o qual o domínio do token aponta. Origin verifica o cabeçalho Origin ou Referer e serve apenas para navegadores.',
                    'domain-binding-dns' => 'DNS (endereço IP do cliente)',
                    'domain-binding-origin' => 'Cabeçalho Origin',
                ],

                'login-protection' => [
                    'title' => 'Proteção de login',
                    'info' => 'Quantos logins com falha, códigos de dois fatores incorretos e requisições de senha são permitidos antes que o cliente precise esperar.',
                    'login-max-attempts' => 'Logins com falha antes do bloqueio',
                    'login-max-attempts-info' => 'Por e-mail e endereço IP. Entre 3 e 20. Padrão 5.',
                    'login-lockout-seconds' => 'Bloqueio de login (segundos)',
                    'login-lockout-seconds-info' => 'Entre 60 e 3600. Padrão 60.',
                    'two-factor-challenge-lifetime' => 'Duração do desafio de dois fatores (minutos)',
                    'two-factor-challenge-lifetime-info' => 'Entre 1 e 15. Padrão 5.',
                    'two-factor-max-failures' => 'Códigos de dois fatores incorretos por minuto',
                    'two-factor-max-failures-info' => 'Por administrador, em todos os desafios. Entre 3 e 20. Padrão 5.',
                    'password-requests-per-minute' => 'Requisições de senha por minuto',
                    'password-requests-per-minute-info' => 'Requisições de esqueci a senha e redefinição por endereço IP. Entre 1 e 20. Padrão 5.',
                ],

                'retention' => [
                    'title' => 'Retenção de dados',
                    'info' => 'Por quanto tempo as sessões encerradas e o histórico de auditoria da API são mantidos.',
                    'session-prune-days' => 'Manter sessões encerradas (dias)',
                    'session-prune-days-info' => 'Sessões revogadas ou expiradas são excluídas após esse número de dias. Entre 1 e 365. Padrão 30.',
                    'audit-retention-days' => 'Manter histórico de auditoria da API (dias)',
                    'audit-retention-days-info' => 'Entradas mais antigas são excluídas diariamente. Entre 0 e 3650. Vazio ou 0 as mantém para sempre.',
                ],
            ],

            'errors' => [
                'unauthenticated' => 'Você não está autenticado.',
                'access-token-expired' => 'O token de acesso expirou. Atualize-o para continuar.',
                'api-token-expired' => 'O token de API expirou.',
                'refresh-token-invalid' => 'O token de atualização é inválido ou expirou. Entre novamente.',
                'api-token-revoked' => 'O token de API foi revogado.',
                'api-token-domain-mismatch' => 'O token de API não pode ser usado a partir deste domínio.',
                'access-token-required' => 'Esta ação precisa do token de acesso de uma sessão de login; tokens de API não podem usá-la.',
                'two-factor-required' => 'Digite o código de 6 dígitos do seu aplicativo autenticador, ou um dos seus códigos de backup, para concluir o login.',
                'too-many-login-attempts' => 'Muitas tentativas de login. Tente novamente em :seconds segundos.',
                'two-factor-challenge-invalid' => 'O desafio de dois fatores é inválido ou expirou. Faça login novamente.',
                'two-factor-attempts-exceeded' => 'Muitos códigos incorretos. Faça login novamente.',
                'password-reset-token-invalid' => 'Este token de redefinição de senha é inválido ou expirou.',
                'too-many-password-requests' => 'Muitas solicitações de senha. Tente novamente em :seconds segundos.',
                'rate-limit-exceeded' => 'Muitas tentativas.',
                'no-permission' => 'Você não tem permissão para gerenciar as sessões da API de administração.',
            ],

            'sessions' => [
                'logged-out' => 'Você saiu da sessão.',
                'logged-out-all' => 'Você saiu de todas as sessões.',
                'not-found' => 'Sessão não encontrada.',
                'revoked' => 'A sessão foi revogada.',
                'revoked-for-admin' => 'Sessões revogadas: :count.',
            ],

            'password' => [
                'reset' => 'Sua senha foi redefinida. Faça login novamente.',
            ],
        ],

        'api-tokens' => [
            'menu' => [
                'title' => 'Tokens de API',
            ],

            'acl' => [
                'title' => 'Tokens de API',
                'create' => 'Criar',
                'delete' => 'Revogar',
                'manage-others' => 'Gerenciar tokens de outros administradores',
            ],

            'index' => [
                'title' => 'Tokens de API',
                'info' => 'Integrações de servidor, como um ERP, PIM, armazém ou sistema contábil, se autenticam com tokens de API. Cada token age como um administrador com as permissões da função dele e só funciona a partir do domínio ao qual está vinculado.',
                'create-btn' => 'Criar token de API',
            ],

            'create' => [
                'title' => 'Criar token de API',
                'back-btn' => 'Voltar',
                'save-btn' => 'Criar token',
                'general' => 'Geral',
                'expiry' => 'Expiração',
                'admin-info' => 'O token age como este administrador e tem exatamente as permissões da função dele.',
                'domain-info' => 'O host de onde a integração faz as chamadas, como erp.example.com. Uma URL é reduzida ao seu host. Endereços IP e curingas não são aceitos, e o domínio não pode ser alterado depois.',
                'expires-on-date' => 'Expira em uma data',
                'never-expires' => 'Nunca expira',
                'no-expiry-warning' => 'Um token sem expiração funciona até ser revogado. Guarde-o em um cofre de segredos e revogue-o assim que não for mais necessário.',
                'no-expiry-confirm' => 'Entendo que este token nunca expira.',
                'no-admins' => 'Não há nenhum administrador para quem você possa criar um token de API.',
            ],

            'view' => [
                'title' => 'Token de API',
                'back-btn' => 'Voltar',
                'save-btn' => 'Salvar',
                'revoke-btn' => 'Revogar',
                'details' => 'Detalhes',
                'usage' => 'Uso',
                'token-once-title' => 'Copie seu token de API agora',
                'token-once-info' => 'Esta é a única vez que o token é exibido. Guarde-o em um cofre de segredos; se ele for perdido, revogue-o e crie um novo.',
                'copy-btn' => 'Copiar',
                'copied' => 'O token foi copiado.',
                'usage-info' => 'Envie-o como token Bearer no cabeçalho Authorization das requisições REST e GraphQL.',
                'not-yet' => 'Ainda não',
                'revoke-confirm' => 'Revogar este token de API? A integração que o usa para de funcionar imediatamente.',
            ],

            'fields' => [
                'id' => 'ID',
                'name' => 'Nome',
                'admin' => 'Administrador',
                'token' => 'Token',
                'domain' => 'Domínio',
                'expiry' => 'Expiração',
                'expires-at' => 'Expira em',
                'no-expiry-confirmation' => 'Confirmação sem expiração',
                'status' => 'Status',
                'created-by' => 'Criado por',
                'created-at' => 'Criado em',
                'last-used-at' => 'Último uso em',
                'last-ip-address' => 'Último endereço IP',
                'last-user-agent' => 'Último agente de usuário',
                'last-channel' => 'Último canal',
                'resolved-ips' => 'Endereços IP resolvidos',
                'resolved-at' => 'Resolvido em',
                'domain-mismatches' => 'Requisições de outros domínios',
                'last-domain-mismatch-at' => 'Última requisição de outro domínio',
                'revoked-at' => 'Revogado em',
                'revoked-by' => 'Revogado por',
            ],

            'datagrid' => [
                'never' => 'Nunca',
                'console' => 'Comando de console',
                'view' => 'Ver',
                'revoke' => 'Revogar',
            ],

            'status' => [
                'active' => 'Ativo',
                'expired' => 'Expirado',
                'revoked' => 'Revogado',
            ],

            'channels' => [
                'rest' => 'REST',
                'graphql' => 'GraphQL',
            ],

            'messages' => [
                'created' => 'O token de API foi criado.',
                'updated' => 'O token de API foi atualizado.',
                'revoked' => 'O token de API foi revogado.',
            ],

            'errors' => [
                'no-permission' => 'Você não tem permissão para gerenciar tokens de API.',
                'not-found' => 'Token de API não encontrado.',
                'cannot-create-for-admin' => 'Você não pode criar um token de API para este administrador: a função dele tem permissões que a sua não tem, ou você não pode gerenciar os tokens de outros administradores.',
                'cannot-manage' => 'Você não pode gerenciar este token de API.',
                'already-revoked' => 'O token de API já foi revogado.',
                'invalid-name' => 'O nome é obrigatório e pode ter no máximo 100 caracteres.',
                'invalid-domain' => 'Informe um nome de domínio como erp.example.com. Endereços IP e curingas não são aceitos.',
                'expiry-required' => 'Os tokens de API devem ter uma data de expiração.',
                'expiry-in-past' => 'A data de expiração deve estar no futuro.',
            ],
        ],

        'api-sessions' => [
            'menu' => [
                'title' => 'Sessões de API',
            ],

            'acl' => [
                'title' => 'Sessões de API',
                'delete' => 'Revogar',
            ],

            'index' => [
                'title' => 'Sessões de API',
                'info' => 'Administradores conectados à API de administração por aplicativos web, móveis, de PDV ou de desktop. Revogar uma sessão desconecta esse dispositivo imediatamente.',
            ],

            'fields' => [
                'id' => 'ID',
                'admin' => 'Administrador',
                'device' => 'Dispositivo',
                'client-type' => 'Tipo de cliente',
                'ip-address' => 'IP de login',
                'user-agent' => 'Agente de usuário de login',
                'last-ip-address' => 'Último endereço IP',
                'last-user-agent' => 'Último agente de usuário',
                'last-used-at' => 'Último uso em',
                'last-channel' => 'Último canal',
                'refresh-expires-at' => 'Atualização expira em',
                'expires-at' => 'Expira em',
                'status' => 'Status',
                'created-at' => 'Conectado em',
            ],

            'datagrid' => [
                'revoke' => 'Revogar',
                'revoke-all' => 'Revogar todas as sessões deste administrador',
            ],

            'client-types' => [
                'web' => 'Web',
                'mobile' => 'Móvel',
                'pos' => 'PDV',
                'desktop' => 'Desktop',
                'other' => 'Outro',
            ],

            'errors' => [
                'not-active' => 'Esta sessão já foi revogada ou expirou.',
            ],
        ],

        'api-audit' => [
            'menu' => [
                'title' => 'Auditoria da API',
            ],

            'acl' => [
                'title' => 'Histórico de alterações da API',
                'delete' => 'Excluir histórico',
            ],

            'index' => [
                'title' => 'Histórico de alterações da API',
                'info' => 'Cada criação, atualização e exclusão feita por meio da API admin, com quem fez isso, qual token e o que mudou.',
                'cleanup-btn' => 'Excluir registros mais antigos',
                'cleanup-days' => 'Excluir registros anteriores a esse número de dias',
                'cleanup-confirm' => 'Excluir todo o histórico anterior ao número de dias determinado? Isto não pode ser desfeito.',
            ],

            'view' => [
                'title' => 'Mudança',
                'back-btn' => 'Voltar',
                'admin' => 'Administrador',
                'token' => 'Símbolo',
                'action' => 'Ação',
                'resource' => 'Recurso',
                'method' => 'Método',
                'ip' => 'Endereço IP',
                'date' => 'Data',
                'version' => 'Versão',
                'url' => 'Ponto final',
                'request-details' => 'Detalhes da solicitação',
                'changes' => 'Mudanças',
                'field' => 'Campo',
                'old' => 'Valor antigo',
                'new' => 'Novo valor',
                'no-field-changes' => 'Nenhuma alteração em nível de campo foi registrada para esta entrada.',
                'same-request' => 'Outras alterações na mesma solicitação',
                'version-chain' => 'Histórico de versões deste registro',
            ],

            'datagrid' => [
                'id' => 'ID',
                'date' => 'Data',
                'admin' => 'Administrador',
                'token' => 'Símbolo',
                'token-kind' => 'Tipo de token',
                'channel' => 'Canal',
                'operation' => 'Operação',
                'resource' => 'Recurso',
                'method' => 'Método',
                'ip' => 'PI',
                'view' => 'Ver',
                'delete' => 'Excluir',
            ],

            'events' => [
                'created' => 'Criado',
                'updated' => 'Atualizado',
                'deleted' => 'Excluído',
            ],

            'token-kinds' => [
                'session' => 'Sessão de login',
                'api-token' => 'Token de API',
                'integration' => 'Token de integração (removido)',
                'customer-session' => 'Sessão de login do cliente',
            ],

            'channels' => [
                'rest' => 'REST',
                'graphql' => 'GraphQL',
                'panel' => 'Painel administrativo',
                'cli' => 'Comando de console',
            ],

            'deleted' => ':count registros de histórico excluídos.',
            'cleanup-input-required' => 'Forneça um número de dias ou uma data para a limpeza.',
        ],

        'api-configuration' => [
            'title' => 'API',
            'info' => 'Configurações para a API Bagisto e seus módulos de administração.',
        ],
    ],
];
