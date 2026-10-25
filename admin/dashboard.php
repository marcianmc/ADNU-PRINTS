<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../config/database.php';

$user_id = (int) $_SESSION['user_id'];

$stmt = mysqli_prepare(
    $conn,
    "SELECT role FROM users WHERE id = ? LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user || $user['role'] !== 'admin') {
    http_response_code(403);
    exit("Access denied. Administrators only.");
}

$total = mysqli_num_rows(
    mysqli_query($conn, "SELECT * FROM print_requests")
);

$pending = mysqli_num_rows(
    mysqli_query(
        $conn,
        "SELECT * FROM print_requests WHERE status='Pending'"
    )
);

$inprogress = mysqli_num_rows(
    mysqli_query(
        $conn,
        "SELECT * FROM print_requests WHERE status='In Progress'"
    )
);

$printing = mysqli_num_rows(
    mysqli_query(
        $conn,
        "SELECT * FROM print_requests WHERE status='Printing'"
    )
);

$ready = mysqli_num_rows(
    mysqli_query(
        $conn,
        "SELECT * FROM print_requests WHERE status='Ready for Pickup'"
    )
);

$completed = mysqli_num_rows(
    mysqli_query(
        $conn,
        "SELECT * FROM print_requests WHERE status='Completed'"
    )
);

$recent_requests = mysqli_query(
    $conn,
    "SELECT pr.*, u.fullname
    FROM print_requests pr
    INNER JOIN users u ON pr.user_id = u.id
    ORDER BY pr.id DESC
    LIMIT 5"
);
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Admin Dashboard</title>

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
    transition:.3s;
}

.sidebar a:hover{
    background:#0d47c8;
}

.main{
    padding:30px;
}

.stat-card{
    border:none;
    border-radius:18px;
    box-shadow:0 3px 15px rgba(0,0,0,.08);
}

.stat-card .card-body{
    padding:25px;
}

.stat-card h2{
    font-weight:bold;
}

.recent-card{
    border:none;
    border-radius:18px;
    box-shadow:0 3px 15px rgba(0,0,0,.08);
}

.request-item{
    padding:15px;
    border-bottom:1px solid #eee;
}

.request-item:last-child{
    border-bottom:none;
}

.badge-status{
    padding:8px 12px;
    border-radius:20px;
}

.quick-card{
    border:none;
    border-radius:18px;
    box-shadow:0 3px 15px rgba(0,0,0,.08);
}

</style>

<link rel="stylesheet" href="../mobile.css?v=1">
</head>

<body>

<div class="container-fluid">

<div class="row">

<div class="col-md-2 sidebar p-0">

<div class="logo">
ADNU-PRINTS
</div>
<a href="dashboard.php">🏠 Dashboard</a>

<a href="requests.php">
📋 Manage Requests
</a>

<a href="../dashboard.php">
👨‍🎓 Student View
</a>

<a href="../logout.php">
🚪 Logout
</a>

<?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'){ ?>


<?php } ?>


</div>

<div class="col-md-10 main">

<h2>Admin Dashboard</h2>

<p class="text-muted">
Manage printing requests and monitor system activity.
</p>

<div class="row mt-4">

<div class="col-md-2 mb-3">
<div class="card stat-card">
<div class="card-body">
<h6>Pending</h6>
<h2><?php echo $pending; ?></h2>
</div>
</div>
</div>

<div class="col-md-2 mb-3">
<div class="card stat-card">
<div class="card-body">
<h6>In Progress</h6>
<h2><?php echo $inprogress; ?></h2>
</div>
</div>
</div>

<div class="col-md-2 mb-3">
<div class="card stat-card">
<div class="card-body">
<h6>Printing</h6>
<h2><?php echo $printing; ?></h2>
</div>
</div>
</div>

<div class="col-md-2 mb-3">
<div class="card stat-card">
<div class="card-body">
<h6>Ready</h6>
<h2><?php echo $ready; ?></h2>
</div>
</div>
</div>

<div class="col-md-2 mb-3">
<div class="card stat-card">
<div class="card-body">
<h6>Completed</h6>
<h2><?php echo $completed; ?></h2>
</div>
</div>
</div>

<div class="col-md-2 mb-3">
<div class="card stat-card">
<div class="card-body">
<h6>Total</h6>
<h2><?php echo $total; ?></h2>
</div>
</div>
</div>

</div>

<div class="row mt-3">

<div class="col-md-8">

<div class="card recent-card">

<div class="card-header bg-white">
<h5>Recent Requests</h5>
</div>

<div class="card-body">

<?php while($row = mysqli_fetch_assoc($recent_requests)){ ?>

<div class="request-item">

<strong>
<?php echo $row['fullname']; ?>
</strong>

<br>

<small>
Queue #<?php echo $row['queue_number']; ?>
</small>

<br>

<?php echo $row['filename']; ?>

<br>

<span class="badge bg-primary">
<?php echo $row['status']; ?>
</span>

</div>

<?php } ?>

</div>

</div>

</div>

<div class="col-md-4">

<div class="card quick-card">

<div class="card-header bg-white">
<h5>Quick Actions</h5>
</div>

<div class="card-body">

<a href="requests.php"
class="btn btn-primary w-100 mb-2">
Manage Requests
</a>

<a href="dashboard.php"
class="btn btn-success w-100 mb-2">
Refresh Dashboard
</a>

<a href="../dashboard.php"
class="btn btn-secondary w-100">
Student Dashboard
</a>

</div>

</div>

</div>

</div>

</div>

</div>

</div>

</body>
</html>
```
