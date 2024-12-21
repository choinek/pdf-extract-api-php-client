<?php

declare(strict_types=1);

namespace Tests\Choinek\PdfExtractApiPhpClient;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Choinek\PdfExtractApiPhpClient\ApiClient;
use Choinek\PdfExtractApiPhpClient\Http\CurlWrapper;
use Choinek\PdfExtractApiPhpClient\Dto\OcrRequestDto;
use Choinek\PdfExtractApiPhpClient\Dto\OcrResponseDto;
use Choinek\PdfExtractApiPhpClient\Dto\GenerateLlamaRequestDto;
use Choinek\PdfExtractApiPhpClient\Dto\GenerateLlamaResponseDto;
use Choinek\PdfExtractApiPhpClient\Dto\PullLlamaRequestDto;
use Choinek\PdfExtractApiPhpClient\Dto\PullLlamaResponseDto;
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

        $this->curlWrapper->method('exec')->willReturn($responseBody);
        $this->curlWrapper->method('getinfo')->willReturn(200);
        $this->curlWrapper->method('setopt')->willReturn(true);
        $this->curlWrapper->expects($this->once())->method('close');

        $response = $this->apiClient->requestOcr($ocrDto);

        $this->assertInstanceOf(OcrResponseDto::class, $response);
        $this->assertSame('1234', $response->getTaskId());
    }

    public function testGenerateLlama(): void
    {
        $llamaDto = new GenerateLlamaRequestDto('llama-model', 'Extract data from PDF');

        $responseBody = json_encode(['generated_text' => 'Sample output']);

        $this->curlWrapper->method('exec')->willReturn($responseBody);
        $this->curlWrapper->method('getinfo')->willReturn(200);
        $this->curlWrapper->method('setopt')->willReturn(true);
        $this->curlWrapper->expects($this->once())->method('close');

        $response = $this->apiClient->generateLlama($llamaDto);

        $this->assertInstanceOf(GenerateLlamaResponseDto::class, $response);
        $this->assertSame('Sample output', $response->getGeneratedText());
    }

    public function testPullLlama(): void
    {
        $pullDto = new PullLlamaRequestDto('llama-model');

        $mockCurlResource = 'mock_curl_resource';
        $responseBody = json_encode(['status' => 'model pulled successfully']);

        $this->curlWrapper->method('exec')->willReturn($responseBody);
        $this->curlWrapper->method('getinfo')->willReturn(200);
        $this->curlWrapper->method('setopt')->willReturn(true);
        $this->curlWrapper->expects($this->once())->method('close');

        $response = $this->apiClient->pullLlama($pullDto);

        $this->assertInstanceOf(PullLlamaResponseDto::class, $response);
        $this->assertSame('model pulled successfully', $response->getStatus());
    }

    public function testRequestOcrWithHttpError(): void
    {
        $fileDto = UploadFileDto::fromBase64(base64_encode('PDF content'), 'file.pdf', 'application/pdf');
        $ocrDto = new OcrRequestDto('tesseract', 'model-name', $fileDto);

        $this->curlWrapper->method('exec')->willReturn(false);
        $this->curlWrapper->method('getinfo')->willReturn(500);
        $this->curlWrapper->method('error')->willReturn('Internal Server Error');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Error: Internal Server Error');

        $this->apiClient->requestOcr($ocrDto);
    }
}
