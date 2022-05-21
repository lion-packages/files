<?php

namespace LionFiles;

class FILES {

	public static string $url_path = "storage/upload_files/";

	public function __construct() {

	}

	private static function response(string $status, ?string $message = null, array $data = []): object {
		return (object) [
			'status' => $status,
			'message' => $message,
			'data' => $data
		];
	}

	public static function imageSize(string $path, array $data_path, string $imgSize): object {
		$data_file = getimagesize("{$path}{$data}");

		$union = "{$data_file[0]}x{$data_file[1]}";
		if ($union != $imgSize) {
			return self::response('error', "The file '{$data}' does not have the requested dimensions '{$imgSize}'.");
		}

		return self::response('success');
	}

	public static function size(string $path, int $size): object {
		$path = self::replace($path);
		$file_size_kb = filesize($path) / 1024;

		if ($file_size_kb > $size) {
			return self::response('error', "The file '{$data}' is larger than the requested size.");
		}

		return self::response('success');
	}

	public static function view(string $path): array {
		$path = self::replace($path);
		$list = scandir($path, 1);
		$data = [];

		for ($i = 0; $i < (count($list) - 2); $i++) {
			array_push($data, "{$path}{$list[$i]}");
		}

		return $data;
	}

	public static function remove(string $path): object {
		if (!unlink($path)) {
			return self::response('error', "The file '{$path}' has not been removed.");
		}

		return self::response('success');
	}

	public static function exist(string $path): object {
		if (!file_exists($path)) {
			return self::response('error', "The file/folder '{$path}' does not exist.");
		}

		return self::response('success');
	}

	public static function rename(string $file, ?string $indicative = null): string {
		if ($indicative != null) {
			return self::replace($indicative) . "-" . md5(hash('sha256', uniqid())) . "." . self::getExtension($file);
		} else {
			return md5(hash('sha256', uniqid())) . "." . self::getExtension($file);
		}
	}

	public static function upload(string $tmp_name, string $name, ?string $path = null): object {
		$path = $path === null ? self::$url_path : $path;
		$path = self::replace($path);
		self::folder($path);

		if (!move_uploaded_file($tmp_name, "{$path}{$name}")) {
			return self::response('error', "The file '{$name}' was not loaded.");
		}

		return self::response('success');
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

	public static function folder(?string $path = null): object {
		$path = self::replace($path === null ? self::$url_path : $path);

		$requestExist = self::exist($path);
		if ($requestExist->status === 'error') {
			if (mkdir($path, 0777, true)) {
				return self::response('success');
			} else {
				return self::response('error', "Directory '{$path}' not created");
			}
		} else {
			return self::response('success');
		}
	}

	public static function validate(array $files, array $exts): object {
		foreach ($files as $key_file => $file) {
			$file_extension = self::getExtension($file);

			if (!in_array($file_extension, $exts)) {
				return self::response('error', "The file '{$file}' does not have the required extension.");
				break;
			}
		}

		return self::response('success');
	}

	public static function replace(string $value): string {
		$value = str_replace("á", "á", $value);
		$value = str_replace("é", "é", $value);
		$value = str_replace("í", "í", $value);
		$value = str_replace("ó", "ó", $value);
		$value = str_replace("ú", "ú", $value);
		$value = str_replace("ñ", "ñ", $value);
		$value = str_replace("Ã¡", "á", $value);
		$value = str_replace("Ã©", "é", $value);
		$value = str_replace("Ã", "í", $value);
		$value = str_replace("Ã³", "ó", $value);
		$value = str_replace("Ãº", "ú", $value);
		$value = str_replace("Ã±", "ñ", $value);
		$value = str_replace("Ã", "á", $value);
		$value = str_replace("Ã‰", "é", $value);
		$value = str_replace("Ã", "í", $value);
		$value = str_replace("Ã“", "ó", $value);
		$value = str_replace("Ãš", "ú", $value);
		$value = str_replace("Ã‘", "ñ", $value);
		$value = str_replace("&aacute;", "á", $value);
		$value = str_replace("&eacute;", "é", $value);
		$value = str_replace("&iacute;", "í", $value);
		$value = str_replace("&oacute;", "ó", $value);
		$value = str_replace("&uacute;", "ú", $value);
		$value = str_replace("&ntilde;", "ñ", $value);

		return $value;
	}

}