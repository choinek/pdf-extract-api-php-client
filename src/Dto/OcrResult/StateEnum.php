<?php

namespace Choinek\PdfExtractApiClient\Dto\OcrResult;

enum StateEnum: string
{
    case PENDING = 'PENDING';
    case PROGRESS = 'PROGRESS';
    case SUCCESS = 'SUCCESS';
    case FAILURE = 'FAILURE';
    case DONE = 'DONE';

    public function isSuccess(): bool
    {
        return self::SUCCESS === $this;
    }

    public function isFailure(): bool
    {
        return self::FAILURE === $this;
    }

    public function isPending(): bool
    {
        return self::PENDING === $this;
    }

    public function isProgress(): bool
    {
        return self::PROGRESS === $this;
    }

    public function isDone(): bool
    {
        return self::DONE === $this;
    }

    public function isFinished(): bool
    {
        return $this->isSuccess() || $this->isFailure() || $this->isDone();
    }

    public function isProcessing(): bool
    {
        return $this->isPending() || $this->isProgress();
    }
}
