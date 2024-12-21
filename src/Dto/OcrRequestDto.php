<?php

namespace Choinek\PdfExtractApiPhpClient\Dto;

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

    public function toArray(): array
    {
        return [
            'strategy' => $this->strategy,
            'model' => $this->model,
            'file' => $this->file->getBase64EncodedContents(),
            'ocr_cache' => $this->ocrCache,
            'prompt' => $this->prompt,
            'storage_profile' => $this->storageProfile,
            'storage_filename' => $this->storageFilename,
        ];
    }
}
