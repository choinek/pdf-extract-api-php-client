<?php

declare(strict_types=1);

namespace Choinek\PdfExtractApiClient\Tests\Utility;

/**
 * Download examples from external repositories.
 */
class AssetDownloader
{
    /**
     * @var array<int, array{url: string, path: string}>
     */
    public static array $assetsToDownload = [
        [
            'url' => 'https://raw.githubusercontent.com/CatchTheTornado/pdf-extract-api/main/examples/example-invoice.pdf',
            'path' => __DIR__.'/../assets/external/example-invoice.pdf',
        ],
        [
            'url' => 'https://raw.githubusercontent.com/CatchTheTornado/pdf-extract-api/main/examples/example-mri.pdf',
            'path' => __DIR__.'/../assets/external/example-mri.pdf',
        ],
    ];

    public function setUp(): void
    {
        foreach (self::$assetsToDownload as $asset) {
            if (!is_file($asset['path'])) {
                $this->download($asset['url'], $asset['path']);
            }
        }
    }

    public function download(string $url, string $savePath): void
    {
        if (empty($url)) {
            throw new \InvalidArgumentException('URL is empty');
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Functional Tests Agent');

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \RuntimeException('cURL error: '.curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (200 !== $httpCode) {
            throw new \RuntimeException("Failed to download file. HTTP status code: $httpCode");
        }

        curl_close($ch);

        if (false === file_put_contents($savePath, $response)) {
            throw new \RuntimeException("Failed to save file to $savePath");
        }
    }
}
