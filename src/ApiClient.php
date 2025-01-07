<?php

namespace Choinek\PdfExtractApiClient;

use Choinek\PdfExtractApiClient\Dto\LlmPullResponseDto;
use Choinek\PdfExtractApiClient\Dto\OcrResultResponseDto;
use Choinek\PdfExtractApiClient\Dto\ResponseDtoInterface;
use Choinek\PdfExtractApiClient\Exception\ApiClientException;
use Choinek\PdfExtractApiClient\Exception\ApiResponseException;
use Choinek\PdfExtractApiClient\Http\CurlWrapper;
use Choinek\PdfExtractApiClient\Dto\OcrUploadRequestDto;
use Choinek\PdfExtractApiClient\Dto\OcrRequestDto;
use Choinek\PdfExtractApiClient\Dto\OcrResponseDto;
use Choinek\PdfExtractApiClient\Dto\ClearCacheResponseDto;
use Choinek\PdfExtractApiClient\Dto\StorageListResponseDto;
use Choinek\PdfExtractApiClient\Dto\LoadFileResponseDto;
use Choinek\PdfExtractApiClient\Dto\DeleteFileResponseDto;

class ApiClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly ?string $username = null,
        private readonly ?string $password = null,
        private readonly CurlWrapper $curlWrapper = new CurlWrapper(),
    ) {
    }

    /**
     * @param class-string<ResponseDtoInterface> $responseDtoClass
     * @param array{
     *     headers?: array<string, string>,
     *     body?: array<string, string|\CURLFile>|string|null
     * } $options
     */
    protected function request(string $method, string $endpoint, string $responseDtoClass, array $options = []): ResponseDtoInterface
    {
        $url = rtrim($this->baseUrl, '/').$endpoint;
        $curlWrapper = $this->curlWrapper->init($url);
        $requestInfo = json_encode(['url' => $url, 'method' => $method, 'options' => $options]);

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
            throw new ApiClientException('Error: '.$curlWrapper->error()."\n. Request info: ".var_export($requestInfo, true));
        }

        $statusCode = $curlWrapper->getinfo(CURLINFO_HTTP_CODE);
        if (is_numeric($statusCode)) {
            $statusCode = (int) $statusCode;
        } else {
            throw new ApiResponseException("HTTP Invalid status code \n. Request info: ".var_export($requestInfo, true), 0, $responseBody);
        }
        $curlWrapper->close();

        if ($statusCode >= 400) {
            throw new ApiResponseException(sprintf('%s HTTP status code > 400 %s: %s. Request info: %s', __METHOD__, $statusCode, $responseBody, var_export($requestInfo, true)), $statusCode, $responseBody);
        }

        file_put_contents('REQSAVE', $requestInfo."\n---\n".$responseBody."\n\n\n--------------", FILE_APPEND);

        return $responseDtoClass::fromResponse($responseBody);
    }

    public function ocrUpload(OcrUploadRequestDto $dto): OcrResponseDto
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

    public function ocrRequest(OcrRequestDto $dto): OcrResponseDto
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

    public function ocrResultGetByTaskId(string $taskId): OcrResultResponseDto
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

    public function ocrClearCache(): ClearCacheResponseDto
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

    public function llmPull(string $model): LlmPullResponseDto
    {
        $response = $this->request(
            'POST',
            '/llm/pull',
            LlmPullResponseDto::class,
            [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode(['model' => $model], JSON_THROW_ON_ERROR),
            ]
        );

        if (!$response instanceof LlmPullResponseDto) {
            throw new \UnexpectedValueException('Expected instance of ClearCacheResponseDto, got '.get_class($response));
        }

        return $response;
    }

    public function storageList(string $storageProfile = 'default'): StorageListResponseDto
    {
        $response = $this->request(
            'GET',
            '/storage/list',
            StorageListResponseDto::class,
            [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode(['storage_profile' => $storageProfile], JSON_THROW_ON_ERROR),
            ]
        );

        if (!$response instanceof StorageListResponseDto) {
            throw new \UnexpectedValueException('Expected instance of ListFilesResponseDto, got '.get_class($response));
        }

        return $response;
    }

    public function storageLoadFileByName(string $fileName, string $storageProfile = 'default'): LoadFileResponseDto
    {
        $queryParams = http_build_query(['file_name' => $fileName, 'storage_profile' => $storageProfile]);
        $response = $this->request(
            'GET',
            '/storage/load?'.$queryParams,
            LoadFileResponseDto::class,
            [
                'headers' => ['Content-Type' => 'application/json'],
            ]
        );

        if (!$response instanceof LoadFileResponseDto) {
            throw new \UnexpectedValueException('Expected instance of LoadFileResponseDto, got '.get_class($response));
        }

        return $response;
    }

    public function storageDeleteFileByName(string $fileName, string $storageProfile = 'default'): DeleteFileResponseDto
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
