<?php

namespace Choinek\PdfExtractApiClient\Dto;

use Choinek\PdfExtractApiClient\Dto\OcrResult\StateEnum;

final class OcrResultResponseDto implements ResponseDtoInterface
{
    /**
     * @param ?array{progress: string, status: string, start_time: float, elapsed_time: float} $info
     *                                                                                               - `progress`: Progress percentage as a string, e.g., "30".
     *                                                                                               - `status`: Current status message, e.g., "OCR Processing (page 1 of 1) chunk no: 217".
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

        $state = StateEnum::tryFrom($response['state']);
        if (null === $state) {
            throw new \InvalidArgumentException('Invalid state value: '.$response['state']);
        }

        $status = $response['status'] ?? null;
        $result = $response['result'] ?? null;
        $info = $response['info'] ?? null;

        if (null !== $status && !is_string($status)) {
            throw new \InvalidArgumentException('Invalid "status" in response: '.$responseBody);
        }

        if (null !== $result && !is_string($result)) {
            throw new \InvalidArgumentException('Invalid "result" in response: '.$responseBody);
        }

        if (null !== $info && !is_array($info)) {
            throw new \InvalidArgumentException('Invalid "info" in response: '.$responseBody);
        }

        if (!isset($info['progress']) || !is_string($info['progress'])) {
            throw new \InvalidArgumentException('Invalid "progress" in response: '.$responseBody);
        }

        if (!isset($info['status']) || !is_string($info['status'])) {
            throw new \InvalidArgumentException('Invalid "status" in response: '.$responseBody);
        }

        if (!isset($info['start_time']) || !is_numeric($info['start_time'])) {
            throw new \InvalidArgumentException('Invalid "start_time" in response: '.$responseBody);
        }
        $info['start_time'] = (float) $info['start_time'];

        if (!isset($info['elapsed_time']) || !is_numeric($info['elapsed_time'])) {
            throw new \InvalidArgumentException('Invalid "start_time" in response: '.$responseBody);
        }
        $info['elapsed_time'] = (float) $info['elapsed_time'];

        return new self(
            rawResponseBody: $responseBody,
            state: $response['state'],
            status: $status,
            result: $result,
            info: $info
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
