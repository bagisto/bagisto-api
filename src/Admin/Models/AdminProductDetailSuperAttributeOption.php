<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

/**
 * Super-attribute option — nested in the `superAttributes` connection
 * (`superAttributes { edges { node { options { edges { node } } } } }`).
 */
#[ApiResource(
    shortName: 'AdminProductDetailSuperAttributeOption',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'admin_name', 'swatch_value', 'sort_order']],
)]
class AdminProductDetailSuperAttributeOption extends Model
{
    /** @var string */
    protected $table = 'attribute_options';

    /** @var array */
    protected $casts = ['id' => 'int', 'sort_order' => 'int'];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }
}
