<?php

declare(strict_types=1);

namespace LionFiles;

use Exception;
use LionFiles\Store;
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

	public function decompress(string $from, string $to): void
    {
        if (!$this->zipArchive->open($from)) {
            throw new Exception("The defined route is not valid: {$from}");
        }

        if (!$this->zipArchive->extractTo($to)) {
            throw new Exception("The defined route is not valid: {$to}");
        }

        $this->zipArchive->close();
	}

	public function create(string $zipName): void
    {
		if (!$this->zipArchive->open($zipName, ZipArchive::CREATE)) {
            throw new Exception("The defined route is not valid: {$zipName}");
        }
	}

	public function save(): void
    {
		$this->zipArchive->close();

		foreach ($this->deleteFiles as $key => $file) {
			$this->store->remove($file);
		}
	}

	public function add(array $files): void
    {
		foreach ($files as $key => $file) {
			$this->zipArchive->addFile($file, $this->store->getBasename($file));
		}
	}

	public function addUpload(string $path, string $file, string $fileName): void
    {
		$this->store->upload($file, $fileName, $path);
		$this->zipArchive->addFile($path . $fileName, $this->store->getBasename($fileName));
		array_push($this->deleteFiles, $path . $fileName);
	}
}
