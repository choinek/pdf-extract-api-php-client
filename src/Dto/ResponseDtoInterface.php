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

    /**
     * Create a DTO representation from API response.
     */
    public static function fromResponse(string $responseBody): ResponseDtoInterface;

    /**
     * Get the raw response body.
     */
    public function getRawResponse(): string;
}
