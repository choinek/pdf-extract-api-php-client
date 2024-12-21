<?php

namespace Choinek\PdfExtractApiPhpClient;

use Choinek\PdfExtractApiPhpClient\Dto\OcrRequestDto;
use Choinek\PdfExtractApiPhpClient\Dto\GenerateLlamaRequestDto;
use Choinek\PdfExtractApiPhpClient\Dto\PullLlamaRequestDto;
use Choinek\PdfExtractApiPhpClient\Dto\OcrResponseDto;
use Choinek\PdfExtractApiPhpClient\Dto\GenerateLlamaResponseDto;
use Choinek\PdfExtractApiPhpClient\Dto\PullLlamaResponseDto;
use Choinek\PdfExtractApiPhpClient\Http\CurlWrapper;

class ApiClient
{
    public function __construct(
        private readonly CurlWrapper $curlWrapper,
        private readonly string $baseUrl,
        private readonly ?string $username = null,
        private readonly ?string $password = null,
    ) {
    }

    /**
     * @param array{
     *     headers?: array<string, string>,
     *     body?: string|null
     * } $options
     *
     * @return array<string|int, mixed>
     */
    private function request(string $method, string $endpoint, array $options = []): array
    {
        $url = rtrim($this->baseUrl, '/').$endpoint;
        $ch = $this->curlWrapper->init($url);

        $this->curlWrapper->setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        $this->curlWrapper->setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = $options['headers'] ?? [];
        $data = $options['body'] ?? null;

        $headerList = [];
        foreach ($headers as $name => $value) {
            $headerList[] = "{$name}: {$value}";
        }
        $this->curlWrapper->setopt($ch, CURLOPT_HTTPHEADER, $headerList);

        if ($this->username && $this->password) {
            $this->curlWrapper->setopt($ch, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
        }

        if (null !== $data) {
            $this->curlWrapper->setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $responseBody = $this->curlWrapper->exec($ch);

        if (!is_string($responseBody)) {
            throw new \RuntimeException('Error: '.$this->curlWrapper->error($ch));
        }

        $statusCode = $this->curlWrapper->getinfo($ch, CURLINFO_HTTP_CODE);
        if (is_numeric($statusCode)) {
            $statusCode = (int) $statusCode;
        } else {
            throw new \RuntimeException('HTTP Invalid status code');
        }
        $this->curlWrapper->close($ch);

        if ($statusCode >= 400) {
            throw new \RuntimeException(sprintf('HTTP error %s: %s', $statusCode, $responseBody));
        }

        $response = json_decode($responseBody, true);
        if (!is_array($response)) {
            throw new \RuntimeException('Invalid JSON response');
        }

        return $response;
    }

    /**
     * Calls the OCR request API and returns a validated DTO response.
     *
     * @param OcrRequestDto $dto the request DTO containing OCR parameters
     *
     * @return OcrResponseDto the response DTO containing the task ID
     */
    public function requestOcr(OcrRequestDto $dto): OcrResponseDto
    {
        $response = $this->request('POST', '/ocr/request', [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode($dto->toArray()) ?: null,
        ]);

        return new OcrResponseDto($response);
    }

    /**
     * Calls the Llama generation API and returns a validated DTO response.
     *
     * @param GenerateLlamaRequestDto $dto the request DTO containing model and prompt information
     *
     * @return GenerateLlamaResponseDto the response DTO containing the generated text and task details
     */
    public function generateLlama(GenerateLlamaRequestDto $dto): GenerateLlamaResponseDto
    {
        $response = $this->request('POST', '/llm/generate', [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode($dto->toArray()) ?: null,
        ]);

        return new GenerateLlamaResponseDto($response);
    }

    /**
     * Calls the Llama pull API to retrieve model data and returns a validated DTO response.
     *
     * @param PullLlamaRequestDto $dto the request DTO specifying the model to pull
     *
     * @return PullLlamaResponseDto the response DTO containing task ID, status, and model version
     */
    public function pullLlama(PullLlamaRequestDto $dto): PullLlamaResponseDto
    {
        $response = $this->request('POST', '/llm/pull', [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode($dto->toArray()) ?: null,
        ]);

        return new PullLlamaResponseDto($response);
    }
}
