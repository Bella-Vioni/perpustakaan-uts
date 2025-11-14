<?php
// proteksi agar file tidak dapat diakses langsung
if (!defined('MY_APP')) {
    die('Akses langsung tidak diperbolehkan!');
}

$pesan = "";
$pesan_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $judul_buku  = $_POST['judul_buku'];
    $penulis     = $_POST['penulis'];
    $penerbit    = $_POST['penerbit'];
    $tahun_terbit = $_POST['tahun_terbit'];
    $stok        = $_POST['stok'];

    // proses upload cover
    $cover_name = null;
    if (!empty($_FILES['cover']['name'])) {

        $target_dir = "uploads/buku/"; // buat directory baru uploads/buku
        $file_name  = time() . "-" . basename($_FILES['cover']['name']); // agar tidak bentrok
        $target_file = $target_dir . $file_name;

        // upload file
        if (move_uploaded_file($_FILES['cover']['tmp_name'], $target_file)) {
            $cover_name = $file_name;
        }
    }

    // proses insert ke database
    $sql = "INSERT INTO buku (judul, penulis, penerbit, tahun_terbit, stok, cover_buku) VALUES (?, ?, ?, ?, ?, ?)";

    if ($stmt = $mysqli->prepare($sql)) {

        $stmt->bind_param("ssssss", $judul_buku, $penulis, $penerbit, $tahun_terbit, $stok, $cover_name);

        if ($stmt->execute()) {

            $id_buku = $stmt->insert_id; // ambil id buku setelah insert

            // proses kategori (checkbox)
            if (!empty($_POST['kategori'])) {

                foreach ($_POST['kategori'] as $id_kategori) {
                    $mysqli->query("INSERT INTO buku_kategori (id_buku, id_kategori) VALUES ($id_buku, $id_kategori)");
                }
            }

            $pesan = "Buku Berhasil di Tambahkan";
        } else {
            $pesan_error = "Gagal Menambahkan Buku";
        }

        $stmt->close();
    }
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Buku</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Tambah Buku</li>
    </ol>

    <?php if (!empty($pesan)) : ?>
        <div class="alert alert-success" role="alert">
            <?php echo $pesan ?>
        </div>
    <?php endif ?>

    <?php if (!empty($pesan_error)) : ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $pesan_error ?>
        </div>
    <?php endif ?>

    <div class="card mb-4">
        <div class="card-body">

            <form method="post" enctype="multipart/form-data">

                <div class="mb-3">
                    <label for="judul_buku" class="form-label">Judul Buku</label>
                    <input type="text" name="judul_buku" class="form-control" id="judul_buku" required>
                </div>

                <!-- kategori -->
                <div class="mb-3">
                    <label for="kategori" class="form-label">Pilih Kategori</label><br>

                    <label class="me-3">
                        <?php
                        // query kategori
                        $sql_kat = "SELECT * FROM kategori ORDER BY nama_kategori ASC";
                        $result_kat = $mysqli->query($sql_kat);
                        ?>

                        <?php while ($kat = $result_kat->fetch_assoc()) : ?>
                            <input type="checkbox" name="kategori[]" 
                                   value="<?php echo $kat['id_kategori'] ?>">
                            <?php echo $kat['nama_kategori'] ?>
                        <?php endwhile; ?>

                        <?php $mysqli->close(); ?>
                    </label>
                </div>

                <div class="mb-3">
                    <label for="penulis" class="form-label">Penulis</label>
                    <input type="text" name="penulis" class="form-control" id="penulis" required>
                </div>

                <div class="mb-3">
                    <label for="penerbit" class="form-label">Penerbit</label>
                    <input type="text" name="penerbit" class="form-control" id="penerbit" required>
                </div>

                <div class="mb-3">
                    <label for="tahun_terbit" class="form-label">Tahun Terbit</label>
                    <input type="text" name="tahun_terbit" class="form-control" id="tahun_terbit" required>
                </div>

                <div class="mb-3">
                    <label for="stok" class="form-label">Stok</label>
                    <input type="text" name="stok" class="form-control" id="stok" required>
                </div>

                <!-- upload cover -->
                <div class="mb-4">
                    <label for="cover" class="form-label">Upload Cover</label>
                    <input type="file" name="cover" class="form-control" id="cover">
                    <!-- cover opsional -->
                </div>

                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="index.php?hal=daftar-buku" class="btn btn-danger">Kembali</a>

            </form>

        </div>
    </div>
</div>
