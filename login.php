
<?php
session_start();
require_once "koneksi.php";

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === "" || $password === "") {
        $error = "Email dan password wajib diisi.";
    } else {

        $stmt = $conn->prepare(
            "SELECT id, nama, email, password FROM admins WHERE email = ? LIMIT 1"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($admin = $result->fetch_assoc()) {

            if (password_verify($password, $admin['password'])) {

                session_regenerate_id(true);

                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_nama'] = $admin['nama'];
                $_SESSION['admin_email'] = $admin['email'];

                header("Location: dashboard.php");
                exit;

            } else {
                $error = "Password yang kamu masukkan salah.";
            }

        } else {
            $error = "Akun Admin tidak ditemukan.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - SIPAM RT</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #eef2f7;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-box {
            width: 100%;
            max-width: 400px;
            background: white;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }

        h2 {
            color: #172554;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #64748b;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-top: 16px;
            margin-bottom: 7px;
            font-weight: bold;
            color: #334155;
        }

        input {
            width: 100%;
            padding: 13px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 15px;
        }

        button {
            width: 100%;
            margin-top: 25px;
            padding: 14px;
            border: none;
            border-radius: 8px;
            background: #1d4ed8;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #1e40af;
        }

        .error {
            background: #fee2e2;
            color: #b91c1c;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .brand {
            color: #1d4ed8;
            font-weight: bold;
            letter-spacing: 1px;
        }
    </style>
</head>

<body>

<div class="login-box">

    <div class="brand">SIPAM RT</div>

    <h2>Login Admin</h2>

    <p class="subtitle">
        Sistem Informasi Pelayanan Aduan Masyarakat
    </p>

    <?php if ($error !== ""): ?>
        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <label>Email Admin</label>
        <input
            type="email"
            name="email"
            placeholder="Masukkan email admin"
            required
        >

        <label>Password</label>
        <input
            type="password"
            name="password"
            placeholder="Masukkan password"
            required
        >

        <button type="submit">Masuk ke Dashboard</button>

    </form>

</div>

</body>
</html>
