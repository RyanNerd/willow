<?php
declare(strict_types=1);

namespace Willow\Controllers\TableAlias;

use Willow\Controllers\QueryValidatorBase;
use Willow\Models\TableAlias;

class TableAliasQueryValidator extends QueryValidatorBase
{
    protected $modelFields = TableAlias::FIELDS;
}