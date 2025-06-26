<?php
namespace Jaca;

class CLI
{
    public function compile(): void
    {
        echo "Compiling .jaca files...\n";
        // Aqui entra a lógica para transpilar .jaca para PHP
    }

    public function create(string $type, ?string $name = null): void
    {
        if ($type === 'controller' && $name) {
            echo "Creating controller: $name\n";
            // Crie o controller em app/controllers/$name.jaca
            // Exemplo: criar o arquivo com código base
        } else {
            echo "Unsupported create command or missing name.\n";
        }
    }
}
