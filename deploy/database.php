<?php
$required = ['DB_HOST', 'DB_USER', 'DB_PASSWORD', 'DB_NAME'];

foreach ($required as $name) {
    if (getenv($name) === false) {
        error_log("Missing database variable: " . $name);
        http_response_code(500);
        exit("Database is not configured.");
    }
}

$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');
$dbname = getenv('DB_NAME');
$port = (int)(getenv('DB_PORT') ?: 3306);

mysqli_report(MYSQLI_REPORT_OFF);
$conn = mysqli_connect($host, $user, $pass, $dbname, $port);

if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    http_response_code(500);
    exit("Database temporarily unavailable.");
}

mysqli_set_charset($conn, 'utf8mb4');