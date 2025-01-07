<?php

namespace Choinek\PdfExtractApiClient\Dto;

use Choinek\PdfExtractApiClient\Dto\OcrResult\StateEnum;
use Choinek\PdfExtractApiClient\Exception\ApiResponseException;

final class OcrResultResponseDto implements ResponseDtoInterface
{
    /**
     * @param ?array{progress: string, status: string, start_time: float, elapsed_time: float} $info
     *                                                                                               - `progress`: Progress percentage as a string, e.g., "30".
     *                                                                                               - `status`: Current status message, e.g., " Processing (page 1 of 1) chunk no: 217".
     *                                                                                               - `start_time`: Start time as a floating-point number, e.g., 1735270717.226997.
     *                                                                                               - `elapsed_time`: Elapsed time as a floating-point number, e.g., 16.150298833847046.
     */
    public function __construct(
        private readonly string $rawResponseBody,
        private readonly StateEnum $state,
        private readonly ?string $status = null,
        private readonly ?string $result = null,
        private readonly ?array $info = null,
    ) {
    }

    public static function fromResponse(string $responseBody): self
    {
        $response = json_decode($responseBody, true);
        if (!$response) {
            throw new ApiResponseException('Invalid response.', 422, $responseBody);
        }

        $state = StateEnum::tryFrom($response['state']);
        if (null === $state) {
            throw new ApiResponseException('Invalid "state" in response.', 422, $responseBody);
        }

        $status = $response['status'] ?? null;
        $result = $response['result'] ?? null;
        $info = $response['info'] ?? null;

        if (null !== $status && !is_string($status)) {
            throw new ApiResponseException('Invalid "status" in response.', 422, $responseBody);

        }

        if (null !== $result && !is_string($result)) {
            throw new ApiResponseException('Invalid "result" in response.', 422, $responseBody);

        }

        if (null !== $info) {
            $pureInfo = [
                'progress' => (isset($info['progress']) && is_scalar($info['progress']))
                        ? (string) $info['progress']
                        : '',
                'status' => (isset($info['status']) && is_scalar($info['status']))
                        ? (string) $info['status']
                        : '',
                'start_time' => (isset($info['start_time']) && is_numeric($info['start_time']))
                    ? (float) $info['start_time']
                    : 0.0,
                'elapsed_time' => (isset($info['elapsed_time']) && is_numeric($info['elapsed_time']))
                    ? (float) $info['elapsed_time']
                    : 0.0,
            ];

        }

        return new self(
            rawResponseBody: $responseBody,
            state: $state,
            status: $status,
            result: $result,
            info: $pureInfo ?? null,
        );
    }

    /**
     * Get the state of the task.
     */
    public function getState(): StateEnum
    {
        return $this->state;
    }

    /**
     * Get the status message of the task.
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * Get the result of the task (if available).
     */
    public function getResult(): ?string
    {
        return $this->result;
    }

    /**
     * Get additional information about the task (if available).
     *
     * @return ?array<string, mixed>
     */
    public function getInfo(): ?array
    {
        return $this->info;
    }

    /**
     * Convert the DTO into an associative array.
     *
     * @return array{state: string, status: ?string, result: ?string, info: ?array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'state' => $this->state->value,
            'status' => $this->status,
            'result' => $this->result,
            'info' => $this->info,
        ];
    }

    public function getRawResponse(): string
    {
        return $this->rawResponseBody;
    }
}
