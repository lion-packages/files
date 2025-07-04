<?php

declare(strict_types=1);

namespace Lion\Files;

use Exception;
use SplFileInfo;
use stdClass;

/**
 * Manipulate system files
 *
 * @package Lion\Files
 */
class Store
{
    /**
     * [Defines how much the file size is divided by to find its size in KB]
     *
     * @const FILE_SIZE
     */
    private const int FILE_SIZE = 1024;

    /**
     * [Permissions to create directories]
     *
     * @const FOLDER_PERMISSIONS
     */
    private const int FOLDER_PERMISSIONS = 0777;

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
     * Gets the namespace of a class through a defined path
     *
     * @param string $file [File path]
     * @param string $namespace [Namespace for the file]
     * @param non-empty-string $split [Separator to get the namespace]
     *
     * @return string
     *
     * @infection-ignore-all
     */
    public function getNamespaceFromFile(string $file, string $namespace, string $split = '/'): string
    {
        $splitFile = explode($split, $file);

        $namespace = str_replace('/', '\\', "{$namespace}{$splitFile[1]}");

        $namespace = str_replace('.php', '', $namespace);

        return trim($namespace);
    }

    /**
     * Creates a file of any type (e.g., txt, json, log) with the given content.
     *
     * If the file extension is .json and the content is an array, it will be
     * automatically encoded as JSON.
     *
     * @param string $path    Full path to the file, including name and extension.
     * @param mixed  $content Content to write to the file. Can be a string or an
     *                        array (for JSON).
     *
     * @return stdClass
     */

    public function create(string $path, mixed $content): stdClass
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if ($extension === 'json' && is_array($content)) {
            $content = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            if ($content === false) {
                return (object) [
                    'code' => 500,
                    'status' => 'file-error',
                    'message' => 'Error while creating JSON file',
                ];
            }
        } elseif (!is_string($content)) {
            return (object) [
                'code' => 500,
                'status' => 'file-error',
                'message' => 'Error while creating file',
            ];
        }

        file_put_contents($path, $content);

        return (object) [
            'code' => 200,
            'status' => 'success',
            'message' => 'File created',
        ];
    }

    /**
     * Get files from a defined path
     *
     * @param string $folder [Defined route]
     *
     * @return array<int, string>
     *
     * @infection-ignore-all
     */
    public function getFiles(string $folder): array
    {
        $files = [];

        /** @var list<string> $content */
        $content = scandir($folder);

        foreach ($content as $element) {
            if ($element != '.' && $element != '..') {
                $path = $folder . '/' . $element;

                if (is_dir($path)) {
                    $files = array_merge($files, $this->getFiles($path));
                } else {
                    /** @var non-empty-string $realPath */
                    $realPath = realpath($path);

                    $files[] = $realPath;
                }
            }
        }

        return $files;
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
     *
     * @infection-ignore-all
     */
    public function imageSize(string $path, string $fileName, string $imgSize): stdClass
    {
        /** @var array<int, string> $dataFile */
        $dataFile = getimagesize($this->normalizePath("{$path}{$fileName}"));

        $union = "{$dataFile[0]}x{$dataFile[1]}";

        if ($union != $imgSize) {
            return (object) [
                'code' => 500,
                'status' => 'file-error',
                'message' => "The file '{$fileName}' does not have the requested dimensions '{$imgSize}'",
            ];
        }

        return (object) [
            'code' => 200,
            'status' => 'success',
            'message' => "File '{$fileName}' meets requested dimensions '{$imgSize}'",
        ];
    }

    /**
     * Validates if the weight of a file is valid in KB
     *
     * @param string $file [File]
     * @param int|float $fileSize [File size]
     *
     * @return stdClass
     */
    public function size(string $file, int|float $fileSize): stdClass
    {
        $file = $this->replace($file);

        $fileSizeKb = filesize($this->normalizePath($file)) / self::FILE_SIZE;

        if ($fileSizeKb > $fileSize) {
            return (object) [
                'code' => 500,
                'status' => 'file-error',
                'message' => "The file '{$file}' is larger than the requested size",
            ];
        }

        return (object) [
            'code' => 200,
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
     *
     * @infection-ignore-all
     */
    public function view(string $path): array|stdClass
    {
        $responseExist = $this->exist($this->normalizePath($path));

        if ('file-error' === $responseExist->status) {
            return $responseExist;
        }

        $path = $this->replace($path);

        /** @var list<string> $list */
        $list = scandir($this->normalizePath($path), 1);

        $size = count($list);

        $data = [];

        for ($i = 0; $i < $size; $i++) {
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
     *
     * @infection-ignore-all
     */
    public function remove(string $path): stdClass
    {
        $exist = $this->exist($this->normalizePath($path));

        if ('file-error' === $exist->status) {
            return (object) [
                'code' => 500,
                'status' => 'file-error',
                'message' => "The file '{$path}' could not be removed because it does not exist",
            ];
        }

        try {
            unlink($this->normalizePath($path));

            return (object) [
                'code' => 200,
                'status' => 'success',
                'message' => "The file '{$path}' has been deleted",
            ];
        } catch (Exception $e) {
            return (object) [
                'code' => $e->getCode(),
                'status' => 'file-error',
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
                'code' => 500,
                'status' => 'file-error',
                'message' => "The file/folder '{$path}' does not exist",
            ];
        }

        return (object) [
            'code' => 200,
            'status' => 'success',
            'message' => "The file/folder '{$path}' exists",
        ];
    }

    /**
     * Renames a file and allows adding a call sign to it
     *
     * @param string $file [File]
     * @param string|null $indicative [File initial call sign]
     *
     * @return string
     */
    public function rename(string $file, ?string $indicative = null): string
    {
        if ($indicative != null) {
            return $this->replace($indicative) . '-' . md5(hash('sha256', uniqid())) . '.' . $this->getExtension($file);
        } else {
            return md5(hash('sha256', uniqid())) . '.' . $this->getExtension($file);
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
                'code' => 500,
                'status' => 'file-error',
                'message' => "The file '{$name}' was not loaded",
            ];
        }

        return (object) [
            'code' => 200,
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
        return new SplFileInfo($this->normalizePath($path))
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
        return new SplFileInfo($this->normalizePath($path))
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
        return new SplFileInfo($this->normalizePath($path))
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

        if ($requestExist->status === 'file-error') {
            if (mkdir($path, self::FOLDER_PERMISSIONS, true)) {
                return (object) [
                    'code' => 200,
                    'status' => 'success',
                    'message' => "Directory '{$path}' created",
                ];
            } else {
                return (object) [
                    'code' => 500,
                    'status' => 'file-error',
                    'message' => "Directory '{$path}' not created",
                ];
            }
        }

        return (object) [
            'code' => 200,
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
                    'code' => 500,
                    'status' => 'file-error',
                    'message' => "The file '{$file}' does not have the required extension",
                ];
            }
        }

        return (object) [
            'code' => 200,
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
     *
     * @infection-ignore-all
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
