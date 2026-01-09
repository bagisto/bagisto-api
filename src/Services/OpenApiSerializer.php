<?php

namespace Webkul\BagistoApi\Services;

/**
 * Utility class to serialize API Platform's OpenAPI object to array
 * This handles the serialization of complex nested objects
 */
class OpenApiSerializer
{
    /**
     * Check if input is an object and has a method
     */
    private static function hasMethod($obj, $method): bool
    {
        return is_object($obj) && method_exists($obj, $method);
    }

    /**
     * Convert an OpenAPI object to an array recursively
     */
    public static function toArray($openApi): array
    {
        $result = [];

        // Return early if it's already an array
        if (is_array($openApi)) {
            return $openApi;
        }

        // Return empty if not an object
        if (! is_object($openApi)) {
            return $result;
        }

        // Get OpenAPI version
        if (static::hasMethod($openApi, 'getOpenapi') && $openApi->getOpenapi()) {
            $result['openapi'] = $openApi->getOpenapi();
        }

        // Get Info object
        if (static::hasMethod($openApi, 'getInfo') && $openApi->getInfo()) {
            $result['info'] = static::serializeInfo($openApi->getInfo());
        }

        // Get Servers array
        if (static::hasMethod($openApi, 'getServers') && $openApi->getServers()) {
            $result['servers'] = static::serializeServers($openApi->getServers());
        }

        // Get Paths object
        if (static::hasMethod($openApi, 'getPaths') && $openApi->getPaths()) {
            $result['paths'] = static::serializePaths($openApi->getPaths());
        }

        // Get Components object
        if (static::hasMethod($openApi, 'getComponents') && $openApi->getComponents()) {
            $result['components'] = static::serializeComponents($openApi->getComponents());
        }

        // Get Security array
        if (static::hasMethod($openApi, 'getSecurity') && $openApi->getSecurity()) {
            $result['security'] = static::serializeSecurity($openApi->getSecurity());
        }

        // Get Tags array
        if (static::hasMethod($openApi, 'getTags') && $openApi->getTags()) {
            $result['tags'] = static::serializeTags($openApi->getTags());
        }

        // Get External Docs
        if (static::hasMethod($openApi, 'getExternalDocs') && $openApi->getExternalDocs()) {
            $result['externalDocs'] = static::serializeExternalDocs($openApi->getExternalDocs());
        }

        return $result;
    }

    /**
     * Serialize Info object
     */
    private static function serializeInfo($info): array
    {
        $result = [];

        if (is_array($info)) {
            return $info;
        }

        if (! is_object($info)) {
            return $result;
        }

        if (static::hasMethod($info, 'getTitle') && $info->getTitle()) {
            $result['title'] = $info->getTitle();
        }

        if (static::hasMethod($info, 'getDescription') && $info->getDescription()) {
            $result['description'] = $info->getDescription();
        }

        if (static::hasMethod($info, 'getTermsOfService') && $info->getTermsOfService()) {
            $result['termsOfService'] = $info->getTermsOfService();
        }

        if (static::hasMethod($info, 'getContact') && $info->getContact()) {
            $result['contact'] = static::serializeContact($info->getContact());
        }

        if (static::hasMethod($info, 'getLicense') && $info->getLicense()) {
            $result['license'] = static::serializeLicense($info->getLicense());
        }

        if (static::hasMethod($info, 'getVersion') && $info->getVersion()) {
            $result['version'] = $info->getVersion();
        }

        return $result;
    }

    /**
     * Serialize Contact object
     */
    private static function serializeContact($contact): array
    {
        $result = [];

        if (is_array($contact)) {
            return $contact;
        }

        if (! is_object($contact)) {
            return $result;
        }

        if (static::hasMethod($contact, 'getName') && $contact->getName()) {
            $result['name'] = $contact->getName();
        }

        if (static::hasMethod($contact, 'getUrl') && $contact->getUrl()) {
            $result['url'] = $contact->getUrl();
        }

        if (static::hasMethod($contact, 'getEmail') && $contact->getEmail()) {
            $result['email'] = $contact->getEmail();
        }

        return $result;
    }

    /**
     * Serialize License object
     */
    private static function serializeLicense($license): array
    {
        $result = [];

        if (is_array($license)) {
            return $license;
        }

        if (! is_object($license)) {
            return $result;
        }

        if (static::hasMethod($license, 'getName') && $license->getName()) {
            $result['name'] = $license->getName();
        }

        if (static::hasMethod($license, 'getUrl') && $license->getUrl()) {
            $result['url'] = $license->getUrl();
        }

        return $result;
    }

    /**
     * Serialize Servers array
     */
    private static function serializeServers($servers): array
    {
        $result = [];

        foreach ($servers as $server) {
            $result[] = static::serializeServer($server);
        }

        return $result;
    }

    /**
     * Serialize Server object
     */
    private static function serializeServer($server): array
    {
        $result = [];

        if (is_array($server)) {
            return $server;
        }

        if (! is_object($server)) {
            return $result;
        }

        if (static::hasMethod($server, 'getUrl') && $server->getUrl()) {
            $result['url'] = $server->getUrl();
        }

        if (static::hasMethod($server, 'getDescription') && $server->getDescription()) {
            $result['description'] = $server->getDescription();
        }

        if (static::hasMethod($server, 'getVariables') && $server->getVariables()) {
            $result['variables'] = static::serializeServerVariables($server->getVariables());
        }

        return $result;
    }

    /**
     * Serialize ServerVariables
     */
    private static function serializeServerVariables($variables): array
    {
        $result = [];

        foreach ($variables as $name => $variable) {
            $result[$name] = static::serializeServerVariable($variable);
        }

        return $result;
    }

    /**
     * Serialize ServerVariable object
     */
    private static function serializeServerVariable($variable): array
    {
        $result = [];

        if (is_array($variable)) {
            return $variable;
        }

        if (! is_object($variable)) {
            return $result;
        }

        if (static::hasMethod($variable, 'getEnum') && $variable->getEnum()) {
            $result['enum'] = $variable->getEnum();
        }

        if (static::hasMethod($variable, 'getDefault') && $variable->getDefault()) {
            $result['default'] = $variable->getDefault();
        }

        if (static::hasMethod($variable, 'getDescription') && $variable->getDescription()) {
            $result['description'] = $variable->getDescription();
        }

        return $result;
    }

    /**
     * Serialize Paths object
     */
    private static function serializePaths($paths): array
    {
        $result = [];

        // Return early if it's already an array
        if (is_array($paths)) {
            foreach ($paths as $path => $pathItem) {
                $result[$path] = static::serializePathItem($pathItem);
            }

            return $result;
        }

        // The Paths object has a getPaths() method that returns an array
        if (static::hasMethod($paths, 'getPaths')) {
            $pathsData = $paths->getPaths();
            foreach ($pathsData as $path => $pathItem) {
                $result[$path] = static::serializePathItem($pathItem);
            }
        }

        return $result;
    }

    /**
     * Serialize PathItem object
     */
    private static function serializePathItem($pathItem): array
    {
        $result = [];

        // Return early if it's already an array
        if (is_array($pathItem)) {
            return $pathItem;
        }

        if (! is_object($pathItem)) {
            return $result;
        }

        // Serialize each HTTP method (get, post, put, delete, etc.)
        $methods = ['get', 'post', 'put', 'patch', 'delete', 'options', 'head', 'trace'];

        foreach ($methods as $method) {
            $getMethod = 'get'.ucfirst($method);
            if (static::hasMethod($pathItem, $getMethod)) {
                $operation = $pathItem->$getMethod();
                if ($operation) {
                    $result[$method] = static::serializeOperation($operation);
                }
            }
        }

        return $result;
    }

    /**
     * Serialize Operation object
     */
    private static function serializeOperation($operation): array
    {
        $result = [];

        // Return early if it's already an array
        if (is_array($operation)) {
            return $operation;
        }

        // Return empty if not an object
        if (! is_object($operation)) {
            return $result;
        }

        if (static::hasMethod($operation, 'getTags') && $operation->getTags()) {
            $result['tags'] = $operation->getTags();
        }

        if (static::hasMethod($operation, 'getSummary') && $operation->getSummary()) {
            $result['summary'] = $operation->getSummary();
        }

        if (static::hasMethod($operation, 'getDescription') && $operation->getDescription()) {
            $result['description'] = $operation->getDescription();
        }

        if (static::hasMethod($operation, 'getParameters') && $operation->getParameters()) {
            $result['parameters'] = static::serializeParameters($operation->getParameters());
        }

        if (static::hasMethod($operation, 'getRequestBody') && $operation->getRequestBody()) {
            $result['requestBody'] = static::serializeRequestBody($operation->getRequestBody());
        }

        if (static::hasMethod($operation, 'getResponses') && $operation->getResponses()) {
            $result['responses'] = static::serializeResponses($operation->getResponses());
        }

        if (static::hasMethod($operation, 'getDeprecated') && $operation->getDeprecated()) {
            $result['deprecated'] = $operation->getDeprecated();
        }

        if (static::hasMethod($operation, 'getSecurity') && $operation->getSecurity()) {
            $result['security'] = static::serializeSecurity($operation->getSecurity());
        }

        if (static::hasMethod($operation, 'getOperationId') && $operation->getOperationId()) {
            $result['operationId'] = $operation->getOperationId();
        }

        return $result;
    }

    /**
     * Serialize Parameters array
     */
    private static function serializeParameters($parameters): array
    {
        $result = [];

        if (! is_array($parameters) && ! is_iterable($parameters)) {
            return $result;
        }

        foreach ($parameters as $parameter) {
            if (is_array($parameter)) {
                // Already serialized, just add it
                $result[] = $parameter;
            } else {
                $result[] = static::serializeParameter($parameter);
            }
        }

        return $result;
    }

    /**
     * Serialize Parameter object
     */
    private static function serializeParameter($parameter): array
    {
        $result = [];

        // Return early if it's already an array
        if (is_array($parameter)) {
            return $parameter;
        }

        // Return empty if not an object
        if (! is_object($parameter)) {
            return $result;
        }

        if (static::hasMethod($parameter, 'getName') && $parameter->getName()) {
            $result['name'] = $parameter->getName();
        }

        if (static::hasMethod($parameter, 'getIn') && $parameter->getIn()) {
            $result['in'] = $parameter->getIn();
        }

        if (static::hasMethod($parameter, 'getDescription') && $parameter->getDescription()) {
            $result['description'] = $parameter->getDescription();
        }

        if (static::hasMethod($parameter, 'getRequired') && $parameter->getRequired()) {
            $result['required'] = $parameter->getRequired();
        }

        if (static::hasMethod($parameter, 'getSchema') && $parameter->getSchema()) {
            $result['schema'] = static::serializeSchema($parameter->getSchema());
        }

        return $result;
    }

    /**
     * Serialize RequestBody object
     */
    private static function serializeRequestBody($requestBody): array
    {
        $result = [];

        // Return early if it's already an array
        if (is_array($requestBody)) {
            return $requestBody;
        }

        // Return empty if not an object
        if (! is_object($requestBody)) {
            return $result;
        }

        if (static::hasMethod($requestBody, 'getDescription') && $requestBody->getDescription()) {
            $result['description'] = $requestBody->getDescription();
        }

        if (static::hasMethod($requestBody, 'getContent') && $requestBody->getContent()) {
            $result['content'] = static::serializeContent($requestBody->getContent());
        }

        if (static::hasMethod($requestBody, 'getRequired') && $requestBody->getRequired()) {
            $result['required'] = $requestBody->getRequired();
        }

        return $result;
    }

    /**
     * Serialize Content (MediaType objects)
     */
    private static function serializeContent($content): array
    {
        $result = [];

        // Return early if it's already an array with all serialized content
        if (is_array($content)) {
            foreach ($content as $mediaType => $mediaTypeObject) {
                if (is_array($mediaTypeObject)) {
                    $result[$mediaType] = $mediaTypeObject;
                } else {
                    $result[$mediaType] = static::serializeMediaType($mediaTypeObject);
                }
            }

            return $result;
        }

        // If it's an iterable object
        if (is_iterable($content)) {
            foreach ($content as $mediaType => $mediaTypeObject) {
                $result[$mediaType] = static::serializeMediaType($mediaTypeObject);
            }
        }

        return $result;
    }

    /**
     * Serialize MediaType object
     */
    private static function serializeMediaType($mediaType): array
    {
        $result = [];

        // Return early if it's already an array
        if (is_array($mediaType)) {
            return $mediaType;
        }

        // Return empty if not an object
        if (! is_object($mediaType)) {
            return $result;
        }

        if (static::hasMethod($mediaType, 'getSchema') && $mediaType->getSchema()) {
            $result['schema'] = static::serializeSchema($mediaType->getSchema());
        }

        return $result;
    }

    /**
     * Serialize Responses object
     */
    private static function serializeResponses($responses): array
    {
        $result = [];

        foreach ($responses as $statusCode => $response) {
            $result[$statusCode] = static::serializeResponse($response);
        }

        return $result;
    }

    /**
     * Serialize Response object
     */
    private static function serializeResponse($response): array
    {
        $result = [];

        // Return early if it's already an array
        if (is_array($response)) {
            return $response;
        }

        // Return empty if not an object
        if (! is_object($response)) {
            return $result;
        }

        if (static::hasMethod($response, 'getDescription') && $response->getDescription()) {
            $result['description'] = $response->getDescription();
        }

        if (static::hasMethod($response, 'getContent') && $response->getContent()) {
            $result['content'] = static::serializeContent($response->getContent());
        }

        if (static::hasMethod($response, 'getHeaders') && $response->getHeaders()) {
            $result['headers'] = static::serializeHeaders($response->getHeaders());
        }

        return $result;
    }

    /**
     * Serialize Headers
     */
    private static function serializeHeaders($headers): array
    {
        $result = [];

        foreach ($headers as $headerName => $header) {
            $result[$headerName] = static::serializeHeader($header);
        }

        return $result;
    }

    /**
     * Serialize Header object
     */
    private static function serializeHeader($header): array
    {
        $result = [];

        if (is_array($header)) {
            return $header;
        }

        if (! is_object($header)) {
            return $result;
        }

        if (static::hasMethod($header, 'getDescription') && $header->getDescription()) {
            $result['description'] = $header->getDescription();
        }

        if (static::hasMethod($header, 'getSchema') && $header->getSchema()) {
            $result['schema'] = static::serializeSchema($header->getSchema());
        }

        return $result;
    }

    /**
     * Serialize Schema object (recursively handles nested schemas)
     */
    private static function serializeSchema($schema): array
    {
        $result = [];

        // Return early if it's already an array
        if (is_array($schema)) {
            return $schema;
        }

        // Return empty if not an object
        if (! is_object($schema)) {
            return $result;
        }

        if (static::hasMethod($schema, 'getType') && $schema->getType()) {
            $result['type'] = $schema->getType();
        }

        if (static::hasMethod($schema, 'getFormat') && $schema->getFormat()) {
            $result['format'] = $schema->getFormat();
        }

        if (static::hasMethod($schema, 'getDescription') && $schema->getDescription()) {
            $result['description'] = $schema->getDescription();
        }

        if (static::hasMethod($schema, 'getDefault') && $schema->getDefault() !== null) {
            $result['default'] = $schema->getDefault();
        }

        if (static::hasMethod($schema, 'getExample') && $schema->getExample() !== null) {
            $result['example'] = $schema->getExample();
        }

        if (static::hasMethod($schema, 'getRef') && $schema->getRef()) {
            $result['$ref'] = $schema->getRef();
        }

        if (static::hasMethod($schema, 'getItems') && $schema->getItems()) {
            $result['items'] = static::serializeSchema($schema->getItems());
        }

        if (static::hasMethod($schema, 'getProperties') && $schema->getProperties()) {
            $result['properties'] = [];
            foreach ($schema->getProperties() as $propName => $property) {
                $result['properties'][$propName] = static::serializeSchema($property);
            }
        }

        if (static::hasMethod($schema, 'getRequired') && $schema->getRequired()) {
            $result['required'] = $schema->getRequired();
        }

        if (static::hasMethod($schema, 'getEnum') && $schema->getEnum()) {
            $result['enum'] = $schema->getEnum();
        }

        if (static::hasMethod($schema, 'getAdditionalProperties') && $schema->getAdditionalProperties() !== null) {
            $additionalProps = $schema->getAdditionalProperties();
            if (is_bool($additionalProps)) {
                $result['additionalProperties'] = $additionalProps;
            } elseif (is_object($additionalProps)) {
                $result['additionalProperties'] = static::serializeSchema($additionalProps);
            }
        }

        if (static::hasMethod($schema, 'getMinimum') && $schema->getMinimum() !== null) {
            $result['minimum'] = $schema->getMinimum();
        }

        if (static::hasMethod($schema, 'getMaximum') && $schema->getMaximum() !== null) {
            $result['maximum'] = $schema->getMaximum();
        }

        if (static::hasMethod($schema, 'getPattern') && $schema->getPattern()) {
            $result['pattern'] = $schema->getPattern();
        }

        if (static::hasMethod($schema, 'getMinLength') && $schema->getMinLength() !== null) {
            $result['minLength'] = $schema->getMinLength();
        }

        if (static::hasMethod($schema, 'getMaxLength') && $schema->getMaxLength() !== null) {
            $result['maxLength'] = $schema->getMaxLength();
        }

        if (static::hasMethod($schema, 'getOneOf') && $schema->getOneOf()) {
            $result['oneOf'] = [];
            foreach ($schema->getOneOf() as $schema) {
                $result['oneOf'][] = static::serializeSchema($schema);
            }
        }

        if (static::hasMethod($schema, 'getAnyOf') && $schema->getAnyOf()) {
            $result['anyOf'] = [];
            foreach ($schema->getAnyOf() as $schema) {
                $result['anyOf'][] = static::serializeSchema($schema);
            }
        }

        if (static::hasMethod($schema, 'getAllOf') && $schema->getAllOf()) {
            $result['allOf'] = [];
            foreach ($schema->getAllOf() as $schema) {
                $result['allOf'][] = static::serializeSchema($schema);
            }
        }

        return $result;
    }

    /**
     * Serialize Components object
     */
    private static function serializeComponents($components): array
    {
        $result = [];

        if (is_array($components)) {
            return $components;
        }

        if (! is_object($components)) {
            return $result;
        }

        if (static::hasMethod($components, 'getSchemas') && $components->getSchemas()) {
            $result['schemas'] = [];
            foreach ($components->getSchemas() as $schemaName => $schema) {
                $result['schemas'][$schemaName] = static::serializeSchema($schema);
            }
        }

        if (static::hasMethod($components, 'getResponses') && $components->getResponses()) {
            $result['responses'] = static::serializeResponses($components->getResponses());
        }

        if (static::hasMethod($components, 'getParameters') && $components->getParameters()) {
            $result['parameters'] = static::serializeParameters($components->getParameters());
        }

        if (static::hasMethod($components, 'getRequestBodies') && $components->getRequestBodies()) {
            $result['requestBodies'] = [];
            foreach ($components->getRequestBodies() as $name => $body) {
                $result['requestBodies'][$name] = static::serializeRequestBody($body);
            }
        }

        if (static::hasMethod($components, 'getSecuritySchemes') && $components->getSecuritySchemes()) {
            $result['securitySchemes'] = static::serializeSecuritySchemes($components->getSecuritySchemes());
        }

        return $result;
    }

    /**
     * Serialize Security array
     */
    private static function serializeSecurity($security): array
    {
        if (is_array($security)) {
            return $security;
        }

        return [];
    }

    /**
     * Serialize SecuritySchemes
     */
    private static function serializeSecuritySchemes($schemes): array
    {
        $result = [];

        foreach ($schemes as $name => $scheme) {
            $result[$name] = static::serializeSecurityScheme($scheme);
        }

        return $result;
    }

    /**
     * Serialize SecurityScheme object
     */
    private static function serializeSecurityScheme($scheme): array
    {
        $result = [];

        if (is_array($scheme)) {
            return $scheme;
        }

        if (! is_object($scheme)) {
            return $result;
        }

        if (static::hasMethod($scheme, 'getType') && $scheme->getType()) {
            $result['type'] = $scheme->getType();
        }

        if (static::hasMethod($scheme, 'getDescription') && $scheme->getDescription()) {
            $result['description'] = $scheme->getDescription();
        }

        if (static::hasMethod($scheme, 'getName') && $scheme->getName()) {
            $result['name'] = $scheme->getName();
        }

        if (static::hasMethod($scheme, 'getIn') && $scheme->getIn()) {
            $result['in'] = $scheme->getIn();
        }

        if (static::hasMethod($scheme, 'getScheme') && $scheme->getScheme()) {
            $result['scheme'] = $scheme->getScheme();
        }

        if (static::hasMethod($scheme, 'getBearerFormat') && $scheme->getBearerFormat()) {
            $result['bearerFormat'] = $scheme->getBearerFormat();
        }

        if (static::hasMethod($scheme, 'getFlows') && $scheme->getFlows()) {
            $result['flows'] = static::serializeFlows($scheme->getFlows());
        }

        if (static::hasMethod($scheme, 'getOpenIdConnectUrl') && $scheme->getOpenIdConnectUrl()) {
            $result['openIdConnectUrl'] = $scheme->getOpenIdConnectUrl();
        }

        return $result;
    }

    /**
     * Serialize Flows object
     */
    private static function serializeFlows($flows): array
    {
        $result = [];

        if (is_array($flows)) {
            return $flows;
        }

        if (! is_object($flows)) {
            return $result;
        }

        if (static::hasMethod($flows, 'getImplicit') && $flows->getImplicit()) {
            $result['implicit'] = static::serializeFlow($flows->getImplicit());
        }

        if (static::hasMethod($flows, 'getPassword') && $flows->getPassword()) {
            $result['password'] = static::serializeFlow($flows->getPassword());
        }

        if (static::hasMethod($flows, 'getClientCredentials') && $flows->getClientCredentials()) {
            $result['clientCredentials'] = static::serializeFlow($flows->getClientCredentials());
        }

        if (static::hasMethod($flows, 'getAuthorizationCode') && $flows->getAuthorizationCode()) {
            $result['authorizationCode'] = static::serializeFlow($flows->getAuthorizationCode());
        }

        return $result;
    }

    /**
     * Serialize Flow object
     */
    private static function serializeFlow($flow): array
    {
        $result = [];

        if (is_array($flow)) {
            return $flow;
        }

        if (! is_object($flow)) {
            return $result;
        }

        if (static::hasMethod($flow, 'getAuthorizationUrl') && $flow->getAuthorizationUrl()) {
            $result['authorizationUrl'] = $flow->getAuthorizationUrl();
        }

        if (static::hasMethod($flow, 'getTokenUrl') && $flow->getTokenUrl()) {
            $result['tokenUrl'] = $flow->getTokenUrl();
        }

        if (static::hasMethod($flow, 'getRefreshUrl') && $flow->getRefreshUrl()) {
            $result['refreshUrl'] = $flow->getRefreshUrl();
        }

        if (static::hasMethod($flow, 'getScopes') && $flow->getScopes()) {
            $result['scopes'] = $flow->getScopes();
        }

        return $result;
    }

    /**
     * Serialize Tags array
     */
    private static function serializeTags($tags): array
    {
        $result = [];

        foreach ($tags as $tag) {
            $result[] = static::serializeTag($tag);
        }

        return $result;
    }

    /**
     * Serialize Tag object
     */
    private static function serializeTag($tag): array
    {
        $result = [];

        if (is_array($tag)) {
            return $tag;
        }

        if (! is_object($tag)) {
            return $result;
        }

        if (static::hasMethod($tag, 'getName') && $tag->getName()) {
            $result['name'] = $tag->getName();
        }

        if (static::hasMethod($tag, 'getDescription') && $tag->getDescription()) {
            $result['description'] = $tag->getDescription();
        }

        if (static::hasMethod($tag, 'getExternalDocs') && $tag->getExternalDocs()) {
            $result['externalDocs'] = static::serializeExternalDocs($tag->getExternalDocs());
        }

        return $result;
    }

    /**
     * Serialize ExternalDocs object
     */
    private static function serializeExternalDocs($externalDocs): array
    {
        $result = [];

        if (is_array($externalDocs)) {
            return $externalDocs;
        }

        if (! is_object($externalDocs)) {
            return $result;
        }

        if (static::hasMethod($externalDocs, 'getDescription') && $externalDocs->getDescription()) {
            $result['description'] = $externalDocs->getDescription();
        }

        if (static::hasMethod($externalDocs, 'getUrl') && $externalDocs->getUrl()) {
            $result['url'] = $externalDocs->getUrl();
        }

        return $result;
    }
}
