<?php

namespace Choinek\PdfExtractApiPhpClient\Dto;

final class ListFilesResponseDto implements ResponseDtoInterface
{
    public function __construct(
        private readonly array $files,
    ) {
    }

    public static function fromResponse(string $responseBody): self
    {
        $response = json_decode($responseBody, true);

        if (!isset($response['files']) || !is_array($response['files'])) {
            throw new \InvalidArgumentException('Invalid files field in response: '.$responseBody);
        }

        foreach ($response['files'] as $file) {
            if (!is_string($file)) {
                throw new \InvalidArgumentException('Invalid file name in response: '.$responseBody);
            }
        }

        return new self($response['files']);
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function toArray(): array
    {
        return ['files' => $this->files];
    }
}
