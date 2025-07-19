<?php
namespace Jaca\Model\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class ReadOnlyAttr 
{
    public function __construct() {}
}
