<?php
session_start();
require_once 'config/db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Process class management actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $nama_kelas = trim($_POST['nama_kelas']);
                $wali_kelas = $_POST['wali_kelas_id'];
                $tahun_ajaran = $_POST['tahun_ajaran'];
                
                $query = "INSERT INTO kelas (nama_kelas, wali_kelas_id, tahun_ajaran) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("sis", $nama_kelas, $wali_kelas, $tahun_ajaran);
                $stmt->execute();
                break;

            case 'edit':
                $id = $_POST['kelas_id'];
                $nama_kelas = trim($_POST['nama_kelas']);
                $wali_kelas = $_POST['wali_kelas_id'];
                $tahun_ajaran = $_POST['tahun_ajaran'];
                
                $query = "UPDATE kelas SET nama_kelas=?, wali_kelas_id=?, tahun_ajaran=? WHERE id=?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("sisi", $nama_kelas, $wali_kelas, $tahun_ajaran, $id);
                $stmt->execute();
                break;

            case 'delete':
                $id = $_POST['kelas_id'];
                $query = "DELETE FROM kelas WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                break;
        }
        header("Location: kelas.php");
        exit();
    }
}

// Get teachers for dropdown
$query_guru = "SELECT id, nama FROM users WHERE role = 'guru' ORDER BY nama";
$result_guru = $conn->query($query_guru);

// Get class data
$query_kelas = "SELECT k.*, u.nama as wali_kelas, 
                (SELECT COUNT(*) FROM siswa s WHERE s.kelas_id = k.id) as jumlah_siswa 
                FROM kelas k 
                LEFT JOIN users u ON k.wali_kelas_id = u.id 
                ORDER BY k.tahun_ajaran DESC, k.nama_kelas";
$result_kelas = $conn->query($query_kelas);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kelas - Sistem Absensi</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Data Kelas</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                Tambah Kelas
            </button>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Nama Kelas</th>
                                <th>Wali Kelas</th>
                                <th>Tahun Ajaran</th>
                                <th>Jumlah Siswa</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result_kelas->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nama_kelas']); ?></td>
                                <td><?php echo htmlspecialchars($row['wali_kelas']); ?></td>
                                <td><?php echo htmlspecialchars($row['tahun_ajaran']); ?></td>
                                <td><?php echo $row['jumlah_siswa']; ?> siswa</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-warning" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editModal<?php echo $row['id']; ?>">
                                        Edit
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteModal<?php echo $row['id']; ?>">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Class Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kelas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="kelas.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label for="nama_kelas" class="form-label">Nama Kelas</label>
                            <input type="text" class="form-control" id="nama_kelas" name="nama_kelas" required>
                        </div>
                        <div class="mb-3">
                            <label for="wali_kelas_id" class="form-label">Wali Kelas</label>
                            <select class="form-select" id="wali_kelas_id" name="wali_kelas_id" required>
                                <option value="">Pilih Wali Kelas...</option>
                                <?php 
                                $result_guru->data_seek(0);
                                while($guru = $result_guru->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $guru['id']; ?>">
                                    <?php echo htmlspecialchars($guru['nama']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tahun_ajaran" class="form-label">Tahun Ajaran</label>
                            <input type="text" class="form-control" id="tahun_ajaran" name="tahun_ajaran" 
                                   placeholder="2023/2024" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit and Delete Modals for each class -->
    <?php 
    $result_kelas->data_seek(0);
    while($row = $result_kelas->fetch_assoc()): 
    ?>
    <!-- Modal structures similar to add modal -->
    <?php endwhile; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>