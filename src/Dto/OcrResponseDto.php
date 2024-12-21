<?php

namespace Choinek\PdfExtractApiPhpClient\Dto;

final class OcrResponseDto implements ResponseDtoInterface
{
    private readonly string|int $taskId;

    /**
     * @param array<string|int, mixed> $response
     */
    public function __construct(array $response)
    {
        if (!isset($response['task_id']) || !is_scalar($response['task_id']) || (!is_string($response['task_id']) && !is_int($response['task_id']))) {
            throw new \InvalidArgumentException('Invalid task_id in response. It must be a string or an integer.');
        }

        $this->taskId = $response['task_id'];
    }

    /**
     * @return array{task_id: string|int}
     */
    public function toArray(): array
    {
        return ['task_id' => $this->taskId];
    }

    public function getTaskId(): string|int
    {
        return $this->taskId;
    }
}
