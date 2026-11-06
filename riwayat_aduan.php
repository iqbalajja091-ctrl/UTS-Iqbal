
<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/koneksi.php";

// Memastikan request menggunakan POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "status" => false,
        "pesan" => "Gunakan metode POST"
    ]);
    exit;
}

// Mengambil data JSON
$data = json_decode(file_get_contents("php://input"), true);

$user_id = intval($data["user_id"] ?? 0);

// Validasi ID pengguna
if ($user_id <= 0) {
    echo json_encode([
        "status" => false,
        "pesan" => "ID pengguna tidak valid"
    ]);
    exit;
}

// Mengambil riwayat aduan berdasarkan ID pengguna
$stmt = $conn->prepare(
    "SELECT
        id,
        kategori,
        judul,
        deskripsi,
        lokasi,
        foto,
        status,
        tanggapan,
        created_at
     FROM pengaduan
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

if (!$stmt) {
    echo json_encode([
        "status" => false,
        "pesan" => "Gagal mengambil riwayat aduan"
    ]);
    exit;
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$hasil = $stmt->get_result();

$daftarAduan = [];

while ($row = $hasil->fetch_assoc()) {

    $daftarAduan[] = [
        "id" => (int) $row["id"],
        "kategori" => $row["kategori"],
        "judul" => $row["judul"],
        "deskripsi" => $row["deskripsi"],
        "lokasi" => $row["lokasi"],
        "foto" => $row["foto"],
        "status" => $row["status"],
        "tanggapan" => $row["tanggapan"],
        "created_at" => $row["created_at"]
    ];
}

echo json_encode([
    "status" => true,
    "pesan" => "Riwayat aduan berhasil diambil",
    "data" => $daftarAduan
], JSON_UNESCAPED_UNICODE);

$stmt->close();
$conn->close();

?>
