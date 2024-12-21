<?php

declare(strict_types=1);

namespace Choinek\PdfExtractApiPhpClient\Http;

class CurlWrapper
{
    /**
     * Execute a cURL session.
     *
     * @param resource $ch
     */
    public function exec($ch): string|false
    {
        return curl_exec($ch);
    }

    /**
     * Initialize a cURL session.
     *
     * @return resource
     */
    public function init(?string $url = null)
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
     * @param resource $ch
     */
    public function setopt($ch, int $option, mixed $value): bool
    {
        return curl_setopt($ch, $option, $value);
    }

    /**
     * Get information about the last transfer.
     *
     * @param resource $ch
     */
    public function getinfo($ch, int $option): mixed
    {
        return curl_getinfo($ch, $option);
    }

    /**
     * Close a cURL session.
     *
     * @param resource $ch
     */
    public function close($ch): void
    {
        curl_close($ch);
    }

    /**
     * Return the error message for the last cURL operation.
     *
     * @param resource $ch
     */
    public function error($ch): string
    {
        return curl_error($ch);
    }
}
