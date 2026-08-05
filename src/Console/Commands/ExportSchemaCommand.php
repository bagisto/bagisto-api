<?php

declare(strict_types=1);

namespace Webkul\BagistoApi\Console\Commands;

use ApiPlatform\GraphQl\Type\FieldsBuilderEnumInterface;
use ApiPlatform\GraphQl\Type\TypesContainerInterface;
use ApiPlatform\GraphQl\Type\TypesFactoryInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use GraphQL\Type\Schema;
use GraphQL\Utils\SchemaPrinter;
use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\File;
use Webkul\BagistoApi\GraphQl\QueryScopedSchemaBuilder;
use Webkul\BagistoApi\GraphQl\ScopedSchemaBuilder;

class ExportSchemaCommand extends Command
{
    protected $signature = 'bagisto-api-platform:export-schema
        {--path= : Output directory (default: the package schema/ folder)}
        {--transport=all : Which schemas to export — all, rest, or graphql}';

    protected $description = 'Export the shop and admin API schemas — OpenAPI JSON (REST) and SDL (GraphQL) — split per surface, for Postman, tooling, and codegen.';

    private const DEPS = [
        ResourceNameCollectionFactoryInterface::class,
        ResourceMetadataCollectionFactoryInterface::class,
        TypesFactoryInterface::class,
        TypesContainerInterface::class,
        FieldsBuilderEnumInterface::class,
    ];

    private const SURFACES = [
        'shop' => '/api/shop',
        'admin' => '/api/admin',
    ];

    public function handle(): int
    {
        $path = rtrim($this->option('path') ?: dirname(__DIR__, 3).'/schema', '/');

        $transport = $this->option('transport');

        File::ensureDirectoryExists($path);

        if (in_array($transport, ['all', 'rest'], true)) {
            $this->exportRest($path);
        }

        if (in_array($transport, ['all', 'graphql'], true)) {
            $this->exportGraphQl($path);
        }

        return self::SUCCESS;
    }

    private function exportRest(string $path): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'openapi').'.json';

        $this->callSilently('api:openapi:export', ['--output' => $tmp]);

        $spec = json_decode(File::get($tmp), true);

        File::delete($tmp);

        foreach (self::SURFACES as $surface => $prefix) {
            $copy = $spec;

            $copy['paths'] = array_filter(
                $spec['paths'] ?? [],
                fn ($route) => str_starts_with($route, $prefix),
                ARRAY_FILTER_USE_KEY,
            );

            $file = "{$path}/openapi-{$surface}.json";

            File::put($file, json_encode($copy, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            $this->info("Wrote {$file} (".count($copy['paths']).' paths)');
        }
    }

    private function exportGraphQl(string $path): void
    {
        foreach (['shop' => false, 'admin' => true] as $surface => $adminScope) {
            $file = "{$path}/{$surface}.graphql";

            File::put($file, SchemaPrinter::doPrint($this->buildGraphQlSchema($adminScope)));

            $this->info("Wrote {$file}");
        }
    }

    private function buildGraphQlSchema(bool $adminScope): Schema
    {
        foreach (self::DEPS as $id) {
            Container::getInstance()->forgetInstance($id);
        }

        $builder = $adminScope
            ? new ScopedSchemaBuilder(
                $this->laravel->make(ResourceNameCollectionFactoryInterface::class),
                $this->laravel->make(ResourceMetadataCollectionFactoryInterface::class),
                $this->laravel->make(TypesFactoryInterface::class),
                $this->laravel->make(TypesContainerInterface::class),
                $this->laravel->make(FieldsBuilderEnumInterface::class),
                true,
            )
            : new QueryScopedSchemaBuilder(
                $this->laravel->make(ResourceNameCollectionFactoryInterface::class),
                $this->laravel->make(ResourceMetadataCollectionFactoryInterface::class),
                $this->laravel->make(TypesFactoryInterface::class),
                $this->laravel->make(TypesContainerInterface::class),
                $this->laravel->make(FieldsBuilderEnumInterface::class),
            );

        return $builder->getSchema();
    }
}
