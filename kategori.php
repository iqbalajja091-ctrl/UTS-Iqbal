
<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once "koneksi.php";

$pesan = "";
$jenisPesan = "";

// Menambahkan kategori baru
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === "tambah") {
        $nama = trim($_POST['nama_kategori'] ?? '');

        if ($nama === '') {
            $pesan = "Nama kategori wajib diisi.";
            $jenisPesan = "error";
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO kategori_aduan (nama_kategori) VALUES (?)"
            );

            $stmt->bind_param("s", $nama);

            if ($stmt->execute()) {
                $stmt->close();
                header("Location: kategori.php?pesan=tambah");
                exit;
            } else {
                $pesan = "Kategori gagal ditambahkan. Nama mungkin sudah digunakan.";
                $jenisPesan = "error";
                $stmt->close();
            }
        }
    }

    // Mengaktifkan atau menonaktifkan kategori
    if ($aksi === "ubah_status") {
        $id = (int) ($_POST['id'] ?? 0);

        if ($id > 0) {
            $stmt = $conn->prepare(
                "UPDATE kategori_aduan
                 SET aktif = IF(aktif = 1, 0, 1)
                 WHERE id = ?"
            );

            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                $stmt->close();
                header("Location: kategori.php?pesan=status");
                exit;
            }

            $stmt->close();
            $pesan = "Status kategori gagal diperbarui.";
            $jenisPesan = "error";
        }
    }
}

// Pesan setelah redirect
if (isset($_GET['pesan'])) {
    if ($_GET['pesan'] === "tambah") {
        $pesan = "Kategori berhasil ditambahkan.";
        $jenisPesan = "success";
    } elseif ($_GET['pesan'] === "status") {
        $pesan = "Status kategori berhasil diperbarui.";
        $jenisPesan = "success";
    }
}

// Mengambil seluruh kategori
$result = $conn->query(
    "SELECT * FROM kategori_aduan ORDER BY id DESC"
);

if (!$result) {
    die("Gagal mengambil kategori: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manajemen Kategori - SIPAM RT</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 25px;
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }

        .container {
            max-width: 950px;
            margin: auto;
        }

        .header, .panel {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.04);
        }

        h2 {
            color: #172554;
            margin-top: 0;
        }

        input[type="text"] {
            width: 100%;
            padding: 13px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            margin: 10px 0;
        }

        button, .back {
            display: inline-block;
            border: none;
            padding: 11px 16px;
            border-radius: 7px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .add {
            background: #1d4ed8;
            color: white;
        }

        .toggle {
            background: #475569;
            color: white;
        }

        .back {
            background: #e2e8f0;
            color: #1e293b;
            margin-top: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 13px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: #f8fafc;
        }

        .aktif {
            color: #15803d;
            font-weight: bold;
        }

        .nonaktif {
            color: #dc2626;
            font-weight: bold;
        }

        .pesan {
            padding: 12px;
            border-radius: 7px;
            margin-bottom: 15px;
        }

        .success {
            background: #dcfce7;
            color: #166534;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
        }

        @media (max-width: 600px) {
            body {
                padding: 12px;
            }

            .header, .panel {
                padding: 16px;
            }

            table {
                font-size: 13px;
            }

            th, td {
                padding: 9px 6px;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <div class="header">
        <h2>Manajemen Kategori Aduan</h2>
        <p>Kelola kategori laporan masyarakat SIPAM RT.</p>
        <a href="dashboard.php" class="back">Kembali ke Dashboard</a>
    </div>

    <div class="panel">

        <h2>Tambah Kategori</h2>

        <?php if ($pesan !== ''): ?>
            <div class="pesan <?= htmlspecialchars($jenisPesan) ?>">
                <?= htmlspecialchars($pesan) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="aksi" value="tambah">

            <label for="nama_kategori">Nama Kategori</label>

            <input
                type="text"
                id="nama_kategori"
                name="nama_kategori"
                placeholder="Contoh: Jalan Rusak"
                maxlength="100"
                required
            >

            <button type="submit" class="add">
                Tambah Kategori
            </button>
        </form>

    </div>

    <div class="panel">

        <h2>Daftar Kategori</h2>

        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama Kategori</th>
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
                            <?= htmlspecialchars($row['nama_kategori']) ?>
                        </td>

                        <td>
                            <?php if ($row['aktif'] == 1): ?>
                                <span class="aktif">Aktif</span>
                            <?php else: ?>
                                <span class="nonaktif">Nonaktif</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <form method="POST">
                                <input type="hidden" name="aksi" value="ubah_status">

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) $row['id'] ?>"
                                >

                                <button type="submit" class="toggle">
                                    <?= $row['aktif'] == 1
                                        ? 'Nonaktifkan'
                                        : 'Aktifkan' ?>
                                </button>
                            </form>
                        </td>
                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="4">Belum ada kategori.</td>
                </tr>

            <?php endif; ?>

            </tbody>
        </table>

    </div>

</div>

</body>
</html>

<?php
$conn->close();
?>
