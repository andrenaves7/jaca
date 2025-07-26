<?php
namespace   Jaca\View\Helper\Form;

use Jaca\Model\Attributes\Types\Interfaces\IType;
use Jaca\Model\Attributes\Types\Text;

class InputMetadata
{
    public string $name;
    public mixed $value;
    public string $label;
    public bool $required = false;
    public ?int $maxlength = null;
    public ?string $type = null;
    public bool $nullable = false;

    public function __construct(
        string $name,
        mixed $value,
        string $label,
        bool $required = false,
        ?int $maxlength = null,
        ?string $type = null,
        bool $nullable = false
    ) {
        $this->name = $name;
        $this->value = $value;
        $this->label = $label;
        $this->required = $required;
        $this->maxlength = $maxlength;
        $this->type = $type;
        $this->nullable = $nullable;
    }

    public function getHtmlAttributes(): string
    {
        $attrs = [];

        if ($this->required) $attrs[] = 'required';
        if ($this->maxlength) $attrs[] = "maxlength=\"{$this->maxlength}\"";

        return implode(' ', $attrs);
    }
}
