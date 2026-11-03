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
<style>
/* ADNU DASHBOARD REDESIGN V1 */

body {
    background: #f4f7fc !important;
    font-family: 'Segoe UI', Arial, sans-serif;
    color: #172445;
}

.sidebar {
    background: #192b57 !important;
}

.sidebar .logo {
    font-size: 20px;
    font-weight: 800;
    padding: 25px 18px;
}

.sidebar a {
    border-radius: 9px;
    margin: 5px 10px;
    padding: 14px 15px;
    font-size: 13px;
}

.sidebar a:hover,
.sidebar a[href="dashboard.php"] {
    background: #30477e;
}

.main {
    padding: 35px;
}

.main > h2 {
    color: #192b57;
    font-weight: 800;
}

.stat-card {
    border-radius: 14px;
    border: 1px solid #edf0f6;
    box-shadow: 0 5px 20px rgba(25,43,87,.05);
}

.stat-card h6 {
    color: #64748b;
    font-size: 13px;
    font-weight: 600;
}

.stat-card h2 {
    color: #192b57;
    font-size: 36px;
    font-weight: 800;
}

.queue-box {
    background: linear-gradient(120deg, #192b57, #2563eb);
    border-radius: 16px;
}

.request-card {
    border: 1px solid #edf0f6;
    box-shadow: none;
}

.quick-card .btn {
    border-radius: 8px;
    min-height: 46px;
    font-weight: 600;
}

@media (max-width: 767.98px) {
    .main {
        padding: 20px 14px !important;
    }

    .main > h2 {
        font-size: 23px !important;
    }

    .stat-card h2 {
        font-size: 29px !important;
    }

    .queue-box {
        padding: 22px;
    }
}
</style>

<style>
/* ADNU MOBILE SIDEBAR V2 */

.mobile-app-header,
.mobile-menu-overlay,
.mobile-sidebar-close {
    display: none;
}

@media (max-width: 767.98px) {

    .mobile-app-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        min-height: 65px;
        padding: 10px 17px;
        background: #ffffff;
        border-bottom: 1px solid #e6ecf5;
        position: relative;
        z-index: 10;
    }

    .mobile-app-brand {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 15px;
        font-weight: 800;
        color: #192b57;
    }

    .mobile-app-brand img {
        width: 37px;
        height: 37px;
        object-fit: contain;
    }

    .mobile-menu-toggle {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f4f7fc;
        color: #192b57;
        border: 1px solid #e2e8f2;
        border-radius: 10px;
        font-size: 24px;
        cursor: pointer;
    }

    /* Hidden sidebar */

    #mobileSidebar.sidebar {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        bottom: 0 !important;
        width: min(300px, 85vw) !important;
        max-width: 85vw !important;
        height: 100dvh !important;
        min-height: 0 !important;
        padding: 60px 12px 20px !important;
        margin: 0 !important;

        display: flex !important;
        flex-direction: column !important;
        align-items: stretch !important;
        flex-wrap: nowrap !important;
        gap: 5px !important;

        overflow-y: auto !important;
        overflow-x: hidden !important;

        background: #192b57 !important;

        transform: translateX(-105%);
        transition: transform .25s ease;
        z-index: 1002;
        box-shadow: 10px 0 35px rgba(0,0,0,.15);
    }

    /* Open sidebar */

    #mobileSidebar.sidebar.mobile-sidebar-open {
        transform: translateX(0);
    }

    #mobileSidebar .logo {
        display: none !important;
    }

    #mobileSidebar .user-box {
        display: block !important;
        width: 100%;
        padding: 15px 12px 20px;
        margin-bottom: 10px;
        border-bottom: 1px solid rgba(255,255,255,.15);
    }

    #mobileSidebar .user-box h5 {
        color: white;
        font-size: 16px;
        overflow-wrap: anywhere;
    }

    #mobileSidebar .user-box small {
        color: #bdc9e6;
    }

    #mobileSidebar.sidebar a {
        display: block !important;
        width: 100% !important;
        flex: 0 0 auto !important;
        white-space: normal !important;
        padding: 15px 14px !important;
        margin: 2px 0 !important;
        font-size: 14px !important;
        border-radius: 9px !important;
        color: #eef2ff !important;
        text-decoration: none;
    }

    #mobileSidebar.sidebar a:hover,
    #mobileSidebar.sidebar a:focus-visible {
        background: #30477e !important;
    }

    #mobileSidebar.sidebar a[href="dashboard.php"] {
        background: #30477e !important;
    }

    .mobile-sidebar-close {
        display: flex;
        align-items: center;
        justify-content: center;

        position: absolute;
        top: 12px;
        right: 13px;

        width: 40px;
        height: 40px;
        border: 1px solid rgba(255,255,255,.2);
        border-radius: 9px;
        background: rgba(255,255,255,.1);
        color: white;
        font-size: 28px;
        cursor: pointer;
    }

    .mobile-menu-overlay {
        display: block;
        position: fixed;
        inset: 0;
        background: rgba(10,20,45,.55);
        opacity: 0;
        visibility: hidden;
        transition: opacity .25s ease, visibility .25s ease;
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
        padding: 20px 15px 35px !important;
    }
}

@media (prefers-reduced-motion: reduce) {
    #mobileSidebar.sidebar,
    .mobile-menu-overlay {
        transition: none !important;
    }
}
</style>

<style>
/* ADNU DASHBOARD THEME V1 */

.dashboard-theme-toolbar {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 20px;
}

.dashboard-theme-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    min-height: 43px;
    padding: 10px 17px;
    border: 1px solid #dce5f2;
    border-radius: 10px;
    background: white;
    color: #192b57;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: background .2s, color .2s;
}

.dashboard-theme-button:hover {
    background: #edf4ff;
}

.dashboard-theme-button:focus-visible {
    outline: 3px solid #60a5fa;
    outline-offset: 2px;
}

#dashboardThemeIcon {
    font-size: 21px;
    line-height: 1;
}

/* DARK MODE */

body.adnu-dark {
    background: #101827 !important;
    color: #e8eef8 !important;
}

body.adnu-dark .main {
    background: #101827 !important;
    color: #e8eef8 !important;
}

body.adnu-dark .main > h2 {
    color: #f1f5f9 !important;
}

body.adnu-dark .main > p,
body.adnu-dark .main .text-muted {
    color: #a8b5ca !important;
}

body.adnu-dark .main .card,
body.adnu-dark .main .stat-card,
body.adnu-dark .main .quick-card,
body.adnu-dark .main .request-card {
    background: #1e293b !important;
    color: #e8eef8 !important;
    border-color: #334155 !important;
    box-shadow: none !important;
}

body.adnu-dark .main .card-header {
    background: #1e293b !important;
    color: #f1f5f9 !important;
    border-color: #334155 !important;
}

body.adnu-dark .main .card-header h4,
body.adnu-dark .main .stat-card h2,
body.adnu-dark .main .request-card h6,
body.adnu-dark .main h3,
body.adnu-dark .main h4,
body.adnu-dark .main h5 {
    color: #f1f5f9 !important;
}

body.adnu-dark .main .stat-card h6,
body.adnu-dark .main .request-card p {
    color: #a8b5ca !important;
}

body.adnu-dark .queue-box {
    background: linear-gradient(120deg, #172554, #1d4ed8) !important;
    color: white !important;
}

body.adnu-dark .queue-box h1,
body.adnu-dark .queue-box h5,
body.adnu-dark .queue-box p {
    color: white !important;
}

body.adnu-dark .dashboard-theme-button {
    background: #26364d;
    color: #f1f5f9;
    border-color: #42536b;
}

body.adnu-dark .dashboard-theme-button:hover {
    background: #334155;
}

body.adnu-dark .mobile-app-header {
    background: #1e293b !important;
    border-color: #334155 !important;
}

body.adnu-dark .mobile-app-brand {
    color: #f1f5f9 !important;
}

body.adnu-dark .mobile-menu-toggle {
    background: #334155 !important;
    color: #f1f5f9 !important;
    border-color: #475569 !important;
}

@media (max-width: 767.98px) {

    .dashboard-theme-toolbar {
        margin-bottom: 18px;
    }

    .dashboard-theme-button {
        min-height: 44px;
        padding: 10px 14px;
        font-size: 12px;
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

    <button
        type="button"
        class="mobile-menu-toggle"
        id="mobileMenuToggle"
        aria-label="Open navigation menu"
        aria-controls="mobileSidebar"
        aria-expanded="false"
    >
        <span>&#9776;</span>
    </button>
</div>

<div id="mobileMenuOverlay" class="mobile-menu-overlay"></div>
<div class="col-md-2 sidebar p-0" id="mobileSidebar">

<button
    type="button"
    class="mobile-sidebar-close"
    id="mobileSidebarClose"
    aria-label="Close navigation menu"
>
    &times;
</button>

<div class="logo">
ADNU-PRINTS
</div>

<div class="user-box">
<h5><?php echo $_SESSION['fullname']; ?></h5>
<small>Student Account</small>
</div>

<a href="dashboard.php">Dashboard</a>
<a href="submit_request.php">Submit Request</a>
<a href="queue.php">Queue Monitor</a>
<a href="history.php">Transaction History</a>
<a href="logout.php">Logout</a>

</div>

<div class="col-md-10 main">

<div class="dashboard-theme-toolbar">
    <button
        type="button"
        id="dashboardThemeToggle"
        class="dashboard-theme-button"
        aria-label="Switch to dark mode"
        aria-pressed="false"
    >
        <span id="dashboardThemeIcon">&#9790;</span>
        <span id="dashboardThemeText">Dark Mode</span>
    </button>
</div>

<h2>
Good Day,
<?php echo $_SESSION['fullname']; ?>
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

<script>
document.addEventListener("DOMContentLoaded", function () {

    const sidebar = document.getElementById("mobileSidebar");
    const toggle = document.getElementById("mobileMenuToggle");
    const closeButton = document.getElementById("mobileSidebarClose");
    const overlay = document.getElementById("mobileMenuOverlay");

    if (!sidebar || !toggle || !closeButton || !overlay) return;

    function openMenu() {
        sidebar.classList.add("mobile-sidebar-open");
        overlay.classList.add("is-visible");
        document.body.classList.add("mobile-menu-active");
        toggle.setAttribute("aria-expanded", "true");
        toggle.setAttribute("aria-label", "Close navigation menu");
        closeButton.focus();
    }

    function closeMenu() {
        sidebar.classList.remove("mobile-sidebar-open");
        overlay.classList.remove("is-visible");
        document.body.classList.remove("mobile-menu-active");
        toggle.setAttribute("aria-expanded", "false");
        toggle.setAttribute("aria-label", "Open navigation menu");
        toggle.focus();
    }

    toggle.addEventListener("click", function () {
        if (sidebar.classList.contains("mobile-sidebar-open")) {
            closeMenu();
        } else {
            openMenu();
        }
    });

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

});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const button = document.getElementById("dashboardThemeToggle");
    const text = document.getElementById("dashboardThemeText");
    const icon = document.getElementById("dashboardThemeIcon");

    if (!button || !text || !icon) return;

    // Always start in Light Mode.
    const savedTheme = localStorage.getItem("adnu-prints-theme");
const savedDark = savedTheme === "dark";

document.body.classList.toggle("adnu-dark", savedDark);

    text.textContent = savedDark ? "Light Mode" : "Dark Mode";
icon.innerHTML = savedDark ? "&#9728;" : "&#9790;";
button.setAttribute("aria-pressed", String(savedDark));
button.setAttribute(
    "aria-label",
    savedDark ? "Switch to light mode" : "Switch to dark mode"
);
button.addEventListener("click", function () {

        const dark = document.body.classList.toggle("adnu-dark");
localStorage.setItem("adnu-prints-theme", dark ? "dark" : "light");

        text.textContent = dark ? "Light Mode" : "Dark Mode";
        icon.innerHTML = dark ? "&#9728;" : "&#9790;";

        button.setAttribute(
            "aria-label",
            dark ? "Switch to light mode" : "Switch to dark mode"
        );

        button.setAttribute("aria-pressed", String(dark));

    });

});
</script>
</body>
</html>
```
