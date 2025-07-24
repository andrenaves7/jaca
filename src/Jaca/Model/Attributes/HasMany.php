<?php
namespace Jaca\Model\Attributes;

use Jaca\Model\Attributes\Interfaces\IRelation;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY | \Attribute::TARGET_CLASS)]
class HasMany implements IRelation
{
    public function __construct(
        public string $related,
        public ?string $foreignKey = '',
        public ?string $localKey = ''
    ) {}
}