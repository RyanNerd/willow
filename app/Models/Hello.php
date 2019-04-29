<?php
declare(strict_types=1);

namespace Willow\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id PK
 * @property string $World Placeholder
 *
 * @mixin Builder
 */
class Hello extends Model
{
    public $timestamps = false;
    public $table = 'Hello';
}