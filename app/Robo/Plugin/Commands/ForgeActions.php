<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Exception;
use Throwable;

class ForgeActions extends ForgeBase
{
    /**
     * Forge the DeleteAction code given the table name.
     * @param string $table
     */
    final public function forgeDeleteAction(string $table): void {
        try {
            $className = self::getClassNameFromTable($table);

            // Render the DeleteAction code.
            $deleteActionCode = $this->render('DeleteAction.php.twig', ['class_name' => $className]);

            $controllerPath = self::makeControllerDirectory($className);

            // Save the deleteAction code file into the Controllers/ directory.
            if (file_put_contents(
                $controllerPath . '/' . $className . 'DeleteAction.php',
                $deleteActionCode
            ) === false
            ) {
                CliBase::showThrowableAndDie(
                    new Exception('Unable to create: ' . $controllerPath . '/' . $className . 'DeleteAction.php')
                );
            }
        } catch (Throwable $e) {
            CliBase::showThrowableAndDie($e);
        }
    }

    /**
     * Forge the GetAction code given the table name.
     * @param string $table
     */
    final public function forgeGetAction(string $table): void {
        try {
            $className = self::getClassNameFromTable($table);

            // Render the GetAction code.
            $getActionCode = $this->render('GetAction.php.twig', ['class_name' => $className]);

            $controllerPath = self::makeControllerDirectory($className);

            // Save the getAction code file into the Controllers/ directory.
            if (file_put_contents($controllerPath . '/' . $className . 'GetAction.php', $getActionCode) === false) {
                CliBase::showThrowableAndDie(
                    new Exception('Unable to create: ' . $controllerPath .  '/' . $className . 'GetAction.php')
                );
            }
        } catch (Throwable $e) {
            CliBase::showThrowableAndDie($e);
        }
    }

    /**
     * Forge the PostAction code given the table name.
     * @param string $table
     */
    final public function forgePostAction(string $table): void {
        try {
            $className = self::getClassNameFromTable($table);

            // Render the PostAction code
            $postActionCode = $this->render('PostAction.php.twig', ['class_name' => $className]);

            $controllerPath = self::makeControllerDirectory($className);

            // Save the postAction code file into the Controllers/ directory.
            if (file_put_contents($controllerPath . '/' . $className . 'PostAction.php', $postActionCode) === false) {
                CliBase::showThrowableAndDie(
                    new Exception('Unable to create: ' . $controllerPath . '/' . $className . 'PostAction.php')
                );
            }
        } catch (Throwable $e) {
            CliBase::showThrowableAndDie($e);
        }
    }

    /**
     * Forge the SearchAction code given the table name.
     * @param string $table
     */
    final public function forgeSearchAction(string $table): void {
        try {
            $className = self::getClassNameFromTable($table);

            // Render the SearchAction code
            $searchActionCode = $this->render('SearchAction.php.twig', ['class_name' => $className]);

            $controllerPath = self::makeControllerDirectory($className);

            // Save the searchAction code file into the Controllers/ directory.
            if (file_put_contents($controllerPath . '/' . $className . 'SearchAction.php', $searchActionCode) === false
            ) {
                CliBase::showThrowableAndDie(
                    new Exception('Unable to create: ' . $controllerPath . '/' . $className . 'SearchAction.php')
                );
            }
        } catch (Throwable $e) {
            CliBase::showThrowableAndDie($e);
        }
    }

    /**
     * Forge the RestoreAction code given the entity table name.
     * @param string $table
     */
    final public function forgeRestoreAction(string $table): void {
        try {
            $className = self::getClassNameFromTable($table);

            // Render the RestoreAction code.
            $restoreActionCode = $this->render('RestoreAction.php.twig', ['class_name' => $className]);

            $controllerPath = self::makeControllerDirectory($className);

            // Save the restoreAction code file into the Controllers/ directory.
            if (file_put_contents(
                $controllerPath . '/' . $className . 'RestoreAction.php',
                $restoreActionCode
            ) === false
            ) {
                CliBase::showThrowableAndDie(
                    new Exception('Unable to create: ' . $controllerPath . '/' . $className . 'RestoreAction.php')
                );
            }
        } catch (Throwable $e) {
            CliBase::showThrowableAndDie($e);
        }
    }
}
