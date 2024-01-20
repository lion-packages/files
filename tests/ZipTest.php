<?php

declare(strict_types=1);

namespace Tests;

use Lion\Files\Store;
use Lion\Files\Traits\FilesTrait;
use Lion\Files\Zip;
use PHPUnit\Framework\TestCase;

class ZipTest extends TestCase
{
    use FilesTrait;

    const URL_PATH = './storage/';
    const ZIP_NAME = 'zip_file.zip';
    const ZIP_NEW_NAME = 'new_zip_file.zip';
    const TO = './storage/example/';

    private Store $store;
    private Zip $zip;

    protected function setUp(): void
    {
        $this->zip = new Zip();
        $this->store = new Store();

        $this->createDirectory(self::URL_PATH);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively(self::URL_PATH);
    }

    public function testDecompress(): void
    {
        $this->store->folder(self::URL_PATH);

        $this->zip->create(self::URL_PATH . self::ZIP_NAME)->add(['./LICENSE'])->save();

        $this->assertFileExists(self::URL_PATH . self::ZIP_NAME);

        $this->store->folder(self::TO);

        $this->zip->decompress(self::URL_PATH . self::ZIP_NAME, self::TO);

        $this->assertDirectoryExists(self::TO);
    }

    public function testCreate(): void
    {
        $this->zip->create(self::URL_PATH . self::ZIP_NAME)->add(['./LICENSE'])->save();

        $this->assertFileExists(self::URL_PATH . self::ZIP_NAME);
    }

    public function testAdd(): void
    {
        $this->zip->create(self::URL_PATH . self::ZIP_NEW_NAME)->add(['./LICENSE', './README.md'])->save();

        $this->assertFileExists(self::URL_PATH . self::ZIP_NEW_NAME);
    }
}
