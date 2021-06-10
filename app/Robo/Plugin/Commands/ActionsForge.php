<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Twig\Environment as Twig;
use Throwable;
use Exception;

class ActionsForge extends ForgeBase
{
    protected Twig $twig;

    public function __construct(Twig $twig) {
        $this->twig = $twig;
    }

    /**
     * Forge the DeleteAction code given the table name.
     * @param string $table
     */
    final public function forgeDeleteAction(string $table): void {
        try {
            // Format the DeleteAction class name
            $className = ucfirst($table);
            // Render the DeleteAction code.
            $deleteActionCode = $this->twig->render('DeleteAction.php.twig', [
                    'class_name' => $className
                ]);
            $controllerPath = self::CONTROLLERS_PATH . $className;
            if (is_dir($controllerPath) === false) {
                if (mkdir($controllerPath) === false) {
                    $this->forgeError(new Exception('Unable to create directory: ' . $controllerPath));
                }
            }
            // Save the deleteAction code file into the Controllers/ directory.
            if (file_put_contents(
                $controllerPath . '/' . $className . 'DeleteAction.php',
                $deleteActionCode
            ) === false
            ) {
                $this->forgeError(
                    new Exception('Unable to create: ' . $controllerPath . '/' . $className . 'DeleteAction.php')
                );
            }
        } catch (Throwable $e) {
            $this->forgeError($e);
        }
    }

    /**
     * Forge the GetAction code given the table name.
     * @param string $table
     */
    final public function forgeGetAction(string $table): void {
        try {
            // Format the GetAction class name
            $className = ucfirst($table);
            // Render the GetAction code.
            $getActionCode = $this->twig->render(
                'GetAction.php.twig',
                [
                    'class_name' => $className
                ]
            );
            $controllerPath = self::CONTROLLERS_PATH . $className;
            if (is_dir($controllerPath) === false) {
                if (mkdir($controllerPath) === false) {
                    $this->forgeError(new Exception('Unable to create directory: ' . $controllerPath));
                }
            }
            // Save the getAction code file into the Controllers/ directory.
            if (file_put_contents($controllerPath . '/' . $className . 'GetAction.php', $getActionCode) === false) {
                $this->forgeError(
                    new Exception('Unable to create: ' . $controllerPath .  '/' . $className . 'GetAction.php')
                );
            }
        } catch (Throwable $e) {
            $this->forgeError($e);
        }
    }

    /**
     * Forge the PatchAction code given the table name.
     * @param string $table
     */
    final public function forgePatchAction(string $table): void {
        // Render the PatchAction code.
        try {
            // Format the PatchAction class name
            $className = ucfirst($table);
            // Render the PatchAction code
            $patchActionCode = $this->twig->render('PatchAction.php.twig', [
                    'class_name' => $className
                ]);
            $controllerPath = self::CONTROLLERS_PATH . $className;

            if (is_dir($controllerPath) === false) {
                if (mkdir($controllerPath) === false) {
                    $this->forgeError(new Exception('Unable to create directory: ' . $controllerPath));
                }
            }
            // Save the patchAction code file into the Controllers/ directory.
            if (file_put_contents($controllerPath . '/' . $className . 'PatchAction.php', $patchActionCode) === false) {
                $this->forgeError(
                    new Exception('Unable to create: ' . $controllerPath . '/' . $className . 'PatchAction.php')
                );
            }
        } catch (Throwable $e) {
            $this->forgeError($e);
        }
    }

    /**
     * Forge the PostAction code given the table name.
     * @param string $table
     */
    final public function forgePostAction(string $table): void {
        try {
            // Format the PostAction class name
            $className = ucfirst($table);
            // Render the PostAction code
            $postActionCode = $this->twig->render(
                'PostAction.php.twig',
                [
                    'class_name' => $className
                ]
            );
            $controllerPath = self::CONTROLLERS_PATH . $className;
            if (is_dir($controllerPath) === false) {
                if (mkdir($controllerPath) === false) {
                    $this->forgeError(new Exception('Unable to create directory: ' . $controllerPath));
                }
            }
            // Save the postAction code file into the Controllers/ directory.
            if (file_put_contents($controllerPath . '/' . $className . 'PostAction.php', $postActionCode) === false) {
                $this->forgeError(
                    new Exception('Unable to create: ' . $controllerPath . '/' . $className . 'PostAction.php')
                );
            }
        } catch (Throwable $e) {
            $this->forgeError($e);
        }
    }

    /**
     * Forge the SearchAction code given the table name.
     * @param string $table
     */
    final public function forgeSearchAction(string $table): void {
        try {
            // Format the SearchAction class name
            $className = ucfirst($table);
            // Render the SearchAction code
            $searchActionCode = $this->twig->render('SearchAction.php.twig', [
                    'class_name' => $className
                ]);
            $controllerPath = self::CONTROLLERS_PATH . $className;
            if (is_dir($controllerPath) === false) {
                if (mkdir($controllerPath) === false) {
                    $this->forgeError(new Exception('Unable to create directory: ' . $controllerPath));
                }
            }
            // Save the searchAction code file into the Controllers/ directory.
            if (file_put_contents($controllerPath . '/' . $className . 'SearchAction.php', $searchActionCode) === false
            ) {
                $this->forgeError(
                    new Exception('Unable to create: ' . $controllerPath . '/' . $className . 'SearchAction.php')
                );
            }
        } catch (Throwable $e) {
            $this->forgeError($e);
        }
    }

    /**
     * Forge the RestoreAction code given the entity table name.
     * @param string $table
     */
    final public function forgeRestoreAction(string $table): void {
        try {
            // Format the RestoreAction class name
            $className = ucfirst($table);
            // Render the RestoreAction code.
            $restoreActionCode = $this->twig->render('RestoreAction.php.twig', [
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
                $controllerPath . '/' . $className . 'RestoreAction.php',
                $restoreActionCode
            ) === false
            ) {
                $this->forgeError(
                    new Exception('Unable to create: ' . $controllerPath . '/' . $className . 'RestoreAction.php')
                );
            }
        } catch (Throwable $e) {
            $this->forgeError($e);
        }
    }
}
