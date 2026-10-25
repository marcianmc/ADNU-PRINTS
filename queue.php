<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

include 'config/database.php';

$user_id = $_SESSION['user_id'];

$current = mysqli_query(
$conn,
"SELECT *
FROM print_requests
WHERE user_id='$user_id'
ORDER BY queue_number DESC
LIMIT 1"
);

$request = mysqli_fetch_assoc($current);

if(!$request){
?>
<!DOCTYPE html>
<html>
<head>

<title>Queue Monitor</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f4f7fc;
}

.sidebar{
    background:#0f2d7a;
    min-height:100vh;
    color:white;
}

.sidebar a{
    display:block;
    padding:12px;
    color:white;
    text-decoration:none;
    border-radius:10px;
    margin-bottom:10px;
}

.sidebar a:hover{
    background:rgba(255,255,255,.15);
}

.main{
    padding:30px;
}

.empty-card{
    background:white;
    border:none;
    border-radius:25px;
    box-shadow:0 5px 20px rgba(0,0,0,.08);
    padding:70px 40px;
    text-align:center;
}

.empty-icon{
    font-size:90px;
}

</style>

<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="mobile.css?v=1">
</head>

<body>

<div class="container-fluid">

<div class="row">

<div class="col-md-2 sidebar p-4">

<h2 class="fw-bold mb-5">
ADNU-PRINTS
</h2>

<a href="dashboard.php">🏠 Dashboard</a>
<a href="submit_request.php">🖨 Submit Request</a>
<a href="queue.php">📋 Queue Monitor</a>
<a href="history.php">📄 Transaction History</a>
<a href="logout.php">🚪 Logout</a>

</div>

<div class="col-md-10 main">

<h1 class="fw-bold">
Queue Monitor
</h1>

<p class="text-muted">
Track your print request in real time.
</p>

<div class="empty-card mt-4">

<div class="empty-icon">
🖨️
</div>

<h2 class="mt-4">
No Print Requests Yet
</h2>

<p class="text-muted mt-3">
You haven't submitted any printing requests yet.
Once you create a request, your queue status and progress will appear here.
</p>

<a href="submit_request.php"
class="btn btn-primary btn-lg mt-3">

Create Your First Request

</a>

</div>

</div>

</div>

</div>

</body>
</html>
<?php
exit();
}
$queue_number = $request['queue_number'];

$ahead = mysqli_num_rows(
mysqli_query(
$conn,
"SELECT *
FROM print_requests
WHERE queue_number < '$queue_number'
AND status!='Completed'"
)
);

$total_queue = mysqli_num_rows(
mysqli_query(
$conn,
"SELECT *
FROM print_requests
WHERE status!='Completed'"
)
);

$estimate = $ahead * 2;

$completed = mysqli_query(
$conn,
"SELECT *
FROM print_requests
WHERE status='Completed'
ORDER BY updated_at DESC
LIMIT 5"
);
?>

<!DOCTYPE html>
<html>
<head>

<title>Queue Monitor</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:#f4f7fc;
}

.sidebar{
background:#0f2d7a;
min-height:100vh;
color:white;
}

.sidebar a{
display:block;
padding:12px;
color:white;
text-decoration:none;
border-radius:10px;
margin-bottom:10px;
}

.sidebar a:hover{
background:rgba(255,255,255,.15);
}

.main{
padding:30px;
}

.card-box{
border:none;
border-radius:20px;
box-shadow:0 5px 15px rgba(0,0,0,.08);
}

.queue-banner{
background:#1745b5;
color:white;
border-radius:20px;
padding:40px;
}

.big-number{
font-size:90px;
font-weight:bold;
line-height:1;
}

.stat-card{
background:rgba(255,255,255,.15);
padding:20px;
border-radius:15px;
text-align:center;
}

</style>

<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="mobile.css?v=1">
</head>

<body>

<div class="container-fluid">

<div class="row">

<div class="col-md-2 sidebar p-4">

<h2 class="fw-bold mb-5">
ADNU-PRINTS
</h2>

<a href="dashboard.php">🏠 Dashboard</a>
<a href="submit_request.php">🖨 Submit Request</a>
<a href="queue.php">📋 Queue Monitor</a>
<a href="history.php">📄 Transaction History</a>
<a href="logout.php">🚪 Logout</a>

</div>

<div class="col-md-10 main">

<h1 class="fw-bold">
Queue Monitor
</h1>

<p class="text-muted">
Track your print request in real time.
</p>

<div class="queue-banner mb-4">

<div class="row">

<div class="col-md-8">

<p>YOUR QUEUE NUMBER</p>

<div class="big-number">
#<?php echo $queue_number; ?>
</div>

<p class="mt-3">
Current Status:
<?php
$status = $request['status'];

$badgeColors = [
    'Pending' => 'bg-warning text-dark',
    'In Progress' => 'bg-info text-dark',
    'Printing' => 'bg-primary',
    'Ready for Pickup' => 'bg-success',
    'Completed' => 'bg-secondary'
];

$badgeClass = $badgeColors[$status] ?? 'bg-secondary';
?>

<span class="badge <?php echo $badgeClass; ?> fs-6">
<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>
</span>
</p>

</div>

<div class="col-md-4">

<div class="stat-card mb-3">

<h2>
<?php echo $ahead; ?>
</h2>

<div>Ahead of You</div>

</div>

<div class="stat-card mb-3">

<h2>
~<?php echo $estimate; ?> min
</h2>

<div>Estimated Wait</div>

</div>

<div class="stat-card">

<h2>
<?php echo $total_queue; ?>
</h2>

<div>In Queue</div>

</div>

</div>

</div>

</div>

<div class="card card-box">

<div class="card-body">

<h4 class="mb-3">
Queue Progress
</h4>

<?php

$progress = 0;

if($total_queue > 0){
$progress = (($total_queue - $ahead) / $total_queue) * 100;
}

?>

<div class="progress" style="height:25px;">

<div
class="progress-bar progress-bar-striped progress-bar-animated"
style="width: <?php echo $progress; ?>%">

<?php echo round($progress); ?>%

</div>

</div>

</div>

</div>

<div class="card card-box mt-4">

<div class="card-body">

<h4>
Recently Completed Requests
</h4>

<table class="table">

<tr>
<th>Queue</th>
<th>File</th>
<th>Status</th>
</tr>

<?php while($row = mysqli_fetch_assoc($completed)){ ?>

<tr>

<td>
#<?php echo $row['queue_number']; ?>
</td>

<td>
<?php echo htmlspecialchars($row['filename'], ENT_QUOTES, "UTF-8"); ?>
</td>

<td>
<span class="badge bg-success">
Completed
</span>
</td>

</tr>

<?php } ?>

</table>

</div>

</div>

</div>

</div>

</div>

</body>
</html>
```
