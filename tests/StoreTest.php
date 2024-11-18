<?php

declare(strict_types=1);

namespace Tests;

use Exception;
use Lion\Files\Store;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use Tests\Providers\CustomClassProvider;

class StoreTest extends Test
{
    private const string URL_PATH = './storage/';
    private const string PROVIDERS_URL_PATH = './tests/Providers/';
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

    #[Testing]
    public function getNamespaceFromFile(): void
    {
        $namespace = $this->store->getNamespaceFromFile(
            self::PROVIDERS_URL_PATH . 'CustomClassProvider.php',
            'Tests\\Providers\\',
            'Providers/'
        );

        $this->assertIsString($namespace);
        $this->assertSame(CustomClassProvider::class, $namespace);
    }

    #[Testing]
    public function getFiles(): void
    {
        $providerFiles = [
            '/var/www/html/tests/Providers/CustomClassProvider.php',
        ];

        $files = $this->store->getFiles(self::PROVIDERS_URL_PATH);

        $this->assertIsArray($files);
        $this->assertSame($providerFiles, $files);
    }

    #[Testing]
    public function get(): void
    {
        $this->assertSame(file_get_contents('./LICENSE'), $this->store->get('./LICENSE'));
    }

    #[Testing]
    public function imageSize(): void
    {
        $this->createImage();

        $res = $this->store->imageSize(self::URL_PATH, self::FILE_NAME, self::IMAGE_SIZE);

        $this->assertIsObject($res);
        $this->assertObjectHasProperty('status', $res);
        $this->assertObjectHasProperty('message', $res);
        $this->assertSame('success', $res->status);
    }

    #[Testing]
    public function imageSizeError(): void
    {
        $this->createImage(100, 300);

        $res = $this->store->imageSize(self::URL_PATH, self::FILE_NAME, self::IMAGE_SIZE);

        $this->assertIsObject($res);
        $this->assertObjectHasProperty('status', $res);
        $this->assertObjectHasProperty('message', $res);
        $this->assertSame('error', $res->status);
    }

    #[Testing]
    public function tsize(): void
    {
        $this->createImage();

        $size = filesize(self::URL_PATH . self::FILE_NAME) / 1024;

        $res = $this->store->size(self::URL_PATH . self::FILE_NAME, $size);

        $this->assertIsObject($res);
        $this->assertObjectHasProperty('status', $res);
        $this->assertObjectHasProperty('message', $res);
        $this->assertSame('success', $res->status);
    }

    #[Testing]
    public function sizeError(): void
    {
        $this->createImage();

        $res = $this->store->size(self::URL_PATH . self::FILE_NAME, 0.2);

        $this->assertIsObject($res);
        $this->assertObjectHasProperty('status', $res);
        $this->assertObjectHasProperty('message', $res);
        $this->assertSame('error', $res->status);
    }

    #[Testing]
    public function view(): void
    {
        $this->createImage();

        $res = $this->store->view(self::URL_PATH);

        $this->assertIsArray($res);
        $this->assertCount(1, $res);
    }

    #[Testing]
    public function viewError(): void
    {
        $this->createImage();

        $res = $this->store->view('./example/');

        $this->assertIsObject($res);
        $this->assertObjectHasProperty('status', $res);
        $this->assertObjectHasProperty('message', $res);
        $this->assertSame('error', $res->status);
    }

    /**
     * @throws Exception
     */
    #[Testing]
    public function remove(): void
    {
        $this->createImage();

        $res = $this->store->remove(self::URL_PATH . self::FILE_NAME);

        $this->assertIsObject($res);
        $this->assertObjectHasProperty('status', $res);
        $this->assertObjectHasProperty('message', $res);
        $this->assertSame('success', $res->status);
    }

    /**
     * @throws Exception
     */
    #[Testing]
    public function removeWithMissingFile(): void
    {
        $res = $this->store->remove(self::URL_PATH . self::FILE_NAME);

        $this->assertIsObject($res);
        $this->assertObjectHasProperty('status', $res);
        $this->assertObjectHasProperty('message', $res);
        $this->assertSame('error', $res->status);
    }

    #[Testing]
    public function exist(): void
    {
        $res = $this->store->exist(self::URL_PATH);

        $this->assertIsObject($res);
        $this->assertObjectHasProperty('status', $res);
        $this->assertObjectHasProperty('message', $res);
        $this->assertSame('success', $res->status);
    }

    #[Testing]
    public function existWithFile(): void
    {
        $this->createImage();

        $res = $this->store->exist(self::URL_PATH . self::FILE_NAME);

        $this->assertIsObject($res);
        $this->assertObjectHasProperty('status', $res);
        $this->assertObjectHasProperty('message', $res);
        $this->assertSame('success', $res->status);
    }

    #[Testing]
    public function existError(): void
    {
        $res = $this->store->exist(self::URL_PATH . self::FILE_NAME);

        $this->assertIsObject($res);
        $this->assertObjectHasProperty('status', $res);
        $this->assertObjectHasProperty('message', $res);
        $this->assertSame('error', $res->status);
    }

    #[Testing]
    public function renameWithoutIndicative(): void
    {
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}\.png$/', $this->store->rename(self::FILE_NAME));
    }

    #[Testing]
    public function renameWithIndicative(): void
    {
        $result = $this->store->rename(self::FILE_NAME, self::INDICATIVE);

        $this->assertMatchesRegularExpression('/^FILE-[a-f0-9]{32}\.png$/', $result);
    }

    #[Testing]
    public function getExtension(): void
    {
        $this->assertSame('png', $this->store->getExtension(self::FILE_NAME));
    }

    #[Testing]
    public function getName(): void
    {
        $this->assertSame('image', $this->store->getName(self::URL_PATH . self::FILE_NAME));
    }

    #[Testing]
    public function getBaseName(): void
    {
        $this->assertSame('image', $this->store->getName(self::URL_PATH . self::FILE_NAME));
    }

    #[Testing]
    public function folder(): void
    {
        $res = $this->store->folder(self::URL_PATH);

        $this->assertIsObject($res);
        $this->assertObjectHasProperty('status', $res);
        $this->assertObjectHasProperty('message', $res);
        $this->assertSame('success', $res->status);
        $this->assertFileExists(self::URL_PATH);
    }

    #[Testing]
    public function folderCustomSuccess(): void
    {
        $res = $this->store->folder(self::URL_PATH . 'new/');

        $this->assertIsObject($res);
        $this->assertObjectHasProperty('status', $res);
        $this->assertObjectHasProperty('message', $res);
        $this->assertSame('success', $res->status);
        $this->assertFileExists(self::URL_PATH . 'new/');
    }

    #[Testing]
    public function validate(): void
    {
        $res = $this->store->validate([self::FILE_NAME], self::EXTENSIONS);

        $this->assertIsObject($res);
        $this->assertObjectHasProperty('status', $res);
        $this->assertObjectHasProperty('message', $res);
        $this->assertSame('success', $res->status);
    }

    #[Testing]
    public function validateError(): void
    {
        $res = $this->store->validate([self::FILE_NAME], ['php']);

        $this->assertIsObject($res);
        $this->assertObjectHasProperty('status', $res);
        $this->assertObjectHasProperty('message', $res);
        $this->assertSame('error', $res->status);
    }

    #[Testing]
    public function replace(): void
    {
        $res = mb_convert_encoding('Ã¡Ã©Ã­Ã³ÃºÃ±', 'ISO-8859-1', 'UTF-8');

        $this->assertSame('áéíóúñ', $this->store->replace($res));
    }
}
