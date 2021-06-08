<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands\Traits;

use Exception;
use Throwable;
use Twig\Environment as Twig;
use Willow\Robo\Plugin\Commands\RoboBase;

trait ForgeModelTrait
{
    protected Twig $twig;

    /**
     * Forge the Model code given the table name.
     * @param string $table
     */
    protected function forgeModel(string $table): void
    {
        try {
            // Render the Model code.
            $modelCode = $this->twig->render(
                'Model.php.twig',
                [
                    'table' => $table,
                    'class_name' => ucfirst($table)
                ]
            );
            // Save the Model code file into the Models directory.
            $modelFile = self::_getContainer()->get('models_path') . ucfirst($table) . '.php';
            if (file_put_contents($modelFile, $modelCode) === false) {
                $this->forgeModelError(new Exception('Unable to create: ' . $modelFile), $table);
            }
        } catch (Throwable $throwable) {
            $this->forgeModelError($throwable, $table);
        }
    }

    /**
     * Called when an exception is encountered.
     * @param Throwable $throwable
     * @param string $table
     */
    protected function forgeModelError(Throwable $throwable, string $table) {
        RoboBase::showThrowableAndDie($throwable, ["Model creation error for: $table"]);
    }
}
