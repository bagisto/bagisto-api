<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

#[ApiResource(
    shortName: 'AdminSettingsTaxRateRef',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'identifier', 'tax_rate']],
)]
class AdminSettingsTaxRateRef extends Model
{
    /**
     * The pivot table that links tax categories to their tax rates.
     *
     * @var string
     */
    protected $table = 'tax_categories_tax_rates';

    /**
     * Indicates the tax rate reference does not maintain created and updated timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The computed attributes appended when the tax rate reference is serialized.
     *
     * @var array
     */
    protected $appends = ['identifier', 'tax_rate'];

    /**
     * The native types the tax rate reference attributes are cast to.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'int',
        'tax_rate_id' => 'int',
    ];

    /**
     * The rate row memo.
     */
    private ?object $rateRowMemo = null;

    /**
     * The rate row loaded.
     */
    private bool $rateRowLoaded = false;

    /**
     * `id` is aliased to `tax_rate_id` by the parent relation's select (the pivot
     * table's own `id` column is never selected), so the node `_id` is the real.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id !== null ? (int) $this->id : null;
    }

    /**
     * Get identifier attribute.
     */
    #[ApiProperty(writable: false)]
    public function getIdentifierAttribute(): ?string
    {
        return $this->rateRow()?->identifier;
    }

    /**
     * Get tax rate attribute.
     */
    #[ApiProperty(writable: false)]
    public function getTaxRateAttribute(): ?float
    {
        $value = $this->rateRow()?->tax_rate;

        return $value !== null ? (float) $value : null;
    }

    /**
     * Rate row.
     */
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
