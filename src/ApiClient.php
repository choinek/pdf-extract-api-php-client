<?php

namespace Choinek\PdfExtractApiPhpClient;

use Choinek\PdfExtractApiPhpClient\Dto\ResponseDtoInterface;
use Choinek\PdfExtractApiPhpClient\Http\CurlWrapper;
use Choinek\PdfExtractApiPhpClient\Dto\OcrUploadRequestDto;
use Choinek\PdfExtractApiPhpClient\Dto\OcrRequestDto;
use Choinek\PdfExtractApiPhpClient\Dto\OcrResponseDto;
use Choinek\PdfExtractApiPhpClient\Dto\ClearCacheResponseDto;
use Choinek\PdfExtractApiPhpClient\Dto\ListFilesResponseDto;
use Choinek\PdfExtractApiPhpClient\Dto\LoadFileResponseDto;
use Choinek\PdfExtractApiPhpClient\Dto\DeleteFileResponseDto;

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
     * @template T of ResponseDtoInterface
     * @param class-string<T> $responseDtoClass
     * @return T
     */
    protected function request(string $method, string $endpoint, string $responseDtoClass, array $options = []): ResponseDtoInterface
    {
        if (!class_exists($responseDtoClass) || !is_subclass_of($responseDtoClass, ResponseDtoInterface::class)) {
            throw new \InvalidArgumentException('Response DTO class must implement '.ResponseDtoInterface::class.'interface');
        }

        $url = rtrim($this->baseUrl, '/').$endpoint;
        $curlWrapper = $this->curlWrapper->init($url);

        $curlWrapper->setopt(CURLOPT_CUSTOMREQUEST, strtoupper($method));
        $curlWrapper->setopt(CURLOPT_RETURNTRANSFER, true);

        $headers = $options['headers'] ?? [];
        $data = $options['body'] ?? null;

        $headerList = [];
        foreach ($headers as $name => $value) {
            $headerList[] = "{$name}: {$value}";
        }
        $curlWrapper->setopt(CURLOPT_HTTPHEADER, $headerList);

        if ($this->username && $this->password) {
            $curlWrapper->setopt(CURLOPT_USERPWD, "{$this->username}:{$this->password}");
        }

        if (null !== $data) {
            $curlWrapper->setopt(CURLOPT_POSTFIELDS, $data);
        }

        $responseBody = $curlWrapper->exec();

        if (!is_string($responseBody)) {
            throw new \RuntimeException('Error: '.$this->curlWrapper->error());
        }

        $statusCode = $curlWrapper->getinfo(CURLINFO_HTTP_CODE);
        if (is_numeric($statusCode)) {
            $statusCode = (int) $statusCode;
        } else {
            throw new \RuntimeException('HTTP Invalid status code');
        }
        $curlWrapper->close();

        if ($statusCode >= 400) {
            throw new \RuntimeException(sprintf('HTTP error %s: %s', $statusCode, $responseBody));
        }

        return $responseDtoClass::fromResponse($responseBody);
    }

    public function uploadFile(OcrUploadRequestDto $dto): OcrResponseDto
    {
        return $this->request(
            'POST',
            '/ocr/upload',
            OcrResponseDto::class,
            [
                'headers' => ['Content-Type' => 'multipart/form-data'],
                'body' => $dto->toMultipartFormData(),
            ]
        );
    }

    public function requestOcr(OcrRequestDto $dto): OcrResponseDto
    {
        return $this->request(
            'POST',
            '/ocr/request',
            OcrResponseDto::class,
            [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode($dto->toArray()),
            ]
        );
    }

    public function getResult(string $taskId): OcrResponseDto
    {
        return $this->request(
            'GET',
            "/ocr/result/{$taskId}",
            OcrResponseDto::class
        );
    }

    public function clearCache(): ClearCacheResponseDto
    {
        return $this->request(
            'POST',
            '/ocr/clear_cache',
            ClearCacheResponseDto::class
        );
    }

    public function listFiles(string $storageProfile = 'default'): ListFilesResponseDto
    {
        return $this->request(
            'GET',
            '/storage/list',
            ListFilesResponseDto::class,
            [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode(['storage_profile' => $storageProfile]),
            ]
        );
    }

    public function loadFile(string $fileName, string $storageProfile = 'default'): LoadFileResponseDto
    {
        return $this->request(
            'GET',
            '/storage/load',
            LoadFileResponseDto::class,
            [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode(['file_name' => $fileName, 'storage_profile' => $storageProfile]),
            ]
        );
    }

    public function deleteFile(string $fileName, string $storageProfile = 'default'): DeleteFileResponseDto
    {
        return $this->request(
            'DELETE',
            '/storage/delete',
            DeleteFileResponseDto::class,
            [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode(['file_name' => $fileName, 'storage_profile' => $storageProfile]),
            ]
        );
    }
}
