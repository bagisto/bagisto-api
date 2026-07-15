<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Tax rate — nested sub-resource of AdminSettingsTaxCategory (`taxRates`
 * connection). Backed by the pivot table `tax_categories_tax_rates` as a plain
 * HasMany (NOT belongsToMany) — the pivot carries its own `id` column, which a
 * belongsToMany connection node would mistakenly resolve as the node `_id`
 * instead of the real rate id. Reading the pivot directly and resolving the rate
 * fields by `tax_rate_id` makes `node { _id identifier taxRate }` resolve to the
 * actual rate (id = tax_rate_id), the same combo HasMany sub-resources use.
 *
 * `tax_rate` surfaces as `taxRate`, the rate id as `_id`, via the central converter.
 */
#[ApiResource(
    shortName: 'AdminSettingsTaxRateRef',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'identifier', 'tax_rate']],
)]
class AdminSettingsTaxRateRef extends Model
{
    /** @var string */
    protected $table = 'tax_categories_tax_rates';

    /** @var bool */
    public $timestamps = false;

    /** @var array */
    protected $appends = ['identifier', 'tax_rate'];

    /** @var array */
    protected $casts = [
        'id' => 'int',
        'tax_rate_id' => 'int',
    ];

    private ?object $rateRowMemo = null;

    private bool $rateRowLoaded = false;

    /**
     * `id` is aliased to `tax_rate_id` by the parent relation's select (the pivot
     * table's own `id` column is never selected), so the node `_id` is the real
     * rate id rather than the pivot row id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id !== null ? (int) $this->id : null;
    }

    #[ApiProperty(writable: false)]
    public function getIdentifierAttribute(): ?string
    {
        return $this->rateRow()?->identifier;
    }

    #[ApiProperty(writable: false)]
    public function getTaxRateAttribute(): ?float
    {
        $value = $this->rateRow()?->tax_rate;

        return $value !== null ? (float) $value : null;
    }

    private function rateRow(): ?object
    {
        if (! $this->rateRowLoaded) {
            $this->rateRowLoaded = true;
            $this->rateRowMemo = $this->tax_rate_id
                ? DB::table('tax_rates')->where('id', $this->tax_rate_id)->first(['id', 'identifier', 'tax_rate'])
                : null;
        }

        return $this->rateRowMemo;
    }
}
