<?php
declare(strict_types=1);

namespace Willow\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin Builder
 */
class ModelTemplate extends ModelBase
{
    protected $table = 'TableName';
}