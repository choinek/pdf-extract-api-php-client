<?php

namespace Choinek\PdfExtractApiClient\Dto;

final class ClearCacheResponseDto implements ResponseDtoInterface
{
    public function __construct(
        private readonly string $rawResponseBody,
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
            status: $response['status']
        );
    }

    public function isSuccess(): bool
    {
        return 'OCR cache cleared' === $this->getStatus();
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function toArray(): array
    {
        return [
            'success' => $this->isSuccess(),
            'status' => $this->getStatus(),
        ];
    }

    public function getRawResponse(): string
    {
        return $this->rawResponseBody;
    }
}
