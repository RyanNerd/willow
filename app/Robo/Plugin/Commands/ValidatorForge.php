<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Twig\Environment as Twig;
use Throwable;
use Exception;

class ValidatorForge extends ForgeBase
{
    protected Twig $twig;

    public function __construct(Twig $twig) {
        $this->twig = $twig;
    }

    /**
     * Forge the RestoreValidator code given the table name.
     * @param string $table
     */
    final public function forgeRestoreValidator(string $table): void {
        try {
            // Format the RestoreValidator class name
            $className = ucfirst($table);
            // Render the RestoreValidator code.
            $restoreActionCode = $this->twig->render('RestoreValidator.php.twig', [
                    'class_name' => $className
                ]);
            $controllerPath = self::CONTROLLERS_PATH . $className;
            if (is_dir($controllerPath) === false) {
                if (mkdir($controllerPath) === false) {
                    $this->forgeError(new Exception('Unable to create directory: ' . $controllerPath));
                }
            }
            // Save the restoreAction code file into the Controllers/ directory.
            if (file_put_contents(
                $controllerPath . '/' . $className . 'RestoreValidator.php',
                $restoreActionCode
            ) === false
            ) {
                $this->forgeError(
                    new Exception('Unable to create: ' . $controllerPath . '/' . $className . 'RestoreValidator.php')
                );
            }
        } catch (Throwable $e) {
            $this->forgeError($e);
        }
    }

    /**
     * Forge the SearchValidator code given the entity table name.
     * @param string $table
     */
    final public function forgeSearchValidator(string $table): void {

        try {
            // Format the SearchValidator class name
            $className = ucfirst($table);
            // Render the SearchValidator code.
            $searchValidatorCode = $this->twig->render('SearchValidator.php.twig', [
                    'class_name' => $className
                ]);
            $controllerPath = self::CONTROLLERS_PATH . $className;
            if (is_dir($controllerPath) === false) {
                if (mkdir($controllerPath) === false) {
                    $this->forgeError(new Exception('Unable to create directory: ' . $controllerPath));
                }
            }
            // Save the WriteValidator code file into the Controllers/ directory.
            if (file_put_contents(
                $controllerPath . '/' . $className . 'SearchValidator.php',
                $searchValidatorCode
            ) === false
            ) {
                $this->forgeError(
                    new Exception('Unable to create: ' . $controllerPath . '/' . $className . 'SearchValidator.php')
                );
            }
        } catch (Throwable $e) {
            $this->forgeError($e);
        }
    }

    /**
     * Forge the WriteValidator code given the entity table name.
     * @param string $table
     */
    final public function forgeWriteValidator(string $table): void {
        try {
            // Format the WriteValidator class name
            $className = ucfirst($table);
            // Render the WriteValidator code.
            $writeValidatorCode = $this->twig->render('WriteValidator.php.twig', [
                    'class_name' => $className
                ]);
            $controllerPath = self::CONTROLLERS_PATH . $className;
            if (is_dir($controllerPath) === false) {
                if (mkdir($controllerPath) === false) {
                    $this->forgeError(new Exception('Unable to create directory: ' . $controllerPath));
                }
            }
            // Save the WriteValidator code file into the Controllers/ directory.
            if (file_put_contents(
                $controllerPath . '/' . $className . 'WriteValidator.php',
                $writeValidatorCode
            ) === false
            ) {
                $this->forgeError(
                    new Exception('Unable to create: ' . $controllerPath . '/' . $className . 'WriteValidator.php')
                );
            }
        } catch (Throwable $e) {
            $this->forgeError($e);
        }
    }
}
