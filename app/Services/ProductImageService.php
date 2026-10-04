<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Owns product image files on disk.
 *
 * Files are stored under a directory with a generated name, so the client
 * supplied filename (and its extension) is never trusted or reused.
 */
class ProductImageService
{
    public const string DIRECTORY = 'products';

    /**
     * Store a newly uploaded image and return its relative path.
     */
    public function store(UploadedFile $file): string
    {
        return $file->store(self::DIRECTORY, Product::IMAGE_DISK);
    }

    /**
     * Remove a stored image, ignoring paths that no longer exist.
     */
    public function delete(?string $path): void
    {
        if (blank($path)) {
            return;
        }

        $disk = Storage::disk(Product::IMAGE_DISK);

        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }

    /**
     * Replace an existing image with a new upload, removing the old file.
     */
    public function replace(?string $oldPath, UploadedFile $file): string
    {
        $newPath = $this->store($file);

        $this->delete($oldPath);

        return $newPath;
    }
}
