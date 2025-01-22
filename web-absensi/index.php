<?php
session_start();
require_once 'config/db.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil data user yang login
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Ambil data absensi hari ini
$today = date('Y-m-d');
$query_attendance = "SELECT a.*, s.nama_siswa, k.nama_kelas 
                    FROM absensi a
                    JOIN siswa s ON a.siswa_id = s.id
                    JOIN kelas k ON s.kelas_id = k.id
                    WHERE DATE(a.tanggal) = ?
                    ORDER BY k.nama_kelas, s.nama_siswa";
$stmt_attendance = $conn->prepare($query_attendance);
$stmt_attendance->bind_param("s", $today);
$stmt_attendance->execute();
$result_attendance = $stmt_attendance->get_result();
?>

    <?php include 'includes/head.php'; ?>
    <?php include 'includes/navbar.php'; ?>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Rekap Absensi Hari Ini (<?php echo date('d/m/Y'); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Siswa</th>
                                        <th>Kelas</th>
                                        <th>Status</th>
                                        <th>Waktu Absen</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    while ($row = $result_attendance->fetch_assoc()):
                                    ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo htmlspecialchars($row['nama_siswa']); ?></td>
                                            <td><?php echo htmlspecialchars($row['nama_kelas']); ?></td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                switch ($row['status']) {
                                                    case 'Hadir':
                                                        $status_class = 'text-success';
                                                        break;
                                                    case 'Izin':
                                                        $status_class = 'text-warning';
                                                        break;
                                                    case 'Sakit':
                                                        $status_class = 'text-info';
                                                        break;
                                                    case 'Alpha':
                                                        $status_class = 'text-danger';
                                                        break;
                                                }
                                                ?>
                                                <span class="<?php echo $status_class; ?>">
                                                    <?php echo htmlspecialchars($row['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('H:i', strtotime($row['tanggal'])); ?></td>
                                            <td><?php echo htmlspecialchars($row['keterangan']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                    <?php if ($result_attendance->num_rows == 0): ?>
                                        <tr>
                                            <td colspan="6" class="text-center">Belum ada data absensi hari ini</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>