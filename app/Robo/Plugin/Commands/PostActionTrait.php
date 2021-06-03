<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Throwable;
use Twig\Environment;

trait PostActionTrait
{
    /**
     * @var Environment
     */
    protected Environment $twig;

    /**
     * Forge the PostAction code given the entity (table/view) name.
     *
     * @param string $entity
     * @return string|null
     */
    protected function forgePostAction(string $entity): ?string
    {
        // Format the PostAction class name
        $className = ucfirst($entity);

        // Render the PostAction code.
        try {
            $postActionCode = $this->twig->render('PostAction.php.twig', [
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

        // Save the postAction code file into the Controllers/ directory.
        if (file_put_contents($controllerPath . '/' . $className . 'PostAction.php', $postActionCode) === false) {
            return 'Unable to create: ' . $controllerPath . '/' . $className . 'PostAction.php';
        }

        return null;
    }
}
