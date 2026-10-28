<?php

namespace Webkul\BagistoApi\Serializer;

use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class OutputOnlySnakeToCamelNameConverter implements NameConverterInterface
{
    /**
     * @var array<string, string>
     */
    private static array $normalized = [];

    /** @var array<string, string> */
    private static array $denormalized = [];

    public function normalize(string $propertyName, ?string $class = null, ?string $format = null, array $context = []): string
    {
        if (isset(self::$normalized[$propertyName])) {
            return self::$normalized[$propertyName];
        }

        $prefix = '';
        $name = $propertyName;
        if (str_starts_with($name, '_')) {
            $prefix = '_';
            $name = substr($name, 1);
        }

        return self::$normalized[$propertyName] = $prefix.lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $name))));
    }

    public function denormalize(string $propertyName, ?string $class = null, ?string $format = null, array $context = []): string
    {
        if (isset(self::$denormalized[$propertyName])) {
            return self::$denormalized[$propertyName];
        }

        $prefix = '';
        $name = $propertyName;
        if (str_starts_with($name, '_')) {
            $prefix = '_';
            $name = substr($name, 1);
        }

        return self::$denormalized[$propertyName] = $prefix.strtolower(preg_replace('/[A-Z]/', '_$0', lcfirst($name)));
    }
}
