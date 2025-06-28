<?php
namespace Jaca\Model\Validation\Attributes;

use Jaca\Model\Validation\Interfaces\IValidator;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class NumberRange implements IValidator
{
    private float|int $min;
    private float|int $max;
    private ?string $message;

    public function __construct(float|int $min, float|int $max, string $message = null)
    {
        $this->min = $min;
        $this->max = $max;
        $this->message = $message;
    }

    public function validate(string $property, mixed $value, ?object $model = null): ?string
    {
        if (!is_numeric($value)) {
            return "O campo '$property' deve ser numérico.";
        }

        if ($value < $this->min || $value > $this->max) {
            return $this->message ?? "O campo '$property' deve estar entre {$this->min} e {$this->max}.";
        }

        return null;
    }
}
