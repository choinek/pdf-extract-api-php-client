<?php

namespace Choinek\PdfExtractApiClient\Tests\Unit\Dto;

use PHPUnit\Framework\TestCase;
use Choinek\PdfExtractApiClient\Dto\OcrRequest\UploadFileDto;

class UploadFileDtoTest extends TestCase
{
    private const ASSET_DIR = __DIR__.'/../../assets';

    public function testFromFileWithValidFile(): void
    {
        $filePath = self::ASSET_DIR.'/sample.pdf';
        $dto = UploadFileDto::fromFile($filePath);

        $this->assertSame('sample.pdf', $dto->fileName);
        $this->assertSame('application/pdf', $dto->mimeType);
        $this->assertStringStartsWith(
            'JVBERi',
            $dto->getBase64EncodedContent()
        );
    }

    public function testFromFileWithCustomMimeType(): void
    {
        $filePath = self::ASSET_DIR.'/sample.pdf';
        $customMimeType = 'application/x-custom-pdf';
        $dto = UploadFileDto::fromFile($filePath, $customMimeType);

        $this->assertSame('sample.pdf', $dto->fileName);
        $this->assertSame($customMimeType, $dto->mimeType);
        $this->assertStringStartsWith('JVBERi', $dto->getBase64EncodedContent());
    }

    public function testFromFileWithInvalidPath(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('File not found: invalid-path.pdf');

        UploadFileDto::fromFile('invalid-path.pdf');
    }

    public function testFromBase64WithValidContent(): void
    {
        $filePath = self::ASSET_DIR.'/sample.pdf';
        $fileContents = file_get_contents($filePath) ?: '';
        $base64Content = base64_encode($fileContents);
        $this->assertNotEmpty($base64Content);

        $dto = UploadFileDto::fromBase64($base64Content, 'sample.pdf', 'application/pdf');

        $this->assertSame('sample.pdf', $dto->fileName);
        $this->assertSame('application/pdf', $dto->mimeType);
        $this->assertSame($base64Content, $dto->getBase64EncodedContent());
    }

    public function testFromBase64WithInvalidContent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid base64 content.');

        UploadFileDto::fromBase64('invalid-base64-content', 'sample.pdf', 'application/pdf');
    }

    public function testGetFileContentsFromFile(): void
    {
        $filePath = self::ASSET_DIR.'/sample.pdf';
        $dto = UploadFileDto::fromFile($filePath);

        $this->assertStringStartsWith('JVBERi0xLjQKMS', $dto->getBase64EncodedContent());
    }

    public function testCompareBase64FromDtoAndDirect(): void
    {
        $filePath = self::ASSET_DIR.'/sample.pdf';
        $fileContents = file_get_contents($filePath) ?: '';
        $base64Content = base64_encode($fileContents);
        $this->assertNotEmpty($base64Content);


        $dto = UploadFileDto::fromBase64($base64Content, 'sample.pdf', 'application/pdf');

        $this->assertSame($base64Content, $dto->getBase64EncodedContent());
    }

    public function testGetBase64EncodedContentsFromFile(): void
    {
        $filePath = self::ASSET_DIR.'/sample.pdf';
        $fileContents = file_get_contents($filePath) ?: '';
        $expectedBase64 = base64_encode($fileContents);
        $this->assertNotEmpty($expectedBase64);

        $dto = UploadFileDto::fromFile($filePath);

        $this->assertSame($expectedBase64, $dto->getBase64EncodedContent());
    }

    public function testConstructorValidationMissingParameters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Base64 content must be provided.');

        new UploadFileDto('sample.pdf', 'application/pdf', '');
    }

    public function testConstructorValidationShould(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid base64 content.');

        new UploadFileDto('sample.pdf', 'application/pdf', 'PDFINVALID###');
    }
}
