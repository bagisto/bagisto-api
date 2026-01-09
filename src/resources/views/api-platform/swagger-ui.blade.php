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
        </style>
        <script id="swagger-data" type="application/json">{!! Illuminate\Support\Js::encode($swagger_data ?? []) !!}</script>
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

        <div id="swagger-ui" class="api-platform"></div>
        <script src="{{ asset('vendor/api-platform/swagger-ui/swagger-ui-bundle.js') }}"></script>
        <script src="{{ asset('vendor/api-platform/swagger-ui/swagger-ui-standalone-preset.js') }}"></script>
        <script src="{{ asset('vendor/api-platform/init-swagger-ui.js') }}"></script>
        <script>
            window.onload = function() {
                
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
                    url: "{{ $specUrl }}",
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
                        if (request.url.includes('/api/docs')) {
                            request.headers['Accept'] = 'application/vnd.openapi+json';
                        }
                        
                        @if(isset($endpoint) && $endpoint === 'shop' && env('API_PLAYGROUND_AUTO_INJECT_STOREFRONT_KEY', false))
                        if (!request.url.includes('/api/admin')) {
                            const storefrontKey = "{{ env('STOREFRONT_PLAYGROUND_KEY') ?? config('storefront.playground_key') }}";
                            if (storefrontKey) {
                                request.headers['X-STOREFRONT-KEY'] = storefrontKey;
                            }
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
