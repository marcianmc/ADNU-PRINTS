<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

include 'config/database.php';

$user_id = $_SESSION['user_id'];

$total_requests = mysqli_num_rows(
    mysqli_query(
        $conn,
        "SELECT * FROM print_requests WHERE user_id='$user_id'"
    )
);

$pending_requests = mysqli_num_rows(
    mysqli_query(
        $conn,
        "SELECT * FROM print_requests
        WHERE user_id='$user_id'
        AND status='Pending'"
    )
);

$completed_requests = mysqli_num_rows(
    mysqli_query(
        $conn,
        "SELECT * FROM print_requests
        WHERE user_id='$user_id'
        AND status='Completed'"
    )
);

$latest_queue = mysqli_query(
    $conn,
    "SELECT queue_number
    FROM print_requests
    WHERE user_id='$user_id'
    ORDER BY id DESC
    LIMIT 1"
);

$queue_data = mysqli_fetch_assoc($latest_queue);

$current_queue = $queue_data['queue_number'] ?? '-';
?>

<!DOCTYPE html>
<html>
<head>

<title>ADNU-PRINTS Dashboard</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

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
    font-size:28px;
    font-weight:bold;
    padding:25px;
    border-bottom:1px solid rgba(255,255,255,.15);
}

.user-box{
    padding:20px;
    border-bottom:1px solid rgba(255,255,255,.15);
}

.user-box h5{
    margin:0;
}

.user-box small{
    color:#d6d6d6;
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
    margin-top:10px;
}

.queue-box{
    background:#0d47c8;
    color:white;
    border-radius:20px;
    padding:30px;
}

.queue-box h1{
    font-size:55px;
    font-weight:bold;
}

.request-card{
    background:white;
    border-radius:12px;
    padding:15px;
    margin-bottom:12px;
    box-shadow:0 2px 10px rgba(0,0,0,.05);
}

.badge-status{
    padding:8px 12px;
    border-radius:20px;
}

.quick-card{
    border:none;
    border-radius:15px;
}

.quick-card .btn{
    margin-bottom:10px;
}

@media(max-width:768px){

.sidebar{
    min-height:auto;
}

.logo{
    text-align:center;
}

.main{
    padding:15px;
}

.queue-box h1{
    font-size:35px;
}

}
</style>

<link rel="stylesheet" href="mobile.css?v=1">
</head>

<body>

<div class="container-fluid">

<div class="row">

<div class="col-md-2 sidebar p-0">

<div class="logo">
ADNU-PRINTS
</div>

<div class="user-box">
<h5><?php echo $_SESSION['fullname']; ?></h5>
<small>Student Account</small>
</div>

<a href="dashboard.php">🏠 Dashboard</a>
<a href="submit_request.php">🖨 Submit Request</a>
<a href="queue.php">📋 Queue Monitor</a>
<a href="history.php">📄 Transaction History</a>
<a href="logout.php">🚪 Logout</a>

</div>

<div class="col-md-10 main">

<h2>
Good Day,
<?php echo $_SESSION['fullname']; ?> 👋
</h2>

<p class="text-muted">
Here's what's happening with your print requests today.
</p>

<div class="row mt-4">

<div class="col-md-4 mb-3">

<div class="card stat-card">

<div class="card-body">

<h6>Pending Requests</h6>

<h2>
<?php echo $pending_requests; ?>
</h2>

</div>

</div>

</div>

<div class="col-md-4 mb-3">

<div class="card stat-card">

<div class="card-body">

<h6>Total Requests</h6>

<h2>
<?php echo $total_requests; ?>
</h2>

</div>

</div>

</div>

<div class="col-md-4 mb-3">

<div class="card stat-card">

<div class="card-body">

<h6>Completed</h6>

<h2>
<?php echo $completed_requests; ?>
</h2>

</div>

</div>

</div>

</div>

<div class="queue-box mt-3">

<h5>CURRENT QUEUE STATUS</h5>

<h1>#<?php echo $current_queue; ?></h1>

<p>
Your latest queue number
</p>

</div>

<div class="row mt-4">

<div class="col-md-8">

<div class="card shadow border-0">

<div class="card-header bg-white">
<h4>Recent Requests</h4>
</div>

<div class="card-body">

<?php

$requests = mysqli_query(
$conn,
"SELECT *
FROM print_requests
WHERE user_id='$user_id'
ORDER BY id DESC
LIMIT 5"
);

if(mysqli_num_rows($requests) > 0){

while($row=mysqli_fetch_assoc($requests)){

?>

<div class="request-card">

<h6>
<?php echo $row['filename']; ?>
</h6>

<p class="mb-1">
Queue #<?php echo $row['queue_number']; ?>
</p>

<span class="badge bg-primary">
<?php echo $row['status']; ?>
</span>

</div>

<?php

}

}else{

echo "<p>No requests yet.</p>";

}

?>

</div>

</div>

</div>

<div class="col-md-4">

<div class="card quick-card shadow">

<div class="card-header bg-white">
<h4>Quick Actions</h4>
</div>

<div class="card-body">

<a href="submit_request.php"
class="btn btn-primary w-100">
New Print Request
</a>

<a href="queue.php"
class="btn btn-success w-100">
Queue Monitor
</a>

<a href="history.php"
class="btn btn-secondary w-100">
Transaction History
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
