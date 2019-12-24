<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

class GenerateCommands extends RoboBase
{
    use ControllerTrait;
    use GetActionTrait;
    use ModelTrait;
    use PatchActionTrait;
    use PostActionTrait;
    use WriteValidatorTrait;

    /**
     * Generate a controller given the name of the table/view and optionally the route.
     *
     * @param string $tableName The table/view to generate a controller
     * @param string|null $route The route to use for the controller
     */
    public function generateController(string $tableName, ?string $route = null): void
    {
        $error = $this->forgeController($tableName, $route);
        if ($error) {
            $this->error($error);
        }

        $error = $this->forgeGetAction($tableName);
        if ($error) {
            $this->error($error);
        }

        $error = $this->forgePatchAction($tableName);
        if ($error) {
            $this->error($error);
        }

        $error = $this->forgePostAction($tableName);
        if ($error) {
            $this->error($error);
        }

        $error = $this->forgeWriteValidator($tableName);
        if ($error) {
            $this->error($error);
        }
    }

    /**
     * Generate a model given the name of the table/view.
     *
     * @param string $tableName
     */
    public function generateModel(string $tableName): void
    {
        $error = $this->forgeModel($tableName);

        if ($error) {
            $this->error($error);
        }
    }
}
