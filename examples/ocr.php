<?php

use Choinek\PdfExtractApiClient\Dto\OcrRequest\UploadFileDto;
use Choinek\PdfExtractApiClient\Dto\OcrRequestDto;

require_once __DIR__.'/../vendor/autoload.php';

$pdfExtractApiClient = new Choinek\PdfExtractApiClient\ApiClient('http://localhost:8000');

$ocrResponse = $pdfExtractApiClient->ocrRequest(new OcrRequestDto(
    'marker',
    'llama3.2-vision',
    UploadFileDto::fromFile(__DIR__.'/assets/example-invoice.pdf'),
    true,
    'You are OCR. Convert image to markdown.'
));
