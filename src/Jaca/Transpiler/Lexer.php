<?php
namespace Jaca\Transpiler;

class Lexer
{
    private string $input;
    private int $position = 0;
    private int $length;

    public function __construct(string $input)
    {
        $this->input = $input;
        $this->length = strlen($input);
    }

    public function tokenize(): array
    {
        $tokens = [];

        while ($this->position < $this->length) {
            $char = $this->peek();

            if (ctype_space($char)) {
                $this->advance();
                continue;
            }

            if (ctype_alpha($char) || $char === '_') {
                $tokens[] = $this->readIdentifierOrKeyword();
                continue;
            }

            if ($char === '"' || $char === "'") {
                $tokens[] = $this->readString();
                continue;
            }

            if (is_numeric($char)) {
                $tokens[] = $this->readNumber();
                continue;
            }

            $tokens[] = $this->readSymbol();
        }

        return $tokens;
    }

    // Helpers...

    private function peek(): string
    {
        return $this->input[$this->position];
    }

    private function advance(): string
    {
        return $this->input[$this->position++];
    }

    private function readIdentifierOrKeyword(): Token
    {
        $start = $this->position;

        while ($this->position < $this->length && (ctype_alnum($this->peek()) || $this->peek() === '_' || $this->peek() === '.')) {
            $this->advance();
        }

        $value = substr($this->input, $start, $this->position - $start);
        $keywords = ['package', 'import', 'class', 'public', 'private', 'protected', 'extends', 'implements', 'void', 'string'];

        $type = in_array($value, $keywords) ? TokenType::KEYWORD : TokenType::IDENTIFIER;

        return new Token($type, $value);
    }

    private function readString(): Token
    {
        $quote = $this->advance(); // consume opening quote
        $value = '';

        while ($this->peek() !== $quote) {
            $value .= $this->advance();
        }

        $this->advance(); // consume closing quote

        return new Token(TokenType::STRING, $value);
    }

    private function readNumber(): Token
    {
        $start = $this->position;

        while ($this->position < $this->length && is_numeric($this->peek())) {
            $this->advance();
        }

        $value = substr($this->input, $start, $this->position - $start);
        return new Token(TokenType::NUMBER, $value);
    }

    private function readSymbol(): Token
    {
        $char = $this->advance();
        return new Token(TokenType::SYMBOL, $char);
    }
}
