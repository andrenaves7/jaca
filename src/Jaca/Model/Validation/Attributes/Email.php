<?php
namespace Jaca\Model\Validation\Attributes;

use Jaca\Model\Validation\Interfaces\IValidator;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Email implements IValidator
{
    private ?string $message;

    public function __construct(string $message = null)
    {
        $this->message = $message;
    }

    public function validate(string $property, mixed $value, ?object $model = null): ?string
    {
        if (!is_string($value)) {
            return $this->message ?? "O campo '$property' deve conter um e-mail válido.";
        }

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return $this->message ?? "O campo '$property' deve conter um e-mail válido.";
        }

        return null;
    }
}
