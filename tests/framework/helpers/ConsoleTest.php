<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\helpers;

use Yii;
use yii\helpers\Console;
use yiiunit\TestCase;

/**
 * @group helpers
 * @group console
 */
class ConsoleTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        // destroy application, Helper must work without Yii::$app
        $this->destroyApplication();

        $this->setupStreams();
    }

    /**
     * Set up streams for Console helper stub
     */
    protected function setupStreams()
    {
        ConsoleStub::$inputStream = fopen('php://memory', 'w+');
        ConsoleStub::$outputStream = fopen('php://memory', 'w+');
        ConsoleStub::$errorStream = fopen('php://memory', 'w+');
    }

    /**
     * Clean streams in Console helper stub
     */
    protected function truncateStreams()
    {
        ftruncate(ConsoleStub::$inputStream, 0);
        ftruncate(ConsoleStub::$outputStream, 0);
        ftruncate(ConsoleStub::$errorStream, 0);
    }

    /**
     * Read data from Console helper output stream
     *
     * @return string
     */
    protected function readFromOutput()
    {
        rewind(ConsoleStub::$outputStream);

        return fread(ConsoleStub::$outputStream, 1024);
    }

    /**
     * Write data to Console helper input stream
     *
     * @param string $data entering data
     * @return void
     */
    protected function writeIntoInput($data)
    {
        fwrite(ConsoleStub::$inputStream, $data . PHP_EOL);

        rewind(ConsoleStub::$inputStream);
    }

    public function testStripAnsiFormat()
    {
        ob_start();
        ob_implicit_flush(false);
        echo 'a';
        Console::moveCursorForward(1);
        echo 'a';
        Console::moveCursorDown(1);
        echo 'a';
        Console::moveCursorUp(1);
        echo 'a';
        Console::moveCursorBackward(1);
        echo 'a';
        Console::moveCursorNextLine(1);
        echo 'a';
        Console::moveCursorPrevLine(1);
        echo 'a';
        Console::moveCursorTo(1);
        echo 'a';
        Console::moveCursorTo(1, 2);
        echo 'a';
        Console::clearLine();
        echo 'a';
        Console::clearLineAfterCursor();
        echo 'a';
        Console::clearLineBeforeCursor();
        echo 'a';
        Console::clearScreen();
        echo 'a';
        Console::clearScreenAfterCursor();
        echo 'a';
        Console::clearScreenBeforeCursor();
        echo 'a';
        Console::scrollDown();
        echo 'a';
        Console::scrollUp();
        echo 'a';
        Console::hideCursor();
        echo 'a';
        Console::showCursor();
        echo 'a';
        Console::saveCursorPosition();
        echo 'a';
        Console::restoreCursorPosition();
        echo 'a';
        Console::beginAnsiFormat([Console::FG_GREEN, Console::BG_BLUE, Console::UNDERLINE]);
        echo 'a';
        Console::endAnsiFormat();
        echo 'a';
        Console::beginAnsiFormat([Console::xtermBgColor(128), Console::xtermFgColor(55)]);
        echo 'a';
        Console::endAnsiFormat();
        echo 'a';
        $output = Console::stripAnsiFormat(ob_get_clean());
        ob_implicit_flush(true);
        // $output = str_replace("\033", 'X003', $output );// uncomment for debugging
        $this->assertEquals(str_repeat('a', 25), $output);
    }

    /*public function testScreenSize()
    {
        for ($i = 1; $i < 20; $i++) {
            echo implode(', ', Console::getScreenSize(true)) . "\n";
            ob_flush();
            sleep(1);
        }
    }*/

    public function ansiFormats()
    {
        return [
            ['test', 'test'],
            [Console::ansiFormat('test', [Console::FG_RED]), '<span style="color: red;">test</span>'],
            ['abc' . Console::ansiFormat('def', [Console::FG_RED]) . 'ghj', 'abc<span style="color: red;">def</span>ghj'],
            ['abc' . Console::ansiFormat('def', [Console::FG_RED, Console::BG_GREEN]) . 'ghj', 'abc<span style="color: red;background-color: lime;">def</span>ghj'],
            ['abc' . Console::ansiFormat('def', [Console::FG_GREEN, Console::FG_RED, Console::BG_GREEN]) . 'ghj', 'abc<span style="color: red;background-color: lime;">def</span>ghj'],
            ['abc' . Console::ansiFormat('def', [Console::BOLD, Console::BG_GREEN]) . 'ghj', 'abc<span style="font-weight: bold;background-color: lime;">def</span>ghj'],

            [
                Console::ansiFormat('test', [Console::UNDERLINE, Console::OVERLINED, Console::CROSSED_OUT, Console::FG_GREEN]),
                '<span style="text-decoration: underline overline line-through;color: lime;">test</span>',
            ],

            [Console::ansiFormatCode([Console::RESET]) . Console::ansiFormatCode([Console::RESET]), ''],
            [Console::ansiFormatCode([Console::RESET]) . Console::ansiFormatCode([Console::RESET]) . 'test', 'test'],
            [Console::ansiFormatCode([Console::RESET]) . 'test' . Console::ansiFormatCode([Console::RESET]), 'test'],

            [
                Console::ansiFormatCode([Console::BOLD]) . 'abc' . Console::ansiFormatCode([Console::RESET, Console::FG_GREEN]) . 'ghj' . Console::ansiFormatCode([Console::RESET]),
                '<span style="font-weight: bold;">abc</span><span style="color: lime;">ghj</span>',
            ],
            [
                Console::ansiFormatCode([Console::FG_GREEN]) . ' a ' . Console::ansiFormatCode([Console::BOLD]) . 'abc' . Console::ansiFormatCode([Console::RESET]) . 'ghj',
                '<span style="color: lime;"> a <span style="font-weight: bold;">abc</span></span>ghj',
            ],
            [
                Console::ansiFormat('test', [Console::FG_GREEN, Console::BG_BLUE, Console::NEGATIVE]),
                '<span style="background-color: lime;color: blue;">test</span>',
            ],
            [
                Console::ansiFormat('test', [Console::NEGATIVE]),
                'test',
            ],
            [
                Console::ansiFormat('test', [Console::CONCEALED]),
                '<span style="visibility: hidden;">test</span>',
            ],
        ];
    }

    /**
     * @dataProvider ansiFormats
     * @param string $ansi
     * @param string $html
     */
    public function testAnsi2Html($ansi, $html)
    {
        $this->assertEquals($html, Console::ansiToHtml($ansi));
    }

    /**
     * @covers \yii\helpers\BaseConsole::prompt()
     */
    public function testPrompt()
    {
        $this->writeIntoInput('smth');
        ConsoleStub::prompt('Testing prompt');
        $this->assertEquals('Testing prompt ', $this->readFromOutput());
        $this->truncateStreams();

        $this->writeIntoInput('smth');
        ConsoleStub::prompt('Testing prompt with default', ['default' => 'myDefault']);
        $this->assertEquals('Testing prompt with default [myDefault] ', $this->readFromOutput());
        $this->truncateStreams();

        $this->writeIntoInput('cat');
        $result = ConsoleStub::prompt('Check clear input');
        $this->assertEquals('cat', $result);
        $this->truncateStreams();

        $this->writeIntoInput('');
        $result = ConsoleStub::prompt('No input with default', ['default' => 'x']);
        $this->assertEquals('x', $result);
        $this->truncateStreams();

        $this->writeIntoInput(PHP_EOL . 'smth');
        $result = ConsoleStub::prompt('SmthRequired', ['required' => true]);
        $this->assertEquals('SmthRequired Invalid input.' . PHP_EOL . 'SmthRequired ', $this->readFromOutput());
        $this->truncateStreams();

        $this->writeIntoInput('cat' . PHP_EOL . '15');
        $result = ConsoleStub::prompt('SmthDigit', ['pattern' => '/^\d+$/']);
        $this->assertEquals('SmthDigit Invalid input.' . PHP_EOL . 'SmthDigit ', $this->readFromOutput());
        $this->truncateStreams();

        $this->writeIntoInput('cat' . PHP_EOL . '15');
        $result = ConsoleStub::prompt('SmthNumeric', ['validator' => function ($value, &$error) {
            return is_numeric($value);
        }]);
        $this->assertEquals('SmthNumeric Invalid input.' . PHP_EOL . 'SmthNumeric ', $this->readFromOutput());
        $this->truncateStreams();

        $this->writeIntoInput('cat' . PHP_EOL . '15');
        $result = ConsoleStub::prompt('SmthNumeric', ['validator' => function ($value, &$error) {
            if (!$response = is_numeric($value)) {
                $error = 'MyCustomError';
            }

            return $response;
        }]);
        $this->assertEquals('SmthNumeric MyCustomError' . PHP_EOL . 'SmthNumeric ', $this->readFromOutput());
        $this->truncateStreams();
    }

    /**
     * @covers \yii\helpers\BaseConsole::confirm()
     */
    public function testConfirm()
    {
        $this->writeIntoInput('y');
        ConsoleStub::confirm('Are you sure?');
        $this->assertEquals('Are you sure? (yes|no) [no]:', $this->readFromOutput());
        $this->truncateStreams();

        $this->writeIntoInput('y');
        ConsoleStub::confirm('Are you sure?', true);
        $this->assertEquals('Are you sure? (yes|no) [yes]:', $this->readFromOutput());
        $this->truncateStreams();

        $this->writeIntoInput('y');
        ConsoleStub::confirm('Are you sure?', false);
        $this->assertEquals('Are you sure? (yes|no) [no]:', $this->readFromOutput());
        $this->truncateStreams();

        foreach ([
            'y' => true,
            'Y' => true,
            'yes' => true,
            'YeS' => true,
            'n' => false,
            'N' => false,
            'no' => false,
            'NO' => false,
            'WHAT?!' . PHP_EOL . 'yes' => true,
        ] as $currInput => $currAssertion) {
            $this->writeIntoInput($currInput);
            $result = ConsoleStub::confirm('Are you sure?');
            $this->assertEquals($currAssertion, $result, $currInput);
            $this->truncateStreams();
        }
    }

    /**
     * @covers \yii\helpers\BaseConsole::select()
     */
    public function testSelect()
    {
        $options = [
            'c' => 'cat',
            'd' => 'dog',
            'm' => 'mouse',
        ];

        $this->writeIntoInput('c');
        $result = ConsoleStub::select('Usual behavior', $options);
        $this->assertEquals('Usual behavior [c,d,m,?]: ', $this->readFromOutput());
        $this->assertEquals('c', $result);
        $this->truncateStreams();

        $this->writeIntoInput('?' . PHP_EOL . 'm');
        $result = ConsoleStub::select('Smth', $options);
        $this->assertEquals(
            'Smth [c,d,m,?]: '
                . ' c - cat'
                . PHP_EOL
                . ' d - dog'
                . PHP_EOL
                . ' m - mouse'
                . PHP_EOL
                . ' ? - Show help'
                . PHP_EOL
                . 'Smth [c,d,m,?]: ',
            $this->readFromOutput()
        );
        $this->truncateStreams();
    }
}
