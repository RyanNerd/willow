<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Robo\Task\Development\PhpServer;
use Willow\Robo\Script;

class WillowCommands extends RoboBase
{
    /**
     * @var PhpServer
     */
    private $server;

    /**
     * Launch the Willow Framework User's Guide in the default web browser
     */
    public function docs(): void
    {
        $this->taskOpenBrowser('https://www.notion.so/ryannerd/Get-Started-bf56317580884ccd95ed8d3889f83c72')->run();
    }

    /**
     * Launch the built-in PHP web server and launch the Sample in a web browser
     */
    public function sample(): void
    {
        $server = $this->taskServer(8088);
        $this->server = $server->host('127.0.0.1')
            ->dir(__DIR__ . '/../../../../public')
            ->background()
            ->run();

        $this->taskOpenBrowser('http://localhost:8088/v1/sample/Hello-World')->run();
        sleep(20);
    }

    /**
     * Show Willow's fancy banner
     */
    public function banner(): void
    {
        Script::fancyBanner();
    }

    /*
     * Execute the unit tests
     */
    public function test(): void
    {
/*
        // This executes ZERO tests?!?
        $this->taskPhpUnit(__DIR__ . '/../../../../vendor/bin/phpunit')
           ->dir(__DIR__ .'/../../../../tests')
           ->filter('tests')
           ->run();
*/

        // This works!
        $this->_exec(__DIR__ . '/../../../../vendor/bin/phpunit ' . __DIR__ . '/../../../../tests');
    }
}
