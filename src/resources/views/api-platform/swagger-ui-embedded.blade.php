<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>{{ $title }} - API Platform</title>
        <link rel="stylesheet" href="{{ asset('vendor/api-platform/fonts/open-sans/400.css') }}">
        <link rel="stylesheet" href="{{ asset('vendor/api-platform/fonts/open-sans/700.css') }}">
        <link rel="stylesheet" href="{{ asset('vendor/api-platform/swagger-ui/swagger-ui.css') }}">
        <link rel="stylesheet" href="{{ asset('vendor/api-platform/style.css') }}">
        <style>
            body { margin: 0; }
            
            .api-platform-header-bar {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                padding: 15px 20px;
                position: sticky;
                top: 0;
                z-index: 1000;
                display: flex;
                align-items: center;
                justify-content: space-between;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .header-info {
                display: flex;
                align-items: center;
                gap: 15px;
                flex: 1;
                color: white;
            }

            .header-info h1 {
                margin: 0;
                font-size: 18px;
                font-weight: 700;
            }

            .header-info p {
                margin: 0;
                font-size: 12px;
                opacity: 0.9;
            }

            .back-to-docs {
                color: white;
                text-decoration: none;
                font-size: 13px;
                padding: 6px 12px;
                background: rgba(255, 255, 255, 0.2);
                border-radius: 3px;
                transition: all 0.2s;
            }

            .back-to-docs:hover {
                background: rgba(255, 255, 255, 0.3);
            }

            .storefront-key-notice {
                background: #f0f4ff;
                border-left: 4px solid #667eea;
                padding: 12px 15px;
                margin: 0 20px 10px 20px;
                font-size: 13px;
                color: #333;
                border-radius: 2px;
            }

            .storefront-key-notice strong {
                color: #667eea;
            }
        </style>
        <script id="swagger-data" type="application/json">{!! Illuminate\Support\Js::encode($specData ?? []) !!}</script>
    </head>
    <body>
        <div class="api-platform-header-bar">
            <div class="header-info">
                <div>
                    <h1>{{ $title }}</h1>
                    <p>{{ $description }}</p>
                </div>
            </div>
            <a href="/api" class="back-to-docs">← Back to Docs</a>
        </div>

        @if(isset($endpoint) && $endpoint === 'shop')
        <div class="storefront-key-notice">
            <strong>🔐 Authentication:</strong> This API requires the <strong>X-STOREFRONT-KEY</strong> header.
            @if(env('API_PLAYGROUND_AUTO_INJECT_STOREFRONT_KEY', false))
            The key is automatically included in requests from this documentation page.
            @else
            You can manually enter your key in the Authorize button above.
            @endif
        </div>
        @endif

        <div id="swagger-ui" class="api-platform"></div>
        <script src="{{ asset('vendor/api-platform/swagger-ui/swagger-ui-bundle.js') }}"></script>
        <script src="{{ asset('vendor/api-platform/swagger-ui/swagger-ui-standalone-preset.js') }}"></script>
        <script src="{{ asset('vendor/api-platform/init-swagger-ui.js') }}"></script>
        <script>
            window.onload = function() {
                
                const specDataElement = document.getElementById('swagger-data');
                const specData = specDataElement ? JSON.parse(specDataElement.textContent) : {};

                
                @if(isset($endpoint) && $endpoint === 'shop' && !env('API_PLAYGROUND_AUTO_INJECT_STOREFRONT_KEY', false))
                
                Object.keys(sessionStorage).forEach(key => {
                    if (key.includes('swagger') || key.includes('auth') || key.includes('X-STOREFRONT')) {
                        sessionStorage.removeItem(key);
                    }
                });
                
                Object.keys(localStorage).forEach(key => {
                    if (key.includes('swagger') || key.includes('auth') || key.includes('X-STOREFRONT')) {
                        localStorage.removeItem(key);
                    }
                });
                @endif

                const config = {
                    spec: specData,
                    dom_id: '#swagger-ui',
                    validatorUrl: null,
                    persistAuthorization: @if(isset($endpoint) && $endpoint === 'shop' && env('API_PLAYGROUND_AUTO_INJECT_STOREFRONT_KEY', false)) true @else false @endif,
                    presets: [
                        SwaggerUIBundle.presets.apis,
                        SwaggerUIStandalonePreset
                    ],
                    plugins: [
                        SwaggerUIBundle.plugins.DownloadUrl
                    ],
                    layout: "StandaloneLayout",
                    docExpansion: "list",
                    filter: true,
                    showRequestHeaders: true,
                    supportedSubmitMethods: ['get', 'post', 'put', 'delete', 'patch', 'head', 'options'],
                    requestInterceptor: function(request) {
                        @if(isset($endpoint) && $endpoint === 'shop' && env('API_PLAYGROUND_AUTO_INJECT_STOREFRONT_KEY', false))
                        const storefrontKey = "{{ env('STOREFRONT_PLAYGROUND_KEY') ?? 'pk_storefront_xxxxx' }}";
                        if (storefrontKey && !request.url.includes('/api/admin')) {
                            request.headers['X-STOREFRONT-KEY'] = storefrontKey;
                        }
                        @endif
                        return request;
                    }
                };

                @if(isset($defaultServer))
                    config.onComplete = function() {
                        if (window.ui && window.ui.specActions) {
                            const servers = window.ui.getState().getIn(['spec', 'servers']);
                            if (servers && servers.size > 0) {
                                servers.forEach((server, index) => {
                                    if (server.get('url') === "{{ $defaultServer }}") {
                                        window.ui.specActions.setSelectedServer(index);
                                    }
                                });
                            }
                        }
                        
                        @if(isset($endpoint) && $endpoint === 'shop' && !env('API_PLAYGROUND_AUTO_INJECT_STOREFRONT_KEY', false))
                            if (window.ui && window.ui.preauthorizeApiKey) {
                                window.ui.preauthorizeApiKey('X-STOREFRONT-KEY', '');
                            }
                        @endif
                    };
                @else
                    config.onComplete = function() {
                        @if(isset($endpoint) && $endpoint === 'shop' && !env('API_PLAYGROUND_AUTO_INJECT_STOREFRONT_KEY', false))
                        if (window.ui && window.ui.preauthorizeApiKey) {
                            window.ui.preauthorizeApiKey('X-STOREFRONT-KEY', '');
                        }
                        @endif
                    };
                @endif

                const ui = SwaggerUIBundle(config);
                window.ui = ui;
            }
        </script>
    </body>
</html>
