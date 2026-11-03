<?php
session_start();
include 'config/database.php';

$message = "";

if(isset($_POST['login'])){

    $email = mysqli_real_escape_string(
        $conn,
        $_POST['email']
    );

    $password = $_POST['password'];

    $query = mysqli_query(
        $conn,
        "SELECT * FROM users
        WHERE email='$email'"
    );

    if(mysqli_num_rows($query) > 0){

        $user = mysqli_fetch_assoc($query);

        if(password_verify($password, $user['password'])){

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role'] = $user['role'];

            if($user['role'] == 'admin'){

                header("Location: admin/dashboard.php");
                exit();

            }else{

                header("Location: dashboard.php");
                exit();
            }

        }else{

            $message = "
            <div class='alert alert-danger'>
                Invalid Password
            </div>";
        }

    }else{

        $message = "
        <div class='alert alert-danger'>
            User Not Found
        </div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login | ADNU-PRINTS</title>

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

.brand-mark {
    width: 39px;
    height: 39px;
    border-radius: 50%;
    background: var(--navy);
    border: 3px solid #e7bc55;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 16px;
    font-weight: 800;
}

.header-caption {
    color: #94a3b8;
    font-size: 12px;
}

/* MAIN LAYOUT */

.login-layout {
    min-height: calc(100vh - 68px);
    display: grid;
    grid-template-columns: 1fr 1fr;
}

/* LEFT PANEL */

.welcome-panel {
    background: linear-gradient(145deg, #a7c9ed 0%, #76a7df 55%, #285bc2 100%);
    padding: 70px 9%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.welcome-panel::after {
    content: "";
    position: absolute;
    width: 340px;
    height: 340px;
    border-radius: 50%;
    border: 65px solid rgba(255,255,255,.13);
    right: -145px;
    bottom: -120px;
}

.welcome-content {
    position: relative;
    z-index: 1;
}

.welcome-label {
    display: inline-block;
    color: #173f88;
    background: rgba(255,255,255,.45);
    padding: 9px 15px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 700;
    margin-bottom: 26px;
}

.welcome-panel h1 {
    font-size: clamp(34px, 4vw, 56px);
    line-height: 1.13;
    letter-spacing: -1.5px;
    color: white;
    font-weight: 850;
    margin-bottom: 23px;
}

.welcome-panel p {
    color: #f1f6ff;
    font-size: 16px;
    max-width: 390px;
    line-height: 1.8;
}

.features {
    margin-top: 35px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.feature {
    display: flex;
    gap: 12px;
    align-items: center;
    color: white;
    font-size: 14px;
}

.feature-icon {
    width: 32px;
    height: 32px;
    background: rgba(255,255,255,.2);
    border: 1px solid rgba(255,255,255,.25);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}

/* RIGHT PANEL */

.form-panel {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 45px 25px;
}

.login-card {
    width: 100%;
    max-width: 440px;
    background: white;
    padding: 43px;
    border-radius: 18px;
    box-shadow: 0 12px 45px rgba(23,43,85,.05);
}

.login-card h2 {
    font-size: 27px;
    font-weight: 800;
    color: var(--navy);
    margin-bottom: 7px;
}

.subtitle {
    font-size: 14px;
    color: var(--muted);
    margin-bottom: 31px;
}

.form-label {
    font-size: 12px;
    font-weight: 700;
    color: #253453;
    margin-bottom: 9px;
}

.form-control {
    height: 49px;
    background: #f7f9fc;
    border: 1px solid #edf1f6;
    border-radius: 8px;
    padding: 12px 14px;
    font-size: 14px;
}

.form-control:focus {
    background: white;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}

.btn-login {
    width: 100%;
    min-height: 49px;
    background: var(--primary);
    color: white;
    font-weight: 700;
    border: none;
    border-radius: 8px;
    transition: background .2s;
}

.btn-login:hover {
    background: var(--primary-dark);
    color: white;
}

.register-section {
    text-align: center;
    font-size: 13px;
    color: var(--muted);
}

.register-section a {
    font-weight: 700;
    text-decoration: none;
    color: var(--primary);
}

.info-box {
    margin-top: 29px;
    background: #edf4ff;
    padding: 16px;
    border-radius: 9px;
    color: #51709d;
    font-size: 12px;
    text-align: center;
    line-height: 1.6;
}

.divider {
    text-align: center;
    position: relative;
    margin: 25px 0;
    color: #94a3b8;
    font-size: 12px;
}

.divider::before {
    content: "";
    height: 1px;
    background: #edf0f5;
    position: absolute;
    width: 100%;
    left: 0;
    top: 50%;
}

.divider span {
    background: white;
    position: relative;
    padding: 0 14px;
}

/* MOBILE */

@media(max-width: 768px) {

    .site-header {
        height: 62px;
        padding: 0 20px;
    }

    .brand {
        font-size: 15px;
    }

    .brand-mark {
        width: 34px;
        height: 34px;
    }

    .header-caption {
        display: none;
    }

    .login-layout {
        display: flex;
        flex-direction: column;
        min-height: calc(100vh - 62px);
    }

    .welcome-panel {
        padding: 30px 24px 35px;
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

    .welcome-label {
        margin-bottom: 16px;
        font-size: 10px;
        padding: 7px 12px;
    }

    .features {
        display: none;
    }

    .form-panel {
        flex: 1;
        display: block;
        padding: 23px 16px 35px;
    }

    .login-card {
        max-width: 480px;
        margin: 0 auto;
        padding: 26px 20px;
        border-radius: 14px;
        box-shadow: 0 5px 25px rgba(23,43,85,.04);
    }

    .login-card h2 {
        font-size: 23px;
    }

    .subtitle {
        font-size: 13px;
        margin-bottom: 23px;
    }

    .form-control {
        height: 50px;
        font-size: 16px;
    }

    .btn-login {
        min-height: 50px;
    }

    .info-box {
        margin-top: 22px;
    }
}

@media(max-width: 360px) {
    .welcome-panel {
        padding: 22px 18px;
    }

    .welcome-panel h1 {
        font-size: 25px;
    }

    .login-card {
        padding: 23px 16px;
    }
}

/* ATENEO BRANDING */

.university-logo {
    width: 43px;
    height: 43px;
    object-fit: contain;
    flex-shrink: 0;
}

/* FOUR PILLARS BACKGROUND */

.welcome-panel {
    background:
        linear-gradient(
            110deg,
            rgba(12, 43, 104, 0.78),
            rgba(37, 99, 235, 0.35)
        ),
        url('assets/images/four-pillars.jpg');

    background-size: cover;
    background-position: center center;
    background-repeat: no-repeat;
}

/* IMPROVE TEXT READABILITY */

.welcome-panel h1 {
    text-shadow: 0 3px 12px rgba(0, 0, 0, 0.15);
}

.welcome-panel p {
    color: #ffffff;
    text-shadow: 0 1px 6px rgba(0, 0, 0, 0.2);
}

.welcome-label {
    background: rgba(255, 255, 255, 0.9);
    color: #143b80;
}

.feature {
    text-shadow: 0 1px 5px rgba(0, 0, 0, 0.2);
}

.feature-icon {
    background: rgba(255, 255, 255, 0.18);
    backdrop-filter: blur(5px);
}

/* MOBILE BACKGROUND */

@media (max-width: 768px) {

    .university-logo {
        width: 36px;
        height: 36px;
    }

    .welcome-panel {
        background:
            linear-gradient(
                100deg,
                rgba(12, 43, 104, 0.84),
                rgba(37, 99, 235, 0.48)
            ),
            url('assets/images/four-pillars.jpg');

        background-size: cover;
        background-position: center 35%;
        background-repeat: no-repeat;
    }

    .welcome-panel h1 {
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }
}

/* CAMPUS PHOTO POSITION REFINEMENT */
@media (min-width: 769px) {
    .welcome-panel {
        background-position: center bottom;
    }
}
</style>
</head>

<body>

<header class="site-header">
    <a href="login.php" class="brand">
        <img src="assets/images/ateneo-logo.png" alt="Ateneo de Naga University Seal" class="university-logo">
        <span>ADNU-PRINTS</span>
    </a>

    <span class="header-caption">Campus printing made simple</span>
</header>

<main class="login-layout">

    <section class="welcome-panel">
        <div class="welcome-content">

            <div class="welcome-label">
                ATENEO DE NAGA UNIVERSITY
            </div>

            <h1>
                PRINT SMARTER,<br>
                STUDY BETTER.
            </h1>

            <p>
                Your campus printing companion.
                Submit print requests, customize your prints,
                and track your orders in one convenient place.
            </p>

            <div class="features">
                <div class="feature">
                    <span class="feature-icon">01</span>
                    Upload documents easily
                </div>

                <div class="feature">
                    <span class="feature-icon">02</span>
                    Customize your printing preferences
                </div>

                <div class="feature">
                    <span class="feature-icon">03</span>
                    Track your printing requests
                </div>
            </div>

        </div>
    </section>

    <section class="form-panel">

        <div class="login-card">

            <h2>Welcome Back!</h2>

            <p class="subtitle">
                Log in to your Campus Prints account.
            </p>

            <?php echo $message; ?>

            <form method="POST">

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
                        autocomplete="username"
                        required
                    >

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
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >

                </div>

                <button
                    type="submit"
                    name="login"
                    class="btn btn-login"
                >
                    Log In
                </button>

            </form>

            <div class="divider">
                <span>OR</span>
            </div>

            <div class="register-section">
                Don't have an account?
                <a href="register.php">Register here</a>
            </div>

            <div class="info-box">
                For students and authorized printing staff.
            </div>

        </div>

    </section>

</main>

</body>
</html>