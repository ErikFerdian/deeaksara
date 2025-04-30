<?php
session_start();
require_once "function.php";

if (isset($_POST["login"])) {
    $login = login_akun();
} else if (isset($_POST["register"])) {
    $register = register_akun();
    
    if ($register > 0) {
        $_SESSION['success'] = "Registrasi berhasil! Silakan login.";
    } else if ($register == -1) {
        $_SESSION['error'] = "Registrasi gagal!";
    }
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - Dee Aksara</title>
    
    <!-- Bootstrap -->
    <link rel="stylesheet" href="./src/css/bootstrap-5.2.0/css/bootstrap.min.css" />
    
    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Javanese&family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #4E342E, #795548);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            width: 100%;
            max-width: 420px;
            background-color: #fff8f0;
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .brand {
            font-family: 'Noto Sans Javanese', sans-serif;
            font-size: 2.5rem;
            text-align: center;
            padding-top: 1rem;
            margin-bottom: -0.8rem;
            color: #d4af37;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
            letter-spacing: 3px;
        }

        .title {
            font-weight: 600;
            font-size: 1.3rem;
            text-align: center;
            color: #4E342E;
        }

        .tab-btn {
            width: 50%;
            border: none;
            padding: 1rem;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
        }

        .tab-btn.active {
            background-color: #4E342E;
            color: #fff;
        }

        .tab-btn:not(.active) {
            background-color: #e0d2c3;
            color: #4E342E;
        }

        .form-section {
            display: none;
        }

        .form-section.active {
            display: block;
        }

        .form-control {
            border-radius: 12px;
            border: 1px solid #bcae9e;
        }

        .form-control:focus {
            box-shadow: 0 0 5px #a1887f;
            border-color: #a1887f;
        }

        .btn-login, .btn-register {
            background-color: #4E342E;
            color: #fff;
            border: none;
            padding: 0.75rem;
            border-radius: 12px;
            transition: 0.3s;
        }

        .btn-login:hover, .btn-register:hover {
            background-color: #3e2723;
        }

        .alert {
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="card">
    <div class="brand">DEE AKSARA</div>
    <div class="title mb-3">Selamat Datang!</div>

    <div class="d-flex">
        <button class="tab-btn active" onclick="showTab('login')">Login</button>
        <button class="tab-btn" onclick="showTab('register')">Register</button>
    </div>

    <div class="p-4">
        <!-- Form Login -->
        <div class="form-section active" id="login-section">
            <?php if (isset($_SESSION['error'])) : ?>
                <div class="alert alert-danger py-2"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])) : ?>
                <div class="alert alert-success py-2"><?= $_SESSION['success'] ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <input type="text" name="username" class="form-control mb-3" placeholder="Username" required />
                <input type="password" name="password" class="form-control mb-4" placeholder="Password" required />
                <button class="btn-login w-100" name="login">Login</button>
            </form>
        </div>

        <!-- Form Registrasi -->
        <div class="form-section" id="register-section">
            <form method="POST" action="login.php">
                <input type="text" name="username" class="form-control mb-3" placeholder="Username" required />
                <input type="password" name="password" class="form-control mb-3" placeholder="Password" required />
                <input type="password" name="confirm_password" class="form-control mb-3" placeholder="Konfirmasi Password" required />
                
                <label for="role" class="form-label">Jenis Akun:</label>
                <select name="role" class="form-control mb-4" required>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
                
                <button class="btn-register w-100" name="register">Daftar</button>
            </form>
        </div>
    </div>
</div>

<script>
    function showTab(tab) {
        const loginBtn = document.querySelectorAll(".tab-btn")[0];
        const registerBtn = document.querySelectorAll(".tab-btn")[1];
        const loginForm = document.getElementById("login-section");
        const registerForm = document.getElementById("register-section");

        if (tab === 'login') {
            loginBtn.classList.add("active");
            registerBtn.classList.remove("active");
            loginForm.classList.add("active");
            registerForm.classList.remove("active");
        } else {
            loginBtn.classList.remove("active");
            registerBtn.classList.add("active");
            loginForm.classList.remove("active");
            registerForm.classList.add("active");
        }
    }
</script>

<script src="./src/css/bootstrap-5.2.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>