<?php
declare(strict_types=1);

namespace Willow\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ModelBase
 */
abstract class ModelBase extends Model
{
    public const FIELDS = [];

    use SoftDeletes;

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
