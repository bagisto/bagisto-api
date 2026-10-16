<?php

namespace Webkul\BagistoApi\Tests\Feature;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Webkul\BagistoApi\Http\Middleware\ParseMultipartFormData;
use Webkul\BagistoApi\Tests\BagistoApiTest;

class ParseMultipartFormDataTest extends BagistoApiTest
{
    public function test_temporary_upload_file_is_removed_on_terminate(): void
    {
        $boundary = 'XBOUNDARYX';
        $body = "--{$boundary}\r\n"
            .'Content-Disposition: form-data; name="image"; filename="a.png"'."\r\n"
            ."Content-Type: image/png\r\n\r\n"
            ."binary\r\n"
            ."--{$boundary}--\r\n";

        $request = Request::create('/api/admin/upload', 'PUT', [], [], [], [
            'CONTENT_TYPE' => 'multipart/form-data; boundary='.$boundary,
        ], $body);

        $path = null;

        (new ParseMultipartFormData)->handle($request, function (Request $request) use (&$path) {
            $path = $request->files->get('image')->getPathname();

            return new Response('ok');
        });

        expect(is_file($path))->toBeTrue();

        $this->app->terminate();

        expect(is_file($path))->toBeFalse();
    }
}
