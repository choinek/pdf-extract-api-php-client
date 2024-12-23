<?php

namespace Choinek\PdfExtractApiPhpClient\Dto;

use Choinek\PdfExtractApiPhpClient\Dto\OcrRequest\UploadFileDto;

class OcrRequestDto
{
    public function __construct(
        public readonly string $strategy,
        public readonly string $model,
        public readonly UploadFileDto $file,
        public readonly bool $ocrCache = true,
        public readonly ?string $prompt = null,
        public readonly ?string $storageProfile = 'default',
        public readonly ?string $storageFilename = null,
    ) {
    }

    /**
     * @return array{strategy: string, model: string, file: string, ocr_cache: bool, prompt?: string, storage_profile?: string, storage_filename?: string}
     */
    public function toArray(): array
    {
        return array_filter([
            'strategy' => $this->strategy,
            'model' => $this->model,
            'file' => $this->file->getBase64EncodedContents(),
            'ocr_cache' => $this->ocrCache,
            'prompt' => $this->prompt,
            'storage_profile' => $this->storageProfile,
            'storage_filename' => $this->storageFilename,
        ], fn ($value) => null !== $value);
    }
}
