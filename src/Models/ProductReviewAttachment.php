<?php

namespace Webkul\BagistoApi\Models;

class ProductReviewAttachment extends \Webkul\Product\Models\ProductReviewAttachment
{
    /**
     * Override __get to expose Eloquent attributes to Serializer
     * Removing public property declarations ensures this method is called
     * instead of property reflection accessing empty declared values.
     */
    public function __get($key)
    {
        if ($this->hasAttribute($key)) {
            return $this->getAttribute($key);
        }

        return parent::__get($key);
    }

    /**
     * Override __isset to ensure isset() works correctly with __get()
     * This is critical for Symfony PropertyAccessor which checks isset() before reading.
     */
    public function __isset($key)
    {
        if ($this->hasAttribute($key)) {
            return true;
        }

        return parent::__isset($key);
    }

    /**
     * Override __set to handle attribute setting properly
     */
    public function __set($key, $value)
    {
        if (in_array($key, $this->fillable)) {
            $this->setAttribute($key, $value);
        } else {
            parent::__set($key, $value);
        }
    }
}
