<?php

namespace LionFiles;

class FILES {

	private static string $url_path = "storage/upload_files/";

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
		foreach ($data_path as $key => $data) {
			$data_file = getimagesize("{$path}{$data}");

			$union = "{$data_file[0]}x{$data_file[1]}";
			if ($union != $imgSize) {
				return self::response('error', "The file '{$data}' does not have the requested dimensions {$imgSize}.");
				break;
			}
		}

		return self::response('success');
	}

	public static function size(string $path, array $data_path, int $size): object {
		$path = self::replace($path);

		foreach ($data_path as $key => $data) {
			$file_size_kb = filesize("{$path}{$data}") / 1024;

			if ($file_size_kb > $size) {
				return self::response('error', "The file '{$data}' is larger than the requested size.");
				break;
			}
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

	public static function remove(array $files): object {
		foreach ($files as $key => $file) {
			if (!unlink($file)) {
				return self::response('error', "The file '{$file}' has not been removed.");
				break;
			}
		}

		return self::response('success');
	}

	public static function exist(array $files): object {
		foreach ($files as $key => $file) {
			if (!file_exists($file)) {
				return self::response('error', "The file/folder '{$file}' does not exist.");
				break;
			}
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

	public static function upload(array $tmps, array $names, ?string $path = null): object {
		$path = $path === null ? self::$url_path : $path;
		$path = self::replace($path);

		$requestFolder = self::folder($path);
		if ($requestFolder->status === 'error') {
			return $requestFolder;
		}

		foreach ($names as $key => $name) {
			if (!move_uploaded_file($tmps[$key], ($path === null ? self::$url_path : $path) . $name)) {
				return self::response('error', "The file '{$name}' was not loaded.");
				break;
			}
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

		$requestExist = self::exist([$path]);
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
		foreach ($files['name'] as $key_file => $file) {
			$file_extension = self::getExtension($file);

			if (!in_array($file_extension, $exts)) {
				return self::response('error', "The file {$file} does not have the required extension.");
				break;
			}
		}

		return self::response('success');
	}

	public static function replace(string $cell): string {
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