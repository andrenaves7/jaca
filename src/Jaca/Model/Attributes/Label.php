<?php
namespace Jaca\Model\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Label
{
    public function __construct(public ?string $label = null) {}
}