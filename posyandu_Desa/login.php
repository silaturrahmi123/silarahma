<?php
session_start();
include 'config.php';

$error = '';

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = mysqli_prepare($conn,
        "SELECT * FROM admin WHERE username = ?"
    );

    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_get_result($stmt);
    $admin = mysqli_fetch_assoc($hasil);

    if ($admin && password_verify($password, $admin['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin'] = $admin['username'];

        header("Location: index.php?page=dashboard");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Admin Posyandu</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: linear-gradient(135deg, #d9f5e4, #effaf3);
    padding: 20px;
}

.login-box {
    background: white;
    width: 100%;
    max-width: 400px;
    padding: 35px;
    border-radius: 20px;
    box-shadow: 0 10px 35px rgba(0,0,0,0.12);
}

.logo {
    text-align: center;
    font-size: 48px;
}

h2 {
    text-align: center;
    color: #218653;
    margin-bottom: 8px;
}

.deskripsi {
    text-align: center;
    color: #777;
    font-size: 14px;
    margin-bottom: 30px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #333;
}

input {
    width: 100%;
    padding: 13px;
    margin-bottom: 20px;
    border: 1px solid #ddd;
    border-radius: 9px;
    outline: none;
}

input:focus {
    border-color: #218653;
    box-shadow: 0 0 5px #b8e8cc;
}

button {
    width: 100%;
    padding: 14px;
    background: #218653;
    color: white;
    border: none;
    border-radius: 9px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
}

button:hover {
    background: #176b40;
}

.error {
    background: #ffe4e4;
    color: #c62828;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 18px;
    text-align: center;
    font-size: 14px;
}

.footer {
    text-align: center;
    margin-top: 25px;
    color: #999;
    font-size: 12px;
}
</style>
</head>

<body>

<div class="login-box">

    <div class="logo">🏥</div>

    <h2>POSYANDU DESA</h2>

    <p class="deskripsi">
        Silakan login untuk mengelola data Posyandu
    </p>

    <?php if ($error): ?>
        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <label>Username</label>
        <input type="text"
               name="username"
               placeholder="Masukkan username"
               required>

        <label>Password</label>
        <input type="password"
               name="password"
               placeholder="Masukkan password"
               required>

        <button type="submit" name="login">
            MASUK
        </button>

    </form>

    <div class="footer">
        © Posyandu Desa - Sehat Bersama
    </div>

</div>

</body>
</html>