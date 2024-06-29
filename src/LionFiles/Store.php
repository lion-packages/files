<?php

declare(strict_types=1);

namespace Lion\Files;

use Exception;
use SplFileInfo;
use stdClass;

/**
 * Manipulate system files
 *
 * @property string $urlPath [Default storage path]
 *
 * @package Lion\Files
 */
class Store
{
    /**
     * [Default storage path]
     *
     * @var string $urlPath
     */
    protected string $urlPath = 'storage/upload-files/';

    /**
     * Normalize routes depending on OS type
     *
     * @param string $path [Defined route]
     *
     * @return string
     */
    public function normalizePath(string $path): string
    {
        return implode(DIRECTORY_SEPARATOR, explode('/', $path));
    }

    /**
     * Gets the file from the defined path
     *
     * @param string $path [Defined route]
     *
     * @return string|false
     */
    public function get(string $path): string|false
    {
        return file_get_contents($this->normalizePath($path));
    }

    /**
     * Validates if the resolution of a file is valid
     *
     * @param string $path [Defined route]
     * @param string $fileName [File name]
     * @param string $imgSize [Image size]
     *
     * @return stdClass
     */
    public function imageSize(string $path, string $fileName, string $imgSize): stdClass
    {
        $dataFile = getimagesize($this->normalizePath("{$path}{$fileName}"));

        $union = "{$dataFile[0]}x{$dataFile[1]}";

        if ($union != $imgSize) {
            return (object) [
                'status' => 'error',
                'message' => "The file '{$fileName}' does not have the requested dimensions '{$imgSize}'",
            ];
        }

        return (object) [
            'status' => 'success',
            'message' => "File '{$fileName}' meets requested dimensions '{$imgSize}'",
        ];
    }

    /**
     * Validates if the weight of a file is valid in KB
     *
     * @param string $file [File]
     * @param int|float $fileSize [File]
     *
     * @return stdClass
     */
    public function size(string $file, int|float $fileSize): stdClass
    {
        $file = $this->replace($file);

        $fileSizeKb = filesize($this->normalizePath($file)) / 1024;

        if ($fileSizeKb > $fileSize) {
            return (object) [
                'status' => 'error',
                'message' => "The file '{$file}' is larger than the requested size",
            ];
        }

        return (object) [
            'status' => 'success',
            'message' => "The file '{$file}' meets the requested size",
        ];
    }

    /**
     * Returns an array with all the files and folders that are within a defined
     * path
     *
     * @param string $path [Defined route]
     *
     * @return array<int, string>|stdClass
     */
    public function view(string $path): array|stdClass
    {
        $responseExist = $this->exist($this->normalizePath($path));

        if ($responseExist->status === 'error') {
            return $responseExist;
        }

        $path = $this->replace($path);

        $list = scandir($this->normalizePath($path), 1);

        $data = [];

        for ($i = 0; $i < (count($list) - 2); $i++) {
            $data[] = $this->normalizePath("{$path}{$list[$i]}");
        }

        return $data;
    }

    /**
     * Remove files from a defined path
     *
     * @param string $path [Defined route]
     *
     * @return stdClass
     *
     * @throws Exception [If an error occurs while deleting the file]
     */
    public function remove(string $path): stdClass
    {
        $exist = $this->exist($this->normalizePath($path));

        if ($exist->status === 'error') {
            return (object) [
                'status' => 'error',
                'message' => "The file '{$path}' could not be removed because it does not exist",
            ];
        }

        try {
            unlink($this->normalizePath($path));

            return (object) [
                'status' => 'success',
                'message' => "The file '{$path}' has been deleted",
            ];
        } catch (Exception $e) {
            return (object) [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Checks if a file/folder exists in a defined path
     *
     * @param string $path [Defined route]
     *
     * @return stdClass
     */
    public function exist(string $path): stdClass
    {
        if (!file_exists($this->normalizePath($path))) {
            return (object) [
                'status' => 'error',
                'message' => "The file/folder '{$path}' does not exist",
            ];
        }

        return (object) [
            'status' => 'success',
            'message' => "The file/folder '{$path}' exists",
        ];
    }

    /**
     * Renames a file and allows adding a callsign to it
     *
     * @param string $file [File]
     * @param string|null $indicative [File initial callsign]
     *
     * @return string
     */
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
     *
     * @param string $tmpName [Temporary file]
     * @param string $name [File name]
     * @param string $path [Defined route]
     *
     * @return stdClass
     */
    public function upload(string $tmpName, string $name, string $path = ''): stdClass
    {
        $path = $this->normalizePath($this->replace('' === $path ? $this->urlPath : $path));

        $this->folder($path);

        if (!move_uploaded_file($tmpName, $this->normalizePath("{$path}{$name}"))) {
            return (object) [
                'status' => 'error',
                'message' => "The file '{$name}' was not loaded",
            ];
        }

        return (object) [
            'status' => 'success',
            'message' => "The file '{$name}' was uploaded",
        ];
    }

    /**
     * Gets the name extension of a file
     *
     * @param string $path [Defined route]
     *
     * @return string
     */
    public function getExtension(string $path): string
    {
        return (new SplFileInfo($this->normalizePath($path)))
            ->getExtension();
    }

    /**
     * Gets the name and extension of a file
     *
     * @param string $path [Defined route]
     *
     * @return string
     */
    public function getName(string $path): string
    {
        return (new SplFileInfo($this->normalizePath($path)))
            ->getBasename('.' . $this->getExtension($path));
    }

    /**
     * Gets the name of a file
     *
     * @param string $path [Defined route]
     *
     * @return string
     */
    public function getBasename(string $path): string
    {
        return (new SplFileInfo($this->normalizePath($path)))
            ->getBasename();
    }

    /**
     * Checks if a folder does not exist and creates it
     *
     * @param string $path [Defined route]
     *
     * @return stdClass
     */
    public function folder(string $path): stdClass
    {
        $path = $this->normalizePath($this->replace($path));

        $requestExist = $this->exist($path);

        if ($requestExist->status === 'error') {
            if (mkdir($path, 0777, true)) {
                return (object) [
                    'status' => 'success',
                    'message' => "Directory '{$path}' created",
                ];
            } else {
                return (object) [
                    'status' => 'error',
                    'message' => "Directory '{$path}' not created",
                ];
            }
        }

        return (object) [
            'status' => 'success',
            'message' => $requestExist->message,
        ];
    }

    /**
     * Validate the extensions allowed for a file
     *
     * @param array<int, string> $files [File path list]
     * @param array<int, string> $exts [Allowed extensions]
     *
     * @return stdClass
     */
    public function validate(array $files, array $exts): stdClass
    {
        foreach ($files as $file) {
            $fileExtension = $this->getExtension($file);

            if (!in_array($fileExtension, $exts)) {
                return (object) [
                    'status' => 'error',
                    'message' => "The file '{$file}' does not have the required extension",
                ];

                break;
            }
        }

        return (object) [
            'status' => 'success',
            'message' => 'Files have required extension',
        ];
    }

    /**
     * Replaces invalid characters with valid characters
     *
     * @param string $str [Text string]
     *
     * @return string
     */
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
