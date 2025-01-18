<?php

require_once __DIR__.'/../vendor/autoload.php';

$pdfExtractApiClient = new Choinek\PdfExtractApiClient\ApiClient('http://localhost:8000');

$cacheClear = $pdfExtractApiClient->ocrClearCache();

echo 'Cache - status '.$cacheClear->getStatus().' - '.$cacheClear->isSuccess();
echo 'Raw response: '.$cacheClear->getRawResponse().PHP_EOL;
