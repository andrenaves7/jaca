<?php
namespace Jaca\Model\Validation\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class MaxLength
{
    public int $max;
    public string $message;

    public function __construct(int $max, string $message = "") {
        $this->max = $max;
        $this->message = $message ?: "Máximo de caracteres permitido: $max";
    }
}