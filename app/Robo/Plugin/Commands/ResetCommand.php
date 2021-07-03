<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use League\CLImate\TerminalObject\Dynamic\Input;

class ResetCommand extends CommandBase
{
    /**
     * Resets the project allowing for a rebuild
     */
    final public function reset(): void {
        $cli = CliBase::getCli();

        // If .viridian doesn't exist then nothing to do
        if (!file_exists(self::VIRIDIAN_PATH)) {
            $cli->yellow('Project appears to be uninitialized. Nothing to do.');
            die();
        }

        $cli->br();
        $cli->bold()
            ->backgroundLightRed()
            ->white()
            ->border('*');
        $cli
            ->bold()
            ->backgroundLightRed()
            ->white('Running reset will allow the make command to be re-run.');
        $cli
            ->bold()
            ->backgroundLightRed()
            ->white('Running make more than once is a destructive action.');
        $cli
            ->bold()
            ->backgroundLightRed()
            ->white('Any code in the controllers, models, routes, etc. will be overwritten.');
        $cli->br();
        /** @var Input $input */
        $input = $cli->bold()->lightGray()->confirm('Are you sure you want to reset?');
        if ($input->confirmed()) {
            if (unlink(self::VIRIDIAN_PATH) === false) {
                $cli
                    ->bold()
                    ->backgroundLightRed()
                    ->white()
                    ->border('*');
                $cli
                    ->bold()
                    ->backgroundWhite()
                    ->red('Unable to reset. You will need to manually delete: ' . self::VIRIDIAN_PATH);
            };
        }
    }
}
