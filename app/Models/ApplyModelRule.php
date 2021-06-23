<?php
declare(strict_types=1);

namespace Willow\Models;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::IS_REPEATABLE)]
class ApplyModelRule
{
    /**
     * ApplyModelRule constructor.
     * @param string $rule This should be the name of the rule class. e.g. ModelDefaultRules::class
     */
    public function __construct(private string $rule) {
    }

    final public function getModelRule(): string {
        return $this->rule;
    }
}
