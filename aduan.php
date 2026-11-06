
<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once "koneksi.php";

// Mengambil seluruh aduan beserta identitas pelapor
$sql = "
    SELECT
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
        u.email AS email_pelapor
    FROM pengaduan p
    INNER JOIN users u ON p.user_id = u.id
    ORDER BY p.created_at DESC
";

$result = $conn->query($sql);

if (!$result) {
    die("Gagal mengambil data aduan: " . $conn->error);
}

function warnaStatus($status) {
    switch ($status) {
        case "Menunggu":
            return "#fef3c7";
        case "Diproses":
            return "#dbeafe";
        case "Selesai":
            return "#dcfce7";
        case "Ditolak":
            return "#fee2e2";
        default:
            return "#e2e8f0";
    }
}

function teksStatus($status) {
    switch ($status) {
        case "Menunggu":
            return "#92400e";
        case "Diproses":
            return "#1e40af";
        case "Selesai":
            return "#166534";
        case "Ditolak":
            return "#991b1b";
        default:
            return "#334155";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Aduan - SIPAM RT</title>

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
            padding: 30px;
        }

        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .top h2 {
            color: #172554;
        }

        .btn {
            display: inline-block;
            padding: 11px 16px;
            border-radius: 7px;
            text-decoration: none;
            color: white;
            background: #1d4ed8;
        }

        .table-box {
            background: white;
            padding: 20px;
            border-radius: 12px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
        }

        th, td {
            padding: 13px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
            font-size: 14px;
        }

        th {
            background: #f8fafc;
            color: #334155;
        }

        .status {
            display: inline-block;
            padding: 7px 11px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            white-space: nowrap;
        }

        .detail {
            display: inline-block;
            background: #1d4ed8;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
        }

        .kosong {
            text-align: center;
            padding: 30px;
            color: #64748b;
        }

        @media (max-width: 600px) {
            .container {
                padding: 15px;
            }
        }
    </style>
</head>

<body>

<header>
    <h2>SIPAM RT | Data Aduan Warga</h2>
</header>

<div class="container">

    <div class="top">
        <div>
            <h2>Daftar Aduan Masyarakat</h2>
            <p>Seluruh laporan yang dikirim oleh warga.</p>
        </div>

        <a href="dashboard.php" class="btn">
            Kembali ke Dashboard
        </a>
    </div>

    <div class="table-box">

        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Pelapor</th>
                    <th>Judul Aduan</th>
                    <th>Kategori</th>
                    <th>Lokasi</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>

            <?php if ($result->num_rows > 0): ?>

                <?php $no = 1; ?>

                <?php while ($row = $result->fetch_assoc()): ?>

                    <tr>
                        <td><?= $no++ ?></td>

                        <td>
                            <strong>
                                <?= htmlspecialchars($row['nama_pelapor']) ?>
                            </strong>
                            <br>
                            <small>
                                <?= htmlspecialchars($row['email_pelapor']) ?>
                            </small>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['judul']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['kategori']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['lokasi']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['created_at']) ?>
                        </td>

                        <td>
                            <span class="status"
                                style="
                                    background: <?= warnaStatus($row['status']) ?>;
                                    color: <?= teksStatus($row['status']) ?>;
                                ">
                                <?= htmlspecialchars($row['status']) ?>
                            </span>
                        </td>

                        <td>
                            <a
                                class="detail"
                                href="detail_aduan.php?id=<?= (int)$row['id'] ?>"
                            >
                                Detail
                            </a>
                        </td>
                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="8" class="kosong">
                        Belum ada aduan dari masyarakat.
                    </td>
                </tr>

            <?php endif; ?>

            </tbody>
        </table>

    </div>

</div>

</body>
</html>

<?php
$result->free();
$conn->close();
?>
