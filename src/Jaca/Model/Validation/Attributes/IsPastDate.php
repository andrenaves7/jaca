<?php
namespace Jaca\Model\Validation\Attributes;

use Jaca\Model\Validation\Interfaces\IValidator;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class IsPastDate implements IValidator
{
    private string $format;
    private ?string $message;

    public function __construct(string $format = 'Y-m-d', ?string $message = null)
    {
        $this->format = $format;
        $this->message = $message;
    }

    public function validate(string $property, mixed $value, ?object $model = null): ?string
    {
        $date = \DateTime::createFromFormat($this->format, $value);

        if (!$date) {
            return $this->message ?? "O campo '$property' não está em um formato válido ({$this->format}).";
        }

        $now = new \DateTime('now');

        if ($date >= $now) {
            return $this->message ?? "O campo '$property' deve ser uma data passada.";
        }

        return null;
    }
}
