<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Webkul\Attribute\Models\Attribute;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\BagistoApi\Admin\Dto\AdminAttributeMassDeleteInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminAttributeMassDelete;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\InvalidInputException;

/**
 * POST /api/admin/catalog/attributes/mass-delete
 * + massDeleteAdminCatalogAttributes GraphQL mutation.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Catalog\AttributeController::massDestroy:
 *   - Pre-validate all IDs: if ANY is a system attribute (is_user_defined = 0), reject the whole batch.
 *   - If all pass, delete them all.
 */
class AdminAttributeMassDeleteProcessor implements ProcessorInterface
{
    public function __construct(
        protected AttributeRepository $attributeRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $indices = $this->resolveIndices($data, $context);

        if (empty($indices)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.attribute.mass-delete-indices-required'), 422);
        }

        foreach ($indices as $index) {
            $attribute = Attribute::find((int) $index);

            if (! $attribute) {
                continue;
            }

            if (! $attribute->is_user_defined) {
                throw new InvalidInputException(
                    __('bagistoapi::app.admin.attribute.system-attribute'),
                    422,
                );
            }
        }

        $deleted = [];

        foreach ($indices as $index) {
            $id = (int) $index;

            $attribute = Attribute::find($id);

            if (! $attribute) {
                continue;
            }

            Event::dispatch('catalog.attribute.delete.before', $id);

            $this->attributeRepository->delete($id);

            Event::dispatch('catalog.attribute.delete.after', $id);

            $deleted[] = $id;
        }

        $result = new AdminAttributeMassDelete;
        $result->id = 1;
        $result->deleted = $deleted;
        $result->message = __('bagistoapi::app.admin.attribute.mass-delete-success');

        return $result;
    }

    protected function resolveIndices(mixed $data, array $context): array
    {
        if ($data instanceof AdminAttributeMassDeleteInput && ! empty($data->indices)) {
            return $data->indices;
        }

        $fromArgs = $context['args']['input']['indices']
            ?? $context['args']['indices']
            ?? null;

        if (is_array($fromArgs)) {
            return $fromArgs;
        }

        $fromBody = request()->input('indices');
        if (is_array($fromBody)) {
            return $fromBody;
        }

        return [];
    }
}
