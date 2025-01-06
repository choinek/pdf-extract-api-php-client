<?php

declare(strict_types=1);

namespace Choinek\PdfExtractApiClient\Tests\Unit;

use Choinek\PdfExtractApiClient\Dto\OcrRequest\UploadFileDto;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Choinek\PdfExtractApiClient\ApiClient;
use Choinek\PdfExtractApiClient\Http\CurlWrapper;
use Choinek\PdfExtractApiClient\Dto\OcrRequestDto;
use Choinek\PdfExtractApiClient\Dto\OcrResponseDto;
use Choinek\PdfExtractApiClient\Dto\ClearCacheResponseDto;
use Choinek\PdfExtractApiClient\Dto\ListFilesResponseDto;
use Choinek\PdfExtractApiClient\Dto\LoadFileResponseDto;

class ApiClientTest extends TestCase
{
    private CurlWrapper&MockObject $curlWrapper;
    private ApiClient $apiClient;
    private const HTTPS_MOCK_LOCALHOST = 'https://mock.localhost';

    protected function setUp(): void
    {
        $this->curlWrapper = $this->createMock(CurlWrapper::class);
        $this->curlWrapper->method('init')->willReturn($this->curlWrapper);
        $this->apiClient = new ApiClient($this->curlWrapper, self::HTTPS_MOCK_LOCALHOST);
    }

    public function testRequestOcr(): void
    {
        $fileDto = new UploadFileDto('file.pdf', 'application/pdf', __DIR__.'/../assets/sample.pdf');
        $ocrDto = new OcrRequestDto('strategy', 'model-name', $fileDto);

        $taskId = '1234';

        $responseBody = json_encode(['task_id' => $taskId]);

        $this->curlWrapper->method('exec')->willReturn($responseBody);
        $this->curlWrapper->method('getinfo')->willReturn(200);
        $this->curlWrapper->method('setopt')->willReturn(true);
        $this->curlWrapper->expects($this->once())->method('close');

        $calls = [];
        $this->curlWrapper->expects($this->exactly(4))
            ->method('setopt')
            ->willReturnCallback(function ($option, $value) use (&$calls) {
                $calls[] = [$option, $value];

                return true;
            });

        $response = $this->apiClient->ocrRequest($ocrDto);

        $this->assertInstanceOf(OcrResponseDto::class, $response);
        $this->assertSame($taskId, $response->getTaskId());

        $this->assertSame(
            [
                [CURLOPT_CUSTOMREQUEST, 'POST'],
                [CURLOPT_RETURNTRANSFER, true],
                [CURLOPT_HTTPHEADER, ['Content-Type: application/json']],
                [CURLOPT_POSTFIELDS, json_encode($ocrDto->toArray())],
            ],
            $calls
        );
    }

    public function testClearCache(): void
    {
        $responseBody = json_encode(['success' => true]);

        $this->curlWrapper->method('exec')->willReturn($responseBody);
        $this->curlWrapper->method('getinfo')->willReturn(200);
        $this->curlWrapper->method('setopt')->willReturn(true);
        $this->curlWrapper->expects($this->once())->method('close');

        $response = $this->apiClient->ocrClearCache();

        $this->assertInstanceOf(ClearCacheResponseDto::class, $response);
        $this->assertTrue($response->isSuccess());
    }

    public function testListFiles(): void
    {
        $responseBody = json_encode(['files' => ['file1.pdf', 'file2.pdf']]);

        $this->curlWrapper->method('exec')->willReturn($responseBody);
        $this->curlWrapper->method('getinfo')->willReturn(200);
        $this->curlWrapper->method('setopt')->willReturn(true);
        $this->curlWrapper->expects($this->once())->method('close');

        $response = $this->apiClient->storageList();

        $this->assertInstanceOf(ListFilesResponseDto::class, $response);
        $this->assertSame(['file1.pdf', 'file2.pdf'], $response->getFiles());
    }

    public function testLoadFile(): void
    {
        $responseBody = json_encode(['content' => 'File content']);

        $this->curlWrapper->method('exec')->willReturn($responseBody);
        $this->curlWrapper->method('getinfo')->willReturn(200);
        $this->curlWrapper->method('setopt')->willReturn(true);
        $this->curlWrapper->expects($this->once())->method('close');

        $response = $this->apiClient->storageLoadFileByName('file1.pdf');

        $this->assertInstanceOf(LoadFileResponseDto::class, $response);
        $this->assertSame('File content', $response->getContent());
    }

    public function testRequestOcrWithHttpError(): void
    {
        $fileDto = new UploadFileDto('file.pdf', 'application/pdf', __DIR__.'/../assets/sample.pdf');

        $ocrDto = new OcrRequestDto('strategy', 'model-name', $fileDto);

        $this->curlWrapper->method('exec')->willReturn(false);
        $this->curlWrapper->method('getinfo')->willReturn(500);
        $this->curlWrapper->method('error')->willReturn('Internal Server Error');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Error: Internal Server Error');

        $this->apiClient->ocrRequest($ocrDto);
    }
}
