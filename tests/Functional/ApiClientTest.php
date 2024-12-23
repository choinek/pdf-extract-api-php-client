<?php

declare(strict_types=1);

namespace Tests\Functional\Choinek\PdfExtractApiPhpClient;

use Choinek\PdfExtractApiPhpClient\ApiClient;
use Choinek\PdfExtractApiPhpClient\Dto\OcrRequestDto;
use Choinek\PdfExtractApiPhpClient\Dto\GenerateLlamaRequestDto;
use Choinek\PdfExtractApiPhpClient\Dto\PullLlamaRequestDto;
use Choinek\PdfExtractApiPhpClient\Dto\UploadFileDto;
use Choinek\PdfExtractApiPhpClient\Http\CurlWrapper;
use PHPUnit\Framework\TestCase;
use Tests\Utility\Choinek\PdfExtractApiPhpClient\AssetDownloader;

class ApiClientTest extends TestCase
{
    private const BASE_URL = 'http://localhost:8000';

    private ApiClient $apiClient;

    protected function setUp(): void
    {
        $this->apiClient = new ApiClient(new CurlWrapper(), self::BASE_URL);
        (new AssetDownloader())->setUp();
    }

    public function testRequestOcr(): void
    {
        $ocrRequest = new OcrRequestDto(
            'llama_vision',
            'llama3.2-vision',
            new UploadFileDto(
                'example-invoice.pdf',
                'application/pdf',
                __DIR__.'/../assets/external/example-invoice.pdf'
            ),
            true
        );

        $response = $this->apiClient->requestOcr($ocrRequest);

        $this->assertNotNull($response->getTaskId());
    }
}
