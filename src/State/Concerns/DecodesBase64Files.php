<?php

namespace Webkul\BagistoApi\State\Concerns;

use finfo;
use Symfony\Component\Mime\MimeTypes;
use Webkul\BagistoApi\Exception\InvalidInputException;

/**
 * Decodes a `data:<mime>;base64,<data>` string, the same file format the profile image accepts.
 */
trait DecodesBase64Files
{
    /**
     * @param  array<int, string>  $allowedMimeTypes  empty allows any type
     * @return array{bytes: string, mime: string, extension: string}
     */
    protected function decodeBase64File(string $dataUri, array $allowedMimeTypes, int $maxBytes = 5 * 1024 * 1024): array
    {
        if (! preg_match('/^data:[\w.+\/-]+;base64,(.+)$/s', $dataUri, $matches)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.upload.invalid-format'));
        }

        $bytes = base64_decode($matches[1], true);

        if ($bytes === false || $bytes === '') {
            throw new InvalidInputException(__('bagistoapi::app.graphql.upload.invalid-base64'));
        }

        if (strlen($bytes) > $maxBytes) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.upload.size-exceeds-limit'));
        }

        $mime = (string) (new finfo(FILEINFO_MIME_TYPE))->buffer($bytes);

        if (! empty($allowedMimeTypes) && ! in_array($mime, $allowedMimeTypes, true)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.return.invalid-image'));
        }

        return [
            'bytes' => $bytes,
            'mime' => $mime,
            'extension' => MimeTypes::getDefault()->getExtensions($mime)[0] ?? 'bin',
        ];
    }
}
