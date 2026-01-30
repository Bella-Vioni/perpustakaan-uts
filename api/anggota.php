<?php
header("Content-Type: application/json");
require "../includes/config.php";

function response($status, $msg, $data = null) {
    echo json_encode([
        "status"  => $status,
        "message" => $msg,
        "data"    => $data
    ]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];


if ($method === "GET") {

    if (isset($_GET['id'])) {

        $id = intval($_GET['id']);
        $stmt = $mysqli->prepare("
            SELECT id_anggota, nama_lengkap, email, alamat, no_telepon, foto_profil
            FROM anggota
            WHERE id_anggota = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $anggota = $stmt->get_result()->fetch_assoc();

        if (!$anggota) {
            response("error", "Anggota tidak ditemukan");
        }

        $anggota['foto_profil'] = $anggota['foto_profil']
            ? "http://localhost/perpustakaan-uts/uploads/users/" . $anggota['foto_profil']
            : null;

        response("success", "Detail anggota ditemukan", $anggota);
    }

    
    $result = $mysqli->query("
        SELECT id_anggota, nama_lengkap, email, alamat, no_telepon, foto_profil
        FROM anggota
        ORDER BY id_anggota DESC
    ");

    $list = [];
    while ($row = $result->fetch_assoc()) {
        $row['foto_profil'] = $row['foto_profil']
            ? "http://localhost/perpustakaan-uts/uploads/users/" . $row['foto_profil']
            : null;
        $list[] = $row;
    }

    response("success", "Daftar anggota", $list);
}

if ($method === "POST") {

    $nama   = $_POST['nama_lengkap'] ?? '';
    $email  = $_POST['email'] ?? '';
    $pass   = $_POST['password'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $telp   = $_POST['no_telepon'] ?? '';

    if (!$nama || !$email || !$pass) {
        response("error", "Data wajib tidak lengkap");
    }

    
    $password = password_hash($pass, PASSWORD_DEFAULT);

   
    $foto_name = null;

    if (!empty($_FILES['foto_profil']['name'])) {

        $upload_dir = __DIR__ . "/../uploads/foto-profil/";

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $ext = pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION);
        $foto_name = time() . "_" . uniqid() . "." . $ext;

        move_uploaded_file(
            $_FILES['foto_profil']['tmp_name'],
            $upload_dir . $foto_name
        );
    }

   
    $stmt = $mysqli->prepare("
        INSERT INTO anggota 
        (nama_lengkap, email, password, alamat, no_telepon, foto_profil)
        VALUES (?,?,?,?,?,?)
    ");

    $stmt->bind_param(
        "ssssss",
        $nama,
        $email,
        $password,
        $alamat,
        $telp,
        $foto_name
    );

    $stmt->execute();

    response("success", "Anggota berhasil ditambahkan");
}


response("error", "Method tidak didukung");
