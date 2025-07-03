<?php
namespace Jaca\Model\Attributes;

#[\Attribute(Attribute::TARGET_PROPERTY)]
class HasMany
{
    public function __construct(
        public string $related,
        public string $foreignKey,
        public ?string $localKey = 'id'
    ) {}
}