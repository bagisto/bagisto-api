<?php

namespace Webkul\BagistoApi\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CartOptionFileStaging
{
    public function config(): array
    {
        return config('storefront.cart.customizable_file', [
            'max_size_kb' => 2048,
            'ttl_minutes' => 60,
            'disk' => 'private',
            'stage_dir' => 'cart-uploads',
        ]);
    }

    public function cartOptionFileKey(string $token): string
    {
        return 'bagistoapi:cart-option-file:'.$token;
    }

    public function stage(UploadedFile $file, int $productId, int $optionId, int|string $ownerId): array
    {
        $cfg = $this->config();
        $ext = strtolower($file->getClientOriginalExtension() ?: ($file->extension() ?: 'bin'));
        $name = Str::random(40).'.'.$ext;
        $path = $file->storeAs($cfg['stage_dir'], $name, $cfg['disk']);

        $token = Str::random(48);

        Cache::put($this->cartOptionFileKey($token), [
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
            'product_id' => $productId,
            'option_id' => $optionId,
            'owner_id' => (string) $ownerId,
        ], now()->addMinutes((int) $cfg['ttl_minutes']));

        return ['token' => $token, 'fileName' => $file->getClientOriginalName()];
    }

    public function resolve(string $token): ?array
    {
        return Cache::get($this->cartOptionFileKey($token));
    }

    public function forget(string $token): void
    {
        Cache::forget($this->cartOptionFileKey($token));
    }

    public function deleteStaged(string $path): void
    {
        $disk = $this->config()['disk'];

        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }

    public function stagedUploadedFile(array $payload): UploadedFile
    {
        $absolute = Storage::disk($this->config()['disk'])->path($payload['path']);

        return new UploadedFile($absolute, $payload['original_name'], $payload['mime'], null, true);
    }
}
