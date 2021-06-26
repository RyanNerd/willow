<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Robo\Tasks;
use Willow\Robo\Script;

class WillowCommands extends Tasks
{
    /**
     * Launch the Willow Framework User's Guide in the web browser
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
}
