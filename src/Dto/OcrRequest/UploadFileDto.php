<?php

namespace Choinek\PdfExtractApiClient\Dto\OcrRequest;

class UploadFileDto
{
    public function __construct(
        public readonly string $fileName,
        public readonly string $mimeType,
        private readonly string $base64Content,
    ) {
        if (!$base64Content) {
            throw new \InvalidArgumentException('Base64 content must be provided.');
        }

        if (!base64_decode($base64Content, true)) {
            throw new \InvalidArgumentException('Invalid base64 content.');
        }
    }

    public static function fromFile(string $filePath, ?string $mimeType = null): self
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("File not found: {$filePath}");
        }

        $fileName = basename($filePath);
        $resolvedMimeType = $mimeType ?? self::resolveMimeType($filePath);

        $fileContent = file_get_contents($filePath);

        if (false === $fileContent) {
            throw new \RuntimeException("Failed to read file: {$filePath}");
        }

        return new self(
            fileName: $fileName,
            mimeType: $resolvedMimeType,
            base64Content: base64_encode($fileContent)
        );
    }

    public static function fromBase64(string $base64Content, string $fileName, string $mimeType): self
    {
        return new self(
            fileName: $fileName,
            mimeType: $mimeType,
            base64Content: $base64Content
        );
    }

    public function getBinaryFileContent(): string
    {
        return base64_decode($this->base64Content);
    }

    public function getBase64EncodedContent(): string
    {
        return $this->base64Content;
    }

    private static function resolveMimeType(string $filePath): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if (!$finfo) {
            throw new \RuntimeException("Failed to open file with finfo: {$filePath}");
        }
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        if (!$mimeType) {
            throw new \RuntimeException("Could not determine MIME type for file: {$filePath}");
        }

        return $mimeType;
    }
}
