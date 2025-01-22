<?php
// includes/navbar.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Sistem Absensi</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" 
                       href="index.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'ambil-absen.php') ? 'active' : ''; ?>" 
                       href="ambil-absen.php">Input Absensi</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'data-siswa.php') ? 'active' : ''; ?>" 
                       href="data-siswa.php">Data Siswa</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'kelas.php') ? 'active' : ''; ?>" 
                       href="kelas.php">Data Kelas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'lap-absensi.php') ? 'active' : ''; ?>" 
                       href="lap-absensi.php">Laporan</a>
                </li>
            </ul>
            <div class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" 
                       data-bs-toggle="dropdown">
                        <?php echo htmlspecialchars($_SESSION['nama']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profil.php">Profil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">Keluar</a></li>
                    </ul>
                </li>
            </div>
        </div>
    </div>
</nav>