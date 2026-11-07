<?php
include 'config.php';

$page = $_GET['page'] ?? '';
$id = $_GET['id'] ?? '';

$menu = ['balita', 'ibu_hamil', 'lansia', 'pemeriksaan', 'imunisasi', 'penimbangan'];

if (in_array($page, $menu) && $id != '') {

    $cek = mysqli_query($conn, "SHOW KEYS FROM `$page` WHERE Key_name='PRIMARY'");
    $pk = mysqli_fetch_assoc($cek);
    $kolom = $pk['Column_name'];

    $stmt = $conn->prepare("DELETE FROM `$page` WHERE `$kolom` = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();

    $jumlah = mysqli_query($conn, "SELECT COUNT(*) AS total FROM `$page`");
    $data = mysqli_fetch_assoc($jumlah);

    if ($data['total'] == 0) {
        mysqli_query($conn, "ALTER TABLE `$page` AUTO_INCREMENT = 1");
    }
}

header("Location: index.php?page=" . urlencode($page));
exit;
?>