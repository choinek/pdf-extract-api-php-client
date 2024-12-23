<?php

namespace Choinek\PdfExtractApiPhpClient;

use Choinek\PdfExtractApiPhpClient\Dto\OcrResultResponseDto;
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
     * @param class-string<ResponseDtoInterface> $responseDtoClass
     * @param array{
     *     headers?: array<string, string>,
     *     body?: string|array<string, string>|null
     * } $options
     */
    protected function request(string $method, string $endpoint, string $responseDtoClass, array $options = []): ResponseDtoInterface
    {
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
            throw new \RuntimeException('Error: '.$curlWrapper->error());
        }

        $statusCode = $curlWrapper->getinfo(CURLINFO_HTTP_CODE);
        if (is_numeric($statusCode)) {
            $statusCode = (int) $statusCode;
        } else {
            throw new \RuntimeException('HTTP Invalid status code');
        }
        $curlWrapper->close();

        if ($statusCode >= 400) {
            throw new \RuntimeException(sprintf('%s HTTP error %s: %s', __METHOD__, $statusCode, $responseBody), $statusCode);
        }

        return $responseDtoClass::fromResponse($responseBody);
    }

    public function uploadFile(OcrUploadRequestDto $dto): OcrResponseDto
    {
        $response = $this->request(
            'POST',
            '/ocr/upload',
            OcrResponseDto::class,
            [
                'headers' => ['Content-Type' => 'multipart/form-data'],
                'body' => $dto->toMultipartFormData(),
            ]
        );

        if (!$response instanceof OcrResponseDto) {
            throw new \UnexpectedValueException('Expected instance of OcrResponseDto, got '.get_class($response));
        }

        return $response;
    }

    public function requestOcr(OcrRequestDto $dto): OcrResponseDto
    {
        $response = $this->request(
            'POST',
            '/ocr/request',
            OcrResponseDto::class,
            [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode($dto->toArray(), JSON_THROW_ON_ERROR),
            ]
        );

        if (!$response instanceof OcrResponseDto) {
            throw new \UnexpectedValueException('Expected instance of OcrRequestDto, got '.get_class($response));
        }

        return $response;
    }

    public function getResult(string $taskId): OcrResultResponseDto
    {
        $response = $this->request(
            'GET',
            "/ocr/result/{$taskId}",
            OcrResultResponseDto::class
        );

        if (!$response instanceof OcrResultResponseDto) {
            throw new \UnexpectedValueException('Expected instance of OcrResultResponseDto, got '.get_class($response));
        }

        return $response;
    }

    public function clearCache(): ClearCacheResponseDto
    {
        $response = $this->request(
            'POST',
            '/ocr/clear_cache',
            ClearCacheResponseDto::class
        );

        if (!$response instanceof ClearCacheResponseDto) {
            throw new \UnexpectedValueException('Expected instance of ClearCacheResponseDto, got '.get_class($response));
        }

        return $response;
    }

    public function listFiles(string $storageProfile = 'default'): ListFilesResponseDto
    {
        $response = $this->request(
            'GET',
            '/storage/list',
            ListFilesResponseDto::class,
            [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode(['storage_profile' => $storageProfile], JSON_THROW_ON_ERROR),
            ]
        );

        if (!$response instanceof ListFilesResponseDto) {
            throw new \UnexpectedValueException('Expected instance of ListFilesResponseDto, got '.get_class($response));
        }

        return $response;
    }

    public function loadFile(string $fileName, string $storageProfile = 'default'): LoadFileResponseDto
    {
        $response = $this->request(
            'GET',
            '/storage/load',
            LoadFileResponseDto::class,
            [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode(['file_name' => $fileName, 'storage_profile' => $storageProfile], JSON_THROW_ON_ERROR),
            ]
        );

        if (!$response instanceof LoadFileResponseDto) {
            throw new \UnexpectedValueException('Expected instance of LoadFileResponseDto, got '.get_class($response));
        }

        return $response;
    }

    public function deleteFile(string $fileName, string $storageProfile = 'default'): DeleteFileResponseDto
    {
        $response = $this->request(
            'DELETE',
            '/storage/delete',
            DeleteFileResponseDto::class,
            [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode(['file_name' => $fileName, 'storage_profile' => $storageProfile], JSON_THROW_ON_ERROR),
            ]
        );

        if (!$response instanceof DeleteFileResponseDto) {
            throw new \UnexpectedValueException('Expected instance of DeleteFileResponseDto, got '.get_class($response));
        }

        return $response;
    }
}
