<?php

/**
 * Error Handling and Logging
 */

function logError($message, $context = [])
{
    $logFile = LOG_PATH . 'error_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? json_encode($context) : '';
    $logMessage = "[$timestamp] $message $contextStr\n";

    error_log($logMessage, 3, $logFile);

    if (DEBUG_MODE) {
        return $message;
    } else {
        return 'An error occurred. Please try again or contact support.';
    }
}

// Custom error handler
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    logError("PHP Error: $errstr", [
        'file' => $errfile,
        'line' => $errline,
        'type' => $errno
    ]);
});

// Custom exception handler
set_exception_handler(function ($exception) {
    logError("Uncaught Exception: " . $exception->getMessage(), [
        'file' => $exception->getFile(),
        'line' => $exception->getLine()
    ]);

    http_response_code(500);
    if (isAjaxRequest()) {
        echo json_encode(['error' => 'Internal server error']);
    } else {
        echo "An error occurred. Please contact support.";
    }
    exit;
});
