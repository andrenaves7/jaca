<?php
namespace Jaca\Model\Attributes;

use Jaca\Model\Attributes\Interfaces\IRelation;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY | \Attribute::TARGET_CLASS)]
class HasManyThrough implements IRelation
{
    public function __construct(
        public string $related,
        public string $through,
        public ?string $firstKey = null,  // country_id
        public ?string $secondKey = null  // user_id
    ) {}
}