<?php

declare(strict_types=1);

namespace Tests\Unit\Choinek\PdfExtractApiPhpClient;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Choinek\PdfExtractApiPhpClient\ApiClient;
use Choinek\PdfExtractApiPhpClient\Http\CurlWrapper;
use Choinek\PdfExtractApiPhpClient\Dto\OcrRequestDto;
use Choinek\PdfExtractApiPhpClient\Dto\OcrResponseDto;
use Choinek\PdfExtractApiPhpClient\Dto\OcrUploadRequestDto;
use Choinek\PdfExtractApiPhpClient\Dto\ClearCacheResponseDto;
use Choinek\PdfExtractApiPhpClient\Dto\ListFilesResponseDto;
use Choinek\PdfExtractApiPhpClient\Dto\LoadFileResponseDto;

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
        $fileDto = OcrUploadRequestDto::fromFile('path/to/file.pdf', true, 'model-name', 'strategy');
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

        $response = $this->apiClient->requestOcr($ocrDto);

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

        $response = $this->apiClient->clearCache();

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

        $response = $this->apiClient->listFiles();

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

        $response = $this->apiClient->loadFile('file1.pdf');

        $this->assertInstanceOf(LoadFileResponseDto::class, $response);
        $this->assertSame('File content', $response->getContent());
    }

    public function testRequestOcrWithHttpError(): void
    {
        $fileDto = OcrUploadRequestDto::fromFile('path/to/file.pdf', true, 'model-name', 'strategy');
        $ocrDto = new OcrRequestDto('strategy', 'model-name', $fileDto);

        $this->curlWrapper->method('exec')->willReturn(false);
        $this->curlWrapper->method('getinfo')->willReturn(500);
        $this->curlWrapper->method('error')->willReturn('Internal Server Error');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Error: Internal Server Error');

        $this->apiClient->requestOcr($ocrDto);
    }
}
