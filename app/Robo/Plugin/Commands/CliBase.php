<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use JetBrains\PhpStorm\NoReturn;
use League\CLImate\CLImate;
use League\CLImate\TerminalObject\Dynamic\Confirm;
use Throwable;

class CliBase
{
    private static CLImate|null $cli = null;

    /**
     * If the CLIMate object has not been saved then do so and return it.
     * @return CLImate
     */
    final public static function getCli(): CLImate {
        if (self::$cli === null) {
            self::$cli = new CLImate();
        }
        return self::$cli;
    }

    /**
     * Display ascii art animations
     * @param string $asciiArtFile
     * @param int $speed
     * @param string $enterFrom
     */
    final public static function billboard(string $asciiArtFile, int $speed, string $enterFrom): void {
        $cli = self::getCli();
        $cli->clear();
        $cli->forceAnsiOn();
        $cli->green()->border('*');
        $cli->addArt(__DIR__ . '/Billboards');
        if (substr($enterFrom, 0, 1) === '-') {
            $cli->bold()->lightGreen()->animation($asciiArtFile)->speed($speed)->exitTo(substr($enterFrom, 1));
        } else {
            $cli->bold()->lightGreen()->animation($asciiArtFile)->speed($speed)->enterFrom($enterFrom);
        }
        $cli->green()->border('*');
    }

    /**
     * Display an optional error message and prompt the user if they want to see the details of the error then die.
     * @param Throwable $throwable
     * @param array|null $message
     */
    #[NoReturn]
    public static function showThrowableAndDie(Throwable $throwable, ?array $message = null): void { // phpcs:ignore
        $cli = new CLImate();
        $cli->br();
        $cli->bold()->yellow()->border('*');
        if ($message !== null) {
            foreach ($message as $text) {
                $cli->bold()->yellow($text);
            }
        } else {
            $cli->bold()->yellow('An error has occurred.');
        }
        $cli->br();
        /** @var Confirm $input */
        $input = $cli->bold()->lightGray()->confirm('Do you want to see the error details?');
        $cli->bold()->yellow()->border('*');
        if ($input->confirmed()) {
            $cli->br();
            $cli->bold()->red()->json([self::parseThrowableToArray($throwable)]);
            $cli->br();
        }
        die();
    }

    /**
     * Given a Throwable object parse the properties and return the result as [['label' => 'value],...]
     * @param Throwable $t
     * @return array[]
     */
    private static function parseThrowableToArray(Throwable $t): array {
        $traceString = $t->getTraceAsString();
        $tracer = explode("\n", $traceString);
        $contents = [
            'Message' => $t->getMessage(),
            'File' => $t->getFile(),
            'Line' => (string)$t->getLine()
        ];
        foreach ($tracer as $key => $value) {
            $contents['Trace' . $key] = $value;
        }
        return $contents;
    }
}
