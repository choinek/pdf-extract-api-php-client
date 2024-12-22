<?php

declare(strict_types=1);

namespace Tests\Integration\Choinek\PdfExtractApiPhpClient;

use PHPUnit\Framework\TestCase;
use Choinek\PdfExtractApiPhpClient\ApiClient;
use Choinek\PdfExtractApiPhpClient\Dto\OcrRequestDto;
use Choinek\PdfExtractApiPhpClient\Dto\UploadFileDto;
use Choinek\PdfExtractApiPhpClient\Http\CurlWrapper;

class ApiClientTest extends TestCase
{
    /**
     * @var resource|null
     */
    private static $serverProcess;
    private static string $serverHost = '127.0.0.1';
    private static int $serverPort;

    // I set the timeout to 60 seconds to prevent the test server from running indefinitely
    private static int $timeoutSeconds = 60;

    private static function findAvailablePort(int $startPort, int $endPort): int
    {
        for ($port = $startPort; $port <= $endPort; ++$port) {
            $socket = @fsockopen(self::$serverHost, $port, $errno, $errstr, 0.1);
            if (!$socket) {
                return $port;
            }
            fclose($socket);
        }

        self::fail('No available ports found in range '.$startPort.'-'.$endPort);
    }

    public static function setUpBeforeClass(): void
    {
        self::$serverPort = self::findAvailablePort(30000, 30100);
        $serverFilePath = __DIR__.'/../Utility/IntegrationMockServer.php';
        $command = sprintf('php -S %s:%d %s', self::$serverHost, self::$serverPort, $serverFilePath);

        register_shutdown_function(function () {
            self::terminateServer();
        });

        self::$serverProcess = proc_open(
            $command,
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes
        ) ?: null;

        $start = microtime(true);
        if (!self::checkIfServerStarted()) {
            stream_set_blocking($pipes[1], false);
            stream_set_blocking($pipes[2], false);
            self::fail(
                'Mock Server did not start in '.round(microtime(true) - $start, 4).' seconds.'.PHP_EOL
                .'Command: '.$command.PHP_EOL
                .'Output: '.stream_get_contents($pipes[1]).PHP_EOL
                .'Error: '.stream_get_contents($pipes[2])
            );
        }



        pcntl_alarm(self::$timeoutSeconds);
        pcntl_signal(SIGALRM, function () {
            self::terminateServer();
            self::fail('Mock Server process terminated due to timeout. Test should not run longer than few seconds.');
        });
    }

    private static function checkIfServerStarted(): bool
    {
        $maxTries = 5;
        for ($tries = 0; $tries < $maxTries; ++$tries) {
            if (@fsockopen(self::$serverHost, self::$serverPort)) {
                $liveUrl = sprintf(
                    'http://%s:%d/%s',
                    self::$serverHost,
                    self::$serverPort,
                    'integration-mock-server-live'
                );
                $headers = @get_headers(sprintf('http://%s:%d/integration-mock-server-live', self::$serverHost, self::$serverPort));
                if ($headers && str_contains($headers[0], '200')) {
                    return true;
                }
            }

            usleep(500000); // 0.5s
        }

        return false;
    }

    public static function tearDownAfterClass(): void
    {
        self::terminateServer();
    }

    private static function terminateServer(): void
    {
        if (self::$serverProcess) {
            proc_terminate(self::$serverProcess);
            proc_close(self::$serverProcess);
            self::$serverProcess = null;
        }
    }

    public function testRequestOcr(): void
    {
        $client = new ApiClient(
            new CurlWrapper(),
            sprintf('http://%s:%d', self::$serverHost, self::$serverPort)
        );

        $uploadFileDto = new UploadFileDto('sample.jpg', 'image/jpeg', __DIR__.'/../assets/sample.jpg');
        $ocrRequestDto = new OcrRequestDto(
            'llama_vision',
            'llama3.2-vision',
            $uploadFileDto
        );

        $response = $client->requestOcr($ocrRequestDto);

        $this->assertEquals('uuid_task_id_ocr_request', $response->getTaskId());
    }

    public function testClearCache(): void
    {
        $client = new ApiClient(
            new CurlWrapper(),
            sprintf('http://%s:%d', self::$serverHost, self::$serverPort)
        );

        $response = $client->clearCache();

        $this->assertTrue($response->isSuccess());
    }

    public function testListFiles(): void
    {
        $client = new ApiClient(
            new CurlWrapper(),
            sprintf('http://%s:%d', self::$serverHost, self::$serverPort)
        );

        $response = $client->listFiles();

        $this->assertEquals(['file1.txt', 'file2.pdf'], $response->getFiles());
    }

    public function testLoadFile(): void
    {
        $client = new ApiClient(
            new CurlWrapper(),
            sprintf('http://%s:%d', self::$serverHost, self::$serverPort)
        );

        $response = $client->loadFile('file1.txt');

        $this->assertEquals('File content here', $response->getContent());
    }

    public function testDeleteFile(): void
    {
        $client = new ApiClient(
            new CurlWrapper(),
            sprintf('http://%s:%d', self::$serverHost, self::$serverPort)
        );

        $response = $client->deleteFile('file1.txt');

        $this->assertTrue($response->isSuccess());
    }

    public function testGetResultPendingState(): void
    {
        $client = new ApiClient(
            new CurlWrapper(),
            sprintf('http://%s:%d', self::$serverHost, self::$serverPort)
        );

        $response = $client->getResult('id-for-pending-task');

        $this->assertEquals('PENDING', $response->getState(), 'The state of the task is not PENDING.');
        $this->assertEquals('Task is pending...', $response->getStatus(), 'The status for PENDING state is incorrect.');
    }

    public function testGetResultProgressState(): void
    {
        $client = new ApiClient(
            new CurlWrapper(),
            sprintf('http://%s:%d', self::$serverHost, self::$serverPort)
        );

        $response = $client->getResult('id-for-progress-task');

        $this->assertEquals('PROGRESS', $response->getState(), 'The state of the task is not PROGRESS.');
        $this->assertEquals('Processing task...', $response->getStatus(), 'The status for PROGRESS state is incorrect.');
        $this->assertArrayHasKey('info', $response->toArray(), 'The "info" key is missing in the PROGRESS response.');
        $this->assertArrayHasKey('elapsed_time', $response->toArray()['info'], 'The "elapsed_time" key is missing in the PROGRESS info.');
    }

    public function testGetResultSuccessState(): void
    {
        $client = new ApiClient(
            new CurlWrapper(),
            sprintf('http://%s:%d', self::$serverHost, self::$serverPort)
        );

        $response = $client->getResult('id-for-success-task');

        $this->assertEquals('SUCCESS', $response->getState(), 'The state of the task is not SUCCESS.');
        $this->assertEquals('Extracted text content', $response->getResult(), 'The extracted text for SUCCESS state is incorrect.');
    }

    public function testGetResultFailureState(): void
    {
        $client = new ApiClient(
            new CurlWrapper(),
            sprintf('http://%s:%d', self::$serverHost, self::$serverPort)
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(404);

        $client->getResult('id-for-non-existing-task');
    }
}
