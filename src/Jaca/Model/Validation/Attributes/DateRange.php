<?php
namespace Jaca\Model\Validation\Attributes;

use Jaca\Model\Validation\Interfaces\IValidator;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class DateRange implements IValidator
{
    private ?string $start;
    private ?string $end;
    private string $format;
    private ?string $message;

    /**
     * @param string|null $start Data mínima no formato informado (ou null para sem limite inferior)
     * @param string|null $end Data máxima no formato informado (ou null para sem limite superior)
     * @param string $format Formato da data (padrão: Y-m-d H:i:s)
     * @param string|null $message Mensagem de erro customizada
     */
    public function __construct(?string $start = null, ?string $end = null, string $format = 'Y-m-d H:i:s', ?string $message = null)
    {
        $this->start = $start;
        $this->end = $end;
        $this->format = $format;
        $this->message = $message;
    }

    public function validate(string $property, mixed $value, ?object $model = null): ?string
    {
        $date = \DateTime::createFromFormat($this->format, $value);

        if (!$date || $date->format($this->format) !== $value) {
            return $this->message ?? "O campo '$property' não está no formato esperado ({$this->format}).";
        }

        if ($this->start !== null) {
            $startDate = \DateTime::createFromFormat($this->format, $this->start);
            if ($startDate && $date < $startDate) {
                return $this->message ?? "O campo '$property' deve ser maior ou igual a {$this->start}.";
            }
        }

        if ($this->end !== null) {
            $endDate = \DateTime::createFromFormat($this->format, $this->end);
            if ($endDate && $date > $endDate) {
                return $this->message ?? "O campo '$property' deve ser menor ou igual a {$this->end}.";
            }
        }

        return null;
    }
}
