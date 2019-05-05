<?php
declare(strict_types=1);

namespace Willow\Controllers\TableAlias;

use Willow\Controllers\ActionBase;
use Willow\Models\TableAlias;

class TableAliasPostAction extends ActionBase
{
    /**
     * @var TableAlias
     */
    protected $model;

    public function __construct(TableAlias $member)
    {
        $this->model = $member;
    }
}
