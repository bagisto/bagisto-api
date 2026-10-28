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

    /**
     * Get name.
     */
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

    /**
     * Set name.
     */
    public function setName(?string $value): void
    {
        $this->setSystemAttributeValue('name', $value);
    }

    /**
     * Get url key attribute.
     */
    public function getUrlKeyAttribute(): ?string
    {
        return $this->getSystemAttributeValue('url_key');
    }

    /**
     * Get url key.
     */
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

    /**
     * Set url key.
     */
    public function setUrl_key(?string $value): void
    {
        $this->setSystemAttributeValue('url_key', $value);
    }

    /**
     * Get status attribute.
     */
    public function getStatusAttribute(): ?bool
    {
        return $this->getSystemAttributeValue('status');
    }

    /**
     * Get status.
     */
    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['product:list'])]
    public function getStatus(): ?bool
    {
        return $this->getStatusAttribute();
    }

    /**
     * Set status.
     */
    public function setStatus(?bool $value): void
    {
        $this->setSystemAttributeValue('status', $value);
    }

    /**
     * Get description attribute.
     */
    public function getDescriptionAttribute(): ?string
    {
        return $this->getSystemAttributeValue('description');
    }

    /**
     * Get description.
     */
    #[ApiProperty(writable: true, readable: true, required: false)]
    public function getDescription(): ?string
    {
        return $this->getDescriptionAttribute();
    }

    /**
     * Set description.
     */
    public function setDescription(?string $value): void
    {
        $this->setSystemAttributeValue('description', $value);
    }

    /**
     * Laravel accessor for description_html appended attribute.
     */
    public function getDescriptionHtmlAttribute(): ?string
    {
        return $this->getDescriptionAttribute();
    }

    /**
     * Get description html.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getDescription_html(): ?string
    {
        return $this->getDescription_htmlAttribute();
    }

    /**
     * Get short description attribute.
     */
    public function getShortDescriptionAttribute(): ?string
    {
        return $this->getSystemAttributeValue('short_description');
    }

    /**
     * The short description of the product.
     */
    #[ApiProperty(
        writable: true,
        readable: true,
        required: false,
        schema: [
            'type' => 'string',
            'nullable' => true,
        ],
        openapiContext: ['nullable' => true],
        jsonSchemaContext: [
            'type' => 'string',
            'nullable' => true,
        ]
    )]
    #[Groups(['product:list'])]
    public function getShort_description(): ?string
    {
        return $this->getShort_descriptionAttribute();
    }

    /**
     * Set short description.
     */
    public function setShort_description(?string $value): void
    {
        $this->setSystemAttributeValue('short_description', $value);
    }

    /**
     * Get price attribute.
     */
    public function getPriceAttribute(): ?float
    {
        if (in_array($this->type, ['configurable', 'bundle'], true)) {
            return $this->priceFromIndex('regular_min_price');
        }

        return (float) core()->convertPrice(floatval($this->getSystemAttributeValue('price')));
    }

    /**
     * Get price.
     */
    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['product:list'])]
    public function getPrice(): ?float
    {
        return $this->getPriceAttribute();
    }

    /**
     * Set price.
     */
    public function setPrice(?float $value): void
    {
        $this->setSystemAttributeValue('price', $value);
    }

    /**
     * Get special price attribute.
     */
    public function getSpecialPriceAttribute(): ?float
    {
        $value = floatval($this->getSystemAttributeValue('special_price'));

        return $value ? (float) core()->convertPrice($value) : null;
    }

    /**
     * Get special price.
     */
    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['product:list'])]
    public function getSpecial_price(): ?float
    {
        return $this->getSpecialPriceAttribute();
    }

    /**
     * Set special price.
     */
    public function setSpecial_price(?float $value): void
    {
        $this->setSystemAttributeValue('special_price', $value);
    }

    /**
     * Get weight attribute.
     */
    public function getWeightAttribute(): ?string
    {
        return $this->getSystemAttributeValue('weight');
    }

    /**
     * Get weight.
     */
    #[ApiProperty(writable: true, readable: true)]
    public function getWeight(): ?string
    {
        return $this->getWeightAttribute();
    }

    /**
     * Set weight.
     */
    public function setWeight(?string $value): void
    {
        $this->setSystemAttributeValue('weight', $value);
    }

    /**
     * Get product number attribute.
     */
    public function getProductNumberAttribute(): ?string
    {
        return $this->getSystemAttributeValue('product_number');
    }

    /**
     * Get product number.
     */
    #[ApiProperty(writable: true, readable: true)]
    public function getProduct_number(): ?string
    {
        return $this->getProductNumberAttribute();
    }

    /**
     * Set product number.
     */
    public function setProduct_number(?string $value): void
    {
        $this->setSystemAttributeValue('product_number', $value);
    }

    /**
     * Get new attribute.
     */
    public function getNewAttribute(): ?bool
    {
        return $this->getSystemAttributeValue('new');
    }

    /**
     * Whether the product is marked as new.
     */
    #[ApiProperty(
        writable: true,
        readable: true,
        required: false,
        schema: [
            'type' => 'boolean',
            'nullable' => true,
        ],
    )]
    #[Groups(['product:list'])]
    public function getNew(): bool
    {
        return $this->getNewAttribute();
    }

    /**
     * Set new.
     */
    public function setNew(bool $value): void
    {
        $this->setSystemAttributeValue('new', $value);
    }

    /**
     * Get featured attribute.
     */
    public function getFeaturedAttribute(): ?bool
    {
        return $this->getSystemAttributeValue('featured');
    }

    /**
     * Get featured.
     */
    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['product:list'])]
    public function getFeatured(): ?bool
    {
        return $this->getFeaturedAttribute();
    }

    /**
     * Set featured.
     */
    public function setFeatured(?bool $value): void
    {
        $this->setSystemAttributeValue('featured', $value);
    }

    /**
     * Get visible individually attribute.
     */
    public function getVisibleIndividuallyAttribute(): ?bool
    {
        return $this->getSystemAttributeValue('visible_individually');
    }

    /**
     * Get visible individually.
     */
    #[ApiProperty(writable: true, readable: true)]
    public function getVisible_individually(): ?bool
    {
        return $this->getVisibleIndividuallyAttribute();
    }

    /**
     * Set visible individually.
     */
    public function setVisible_individually(?bool $value): void
    {
        $this->setSystemAttributeValue('visible_individually', $value);
    }

    /**
     * Get guest checkout attribute.
     */
    public function getGuestCheckoutAttribute(): ?bool
    {
        return $this->getSystemAttributeValue('guest_checkout');
    }

    /**
     * Get guest checkout.
     */
    #[ApiProperty(writable: true, readable: true)]
    public function getGuest_checkout(): ?bool
    {
        return $this->getGuestCheckoutAttribute();
    }

    /**
     * Set guest checkout.
     */
    public function setGuest_checkout(?bool $value): void
    {
        $this->setSystemAttributeValue('guest_checkout', $value);
    }

    /**
     * Get manage stock attribute.
     */
    public function getManageStockAttribute(): ?bool
    {
        return $this->getSystemAttributeValue('manage_stock');
    }

    /**
     * Get manage stock.
     */
    #[ApiProperty(writable: true, readable: true)]
    public function getManage_stock(): ?bool
    {
        return $this->getManageStockAttribute();
    }

    /**
     * Set manage stock.
     */
    public function setManage_stock(?bool $value): void
    {
        $this->setSystemAttributeValue('manage_stock', $value);
    }

    /**
     * Get meta title attribute.
     */
    public function getMetaTitleAttribute(): ?string
    {
        return $this->getSystemAttributeValue('meta_title');
    }

    /**
     * Get meta title.
     */
    #[ApiProperty(writable: true, readable: true)]
    public function getMeta_title(): ?string
    {
        return $this->getMetaTitleAttribute();
    }

    /**
     * Set meta title.
     */
    public function setMeta_title(?string $value): void
    {
        $this->setSystemAttributeValue('meta_title', $value);
    }

    /**
     * Get meta keywords attribute.
     */
    public function getMetaKeywordsAttribute(): ?string
    {
        return $this->getSystemAttributeValue('meta_keywords');
    }

    /**
     * Get meta keywords.
     */
    #[ApiProperty(writable: true, readable: true)]
    public function getMeta_keywords(): ?string
    {
        return $this->getMetaKeywordsAttribute();
    }

    /**
     * Set meta keywords.
     */
    public function setMeta_keywords(?string $value): void
    {
        $this->setSystemAttributeValue('meta_keywords', $value);
    }

    /**
     * Get tax category id attribute.
     */
    public function getTaxCategoryIdAttribute(): ?int
    {
        return (int) $this->getSystemAttributeValue('tax_category_id');
    }

    /**
     * Get tax category id.
     */
    #[ApiProperty(writable: true, readable: true)]
    public function getTax_category_id(): ?int
    {
        return $this->getTaxCategoryIdAttribute();
    }

    /**
     * Set tax category id.
     */
    public function setTax_category_id(?int $value): void
    {
        $this->setSystemAttributeValue('tax_category_id', $value);
    }

    /**
     * Get special price from attribute.
     */
    public function getSpecialPriceFromAttribute(): ?string
    {
        return $this->getSystemAttributeValue('special_price_from');
    }

    /**
     * Get special price from.
     */
    #[ApiProperty(writable: true, readable: true)]
    public function getSpecial_price_from(): ?string
    {
        return $this->getSpecialPriceFromAttribute();
    }

    /**
     * Set special price from.
     */
    public function setSpecial_price_from(?string $value): void
    {
        $this->setSystemAttributeValue('special_price_from', $value);
    }

    /**
     * Get special price to attribute.
     */
    public function getSpecialPriceToAttribute(): ?string
    {
        return $this->getSystemAttributeValue('special_price_to');
    }

    /**
     * Get special price to.
     */
    #[ApiProperty(writable: true, readable: true)]
    public function getSpecial_price_to(): ?string
    {
        return $this->getSpecialPriceToAttribute();
    }

    /**
     * Set special price to.
     */
    public function setSpecial_price_to(?string $value): void
    {
        $this->setSystemAttributeValue('special_price_to', $value);
    }

    /**
     * Get cost attribute.
     */
    public function getCostAttribute()
    {
        return floatval($this->getSystemAttributeValue('cost'));
    }

    /**
     * Get cost.
     */
    #[ApiProperty(writable: true, readable: false)]
    public function getCost(): ?float
    {
        return $this->getCostAttribute();
    }

    /**
     * Set cost.
     */
    public function setCost(?float $value): void
    {
        $this->setSystemAttributeValue('cost', $value);
    }

    /**
     * Get meta description attribute.
     */
    public function getMetaDescriptionAttribute(): ?string
    {
        return $this->getSystemAttributeValue('meta_description');
    }

    /**
     * Get meta description.
     */
    #[ApiProperty(writable: true, readable: true)]
    public function getMeta_description(): ?string
    {
        return $this->getMetaDescriptionAttribute();
    }

    /**
     * Set meta description.
     */
    public function setMeta_description(?string $value): void
    {
        $this->setSystemAttributeValue('meta_description', $value);
    }

    /**
     * Get length attribute.
     */
    public function getLengthAttribute(): ?string
    {
        return $this->getSystemAttributeValue('length');
    }

    /**
     * Get length.
     */
    #[ApiProperty(writable: true, readable: true)]
    public function getLength(): ?string
    {
        return $this->getLengthAttribute();
    }

    /**
     * Set length.
     */
    public function setLength(?string $value): void
    {
        $this->setSystemAttributeValue('length', $value);
    }

    /**
     * Get width attribute.
     */
    public function getWidthAttribute(): ?string
    {
        return $this->getSystemAttributeValue('width');
    }

    /**
     * Get width.
     */
    #[ApiProperty(writable: true, readable: true)]
    public function getWidth(): ?string
    {
        return $this->getWidthAttribute();
    }

    /**
     * Set width.
     */
    public function setWidth(?string $value): void
    {
        $this->setSystemAttributeValue('width', $value);
    }

    /**
     * Get height attribute.
     */
    public function getHeightAttribute(): ?string
    {
        return $this->getSystemAttributeValue('height');
    }

    /**
     * Get height.
     */
    #[ApiProperty(writable: true, readable: true)]
    public function getHeight(): ?string
    {
        return $this->getHeightAttribute();
    }

    /**
     * Set height.
     */
    public function setHeight(?string $value): void
    {
        $this->setSystemAttributeValue('height', $value);
    }

    /**
     * Get color attribute.
     */
    public function getColorAttribute()
    {
        return $this->getSystemAttributeValue('color');
    }

    /**
     * Get color.
     */
    #[ApiProperty(writable: true, readable: true)]
    public function getColor(): ?string
    {
        return $this->getColorAttribute();
    }

    /**
     * Set color.
     */
    public function setColor(?int $value): void
    {
        $this->setSystemAttributeValue('color', $value);
    }

    /**
     * Get size attribute.
     */
    public function getSizeAttribute()
    {
        $sizeValue = $this->getSystemAttributeValue('size');

        return is_array($sizeValue) ? implode(',', $sizeValue) : $sizeValue;
    }

    /**
     * Get size.
     */
    #[ApiProperty(writable: true, readable: true)]
    public function getSize(): ?string
    {
        return $this->getSizeAttribute();
    }

    /**
     * Set size.
     */
    public function setSize(?int $value): void
    {
        $this->setSystemAttributeValue('size', $value);
    }

    /**
     * Get brand attribute.
     */
    public function getBrandAttribute()
    {
        return $this->getSystemAttributeValue('brand');
    }

    /**
     * Get brand.
     */
    #[ApiProperty(writable: true, readable: true)]
    public function getBrand(): ?string
    {
        return $this->getBrandAttribute();
    }

    /**
     * Set brand.
     */
    public function setBrand(?int $value): void
    {
        $this->setSystemAttributeValue('brand', $value);
    }
}
