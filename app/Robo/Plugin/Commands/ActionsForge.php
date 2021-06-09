<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Twig\Environment as Twig;
use Throwable;
use Exception;

class ActionsForge
{
    protected Twig $twig;

    protected const CONTROLLERS_PATH = __DIR__ . '/../../../Controllers/';

    public function __construct(Twig $twig) {
        $this->twig = $twig;
    }

    /**
     * Forge the DeleteAction code given the table name.
     * @param string $table
     */
    public function forgeDeleteAction(string $table): void
    {
        try {
            // Format the DeleteAction class name
            $className = ucfirst($table);
            // Render the DeleteAction code.
            $deleteActionCode = $this->twig->render('DeleteAction.php.twig', [
                    'class_name' => $className
                ]
            );
            $controllerPath = self::CONTROLLERS_PATH . $className;
            if (is_dir($controllerPath) === false) {
                if (mkdir($controllerPath) === false) {
                    $this->forgeActionError(new Exception('Unable to create directory: ' . $controllerPath), $table);
                }
            }
            // Save the deleteAction code file into the Controllers/ directory.
            if (file_put_contents($controllerPath . '/' . $className . 'DeleteAction.php', $deleteActionCode) === false) {
                $this->forgeActionError(
                    new Exception('Unable to create: ' . $controllerPath . '/' . $className . 'DeleteAction.php'), $table
                );
            }
        } catch (Throwable $e) {
            $this->forgeActionError($e, $table);
        }
    }

    /**
     * Forge the GetAction code given the table name.
     * @param string $table
     */
    public function forgeGetAction(string $table) {
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
            $controllerPath = SELF::CONTROLLERS_PATH . $className;
            if (is_dir($controllerPath) === false) {
                if (mkdir($controllerPath) === false) {
                    $this->forgeActionError(new Exception('Unable to create directory: ' . $controllerPath), $table);
                }
            }
            // Save the getAction code file into the Controllers/ directory.
            if (file_put_contents($controllerPath . '/' . $className . 'GetAction.php', $getActionCode) === false) {
                $this->forgeActionError(
                    new Exception('Unable to create: ' . $controllerPath .  '/' . $className . 'GetAction.php'), $table
                );
            }
        } catch (Throwable $e) {
            $this->forgeActionError($e, $table);
        }
    }

    /**
     * Forge the PatchAction code given the table name.
     * @param string $table
     */
    public function forgePatchAction(string $table): void {
        // Render the PatchAction code.
        try {
            // Format the PatchAction class name
            $className = ucfirst($table);
            // Render the PatchAction code
            $patchActionCode = $this->twig->render('PatchAction.php.twig', [
                    'class_name' => $className
                ]
            );
            $controllerPath = self::CONTROLLERS_PATH . $className;

            if (is_dir($controllerPath) === false) {
                if (mkdir($controllerPath) === false) {
                    $this->forgeActionError(new Exception('Unable to create directory: ' . $controllerPath), $table);
                }
            }
            // Save the patchAction code file into the Controllers/ directory.
            if (file_put_contents($controllerPath . '/' . $className . 'PatchAction.php', $patchActionCode) === false) {
                $this->forgeActionError(
                    new Exception('Unable to create: ' . $controllerPath . '/' . $className . 'PatchAction.php'), $table
                );
            }
        } catch (Throwable $e) {
            $this->forgeActionError($e, $table);
        }
    }

    /**
     * Forge the PostAction code given the table name.
     * @param string $table
     */
    public function forgePostAction(string $table): void
    {
        try {
            // Format the PostAction class name
            $className = ucfirst($table);
            // Render the PostAction code
            $postActionCode = $this->twig->render('PostAction.php.twig', [
                    'class_name' => $className
                ]
            );
            $controllerPath = self::CONTROLLERS_PATH . $className;
            if (is_dir($controllerPath) === false) {
                if (mkdir($controllerPath) === false) {
                    $this->forgeActionError(new Exception('Unable to create directory: ' . $controllerPath), $table);
                }
            }
            // Save the postAction code file into the Controllers/ directory.
            if (file_put_contents($controllerPath . '/' . $className . 'PostAction.php', $postActionCode) === false) {
                $this->forgeActionError(
                    new Exception('Unable to create: ' . $controllerPath . '/' . $className . 'PostAction.php'), $table
                );
            }
        } catch (Throwable $e) {
            $this->forgeActionError($e, $table);
        }
    }

    /**
     * Forge the SearchAction code given the table name.
     * @param string $table
     */
    public function forgeSearchAction(string $table): void
    {
        try {
            // Format the SearchAction class name
            $className = ucfirst($table);
            // Render the SearchAction code
            $searchActionCode = $this->twig->render('SearchAction.php.twig', [
                    'class_name' => $className
                ]
            );
            $controllerPath = self::CONTROLLERS_PATH . $className;
            if (is_dir($controllerPath) === false) {
                if (mkdir($controllerPath) === false) {
                    $this->forgeActionError(new Exception('Unable to create directory: ' . $controllerPath), $table);
                }
            }
            // Save the searchAction code file into the Controllers/ directory.
            if (file_put_contents($controllerPath . '/' . $className . 'SearchAction.php', $searchActionCode) === false) {
                $this->forgeActionError(
                    new Exception('Unable to create: ' . $controllerPath . '/' . $className . 'SearchAction.php'), $table
                );
            }
        } catch (Throwable $e) {
            $this->forgeActionError($e, $table);
        }
    }



    /**
     * Forge the RestoreAction code given the entity table name.
     * @param string $table
     */
    public function forgeRestoreAction(string $table): void
    {
        try {
            // Format the RestoreAction class name
            $className = ucfirst($table);
            // Render the RestoreAction code.
            $restoreActionCode = $this->twig->render('RestoreAction.php.twig', [
                    'class_name' => $className
                ]
            );
            $controllerPath = self::CONTROLLERS_PATH . $className;
            if (is_dir($controllerPath) === false) {
                if (mkdir($controllerPath) === false) {
                    $this->forgeActionError(new Exception('Unable to create directory: ' . $controllerPath), $table);
                }
            }

            // Save the restoreAction code file into the Controllers/ directory.
            if (file_put_contents($controllerPath . '/' . $className . 'RestoreAction.php', $restoreActionCode) === false) {
                $this->forgeActionError(
                    new Exception('Unable to create: ' . $controllerPath . '/' . $className . 'RestoreAction.php'), $table
                );
            }
        } catch (Throwable $e) {
            $this->forgeActionError($e, $table);
        }
    }

    /**
     * Called when an exception is encountered.
     * @param Throwable $throwable
     * @param string $table
     */
    protected function forgeActionError(Throwable $throwable, string $table) {
        RoboBase::showThrowableAndDie($throwable, ["Action creation error for: $table"]);
    }
}
