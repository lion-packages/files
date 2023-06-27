<?php

namespace LionFiles;

class Store {

	public static string $url_path = "storage/upload_files/";

	public static function get(string $path): string|false {
		return file_get_contents($path);
	}

	public static function imageSize(string $path, string $data_path, string $imgSize): object {
		$data_file = getimagesize("{$path}{$data_path}");
		$union = "{$data_file[0]}x{$data_file[1]}";

		if ($union != $imgSize) {
			return (object) [
				'status' => "error",
				'message' => "the file '{$data_path}' does not have the requested dimensions '{$imgSize}'"
			];
		}

		return (object) [
			'status' => "success",
			'message' => "file '{$data_path}' meets requested dimensions '{$imgSize}'"
		];
	}

	public static function size(string $path, int $size): object {
		$path = self::replace($path);
		$file_size_kb = filesize($path) / 1024;

		if ($file_size_kb > $size) {
			return (object) [
				'status' => "error",
				'message' => "the file '{$path}' is larger than the requested size"
			];
		}

		return (object) [
			'status' => "success",
			'message' => "the file '{$path}' meets the requested size"
		];
	}

	public static function view(string $path): array|object {
		$responseExist = self::exist($path);

		if ($responseExist->status === 'error') {
			return $responseExist;
		}

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
			return (object) [
				'status' => "error",
				'message' => "the file '{$path}' has not been removed"
			];
		}

		return (object) [
			'status' => "success",
			'message' => "the file '{$path}' has been deleted"
		];
	}

	public static function exist(string $path): object {
		if (!file_exists($path)) {
			return (object) [
				'status' => "error",
				'message' => "the file/folder '{$path}' does not exist"
			];
		}

		return (object) [
			'status' => "success",
			'message' => "the file/folder '{$path}' exists"
		];
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
			return (object) [
				'status' => "error",
				'message' => "the file '{$name}' was not loaded"
			];
		}

		return (object) [
			'status' => "success",
			'message' => "the file '{$name}' was uploaded"
		];
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
				return (object) [
					'status' => "success",
					'message' => "directory '{$path}' created"
				];
			} else {
				return (object) [
					'status' => "error",
					'message' => "directory '{$path}' not created"
				];
			}
		}

		return (object) [
			'status' => "success",
			'message' => $requestExist->message
		];
	}

	public static function validate(array $files, array $exts): object {
		foreach ($files as $key_file => $file) {
			$file_extension = self::getExtension($file);

			if (!in_array($file_extension, $exts)) {
				return (object) [
					'status' => "error",
					'message' => "the file '{$file}' does not have the required extension"
				];
				break;
			}
		}

		return (object) [
			'status' => "success",
			'message' => "files have required extension"
		];
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