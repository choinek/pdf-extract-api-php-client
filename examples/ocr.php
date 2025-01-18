<?php

use Choinek\PdfExtractApiClient\Dto\OcrRequest\UploadFileDto;
use Choinek\PdfExtractApiClient\Dto\OcrRequestDto;

require_once __DIR__.'/../vendor/autoload.php';

$pdfExtractApiClient = new Choinek\PdfExtractApiClient\ApiClient('http://127.0.0.1:8000');
echo 'before';



$ocrRequest = $pdfExtractApiClient->ocrRequest(new OcrRequestDto(
    'llama_vision',
    'llama3.2-vision',
    // UploadFileDto::fromFile(__DIR__.'/assets/example-small-image.png'),
    // UploadFileDto::fromFile(__DIR__.'/assets/phones_list_scanned_v4.pdf'),
    UploadFileDto::fromFile(__DIR__.'/assets/example-invoice.pdf'),
    // UploadFileDto::fromFile(__DIR__.'/assets/merged-output.pdf'),
    false,
    'You are OCR. Convert image to markdown.'
));

// $ocrRequest = $pdfExtractApiClient->ocrRequest(new OcrRequestDto(
//    'marker',
//    'llama3.2-vision',
//    UploadFileDto::fromFile(__DIR__.'/assets/example-invoice.pdf'),
//    true,
//    'You are OCR. Convert image to markdown.'
// ));

if (!$ocrRequest->getTaskId()) {
    echo 'Task ID not received from PDF Extract API. Aborting.';
    exit(1);
}

do {
    echo 'before';
    $ocrResult = $pdfExtractApiClient->ocrResultGetByTaskId($ocrRequest->getTaskId());
    echo json_encode($ocrResult->getInfo(), JSON_PRETTY_PRINT);
    usleep(500000);
} while ($ocrResult->getState()->isProcessing());

sleep(2);
$ocrResult = $pdfExtractApiClient->ocrResultGetByTaskId($ocrRequest->getTaskId());
echo $ocrResult->getResult();
