<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use Choinek\PdfExtractApiClient\ApiClient;

$client = new ApiClient('http://localhost:8000');

if (isset(
    $_FILES['files']['tmp_name'][0],
    $_FILES['files']['type'][0],
    $_FILES['files']['name'][0]
)) {
    header('Content-Type: application/json; charset=utf-8');
    // Handle ajax request from uploader

    $response = [];
    try {
        $file = file_get_contents($_FILES['files']['tmp_name'][0]);
        $filename = $_FILES['files']['name'][0];
        $fileType = $_FILES['files']['type'][0];
        if (empty($file)) {
            throw new Exception('File empty or corrupted.');
        }

        $fileContent = base64_encode($file);

        $ocrRequest = $client->ocrRequest(new Choinek\PdfExtractApiClient\Dto\OcrRequestDto(
            'llama_vision',
            'llama3.2-vision',
            Choinek\PdfExtractApiClient\Dto\OcrRequest\UploadFileDto::fromBase64($fileContent, $filename, $fileType)
        ));

        if (!$ocrRequest->getTaskId()) {
            echo 'Task ID not received from PDF Extract API. Aborting.';
            exit(1);
        }

        $ocrStatus = $client->ocrResultGetByTaskId($ocrRequest->getTaskId());

        $response = [
            'file' => [
                'name' => $filename,
                'type' => $fileType,
                'size' => filesize($_FILES['files']['tmp_name'][0]),
            ],
            'taskId' => $ocrRequest->getTaskId(),
            'ocr' => $ocrStatus->toArray(),
        ];
    } catch (Exception $e) {
        sendJsonResponse([
            'error' => $e->getMessage(),
        ], 500);
    }

    sendJsonResponse($response);
} elseif (isset($_GET['taskStatusById']) && is_string($_GET['taskStatusById'])) {

    $response = [];
    $ocrStatus = $client->ocrResultGetByTaskId($_GET['taskStatusById']);

    sendJsonResponse([
        'ocr' => $ocrStatus->toArray(),
    ]);
}

/**
 * @param mixed $response
 */
function sendJsonResponse(array $response, $status = 200): void
{
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($status);
    echo json_encode($response);
    exit;
}

$uploadFileHtml = __DIR__.'/assets/upload.html';
include $uploadFileHtml;
