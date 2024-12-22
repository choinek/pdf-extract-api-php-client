<?php

namespace Choinek\PdfExtractApiPhpClient\Dto;

final class OcrUploadRequestDto
{
    public function __construct(
        private readonly string $filePath,
        private readonly bool $ocrCache,
        private readonly string $model,
        private readonly string $strategy,
        private readonly string $storageProfile = 'default',
        private readonly ?string $storageFilename = null,
        private readonly ?string $prompt = null,
    ) {
    }

    /**
     * Convert the DTO into a multipart form-data array.
     *
     * @return array The multipart form-data representation
     */
    public function toMultipartFormData(): array
    {
        $data = [
            'file' => new \CURLFile($this->filePath),
            'ocr_cache' => $this->ocrCache ? 'true' : 'false',
            'model' => $this->model,
            'strategy' => $this->strategy,
            'storage_profile' => $this->storageProfile,
        ];

        if (null !== $this->storageFilename) {
            $data['storage_filename'] = $this->storageFilename;
        }

        if (null !== $this->prompt) {
            $data['prompt'] = $this->prompt;
        }

        return $data;
    }
}
