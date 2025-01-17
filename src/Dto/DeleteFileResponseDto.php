<?php

namespace Choinek\PdfExtractApiClient\Dto;

final class DeleteFileResponseDto implements ResponseDtoInterface
{
    public function __construct(
        private readonly string $rawResponseBody,
        private readonly bool $success,
    ) {
    }

    public static function fromResponse(string $responseBody): self
    {
        $response = json_decode($responseBody, true);

        if (!isset($response['success']) || !is_bool($response['success'])) {
            throw new \InvalidArgumentException('Invalid success field in response: '.$responseBody);
        }

        return new self($responseBody, $response['success']);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function toArray(): array
    {
        return ['success' => $this->success];
    }

    public function getRawResponse(): string
    {
        return $this->rawResponseBody;
    }
}
