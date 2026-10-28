<?php

namespace Webkul\BagistoApi\Admin\Dto\Concerns;

trait AcceptsCamelCaseWrites
{
    /**
     * Handle the set magic method.
     */
    public function __set(string $name, mixed $value): void
    {
        $snake = strtolower((string) preg_replace('/[A-Z]/', '_$0', lcfirst($name)));

        if (
            $snake !== $name
            && property_exists($this, $snake)
        ) {
            $this->{$snake} = $value;

            return;
        }

        $this->{$name} = $value;
    }
}
