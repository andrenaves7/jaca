<?php
namespace Jaca\Model\Validation\Attributes;

use Jaca\Model\Validation\Interfaces\IValidator;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class MaxLength implements IValidator
{
    public ?string $message;
    public ?int $max;

    public function __construct(int $max, string $message = null) {
        $this->message = $message;
        $this->max = $max;
    }

    public function validate(string $property, mixed $value, ?object $model = null): ?string
    {
        if (!is_scalar($value)) {
            return null; // ignora arrays, objetos, etc.
        }

        $length = strlen((string) $value);

        if ($length > $this->max) {
            return $this->message ?? "O campo '$property' não pode conter mais do que '{$this->max}' caracteres.";
        }

        return null;
    }
}