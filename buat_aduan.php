
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

// Mengambil data dari form
$user_id = intval($_POST["user_id"] ?? 0);
$kategori = trim($_POST["kategori"] ?? "");
$judul = trim($_POST["judul"] ?? "");
$deskripsi = trim($_POST["deskripsi"] ?? "");
$lokasi = trim($_POST["lokasi"] ?? "");

// Validasi data
if (
    $user_id <= 0 ||
    $kategori === "" ||
    $judul === "" ||
    $deskripsi === "" ||
    $lokasi === ""
) {
    echo json_encode([
        "status" => false,
        "pesan" => "Semua data pengaduan wajib diisi"
    ]);
    exit;
}

// Memastikan pengguna terdaftar
$cekUser = $conn->prepare(
    "SELECT id FROM users WHERE id = ?"
);

if (!$cekUser) {
    echo json_encode([
        "status" => false,
        "pesan" => "Gagal memeriksa pengguna"
    ]);
    exit;
}

$cekUser->bind_param("i", $user_id);
$cekUser->execute();

$hasilUser = $cekUser->get_result();

if ($hasilUser->num_rows === 0) {
    $cekUser->close();

    echo json_encode([
        "status" => false,
        "pesan" => "Pengguna tidak ditemukan"
    ]);
    exit;
}

$cekUser->close();

$foto = null;

// Memproses foto jika dikirim
if (
    isset($_FILES["foto"]) &&
    $_FILES["foto"]["error"] !== UPLOAD_ERR_NO_FILE
) {

    if ($_FILES["foto"]["error"] !== UPLOAD_ERR_OK) {
        echo json_encode([
            "status" => false,
            "pesan" => "Gagal menerima foto"
        ]);
        exit;
    }

    // Maksimal ukuran foto 5 MB
    if ($_FILES["foto"]["size"] > 5 * 1024 * 1024) {
        echo json_encode([
            "status" => false,
            "pesan" => "Ukuran foto maksimal 5 MB"
        ]);
        exit;
    }

    // Memeriksa jenis file
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($_FILES["foto"]["tmp_name"]);

    $jenisFoto = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp"
    ];

    if (!isset($jenisFoto[$mime])) {
        echo json_encode([
            "status" => false,
            "pesan" => "Format foto harus JPG, PNG, atau WEBP"
        ]);
        exit;
    }

    $namaFoto = bin2hex(random_bytes(16))
        . "." . $jenisFoto[$mime];

    $folderUpload = __DIR__ . "/uploads/";

    if (
        !is_dir($folderUpload) &&
        !mkdir($folderUpload, 0755, true)
    ) {
        echo json_encode([
            "status" => false,
            "pesan" => "Folder upload gagal dibuat"
        ]);
        exit;
    }

    $tujuanFoto = $folderUpload . $namaFoto;

    if (!move_uploaded_file(
        $_FILES["foto"]["tmp_name"],
        $tujuanFoto
    )) {
        echo json_encode([
            "status" => false,
            "pesan" => "Foto gagal disimpan"
        ]);
        exit;
    }

    $foto = $namaFoto;
}

// Menyimpan data pengaduan
$stmt = $conn->prepare(
    "INSERT INTO pengaduan
    (user_id, kategori, judul, deskripsi, lokasi, foto)
    VALUES (?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    echo json_encode([
        "status" => false,
        "pesan" => "Gagal menyiapkan penyimpanan pengaduan"
    ]);
    exit;
}

$stmt->bind_param(
    "isssss",
    $user_id,
    $kategori,
    $judul,
    $deskripsi,
    $lokasi,
    $foto
);

if ($stmt->execute()) {

    echo json_encode([
        "status" => true,
        "pesan" => "Pengaduan berhasil dikirim",
        "pengaduan_id" => $stmt->insert_id,
        "foto" => $foto
    ], JSON_UNESCAPED_UNICODE);

} else {

    echo json_encode([
        "status" => false,
        "pesan" => "Pengaduan gagal disimpan"
    ]);
}

$stmt->close();
$conn->close();

?>
