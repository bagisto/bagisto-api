<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Str;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminMenu;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\AuthenticationException;

class AdminMenuProvider implements ProviderInterface
{
    use ChecksAdminPermission;

    private const ADMIN_NAMESPACE = '\\Admin\\Models\\';

    private const STANDARD_GRAPHQL_OPS = ['collection_query', 'item_query', 'create', 'update', 'delete'];

    /**
     * Menu keys whose API route does not follow the key→path convention
     * (core menu naming and API route naming diverge). Each value MUST be a
     * real registered listing route — it is validated against the discovered
     * resource index, so a wrong/removed route resolves to null rather than a
     * dead link.
     */
    private const IRREGULAR = [
        'cms' => '/api/admin/cms/pages',
        'dashboard' => '/api/admin/dashboard/stats',
        'reporting' => '/api/admin/reporting/stats',
        'configuration' => '/api/admin/configuration/menu',
        'marketing.communications.email_templates' => '/api/admin/marketing/templates',
    ];

    /**
     * @var array<string, array<string, string|null>>|null
     */
    private static ?array $resourceIndex = null;

    public function __construct(
        protected ResourceNameCollectionFactoryInterface $resourceNameFactory,
        protected ResourceMetadataCollectionFactoryInterface $resourceMetadataFactory,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $admin = AdminAuthHelper::resolveAdmin();

        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        return [self::buildPayload($admin, $this)];
    }

    /**
     * @return array<string, mixed>
     */
    public static function buildPayload(object $admin, self $instance): array
    {
        return ['id' => 'menu', 'tree' => $instance->buildTree($admin)];
    }

    public static function toDto(array $payload): AdminMenu
    {
        $dto = new AdminMenu;
        $dto->id = $payload['id'] ?? 'menu';
        $dto->tree = $payload['tree'] ?? [];

        return $dto;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildTree(object $admin): array
    {
        $index = $this->discoverResources();
        $items = (array) config('menu.admin', []);

        $byKey = [];

        foreach ($items as $item) {
            $key = $item['key'] ?? null;

            if ($key === null || isset($byKey[$key])) {
                continue;
            }

            $byKey[$key] = [
                'key' => $key,
                'label' => isset($item['name']) ? __($item['name']) : $key,
                'icon' => ! empty($item['icon']) ? $item['icon'] : null,
                'sort' => (int) ($item['sort'] ?? 0),
                'permission' => $key,
                'apiResource' => $this->resolveApiResource($key, $index),
            ];
        }

        uasort($byKey, fn ($a, $b) => $a['sort'] <=> $b['sort']);

        return $this->filterByPermission($this->nest($byKey, null), $admin);
    }

    /**
     * Discover every admin resource's listing route + GraphQL field straight
     * from the registered API Platform metadata, so the map never drifts and
     * new resources appear automatically. Keyed by REST listing path.
     *
     * @return array<string, array<string, string|null>>
     */
    private function discoverResources(): array
    {
        if (self::$resourceIndex !== null) {
            return self::$resourceIndex;
        }

        $index = [];

        foreach ($this->resourceNameFactory->create() as $class) {
            if (! str_contains($class, self::ADMIN_NAMESPACE)) {
                continue;
            }

            try {
                $collection = $this->resourceMetadataFactory->create($class);
            } catch (\Throwable) {
                continue;
            }

            foreach ($collection as $metadata) {
                $rest = $this->restListingPath($metadata);

                if ($rest === null) {
                    continue;
                }

                $index[$rest] = [
                    'rest' => $rest,
                    'graphql' => $this->graphqlField($metadata),
                ];
            }
        }

        return self::$resourceIndex = $index;
    }

    private function restListingPath(object $metadata): ?string
    {
        $best = null;

        foreach ($metadata->getOperations() ?? [] as $op) {
            if (! $op instanceof GetCollection) {
                continue;
            }

            $template = (string) $op->getUriTemplate();

            if ($template === '' || str_contains($template, '{')) {
                continue;
            }

            $path = rtrim((string) $op->getRoutePrefix(), '/').$template;

            if ($best === null || strlen($path) < strlen($best)) {
                $best = $path;
            }
        }

        return $best;
    }

    private function graphqlField(object $metadata): ?string
    {
        $shortName = $metadata->getShortName();
        $custom = null;
        $hasCollection = false;

        foreach ($metadata->getGraphQlOperations() ?? [] as $name => $op) {
            if ($op instanceof QueryCollection) {
                $hasCollection = true;
            } elseif ($op instanceof Query && ! in_array($name, self::STANDARD_GRAPHQL_OPS, true)) {
                if ($custom === null || $name === 'stats') {
                    $custom = $name;
                }
            }
        }

        if ($custom !== null) {
            return $custom.$shortName;
        }

        if ($hasCollection) {
            return lcfirst(Str::plural($shortName));
        }

        return null;
    }

    /**
     * @param  array<string, array<string, string|null>>  $index
     * @return array<string, string|null>|null
     */
    private function resolveApiResource(string $key, array $index): ?array
    {
        foreach ($this->candidatePaths($key) as $candidate) {
            if (isset($index[$candidate])) {
                return $index[$candidate];
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function candidatePaths(string $key): array
    {
        if (isset(self::IRREGULAR[$key])) {
            return [self::IRREGULAR[$key]];
        }

        $parts = array_map(fn ($p) => str_replace('_', '-', $p), explode('.', $key));
        $candidates = ['/api/admin/'.implode('/', $parts)];

        if (count($parts) >= 3) {
            $candidates[] = '/api/admin/'.$parts[0].'/'.end($parts);
        }

        if (count($parts) >= 2) {
            $candidates[] = '/api/admin/'.implode('/', array_slice($parts, 1));
        }

        $candidates[] = '/api/admin/'.end($parts);

        return array_values(array_unique($candidates));
    }

    /**
     * @param  array<string, array<string, mixed>>  $byKey
     * @return array<int, array<string, mixed>>
     */
    private function nest(array $byKey, ?string $parent): array
    {
        $result = [];

        foreach ($byKey as $key => $node) {
            $nodeParent = str_contains($key, '.') ? substr($key, 0, strrpos($key, '.')) : null;

            if ($nodeParent !== $parent) {
                continue;
            }

            $node['children'] = $this->nest($byKey, $key);
            $result[] = $node;
        }

        return $result;
    }

    /**
     * @param  array<int, array<string, mixed>>  $nodes
     * @return array<int, array<string, mixed>>
     */
    private function filterByPermission(array $nodes, object $admin): array
    {
        $visible = [];

        foreach ($nodes as $node) {
            $children = $this->filterByPermission($node['children'] ?? [], $admin);

            if ($this->adminHasPermission($admin, $node['key']) || $children !== []) {
                $node['children'] = array_values($children);
                $visible[] = $node;
            }
        }

        return array_values($visible);
    }
}
