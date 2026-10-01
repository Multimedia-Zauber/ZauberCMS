<?php

declare(strict_types=1);

namespace ZauberCMS\Media;

use RuntimeException;

final class MediaStorage
{
    /** @var array<string, string> */
    private array $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'application/pdf' => 'pdf',
        'application/zip' => 'zip',
        'application/x-zip-compressed' => 'zip',
        'text/plain' => 'txt',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
    ];

    public function __construct(
        private readonly string $storagePath,
        private readonly int $maxBytes = 20_971_520,
    ) {
    }

    public function storeUploadedFile(array $file): array
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Upload failed.');
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            throw new RuntimeException('Invalid uploaded file.');
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > $this->maxBytes) {
            throw new RuntimeException('File size is not allowed.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = (string) $finfo->file($tmpName);
        if (!isset($this->allowedMimeTypes[$mimeType])) {
            throw new RuntimeException('File type is not allowed.');
        }

        $extension = $this->allowedMimeTypes[$mimeType];
        $originalName = basename((string) ($file['name'] ?? 'upload.' . $extension));
        $baseName = $this->sanitizeBaseName(pathinfo($originalName, PATHINFO_FILENAME));
        $storedName = sprintf('%s-%s.%s', $baseName, bin2hex(random_bytes(8)), $extension);

        $datePath = date('Y/m');
        $targetDirectory = rtrim($this->storagePath, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $datePath);
        if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0755, true) && !is_dir($targetDirectory)) {
            throw new RuntimeException('Media directory cannot be created.');
        }

        $targetPath = $targetDirectory . DIRECTORY_SEPARATOR . $storedName;
        if (!move_uploaded_file($tmpName, $targetPath)) {
            throw new RuntimeException('Uploaded file could not be stored.');
        }

        [$width, $height] = $this->imageDimensions($targetPath, $mimeType);

        return [
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'path' => $datePath . '/' . $storedName,
            'mime_type' => $mimeType,
            'extension' => $extension,
            'size_bytes' => $size,
            'width' => $width,
            'height' => $height,
        ];
    }

    public function delete(string $relativePath): bool
    {
        $fullPath = $this->resolvePath($relativePath);
        return is_file($fullPath) ? unlink($fullPath) : false;
    }

    public function resolvePath(string $relativePath): string
    {
        $clean = ltrim(str_replace(['..', '\\'], ['', '/'], $relativePath), '/');
        return rtrim($this->storagePath, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $clean);
    }

    private function sanitizeBaseName(string $name): string
    {
        $name = strtolower(trim($name));
        $name = preg_replace('/[^a-z0-9]+/i', '-', $name) ?? 'file';
        $name = trim($name, '-');

        return $name !== '' ? substr($name, 0, 80) : 'file';
    }

    /** @return array{0:?int,1:?int} */
    private function imageDimensions(string $path, string $mimeType): array
    {
        if (!str_starts_with($mimeType, 'image/')) {
            return [null, null];
        }

        $dimensions = @getimagesize($path);
        if ($dimensions === false) {
            return [null, null];
        }

        return [(int) $dimensions[0], (int) $dimensions[1]];
    }
}
