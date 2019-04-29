<?php
declare(strict_types=1);

namespace Willow\TableAlias;

use Slim\Routing\RouteCollectorProxy;

class TableAliasController
{
    public function register(RouteCollectorProxy $group)
    {
        $group->get('/route/{id}', TableAliasGetAction::class);
        $group->post('/route', TableAliasPostAction::class);
        $group->patch('/route', TableAliasPatchAction::class);
        $group->delete('/route/{id}', TableAliasDeleteAction::class);
    }
}
