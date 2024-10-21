<?php
include('conexion.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$fechaInicio = $_POST['fechaInicio'];
$fechaFin = $_POST['fechaFin'];
$asesores = isset($_POST['asesor']) ? $_POST['asesor'] : [];
$sedes = isset($_POST['sede']) ? $_POST['sede'] : [];
$categorias = isset($_POST['categoria']) ? $_POST['categoria'] : [];

if (!is_array($asesores)) {
    $asesores = [$asesores];
}
if (!is_array($sedes)) {
    $sedes = [$sedes];
}
if (!is_array($categorias)) {
    $categorias = [$categorias];
}

$fechaInicio = $conn->real_escape_string($fechaInicio);
$fechaFin = $conn->real_escape_string($fechaFin);

$asesoresList = !empty($asesores) ? implode(",", array_map('intval', $asesores)) : '';
$sedesList = !empty($sedes) ? implode(",", array_map('intval', $sedes)) : '';
$categoriasList = !empty($categorias) ? implode(",", array_map('intval', $categorias)) : '';

$sql = "SELECT asesoria.ID, asesoria.Correo, asesoria.Fecha, asesoria.Duracion, categoria.Nombre AS Categoria, asesor.Nombre AS Asesor
        FROM asesoria
        JOIN asesoria_asesor ON asesoria.ID = asesoria_asesor.id_Asesoria
        JOIN asesor ON asesoria_asesor.id_Asesor = asesor.ID
        JOIN categoria ON asesoria.id_Categoria = categoria.ID
        WHERE asesoria.Fecha BETWEEN '$fechaInicio' AND '$fechaFin'";

if (!empty($asesoresList)) {
    $sql .= " AND asesor.ID IN ($asesoresList)";
}
if (!empty($sedesList)) {
    $sql .= " AND asesoria.id_Sede IN ($sedesList)";
}
if (!empty($categoriasList)) {
    $sql .= " AND asesoria.id_Categoria IN ($categoriasList)";
}

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table><tr><th>ID</th><th>Correo</th><th>Fecha</th><th>Duración</th><th>Categoría</th><th>Asesor</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['ID']}</td><td>{$row['Correo']}</td><td>{$row['Fecha']}</td><td>{$row['Duracion']}</td><td>{$row['Categoria']}</td><td>{$row['Asesor']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p>No se encontraron resultados para los filtros aplicados.</p>";
}

$conn->close();
