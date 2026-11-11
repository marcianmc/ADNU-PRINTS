<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config/database.php';

$message = "";

if (isset($_POST['submit_request'])) {

    $user_id = (int) $_SESSION['user_id'];
    $copies = filter_var($_POST['copies'] ?? null, FILTER_VALIDATE_INT);
    $pages = filter_var($_POST['pages'] ?? null, FILTER_VALIDATE_INT);

    $paper_size = $_POST['paper_size'] ?? '';
    $color_mode = $_POST['color_mode'] ?? '';
    $print_type = $_POST['print_type'] ?? '';
    $orientation = $_POST['orientation'] ?? '';

    $error = "";

    if (!$copies || !$pages || $copies < 1 || $pages < 1 || $copies > 100 || $pages > 1000) {
        $error = "Please enter valid copies (1-100) and pages (1-1000).";
    }

    if (
        !in_array($paper_size, ['A4', 'Letter', 'Legal'], true) ||
        !in_array($color_mode, ['Black & White', 'Colored'], true) ||
        !in_array($print_type, ['Single Sided', 'Double Sided'], true) ||
        !in_array($orientation, ['Portrait', 'Landscape'], true)
    ) {
        $error = "Invalid printing options.";
    }

    if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
        $error = "Please select a valid document to upload.";
    }

    if (!$error) {
        $file = $_FILES['document'];
        $filename = basename(str_replace('\\', '/', $file['name']));
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        $max_size = 10 * 1024 * 1024;

        if (!in_array($extension, $allowed, true)) {
            $error = "Only PDF, DOC, DOCX, JPG, JPEG, and PNG files are allowed.";
        } elseif ($file['size'] <= 0 || $file['size'] > $max_size) {
            $error = "The file must be smaller than 10 MB.";
        } elseif (!is_uploaded_file($file['tmp_name'])) {
            $error = "Invalid document upload.";
        }
    }

    if (!$error) {
        $estimated_cost = $pages * $copies *
            ($color_mode === 'Colored' ? 5 : 1);

        $upload_dir = __DIR__ . "/uploads/";

        if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true)) {
            $error = "Upload folder could not be created.";
        }

        if (!$error) {
            $new_filename = bin2hex(random_bytes(16)) . "." . $extension;
            $file_path = $upload_dir . $new_filename;

            if (!move_uploaded_file($file['tmp_name'], $file_path)) {
                $error = "Could not save your document. Please try again.";
            } else {
                try {
                    $queue_result = mysqli_query(
                        $conn,
                        "SELECT COALESCE(MAX(queue_number), 0) + 1 AS next_queue FROM print_requests"
                    );

                    if (!$queue_result) {
                        throw new Exception("Queue query failed");
                    }

                    $queue_data = mysqli_fetch_assoc($queue_result);
                    $queue_number = (int) $queue_data['next_queue'];

                    $sql = "INSERT INTO print_requests
                        (user_id, filename, file_path, copies,
                         paper_size, color_mode, pages, print_type,
                         orientation, queue_number, estimated_cost)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                    $stmt = mysqli_prepare($conn, $sql);

                    if (!$stmt) {
                        throw new Exception("Prepare failed");
                    }

                    mysqli_stmt_bind_param(
                        $stmt,
                        "issississid",
                        $user_id,
                        $filename,
                        $file_path,
                        $copies,
                        $paper_size,
                        $color_mode,
                        $pages,
                        $print_type,
                        $orientation,
                        $queue_number,
                        $estimated_cost
                    );

                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception("Insert failed");
                    }

                    mysqli_stmt_close($stmt);

                    $message = '<div class="alert alert-success">
                        Request Submitted Successfully!<br>
                        Queue Number: <strong>' . $queue_number . '</strong><br>
                        Estimated Cost: <strong>&#8369;' .
                        number_format($estimated_cost, 2) . '</strong>
                        <br><a href="queue.php">Queue Monitor</a>
                        </div>';

                } catch (Throwable $e) {
                    error_log("Print request failed: " . $e->getMessage());

                    if (is_file($file_path)) {
                        unlink($file_path);
                    }

                    $error = "Could not save your request. Please try again.";
                }
            }
        }
    }

    if ($error) {
        $message = '<div class="alert alert-danger">' .
            htmlspecialchars($error, ENT_QUOTES, 'UTF-8') .
            '</div>';
    }
}
?>
<!DOCTYPE html>
<html>
<head>

<title>Submit Request</title>

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

.logo{
    font-size:32px;
    font-weight:bold;
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

.card-custom{
    border:none;
    border-radius:20px;
    box-shadow:0 5px 15px rgba(0,0,0,.08);
}

.upload-box{
    border:2px dashed #d5dbe8;
    border-radius:15px;
    padding:50px;
    text-align:center;
    background:white;
}

.summary-box{
    border:none;
    border-radius:20px;
    box-shadow:0 5px 15px rgba(0,0,0,.08);
}

.summary-item{
    display:flex;
    justify-content:space-between;
    margin-bottom:12px;
}

.btn-submit{
    width:100%;
    background:#0f2d7a;
    border:none;
    padding:12px;
    font-weight:bold;
}

.btn-submit:hover{
    background:#0a225d;
}

</style>

<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="mobile.css?v=1">

<style>
/* ADNU SUBMIT REDESIGN V1 */

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
    color: white;
    font-size: 21px;
    font-weight: 800;
    margin-bottom: 30px !important;
}

.sidebar a {
    color: #e8eeff;
    padding: 14px 15px;
    font-size: 13px;
    font-weight: 600;
    border-radius: 9px;
    margin-bottom: 6px;
}

.sidebar a:hover,
.sidebar a[href="submit_request.php"] {
    background: #30477e;
    color: white;
}

.main {
    padding: 35px 40px;
}

.main > h2 {
    color: #192b57;
    font-size: 27px;
    font-weight: 800;
}

.main > p {
    font-size: 14px;
    color: #64748b;
}

.card-custom,
.summary-box {
    background: white;
    border: 1px solid #edf0f6;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(25,43,87,.05);
}

.card-custom h3,
.summary-box h3 {
    color: #192b57;
    font-size: 19px;
    font-weight: 750;
}

.upload-box {
    background: #f8faff;
    border: 2px dashed #b8cae7;
    border-radius: 12px;
    padding: 35px 20px;
}

.form-control,
.form-select {
    min-height: 48px;
    background-color: #f7f9fc;
    border: 1px solid #e4eaf4;
    border-radius: 8px;
    font-size: 14px;
}

.form-control:focus,
.form-select:focus {
    background-color: white;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}

label {
    color: #253453;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 8px;
}

.summary-item {
    gap: 12px;
    color: #64748b;
    font-size: 13px;
}

.summary-item strong {
    color: #192b57;
    text-align: right;
}

.btn-submit {
    background: #2563eb;
    min-height: 49px;
    border-radius: 9px;
}

.btn-submit:hover {
    background: #1d4ed8;
}

.theme-toolbar {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 20px;
}

.theme-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    min-height: 43px;
    padding: 10px 16px;
    background: white;
    border: 1px solid #dce5f2;
    border-radius: 10px;
    color: #192b57;
    font-weight: 700;
    font-size: 13px;
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

body.adnu-dark .main > h2,
body.adnu-dark .card-custom h3,
body.adnu-dark .summary-box h3 {
    color: #f1f5f9 !important;
}

body.adnu-dark .main > p,
body.adnu-dark .text-muted {
    color: #a8b5ca !important;
}

body.adnu-dark .card-custom,
body.adnu-dark .summary-box {
    background: #1e293b !important;
    border-color: #334155 !important;
    color: #f1f5f9;
    box-shadow: none;
}

body.adnu-dark .upload-box {
    background: #26364d !important;
    border-color: #475569;
}

body.adnu-dark label,
body.adnu-dark .summary-item strong {
    color: #f1f5f9 !important;
}

body.adnu-dark .summary-item {
    color: #a8b5ca;
}

body.adnu-dark .form-control,
body.adnu-dark .form-select {
    background-color: #26364d !important;
    border-color: #475569 !important;
    color: #f1f5f9 !important;
}

body.adnu-dark .form-control::placeholder {
    color: #94a3b8;
}

body.adnu-dark .theme-button {
    background: #26364d;
    border-color: #475569;
    color: #f1f5f9;
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

    #submitSidebar.sidebar {
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

    #submitSidebar.mobile-sidebar-open {
        transform: translateX(0);
    }

    #submitSidebar .logo {
        display: block !important;
        font-size: 18px;
        margin-bottom: 20px !important;
    }

    #submitSidebar.sidebar a {
        display: block !important;
        flex: 0 0 auto !important;
        width: 100% !important;
        white-space: normal !important;
        padding: 14px !important;
        margin: 3px 0 !important;
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
        color: white;
        border: 1px solid rgba(255,255,255,.2);
        border-radius: 9px;
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
        padding: 22px 15px 35px !important;
    }

    .main > h2 {
        font-size: 23px !important;
    }

    .card-custom,
    .summary-box {
        padding: 20px !important;
    }

    .upload-box {
        padding: 22px 12px !important;
    }

    .form-control,
    .form-select {
        font-size: 16px !important;
    }

    .theme-toolbar {
        margin-bottom: 18px;
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
    #submitSidebar.sidebar,
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

    <button type="button" id="submitMenuToggle"
        class="mobile-menu-toggle"
        aria-label="Open navigation menu"
        aria-controls="submitSidebar"
        aria-expanded="false">
        &#9776;
    </button>
</div>

<div class="mobile-menu-overlay" id="submitMenuOverlay"></div>

<div class="col-md-2 sidebar p-4" id="submitSidebar">

<button type="button" class="mobile-sidebar-close"
    id="submitMenuClose" aria-label="Close navigation menu">
    &times;
</button>

<div class="logo mb-5">
ADNU-PRINTS
</div>

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
    <button type="button" id="submitThemeToggle"
        class="theme-button" aria-pressed="false">
        <span id="submitThemeIcon">&#9790;</span>
        <span id="submitThemeText">Dark Mode</span>
    </button>
</div>

<h2 class="fw-bold">
Submit Print Request
</h2>

<p class="text-muted">
Upload your document and configure your printing preferences.
</p>

<?php echo $message; ?>

<form method="POST" enctype="multipart/form-data">

<div class="row">

<div class="col-lg-8">

<div class="card card-custom p-4 mb-4">

<h3 class="mb-4">
Document Upload
</h3>

<div class="upload-box">

<input
type="file"
id="document"
name="document" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
class="form-control"
required>
</div>

</div>

<div class="card card-custom p-4">

<h3 class="mb-4">
Printing Options
</h3>

<div class="row">

<div class="col-md-6 mb-3">
<label>Copies</label>
<input
type="number"
id="copies"
name="copies" max="100"
value="1"
min="1"
class="form-control"
required>
</div>

<div class="col-md-6 mb-3">
<label>Pages</label>
<input
type="number"
id="pages"
name="pages" max="1000"
value="1"
min="1"
class="form-control"
required>
</div>

<div class="col-md-6 mb-3">
<label>Paper Size</label>
<select
id="paper_size"
name="paper_size"
class="form-select">
<option>A4</option>
<option>Letter</option>
<option>Legal</option>
</select>
</div>

<div class="col-md-6 mb-3">
<label>Color Mode</label>
<select
id="color_mode"
name="color_mode"
class="form-select">
<option>Black & White</option>
<option>Colored</option>
</select>
</div>

<div class="col-md-6 mb-3">
<label>Print Type</label>
<select
id="print_type"
name="print_type"
class="form-select">
<option>Single Sided</option>
<option>Double Sided</option>
</select>
</div>

<div class="col-md-6 mb-3">
<label>Orientation</label>
<select
id="orientation"
name="orientation"
class="form-select">
<option>Portrait</option>
<option>Landscape</option>
</select>
</div>

</div>

</div>

</div>

<div class="col-lg-4">

<div class="card summary-box p-4">

<h3 class="mb-4">Request Summary</h3>

<div class="summary-item">
<span>Copies</span>
<strong id="summaryCopies">1</strong>
</div>

<div class="summary-item">
<span>Pages</span>
<strong id="summaryPages">1</strong>
</div>

<div class="summary-item">
<span>Paper Size</span>
<strong id="summaryPaper">A4</strong>
</div>

<div class="summary-item">
<span>Color Mode</span>
<strong id="summaryColor">Black & White</strong>
</div>

<div class="summary-item">
<span>Orientation</span>
<strong id="summaryOrientation">Portrait</strong>
</div>

<div class="summary-item">
<span>Print Type</span>
<strong id="summaryPrintType">Single Sided</strong>
</div>

<hr>

<div class="summary-item">
<span>Price Per Page</span>
<strong id="pricePerPage">&#8369;1</strong>
</div>

<h5 class="text-primary mt-3">
Estimated Cost
</h5>

<h2 class="fw-bold text-success">
&#8369;<span id="estimatedCost">1</span>
</h2>

<small class="text-muted">
Updates automatically
</small>

<button
type="submit"
name="submit_request"
class="btn btn-submit text-white mt-4">

Submit Request

</button>

</div>

</div>

</div>

</div>

</form>

</div>

</div>

</div>
<script>

function updateSummary(){

    let copies =
        parseInt(document.getElementById("copies").value) || 1;

    let pages =
        parseInt(document.getElementById("pages").value) || 1;

    let paper =
        document.getElementById("paper_size").value;

    let color =
        document.getElementById("color_mode").value;

    let orientation =
        document.getElementById("orientation").value;

    let printType =
        document.getElementById("print_type").value;

    let price = color === "Colored" ? 5 : 1;

    let cost = copies * pages * price;

    document.getElementById("summaryCopies").innerText = copies;
    document.getElementById("summaryPages").innerText = pages;
    document.getElementById("summaryPaper").innerText = paper;
    document.getElementById("summaryColor").innerText = color;
    document.getElementById("summaryOrientation").innerText = orientation;
    document.getElementById("summaryPrintType").innerText = printType;

    document.getElementById("pricePerPage").innerText =
        "\u20B1" + price;

    document.getElementById("estimatedCost").innerText =
        cost.toFixed(2);
}

document.querySelectorAll(
'input, select'
).forEach(function(element){

    element.addEventListener(
        'input',
        updateSummary
    );

    element.addEventListener(
        'change',
        updateSummary
    );

});

updateSummary();

</script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const sidebar = document.getElementById("submitSidebar");
    const menuButton = document.getElementById("submitMenuToggle");
    const closeButton = document.getElementById("submitMenuClose");
    const overlay = document.getElementById("submitMenuOverlay");

    function openMenu() {
        sidebar.classList.add("mobile-sidebar-open");
        overlay.classList.add("is-visible");
        document.body.classList.add("mobile-menu-active");
        menuButton.setAttribute("aria-expanded", "true");
        closeButton.focus();
    }

    function closeMenu() {
        sidebar.classList.remove("mobile-sidebar-open");
        overlay.classList.remove("is-visible");
        document.body.classList.remove("mobile-menu-active");
        menuButton.setAttribute("aria-expanded", "false");
        menuButton.focus();
    }

    if (sidebar && menuButton && closeButton && overlay) {
        menuButton.addEventListener("click", openMenu);
        closeButton.addEventListener("click", closeMenu);
        overlay.addEventListener("click", closeMenu);

        document.addEventListener("keydown", function (e) {
            if (e.key === "Escape" &&
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

    const themeButton = document.getElementById("submitThemeToggle");
    const themeText = document.getElementById("submitThemeText");
    const themeIcon = document.getElementById("submitThemeIcon");

    const savedTheme = localStorage.getItem("adnu-prints-theme");
const savedDark = savedTheme === "dark";

document.body.classList.toggle("adnu-dark", savedDark);

    if (themeButton && themeText && themeIcon) {
        themeText.textContent = savedDark ? "Light Mode" : "Dark Mode";
themeIcon.innerHTML = savedDark ? "&#9728;" : "&#9790;";
themeButton.setAttribute("aria-pressed", String(savedDark));
themeButton.addEventListener("click", function () {
            const dark = document.body.classList.toggle("adnu-dark");
localStorage.setItem("adnu-prints-theme", dark ? "dark" : "light");

            themeText.textContent = dark ? "Light Mode" : "Dark Mode";
            themeIcon.innerHTML = dark ? "&#9728;" : "&#9790;";
            themeButton.setAttribute("aria-pressed", String(dark));
        });
    }
});
</script>
</body>
</html>