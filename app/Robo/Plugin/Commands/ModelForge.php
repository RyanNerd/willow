<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Exception;
use Throwable;
use Twig\Environment as Twig;

class ModelForge extends ForgeBase
{
    protected Twig $twig;

    public function __construct(Twig $twig) {
        $this->twig = $twig;
    }

    /**
     * Forge the Model code given the table name.
     * @param string $table
     */
    final public function forgeModel(string $table): void {
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
            $modelFile = __DIR__ . '/../../../Models/' . ucfirst($table) . '.php';
            if (file_put_contents($modelFile, $modelCode) === false) {
                $this->forgeError(new Exception('Unable to create: ' . $modelFile));
            }
        } catch (Throwable $throwable) {
            $this->forgeError($throwable);
        }
    }
}
