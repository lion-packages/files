<?php

namespace LionFiles;

use LionRequest\Response;
use \ZipArchive;

class Zip {

	private static ZipArchive $zipArchive;
	private static array $delete_files = [];

	public static function create(string $zip_name): void {
		self::$zipArchive = new ZipArchive();
		self::$zipArchive->open($zip_name, ZipArchive::CREATE);
	}

	public static function save(): void {
		self::$zipArchive->close();

		foreach (self::$delete_files as $key => $file) {
			Manage::remove($file);
		}
	}

	public static function add(array $files): void {
		foreach ($files as $key => $file) {
			self::$zipArchive->addFile($file, Manage::getBasename($file));
		}
	}

	public static function addUpload(string $path, string $file, string $file_name) {
		Manage::upload($file, $file_name, $path);
		self::$zipArchive->addFile($path . $file_name, Manage::getBasename($file_name));
		array_push(self::$delete_files, $path . $file_name);
	}

}