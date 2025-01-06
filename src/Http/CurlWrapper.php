<?php

declare(strict_types=1);

namespace Choinek\PdfExtractApiClient\Http;

use Choinek\PdfExtractApiClient\Exception\ApiClientException;

/**
 * This class is a wrapper around the cURL functions.
 * It is used to make the code testable.
 * I don't want to add a dependency like Guzzle.
 */
class CurlWrapper
{
    private \CurlHandle $curlHandle;

    public function __construct(?string $url = null)
    {
        if ($url) {
            $curlHandle = curl_init($url);
            if (false === $curlHandle) {
                throw new ApiClientException('Failed to initialize cURL session.');
            }
            $this->curlHandle = $curlHandle;
        }
    }

    public function exec(): bool|string
    {
        $this->checkHandle();

        return curl_exec($this->curlHandle);
    }

    public function init(string $url): self
    {
        return new CurlWrapper($url);
    }

    public function setopt(int $option, mixed $value): bool
    {
        $this->checkHandle();

        return curl_setopt($this->curlHandle, $option, $value);
    }

    public function getinfo(int $option): mixed
    {
        $this->checkHandle();

        return curl_getinfo($this->curlHandle, $option);
    }

    public function close(): void
    {
        $this->checkHandle();
        curl_close($this->curlHandle);
    }

    public function error(): string
    {
        $this->checkHandle();

        return curl_error($this->curlHandle);
    }

    private function checkHandle(): void
    {
        if (!isset($this->curlHandle)) {
            throw new ApiClientException('CurlWrapper was not initialized with a URL. CurlWrapper::init() must be called first.');
        }
    }
}
