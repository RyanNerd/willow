<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Exception;
use Throwable;
use Twig\Environment as Twig;
use Illuminate\Support\Str;

class ForgeModel extends ForgeBase
{
    protected Twig $twig;

    public function __construct(Twig $twig) {
        $this->twig = $twig;
    }

    /**
     * Forge the Model code given the table name and $columnList
     * @param string $table
     * @param array $columnList
     */
    final public function forgeModel(string $table, array $columnList): void {
        try {
            $className = ucfirst(Str::camel($table));

            // Render the Model code
            $modelCode = $this->twig->render(
                'Model.php.twig',
                [
                    'table' => $table,
                    'class_name' => $className,
                    'column_list' => $columnList
                ]
            );
            // Save the Model code file into the Models directory
            $modelFile = __DIR__ . '/../../../Models/' . ucfirst(Str::camel($table)) . '.php';
            if (file_put_contents($modelFile, $modelCode) === false) {
                $this->forgeError(new Exception('Unable to create: ' . $modelFile));
            }

            // Render the ModelRule code
            $modelRuleCode = $this->twig->render(
                'ModelRules.php.twig',
                [
                    'class_name' => $className
                ]
            );
            // Save the ModelRule code file into the Models directory
            $modelRuleFile = __DIR__ . '/../../../Models/' . ucfirst(Str::camel($table)) . 'ModelRules.php';
            if (file_put_contents($modelRuleFile, $modelRuleCode) === false) {
                $this->forgeError(new Exception('Unable to create: ' . $modelRuleFile));
            }
        } catch (Throwable $throwable) {
            $this->forgeError($throwable);
        }
    }
}
