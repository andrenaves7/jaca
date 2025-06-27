<?php
namespace Jaca\Transpiler;

class TokenType
{
    public const KEYWORD     = 'KEYWORD';
    public const IDENTIFIER  = 'IDENTIFIER';
    public const SYMBOL      = 'SYMBOL';
    public const STRING      = 'STRING';
    public const NUMBER      = 'NUMBER';
    public const OPERATOR    = 'OPERATOR';
    public const WHITESPACE  = 'WHITESPACE';
    public const COMMENT     = 'COMMENT';
}