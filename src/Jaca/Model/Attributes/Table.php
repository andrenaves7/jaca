<?php
namespace Jaca\Model\Attributes;

#[\Attribute(Attribute::TARGET_CLASS)]
class Table
{
    public function __construct(public string $name) {}
}
