<?php

namespace Choinek\PdfExtractApiPhpClient\Dto;

final class LoadFileResponseDto implements ResponseDtoInterface
{
    public function __construct(
        private readonly string $content,
    ) {
    }

    public static function fromResponse(string $responseBody): self
    {
        $response = json_decode($responseBody, true);

        if (!isset($response['content']) || !is_string($response['content'])) {
            throw new \InvalidArgumentException('Invalid content field in response: '.$responseBody);
        }

        return new self($response['content']);
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function toArray(): array
    {
        return ['content' => $this->content];
    }
}
