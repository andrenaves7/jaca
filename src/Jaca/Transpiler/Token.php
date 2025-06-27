<?php
namespace Jaca\Transpiler;

class Token
{
    public string $type;
    public string $value;

    public function __construct(string $type, string $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    public function __toString(): string
    {
        return "{$this->type}: '{$this->value}'";
    }
}