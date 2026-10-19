<?php

namespace Webkul\BagistoApi\Metadata;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type as NativeType;

class SourceDocblockPropertyMetadataFactory implements PropertyMetadataFactoryInterface
{
    /**
     * The namespace prefix constant.
     */
    private const NAMESPACE_PREFIX = 'Webkul\\BagistoApi\\';

    /** @var array<class-string, array{ns:string, uses:array<string,string>, docs:array<string,string>}> */
    private static array $fileCache = [];

    /**
     * Resolved native type per "class::property", or false when the property
     * needs no rewrite.
     *
     * @var array<string, Type|false>
     */
    private static array $decisionCache = [];

    /**
     * Fully-resolved ApiProperty per "class::property::optionsHash".
     *
     * @var array<string, ApiProperty>
     */
    private static array $resultCache = [];

    /**
     * Create a new instance.
     */
    public function __construct(private readonly PropertyMetadataFactoryInterface $decorated) {}

    /**
     * Create.
     */
    public function create(string $resourceClass, string $property, array $options = []): ApiProperty
    {
        $cacheKey = $resourceClass.'::'.$property.'::'
            .($options === [] ? '' : md5(serialize($options)));

        if (isset(self::$resultCache[$cacheKey])) {
            return self::$resultCache[$cacheKey];
        }

        return self::$resultCache[$cacheKey] = $this->build($resourceClass, $property, $options);
    }

    /**
     * Build.
     */
    private function build(string $resourceClass, string $property, array $options = []): ApiProperty
    {
        $metadata = $this->decorated->create($resourceClass, $property, $options);

        if (! str_starts_with($resourceClass, self::NAMESPACE_PREFIX)) {
            return $metadata;
        }

        $key = $resourceClass.'::'.$property;

        if (! array_key_exists($key, self::$decisionCache)) {
            self::$decisionCache[$key] = $this->resolveNativeType($resourceClass, $property, $metadata);
        }

        $native = self::$decisionCache[$key];

        return $native === false ? $metadata : $metadata->withNativeType($native);
    }

    /**
     * @return Type|false false = leave metadata alone
     */
    private function resolveNativeType(string $resourceClass, string $property, ApiProperty $metadata): mixed
    {
        if ($this->alreadyHasObjectElement($metadata)) {
            return false;
        }

        if (! $this->isArrayTyped($resourceClass, $property, $allowsNull)) {
            return false;
        }

        $elementClass = $this->elementClassFromSource($resourceClass, $property);

        if ($elementClass === null) {
            return false;
        }

        $native = NativeType::array(NativeType::object($elementClass));

        return $allowsNull ? NativeType::nullable($native) : $native;
    }

    /**
     * Already has object element.
     */
    private function alreadyHasObjectElement(ApiProperty $metadata): bool
    {
        $native = $metadata->getNativeType();

        return $native !== null
            && str_contains((string) $native, '\\');
    }

    /**
     * Whether array typed.
     */
    private function isArrayTyped(string $class, string $property, ?bool &$allowsNull): bool
    {
        $allowsNull = false;

        try {
            $reflectionProperty = new ReflectionProperty($class, $property);
        } catch (\Throwable) {
            return false;
        }

        $type = $reflectionProperty->getType();

        if ($type === null) {
            return false;
        }

        $allowsNull = $type->allowsNull();

        $name = $type instanceof \ReflectionNamedType ? $type->getName() : (string) $type;

        return in_array($name, ['array', 'iterable'], true);
    }

    /**
     * Element class from source.
     */
    private function elementClassFromSource(string $class, string $property): ?string
    {
        $parsed = $this->parseFile($class);

        if (
            $parsed === null
            || ! isset($parsed['docs'][$property])
        ) {
            return null;
        }

        if (! preg_match('/@var\s+([^\s*]+)/', $parsed['docs'][$property], $matches)) {
            return null;
        }

        $element = $this->elementFromVar($matches[1]);

        if ($element === null) {
            return null;
        }

        $fqcn = $this->resolveClass($element, $parsed['ns'], $parsed['uses']);

        return $fqcn !== null && (class_exists($fqcn) || interface_exists($fqcn)) ? $fqcn : null;
    }

    /** Extract the element type from an `@var` expression: array<X>, array<int,X>, X[], X[]|null. */
    private function elementFromVar(string $var): ?string
    {
        $var = trim($var);

        foreach (explode('|', $var) as $part) {
            $part = trim($part);

            if (
                $part === ''
                || strcasecmp($part, 'null') === 0
            ) {
                continue;
            }

            if (preg_match('/^array<(.+)>$/i', $part, $matches)) {
                $inner = $matches[1];
                $segments = explode(',', $inner);
                $value = trim(end($segments));

                return $this->cleanType($value);
            }

            if (preg_match('/^(.+)\[\]$/', $part, $matches)) {
                return $this->cleanType($matches[1]);
            }
        }

        return null;
    }

    /**
     * Clean type.
     */
    private function cleanType(string $type): ?string
    {
        $type = trim($type);
        $type = preg_replace('/<.*>$/', '', $type);
        $type = rtrim($type, '[]');

        if (
            $type === ''
            || in_array(strtolower($type), ['int', 'string', 'float', 'bool', 'mixed', 'array', 'object'], true)
        ) {
            return null;
        }

        return $type;
    }

    /**
     * Resolve class.
     */
    private function resolveClass(string $name, string $namespace, array $uses): ?string
    {
        if (str_starts_with($name, '\\')) {
            return ltrim($name, '\\');
        }

        $segments = explode('\\', $name);
        $first = $segments[0];

        if (isset($uses[$first])) {
            $segments[0] = $uses[$first];

            return implode('\\', $segments);
        }

        if (
            class_exists($name)
            || interface_exists($name)
        ) {
            return $name;
        }

        return $namespace !== '' ? $namespace.'\\'.$name : $name;
    }

    /**
     * @return array{ns:string, uses:array<string,string>, docs:array<string,string>}|null
     */
    private function parseFile(string $class): ?array
    {
        if (array_key_exists($class, self::$fileCache)) {
            return self::$fileCache[$class] ?: null;
        }

        try {
            $file = (new ReflectionClass($class))->getFileName();
        } catch (\Throwable) {
            $file = false;
        }

        if (
            $file === false
            || ! is_string($file)
            || ! is_readable($file)
        ) {
            return self::$fileCache[$class] = [
                'ns' => '',
                'uses' => [],
                'docs' => [],
            ];
        }

        $tokens = token_get_all(file_get_contents($file));

        $namespace = '';
        $uses = [];
        $docs = [];
        $lastDoc = null;
        $count = count($tokens);

        for ($position = 0; $position < $count; $position++) {
            $token = $tokens[$position];

            if (! is_array($token)) {
                if (
                    $token === ';'
                    || $token === '{'
                    || $token === '}'
                ) {
                    $lastDoc = null;
                }

                continue;
            }

            switch ($token[0]) {
                case T_DOC_COMMENT:
                    $lastDoc = $token[1];
                    break;

                case T_NAMESPACE:
                    $namespace = $this->readName($tokens, $position);
                    break;

                case T_USE:
                    $this->readUse($tokens, $position, $uses);
                    break;

                case T_VARIABLE:
                    $name = ltrim($token[1], '$');
                    if (
                        $lastDoc !== null
                        && ! isset($docs[$name])
                    ) {
                        $docs[$name] = $lastDoc;
                    }
                    $lastDoc = null;
                    break;
            }
        }

        return self::$fileCache[$class] = [
            'ns' => $namespace,
            'uses' => $uses,
            'docs' => $docs,
        ];
    }

    /**
     * Read name.
     */
    private function readName(array $tokens, int &$index): string
    {
        $name = '';
        $count = count($tokens);

        for ($index++; $index < $count; $index++) {
            $token = $tokens[$index];

            if (is_array($token)) {
                if (in_array($token[0], [T_STRING, T_NS_SEPARATOR, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED], true)) {
                    $name .= $token[1];

                    continue;
                }

                if ($token[0] === T_WHITESPACE) {
                    continue;
                }
            }

            break;
        }

        return trim($name, '\\');
    }

    /**
     * Read use.
     */
    private function readUse(array $tokens, int &$index, array &$uses): void
    {
        $name = '';
        $alias = '';
        $seenAs = false;
        $count = count($tokens);

        for ($index++; $index < $count; $index++) {
            $token = $tokens[$index];

            if (
                $token === ';'
                || $token === '{'
                || $token === ','
            ) {
                break;
            }

            if (! is_array($token)) {
                continue;
            }

            if ($token[0] === T_AS) {
                $seenAs = true;

                continue;
            }

            if (! in_array($token[0], [T_STRING, T_NS_SEPARATOR, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED], true)) {
                continue;
            }

            if ($seenAs) {
                $alias .= $token[1];
            } else {
                $name .= $token[1];
            }
        }

        $name = trim($name, '\\');

        if ($name === '') {
            return;
        }

        $short = $alias !== '' ? $alias : substr(strrchr('\\'.$name, '\\'), 1);
        $uses[$short] = $name;
    }
}
