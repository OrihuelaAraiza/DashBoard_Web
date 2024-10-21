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

if (empty($fechaInicio) || empty($fechaFin)) {
    echo "<p>Por favor, selecciona una fecha de inicio y una fecha de fin.</p>";
    exit;
}

$fechaInicio = $conn->real_escape_string($fechaInicio);
$fechaFin = $conn->real_escape_string($fechaFin);

$asesoresList = !empty($asesores) ? implode(",", array_map('intval', $asesores)) : '';
$sedesList = !empty($sedes) ? implode(",", array_map('intval', $sedes)) : '';
$categoriasList = !empty($categorias) ? implode(",", array_map('intval', $categorias)) : '';

$sqlAsesores = "SELECT 
                    asesor.ID, 
                    asesor.Nombre, 
                    asesor.Correo, 
                    COUNT(DISTINCT asesoria.ID) AS TotalAsesorias,
                    SUM(asesoria.Duracion) / 60 AS TotalHorasAsesorias
                FROM asesor
                JOIN asesoria_asesor ON asesor.ID = asesoria_asesor.id_Asesor
                JOIN asesoria ON asesoria_asesor.id_Asesoria = asesoria.ID
                WHERE asesoria.Fecha BETWEEN '$fechaInicio' AND '$fechaFin'";

if (!empty($asesoresList)) {
    $sqlAsesores .= " AND asesor.ID IN ($asesoresList)";
}
if (!empty($sedesList)) {
    $sqlAsesores .= " AND asesoria.id_Sede IN ($sedesList)";
}
if (!empty($categoriasList)) {
    $sqlAsesores .= " AND asesoria.id_Categoria IN ($categoriasList)";
}

$sqlAsesores .= " GROUP BY asesor.ID";

$resultAsesores = $conn->query($sqlAsesores);

if ($resultAsesores) {
    if ($resultAsesores->num_rows > 0) {
        echo "<table><tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Total Asesorías</th><th>Total Horas</th></tr>";
        while ($row = $resultAsesores->fetch_assoc()) {
            echo "<tr>
                <td>{$row['ID']}</td>
                <td>{$row['Nombre']}</td>
                <td>{$row['Correo']}</td>
                <td>{$row['TotalAsesorias']}</td>
                <td>" . number_format($row['TotalHorasAsesorias'], 2) . "</td>
            </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No se encontraron resultados para los asesores seleccionados.</p>";
    }
} else {
    echo "Error en la consulta: " . $conn->error;
}

$conn->close();
