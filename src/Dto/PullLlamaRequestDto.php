<?php

namespace Choinek\PdfExtractApiPhpClient\Dto;

class PullLlamaRequestDto
{
    public function __construct(
        public readonly string $model,
    ) {
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
