<?php
namespace Jaca\Model\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY)]
class BelongsTo
{
    public function __construct(
        public string $related,
        public string $foreignKey = 'id',
        public string $ownerKey = ''
    ) {}
}