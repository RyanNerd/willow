<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Throwable;
use Twig\Environment;

trait PatchActionTrait
{
    /**
     * @var Environment
     */
    protected Environment $twig;

    /**
     * Forge the PatchAction code given the entity (table/view) name.
     *
     * @param string $entity
     * @return string|null
     */
    protected function forgePatchAction(string $entity): ?string
    {
        // Format the PatchAction class name
        $className = ucfirst($entity);

        // Render the PatchAction code.
        try {
            $patchActionCode = $this->twig->render('PatchAction.php.twig', [
                    'class_name' => $className
                ]
            );
        } catch (Throwable $e) {
            return $e->getMessage();
        }

        $controllerPath = __DIR__ . '/../../../Controllers/' . $className;

        if (is_dir($controllerPath) === false) {
            if (mkdir($controllerPath) === false) {
                return 'Unable to create directory: ' . $controllerPath;
            }
        }

        // Save the patchAction code file into the Controllers/ directory.
        if (file_put_contents($controllerPath . '/' . $className . 'PatchAction.php', $patchActionCode) === false) {
            return 'Unable to create: ' . $controllerPath . '/' . $className . 'PatchAction.php';
        }

        return null;
    }
}
