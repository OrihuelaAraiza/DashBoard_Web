<?php
include('conexion.php');

$sql = "SELECT ID, Nombre FROM categoria";
$result = $conn->query($sql);

$categorias = array();
while ($row = $result->fetch_assoc()) {
    $categorias[] = $row;
}

echo json_encode($categorias);
$conn->close();
