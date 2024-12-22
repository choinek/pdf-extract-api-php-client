<?php

namespace Choinek\PdfExtractApiPhpClient\Dto;

final class GenerateLlamaResponseDto implements ResponseDtoInterface
{
    public function __construct(
        private readonly string $generatedText,
    ) {
    }

    public static function fromResponse(string $responseBody): self
    {
        $response = json_decode($responseBody, true);

        if (!isset($response['generated_text']) || !is_string($response['generated_text'])) {
            throw new \InvalidArgumentException('Invalid generated_text in response: '.$responseBody);
        }

        return new self($response['generated_text']);
    }

    /**
     * Get the generated text.
     *
     * @return string The generated text
     */
    public function getGeneratedText(): string
    {
        return $this->generatedText;
    }

    /**
     * Convert the DTO into an array.
     *
     * @return array{generated_text: string} The DTO as an associative array
     */
    public function toArray(): array
    {
        return ['generated_text' => $this->generatedText];
    }
}
