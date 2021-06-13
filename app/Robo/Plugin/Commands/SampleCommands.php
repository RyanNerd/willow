<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

class SampleCommands extends RoboBase
{
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
}
