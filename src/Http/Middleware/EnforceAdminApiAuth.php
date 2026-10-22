<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use GraphQL\Error\SyntaxError;
use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\OperationDefinitionNode;
use GraphQL\Language\Parser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Webkul\BagistoApi\Admin\Auth\AdminApiGuard;
use Webkul\BagistoApi\Exception\AdminAuthException;

class EnforceAdminApiAuth
{
    /**
     * The admin REST paths reachable without a token, all of them POST.
     */
    public const PUBLIC_REST_PATHS = [
        '/api/admin/auth/login',
        '/api/admin/auth/two-factor',
        '/api/admin/auth/refresh',
        '/api/admin/auth/forgot-password',
        '/api/admin/auth/reset-password',
    ];

    /**
     * The admin GraphQL mutations reachable without a token.
     */
    public const PUBLIC_GRAPHQL_MUTATIONS = [
        'createAdminLogin',
        'createAdminTwoFactorVerify',
        'createAdminTokenRefresh',
        'createAdminForgotPassword',
        'createAdminResetPassword',
    ];

    /**
     * The admin GraphQL endpoint.
     */
    public const GRAPHQL_PATH = '/api/admin/graphql';

    /**
     * Reject an admin API request without a valid access token or API token, except the public auth operations.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->getPathInfo();

        if (! str_starts_with($path, '/api/admin')) {
            return $next($request);
        }

        if (
            $this->isDocumentationEndpoint($request)
            || $this->isPublicOperation($request)
        ) {
            return $next($request);
        }

        $guard = Auth::guard('admin-api');

        if ($guard->user()) {
            return $next($request);
        }

        $failure = $guard instanceof AdminApiGuard
            ? $guard->failure()
            : null;

        return $this->reject($request, $failure ?? new AdminAuthException(
            trans('bagistoapi::app.admin.auth.errors.unauthenticated'),
        ));
    }

    /**
     * Whether the request is one of the login, two-factor, refresh or password operations.
     */
    protected function isPublicOperation(Request $request): bool
    {
        if (! $request->isMethod('POST')) {
            return false;
        }

        $path = rtrim($request->getPathInfo(), '/');

        if ($path === self::GRAPHQL_PATH) {
            return $this->isPublicGraphQlDocument($this->graphQlDocument($request));
        }

        return in_array($path, self::PUBLIC_REST_PATHS, true);
    }

    /**
     * The GraphQL document the entrypoint will run, read from the raw body, or null when it is ambiguous.
     */
    protected function graphQlDocument(Request $request): ?string
    {
        if (
            $request->query->has('query')
            || $request->request->has('operations')
            || count($request->files->all()) > 0
            || str_contains((string) $request->headers->get('Content-Type'), 'multipart/')
        ) {
            return null;
        }

        $content = (string) $request->getContent();

        $data = json_decode($content, true);

        if (! is_array($data)) {
            return $content;
        }

        if (
            array_is_list($data)
            || ! is_string($data['query'] ?? null)
        ) {
            return null;
        }

        return $data['query'];
    }

    /**
     * Whether a GraphQL document holds only mutations whose every root field is a public auth mutation.
     */
    protected function isPublicGraphQlDocument(?string $query): bool
    {
        if (
            $query === null
            || trim($query) === ''
        ) {
            return false;
        }

        try {
            $document = Parser::parse($query, ['noLocation' => true]);
        } catch (SyntaxError $exception) {
            return false;
        }

        $operations = 0;

        foreach ($document->definitions as $definition) {
            if (! $definition instanceof OperationDefinitionNode) {
                continue;
            }

            if (
                $definition->operation !== 'mutation'
                || ! $this->selectsOnlyPublicFields($definition)
            ) {
                return false;
            }

            $operations++;
        }

        return $operations > 0;
    }

    /**
     * Whether every root selection of an operation is a public auth mutation field.
     */
    protected function selectsOnlyPublicFields(OperationDefinitionNode $operation): bool
    {
        if (count($operation->selectionSet->selections) === 0) {
            return false;
        }

        foreach ($operation->selectionSet->selections as $selection) {
            if (
                ! $selection instanceof FieldNode
                || (
                    ! in_array($selection->name->value, self::PUBLIC_GRAPHQL_MUTATIONS, true)
                    && $selection->name->value !== '__typename'
                )
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * The 401 or 403 response for a rejected request, shaped for REST or for GraphQL.
     */
    protected function reject(Request $request, AdminAuthException $exception): JsonResponse
    {
        $body = $exception->toResponseBody();

        if (rtrim($request->getPathInfo(), '/') === self::GRAPHQL_PATH) {
            $body['errors'] = [[
                'message' => $exception->getMessage(),
                'extensions' => $exception->getExtensions(),
            ]];
        }

        return new JsonResponse($body, $exception->getStatusCode(), $exception->getHeaders());
    }

    /**
     * Whether the request reads the admin API documentation.
     */
    protected function isDocumentationEndpoint(Request $request): bool
    {
        if ($request->method() !== 'GET') {
            return false;
        }

        return in_array($request->getPathInfo(), [
            '/api/admin',
            '/api/admin/docs',
            '/api/admin/graphiql',
        ], true);
    }
}
