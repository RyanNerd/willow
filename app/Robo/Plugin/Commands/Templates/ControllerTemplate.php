<?php
declare(strict_types=1);

namespace Willow\Controllers\TableAlias;

use Slim\Interfaces\RouteCollectorProxyInterface;
use Willow\Controllers\IController;

class TableAliasController implements IController
{
    public function register(RouteCollectorProxyInterface $group): void
    {
        $group->get('/%route%/{id}', TableAliasGetAction::class);
        $group->post('/%route%', TableAliasPostAction::class)
            ->add(TableAliasWriteValidator::class);
        $group->patch('/%route%', TableAliasPatchAction::class)
            ->add(TableAliasWriteValidator::class);
        $group->delete('/%route%/{id}', TableAliasDeleteAction::class);
    }
}