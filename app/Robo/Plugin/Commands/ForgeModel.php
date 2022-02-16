<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Exception;
use Throwable;

class ForgeModel extends ForgeBase
{
    private const MODELS_PATH = __DIR__ . '/../../../Models/';
    /**
     * Forge the Model code given the table name and $columnList
     * @param string $table
     * @param array $columnList
     */
    final public function forgeModel(string $table, array $columnList): void {
        try {
            $className = self::getClassNameFromTable($table);

            // Render the Model code
            $modelCode = $this->render(
                'Model.php.twig',
                [
                    'table' => $table,
                    'class_name' => $className,
                    'column_list' => $columnList
                ]
            );

            // Save the Model code file into the Models directory
            $modelFile = self::MODELS_PATH . $className . '.php';
            if (file_put_contents($modelFile, $modelCode) === false) {
                CliBase::showThrowableAndDie(new Exception('Unable to create: ' . $modelFile));
            }

            // Render the ModelRule code
            $modelRuleCode = $this->render('ModelRules.php.twig', ['class_name' => $className]);

            // Save the ModelRule code file into the Models directory
            $modelRuleFile = self::MODELS_PATH . $className . 'ModelRules.php';
            if (file_put_contents($modelRuleFile, $modelRuleCode) === false) {
                CliBase::showThrowableAndDie(new Exception('Unable to create: ' . $modelRuleFile));
            }
        } catch (Throwable $throwable) {
            CliBase::showThrowableAndDie($throwable);
        }
    }
}
