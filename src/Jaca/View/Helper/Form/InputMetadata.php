<?php
namespace   Jaca\View\Helper\Form;

class InputMetadata
{
    public string $name;
    public mixed $value;
    public bool $required = false;
    public ?int $maxlength = null;
    public string $type = 'string';
    public bool $nullable = false;

    public function __construct(
        string $name,
        mixed $value,
        bool $required = false,
        ?int $maxlength = null,
        string $type = 'string',
        bool $nullable = false
    ) {
        $this->name = $name;
        $this->value = $value;
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
