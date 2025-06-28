<?php
namespace Jaca\Model\Validation\Attributes;

use Jaca\Model\Validation\Interfaces\IValidator;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class DateFormat implements IValidator
{
    private string $format;
    private ?string $message;

    public function __construct(string $format = 'Y-m-d', string $message = null)
    {
        $this->format = $format;
        $this->message = $message;
    }

    public function validate(string $property, mixed $value, ?object $model = null): ?string
    {
        if (!is_string($value) || empty($value)) {
            return $this->message ?? "O campo '$property' deve ser uma data no formato {$this->format}.";
        }

        $date = \DateTime::createFromFormat($this->format, $value);

        $valid = $date && $date->format($this->format) === $value;

        if (!$valid) {
            return $this->message ?? "O campo '$property' deve estar no formato {$this->format}.";
        }

        return null;
    }
}
