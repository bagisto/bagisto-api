<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

trait SanitisesHtmlContent
{
    /**
     * Run each named field of the payload through core's `clean_content()`, as the admin panel does.
     */
    protected function sanitiseHtmlFields(array $payload, array $fields): array
    {
        foreach ($fields as $field) {
            if (is_string($payload[$field] ?? null)) {
                $payload[$field] = clean_content($payload[$field]);
            }
        }

        return $payload;
    }

    /**
     * Sanitise the named fields at the top level of the payload and inside every locale-keyed block.
     */
    protected function sanitiseLocalisedHtmlFields(array $payload, array $fields): array
    {
        $payload = $this->sanitiseHtmlFields($payload, $fields);

        foreach ($payload as $key => $value) {
            if (
                is_array($value)
                && ! array_is_list($value)
            ) {
                $payload[$key] = $this->sanitiseHtmlFields($value, $fields);
            }
        }

        return $payload;
    }
}
