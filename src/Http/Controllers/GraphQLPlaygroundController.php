<?php

namespace Webkul\BagistoApi\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

/**
 * GraphQL Playground UI with X-STOREFRONT-KEY header support
 */
class GraphQLPlaygroundController extends Controller
{
    /**
     * Display GraphQL Playground with Storefront Key input
     */
    public function __invoke()
    {
        $storefrontKey = env('STOREFRONT_PLAYGROUND_KEY') ?? 'pk_storefront_xxxxx';
        $autoInjectKey = env('API_PLAYGROUND_AUTO_INJECT_STOREFRONT_KEY', false);

        return new Response($this->getGraphQLPlaygroundHTML($storefrontKey, $autoInjectKey), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    /**
     * Generate GraphQL Playground HTML with custom styling and header injection
     *
     * @param  string  $storefrontKey  The storefront API key to use
     * @param  bool  $autoInjectKey  Whether to auto-inject the key in headers (controlled by API_AUTO_INJECT_STOREFRONT_KEY env)
     */
    private function getGraphQLPlaygroundHTML(string $storefrontKey, bool $autoInjectKey = false): string
    {
        $graphiqlData = json_encode([
            'entrypoint'    => '/api/graphql',
            'apiKey'        => $storefrontKey,
            'autoInjectKey' => $autoInjectKey,
        ]);

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>GraphQL - API Platform</title>
    <link rel="stylesheet" href="/vendor/api-platform/graphiql/graphiql.css">
    <link rel="stylesheet" href="/vendor/api-platform/graphiql-style.css">
    <script id="graphiql-data" type="application/json">$graphiqlData</script>
</head>
<body>
<div id="graphiql">Loading...</div>
<script src="/vendor/api-platform/react/react.production.min.js"></script>
<script src="/vendor/api-platform/react/react-dom.production.min.js"></script>
<script src="/vendor/api-platform/graphiql/graphiql.min.js"></script>
<script>
var initParameters = {};
var entrypoint = null;
var defaultApiKey = null;
var autoInjectStorefrontKey = false;

function onEditQuery(newQuery) {
    initParameters.query = newQuery;
    updateURL();
}

function onEditVariables(newVariables) {
    initParameters.variables = newVariables;
    updateURL();
}

function onEditOperationName(newOperationName) {
    initParameters.operationName = newOperationName;
    updateURL();
}

function updateURL() {
    var newSearch = '?' + Object.keys(initParameters).filter(function (key) {
        return Boolean(initParameters[key]);
    }).map(function (key) {
        return encodeURIComponent(key) + '=' + encodeURIComponent(initParameters[key]);
    }).join('&');
    history.replaceState(null, null, newSearch);
}

function graphQLFetcher(graphQLParams, {headers}) {
    return fetch(entrypoint, {
        method: 'post',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            ...headers
        },
        body: JSON.stringify(graphQLParams),
        credentials: 'include'
    }).then(function (response) {
        return response.text();
    }).then(function (responseBody) {
        try {
            return JSON.parse(responseBody);
        } catch (error) {
            return responseBody;
        }
    });
}

window.onload = function() {
    var data = JSON.parse(document.getElementById('graphiql-data').innerText);
    entrypoint = data.entrypoint;
    defaultApiKey = data.apiKey;
    autoInjectStorefrontKey = data.autoInjectKey === true || data.autoInjectKey === 'true';

    sessionStorage.setItem('bagisto-api-key', defaultApiKey);

    var search = window.location.search;
    search.substr(1).split('&').forEach(function (entry) {
        var eq = entry.indexOf('=');
        if (eq >= 0) {
            initParameters[decodeURIComponent(entry.slice(0, eq))] = decodeURIComponent(entry.slice(eq + 1));
        }
    });

    if (initParameters.variables) {
        try {
            initParameters.variables = JSON.stringify(JSON.parse(initParameters.variables), null, 2);
        } catch (e) {
            // Do nothing, we want to display the invalid JSON as a string, rather than present an error.
        }
    }

    // Prepare headers to show in GraphiQL UI (as JSON string)
    var defaultHeaders = JSON.stringify({
        'X-STOREFRONT-KEY': defaultApiKey
    });

    var renderProps = {
        fetcher: graphQLFetcher,
        query: initParameters.query,
        variables: initParameters.variables,
        operationName: initParameters.operationName,
        onEditQuery: onEditQuery,
        onEditVariables: onEditVariables,
        onEditOperationName: onEditOperationName
    };

    // Only add defaultHeaders if auto-injection is enabled
    if (autoInjectStorefrontKey) {
        renderProps.defaultHeaders = defaultHeaders;
    }

    ReactDOM.render(
        React.createElement(GraphiQL, renderProps),
        document.getElementById('graphiql')
    );
}
</script>
</body>
</html>
HTML;
    }
}
