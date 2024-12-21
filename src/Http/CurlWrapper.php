<?php

declare(strict_types=1);

namespace Choinek\PdfExtractApiPhpClient\Http;

class CurlWrapper
{
    private \CurlHandle $curlHandle;

    public function exec(): bool|string
    {
        return curl_exec($this->curlHandle);
    }

    public function __construct(string $url)
    {
        $curlHandle = curl_init($url);
        if (false === $curlHandle) {
            throw new \RuntimeException('Failed to initialize cURL session.');
        }
        $this->curlHandle = $curlHandle;
    }

    public function init(string $url): self
    {
        return new CurlWrapper($url);
    }

    public function setopt(int $option, mixed $value): bool
    {
        return curl_setopt($this->curlHandle, $option, $value);
    }

    public function getinfo(int $option): mixed
    {
        return curl_getinfo($this->curlHandle, $option);
    }

    public function close(): void
    {
        curl_close($this->curlHandle);
    }

    public function error(): string
    {
        return curl_error($this->curlHandle);
    }
}
