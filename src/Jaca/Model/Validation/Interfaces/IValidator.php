<?php
namespace Jaca\Model\Validation\Interfaces;

interface IValidator
{
    public function validate(string $property, mixed $value, ?object $model = null): ?string;
}