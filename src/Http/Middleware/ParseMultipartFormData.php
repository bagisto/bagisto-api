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

        if ($content === '' || $content === false) {
            return;
        }

        if (! preg_match('/boundary="?([^";]+)"?/', $contentType, $m)) {
            return;
        }

        $boundary = $m[1];

        $parts = array_filter(
            preg_split('/-+'.preg_quote($boundary, '/').'/', $content),
            fn ($p) => trim($p) !== '' && trim($p) !== '--'
        );

        $params = [];

        foreach ($parts as $part) {
            $part = ltrim($part, "\r\n");

            if (! str_contains($part, "\r\n\r\n")) {
                continue;
            }

            [$rawHeaders, $body] = explode("\r\n\r\n", $part, 2);

            $body = preg_replace('/\r\n$/', '', $body);

            $headers = [];

            foreach (explode("\r\n", $rawHeaders) as $header) {
                if (str_contains($header, ':')) {
                    [$key, $value] = explode(':', $header, 2);
                    $headers[strtolower(trim($key))] = trim($value);
                }
            }

            $disposition = $headers['content-disposition'] ?? '';

            if (! preg_match('/name="([^"]*)"/', $disposition, $nameMatch)) {
                continue;
            }

            $name = $nameMatch[1];

            if (preg_match('/filename="([^"]*)"/', $disposition, $fileMatch)) {
                if ($fileMatch[1] === '') {
                    continue;
                }

                $tmpPath = tempnam(sys_get_temp_dir(), 'bapi_mpu');
                file_put_contents($tmpPath, $body);

                $request->files->set($name, new UploadedFile(
                    $tmpPath,
                    $fileMatch[1],
                    $headers['content-type'] ?? null,
                    null,
                    true
                ));

                continue;
            }

            $params[$name] = $body;
        }

        if ($params !== []) {
            $request->request->add($params);
        }
    }
}
