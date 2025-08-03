<?php
namespace Jaca\Model\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Enum
{
    public function __construct(public array $values) 
    {}
}
