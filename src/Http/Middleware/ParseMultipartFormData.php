<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

/**
 * PHP only parses `multipart/form-data` bodies for POST, so a genuine PUT/PATCH
 * multipart request arrives with empty `$_POST`/`$_FILES` — every field reads as
 * missing (e.g. "The type field is required" on an import update). This parses the
 * raw body for PUT/PATCH multipart requests and repopulates the request bags, so
 * `request()->input()` / `request()->file()` work exactly as they do on POST.
 */
class ParseMultipartFormData
{
    public function handle(Request $request, Closure $next)
    {
        $method = strtoupper($request->getRealMethod());
        $contentType = (string) $request->headers->get('Content-Type', '');

        if (
            in_array($method, ['PUT', 'PATCH'], true)
            && str_contains($contentType, 'multipart/form-data')
            && $request->request->count() === 0
            && $request->files->count() === 0
        ) {
            $this->parse($request, $contentType);
        }

        return $next($request);
    }

    protected function parse(Request $request, string $contentType): void
    {
        $content = $request->getContent();

        if (
            $content === ''
            || $content === false
        ) {
            return;
        }

        if (! preg_match('/boundary="?([^";]+)"?/', $contentType, $matches)) {
            return;
        }

        $params = [];

        foreach ($this->splitParts($content, $matches[1]) as $part) {
            $this->readPart($request, ltrim($part, "\r\n"), $params);
        }

        if ($params !== []) {
            $request->request->add($params);
        }
    }

    /**
     * Split the raw body into the non-empty multipart parts.
     */
    protected function splitParts(string $content, string $boundary): array
    {
        return array_filter(
            preg_split('/-+'.preg_quote($boundary, '/').'/', $content),
            fn ($part) => trim($part) !== '' && trim($part) !== '--'
        );
    }

    /**
     * Read one part into either the request files or the field list.
     */
    protected function readPart(Request $request, string $part, array &$params): void
    {
        if (! str_contains($part, "\r\n\r\n")) {
            return;
        }

        [$rawHeaders, $body] = explode("\r\n\r\n", $part, 2);

        $body = preg_replace('/\r\n$/', '', $body);

        $headers = $this->parseHeaders($rawHeaders);

        $disposition = $headers['content-disposition'] ?? '';

        if (! preg_match('/name="([^"]*)"/', $disposition, $nameMatch)) {
            return;
        }

        if (! preg_match('/filename="([^"]*)"/', $disposition, $fileMatch)) {
            $params[$nameMatch[1]] = $body;

            return;
        }

        if ($fileMatch[1] === '') {
            return;
        }

        $this->storeUploadedFile($request, $nameMatch[1], $fileMatch[1], $body, $headers['content-type'] ?? null);
    }

    /**
     * Parse the header lines of one part into a lower-cased map.
     */
    protected function parseHeaders(string $rawHeaders): array
    {
        $headers = [];

        foreach (explode("\r\n", $rawHeaders) as $header) {
            if (! str_contains($header, ':')) {
                continue;
            }

            [$key, $value] = explode(':', $header, 2);

            $headers[strtolower(trim($key))] = trim($value);
        }

        return $headers;
    }

    /**
     * Write an uploaded part to a temp file that is removed when the request ends.
     */
    protected function storeUploadedFile(Request $request, string $name, string $filename, string $body, ?string $mimeType): void
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'bapi_mpu');

        file_put_contents($tmpPath, $body);

        app()->terminating(static function () use ($tmpPath): void {
            if (is_file($tmpPath)) {
                unlink($tmpPath);
            }
        });

        $request->files->set($name, new UploadedFile($tmpPath, $filename, $mimeType, null, true));
    }
}
