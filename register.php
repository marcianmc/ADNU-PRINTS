```php
<?php
include 'config/database.php';

$message = "";

if(isset($_POST['register'])){

    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $requested_role = $_POST['role'] ?? 'student';
$role = in_array($requested_role, ['student', 'faculty'], true)
    ? $requested_role
    : 'student';

    $password = password_hash(
        $_POST['password'],
        PASSWORD_DEFAULT
    );

    $check = mysqli_query(
        $conn,
        "SELECT * FROM users WHERE email='$email'"
    );

    if(mysqli_num_rows($check) > 0){

        $message = "
        <div class='alert alert-danger'>
            Email already exists!
        </div>";

    }else{

        $sql = "
        INSERT INTO users
        (
            fullname,
            student_id,
            email,
            password,
            role
        )
        VALUES
        (
            '$fullname',
            '$student_id',
            '$email',
            '$password',
            '$role'
        )
        ";

        if(mysqli_query($conn,$sql)){

            $message = "
            <div class='alert alert-success'>
                Registration Successful!
            </div>";

        }else{

            $message = "
            <div class='alert alert-danger'>
                Registration Failed!
            </div>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<title>ADNU-PRINTS Register</title>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#0f2d7a;
}

.register-card{
    border:none;
    border-radius:20px;
}

.logo{
    color:#0f2d7a;
    font-weight:bold;
    font-size:32px;
}

.btn-adnu{
    background:#0f2d7a;
    color:white;
}

.btn-adnu:hover{
    background:#091d4f;
    color:white;
}

</style>

</head>

<body>

<div class="container">

<div class="row vh-100 justify-content-center align-items-center">

<div class="col-md-5">

<div class="card register-card shadow-lg">

<div class="card-body p-5">

<h2 class="text-center logo">
ADNU-PRINTS
</h2>

<p class="text-center text-muted">
Create your account
</p>

<?php echo $message; ?>

<form method="POST">

<div class="mb-3">

<label>Full Name</label>

<input
type="text"
name="fullname"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Student / Employee ID</label>

<input
type="text"
name="student_id"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Email</label>

<input
type="email"
name="email"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Role</label>

<select
name="role"
class="form-select"
required>

<option value="student">
Student
</option>

<option value="faculty">
Faculty
</option>

</select>

</div>

<div class="mb-3">

<label>Password</label>

<input
type="password"
name="password"
class="form-control"
required>

</div>

<button
type="submit"
name="register"
class="btn btn-adnu w-100">

Create Account

</button>

</form>

<hr>

<div class="text-center">

<a href="login.php">
Already have an account? Login
</a>

</div>

</div>

</div>

</div>

</div>

</div>

</body>
</html>
```
