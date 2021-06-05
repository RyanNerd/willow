<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Willow\Robo\Plugin\Commands\Traits\{
    ControllerTrait,
    GetActionTrait,
    ModelTrait,
    PatchActionTrait,
    PostActionTrait,
    RegisterControllersTrait,
    RestoreActionTrait,
    RestoreValidatorTrait,
    SearchActionTrait,
    SearchValidatorTrait,
    WriteValidatorTrait
};

class GenerateCommands extends RoboBase
{
    use ControllerTrait;
    use DeleteActionTrait;
    use GetActionTrait;
    use ModelTrait;
    use PatchActionTrait;
    use PostActionTrait;
    use RegisterControllersTrait;
    use RestoreActionTrait;
    use RestoreValidatorTrait;
    use SearchActionTrait;
    use SearchValidatorTrait;
    use WriteValidatorTrait;

    /**
     * Generates a controller, actions, validations for a given table and optionally route.
     *
     * @param string $tableName The table/view to generate a controller
     * @param string|null $route The route to use for the controller
     * @todo Warn user that this is a destructive command and existing PHP Controller and script files will be overwritten
     * @todo Use a .willow or a .viridian file to define TableNames and Routes???
     */
    public function generate(string $tableName, ?string $route = null): void
    {
        $error = $this->forgeModel($tableName);
        if ($error) {
            $this->error($error);
        }

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

        $error = $this->forgeSearchAction($tableName);
        if ($error) {
            $this->error($error);
        }

        $error = $this->forgeSearchValidator($tableName);
        if ($error) {
            $this->error($error);
        }

        $error = $this->forgeDeleteAction($tableName);
        if ($error) {
            $this->error($error);
        }

        $error = $this->forgeRestoreAction($tableName);
        if ($error) {
            $this->error($error);
        }

        $error = $this->forgeRestoreValidator($tableName);
        if ($error) {
            $this->error($error);
        }

        $error = $this->forgeRegisterControllers();
        if ($error) {
            $this->error($error);
        }
    }
}
