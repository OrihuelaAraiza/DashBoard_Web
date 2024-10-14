<?php
$servername = "localhost";
$username = "Pantera";
$password = "root";
$dbname = "panteras2";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
