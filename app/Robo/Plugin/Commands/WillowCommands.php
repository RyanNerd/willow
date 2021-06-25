<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use League\CLImate\CLImate;
use Willow\Robo\Script;
use Robo\Tasks;
use Illuminate\Support\Str;

class WillowCommands extends Tasks
{
    protected CLImate $cli;

    public function __construct() {
        $this->cli = CliBase::getCli();
    }

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
        Script::fancyBanner();
    }

    final public function billboardWelcome(): void {
        CliBase::billboard('welcome', 160, 'top');
        $input = $this->cli->bold()->white()->input('Press enter to start. Ctrl-C to quit.');
        $input->prompt();
        CliBase::billboard('welcome', 160, '-top');
    }

    final public function billboardEnv(): void {
        $cli = $this->cli;
        CliBase::billboard('make-env', 660, 'right');
        $input = $cli->bold()->white()->input('Press enter to continue. Ctrl-C to quit.');
        $input->prompt();
        CliBase::billboard('make-env', 660, '-right');
    }

    final public function dank(): void {
        $cli = $this->cli;
        CliBase::billboard('make-tables', 165, 'bottom');
        $input = $cli->bold()->white()->input('Press enter to begin. Ctrl-C to quit.');
        $input->prompt();
        CliBase::billboard('make-tables', 165, '-top');
        $input = $cli->bold()->white()->input('Press enter to begin. Ctrl-C to quit.');
        $input->prompt();
        CliBase::billboard('construction', 165, 'left');
        $input = $cli->bold()->white()->input('Press enter to begin. Ctrl-C to quit.');
        $input->prompt();
        CliBase::billboard('construction', 165, '-right');
        CliBase::billboard('backhoe', 165, 'left');
    }

    final public function snake(string $s) {
        $cli = $this->cli;
        $cli->bold()->yellow(Str::snake($s));
    }
}
