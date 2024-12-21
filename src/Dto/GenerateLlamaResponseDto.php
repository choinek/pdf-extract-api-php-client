<?php

namespace Choinek\PdfExtractApiPhpClient\Dto;

final class GenerateLlamaResponseDto implements ResponseDtoInterface
{
    /**
     * @var string The generated text from the API response
     */
    private readonly string $generatedText;

    /**
     * GenerateLlamaResponseDto constructor.
     *
     * @param array<int|string, mixed> $response The raw API response as an associative array
     *
     * @throws \InvalidArgumentException if the response does not contain a valid 'generated_text' field
     */
    public function __construct(array $response)
    {
        if (!isset($response['generated_text']) || !is_string($response['generated_text'])) {
            throw new \InvalidArgumentException('Invalid generated_text in response');
        }

        $this->generatedText = $response['generated_text'];
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
