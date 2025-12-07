<?php
$servidor_db = "localhost";
$usuario_db = "root";
$clave_db = "12345678";
$nombre_db   = "terminal_rapido_ochoa_db";

$conn = new mysqli($servidor_db, $usuario_db, $clave_db, $nombre_db);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");
