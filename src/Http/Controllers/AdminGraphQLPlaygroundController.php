<?php

namespace Webkul\BagistoApi\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

/**
 * Admin GraphQL Playground UI with X-Admin-Key header support
 */
class AdminGraphQLPlaygroundController extends Controller
{
    public function __invoke()
    {
        $adminKey = env('ADMIN_PLAYGROUND_KEY') ?? 'ak_admin_live_xxxxx';

        return new Response($this->getGraphQLPlaygroundHTML($adminKey), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    private function getGraphQLPlaygroundHTML(string $adminKey): string
    {
        $serverUrl = config('app.url').'/graphql';

        return <<<HTML
<!DOCTYPE html>
<html>
  <head>
    <meta charset=utf-8/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>GraphQL Playground - Admin API</title>
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/graphql-playground-react/build/static/css/index.css" />
    <link rel="shortcut icon" href="//cdn.jsdelivr.net/npm/graphql-playground-react/build/favicon.png" />
    <script src="//cdn.jsdelivr.net/npm/graphql-playground-react/build/static/js/middleware.js"></script>
    <style>
        body {
            height: 100%;
            margin: 0;
            width: 100%;
            overflow: hidden;
        }
        #root {
            height: 100%;
            width: 100%;
            display: flex;
            flex-direction: column;
        }
        .graphql-header {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 15px 20px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Oxygen", "Ubuntu", "Cantarell", "Fira Sans", "Droid Sans", "Helvetica Neue", sans-serif;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            flex-wrap: wrap;
            gap: 10px;
        }
        .graphql-header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
        }
        .graphql-header .info {
            font-size: 13px;
            opacity: 0.9;
        }
        .key-input-container {
            display: flex;
            gap: 8px;
            align-items: center;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 12px;
            border-radius: 4px;
        }
        .key-input-container label {
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }
        .key-input-container input {
            padding: 6px 8px;
            border: none;
            border-radius: 3px;
            font-size: 12px;
            min-width: 200px;
            font-family: 'Monaco', 'Courier New', monospace;
        }
        .key-input-container input::placeholder {
            color: #999;
        }
        .graphql-playground {
            flex: 1;
            overflow: hidden;
        }
        .key-indicator {
            background: rgba(255, 255, 255, 0.2);
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }
    </style>
  </head>
  <body>
    <div id="root">
        <div class="graphql-header">
            <div>
                <h1>🔑 Admin GraphQL Playground</h1>
                <p class="info">Bagisto Admin API - Interactive GraphQL Explorer</p>
            </div>
            <div class="key-input-container">
                <label for="admin-key">X-Admin-Key:</label>
                <input 
                    type="text" 
                    id="admin-key" 
                    placeholder="ak_admin_live_xxxxx"
                    value="{$adminKey}"
                />
            </div>
        </div>
        <div class="graphql-playground" id="playground"></div>
    </div>
    <script>
      window.addEventListener('load', function (event) {
        GraphQLPlayground.init(document.getElementById('playground'), {
          endpoint: window.location.href.split('?')[0].replace('/admin/graphiql', '/graphql'),
          subscriptionEndpoint: window.location.protocol === 'wss:' ? 'wss:' : 'ws:' + '//' + window.location.host + '/graphql',
          headers: {
            'X-Admin-Key': document.getElementById('admin-key').value || '{$adminKey}'
          },
          settings: {
            'request.credentials': 'include',
            'prettier.useTabs': false,
            'prettier.printWidth': 100,
            'editor.fontSize': 13,
            'editor.fontFamily': '"Consolas", "Inconsolata", "Droid Sans Mono", "Source Code Pro", monospace'
          }
        })
      })

      // Update headers when key input changes
      document.getElementById('admin-key').addEventListener('change', function(e) {
        const newKey = e.target.value || '{$adminKey}';
        // Update the headers in GraphQL Playground
        if (window.playground && window.playground.state) {
          window.playground.state.headers = { 'X-Admin-Key': newKey };
        }
        // Store in localStorage for persistence
        localStorage.setItem('bagisto-admin-key', newKey);
      });

      // Load persisted key from localStorage
      const savedKey = localStorage.getItem('bagisto-admin-key');
      if (savedKey) {
        document.getElementById('admin-key').value = savedKey;
      }
    </script>
  </body>
</html>
HTML;
    }
}
