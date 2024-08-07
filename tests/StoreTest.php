<?php

declare(strict_types=1);

namespace Tests;

use Lion\Files\Store;
use Lion\Test\Test;

class StoreTest extends Test
{
    private const string URL_PATH = './storage/';
    private const string IMAGE_SIZE = '100x100';
    private const string FILE_NAME = 'image.png';
    private const string INDICATIVE = 'FILE';
    private const array EXTENSIONS = ['png'];

    private Store $store;

    public function setUp(): void
    {
        $this->store = new Store();

        $this->createDirectory(self::URL_PATH);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively(self::URL_PATH);
    }

    public function testGet(): void
    {
        $this->assertSame(file_get_contents('./LICENSE'), $this->store->get('./LICENSE'));
    }

    public function testImageSize(): void
    {
        $this->createImage();

        $res = $this->store->imageSize(self::URL_PATH, self::FILE_NAME, self::IMAGE_SIZE);

        $this->assertIsObject($res);
        $this->assertObjectHasProperty('status', $res);
        $this->assertObjectHasProperty('message', $res);
        $this->assertSame('success', $res->status);
    }

    public function testImageSizeError(): void
    {
        $this->createImage(100, 300);

        $res = $this->store->imageSize(self::URL_PATH, self::FILE_NAME, self::IMAGE_SIZE);

        $this->assertIsObject($res);
        $this->assertObjectHasProperty('status', $res);
        $this->assertObjectHasProperty('message', $res);
        $this->assertSame('error', $res->status);
    }

    public function testSize(): void
    {
        $this->createImage();

        $size = filesize(self::URL_PATH . self::FILE_NAME) / 1024;

        $res = $this->store->size(self::URL_PATH . self::FILE_NAME, $size);

        $this->assertIsObject($res);
        $this->assertObjectHasProperty('status', $res);
        $this->assertObjectHasProperty('message', $res);
        $this->assertSame('success', $res->status);
    }

    public function testSizeError(): void
    {
        $this->createImage();

        $res = $this->store->size(self::URL_PATH . self::FILE_NAME, 0.2);

        $this->assertIsObject($res);
        $this->assertObjectHasProperty('status', $res);
        $this->assertObjectHasProperty('message', $res);
        $this->assertSame('error', $res->status);
    }

    public function testView(): void
    {
        $this->createImage();

        $res = $this->store->view(self::URL_PATH);

        $this->assertIsArray($res);
        $this->assertCount(1, $res);
    }

    public function testViewError(): void
    {
        $this->createImage();

        $res = $this->store->view('./example/');

        $this->assertIsObject($res);
        $this->assertObjectHasProperty('status', $res);
        $this->assertObjectHasProperty('message', $res);
        $this->assertSame('error', $res->status);
    }

    public function testRemove(): void
    {
        $this->createImage();

        $res = $this->store->remove(self::URL_PATH . self::FILE_NAME);

        $this->assertIsObject($res);
        $this->assertObjectHasProperty('status', $res);
        $this->assertObjectHasProperty('message', $res);
        $this->assertSame('success', $res->status);
    }

    public function testRemoveWithMissingFile(): void
    {
        $res = $this->store->remove(self::URL_PATH . self::FILE_NAME);

        $this->assertIsObject($res);
        $this->assertObjectHasProperty('status', $res);
        $this->assertObjectHasProperty('message', $res);
        $this->assertSame('error', $res->status);
    }

    public function testExist(): void
    {
        $res = $this->store->exist(self::URL_PATH);

        $this->assertIsObject($res);
        $this->assertObjectHasProperty('status', $res);
        $this->assertObjectHasProperty('message', $res);
        $this->assertSame('success', $res->status);
    }

    public function testExistWithFile(): void
    {
        $this->createImage();

        $res = $this->store->exist(self::URL_PATH . self::FILE_NAME);

        $this->assertIsObject($res);
        $this->assertObjectHasProperty('status', $res);
        $this->assertObjectHasProperty('message', $res);
        $this->assertSame('success', $res->status);
    }

    public function testExistError(): void
    {
        $res = $this->store->exist(self::URL_PATH . self::FILE_NAME);

        $this->assertIsObject($res);
        $this->assertObjectHasProperty('status', $res);
        $this->assertObjectHasProperty('message', $res);
        $this->assertSame('error', $res->status);
    }

    public function testRenameWithoutIndicative(): void
    {
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}\.png$/', $this->store->rename(self::FILE_NAME));
    }

    public function testRenameWithIndicative(): void
    {
        $result = $this->store->rename(self::FILE_NAME, self::INDICATIVE);

        $this->assertMatchesRegularExpression('/^FILE-[a-f0-9]{32}\.png$/', $result);
    }

    public function testGetExtension(): void
    {
        $this->assertSame('png', $this->store->getExtension(self::FILE_NAME));
    }

    public function testGetName(): void
    {
        $this->assertSame('image', $this->store->getName(self::URL_PATH . self::FILE_NAME));
    }

    public function testGetBaseName(): void
    {
        $this->assertSame('image', $this->store->getName(self::URL_PATH . self::FILE_NAME));
    }

    public function testFolder(): void
    {
        $res = $this->store->folder(self::URL_PATH);

        $this->assertIsObject($res);
        $this->assertObjectHasProperty('status', $res);
        $this->assertObjectHasProperty('message', $res);
        $this->assertSame('success', $res->status);
        $this->assertFileExists(self::URL_PATH);
    }

    public function testFolderCustomSuccess(): void
    {
        $res = $this->store->folder(self::URL_PATH . 'new/');

        $this->assertIsObject($res);
        $this->assertObjectHasProperty('status', $res);
        $this->assertObjectHasProperty('message', $res);
        $this->assertSame('success', $res->status);
        $this->assertFileExists(self::URL_PATH . 'new/');
    }

    public function testValidate(): void
    {
        $res = $this->store->validate([self::FILE_NAME], self::EXTENSIONS);

        $this->assertIsObject($res);
        $this->assertObjectHasProperty('status', $res);
        $this->assertObjectHasProperty('message', $res);
        $this->assertSame('success', $res->status);
    }

    public function testValidateError(): void
    {
        $res = $this->store->validate([self::FILE_NAME], ['php']);

        $this->assertIsObject($res);
        $this->assertObjectHasProperty('status', $res);
        $this->assertObjectHasProperty('message', $res);
        $this->assertSame('error', $res->status);
    }

    public function testReplace(): void
    {
        $res = mb_convert_encoding('Ã¡Ã©Ã­Ã³ÃºÃ±', 'ISO-8859-1', 'UTF-8');

        $this->assertSame('áéíóúñ', $this->store->replace($res));
    }
}
