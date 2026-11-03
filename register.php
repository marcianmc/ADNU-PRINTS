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
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Register | ADNU-PRINTS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

:root {
    --primary: #2563eb;
    --primary-dark: #1d4ed8;
    --navy: #192b57;
    --background: #f4f7fc;
    --text: #172445;
    --muted: #64748b;
}

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    min-height: 100vh;
    background: var(--background);
    font-family: 'Segoe UI', Arial, sans-serif;
    color: var(--text);
}

/* HEADER */

.site-header {
    height: 68px;
    background: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 6%;
    border-bottom: 1px solid #e8edf5;
}

.brand {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    color: var(--navy);
    font-weight: 800;
    font-size: 17px;
    letter-spacing: .3px;
}

.university-logo {
    width: 43px;
    height: 43px;
    object-fit: contain;
    flex-shrink: 0;
}

.header-caption {
    color: #94a3b8;
    font-size: 12px;
}

/* MAIN LAYOUT */

.register-layout {
    min-height: calc(100vh - 68px);
    display: grid;
    grid-template-columns: 1fr 1fr;
}

/* LEFT CAMPUS PANEL */

.welcome-panel {
    background:
        linear-gradient(
            110deg,
            rgba(12, 43, 104, .78),
            rgba(37, 99, 235, .35)
        ),
        url('assets/images/four-pillars.jpg');

    background-size: cover;
    background-position: center bottom;
    background-repeat: no-repeat;

    padding: 65px 9%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.welcome-content {
    position: relative;
    z-index: 1;
}

.welcome-label {
    display: inline-block;
    color: #143b80;
    background: rgba(255,255,255,.92);
    padding: 9px 15px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 700;
    margin-bottom: 26px;
}

.welcome-panel h1 {
    font-size: clamp(34px, 4vw, 54px);
    line-height: 1.15;
    letter-spacing: -1.5px;
    color: white;
    font-weight: 800;
    margin-bottom: 23px;
    text-shadow: 0 3px 12px rgba(0,0,0,.2);
}

.welcome-panel p {
    color: white;
    font-size: 16px;
    max-width: 410px;
    line-height: 1.8;
    text-shadow: 0 1px 6px rgba(0,0,0,.2);
}

.features {
    margin-top: 30px;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.feature {
    display: flex;
    align-items: center;
    gap: 12px;
    color: white;
    font-size: 14px;
}

.feature-icon {
    width: 34px;
    height: 34px;
    background: rgba(255,255,255,.2);
    border: 1px solid rgba(255,255,255,.25);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}

/* RIGHT FORM PANEL */

.form-panel {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 45px 25px;
}

.register-card {
    width: 100%;
    max-width: 510px;
    background: white;
    padding: 40px;
    border-radius: 18px;
    box-shadow: 0 12px 45px rgba(23,43,85,.05);
}

.register-card h2 {
    font-size: 27px;
    font-weight: 800;
    color: var(--navy);
    margin-bottom: 7px;
}

.subtitle {
    font-size: 14px;
    color: var(--muted);
    margin-bottom: 28px;
}

.form-label {
    font-size: 12px;
    font-weight: 700;
    color: #253453;
    margin-bottom: 8px;
}

.form-control,
.form-select {
    min-height: 47px;
    background-color: #f7f9fc;
    border: 1px solid #edf1f6;
    border-radius: 8px;
    padding: 11px 14px;
    font-size: 14px;
}

.form-control:focus,
.form-select:focus {
    background-color: white;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}

.btn-register {
    width: 100%;
    min-height: 49px;
    background: var(--primary);
    color: white;
    font-weight: 700;
    border: none;
    border-radius: 8px;
    margin-top: 8px;
}

.btn-register:hover {
    background: var(--primary-dark);
    color: white;
}

.login-section {
    text-align: center;
    font-size: 13px;
    color: var(--muted);
    margin-top: 25px;
}

.login-section a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 700;
}

.info-box {
    margin-top: 22px;
    background: #edf4ff;
    padding: 14px;
    border-radius: 9px;
    color: #51709d;
    font-size: 12px;
    text-align: center;
    line-height: 1.6;
}

/* MOBILE RESPONSIVENESS */

@media (max-width: 768px) {

    .site-header {
        height: 62px;
        padding: 0 20px;
    }

    .brand {
        font-size: 15px;
    }

    .university-logo {
        width: 36px;
        height: 36px;
    }

    .header-caption {
        display: none;
    }

    .register-layout {
        display: flex;
        flex-direction: column;
        min-height: calc(100vh - 62px);
    }

    .welcome-panel {
        padding: 30px 24px 35px;

        background:
            linear-gradient(
                100deg,
                rgba(12,43,104,.84),
                rgba(37,99,235,.48)
            ),
            url('assets/images/four-pillars.jpg');

        background-size: cover;
        background-position: center 35%;
    }

    .welcome-label {
        margin-bottom: 16px;
        font-size: 10px;
        padding: 7px 12px;
    }

    .welcome-panel h1 {
        font-size: 29px;
        letter-spacing: -.5px;
        margin-bottom: 12px;
    }

    .welcome-panel p {
        font-size: 13px;
        line-height: 1.6;
        margin-bottom: 0;
    }

    .features {
        display: none;
    }

    .form-panel {
        flex: 1;
        padding: 23px 16px 35px;
    }

    .register-card {
        max-width: 480px;
        padding: 27px 20px;
        border-radius: 14px;
        box-shadow: 0 5px 25px rgba(23,43,85,.04);
    }

    .register-card h2 {
        font-size: 23px;
    }

    .subtitle {
        font-size: 13px;
        margin-bottom: 23px;
    }

    .form-control,
    .form-select {
        min-height: 50px;
        font-size: 16px;
    }

    .btn-register {
        min-height: 50px;
    }
}

@media (max-width: 360px) {

    .welcome-panel {
        padding: 22px 18px;
    }

    .welcome-panel h1 {
        font-size: 25px;
    }

    .register-card {
        padding: 23px 16px;
    }
}

</style>
</head>

<body>

<header class="site-header">

    <a href="login.php" class="brand">

        <img
            src="assets/images/ateneo-logo.png"
            alt="Ateneo de Naga University Seal"
            class="university-logo"
        >

        <span>ADNU-PRINTS</span>

    </a>

    <span class="header-caption">
        Campus printing made simple
    </span>

</header>

<main class="register-layout">

    <section class="welcome-panel">

        <div class="welcome-content">

            <div class="welcome-label">
                ATENEO DE NAGA UNIVERSITY
            </div>

            <h1>
                JOIN CAMPUS<br>
                PRINTS TODAY.
            </h1>

            <p>
                Create your account and enjoy a simpler way
                to submit, manage, and track your campus
                printing requests.
            </p>

            <div class="features">

                <div class="feature">
                    <span class="feature-icon">01</span>
                    Register your account
                </div>

                <div class="feature">
                    <span class="feature-icon">02</span>
                    Submit your documents
                </div>

                <div class="feature">
                    <span class="feature-icon">03</span>
                    Track your printing requests
                </div>

            </div>

        </div>

    </section>

    <section class="form-panel">

        <div class="register-card">

            <h2>Create Account</h2>

            <p class="subtitle">
                Join ADNU-PRINTS and start printing smarter.
            </p>

            <?php echo $message; ?>

            <form method="POST">

                <div class="mb-3">

                    <label for="fullname" class="form-label">
                        Full Name
                    </label>

                    <input
                        type="text"
                        id="fullname"
                        name="fullname"
                        class="form-control"
                        placeholder="Enter your full name"
                        autocomplete="name"
                        required
                    >

                </div>

                <div class="mb-3">

                    <label for="student_id" class="form-label">
                        Student / Employee ID
                    </label>

                    <input
                        type="text"
                        id="student_id"
                        name="student_id"
                        class="form-control"
                        placeholder="Enter your ID number"
                        required
                    >

                </div>

                <div class="mb-3">

                    <label for="email" class="form-label">
                        Email Address
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        placeholder="Enter your school email"
                        autocomplete="email"
                        required
                    >

                </div>

                <div class="mb-3">

                    <label for="role" class="form-label">
                        Account Type
                    </label>

                    <select
                        id="role"
                        name="role"
                        class="form-select"
                        required
                    >

                        <option value="student">
                            Student
                        </option>

                        <option value="faculty">
                            Faculty
                        </option>

                    </select>

                </div>

                <div class="mb-4">

                    <label for="password" class="form-label">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="Create your password"
                        autocomplete="new-password"
                        required
                    >

                </div>

                <button
                    type="submit"
                    name="register"
                    class="btn btn-register"
                >
                    Create Account
                </button>

            </form>

            <div class="login-section">
                Already have an account?
                <a href="login.php">Log in here</a>
            </div>

            <div class="info-box">
                Registration is available for students and faculty.
            </div>

        </div>

    </section>

</main>

</body>
</html>