<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

class SampleCommand extends CommandBase
{
    /**
     * Launch the built-in PHP server & load the sample in the web
     */
    final public function sample(): void {
        $this->runSample();
    }
}
