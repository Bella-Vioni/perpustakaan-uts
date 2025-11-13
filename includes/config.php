<?php

// user pass database
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'db_perpus';

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($mysqli->connect_error) {
    die("koneksi gagal: ". $mysqli->connect_error);
}
?>