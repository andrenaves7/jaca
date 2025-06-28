<?php
namespace Jaca\Model\Validation\Attributes;

use Jaca\Model\Validation\Interfaces\IValidator;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Regex implements IValidator
{
    private string $pattern;
    private ?string $message;

    public function __construct(string $pattern, string $message = null)
    {
        $this->pattern = $pattern;
        $this->message = $message;
    }

    public function validate(string $property, mixed $value, ?object $model = null): ?string
    {
        if (!is_scalar($value)) {
            return null; // ignora arrays, objetos, etc.
        }

        if (!preg_match($this->pattern, (string) $value)) {
            return $this->message ?? "O campo '$property' não corresponde ao formato esperado.";
        }

        return null;
    }
}
