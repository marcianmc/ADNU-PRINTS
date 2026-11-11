<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../config/database.php';

$user_id = (int) $_SESSION['user_id'];

$stmt = mysqli_prepare(
    $conn,
    "SELECT role FROM users WHERE id = ? LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$user || $user['role'] !== 'admin') {
    http_response_code(403);
    exit("Access denied.");
}

$request_id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$request_id || $request_id < 1) {
    http_response_code(400);
    exit("Invalid document request.");
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT filename, file_path
     FROM print_requests
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $request_id);
mysqli_stmt_execute($stmt);

$document = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$document) {
    http_response_code(404);
    exit("Document not found.");
}

$upload_dir = realpath(__DIR__ . '/../uploads');

$stored_name = basename(
    str_replace('\\', '/', $document['file_path'])
);

$file = $upload_dir
    ? realpath($upload_dir . DIRECTORY_SEPARATOR . $stored_name)
    : false;

if (
    !$upload_dir ||
    !$file ||
    dirname($file) !== $upload_dir ||
    !is_file($file) ||
    !is_readable($file)
) {
    http_response_code(404);
    exit("Uploaded file is unavailable.");
}

$original_name = basename(
    str_replace('\\', '/', $document['filename'])
);

$extension = strtolower(
    pathinfo($original_name, PATHINFO_EXTENSION)
);

$mime_types = [
    'pdf'  => 'application/pdf',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png'
];

if (!isset($mime_types[$extension])) {
    http_response_code(403);
    exit("File type not allowed.");
}

$download_name = preg_replace(
    '/[^A-Za-z0-9._-]/',
    '_',
    $original_name
);

if (!$download_name || $download_name === '.' || $download_name === '..') {
    $download_name = 'document.' . $extension;
}

header('Content-Type: ' . $mime_types[$extension]);
header('Content-Disposition: attachment; filename="' . $download_name . '"');
header('Content-Length: ' . filesize($file));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');

readfile($file);
exit();