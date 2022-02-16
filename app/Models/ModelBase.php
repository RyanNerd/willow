<?php
declare(strict_types=1);

namespace Willow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;

/**
 * @mixin Collection
 * @mixin Builder
 */
abstract class ModelBase extends Model
{
    use SoftDeletes;

    /**
     * Return the name of the primary key column (usually but not always "id")
     * @return string
     */
    final public function getPrimaryKey(): string {
        return $this->primaryKey;
    }
}
