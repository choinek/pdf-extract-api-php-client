<?php

declare(strict_types=1);

namespace Choinek\PdfExtractApiPhpClient\Http;

class CurlWrapper
{
    /**
     * Execute a cURL session.
     *
     * @param \CurlHandle $ch
     */
    public function exec($ch): bool|string
    {
        return curl_exec($ch);
    }

    public function init(?string $url = null): \CurlHandle
    {
        $handle = curl_init($url);
        if (false === $handle) {
            throw new \RuntimeException('Failed to initialize cURL session.');
        }

        return $handle;
    }

    /**
     * Set an option for a cURL transfer.
     *
     * @param \CurlHandle $ch
     */
    public function setopt($ch, int $option, mixed $value): bool
    {
        return curl_setopt($ch, $option, $value);
    }

    /**
     * Get information about the last transfer.
     *
     * @param \CurlHandle $ch
     */
    public function getinfo($ch, int $option): mixed
    {
        return curl_getinfo($ch, $option);
    }

    /**
     * Close a cURL session.
     *
     * @param \CurlHandle $ch
     */
    public function close($ch): void
    {
        curl_close($ch);
    }

    /**
     * Return the error message for the last cURL operation.
     *
     * @param \CurlHandle $ch
     */
    public function error($ch): string
    {
        return curl_error($ch);
    }
}
