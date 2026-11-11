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

// ADNU ADMIN ACTIVITY V1
// Read-only activity data for the last seven calendar days.

$activityCounts = array_fill(0, 7, 0);
$activityLabels = [];

for ($i = 6; $i >= 0; $i--) {
    $activityLabels[] = date('D', strtotime("-$i days"));
}

$activityResult = mysqli_query(
    $conn,
    "SELECT DATE(created_at) AS order_day,
            COUNT(*) AS order_count
     FROM print_requests
     WHERE created_at >= CURDATE() - INTERVAL 6 DAY
       AND created_at < CURDATE() + INTERVAL 1 DAY
     GROUP BY DATE(created_at)"
);

if ($activityResult) {
    $dailyCounts = [];

    while ($activityRow = mysqli_fetch_assoc($activityResult)) {
        $dailyCounts[$activityRow['order_day']] =
            (int) $activityRow['order_count'];
    }

    for ($i = 6; $i >= 0; $i--) {
        $dateKey = date('Y-m-d', strtotime("-$i days"));
        $activityCounts[6 - $i] = $dailyCounts[$dateKey] ?? 0;
    }
}

$activityMax = max(1, max($activityCounts));
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

<style>
/* ADNU ADMIN REDESIGN V1 */

body {
    background: #f4f7fc !important;
    color: #172445;
    font-family: 'Segoe UI', Arial, sans-serif;
}

.sidebar {
    background: #192b57 !important;
    min-height: 100vh;
}

.sidebar .logo {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 23px 17px;
    font-size: 17px;
    font-weight: 800;
}

.sidebar-seal {
    width: 43px;
    height: 43px;
    object-fit: contain;
    flex-shrink: 0;
}

.sidebar a {
    color: #e8eeff;
    padding: 14px 15px;
    margin: 5px 10px;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 600;
}

.sidebar a:hover,
.sidebar a[href="dashboard.php"] {
    background: #30477e;
    color: white;
}

.main {
    padding: 35px 40px;
}

.main > h2 {
    color: #192b57;
    font-size: 28px;
    font-weight: 800;
}

.main > p {
    color: #64748b;
    font-size: 14px;
}

/* STATISTICS */

.stat-card {
    background: white;
    border: 1px solid #edf0f6;
    border-radius: 14px;
    box-shadow: 0 5px 20px rgba(25,43,87,.05);
    height: 100%;
}

.stat-card .card-body {
    padding: 21px;
}

.stat-card h6 {
    color: #64748b;
    font-size: 12px;
    font-weight: 700;
}

.stat-card h2 {
    color: #192b57;
    font-size: 32px;
    font-weight: 800;
    margin-top: 12px;
}

/* CONTENT CARDS */

.recent-card,
.quick-card {
    background: white;
    border: 1px solid #edf0f6;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(25,43,87,.05);
    overflow: hidden;
}

.card-header {
    padding: 18px 22px;
    border-bottom: 1px solid #edf0f6;
}

.card-header h5 {
    color: #192b57;
    font-size: 17px;
    font-weight: 750;
    margin: 0;
}

.request-item {
    padding: 18px 12px;
    border-bottom: 1px solid #edf0f6;
    font-size: 13px;
    overflow-wrap: anywhere;
}

.request-item strong {
    color: #192b57;
    font-size: 14px;
}

.request-item small {
    color: #64748b;
}

.quick-card .btn {
    min-height: 46px;
    padding: 12px;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 700;
}

.quick-card .btn-primary {
    background: #2563eb;
    border-color: #2563eb;
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

body.adnu-dark .main > h2 {
    color: #f1f5f9 !important;
}

body.adnu-dark .main > p,
body.adnu-dark .text-muted {
    color: #a8b5ca !important;
}

body.adnu-dark .stat-card,
body.adnu-dark .recent-card,
body.adnu-dark .quick-card {
    background: #1e293b !important;
    color: #f1f5f9;
    border-color: #334155 !important;
    box-shadow: none;
}

body.adnu-dark .stat-card h2,
body.adnu-dark .card-header h5,
body.adnu-dark .request-item strong {
    color: #f1f5f9 !important;
}

body.adnu-dark .stat-card h6,
body.adnu-dark .request-item small {
    color: #a8b5ca;
}

body.adnu-dark .card-header {
    background: #1e293b !important;
    border-color: #334155 !important;
}

body.adnu-dark .request-item {
    border-color: #334155;
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

    #adminSidebar.sidebar {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        bottom: 0 !important;
        width: min(300px, 85vw) !important;
        max-width: 85vw !important;
        height: 100dvh !important;
        min-height: 0 !important;
        padding: 65px 12px 20px !important;
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

    #adminSidebar.mobile-sidebar-open {
        transform: translateX(0);
    }

    #adminSidebar.sidebar .logo {
        display: none !important;
    }

    #adminSidebar.sidebar a {
        display: block !important;
        flex: 0 0 auto !important;
        width: 100% !important;
        padding: 15px 14px !important;
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

    .main > h2 {
        font-size: 24px;
    }

    .main > .row > .col-md-2 {
        width: 50% !important;
        flex: 0 0 50% !important;
    }

    .stat-card .card-body {
        padding: 17px !important;
    }

    .stat-card h2 {
        font-size: 28px;
    }

    .stat-card h6 {
        font-size: 11px;
    }

    .recent-card,
    .quick-card {
        margin-bottom: 15px;
    }

    .card-header {
        padding: 16px;
    }

    .request-item {
        padding: 15px 5px;
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
    #adminSidebar.sidebar,
    .mobile-menu-overlay {
        transition: none !important;
    }
}
</style>

<style>
/* ADNU ADMIN ACTIVITY V1 */

.activity-panel {
    background: #ffffff;
    border: 1px solid #edf0f6;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(25,43,87,.05);
    height: 100%;
}

.activity-heading {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 25px;
}

.activity-heading h5 {
    color: #192b57;
    font-size: 17px;
    font-weight: 800;
    margin-bottom: 5px;
}

.activity-heading p {
    color: #64748b;
    font-size: 12px;
    margin: 0;
}

.activity-period {
    background: #edf4ff;
    color: #2563eb;
    padding: 7px 11px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}

/* BAR CHART */

.activity-chart {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap: 12px;
    height: 220px;
    align-items: end;
}

.activity-column {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
    height: 100%;
    min-width: 0;
}

.activity-count {
    color: #64748b;
    font-size: 11px;
    font-weight: 700;
    margin-bottom: 7px;
}

.activity-bar-track {
    width: 100%;
    max-width: 42px;
    height: 170px;
    display: flex;
    align-items: flex-end;
}

.activity-bar {
    width: 100%;
    min-height: 3px;
    background: #93b7f3;
    border-radius: 5px 5px 0 0;
    transition: height .3s ease;
}

.activity-column:nth-child(6) .activity-bar {
    background: #2563eb;
}

.activity-day {
    color: #64748b;
    font-size: 11px;
    margin-top: 10px;
}

/* STATUS SUMMARY */

.status-summary-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.status-summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    padding: 13px 15px;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 600;
}

.status-summary-row strong {
    font-size: 15px;
    font-weight: 800;
}

.status-summary-row.status-pending {
    background: #fff3dc;
    color: #92400e;
}

.status-summary-row.status-progress {
    background: #e0e7ff;
    color: #3730a3;
}

.status-summary-row.status-printing {
    background: #dbeafe;
    color: #1e40af;
}

.status-summary-row.status-ready {
    background: #dcfce7;
    color: #166534;
}

.status-summary-row.status-completed {
    background: #e2e8f0;
    color: #334155;
}

/* DARK MODE */

body.adnu-dark .activity-panel {
    background: #1e293b !important;
    border-color: #334155 !important;
    box-shadow: none;
}

body.adnu-dark .activity-heading h5 {
    color: #f1f5f9;
}

body.adnu-dark .activity-heading p,
body.adnu-dark .activity-count,
body.adnu-dark .activity-day {
    color: #a8b5ca;
}

body.adnu-dark .activity-period {
    background: #263b5d;
    color: #bfdbfe;
}

body.adnu-dark .activity-bar {
    background: #60a5fa;
}

body.adnu-dark .activity-column:nth-child(6) .activity-bar {
    background: #2563eb;
}

/* MOBILE */

@media (max-width: 767.98px) {

    .activity-panel {
        padding: 19px;
    }

    .activity-heading {
        margin-bottom: 20px;
    }

    .activity-chart {
        gap: 7px;
        height: 190px;
    }

    .activity-bar-track {
        height: 140px;
    }

    .activity-day,
    .activity-count {
        font-size: 10px;
    }

    .activity-period {
        font-size: 10px;
        padding: 6px 9px;
    }

    .status-summary-row {
        padding: 12px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .activity-bar {
        transition: none;
    }
}
</style>
</head>

<body>

<div class="container-fluid">

<div class="row">

<div class="mobile-app-header">
    <div class="mobile-app-brand">
        <img src="../assets/images/ateneo-logo.png" alt="Ateneo Seal">
        <span>ADNU-PRINTS</span>
    </div>

    <button type="button" id="adminMenuToggle"
        class="mobile-menu-toggle"
        aria-label="Open admin menu"
        aria-controls="adminSidebar"
        aria-expanded="false">
        &#9776;
    </button>
</div>

<div class="mobile-menu-overlay" id="adminMenuOverlay"></div>

<div class="col-md-2 sidebar p-0" id="adminSidebar">

<button type="button" class="mobile-sidebar-close"
    id="adminMenuClose" aria-label="Close admin menu">
    &times;
</button>

<div class="logo">
ADNU-PRINTS
</div>
<a href="dashboard.php">Dashboard</a>

<a href="requests.php">
Manage Requests</a>

<a href="../dashboard.php">
Student View</a>

<a href="../logout.php">
Logout</a>

<?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'){ ?>


<?php } ?>


</div>

<div class="col-md-10 main">

<div class="theme-toolbar">
    <button type="button" id="adminThemeToggle"
        class="theme-button" aria-pressed="false">
        <span id="adminThemeIcon">&#9790;</span>
        <span id="adminThemeText">Dark Mode</span>
    </button>
</div>

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

<!-- DYNAMIC ACTIVITY AND STATUS SUMMARY -->

<div class="row g-3 mt-2 mb-4 admin-activity-section">

    <div class="col-lg-7">

        <div class="activity-panel">

            <div class="activity-heading">
                <div>
                    <h5>Order Activity</h5>
                    <p>Printing requests over the last 7 days</p>
                </div>
                <span class="activity-period">Last 7 days</span>
            </div>

            <div class="activity-chart">

                <?php foreach ($activityCounts as $index => $count): ?>

                <?php
                    $height = ($count / $activityMax) * 100;
                    $barHeight = $count > 0 ? max(6, $height) : 3;
                ?>

                <div class="activity-column">

                    <span class="activity-count">
                        <?php echo $count; ?>
                    </span>

                    <div class="activity-bar-track">
                        <div class="activity-bar"
                             style="height: <?php echo $barHeight; ?>%;">
                        </div>
                    </div>

                    <span class="activity-day">
                        <?php echo htmlspecialchars($activityLabels[$index]); ?>
                    </span>

                </div>

                <?php endforeach; ?>

            </div>

        </div>

    </div>

    <div class="col-lg-5">

        <div class="activity-panel">

            <div class="activity-heading">
                <div>
                    <h5>Order Status Summary</h5>
                    <p>Current printing request statuses</p>
                </div>
            </div>

            <div class="status-summary-list">

                <div class="status-summary-row status-pending">
                    <span>Pending</span>
                    <strong><?php echo $pending; ?></strong>
                </div>

                <div class="status-summary-row status-progress">
                    <span>In Progress</span>
                    <strong><?php echo $inprogress; ?></strong>
                </div>

                <div class="status-summary-row status-printing">
                    <span>Printing</span>
                    <strong><?php echo $printing; ?></strong>
                </div>

                <div class="status-summary-row status-ready">
                    <span>Ready for Pickup</span>
                    <strong><?php echo $ready; ?></strong>
                </div>

                <div class="status-summary-row status-completed">
                    <span>Completed</span>
                    <strong><?php echo $completed; ?></strong>
                </div>

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

<script>
document.addEventListener("DOMContentLoaded", function () {

    const sidebar = document.getElementById("adminSidebar");
    const toggle = document.getElementById("adminMenuToggle");
    const closeButton = document.getElementById("adminMenuClose");
    const overlay = document.getElementById("adminMenuOverlay");

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

    const themeButton = document.getElementById("adminThemeToggle");
    const themeText = document.getElementById("adminThemeText");
    const themeIcon = document.getElementById("adminThemeIcon");

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
<script>
document.addEventListener("DOMContentLoaded", function () {

    // Refresh visible dashboard data every 30 seconds.
    setInterval(function () {

        const menu = document.getElementById("adminSidebar");

        if (document.hidden) return;

        if (menu && menu.classList.contains("mobile-sidebar-open")) {
            return;
        }

        if (document.querySelector("input:focus, select:focus, textarea:focus")) {
            return;
        }

        window.location.reload();

    }, 30000);

});
</script>
</body>
</html>
```
