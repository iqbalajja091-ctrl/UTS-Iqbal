
<?php

header("Content-Type: application/json");

require_once "koneksi.php";

$response = [
    "status" => true,
    "pesan" => "Koneksi database SIPAM RT berhasil!"
];

echo json_encode($response);

?>
