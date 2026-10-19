<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

#[ApiResource(
    shortName: 'AdminCmsPageTranslation',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['locale', 'url_key', 'page_title', 'html_content', 'meta_title', 'meta_keywords', 'meta_description']],
)]
class AdminCmsPageTranslation extends Model
{
    /**
     * The table.
     */
    protected $table = 'cms_page_translations';

    /**
     * The timestamps.
     */
    public $timestamps = false;

    /**
     * The casts.
     */
    protected $casts = [
        'id' => 'int',
        'cms_page_id' => 'int',
    ];

    /**
     * Get id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get locale.
     */
    #[ApiProperty(writable: false)]
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * Get url key.
     */
    #[ApiProperty(writable: false)]
    public function getUrlKey(): ?string
    {
        return $this->url_key;
    }

    /**
     * Get page title.
     */
    #[ApiProperty(writable: false)]
    public function getPageTitle(): ?string
    {
        return $this->page_title;
    }

    /**
     * Get html content.
     */
    #[ApiProperty(writable: false)]
    public function getHtmlContent(): ?string
    {
        return $this->html_content;
    }

    /**
     * Get meta title.
     */
    #[ApiProperty(writable: false)]
    public function getMetaTitle(): ?string
    {
        return $this->meta_title;
    }

    /**
     * Get meta keywords.
     */
    #[ApiProperty(writable: false)]
    public function getMetaKeywords(): ?string
    {
        return $this->meta_keywords;
    }

    /**
     * Get meta description.
     */
    #[ApiProperty(writable: false)]
    public function getMetaDescription(): ?string
    {
        return $this->meta_description;
    }
}
