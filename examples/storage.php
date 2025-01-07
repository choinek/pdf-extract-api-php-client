<?php

require_once __DIR__.'/../vendor/autoload.php';

$pdfExtractApiClient = new Choinek\PdfExtractApiClient\ApiClient('http://localhost:8000');

$storageList = $pdfExtractApiClient->storageList();

$lastFile = false;
foreach ($storageList->getFiles() as $file) {
    echo $file.PHP_EOL;
    if ($file) {
        $lastFile = $file;
    }
}

if ($lastFile) {
    $fileContent = $pdfExtractApiClient->storageLoadFileByName($lastFile);
    echo 'File downloaded, size: '.strlen($fileContent->getContent());
}
