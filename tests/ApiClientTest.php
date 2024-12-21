<?php

declare(strict_types=1);

namespace Tests\Choinek\PdfExtractApiPhpClient;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Choinek\PdfExtractApiPhpClient\ApiClient;
use Choinek\PdfExtractApiPhpClient\Http\CurlWrapper;
use Choinek\PdfExtractApiPhpClient\Dto\OcrRequestDto;
use Choinek\PdfExtractApiPhpClient\Dto\GenerateLlamaRequestDto;
use Choinek\PdfExtractApiPhpClient\Dto\PullLlamaRequestDto;
use Choinek\PdfExtractApiPhpClient\Dto\UploadFileDto;

class ApiClientTest extends TestCase
{
    private CurlWrapper&MockObject $curlWrapper;
    private ApiClient $apiClient;
    private const BASE_URL = 'https://mock.localhost';
    private const USERNAME = 'mockUser';
    private const PASSWORD = 'mockPass';

    protected function setUp(): void
    {
        $this->curlWrapper = $this->createMock(CurlWrapper::class);
        $this->apiClient = new ApiClient($this->curlWrapper, self::BASE_URL, self::USERNAME, self::PASSWORD);
    }

    public function testRequestOcr(): void
    {
        $fileDto = UploadFileDto::fromBase64(base64_encode('PDF content'), 'file.pdf', 'application/pdf');
        $ocrDto = new OcrRequestDto('tesseract', 'model-name', $fileDto);

        $mockCurlResource = 'mock_curl_resource';
        $responseBody = json_encode(['task_id' => '1234', 'status' => 'pending']);

        $this->curlWrapper->method('init')->willReturn($mockCurlResource);
        $this->curlWrapper->method('exec')->willReturn($responseBody);
        $this->curlWrapper->method('getinfo')->willReturn(200);
        $this->curlWrapper->method('setopt')->willReturn(true);
        $this->curlWrapper->expects($this->once())->method('close');

        $response = $this->apiClient->requestOcr($ocrDto);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('task_id', $response);
        $this->assertArrayHasKey('status', $response);
        $this->assertSame('1234', $response['task_id']);
        $this->assertSame('pending', $response['status']);
    }

    public function testGenerateLlama(): void
    {
        $llamaDto = new GenerateLlamaRequestDto('llama-model', 'Extract data from PDF');

        $mockCurlResource = 'mock_curl_resource';
        $responseBody = json_encode(['generated_text' => 'Sample output']);

        $this->curlWrapper->method('init')->willReturn($mockCurlResource);
        $this->curlWrapper->method('exec')->willReturn($responseBody);
        $this->curlWrapper->method('getinfo')->willReturn(200);
        $this->curlWrapper->method('setopt')->willReturn(true);
        $this->curlWrapper->expects($this->once())->method('close');

        $response = $this->apiClient->generateLlama($llamaDto);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('generated_text', $response);
        $this->assertSame('Sample output', $response['generated_text']);
    }

    public function testPullLlama(): void
    {
        $pullDto = new PullLlamaRequestDto('llama-model');

        $mockCurlResource = 'mock_curl_resource';
        $responseBody = json_encode(['status' => 'model pulled successfully']);

        $this->curlWrapper->method('init')->willReturn($mockCurlResource);
        $this->curlWrapper->method('exec')->willReturn($responseBody);
        $this->curlWrapper->method('getinfo')->willReturn(200);
        $this->curlWrapper->method('setopt')->willReturn(true);
        $this->curlWrapper->expects($this->once())->method('close');

        $response = $this->apiClient->pullLlama($pullDto);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('status', $response);
        $this->assertSame('model pulled successfully', $response['status']);
    }

    public function testRequestOcrWithHttpError(): void
    {
        $fileDto = UploadFileDto::fromBase64(base64_encode('PDF content'), 'file.pdf', 'application/pdf');
        $ocrDto = new OcrRequestDto('tesseract', 'model-name', $fileDto);

        $mockCurlResource = 'mock_curl_resource';

        $this->curlWrapper->method('init')->willReturn($mockCurlResource);
        $this->curlWrapper->method('exec')->willReturn(false);
        $this->curlWrapper->method('getinfo')->willReturn(500);
        $this->curlWrapper->method('error')->willReturn('Internal Server Error');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Error: Internal Server Error');

        $this->apiClient->requestOcr($ocrDto);
    }
}
