<?php
namespace Jaca\Model\Validation\Attributes;

use Jaca\Model\Validation\Interfaces\IValidator;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class TimeFormat implements IValidator
{
    private string $format;
    private ?string $message;

    public function __construct(string $format = 'H:i:s', string $message = null)
    {
        $this->format = $format;
        $this->message = $message;
    }

    public function validate(string $property, mixed $value, ?object $model = null): ?string
    {
        if (!is_string($value) || empty($value)) {
            return $this->message ?? "O campo '$property' deve ser um horário no formato {$this->format}.";
        }

        $time = \DateTime::createFromFormat($this->format, $value);

        if (!$time || $time->format($this->format) !== $value) {
            return $this->message ?? "O campo '$property' deve estar no formato {$this->format}.";
        }

        return null;
    }
}
