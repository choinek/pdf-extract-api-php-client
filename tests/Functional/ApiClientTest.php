<?php

declare(strict_types=1);

namespace Choinek\PdfExtractApiClient\Tests\Functional;

use Choinek\PdfExtractApiClient\ApiClient;
use Choinek\PdfExtractApiClient\Dto\OcrRequestDto;
use Choinek\PdfExtractApiClient\Dto\OcrRequest\UploadFileDto;
use Choinek\PdfExtractApiClient\Tests\Utility\SimilarityValidator;
use FuzzyWuzzy\Fuzz;
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
    public static array $models = ['llama3.2-vision', 'llama3.1'];

    /**
     * @var string[]
     */
    public static array $strategies = ['llama_vision', 'easyocr'];
    // public static array $strategies = ['easyocr'];

    /**
     * @var string[]
     */
    public static array $storages = ['default', 's3'];

    private ApiClient $apiClient;

    public function __construct(
        string $name,
        private string $baseUrl = 'http://localhost:8000',
        private readonly SimilarityValidator $similarityValidator = new SimilarityValidator(new Fuzz()),
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

    private function prepareExampleRequest(string $filepath, string $model, string $strategy, string $storage): OcrRequestDto
    {
        return new OcrRequestDto(
            strategy: $strategy,
            model: $model,
            file: UploadFileDto::fromFile($filepath),
            ocrCache: false,
            storageProfile: $storage,
            storageFilename: basename($filepath)
        );
    }

    /**
     * @return array<string, array{filepath: string, textContains: string[], model: string}>
     */
    public static function filesToParseDataProvider(): array
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
                foreach (self::$strategies as $strategy) {
                    foreach (self::$storages as $storage) {
                        $key = 'read '.basename($file).' with extract strategy: '.$strategy.' and model: '.$model.' saved in '.$storage;
                        $data[$key] = [
                            'filepath' => $file,
                            'textContains' => $textContains,
                            'model' => $model,
                            'strategy' => $strategy,
                            'storage' => $storage,
                        ];
                    }
                }
            }
        }

        return $data;
    }

    public function testPullApi(): void
    {
        $pullApiResponse = $this->apiClient->llmPull('smollm:135m');

        $this->assertEquals('success', $pullApiResponse->getStatus());
        $this->assertTrue($pullApiResponse->isSuccess());
    }

    public function testClearCache(): void
    {
        $clearCacheResponse = $this->apiClient->ocrClearCache();

        $this->assertTrue($clearCacheResponse->isSuccess());
    }

    /**
     * @param string[] $textContains
     *
     * @throws \JsonException
     */
    #[DataProvider('filesToParseDataProvider')]
    public function testReadTextFromImagesUsingOcrRequestMethod(string $filepath, array $textContains, string $model, string $strategy, string $storage): void
    {
        $ocrRequest = $this->prepareExampleRequest($filepath, $model, $strategy, $storage);

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

            if ($ocrResultResponse->getState()->isFailure()) {
                $this->fail('OCR failed: '.$ocrResultResponse->getStatus());
            }

            sleep(1);
        } while (!$ocrResultResponse->getState()->isFinished());

        $ocrResultResponse = $ocrResultResponse->getResult();
        if (null === $ocrResultResponse) {
            $this->fail('OCR Response is null');
        }

        $this->similarityValidator->validateMultipleTerms(mb_strtolower($ocrResultResponse), $textContains);
    }

    //    /**
    //     * @depends testReadTextFromImagesUsingOcrRequestMethod
    //     */
    //    public function testStorageList(): void
    //    {
    //        $storageListResponse = $this->apiClient->storageList();
    //
    //    }
}
