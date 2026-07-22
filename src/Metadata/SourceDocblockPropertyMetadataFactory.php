<?php

namespace Webkul\BagistoApi\Metadata;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\TypeInfo\Type as NativeType;

/**
 * Restores the element type of `@var array<Foo>` / `Foo[]` DTO properties when the
 * runtime docblock is unavailable.
 *
 * php-fpm built with `opcache.save_comments=0` strips doc comments from bytecode, so
 * Reflection::getDocComment() returns false and symfony/type-info cannot read the
 * generic element type — a `?array $items` property then resolves to the GraphQL
 * `Iterable` scalar instead of a `[CartItem]` connection. This factory re-reads the
 * element class from the class SOURCE FILE (comments intact on disk, OPcache-immune)
 * and re-applies the collection type. No-op when the decorated factory already carries
 * the object element type (CLI / save_comments=1).
 */
class SourceDocblockPropertyMetadataFactory implements PropertyMetadataFactoryInterface
{
    private const NAMESPACE_PREFIX = 'Webkul\\BagistoApi\\';

    /** @var array<class-string, array{ns:string, uses:array<string,string>, docs:array<string,string>}> */
    private static array $fileCache = [];

    public function __construct(private readonly PropertyMetadataFactoryInterface $decorated) {}

    public function create(string $resourceClass, string $property, array $options = []): ApiProperty
    {
        $metadata = $this->decorated->create($resourceClass, $property, $options);

        if (! str_starts_with($resourceClass, self::NAMESPACE_PREFIX)) {
            return $metadata;
        }

        if ($this->alreadyHasObjectElement($metadata)) {
            return $metadata;
        }

        if (! $this->isArrayTyped($resourceClass, $property, $allowsNull)) {
            return $metadata;
        }

        $elementClass = $this->elementClassFromSource($resourceClass, $property);

        if ($elementClass === null) {
            return $metadata;
        }

        $native = NativeType::array(NativeType::object($elementClass));

        if ($allowsNull) {
            $native = NativeType::nullable($native);
        }

        return $metadata->withNativeType($native);
    }

    /** True when the decorated metadata already exposes a class element type (docblock was readable). */
    private function alreadyHasObjectElement(ApiProperty $metadata): bool
    {
        $native = $metadata->getNativeType();

        if ($native !== null && str_contains((string) $native, '\\')) {
            return true;
        }

        foreach ($metadata->getBuiltinTypes() ?? [] as $type) {
            foreach ($type->getCollectionValueTypes() as $value) {
                if ($value->getClassName() !== null) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isArrayTyped(string $class, string $property, ?bool &$allowsNull): bool
    {
        $allowsNull = false;

        try {
            $rp = new ReflectionProperty($class, $property);
        } catch (\Throwable) {
            return false;
        }

        $type = $rp->getType();

        if ($type === null) {
            return false;
        }

        $allowsNull = $type->allowsNull();

        $name = $type instanceof \ReflectionNamedType ? $type->getName() : (string) $type;

        return in_array($name, ['array', 'iterable'], true);
    }

    private function elementClassFromSource(string $class, string $property): ?string
    {
        $parsed = $this->parseFile($class);

        if ($parsed === null || ! isset($parsed['docs'][$property])) {
            return null;
        }

        if (! preg_match('/@var\s+([^\s*]+)/', $parsed['docs'][$property], $m)) {
            return null;
        }

        $element = $this->elementFromVar($m[1]);

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

        // Drop a trailing |null / |int etc — keep the array-ish part.
        foreach (explode('|', $var) as $part) {
            $part = trim($part);

            if ($part === '' || strcasecmp($part, 'null') === 0) {
                continue;
            }

            if (preg_match('/^array<(.+)>$/i', $part, $m)) {
                $inner = $m[1];
                $segments = explode(',', $inner);
                $value = trim(end($segments));

                return $this->cleanType($value);
            }

            if (preg_match('/^(.+)\[\]$/', $part, $m)) {
                return $this->cleanType($m[1]);
            }
        }

        return null;
    }

    private function cleanType(string $type): ?string
    {
        $type = trim($type);
        $type = preg_replace('/<.*>$/', '', $type);   // strip a nested generic
        $type = rtrim($type, '[]');

        if ($type === '' || in_array(strtolower($type), ['int', 'string', 'float', 'bool', 'mixed', 'array', 'object'], true)) {
            return null;
        }

        return $type;
    }

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

        if (class_exists($name) || interface_exists($name)) {
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

        if ($file === false || ! is_string($file) || ! is_readable($file)) {
            return self::$fileCache[$class] = ['ns' => '', 'uses' => [], 'docs' => []];
        }

        $tokens = token_get_all(file_get_contents($file));

        $ns = '';
        $uses = [];
        $docs = [];
        $lastDoc = null;
        $count = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            $t = $tokens[$i];

            if (is_array($t)) {
                switch ($t[0]) {
                    case T_DOC_COMMENT:
                        $lastDoc = $t[1];
                        break;

                    case T_NAMESPACE:
                        $ns = $this->readName($tokens, $i);
                        break;

                    case T_USE:
                        $this->readUse($tokens, $i, $uses);
                        break;

                    case T_VARIABLE:
                        $name = ltrim($t[1], '$');
                        if ($lastDoc !== null && ! isset($docs[$name])) {
                            $docs[$name] = $lastDoc;
                        }
                        $lastDoc = null;
                        break;
                }

                continue;
            }

            if ($t === ';' || $t === '{' || $t === '}') {
                $lastDoc = null;
            }
        }

        return self::$fileCache[$class] = compact('ns', 'uses', 'docs');
    }

    /** Read a namespace / qualified name starting at token $i, advancing $i past it. */
    private function readName(array $tokens, int &$i): string
    {
        $name = '';
        $count = count($tokens);

        for ($i++; $i < $count; $i++) {
            $t = $tokens[$i];

            if (is_array($t)) {
                if (in_array($t[0], [T_STRING, T_NS_SEPARATOR, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED], true)) {
                    $name .= $t[1];

                    continue;
                }

                if ($t[0] === T_WHITESPACE) {
                    continue;
                }
            }

            break;
        }

        return trim($name, '\\');
    }

    /** Parse a `use A\B\C;` / `use A\B\C as D;` statement into the alias map. */
    private function readUse(array $tokens, int &$i, array &$uses): void
    {
        $name = '';
        $alias = '';
        $seenAs = false;
        $count = count($tokens);

        for ($i++; $i < $count; $i++) {
            $t = $tokens[$i];

            if ($t === ';' || $t === '{' || $t === ',') {
                break;
            }

            if (is_array($t)) {
                if ($t[0] === T_AS) {
                    $seenAs = true;

                    continue;
                }

                if (in_array($t[0], [T_STRING, T_NS_SEPARATOR, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED], true)) {
                    if ($seenAs) {
                        $alias .= $t[1];
                    } else {
                        $name .= $t[1];
                    }
                }
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
