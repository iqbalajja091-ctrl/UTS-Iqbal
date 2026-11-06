
<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/koneksi.php";

$sql = "
    SELECT
        p.id,
        u.nama AS nama_pelapor,
        p.kategori,
        p.judul,
        p.deskripsi,
        p.lokasi,
        p.status,
        p.tanggapan,
        p.created_at
    FROM pengaduan p
    INNER JOIN users u ON p.user_id = u.id
    ORDER BY p.created_at DESC
";

$result = $conn->query($sql);

if (!$result) {
    die("Gagal mengambil data laporan.");
}

$total = $result->num_rows;

function aman($teks) {
    return htmlspecialchars((string)$teks, ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Aduan SIPAM RT</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            color: #222;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
        }

        .header h2 {
            margin-bottom: 5px;
        }

        .info {
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        th, td {
            border: 1px solid #777;
            padding: 7px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #eeeeee;
            text-align: center;
        }

        .tombol {
            display: inline-block;
            background: #176b45;
            color: white;
            padding: 11px 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .tombol-kembali {
            background: #555;
            text-decoration: none;
            margin-left: 5px;
        }

        @media print {
            body {
                margin: 10mm;
            }

            .no-print {
                display: none;
            }

            @page {
                size: landscape;
                margin: 10mm;
            }
        }
    </style>
</head>

<body>

<div class="no-print">
    <button class="tombol" onclick="window.print()">
        Cetak / Simpan PDF
    </button>

    <a href="dashboard.php" class="tombol tombol-kembali">
        Kembali ke Dashboard
    </a>
</div>

<div class="header">
    <h2>LAPORAN PENGADUAN MASYARAKAT</h2>
    <h3>SIPAM RT</h3>
    <p>Sistem Informasi Pelayanan Aduan Masyarakat Tingkat RT</p>
    <hr>
</div>

<div class="info">
    <strong>Total Aduan:</strong> <?= $total ?> data
    <br>
    <strong>Tanggal Cetak:</strong> <?= date("d-m-Y H:i") ?>
</div>

<table>
    <thead>
        <tr>
            <th>No.</th>
            <th>Pelapor</th>
            <th>Kategori</th>
            <th>Judul Aduan</th>
            <th>Deskripsi</th>
            <th>Lokasi</th>
            <th>Status</th>
            <th>Tanggapan RT</th>
            <th>Tanggal</th>
        </tr>
    </thead>

    <tbody>
        <?php if ($total > 0): ?>
            <?php $no = 1; ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= aman($row["nama_pelapor"]) ?></td>
                    <td><?= aman($row["kategori"]) ?></td>
                    <td><?= aman($row["judul"]) ?></td>
                    <td><?= aman($row["deskripsi"]) ?></td>
                    <td><?= aman($row["lokasi"]) ?></td>
                    <td><?= aman($row["status"]) ?></td>
                    <td><?= aman($row["tanggapan"] ?: "-") ?></td>
                    <td><?= aman(date("d-m-Y H:i", strtotime($row["created_at"]))) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="9" style="text-align:center;">
                    Belum ada data pengaduan.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>

<?php
$result->free();
$conn->close();
?>
