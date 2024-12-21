<?php

namespace Choinek\PdfExtractApiPhpClient\Dto;

interface ResponseDtoInterface
{
    /**
     * Convert the DTO to an array representation.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
