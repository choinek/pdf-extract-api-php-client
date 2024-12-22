<?php

namespace Choinek\PdfExtractApiPhpClient\Dto;

final class OcrResponseDto implements ResponseDtoInterface
{
    public function __construct(
        private readonly ?string $taskId = null,
        private readonly ?string $text = null,
    ) {
    }

    public static function fromResponse(string $responseBody): self
    {
        $response = json_decode($responseBody, true);

        $taskId = $response['task_id'] ?? null;
        $text = $response['text'] ?? null;

        if (null !== $taskId && !is_string($taskId)) {
            throw new \InvalidArgumentException('Invalid task_id in response: '.$responseBody);
        }

        if (null !== $text && !is_string($text)) {
            throw new \InvalidArgumentException('Invalid text in response: '.$responseBody);
        }

        return new self($taskId, $text);
    }

    public function getTaskId(): ?string
    {
        return $this->taskId;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function toArray(): array
    {
        return [
            'task_id' => $this->taskId,
            'text' => $this->text,
        ];
    }
}
