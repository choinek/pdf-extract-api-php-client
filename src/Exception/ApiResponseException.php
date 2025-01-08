<?php

namespace Choinek\PdfExtractApiClient\Exception;

/**
 * Represents an exception related to HTTP responses in the PDF Extract API Client.
 *
 * Thrown when the API server responds with an error status code or invalid data.
 */
class ApiResponseException extends ApiClientException
{
    /**
     * @param string          $message      a descriptive error message
     * @param int             $statusCode   the HTTP status code returned by the API
     * @param string|null     $responseBody the raw response body from the server (optional)
     * @param \Throwable|null $previous     the previous exception used for exception chaining
     */
    public function __construct(
        string $message,
        int $statusCode,
        ?string $responseBody = null,
        ?\Throwable $previous = null,
    ) {
        $detailedMessage = sprintf(
            "Error communicating with the pdf-extract-api service.\nStatus Code: %d\nMessage: %s\nResponse: %s",
            $statusCode,
            $message,
            $responseBody ? "\nResponse Body: {$responseBody}" : ''
        );
        parent::__construct($detailedMessage, $statusCode, $previous);
    }
}
