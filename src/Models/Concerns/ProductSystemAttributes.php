<?php

namespace Webkul\BagistoApi\Models\Concerns;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Get;
use Symfony\Component\Serializer\Annotation\Groups;

trait ProductSystemAttributes
{
    /**
     * Get name attribute.
     */
    public function getNameAttribute(): ?string
    {
        return $this->getSystemAttributeValue('name');
    }

    #[ApiProperty(
        writable: true,
        readable: true,
        required: false
    )]
    #[Groups(['product:list'])]
    public function getName(): ?string
    {
        return $this->getNameAttribute();
    }

    public function setName(?string $value): void
    {
        $this->setSystemAttributeValue('name', $value);
    }

    public function getUrlKeyAttribute(): ?string
    {
        return $this->getSystemAttributeValue('url_key');
    }

    #[ApiProperty(
        writable: true,
        readable: true,
        required: false
    )]
    #[Groups(['product:list'])]
    public function getUrl_key(): ?string
    {
        return $this->getUrlKeyAttribute();
    }

    public function setUrl_key(?string $value): void
    {
        $this->setSystemAttributeValue('url_key', $value);
    }

    public function getStatusAttribute(): ?bool
    {
        return $this->getSystemAttributeValue('status');
    }

    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['product:list'])]
    public function getStatus(): ?bool
    {
        return $this->getStatusAttribute();
    }

    public function setStatus(?bool $value): void
    {
        $this->setSystemAttributeValue('status', $value);
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->getSystemAttributeValue('description');
    }

    #[ApiProperty(writable: true, readable: true, required: false)]
    public function getDescription(): ?string
    {
        return $this->getDescriptionAttribute();
    }

    public function setDescription(?string $value): void
    {
        $this->setSystemAttributeValue('description', $value);
    }

    /**
     * Laravel accessor for description_html appended attribute
     */
    public function getDescriptionHtmlAttribute(): ?string
    {
        return $this->getDescriptionAttribute();
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getDescription_html(): ?string
    {
        return $this->getDescription_htmlAttribute();
    }

    public function getShortDescriptionAttribute(): ?string
    {
        return $this->getSystemAttributeValue('short_description');
    }

    #[ApiProperty(
        writable: true,
        readable: true,
        required: false,
        schema: ['type' => 'string', 'nullable' => true],
        openapiContext: ['nullable' => true],
        jsonSchemaContext: ['type' => 'string', 'nullable' => true]
    )]
    #[Groups(['product:list'])]
    public function getShort_description(): ?string
    {
        return $this->getShort_descriptionAttribute();
    }

    public function setShort_description(?string $value): void
    {
        $this->setSystemAttributeValue('short_description', $value);
    }

    public function getPriceAttribute(): ?float
    {
        return (float) core()->convertPrice(floatval($this->getSystemAttributeValue('price')));
    }

    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['product:list'])]
    public function getPrice(): ?float
    {
        return $this->getPriceAttribute();
    }

    public function setPrice(?float $value): void
    {
        $this->setSystemAttributeValue('price', $value);
    }

    public function getSpecialPriceAttribute(): ?float
    {
        $value = floatval($this->getSystemAttributeValue('special_price'));

        return $value ? (float) core()->convertPrice($value) : null;
    }

    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['product:list'])]
    public function getSpecial_price(): ?float
    {
        return $this->getSpecialPriceAttribute();
    }

    public function setSpecial_price(?float $value): void
    {
        $this->setSystemAttributeValue('special_price', $value);
    }

    public function getWeightAttribute(): ?string
    {
        return $this->getSystemAttributeValue('weight');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getWeight(): ?string
    {
        return $this->getWeightAttribute();
    }

    public function setWeight(?string $value): void
    {
        $this->setSystemAttributeValue('weight', $value);
    }

    public function getProductNumberAttribute(): ?string
    {
        return $this->getSystemAttributeValue('product_number');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getProduct_number(): ?string
    {
        return $this->getProductNumberAttribute();
    }

    public function setProduct_number(?string $value): void
    {
        $this->setSystemAttributeValue('product_number', $value);
    }

    public function getNewAttribute(): ?bool
    {
        return $this->getSystemAttributeValue('new');
    }

    #[ApiProperty(
        writable: true,
        readable: true,
        required: false,
        schema: ['type' => 'boolean', 'nullable' => true],
    )]
    #[Groups(['product:list'])]
    public function getNew(): bool
    {
        return $this->getNewAttribute();
    }

    public function setNew(bool $value): void
    {
        $this->setSystemAttributeValue('new', $value);
    }

    public function getFeaturedAttribute(): ?bool
    {
        return $this->getSystemAttributeValue('featured');
    }

    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['product:list'])]
    public function getFeatured(): ?bool
    {
        return $this->getFeaturedAttribute();
    }

    public function setFeatured(?bool $value): void
    {
        $this->setSystemAttributeValue('featured', $value);
    }

    public function getVisibleIndividuallyAttribute(): ?bool
    {
        return $this->getSystemAttributeValue('visible_individually');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getVisible_individually(): ?bool
    {
        return $this->getVisibleIndividuallyAttribute();
    }

    public function setVisible_individually(?bool $value): void
    {
        $this->setSystemAttributeValue('visible_individually', $value);
    }

    public function getGuestCheckoutAttribute(): ?bool
    {
        return $this->getSystemAttributeValue('guest_checkout');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getGuest_checkout(): ?bool
    {
        return $this->getGuestCheckoutAttribute();
    }

    public function setGuest_checkout(?bool $value): void
    {
        $this->setSystemAttributeValue('guest_checkout', $value);
    }

    public function getManageStockAttribute(): ?bool
    {
        return $this->getSystemAttributeValue('manage_stock');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getManage_stock(): ?bool
    {
        return $this->getManageStockAttribute();
    }

    public function setManage_stock(?bool $value): void
    {
        $this->setSystemAttributeValue('manage_stock', $value);
    }

    public function getMetaTitleAttribute(): ?string
    {
        return $this->getSystemAttributeValue('meta_title');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getMeta_title(): ?string
    {
        return $this->getMetaTitleAttribute();
    }

    public function setMeta_title(?string $value): void
    {
        $this->setSystemAttributeValue('meta_title', $value);
    }

    public function getMetaKeywordsAttribute(): ?string
    {
        return $this->getSystemAttributeValue('meta_keywords');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getMeta_keywords(): ?string
    {
        return $this->getMetaKeywordsAttribute();
    }

    public function setMeta_keywords(?string $value): void
    {
        $this->setSystemAttributeValue('meta_keywords', $value);
    }

    public function getTaxCategoryIdAttribute(): ?int
    {
        return (int) $this->getSystemAttributeValue('tax_category_id');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getTax_category_id(): ?int
    {
        return $this->getTaxCategoryIdAttribute();
    }

    public function setTax_category_id(?int $value): void
    {
        $this->setSystemAttributeValue('tax_category_id', $value);
    }

    public function getSpecialPriceFromAttribute(): ?string
    {
        return $this->getSystemAttributeValue('special_price_from');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getSpecial_price_from(): ?string
    {
        return $this->getSpecialPriceFromAttribute();
    }

    public function setSpecial_price_from(?string $value): void
    {
        $this->setSystemAttributeValue('special_price_from', $value);
    }

    public function getSpecialPriceToAttribute(): ?string
    {
        return $this->getSystemAttributeValue('special_price_to');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getSpecial_price_to(): ?string
    {
        return $this->getSpecialPriceToAttribute();
    }

    public function setSpecial_price_to(?string $value): void
    {
        $this->setSystemAttributeValue('special_price_to', $value);
    }

    public function getCostAttribute()
    {
        return floatval($this->getSystemAttributeValue('cost'));
    }

    #[ApiProperty(writable: true, readable: false)]
    public function getCost(): ?float
    {
        return $this->getCostAttribute();
    }

    public function setCost(?float $value): void
    {
        $this->setSystemAttributeValue('cost', $value);
    }

    public function getMetaDescriptionAttribute(): ?string
    {
        return $this->getSystemAttributeValue('meta_description');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getMeta_description(): ?string
    {
        return $this->getMetaDescriptionAttribute();
    }

    public function setMeta_description(?string $value): void
    {
        $this->setSystemAttributeValue('meta_description', $value);
    }

    public function getLengthAttribute(): ?string
    {
        return $this->getSystemAttributeValue('length');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getLength(): ?string
    {
        return $this->getLengthAttribute();
    }

    public function setLength(?string $value): void
    {
        $this->setSystemAttributeValue('length', $value);
    }

    public function getWidthAttribute(): ?string
    {
        return $this->getSystemAttributeValue('width');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getWidth(): ?string
    {
        return $this->getWidthAttribute();
    }

    public function setWidth(?string $value): void
    {
        $this->setSystemAttributeValue('width', $value);
    }

    public function getHeightAttribute(): ?string
    {
        return $this->getSystemAttributeValue('height');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getHeight(): ?string
    {
        return $this->getHeightAttribute();
    }

    public function setHeight(?string $value): void
    {
        $this->setSystemAttributeValue('height', $value);
    }

    public function getColorAttribute()
    {
        return $this->getSystemAttributeValue('color');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getColor(): ?int
    {
        return $this->getColorAttribute();
    }

    public function setColor(?int $value): void
    {
        $this->setSystemAttributeValue('color', $value);
    }

    public function getSizeAttribute()
    {
        $sizeValue = $this->getSystemAttributeValue('size');

        return is_array($sizeValue) ? implode(',', $sizeValue) : $sizeValue;
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getSize(): ?int
    {
        return $this->getSizeAttribute();
    }

    public function setSize(?int $value): void
    {
        $this->setSystemAttributeValue('size', $value);
    }

    public function getBrandAttribute()
    {
        return $this->getSystemAttributeValue('brand');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getBrand(): ?int
    {
        return $this->getBrandAttribute();
    }

    public function setBrand(?int $value): void
    {
        $this->setSystemAttributeValue('brand', $value);
    }
}
