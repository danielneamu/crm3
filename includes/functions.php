<?php

/**
 * Utility Functions
 */

// Output escaping
function esc($string)
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Validation functions
function validateRequired($value, $fieldName)
{
    if (empty(trim($value))) {
        throw new Exception("$fieldName is required");
    }
    return trim($value);
}

function validateInt($value, $fieldName, $min = null, $max = null)
{
    if (!is_numeric($value) || (int)$value != $value) {
        throw new Exception("$fieldName must be an integer");
    }
    $value = (int)$value;

    if ($min !== null && $value < $min) {
        throw new Exception("$fieldName must be at least $min");
    }
    if ($max !== null && $value > $max) {
        throw new Exception("$fieldName must be at most $max");
    }

    return $value;
}

function validateDecimal($value, $fieldName)
{
    if (!is_numeric($value)) {
        throw new Exception("$fieldName must be a number");
    }
    return (float)$value;
}

function validateDate($date, $fieldName)
{
    $d = DateTime::createFromFormat('Y-m-d', $date);
    if (!$d || $d->format('Y-m-d') !== $date) {
        throw new Exception("$fieldName must be a valid date (YYYY-MM-DD)");
    }
    return $date;
}

function validateEnum($value, $allowedValues, $fieldName)
{
    if (!in_array($value, $allowedValues, true)) {
        throw new Exception("$fieldName has invalid value");
    }
    return $value;
}

function validateEmail($email)
{
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }
    return $email;
}

// AOV Calculation
function calculateAOV($tcv, $contractMonths)
{
    if ($contractMonths == 0) return 0;

    switch ($contractMonths) {
        case 1:
        case 12:
            return $tcv;
        case 24:
            return $tcv / 2;
        case 36:
            return $tcv / 3;
        default:
            return ($tcv / $contractMonths) * 12;
    }
}

// Redirect helper
function redirect($url)
{
    header("Location: $url");
    exit;
}

// JSON response helpers
function jsonSuccess($data, $message = '')
{
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => $data,
        'message' => $message
    ]);
    exit;
}

function jsonError($message, $statusCode = 400)
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => $message
    ]);
    exit;
}
