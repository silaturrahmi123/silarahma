<?php
include 'config.php';

// Simpan data balita
if (isset($_POST['simpan'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $nik = mysqli_real_escape_string($conn, $_POST['nik']);
    $jk = mysqli_real_escape_string($conn, $_POST['jk']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $ortu = mysqli_real_escape_string($conn, $_POST['ortu']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);

    $sql = "INSERT INTO balita
    (nama_balita, nik, jenis_kelamin, tanggal_lahir, nama_orang_tua, alamat)
    VALUES ('$nama', '$nik', '$jk', '$tanggal', '$ortu', '$alamat')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Pendaftaran berhasil!'); 
        window.location='data_balita.php';</script>";
        exit;
    } else {
        echo "Gagal menyimpan: " . mysqli_error($conn);
    }
}

// Ambil data
$data = mysqli_query($conn, "SELECT * FROM balita ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data Balita Posyandu</title>
<style>
body {
    font-family: Arial;
    background: #f2f6f5;
    padding: 25px;
}
.container {
    background: white;
    padding: 20px;
    border-radius: 10px;
}
input, select, textarea {
    width: 100%;
    padding: 10px;
    margin: 7px 0 15px;
    box-sizing: border-box;
}
button {
    background: #16865b;
    color: white;
    padding: 12px 20px;
    border: none;
    cursor: pointer;
    border-radius: 5px;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
th, td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: left;
}
th {
    background: #16865b;
    color: white;
}
</style>
</head>
<body>

<div class="container">

<h2>Pendaftaran Data Balita</h2>

<form method="POST">

<label>Nama Balita</label>
<input type="text" name="nama" required>

<label>NIK</label>
<input type="text" name="nik" required>

<label>Jenis Kelamin</label>
<select name="jk" required>
<option value="">Pilih Jenis Kelamin</option>
<option value="Laki-laki">Laki-laki</option>
<option value="Perempuan">Perempuan</option>
</select>

<label>Tanggal Lahir</label>
<input type="date" name="tanggal" required>

<label>Nama Orang Tua</label>
<input type="text" name="ortu" required>

<label>Alamat</label>
<textarea name="alamat" required></textarea>

<button type="submit" name="simpan">Simpan Pendaftaran</button>

</form>

<hr>

<h2>Daftar Data Balita</h2>

<table>
<tr>
<th>No</th>
<th>Nama Balita</th>
<th>NIK</th>
<th>Jenis Kelamin</th>
<th>Tanggal Lahir</th>
<th>Orang Tua</th>
<th>Alamat</th>
</tr>

<?php
$no = 1;
while ($row = mysqli_fetch_assoc($data)) {
?>
<tr>
<td><?= $no++ ?></td>
<td><?= htmlspecialchars($row['nama_balita']) ?></td>
<td><?= htmlspecialchars($row['nik']) ?></td>
<td><?= htmlspecialchars($row['jenis_kelamin']) ?></td>
<td><?= htmlspecialchars($row['tanggal_lahir']) ?></td>
<td><?= htmlspecialchars($row['nama_orang_tua']) ?></td>
<td><?= htmlspecialchars($row['alamat']) ?></td>
</tr>
<?php } ?>

</table>

</div>
</body>
</html>