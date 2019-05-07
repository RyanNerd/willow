<?php
declare(strict_types=1);

namespace Willow\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ModelBase
 *
 * @mixin Builder
 */
abstract class ModelBase extends Model
{
    public const FIELDS = [];

    /**
     * Return the name of the primary key column (usually but not always "id")
     *
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    public function getTableName(): string
    {
        return $this->table;
    }
}