<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../config/database.php';

$user_id = (int) $_SESSION['user_id'];

$roleStmt = mysqli_prepare(
    $conn,
    "SELECT role FROM users WHERE id = ? LIMIT 1"
);
mysqli_stmt_bind_param($roleStmt, "i", $user_id);
mysqli_stmt_execute($roleStmt);
$roleResult = mysqli_stmt_get_result($roleStmt);
$roleData = mysqli_fetch_assoc($roleResult);
mysqli_stmt_close($roleStmt);

if (!$roleData || $roleData['role'] !== 'admin') {
    http_response_code(403);
    exit("Access denied. Administrators only.");
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_POST['update_status'])) {

    if (!hash_equals(
        $_SESSION['csrf_token'],
        $_POST['csrf_token'] ?? ''
    )) {
        http_response_code(403);
        exit("Invalid request token. Please refresh the page.");
    }

    $id = filter_var(
        $_POST['request_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    $status = $_POST['status'] ?? '';

    $allowedStatuses = [
        'Pending',
        'In Progress',
        'Printing',
        'Ready for Pickup',
        'Completed'
    ];

    if (!$id || $id < 1 || !in_array($status, $allowedStatuses, true)) {
        http_response_code(400);
        exit("Invalid request or status.");
    }

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE print_requests
         SET status = ?, updated_at = NOW()
         WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "si", $status, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: requests.php");
    exit();
}

$search = trim($_GET['search'] ?? '');
$search = substr($search, 0, 100);
$pattern = '%' . $search . '%';

$stmt = mysqli_prepare(
    $conn,
    "SELECT pr.*, u.fullname, u.student_id
     FROM print_requests pr
     INNER JOIN users u ON pr.user_id = u.id
     WHERE u.fullname LIKE ?
        OR pr.filename LIKE ?
        OR CAST(pr.queue_number AS CHAR) LIKE ?
     ORDER BY pr.queue_number ASC"
);

mysqli_stmt_bind_param($stmt, "sss", $pattern, $pattern, $pattern);
mysqli_stmt_execute($stmt);
$requests = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Manage Requests</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#eef2f7;
    font-family:'Segoe UI',sans-serif;
}

.sidebar{
    background:#062b78;
    min-height:100vh;
    color:white;
}

.logo{
    font-size:26px;
    font-weight:bold;
    padding:25px;
    border-bottom:1px solid rgba(255,255,255,.15);
}

.sidebar a{
    display:block;
    color:white;
    text-decoration:none;
    padding:15px 25px;
}

.sidebar a:hover{
    background:#0d47c8;
}

.main{
    padding:30px;
}

.search-card{
    background:white;
    padding:20px;
    border-radius:15px;
    box-shadow:0 3px 15px rgba(0,0,0,.08);
    margin-bottom:20px;
}

.request-card{
    background:white;
    border-radius:15px;
    box-shadow:0 3px 15px rgba(0,0,0,.08);
    padding:20px;
    margin-bottom:20px;
}

.status-pending{
    background:#fff3cd;
    color:#856404;
}

.status-progress{
    background:#d1ecf1;
    color:#0c5460;
}

.status-printing{
    background:#cce5ff;
    color:#004085;
}

.status-ready{
    background:#d4edda;
    color:#155724;
}

.status-completed{
    background:#e2e3e5;
    color:#383d41;
}

.badge-custom{
    padding:8px 12px;
    border-radius:20px;
    font-size:13px;
}

</style>

</head>

<body>

<div class="container-fluid">

<div class="row">

<div class="col-md-2 sidebar p-0">

<div class="logo">
ADNU-PRINTS
</div>

<a href="dashboard.php">📊 Dashboard</a>
<a href="requests.php">📋 Manage Requests</a>
<a href="../dashboard.php">👨 Student View</a>
<a href="../logout.php">🚪 Logout</a>

</div>

<div class="col-md-10 main">

<h2>Manage Print Requests</h2>

<p class="text-muted">
Update request status and monitor queue activity.
</p>

<div class="search-card">

<form method="GET">

<div class="row">

<div class="col-md-10">

<input
type="text"
name="search"
class="form-control"
placeholder="Search student, file, or queue number..."
value="<?php echo htmlspecialchars($search, ENT_QUOTES, "UTF-8"); ?>">

</div>

<div class="col-md-2">

<button class="btn btn-primary w-100">
Search
</button>

</div>

</div>

</form>

</div>

<?php while($row = mysqli_fetch_assoc($requests)){ ?>

<div class="request-card">

<div class="row">

<div class="col-md-8">

<h5>
Queue #<?php echo $row['queue_number']; ?>
</h5>

<p class="mb-1">

<strong>
<?php echo htmlspecialchars($row['fullname'], ENT_QUOTES, "UTF-8"); ?>
</strong>

</p>

<p class="text-muted mb-1">
Student ID:
<?php echo htmlspecialchars($row['student_id'], ENT_QUOTES, "UTF-8"); ?>
</p>

<p class="mb-1">
<?php echo htmlspecialchars($row['filename'], ENT_QUOTES, "UTF-8"); ?>
</p>

<p class="mb-1">
Pages:
<?php echo $row['pages']; ?>
</p>

<p class="mb-1">
Copies:
<?php echo $row['copies']; ?>
</p>

<p class="mb-1">
Cost:
<strong>
₱<?php echo $row['estimated_cost']; ?>
</strong>
</p>

<?php

$statusClass = "";

if($row['status']=="Pending"){
    $statusClass="status-pending";
}
elseif($row['status']=="In Progress"){
    $statusClass="status-progress";
}
elseif($row['status']=="Printing"){
    $statusClass="status-printing";
}
elseif($row['status']=="Ready for Pickup"){
    $statusClass="status-ready";
}
elseif($row['status']=="Completed"){
    $statusClass="status-completed";
}

?>

<span class="badge-custom <?php echo $statusClass; ?>">
<?php echo htmlspecialchars($row['status'], ENT_QUOTES, "UTF-8"); ?>
</span>

</div>

<div class="col-md-4">

<form method="POST">
<input type="hidden" name="csrf_token"
value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

<input
type="hidden"
name="request_id"
value="<?php echo $row['id']; ?>">

<select
name="status"
class="form-select mb-2">

<option
<?php if($row['status']=="Pending") echo "selected"; ?>>
Pending
</option>

<option
<?php if($row['status']=="In Progress") echo "selected"; ?>>
In Progress
</option>

<option
<?php if($row['status']=="Printing") echo "selected"; ?>>
Printing
</option>

<option
<?php if($row['status']=="Ready for Pickup") echo "selected"; ?>>
Ready for Pickup
</option>

<option
<?php if($row['status']=="Completed") echo "selected"; ?>>
Completed
</option>

</select>

<button
name="update_status"
class="btn btn-primary w-100">

Update Status

</button>

</form>

</div>

</div>

</div>

<?php } ?>

</div>

</div>

</div>

</body>
</html>