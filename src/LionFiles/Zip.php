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
        $this->zipArchive->open($from);
        $this->zipArchive->extractTo($to);
        $this->zipArchive->close();
	}

    /**
     * Create a ZIP file at a defined path
     * */
	public function create(string $zipName): Zip
    {
		$this->zipArchive->open($zipName, ZipArchive::CREATE);

        return $this;
	}

    /**
     * Add files from a defined path to the ZIP archive
     * */
	public function add(array $files): Zip
    {
		foreach ($files as $key => $file) {
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
		$this->zipArchive->addFile($path . $fileName, $this->store->getBasename($fileName));
		array_push($this->deleteFiles, $path . $fileName);

        return $this;
	}

    /**
     * Save the ZIP file with the current data
     * */
    public function save(): void
    {
        $this->zipArchive->close();

        foreach ($this->deleteFiles as $key => $file) {
            $this->store->remove($file);
        }
    }
}
