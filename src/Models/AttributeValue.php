<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ApiResource(
    routePrefix: '/api/shop',
    operations: [],
    graphQlOperations: []
)]
class AttributeValue extends Model
{
    protected $table = 'product_attribute_values';

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'attribute_id',
        'locale',
        'channel',
        'text_value',
        'boolean_value',
        'integer_value',
        'float_value',
        'datetime_value',
        'date_value',
        'json_value',
    ];

    /**
     * The attributes that should be hidden from arrays and JSON
     * We hide 'value' from being saved to database since it's a virtual attribute
     */
    protected $guarded = ['value'];

    protected $casts = [
        'boolean_value'  => 'boolean',
        'integer_value'  => 'integer',
        'float_value'    => 'decimal:4',
        'datetime_value' => 'datetime',
        'date_value'     => 'date',
        'json_value'     => 'array',
    ];

    /**
     * Append virtual 'value' attribute to array/JSON serialization
     */
    protected $appends = ['value'];

    /**
     * API Platform identifier
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Relationship to the attribute
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }

    /**
     * Relationship to the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Expose value as a readable/writable property for API Platform
     * This makes the virtual attribute available in BagistoApi
     */
    #[ApiProperty(writable: true, readable: true)]
    public function getValue(): mixed
    {
        return $this->getValueAttribute();
    }

    /**
     * Setter for API Platform to write to the value property
     */
    public function setValue(mixed $value): void
    {
        $this->setValueAttribute($value);
    }

    /**
     * Laravel accessor: Get the unified value (checks all value columns)
     * This is called when accessing $model->value in Laravel
     */
    public function getValueAttribute(): mixed
    {
        if (isset($this->attributes['value'])) {
            return $this->attributes['value'];
        }

        if ($this->text_value !== null) {
            return $this->text_value;
        }
        if ($this->boolean_value !== null) {
            return $this->boolean_value;
        }
        if ($this->integer_value !== null) {
            return $this->integer_value;
        }
        if ($this->float_value !== null) {
            return $this->float_value;
        }
        if ($this->datetime_value !== null) {
            return $this->datetime_value->toIso8601String();
        }
        if ($this->date_value !== null) {
            return $this->date_value->toDateString();
        }
        if ($this->json_value !== null) {
            return $this->json_value;
        }

        return null;
    }

    /**
     * Laravel mutator: Set the value (will be processed later by processor)
     */
    public function setValueAttribute(mixed $value): void
    {
        $this->attributes['value'] = $value;
    }

    /**
     * Clear all value columns
     */
    public function clearAllValues(): void
    {
        $this->text_value = null;
        $this->boolean_value = null;
        $this->integer_value = null;
        $this->float_value = null;
        $this->datetime_value = null;
        $this->date_value = null;
        $this->json_value = null;
    }

    /**
     * Set value to the appropriate column based on attribute type
     *
     * @param  mixed  $value  The value to set
     * @param  string  $attributeType  The type of attribute (text, boolean, price, etc.)
     */
    public function setValueByType(mixed $value, string $attributeType): void
    {
        $this->clearAllValues();

        switch ($attributeType) {
            case 'boolean':
                $this->boolean_value = (bool) $value;
                break;

            case 'price':
            case 'decimal':
                $this->float_value = (float) $value;
                break;

            case 'integer':
                $this->integer_value = (int) $value;
                break;

            case 'date':
                $this->date_value = $value;
                break;

            case 'datetime':
                $this->datetime_value = $value;
                break;

            case 'select':
            case 'multiselect':
            case 'checkbox':
                $this->json_value = is_array($value) ? $value : [$value];
                break;

            case 'text':
            case 'textarea':
            default:
                $this->text_value = (string) $value;
                break;
        }
    }
}
