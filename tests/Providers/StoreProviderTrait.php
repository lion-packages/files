<?php

declare(strict_types=1);

namespace Tests\Providers;

trait StoreProviderTrait
{
    private const string URL_PATH = './storage/';

    /**
     * @return array<int, array{
     *     path: string,
     *     content: array<int|string, mixed>|string,
     *     return: false|string,
     * }>
     */
    public static function createProvider(): array
    {
        return [
            [
                'path' => self::URL_PATH . 'file.txt',
                'content' => 'content',
                'return' => 'content',
            ],
            [
                'path' => self::URL_PATH . 'file.log',
                'content' => 'content',
                'return' => 'content',
            ],
            [
                'path' => self::URL_PATH . 'file.php',
                'content' => <<<PHP
                <?php

                echo 'content';
                PHP,
                'return' => <<<PHP
                <?php

                echo 'content';
                PHP,
            ],
            [
                'path' => self::URL_PATH . 'file.json',
                'content' => [
                    'message' => 'content',
                ],
                'return' => json_encode([
                    'message' => 'content',
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            ],
        ];
    }
}
