
<?php

require_once __DIR__ . '/../src/db.php';

try {
    db()->query('SELECT 1');

    echo 'Connected successfully!';

} catch (Throwable $e) {
    http_response_code(500);
    echo 'Connection failed!';
}
