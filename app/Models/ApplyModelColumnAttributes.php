<?php
declare(strict_types=1);

namespace Willow\Models;

use Attribute;
use JetBrains\PhpStorm\ArrayShape;

#[Attribute(Attribute::IS_REPEATABLE|Attribute::TARGET_CLASS)]
class ApplyModelColumnAttribute
{
    /**
     * These are flags "stolen" from MySQL workbench adding some of our own
     * @link https://dev.mysql.com/doc/workbench/en/workbench-faq.html#faq-workbench-column-acronyms
     * @link https://stackoverflow.com/a/3663971/4323201
     */
    private const VALID_FLAGS = [
        'PK',       // Primary Key
        'NN',       // Not Null
        'UQ',       // Unique
        'BIN',      // Binary such as a blob
        'UN',       // Unsigned
        'ZF',       // Zero Fill
        'AI',       // Auto Incrementing
        'G',        // Generated
        'CE',       // This is our own custom flag indicating a Column Exemption for some validation checks
        'HIDDEN',   // This is our own custom flag indicating that the column is HIdden (informational)
        null
    ];

    /**
     * ApplyModelColumnAttribute constructor.
     * @param string $columnName
     * @param string $datatype
     * @param int|null $length
     * @param bool[]|null $flags
     * @param string|null $default
     */
    public function __construct(
        private string $columnName,
        private string $datatype,
        private ?int $length = null,
        private ?array $flags = null,
        private ?string $default = null
    ) {
        assert(in_array($this->flags, self::VALID_FLAGS), 'Invalid ModelColumnAttribute.flags');
    }

    #[ArrayShape([
        'ColumnName' => "string",
        'Type' => "string",
        'Length' => "int|null",
        'Flags' => "string[]|null",
        'Default' => "null|string"
    ])]
    final public function getModelColumnAttribute(): array {
        return [
            'ColumnName' => $this->columnName,
            'Type' => $this->datatype,
            'Length' => $this->length,
            'Flags' => $this->flags,
            'Default' => $this->default
        ];
    }
}
