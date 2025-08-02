<?php
namespace Jaca\Core;

/**
 * Class ErrorHandler
 *
 * Centralized error and exception handling for the application.
 * Registers handlers for uncaught exceptions, PHP errors, and fatal shutdown errors.
 *
 * @package Jaca\Core
 */
class ErrorHandler
{
    /**
     * Registers the error, exception, and shutdown handlers.
     *
     * @return void
     */
    public static function register(): void
    {
        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * Handles uncaught exceptions.
     *
     * Sends HTTP 500 response code and displays detailed error information in development environment,
     * or a generic error message in other environments.
     *
     * @param \Throwable $e The uncaught exception.
     * @return void
     */
    public static function handleException(\Throwable $e): void
    {
        http_response_code(500);

        if ($_ENV['APP_ENV'] === 'dev') {
            echo "<pre>Uncaught Exception: " . $e->getMessage() . "\n";
            echo $e->getFile() . ':' . $e->getLine() . "\n";
            echo $e->getTraceAsString() . "</pre>";
        } else {
            echo "An unexpected error occurred. Please try again later.";
            // Logging to file can be added here
        }
    }

    /**
     * Converts PHP errors to ErrorException, allowing them to be handled as exceptions.
     *
     * @param int $errno The level of the error raised.
     * @param string $errstr The error message.
     * @param string $errfile The filename where the error was raised.
     * @param int $errline The line number where the error was raised.
     * @return bool Always throws an ErrorException.
     * @throws \ErrorException
     */
    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    /**
     * Handles fatal errors on script shutdown.
     *
     * Checks for fatal errors and outputs an HTTP 500 response with error details.
     * Logging can also be performed here.
     *
     * @return void
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            http_response_code(500);
            echo "Fatal error: {$error['message']} in {$error['file']} on line {$error['line']}";
            // Logging can also be added here
        }
    }
}
