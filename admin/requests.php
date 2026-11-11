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

<link rel="stylesheet" href="../mobile.css?v=1">

<style>
/* ADNU ADMIN REQUESTS REDESIGN V1 */

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
.sidebar a[href="requests.php"] {
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

/* SEARCH */

.search-card {
    background: white;
    border: 1px solid #edf0f6;
    border-radius: 14px;
    padding: 22px;
    box-shadow: 0 5px 20px rgba(25,43,87,.05);
}

.search-card .form-control {
    min-height: 48px;
    background: #f7f9fc;
    border: 1px solid #e4eaf4;
    border-radius: 9px;
    font-size: 14px;
}

.search-card .form-control:focus {
    background: white;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}

.search-card .btn {
    min-height: 48px;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 700;
}

/* REQUEST CARDS */

.request-card {
    background: white;
    border: 1px solid #edf0f6;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(25,43,87,.05);
    margin-bottom: 18px;
}

.request-card h5 {
    color: #192b57;
    font-size: 19px;
    font-weight: 800;
    margin-bottom: 15px;
}

.request-card p {
    font-size: 13px;
    color: #475569;
    overflow-wrap: anywhere;
}

.request-card strong {
    color: #192b57;
}

.request-card .text-muted {
    color: #64748b !important;
}

.request-card .form-select {
    min-height: 48px;
    background-color: #f7f9fc;
    border: 1px solid #e4eaf4;
    border-radius: 9px;
    font-size: 14px;
}

.request-card .btn {
    min-height: 48px;
    background: #2563eb;
    border: none;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 700;
}

.request-card .btn:hover,
.search-card .btn:hover {
    background: #1d4ed8;
}

.badge-custom {
    display: inline-block;
    padding: 8px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
}

/* THEME BUTTON */

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

body.adnu-dark .search-card,
body.adnu-dark .request-card {
    background: #1e293b !important;
    color: #f1f5f9;
    border-color: #334155 !important;
    box-shadow: none;
}

body.adnu-dark .request-card h5,
body.adnu-dark .request-card strong,
body.adnu-dark .request-card p {
    color: #f1f5f9 !important;
}

body.adnu-dark .search-card .form-control,
body.adnu-dark .request-card .form-select {
    background-color: #26364d !important;
    color: #f1f5f9 !important;
    border-color: #475569 !important;
}

body.adnu-dark .search-card .form-control::placeholder {
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

    #requestsSidebar.sidebar {
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

    #requestsSidebar.mobile-sidebar-open {
        transform: translateX(0);
    }

    #requestsSidebar.sidebar .logo {
        display: none !important;
    }

    #requestsSidebar.sidebar a {
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

    .search-card {
        padding: 17px;
    }

    .search-card .row {
        row-gap: 12px;
    }

    .request-card {
        padding: 19px;
    }

    .request-card .row > [class*="col-"] {
        width: 100% !important;
        flex: 0 0 100% !important;
    }

    .request-card .col-md-4 {
        margin-top: 20px;
        padding-top: 17px;
        border-top: 1px solid #edf0f6;
    }

    .request-card .form-select {
        font-size: 16px !important;
    }

    .request-card .btn {
        min-height: 48px;
    }

    body.adnu-dark .request-card .col-md-4 {
        border-color: #334155;
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
    #requestsSidebar.sidebar,
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
        <img src="../assets/images/ateneo-logo.png" alt="Ateneo Seal">
        <span>ADNU-PRINTS</span>
    </div>

    <button type="button" id="requestsMenuToggle"
        class="mobile-menu-toggle"
        aria-label="Open admin menu"
        aria-controls="requestsSidebar"
        aria-expanded="false">
        &#9776;
    </button>
</div>

<div class="mobile-menu-overlay" id="requestsMenuOverlay"></div>

<div class="col-md-2 sidebar p-0" id="requestsSidebar">

<button type="button" class="mobile-sidebar-close"
    id="requestsMenuClose" aria-label="Close admin menu">
    &times;
</button>

<div class="logo">
ADNU-PRINTS
</div>

<a href="dashboard.php">Dashboard</a>
<a href="requests.php">Manage Requests</a>
<a href="../dashboard.php">Student View</a>
<a href="../logout.php">Logout</a>

</div>

<div class="col-md-10 main">

<div class="theme-toolbar">
    <button type="button" id="requestsThemeToggle"
        class="theme-button" aria-pressed="false">
        <span id="requestsThemeIcon">&#9790;</span>
        <span id="requestsThemeText">Dark Mode</span>
    </button>
</div>

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
<a href="download.php?id=<?php echo (int) $row['id']; ?>"
   class="btn btn-outline-primary btn-sm mt-2 mb-3">
    &#11015; Download File
</a>


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
&#8369;<?php echo $row['estimated_cost']; ?>
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

<script>
document.addEventListener("DOMContentLoaded", function () {

    const sidebar = document.getElementById("requestsSidebar");
    const toggle = document.getElementById("requestsMenuToggle");
    const closeButton = document.getElementById("requestsMenuClose");
    const overlay = document.getElementById("requestsMenuOverlay");

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

    const themeButton = document.getElementById("requestsThemeToggle");
    const themeText = document.getElementById("requestsThemeText");
    const themeIcon = document.getElementById("requestsThemeIcon");

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