<?php

namespace Webkul\BagistoApi\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

/**
 * Admin GraphQL Playground UI — mirrors GraphQLPlaygroundController structure
 * with the admin-specific differences:
 *   - posts to /api/admin/graphql (dedicated admin endpoint)
 *   - injects Authorization: Bearer <token> only (NO X-STOREFRONT-KEY)
 *   - single token slot (admin integration token, format `<id>|<random>`) —
 *     admin has no guest-cart equivalent so the cart-token slot is dropped
 *   - banner reads "Admin authenticated"
 */
class AdminGraphQLPlaygroundController extends Controller
{
    public function __invoke()
    {
        return new Response($this->getGraphQLPlaygroundHTML(), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    private function getGraphQLPlaygroundHTML(): string
    {
        $graphiqlData = json_encode([
            'entrypoint' => '/api/admin/graphql',
        ]);

        $html = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>GraphQL - Admin API Platform</title>
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
        #auth-top-bar.bar-admin {
            background: #cfe2ff;
            border-bottom: 1px solid #0d6efd;
            color: #084298;
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
            width: 320px;
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

async function initCryptoKey(passphrase) {
    /* crypto.subtle only exists in a secure context; fall back to plaintext otherwise */
    if (!window.isSecureContext || !window.crypto || !crypto.subtle) {
        CRYPTO_KEY = null;
        return;
    }
    var enc = new TextEncoder();
    var keyMaterial = await crypto.subtle.importKey(
        'raw', enc.encode(passphrase), 'PBKDF2', false, ['deriveKey']
    );
    CRYPTO_KEY = await crypto.subtle.deriveKey(
        { name: 'PBKDF2', salt: enc.encode('bagisto-admin-graphiql-v1'), iterations: 100000, hash: 'SHA-256' },
        keyMaterial,
        { name: 'AES-GCM', length: 256 },
        false,
        ['encrypt', 'decrypt']
    );
}

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
        var combined = new Uint8Array(iv.length + ciphertext.byteLength);
        combined.set(iv);
        combined.set(new Uint8Array(ciphertext), iv.length);
        return 'enc:' + btoa(String.fromCharCode.apply(null, combined));
    } catch (e) {
        return plaintext;
    }
}

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
        return null;
    }
}

/* ═══════════════════════════════════════════════════════════
   Token Storage (encrypted in localStorage)
   ═══════════════════════════════════════════════════════════ */
var ADMIN_TOKEN_KEY = 'bagisto-graphiql-admin-token';

var _cachedAdminToken = null;

function getStoredToken() { return _cachedAdminToken; }

async function storeToken(token) {
    _cachedAdminToken = token;
    localStorage.setItem(ADMIN_TOKEN_KEY, await encryptToken(token));
    /* Backwards-compat with existing setBagistoAdminBearer helper consumers */
    localStorage.setItem('bagisto-admin-bearer', token || '');
    refreshUI();
}

function clearAdminToken() {
    _cachedAdminToken = null;
    localStorage.removeItem(ADMIN_TOKEN_KEY);
    localStorage.removeItem('bagisto-admin-bearer');
    refreshUI();
}

async function restoreTokens() {
    _cachedAdminToken = await decryptToken(localStorage.getItem(ADMIN_TOKEN_KEY));
    /* Fall back to plaintext key from setBagistoAdminBearer(...) usage */
    if (!_cachedAdminToken) {
        var plain = localStorage.getItem('bagisto-admin-bearer');
        if (plain) {
            _cachedAdminToken = plain;
            localStorage.setItem(ADMIN_TOKEN_KEY, await encryptToken(plain));
        }
    }
    if (localStorage.getItem(ADMIN_TOKEN_KEY) && !_cachedAdminToken) localStorage.removeItem(ADMIN_TOKEN_KEY);
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
        ? token.substring(0, 10) + '•••' + token.substring(token.length - 4)
        : token;
}

function syncHeadersEditor() {
    var headersObj = {};
    var token = getStoredToken();
    headersObj['Authorization'] = 'Bearer ' + (token || '**|*********');
    var headersJson = JSON.stringify(headersObj, null, 2);
    setTimeout(function() {
        var editors = document.querySelectorAll('.graphiql-editor-tool .CodeMirror');
        if (editors.length >= 2) {
            var cm = editors[1].CodeMirror;
            if (cm) cm.setValue(headersJson);
        }
    }, 100);
}

/* Expose programmatic helper (mirrors setBagistoApiKey on shop) */
window.setBagistoAdminBearer = function(token) {
    storeToken(token);
};

/* ═══════════════════════════════════════════════════════════
   Namespaced localStorage adapter — keeps admin GraphiQL state
   (tabs, history, query, variable / header editors, ...) fully
   isolated from the shop GraphiQL UI on the same origin.
   ═══════════════════════════════════════════════════════════ */
function createNamespacedStorage(prefix) {
    return {
        getItem: function(key) {
            return localStorage.getItem(prefix + key);
        },
        setItem: function(key, value) {
            localStorage.setItem(prefix + key, value);
        },
        removeItem: function(key) {
            localStorage.removeItem(prefix + key);
        },
        clear: function() {
            for (var i = localStorage.length - 1; i >= 0; i--) {
                var k = localStorage.key(i);
                if (k && k.indexOf(prefix) === 0) localStorage.removeItem(k);
            }
        },
        get length() {
            var n = 0;
            for (var i = 0; i < localStorage.length; i++) {
                var k = localStorage.key(i);
                if (k && k.indexOf(prefix) === 0) n++;
            }
            return n;
        },
        key: function(index) {
            var n = 0;
            for (var i = 0; i < localStorage.length; i++) {
                var k = localStorage.key(i);
                if (k && k.indexOf(prefix) === 0) {
                    if (n === index) return k.substring(prefix.length);
                    n++;
                }
            }
            return null;
        }
    };
}

/* ═══════════════════════════════════════════════════════════
   GraphQL Fetcher
   ═══════════════════════════════════════════════════════════ */
var initParameters = {};
var entrypoint = null;

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
    var token = getStoredToken();
    if (token && !headers['Authorization'] && !headers['authorization']) {
        headers['Authorization'] = 'Bearer ' + token;
    }

    /* Strip stale operationName when switching tabs (mirrors shop fix) */
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
        try { return JSON.parse(body); }
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

    var adminToken = getStoredToken();
    var hasAuth = !!adminToken;

    var barClass = hasAuth ? 'bar-admin' : 'bar-none';

    var msgParts = [];
    if (hasAuth) {
        msgParts.push('🔑 Admin authenticated');
        msgParts.push(React.createElement('span', { key: 't', className: 'bar-token' }, '— Bearer ' + maskToken(adminToken)));
    } else {
        msgParts.push('🔓 No admin token — paste an integration token (Settings → Integration in the admin panel)');
    }

    function handleManualApply() {
        var input = document.getElementById('manual-token-input');
        if (!input || !input.value.trim()) return;
        storeToken(input.value.trim());
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

    var actions = [];
    if (_showManualInput) {
        actions.push(
            React.createElement('input', {
                key: 'inp',
                id: 'manual-token-input',
                className: 'bar-manual-input',
                type: 'text',
                placeholder: 'Paste admin integration token (<id>|<random>)...',
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
            React.createElement('button', { key: 'ca', className: 'bar-btn-clear', onClick: clearAdminToken }, 'Clear')
        );
    }

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

    /* Initialise encryption key with a stable passphrase scoped to the admin playground */
    await initCryptoKey('bagisto-admin-graphiql-secret');
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

    var headersObj = {};
    var existingToken = getStoredToken();
    headersObj['Authorization'] = 'Bearer ' + (existingToken || '**|*********');
    var defaultHeaders = JSON.stringify(headersObj, null, 2);

    /* Namespace GraphiQL's own localStorage (tabs, history, query, headers, ...)
       under an `admin:` prefix so the admin UI's state is fully isolated from
       the shop UI's. Without this, both UIs read/write the same keys (same
       origin) and shop tabs leak into admin UI. */
    var renderProps = {
        fetcher: graphQLFetcher,
        query: initParameters.query,
        variables: initParameters.variables,
        operationName: initParameters.operationName,
        onEditQuery: onEditQuery,
        onEditVariables: onEditVariables,
        onEditOperationName: onEditOperationName,
        defaultHeaders: defaultHeaders,
        storage: createNamespacedStorage('bagisto-admin-graphiql:')
    };

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
