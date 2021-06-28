<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Exception;
use Throwable;

class ForgeValidator extends ForgeBase
{
    /**
     * Forge the SearchValidator code given the entity table name.
     * @param string $table
     */
    final public function forgeSearchValidator(string $table): void {
        try {
            $className = self::getClassNameFromTable($table);

            // Render the SearchValidator code.
            $searchValidatorCode = $this->render('SearchValidator.php.twig', ['class_name' => $className]);
            $controllerPath = $this->makeControllerDirectory($className);
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
            $className = self::getClassNameFromTable($table);

            // Render the WriteValidator code.
            $writeValidatorCode = $this->render('WriteValidator.php.twig', ['class_name' => $className]);

            $controllerPath = $this->makeControllerDirectory($className);

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

    /**
     * Forge the ModelValidator code given the entity table name.
     * @param string $table
     */
    final public function forgeModelValidator(string $table): void {
        try {
            $className = self::getClassNameFromTable($table);

            // Render the ModelValidator code.
            $modelValidatorCode = $this->render('ModelValidator.php.twig', ['class_name' => $className]);

            $controllerPath = $this->makeControllerDirectory($className);

            // Save the ModelValidator code file into the Controllers/ directory.
            if (file_put_contents(
                $controllerPath . '/' . $className . 'ModelValidator.php',
                $modelValidatorCode
            ) === false
            ) {
                CliBase::showThrowableAndDie(
                    new Exception('Unable to create: ' . $controllerPath . '/' . $className . 'ModelValidator.php')
                );
            }
        } catch (Throwable $e) {
            CliBase::showThrowableAndDie($e);
        }
    }
}
