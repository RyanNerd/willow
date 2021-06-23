<?php
declare(strict_types=1);

namespace Willow\Controllers;

use Willow\Models\ModelBase;

abstract class ActionBase
{
    /**
     * @var ModelBase
     */
    protected ModelBase $model;
}
