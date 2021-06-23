<?php
declare(strict_types=1);

namespace Willow\Models;

use Attribute;

/**
 * This is an informational attribute indicating that a class, method, or property is intended to be overridden
 */
#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD|Attribute::TARGET_PROPERTY)]
class ApplyOverride
{
    public function __construct(?string $comment = null) {
    }
}
