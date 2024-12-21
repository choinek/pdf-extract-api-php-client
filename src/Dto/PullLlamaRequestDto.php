<?php

namespace Choinek\PdfExtractApiPhpClient\Dto;

class PullLlamaRequestDto
{
    public function __construct(
        public readonly string $model,
    ) {
    }

    /**
     * @return array{model: string}
     */
    public function toArray(): array
    {
        return[
            'model' => $this->model,
        ];
    }
}
