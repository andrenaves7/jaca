<?php
namespace Jaca\Model\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
class HasOne
{
    public function __construct(
        public string $related,
        public string $foreignKey = '',
        public string $localKey = 'id'
    ) {}
}
