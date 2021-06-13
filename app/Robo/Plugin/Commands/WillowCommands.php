<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Willow\Robo\Script;

class WillowCommands extends RoboBase
{
    /**
     * Launch the Willow Framework User's Guide in the default web browser
     */
    final public function docs(): void {
        $this->taskOpenBrowser('https://www.notion.so/ryannerd/Get-Started-bf56317580884ccd95ed8d3889f83c72')->run();
    }

    /**
     * Launch the built-in PHP server and load the Sample in a web browser
     */
    final public function sample(): void {
        $this->taskServer(8088)
            ->host('127.0.0.1')
            ->dir(__DIR__ . '/../../../../public')
            ->background()
            ->run();
        $this->taskOpenBrowser('http://localhost:8088/v1/sample/Hello-World')->run();
        sleep(15);
    }

    /**
     * Show Willow's fancy banner
     */
    final public function banner(): void {
        Script::fancyBanner($this->cli);
    }
}
