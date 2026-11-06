
<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once "koneksi.php";

$namaAdmin = $_SESSION['admin_nama'];

// Mengambil statistik langsung dari database
$sql = "
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'Menunggu' THEN 1 ELSE 0 END) AS menunggu,
        SUM(CASE WHEN status = 'Diproses' THEN 1 ELSE 0 END) AS diproses,
        SUM(CASE WHEN status = 'Selesai' THEN 1 ELSE 0 END) AS selesai,
        SUM(CASE WHEN status = 'Ditolak' THEN 1 ELSE 0 END) AS ditolak
    FROM pengaduan
";

$result = $conn->query($sql);

if (!$result) {
    die("Gagal mengambil statistik: " . $conn->error);
}

$statistik = $result->fetch_assoc();

$total = (int) $statistik['total'];
$menunggu = (int) $statistik['menunggu'];
$diproses = (int) $statistik['diproses'];
$selesai = (int) $statistik['selesai'];
$ditolak = (int) $statistik['ditolak'];

$persentase = $total > 0
    ? round(($selesai / $total) * 100, 1)
    : 0;

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SIPAM RT</title>

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
            padding: 22px 30px;
        }

        header h2 {
            margin: 0;
        }

        .container {
            padding: 30px;
        }

        .welcome {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
        }

        .welcome h2 {
            color: #172554;
            margin-top: 0;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 18px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            padding: 22px;
            border-radius: 12px;
            border-left: 5px solid #1d4ed8;
            box-shadow: 0 3px 12px rgba(0,0,0,0.04);
        }

        .stat-card h3 {
            margin: 0 0 12px;
            color: #64748b;
            font-size: 14px;
        }

        .number {
            font-size: 32px;
            font-weight: bold;
            color: #172554;
        }

        .blue {
            border-left-color: #2563eb;
        }

        .yellow {
            border-left-color: #d97706;
        }

        .purple {
            border-left-color: #7c3aed;
        }

        .green {
            border-left-color: #16a34a;
        }

        .red {
            border-left-color: #dc2626;
        }

        .progress-box {
            background: white;
            padding: 22px;
            border-radius: 12px;
            margin-bottom: 25px;
        }

        .progress-track {
            width: 100%;
            height: 14px;
            background: #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            margin-top: 12px;
        }

        .progress-fill {
            height: 100%;
            width: <?= $persentase ?>%;
            background: #16a34a;
            border-radius: 20px;
        }

        .menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .card {
            display: block;
            background: white;
            padding: 25px;
            border-radius: 12px;
            text-decoration: none;
            color: inherit;
            box-shadow: 0 3px 12px rgba(0,0,0,0.05);
            transition: 0.2s;
        }

        .card:hover {
            transform: translateY(-3px);
        }

        .card h3 {
            color: #172554;
            margin-top: 0;
        }

        .card p {
            color: #64748b;
        }

        .logout {
            display: inline-block;
            margin-top: 15px;
            padding: 12px 20px;
            background: #dc2626;
            color: white;
            text-decoration: none;
            border-radius: 7px;
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
    <h2>SIPAM RT | Admin Panel</h2>
</header>

<div class="container">

    <div class="welcome">
        <h2>Selamat Datang, <?= htmlspecialchars($namaAdmin) ?></h2>
        <p>Dashboard pengelolaan pelayanan aduan masyarakat tingkat RT.</p>

        <a href="logout.php" class="logout">Logout</a>
    </div>

    <h2>Statistik Aduan</h2>

    <div class="stats">

        <div class="stat-card blue">
            <h3>Total Aduan</h3>
            <div class="number"><?= $total ?></div>
        </div>

        <div class="stat-card yellow">
            <h3>Menunggu</h3>
            <div class="number"><?= $menunggu ?></div>
        </div>

        <div class="stat-card purple">
            <h3>Diproses</h3>
            <div class="number"><?= $diproses ?></div>
        </div>

        <div class="stat-card green">
            <h3>Selesai</h3>
            <div class="number"><?= $selesai ?></div>
        </div>

        <div class="stat-card red">
            <h3>Ditolak</h3>
            <div class="number"><?= $ditolak ?></div>
        </div>

    </div>

    <div class="progress-box">
        <h3>Persentase Penyelesaian Aduan</h3>

        <div class="number"><?= $persentase ?>%</div>

        <div class="progress-track">
            <div class="progress-fill"></div>
        </div>

        <p>
            <?= $selesai ?> dari <?= $total ?> aduan telah selesai.
        </p>
    </div>

    <h2>Menu Pengelolaan</h2>

    <div class="menu">
        
        <a href="kategori.php" class="card">
    <h3>Manajemen Kategori</h3>
    <p>Menambah, mengaktifkan, dan menonaktifkan kategori aduan.</p>
</a>

<a href="laporan.php" class="card">
    <h3>Laporan Aduan</h3>
    <p>Lihat dan cetak seluruh laporan pengaduan masyarakat dalam format PDF.</p>
    
    </a>

        <a href="aduan.php" class="card">
            <h3>Data Aduan</h3>
            <p>Melihat seluruh laporan masyarakat.</p>
        </a>

        <a href="aduan.php" class="card">
            <h3>Kelola Aduan</h3>
            <p>Membuka detail, mengubah status, dan memberikan tanggapan.</p>
        </a>

    </div>

</div>

</body>
</html>
