<?php
session_start();
include 'config.php';

$menu = [
    'dashboard' => 'Dashboard',
    'balita' => 'Data Balita',
    'ibu_hamil' => 'Data Ibu Hamil',
    'lansia' => 'Data Lansia',
    'pemeriksaan' => 'Data Pemeriksaan',
    'imunisasi' => 'Data Imunisasi',
    'penimbangan' => 'Data Penimbangan',
    'jadwal_posyandu' => 'Jadwal Posyandu'
];

// Data untuk grafik
$namaData = [
    'Balita' => 'balita',
    'Ibu Hamil' => 'ibu_hamil',
    'Lansia' => 'lansia',
    'Pemeriksaan' => 'pemeriksaan',
    'Imunisasi' => 'imunisasi',
    'Penimbangan' => 'penimbangan'
];

$jumlahData = [];

foreach ($namaData as $nama => $tabel) {
    $hasil = mysqli_query($conn, "SELECT COUNT(*) AS total FROM `$tabel`");

    if ($hasil) {
        $row = mysqli_fetch_assoc($hasil);
        $jumlahData[$nama] = (int)$row['total'];
    } else {
        $jumlahData[$nama] = 0;
    }
}

foreach ($namaData as $nama => $tabel) {
    $hasil = mysqli_query($conn, "SELECT COUNT(*) AS total FROM `$tabel`");
    $row = mysqli_fetch_assoc($hasil);
    $jumlahData[$nama] = (int)$row['total'];
}


$page = $_GET['page'] ?? 'beranda';
if (isset($menu[$page]) && !isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
$aksi = $_GET['aksi'] ?? '';

function aman($v) {
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
}

function jumlah($conn, $tabel) {
    $q = mysqli_query($conn, "SELECT COUNT(*) total FROM `$tabel`");
    return $q ? mysqli_fetch_assoc($q)['total'] : 0;
}

function kolomTabel($conn, $tabel) {
    $q = mysqli_query($conn, "SHOW COLUMNS FROM `$tabel`");
    return $q ? mysqli_fetch_all($q, MYSQLI_ASSOC) : [];
}

if (!isset($menu[$page]) && !in_array($page, ['beranda','profil','layanan','jadwal','kontak'])) {
    $page = 'beranda';
}

$pesan = '';
$kolom = [];
$kolomInput = [];

if (isset($menu[$page]) && $page != 'dashboard') {
    $kolom = kolomTabel($conn, $page);

    foreach ($kolom as $k) {
        if (strpos($k['Extra'], 'auto_increment') !== false ||
            strpos($k['Extra'], 'GENERATED') !== false) {
            continue;
        }
        $kolomInput[] = $k;
    }
}

if (isset($_POST['simpan_data']) && isset($menu[$page]) && $page != 'dashboard') {
    $fields = [];
    $values = [];

    foreach ($kolomInput as $k) {
        $nama = $k['Field'];
        $key = 'field_' . $nama;

        if (isset($_POST[$key])) {
            $fields[] = $nama;
            $values[] = trim($_POST[$key]);
        } elseif ($k['Null'] == 'NO' && $k['Default'] === null) {
            $pesan = "Kolom " . $nama . " wajib diisi.";
            break;
        }
    }

    if ($pesan == '' && count($fields) > 0) {
        $namaFields = '`' . implode('`,`', $fields) . '`';
        $tanda = implode(',', array_fill(0, count($values), '?'));
        $sql = "INSERT INTO `$page` ($namaFields) VALUES ($tanda)";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            $types = str_repeat('s', count($values));
            $params = [$types];

            foreach ($values as $i => $v) {
                $params[] = &$values[$i];
            }

            call_user_func_array([$stmt, 'bind_param'], $params);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: index.php?page=$page&status=sukses");
                exit;
            } else {
                $pesan = "Gagal menyimpan: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $pesan = "Kesalahan database: " . mysqli_error($conn);
        }
    }
}

$halamanAdmin = isset($menu[$page]);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Posyandu Desa | Pelayanan Kesehatan Masyarakat</title>

<style>
* {box-sizing:border-box}
body {
    margin:0;
    font-family:Arial, sans-serif;
    color:#24352d;
    background:#f6f8f6;
}
a {text-decoration:none}
.navbar {
    background:white;
    padding:17px 7%;
    display:flex;
    align-items:center;
    justify-content:space-between;
    box-shadow:0 2px 12px #0000000b;
    position:sticky;
    top:0;
    z-index:10;
}
.brand {
    display:flex;
    align-items:center;
    gap:12px;
    font-weight:bold;
    color:#176b46;
    font-size:19px;
}
.logo {
    background:#176b46;
    color:white;
    padding:11px;
    border-radius:9px;
}
.nav-links {
    display:flex;
    align-items:center;
    gap:25px;
}
.nav-links a {
    color:#47574e;
    font-size:14px;
    font-weight:bold;
}
.nav-links a:hover {color:#16804f}
.nav-admin {
    background:#176b46;
    color:white!important;
    padding:11px 17px;
    border-radius:6px;
}
.hero {
    background:linear-gradient(110deg,#124f36,#218354);
    color:white;
    padding:85px 8%;
    min-height:390px;
    display:flex;
    align-items:center;
}
.hero-content {max-width:650px}
.hero small {
    color:#cce9d7;
    letter-spacing:2px;
    font-weight:bold;
}
.hero h1 {
    font-size:42px;
    line-height:1.2;
    margin:18px 0;
}
.hero p {
    color:#e0eee5;
    line-height:1.8;
    font-size:16px;
}
.hero-btn {
    display:inline-block;
    background:white;
    color:#176b46;
    padding:13px 22px;
    border-radius:6px;
    font-weight:bold;
    margin-top:15px;
}
.section {
    padding:65px 8%;
}
.section-title {
    text-align:center;
    margin-bottom:35px;
}
.section-title h2 {
    color:#185d3d;
    font-size:28px;
}
.section-title p {color:#718078}
.stats {
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:20px;
    margin-top:-35px;
    position:relative;
}
.stat-card {
    background:white;
    padding:25px;
    border-radius:10px;
    box-shadow:0 5px 20px #0000000b;
    border-bottom:3px solid #218354;
}
.stat-card h2 {
    color:#176b46;
    font-size:30px;
    margin:8px 0;
}
.stat-card p {color:#78857e;margin:0}
.services {
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:20px;
}
.service {
    background:white;
    padding:25px;
    border-radius:9px;
    border:1px solid #e8eee9;
}
.service h3 {color:#176b46}
.service p {color:#718078;line-height:1.7}
.about {
    background:#eaf3ed;
}
.about-content {
    max-width:800px;
    margin:auto;
    text-align:center;
    line-height:1.9;
}
.footer {
    background:#123f2d;
    color:#dcebe2;
    padding:30px 8%;
    text-align:center;
    font-size:14px;
}
.sidebar {
    width:235px;
    height:100vh;
    position:fixed;
    top:0;
    left:0;
    background:#174e37;
    padding:25px 17px;
    color:white;
    overflow-y:auto;
}
.sidebar h2 {font-size:20px;margin-bottom:35px}
.sidebar small {color:#b7d5c3}
.sidebar a {
    display:block;
    padding:13px;
    color:#e2eee6;
    border-radius:6px;
    margin:5px 0;
    font-size:14px;
}
.sidebar a:hover,.sidebar a.aktif {
    background:#277d53;
    color:white;
}
.admin-content {
    margin-left:235px;
    padding:30px;
}
.admin-top {
    background:white;
    padding:18px 23px;
    border-radius:8px;
    margin-bottom:25px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}
.admin-card {
    display:inline-block;
    width:205px;
    background:white;
    padding:22px;
    margin:8px;
    border-radius:9px;
    border:1px solid #e8eee9;
}
.admin-card h2 {color:#176b46;font-size:29px}
.table-wrap {overflow-x:auto}
table {
    width:100%;
    border-collapse:collapse;
    background:white;
    margin-top:20px;
    border-radius:8px;
    overflow:hidden;
}
th,td {
    padding:13px;
    text-align:left;
    border-bottom:1px solid #e8eee9;
    font-size:13px;
}
th {background:#eaf3ed;color:#205b3d}
.form-box {
    background:white;
    padding:25px;
    border-radius:9px;
    max-width:700px;
    margin-top:20px;
}
.form-box label {display:block;font-weight:bold;font-size:14px}
.form-box input,.form-box select,.form-box textarea {
    width:100%;
    padding:12px;
    border:1px solid #d8e1da;
    border-radius:6px;
    margin:8px 0 18px;
}
.btn {
    display:inline-block;
    padding:12px 18px;
    border:0;
    background:#176b46;
    color:white;
    border-radius:6px;
    cursor:pointer;
}
.btn:hover {background:#104d32}
.btn-grey {background:#777}
.alert {
    background:#e3f3e8;
    color:#176b46;
    padding:14px;
    border-radius:6px;
    margin:15px 0;
}
@media(max-width:800px) {
    .navbar {padding:15px 5%;flex-wrap:wrap;gap:15px}
    .nav-links {gap:12px;flex-wrap:wrap}
    .hero {padding:60px 7%}
    .hero h1 {font-size:32px}
    .stats,.services {grid-template-columns:1fr}
    .sidebar {width:100%;height:auto;position:relative}
    .admin-content {margin:0;padding:15px}
    .admin-card {width:100%;margin:7px 0}
}

.services .service img {
    width: 100% !important;
    height: 150px !important;
    max-height: 150px !important;
    object-fit: cover !important;
    display: block;
    border-radius: 10px;
}

.services .service {
    overflow: hidden;
}


    

.profil-overlay {
    color: white;
    max-width: 600px;
}

.profil-overlay h2 {
    font-size: 30px;
}

.profil-overlay p {
    font-size: 16px;
    line-height: 1.7;
}

.profil-banner {
    width: 100%;
    min-height: 100vh;
    background-image:
        linear-gradient(rgba(0,0,0,0.25), rgba(0,0,0,0.25)),
        url("gambar/posyandu_baru.jpg");
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;

    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
    padding: 30px;
    box-sizing: border-box;
}

.profil-overlay {
    color: white;
    max-width: 800px;
    text-shadow: 2px 2px 5px black;
}

.profil-overlay h2 {
    font-size: 45px;
    color: white;
}

.profil-overlay p {
    font-size: 20px;
    line-height: 1.7;
    color: white;
}
</style>
</head>

<body>

<?php if ($page == 'beranda' || in_array($page, ['profil','layanan','jadwal','kontak'])): ?>

<header class="navbar">
    <div class="brand">
        <span class="logo">PD</span>
        <span>POSYANDU DESA</span>
    </div>
    <nav class="nav-links">
        <a href="index.php">Beranda</a>
        <a href="index.php?page=profil">Profil</a>
        <a href="index.php?page=layanan">Layanan</a>
      
        <a href="index.php?page=kontak">Kontak</a>
        <a class="nav-admin" href="login.php">Login Admin</a>
    </nav>
</header>

<?php if ($page == 'beranda'): ?>

<section class="hero">
    <div class="hero-content">
        <small>PELAYANAN KESEHATAN MASYARAKAT</small>
        <h1>Posyandu Desa untuk Masyarakat yang Lebih Sehat</h1>
        <p>
            Memberikan informasi pelayanan kesehatan ibu dan anak,
            pemantauan tumbuh kembang balita, serta pelayanan kesehatan
            masyarakat desa.
        </p>
        <a href="index.php?page=layanan" class="hero-btn">Lihat Layanan</a>
    </div>
</section>
<section class="section">
    <div class="section-title">
        <h2>Jadwal Kegiatan Posyandu</h2>
        <p>Informasi jadwal pelayanan Posyandu Desa.</p>
    </div>

    <?php
    $jadwal = mysqli_query($conn, "SELECT * FROM jadwal_posyandu ORDER BY tanggal ASC");
    ?>

    <?php if ($jadwal && mysqli_num_rows($jadwal) > 0): ?>
        <?php while ($j = mysqli_fetch_assoc($jadwal)): ?>
            <div class="form-box">
                <h3><?= date('d-m-Y', strtotime($j['tanggal'])) ?></h3>
                <p>Waktu: <?= aman($j['waktu']) ?></p>
                <p>Lokasi: <?= aman($j['lokasi']) ?></p>
                <p>Kegiatan: <?= aman($j['kegiatan']) ?></p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Belum ada jadwal Posyandu.</p>
    <?php endif; ?>
</section>

<section class="section">
    <div class="section-title">
        <h2>Grafik Data Posyandu</h2>
        <p>Jumlah data pelayanan Posyandu Desa.</p>
    </div>

    <div style="background:white; padding:20px; border-radius:15px;">
        <canvas id="grafikPosyandu"></canvas>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const grafik = document.getElementById('grafikPosyandu');

new Chart(grafik, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_keys($jumlahData)) ?>,
        datasets: [{
            label: 'Jumlah Data',
            data: <?= json_encode(array_values($jumlahData)) ?>,
            backgroundColor: [
                '#36A2EB',
                '#FF6384',
                '#4BC0C0',
                '#FFCE56',
                '#9966FF',
                '#FF9F40'
            ],
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        scales: {
    y: {
        beginAtZero: true,
        suggestedMax: 10,
        ticks: {
            stepSize: 1
        }
    }
}
    }
});
</script>

<section class="section">
    <div class="stats">
        <div class="stat-card">
            <p>Data Balita</p>
            <h2><?= jumlah($conn,'balita') ?></h2>
            <p>Jumlah balita terdaftar</p>
        </div>
        <div class="stat-card">
            <p>Data Ibu Hamil</p>
            <h2><?= jumlah($conn,'ibu_hamil') ?></h2>
            <p>Jumlah ibu hamil terdaftar</p>
        </div>
        <div class="stat-card">
            <p>Data Lansia</p>
            <h2><?= jumlah($conn,'lansia') ?></h2>
            <p>Jumlah lansia terdaftar</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="section-title">
        <h2>Layanan Posyandu</h2>
        <p>Pelayanan kesehatan untuk seluruh masyarakat desa.</p>
    </div>
    <div class="services">
        <div class="service">
            <h3>Kesehatan Balita</h3>
             <img src="/posyandu_Desa/gambar/balita.jpg" alt="Pelayanan Balita">
            <p>Pemantauan pertumbuhan, perkembangan, dan kesehatan anak.</p>
        </div>
        <div class="service">
            <h3>Kesehatan Ibu Hamil</h3>
             <img src="/posyandu_Desa/gambar/download.jpg" alt="Pelayanan Ibu Hamil">
            <p>Pelayanan dan pemantauan kesehatan selama masa kehamilan.</p>
        </div>
        <div class="service">
            <h3>Kesehatan Lansia</h3>
            <img src="/posyandu_Desa/gambar/lansia.jpg" alt="Pelayanan Lansia">
            <p>Pemeriksaan dan pemantauan kesehatan bagi warga lanjut usia.</p>
        </div>
        <div class="service">
            <h3>Imunisasi</h3>
            <img src="/posyandu_Desa/gambar/imunisasi.jpg" alt="Pelayanan Imunisasi">
            <p>Pencatatan pelayanan imunisasi bagi balita.</p>
        </div>
        <div class="service">
            <h3>Penimbangan</h3>
            <img src="/posyandu_Desa/gambar/penimbangan.jpg" alt="Penimbangan">
            <p>Pencatatan berat badan dan pertumbuhan balita.</p>
        </div>
        <div class="service">
            <h3>Pemeriksaan</h3>
             <img src="/posyandu_Desa/gambar/pemeriksaan.jpg" alt="Pemeriksaan Kesehatan">
            <p>Pencatatan kegiatan pemeriksaan kesehatan masyarakat.</p>
        </div>
    </div>
</section>

<section class="section about">
    <div class="about-content">
        <h2>Tentang Posyandu Desa</h2>
        <p>
            Posyandu merupakan salah satu bentuk pelayanan kesehatan
            berbasis masyarakat yang membantu meningkatkan kualitas
            kesehatan ibu, anak, dan masyarakat melalui kegiatan
            pemantauan serta pelayanan kesehatan secara berkala.
        </p>
    </div>
</section>

<?php elseif ($page == 'profil'): ?>

<div class="profil-banner">
    <div class="profil-overlay">
        <h2>Selamat Datang di Posyandu Desa</h2>
        <p>
            Posyandu Desa hadir memberikan pelayanan kesehatan
            bagi ibu, anak, balita, dan lansia.
        </p>
        <p><b>Sehat Bersama, Tumbuh Bersama ❤️</b></p>
    </div>
</div>
</div>

    <div class="about-content">
        <h2>Selamat Datang di Posyandu Desa</h2>
       
    
        <p>
            Posyandu Desa merupakan salah satu bentuk pelayanan
            kesehatan masyarakat yang bertujuan membantu meningkatkan
            kesehatan ibu, bayi, balita, lansia, dan seluruh masyarakat.
        </p>
        <p>
            Melalui kegiatan Posyandu, masyarakat dapat memperoleh
            pelayanan kesehatan, pemantauan tumbuh kembang anak,
            pemeriksaan ibu hamil, imunisasi, serta penyuluhan kesehatan.
        </p>
    </div>

    <div class="services">
        <div class="service">
            <h3>🎯 Visi</h3>
            <p>
                Mewujudkan masyarakat desa yang sehat, mandiri,
                dan memiliki kesadaran tinggi terhadap pentingnya kesehatan.
            </p>
        </div>

        <div class="service">
            <h3>🌿 Misi</h3>
            <p>
                Meningkatkan pelayanan kesehatan ibu dan anak,
                memberikan edukasi kesehatan, serta mendorong
                partisipasi masyarakat dalam kegiatan Posyandu.
            </p>
        </div>

        <div class="service">
            <h3>❤️ Tujuan</h3>
            <p>
                Membantu meningkatkan kualitas kesehatan masyarakat
                melalui pelayanan yang mudah dijangkau dan berkelanjutan.
            </p>
        </div>
    </div>

    <div class="about-content">
        <h2>Pelayanan Kami</h2>
        <ul>
            <li>Pelayanan kesehatan ibu hamil</li>
            <li>Pemantauan kesehatan dan tumbuh kembang balita</li>
            <li>Imunisasi</li>
            <li>Penimbangan balita</li>
            <li>Pelayanan kesehatan lansia</li>
            <li>Penyuluhan kesehatan masyarakat</li>
        </ul>
    </div>
</section>

<?php elseif ($page == 'layanan'): ?>

<section class="section">
    <div class="section-title">
        <h2>Informasi Layanan</h2>
    </div>

    <div class="services">

        <div class="service">
            <img src="/posyandu_Desa/gambar/balita.jpg" alt="Pelayanan Balita">
            <h3>Pelayanan Balita</h3>
            <p>Pemantauan pertumbuhan dan perkembangan anak.</p>
        </div>

        <div class="service">
            <img src="/posyandu_Desa/gambar/download.jpg" alt="Pelayanan Ibu Hamil">
            <h3>Pelayanan Ibu Hamil</h3>
            <p>Pemantauan kesehatan ibu selama masa kehamilan.</p>
        </div>

        <div class="service">
            <img src="/posyandu_Desa/gambar/lansia.jpg" alt="Pelayanan Lansia">
            <h3>Pelayanan Lansia</h3>
            <p>Pemeriksaan kesehatan warga lanjut usia.</p>
        </div>

        <div class="service">
            <img src="/posyandu_Desa/gambar/imunisasi.jpg" alt="Pelayanan Imunisasi">
            <h3>Pelayanan Imunisasi</h3>
            <p>Pemberian dan pencatatan kegiatan imunisasi.</p>
        </div>

        <div class="service">
            <img src="/posyandu_Desa/gambar/penimbangan.jpg" alt="Penimbangan">
            <h3>Penimbangan</h3>
            <p>Pencatatan berat badan dan tinggi badan balita.</p>
        </div>

        <div class="service">
            <img src="/posyandu_Desa/gambar/pemeriksaan.jpg" alt="Pemeriksaan Kesehatan">
            <h3>Pemeriksaan Kesehatan</h3>
            <p>Pemeriksaan dan pencatatan kesehatan masyarakat.</p>
        </div>

    </div>
</section>

<?php elseif ($page == 'jadwal_posyandu'): ?>

<section class="section">
    <div class="section-title">
        <h2>Jadwal Kegiatan Posyandu</h2>
        <p>Informasi jadwal kegiatan pelayanan Posyandu.</p>
    </div>
    <div class="about-content">
        <p>Untuk informasi hari, tanggal, dan lokasi kegiatan, silakan hubungi petugas Posyandu desa.</p>
    </div>
</section>

<?php elseif ($page == 'kontak'): ?>

<section class="section">
    <div class="section-title">
        <h2>Kontak Posyandu</h2>
        <p>Hubungi petugas untuk mendapatkan informasi lebih lanjut.</p>
    </div>

    <div class="about-content">
        <p>Alamat: WANASABA.</p>
        <p>WhatsApp: 085977802232</p>

        <a href="https://wa.me/6285977802232?text=Halo%20Petugas%20Posyandu,%20saya%20ingin%20bertanya%20tentang%20pelayanan%20Posyandu."
           target="_blank"
           rel="noopener noreferrer"
           class="btn">
            Hubungi Kami melalui WhatsApp
        </a>
    </div>
</section>


<?php endif; ?>

<footer class="footer">
    <p>© <?= date('Y') ?> Posyandu Desa. Pelayanan Kesehatan Masyarakat.</p>
</footer>

<?php else: ?>

<div class="sidebar">
    <h2>POSYANDU DESA</h2>
    <small>ADMINISTRATOR</small>
    <br><br>

    <?php foreach ($menu as $key => $nama): ?>
        <a class="<?= $page == $key ? 'aktif' : '' ?>"
           href="index.php?page=<?= $key ?>">
           <?= aman($nama) ?>
        </a>
    <?php endforeach; ?>
    <a href="logout.php" style="background:#dc3545; color:white;">
    Logout
</a>

    <br>
    <a href="index.php">Kembali ke Website</a>
</div>

<div class="admin-content">
    <div class="admin-top">
        <strong><?= aman($menu[$page]) ?></strong>
        <span>Panel Pengelolaan Posyandu</span>
    </div>

    <?php if ($page == 'dashboard'): ?>

        <h2>Dashboard</h2>
        <p>Ringkasan data pelayanan Posyandu Desa.</p>

        <?php foreach (['balita','ibu_hamil','lansia','pemeriksaan','imunisasi','penimbangan'] as $tabel): ?>
            <div class="admin-card">
                <p><?= aman(ucwords(str_replace('_',' ',$tabel))) ?></p>
                <h2><?= jumlah($conn,$tabel) ?></h2>
                <small>Total data tersimpan</small>
            </div>
        <?php endforeach; ?>

    <?php else: ?>

        <h2><?= aman($menu[$page]) ?></h2>
        <p>Kelola data <?= aman(strtolower($menu[$page])) ?>.</p>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'sukses'): ?>
            <div class="alert">Data berhasil disimpan.</div>
        <?php endif; ?>

        <?php if ($pesan != ''): ?>
            <div class="alert"><?= aman($pesan) ?></div>
        <?php endif; ?>

        <?php if ($aksi == 'tambah'): ?>

            <div class="form-box">
                <h3>Tambah <?= aman($menu[$page]) ?></h3>
                <form method="POST">

                <?php foreach ($kolomInput as $k):
                    $nama = $k['Field'];
                    $tipe = strtolower($k['Type']);
                    $wajib = ($k['Null'] == 'NO' && $k['Default'] === null);
                    ?>

                    <label><?= aman(ucwords(str_replace('_',' ',$nama))) ?></label>

                    <?php if (strpos($tipe,'enum(') === 0):
                        preg_match_all("/'((?:[^'\\\\]|\\\\.)*)'/",$k['Type'],$opsi);
                    ?>
                        <select name="field_<?= aman($nama) ?>" <?= $wajib ? 'required' : '' ?>>
                            <option value="">Pilih</option>
                            <?php foreach ($opsi[1] as $o): ?>
                                <option value="<?= aman(stripslashes($o)) ?>"><?= aman(stripslashes($o)) ?></option>
                            <?php endforeach; ?>
                        </select>

                    <?php elseif (strpos($tipe,'text') !== false): ?>
                        <textarea name="field_<?= aman($nama) ?>" rows="3" <?= $wajib ? 'required' : '' ?>></textarea>

                    <?php else:
                        if (strpos($tipe,'datetime') === 0 || strpos($tipe,'timestamp') === 0) {
                            $jenis = 'datetime-local';
                        } elseif (strpos($tipe,'date') === 0) {
                            $jenis = 'date';
                        } elseif (strpos($tipe,'int') !== false || strpos($tipe,'decimal') !== false || strpos($tipe,'float') !== false || strpos($tipe,'double') !== false) {
                            $jenis = 'number';
                        } else {
                            $jenis = 'text';
                        }
                    ?>
                        <input type="<?= $jenis ?>"
                               name="field_<?= aman($nama) ?>"
                               <?= $jenis == 'number' ? 'step="any"' : '' ?>
                               <?= $wajib ? 'required' : '' ?>>
                    <?php endif; ?>

                <?php endforeach; ?>

                    <button type="submit" name="simpan_data" class="btn">Simpan Data</button>
                    <a href="index.php?page=<?= aman($page) ?>" class="btn btn-grey">Kembali</a>
                </form>
            </div>

        <?php else: ?>

            <a href="index.php?page=<?= aman($page) ?>&aksi=tambah" class="btn">+ Tambah Data</a>

            <?php $hasil = mysqli_query($conn,"SELECT * FROM `$page`"); ?>

            <div class="table-wrap">
            <?php if ($hasil && mysqli_num_rows($hasil) > 0): ?>
                <table>
                    <tr>
                        <?php foreach (mysqli_fetch_fields($hasil) as $f): ?>
                            <th><?= aman($f->name) ?></th>
                        <?php endforeach; ?>
                        <th>Aksi</th>
                    </tr>
                    <?php while ($baris = mysqli_fetch_assoc($hasil)): ?>
                        <tr>
<?php foreach ($baris as $isi): ?>
    <td><?= aman($isi) ?></td>
<?php endforeach; ?>

<td>
    <a href="index.php?page=<?= $page ?>&aksi=edit&id=<?= $baris['id'] ?>">Edit</a>

    <a href="hapus.php?page=<?= $page ?>&id=<?= $baris['id'] ?>"
       onclick="return confirm('Yakin ingin menghapus data?')">Hapus</a>
</td>
</tr>
                        
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php else: ?>
                <p>Belum ada data yang tersimpan.</p>
            <?php endif; ?>
            </div>

        <?php endif; ?>
    <?php endif; ?>
</div>

<?php endif; ?>

</body>
</html>