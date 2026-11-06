
<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/koneksi.php";
if (!isset($conn) || !($conn instanceof mysqli)) {
    die(json_encode([
        "status" => false,
        "pesan" => "Variabel koneksi database $conn tidak ditemukan. Periksa koneksi.php"
    ]));
}

// Mengambil kategori yang aktif saja
$sql = "
    SELECT id, nama_kategori
    FROM kategori_aduan
    WHERE aktif = 1
    ORDER BY nama_kategori ASC
";

$result = $conn->query($sql);

if (!$result) {
    http_response_code(500);

    echo json_encode([
        "status" => false,
        "pesan" => "Gagal mengambil data kategori"
    ]);

    $conn->close();
    exit;
}

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = [
        "id" => (int) $row["id"],
        "nama_kategori" => $row["nama_kategori"]
    ];
}

echo json_encode([
    "status" => true,
    "pesan" => "Data kategori berhasil diambil",
    "data" => $data
], JSON_UNESCAPED_UNICODE);

$conn->close();

?>
