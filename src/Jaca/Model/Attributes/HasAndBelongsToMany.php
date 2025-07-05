<?php
namespace Jaca\Model\Attributes;

use Jaca\Model\Attributes\Interfaces\IRelation;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY | \Attribute::TARGET_CLASS)]
class HasAndBelongsToMany implements IRelation
{
    public function __construct(
        public string $related,
        public ?string $pivot = '',
        public ?string $foreignPivotKey = '',
        public ?string $relatedPivotKey = '',
    ) {}
}