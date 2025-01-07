<?php

declare(strict_types=1);

namespace Choinek\PdfExtractApiClient\Tests\Functional;

use Choinek\PdfExtractApiClient\ApiClient;
use Choinek\PdfExtractApiClient\Dto\OcrRequestDto;
use Choinek\PdfExtractApiClient\Dto\OcrRequest\UploadFileDto;
use Choinek\PdfExtractApiClient\Dto\OcrResult\StateEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use Choinek\PdfExtractApiClient\Tests\Utility\AssetDownloader;

class ApiClientTest extends TestCase
{
    public const TASK_TIMEOUT = 180;

    /**
     * @var string[]
     */
    public static array $models = ['llama3.2-vision'];
    private ApiClient $apiClient;

    public function __construct(
        string $name,
        private string $baseUrl = 'http://localhost:8000',
    ) {
        if (getenv('TEST_API_URL')) {
            $this->baseUrl = getenv('TEST_API_URL');
        }

        if (getenv('TEST_API_MODELS')) {
            self::$models = explode(',', getenv('TEST_API_MODELS'));
        }

        parent::__construct($name);
    }

    protected function setUp(): void
    {
        $this->apiClient = new ApiClient($this->baseUrl);
        (new AssetDownloader())->setUp();

        $this->apiClient->ocrClearCache();
    }

    private function prepareExampleRequest(string $filepath, string $model): OcrRequestDto
    {
        return new OcrRequestDto(
            strategy: 'marker',
            model: $model,
            file: UploadFileDto::fromFile($filepath)
            //   prompt: 'You are OCR. Convert image to markdown.'
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
                'Darrow Street',
                'INV/S/24/2024',
                '17/09/2024',
                '11/10/2024',
            ],
            __DIR__.'/../assets/external/example-mri.pdf' => [
                'diffusion signal abnormality',
                'Jane Mary',
                '55 years old',
                'Female',
                'Posterior fossa',
            ],
        ];

        $data = [];
        foreach ($files as $file => $textContains) {
            foreach (self::$models as $model) {
                $key = 'read '.basename($file).' with '.$model;
                $data[$key] = [
                    'filepath' => $file,
                    'textContains' => $textContains,
                    'model' => $model,
                ];
            }
        }

        return $data;
    }

    public function testPullApi(): void
    {

    }

    public function testClearCache(): void
    {
        $clearCacheResponse = $this->apiClient->ocrClearCache();

        $this->assertTrue($clearCacheResponse->isSuccess());
    }

    /**
     * @param string[] $textContains
     */
    #[DataProvider('filesToParseDataProvider')]
    #[Depends('testClearCache')]
    public function testReadTextFromImagesUsingOcrRequestMethod(string $filepath, array $textContains, string $model): void
    {
        $ocrRequest = $this->prepareExampleRequest($filepath, $model);

        $ocrRequestResponse = $this->apiClient->ocrRequest($ocrRequest);

        $taskId = $ocrRequestResponse->getTaskId();
        $this->assertNotNull($taskId);


        $start = microtime(true);
        do {
            $timeElapsed = microtime(true) - $start;
            if ($timeElapsed > self::TASK_TIMEOUT) {
                $this->fail('OCR task exceeds '.self::TASK_TIMEOUT.'s ( '.round($timeElapsed, 4).'s). Task id: '.$taskId);
            }

            if (!$ocrRequestResponse->getTaskId()) {
                $this->fail('Task ID is not set');
            }

            $ocrResultResponse = $this->apiClient->ocrResultGetByTaskId($ocrRequestResponse->getTaskId());

            if (StateEnum::FAILURE === $ocrResultResponse->getState()) {
                $this->fail('OCR failed: '.$ocrResultResponse->getStatus());
            }

            sleep(1);
        } while (StateEnum::SUCCESS !== $ocrResultResponse->getState());

        foreach ($textContains as $textContain) {
            if (null === $ocrResultResponse->getResult()) {
                $this->fail('Result is null');
            }

            $this->assertStringContainsStringIgnoringCase($textContain, $ocrResultResponse->getResult());
        }
    }

    /**
     * @depends testReadTextFromImagesUsingOcrRequestMethod
     */
    public function testStorageList(): void
    {
        $storageListResponse = $this->apiClient->storageList();
    }
}
