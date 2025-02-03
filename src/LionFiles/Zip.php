<?php

declare(strict_types=1);

namespace Lion\Files;

use Exception;
use Lion\Files\Store;
use ZipArchive;

/**
 * Manage files to compress or decompress in ZIP format
 *
 * @property Store $store [Manipulate system files]
 * @property ZipArchive $zipArchive [A file archive, compressed with Zip]
 * @property array<int, string> $deleteFiles [List of files that are deleted in
 * management]
 *
 * @package Lion\Files
 */
class Zip
{
    /**
     * [Manipulate system files]
     *
     * @var Store $store
     */
    private Store $store;

    /**
     * [A file archive, compressed with Zip]
     *
     * @var ZipArchive $zipArchive
     */
    private ZipArchive $zipArchive;

    /**
     * [List of files that are deleted in management]
     *
     * @var array<int, string> $deleteFiles
     */
    private array $deleteFiles = [];

    public function __construct()
    {
        $this->store = new Store();

        $this->zipArchive = new ZipArchive();
    }

    /**
     * Unzip a ZIP file from a defined path to a defined path
     *
     * @param string $from [Path from where files are obtained]
     * @param string $to [Path where files are stored]
     *
     * @return void
     */
    public function decompress(string $from, string $to): void
    {
        $this->zipArchive->open($this->store->normalizePath($from));

        $this->zipArchive->extractTo($this->store->normalizePath($to));

        $this->zipArchive->close();
    }

    /**
     * Create a ZIP file at a defined path
     *
     * @param string $zipName [ZIP file name]
     *
     * @return Zip
     */
    public function create(string $zipName): Zip
    {
        $this->zipArchive->open($this->store->normalizePath($zipName), ZipArchive::CREATE);

        return $this;
    }

    /**
     * Add files from a defined path to the ZIP archive
     *
     * @param array<int, string> $files [List of files to be compressed]
     *
     * @return Zip
     */
    public function add(array $files): Zip
    {
        foreach ($files as $file) {
            $this->zipArchive->addFile($file, $this->store->getBasename($file));
        }

        return $this;
    }

    /**
     * Add files sent through a request to the ZIP archive
     *
     * @param string $path [Defined route]
     * @param string $tmpName [Temporary file]
     * @param string $name [File name]
     *
     * @return Zip
     */
    public function addUpload(string $path, string $tmpName, string $name): Zip
    {
        $this->store->upload($tmpName, $name, $path);

        $filePath = $this->store->normalizePath($path . $name);

        $this->zipArchive->addFile($filePath, $this->store->getBasename($name));

        $this->deleteFiles[] = $filePath;

        return $this;
    }

    /**
     * Save the ZIP file with the current data
     *
     * @return void
     *
     * @throws Exception [If an error occurs while deleting the file]
     */
    public function save(): void
    {
        $this->zipArchive->close();

        foreach ($this->deleteFiles as $file) {
            $this->store->remove($file);
        }
    }
}
