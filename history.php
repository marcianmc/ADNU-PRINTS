<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

include 'config/database.php';

$user_id = $_SESSION['user_id'];

$search = "";

if(isset($_GET['search'])){

    $search = mysqli_real_escape_string(
        $conn,
        $_GET['search']
    );

    $query = mysqli_query(
        $conn,
        "SELECT *
        FROM print_requests
        WHERE user_id='$user_id'
        AND filename LIKE '%$search%'
        ORDER BY id DESC"
    );

}else{

    $query = mysqli_query(
        $conn,
        "SELECT *
        FROM print_requests
        WHERE user_id='$user_id'
        ORDER BY id DESC"
    );
}

$total_requests = mysqli_num_rows(
    mysqli_query(
        $conn,
        "SELECT *
        FROM print_requests
        WHERE user_id='$user_id'"
    )
);

$completed = mysqli_num_rows(
    mysqli_query(
        $conn,
        "SELECT *
        FROM print_requests
        WHERE user_id='$user_id'
        AND status='Completed'"
    )
);

$total_pages_data = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT SUM(pages) as pages
        FROM print_requests
        WHERE user_id='$user_id'"
    )
);

$total_pages = $total_pages_data['pages'] ?? 0;

$total_cost_data = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT SUM(estimated_cost) as total
        FROM print_requests
        WHERE user_id='$user_id'"
    )
);

$total_cost = $total_cost_data['total'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>

<title>Transaction History</title>

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
    color:white;
    text-decoration:none;
    padding:12px;
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

.stat-card{
    border:none;
    border-radius:20px;
    box-shadow:0 5px 15px rgba(0,0,0,.08);
}

.badge-status{
    padding:8px 12px;
    border-radius:30px;
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

<a href="dashboard.php">
🏠 Dashboard
</a>

<a href="submit_request.php">
🖨 Submit Request
</a>

<a href="queue.php">
📋 Queue Monitor
</a>

<a href="history.php">
📄 Transaction History
</a>

<a href="logout.php">
🚪 Logout
</a>

</div>

<div class="col-md-10 main">

<h1 class="fw-bold">
Transaction History
</h1>

<p class="text-muted">
View all your print requests.
</p>

<div class="row mb-4">

<div class="col-md-3">

<div class="card stat-card">
<div class="card-body">

<h2>
<?php echo $total_requests; ?>
</h2>

<p>Total Requests</p>

</div>
</div>

</div>

<div class="col-md-3">

<div class="card stat-card">
<div class="card-body">

<h2>
<?php echo $completed; ?>
</h2>

<p>Completed</p>

</div>
</div>

</div>

<div class="col-md-3">

<div class="card stat-card">
<div class="card-body">

<h2>
<?php echo $total_pages; ?>
</h2>

<p>Total Pages</p>

</div>
</div>

</div>

<div class="col-md-3">

<div class="card stat-card">
<div class="card-body">

<h2>
₱<?php echo number_format($total_cost,2); ?>
</h2>

<p>Total Cost</p>

</div>
</div>

</div>

</div>

<div class="card card-box">

<div class="card-body">

<form method="GET" class="mb-4">

<input
type="text"
name="search"
class="form-control"
placeholder="Search file name..."
value="<?php echo $search; ?>">

</form>

<div class="table-responsive">

<table class="table align-middle">

<thead>

<tr>

<th>Queue</th>
<th>File</th>
<th>Copies</th>
<th>Pages</th>
<th>Cost</th>
<th>Status</th>
<th>Date</th>

</tr>

</thead>

<tbody>

<?php while($row = mysqli_fetch_assoc($query)){ ?>

<tr>

<td>
#<?php echo $row['queue_number']; ?>
</td>

<td>
<?php echo $row['filename']; ?>
</td>

<td>
<?php echo $row['copies']; ?>
</td>

<td>
<?php echo $row['pages']; ?>
</td>

<td>
₱<?php echo number_format($row['estimated_cost'],2); ?>
</td>

<td>

<?php

$status = $row['status'];

if($status=="Completed"){
    echo "<span class='badge bg-success badge-status'>$status</span>";
}
elseif($status=="Ready for Pickup"){
    echo "<span class='badge bg-warning text-dark badge-status'>$status</span>";
}
elseif($status=="Printing"){
    echo "<span class='badge bg-primary badge-status'>$status</span>";
}
elseif($status=="In Progress"){
    echo "<span class='badge bg-info text-dark badge-status'>$status</span>";
}
else{
    echo "<span class='badge bg-secondary badge-status'>$status</span>";
}

?>

</td>

<td>
<?php echo date("M d, Y", strtotime($row['created_at'])); ?>
</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

</div>

</div>

</body>
</html>
```
