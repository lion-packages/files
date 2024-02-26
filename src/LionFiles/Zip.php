<?php

declare(strict_types=1);

namespace Lion\Files;

use Lion\Files\Store;
use ZipArchive;

class Zip
{
    private Store $store;
	private ZipArchive $zipArchive;

	private array $deleteFiles = [];

    public function __construct()
    {
        $this->store = new Store();
        $this->zipArchive = new ZipArchive();
    }

    /**
     * Unzip a ZIP file from a defined path to a defined path
     * */
	public function decompress(string $from, string $to): void
    {
        $this->zipArchive->open($this->store->normalizePath($from));
        $this->zipArchive->extractTo($this->store->normalizePath($to));
        $this->zipArchive->close();
	}

    /**
     * Create a ZIP file at a defined path
     * */
	public function create(string $zipName): Zip
    {
		$this->zipArchive->open($this->store->normalizePath($zipName), ZipArchive::CREATE);

        return $this;
	}

    /**
     * Add files from a defined path to the ZIP archive
     * */
	public function add(array $files): Zip
    {
		foreach ($files as $file) {
			$this->zipArchive->addFile($file, $this->store->getBasename($file));
		}

        return $this;
	}

    /**
     * Add files sent through a request to the ZIP archive
     * */
	public function addUpload(string $path, string $file, string $fileName): Zip
    {
		$this->store->upload($file, $fileName, $path);

        $filePath = $this->store->normalizePath($path . $fileName);

		$this->zipArchive->addFile($filePath, $this->store->getBasename($fileName));

		array_push($this->deleteFiles, $filePath);

        return $this;
	}

    /**
     * Save the ZIP file with the current data
     * */
    public function save(): void
    {
        $this->zipArchive->close();

        foreach ($this->deleteFiles as $file) {
            $this->store->remove($file);
        }
    }
}
