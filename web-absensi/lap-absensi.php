<?php
session_start();
require_once 'config/db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get filter parameters
$kelas_id = $_GET['kelas_id'] ?? '';
$bulan = $_GET['bulan'] ?? date('Y-m');
$status = $_GET['status'] ?? '';

// Get class list
$query_kelas = "SELECT * FROM kelas ORDER BY nama_kelas";
$result_kelas = $conn->query($query_kelas);

// Get attendance data if filter is set
if ($kelas_id) {
    $query = "SELECT s.nis, s.nama_siswa, k.nama_kelas,
              COUNT(CASE WHEN a.status = 'Hadir' THEN 1 END) as hadir,
              COUNT(CASE WHEN a.status = 'Izin' THEN 1 END) as izin,
              COUNT(CASE WHEN a.status = 'Sakit' THEN 1 END) as sakit,
              COUNT(CASE WHEN a.status = 'Alpha' THEN 1 END) as alpha,
              COUNT(a.id) as total_hari
              FROM siswa s
              JOIN kelas k ON s.kelas_id = k.id
              LEFT JOIN absensi a ON s.id = a.siswa_id 
              AND DATE_FORMAT(a.tanggal, '%Y-%m') = ?
              WHERE s.kelas_id = ?
              GROUP BY s.id";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $bulan, $kelas_id);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Absensi - Sistem Absensi</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <h2 class="mb-4">Laporan Absensi</h2>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="laporan.php" class="row g-3">
                    <div class="col-md-4">
                        <label for="kelas_id" class="form-label">Kelas</label>
                        <select class="form-select" id="kelas_id" name="kelas_id" required>
                            <option value="">Pilih Kelas...</option>
                            <?php while($kelas = $result_kelas->fetch_assoc()): ?>
                            <option value="<?php echo $kelas['id']; ?>" 
                                    <?php echo ($kelas_id == $kelas['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($kelas['nama_kelas']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="bulan" class="form-label">Bulan</label>
                        <input type="month" class="form-control" id="bulan" name="bulan" 
                               value="<?php echo $bulan; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary d-block">Tampilkan</button>
                    </div>
                </form>
            </div>
        </div>

        <?php if($kelas_id && isset($result)): ?>
        <div class="card">
        <div class="card-body">
                <h5 class="card-title">Rekap Absensi</h5>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Hadir</th>
                            <th>Izin</th>
                            <th>Sakit</th>
                            <th>Alpha</th>
                            <th>Total Hari</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result->num_rows > 0): ?>
                            <?php $no = 1; ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($row['nis']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_siswa']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_kelas']); ?></td>
                                <td><?php echo $row['hadir']; ?></td>
                                <td><?php echo $row['izin']; ?></td>
                                <td><?php echo $row['sakit']; ?></td>
                                <td><?php echo $row['alpha']; ?></td>
                                <td><?php echo $row['total_hari']; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">Tidak ada data absensi untuk filter ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
