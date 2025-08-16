<?php
namespace Jaca\Model\Attributes;

/**
 * Attribute to define validation rules for uploaded files.
 *
 * This attribute can be applied to a model property to specify
 * allowed file extensions, maximum file size, allowed MIME types,
 * and custom error messages.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FileValidation
{
    /**
     * Allowed file extensions (e.g., ['jpg', 'png']).
     *
     * @var array
     */
    public array $extensions;

    /**
     * Maximum allowed file size in bytes.
     *
     * @var int|null
     */
    public ?int $maxSize;

    /**
     * Allowed MIME types (e.g., ['image/jpeg', 'image/png']).
     *
     * @var array|null
     */
    public ?array $mimeTypes;

    /**
     * Custom error message(s). Can be a string (applied to all error types)
     * or an associative array with keys 'extensions', 'size', 'mime'.
     *
     * @var array|string|null
     */
    private array|string|null $message;

    /**
     * Constructor for FileValidation.
     *
     * @param string $extensions Comma-separated list of allowed extensions.
     * @param int|null $maxSize Maximum allowed file size in bytes.
     * @param string|null $mimeTypes Comma-separated list of allowed MIME types.
     * @param array|string|null $message Optional custom message(s) for validation errors.
     */
    public function __construct(
        string $extensions = '',
        ?int $maxSize = null,
        ?string $mimeTypes = null,
        array|string|null $message = null
    ) {
        $this->extensions = array_filter(array_map('trim', explode(',', $extensions)));
        $this->maxSize = $maxSize;
        $this->mimeTypes = $mimeTypes ? array_map('trim', explode(',', $mimeTypes)) : null;
        $this->message = $message;
    }

    /**
     * Returns the final error message based on the type of validation failure.
     *
     * @param string $errorType The type of error ('extensions', 'size', 'mime', etc.).
     * @param string $fileName The name of the uploaded file.
     * @param int|null $maxSize Optional maximum size (used in default message).
     * @param array|null $allowed Optional allowed extensions or MIME types (used in default message).
     * @return string The error message.
     */
    public function getMessage(string $errorType, string $fileName, ?int $maxSize = null, ?array $allowed = null): string
    {
        if (is_array($this->message) && isset($this->message[$errorType])) {
            return $this->message[$errorType];
        }

        if (is_string($this->message)) {
            return $this->message;
        }

        return match ($errorType) {
            'extensions' => "File '$fileName' has invalid extension. Allowed: ".implode(', ', $allowed),
            'size' => "File '$fileName' exceeds max size of $maxSize bytes.",
            'mime' => "File '$fileName' has invalid MIME type. Allowed: ".implode(', ', $allowed),
            default => "File '$fileName' is invalid."
        };
    }
}