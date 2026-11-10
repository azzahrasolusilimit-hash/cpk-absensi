
<?php

/**
 * Mengirim respons JSON kepada frontend.
 */
function jsonResponse(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
}

/**
 * Membaca data JSON dari request frontend.
 */
function getJsonInput(): array
{
    $rawInput = file_get_contents('php://input');

    if ($rawInput === false || trim($rawInput) === '') {
        return [];
    }

    $data = json_decode($rawInput, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        jsonResponse([
            'success' => false,
            'message' => 'Format JSON tidak valid.'
        ], 400);
    }

    return $data;
}

/**
 * Memeriksa metode HTTP request.
 */
function requireMethod(string $method): void
{
    if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
        header('Allow: ' . strtoupper($method));

        jsonResponse([
            'success' => false,
            'message' => 'Metode request tidak diizinkan.'
        ], 405);
    }
}

/**
 * Memeriksa field wajib.
 */
function validateRequiredFields(array $data, array $fields): array
{
    $missingFields = [];

    foreach ($fields as $field) {
        if (
            !isset($data[$field]) ||
            (is_string($data[$field]) && trim($data[$field]) === '')
        ) {
            $missingFields[] = $field;
        }
    }

    return $missingFields;
}
