<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

#[ApiResource(
    shortName: 'AdminProductDetailAttributeValue',
    operations: [],
    graphQlOperations: [],

    normalizationContext: ['attributes' => [
        'id', 'attribute_id', 'code', 'admin_name', 'type', 'is_required',
        'group_code', 'value',
    ]],
)]
class AdminProductDetailAttributeValue extends Model
{
    /** @var string */
    protected $table = 'product_attribute_values';

    /** @var array */
    protected $appends = ['code', 'admin_name', 'type', 'is_required', 'group_code', 'value'];

    /** @var array */
    protected $casts = [
        'id' => 'int',
        'attribute_id' => 'int',
    ];

    /**
     * The attr row.
     */
    private ?object $attrRow = null;

    /**
     * Get id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get code attribute.
     */
    #[ApiProperty(writable: false)]
    public function getCodeAttribute(): ?string
    {
        return $this->attr()->code ?? null;
    }

    /**
     * Get admin name attribute.
     */
    #[ApiProperty(writable: false)]
    public function getAdminNameAttribute(): ?string
    {
        return $this->attr()->admin_name ?? null;
    }

    /**
     * Get type attribute.
     */
    #[ApiProperty(writable: false)]
    public function getTypeAttribute(): ?string
    {
        return $this->attr()->type ?? null;
    }

    /**
     * Get is required attribute.
     */
    #[ApiProperty(writable: false)]
    public function getIsRequiredAttribute(): bool
    {
        return (bool) ($this->attr()->is_required ?? false);
    }

    /**
     * Get group code attribute.
     */
    #[ApiProperty(writable: false)]
    public function getGroupCodeAttribute(): ?string
    {
        $groupId = $this->attr()->group_id ?? null;

        return $groupId ? DB::table('attribute_groups')->where('id', $groupId)->value('code') : null;
    }

    /** Polymorphic stored value coerced to a string scalar (per the attribute type). */
    #[ApiProperty(writable: false)]
    public function getValueAttribute(): ?string
    {
        $type = $this->attr()->type ?? 'text';
        $column = match ($type) {
            'boolean' => 'boolean_value',
            'select', 'price', 'integer' => 'integer_value',
            'decimal' => 'float_value',
            'date' => 'date_value',
            'datetime' => 'datetime_value',
            'multiselect', 'checkbox' => 'text_value',
            default => 'text_value',
        };

        $raw = $this->attributes[$column] ?? $this->attributes['text_value'] ?? null;

        return $raw !== null ? (string) $raw : null;
    }

    /**
     * Attr.
     */
    private function attr(): object
    {
        if ($this->attrRow === null) {
            $this->attrRow = DB::table('attributes')->where('id', $this->attribute_id)
                ->first(['id', 'code', 'admin_name', 'type', 'is_required']) ?? (object) [];
            $this->attrRow->group_id = DB::table('attribute_group_mappings')
                ->where('attribute_id', $this->attribute_id)->value('attribute_group_id');
        }

        return $this->attrRow;
    }
}
