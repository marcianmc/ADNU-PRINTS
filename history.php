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

<style>
/* ADNU HISTORY REDESIGN V1 */

body {
    background: #f4f7fc !important;
    color: #172445;
    font-family: 'Segoe UI', Arial, sans-serif;
}

.sidebar {
    background: #192b57 !important;
    min-height: 100vh;
}

.sidebar h2 {
    color: white;
    font-size: 21px;
    font-weight: 800;
}

.sidebar a {
    color: #e8eeff;
    padding: 14px 15px;
    margin-bottom: 6px;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 600;
}

.sidebar a:hover,
.sidebar a[href="history.php"] {
    background: #30477e;
    color: white;
}

.main {
    padding: 35px 40px;
}

.main > h1 {
    color: #192b57;
    font-size: 28px;
    font-weight: 800;
}

.main > p {
    font-size: 14px;
    color: #64748b;
}

.stat-card {
    background: white;
    border: 1px solid #edf0f6;
    border-radius: 14px;
    box-shadow: 0 5px 20px rgba(25,43,87,.05);
    height: 100%;
}

.stat-card .card-body {
    padding: 24px;
}

.stat-card h2 {
    color: #192b57;
    font-size: 32px;
    font-weight: 800;
    overflow-wrap: anywhere;
}

.stat-card p {
    color: #64748b;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 0;
}

.card-box {
    background: white;
    border: 1px solid #edf0f6;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(25,43,87,.05);
}

.card-box .card-body {
    padding: 25px;
}

.card-box .form-control {
    min-height: 48px;
    background: #f7f9fc;
    border: 1px solid #e4eaf4;
    border-radius: 9px;
    font-size: 14px;
}

.card-box .form-control:focus {
    background: white;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}

.table {
    margin-bottom: 0;
}

.table th {
    color: #64748b;
    font-size: 12px;
    font-weight: 700;
    padding: 15px;
    white-space: nowrap;
}

.table td {
    color: #253453;
    font-size: 13px;
    padding: 15px;
    vertical-align: middle;
}

.badge-status {
    padding: 8px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
}

.theme-toolbar {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 20px;
}

.theme-button {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    min-height: 43px;
    padding: 10px 16px;
    background: white;
    border: 1px solid #dce5f2;
    border-radius: 10px;
    color: #192b57;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
}

.theme-button:focus-visible,
.mobile-menu-toggle:focus-visible,
.mobile-sidebar-close:focus-visible {
    outline: 3px solid #60a5fa;
    outline-offset: 2px;
}

.mobile-app-header,
.mobile-menu-overlay,
.mobile-sidebar-close {
    display: none;
}

/* DARK MODE */

body.adnu-dark {
    background: #101827 !important;
    color: #f1f5f9;
}

body.adnu-dark .main {
    background: #101827 !important;
    color: #f1f5f9;
}

body.adnu-dark .main > h1 {
    color: #f1f5f9 !important;
}

body.adnu-dark .main > p,
body.adnu-dark .text-muted {
    color: #a8b5ca !important;
}

body.adnu-dark .stat-card,
body.adnu-dark .card-box {
    background: #1e293b !important;
    color: #f1f5f9;
    border-color: #334155 !important;
    box-shadow: none;
}

body.adnu-dark .stat-card h2 {
    color: #f1f5f9;
}

body.adnu-dark .stat-card p {
    color: #a8b5ca;
}

body.adnu-dark .card-box .table,
body.adnu-dark .card-box .table td,
body.adnu-dark .card-box .table th {
    background: #1e293b !important;
    color: #f1f5f9 !important;
    border-color: #334155 !important;
}

body.adnu-dark .card-box .form-control {
    background: #26364d !important;
    color: #f1f5f9;
    border-color: #475569;
}

body.adnu-dark .card-box .form-control::placeholder {
    color: #94a3b8;
}

body.adnu-dark .theme-button {
    background: #26364d;
    color: #f1f5f9;
    border-color: #475569;
}

/* MOBILE */

@media (max-width: 767.98px) {

    .mobile-app-header {
        display: flex;
        width: 100%;
        min-height: 65px;
        padding: 10px 17px;
        align-items: center;
        justify-content: space-between;
        background: white;
        border-bottom: 1px solid #e6ecf5;
    }

    .mobile-app-brand {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #192b57;
        font-size: 15px;
        font-weight: 800;
    }

    .mobile-app-brand img {
        width: 37px;
        height: 37px;
        object-fit: contain;
    }

    .mobile-menu-toggle {
        width: 45px;
        height: 45px;
        border: 1px solid #e2e8f2;
        border-radius: 10px;
        background: #f4f7fc;
        color: #192b57;
        font-size: 24px;
    }

    #historySidebar.sidebar {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        bottom: 0 !important;
        width: min(300px, 85vw) !important;
        max-width: 85vw !important;
        height: 100dvh !important;
        min-height: 0 !important;
        padding: 65px 15px 25px !important;
        margin: 0 !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: stretch !important;
        flex-wrap: nowrap !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        background: #192b57 !important;
        transform: translateX(-105%);
        transition: transform .25s ease;
        z-index: 1002;
    }

    #historySidebar.mobile-sidebar-open {
        transform: translateX(0);
    }

    #historySidebar.sidebar h2 {
        display: block !important;
        font-size: 18px;
        margin-bottom: 25px !important;
    }

    #historySidebar.sidebar a {
        display: block !important;
        flex: 0 0 auto !important;
        width: 100% !important;
        padding: 14px !important;
        margin: 3px 0 !important;
        white-space: normal !important;
        font-size: 14px !important;
    }

    .mobile-sidebar-close {
        display: flex;
        position: absolute;
        top: 12px;
        right: 13px;
        width: 40px;
        height: 40px;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.2);
        border-radius: 9px;
        color: white;
        font-size: 27px;
    }

    .mobile-menu-overlay {
        display: block;
        position: fixed;
        inset: 0;
        background: rgba(10,20,45,.55);
        opacity: 0;
        visibility: hidden;
        transition: opacity .25s ease;
        z-index: 1001;
    }

    .mobile-menu-overlay.is-visible {
        opacity: 1;
        visibility: visible;
    }

    body.mobile-menu-active {
        overflow: hidden;
    }

    .main {
        width: 100% !important;
        max-width: 100% !important;
        padding: 22px 15px 35px !important;
    }

    .main > h1 {
        font-size: 24px !important;
    }

    .main > .row > .col-md-3 {
        width: 50% !important;
        flex: 0 0 50% !important;
        margin-bottom: 12px;
    }

    .stat-card .card-body {
        padding: 16px !important;
    }

    .stat-card h2 {
        font-size: 25px;
    }

    .stat-card p {
        font-size: 11px;
    }

    .card-box .card-body {
        padding: 18px !important;
    }

    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .table {
        min-width: 680px;
    }

    .card-box .form-control {
        font-size: 16px !important;
    }

    body.adnu-dark .mobile-app-header {
        background: #1e293b;
        border-color: #334155;
    }

    body.adnu-dark .mobile-app-brand {
        color: #f1f5f9;
    }

    body.adnu-dark .mobile-menu-toggle {
        background: #334155;
        color: white;
        border-color: #475569;
    }
}

@media (prefers-reduced-motion: reduce) {
    #historySidebar.sidebar,
    .mobile-menu-overlay {
        transition: none !important;
    }
}
</style>
</head>

<body>

<div class="container-fluid">

<div class="row">

<div class="mobile-app-header">
    <div class="mobile-app-brand">
        <img src="assets/images/ateneo-logo.png" alt="Ateneo Seal">
        <span>ADNU-PRINTS</span>
    </div>

    <button type="button" id="historyMenuToggle"
        class="mobile-menu-toggle"
        aria-label="Open navigation menu"
        aria-controls="historySidebar"
        aria-expanded="false">
        &#9776;
    </button>
</div>

<div class="mobile-menu-overlay" id="historyMenuOverlay"></div>

<div class="col-md-2 sidebar p-4" id="historySidebar">

<button type="button" class="mobile-sidebar-close"
    id="historyMenuClose"
    aria-label="Close navigation menu">
    &times;
</button>

<h2 class="fw-bold mb-5">
ADNU-PRINTS
</h2>

<a href="dashboard.php">
Dashboard</a>

<a href="submit_request.php">
Submit Request</a>

<a href="queue.php">
Queue Monitor</a>

<a href="history.php">
Transaction History</a>

<a href="logout.php">
Logout</a>

</div>

<div class="col-md-10 main">

<div class="theme-toolbar">
    <button type="button" id="historyThemeToggle"
        class="theme-button" aria-pressed="false">
        <span id="historyThemeIcon">&#9790;</span>
        <span id="historyThemeText">Dark Mode</span>
    </button>
</div>

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
&#8369;<?php echo number_format($total_cost,2); ?>
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
&#8369;<?php echo number_format($row['estimated_cost'],2); ?>
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

<script>
document.addEventListener("DOMContentLoaded", function () {

    const sidebar = document.getElementById("historySidebar");
    const toggle = document.getElementById("historyMenuToggle");
    const closeButton = document.getElementById("historyMenuClose");
    const overlay = document.getElementById("historyMenuOverlay");

    function openMenu() {
        sidebar.classList.add("mobile-sidebar-open");
        overlay.classList.add("is-visible");
        document.body.classList.add("mobile-menu-active");
        toggle.setAttribute("aria-expanded", "true");
        closeButton.focus();
    }

    function closeMenu() {
        sidebar.classList.remove("mobile-sidebar-open");
        overlay.classList.remove("is-visible");
        document.body.classList.remove("mobile-menu-active");
        toggle.setAttribute("aria-expanded", "false");
        toggle.focus();
    }

    if (sidebar && toggle && closeButton && overlay) {
        toggle.addEventListener("click", openMenu);
        closeButton.addEventListener("click", closeMenu);
        overlay.addEventListener("click", closeMenu);

        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape" &&
                sidebar.classList.contains("mobile-sidebar-open")) {
                closeMenu();
            }
        });

        window.addEventListener("resize", function () {
            if (window.innerWidth >= 768 &&
                sidebar.classList.contains("mobile-sidebar-open")) {
                closeMenu();
            }
        });
    }

    const themeButton = document.getElementById("historyThemeToggle");
    const themeText = document.getElementById("historyThemeText");
    const themeIcon = document.getElementById("historyThemeIcon");

    const savedDark =
        localStorage.getItem("adnu-prints-theme") === "dark";

    document.body.classList.toggle("adnu-dark", savedDark);

    if (themeButton && themeText && themeIcon) {

        function updateThemeButton(dark) {
            themeText.textContent = dark ? "Light Mode" : "Dark Mode";
            themeIcon.innerHTML = dark ? "&#9728;" : "&#9790;";
            themeButton.setAttribute("aria-pressed", String(dark));
        }

        updateThemeButton(savedDark);

        themeButton.addEventListener("click", function () {
            const dark = document.body.classList.toggle("adnu-dark");

            localStorage.setItem(
                "adnu-prints-theme",
                dark ? "dark" : "light"
            );

            updateThemeButton(dark);
        });
    }

});
</script>
</body>
</html>
```
