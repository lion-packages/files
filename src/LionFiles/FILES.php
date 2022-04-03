<?php

namespace LionFiles;

class FILES {

	private static string $url_path = "resources/upload_files/";

	public function __construct() {

	}

	public static function view(string $path): array {
		$data = [];
		$list = scandir($path, 1);

		for ($i = 0; $i < (count($list) - 2); $i++) {
			array_push($data, "{$path}{$list[$i]}");
		}

		return $data;
	}

	public static function remove(array $files): bool {
		foreach ($files as $key => $file) {
			if (!unlink($file)) {
				return false;
				break;
			}
		}

		return true;
	}

	public static function exist(array $files): bool {
		foreach ($files as $key => $file) {
			if (!file_exists($file)) {
				return false;
				break;
			}
		}

		return true;
	}

	public static function rename(string $file, ?string $indicative = null): string {
		if ($indicative != null) {
			return "{$indicative}-" . md5(hash('sha256', uniqid())) . "." . self::getExtension($file);
		} else {
			return md5(hash('sha256', uniqid())) . "." . self::getExtension($file);
		}
	}

	public static function upload(array $tmps, array $names, ?string $path = null): bool {
		self::folder($path === null ? self::$url_path : $path);

		foreach ($names as $key => $name) {
			if (!move_uploaded_file($tmps[$key], ($path === null ? self::$url_path : $path) . $name)) {
				return false;
				break;
			}
		}

		return true;
	}

	public static function getExtension(string $path): string {
		return (new \SplFileInfo($path))->getExtension();
	}

	public static function getName(string $path): string {
		return (new \SplFileInfo($path))->getBasename("." . self::getExtension($path));
	}

	public static function getBasename(string $path): string {
		return (new \SplFileInfo($path))->getBasename();
	}

	public static function folder(?string $path = null): bool {
		$path = $path === null ? self::$url_path : $path;
		return !self::exist([$path]) ? mkdir($path, 0777, true) : true;
	}

	public static function validate(array $files, array $exts): bool {
		foreach ($files['name'] as $key_file => $file) {
			$file_extension = self::getExtension($file);

			if (!in_array($file_extension, $exts)) {
				return false;
				break;
			}
		}

		return true;
	}

	public static function replace($cell): string {
		$cell = str_replace("á", "á", $cell);
		$cell = str_replace("é", "é", $cell);
		$cell = str_replace("í", "í", $cell);
		$cell = str_replace("ó", "ó", $cell);
		$cell = str_replace("ú", "ú", $cell);
		$cell = str_replace("ñ", "ñ", $cell);
		$cell = str_replace("Ã¡", "á", $cell);
		$cell = str_replace("Ã©", "é", $cell);
		$cell = str_replace("Ã", "í", $cell);
		$cell = str_replace("Ã³", "ó", $cell);
		$cell = str_replace("Ãº", "ú", $cell);
		$cell = str_replace("Ã±", "ñ", $cell);
		$cell = str_replace("Ã", "á", $cell);
		$cell = str_replace("Ã‰", "é", $cell);
		$cell = str_replace("Ã", "í", $cell);
		$cell = str_replace("Ã“", "ó", $cell);
		$cell = str_replace("Ãš", "ú", $cell);
		$cell = str_replace("Ã‘", "ñ", $cell);
		$cell = str_replace("&aacute;", "á", $cell);
		$cell = str_replace("&eacute;", "é", $cell);
		$cell = str_replace("&iacute;", "í", $cell);
		$cell = str_replace("&oacute;", "ó", $cell);
		$cell = str_replace("&uacute;", "ú", $cell);
		$cell = str_replace("&ntilde;", "ñ", $cell);
		return $cell;
	}

}