<?php

namespace Choinek\PdfExtractApiPhpClient\Dto;

class GenerateLlamaRequestDto
{
    public function __construct(
        public readonly string $model,
        public readonly string $prompt,
    ) {
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
