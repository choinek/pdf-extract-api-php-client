<?php

namespace Choinek\PdfExtractApiPhpClient;

use Choinek\PdfExtractApiPhpClient\Dto\OcrRequestDto;
use Choinek\PdfExtractApiPhpClient\Dto\GenerateLlamaRequestDto;
use Choinek\PdfExtractApiPhpClient\Dto\PullLlamaRequestDto;
use Choinek\PdfExtractApiPhpClient\Http\CurlWrapper;
use RuntimeException;

class ApiClient
{
    public function __construct(
        private readonly CurlWrapper $curlWrapper,
        private readonly string $baseUrl,
        private readonly ?string $username = null,
        private readonly ?string $password = null,
    ) {
    }

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

        if (false === $responseBody) {
            throw new RuntimeException('Error: '.$this->curlWrapper->error($ch));
        }

        $statusCode = $this->curlWrapper->getinfo($ch, CURLINFO_HTTP_CODE);
        $this->curlWrapper->close($ch);

        if ($statusCode >= 400) {
            throw new RuntimeException("HTTP error {$statusCode}: {$responseBody}");
        }

        return json_decode($responseBody, true) ?: [];
    }

    public function requestOcr(OcrRequestDto $dto): array
    {
        return $this->request('POST', '/ocr/request', [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode($dto->toArray()),
        ]);
    }

    public function generateLlama(GenerateLlamaRequestDto $dto): array
    {
        return $this->request('POST', '/llm/generate', [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode($dto->toArray()),
        ]);
    }

    public function pullLlama(PullLlamaRequestDto $dto): array
    {
        return $this->request('POST', '/llm/pull', [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode($dto->toArray()),
        ]);
    }
}
