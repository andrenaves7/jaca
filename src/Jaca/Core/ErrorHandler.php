<?php
namespace Jaca\Core;

class ErrorHandler
{
    public static function register(): void
    {
        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function handleException(\Throwable $e): void
    {
        http_response_code(500);

        if ($_ENV['APP_ENV'] === 'dev') {
            echo "<pre>Uncaught Exception: " . $e->getMessage() . "\n";
            echo $e->getFile() . ':' . $e->getLine() . "\n";
            echo $e->getTraceAsString() . "</pre>";
        } else {
            echo "An unexpected error occurred. Please try again later.";
            // Log para arquivo pode ser adicionado aqui
        }
    }

    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            http_response_code(500);
            echo "Fatal error: {$error['message']} in {$error['file']} on line {$error['line']}";
            // Aqui também pode logar
        }
    }
}
