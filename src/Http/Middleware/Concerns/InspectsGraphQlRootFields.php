<?php

namespace Webkul\BagistoApi\Http\Middleware\Concerns;

use GraphQL\Error\SyntaxError;
use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\OperationDefinitionNode;
use GraphQL\Language\Parser;
use Illuminate\Http\Request;

trait InspectsGraphQlRootFields
{
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
     * Whether a GraphQL document holds only mutations whose every root field is one of the given fields.
     */
    protected function documentSelectsOnlyMutations(?string $query, array $fields): bool
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
                || ! $this->selectsOnlyFields($definition, $fields)
            ) {
                return false;
            }

            $operations++;
        }

        return $operations > 0;
    }

    /**
     * Whether every root selection of an operation is one of the given fields.
     */
    protected function selectsOnlyFields(OperationDefinitionNode $operation, array $fields): bool
    {
        if (count($operation->selectionSet->selections) === 0) {
            return false;
        }

        foreach ($operation->selectionSet->selections as $selection) {
            if (
                ! $selection instanceof FieldNode
                || (
                    ! in_array($selection->name->value, $fields, true)
                    && $selection->name->value !== '__typename'
                )
            ) {
                return false;
            }
        }

        return true;
    }
}
