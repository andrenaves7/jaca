<?php
namespace Jaca\Model\Attributes;

#[\Attribute(Attribute::TARGET_PROPERTY)]
class Column
{
    public function __construct(
        public ?string $name = null,       // nome real da coluna no banco
        public string $type = 'string',
        public ?int $length = null,
        public bool $nullable = false,
        public mixed $default = null
    ) {}
}
