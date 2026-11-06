
<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . "/koneksi.php";

// Memastikan koneksi database tersedia
if (!isset($conn) || !($conn instanceof mysqli)) {
    http_response_code(500);
    echo json_encode([
        "status" => false,
        "pesan" => "Koneksi database tidak tersedia"
    ]);
    exit;
}

// Hanya menerima metode POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "status" => false,
        "pesan" => "Gunakan metode POST"
    ]);
    exit;
}

// Mengambil data JSON dari Android
$data = json_decode(file_get_contents("php://input"), true);

if (!is_array($data)) {
    echo json_encode([
        "status" => false,
        "pesan" => "Format data tidak valid"
    ]);
    exit;
}

$nama = trim($data["nama"] ?? "");
$telepon = trim($data["telepon"] ?? "");
$alamat = trim($data["alamat"] ?? "");
$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

// Validasi data
if (
    $nama === "" ||
    $telepon === "" ||
    $alamat === "" ||
    $email === "" ||
    $password === ""
) {
    echo json_encode([
        "status" => false,
        "pesan" => "Semua kolom wajib diisi"
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "status" => false,
        "pesan" => "Format email tidak valid"
    ]);
    exit;
}

if (strlen($password) < 8) {
    echo json_encode([
        "status" => false,
        "pesan" => "Password minimal 8 karakter"
    ]);
    exit;
}

// Memeriksa apakah email sudah terdaftar
$stmt = $conn->prepare(
    "SELECT id FROM users WHERE email = ?"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "status" => false,
        "pesan" => "Gagal memeriksa email"
    ]);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$hasil = $stmt->get_result();

if ($hasil->num_rows > 0) {
    $stmt->close();

    echo json_encode([
        "status" => false,
        "pesan" => "Email sudah terdaftar"
    ]);
    exit;
}

$stmt->close();

// Mengamankan password
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Menyimpan pengguna ke database
$stmt = $conn->prepare(
    "INSERT INTO users (nama, telepon, alamat, email, password)
     VALUES (?, ?, ?, ?, ?)"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "status" => false,
        "pesan" => "Gagal menyiapkan penyimpanan data"
    ]);
    exit;
}

$stmt->bind_param(
    "sssss",
    $nama,
    $telepon,
    $alamat,
    $email,
    $passwordHash
);

if ($stmt->execute()) {
    echo json_encode([
        "status" => true,
        "pesan" => "Registrasi berhasil",
        "user_id" => $stmt->insert_id
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "status" => false,
        "pesan" => "Registrasi gagal"
    ]);
}

$stmt->close();
$conn->close();

?>
