<?php

namespace LionFiles;

use \ZipArchive;
use LionFiles\Store;

class Zip {

	private static ZipArchive $zipArchive;
	private static array $delete_files = [];

	public static function decompress(string $from, string $to) {
		$zip = new ZipArchive();
        $zip->open($from, false);
        $zip->extractTo($to);
        $zip->close();
	}

	public static function create(string $zip_name): void {
		self::$zipArchive = new ZipArchive();
		self::$zipArchive->open($zip_name, ZipArchive::CREATE);
	}

	public static function save(): void {
		self::$zipArchive->close();

		foreach (self::$delete_files as $key => $file) {
			Store::remove($file);
		}
	}

	public static function add(array $files): void {
		foreach ($files as $key => $file) {
			self::$zipArchive->addFile($file, Store::getBasename($file));
		}
	}

	public static function addUpload(string $path, string $file, string $file_name) {
		Store::upload($file, $file_name, $path);
		self::$zipArchive->addFile($path . $file_name, Store::getBasename($file_name));
		array_push(self::$delete_files, $path . $file_name);
	}

}