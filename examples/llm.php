<?php

require_once __DIR__.'/../vendor/autoload.php';

$pdfExtractApiClient = new Choinek\PdfExtractApiClient\ApiClient('http://localhost:8000');

$llmPullResponse = $pdfExtractApiClient->llmPull('llama3.2');

echo $llmPullResponse->getStatus().PHP_EOL;
echo $llmPullResponse->getRawResponse();
