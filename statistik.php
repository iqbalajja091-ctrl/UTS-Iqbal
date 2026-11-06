
<?php

header("Content-Type: application/json; charset=UTF-8");

require_once "koneksi.php";

// Memastikan request menggunakan POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Metode harus POST"
    ]);
    exit;
}

// Mengambil data JSON dari Android
$data = json_decode(file_get_contents("php://input"), true);

$user_id = isset($data["user_id"]) ? (int)$data["user_id"] : 0;

// Memeriksa ID pengguna
if ($user_id <= 0) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "User ID tidak valid"
    ]);
    exit;
}

// Menghitung aduan berdasarkan pengguna yang login
$sql = "SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN status = 'Menunggu' THEN 1 ELSE 0 END) AS menunggu,
    SUM(CASE WHEN status = 'Diproses' THEN 1 ELSE 0 END) AS diproses,
    SUM(CASE WHEN status = 'Selesai' THEN 1 ELSE 0 END) AS selesai,
    SUM(CASE WHEN status = 'Ditolak' THEN 1 ELSE 0 END) AS ditolak
    FROM pengaduan
    WHERE user_id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Query statistik gagal disiapkan"
    ]);
    exit;
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Mengirim hasil ke Android
echo json_encode([
    "success" => true,
    "total" => (int)$row["total"],
    "menunggu" => (int)$row["menunggu"],
    "diproses" => (int)$row["diproses"],
    "selesai" => (int)$row["selesai"],
    "ditolak" => (int)$row["ditolak"]
]);

$stmt->close();
$conn->close();

?>
