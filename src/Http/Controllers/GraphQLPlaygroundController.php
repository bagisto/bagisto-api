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
        $storefrontKey = env('STOREFRONT_PLAYGROUND_KEY', 'pk_storefront_xxxxx');
        $autoInjectKey = filter_var(env('API_PLAYGROUND_AUTO_INJECT_STOREFRONT_KEY', 'true'), FILTER_VALIDATE_BOOLEAN);

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

        $html = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>GraphQL - API Platform</title>
    <link rel="stylesheet" href="/vendor/api-platform/graphiql/graphiql.css">
    <link rel="stylesheet" href="/vendor/api-platform/graphiql-style.css">
    <script id="graphiql-data" type="application/json">GRAPHIQL_DATA_PLACEHOLDER</script>
    <style>
        body { margin: 0; padding: 0; }
        #graphiql { height: calc(100vh - 36px); }

        /* Auth status bar */
        #auth-top-bar {
            height: 36px;
            display: flex;
            align-items: center;
            padding: 0 14px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 13px;
            font-weight: 600;
            box-sizing: border-box;
            gap: 10px;
            transition: background 0.2s;
        }
        #auth-top-bar.bar-none {
            background: #fff3cd;
            border-bottom: 1px solid #ffc107;
            color: #856404;
        }
        #auth-top-bar.bar-customer {
            background: #d4edda;
            border-bottom: 1px solid #28a745;
            color: #155724;
        }
        #auth-top-bar.bar-guest {
            background: #d1ecf1;
            border-bottom: 1px solid #17a2b8;
            color: #0c5460;
        }
        #auth-top-bar .bar-msg {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            line-height: 1;
        }
        #auth-top-bar .bar-token {
            font-family: 'SFMono-Regular', Consolas, monospace;
            font-size: 11px;
            font-weight: 400;
            opacity: 0.85;
        }
        #auth-top-bar .bar-actions {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
        }
        #auth-top-bar button {
            padding: 3px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 11px;
            font-weight: 600;
            transition: opacity 0.15s;
            line-height: 1.4;
        }
        #auth-top-bar button:hover { opacity: 0.85; }
        .bar-btn-clear { background: rgba(0,0,0,0.15); color: inherit; }
        .bar-btn-manual { background: rgba(0,0,0,0.1); color: inherit; }
        .bar-btn-apply { background: #0d6efd; color: #fff; }
        .bar-manual-input {
            font-family: 'SFMono-Regular', Consolas, monospace;
            font-size: 11px;
            padding: 3px 8px;
            border: 1px solid rgba(0,0,0,0.2);
            border-radius: 4px;
            outline: none;
            background: rgba(255,255,255,0.7);
            color: #333;
            width: 280px;
        }
        .bar-manual-input:focus {
            border-color: #80bdff;
            background: #fff;
        }
    </style>
</head>
<body>
<div id="auth-top-bar"></div>
<div id="graphiql">Loading...</div>
<script src="/vendor/api-platform/react/react.production.min.js"></script>
<script src="/vendor/api-platform/react/react-dom.production.min.js"></script>
<script src="/vendor/api-platform/graphiql/graphiql.min.js"></script>
<script>
/* ═══════════════════════════════════════════════════════════
   Token Encryption — AES-GCM via Web Crypto API
   ═══════════════════════════════════════════════════════════ */
var CRYPTO_KEY = null;

/** Derive a stable encryption key from the storefront API key using PBKDF2 → AES-GCM */
async function initCryptoKey(passphrase) {
    var enc = new TextEncoder();
    var keyMaterial = await crypto.subtle.importKey(
        'raw', enc.encode(passphrase), 'PBKDF2', false, ['deriveKey']
    );
    CRYPTO_KEY = await crypto.subtle.deriveKey(
        { name: 'PBKDF2', salt: enc.encode('bagisto-graphiql-v1'), iterations: 100000, hash: 'SHA-256' },
        keyMaterial,
        { name: 'AES-GCM', length: 256 },
        false,
        ['encrypt', 'decrypt']
    );
}

/** Encrypt a plaintext token → base64 string stored in sessionStorage */
async function encryptToken(plaintext) {
    if (!CRYPTO_KEY || !plaintext) return plaintext;
    try {
        var enc = new TextEncoder();
        var iv = crypto.getRandomValues(new Uint8Array(12));
        var ciphertext = await crypto.subtle.encrypt(
            { name: 'AES-GCM', iv: iv },
            CRYPTO_KEY,
            enc.encode(plaintext)
        );
        /* Prepend IV (12 bytes) to ciphertext */
        var combined = new Uint8Array(iv.length + ciphertext.byteLength);
        combined.set(iv);
        combined.set(new Uint8Array(ciphertext), iv.length);
        return 'enc:' + btoa(String.fromCharCode.apply(null, combined));
    } catch (e) {
        return plaintext; /* Fallback to plaintext if encryption fails */
    }
}

/** Decrypt a stored value back to plaintext */
async function decryptToken(stored) {
    if (!stored) return null;
    if (!CRYPTO_KEY || !stored.startsWith('enc:')) return stored;
    try {
        var raw = atob(stored.substring(4));
        var bytes = new Uint8Array(raw.length);
        for (var i = 0; i < raw.length; i++) bytes[i] = raw.charCodeAt(i);
        var iv = bytes.slice(0, 12);
        var ciphertext = bytes.slice(12);
        var decrypted = await crypto.subtle.decrypt(
            { name: 'AES-GCM', iv: iv },
            CRYPTO_KEY,
            ciphertext
        );
        return new TextDecoder().decode(decrypted);
    } catch (e) {
        return null; /* Corrupted — return null so it gets cleared */
    }
}

/* ═══════════════════════════════════════════════════════════
   Token Storage (encrypted in localStorage)
   ═══════════════════════════════════════════════════════════ */
var AUTH_TOKEN_KEY = 'bagisto-graphiql-auth-token';
var CART_TOKEN_KEY = 'bagisto-graphiql-cart-token';

/* In-memory plaintext cache to avoid async decrypt on every fetch */
var _cachedAuthToken = null;
var _cachedCartToken = null;

function getStoredToken() { return _cachedAuthToken; }
function getStoredCartToken() { return _cachedCartToken; }
function getActiveToken() { return _cachedAuthToken || _cachedCartToken || null; }

async function storeToken(token) {
    _cachedAuthToken = token;
    _cachedCartToken = null;
    localStorage.setItem(AUTH_TOKEN_KEY, await encryptToken(token));
    localStorage.removeItem(CART_TOKEN_KEY);
    refreshUI();
}

async function storeCartToken(token) {
    if (_cachedAuthToken) return; /* Customer auth takes priority */
    _cachedCartToken = token;
    localStorage.setItem(CART_TOKEN_KEY, await encryptToken(token));
    refreshUI();
}

function clearAuthToken() {
    _cachedAuthToken = null;
    localStorage.removeItem(AUTH_TOKEN_KEY);
    refreshUI();
}

function clearCartToken() {
    _cachedCartToken = null;
    localStorage.removeItem(CART_TOKEN_KEY);
    refreshUI();
}

/** Restore cached tokens from encrypted localStorage on page load */
async function restoreTokens() {
    _cachedAuthToken = await decryptToken(localStorage.getItem(AUTH_TOKEN_KEY));
    _cachedCartToken = await decryptToken(localStorage.getItem(CART_TOKEN_KEY));
    /* Clear corrupted entries */
    if (localStorage.getItem(AUTH_TOKEN_KEY) && !_cachedAuthToken) localStorage.removeItem(AUTH_TOKEN_KEY);
    if (localStorage.getItem(CART_TOKEN_KEY) && !_cachedCartToken) localStorage.removeItem(CART_TOKEN_KEY);
}

function refreshUI() {
    updateToolbarButton();
    syncHeadersEditor();
}

/* ═══════════════════════════════════════════════════════════
   Helpers
   ═══════════════════════════════════════════════════════════ */
function maskToken(token) {
    if (!token) return '';
    return token.length > 20
        ? token.substring(0, 10) + '\u2022\u2022\u2022' + token.substring(token.length - 4)
        : token;
}

function syncHeadersEditor() {
    var headersObj = { 'X-STOREFRONT-KEY': defaultApiKey };
    var token = getActiveToken();
    if (token) headersObj['Authorization'] = 'Bearer ' + token;
    var headersJson = JSON.stringify(headersObj, null, 2);
    setTimeout(function() {
        var editors = document.querySelectorAll('.graphiql-editor-tool .CodeMirror');
        if (editors.length >= 2) {
            var cm = editors[1].CodeMirror;
            if (cm) cm.setValue(headersJson);
        }
    }, 100);
}

function deepFind(obj, field, maxDepth) {
    if (!obj || typeof obj !== 'object' || maxDepth <= 0) return null;
    if (obj.hasOwnProperty(field) && typeof obj[field] === 'string' && obj[field].length > 0) return obj[field];
    var keys = Object.keys(obj);
    for (var i = 0; i < keys.length; i++) {
        var val = obj[keys[i]];
        if (val && typeof val === 'object') {
            var found = deepFind(val, field, maxDepth - 1);
            if (found) return found;
        }
    }
    return null;
}

function interceptAuthResponse(result) {
    if (!result || !result.data) return;
    var loginData = result.data.createCustomerLogin;
    if (loginData) {
        var inner = loginData.customerLogin || loginData;
        if (inner && inner.success === true && inner.token) { storeToken(inner.token); return; }
    }
    var logoutData = result.data.createLogout;
    if (logoutData) {
        var logoutInner = logoutData.logout || logoutData;
        if (logoutInner && logoutInner.success === true) { clearAuthToken(); return; }
    }
    var cartToken = deepFind(result.data, 'cartToken', 3);
    if (cartToken && typeof cartToken === 'string' && cartToken.length > 0) storeCartToken(cartToken);
}

/* ═══════════════════════════════════════════════════════════
   GraphQL Fetcher
   ═══════════════════════════════════════════════════════════ */
var initParameters = {};
var entrypoint = null;
var defaultApiKey = null;
var autoInjectStorefrontKey = false;

function onEditQuery(q) { initParameters.query = q; updateURL(); }
function onEditVariables(v) { initParameters.variables = v; updateURL(); }
function onEditOperationName(n) { initParameters.operationName = n; updateURL(); }

function updateURL() {
    var s = '?' + Object.keys(initParameters).filter(function(k){ return Boolean(initParameters[k]); })
        .map(function(k){ return encodeURIComponent(k) + '=' + encodeURIComponent(initParameters[k]); }).join('&');
    history.replaceState(null, null, s);
}

function graphQLFetcher(graphQLParams, opts) {
    var headers = (opts && opts.headers) ? opts.headers : {};
    var token = getActiveToken();
    if (token && !headers['Authorization'] && !headers['authorization']) {
        headers['Authorization'] = 'Bearer ' + token;
    }

    /**
     * Fix "Unknown operation named" error when switching tabs.
     * GraphiQL may pass an operationName from a previous tab that
     * doesn't exist in the current query. Strip it if not found.
     */
    var params = Object.assign({}, graphQLParams);
    if (params.operationName && params.query) {
        var escaped = params.operationName.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        var opNamePattern = new RegExp('(query|mutation|subscription)\\s+' + escaped + '\\b');
        if (!opNamePattern.test(params.query)) {
            delete params.operationName;
        }
    }

    return fetch(entrypoint, {
        method: 'post',
        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', ...headers },
        body: JSON.stringify(params),
        credentials: 'include'
    }).then(function(r){ return r.text(); })
    .then(function(body){
        try { var result = JSON.parse(body); interceptAuthResponse(result); return result; }
        catch(e) { return body; }
    });
}

/* ═══════════════════════════════════════════════════════════
   Auth Status Bar (React component)
   ═══════════════════════════════════════════════════════════ */
var _authBarForceUpdate = null;
var _showManualInput = false;

function updateToolbarButton() {
    if (_authBarForceUpdate) _authBarForceUpdate();
}

function AuthStatusBar() {
    var stateRef = React.useState(0);
    var forceUpdate = function() { stateRef[1](function(n){ return n + 1; }); };
    React.useEffect(function() { _authBarForceUpdate = forceUpdate; return function() { _authBarForceUpdate = null; }; }, []);

    var authToken = getStoredToken();
    var cartToken = getStoredCartToken();
    var hasAuth = !!authToken;
    var hasCart = !hasAuth && !!cartToken;

    /* Set bar class for color */
    var barClass = hasAuth ? 'bar-customer' : (hasCart ? 'bar-guest' : 'bar-none');

    /* Build message */
    var msgParts = [];
    if (hasAuth) {
        msgParts.push('\uD83D\uDD12 Customer authenticated');
        msgParts.push(React.createElement('span', { key: 't', className: 'bar-token' }, '\u2014 Bearer ' + maskToken(authToken)));
    } else if (hasCart) {
        msgParts.push('\uD83D\uDED2 Guest cart token active');
        msgParts.push(React.createElement('span', { key: 't', className: 'bar-token' }, '\u2014 Bearer ' + maskToken(cartToken)));
    } else {
        msgParts.push('\uD83D\uDD13 No auth token \u2014 run createCustomerLogin or createCartToken mutation');
    }

    function handleManualApply() {
        var input = document.getElementById('manual-token-input');
        if (!input || !input.value.trim()) return;
        var val = input.value.trim();
        if (/^\d+\|/.test(val)) { storeToken(val); } else { storeCartToken(val); }
        _showManualInput = false;
        forceUpdate();
    }

    function toggleManual() {
        _showManualInput = !_showManualInput;
        forceUpdate();
        if (_showManualInput) {
            setTimeout(function() { var el = document.getElementById('manual-token-input'); if (el) el.focus(); }, 50);
        }
    }

    /* Action buttons */
    var actions = [];
    if (_showManualInput) {
        actions.push(
            React.createElement('input', {
                key: 'inp',
                id: 'manual-token-input',
                className: 'bar-manual-input',
                type: 'text',
                placeholder: 'Paste token (123|abc... or UUID)...',
                onKeyDown: function(e) { if (e.key === 'Enter') handleManualApply(); if (e.key === 'Escape') toggleManual(); }
            }),
            React.createElement('button', { key: 'ap', className: 'bar-btn-apply', onClick: handleManualApply }, 'Apply'),
            React.createElement('button', { key: 'cn', className: 'bar-btn-manual', onClick: toggleManual }, 'Cancel')
        );
    } else {
        actions.push(
            React.createElement('button', { key: 'me', className: 'bar-btn-manual', onClick: toggleManual }, 'Manual Entry')
        );
        if (hasAuth) actions.push(
            React.createElement('button', { key: 'ca', className: 'bar-btn-clear', onClick: clearAuthToken }, 'Clear')
        );
        if (hasCart) actions.push(
            React.createElement('button', { key: 'cc', className: 'bar-btn-clear', onClick: clearCartToken }, 'Clear')
        );
    }

    /* Update bar element class directly for color */
    React.useEffect(function() {
        var bar = document.getElementById('auth-top-bar');
        if (bar) { bar.className = barClass; }
    });

    return React.createElement(React.Fragment, null,
        React.createElement('div', { className: 'bar-msg' }, msgParts),
        React.createElement('div', { className: 'bar-actions' }, actions)
    );
}

/* ═══════════════════════════════════════════════════════════
   Init
   ═══════════════════════════════════════════════════════════ */
window.onload = async function() {
    var data = JSON.parse(document.getElementById('graphiql-data').innerText);
    entrypoint = data.entrypoint;
    defaultApiKey = data.apiKey;
    autoInjectStorefrontKey = data.autoInjectKey === true || data.autoInjectKey === 'true';

    /* Initialize encryption key from the storefront API key */
    await initCryptoKey(defaultApiKey + '-playground-secret');
    await restoreTokens();

    var search = window.location.search;
    search.substr(1).split('&').forEach(function(entry) {
        var eq = entry.indexOf('=');
        if (eq >= 0) initParameters[decodeURIComponent(entry.slice(0, eq))] = decodeURIComponent(entry.slice(eq + 1));
    });

    if (initParameters.variables) {
        try { initParameters.variables = JSON.stringify(JSON.parse(initParameters.variables), null, 2); }
        catch(e) {}
    }

    var headersObj = { 'X-STOREFRONT-KEY': defaultApiKey };
    var existingToken = getActiveToken();
    if (existingToken) headersObj['Authorization'] = 'Bearer ' + existingToken;
    var defaultHeaders = JSON.stringify(headersObj, null, 2);

    var renderProps = {
        fetcher: graphQLFetcher,
        query: initParameters.query,
        variables: initParameters.variables,
        operationName: initParameters.operationName,
        onEditQuery: onEditQuery,
        onEditVariables: onEditVariables,
        onEditOperationName: onEditOperationName
    };

    if (autoInjectStorefrontKey) renderProps.defaultHeaders = defaultHeaders;

    /* Render auth status bar above GraphiQL */
    ReactDOM.render(
        React.createElement(AuthStatusBar, null),
        document.getElementById('auth-top-bar')
    );

    ReactDOM.render(
        React.createElement(GraphiQL, renderProps),
        document.getElementById('graphiql')
    );
}
</script>
</body>
</html>
HTML;

        return str_replace('GRAPHIQL_DATA_PLACEHOLDER', $graphiqlData, $html);
    }
}
