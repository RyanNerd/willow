<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Willow\Robo\Script;

class WillowCommands extends CommandBase
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

    /**
     * Start here if you are new to Willow
     */
    final public function start(): void {
        $cli = CliBase::getCli();
        CliBase::billboard('welcome', 165, 'top');
        $input = $cli->bold()->white()->input('Press enter to continue. Ctrl-C to quit.');
        $input->prompt();
        CliBase::billboard('welcome', 265, '-top');
        $cli->clear();

        $cli->green()->border('*');
        $choices = [
            'willow' => 'Learn about Willow',
            'willow-online' => "Go to Willow's <underline><blue>User Guide</blue></underline> web page",
            'willow-github' => "Go to Willow's <underline><blue>Github</blue></underline> web page",
            'slim' => 'Go to the <underline><blue>Slim Framework</blue></underline> web page',
            'eloquent' => 'Go to <underline><blue>Eloquent ORM</blue></underline> web page',
            'sample' => 'Run the sample API',
            'quit' => 'Leave'
        ];
        $input = $cli->bold()->green()->radio('What would you like to do next?', $choices);
        $selection = $input->prompt();

        switch ($selection) {
            case 'willow':
                $this->tutorial();
                break;
            case 'willow-online':
                $this->docs();
                break;
            case 'willow-github':
                $this->taskOpenBrowser('https://www.github.com/ryannerd/willow')->run();
                break;
            case 'slim':
                $this->taskOpenBrowser('http://www.slimframework.com')->run();
                break;
            case 'eloquent':
                $this->taskOpenBrowser('https://laravel.com/docs/8.x/eloquent')->run();
                break;
            case 'sample':
                $this->runSample();
        }
    }

    /**
     * A text based tutorial on how to use Willow
     */
    final public function tutorial(): void {
        $cli = CliBase::getCli();
        CliBase::billboard('tutorial-1', 365, 'left');
        $input = $cli->bold()->white()->input('Press enter to continue. Ctrl-C to quit.');
        $input->prompt();
        CliBase::billboard('tutorial-1', 365, '-left');
        $cli->clear();

        CliBase::billboard('tutorial-2', 165, 'top');
        $input = $cli->bold()->white()->input('Press enter to continue. Ctrl-C to quit.');
        $input->prompt();
        CliBase::billboard('tutorial-2', 265, '-top');
        $cli->clear();

        CliBase::billboard('tutorial-3', 365, 'left');
        $input = $cli->bold()->white()->input('Press enter to continue. Ctrl-C to quit.');
        $input->prompt();
        CliBase::billboard('tutorial-3', 265, '-top');
        $cli->clear();

        CliBase::billboard('tutorial-4', 165, 'top');
        $input = $cli->bold()->white()->input('Press enter to continue. Ctrl-C to quit.');
        $input->prompt();
        CliBase::billboard('tutorial-4', 265, '-top');
        $cli->clear();

        CliBase::billboard('tutorial-5', 365, 'top');
        $input = $cli->bold()->white()->input('Press enter to continue. Ctrl-C to quit.');
        $input->prompt();
        CliBase::billboard('tutorial-5', 265, '-top');
        $cli->clear();

        CliBase::billboard('tutorial-6', 365, 'left');
        $input = $cli->bold()->white()->input('Press enter to continue. Ctrl-C to quit.');
        $input->prompt();
        CliBase::billboard('tutorial-6', 365, '-left');
        $cli->clear();

        CliBase::billboard('tutorial-7', 165, 'top');
        $input = $cli->bold()->white()->input('Press enter to continue. Ctrl-C to quit.');
        $input->prompt();
        CliBase::billboard('tutorial-7', 265, '-left');
        $cli->clear();

        CliBase::billboard('tutorial-8', 365, 'left');
        $input = $cli->bold()->white()->input('Press enter to continue. Ctrl-C to quit.');
        $input->prompt();
        CliBase::billboard('tutorial-8', 265, '-top');
        $cli->clear();

        CliBase::billboard('tutorial-9', 165, 'top');
        $input = $cli->bold()->white()->input('Press enter to continue. Ctrl-C to quit.');
        $input->prompt();
        CliBase::billboard('tutorial-9', 265, '-top');
        $cli->clear();

        CliBase::billboard('tutorial-10', 365, 'right');
        $input = $cli->bold()->white()->input('Press enter to continue. Ctrl-C to quit.');
        $input->prompt();
        CliBase::billboard('tutorial-10', 265, '-top');
        $cli->clear();

        CliBase::billboard('tutorial-11', 165, 'top');
        $input = $cli->bold()->white()->input('Press enter to continue. Ctrl-C to quit.');
        $input->prompt();
        CliBase::billboard('tutorial-11', 265, '-top');

        CliBase::billboard('tutorial-12', 365, 'right');
        $input = $cli->bold()->white()->input('Press enter to continue. Ctrl-C to quit.');
        $input->prompt();
        CliBase::billboard('tutorial-12', 265, '-top');
        $cli->clear();

        CliBase::billboard('tutorial-13', 365, 'right');
        $input = $cli->bold()->white()->input('Press enter to continue. Ctrl-C to quit.');
        $input->prompt();
        CliBase::billboard('tutorial-13', 265, '-top');
        $cli->clear();

        CliBase::billboard('tutorial-14', 365, 'left');
        $input = $cli->bold()->white()->input('Press enter to continue. Ctrl-C to quit.');
        $input->prompt();
        CliBase::billboard('tutorial-14', 265, '-top');
        $cli->clear();

        CliBase::billboard('tutorial-15', 365, 'left');
        $input = $cli->bold()->white()->input('Press enter to continue. Ctrl-C to quit.');
        $input->prompt();
        CliBase::billboard('tutorial-15', 265, '-top');
        $cli->clear();
    }
}
