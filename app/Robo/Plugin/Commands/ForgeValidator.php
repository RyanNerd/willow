<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Twig\Environment as Twig;
use Throwable;
use Exception;

class ForgeValidator
{
    private const CONTROLLERS_PATH = __DIR__ . '/../../../Controllers/';

    public function __construct(private Twig $twig) {
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
                    CliBase::showThrowableAndDie(new Exception('Unable to create directory: ' . $controllerPath));
                }
            }
            // Save the restoreAction code file into the Controllers/ directory.
            if (file_put_contents(
                $controllerPath . '/' . $className . 'RestoreValidator.php',
                $restoreActionCode
            ) === false
            ) {
                CliBase::showThrowableAndDie(
                    new Exception('Unable to create: ' . $controllerPath . '/' . $className . 'RestoreValidator.php')
                );
            }
        } catch (Throwable $e) {
            CliBase::showThrowableAndDie($e);
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
                    CliBase::showThrowableAndDie(new Exception('Unable to create directory: ' . $controllerPath));
                }
            }
            // Save the WriteValidator code file into the Controllers/ directory.
            if (file_put_contents(
                $controllerPath . '/' . $className . 'SearchValidator.php',
                $searchValidatorCode
            ) === false
            ) {
                CliBase::showThrowableAndDie(
                    new Exception('Unable to create: ' . $controllerPath . '/' . $className . 'SearchValidator.php')
                );
            }
        } catch (Throwable $e) {
            CliBase::showThrowableAndDie($e);
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
                    CliBase::showThrowableAndDie(new Exception('Unable to create directory: ' . $controllerPath));
                }
            }
            // Save the WriteValidator code file into the Controllers/ directory.
            if (file_put_contents(
                $controllerPath . '/' . $className . 'WriteValidator.php',
                $writeValidatorCode
            ) === false
            ) {
                CliBase::showThrowableAndDie(
                    new Exception('Unable to create: ' . $controllerPath . '/' . $className . 'WriteValidator.php')
                );
            }
        } catch (Throwable $e) {
            CliBase::showThrowableAndDie($e);
        }
    }
}
