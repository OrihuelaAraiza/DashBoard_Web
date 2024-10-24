<?php
include('conexion.php');

// Habilitar reporte de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Obtener datos del formulario
$fechaInicio = $_POST['fechaInicio'] ?? '';
$fechaFin = $_POST['fechaFin'] ?? '';
$asesores = $_POST['asesor'] ?? [];
$sedes = $_POST['sede'] ?? [];
$categorias = $_POST['categoria'] ?? [];

// Asegurarse de que son arrays
$asesores = is_array($asesores) ? $asesores : [$asesores];
$sedes = is_array($sedes) ? $sedes : [$sedes];
$categorias = is_array($categorias) ? $categorias : [$categorias];

// Sanitizar las entradas
$fechaInicio = $conn->real_escape_string($fechaInicio);
$fechaFin = $conn->real_escape_string($fechaFin);

// Convertimos los arrays a cadenas para la cláusula IN de SQL
$asesoresList = !empty($asesores) ? implode(",", array_map('intval', $asesores)) : '';
$sedesList = !empty($sedes) ? implode(",", array_map('intval', $sedes)) : '';
$categoriasList = !empty($categorias) ? implode(",", array_map('intval', $categorias)) : '';

$sqlCategorias = "SELECT 
                    categoria.Llave AS `Key`, 
                    categoria.Nombre AS Nombre, 
                    COUNT(DISTINCT asesoria.ID) AS Sesiones,
                    COUNT(DISTINCT asesoria.Correo) AS Profesores, 
                    SUM(asesoria.Duracion) / 60 AS TotalHorasProf, 
                    SUM(asesoria.Duracion * (SELECT COUNT(*) FROM asesoria_asesor WHERE asesoria_asesor.id_Asesoria = asesoria.ID)) / 60 AS TotalHorasTalent
                FROM asesoria
                JOIN categoria ON asesoria.id_Categoria = categoria.ID
                JOIN asesoria_asesor ON asesoria.ID = asesoria_asesor.id_Asesoria
                JOIN asesor ON asesoria_asesor.id_Asesor = asesor.ID
                WHERE asesoria.Fecha BETWEEN '$fechaInicio' AND '$fechaFin'";

if (!empty($asesoresList)) {
    $sqlCategorias .= " AND asesor.ID IN ($asesoresList)";
}
if (!empty($sedesList)) {
    $sqlCategorias .= " AND asesoria.id_Sede IN ($sedesList)";
}
if (!empty($categoriasList)) {
    $sqlCategorias .= " AND asesoria.id_Categoria IN ($categoriasList)";
}

$sqlCategorias .= " GROUP BY categoria.ID";

$resultCategorias = $conn->query($sqlCategorias);

if ($resultCategorias && $resultCategorias->num_rows > 0) {
    echo "<table><tr><th>Key</th><th>Nombre</th><th>Sesiones</th><th>Profesores</th><th>Total Horas Prof</th><th>Total Horas Talent</th><th>Duración Media Prof</th><th>Duración Media Talent</th></tr>";
    while ($row = $resultCategorias->fetch_assoc()) {
        $duracionMediaProf = $row['Sesiones'] > 0 ? $row['TotalHorasProf'] / $row['Sesiones'] : 0;
        $duracionMediaTalent = $row['Sesiones'] > 0 ? $row['TotalHorasTalent'] / $row['Sesiones'] : 0;
        echo "<tr>
            <td>{$row['Key']}</td>
            <td>{$row['Nombre']}</td>
            <td>{$row['Sesiones']}</td>
            <td>{$row['Profesores']}</td>
            <td>" . number_format($row['TotalHorasProf'], 2) . "</td>
            <td>" . number_format($row['TotalHorasTalent'], 2) . "</td>
            <td>" . number_format($duracionMediaProf, 2) . "</td>
            <td>" . number_format($duracionMediaTalent, 2) . "</td>
          </tr>";
    }
    echo "</table>";
} else {
    echo "<p>No se encontraron resultados para la vista de categorías.</p>";
}

$conn->close();
