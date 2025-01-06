<?php

namespace Choinek\PdfExtractApiClient\Dto\OcrRequest;

class UploadFileDto
{
    public function __construct(
        public readonly string $fileName,
        public readonly string $mimeType,
        private readonly ?string $filePath = null,
        private readonly ?string $base64Content = null,
    ) {
        if (!$filePath && !$base64Content) {
            throw new \InvalidArgumentException('Either filePath or base64Content must be provided.');
        }

        if ($filePath && $base64Content) {
            throw new \InvalidArgumentException('Provide either filePath or base64Content, not both.');
        }

        if ($filePath && !file_exists($filePath)) {
            throw new \RuntimeException("File not found: {$filePath}");
        }

        if ($base64Content && !base64_decode($base64Content, true)) {
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

        return new self(
            fileName: $fileName,
            mimeType: $resolvedMimeType,
            filePath: $filePath
        );
    }

    public static function fromBase64(string $base64Content, string $fileName, string $mimeType): self
    {
        if (!base64_decode($base64Content, true)) {
            throw new \InvalidArgumentException('Invalid base64 content.');
        }

        return new self(
            fileName: $fileName,
            mimeType: $mimeType,
            base64Content: $base64Content
        );
    }

    public function getFileContents(): string
    {
        if (!empty($this->filePath)) {
            if (!file_exists($this->filePath)) {
                throw new \RuntimeException("File not found: {$this->filePath}");
            }

            $fileContents = file_get_contents($this->filePath);

            if (!$fileContents) {
                throw new \RuntimeException("Could not read file contents: {$this->filePath}");
            }

            return $fileContents;
        }

        if (!empty($this->base64Content)) {
            return base64_decode($this->base64Content);
        }

        throw new \RuntimeException('Could not read file contents.');
    }

    public function getBase64EncodedContents(): string
    {
        if (!empty($this->base64Content)) {
            return $this->base64Content;
        }

        $fileContents = $this->getFileContents();
        if (!$fileContents) {
            throw new \RuntimeException('Could not read file contents.');
        }

        return base64_encode($this->getFileContents());
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
