<?php

declare(strict_types=1);

namespace LionFiles;

use Exception;
use SplFileInfo;

class Store
{
    protected string $url_path = 'storage/upload-files/';

    /**
     * Gets the file from the defined path
     * */
    public function get(string $path): string|false
    {
        return file_get_contents($path);
    }

    /**
     * Validates if the resolution of a file is valid
     * */
    public function imageSize(string $path, string $data_path, string $imgSize): object
    {
        $data_file = getimagesize("{$path}{$data_path}");
        $union = "{$data_file[0]}x{$data_file[1]}";

        if ($union != $imgSize) {
            return (object) [
                'status' => 'error',
                'message' => "The file '{$data_path}' does not have the requested dimensions '{$imgSize}'"
            ];
        }

        return (object) [
            'status' => 'success',
            'message' => "File '{$data_path}' meets requested dimensions '{$imgSize}'"
        ];
    }

    /**
     * Validates if the weight of a file is valid in KB
     * */
    public function size(string $file, int|float $fileSize): object
    {
        $file = $this->replace($file);
        $fileSizeKb = filesize($file) / 1024;

        if ($fileSizeKb > $fileSize) {
            return (object) ['status' => 'error', 'message' => "The file '{$file}' is larger than the requested size"];
        }

        return (object) ['status' => 'success', 'message' => "The file '{$file}' meets the requested size"];
    }

    /**
     * Returns an array with all the files and folders that are within a
     * defined path
     * */
    public function view(string $path): array|object
    {
        $responseExist = $this->exist($path);

        if ($responseExist->status === 'error') {
            return $responseExist;
        }

        $path = $this->replace($path);
        $list = scandir($path, 1);
        $data = [];

        for ($i = 0; $i < (count($list) - 2); $i++) {
            array_push($data, "{$path}{$list[$i]}");
        }

        return $data;
    }

    /**
     * Remove files from a defined path
     * */
    public function remove(string $path): object
    {
        $exist = $this->exist($path);

        if ($exist->status === 'error') {
            return (object) [
                'status' => 'error',
                'message' => "The file '{$path}' could not be removed because it does not exist"
            ];
        }

        try {
            unlink($path);

            return (object) ['status' => 'success', 'message' => "The file '{$path}' has been deleted"];
        } catch (Exception $e) {
            return (object) ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Checks if a file/folder exists in a defined path
     * */
    public function exist(string $path): object
    {
        if (!file_exists($path)) {
            return (object) ['status' => 'error', 'message' => "The file/folder '{$path}' does not exist"];
        }

        return (object) ['status' => 'success', 'message' => "The file/folder '{$path}' exists"];
    }

    /**
     * Renames a file and allows adding a callsign to it
     * */
    public function rename(string $file, ?string $indicative = null): string
    {
        if ($indicative != null) {
            return $this->replace($indicative) . "-" . md5(hash('sha256', uniqid())) . "." . $this->getExtension($file);
        } else {
            return md5(hash('sha256', uniqid())) . "." . $this->getExtension($file);
        }
    }

    /**
     * Allows uploading files to a defined path
     * */
    public function upload(string $tmpName, string $name, ?string $path = null): object
    {
        $path = $path === null ? $this->url_path : $path;
        $path = $this->replace($path);
        $this->folder($path);

        if (!move_uploaded_file($tmpName, "{$path}{$name}")) {
            return (object) ['status' => 'error', 'message' => "The file '{$name}' was not loaded"];
        }

        return (object) ['status' => 'success', 'message' => "The file '{$name}' was uploaded"];
    }

    /**
     * Gets the name extension of a file
     * */
    public function getExtension(string $path): string
    {
        return (new SplFileInfo($path))->getExtension();
    }

    /**
     * Gets the name and extension of a file
     * */
    public function getName(string $path): string
    {
        return (new SplFileInfo($path))->getBasename("." . $this->getExtension($path));
    }

    /**
     * Gets the name of a file
     * */
    public function getBasename(string $path): string
    {
        return (new SplFileInfo($path))->getBasename();
    }

    /**
     * Checks if a folder does not exist and creates it
     * */
    public function folder(?string $path = null): object
    {
        $path = $this->replace($path === null ? $this->url_path : $path);
        $requestExist = $this->exist($path);

        if ($requestExist->status === 'error') {
            if (mkdir($path, 0777, true)) {
                return (object) ['status' => 'success', 'message' => "Directory '{$path}' created"];
            } else {
                return (object) ['status' => 'error', 'message' => "Directory '{$path}' not created"];
            }
        }

        return (object) ['status' => 'success', 'message' => $requestExist->message];
    }

    /**
     * Validate the extensions allowed for a file
     * */
    public function validate(array $files, array $exts): object
    {
        foreach ($files as $key_file => $file) {
            $file_extension = $this->getExtension($file);

            if (!in_array($file_extension, $exts)) {
                return (object) [
                    'status' => 'error',
                    'message' => "The file '{$file}' does not have the required extension"
                ];

                break;
            }
        }

        return (object) ['status' => 'success', 'message' => 'Files have required extension'];
    }

    /**
     * Replaces invalid characters with valid characters
     * */
    public function replace(string $str): string
    {
        $str = str_replace("á", "á", $str);
        $str = str_replace("é", "é", $str);
        $str = str_replace("í", "í", $str);
        $str = str_replace("ó", "ó", $str);
        $str = str_replace("ú", "ú", $str);
        $str = str_replace("ñ", "ñ", $str);
        $str = str_replace("Ã¡", "á", $str);
        $str = str_replace("Ã©", "é", $str);
        $str = str_replace("Ã", "í", $str);
        $str = str_replace("Ã³", "ó", $str);
        $str = str_replace("Ãº", "ú", $str);
        $str = str_replace("Ã±", "ñ", $str);
        $str = str_replace("Ã", "á", $str);
        $str = str_replace("Ã‰", "é", $str);
        $str = str_replace("Ã", "í", $str);
        $str = str_replace("Ã“", "ó", $str);
        $str = str_replace("Ãš", "ú", $str);
        $str = str_replace("Ã‘", "ñ", $str);
        $str = str_replace("&aacute;", "á", $str);
        $str = str_replace("&eacute;", "é", $str);
        $str = str_replace("&iacute;", "í", $str);
        $str = str_replace("&oacute;", "ó", $str);
        $str = str_replace("&uacute;", "ú", $str);
        $str = str_replace("&ntilde;", "ñ", $str);

        return $str;
    }
}
