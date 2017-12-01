<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\helpers;

use Exception;

use yii\helpers\BaseConsole;

/**
 * Console helper stub for STDIN/STDOUT/STDERR replacement
 *
 * @author Pavel Dovlatov <mysterydragon@yandex.ru>
 */
class ConsoleStub extends BaseConsole
{
    /**
     * @var resource input stream
     */
    public static $inputStream = \STDIN;

    /**
     * @var resource output stream
     */
    public static $outputStream = \STDOUT;

    /**
     * @var resource error stream
     */
    public static $errorStream = \STDERR;

    /**
     * @var bool when it's possible that user print nothing
     */
    public static $allowBlankInput = false;


    /**
     * @inheritdoc
     */
    public static function stdin($raw = false)
    {
        if (self::$inputStream !== \STDIN) {
            // emulating user input
            // real STDIN waits until user prompts something,
            // but other stream can easily go away with blank string

            $response = $raw ? fgets(self::$inputStream) : rtrim(fgets(self::$inputStream), PHP_EOL);

            if ($response === '' && !self::$allowBlankInput) {
                throw new Exception(__METHOD__ . ' didn\'t receive user data');
            }

            return $response;
        }

        return parent::stdin($raw);
    }

    /**
     * @inheritdoc
     */
    public static function stdout($string)
    {
        return fwrite(self::$outputStream, $string);
    }

    /**
     * @inheritdoc
     */
    public static function stderr($string)
    {
        return fwrite(self::$errorStream, $string);
    }
}
