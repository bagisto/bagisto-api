<?php

namespace Webkul\BagistoApi\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CartOptionFileStaging
{
    /**
     * Disk and directory the staged uploads live on.
     */
    public const DISK = 'private';

    /**
     * The stage dir constant.
     */
    public const STAGE_DIR = 'cart-uploads';

    /**
     * How long a staged upload stays resolvable — follows the store's session lifetime.
     */
    public function ttlMinutes(): int
    {
        return (int) config('session.lifetime', 120);
    }

    /**
     * Upload ceiling, taken from php.ini (`upload_max_filesize`) like the rest of Bagisto.
     */
    public function maxUploadBytes(): int
    {
        $limit = trim((string) core()->getMaxUploadSize());

        if ($limit === '') {
            return 0;
        }

        $value = (int) $limit;

        return match (strtoupper(substr($limit, -1))) {
            'G' => $value * 1024 ** 3,
            'M' => $value * 1024 ** 2,
            'K' => $value * 1024,
            default => $value,
        };
    }

    /**
     * Cart option file key.
     */
    public function cartOptionFileKey(string $token): string
    {
        return 'bagistoapi:cart-option-file:'.$token;
    }

    /**
     * Stage.
     */
    public function stage(UploadedFile $file, int $productId, int $optionId, int|string $ownerId): array
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: ($file->extension() ?: 'bin'));
        $name = Str::random(40).'.'.$extension;
        $path = $file->storeAs(self::STAGE_DIR, $name, self::DISK);

        $token = Str::random(48);

        Cache::put($this->cartOptionFileKey($token), [
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
            'product_id' => $productId,
            'option_id' => $optionId,
            'owner_id' => (string) $ownerId,
        ], now()->addMinutes($this->ttlMinutes()));

        return [
            'token' => $token,
            'fileName' => $file->getClientOriginalName(),
        ];
    }

    /**
     * Resolve.
     */
    public function resolve(string $token): ?array
    {
        return Cache::get($this->cartOptionFileKey($token));
    }

    /**
     * Forget.
     */
    public function forget(string $token): void
    {
        Cache::forget($this->cartOptionFileKey($token));
    }

    /**
     * Delete staged.
     */
    public function deleteStaged(string $path): void
    {
        if (Storage::disk(self::DISK)->exists($path)) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    /**
     * Staged uploaded file.
     */
    public function stagedUploadedFile(array $payload): UploadedFile
    {
        $absolute = Storage::disk(self::DISK)->path($payload['path']);

        return new UploadedFile($absolute, $payload['original_name'], $payload['mime'], null, true);
    }
}
