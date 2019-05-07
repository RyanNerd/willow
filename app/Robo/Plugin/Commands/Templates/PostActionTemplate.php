<?php
declare(strict_types=1);

namespace Willow\Controllers\TableAlias;

use Willow\Controllers\WriteActionBase;
use Willow\Models\TableAlias;

class TableAliasPostAction extends WriteActionBase
{
    /**
     * @var TableAlias
     */
    protected $model;

    public function __construct(TableAlias $model)
    {
        $this->model = $model;
    }
}
