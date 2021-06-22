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
     * Show Willow's fancy banner
     */
    final public function banner(): void {
        Script::fancyBanner($this->cli);
    }

    final public function welcome(): void {
        $this->billboard(__DIR__ . '/Billboards', 'welcome', 160, 'top');
        $input = $this->cli->bold()->white()->input('Press enter to start. Ctrl-C to quit.');
        $input->prompt();
        $this->billboard(__DIR__ . '/Billboards', 'welcome', 160, '-top');
    }

    final public function env(): void {
        $cli = $this->cli;
        $this->billboard(__DIR__ . '/Billboards', 'make-env', 660, 'right');
        $input = $cli->bold()->white()->input('Press enter to continue. Ctrl-C to quit.');
        $input->prompt();
        $this->billboard(__DIR__ . '/Billboards', 'make-env', 660, '-right');
    }

    final public function dank(): void {
        $cli = $this->cli;
        $this->billboard(__DIR__ . '/Billboards', 'make-tables', 165, 'bottom');
        $input = $cli->bold()->white()->input('Press enter to begin. Ctrl-C to quit.');
        $input->prompt();
        $this->billboard(__DIR__ . '/Billboards', 'make-tables', 165, '-top');
        $input = $cli->bold()->white()->input('Press enter to begin. Ctrl-C to quit.');
        $input->prompt();
        $this->billboard(__DIR__ . '/Billboards', 'construction', 165, 'left');
        $input = $cli->bold()->white()->input('Press enter to begin. Ctrl-C to quit.');
        $input->prompt();
        $this->billboard(__DIR__ . '/Billboards', 'construction', 165, '-right');
        $this->billboard(__DIR__ . '/Billboards', 'backhoe', 165, 'left');
    }
}
