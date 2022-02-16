<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Exception;
use Illuminate\Support\Str;
use Twig\Environment as Twig;

class ForgeBase
{
    protected const CONTROLLERS_PATH = __DIR__ . '/../../../Controllers/';

    public function __construct(private Twig $twig) {
    }

    protected static function getClassNameFromTable(string $table) {
        return ucfirst(Str::camel($table));
    }

    protected static function makeControllerDirectory(string $className): string {
        $controllerPath = self::CONTROLLERS_PATH . $className;
        if (is_dir($controllerPath) === false) {
            if (mkdir($controllerPath) === false) {
                CliBase::showThrowableAndDie(new Exception('Unable to create directory: ' . $controllerPath));
            }
        }
        return $controllerPath;
    }

    protected function render(string $twigName, array $parameters): string {
        return $this->twig->render($twigName, $parameters);
    }
}
