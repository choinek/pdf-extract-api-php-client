<?php

declare(strict_types=1);

namespace Tests\Functional\Choinek\PdfExtractApiPhpClient;

use Choinek\PdfExtractApiPhpClient\ApiClient;
use Choinek\PdfExtractApiPhpClient\Dto\OcrRequestDto;
use Choinek\PdfExtractApiPhpClient\Dto\OcrRequest\UploadFileDto;
use Choinek\PdfExtractApiPhpClient\Http\CurlWrapper;
use PHPUnit\Framework\TestCase;
use Tests\Utility\Choinek\PdfExtractApiPhpClient\AssetDownloader;

class ApiClientTest extends TestCase
{
    private const BASE_URL = 'http://localhost:8000';
    private const TASK_TIMEOUT = 180;

    private ApiClient $apiClient;

    protected function setUp(): void
    {
        $this->apiClient = new ApiClient(new CurlWrapper(), self::BASE_URL);
        (new AssetDownloader())->setUp();
    }

    /**
     * @param array{filepath: string, textContains: string[], model: string} $testDataProvided
     */
    private function prepareExampleRequest(array $testDataProvided): OcrRequestDto
    {
        return new OcrRequestDto(
            'llama_vision',
            $testDataProvided['model'],
            UploadFileDto::fromFile($testDataProvided['filepath'])
        );
    }

    /**
     * @return array<string, array{filepath: string, textContains: string[], model: string}>
     */
    public function filesToParseDataProvider(): array
    {
        $files = [
            __DIR__.'/../assets/external/example-invoice.pdf' => [
                'Acme Invoice Ltd',
                'Darrow Street 2',
                'Invoice ID: INV/S/24/2024',
                'Issue Date: 17/09/2024',
                'Due Date: 11/10/2024',
            ],
            __DIR__.'/../assets/external/example-mri.pdf' => [
                'Clinic Information',
                'Address: 0 Maywood Ave, Maywood, NJ 00000',
                'Phone Number: (201) 725-0913',
                'Age: 55 years old (as of April 29th, 2021)',
                'Sex: Female',
            ],
        ];
        $models = [
            'llama3.1',
            'llama3.2-vision',
        ];

        $data = [];
        foreach ($files as $file => $textContains) {
            foreach ($models as $model) {
                $key = 'read '.basename($file).' with '.$model;
                $data[$key] = [
                    [
                        'filepath' => $file,
                        'textContains' => $textContains,
                        'model' => $model,
                    ],
                ];
            }
        }

        return $data;
    }

    /**
     * @dataProvider filesToParseDataProvider
     *
     * @param array{filepath: string, textContains: string[], model: string} $testDataProvided
     */
    public function testReadTextFromImagesUsingOcrRequestMethod(mixed $testDataProvided): void
    {
        $ocrRequest = $this->prepareExampleRequest($testDataProvided);

        $ocrRequestResponse = $this->apiClient->ocrRequest($ocrRequest);

        $taskId = $ocrRequestResponse->getTaskId();
        $this->assertNotNull($taskId);


        $start = microtime(true);
        do {
            $timeElapsed = microtime(true) - $start;
            if ($timeElapsed > self::TASK_TIMEOUT) {
                $this->fail('OCR task exceeds '.self::TASK_TIMEOUT.'s ( '.round($timeElapsed, 4).'s). Task id: '.$taskId);
            }

            $ocrResultResponse = $this->apiClient->ocrResultGetByTaskId($ocrRequestResponse->getTaskId());

            if ('failure' === $ocrResultResponse->getState()) {
                $this->fail('OCR failed: '.$ocrResultResponse->getStatus());
            }

            sleep(1);
        } while ('success' != $ocrResultResponse->getState());

        foreach ($testDataProvided['textContains'] as $textContains) {
            $this->assertStringContainsString($textContains, $ocrResultResponse->getResult());
        }
    }
}
