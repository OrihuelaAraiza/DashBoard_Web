<?php
include('conexion.php');

$sql = "SELECT ID, Nombre FROM asesor";
$result = $conn->query($sql);

$asesores = array();
while ($row = $result->fetch_assoc()) {
    $asesores[] = $row;
}

echo json_encode($asesores);
$conn->close();
