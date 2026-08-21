<?php
namespace App\Utils;

use Throwable;

class ErrorHandler {
    /**
     * Register the handlers. Call this from bootstrap.
     */
    public static function register(): void {
        // Report all errors
        error_reporting(E_ALL);
        ini_set('display_errors', '0'); // Do not expose errors to users
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * Convert PHP errors to ErrorException and pass to exception handler.
     */
    public static function handleError(int $severity, string $message, string $file, int $line): bool {
        // Respect error_reporting settings
        if (!(error_reporting() & $severity)) {
            return false; // Let PHP handle it
        }
        // Throw as ErrorException to be caught by the exception handler
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    /**
     * Global exception handler.
     */
    public static function handleException(Throwable $exception): void {
        // Log the error details using Logger
        Logger::error($exception->getMessage() . " in " . $exception->getFile() . ":" . $exception->getLine());
        // Output generic JSON response for API calls
        if (php_sapi_name() !== 'cli') {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Ha ocurrido un error interno. Por favor, contacte al administrador.'
            ]);
        }
    }

    /**
     * Shutdown handler to catch fatal errors.
     */
    public static function handleShutdown(): void {
        $error = error_get_last();
        if ($error && ($error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR))) {
            $exception = new \ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            );
            self::handleException($exception);
        }
    }
}
?>
