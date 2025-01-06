<?php

namespace Choinek\PdfExtractApiClient\Exception;

/**
 * Represents a generic exception in the PDF Extract API Client.
 *
 * This is the base exception for all errors related to the API client.
 */
class ApiClientException extends \RuntimeException
{
    public function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
