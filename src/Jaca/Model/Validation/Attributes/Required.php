<?php
namespace Jaca\Model\Validation\Attributes;

use Jaca\Model\Validation\Interfaces\IValidator;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Required implements IValidator
{
    private ?string $message;

    public function __construct(string $message = null) {
        $this->message = $message;
    }

    public function validate(string $property, mixed $value, ?object $model = null): ?string
    {
        if ($value === null) {
            return $this->message ?? "O campo '{$property}' é obrigatório.";
        }

        if (is_string($value) && trim($value) === '') {
            return $this->message ?? "O campo '{$property}' é obrigatório.";
        }

        return null;
    }
}
