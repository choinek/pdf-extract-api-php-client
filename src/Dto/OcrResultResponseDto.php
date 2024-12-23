<?php

namespace Choinek\PdfExtractApiPhpClient\Dto;

final class OcrResultResponseDto implements ResponseDtoInterface
{
    public function __construct(
        private readonly string $state,
        private readonly ?string $status = null,
        private readonly ?string $result = null,
        private readonly ?array $info = null,
    ) {
    }

    public static function fromResponse(string $responseBody): self
    {
        $response = json_decode($responseBody, true);

        if (!isset($response['state']) || !is_string($response['state'])) {
            throw new \InvalidArgumentException('Invalid or missing "state" in response: '.$responseBody);
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

        return new self(
            $response['state'],
            $status,
            $result,
            $info
        );
    }

    /**
     * Get the state of the task.
     */
    public function getState(): string
    {
        return strtolower($this->state);
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
            'state' => $this->state,
            'status' => $this->status,
            'result' => $this->result,
            'info' => $this->info,
        ];
    }
}
