<?php
namespace Jaca\Model\Validation\Attributes;

use Jaca\Model\Validation\Interfaces\IValidator;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class IsPastDateTime implements IValidator
{
    private string $format;
    private ?string $message;

    public function __construct(string $format = 'Y-m-d H:i:s', ?string $message = null)
    {
        $this->format = $format;
        $this->message = $message;
    }

    public function validate(string $property, mixed $value, ?object $model = null): ?string
    {
        $date = \DateTime::createFromFormat($this->format, $value);

        if (!$date || $date->format($this->format) !== $value) {
            return $this->message ?? "O campo '$property' não está no formato esperado ({$this->format}).";
        }

        $now = new \DateTime('now');

        if ($date >= $now) {
            return $this->message ?? "O campo '$property' deve ser uma data e hora passada.";
        }

        return null;
    }
}
