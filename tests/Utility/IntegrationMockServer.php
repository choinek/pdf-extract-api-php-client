<?php

namespace Choinek\PdfExtractApiClient\Tests\Utility;

class IntegrationMockServer
{
    public function __construct(
        private readonly string $method,
        private readonly string $path,
    ) {
    }

    public function handle(): void
    {
        if ('GET' === $this->method && '/integration-mock-server-live' === $this->path) {
            $this->sendResponse(['status' => 'ok'], 200);

        } elseif ('POST' === $this->method && '/ocr/upload' === $this->path) {
            $this->sendResponse(['task_id' => 'uuid_task_id_ocr_upload'], 200);

        } elseif ('POST' === $this->method && '/ocr/request' === $this->path) {
            $this->sendResponse(['task_id' => 'uuid_task_id_ocr_request'], 200);

        } elseif ('GET' === $this->method && preg_match('~^/ocr/result/([a-zA-Z\-]+)$~', $this->path, $matches)) {
            $taskId = $matches[1];

            $mockTasks = [
                'id-for-pending-task' => ['state' => 'PENDING', 'status' => 'Task is pending...'],
                'id-for-progress-task' => [
                    'state' => 'PROGRESS',
                    'status' => 'Processing task...',
                    'info' => ['start_time' => time() - 30, 'elapsed_time' => 30],
                ],
                'id-for-success-task' => ['state' => 'SUCCESS', 'result' => 'Extracted text content'],
            ];

            if (isset($mockTasks[$taskId])) {
                $task = $mockTasks[$taskId];

                $this->sendResponse($task, 200);
            } else {
                $this->sendResponse(
                    ['state' => 'FAILURE', 'status' => 'Task not found'],
                    404
                );
            }

        } elseif ('POST' === $this->method && '/ocr/clear_cache' === $this->path) {
            $this->sendResponse(['success' => true], 200);
        } elseif ('POST' === $this->method && '/llm/generate' === $this->path) {
            $this->sendResponse(['generated_text' => 'Generated text response'], 200);

        } elseif ('POST' === $this->method && '/llm/pull' === $this->path) {
            $this->sendResponse(['status' => 'completed'], 200);

        } elseif ('GET' === $this->method && '/storage/list' === $this->path) {
            $this->sendResponse(['files' => ['file1.txt', 'file2.pdf']], 200);

        } elseif ('GET' === $this->method && '/storage/load' == $this->path) {
            $this->sendResponse(['content' => 'File content here'], 200);

        } elseif ('DELETE' === $this->method && '/storage/delete' == $this->path) {
            $this->sendResponse(['success' => true], 200);

        } else {
            $this->sendResponse(['error' => 'Endpoint not found'], 404);
        }
    }

    /**
     * @param array<string|int,mixed> $response
     */
    private function sendResponse(array $response, int $statusCode): void
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($response);
    }
}

if (!isset($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'])) {
    exit('Invalid request - missing method or URI. Probably not called from a web server.');
}

$server = new IntegrationMockServer(
    method: $_SERVER['REQUEST_METHOD'],
    path: strtok($_SERVER['REQUEST_URI'], '?') ?: $_SERVER['REQUEST_URI']
);

$server->handle();
