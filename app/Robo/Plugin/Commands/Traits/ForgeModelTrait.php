<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands\Traits;

use Throwable;
use Twig\Environment;

trait ForgeModelTrait
{
    /**
     * @var Environment
     */
    protected Environment $twig;

    /**
     * Forge the Model code given the entity (table/view) name.
     *
     * @param string $entity
     * @return string|null
     */
    protected function forgeModel(string $entity): ?string
    {
        // Render the Model code.
        try {
            $modelCode = $this->twig->render('Model.php.twig',
                [
                    'entity' => $entity
                ]
            );
        } catch (Throwable $e) {
            return $e->getMessage();
        }

        // Save the Model code file into the Models directory.
        $modelFile = self::_getContainer()->get('models_path') . ucfirst(strtolower($entity)) . '.php';
        if (file_put_contents($modelFile, $modelCode) === false) {
            return 'Unable to create: ' . $modelFile;
        }

        return null;
    }
}
