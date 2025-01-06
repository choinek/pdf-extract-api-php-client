<?php

require_once __DIR__.'/../vendor/autoload.php';

$pdfExtractApiClient = new Choinek\PdfExtractApiClient\ApiClient('http://localhost:8000');
$storageList = $pdfExtractApiClient->storageList();
foreach ($storageList->getFiles() as $file) {
    echo $file.PHP_EOL;
}
