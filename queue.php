<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

include 'config/database.php';

$user_id = $_SESSION['user_id'];

$studentId = (int) $_SESSION['user_id'];

$selectedRequestId = filter_input(
    INPUT_GET,
    'request_id',
    FILTER_VALIDATE_INT
);

$selectedRequestId = (
    $selectedRequestId && $selectedRequestId > 0
) ? (int) $selectedRequestId : 0;

$request = null;

// Try the student's selected active request first.
if ($selectedRequestId > 0) {
    $stmt = mysqli_prepare(
        $conn,
        "SELECT *
         FROM print_requests
         WHERE id = ?
           AND user_id = ?
           AND status != 'Completed'
         LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $selectedRequestId,
        $studentId
    );

    mysqli_stmt_execute($stmt);
    $request = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
}

// If nothing was selected, or it was completed,
// display the student's latest active request.
if (!$request) {
    $stmt = mysqli_prepare(
        $conn,
        "SELECT *
         FROM print_requests
         WHERE user_id = ?
           AND status != 'Completed'
         ORDER BY queue_number DESC
         LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "i", $studentId);
    mysqli_stmt_execute($stmt);
    $request = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
}

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

<style>
/* ADNU QUEUE REDESIGN V1 */

body {
    background: #f4f7fc !important;
    color: #172445;
    font-family: 'Segoe UI', Arial, sans-serif;
}

.sidebar {
    background: #192b57 !important;
}

.sidebar h2 {
    font-size: 21px;
    color: white;
}

.sidebar a {
    color: #e8eeff;
    font-size: 13px;
    font-weight: 600;
    padding: 14px 15px;
    margin-bottom: 6px;
    border-radius: 9px;
}

.sidebar a:hover,
.sidebar a[href="queue.php"] {
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

.queue-banner {
    background: linear-gradient(120deg, #192b57, #2563eb);
    border-radius: 16px;
    padding: 35px;
}

.queue-banner .big-number {
    font-size: clamp(48px, 6vw, 85px);
    font-weight: 800;
}

.stat-card {
    border: 1px solid rgba(255,255,255,.18);
    background: rgba(255,255,255,.13);
    border-radius: 12px;
    padding: 18px;
}

.stat-card h2 {
    font-weight: 800;
}

.card-box,
.empty-card {
    background: white;
    border: 1px solid #edf0f6;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(25,43,87,.05);
}

.card-box .card-body {
    padding: 25px;
}

.card-box h4 {
    color: #192b57;
    font-size: 19px;
    font-weight: 750;
}

.progress {
    background: #e6edf8;
    border-radius: 8px;
    overflow: hidden;
}

.progress-bar {
    background: #2563eb;
    font-weight: 700;
}

.table {
    margin-bottom: 0;
}

.table th {
    color: #64748b;
    font-size: 12px;
    font-weight: 700;
}

.table td {
    font-size: 13px;
    vertical-align: middle;
    overflow-wrap: anywhere;
}

.empty-card {
    padding: 55px 30px;
}

.empty-icon {
    display: inline-block;
    padding: 12px 20px;
    background: #edf4ff;
    color: #2563eb;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
}

.empty-card h2 {
    color: #192b57;
    font-weight: 800;
}

.empty-card p {
    max-width: 480px;
    margin-left: auto;
    margin-right: auto;
}

.empty-card .btn {
    background: #2563eb;
    border: none;
    border-radius: 9px;
    padding: 13px 22px;
    font-size: 14px;
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

body.adnu-dark .main > h1,
body.adnu-dark .card-box h4,
body.adnu-dark .empty-card h2 {
    color: #f1f5f9 !important;
}

body.adnu-dark .main > p,
body.adnu-dark .text-muted {
    color: #a8b5ca !important;
}

body.adnu-dark .card-box,
body.adnu-dark .empty-card {
    background: #1e293b !important;
    color: #f1f5f9;
    border-color: #334155 !important;
    box-shadow: none;
}

body.adnu-dark .card-box .table,
body.adnu-dark .card-box .table td,
body.adnu-dark .card-box .table th {
    background: #1e293b !important;
    color: #f1f5f9 !important;
    border-color: #334155 !important;
}

body.adnu-dark .empty-icon {
    background: #263b5d;
    color: #bfdbfe;
}

body.adnu-dark .theme-button {
    background: #26364d;
    border-color: #475569;
    color: #f1f5f9;
}

body.adnu-dark .progress {
    background: #334155;
}

body.adnu-dark .queue-banner {
    background: linear-gradient(120deg, #172554, #1d4ed8);
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

    #queueSidebar.sidebar {
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

    #queueSidebar.mobile-sidebar-open {
        transform: translateX(0);
    }

    #queueSidebar.sidebar h2 {
        display: block !important;
        font-size: 18px;
        margin-bottom: 25px !important;
    }

    #queueSidebar.sidebar a {
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

    .queue-banner {
        padding: 23px !important;
    }

    .queue-banner .big-number {
        font-size: 55px !important;
    }

    .stat-card {
        padding: 15px;
    }

    .card-box .card-body {
        padding: 18px !important;
    }

    .empty-card {
        padding: 35px 20px !important;
    }

    .table {
        font-size: 12px;
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
    #queueSidebar.sidebar,
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

    <button type="button" class="mobile-menu-toggle"
        id="queueMenuToggle"
        aria-label="Open navigation menu"
        aria-controls="queueSidebar"
        aria-expanded="false">
        &#9776;
    </button>
</div>

<div class="mobile-menu-overlay" id="queueMenuOverlay"></div>

<div class="col-md-2 sidebar p-4" id="queueSidebar">

<button type="button" class="mobile-sidebar-close"
    id="queueMenuClose" aria-label="Close navigation menu">
    &times;
</button>

<h2 class="fw-bold mb-5">
ADNU-PRINTS
</h2>

<a href="dashboard.php">Dashboard</a>
<a href="submit_request.php">Submit Request</a>
<a href="queue.php">Queue Monitor</a>
<a href="history.php">Transaction History</a>
<a href="logout.php">Logout</a>

</div>

<div class="col-md-10 main">

<div class="theme-toolbar">
    <button type="button" id="queueThemeToggle"
        class="theme-button" aria-pressed="false">
        <span id="queueThemeIcon">&#9790;</span>
        <span id="queueThemeText">Dark Mode</span>
    </button>
</div>

<h1 class="fw-bold">
Queue Monitor
</h1>

<p class="text-muted">
Track your print request in real time.
</p>

<div class="empty-card mt-4">

<div class="empty-icon">No requests yet</div>

<h2 class="mt-4">
No Active Print Requests
</h2>

<p class="text-muted mt-3">
You don't have any active printing requests.
Completed requests can be viewed in Transaction History. Submit a new request to join the queue.
</p>

<a href="submit_request.php"
class="btn btn-primary btn-lg mt-3">

Create Your First Request

</a>

</div>

</div>

</div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const sidebar = document.getElementById("queueSidebar");
    const toggle = document.getElementById("queueMenuToggle");
    const closeButton = document.getElementById("queueMenuClose");
    const overlay = document.getElementById("queueMenuOverlay");

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

    const themeButton = document.getElementById("queueThemeToggle");
    const themeText = document.getElementById("queueThemeText");
    const themeIcon = document.getElementById("queueThemeIcon");

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
(function () {
    // Refresh queue information every 15 seconds.
    setInterval(function () {
        if (document.visibilityState === "visible") {
            window.location.reload();
        }
    }, 15000);
})();
</script>
</body>
</html>
<?php
exit();
}
$queue_number = $request['queue_number'];

$queue_number = (int) $queue_number;

$waitingStatuses = "'Pending','In Progress','Printing'";

$aheadResult = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM print_requests
     WHERE queue_number < $queue_number
     AND status IN ($waitingStatuses)"
);

$aheadData = mysqli_fetch_assoc($aheadResult);
$ahead = (int) ($aheadData['total'] ?? 0);

$totalResult = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM print_requests
     WHERE status IN ($waitingStatuses)"
);

$totalData = mysqli_fetch_assoc($totalResult);
$total_queue = (int) ($totalData['total'] ?? 0);

$estimate = $ahead * 2;
$studentId = (int) $user_id;

$activeRequests = mysqli_query(
    $conn,
    "SELECT id, queue_number, filename, status
     FROM print_requests
     WHERE user_id = $studentId
       AND status != 'Completed'
     ORDER BY queue_number ASC"
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

<style>
/* ADNU QUEUE REDESIGN V1 */

body {
    background: #f4f7fc !important;
    color: #172445;
    font-family: 'Segoe UI', Arial, sans-serif;
}

.sidebar {
    background: #192b57 !important;
}

.sidebar h2 {
    font-size: 21px;
    color: white;
}

.sidebar a {
    color: #e8eeff;
    font-size: 13px;
    font-weight: 600;
    padding: 14px 15px;
    margin-bottom: 6px;
    border-radius: 9px;
}

.sidebar a:hover,
.sidebar a[href="queue.php"] {
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

.queue-banner {
    background: linear-gradient(120deg, #192b57, #2563eb);
    border-radius: 16px;
    padding: 35px;
}

.queue-banner .big-number {
    font-size: clamp(48px, 6vw, 85px);
    font-weight: 800;
}

.stat-card {
    border: 1px solid rgba(255,255,255,.18);
    background: rgba(255,255,255,.13);
    border-radius: 12px;
    padding: 18px;
}

.stat-card h2 {
    font-weight: 800;
}

.card-box,
.empty-card {
    background: white;
    border: 1px solid #edf0f6;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(25,43,87,.05);
}

.card-box .card-body {
    padding: 25px;
}

.card-box h4 {
    color: #192b57;
    font-size: 19px;
    font-weight: 750;
}

.progress {
    background: #e6edf8;
    border-radius: 8px;
    overflow: hidden;
}

.progress-bar {
    background: #2563eb;
    font-weight: 700;
}

.table {
    margin-bottom: 0;
}

.table th {
    color: #64748b;
    font-size: 12px;
    font-weight: 700;
}

.table td {
    font-size: 13px;
    vertical-align: middle;
    overflow-wrap: anywhere;
}

.empty-card {
    padding: 55px 30px;
}

.empty-icon {
    display: inline-block;
    padding: 12px 20px;
    background: #edf4ff;
    color: #2563eb;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
}

.empty-card h2 {
    color: #192b57;
    font-weight: 800;
}

.empty-card p {
    max-width: 480px;
    margin-left: auto;
    margin-right: auto;
}

.empty-card .btn {
    background: #2563eb;
    border: none;
    border-radius: 9px;
    padding: 13px 22px;
    font-size: 14px;
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

body.adnu-dark .main > h1,
body.adnu-dark .card-box h4,
body.adnu-dark .empty-card h2 {
    color: #f1f5f9 !important;
}

body.adnu-dark .main > p,
body.adnu-dark .text-muted {
    color: #a8b5ca !important;
}

body.adnu-dark .card-box,
body.adnu-dark .empty-card {
    background: #1e293b !important;
    color: #f1f5f9;
    border-color: #334155 !important;
    box-shadow: none;
}

body.adnu-dark .card-box .table,
body.adnu-dark .card-box .table td,
body.adnu-dark .card-box .table th {
    background: #1e293b !important;
    color: #f1f5f9 !important;
    border-color: #334155 !important;
}

body.adnu-dark .empty-icon {
    background: #263b5d;
    color: #bfdbfe;
}

body.adnu-dark .theme-button {
    background: #26364d;
    border-color: #475569;
    color: #f1f5f9;
}

body.adnu-dark .progress {
    background: #334155;
}

body.adnu-dark .queue-banner {
    background: linear-gradient(120deg, #172554, #1d4ed8);
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

    #queueSidebar.sidebar {
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

    #queueSidebar.mobile-sidebar-open {
        transform: translateX(0);
    }

    #queueSidebar.sidebar h2 {
        display: block !important;
        font-size: 18px;
        margin-bottom: 25px !important;
    }

    #queueSidebar.sidebar a {
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

    .queue-banner {
        padding: 23px !important;
    }

    .queue-banner .big-number {
        font-size: 55px !important;
    }

    .stat-card {
        padding: 15px;
    }

    .card-box .card-body {
        padding: 18px !important;
    }

    .empty-card {
        padding: 35px 20px !important;
    }

    .table {
        font-size: 12px;
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
    #queueSidebar.sidebar,
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

    <button type="button" class="mobile-menu-toggle"
        id="queueMenuToggle"
        aria-label="Open navigation menu"
        aria-controls="queueSidebar"
        aria-expanded="false">
        &#9776;
    </button>
</div>

<div class="mobile-menu-overlay" id="queueMenuOverlay"></div>

<div class="col-md-2 sidebar p-4" id="queueSidebar">

<button type="button" class="mobile-sidebar-close"
    id="queueMenuClose" aria-label="Close navigation menu">
    &times;
</button>

<h2 class="fw-bold mb-5">
ADNU-PRINTS
</h2>

<a href="dashboard.php">Dashboard</a>
<a href="submit_request.php">Submit Request</a>
<a href="queue.php">Queue Monitor</a>
<a href="history.php">Transaction History</a>
<a href="logout.php">Logout</a>

</div>

<div class="col-md-10 main">

<div class="theme-toolbar">
    <button type="button" id="queueThemeToggle"
        class="theme-button" aria-pressed="false">
        <span id="queueThemeIcon">&#9790;</span>
        <span id="queueThemeText">Dark Mode</span>
    </button>
</div>

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
<?php
if ($status === 'Ready for Pickup') {
    echo 'Ready to collect';
} elseif ($status === 'Printing') {
    echo 'Printing now';
} elseif ($status === 'In Progress') {
    echo 'Being prepared';
} else {
    echo '~' . $estimate . ' min';
}
?>
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

$progressByStatus = [
    'Pending' => 10,
    'In Progress' => 35,
    'Printing' => 65,
    'Ready for Pickup' => 90,
    'Completed' => 100
];

$progress = $progressByStatus[$status] ?? 0;

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

<h4>My Active Print Requests</h4>

<div class="table-responsive">
<table class="table align-middle">
<thead>
<tr>
    <th>Queue</th>
    <th>Document</th>
    <th>Status</th>
    <th>Progress</th>
</tr>
</thead>
<tbody>

<?php if (mysqli_num_rows($activeRequests) === 0): ?>
<tr>
    <td colspan="4" class="text-center py-4">
        No active requests.
    </td>
</tr>
<?php else: ?>

<?php while ($row = mysqli_fetch_assoc($activeRequests)): ?>
<?php
    $stages = [
        'Pending' => 10,
        'In Progress' => 35,
        'Printing' => 65,
        'Ready for Pickup' => 90
    ];

    $stage = $stages[$row['status']] ?? 0;

    $badge = [
        'Pending' => 'bg-warning text-dark',
        'In Progress' => 'bg-info text-dark',
        'Printing' => 'bg-primary',
        'Ready for Pickup' => 'bg-success'
    ];

    $badgeClass = $badge[$row['status']] ?? 'bg-secondary';
?>

<tr
    role="link"
    tabindex="0"
    data-href="queue.php?request_id=<?php echo (int) $row['id']; ?>"
    onclick="window.location.href = this.dataset.href"
    onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location.href = this.dataset.href; }"
    style="cursor: pointer; <?php if ((int) $row['id'] === (int) $request['id']) echo 'outline: 2px solid #2563eb; outline-offset: -2px;'; ?>"
>
    <td>
    <a href="queue.php?request_id=<?php echo (int) $row['id']; ?>"
       class="fw-bold text-primary text-decoration-none">
        #<?php echo (int) $row['queue_number']; ?>
    </a>
</td>

    <td>
        <?php echo htmlspecialchars(
            $row['filename'],
            ENT_QUOTES,
            'UTF-8'
        ); ?>
    </td>

    <td>
        <span class="badge <?php echo $badgeClass; ?>">
            <?php echo htmlspecialchars(
                $row['status'],
                ENT_QUOTES,
                'UTF-8'
            ); ?>
        </span>
    </td>

    <td><?php echo $stage; ?>%</td>
</tr>

<?php endwhile; ?>
<?php endif; ?>

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

    const sidebar = document.getElementById("queueSidebar");
    const toggle = document.getElementById("queueMenuToggle");
    const closeButton = document.getElementById("queueMenuClose");
    const overlay = document.getElementById("queueMenuOverlay");

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

    const themeButton = document.getElementById("queueThemeToggle");
    const themeText = document.getElementById("queueThemeText");
    const themeIcon = document.getElementById("queueThemeIcon");

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
(function () {
    // Refresh queue information every 15 seconds.
    setInterval(function () {
        if (document.visibilityState === "visible") {
            window.location.reload();
        }
    }, 15000);
})();
</script>
</body>
</html>
```
