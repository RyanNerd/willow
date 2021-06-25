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
     * Launch the Willow Framework User's Guide in the web browser
     */
    final public function willowDocs(): void {
        $this->taskOpenBrowser('https://www.notion.so/ryannerd/Get-Started-bf56317580884ccd95ed8d3889f83c72')->run();
    }

    /**
     * Show Willow's fancy banner
     */
    final public function willowBanner(): void {
        Script::fancyBanner();
    }

    final public function willowBuild(): void {
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

    final public function willowSnake(string $s) {
        $cli = $this->cli;
        $cli->bold()->yellow(Str::snake($s));
    }

    final public function willowDash(string $s) {
        $cli = $this->cli;
        $cli->bold()->yellow(str_replace('_', '-', Str::snake($s)));
    }
}
