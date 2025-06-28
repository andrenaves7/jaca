<?php
namespace Jaca\Model\Validation\Attributes;

use Jaca\Model\Validation\Interfaces\IValidator;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class MinLength implements IValidator
{
    private ?string $message;
    private ?int $min;

    public function __construct(int $min, string $message = null) {
        $this->message = $message;
        $this->min = $min;
    }

    public function validate(string $property, mixed $value, ?object $model = null): ?string
    {
        if (!is_scalar($value)) {
            return null; // ignora arrays, objetos, etc.
        }

        $length = strlen((string) $value);

        if ($length < $this->min) {
            return $this->message ?? "O campo '$property' deve conter pelo menos {$this->min} caracteres.";
        }

        return null;
    }
}