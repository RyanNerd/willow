<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Willow\Robo\Script;

class MakeCommands extends RoboBase
{

    /**
     * Builds the app (routes, controllers, actions, middleware, etc.
     */
    public function make()
    {
        $cli = $this->cli;


    }

//    public function make() {
//        $error = $this->forgeRegisterControllers();
//        if ($error) {
//            $this->error($error);
//        }
//    }
}
