<?php

declare(strict_types=1);

namespace Tests;

use Exception;
use Lion\Files\Store;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use stdClass;
use Tests\Providers\CustomClassProvider;

class StoreTest extends Test
{
    private const string URL_PATH = './storage/';
    private const string PROVIDERS_URL_PATH = './tests/Providers/';
    private const string IMAGE_SIZE = '100x100';
    private const string FILE_NAME = 'image.png';
    private const string INDICATIVE = 'FILE';
    private const array EXTENSIONS = [
        'png',
    ];

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

        $this->assertSame(CustomClassProvider::class, $namespace);
    }

    #[Testing]
    public function getFiles(): void
    {
        $providerFiles = [
            __DIR__ . '/Providers/CustomClassProvider.php',
        ];

        $files = $this->store->getFiles(self::PROVIDERS_URL_PATH);

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

        $response = $this->store->imageSize(self::URL_PATH, self::FILE_NAME, self::IMAGE_SIZE);

        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertIsString($response->status);
        $this->assertIsString($response->message);
        $this->assertSame(200, $response->code);
        $this->assertSame('success', $response->status);

        $this->assertSame(
            "File '" . self::FILE_NAME . "' meets requested dimensions '" . self::IMAGE_SIZE . "'",
            $response->message
        );
    }

    #[Testing]
    public function imageSizeError(): void
    {
        $this->createImage(100, 300);

        $response = $this->store->imageSize(self::URL_PATH, self::FILE_NAME, self::IMAGE_SIZE);

        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertIsString($response->status);
        $this->assertIsString($response->message);
        $this->assertSame(500, $response->code);
        $this->assertSame('error', $response->status);

        $this->assertSame(
            "The file '" . self::FILE_NAME . "' does not have the requested dimensions '" . self::IMAGE_SIZE . "'",
            $response->message
        );
    }

    #[Testing]
    public function tsize(): void
    {
        $this->createImage();

        $file = self::URL_PATH . self::FILE_NAME;

        $size = filesize($file) / 1024;

        $response = $this->store->size($file, $size);

        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertIsString($response->status);
        $this->assertIsString($response->message);
        $this->assertSame(200, $response->code);
        $this->assertSame('success', $response->status);
        $this->assertSame("The file '{$file}' meets the requested size", $response->message);
    }

    #[Testing]
    public function sizeError(): void
    {
        $this->createImage();

        $file = self::URL_PATH . self::FILE_NAME;

        $response = $this->store->size($file, 0.2);

        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertIsString($response->status);
        $this->assertIsString($response->message);
        $this->assertSame(500, $response->code);
        $this->assertSame('error', $response->status);
        $this->assertSame("The file '{$file}' is larger than the requested size", $response->message);
    }

    #[Testing]
    public function view(): void
    {
        $this->createImage();

        $res = $this->store->view(self::URL_PATH);

        $this->assertIsArray($res);
        $this->assertCount(3, $res);
    }

    #[Testing]
    public function viewError(): void
    {
        $this->createImage();

        $response = $this->store->view('./example/');

        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertIsString($response->status);
        $this->assertIsString($response->message);
        $this->assertSame(500, $response->code);
        $this->assertSame('error', $response->status);
        $this->assertSame("The file/folder './example/' does not exist", $response->message);
    }

    /**
     * @throws Exception
     */
    #[Testing]
    public function remove(): void
    {
        $this->createImage();

        $file = self::URL_PATH . self::FILE_NAME;

        $response = $this->store->remove($file);

        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertIsString($response->status);
        $this->assertIsString($response->message);
        $this->assertSame(200, $response->code);
        $this->assertSame('success', $response->status);
        $this->assertSame("The file '{$file}' has been deleted", $response->message);
    }

    /**
     * @throws Exception
     */
    #[Testing]
    public function removeWithMissingFile(): void
    {
        $file = self::URL_PATH . self::FILE_NAME;

        $response = $this->store->remove($file);

        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertIsString($response->status);
        $this->assertIsString($response->message);
        $this->assertSame(500, $response->code);
        $this->assertSame('error', $response->status);
        $this->assertSame("The file '{$file}' could not be removed because it does not exist", $response->message);
    }

    #[Testing]
    public function exist(): void
    {
        $response = $this->store->exist(self::URL_PATH);

        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertIsString($response->status);
        $this->assertIsString($response->message);
        $this->assertSame(200, $response->code);
        $this->assertSame('success', $response->status);
        $this->assertSame("The file/folder '" . self::URL_PATH . "' exists", $response->message);
    }

    #[Testing]
    public function existWithFile(): void
    {
        $this->createImage();

        $file = self::URL_PATH . self::FILE_NAME;

        $response = $this->store->exist($file);

        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertIsString($response->status);
        $this->assertIsString($response->message);
        $this->assertSame(200, $response->code);
        $this->assertSame('success', $response->status);
        $this->assertSame("The file/folder '{$file}' exists", $response->message);
    }

    #[Testing]
    public function existError(): void
    {
        $file = self::URL_PATH . self::FILE_NAME;

        $response = $this->store->exist($file);

        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertIsString($response->status);
        $this->assertIsString($response->message);
        $this->assertSame(500, $response->code);
        $this->assertSame('error', $response->status);
        $this->assertSame("The file/folder '{$file}' does not exist", $response->message);
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
        $this->rmdirRecursively(self::URL_PATH);

        $response = $this->store->folder(self::URL_PATH);

        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertIsString($response->status);
        $this->assertIsString($response->message);
        $this->assertSame(200, $response->code);
        $this->assertSame('success', $response->status);
        $this->assertSame("Directory '" . self::URL_PATH . "' created", $response->message);
        $this->assertFileExists(self::URL_PATH);
    }

    #[Testing]
    public function folderExists(): void
    {
        $response = $this->store->folder(self::URL_PATH);

        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertIsString($response->status);
        $this->assertIsString($response->message);
        $this->assertSame(200, $response->code);
        $this->assertSame('success', $response->status);
        $this->assertSame("The file/folder '" . self::URL_PATH . "' exists", $response->message);
        $this->assertFileExists(self::URL_PATH);
    }

    #[Testing]
    public function validate(): void
    {
        $response = $this->store->validate([self::FILE_NAME], self::EXTENSIONS);

        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertIsString($response->status);
        $this->assertIsString($response->message);
        $this->assertSame(200, $response->code);
        $this->assertSame('success', $response->status);
        $this->assertSame('Files have required extension', $response->message);
    }

    #[Testing]
    public function validateError(): void
    {
        $response = $this->store->validate([self::FILE_NAME], ['php']);

        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertIsString($response->status);
        $this->assertIsString($response->message);
        $this->assertSame(500, $response->code);
        $this->assertSame('error', $response->status);
        $this->assertSame(
            "The file '" . self::FILE_NAME . "' does not have the required extension",
            $response->message
        );
    }

    #[Testing]
    public function replace(): void
    {
        $res = mb_convert_encoding('Ã¡Ã©Ã­Ã³ÃºÃ±', 'ISO-8859-1', 'UTF-8');

        $this->assertSame('áéíóúñ', $this->store->replace($res));
    }
}
