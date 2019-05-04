<?php
declare(strict_types=1);

namespace Willow\Robo;

class Script
{
    //ln ./vendor/bin/robo willow
    public static function postPackageInstall($event)
    {
        file_put_contents(__DIR__ . 'install.test', 'worked!');
    }
}
