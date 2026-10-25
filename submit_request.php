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
                        <br><a href="queue.php">View Queue</a>
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

</head>

<body>

<div class="container-fluid">

<div class="row">

<div class="col-md-2 sidebar p-4">

<div class="logo mb-5">
ADNU-PRINTS
</div>

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

<h3 class="mb-4">
📋 Request Summary
</h3>

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
<strong id="pricePerPage">₱1</strong>
</div>

<h5 class="text-primary mt-3">
Estimated Cost
</h5>

<h2 class="fw-bold text-success">
₱<span id="estimatedCost">1</span>
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
        "₱" + price;

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
</body>
</html>