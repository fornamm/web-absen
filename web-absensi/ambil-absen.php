<?php
session_start();
require_once 'config/db.php';

// Cek login
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Proses input absensi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kelas_id = $_POST['kelas_id'];
    $tanggal = $_POST['tanggal'];
    $petugas_id = $_SESSION['user_id'];
    
    foreach ($_POST['status'] as $siswa_id => $status) {
        $keterangan = $_POST['keterangan'][$siswa_id] ?? '';
        
        // Cek apakah sudah ada absensi untuk siswa dan tanggal tersebut
        $check_query = "SELECT id FROM absensi WHERE siswa_id = ? AND DATE(tanggal) = DATE(?)";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("is", $siswa_id, $tanggal);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Update absensi yang sudah ada
            $row = $check_result->fetch_assoc();
            $query = "UPDATE absensi SET status = ?, keterangan = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssi", $status, $keterangan, $row['id']);
        } else {
            // Insert absensi baru
            $query = "INSERT INTO absensi (siswa_id, tanggal, status, keterangan, petugas_id) 
                     VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("isssi", $siswa_id, $tanggal, $status, $keterangan, $petugas_id);
        }
        $stmt->execute();
    }
    
    header("Location: absensi.php?kelas_id=" . $kelas_id . "&tanggal=" . $tanggal);
    exit();
}

// Ambil data kelas
$query_kelas = "SELECT * FROM kelas ORDER BY nama_kelas";
$result_kelas = $conn->query($query_kelas);

// Ambil data siswa berdasarkan kelas jika ada filter
$kelas_id = $_GET['kelas_id'] ?? '';
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');

if ($kelas_id) {
    $query_siswa = "SELECT s.*, k.nama_kelas,
                    (SELECT a.status FROM ab
                    (SELECT a.status FROM absensi a 
                     WHERE a.siswa_id = s.id AND DATE(a.tanggal) = ? LIMIT 1) as status_absen,
                    (SELECT a.keterangan FROM absensi a 
                     WHERE a.siswa_id = s.id AND DATE(a.tanggal) = ? LIMIT 1) as keterangan_absen
                    FROM siswa s 
                    JOIN kelas k ON s.kelas_id = k.id 
                    WHERE s.kelas_id = ?
                    ORDER BY s.nama_siswa";
    $stmt_siswa = $conn->prepare($query_siswa);
    $stmt_siswa->bind_param("ssi", $tanggal, $tanggal, $kelas_id);
    $stmt_siswa->execute();
    $result_siswa = $stmt_siswa->get_result();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Absensi - Sistem Absensi</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <h2 class="mb-4">Input Absensi</h2>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="absensi.php" id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="kelas_id" class="form-label">Pilih Kelas</label>
                            <select class="form-select" id="kelas_id" name="kelas_id" required 
                                    onchange="document.getElementById('filterForm').submit()">
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
                            <label for="tanggal" class="form-label">Tanggal</label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" 
                                   value="<?php echo $tanggal; ?>" required
                                   onchange="document.getElementById('filterForm').submit()">
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php if($kelas_id && isset($result_siswa) && $result_siswa->num_rows > 0): ?>
        <div class="card">
            <div class="card-body">
                <form action="absensi.php" method="POST">
                    <input type="hidden" name="kelas_id" value="<?php echo $kelas_id; ?>">
                    <input type="hidden" name="tanggal" value="<?php echo $tanggal; ?>">
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Status</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                while($siswa = $result_siswa->fetch_assoc()): 
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($siswa['nis']); ?></td>
                                    <td><?php echo htmlspecialchars($siswa['nama_siswa']); ?></td>
                                    <td>
                                        <select class="form-select" name="status[<?php echo $siswa['id']; ?>]" required>
                                            <option value="Hadir" <?php echo ($siswa['status_absen'] == 'Hadir') ? 'selected' : ''; ?>>
                                                Hadir
                                            </option>
                                            <option value="Izin" <?php echo ($siswa['status_absen'] == 'Izin') ? 'selected' : ''; ?>>
                                                Izin
                                            </option>
                                            <option value="Sakit" <?php echo ($siswa['status_absen'] == 'Sakit') ? 'selected' : ''; ?>>
                                                Sakit
                                            </option>
                                            <option value="Alpha" <?php echo ($siswa['status_absen'] == 'Alpha') ? 'selected' : ''; ?>>
                                                Alpha
                                            </option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" 
                                               name="keterangan[<?php echo $siswa['id']; ?>]"
                                               value="<?php echo htmlspecialchars($siswa['keterangan_absen'] ?? ''); ?>"
                                               placeholder="Optional">
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                        <button type="submit" class="btn btn-primary">Simpan Absensi</button>
                    </div>
                </form>
            </div>
        </div>
        <?php elseif($kelas_id): ?>
        <div class="alert alert-info">
            Tidak ada data siswa untuk kelas yang dipilih.
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>