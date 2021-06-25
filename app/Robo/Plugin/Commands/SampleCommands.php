<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Robo\Tasks;

class SampleCommands extends Tasks
{
    /**
     * Launch the built-in PHP server & load the sample in the web
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
}
