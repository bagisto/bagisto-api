<?php

return [
    'graphql' => [
        'cart' => [
            'authentication-required'           => 'É necessário um token de autenticação',
            'invalid-token'                     => 'Token de autenticação inválido ou expirado',
            'unauthorized-access'               => 'Acesso não autorizado ao carrinho',
            'authenticated-only'                => 'Apenas usuários autenticados podem acessar seus carrinhos',
            'merge-requires-auth'               => 'A mesclagem de convidado requer autenticação',
            'unknown-operation'                 => 'Operação de carrinho desconhecida',

            'cart-not-found'                    => 'Carrinho não encontrado',
            'guest-cart-not-found'              => 'Carrinho de convidado não encontrado',
            'product-not-found'                 => 'Produto não encontrado',

            'product-id-quantity-required'      => 'O ID do produto e a quantidade são obrigatórios',
            'cart-item-id-quantity-required'    => 'O ID do item do carrinho e a quantidade são obrigatórios',
            'cart-item-id-required'             => 'O ID do item do carrinho é obrigatório',
            'item-ids-required'                 => 'A lista de IDs de itens é obrigatória',
            'coupon-code-required'              => 'O código do cupom é obrigatório',
            'address-data-required'             => 'País, estado e CEP são obrigatórios',

            'add-product-failed'                => 'Falha ao adicionar o produto ao carrinho',
            'update-item-failed'                => 'Falha ao atualizar o item do carrinho',
            'remove-item-failed'                => 'Falha ao remover o item do carrinho',
            'apply-coupon-failed'               => 'Falha ao aplicar o cupom',
            'remove-coupon-failed'              => 'Falha ao remover o cupom',
            'move-to-wishlist-failed'           => 'Falha ao mover o item para a lista de desejos',
            'estimate-shipping-failed'          => 'Falha ao estimar o frete',

            'product-added-successfully'         => 'Produto adicionado ao carrinho com sucesso',
            'guest-cart-merged'                  => 'Carrinho de convidado mesclado com sucesso',
            'using-authenticated-cart'           => 'Usando o carrinho do cliente autenticado',
            'cart-item-not-found'                => 'Item do carrinho não encontrado',
            'new-guest-cart-created'             => 'Novo carrinho de convidado criado com token de sessão exclusivo',
            'select-items-to-remove'             => 'Selecione os itens para remover',
            'select-items-to-move-wishlist'      => 'Selecione os itens para mover para a lista de desejos',
            'invalid-or-expired-token'           => 'O token do carrinho é inválido ou expirou. Crie um novo carrinho.',
            'invalid-token-of-login-user'        => 'O token do usuário conectado é inválido.',
        ],

        'token-verification' => [
            'invalid-operation'                 => 'Operação inválida',
            'invalid-input-data'                => 'Dados de entrada inválidos',
            'token-required'                    => 'O token é obrigatório',
            'invalid-token-format'              => 'Formato de token inválido',
            'token-not-found-or-expired'        => 'Token não encontrado ou expirado',
            'customer-not-found'                => 'Cliente não encontrado',
            'customer-account-suspended'        => 'A conta do cliente está suspensa',
            'error-verifying-token'             => 'Erro ao verificar o token',
            'token-is-valid'                    => 'O token é válido',
        ],

        'forgot-password' => [
            'invalid-operation'                 => 'Operação inválida',
            'invalid-input-data'                => 'Dados de entrada inválidos',
            'email-required'                    => 'O e-mail é obrigatório',
            'reset-link-sent'                   => 'Link de redefinição enviado com sucesso para o seu e-mail',
            'email-not-found'                   => 'Endereço de e-mail não encontrado',
            'error-sending-reset-link'          => 'Ocorreu um erro ao enviar o link de redefinição',
        ],

        'logout' => [
            'invalid-operation'                 => 'Operação inválida',
            'invalid-input-data'                => 'Dados de entrada inválidos',
            'token-required'                    => 'O token é obrigatório',
            'invalid-token-format'              => 'Formato de token inválido',
            'logged-out-successfully'           => 'Sessão encerrada com sucesso',
            'token-not-found-or-expired'        => 'Token não encontrado ou já expirado',
            'error-during-logout'               => 'Erro ao encerrar a sessão',
        ],

        'address' => [
            'deleted-successfully'              => 'Endereço excluído com sucesso',
            'authentication-required'           => 'É necessário um token de autenticação',
            'invalid-token'                     => 'Token inválido ou expirado',
            'unknown-operation'                 => 'Operação desconhecida',
            'address-id-required'               => 'O ID do endereço é obrigatório',
            'address-not-found'                 => 'Endereço não encontrado ou não pertence a este cliente',
            'retrieved'                         => 'Endereços recuperados com sucesso',
            'fetch-failed'                      => 'Falha ao buscar os endereços:',
        ],

        'customer-profile' => [
            'authentication-required'           => 'É necessário um token de autenticação. Forneça o token na entrada da consulta',
            'invalid-token'                     => 'Token inválido ou expirado',
        ],

        'customer' => [
            'password-mismatch'                 => 'A senha e a confirmação de senha não coincidem',
            'confirm-password-required'         => 'A confirmação de senha é obrigatória ao alterar a senha',
            'unauthenticated'                   => 'Não autenticado. Faça login para realizar esta ação',
        ],

        'product-review' => [
            'product-id-required'               => 'O ID do produto é obrigatório',
            'product-not-found'                 => 'Produto não encontrado',
            'rating-invalid'                    => 'A avaliação deve estar entre 1 e 5',
            'title-required'                    => 'O título da avaliação é obrigatório',
            'comment-required'                  => 'O comentário da avaliação é obrigatório',
        ],

        'product' => [
            'not-found-with-sku'                => 'No product found with SKU',
            'not-found-with-url-key'            => 'No product found with URL key',
            'parameters-required'               => 'At least one of the following parameters must be provided: "sku", "id", "urlKey"',
        ],

        'auth' => [
            'no-token-provided'                 => 'Nenhum token de autenticação fornecido. Forneça o token no cabeçalho Authorization como "Bearer <token>" ou no campo input.token',
            'invalid-or-expired-token'          => 'Token inválido ou expirado',
            'request-not-found'                 => 'Solicitação não encontrada no contexto',
            'token-required'                    => 'Authentication token is required. Please provide the token either in the GraphQL mutation input field or in the Authorization header as "Bearer <token>"',
            'unknown-resource'                  => 'Recurso desconhecido',
            'cannot-update-other-profile'       => 'Não autorizado: não é possível atualizar o perfil de outro cliente',
        ],

        'upload' => [
            'invalid-base64'                    => 'Dados de imagem codificados em base64 inválidos',
            'size-exceeds-limit'                => 'O tamanho da imagem não deve exceder 5 MB',
            'invalid-format'                    => 'Formato de imagem inválido. Forneça a imagem codificada em base64 com esquema de URI de dados (data:image/jpeg;base64,...)',
            'failed'                            => 'Falha no envio da imagem',
        ],

        'attribute' => [
            'code-already-exists'               => 'O código do atributo já existe',
        ],

        'login' => [
            'invalid-credentials'               => 'E-mail ou senha inválidos',
            'account-suspended'                 => 'Sua conta foi suspensa',
            'successful'                        => 'Você fez login com sucesso',
            'invalid-request'                   => 'Solicitação de login inválida',
        ],

        'checkout' => [
            'invalid-input'                     => 'Dados de entrada inválidos para a operação de checkout',
            'billing-address-required'          => 'O endereço de cobrança é obrigatório',
            'shipping-address-required'         => 'O endereço de entrega é obrigatório para envios',
            'address-save-failed'               => 'Falha ao salvar o endereço',
            'address-saved'                     => 'Endereço salvo com sucesso',
            'shipping-method-required'          => 'O método de envio é obrigatório',
            'invalid-shipping-method'           => 'Método de envio inválido ou indisponível',
            'shipping-method-save-failed'       => 'Falha ao salvar o método de envio',
            'shipping-method-saved'             => 'Método de envio salvo com sucesso',
            'shipping-method-error'             => 'Erro ao salvar o método de envio',
            'payment-method-required'           => 'O método de pagamento é obrigatório',
            'invalid-payment-method'            => 'Método de pagamento inválido ou indisponível',
            'payment-method-save-failed'        => 'Falha ao salvar o método de pagamento',
            'payment-method-saved'              => 'Método de pagamento salvo com sucesso',
            'payment-method-error'              => 'Erro ao salvar o método de pagamento',
            'order-creation-failed'             => 'Falha na criação do pedido: o ID do pedido é nulo ou o pedido não foi persistido',
            'order-retrieval-failed'            => 'Falha ao recuperar o pedido criado',
            'order-creation-error'              => 'Falha ao criar o pedido',
            'cart-empty'                        => 'O carrinho está vazio',
            'account-suspended'                 => 'Sua conta foi suspensa. Entre em contato com o suporte.',
            'account-inactive'                  => 'Sua conta está inativa. Entre em contato com o suporte.',
            'minimum-order-not-met'             => 'O valor mínimo do pedido é :amount',
            'email-required'                    => 'O endereço de e-mail é obrigatório para a criação do pedido',
            'unknown-operation'                 => 'Operação de checkout desconhecida',
        ],

        'customer-addresses' => [
            'token-required'                    => 'É necessário um token para buscar os endereços do cliente',
            'invalid-or-expired-token'          => 'Token inválido ou expirado',
            'token-validation-failed'           => 'Falha na validação do token',
        ],

        'product' => [
            'type'                              => 'Tipo de produto',
            'attribute-family'                  => 'Família de atributos',
            'sku'                               => 'SKU',
            'name'                              => 'Nome',
            'description'                       => 'Descrição',
            'short-description'                 => 'Descrição curta',
            'status'                            => 'Status',
            'new'                               => 'Novo',
            'featured'                          => 'Destaque',
            'price'                             => 'Preço',
            'special-price'                     => 'Preço especial',
            'weight'                            => 'Peso',
            'cost'                              => 'Custo',
            'length'                            => 'Comprimento',
            'width'                             => 'Largura',
            'height'                            => 'Altura',
            'color'                             => 'Cor',
            'size'                              => 'Tamanho',
            'brand'                             => 'Marca',
            'super-attributes'                  => 'Superatributos',
        ],

        'compare-item' => [
            'id-required'                       => 'ID do item de comparação é obrigatório',
            'invalid-id-format'                 => 'Formato de ID inválido. Esperado formato IRI como "/api/shop/compare-items/1" ou ID numérico',
            'not-found'                         => 'Item de comparação não encontrado',
            'product-id-required'               => 'ID do produto é obrigatório',
            'customer-id-required'              => 'ID do cliente é obrigatório',
            'product-not-found'                 => 'Produto não encontrado',
            'customer-not-found'                => 'Cliente não encontrado',
            'already-exists'                    => 'Este produto já está em sua lista de comparação',
        ],

        'downloadable-product' => [
            'download-link-not-found'           => 'Link de download não encontrado ou expirado',
            'purchased-link-not-found'          => 'Link de compra não encontrado',
            'file-not-found'                    => 'Arquivo não encontrado',
            'download-successful'               => 'Arquivo pronto para download',
            'token-required'                    => 'Token de download é obrigatório',
            'invalid-token'                     => 'Token de download inválido ou expirado',
            'token-expired'                     => 'O token de download expirou. Por favor, gere um novo',
            'access-denied'                     => 'Acesso negado: Você não tem permissão para baixar este arquivo',
            'redirect-external-url'             => 'Redirecionando para URL de download externo',
            'file-error'                        => 'Ocorreu um erro ao processar sua solicitação de download',
            'unauthorized-access'               => 'Acesso não autorizado ao recurso de download',
        ],
    ],

    'integration' => [
        'menu' => [
            'title'  => 'Integração',
            'tokens' => 'Fichas',
        ],

        'history' => [
            'menu' => [
                'title' => 'História',
            ],

            'acl' => [
                'title'  => 'Histórico de alterações da API',
                'delete' => 'Excluir histórico',
            ],

            'index' => [
                'title'           => 'Histórico de alterações da API',
                'info'            => 'Cada criação, atualização e exclusão feita por meio da API admin, com quem fez isso, qual token e o que mudou.',
                'cleanup-btn'     => 'Excluir registros mais antigos',
                'cleanup-days'    => 'Excluir registros anteriores a esse número de dias',
                'cleanup-confirm' => 'Excluir todo o histórico anterior ao número de dias determinado? Isto não pode ser desfeito.',
            ],

            'view' => [
                'title'             => 'Mudança',
                'back-btn'          => 'Voltar',
                'admin'             => 'Administrador',
                'token'             => 'Símbolo',
                'action'            => 'Ação',
                'resource'          => 'Recurso',
                'method'            => 'Método',
                'ip'                => 'Endereço IP',
                'date'              => 'Data',
                'version'           => 'Versão',
                'url'               => 'Ponto final',
                'request-details'   => 'Detalhes da solicitação',
                'changes'           => 'Mudanças',
                'field'             => 'Campo',
                'old'               => 'Valor antigo',
                'new'               => 'Novo valor',
                'no-field-changes'  => 'Nenhuma alteração em nível de campo foi registrada para esta entrada.',
                'same-request'      => 'Outras alterações na mesma solicitação',
                'version-chain'     => 'Histórico de versões deste registro',
            ],

            'datagrid' => [
                'id'        => 'ID',
                'date'      => 'Data',
                'admin'     => 'Administrador',
                'token'     => 'Símbolo',
                'action'    => 'Ação',
                'operation' => 'Operação',
                'resource'  => 'Recurso',
                'version'   => 'Versão',
                'method'    => 'Método',
                'ip'        => 'PI',
                'view'      => 'Ver',
                'delete'    => 'Excluir',
            ],

            'events' => [
                'created' => 'Criado',
                'updated' => 'Atualizado',
                'deleted' => 'Excluído',
            ],

            'deleted'                => ':count registros de histórico excluídos.',
            'cleanup-input-required' => 'Forneça um número de dias ou uma data para a limpeza.',
        ],

        'acl' => [
            'title'      => 'Integração',
            'create'     => 'Criar Integração',
            'edit'       => 'Editar integração',
            'delete'     => 'Revogar token de integração',
            'generate'   => 'Gerar token de integração',
            'regenerate' => 'Regenerar token de integração',
        ],

        'index' => [
            'title'      => 'Integrações',
            'create-btn' => 'Criar Integração',
        ],

        'create' => [
            'title'    => 'Criar Integração',
            'save-btn' => 'Salvar',
            'back-btn' => 'Voltar',
        ],

        'edit' => [
            'title'          => 'Editar integração',
            'save-btn'       => 'Salvar',
            'back-btn'       => 'Voltar',
            'generate-btn'   => 'Gerar token',
            'regenerate-btn' => 'Regenerar token',
            'revoke-btn'     => 'Revogar token',
            'copy-btn'       => 'Copiar',
            'token-warning'  => 'Salve este token agora – ele não será mostrado novamente.',
            'token-label'    => 'Símbolo',
            'not-generated'  => 'Ainda não gerado',
            'masked'         => '(Armazenado — mostrado apenas uma vez na geração)',
            'history-banner' => 'Este token não está mais ativo.',
        ],

        'fields' => [
            'name'                  => 'Nome',
            'description'           => 'Descrição',
            'assign-user'           => 'Atribuir usuário',
            'permission-type'       => 'Tipo de permissão',
            'access-control'        => 'Controle de acesso',
            'general'               => 'Geral',
            'token-settings'        => 'Configurações de token',
            'valid-till'            => 'Válido até',
            'rate-limit-per-minute' => 'Limite de taxa (por minuto)',
            'rate-limit-per-day'    => 'Limite de tarifa (por dia)',
            'never-expires'         => 'Nunca expira',
            'expires-on'            => 'Expira em',
            'unlimited'             => 'Ilimitado',
            'limit-to'              => 'Limitar a',
            'requests-per-minute'   => 'solicitações/minuto',
            'requests-per-day'      => 'solicitações / dia',
            'select-admin'          => 'Selecione um administrador',
            'no-available-admins'   => 'Nenhum administrador disponível – cada administrador já possui um token ativo.',
            'same-as-web-hint'      => 'O token espelhará as permissões de função atuais do administrador atribuído ao vivo.',
            'ip-allowlist'          => 'Lista de permissões de IP',
            'ip-any'                => 'Qualquer IP (padrão)',
            'ip-restricted'         => 'Restrito a IPs específicos',
            'ip-list-hint'          => 'Uma entrada por linha. Suporta IPv4, IPv6 e CIDR (por exemplo, 10.0.0.0/24 ou 2001:db8::/32). Deixe em branco para permitir todos os IPs.',
        ],

        'permission_type' => [
            'all'         => 'Todos',
            'custom'      => 'Personalizado',
            'same_as_web' => 'O mesmo que permissão da Web',
        ],

        'status' => [
            'draft'       => 'Rascunho',
            'active'      => 'Ativo',
            'revoked'     => 'Revogado',
            'regenerated' => 'Regenerado',
        ],

        'datagrid' => [
            'id'              => 'ID',
            'name'            => 'Nome',
            'admin'           => 'Administrador',
            'token'           => 'Símbolo',
            'status'          => 'Estado',
            'permission-type' => 'Tipo de permissão',
            'expires-at'      => 'Válido até',
            'last-used-at'    => 'Último uso',
            'created-at'      => 'Criado em',
            'edit'            => 'Editar',
            'revoke'          => 'Revogar',
        ],

        'messages' => [
            'draft-created'          => 'Integração criada. Gere o token para começar a usá-lo.',
            'updated'                => 'Integração atualizada com sucesso.',
            'generated'              => 'Token gerado. Copie agora – ele não será mostrado novamente.',
            'regenerated'            => 'Token regenerado. Copie o novo token agora – ele não será mostrado novamente.',
            'revoked'                => 'Token revogado com sucesso.',
            'generate-only-draft'    => 'Apenas rascunhos de integrações podem ter seu token gerado.',
            'regenerate-only-active' => 'Apenas tokens ativos podem ser regenerados.',
            'cannot-edit-historic'   => 'Os tokens revogados ou regenerados não podem ser editados.',
            'already-inactive'       => 'Este token já está inativo.',
        ],

        'errors' => [
            'admin-has-token' => 'O administrador selecionado já possui um token de integração ativo.',
        ],

        'validation' => [
            'ip-invalid'           => 'Cada IP permitido deve ser um endereço IPv4 ou IPv6 válido (notação CIDR suportada).',
            'cidr-prefix-invalid'  => 'O prefixo CIDR é inválido para a versão IP fornecida.',
        ],

        'configuration' => [
            'api' => [
                'title' => 'API',
                'info'  => 'Configurações para a API Bagisto e seus módulos de administração.',
            ],
            'integration' => [
                'title' => 'Integração',
                'info'  => 'Gerencie o plug-in de integração de API usado para emitir tokens de API de administrador.',
            ],
            'settings' => [
                'title'  => 'Configurações do módulo',
                'info'   => 'Habilite ou desabilite o plugin de integração de API. Quando desativado, o menu da barra lateral fica oculto e suas páginas retornam 404.',
                'enable' => 'Habilitar módulo de integração de API',
            ],
        ],

        'emails' => [
            'generated' => [
                'subject'  => 'Um novo token de API foi gerado: :name',
                'greeting' => 'Um token de integração de API chamado ":name" acabou de ser gerado em sua conta.',
            ],
            'regenerated' => [
                'subject'  => 'Seu token de API foi regenerado: :name',
                'greeting' => 'O token de integração da API denominado ":name" acabou de ser regenerado. O token anterior parou de funcionar — apenas o novo é válido.',
            ],
            'revoked' => [
                'subject'  => 'Seu token de API foi revogado: :name',
                'greeting' => 'O token de integração da API denominado ":name" foi revogado. Qualquer cliente que o utilize perdeu o acesso.',
            ],

            'details' => [
                'name' => 'Nome do token',
                'date' => 'Data',
                'ip'   => 'Do IP',
            ],

            'revoke-hint'   => 'Se você não esperava por isso, revogue o token imediatamente usando o botão abaixo.',
            'revoke-btn'    => 'Revogar este token',
            'revoke-expiry' => 'Este link de revogação é válido por 7 dias. Depois disso, faça login no painel de administração para gerenciar o token.',
            'no-action'     => 'Nenhuma ação é necessária – este e-mail é apenas uma confirmação.',
        ],

        'revoke-confirmation' => [
            'title'                    => 'Revogar token de API',
            'success-title'            => 'Token revogado',
            'success-message'          => 'O token ":name" foi revogado. Qualquer cliente que o utilize perdeu o acesso imediatamente.',
            'already-inactive-title'   => 'Token já inativo',
            'already-inactive-message' => 'O token ":name" já foi revogado ou regenerado. Nenhuma ação adicional é necessária.',
        ],

        'confirm' => [
            'generate' => [
                'title'   => 'Gerar token',
                'message' => 'Gerar o token agora? O texto simples será mostrado apenas uma vez – copie-o antes de sair da página.',
            ],
            'regenerate' => [
                'title'   => 'Regenerar token',
                'message' => 'Regenerar o token? O token antigo irá parar de funcionar imediatamente e o novo texto simples será mostrado apenas uma vez.',
            ],
            'revoke' => [
                'title'   => 'Revogar token',
                'message' => 'Revogar este token? Qualquer cliente que o utilize perderá o acesso imediatamente. Esta ação não pode ser desfeita.',
            ],
        ],
    ],
];
