<?php

namespace Choinek\PdfExtractApiPhpClient\Dto;

final class PullLlamaResponseDto implements ResponseDtoInterface
{
    private readonly string $status;

    /**
     * @param array<string|int, mixed> $response
     */
    public function __construct(array $response)
    {
        if (!isset($response['status']) || !is_string($response['status'])) {
            throw new \InvalidArgumentException('Invalid status in response');
        }

        $this->status = $response['status'];
    }

    /**
     * @return array{
     *     status: string,
     * }
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
        ];
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
