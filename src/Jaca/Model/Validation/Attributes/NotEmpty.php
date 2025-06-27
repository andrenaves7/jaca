<?php
namespace Jaca\Model\Validation\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class NotEmpty
{
    public string $message;
    public function __construct(string $message = "Campo obrigatório") {
        $this->message = $message;
    }
}