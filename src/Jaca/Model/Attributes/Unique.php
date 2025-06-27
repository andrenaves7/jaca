<?php
namespace Jaca\Model\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Unique
{
    public function __construct() {}
}
