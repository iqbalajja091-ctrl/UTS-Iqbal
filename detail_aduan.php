
<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once "koneksi.php";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    die("ID aduan tidak valid.");
}

$pesan = "";
$error = "";

// Proses perubahan status dan tanggapan
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $status = $_POST['status'] ?? '';
    $tanggapan = trim($_POST['tanggapan'] ?? '');

    $statusValid = [
        "Menunggu",
        "Diproses",
        "Selesai",
        "Ditolak"
    ];

    if (!in_array($status, $statusValid, true)) {
        $error = "Status aduan tidak valid.";
    } elseif ($tanggapan === "") {
        $error = "Tanggapan wajib diisi.";
    } else {

        $stmtUpdate = $conn->prepare(
            "UPDATE pengaduan
             SET status = ?, tanggapan = ?
             WHERE id = ?"
        );

        $stmtUpdate->bind_param(
            "ssi",
            $status,
            $tanggapan,
            $id
        );

        if ($stmtUpdate->execute()) {
            header("Location: detail_aduan.php?id=" . $id . "&berhasil=1");
            exit;
        } else {
            $error = "Gagal memperbarui aduan.";
        }

        $stmtUpdate->close();
    }
}

// Ambil data aduan dan identitas pelapor
$stmt = $conn->prepare(
    "SELECT
        p.id,
        p.user_id,
        p.kategori,
        p.judul,
        p.deskripsi,
        p.lokasi,
        p.foto,
        p.status,
        p.tanggapan,
        p.created_at,
        u.nama AS nama_pelapor,
        u.email AS email_pelapor,
        u.telepon AS telepon_pelapor
     FROM pengaduan p
     INNER JOIN users u ON p.user_id = u.id
     WHERE p.id = ?
     LIMIT 1"
);

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$aduan = $result->fetch_assoc();

$stmt->close();

if (!$aduan) {
    die("Data aduan tidak ditemukan.");
}

if (isset($_GET['berhasil'])) {
    $pesan = "Status dan tanggapan berhasil diperbarui.";
}

// Lokasi foto bukti
$namaFoto = basename($aduan['foto'] ?? '');
$pathFoto = "../sipam_api/uploads/" . rawurlencode($namaFoto);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detail Aduan - SIPAM RT</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }

        header {
            background: #172554;
            color: white;
            padding: 20px 30px;
        }

        header h2 {
            margin: 0;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            padding: 25px;
        }

        .card {
            background: white;
            padding: 25px;
            margin-bottom: 20px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.04);
        }

        h3 {
            color: #172554;
            margin-top: 0;
        }

        .info {
            margin: 12px 0;
            line-height: 1.6;
        }

        .label {
            color: #64748b;
            font-size: 13px;
        }

        .value {
            font-weight: bold;
            color: #1e293b;
        }

        img {
            max-width: 100%;
            max-height: 400px;
            object-fit: contain;
            border-radius: 10px;
            margin-top: 10px;
        }

        select, textarea {
            width: 100%;
            padding: 13px;
            margin-top: 8px;
            margin-bottom: 18px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 15px;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        button, .back {
            display: inline-block;
            padding: 13px 20px;
            border: none;
            border-radius: 7px;
            background: #1d4ed8;
            color: white;
            font-size: 15px;
            text-decoration: none;
            cursor: pointer;
        }

        .back {
            background: #64748b;
            margin-bottom: 20px;
        }

        .success {
            padding: 13px;
            background: #dcfce7;
            color: #166534;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .error {
            padding: 13px;
            background: #fee2e2;
            color: #991b1b;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        @media(max-width: 600px) {
            .container {
                padding: 15px;
            }

            .card {
                padding: 18px;
            }
        }
    </style>
</head>

<body>

<header>
    <h2>SIPAM RT | Detail Aduan</h2>
</header>

<div class="container">

    <a href="aduan.php" class="back">
        Kembali ke Daftar Aduan
    </a>

    <?php if ($pesan !== ""): ?>
        <div class="success">
            <?= htmlspecialchars($pesan) ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="card">

        <h3>Informasi Pelapor</h3>

        <div class="info">
            <div class="label">Nama Pelapor</div>
            <div class="value">
                <?= htmlspecialchars($aduan['nama_pelapor']) ?>
            </div>
        </div>

        <div class="info">
            <div class="label">Email</div>
            <div class="value">
                <?= htmlspecialchars($aduan['email_pelapor']) ?>
            </div>
        </div>

        <div class="info">
            <div class="label">Nomor Telepon</div>
            <div class="value">
                <?= htmlspecialchars($aduan['telepon_pelapor']) ?>
            </div>
        </div>

    </div>

    <div class="card">

        <h3>Informasi Aduan</h3>

        <div class="info">
            <div class="label">Judul Aduan</div>
            <div class="value">
                <?= htmlspecialchars($aduan['judul']) ?>
            </div>
        </div>

        <div class="info">
            <div class="label">Kategori</div>
            <div class="value">
                <?= htmlspecialchars($aduan['kategori']) ?>
            </div>
        </div>

        <div class="info">
            <div class="label">Deskripsi</div>
            <div class="value">
                <?= nl2br(htmlspecialchars($aduan['deskripsi'])) ?>
            </div>
        </div>

        <div class="info">
            <div class="label">Lokasi Kejadian</div>
            <div class="value">
                <?= htmlspecialchars($aduan['lokasi']) ?>
            </div>
        </div>

        <div class="info">
            <div class="label">Tanggal Laporan</div>
            <div class="value">
                <?= htmlspecialchars($aduan['created_at']) ?>
            </div>
        </div>

        <div class="info">
            <div class="label">Foto Bukti</div>

            <?php if ($namaFoto !== ""): ?>

                <img
                    src="<?= htmlspecialchars($pathFoto) ?>"
                    alt="Foto bukti aduan"
                    onerror="this.style.display='none'; document.getElementById('fotoError').style.display='block';"
                >

                <p id="fotoError" style="display:none;color:#dc2626;">
                    Foto tidak ditemukan. Periksa lokasi penyimpanan foto.
                </p>

            <?php else: ?>

                <p>Warga tidak melampirkan foto.</p>

            <?php endif; ?>
        </div>

    </div>

    <div class="card">

        <h3>Pengelolaan Aduan</h3>

        <form method="POST">

            <label for="status">Status Pengerjaan</label>

            <select name="status" id="status" required>

                <option value="Menunggu"
                    <?= $aduan['status'] === 'Menunggu' ? 'selected' : '' ?>>
                    Menunggu
                </option>

                <option value="Diproses"
                    <?= $aduan['status'] === 'Diproses' ? 'selected' : '' ?>>
                    Diproses
                </option>

                <option value="Selesai"
                    <?= $aduan['status'] === 'Selesai' ? 'selected' : '' ?>>
                    Selesai
                </option>

                <option value="Ditolak"
                    <?= $aduan['status'] === 'Ditolak' ? 'selected' : '' ?>>
                    Ditolak
                </option>

            </select>

            <label for="tanggapan">Tanggapan Admin / RT</label>

            <textarea
                name="tanggapan"
                id="tanggapan"
                placeholder="Tuliskan tanggapan atau informasi penanganan aduan..."
                required><?= htmlspecialchars($aduan['tanggapan'] ?? '') ?></textarea>

            <button type="submit">
                Simpan Perubahan
            </button>

        </form>

    </div>

</div>

</body>
</html>

<?php
$conn->close();
?>
