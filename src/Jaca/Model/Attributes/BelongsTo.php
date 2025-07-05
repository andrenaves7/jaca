<?php
namespace Jaca\Model\Attributes;

use Jaca\Model\Attributes\Interfaces\IRelation;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY)]
class BelongsTo implements IRelation
{
    public function __construct(
        public string $related,
        public ?string $foreignKey = '',
        public ?string $ownerKey = ''
    ) {}
}