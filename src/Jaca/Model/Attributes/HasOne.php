<?php
namespace Jaca\Model\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY | \Attribute::TARGET_CLASS)]
class HasOne
{
    public function __construct(
        public string $related,
        public string $foreignKey = '',
        public string $localKey = 'id'
    ) {}
}
