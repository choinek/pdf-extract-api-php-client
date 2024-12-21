<?php

namespace Choinek\PdfExtractApiPhpClient\Dto;

class GenerateLlamaRequestDto
{
    public function __construct(
        public readonly string $model,
        public readonly string $prompt,
    ) {
    }

    /**
     * @return array{model: string, prompt: string}
     */
    public function toArray(): array
    {
        return [
            'model' => $this->model,
            'prompt' => $this->prompt,
        ];
    }
}
