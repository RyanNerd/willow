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
     * Used by SearchActionBase.
     * Override this to true if you want to allow the model searchAction to not include a where filter (all records).
     * @override
     * @var bool
     */
    public bool $allowAll = false;

    /**
     * Return the name of the primary key column (usually but not always "id")
     * @return string
     */
    final public function getPrimaryKey(): string {
        return $this->primaryKey;
    }

    /**
     * Return the name of the table for the model
     * @return string
     */
    final public function getTableName(): string {
        return $this->table;
    }
}
