<?php
session_start();
require_once 'config/db.php';

// Cek login
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Proses tambah/edit/hapus siswa
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $nis = trim($_POST['nis']);
                $nama = trim($_POST['nama_siswa']);
                $jk = $_POST['jenis_kelamin'];
                $kelas = $_POST['kelas_id'];
                $alamat = trim($_POST['alamat']);
                $hp = trim($_POST['nomor_hp']);

                $query = "INSERT INTO siswa (nis, nama_siswa, jenis_kelamin, kelas_id, alamat, nomor_hp) 
                         VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("sssiss", $nis, $nama, $jk, $kelas, $alamat, $hp);
                $stmt->execute();
                break;

            case 'edit':
                $id = $_POST['siswa_id'];
                $nis = trim($_POST['nis']);
                $nama = trim($_POST['nama_siswa']);
                $jk = $_POST['jenis_kelamin'];
                $kelas = $_POST['kelas_id'];
                $alamat = trim($_POST['alamat']);
                $hp = trim($_POST['nomor_hp']);

                $query = "UPDATE siswa SET nis=?, nama_siswa=?, jenis_kelamin=?, 
                         kelas_id=?, alamat=?, nomor_hp=? WHERE id=?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("sssissi", $nis, $nama, $jk, $kelas, $alamat, $hp, $id);
                $stmt->execute();
                break;

            case 'delete':
                $id = $_POST['siswa_id'];
                $query = "DELETE FROM siswa WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                break;
        }
        header("Location: siswa.php");
        exit();
    }
}

// Ambil data kelas untuk dropdown
$query_kelas = "SELECT * FROM kelas ORDER BY nama_kelas";
$result_kelas = $conn->query($query_kelas);

// Ambil data siswa
$query_siswa = "SELECT s.*, k.nama_kelas 
                FROM siswa s 
                LEFT JOIN kelas k ON s.kelas_id = k.id 
                ORDER BY k.nama_kelas, s.nama_siswa";
$result_siswa = $conn->query($query_siswa);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa - Sistem Absensi</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Data Siswa</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                Tambah Siswa
            </button>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Jenis Kelamin</th>
                                <th>No. HP</th>
                                <th>Alamat</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result_siswa->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nis']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_siswa']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_kelas']); ?></td>
                                <td><?php echo htmlspecialchars($row['jenis_kelamin']); ?></td>
                                <td><?php echo htmlspecialchars($row['nomor_hp']); ?></td>
                                <td><?php echo htmlspecialchars($row['alamat']); ?></td>
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

    <!-- Modal Tambah Siswa -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="siswa.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label for="nis" class="form-label">NIS</label>
                            <input type="text" class="form-control" id="nis" name="nis" required>
                        </div>
                        <div class="mb-3">
                            <label for="nama_siswa" class="form-label">Nama Siswa</label>
                            <input type="text" class="form-control" id="nama_siswa" name="nama_siswa" required>
                        </div>
                        <div class="mb-3">
                            <label for="kelas_id" class="form-label">Kelas</label>
                            <select class="form-select" id="kelas_id" name="kelas_id" required>
                                <?php 
                                $result_kelas->data_seek(0);
                                while($kelas = $result_kelas->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $kelas['id']; ?>">
                                    <?php echo htmlspecialchars($kelas['nama_kelas']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jenis Kelamin</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="jenis_kelamin" 
                                           id="jk_l" value="L" required>
                                    <label class="form-check-label" for="jk_l">Laki-laki</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="jenis_kelamin" 
                                           id="jk_p" value="P" required>
                                    <label class="form-check-label" for="jk_p">Perempuan</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="nomor_hp" class="form-label">Nomor HP</label>
                            <input type="text" class="form-control" id="nomor_hp" name="nomor_hp">
                        </div>
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3"></textarea>
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

    <!-- Modal Edit dan Delete untuk setiap siswa -->
    <?php 
    $result_siswa->data_seek(0);
    while($row = $result_siswa->fetch_assoc()): 
    ?>
    <!-- Modal Edit -->
    <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="siswa.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="siswa_id" value="<?php echo $row['id']; ?>">
                        <!-- Form fields similar to add modal but with values -->
                        <!-- [Previous form fields would be repeated here with values] -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Delete -->
    <div class="modal fade" id="deleteModal<?php echo $row['id']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Hapus Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Anda yakin ingin menghapus data siswa <?php echo htmlspecialchars($row['nama_siswa']); ?>?</p>
                </div>
                <div class="modal-footer">
                    <form action="siswa.php" method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="siswa_id" value="<?php echo $row['id']; ?>">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
    <?php include 'includes/footer.php'; ?>