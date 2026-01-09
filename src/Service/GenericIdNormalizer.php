<?php

namespace Webkul\BagistoApi\Service;

/**
 * Generic service to normalize and convert IDs between formats
 *
 * Supports:
 * - Numeric format: "1", "23", "456"
 * - IRI format: "/api/shop/countries/1", "/api/shop/channels/5"
 *
 * This service is reusable across all resolvers and providers
 */
class GenericIdNormalizer
{
    /**
     * Extract numeric ID from any format (numeric or IRI)
     *
     * @param  mixed  $id  The ID to normalize (could be numeric or IRI string)
     * @return int|null The numeric ID, or null if format is invalid
     *
     * @example
     * extractNumericId("1") => 1
     * extractNumericId("/api/shop/countries/1") => 1
     * extractNumericId("/api/shop/country_states/99") => 99
     * extractNumericId("invalid") => null
     */
    public static function extractNumericId($id): ?int
    {
        // If already numeric, return as is
        if (is_numeric($id)) {
            return intval($id);
        }

        // If string, extract from IRI format
        if (is_string($id) && str_contains($id, '/')) {
            $parts = explode('/', $id);
            $lastPart = end($parts);

            if (is_numeric($lastPart)) {
                return intval($lastPart);
            }
        }

        // Invalid format
        return null;
    }

    /**
     * Convert numeric ID to IRI format
     *
     * @param  int  $id  The numeric ID
     * @param  string  $resourceName  The resource name in kebab-case (e.g., 'countries', 'country-states')
     * @return string The IRI formatted ID (e.g., '/api/shop/countries/1')
     *
     * @example
     * toIri(1, 'countries') => '/api/shop/countries/1'
     * toIri(5, 'channel-options') => '/api/shop/channel-options/5'
     */
    public static function toIri(int $id, string $resourceName): string
    {
        return "/api/shop/{$resourceName}/{$id}";
    }

    /**
     * Check if ID is in IRI format
     *
     * @param  mixed  $id  The ID to check
     * @return bool True if ID is in IRI format, false otherwise
     *
     * @example
     * isIri("/api/shop/countries/1") => true
     * isIri("1") => false
     * isIri("invalid") => false
     */
    public static function isIri($id): bool
    {
        return is_string($id) && str_starts_with($id, '/api/shop/');
    }

    /**
     * Check if ID is in numeric format
     *
     * @param  mixed  $id  The ID to check
     * @return bool True if ID is numeric, false otherwise
     *
     * @example
     * isNumeric("1") => true
     * isNumeric(1) => true
     * isNumeric("/api/shop/countries/1") => false
     */
    public static function isNumeric($id): bool
    {
        return is_numeric($id);
    }

    /**
     * Validate ID format and return normalized numeric ID
     * Throws exception if invalid format
     *
     * @param  mixed  $id  The ID to validate
     * @param  string  $errorMessageKey  Translation key for error message (without prefix)
     * @return int The numeric ID
     *
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     *
     * @example
     * validateAndExtract("1", 'country.id-required') => 1
     * validateAndExtract("/api/shop/countries/5", 'country.id-required') => 5
     * validateAndExtract("invalid", 'country.id-required') => throws exception
     */
    public static function validateAndExtract($id, string $errorMessageKey): int
    {
        $numericId = self::extractNumericId($id);

        if ($numericId === null) {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException(
                __("bagistoapi::app.graphql.{$errorMessageKey}")
            );
        }

        return $numericId;
    }
}
