<?php
declare(strict_types=1);

namespace Willow\Controllers;

use Willow\Models\ModelBase;

abstract class ActionBase
{
    /**
     * Change this to `protected ModelBase $model;` if using PHP 8+ which allows union types
     * @var ModelBase
     */
    protected $model;
}
