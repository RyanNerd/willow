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
    protected function forgePatchAction(string $table): void {
        // Render the PatchAction code.
        try {
            // Format the PatchAction class name
            $className = ucfirst($table);
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
     * Called when an exception is encountered.
     * @param Throwable $throwable
     * @param string $table
     */
    protected function forgeActionError(Throwable $throwable, string $table) {
        RoboBase::showThrowableAndDie($throwable, ["Action creation error for: $table"]);
    }
}
