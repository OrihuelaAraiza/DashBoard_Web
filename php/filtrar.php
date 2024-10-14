<?php
include('conexion.php');

$fechaInicio = $_GET['fechaInicio'];
$fechaFin = $_GET['fechaFin'];
$asesor = $_GET['asesor'];
$sede = $_GET['sede'];
$categoria = $_GET['categoria'];

$sql = "SELECT asesoria.ID, asesoria.Correo, asesoria.Fecha, asesoria.Duracion, categoria.Nombre AS Categoria, asesor.Nombre AS Asesor
        FROM asesoria
        JOIN categoria ON asesoria.id_Categoria = categoria.ID
        JOIN asesor ON asesoria_asesor.id_Asesor = asesor.ID
        JOIN asesoria_asesor ON asesoria.ID = asesoria_asesor.id_Asesoria
        WHERE asesoria.Fecha BETWEEN '$fechaInicio' AND '$fechaFin' 
        AND asesor.ID = '$asesor' 
        AND asesoria.id_Sede = '$sede' 
        AND asesoria.id_Categoria = '$categoria'";

$result = $conn->query($sql);

echo "<table>";
echo "<tr><th>ID</th><th>Correo</th><th>Fecha</th><th>Duración</th><th>Categoría</th><th>Asesor</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['ID']}</td><td>{$row['Correo']}</td><td>{$row['Fecha']}</td><td>{$row['Duracion']}</td><td>{$row['Categoria']}</td><td>{$row['Asesor']}</td></tr>";
}
echo "</table>";

$conn->close();
