<?php

namespace Choinek\PdfExtractApiPhpClient\Dto;

final class ClearCacheResponseDto implements ResponseDtoInterface
{
    public function __construct(
        private readonly string $rawResponseBody,
        private readonly bool $success,
        private readonly string $status,
    ) {
    }

    public static function fromResponse(string $responseBody): ClearCacheResponseDto
    {
        $response = json_decode($responseBody, true);

        if (!isset($response['status']) || !is_string($response['status'])) {
            throw new \InvalidArgumentException('Invalid status field in response: '.$responseBody);
        }

        return new self(
            rawResponseBody: $responseBody,
            success: 'OCR cache cleared' === $response['status'],
            status: $response['status']
        );
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'status' => $this->status,
        ];
    }

    public function getRawResponse(): string
    {
        return $this->rawResponseBody;
    }
}
