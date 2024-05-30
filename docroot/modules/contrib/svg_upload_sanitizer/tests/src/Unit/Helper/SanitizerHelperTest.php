<?php

namespace Drupal\Tests\svg_upload_sanitizer\Unit\Helper;

use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileInterface;
use Drupal\svg_upload_sanitizer\Helper\SanitizerHelper;
use Drupal\Tests\svg_upload_sanitizer\TestLogger;
use Drupal\Tests\UnitTestCase;
use enshrined\svgSanitize\Sanitizer;
use PHPUnit\Framework\TestCase;

/**
 * Unit test class for the SanitizeHelper class.
 *
 * @package Drupal\Tests\svg_upload_sanitizer\Unit\Helper
 *
 * @internal
 */
class SanitizerHelperTest extends TestCase {

  /**
   * The mocked file system.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject
   */
  private $fileSystem;

  /**
   * The logger.
   *
   * @var \Drupal\Tests\svg_upload_sanitizer\TestLogger
   */
  private $logger;

  /**
   * The file helper to test.
   *
   * @var \Drupal\svg_upload_sanitizer\Helper\SanitizerHelper
   */
  private $sanitizerHelper;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->fileSystem = $this->createMock(FileSystemInterface::class);

    $this->logger = new TestLogger();
    $this->sanitizerHelper = new SanitizerHelper($this->fileSystem, new Sanitizer());
    $this->sanitizerHelper->setLogger($this->logger);
  }

  public function testSanitizeWhenMimeTypeIsNotSvg(): void {
    $file = $this->createMock(FileInterface::class);
    $file
      ->expects($this->atLeastOnce())
      ->method('getMimeType')
      ->willReturn('image/png');

    $this->assertFalse($this->sanitizerHelper->sanitize($file));
  }

  public function testSanitizeWhenRealpathIsNotResolved(): void {
    list($file) = $this->prepareFile(FALSE);

    $this->assertFalse($this->sanitizerHelper->sanitize($file));

    $logs = $this->logger->getLogs('notice');
    $this->assertCount(1, $logs);
    $this->assertSame('Could not resolve the path of the file (URI: "public://fileuri").', reset($logs));
  }

  public function testSanitizeWhenFileDoesNotExist(): void {
    list($file) = $this->prepareFile(TRUE);

    $this->assertFalse($this->sanitizerHelper->sanitize($file));

    $logs = $this->logger->getLogs('notice');
    $this->assertCount(1, $logs);
    $this->assertSame('The file does not exist (path: "something/that/will/never/exists.casper").', reset($logs));
  }

  public function testSanitize(): void {
    list($file) = $this->prepareFile(TRUE, TRUE);

    $this->assertTrue($this->sanitizerHelper->sanitize($file));
  }

  private function prepareFile($pathIsResolved, $filePathExists = FALSE): array {
    $filePath = $filePathExists ? sprintf('%s/fixtures/image.svg', __DIR__) : 'something/that/will/never/exists.casper';

    $fileUri = 'public://fileuri';

    $file = $this->createMock(FileInterface::class);
    $file
      ->expects($this->atLeastOnce())
      ->method('getMimeType')
      ->willReturn('image/svg+xml');
    $file
      ->expects($this->atLeastOnce())
      ->method('getFileUri')
      ->willReturn($fileUri);

    $this->fileSystem
      ->expects($this->atLeastOnce())
      ->method('realpath')
      ->with($fileUri)
      ->willReturn($pathIsResolved ? $filePath : FALSE);

    return [$file, $filePath];
  }

}
