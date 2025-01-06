<?php

namespace Choinek\PdfExtractApiClient\Dto;

final class StorageListDto implements ResponseDtoInterface
{
    /**
     * @param string[] $files
     */
    public function __construct(
        private readonly string $rawResponseBody,
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

        return new self(
            rawResponseBody: $responseBody,
            files: $response['files']
        );
    }

    /**
     * @return string[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @return array{files: string[]}
     */
    public function toArray(): array
    {
        return ['files' => $this->files];
    }

    public function getRawResponse(): string
    {
        return $this->rawResponseBody;
    }
}
