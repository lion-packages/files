<?php

declare(strict_types=1);

namespace Tests;

use Exception;
use Lion\Files\Store;
use Lion\Files\Zip;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;

class ZipTest extends Test
{
    private const string URL_PATH = './storage/';
    private const string ZIP_NAME = 'zip_file.zip';
    private const string ZIP_NEW_NAME = 'new_zip_file.zip';
    private const string TO = self::URL_PATH . 'example/';
    private const string LICENSE_PATH = './LICENSE';

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

    /**
     * @throws Exception
     */
    #[Testing]
    public function decompress(): void
    {
        $this->zip
            ->create(self::URL_PATH . self::ZIP_NAME)
            ->add([
                self::LICENSE_PATH,
            ])
            ->save();

        $this->assertFileExists(self::URL_PATH . self::ZIP_NAME);

        $this->store->folder(self::TO);

        $this->zip->decompress(self::URL_PATH . self::ZIP_NAME, self::TO);

        $this->assertDirectoryExists(self::TO);
    }

    /**
     * @throws Exception
     */
    #[Testing]
    public function create(): void
    {
        $this->zip
            ->create(self::URL_PATH . self::ZIP_NAME)
            ->add([
                self::LICENSE_PATH,
            ])
            ->save();

        $this->assertFileExists(self::URL_PATH . self::ZIP_NAME);
    }

    /**
     * @throws Exception
     */
    #[Testing]
    public function add(): void
    {
        $this->zip
            ->create(self::URL_PATH . self::ZIP_NEW_NAME)
            ->add([
                self::LICENSE_PATH,
                './.github/README.md',
            ])
            ->save();

        $this->assertFileExists(self::URL_PATH . self::ZIP_NEW_NAME);
    }
}
