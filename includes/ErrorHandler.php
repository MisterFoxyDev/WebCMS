<?php

class ErrorHandler
{
    private static $instance = null;
    private $logFile;
    private $displayErrors = false;

    private function __construct()
    {
        $this->logFile = __DIR__ . '/../logs/error.log';
        if (!file_exists(dirname($this->logFile))) {
            mkdir(dirname($this->logFile), 0777, true);
        }

        // Définir le gestionnaire d'erreurs personnalisé
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function setDisplayErrors($display)
    {
        $this->displayErrors = $display;
    }

    public function handleError($errno, $errstr, $errfile, $errline)
    {
        $error = [
            'type' => $this->getErrorType($errno),
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'time' => date('Y-m-d H:i:s')
        ];

        $this->logError($error);

        if ($this->displayErrors) {
            $this->displayError($error);
        }

        return true;
    }

    public function handleException($exception)
    {
        $error = [
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'time' => date('Y-m-d H:i:s')
        ];

        $this->logError($error);

        if ($this->displayErrors) {
            $this->displayError($error);
        }

        // Envoyer une réponse d'erreur appropriée
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
        }

        if ($this->displayErrors) {
            echo '<h1>Une erreur est survenue</h1>';
            echo '<p>Veuillez réessayer plus tard ou contacter l\'administrateur.</p>';
        } else {
            echo '<h1>Une erreur est survenue</h1>';
            echo '<p>Une erreur inattendue s\'est produite. Veuillez réessayer plus tard.</p>';
        }
    }

    public function handleShutdown()
    {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            $this->handleError(
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
            );
        }
    }

    private function logError($error)
    {
        $logMessage = sprintf(
            "[%s] %s: %s in %s on line %d\n",
            $error['time'],
            $error['type'],
            $error['message'],
            $error['file'],
            $error['line']
        );

        if (isset($error['trace'])) {
            $logMessage .= "Stack trace:\n" . $error['trace'] . "\n";
        }

        error_log($logMessage, 3, $this->logFile);
    }

    private function displayError($error)
    {
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=utf-8');
        }

        echo '<div style="background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px; border-radius: 4px;">';
        echo '<h3 style="margin-top: 0;">' . htmlspecialchars($error['type']) . '</h3>';
        echo '<p><strong>Message:</strong> ' . htmlspecialchars($error['message']) . '</p>';
        echo '<p><strong>File:</strong> ' . htmlspecialchars($error['file']) . '</p>';
        echo '<p><strong>Line:</strong> ' . htmlspecialchars($error['line']) . '</p>';

        if (isset($error['trace'])) {
            echo '<p><strong>Stack trace:</strong></p>';
            echo '<pre style="background-color: #f8f9fa; padding: 10px; border-radius: 4px;">';
            echo htmlspecialchars($error['trace']);
            echo '</pre>';
        }

        echo '</div>';
    }

    private function getErrorType($type)
    {
        switch ($type) {
            case E_ERROR:
                return 'E_ERROR';
            case E_WARNING:
                return 'E_WARNING';
            case E_PARSE:
                return 'E_PARSE';
            case E_NOTICE:
                return 'E_NOTICE';
            case E_CORE_ERROR:
                return 'E_CORE_ERROR';
            case E_CORE_WARNING:
                return 'E_CORE_WARNING';
            case E_COMPILE_ERROR:
                return 'E_COMPILE_ERROR';
            case E_COMPILE_WARNING:
                return 'E_COMPILE_WARNING';
            case E_USER_ERROR:
                return 'E_USER_ERROR';
            case E_USER_WARNING:
                return 'E_USER_WARNING';
            case E_USER_NOTICE:
                return 'E_USER_NOTICE';
            case E_STRICT:
                return 'E_STRICT';
            case E_RECOVERABLE_ERROR:
                return 'E_RECOVERABLE_ERROR';
            case E_DEPRECATED:
                return 'E_DEPRECATED';
            case E_USER_DEPRECATED:
                return 'E_USER_DEPRECATED';
            default:
                return 'UNKNOWN';
        }
    }
}