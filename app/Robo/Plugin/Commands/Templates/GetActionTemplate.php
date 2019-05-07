<?php
declare(strict_types=1);

namespace Willow\Controllers\TableAlias;

use Willow\Controllers\GetActionBase;
use Willow\Models\TableAlias;

class TableAliasGetAction extends GetActionBase
{
    /**
     * @var TableAlias
     */
    protected $model;

    /**
     * Get the model via Dependency Injection and save it as a property.
     *
     * @param TableAlias $model
     */
    public function __construct(TableAlias $model)
    {
        $this->model = $model;
    }
}