<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Str;
use Webkul\BagistoApi\Admin\Models\AdminConfigurationSearch;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Core\Repositories\CoreConfigRepository;

/**
 * Mirrors the admin ConfigurationController::search over system_config()->getItems().
 */
class AdminConfigurationSearchProvider implements ProviderInterface
{
    public function __construct(protected CoreConfigRepository $coreConfigRepository) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return [$this->build(request()->query('query'))];
    }

    public function build(mixed $searchTerm): AdminConfigurationSearch
    {
        AdminReadPermissions::authorize(null, null, 'configuration');

        if (! is_string($searchTerm) || $searchTerm === '') {
            throw new InvalidInputException(__('bagistoapi::app.admin.configuration.search-query-required'), 422);
        }

        $results = $this->coreConfigRepository->search(system_config()->getItems(), $searchTerm);

        $result = new AdminConfigurationSearch;
        $result->query = $searchTerm;
        $result->results = array_map(fn (array $row) => [
            'title' => $row['title'],
            'url' => $row['url'],
            'slug' => $this->slugFromUrl($row['url']),
        ], $results);

        return $result;
    }

    protected function slugFromUrl(string $url): ?string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);

        if (! Str::contains($path, '/configuration/')) {
            return null;
        }

        return str_replace('/', '.', trim(Str::afterLast($path, '/configuration/'), '/'));
    }
}
